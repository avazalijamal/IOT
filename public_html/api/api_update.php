<?php
// api_update.php — PUT/PATCH: id üzrə qeyd yenilə

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT','PATCH'], true)) {
    http_response_code(405);
    echo json_encode(["error" => "Yalnız PUT və ya PATCH icazəlidir"]);
    exit;
}

// ---- id-ni oxu: ?id=..., /api_update.php/7, yaxud body { "id": 7 }
$id = null;

// 1) Query param
if (isset($_GET['id']) && $_GET['id'] !== '') { $id = $_GET['id']; }

// 2) Path param (PATH_INFO aktivdirsə)
if ($id === null && !empty($_SERVER['PATH_INFO'])) {
    $pathPart = trim($_SERVER['PATH_INFO'], "/");
    if ($pathPart !== '') { $id = $pathPart; }
}

// 3) Body (JSON)
$contentType = $_SERVER["CONTENT_TYPE"] ?? '';
$raw = file_get_contents('php://input');
$body = [];
if ($raw !== '') {
    if (stripos($contentType, 'application/json') === false) {
        http_response_code(400);
        echo json_encode(["error" => "Content-Type application/json olmalıdır"]);
        exit;
    }
    $body = json_decode($raw, true);
    if (!is_array($body)) {
        http_response_code(400);
        echo json_encode(["error" => "Yanlış JSON"]);
        exit;
    }
    if ($id === null && isset($body['id'])) { $id = $body['id']; }
}

if ($id === null || !ctype_digit((string)$id)) {
    http_response_code(400);
    echo json_encode(["error" => "Düzgün 'id' verilməyib"]);
    exit;
}
$id = (int)$id;

// ---- Yenilənə bilən sahələr
$fields = [
    'sethi_suret' => null,
    'orta_suret'  => null,
    'tezlik'      => null,
];

// Body-dən gələn sahələri götür və yoxla
$updates = [];
$params  = [':id' => $id];

foreach ($fields as $k => $_) {
    if (array_key_exists($k, $body)) {
        if ($body[$k] === '' || !is_numeric($body[$k])) {
            http_response_code(422);
            echo json_encode(["error" => "Validasiya xətası", "details" => ["$k rəqəm olmalıdır"]]);
            exit;
        }
        $updates[]       = "$k = :$k";
        $params[":$k"]   = (float)$body[$k];
    }
}

if (empty($updates)) {
    http_response_code(422);
    echo json_encode(["error" => "Yenilənəcək heç bir sahə göndərilməyib (sethi_suret, orta_suret, tezlik)"]);
    exit;
}

require_once __DIR__ . "/db.php";

try {
    // Mövcudluq yoxlaması
    $chk = $pdo->prepare("SELECT id FROM su_suretleri WHERE id = :id");
    $chk->execute([':id' => $id]);
    if (!$chk->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "ID=$id tapılmadı"]);
        exit;
    }

    // Dinamik UPDATE
    $sql = "UPDATE su_suretleri SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Yenilənmiş sətri qaytar
    $get = $pdo->prepare("SELECT id, sethi_suret, orta_suret, tezlik, tarix FROM su_suretleri WHERE id = :id");
    $get->execute([':id' => $id]);
    $row = $get->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Qeyd yeniləndi", "data" => $row]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Update xətası"]);
}
