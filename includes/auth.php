<?php
/**
 * Authentication and Authorization Helper Functions
 * Santiago Tickets - Security Layer
 */

if (!defined('SECURITY_KEY')) {
    define('SECURITY_KEY', 'SantiagoTickets2024');
}

/**
 * Verificar si el usuario está autenticado
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar rol del usuario
 */
function hasRole($required_role) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $user_role = $_SESSION['rol'] ?? 'cliente';
    
    $role_hierarchy = [
        'admin' => 3,
        'organizador' => 2,
        'cliente' => 1
    ];
    
    $user_level = $role_hierarchy[$user_role] ?? 0;
    $required_level = $role_hierarchy[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

/**
 * Requiere autenticación
 */
function requireAuth($redirect_to = 'login.php') {
    if (!isAuthenticated()) {
        $current_url = $_SERVER['REQUEST_URI'];
        header("Location: $redirect_to?redirect=" . urlencode($current_url));
        exit;
    }
}

/**
 * Requiere rol específico
 */
function requireRole($required_role, $redirect_to = 'index.php') {
    requireAuth();
    
    if (!hasRole($required_role)) {
        header("Location: $redirect_to?error=access_denied");
        exit;
    }
}

/**
 * Sanitizar entrada de usuario
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar token CSRF
 */
function validateCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar token CSRF
 */
function generateCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar contraseña segura
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'La contraseña debe contener al menos una letra mayúscula';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'La contraseña debe contener al menos una letra minúscula';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'La contraseña debe contener al menos un número';
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Limpiar sesión de usuario
 */
function logout() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Verificar límite de intentos de login
 */
function checkLoginAttempts($identifier, $max_attempts = 5, $lockout_time = 900) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    $lockout_key = 'login_lockout_' . md5($identifier);
    
    // Verificar si está bloqueado
    if (isset($_SESSION[$lockout_key]) && $_SESSION[$lockout_key] > time()) {
        return false;
    }
    
    // Verificar intentos
    $attempts = $_SESSION[$attempts_key] ?? 0;
    if ($attempts >= $max_attempts) {
        $_SESSION[$lockout_key] = time() + $lockout_time;
        return false;
    }
    
    return true;
}

/**
 * Registrar intento de login fallido
 */
function recordFailedLogin($identifier) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    $_SESSION[$attempts_key] = ($_SESSION[$attempts_key] ?? 0) + 1;
}

/**
 * Limpiar intentos de login exitoso
 */
function clearLoginAttempts($identifier) {
    $attempts_key = 'login_attempts_' . md5($identifier);
    $lockout_key = 'login_lockout_' . md5($identifier);
    
    unset($_SESSION[$attempts_key]);
    unset($_SESSION[$lockout_key]);
}

/**
 * Obtener información del usuario actual
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'rol' => $_SESSION['rol'] ?? 'cliente'
    ];
}

/**
 * Verificar propiedad de recurso
 */
function canAccessResource($resource_user_id) {
    $current_user = getCurrentUser();
    
    if (!$current_user) {
        return false;
    }
    
    // Admin puede acceder a todo
    if ($current_user['rol'] === 'admin') {
        return true;
    }
    
    // Usuario puede acceder a sus propios recursos
    return $current_user['id'] == $resource_user_id;
}

/**
 * Log de seguridad
 */
function logSecurityEvent($event, $details = []) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'user_id' => $_SESSION['user_id'] ?? null,
        'event' => $event,
        'details' => $details
    ];
    
    error_log('[SECURITY] ' . json_encode($log_entry));
}

/**
 * Inicializar sesión segura
 */
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración segura de sesión
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        
        session_start();
        
        // Regenerar ID de sesión periódicamente
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}