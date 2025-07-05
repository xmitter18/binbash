<?php
$host = 'db';               // Nombre del host
$dbname = 'cooperativa';    // Nombre de la base de datos
$username = 'usuario';      // Usuario de la base de datos
$password = 'pass';         // Contraseña del usuario

try {
    // Crea la conexión utilizando PDO y establece el charset a UTF-8
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Habilita el modo de errores con excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Muestra un mensaje de error en caso de falla en la conexión
    die("Conexión fallida: " . $e->getMessage());
}
?>
