<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}

require_once '../servicios/middleware_permisos.php';
require_once '../servicios/menu_dinamico.php';

$permisos = obtenerPermisosUsuario('devoluciones');
$puedeCrear = in_array('crear', $permisos);
$puedeAprobar = in_array('aprobar', $permisos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Devoluciones</title>
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="stylesheet" href="../componentes/movimientos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mejorar espaciado general */
        .main-content {
            padding: 25px;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .header {
            margin-bottom: 30px;
            padding: 25px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            max-width: 100%;
        }
        
        .header h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.8rem;
        }
        
        /* Mejorar tabla */
        .movements-table {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
            max-width: 100%;
            -webkit-overflow-scrolling: touch;
        }
        
        .movements-table table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }
        
        .movements-table thead th {
            background: #395886;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 500;
            border: none;
            white-space: nowrap;
        }
        
        .movements-table thead th:first-child {
            border-radius: 8px 0 0 0;
        }
        
        .movements-table thead th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        .movements-table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            white-space: nowrap;
        }
        
        .movements-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Estados */
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pendiente { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .status-procesada { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-rechazada { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Acciones */
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 1px solid #e0e0e0;
            background: white;
            transition: all 0.3s ease;
        }
        
        .action-icon:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .action-icon.view {
            color: #2196F3;
        }
        
        .action-icon.view:hover {
            background: #2196F3;
            color: white;
        }
        
        .action-icon.print {
            color: #4CAF50;
        }
        
        .action-icon.print:hover {
            background: #4CAF50;
            color: white;
        }
        
        /* Evitar scroll vertical en main-content */
        body {
            overflow-x: hidden;
        }
        
        .main-content {
            overflow-y: auto;
            overflow-x: hidden;
            max-height: calc(100vh - 20px);
            box-sizing: border-box;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                padding: 20px;
            }
            
            .movements-table {
                padding: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .header {
                padding: 15px;
                flex-direction: column;
                gap: 15px;
            }
            
            .header h2 {
                font-size: 1.4rem;
            }
            
            .search-bar {
                width: 100%;
            }
            
            .action-buttons {
                width: 100%;
                flex-direction: column;
            }
            
            .action-buttons select,
            .action-buttons button {
                width: 100%;
            }
            
            .movements-table {
                padding: 10px;
                border-radius: 8px;
                overflow-x: auto;
            }
            
            /* Diseño de tarjetas para móviles */
            .movements-table table {
                display: block;
                min-width: auto;
            }
            
            .movements-table thead {
                display: none;
            }
            
            .movements-table tbody {
                display: block;
            }
            
            .movements-table tbody tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 15px;
                background: white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            
            .movements-table tbody td {
                display: block;
                text-align: right;
                padding: 8px 0;
                border: none;
                position: relative;
                padding-left: 50%;
                white-space: normal;
            }
            
            .movements-table tbody td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 45%;
                padding-right: 10px;
                text-align: left;
                font-weight: 600;
                color: #395886;
            }
            
            .movements-table tbody td:last-child {
                text-align: center;
                padding-left: 0;
                padding-top: 15px;
                border-top: 1px solid #e9ecef;
                margin-top: 10px;
                display: flex;
                flex-direction: row;
            }
            
            .movements-table tbody td:last-child::before {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
            }
            
            .header {
                padding: 12px;
            }
            
            .movements-table {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <?php echo generarSidebarCompleto('devoluciones'); ?>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Devoluciones</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar devoluciones...">
            </div>
            <div class="action-buttons">
                <select id="filterEstado" class="btn btn-secondary">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="procesada">Procesada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
                <?php if ($puedeCrear): ?>
                <button class="btn btn-primary" id="btnNuevaDevolucion">
                    <i class="fas fa-plus"></i> Nueva Devolución
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="movements-table">
            <table>
                <thead>
                    <tr>
                        <th>N° Devolución</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Solicitante</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="devolucionesBody">
                    <tr><td colspan="8" style="text-align:center;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nueva Devolución -->
    <div class="modal" id="devolucionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Solicitar Devolución</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="devolucionForm">
                    <div class="form-group">
                        <label>Producto</label>
                        <select id="devProducto" class="form-control" required>
                            <option value="">Seleccionar producto</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Cantidad</label>
                            <input type="number" id="devCantidad" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Motivo</label>
                            <select id="devMotivo" class="form-control" required>
                                <option value="">Seleccionar motivo</option>
                                <option value="defectuoso">Producto Defectuoso</option>
                                <option value="incorrecto">Producto Incorrecto</option>
                                <option value="no_requerido">No Requerido</option>
                                <option value="vencido">Producto Vencido</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción del problema</label>
                        <textarea id="devDescripcion" class="form-control" rows="3" placeholder="Describa el motivo de la devolución..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Notas adicionales</label>
                        <textarea id="devNotas" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelarDev">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="devolucionForm">Solicitar Devolución</button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal" id="detalleDevModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detalle de Devolución</h3>
                <button class="close-modal" id="closeDetalleDev">&times;</button>
            </div>
            <div class="modal-body" id="detalleDevContent"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cerrarDetalleDev">Cerrar</button>
                <?php if ($puedeAprobar): ?>
                <button class="btn btn-danger" id="btnRechazarDev" style="display:none;">
                    <i class="fas fa-times"></i> Rechazar
                </button>
                <button class="btn btn-success" id="btnAprobarDev" style="display:none;">
                    <i class="fas fa-check"></i> Aprobar y Procesar
                </button>
                <?php endif; ?>
                <button class="btn btn-primary" id="btnImprimirDev">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>

    <script>
        let productos = [];
        let devolucionActual = null;

        document.addEventListener('DOMContentLoaded', function() {
            cargarProductos();
            cargarDevoluciones();

            document.getElementById('btnNuevaDevolucion')?.addEventListener('click', abrirModalDevolucion);
            document.getElementById('btnCancelarDev').addEventListener('click', cerrarModalDevolucion);
            document.querySelector('#devolucionModal .close-modal').addEventListener('click', cerrarModalDevolucion);
            document.getElementById('devolucionForm').addEventListener('submit', crearDevolucion);
            document.getElementById('closeDetalleDev').addEventListener('click', () => document.getElementById('detalleDevModal').style.display = 'none');
            document.getElementById('cerrarDetalleDev').addEventListener('click', () => document.getElementById('detalleDevModal').style.display = 'none');
            document.getElementById('btnImprimirDev').addEventListener('click', () => {
                if (devolucionActual) window.open(`../servicios/imprimir_devolucion.php?id=${devolucionActual}`, '_blank');
            });
            document.getElementById('btnAprobarDev')?.addEventListener('click', () => procesarDevolucion('aprobar'));
            document.getElementById('btnRechazarDev')?.addEventListener('click', () => procesarDevolucion('rechazar'));
            document.getElementById('filterEstado').addEventListener('change', cargarDevoluciones);
            document.getElementById('searchInput').addEventListener('input', cargarDevoluciones);
        });

        async function cargarProductos() {
            const res = await fetch('../servicios/obtener_productos.php');
            productos = await res.json();
            
            const select = document.getElementById('devProducto');
            productos.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.nombre;
                select.appendChild(opt);
            });
        }

        async function cargarDevoluciones() {
            const estado = document.getElementById('filterEstado').value;
            const busqueda = document.getElementById('searchInput').value.toLowerCase();
            
            const res = await fetch('../servicios/devoluciones.php?accion=listar');
            const data = await res.json();
            
            const tbody = document.getElementById('devolucionesBody');
            
            if (data.status === 'success' && data.data.length > 0) {
                let devoluciones = data.data;
                
                if (estado) devoluciones = devoluciones.filter(d => d.estado === estado);
                if (busqueda) devoluciones = devoluciones.filter(d => 
                    d.numero_devolucion.toLowerCase().includes(busqueda) ||
                    (d.nombre_material && d.nombre_material.toLowerCase().includes(busqueda))
                );
                
                const motivoLabels = {
                    'defectuoso': 'Defectuoso',
                    'incorrecto': 'Incorrecto',
                    'no_requerido': 'No Requerido',
                    'vencido': 'Vencido',
                    'otro': 'Otro'
                };
                
                tbody.innerHTML = devoluciones.map(d => `
                    <tr>
                        <td data-label="N° Devolución">${d.numero_devolucion}</td>
                        <td data-label="Producto">${d.nombre_material || 'N/A'}</td>
                        <td data-label="Cantidad">${d.cantidad}</td>
                        <td data-label="Motivo">${motivoLabels[d.motivo] || d.motivo}</td>
                        <td data-label="Solicitante">${d.solicitante_nombre || 'N/A'}</td>
                        <td data-label="Fecha">${new Date(d.fecha_solicitud).toLocaleDateString()}</td>
                        <td data-label="Estado"><span class="status status-${d.estado}">${d.estado}</span></td>
                        <td class="actions">
                            <button class="action-icon view" onclick="verDevolucion(${d.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <button class="action-icon print" onclick="window.open('../servicios/imprimir_devolucion.php?id=${d.id}', '_blank')" title="Imprimir"><i class="fas fa-print"></i></button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No hay devoluciones</td></tr>';
            }
        }

        function abrirModalDevolucion() {
            document.getElementById('devolucionModal').style.display = 'flex';
            document.getElementById('devolucionForm').reset();
        }

        function cerrarModalDevolucion() {
            document.getElementById('devolucionModal').style.display = 'none';
        }

        async function crearDevolucion(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('material_id', document.getElementById('devProducto').value);
            formData.append('cantidad', document.getElementById('devCantidad').value);
            formData.append('motivo', document.getElementById('devMotivo').value);
            formData.append('descripcion', document.getElementById('devDescripcion').value);
            formData.append('notas', document.getElementById('devNotas').value);
            
            const res = await fetch('../servicios/devoluciones.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                alert('✅ Devolución solicitada: ' + data.numero_devolucion);
                cerrarModalDevolucion();
                cargarDevoluciones();
            } else {
                alert('❌ ' + data.message);
            }
        }

        async function verDevolucion(id) {
            devolucionActual = id;
            const res = await fetch(`../servicios/devoluciones.php?accion=ver&id=${id}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                const d = data.data;
                const motivoLabels = {
                    'defectuoso': 'Producto Defectuoso',
                    'incorrecto': 'Producto Incorrecto',
                    'no_requerido': 'No Requerido',
                    'vencido': 'Producto Vencido',
                    'otro': 'Otro'
                };
                
                document.getElementById('detalleDevContent').innerHTML = `
                    <p><strong>N° Devolución:</strong> ${d.numero_devolucion}</p>
                    <p><strong>Producto:</strong> ${d.nombre_material}</p>
                    <p><strong>Cantidad:</strong> ${d.cantidad} unidades</p>
                    <p><strong>Motivo:</strong> ${motivoLabels[d.motivo] || d.motivo}</p>
                    <p><strong>Descripción:</strong> ${d.descripcion || '-'}</p>
                    <p><strong>Estado:</strong> <span class="status status-${d.estado}">${d.estado}</span></p>
                    <p><strong>Solicitado por:</strong> ${d.solicitante_nombre}</p>
                    <p><strong>Fecha:</strong> ${new Date(d.fecha_solicitud).toLocaleString()}</p>
                    ${d.procesador_nombre ? `<p><strong>Procesado por:</strong> ${d.procesador_nombre}</p>` : ''}
                    ${d.notas ? `<p><strong>Notas:</strong> ${d.notas}</p>` : ''}
                `;
                
                const btnAprobar = document.getElementById('btnAprobarDev');
                const btnRechazar = document.getElementById('btnRechazarDev');
                if (btnAprobar && btnRechazar) {
                    const mostrar = d.estado === 'pendiente';
                    btnAprobar.style.display = mostrar ? 'inline-block' : 'none';
                    btnRechazar.style.display = mostrar ? 'inline-block' : 'none';
                }
                
                document.getElementById('detalleDevModal').style.display = 'flex';
            }
        }

        async function procesarDevolucion(accion) {
            if (!devolucionActual) return;
            
            const mensaje = accion === 'aprobar' 
                ? '¿Aprobar esta devolución? Se descontará el stock del producto.'
                : '¿Rechazar esta devolución?';
            
            if (!confirm(mensaje)) return;
            
            const formData = new FormData();
            formData.append('accion', 'procesar');
            formData.append('id', devolucionActual);
            formData.append('accion_procesar', accion === 'aprobar' ? 'aprobar' : 'rechazar');
            
            const res = await fetch('../servicios/devoluciones.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                alert('✅ Devolución ' + (accion === 'aprobar' ? 'aprobada' : 'rechazada'));
                document.getElementById('detalleDevModal').style.display = 'none';
                cargarDevoluciones();
            } else {
                alert('❌ ' + data.message);
            }
        }
    </script>
</body>
</html>
