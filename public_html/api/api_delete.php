<?php
// api_delete.php — yalnız DELETE

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["error" => "Yalnız DELETE icazəlidir"]);
    exit;
}

// id-ni ?id= və ya PATH_INFO (/api_delete.php/7) vasitəsilə oxu
$id = null;

// 1) Query param: ?id=7
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $id = $_GET['id'];
}

// 2) Path param: /api_delete.php/7  (server PATH_INFO dəstəkləyirsə)
if ($id === null && !empty($_SERVER['PATH_INFO'])) {
    $pathPart = trim($_SERVER['PATH_INFO'], "/");
    if ($pathPart !== '') { $id = $pathPart; }
}

require_once __DIR__ . "/db.php";

try {
    if ($id !== null && $id !== '' && is_numeric($id)) {
        // Tək sətiri sil
        $stmt = $pdo->prepare("DELETE FROM su_suretleri WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["success" => true, "message" => "ID=$id olan qeyd silindi"]);
        } else {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "ID=$id tapılmadı"]);
        }
    } else {
        // id göndərilməyibsə — bütün cədvəli sil
        $stmt = $pdo->prepare("DELETE FROM su_suretleri");
        $stmt->execute();

        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Bütün qeyd(lər) silindi"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Delete xətası"]);
}
