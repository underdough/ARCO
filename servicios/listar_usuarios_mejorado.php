<?php
require_once 'conexion.php';

// Obtener parámetros de búsqueda y filtrado
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro_rol = isset($_GET['rol']) ? $_GET['rol'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$conexion = ConectarDB();

// Construir consulta SQL con filtros
$sql = "SELECT id_usuarios, num_doc, nombre, apellido, rol, cargos, correo, estado, 
        fecha_creacion, ultimo_conexion 
        FROM usuarios WHERE 1=1";

$params = [];
$types = '';

// Filtro de búsqueda (nombre, apellido, correo o documento)
if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR correo LIKE ? OR num_doc LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= 'ssss';
}

// Filtro por rol
if (!empty($filtro_rol)) {
    $sql .= " AND rol = ?";
    $params[] = $filtro_rol;
    $types .= 's';
}

// Filtro por estado
if (!empty($filtro_estado)) {
    $sql .= " AND estado = ?";
    $params[] = $filtro_estado;
    $types .= 's';
}

$sql .= " ORDER BY fecha_creacion DESC";

// Preparar y ejecutar consulta
$stmt = $conexion->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();

// Generar respuesta JSON
$usuarios = [];
while ($fila = $resultado->fetch_assoc()) {
    $usuarios[] = [
        'id' => $fila['id_usuarios'],
        'num_doc' => $fila['num_doc'],
        'nombre' => $fila['nombre'],
        'apellido' => $fila['apellido'],
        'nombre_completo' => $fila['nombre'] . ' ' . $fila['apellido'],
        'rol' => $fila['rol'],
        'cargos' => $fila['cargos'],
        'correo' => $fila['correo'],
        'estado' => $fila['estado'],
        'fecha_creacion' => $fila['fecha_creacion'],
        'ultimo_conexion' => $fila['ultimo_conexion']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'total' => count($usuarios),
    'usuarios' => $usuarios
]);

$stmt->close();
$conexion->close();
?>
