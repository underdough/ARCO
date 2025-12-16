<?php
/**
 * Configuración de Email - Sistema ARCO
 * Configuración centralizada para envío de correos
 */

// Prevenir acceso directo
if (!defined('EMAIL_CONFIG_LOADED')) {
    define('EMAIL_CONFIG_LOADED', true);
}

/**
 * Configuración de Email
 * 
 * IMPORTANTE: Cambiar estos valores según tu entorno
 */
class ConfigEmail {
    
    // ============================================================================
    // CONFIGURACIÓN GENERAL
    // ============================================================================
    
    // Modo: 'desarrollo' o 'produccion'
    const MODO = 'desarrollo'; // Cambiar a 'produccion' cuando esté listo
    
    // ============================================================================
    // CONFIGURACIÓN SMTP (Para producción)
    // ============================================================================
    
    // Proveedor SMTP (gmail, outlook, sendgrid, mailgun, custom)
    const SMTP_PROVIDER = 'gmail';
    
    // Servidor SMTP
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls'; // 'tls' o 'ssl'
    
    // Credenciales SMTP
    const SMTP_USERNAME = 'correopruebas0701@gmail.com'; // Cambiar por tu email
    const SMTP_PASSWORD = 'lwhn guvl orqz jeys';  // Contraseña de aplicación de Gmail
    
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
 * INSTRUCCIONES DE CONFIGURACIÓN
 * 
 * 1. GMAIL:
 *    - Ir a: https://myaccount.google.com/apppasswords
 *    - Generar contraseña de aplicación
 *    - Usar esa contraseña en SMTP_PASSWORD
 * 
 * 2. OUTLOOK/HOTMAIL:
 *    - Usar tu email y contraseña normal
 *    - Asegurarse de tener SMTP habilitado
 * 
 * 3. SENDGRID:
 *    - Crear cuenta en sendgrid.com
 *    - Generar API Key
 *    - Usuario: 'apikey'
 *    - Contraseña: tu API Key
 * 
 * 4. MAILGUN:
 *    - Crear cuenta en mailgun.com
 *    - Obtener credenciales SMTP
 *    - Configurar dominio verificado
 * 
 * 5. SERVIDOR PROPIO:
 *    - Configurar SMTP_HOST, SMTP_PORT
 *    - Configurar credenciales
 *    - Cambiar SMTP_PROVIDER a 'custom'
 */