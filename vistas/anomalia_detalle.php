<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}

$anomalia_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($anomalia_id <= 0) {
    die("ID de anomalía inválido");
}

$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Detalle de Anomalía</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .detalle-container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .detalle-header {
            background: linear-gradient(135deg, #395886 0%, #2d4a73 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .detalle-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .codigo-seguimiento {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .detalle-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detalle-main {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .detalle-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            color: #395886;
            margin: 0 0 15px 0;
            font-size: 16px;
            border-bottom: 2px solid #395886;
            padding-bottom: 8px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
        }
        
        .info-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-urgente {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-media {
            background: #fff8e1;
            color: #f57f17;
        }
        
        .badge-baja {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .badge-abierta {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-en_proceso {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .badge-resuelta {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .badge-cerrada {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .descripcion-section {
            margin-bottom: 30px;
        }
        
        .descripcion-section h2 {
            color: #395886;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .descripcion-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #395886;
            line-height: 1.6;
        }
        
        .acciones-rapidas {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #395886;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2d4a73;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #e67e22;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .materiales-section {
            margin-top: 20px;
        }
        
        .materiales-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .error {
            text-align: center;
            padding: 40px;
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .detalle-grid {
                grid-template-columns: 1fr;
            }
            
            .acciones-rapidas {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h1>ARCO</h1>
            <p class="subtlo">Gestión de Inventario</p>
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
            <a href="usuario.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="configuracion.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="anomalias.php" class="menu-item active">
                <i class="fas fa-exclamation-circle"></i>
                <span class="menu-text">Anomalías</span>
            </a>
            <a href="anomalias_reportes.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span class="menu-text">Reportes Anomalías</span>
            </a>
            <a href="../servicios/logout.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Detalle de Anomalía</h2>
            <div class="user-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                <span>Hola, <strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>
        
        <div class="detalle-container">
            <div id="loadingIndicator" class="loading">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Cargando información de la anomalía...</p>
            </div>
            
            <div id="errorIndicator" class="error" style="display: none;">
                <i class="fas fa-exclamation-triangle" style="font-size: 32px;"></i>
                <p>Error al cargar la información de la anomalía</p>
                <button class="btn btn-primary" onclick="location.href='anomalias.php'">
                    <i class="fas fa-arrow-left"></i> Volver a Anomalías
                </button>
            </div>
            
            <div id="contentContainer" style="display: none;">
                <!-- Header -->
                <div class="detalle-header">
                    <h1 id="tituloAnomalia">Cargando...</h1>
                    <div class="codigo-seguimiento" id="codigoSeguimiento">
                        <i class="fas fa-barcode"></i> Cargando...
                    </div>
                </div>
                
                <!-- Grid Principal -->
                <div class="detalle-grid">
                    <!-- Contenido Principal -->
                    <div class="detalle-main">
                        <!-- Descripción -->
                        <div class="descripcion-section">
                            <h2><i class="fas fa-align-left"></i> Descripción Detallada</h2>
                            <div class="descripcion-content" id="descripcionAnomalia">
                                Cargando descripción...
                            </div>
                        </div>
                        
                        <!-- Materiales Afectados -->
                        <div class="materiales-section" id="materialesSection" style="display: none;">
                            <h2><i class="fas fa-boxes"></i> Materiales/Productos Afectados</h2>
                            <div class="materiales-content" id="materialesAfectados">
                                -
                            </div>
                        </div>
                        
                        <!-- Acciones Rápidas -->
                        <div style="margin-top: 30px;">
                            <h2><i class="fas fa-tools"></i> Acciones Disponibles</h2>
                            <div class="acciones-rapidas">
                                <button class="btn btn-primary" onclick="editarAnomalia()">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-success" onclick="verSeguimiento()">
                                    <i class="fas fa-eye"></i> Ver Seguimiento
                                </button>
                                <button class="btn btn-warning" onclick="imprimirDetalle()">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                                <a href="anomalias.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="detalle-sidebar">
                        <!-- Información General -->
                        <div class="info-card">
                            <h3><i class="fas fa-info-circle"></i> Información General</h3>
                            <div class="info-item">
                                <span class="info-label">Estado:</span>
                                <span class="info-value" id="estadoAnomalia">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Prioridad:</span>
                                <span class="info-value" id="prioridadAnomalia">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Impacto:</span>
                                <span class="info-value" id="impactoAnomalia">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Categoría:</span>
                                <span class="info-value" id="categoriaAnomalia">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ubicación:</span>
                                <span class="info-value" id="ubicacionAnomalia">-</span>
                            </div>
                        </div>
                        
                        <!-- Responsables -->
                        <div class="info-card">
                            <h3><i class="fas fa-users"></i> Responsables</h3>
                            <div class="info-item">
                                <span class="info-label">Creado por:</span>
                                <span class="info-value" id="creadorAnomalia">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Asignado a:</span>
                                <span class="info-value" id="responsableAnomalia">-</span>
                            </div>
                        </div>
                        
                        <!-- Fechas -->
                        <div class="info-card">
                            <h3><i class="fas fa-calendar"></i> Fechas Importantes</h3>
                            <div class="info-item">
                                <span class="info-label">Creación:</span>
                                <span class="info-value" id="fechaCreacion">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Última actualización:</span>
                                <span class="info-value" id="fechaActualizacion">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Resolución:</span>
                                <span class="info-value" id="fechaResolucion">-</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Días transcurridos:</span>
                                <span class="info-value" id="diasTranscurridos">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const anomaliaId = <?php echo $anomalia_id; ?>;
        let anomaliaData = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            cargarAnomalia();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
        
        async function cargarAnomalia() {
            try {
                const response = await fetch(`../servicios/obtener_anomalia.php?id=${anomaliaId}`);
                const data = await response.json();
                
                if (data.success) {
                    anomaliaData = data.anomalia;
                    mostrarAnomalia(anomaliaData);
                } else {
                    mostrarError('Error al cargar la anomalía: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión al cargar la anomalía');
            }
        }
        
        function mostrarAnomalia(anomalia) {
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('contentContainer').style.display = 'block';
            
            // Header
            document.getElementById('tituloAnomalia').textContent = anomalia.titulo;
            document.getElementById('codigoSeguimiento').innerHTML = `
                <i class="fas fa-barcode"></i> ${anomalia.codigo_seguimiento || 'ANO-' + anomalia.id}
            `;
            
            // Descripción
            document.getElementById('descripcionAnomalia').textContent = anomalia.descripcion;
            
            // Información General
            document.getElementById('estadoAnomalia').innerHTML = `<span class="badge badge-${anomalia.estado}">${anomalia.estado.replace('_', ' ').toUpperCase()}</span>`;
            document.getElementById('prioridadAnomalia').innerHTML = `<span class="badge badge-${anomalia.prioridad}">${anomalia.prioridad.toUpperCase()}</span>`;
            document.getElementById('impactoAnomalia').innerHTML = anomalia.impacto ? `<span class="badge badge-${anomalia.impacto}">${anomalia.impacto.toUpperCase()}</span>` : '-';
            document.getElementById('categoriaAnomalia').textContent = anomalia.categoria || '-';
            document.getElementById('ubicacionAnomalia').textContent = anomalia.ubicacion || '-';
            
            // Responsables
            document.getElementById('creadorAnomalia').textContent = anomalia.nombre_creador || 'Usuario desconocido';
            document.getElementById('responsableAnomalia').textContent = anomalia.nombre_asignado || 'Sin asignar';
            
            // Fechas
            document.getElementById('fechaCreacion').textContent = anomalia.fecha_creacion_formateada || '-';
            document.getElementById('fechaActualizacion').textContent = anomalia.fecha_actualizacion_formateada || '-';
            document.getElementById('fechaResolucion').textContent = anomalia.fecha_resolucion_formateada || 'Pendiente';
            
            // Calcular días transcurridos
            const fechaCreacion = new Date(anomalia.fecha_creacion);
            const fechaActual = anomalia.fecha_resolucion ? new Date(anomalia.fecha_resolucion) : new Date();
            const diasTranscurridos = Math.floor((fechaActual - fechaCreacion) / (1000 * 60 * 60 * 24));
            document.getElementById('diasTranscurridos').textContent = diasTranscurridos + ' días';
            
            // Materiales afectados
            if (anomalia.materiales_afectados) {
                document.getElementById('materialesSection').style.display = 'block';
                document.getElementById('materialesAfectados').textContent = anomalia.materiales_afectados;
            }
        }
        
        function editarAnomalia() {
            window.location.href = `anomalias.php?editar=${anomaliaId}`;
        }
        
        function verSeguimiento() {
            window.open(`anomalia_seguimiento.php?id=${anomaliaId}`, '_blank', 'width=1000,height=700');
        }
        
        function imprimirDetalle() {
            window.print();
        }
        
        function mostrarError(mensaje) {
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('errorIndicator').style.display = 'block';
            document.getElementById('errorIndicator').querySelector('p').textContent = mensaje;
        }
    </script>
</body>
</html>