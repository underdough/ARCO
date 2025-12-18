<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}

require_once '../servicios/middleware_permisos.php';
require_once '../servicios/menu_dinamico.php';

$permisos = obtenerPermisosUsuario('ordenes_compra');
$puedeCrear = in_array('crear', $permisos);
$puedeEditar = in_array('editar', $permisos);
$puedeAprobar = in_array('aprobar', $permisos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Órdenes de Compra</title>
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
            min-width: 800px;
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
        .status-recibida { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-cancelada { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-parcial { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
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
        
        /* Items de orden */
        .item-row { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 10px; 
            align-items: center; 
        }
        
        .item-row input, .item-row select { 
            flex: 1; 
        }
        
        .btn-remove-item { 
            background: #dc3545; 
            color: white; 
            border: none; 
            padding: 8px 12px; 
            cursor: pointer; 
            border-radius: 4px; 
        }
        
        #itemsContainer { 
            max-height: 300px; 
            overflow-y: auto; 
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .total-display { 
            font-size: 1.3em; 
            font-weight: bold; 
            text-align: right; 
            margin-top: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #395886;
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
    <?php echo generarSidebarCompleto('ordenes_compra'); ?>

    <div class="main-content">
        <div class="header">
            <h2>Órdenes de Compra</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar órdenes...">
            </div>
            <div class="action-buttons">
                <select id="filterEstado" class="btn btn-secondary">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="parcial">Parcial</option>
                    <option value="recibida">Recibida</option>
                    <option value="cancelada">Cancelada</option>
                </select>
                <?php if ($puedeCrear): ?>
                <button class="btn btn-primary" id="btnNuevaOrden">
                    <i class="fas fa-plus"></i> Nueva Orden
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="movements-table">
            <table>
                <thead>
                    <tr>
                        <th>N° Orden</th>
                        <th>Proveedor</th>
                        <th>Fecha Pedido</th>
                        <th>Fecha Esperada</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="ordenesBody">
                    <tr><td colspan="7" style="text-align:center;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Nueva Orden -->
    <div class="modal" id="ordenModal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3 class="modal-title">Nueva Orden de Compra</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="ordenForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Proveedor</label>
                            <input type="text" id="proveedor" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha Esperada</label>
                            <input type="date" id="fechaEsperada" class="form-control">
                        </div>
                    </div>
                    
                    <h4>Items de la Orden</h4>
                    <div id="itemsContainer"></div>
                    <button type="button" class="btn btn-secondary" id="btnAgregarItem">
                        <i class="fas fa-plus"></i> Agregar Item
                    </button>
                    
                    <div class="total-display">
                        Total: $<span id="totalOrden">0.00</span>
                    </div>
                    
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea id="notasOrden" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelarOrden">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="ordenForm">Crear Orden</button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal" id="detalleModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Detalle de Orden</h3>
                <button class="close-modal" id="closeDetalle">&times;</button>
            </div>
            <div class="modal-body" id="detalleContent"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cerrarDetalle">Cerrar</button>
                <?php if ($puedeAprobar): ?>
                <button class="btn btn-success" id="btnRecibirOrden" style="display:none;">
                    <i class="fas fa-check"></i> Marcar como Recibida
                </button>
                <?php endif; ?>
                <button class="btn btn-primary" id="btnImprimirOrden">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>

    <script>
        let productos = [];
        let ordenActual = null;

        document.addEventListener('DOMContentLoaded', function() {
            cargarProductos();
            cargarOrdenes();

            document.getElementById('btnNuevaOrden')?.addEventListener('click', abrirModalOrden);
            document.getElementById('btnCancelarOrden').addEventListener('click', cerrarModalOrden);
            document.querySelector('#ordenModal .close-modal').addEventListener('click', cerrarModalOrden);
            document.getElementById('btnAgregarItem').addEventListener('click', agregarItemRow);
            document.getElementById('ordenForm').addEventListener('submit', crearOrden);
            document.getElementById('closeDetalle').addEventListener('click', () => document.getElementById('detalleModal').style.display = 'none');
            document.getElementById('cerrarDetalle').addEventListener('click', () => document.getElementById('detalleModal').style.display = 'none');
            document.getElementById('btnImprimirOrden').addEventListener('click', () => {
                if (ordenActual) window.open(`../servicios/imprimir_orden_compra.php?id=${ordenActual}`, '_blank');
            });
            document.getElementById('btnRecibirOrden')?.addEventListener('click', recibirOrdenCompleta);
            document.getElementById('filterEstado').addEventListener('change', cargarOrdenes);
            document.getElementById('searchInput').addEventListener('input', cargarOrdenes);
        });

        async function cargarProductos() {
            const res = await fetch('../servicios/obtener_productos.php');
            productos = await res.json();
        }

        async function cargarOrdenes() {
            const estado = document.getElementById('filterEstado').value;
            const busqueda = document.getElementById('searchInput').value;
            
            let url = '../servicios/ordenes_compra.php?accion=listar';
            
            const res = await fetch(url);
            const data = await res.json();
            
            const tbody = document.getElementById('ordenesBody');
            
            if (data.status === 'success' && data.data.length > 0) {
                let ordenes = data.data;
                
                if (estado) ordenes = ordenes.filter(o => o.estado === estado);
                if (busqueda) ordenes = ordenes.filter(o => 
                    o.numero_orden.toLowerCase().includes(busqueda.toLowerCase()) ||
                    o.proveedor.toLowerCase().includes(busqueda.toLowerCase())
                );
                
                tbody.innerHTML = ordenes.map(o => `
                    <tr>
                        <td data-label="N° Orden">${o.numero_orden}</td>
                        <td data-label="Proveedor">${o.proveedor}</td>
                        <td data-label="Fecha Pedido">${o.fecha_pedido}</td>
                        <td data-label="Fecha Esperada">${o.fecha_esperada || '-'}</td>
                        <td data-label="Total">$${parseFloat(o.total).toLocaleString()}</td>
                        <td data-label="Estado"><span class="status status-${o.estado}">${o.estado}</span></td>
                        <td class="actions">
                            <button class="action-icon view" onclick="verOrden(${o.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <button class="action-icon print" onclick="window.open('../servicios/imprimir_orden_compra.php?id=${o.id}', '_blank')" title="Imprimir"><i class="fas fa-print"></i></button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No hay órdenes</td></tr>';
            }
        }

        function abrirModalOrden() {
            document.getElementById('ordenModal').style.display = 'flex';
            document.getElementById('ordenForm').reset();
            document.getElementById('itemsContainer').innerHTML = '';
            agregarItemRow();
        }

        function cerrarModalOrden() {
            document.getElementById('ordenModal').style.display = 'none';
        }

        function agregarItemRow() {
            const container = document.getElementById('itemsContainer');
            const row = document.createElement('div');
            row.className = 'item-row';
            row.innerHTML = `
                <select class="form-control item-producto" required>
                    <option value="">Seleccionar producto</option>
                    ${productos.map(p => `<option value="${p.id}" data-nombre="${p.nombre}">${p.nombre}</option>`).join('')}
                </select>
                <input type="number" class="form-control item-cantidad" placeholder="Cantidad" min="1" required>
                <input type="number" class="form-control item-precio" placeholder="Precio unit." step="0.01" min="0" required>
                <button type="button" class="btn-remove-item" onclick="this.parentElement.remove(); calcularTotal();">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
            
            row.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', calcularTotal);
            });
        }

        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const cantidad = parseFloat(row.querySelector('.item-cantidad').value) || 0;
                const precio = parseFloat(row.querySelector('.item-precio').value) || 0;
                total += cantidad * precio;
            });
            document.getElementById('totalOrden').textContent = total.toLocaleString('es-CO', {minimumFractionDigits: 2});
        }

        async function crearOrden(e) {
            e.preventDefault();
            
            const items = [];
            document.querySelectorAll('.item-row').forEach(row => {
                const producto = row.querySelector('.item-producto');
                items.push({
                    material_id: producto.value,
                    cantidad: parseInt(row.querySelector('.item-cantidad').value),
                    precio_unitario: parseFloat(row.querySelector('.item-precio').value)
                });
            });
            
            if (items.length === 0 || items.some(i => !i.material_id)) {
                alert('Debe agregar al menos un item válido');
                return;
            }
            
            const formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('proveedor', document.getElementById('proveedor').value);
            formData.append('fecha_esperada', document.getElementById('fechaEsperada').value);
            formData.append('items', JSON.stringify(items));
            formData.append('notas', document.getElementById('notasOrden').value);
            
            const res = await fetch('../servicios/ordenes_compra.php', { method: 'POST', body: formData });
            const data = await res.json();
            
            if (data.status === 'success') {
                alert('✅ Orden creada: ' + data.numero_orden);
                cerrarModalOrden();
                cargarOrdenes();
            } else {
                alert('❌ ' + data.message);
            }
        }

        async function verOrden(id) {
            ordenActual = id;
            const res = await fetch(`../servicios/ordenes_compra.php?accion=ver&id=${id}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                const o = data.data;
                document.getElementById('detalleContent').innerHTML = `
                    <p><strong>N° Orden:</strong> ${o.numero_orden}</p>
                    <p><strong>Proveedor:</strong> ${o.proveedor}</p>
                    <p><strong>Estado:</strong> <span class="status status-${o.estado}">${o.estado}</span></p>
                    <p><strong>Fecha Pedido:</strong> ${o.fecha_pedido}</p>
                    <p><strong>Fecha Esperada:</strong> ${o.fecha_esperada || '-'}</p>
                    <h4>Items:</h4>
                    <table style="width:100%; border-collapse:collapse;">
                        <tr style="background:#f5f5f5;"><th>Producto</th><th>Pedido</th><th>Recibido</th><th>Precio</th></tr>
                        ${o.detalles.map(d => `
                            <tr style="border-bottom:1px solid #ddd;">
                                <td>${d.nombre_material}</td>
                                <td>${d.cantidad_pedida}</td>
                                <td>${d.cantidad_recibida}</td>
                                <td>$${parseFloat(d.precio_unitario).toLocaleString()}</td>
                            </tr>
                        `).join('')}
                    </table>
                    <p style="text-align:right; font-weight:bold; margin-top:10px;">Total: $${parseFloat(o.total).toLocaleString()}</p>
                    ${o.notas ? `<p><strong>Notas:</strong> ${o.notas}</p>` : ''}
                `;
                
                const btnRecibir = document.getElementById('btnRecibirOrden');
                if (btnRecibir) {
                    btnRecibir.style.display = (o.estado === 'pendiente' || o.estado === 'parcial') ? 'inline-block' : 'none';
                }
                
                document.getElementById('detalleModal').style.display = 'flex';
            }
        }

        async function recibirOrdenCompleta() {
            if (!ordenActual) return;
            if (!confirm('¿Marcar todos los items como recibidos?')) return;
            
            const res = await fetch(`../servicios/ordenes_compra.php?accion=ver&id=${ordenActual}`);
            const data = await res.json();
            
            if (data.status === 'success') {
                const items = data.data.detalles.map(d => ({
                    material_id: d.material_id,
                    cantidad_recibida: d.cantidad_pedida - d.cantidad_recibida
                }));
                
                const formData = new FormData();
                formData.append('accion', 'recibir');
                formData.append('orden_id', ordenActual);
                formData.append('items', JSON.stringify(items));
                
                const resRecibir = await fetch('../servicios/ordenes_compra.php', { method: 'POST', body: formData });
                const dataRecibir = await resRecibir.json();
                
                if (dataRecibir.status === 'success') {
                    alert('✅ Orden recibida. Stock actualizado.');
                    document.getElementById('detalleModal').style.display = 'none';
                    cargarOrdenes();
                } else {
                    alert('❌ ' + dataRecibir.message);
                }
            }
        }
    </script>
</body>
</html>
