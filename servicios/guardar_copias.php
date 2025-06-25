<?php
require_once 'conexion.php';
session_start();

$conexion = conectarDB();
$usuarioId = $_SESSION['usuario_id'] ?? null;

if ($usuarioId) {
    $autoBackup = isset($_POST['autoBackup']) ? 1 : 0;
    $frecuencia = $_POST['frecuencia'] ?? 'diaria';
    $retencion_dias = $_POST['retencion_dias'] ?? 30;

    // Si ya existe una fila para este usuario, actualízala; si no, insértala
    $stmtCheck = $conexion->prepare("SELECT id FROM copias_seguridad WHERE usuario_id = ?");
    $stmtCheck->bind_param("i", $usuarioId);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conexion->prepare("UPDATE copias_seguridad SET auto_backup = ?, frecuencia = ?, retencion_dias = ? WHERE usuario_id = ?");
        $stmt->bind_param("isii", $autoBackup, $frecuencia, $retencion_dias, $usuarioId);
    } else {
        $stmt = $conexion->prepare("INSERT INTO copias_seguridad (usuario_id, auto_backup, backup_frequency, backup_retention_days) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $usuarioId, $autoBackup, $frecuencia, $retencion_dias);
    }

    $stmt->execute();
    header("Location: ../vistas/configuracion.php");
    exit();
}
?>
