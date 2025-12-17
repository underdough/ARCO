-- =====================================================
-- SCRIPT DE VERIFICACIÓN: EDICIÓN DE PERMISOS
-- =====================================================
-- Ejecutar este script para verificar que el sistema
-- de edición de permisos está funcionando correctamente

-- =====================================================
-- 1. VERIFICAR TABLAS NECESARIAS
-- =====================================================
SELECT 'VERIFICANDO TABLAS...' AS paso;

SELECT 
    'modulos' AS tabla,
    COUNT(*) AS registros,
    CASE 
        WHEN COUNT(*) >= 11 THEN '✓ OK'
        ELSE '✗ FALTAN DATOS'
    END AS estado
FROM modulos

UNION ALL

SELECT 
    'permisos' AS tabla,
    COUNT(*) AS registros,
    CASE 
        WHEN COUNT(*) >= 8 THEN '✓ OK'
        ELSE '✗ FALTAN DATOS'
    END AS estado
FROM permisos

UNION ALL

SELECT 
    'modulo_permisos' AS tabla,
    COUNT(*) AS registros,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ OK'
        ELSE '✗ VACÍA - Ejecutar insertar_permisos_directamente.php'
    END AS estado
FROM modulo_permisos

UNION ALL

SELECT 
    'rol_permisos' AS tabla,
    COUNT(*) AS registros,
    CASE 
        WHEN COUNT(*) > 0 THEN '✓ OK'
        ELSE '✗ VACÍA - Ejecutar insertar_permisos_directamente.php'
    END AS estado
FROM rol_permisos

UNION ALL

SELECT 
    'auditoria_permisos' AS tabla,
    COUNT(*) AS registros,
    CASE 
        WHEN COUNT(*) >= 0 THEN '✓ OK (puede estar vacía al inicio)'
        ELSE '✗ ERROR'
    END AS estado
FROM auditoria_permisos;

-- =====================================================
-- 2. VERIFICAR PERMISOS POR ROL
-- =====================================================
SELECT '' AS separador;
SELECT 'PERMISOS POR ROL...' AS paso;

SELECT 
    rol,
    COUNT(DISTINCT id_modulo) AS modulos_con_acceso,
    COUNT(*) AS total_permisos,
    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) AS permisos_activos,
    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) AS permisos_inactivos
FROM rol_permisos
GROUP BY rol
ORDER BY 
    CASE rol
        WHEN 'administrador' THEN 1
        WHEN 'gerente' THEN 2
        WHEN 'supervisor' THEN 3
        WHEN 'almacenista' THEN 4
        WHEN 'usuario' THEN 5
    END;

-- =====================================================
-- 3. VERIFICAR PERMISOS DEL ADMINISTRADOR
-- =====================================================
SELECT '' AS separador;
SELECT 'PERMISOS DEL ADMINISTRADOR...' AS paso;

SELECT 
    m.nombre AS modulo,
    GROUP_CONCAT(p.codigo ORDER BY p.codigo SEPARATOR ', ') AS permisos
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador' AND rp.activo = 1
GROUP BY m.nombre
ORDER BY m.orden;

-- =====================================================
-- 4. VERIFICAR MÓDULOS DISPONIBLES
-- =====================================================
SELECT '' AS separador;
SELECT 'MÓDULOS DEL SISTEMA...' AS paso;

SELECT 
    id_modulo,
    nombre,
    descripcion,
    icono,
    orden,
    CASE WHEN activo = 1 THEN '✓ Activo' ELSE '✗ Inactivo' END AS estado
FROM modulos
ORDER BY orden;

-- =====================================================
-- 5. VERIFICAR PERMISOS DISPONIBLES
-- =====================================================
SELECT '' AS separador;
SELECT 'PERMISOS DISPONIBLES...' AS paso;

SELECT 
    id_permiso,
    nombre,
    codigo,
    descripcion,
    CASE WHEN activo = 1 THEN '✓ Activo' ELSE '✗ Inactivo' END AS estado
FROM permisos
ORDER BY id_permiso;

-- =====================================================
-- 6. VERIFICAR RELACIÓN MÓDULO-PERMISOS
-- =====================================================
SELECT '' AS separador;
SELECT 'PERMISOS POR MÓDULO...' AS paso;

SELECT 
    m.nombre AS modulo,
    COUNT(*) AS permisos_disponibles,
    GROUP_CONCAT(p.codigo ORDER BY p.codigo SEPARATOR ', ') AS permisos
FROM modulo_permisos mp
JOIN modulos m ON mp.id_modulo = m.id_modulo
JOIN permisos p ON mp.id_permiso = p.id_permiso
GROUP BY m.nombre
ORDER BY m.orden;

-- =====================================================
-- 7. VERIFICAR AUDITORÍA (ÚLTIMOS 10 CAMBIOS)
-- =====================================================
SELECT '' AS separador;
SELECT 'ÚLTIMOS CAMBIOS EN AUDITORÍA...' AS paso;

SELECT 
    DATE_FORMAT(fecha_accion, '%Y-%m-%d %H:%i:%s') AS fecha,
    rol,
    accion,
    CONCAT(
        (SELECT nombre FROM modulos WHERE id_modulo = ap.id_modulo),
        ' - ',
        (SELECT codigo FROM permisos WHERE id_permiso = ap.id_permiso)
    ) AS permiso_modificado,
    valor_anterior,
    valor_nuevo,
    realizado_por AS usuario_id,
    ip_address
FROM auditoria_permisos ap
ORDER BY fecha_accion DESC
LIMIT 10;

-- =====================================================
-- 8. COMPARAR PERMISOS ENTRE ROLES
-- =====================================================
SELECT '' AS separador;
SELECT 'COMPARACIÓN DE PERMISOS ENTRE ROLES...' AS paso;

SELECT 
    m.nombre AS modulo,
    MAX(CASE WHEN rp.rol = 'administrador' AND rp.activo = 1 THEN '✓' ELSE '✗' END) AS admin,
    MAX(CASE WHEN rp.rol = 'gerente' AND rp.activo = 1 THEN '✓' ELSE '✗' END) AS gerente,
    MAX(CASE WHEN rp.rol = 'supervisor' AND rp.activo = 1 THEN '✓' ELSE '✗' END) AS supervisor,
    MAX(CASE WHEN rp.rol = 'almacenista' AND rp.activo = 1 THEN '✓' ELSE '✗' END) AS almacenista,
    MAX(CASE WHEN rp.rol = 'usuario' AND rp.activo = 1 THEN '✓' ELSE '✗' END) AS usuario
FROM modulos m
LEFT JOIN rol_permisos rp ON m.id_modulo = rp.id_modulo
GROUP BY m.nombre
ORDER BY m.orden;

-- =====================================================
-- 9. VERIFICAR INTEGRIDAD DE DATOS
-- =====================================================
SELECT '' AS separador;
SELECT 'VERIFICACIÓN DE INTEGRIDAD...' AS paso;

-- Verificar permisos huérfanos (sin módulo o permiso válido)
SELECT 
    'Permisos huérfanos' AS verificacion,
    COUNT(*) AS cantidad,
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ OK'
        ELSE '✗ HAY REGISTROS HUÉRFANOS'
    END AS estado
FROM rol_permisos rp
WHERE NOT EXISTS (SELECT 1 FROM modulos m WHERE m.id_modulo = rp.id_modulo)
   OR NOT EXISTS (SELECT 1 FROM permisos p WHERE p.id_permiso = rp.id_permiso)

UNION ALL

-- Verificar módulos sin permisos asignados
SELECT 
    'Módulos sin permisos' AS verificacion,
    COUNT(*) AS cantidad,
    CASE 
        WHEN COUNT(*) = 0 THEN '✓ OK'
        ELSE '⚠ HAY MÓDULOS SIN PERMISOS'
    END AS estado
FROM modulos m
WHERE NOT EXISTS (SELECT 1 FROM modulo_permisos mp WHERE mp.id_modulo = m.id_modulo)

UNION ALL

-- Verificar roles sin permisos
SELECT 
    'Roles sin permisos' AS verificacion,
    COUNT(DISTINCT rol) AS cantidad,
    CASE 
        WHEN COUNT(DISTINCT rol) = 5 THEN '✓ OK - Todos los roles tienen permisos'
        ELSE '⚠ FALTAN PERMISOS PARA ALGUNOS ROLES'
    END AS estado
FROM (
    SELECT 'administrador' AS rol
    UNION SELECT 'gerente'
    UNION SELECT 'supervisor'
    UNION SELECT 'almacenista'
    UNION SELECT 'usuario'
) roles
WHERE EXISTS (SELECT 1 FROM rol_permisos rp WHERE rp.rol = roles.rol);

-- =====================================================
-- 10. RESUMEN FINAL
-- =====================================================
SELECT '' AS separador;
SELECT '═══════════════════════════════════════════' AS resumen;
SELECT 'RESUMEN DE VERIFICACIÓN' AS resumen;
SELECT '═══════════════════════════════════════════' AS resumen;

SELECT 
    CONCAT('✓ Módulos: ', COUNT(*)) AS resultado
FROM modulos

UNION ALL

SELECT 
    CONCAT('✓ Permisos: ', COUNT(*)) AS resultado
FROM permisos

UNION ALL

SELECT 
    CONCAT('✓ Relaciones módulo-permiso: ', COUNT(*)) AS resultado
FROM modulo_permisos

UNION ALL

SELECT 
    CONCAT('✓ Permisos asignados a roles: ', COUNT(*)) AS resultado
FROM rol_permisos

UNION ALL

SELECT 
    CONCAT('✓ Registros de auditoría: ', COUNT(*)) AS resultado
FROM auditoria_permisos

UNION ALL

SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM rol_permisos) > 0 THEN '✓✓✓ SISTEMA OPERATIVO ✓✓✓'
        ELSE '✗✗✗ SISTEMA NO OPERATIVO - EJECUTAR INSTALACIÓN ✗✗✗'
    END AS resultado;

SELECT '═══════════════════════════════════════════' AS resumen;

-- =====================================================
-- FIN DE LA VERIFICACIÓN
-- =====================================================
