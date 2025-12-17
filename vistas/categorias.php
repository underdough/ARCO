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
verificarAccesoModulo('categorias');

// Obtener permisos del usuario para este módulo
$permisos = obtenerPermisosUsuario('categorias');
$puedeCrear = in_array('crear', $permisos);
$puedeEditar = in_array('editar', $permisos);
$puedeEliminar = in_array('eliminar', $permisos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Gestión de Categorías</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/categorias.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        <?php echo generarMenuHTML('categorias'); ?>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Categorías</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar categorías...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if ($puedeCrear): ?>
                <button class="btn btn-primary" id="btnAddCategory">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
                <?php else: ?>
                <button class="btn btn-primary" disabled title="No tiene permisos">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="categories-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Electrónicos</td>
                        <td>Productos electrónicos y tecnológicos</td>
                        <td>42</td>
                        <td>Activa</td>
                        <td class="actions">
                            <button class="action-icon edit"><i class="fas fa-edit"></i></button>
                            <button class="action-icon delete"><i class="fas fa-trash"></i></button>
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

    <div class="modal" id="categoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nueva Categoría</h3>
                <button class="close-modal">&times;</button>
            </div>
            
            <form id="categoryForm">
                <div class="form-group">
                    <label for="categoryName">Nombre de la Categoría</label>
                    <input type="text" class="form-control" id="categoryName" placeholder="Ingrese el nombre de la categoría" required>
                </div>
                
                <div class="form-group">
                    <label for="categoryDescription">Descripción</label>
                    <textarea class="form-control" id="categoryDescription" rows="3" placeholder="Descripción opcional de la categoría"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="categoryProducts">Cantidad de Productos</label>
                    <input type="number" class="form-control" id="categoryProducts" min="0" value="0" required>
                </div>
    
                <div class="form-group">
                    <label for="categoryStatus">Estado</label>
                    <select class="form-control" id="categoryStatus" required>
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>
            </form>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelCategory">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="categoryForm">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        // categories.js

        document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('categoryModal');
    const btnAddCategory = document.getElementById('btnAddCategory');
    const btnCancelCategory = document.getElementById('btnCancelCategory');
    const closeModal = document.querySelector('.close-modal');
    const searchInput = document.querySelector('.search-bar input');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const pageItems = document.querySelectorAll('.page-item');

    cargarCategorias();

    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    document.addEventListener('click', event => {
        if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    function openModal(title, isEdit = false) {
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease';

        const modalContent = document.querySelector('.modal-content');
        modalContent.style.animation = 'slideInDown 0.4s ease';

        document.querySelector('.modal-title').textContent = title;

        if (!isEdit) {
            document.getElementById('categoryForm').reset();
            delete document.getElementById('categoryForm').dataset.id;
        }

        setTimeout(() => {
            document.getElementById('categoryName').focus();
        }, 400);
    }

    function closeCategoryModal() {
        const modalContent = document.querySelector('.modal-content');
        modalContent.style.animation = 'slideOutUp 0.3s ease';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    btnAddCategory.addEventListener('click', () => openModal('Agregar Nueva Categoría'));
    btnCancelCategory.addEventListener('click', closeCategoryModal);
    closeModal.addEventListener('click', closeCategoryModal);

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.style.display === 'flex') {
            closeCategoryModal();
        }
    });


    document.getElementById('categoryForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const idCategoria = this.dataset.id || '';
        const nombre = document.getElementById('categoryName').value;
        const descripcion = document.getElementById('categoryDescription').value;
        const productos = document.getElementById('categoryProducts').value;
        const estado = document.getElementById('categoryStatus').value;

        const formData = new FormData();
        formData.append('nombre_cat', nombre);
        formData.append('subcategorias', descripcion);
        formData.append('productos', productos);
        formData.append('estado', estado);

        const url = idCategoria
            ? `../servicios/editar_categoria.php?id=${idCategoria}`
            : '../servicios/agregar_categoria.php';

        fetch(url, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeCategoryModal();
                    cargarCategorias();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error(error);
                showNotification('Error al guardar la categoría', 'error');
            });
    });

    function cargarCategorias() {
        fetch('../servicios/listar_categorias.php')
            .then(res => res.json())
            .then(data => {

                const categorias = data.data || data;

                const tbody = document.querySelector('tbody');
                tbody.innerHTML = '';


                categorias.forEach(cat => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${cat.id_categorias}</td>
                        <td>${cat.nombre_cat}</td>
                        <td>${cat.subcategorias}</td>
                        <td>${cat.productos ?? '--'}</td>
                        <td>${cat.estado == 1 ? 'Activa' : 'Inactiva'}</td>
                        <td class="actions">
                            <button class="action-icon edit"><i class="fas fa-edit"></i></button>
                            <button class="action-icon delete"><i class="fas fa-trash"></i></button>
                        </td>
                    `;

                    tr.querySelector('.edit').addEventListener('click', function () {
                        openModal('Editar Categoría', true);
                        document.getElementById('categoryName').value = cat.nombre_cat;
                        document.getElementById('categoryDescription').value = cat.subcategorias;
                        document.getElementById('categoryProducts').value = cat.productos || 0;
                        document.getElementById('categoryStatus').value = cat.estado;
                        document.getElementById('categoryForm').dataset.id = cat.id_categorias;
                    });

                    tr.querySelector('.delete').addEventListener('click', function () {
                        if (confirm(`¿Eliminar la categoría "${cat.nombre_cat}"?`)) {
                            fetch(`../servicios/eliminar_categoria.php?id=${cat.id_categorias}`)
                                .then(res => res.json())
                                .then(result => {
                                    if (result.success) {
                                        tr.remove();
                                        showNotification('Categoría eliminada', 'success');
                                    } else {
                                        showNotification(result.message, 'error');
                                    }
                                });
                        }
                    });

                    tbody.appendChild(tr);
                });
            });
    }

    pageItems.forEach(item => {
        item.addEventListener('click', function () {
            if (!this.classList.contains('active')) {
                pageItems.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');

        tableRows.forEach(row => {
            const categoryName = row.cells[1].textContent.toLowerCase();
            const categoryDesc = row.cells[2].textContent.toLowerCase();
            row.style.display = (categoryName.includes(searchTerm) || categoryDesc.includes(searchTerm)) ? '' : 'none';
        });
    });

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button class="close-notification">&times;</button>
        `;

        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#4CAF50' : '#2196F3',
            color: 'white',
            padding: '15px 20px',
            borderRadius: '5px',
            boxShadow: '0 4px 15px rgba(0, 0, 0, 0.2)',
            zIndex: '10000',
            display: 'flex',
            alignItems: 'center',
            gap: '10px',
            animation: 'slideInRight 0.3s ease',
            maxWidth: '300px'
        });

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);

        notification.querySelector('.close-notification').addEventListener('click', () => {
            notification.remove();
        });
    }
});


    </script>
    <?php echo generarScriptPermisos('categorias'); ?>
    <script>
        // Aplicar permisos a la tabla cuando se carguen las categorías
        const puedeEditar = <?php echo $puedeEditar ? 'true' : 'false'; ?>;
        const puedeEliminar = <?php echo $puedeEliminar ? 'true' : 'false'; ?>;
    </script>
</body>

</html>