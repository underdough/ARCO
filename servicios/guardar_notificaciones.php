<?php
session_start(); // <-- Muy importante: debe ir antes de cualquier acceso a $_SESSION
file_put_contents(__DIR__ . "/log_debug.txt", "Entrando a guardar_notificaciones.php\n", FILE_APPEND);
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    file_put_contents(__DIR__ . "/log_debug.txt", "Error: sesión no contiene usuario_id\n", FILE_APPEND);
    die("Usuario no autenticado.");
}

file_put_contents(__DIR__ . "/log_debug.txt", "usuario_id activo en notificaciones: " . $_SESSION['usuario_id'] . "\n", FILE_APPEND);

$usuarioId = $_SESSION['usuario_id'];

// Recuperar los datos del formulario
$notifyLowStock = isset($_POST['notifyLowStock']) ? 1 : 0;
$lowStockThreshold = isset($_POST['lowStockThreshold']) ? intval($_POST['lowStockThreshold']) : 15;
$notifyMovements = isset($_POST['notifyMovements']) ? 1 : 0;
$notifyEmail = isset($_POST['notifyEmail']) ? 1 : 0;
$notificationEmails = isset($_POST['notificationEmails']) ? trim($_POST['notificationEmails']) : '';

$conexion = ConectarDB();

// Verificar si ya existen preferencias para este usuario
$sqlCheck = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ?";
$stmt = $conexion->prepare($sqlCheck);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data['total'] > 0) {
    // Actualizar
    $sql = "UPDATE notificaciones SET 
        notify_low_stock = ?, 
        low_stock_threshold = ?, 
        notify_movements = ?, 
        notify_email = ?, 
        notification_emails = ? 
        WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiissi", $notifyLowStock, $lowStockThreshold, $notifyMovements, $notifyEmail, $notificationEmails, $usuarioId);
} else {
    // Insertar
    $sql = "INSERT INTO notificaciones 
        (usuario_id, notify_low_stock, low_stock_threshold, notify_movements, notify_email, notification_emails) 
        VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiiiis", $usuarioId, $notifyLowStock, $lowStockThreshold, $notifyMovements, $notifyEmail, $notificationEmails);
}

if ($stmt->execute()) {
    header("Location: ../vistas/configuracion.php?success=Preferencias guardadas");
} else {
    header("Location: ../vistas/configuracion.php?error=Error al guardar");
}
exit;
