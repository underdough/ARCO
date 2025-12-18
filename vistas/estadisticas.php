<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}

require_once '../servicios/conexion.php';
$conexion = ConectarDB();

require_once '../servicios/middleware_permisos.php';
require_once '../servicios/menu_dinamico.php';

// Verificar que el usuario tenga rol autorizado
$rolesAutorizados = ['administrador', 'gerente', 'supervisor'];
if (!in_array($_SESSION['rol'], $rolesAutorizados)) {
    header("Location: dashboard.php?error=No tiene permisos para acceder a estadísticas");
    exit;
}

$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
$rol = $_SESSION['rol'] ?? '';

// Mapeo de roles a etiquetas legibles
$rolesLegibles = [
    'administrador' => 'Administrador',
    'gerente' => 'Gerente',
    'supervisor' => 'Supervisor',
    'almacenista' => 'Almacenista',
    'funcionario' => 'Funcionario'
];
$rolLegible = $rolesLegibles[$rol] ?? ucfirst($rol);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCO - Estadísticas</title>
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/reportes.css">
    <link rel="stylesheet" href="../componentes/estadisticas.css">
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php echo generarSidebarCompleto('estadisticas'); ?>
    
    <div class="main-content">
        <div class="header">
            <h2><i class="fas fa-chart-bar"></i> Estadísticas del Sistema</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <div style="display: flex; flex-direction: column; gap: 2px; flex-wrap:wrap;">
                    <span>Hola, <?php echo htmlspecialchars($nombreCompleto); ?></span>
                    <span style="font-size: 12px; color: rgba(255,255,255,0.7);">Rol: <strong><?php echo htmlspecialchars($rolLegible); ?></strong></span>
                </div>
            </div>
        </div>
        
        <!-- Tarjetas de estadísticas principales -->
        <div class="stats-grid" id="statsGrid">
            <div class="loading">
                <i class="fas fa-spinner"></i>
                <p>Cargando estadísticas...</p>
            </div>
        </div>
        
        <!-- Gráfico de movimientos por mes -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Movimientos por Mes</h3>
                <div class="filter-group">
                    <select class="filter-select" id="yearFilter">
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                </div>
            </div>
            <canvas id="movimientosChart"></canvas>
        </div>
        
        <!-- Gráfico de productos por categoría -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Productos por Categoría</h3>
            </div>
            <canvas id="categoriasChart"></canvas>
        </div>
        
        <!-- Gráfico de stock por categoría -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Stock Total por Categoría</h3>
            </div>
            <canvas id="stockChart"></canvas>
        </div>
        
        <!-- Gráfico de movimientos por tipo -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Movimientos por Tipo</h3>
                <div class="filter-group">
                    <select class="filter-select" id="periodoFilter">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="90">Últimos 90 días</option>
                    </select>
                </div>
            </div>
            <canvas id="tiposChart"></canvas>
        </div>
    </div>
    
    <script>
        // Configuración de colores del sistema
        const colors = {
            primary: '#395886',
            secondary: '#638ECB',
            tertiary: '#8AAEE0',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6'
        };
        
        let charts = {};
        
        // Cargar estadísticas principales
        async function cargarEstadisticas() {
            try {
                console.log('Cargando estadísticas...');
                const response = await fetch('../servicios/estadisticas_data.php?tipo=resumen');
                console.log('Response status:', response.status);
                
                const data = await response.json();
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    renderizarEstadisticas(data.data);
                } else {
                    console.error('Error en respuesta:', data.error);
                    mostrarError('No se pudieron cargar las estadísticas');
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
                mostrarError('Error de conexión al cargar estadísticas');
            }
        }
        
        function mostrarError(mensaje) {
            const container = document.getElementById('statsGrid');
            container.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">${mensaje}</p>
                    <button onclick="cargarEstadisticas()" style="margin-top: 15px; padding: 10px 20px; background: #395886; color: white; border: none; border-radius: 8px; cursor: pointer;">
                        <i class="fas fa-sync-alt"></i> Reintentar
                    </button>
                </div>
            `;
        }
        
        function renderizarEstadisticas(stats) {
            const container = document.getElementById('statsGrid');
            container.innerHTML = `
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Total Productos</span>
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="stat-value">${stats.total_productos || 0}</div>
                    <div class="stat-change ${stats.cambio_productos >= 0 ? 'positive' : 'negative'}">
                        <i class="fas fa-arrow-${stats.cambio_productos >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.cambio_productos || 0)}% vs mes anterior
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Movimientos del Mes</span>
                        <div class="stat-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value">${stats.movimientos_mes || 0}</div>
                    <div class="stat-change ${stats.cambio_movimientos >= 0 ? 'positive' : 'negative'}">
                        <i class="fas fa-arrow-${stats.cambio_movimientos >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.cambio_movimientos || 0)}% vs mes anterior
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Stock Total</span>
                        <div class="stat-icon">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
                    <div class="stat-value">${stats.stock_total || 0}</div>
                    <div class="stat-change ${stats.cambio_stock >= 0 ? 'positive' : 'negative'}">
                        <i class="fas fa-arrow-${stats.cambio_stock >= 0 ? 'up' : 'down'}"></i>
                        ${Math.abs(stats.cambio_stock || 0)}% vs mes anterior
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-title">Alertas de Stock</span>
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-value">${stats.alertas_stock || 0}</div>
                    <div class="stat-change ${stats.alertas_stock > 0 ? 'negative' : 'positive'}">
                        <i class="fas fa-${stats.alertas_stock > 0 ? 'exclamation-circle' : 'check-circle'}"></i>
                        ${stats.alertas_stock > 0 ? 'Requiere atención' : 'Todo en orden'}
                    </div>
                </div>
            `;
        }
        
        // Cargar gráfico de movimientos por mes
        async function cargarMovimientosPorMes(year = 2025) {
            try {
                console.log('Cargando movimientos por mes, año:', year);
                const response = await fetch(`../servicios/estadisticas_data.php?tipo=movimientos_mes&year=${year}`);
                const data = await response.json();
                console.log('Movimientos por mes:', data);
                
                if (data.success) {
                    renderizarGraficoMovimientos(data.data);
                } else {
                    console.error('Error en movimientos:', data.error);
                }
            } catch (error) {
                console.error('Error cargando movimientos:', error);
            }
        }
        
        function renderizarGraficoMovimientos(data) {
            const ctx = document.getElementById('movimientosChart').getContext('2d');
            
            if (charts.movimientos) {
                charts.movimientos.destroy();
            }
            
            charts.movimientos = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    datasets: [{
                        label: 'Entradas',
                        data: data.entradas || [],
                        borderColor: colors.success,
                        backgroundColor: colors.success + '20',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Salidas',
                        data: data.salidas || [],
                        borderColor: colors.danger,
                        backgroundColor: colors.danger + '20',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Cargar gráfico de categorías
        async function cargarCategoriasChart() {
            try {
                console.log('Cargando productos por categoría...');
                const response = await fetch('../servicios/estadisticas_data.php?tipo=categorias');
                const data = await response.json();
                console.log('Categorías:', data);
                
                if (data.success) {
                    renderizarGraficoCategorias(data.data);
                } else {
                    console.error('Error en categorías:', data.error);
                }
            } catch (error) {
                console.error('Error cargando categorías:', error);
            }
        }
        
        function renderizarGraficoCategorias(data) {
            const ctx = document.getElementById('categoriasChart').getContext('2d');
            
            if (charts.categorias) {
                charts.categorias.destroy();
            }
            
            charts.categorias = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.values || [],
                        backgroundColor: [
                            colors.primary,
                            colors.secondary,
                            colors.tertiary,
                            colors.success,
                            colors.warning,
                            colors.info
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });
        }
        
        // Cargar gráfico de stock
        async function cargarStockChart() {
            try {
                console.log('Cargando stock por categoría...');
                const response = await fetch('../servicios/estadisticas_data.php?tipo=stock_categorias');
                const data = await response.json();
                console.log('Stock por categoría:', data);
                
                if (data.success) {
                    renderizarGraficoStock(data.data);
                } else {
                    console.error('Error en stock:', data.error);
                }
            } catch (error) {
                console.error('Error cargando stock:', error);
            }
        }
        
        function renderizarGraficoStock(data) {
            const ctx = document.getElementById('stockChart').getContext('2d');
            
            if (charts.stock) {
                charts.stock.destroy();
            }
            
            charts.stock = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Stock Total',
                        data: data.values || [],
                        backgroundColor: colors.primary,
                        borderColor: colors.secondary,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Cargar gráfico de tipos de movimiento
        async function cargarTiposChart(dias = 30) {
            try {
                console.log('Cargando tipos de movimiento, días:', dias);
                const response = await fetch(`../servicios/estadisticas_data.php?tipo=tipos_movimiento&dias=${dias}`);
                const data = await response.json();
                console.log('Tipos de movimiento:', data);
                
                if (data.success) {
                    renderizarGraficoTipos(data.data);
                } else {
                    console.error('Error en tipos:', data.error);
                }
            } catch (error) {
                console.error('Error cargando tipos:', error);
            }
        }
        
        function renderizarGraficoTipos(data) {
            const ctx = document.getElementById('tiposChart').getContext('2d');
            
            if (charts.tipos) {
                charts.tipos.destroy();
            }
            
            charts.tipos = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.values || [],
                        backgroundColor: [
                            colors.success,
                            colors.danger,
                            colors.warning,
                            colors.info
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
        
        // Event listeners
        document.getElementById('yearFilter').addEventListener('change', (e) => {
            cargarMovimientosPorMes(e.target.value);
        });
        
        document.getElementById('periodoFilter').addEventListener('change', (e) => {
            cargarTiposChart(e.target.value);
        });
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', () => {
            cargarEstadisticas();
            cargarMovimientosPorMes();
            cargarCategoriasChart();
            cargarStockChart();
            cargarTiposChart();
        });
    </script>
</body>
</html>
