<?php
require 'conexion.php';
header('Content-Type: application/json');

$conn = ConectarDB();

$nombre = $_POST['nombre_cat'] ?? '';
$descripcion = $_POST['subcategorias'] ?? '';
$estado = $_POST['estado'] ?? '';
$productos = $_POST['productos'] ?? 0;

if ($nombre === '' || $estado === '') {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
    exit;
}

$estado = (int)$estado;
$productos = (int)$productos;

$stmt = $conn->prepare("INSERT INTO categorias (nombre_cat, subcategorias, estado, productos) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta']);
    exit;
}

$stmt->bind_param("ssii", $nombre, $descripcion, $estado, $productos);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Categoría agregada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
}
