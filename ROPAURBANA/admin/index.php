<?php
require_once '../includes/config.php';
require_admin(); // Solo admins pueden acceder

$stmt = $pdo->query("SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.nombre ASC");
$products = $stmt->fetchAll();

require_once '../includes/header.php'; // Podrías tener un header_admin.php específico
?>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="section-title">Panel de Administración - Productos</h1>
        <a href="product_form.php" class="btn btn-success"><i class="fas fa-plus"></i> Agregar Producto</a>
    </div>

    <?php if (empty($products)): ?>
        <p>No hay productos para mostrar. ¡Agrega algunos!</p>
    <?php else: ?>
        <table class="table table-striped table-hover admin-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><img src="<?php echo UPLOADS_URL . html_escape($product['imagen_url']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover;"></td>
                    <td><?php echo html_escape($product['nombre']); ?></td>
                    <td><?php echo html_escape($product['categoria_nombre'] ?? 'N/A'); ?></td>
                    <td>$<?php echo html_escape(number_format($product['precio'], 0, ',', '.')); ?></td>
                    <td><?php echo $product['stock']; ?></td>
                    <td>
                        <a href="product_form.php?edit_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary" title="Editar"><i class="fas fa-edit"></i></a>
                        <form action="product_actions.php" method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>