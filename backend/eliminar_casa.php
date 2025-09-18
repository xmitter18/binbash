<?php
require __DIR__ . '/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    // Buscar imagen antes de borrar
    $stmt = $conn->prepare("SELECT imagen FROM casas WHERE id = ?");
    $stmt->execute([$id]);
    $casa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($casa) {
        $imagenPath = __DIR__ . "/../" . $casa['imagen'];
        if (file_exists($imagenPath)) {
            unlink($imagenPath); // Borrar foto
        }

        // Borrar la casa
        $stmt = $conn->prepare("DELETE FROM casas WHERE id = ?");
        $stmt->execute([$id]);
    }
}

header("Location: backofice.php");
exit();
