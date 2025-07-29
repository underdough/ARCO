<?php
header('Content-Type: application/json');
include_once "conexion.php";

$conexion = ConectarDB();

$sql = "SELECT id_material AS id, nombre_material AS nombre FROM materiales";
$resultado = $conexion->query($sql);

$productos = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
    echo json_encode($productos);
} else {
    echo json_encode(['error' => 'Error al consultar productos: ' . $conexion->error]);
}
?>
