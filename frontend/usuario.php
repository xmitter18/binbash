<?php
require 'api/conexion.php';

$ci = $_GET['ci'] ?? null;

if (!$ci) {
    echo "CI no proporcionado";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $domicilio = $_POST["domicilio"];
    $correo = $_POST["correo"];

    $sqlUpdate = "UPDATE Persona SET Nombres=?, Apellidos=?, Domicilio=?, Correo=? WHERE CI=?";
    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param("ssssi", $nombres, $apellidos, $domicilio, $correo, $ci);
    $stmt->execute();
    echo "<p>Datos actualizados correctamente.</p>";
}

// Obtener datos del usuario
$sql = "SELECT * FROM Persona WHERE CI = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ci);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Perfil del Usuario</title>
  <link rel="stylesheet" href="estilo.css">
  <link rel="icon" href="favicon.ico">
</head>
<body>
  <h1>Bienvenido, <?php echo htmlspecialchars($usuario['Nombres']); ?></h1>
  <form method="POST">
    <label>Nombre: <input type="text" name="nombres" value="<?php echo $usuario['Nombres']; ?>" /></label><br>
    <label>Apellido: <input type="text" name="apellidos" value="<?php echo $usuario['Apellidos']; ?>" /></label><br>
    <label>Domicilio: <input type="text" name="domicilio" value="<?php echo $usuario['Domicilio']; ?>" /></label><br>
    <label>Correo: <input type="email" name="correo" value="<?php echo $usuario['Correo']; ?>" /></label><br>
    <button type="submit">Guardar cambios</button>
  </form>
</body>
</html>
