/**
 * Sistema de Alertas de Stock Bajo - ARCO
 * Verifica y muestra notificaciones de productos con stock bajo
 */

class AlertasStock {
    constructor() {
        this.intervalo = 300000; // 5 minutos
        this.ultimaVerificacion = null;
        this.productosAlertados = new Set();
        this.init();
    }
    
    init() {
        // Verificar inmediatamente al cargar
        this.verificarStock();
        
        // Verificar periódicamente
        setInterval(() => this.verificarStock(), this.intervalo);
        
        // Crear contenedor de notificaciones si no existe
        if (!document.getElementById('notificaciones-container')) {
            const container = document.createElement('div');
            container.id = 'notificaciones-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
    }
    
    async verificarStock() {
        try {
            const response = await fetch('../servicios/verificar_stock_bajo.php');
            const data = await response.json();
            
            if (data.success && data.productos.length > 0) {
                this.mostrarAlertas(data.productos);
            }
            
            this.ultimaVerificacion = new Date();
        } catch (error) {
            console.error('Error al verificar stock:', error);
        }
    }
    
    mostrarAlertas(productos) {
        // Filtrar productos que no han sido alertados en esta sesión
        const nuevosProductos = productos.filter(p => !this.productosAlertados.has(p.id_productos));
        
        if (nuevosProductos.length === 0) return;
        
        // Mostrar notificación general
        this.mostrarNotificacion({
            tipo: 'warning',
            titulo: '⚠️ Alerta de Stock Bajo',
            mensaje: `${nuevosProductos.length} producto(s) con stock bajo`,
            productos: nuevosProductos,
            duracion: 10000
        });
        
        // Marcar como alertados
        nuevosProductos.forEach(p => this.productosAlertados.add(p.id_productos));
    }
    
    mostrarNotificacion({ tipo, titulo, mensaje, productos, duracion = 5000 }) {
        const container = document.getElementById('notificaciones-container');
        const notificacion = document.createElement('div');
        notificacion.className = `notificacion notificacion-${tipo}`;
        
        const iconos = {
            success: '✓',
            warning: '⚠',
            error: '✕',
            info: 'ℹ'
        };
        
        const colores = {
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444',
            info: '#3b82f6'
        };
        
        notificacion.style.cssText = `
            background: white;
            border-left: 4px solid ${colores[tipo]};
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 16px;
            margin-bottom: 12px;
            animation: slideIn 0.3s ease-out;
            max-height: 400px;
            overflow-y: auto;
        `;
        
        let productosHTML = '';
        if (productos && productos.length > 0) {
            productosHTML = `
                <div style="margin-top: 12px; font-size: 0.9rem;">
                    <strong>Productos afectados:</strong>
                    <ul style="margin: 8px 0; padding-left: 20px;">
                        ${productos.map(p => `
                            <li style="margin: 4px 0;">
                                <strong>${p.nombre}</strong>
                                <br>
                                <span style="color: #6b7280; font-size: 0.85rem;">
                                    Stock actual: ${p.stock} | Mínimo: ${p.stock_minimo}
                                    ${p.porcentaje_stock ? ` (${p.porcentaje_stock}%)` : ''}
                                </span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }
        
        notificacion.innerHTML = `
            <div style="display: flex; align-items: start; gap: 12px;">
                <div style="
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: ${colores[tipo]}20;
                    color: ${colores[tipo]};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    font-weight: bold;
                    flex-shrink: 0;
                ">
                    ${iconos[tipo]}
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                        ${titulo}
                    </div>
                    <div style="color: #6b7280; font-size: 0.9rem;">
                        ${mensaje}
                    </div>
                    ${productosHTML}
                </div>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: #9ca3af;
                    cursor: pointer;
                    font-size: 20px;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    flex-shrink: 0;
                ">×</button>
            </div>
        `;
        
        container.appendChild(notificacion);
        
        // Auto-remover después de la duración especificada
        if (duracion > 0) {
            setTimeout(() => {
                notificacion.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notificacion.remove(), 300);
            }, duracion);
        }
    }
    
    // Método público para mostrar notificaciones personalizadas
    static notificar(tipo, titulo, mensaje, duracion = 5000) {
        const alertas = new AlertasStock();
        alertas.mostrarNotificacion({ tipo, titulo, mensaje, duracion });
    }
}

// Agregar estilos de animación
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
    
    .notificacion:hover {
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }
    
    #notificaciones-container::-webkit-scrollbar {
        width: 6px;
    }
    
    #notificaciones-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    #notificaciones-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    #notificaciones-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
`;
document.head.appendChild(style);

// Inicializar automáticamente cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.alertasStock = new AlertasStock();
    });
} else {
    window.alertasStock = new AlertasStock();
}

// Exportar para uso global
window.AlertasStock = AlertasStock;