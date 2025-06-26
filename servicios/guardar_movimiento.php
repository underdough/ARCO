<?php
session_start();
header('Content-Type: application/json');

include_once "conexion.php";
$conexion = ConectarDB();

// Verificamos si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
    exit;
}

$usuario = (int) $_SESSION['usuario_id'];

// Validamos los campos necesarios
if (
    isset($_POST['tipo']) &&
    isset($_POST['fecha']) &&
    isset($_POST['producto']) &&
    isset($_POST['cantidad'])
) {
    $tipo = $conexion->real_escape_string($_POST['tipo']);
    $fecha = $conexion->real_escape_string($_POST['fecha']);
    $producto = (int) $_POST['producto'];
    $cantidad = (int) $_POST['cantidad'];
    $notas = isset($_POST['notas']) ? $conexion->real_escape_string($_POST['notas']) : '';

    // Insertamos en la base de datos
    $sql = "INSERT INTO movimientos (tipo, fecha, producto_id, cantidad, usuario_id, notas) 
            VALUES ('$tipo', '$fecha', $producto, $cantidad, $usuario, '$notas')";

    if ($conexion->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Movimiento guardado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $conexion->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
