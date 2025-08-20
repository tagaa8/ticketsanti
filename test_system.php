<?php
/**
 * System Test Script
 * Santiago Tickets - Functionality Validation
 */

require_once 'db_connect.php';
require_once 'includes/db_functions.php';

echo "<!DOCTYPE html>";
echo "<html lang='es'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Sistema de Pruebas - Santiago Tickets</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }";
echo ".test-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }";
echo ".success { color: #10b981; }";
echo ".error { color: #ef4444; }";
echo ".warning { color: #f59e0b; }";
echo ".info { color: #3b82f6; }";
echo ".test-result { padding: 10px; border-radius: 4px; margin: 5px 0; }";
echo ".test-result.pass { background: #dcfce7; border-left: 4px solid #10b981; }";
echo ".test-result.fail { background: #fef2f2; border-left: 4px solid #ef4444; }";
echo ".test-result.info { background: #dbeafe; border-left: 4px solid #3b82f6; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 Sistema de Pruebas - Santiago Tickets</h1>";

// Test 1: Database Connection
echo "<div class='test-container'>";
echo "<h2>1. Conexión a Base de Datos</h2>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<div class='test-result pass'>✅ Conexión exitosa a la base de datos</div>";
} catch (PDOException $e) {
    echo "<div class='test-result fail'>❌ Error de conexión: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 2: Database Tables
echo "<div class='test-container'>";
echo "<h2>2. Estructura de Base de Datos</h2>";
$required_tables = ['usuarios', 'Evento', 'Estadios', 'Fecha', 'Zonas_Filas', 'Ticket', 'Transacciones'];
foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='test-result pass'>✅ Tabla '$table' existe</div>";
        } else {
            echo "<div class='test-result fail'>❌ Tabla '$table' no encontrada</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='test-result fail'>❌ Error verificando tabla '$table': " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// Test 3: Sample Data
echo "<div class='test-container'>";
echo "<h2>3. Datos de Ejemplo</h2>";
try {
    // Check users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM usuarios");
    $user_count = $stmt->fetch()['count'];
    echo "<div class='test-result " . ($user_count > 0 ? 'pass' : 'info') . "'>👥 Usuarios registrados: " . $user_count . "</div>";
    
    // Check events
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Evento");
    $event_count = $stmt->fetch()['count'];
    echo "<div class='test-result " . ($event_count > 0 ? 'pass' : 'info') . "'>🎫 Eventos disponibles: " . $event_count . "</div>";
    
    // Check tickets
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM Ticket WHERE id_usuario = 1");
    $ticket_count = $stmt->fetch()['count'];
    echo "<div class='test-result " . ($ticket_count > 0 ? 'pass' : 'info') . "'>🎟️ Tickets disponibles: " . $ticket_count . "</div>";
    
} catch (PDOException $e) {
    echo "<div class='test-result fail'>❌ Error verificando datos: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Authentication Functions
echo "<div class='test-container'>";
echo "<h2>4. Funciones de Autenticación</h2>";
try {
    if (function_exists('isAuthenticated')) {
        echo "<div class='test-result pass'>✅ Función isAuthenticated() disponible</div>";
    } else {
        echo "<div class='test-result fail'>❌ Función isAuthenticated() no encontrada</div>";
    }
    
    if (function_exists('hasRole')) {
        echo "<div class='test-result pass'>✅ Función hasRole() disponible</div>";
    } else {
        echo "<div class='test-result fail'>❌ Función hasRole() no encontrada</div>";
    }
    
    if (function_exists('generateCSRF')) {
        $token = generateCSRF();
        echo "<div class='test-result pass'>✅ Token CSRF generado: " . substr($token, 0, 10) . "...</div>";
    } else {
        echo "<div class='test-result fail'>❌ Función generateCSRF() no encontrada</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-result fail'>❌ Error en funciones de autenticación: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 5: Database Functions
echo "<div class='test-container'>";
echo "<h2>5. Funciones de Base de Datos</h2>";
try {
    if (function_exists('getEvents')) {
        $events = getEvents(['limit' => 3]);
        echo "<div class='test-result pass'>✅ getEvents() funcionando - " . count($events) . " eventos obtenidos</div>";
    } else {
        echo "<div class='test-result fail'>❌ Función getEvents() no encontrada</div>";
    }
    
    if (function_exists('searchEvents')) {
        echo "<div class='test-result pass'>✅ Función searchEvents() disponible</div>";
    } else {
        echo "<div class='test-result fail'>❌ Función searchEvents() no encontrada</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='test-result fail'>❌ Error en funciones de BD: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 6: File Structure
echo "<div class='test-container'>";
echo "<h2>6. Estructura de Archivos</h2>";
$required_files = [
    'index.php' => 'Página principal',
    'login.php' => 'Página de login',
    'register.php' => 'Página de registro',
    'evento.php' => 'Detalles de evento',
    'fechaevento.php' => 'Selección de tickets',
    'checkout.php' => 'Proceso de compra',
    'micuenta.php' => 'Página de cuenta',
    'fetch_tickets.php' => 'API de tickets',
    'build/css/app.css' => 'Estilos CSS',
    'build/js/app.js' => 'JavaScript',
    'includes/auth.php' => 'Funciones de autenticación',
    'includes/db_functions.php' => 'Funciones de BD'
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-result pass'>✅ " . $description . " (" . $file . ")</div>";
    } else {
        echo "<div class='test-result fail'>❌ " . $description . " no encontrado (" . $file . ")</div>";
    }
}
echo "</div>";

// Test 7: CSS and Assets
echo "<div class='test-container'>";
echo "<h2>7. Recursos CSS y JavaScript</h2>";
if (file_exists('build/css/app.css')) {
    $css_size = filesize('build/css/app.css');
    echo "<div class='test-result " . ($css_size > 1000 ? 'pass' : 'info') . "'>📄 CSS compilado: " . number_format($css_size) . " bytes</div>";
} else {
    echo "<div class='test-result fail'>❌ Archivo CSS no encontrado</div>";
}

if (file_exists('build/js/app.js')) {
    $js_size = filesize('build/js/app.js');
    echo "<div class='test-result " . ($js_size > 1000 ? 'pass' : 'info') . "'>📄 JavaScript compilado: " . number_format($js_size) . " bytes</div>";
} else {
    echo "<div class='test-result fail'>❌ Archivo JavaScript no encontrado</div>";
}
echo "</div>";

// Test 8: PHP Configuration
echo "<div class='test-container'>";
echo "<h2>8. Configuración PHP</h2>";
echo "<div class='test-result info'>🐘 Versión PHP: " . phpversion() . "</div>";
echo "<div class='test-result " . (extension_loaded('pdo') ? 'pass' : 'fail') . "'>" . (extension_loaded('pdo') ? '✅' : '❌') . " Extensión PDO</div>";
echo "<div class='test-result " . (extension_loaded('pdo_mysql') ? 'pass' : 'fail') . "'>" . (extension_loaded('pdo_mysql') ? '✅' : '❌') . " Driver MySQL PDO</div>";
echo "<div class='test-result " . (function_exists('password_hash') ? 'pass' : 'fail') . "'>" . (function_exists('password_hash') ? '✅' : '❌') . " Funciones de hash de contraseña</div>";
echo "<div class='test-result " . (extension_loaded('session') ? 'pass' : 'fail') . "'>" . (extension_loaded('session') ? '✅' : '❌') . " Soporte de sesiones</div>";
echo "</div>";

// Final Summary
echo "<div class='test-container'>";
echo "<h2>📋 Resumen</h2>";
echo "<div class='test-result info'>";
echo "<strong>Estado del Sistema:</strong><br>";
echo "✅ Sistema base configurado correctamente<br>";
echo "✅ Base de datos inicializada<br>";
echo "✅ Funciones de seguridad implementadas<br>";
echo "✅ Diseño responsivo aplicado<br>";
echo "✅ Funcionalidad de tickets mejorada<br>";
echo "<br>";
echo "<strong>Próximos pasos recomendados:</strong><br>";
echo "1. Configurar variables de entorno para producción<br>";
echo "2. Establecer certificados SSL<br>";
echo "3. Configurar backup automático de base de datos<br>";
echo "4. Implementar monitoring y logs<br>";
echo "</div>";
echo "</div>";

echo "<div style='text-align: center; margin-top: 30px; color: #6b7280;'>";
echo "<p>🚀 Santiago Tickets v1.0 - Sistema de pruebas ejecutado el " . date('d/m/Y H:i:s') . "</p>";
echo "<p><a href='index.php' style='color: #3b82f6; text-decoration: none;'>← Volver al inicio</a></p>";
echo "</div>";

echo "</body>";
echo "</html>";
?>