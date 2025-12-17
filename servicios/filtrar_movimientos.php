<?php
header('Content-Type: application/json');
include_once "conexion.php";

$conexion = ConectarDB();

// Obtener parámetros de filtro
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$usuario = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$producto_id = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : 0;

// Construir la consulta SQL con filtros
$sql = "SELECT
    m.id AS id,
    m.fecha,
    m.tipo,
    m.cantidad,
    m.notas,
    u.nombre AS usuario_nombre,
    p.nombre_material AS producto
FROM movimientos m
LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
LEFT JOIN materiales p ON m.producto_id = p.id_material
WHERE 1=1";

$params = [];
$types = "";

// Aplicar filtros
if (!empty($tipo)) {
    $sql .= " AND m.tipo = ?";
    $params[] = $tipo;
    $types .= "s";
}

if (!empty($usuario)) {
    $sql .= " AND u.nombre LIKE ?";
    $params[] = "%" . $usuario . "%";
    $types .= "s";
}

if (!empty($fecha)) {
    $sql .= " AND DATE(m.fecha) = ?";
    $params[] = $fecha;
    $types .= "s";
}

if ($producto_id > 0) {
    $sql .= " AND m.producto_id = ?";
    $params[] = $producto_id;
    $types .= "i";
}

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre_material LIKE ? OR m.notas LIKE ? OR u.nombre LIKE ? OR m.tipo LIKE ? OR m.cantidad LIKE ? OR m.id LIKE ? OR DATE_FORMAT(m.fecha, '%Y-%m-%d') LIKE ? OR DATE_FORMAT(m.fecha, '%d/%m/%Y') LIKE ?)";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
    $types .= "ssssssss";
}

$sql .= " ORDER BY m.fecha DESC";

$movimientos = [];

if (!empty($params)) {
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conexion->query($sql);
}

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $movimientos[] = $fila;
    }
    echo json_encode($movimientos);
} else {
    echo json_encode(['error' => 'Error al consultar movimientos: ' . $conexion->error]);
}

$conexion->close();
?>