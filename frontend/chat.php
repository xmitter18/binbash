<?php
require __DIR__ . '/backend/conexion.php';


$ci = $_GET['ci'] ?? null;
if (!$ci) {
    die("CI no proporcionado");
}

// Enviar mensaje
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $stmt = $conn->prepare("INSERT INTO Chat (CI, remitente, mensaje) VALUES (:ci, 'usuario', :mensaje)");
    $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->execute();
    header("Location: chat.php?ci=" . urlencode($ci));
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
  <title>Chat con Admin</title>
  <link rel="stylesheet" href="estilo.css?v=<?= time() ?>">
</head>
<body>
    <div class="chat-container">
  <h1>Chat con el Administrador</h1>
    <div class="chat-box">
    <?php foreach ($mensajes as $msg): ?>
      <p><strong><?= htmlspecialchars($msg['remitente']) ?>:</strong> <?= htmlspecialchars($msg['mensaje']) ?> <small>(<?= $msg['fecha'] ?>)</small></p>
    <?php endforeach; ?>
  </div>

    <form method="POST" class="chat-form">
      <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
      <button type="submit">Enviar</button>
  </form>

  <br>
    <a href="usuario.php?ci=<?= urlencode($ci) ?>" class="btn-link">⬅ Volver al perfil</a>
      </div>
</body>
</html>
