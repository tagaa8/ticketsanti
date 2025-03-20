<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Tickets Disponibles</title>
</head>

<body>
    <?php
    session_start();
    include 'db_connect.php';

    if (!isset($_SESSION['user_id'])) {
        echo 'No user_id in session. Please log in.';
        exit;
    }

    $id_zona_fila = 4; // Cambia esto al valor que desees probar
    $cantidad = 2; // Cambia esto al valor que desees probar
    $user_id = $_SESSION['user_id'];
    $id_fecha = 1; // Cambia esto al valor que desees probar

    // Depuración: Imprimir los valores de las variables
    echo '<pre>';
    echo 'id_zona_fila: ' . $id_zona_fila . '<br>';
    echo 'cantidad: ' . $cantidad . '<br>';
    echo 'user_id: ' . $user_id . '<br>';
    echo 'id_fecha: ' . $id_fecha . '<br>';
    echo '</pre>';

    // Verificar la conexión a la base de datos
    if ($pdo) {
        echo '<p>Conexión a la base de datos exitosa.</p>';
    } else {
        echo '<p>Error al conectar a la base de datos.</p>';
        exit;
    }

    // Verificar el nombre de la base de datos actual
    $sql_db_name = "SELECT DATABASE()";
    $stmt_db_name = $pdo->query($sql_db_name);
    $db_name = $stmt_db_name->fetchColumn();
    echo '<p>Base de datos actual: ' . $db_name . '</p>';

    // Obtener los primeros tickets disponibles para la zona y fila especificada
    $sql_tickets = "SELECT id_ticket FROM Ticket WHERE id_zona_fila = ? AND id_fecha = ? AND id_usuario = 1 LIMIT ?";
    $stmt_tickets = $pdo->prepare($sql_tickets);

    // Depuración: Imprimir la consulta SQL y los parámetros
    echo '<pre>';
    echo 'Consulta SQL: ' . $sql_tickets . '<br>';
    echo 'Parámetros: ' . json_encode([$id_zona_fila, $id_fecha, $cantidad]) . '<br>';
    echo '</pre>';

    $stmt_tickets->bindValue(1, $id_zona_fila, PDO::PARAM_INT);
    $stmt_tickets->bindValue(2, $id_fecha, PDO::PARAM_INT);
    $stmt_tickets->bindValue(3, $cantidad, PDO::PARAM_INT); // Asegurar que LIMIT es un entero
    if ($stmt_tickets->execute()) {


        $tickets_disponibles = $stmt_tickets->fetchAll();

        // Depuración: Imprimir los tickets disponibles obtenidos
        echo '<pre>';
        print_r($tickets_disponibles);
        echo '</pre>';
    } else {
        // Depuración: Imprimir el error de la consulta SQL
        $error_info = $stmt_tickets->errorInfo();
        echo '<pre>';
        echo 'Error en la consulta SQL: ' . $error_info[2];
        echo '</pre>';
    }
    ?>
</body>

</html>