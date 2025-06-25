<?php
session_start();
require_once 'conexion.php'; // Ajusta la ruta según corresponda

$conexion = conectarDB();
$usuarioId = $_SESSION['usuario_id'] ?? null;

if (!$usuarioId) {
    echo "Sesión inválida.";
    exit;
}

// Capturar el rol y los permisos
$rol = $_POST['userRole'] ?? 'viewer';
$permisos = $_POST['permisos'] ?? [];

// Guardar el rol del usuario (en tabla usuarios, por ejemplo)
$actualizarRol = $conexion->prepare("UPDATE usuarios SET rol = ? WHERE id_usuarios = ?");
$actualizarRol->bind_param("si", $rol, $usuarioId);
$actualizarRol->execute();

// Borrar permisos anteriores
$conexion->query("DELETE FROM permisos_usuario WHERE usuario_id = $usuarioId");

// Insertar los nuevos permisos
$stmt = $conexion->prepare("INSERT INTO permisos_usuario (usuario_id, modulo, ver, crear, editar, eliminar) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($permisos as $modulo => $acciones) {
    $ver = isset($acciones['ver']) ? 1 : 0;
    $crear = isset($acciones['crear']) ? 1 : 0;
    $editar = isset($acciones['editar']) ? 1 : 0;
    $eliminar = isset($acciones['eliminar']) ? 1 : 0;

    $stmt->bind_param("isiiii", $usuarioId, $modulo, $ver, $crear, $editar, $eliminar);
    $stmt->execute();
}

header("Location: ../vistas/configuracion.php?exito=1");
exit;
