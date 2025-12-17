<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para editar usuarios']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar campos obligatorios
$campos_requeridos = ['id_usuarios', 'nombre', 'apellido', 'num_doc', 'correo', 'rol', 'cargos', 'estado'];
foreach ($campos_requeridos as $campo) {
    if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "El campo {$campo} es obligatorio"]);
        exit;
    }
}

$id_usuario = intval($_POST['id_usuarios']);
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$num_doc = trim($_POST['num_doc']);
$correo = filter_var(trim($_POST['correo']), FILTER_VALIDATE_EMAIL);
$rol = $_POST['rol'];
$cargos = trim($_POST['cargos']);
$estado = $_POST['estado'];
$telefono = isset($_POST['num_telefono']) ? trim($_POST['num_telefono']) : null;

// Validaciones
if (!$correo) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
    exit;
}

if (!is_numeric($num_doc) || $num_doc <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Número de documento inválido']);
    exit;
}

$roles_validos = ['administrador', 'usuario', 'almacenista', 'supervisor', 'gerente'];
if (!in_array($rol, $roles_validos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rol inválido']);
    exit;
}

$estados_validos = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];
if (!in_array($estado, $estados_validos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $conn = ConectarDB();
    
    // Obtener datos actuales del usuario para auditoría
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuarios = ?");
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
    
    $usuario_anterior = $resultado->fetch_assoc();
    $stmt->close();
    
    // Verificar si el documento ya existe en otro usuario
    $stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE num_doc = ? AND id_usuarios != ?");
    $stmt->bind_param("si", $num_doc, $id_usuario);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El número de documento ya está registrado en otro usuario']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Verificar si el correo ya existe en otro usuario
    $stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ? AND id_usuarios != ?");
    $stmt->bind_param("si", $correo, $id_usuario);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado en otro usuario']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Actualizar usuario
    $fecha_modificacion = date('Y-m-d H:i:s');
    $modificado_por = $_SESSION['usuario_id'];
    
    if ($telefono) {
        $sql = "UPDATE usuarios SET 
                nombre = ?, apellido = ?, num_doc = ?, correo = ?, 
                rol = ?, cargos = ?, estado = ?, num_telefono = ?,
                fecha_modificacion = ?, modificado_por = ?
                WHERE id_usuarios = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssii", $nombre, $apellido, $num_doc, $correo, $rol, $cargos, $estado, $telefono, $fecha_modificacion, $modificado_por, $id_usuario);
    } else {
        $sql = "UPDATE usuarios SET 
                nombre = ?, apellido = ?, num_doc = ?, correo = ?, 
                rol = ?, cargos = ?, estado = ?,
                fecha_modificacion = ?, modificado_por = ?
                WHERE id_usuarios = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssii", $nombre, $apellido, $num_doc, $correo, $rol, $cargos, $estado, $fecha_modificacion, $modificado_por, $id_usuario);
    }
    
    if ($stmt->execute()) {
        // Registrar cambios en auditoría
        $cambios = [];
        if ($usuario_anterior['nombre'] !== $nombre) $cambios[] = ['campo' => 'nombre', 'anterior' => $usuario_anterior['nombre'], 'nuevo' => $nombre];
        if ($usuario_anterior['apellido'] !== $apellido) $cambios[] = ['campo' => 'apellido', 'anterior' => $usuario_anterior['apellido'], 'nuevo' => $apellido];
        if ($usuario_anterior['num_doc'] != $num_doc) $cambios[] = ['campo' => 'num_doc', 'anterior' => $usuario_anterior['num_doc'], 'nuevo' => $num_doc];
        if ($usuario_anterior['correo'] !== $correo) $cambios[] = ['campo' => 'correo', 'anterior' => $usuario_anterior['correo'], 'nuevo' => $correo];
        if ($usuario_anterior['rol'] !== $rol) $cambios[] = ['campo' => 'rol', 'anterior' => $usuario_anterior['rol'], 'nuevo' => $rol];
        if ($usuario_anterior['cargos'] !== $cargos) $cambios[] = ['campo' => 'cargos', 'anterior' => $usuario_anterior['cargos'], 'nuevo' => $cargos];
        if ($usuario_anterior['estado'] !== $estado) $cambios[] = ['campo' => 'estado', 'anterior' => $usuario_anterior['estado'], 'nuevo' => $estado];
        
        // Insertar registros de auditoría
        foreach ($cambios as $cambio) {
            $sql_audit = "INSERT INTO auditoria_usuarios (usuario_id, accion, campo_modificado, valor_anterior, valor_nuevo, realizado_por, fecha_accion) 
                          VALUES (?, 'editar', ?, ?, ?, ?, NOW())";
            $stmt_audit = $conn->prepare($sql_audit);
            $stmt_audit->bind_param("isssi", $id_usuario, $cambio['campo'], $cambio['anterior'], $cambio['nuevo'], $_SESSION['usuario_id']);
            $stmt_audit->execute();
            $stmt_audit->close();
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario actualizado exitosamente',
            'cambios' => count($cambios)
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}
?>
