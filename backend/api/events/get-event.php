<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";

$id = $_GET["id"] ?? null;

if (!$id) {

    echo json_encode([
        "success" => false,
        "message" => "Event ID is required."
    ]);

    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM events WHERE id = ?"
);

$stmt->execute([$id]);

$event = $stmt->fetch();

if (!$event) {

    echo json_encode([
        "success" => false,
        "message" => "Event not found."
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "event" => $event
]);

?>