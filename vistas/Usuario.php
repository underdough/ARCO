<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión para acceder al sistema");
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
    <title>ARCO - Gestión de Usuarios</title>
    <link rel="stylesheet" href="../componentes/modal-common.css">
    <link rel="stylesheet" href="../componentes/usuarios.css">
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/usuarios.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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
            <a href="usuario.php" class="menu-item active">
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
            <h2>Gestión de Usuarios</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar usuario...">
            </div>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="abrirModalCrearUsuario()">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- Eliminar completamente el div users-actions -->
        <!-- <div class="users-actions">
            <input type="text" class="form-control" placeholder="Buscar usuario..." />
            <button class="btn-login" onclick="abrirModalCrearUsuario()">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </button>
        </div> -->

        <div class="users-table">
            <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == '1') {
                echo "<p style='color: green; font-weight: bold;'>Usuario eliminado correctamente.</p>";
            } ?>

            <?php include '../servicios/listar_usuarios.php'; ?>
        </div>
    </div>

    <!-- MODAL EDITAR USUARIO -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-user-edit"></i> Editar Usuario
                </h3>
                <span class="close-modal" onclick="cerrarModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="contenidoModal"></div>
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
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numeroDocumento">Número de Documento</label>
                            <input type="text" id="numeroDocumento" name="numeroDocumento" class="form-control" minlength="6" maxlength="12" pattern="[0-9]+" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contrasena">Contraseña</label>
                            <div class="password-toggle">
                                <input type="password" id="contrasena" name="contrasena" class="form-control" maxlength="20" minlength="8" required>
                                <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('contrasena', this)"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="confirmarContrasena">Confirmar Contraseña</label>
                            <div class="password-toggle">
                                <input type="password" id="confirmarContrasena" name="confirmarContrasena" class="form-control" maxlength="20" minlength="8" required>
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
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('collapsed');
            }
        });

        function showUserMenu() {
            window.location.href = 'menuUsu.html';
        }

        // ===== FUNCIONES DE MODALES =====
        
        // Abrir modal de crear usuario
        function abrirModalCrearUsuario() {
            document.getElementById('crearUsuarioModal').style.display = 'flex';
        }
        
        // Cerrar modal de crear usuario
        function cerrarModalCrear() {
            document.getElementById('crearUsuarioModal').style.display = 'none';
            document.getElementById('formCrearUsuario').reset();
            document.getElementById('alertContainer').innerHTML = '';
        }
        
        // Cerrar modal de editar
        function cerrarModal() {
            document.getElementById('editarModal').style.display = 'none';
        }

        // Abrir modal de editar con AJAX
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const userId = this.getAttribute('data-id');
                    fetch(`../servicios/editar_usuario.php?id=${userId}`)
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('contenidoModal').innerHTML = html;
                            document.getElementById('editarModal').style.display = 'flex';
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            alert("Error al cargar formulario");
                        });
                });
            });
        });
        
        // Toggle visibilidad de contraseña
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Mostrar mensajes de alerta
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            setTimeout(() => alertContainer.innerHTML = '', 5000);
        }

        // ===== MANEJO DEL FORMULARIO =====
        
        // Envío del formulario de crear usuario
        document.getElementById('formCrearUsuario').addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const password = document.getElementById('contrasena').value;
            const confirmPassword = document.getElementById('confirmarContrasena').value;
            const submitBtn = document.getElementById('btnCrearUsuario');
            const form = document.getElementById('formCrearUsuario');
        
            // Validación de contraseñas
            if (password !== confirmPassword) {
                showAlert('Las contraseñas no coinciden', 'error');
                return;
            }
        
            // Mostrar estado de carga
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
            submitBtn.disabled = true;
        
            try {
                const formData = new FormData(this);
                
                const response = await fetch('../servicios/registro.php', {
                    method: 'POST',
                    body: formData
                });
        
                const result = await response.json();
        
                if (result.success) {
                    showAlert(result.message, 'success');
                    form.reset();
                    
                    // Cerrar modal y recargar tabla después de 2 segundos
                    setTimeout(() => {
                        cerrarModalCrear();
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
            } finally {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Usuario';
                submitBtn.disabled = false;
            }
        });

        // AGREGAR ESTE BLOQUE (funcionalidad Escape):
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                // Cerrar modal de crear usuario
                const modalCrear = document.getElementById('crearUsuarioModal');
                if (modalCrear.style.display === 'flex') {
                    cerrarModalCrear();
                }
                
                // Cerrar modal de editar usuario
                const modalEditar = document.getElementById('editarModal');
                if (modalEditar.style.display === 'flex') {
                    cerrarModal();
                }
            }
        });

        // ===== FUNCIONES AUXILIARES =====
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Mostrar mensajes de alerta
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            setTimeout(() => alertContainer.innerHTML = '', 5000);
        }

        // ===== MANEJO DEL FORMULARIO =====
        
        // Envío del formulario de crear usuario
        document.getElementById('formCrearUsuario').addEventListener('submit', async function (e) {
            e.preventDefault();
            
            const password = document.getElementById('contrasena').value;
            const confirmPassword = document.getElementById('confirmarContrasena').value;
            const submitBtn = document.getElementById('btnCrearUsuario');
            const form = document.getElementById('formCrearUsuario');
        
            // Validación de contraseñas
            if (password !== confirmPassword) {
                showAlert('Las contraseñas no coinciden', 'error');
                return;
            }
        
            // Mostrar estado de carga
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';
            submitBtn.disabled = true;
        
            try {
                const formData = new FormData(this);
                
                const response = await fetch('../servicios/registro.php', {
                    method: 'POST',
                    body: formData
                });
        
                const result = await response.json();
        
                if (result.success) {
                    showAlert(result.message, 'success');
                    form.reset();
                    
                    // Cerrar modal y recargar tabla después de 2 segundos
                    setTimeout(() => {
                        cerrarModalCrear();
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
            } finally {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Usuario';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>

</html>