<?php
/**
 * Generador de Menú Dinámico según Permisos
 * Genera el HTML del menú lateral basado en los permisos del usuario
 */

require_once __DIR__ . '/conexion.php';

/**
 * Obtiene los módulos del menú según el rol del usuario
 * @param string $rol Rol del usuario
 * @return array Módulos accesibles
 */
function obtenerMenuUsuario($rol) {
    $conn = ConectarDB();
    
    // Mapeo de módulos a rutas reales del sistema
    $rutasReales = [
        'dashboard' => 'dashboard.php',
        'productos' => 'productos.php',
        'categorias' => 'categorias.php',
        'movimientos' => 'movimientos.php',
        'usuarios' => 'gestion_usuarios.php',
        'reportes' => 'reportes.php',
        'estadisticas' => 'estadisticas.php',
        'configuracion' => 'configuracion.php',
        'ordenes_compra' => 'ordenes_compra.php',
        'devoluciones' => 'devoluciones.php',
        'permisos' => 'gestion_permisos.php',
        'anomalias' => 'anomalias.php',
        'anomalias_reportes' => 'anomalias_reportes.php'
    ];
    
    // Descripciones estándar para el menú (como en la imagen de referencia)
    $descripcionesEstandar = [
        'dashboard' => 'Inicio',
        'productos' => 'Gestión de Productos',
        'categorias' => 'Gestión de Categorías',
        'movimientos' => 'Movimientos de Inventario',
        'usuarios' => 'Gestión de Usuarios',
        'reportes' => 'Reportes y Estadísticas',
        'estadisticas' => 'Estadísticas',
        'configuracion' => 'Configuración del Sistema',
        'ordenes_compra' => 'Órdenes de Compra',
        'devoluciones' => 'Gestión de Devoluciones',
        'permisos' => 'Permisos',
        'anomalias' => 'Anomalías',
        'anomalias_reportes' => 'Reportes de Anomalías'
    ];
    
    // Iconos estándar para el menú
    $iconosEstandar = [
        'dashboard' => 'fa-home',
        'productos' => 'fa-box',
        'categorias' => 'fa-tags',
        'movimientos' => 'fa-exchange-alt',
        'usuarios' => 'fa-users',
        'reportes' => 'fa-chart-bar',
        'estadisticas' => 'fa-chart-line',
        'configuracion' => 'fa-cog',
        'ordenes_compra' => 'fa-shopping-cart',
        'devoluciones' => 'fa-undo',
        'permisos' => 'fa-user-shield',
        'anomalias' => 'fa-exclamation-circle',
        'anomalias_reportes' => 'fa-chart-pie'
    ];
    
    // Módulos que existen actualmente en el sistema
    $modulosExistentes = [
        'dashboard', 'productos', 'categorias', 'movimientos', 
        'usuarios', 'reportes', 'estadisticas', 'configuracion',
        'ordenes_compra', 'devoluciones', 'anomalias', 'anomalias_reportes'
    ];
    
    $sql = "SELECT DISTINCT 
                m.nombre,
                m.descripcion,
                m.icono,
                m.ruta,
                m.orden
            FROM rol_permisos rp
            JOIN modulos m ON rp.id_modulo = m.id_modulo
            JOIN permisos p ON rp.id_permiso = p.id_permiso
            WHERE rp.rol = ?
              AND rp.activo = 1
              AND m.activo = 1
              AND p.codigo = 'ver'
            ORDER BY m.orden";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $modulos = [];
    while ($fila = $resultado->fetch_assoc()) {
        // Solo incluir módulos que existen en el sistema
        if (in_array($fila['nombre'], $modulosExistentes)) {
            $fila['ruta'] = $rutasReales[$fila['nombre']] ?? $fila['ruta'];
            // Usar descripciones e iconos estándar para consistencia
            $fila['descripcion'] = $descripcionesEstandar[$fila['nombre']] ?? $fila['descripcion'];
            $fila['icono'] = $iconosEstandar[$fila['nombre']] ?? $fila['icono'];
            $modulos[] = $fila;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $modulos;
}

/**
 * Genera el HTML del menú lateral
 * @param string $paginaActual Nombre del módulo actual (para marcar como activo)
 * @return string HTML del menú
 */
function generarMenuHTML($paginaActual = '') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $rol = $_SESSION['rol'] ?? 'usuario';
    $modulos = obtenerMenuUsuario($rol);
    
    // Estilos inline para el sidebar mejorado
    $html = '<style>
        /* Importar fuente Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");
        
        /* Estilos base del sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            overflow: hidden;
            z-index: 1000;
            transition: transform 0.3s ease;
            font-family: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .sidebar *:not(i):not(.fas):not(.fa):not(.far):not(.fab) {
            font-family: inherit;
        }
        .sidebar i,
        .sidebar .fas,
        .sidebar .fa,
        .sidebar .far,
        .sidebar .fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free";
        }
        .sidebar-header {
            flex-shrink: 0;
        }
        .sidebar-menu {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        .sidebar-menu .menu-items-container {
            flex: 1;
            overflow-y: auto;
            padding: 5px 0;
            /* Scroll invisible pero funcional */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE y Edge */
        }
        .sidebar-menu .menu-items-container::-webkit-scrollbar {
            width: 0;
            height: 0;
            display: none;
        }
        .sidebar-menu .menu-item {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            margin: 1px 6px;
            border-radius: 5px;
            font-size: 12px;
            text-decoration: none;
            color: inherit;
            transition: background 0.2s;
        }
        .sidebar-menu .menu-item i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }
        .sidebar-menu .menu-separator {
            height: 1px;
            background: rgba(255,255,255,0.15);
            margin: 6px 6px;
        }
        .sidebar-menu .menu-footer {
            flex-shrink: 0;
            padding: 10px 6px 10px 6px;
            margin: 0;
            border-top: 1px solid rgba(255,255,255,0.15);
            background: inherit;
            box-sizing: border-box;
        }
        .sidebar-menu .menu-cerrar,
        .sidebar .menu-cerrar {
            display: flex;
            align-items: center;
            padding: 8px 9px;
            margin: 0;
            border-radius: 5px;
            font-size: 12px;
            text-decoration: none;
            color: inherit;
            background: rgba(255,255,255,0.05);
            box-sizing: border-box;
            width: 100%;
        }
        .sidebar-menu .menu-cerrar:hover,
        .sidebar .menu-cerrar:hover {
            background: rgba(220,53,69,0.3);
        }
        .sidebar-menu .menu-cerrar i,
        .sidebar .menu-cerrar i {
            width: 20px;
            min-width: 20px;
            margin-right: 10px;
            text-align: center;
            flex-shrink: 0;
        }
        
        /* Botón flotante para móviles */
        .menu-toggle-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            width: 44px;
            height: 44px;
            border-radius: 8px;
            background: #395886;
            color: white;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 1001;
            font-size: 20px;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .menu-toggle-btn:hover {
            background: #34495e;
            transform: scale(1.05);
        }
        .menu-toggle-btn.active {
            background: #e74c3c;
        }
        .menu-toggle-btn.active i:before {
            content: "\\f00d";
        }
        
        /* Overlay para cerrar menú */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            opacity: 1;
        }
        
        /* Media query para móviles */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
                transform: translateX(-100%);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
                width: 250px;
            }
            .sidebar.mobile-open .sidebar-header h1,
            .sidebar.mobile-open .menu-text {
                opacity: 1;
                pointer-events: auto;
            }
            /* Ocultar botón toggle original del dashboard */
            .sidebar-toggle {
                display: none;
            }
            .menu-toggle-btn {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .sidebar-overlay {
                display: block;
                pointer-events: none;
            }
            .sidebar-overlay.active {
                pointer-events: auto;
            }
        }
    </style>';
    
    $html .= '<div class="sidebar-menu">';
    $html .= '<div class="menu-items-container">';
    
    // Agrupar módulos por categoría
    $gruposPrincipales = ['dashboard', 'productos', 'categorias', 'movimientos'];
    $gruposGestion = ['usuarios', 'ordenes_compra', 'devoluciones'];
    $gruposReportes = ['reportes', 'estadisticas', 'anomalias', 'anomalias_reportes'];
    $gruposConfig = ['configuracion'];
    
    $modulosRenderizados = [];
    
    // Renderizar grupo principal
    foreach ($modulos as $modulo) {
        if (in_array($modulo['nombre'], $gruposPrincipales)) {
            $activo = ($modulo['nombre'] === $paginaActual) ? ' active' : '';
            $icono = $modulo['icono'] ?: 'fa-circle';
            $html .= sprintf(
                '<a href="%s" class="menu-item%s">
                    <i class="fas %s"></i>
                    <span class="menu-text">%s</span>
                </a>',
                htmlspecialchars($modulo['ruta']),
                $activo,
                htmlspecialchars($icono),
                htmlspecialchars($modulo['descripcion'])
            );
            $modulosRenderizados[] = $modulo['nombre'];
        }
    }
    
    // Separador
    $html .= '<div class="menu-separator"></div>';
    
    // Renderizar grupo gestión
    foreach ($modulos as $modulo) {
        if (in_array($modulo['nombre'], $gruposGestion)) {
            $activo = ($modulo['nombre'] === $paginaActual) ? ' active' : '';
            $icono = $modulo['icono'] ?: 'fa-circle';
            $html .= sprintf(
                '<a href="%s" class="menu-item%s">
                    <i class="fas %s"></i>
                    <span class="menu-text">%s</span>
                </a>',
                htmlspecialchars($modulo['ruta']),
                $activo,
                htmlspecialchars($icono),
                htmlspecialchars($modulo['descripcion'])
            );
            $modulosRenderizados[] = $modulo['nombre'];
        }
    }
    
    // Separador
    $html .= '<div class="menu-separator"></div>';
    
    // Renderizar grupo reportes
    foreach ($modulos as $modulo) {
        if (in_array($modulo['nombre'], $gruposReportes)) {
            $activo = ($modulo['nombre'] === $paginaActual) ? ' active' : '';
            $icono = $modulo['icono'] ?: 'fa-circle';
            $html .= sprintf(
                '<a href="%s" class="menu-item%s">
                    <i class="fas %s"></i>
                    <span class="menu-text">%s</span>
                </a>',
                htmlspecialchars($modulo['ruta']),
                $activo,
                htmlspecialchars($icono),
                htmlspecialchars($modulo['descripcion'])
            );
            $modulosRenderizados[] = $modulo['nombre'];
        }
    }
    
    // Separador
    $html .= '<div class="menu-separator"></div>';
    
    // Renderizar grupo configuración
    foreach ($modulos as $modulo) {
        if (in_array($modulo['nombre'], $gruposConfig)) {
            $activo = ($modulo['nombre'] === $paginaActual) ? ' active' : '';
            $icono = $modulo['icono'] ?: 'fa-circle';
            $html .= sprintf(
                '<a href="%s" class="menu-item%s">
                    <i class="fas %s"></i>
                    <span class="menu-text">%s</span>
                </a>',
                htmlspecialchars($modulo['ruta']),
                $activo,
                htmlspecialchars($icono),
                htmlspecialchars($modulo['descripcion'])
            );
            $modulosRenderizados[] = $modulo['nombre'];
        }
    }
    
    // Agregar enlace de Permisos solo para administrador
    if ($rol === 'administrador') {
        $activoPermisos = ($paginaActual === 'permisos') ? ' active' : '';
        $html .= sprintf(
            '<a href="gestion_permisos.php" class="menu-item%s">
                <i class="fas fa-user-shield"></i>
                <span class="menu-text">Permisos</span>
            </a>',
            $activoPermisos
        );
    }
    
    $html .= '</div>'; // Cierre menu-items-container
    
    // Footer con enlace de cerrar sesión (siempre al fondo)
    $html .= '<div class="menu-footer">';
    $html .= '<a href="../servicios/logout.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>';
    $html .= '</div>';
    
    $html .= '</div>';
    
    // JavaScript para crear botón flotante y overlay fuera del sidebar
    $html .= '<script>
        (function() {
            // Crear elementos solo si no existen
            if (!document.getElementById("menuToggleBtn")) {
                // Crear overlay
                var overlay = document.createElement("div");
                overlay.className = "sidebar-overlay";
                overlay.id = "sidebarOverlay";
                document.body.appendChild(overlay);
                
                // Crear botón flotante
                var btn = document.createElement("button");
                btn.className = "menu-toggle-btn";
                btn.id = "menuToggleBtn";
                btn.setAttribute("aria-label", "Abrir menú");
                btn.innerHTML = \'<i class="fas fa-bars"></i>\';
                document.body.appendChild(btn);
            }
            
            document.addEventListener("DOMContentLoaded", function() {
                var toggleBtn = document.getElementById("menuToggleBtn");
                var sidebar = document.querySelector(".sidebar");
                var overlay = document.getElementById("sidebarOverlay");
                
                if (toggleBtn && sidebar && overlay) {
                    toggleBtn.addEventListener("click", function() {
                        sidebar.classList.toggle("mobile-open");
                        overlay.classList.toggle("active");
                        toggleBtn.classList.toggle("active");
                    });
                    
                    overlay.addEventListener("click", function() {
                        sidebar.classList.remove("mobile-open");
                        overlay.classList.remove("active");
                        toggleBtn.classList.remove("active");
                    });
                    
                    // Cerrar menú al hacer clic en un enlace (móvil)
                    var menuItems = sidebar.querySelectorAll(".menu-item, .menu-cerrar");
                    menuItems.forEach(function(item) {
                        item.addEventListener("click", function() {
                            if (window.innerWidth <= 768) {
                                sidebar.classList.remove("mobile-open");
                                overlay.classList.remove("active");
                                toggleBtn.classList.remove("active");
                            }
                        });
                    });
                }
            });
        })();
    </script>';
    
    return $html;
}

/**
 * Genera el sidebar completo con header y menú
 * @param string $paginaActual Nombre del módulo actual
 * @return string HTML del sidebar completo
 */
function generarSidebarCompleto($paginaActual = '') {
    $html = '<div class="sidebar">
        <div class="sidebar-header">
            <h1>ARCO</h1>
            <p class="subtlo">Gestión de Inventario</p>
        </div>';
    
    $html .= generarMenuHTML($paginaActual);
    $html .= '</div>';
    
    return $html;
}

/**
 * Genera script JS con los permisos del usuario para el módulo actual
 * @param string $modulo Nombre del módulo
 * @return string Script JS
 */
function generarScriptPermisos($modulo) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once __DIR__ . '/verificar_permisos.php';
    
    $rol = $_SESSION['rol'] ?? 'usuario';
    $permisos = obtenerPermisosModulo($rol, $modulo);
    
    $permisosObj = [];
    foreach ($permisos as $p) {
        $permisosObj[$p] = true;
    }
    
    $json = json_encode($permisosObj);
    
    return "<script>
        window.PERMISOS_USUARIO = $json;
        window.ROL_USUARIO = '" . htmlspecialchars($rol) . "';
        
        // Funciones helper para verificar permisos en JS
        function tienePermiso(permiso) {
            return window.PERMISOS_USUARIO[permiso] === true;
        }
        
        function ocultarSinPermiso(selector, permiso) {
            if (!tienePermiso(permiso)) {
                document.querySelectorAll(selector).forEach(el => el.style.display = 'none');
            }
        }
        
        function deshabilitarSinPermiso(selector, permiso) {
            if (!tienePermiso(permiso)) {
                document.querySelectorAll(selector).forEach(el => {
                    el.disabled = true;
                    el.classList.add('disabled');
                });
            }
        }
    </script>";
}
