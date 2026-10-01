<?php

session_start();

require_once "../../config/cors.php";
require_once "../../config/database.php";


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" =>
            "You must be logged in to view your events."
    ]);

    exit;
}


$user_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| GET REGISTERED EVENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT

        r.id AS registration_id,

        r.registration_code,
        r.status,
        r.registered_at,

        e.id AS event_id,
        e.title,
        e.description,
        e.location,
        e.start_date,
        e.end_date,
        e.cover_image,
        e.status AS event_status

     FROM registrations r

     INNER JOIN events e
        ON r.event_id = e.id

     WHERE r.user_id = ?

     ORDER BY e.start_date DESC"
);

$stmt->execute([
    $user_id
]);

$events = $stmt->fetchAll();


echo json_encode([
    "success" => true,
    "events" => $events
]);

?>