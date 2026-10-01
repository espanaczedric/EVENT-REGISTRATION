<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Use POST to create a highlight."
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$eventId = filter_var($data["event_id"] ?? null, FILTER_VALIDATE_INT);
$title = trim($data["title"] ?? "");
$description = trim($data["description"] ?? "");
$mediaUrl = trim($data["media_url"] ?? "");
$mediaType = $data["media_type"] ?? "image";

if (!$eventId || $title === "" || strlen($title) > 255 || $description === "" || $mediaUrl === "" || strlen($mediaUrl) > 255) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Complete the event, title, description, and media URL fields."
    ]);
    exit;
}

if (!in_array($mediaType, ["image", "video"], true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Media type must be image or video."
    ]);
    exit;
}

$isLocalUpload = str_starts_with($mediaUrl, "/uploads/highlights/") && !str_contains($mediaUrl, "..");
$parsedUrl = filter_var($mediaUrl, FILTER_VALIDATE_URL) ? parse_url($mediaUrl) : false;
$isWebUrl = $parsedUrl && in_array(strtolower($parsedUrl["scheme"] ?? ""), ["http", "https"], true);

if (!$isLocalUpload && !$isWebUrl) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Use an HTTP(S) URL or a path under /uploads/highlights/."
    ]);
    exit;
}

$eventStmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
$eventStmt->execute([$eventId]);
if (!$eventStmt->fetch()) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Event not found."
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO event_highlights (event_id, title, description, media_url, media_type)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$eventId, $title, $description, $mediaUrl, $mediaType]);

echo json_encode([
    "success" => true,
    "message" => "Highlight added.",
    "highlight_id" => $pdo->lastInsertId()
]);
