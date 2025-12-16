<?php
/**
 * Procesar Restablecimiento de Contraseña - Sistema ARCO
 */

session_start();
require_once "conexion.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $token = $_POST['token'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    if (empty($token) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        throw new Exception('Todos los campos son requeridos');
    }
    
    if ($nueva_contrasena !== $confirmar_contrasena) {
        throw new Exception('Las contraseñas no coinciden');
    }
    
    if (strlen($nueva_contrasena) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }
    
    $conexion = ConectarDB();
    
    if (!$conexion) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Verificar token
    $stmt = $conexion->prepare("
        SELECT usuario_id 
        FROM password_resets 
        WHERE token = ? AND expira_en > NOW() AND usado = 0
    ");
    
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conexion->error);
    }
    
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('El enlace de recuperación es inválido o ha expirado');
    }
    
    $datos = $result->fetch_assoc();
    $usuario_id = $datos['usuario_id'];
    
    // Hashear nueva contraseña
    $password_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
    
    // Actualizar contraseña
    $stmt = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuarios = ?");
    
    if (!$stmt) {
        throw new Exception('Error al preparar actualización: ' . $conexion->error);
    }
    
    $stmt->bind_param("si", $password_hash, $usuario_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar contraseña: ' . $stmt->error);
    }
    
    // Marcar token como usado
    $stmt = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE token = ?");
    
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
    }
    
    // Registrar en auditoría si la tabla existe
    $checkAuditoria = $conexion->query("SHOW TABLES LIKE 'auditoria'");
    if ($checkAuditoria && $checkAuditoria->num_rows > 0) {
        $stmt = $conexion->prepare("
            INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
            VALUES (?, 'restablecer_contrasena', 'Contraseña restablecida exitosamente', ?, ?, NOW())
        ");
        
        if ($stmt) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
            $stmt->bind_param("iss", $usuario_id, $ipAddress, $userAgent);
            $stmt->execute();
        }
    }
    
    error_log("Contraseña restablecida exitosamente para usuario ID: " . $usuario_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Contraseña restablecida exitosamente. Redirigiendo al login...'
    ]);
    
} catch (Exception $e) {
    error_log("Error al restablecer contraseña: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}