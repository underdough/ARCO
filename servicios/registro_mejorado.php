<?php
session_start();
include 'conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado y sea administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para crear usuarios']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar campos obligatorios
$campos_requeridos = ['nombre', 'apellido', 'numeroDocumento', 'email', 'rol', 'cargos', 'contrasena', 'confirmarContrasena'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "El campo {$campo} es obligatorio"]);
        exit;
    }
}

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$numeroDocumento = trim($_POST['numeroDocumento']);
$email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$rol = $_POST['rol'];
$cargos = trim($_POST['cargos']);
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '0000000000';
$contrasena = $_POST['contrasena'];
$confirmar = $_POST['confirmarContrasena'];

// Validaciones
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
    exit;
}

if (!is_numeric($numeroDocumento) || $numeroDocumento <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Número de documento inválido']);
    exit;
}

if ($numeroDocumento > 2147483647) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El número de documento es demasiado largo']);
    exit;
}

// Validar rol
$roles_validos = ['administrador', 'usuario', 'almacenista', 'supervisor', 'gerente'];
if (!in_array($rol, $roles_validos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Rol inválido']);
    exit;
}

if ($contrasena !== $confirmar) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($contrasena) < 8 || strlen($contrasena) > 20) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener entre 8 y 20 caracteres']);
    exit;
}

try {
    $conn = ConectarDB();
    
    // Verificar si ya existe el número de documento
    $stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE num_doc = ?");
    $stmt->bind_param("s", $numeroDocumento);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El número de documento ya está registrado']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Verificar si ya existe el email
    $stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    
    // Hash de la contraseña
    $hash = password_hash($contrasena, PASSWORD_BCRYPT);
    
    $estado = 'ACTIVO';
    $fecha_creacion = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO usuarios (num_doc, nombre, apellido, rol, cargos, correo, contrasena, num_telefono, fecha_creacion, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssss",
        $numeroDocumento,
        $nombre,
        $apellido,
        $rol,
        $cargos,
        $email,
        $hash,
        $telefono,
        $fecha_creacion,
        $estado
    );
    
    if ($stmt->execute()) {
        $nuevo_id = $stmt->insert_id;
        
        // Registrar en auditoría
        $sql_audit = "INSERT INTO auditoria_usuarios (usuario_id, accion, realizado_por, fecha_accion) 
                      VALUES (?, 'crear', ?, NOW())";
        $stmt_audit = $conn->prepare($sql_audit);
        $stmt_audit->bind_param("ii", $nuevo_id, $_SESSION['usuario_id']);
        $stmt_audit->execute();
        $stmt_audit->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario creado exitosamente',
            'usuario_id' => $nuevo_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}
?>
