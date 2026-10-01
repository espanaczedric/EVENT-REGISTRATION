<?php

session_start();

require_once "../../config/cors.php";

$_SESSION = [];

session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Logged out successfully."
]);

?>