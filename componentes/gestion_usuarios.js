// =====================================================
// GESTIÓN AVANZADA DE USUARIOS - JavaScript
// =====================================================

let usuariosData = [];

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
    configurarEventos();
});

// ===== CONFIGURACIÓN DE EVENTOS =====
function configurarEventos() {
    // Toggle sidebar
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

    // Búsqueda en tiempo real
    document.getElementById('searchInput')?.addEventListener('input', debounce(aplicarFiltros, 500));
    
    // Filtros
    document.getElementById('filterRol')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado')?.addEventListener('change', aplicarFiltros);
    
    // Cerrar modales con ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            cerrarModalCrear();
            cerrarModalEditar();
        }
    });
    
    // Formulario crear usuario
    document.getElementById('formCrearUsuario')?.addEventListener('submit', crearUsuario);
    
    // Formulario editar usuario
    document.getElementById('formEditarUsuario')?.addEventListener('submit', actualizarUsuario);
}

// ===== CARGAR USUARIOS =====
async function cargarUsuarios() {
    try {
        const busqueda = document.getElementById('searchInput')?.value || '';
        const rol = document.getElementById('filterRol')?.value || '';
        const estado = document.getElementById('filterEstado')?.value || '';
        
        const params = new URLSearchParams();
        if (busqueda) params.append('busqueda', busqueda);
        if (rol) params.append('rol', rol);
        if (estado) params.append('estado', estado);
        
        const response = await fetch(`../servicios/listar_usuarios_mejorado.php?${params.toString()}`);
        const data = await response.json();
        
        if (data.success) {
            usuariosData = data.usuarios;
            renderizarTabla(data.usuarios);
            actualizarEstadisticas(data.usuarios);
        } else {
            mostrarError('Error al cargar usuarios');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error de conexión al cargar usuarios');
    }
}

// ===== RENDERIZAR TABLA =====
function renderizarTabla(usuarios) {
    const container = document.getElementById('usersTableContainer');
    
    if (usuarios.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <i class="fas fa-users fa-3x"></i>
                <p>No se encontraron usuarios con los filtros aplicados</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="users-table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Cargo/Área</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    usuarios.forEach(usuario => {
        const estadoBadge = getEstadoBadge(usuario.estado);
        const rolBadge = `<span class="badge badge-rol">${capitalize(usuario.rol)}</span>`;
        
        html += `
            <tr>
                <td>${usuario.num_doc}</td>
                <td><strong>${usuario.nombre_completo}</strong></td>
                <td>${usuario.correo}</td>
                <td>${rolBadge}</td>
                <td>${usuario.cargos}</td>
                <td>${estadoBadge}</td>
                <td>
                    <div class="action-buttons-group">
                        <button class="btn-action btn-edit" onclick="abrirModalEditar(${usuario.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-toggle" onclick="cambiarEstado(${usuario.id}, '${usuario.estado}')" title="Cambiar estado">
                            <i class="fas fa-toggle-on"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="confirmarEliminar(${usuario.id}, '${usuario.nombre_completo}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    container.innerHTML = html;
}

// ===== ACTUALIZAR ESTADÍSTICAS =====
function actualizarEstadisticas(usuarios) {
    const total = usuarios.length;
    const activos = usuarios.filter(u => u.estado === 'ACTIVO').length;
    const inactivos = usuarios.filter(u => u.estado === 'INACTIVO' || u.estado === 'SUSPENDIDO').length;
    
    document.getElementById('totalUsuarios').textContent = total;
    document.getElementById('usuariosActivos').textContent = activos;
    document.getElementById('usuariosInactivos').textContent = inactivos;
}

// ===== APLICAR FILTROS =====
function aplicarFiltros() {
    cargarUsuarios();
}

// ===== CREAR USUARIO =====
async function crearUsuario(e) {
    e.preventDefault();
    
    const password = document.getElementById('contrasena').value;
    const confirmPassword = document.getElementById('confirmarContrasena').value;
    const submitBtn = document.getElementById('btnCrearUsuario');
    const form = document.getElementById('formCrearUsuario');

    if (password !== confirmPassword) {
        showAlert('❌ Las contraseñas no coinciden', 'error');
        showNotification('Las contraseñas no coinciden', 'error');
        return;
    }

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando usuario...';
    submitBtn.disabled = true;

    try {
        const formData = new FormData(form);
        const nombreCompleto = formData.get('nombre') + ' ' + formData.get('apellido');
        
        const response = await fetch('../servicios/registro_mejorado.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showAlert('✅ ' + result.message, 'success');
            showNotification(`Usuario "${nombreCompleto}" creado exitosamente`, 'success');
            form.reset();
            
            // Registrar en auditoría
            registrarAuditoria('CREAR USUARIO', `Usuario "${nombreCompleto}" creado con rol: ${formData.get('rol')}`);
            
            setTimeout(() => {
                cerrarModalCrear();
                cargarUsuarios();
            }, 1500);
        } else {
            showAlert('❌ ' + result.message, 'error');
            showNotification('Error al crear usuario: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('❌ Error de conexión. Por favor, intenta nuevamente.', 'error');
        showNotification('Error de conexión al crear usuario', 'error');
    } finally {
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Crear Usuario';
        submitBtn.disabled = false;
    }
}

// ===== ABRIR MODAL EDITAR =====
async function abrirModalEditar(userId) {
    const usuario = usuariosData.find(u => u.id === userId);
    
    if (!usuario) {
        showAlert('Usuario no encontrado', 'error', 'alertContainerEdit');
        return;
    }
    
    // Llenar formulario
    document.getElementById('edit_id_usuarios').value = usuario.id;
    document.getElementById('edit_nombre').value = usuario.nombre;
    document.getElementById('edit_apellido').value = usuario.apellido;
    document.getElementById('edit_num_doc').value = usuario.num_doc;
    document.getElementById('edit_telefono').value = usuario.num_telefono || '';
    document.getElementById('edit_correo').value = usuario.correo;
    document.getElementById('edit_rol').value = usuario.rol;
    document.getElementById('edit_cargos').value = usuario.cargos;
    document.getElementById('edit_estado').value = usuario.estado;
    
    const modal = document.getElementById('editarModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// ===== ACTUALIZAR USUARIO =====
async function actualizarUsuario(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('btnEditarUsuario');
    const form = document.getElementById('formEditarUsuario');

    // Confirmar antes de actualizar
    const nombreCompleto = document.getElementById('edit_nombre').value + ' ' + document.getElementById('edit_apellido').value;
    const confirmar = confirm(`¿Está seguro de actualizar la información del usuario "${nombreCompleto}"?`);
    
    if (!confirmar) return;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
    submitBtn.disabled = true;

    try {
        const formData = new FormData(form);
        
        const response = await fetch('../servicios/actualizar_usuario_mejorado.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showAlert('✅ ' + result.message, 'success', 'alertContainerEdit');
            showNotification(`Usuario "${nombreCompleto}" actualizado correctamente`, 'success');
            
            // Registrar en auditoría
            registrarAuditoria('EDITAR USUARIO', `Usuario "${nombreCompleto}" actualizado - ${result.cambios} cambios realizados`);
            
            setTimeout(() => {
                cerrarModalEditar();
                cargarUsuarios();
            }, 1500);
        } else {
            showAlert('❌ ' + result.message, 'error', 'alertContainerEdit');
            showNotification('Error al actualizar usuario: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('❌ Error de conexión. Por favor, intenta nuevamente.', 'error', 'alertContainerEdit');
        showNotification('Error de conexión al actualizar usuario', 'error');
    } finally {
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        submitBtn.disabled = false;
    }
}

// ===== CAMBIAR ESTADO =====
async function cambiarEstado(userId, estadoActual) {
    const usuario = usuariosData.find(u => u.id === userId);
    if (!usuario) return;
    
    const nombreCompleto = usuario.nombre_completo;
    const estados = ['ACTIVO', 'INACTIVO', 'SUSPENDIDO'];
    const estadoActualIndex = estados.indexOf(estadoActual);
    const nuevoEstado = estados[(estadoActualIndex + 1) % estados.length];
    
    // Mensaje personalizado según el estado
    let mensajeConfirmacion = '';
    let accionTexto = '';
    
    if (nuevoEstado === 'INACTIVO') {
        mensajeConfirmacion = `¿Está seguro de DESACTIVAR al usuario "${nombreCompleto}"?\n\nEl usuario no podrá acceder al sistema hasta que sea reactivado.`;
        accionTexto = 'desactivado';
    } else if (nuevoEstado === 'SUSPENDIDO') {
        mensajeConfirmacion = `¿Está seguro de SUSPENDER al usuario "${nombreCompleto}"?\n\nEsta acción indica una suspensión temporal por razones administrativas.`;
        accionTexto = 'suspendido';
    } else {
        mensajeConfirmacion = `¿Está seguro de ACTIVAR al usuario "${nombreCompleto}"?\n\nEl usuario podrá acceder al sistema normalmente.`;
        accionTexto = 'activado';
    }
    
    const confirmacion = confirm(mensajeConfirmacion);
    
    if (!confirmacion) {
        showNotification('Cambio de estado cancelado', 'info');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('id_usuario', userId);
        formData.append('estado', nuevoEstado);
        
        const response = await fetch('../servicios/cambiar_estado_usuario.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(`✅ Usuario "${nombreCompleto}" ${accionTexto} correctamente`, 'success');
            
            // Registrar en auditoría
            registrarAuditoria('CAMBIAR ESTADO', `Usuario "${nombreCompleto}" - Estado: ${estadoActual} → ${nuevoEstado}`);
            
            cargarUsuarios();
        } else {
            showNotification('❌ Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error al cambiar el estado del usuario', 'error');
    }
}

// ===== CONFIRMAR ELIMINAR =====
function confirmarEliminar(userId, nombreCompleto) {
    // Primera confirmación
    const confirmacion1 = confirm(
        `⚠️ ADVERTENCIA: ELIMINACIÓN PERMANENTE\n\n` +
        `¿Está seguro de eliminar al usuario "${nombreCompleto}"?\n\n` +
        `Esta acción NO se puede deshacer.\n` +
        `Se recomienda DESACTIVAR el usuario en lugar de eliminarlo.`
    );
    
    if (!confirmacion1) {
        showNotification('Eliminación cancelada', 'info');
        return;
    }
    
    // Segunda confirmación para acciones críticas
    const confirmacion2 = confirm(
        `CONFIRMACIÓN FINAL\n\n` +
        `Escriba mentalmente "CONFIRMAR" para proceder con la eliminación de "${nombreCompleto}"\n\n` +
        `¿Desea continuar?`
    );
    
    if (confirmacion2) {
        eliminarUsuario(userId, nombreCompleto);
    } else {
        showNotification('Eliminación cancelada', 'info');
    }
}

// ===== ELIMINAR USUARIO =====
async function eliminarUsuario(userId, nombreCompleto) {
    try {
        const formData = new FormData();
        formData.append('id_usuario', userId);
        
        const response = await fetch('../servicios/eliminar_usuario_mejorado.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification(`✅ Usuario "${nombreCompleto}" eliminado del sistema`, 'success');
            
            // Registrar en auditoría
            registrarAuditoria('ELIMINAR USUARIO', `Usuario "${nombreCompleto}" eliminado permanentemente del sistema`);
            
            cargarUsuarios();
        } else {
            showNotification('❌ Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error al eliminar el usuario', 'error');
    }
}

// ===== MODALES =====
function abrirModalCrearUsuario() {
    const modal = document.getElementById('crearUsuarioModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function cerrarModalCrear() {
    const modal = document.getElementById('crearUsuarioModal');
    modal.style.display = 'none';
    document.getElementById('formCrearUsuario').reset();
    document.getElementById('alertContainer').innerHTML = '';
    document.body.style.overflow = 'auto';
}

function cerrarModalEditar() {
    const modal = document.getElementById('editarModal');
    modal.style.display = 'none';
    document.getElementById('formEditarUsuario').reset();
    document.getElementById('alertContainerEdit').innerHTML = '';
    document.body.style.overflow = 'auto';
}

// ===== UTILIDADES =====
function togglePasswordVisibility(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function showAlert(message, type = 'error', containerId = 'alertContainer') {
    const alertContainer = document.getElementById(containerId);
    const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
    alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
    setTimeout(() => alertContainer.innerHTML = '', 5000);
}

function getEstadoBadge(estado) {
    const badges = {
        'ACTIVO': '<span class="badge badge-activo">ACTIVO</span>',
        'INACTIVO': '<span class="badge badge-inactivo">INACTIVO</span>',
        'SUSPENDIDO': '<span class="badge badge-suspendido">SUSPENDIDO</span>'
    };
    return badges[estado] || estado;
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function mostrarError(mensaje) {
    const container = document.getElementById('usersTableContainer');
    container.innerHTML = `
        <div class="no-results">
            <i class="fas fa-exclamation-triangle fa-3x" style="color: #f44336;"></i>
            <p>${mensaje}</p>
        </div>
    `;
}

// ===== SISTEMA DE NOTIFICACIONES TOAST =====
function showNotification(message, type = 'info') {
    // Crear contenedor de notificaciones si no existe
    let container = document.getElementById('notificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
    
    // Crear notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Iconos según tipo
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    // Colores según tipo
    const colors = {
        success: '#4CAF50',
        error: '#f44336',
        warning: '#ff9800',
        info: '#2196F3'
    };
    
    notification.style.cssText = `
        background: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        border-left: 4px solid ${colors[type]};
        animation: slideIn 0.3s ease-out;
        min-width: 300px;
    `;
    
    notification.innerHTML = `
        <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 20px;"></i>
        <span style="flex: 1; color: #333; font-size: 14px;">${message}</span>
        <button onclick="this.parentElement.remove()" style="
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        ">×</button>
    `;
    
    // Agregar animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    if (!document.getElementById('notificationStyles')) {
        style.id = 'notificationStyles';
        document.head.appendChild(style);
    }
    
    container.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// ===== REGISTRAR ACCIÓN EN AUDITORÍA (CONSOLA) =====
function registrarAuditoria(accion, detalles) {
    const timestamp = new Date().toLocaleString('es-CO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    console.log(`
╔════════════════════════════════════════════════════════════════
║ REGISTRO DE AUDITORÍA - GESTIÓN DE USUARIOS
╠════════════════════════════════════════════════════════════════
║ Fecha/Hora: ${timestamp}
║ Acción: ${accion}
║ Detalles: ${detalles}
║ Usuario: ${sessionStorage.getItem('usuario_nombre') || 'Administrador'}
╚════════════════════════════════════════════════════════════════
    `);
}
