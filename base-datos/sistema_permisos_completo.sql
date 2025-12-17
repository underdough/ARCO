-- =====================================================
-- SISTEMA COMPLETO DE PERMISOS POR ROL - ARCO
-- =====================================================
-- Versión: 2.0
-- Fecha: Diciembre 2025
-- Descripción: Sistema granular de permisos que permite
--              controlar el acceso a cada módulo y acción

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. TABLA DE MÓDULOS DEL SISTEMA
-- =====================================================
DROP TABLE IF EXISTS `modulos`;
CREATE TABLE `modulos` (
  `id_modulo` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `icono` VARCHAR(50) NULL,
  `ruta` VARCHAR(100) NULL,
  `orden` INT DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `nombre_unique` (`nombre`),
  INDEX `idx_activo` (`activo`),
  INDEX `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =====================================================
-- 2. TABLA DE PERMISOS (ACCIONES)
-- =====================================================
DROP TABLE IF EXISTS `permisos`;
CREATE TABLE `permisos` (
  `id_permiso` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =====================================================
-- 3. TABLA RELACIÓN MÓDULO-PERMISOS
-- =====================================================
DROP TABLE IF EXISTS `modulo_permisos`;
CREATE TABLE `modulo_permisos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_modulo` INT NOT NULL,
  `id_permiso` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `modulo_permiso_unique` (`id_modulo`, `id_permiso`),
  FOREIGN KEY (`id_modulo`) REFERENCES `modulos`(`id_modulo`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id_permiso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =====================================================
-- 4. TABLA ROL-PERMISOS (Permisos asignados a roles)
-- =====================================================
DROP TABLE IF EXISTS `rol_permisos`;
CREATE TABLE `rol_permisos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `rol` ENUM('administrador','usuario','almacenista','supervisor','gerente') NOT NULL,
  `id_modulo` INT NOT NULL,
  `id_permiso` INT NOT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rol_modulo_permiso` (`rol`, `id_modulo`, `id_permiso`),
  FOREIGN KEY (`id_modulo`) REFERENCES `modulos`(`id_modulo`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id_permiso`) ON DELETE CASCADE,
  INDEX `idx_rol` (`rol`),
  INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =====================================================
-- 5. TABLA DE AUDITORÍA DE PERMISOS
-- =====================================================
DROP TABLE IF EXISTS `auditoria_permisos`;
CREATE TABLE `auditoria_permisos` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `rol` VARCHAR(50) NOT NULL,
  `id_modulo` INT NULL,
  `id_permiso` INT NULL,
  `accion` ENUM('asignar','modificar','eliminar','activar','desactivar') NOT NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `realizado_por` INT NOT NULL,
  `fecha_accion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL,
  PRIMARY KEY (`id_auditoria`),
  INDEX `idx_rol` (`rol`),
  INDEX `idx_fecha` (`fecha_accion`),
  INDEX `idx_realizado_por` (`realizado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =====================================================
-- INSERTAR MÓDULOS DEL SISTEMA
-- =====================================================
INSERT INTO `modulos` (`nombre`, `descripcion`, `icono`, `ruta`, `orden`, `activo`) VALUES
('dashboard', 'Panel de Control', 'fa-tachometer-alt', 'dashboard.php', 1, 1),
('productos', 'Gestión de Productos', 'fa-box', 'productos.php', 2, 1),
('categorias', 'Gestión de Categorías', 'fa-tags', 'categorias.php', 3, 1),
('movimientos', 'Movimientos de Inventario', 'fa-exchange-alt', 'movimientos.php', 4, 1),
('usuarios', 'Gestión de Usuarios', 'fa-users', 'gestion_usuarios.php', 5, 1),
('reportes', 'Reportes y Estadísticas', 'fa-chart-bar', 'reportes.php', 6, 1),
('configuracion', 'Configuración del Sistema', 'fa-cog', 'configuracion.php', 7, 1),
('ordenes_compra', 'Órdenes de Compra', 'fa-shopping-cart', 'ordenes_compra.php', 8, 1),
('devoluciones', 'Gestión de Devoluciones', 'fa-undo', 'devoluciones.php', 9, 1),
('recepcion', 'Recepción de Materiales', 'fa-truck-loading', 'recepcion.php', 10, 1),
('anomalias_novedades', 'Anomalías y Novedades', 'fa-exclamation-triangle', 'anomalias_novedades.php', 11, 1);

-- =====================================================
-- INSERTAR PERMISOS/ACCIONES
-- =====================================================
INSERT INTO `permisos` (`nombre`, `descripcion`, `codigo`, `activo`) VALUES
('Ver', 'Ver/Consultar información', 'ver', 1),
('Crear', 'Crear/Agregar nuevos registros', 'crear', 1),
('Editar', 'Editar/Modificar registros existentes', 'editar', 1),
('Eliminar', 'Eliminar registros', 'eliminar', 1),
('Exportar', 'Exportar datos a archivos', 'exportar', 1),
('Importar', 'Importar datos desde archivos', 'importar', 1),
('Aprobar', 'Aprobar operaciones', 'aprobar', 1),
('Auditar', 'Ver registros de auditoría', 'auditar', 1);

-- =====================================================
-- ASIGNAR PERMISOS DISPONIBLES A CADA MÓDULO
-- =====================================================

-- Dashboard: Solo ver
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'dashboard' AND p.codigo = 'ver';

-- Productos: Ver, Crear, Editar, Eliminar, Exportar, Importar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'productos' AND p.codigo IN ('ver', 'crear', 'editar', 'eliminar', 'exportar', 'importar');

-- Categorías: Ver, Crear, Editar, Eliminar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'categorias' AND p.codigo IN ('ver', 'crear', 'editar', 'eliminar');

-- Movimientos: Ver, Crear, Editar, Aprobar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'movimientos' AND p.codigo IN ('ver', 'crear', 'editar', 'aprobar', 'exportar');

-- Usuarios: Ver, Crear, Editar, Eliminar, Auditar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'usuarios' AND p.codigo IN ('ver', 'crear', 'editar', 'eliminar', 'auditar');

-- Reportes: Ver, Crear, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'reportes' AND p.codigo IN ('ver', 'crear', 'exportar');

-- Configuración: Ver, Editar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'configuracion' AND p.codigo IN ('ver', 'editar');

-- Órdenes de Compra: Ver, Crear, Editar, Aprobar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'ordenes_compra' AND p.codigo IN ('ver', 'crear', 'editar', 'aprobar', 'exportar');

-- Devoluciones: Ver, Crear, Editar, Aprobar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'devoluciones' AND p.codigo IN ('ver', 'crear', 'editar', 'aprobar');

-- Recepción: Ver, Crear, Editar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'recepcion' AND p.codigo IN ('ver', 'crear', 'editar');

-- Anomalías y Novedades: Ver, Crear, Editar, Eliminar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' AND p.codigo IN ('ver', 'crear', 'editar', 'eliminar', 'exportar');

-- =====================================================
-- ASIGNAR PERMISOS POR ROL
-- =====================================================

-- ========== ADMINISTRADOR: Acceso completo a todo ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'administrador', mp.id_modulo, mp.id_permiso, 1
FROM modulo_permisos mp;

-- ========== GERENTE: Acceso amplio excepto gestión completa de usuarios ==========
-- Dashboard: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'dashboard' AND p.codigo = 'ver';

-- Productos: Ver, Crear, Editar, Exportar, Importar (sin eliminar)
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'productos' AND p.codigo IN ('ver', 'crear', 'editar', 'exportar', 'importar');

-- Categorías: Ver, Crear, Editar (sin eliminar)
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'categorias' AND p.codigo IN ('ver', 'crear', 'editar');

-- Movimientos: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre = 'movimientos' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Usuarios: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'usuarios' AND p.codigo = 'ver';

-- Reportes: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre = 'reportes' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Configuración: Ver y Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'configuracion' AND p.codigo IN ('ver', 'editar');

-- Órdenes de Compra: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre = 'ordenes_compra' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Devoluciones: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre = 'devoluciones' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Recepción: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre = 'recepcion' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Anomalías y Novedades: Ver, Crear, Editar, Exportar (sin eliminar)
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' AND p.codigo IN ('ver', 'crear', 'editar', 'exportar');

-- ========== SUPERVISOR: Supervisión y aprobación ==========
-- Dashboard: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'dashboard' AND p.codigo = 'ver';

-- Productos: Ver y Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'productos' AND p.codigo IN ('ver', 'exportar');

-- Categorías: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'categorias' AND p.codigo = 'ver';

-- Movimientos: Ver, Aprobar, Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'movimientos' AND p.codigo IN ('ver', 'aprobar', 'exportar');

-- Reportes: Ver y Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'reportes' AND p.codigo IN ('ver', 'exportar');

-- Órdenes de Compra: Ver y Aprobar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'ordenes_compra' AND p.codigo IN ('ver', 'aprobar');

-- Devoluciones: Ver y Aprobar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'devoluciones' AND p.codigo IN ('ver', 'aprobar');

-- Recepción: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'recepcion' AND p.codigo = 'ver';

-- Anomalías y Novedades: Ver, Crear, Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' AND p.codigo IN ('ver', 'crear', 'exportar');

-- ========== ALMACENISTA: Gestión operativa de inventario ==========
-- Dashboard: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'dashboard' AND p.codigo = 'ver';

-- Productos: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'productos' AND p.codigo IN ('ver', 'crear', 'editar');

-- Categorías: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'categorias' AND p.codigo = 'ver';

-- Movimientos: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'movimientos' AND p.codigo IN ('ver', 'crear', 'editar');

-- Reportes: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'reportes' AND p.codigo = 'ver';

-- Recepción: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'recepcion' AND p.codigo IN ('ver', 'crear', 'editar');

-- Devoluciones: Ver, Crear
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'devoluciones' AND p.codigo IN ('ver', 'crear');

-- Anomalías y Novedades: Ver, Crear
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' AND p.codigo IN ('ver', 'crear');

-- ========== USUARIO: Acceso básico de consulta ==========
-- Dashboard: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'dashboard' AND p.codigo = 'ver';

-- Productos: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'productos' AND p.codigo = 'ver';

-- Categorías: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'categorias' AND p.codigo = 'ver';

-- Movimientos: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'movimientos' AND p.codigo = 'ver';

-- Reportes: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'reportes' AND p.codigo = 'ver';

-- Anomalías y Novedades: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' AND p.codigo = 'ver';

-- =====================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================

-- Ver todos los módulos creados
SELECT 
    id_modulo,
    nombre,
    descripcion,
    icono,
    ruta,
    orden,
    activo
FROM modulos 
ORDER BY orden;

-- Ver todos los permisos creados
SELECT 
    id_permiso,
    nombre,
    descripcion,
    codigo,
    activo
FROM permisos
ORDER BY id_permiso;

-- Ver permisos disponibles por módulo
SELECT 
    m.nombre AS modulo,
    GROUP_CONCAT(p.nombre ORDER BY p.nombre SEPARATOR ', ') AS permisos_disponibles
FROM modulo_permisos mp
JOIN modulos m ON mp.id_modulo = m.id_modulo
JOIN permisos p ON mp.id_permiso = p.id_permiso
GROUP BY m.nombre
ORDER BY m.orden;

-- Ver permisos por rol (Administrador)
SELECT 
    rp.rol,
    m.nombre AS modulo,
    p.nombre AS permiso,
    rp.activo
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador'
ORDER BY m.orden, p.nombre;

-- Resumen de permisos por rol
SELECT 
    rp.rol,
    COUNT(DISTINCT rp.id_modulo) AS modulos_acceso,
    COUNT(*) AS total_permisos
FROM rol_permisos rp
WHERE rp.activo = 1
GROUP BY rp.rol
ORDER BY total_permisos DESC;

COMMIT;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
