<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";

$eventId = filter_var($_GET["event_id"] ?? null, FILTER_VALIDATE_INT);
if (!$eventId) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "A valid event ID is required."
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, event_id, title, description, media_url, media_type, created_at
     FROM event_highlights
     WHERE event_id = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$eventId]);

echo json_encode([
    "success" => true,
    "highlights" => $stmt->fetchAll()
]);
