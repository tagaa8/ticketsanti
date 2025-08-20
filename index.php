<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Santiago Tickets - Plataforma de Tickets</title>
    <meta name="description" content="Santiago Tickets - La mejor plataforma para comprar boletos de eventos">
    <link rel="stylesheet" href="build/css/app.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <?php
    session_start();
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
                <a href="index.php" class="nav-link active">Inicio</a>
                <a href="#eventos" class="nav-link">Eventos</a>
                <a href="#sobre-nosotros" class="nav-link">Sobre Nosotros</a>
            </nav>

            <div class="user-actions">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="micuenta.php" class="btn btn-outline">Mi Cuenta</a>
                <?php } else { ?>
                    <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                    <a href="register.php" class="btn btn-outline">Registrarse</a>
                <?php } ?>
            </div>

            <button class="menu-toggle" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
    <div class="video">
        <div class="overlay">
            <div class="contenedor contenido-video">
                <h2>BZRP</h2>
                <p>Julio 2026, CDMX, México</p>
            </div>
        </div>
        <video autoplay muted loop>
            <source src="src/video/videoplayback.mp4" type="video/mp4" />
            <source src="video/dj.ogv" type="video/ogg" />
            <source src="video/dj.webm" type="video/webm" />
        </video>
    </div>

    <section class="eventos" id="eventos">
        <div class="contenedor">
            <div class="eventos-header">
                <h2>Próximos Eventos</h2>
                <p>Descubre los mejores eventos y consigue tus boletos</p>
            </div>
            
            <?php
            include 'db_connect.php';

            try {
                // Solo mostrar eventos con fechas futuras
                $sql = "SELECT DISTINCT e.*, 
                               COUNT(f.id_fecha) as total_fechas,
                               MIN(f.fecha) as proxima_fecha
                        FROM Evento e 
                        LEFT JOIN Fecha f ON e.id_evento = f.id_evento 
                        WHERE f.fecha >= CURDATE() OR f.fecha IS NULL
                        GROUP BY e.id_evento 
                        ORDER BY proxima_fecha ASC";
                $stmt = $pdo->query($sql);

                if ($stmt->rowCount() > 0) {
                    echo '<div class="eventos-grid">';
                    while ($row = $stmt->fetch()) {
                        echo '<article class="evento-card">';
                        echo '<div class="evento-imagen">';
                        echo '<img src="' . htmlspecialchars($row["foto"]) . '" alt="' . htmlspecialchars($row["nombre_evento"]) . '" loading="lazy">';
                        echo '<div class="evento-overlay">';
                        echo '<span class="evento-categoria">' . htmlspecialchars($row["categoria"] ?? 'Evento') . '</span>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="evento-contenido">';
                        echo '<h3 class="evento-titulo">' . htmlspecialchars($row["nombre_evento"]) . '</h3>';
                        echo '<p class="evento-descripcion">' . htmlspecialchars(substr($row["descripcion_evento"] ?? '', 0, 100)) . '...</p>';
                        if ($row['proxima_fecha']) {
                            echo '<p class="evento-fecha">📅 ' . date('d/m/Y', strtotime($row['proxima_fecha'])) . '</p>';
                        }
                        echo '<div class="evento-acciones">';
                        echo '<a href="evento.php?id_evento=' . $row["id_evento"] . '" class="btn btn-primary">Ver Detalles</a>';
                        echo '</div>';
                        echo '</div>';
                        echo '</article>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="eventos-empty">';
                    echo '<div class="empty-icon">🎫</div>';
                    echo '<h3>No hay eventos disponibles</h3>';
                    echo '<p>Actualmente no tenemos eventos programados. ¡Vuelve pronto para ver nuevos eventos!</p>';
                    echo '</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="error-message">Error al cargar eventos. Por favor, intenta más tarde.</div>';
                error_log("Error en index.php: " . $e->getMessage());
            }
            ?>
        </div>
    </section>
    </div>

    <section class="sobre-nosotros" id="sobre-nosotros">
        <div class="contenedor">
            <div class="sobre-nosotros-contenido">
                <div class="sobre-imagen">
                    <picture>
                        <img width="500" height="400" loading="lazy" src="src/imagen_dj.jpg" alt="Sobre Santiago Tickets" />
                    </picture>
                </div>
                <div class="sobre-texto">
                    <h2>Santiago Tickets</h2>
                    <p class="sobre-subtitulo">Tu plataforma de confianza para eventos</p>
                    <p>Somos la plataforma líder en venta de boletos para eventos en México. Ofrecemos una experiencia segura, confiable y fácil de usar para que puedas disfrutar de los mejores eventos sin complicaciones.</p>
                    
                    <div class="caracteristicas">
                        <div class="caracteristica">
                            <div class="caracteristica-icono">🎫</div>
                            <h4>Boletos Seguros</h4>
                            <p>Tickets digitales con códigos QR únicos</p>
                        </div>
                        <div class="caracteristica">
                            <div class="caracteristica-icono">💳</div>
                            <h4>Pago Seguro</h4>
                            <p>Transacciones protegidas y confiables</p>
                        </div>
                        <div class="caracteristica">
                            <div class="caracteristica-icono">📱</div>
                            <h4>Fácil de Usar</h4>
                            <p>Interfaz moderna y responsiva</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <footer class="footer">
        <p> Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>