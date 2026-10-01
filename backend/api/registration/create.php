<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$event_id = $data["event_id"] ?? null;

$first_name = trim(
    $data["first_name"] ?? ""
);

$last_name = trim(
    $data["last_name"] ?? ""
);

$student_id = trim(
    $data["student_id"] ?? ""
);

$email = trim(
    $data["email"] ?? ""
);

$course = trim(
    $data["course"] ?? ""
);

$year_level = trim(
    $data["year_level"] ?? ""
);


if (
    !$event_id ||
    !$first_name ||
    !$last_name ||
    !$student_id ||
    !$email
) {

    echo json_encode([
        "success" => false,
        "message" => "Please complete all required fields."
    ]);

    exit;
}

if (!preg_match('/^01-[0-9]{4}-[0-9]{6}$/D', $student_id)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Student ID must use the format 01-1234-123456."
    ]);
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK EVENT
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM events
     WHERE id = ?"
);

$stmt->execute([
    $event_id
]);

$event = $stmt->fetch();


if (!$event) {

    echo json_encode([
        "success" => false,
        "message" => "Event not found."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK REGISTRATION DEADLINE
|--------------------------------------------------------------------------
*/

if (
    $event["registration_deadline"] &&
    strtotime(
        $event["registration_deadline"]
    ) < time()
) {

    echo json_encode([
        "success" => false,
        "message" => "Registration is already closed."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK CAPACITY
|--------------------------------------------------------------------------
*/

$countStmt = $pdo->prepare(
    "SELECT COUNT(*)
     FROM registrations
     WHERE event_id = ?
     AND status != 'cancelled'"
);

$countStmt->execute([
    $event_id
]);

$registeredCount =
    $countStmt->fetchColumn();


if (
    $event["capacity"] > 0 &&
    $registeredCount >= $event["capacity"]
) {

    echo json_encode([
        "success" => false,
        "message" => "This event is already full."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CREATE USER
|--------------------------------------------------------------------------
*/

$userStmt = $pdo->prepare(
    "SELECT id
     FROM users
     WHERE student_id = ?"
);

$userStmt->execute([
    $student_id
]);

$user = $userStmt->fetch();


if ($user) {

    $user_id = $user["id"];

} else {

    /*
     * Temporary account creation.
     * Authentication can be added later.
     */

    $temporaryPassword =
        password_hash(
            bin2hex(random_bytes(8)),
            PASSWORD_DEFAULT
        );

    $insertUser = $pdo->prepare(
        "INSERT INTO users
        (
            student_id,
            first_name,
            last_name,
            email,
            password,
            course,
            year_level
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $insertUser->execute([
        $student_id,
        $first_name,
        $last_name,
        $email,
        $temporaryPassword,
        $course,
        $year_level
    ]);

    $user_id =
        $pdo->lastInsertId();
}


/*
|--------------------------------------------------------------------------
| CHECK DUPLICATE REGISTRATION
|--------------------------------------------------------------------------
*/

$duplicate = $pdo->prepare(
    "SELECT id
     FROM registrations
     WHERE user_id = ?
     AND event_id = ?"
);

$duplicate->execute([
    $user_id,
    $event_id
]);


if ($duplicate->fetch()) {

    echo json_encode([
        "success" => false,
        "message" =>
            "You are already registered for this event."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CREATE REGISTRATION CODE
|--------------------------------------------------------------------------
*/

$registration_code =
    "REG-" .
    strtoupper(
        bin2hex(
            random_bytes(6)
        )
    );


/*
|--------------------------------------------------------------------------
| INSERT REGISTRATION
|--------------------------------------------------------------------------
*/

$registration = $pdo->prepare(
    "INSERT INTO registrations
    (
        user_id,
        event_id,
        registration_code
    )
    VALUES (?, ?, ?)"
);

$registration->execute([
    $user_id,
    $event_id,
    $registration_code
]);


echo json_encode([
    "success" => true,
    "message" =>
        "Registration successful.",
    "registration_code" =>
        $registration_code
]);

?>