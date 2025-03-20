<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets Disponibles</title>
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
        header('Location: login.php?redirect=fechaevento.php&id_evento=' . $_GET['id_evento'] . '&id_fecha=' . $_GET['id_fecha']);
        exit;
    }

    $id_evento = isset($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;
    $id_fecha = isset($_GET['id_fecha']) ? (int)$_GET['id_fecha'] : 0;
    $zona_filtro = isset($_GET['zona']) ? $_GET['zona'] : null;

    // Consultas para obtener los tickets disponibles
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
        $id_zona_fila = $_POST['id_zona_fila'];
        $cantidad = (int)$_POST['cantidad'];
        $user_id = $_SESSION['user_id'];

        // Obtener los primeros tickets disponibles para la zona y fila especificada
        $sql_tickets = "SELECT id_ticket FROM Ticket WHERE id_zona_fila = ? AND id_fecha = ? AND id_usuario = 1 LIMIT ?";
        $stmt_tickets = $pdo->prepare($sql_tickets);

        $stmt_tickets->bindValue(1, $id_zona_fila, PDO::PARAM_INT);
        $stmt_tickets->bindValue(2, $id_fecha, PDO::PARAM_INT);
        $stmt_tickets->bindValue(3, $cantidad, PDO::PARAM_INT); // Asegurar que LIMIT es un entero
        if ($stmt_tickets->execute()) {
            $tickets_disponibles = $stmt_tickets->fetchAll();

            // Verificar si se obtuvieron tickets disponibles
            if (!empty($tickets_disponibles)) {
                // Actualizar los tickets para el usuario
                foreach ($tickets_disponibles as $ticket) {
                    $sql_update = "UPDATE Ticket SET id_usuario = ? WHERE id_ticket = ?";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->execute([$user_id, $ticket['id_ticket']]);
                }
            }

            // Redirección después de la compra
            header('Location: fechaevento.php?id_evento=' . $id_evento . '&id_fecha=' . urlencode($id_fecha));
            exit;
        } else {
            // Depuración: Imprimir el error de la consulta SQL
            $error_info = $stmt_tickets->errorInfo();
            echo '<pre>';
            echo 'Error en la consulta SQL: ' . $error_info[2];
            echo '</pre>';
        }
    }
    ?>

    <!-- Header -->
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

    <!-- Contenido -->
    <div class="contenido-fechaevento">
        <div class="mapa-estadio">
            <?php
            // Obtener los detalles del estadio y los tickets disponibles
            $sql = "SELECT ZF.id_zona_fila, ZF.id_estadio, ZF.zona, ZF.fila, ZF.cantidad, 
                           E.mapa_estadio, 
                           T.id_ticket, T.id_zona_fila, T.id_fecha, T.asiento, T.precio, T.id_usuario
                    FROM Zonas_Filas ZF
                    JOIN Estadios E ON ZF.id_estadio = E.id_estadio
                    LEFT JOIN Ticket T ON ZF.id_zona_fila = T.id_zona_fila
                    WHERE T.id_fecha = ? AND (T.id_usuario = 1 OR T.id_usuario IS NULL)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_fecha]);
            $tickets = $stmt->fetchAll();

            if (!empty($tickets)) { ?>
                <img src="<?php echo htmlspecialchars($tickets[0]['mapa_estadio']); ?>" alt="Mapa del Estadio">
            <?php } ?>
        </div>



        <div class="tickets-disponibles">
            <!-- Filtros -->
            <div class="filtros">
                <h3>Elige tu zona</h3>
                <?php
                // Obtener las zonas y precios mínimos
                $sql_zonas = "SELECT DISTINCT ZF.zona, MIN(T.precio) as precio_minimo
                          FROM Zonas_Filas ZF
                          JOIN Ticket T ON ZF.id_zona_fila = T.id_zona_fila
                          WHERE T.id_fecha = ? AND (T.id_usuario = 1 OR T.id_usuario IS NULL)
                          GROUP BY ZF.zona";
                $stmt_zonas = $pdo->prepare($sql_zonas);
                $stmt_zonas->execute([$id_fecha]);
                $zonas = $stmt_zonas->fetchAll();

                foreach ($zonas as $zona) {
                    echo '<a href="fechaevento.php?id_evento=' . $id_evento . '&id_fecha=' . urlencode($id_fecha) . '&zona=' . urlencode($zona['zona']) . '">';
                    echo '<button>' . htmlspecialchars($zona['zona']) . ' - Desde $' . htmlspecialchars($zona['precio_minimo']) . '</button>';
                    echo '</a>';
                }
                ?>
            </div>
            <h3>Tickets Disponibles</h3>
            <ul class="tickets-lista">
                <?php
                $zonas_filas = [];
                foreach ($tickets as $ticket) {
                    if ($zona_filtro && $ticket['zona'] !== $zona_filtro) {
                        continue;
                    }
                    if (!isset($zonas_filas[$ticket['id_zona_fila']])) {
                        $zonas_filas[$ticket['id_zona_fila']] = [
                            'zona' => $ticket['zona'],
                            'fila' => $ticket['fila'],
                            'cantidad' => 0,
                            'precio' => $ticket['precio']
                        ];
                    }
                    $zonas_filas[$ticket['id_zona_fila']]['cantidad']++;
                }

                foreach ($zonas_filas as $id_zona_fila => $datos) {
                ?>
                    <li class="ticket-item">
                        <div class="ticket-info">
                            <p><strong>Zona:</strong> <?php echo htmlspecialchars($datos['zona']); ?></p>
                            <p><strong>Fila:</strong> <?php echo htmlspecialchars($datos['fila']); ?></p>
                            <p><strong>Precio:</strong> $<?php echo htmlspecialchars($datos['precio']); ?></p>
                            <p><strong>Disponibles:</strong> <?php echo htmlspecialchars($datos['cantidad']); ?></p>
                        </div>
                        <form action="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo urlencode($id_fecha); ?>" method="POST">
                            <input type="hidden" name="id_zona_fila" value="<?php echo $id_zona_fila; ?>">
                            <label for="cantidad">Cantidad:</label>
                            <input type="number" name="cantidad" id="cantidad" min="1" max="<?php echo $datos['cantidad']; ?>" required>
                            <button type="submit" name="comprar">Comprar</button>
                        </form>
                    </li>
                <?php
                }
                ?>
            </ul>
        </div>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>