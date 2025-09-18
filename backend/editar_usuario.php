<?php
require('conexion.php');

$ci = $_GET['ci'] ?? null;
if (!$ci) {
    die("CI no proporcionado");
}

// Si se envió el formulario, actualizar
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombres   = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $correo    = $_POST['correo'];
    $telefono  = $_POST['telefono'];
    $domicilio = $_POST['domicilio'];

    $sql = "UPDATE Persona 
            SET Nombres = :nombres, Apellidos = :apellidos, Correo = :correo, 
                Telefono = :telefono, Domicilio = :domicilio
            WHERE CI = :ci";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':correo' => $correo,
        ':telefono' => $telefono,
        ':domicilio' => $domicilio,
        ':ci' => $ci
    ]);

    header("Location: backofice.php");
    exit();
}

// Obtener datos actuales
$stmt = $conn->prepare("SELECT * FROM Persona WHERE CI = :ci");
$stmt->execute([':ci' => $ci]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="/estilo.css?v=<?= time() ?>">
</head>
<body class="fondousuario">
    <div class="logusuario">
     <h1>Editar Usuario</h1>
    <form method="POST" class="registro-form">
      <div class="campo">
        <label>Nombre:</label>
        <input type="text" name="nombres" value="<?= htmlspecialchars($usuario['Nombres']) ?>" required>
      </div>

      <div class="campo">
        <label>Apellido:</label>
        <input type="text" name="apellidos" value="<?= htmlspecialchars($usuario['Apellidos']) ?>" required>
      </div>

      <div class="campo">
        <label>Correo:</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($usuario['Correo']) ?>" required>
      </div>

      <div class="campo">
        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['Telefono']) ?>" required>
      </div>

      <div class="campo">
        <label>Domicilio:</label>
        <input type="text" name="domicilio" value="<?= htmlspecialchars($usuario['Domicilio']) ?>" required>
      </div>

      <button type="submit" class="btn-submit">Guardar cambios</button>
      <a href="backofice.php" class="btn-atras">Cancelar</a>
    </form>
  </div>
</body>
</html>
