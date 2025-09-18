<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $imagen = null;

    // Subida de imagen
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
if (in_array($ext, ['jpg','jpeg','png'])) {
    $nombreArchivo = uniqid("casa_", true) . "." . $ext;

    // Ruta dentro del contenedor (carpeta img del frontend)
    $destino = __DIR__ . "/../img/" . $nombreArchivo;

    // Crear carpeta si no existe
    if (!is_dir(dirname($destino))) {
        mkdir(dirname($destino), 0777, true);
    }

    move_uploaded_file($_FILES['imagen']['tmp_name'], $destino);

    // Guardamos la ruta relativa que verá el navegador
    $imagen = "img/" . $nombreArchivo;
}


    $sql = "INSERT INTO casas (nombre, precio, imagen) VALUES (:nombre, :precio, :imagen)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':precio' => $precio,
        ':imagen' => $imagen
    ]);

    header("Location: backofice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Casa</title>
  <link rel="stylesheet" href="/estilo.css?v=<?= time() ?>">
</head>
<body class="fondousuario">
  <div class="logusuario">
    <h1>Agregar nueva casa</h1>
    <form method="POST" enctype="multipart/form-data" class="registro-form">
      <div class="campo">
        <label>Nombre de la casa:</label>
        <input type="text" name="nombre" required>
      </div>
      <div class="campo">
        <label>Precio estimado (USD):</label>
        <input type="number" name="precio" step="0.01" required>
      </div>
      <div class="campo">
        <label>Foto:</label>
        <input type="file" name="imagen" accept="image/*" required>
      </div>
      <button type="submit" class="btn-submit">Guardar</button>
      <a href="backofice.php" class="btn-atras">Cancelar</a>
    </form>
  </div>
</body>
</html>
