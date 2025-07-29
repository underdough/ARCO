<?php
require_once 'conexion.php';

function registrarHistorial($usuario_id, $tipo_accion, $descripcion) {
    $conexion = ConectarDB(); // ← Crea su propia conexión local

    if (!$conexion) {
        error_log("Error al conectar en registrarHistorial");
        return;
    }

    $stmt = $conexion->prepare("INSERT INTO historial_acciones (usuario_id, tipo_accion, descripcion, fecha) VALUES (?, ?, ?, NOW())");

    if (!$stmt) {
        error_log("Error al preparar statement: " . $conexion->error);
        return;
    }

    $stmt->bind_param("iss", $usuario_id, $tipo_accion, $descripcion);
    $stmt->execute();
    $stmt->close();
    $conexion->close(); // ← Siempre cerrar la conexión
}
?>
