<?php
require __DIR__ . '/conexion.php';


$ci = $_GET['ci'] ?? null;
if (!$ci) {
    die("CI no proporcionado");
}

// Enviar mensaje como admin
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $stmt = $conn->prepare("INSERT INTO Chat (CI, remitente, mensaje) VALUES (:ci, 'admin', :mensaje)");
    $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->execute();
    header("Location: chat_admin.php?ci=" . urlencode($ci));
    exit();
}

// Obtener mensajes
$stmt = $conn->prepare("SELECT remitente, mensaje, fecha FROM Chat WHERE CI = :ci ORDER BY fecha ASC");
$stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Chat con Usuario <?= htmlspecialchars($ci) ?></title>
  <link rel="stylesheet" href="estilo.css?v=<?= time() ?>">
</head>
<body>
  <h1>Chat con Usuario <?= htmlspecialchars($ci) ?></h1>
  <div class="chat-box" style="border:1px solid #ccc; padding:10px; max-width:600px; height:300px; overflow-y:auto;">
    <?php foreach ($mensajes as $msg): ?>
      <p><strong><?= htmlspecialchars($msg['remitente']) ?>:</strong> <?= htmlspecialchars($msg['mensaje']) ?> <small>(<?= $msg['fecha'] ?>)</small></p>
    <?php endforeach; ?>
  </div>

  <form method="POST" style="margin-top:10px;">
    <input type="text" name="mensaje" placeholder="Escribe tu respuesta..." required style="width:70%">
    <button type="submit">Enviar</button>
  </form>

  <br>
  <a href="backofice.php">⬅ Volver al panel</a>
</body>
</html>
