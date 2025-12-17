<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

try {
    require_once 'conexion.php';
    $conn = ConectarDB();
    $conn->set_charset("utf8mb4");
    
    // Obtener datos del formulario
    $anomalia_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nuevo_estado = trim($_POST['estado'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];
    
    // Validaciones
    if ($anomalia_id <= 0) {
        throw new Exception("ID de anomalía inválido");
    }
    
    if (!in_array($nuevo_estado, ['abierta', 'en_proceso', 'resuelta', 'cerrada'])) {
        throw new Exception("Estado inválido");
    }
    
    // Obtener datos actuales de la anomalía
    $sql_actual = "SELECT estado, titulo FROM anomalias WHERE id = ?";
    $stmt_actual = $conn->prepare($sql_actual);
    if (!$stmt_actual) {
        throw new Exception("Error al preparar consulta: " . $conn->error);
    }
    
    $stmt_actual->bind_param("i", $anomalia_id);
    $stmt_actual->execute();
    $result = $stmt_actual->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Anomalía no encontrada");
    }
    
    $anomalia_actual = $result->fetch_assoc();
    $estado_anterior = $anomalia_actual['estado'];
    $titulo_anomalia = $anomalia_actual['titulo'];
    $stmt_actual->close();
    
    // Verificar si realmente hay cambio
    if ($estado_anterior === $nuevo_estado) {
        throw new Exception("El estado seleccionado es el mismo que el actual");
    }
    
    // Actualizar el estado
    $fecha_resolucion_sql = "";
    if ($nuevo_estado === 'resuelta' || $nuevo_estado === 'cerrada') {
        $fecha_resolucion_sql = ", fecha_resolucion = CURRENT_TIMESTAMP";
    }
    
    $sql_update = "UPDATE anomalias SET 
                    estado = ?, 
                    fecha_actualizacion = CURRENT_TIMESTAMP
                    $fecha_resolucion_sql
                   WHERE id = ?";
    
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        throw new Exception("Error al preparar consulta de actualización: " . $conn->error);
    }
    
    $stmt_update->bind_param("si", $nuevo_estado, $anomalia_id);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Error al actualizar el estado: " . $stmt_update->error);
    }
    
    $stmt_update->close();
    
    // Registrar en historial (si existe la tabla)
    $check_historial = $conn->query("SHOW TABLES LIKE 'anomalias_historial'");
    if ($check_historial && $check_historial->num_rows > 0) {
        $sql_historial = "INSERT INTO anomalias_historial 
                         (anomalia_id, campo_modificado, valor_anterior, valor_nuevo, usuario_modificador, comentario) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_historial = $conn->prepare($sql_historial);
        if ($stmt_historial) {
            $campo = "estado";
            $comentario = "Cambio de estado desde vista de seguimiento";
            $stmt_historial->bind_param("isssss", $anomalia_id, $campo, $estado_anterior, $nuevo_estado, $usuario_id, $comentario);
            $stmt_historial->execute();
            $stmt_historial->close();
        }
    }
    
    // Registrar en auditoría (si existe la tabla)
    $check_auditoria = $conn->query("SHOW TABLES LIKE 'anomalias_auditoria'");
    if ($check_auditoria && $check_auditoria->num_rows > 0) {
        $sql_auditoria = "INSERT INTO anomalias_auditoria 
                         (anomalia_id, usuario_id, accion, descripcion_accion, datos_anteriores, datos_nuevos, ip_address, user_agent) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_auditoria = $conn->prepare($sql_auditoria);
        if ($stmt_auditoria) {
            $accion = "cambiar_estado";
            $descripcion = "Estado cambiado de '$estado_anterior' a '$nuevo_estado'";
            $datos_anteriores = json_encode(['estado' => $estado_anterior]);
            $datos_nuevos = json_encode(['estado' => $nuevo_estado]);
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt_auditoria->bind_param("iissssss", $anomalia_id, $usuario_id, $accion, $descripcion, $datos_anteriores, $datos_nuevos, $ip_address, $user_agent);
            $stmt_auditoria->execute();
            $stmt_auditoria->close();
        }
    }
    
    // Enviar notificación si existe la tabla
    $check_notificaciones = $conn->query("SHOW TABLES LIKE 'anomalias_notificaciones'");
    if ($check_notificaciones && $check_notificaciones->num_rows > 0) {
        // Obtener responsable asignado para notificar
        $sql_responsable = "SELECT responsable_asignado FROM anomalias WHERE id = ?";
        $stmt_responsable = $conn->prepare($sql_responsable);
        if ($stmt_responsable) {
            $stmt_responsable->bind_param("i", $anomalia_id);
            $stmt_responsable->execute();
            $result_responsable = $stmt_responsable->get_result();
            
            if ($row_responsable = $result_responsable->fetch_assoc()) {
                $responsable_id = $row_responsable['responsable_asignado'];
                
                if ($responsable_id) {
                    $sql_notif = "INSERT INTO anomalias_notificaciones 
                                 (anomalia_id, usuario_id, tipo_notificacion, mensaje) 
                                 VALUES (?, ?, ?, ?)";
                    
                    $stmt_notif = $conn->prepare($sql_notif);
                    if ($stmt_notif) {
                        $tipo_notif = "actualizacion";
                        $mensaje_notif = "El estado de la anomalía '$titulo_anomalia' ha cambiado a '$nuevo_estado'";
                        $stmt_notif->bind_param("iiss", $anomalia_id, $responsable_id, $tipo_notif, $mensaje_notif);
                        $stmt_notif->execute();
                        $stmt_notif->close();
                    }
                }
            }
            $stmt_responsable->close();
        }
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Estado actualizado correctamente de '$estado_anterior' a '$nuevo_estado'",
        'estado_anterior' => $estado_anterior,
        'estado_nuevo' => $nuevo_estado
    ]);
    
} catch (Exception $e) {
    error_log("Error en cambiar_estado_anomalia.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>