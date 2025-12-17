<?php
/**
 * Middleware de Permisos
 * Protege las vistas y verifica permisos del usuario
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión");
    exit;
}

// Incluir funciones de verificación de permisos
require_once __DIR__ . '/verificar_permisos.php';

/**
 * Obtiene el rol del usuario actual
 */
function obtenerRolActual() {
    return $_SESSION['rol'] ?? 'usuario';
}

/**
 * Verifica si el usuario puede acceder al módulo actual
 * @param string $modulo Nombre del módulo
 */
function verificarAccesoModulo($modulo) {
    $rol = obtenerRolActual();
    
    if (!puedeAccederModulo($rol, $modulo)) {
        header("Location: dashboard.php?error=No tiene permisos para acceder a este módulo");
        exit;
    }
}

/**
 * Obtiene los permisos del usuario para el módulo actual
 * @param string $modulo Nombre del módulo
 * @return array Array de permisos
 */
function obtenerPermisosUsuario($modulo) {
    $rol = obtenerRolActual();
    return obtenerPermisosModulo($rol, $modulo);
}

/**
 * Verifica si el usuario tiene un permiso específico
 * @param string $modulo Nombre del módulo
 * @param string $permiso Código del permiso
 * @return bool
 */
function usuarioTienePermiso($modulo, $permiso) {
    $rol = obtenerRolActual();
    return tienePermiso($rol, $modulo, $permiso);
}

/**
 * Genera atributos HTML para mostrar/ocultar elementos según permisos
 * @param string $modulo Nombre del módulo
 * @param string $permiso Código del permiso requerido
 * @return string Atributos HTML
 */
function mostrarSiTienePermiso($modulo, $permiso) {
    if (!usuarioTienePermiso($modulo, $permiso)) {
        return 'style="display: none;"';
    }
    return '';
}

/**
 * Genera clase CSS para deshabilitar elementos según permisos
 * @param string $modulo Nombre del módulo
 * @param string $permiso Código del permiso requerido
 * @return string Clase CSS
 */
function deshabilitarSiNoTienePermiso($modulo, $permiso) {
    if (!usuarioTienePermiso($modulo, $permiso)) {
        return 'disabled';
    }
    return '';
}

/**
 * Obtiene información del usuario actual
 * @return array Información del usuario
 */
function obtenerInfoUsuario() {
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? '',
        'apellido' => $_SESSION['apellido'] ?? '',
        'nombre_completo' => ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''),
        'correo' => $_SESSION['correo'] ?? '',
        'rol' => $_SESSION['rol'] ?? 'usuario',
        'documento' => $_SESSION['documento'] ?? ''
    ];
}

/**
 * Genera JSON con permisos del usuario para JavaScript
 * @param string $modulo Nombre del módulo
 * @return string JSON con permisos
 */
function generarPermisosJS($modulo) {
    $permisos = obtenerPermisosUsuario($modulo);
    $permisosObj = [];
    
    foreach ($permisos as $permiso) {
        $permisosObj[$permiso] = true;
    }
    
    return json_encode($permisosObj);
}
?>
