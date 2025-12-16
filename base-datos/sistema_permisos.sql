-- =====================================================
-- SISTEMA DE GESTIÓN DE PERMISOS POR ROLES
-- =====================================================

-- 1. Crear tabla de permisos disponibles en el sistema
CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` INT NOT NULL AUTO_INCREMENT,
  `modulo` VARCHAR(50) NOT NULL COMMENT 'Módulo del sistema (productos, categorias, usuarios, etc)',
  `accion` VARCHAR(50) NOT NULL COMMENT 'Acción permitida (ver, crear, editar, eliminar)',
  `nombre_permiso` VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo del permiso',
  `descripcion` TEXT NULL COMMENT 'Descripción detallada del permiso',
  `codigo_permiso` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Código único del permiso (ej: productos.crear)',
  `activo` TINYINT(1) DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_permiso`),
  INDEX `idx_modulo` (`modulo`),
  INDEX `idx_codigo` (`codigo_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- 2. Crear tabla de relación roles-permisos
CREATE TABLE IF NOT EXISTS `roles_permisos` (
  `id_rol_permiso` INT NOT NULL AUTO_INCREMENT,
  `rol` ENUM('administrador','gerente','supervisor','almacenista','recepcionista','usuario') NOT NULL,
  `id_permiso` INT NOT NULL,
  `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `asignado_por` INT NULL COMMENT 'ID del administrador que asignó el permiso',
  PRIMARY KEY (`id_rol_permiso`),
  UNIQUE KEY `unique_rol_permiso` (`rol`, `id_permiso`),
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id_permiso`) ON DELETE CASCADE,
  INDEX `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- 3. Actualizar tabla de usuarios para incluir recepcionista
ALTER TABLE `usuarios` 
MODIFY COLUMN `rol` ENUM('administrador','gerente','supervisor','almacenista','recepcionista','usuario') 
COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'usuario';

-- 4. Crear tabla de auditoría de permisos
CREATE TABLE IF NOT EXISTS `auditoria_permisos` (
  `id_auditoria_permiso` INT NOT NULL AUTO_INCREMENT,
  `rol` VARCHAR(50) NOT NULL,
  `id_permiso` INT NULL,
  `accion` ENUM('asignar','revocar','modificar') NOT NULL,
  `realizado_por` INT NOT NULL,
  `fecha_accion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `detalles` TEXT NULL,
  PRIMARY KEY (`id_auditoria_permiso`),
  INDEX `idx_rol` (`rol`),
  INDEX `idx_fecha` (`fecha_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- 5. Insertar permisos base del sistema
INSERT INTO `permisos` (`modulo`, `accion`, `nombre_permiso`, `descripcion`, `codigo_permiso`) VALUES
-- Dashboard
('dashboard', 'ver', 'Ver Dashboard', 'Acceso al panel principal del sistema', 'dashboard.ver'),
('dashboard', 'estadisticas', 'Ver Estadísticas', 'Ver estadísticas y métricas del sistema', 'dashboard.estadisticas'),

-- Productos/Materiales
('productos', 'ver', 'Ver Productos', 'Consultar listado de productos/materiales', 'productos.ver'),
('productos', 'crear', 'Crear Productos', 'Agregar nuevos productos al inventario', 'productos.crear'),
('productos', 'editar', 'Editar Productos', 'Modificar información de productos existentes', 'productos.editar'),
('productos', 'eliminar', 'Eliminar Productos', 'Eliminar productos del sistema', 'productos.eliminar'),
('productos', 'stock', 'Gestionar Stock', 'Actualizar cantidades de stock', 'productos.stock'),

-- Categorías
('categorias', 'ver', 'Ver Categorías', 'Consultar categorías del inventario', 'categorias.ver'),
('categorias', 'crear', 'Crear Categorías', 'Agregar nuevas categorías', 'categorias.crear'),
('categorias', 'editar', 'Editar Categorías', 'Modificar categorías existentes', 'categorias.editar'),
('categorias', 'eliminar', 'Eliminar Categorías', 'Eliminar categorías del sistema', 'categorias.eliminar'),

-- Movimientos
('movimientos', 'ver', 'Ver Movimientos', 'Consultar historial de movimientos', 'movimientos.ver'),
('movimientos', 'entrada', 'Registrar Entradas', 'Registrar entrada de materiales', 'movimientos.entrada'),
('movimientos', 'salida', 'Registrar Salidas', 'Registrar salida de materiales', 'movimientos.salida'),
('movimientos', 'aprobar', 'Aprobar Movimientos', 'Aprobar entradas y salidas de materiales', 'movimientos.aprobar'),
('movimientos', 'editar', 'Editar Movimientos', 'Modificar movimientos registrados', 'movimientos.editar'),
('movimientos', 'eliminar', 'Eliminar Movimientos', 'Eliminar movimientos del sistema', 'movimientos.eliminar'),

-- Usuarios
('usuarios', 'ver', 'Ver Usuarios', 'Consultar listado de usuarios', 'usuarios.ver'),
('usuarios', 'crear', 'Crear Usuarios', 'Agregar nuevos usuarios al sistema', 'usuarios.crear'),
('usuarios', 'editar', 'Editar Usuarios', 'Modificar información de usuarios', 'usuarios.editar'),
('usuarios', 'eliminar', 'Eliminar Usuarios', 'Eliminar usuarios del sistema', 'usuarios.eliminar'),
('usuarios', 'roles', 'Gestionar Roles', 'Asignar y modificar roles de usuarios', 'usuarios.roles'),
('usuarios', 'permisos', 'Gestionar Permisos', 'Asignar y modificar permisos de roles', 'usuarios.permisos'),

-- Reportes
('reportes', 'ver', 'Ver Reportes', 'Acceso al módulo de reportes', 'reportes.ver'),
('reportes', 'basicos', 'Reportes Básicos', 'Generar reportes básicos de inventario', 'reportes.basicos'),
('reportes', 'avanzados', 'Reportes Avanzados', 'Generar reportes avanzados y financieros', 'reportes.avanzados'),
('reportes', 'exportar', 'Exportar Reportes', 'Exportar reportes a PDF/Excel', 'reportes.exportar'),

-- Configuración
('configuracion', 'ver', 'Ver Configuración', 'Acceso a configuración del sistema', 'configuracion.ver'),
('configuracion', 'empresa', 'Configurar Empresa', 'Modificar datos de la empresa', 'configuracion.empresa'),
('configuracion', 'sistema', 'Configurar Sistema', 'Modificar configuración del sistema', 'configuracion.sistema'),
('configuracion', 'copias', 'Gestionar Copias', 'Crear y restaurar copias de seguridad', 'configuracion.copias'),

-- Auditoría
('auditoria', 'ver', 'Ver Auditoría', 'Consultar registros de auditoría', 'auditoria.ver'),
('auditoria', 'exportar', 'Exportar Auditoría', 'Exportar registros de auditoría', 'auditoria.exportar');

-- 6. Asignar permisos por rol según especificaciones

-- ADMINISTRADOR: Acceso completo a todo
INSERT INTO `roles_permisos` (`rol`, `id_permiso`)
SELECT 'administrador', id_permiso FROM permisos WHERE activo = 1;

-- GERENTE: Acceso completo excepto gestión de permisos (requiere aprobación)
INSERT INTO `roles_permisos` (`rol`, `id_permiso`)
SELECT 'gerente', id_permiso FROM permisos 
WHERE activo = 1 
AND codigo_permiso NOT IN ('usuarios.permisos');

-- SUPERVISOR: Supervisión, aprobación, reportes básicos
INSERT INTO `roles_permisos` (`rol`, `id_permiso`)
SELECT 'supervisor', id_permiso FROM permisos 
WHERE codigo_permiso IN (
    'dashboard.ver',
    'dashboard.estadisticas',
    'productos.ver',
    'categorias.ver',
    'movimientos.ver',
    'movimientos.aprobar',
    'reportes.ver',
    'reportes.basicos',
    'reportes.exportar',
    'auditoria.ver'
);

-- ALMACENISTA: Gestión de inventario, movimientos, reportes de stock
INSERT INTO `roles_permisos` (`rol`, `id_permiso`)
SELECT 'almacenista', id_permiso FROM permisos 
WHERE codigo_permiso IN (
    'dashboard.ver',
    'productos.ver',
    'productos.crear',
    'productos.editar',
    'productos.stock',
    'categorias.ver',
    'categorias.crear',
    'categorias.editar',
    'movimientos.ver',
    'movimientos.entrada',
    'movimientos.salida',
    'movimientos.editar',
    'reportes.ver',
    'reportes.basicos',
    'reportes.exportar'
);

-- RECEPCIONISTA: Registro de entradas/salidas, consulta de inventario
INSERT INTO `roles_permisos` (`rol`, `id_permiso`)
SELECT 'recepcionista', id_permiso FROM permisos 
WHERE codigo_permiso IN (
    'dashboard.ver',
    'productos.ver',
    'categorias.ver',
    'movimientos.ver',
    'movimientos.entrada',
    'movimientos.salida'
);

-- USUARIO: Solo consulta básica
INSERT INTO `roles_permisos` (`rol`, `id_permiso`)
SELECT 'usuario', id_permiso FROM permisos 
WHERE codigo_permiso IN (
    'dashboard.ver',
    'productos.ver',
    'categorias.ver',
    'movimientos.ver'
);

-- 7. Crear vista para consulta rápida de permisos por rol
CREATE OR REPLACE VIEW `vista_permisos_roles` AS
SELECT 
    rp.rol,
    p.modulo,
    p.accion,
    p.nombre_permiso,
    p.codigo_permiso,
    p.descripcion,
    rp.fecha_asignacion
FROM roles_permisos rp
INNER JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE p.activo = 1
ORDER BY rp.rol, p.modulo, p.accion;

-- 8. Crear función para verificar si un rol tiene un permiso específico
DELIMITER //
CREATE FUNCTION IF NOT EXISTS tiene_permiso(
    p_rol VARCHAR(50),
    p_codigo_permiso VARCHAR(100)
) RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE tiene INT;
    
    SELECT COUNT(*) INTO tiene
    FROM roles_permisos rp
    INNER JOIN permisos p ON rp.id_permiso = p.id_permiso
    WHERE rp.rol = p_rol 
    AND p.codigo_permiso = p_codigo_permiso
    AND p.activo = 1;
    
    RETURN tiene > 0;
END//
DELIMITER ;

COMMIT;

-- Verificación de instalación
SELECT 'Sistema de permisos instalado correctamente' as mensaje;
SELECT COUNT(*) as total_permisos FROM permisos;
SELECT rol, COUNT(*) as permisos_asignados FROM roles_permisos GROUP BY rol;
