<?php
$conexion = new mysqli("db", "usuario", "pass", "cooperativa");
if ($conexion->connect_error) die("Conexión fallida: " . $conexion->connect_error);

// --- Aceptar o rechazar usuarios ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'], $_POST['ci'])) {
    $accion = $_POST['accion'];
    $ci = (int) $_POST['ci'];

    if (in_array($accion, ['aceptado', 'rechazado'])) {
        $conexion->query("UPDATE Usuario SET activo = '$accion' WHERE CI = $ci");
    } elseif ($accion === 'eliminar') {
        $conexion->query("DELETE FROM Usuario WHERE CI = $ci");
        $conexion->query("DELETE FROM Persona WHERE CI = $ci");
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
        <th>Casa actual</th>
        <th>Horas en casa actual</th>
        <th>Horas totales</th>
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

<!-- Casa actual -->
<td>
    <?php
        $stmtCasa = $conexion->prepare("
            SELECT c.nombre, t.casa_id 
            FROM Trabajo t
            JOIN casas c ON t.casa_id = c.id
            WHERE t.CI = ?
        ");
        $stmtCasa->bind_param("i", $ci);
        $stmtCasa->execute();
        $resCasa = $stmtCasa->get_result()->fetch_assoc();

        $nombreCasa = $resCasa['nombre'] ?? 'Ninguna';
        $casaId = $resCasa['casa_id'] ?? null;

        echo htmlspecialchars($nombreCasa);
    ?>
</td>


              <!-- Horas trabajadas en casa actual (solo semana actual) -->
<td>
    <?php
        $horasSemana = 0;
        if ($casaId) {
            // lunes de esta semana
            $lunesSemana = date('Y-m-d', strtotime('monday this week'));

            $stmtHorasSemana = $conexion->prepare("
                SELECT horas 
                FROM HorasTrabajo 
                WHERE CI = ? AND casa_id = ? AND semana = ?
            ");
            $stmtHorasSemana->bind_param("iis", $ci, $casaId, $lunesSemana);
            $stmtHorasSemana->execute();
            $resHorasSemana = $stmtHorasSemana->get_result()->fetch_assoc();
            $horasSemana = $resHorasSemana['horas'] ?? 0;
        }

        // Emoji rojo si trabajó menos de 21 horas
        $emoji = ($horasSemana < 21) ? '🔴' : '';

        echo $horasSemana . " " . $emoji;
    ?>
</td>


<!-- Horas trabajadas totales -->
<td>
    <?php
        $stmtHorasTotales = $conexion->prepare("
            SELECT SUM(horas) as total
            FROM HorasTrabajo
            WHERE CI = ?
        ");
        $stmtHorasTotales->bind_param("i", $ci);
        $stmtHorasTotales->execute();
        $resHorasTotales = $stmtHorasTotales->get_result()->fetch_assoc();
        $horasTotales = $resHorasTotales['total'] ?? 0;

        echo $horasTotales;
    ?>
</td>



                <!-- Acciones -->
               <td>
    <form method="POST" class="acciones" style="display:inline-block;">
        <input type="hidden" name="ci" value="<?= $fila['CI'] ?>">
        <button type="submit" name="accion" value="aceptado" class="btn aceptar">Aceptar</button>
        <button type="submit" name="accion" value="rechazado" class="btn rechazar">Rechazar</button>
    </form>
    <form method="GET" action="editar_usuario.php" style="display:inline-block;">
        <input type="hidden" name="ci" value="<?= $fila['CI'] ?>">
        <button type="submit" class="btn editar">Editar</button>
    </form>
    <form method="POST" class="acciones" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="ci" value="<?= $fila['CI'] ?>">
    <button type="submit" class="btn eliminar">Eliminar</button>
</form>
<form method="GET" action="chat_admin.php" style="display:inline-block;">
    <input type="hidden" name="ci" value="<?= $fila['CI'] ?>">
    <button type="submit" class="btn chat" >Chat</button>
</form>

</td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="agregar_usuario.php" class="btn-ver-propiedades">Agregar usuario</a>

<br><br>

    <a href="/casas.php?ci=<?= urlencode($ci) ?>&origen=backofice" class="btn-ver-propiedades">Ver propiedades disponibles</a>

<h2>Casas Registradas</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Imagen</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmtCasas = $conexion->query("SELECT id, nombre, precio, imagen FROM casas ORDER BY id ASC");
        while ($casa = $stmtCasas->fetch_assoc()):
        ?>
        <tr>
            <td><?= $casa['id'] ?></td>
            <td><?= htmlspecialchars($casa['nombre']) ?></td>
            <td>USD <?= number_format($casa['precio'], 2) ?></td>
            <td>
                <img src="/<?= htmlspecialchars($casa['imagen']) ?>" alt="casa" width="120">
            </td>
            <td>
                <form method="POST" action="eliminar_casa.php" onsubmit="return confirm('¿Seguro que quieres eliminar esta casa?');">
                    <input type="hidden" name="id" value="<?= $casa['id'] ?>">
                    <button type="submit" class="btn rechazar">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
    <br><br>

    <a href="agregar_casa.php" class="btn-ver-propiedades">Agregar nueva casa</a>

    <br><br>

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
