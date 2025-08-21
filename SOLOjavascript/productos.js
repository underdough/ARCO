// Variables globales
        let productos = [];
        let categorias = [];
        let idProductoEditando = null;
        let ordenActual = { campo: 'nombre', direccion: 'ASC' };
        let busquedaActual = '';

        // Formatear precio en pesos colombianos
        function formatearPrecio(precio) {
            const numero = Number(precio);
            
            // Formatear con separador de miles (puntos) y decimales (comas)
            let formatted;
            if (numero % 1 === 0) {
                // Número entero - sin decimales
                formatted = numero.toLocaleString('co-CO', { 
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0 
                });
            } else {
                // Número con decimales
                formatted = numero.toLocaleString('co-CO', { 
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2 
                });
            }
            
            return '$' + formatted;
        }

        // Cargar productos desde la base de datos
        async function cargarProductos() {
            try {
                const params = new URLSearchParams({
                    orden: ordenActual.campo,
                    direccion: ordenActual.direccion,
                    busqueda: busquedaActual
                });
                
                const response = await fetch(`../servicios/listar_productos.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    productos = data.data;
                    renderizarTablaProductos();
                } else {
                    mostrarNotificacion('Error al cargar productos: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarNotificacion('Error de conexión: ' + error.message, 'error');
            }
        }

        // Cargar categorías desde la base de datos
        async function cargarCategorias() {
            try {
                const response = await fetch('../servicios/listar_categorias.php')

                const data = await response.json();
                
                if (data.success) {
                    categorias = data.data;
                    renderizarSelectCategorias();
                } else {
                    mostrarNotificacion('Error al cargar categorías: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarNotificacion('Error de conexión: ' + error.message, 'error');
            }
        }

        // Renderizar select de categorías
        function renderizarSelectCategorias() {
            const select = document.getElementById('productCategory');
            select.innerHTML = '<option value="">Seleccionar categoría</option>';
            
            categorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id_categorias;
                option.textContent = categoria.nombre_cat;
                select.appendChild(option);
            });
        }

        // Renderizar tabla de productos
        function renderizarTablaProductos() {
            const tbody = document.getElementById('productTableBody');
            tbody.innerHTML = '';
            
            if (productos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No se encontraron productos</td></tr>';
                return;
            }
            
            productos.forEach(producto => {
                const row = document.createElement('tr');
                
                const estadoClass = producto.estado === 'Disponible' ? 'status-disponible' : 
                producto.estado === 'Stock Bajo' ? 'status-bajo' : 'status-agotado';
                
                row.innerHTML = `
                    <td>${producto.id}</td>
                    <td>${producto.nombre}</td>
                    <td>${producto.categoria}</td>
                    <td>${producto.stock}</td>
                    <td>${formatearPrecio(producto.precio)}</td>
                    <td><span class="status ${estadoClass}">${producto.estado}</span></td>
                    <td>
                        <div class="actions">
                            <a href="#" class="action-icon btn-editar" onclick="editarProducto(${producto.id})" title="Editar producto">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="action-icon btn-eliminar" onclick="eliminarProducto(${producto.id}, '${producto.nombre}')" title="Eliminar producto">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }

        // Agregar producto
        async function agregarProducto(formData) {
            try {
                const response = await fetch('../servicios/agregar_producto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarNotificacion('Producto agregado exitosamente', 'success');
                    cargarProductos();
                    cerrarModalProducto();
                } else {
                    mostrarNotificacion('Error al agregar producto: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarNotificacion('Error de conexión: ' + error.message, 'error');
            }
        }

        // Editar producto
        async function editarProducto(id) {
            const producto = productos.find(p => p.id == id);
            if (!producto) return;
            
            idProductoEditando = id;
            abrirModal('Editar Producto');
            
            // Llenar formulario
            document.getElementById('productName').value = producto.nombre;
            document.getElementById('productCategory').value = getCategoriaIdByName(producto.categoria);
            document.getElementById('productStock').value = producto.stock;
            document.getElementById('productDescription').value = '';
        }

        // Actualizar producto
        async function actualizarProducto(formData) {
            try {
                formData.id = idProductoEditando;
                
                const response = await fetch('../servicios/editar_producto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarNotificacion('Producto actualizado exitosamente', 'success');
                    cargarProductos();
                    cerrarModalProducto();
                } else {
                    mostrarNotificacion('Error al actualizar producto: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarNotificacion('Error de conexión: ' + error.message, 'error');
            }
        }

        // Eliminar producto
        async function eliminarProducto(id, nombre) {
            if (!confirm(`¿Está seguro de que desea eliminar "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                return;
            }
            
            try {
                const response = await fetch('../servicios/eliminar_producto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });
                

                const data = await response.json();
                
                if (data.success) {
                    mostrarNotificacion(data.message, 'success');
                    cargarProductos();
                } else {
                    mostrarNotificacion('Error al eliminar producto: ' + data.error, 'error');
                }
            } catch (error) {
                mostrarNotificacion('Error de conexión: ' + error.message, 'error');
            }
        }

        // Obtener ID de categoría por nombre
        function getCategoriaIdByName(nombre) {
            const categoria = categorias.find(c => c.nombre_cat === nombre);
            return categoria ? categoria.id_categorias : '';
        }

        // Funciones del modal
        function abrirModal(titulo = 'Agregar Nuevo Producto') {
            const modal = document.getElementById('productModal');
            modal.classList.add('show');
            modal.style.display = 'flex';
            document.querySelector('.modal-title').textContent = titulo;
            document.body.style.overflow = 'hidden';
            
            if (titulo === 'Agregar Nuevo Producto') {
                idProductoEditando = null;
                document.getElementById('productForm').reset();
            }
        }

        function cerrarModalProducto() {
            const modal = document.getElementById('productModal');
            modal.classList.remove('show');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('productForm').reset();
            idProductoEditando = null;
        }

        // Sistema de notificaciones
        function mostrarNotificacion(mensaje, tipo = 'info', duracion = 3000) {
            let container = document.querySelector('.notification-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'notification-container';
                container.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 10000;
                    pointer-events: none;
                `;
                document.body.appendChild(container);
            }
            
            const notification = document.createElement('div');
            notification.className = `notification notification-${tipo}`;
            notification.style.cssText = `
                background: ${tipo === 'success' ? '#4CAF50' : tipo === 'error' ? '#f44336' : '#2196F3'};
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transform: translateX(100%);
                transition: all 0.3s ease;
                pointer-events: auto;
                cursor: pointer;
                max-width: 300px;
                word-wrap: break-word;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${mensaje}</span>
                    <i class="fas fa-times" style="margin-left: auto; cursor: pointer; opacity: 0.7;"></i>
                </div>
            `;
            
            container.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            const autoRemove = setTimeout(() => {
                removeNotification(notification);
            }, duracion);
            
            notification.addEventListener('click', () => {
                clearTimeout(autoRemove);
                removeNotification(notification);
            });
            
            function removeNotification(notif) {
                notif.style.transform = 'translateX(100%)';
                notif.style.opacity = '0';
                setTimeout(() => {
                    if (notif.parentNode) {
                        notif.parentNode.removeChild(notif);
                    }
                }, 300);
            }
        }

        // Animaciones de entrada
        function animateOnLoad() {
            const header = document.querySelector('.header');
            const table = document.querySelector('.products-table');
            
            if (header) {
                header.style.opacity = '0';
                header.style.transform = 'translateY(-30px)';
                
                setTimeout(() => {
                    header.style.transition = 'all 0.5s ease';
                    header.style.opacity = '1';
                    header.style.transform = 'translateY(0)';
                }, 100);
            }
            
            if (table) {
                table.style.opacity = '0';
                table.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    table.style.transition = 'all 0.6s ease';
                    table.style.opacity = '1';
                    table.style.transform = 'translateY(0)';
                }, 300);
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Cargar datos iniciales
            cargarCategorias();
            cargarProductos();
            
            // Botón agregar producto
            document.getElementById('btnAddProduct').addEventListener('click', () => {
                abrirModal('Agregar Nuevo Producto');
            });
            
            // Botón cancelar
            document.getElementById('btnCancelProduct').addEventListener('click', cerrarModalProducto);
            
            // Cerrar modal
            document.querySelector('.close-modal').addEventListener('click', cerrarModalProducto);
            
            // MANTENER ESTE BLOQUE (funcionalidad Escape):
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    cerrarModalProducto();
                }
            });
       
            window.removeEventListener('click', function() {}); 
            
            // Formulario de producto
            document.getElementById('productForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    nombre: document.getElementById('productName').value.trim(),
                    categoria_id: parseInt(document.getElementById('productCategory').value),
                    stock: parseInt(document.getElementById('productStock').value),
                    descripcion: document.getElementById('productDescription').value.trim()
                };
                
                // Validaciones
                if (!formData.nombre) {
                    mostrarNotificacion('El nombre del producto es requerido', 'error');
                    return;
                }
                
                if (!formData.categoria_id) {
                    mostrarNotificacion('Debe seleccionar una categoría', 'error');
                    return;
                }
                
                if (formData.stock < 0) {
                    mostrarNotificacion('El stock no puede ser negativo', 'error');
                    return;
                }
                
                if (idProductoEditando) {
                    actualizarProducto(formData);
                } else {
                    agregarProducto(formData);
                }
            });
            
            // Búsqueda
            document.querySelector('.search-bar input').addEventListener('input', function(e) {
                busquedaActual = e.target.value;
                cargarProductos();
            });
            
            // Ordenamiento
            document.getElementById('sortSelect').addEventListener('change', function(e) {
                const [campo, direccion] = e.target.value.split('-');
                ordenActual = { campo, direccion };
                cargarProductos();
            });
            
            // Sidebar toggle
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                const sidebar = document.querySelector('.sidebar');
                sidebar.classList.toggle('collapsed');
            });
            
            // Inicializar animaciones
            animateOnLoad();
            
            // Smooth scroll para navegación
            document.querySelectorAll('.menu-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    if (this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth' });
                        }
                    }
                });
            });
            
            // Agregar estilos CSS para animaciones adicionales
            const additionalStyles = `
                @keyframes fadeOut {
                    from { opacity: 1; transform: scale(1); }
                    to { opacity: 0; transform: scale(0.8); }
                }
                
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                
                .form-control.error {
                    border-color: #dc3545 !important;
                    box-shadow: 0 0 5px rgba(220, 53, 69, 0.3) !important;
                }
            `;
            
            const styleSheet = document.createElement('style');
            styleSheet.textContent = additionalStyles;
            document.head.appendChild(styleSheet);
        });