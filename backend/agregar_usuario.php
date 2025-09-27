<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibe los datos del formulario
    $nombre = $_POST["nombre"] ?? '';
    $apellido = $_POST["apellido"] ?? '';
    $ci = $_POST["ci"] ?? '';
    $fecha = $_POST["fecha"] ?? '';
    $usuario = $_POST["nombreusuario"] ?? '';
    $mail = $_POST["mail"] ?? '';
    $telefono = $_POST["telefono"] ?? '';
    $direccion = $_POST["direccion"] ?? '';
    $contraseña = $_POST["contraseña"] ?? '';

    // Encripta la contraseña
    $contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

    try {
        $conn->beginTransaction();

        // Verifica si ya existe una persona con esa cédula
        $sql_check = "SELECT 1 FROM Persona WHERE CI = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([$ci]);

        if ($stmt_check->fetch()) {
            $error = "Error: Ya existe una persona registrada con esa cédula.";
        } else {
            // Verifica si ya existe un usuario con ese nombre de usuario
            $consulta = "SELECT COUNT(*) FROM Usuario WHERE NombreUsuario = :nombreusuario";
            $stmt = $conn->prepare($consulta);
            $stmt->bindParam(':nombreusuario', $usuario);
            $stmt->execute();
            $existe = $stmt->fetchColumn();

            if ($existe > 0) {
                $error = "Error: El nombre de usuario ya está en uso.";
            } else {
                // Inserta en Persona
                $sql1 = "INSERT INTO Persona (CI, Nombres, Apellidos, Domicilio, Telefono, Correo, FechaNac) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt1 = $conn->prepare($sql1);
                $stmt1->execute([$ci, $nombre, $apellido, $direccion, $telefono, $mail, $fecha]);

                // Inserta en Usuario
                $sql2 = "INSERT INTO Usuario (CI, NombreUsuario, Contrasena, activo) 
                         VALUES (?, ?, ?, 'pendiente')";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([$ci, $usuario, $contraseñaHash]);

                $conn->commit();

                // Redirige al backoffice
                header("Location: backofice.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error al registrar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="/estilo.css?v=<?= time() ?>">
</head>
<body class="lado-izquierdo">

<div class="container">
    <h2>Agregar Usuario</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="campo">
            <label for="ci">CI</label>
            <input type="number" name="ci" required>
        </div>
        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" required>
        </div>
        <div class="campo">
            <label for="apellido">Apellido</label>
            <input type="text" name="apellido" required>
        </div>
        <div class="campo">
            <label for="fecha">Fecha de nacimiento</label>
            <input type="date" name="fecha" required>
        </div>
        <div class="campo">
            <label for="direccion">Dirección</label>
            <input type="text" name="direccion" required>
        </div>
        <div class="campo">
            <label for="telefono">Teléfono</label>
            <input type="text" name="telefono" required>
        </div>
        <div class="campo">
            <label for="mail">Correo</label>
            <input type="email" name="mail" required>
        </div>
        <div class="campo">
            <label for="nombreusuario">Nombre de usuario</label>
            <input type="text" name="nombreusuario" required>
        </div>
        <div class="campo">
            <label for="contraseña">Contraseña</label>
            <input type="password" name="contraseña" required>
        </div>
        <button type="submit" class="btn-submit">Guardar</button>
    <a href="backofice.php" class="btn-atras">Cancelar</a>
    </form>
</div>

</body>
</html>
