<?php
require_once 'includes/config.php';
require_login(); // Requiere que el usuario esté logueado

$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    $_SESSION['error_message'] = "Tu carrito está vacío. No puedes proceder al checkout.";
    redirect(SITE_URL . 'cart.php');
}

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_order'])) {
    // Aquí procesarías el pago (simulación) y guardarías la orden
    $nombre_envio = trim($_POST['nombre_envio']);
    $direccion_envio = trim($_POST['direccion_envio']);
    // ... otros campos del formulario de envío

    // Validación básica
    if (empty($nombre_envio) || empty($direccion_envio)) {
        $_SESSION['error_message'] = "Por favor, completa todos los campos de envío.";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Insertar en la tabla 'ordenes'
            $stmt_orden = $pdo->prepare("INSERT INTO ordenes (usuario_id, total_pedido, nombre_envio, direccion_envio, estado) VALUES (:usuario_id, :total_pedido, :nombre_envio, :direccion_envio, 'Procesando')");
            $stmt_orden->execute([
                ':usuario_id' => $_SESSION['user_id'],
                ':total_pedido' => $subtotal,
                ':nombre_envio' => $nombre_envio,
                ':direccion_envio' => $direccion_envio
            ]);
            $orden_id = $pdo->lastInsertId();

            // 2. Insertar en 'orden_items' y actualizar stock
            $stmt_item = $pdo->prepare("INSERT INTO orden_items (orden_id, producto_id, cantidad, precio_unitario) VALUES (:orden_id, :producto_id, :cantidad, :precio_unitario)");
            $stmt_update_stock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id AND stock >= :cantidad_actual_stock"); // Importante: AND stock >= cantidad

            foreach ($cart_items as $item) {
                $stmt_item->execute([
                    ':orden_id' => $orden_id,
                    ':producto_id' => $item['id'],
                    ':cantidad' => $item['cantidad'],
                    ':precio_unitario' => $item['precio']
                ]);

                $update_stock_result = $stmt_update_stock->execute([
                    ':cantidad' => $item['cantidad'],
                    ':producto_id' => $item['id'],
                    ':cantidad_actual_stock' => $item['cantidad'] // Para la condición
                ]);

                if ($stmt_update_stock->rowCount() == 0) {
                    // No se pudo actualizar el stock (stock insuficiente)
                    throw new Exception("Stock insuficiente para el producto: " . html_escape($item['nombre']));
                }
            }

            $pdo->commit();

            // 3. Limpiar carrito
            $_SESSION['cart'] = [];
            $_SESSION['success_message'] = "¡Gracias por tu compra! Tu pedido #${orden_id} ha sido procesado.";
            redirect(SITE_URL . 'user_panel.php?order_success=' . $orden_id);

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Error al procesar el pedido: " . $e->getMessage();
            // No redirigir para que el usuario pueda ver el error y reintentar o corregir
        }
    }
}

require_once 'includes/header.php';
?>
<div class="container my-5">
    <h1 class="section-title text-center mb-4">FINALIZAR COMPRA</h1>
    <div class="row">
        <div class="col-md-7">
            <h4>Detalles de Envío</h4>
            <form action="checkout.php" method="post" id="checkout-form">
                <div class="mb-3">
                    <label for="nombre_envio" class="form-label">Nombre Completo (Receptor)</label>
                    <input type="text" class="form-control" id="nombre_envio" name="nombre_envio" value="<?php echo html_escape($_SESSION['user_nombre'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="direccion_envio" class="form-label">Dirección de Envío</label>
                    <textarea class="form-control" id="direccion_envio" name="direccion_envio" rows="3" required></textarea>
                </div>
                <hr>
                <h4>Método de Pago (Simulación)</h4>
                <p class="text-muted">Este es un checkout simulado. No se procesarán pagos reales.</p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="simulated_card" value="card" checked>
                    <label class="form-check-label" for="simulated_card">
                        Tarjeta de Crédito/Débito (Simulada)
                    </label>
                </div>
                 <button type="submit" name="confirm_order" class="btn btn-accent btn-lg w-100 mt-4">Confirmar y Pagar (Simulado)</button>
            </form>
        </div>
        <div class="col-md-5">
            <div class="order-summary p-3" style="background-color: var(--color-surface); border-radius: 5px;">
                <h4>Resumen de tu Pedido</h4>
                <?php foreach ($cart_items as $item): ?>
                <div class="d-flex justify-content-between my-2 border-bottom pb-2">
                    <div>
                        <img src="<?php echo UPLOADS_URL . html_escape($item['imagen_url']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 4px;">
                        <?php echo html_escape($item['nombre']); ?> (x<?php echo $item['cantidad']; ?>)
                    </div>
                    <span>$<?php echo html_escape(number_format($item['precio'] * $item['cantidad'], 0, ',', '.')); ?></span>
                </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between fw-bold mt-3">
                    <h5>TOTAL:</h5>
                    <h5>$<?php echo html_escape(number_format($subtotal, 0, ',', '.')); ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>