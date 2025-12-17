<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../servicios/menu_dinamico.php';

$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Reportes de Anomalías</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/reportes.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filtros-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .filtro-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filtro-group label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .filtro-group select,
        .filtro-group input {
            padding: 8px 12px;
            border: 2px solid #ecf0f1;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .filtros-acciones {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-filtrar {
            background: #395886;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-limpiar {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-exportar {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .estadisticas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .estadistica-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .estadistica-valor {
            font-size: 32px;
            font-weight: bold;
            color: #395886;
            margin-bottom: 5px;
        }
        
        .estadistica-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .tabla-reportes {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tabla-reportes table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tabla-reportes th {
            background: #395886;
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 500;
        }
        
        .tabla-reportes td {
            padding: 12px 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .tabla-reportes tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
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
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
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
        <?php echo generarMenuHTML('anomalias_reportes'); ?>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Reportes de Anomalías</h2>
            <div class="user-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                <span>Bienvenido, <strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>
        
        <div class="container">
            <!-- Filtros -->
            <div class="filtros-container">
                <h3>Filtros de Búsqueda</h3>
                <div class="filtros-grid">
                    <div class="filtro-group">
                        <label for="filtro-estado">Estado</label>
                        <select id="filtro-estado">
                            <option value="todos">Todos los estados</option>
                            <option value="abierta">Abierta</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="resuelta">Resuelta</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </div>
                    
                    <div class="filtro-group">
                        <label for="filtro-prioridad">Prioridad</label>
                        <select id="filtro-prioridad">
                            <option value="todos">Todas las prioridades</option>
                            <option value="urgente">Urgente</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                    
                    <div class="filtro-group">
                        <label for="filtro-fecha-desde">Fecha Desde</label>
                        <input type="date" id="filtro-fecha-desde">
                    </div>
                    
                    <div class="filtro-group">
                        <label for="filtro-fecha-hasta">Fecha Hasta</label>
                        <input type="date" id="filtro-fecha-hasta">
                    </div>
                    
                    <div class="filtro-group">
                        <label for="filtro-responsable">Responsable</label>
                        <select id="filtro-responsable">
                            <option value="">Todos los responsables</option>
                        </select>
                    </div>
                </div>
                
                <div class="filtros-acciones">
                    <button class="btn-limpiar" onclick="limpiarFiltros()">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                    <button class="btn-filtrar" onclick="aplicarFiltros()">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <button class="btn-exportar" onclick="exportarReporte()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="estadisticas-grid" id="estadisticasGrid">
                <!-- Se cargarán dinámicamente -->
            </div>
            
            <!-- Tabla de Resultados -->
            <div class="tabla-reportes">
                <div id="loadingIndicator" class="loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Cargando reportes...
                </div>
                
                <div id="tablaContainer" style="display: none;">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Título</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Categoría</th>
                                <th>Responsable</th>
                                <th>Fecha Creación</th>
                                <th>Días Transcurridos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaBody">
                            <!-- Se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
                
                <div id="noDataIndicator" class="no-data" style="display: none;">
                    <i class="fas fa-inbox"></i>
                    <p>No se encontraron anomalías con los filtros aplicados</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            cargarUsuarios();
            cargarReporte();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
        
        async function cargarUsuarios() {
            try {
                const response = await fetch('../servicios/listar_usuarios.php', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                const select = document.getElementById('filtro-responsable');
                select.innerHTML = '<option value="">Todos los responsables</option>';
                
                if (data.success && data.usuarios) {
                    data.usuarios.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id_usuarios;
                        option.textContent = `${usuario.nombre} ${usuario.apellido}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
            }
        }
        
        async function cargarReporte() {
            const loadingIndicator = document.getElementById('loadingIndicator');
            const tablaContainer = document.getElementById('tablaContainer');
            const noDataIndicator = document.getElementById('noDataIndicator');
            
            loadingIndicator.style.display = 'block';
            tablaContainer.style.display = 'none';
            noDataIndicator.style.display = 'none';
            
            try {
                const params = new URLSearchParams({
                    estado: document.getElementById('filtro-estado').value,
                    prioridad: document.getElementById('filtro-prioridad').value,
                    fecha_desde: document.getElementById('filtro-fecha-desde').value,
                    fecha_hasta: document.getElementById('filtro-fecha-hasta').value,
                    responsable: document.getElementById('filtro-responsable').value
                });
                
                const response = await fetch(`../servicios/reporte_anomalias.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarEstadisticas(data.estadisticas);
                    mostrarTabla(data.anomalias);
                } else {
                    console.error('Error al cargar reporte:', data.error);
                    noDataIndicator.style.display = 'block';
                }
            } catch (error) {
                console.error('Error de conexión:', error);
                noDataIndicator.style.display = 'block';
            }
            
            loadingIndicator.style.display = 'none';
        }
        
        function mostrarEstadisticas(stats) {
            const grid = document.getElementById('estadisticasGrid');
            grid.innerHTML = `
                <div class="estadistica-card">
                    <div class="estadistica-valor">${stats.total || 0}</div>
                    <div class="estadistica-label">Total Anomalías</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor">${stats.abiertas || 0}</div>
                    <div class="estadistica-label">Abiertas</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor">${stats.en_proceso || 0}</div>
                    <div class="estadistica-label">En Proceso</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor">${stats.resueltas || 0}</div>
                    <div class="estadistica-label">Resueltas</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor">${stats.urgentes || 0}</div>
                    <div class="estadistica-label">Urgentes</div>
                </div>
                <div class="estadistica-card">
                    <div class="estadistica-valor">${Math.round(stats.promedio_dias_resolucion || 0)}</div>
                    <div class="estadistica-label">Días Promedio</div>
                </div>
            `;
        }
        
        function mostrarTabla(anomalias) {
            const tablaBody = document.getElementById('tablaBody');
            const tablaContainer = document.getElementById('tablaContainer');
            const noDataIndicator = document.getElementById('noDataIndicator');
            
            if (anomalias.length === 0) {
                noDataIndicator.style.display = 'block';
                return;
            }
            
            tablaBody.innerHTML = anomalias.map(anomalia => `
                <tr>
                    <td><strong>${anomalia.codigo_seguimiento}</strong></td>
                    <td>${anomalia.titulo}</td>
                    <td><span class="badge badge-${anomalia.prioridad}">${anomalia.prioridad}</span></td>
                    <td><span class="badge badge-${anomalia.estado}">${anomalia.estado.replace('_', ' ')}</span></td>
                    <td>${anomalia.categoria || '-'}</td>
                    <td>${anomalia.responsable || 'Sin asignar'}</td>
                    <td>${anomalia.fecha_creacion_formateada}</td>
                    <td>${anomalia.dias_transcurridos} días</td>
                    <td>
                        <button onclick="verDetalle(${anomalia.id})" class="btn-accion btn-editar" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            
            tablaContainer.style.display = 'block';
        }
        
        function aplicarFiltros() {
            cargarReporte();
        }
        
        function limpiarFiltros() {
            document.getElementById('filtro-estado').value = 'todos';
            document.getElementById('filtro-prioridad').value = 'todos';
            document.getElementById('filtro-fecha-desde').value = '';
            document.getElementById('filtro-fecha-hasta').value = '';
            document.getElementById('filtro-responsable').value = '';
            cargarReporte();
        }
        
        function exportarReporte() {
            const params = new URLSearchParams({
                estado: document.getElementById('filtro-estado').value,
                prioridad: document.getElementById('filtro-prioridad').value,
                fecha_desde: document.getElementById('filtro-fecha-desde').value,
                fecha_hasta: document.getElementById('filtro-fecha-hasta').value,
                responsable: document.getElementById('filtro-responsable').value,
                formato: 'excel'
            });
            
            window.open(`../servicios/exportar_anomalias.php?${params}`, '_blank');
        }
        
        function verDetalle(id) {
            window.open(`anomalia_detalle.php?id=${id}`, '_blank');
        }
    </script>
</body>
</html>