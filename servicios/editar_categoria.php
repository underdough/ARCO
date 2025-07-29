<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
require_once 'registrar_historial.php'; // Importante para registrar cambios
session_start();
header('Content-Type: application/json');

$conn = ConectarDB();

$id = $_GET['id'] ?? '';
$nombre = $_POST['nombre_cat'] ?? '';
$descripcion = $_POST['subcategorias'] ?? '';
$estado = $_POST['estado'] ?? 1;
$productos = $_POST['productos'] ?? 0;

if (empty($id) || empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$estado = (int)$estado;
$productos = (int)$productos;

$stmt = $conn->prepare("UPDATE categorias SET nombre_cat = ?, subcategorias = ?, estado = ?, productos = ? WHERE id_categorias = ?");
$stmt->bind_param("ssiii", $nombre, $descripcion, $estado, $productos, $id);

if ($stmt->execute()) {
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    if ($usuario_id) {
        registrarHistorial($usuario_id, 'editar', 'Editó la categoría ID: ' . $id . ' — Nuevo nombre: ' . $nombre);
    }
    echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
}

/*if ($stmt->execute()) {
    // ✅ Registrar en historial si se actualizó correctamente
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    if ($usuario_id) {
        registrarHistorial($usuario_id, 'editar', 'Editó la categoría ID: ' . $id . ' — Nuevo nombre: ' . $nombre);
    } */

        