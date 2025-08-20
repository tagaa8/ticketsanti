<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Santiago Tickets</title>
    <meta name="description" content="Gestiona tu cuenta y revisa tus boletos en Santiago Tickets">
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
        <div class="contenedor header-contenido">
            <div class="logo">
                <a href="index.php">
                    <img src="src/img/gallery/full/logo.jpg" alt="Santiago Tickets Logo" />
                    <span class="logo-text">Santiago Tickets</span>
                </a>
            </div>

            <nav class="nav-principal">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="index.php#eventos" class="nav-link">Eventos</a>
            </nav>

            <div class="user-actions">
                <a href="micuenta.php" class="btn btn-primary">Mi Cuenta</a>
            </div>
        </div>
    </header>

    <div class="micuenta-container">
        <div class="contenedor">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <span>Mi Cuenta</span>
            </nav>
            
            <!-- Header de usuario -->
            <div class="usuario-header">
                <div class="usuario-info">
                    <div class="usuario-avatar">
                        <span><?php echo strtoupper(substr($user['nombre_usuario'], 0, 1)); ?></span>
                    </div>
                    <div class="usuario-detalles">
                        <h1>Hola, <?php echo htmlspecialchars($user['nombre_usuario']); ?></h1>
                        <p>Gestiona tu cuenta y revisa tus boletos</p>
                    </div>
                </div>
                <div class="usuario-acciones">
                    <form action="micuenta.php" method="POST" style="display: inline;">
                        <button type="submit" name="logout" class="btn btn-outline">
                            😪 Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Sección de boletos -->
            <div class="tickets-container">
                <div class="tickets-header">
                    <h2>Mis Boletos</h2>
                    <p>Administra todos tus boletos desde un solo lugar</p>
                </div>
                
                <!-- Filtros de boletos -->
                <div class="tickets-filter">
                    <button id="btn-proximos" class="filter-btn active" data-type="proximos">
                        📅 Próximos Eventos
                    </button>
                    <button id="btn-hoy" class="filter-btn" data-type="hoy">
                        ⭐ Eventos Hoy
                    </button>
                    <button id="btn-pasados" class="filter-btn" data-type="pasados">
                        📋 Eventos Pasados
                    </button>
                </div>
                
                <!-- Contenedor de boletos -->
                <div id="tickets-container" class="tickets-content">
                    <div class="loading-tickets">
                        <div class="loading-spinner"></div>
                        <p>Cargando tus boletos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>