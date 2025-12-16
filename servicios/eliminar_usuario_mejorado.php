<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar usuarios']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;

if ($id_usuario <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

// No permitir que el admin se elimine a sí mismo
if ($id_usuario == $_SESSION['usuario_id']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No puede eliminar su propia cuenta']);
    exit;
}

try {
    $conn = ConectarDB();
    
    // Verificar que el usuario existe
    $stmt = $conn->prepare("SELECT nombre, apellido FROM usuarios WHERE id_usuarios = ?");
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
    $nombre_completo = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $stmt->close();
    
    // Registrar en auditoría antes de eliminar
    $sql_audit = "INSERT INTO auditoria_usuarios (usuario_id, accion, valor_anterior, realizado_por, fecha_accion) 
                  VALUES (?, 'eliminar', ?, ?, NOW())";
    $stmt_audit = $conn->prepare($sql_audit);
    $stmt_audit->bind_param("isi", $id_usuario, $nombre_completo, $_SESSION['usuario_id']);
    $stmt_audit->execute();
    $stmt_audit->close();
    
    // Eliminar usuario
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuarios = ?");
    $stmt->bind_param("i", $id_usuario);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario eliminado exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}
?>
