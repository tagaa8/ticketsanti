<?php
// Configuración de la base de datos
$host = 'localhost'; // Dirección del servidor (localhost si es local)
$dbname = 'Eventos_Estadios'; // Nombre de la base de datos
$username = 'root'; // Usuario de MySQL
$password = 'Santi12345678'; // Contraseña de MySQL

try {
    // Crear la conexión con PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Mensaje de éxito (opcional)
    // echo "✅ Conexión exitosa a la base de datos";
} catch (PDOException $e) {
    // Si hay un error, mostrar el mensaje
    echo "❌ Error de conexión: " . $e->getMessage();
    exit; // Terminar el script si hay un error de conexión
}
