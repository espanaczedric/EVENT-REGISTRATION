<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";
require_once "../admin/require-admin.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Use POST to verify a registration."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$registrationCode = trim($data["registration_code"] ?? "");

if ($registrationCode === "" || strlen($registrationCode) > 100) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "The QR code is empty or invalid."
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT
            r.id AS registration_id,
            r.registration_code,
            r.status AS registration_status,
            u.first_name,
            u.last_name,
            u.student_id,
            u.email,
            u.course,
            u.year_level,
            e.title AS event_title,
            e.location AS event_location,
            e.start_date,
            e.end_date
         FROM registrations r
         INNER JOIN users u ON u.id = r.user_id
         INNER JOIN events e ON e.id = r.event_id
         WHERE r.registration_code = ?
         FOR UPDATE"
    );
    $stmt->execute([$registrationCode]);
    $registration = $stmt->fetch();

    if (!$registration) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "No registration matches this QR code."
        ]);
        exit;
    }

    $attendee = [
        "name" => $registration["first_name"] . " " . $registration["last_name"],
        "student_id" => $registration["student_id"],
        "email" => $registration["email"],
        "course" => $registration["course"],
        "year_level" => $registration["year_level"]
    ];
    $event = [
        "title" => $registration["event_title"],
        "location" => $registration["event_location"],
        "start_date" => $registration["start_date"],
        "end_date" => $registration["end_date"]
    ];

    if ($registration["registration_status"] === "cancelled") {
        $pdo->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "This registration was cancelled and cannot be checked in.",
            "attendee" => $attendee,
            "event" => $event,
            "registration_code" => $registration["registration_code"],
            "registration_status" => "cancelled"
        ]);
        exit;
    }

    if ($registration["registration_status"] === "attended") {
        $timeStmt = $pdo->prepare(
            "SELECT time_in
             FROM attendance
             WHERE registration_id = ?
             ORDER BY time_in DESC
             LIMIT 1"
        );
        $timeStmt->execute([$registration["registration_id"]]);
        $timeIn = $timeStmt->fetchColumn();
        $pdo->commit();

        echo json_encode([
            "success" => false,
            "already_checked_in" => true,
            "message" => "This student has already checked in.",
            "attendee" => $attendee,
            "event" => $event,
            "registration_code" => $registration["registration_code"],
            "registration_status" => "attended",
            "time_in" => $timeIn ?: null
        ]);
        exit;
    }

    $attendanceStmt = $pdo->prepare(
        "INSERT INTO attendance (registration_id, time_in)
         VALUES (?, NOW())"
    );
    $attendanceStmt->execute([$registration["registration_id"]]);
    $attendanceId = $pdo->lastInsertId();

    $updateStmt = $pdo->prepare(
        "UPDATE registrations
         SET status = 'attended'
         WHERE id = ?"
    );
    $updateStmt->execute([$registration["registration_id"]]);

    $timeStmt = $pdo->prepare(
        "SELECT time_in FROM attendance WHERE id = ?"
    );
    $timeStmt->execute([$attendanceId]);
    $timeIn = $timeStmt->fetchColumn();

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Registration verified. Attendance recorded.",
        "attendee" => $attendee,
        "event" => $event,
        "registration_code" => $registration["registration_code"],
        "registration_status" => "attended",
        "time_in" => $timeIn
    ]);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Attendance could not be recorded. Please try again."
    ]);
}