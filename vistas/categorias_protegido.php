<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}

// Incluir middleware de permisos
require_once '../servicios/middleware_permisos.php';

// Verificar acceso al módulo de categorías
verificarAccesoModulo('categorias');

// Obtener permisos del usuario
$permisos = obtenerPermisosUsuario('categorias');
$infoUsuario = obtenerInfoUsuario();
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
    
    <script>
        // Permisos del usuario disponibles en JavaScript
        window.userPermissions = <?php echo generarPermisosJS('categorias'); ?>;
        window.userInfo = <?php echo json_encode($infoUsuario); ?>;
    </script>
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
            <a href="productos_protegido.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias_protegido.php" class="menu-item active">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="gestion_usuarios.php" class="menu-item">
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
            <a href="../servicios/logout.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Categorías</h2>
            <div class="user-badge">
                <i class="fas fa-user-shield"></i>
                <span><?php echo htmlspecialchars($infoUsuario['nombre_completo']); ?> (<?php echo ucfirst($infoUsuario['rol']); ?>)</span>
            </div>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar categorías..." id="searchInput">
            </div>
            <div class="action-buttons">
                <button class="btn btn-primary" id="btnAddCategory" <?php echo mostrarSiTienePermiso('categorias', 'crear'); ?>>
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
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
                <tbody id="categoryTableBody">
                    <!-- Las categorías se cargarán dinámicamente -->
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

    <script src="../SOLOjavascript/categorias_protegido.js"></script>
</body>

</html>
