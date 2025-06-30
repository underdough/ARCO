<?php
session_start();
include 'conexion.php';


if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo "Método no permitido";
    exit();
}

// Sanitizar datos (aunque estén vacíos)
$nombreEmpresa   = htmlspecialchars(trim($_POST['companyName']    ?? ''));
$idEmpresa       = htmlspecialchars(trim($_POST['companyTaxId']   ?? ''));
$dirEmpresa      = htmlspecialchars(trim($_POST['companyAddress'] ?? ''));
$ciudadEmpresa   = htmlspecialchars(trim($_POST['companyCity']    ?? ''));
$numeroEmpresa   = htmlspecialchars(trim($_POST['companyPhone']   ?? ''));
$correoEmpresa   = htmlspecialchars(trim($_POST['companyEmail']   ?? ''));

// Conexión
$conexion = conectarDB();

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Log para depuración
file_put_contents(__DIR__ . "/log_empresa.txt", "Procesando empresa: $nombreEmpresa, $idEmpresa, $dirEmpresa, $ciudadEmpresa, $numeroEmpresa, $correoEmpresa\n", FILE_APPEND);

// ¿Ya existe el registro con ID 1?
$sqlCheck = "SELECT id FROM empresa WHERE id = 2";
$result = $conexion->query($sqlCheck);

if ($result && $result->num_rows > 0) {
    // Ya existe, hacer UPDATE
    $sql = "UPDATE empresa SET 
                nombre = ?, 
                nif = ?, 
                direccion = ?, 
                ciudad = ?, 
                telefono = ?, 
                email = ?
            WHERE id = 2";

    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die("Error en prepare (UPDATE): " . $conexion->error);
    }

    $stmt->bind_param("ssssss", $nombreEmpresa, $idEmpresa, $dirEmpresa, $ciudadEmpresa, $numeroEmpresa, $correoEmpresa);
    $accion = "actualizados";
} else {
    // No existe, hacer INSERT con id = 1
    $sql = "INSERT INTO empresa (id, nombre, nif, direccion, ciudad, telefono, email) 
            VALUES (2, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die("Error en prepare (INSERT): " . $conexion->error);
    }

    $stmt->bind_param("ssssss", $nombreEmpresa, $idEmpresa, $dirEmpresa, $ciudadEmpresa, $numeroEmpresa, $correoEmpresa);
    $accion = "insertados";
}

// Ejecutar
if ($stmt->execute()) {
    $_SESSION['success_message'] = "Datos $accion correctamente.";
} else {
    $_SESSION['error_message'] = "Error al guardar: " . $stmt->error;
}

$stmt->close();
$conexion->close();

// Redirigir
header("Location: ../vistas/configuracion.php");
exit();
