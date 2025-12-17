<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID de movimiento no proporcionado.");
}

$id = (int) $_GET['id'];
$conexion = ConectarDB();

$sql = "SELECT 
            m.id, 
            m.fecha, 
            m.tipo, 
            m.producto_id, 
            m.cantidad, 
            m.notas, 
            u.nombre AS usuario_nombre
        FROM movimientos m
        LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
        WHERE m.id = $id";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $mov = $resultado->fetch_assoc();
} else {
    die("Movimiento no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Movimiento</title>
    <link rel="stylesheet" href="../componentes/imprimir_mov.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <h1>Detalle del Movimiento</h1>
    <table>
        <tr><td><strong>ID</strong></td><td><?= $mov['id'] ?></td></tr>
        <tr><td><strong>Fecha</strong></td><td><?= $mov['fecha'] ?></td></tr>
        <tr><td><strong>Tipo</strong></td><td><?= ucfirst($mov['tipo']) ?></td></tr>
        <tr><td><strong>ID Producto</strong></td><td><?= $mov['producto_id'] ?></td></tr>
        <tr><td><strong>Cantidad</strong></td><td><?= $mov['cantidad'] ?></td></tr>
        <tr><td><strong>Usuario</strong></td><td><?= $mov['usuario_nombre'] ?></td></tr>
        <tr><td><strong>Notas</strong></td><td><?= $mov['notas'] ?></td></tr>
    </table>
    <br>
    <button class="no-print" onclick="window.location.href='../vistas/movimientos.php'">Volver</button>
</body>
</html>
