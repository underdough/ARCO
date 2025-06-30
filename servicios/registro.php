<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso denegado.");
}

// Validar campos obligatorios
if (
    empty($_POST['nombreCompleto']) ||
    empty($_POST['numeroDocumento']) ||
    empty($_POST['email']) ||
    empty($_POST['contrasena']) ||
    empty($_POST['confirmarContrasena'])
) {
    die("Todos los campos son obligatorios.");
}

$nombreCompleto = trim($_POST['nombreCompleto']);
$numeroDocumento = filter_var($_POST['numeroDocumento'], FILTER_VALIDATE_INT);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$contrasena = $_POST['contrasena'];
$confirmar = $_POST['confirmarContrasena'];

if ($contrasena !== $confirmar) {
    die("Las contraseñas no coinciden.");
}

$conn = ConectarDB();

// Verificar si ya existe el número de documento
$stmt = $conn->prepare("SELECT id_usuarios FROM usuarios WHERE num_doc = ?");
$stmt->bind_param("i", $numeroDocumento);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("El número de documento ya está registrado.");
}
$stmt->close();

// sirve para separar nombre y apellido si vienen juntos
$partes = explode(" ", $nombreCompleto, 2);
$nombre = $partes[0];
$apellido = isset($partes[1]) ? $partes[1] : "";


$hash = password_hash($contrasena, PASSWORD_BCRYPT);

// Insertar nuevo usuario
$rol = 'usuario';
$cargos = 'sin definir';
$estado = 'ACTIVO';
$fecha_creacion = date('Y-m-d H:i:s');

$sql = "INSERT INTO usuarios (num_doc, nombre, apellido, rol, cargos, correo, contrasena, num_telefono, fecha_creacion, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$num_telefono = '0000000000'; // Temporal, ya que no se pide en el formulario

$stmt->bind_param(
    "isssssssss",
    $numeroDocumento,
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
    header('Location: ../vistas/Usuario.php?success=' . urlencode('Usuario registrado con éxito.'));
    exit;
} else {
    die("Error al registrar usuario: " . $stmt->error);
}

$stmt->close();
$conn->close();
