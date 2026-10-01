<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";

$sql = "
    SELECT *
    FROM events
    WHERE status IN ('upcoming', 'ongoing')
    ORDER BY start_date ASC
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$event = $stmt->fetch();

echo json_encode([
    "success" => true,
    "event" => $event
]);

?>