<?php
require_once '../includes/config.php';
require_admin();

$product = ['id' => null, 'nombre' => '', 'descripcion' => '', 'precio' => '', 'categoria_id' => null, 'stock' => 0, 'imagen_url' => ''];
$form_action = 'add';
$page_title = 'Agregar Nuevo Producto';
$errors = [];

// Cargar categorías para el select
$stmt_categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$categorias = $stmt_categorias->fetchAll();

if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->execute(['id' => $edit_id]);
    $product_data = $stmt->fetch();
    if ($product_data) {
        $product = $product_data;
        $form_action = 'edit';
        $page_title = 'Editar Producto: ' . html_escape($product['nombre']);
    } else {
        $_SESSION['error_message'] = "Producto no encontrado.";
        redirect(SITE_URL . 'admin/index.php');
    }
}

require_once '../includes/header.php';
?>
<div class="container my-5">
    <h1 class="section-title text-center mb-4"><?php echo $page_title; ?></h1>

    <?php if (!empty($errors)): /* Mostrar errores si vienen de product_actions.php vía sesión */ ?>
    <div class="alert alert-danger">
        <?php foreach ($_SESSION['form_errors'] ?? [] as $error): ?>
            <p class="mb-0"><?php echo html_escape($error); ?></p>
        <?php endforeach; unset($_SESSION['form_errors']); ?>
    </div>
    <?php endif; ?>

    <form action="product_actions.php" method="post" enctype="multipart/form-data" class="card p-4 form-container" style="max-width: 700px; margin:auto;">
        <input type="hidden" name="action" value="<?php echo $form_action; ?>">
        <?php if ($form_action == 'edit'): ?>
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="current_image_url" value="<?php echo html_escape($product['imagen_url']); ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo html_escape($product['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?php echo html_escape($product['descripcion']); ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="precio" class="form-label">Precio ($)</label>
                <input type="number" class="form-control" id="precio" name="precio" step="0.01" value="<?php echo html_escape($product['precio']); ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="stock" name="stock" value="<?php echo html_escape($product['stock']); ?>" required min="0">
            </div>
        </div>
        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoría</label>
            <select class="form-select" id="categoria_id" name="categoria_id">
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($product['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo html_escape($cat['nombre']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen del Producto</label>
            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/jpeg, image/png, image/webp">
            <?php if ($form_action == 'edit' && !empty($product['imagen_url']) && $product['imagen_url'] != 'default_product.jpg'): ?>
            <small class="form-text text-muted">Imagen actual: <?php echo html_escape($product['imagen_url']); ?>. Dejar vacío para no cambiar.</small><br>
            <img src="<?php echo UPLOADS_URL . html_escape($product['imagen_url']); ?>" alt="Imagen actual" style="max-width: 100px; margin-top: 10px;">
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-end">
            <a href="<?php echo SITE_URL; ?>admin/index.php" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?php echo ($form_action == 'edit') ? 'Actualizar' : 'Guardar'; ?> Producto</button>
        </div>
    </form>
</div>
<?php require_once '../includes/footer.php'; ?>