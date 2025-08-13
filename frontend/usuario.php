<?php
require('backend/conexion.php');

// Obtener CI desde URL
$ci = $_GET['ci'] ?? null;
if (!$ci) {
    echo "CI no proporcionado";
    exit();
}

// Actualizar datos si se envió el formulario de perfil
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["nombres"])) {
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

    $stmt->execute();
}

// Obtener datos del usuario
$sql = "SELECT p.*, u.activo 
        FROM Persona p
        JOIN Usuario u ON p.CI = u.CI
        WHERE p.CI = :ci";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Manejo de comprobantes
$comprobantesDir = "comprobantes/$ci/";
if (!is_dir($comprobantesDir)) {
    mkdir($comprobantesDir, 0777, true);
}
$archivosActuales = glob($comprobantesDir . "*.pdf");

// Subida de comprobantes
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['comprobantes'])) {
    $archivos = $_FILES['comprobantes'];
    $cantidadActual = count($archivosActuales);

    for ($i = 0; $i < count($archivos['name']); $i++) {
        if ($archivos['error'][$i] === UPLOAD_ERR_OK && $cantidadActual < 2) {
            $ext = strtolower(pathinfo($archivos['name'][$i], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $nuevoNombre = uniqid("comp_", true) . ".pdf";
                $rutaDestino = $comprobantesDir . $nuevoNombre;
                move_uploaded_file($archivos['tmp_name'][$i], $rutaDestino);
                $cantidadActual++;
            }
        }
    }

    // Refrescar lista de archivos
    $archivosActuales = glob($comprobantesDir . "*.pdf");
}
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
      <div class="campo"><label for="nombres">Nombre</label><input type="text" name="nombres" id="nombres" value="<?= $usuario['Nombres'] ?>" required /></div>
      <div class="campo"><label for="apellidos">Apellido</label><input type="text" name="apellidos" id="apellidos" value="<?= $usuario['Apellidos'] ?>" required /></div>
      <div class="campo"><label for="domicilio">Domicilio</label><input type="text" name="domicilio" id="domicilio" value="<?= $usuario['Domicilio'] ?>" required /></div>
      <div class="campo"><label for="telefono">Teléfono</label><input type="text" name="telefono" id="telefono" value="<?= $usuario['Telefono'] ?>" required /></div>
      <div class="campo"><label for="correo">Correo</label><input type="email" name="correo" id="correo" value="<?= $usuario['Correo'] ?>" required /></div>
      <button type="submit" class="btn-submit">Guardar cambios</button>
      <br>
      <br>
     <div class="links-ejemplos">
  <a href="comprobantes/COMPROBANTE%20DE%20PAGO.pdf" target="_blank" class="btn-link">
      Ejemplo de comprobante de pago
  </a>
  <a href="comprobantes/COMPROBANTE%20DE%20HORAS.pdf" target="_blank" class="btn-link">
      Ejemplo de comprobante de horas
  </a>
</div>

    </form>
  </div>

  <br>

  <div class="comprobaqui">
    <?php if (count($archivosActuales) > 0): ?>
      <p>Comprobantes subidos:</p>
      <ul>
        <?php foreach ($archivosActuales as $archivo): ?>
          <li><?= basename($archivo) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <br>
      <p> Inserte aquí sus comprobantes de pago.</p>
      <br>
    <?php endif; ?>
  </div>

  <?php if (count($archivosActuales) < 2): ?>
    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px;">
  <form method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
    <input type="file" name="comprobantes[]" accept="application/pdf" multiple required>
    <button type="submit" class="btn-subir">Subir</button>
  </form>
</div>
  <?php endif; ?>

  <div class="contenedor-boton">
  <?php if ($usuario['activo'] === 'aceptado'): ?>
    <a href="casas.html" class="btn-ver-propiedades">Ver propiedades disponibles</a>
  <?php else: ?>
    <button type="button" class="btn-ver-propiedades" disabled style="background-color: grey; cursor: not-allowed;">Esperando aprobación</button>
  <?php endif; ?>
</div>


</body>
</html>
