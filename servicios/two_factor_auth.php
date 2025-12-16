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
     * Envía código por email usando PHPMailer
     */
    public function sendEmailCode($email, $code, $userName) {
        require_once __DIR__ . '/email_sender.php';
        
        $asunto = "ARCO - Código de Verificación 2FA";
        $mensaje = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f3f4f6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 30px; background: white; border-radius: 0 0 10px 10px; }
                .code-box { background: #f8fafc; border: 2px dashed #2563eb; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: center; }
                .code { font-size: 32px; font-weight: bold; color: #2563eb; letter-spacing: 8px; font-family: 'Courier New', monospace; }
                .info { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .footer { text-align: center; color: #6b7280; font-size: 14px; margin-top: 20px; padding: 20px; }
                .icon { font-size: 48px; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='icon'>🔐</div>
                    <h1 style='margin: 0;'>Verificación de Dos Factores</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Sistema ARCO</p>
                </div>
                <div class='content'>
                    <h2 style='color: #1f2937;'>Hola, {$userName}</h2>
                    <p style='color: #4b5563; line-height: 1.6;'>
                        Has iniciado sesión en el Sistema ARCO. Para completar el acceso, 
                        ingresa el siguiente código de verificación:
                    </p>
                    
                    <div class='code-box'>
                        <p style='margin: 0 0 10px 0; color: #6b7280; font-size: 14px;'>Tu código de verificación es:</p>
                        <div class='code'>{$code}</div>
                    </div>
                    
                    <div class='info'>
                        <p style='margin: 0; color: #92400e;'>
                            <strong>⏰ Importante:</strong> Este código expira en <strong>10 minutos</strong>.
                        </p>
                    </div>
                    
                    <p style='color: #6b7280; font-size: 14px; line-height: 1.6;'>
                        Si no solicitaste este código, ignora este mensaje y tu cuenta permanecerá segura.
                        Nadie podrá acceder sin el código de verificación.
                    </p>
                    
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;'>
                    
                    <p style='color: #9ca3af; font-size: 12px; margin: 0;'>
                        <strong>Consejos de seguridad:</strong><br>
                        • No compartas este código con nadie<br>
                        • ARCO nunca te pedirá este código por teléfono o email<br>
                        • Si recibes este código sin haberlo solicitado, cambia tu contraseña inmediatamente
                    </p>
                </div>
                <div class='footer'>
                    <p style='margin: 0 0 10px 0;'><strong>Sistema ARCO</strong></p>
                    <p style='margin: 0;'>Gestión de Inventarios Profesional</p>
                    <p style='margin: 10px 0 0 0; font-size: 12px;'>
                        Este es un mensaje automático, no respondas a este correo.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        try {
            $sender = new EmailSender();
            $resultado = $sender->enviar($email, $userName, $asunto, $mensaje);
            
            // Log del resultado
            error_log("2FA Email - Destinatario: $email, Código: $code, Resultado: " . ($resultado['success'] ? 'Éxito' : 'Fallo'));
            
            return $resultado['success'];
        } catch (Exception $e) {
            error_log("Error al enviar email 2FA: " . $e->getMessage());
            return false;
        }
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