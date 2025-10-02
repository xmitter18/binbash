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

    // 👉 Redirigir de vuelta a chat_admin.php
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
    <link rel="stylesheet" href="/estilo.css?v=<?= time() ?>">
</head>
<body>
  <div class="chat-container">
    <h1>Chat con Usuario <?= htmlspecialchars($ci) ?></h1>
    <div class="chat-box">
  <?php foreach ($mensajes as $msg): ?>
    <div class="mensaje <?= $msg['remitente'] === 'admin' ? 'admin' : 'usuario' ?>">
      <p>
        <strong><?= htmlspecialchars($msg['remitente']) ?>:</strong>
        <?= htmlspecialchars($msg['mensaje']) ?>
      </p>
      <small>(<?= $msg['fecha'] ?>)</small>
    </div>
  <?php endforeach; ?>
</div>

    <form method="POST" class="chat-form">
      <input type="text" name="mensaje" placeholder="Escribe tu respuesta..." required>
      <button type="submit">Enviar</button>
    </form>

    <br>
    <a href="backofice.php" class="btn-link">⬅ Volver al panel</a>
  </div>
</body>
</html>
