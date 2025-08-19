<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Usuario no autenticado, redirigir al login
    header("Location: ../login.html");
    exit;
}
$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Inicio</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Añadir FontAwesome para iconos -->
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
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active">
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
            <a href="usuario.php" class="menu-item">
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
            <h2>Panel de control</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>
        
        <div class="dashboard-cards">
            <div class="card" onclick="navigateTo('productos.php')">
                <div class="card-header">
                    <h3 class="card-title">Total Productos</h3>
                    <div class="card-icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="card-value" id="totalProducts">Cargando...</div>
                <div class="card-footer" id="footerProducts">Cargando datos...</div>
            </div>
            
            <div class="card" onclick="navigateTo('categorias.php')">
                <div class="card-header">
                    <h3 class="card-title">Categorías</h3>
                    <div class="card-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div class="card-value" id="totalCategories">Cargando...</div>
                <div class="card-footer" id="footerCategories">Cargando datos...</div>
            </div>
            
            <div class="card" onclick="navigateTo('movimientos.php')">
                <div class="card-header">
                    <h3 class="card-title">Movimientos Hoy</h3>
                    <div class="card-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="card-value" id="todayMovements">Cargando...</div>
                <div class="card-footer" id="footerMovements">Cargando datos...</div>
            </div>
            
            <div class="card" onclick="showAlerts()">
                <div class="card-header">
                    <h3 class="card-title">Alertas</h3>
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="card-value" id="totalAlerts">Cargando...</div>
                <div class="card-footer" id="footerAlerts">Cargando datos...</div>
            </div>
        </div>
        
        <div class="recent-activity">
            <div class="activity-header">
                <h3>Actividad Reciente</h3>
                <a href="#" class="btn-login" onclick="refreshActivity()">Actualizar</a>
            </div>
            
            <ul class="activity-list" id="activityList">
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">Cargando actividad reciente...</div>
                        <div class="activity-time">Por favor espere</div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    
    <script>
        // JavaScript para funcionalidad interactiva
        document.addEventListener('DOMContentLoaded', function() {
            initializeDashboard();
            setupEventListeners();
            loadDashboardData();
        });
        
        function initializeDashboard() {
            // Animación de entrada para las tarjetas
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Configurar scroll suave
            document.documentElement.style.scrollBehavior = 'smooth';
        }
        
        function setupEventListeners() {
            // Toggle sidebar en móvil
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
            
            // Cerrar sidebar al hacer click fuera en móvil
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768 && 
                    !sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('collapsed');
                }
            });
            
            // Actualizar datos cada 30 segundos
            setInterval(updateDashboardData, 30000);
        }
        
        async function loadDashboardData() {
            try {
                showLoading();
                
                const response = await fetch('../servicios/obtener_dashboard.php');
                const data = await response.json();
                
                if (data.success) {
                    updateDashboardCards(data.data);
                    updateActivityList(data.data.actividad_reciente);
                } else {
                    console.error('Error al cargar datos:', data.error);
                    showErrorMessage('Error al cargar los datos del dashboard');
                }
                
                hideLoading();
            } catch (error) {
                console.error('Error de conexión:', error);
                hideLoading();
                showErrorMessage('Error de conexión con el servidor');
            }
        }
        
        function showLoading() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.classList.add('loading', 'pulse');
            });
        }
        
        function hideLoading() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.classList.remove('loading', 'pulse');
            });
        }
        
        function updateDashboardCards(data) {
            // Actualizar tarjetas con datos reales
            animateCounter('totalProducts', data.total_productos);
            animateCounter('totalCategories', data.total_categorias);
            animateCounter('todayMovements', data.movimientos_hoy);
            animateCounter('totalAlerts', data.total_alertas);
            
            // Actualizar footers con información adicional
            document.getElementById('footerProducts').textContent = `+${data.porcentaje_productos}% desde el mes pasado`;
            document.getElementById('footerCategories').textContent = `+${data.nuevas_categorias} nuevas categorías`;
            document.getElementById('footerMovements').textContent = `${data.entradas_hoy} entradas, ${data.salidas_hoy} salidas`;
            
            const alertText = data.total_alertas > 0 ? 
                `Stock bajo en ${data.total_alertas} producto${data.total_alertas > 1 ? 's' : ''}` :
                'No hay alertas de stock';
            document.getElementById('footerAlerts').textContent = alertText;
        }
        
        function updateActivityList(actividades) {
            const activityList = document.getElementById('activityList');
            activityList.innerHTML = '';
            
            if (actividades.length === 0) {
                activityList.innerHTML = `
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">No hay actividad reciente</div>
                            <div class="activity-time">No se han registrado movimientos</div>
                        </div>
                    </li>
                `;
                return;
            }
            
            actividades.forEach((actividad, index) => {
                const iconClass = getActivityIcon(actividad.tipo);
                const actionText = getActivityText(actividad.tipo, actividad.cantidad, actividad.producto);
                
                const activityItem = document.createElement('li');
                activityItem.className = 'activity-item';
                activityItem.onclick = () => showActivityDetails(index + 1);
                
                activityItem.innerHTML = `
                    <div class="activity-icon">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">${actionText}</div>
                        <div class="activity-time">${actividad.tiempo} - por ${actividad.usuario}</div>
                    </div>
                `;
                
                activityList.appendChild(activityItem);
            });
        }
        
        function getActivityIcon(tipo) {
            switch(tipo) {
                case 'entrada': return 'fa-plus';
                case 'salida': return 'fa-minus';
                case 'ajuste': return 'fa-edit';
                case 'transferencia': return 'fa-exchange-alt';
                default: return 'fa-circle';
            }
        }
        
        function getActivityText(tipo, cantidad, producto) {
    switch(tipo) {
        case 'entrada':
            return `Se agregaron ${cantidad} unidades de ${producto}`;
        case 'salida':
            return `Se retiraron ${cantidad} unidades de ${producto}`;
        case 'ajuste':
            return `Se ajustó el stock de ${producto} (${cantidad} unidades)`;
        case 'transferencia':
            return `Se transfirieron ${cantidad} unidades de ${producto}`;

        // Aquí vienen los del historial_acciones:
        case 'crear':
            return `Se creó: ${producto}`;
        case 'editar':
            return `Se editó: ${producto}`;
        case 'eliminar_producto':
            return `Se eliminó: ${producto}`;
        case 'agregar_producto':
            return `Se agregó el producto: ${producto}`;
        case 'agregar_categoria':
            return `Se agregó la categoría: ${producto}`;
        case 'editar_categoria':
            return `Se editó la categoría: ${producto}`;
        case 'eliminar_categoria':
            return `Se eliminó la categoría: ${producto}`;
        default:
            return producto; // Por si acaso viene una descripción genérica
    }
}
    
        
        function showErrorMessage(message) {
            // Mostrar mensaje de error en las tarjetas
            document.getElementById('totalProducts').textContent = 'Error';
            document.getElementById('totalCategories').textContent = 'Error';
            document.getElementById('todayMovements').textContent = 'Error';
            document.getElementById('totalAlerts').textContent = 'Error';
            
            document.getElementById('footerProducts').textContent = message;
            document.getElementById('footerCategories').textContent = message;
            document.getElementById('footerMovements').textContent = message;
            document.getElementById('footerAlerts').textContent = message;
        }
        
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const duration = 2000;
            const startTime = performance.now();
            
            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const currentValue = Math.floor(targetValue * progress);
                
                element.textContent = currentValue.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            }
            
            requestAnimationFrame(updateCounter);
        }
        
        function navigateTo(page) {
            // Efecto de transición antes de navegar
            document.body.style.opacity = '0.8';
            setTimeout(() => {
                // Corregir las rutas para que apunten a los archivos PHP correctos
                let correctPage;
                switch(page) {
                    case 'productos.html':
                        correctPage = 'productos.php';
                        break;
                    case 'categorias.html':
                        correctPage = 'categorias.php';
                        break;
                    case 'movimientos.html':
                        correctPage = 'movimientos.php';
                        break;
                    default:
                        correctPage = page;
                }
                window.location.href = correctPage;
            }, 200);
        }
        
        function showAlerts() {
            // Redirigir a una página de alertas o mostrar modal
            window.location.href = 'productos.php?filter=low_stock';
        }
        
        function showActivityDetails(activityId) {
            // Mostrar modal con detalles de la actividad
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                    <h3>Detalles de Actividad #${activityId}</h3>
                    <p>Cargando detalles...</p>
                </div>
            `;
            document.body.appendChild(modal);
            
            // Aquí puedes hacer una llamada AJAX para obtener más detalles
        }
        
        function showUserMenu() {
            // Implementar menú de usuario real
            const userMenu = document.createElement('div');
            userMenu.className = 'user-dropdown';
            userMenu.innerHTML = `
                <a href="configuracion.php">Configuración</a>
                <a href="../servicios/logout.php">Cerrar Sesión</a>
            `;
            document.querySelector('.user-info').appendChild(userMenu);
        }
        
        async function refreshActivity() {
            const activityList = document.getElementById('activityList');
            activityList.style.opacity = '0.5';
            
            try {
                const response = await fetch('../servicios/obtener_dashboard.php');
                const data = await response.json();
                
                if (data.success) {
                    updateActivityList(data.data.actividad_reciente);
                } else {
                    console.error('Error al actualizar actividad:', data.error);
                }
            } catch (error) {
                console.error('Error de conexión al actualizar actividad:', error);
            }
            
            activityList.style.opacity = '1';
        }
        
        async function updateDashboardData() {
            // Actualizar datos en tiempo real
            console.log('Actualizando datos del dashboard...');
            try {
                const response = await fetch('../servicios/obtener_dashboard.php');
                const data = await response.json();
                
                if (data.success) {
                    updateDashboardCards(data.data);
                    updateActivityList(data.data.actividad_reciente);
                } else {
                    console.error('Error al actualizar datos:', data.error);
                }
            } catch (error) {
                console.error('Error de conexión al actualizar:', error);
            }
        }
        
        // Funciones de utilidad
        function smoothScrollTo(element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Manejo de errores
        window.addEventListener('error', function(e) {
            console.error('Error en el dashboard:', e.error);
        });
    </script>
</body>
</html>