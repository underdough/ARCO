<?php
header('Content-Type: application/json');

// Simulated database connection
$db = new PDO('mysql:host=localhost;dbname=arco_bdd', 'root', '');

// Obtener tipo de reporte, fechas y formato desde los datos POST
$reportType = $_POST['reportType'] ?? '';
$dateFrom = $_POST['reportDateFrom'] ?? '';
$dateTo = $_POST['reportDateTo'] ?? '';
$format = $_POST['reportFormat'] ?? '';

// Generar reporte basado en el tipo
switch ($reportType) {

    case 'movimientos':
        $query = "SELECT * FROM movimientos WHERE date BETWEEN ? AND ?";
        break;
    case 'ventas':
        $query = "SELECT categorias, SUM(ventas) AS total_ventas FROM ventas WHERE date BETWEEN ? AND ? GROUP BY categoria";
        break;
    case 'bajo_stock':
        $query = "SELECT * FROM materiales WHERE stock < 10 AND date BETWEEN ? AND ?";
        break;
    // case 'useractions':
    //     $query = "SELECT * FROM user_actions WHERE date BETWEEN ? AND ?";
    //     break;
    default:
        echo json_encode(['error' => 'Tipo de reporte inválido']);
        exit;
}

try {
    $stmt = $db->prepare($query);
    $stmt->execute([$dateFrom, $dateTo]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ejemplo de generación de un reporte en PDF (usando TCPDF)
    if ($format === 'pdf') {
        include('../servicios/tcpdf/examples/t  cpdf_include.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->writeHTML($this->generateHtmlTable($data));

        $pdf->Output('reporte.pdf', 'I');
    } else {
        echo json_encode(['data' => $data]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}