<?php
/**
 * Script para insertar permisos directamente con PHP
 * Más confiable que ejecutar SQL complejo
 */

require_once 'conexion.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Inserción Directa de Permisos</h1>";
echo "<pre>";

$conn = ConectarDB();

// Verificar que existan las tablas base
$result = $conn->query("SELECT COUNT(*) as total FROM modulos");
$modulos = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM permisos");
$permisos = $result->fetch_assoc()['total'];

echo "Módulos existentes: $modulos\n";
echo "Permisos existentes: $permisos\n\n";

if ($modulos == 0 || $permisos == 0) {
    die("ERROR: Primero debes ejecutar instalar_permisos.php para crear módulos y permisos base");
}

echo "========================================\n";
echo "INSERTANDO MODULO_PERMISOS\n";
echo "========================================\n";

// Limpiar tabla primero
$conn->query("DELETE FROM modulo_permisos");
$conn->query("DELETE FROM rol_permisos");

// Obtener IDs de módulos
$modulos_map = [];
$result = $conn->query("SELECT id_modulo, nombre FROM modulos");
while ($row = $result->fetch_assoc()) {
    $modulos_map[$row['nombre']] = $row['id_modulo'];
}

// Obtener IDs de permisos
$permisos_map = [];
$result = $conn->query("SELECT id_permiso, codigo FROM permisos");
while ($row = $result->fetch_assoc()) {
    $permisos_map[$row['codigo']] = $row['id_permiso'];
}

// Definir permisos por módulo
$modulo_permisos_config = [
    'dashboard' => ['ver'],
    'productos' => ['ver', 'crear', 'editar', 'eliminar', 'exportar', 'importar'],
    'categorias' => ['ver', 'crear', 'editar', 'eliminar'],
    'movimientos' => ['ver', 'crear', 'editar', 'aprobar', 'exportar'],
    'usuarios' => ['ver', 'crear', 'editar', 'eliminar', 'auditar'],
    'reportes' => ['ver', 'crear', 'exportar'],
    'configuracion' => ['ver', 'editar'],
    'ordenes_compra' => ['ver', 'crear', 'editar', 'aprobar', 'exportar'],
    'devoluciones' => ['ver', 'crear', 'editar', 'aprobar'],
    'recepcion' => ['ver', 'crear', 'editar'],
    'anomalias_novedades' => ['ver', 'crear', 'editar', 'eliminar', 'exportar']
];

$insertados = 0;
foreach ($modulo_permisos_config as $modulo => $permisos_list) {
    if (!isset($modulos_map[$modulo])) continue;
    
    $id_modulo = $modulos_map[$modulo];
    
    foreach ($permisos_list as $permiso) {
        if (!isset($permisos_map[$permiso])) continue;
        
        $id_permiso = $permisos_map[$permiso];
        
        $sql = "INSERT INTO modulo_permisos (id_modulo, id_permiso) VALUES ($id_modulo, $id_permiso)";
        if ($conn->query($sql)) {
            $insertados++;
        }
    }
}

echo "✓ Insertados $insertados registros en modulo_permisos\n\n";

echo "========================================\n";
echo "INSERTANDO ROL_PERMISOS\n";
echo "========================================\n";

// Definir permisos por rol
$rol_permisos_config = [
    'administrador' => [
        'dashboard' => ['ver'],
        'productos' => ['ver', 'crear', 'editar', 'eliminar', 'exportar', 'importar'],
        'categorias' => ['ver', 'crear', 'editar', 'eliminar'],
        'movimientos' => ['ver', 'crear', 'editar', 'aprobar', 'exportar'],
        'usuarios' => ['ver', 'crear', 'editar', 'eliminar', 'auditar'],
        'reportes' => ['ver', 'crear', 'exportar'],
        'configuracion' => ['ver', 'editar'],
        'ordenes_compra' => ['ver', 'crear', 'editar', 'aprobar', 'exportar'],
        'devoluciones' => ['ver', 'crear', 'editar', 'aprobar'],
        'recepcion' => ['ver', 'crear', 'editar'],
        'anomalias_novedades' => ['ver', 'crear', 'editar', 'eliminar', 'exportar']
    ],
    'gerente' => [
        'dashboard' => ['ver'],
        'productos' => ['ver', 'crear', 'editar', 'exportar', 'importar'],
        'categorias' => ['ver', 'crear', 'editar'],
        'movimientos' => ['ver', 'crear', 'editar', 'aprobar', 'exportar'],
        'usuarios' => ['ver'],
        'reportes' => ['ver', 'crear', 'exportar'],
        'configuracion' => ['ver', 'editar'],
        'ordenes_compra' => ['ver', 'crear', 'editar', 'aprobar', 'exportar'],
        'devoluciones' => ['ver', 'crear', 'editar', 'aprobar'],
        'recepcion' => ['ver', 'crear', 'editar'],
        'anomalias_novedades' => ['ver', 'crear', 'editar', 'exportar']
    ],
    'supervisor' => [
        'dashboard' => ['ver'],
        'productos' => ['ver', 'exportar'],
        'categorias' => ['ver'],
        'movimientos' => ['ver', 'aprobar', 'exportar'],
        'reportes' => ['ver', 'exportar'],
        'ordenes_compra' => ['ver', 'aprobar'],
        'devoluciones' => ['ver', 'aprobar'],
        'recepcion' => ['ver'],
        'anomalias_novedades' => ['ver', 'crear', 'exportar']
    ],
    'almacenista' => [
        'dashboard' => ['ver'],
        'productos' => ['ver', 'crear', 'editar'],
        'categorias' => ['ver'],
        'movimientos' => ['ver', 'crear', 'editar'],
        'reportes' => ['ver'],
        'recepcion' => ['ver', 'crear', 'editar'],
        'devoluciones' => ['ver', 'crear'],
        'anomalias_novedades' => ['ver', 'crear']
    ],
    'usuario' => [
        'dashboard' => ['ver'],
        'productos' => ['ver'],
        'categorias' => ['ver'],
        'movimientos' => ['ver'],
        'reportes' => ['ver'],
        'anomalias_novedades' => ['ver']
    ]
];

$total_insertados = 0;
foreach ($rol_permisos_config as $rol => $modulos_permisos) {
    $count = 0;
    
    foreach ($modulos_permisos as $modulo => $permisos_list) {
        if (!isset($modulos_map[$modulo])) continue;
        
        $id_modulo = $modulos_map[$modulo];
        
        foreach ($permisos_list as $permiso) {
            if (!isset($permisos_map[$permiso])) continue;
            
            $id_permiso = $permisos_map[$permiso];
            
            $sql = "INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
                    VALUES ('$rol', $id_modulo, $id_permiso, 1)";
            
            if ($conn->query($sql)) {
                $count++;
                $total_insertados++;
            }
        }
    }
    
    echo "✓ Rol '$rol': $count permisos insertados\n";
}

echo "\n✓ Total insertados: $total_insertados registros en rol_permisos\n\n";

// Verificación final
echo "========================================\n";
echo "VERIFICACIÓN FINAL\n";
echo "========================================\n";

$result = $conn->query("SELECT COUNT(*) as total FROM modulo_permisos");
$mp = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM rol_permisos");
$rp = $result->fetch_assoc()['total'];

echo "modulo_permisos: $mp registros\n";
echo "rol_permisos: $rp registros\n\n";

if ($mp > 0 && $rp > 0) {
    echo "✓✓✓ INSERCIÓN EXITOSA ✓✓✓\n\n";
    echo "Ahora ve a:\n";
    echo "http://localhost/ARCO/vistas/gestion_permisos.php\n\n";
    echo "Y presiona F5 para recargar\n";
} else {
    echo "✗✗✗ ERROR EN LA INSERCIÓN ✗✗✗\n";
}

$conn->close();

echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inserción de Permisos</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        h1 {
            color: #4ec9b0;
        }
        pre {
            background: #252526;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #4ec9b0;
        }
    </style>
</head>
<body>
    <p><a href="../vistas/gestion_permisos.php" style="color: #4ec9b0;">← Volver a Gestión de Permisos</a></p>
</body>
</html>
