<?php
/**
 * Reenvío de Código 2FA - Sistema ARCO
 * Genera y envía un nuevo código de verificación
 * 
 * @version 2.0
 * @since 2025-12-15
 */

session_start();
require_once "conexion.php";
require_once "two_factor_auth.php";

header('Content-Type: application/json');

try {
    // Verificar sesión temporal
    if (!isset($_SESSION['temp_user_id'])) {
        throw new Exception("Sesión expirada");
    }
    
    $userId = $_SESSION['temp_user_id'];
    $usuario = $_SESSION['temp_user_data'];
    $metodo = $usuario['two_factor_method'] ?? 'email';
    
    // Generar nuevo código
    $tfa = new TwoFactorAuth();
    $codigo = $tfa->generateVerificationCode();
    
    // Guardar código
    if (!$tfa->saveVerificationCode($userId, $codigo, $metodo)) {
        throw new Exception("Error al generar código de verificación");
    }
    
    // Enviar código según el método
    $enviado = false;
    if ($metodo === 'email') {
        $enviado = $tfa->sendEmailCode($usuario['correo'], $codigo, $usuario['nombre']);
    } else {
        $enviado = $tfa->sendSMSCode($usuario['num_telefono'], $codigo);
    }
    
    if (!$enviado) {
        throw new Exception("Error al enviar código de verificación");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Código reenviado exitosamente'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}