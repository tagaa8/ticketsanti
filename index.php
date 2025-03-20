<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
                <a href="login.php"><button>Iniciar Sesión</button></a>
            <?php } ?>
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

    <div class="eventos">
        <h2>Próximos Eventos</h2>
        <?php
        include 'db_connect.php';

        $sql = "SELECT * FROM Evento";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() > 0) {
            echo '<div class="eventos-grid">';
            while ($row = $stmt->fetch()) {
                echo '<div class="evento">';
                echo '<img src="' . $row["foto"] . '" alt="' . $row["nombre_evento"] . '">';
                echo '<h3>' . $row["nombre_evento"] . '</h3>';
                echo '<div class="evento-detalle">';
                echo '<a href="evento.php?id_evento=' . $row["id_evento"] . '"><button>Ver Evento</button></a>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo "No hay eventos disponibles.";
        }
        ?>
    </div>

    <section class="sobre-festival">
        <div class="imagen">
            <picture>

                <img width="300" height="200" loading="lazy" src="src/imagen_dj.jpg" alt="Sobre Festival" />
            </picture>
        </div>
        <div class="contenido-festival">
            <h2>Santi Tickets</h2>
            <p class="fecha">Sobre Nosotros</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. </p>
        </div>
    </section>


    <footer class="footer">
        <p> Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>