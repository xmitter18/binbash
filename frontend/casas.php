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

// Obtener todas las casas como arreglo asociativo
$casas = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <a href="usuario.php?ci=<?= urlencode($ci) ?>" class="btn-atras">Atrás</a>
    <div class="encabezado-casas">
        <img src="img/Logocooperativa.png" alt="Logo Cooperativa" width="100px">
    </div>
    <div class="contenedor-casas">
        <div class="casa">
            <img src="img/CASA1.png" alt="Casa 1">
            <p>Casas Montes<br>Precio estimado de la casa: USD 88.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA2.png" alt="Casa 2">
            <p>Casas Bellavista<br>Precio estimado de la casa: USD 105.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA3.png" alt="Casa 3">
            <p>Casas Julio Cesar<br>Precio estimado de la casa: USD 82.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA4.png" alt="Casa 4">
            <p>Fila de viviendas verdes<br>USD 45.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA5.png" alt="Casa 5">
            <p>Residencias Horizonte<br>USD 118.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA6.png" alt="Casa 6">
            <p>Barrio Amanecer<br>USD 83.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA7.png" alt="Casa 7">
            <p>Villa Los Ceibos<br>USD 72.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA8.png" alt="Casa 8">
            <p>Casa Jardín del Río<br>USD 132.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>

        <div class="casa">
            <img src="img/CASA9.png" alt="Casa 9">
            <p>Residencias Terracota<br>USD 155.000</p>
            <button class="btn-ver-propiedades">Trabajar</button>
        </div>
    </div>

</body>
</html>
