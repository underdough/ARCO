<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para cambiar el estado de usuarios']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
$nuevo_estado = isset($_POST['estado']) ? $_POST['estado'] : '';

if ($id_usuario <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

$estados_validos = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];
if (!in_array($nuevo_estado, $estados_validos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

// No permitir que el admin se desactive a sí mismo
if ($id_usuario == $_SESSION['usuario_id'] && $nuevo_estado !== 'ACTIVO') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No puede desactivar su propia cuenta']);
    exit;
}

try {
    $conn = ConectarDB();
    
    // Obtener estado actual
    $stmt = $conn->prepare("SELECT estado FROM usuarios WHERE id_usuarios = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $usuario = $resultado->fetch_assoc();
    $estado_anterior = $usuario['estado'];
    $stmt->close();
    
    // Actualizar estado
    $fecha_modificacion = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE usuarios SET estado = ?, fecha_modificacion = ?, modificado_por = ? WHERE id_usuarios = ?");
    $stmt->bind_param("ssii", $nuevo_estado, $fecha_modificacion, $_SESSION['usuario_id'], $id_usuario);
    
    if ($stmt->execute()) {
        // Registrar en auditoría
        $accion = $nuevo_estado === 'ACTIVO' ? 'activar' : ($nuevo_estado === 'INACTIVO' ? 'desactivar' : 'suspender');
        $sql_audit = "INSERT INTO auditoria_usuarios (usuario_id, accion, campo_modificado, valor_anterior, valor_nuevo, realizado_por, fecha_accion) 
                      VALUES (?, ?, 'estado', ?, ?, ?, NOW())";
        $stmt_audit = $conn->prepare($sql_audit);
        $stmt_audit->bind_param("isssi", $id_usuario, $accion, $estado_anterior, $nuevo_estado, $_SESSION['usuario_id']);
        $stmt_audit->execute();
        $stmt_audit->close();
        
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Estado del usuario actualizado exitosamente',
            'nuevo_estado' => $nuevo_estado
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar estado: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}
?>
