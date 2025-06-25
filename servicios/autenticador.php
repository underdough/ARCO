<?php
include "conexion.php";
session_start();

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

// Capturar los datos del formulario
$num_documento = filter_var($_POST['numeroDocumento'], FILTER_VALIDATE_INT);
$contrasena = $_POST['contrasena'];


// 🟡 REGISTRO EN LOG DE DEPURACIÓN
file_put_contents('debug_login.txt', "Intentando login con: $num_documento / $contrasena\n", FILE_APPEND);

$conexiondb = ConectarDB();

// Usar prepared statements
$stmt = $conexiondb->prepare("SELECT * FROM usuarios WHERE num_doc = ? AND contrasena = ?");
$stmt->bind_param("is", $num_documento, $contrasena);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $hallado = $result->fetch_assoc();

    $_SESSION['usuario_id'] = $hallado['id_usuarios']; // <-- Usa el nombre correcto del campo

    // 🟢 También lo registramos en el log
    file_put_contents('debug_login.txt', "Login correcto - usuario_id: " . $_SESSION['usuario_id'] . "\n", FILE_APPEND);

    header("Location: ../vistas/dashboard.php");
    exit;
} else {
    file_put_contents('debug_login.txt', "Login fallido para documento: $num_documento\n", FILE_APPEND);
    echo "Usted no se encuentra registrado en la base de datos. Por favor comuníquese con un administrador";
    echo '<a href="/ARCO/login.html">Volver al inicio</a>';
}
