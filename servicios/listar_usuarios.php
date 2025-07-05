<?php
require_once 'conexion.php';
$conexion = ConectarDB();

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
                        echo $rol === 'admin' || $rol === 'administrador' ? 'Administrador' :
                            ($rol === 'usuario' ? 'Usuario' : 'No definido');
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                    <td><?php echo strtoupper(htmlspecialchars($fila['estado'])); ?></td>
                    <td>
                        <a href="#" class="btn-editar" data-id="<?php echo $fila['id_usuarios']; ?>" title="Editar usuario">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="../servicios/eliminar_usuario.php?id=<?php echo $fila['id_usuarios']; ?>" class="btn-eliminar" title="Eliminar usuario" onclick="return confirm('¿Está seguro de eliminar este usuario?');">
                            <i class="fas fa-trash"></i>
                        </a>
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
