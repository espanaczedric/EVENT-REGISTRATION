<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Use POST to create an event."
    ]);
    exit;
}

$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$location = trim($_POST["location"] ?? "");
$startValue = trim($_POST["start_date"] ?? "");
$endValue = trim($_POST["end_date"] ?? "");
$deadlineValue = trim($_POST["registration_deadline"] ?? "");
$capacity = filter_var($_POST["capacity"] ?? null, FILTER_VALIDATE_INT);
$status = $_POST["status"] ?? "upcoming";

$parseDate = static function (string $value): ?DateTimeImmutable {
    $date = DateTimeImmutable::createFromFormat("!Y-m-d\\TH:i", $value);
    $errors = DateTimeImmutable::getLastErrors();

    if (!$date || ($errors && ($errors["warning_count"] > 0 || $errors["error_count"] > 0))) {
        return null;
    }

    return $date;
};

$startDate = $parseDate($startValue);
$endDate = $parseDate($endValue);
$deadline = $deadlineValue === "" ? null : $parseDate($deadlineValue);

if ($title === "" || strlen($title) > 255 || $description === "" || $location === "" || strlen($location) > 255) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Enter a title, event description, and location."
    ]);
    exit;
}

if (!$startDate || !$endDate || $startDate >= $endDate) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Enter valid start and end times; the end must be after the start."
    ]);
    exit;
}

if ($deadlineValue !== "" && (!$deadline || $deadline > $startDate)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "The registration deadline must be valid and no later than the event start."
    ]);
    exit;
}

if ($capacity === false || $capacity < 1) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Capacity must be at least one attendee."
    ]);
    exit;
}

if (!in_array($status, ["upcoming", "ongoing"], true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Choose Upcoming or Happening now for a new event."
    ]);
    exit;
}

$image = $_FILES["cover_image"] ?? null;
if (!$image || $image["error"] !== UPLOAD_ERR_OK || $image["size"] > 5 * 1024 * 1024) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Choose an image smaller than 5 MB."
    ]);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($image["tmp_name"]);
$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

if (!isset($allowedTypes[$mimeType])) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Use a JPG, PNG, or WebP cover image."
    ]);
    exit;
}

$uploadDirectory = dirname(__DIR__, 3) . "/uploads/events";
if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "The event image folder could not be created."
    ]);
    exit;
}

$fileName = "event-" . bin2hex(random_bytes(12)) . "." . $allowedTypes[$mimeType];
$imagePath = $uploadDirectory . "/" . $fileName;
$coverImage = "/uploads/events/" . $fileName;

try {
    if (!move_uploaded_file($image["tmp_name"], $imagePath)) {
        throw new RuntimeException("Unable to store uploaded image.");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO events (
            title,
            description,
            location,
            start_date,
            end_date,
            registration_deadline,
            capacity,
            cover_image,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $title,
        $description,
        $location,
        $startDate->format("Y-m-d H:i:s"),
        $endDate->format("Y-m-d H:i:s"),
        $deadline ? $deadline->format("Y-m-d H:i:s") : null,
        $capacity,
        $coverImage,
        $status
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Event created successfully.",
        "event_id" => $pdo->lastInsertId()
    ]);
} catch (Throwable $error) {
    if (is_file($imagePath)) {
        unlink($imagePath);
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "The event could not be saved. Please try again."
    ]);
}
