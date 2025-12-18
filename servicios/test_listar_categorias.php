<?php
/**
 * Script de prueba para listar_categorias.php
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

echo "=== TEST LISTAR CATEGORIAS ===\n\n";

require_once 'conexion.php';

try {
    $conexion = ConectarDB();
    echo "✓ Conexión establecida\n\n";
    
    // Test 1: Verificar tabla categorias
    echo "Test 1: Verificar tabla categorias\n";
    $result = $conexion->query("SHOW TABLES LIKE 'categorias'");
    if ($result->num_rows > 0) {
        echo "✓ Tabla 'categorias' existe\n";
    } else {
        echo "✗ Tabla 'categorias' NO existe\n";
        exit;
    }
    
    // Test 2: Verificar estructura de tabla
    echo "\nTest 2: Estructura de tabla categorias\n";
    $result = $conexion->query("DESCRIBE categorias");
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['Field']} ({$row['Type']})\n";
    }
    
    // Test 3: Contar registros
    echo "\nTest 3: Contar registros\n";
    $result = $conexion->query("SELECT COUNT(*) as total FROM categorias");
    $row = $result->fetch_assoc();
    echo "✓ Total de categorías: {$row['total']}\n";
    
    // Test 4: Consulta con JOIN
    echo "\nTest 4: Consulta con JOIN\n";
    $query = "SELECT 
                c.id_categorias,
                c.nombre_cat,
                c.subcategoria as subcategorias,
                c.estado,
                COUNT(m.id_material) as productos
              FROM categorias c
              LEFT JOIN materiales m ON c.id_categorias = m.id_categorias
              GROUP BY c.id_categorias, c.nombre_cat, c.subcategoria, c.estado
              ORDER BY c.id_categorias DESC
              LIMIT 5";
    
    $result = $conexion->query($query);
    if (!$result) {
        echo "✗ Error en consulta: " . $conexion->error . "\n";
    } else {
        echo "✓ Consulta exitosa, registros: " . $result->num_rows . "\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - ID: {$row['id_categorias']}, Nombre: {$row['nombre_cat']}, Productos: {$row['productos']}\n";
        }
    }
    
    // Test 5: Consulta con prepared statement
    echo "\nTest 5: Prepared statement\n";
    $limite = 10;
    $offset = 0;
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        echo "✗ Error preparando: " . $conexion->error . "\n";
    } else {
        echo "✓ Statement preparado correctamente\n";
    }
    
    echo "\n=== TODOS LOS TESTS COMPLETADOS ===\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
