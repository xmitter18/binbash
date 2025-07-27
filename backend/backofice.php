<?php
$conexion = new mysqli("db", "usuario", "pass", "cooperativa");
if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

// Aceptar o rechazar usuarios
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'], $_POST['ci'])) {
    $accion = $_POST['accion'];
    $ci = (int) $_POST['ci'];

    if (in_array($accion, ['aceptado', 'rechazado'])) {
        $conexion->query("UPDATE Usuario SET activo = '$accion' WHERE CI = $ci");
    }
}

// Obtener usuarios
$sql = "SELECT p.CI, p.Nombres, p.Apellidos, p.Domicilio, p.Telefono, p.Correo, u.NombreUsuario, u.activo
        FROM Persona p
        JOIN Usuario u ON p.CI = u.CI";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
</head>
<body>
<h1>Panel de Administración</h1>
<h2>Usuarios Registrados</h2>

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <ul>
    <?php while ($fila = $resultado->fetch_assoc()): ?>
        <li>
            <p><strong>CI:</strong> <?= $fila["CI"] ?></p>
            <p><strong>Nombre:</strong> <?= $fila["Nombres"] ?> <?= $fila["Apellidos"] ?></p>
            <p><strong>Usuario:</strong> <?= $fila["NombreUsuario"] ?></p>
            <p><strong>Correo:</strong> <?= $fila["Correo"] ?></p>
            <p><strong>Teléfono:</strong> <?= $fila["Telefono"] ?></p>
            <p><strong>Domicilio:</strong> <?= $fila["Domicilio"] ?></p>
            <p><strong>Estado:</strong> <?= $fila["activo"] ?></p>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="ci" value="<?= $fila['CI'] ?>">
                <button type="submit" name="accion" value="aceptado">Aceptar</button>
                <button type="submit" name="accion" value="rechazado">Rechazar</button>
            </form>

            <p><strong>Comprobantes:</strong></p>
<ul>
<?php
    $ci = $fila['CI'];
    $comprobantes = glob(__DIR__ . "/../comprobantes/$ci/*.[Pp][Dd][Ff]");

if ($comprobantes && count($comprobantes) > 0) {
    foreach ($comprobantes as $comp) {
        $nombreArchivo = basename($comp);
        echo "<li><a href='/comprobantes/$ci/$nombreArchivo' target='_blank'>Ver</a> | 
                  <a href='/comprobantes/$ci/$nombreArchivo' download>Descargar</a></li>";
    }
} else {
    echo "<li>No hay comprobantes subidos.</li>";
}

?>
</ul>


            <hr>
        </li>
    <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>No hay usuarios registrados o hubo un error en la consulta.</p>
<?php endif; ?>
</body>
</html>

<?php $conexion->close(); ?>

