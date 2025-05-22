<?php
require_once 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($action == 'add' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        $talla = isset($_POST['talla']) ? trim($_POST['talla']) : null; // Recibir la talla

        if ($quantity <= 0) $quantity = 1;

        // Verificar si se seleccionó una talla (si es obligatorio)
        if ($talla === null) {
            $response = ['status' => 'error', 'message' => 'Por favor, selecciona una talla.'];
        } else {
            // Obtener info del producto de la BD
            $stmt = $pdo->prepare("SELECT id, nombre, precio, imagen_url, stock FROM productos WHERE id = :id AND stock >= :quantity");
            $stmt->execute(['id' => $product_id, 'quantity' => $quantity]); // Verificar stock
            $product = $stmt->fetch();

            if ($product) {
                $item_key = $product_id . '_' . $talla; // Clave única por producto y talla
                if (isset($_SESSION['cart'][$item_key])) {
                    // Si ya está, aumentar cantidad (verificando stock)
                    $new_quantity = $_SESSION['cart'][$item_key]['cantidad'] + $quantity;
                    if ($new_quantity <= $product['stock']) {
                        $_SESSION['cart'][$item_key]['cantidad'] = $new_quantity;
                        $response = ['status' => 'success', 'message' => "'" . html_escape($product['nombre']) . "' (Talla: " . html_escape($talla) . ") cantidad actualizada en el carrito.", 'cart_item_count' => count($_SESSION['cart'])];
                    } else {
                        $response = ['status' => 'error', 'message' => "No hay suficiente stock para '" . html_escape($product['nombre']) . "' (Talla: " . html_escape($talla) . "). Disponible: " . $product['stock']];
                    }
                } else {
                    // Agregar nuevo producto con talla
                    $_SESSION['cart'][$item_key] = [
                        'id' => $product['id'],
                        'nombre' => $product['nombre'],
                        'precio' => $product['precio'],
                        'cantidad' => $quantity,
                        'imagen_url' => $product['imagen_url'],
                        'talla' => $talla
                    ];
                    $response = ['status' => 'success', 'message' => "'" . html_escape($product['nombre']) . "' (Talla: " . html_escape($talla) . ") agregado al carrito.", 'cart_item_count' => count($_SESSION['cart'])];
                }
            } else {
                $response = ['status' => 'error', 'message' => "Producto no encontrado o no hay suficiente stock."];
            }
        }

        // Si es una petición AJAX, responder con JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else {
            // Si no es AJAX, redirigir como antes (esto podría necesitar ajustes si también quieres manejar la talla sin AJAX)
            $_SESSION['response'] = $response; // Pasar la respuesta a la sesión para mostrarla en la página siguiente
            $referer = $_SERVER['HTTP_REFERER'] ?? SITE_URL . 'cart.php';
            redirect($referer);
        }

    } elseif ($action == 'update' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        // ... (tu código para actualizar el carrito - podría necesitar considerar la talla también) ...
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        $talla = isset($_POST['talla']) ? trim($_POST['talla']) : null;
        $item_key = $product_id . '_' . $talla;

        if (isset($_SESSION['cart'][$item_key])) {
            if ($quantity > 0) {
                $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = :id");
                $stmt->execute(['id' => $product_id]);
                $product_stock = $stmt->fetchColumn();

                if ($quantity <= $product_stock) {
                    $_SESSION['cart'][$item_key]['cantidad'] = $quantity;
                    $_SESSION['success_message'] = "Cantidad actualizada.";
                } else {
                    $_SESSION['error_message'] = "No hay suficiente stock. Disponible: " . $product_stock;
                }
            } else {
                unset($_SESSION['cart'][$item_key]);
                $_SESSION['success_message'] = "Producto eliminado del carrito.";
            }
        }
        redirect(SITE_URL . 'cart.php');

    } elseif ($action == 'remove' && isset($_POST['product_id']) && isset($_POST['talla'])) {
        $product_id = (int)$_POST['product_id'];
        $talla = trim($_POST['talla']);
        $item_key = $product_id . '_' . $talla;
        if (isset($_SESSION['cart'][$item_key])) {
            unset($_SESSION['cart'][$item_key]);
            $_SESSION['success_message'] = "Producto (Talla: " . html_escape($talla) . ") eliminado del carrito.";
        }
        redirect(SITE_URL . 'cart.php');

    } elseif ($action == 'clear') {
        $_SESSION['cart'] = [];
        $_SESSION['success_message'] = "Carrito vaciado.";
        redirect(SITE_URL . 'cart.php');
    }
} else {
    // Si se accede directamente sin POST o acción, redirigir
    redirect(SITE_URL . 'index.php');
}
?>