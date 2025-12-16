-- Tabla para códigos de verificación 2FA
-- Sistema ARCO

CREATE TABLE IF NOT EXISTS `verification_codes` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar columnas de 2FA a la tabla usuarios si no existen
ALTER TABLE `usuarios` 
ADD COLUMN IF NOT EXISTS `two_factor_enabled` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `two_factor_method` VARCHAR(10) DEFAULT 'email',
ADD COLUMN IF NOT EXISTS `intentos_fallidos` INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS `bloqueado_hasta` DATETIME NULL,
ADD COLUMN IF NOT EXISTS `token_recordar` VARCHAR(64) NULL,
ADD COLUMN IF NOT EXISTS `token_recordar_expira` DATETIME NULL,
ADD COLUMN IF NOT EXISTS `ultimo_acceso` DATETIME NULL;

-- Índices para optimización
CREATE INDEX IF NOT EXISTS `idx_two_factor` ON `usuarios`(`two_factor_enabled`, `two_factor_method`);
CREATE INDEX IF NOT EXISTS `idx_bloqueado` ON `usuarios`(`bloqueado_hasta`);
CREATE INDEX IF NOT EXISTS `idx_token_recordar` ON `usuarios`(`token_recordar`);
