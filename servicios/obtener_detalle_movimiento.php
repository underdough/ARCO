<?php
session_start();
require_once "conexion.php";
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Sesión no válida'
    ]);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'ID no proporcionado'
    ]);
    exit;
}

try {
    $conexion = ConectarDB();
    $id = (int) $_GET['id'];

    $sql = "SELECT 
    m.id, 
    m.fecha, 
    m.tipo, 
    m.cantidad, 
    m.notas,
    m.creado_en,
    COALESCE(mat.nombre_material, 'Producto no encontrado') AS producto,
    COALESCE(CONCAT(u.nombre, ' ', u.apellido), 'Usuario Sistema') AS usuario_nombre
FROM movimientos m
LEFT JOIN materiales mat ON m.producto_id = mat.id_material
LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
WHERE m.id = ?
LIMIT 1
";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $row = $resultado->fetch_assoc()) {
        // Formatear la fecha
        $fecha_formateada = date('d/m/Y H:i:s', strtotime($row['creado_en']));

        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $row['id'],
                'fecha' => $fecha_formateada,
                'tipo' => ucfirst($row['tipo']),
                'producto' => $row['producto'],
                'cantidad' => $row['cantidad'],
                'usuario' => $row['usuario'],
                'notas' => $row['notas'] ?: 'Sin notas'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Movimiento no encontrado'
        ]);
    }

    $stmt->close();
    $conexion->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener detalles: ' . $e->getMessage()
    ]);
}
