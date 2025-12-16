-- ============================================================================
-- Crear Tabla de Recuperación de Contraseña - Sistema ARCO
-- Ejecutar este script si la tabla no existe
-- ============================================================================

USE arco_bdd;

-- Crear tabla de recuperación de contraseña
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expira_en DATETIME NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expira (expira_en),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que se creó correctamente
SELECT 'Tabla password_resets creada exitosamente' AS mensaje;
SELECT COUNT(*) AS total_registros FROM password_resets;