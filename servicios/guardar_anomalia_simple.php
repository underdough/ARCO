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
    // Incluir configuración de base de datos
    require_once 'conexion.php';
    
    // Crear conexión
    $conn = ConectarDB();
    
    // Configurar charset
    $conn->set_charset("utf8mb4");
    
    // Obtener datos del formulario
    $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $prioridad = $_POST['prioridad'] ?? '';
    $categoria = trim($_POST['categoria'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $impacto = $_POST['impacto'] ?? 'medio';
    $materiales_afectados = trim($_POST['materiales_afectados'] ?? '');
    $responsable_asignado = isset($_POST['responsable_asignado']) && !empty($_POST['responsable_asignado']) ? intval($_POST['responsable_asignado']) : null;
    $usuario_id = $_SESSION['usuario_id'];
    
    // Validaciones
    if (empty($titulo)) {
        throw new Exception("El título es obligatorio");
    }
    
    if (strlen($titulo) > 100) {
        throw new Exception("El título no puede exceder 100 caracteres");
    }
    
    if (empty($descripcion)) {
        throw new Exception("La descripción es obligatoria");
    }
    
    if (empty($prioridad) || !in_array($prioridad, ['baja', 'media', 'urgente'])) {
        throw new Exception("La prioridad es obligatoria y debe ser válida");
    }
    
    if (!empty($categoria) && strlen($categoria) > 50) {
        throw new Exception("La categoría no puede exceder 50 caracteres");
    }
    
    if (!empty($ubicacion) && strlen($ubicacion) > 100) {
        throw new Exception("La ubicación no puede exceder 100 caracteres");
    }
    
    if (!in_array($impacto, ['bajo', 'medio', 'alto', 'critico'])) {
        $impacto = 'medio';
    }
    
    // Preparar consulta según si es creación o edición
    if ($id) {
        // Obtener datos anteriores para auditoría
        $audit_sql = "SELECT * FROM anomalias WHERE id = ?";
        $audit_stmt = $conn->prepare($audit_sql);
        $audit_stmt->bind_param("i", $id);
        $audit_stmt->execute();
        $datos_anteriores = $audit_stmt->get_result()->fetch_assoc();
        $audit_stmt->close();
        
        // Editar anomalía existente
        $sql = "UPDATE anomalias SET 
                    titulo = ?, 
                    descripcion = ?, 
                    prioridad = ?, 
                    categoria = ?, 
                    ubicacion = ?,
                    impacto = ?,
                    materiales_afectados = ?,
                    responsable_asignado = ?,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de actualización: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssii", $titulo, $descripcion, $prioridad, $categoria, $ubicacion, $impacto, $materiales_afectados, $responsable_asignado, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar la anomalía: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Registrar auditoría
        registrarAuditoria($conn, $id, $usuario_id, 'editar', 'Anomalía editada desde interfaz web', $datos_anteriores, [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
            'categoria' => $categoria,
            'ubicacion' => $ubicacion,
            'impacto' => $impacto,
            'materiales_afectados' => $materiales_afectados,
            'responsable_asignado' => $responsable_asignado
        ]);
        
        // Enviar notificación si se asignó responsable
        if ($responsable_asignado && $responsable_asignado != $datos_anteriores['responsable_asignado']) {
            enviarNotificacion($conn, $id, $responsable_asignado, 'asignacion', "Se te ha asignado la anomalía: $titulo");
        }
        
        $mensaje = "Anomalía actualizada correctamente";
        $codigo_seguimiento = $datos_anteriores['codigo_seguimiento'];
        
    } else {
        // Crear nueva anomalía
        $sql = "INSERT INTO anomalias (titulo, descripcion, prioridad, categoria, ubicacion, impacto, materiales_afectados, responsable_asignado, usuario_creador) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar consulta de inserción: " . $conn->error);
        }
        
        $stmt->bind_param("sssssssii", $titulo, $descripcion, $prioridad, $categoria, $ubicacion, $impacto, $materiales_afectados, $responsable_asignado, $usuario_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear la anomalía: " . $stmt->error);
        }
        
        $id = $conn->insert_id;
        $stmt->close();
        
        // Obtener código de seguimiento generado
        $codigo_sql = "SELECT codigo_seguimiento FROM anomalias WHERE id = ?";
        $codigo_stmt = $conn->prepare($codigo_sql);
        $codigo_stmt->bind_param("i", $id);
        $codigo_stmt->execute();
        $codigo_result = $codigo_stmt->get_result();
        $codigo_seguimiento = $codigo_result->fetch_assoc()['codigo_seguimiento'];
        $codigo_stmt->close();
        
        // Registrar auditoría
        registrarAuditoria($conn, $id, $usuario_id, 'crear', 'Anomalía creada desde interfaz web', null, [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
            'categoria' => $categoria,
            'ubicacion' => $ubicacion,
            'impacto' => $impacto,
            'materiales_afectados' => $materiales_afectados,
            'responsable_asignado' => $responsable_asignado
        ]);
        
        // Enviar notificación al responsable si se asignó
        if ($responsable_asignado) {
            enviarNotificacion($conn, $id, $responsable_asignado, 'asignacion', "Se te ha asignado la nueva anomalía: $titulo");
        }
        
        // Notificar a administradores sobre nueva anomalía urgente
        if ($prioridad === 'urgente') {
            notificarAdministradores($conn, $id, $titulo, $prioridad);
        }
        
        $mensaje = "Anomalía creada correctamente";
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'anomalia_id' => $id,
        'codigo_seguimiento' => $codigo_seguimiento
    ]);
    
} catch (Exception $e) {
    error_log("Error en guardar_anomalia_simple.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Función para registrar auditoría
function registrarAuditoria($conn, $anomalia_id, $usuario_id, $accion, $descripcion, $datos_anteriores, $datos_nuevos) {
    // Verificar si existe la tabla de auditoría
    $check_table = $conn->query("SHOW TABLES LIKE 'anomalias_auditoria'");
    if ($check_table && $check_table->num_rows > 0) {
        $sql = "INSERT INTO anomalias_auditoria (anomalia_id, usuario_id, accion, descripcion_accion, datos_anteriores, datos_nuevos, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $datos_anteriores_json = $datos_anteriores ? json_encode($datos_anteriores) : null;
            $datos_nuevos_json = json_encode($datos_nuevos);
            
            $stmt->bind_param("iissssss", $anomalia_id, $usuario_id, $accion, $descripcion, $datos_anteriores_json, $datos_nuevos_json, $ip_address, $user_agent);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Función para enviar notificaciones
function enviarNotificacion($conn, $anomalia_id, $usuario_id, $tipo, $mensaje) {
    // Verificar si existe la tabla de notificaciones
    $check_table = $conn->query("SHOW TABLES LIKE 'anomalias_notificaciones'");
    if ($check_table && $check_table->num_rows > 0) {
        $sql = "INSERT INTO anomalias_notificaciones (anomalia_id, usuario_id, tipo_notificacion, mensaje) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iiss", $anomalia_id, $usuario_id, $tipo, $mensaje);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Función para notificar administradores
function notificarAdministradores($conn, $anomalia_id, $titulo, $prioridad) {
    // Buscar usuarios administradores (asumiendo que tienen rol 'admin' o similar)
    $sql = "SELECT id_usuarios FROM usuarios WHERE rol IN ('admin', 'administrador', 'supervisor') AND estado = 'activo'";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            enviarNotificacion($conn, $anomalia_id, $row['id_usuarios'], 'creacion', "Nueva anomalía URGENTE registrada: $titulo");
        }
    }
}
?>