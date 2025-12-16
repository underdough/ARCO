<?php
/**
 * Configuración de Email - EJEMPLO
 * Sistema ARCO
 * 
 * INSTRUCCIONES:
 * 1. Copiar este archivo como: config_email_local.php
 * 2. Editar config_email_local.php con tus credenciales reales
 * 3. El archivo config_email_local.php está en .gitignore (no se subirá a Git)
 * 4. Usar config_email_local.php para credenciales sensibles
 */

// Prevenir acceso directo
if (!defined('EMAIL_CONFIG_LOADED')) {
    define('EMAIL_CONFIG_LOADED', true);
}

/**
 * Configuración de Email
 */
class ConfigEmail {
    
    // ============================================================================
    // CONFIGURACIÓN GENERAL
    // ============================================================================
    
    // Modo: 'desarrollo' o 'produccion'
    const MODO = 'desarrollo';
    
    // ============================================================================
    // CONFIGURACIÓN SMTP
    // ============================================================================
    
    // Proveedor SMTP (gmail, outlook, sendgrid, mailgun, custom)
    const SMTP_PROVIDER = 'gmail';
    
    // Servidor SMTP
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls'; // 'tls' o 'ssl'
    
    // Credenciales SMTP
    const SMTP_USERNAME = 'tu_email@gmail.com';        // ← CAMBIAR
    const SMTP_PASSWORD = 'tu_contraseña_aplicacion';  // ← CAMBIAR
    
    // Remitente
    const FROM_EMAIL = 'noreply@arco.com';
    const FROM_NAME = 'Sistema ARCO';
    
    // ============================================================================
    // CONFIGURACIONES PREDEFINIDAS POR PROVEEDOR
    // ============================================================================
    
    public static function getProviderConfig($provider = null) {
        $provider = $provider ?? self::SMTP_PROVIDER;
        
        $configs = [
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'secure' => 'tls',
                'auth' => true,
                'nota' => 'Usar contraseña de aplicación: https://myaccount.google.com/apppasswords'
            ],
            'outlook' => [
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'secure' => 'tls',
                'auth' => true,
                'nota' => 'Usar tu contraseña de Outlook/Hotmail'
            ],
            'office365' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'secure' => 'tls',
                'auth' => true,
                'nota' => 'Para cuentas de Office 365'
            ],
            'sendgrid' => [
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'secure' => 'tls',
                'auth' => true,
                'nota' => 'Usar API Key como contraseña'
            ],
            'mailgun' => [
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'secure' => 'tls',
                'auth' => true,
                'nota' => 'Usar credenciales de Mailgun'
            ],
            'custom' => [
                'host' => self::SMTP_HOST,
                'port' => self::SMTP_PORT,
                'secure' => self::SMTP_SECURE,
                'auth' => true,
                'nota' => 'Configuración personalizada'
            ]
        ];
        
        return $configs[$provider] ?? $configs['custom'];
    }
    
    // ============================================================================
    // MÉTODOS DE UTILIDAD
    // ============================================================================
    
    public static function esProduccion() {
        return self::MODO === 'produccion';
    }
    
    public static function esDesarrollo() {
        return self::MODO === 'desarrollo';
    }
    
    public static function getConfig() {
        return [
            'modo' => self::MODO,
            'smtp_host' => self::SMTP_HOST,
            'smtp_port' => self::SMTP_PORT,
            'smtp_secure' => self::SMTP_SECURE,
            'smtp_username' => self::SMTP_USERNAME,
            'smtp_password' => self::SMTP_PASSWORD,
            'from_email' => self::FROM_EMAIL,
            'from_name' => self::FROM_NAME,
            'provider' => self::SMTP_PROVIDER
        ];
    }
}

/**
 * ============================================================================
 * GUÍA RÁPIDA DE CONFIGURACIÓN
 * ============================================================================
 * 
 * GMAIL:
 * ------
 * 1. Ir a: https://myaccount.google.com/security
 * 2. Activar "Verificación en 2 pasos"
 * 3. Ir a: https://myaccount.google.com/apppasswords
 * 4. Crear contraseña para "Sistema ARCO"
 * 5. Copiar contraseña generada (16 caracteres)
 * 6. Configurar:
 *    const SMTP_PROVIDER = 'gmail';
 *    const SMTP_USERNAME = 'tu_email@gmail.com';
 *    const SMTP_PASSWORD = 'xxxx xxxx xxxx xxxx';
 * 
 * OUTLOOK/HOTMAIL:
 * ----------------
 * 1. Usar tu email y contraseña normal
 * 2. Configurar:
 *    const SMTP_PROVIDER = 'outlook';
 *    const SMTP_USERNAME = 'tu_email@outlook.com';
 *    const SMTP_PASSWORD = 'tu_contraseña';
 * 
 * SENDGRID:
 * ---------
 * 1. Crear cuenta en: https://sendgrid.com
 * 2. Generar API Key en Settings > API Keys
 * 3. Configurar:
 *    const SMTP_PROVIDER = 'sendgrid';
 *    const SMTP_USERNAME = 'apikey';
 *    const SMTP_PASSWORD = 'SG.xxxxxxxxxx';
 * 
 * MAILGUN:
 * --------
 * 1. Crear cuenta en: https://mailgun.com
 * 2. Obtener credenciales SMTP
 * 3. Configurar:
 *    const SMTP_PROVIDER = 'mailgun';
 *    const SMTP_USERNAME = 'postmaster@tu-dominio.mailgun.org';
 *    const SMTP_PASSWORD = 'tu_contraseña_mailgun';
 * 
 * SERVIDOR PERSONALIZADO:
 * -----------------------
 * const SMTP_PROVIDER = 'custom';
 * const SMTP_HOST = 'mail.tudominio.com';
 * const SMTP_PORT = 587;
 * const SMTP_SECURE = 'tls';
 * const SMTP_USERNAME = 'noreply@tudominio.com';
 * const SMTP_PASSWORD = 'tu_contraseña';
 * 
 * ============================================================================
 * PROBAR CONFIGURACIÓN
 * ============================================================================
 * 
 * Abrir en el navegador:
 * http://localhost/ARCO/servicios/test_email.php
 * 
 * ============================================================================
 * DOCUMENTACIÓN COMPLETA
 * ============================================================================
 * 
 * Ver: documentacion/configuracion_email_produccion.md
 * 
 */
