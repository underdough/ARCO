<?php
/**
 * Script de prueba para listar_productos.php
 */

// Habilitar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simular sesión si no existe
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['rol'] = 'administrador';
}

echo "=== TEST LISTAR PRODUCTOS ===\n\n";

require_once 'conexion.php';

try {
    $conexion = ConectarDB();
    echo "✓ Conexión establecida\n\n";
    
    // Test 1: Verificar tabla materiales
    echo "Test 1: Verificar tabla materiales\n";
    $result = $conexion->query("SHOW TABLES LIKE 'materiales'");
    if ($result->num_rows > 0) {
        echo "✓ Tabla 'materiales' existe\n";
    } else {
        echo "✗ Tabla 'materiales' NO existe\n";
        exit;
    }
    
    // Test 2: Verificar estructura de tabla
    echo "\nTest 2: Estructura de tabla materiales\n";
    $result = $conexion->query("DESCRIBE materiales");
    $campos = [];
    while ($row = $result->fetch_assoc()) {
        $campos[] = $row['Field'];
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    // Test 3: Verificar campos necesarios
    echo "\nTest 3: Verificar campos necesarios\n";
    $camposNecesarios = ['id_material', 'nombre_material', 'stock', 'id_categorias'];
    foreach ($camposNecesarios as $campo) {
        if (in_array($campo, $campos)) {
            echo "✓ Campo '$campo' existe\n";
        } else {
            echo "✗ Campo '$campo' NO existe\n";
        }
    }
    
    // Verificar campos opcionales
    $camposOpcionales = ['precio', 'descripcion', 'disponibilidad'];
    echo "\nCampos opcionales:\n";
    foreach ($camposOpcionales as $campo) {
        if (in_array($campo, $campos)) {
            echo "✓ Campo '$campo' existe\n";
        } else {
            echo "⚠ Campo '$campo' NO existe (se usará valor por defecto)\n";
        }
    }
    
    // Test 4: Contar registros
    echo "\nTest 4: Contar registros\n";
    $result = $conexion->query("SELECT COUNT(*) as total FROM materiales");
    $row = $result->fetch_assoc();
    echo "✓ Total de productos: {$row['total']}\n";
    
    // Test 5: Consulta básica
    echo "\nTest 5: Consulta básica\n";
    $query = "SELECT 
                m.id_material as id,
                m.nombre_material as nombre,
                COALESCE(c.nombre_cat, 'Sin categoría') as categoria,
                COALESCE(m.stock, 0) as stock
              FROM materiales m
              LEFT JOIN categorias c ON m.id_categorias = c.id_categorias
              LIMIT 5";
    
    $result = $conexion->query($query);
    if (!$result) {
        echo "✗ Error en consulta: " . $conexion->error . "\n";
    } else {
        echo "✓ Consulta exitosa, registros: " . $result->num_rows . "\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - ID: {$row['id']}, Nombre: {$row['nombre']}, Stock: {$row['stock']}\n";
        }
    }
    
    echo "\n=== TODOS LOS TESTS COMPLETADOS ===\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
