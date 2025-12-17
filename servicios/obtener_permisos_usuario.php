<?php
/**
 * Servicio para obtener permisos del usuario actual
 * Retorna JSON con los módulos y permisos del usuario
 */

session_start();
header('Content-Type: application/json');

require_once 'verificar_permisos.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autenticado'
    ]);
    exit;
}

$rol = $_SESSION['rol'];

try {
    // Obtener módulos accesibles
    $modulos = obtenerModulosAccesibles($rol);
    
    // Obtener matriz completa de permisos
    $matriz_permisos = obtenerMatrizPermisos($rol);
    
    echo json_encode([
        'success' => true,
        'rol' => $rol,
        'modulos' => $modulos,
        'matriz_permisos' => $matriz_permisos,
        'total_modulos' => count($modulos)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener permisos: ' . $e->getMessage()
    ]);
}
?>
