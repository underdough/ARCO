<?php
require 'conexion.php';
header('Content-Type: application/json');

$conn = ConectarDB();

$id = $_GET['id'] ?? '';
$nombre = $_POST['nombre_cat'] ?? '';
$descripcion = $_POST['subcategorias'] ?? '';
$estado = $_POST['estado'] ?? 1;
$productos = $_POST['productos'] ?? 0;

if (empty($id) || empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$estado = (int)$estado;
$productos = (int)$productos;

$stmt = $conn->prepare("UPDATE categorias SET nombre_cat = ?, subcategorias = ?, estado = ?, productos = ? WHERE id_categorias = ?");
$stmt->bind_param("ssiii", $nombre, $descripcion, $estado, $productos, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
}
