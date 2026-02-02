<?php
// api_insert.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Yalnız POST icazəlidir"]);
    exit;
}

$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
if (stripos($contentType, 'application/json') === false) {
    http_response_code(400);
    echo json_encode(["error" => "Content-Type application/json olmalıdır"]);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Yanlış JSON"]);
    exit;
}

$sethi_suret = $data['sethi_suret'] ?? null;
$orta_suret  = $data['orta_suret']  ?? null;
$tezlik      = $data['tezlik']      ?? null;

$errors = [];
foreach (['sethi_suret','orta_suret','tezlik'] as $k) {
    if (!isset($data[$k]) || $data[$k] === '') $errors[] = "$k tələb olunur";
    elseif (!is_numeric($data[$k])) $errors[] = "$k rəqəm olmalıdır";
}
if ($errors) {
    http_response_code(422);
    echo json_encode(["error" => "Validasiya xətası", "details" => $errors]);
    exit;
}

// 🔗 Burada DB bağlantısını çağırırıq
require_once __DIR__ . "/db.php";

try {
    $stmt = $pdo->prepare(
        "INSERT INTO su_suretleri (sethi_suret, orta_suret, tezlik) VALUES (:s, :o, :t)"
    );
    $stmt->execute([
        ':s' => (float)$sethi_suret,
        ':o' => (float)$orta_suret,
        ':t' => (float)$tezlik
    ]);

    $insertId = $pdo->lastInsertId();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "id" => (int)$insertId,
        "message" => "Məlumat əlavə olundu"
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Insert xətası"]);
}
