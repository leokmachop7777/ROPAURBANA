<?php
require_once 'includes/config.php'; // Siempre primero

// Lógica para obtener productos
$search_term = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE 1=1"; // 1=1 para facilitar añadir condiciones

$params = [];

if (!empty($search_term)) {
    $sql .= " AND (p.nombre LIKE :search_term OR p.descripcion LIKE :search_term)";
    $params[':search_term'] = '%' . $search_term . '%';
}

if (!empty($category_slug)) {
    $sql_cat_id = "SELECT id FROM categorias WHERE slug = :category_slug";
    $stmt_cat_id = $pdo->prepare($sql_cat_id);
    $stmt_cat_id->execute([':category_slug' => $category_slug]);
    $cat_id_result = $stmt_cat_id->fetch();
    if ($cat_id_result) {
        $sql .= " AND p.categoria_id = :category_id";
        $params[':category_id'] = $cat_id_result['id'];
    }
}

$sql .= " ORDER BY p.fecha_creacion DESC";
// Podrías agregar paginación aquí

$stmt_products = $pdo->prepare($sql);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll();

// Incluir el header
require_once 'includes/header.php';
?>

<section id="hero-carousel" class="container-fluid p-0 mb-5">
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="<?php echo SITE_URL; ?>assets/images/placeholder-carousel-1.jpg" class="d-block w-100" alt="Promo 1">
                <div class="carousel-caption d-none d-md-block"><h5>NUEVA COLECCIÓN</h5></div>
            </div>
            </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
</section>

<section id="featured-products" class="container mt-5">
    <h2 class="section-title text-center mb-4">
        <?php
        if (!empty($category_slug) && $cat_id_result) {
            $stmt_cat_name = $pdo->prepare("SELECT nombre FROM categorias WHERE id = :id");
            $stmt_cat_name->execute([':id' => $cat_id_result['id']]);
            $cat_name = $stmt_cat_name->fetchColumn();
            echo html_escape(strtoupper($cat_name));
        } elseif (!empty($search_term)) {
            echo 'Resultados para: "' . html_escape($search_term) . '"';
        } else {
            echo 'PRODUCTOS DESTACADOS';
        }
        ?>
    </h2>

    <?php if (empty($products)): ?>
        <p class="text-center">No se encontraron productos<?php echo !empty($category_slug) ? ' en esta categoría.' : (!empty($search_term) ? ' para tu búsqueda.' : '.'); ?></p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card">
                        <a href="<?php echo SITE_URL . 'product_detail.php?id=' . $product['id']; ?>">
                            <img src="<?php echo UPLOADS_URL . html_escape($product['imagen_url']); ?>" class="card-img-top" alt="<?php echo html_escape($product['nombre']); ?>">
                        </a>
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <a href="<?php echo SITE_URL . 'product_detail.php?id=' . $product['id']; ?>" class="text-decoration-none text-light">
                                    <?php echo html_escape($product['nombre']); ?>
                                </a>
                            </h5>
                            <p class="card-text price">$<?php echo html_escape(number_format($product['precio'], 0, ',', '.')); ?></p>
                            <form action="<?php echo SITE_URL; ?>cart_options.php" method="post" class="add-to-cart-form-ajax">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-accent">AGREGAR AL CARRITO</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section id="categories" class="container mt-5 mb-5">
    </section>

<?php
// Incluir el footer
require_once 'includes/footer.php';
?>