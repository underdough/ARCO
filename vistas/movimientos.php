<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}

// Incluir sistema de permisos
require_once '../servicios/middleware_permisos.php';
require_once '../servicios/menu_dinamico.php';

// Verificar acceso al módulo
verificarAccesoModulo('movimientos');

// Obtener permisos del usuario para este módulo
$permisos = obtenerPermisosUsuario('movimientos');
$puedeCrear = in_array('crear', $permisos);
$puedeEditar = in_array('editar', $permisos);
$puedeExportar = in_array('exportar', $permisos);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Movimientos de Inventario</title>
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="stylesheet" href="../componentes/movimientos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php echo generarSidebarCompleto('movimientos'); ?>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Movimientos</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar movimientos...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if ($puedeExportar): ?>
                <button class="btn btn-secondary" id="btnExportar">
                    <i class="fas fa-download"></i> Exportar
                </button>
                <?php endif; ?>
                <?php if ($puedeCrear): ?>
                <button class="btn btn-primary" id="btnAddMovement">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </button>
                <?php else: ?>
                <button class="btn btn-primary" disabled title="No tiene permisos para crear movimientos">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="filter-panel" id="filterPanel" style="display: none; margin-top: 1rem;">
            <form id="filterForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="filterTipo">Tipo de Movimiento</label>
                        <select id="filterTipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="recibido">Recibido (OC)</option>
                            <option value="devolucion">Devolución</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filterUsuario">Usuario</label>
                        <input type="text" id="filterUsuario" class="form-control" placeholder="Nombre del usuario">
                    </div>
                    <div class="form-group">
                        <label for="filterFecha">Fecha</label>
                        <input type="date" id="filterFecha" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
                <button type="button" class="btn btn-secondary" id="btnResetFilter">Restablecer</button>
            </form>
        </div>


        <div class="movements-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Notas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td><span class="status status-entrada"></span></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="actions">
                            <div class="action-icon view"><i class="fas fa-eye"></i></div>
                            <div class="action-icon print"><i class="fas fa-print"></i></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="page-item"><i class="fas fa-chevron-left"></i></div>
            <div class="page-item active">1</div>
            <div class="page-item">2</div>
            <div class="page-item">3</div>
            <div class="page-item"><i class="fas fa-chevron-right"></i></div>
        </div>
    </div>

    <!-- Modal para registrar movimiento -->
    <div class="modal" id="movementModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Registrar Nuevo Movimiento</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="movementForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="movementType">Tipo de Movimiento</label>
                            <select class="form-control" id="movementType" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="entrada">Entrada</option>
                                <option value="salida">Salida</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="recibido">Recibido (desde OC)</option>
                                <option value="devolucion">Devolución</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="movementProduct">Producto</label>
                            <select class="form-control" id="movementProduct" required>
                                <option value="">Seleccionar producto</option>
                                <!-- Las opciones se llenarán automáticamente -->
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="movementDate">Fecha</label>
                            <input type="date" class="form-control" id="movementDate" required>
                        </div>
                        <div class="form-group">
                            <label for="movementQuantity">Cantidad</label>
                            <input type="number" class="form-control" id="movementQuantity" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="movementUser">Usuario</label>
                        <select class="form-control" id="movementUser" required>
                            <option value="1">Admin</option>
                            <option value="2">Supervisor</option>
                            <option value="3">Vendedor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="movementNotes">Notas</label>
                        <textarea class="form-control" id="movementNotes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelMovement">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="movementForm">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalles del movimiento -->
    <div class="modal" id="viewMovementModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detalle del Movimiento</h3>
                <button class="close-modal" id="closeViewModal">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="detalleId"></span></p>
                <p><strong>Fecha:</strong> <span id="detalleFecha"></span></p>
                <p><strong>Tipo:</strong> <span id="detalleTipo"></span></p>
                <p><strong>Producto:</strong> <span id="detalleProducto"></span></p>
                <p><strong>Cantidad:</strong> <span id="detalleCantidad"></span></p>
                <p><strong>Usuario:</strong> <span id="detalleUsuario"></span></p>
                <p><strong>Notas:</strong> <span id="detalleNotas"></span></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cerrarDetalleBtn">Cerrar</button>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let movimientosData = []; // Variable para almacenar todos los movimientos
            let paginaActual = 1;
            const itemsPorPagina = 20; // 10 items por página para mejor visualización

            function cargarMovimientos(filtros = {}) {
                // Mostrar indicador de carga
                const tbody = document.querySelector('.movements-table tbody');
                if (filtros.busqueda !== undefined) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Buscando...</td></tr>';
                }

                // Construir URL con parámetros de filtro
                let url = '../servicios/filtrar_movimientos.php';
                const params = new URLSearchParams();

                if (filtros.tipo) params.append('tipo', filtros.tipo);
                if (filtros.usuario) params.append('usuario', filtros.usuario);
                if (filtros.fecha) params.append('fecha', filtros.fecha);
                if (filtros.busqueda !== undefined) params.append('busqueda', filtros.busqueda);

                if (params.toString()) {
                    url += '?' + params.toString();
                }

                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        movimientosData = data;
                        paginaActual = 1; // Reset a página 1 cuando se cargan nuevos datos
                        mostrarMovimientos();
                    })
                    .catch(err => {
                        console.error('Error al cargar movimientos:', err);
                        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: red;">Error al cargar movimientos</td></tr>';
                    });
            }

            function mostrarMovimientos() {
                const tbody = document.querySelector('.movements-table tbody');
                tbody.innerHTML = '';

                if (movimientosData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px;">No se encontraron movimientos</td></tr>';
                    actualizarPaginacion(0);
                    return;
                }

                // Calcular índices de paginación
                const inicio = (paginaActual - 1) * itemsPorPagina;
                const fin = inicio + itemsPorPagina;
                const movimientosPagina = movimientosData.slice(inicio, fin);

                movimientosPagina.forEach(mov => {
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${mov.id}</td>
                        <td>${mov.fecha}</td>
                        <td><span class="status status-${mov.tipo}">${mov.tipo}</span></td>
                        <td>${mov.producto}</td>
                        <td>${mov.cantidad}</td>
                        <td>${mov.usuario_nombre || 'Sin nombre'}</td>
                        <td>${mov.notas}</td>
                        <td class="actions">
                            <button class="action-icon view" data-id="${mov.id}"><i class="fas fa-eye"></i></button>
                            <button class="action-icon print" data-id="${mov.id}"><i class="fas fa-print"></i></button>
                        </td>
                    `;
                    tbody.appendChild(fila);

                    // Evento Ver
                    const viewBtn = fila.querySelector('.view');
                    viewBtn.addEventListener('click', () => {
                        const id = viewBtn.dataset.id;
                        fetch(`../servicios/obtener_detalle_movimiento.php?id=${id}`)
                            .then(res => res.json())
                            .then(response => {
                                if (response.success && response.data) {
                                    mostrarModalDetalleMovimiento(response.data);
                                } else if (response.id) {
                                    // Formato directo (compatibilidad)
                                    mostrarModalDetalleMovimiento(response);
                                } else {
                                    alert('Error: ' + (response.error || 'No se encontró el movimiento'));
                                }
                            })
                            .catch(err => {
                                console.error('Error fetch detalle:', err);
                                alert('Error al obtener detalles: ' + err);
                            });
                    });

                    // Evento imprimir
                    const printBtn = fila.querySelector('.print');
                    printBtn.addEventListener('click', () => {
                        window.location.href = `../servicios/imprimir_movimiento.php?id=${mov.id}`;
                    });
                });

                actualizarPaginacion(movimientosData.length);
            }

            function actualizarPaginacion(totalItems) {
                const totalPaginas = Math.ceil(totalItems / itemsPorPagina);
                const paginationDiv = document.querySelector('.pagination');
                paginationDiv.innerHTML = '';

                if (totalPaginas <= 1) {
                    paginationDiv.style.display = 'none';
                    return;
                }

                paginationDiv.style.display = 'flex';

                // Botón anterior
                const btnAnterior = document.createElement('div');
                btnAnterior.className = 'page-item' + (paginaActual === 1 ? ' disabled' : '');
                btnAnterior.innerHTML = '<i class="fas fa-chevron-left"></i>';
                btnAnterior.addEventListener('click', () => {
                    if (paginaActual > 1) {
                        paginaActual--;
                        mostrarMovimientos();
                    }
                });
                paginationDiv.appendChild(btnAnterior);

                // Páginas
                let inicio = Math.max(1, paginaActual - 2);
                let fin = Math.min(totalPaginas, paginaActual + 2);

                if (inicio > 1) {
                    const primera = document.createElement('div');
                    primera.className = 'page-item';
                    primera.textContent = '1';
                    primera.addEventListener('click', () => {
                        paginaActual = 1;
                        mostrarMovimientos();
                    });
                    paginationDiv.appendChild(primera);

                    if (inicio > 2) {
                        const puntos = document.createElement('div');
                        puntos.className = 'page-item disabled';
                        puntos.textContent = '...';
                        paginationDiv.appendChild(puntos);
                    }
                }

                for (let i = inicio; i <= fin; i++) {
                    const pageItem = document.createElement('div');
                    pageItem.className = 'page-item' + (i === paginaActual ? ' active' : '');
                    pageItem.textContent = i;
                    pageItem.addEventListener('click', () => {
                        paginaActual = i;
                        mostrarMovimientos();
                    });
                    paginationDiv.appendChild(pageItem);
                }

                if (fin < totalPaginas) {
                    if (fin < totalPaginas - 1) {
                        const puntos = document.createElement('div');
                        puntos.className = 'page-item disabled';
                        puntos.textContent = '...';
                        paginationDiv.appendChild(puntos);
                    }

                    const ultima = document.createElement('div');
                    ultima.className = 'page-item';
                    ultima.textContent = totalPaginas;
                    ultima.addEventListener('click', () => {
                        paginaActual = totalPaginas;
                        mostrarMovimientos();
                    });
                    paginationDiv.appendChild(ultima);
                }

                // Botón siguiente
                const btnSiguiente = document.createElement('div');
                btnSiguiente.className = 'page-item' + (paginaActual === totalPaginas ? ' disabled' : '');
                btnSiguiente.innerHTML = '<i class="fas fa-chevron-right"></i>';
                btnSiguiente.addEventListener('click', () => {
                    if (paginaActual < totalPaginas) {
                        paginaActual++;
                        mostrarMovimientos();
                    }
                });
                paginationDiv.appendChild(btnSiguiente);
            }

            // Funcionalidad del botón de filtrar
            const filterBtn = document.querySelector('.btn-secondary');
            const filterPanel = document.getElementById('filterPanel');

            if (filterBtn && filterPanel) {
                filterBtn.addEventListener('click', function() {
                    if (filterPanel.style.display === 'none' || filterPanel.style.display === '') {
                        filterPanel.style.display = 'block';
                    } else {
                        filterPanel.style.display = 'none';
                    }
                });
            }

            // Funcionalidad del formulario de filtros
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const filtros = {
                        tipo: document.getElementById('filterTipo').value,
                        usuario: document.getElementById('filterUsuario').value,
                        fecha: document.getElementById('filterFecha').value
                    };

                    cargarMovimientos(filtros);
                });
            }

            // Funcionalidad del botón restablecer
            const btnResetFilter = document.getElementById('btnResetFilter');
            if (btnResetFilter) {
                btnResetFilter.addEventListener('click', function() {
                    if (filterForm) {
                        filterForm.reset();
                    }
                    cargarMovimientos(); // Cargar todos los movimientos
                });
            }

            // Funcionalidad de la barra de búsqueda
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const busqueda = this.value.trim();
                        console.log('Buscando:', busqueda); // Para debug
                        if (busqueda.length >= 1 || busqueda.length === 0) {
                            cargarMovimientos({
                                busqueda: busqueda
                            });
                        }
                    }, 300); // Reducido a 300ms para respuesta más rápida
                });

                // También agregar evento para búsqueda al presionar Enter
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        const busqueda = this.value.trim();
                        cargarMovimientos({
                            busqueda: busqueda
                        });
                    }
                });
            }

            // Mostrar modal al hacer clic en "Nuevo Movimiento"
            const btnAddMovement = document.getElementById('btnAddMovement');
            if (btnAddMovement) {
                btnAddMovement.addEventListener('click', function() {
                    document.getElementById('movementModal').style.display = 'flex';
                });
            }
            
            // Cerrar modal al hacer clic en "Cancelar" o la X
            const closeModalBtn = document.querySelector('#movementModal .close-modal');
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', () => {
                    document.getElementById('movementModal').style.display = 'none';
                });
            }

            // MANTENER ESTE BLOQUE (funcionalidad Escape):
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    // Cerrar modal de nuevo movimiento
                    const movementModal = document.getElementById('movementModal');
                    if (movementModal.style.display === 'flex') {
                        movementModal.style.display = 'none';
                    }

                    // Cerrar modal de detalle de movimiento
                    const viewMovementModal = document.getElementById('viewMovementModal');
                    if (viewMovementModal.style.display === 'flex') {
                        viewMovementModal.style.display = 'none';
                    }
                }
            });

            const movementForm = document.getElementById('movementForm');
            if (movementForm) {
                movementForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const tipo = document.getElementById('movementType').value;
                    const fecha = document.getElementById('movementDate').value;
                    const producto = document.getElementById('movementProduct').value;
                    const cantidad = document.getElementById('movementQuantity').value;
                    const notas = document.getElementById('movementNotes').value;

                    const formData = new FormData();
                    formData.append('tipo', tipo);
                    formData.append('fecha', fecha);
                    formData.append('producto', producto);
                    formData.append('cantidad', cantidad);
                    formData.append('notas', notas);

                    fetch('../servicios/guardar_movimiento.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert('✅ ' + data.message);
                                document.getElementById('movementModal').style.display = 'none';
                                cargarMovimientos(); // vuelve a cargar la tabla
                            } else {
                                alert('❌ ' + data.message);
                            }
                        })
                        .catch(err => {
                            console.error('Error al guardar movimiento:', err);
                            alert('❌ Error al enviar los datos');
                        });
                });
            }

            function cargarProductos() {
                fetch('../servicios/obtener_productos.php')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById('movementProduct');
                        data.forEach(producto => {
                            const option = document.createElement('option');
                            option.value = producto.id; // debe coincidir con el campo en tu tabla productos
                            option.textContent = producto.nombre; // o el campo que uses como nombre visible
                            select.appendChild(option);
                        });
                    })
                    .catch(err => {
                        console.error('Error al cargar productos:', err);
                    });
            }




            function mostrarModalDetalleMovimiento(mov) {
                document.getElementById('detalleId').innerText = mov.id || '-';
                document.getElementById('detalleFecha').innerText = mov.fecha || '-';
                document.getElementById('detalleTipo').innerText = mov.tipo || '-';
                document.getElementById('detalleProducto').innerText = mov.producto || '-';
                document.getElementById('detalleCantidad').innerText = mov.cantidad || '-';
                document.getElementById('detalleUsuario').innerText = mov.usuario_nombre || mov.usuario || 'Sin nombre';
                document.getElementById('detalleNotas').innerText = mov.notas || 'Sin notas';

                document.getElementById('viewMovementModal').style.display = 'flex';
            }

            const closeViewModal = document.getElementById('closeViewModal');
            if (closeViewModal) {
                closeViewModal.addEventListener('click', () => {
                    document.getElementById('viewMovementModal').style.display = 'none';
                });
            }

            const cerrarDetalleBtn = document.getElementById('cerrarDetalleBtn');
            if (cerrarDetalleBtn) {
                cerrarDetalleBtn.addEventListener('click', () => {
                    document.getElementById('viewMovementModal').style.display = 'none';
                });
            }

            cargarProductos();

            cargarMovimientos();
        });
    </script>
    <?php echo generarScriptPermisos('movimientos'); ?>
</body>

</html>