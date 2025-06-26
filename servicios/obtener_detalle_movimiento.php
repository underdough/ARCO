<?php
require_once "conexion.php";
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$conexion = ConectarDB();
$id = (int) $_GET['id'];

$sql = "SELECT m.id, m.fecha, m.tipo, m.producto_id, m.cantidad, m.notas, 
               u.nombre AS usuario_nombre,
               m.producto_id AS producto
        FROM movimientos m
        LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
        WHERE m.id = $id";

$resultado = $conexion->query($sql);
if ($resultado && $row = $resultado->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Movimiento no encontrado']);
}
