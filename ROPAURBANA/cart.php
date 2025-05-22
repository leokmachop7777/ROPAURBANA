<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$cart_items = $_SESSION['cart'] ?? [];
$subtotal = 0;
?>

<div class="container my-5">
    <h1 class="section-title text-center mb-4">TU CARRITO</h1>

    <?php if (empty($cart_items)): ?>
        <p class="text-center lead">Tu carrito está vacío.</p>
        <div class="text-center">
            <a href="<?php echo SITE_URL; ?>" class="btn btn-accent btn-lg">Seguir Comprando</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table cart-table">
                <thead>
                    <tr>
                        <th colspan="2">Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item):
                        $item_total = $item['precio'] * $item['cantidad'];
                        $subtotal += $item_total;
                    ?>
                    <tr>
                        <td style="width: 100px;">
                            <img src="<?php echo UPLOADS_URL . html_escape($item['imagen_url']); ?>" alt="<?php echo html_escape($item['nombre']); ?>" class="img-fluid rounded" style="max-height: 75px;">
                        </td>
                        <td><?php echo html_escape($item['nombre']); ?></td>
                        <td>$<?php echo html_escape(number_format($item['precio'], 0, ',', '.')); ?></td>
                        <td>
                            <form action="<?php echo SITE_URL; ?>cart_actions.php" method="post" class="d-inline-flex align-items-center update-quantity-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['cantidad']; ?>" min="1" class="form-control form-control-sm" style="width: 70px;">
                                <button type="submit" class="btn btn-sm btn-outline-light ms-1" title="Actualizar"><i class="fas fa-sync-alt"></i></button>
                            </form>
                        </td>
                        <td>$<?php echo html_escape(number_format($item_total, 0, ',', '.')); ?></td>
                        <td>
                            <form action="<?php echo SITE_URL; ?>cart_actions.php" method="post" class="d-inline remove-item-form">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <div class="cart-summary p-3" style="background-color: var(--color-surface); border-radius: 5px;">
                    <h4 class="mb-3">Resumen del Pedido</h4>
                    <div class="d-flex justify-content-between">
                        <h5>SUBTOTAL:</h5>
                        <h5>$<?php echo html_escape(number_format($subtotal, 0, ',', '.')); ?></h5>
                    </div>
                    <p class="text-muted small">El envío e impuestos se calculan en el checkout.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo SITE_URL; ?>checkout.php" class="btn btn-accent btn-lg">FINALIZAR COMPRA</a>
                        <form action="<?php echo SITE_URL; ?>cart_actions.php" method="post">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger w-100 mt-2" onclick="return confirm('¿Estás seguro de que quieres vaciar el carrito?');">Vaciar Carrito</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>