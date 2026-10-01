<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";

$sql = "
    SELECT *
    FROM events
    WHERE status = 'completed'
    ORDER BY start_date DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$events = $stmt->fetchAll();

echo json_encode([
    "success" => true,
    "events" => $events
]);

?>