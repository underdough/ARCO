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
    
    // Parámetros de filtro
    $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
    $orden = isset($_GET['orden']) ? $_GET['orden'] : 'id_desc';
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
    
    // Validar parámetros
    if ($pagina < 1) $pagina = 1;
    if ($limite < 1 || $limite > 100) $limite = 10;
    
    // Construir condiciones WHERE
    $whereConditions = [];
    $params = [];
    $types = '';
    
    if ($estado !== '') {
        $whereConditions[] = "c.estado = ?";
        $params[] = (int)$estado;
        $types .= 'i';
    }
    
    if ($busqueda !== '') {
        $whereConditions[] = "(c.nombre_cat LIKE ? OR c.subcategorias LIKE ?)";
        $searchTerm = "%{$busqueda}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    
    $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Determinar ORDER BY
    $orderBy = 'c.id_categorias DESC';
    switch ($orden) {
        case 'id_asc':
            $orderBy = 'c.id_categorias ASC';
            break;
        case 'id_desc':
            $orderBy = 'c.id_categorias DESC';
            break;
        case 'nombre_asc':
            $orderBy = 'c.nombre_cat ASC';
            break;
        case 'nombre_desc':
            $orderBy = 'c.nombre_cat DESC';
            break;
        case 'productos_asc':
            $orderBy = 'productos ASC';
            break;
        case 'productos_desc':
            $orderBy = 'productos DESC';
            break;
    }
    
    // Obtener total de registros con filtros
    $queryTotal = "SELECT COUNT(DISTINCT c.id_categorias) as total 
                   FROM categorias c 
                   LEFT JOIN materiales m ON c.id_categorias = m.id_categorias 
                   {$whereClause}";
    
    if (count($params) > 0) {
        $stmtTotal = $conexion->prepare($queryTotal);
        if (!$stmtTotal) {
            throw new Exception("Error preparando consulta de total: " . $conexion->error);
        }
        $stmtTotal->bind_param($types, ...$params);
        $stmtTotal->execute();
        $resultTotal = $stmtTotal->get_result();
    } else {
        $resultTotal = $conexion->query($queryTotal);
    }
    
    if (!$resultTotal) {
        throw new Exception("Error en consulta de total: " . $conexion->error);
    }
    
    $row = $resultTotal->fetch_assoc();
    $total = $row ? $row['total'] : 0;
    
    // Obtener categorías con paginación y filtros
    $query = "SELECT 
                c.id_categorias,
                c.nombre_cat,
                c.subcategorias,
                c.estado,
                COUNT(m.id_material) as productos
              FROM categorias c
              LEFT JOIN materiales m ON c.id_categorias = m.id_categorias
              {$whereClause}
              GROUP BY c.id_categorias, c.nombre_cat, c.subcategorias, c.estado
              ORDER BY {$orderBy}
              LIMIT ? OFFSET ?";
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conexion->error);
    }
    
    // Agregar parámetros de límite y offset
    $params[] = $limite;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt->bind_param($types, ...$params);
    
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
