<?php 

session_start();

require_once 'conexion.php';  // ✅ Esta línea es la correcta

// Para depuración (puedes quitarlo luego)
//echo "SESSION ID: " . session_id();
//print_r($_SESSION);

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html?error=Debe iniciar sesión");
    exit;
}   

$id_usuario = intval($_GET['id']);
$conexion = ConectarDB();

$sql = "SELECT * FROM usuarios WHERE id_usuarios = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../componentes/crearUsu.css">
</head>
<body>
    <div class="cont-auten">
        <div class="form-auten">
            <h2>Editar Usuario</h2>
            <form action="../servicios/actualizar_usuario.php" method="post">
                <input type="hidden" name="id_usuarios" value="<?php echo $usuario['id_usuarios']; ?>">

                <label>Nombre:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

                <label>Apellido:</label>
                <input type="text" name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required>

                <label>Correo:</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>

                <label for="num_doc">Número de Documento</label>
                <input type="text" class="input-form" id="num_doc" name="num_doc" value="<?php echo htmlspecialchars($usuario['num_doc']); ?>" required>

                <label>Rol:</label>
                <select name="rol" required>
                <option value="administrador" <?php if ($usuario['rol'] == 'administrador') echo 'selected'; ?>>Administrador</option>
                <option value="usuario" <?php if ($usuario['rol'] == 'usuario') echo 'selected'; ?>>Usuario</option>
                </select>

                <label>Estado:</label>
                <select name="estado" required>
                    <option value="activo" <?php if ($usuario['estado'] == 'activo') echo 'selected'; ?>>ACTIVO</option>
                    <option value="inactivo" <?php if ($usuario['estado'] == 'inactivo') echo 'selected'; ?>>INACTIVO</option>
                </select>


                <br><br>
                <button type="submit">Actualizar</button>
                <a href="../vistas/usuario.php" class="btn-cancelar">Cancelar</a>

            </form>
        </div>
    </div>
</body>
</html>
