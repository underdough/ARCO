<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('session.cookie_path', '/');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión para acceder al sistema");
    exit;
}

require_once 'conexion.php';

// Validación del método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Acceso no autorizado.";
    exit;
}

// Captura de datos del formulario
$id = intval($_POST['id_usuarios']);
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$correo = trim($_POST['correo']);
$num_doc = trim($_POST['num_doc']);
$rol = trim($_POST['rol']);
$estado = trim($_POST['estado']);

$conexion = ConectarDB();

$sql = "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, num_doc = ?, rol = ?, estado = ? WHERE id_usuarios = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo "Error al preparar la consulta: " . $conexion->error;
    exit;
}

$stmt->bind_param("ssssssi", $nombre, $apellido, $correo, $num_doc, $rol, $estado, $id);

if ($stmt->execute()) {
    // Redirigir exitosamente
    header("Location: ../vistas/usuario.php?success=1");
    exit;
} else {
    echo "Error al actualizar el usuario: " . $stmt->error;
}

$stmt->close();
$conexion->close();
