<?php
header('Content-Type: application/json');
include_once "conexion.php";

$conexion = ConectarDB();

$sql = "SELECT
    m.id AS id,
    m.fecha,
    m.tipo,
    m.cantidad,
    m.notas,
    u.nombre AS usuario_nombre,
    p.nombre_material AS producto
FROM movimientos m
LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
LEFT JOIN materiales p ON m.producto_id = p.id_material
ORDER BY m.id DESC";


$resultado = $conexion->query($sql);

$movimientos = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $movimientos[] = $fila;
    }

    echo json_encode($movimientos);
} else {
    echo json_encode(['error' => 'Error al consultar movimientos: ' . $conexion->error]);
}
?>
