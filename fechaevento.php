<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletos Disponibles - Santiago Tickets</title>
    <meta name="description" content="Encuentra y compra tus boletos para el evento">
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
                    <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                <?php } ?>
            </div>
        </div>
    </header>

    <div class="fechaevento-container">
        <div class="contenedor">
            <?php
            // Obtener información del evento y fecha
            $sql_info = "SELECT e.nombre_evento, e.foto, es.nombre_estadio, es.mapa_estadio, f.fecha, f.hora
                        FROM Fecha f
                        JOIN Evento e ON f.id_evento = e.id_evento
                        JOIN Estadios es ON f.id_estadio = es.id_estadio
                        WHERE f.id_fecha = ?";
            $stmt_info = $pdo->prepare($sql_info);
            $stmt_info->execute([$id_fecha]);
            $info_evento = $stmt_info->fetch();
            
            if (!$info_evento) {
                echo '<div class="error-message">Evento no encontrado.</div>';
                exit;
            }
            
            $fecha_formateada = date('d/m/Y', strtotime($info_evento['fecha']));
            $hora_formateada = date('H:i', strtotime($info_evento['hora']));
            ?>
            
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Inicio</a>
                <span>/</span>
                <a href="index.php#eventos">Eventos</a>
                <span>/</span>
                <a href="evento.php?id_evento=<?php echo $id_evento; ?>"><?php echo htmlspecialchars($info_evento['nombre_evento']); ?></a>
                <span>/</span>
                <span>Boletos</span>
            </nav>
            
            <!-- Header del evento -->
            <div class="evento-header">
                <div class="evento-info">
                    <h1><?php echo htmlspecialchars($info_evento['nombre_evento']); ?></h1>
                    <div class="evento-meta">
                        <span class="fecha">📅 <?php echo $fecha_formateada; ?></span>
                        <span class="hora">🕕 <?php echo $hora_formateada; ?> hrs</span>
                        <span class="ubicacion">📍 <?php echo htmlspecialchars($info_evento['nombre_estadio']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="fechaevento-contenido">
                <!-- Mapa del estadio -->
                <div class="mapa-section">
                    <h2>Mapa del Estadio</h2>
                    <?php if (!empty($info_evento['mapa_estadio'])): ?>
                        <div class="mapa-container">
                            <img src="<?php echo htmlspecialchars($info_evento['mapa_estadio']); ?>" alt="Mapa del <?php echo htmlspecialchars($info_evento['nombre_estadio']); ?>" class="mapa-estadio">
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Tickets disponibles -->
                <div class="tickets-section">
                    <?php
                    // Obtener los tickets disponibles
                    $sql = "SELECT ZF.id_zona_fila, ZF.zona, ZF.fila, ZF.cantidad, 
                                   T.id_ticket, T.asiento, T.precio, T.id_usuario
                            FROM Zonas_Filas ZF
                            LEFT JOIN Ticket T ON ZF.id_zona_fila = T.id_zona_fila
                            WHERE T.id_fecha = ? AND T.id_usuario = 1
                            ORDER BY ZF.zona, ZF.fila";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id_fecha]);
                    $tickets = $stmt->fetchAll();
                    
                    if (!empty($tickets)) {
                        // Obtener las zonas y precios mínimos
                        $sql_zonas = "SELECT DISTINCT ZF.zona, MIN(T.precio) as precio_minimo, COUNT(T.id_ticket) as disponibles
                                      FROM Zonas_Filas ZF
                                      JOIN Ticket T ON ZF.id_zona_fila = T.id_zona_fila
                                      WHERE T.id_fecha = ? AND T.id_usuario = 1
                                      GROUP BY ZF.zona
                                      ORDER BY precio_minimo";
                        $stmt_zonas = $pdo->prepare($sql_zonas);
                        $stmt_zonas->execute([$id_fecha]);
                        $zonas = $stmt_zonas->fetchAll();
                    ?>
                    
                    <!-- Filtros de zona -->
                    <div class="zona-filtros">
                        <h2>Selecciona tu Zona</h2>
                        <div class="zona-buttons">
                            <a href="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo $id_fecha; ?>" 
                               class="zona-btn <?php echo !$zona_filtro ? 'active' : ''; ?>">
                                Todas las Zonas
                            </a>
                            <?php foreach ($zonas as $zona): ?>
                                <a href="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo $id_fecha; ?>&zona=<?php echo urlencode($zona['zona']); ?>" 
                                   class="zona-btn <?php echo ($zona_filtro === $zona['zona']) ? 'active' : ''; ?>">
                                    <span class="zona-nombre"><?php echo htmlspecialchars($zona['zona']); ?></span>
                                    <span class="zona-precio">Desde $<?php echo number_format($zona['precio_minimo']); ?></span>
                                    <span class="zona-disponibles"><?php echo $zona['disponibles']; ?> disponibles</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Lista de tickets -->
                    <div class="tickets-lista-section">
                        <h3>Boletos Disponibles <?php echo $zona_filtro ? 'en ' . htmlspecialchars($zona_filtro) : ''; ?></h3>
                        
                        <div class="tickets-grid">
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

                            if (empty($zonas_filas)): ?>
                                <div class="no-tickets">
                                    <div class="no-tickets-icon">🎫</div>
                                    <h4>No hay boletos disponibles</h4>
                                    <p>Actualmente no hay boletos disponibles <?php echo $zona_filtro ? 'en la zona ' . htmlspecialchars($zona_filtro) : 'para esta fecha'; ?>.</p>
                                    <?php if ($zona_filtro): ?>
                                        <a href="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo $id_fecha; ?>" class="btn btn-outline">Ver Todas las Zonas</a>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($zonas_filas as $id_zona_fila => $datos): ?>
                                    <div class="ticket-card">
                                        <div class="ticket-header">
                                            <h4 class="zona-nombre"><?php echo htmlspecialchars($datos['zona']); ?></h4>
                                            <span class="precio">${<?php echo number_format($datos['precio']); ?></span>
                                        </div>
                                        <div class="ticket-info">
                                            <div class="info-item">
                                                <span class="label">Fila:</span>
                                                <span class="value"><?php echo htmlspecialchars($datos['fila']); ?></span>
                                            </div>
                                            <div class="info-item">
                                                <span class="label">Disponibles:</span>
                                                <span class="value disponibles"><?php echo $datos['cantidad']; ?> boletos</span>
                                            </div>
                                        </div>
                                        <form class="ticket-form" action="fechaevento.php?id_evento=<?php echo $id_evento; ?>&id_fecha=<?php echo $id_fecha; ?>" method="POST">
                                            <input type="hidden" name="id_zona_fila" value="<?php echo $id_zona_fila; ?>">
                                            <div class="cantidad-selector">
                                                <label for="cantidad_<?php echo $id_zona_fila; ?>">Cantidad:</label>
                                                <select name="cantidad" id="cantidad_<?php echo $id_zona_fila; ?>" required>
                                                    <?php for ($i = 1; $i <= min(8, $datos['cantidad']); $i++): ?>
                                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> boleto<?php echo $i > 1 ? 's' : ''; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            <button type="submit" name="comprar" class="btn btn-primary btn-block">
                                                🛍️ Comprar Boletos
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php } else { ?>
                        <div class="no-tickets-evento">
                            <div class="no-tickets-icon">🎫</div>
                            <h3>No hay boletos disponibles</h3>
                            <p>Actualmente no hay boletos disponibles para esta fecha del evento.</p>
                            <a href="evento.php?id_evento=<?php echo $id_evento; ?>" class="btn btn-outline">Ver Otras Fechas</a>
                        </div>
                    <?php } ?>
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