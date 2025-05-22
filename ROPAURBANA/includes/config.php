<?php
// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Usuario por defecto en WAMP/XAMPP
define('DB_PASSWORD', '');     // Contraseña por defecto en WAMP/XAMPP
define('DB_NAME', 'tu_tienda_urbana_db'); // Asegúrate que coincida con tu BD

// --- CONFIGURACIÓN DEL SITIO ---
define('SITE_URL', 'http://localhost/ROPAURBANA/'); // Cambia si tu proyecto está en otra ruta
define('UPLOADS_PATH', $_SERVER['DOCUMENT_ROOT'] . '/http://localhost/ROPAURBANA/'); // Ruta física para subidas
define('UPLOADS_URL', SITE_URL . 'uploads/'); // URL para acceder a las imágenes

// --- INICIAR SESIÓN ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- CONEXIÓN PDO A LA BASE DE DATOS ---
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Para obtener arrays asociativos por defecto
} catch(PDOException $e) {
    // En un entorno de producción, loguear el error y mostrar un mensaje genérico.
    error_log("Error de conexión a BD: " . $e->getMessage());
    die("Lo sentimos, estamos experimentando problemas técnicos. Por favor, inténtalo más tarde.");
}

// --- FUNCIONES BÁSICAS DE AYUDA (Puedes moverlas a functions.php si crece mucho) ---

/**
 * Escapa HTML para prevenir XSS.
 * @param string $string
 * @return string
 */
function html_escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Redirige a otra página.
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Verifica si el usuario ha iniciado sesión.
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Verifica si el usuario logueado es administrador.
 * @return bool
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Requiere que el usuario haya iniciado sesión.
 * @param string $redirect_url URL a la que redirigir si no está logueado.
 */
function require_login($redirect_url = 'login.php') {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI']; // Guardar página actual para redirigir después del login
        $_SESSION['error_message'] = "Debes iniciar sesión para acceder a esta página.";
        redirect($redirect_url . '?referer=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Requiere que el usuario sea administrador.
 * @param string $redirect_url URL a la que redirigir si no es admin.
 */
function require_admin($redirect_url = SITE_URL . 'index.php') {
    if (!is_admin()) {
        $_SESSION['error_message'] = "Acceso denegado. No tienes permisos de administrador.";
        redirect($redirect_url);
    }
}

// Configurar reporte de errores para desarrollo (comentar o cambiar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>