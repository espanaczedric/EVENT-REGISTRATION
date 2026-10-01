<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Use POST to delete a highlight."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$highlightId = filter_var($data["id"] ?? null, FILTER_VALIDATE_INT);
if (!$highlightId) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "A valid highlight ID is required."
    ]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM event_highlights WHERE id = ?");
$stmt->execute([$highlightId]);
if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Highlight not found."
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Highlight deleted."
]);
