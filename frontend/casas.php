<?php
// Conexión a la base de datos
require __DIR__ . '/backend/conexion.php';

$ci = $_GET['ci'] ?? null;
if (!$ci) {
    echo "CI no proporcionado";
    exit();
}

// Consulta para obtener las casas
$sql = "SELECT id, nombre, precio, imagen FROM casas";
$stmt = $conn->query($sql);
$casas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$origen = $_GET['origen'] ?? 'usuario'; // por defecto usuario
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casas Disponibles</title>
    <link rel="stylesheet" href="estilo.css?v=<?= time() ?>">
</head>
<body>  

    <?php if ($origen === 'backofice'): ?>
        <a href="backend/backofice.php" class="btn-atras">Atrás</a>
    <?php else: ?>
        <a href="usuario.php?ci=<?= urlencode($ci) ?>" class="btn-atras">Atrás</a>
    <?php endif; ?>

    <div class="encabezado-casas">
        <img src="img/Logocooperativa.png" alt="Logo Cooperativa" width="100px">
    </div>

    <div class="contenedor-casas">
        <?php if ($casas && count($casas) > 0): ?>
            <?php foreach ($casas as $casa): ?>
                <div class="casa">
                    <img src="<?= htmlspecialchars($casa['imagen']) ?>" alt="Imagen de <?= htmlspecialchars($casa['nombre']) ?>">
                    <p><?= htmlspecialchars($casa['nombre']) ?><br>USD <?= number_format($casa['precio'], 2) ?></p>
                    <button class="btn-ver-propiedades"
                            onclick="trabajar(<?= $casa['id'] ?>,'<?= htmlspecialchars($casa['nombre']) ?>')">
                        Trabajar
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No hay casas registradas todavía.</p>
        <?php endif; ?>
    </div>

    <form id="formTrabajar" method="POST" action="trabajar.php" style="display:none;">
        <input type="hidden" name="ci" value="<?= htmlspecialchars($ci) ?>">
        <input type="hidden" name="casa_id" id="casa_id">
    </form>

    <script>
        function trabajar(id, nombre) {
            if (confirm(`¿Quieres trabajar para la casa ${nombre}?`)) {
                document.getElementById('casa_id').value = id;
                document.getElementById('formTrabajar').submit();
            }
        }
    </script>

</body>
</html>
