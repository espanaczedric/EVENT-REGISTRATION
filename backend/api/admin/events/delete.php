<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Use POST to remove an event."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$eventId = filter_var($data["id"] ?? null, FILTER_VALIDATE_INT);
if (!$eventId) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "A valid event ID is required."
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $eventStmt = $pdo->prepare("SELECT id, title, cover_image FROM events WHERE id = ? FOR UPDATE");
    $eventStmt->execute([$eventId]);
    $event = $eventStmt->fetch();

    if (!$event) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Event not found."
        ]);
        exit;
    }

    $registrationStmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
    $registrationStmt->execute([$eventId]);
    $registrationCount = (int) $registrationStmt->fetchColumn();

    $imageReferenceStmt = $pdo->prepare(
        "SELECT COUNT(*) FROM events WHERE cover_image = ? AND id != ?"
    );
    $imageReferenceStmt->execute([$event["cover_image"], $eventId]);
    $otherImageReferences = (int) $imageReferenceStmt->fetchColumn();

    $deleteStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $deleteStmt->execute([$eventId]);
    $pdo->commit();

    if (
        $otherImageReferences === 0 &&
        preg_match("#^/uploads/events/event-[a-f0-9]{24}\\.(jpg|png|webp)$#", $event["cover_image"])
    ) {
        $imagePath = dirname(__DIR__, 3) . "/uploads/events/" . basename($event["cover_image"]);
        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Event and its related records were removed.",
        "event_title" => $event["title"],
        "registrations_removed" => $registrationCount
    ]);
} catch (Throwable $error) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "The event could not be removed. Please try again."
    ]);
}
