<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Evento</title>
    <link rel="stylesheet" href="build/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
    include 'db_connect.php';

    // Obtener el ID del evento desde la URL
    $id_evento = isset($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;

    // Obtener los detalles del evento
    $sql_evento = "SELECT * FROM Evento WHERE id_evento = ?";
    $stmt_evento = $pdo->prepare($sql_evento);
    $stmt_evento->execute([$id_evento]);
    $evento = $stmt_evento->fetch();

    if ($evento) {
        // Obtener las fechas disponibles para el evento
        $sql_fechas = "SELECT Fecha.id_fecha, Fecha.fecha, Fecha.hora, Estadios.nombre_estadio, Estadios.foto_estadio, Estadios.ubicacion_estadio
                       FROM Fecha
                       JOIN Estadios ON Fecha.id_estadio = Estadios.id_estadio
                       WHERE Fecha.id_evento = ?";
        $stmt_fechas = $pdo->prepare($sql_fechas);
        $stmt_fechas->execute([$id_evento]);
        $fechas = $stmt_fechas->fetchAll();
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
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="micuenta.php"><button>Mi Cuenta</button></a>
                <?php } else { ?>
                    <a href="login.php?redirect=boletos.php"><button>Iniciar Sesión</button></a>
                <?php } ?>
            </div>
        </header>

        <div class="video">
            <div class="overlay">
                <div class="contenedor contenido-video">
                    <h2><?php echo htmlspecialchars($evento['nombre_evento']); ?></h2>
                    <p><?php echo nl2br(htmlspecialchars($evento['descripcion_evento'])); ?></p>
                </div>
            </div>
            <img src="<?php echo htmlspecialchars($evento['foto']); ?>" alt="<?php echo htmlspecialchars($evento['nombre_evento']); ?>">
        </div>

        <div class="evento-detalle">
            <h3>Fechas Disponibles</h3>
            <?php if ($fechas) { ?>
                <ul class="fechas-lista">
                    <?php foreach ($fechas as $fecha) { ?>
                        <li class="fecha-item">
                            <div class="fecha-hora">
                                <p><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha['fecha']); ?></p>
                                <p><strong>Hora:</strong> <?php echo htmlspecialchars($fecha['hora']); ?></p>
                            </div>
                            <div class="info-estadio">
                                <p><strong>Estadio:</strong> <?php echo htmlspecialchars($fecha['nombre_estadio']); ?></p>
                                <p><strong>Ubicación:</strong> <?php echo htmlspecialchars($fecha['ubicacion_estadio']); ?></p>
                            </div>
                            <div class="foto-estadio">
                                <img src="<?php echo htmlspecialchars($fecha['foto_estadio']); ?>" alt="<?php echo htmlspecialchars($fecha['nombre_estadio']); ?>">
                            </div>
                            <div class="buscar-boletos">
                                <a href="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo urlencode($fecha['id_fecha']); ?>">
                                    <button>Buscar Boletos</button>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            <?php } else { ?>
                <p>No hay fechas disponibles para este evento.</p>
            <?php } ?>
        </div>
    <?php
    } else {
        echo "<p>Evento no encontrado.</p>";
    }
    ?>
    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>