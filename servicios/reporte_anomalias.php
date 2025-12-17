<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

try {
    require_once 'conexion.php';
    $conn = ConectarDB();
    $conn->set_charset("utf8mb4");
    
    // Obtener parámetros de filtro
    $estado = $_GET['estado'] ?? 'todos';
    $prioridad = $_GET['prioridad'] ?? 'todos';
    $fecha_desde = $_GET['fecha_desde'] ?? '';
    $fecha_hasta = $_GET['fecha_hasta'] ?? '';
    $responsable = $_GET['responsable'] ?? '';
    
    // Construir consulta base
    $sql = "SELECT 
                a.id,
                a.codigo_seguimiento,
                a.titulo,
                a.descripcion,
                a.prioridad,
                a.categoria,
                a.ubicacion,
                a.estado,
                a.impacto,
                a.costo_estimado,
                a.fecha_creacion,
                a.fecha_actualizacion,
                a.fecha_resolucion,
                a.fecha_limite,
                a.materiales_afectados,
                CONCAT(u_creador.nombre, ' ', u_creador.apellido) as creador,
                CONCAT(u_responsable.nombre, ' ', u_responsable.apellido) as responsable,
                DATEDIFF(COALESCE(a.fecha_resolucion, NOW()), a.fecha_creacion) as dias_transcurridos
            FROM anomalias a
            LEFT JOIN usuarios u_creador ON a.usuario_creador = u_creador.id_usuarios
            LEFT JOIN usuarios u_responsable ON a.responsable_asignado = u_responsable.id_usuarios
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Aplicar filtros
    if ($estado !== 'todos') {
        $sql .= " AND a.estado = ?";
        $params[] = $estado;
        $types .= "s";
    }
    
    if ($prioridad !== 'todos') {
        $sql .= " AND a.prioridad = ?";
        $params[] = $prioridad;
        $types .= "s";
    }
    
    if (!empty($fecha_desde)) {
        $sql .= " AND DATE(a.fecha_creacion) >= ?";
        $params[] = $fecha_desde;
        $types .= "s";
    }
    
    if (!empty($fecha_hasta)) {
        $sql .= " AND DATE(a.fecha_creacion) <= ?";
        $params[] = $fecha_hasta;
        $types .= "s";
    }
    
    if (!empty($responsable)) {
        $sql .= " AND a.responsable_asignado = ?";
        $params[] = intval($responsable);
        $types .= "i";
    }
    
    $sql .= " ORDER BY 
                CASE a.prioridad 
                    WHEN 'urgente' THEN 1 
                    WHEN 'media' THEN 2 
                    WHEN 'baja' THEN 3 
                END,
                a.fecha_creacion DESC";
    
    // Ejecutar consulta
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    
    $anomalias = [];
    while ($row = $result->fetch_assoc()) {
        // Formatear fechas
        $row['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($row['fecha_creacion']));
        if ($row['fecha_actualizacion']) {
            $row['fecha_actualizacion_formateada'] = date('d/m/Y H:i', strtotime($row['fecha_actualizacion']));
        }
        if ($row['fecha_resolucion']) {
            $row['fecha_resolucion_formateada'] = date('d/m/Y H:i', strtotime($row['fecha_resolucion']));
        }
        if ($row['fecha_limite']) {
            $row['fecha_limite_formateada'] = date('d/m/Y', strtotime($row['fecha_limite']));
        }
        
        $anomalias[] = $row;
    }
    
    // Obtener estadísticas del reporte
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'abierta' THEN 1 ELSE 0 END) as abiertas,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
                    SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas,
                    SUM(CASE WHEN prioridad = 'urgente' THEN 1 ELSE 0 END) as urgentes,
                    SUM(CASE WHEN prioridad = 'media' THEN 1 ELSE 0 END) as medias,
                    SUM(CASE WHEN prioridad = 'baja' THEN 1 ELSE 0 END) as bajas,
                    AVG(DATEDIFF(COALESCE(fecha_resolucion, NOW()), fecha_creacion)) as promedio_dias_resolucion,
                    SUM(COALESCE(costo_estimado, 0)) as costo_total_estimado
                  FROM anomalias a
                  WHERE 1=1";
    
    // Aplicar los mismos filtros a las estadísticas
    if ($estado !== 'todos') {
        $stats_sql .= " AND estado = '$estado'";
    }
    if ($prioridad !== 'todos') {
        $stats_sql .= " AND prioridad = '$prioridad'";
    }
    if (!empty($fecha_desde)) {
        $stats_sql .= " AND DATE(fecha_creacion) >= '$fecha_desde'";
    }
    if (!empty($fecha_hasta)) {
        $stats_sql .= " AND DATE(fecha_creacion) <= '$fecha_hasta'";
    }
    if (!empty($responsable)) {
        $stats_sql .= " AND responsable_asignado = " . intval($responsable);
    }
    
    $stats_result = $conn->query($stats_sql);
    $estadisticas = $stats_result->fetch_assoc();
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'anomalias' => $anomalias,
        'estadisticas' => $estadisticas,
        'filtros_aplicados' => [
            'estado' => $estado,
            'prioridad' => $prioridad,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'responsable' => $responsable
        ],
        'total_registros' => count($anomalias)
    ]);
    
} catch (Exception $e) {
    error_log("Error en reporte_anomalias.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>