<?php
require_once '../servicios/conexion.php';
$conexion = ConectarDB();
// TCPDF se carga bajo demanda al momento de generar/visualizar un PDF

session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Usuario no autenticado, redirigir al login
    header("Location: ../login.html");
    exit;
}
// Verificar si se generó un reporte exitosamente
$reporteGenerado = false;
if (isset($_SESSION['reporte_generado']) && $_SESSION['reporte_generado'] === true) {
    $reporteGenerado = true;
    unset($_SESSION['reporte_generado']); // Limpiar la sesión
}

// Función para obtener datos desde la base de datos

function obtenerDatosEnTiempoReal($conexion, $tipo = 'movimientos') {
    switch ($tipo) {
        case 'movimientos':
            $query = "SELECT m.id, m.tipo, m.fecha, m.producto_id, m.cantidad, m.usuario_id, m.notas,
                    DATE_FORMAT(m.creado_en, '%d/%m/%Y %H:%i') as fecha_formateada,
                    u.nombre as usuario,
                    COALESCE(mat.nombre_material, CONCAT('Producto ID: ', m.producto_id)) as producto
                    FROM movimientos m 
                    LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
                    LEFT JOIN materiales mat ON m.producto_id = mat.id_material
                    ORDER BY m.creado_en DESC LIMIT 10";
            break;
        case 'productos_categoria':
            $query = "SELECT COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
                    COUNT(m.id_material) as total_productos,
                    COALESCE(SUM(m.stock), 0) as stock_total
                    FROM materiales m 
                    LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                    GROUP BY c.id_categorias, c.nombre_cat
                    ORDER BY total_productos DESC";
            break;
        case 'stock_bajo':
            $query = "SELECT m.nombre_material, m.stock, 
                    COALESCE(c.nombre_cat, 'Sin categoría') as categoria
                    FROM materiales m 
                    LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                    WHERE m.stock <= 10 
                    ORDER BY m.stock ASC";
            break;
        case 'categorias':
            $query = "SELECT c.id_categorias, c.nombre_cat, c.subcategoria as descripcion, 
                    COUNT(m.id_material) as total_productos,
                    COALESCE(SUM(m.stock), 0) as stock_total
                    FROM categorias c 
                    LEFT JOIN materiales m ON c.id_categorias = m.id_categorias 
                    GROUP BY c.id_categorias, c.nombre_cat, c.subcategoria
                    ORDER BY c.nombre_cat";
            break;
        default:
            return [];
    }
    
    $result = $conexion->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Manejo de peticiones AJAX
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    $tipo = $_GET['tipo'] ?? 'movimientos';
    $datos = obtenerDatosEnTiempoReal($conexion, $tipo);
    echo json_encode($datos);
    exit;
}

if (isset($_POST['generar_reporte'])) {
    // Debug completo de datos recibidos
    $debug_log = "=== FORM SUBMISSION DEBUG ===\n";
    $debug_log .= "POST data: " . print_r($_POST, true) . "\n";
    $debug_log .= "generar_reporte isset: " . (isset($_POST['generar_reporte']) ? 'YES' : 'NO') . "\n";
    file_put_contents('../debug_form.log', $debug_log . "\n", FILE_APPEND);
    
    error_log("=== FORM SUBMISSION DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("generar_reporte isset: " . (isset($_POST['generar_reporte']) ? 'YES' : 'NO'));
    
    $reportType = $_POST['reportType'] ?? 'NO_DEFINIDO';
    $dateFrom = $_POST['reportDateFrom'] ?? 'NO_DEFINIDO';
    $dateTo = $_POST['reportDateTo'] ?? 'NO_DEFINIDO';
    $format = $_POST['reportFormat'] ?? 'NO_DEFINIDO';
    $reportNotes = $_POST['reportNotes'] ?? '';
    
    // Debug: Mostrar datos recibidos
    error_log("Generando reporte - Tipo: $reportType, Desde: $dateFrom, Hasta: $dateTo, Formato: $format");
    error_log("Notas: $reportNotes");
    
    // Validar datos críticos
    if ($reportType === 'NO_DEFINIDO') {
        error_log("ERROR: Tipo de reporte no definido");
        echo "<script>alert('Error: Tipo de reporte no seleccionado'); window.location.href='reportes.php';</script>";
        exit;
    }
    if ($format === 'NO_DEFINIDO') {
        error_log("ERROR: Formato no definido");
        echo "<script>alert('Error: Formato no seleccionado'); window.location.href='reportes.php';</script>";
        exit;
    }
    
    // Validar fechas para reportes que las requieren
    if (in_array($reportType, ['movimientos', 'useractions'])) {
        if (empty($dateFrom) || empty($dateTo)) {
            echo "<script>alert('Las fechas son requeridas para este tipo de reporte.'); window.location.href='reportes.php';</script>";
            exit;
        }
    }

    // Lógica mejorada para generar reportes
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
            $titulo = "Reporte de Movimientos de Inventario";
            break;
        case 'inventario':
            $query = "SELECT m.nombre_material, m.stock, c.nombre_cat as categoria, 
                    u.nombre_ubicacion, m.disponibilidad
                    FROM materiales m 
                    LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                    LEFT JOIN ubicaciones u ON m.fk_ubicacion = u.id_ubicaciones 
                    ORDER BY c.nombre_cat, m.nombre_material";
            $titulo = "Reporte de Inventario Actual";
            break;
        case 'sales':
            $query = "SELECT COALESCE(c.nombre_cat, 'Sin categoría') as categoria, 
                    COUNT(m.id_material) as total_productos,
                    COALESCE(SUM(m.stock), 0) as stock_total
                    FROM materiales m 
                    LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                    GROUP BY c.id_categorias, c.nombre_cat
                    ORDER BY total_productos DESC";
            $titulo = "Reporte de Productos por Categoría";
            break;
        case 'lowstock':
            $query = "SELECT m.nombre_material, m.stock, 
                    COALESCE(c.nombre_cat, 'Sin categoría') as categoria
                    FROM materiales m 
                    LEFT JOIN categorias c ON m.id_categorias = c.id_categorias 
                    WHERE m.stock <= 10 
                    ORDER BY m.stock ASC";
            $titulo = "Reporte de Stock Bajo";
            break;
        case 'useractions':
            $query = "SELECT CONCAT(u.nombre, ' ', u.apellido) as usuario, m.tipo, COUNT(*) as total_acciones,
                    DATE_FORMAT(MAX(m.fecha), '%d/%m/%Y') as ultima_accion
                    FROM movimientos m
                    LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
                    WHERE DATE(m.fecha) BETWEEN ? AND ?
                    GROUP BY u.id_usuarios, m.tipo 
                    ORDER BY usuario, m.tipo";
            $titulo = "Reporte de Acciones por Usuario";
            break;
        default:
            $query = "";
            $titulo = "Reporte";
            break;
    }

    if (!empty($query)) {
        // Preparar consulta según el tipo
        if (in_array($reportType, ['movimientos', 'useractions'])) {
            $stmt = $conexion->prepare($query);
            if (!$stmt) {
                error_log("Error preparando consulta: " . $conexion->error);
                echo "<script>alert('Error en la consulta de base de datos.'); window.location.href='reportes.php';</script>";
                exit;
            }
            $stmt->bind_param("ss", $dateFrom, $dateTo);
        } else {
            $stmt = $conexion->prepare($query);
            if (!$stmt) {
                error_log("Error preparando consulta: " . $conexion->error);
                echo "<script>alert('Error en la consulta de base de datos.'); window.location.href='reportes.php';</script>";
                exit;
            }
        }
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            echo "<script>alert('Error ejecutando la consulta.'); window.location.href='reportes.php';</script>";
            exit;
        }
        
        $result = $stmt->get_result();
        
        // Obtener todos los datos antes de generar el PDF
        $datos = [];
        while ($row = $result->fetch_assoc()) {
            $datos[] = $row;
        }
        
        error_log("Datos obtenidos: " . count($datos) . " registros");

        // Guardar reporte en la base de datos ANTES de generar el PDF
        $archivoUrl = "reportes/" . uniqid() . ".pdf";
        $insertQuery = "INSERT INTO reportes_generados (titulo, descripcion, tipo_reporte, formato, fecha_inicio, fecha_fin, archivo_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conexion->prepare($insertQuery);
        
        if (!$insertStmt) {
            error_log("Error preparando inserción: " . $conexion->error);
            echo "<script>alert('Error preparando el guardado del reporte.'); window.location.href='reportes.php';</script>";
            exit;
        }
        
        $insertStmt->bind_param("sssssss", $titulo, $reportNotes, $reportType, $format, $dateFrom, $dateTo, $archivoUrl);
        
        if (!$insertStmt->execute()) {
            error_log("Error guardando reporte: " . $insertStmt->error);
            echo "<script>alert('Error al guardar el reporte: " . addslashes($insertStmt->error) . "'); window.location.href='reportes.php';</script>";
            exit;
        }
        
        error_log("Reporte guardado exitosamente con ID: " . $conexion->insert_id);
        
        // Cerrar statement de inserción
        $insertStmt->close();

        // Verificar TCPDF e incluirlo bajo demanda
        $tcpdfPath = realpath(__DIR__ . '/../servicios/librerias/tcpdf/tcpdf.php');
        if (!$tcpdfPath || !file_exists($tcpdfPath)) {
            error_log("TCPDF no encontrado en: " . (__DIR__ . '/../servicios/librerias/tcpdf/tcpdf.php'));
            echo "<script>alert('No se encuentra la librería TCPDF. Por favor instálela en servicios/librerias/tcpdf.'); window.location.href='reportes.php';</script>";
            exit;
        }
        require_once $tcpdfPath;

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
        $pdf->Ln(10);

        // Crear tabla HTML según el tipo de reporte
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

        // Verificar si hay datos
        if (empty($datos)) {
            $html .= '<tr><td colspan="100%" style="text-align: center; color: #666;">No se encontraron datos para este reporte</td></tr>';
            error_log("No hay datos para mostrar en el reporte tipo: $reportType");
        } else {
            // Usar los datos ya obtenidos
            foreach ($datos as $row) {
                $html .= '<tr>';
                // Mostrar datos según el tipo de reporte para mantener el orden correcto
                switch ($reportType) {
                    case 'movimientos':
                        $html .= '<td>' . htmlspecialchars($row['id'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['producto'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['cantidad'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['tipo'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['fecha_formateada'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['usuario'] ?? 'N/A') . '</td>';
                        break;
                    case 'inventario':
                        $html .= '<td>' . htmlspecialchars($row['nombre_material'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['stock'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['categoria'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['nombre_ubicacion'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['disponibilidad'] ?? 'N/A') . '</td>';
                        break;
                    case 'sales':
                        $html .= '<td>' . htmlspecialchars($row['categoria'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['total_productos'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['stock_total'] ?? 'N/A') . '</td>';
                        break;
                    case 'lowstock':
                        $html .= '<td>' . htmlspecialchars($row['nombre_material'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['stock'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['categoria'] ?? 'N/A') . '</td>';
                        break;
                    case 'useractions':
                        $html .= '<td>' . htmlspecialchars($row['usuario'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['tipo'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['total_acciones'] ?? 'N/A') . '</td>';
                        $html .= '<td>' . htmlspecialchars($row['ultima_accion'] ?? 'N/A') . '</td>';
                        break;
                    default:
                        // Fallback: mostrar todos los valores
                        foreach ($row as $value) {
                            $html .= '<td>' . htmlspecialchars($value ?? 'N/A') . '</td>';
                        }
                }
                $html .= '</tr>';
            }
        }

        $html .= '</table>';
        $pdf->SetFont('helvetica', '', 9);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Agregar información adicional al PDF
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 8);
        
        // Solo agregar rango de fechas si aplica al tipo de reporte
        if (in_array($reportType, ['movimientos', 'useractions'])) {
            $pdf->Cell(0, 5, 'Rango de fechas: ' . date('d/m/Y', strtotime($dateFrom)) . ' - ' . date('d/m/Y', strtotime($dateTo)), 0, 1, 'L');
        }
        
        if (!empty($reportNotes)) {
            $pdf->Cell(0, 5, 'Notas: ' . $reportNotes, 0, 1, 'L');
        }
        $pdf->Cell(0, 5, 'Total de registros: ' . count($datos), 0, 1, 'L');
        $pdf->Cell(0, 5, 'Generado por: Sistema ARCO', 0, 1, 'L');
        
        if ($format === 'pdf') {
            // Antes de mostrar el PDF, guardar en sesión que fue exitoso
            $_SESSION['reporte_generado'] = true;
            $pdf->Output($titulo . '.pdf', 'I');
            exit; // Salir después de generar el PDF
        }
    } else {
        error_log("Error: No se pudo ejecutar la consulta o no hay datos disponibles para el tipo: $reportType");
        echo "<script>alert('Error: No se pudo ejecutar la consulta o no hay datos disponibles.'); window.location.href='reportes.php';</script>";
        exit;
    }
    
    // Esta redirección solo se ejecutará si no es formato PDF
    header("Location: reportes.php?success=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Reportes</title>
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="stylesheet" href="../componentes/reportes.css">
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>ARCO</h1>
            <p class="subtitle">Gestión de Inventario</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="productos.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.php" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="Usuario.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="reportes.php" class="menu-item active">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="configuracion.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="../servicios/logout.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h2>Reportes</h2>
            <button class="btn btn-primary" id="btnGenerateReport">
                <i class="fas fa-plus"></i> Generar Nuevo Reporte
            </button>
        </div>
        
        <?php if ((isset($_GET['success']) && $_GET['success'] == '1') || $reporteGenerado): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> ¡Reporte generado y guardado exitosamente!
        </div>
        <?php endif; ?>
        
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Movimientos de Inventario </h3>
                <div class="chart-filters">
                    <button class="btn btn-primary" id="refreshMovimientos">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <span class="update-indicator" id="lastUpdateMovimientos">Última actualización: --</span>
                </div>
            </div>
            <div class="chart-body" id="movimientosContainer">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Cargando datos...
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Productos por Categoría </h3>
                <div class="chart-filters">
                    <button class="btn btn-primary" id="refreshCategorias">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <span class="update-indicator" id="lastUpdateCategorias">Última actualización: --</span>
                </div>
            </div>
            <div class="chart-body" id="categoriasContainer">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Cargando datos...
                </div>
            </div>
        </div>
        
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Alertas de Stock Bajo</h3>
                <div class="chart-filters">
                    <button class="btn btn-warning" id="refreshStockBajo">
                        <i class="fas fa-exclamation-triangle"></i> Verificar Stock
                    </button>
                    <span class="update-indicator" id="lastUpdateStock">Última actualización: --</span>
                </div>
            </div>
            <div class="chart-body" id="stockBajoContainer">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i> Cargando datos...
                </div>
            </div>
        </div>
        
        <h3>Reportes Guardados</h3>
        
        <div class="reports-grid" id="reportesGuardados">
            <?php
            // Obtener reportes guardados de la base de datos
            $queryReportes = "SELECT id, titulo, descripcion, tipo_reporte, formato, fecha_inicio, fecha_fin, fecha_generado, archivo_url FROM reportes_generados ORDER BY fecha_generado DESC LIMIT 8";
            $resultReportes = $conexion->query($queryReportes);
            
            if ($resultReportes && $resultReportes->num_rows > 0) {
                while ($reporte = $resultReportes->fetch_assoc()) {
                    $fechaGenerado = date('d/m/Y H:i', strtotime($reporte['fecha_generado']));
                    $fechaRango = '';
                    if ($reporte['fecha_inicio'] && $reporte['fecha_fin']) {
                        $fechaRango = ' (' . date('d/m/Y', strtotime($reporte['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($reporte['fecha_fin'])) . ')';
                    }
                    echo '<div class="report-card">';
                    echo '<div class="report-header">';
                    echo '<h4 class="report-title">' . htmlspecialchars($reporte['titulo']) . '</h4>';
                    echo '</div>';
                    echo '<div class="report-body">';
                    echo '<p class="report-description">' . htmlspecialchars($reporte['descripcion'] ?: 'Reporte generado automáticamente') . '</p>';
                    echo'<br>';
                    echo '<span class="report-type">Tipo: ' . ucfirst(str_replace('_', ' ', $reporte['tipo_reporte'])) . '</span>'; 
                    echo'<br>';
                    echo'<br>';
                    echo '<span class="report-format">Formato: ' . strtoupper($reporte['formato']) . '</span>';
                    echo'<br>';
                    echo'<br>';
                    if ($fechaRango) {
                        echo '<span class="report-range">Período: ' . $fechaRango . '</span>';
                    }
                    echo '</div>';
                    echo '<div class="report-footer">';
                    echo '<span class="report-date">Generado: ' . $fechaGenerado . '</span>';
                    echo '<div class="report-actions">';
                    echo '<button class="btn btn-secondary" onclick="verReporte(' . $reporte['id'] . ')" title="Ver reporte">';
                    echo '<i class="fas fa-eye"></i>';
                    echo '</button>';
                    echo '<button class="btn btn-secondary" onclick="descargarReporte(' . $reporte['id'] . ')" title="Descargar reporte">';
                    echo '<i class="fas fa-download"></i>';
                    echo '</button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-reports">';
                echo '<p>No hay reportes generados. Genera tu primer reporte usando el botón "Generar Nuevo Reporte".</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Modal para generar reporte -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-section">
                    <i class="fas fa-chart-line modal-icon"></i>
                    <h3 class="modal-title">Generar Nuevo Reporte</h3>
                </div>
                <button class="close-modal" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="post" action="reportes.php" class="report-form">
                <div class="form-section">
                    <div class="form-group">
                        <label for="reportType">
                            <i class="fas fa-file-alt"></i>
                            Tipo de Reporte
                        </label>
                        <select class="form-control" name="reportType" id="reportType" required>
                            <option value="" disabled selected>Seleccionar tipo</option>
                            <option value="inventario">📦 Inventario Actual</option>
                            <option value="movimientos">🔄 Movimientos de Inventario</option>
                            <option value="sales">📊 Ventas por Categoría</option>
                            <option value="lowstock">⚠️ Productos con Stock Bajo</option>
                            <option value="useractions">👥 Acciones por Usuario</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reportDateFrom">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de Inicio
                            </label>
                            <input type="date" class="form-control" name="reportDateFrom" id="reportDateFrom" required>
                        </div>
                        <div class="form-group">
                            <label for="reportDateTo">
                                <i class="fas fa-calendar-check"></i>
                                Fecha de Fin
                            </label>
                            <input type="date" class="form-control" name="reportDateTo" id="reportDateTo" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-group">
                        <label for="reportFormat">
                            <i class="fas fa-file-pdf"></i>
                            Formato de Salida
                        </label>
                        <select class="form-control" name="reportFormat" id="reportFormat" required>
                            <option value="pdf">📄 PDF (Recomendado)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <div class="form-group">
                        <label for="reportNotes">
                            <i class="fas fa-sticky-note"></i>
                            Notas Adicionales
                            <span class="optional-label">(Opcional)</span>
                        </label>
                        <textarea class="form-control" name="reportNotes" id="reportNotes" rows="3" placeholder="Agregar comentarios o notas sobre este reporte..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('reportModal').style.display='none'">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" name="generar_reporte">
                        <i class="fas fa-download"></i>
                        Generar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // JavaScript completo con funcionalidades restauradas
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('reportModal');
            const btnGenerateReport = document.getElementById('btnGenerateReport');
            const closeModal = document.querySelector('.close-modal');
            
            // Funcionalidad básica del modal
            if (btnGenerateReport) {
                btnGenerateReport.addEventListener('click', function() {
                    modal.style.display = 'flex';
                });
            }
            
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            }
            
            // AGREGAR ESTE BLOQUE (funcionalidad Escape):
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            });
            
            // Inicializar carga de datos
            cargarMovimientos();
            cargarCategorias();
            cargarStockBajo();
            
            // Configurar botones de actualización
            document.getElementById('refreshMovimientos')?.addEventListener('click', cargarMovimientos);
            document.getElementById('refreshCategorias')?.addEventListener('click', cargarCategorias);
            document.getElementById('refreshStockBajo')?.addEventListener('click', cargarStockBajo);
            
            // Auto-actualización cada 30 segundos
            setInterval(function() {
                cargarMovimientos();
                cargarCategorias();
                cargarStockBajo();
            }, 30000);
        });
        
        // Función para cargar movimientos 
        // Función mejorada manteniendo simplicidad
        function cargarMovimientos() {
            fetch('../servicios/obtener_reportes.php?tipo=movimientos')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('movimientosContainer');
                    if (data.length === 0) {
                        container.innerHTML = `
                            <div class="no-data" style="text-align: center; padding: 40px; color: #666;">
                                <i class="fas fa-info-circle" style="font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
                                <p style="margin: 0;">No hay movimientos recientes</p>
                            </div>`;
                    } else {
                        let html = '<div class="data-table"><table><thead><tr>';
                        html += '<th>Producto</th>';
                        html += '<th>Tipo</th>';
                        html += '<th>Cantidad</th>';
                        html += '<th>Usuario</th>';
                        html += '<th>Fecha</th>';
                        html += '</tr></thead><tbody>';
                        
                        data.forEach(item => {
                            let badgeClass = '';
                            switch((item.tipo || '').toLowerCase()) {
                                case 'entrada':
                                case 'compra':
                                case 'recepción':
                                    badgeClass = 'badge badge-success';
                                    break;
                                case 'salida':
                                case 'venta':
                                case 'consumo':
                                case 'uso':
                                    badgeClass = 'badge badge-danger';
                                    break;
                                case 'ajuste':
                                case 'corrección':
                                case 'inventario':
                                    badgeClass = 'badge badge-warning';
                                    break;
                                case 'transferencia':
                                case 'movimiento':
                                case 'traslado':
                                    badgeClass = 'badge badge-info';
                                    break;
                                default:
                                    badgeClass = 'badge badge-secondary';
                            }
                            
                            html += `<tr>
                                <td>${item.producto || 'N/A'}</td>
                                <td><span class="${badgeClass}">${item.tipo || 'N/A'}</span></td>
                                <td><strong>${item.cantidad}</strong></td>
                                <td>${item.usuario || 'N/A'}</td>
                                <td>${item.fecha_formateada}</td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                        container.innerHTML = html;
                    }
                    document.getElementById('lastUpdateMovimientos').textContent = `Última actualización: ${new Date().toLocaleTimeString()}`;
                })
                .catch(error => {
                    console.error('Error cargando movimientos:', error);
                    document.getElementById('movimientosContainer').innerHTML = `
                        <div class="error" style="text-align: center; padding: 40px; color: #dc3545;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
                            <p style="margin: 0;">Error cargando datos</p>
                        </div>`;
                });
        }
        
        // Función para cargar categorías
        function cargarCategorias() {
            fetch('../servicios/obtener_reportes.php?tipo=categorias')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('categoriasContainer');
                    if (data.length === 0) {
                        container.innerHTML = '<div class="no-data"><i class="fas fa-info-circle"></i> No hay datos de categorías</div>';
                    } else {
                        let html = '<div class="data-table"><table><thead><tr><th>Categoría</th><th>Total Productos</th><th>Stock Total</th></tr></thead><tbody>';
                        data.forEach(item => {
                            html += `<tr>
                                <td>${item.categoria}</td>
                                <td>${item.total_productos}</td>
                                <td>${item.stock_total}</td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                        container.innerHTML = html;
                    }
                    document.getElementById('lastUpdateCategorias').textContent = `Última actualización: ${new Date().toLocaleTimeString()}`;
                })
                .catch(error => {
                    console.error('Error cargando categorías:', error);
                    document.getElementById('categoriasContainer').innerHTML = '<div class="error"><i class="fas fa-exclamation-triangle"></i> Error cargando datos</div>';
                });
        }
        
        // Función para cargar stock bajo
        function cargarStockBajo() {
            fetch('../servicios/obtener_reportes.php?tipo=stock_bajo')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('stockBajoContainer');
                    if (data.length === 0) {
                        container.innerHTML = '<div class="no-data"><i class="fas fa-check-circle"></i> No hay productos con stock bajo</div>';
                    } else {
                        let html = '<div class="data-table"><table><thead><tr><th>Material</th><th>Stock Actual</th><th>Categoría</th></tr></thead><tbody>';
                        data.forEach(item => {
                            const stockClass = item.stock <= 5 ? 'stock-critical' : 'stock-warning';
                            html += `<tr>
                                <td>${item.nombre_material}</td>
                                <td><span class="${stockClass}">${item.stock}</span></td>
                                <td>${item.categoria}</td>
                            </tr>`;
                        });
                        html += '</tbody></table></div>';
                        container.innerHTML = html;
                    }
                    document.getElementById('lastUpdateStock').textContent = `Última actualización: ${new Date().toLocaleTimeString()}`;
                })
                .catch(error => {
                    console.error('Error cargando stock bajo:', error);
                    document.getElementById('stockBajoContainer').innerHTML = '<div class="error"><i class="fas fa-exclamation-triangle"></i> Error cargando datos</div>';
                });
        }
        
        // Funciones para manejar reportes guardados
        function verReporte(id) {
            // Abrir reporte en nueva ventana
            window.open(`../servicios/ver_reporte.php?id=${id}`, '_blank');
        }
        
        function descargarReporte(id) {
            // Descargar reporte
            window.location.href = `../servicios/descargar_reporte.php?id=${id}`;
        }    </script>
</body>
</html>