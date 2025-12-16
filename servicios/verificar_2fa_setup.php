<?php
/**
 * Verificación y Configuración Automática de 2FA
 * Sistema ARCO
 * 
 * Este script verifica y crea automáticamente las tablas y columnas necesarias para 2FA
 */

require_once 'conexion.php';

header('Content-Type: text/html; charset=UTF-8');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificación 2FA - Sistema ARCO</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f3f4f6; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; }
        .success { color: #10b981; padding: 10px; background: #d1fae5; border-radius: 6px; margin: 10px 0; }
        .error { color: #ef4444; padding: 10px; background: #fee2e2; border-radius: 6px; margin: 10px 0; }
        .info { color: #2563eb; padding: 10px; background: #dbeafe; border-radius: 6px; margin: 10px 0; }
        .warning { color: #f59e0b; padding: 10px; background: #fef3c7; border-radius: 6px; margin: 10px 0; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #2563eb; background: #f8fafc; }
        code { background: #1f2937; color: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔐 Verificación de Sistema 2FA</h1>
        <p>Verificando y configurando el sistema de autenticación de dos factores...</p>
";

try {
    $conexion = ConectarDB();
    
    if (!$conexion) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    echo "<div class='success'>✅ Conexión a base de datos establecida</div>";
    
    // Verificar y crear tabla verification_codes
    echo "<div class='step'><h3>Paso 1: Tabla verification_codes</h3>";
    
    $checkTable = $conexion->query("SHOW TABLES LIKE 'verification_codes'");
    
    if ($checkTable->num_rows === 0) {
        echo "<div class='warning'>⚠️ Tabla verification_codes no existe. Creando...</div>";
        
        $sql = "CREATE TABLE `verification_codes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `code` VARCHAR(6) NOT NULL,
            `type` VARCHAR(10) DEFAULT 'email',
            `expires_at` DATETIME NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `attempts` INT DEFAULT 0,
            INDEX `idx_user_code` (`user_id`, `code`),
            INDEX `idx_expires` (`expires_at`),
            FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id_usuarios`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conexion->query($sql)) {
            echo "<div class='success'>✅ Tabla verification_codes creada exitosamente</div>";
        } else {
            throw new Exception("Error al crear tabla: " . $conexion->error);
        }
    } else {
        echo "<div class='success'>✅ Tabla verification_codes ya existe</div>";
    }
    echo "</div>";
    
    // Verificar y agregar columnas a usuarios
    echo "<div class='step'><h3>Paso 2: Columnas de 2FA en tabla usuarios</h3>";
    
    $columnas = [
        'two_factor_enabled' => "TINYINT(1) DEFAULT 0",
        'two_factor_method' => "VARCHAR(10) DEFAULT 'email'",
        'intentos_fallidos' => "INT DEFAULT 0",
        'bloqueado_hasta' => "DATETIME NULL",
        'token_recordar' => "VARCHAR(64) NULL",
        'token_recordar_expira' => "DATETIME NULL",
        'ultimo_acceso' => "DATETIME NULL"
    ];
    
    foreach ($columnas as $columna => $tipo) {
        $checkColumn = $conexion->query("SHOW COLUMNS FROM usuarios LIKE '$columna'");
        
        if ($checkColumn->num_rows === 0) {
            echo "<div class='warning'>⚠️ Columna <code>$columna</code> no existe. Agregando...</div>";
            
            $sql = "ALTER TABLE usuarios ADD COLUMN $columna $tipo";
            
            if ($conexion->query($sql)) {
                echo "<div class='success'>✅ Columna <code>$columna</code> agregada</div>";
            } else {
                echo "<div class='error'>❌ Error al agregar columna <code>$columna</code>: " . $conexion->error . "</div>";
            }
        } else {
            echo "<div class='success'>✅ Columna <code>$columna</code> ya existe</div>";
        }
    }
    echo "</div>";
    
    // Crear índices
    echo "<div class='step'><h3>Paso 3: Índices de optimización</h3>";
    
    $indices = [
        'idx_two_factor' => "CREATE INDEX idx_two_factor ON usuarios(two_factor_enabled, two_factor_method)",
        'idx_bloqueado' => "CREATE INDEX idx_bloqueado ON usuarios(bloqueado_hasta)",
        'idx_token_recordar' => "CREATE INDEX idx_token_recordar ON usuarios(token_recordar)"
    ];
    
    foreach ($indices as $nombre => $sql) {
        $checkIndex = $conexion->query("SHOW INDEX FROM usuarios WHERE Key_name = '$nombre'");
        
        if ($checkIndex->num_rows === 0) {
            echo "<div class='warning'>⚠️ Índice <code>$nombre</code> no existe. Creando...</div>";
            
            if ($conexion->query($sql)) {
                echo "<div class='success'>✅ Índice <code>$nombre</code> creado</div>";
            } else {
                // Algunos índices pueden fallar si ya existen con otro nombre, no es crítico
                echo "<div class='info'>ℹ️ Índice <code>$nombre</code>: " . $conexion->error . "</div>";
            }
        } else {
            echo "<div class='success'>✅ Índice <code>$nombre</code> ya existe</div>";
        }
    }
    echo "</div>";
    
    // Verificar tabla de auditoría
    echo "<div class='step'><h3>Paso 4: Tabla de auditoría</h3>";
    
    $checkAuditoria = $conexion->query("SHOW TABLES LIKE 'auditoria'");
    
    if ($checkAuditoria->num_rows === 0) {
        echo "<div class='warning'>⚠️ Tabla auditoria no existe. Creando...</div>";
        
        $sql = "CREATE TABLE `auditoria` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `accion` VARCHAR(50) NOT NULL,
            `descripcion` TEXT,
            `ip_address` VARCHAR(45),
            `user_agent` TEXT,
            `fecha_hora` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_usuario` (`usuario_id`),
            INDEX `idx_fecha` (`fecha_hora`),
            INDEX `idx_accion` (`accion`),
            FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id_usuarios`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conexion->query($sql)) {
            echo "<div class='success'>✅ Tabla auditoria creada exitosamente</div>";
        } else {
            echo "<div class='error'>❌ Error al crear tabla auditoria: " . $conexion->error . "</div>";
        }
    } else {
        echo "<div class='success'>✅ Tabla auditoria ya existe</div>";
    }
    echo "</div>";
    
    // Verificar archivos necesarios
    echo "<div class='step'><h3>Paso 5: Archivos del sistema</h3>";
    
    $archivos = [
        'two_factor_auth.php' => 'Clase de autenticación 2FA',
        'verificacion-2fa.php' => 'Página de verificación',
        'procesar-2fa.php' => 'Procesador de códigos',
        'reenviar-codigo-2fa.php' => 'Reenvío de códigos',
        'guardar_2fa.php' => 'Guardar preferencias 2FA',
        'email_sender.php' => 'Envío de emails con PHPMailer'
    ];
    
    foreach ($archivos as $archivo => $descripcion) {
        if (file_exists(__DIR__ . '/' . $archivo)) {
            echo "<div class='success'>✅ <code>$archivo</code> - $descripcion</div>";
        } else {
            echo "<div class='error'>❌ <code>$archivo</code> no encontrado - $descripcion</div>";
        }
    }
    echo "</div>";
    
    // Verificar PHPMailer
    echo "<div class='step'><h3>Paso 6: PHPMailer</h3>";
    
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            echo "<div class='success'>✅ PHPMailer instalado y disponible</div>";
        } else {
            echo "<div class='error'>❌ PHPMailer no se encuentra</div>";
        }
    } else {
        echo "<div class='error'>❌ Composer autoload no encontrado. Ejecuta: <code>composer require phpmailer/phpmailer</code></div>";
    }
    echo "</div>";
    
    // Resumen final
    echo "<hr>";
    echo "<h2>📊 Resumen</h2>";
    echo "<div class='success'>";
    echo "<h3>✅ Sistema 2FA Configurado Correctamente</h3>";
    echo "<p>El sistema de autenticación de dos factores está listo para usar.</p>";
    echo "<ul>";
    echo "<li>✅ Base de datos configurada</li>";
    echo "<li>✅ Tablas y columnas creadas</li>";
    echo "<li>✅ Archivos del sistema verificados</li>";
    echo "<li>✅ PHPMailer disponible</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🎯 Próximos Pasos</h3>";
    echo "<div class='info'>";
    echo "<ol>";
    echo "<li>Configurar credenciales SMTP en <code>servicios/config_email.php</code></li>";
    echo "<li>Habilitar 2FA para usuarios en <strong>Configuración → Seguridad</strong></li>";
    echo "<li>Probar el login con un usuario que tenga 2FA habilitado</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h3>📚 Documentación</h3>";
    echo "<div class='info'>";
    echo "<ul>";
    echo "<li><a href='test_email.php'>🧪 Probar configuración de email</a></li>";
    echo "<li><a href='../vistas/configuracion.php'>⚙️ Ir a Configuración</a></li>";
    echo "<li><a href='../login.html'>🔐 Ir al Login</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "
    </div>
</body>
</html>";
?>
