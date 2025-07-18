<?php
require 'conexion.php';
header('Content-Type: application/json');

$conn = ConectarDB();
$sql = "SELECT id_categorias, nombre_cat FROM categorias WHERE estado = 1";
$result = $conn->query($sql);

$categorias = [];

while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode($categorias);
$conn->close();
?>
