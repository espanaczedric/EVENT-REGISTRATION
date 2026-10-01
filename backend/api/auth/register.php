<?php

require_once "../../config/cors.php";
require_once "../../config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$student_id = trim(
    $data["student_id"] ?? ""
);

$first_name = trim(
    $data["first_name"] ?? ""
);

$last_name = trim(
    $data["last_name"] ?? ""
);

$email = trim(
    $data["email"] ?? ""
);

$password = $data["password"] ?? "";

$course = trim(
    $data["course"] ?? ""
);

$year_level = trim(
    $data["year_level"] ?? ""
);


/*
|--------------------------------------------------------------------------
| VALIDATE
|--------------------------------------------------------------------------
*/

if (
    !$student_id ||
    !$first_name ||
    !$last_name ||
    !$email ||
    !$password ||
    !$course ||
    !$year_level
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Please complete all required fields."
    ]);

    exit;
}

if (!preg_match('/^01-[0-9]{4}-[0-9]{6}$/D', $student_id)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Student ID must use the format 01-1234-123456."
    ]);
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);

    exit;
}


if (strlen($password) < 8) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Password must be at least 8 characters."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK EXISTING ACCOUNT
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT id
     FROM users
     WHERE student_id = ?
        OR email = ?
     LIMIT 1"
);

$stmt->execute([
    $student_id,
    $email
]);

$existingUser = $stmt->fetch();


if ($existingUser) {

    echo json_encode([
        "success" => false,
        "message" =>
            "A student account with that ID or email already exists."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| CREATE ACCOUNT
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "INSERT INTO users
    (
        student_id,
        first_name,
        last_name,
        email,
        password,
        course,
        year_level,
        role
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'student')"
);

$stmt->execute([
    $student_id,
    $first_name,
    $last_name,
    $email,
    $hashedPassword,
    $course,
    $year_level
]);


echo json_encode([
    "success" => true,
    "message" =>
        "Account created successfully."
]);

?>