<?php

session_start();

require_once "../../config/cors.php";
require_once "../../config/database.php";


if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" => "Not logged in."
    ]);

    exit;
}


$stmt = $pdo->prepare(
    "SELECT
        id,
        student_id,
        first_name,
        last_name,
        email,
        course,
        year_level,
        role

     FROM users

     WHERE id = ?

     LIMIT 1"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$user = $stmt->fetch();


if (!$user) {

    echo json_encode([
        "success" => false,
        "message" => "User not found."
    ]);

    exit;
}


echo json_encode([
    "success" => true,
    "user" => $user
]);

?>