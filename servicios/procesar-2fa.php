<?php
/**
 * Procesador de Verificación 2FA - Sistema ARCO
 * Valida el código de verificación de dos factores
 * 
 * @version 2.0
 * @since 2025-12-15
 */

session_start();
require_once "conexion.php";
require_once "two_factor_auth.php";

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: verificacion-2fa.php?error=" . urlencode("Petición no válida"));
    exit;
}

// Verificar sesión temporal
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: ../login.html?error=" . urlencode("Sesión expirada"));
    exit;
}

try {
    $userId = $_SESSION['temp_user_id'];
    $codigo = $_POST['codigo'] ?? '';
    
    if (empty($codigo) || strlen($codigo) !== 6) {
        throw new Exception("Por favor ingrese el código de verificación completo");
    }
    
    // Verificar código 2FA
    $tfa = new TwoFactorAuth();
    
    if (!$tfa->verifyCode($userId, $codigo)) {
        throw new Exception("Código incorrecto o expirado. Por favor intente nuevamente.");
    }
    
    // Código válido - marcar dispositivo como confiable
    $tfa->markDeviceAsTrusted($userId);
    
    // Completar login
    $usuario = $_SESSION['temp_user_data'];
    $recordarme = $_SESSION['temp_recordarme'] ?? false;
    
    $_SESSION['usuario_id'] = $usuario['id_usuarios'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['ultimo_acceso'] = date('Y-m-d H:i:s');
    
    // Limpiar datos temporales
    unset($_SESSION['temp_user_id'], $_SESSION['temp_user_data'], $_SESSION['temp_recordarme']);
    
    // Actualizar último acceso
    $conexion = ConectarDB();
    $stmt = $conexion->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuarios = ?");
    $stmt->bind_param("i", $usuario['id_usuarios']);
    $stmt->execute();
    
    // Registrar en auditoría
    $stmt = $conexion->prepare("
        INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
        VALUES (?, 'login_2fa', 'Inicio de sesión con 2FA exitoso', ?, ?, NOW())
    ");
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    $stmt->bind_param("iss", $usuario['id_usuarios'], $ipAddress, $userAgent);
    $stmt->execute();
    
    // Configurar cookie de recordar si está habilitado
    if ($recordarme) {
        $token = bin2hex(random_bytes(32));
        $expiracion = time() + (30 * 24 * 60 * 60); // 30 días
        
        setcookie('recordar_token', $token, $expiracion, '/', '', false, true);
        
        $stmt = $conexion->prepare("
            UPDATE usuarios 
            SET token_recordar = ?, token_recordar_expira = FROM_UNIXTIME(?)
            WHERE id_usuarios = ?
        ");
        $stmt->bind_param("sii", $token, $expiracion, $usuario['id_usuarios']);
        $stmt->execute();
    }
    
    // Redirigir al dashboard
    header("Location: ../vistas/dashboard.php");
    exit;
    
} catch (Exception $e) {
    error_log("Error en verificación 2FA: " . $e->getMessage());
    header("Location: verificacion-2fa.php?error=" . urlencode($e->getMessage()));
    exit;
}