<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta</title>
    <link rel="stylesheet" href="build/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    include 'db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT nombre_usuario FROM usuarios WHERE id_usuario = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    ?>
    <header class="header">
        <div class="logo">
            <a href="index.php">
                <img src="src/img/gallery/full/logo.jpg" alt="Logo del Festival" />
            </a>
        </div>

        <div class="contenedor contenido-header">
            <h1>Santiago Tickets</h1>

        </div>
        <div class="micuenta">
            <a href="micuenta.php"><button>Mi Cuenta</button></a>
        </div>
    </header>

    <div class="contenido-micuenta">
        <h2>Hola, <?php echo htmlspecialchars($user['nombre_usuario']); ?></h2>
        <form action="micuenta.php" method="POST">
            <button type="submit" name="logout">Cerrar Sesión</button>
        </form>

        <div class="mis-boletos">
            <h3>Mis Boletos</h3>
            <button id="btn-pasados">Eventos Pasados</button>
            <button id="btn-hoy">Eventos Hoy</button>
            <button id="btn-proximos">Fechas Próximas</button>
            <div id="tickets-container"></div>
        </div>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>