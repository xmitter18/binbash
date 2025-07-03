<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $ci = $_POST["ci"];
    $fecha = $_POST["fecha"]; 
    $usuario = $_POST["nombreusuario"];
    $mail = $_POST["mail"];
    $direccion = $_POST["direccion"];
    $contraseña = $_POST["contraseña"];

    // Encriptar la contraseña
    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // Verificar si ya existe la persona
        $sql_check = "SELECT 1 FROM Persona WHERE CI = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$ci]);

        if ($stmt_check->fetch()) {
        echo "Error: Ya existe una persona registrada con esa cédula.";
        exit();
        }

        // Verificar si el nombre de usuario ya existe
        $consulta = "SELECT COUNT(*) FROM Usuario WHERE NombreUsuario = :nombreusuario";
        $stmt = $conn->prepare($consulta);
        $stmt->bindParam(':nombreusuario', $usuario); // antes decía $nombreusuario (incorrecto)
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
        echo "Error: El nombre de usuario ya está en uso.";
        exit();
        }

        // Insertar en Persona
        $sql1 = "INSERT INTO Persona (CI, Nombres, Apellidos, Domicilio, Correo) VALUES (?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$ci, $nombre, $apellido, $direccion, $mail]);

        // Insertar en Usuario
        $sql2 = "INSERT INTO Usuario (CI, NombreUsuario, Contrasea) VALUES (?, ?, ?)";
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
