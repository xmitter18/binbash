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
    <link rel="stylesheet" href="/estilo.css" />
    <link rel="icon" href="/favicon.ico">
</head>
<body class="lado-izquierdo">
<header class="top-bar">
    <h1>Panel de Administración</h1>
</header>
<br>
<h2>Usuarios Registrados</h2>
<div class="filtro-busqueda">
    <label for="buscar-ci"><strong>Buscar por CI:</strong></label>
    <input type="text" id="buscar-ci" placeholder="Escribe una cédula...">
</div>
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
<script>
    document.getElementById('buscar-ci').addEventListener('input', function () {
        const filtro = this.value.trim().toLowerCase();
        const usuarios = document.querySelectorAll('ul > li');

        usuarios.forEach(usuario => {
            const ci = usuario.querySelector('p strong + text')?.textContent?.toLowerCase() || '';
            const textoCI = usuario.querySelector('p')?.textContent?.toLowerCase() || '';
            if (textoCI.includes(filtro)) {
                usuario.style.display = '';
            } else {
                usuario.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>

<?php $conexion->close(); ?>