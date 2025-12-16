<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die("Usuario no autenticado.");
}

$usuarioId = $_SESSION['usuario_id'];
$conexion = ConectarDB();

// Configura estos valores según tu base de datos
$host = 'localhost';
$user = 'root';
$password = 'tu_contraseña';
$database = 'arco_bdd';

// Nombre del archivo
$fecha = date("Ymd_His");
$nombreArchivo = "backup_{$usuarioId}_{$fecha}.sql";
$rutaArchivo = __DIR__ . "/../respaldos/$nombreArchivo"; // Asegúrate que exista la carpeta 'respaldos'

// Comando para generar el respaldo (requiere que `mysqldump` esté instalado y en el PATH del servidor)
$comando = "mysqldump -h $host -u $user -p$password $database > $rutaArchivo";
exec($comando, $output, $resultado);

if ($resultado === 0) {
    // Actualizar la fecha de última copia
    $sql = "UPDATE copias_seguridad SET ultima_copia = NOW() WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();

    header("Location: ../vistas/configuracion.php?success=Copia de seguridad creada con éxito");
} else {
    header("Location: ../vistas/configuracion.php?error=Error al crear copia de seguridad");
}
exit;
