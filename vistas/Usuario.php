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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ARCO - Usuarios</title>
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
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>

        <div class="users-actions">
            <input type="text" class="form-control" placeholder="Buscar usuario..." />
            <button class="btn-login" onclick="abrirModalCrearUsuario()">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </button>
        </div>

        <div class="users-table">
            <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == '1') {
                echo "<p style='color: green; font-weight: bold;'>Usuario eliminado correctamente.</p>";
            } ?>

            <?php include '../servicios/listar_usuarios.php'; ?>
        </div>
    </div>

    <!-- MODAL -->
    <div id="editarModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            <div id="contenidoModal"></div>
        </div>
    </div>

    <!-- MODAL CREAR USUARIO -->
    <div id="crearUsuarioModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="cerrarModalCrear()">&times;</span>
            <div id="contenidoModalCrear">
                <h3><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h3>
                <form id="formCrearUsuario">
                    <div id="alertContainer"></div>
                    
                    <label for="nombreCompleto">Nombre Completo</label>
                    <input type="text" id="nombreCompleto" name="nombreCompleto" required>
                    
                    <label for="numeroDocumento">Número de Documento</label>
                    <input type="text" id="numeroDocumento" name="numeroDocumento" minlength="6" maxlength="12" pattern="[0-9]+" required>
                    
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="contrasena">Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" id="contrasena" name="contrasena" maxlength="20" minlength="8" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('contrasena', this)"></i>
                    </div>
                    
                    <label for="confirmarContrasena">Confirmar Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" id="confirmarContrasena" name="confirmarContrasena" maxlength="20" minlength="8" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('confirmarContrasena', this)"></i>
                    </div>
                    
                    <button type="submit" id="btnCrearUsuario">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

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

        // Abrir modal con AJAX
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
                    .catch(err => alert("Error al cargar formulario"));
            });
        });

        function cerrarModal() {
            document.getElementById('editarModal').style.display = 'none';
        }

        // Función para abrir modal de crear usuario
        function abrirModalCrearUsuario() {
            document.getElementById('crearUsuarioModal').style.display = 'flex';
        }
        
        // Función para cerrar modal de crear usuario
        function cerrarModalCrear() {
            document.getElementById('crearUsuarioModal').style.display = 'none';
            document.getElementById('formCrearUsuario').reset();
            document.getElementById('alertContainer').innerHTML = '';
        }
        
        // Función para mostrar/ocultar contraseña
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
        
        // Función para mostrar mensajes de alerta
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            setTimeout(() => alertContainer.innerHTML = '', 5000);
        }
        
        // Manejar envío del formulario de crear usuario
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
                        location.reload(); // Recargar para mostrar el nuevo usuario
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
        
        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(e) {
            const modalCrear = document.getElementById('crearUsuarioModal');
            const modalEditar = document.getElementById('editarModal');
            
            if (e.target === modalCrear) {
                cerrarModalCrear();
            }
            if (e.target === modalEditar) {
                cerrarModal();
            }
        });
    </script>
</body>

</html>