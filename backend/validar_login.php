<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Captura los datos del formulario
    $nombreusuario = $_POST["nombreusuario"];
    $contraseña = $_POST["contraseña"];

    // Validación básica: campos obligatorios
    if (empty($nombreusuario) || empty($contraseña)) {
        echo "Error: Por favor completa todos los campos.";
        exit();
    }

    // Busca el usuario por nombre de usuario
    $sql = "SELECT CI, Contrasena FROM Usuario WHERE NombreUsuario = :nombreusuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombreusuario', $nombreusuario);
    $stmt->execute();

    // Verifica si se encontró un usuario
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $ci = $row['CI'];
        $contraseñaHash = $row['Contrasena'];

        // Compara la contraseña ingresada con la contraseña encriptada almacenada
        if (password_verify($contraseña, $contraseñaHash)) {
            // Redirige al perfil si la autenticación es exitosa
            header("Location: ../usuario.php?ci=$ci");
            exit();
        } else {
            echo "Error: Contraseña incorrecta.";
            exit();
        }
    } else {
        echo "Error: Usuario no encontrado.";
        exit();
    }
}
?>
