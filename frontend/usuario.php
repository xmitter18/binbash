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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['guardar_horas'])) {
    $horas = (int)$_POST['horas_semanales'];

    // Obtener el id de la casa donde trabaja actualmente
    $stmtCasa = $conn->prepare("SELECT casa_id FROM Trabajo WHERE CI = :ci");
    $stmtCasa->bindParam(':ci', $ci, PDO::PARAM_INT);
    $stmtCasa->execute();
    $casa = $stmtCasa->fetch(PDO::FETCH_ASSOC);

    if ($casa) {
        $casa_id = $casa['casa_id'];

        // Fecha del lunes de esta semana
        $lunesSemana = date('Y-m-d', strtotime('monday this week'));

        // Verificar si ya tiene registro de esta semana
        $stmtCheck = $conn->prepare("SELECT horas FROM HorasTrabajo WHERE CI = :ci AND casa_id = :casa_id AND semana = :semana");
        $stmtCheck->bindParam(':ci', $ci, PDO::PARAM_INT);
        $stmtCheck->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
        $stmtCheck->bindParam(':semana', $lunesSemana);
        $stmtCheck->execute();
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Sumar horas a las existentes
            $totalHoras = $existe['horas'] + $horas;
            $stmtUpdate = $conn->prepare("UPDATE HorasTrabajo SET horas = :totalHoras WHERE CI = :ci AND casa_id = :casa_id AND semana = :semana");
            $stmtUpdate->bindParam(':totalHoras', $totalHoras, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':ci', $ci, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':semana', $lunesSemana);
            $stmtUpdate->execute();
        } else {
            // Insertar nuevo registro de esta semana
            $stmtInsert = $conn->prepare("INSERT INTO HorasTrabajo (CI, casa_id, semana, horas) VALUES (:ci, :casa_id, :semana, :horas)");
            $stmtInsert->bindParam(':ci', $ci, PDO::PARAM_INT);
            $stmtInsert->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
            $stmtInsert->bindParam(':semana', $lunesSemana);
            $stmtInsert->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmtInsert->execute();
        }
    }

    // Refrescar la página
    header("Location: usuario.php?ci=" . urlencode($ci));
    exit();
}


// Obtener datos del usuario
$sql = "SELECT p.*, u.activo, c.nombre AS nombre_casa
        FROM Persona p
        JOIN Usuario u ON p.CI = u.CI
        LEFT JOIN Trabajo t ON p.CI = t.CI
        LEFT JOIN casas c ON t.casa_id = c.id
        WHERE p.CI = :ci";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener datos del usuario y la casa en la que trabaja (si existe)
$sql = "SELECT p.*, u.activo, c.nombre AS nombre_casa
        FROM Persona p
        JOIN Usuario u ON p.CI = u.CI
        LEFT JOIN Trabajo t ON p.CI = t.CI
        LEFT JOIN casas c ON t.casa_id = c.id
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
// Subida de comprobantes (máximo 2 siempre)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['comprobantes'])) {
    $archivos = $_FILES['comprobantes'];

    // Borrar comprobantes anteriores para permitir reemplazo
    foreach (glob($comprobantesDir . "*.pdf") as $archivoAntiguo) {
        unlink($archivoAntiguo);
    }

    // Subir hasta 2 comprobantes nuevos
    $subidos = 0;
    for ($i = 0; $i < count($archivos['name']); $i++) {
        if ($archivos['error'][$i] === UPLOAD_ERR_OK && $subidos < 2) {
            $ext = strtolower(pathinfo($archivos['name'][$i], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
    $nombreOriginal = basename($archivos['name'][$i]); // nombre real
    $rutaDestino = $comprobantesDir . $nombreOriginal;

    move_uploaded_file($archivos['tmp_name'][$i], $rutaDestino);
    $subidos++;
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
    <?php
// Suponiendo que $usuario y $ci ya están definidos como antes
$trabajoActual = $usuario['nombre_casa'] ?? null;

// Calcular la fecha del lunes de la semana actual
$lunesSemana = date('Y-m-d', strtotime('monday this week'));
?>
<?php if ($trabajoActual): ?>
  <p>Actualmente estás trabajando para: <strong><?= htmlspecialchars($trabajoActual) ?></strong></p>

 <?php
// --- Calcular horas totales trabajadas ---
$stmtHorasTotales = $conn->prepare("SELECT SUM(horas) as total FROM HorasTrabajo WHERE CI = :ci");
$stmtHorasTotales->bindParam(':ci', $ci, PDO::PARAM_INT);
$stmtHorasTotales->execute();
$resHorasTotales = $stmtHorasTotales->fetch(PDO::FETCH_ASSOC);
$horasTotales = $resHorasTotales['total'] ?? 0;

// --- Último registro de horas (para bloqueo 7 días) ---
$stmtUltimo = $conn->prepare("SELECT MAX(semana) as ultima_semana FROM HorasTrabajo WHERE CI = :ci");
$stmtUltimo->bindParam(':ci', $ci, PDO::PARAM_INT);
$stmtUltimo->execute();
$ultimaSemana = $stmtUltimo->fetch(PDO::FETCH_ASSOC)['ultima_semana'] ?? null;

// Bloqueo: si ya registró horas hace menos de 7 días
$bloqueado = false;
if ($ultimaSemana) {
    $fechaUltima = new DateTime($ultimaSemana);
    $hoy = new DateTime();
    $diasPasados = $fechaUltima->diff($hoy)->days;
    if ($diasPasados < 7) {
        $bloqueado = true;
    }
}
?>

<!-- Formulario de carga de horas -->
<form method="POST" action="usuario.php?ci=<?= urlencode($ci) ?>" 
      onsubmit="return confirm('¿Estás seguro de que deseas registrar estas horas?');">
  <label for="horas_semanales">Registrar horas trabajadas:</label>
  <select name="horas_semanales" id="horas_semanales" required <?= $bloqueado ? 'disabled' : '' ?>>
    <?php for ($i = 1; $i <= 50; $i++): ?>
      <option value="<?= $i ?>"><?= $i ?></option>
    <?php endfor; ?>
  </select>
  <button type="submit" name="guardar_horas" <?= $bloqueado ? 'disabled style="background-color: grey; cursor: not-allowed;"' : '' ?>>
    Enviar
  </button>
</form>

<!-- Mostrar total acumulado -->
<p>Horas totales trabajadas: <?= $horasTotales ?></p>

<?php if ($bloqueado): ?>
  <p style="color: red;">⏳ Ya registraste horas recientemente. Podrás volver a hacerlo en 7 días.</p>
<?php endif; ?>


<?php else: ?>
  <p>No has seleccionado ninguna casa aún.</p>
<?php endif; ?>
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

  <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 20px;">
  <form method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
    <input type="file" name="comprobantes[]" accept="application/pdf" multiple required>
    <button type="submit" class="btn-subir">Subir/Reemplazar</button>
  </form>
</div>

 <div class="contenedor-boton">
  <?php if ($usuario['activo'] === 'aceptado'): ?>
    <a href="casas.php?ci=<?= urlencode($ci) ?>&origen=usuario" class="btn-ver-propiedades">Ver propiedades disponibles</a>
    <a href="chat.php?ci=<?= urlencode($ci) ?>" class="btn-ver-propiedades">Chat con Admin</a>
  <?php else: ?>
    <button type="button" class="btn-ver-propiedades" disabled style="background-color: grey; cursor: not-allowed;">Esperando aprobación</button>
  <?php endif; ?>
</div>




</body>
</html>
