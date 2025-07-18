<?php
require 'conexion.php';
header('Content-Type: application/json');

$conn = ConectarDB();

$sql = "SELECT id_categorias, nombre_cat, subcategorias, productos, estado FROM categorias";
$result = $conn->query($sql);

$categorias = [];

while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $categorias
]);
