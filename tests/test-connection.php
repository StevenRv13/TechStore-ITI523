<?php
// Configuración simple para TechStore
$host = 'localhost';
$port = '3306';
$dbname = 'techstore_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", 
                   $username, $password, [
                       PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                       PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                   ]);
    
    // Variable global para usar en toda la aplicación
    $GLOBALS['db'] = $pdo;
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función simple para obtener conexión
function getDB() {
    return $GLOBALS['db'];
}
?>