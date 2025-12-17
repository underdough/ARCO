<?php
/**
 * API para obtener permisos de un rol específico
 * Retorna JSON con todos los permisos del rol
 */

header('Content-Type: application/json');
require_once 'verificar_permisos.php';

// Verificar que sea una petición válida
if (!isset($_GET['rol'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Rol no especificado'
    ]);
    exit;
}

$rol = $_GET['rol'];

// Validar que el rol sea válido
$rolesValidos = ['administrador', 'gerente', 'supervisor', 'almacenista', 'usuario'];
if (!in_array($rol, $rolesValidos)) {
    echo json_encode([
        'success' => false,
        'error' => 'Rol inválido'
    ]);
    exit;
}

try {
    // Obtener módulos accesibles
    $modulos = obtenerModulosAccesibles($rol);
    
    // Obtener matriz de permisos
    $matriz = obtenerMatrizPermisos($rol);
    
    // Calcular estadísticas
    $totalModulos = count($modulos);
    $totalPermisos = 0;
    $permisosActivos = 0;
    
    foreach ($matriz as $modulo => $permisos) {
        foreach ($permisos as $permiso => $info) {
            $totalPermisos++;
            if ($info['activo']) {
                $permisosActivos++;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'rol' => $rol,
        'modulos' => $modulos,
        'matriz' => $matriz,
        'estadisticas' => [
            'total_modulos' => $totalModulos,
            'total_permisos' => $totalPermisos,
            'permisos_activos' => $permisosActivos
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener permisos: ' . $e->getMessage()
    ]);
}
?>
