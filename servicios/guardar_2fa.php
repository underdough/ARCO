<?php
/**
 * Guardar Preferencias 2FA - Sistema ARCO
 * Actualiza las preferencias de autenticación de dos factores del usuario
 * 
 * @version 2.0
 * @since 2025-12-15
 */

session_start();
require_once "conexion.php";

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=" . urlencode("Sesión expirada"));
    exit;
}

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: ../vistas/configuracion.php?error=" . urlencode("Petición no válida"));
    exit;
}

try {
    $usuarioId = $_SESSION['usuario_id'];
    $enable2FA = isset($_POST['enable2FA']) ? 1 : 0;
    $method2FA = $_POST['method2FA'] ?? 'email';
    
    // Validar método
    if (!in_array($method2FA, ['email', 'sms'])) {
        throw new Exception("Método de verificación no válido");
    }
    
    $conexion = ConectarDB();
    
    // Actualizar preferencias de 2FA
    $stmt = $conexion->prepare("
        UPDATE usuarios 
        SET two_factor_enabled = ?, two_factor_method = ?
        WHERE id_usuarios = ?
    ");
    $stmt->bind_param("isi", $enable2FA, $method2FA, $usuarioId);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar preferencias de 2FA");
    }
    
    // Registrar en auditoría
    $accion = $enable2FA ? 'habilitar_2fa' : 'deshabilitar_2fa';
    $descripcion = $enable2FA ? 
        "2FA habilitado con método: $method2FA" : 
        "2FA deshabilitado";
    
    $stmt = $conexion->prepare("
        INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    $stmt->bind_param("issss", $usuarioId, $accion, $descripcion, $ipAddress, $userAgent);
    $stmt->execute();
    
    // Redirigir con mensaje de éxito
    $mensaje = $enable2FA ? 
        "Autenticación de dos factores habilitada exitosamente" : 
        "Autenticación de dos factores deshabilitada";
    
    header("Location: ../vistas/configuracion.php?success=" . urlencode($mensaje));
    exit;
    
} catch (Exception $e) {
    error_log("Error al guardar preferencias 2FA: " . $e->getMessage());
    header("Location: ../vistas/configuracion.php?error=" . urlencode($e->getMessage()));
    exit;
}