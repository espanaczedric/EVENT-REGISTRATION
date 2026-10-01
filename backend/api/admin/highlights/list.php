<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

$stmt = $pdo->query(
    "SELECT h.id, h.event_id, h.title, h.description, h.media_url, h.media_type, h.created_at,
            e.title AS event_title
     FROM event_highlights h
     INNER JOIN events e ON e.id = h.event_id
     ORDER BY h.created_at DESC"
);

echo json_encode([
    "success" => true,
    "highlights" => $stmt->fetchAll()
]);
