<?php
require 'conexion.php';
header('Content-Type: application/json');

$conn = ConectarDB(); // ✅ Establecer la conexión

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM categorias WHERE id_categorias = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Categoría eliminada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar categoría']);
}
?>
