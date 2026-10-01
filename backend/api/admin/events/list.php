<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

$eventsStmt = $pdo->query(
    "SELECT
        e.*,
        (
            SELECT COUNT(*)
            FROM registrations r
            WHERE r.event_id = e.id
                AND r.status != 'cancelled'
        ) AS registration_count
     FROM events e
     ORDER BY e.start_date DESC"
);

$statsStmt = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM events) AS event_count,
        (SELECT COUNT(*) FROM events WHERE status IN ('upcoming', 'ongoing')) AS active_event_count,
        (SELECT COUNT(*) FROM registrations WHERE status != 'cancelled') AS registration_count,
        (SELECT COUNT(*) FROM users WHERE role = 'student') AS student_count"
);

echo json_encode([
    "success" => true,
    "events" => $eventsStmt->fetchAll(),
    "stats" => $statsStmt->fetch()
]);
