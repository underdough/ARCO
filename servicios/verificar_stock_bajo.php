<?php
/**
 * Verificar Stock Bajo - Sistema ARCO
 * Retorna productos con stock por debajo del mínimo
 */

session_start();
require_once "conexion.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

try {
    $conexion = ConectarDB();
    
    // Obtener umbral de stock bajo de las notificaciones del usuario
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conexion->prepare("SELECT low_stock_threshold FROM notificaciones WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $umbral = 15; // Valor por defecto
    if ($result->num_rows > 0) {
        $config = $result->fetch_assoc();
        $umbral = $config['low_stock_threshold'];
    }
    
    // Buscar productos con stock bajo
    $stmt = $conexion->prepare("
        SELECT 
            p.id_productos,
            p.nombre,
            p.stock,
            p.stock_minimo,
            c.nombre as categoria,
            ROUND((p.stock / NULLIF(p.stock_minimo, 0)) * 100, 2) as porcentaje_stock
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
        WHERE p.stock <= p.stock_minimo
        AND p.stock_minimo > 0
        ORDER BY porcentaje_stock ASC
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'productos' => $productos,
        'total' => count($productos)
    ]);
    
} catch (Exception $e) {
    error_log("Error al verificar stock bajo: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al verificar stock'
    ]);
}