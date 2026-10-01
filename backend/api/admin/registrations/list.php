<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

$stmt = $pdo->query(
    "SELECT
        r.id AS registration_id,
        r.registration_code,
        r.status,
        r.registered_at,
        u.first_name,
        u.last_name,
        u.student_id,
        u.email,
        e.title AS event_title
     FROM registrations r
     INNER JOIN users u ON u.id = r.user_id
     INNER JOIN events e ON e.id = r.event_id
     ORDER BY r.registered_at DESC
     LIMIT 200"
);

$countStmt = $pdo->query(
    "SELECT COUNT(*) FROM registrations WHERE status != 'cancelled'"
);

echo json_encode([
    "success" => true,
    "registrations" => $stmt->fetchAll(),
    "registration_count" => $countStmt->fetchColumn()
]);
