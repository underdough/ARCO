<?php
/**
 * Script de instalación automática de permisos
 * Ejecutar una sola vez para crear las tablas y datos
 */

require_once 'conexion.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Instalación de Sistema de Permisos</h1>";
echo "<pre>";

$conn = ConectarDB();
$errores = [];
$exitos = [];

// Leer el archivo SQL
$sqlFile = '../base-datos/sistema_permisos_completo.sql';

if (!file_exists($sqlFile)) {
    die("ERROR: No se encuentra el archivo $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Dividir en statements individuales
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^--/', $stmt) && 
               !preg_match('/^\/\*/', $stmt);
    }
);

echo "Total de statements a ejecutar: " . count($statements) . "\n\n";

$ejecutados = 0;
$fallidos = 0;

foreach ($statements as $index => $statement) {
    if (empty(trim($statement))) continue;
    
    // Ejecutar statement
    if ($conn->multi_query($statement . ';')) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        $ejecutados++;
        
        // Mostrar progreso cada 10 statements
        if ($ejecutados % 10 == 0) {
            echo "✓ Ejecutados: $ejecutados statements\n";
        }
    } else {
        $fallidos++;
        $error = $conn->error;
        
        // Ignorar errores de "tabla ya existe"
        if (strpos($error, 'already exists') === false && 
            strpos($error, 'Duplicate') === false) {
            echo "✗ Error en statement " . ($index + 1) . ": $error\n";
            $errores[] = $error;
        }
    }
}

echo "\n";
echo "========================================\n";
echo "RESUMEN DE INSTALACIÓN\n";
echo "========================================\n";
echo "✓ Statements ejecutados: $ejecutados\n";
echo "✗ Statements fallidos: $fallidos\n";
echo "\n";

// Verificar instalación
echo "========================================\n";
echo "VERIFICACIÓN DE TABLAS\n";
echo "========================================\n";

$tablas = ['modulos', 'permisos', 'modulo_permisos', 'rol_permisos', 'auditoria_permisos'];

foreach ($tablas as $tabla) {
    $result = $conn->query("SELECT COUNT(*) as total FROM $tabla");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ Tabla '$tabla': {$row['total']} registros\n";
        $exitos[] = "Tabla $tabla creada con {$row['total']} registros";
    } else {
        echo "✗ Tabla '$tabla': ERROR - " . $conn->error . "\n";
        $errores[] = "Error en tabla $tabla: " . $conn->error;
    }
}

echo "\n";

// Verificar permisos por rol
echo "========================================\n";
echo "PERMISOS POR ROL\n";
echo "========================================\n";

$roles = ['administrador', 'gerente', 'supervisor', 'almacenista', 'usuario'];

foreach ($roles as $rol) {
    $result = $conn->query("SELECT COUNT(*) as total FROM rol_permisos WHERE rol = '$rol'");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ Rol '$rol': {$row['total']} permisos\n";
    } else {
        echo "✗ Rol '$rol': ERROR\n";
    }
}

$conn->close();

echo "\n";
echo "========================================\n";
echo "RESULTADO FINAL\n";
echo "========================================\n";

if (count($errores) == 0) {
    echo "✓✓✓ INSTALACIÓN EXITOSA ✓✓✓\n";
    echo "\nPuedes ir a:\n";
    echo "http://localhost/ARCO/vistas/gestion_permisos.php\n";
    echo "\nY recargar la página (F5)\n";
} else {
    echo "⚠ INSTALACIÓN CON ERRORES ⚠\n";
    echo "\nErrores encontrados:\n";
    foreach ($errores as $error) {
        echo "- $error\n";
    }
}

echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Instalación de Permisos</title>
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
