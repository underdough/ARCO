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
verificarAccesoModulo('productos');

// Obtener permisos del usuario para este módulo
$permisos = obtenerPermisosUsuario('productos');
$puedeCrear = in_array('crear', $permisos);
$puedeEditar = in_array('editar', $permisos);
$puedeEliminar = in_array('eliminar', $permisos);
$puedeExportar = in_array('exportar', $permisos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Gestión de Productos</title>
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="stylesheet" href="../componentes/productos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php echo generarSidebarCompleto('productos'); ?>
    
    <div class="main-content">
        <div class="header">
            <h2>Gestión de Productos</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar productos...">
            </div>
            <div class="action-buttons">
                <select id="sortSelect" class="btn btn-secondary" style="margin-right: 10px;">
                    <option value="nombre-ASC">Nombre A-Z</option>
                    <option value="nombre-DESC">Nombre Z-A</option>
                    <option value="categoria-ASC">Categoría A-Z</option>
                    <option value="categoria-DESC">Categoría Z-A</option>
                    <option value="stock-ASC">Stock Menor-Mayor</option>
                    <option value="stock-DESC">Stock Mayor-Menor</option>
                    <option value="precio-ASC">Precio Menor-Mayor</option>
                    <option value="precio-DESC">Precio Mayor-Menor</option>
                </select>
                <?php if ($puedeCrear): ?>
                <button class="btn btn-primary" id="btnAddProduct">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
                <?php else: ?>
                <button class="btn btn-primary" disabled title="No tiene permisos para crear productos">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Stock</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <!-- Los productos se cargarán dinámicamente desde la base de datos -->
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
    
    <!-- Modal para agregar/editar producto -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nuevo Producto</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <div class="form-group">
                        <label for="productName">Nombre del Producto</label>
                        <input type="text" class="form-control" id="productName" required>
                    </div>
                    <div class="form-group">
                        <label for="productCategory">Categoría</label>
                        <select class="form-control" id="productCategory" required>
                            <option value="">Seleccionar categoría</option>
                            <!-- Las categorías se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="productStock">Stock</label>
                        <input type="number" class="form-control" id="productStock" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="productPrice">Precio</label>
                        <input type="number" class="form-control" id="productPrice" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Descripción</label>
                        <textarea class="form-control" id="productDescription" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelProduct">Cancelar</button>
                <button type="submit" class="btn btn-primary" form="productForm">Guardar</button>
            </div>
        </div>
    </div>
    
    <!-- Botón toggle para sidebar en móvil -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <script src="../SOLOjavascript/productos.js"></script>
    <script src="../public/js/admin-verification.js"></script>
    <?php echo generarScriptPermisos('productos'); ?>
    <script>
        // Aplicar permisos a botones de acción en la tabla
        document.addEventListener('DOMContentLoaded', function() {
            if (!tienePermiso('editar')) {
                ocultarSinPermiso('.action-icon.edit', 'editar');
            }
            if (!tienePermiso('eliminar')) {
                ocultarSinPermiso('.action-icon.delete', 'eliminar');
            }
        });
    </script>
</body>
</html>