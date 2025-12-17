<?php
/**
 * Servicio de Verificación de Permisos
 * Verifica si un usuario tiene permiso para realizar una acción en un módulo
 */

require_once 'conexion.php';

/**
 * Verifica si un rol tiene un permiso específico en un módulo
 * 
 * @param string $rol Rol del usuario
 * @param string $modulo Nombre del módulo
 * @param string $permiso Código del permiso (ver, crear, editar, eliminar, etc.)
 * @return bool True si tiene permiso, False si no
 */
function tienePermiso($rol, $modulo, $permiso) {
    $conn = ConectarDB();
    
    $sql = "SELECT COUNT(*) as tiene_permiso
            FROM rol_permisos rp
            JOIN modulos m ON rp.id_modulo = m.id_modulo
            JOIN permisos p ON rp.id_permiso = p.id_permiso
            WHERE rp.rol = ? 
              AND m.nombre = ? 
              AND p.codigo = ?
              AND rp.activo = 1
              AND m.activo = 1
              AND p.activo = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $rol, $modulo, $permiso);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    return $fila['tiene_permiso'] > 0;
}

/**
 * Obtiene todos los permisos de un rol para un módulo
 * 
 * @param string $rol Rol del usuario
 * @param string $modulo Nombre del módulo
 * @return array Array de códigos de permisos
 */
function obtenerPermisosModulo($rol, $modulo) {
    $conn = ConectarDB();
    
    $sql = "SELECT p.codigo
            FROM rol_permisos rp
            JOIN modulos m ON rp.id_modulo = m.id_modulo
            JOIN permisos p ON rp.id_permiso = p.id_permiso
            WHERE rp.rol = ? 
              AND m.nombre = ?
              AND rp.activo = 1
              AND m.activo = 1
              AND p.activo = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $rol, $modulo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $permisos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $permisos[] = $fila['codigo'];
    }
    
    $stmt->close();
    $conn->close();
    
    return $permisos;
}

/**
 * Obtiene todos los módulos a los que un rol tiene acceso
 * 
 * @param string $rol Rol del usuario
 * @return array Array de módulos con sus permisos
 */
function obtenerModulosAccesibles($rol) {
    $conn = ConectarDB();
    
    $sql = "SELECT DISTINCT 
                m.id_modulo,
                m.nombre,
                m.descripcion,
                m.icono,
                m.ruta,
                m.orden
            FROM rol_permisos rp
            JOIN modulos m ON rp.id_modulo = m.id_modulo
            WHERE rp.rol = ?
              AND rp.activo = 1
              AND m.activo = 1
            ORDER BY m.orden";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $modulos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $modulos[] = [
            'id' => $fila['id_modulo'],
            'nombre' => $fila['nombre'],
            'descripcion' => $fila['descripcion'],
            'icono' => $fila['icono'],
            'ruta' => $fila['ruta'],
            'orden' => $fila['orden'],
            'permisos' => obtenerPermisosModulo($rol, $fila['nombre'])
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    return $modulos;
}

/**
 * Verifica si un usuario puede acceder a un módulo
 * 
 * @param string $rol Rol del usuario
 * @param string $modulo Nombre del módulo
 * @return bool True si puede acceder, False si no
 */
function puedeAccederModulo($rol, $modulo) {
    return tienePermiso($rol, $modulo, 'ver');
}

/**
 * Middleware para verificar permisos
 * Redirige si no tiene permiso
 * 
 * @param string $modulo Nombre del módulo
 * @param string $permiso Código del permiso requerido
 */
function requierePermiso($modulo, $permiso = 'ver') {
    session_start();
    
    if (!isset($_SESSION['rol'])) {
        header("Location: ../login.html?error=Debe iniciar sesión");
        exit;
    }
    
    if (!tienePermiso($_SESSION['rol'], $modulo, $permiso)) {
        header("Location: dashboard.php?error=No tiene permisos para realizar esta acción");
        exit;
    }
}

/**
 * Obtiene matriz de permisos para un rol (para debugging)
 * 
 * @param string $rol Rol del usuario
 * @return array Matriz de permisos
 */
function obtenerMatrizPermisos($rol) {
    $conn = ConectarDB();
    
    $sql = "SELECT 
                m.id_modulo,
                m.nombre AS modulo,
                p.id_permiso,
                p.codigo AS permiso,
                p.nombre AS permiso_nombre,
                rp.activo
            FROM rol_permisos rp
            JOIN modulos m ON rp.id_modulo = m.id_modulo
            JOIN permisos p ON rp.id_permiso = p.id_permiso
            WHERE rp.rol = ?
            ORDER BY m.orden, p.nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $matriz = [];
    while ($fila = $resultado->fetch_assoc()) {
        if (!isset($matriz[$fila['modulo']])) {
            $matriz[$fila['modulo']] = [];
        }
        $matriz[$fila['modulo']][$fila['permiso']] = [
            'nombre' => $fila['permiso_nombre'],
            'activo' => $fila['activo'] == 1,
            'id_modulo' => $fila['id_modulo'],
            'id_permiso' => $fila['id_permiso']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    return $matriz;
}
?>
