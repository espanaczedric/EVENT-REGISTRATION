<?php

require_once "../../../config/cors.php";
require_once "../../../config/database.php";
require_once "../require-admin.php";

$stmt = $pdo->query(
    "SELECT
        id,
        student_id,
        first_name,
        last_name,
        email,
        course,
        year_level,
        role,
        created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT 500"
);

$countStmt = $pdo->query(
    "SELECT COUNT(*) FROM users WHERE role = 'student'"
);

echo json_encode([
    "success" => true,
    "users" => $stmt->fetchAll(),
    "student_count" => $countStmt->fetchColumn()
]);
