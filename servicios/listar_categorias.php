<?php
require 'conexion.php';
header('Content-Type: application/json');

$conn = ConectarDB();

$sql = "SELECT id_categorias, nombre_cat, subcategorias, estado, productos FROM categorias";
$result = $conn->query($sql);

$categorias = [];

while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode($categorias);
