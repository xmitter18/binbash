<?php
require('backend/conexion.php');

// Obtener CI desde URL
$ci = $_GET['ci'] ?? null;
if (!$ci) {
    echo "CI no proporcionado";
    exit();
}

// Actualizar datos si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombres = $_POST["nombres"];
    $apellidos = $_POST["apellidos"];
    $domicilio = $_POST["domicilio"];
    $telefono = $_POST["telefono"];
    $correo = $_POST["correo"];

    $sqlUpdate = "UPDATE Persona 
                  SET Nombres = :nombres, Apellidos = :apellidos, Domicilio = :domicilio, Telefono = :telefono, Correo = :correo 
                  WHERE CI = :ci";

    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bindParam(':nombres', $nombres);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':domicilio', $domicilio);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<p>Datos actualizados correctamente.</p>";
    } else {
        echo "<p>Error al actualizar los datos.</p>";
    }
}

// Obtener datos
$sql = "SELECT p.*, u.activo 
        FROM Persona p
        JOIN Usuario u ON p.CI = u.CI
        WHERE p.CI = :ci";

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
  <link rel="stylesheet" href="estilo.css?v=<?= time() ?>">
  <link rel="icon" href="favicon.ico">
</head>
<body class="fondousuario">
  <a href="landingpage.html" class="btn-logout">Cerrar sesión</a>
  <div class="logusuario">
    <h1>Bienvenido, <?= htmlspecialchars($usuario['Nombres']) ?></h1>
    <form method="POST" class="registro-form">
      <div class="campo">
        <label for="nombres">Nombre</label>
        <input type="text" name="nombres" id="nombres" value="<?= $usuario['Nombres'] ?>" required />
      </div>

      <div class="campo">
        <label for="apellidos">Apellido</label>
        <input type="text" name="apellidos" id="apellidos" value="<?= $usuario['Apellidos'] ?>" required />
      </div>

      <div class="campo">
        <label for="domicilio">Domicilio</label>
        <input type="text" name="domicilio" id="domicilio" value="<?= $usuario['Domicilio'] ?>" required />
      </div>

      <div class="campo">
        <label for="telefono">Teléfono</label>
        <input type="text" name="telefono" id="telefono" value="<?= $usuario['Telefono'] ?>" required />
      </div>

      <div class="campo">
        <label for="correo">Correo</label>
        <input type="email" name="correo" id="correo" value="<?= $usuario['Correo'] ?>" required />
      </div>

      <button type="submit" class="btn-submit">Guardar cambios</button>
    </form>
  </div>

  <br>

  <div class="comprobaqui">
    <p>Inserte aquí sus comprobantes de pago.</p>
  </div>

  <div style="max-width: 75%; margin: 0 auto;">
    <button type="button" class="btn-subir">Subir</button>

    <?php if ($usuario['activo'] === 'aceptado'): ?>
      <a href="propiedades.html"><button type="button" class="btn-ver-propiedades">Ver propiedades disponibles</button></a>
    <?php else: ?>
      <button type="button" class="btn-ver-propiedades" disabled style="background-color: grey; cursor: not-allowed;">Esperando aprobación</button>
    <?php endif; ?>
  </div>

  <footer class="iniciosesion">
    <div class="realizadopor">
      <p>Trabajo realizado por Nicolas Graña, Benjamín Hiriart, Rachel Montesinos y Federico Ricca</p>
      <img src="img/Logo de bin-bash sin fondo (light theme).png" width="80px" alt="Logo de bin-bash">
    </div>
  </footer>
</body>
</html>
