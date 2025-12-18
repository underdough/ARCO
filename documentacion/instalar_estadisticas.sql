-- ============================================
-- Script de Instalación: Módulo de Estadísticas
-- Sistema ARCO - Gestión de Inventario
-- ============================================

-- Verificar si el módulo ya existe
SELECT 'Verificando si el módulo estadísticas ya existe...' as mensaje;
SELECT COUNT(*) as existe FROM modulos WHERE nombre = 'estadisticas';

-- Insertar módulo de estadísticas
INSERT INTO modulos (nombre, descripcion, icono, ruta, orden, activo) 
VALUES ('estadisticas', 'Estadísticas', 'fa-chart-line', 'estadisticas.php', 7, 1)
ON DUPLICATE KEY UPDATE 
    descripcion = 'Estadísticas',
    icono = 'fa-chart-line',
    ruta = 'estadisticas.php',
    orden = 7,
    activo = 1;

SELECT 'Módulo de estadísticas creado/actualizado' as mensaje;

-- Obtener el ID del módulo
SET @modulo_id = (SELECT id_modulo FROM modulos WHERE nombre = 'estadisticas' LIMIT 1);

-- Obtener IDs de permisos
SET @permiso_ver = (SELECT id_permiso FROM permisos WHERE codigo = 'ver' LIMIT 1);
SET @permiso_exportar = (SELECT id_permiso FROM permisos WHERE codigo = 'exportar' LIMIT 1);

-- Verificar que los permisos existen
SELECT 'Verificando permisos...' as mensaje;
SELECT @permiso_ver as permiso_ver_id, @permiso_exportar as permiso_exportar_id;

-- Asignar permisos a Administrador
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
VALUES 
('administrador', @modulo_id, @permiso_ver, 1),
('administrador', @modulo_id, @permiso_exportar, 1)
ON DUPLICATE KEY UPDATE activo = 1;

SELECT 'Permisos asignados a Administrador' as mensaje;

-- Asignar permisos a Gerente
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
VALUES 
('gerente', @modulo_id, @permiso_ver, 1),
('gerente', @modulo_id, @permiso_exportar, 1)
ON DUPLICATE KEY UPDATE activo = 1;

SELECT 'Permisos asignados a Gerente' as mensaje;

-- Asignar permisos a Supervisor
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
VALUES 
('supervisor', @modulo_id, @permiso_ver, 1),
('supervisor', @modulo_id, @permiso_exportar, 1)
ON DUPLICATE KEY UPDATE activo = 1;

SELECT 'Permisos asignados a Supervisor' as mensaje;

-- Verificar la instalación
SELECT '============================================' as mensaje;
SELECT 'VERIFICACIÓN DE INSTALACIÓN' as mensaje;
SELECT '============================================' as mensaje;

-- Mostrar el módulo creado
SELECT 'Módulo creado:' as mensaje;
SELECT id_modulo, nombre, descripcion, icono, ruta, orden, activo 
FROM modulos 
WHERE nombre = 'estadisticas';

-- Mostrar permisos asignados
SELECT '============================================' as mensaje;
SELECT 'Permisos asignados:' as mensaje;
SELECT 
    rp.rol,
    m.nombre as modulo,
    p.nombre as permiso,
    p.codigo as codigo_permiso,
    rp.activo
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE m.nombre = 'estadisticas'
ORDER BY rp.rol, p.codigo;

SELECT '============================================' as mensaje;
SELECT 'INSTALACIÓN COMPLETADA' as mensaje;
SELECT '============================================' as mensaje;
SELECT 'El módulo de estadísticas está listo para usar.' as mensaje;
SELECT 'Acceso permitido para: Administrador, Gerente, Supervisor' as mensaje;
SELECT 'Ruta: vistas/estadisticas.php' as mensaje;
