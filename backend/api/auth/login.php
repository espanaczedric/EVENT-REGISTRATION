<?php

session_start();

require_once "../../config/cors.php";
require_once "../../config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";


if (!$email || !$password) {

    echo json_encode([
        "success" => false,
        "message" => "Please enter your email and password."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| FIND USER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT *
     FROM users
     WHERE email = ?
     LIMIT 1"
);

$stmt->execute([$email]);

$user = $stmt->fetch();


if (!$user) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| VERIFY PASSWORD
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user["password"])) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CREATE SESSION
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

$_SESSION["user_id"] = $user["id"];
$_SESSION["role"] = $user["role"];


/*
|--------------------------------------------------------------------------
| RETURN USER
|--------------------------------------------------------------------------
*/

echo json_encode([
    "success" => true,
    "message" => "Login successful.",

    "user" => [
        "id" => $user["id"],
        "student_id" => $user["student_id"],
        "first_name" => $user["first_name"],
        "last_name" => $user["last_name"],
        "email" => $user["email"],
        "course" => $user["course"],
        "year_level" => $user["year_level"],
        "role" => $user["role"]
    ]
]);

?>