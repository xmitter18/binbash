<?php
function autenticarUsuario(PDO $conn, string $nombreusuario, string $contraseña) {
    if (empty($nombreusuario) || empty($contraseña)) {
        return "Error: Por favor completa todos los campos.";
    }

    $sql = "SELECT CI, Contrasena FROM Usuario WHERE NombreUsuario = :nombreusuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombreusuario', $nombreusuario);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if (password_verify($contraseña, $row['Contrasena'])) {
            return "OK:" . $row['CI'];  // devolvemos CI para usarlo en redirección
        } else {
            return "Error: Contraseña incorrecta.";
        }
    } else {
        return "Error: Usuario no encontrado.";
    }
}
