<?php
/**
 * Manejador de Envío de Emails - Sistema ARCO
 * Usa exclusivamente PHPMailer con SMTP
 */

require_once __DIR__ . '/config_email.php';

class EmailSender {
    
    private $config;
    private $phpMailerDisponible = false;
    
    public function __construct() {
        $this->config = ConfigEmail::getConfig();
        
        // Verificar si PHPMailer está disponible
        $phpMailerPath = __DIR__ . '/PHPMailer/PHPMailer.php';
        $vendorPath = __DIR__ . '/../vendor/autoload.php';
        
        // Intentar cargar desde vendor (Composer)
        if (file_exists($vendorPath)) {
            require_once $vendorPath;
            $this->phpMailerDisponible = true;
        }
        // Intentar cargar desde instalación manual
        elseif (file_exists($phpMailerPath)) {
            require_once $phpMailerPath;
            require_once __DIR__ . '/PHPMailer/SMTP.php';
            require_once __DIR__ . '/PHPMailer/Exception.php';
            $this->phpMailerDisponible = true;
        }
    }
    
    /**
     * Enviar email usando PHPMailer
     * 
     * @param string $destinatario Email del destinatario
     * @param string $nombreDestinatario Nombre del destinatario
     * @param string $asunto Asunto del email
     * @param string $mensajeHTML Contenido HTML del email
     * @param string $mensajeTexto Contenido en texto plano (opcional)
     * @return array Resultado del envío
     */
    public function enviar($destinatario, $nombreDestinatario, $asunto, $mensajeHTML, $mensajeTexto = '') {
        try {
            // Validar email
            if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email de destinatario inválido');
            }
            
            // Verificar que PHPMailer esté disponible
            if (!$this->phpMailerDisponible) {
                throw new Exception('PHPMailer no está instalado. Ejecuta: composer require phpmailer/phpmailer');
            }
            
            // Enviar con PHPMailer
            return $this->enviarConPHPMailer($destinatario, $nombreDestinatario, $asunto, $mensajeHTML, $mensajeTexto);
            
        } catch (Exception $e) {
            error_log("Error al enviar email: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'metodo' => 'phpmailer'
            ];
        }
    }
    
    /**
     * Enviar con PHPMailer (SMTP)
     */
    private function enviarConPHPMailer($destinatario, $nombreDestinatario, $asunto, $mensajeHTML, $mensajeTexto) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_secure'];
            $mail->Port = $this->config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            
            // Configuración de debug (solo en desarrollo)
            if (ConfigEmail::esDesarrollo()) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer: $str");
                };
            }
            
            // Remitente
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            
            // Destinatario
            $mail->addAddress($destinatario, $nombreDestinatario);
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $mensajeHTML;
            $mail->AltBody = $mensajeTexto ?: strip_tags($mensajeHTML);
            
            // Enviar
            $mail->send();
            
            error_log("Email enviado exitosamente con PHPMailer a: $destinatario");
            
            return [
                'success' => true,
                'metodo' => 'phpmailer',
                'mensaje' => 'Email enviado exitosamente con PHPMailer'
            ];
            
        } catch (Exception $e) {
            error_log("Error PHPMailer: " . $e->getMessage());
            
            return [
                'success' => false,
                'metodo' => 'phpmailer',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar configuración de email
     */
    public function verificarConfiguracion() {
        $resultado = [
            'phpmailer_disponible' => $this->phpMailerDisponible,
            'modo' => $this->config['modo'],
            'smtp_configurado' => !empty($this->config['smtp_username']) && $this->config['smtp_username'] !== 'tu_email@gmail.com',
            'metodo' => 'PHPMailer (SMTP)',
            'config' => [
                'host' => $this->config['smtp_host'],
                'port' => $this->config['smtp_port'],
                'secure' => $this->config['smtp_secure'],
                'from' => $this->config['from_email'],
                'username' => $this->config['smtp_username']
            ]
        ];
        
        return $resultado;
    }
    
    /**
     * Enviar email de prueba
     */
    public function enviarPrueba($destinatario) {
        $asunto = "Prueba de Email - Sistema ARCO";
        $mensaje = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #f8fafc; padding: 30px; border-radius: 10px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; border-radius: 8px; }
                .content { background: white; padding: 20px; margin-top: 20px; border-radius: 8px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Email de Prueba</h1>
                </div>
                <div class='content'>
                    <h2>¡Configuración Exitosa!</h2>
                    <p>Si estás leyendo este mensaje, significa que el sistema de envío de emails está funcionando correctamente.</p>
                    <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
                    <p><strong>Sistema:</strong> ARCO - Gestión de Inventarios</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return $this->enviar($destinatario, 'Usuario de Prueba', $asunto, $mensaje);
    }
}

/**
 * Función helper para enviar emails fácilmente
 */
function enviarEmail($destinatario, $nombreDestinatario, $asunto, $mensajeHTML) {
    $sender = new EmailSender();
    return $sender->enviar($destinatario, $nombreDestinatario, $asunto, $mensajeHTML);
}