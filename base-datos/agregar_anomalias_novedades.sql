-- =====================================================
-- AGREGAR MÓDULO DE ANOMALÍAS Y NOVEDADES
-- =====================================================
-- Script para agregar el módulo de Anomalías y Novedades
-- al sistema de permisos existente

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- 1. VERIFICAR SI YA EXISTE EL MÓDULO
-- =====================================================
SELECT 'Verificando si existe el módulo...' AS paso;

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '⚠ El módulo ya existe'
        ELSE '✓ Módulo no existe, se puede agregar'
    END AS estado
FROM modulos
WHERE nombre = 'anomalias_novedades';

-- =====================================================
-- 2. AGREGAR MÓDULO (si no existe)
-- =====================================================
INSERT INTO `modulos` (`nombre`, `descripcion`, `icono`, `ruta`, `orden`, `activo`)
SELECT 'anomalias_novedades', 'Anomalías y Novedades', 'fa-exclamation-triangle', 'anomalias_novedades.php', 11, 1
WHERE NOT EXISTS (SELECT 1 FROM modulos WHERE nombre = 'anomalias_novedades');

-- =====================================================
-- 3. AGREGAR PERMISOS DISPONIBLES PARA EL MÓDULO
-- =====================================================
SELECT 'Agregando permisos disponibles...' AS paso;

-- Anomalías y Novedades: Ver, Crear, Editar, Eliminar, Exportar
INSERT INTO `modulo_permisos` (`id_modulo`, `id_permiso`)
SELECT m.id_modulo, p.id_permiso
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' 
  AND p.codigo IN ('ver', 'crear', 'editar', 'eliminar', 'exportar')
  AND NOT EXISTS (
    SELECT 1 FROM modulo_permisos mp 
    WHERE mp.id_modulo = m.id_modulo 
      AND mp.id_permiso = p.id_permiso
  );

-- =====================================================
-- 4. ASIGNAR PERMISOS A ADMINISTRADOR
-- =====================================================
SELECT 'Asignando permisos a Administrador...' AS paso;

-- Administrador: Todos los permisos
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'administrador', mp.id_modulo, mp.id_permiso, 1
FROM modulo_permisos mp
JOIN modulos m ON mp.id_modulo = m.id_modulo
WHERE m.nombre = 'anomalias_novedades'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp 
    WHERE rp.rol = 'administrador' 
      AND rp.id_modulo = mp.id_modulo 
      AND rp.id_permiso = mp.id_permiso
  );

-- =====================================================
-- 5. ASIGNAR PERMISOS A GERENTE
-- =====================================================
SELECT 'Asignando permisos a Gerente...' AS paso;

-- Gerente: Ver, Crear, Editar, Exportar (sin eliminar)
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'gerente', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' 
  AND p.codigo IN ('ver', 'crear', 'editar', 'exportar')
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp 
    WHERE rp.rol = 'gerente' 
      AND rp.id_modulo = m.id_modulo 
      AND rp.id_permiso = p.id_permiso
  );

-- =====================================================
-- 6. ASIGNAR PERMISOS A SUPERVISOR
-- =====================================================
SELECT 'Asignando permisos a Supervisor...' AS paso;

-- Supervisor: Ver, Crear, Exportar
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'supervisor', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' 
  AND p.codigo IN ('ver', 'crear', 'exportar')
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp 
    WHERE rp.rol = 'supervisor' 
      AND rp.id_modulo = m.id_modulo 
      AND rp.id_permiso = p.id_permiso
  );

-- =====================================================
-- 7. ASIGNAR PERMISOS A ALMACENISTA
-- =====================================================
SELECT 'Asignando permisos a Almacenista...' AS paso;

-- Almacenista: Ver, Crear
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'almacenista', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' 
  AND p.codigo IN ('ver', 'crear')
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp 
    WHERE rp.rol = 'almacenista' 
      AND rp.id_modulo = m.id_modulo 
      AND rp.id_permiso = p.id_permiso
  );

-- =====================================================
-- 8. ASIGNAR PERMISOS A USUARIO
-- =====================================================
SELECT 'Asignando permisos a Usuario...' AS paso;

-- Usuario: Solo ver
INSERT INTO `rol_permisos` (`rol`, `id_modulo`, `id_permiso`, `activo`)
SELECT 'usuario', m.id_modulo, p.id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'anomalias_novedades' 
  AND p.codigo = 'ver'
  AND NOT EXISTS (
    SELECT 1 FROM rol_permisos rp 
    WHERE rp.rol = 'usuario' 
      AND rp.id_modulo = m.id_modulo 
      AND rp.id_permiso = p.id_permiso
  );

-- =====================================================
-- 9. VERIFICACIÓN FINAL
-- =====================================================
SELECT '' AS separador;
SELECT '═══════════════════════════════════════════' AS verificacion;
SELECT 'VERIFICACIÓN FINAL' AS verificacion;
SELECT '═══════════════════════════════════════════' AS verificacion;

-- Verificar módulo
SELECT 
    'Módulo agregado' AS verificacion,
    COUNT(*) AS cantidad,
    CASE 
        WHEN COUNT(*) = 1 THEN '✓ OK'
        ELSE '✗ ERROR'
    END AS estado
FROM modulos
WHERE nombre = 'anomalias_novedades';

-- Verificar permisos disponibles
SELECT 
    'Permisos disponibles' AS verificacion,
    COUNT(*) AS cantidad,
    CASE 
        WHEN COUNT(*) = 5 THEN '✓ OK (ver, crear, editar, eliminar, exportar)'
        ELSE '✗ FALTAN PERMISOS'
    END AS estado
FROM modulo_permisos mp
JOIN modulos m ON mp.id_modulo = m.id_modulo
WHERE m.nombre = 'anomalias_novedades';

-- Verificar permisos por rol
SELECT 
    rol,
    COUNT(*) AS permisos_asignados,
    GROUP_CONCAT(p.codigo ORDER BY p.codigo SEPARATOR ', ') AS permisos
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE m.nombre = 'anomalias_novedades'
GROUP BY rol
ORDER BY 
    CASE rol
        WHEN 'administrador' THEN 1
        WHEN 'gerente' THEN 2
        WHEN 'supervisor' THEN 3
        WHEN 'almacenista' THEN 4
        WHEN 'usuario' THEN 5
    END;

SELECT '═══════════════════════════════════════════' AS verificacion;
SELECT '✓✓✓ MÓDULO AGREGADO EXITOSAMENTE ✓✓✓' AS verificacion;
SELECT '═══════════════════════════════════════════' AS verificacion;

COMMIT;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
