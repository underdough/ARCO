<?php
require_once 'conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$conexion = ConectarDB();

try {
    // Obtener ID del producto
    $id = 0;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }
        $id = isset($input['id']) ? (int)$input['id'] : 0;
    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        
        // También intentar obtener de la URL
        if ($id === 0 && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
        }
    }
    
    // Validaciones
    if ($id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    // Verificar que el producto existe
    $sql_verificar = "SELECT id_material, nombre_material FROM materiales WHERE id_material = ?";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->bind_param('i', $id);
    $stmt_verificar->execute();
    $resultado_verificar = $stmt_verificar->get_result();
    
    if ($resultado_verificar->num_rows === 0) {
        throw new Exception('El producto no existe');
    }
    
    $producto = $resultado_verificar->fetch_assoc();
    
    // Verificar si el producto tiene movimientos asociados
    $sql_movimientos = "SELECT COUNT(*) as total FROM movimientos WHERE producto_id = ?";
    $stmt_movimientos = $conexion->prepare($sql_movimientos);
    $stmt_movimientos->bind_param('i', $id);
    $stmt_movimientos->execute();
    $resultado_movimientos = $stmt_movimientos->get_result();
    $movimientos = $resultado_movimientos->fetch_assoc();
    
    if ($movimientos['total'] > 0) {
        // Si tiene movimientos, solo marcar como no disponible
        $sql = "UPDATE materiales SET disponibilidad = 0 WHERE id_material = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto desactivado exitosamente (tiene movimientos asociados)',
                'action' => 'deactivated'
            ]);
        } else {
            throw new Exception('Error al desactivar el producto: ' . $stmt->error);
        }
    } else {
        // Si no tiene movimientos, eliminar completamente
        $sql = "DELETE FROM materiales WHERE id_material = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente',
                    'action' => 'deleted'
                ]);
            } else {
                throw new Exception('No se pudo eliminar el producto');
            }
        } else {
            throw new Exception('Error al eliminar el producto: ' . $stmt->error);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();
?>