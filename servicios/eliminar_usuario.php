<?php
session_start();

// Verifica si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión para acceder al sistema");
    exit;
}

// Validar ID por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de usuario no válido.";
    exit;
}

require_once 'conexion.php';
$conexion = ConectarDB();

$id_usuario = intval($_GET['id']);

// Puedes agregar una protección para no borrar al mismo admin que está logueado
if ($_SESSION['usuario_id'] == $id_usuario) {
    echo "No puedes eliminar tu propia cuenta.";
    exit;
}

// Eliminar el usuario
$sql = "DELETE FROM usuarios WHERE id_usuarios = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);

if ($stmt->execute()) {
    header("Location: ../vistas/usuario.php?eliminado=1");
    exit;
} else {
    echo "Error al eliminar el usuario: " . $stmt->error;
}

$stmt->close();
$conexion->close();
