<?php
require('backend/conexion.php');

// Obtiene el valor de 'ci' que viene por la URL (Ejemplo: usuario.php?ci=12345678)
$ci = $_GET['ci'] ?? null;

// Si no se proporciona el 'ci', muestra un mensaje de error y detiene el script
if (!$ci) {
    echo "CI no proporcionado";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Captura los datos que vienen desde el formulario HTML
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $domicilio = $_POST["domicilio"];
    $correo = $_POST["correo"];

    // Prepara la consulta SQL para actualizar los datos en la tabla Persona
    $sqlUpdate = "UPDATE Persona 
                  SET Nombres = :nombres, Apellidos = :apellidos, Domicilio = :domicilio, Correo = :correo 
                  WHERE CI = :ci";

    $stmt = $conn->prepare($sqlUpdate);

    // Asocia los valores del formulario a los parámetros de la consulta
    $stmt->bindParam(':nombres', $nombres);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':domicilio', $domicilio);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);

    // Ejecuta la consulta y muestra un mensaje según el resultado
    if ($stmt->execute()) {
        echo "<p>Datos actualizados correctamente.</p>";
    } else {
        echo "<p>Error al actualizar los datos.</p>";
    }
}

// Si no se envió el formulario o después de actualizar, obtiene los datos actuales del usuario
$sql = "SELECT * FROM Persona WHERE CI = :ci";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
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
