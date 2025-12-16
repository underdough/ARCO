-- ============================================================================
-- Script de Actualización de Base de Datos - Sistema ARCO v2.0
-- Añade soporte para 2FA, auditoría mejorada y nuevas funcionalidades
-- ============================================================================

USE arco_bdd;

-- ============================================================================
-- 1. ACTUALIZAR TABLA DE USUARIOS
-- ============================================================================

-- Verificar si las columnas ya existen antes de agregarlas
SET @dbname = DATABASE();
SET @tablename = 'usuarios';

-- Agregar columnas de 2FA si no existen
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'two_factor_enabled'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER estado',
    'SELECT "Columna two_factor_enabled ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'two_factor_method'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN two_factor_method VARCHAR(10) DEFAULT "email" AFTER two_factor_enabled',
    'SELECT "Columna two_factor_method ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columnas de seguridad si no existen
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'intentos_fallidos'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN intentos_fallidos INT DEFAULT 0 AFTER two_factor_method',
    'SELECT "Columna intentos_fallidos ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'bloqueado_hasta'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN bloqueado_hasta DATETIME NULL AFTER intentos_fallidos',
    'SELECT "Columna bloqueado_hasta ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columnas de auditoría si no existen
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'ultimo_acceso'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN ultimo_acceso DATETIME NULL AFTER bloqueado_hasta',
    'SELECT "Columna ultimo_acceso ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'fecha_creacion'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP AFTER ultimo_acceso',
    'SELECT "Columna fecha_creacion ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'fecha_actualizacion'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP AFTER fecha_creacion',
    'SELECT "Columna fecha_actualizacion ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columnas de token de recordar si no existen
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'token_recordar'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN token_recordar VARCHAR(64) NULL AFTER fecha_actualizacion',
    'SELECT "Columna token_recordar ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'token_recordar_expira'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE usuarios ADD COLUMN token_recordar_expira DATETIME NULL AFTER token_recordar',
    'SELECT "Columna token_recordar_expira ya existe" AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 2. CREAR TABLA DE RECUPERACIÓN DE CONTRASEÑA
-- ============================================================================

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expira_en DATETIME NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expira (expira_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. CREAR TABLA DE CÓDIGOS DE VERIFICACIÓN 2FA
-- ============================================================================

CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(6) NOT NULL,
    type VARCHAR(10) DEFAULT 'email',
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    attempts INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE,
    INDEX idx_user_code (user_id, code),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. CREAR/ACTUALIZAR TABLA DE AUDITORÍA
-- ============================================================================

CREATE TABLE IF NOT EXISTS auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT NULL,
    realizado_por INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE SET NULL,
    FOREIGN KEY (realizado_por) REFERENCES usuarios(id_usuarios) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. CREAR TABLA DE EMPRESA (si no existe)
-- ============================================================================

CREATE TABLE IF NOT EXISTS empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    nif VARCHAR(50) NULL,
    direccion VARCHAR(255) NULL,
    ciudad VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    logo VARCHAR(255) NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar registro por defecto si no existe
INSERT INTO empresa (id, nombre, nif, direccion, ciudad, telefono, email)
SELECT 2, 'ARCO Sistema', '', '', '', '', ''
WHERE NOT EXISTS (SELECT 1 FROM empresa WHERE id = 2);

-- ============================================================================
-- 5. CREAR TABLA DE NOTIFICACIONES (si no existe)
-- ============================================================================

CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    notify_low_stock TINYINT(1) DEFAULT 0,
    low_stock_threshold INT DEFAULT 15,
    notify_movements TINYINT(1) DEFAULT 0,
    notify_email TINYINT(1) DEFAULT 0,
    notification_emails TEXT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. CREAR TABLA DE COPIAS DE SEGURIDAD (si no existe)
-- ============================================================================

CREATE TABLE IF NOT EXISTS copias_seguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    auto_backup TINYINT(1) DEFAULT 0,
    frecuencia VARCHAR(20) DEFAULT 'diaria',
    retencion_dias INT DEFAULT 30,
    ultima_copia DATETIME NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. CREAR TABLA DE PERMISOS DE USUARIO (si no existe)
-- ============================================================================

CREATE TABLE IF NOT EXISTS permisos_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    ver TINYINT(1) DEFAULT 0,
    crear TINYINT(1) DEFAULT 0,
    editar TINYINT(1) DEFAULT 0,
    eliminar TINYINT(1) DEFAULT 0,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_modulo (usuario_id, modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. ACTUALIZAR CONTRASEÑAS EXISTENTES A FORMATO HASH
-- ============================================================================

-- Nota: Este script solo marca las contraseñas que necesitan actualización
-- Las contraseñas se actualizarán automáticamente en el próximo login

UPDATE usuarios 
SET fecha_actualizacion = NOW()
WHERE LENGTH(contrasena) < 60;

-- ============================================================================
-- 9. CREAR ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================================================

-- Índices en tabla usuarios
CREATE INDEX IF NOT EXISTS idx_num_doc ON usuarios(num_doc);
CREATE INDEX IF NOT EXISTS idx_correo ON usuarios(correo);
CREATE INDEX IF NOT EXISTS idx_estado ON usuarios(estado);
CREATE INDEX IF NOT EXISTS idx_rol ON usuarios(rol);

-- Índices en tabla auditoria
CREATE INDEX IF NOT EXISTS idx_fecha_hora ON auditoria(fecha_hora DESC);

-- ============================================================================
-- 10. CREAR VISTAS ÚTILES
-- ============================================================================

-- Vista de usuarios activos con información de 2FA
CREATE OR REPLACE VIEW v_usuarios_activos AS
SELECT 
    u.id_usuarios,
    u.nombre,
    u.apellido,
    u.num_doc,
    u.correo,
    u.rol,
    u.two_factor_enabled,
    u.two_factor_method,
    u.ultimo_acceso,
    u.fecha_creacion,
    COUNT(DISTINCT a.id) as total_acciones
FROM usuarios u
LEFT JOIN auditoria a ON u.id_usuarios = a.usuario_id
WHERE u.estado = 'activo'
GROUP BY u.id_usuarios;

-- Vista de auditoría reciente
CREATE OR REPLACE VIEW v_auditoria_reciente AS
SELECT 
    a.id,
    a.accion,
    a.descripcion,
    u.nombre as usuario_nombre,
    u.apellido as usuario_apellido,
    a.ip_address,
    a.fecha_hora
FROM auditoria a
LEFT JOIN usuarios u ON a.usuario_id = u.id_usuarios
ORDER BY a.fecha_hora DESC
LIMIT 100;

-- ============================================================================
-- 11. CREAR PROCEDIMIENTOS ALMACENADOS
-- ============================================================================

DELIMITER //

-- Procedimiento para limpiar códigos 2FA expirados
CREATE PROCEDURE IF NOT EXISTS sp_limpiar_codigos_expirados()
BEGIN
    DELETE FROM verification_codes WHERE expires_at < NOW();
    SELECT ROW_COUNT() as codigos_eliminados;
END //

-- Procedimiento para obtener estadísticas de usuarios
CREATE PROCEDURE IF NOT EXISTS sp_estadisticas_usuarios()
BEGIN
    SELECT 
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as usuarios_activos,
        SUM(CASE WHEN two_factor_enabled = 1 THEN 1 ELSE 0 END) as usuarios_con_2fa,
        SUM(CASE WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as usuarios_activos_semana
    FROM usuarios;
END //

-- Procedimiento para registrar auditoría
CREATE PROCEDURE IF NOT EXISTS sp_registrar_auditoria(
    IN p_usuario_id INT,
    IN p_accion VARCHAR(50),
    IN p_descripcion TEXT,
    IN p_ip_address VARCHAR(45),
    IN p_user_agent TEXT
)
BEGIN
    INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
    VALUES (p_usuario_id, p_accion, p_descripcion, p_ip_address, p_user_agent, NOW());
END //

DELIMITER ;

-- ============================================================================
-- 12. CREAR EVENTOS PROGRAMADOS
-- ============================================================================

-- Habilitar el programador de eventos
SET GLOBAL event_scheduler = ON;

-- Evento para limpiar códigos 2FA expirados cada hora
CREATE EVENT IF NOT EXISTS evt_limpiar_codigos_2fa
ON SCHEDULE EVERY 1 HOUR
DO
    CALL sp_limpiar_codigos_expirados();

-- Evento para limpiar auditoría antigua (más de 90 días)
CREATE EVENT IF NOT EXISTS evt_limpiar_auditoria_antigua
ON SCHEDULE EVERY 1 DAY
DO
    DELETE FROM auditoria WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- ============================================================================
-- 13. INSERTAR DATOS DE PRUEBA (OPCIONAL)
-- ============================================================================

-- Insertar usuario administrador de prueba si no existe
INSERT INTO usuarios (nombre, apellido, num_doc, correo, contrasena, rol, estado, two_factor_enabled)
SELECT 'Admin', 'Sistema', 12345678, 'admin@arco.com', 
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
       'administrador_sistema', 'activo', 0
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE num_doc = 12345678);

-- ============================================================================
-- 14. VERIFICACIÓN FINAL
-- ============================================================================

-- Mostrar resumen de tablas creadas/actualizadas
SELECT 
    'Actualización completada exitosamente' as mensaje,
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM auditoria) as total_registros_auditoria,
    (SELECT COUNT(*) FROM verification_codes) as codigos_2fa_activos;

-- ============================================================================
-- FIN DEL SCRIPT DE ACTUALIZACIÓN
-- ============================================================================