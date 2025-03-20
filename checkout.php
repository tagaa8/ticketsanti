<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['comprar'])) {
        $id_zona_fila = $_GET['id_zona_fila'];
        $cantidad = (int)$_GET['cantidad'];
        $id_fecha = (int)$_GET['id_fecha'];
        $user_id = $_SESSION['user_id'];

        // Obtener los detalles de los tickets seleccionados
        $sql_tickets = "SELECT ZF.zona, ZF.fila, T.precio
                        FROM Zonas_Filas ZF
                        JOIN Ticket T ON ZF.id_zona_fila = T.id_zona_fila
                        WHERE ZF.id_zona_fila = ? AND T.id_fecha = ? AND (T.id_usuario = 1 OR T.id_usuario IS NULL)
                        LIMIT ?";
        $stmt_tickets = $pdo->prepare($sql_tickets);
        $stmt_tickets->execute([$id_zona_fila, $id_fecha, $cantidad]);
        $tickets = $stmt_tickets->fetchAll();

        if (empty($tickets)) {
            echo '<p>No se encontraron tickets disponibles.</p>';
            exit;
        }

        $total = array_sum(array_column($tickets, 'precio'));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
        $id_zona_fila = $_POST['id_zona_fila'];
        $cantidad = (int)$_POST['cantidad'];
        $id_fecha = (int)$_POST['id_fecha'];
        $user_id = $_SESSION['user_id'];
        $tarjeta = $_POST['tarjeta'];
        $cvv = $_POST['cvv'];
        $guardar_tarjeta = isset($_POST['guardar_tarjeta']) ? 1 : 0;

        // Validar tarjeta y CVV
        if (preg_match('/^\d{16}$/', $tarjeta) && preg_match('/^\d{3,4}$/', $cvv)) {
            // Obtener los primeros tickets disponibles para la zona y fila especificada
            $sql_tickets = "SELECT id_ticket FROM Ticket WHERE id_zona_fila = ? AND id_fecha = ? AND id_usuario = 1 LIMIT ?";
            $stmt_tickets = $pdo->prepare($sql_tickets);
            $stmt_tickets->execute([$id_zona_fila, $id_fecha, $cantidad]);
            $tickets_disponibles = $stmt_tickets->fetchAll();

            if (!empty($tickets_disponibles)) {
                // Actualizar los tickets para el usuario
                foreach ($tickets_disponibles as $ticket) {
                    $sql_update = "UPDATE Ticket SET id_usuario = ? WHERE id_ticket = ?";
                    $stmt_update = $pdo->prepare($sql_update);
                    $stmt_update->execute([$user_id, $ticket['id_ticket']]);
                }

                // Guardar tarjeta si se seleccionó
                if ($guardar_tarjeta) {
                    $sql_guardar_tarjeta = "INSERT INTO Tarjetas (id_usuario, tarjeta, cvv) VALUES (?, ?, ?)";
                    $stmt_guardar_tarjeta = $pdo->prepare($sql_guardar_tarjeta);
                    $stmt_guardar_tarjeta->execute([$user_id, $tarjeta, $cvv]);
                }

                // Redirección después de la compra
                header('Location: confirmacion.php');
                exit;
            } else {
                echo '<p>No se encontraron tickets disponibles.</p>';
            }
        } else {
            echo '<p>Tarjeta o CVV inválidos.</p>';
        }
    }
    ?>

    <!-- Contenido -->
    <div class="checkout">
        <h2>Resumen de la Compra</h2>
        <ul>
            <?php foreach ($tickets as $ticket) { ?>
                <li>
                    <p><strong>Zona:</strong> <?php echo htmlspecialchars($ticket['zona']); ?></p>
                    <p><strong>Fila:</strong> <?php echo htmlspecialchars($ticket['fila']); ?></p>
                    <p><strong>Precio:</strong> $<?php echo htmlspecialchars($ticket['precio']); ?></p>
                </li>
            <?php } ?>
        </ul>
        <p><strong>Total:</strong> $<?php echo htmlspecialchars($total); ?></p>

        <h2>Información de Pago</h2>
        <form action="checkout.php" method="POST">
            <input type="hidden" name="id_zona_fila" value="<?php echo $id_zona_fila; ?>">
            <input type="hidden" name="cantidad" value="<?php echo $cantidad; ?>">
            <input type="hidden" name="id_fecha" value="<?php echo $id_fecha; ?>">
            <label for="tarjeta">Número de Tarjeta:</label>
            <input type="text" name="tarjeta" id="tarjeta" required>
            <label for="cvv">CVV:</label>
            <input type="text" name="cvv" id="cvv" required>
            <label for="guardar_tarjeta">
                <input type="checkbox" name="guardar_tarjeta" id="guardar_tarjeta"> Guardar tarjeta para futuras compras
            </label>
            <button type="submit" name="finalizar_compra">Finalizar Compra</button>
        </form>
    </div>

    <footer class="footer">
        <p>Santiago Montero. Todos los derechos reservados.</p>
    </footer>
    <script src="build/js/app.js"></script>
</body>

</html>