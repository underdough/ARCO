<?php
/**
 * Servicio para listar categorías con paginación
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

require_once 'conexion.php';
$conexion = ConectarDB();

header('Content-Type: application/json');

try {
    // Parámetros de paginación
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    $offset = ($pagina - 1) * $limite;
    
    // Validar parámetros
    if ($pagina < 1) $pagina = 1;
    if ($limite < 1 || $limite > 100) $limite = 10;
    
    // Obtener total de registros
    $queryTotal = "SELECT COUNT(*) as total FROM categorias";
    $resultTotal = $conexion->query($queryTotal);
    
    if (!$resultTotal) {
        throw new Exception("Error en consulta de total: " . $conexion->error);
    }
    
    $row = $resultTotal->fetch_assoc();
    $total = $row ? $row['total'] : 0;
    
    // Obtener categorías con paginación
    $query = "SELECT 
                c.id_categorias,
                c.nombre_cat,
                c.subcategorias,
                c.estado,
                COUNT(m.id_material) as productos
              FROM categorias c
              LEFT JOIN materiales m ON c.id_categorias = m.id_categorias
              GROUP BY c.id_categorias, c.nombre_cat, c.subcategorias, c.estado
              ORDER BY c.id_categorias DESC
              LIMIT ? OFFSET ?";
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("ii", $limite, $offset);
    
    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Error obteniendo resultados: " . $stmt->error);
    }
    
    $categorias = [];
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $categorias,
        'categorias' => $categorias,
        'total' => (int)$total,
        'pagina' => $pagina,
        'limite' => $limite,
        'total_paginas' => ceil($total / $limite)
    ]);
    
} catch (Exception $e) {
    error_log("Error en listar_categorias.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener las categorías',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

$conexion->close();
