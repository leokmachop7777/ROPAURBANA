<?php

// Asegurarse de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Función para forzar el inicio de sesión si el usuario no está logueado
function require_login() {
    if (!is_logged_in()) {
        // Guardar la URL actual para redirigir después del login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
}

// Función para verificar si el usuario es administrador
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Función para requerir privilegios de administrador
function require_admin() {
    if (!is_admin()) {
        // Puedes redirigir a una página de error o simplemente detener la ejecución
        header('Location: ' . SITE_URL . 'user_panel.php?error=no_admin');
        exit();
    }
}

?>