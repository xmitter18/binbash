<?php
require('conexion.php');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    try {
        $conexionPDO = new PDO("mysql:host=db;dbname=cooperativa;charset=utf8", "usuario", "pass");
        $conexionPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Eliminar registros relacionados antes de borrar la casa
        $conexionPDO->prepare("DELETE FROM HorasTrabajo WHERE casa_id = ?")->execute([$id]);
        $conexionPDO->prepare("DELETE FROM Trabajo WHERE casa_id = ?")->execute([$id]);

        // Luego eliminar la casa
        $conexionPDO->prepare("DELETE FROM casas WHERE id = ?")->execute([$id]);

        header("Location: backofice.php");
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar casa: " . $e->getMessage();
    }
}
?>
