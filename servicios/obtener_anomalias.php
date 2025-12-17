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
    // Incluir configuración de base de datos
    require_once 'conexion.php';
    
    // Crear conexión
    $conn = ConectarDB();
    
    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    // Configurar charset
    $conn->set_charset("utf8mb4");
    
    // Verificar si existe la tabla anomalias
    $check_table = $conn->query("SHOW TABLES LIKE 'anomalias'");
    if ($check_table->num_rows === 0) {
        throw new Exception("La tabla 'anomalias' no existe. Ejecuta el script de configuración primero.");
    }
    
    // Consulta para obtener todas las anomalías con información del usuario creador
    $sql = "SELECT 
                a.id,
                a.titulo,
                a.descripcion,
                a.prioridad,
                a.categoria,
                a.ubicacion,
                a.estado,
                a.fecha_creacion,
                a.fecha_actualizacion,
                a.fecha_resolucion,
                a.notas_resolucion,
                COALESCE(CONCAT(u_creador.nombre, ' ', u_creador.apellido), 'Usuario desconocido') as usuario_creador,
                COALESCE(CONCAT(u_asignado.nombre, ' ', u_asignado.apellido), NULL) as usuario_asignado
            FROM anomalias a
            LEFT JOIN usuarios u_creador ON a.usuario_creador = u_creador.id_usuarios
            LEFT JOIN usuarios u_asignado ON a.usuario_asignado = u_asignado.id_usuarios
            ORDER BY 
                CASE a.prioridad 
                    WHEN 'urgente' THEN 1 
                    WHEN 'media' THEN 2 
                    WHEN 'baja' THEN 3 
                END,
                a.fecha_creacion DESC";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception("Error en la consulta: " . $conn->error);
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
        
        // Truncar descripción para la vista de tarjetas
        if (strlen($row['descripcion']) > 150) {
            $row['descripcion_corta'] = substr($row['descripcion'], 0, 150) . '...';
        } else {
            $row['descripcion_corta'] = $row['descripcion'];
        }
        
        $anomalias[] = $row;
    }
    
    // Obtener estadísticas adicionales
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN prioridad = 'urgente' THEN 1 ELSE 0 END) as urgentes,
                    SUM(CASE WHEN prioridad = 'media' THEN 1 ELSE 0 END) as medias,
                    SUM(CASE WHEN prioridad = 'baja' THEN 1 ELSE 0 END) as bajas,
                    SUM(CASE WHEN estado = 'abierta' THEN 1 ELSE 0 END) as abiertas,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
                    SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas
                  FROM anomalias";
    
    $stats_result = $conn->query($stats_sql);
    $estadisticas = $stats_result->fetch_assoc();
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'anomalias' => $anomalias,
        'estadisticas' => $estadisticas,
        'total' => count($anomalias)
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_anomalias.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>