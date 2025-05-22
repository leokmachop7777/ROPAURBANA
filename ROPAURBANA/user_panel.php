<?php
require_once 'includes/config.php';
require_login(); // Asegura que el usuario esté logueado
require_once 'includes/header.php';

$user_id = $_SESSION['user_id'];
$stmt_orders = $pdo->prepare("SELECT * FROM ordenes WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC");
$stmt_orders->execute([':usuario_id' => $user_id]);
$orders = $stmt_orders->fetchAll();
?>
<div class="container my-5">
    <h1 class="section-title text-center mb-4">Mi Panel</h1>
    <h3 class="mb-3">Hola, <?php echo html_escape($_SESSION['user_nombre']); ?>!</h3>

    <h4>Historial de Compras</h4>
    <?php if (empty($orders)): ?>
        <p>Aún no has realizado ninguna compra.</p>
    <?php else: ?>
        <div class="accordion" id="ordersAccordion">
            <?php foreach ($orders as $index => $order): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $order['id']; ?>">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $order['id']; ?>" aria-expanded="<?php echo $index == 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $order['id']; ?>">
                            Pedido #<?php echo $order['id']; ?> - Fecha: <?php echo date("d/m/Y H:i", strtotime($order['fecha_creacion'])); ?> - Total: $<?php echo html_escape(number_format($order['total_pedido'], 0, ',', '.')); ?> - Estado: <?php echo html_escape($order['estado']); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $order['id']; ?>" class="accordion-collapse collapse <?php echo $index == 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $order['id']; ?>" data-bs-parent="#ordersAccordion">
                        <div class="accordion-body">
                            <h5>Detalles del Pedido:</h5>
                            <p><strong>Enviado a:</strong> <?php echo html_escape($order['nombre_envio']); ?><br>
                               <strong>Dirección:</strong> <?php echo html_escape($order['direccion_envio']); ?></p>
                            <table class="table table-sm">
                                <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>
                                <tbody>
                                <?php
                                $stmt_items = $pdo->prepare("
                                    SELECT oi.*, p.nombre as producto_nombre
                                    FROM orden_items oi
                                    JOIN productos p ON oi.producto_id = p.id
                                    WHERE oi.orden_id = :orden_id
                                ");
                                $stmt_items->execute([':orden_id' => $order['id']]);
                                $items = $stmt_items->fetchAll();
                                foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo html_escape($item['producto_nombre']); ?></td>
                                        <td><?php echo $item['cantidad']; ?></td>
                                        <td>$<?php echo html_escape(number_format($item['precio_unitario'], 0, ',', '.')); ?></td>
                                        <td>$<?php echo html_escape(number_format($item['precio_unitario'] * $item['cantidad'], 0, ',', '.')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
<?php require_once 'includes/footer.php'; ?>