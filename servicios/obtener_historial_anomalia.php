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

// Verificar que se proporcione el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID de anomalía no proporcionado'
    ]);
    exit;
}

try {
    require_once 'conexion.php';
    $conn = ConectarDB();
    $conn->set_charset("utf8mb4");
    
    $anomalia_id = intval($_GET['id']);
    $historial = [];
    
    // Obtener historial de la tabla anomalias_historial (si existe)
    $check_historial_table = $conn->query("SHOW TABLES LIKE 'anomalias_historial'");
    if ($check_historial_table && $check_historial_table->num_rows > 0) {
        $sql_historial = "SELECT 
                            h.campo_modificado,
                            h.valor_anterior,
                            h.valor_nuevo,
                            h.fecha_modificacion,
                            h.comentario,
                            CONCAT(u.nombre, ' ', u.apellido) as usuario
                          FROM anomalias_historial h
                          LEFT JOIN usuarios u ON h.usuario_modificador = u.id_usuarios
                          WHERE h.anomalia_id = ?
                          ORDER BY h.fecha_modificacion DESC";
        
        $stmt = $conn->prepare($sql_historial);
        if ($stmt) {
            $stmt->bind_param("i", $anomalia_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $historial[] = [
                    'tipo' => 'modificacion',
                    'campo_modificado' => $row['campo_modificado'],
                    'valor_anterior' => $row['valor_anterior'],
                    'valor_nuevo' => $row['valor_nuevo'],
                    'fecha_modificacion' => $row['fecha_modificacion'],
                    'comentario' => $row['comentario'],
                    'usuario' => $row['usuario']
                ];
            }
            $stmt->close();
        }
    }
    
    // Obtener historial de auditoría (si existe)
    $check_auditoria_table = $conn->query("SHOW TABLES LIKE 'anomalias_auditoria'");
    if ($check_auditoria_table && $check_auditoria_table->num_rows > 0) {
        $sql_auditoria = "SELECT 
                            a.accion,
                            a.descripcion_accion,
                            a.fecha_accion,
                            a.datos_anteriores,
                            a.datos_nuevos,
                            CONCAT(u.nombre, ' ', u.apellido) as usuario
                          FROM anomalias_auditoria a
                          LEFT JOIN usuarios u ON a.usuario_id = u.id_usuarios
                          WHERE a.anomalia_id = ?
                          ORDER BY a.fecha_accion DESC";
        
        $stmt = $conn->prepare($sql_auditoria);
        if ($stmt) {
            $stmt->bind_param("i", $anomalia_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $historial[] = [
                    'tipo' => 'auditoria',
                    'accion' => $row['accion'],
                    'descripcion_accion' => $row['descripcion_accion'],
                    'fecha_accion' => $row['fecha_accion'],
                    'datos_anteriores' => $row['datos_anteriores'],
                    'datos_nuevos' => $row['datos_nuevos'],
                    'usuario' => $row['usuario']
                ];
            }
            $stmt->close();
        }
    }
    
    // Si no hay historial específico, crear uno básico con la información de la anomalía
    if (empty($historial)) {
        $sql_basico = "SELECT 
                        a.fecha_creacion,
                        a.fecha_actualizacion,
                        a.fecha_resolucion,
                        CONCAT(u.nombre, ' ', u.apellido) as creador
                       FROM anomalias a
                       LEFT JOIN usuarios u ON a.usuario_creador = u.id_usuarios
                       WHERE a.id = ?";
        
        $stmt = $conn->prepare($sql_basico);
        if ($stmt) {
            $stmt->bind_param("i", $anomalia_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $historial[] = [
                    'tipo' => 'creacion',
                    'accion' => 'crear',
                    'descripcion_accion' => 'Anomalía registrada en el sistema',
                    'fecha_accion' => $row['fecha_creacion'],
                    'usuario' => $row['creador']
                ];
                
                if ($row['fecha_actualizacion'] && $row['fecha_actualizacion'] != $row['fecha_creacion']) {
                    $historial[] = [
                        'tipo' => 'actualizacion',
                        'accion' => 'actualizar',
                        'descripcion_accion' => 'Anomalía actualizada',
                        'fecha_accion' => $row['fecha_actualizacion'],
                        'usuario' => 'Sistema'
                    ];
                }
                
                if ($row['fecha_resolucion']) {
                    $historial[] = [
                        'tipo' => 'resolucion',
                        'accion' => 'resolver',
                        'descripcion_accion' => 'Anomalía marcada como resuelta',
                        'fecha_accion' => $row['fecha_resolucion'],
                        'usuario' => 'Sistema'
                    ];
                }
            }
            $stmt->close();
        }
    }
    
    // Ordenar historial por fecha (más reciente primero)
    usort($historial, function($a, $b) {
        $fecha_a = $a['fecha_accion'] ?? $a['fecha_modificacion'] ?? '1970-01-01';
        $fecha_b = $b['fecha_accion'] ?? $b['fecha_modificacion'] ?? '1970-01-01';
        return strtotime($fecha_b) - strtotime($fecha_a);
    });
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'historial' => $historial,
        'total' => count($historial)
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_historial_anomalia.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>