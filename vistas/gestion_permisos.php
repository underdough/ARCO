<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}

// Solo administradores pueden ver permisos
if ($_SESSION['rol'] !== 'administrador') {
    header('Location: dashboard.php?error=No tiene permisos para acceder a esta sección');
    exit();
}

require_once '../servicios/menu_dinamico.php';

$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Gestión de Permisos</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
        <?php echo generarMenuHTML('permisos'); ?>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Permisos</h2>
            <div class="user-info" onclick="showUserMenu()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                <span>Bienvenido, <strong id="userName"><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>
        
        <!-- Selector de Rol -->
        <div class="dashboard-cards" style="margin-bottom: 20px;">
            <div class="card" style="grid-column: 1 / -1; cursor: default;">
                <div style="display: flex; align-items: center; gap: 15px; padding: 10px; flex-wrap: wrap;">
                    <label for="roleSelect" style="font-weight: 500; color: #333; min-width: 150px;">Seleccionar Rol:</label>
                    <select id="roleSelect" style="flex: 1; max-width: 300px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        <option value="administrador">Administrador</option>
                        <option value="gerente">Gerente</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="almacenista">Almacenista</option>
                        <option value="usuario">Usuario</option>
                    </select>
                    <button class="btn-login" id="btnViewPermissions" style="padding: 10px 20px;">
                        <i class="fas fa-eye"></i> Ver Permisos
                    </button>
                    <button class="btn-login" id="btnDebug" style="padding: 10px 20px; background: #dc3545;">
                        <i class="fas fa-bug"></i> Debug
                    </button>
                    <button class="btn-login" id="btnInstall" style="padding: 10px 20px; background: #28a745;">
                        <i class="fas fa-download"></i> Instalar Permisos
                    </button>
                    <button class="btn-login" id="btnInsertData" style="padding: 10px 20px; background: #17a2b8;">
                        <i class="fas fa-database"></i> Insertar Datos
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Resumen de Permisos -->
        <div class="dashboard-cards">
            <div class="card" onclick="return false;" style="cursor: default;">
                <div class="card-header">
                    <h3 class="card-title">Módulos Accesibles</h3>
                    <div class="card-icon">
                        <i class="fas fa-th-large"></i>
                    </div>
                </div>
                <div class="card-value" id="totalModules">0</div>
                <div class="card-footer">Total de módulos</div>
            </div>
            
            <div class="card" onclick="return false;" style="cursor: default;">
                <div class="card-header">
                    <h3 class="card-title">Permisos Totales</h3>
                    <div class="card-icon">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
                <div class="card-value" id="totalPermissions">0</div>
                <div class="card-footer">Permisos asignados</div>
            </div>
            
            <div class="card" onclick="return false;" style="cursor: default;">
                <div class="card-header">
                    <h3 class="card-title">Permisos Activos</h3>
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="card-value" id="activePermissions">0</div>
                <div class="card-footer">Actualmente activos</div>
            </div>
        </div>
        
        <!-- Matriz de Permisos -->
        <div class="recent-activity">
            <div class="activity-header">
                <h3>Matriz de Permisos por Módulo</h3>
                <button class="btn-login" id="btnSaveChanges" style="padding: 10px 20px; background: #28a745; display: none;">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
            <div id="matrixContainer" style="overflow-x: auto; padding: 20px;">
                <p style="text-align: center; color: #999; padding: 40px;">Seleccione un rol para ver sus permisos...</p>
            </div>
        </div>
        
        <!-- Tabla Detallada -->
        <div class="recent-activity">
            <div class="activity-header">
                <h3>Detalle de Permisos</h3>
            </div>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Módulo</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Descripción</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Permisos</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Estado</th>
                    </tr>
                </thead>
                <tbody id="permissionsTableBody">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: #999;">Seleccione un rol para ver los permisos</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // JavaScript para gestión de permisos
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            // Cargar permisos del administrador por defecto
            loadPermissions('administrador');
        });

        function setupEventListeners() {
            // Sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    const sidebar = document.getElementById('sidebar');
                    sidebar.classList.toggle('collapsed');
                });
            }
            
            // Botón ver permisos
            const btnViewPermissions = document.getElementById('btnViewPermissions');
            if (btnViewPermissions) {
                btnViewPermissions.addEventListener('click', function() {
                    const rol = document.getElementById('roleSelect').value;
                    loadPermissions(rol);
                });
            }
            
            // Botón debug
            const btnDebug = document.getElementById('btnDebug');
            if (btnDebug) {
                btnDebug.addEventListener('click', async function() {
                    try {
                        const response = await fetch('../servicios/verificar_permisos_db.php');
                        const data = await response.json();
                        console.log('Debug DB:', data);
                        
                        let mensaje = 'VERIFICACIÓN DE BASE DE DATOS:\n\n';
                        mensaje += `Módulos: ${data.tablas.modulos.registros} registros\n`;
                        mensaje += `Permisos: ${data.tablas.permisos.registros} registros\n`;
                        mensaje += `Rol-Permisos: ${data.tablas.rol_permisos.registros} registros\n`;
                        mensaje += `Permisos Admin: ${data.permisos_administrador}\n\n`;
                        
                        if (data.errores.length > 0) {
                            mensaje += 'ERRORES:\n' + data.errores.join('\n');
                            mensaje += '\n\n⚠ SOLUCIÓN: Hacer clic en "Instalar Permisos"';
                        } else if (data.tablas.rol_permisos.registros === 0) {
                            mensaje += '⚠ Las tablas existen pero están VACÍAS\n';
                            mensaje += '\n⚠ SOLUCIÓN: Hacer clic en "Instalar Permisos"';
                        } else {
                            mensaje += '✓ Todas las tablas existen y tienen datos';
                        }
                        
                        alert(mensaje);
                    } catch (error) {
                        console.error('Error debug:', error);
                        alert('Error al verificar BD: ' + error.message);
                    }
                });
            }
            
            // Botón instalar
            const btnInstall = document.getElementById('btnInstall');
            if (btnInstall) {
                btnInstall.addEventListener('click', function() {
                    if (confirm('¿Instalar el sistema de permisos?\n\nEsto creará las tablas y datos necesarios.\n\n⚠ Si ya existen datos, se mantendrán.')) {
                        window.open('../servicios/instalar_permisos.php', '_blank');
                        
                        setTimeout(() => {
                            if (confirm('¿La instalación se completó exitosamente?\n\nHacer clic en OK para recargar los permisos.')) {
                                location.reload();
                            }
                        }, 3000);
                    }
                });
            }
            
            // Botón insertar datos
            const btnInsertData = document.getElementById('btnInsertData');
            if (btnInsertData) {
                btnInsertData.addEventListener('click', function() {
                    if (confirm('¿Insertar datos de permisos?\n\nEsto llenará las tablas modulo_permisos y rol_permisos.\n\n⚠ Los datos existentes serán reemplazados.')) {
                        window.open('../servicios/insertar_permisos_directamente.php', '_blank');
                        
                        setTimeout(() => {
                            if (confirm('¿La inserción se completó exitosamente?\n\nHacer clic en OK para recargar los permisos.')) {
                                location.reload();
                            }
                        }, 3000);
                    }
                });
            }
            
            // Cambio de rol en select
            const roleSelect = document.getElementById('roleSelect');
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    loadPermissions(this.value);
                });
            }
            
            // Botón guardar cambios
            const btnSaveChanges = document.getElementById('btnSaveChanges');
            if (btnSaveChanges) {
                btnSaveChanges.addEventListener('click', async function() {
                    const rol = document.getElementById('roleSelect').value;
                    
                    if (!confirm(`¿Guardar los cambios de permisos para el rol "${rol}"?\n\nEsta acción modificará los permisos del rol en el sistema.`)) {
                        return;
                    }
                    
                    // Recopilar todos los checkboxes
                    const checkboxes = document.querySelectorAll('.permission-checkbox');
                    const permisos = [];
                    
                    checkboxes.forEach(checkbox => {
                        permisos.push({
                            id_modulo: parseInt(checkbox.dataset.idModulo),
                            id_permiso: parseInt(checkbox.dataset.idPermiso),
                            activo: checkbox.checked
                        });
                    });
                    
                    try {
                        btnSaveChanges.disabled = true;
                        btnSaveChanges.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                        
                        const response = await fetch('../servicios/actualizar_permisos_rol.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                rol: rol,
                                permisos: permisos
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert(`✓ Permisos actualizados correctamente\n\nRegistros actualizados: ${data.actualizados}`);
                            btnSaveChanges.style.background = '#28a745';
                            btnSaveChanges.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                            
                            // Recargar permisos
                            loadPermissions(rol);
                        } else {
                            alert('Error al guardar permisos: ' + data.error);
                            btnSaveChanges.disabled = false;
                            btnSaveChanges.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error de conexión: ' + error.message);
                        btnSaveChanges.disabled = false;
                        btnSaveChanges.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                    }
                });
            }
        }

        async function loadPermissions(rol) {
            try {
                showLoading();
                
                console.log('Cargando permisos para rol:', rol);
                const url = `../servicios/obtener_permisos_rol.php?rol=${rol}`;
                console.log('URL:', url);
                
                const response = await fetch(url);
                console.log('Response status:', response.status);
                
                const text = await response.text();
                console.log('Response text:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                    alert('Error: La respuesta no es JSON válido. Ver consola para detalles.');
                    hideLoading();
                    return;
                }
                
                console.log('Datos parseados:', data);
                
                if (data.success) {
                    console.log('Estadísticas:', data.estadisticas);
                    console.log('Módulos:', data.modulos);
                    console.log('Matriz:', data.matriz);
                    
                    updateSummary(data.estadisticas);
                    renderPermissionsMatrix(data.matriz, rol);
                    renderPermissionsTable(data.modulos, data.matriz);
                } else {
                    console.error('Error en respuesta:', data.error);
                    alert('Error al cargar permisos: ' + data.error + '\n\nPosible causa: Las tablas de permisos no existen en la base de datos.\n\nSolución: Ejecutar el script SQL:\nbase-datos/sistema_permisos_completo.sql');
                }
                
                hideLoading();
            } catch (error) {
                console.error('Error completo:', error);
                alert('Error de conexión: ' + error.message + '\n\nVerificar:\n1. Que el servidor esté corriendo\n2. Que exista servicios/obtener_permisos_rol.php\n3. Ver consola del navegador (F12) para más detalles');
                hideLoading();
            }
        }

        function updateSummary(stats) {
            document.getElementById('totalModules').textContent = stats.total_modulos;
            document.getElementById('totalPermissions').textContent = stats.total_permisos;
            document.getElementById('activePermissions').textContent = stats.permisos_activos;
        }

        function renderPermissionsMatrix(matriz, rol) {
            const container = document.getElementById('matrixContainer');
            const btnSave = document.getElementById('btnSaveChanges');
            
            if (!matriz || Object.keys(matriz).length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;">No hay permisos asignados a este rol</p>';
                btnSave.style.display = 'none';
                return;
            }
            
            // Mostrar botón guardar
            btnSave.style.display = 'inline-block';
            
            // Obtener todos los permisos únicos
            const allPermissions = new Set();
            Object.values(matriz).forEach(permisos => {
                Object.keys(permisos).forEach(permiso => allPermissions.add(permiso));
            });
            
            const permissionsArray = Array.from(allPermissions);
            
            let html = `
                <table style="width: 100%; border-collapse: collapse; min-width: 800px;" id="permissionsTable">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; position: sticky; left: 0; background: #f8f9fa;">Módulo</th>
                            ${permissionsArray.map(p => `<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">${capitalizeFirst(p)}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            Object.entries(matriz).forEach(([modulo, permisos]) => {
                html += `<tr style="border-bottom: 1px solid #eee;" data-modulo="${modulo}">`;
                html += `<td style="padding: 12px; font-weight: 500; position: sticky; left: 0; background: white;">${capitalizeFirst(modulo.replace('_', ' '))}</td>`;
                
                permissionsArray.forEach(permiso => {
                    const permisoData = permisos[permiso];
                    const hasPermission = permisoData && permisoData.activo;
                    const id_modulo = permisoData ? permisoData.id_modulo : '';
                    const id_permiso = permisoData ? permisoData.id_permiso : '';
                    
                    html += `<td style="padding: 12px; text-align: center;">`;
                    if (permisoData) {
                        html += `<input type="checkbox" 
                                       class="permission-checkbox" 
                                       data-modulo="${modulo}" 
                                       data-permiso="${permiso}"
                                       data-id-modulo="${id_modulo}"
                                       data-id-permiso="${id_permiso}"
                                       ${hasPermission ? 'checked' : ''}
                                       style="width: 20px; height: 20px; cursor: pointer;">`;
                    } else {
                        html += '<span style="color: #ccc;">N/A</span>';
                    }
                    html += `</td>`;
                });
                
                html += `</tr>`;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            container.innerHTML = html;
            
            // Agregar event listeners a los checkboxes
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Marcar que hay cambios pendientes
                    btnSave.style.background = '#ffc107';
                    btnSave.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Cambios Pendientes';
                });
            });
        }

        function renderPermissionsTable(modulos, matriz) {
            const tbody = document.getElementById('permissionsTableBody');
            
            if (!modulos || modulos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #999;">No hay módulos accesibles</td></tr>';
                return;
            }
            
            tbody.innerHTML = '';
            
            modulos.forEach(modulo => {
                const tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #eee';
                
                // Nombre del módulo
                const tdNombre = document.createElement('td');
                tdNombre.style.padding = '12px';
                tdNombre.innerHTML = `<strong><i class="fas ${modulo.icono}" style="margin-right: 8px; color: #395886;"></i> ${capitalizeFirst(modulo.nombre.replace('_', ' '))}</strong>`;
                tr.appendChild(tdNombre);
                
                // Descripción
                const tdDesc = document.createElement('td');
                tdDesc.style.padding = '12px';
                tdDesc.style.color = '#666';
                tdDesc.textContent = modulo.descripcion || 'Sin descripción';
                tr.appendChild(tdDesc);
                
                // Permisos
                const tdPermisos = document.createElement('td');
                tdPermisos.style.padding = '12px';
                
                if (modulo.permisos && modulo.permisos.length > 0) {
                    const permisosHtml = modulo.permisos.map(permiso => 
                        `<span style="display: inline-block; padding: 4px 10px; margin: 2px; background: #d4edda; color: #155724; border-radius: 12px; font-size: 12px;">${capitalizeFirst(permiso)}</span>`
                    ).join('');
                    tdPermisos.innerHTML = permisosHtml;
                } else {
                    tdPermisos.textContent = 'Sin permisos';
                    tdPermisos.style.color = '#999';
                }
                
                tr.appendChild(tdPermisos);
                
                // Estado
                const tdEstado = document.createElement('td');
                tdEstado.style.padding = '12px';
                tdEstado.innerHTML = '<span style="display: inline-block; padding: 4px 12px; background: #d4edda; color: #155724; border-radius: 12px; font-size: 12px;"><i class="fas fa-check-circle"></i> Activo</span>';
                tr.appendChild(tdEstado);
                
                tbody.appendChild(tr);
            });
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function showLoading() {
            const container = document.getElementById('matrixContainer');
            container.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Cargando permisos...</p>';
        }

        function hideLoading() {
            // La función de renderizado reemplazará el contenido de carga
        }
        
        function showUserMenu() {
            // Implementar menú de usuario
        }
    </script>
</body>
</html>
