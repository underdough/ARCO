<?php
/**
 * Servicio para listar productos con paginación, búsqueda y ordenamiento
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
    
    // Parámetros de búsqueda y ordenamiento
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre';
    $direccion = isset($_GET['direccion']) ? strtoupper($_GET['direccion']) : 'ASC';
    
    // Validar parámetros
    if ($pagina < 1) $pagina = 1;
    if ($limite < 1 || $limite > 100) $limite = 10;
    if (!in_array($direccion, ['ASC', 'DESC'])) $direccion = 'ASC';
    
    // Mapeo de campos de ordenamiento
    $camposOrden = [
        'nombre' => 'm.nombre_material',
        'categoria' => 'c.nombre_cat',
        'stock' => 'm.stock',
        'precio' => 'm.precio'
    ];
    
    $campoOrden = isset($camposOrden[$orden]) ? $camposOrden[$orden] : 'm.nombre_material';
    
    // Construir condición de búsqueda
    $whereClause = '';
    $params = [];
    $types = '';
    
    if (!empty($busqueda)) {
        $whereClause = "WHERE (m.nombre_material LIKE ? OR c.nombre_cat LIKE ?)";
        $searchTerm = "%$busqueda%";
        $params = [$searchTerm, $searchTerm];
        $types = 'ss';
    }
    
    // Obtener total de registros
    $queryTotal = "SELECT COUNT(*) as total 
                   FROM materiales m
                   LEFT JOIN categorias c ON m.id_categorias = c.id_categorias
                   $whereClause";
    
    if (!empty($params)) {
        $stmtTotal = $conexion->prepare($queryTotal);
        if (!$stmtTotal) {
            throw new Exception("Error preparando consulta de total: " . $conexion->error);
        }
        $stmtTotal->bind_param($types, ...$params);
        if (!$stmtTotal->execute()) {
            throw new Exception("Error ejecutando consulta de total: " . $stmtTotal->error);
        }
        $resultTotal = $stmtTotal->get_result();
    } else {
        $resultTotal = $conexion->query($queryTotal);
        if (!$resultTotal) {
            throw new Exception("Error en consulta de total: " . $conexion->error);
        }
    }
    
    $row = $resultTotal->fetch_assoc();
    $total = $row ? $row['total'] : 0;
    
    // Obtener productos con paginación
    $query = "SELECT 
                m.id_material as id,
                m.nombre_material as nombre,
                COALESCE(c.nombre_cat, 'Sin categoría') as categoria,
                COALESCE(m.stock, 0) as stock,
                COALESCE(m.precio, 0) as precio,
                '' as descripcion,
                COALESCE(m.disponibilidad, 1) as disponibilidad,
                CASE 
                    WHEN COALESCE(m.stock, 0) = 0 THEN 'Agotado'
                    WHEN COALESCE(m.stock, 0) <= 10 THEN 'Stock Bajo'
                    ELSE 'Disponible'
                END as estado
              FROM materiales m
              LEFT JOIN categorias c ON m.id_categorias = c.id_categorias
              $whereClause
              ORDER BY $campoOrden $direccion
              LIMIT ? OFFSET ?";
    
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error preparando consulta de productos: " . $conexion->error);
    }
    
    if (!empty($params)) {
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param("ii", $limite, $offset);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando consulta de productos: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Error obteniendo resultados de productos: " . $stmt->error);
    }
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $productos,
        'productos' => $productos,
        'total' => (int)$total,
        'pagina' => $pagina,
        'limite' => $limite,
        'total_paginas' => ceil($total / $limite)
    ]);
    
} catch (Exception $e) {
    error_log("Error en listar_productos.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener los productos',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

$conexion->close();
