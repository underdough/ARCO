// categorias_protegido.js - Con control de permisos

document.addEventListener('DOMContentLoaded', function() {
    initializeCategoriesPage();
    loadCategories();
    setupEventListeners();
    applyPermissions();
});

// Aplicar permisos a la interfaz
function applyPermissions() {
    const permisos = window.userPermissions || {};
    
    // Ocultar botón crear si no tiene permiso
    if (!permisos.crear) {
        hideElement('#btnAddCategory');
    }
    
    console.log('Permisos aplicados:', permisos);
    console.log('Usuario:', window.userInfo);
}

function hideElement(selector) {
    const element = document.querySelector(selector);
    if (element) {
        element.style.display = 'none';
    }
}

function initializeCategoriesPage() {
    console.log('Inicializando página de categorías con permisos');
}

function setupEventListeners() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Botón agregar categoría
    const btnAddCategory = document.getElementById('btnAddCategory');
    if (btnAddCategory) {
        btnAddCategory.addEventListener('click', openAddCategoryModal);
    }
    
    // Botón cancelar
    const btnCancelCategory = document.getElementById('btnCancelCategory');
    if (btnCancelCategory) {
        btnCancelCategory.addEventListener('click', closeCategoryModal);
    }
    
    // Cerrar modal
    const closeModal = document.querySelector('.close-modal');
    if (closeModal) {
        closeModal.addEventListener('click', closeCategoryModal);
    }
    
    // Búsqueda
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', searchCategories);
    }
    
    // Formulario
    const categoryForm = document.getElementById('categoryForm');
    if (categoryForm) {
        categoryForm.addEventListener('submit', handleCategorySubmit);
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('active');
}

function openAddCategoryModal() {
    if (!window.userPermissions.crear) {
        showNotification('No tiene permisos para crear categorías', 'error');
        return;
    }
    
    const modal = document.getElementById('categoryModal');
    modal.style.display = 'flex';
    document.querySelector('.modal-title').textContent = 'Agregar Nueva Categoría';
    document.getElementById('categoryForm').reset();
    delete document.getElementById('categoryForm').dataset.categoryId;
}

function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    modal.style.display = 'none';
}

async function loadCategories() {
    try {
        const response = await fetch('../servicios/listar_categorias.php');
        const data = await response.json();
        
        const categorias = data.data || data;
        renderCategories(categorias);
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar categorías', 'error');
    }
}

function renderCategories(categories) {
    const tbody = document.getElementById('categoryTableBody');
    tbody.innerHTML = '';
    
    if (categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">No hay categorías registradas</td></tr>';
        return;
    }
    
    categories.forEach(cat => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${cat.id_categorias}</td>
            <td>${cat.nombre_cat}</td>
            <td>${cat.subcategorias}</td>
            <td>${cat.productos ?? '--'}</td>
            <td>${cat.estado == 1 ? 'Activa' : 'Inactiva'}</td>
            <td class="actions">
                ${renderActionButtons(cat)}
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    attachActionListeners();
}

function renderActionButtons(category) {
    const permisos = window.userPermissions || {};
    let buttons = '';
    
    if (permisos.editar) {
        buttons += `<button class="action-icon edit" data-id="${category.id_categorias}"><i class="fas fa-edit"></i></button>`;
    }
    
    if (permisos.eliminar) {
        buttons += `<button class="action-icon delete" data-id="${category.id_categorias}"><i class="fas fa-trash"></i></button>`;
    }
    
    if (!permisos.editar && !permisos.eliminar) {
        buttons = '<span style="color: #999;">Sin acciones</span>';
    }
    
    return buttons;
}

function attachActionListeners() {
    // Botones de editar
    document.querySelectorAll('.edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            editCategory(categoryId);
        });
    });
    
    // Botones de eliminar
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoryId = this.dataset.id;
            deleteCategory(categoryId);
        });
    });
}

async function editCategory(categoryId) {
    if (!window.userPermissions.editar) {
        showNotification('No tiene permisos para editar categorías', 'error');
        return;
    }
    
    try {
        const response = await fetch('../servicios/listar_categorias.php');
        const data = await response.json();
        const categorias = data.data || data;
        const category = categorias.find(c => c.id_categorias == categoryId);
        
        if (category) {
            document.getElementById('categoryName').value = category.nombre_cat;
            document.getElementById('categoryDescription').value = category.subcategorias;
            document.getElementById('categoryProducts').value = category.productos || 0;
            document.getElementById('categoryStatus').value = category.estado;
            
            document.querySelector('.modal-title').textContent = 'Editar Categoría';
            document.getElementById('categoryForm').dataset.categoryId = categoryId;
            document.getElementById('categoryModal').style.display = 'flex';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar categoría', 'error');
    }
}

async function deleteCategory(categoryId) {
    if (!window.userPermissions.eliminar) {
        showNotification('No tiene permisos para eliminar categorías', 'error');
        return;
    }
    
    if (!confirm('¿Está seguro de eliminar esta categoría?')) {
        return;
    }
    
    try {
        const response = await fetch(`../servicios/eliminar_categoria.php?id=${categoryId}`);
        const data = await response.json();
        
        if (data.success) {
            showNotification('Categoría eliminada correctamente', 'success');
            loadCategories();
        } else {
            showNotification(data.message || 'Error al eliminar categoría', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    }
}

async function handleCategorySubmit(e) {
    e.preventDefault();
    
    const categoryId = this.dataset.categoryId;
    const isEdit = !!categoryId;
    
    if (isEdit && !window.userPermissions.editar) {
        showNotification('No tiene permisos para editar categorías', 'error');
        return;
    }
    
    if (!isEdit && !window.userPermissions.crear) {
        showNotification('No tiene permisos para crear categorías', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('nombre_cat', document.getElementById('categoryName').value);
    formData.append('subcategorias', document.getElementById('categoryDescription').value);
    formData.append('productos', document.getElementById('categoryProducts').value);
    formData.append('estado', document.getElementById('categoryStatus').value);
    
    const url = isEdit 
        ? `../servicios/editar_categoria.php?id=${categoryId}`
        : '../servicios/agregar_categoria.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(isEdit ? 'Categoría actualizada' : 'Categoría creada', 'success');
            closeCategoryModal();
            loadCategories();
        } else {
            showNotification(data.message || 'Error al guardar categoría', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    }
}

function searchCategories() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#categoryTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
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
