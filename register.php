<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Santiago Tickets</title>
    <meta name="description" content="Crea tu cuenta en Santiago Tickets y disfruta de los mejores eventos">
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

    <div class="register-container">
        <div class="form-card">
            <div class="form-header">
                <div class="logo">
                    <img src="src/img/gallery/full/logo.jpg" alt="Santiago Tickets" />
                </div>
                <h2>Crear Cuenta</h2>
                <p>Regístrate para acceder a los mejores eventos</p>
            </div>
            
            <?php
            $error_message = '';
            $success_message = '';
            $form_data = [];
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                include 'db_connect.php';

                // Sanitizar y validar datos
                $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
                $nombre = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                $correo = trim($_POST['correo'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                // Guardar datos para repoblar el formulario
                $form_data = [
                    'nombre_usuario' => $nombre_usuario,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'correo' => $correo
                ];

                // Validaciones
                if (empty($nombre_usuario) || empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
                    $error_message = 'Todos los campos son obligatorios.';
                } elseif (strlen($nombre_usuario) < 3) {
                    $error_message = 'El nombre de usuario debe tener al menos 3 caracteres.';
                } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $error_message = 'El correo electrónico no es válido.';
                } elseif (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Za-z]/', $password)) {
                    $error_message = 'La contraseña debe tener al menos 8 caracteres, incluir letras y números.';
                } elseif ($password !== $confirm_password) {
                    $error_message = 'Las contraseñas no coinciden.';
                } else {
                    try {
                        // Verificar si el usuario o correo ya existen
                        $sql_check = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? OR correo = ?";
                        $stmt_check = $pdo->prepare($sql_check);
                        $stmt_check->execute([$nombre_usuario, $correo]);
                        
                        if ($stmt_check->fetch()) {
                            $error_message = 'El nombre de usuario o correo ya están registrados.';
                        } else {
                            $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                            $sql = "INSERT INTO usuarios (nombre_usuario, nombre, apellido, correo, password, fecha_registro, activo) VALUES (?, ?, ?, ?, ?, NOW(), 1)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$nombre_usuario, $nombre, $apellido, $correo, $password_hashed]);

                            $success_message = 'Cuenta creada exitosamente. Redirigiendo al inicio de sesión...';
                            header('Refresh: 3; URL=login.php');
                        }
                    } catch (PDOException $e) {
                        $error_message = 'Error del sistema. Por favor, intenta más tarde.';
                        error_log("Error en register.php: " . $e->getMessage());
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
            
            <?php if (!$success_message): ?>
            <form action="register.php" method="POST" id="registerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required 
                               placeholder="Tu nombre" 
                               value="<?php echo htmlspecialchars($form_data['nombre'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="apellido">Apellido <span class="required">*</span></label>
                        <input type="text" id="apellido" name="apellido" required 
                               placeholder="Tu apellido" 
                               value="<?php echo htmlspecialchars($form_data['apellido'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">👤</span>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required 
                               placeholder="Ej: usuario123" minlength="3"
                               value="<?php echo htmlspecialchars($form_data['nombre_usuario'] ?? ''); ?>">
                    </div>
                    <div class="form-help">Mínimo 3 caracteres, solo letras, números y guiones bajos</div>
                </div>

                <div class="form-group">
                    <label for="correo">Correo Electrónico <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">📧</span>
                        <input type="email" id="correo" name="correo" required 
                               placeholder="usuario@ejemplo.com"
                               value="<?php echo htmlspecialchars($form_data['correo'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" required 
                               placeholder="Tu contraseña" minlength="8"
                               pattern="(?=.*\d)(?=.*[a-zA-Z]).{8,}">
                        <button type="button" class="input-action" onclick="togglePassword('password')" aria-label="Mostrar/ocultar contraseña">
                            👁️
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Ingresa una contraseña</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirma tu contraseña" minlength="8">
                        <button type="button" class="input-action" onclick="togglePassword('confirm_password')" aria-label="Mostrar/ocultar contraseña">
                            👁️
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Crear Cuenta</button>
                </div>
            </form>
            <?php endif; ?>
            
            <div class="form-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
        
        // Validación de contraseña en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            if (passwordField && strengthFill && strengthText) {
                passwordField.addEventListener('input', function() {
                    const password = this.value;
                    const strength = calculatePasswordStrength(password);
                    
                    strengthFill.className = 'strength-fill ' + strength.class;
                    strengthText.className = 'strength-text ' + strength.class;
                    strengthText.textContent = strength.text;
                });
            }
        });
        
        function calculatePasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            switch (score) {
                case 0:
                case 1:
                    return { class: 'weak', text: 'Muy débil' };
                case 2:
                    return { class: 'fair', text: 'Débil' };
                case 3:
                case 4:
                    return { class: 'good', text: 'Buena' };
                case 5:
                    return { class: 'strong', text: 'Muy fuerte' };
                default:
                    return { class: 'weak', text: 'Muy débil' };
            }
        }
    </script>
</body>

</html>