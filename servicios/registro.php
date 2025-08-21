<?php
session_start();
include 'conexion.php';

// Establecer tipo de contenido JSON para mejor manejo de errores
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar campos obligatorios
if (
    empty($_POST['nombre']) ||
    empty($_POST['apellido']) ||
    empty($_POST['numeroDocumento']) ||
    empty($_POST['email']) ||
    empty($_POST['contrasena']) ||
    empty($_POST['confirmarContrasena'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$numeroDocumento = trim($_POST['numeroDocumento']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$contrasena = $_POST['contrasena'];
$confirmar = $_POST['confirmarContrasena'];

// Validar número de documento
$numeroDocumento = trim($_POST['numeroDocumento']);
if (!is_numeric($numeroDocumento) || $numeroDocumento <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Número de documento inválido']);
    exit;
}

// Validar que el número no exceda el límite de INT
if ($numeroDocumento > 2147483647) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El número de documento es demasiado largo. Máximo 10 dígitos.']);
    exit;
}

if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Correo electrónico inválido']);
    exit;
}

// Validar contraseñas
if ($contrasena !== $confirmar) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

// Validar longitud de contraseña
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
    
    // Insertar nuevo usuario
    $rol = 'usuario';
    $cargos = 'sin definir';
    $estado = 'ACTIVO';
    $fecha_creacion = date('Y-m-d H:i:s');
    $num_telefono = '0000000000'; // Temporal
    
    $sql = "INSERT INTO usuarios (num_doc, nombre, apellido, rol, cargos, correo, contrasena, num_telefono, fecha_creacion, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssss",
        $numeroDocumento, // Ya no usar filter_var con FILTER_VALIDATE_INT
        $nombre,
        $apellido,
        $rol,
        $cargos,
        $email,
        $hash,
        $num_telefono,
        $fecha_creacion,
        $estado
    );
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Usuario registrado con éxito']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al registrar usuario: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>