<?php
/**
 * EJEMPLO DE USO DEL SISTEMA DE PERMISOS
 * Este archivo muestra cómo implementar el sistema de permisos en las vistas
 */

session_start();

// Incluir el sistema de permisos
require_once '../servicios/verificar_permisos.php';

// =====================================================
// EJEMPLO 1: Verificar acceso al módulo
// =====================================================

// Verificar que el usuario puede acceder al módulo de productos
if (!isset($_SESSION['rol']) || !puedeAccederModulo($_SESSION['rol'], 'productos')) {
    header("Location: ../vistas/dashboard.php?error=No tiene acceso a este módulo");
    exit;
}

// =====================================================
// EJEMPLO 2: Verificar permiso específico
// =====================================================

$rol = $_SESSION['rol'];

// Verificar si puede crear productos
$puede_crear = tienePermiso($rol, 'productos', 'crear');
$puede_editar = tienePermiso($rol, 'productos', 'editar');
$puede_eliminar = tienePermiso($rol, 'productos', 'eliminar');
$puede_exportar = tienePermiso($rol, 'productos', 'exportar');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejemplo - Sistema de Permisos</title>
</head>
<body>
    <h1>Gestión de Productos</h1>
    
    <!-- =====================================================
         EJEMPLO 3: Mostrar botones según permisos
         ===================================================== -->
    
    <div class="action-buttons">
        <?php if ($puede_crear): ?>
            <button onclick="crearProducto()">
                <i class="fas fa-plus"></i> Nuevo Producto
            </button>
        <?php endif; ?>
        
        <?php if ($puede_exportar): ?>
            <button onclick="exportarProductos()">
                <i class="fas fa-download"></i> Exportar
            </button>
        <?php endif; ?>
    </div>
    
    <!-- =====================================================
         EJEMPLO 4: Tabla con acciones según permisos
         ===================================================== -->
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Stock</th>
                <?php if ($puede_editar || $puede_eliminar): ?>
                    <th>Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Ejemplo de productos
            $productos = [
                ['id' => 1, 'nombre' => 'Producto A', 'stock' => 100],
                ['id' => 2, 'nombre' => 'Producto B', 'stock' => 50]
            ];
            
            foreach ($productos as $producto):
            ?>
                <tr>
                    <td><?php echo $producto['id']; ?></td>
                    <td><?php echo $producto['nombre']; ?></td>
                    <td><?php echo $producto['stock']; ?></td>
                    <?php if ($puede_editar || $puede_eliminar): ?>
                        <td>
                            <?php if ($puede_editar): ?>
                                <button onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($puede_eliminar): ?>
                                <button onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- =====================================================
         EJEMPLO 5: Verificar permiso antes de acción
         ===================================================== -->
    
    <script>
        function editarProducto(id) {
            <?php if (!$puede_editar): ?>
                alert('No tiene permisos para editar productos');
                return;
            <?php endif; ?>
            
            // Código para editar producto
            console.log('Editando producto:', id);
        }
        
        function eliminarProducto(id) {
            <?php if (!$puede_eliminar): ?>
                alert('No tiene permisos para eliminar productos');
                return;
            <?php endif; ?>
            
            if (confirm('¿Está seguro de eliminar este producto?')) {
                // Código para eliminar producto
                console.log('Eliminando producto:', id);
            }
        }
    </script>
    
    <!-- =====================================================
         EJEMPLO 6: Obtener permisos vía AJAX
         ===================================================== -->
    
    <script>
        // Cargar permisos del usuario actual
        fetch('../servicios/obtener_permisos_usuario.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Rol:', data.rol);
                    console.log('Módulos accesibles:', data.modulos);
                    console.log('Matriz de permisos:', data.matriz_permisos);
                    
                    // Usar permisos para habilitar/deshabilitar funcionalidades
                    actualizarInterfazSegunPermisos(data);
                }
            })
            .catch(error => console.error('Error:', error));
        
        function actualizarInterfazSegunPermisos(permisos) {
            // Ejemplo: Habilitar/deshabilitar botones según permisos
            const moduloProductos = permisos.matriz_permisos.productos;
            
            if (moduloProductos) {
                if (!moduloProductos.crear || !moduloProductos.crear.activo) {
                    document.querySelector('.btn-crear')?.setAttribute('disabled', 'disabled');
                }
                
                if (!moduloProductos.eliminar || !moduloProductos.eliminar.activo) {
                    document.querySelectorAll('.btn-eliminar').forEach(btn => {
                        btn.setAttribute('disabled', 'disabled');
                    });
                }
            }
        }
    </script>
    
    <!-- =====================================================
         EJEMPLO 7: Middleware en PHP (uso en servicios)
         ===================================================== -->
    
    <?php
    /*
    // En un servicio PHP (ej: servicios/crear_producto.php)
    
    require_once 'verificar_permisos.php';
    
    // Verificar que tiene permiso para crear productos
    requierePermiso('productos', 'crear');
    
    // Si llega aquí, tiene permiso
    // Continuar con la lógica de crear producto
    */
    ?>
    
    <!-- =====================================================
         EJEMPLO 8: Mostrar módulos en menú según permisos
         ===================================================== -->
    
    <nav class="sidebar">
        <?php
        $modulos_accesibles = obtenerModulosAccesibles($rol);
        
        foreach ($modulos_accesibles as $modulo):
        ?>
            <a href="<?php echo $modulo['ruta']; ?>" class="menu-item">
                <i class="fas <?php echo $modulo['icono']; ?>"></i>
                <span><?php echo $modulo['descripcion']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    
    <!-- =====================================================
         EJEMPLO 9: Verificar múltiples permisos
         ===================================================== -->
    
    <?php
    $permisos_productos = obtenerPermisosModulo($rol, 'productos');
    
    echo "<h3>Permisos en Productos:</h3>";
    echo "<ul>";
    foreach ($permisos_productos as $permiso) {
        echo "<li>Puede: $permiso</li>";
    }
    echo "</ul>";
    ?>
    
    <!-- =====================================================
         EJEMPLO 10: Debugging - Ver todos los permisos
         ===================================================== -->
    
    <?php if ($_SESSION['rol'] === 'administrador'): ?>
        <div class="debug-panel">
            <h3>Panel de Debug - Permisos</h3>
            <pre><?php print_r(obtenerMatrizPermisos($rol)); ?></pre>
        </div>
    <?php endif; ?>
    
</body>
</html>
