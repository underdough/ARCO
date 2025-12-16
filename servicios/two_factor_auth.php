<?php
session_start();
require_once 'conexion.php';

class TwoFactorAuth {
    private $conn;
    
    public function __construct() {
        $this->conn = ConectarDB();
    }
    
    /**
     * Genera un código de verificación de 6 dígitos
     */
    public function generateVerificationCode() {
        return sprintf("%06d", mt_rand(100000, 999999));
    }
    
    /**
     * Guarda el código de verificación en la base de datos
     */
    public function saveVerificationCode($userId, $code, $type = 'email') {
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Eliminar códigos anteriores del usuario
        $stmt = $this->conn->prepare("DELETE FROM verification_codes WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Insertar nuevo código
        $stmt = $this->conn->prepare("INSERT INTO verification_codes (user_id, code, type, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $userId, $code, $type, $expiry);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica el código de verificación
     */
    public function verifyCode($userId, $code) {
        $stmt = $this->conn->prepare("SELECT * FROM verification_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()");
        $stmt->bind_param("is", $userId, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Eliminar el código usado
            $stmt = $this->conn->prepare("DELETE FROM verification_codes WHERE user_id = ? AND code = ?");
            $stmt->bind_param("is", $userId, $code);
            $stmt->execute();
            return true;
        }
        
        return false;
    }
    
    /**
     * Envía código por email
     */
    public function sendEmailCode($email, $code, $userName) {
        $subject = "ARCO - Código de Verificación";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8fafc; }
                .code { font-size: 24px; font-weight: bold; color: #2563eb; text-align: center; padding: 20px; background: white; border-radius: 8px; margin: 20px 0; }
                .footer { text-align: center; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Sistema ARCO</h1>
                </div>
                <div class='content'>
                    <h2>Hola, {$userName}</h2>
                    <p>Has solicitado acceso al sistema ARCO. Tu código de verificación es:</p>
                    <div class='code'>{$code}</div>
                    <p>Este código expira en 10 minutos.</p>
                    <p>Si no solicitaste este código, ignora este mensaje.</p>
                </div>
                <div class='footer'>
                    <p>Sistema ARCO - Gestión de Inventarios</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Sistema ARCO <noreply@arco.com>" . "\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
    
    /**
     * Simula envío de SMS (en producción usar servicio real como Twilio)
     */
    public function sendSMSCode($phone, $code) {
        // En un entorno real, aquí integrarías con un servicio SMS como Twilio
        // Por ahora, solo registramos en log para desarrollo
        error_log("SMS Code for {$phone}: {$code}");
        
        // Simular éxito para desarrollo
        return true;
    }
    
    /**
     * Obtiene las preferencias de 2FA del usuario
     */
    public function getUserTwoFactorPreferences($userId) {
        $stmt = $this->conn->prepare("SELECT two_factor_enabled, two_factor_method FROM usuarios WHERE id_usuarios = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return ['two_factor_enabled' => 0, 'two_factor_method' => 'email'];
    }
    
    /**
     * Actualiza las preferencias de 2FA del usuario
     */
    public function updateTwoFactorPreferences($userId, $enabled, $method) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET two_factor_enabled = ?, two_factor_method = ? WHERE id_usuarios = ?");
        $stmt->bind_param("isi", $enabled, $method, $userId);
        return $stmt->execute();
    }
}

// Manejo de peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    $tfa = new TwoFactorAuth();
    
    switch ($action) {
        case 'send_code':
            if (!isset($_SESSION['temp_user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
                exit;
            }
            
            $userId = $_SESSION['temp_user_id'];
            $method = $_POST['method'] ?? 'email';
            
            // Obtener datos del usuario
            $conn = ConectarDB();
            $stmt = $conn->prepare("SELECT nombre, correo, num_telefono FROM usuarios WHERE id_usuarios = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
                exit;
            }
            
            $code = $tfa->generateVerificationCode();
            
            if ($tfa->saveVerificationCode($userId, $code, $method)) {
                if ($method === 'email') {
                    $sent = $tfa->sendEmailCode($user['correo'], $code, $user['nombre']);
                } else {
                    $sent = $tfa->sendSMSCode($user['num_telefono'], $code);
                }
                
                if ($sent) {
                    echo json_encode(['success' => true, 'message' => 'Código enviado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al enviar el código']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al generar el código']);
            }
            break;
            
        case 'verify_code':
            if (!isset($_SESSION['temp_user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
                exit;
            }
            
            $userId = $_SESSION['temp_user_id'];
            $code = $_POST['code'] ?? '';
            
            if ($tfa->verifyCode($userId, $code)) {
                // Código correcto, completar login
                $conn = ConectarDB();
                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuarios = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                
                $_SESSION['usuario_id'] = $user['id_usuarios'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellido'] = $user['apellido'];
                $_SESSION['rol'] = $user['rol'];
                
                // Limpiar datos temporales
                unset($_SESSION['temp_user_id']);
                
                echo json_encode(['success' => true, 'redirect' => '../vistas/dashboard.php']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Código incorrecto o expirado']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>