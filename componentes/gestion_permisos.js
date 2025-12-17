// gestion_permisos.js - Visualización de permisos del sistema

document.addEventListener('DOMContentLoaded', function() {
    initializePermissionsPage();
    setupEventListeners();
    
    // Cargar permisos del administrador por defecto
    loadPermissions('administrador');
});

function initializePermissionsPage() {
    console.log('Inicializando página de gestión de permisos');
}

function setupEventListeners() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Botón ver permisos
    const btnViewPermissions = document.getElementById('btnViewPermissions');
    if (btnViewPermissions) {
        btnViewPermissions.addEventListener('click', function() {
            const rol = document.getElementById('roleSelect').value;
            loadPermissions(rol);
        });
    }
    
    // Cambio de rol en select
    const roleSelect = document.getElementById('roleSelect');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            loadPermissions(this.value);
        });
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

async function loadPermissions(rol) {
    try {
        showLoading();
        
        const response = await fetch(`../servicios/obtener_permisos_rol.php?rol=${rol}`);
        const data = await response.json();
        
        if (data.success) {
            updateSummary(data.estadisticas);
            renderPermissionsMatrix(data.matriz, rol);
            renderPermissionsTable(data.modulos, data.matriz);
        } else {
            showNotification('Error al cargar permisos: ' + data.error, 'error');
        }
        
        hideLoading();
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al cargar permisos', 'error');
        hideLoading();
    }
}

function updateSummary(stats) {
    document.getElementById('totalModules').textContent = stats.total_modulos;
    document.getElementById('totalPermissions').textContent = stats.total_permisos;
    document.getElementById('activePermissions').textContent = stats.permisos_activos;
    
    // Animar los números
    animateNumbers();
}

function animateNumbers() {
    const numbers = document.querySelectorAll('.summary-content h3');
    numbers.forEach(num => {
        num.style.animation = 'none';
        setTimeout(() => {
            num.style.animation = 'pulse 0.5s ease';
        }, 10);
    });
}

function renderPermissionsMatrix(matriz, rol) {
    const container = document.getElementById('matrixContainer');
    
    if (Object.keys(matriz).length === 0) {
        container.innerHTML = '<p class="loading-text">No hay permisos asignados a este rol</p>';
        return;
    }
    
    // Obtener todos los permisos únicos
    const allPermissions = new Set();
    Object.values(matriz).forEach(permisos => {
        Object.keys(permisos).forEach(permiso => allPermissions.add(permiso));
    });
    
    const permissionsArray = Array.from(allPermissions);
    
    let html = `
        <table class="matrix-table">
            <thead>
                <tr>
                    <th>Módulo</th>
                    ${permissionsArray.map(p => `<th>${capitalizeFirst(p)}</th>`).join('')}
                </tr>
            </thead>
            <tbody>
    `;
    
    Object.entries(matriz).forEach(([modulo, permisos]) => {
        html += `<tr>`;
        html += `<td><strong>${capitalizeFirst(modulo.replace('_', ' '))}</strong></td>`;
        
        permissionsArray.forEach(permiso => {
            const hasPermission = permisos[permiso] && permisos[permiso].activo;
            const icon = hasPermission 
                ? '<i class="fas fa-check-circle permission-icon has-permission"></i>' 
                : '<i class="fas fa-times-circle permission-icon no-permission"></i>';
            html += `<td>${icon}</td>`;
        });
        
        html += `</tr>`;
    });
    
    html += `
            </tbody>
        </table>
    `;
    
    container.innerHTML = html;
}

function renderPermissionsTable(modulos, matriz) {
    const tbody = document.getElementById('permissionsTableBody');
    
    if (modulos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No hay módulos accesibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = '';
    
    modulos.forEach(modulo => {
        const tr = document.createElement('tr');
        
        // Nombre del módulo
        const tdNombre = document.createElement('td');
        tdNombre.innerHTML = `<strong><i class="fas ${modulo.icono}"></i> ${capitalizeFirst(modulo.nombre.replace('_', ' '))}</strong>`;
        tr.appendChild(tdNombre);
        
        // Descripción
        const tdDesc = document.createElement('td');
        tdDesc.textContent = modulo.descripcion || 'Sin descripción';
        tr.appendChild(tdDesc);
        
        // Permisos
        const tdPermisos = document.createElement('td');
        const permisosDiv = document.createElement('div');
        permisosDiv.className = 'permissions-list';
        
        if (modulo.permisos && modulo.permisos.length > 0) {
            modulo.permisos.forEach(permiso => {
                const badge = document.createElement('span');
                badge.className = 'permission-badge active';
                badge.textContent = capitalizeFirst(permiso);
                permisosDiv.appendChild(badge);
            });
        } else {
            permisosDiv.textContent = 'Sin permisos';
        }
        
        tdPermisos.appendChild(permisosDiv);
        tr.appendChild(tdPermisos);
        
        // Estado
        const tdEstado = document.createElement('td');
        const estadoBadge = document.createElement('span');
        estadoBadge.className = 'status-badge status-active';
        estadoBadge.innerHTML = '<i class="fas fa-check-circle"></i> Activo';
        tdEstado.appendChild(estadoBadge);
        tr.appendChild(tdEstado);
        
        tbody.appendChild(tr);
    });
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function showLoading() {
    const container = document.getElementById('matrixContainer');
    container.innerHTML = '<p class="loading-text"><i class="fas fa-spinner fa-spin"></i> Cargando permisos...</p>';
}

function hideLoading() {
    // La función de renderizado reemplazará el contenido de carga
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
        <button class="close-notification">&times;</button>
    `;
    
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        background: type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3',
        color: 'white',
        padding: '15px 20px',
        borderRadius: '5px',
        boxShadow: '0 4px 15px rgba(0, 0, 0, 0.2)',
        zIndex: '10000',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        animation: 'slideInRight 0.3s ease',
        maxWidth: '300px'
    });
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
    
    notification.querySelector('.close-notification').addEventListener('click', () => {
        notification.remove();
    });
}

// Animaciones CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }
`;
document.head.appendChild(style);
