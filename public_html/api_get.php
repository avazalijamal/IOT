<?php
// api_get.php — GET: hamısı və ya id ilə tək qeyd

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Yalnız GET icazəlidir"]);
    exit;
}

require_once __DIR__ . "/db.php";

// id-ni ?id= və ya PATH_INFO (/api_get.php/7) vasitəsilə oxu
$id = null;

// 1) Query param: ?id=7
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $id = $_GET['id'];
}

// 2) Path param: /api_get.php/7  (server PATH_INFO dəstəkləyirsə)
if ($id === null && !empty($_SERVER['PATH_INFO'])) {
    $pathPart = trim($_SERVER['PATH_INFO'], "/");
    if ($pathPart !== '') { $id = $pathPart; }
}

try {
    if ($id !== null) {
        // ---- Tək qeyd (by id) ----
        if (!ctype_digit((string)$id)) {
            http_response_code(400);
            echo json_encode(["error" => "id rəqəm olmalıdır"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, sethi_suret, orta_suret, tezlik, tarix 
                               FROM su_suretleri WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            http_response_code(200);
            echo json_encode(["success" => true, "data" => $row]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "ID=$id tapılmadı"]);
        }
    } else {
        // ---- Siyahı (hamısı) ----
        // Opsional: limit/offset/order
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $order = strtolower($_GET['order'] ?? 'desc'); // 'asc' | 'desc'

        // Sadə validasiya
        $limit = max(1, min($limit, 500));
        $offset = max(0, $offset);
        $order = in_array($order, ['asc','desc'], true) ? $order : 'desc';

        // (İstəyə görə tarix aralığı filtrləri də əlavə oluna bilər)
        $sql = "SELECT id, sethi_suret, orta_suret, tezlik, tarix
                FROM su_suretleri
                ORDER BY tarix $order, id $order
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "count"   => count($rows),
            "limit"   => $limit,
            "offset"  => $offset,
            "order"   => $order,
            "data"    => $rows
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Select xətası"]);
}
