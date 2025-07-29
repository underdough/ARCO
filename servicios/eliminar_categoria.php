<?php
require_once 'conexion.php';
require_once 'registrar_historial.php';
session_start();
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
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    if ($usuario_id) {
        registrarHistorial($usuario_id, 'eliminar', 'Eliminó la categoría ID: ' . $id);
    }
    echo json_encode(['success' => true, 'message' => 'Categoría eliminada']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar categoría']);
}
?>
