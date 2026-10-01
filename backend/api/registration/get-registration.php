<?php

session_start();

require_once "../../config/cors.php";
require_once "../../config/database.php";


if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "You must be logged in."
    ]);

    exit;
}


$code = trim(
    $_GET["code"] ?? ""
);


if (!$code) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Registration code is required."
    ]);

    exit;
}


$stmt = $pdo->prepare(
    "SELECT

        r.id AS registration_id,
        r.registration_code,
        r.status,
        r.registered_at,

        u.student_id,
        u.first_name,
        u.last_name,
        u.email,
        u.course,
        u.year_level,

        e.id AS event_id,
        e.title,
        e.description,
        e.location,
        e.start_date,
        e.end_date,
        e.cover_image

     FROM registrations r

     INNER JOIN users u
        ON r.user_id = u.id

     INNER JOIN events e
        ON r.event_id = e.id

     WHERE r.registration_code = ?
       AND r.user_id = ?

     LIMIT 1"
);

$stmt->execute([
    $code,
    $_SESSION["user_id"]
]);

$registration = $stmt->fetch();


if (!$registration) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Registration not found."
    ]);

    exit;
}


echo json_encode([
    "success" => true,
    "registration" => $registration
]);

?>