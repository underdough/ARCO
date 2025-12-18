<?php
/**
 * API de Estadísticas
 * Provee datos para el módulo de estadísticas
 */

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

// Verificar permisos
$rolesAutorizados = ['administrador', 'gerente', 'supervisor'];
if (!in_array($_SESSION['rol'], $rolesAutorizados)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Sin permisos']);
    exit;
}

require_once 'conexion.php';
$conexion = ConectarDB();

header('Content-Type: application/json');

$tipo = $_GET['tipo'] ?? '';

// Log para depuración
error_log("Estadísticas - Tipo solicitado: $tipo");
error_log("Estadísticas - Usuario: " . $_SESSION['usuario_id'] . " - Rol: " . $_SESSION['rol']);

try {
    switch ($tipo) {
        case 'resumen':
            $resultado = obtenerResumen($conexion);
            error_log("Estadísticas - Resumen: " . json_encode($resultado));
            echo json_encode($resultado);
            break;
        
        case 'movimientos_mes':
            $year = $_GET['year'] ?? date('Y');
            $resultado = obtenerMovimientosPorMes($conexion, $year);
            error_log("Estadísticas - Movimientos mes ($year): " . json_encode($resultado));
            echo json_encode($resultado);
            break;
        
        case 'categorias':
            $resultado = obtenerProductosPorCategoria($conexion);
            error_log("Estadísticas - Categorías: " . json_encode($resultado));
            echo json_encode($resultado);
            break;
        
        case 'stock_categorias':
            $resultado = obtenerStockPorCategoria($conexion);
            error_log("Estadísticas - Stock categorías: " . json_encode($resultado));
            echo json_encode($resultado);
            break;
        
        case 'tipos_movimiento':
            $dias = $_GET['dias'] ?? 30;
            $resultado = obtenerMovimientosPorTipo($conexion, $dias);
            error_log("Estadísticas - Tipos movimiento ($dias días): " . json_encode($resultado));
            echo json_encode($resultado);
            break;
        
        default:
            error_log("Estadísticas - Tipo no válido: $tipo");
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Tipo no válido: ' . $tipo]);
    }
} catch (Exception $e) {
    error_log("Estadísticas - Error: " . $e->getMessage());
    error_log("Estadísticas - Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();

/**
 * Obtiene estadísticas resumidas principales
 */
function obtenerResumen($conexion) {
    $data = [];
    
    // Total de productos
    $query = "SELECT COUNT(*) as total FROM materiales";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de productos: " . $conexion->error);
    }
    $data['total_productos'] = $result->fetch_assoc()['total'];
    
    // Cambio vs mes anterior (basado en movimientos de entrada)
    $query = "SELECT COUNT(DISTINCT producto_id) as total 
              FROM movimientos 
              WHERE tipo = 'entrada' 
              AND fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de cambio productos: " . $conexion->error);
    }
    $nuevosProductos = $result->fetch_assoc()['total'];
    $data['cambio_productos'] = $data['total_productos'] > 0 ? round(($nuevosProductos / $data['total_productos']) * 100, 1) : 0;
    
    // Movimientos del mes actual
    $query = "SELECT COUNT(*) as total FROM movimientos 
              WHERE MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de movimientos mes: " . $conexion->error);
    }
    $data['movimientos_mes'] = $result->fetch_assoc()['total'];
    
    // Movimientos del mes anterior
    $query = "SELECT COUNT(*) as total FROM movimientos 
              WHERE MONTH(fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
              AND YEAR(fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de movimientos mes anterior: " . $conexion->error);
    }
    $movimientosMesAnterior = $result->fetch_assoc()['total'];
    
    // Calcular cambio porcentual
    if ($movimientosMesAnterior > 0) {
        $data['cambio_movimientos'] = round((($data['movimientos_mes'] - $movimientosMesAnterior) / $movimientosMesAnterior) * 100, 1);
    } else {
        $data['cambio_movimientos'] = $data['movimientos_mes'] > 0 ? 100 : 0;
    }
    
    // Stock total
    $query = "SELECT COALESCE(SUM(stock), 0) as total FROM materiales";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de stock total: " . $conexion->error);
    }
    $data['stock_total'] = $result->fetch_assoc()['total'];
    
    // Calcular cambio de stock basado en movimientos del mes
    $query = "SELECT 
                SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo = 'salida' THEN cantidad ELSE 0 END) as salidas
              FROM movimientos 
              WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de cambio stock: " . $conexion->error);
    }
    $movStock = $result->fetch_assoc();
    $cambioStock = ($movStock['entradas'] ?? 0) - ($movStock['salidas'] ?? 0);
    
    if ($data['stock_total'] > 0) {
        $data['cambio_stock'] = round(($cambioStock / $data['stock_total']) * 100, 1);
    } else {
        $data['cambio_stock'] = 0;
    }
    
    // Alertas de stock bajo (stock <= 10)
    $query = "SELECT COUNT(*) as total FROM materiales WHERE stock <= 10";
    $result = $conexion->query($query);
    if (!$result) {
        throw new Exception("Error en consulta de alertas stock: " . $conexion->error);
    }
    $data['alertas_stock'] = $result->fetch_assoc()['total'];
    
    return ['success' => true, 'data' => $data];
}

/**
 * Obtiene movimientos agrupados por mes
 */
function obtenerMovimientosPorMes($conexion, $year) {
    $entradas = array_fill(0, 12, 0);
    $salidas = array_fill(0, 12, 0);
    
    // Obtener entradas por mes
    $query = "SELECT MONTH(fecha) as mes, COUNT(*) as total 
              FROM movimientos 
              WHERE YEAR(fecha) = ? AND tipo = 'entrada'
              GROUP BY MONTH(fecha)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $entradas[$row['mes'] - 1] = (int)$row['total'];
    }
    
    // Obtener salidas por mes
    $query = "SELECT MONTH(fecha) as mes, COUNT(*) as total 
              FROM movimientos 
              WHERE YEAR(fecha) = ? AND tipo = 'salida'
              GROUP BY MONTH(fecha)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $salidas[$row['mes'] - 1] = (int)$row['total'];
    }
    
    return [
        'success' => true,
        'data' => [
            'entradas' => $entradas,
            'salidas' => $salidas
        ]
    ];
}

/**
 * Obtiene cantidad de productos por categoría
 */
function obtenerProductosPorCategoria($conexion) {
    $query = "SELECT COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
              COUNT(m.id_material) as total
              FROM materiales m
              LEFT JOIN categorias c ON m.id_categorias = c.id_categorias
              GROUP BY c.id_categorias, c.nombre_cat
              ORDER BY total DESC
              LIMIT 10";
    
    $result = $conexion->query($query);
    
    $labels = [];
    $values = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['categoria'];
        $values[] = (int)$row['total'];
    }
    
    return [
        'success' => true,
        'data' => [
            'labels' => $labels,
            'values' => $values
        ]
    ];
}

/**
 * Obtiene stock total por categoría
 */
function obtenerStockPorCategoria($conexion) {
    $query = "SELECT COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
              COALESCE(SUM(m.stock), 0) as stock_total
              FROM materiales m
              LEFT JOIN categorias c ON m.id_categorias = c.id_categorias
              GROUP BY c.id_categorias, c.nombre_cat
              ORDER BY stock_total DESC
              LIMIT 10";
    
    $result = $conexion->query($query);
    
    $labels = [];
    $values = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['categoria'];
        $values[] = (int)$row['stock_total'];
    }
    
    return [
        'success' => true,
        'data' => [
            'labels' => $labels,
            'values' => $values
        ]
    ];
}

/**
 * Obtiene movimientos por tipo en un período
 */
function obtenerMovimientosPorTipo($conexion, $dias) {
    $query = "SELECT tipo, COUNT(*) as total
              FROM movimientos
              WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY tipo
              ORDER BY total DESC";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $dias);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    $values = [];
    
    while ($row = $result->fetch_assoc()) {
        $labels[] = ucfirst($row['tipo']);
        $values[] = (int)$row['total'];
    }
    
    return [
        'success' => true,
        'data' => [
            'labels' => $labels,
            'values' => $values
        ]
    ];
}
