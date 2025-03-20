<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="build/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="src/img/gallery/full/logo.jpg" alt="Logo del Festival" />
            </a>
        </div>
        <div class="contenedor contenido-header">
            <h1>Santiago Tickets</h1>
        </div>
    </header>

    <div class="login-container">
        <h2>Inicio de Sesión</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include 'db_connect.php';

            $username = $_POST['username'];
            $password = $_POST['password'];

            $sql = "SELECT * FROM usuarios WHERE (nombre_usuario = ? OR correo = ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Iniciar sesión
                session_start();
                $_SESSION['user_id'] = $user['id_usuario'];
                $redirect = 'index.php';
                if (isset($_GET['redirect'])) {
                    $redirect = filter_var($_GET['redirect'], FILTER_SANITIZE_URL);
                    if (isset($_GET['id_evento']) && isset($_GET['id_fecha'])) {
                        $redirect .= (strpos($redirect, '?') === false ? '?' : '&') . 'id_evento=' . urlencode($_GET['id_evento']) . '&id_fecha=' . urlencode($_GET['id_fecha']);
                    }
                }
                echo '<p>Redirigiendo a: ' . htmlspecialchars($redirect) . '</p>';
                header("Refresh: 3; URL=$redirect");
                exit();
            } else {
                echo '<p class="error">Usuario o contraseña incorrectos.</p>';
            }
        }
        ?>
        <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) . (isset($_GET['id_evento']) ? '&id_evento=' . urlencode($_GET['id_evento']) : '') . (isset($_GET['id_fecha']) ? '&id_fecha=' . urlencode($_GET['id_fecha']) : '') : ''; ?>" method="POST">
            <label for="username">Correo o Usuario:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Iniciar Sesión</button>
        </form>
        <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>