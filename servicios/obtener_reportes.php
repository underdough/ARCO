<?php
header('Content-Type: application/json');
require_once __DIR__ . '/conexion.php';

// Usar conexión centralizada
$conn = ConectarDB();

// Obtener el tipo de reporte solicitado
$tipo = $_GET['tipo'] ?? 'movimientos';

$data = [];

switch ($tipo) {
    case 'movimientos':
        $sql = "SELECT m.id, m.tipo, m.fecha, m.producto_id, m.cantidad, m.usuario_id, m.notas,
                DATE_FORMAT(m.creado_en, '%d/%m/%Y %H:%i') as fecha_formateada,
                u.nombre as usuario,
                COALESCE(mat.nombre_material, CONCAT('Producto ID: ', m.producto_id)) as producto
                FROM movimientos m 
                LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
                LEFT JOIN materiales mat ON m.producto_id = mat.id_material
                ORDER BY m.creado_en DESC LIMIT 10";
        break;
        
    case 'categorias':
        $sql = "SELECT COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
                COUNT(m.id_material) as total_productos,
                COALESCE(SUM(m.stock), 0) as stock_total
                FROM materiales m 
                LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                GROUP BY c.id_categorias, c.nombre_cat
                ORDER BY total_productos DESC";
        break;
        
    case 'stock_bajo':
        // Evitar dependencia de columna posiblemente inexistente 'minimo_alarma'
        $sql = "SELECT m.nombre_material, m.stock,
                COALESCE(c.nombre_cat, 'Sin categoría') as categoria
                FROM materiales m 
                LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                WHERE m.stock <= 10 
                ORDER BY m.stock ASC";
        break;
        
    default:
        echo json_encode(["error" => "Tipo de reporte no válido"]);
        exit;
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

echo json_encode($data);
?>