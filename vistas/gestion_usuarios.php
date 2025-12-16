<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión para acceder al sistema");
    exit;
}

// Verificar que sea administrador
if ($_SESSION['rol'] !== 'administrador') {
    header("Location: dashboard.php?error=No tiene permisos para acceder a esta sección");
    exit;
}

require_once '../servicios/conexion.php';

$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Gestión Avanzada de Usuarios</title>
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="stylesheet" href="../componentes/usuarios.css">
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .filters-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filters-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        
        .filter-input, .filter-select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .btn-filter {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-filter:hover {
            background: #45a049;
        }
        
        .btn-clear {
            padding: 10px 20px;
            background: #757575;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-clear:hover {
            background: #616161;
        }
        
        .users-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin: 0;
            color: #4CAF50;
        }
        
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-activo {
            background: #4CAF50;
            color: white;
        }
        
        .badge-inactivo {
            background: #f44336;
            color: white;
        }
        
        .badge-suspendido {
            background: #ff9800;
            color: white;
        }
        
        .badge-rol {
            background: #2196F3;
            color: white;
        }
        
        .action-buttons-group {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        
        .btn-edit:hover {
            background: #1976D2;
        }
        
        .btn-toggle {
            background: #ff9800;
            color: white;
        }
        
        .btn-toggle:hover {
            background: #f57c00;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-delete:hover {
            background: #d32f2f;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .filters-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* Estilos para alertas en modales */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            animation: fadeIn 0.3s ease-in;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mejoras en los modales */
        .modal {
            animation: fadeIn 0.3s ease-in;
        }
        
        .modal-content {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        /* Estilos para formularios */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }
        
        .form-control {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle input {
            padding-right: 40px;
        }
        
        .toggle-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        .toggle-icon:hover {
            color: #333;
        }
        
        /* Botones del modal */
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 15px 20px;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        
        .btn-secondary {
            background: #757575;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #616161;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="productos.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.php" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="gestion_usuarios.php" class="menu-item active">
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

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2><i class="fas fa-users-cog"></i> Gestión Avanzada de Usuarios</h2>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="abrirModalCrearUsuario()">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="users-stats" id="statsContainer">
            <div class="stat-card">
                <h3 id="totalUsuarios">0</h3>
                <p>Total Usuarios</p>
            </div>
            <div class="stat-card">
                <h3 id="usuariosActivos">0</h3>
                <p>Activos</p>
            </div>
            <div class="stat-card">
                <h3 id="usuariosInactivos">0</h3>
                <p>Inactivos</p>
            </div>
        </div>

        <!-- Filtros de búsqueda -->
        <div class="filters-container">
            <div class="filters-row">
                <div class="filter-group">
                    <label for="searchInput">
                        <i class="fas fa-search"></i> Buscar
                    </label>
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="filter-input" 
                        placeholder="Buscar por nombre, apellido, correo o documento..."
                    >
                </div>
                
                <div class="filter-group">
                    <label for="filterRol">
                        <i class="fas fa-user-tag"></i> Rol
                    </label>
                    <select id="filterRol" class="filter-select">
                        <option value="">Todos los roles</option>
                        <option value="administrador">Administrador</option>
                        <option value="usuario">Usuario</option>
                        <option value="almacenista">Almacenista</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="gerente">Gerente</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filterEstado">
                        <i class="fas fa-toggle-on"></i> Estado
                    </label>
                    <select id="filterEstado" class="filter-select">
                        <option value="">Todos los estados</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                        <option value="SUSPENDIDO">Suspendido</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button class="btn-filter" onclick="aplicarFiltros()">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="users-table" id="usersTableContainer">
            <div class="loading">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Cargando usuarios...</p>
            </div>
        </div>
    </div>

    <!-- MODAL CREAR USUARIO -->
    <div id="crearUsuarioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
                </h3>
                <span class="close-modal" onclick="cerrarModalCrear()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="alertContainer"></div>
                <form id="formCrearUsuario">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido">Apellido *</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numeroDocumento">Número de Documento *</label>
                            <input type="text" id="numeroDocumento" name="numeroDocumento" class="form-control" 
                                   minlength="6" maxlength="12" pattern="[0-9]+" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control" 
                                   maxlength="10" pattern="[0-9]+">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="rol">Rol *</label>
                            <select id="rol" name="rol" class="form-control" required>
                                <option value="">Seleccione un rol</option>
                                <option value="usuario">Usuario</option>
                                <option value="almacenista">Almacenista</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="gerente">Gerente</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cargos">Cargo/Área *</label>
                            <input type="text" id="cargos" name="cargos" class="form-control" 
                                   placeholder="Ej: Almacén Principal" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contrasena">Contraseña *</label>
                            <div class="password-toggle">
                                <input type="password" id="contrasena" name="contrasena" class="form-control" 
                                       maxlength="20" minlength="8" required>
                                <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('contrasena', this)"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmarContrasena">Confirmar Contraseña *</label>
                            <div class="password-toggle">
                                <input type="password" id="confirmarContrasena" name="confirmarContrasena" 
                                       class="form-control" maxlength="20" minlength="8" required>
                                <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('confirmarContrasena', this)"></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalCrear()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="formCrearUsuario" id="btnCrearUsuario" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Usuario
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR USUARIO -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-edit"></i> Editar Usuario
                </h3>
                <span class="close-modal" onclick="cerrarModalEditar()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="alertContainerEdit"></div>
                <form id="formEditarUsuario">
                    <input type="hidden" id="edit_id_usuarios" name="id_usuarios">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_nombre">Nombre *</label>
                            <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_apellido">Apellido *</label>
                            <input type="text" id="edit_apellido" name="apellido" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_num_doc">Número de Documento *</label>
                            <input type="text" id="edit_num_doc" name="num_doc" class="form-control" 
                                   minlength="6" maxlength="12" pattern="[0-9]+" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_telefono">Teléfono</label>
                            <input type="text" id="edit_telefono" name="num_telefono" class="form-control" 
                                   maxlength="10" pattern="[0-9]+">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_correo">Correo Electrónico *</label>
                            <input type="email" id="edit_correo" name="correo" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_rol">Rol *</label>
                            <select id="edit_rol" name="rol" class="form-control" required>
                                <option value="usuario">Usuario</option>
                                <option value="almacenista">Almacenista</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="gerente">Gerente</option>
                                <option value="administrador">Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_cargos">Cargo/Área *</label>
                            <input type="text" id="edit_cargos" name="cargos" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_estado">Estado *</label>
                            <select id="edit_estado" name="estado" class="form-control" required>
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                                <option value="SUSPENDIDO">Suspendido</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalEditar()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" form="formEditarUsuario" id="btnEditarUsuario" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <script src="../componentes/gestion_usuarios.js"></script>
</body>
</html>
