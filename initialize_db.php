<?php
/**
 * Santiago Tickets - Database Initialization System
 * Automatic database creation and setup
 * 
 * This script automatically creates the database structure
 * if it doesn't exist when the application is first accessed.
 */

// Database configuration
$host = 'localhost';
$dbname = 'Eventos_Estadios';
$username = 'root';
$password = 'Santi12345678';

/**
 * Initialize database connection and create structure if needed
 */
function initializeDatabase() {
    global $host, $dbname, $username, $password;
    
    try {
        // First, connect without specifying database to check if it exists
        $pdo_check = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $pdo_check->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $pdo_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        $database_exists = $stmt->fetch() !== false;
        
        if (!$database_exists) {
            echo "🔧 Database doesn't exist. Creating database structure...\n";
            createDatabaseStructure($pdo_check);
        } else {
            // Check if tables exist
            $pdo_db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo_db->query("SHOW TABLES LIKE 'usuarios'");
            $tables_exist = $stmt->fetch() !== false;
            
            if (!$tables_exist) {
                echo "🔧 Database exists but tables are missing. Creating table structure...\n";
                createTablesStructure($pdo_db);
            } else {
                // Verify critical tables have data
                verifyAndPopulateBasicData($pdo_db);
            }
        }
        
        echo "✅ Database initialization completed successfully!\n";
        return true;
        
    } catch (PDOException $e) {
        echo "❌ Database initialization failed: " . $e->getMessage() . "\n";
        return false;
    }
}

/**
 * Create complete database structure from scratch
 */
function createDatabaseStructure($pdo) {
    global $dbname;
    
    // Read and execute the schema file
    $schema_file = __DIR__ . '/database_schema.sql';
    
    if (file_exists($schema_file)) {
        $sql_content = file_get_contents($schema_file);
        
        // Split SQL content by statements
        $statements = explode(';', $sql_content);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !str_starts_with($statement, '--') && !str_starts_with($statement, '/*')) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignore some common warnings for CREATE IF NOT EXISTS
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "Warning: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    } else {
        // Fallback: Create basic structure manually
        createBasicStructure($pdo);
    }
}

/**
 * Create tables in existing database
 */
function createTablesStructure($pdo) {
    $tables = [
        // Users table
        "CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INT PRIMARY KEY AUTO_INCREMENT,
            nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            correo VARCHAR(150) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            rol ENUM('admin', 'organizador', 'cliente') DEFAULT 'cliente',
            telefono VARCHAR(20) NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            activo BOOLEAN DEFAULT TRUE,
            INDEX idx_email (correo),
            INDEX idx_username (nombre_usuario)
        ) ENGINE=InnoDB",
        
        // Events table
        "CREATE TABLE IF NOT EXISTS Evento (
            id_evento INT PRIMARY KEY AUTO_INCREMENT,
            nombre_evento VARCHAR(200) NOT NULL,
            descripcion_evento TEXT,
            foto VARCHAR(255) NULL,
            categoria VARCHAR(100) DEFAULT 'Concierto',
            estado ENUM('activo', 'cancelado', 'finalizado') DEFAULT 'activo',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_categoria (categoria),
            INDEX idx_estado (estado)
        ) ENGINE=InnoDB",
        
        // Stadiums table
        "CREATE TABLE IF NOT EXISTS Estadios (
            id_estadio INT PRIMARY KEY AUTO_INCREMENT,
            nombre_estadio VARCHAR(200) NOT NULL,
            ubicacion_estadio VARCHAR(300) NOT NULL,
            ciudad VARCHAR(100) NOT NULL,
            capacidad INT NOT NULL DEFAULT 0,
            foto_estadio VARCHAR(255) NULL,
            mapa_estadio VARCHAR(255) NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ciudad (ciudad),
            INDEX idx_nombre (nombre_estadio)
        ) ENGINE=InnoDB",
        
        // Event dates table
        "CREATE TABLE IF NOT EXISTS Fecha (
            id_fecha INT PRIMARY KEY AUTO_INCREMENT,
            id_evento INT NOT NULL,
            id_estadio INT NOT NULL,
            fecha DATE NOT NULL,
            hora TIME NOT NULL,
            precio_base DECIMAL(10, 2) DEFAULT 0.00,
            tickets_disponibles INT DEFAULT 0,
            tickets_vendidos INT DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_evento) REFERENCES Evento(id_evento) ON DELETE CASCADE,
            FOREIGN KEY (id_estadio) REFERENCES Estadios(id_estadio) ON DELETE CASCADE,
            INDEX idx_evento (id_evento),
            INDEX idx_estadio (id_estadio),
            INDEX idx_fecha (fecha)
        ) ENGINE=InnoDB",
        
        // Zones and rows table
        "CREATE TABLE IF NOT EXISTS Zonas_Filas (
            id_zona_fila INT PRIMARY KEY AUTO_INCREMENT,
            id_estadio INT NOT NULL,
            zona VARCHAR(100) NOT NULL,
            fila VARCHAR(50) NOT NULL,
            cantidad INT NOT NULL DEFAULT 0,
            precio_multiplicador DECIMAL(5, 2) DEFAULT 1.00,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_estadio) REFERENCES Estadios(id_estadio) ON DELETE CASCADE,
            UNIQUE KEY unique_zona_fila_estadio (id_estadio, zona, fila),
            INDEX idx_estadio (id_estadio),
            INDEX idx_zona (zona)
        ) ENGINE=InnoDB",
        
        // Tickets table
        "CREATE TABLE IF NOT EXISTS Ticket (
            id_ticket INT PRIMARY KEY AUTO_INCREMENT,
            id_zona_fila INT NOT NULL,
            id_fecha INT NOT NULL,
            id_usuario INT NULL,
            asiento VARCHAR(20) NOT NULL,
            precio DECIMAL(10, 2) NOT NULL,
            qrcode VARCHAR(255) NULL,
            id_activo TINYINT DEFAULT 1 COMMENT '1=activo, 2=usado, 0=cancelado',
            fecha_compra TIMESTAMP NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_zona_fila) REFERENCES Zonas_Filas(id_zona_fila) ON DELETE CASCADE,
            FOREIGN KEY (id_fecha) REFERENCES Fecha(id_fecha) ON DELETE CASCADE,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
            UNIQUE KEY unique_asiento_fecha (id_fecha, id_zona_fila, asiento),
            INDEX idx_usuario (id_usuario),
            INDEX idx_fecha (id_fecha),
            INDEX idx_activo (id_activo)
        ) ENGINE=InnoDB",
        
        // Cards table (optional)
        "CREATE TABLE IF NOT EXISTS Tarjetas (
            id_tarjeta INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            alias_tarjeta VARCHAR(50) NOT NULL,
            ultimos_4_digitos VARCHAR(4) NOT NULL,
            tipo_tarjeta ENUM('visa', 'mastercard', 'amex') NOT NULL,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
            INDEX idx_usuario (id_usuario)
        ) ENGINE=InnoDB"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage() . "\n";
        }
    }
    
    // Insert basic data
    populateBasicData($pdo);
}

/**
 * Create basic fallback structure
 */
function createBasicStructure($pdo) {
    global $dbname;
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $dbname");
    
    // Create basic tables
    createTablesStructure($pdo);
}

/**
 * Populate database with basic required data
 */
function populateBasicData($pdo) {
    try {
        // Insert system admin user
        $admin_exists = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE nombre_usuario = 'admin'")->fetch();
        if ($admin_exists['count'] == 0) {
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO usuarios (nombre_usuario, nombre, apellido, correo, password, rol) VALUES ('admin', 'Administrador', 'Sistema', 'admin@tickets.com', '$admin_password', 'admin')");
        }
        
        // Insert system user for available tickets
        $sistema_exists = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE nombre_usuario = 'sistema'")->fetch();
        if ($sistema_exists['count'] == 0) {
            $sistema_password = password_hash('sistema_interno', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO usuarios (nombre_usuario, nombre, apellido, correo, password, rol) VALUES ('sistema', 'Sistema', 'Tickets', 'sistema@tickets.com', '$sistema_password', 'admin')");
        }
        
        // Insert example stadium
        $stadium_exists = $pdo->query("SELECT COUNT(*) as count FROM Estadios WHERE nombre_estadio = 'Foro Sol'")->fetch();
        if ($stadium_exists['count'] == 0) {
            $pdo->exec("INSERT INTO Estadios (nombre_estadio, ubicacion_estadio, ciudad, capacidad) VALUES ('Foro Sol', 'Av. Viaducto Tlalpan 3465, CDMX', 'Ciudad de México', 65000)");
        }
        
        // Insert example zones for the stadium
        $zones_exist = $pdo->query("SELECT COUNT(*) as count FROM Zonas_Filas WHERE id_estadio = 1")->fetch();
        if ($zones_exist['count'] == 0) {
            $zones = [
                "INSERT INTO Zonas_Filas (id_estadio, zona, fila, cantidad, precio_multiplicador) VALUES (1, 'VIP', 'A', 50, 3.00)",
                "INSERT INTO Zonas_Filas (id_estadio, zona, fila, cantidad, precio_multiplicador) VALUES (1, 'VIP', 'B', 50, 2.50)",
                "INSERT INTO Zonas_Filas (id_estadio, zona, fila, cantidad, precio_multiplicador) VALUES (1, 'Preferente', 'C', 100, 2.00)",
                "INSERT INTO Zonas_Filas (id_estadio, zona, fila, cantidad, precio_multiplicador) VALUES (1, 'General', 'D', 200, 1.50)",
                "INSERT INTO Zonas_Filas (id_estadio, zona, fila, cantidad, precio_multiplicador) VALUES (1, 'Gradas', 'E', 300, 1.00)"
            ];
            
            foreach ($zones as $zone_sql) {
                $pdo->exec($zone_sql);
            }
        }
        
        // Insert example event
        $event_exists = $pdo->query("SELECT COUNT(*) as count FROM Evento WHERE nombre_evento = 'BZRP Music Sessions Live'")->fetch();
        if ($event_exists['count'] == 0) {
            $pdo->exec("INSERT INTO Evento (nombre_evento, descripcion_evento, categoria) VALUES ('BZRP Music Sessions Live', 'Experiencia única con los mejores hits de Bizarrap en vivo', 'Concierto')");
        }
        
        // Insert example event date
        $date_exists = $pdo->query("SELECT COUNT(*) as count FROM Fecha WHERE id_evento = 1")->fetch();
        if ($date_exists['count'] == 0) {
            $pdo->exec("INSERT INTO Fecha (id_evento, id_estadio, fecha, hora, precio_base) VALUES (1, 1, '2026-07-15', '20:00:00', 150.00)");
        }
        
        // Generate example tickets
        generateExampleTickets($pdo);
        
    } catch (PDOException $e) {
        echo "Error populating basic data: " . $e->getMessage() . "\n";
    }
}

/**
 * Verify and populate basic data if missing
 */
function verifyAndPopulateBasicData($pdo) {
    try {
        // Check if we have basic users
        $user_count = $pdo->query("SELECT COUNT(*) as count FROM usuarios")->fetch();
        if ($user_count['count'] < 2) {
            populateBasicData($pdo);
        }
        
        // Check if we have tickets
        $ticket_count = $pdo->query("SELECT COUNT(*) as count FROM Ticket")->fetch();
        if ($ticket_count['count'] == 0) {
            generateExampleTickets($pdo);
        }
        
    } catch (PDOException $e) {
        echo "Error verifying basic data: " . $e->getMessage() . "\n";
    }
}

/**
 * Generate example tickets for testing
 */
function generateExampleTickets($pdo) {
    try {
        // Check if tickets already exist
        $existing_tickets = $pdo->query("SELECT COUNT(*) as count FROM Ticket WHERE id_fecha = 1")->fetch();
        if ($existing_tickets['count'] > 0) {
            return; // Tickets already exist
        }
        
        // Get zones and generate tickets
        $zones = $pdo->query("
            SELECT zf.id_zona_fila, zf.zona, zf.fila, zf.cantidad, zf.precio_multiplicador,
                   f.precio_base
            FROM Zonas_Filas zf 
            JOIN Fecha f ON zf.id_estadio = f.id_estadio 
            WHERE f.id_fecha = 1
        ")->fetchAll();
        
        $ticket_counter = 1;
        
        foreach ($zones as $zone) {
            $precio_final = $zone['precio_base'] * $zone['precio_multiplicador'];
            
            for ($i = 1; $i <= $zone['cantidad']; $i++) {
                $asiento = $zone['fila'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $qrcode = 'codigoqr' . $ticket_counter . '.png';
                
                $pdo->exec("
                    INSERT INTO Ticket (id_zona_fila, id_fecha, id_usuario, asiento, precio, qrcode, id_activo) 
                    VALUES ({$zone['id_zona_fila']}, 1, 1, '$asiento', $precio_final, '$qrcode', 1)
                ");
                
                $ticket_counter++;
            }
        }
        
        // Update tickets count in Fecha table
        $total_tickets = $pdo->query("SELECT COUNT(*) as count FROM Ticket WHERE id_fecha = 1 AND id_usuario = 1")->fetch();
        $pdo->exec("UPDATE Fecha SET tickets_disponibles = {$total_tickets['count']} WHERE id_fecha = 1");
        
        echo "✅ Generated {$total_tickets['count']} example tickets\n";
        
    } catch (PDOException $e) {
        echo "Error generating tickets: " . $e->getMessage() . "\n";
    }
}

/**
 * Check if database needs initialization
 */
function needsInitialization() {
    global $host, $dbname, $username, $password;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
        $result = $stmt->fetch();
        return $result['count'] == 0;
    } catch (PDOException $e) {
        return true; // If we can't connect, assume we need initialization
    }
}

/**
 * Get initialization status
 */
function getInitializationStatus() {
    global $host, $dbname, $username, $password;
    
    $status = [
        'database_exists' => false,
        'tables_exist' => false,
        'data_populated' => false,
        'ready' => false
    ];
    
    try {
        // Check database
        $pdo_check = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $stmt = $pdo_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
        $status['database_exists'] = $stmt->fetch() !== false;
        
        if ($status['database_exists']) {
            // Check tables
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
            $status['tables_exist'] = $stmt->fetch() !== false;
            
            if ($status['tables_exist']) {
                // Check data
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
                $result = $stmt->fetch();
                $status['data_populated'] = $result['count'] > 0;
                
                $status['ready'] = $status['data_populated'];
            }
        }
        
    } catch (PDOException $e) {
        // Connection failed, database probably doesn't exist
    }
    
    return $status;
}

// If called directly, run initialization
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    echo "🚀 Starting database initialization...\n";
    
    if (initializeDatabase()) {
        echo "\n✅ Database is ready to use!\n";
        echo "📊 You can now access the application.\n\n";
        echo "Default admin credentials:\n";
        echo "Username: admin\n";
        echo "Password: admin123\n\n";
        echo "⚠️  Please change the default password after first login!\n";
    } else {
        echo "\n❌ Database initialization failed.\n";
        echo "Please check your database configuration and try again.\n";
    }
}
?>