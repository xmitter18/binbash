<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombreusuario = $_POST["nombreusuario"];
    $contraseña = $_POST["contraseña"];

    // Buscar el usuario por su nombre de usuario
    $sql = "SELECT CI, Contraseña FROM Usuario WHERE NombreUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombreusuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $ci = $row['CI'];
        $contraseñaHash = $row['Contraseña'];

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
?>
