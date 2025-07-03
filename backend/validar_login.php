<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombreusuario = $_POST["nombreusuario"];
    $contraseña = $_POST["contraseña"];

    // Buscar el usuario por su nombre de usuario
    $stmt = $conn->query("SELECT DATABASE()");
    echo "Base de datos activa: " . $stmt->fetchColumn() . "<br>";
    $sql = "SELECT CI, Contrasena FROM Usuario WHERE NombreUsuario = :nombreusuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombreusuario', $nombreusuario);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $ci = $row['CI'];
        $contraseñaHash = $row['Contrasena'];

        // Verificar la contraseña
        if (password_verify($contraseña, $contraseñaHash)) {
            header("Location: ../usuario.php?ci=$ci");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
}

if (empty($nombreusuario) || empty($contraseña)) {
    echo "Faltan datos.";
    exit();
}

?>
