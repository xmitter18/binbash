<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Recibe los datos del formulario
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $ci = $_POST["ci"];
    $fecha = $_POST["fecha"]; 
    $usuario = $_POST["nombreusuario"];
    $mail = $_POST["mail"];
    $telefono = $_POST["telefono"];
    $direccion = $_POST["direccion"];
    $contraseña = $_POST["contraseña"];

    // Encripta la contraseña antes de guardarla
    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();

        // Verifica si ya existe una persona con esa cédula
        $sql_check = "SELECT 1 FROM Persona WHERE CI = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$ci]);

        if ($stmt_check->fetch()) {
            echo "Error: Ya existe una persona registrada con esa cédula.";
            exit();
        }

        // Verifica si ya existe un usuario con ese nombre de usuario
        $consulta = "SELECT COUNT(*) FROM Usuario WHERE NombreUsuario = :nombreusuario";
        $stmt = $conn->prepare($consulta);
        $stmt->bindParam(':nombreusuario', $usuario); 
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            echo "Error: El nombre de usuario ya está en uso.";
            exit();
        }

        // Inserta la persona en la tabla Persona
        $sql1 = "INSERT INTO Persona (CI, Nombres, Apellidos, Domicilio, Telefono, Correo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$ci, $nombre, $apellido, $direccion, $telefono, $mail]);


        // Inserta el usuario en la tabla Usuario
        $sql2 = "INSERT INTO Usuario (CI, NombreUsuario, Contrasena) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$ci, $usuario, $contraseñaHash]);

        // Confirma los cambios
        $conn->commit();

        // Redirige al perfil del usuario
        header("Location: ../usuario.php?ci=$ci");
        exit();
        
    } catch (PDOException $e) {
        // Si ocurre un error, revierte los cambios
        $conn->rollBack();
        echo "Error al registrar: " . $e->getMessage();
    }
}
?>
