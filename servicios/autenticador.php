<?php
session_start();
include "conexion.php";

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    echo "Tu petición ha sido rechazada";
    exit;
}

if (
    empty($_POST['numeroDocumento']) ||
    empty($_POST['contrasena'])
) {
    echo "Hay datos errados";
    exit;
}

$num_documento = filter_var($_POST['numeroDocumento'], FILTER_VALIDATE_INT);
$contrasena = $_POST['contrasena'];

$conexiondb = ConectarDB();

//  se busca al usuario solo por num_doc
$stmt = $conexiondb->prepare("SELECT * FROM usuarios WHERE num_doc = ?");
$stmt->bind_param("i", $num_documento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();

    // Verificar contraseña con password_verify
    $hashBD = $usuario['contrasena'];

if (
    password_verify($contrasena, $hashBD) || // para nuevos usuarios
    $contrasena === $hashBD                  // para antiguos en texto plano
) {
    $_SESSION['usuario_id'] = $usuario['id_usuarios'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['rol'] = $usuario['rol'];

    header("Location: ../vistas/dashboard.php");
    exit;
} else {
    echo "Contraseña incorrecta.";
}

} else {
    echo "Usted no se encuentra registrado en la base de datos. Por favor comuníquese con un administrador";
    echo '<a href="/ARCO/login.html">Volver al inicio</a>';
}
