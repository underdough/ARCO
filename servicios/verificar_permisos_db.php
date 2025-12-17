<?php
/**
 * Script de verificación de permisos en la base de datos
 * Verifica si las tablas existen y tienen datos
 */

require_once 'conexion.php';

header('Content-Type: application/json');

$conn = ConectarDB();
$resultado = [
    'success' => true,
    'tablas' => [],
    'errores' => []
];

// Verificar tabla modulos
$sql = "SELECT COUNT(*) as total FROM modulos";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $resultado['tablas']['modulos'] = [
        'existe' => true,
        'registros' => $row['total']
    ];
} else {
    $resultado['tablas']['modulos'] = [
        'existe' => false,
        'error' => $conn->error
    ];
    $resultado['errores'][] = 'Tabla modulos no existe o tiene error';
}

// Verificar tabla permisos
$sql = "SELECT COUNT(*) as total FROM permisos";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $resultado['tablas']['permisos'] = [
        'existe' => true,
        'registros' => $row['total']
    ];
} else {
    $resultado['tablas']['permisos'] = [
        'existe' => false,
        'error' => $conn->error
    ];
    $resultado['errores'][] = 'Tabla permisos no existe o tiene error';
}

// Verificar tabla rol_permisos
$sql = "SELECT COUNT(*) as total FROM rol_permisos";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $resultado['tablas']['rol_permisos'] = [
        'existe' => true,
        'registros' => $row['total']
    ];
} else {
    $resultado['tablas']['rol_permisos'] = [
        'existe' => false,
        'error' => $conn->error
    ];
    $resultado['errores'][] = 'Tabla rol_permisos no existe o tiene error';
}

// Verificar permisos de administrador
$sql = "SELECT COUNT(*) as total FROM rol_permisos WHERE rol = 'administrador'";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $resultado['permisos_administrador'] = $row['total'];
} else {
    $resultado['permisos_administrador'] = 0;
}

// Si hay errores, marcar como no exitoso
if (count($resultado['errores']) > 0) {
    $resultado['success'] = false;
}

$conn->close();

echo json_encode($resultado, JSON_PRETTY_PRINT);
?>
