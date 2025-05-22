<?php
require_once 'includes/config.php';
$errors = [];
$email = '';

// Si ya está logueado, redirigir
if (is_logged_in()) {
    redirect('user_panel.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email)) $errors[] = "El email es requerido.";
    if (empty($password)) $errors[] = "La contraseña es requerida.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre_completo, email, password_hash, es_admin FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre_completo'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = (bool)$user['es_admin'];

                $_SESSION['success_message'] = "¡Bienvenido de nuevo, " . html_escape($user['nombre_completo']) . "!";

                // Redirigir a la página anterior si existe, o al panel de usuario/admin
                if ($user['es_admin']) {
                    redirect(SITE_URL . 'admin/index.php');
                } elseif (isset($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    redirect($redirect_url);
                } else {
                    redirect('user_panel.php');
                }
            } else {
                $errors[] = "Correo electrónico o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            $errors[] = "Error al iniciar sesión. Inténtalo más tarde."; // Mensaje genérico
            error_log("Error login: " . $e->getMessage()); // Loguear el error real
        }
    }
}
require_once 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card form-container">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Iniciar Sesión</h2>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo html_escape($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo html_escape($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">Ingresar</button>
                </form>
                <p class="mt-3 text-center">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>