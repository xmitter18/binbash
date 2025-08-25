<?php
$conexion = new mysqli("db", "usuario", "pass", "cooperativa");
if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

// --- Aceptar o rechazar usuarios ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'], $_POST['ci'])) {
    $accion = $_POST['accion'];
    $ci = (int) $_POST['ci'];

    if (in_array($accion, ['aceptado', 'rechazado'])) {
        $conexion->query("UPDATE Usuario SET activo = '$accion' WHERE CI = $ci");
    }
}

// --- Obtener usuarios ---
$sql = "SELECT p.CI, p.Nombres, p.Apellidos, p.Domicilio, p.Telefono, p.Correo, 
               u.NombreUsuario, u.activo
        FROM Persona p
        JOIN Usuario u ON p.CI = u.CI";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="/estilo.css?v=<?= time() ?>">
    <link rel="icon" href="/favicon.ico">
</head>
<body class="lado-izquierdo">

<header class="top-bar">
    <h1>Panel de Administración</h1>
</header>

<h2>Usuarios Registrados</h2>

<div class="filtro-busqueda">
    <label for="buscar-ci"><strong>Buscar por CI:</strong></label>
    <input type="text" id="buscar-ci" placeholder="Escribe una cédula...">
</div>

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <table id="tablaUsuarios">
        <thead>
            <tr>
                <th>CI</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Domicilio</th>
                <th>Estado</th>
                <th>Comprobantes</th>
                <th>Horas Trabajadas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($fila = $resultado->fetch_assoc()): ?>
            <tr>
                <!-- CI -->
                <td><?= $fila["CI"] ?></td>

                <!-- Nombre -->
                <td><?= $fila["Nombres"] . " " . $fila["Apellidos"] ?></td>

                <!-- Usuario -->
                <td><?= $fila["NombreUsuario"] ?></td>

                <!-- Correo -->
                <td><?= $fila["Correo"] ?></td>

                <!-- Teléfono -->
                <td><?= $fila["Telefono"] ?></td>

                <!-- Domicilio -->
                <td><?= $fila["Domicilio"] ?></td>

                <!-- Estado -->
                <td>
                    <?php
                        $ci = $fila['CI'];

                        // ---- Estado (aceptado/rechazado) con emoji de comprobante actualizado ----
                        $basePath = __DIR__ . "/../comprobantes/$ci/";

                        if (!is_dir($basePath)) {
                            mkdir($basePath, 0777, true);
                        }

                        $comprobantes = glob($basePath . "*.[Pp][Dd][Ff]");
                        $emojiActualizado = '';

                        foreach ($comprobantes as $archivo) {
                            if (filemtime($archivo) >= time() - 24 * 60 * 60) {
                                $emojiActualizado = '🟡';
                                break;
                            }
                        }

                        echo $fila["activo"] . " " . $emojiActualizado;
                    ?>
                </td>

                <!-- Comprobantes -->
                <td>
                    <ul>
                        <?php
                        if ($comprobantes && count($comprobantes) > 0) {
                            foreach ($comprobantes as $comp) {
                                $nombreArchivo = basename($comp);
                                echo "<li>
                                        <a href='/comprobantes/$ci/$nombreArchivo' target='_blank'>Ver</a> | 
                                        <a href='/comprobantes/$ci/$nombreArchivo' download>Descargar</a>
                                      </li>";
                            }
                        } else {
                            echo "<li>No hay comprobantes</li>";
                        }
                        ?>
                    </ul>
                </td>

                <!-- Horas trabajadas -->
                <td>
                    <?php
                        $stmtHoras = $conexion->prepare(
                            "SELECT SUM(horas) as total_horas, MAX(semana) as ultima_semana
                             FROM HorasTrabajo
                             WHERE CI = ?"
                        );
                        $stmtHoras->bind_param("i", $ci);
                        $stmtHoras->execute();

                        $resHoras = $stmtHoras->get_result()->fetch_assoc();
                        $totalHoras = $resHoras['total_horas'] ?? 0;
                        $ultimaSemana = $resHoras['ultima_semana'] ?? null;

                        // --- Verificar mínimo 21 horas por semana ---
                        $emojiHoras = '';
                        if ($ultimaSemana && $totalHoras < 21) {
                            $emojiHoras = '🔴';
                        }

                        echo $totalHoras . " " . $emojiHoras;
                    ?>
                </td>

                <!-- Acciones -->
                <td>
                    <form method="POST" class="acciones">
                        <input type="hidden" name="ci" value="<?= $fila['CI'] ?>">
                        <button type="submit" name="accion" value="aceptado" class="btn aceptar">Aceptar</button>
                        <button type="submit" name="accion" value="rechazado" class="btn rechazar">Rechazar</button>
                    </form>
                </td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="sin-usuarios">No hay usuarios registrados o hubo un error en la consulta.</p>
<?php endif; ?>

<script>
    // --- Búsqueda en vivo por CI ---
    document.getElementById('buscar-ci').addEventListener('input', function () {
        const filtro = this.value.trim().toLowerCase();
        const filas = document.querySelectorAll("#tablaUsuarios tbody tr");

        filas.forEach(fila => {
            const ci = fila.querySelector("td").textContent.toLowerCase();
            fila.style.display = ci.includes(filtro) ? '' : 'none';
        });
    });
</script>

</body>
</html>

<?php 
$conexion->close(); 
?>
