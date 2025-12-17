-- =====================================================
-- MEJORA DE GESTIÓN DE ROLES Y USUARIOS
-- =====================================================
-- Este script mejora la tabla de usuarios para una gestión más completa

-- 1. Modificar la tabla usuarios para agregar más roles y campos
ALTER TABLE `usuarios` 
MODIFY COLUMN `rol` ENUM('administrador','usuario','almacenista','supervisor','gerente') 
COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'usuario';

-- 2. Agregar índices para mejorar búsquedas
ALTER TABLE `usuarios` 
ADD INDEX `idx_estado` (`estado`),
ADD INDEX `idx_rol` (`rol`),
ADD INDEX `idx_correo` (`correo`);

-- 3. Modificar el campo estado para usar ENUM
ALTER TABLE `usuarios` 
MODIFY COLUMN `estado` ENUM('ACTIVO','INACTIVO','SUSPENDIDO') 
COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'ACTIVO';

-- 4. Agregar campo para fecha de última modificación
ALTER TABLE `usuarios` 
ADD COLUMN `fecha_modificacion` DATETIME NULL DEFAULT NULL AFTER `fecha_creacion`,
ADD COLUMN `modificado_por` INT NULL DEFAULT NULL AFTER `fecha_modificacion`;

-- 5. Crear tabla de auditoría para cambios en usuarios
CREATE TABLE IF NOT EXISTS `auditoria_usuarios` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `accion` ENUM('crear','editar','eliminar','activar','desactivar','suspender') NOT NULL,
  `campo_modificado` VARCHAR(50) NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `realizado_por` INT NOT NULL,
  `fecha_accion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL,
  PRIMARY KEY (`id_auditoria`),
  INDEX `idx_usuario` (`usuario_id`),
  INDEX `idx_fecha` (`fecha_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- 6. Actualizar registros existentes con fecha de modificación
UPDATE `usuarios` 
SET `fecha_modificacion` = `fecha_creacion` 
WHERE `fecha_modificacion` IS NULL;

COMMIT;
