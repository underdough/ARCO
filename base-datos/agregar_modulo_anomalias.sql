-- =====================================================
-- AGREGAR MÓDULO: ANOMALÍAS Y NOVEDADES
-- =====================================================
-- Descripción: Módulo para reportar anomalías y novedades
--              Todos los roles excepto "usuario" pueden crear

-- =====================================================
-- 1. INSERTAR MÓDULO
-- =====================================================
INSERT INTO `modulos` (`nombre`, `descripcion`, `icono`, `ruta`, `orden`, `activo`) 
VALUES ('anomalias_novedades', 'Anomalías y Novedades', 'fa-exclamation-triangle', 'anomalias_novedades.php', 11, 1);

-- Obtener el ID del módulo recién insertado
SET @id_modulo_anomalias = LAST_INSERT_ID();

-- =====================================================
-- 2. ASIGNAR PERMISOS DISPONIBLES AL MÓDULO
-- =====================================================
-- Anomalías y Novedades: Ver, Crear, Editar, Eliminar, Exportar

INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT @id_modulo_anomalias, p.id_permiso
FROM permisos p
WHERE p.codigo IN ('ver', 'crear', 'editar', 'eliminar', 'exportar');

-- =====================================================
-- 3. ASIGNAR PERMISOS POR ROL
-- =====================================================

-- ========== ADMINISTRADOR: Todos los permisos ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'administrador', @id_modulo_anomalias, p.id_permiso, 1
FROM permisos p
WHERE p.codigo IN ('ver', 'crear', 'editar', 'eliminar', 'exportar');

-- ========== GERENTE: Ver, Crear, Editar, Exportar ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', @id_modulo_anomalias, p.id_permiso, 1
FROM permisos p
WHERE p.codigo IN ('ver', 'crear', 'editar', 'exportar');

-- ========== SUPERVISOR: Ver, Crear, Exportar ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', @id_modulo_anomalias, p.id_permiso, 1
FROM permisos p
WHERE p.codigo IN ('ver', 'crear', 'exportar');

-- ========== ALMACENISTA: Ver, Crear ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', @id_modulo_anomalias, p.id_permiso, 1
FROM permisos p
WHERE p.codigo IN ('ver', 'crear');

-- ========== USUARIO: Solo Ver (NO puede crear) ==========
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', @id_modulo_anomalias, p.id_permiso, 1
FROM permisos p
WHERE p.codigo = 'ver';

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Ver el módulo creado
SELECT * FROM modulos WHERE nombre = 'anomalias_novedades';

-- Ver permisos disponibles para el módulo
SELECT 
    m.nombre AS modulo,
    GROUP_CONCAT(p.nombre ORDER BY p.nombre SEPARATOR ', ') AS permisos_disponibles
FROM modulo_permisos mp
JOIN modulos m ON mp.id_modulo = m.id_modulo
JOIN permisos p ON mp.id_permiso = p.id_permiso
WHERE m.nombre = 'anomalias_novedades'
GROUP BY m.nombre;

-- Ver permisos por rol para Anomalías y Novedades
SELECT 
    rp.rol,
    m.nombre AS modulo,
    GROUP_CONCAT(p.codigo ORDER BY p.codigo SEPARATOR ', ') AS permisos
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE m.nombre = 'anomalias_novedades' AND rp.activo = 1
GROUP BY rp.rol, m.nombre
ORDER BY 
    CASE rp.rol
        WHEN 'administrador' THEN 1
        WHEN 'gerente' THEN 2
        WHEN 'supervisor' THEN 3
        WHEN 'almacenista' THEN 4
        WHEN 'usuario' THEN 5
    END;

-- =====================================================
-- RESUMEN DE PERMISOS POR ROL
-- =====================================================
/*
ADMINISTRADOR: Ver, Crear, Editar, Eliminar, Exportar (TODOS)
GERENTE:       Ver, Crear, Editar, Exportar
SUPERVISOR:    Ver, Crear, Exportar
ALMACENISTA:   Ver, Crear
USUARIO:       Ver (NO puede crear)
*/

COMMIT;
