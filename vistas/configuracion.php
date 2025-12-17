<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}
require_once '../servicios/conexion.php'; 


$conexion = conectarDB();

$empresa = [
    'nombre' => '',
    'nif' => '',
    'direccion' => '',
    'ciudad' => '',
    'telefono' => '',
    'email' => ''
];

$sql = "SELECT * FROM empresa WHERE id = 2";
$result = $conexion->query($sql);

if ($result && $result->num_rows > 0) {
    $empresa = $result->fetch_assoc();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Configuración</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/configuracion.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
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
            <a href="gestion_usuarios.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
            <a href="gestion_permisos.php" class="menu-item">
                <i class="fas fa-user-shield"></i>
                <span class="menu-text">Permisos</span>
            </a>
            <?php endif; ?>
            <a href="configuracion.php" class="menu-item active">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="../servicios/logout.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Configuración del Sistema</h2>
        </div>


       <form action="../servicios/guardar_empresa.php" method="POST" enctype="multipart/form-data"
            id="companyInfoForm">
            <div class="config-section">
                <h3><i class="fas fa-building"></i> Información de la Empresa</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="companyName">Nombre de la Empresa</label>
                        <input type="text" class="form-control" id="companyName" name="companyName" 
                            value="<?= htmlspecialchars($empresa['nombre']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="companyTaxId">NIF/CIF</label>
                        <input type="text" class="form-control" id="companyTaxId" name="companyTaxId"
                            value="<?= htmlspecialchars($empresa['nif']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="companyAddress">Dirección</label>
                        <input type="text" class="form-control" id="companyAddress" name="companyAddress"
                            value="<?= htmlspecialchars($empresa['direccion']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="companyCity">Ciudad</label>
                        <input type="text" class="form-control" id="companyCity" name="companyCity"
                            value="<?= htmlspecialchars($empresa['ciudad']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="companyPhone">Teléfono</label>
                        <input type="text" class="form-control" id="companyPhone" name="companyPhone"
                            value="<?= htmlspecialchars($empresa['telefono']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="companyEmail">Email</label>
                        <input type="email" class="form-control" id="companyEmail" name="companyEmail"
                            value="<?= htmlspecialchars($empresa['email']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="companyLogo">Logo de la Empresa</label>
                    <input type="file" class="form-control" id="companyLogo" name="companyLogo">
                    <small>Tamaño recomendado: 200x200px. Formatos: JPG, PNG</small>
                </div>

                <div class="section-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Información
                    </button>
                </div>
            </div>
        </form>

        <?php
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        $preferencias = [
            'notify_low_stock' => 0,
            'low_stock_threshold' => 15,
            'notify_movements' => 0,
            'notify_email' => 0,
            'notification_emails' => ''
        ];

        $sql = "SELECT * FROM notificaciones WHERE usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $preferencias = $row;
        }
        ?>


        <!-- Sección de Preferencias de Notificaciones -->
        <form action="../servicios/guardar_notificaciones.php" method="POST" id="formNotificaciones">
            <div class="config-section" id="notificacionesSection">
                <h3><i class="fas fa-bell"></i> Preferencias de Notificaciones</h3>

                <div class="form-group">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label>Notificaciones de Stock Bajo</label>
                        <label class="switch">
                            <input type="checkbox" name="notifyLowStock" <?= $preferencias['notify_low_stock'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group" id="lowStockThresholdGroup">
                        <label for="lowStockThreshold">Umbral de Stock Bajo (%)</label>
                        <input type="number" name="lowStockThreshold"
                            value="<?= htmlspecialchars($preferencias['low_stock_threshold']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label>Notificaciones de Movimientos</label>
                        <label class="switch">
                            <input type="checkbox" name="notifyMovements" <?= $preferencias['notify_movements'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label>Notificaciones por Email</label>
                        <label class="switch">
                            <input type="checkbox" name="notifyEmail" id="notifyEmail" <?= $preferencias['notify_email'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="form-group" id="emailNotificationsGroup" <?= $preferencias['notify_email'] ? '' : 'style="display: none;"' ?>>
                        <label for="notificationEmails">Emails para Notificaciones (separados por coma)</label>
                        <input type="text" class="form-control" id="notificationEmails" name="notificationEmails"
                            value="<?= htmlspecialchars($preferencias['notification_emails']) ?>"
                            placeholder="admin@ejemplo.com, gerente@ejemplo.com">
                    </div>
                </div>

                <div class="section-actions">
                    <button class="btn btn-primary" type="submit" id="saveNotificationSettings">
                        <i class="fas fa-save"></i> Guardar Preferencias
                    </button>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const checkbox = document.getElementById("notifyEmail");
                const emailGroup = document.getElementById("emailNotificationsGroup");

                checkbox.addEventListener("change", () => {
                    emailGroup.style.display = checkbox.checked ? "block" : "none";
                });
            });
        </script>



        <!-- Sección de Copias de Seguridad -->
        <?php
        $backupPrefs = [
            'auto_backup' => 0,
            'frecuencia' => 'diaria',
            'retencion_dias' => 30,
            'ultima_copia' => null
        ];

        if ($usuarioId) {
            $stmt = $conexion->prepare("SELECT auto_backup, frecuencia, retencion_dias, ultima_copia FROM copias_seguridad WHERE usuario_id = ?");
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $backupPrefs = array_merge($backupPrefs, $row);
            }
        }

        ?>

        <form action="../servicios/guardar_copias.php" method="POST" id="formBackup">
            <div class="config-section">
                <h3><i class="fas fa-database"></i> Copias de Seguridad</h3>

                <div class="form-group">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label>Activar Copias Automáticas</label>
                        <label class="switch">
                            <input type="checkbox" id="autoBackup" name="autoBackup" value="1"
                                <?= $backupPrefs['auto_backup'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="frecuenciaGroup" style="display: none;">
                    <label for="frecuencia">Frecuencia de Copias</label>
                    <select class="form-control" id="frecuencia" name="frecuencia">
                        <option value="diaria" <?= $backupPrefs['frecuencia'] == 'diaria' ? 'selected' : '' ?>>Diaria
                        </option>
                        <option value="semanal" <?= $backupPrefs['frecuencia'] == 'semanal' ? 'selected' : '' ?>>
                            Semanal</option>
                        <option value="mensual" <?= $backupPrefs['frecuencia'] == 'mensual' ? 'selected' : '' ?>>
                            Mensual</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="retencion_dias">Días de Retención</label>
                    <input type="number" class="form-control" id="retencion_dias" name="retencion_dias"
                        value="<?= htmlspecialchars($backupPrefs['retencion_dias']) ?>">
                </div>


                <div class="form-group">
                    <label>Última copia de seguridad:</label>
                    <p id="lastBackupDate">
                        <?= $backupPrefs['ultima_copia'] ? htmlspecialchars($backupPrefs['ultima_copia']) : 'No disponible' ?>
                    </p>
                </div>

                <div class="section-actions">
                    <button type="submit" class="btn btn-primary" id="saveBackupSettings">
                        <i class="fas fa-save"></i> Guardar Configuración
                    </button>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const checkbox = document.getElementById("autoBackup");
                            const group = document.getElementById("frecuenciaGroup");

                            const toggleGroup = () => {
                                group.style.display = checkbox.checked ? "block" : "none";
                            };

                            checkbox.addEventListener("change", toggleGroup);
                            toggleGroup(); // Llamar al cargar
                        });
                    </script>

                    <button type="button" class="btn btn-secondary" id="createBackupNow">
                        <i class="fas fa-download"></i> Crear Copia Ahora
                    </button>
                    <script>
                        document.getElementById("createBackupNow").addEventListener("click", () => {
                            if (confirm("¿Deseas crear una copia de seguridad ahora?")) {
                                window.location.href = "../servicios/crear_copia_ahora.php";
                            }
                        });
                    </script>
                </div>
            </div>
        </form>



        <?php
        $usuarioId = $_SESSION['usuario_id'] ?? null;

        // Inicializar permisos vacíos
        $permisosGuardados = [];

        // Consultar permisos si hay usuario
        if ($usuarioId) {
            $sql = "SELECT * FROM permisos_usuario WHERE usuario_id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $resultado = $stmt->get_result();

            while ($perm = $resultado->fetch_assoc()) {
                $modulo = $perm['modulo'];
                $permisosGuardados[$modulo] = [
                    'ver' => $perm['ver'],
                    'crear' => $perm['crear'],
                    'editar' => $perm['editar'],
                    'eliminar' => $perm['eliminar']
                ];
            }

            // Obtener también el rol del usuario si está guardado
            $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuarios = ?");
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $rolGuardado = $resultado->fetch_assoc()['rol'] ?? '';
        }
        ?>


        <!-- Sección de Autenticación de Dos Factores -->
        <?php
        $preferencias2FA = [
            'two_factor_enabled' => 0,
            'two_factor_method' => 'email'
        ];

        if ($usuarioId) {
            $stmt = $conexion->prepare("SELECT two_factor_enabled, two_factor_method FROM usuarios WHERE id_usuarios = ?");
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $preferencias2FA = $row;
            }
        }
        ?>

        <form action="../servicios/guardar_2fa.php" method="POST" id="form2FA">
            <div class="config-section">
                <h3><i class="fas fa-shield-alt"></i> Autenticación de Dos Factores (2FA)</h3>
                
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <label>Activar Autenticación de Dos Factores</label>
                        <label class="switch">
                            <input type="checkbox" id="enable2FA" name="enable2FA" value="1" 
                                <?= $preferencias2FA['two_factor_enabled'] ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <small style="color: #6b7280;">
                        La autenticación de dos factores añade una capa adicional de seguridad a su cuenta.
                    </small>
                </div>

                <div class="form-group" id="method2FAGroup" style="<?= $preferencias2FA['two_factor_enabled'] ? '' : 'display: none;' ?>">
                    <label for="method2FA">Método de Verificación</label>
                    <select class="form-control" id="method2FA" name="method2FA">
                        <option value="email" <?= $preferencias2FA['two_factor_method'] == 'email' ? 'selected' : '' ?>>
                            📧 Correo Electrónico
                        </option>
                        <option value="sms" <?= $preferencias2FA['two_factor_method'] == 'sms' ? 'selected' : '' ?>>
                            📱 Mensaje SMS
                        </option>
                    </select>
                    <small style="color: #6b7280;">
                        Seleccione cómo desea recibir los códigos de verificación.
                    </small>
                </div>

                <div class="section-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración 2FA
                    </button>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const checkbox = document.getElementById("enable2FA");
                const methodGroup = document.getElementById("method2FAGroup");

                checkbox.addEventListener("change", () => {
                    methodGroup.style.display = checkbox.checked ? "block" : "none";
                });
            });
        </script>

        <!-- Sección de Permisos de Usuario -->
        <form action="../servicios/guardar_permisos.php" method="POST" id="formPermisos">
            <div class="config-section" id="userPermissionsSection">
                <h3><i class="fas fa-user-shield"></i> Permisos de Usuario</h3>

                <div class="form-group">
                    <label for="userRoles">Rol del Usuario</label>
                    <select class="form-control" id="userRoles" name="userRole">
                        <select class="form-control" id="userRoles" name="userRole">
                            <option value="admin" <?= $rolGuardado == 'admin' ? 'selected' : '' ?>>Administrador</option>
                            <option value="manager" <?= $rolGuardado == 'manager' ? 'selected' : '' ?>>Gerente</option>
                            <option value="operator" <?= $rolGuardado == 'operator' ? 'selected' : '' ?>>Operador</option>
                            <option value="viewer" <?= $rolGuardado == 'viewer' ? 'selected' : '' ?>>Visualizador</option>
                        </select>

                    </select>
                </div>

                <table class="permissions-table">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Ver</th>
                            <th>Crear</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cada fila representa un módulo -->
                        <tr>
                            <td>Productos</td>
                            <td><input type="checkbox" name="permisos[productos][ver]"
                                    <?= !empty($permisosGuardados['productos']['ver']) ? 'checked' : '' ?>>
                            </td>
                            <td><input type="checkbox" name="permisos[productos][crear]"
                                    <?= !empty($permisosGuardados['productos']['ver']) ? 'checked' : '' ?>>
                            </td>
                            <td><input type="checkbox" name="permisos[productos][editar]"
                                    <?= !empty($permisosGuardados['productos']['ver']) ? 'checked' : '' ?>>
                            </td>
                            <td><input type="checkbox" name="permisos[productos][eliminar]"
                                    <?= !empty($permisosGuardados['productos']['ver']) ? 'checked' : '' ?>>
                            </td>
                        </tr>
                        <tr>
                        <tr>
                            <td>Categorías</td>
                            <td><input type="checkbox" name="permisos[categorias][ver]"
                                    <?= !empty($permisosGuardados['categorias']['ver']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[categorias][crear]"
                                    <?= !empty($permisosGuardados['categorias']['crear']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[categorias][editar]"
                                    <?= !empty($permisosGuardados['categorias']['editar']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[categorias][eliminar]"
                                    <?= !empty($permisosGuardados['categorias']['eliminar']) ? 'checked' : '' ?>></td>
                        </tr>
                        <tr>
                            <td>Movimientos</td>
                            <td><input type="checkbox" name="permisos[movimientos][ver]"
                                    <?= !empty($permisosGuardados['movimientos']['ver']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[movimientos][crear]"
                                    <?= !empty($permisosGuardados['movimientos']['crear']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[movimientos][editar]"
                                    <?= !empty($permisosGuardados['movimientos']['editar']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[movimientos][eliminar]"
                                    <?= !empty($permisosGuardados['movimientos']['eliminar']) ? 'checked' : '' ?>></td>
                        </tr>
                        <tr>
                            <td>Reportes</td>
                            <td><input type="checkbox" name="permisos[reportes][ver]"
                                    <?= !empty($permisosGuardados['reportes']['ver']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[reportes][crear]"
                                    <?= !empty($permisosGuardados['reportes']['crear']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[reportes][editar]"
                                    <?= !empty($permisosGuardados['reportes']['editar']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[reportes][eliminar]"
                                    <?= !empty($permisosGuardados['reportes']['eliminar']) ? 'checked' : '' ?>></td>
                        </tr>
                        <tr>
                            <td>Usuarios</td>
                            <td><input type="checkbox" name="permisos[usuarios][ver]"
                                    <?= !empty($permisosGuardados['usuarios']['ver']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[usuarios][crear]"
                                    <?= !empty($permisosGuardados['usuarios']['crear']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[usuarios][editar]"
                                    <?= !empty($permisosGuardados['usuarios']['editar']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[usuarios][eliminar]"
                                    <?= !empty($permisosGuardados['usuarios']['eliminar']) ? 'checked' : '' ?>></td>
                        </tr>
                        <tr>
                            <td>Configuración</td>
                            <td><input type="checkbox" name="permisos[configuracion][ver]"
                                    <?= !empty($permisosGuardados['configuracion']['ver']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[configuracion][crear]"
                                    <?= !empty($permisosGuardados['configuracion']['crear']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[configuracion][editar]"
                                    <?= !empty($permisosGuardados['configuracion']['editar']) ? 'checked' : '' ?>></td>
                            <td><input type="checkbox" name="permisos[configuracion][eliminar]"
                                    <?= !empty($permisosGuardados['configuracion']['eliminar']) ? 'checked' : '' ?>></td>
                        </tr>

                        </tr>
                    </tbody>
                </table>

                <div class="section-actions">
                    <button type="button" class="btn btn-secondary" id="resetPermissions">
                        <i class="fas fa-undo"></i> Restablecer
                    </button>
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            const resetBtn = document.getElementById("resetPermissions");
                            const form = document.getElementById("formPermisos");

                            resetBtn.addEventListener("click", function () {
                                const checkboxes = form.querySelectorAll('input[type="checkbox"]');
                                checkboxes.forEach(checkbox => checkbox.checked = false);
                            });
                        });
                    </script>

                    <button type="submit" class="btn btn-primary" id="savePermissions">
                        <i class="fas fa-save"></i> Guardar Permisos
                    </button>
                </div>
            </div>
        </form>
