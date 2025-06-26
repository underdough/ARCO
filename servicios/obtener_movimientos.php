<?php
header('Content-Type: application/json');
include_once "conexion.php";

$conexion = ConectarDB();

$sql = "SELECT 
            m.id,
            m.tipo,
            m.fecha,
            m.producto_id,
            m.cantidad,
            m.usuario_id,
            u.nombre AS usuario_nombre,
            m.notas
        FROM movimientos m
        LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
        ORDER BY m.id ASC";

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
