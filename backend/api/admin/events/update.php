<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Use POST to update an event."
    ]);
    exit;
}

$eventId = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);
if (!$eventId) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "A valid event ID is required."
    ]);
    exit;
}

$existingStmt = $pdo->prepare("SELECT id, cover_image FROM events WHERE id = ?");
$existingStmt->execute([$eventId]);
$existingEvent = $existingStmt->fetch();

if (!$existingEvent) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Event not found."
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

if (!in_array($status, ["upcoming", "ongoing", "completed", "cancelled"], true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Choose a valid event status."
    ]);
    exit;
}

$registrationCountStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM registrations WHERE event_id = ? AND status != 'cancelled'"
);
$registrationCountStmt->execute([$eventId]);
$registrationCount = (int) $registrationCountStmt->fetchColumn();

if ($capacity < $registrationCount) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Capacity cannot be lower than the existing attendee count."
    ]);
    exit;
}

$image = $_FILES["cover_image"] ?? null;
$replaceImage = $image && $image["error"] !== UPLOAD_ERR_NO_FILE;
$coverImage = $existingEvent["cover_image"];
$imagePath = null;

if ($replaceImage) {
    if ($image["error"] !== UPLOAD_ERR_OK || $image["size"] > 5 * 1024 * 1024) {
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
}

try {
    if ($replaceImage && !move_uploaded_file($image["tmp_name"], $imagePath)) {
        throw new RuntimeException("Unable to store uploaded image.");
    }

    $stmt = $pdo->prepare(
        "UPDATE events SET
            title = ?,
            description = ?,
            location = ?,
            start_date = ?,
            end_date = ?,
            registration_deadline = ?,
            capacity = ?,
            cover_image = ?,
            status = ?
         WHERE id = ?"
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
        $status,
        $eventId
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Event updated successfully.",
        "event_id" => $eventId
    ]);
} catch (Throwable $error) {
    if ($imagePath && is_file($imagePath)) {
        unlink($imagePath);
    }

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "The event could not be updated. Please try again."
    ]);
}
