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
    
    $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
    $categoria_id = isset($input['categoria_id']) ? (int)$input['categoria_id'] : 0;
    $stock = isset($input['stock']) ? (int)$input['stock'] : 0;
    $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : '';
    
    // Validaciones
    if (empty($nombre)) {
        throw new Exception('El nombre del producto es requerido');
    }
    
    if ($categoria_id <= 0) {
        throw new Exception('Debe seleccionar una categoría válida');
    }
    
    if ($stock < 0) {
        throw new Exception('El stock no puede ser negativo');
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
    
    // Insertar producto
    $sql = "INSERT INTO materiales (nombre_material, id_categorias, stock, disponibilidad, minimo_alarma, conf_recibido) 
            VALUES (?, ?, ?, 1, 5, 'recibido')";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sii', $nombre, $categoria_id, $stock);
    
    if ($stmt->execute()) {
        $producto_id = $conexion->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto agregado exitosamente',
            'producto_id' => $producto_id
        ]);
    } else {
        throw new Exception('Error al insertar el producto: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();
?>