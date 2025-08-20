<?php
/**
 * Santiago Tickets - Database Connection
 * Automatic database initialization and connection management
 */

// Configuración de la base de datos
$host = $_ENV['DB_HOST'] ?? 'localhost'; // Dirección del servidor (localhost si es local)
$dbname = $_ENV['DB_NAME'] ?? 'Eventos_Estadios'; // Nombre de la base de datos
$username = $_ENV['DB_USER'] ?? 'root'; // Usuario de MySQL
$password = $_ENV['DB_PASS'] ?? ''; // Contraseña de MySQL

// Include required files
require_once __DIR__ . '/initialize_db.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize secure session
initSecureSession();

try {
    // First attempt to connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Verificar si la base de datos necesita inicialización
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'admin'");
    $admin_count = $stmt->fetch()['count'];
    
    if ($admin_count == 0) {
        // Database needs initialization
        populateBasicData($pdo);
    }
    
    // Mensaje de éxito (opcional para debugging)
    // echo "✅ Conexión exitosa a la base de datos";
    
} catch (PDOException $e) {
    // Si la base de datos no existe, intentar crearla
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            // Attempt to initialize database
            if (initializeDatabase()) {
                // Reconnect after initialization
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } else {
                throw new Exception("Failed to initialize database");
            }
        } catch (Exception $init_e) {
            echo "❌ Error inicializando la base de datos: " . $init_e->getMessage();
            echo "<br>Por favor, ejecuta el archivo initialize_db.php manualmente o verifica la configuración.";
            exit;
        }
    } else {
        // Other connection error
        echo "❌ Error de conexión: " . $e->getMessage();
        echo "<br>Por favor, verifica tu configuración de base de datos.";
        exit;
    }
}

// Función auxiliar para verificar si es la primera ejecución
function isFirstRun() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
        return $stmt->fetch()['count'] == 0;
    } catch (PDOException $e) {
        return true;
    }
}

// Función para obtener configuración del sistema
function getSystemConfig() {
    return [
        'app_name' => 'Santiago Tickets',
        'version' => '1.0.0',
        'timezone' => 'America/Mexico_City',
        'currency' => 'MXN',
        'max_tickets_per_user' => 10,
        'session_timeout' => 3600, // 1 hour
        'qr_code_path' => 'src/codigos_qr/',
        'upload_path' => 'src/uploads/',
        'default_event_image' => 'src/img/default_event.jpg',
        'default_stadium_image' => 'src/img/default_stadium.jpg'
    ];
}

// Función para logging de errores
function logError($message, $context = []) {
    $log_entry = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $log_entry .= " - Context: " . json_encode($context);
    }
    $log_entry .= "\n";
    
    // Crear directorio de logs si no existe
    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_dir . '/app_' . date('Y-m-d') . '.log', $log_entry, FILE_APPEND);
}

// Función para validar entrada
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Función para verificar autenticación
function requireAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        $current_url = $_SERVER['REQUEST_URI'];
        header("Location: login.php?redirect=" . urlencode($current_url));
        exit;
    }
}

// Función para verificar rol
function requireRole($required_role) {
    requireAuth();
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $roles_hierarchy = ['cliente' => 1, 'organizador' => 2, 'admin' => 3];
    
    if (!$user || $roles_hierarchy[$user['rol']] < $roles_hierarchy[$required_role]) {
        header("HTTP/1.1 403 Forbidden");
        echo "Acceso denegado. Rol requerido: $required_role";
        exit;
    }
}

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');
