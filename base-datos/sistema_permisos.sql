-- =====================================================
-- SISTEMA DE PERMISOS POR ROL - ARCO
-- =====================================================
-- Este script crea el sistema de permisos granulares
-- que permite controlar el acceso a cada módulo del sistema

-- =====================================================
-- 1. TABLA DE MÓDULOS DEL SISTEMA
-- =====================================================
CREATE TABLE IF NOT EXISTS `modulos` (
  `id_modulo` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `icono` VARCHAR(50) NULL,
  `ruta` VARCHAR(100) NULL,
  `orden` INT DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Insertar módulos del sistema
INSERT INTO `modulos` (`nombre`, `descripcion`, `icono`, `ruta`, `orden`, `activo`) VALUES
('dashboard', 'Panel de Control', 'fa-tachometer-alt', 'dashboard.php', 1, 1),
('productos', 'Gestión de Productos', 'fa-box', 'productos.php', 2, 1),
('categorias', 'Gestión de Categorías', 'fa-tags', 'categorias.php', 3, 1),
('movimientos', 'Movimientos de Inventario', 'fa-exchange-alt', 'movimientos.php', 4, 1),
('usuarios', 'Gestión de Usuarios', 'fa-users', 'gestion_usuarios.php', 5, 1),
('reportes', 'Reportes y Estadísticas', 'fa-chart-bar', 'reportes.php', 6, 1),
('configuracion', 'Configuración del Sistema', 'fa-cog', 'configuracion.php', 7, 1);

-- =====================================================
-- 2. TABLA DE PERMISOS (ACCIONES)
-- =====================================================
CREATE TABLE IF NOT EXISTS `permisos` (
  `id_permiso` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `codigo` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Insertar permisos básicos
INSERT INTO `permisos` (`nombre`, `descripcion`, `codigo`) VALUES
('Ver', 'Puede ver el módulo', 'ver'),
('Crear', 'Puede crear nuevos registros', 'crear'),
('Editar', 'Puede editar registros existentes', 'editar'),
('Eliminar', 'Puede eliminar registros', 'eliminar'),
('Exportar',Y (`id`),
  UNIQUE KEY `rol_modulo_permiso` (`rol`, `id_modulo`, `id_permiso`),
  FOREIGN KEY (`id_modulo`) REFERENCES `modulos`(`id_modulo`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id_permiso`) ON DELETE CASCADE,
  INDEX `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- 5. Tabla de auditoría de permisos
CREATE TABLE IF NOT EXISTS `auditoria_permisos` (
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
  INDEX `idx_fecha` (`fecha_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- =====================================================
-- INSERTAR MÓDULOS DEL SISTEMA
-- =====================================================

INSERT INTO `modulos` (`nombre_modulo`, `descripcion`, `icono`, `ruta`, `orden`) VALUES
('dashboard', 'Panel de Control', 'fa-tachometer-alt', 'dashboard.php', 1),
('productos', 'Gestión de Productos', 'fa-box', 'productos.php', 2),
('categorias', 'Gestión de Categorías', 'fa-tags', 'categorias.php', 3),
('movimientos', 'Movimientos de Inventario', 'fa-exchange-alt', 'movimientos.php', 4),
('usuarios', 'Gestión de Usuarios', 'fa-users', 'gestion_usuarios.php', 5),
('reportes', 'Reportes y Estadísticas', 'fa-chart-bar', 'reportes.php', 6),
('configuracion', 'Configuración del Sistema', 'fa-cog', 'configuracion.php', 7),
('ordenes_compra', 'Órdenes de Compra', 'fa-shopping-cart', 'ordenes_compra.php', 8),
('devoluciones', 'Gestión de Devoluciones', 'fa-undo', 'devoluciones.php', 9),
('recepcion', 'Recepción de Materiales', 'fa-truck-loading', 'recepcion.php', 10);

-- =====================================================
-- INSERTAR PERMISOS/ACCIONES
-- =====================================================

INSERT INTO `permisos` (`nombre_permiso`, `descripcion`, `codigo`) VALUES
('ver', 'Ver/Consultar', 'view'),
('crear', 'Crear/Agregar', 'create'),
('editar', 'Editar/Modificar', 'edit'),
('eliminar', 'Eliminar', 'delete'),
('exportar', 'Exportar Datos', 'export'),
('importar', 'Importar Datos', 'import'),
('aprobar', 'Aprobar Operaciones', 'approve'),
('auditar', 'Ver Auditoría', 'audit');

-- =====================================================
-- ASIGNAR PERMISOS A MÓDULOS
-- =====================================================

-- Dashboard: Solo ver
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'dashboard' AND p.codigo = 'view';

-- Productos: Ver, Crear, Editar, Eliminar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'productos' AND p.codigo IN ('view', 'create', 'edit', 'delete', 'export');

-- Categorías: Ver, Crear, Editar, Eliminar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'categorias' AND p.codigo IN ('view', 'create', 'edit', 'delete');

-- Movimientos: Ver, Crear, Editar, Aprobar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'movimientos' AND p.codigo IN ('view', 'create', 'edit', 'approve', 'export');

-- Usuarios: Ver, Crear, Editar, Eliminar, Auditar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'usuarios' AND p.codigo IN ('view', 'create', 'edit', 'delete', 'audit');

-- Reportes: Ver, Crear, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'reportes' AND p.codigo IN ('view', 'create', 'export');

-- Configuración: Ver, Editar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'configuracion' AND p.codigo IN ('view', 'edit');

-- Órdenes de Compra: Ver, Crear, Editar, Aprobar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'ordenes_compra' AND p.codigo IN ('view', 'create', 'edit', 'approve', 'export');

-- Devoluciones: Ver, Crear, Editar, Aprobar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'devoluciones' AND p.codigo IN ('view', 'create', 'edit', 'approve');

-- Recepción: Ver, Crear, Editar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'recepcion' AND p.codigo IN ('view', 'create', 'edit');

-- =====================================================
-- ASIGNAR PERMISOS POR ROL
-- =====================================================

-- ========== ADMINISTRADOR: Acceso completo a todo ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'administrador', mp.id_modulo, mp.id_permiso
FROM modulo_permisos mp;

-- ========== GERENTE: Acceso completo excepto gestión de usuarios ==========
-- Dashboard
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre_modulo = 'dashboard' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Productos: Ver, Crear, Editar, Exportar (sin eliminar)
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'productos' AND p.codigo IN ('view', 'create', 'edit', 'export');

-- Categorías: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'categorias' AND p.codigo IN ('view', 'create', 'edit');

-- Movimientos: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre_modulo = 'movimientos' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Usuarios: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'usuarios' AND p.codigo = 'view';

-- Reportes: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre_modulo = 'reportes' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Configuración: Ver y Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'configuracion' AND p.codigo IN ('view', 'edit');

-- Órdenes de Compra: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre_modulo = 'ordenes_compra' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Devoluciones: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre_modulo = 'devoluciones' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- Recepción: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'gerente', m.id_modulo, p.id_permiso
FROM modulos m, permisos p, modulo_permisos mp
WHERE m.nombre_modulo = 'recepcion' 
  AND mp.id_modulo = m.id_modulo 
  AND mp.id_permiso = p.id_permiso;

-- ========== SUPERVISOR: Supervisión y aprobación ==========
-- Dashboard
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'dashboard' AND p.codigo = 'view';

-- Productos: Ver y Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'productos' AND p.codigo IN ('view', 'export');

-- Categorías: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'categorias' AND p.codigo = 'view';

-- Movimientos: Ver, Aprobar, Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'movimientos' AND p.codigo IN ('view', 'approve', 'export');

-- Reportes: Ver y Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'reportes' AND p.codigo IN ('view', 'export');

-- Órdenes de Compra: Ver y Aprobar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'ordenes_compra' AND p.codigo IN ('view', 'approve');

-- Devoluciones: Ver y Aprobar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'devoluciones' AND p.codigo IN ('view', 'approve');

-- Recepción: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'supervisor', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'recepcion' AND p.codigo = 'view';

-- ========== ALMACENISTA: Gestión de inventario ==========
-- Dashboard
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'dashboard' AND p.codigo = 'view';

-- Productos: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'productos' AND p.codigo IN ('view', 'create', 'edit');

-- Categorías: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'categorias' AND p.codigo = 'view';

-- Movimientos: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'movimientos' AND p.codigo IN ('view', 'create', 'edit');

-- Reportes: Ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'reportes' AND p.codigo = 'view';

-- Recepción: Ver, Crear, Editar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'recepcion' AND p.codigo IN ('view', 'create', 'edit');

-- Devoluciones: Ver, Crear
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'almacenista', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'devoluciones' AND p.codigo IN ('view', 'create');

-- ========== USUARIO: Acceso básico de consulta ==========
-- Dashboard
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'usuario', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'dashboard' AND p.codigo = 'view';

-- Productos: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'usuario', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'productos' AND p.codigo = 'view';

-- Categorías: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'usuario', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'categorias' AND p.codigo = 'view';

-- Movimientos: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'usuario', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'movimientos' AND p.codigo = 'view';

-- Reportes: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`)
SELECT 'usuario', m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre_modulo = 'reportes' AND p.codigo = 'view';

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Ver módulos creados
SELECT * FROM modulos ORDER BY orden;

-- Ver permisos creados
SELECT * FROM permisos;

-- Ver permisos por rol (Administrador)
SELECT 
    rp.rol,
    m.nombre_modulo,
    p.nombre_permiso,
    rp.activo
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador'
ORDER BY m.orden, p.nombre_permiso;

COMMIT;
