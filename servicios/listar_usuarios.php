<?php
require_once 'conexion.php';
$conexion = ConectarDB();

// Verificar si es una petición AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    try {
        $sql = "SELECT id_usuarios, nombre, apellido, rol, correo, estado FROM usuarios WHERE estado = 'activo' ORDER BY nombre, apellido";
        $resultado = $conexion->query($sql);
        
        $usuarios = [];
        if ($resultado && $resultado->num_rows > 0) {
            while ($row = $resultado->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'usuarios' => $usuarios
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
    $conexion->close();
    exit;
}

// Código original para vista HTML
$sql = "SELECT id_usuarios, nombre, apellido, rol, correo, estado FROM usuarios";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0): ?>
    <table class="users-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Rol</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($fila['apellido']); ?></td>
                    <td>
                        <?php
                        $rol = strtolower($fila['rol']);
                        echo $rol === 'admin' || $rol === 'administrador' ? 'Administrador' : ($rol === 'usuario' ? 'Usuario' : 'No definido');
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                    <td><?php echo strtoupper(htmlspecialchars($fila['estado'])); ?></td>
                    <td>
                        <div class="actions">
                            <a href="#" class="action-icon btn-editar" data-id="<?php echo $fila['id_usuarios']; ?>" title="Editar usuario">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="../servicios/eliminar_usuario.php?id=<?php echo $fila['id_usuarios']; ?>" class="action-icon btn-eliminar" title="Eliminar usuario" onclick="return confirm('¿Está seguro de eliminar este usuario?');">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay usuarios registrados.</p>
<?php endif;

$conexion->close();
?>