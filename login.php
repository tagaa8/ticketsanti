<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Santiago Tickets</title>
    <meta name="description" content="Inicia sesión en Santiago Tickets para acceder a tu cuenta">
    <link rel="stylesheet" href="build/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <header class="header">
        <div class="contenedor header-contenido">
            <div class="logo">
                <a href="index.php">
                    <img src="src/img/gallery/full/logo.jpg" alt="Santiago Tickets Logo" />
                    <span class="logo-text">Santiago Tickets</span>
                </a>
            </div>
        </div>
    </header>

    <div class="login-container">
        <div class="form-card">
            <div class="form-header">
                <div class="logo">
                    <img src="src/img/gallery/full/logo.jpg" alt="Santiago Tickets" />
                </div>
                <h2>Iniciar Sesión</h2>
                <p>Accede a tu cuenta para gestionar tus boletos</p>
            </div>
            
            <?php
            $error_message = '';
            $success_message = '';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                include 'db_connect.php';

                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($username) || empty($password)) {
                    $error_message = 'Por favor, completa todos los campos.';
                } else {
                    try {
                        $sql = "SELECT id_usuario, nombre_usuario, correo, password, rol FROM usuarios WHERE (nombre_usuario = ? OR correo = ?) AND activo = 1";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$username, $username]);
                        $user = $stmt->fetch();

                        if ($user && password_verify($password, $user['password'])) {
                            session_start();
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $user['id_usuario'];
                            $_SESSION['username'] = $user['nombre_usuario'];
                            $_SESSION['rol'] = $user['rol'];
                            
                            // Actualizar último acceso
                            $sql_update = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?";
                            $stmt_update = $pdo->prepare($sql_update);
                            $stmt_update->execute([$user['id_usuario']]);
                            
                            $redirect = 'index.php';
                            if (isset($_GET['redirect'])) {
                                $redirect = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
                                if (isset($_GET['id_evento']) && isset($_GET['id_fecha'])) {
                                    $redirect .= (strpos($redirect, '?') === false ? '?' : '&') . 'id_evento=' . urlencode($_GET['id_evento']) . '&id_fecha=' . urlencode($_GET['id_fecha']);
                                }
                            }
                            
                            $success_message = 'Inicio de sesión exitoso. Redirigiendo...';
                            header("Refresh: 2; URL=$redirect");
                        } else {
                            $error_message = 'Credenciales incorrectas. Verifica tu usuario y contraseña.';
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Error del sistema. Por favor, intenta más tarde.';
                        error_log("Error en login.php: " . $e->getMessage());
                    }
                }
            }
            
            if ($error_message): ?>
                <div class="form-group">
                    <div class="form-error">
                        <span class="error-icon">⚠️</span>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="form-group">
                    <div class="form-success">
                        <span class="success-icon">✅</span>
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) . (isset($_GET['id_evento']) ? '&id_evento=' . urlencode($_GET['id_evento']) : '') . (isset($_GET['id_fecha']) ? '&id_fecha=' . urlencode($_GET['id_fecha']) : '') : ''; ?>" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Correo Electrónico o Usuario <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">👤</span>
                        <input type="text" id="username" name="username" required 
                               placeholder="usuario@ejemplo.com" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" required placeholder="Tu contraseña">
                        <button type="button" class="input-action" onclick="togglePassword()" aria-label="Mostrar/ocultar contraseña">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Iniciar Sesión</button>
                </div>
            </form>
            
            <div class="form-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
    </script>
</body>

</html>