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
    <title>ARCO - Seguimiento de Anomalía</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #395886 0%, #2d4a73 100%);
            color: white;
            padding: 30px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .codigo-seguimiento {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 8px;
            display: inline-block;
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h2 {
            color: #395886;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #395886;
            padding-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }
        
        .info-value {
            color: #2c3e50;
            font-size: 16px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            display: inline-block;
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
        
        .badge-bajo {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .badge-medio {
            background: #fff8e1;
            color: #f57f17;
        }
        
        .badge-alto {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-critico {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ef5350;
        }
        
        .descripcion-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #395886;
            margin-bottom: 20px;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #395886;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #395886;
        }
        
        .timeline-date {
            font-size: 12px;
            color: #999;
            margin-bottom: 5px;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
        }
        
        .timeline-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .timeline-description {
            color: #666;
            font-size: 14px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
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
        }
        
        .btn-primary {
            background: #395886;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2d4a73;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
        }
        
        .btn-success:hover {
            background: #229954;
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
        
        .materiales-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .materiales-list ul {
            list-style: none;
            padding: 0;
        }
        
        .materiales-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .materiales-list li:last-child {
            border-bottom: none;
        }
        
        .estado-cambio {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .estado-cambio select {
            flex: 1;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> Seguimiento de Anomalía</h1>
            <div class="codigo-seguimiento" id="codigoSeguimiento">
                <i class="fas fa-barcode"></i> Cargando...
            </div>
        </div>
        
        <div class="content">
            <div id="loadingIndicator" class="loading">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Cargando información de la anomalía...</p>
            </div>
            
            <div id="errorIndicator" class="error" style="display: none;">
                <i class="fas fa-exclamation-triangle" style="font-size: 32px;"></i>
                <p>Error al cargar la información de la anomalía</p>
            </div>
            
            <div id="contentContainer" style="display: none;">
                <!-- Información General -->
                <div class="info-section">
                    <h2><i class="fas fa-info-circle"></i> Información General</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Título</span>
                            <span class="info-value" id="titulo">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Estado</span>
                            <span class="info-value" id="estado">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Prioridad</span>
                            <span class="info-value" id="prioridad">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Impacto</span>
                            <span class="info-value" id="impacto">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Categoría</span>
                            <span class="info-value" id="categoria">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Ubicación</span>
                            <span class="info-value" id="ubicacion">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Creado por</span>
                            <span class="info-value" id="creador">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Responsable</span>
                            <span class="info-value" id="responsable">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha de Creación</span>
                            <span class="info-value" id="fechaCreacion">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Última Actualización</span>
                            <span class="info-value" id="fechaActualizacion">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Días Transcurridos</span>
                            <span class="info-value" id="diasTranscurridos">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Fecha de Resolución</span>
                            <span class="info-value" id="fechaResolucion">-</span>
                        </div>
                    </div>
                </div>
                
                <!-- Descripción -->
                <div class="info-section">
                    <h2><i class="fas fa-align-left"></i> Descripción</h2>
                    <div class="descripcion-box" id="descripcion">
                        Cargando descripción...
                    </div>
                </div>
                
                <!-- Materiales Afectados -->
                <div class="info-section" id="materialesSection" style="display: none;">
                    <h2><i class="fas fa-boxes"></i> Materiales/Productos Afectados</h2>
                    <div class="materiales-list" id="materialesAfectados">
                        -
                    </div>
                </div>
                
                <!-- Cambiar Estado -->
                <div class="info-section">
                    <h2><i class="fas fa-exchange-alt"></i> Cambiar Estado</h2>
                    <div class="estado-cambio">
                        <select id="nuevoEstado">
                            <option value="abierta">Abierta</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="resuelta">Resuelta</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                        <button class="btn btn-success" onclick="cambiarEstado()">
                            <i class="fas fa-check"></i> Actualizar Estado
                        </button>
                    </div>
                </div>
                
                <!-- Historial de Cambios -->
                <div class="info-section">
                    <h2><i class="fas fa-history"></i> Historial de Cambios</h2>
                    <div class="timeline" id="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">Cargando historial...</div>
                            <div class="timeline-content">
                                <div class="timeline-title">Cargando...</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones -->
                <div class="actions">
                    <button class="btn btn-primary" onclick="editarAnomalia()">
                        <i class="fas fa-edit"></i> Editar Anomalía
                    </button>
                    <button class="btn btn-secondary" onclick="imprimirSeguimiento()">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                    <button class="btn btn-secondary" onclick="window.close()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const anomaliaId = <?php echo $anomalia_id; ?>;
        let anomaliaData = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            cargarAnomalia();
        });
        
        async function cargarAnomalia() {
            try {
                const response = await fetch(`../servicios/obtener_anomalia.php?id=${anomaliaId}`);
                const data = await response.json();
                
                if (data.success) {
                    anomaliaData = data.anomalia;
                    mostrarAnomalia(anomaliaData);
                    cargarHistorial();
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
            
            // Código de seguimiento
            document.getElementById('codigoSeguimiento').innerHTML = `
                <i class="fas fa-barcode"></i> ${anomalia.codigo_seguimiento || 'ANO-' + anomalia.id}
            `;
            
            // Información general
            document.getElementById('titulo').textContent = anomalia.titulo;
            document.getElementById('estado').innerHTML = `<span class="badge badge-${anomalia.estado}">${anomalia.estado.replace('_', ' ').toUpperCase()}</span>`;
            document.getElementById('prioridad').innerHTML = `<span class="badge badge-${anomalia.prioridad}">${anomalia.prioridad.toUpperCase()}</span>`;
            document.getElementById('impacto').innerHTML = anomalia.impacto ? `<span class="badge badge-${anomalia.impacto}">${anomalia.impacto.toUpperCase()}</span>` : '-';
            document.getElementById('categoria').textContent = anomalia.categoria || '-';
            document.getElementById('ubicacion').textContent = anomalia.ubicacion || '-';
            document.getElementById('creador').textContent = anomalia.nombre_creador || 'Usuario desconocido';
            document.getElementById('responsable').textContent = anomalia.nombre_asignado || 'Sin asignar';
            document.getElementById('fechaCreacion').textContent = anomalia.fecha_creacion_formateada || '-';
            document.getElementById('fechaActualizacion').textContent = anomalia.fecha_actualizacion_formateada || '-';
            document.getElementById('fechaResolucion').textContent = anomalia.fecha_resolucion_formateada || 'Pendiente';
            
            // Calcular días transcurridos
            const fechaCreacion = new Date(anomalia.fecha_creacion);
            const fechaActual = anomalia.fecha_resolucion ? new Date(anomalia.fecha_resolucion) : new Date();
            const diasTranscurridos = Math.floor((fechaActual - fechaCreacion) / (1000 * 60 * 60 * 24));
            document.getElementById('diasTranscurridos').textContent = diasTranscurridos + ' días';
            
            // Descripción
            document.getElementById('descripcion').textContent = anomalia.descripcion;
            
            // Materiales afectados
            if (anomalia.materiales_afectados) {
                document.getElementById('materialesSection').style.display = 'block';
                document.getElementById('materialesAfectados').innerHTML = `<p>${anomalia.materiales_afectados}</p>`;
            }
            
            // Estado actual en el select
            document.getElementById('nuevoEstado').value = anomalia.estado;
        }
        
        async function cargarHistorial() {
            try {
                const response = await fetch(`../servicios/obtener_historial_anomalia.php?id=${anomaliaId}`);
                const data = await response.json();
                
                if (data.success && data.historial && data.historial.length > 0) {
                    mostrarHistorial(data.historial);
                } else {
                    document.getElementById('timeline').innerHTML = `
                        <div class="timeline-item">
                            <div class="timeline-date">${anomaliaData.fecha_creacion_formateada}</div>
                            <div class="timeline-content">
                                <div class="timeline-title">Anomalía Creada</div>
                                <div class="timeline-description">La anomalía fue registrada en el sistema</div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error al cargar historial:', error);
            }
        }
        
        function mostrarHistorial(historial) {
            const timeline = document.getElementById('timeline');
            timeline.innerHTML = historial.map(item => `
                <div class="timeline-item">
                    <div class="timeline-date">${formatearFecha(item.fecha_modificacion || item.fecha_accion)}</div>
                    <div class="timeline-content">
                        <div class="timeline-title">${item.accion || item.campo_modificado}: ${item.valor_nuevo || ''}</div>
                        <div class="timeline-description">${item.descripcion_accion || item.comentario || ''}</div>
                        ${item.usuario ? `<small>Por: ${item.usuario}</small>` : ''}
                    </div>
                </div>
            `).join('');
        }
        
        async function cambiarEstado() {
            const nuevoEstado = document.getElementById('nuevoEstado').value;
            
            if (nuevoEstado === anomaliaData.estado) {
                alert('El estado seleccionado es el mismo que el actual');
                return;
            }
            
            if (!confirm(`¿Está seguro de cambiar el estado a "${nuevoEstado.replace('_', ' ').toUpperCase()}"?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('id', anomaliaId);
                formData.append('estado', nuevoEstado);
                
                const response = await fetch('../servicios/cambiar_estado_anomalia.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Estado actualizado correctamente');
                    location.reload();
                } else {
                    alert('Error al actualizar el estado: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar el estado');
            }
        }
        
        function editarAnomalia() {
            window.opener.location.href = `anomalias.php?editar=${anomaliaId}`;
            window.close();
        }
        
        function imprimirSeguimiento() {
            window.print();
        }
        
        function mostrarError(mensaje) {
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('errorIndicator').style.display = 'block';
            document.getElementById('errorIndicator').querySelector('p').textContent = mensaje;
        }
        
        function formatearFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
</body>
</html>