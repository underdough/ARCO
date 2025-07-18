<?php
require_once 'conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$conexion = ConectarDB();

try {
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST;
    }
    
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
    $categoria_id = isset($input['categoria_id']) ? (int)$input['categoria_id'] : 0;
    $stock = isset($input['stock']) ? (int)$input['stock'] : 0;
    $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : '';
    
    // Validaciones
    if ($id <= 0) {
        throw new Exception('ID de producto inválido');
    }
    
    if (empty($nombre)) {
        throw new Exception('El nombre del producto es requerido');
    }
    
    if ($categoria_id <= 0) {
        throw new Exception('Debe seleccionar una categoría válida');
    }
    
    if ($stock < 0) {
        throw new Exception('El stock no puede ser negativo');
    }
    
    // Verificar que el producto existe
    $sql_verificar = "SELECT id_material FROM materiales WHERE id_material = ?";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->bind_param('i', $id);
    $stmt_verificar->execute();
    $resultado_verificar = $stmt_verificar->get_result();
    
    if ($resultado_verificar->num_rows === 0) {
        throw new Exception('El producto no existe');
    }
    
    // Verificar que la categoría existe
    $sql_categoria = "SELECT id_categorias FROM categorias WHERE id_categorias = ?";
    $stmt_categoria = $conexion->prepare($sql_categoria);
    $stmt_categoria->bind_param('i', $categoria_id);
    $stmt_categoria->execute();
    $resultado_categoria = $stmt_categoria->get_result();
    
    if ($resultado_categoria->num_rows === 0) {
        throw new Exception('La categoría seleccionada no existe');
    }
    
    // Actualizar producto
    $sql = "UPDATE materiales SET 
            nombre_material = ?, 
            id_categorias = ?, 
            stock = ?,
            disponibilidad = CASE WHEN ? > 0 THEN 1 ELSE 0 END
            WHERE id_material = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('siiii', $nombre, $categoria_id, $stock, $stock, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No se realizaron cambios en el producto'
            ]);
        }
    } else {
        throw new Exception('Error al actualizar el producto: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();
?>