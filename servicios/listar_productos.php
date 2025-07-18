<?php
require_once 'conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conexion = ConectarDB();

// Obtener parámetros de ordenamiento
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre';
$direccion = isset($_GET['direccion']) ? $_GET['direccion'] : 'ASC';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Validar parámetros de ordenamiento
$campos_validos = ['nombre', 'categoria', 'stock', 'precio'];
$direcciones_validas = ['ASC', 'DESC'];

if (!in_array($orden, $campos_validos)) {
    $orden = 'nombre';
}

if (!in_array(strtoupper($direccion), $direcciones_validas)) {
    $direccion = 'ASC';
}

// Construir consulta SQL
$sql = "SELECT 
    m.id_material as id,
    m.nombre_material as nombre,
    c.nombre_cat as categoria,
    m.stock,
    CASE 
        WHEN m.stock = 0 THEN 'Agotado'
        WHEN m.stock <= m.minimo_alarma THEN 'Stock Bajo'
        ELSE 'Disponible'
    END as estado,
    m.disponibilidad,
    -- Precio fijo basado en categoría y ID del material (en pesos colombianos)
    CASE 
        WHEN c.nombre_cat = 'Electronicos' THEN (m.id_material * 150000) + 500000
        WHEN c.nombre_cat = 'Oficina' THEN (m.id_material * 3000) + 5000
        WHEN c.nombre_cat = 'Herramientas' THEN (m.id_material * 15000) + 20000
        WHEN c.nombre_cat = 'Limpieza' THEN (m.id_material * 2000) + 5000
        WHEN c.nombre_cat = 'Seguridad' THEN (m.id_material * 8000) + 15000
        ELSE (m.id_material * 5000) + 10000
    END as precio
FROM materiales m 
LEFT JOIN categorias c ON m.id_categorias = c.id_categorias
WHERE 1=1";

// Agregar filtro de búsqueda si existe
if (!empty($busqueda)) {
    $busqueda_escapada = $conexion->real_escape_string($busqueda);
    $sql .= " AND (m.nombre_material LIKE '%$busqueda_escapada%' OR c.nombre_cat LIKE '%$busqueda_escapada%')";
}

// Agregar ordenamiento
switch($orden) {
    case 'nombre':
        $sql .= " ORDER BY m.nombre_material $direccion";
        break;
    case 'categoria':
        $sql .= " ORDER BY c.nombre_cat $direccion";
        break;
    case 'stock':
        $sql .= " ORDER BY m.stock $direccion";
        break;
    case 'precio':
        $sql .= " ORDER BY precio $direccion";
        break;
    default:
        $sql .= " ORDER BY m.nombre_material ASC";
}

try {
    $resultado = $conexion->query($sql);
    
    if ($resultado) {
        $productos = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $productos[] = [
                'id' => $fila['id'],
                'nombre' => $fila['nombre'],
                'categoria' => $fila['categoria'],
                'stock' => (int)$fila['stock'],
                'precio' => (int)$fila['precio'],
                'estado' => $fila['estado'],
                'disponibilidad' => (bool)$fila['disponibilidad'],
                'imagen' => 'https://via.placeholder.com/40' // Imagen por defecto
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $productos,
            'total' => count($productos)
        ]);
    } else {
        throw new Exception('Error en la consulta: ' . $conexion->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();
?>