<?php
$host = "localhost";
$db   = "u879108216_iot";
$user = "u879108216_iot";
$pass = "Salam12345Salam";

$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "DB bağlantı xətası"]);
    exit;
}
