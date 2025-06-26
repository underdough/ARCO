<?php
require_once 'conexion.php';
$conexion = ConectarDB();
require_once 'librerias/tcpdf/tcpdf.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de reporte inválido');
}

$reporteId = (int)$_GET['id'];

// Obtener información del reporte
$query = "SELECT * FROM reportes_generados WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param('i', $reporteId);
$stmt->execute();
$result = $stmt->get_result();
$reporte = $result->fetch_assoc();

if (!$reporte) {
    die('Reporte no encontrado');
}

// Regenerar el reporte basado en los parámetros guardados
$reportType = $reporte['tipo_reporte'];
$dateFrom = $reporte['fecha_inicio'];
$dateTo = $reporte['fecha_fin'];
$titulo = $reporte['titulo'];

// Definir consultas según el tipo de reporte
switch ($reportType) {
    case 'movimientos':
        $query = "SELECT m.id, m.tipo, m.cantidad, DATE_FORMAT(m.fecha, '%d/%m/%Y') as fecha_formateada,
                 COALESCE(mat.nombre_material, CONCAT('Producto ID: ', m.producto_id)) as producto,
                 CONCAT(u.nombre, ' ', u.apellido) as usuario
                 FROM movimientos m 
                 LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
                 LEFT JOIN materiales mat ON m.producto_id = mat.id_material
                 WHERE DATE(m.fecha) BETWEEN ? AND ? 
                 ORDER BY m.fecha DESC";
        break;
    case 'inventario':
        $query = "SELECT m.nombre_material, m.stock, COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
                 COALESCE(u.nombre_ubicacion, 'Sin ubicación') as ubicacion, m.disponibilidad
                 FROM materiales m 
                 LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                 LEFT JOIN ubicaciones u ON m.fk_ubicacion = u.id_ubicaciones 
                 ORDER BY c.nombre_cat, m.nombre_material";
        break;
    case 'sales':
        $query = "SELECT COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
                 COUNT(m.id_material) as total_productos,
                 COALESCE(SUM(m.stock), 0) as stock_total
                 FROM materiales m 
                 LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                 GROUP BY c.id_categorias, c.nombre_cat
                 ORDER BY total_productos DESC";
        break;
    case 'lowstock':
        $query = "SELECT m.nombre_material, m.stock, 
                 COALESCE(c.nombre_cat, 'Sin categoría') as categoria
                 FROM materiales m 
                 LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                 WHERE m.stock <= 10 
                 ORDER BY m.stock ASC";
        break;
    case 'useractions':
        $query = "SELECT CONCAT(u.nombre, ' ', u.apellido) as usuario, m.tipo, COUNT(*) as total_acciones,
                 DATE_FORMAT(MAX(m.fecha), '%d/%m/%Y') as ultima_accion
                 FROM movimientos m
                 LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
                 WHERE DATE(m.fecha) BETWEEN ? AND ?
                 GROUP BY u.id_usuarios, m.tipo 
                 ORDER BY usuario, m.tipo";
        break;
    default:
        die('Tipo de reporte no válido');
}

// Ejecutar consulta
if (in_array($reportType, ['movimientos', 'useractions'])) {
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ss", $dateFrom, $dateTo);
} else {
    $stmt = $conexion->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

// Crear PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema ARCO');
$pdf->SetTitle($titulo);
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, $titulo, 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
if ($dateFrom && $dateTo) {
    $pdf->Cell(0, 5, 'Período: ' . date('d/m/Y', strtotime($dateFrom)) . ' - ' . date('d/m/Y', strtotime($dateTo)), 0, 1, 'R');
}
$pdf->Ln(10);

// Crear tabla HTML
$html = '<table border="1" cellpadding="4" style="border-collapse: collapse;">';

// Headers dinámicos según el tipo de reporte
switch ($reportType) {
    case 'movimientos':
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th>ID</th><th>Producto</th><th>Cantidad</th><th>Tipo</th><th>Fecha</th><th>Usuario</th>';
        $html .= '</tr>';
        break;
    case 'inventario':
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th>Material</th><th>Stock</th><th>Categoría</th><th>Ubicación</th><th>Disponible</th>';
        $html .= '</tr>';
        break;
    case 'sales':
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th>Categoría</th><th>Total Productos</th><th>Stock Total</th>';
        $html .= '</tr>';
        break;
    case 'lowstock':
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th>Material</th><th>Stock Actual</th><th>Categoría</th>';
        $html .= '</tr>';
        break;
    case 'useractions':
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th>Usuario</th><th>Tipo Acción</th><th>Total Acciones</th><th>Última Acción</th>';
        $html .= '</tr>';
        break;
}

while ($row = $result->fetch_assoc()) {
    $html .= '<tr>';
    foreach ($row as $value) {
        $html .= '<td>' . htmlspecialchars($value ?? 'N/A') . '</td>';
    }
    $html .= '</tr>';
}

$html .= '</table>';
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');

// Mostrar PDF en el navegador
$pdf->Output($titulo . '.pdf', 'I');
?>