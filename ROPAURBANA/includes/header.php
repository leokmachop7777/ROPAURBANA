<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Tienda Urbana - SeBashard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css"> </head>
<body>
    <header>
        <div class="top-bar text-center py-2">
            <span>3 CUOTAS SIN INTERÉS</span> | <span>ENVÍOS GRATIS A PARTIR DE $150.000</span>
        </div>
        <nav class="navbar navbar-expand-lg navbar-dark main-navbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo SITE_URL; ?>">SEBASHARD</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>">HOME</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">SHOP ALL</a>
                            <ul class="dropdown-menu">
                                <?php
                                try {
                                    $stmt_cat = $pdo->query("SELECT nombre, slug FROM categorias ORDER BY nombre ASC");
                                    while ($cat = $stmt_cat->fetch()) {
                                        echo '<li><a class="dropdown-item" href="' . SITE_URL . 'index.php?category=' . html_escape($cat['slug']) . '">' . html_escape($cat['nombre']) . '</a></li>';
                                    }
                                } catch (PDOException $e) { /* Manejar error */ }
                                ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL . 'index.php'; ?>">VER TODO</a></li>
                            </ul>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>#faq-section">FAQ</a></li> <?php if (is_admin()): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>admin/">ADMIN</a></li>
                        <?php endif; ?>
                    </ul>
                    <div class="navbar-icons">
                        <a href="#" class="nav-icon" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></a>
                        <?php if (is_logged_in()): ?>
                            <a href="<?php echo SITE_URL; ?>user_panel.php" class="nav-icon" title="Mi Cuenta"><i class="fas fa-user"></i></a>
                            <a href="<?php echo SITE_URL; ?>logout.php" class="nav-icon" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i></a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>login.php" class="nav-icon" title="Iniciar Sesión"><i class="fas fa-user"></i></a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>cart.php" class="nav-icon" id="cartIcon" title="Carrito">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge bg-accent" id="cart-count">
                                <?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'cantidad')) : 0; ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main class="container mt-4">
        <?php
        // Mostrar mensajes de error o éxito de la sesión
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . html_escape($_SESSION['success_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . html_escape($_SESSION['error_message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message']);
        }
        ?>