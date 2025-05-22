<?php
require_once 'includes/config.php';
$errors = [];
$nombre_completo = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validaciones
    if (empty($nombre_completo)) $errors[] = "El nombre completo es requerido.";
    if (empty($email)) {
        $errors[] = "El email es requerido.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El formato del email no es válido.";
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Este correo electrónico ya está registrado.";
        }
    }
    if (empty($password)) {
        $errors[] = "La contraseña es requerida.";
    } elseif (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Las contraseñas no coinciden.";
    }
    // Podrías agregar más validaciones para la contraseña (mayúsculas, números, etc.)

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, password_hash) VALUES (:nombre, :email, :password_hash)");
            $stmt->execute(['nombre' => $nombre_completo, 'email' => $email, 'password_hash' => $password_hash]);
            $_SESSION['success_message'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            redirect('login.php');
        } catch (PDOException $e) {
            $errors[] = "Error al registrar el usuario: " . $e->getMessage(); // Para desarrollo, en prod un mensaje genérico
        }
    }
}
require_once 'includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card form-container">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Crear Cuenta</h2>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?php echo html_escape($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="<?php echo html_escape($nombre_completo); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo html_escape($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <button type="submit" class="btn btn-accent w-100">Registrarse</button>
                </form>
                <p class="mt-3 text-center">¿Ya tienes cuenta? <a href="login.php">Inicia Sesión</a></p>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>