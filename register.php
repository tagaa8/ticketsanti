<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
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

    <div class="register-container">
        <h2>Registro</h2>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include 'db_connect.php';

            $nombre_usuario = $_POST['nombre_usuario'];
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $correo = $_POST['correo'];
            $password = $_POST['password'];

            // Validar la contraseña
            if (strlen($password) < 8 || !preg_match('/[0-9]/', $password)) {
                echo '<p class="error">La contraseña debe tener al menos 8 caracteres y contener al menos un número.</p>';
            } else {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (nombre_usuario, nombre, apellido, correo, password) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre_usuario, $nombre, $apellido, $correo, $password_hashed]);

                header('Location: login.php');
                exit;
            }
        }
        ?>
        <form action="register.php" method="POST" onsubmit="return validatePassword()">
            <label for="nombre_usuario">Nombre de Usuario:</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" required>

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>

            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required pattern="(?=.*\d).{8,}" title="Debe tener al menos 8 caracteres y contener al menos un número">

            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>