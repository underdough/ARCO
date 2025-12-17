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
        'configuracion' => 'configuracion.php',
        'ordenes_compra' => 'ordenes_compra.php',
        'devoluciones' => 'devoluciones.php',
        'permisos' => 'gestion_permisos.php',
        'anomalias' => 'anomalias.php',
        'anomalias_reportes' => 'anomalias_reportes.php'
    ];
    
    // Descripciones estándar para el menú (como en la imagen de referencia)
    $descripcionesEstandar = [
        'dashboard' => 'Panel de Control',
        'productos' => 'Gestión de Productos',
        'categorias' => 'Gestión de Categorías',
        'movimientos' => 'Movimientos de Inventario',
        'usuarios' => 'Gestión de Usuarios',
        'reportes' => 'Reportes y Estadísticas',
        'configuracion' => 'Configuración del Sistema',
        'ordenes_compra' => 'Órdenes de Compra',
        'devoluciones' => 'Gestión de Devoluciones',
        'permisos' => 'Permisos',
        'anomalias' => 'Anomalías',
        'anomalias_reportes' => 'Reportes de Anomalías'
    ];
    
    // Iconos estándar para el menú
    $iconosEstandar = [
        'dashboard' => 'fa-tachometer-alt',
        'productos' => 'fa-box',
        'categorias' => 'fa-tags',
        'movimientos' => 'fa-exchange-alt',
        'usuarios' => 'fa-users',
        'reportes' => 'fa-chart-bar',
        'configuracion' => 'fa-cog',
        'ordenes_compra' => 'fa-shopping-cart',
        'devoluciones' => 'fa-undo',
        'permisos' => 'fa-user-shield',
        'anomalias' => 'fa-exclamation-circle',
        'anomalias_reportes' => 'fa-chart-line'
    ];
    
    // Módulos que existen actualmente en el sistema
    $modulosExistentes = [
        'dashboard', 'productos', 'categorias', 'movimientos', 
        'usuarios', 'reportes', 'configuracion',
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
    
    $html = '<div class="sidebar-menu">';
    
    foreach ($modulos as $modulo) {
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
    
    // Enlace de cerrar sesión
    $html .= '<a href="../servicios/logout.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>';
    
    $html .= '</div>';
    
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
