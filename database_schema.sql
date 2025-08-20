-- =============================================
-- Santiago Tickets - Database Schema
-- Sistema de Venta de Boletos Completo
-- =============================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS Eventos_Estadios 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE Eventos_Estadios;

-- =============================================
-- 1. TABLA USUARIOS
-- =============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'organizador', 'cliente') DEFAULT 'cliente',
    telefono VARCHAR(20) NULL,
    fecha_nacimiento DATE NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE,
    verificado BOOLEAN DEFAULT FALSE,
    token_verificacion VARCHAR(255) NULL,
    
    INDEX idx_email (correo),
    INDEX idx_username (nombre_usuario),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- =============================================
-- 2. TABLA EVENTOS
-- =============================================
CREATE TABLE IF NOT EXISTS Evento (
    id_evento INT PRIMARY KEY AUTO_INCREMENT,
    nombre_evento VARCHAR(200) NOT NULL,
    descripcion_evento TEXT,
    foto VARCHAR(255) NULL,
    categoria VARCHAR(100) DEFAULT 'Concierto',
    genero VARCHAR(100) NULL,
    artista VARCHAR(200) NULL,
    organizador_id INT NULL,
    edad_minima INT DEFAULT 0,
    estado ENUM('activo', 'cancelado', 'finalizado') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (organizador_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_categoria (categoria),
    INDEX idx_estado (estado),
    INDEX idx_organizador (organizador_id),
    INDEX idx_fecha_creacion (fecha_creacion)
) ENGINE=InnoDB;

-- =============================================
-- 3. TABLA ESTADIOS
-- =============================================
CREATE TABLE IF NOT EXISTS Estadios (
    id_estadio INT PRIMARY KEY AUTO_INCREMENT,
    nombre_estadio VARCHAR(200) NOT NULL,
    ubicacion_estadio VARCHAR(300) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    pais VARCHAR(100) DEFAULT 'México',
    capacidad INT NOT NULL DEFAULT 0,
    foto_estadio VARCHAR(255) NULL,
    mapa_estadio VARCHAR(255) NULL,
    direccion_completa TEXT NULL,
    latitud DECIMAL(10, 8) NULL,
    longitud DECIMAL(11, 8) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    sitio_web VARCHAR(255) NULL,
    facilidades JSON NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ciudad (ciudad),
    INDEX idx_nombre (nombre_estadio),
    INDEX idx_capacidad (capacidad)
) ENGINE=InnoDB;

-- =============================================
-- 4. TABLA FECHAS DE EVENTOS
-- =============================================
CREATE TABLE IF NOT EXISTS Fecha (
    id_fecha INT PRIMARY KEY AUTO_INCREMENT,
    id_evento INT NOT NULL,
    id_estadio INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    fecha_fin DATE NULL,
    hora_fin TIME NULL,
    precio_base DECIMAL(10, 2) DEFAULT 0.00,
    estado ENUM('programado', 'en_curso', 'finalizado', 'cancelado') DEFAULT 'programado',
    tickets_disponibles INT DEFAULT 0,
    tickets_vendidos INT DEFAULT 0,
    fecha_limite_venta DATETIME NULL,
    notas TEXT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_evento) REFERENCES Evento(id_evento) ON DELETE CASCADE,
    FOREIGN KEY (id_estadio) REFERENCES Estadios(id_estadio) ON DELETE CASCADE,
    INDEX idx_evento (id_evento),
    INDEX idx_estadio (id_estadio),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado),
    INDEX idx_tickets_disponibles (tickets_disponibles)
) ENGINE=InnoDB;

-- =============================================
-- 5. TABLA ZONAS Y FILAS
-- =============================================
CREATE TABLE IF NOT EXISTS Zonas_Filas (
    id_zona_fila INT PRIMARY KEY AUTO_INCREMENT,
    id_estadio INT NOT NULL,
    zona VARCHAR(100) NOT NULL,
    fila VARCHAR(50) NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    precio_multiplicador DECIMAL(5, 2) DEFAULT 1.00,
    descripcion TEXT NULL,
    color_mapa VARCHAR(7) DEFAULT '#cccccc',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_estadio) REFERENCES Estadios(id_estadio) ON DELETE CASCADE,
    UNIQUE KEY unique_zona_fila_estadio (id_estadio, zona, fila),
    INDEX idx_estadio (id_estadio),
    INDEX idx_zona (zona),
    INDEX idx_cantidad (cantidad)
) ENGINE=InnoDB;

-- =============================================
-- 6. TABLA TICKETS
-- =============================================
CREATE TABLE IF NOT EXISTS Ticket (
    id_ticket INT PRIMARY KEY AUTO_INCREMENT,
    id_zona_fila INT NOT NULL,
    id_fecha INT NOT NULL,
    id_usuario INT NULL,
    asiento VARCHAR(20) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    qrcode VARCHAR(255) NULL,
    id_activo TINYINT DEFAULT 1 COMMENT '1=activo, 2=usado, 0=cancelado, 3=reembolsado',
    fecha_compra TIMESTAMP NULL,
    fecha_uso TIMESTAMP NULL,
    metodo_pago ENUM('tarjeta', 'efectivo', 'transferencia', 'paypal') DEFAULT 'tarjeta',
    referencia_pago VARCHAR(100) NULL,
    descuento_aplicado DECIMAL(5, 2) DEFAULT 0.00,
    impuestos DECIMAL(10, 2) DEFAULT 0.00,
    total_pagado DECIMAL(10, 2) NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_zona_fila) REFERENCES Zonas_Filas(id_zona_fila) ON DELETE CASCADE,
    FOREIGN KEY (id_fecha) REFERENCES Fecha(id_fecha) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    UNIQUE KEY unique_asiento_fecha (id_fecha, id_zona_fila, asiento),
    INDEX idx_usuario (id_usuario),
    INDEX idx_fecha (id_fecha),
    INDEX idx_zona_fila (id_zona_fila),
    INDEX idx_activo (id_activo),
    INDEX idx_precio (precio),
    INDEX idx_fecha_compra (fecha_compra),
    INDEX idx_qrcode (qrcode)
) ENGINE=InnoDB;

-- =============================================
-- 7. TABLA TARJETAS (OPCIONAL - DATOS SENSIBLES)
-- =============================================
CREATE TABLE IF NOT EXISTS Tarjetas (
    id_tarjeta INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    alias_tarjeta VARCHAR(50) NOT NULL,
    ultimos_4_digitos VARCHAR(4) NOT NULL,
    tipo_tarjeta ENUM('visa', 'mastercard', 'amex') NOT NULL,
    mes_expiracion TINYINT NOT NULL,
    ano_expiracion SMALLINT NOT NULL,
    titular VARCHAR(200) NOT NULL,
    principal BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_activa (activa)
) ENGINE=InnoDB;

-- =============================================
-- 8. TABLA TRANSACCIONES
-- =============================================
CREATE TABLE IF NOT EXISTS Transacciones (
    id_transaccion INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    tipo_transaccion ENUM('compra', 'reembolso', 'cancelacion') NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    moneda VARCHAR(3) DEFAULT 'MXN',
    estado ENUM('pendiente', 'completada', 'fallida', 'cancelada') DEFAULT 'pendiente',
    referencia_externa VARCHAR(255) NULL,
    metodo_pago ENUM('tarjeta', 'efectivo', 'transferencia', 'paypal') NOT NULL,
    detalles JSON NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_completada TIMESTAMP NULL,
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_usuario (id_usuario),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo_transaccion),
    INDEX idx_fecha (fecha_creacion)
) ENGINE=InnoDB;

-- =============================================
-- 9. TABLA CARRITOS DE COMPRA
-- =============================================
CREATE TABLE IF NOT EXISTS Carrito (
    id_carrito INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_ticket INT NOT NULL,
    cantidad INT DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 15 MINUTE),
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_ticket) REFERENCES Ticket(id_ticket) ON DELETE CASCADE,
    UNIQUE KEY unique_user_ticket (id_usuario, id_ticket),
    INDEX idx_usuario (id_usuario),
    INDEX idx_expiracion (fecha_expiracion)
) ENGINE=InnoDB;

-- =============================================
-- 10. TABLA LOG DE ACTIVIDADES
-- =============================================
CREATE TABLE IF NOT EXISTS Log_Actividades (
    id_log INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50) NULL,
    id_registro INT NULL,
    datos_anteriores JSON NULL,
    datos_nuevos JSON NULL,
    ip_usuario VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    INDEX idx_usuario (id_usuario),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha_accion),
    INDEX idx_tabla (tabla_afectada)
) ENGINE=InnoDB;

-- =============================================
-- DATOS DE EJEMPLO E INICIALIZACIÓN
-- =============================================

-- Usuario administrador por defecto
INSERT IGNORE INTO usuarios (
    nombre_usuario, nombre, apellido, correo, password, rol, verificado
) VALUES (
    'admin', 'Administrador', 'Sistema', 'admin@tickets.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'admin', TRUE
);

-- Usuario predeterminado para tickets disponibles
INSERT IGNORE INTO usuarios (
    nombre_usuario, nombre, apellido, correo, password, rol, verificado
) VALUES (
    'sistema', 'Sistema', 'Tickets', 'sistema@tickets.com', 
    'sistema_interno', 'admin', TRUE
);

-- Estadio de ejemplo
INSERT IGNORE INTO Estadios (
    nombre_estadio, ubicacion_estadio, ciudad, capacidad, foto_estadio, mapa_estadio
) VALUES (
    'Foro Sol', 'Av. Viaducto Tlalpan 3465, Granjas México, Iztacalco, CDMX', 'Ciudad de México', 
    65000, 'src/img/foro_sol.jpg', 'src/img/mapa_foro_sol.jpg'
);

-- Evento de ejemplo
INSERT IGNORE INTO Evento (
    nombre_evento, descripcion_evento, foto, categoria, artista
) VALUES (
    'BZRP Music Sessions Live', 'Experiencia única con los mejores hits de Bizarrap en vivo', 
    'src/img/bzrp_live.jpg', 'Concierto', 'Bizarrap'
);

-- Fecha de ejemplo
INSERT IGNORE INTO Fecha (
    id_evento, id_estadio, fecha, hora, precio_base, tickets_disponibles
) VALUES (
    1, 1, '2026-07-15', '20:00:00', 150.00, 1000
);

-- Zonas de ejemplo
INSERT IGNORE INTO Zonas_Filas (id_estadio, zona, fila, cantidad, precio_multiplicador) VALUES
(1, 'VIP', 'A', 50, 3.00),
(1, 'VIP', 'B', 50, 2.50),
(1, 'Preferente', 'C', 100, 2.00),
(1, 'Preferente', 'D', 100, 2.00),
(1, 'General', 'E', 200, 1.50),
(1, 'General', 'F', 200, 1.50),
(1, 'Gradas', 'G', 300, 1.00);

-- =============================================
-- TRIGGERS PARA AUTOMATIZACIÓN
-- =============================================

-- Trigger para actualizar contador de tickets vendidos
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_tickets_vendidos 
AFTER UPDATE ON Ticket
FOR EACH ROW
BEGIN
    IF OLD.id_usuario IS NULL AND NEW.id_usuario IS NOT NULL THEN
        UPDATE Fecha 
        SET tickets_vendidos = tickets_vendidos + 1,
            tickets_disponibles = tickets_disponibles - 1
        WHERE id_fecha = NEW.id_fecha;
    ELSEIF OLD.id_usuario IS NOT NULL AND NEW.id_usuario IS NULL THEN
        UPDATE Fecha 
        SET tickets_vendidos = tickets_vendidos - 1,
            tickets_disponibles = tickets_disponibles + 1
        WHERE id_fecha = NEW.id_fecha;
    END IF;
END//
DELIMITER ;

-- Trigger para log de actividades
DELIMITER //
CREATE TRIGGER IF NOT EXISTS log_ticket_changes 
AFTER UPDATE ON Ticket
FOR EACH ROW
BEGIN
    INSERT INTO Log_Actividades (
        id_usuario, accion, tabla_afectada, id_registro, datos_anteriores, datos_nuevos
    ) VALUES (
        NEW.id_usuario, 'UPDATE', 'Ticket', NEW.id_ticket,
        JSON_OBJECT('id_activo', OLD.id_activo, 'id_usuario', OLD.id_usuario),
        JSON_OBJECT('id_activo', NEW.id_activo, 'id_usuario', NEW.id_usuario)
    );
END//
DELIMITER ;

-- =============================================
-- VISTAS ÚTILES
-- =============================================

-- Vista de tickets completos
CREATE OR REPLACE VIEW vista_tickets_completos AS
SELECT 
    t.id_ticket,
    t.asiento,
    t.precio,
    t.qrcode,
    t.id_activo,
    t.fecha_compra,
    u.nombre_usuario,
    u.nombre,
    u.apellido,
    e.nombre_evento,
    e.categoria,
    f.fecha,
    f.hora,
    es.nombre_estadio,
    es.ubicacion_estadio,
    zf.zona,
    zf.fila,
    CASE t.id_activo
        WHEN 1 THEN 'Activo'
        WHEN 2 THEN 'Usado'
        WHEN 0 THEN 'Cancelado'
        WHEN 3 THEN 'Reembolsado'
        ELSE 'Desconocido'
    END as estado_texto
FROM Ticket t
LEFT JOIN usuarios u ON t.id_usuario = u.id_usuario
JOIN Zonas_Filas zf ON t.id_zona_fila = zf.id_zona_fila
JOIN Fecha f ON t.id_fecha = f.id_fecha
JOIN Evento e ON f.id_evento = e.id_evento
JOIN Estadios es ON f.id_estadio = es.id_estadio;

-- Vista de eventos con estadísticas
CREATE OR REPLACE VIEW vista_eventos_estadisticas AS
SELECT 
    e.id_evento,
    e.nombre_evento,
    e.categoria,
    COUNT(f.id_fecha) as total_fechas,
    SUM(f.tickets_disponibles) as total_tickets_disponibles,
    SUM(f.tickets_vendidos) as total_tickets_vendidos,
    MIN(f.fecha) as primera_fecha,
    MAX(f.fecha) as ultima_fecha,
    AVG(t.precio) as precio_promedio,
    SUM(CASE WHEN t.id_usuario IS NOT NULL THEN t.precio ELSE 0 END) as ingresos_totales
FROM Evento e
LEFT JOIN Fecha f ON e.id_evento = f.id_evento
LEFT JOIN Ticket t ON f.id_fecha = t.id_fecha
WHERE e.estado = 'activo'
GROUP BY e.id_evento, e.nombre_evento, e.categoria;

-- =============================================
-- FUNCIONES ÚTILES
-- =============================================

-- Función para calcular precio con multiplicador
DELIMITER //
CREATE FUNCTION IF NOT EXISTS calcular_precio_ticket(
    precio_base DECIMAL(10,2), 
    multiplicador DECIMAL(5,2)
) 
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    RETURN ROUND(precio_base * multiplicador, 2);
END//
DELIMITER ;

-- =============================================
-- PROCEDIMIENTOS ALMACENADOS
-- =============================================

-- Procedimiento para generar tickets automáticamente
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS generar_tickets_fecha(
    IN p_id_fecha INT,
    IN p_incluir_sistema BOOLEAN DEFAULT TRUE
)
BEGIN
    DECLARE v_precio_base DECIMAL(10,2);
    DECLARE v_id_zona_fila INT;
    DECLARE v_zona VARCHAR(100);
    DECLARE v_fila VARCHAR(50);
    DECLARE v_cantidad INT;
    DECLARE v_multiplicador DECIMAL(5,2);
    DECLARE v_contador INT;
    DECLARE v_precio_final DECIMAL(10,2);
    DECLARE done INT DEFAULT FALSE;
    
    -- Cursor para zonas y filas
    DECLARE cur_zonas CURSOR FOR 
        SELECT zf.id_zona_fila, zf.zona, zf.fila, zf.cantidad, zf.precio_multiplicador
        FROM Zonas_Filas zf
        JOIN Fecha f ON zf.id_estadio = f.id_estadio
        WHERE f.id_fecha = p_id_fecha;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Obtener precio base
    SELECT precio_base INTO v_precio_base 
    FROM Fecha WHERE id_fecha = p_id_fecha;
    
    OPEN cur_zonas;
    
    zona_loop: LOOP
        FETCH cur_zonas INTO v_id_zona_fila, v_zona, v_fila, v_cantidad, v_multiplicador;
        
        IF done THEN
            LEAVE zona_loop;
        END IF;
        
        SET v_precio_final = calcular_precio_ticket(v_precio_base, v_multiplicador);
        SET v_contador = 1;
        
        -- Generar tickets para esta zona/fila
        WHILE v_contador <= v_cantidad DO
            INSERT INTO Ticket (
                id_zona_fila, id_fecha, id_usuario, asiento, precio, 
                qrcode, fecha_creacion
            ) VALUES (
                v_id_zona_fila, p_id_fecha, 
                CASE WHEN p_incluir_sistema THEN 1 ELSE NULL END,
                CONCAT(v_fila, '-', LPAD(v_contador, 3, '0')),
                v_precio_final,
                CONCAT('codigoqr', LAST_INSERT_ID(), '.png'),
                NOW()
            );
            
            SET v_contador = v_contador + 1;
        END WHILE;
        
    END LOOP;
    
    CLOSE cur_zonas;
    
    -- Actualizar contadores de la fecha
    UPDATE Fecha 
    SET tickets_disponibles = (
        SELECT COUNT(*) FROM Ticket WHERE id_fecha = p_id_fecha AND id_usuario = 1
    ) 
    WHERE id_fecha = p_id_fecha;
    
END//
DELIMITER ;

-- =============================================
-- ÍNDICES ADICIONALES PARA RENDIMIENTO
-- =============================================

-- Índices compuestos para consultas comunes
CREATE INDEX IF NOT EXISTS idx_ticket_usuario_fecha ON Ticket(id_usuario, id_fecha);
CREATE INDEX IF NOT EXISTS idx_ticket_fecha_activo ON Ticket(id_fecha, id_activo);
CREATE INDEX IF NOT EXISTS idx_evento_categoria_estado ON Evento(categoria, estado);
CREATE INDEX IF NOT EXISTS idx_fecha_evento_fecha ON Fecha(id_evento, fecha);

-- =============================================
-- CONFIGURACIÓN DE CHARSET Y COLLATION
-- =============================================
ALTER DATABASE Eventos_Estadios CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- FINALIZACIÓN
-- =============================================
SELECT 'Database schema created successfully!' as mensaje;