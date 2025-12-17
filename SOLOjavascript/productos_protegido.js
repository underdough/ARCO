// productos_protegido.js - Con control de permisos

document.addEventListener('DOMContentLoaded', function() {
    initializeProductsPage();
    loadProducts();
    setupEventListeners();
    applyPermissions();
});

// Aplicar permisos a la interfaz
function applyPermissions() {
    const permisos = window.userPermissions || {};
    
    // Ocultar botones según permisos
    if (!permisos.crear) {
        hideElement('#btnAddProduct');
    }
    
    if (!permisos.importar) {
        hideElement('#btnImportProducts');
    }
    
    if (!permisos.exportar) {
        hideElement('#btnExportProducts');
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

function initializeProductsPage() {
    console.log('Inicializando página de productos con permisos');
}

function setupEventListeners() {
    // Sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Botón agregar producto
    const btnAddProduct = document.getElementById('btnAddProduct');
    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', openAddProductModal);
    }
    
    // Botón cancelar
    const btnCancelProduct = document.getElementById('btnCancelProduct');
    if (btnCancelProduct) {
        btnCancelProduct.addEventListener('click', closeProductModal);
    }
    
    // Cerrar modal
    const closeModal = document.querySelector('.close-modal');
    if (closeModal) {
        closeModal.addEventListener('click', closeProductModal);
    }
}

// Búsqueda de productos
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', searchProducts);
}

// Ordenamiento
const sortSelect = document.getElementById('sortSelect');
if (sortSelect) {
    sortSelect.addEventListener('change', sortProducts);
}

// Formulario de producto
const productForm = document.getElementById('productForm');
if (productForm) {
    productForm.addEventListener('submit', handleProductSubmit);
}

// Exportar productos
const btnExportProducts = document.getElementById('btnExportProducts');
if (btnExportProducts) {
    btnExportProducts.addEventListener('click', exportProducts);
}

// Importar productos
const btnImportProducts = document.getElementById('btnImportProducts');
if (btnImportProducts) {
    btnImportProducts.addEventListener('click', importProducts);
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');
}

function openAddProductModal() {
    if (!window.userPermissions.crear) {
        showNotification('No tiene permisos para crear productos', 'error');
        return;
    }
    
    const modal = document.getElementById('productModal');
    modal.style.display = 'flex';
    document.querySelector('.modal-title').textContent = 'Agregar Nuevo Producto';
    document.getElementById('productForm').reset();
    delete document.getElementById('productForm').dataset.productId;
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    modal.style.display = 'none';
}

async function loadProducts() {
    try {
        const response = await fetch('../servicios/listar_productos.php');
        const data = await response.json();
        
        if (data.success) {
            renderProducts(data.data);
        } else {
            showNotification('Error al cargar productos', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    }
}

function renderProducts(products) {
    const tbody = document.getElementById('productTableBody');
    tbody.innerHTML = '';
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No hay productos registrados</td></tr>';
        return;
    }
    
    products.forEach(product => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${product.id_producto}</td>
            <td>${product.nombre}</td>
            <td>${product.categoria || 'Sin categoría'}</td>
            <td>${product.stock}</td>
            <td>$${parseFloat(product.precio).toFixed(2)}</td>
            <td><span class="badge ${product.stock > 0 ? 'badge-success' : 'badge-danger'}">${product.stock > 0 ? 'Disponible' : 'Agotado'}</span></td>
            <td class="actions">
                ${renderActionButtons(product)}
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    attachActionListeners();
}

function renderActionButtons(product) {
    const permisos = window.userPermissions || {};
    let buttons = '';
    
    if (permisos.editar) {
        buttons += `<button class="action-icon edit" data-id="${product.id_producto}"><i class="fas fa-edit"></i></button>`;
    }
    
    if (permisos.eliminar) {
        buttons += `<button class="action-icon delete" data-id="${product.id_producto}"><i class="fas fa-trash"></i></button>`;
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
            const productId = this.dataset.id;
            editProduct(productId);
        });
    });
    
    // Botones de eliminar
    document.querySelectorAll('.delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            deleteProduct(productId);
        });
    });
}

async function editProduct(productId) {
    if (!window.userPermissions.editar) {
        showNotification('No tiene permisos para editar productos', 'error');
        return;
    }
    
    try {
        const response = await fetch(`../servicios/obtener_producto.php?id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            const product = data.data;
            document.getElementById('productName').value = product.nombre;
            document.getElementById('productCategory').value = product.id_categoria;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productPrice').value = product.precio;
            document.getElementById('productDescription').value = product.descripcion || '';
            
            document.querySelector('.modal-title').textContent = 'Editar Producto';
            document.getElementById('productForm').dataset.productId = productId;
            document.getElementById('productModal').style.display = 'flex';
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar producto', 'error');
    }
}

async function deleteProduct(productId) {
    if (!window.userPermissions.eliminar) {
        showNotification('No tiene permisos para eliminar productos', 'error');
        return;
    }
    
    if (!confirm('¿Está seguro de eliminar este producto?')) {
        return;
    }
    
    try {
        const response = await fetch(`../servicios/eliminar_producto.php?id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            showNotification('Producto eliminado correctamente', 'success');
            loadProducts();
        } else {
            showNotification(data.message || 'Error al eliminar producto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    }
}

async function handleProductSubmit(e) {
    e.preventDefault();
    
    const productId = this.dataset.productId;
    const isEdit = !!productId;
    
    if (isEdit && !window.userPermissions.editar) {
        showNotification('No tiene permisos para editar productos', 'error');
        return;
    }
    
    if (!isEdit && !window.userPermissions.crear) {
        showNotification('No tiene permisos para crear productos', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('nombre', document.getElementById('productName').value);
    formData.append('id_categoria', document.getElementById('productCategory').value);
    formData.append('stock', document.getElementById('productStock').value);
    formData.append('precio', document.getElementById('productPrice').value);
    formData.append('descripcion', document.getElementById('productDescription').value);
    
    const url = isEdit 
        ? `../servicios/editar_producto.php?id=${productId}`
        : '../servicios/agregar_producto.php';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(isEdit ? 'Producto actualizado' : 'Producto creado', 'success');
            closeProductModal();
            loadProducts();
        } else {
            showNotification(data.message || 'Error al guardar producto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    }
}

function searchProducts() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#productTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function sortProducts() {
    const [field, order] = this.value.split('-');
    console.log('Ordenar por:', field, order);
    // Implementar lógica de ordenamiento
}

function exportProducts() {
    if (!window.userPermissions.exportar) {
        showNotification('No tiene permisos para exportar productos', 'error');
        return;
    }
    
    window.location.href = '../servicios/exportar_productos.php';
}

function importProducts() {
    if (!window.userPermissions.importar) {
        showNotification('No tiene permisos para importar productos', 'error');
        return;
    }
    
    showNotification('Función de importación en desarrollo', 'info');
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
