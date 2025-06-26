<?php
require_once '../servicios/conexion.php';
$conexion = ConectarDB();
require_once '../servicios/librerias/tcpdf/tcpdf.php';

// Función para obtener datos en tiempo real
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
    $reportType = $_POST['reportType'];
    $dateFrom = $_POST['reportDateFrom'];
    $dateTo = $_POST['reportDateTo'];
    $format = $_POST['reportFormat'];
    $reportNotes = $_POST['reportNotes'] ?? '';

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
            $stmt->bind_param("ss", $dateFrom, $dateTo);
        } else {
            $stmt = $conexion->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        // Guardar reporte en la base de datos
        $insertQuery = "INSERT INTO reportes_generados (titulo, descripcion, tipo_reporte, formato, fecha_inicio, fecha_fin, archivo_url) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conexion->prepare($insertQuery);
        $archivoUrl = "reportes/" . uniqid() . ".pdf";
        $insertStmt->bind_param("sssssss", $titulo, $reportNotes, $reportType, $format, $dateFrom, $dateTo, $archivoUrl);
        $insertStmt->execute();

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

        if ($format === 'pdf') {
            $pdf->Output($titulo . '.pdf', 'I');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Reportes</title>
    <link rel="stylesheet" href="../componentes/reportes.css">
    <link rel="stylesheet" href="../public/componentes/global.css">
    <!-- Añadir FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>ARCO</h1>
            <p class="subtitle">Gestión de Inventario</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.html" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="productos.html" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.html" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.html" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="usuarios.html" class="menu-item">
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
            <a href="../login.html" class="menu-item">
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
        
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Movimientos de Inventario en Tiempo Real</h3>
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
                <h3 class="chart-title">Productos por Categoría en Tiempo Real</h3>
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
            $queryReportes = "SELECT * FROM reportes_generados ORDER BY fecha_generado DESC LIMIT 8";
            $resultReportes = $conexion->query($queryReportes);
            
            if ($resultReportes && $resultReportes->num_rows > 0) {
                while ($reporte = $resultReportes->fetch_assoc()) {
                    $fechaGenerado = date('d/m/Y', strtotime($reporte['fecha_generado']));
                    echo '<div class="report-card">';
                    echo '<div class="report-header">';
                    echo '<h4 class="report-title">' . htmlspecialchars($reporte['titulo']) . '</h4>';
                    echo '</div>';
                    echo '<div class="report-body">';
                    echo '<p class="report-description">' . htmlspecialchars($reporte['descripcion'] ?: 'Reporte generado automáticamente') . '</p>';
                    echo '<span class="report-type">Tipo: ' . ucfirst($reporte['tipo_reporte']) . '</span>';
                    echo '</div>';
                    echo '<div class="report-footer">';
                    echo '<span class="report-date">Generado: ' . $fechaGenerado . '</span>';
                    echo '<div class="report-actions">';
                    echo '<button class="btn btn-secondary" onclick="verReporte(' . $reporte['id'] . ')">';
                    echo '<i class="fas fa-eye"></i>';
                    echo '</button>';
                    echo '<button class="btn btn-secondary" onclick="descargarReporte(' . $reporte['id'] . ')">';
                    echo '<i class="fas fa-download"></i>';
                    echo '</button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-reports">';
                echo '<p>No hay reportes guardados. Genera tu primer reporte usando el botón "Generar Nuevo Reporte".</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    
    <!-- Modal para generar reporte -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Generar Nuevo Reporte</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="reportForm" method="post">
                <div class="form-group">
                    <label for="reportType">Tipo de Reporte</label>
                    <select class="form-control" id="reportType" name="reportType" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="inventario">Inventario Actual</option>
                        <option value="movimientos">Movimientos de Inventario</option>
                        <option value="sales">Ventas por Categoría</option>
                        <option value="lowstock">Productos con Stock Bajo</option>
                        <option value="useractions">Acciones por Usuario</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="reportDateFrom">Desde</label>
                        <input type="date" class="form-control" id="reportDateFrom" name="reportDateFrom" required>
                    </div>
                    <div class="form-group">
                        <label for="reportDateTo">Hasta</label>
                        <input type="date" class="form-control" id="reportDateTo" name="reportDateTo" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reportFormat">Formato</label>
                    <select class="form-control" id="reportFormat" name="reportFormat" required>
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="reportNotes">Notas</label>
                    <textarea class="form-control" id="reportNotes" name="reportNotes" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelReport">Cancelar</button>
                    <button type="submit" class="btn btn-primary" name="generar_reporte">Generar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // JavaScript para funcionalidad interactiva y tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('reportModal');
            const btnGenerateReport = document.getElementById('btnGenerateReport');
            const btnCancelReport = document.getElementById('btnCancelReport');
            const closeModal = document.querySelector('.close-modal');
            
            // Establecer fechas por defecto (último mes)
            const today = new Date();
            const lastMonth = new Date();
            lastMonth.setMonth(today.getMonth() - 1);
            
            document.getElementById('reportDateFrom').value = lastMonth.toISOString().split('T')[0];
            document.getElementById('reportDateTo').value = today.toISOString().split('T')[0];
            
            // Funciones para datos en tiempo real
            function cargarDatosEnTiempoReal(tipo, containerId, updateId) {
                fetch(`?ajax=true&tipo=${tipo}`)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById(containerId);
                        const updateIndicator = document.getElementById(updateId);
                        
                        if (data.length === 0) {
                            container.innerHTML = '<p class="no-data">No hay datos disponibles</p>';
                            return;
                        }
                        
                        let html = '';
                        
                        switch (tipo) {
                            case 'movimientos':
                                html = '<div class="data-table"><table class="table"><thead><tr><th>Producto</th><th>Cantidad</th><th>Tipo</th><th>Fecha</th><th>Usuario</th></tr></thead><tbody>';
                                data.forEach(item => {
                                    const tipoClass = item.tipo === 'entrada' ? 'entrada' : 'salida';
                                    html += `<tr><td>${item.producto}</td><td>${item.cantidad}</td><td><span class="badge ${tipoClass}">${item.tipo}</span></td><td>${item.fecha_formateada}</td><td>${item.usuario}</td></tr>`;
                                });
                                html += '</tbody></table></div>';
                                break;
                                
                            case 'productos_categoria':
                                html = '<div class="data-table"><table class="table"><thead><tr><th>Categoría</th><th>Total Productos</th><th>Stock Total</th></tr></thead><tbody>';
                                data.forEach(item => {
                                    html += `<tr><td>${item.categoria || 'Sin categoría'}</td><td>${item.total_productos}</td><td>${item.stock_total || 0}</td></tr>`;
                                });
                                html += '</tbody></table></div>';
                                break;
                                
                            case 'stock_bajo':
                                html = '<div class="data-table"><table class="table"><thead><tr><th>Material</th><th>Stock Actual</th><th>Categoría</th><th>Estado</th></tr></thead><tbody>';
                                data.forEach(item => {
                                    const alertClass = item.stock <= 5 ? 'critical' : 'warning';
                                    html += `<tr class="${alertClass}"><td>${item.nombre_material}</td><td>${item.stock}</td><td>${item.categoria || 'Sin categoría'}</td><td><span class="badge ${alertClass}">${item.stock <= 5 ? 'Crítico' : 'Bajo'}</span></td></tr>`;
                                });
                                html += '</tbody></table></div>';
                                break;
                        }
                        
                        container.innerHTML = html;
                        updateIndicator.textContent = `Última actualización: ${new Date().toLocaleTimeString()}`;
                    })
                    .catch(error => {
                        console.error('Error al cargar datos:', error);
                        document.getElementById(containerId).innerHTML = '<p class="error">Error al cargar los datos</p>';
                    });
            }
            
            // Cargar datos iniciales
            cargarDatosEnTiempoReal('movimientos', 'movimientosContainer', 'lastUpdateMovimientos');
            cargarDatosEnTiempoReal('productos_categoria', 'categoriasContainer', 'lastUpdateCategorias');
            cargarDatosEnTiempoReal('stock_bajo', 'stockBajoContainer', 'lastUpdateStock');
            
            // Actualización automática cada 30 segundos
            setInterval(() => {
                cargarDatosEnTiempoReal('movimientos', 'movimientosContainer', 'lastUpdateMovimientos');
                cargarDatosEnTiempoReal('productos_categoria', 'categoriasContainer', 'lastUpdateCategorias');
                cargarDatosEnTiempoReal('stock_bajo', 'stockBajoContainer', 'lastUpdateStock');
            }, 30000);
            
            // Botones de actualización manual
            document.getElementById('refreshMovimientos').addEventListener('click', () => {
                cargarDatosEnTiempoReal('movimientos', 'movimientosContainer', 'lastUpdateMovimientos');
            });
            
            document.getElementById('refreshCategorias').addEventListener('click', () => {
                cargarDatosEnTiempoReal('productos_categoria', 'categoriasContainer', 'lastUpdateCategorias');
            });
            
            document.getElementById('refreshStockBajo').addEventListener('click', () => {
                cargarDatosEnTiempoReal('stock_bajo', 'stockBajoContainer', 'lastUpdateStock');
            });
            
            // Funcionalidad del modal
            btnGenerateReport.addEventListener('click', function() {
                modal.style.display = 'flex';
            });
            
            function closeReportModal() {
                modal.style.display = 'none';
            }
            
            btnCancelReport.addEventListener('click', closeReportModal);
            closeModal.addEventListener('click', closeReportModal);
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeReportModal();
                }
            });
            
            // Manejar envío del formulario
            document.getElementById('reportForm').addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
                submitBtn.disabled = true;
                
                // El formulario se enviará normalmente para generar el PDF
                setTimeout(() => {
                    submitBtn.innerHTML = 'Generar';
                    submitBtn.disabled = false;
                    closeReportModal();
                    // Recargar la página para mostrar el nuevo reporte en la lista
                    setTimeout(() => location.reload(), 1000);
                }, 2000);
            });
        });
        
        // Funciones globales para los botones de reportes guardados
        function verReporte(id) {
            alert(`Funcionalidad de visualización del reporte ${id} - En desarrollo`);
        }
        
        function descargarReporte(id) {
            alert(`Funcionalidad de descarga del reporte ${id} - En desarrollo`);
        }
    </script>
</body>
</html>