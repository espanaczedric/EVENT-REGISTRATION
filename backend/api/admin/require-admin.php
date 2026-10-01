<?php

session_start();

if (empty($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Administrator access is required."
    ]);
    exit;
}
