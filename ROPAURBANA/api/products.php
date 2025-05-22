<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Para desarrollo, permite cualquier origen
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../includes/config.php'; // Ajusta la ruta si es necesario

$response = ['status' => 'error', 'message' => 'Acción no válida.', 'data' => null];
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'OPTIONS') { // Pre-flight request for CORS
    http_response_code(200);
    exit();
}

if ($method == 'GET') {
    try {
        if (isset($_GET['id'])) {
            $product_id = (int)$_GET['id'];
            $stmt = $pdo->prepare("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = :id");
            $stmt->execute(['id' => $product_id]);
            $product = $stmt->fetch();
            if ($product) {
                $response = ['status' => 'success', 'data' => $product];
            } else {
                http_response_code(404);
                $response = ['status' => 'error', 'message' => 'Producto no encontrado.'];
            }
        } else {
            // Lógica para filtros (similar a index.php)
            $category_slug = $_GET['category'] ?? null;
            $search_term = $_GET['search_term'] ?? null;

            $sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE 1=1";
            $params = [];

            if ($category_slug) {
                // Obtener ID de categoría por slug (simplificado, deberías manejar si no existe)
                $stmt_cat = $pdo->prepare("SELECT id FROM categorias WHERE slug = :slug");
                $stmt_cat->execute(['slug' => $category_slug]);
                $cat = $stmt_cat->fetch();
                if ($cat) {
                    $sql .= " AND p.categoria_id = :cat_id";
                    $params[':cat_id'] = $cat['id'];
                }
            }
            if ($search_term) {
                $sql .= " AND (p.nombre LIKE :search OR p.descripcion LIKE :search)";
                $params[':search'] = '%' . $search_term . '%';
            }
            // Añadir más filtros: precio, stock, etc.

            $stmt = $pdo->prepare($sql . " ORDER BY p.fecha_creacion DESC");
            $stmt->execute($params);
            $products = $stmt->fetchAll();
            $response = ['status' => 'success', 'data' => $products, 'count' => count($products)];
        }
    } catch (PDOException $e) {
        http_response_code(500);
        $response = ['status' => 'error', 'message' => 'Error de base de datos: ' . $e->getMessage()]; // No mostrar $e->getMessage() en producción
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>