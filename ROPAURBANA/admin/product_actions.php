<?php
require_once '../includes/config.php';
require_admin();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ---- ACCIÓN AGREGAR O EDITAR ----
    if ($action == 'add' || $action == 'edit') {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
        $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
        $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT, ["options" => ["min_range"=>0]]);

        // Validaciones
        if (empty($nombre)) $errors[] = "El nombre del producto es requerido.";
        if ($precio === false || $precio < 0) $errors[] = "El precio no es válido.";
        if ($stock === false) $errors[] = "El stock no es válido.";
        // Validar categoría si es obligatoria, etc.

        $imagen_url = $_POST['current_image_url'] ?? 'default_product.jpg'; // Para edición
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
            $target_dir = UPLOADS_PATH; // Definido en config.php
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            $file_extension = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($file_extension, $allowed_types)) {
                $errors[] = "Formato de imagen no permitido. Solo JPG, JPEG, PNG, WEBP.";
            } elseif ($_FILES["imagen"]["size"] > 2000000) { // 2MB
                $errors[] = "La imagen es demasiado grande (máx 2MB).";
            } else {
                // Generar nombre único para evitar colisiones
                $imagen_nombre_base = preg_replace("/[^a-zA-Z0-9_-]/", "", str_replace(" ", "_", strtolower($nombre)));
                $imagen_url_new = $imagen_nombre_base . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $imagen_url_new;

                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                    // Si se subió una nueva imagen y estamos editando, borrar la anterior (si no es la default)
                    if ($action == 'edit' && !empty($_POST['current_image_url']) && $_POST['current_image_url'] != 'default_product.jpg' && $_POST['current_image_url'] != $imagen_url_new) {
                        if (file_exists($target_dir . $_POST['current_image_url'])) {
                            unlink($target_dir . $_POST['current_image_url']);
                        }
                    }
                    $imagen_url = $imagen_url_new; // Usar la nueva imagen
                } else {
                    $errors[] = "Error al subir la imagen.";
                }
            }
        } elseif ($action == 'add' && (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] != UPLOAD_ERR_OK) ) {
             $imagen_url = 'default_product.jpg'; // Imagen por defecto si no se sube ninguna al agregar
        }


        if (empty($errors)) {
            try {
                if ($action == 'add') {
                    $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock, imagen_url) VALUES (:nombre, :descripcion, :precio, :categoria_id, :stock, :imagen_url)";
                    $stmt = $pdo->prepare($sql);
                } else { // 'edit'
                    $product_id = (int)$_POST['product_id'];
                    $sql = "UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, categoria_id = :categoria_id, stock = :stock, imagen_url = :imagen_url WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                }

                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':categoria_id', $categoria_id, $categoria_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
                $stmt->bindParam(':imagen_url', $imagen_url);
                $stmt->execute();

                $_SESSION['success_message'] = "Producto " . ($action == 'add' ? 'agregado' : 'actualizado') . " exitosamente.";
                redirect(SITE_URL . 'admin/index.php');

            } catch (PDOException $e) {
                $_SESSION['error_message'] = "Error al guardar el producto: " . $e->getMessage(); // Para desarrollo
                // $_SESSION['error_message'] = "Error al guardar el producto. Inténtalo de nuevo."; // Para producción
                $_SESSION['form_data'] = $_POST; // Guardar datos del form para rellenar
                $_SESSION['form_errors'] = $errors; // No hay errores de validación aquí, es error de BD
                 redirect(SITE_URL . 'admin/product_form.php' . ($action == 'edit' ? '?edit_id=' . $product_id : ''));
            }
        } else {
            // Si hay errores de validación
            $_SESSION['error_message'] = "Por favor, corrige los errores.";
            $_SESSION['form_data'] = $_POST;
            $_SESSION['form_errors'] = $errors;
            redirect(SITE_URL . 'admin/product_form.php' . ($action == 'edit' ? '?edit_id=' . $_POST['product_id'] : ''));
        }
    }
    // ---- ACCIÓN ELIMINAR ----
    elseif ($action == 'delete' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        try {
            // Opcional: borrar imagen del servidor
            $stmt_img = $pdo->prepare("SELECT imagen_url FROM productos WHERE id = :id");
            $stmt_img->execute(['id' => $product_id]);
            $img_to_delete = $stmt_img->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->execute(['id' => $product_id]);

            if ($stmt->rowCount() > 0) {
                if ($img_to_delete && $img_to_delete != 'default_product.jpg' && file_exists(UPLOADS_PATH . $img_to_delete)) {
                    unlink(UPLOADS_PATH . $img_to_delete);
                }
                $_SESSION['success_message'] = "Producto eliminado exitosamente.";
            } else {
                $_SESSION['error_message'] = "No se pudo eliminar el producto o ya no existía.";
            }
        } catch (PDOException $e) {
            // Manejar error si el producto está en una orden_item y hay restricción FK
            if ($e->getCode() == '23000') { // Código de error para violación de integridad
                 $_SESSION['error_message'] = "No se puede eliminar el producto porque está asociado a órdenes existentes.";
            } else {
                $_SESSION['error_message'] = "Error al eliminar el producto: " . $e->getMessage();
            }
        }
        redirect(SITE_URL . 'admin/index.php');
    }
} else {
    redirect(SITE_URL . 'admin/index.php'); // Redirigir si no hay acción válida
}
?>