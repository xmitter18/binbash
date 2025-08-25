<?php
require('backend/conexion.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ci'], $_POST['casa_id'], $_POST['horas'])) {
    $ci = (int)$_POST['ci'];
    $casa_id = (int)$_POST['casa_id'];
    $horas = (int)$_POST['horas'];

    // Calcular el lunes de la semana actual
    $lunes = date('Y-m-d', strtotime('monday this week'));

    // Verificar si ya se registraron horas esta semana
    $stmt = $conn->prepare("SELECT * FROM Horas_Semana WHERE CI = :ci AND casa_id = :casa_id AND semana_inicio = :lunes");
    $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
    $stmt->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
    $stmt->bindParam(':lunes', $lunes);
    $stmt->execute();
    $existe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        // Ya registró horas esta semana → no permitir más
        echo "Ya registraste tus horas esta semana.";
    } else {
        // Insertar horas
        $stmtInsert = $conn->prepare("INSERT INTO Horas_Semana (CI, casa_id, semana_inicio, horas) VALUES (:ci, :casa_id, :lunes, :horas)");
        $stmtInsert->bindParam(':ci', $ci, PDO::PARAM_INT);
        $stmtInsert->bindParam(':casa_id', $casa_id, PDO::PARAM_INT);
        $stmtInsert->bindParam(':lunes', $lunes);
        $stmtInsert->bindParam(':horas', $horas, PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    header("Location: usuario.php?ci=" . $ci);
    exit();
}
?>
