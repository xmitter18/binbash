<?php
require __DIR__ . '/backend/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ci = $_POST['ci'] ?? null;
    $casa_id = $_POST['casa_id'] ?? null;

    if ($ci && $casa_id) {
        // Verificar si ya tiene trabajo
        $sqlCheck = "SELECT * FROM Trabajo WHERE CI = :ci";
        $stmt = $conn->prepare($sqlCheck);
        $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
        $stmt->execute();
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Actualizar casa_id
            $sqlUpdate = "UPDATE Trabajo SET casa_id = :casa_id WHERE CI = :ci";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // Insertar nuevo registro
            $sqlInsert = "INSERT INTO Trabajo (CI, casa_id) VALUES (:ci, :casa_id)";
            $stmt = $conn->prepare($sqlInsert);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
            $stmt->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    header("Location: usuario.php?ci=" . urlencode($ci));
    exit();
}
?>
