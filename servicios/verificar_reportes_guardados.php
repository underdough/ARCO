<?php
require_once 'conexion.php';

// Conectar a la base de datos
$conexion = ConectarDB();

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

echo "=== VERIFICACION DE REPORTES GUARDADOS ===\n";

// Contar todos los reportes
$countQuery = "SELECT COUNT(*) as total FROM reportes_generados";
$countResult = $conexion->query($countQuery);
$totalReportes = $countResult->fetch_assoc()['total'];
echo "Total de reportes en la base de datos: $totalReportes\n\n";

// Mostrar los últimos 5 reportes
echo "Últimos 5 reportes generados:\n";
echo "=================================\n";
$selectQuery = "SELECT id, titulo, tipo_reporte, formato, fecha_generado, archivo_url 
                FROM reportes_generados 
                ORDER BY fecha_generado DESC 
                LIMIT 5";
$result = $conexion->query($selectQuery);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Título: " . $row['titulo'] . "\n";
        echo "Tipo: " . $row['tipo_reporte'] . "\n";
        echo "Formato: " . $row['formato'] . "\n";
        echo "Fecha: " . $row['fecha_generado'] . "\n";
        echo "Archivo: " . $row['archivo_url'] . "\n";
        echo "---\n";
    }
} else {
    echo "No hay reportes en la base de datos.\n";
}

// Verificar reportes por tipo
echo "\nReportes por tipo:\n";
echo "==================\n";
$tiposQuery = "SELECT tipo_reporte, COUNT(*) as cantidad 
               FROM reportes_generados 
               GROUP BY tipo_reporte 
               ORDER BY cantidad DESC";
$tiposResult = $conexion->query($tiposQuery);

if ($tiposResult->num_rows > 0) {
    while ($row = $tiposResult->fetch_assoc()) {
        echo $row['tipo_reporte'] . ": " . $row['cantidad'] . " reportes\n";
    }
} else {
    echo "No hay datos de tipos de reportes.\n";
}

$conexion->close();
echo "\n=== FIN DE LA VERIFICACION ===\n";
?>