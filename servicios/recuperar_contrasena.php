<?php
/**
 * Servicio de Recuperación de Contraseña - Sistema ARCO
 * Genera token y envía email para restablecer contraseña
 */

session_start();
require_once "conexion.php";
require_once "email_sender.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $correo = filter_var($_POST['correo'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$correo) {
        throw new Exception('Correo electrónico inválido');
    }
    
    $conexion = ConectarDB();
    
    if (!$conexion) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Buscar usuario por correo
    $stmt = $conexion->prepare("SELECT id_usuarios, nombre, apellido FROM usuarios WHERE correo = ?");
    
    if (!$stmt) {
        throw new Exception('Error al preparar consulta: ' . $conexion->error);
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Por seguridad, no revelar si el correo existe o no
        echo json_encode([
            'success' => true,
            'message' => 'Si el correo existe, recibirás instrucciones para restablecer tu contraseña'
        ]);
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    // Generar token único
    $token = bin2hex(random_bytes(32));
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Verificar si la tabla existe, si no, crearla
    $checkTable = $conexion->query("SHOW TABLES LIKE 'password_resets'");
    if ($checkTable->num_rows === 0) {
        $conexion->query("
            CREATE TABLE password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expira_en DATETIME NOT NULL,
                usado TINYINT(1) DEFAULT 0,
                creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_token (token),
                INDEX idx_expira (expira_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
    
    // Guardar token en base de datos
    $stmt = $conexion->prepare("
        INSERT INTO password_resets (usuario_id, token, expira_en, creado_en) 
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE token = ?, expira_en = ?, creado_en = NOW()
    ");
    
    if (!$stmt) {
        throw new Exception('Error al preparar inserción: ' . $conexion->error);
    }
    
    $stmt->bind_param("issss", $usuario['id_usuarios'], $token, $expiracion, $token, $expiracion);
    $stmt->execute();
    
    // Preparar email
    $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
    
    // Detectar la ruta correcta
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $resetLink = $protocol . "://" . $host . "/ARCO/vistas/restablecer-contra.php?token=" . $token;
    
    // Registrar en auditoría si la tabla existe
    $checkAuditoria = $conexion->query("SHOW TABLES LIKE 'auditoria'");
    if ($checkAuditoria && $checkAuditoria->num_rows > 0) {
        $stmtAudit = $conexion->prepare("
            INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
            VALUES (?, 'solicitar_recuperacion', 'Solicitud de recuperación de contraseña', ?, ?, NOW())
        ");
        if ($stmtAudit) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
            $stmtAudit->bind_param("iss", $usuario['id_usuarios'], $ipAddress, $userAgent);
            $stmtAudit->execute();
        }
    }
    
    // Preparar email HTML
    $asunto = "ARCO - Restablecer Contraseña";
    $mensaje = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f8fafc; }
            .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
            .footer { text-align: center; color: #6b7280; font-size: 14px; margin-top: 20px; }
            .link-box { word-break: break-all; background: #f3f4f6; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 Restablecer Contraseña</h1>
            </div>
            <div class='content'>
                <h2>Hola, {$nombreCompleto}</h2>
                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en el Sistema ARCO.</p>
                <p>Para restablecer tu contraseña, haz clic en el siguiente botón:</p>
                <p style='text-align: center;'>
                    <a href='{$resetLink}' class='button'>Restablecer Contraseña</a>
                </p>
                <p>O copia y pega este enlace en tu navegador:</p>
                <div class='link-box'>{$resetLink}</div>
                <p><strong>⏰ Este enlace expirará en 1 hora.</strong></p>
                <p style='color: #6b7280; font-size: 0.9rem;'>Si no solicitaste restablecer tu contraseña, puedes ignorar este correo de forma segura.</p>
            </div>
            <div class='footer'>
                <p><strong>Sistema ARCO</strong> - Gestión de Inventarios</p>
                <p>Este es un mensaje automático, no respondas a este correo.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Enviar email usando el nuevo sistema
    $emailSender = new EmailSender();
    $resultadoEmail = $emailSender->enviar($correo, $nombreCompleto, $asunto, $mensaje);
    
    // Logs detallados
    error_log("=== RECUPERACIÓN DE CONTRASEÑA ===");
    error_log("Usuario: " . $nombreCompleto . " (" . $correo . ")");
    error_log("Link de recuperación: " . $resetLink);
    error_log("Token: " . $token);
    error_log("Expira: " . $expiracion);
    error_log("Método de envío: " . ($resultadoEmail['metodo'] ?? 'desconocido'));
    error_log("Email enviado: " . ($resultadoEmail['success'] ? 'SÍ' : 'NO'));
    if (!$resultadoEmail['success'] && isset($resultadoEmail['error'])) {
        error_log("Error: " . $resultadoEmail['error']);
    }
    error_log("===================================");
    
    // Preparar respuesta
    $respuesta = [
        'success' => true,
        'message' => 'Se ha enviado un correo con instrucciones para restablecer tu contraseña',
        'email_enviado' => $resultadoEmail['success'],
        'metodo' => $resultadoEmail['metodo'] ?? 'desconocido'
    ];
    
    // En desarrollo o si el email falló, incluir el link
    if (ConfigEmail::esDesarrollo() || !$resultadoEmail['success']) {
        $respuesta['debug'] = [
            'link' => $resetLink,
            'email_enviado' => $resultadoEmail['success'],
            'metodo' => $resultadoEmail['metodo'] ?? 'desconocido',
            'nota' => !$resultadoEmail['success'] ? 
                'El email no pudo enviarse. Usa el link directo.' : 
                'Modo desarrollo: Link disponible para pruebas'
        ];
        
        if (!$resultadoEmail['success'] && isset($resultadoEmail['error'])) {
            $respuesta['debug']['error'] = $resultadoEmail['error'];
        }
    }
    
    echo json_encode($respuesta);
    
} catch (Exception $e) {
    error_log("Error en recuperación de contraseña: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}