-- =====================================================
-- SCRIPT DE VERIFICACIÓN DE INSTALACIÓN
-- Sistema de Gestión de Usuarios ARCO
-- =====================================================

-- Este script verifica que todas las tablas y columnas
-- necesarias estén correctamente instaladas

SELECT '=== VERIFICACIÓN DE INSTALACIÓN ===' as '';

-- 1. Verificar tabla usuarios
SELECT '1. Verificando tabla usuarios...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ Tabla usuarios existe'
        ELSE '❌ ERROR: Tabla usuarios no existe'
    END as resultado
FROM information_schema.tables 
WHERE table_schema = 'arco_bdd' 
AND table_name = 'usuarios';

-- 2. Verificar columnas de usuarios
SELECT '2. Verificando columnas de usuarios...' as '';

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    CASE 
        WHEN COLUMN_NAME IN ('id_usuarios', 'num_doc', 'nombre', 'apellido', 'rol', 'cargos', 'correo', 'contrasena', 'num_telefono', 'fecha_creacion', 'ultimo_conexion', 'estado', 'fecha_modificacion', 'modificado_por') 
        THEN '✅'
        ELSE '⚠️'
    END as estado
FROM information_schema.columns
WHERE table_schema = 'arco_bdd' 
AND table_name = 'usuarios'
ORDER BY ORDINAL_POSITION;

-- 3. Verificar que el campo rol tiene los valores correctos
SELECT '3. Verificando valores ENUM del campo rol...' as '';

SELECT 
    COLUMN_TYPE,
    CASE 
        WHEN COLUMN_TYPE LIKE '%administrador%' 
        AND COLUMN_TYPE LIKE '%usuario%'
        AND COLUMN_TYPE LIKE '%almacenista%'
        AND COLUMN_TYPE LIKE '%supervisor%'
        AND COLUMN_TYPE LIKE '%gerente%'
        THEN '✅ Todos los roles están configurados'
        ELSE '❌ ERROR: Faltan roles en ENUM'
    END as resultado
FROM information_schema.columns
WHERE table_schema = 'arco_bdd' 
AND table_name = 'usuarios'
AND column_name = 'rol';

-- 4. Verificar que el campo estado tiene los valores correctos
SELECT '4. Verificando valores ENUM del campo estado...' as '';

SELECT 
    COLUMN_TYPE,
    CASE 
        WHEN COLUMN_TYPE LIKE '%ACTIVO%' 
        AND COLUMN_TYPE LIKE '%INACTIVO%'
        AND COLUMN_TYPE LIKE '%SUSPENDIDO%'
        THEN '✅ Todos los estados están configurados'
        ELSE '❌ ERROR: Faltan estados en ENUM'
    END as resultado
FROM information_schema.columns
WHERE table_schema = 'arco_bdd' 
AND table_name = 'usuarios'
AND column_name = 'estado';

-- 5. Verificar tabla auditoria_usuarios
SELECT '5. Verificando tabla auditoria_usuarios...' as '';

SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ Tabla auditoria_usuarios existe'
        ELSE '❌ ERROR: Tabla auditoria_usuarios no existe'
    END as resultado
FROM information_schema.tables 
WHERE table_schema = 'arco_bdd' 
AND table_name = 'auditoria_usuarios';

-- 6. Verificar columnas de auditoria_usuarios
SELECT '6. Verificando columnas de auditoria_usuarios...' as '';

SELECT 
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    CASE 
        WHEN COLUMN_NAME IN ('id_auditoria', 'usuario_id', 'accion', 'campo_modificado', 'valor_anterior', 'valor_nuevo', 'realizado_por', 'fecha_accion', 'ip_address') 
        THEN '✅'
        ELSE '⚠️'
    END as estado
FROM information_schema.columns
WHERE table_schema = 'arco_bdd' 
AND table_name = 'auditoria_usuarios'
ORDER BY ORDINAL_POSITION;

-- 7. Verificar índices
SELECT '7. Verificando índices...' as '';

SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    '✅' as estado
FROM information_schema.statistics
WHERE table_schema = 'arco_bdd' 
AND table_name IN ('usuarios', 'auditoria_usuarios')
ORDER BY TABLE_NAME, INDEX_NAME;

-- 8. Verificar que existe al menos un administrador
SELECT '8. Verificando usuarios administradores...' as '';

SELECT 
    COUNT(*) as total_admins,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ Existe al menos un administrador'
        ELSE '❌ ERROR: No hay administradores en el sistema'
    END as resultado
FROM usuarios 
WHERE rol = 'administrador';

-- 9. Mostrar usuarios administradores
SELECT '9. Listando usuarios administradores...' as '';

SELECT 
    id_usuarios,
    num_doc,
    nombre,
    apellido,
    correo,
    rol,
    estado,
    fecha_creacion
FROM usuarios 
WHERE rol = 'administrador';

-- 10. Verificar distribución de roles
SELECT '10. Distribución de roles en el sistema...' as '';

SELECT 
    rol,
    COUNT(*) as cantidad,
    CONCAT(ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM usuarios), 2), '%') as porcentaje
FROM usuarios
GROUP BY rol
ORDER BY cantidad DESC;

-- 11. Verificar distribución de estados
SELECT '11. Distribución de estados en el sistema...' as '';

SELECT 
    estado,
    COUNT(*) as cantidad,
    CONCAT(ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM usuarios), 2), '%') as porcentaje
FROM usuarios
GROUP BY estado
ORDER BY cantidad DESC;

-- 12. Verificar registros de auditoría
SELECT '12. Verificando registros de auditoría...' as '';

SELECT 
    COUNT(*) as total_registros,
    CASE 
        WHEN COUNT(*) > 0 THEN '✅ Existen registros de auditoría'
        ELSE '⚠️ No hay registros de auditoría aún (normal en instalación nueva)'
    END as resultado
FROM auditoria_usuarios;

-- 13. Últimas acciones de auditoría (si existen)
SELECT '13. Últimas 5 acciones de auditoría...' as '';

SELECT 
    a.fecha_accion,
    a.accion,
    CONCAT(u.nombre, ' ', u.apellido) as usuario_afectado,
    CONCAT(admin.nombre, ' ', admin.apellido) as realizado_por
FROM auditoria_usuarios a
LEFT JOIN usuarios u ON a.usuario_id = u.id_usuarios
LEFT JOIN usuarios admin ON a.realizado_por = admin.id_usuarios
ORDER BY a.fecha_accion DESC
LIMIT 5;

-- 14. Resumen final
SELECT '=== RESUMEN DE VERIFICACIÓN ===' as '';

SELECT 
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'arco_bdd' AND table_name = 'usuarios') as tabla_usuarios,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'arco_bdd' AND table_name = 'auditoria_usuarios') as tabla_auditoria,
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador') as total_admins,
    (SELECT COUNT(*) FROM usuarios WHERE estado = 'ACTIVO') as usuarios_activos,
    (SELECT COUNT(*) FROM auditoria_usuarios) as registros_auditoria;

-- 15. Verificación de integridad
SELECT '15. Verificación de integridad...' as '';

SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'arco_bdd' AND table_name = 'usuarios') = 1
        AND (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'arco_bdd' AND table_name = 'auditoria_usuarios') = 1
        AND (SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador') > 0
        THEN '✅✅✅ INSTALACIÓN CORRECTA - Sistema listo para usar'
        ELSE '❌ ERROR: Revisar mensajes anteriores'
    END as resultado_final;

-- Fin del script
SELECT '=== FIN DE VERIFICACIÓN ===' as '';
