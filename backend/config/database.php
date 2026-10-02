<?php

$host = "gateway01.ap-northeast-1.prod.aws.tidbcloud.com";
$port = 4000;
$dbname = "university_events";
$username = "2NRtqHAVJF8Cb4t.root";

$password = getenv("TIDB_PASSWORD");

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::MYSQL_ATTR_SSL_CA => "/etc/ssl/cert.pem",
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
        ]
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed."
    ]);

    exit;
}

?>
