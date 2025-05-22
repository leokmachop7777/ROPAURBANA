<?php
require_once 'includes/config.php';

// Obtener el ID del producto de la URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    // Redirigir si no hay ID válido
    header("Location: " . SITE_URL . "index.php");
    exit();
}

// Obtener los detalles del producto de la base de datos
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    // Redirigir si el producto no existe
    header("Location: " . SITE_URL . "index.php");
    exit();
}

// Aquí podrías también obtener las imágenes adicionales del producto si las tienes en otra tabla

require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($product['nombre']); ?> - Tu Tienda Urbana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <img src="<?php echo UPLOADS_URL . html_escape($product['imagen_url']); ?>" alt="<?php echo html_escape($product['nombre']); ?>" class="img-fluid rounded main-image">
                </div>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <?php
                    $additional_image_url = pathinfo($product['imagen_url'], PATHINFO_FILENAME) . '_' . $i . '.' . pathinfo($product['imagen_url'], PATHINFO_EXTENSION);
                    $full_additional_image_path = $_SERVER['DOCUMENT_ROOT'] . '/tu-tienda-urbana/uploads/' . $additional_image_url;
                    if (file_exists($full_additional_image_path)):
                    ?>
                        <div class="col-md-4 mb-3">
                            <img src="<?php echo UPLOADS_URL . html_escape($additional_image_url); ?>" alt="<?php echo html_escape($product['nombre']) . ' - Vista ' . $i; ?>" class="img-fluid rounded small-image">
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="mb-3"><?php echo html_escape($product['nombre']); ?></h1>
            <p class="price h4 mb-4">$<?php echo html_escape(number_format($product['precio'], 0, ',', '.')); ?></p>
            <p class="mb-4"><?php echo nl2br(html_escape($product['descripcion'])); ?></p>

            <div class="mb-3">
                <label for="talla" class="form-label">Talla:</label>
                <select class="form-select" id="talla">
                    <option value="" disabled selected>Selecciona una talla</option>
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                </select>
                <div class="invalid-feedback">Por favor, selecciona una talla.</div>
            </div>

            <form id="add-to-cart-form-detail" action="<?php echo SITE_URL; ?>cart_options.php" method="post">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad:</label>
                    <input type="number" class="form-control" id="cantidad" name="quantity" value="1" min="1">
                </div>
                <button type="submit" class="btn btn-accent">AGREGAR AL CARRITO</button>
                <div id="add-to-cart-message" class="mt-3"></div>
            </form>
        </div>
    </div>
</div>

<style>
.small-image {
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}
.small-image:hover {
    opacity: 1;
}
.main-image {
    width: 100%; /* Asegura que la imagen principal ocupe todo el ancho del contenedor */
    display: block; /* Evita espacio extra debajo de la imagen */
    margin-bottom: 15px; /* Espacio entre la imagen principal y las miniaturas */
}
</style>

<script>
$(document).ready(function() {
    $('.small-image').on('click', function() {
        var newImageSrc = $(this).attr('src');
        $('.main-image').attr('src', newImageSrc);
    });

    $('#add-to-cart-form-detail').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var tallaSeleccionada = $('#talla').val();

        if (!tallaSeleccionada) {
            $('#talla').addClass('is-invalid');
            return;
        } else {
            $('#talla').removeClass('is-invalid');
        }

        var formData = form.serialize() + '&talla=' + tallaSeleccionada; // Incluir la talla

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#add-to-cart-message').html('<div class="alert alert-success">' + (response.message || 'Producto agregado al carrito') + '</div>').fadeIn();
                    if ($('#cart-count').length) {
                        $('#cart-count').text(response.cart_item_count);
                    }
                    setTimeout(function() {
                        $('#add-to-cart-message').fadeOut('slow', function() {
                            $(this).empty();
                        });
                    }, 3000);
                } else {
                    $('#add-to-cart-message').html('<div class="alert alert-danger">' + (response.message || 'Error al agregar al carrito') + '</div>').fadeIn();
                }
            },
            error: function() {
                $('#add-to-cart-message').html('<div class="alert alert-danger">Error de conexión.</div>').fadeIn();
            }
        });
    });

    $('#talla').on('change', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>