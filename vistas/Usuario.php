<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión para acceder al sistema");
    exit;
}

require_once '../servicios/conexion.php';  // usar require_once

$nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';
$apellido = isset($_SESSION['apellido']) ? $_SESSION['apellido'] : '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ARCO - Usuarios</title>
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
            <a href="productos.html" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.html" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.html" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="Usuario.php" class="menu-item active">
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
            <a href="../login.html" class="menu-cerrar">
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
            <a href="crearUsu.html" class="btn-login">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        </div>

        <div class="users-table">
            <?php
            if (isset($_GET['eliminado']) && $_GET['eliminado'] == '1') {
                echo "<p style='color: green; font-weight: bold;'>Usuario eliminado correctamente.</p>";
            }
            ?>

            <?php include '../servicios/listar_usuarios.php'; ?>

        </div>
    </div>

    <script>
        // Sidebar responsive
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
        };
    </script>
</body>

</html>