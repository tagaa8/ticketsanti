<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Evento - Santiago Tickets</title>
    <meta name="description" content="Descubre todos los detalles del evento y compra tus boletos">
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
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <a href="micuenta.php" class="btn btn-outline">Mi Cuenta</a>
                    <?php } else { ?>
                        <a href="login.php?redirect=evento.php&id_evento=<?php echo $id_evento; ?>" class="btn btn-primary">Iniciar Sesión</a>
                    <?php } ?>
                </div>
            </div>
        </header>

        <div class="evento-hero">
            <div class="evento-hero-imagen">
                <img src="<?php echo htmlspecialchars($evento['foto']); ?>" alt="<?php echo htmlspecialchars($evento['nombre_evento']); ?>">
                <div class="evento-hero-overlay"></div>
            </div>
            <div class="evento-hero-contenido">
                <div class="contenedor">
                    <nav class="breadcrumb">
                        <a href="index.php">Inicio</a>
                        <span>/</span>
                        <a href="index.php#eventos">Eventos</a>
                        <span>/</span>
                        <span><?php echo htmlspecialchars($evento['nombre_evento']); ?></span>
                    </nav>
                    <h1 class="evento-titulo"><?php echo htmlspecialchars($evento['nombre_evento']); ?></h1>
                    <p class="evento-descripcion"><?php echo nl2br(htmlspecialchars($evento['descripcion_evento'])); ?></p>
                    <?php if (!empty($evento['categoria'])): ?>
                        <span class="evento-categoria"><?php echo htmlspecialchars($evento['categoria']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="evento-contenido">
            <div class="contenedor">
                <div class="evento-info">
                    <h2>Fechas y Ubicaciones</h2>
                    <p>Selecciona la fecha que más te convenga para asistir a este increíble evento.</p>
                </div>
                
                <?php if ($fechas) { ?>
                    <div class="fechas-grid">
                        <?php foreach ($fechas as $fecha) { 
                            // Calcular disponibilidad de tickets
                            $sql_disponibilidad = "SELECT COUNT(*) as disponibles FROM Ticket t 
                                                   JOIN Zonas_Filas zf ON t.id_zona_fila = zf.id_zona_fila 
                                                   WHERE t.id_fecha = ? AND t.id_usuario = 1";
                            $stmt_disp = $pdo->prepare($sql_disponibilidad);
                            $stmt_disp->execute([$fecha['id_fecha']]);
                            $disponibilidad = $stmt_disp->fetch()['disponibles'];
                            
                            $fecha_formateada = date('d/m/Y', strtotime($fecha['fecha']));
                            $hora_formateada = date('H:i', strtotime($fecha['hora']));
                        ?>
                            <article class="fecha-card">
                                <div class="fecha-card-header">
                                    <div class="fecha-info">
                                        <div class="fecha-principal">
                                            <span class="dia"><?php echo date('d', strtotime($fecha['fecha'])); ?></span>
                                            <div class="mes-ano">
                                                <span class="mes"><?php echo strftime('%b', strtotime($fecha['fecha'])); ?></span>
                                                <span class="ano"><?php echo date('Y', strtotime($fecha['fecha'])); ?></span>
                                            </div>
                                        </div>
                                        <div class="fecha-detalles">
                                            <h3 class="fecha-titulo"><?php echo $fecha_formateada; ?></h3>
                                            <p class="fecha-hora">🕕 <?php echo $hora_formateada; ?> hrs</p>
                                        </div>
                                    </div>
                                    <div class="disponibilidad <?php echo $disponibilidad > 0 ? 'disponible' : 'agotado'; ?>">
                                        <?php if ($disponibilidad > 0): ?>
                                            <span class="estado">✅ Disponible</span>
                                            <span class="cantidad"><?php echo $disponibilidad; ?> boletos</span>
                                        <?php else: ?>
                                            <span class="estado">❌ Agotado</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="fecha-card-body">
                                    <div class="estadio-info">
                                        <div class="estadio-imagen">
                                            <img src="<?php echo htmlspecialchars($fecha['foto_estadio']); ?>" 
                                                 alt="<?php echo htmlspecialchars($fecha['nombre_estadio']); ?>" loading="lazy">
                                        </div>
                                        <div class="estadio-detalles">
                                            <h4 class="estadio-nombre"><?php echo htmlspecialchars($fecha['nombre_estadio']); ?></h4>
                                            <p class="estadio-ubicacion">📍 <?php echo htmlspecialchars($fecha['ubicacion_estadio']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="fecha-card-footer">
                                    <?php if ($disponibilidad > 0): ?>
                                        <a href="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo urlencode($fecha['id_fecha']); ?>" 
                                           class="btn btn-primary btn-block">
                                            Ver Boletos Disponibles
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-disabled btn-block" disabled>
                                            Boletos Agotados
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="no-fechas">
                        <div class="no-fechas-icono">📅</div>
                        <h3>No hay fechas disponibles</h3>
                        <p>Actualmente no hay fechas programadas para este evento. ¡Mantente atento para futuras fechas!</p>
                        <a href="index.php" class="btn btn-outline">Ver Otros Eventos</a>
                    </div>
                <?php } ?>
            </div>
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