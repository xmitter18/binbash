<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $ci = $_POST["ci"];
    $fecha = $_POST["fecha"]; // Nota: no se usa en el insert actual
    $usuario = $_POST["nombreusuario"];
    $mail = $_POST["mail"];
    $direccion = $_POST["direccion"];
    $contraseña = $_POST["contraseña"];

    // Encriptar la contraseña
    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Insertar en Persona
        $sql1 = "INSERT INTO Persona (CI, Nombres, Apellidos, Domicilio, Correo) VALUES (?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$ci, $nombre, $apellido, $direccion, $mail]);

        // Insertar en Usuario
        $sql2 = "INSERT INTO Usuario (CI, NombreUsuario, Contraseña) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$ci, $usuario, $contraseñaHash]);

        // Confirmar la transacción
        $conn->commit();

        // Redirigir a usuario.php
        header("Location: ../usuario.php?ci=$ci");
        exit();
    } catch (PDOException $e) {
        // Revertir si algo falla
        $conn->rollBack();
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>
