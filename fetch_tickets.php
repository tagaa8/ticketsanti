<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? '';

// Validar tipo
if (!in_array($type, ['pasados', 'hoy', 'proximos'])) {
    echo json_encode(['error' => 'Tipo de consulta inválido']);
    exit;
}

try {
    switch ($type) {
        case 'pasados':
            $sql = "SELECT t.id_ticket, e.nombre_evento, e.foto as evento_foto,
                           es.ubicacion_estadio, es.nombre_estadio, 
                           f.fecha, f.hora, f.id_evento,
                           u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, 
                           z.zona, z.fila, t.asiento, t.precio, t.qrcode,
                           'usado' as status
                    FROM Ticket t
                    JOIN Fecha f ON t.id_fecha = f.id_fecha
                    JOIN Evento e ON f.id_evento = e.id_evento
                    JOIN Estadios es ON f.id_estadio = es.id_estadio
                    JOIN usuarios u ON t.id_usuario = u.id_usuario
                    JOIN Zonas_Filas z ON t.id_zona_fila = z.id_zona_fila
                    WHERE t.id_usuario = ? AND f.fecha < CURDATE()
                    ORDER BY f.fecha DESC, f.hora DESC";
            break;
            
        case 'hoy':
            $sql = "SELECT t.id_ticket, e.nombre_evento, e.foto as evento_foto,
                           es.ubicacion_estadio, es.nombre_estadio, 
                           f.fecha, f.hora, f.id_evento,
                           u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, 
                           z.zona, z.fila, t.asiento, t.precio, t.qrcode,
                           'activo' as status
                    FROM Ticket t
                    JOIN Fecha f ON t.id_fecha = f.id_fecha
                    JOIN Evento e ON f.id_evento = e.id_evento
                    JOIN Estadios es ON f.id_estadio = es.id_estadio
                    JOIN usuarios u ON t.id_usuario = u.id_usuario
                    JOIN Zonas_Filas z ON t.id_zona_fila = z.id_zona_fila
                    WHERE t.id_usuario = ? AND f.fecha = CURDATE()
                    ORDER BY f.hora ASC";
            break;
            
        case 'proximos':
            $sql = "SELECT t.id_ticket, e.nombre_evento, e.foto as evento_foto,
                           es.ubicacion_estadio, es.nombre_estadio, 
                           f.fecha, f.hora, f.id_evento,
                           u.nombre AS nombre_usuario, u.apellido AS apellido_usuario, 
                           z.zona, z.fila, t.asiento, t.precio, t.qrcode,
                           'activo' as status
                    FROM Ticket t
                    JOIN Fecha f ON t.id_fecha = f.id_fecha
                    JOIN Evento e ON f.id_evento = e.id_evento
                    JOIN Estadios es ON f.id_estadio = es.id_estadio
                    JOIN usuarios u ON t.id_usuario = u.id_usuario
                    JOIN Zonas_Filas z ON t.id_zona_fila = z.id_zona_fila
                    WHERE t.id_usuario = ? AND f.fecha > CURDATE()
                    ORDER BY f.fecha ASC, f.hora ASC";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear datos para el frontend
    foreach ($tickets as &$ticket) {
        $ticket['fecha_formateada'] = date('d/m/Y', strtotime($ticket['fecha']));
        $ticket['hora_formateada'] = date('H:i', strtotime($ticket['hora']));
        $ticket['precio_formateado'] = number_format($ticket['precio'], 0);
        
        // Generar QR code si no existe
        if (empty($ticket['qrcode'])) {
            $ticket['qrcode'] = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode('TICKET-' . $ticket['id_ticket']);
        }
    }
    
    echo json_encode([
        'success' => true,
        'tickets' => $tickets,
        'count' => count($tickets)
    ]);
    
} catch (PDOException $e) {
    error_log("Error en fetch_tickets.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener los boletos']);
}
