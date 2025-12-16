<?php
/**
 * Prueba de Configuración de Email - Sistema ARCO
 * Usar para verificar que el envío de emails funciona correctamente
 */

session_start();
require_once "email_sender.php";

// Solo permitir en desarrollo o para administradores
$permitido = ConfigEmail::esDesarrollo() || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador_sistema');

if (!$permitido) {
    die('Acceso denegado');
}

$resultado = null;
$verificacion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_prueba'])) {
    $emailPrueba = filter_var($_POST['email_prueba'], FILTER_VALIDATE_EMAIL);
    
    if ($emailPrueba) {
        $sender = new EmailSender();
        $resultado = $sender->enviarPrueba($emailPrueba);
    } else {
        $resultado = ['success' => false, 'error' => 'Email inválido'];
    }
}

$sender = new EmailSender();
$verificacion = $sender->verificarConfiguracion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Email - ARCO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }
        .section.success { border-color: #10b981; background: #f0fdf4; }
        .section.warning { border-color: #f59e0b; background: #fffbeb; }
        .section.error { border-color: #ef4444; background: #fef2f2; }
        .section h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
        }
        .info-value {
            color: #1f2937;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        .btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .badge.success { background: #d1fae5; color: #065f46; }
        .badge.error { background: #fee2e2; color: #991b1b; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .code {
            background: #1f2937;
            color: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .alert.success {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }
        .alert.error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope"></i> Prueba de Configuración de Email</h1>
            <p>Sistema ARCO - Verificación de Envío de Correos</p>
        </div>
        
        <div class="content">
            <!-- Estado de la Configuración -->
            <div class="section <?= $verificacion['phpmailer_disponible'] ? 'success' : 'warning' ?>">
                <h3>
                    <i class="fas fa-cog"></i>
                    Estado de la Configuración
                </h3>
                
                <div class="info-grid">
                    <div class="info-label">Modo:</div>
                    <div class="info-value">
                        <span class="badge <?= $verificacion['modo'] === 'produccion' ? 'success' : 'warning' ?>">
                            <?= strtoupper($verificacion['modo']) ?>
                        </span>
                    </div>
                    
                    <div class="info-label">PHPMailer:</div>
                    <div class="info-value">
                        <span class="badge <?= $verificacion['phpmailer_disponible'] ? 'success' : 'error' ?>">
                            <?= $verificacion['phpmailer_disponible'] ? '✅ Instalado' : '❌ No instalado' ?>
                        </span>
                    </div>
                    
                    <div class="info-label">SMTP Configurado:</div>
                    <div class="info-value">
                        <span class="badge <?= $verificacion['smtp_configurado'] ? 'success' : 'warning' ?>">
                            <?= $verificacion['smtp_configurado'] ? '✅ Sí' : '⚠️ No' ?>
                        </span>
                    </div>
                    
                    <div class="info-label">Método de Envío:</div>
                    <div class="info-value"><strong><?= $verificacion['metodo'] ?></strong></div>
                    
                    <div class="info-label">Servidor SMTP:</div>
                    <div class="info-value"><?= $verificacion['config']['host'] ?>:<?= $verificacion['config']['port'] ?></div>
                    
                    <div class="info-label">Seguridad:</div>
                    <div class="info-value"><?= strtoupper($verificacion['config']['secure']) ?></div>
                    
                    <div class="info-label">Usuario SMTP:</div>
                    <div class="info-value"><?= $verificacion['config']['username'] ?></div>
                    
                    <div class="info-label">Remitente:</div>
                    <div class="info-value"><?= $verificacion['config']['from'] ?></div>
                </div>
                
                <?php if (!$verificacion['phpmailer_disponible']): ?>
                    <div class="alert error" style="margin-top: 15px;">
                        <h4><i class="fas fa-exclamation-triangle"></i> PHPMailer No Instalado</h4>
                        <p>El sistema requiere PHPMailer para enviar emails. Por favor instálalo:</p>
                        <div class="code">composer require phpmailer/phpmailer</div>
                        <p>O ejecuta el script de instalación:</p>
                        <div class="code">instalar_phpmailer.bat</div>
                    </div>
                <?php endif; ?>
                
                <?php if (!$verificacion['smtp_configurado']): ?>
                    <div class="alert error" style="margin-top: 15px;">
                        <h4><i class="fas fa-exclamation-triangle"></i> SMTP No Configurado</h4>
                        <p>Por favor configura tus credenciales SMTP en:</p>
                        <div class="code">servicios/config_email.php</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Resultado de la Prueba -->
            <?php if ($resultado): ?>
                <div class="alert <?= $resultado['success'] ? 'success' : 'error' ?>">
                    <h4>
                        <i class="fas fa-<?= $resultado['success'] ? 'check-circle' : 'times-circle' ?>"></i>
                        <?= $resultado['success'] ? 'Email Enviado Exitosamente' : 'Error al Enviar Email' ?>
                    </h4>
                    <p><strong>Método usado:</strong> <?= $resultado['metodo'] ?? 'desconocido' ?></p>
                    <?php if (isset($resultado['error'])): ?>
                        <p><strong>Error:</strong> <?= htmlspecialchars($resultado['error']) ?></p>
                    <?php endif; ?>
                    <?php if (isset($resultado['mensaje'])): ?>
                        <p><?= htmlspecialchars($resultado['mensaje']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Prueba -->
            <div class="section">
                <h3>
                    <i class="fas fa-paper-plane"></i>
                    Enviar Email de Prueba
                </h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email_prueba">
                            <i class="fas fa-envelope"></i> Email de Destino
                        </label>
                        <input 
                            type="email" 
                            id="email_prueba" 
                            name="email_prueba" 
                            placeholder="tu@correo.com"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-paper-plane"></i> Enviar Email de Prueba
                    </button>
                </form>
            </div>
            
            <!-- Instrucciones de Configuración -->
            <div class="section">
                <h3>
                    <i class="fas fa-book"></i>
                    Instrucciones de Configuración
                </h3>
                
                <p><strong>Para configurar el envío de emails en producción:</strong></p>
                
                <ol style="margin: 15px 0; padding-left: 25px; line-height: 1.8;">
                    <li>Editar <code>servicios/config_email.php</code></li>
                    <li>Cambiar <code>MODO</code> a <code>'produccion'</code></li>
                    <li>Configurar credenciales SMTP</li>
                    <li>Probar el envío con este formulario</li>
                </ol>
                
                <p><strong>Ejemplo de configuración para Gmail:</strong></p>
                <div class="code">
const MODO = 'produccion';
const SMTP_PROVIDER = 'gmail';
const SMTP_USERNAME = 'tu_email@gmail.com';
const SMTP_PASSWORD = 'tu_contraseña_app'; // Contraseña de aplicación
                </div>
                
                <p style="margin-top: 15px;">
                    <strong>Nota:</strong> Para Gmail, necesitas generar una contraseña de aplicación en:
                    <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: #2563eb;">
                        https://myaccount.google.com/apppasswords
                    </a>
                </p>
            </div>
            
            <!-- Volver -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="../vistas/configuracion.php" style="color: #2563eb; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Volver a Configuración
                </a>
            </div>
        </div>
    </div>
</body>
</html>