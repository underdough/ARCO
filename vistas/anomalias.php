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
    <title>ARCO - Anomalías</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .anomalias-container {
            padding: 20px;
        }
        
        .anomalias-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .btn-nueva-anomalia {
            background: #395886;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-nueva-anomalia:hover {
            background: #2d4a73;
            transform: translateY(-2px);
        }
        
        .anomalias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .anomalia-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 4px solid #395886;
            transition: all 0.3s ease;
        }
        
        .anomalia-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .anomalia-card.urgente {
            border-left-color: #e74c3c;
        }
        
        .anomalia-card.media {
            border-left-color: #f39c12;
        }
        
        .anomalia-card.baja {
            border-left-color: #27ae60;
        }
        
        .anomalia-header-card {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .anomalia-titulo {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .anomalia-prioridad {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .prioridad-urgente {
            background: #fee;
            color: #e74c3c;
        }
        
        .prioridad-media {
            background: #fef9e7;
            color: #f39c12;
        }
        
        .prioridad-baja {
            background: #eafaf1;
            color: #27ae60;
        }
        
        .anomalia-descripcion {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .anomalia-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .anomalia-fecha {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .anomalia-acciones {
            display: flex;
            gap: 8px;
        }
        
        .btn-accion {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-editar {
            background: #3498db;
            color: white;
        }
        
        .btn-editar:hover {
            background: #2980b9;
        }
        
        .btn-eliminar {
            background: #e74c3c;
            color: white;
        }
        
        .btn-eliminar:hover {
            background: #c0392b;
        }
        
        .modal-form {
            display: grid;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #395886;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn-cancelar {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-guardar {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .empty-state p {
            margin: 0;
            font-size: 16px;
        }
        
        .anomalia-codigo {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .anomalia-codigo i {
            margin-right: 5px;
        }
        
        .anomalia-estado {
            margin: 10px 0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .estado-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .estado-abierta {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .estado-en_proceso {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .estado-resuelta {
            background: #e8f5e8;
            color: #388e3c;
        }
        
        .estado-cerrada {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .impacto-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .impacto-bajo {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .impacto-medio {
            background: #fff8e1;
            color: #f57f17;
        }
        
        .impacto-alto {
            background: #ffebee;
            color: #c62828;
        }
        
        .impacto-critico {
            background: #ffebee;
            color: #b71c1c;
            border: 1px solid #ef5350;
        }
        
        .btn-seguimiento {
            background: #2196f3;
            color: white;
        }
        
        .btn-seguimiento:hover {
            background: #1976d2;
        }
        
        .anomalia-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .anomalia-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .confirmacion-mensaje {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 10001;
            text-align: center;
            max-width: 400px;
        }
        
        .confirmacion-mensaje h3 {
            color: #27ae60;
            margin-bottom: 15px;
        }
        
        .confirmacion-mensaje .codigo-seguimiento {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: #395886;
            margin: 15px 0;
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
        <?php echo generarMenuHTML('anomalias'); ?>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Anomalías</h2>
            <div class="user-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                <span>Hola, <strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>
        
        <div class="anomalias-container">
            <div class="anomalias-header">
                <div>
                    <h3>Registro de Anomalías y Novedades</h3>
                    <p>Gestiona y reporta incidencias del sistema de inventario</p>
                </div>
                <button class="btn-nueva-anomalia" onclick="abrirModalAnomalia()">
                    <i class="fas fa-plus"></i>
                    Nueva Anomalía
                </button>
            </div>
            
            <div class="anomalias-grid" id="anomaliasGrid">
                <!-- Las anomalías se cargarán aquí dinámicamente -->
            </div>
            
            <div class="empty-state" id="emptyState" style="display: none;">
                <i class="fas fa-clipboard-check"></i>
                <h3>No hay anomalías registradas</h3>
                <p>Comienza reportando la primera anomalía del sistema</p>
            </div>
        </div>
    </div>
    
    <!-- Modal para crear/editar anomalía -->
    <div id="modalAnomalia" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitulo">Nueva Anomalía</h3>
                <span class="close" onclick="cerrarModalAnomalia()">&times;</span>
            </div>
            <form id="formAnomalia" class="modal-form">
                <input type="hidden" id="anomaliaId" name="id">
                
                <div class="form-group">
                    <label for="titulo">Título de la Anomalía *</label>
                    <input type="text" id="titulo" name="titulo" required maxlength="100" 
                           placeholder="Ej: Discrepancia en inventario de producto X">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción Detallada *</label>
                    <textarea id="descripcion" name="descripcion" required 
                              placeholder="Describe detalladamente la anomalía encontrada, pasos para reproducirla, impacto, etc."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="prioridad">Prioridad *</label>
                    <select id="prioridad" name="prioridad" required>
                        <option value="">Seleccionar prioridad</option>
                        <option value="baja">Baja - No afecta operaciones críticas</option>
                        <option value="media">Media - Afecta algunas operaciones</option>
                        <option value="urgente">Urgente - Afecta operaciones críticas</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria">
                        <option value="">Seleccionar categoría</option>
                        <option value="inventario">Inventario</option>
                        <option value="sistema">Sistema</option>
                        <option value="usuario">Usuario</option>
                        <option value="hardware">Hardware</option>
                        <option value="proceso">Proceso</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ubicacion">Ubicación/Módulo Afectado</label>
                    <input type="text" id="ubicacion" name="ubicacion" maxlength="100"
                           placeholder="Ej: Módulo de productos, Almacén principal, etc.">
                </div>
                
                <div class="form-group">
                    <label for="impacto">Nivel de Impacto</label>
                    <select id="impacto" name="impacto">
                        <option value="bajo">Bajo - Impacto mínimo en operaciones</option>
                        <option value="medio" selected>Medio - Impacto moderado</option>
                        <option value="alto">Alto - Impacto significativo</option>
                        <option value="critico">Crítico - Impacto severo en operaciones</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="materiales_afectados">Materiales/Productos Afectados</label>
                    <textarea id="materiales_afectados" name="materiales_afectados" rows="3"
                              placeholder="Describe los materiales o productos involucrados en la anomalía (opcional)"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="responsable_asignado">Responsable Asignado</label>
                    <select id="responsable_asignado" name="responsable_asignado">
                        <option value="">Sin asignar</option>
                        <!-- Se cargarán dinámicamente los usuarios -->
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalAnomalia()">Cancelar</button>
                    <button type="submit" class="btn-guardar">Guardar Anomalía</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let anomalias = [];
        let editandoId = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            cargarAnomalias();
            setupEventListeners();
            
            // Verificar si se debe abrir modal de edición
            const urlParams = new URLSearchParams(window.location.search);
            const editarId = urlParams.get('editar');
            if (editarId) {
                setTimeout(() => {
                    abrirModalAnomalia(parseInt(editarId));
                }, 1000); // Esperar a que se carguen las anomalías
            }
        });
        
        function setupEventListeners() {
            // Toggle sidebar
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
            
            // Form submit
            document.getElementById('formAnomalia').addEventListener('submit', function(e) {
                e.preventDefault();
                guardarAnomalia();
            });
            
            // Cerrar modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    cerrarModalAnomalia();
                }
            });
        }
        
        async function cargarAnomalias() {
            try {
                const response = await fetch('../servicios/obtener_anomalias.php');
                const data = await response.json();
                
                if (data.success) {
                    anomalias = data.anomalias;
                    renderizarAnomalias();
                } else {
                    console.error('Error al cargar anomalías:', data.error);
                    mostrarError('Error al cargar las anomalías');
                }
            } catch (error) {
                console.error('Error de conexión:', error);
                mostrarError('Error de conexión con el servidor');
            }
        }
        
        function renderizarAnomalias() {
            const grid = document.getElementById('anomaliasGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (anomalias.length === 0) {
                grid.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }
            
            grid.style.display = 'grid';
            emptyState.style.display = 'none';
            
            grid.innerHTML = anomalias.map(anomalia => `
                <div class="anomalia-card ${anomalia.prioridad}" onclick="verDetalleAnomalia(${anomalia.id})">
                    <div class="anomalia-header-card">
                        <div>
                            <h4 class="anomalia-titulo">${anomalia.titulo}</h4>
                            <div class="anomalia-codigo">
                                <i class="fas fa-barcode"></i>
                                <strong>${anomalia.codigo_seguimiento || 'ANO-' + anomalia.id}</strong>
                            </div>
                        </div>
                        <span class="anomalia-prioridad prioridad-${anomalia.prioridad}">
                            ${anomalia.prioridad}
                        </span>
                    </div>
                    <p class="anomalia-descripcion">${anomalia.descripcion_corta || anomalia.descripcion}</p>
                    <div class="anomalia-estado">
                        <span class="estado-badge estado-${anomalia.estado}">
                            ${anomalia.estado.replace('_', ' ').toUpperCase()}
                        </span>
                        ${anomalia.impacto ? `<span class="impacto-badge impacto-${anomalia.impacto}">${anomalia.impacto.toUpperCase()}</span>` : ''}
                    </div>
                    <div class="anomalia-meta">
                        <div class="anomalia-fecha">
                            <i class="fas fa-calendar"></i>
                            ${formatearFecha(anomalia.fecha_creacion)}
                        </div>
                        <div class="anomalia-acciones" onclick="event.stopPropagation()">
                            <button class="btn-accion btn-editar" onclick="editarAnomalia(${anomalia.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-accion btn-seguimiento" onclick="verSeguimiento(${anomalia.id})" title="Seguimiento">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-accion btn-eliminar" onclick="eliminarAnomalia(${anomalia.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    ${anomalia.categoria ? `<div style="margin-top: 10px;"><small><i class="fas fa-tag"></i> ${anomalia.categoria}</small></div>` : ''}
                    ${anomalia.ubicacion ? `<div><small><i class="fas fa-map-marker-alt"></i> ${anomalia.ubicacion}</small></div>` : ''}
                    ${anomalia.usuario_creador ? `<div><small><i class="fas fa-user"></i> Creado por: ${anomalia.usuario_creador}</small></div>` : ''}
                </div>
            `).join('');
        }
        
        async function abrirModalAnomalia(id = null) {
            const modal = document.getElementById('modalAnomalia');
            const titulo = document.getElementById('modalTitulo');
            const form = document.getElementById('formAnomalia');
            
            editandoId = id;
            
            // Cargar usuarios para el select de responsable
            await cargarUsuarios();
            
            if (id) {
                titulo.textContent = 'Editar Anomalía';
                
                try {
                    // Cargar datos de la anomalía desde el servidor
                    const response = await fetch(`../servicios/obtener_anomalia.php?id=${id}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        const anomalia = data.anomalia;
                        document.getElementById('anomaliaId').value = anomalia.id;
                        document.getElementById('titulo').value = anomalia.titulo;
                        document.getElementById('descripcion').value = anomalia.descripcion;
                        document.getElementById('prioridad').value = anomalia.prioridad;
                        document.getElementById('categoria').value = anomalia.categoria || '';
                        document.getElementById('ubicacion').value = anomalia.ubicacion || '';
                        document.getElementById('impacto').value = anomalia.impacto || 'medio';
                        document.getElementById('materiales_afectados').value = anomalia.materiales_afectados || '';
                        document.getElementById('responsable_asignado').value = anomalia.responsable_asignado || '';
                    } else {
                        mostrarError('Error al cargar los datos de la anomalía: ' + data.error);
                        return;
                    }
                } catch (error) {
                    console.error('Error al cargar anomalía:', error);
                    mostrarError('Error de conexión al cargar la anomalía');
                    return;
                }
            } else {
                titulo.textContent = 'Nueva Anomalía';
                form.reset();
                document.getElementById('anomaliaId').value = '';
            }
            
            modal.style.display = 'block';
            document.getElementById('titulo').focus();
        }
        
        async function cargarUsuarios() {
            try {
                const response = await fetch('../servicios/listar_usuarios.php', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const text = await response.text();
                console.log('Respuesta de usuarios:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Error al parsear JSON de usuarios:', e);
                    console.error('Respuesta recibida:', text);
                    return;
                }
                
                const select = document.getElementById('responsable_asignado');
                select.innerHTML = '<option value="">Sin asignar</option>';
                
                if (data.success && data.usuarios) {
                    console.log('Usuarios cargados:', data.usuarios.length);
                    data.usuarios.forEach(usuario => {
                        const option = document.createElement('option');
                        option.value = usuario.id_usuarios;
                        option.textContent = `${usuario.nombre} ${usuario.apellido} (${usuario.rol})`;
                        select.appendChild(option);
                    });
                } else {
                    console.error('No se pudieron cargar usuarios:', data);
                }
            } catch (error) {
                console.error('Error al cargar usuarios:', error);
            }
        }
        
        function cerrarModalAnomalia() {
            const modal = document.getElementById('modalAnomalia');
            modal.style.display = 'none';
            editandoId = null;
        }
        
        async function guardarAnomalia() {
            const formData = new FormData(document.getElementById('formAnomalia'));
            
            try {
                const response = await fetch('../servicios/guardar_anomalia_simple.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const text = await response.text();
                console.log('Respuesta del servidor:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('Error al parsear JSON:', parseError);
                    console.error('Respuesta recibida:', text);
                    throw new Error('Respuesta inválida del servidor');
                }
                
                if (data.success) {
                    cerrarModalAnomalia();
                    cargarAnomalias();
                    
                    // Mostrar mensaje de confirmación con código de seguimiento
                    if (data.codigo_seguimiento) {
                        mostrarConfirmacionConCodigo(data.codigo_seguimiento, editandoId);
                    } else {
                        mostrarExito(editandoId ? 'Anomalía actualizada correctamente' : 'Anomalía creada correctamente');
                    }
                } else {
                    mostrarError(data.error || 'Error al guardar la anomalía');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión al guardar: ' + error.message);
            }
        }
        
        function editarAnomalia(id) {
            abrirModalAnomalia(id);
        }
        
        async function eliminarAnomalia(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta anomalía?')) {
                return;
            }
            
            try {
                const response = await fetch('../servicios/eliminar_anomalia_simple.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const text = await response.text();
                console.log('Respuesta del servidor:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (parseError) {
                    console.error('Error al parsear JSON:', parseError);
                    console.error('Respuesta recibida:', text);
                    throw new Error('Respuesta inválida del servidor');
                }
                
                if (data.success) {
                    cargarAnomalias();
                    mostrarExito('Anomalía eliminada correctamente');
                } else {
                    mostrarError(data.error || 'Error al eliminar la anomalía');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarError('Error de conexión al eliminar: ' + error.message);
            }
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
        
        function mostrarExito(mensaje) {
            mostrarNotificacion(mensaje, 'success');
        }
        
        function mostrarError(mensaje) {
            mostrarNotificacion(mensaje, 'error');
        }
        
        function mostrarNotificacion(mensaje, tipo) {
            // Crear elemento de notificación
            const notificacion = document.createElement('div');
            notificacion.className = `notificacion ${tipo}`;
            notificacion.innerHTML = `
                <div class="notificacion-content">
                    <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${mensaje}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="btn-cerrar-notif">×</button>
                </div>
            `;
            
            // Agregar estilos si no existen
            if (!document.getElementById('notificacion-styles')) {
                const styles = document.createElement('style');
                styles.id = 'notificacion-styles';
                styles.textContent = `
                    .notificacion {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 10000;
                        max-width: 400px;
                        padding: 15px;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        animation: slideIn 0.3s ease-out;
                    }
                    .notificacion.success {
                        background: #d4edda;
                        border: 1px solid #c3e6cb;
                        color: #155724;
                    }
                    .notificacion.error {
                        background: #f8d7da;
                        border: 1px solid #f5c6cb;
                        color: #721c24;
                    }
                    .notificacion-content {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .btn-cerrar-notif {
                        background: none;
                        border: none;
                        font-size: 18px;
                        cursor: pointer;
                        margin-left: auto;
                        opacity: 0.7;
                    }
                    .btn-cerrar-notif:hover {
                        opacity: 1;
                    }
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(styles);
            }
            
            // Agregar al DOM
            document.body.appendChild(notificacion);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notificacion.parentElement) {
                    notificacion.remove();
                }
            }, 5000);
        }
        
        function mostrarConfirmacionConCodigo(codigo, esEdicion) {
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 10000;
                display: flex;
                justify-content: center;
                align-items: center;
            `;
            
            const mensaje = document.createElement('div');
            mensaje.className = 'confirmacion-mensaje';
            mensaje.innerHTML = `
                <i class="fas fa-check-circle" style="font-size: 48px; color: #27ae60; margin-bottom: 15px;"></i>
                <h3>${esEdicion ? 'Anomalía Actualizada' : 'Anomalía Registrada Exitosamente'}</h3>
                <p>Su anomalía ha sido ${esEdicion ? 'actualizada' : 'registrada'} correctamente en el sistema.</p>
                <div class="codigo-seguimiento">
                    <strong>Código de Seguimiento:</strong><br>
                    ${codigo}
                </div>
                <p><small>Guarde este código para futuras consultas y seguimiento.</small></p>
                <button onclick="this.parentElement.parentElement.remove()" 
                        style="background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-top: 15px;">
                    Entendido
                </button>
            `;
            
            overlay.appendChild(mensaje);
            document.body.appendChild(overlay);
            
            // Auto-cerrar después de 10 segundos
            setTimeout(() => {
                if (overlay.parentElement) {
                    overlay.remove();
                }
            }, 10000);
        }
        
        function verDetalleAnomalia(id) {
            // Implementar vista detallada de anomalía
            window.location.href = `anomalia_detalle.php?id=${id}`;
        }
        
        function verSeguimiento(id) {
            // Implementar vista de seguimiento
            window.open(`anomalia_seguimiento.php?id=${id}`, '_blank', 'width=800,height=600');
        }
    </script>
</body>
</html>
</html>