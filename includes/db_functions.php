<?php
/**
 * Database Helper Functions
 * Santiago Tickets - Database Operations
 */

require_once __DIR__ . '/../db_connect.php';
require_once __DIR__ . '/auth.php';

/**
 * Obtener eventos con filtros
 */
function getEvents($filters = []) {
    global $pdo;
    
    $sql = "SELECT DISTINCT e.*, 
                   COUNT(f.id_fecha) as total_fechas,
                   MIN(f.fecha) as proxima_fecha,
                   MIN(t.precio) as precio_minimo
            FROM Evento e 
            LEFT JOIN Fecha f ON e.id_evento = f.id_evento 
            LEFT JOIN Ticket t ON f.id_fecha = t.id_fecha AND t.id_usuario = 1
            WHERE 1=1";
    
    $params = [];
    
    // Filtro por fecha futura
    if ($filters['future_only'] ?? true) {
        $sql .= " AND (f.fecha >= CURDATE() OR f.fecha IS NULL)";
    }
    
    // Filtro por categoría
    if (!empty($filters['categoria'])) {
        $sql .= " AND e.categoria = ?";
        $params[] = $filters['categoria'];
    }
    
    // Filtro por búsqueda
    if (!empty($filters['search'])) {
        $sql .= " AND (e.nombre_evento LIKE ? OR e.descripcion_evento LIKE ? OR e.artista LIKE ?)";
        $search_term = '%' . $filters['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $sql .= " GROUP BY e.id_evento ORDER BY proxima_fecha ASC";
    
    if (isset($filters['limit'])) {
        $sql .= " LIMIT " . (int)$filters['limit'];
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en getEvents: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener detalles de un evento
 */
function getEventDetails($id_evento) {
    global $pdo;
    
    $sql = "SELECT e.*, u.nombre as organizador_nombre, u.apellido as organizador_apellido
            FROM Evento e
            LEFT JOIN usuarios u ON e.organizador_id = u.id_usuario
            WHERE e.id_evento = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_evento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en getEventDetails: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtener fechas de un evento
 */
function getEventDates($id_evento) {
    global $pdo;
    
    $sql = "SELECT f.*, es.nombre_estadio, es.foto_estadio, es.ubicacion_estadio,
                   COUNT(t.id_ticket) as tickets_disponibles
            FROM Fecha f
            JOIN Estadios es ON f.id_estadio = es.id_estadio
            LEFT JOIN Ticket t ON f.id_fecha = t.id_fecha AND t.id_usuario = 1
            WHERE f.id_evento = ?
            GROUP BY f.id_fecha
            ORDER BY f.fecha ASC, f.hora ASC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_evento]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en getEventDates: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener tickets disponibles por fecha
 */
function getAvailableTickets($id_fecha, $zona_filtro = null) {
    global $pdo;
    
    $sql = "SELECT zf.id_zona_fila, zf.zona, zf.fila, zf.cantidad,
                   t.id_ticket, t.asiento, t.precio, t.id_usuario,
                   COUNT(t.id_ticket) as tickets_disponibles
            FROM Zonas_Filas zf
            LEFT JOIN Ticket t ON zf.id_zona_fila = t.id_zona_fila
            WHERE t.id_fecha = ? AND t.id_usuario = 1";
    
    $params = [$id_fecha];
    
    if ($zona_filtro) {
        $sql .= " AND zf.zona = ?";
        $params[] = $zona_filtro;
    }
    
    $sql .= " GROUP BY zf.id_zona_fila ORDER BY zf.zona, zf.fila";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en getAvailableTickets: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtener zonas con precios mínimos
 */
function getZonesWithPrices($id_fecha) {
    global $pdo;
    
    $sql = "SELECT DISTINCT zf.zona, MIN(t.precio) as precio_minimo, COUNT(t.id_ticket) as disponibles
            FROM Zonas_Filas zf
            JOIN Ticket t ON zf.id_zona_fila = t.id_zona_fila
            WHERE t.id_fecha = ? AND t.id_usuario = 1
            GROUP BY zf.zona
            ORDER BY precio_minimo";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_fecha]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en getZonesWithPrices: " . $e->getMessage());
        return [];
    }
}

/**
 * Verificar disponibilidad de tickets
 */
function checkTicketAvailability($id_zona_fila, $id_fecha, $cantidad) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) as disponibles
            FROM Ticket
            WHERE id_zona_fila = ? AND id_fecha = ? AND id_usuario = 1";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_zona_fila, $id_fecha]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['disponibles'] >= $cantidad;
    } catch (PDOException $e) {
        error_log("Error en checkTicketAvailability: " . $e->getMessage());
        return false;
    }
}

/**
 * Comprar tickets (transacción segura)
 */
function purchaseTickets($user_id, $id_zona_fila, $id_fecha, $cantidad, $payment_data = []) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Verificar disponibilidad
        if (!checkTicketAvailability($id_zona_fila, $id_fecha, $cantidad)) {
            throw new Exception('No hay suficientes tickets disponibles');
        }
        
        // Obtener tickets disponibles
        $sql_tickets = "SELECT id_ticket, precio FROM Ticket 
                       WHERE id_zona_fila = ? AND id_fecha = ? AND id_usuario = 1 
                       LIMIT ?";
        $stmt_tickets = $pdo->prepare($sql_tickets);
        $stmt_tickets->execute([$id_zona_fila, $id_fecha, $cantidad]);
        $tickets_disponibles = $stmt_tickets->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($tickets_disponibles) < $cantidad) {
            throw new Exception('No hay suficientes tickets disponibles');
        }
        
        $total = array_sum(array_column($tickets_disponibles, 'precio'));
        
        // Actualizar tickets
        $ticket_ids = [];
        foreach ($tickets_disponibles as $ticket) {
            $sql_update = "UPDATE Ticket SET id_usuario = ?, fecha_compra = NOW() WHERE id_ticket = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$user_id, $ticket['id_ticket']]);
            $ticket_ids[] = $ticket['id_ticket'];
        }
        
        // Registrar transacción
        $sql_transaction = "INSERT INTO Transacciones 
                           (id_usuario, tipo_transaccion, monto, metodo_pago, referencia_pago, estado, fecha_transaccion)
                           VALUES (?, 'compra', ?, ?, ?, 'completada', NOW())";
        $stmt_transaction = $pdo->prepare($sql_transaction);
        $stmt_transaction->execute([
            $user_id, 
            $total, 
            $payment_data['metodo'] ?? 'tarjeta',
            $payment_data['referencia'] ?? 'REF-' . time()
        ]);
        
        $transaction_id = $pdo->lastInsertId();
        
        $pdo->commit();
        
        // Log de seguridad
        logSecurityEvent('ticket_purchase', [
            'user_id' => $user_id,
            'transaction_id' => $transaction_id,
            'ticket_count' => $cantidad,
            'total_amount' => $total
        ]);
        
        return [
            'success' => true,
            'transaction_id' => $transaction_id,
            'ticket_ids' => $ticket_ids,
            'total' => $total
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error en purchaseTickets: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Obtener tickets de usuario
 */
function getUserTickets($user_id, $type = 'all') {
    global $pdo;
    
    $sql = "SELECT t.id_ticket, t.asiento, t.precio, t.qrcode, t.fecha_compra,
                   e.nombre_evento, e.foto as evento_foto,
                   es.nombre_estadio, es.ubicacion_estadio,
                   f.fecha, f.hora, f.id_evento,
                   zf.zona, zf.fila,
                   CASE 
                       WHEN f.fecha < CURDATE() THEN 'usado'
                       WHEN f.fecha = CURDATE() THEN 'hoy'
                       ELSE 'activo'
                   END as status
            FROM Ticket t
            JOIN Fecha f ON t.id_fecha = f.id_fecha
            JOIN Evento e ON f.id_evento = e.id_evento
            JOIN Estadios es ON f.id_estadio = es.id_estadio
            JOIN Zonas_Filas zf ON t.id_zona_fila = zf.id_zona_fila
            WHERE t.id_usuario = ?";
    
    $params = [$user_id];
    
    switch ($type) {
        case 'pasados':
            $sql .= " AND f.fecha < CURDATE()";
            $sql .= " ORDER BY f.fecha DESC, f.hora DESC";
            break;
        case 'hoy':
            $sql .= " AND f.fecha = CURDATE()";
            $sql .= " ORDER BY f.hora ASC";
            break;
        case 'proximos':
            $sql .= " AND f.fecha > CURDATE()";
            $sql .= " ORDER BY f.fecha ASC, f.hora ASC";
            break;
        default:
            $sql .= " ORDER BY f.fecha ASC, f.hora ASC";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos
        foreach ($tickets as &$ticket) {
            $ticket['fecha_formateada'] = date('d/m/Y', strtotime($ticket['fecha']));
            $ticket['hora_formateada'] = date('H:i', strtotime($ticket['hora']));
            $ticket['precio_formateado'] = number_format($ticket['precio'], 0);
            
            // Generar QR si no existe
            if (empty($ticket['qrcode'])) {
                $ticket['qrcode'] = generateQRCode($ticket['id_ticket']);
            }
        }
        
        return $tickets;
    } catch (PDOException $e) {
        error_log("Error en getUserTickets: " . $e->getMessage());
        return [];
    }
}

/**
 * Generar código QR
 */
function generateQRCode($ticket_id) {
    $data = "SANTIAGO-TICKETS-" . $ticket_id . "-" . date('Y');
    return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($data);
}

/**
 * Obtener estadísticas del usuario
 */
function getUserStats($user_id) {
    global $pdo;
    
    try {
        $sql = "SELECT 
                   COUNT(*) as total_tickets,
                   SUM(t.precio) as total_gastado,
                   COUNT(CASE WHEN f.fecha > CURDATE() THEN 1 END) as tickets_futuros,
                   COUNT(CASE WHEN f.fecha < CURDATE() THEN 1 END) as tickets_usados
                FROM Ticket t
                JOIN Fecha f ON t.id_fecha = f.id_fecha
                WHERE t.id_usuario = ? AND t.id_usuario != 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en getUserStats: " . $e->getMessage());
        return [
            'total_tickets' => 0,
            'total_gastado' => 0,
            'tickets_futuros' => 0,
            'tickets_usados' => 0
        ];
    }
}

/**
 * Buscar eventos
 */
function searchEvents($query, $limit = 10) {
    global $pdo;
    
    $sql = "SELECT e.*, MIN(f.fecha) as proxima_fecha
            FROM Evento e
            LEFT JOIN Fecha f ON e.id_evento = f.id_evento AND f.fecha >= CURDATE()
            WHERE e.nombre_evento LIKE ? OR e.descripcion_evento LIKE ? OR e.artista LIKE ?
            GROUP BY e.id_evento
            ORDER BY proxima_fecha ASC
            LIMIT ?";
    
    try {
        $search_term = '%' . $query . '%';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en searchEvents: " . $e->getMessage());
        return [];
    }
}