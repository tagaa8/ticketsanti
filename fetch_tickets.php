<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_GET['type'];
$tickets = [];

switch ($type) {
    case 'pasados':
        $sql = "SELECT t.id_ticket, e.nombre_evento, es.ubicacion_estadio, es.nombre_estadio, f.fecha, f.hora, u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, z.zona, t.asiento, t.qrcode
                FROM Ticket t
                JOIN Fecha f ON t.id_fecha = f.id_fecha
                JOIN Evento e ON f.id_evento = e.id_evento
                JOIN Estadios es ON f.id_estadio = es.id_estadio
                JOIN usuarios u ON t.id_usuario = u.id_usuario
                JOIN Zonas_Filas z ON t.id_zona_fila = z.id_zona_fila
                WHERE t.id_usuario = ? AND t.id_activo = 2";
        break;
    case 'hoy':
        $sql = "SELECT t.id_ticket, e.nombre_evento, es.ubicacion_estadio, es.nombre_estadio, f.fecha, f.hora, u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, z.zona, t.asiento, t.qrcode
                FROM Ticket t
                JOIN Fecha f ON t.id_fecha = f.id_fecha
                JOIN Evento e ON f.id_evento = e.id_evento
                JOIN Estadios es ON f.id_estadio = es.id_estadio
                JOIN usuarios u ON t.id_usuario = u.id_usuario
                JOIN Zonas_Filas z ON t.id_zona_fila = z.id_zona_fila
                WHERE t.id_usuario = ? AND f.fecha = CURDATE()";
        break;
    case 'proximos':
        $sql = "SELECT t.id_ticket, e.nombre_evento, es.ubicacion_estadio, es.nombre_estadio, f.fecha, f.hora, u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, z.zona, t.asiento, t.qrcode
                FROM Ticket t
                JOIN Fecha f ON t.id_fecha = f.id_fecha
                JOIN Evento e ON f.id_evento = e.id_evento
                JOIN Estadios es ON f.id_estadio = es.id_estadio
                JOIN usuarios u ON t.id_usuario = u.id_usuario
                JOIN Zonas_Filas z ON t.id_zona_fila = z.id_zona_fila
                WHERE t.id_usuario = ? AND f.fecha > CURDATE() AND t.id_activo = 1";
        break;
    default:
        echo json_encode([]);
        exit;
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tickets);
