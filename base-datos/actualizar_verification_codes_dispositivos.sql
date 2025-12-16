-- Actualización de tabla verification_codes para soporte de dispositivos confiables
-- Sistema ARCO - 2FA con recordar dispositivo

-- Agregar columnas para identificación de dispositivos
ALTER TABLE `verification_codes` 
ADD COLUMN IF NOT EXISTS `verified` TINYINT(1) DEFAULT 0 COMMENT 'Indica si el código fue verificado exitosamente',
ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL COMMENT 'Dirección IP del dispositivo',
ADD COLUMN IF NOT EXISTS `user_agent` VARCHAR(255) NULL COMMENT 'User Agent del navegador',
ADD COLUMN IF NOT EXISTS `device_fingerprint` VARCHAR(64) NULL COMMENT 'Huella digital única del dispositivo';

-- Crear índice para búsquedas por dispositivo
CREATE INDEX IF NOT EXISTS `idx_device` ON `verification_codes`(`user_id`, `device_fingerprint`);

-- Crear índice para búsquedas de dispositivos verificados
CREATE INDEX IF NOT EXISTS `idx_verified` ON `verification_codes`(`user_id`, `verified`, `device_fingerprint`);
