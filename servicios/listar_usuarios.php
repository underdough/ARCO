<?php
require_once 'conexion.php';

$conexion = ConectarDB();
$sql = "SELECT id_usuarios, nombre, apellido, rol, correo, estado FROM usuarios";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
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
                            if ($rol === 'admin' || $rol === 'administrador') {
                                echo 'Administrador';
                            } elseif ($rol === 'usuario') {
                                echo 'Usuario';
                            } else {
                                echo 'No definido';
                            }
                        ?>
                    </td>

                    <td><?php echo htmlspecialchars($fila['correo']); ?></td>
                    <td><?php echo strtoupper(htmlspecialchars($fila['estado'])); ?></td>
                    <td>
                        <a href="../servicios/editar_usuario.php?id=<?php echo $fila['id_usuarios']; ?>" class="btn-editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        |
                        <a href="../servicios/eliminar_usuario.php?id=<?php echo $fila['id_usuarios']; ?>" class="btn-eliminar" onclick="return confirm('¿Está seguro de eliminar este usuario?');">
                            <i class="fas fa-trash"></i> Eliminar
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
