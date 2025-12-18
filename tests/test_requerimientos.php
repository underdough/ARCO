<?php
/**
 * =====================================================
 * ARCHIVO DE PRUEBAS - REQUERIMIENTOS MVP
 * =====================================================
 * Ejecutar en navegador: http://localhost/tu-proyecto/test_requerimientos.php
 * 
 * IMPORTANTE: Antes de ejecutar, asegúrate de:
 * 1. Ejecutar el SQL: base-datos/actualizar_movimientos_completo.sql
 * 2. Ejecutar el SQL: servicios/create_2fa_tables.sql (si no lo has hecho)
 * 3. Tener una sesión activa (estar logueado)
 */

session_start();
include_once "servicios/conexion.php";

// Simular sesión si no existe (SOLO PARA PRUEBAS)
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['rol'] = 'administrador';
    $_SESSION['nombre'] = 'Admin Test';
}

$conexion = ConectarDB();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Requerimientos MVP - ARCO</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; background: #f5f5f5; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #007bff; margin-top: 30px; }
        .test-section { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .test-item { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; background: #fafafa; }
        .test-item.success { border-left-color: #28a745; background: #d4edda; }
        .test-item.error { border-left-color: #dc3545; background: #f8d7da; }
        .test-item.warning { border-left-color: #ffc107; background: #fff3cd; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; border: none; border-radius: 5px; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #333; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .result { margin-top: 10px; padding: 10px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .status-badge { padding: 3px 10px; border-radius: 15px; font-size: 12px; color: white; }
        .status-ok { background: #28a745; }
        .status-fail { background: #dc3545; }
        .status-pending { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <h1>🧪 Test de Requerimientos MVP - Sistema ARCO</h1>
    
    <div class="test-section">
        <h2>📋 Estado de Tablas Requeridas</h2>
        <div id="tablas-status">
            <?php
            $tablasRequeridas = [
                'materiales' => 'Productos/Materiales',
                'movimientos' => 'Movimientos de inventario',
                'ordenes_compra' => 'Órdenes de compra',
                'orden_detalles' => 'Detalles de órdenes',
                'devoluciones' => 'Devoluciones',
                'anomalias' => 'Anomalías/Novedades',
                'historial_acciones' => 'Auditoría/Historial',
                'usuarios' => 'Usuarios',
                'empresa' => 'Datos de empresa'
            ];
            
            echo '<table>';
            echo '<tr><th>Tabla</th><th>Descripción</th><th>Estado</th><th>Registros</th></tr>';
            
            foreach ($tablasRequeridas as $tabla => $desc) {
                $existe = $conexion->query("SHOW TABLES LIKE '$tabla'")->num_rows > 0;
                $registros = 0;
                if ($existe) {
                    $res = $conexion->query("SELECT COUNT(*) as c FROM $tabla");
                    $registros = $res ? $res->fetch_assoc()['c'] : 0;
                }
                $status = $existe ? '<span class="status-badge status-ok">✓ OK</span>' : '<span class="status-badge status-fail">✗ Falta</span>';
                echo "<tr><td><code>$tabla</code></td><td>$desc</td><td>$status</td><td>$registros</td></tr>";
            }
            echo '</table>';
            ?>
        </div>
    </div>

    <div class="test-section">
        <h2>🔧 A) Movimientos actualiza Stock</h2>
        <p>Probar que los movimientos actualicen automáticamente el stock de productos.</p>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="btn-success" onclick="testMovimiento('entrada')">Test ENTRADA (+10)</button>
            <button class="btn-danger" onclick="testMovimiento('salida')">Test SALIDA (-5)</button>
            <button class="btn-warning" onclick="testMovimiento('ajuste')">Test AJUSTE (=50)</button>
            <button class="btn-info" onclick="testMovimiento('recibido')">Test RECIBIDO (+15)</button>
        </div>
        <div id="result-movimiento" class="result"></div>
    </div>

    <div class="test-section">
        <h2>📦 B) Recibidos y Devoluciones</h2>
        <p>Probar creación y procesamiento de devoluciones.</p>
        
        <button class="btn-primary" onclick="testCrearDevolucion()">Crear Devolución</button>
        <button class="btn-success" onclick="testListarDevoluciones()">Listar Devoluciones</button>
        <div id="result-devolucion" class="result"></div>
    </div>

    <div class="test-section">
        <h2>🛒 C) Órdenes de Compra</h2>
        <p>Probar CRUD de órdenes de compra.</p>
        
        <button class="btn-primary" onclick="testCrearOrden()">Crear Orden</button>
        <button class="btn-success" onclick="testListarOrdenes()">Listar Órdenes</button>
        <button class="btn-info" onclick="testVerOrden()">Ver Última Orden</button>
        <div id="result-orden" class="result"></div>
    </div>

    <div class="test-section">
        <h2>🖨️ D) Comprobantes Imprimibles</h2>
        <p>Abrir comprobantes en nueva ventana.</p>
        
        <button class="btn-primary" onclick="abrirComprobante('movimiento')">Comprobante Movimiento</button>
        <button class="btn-success" onclick="abrirComprobante('orden')">Comprobante OC</button>
        <button class="btn-warning" onclick="abrirComprobante('devolucion')">Comprobante Devolución</button>
        <div id="result-comprobante" class="result"></div>
    </div>

    <div class="test-section">
        <h2>📊 E) Auditoría Global</h2>
        <p>Probar endpoints de auditoría (solo admin).</p>
        
        <button class="btn-primary" onclick="testAuditoria('listar')">Listar Historial</button>
        <button class="btn-info" onclick="testAuditoria('resumen')">Ver Resumen</button>
        <button class="btn-success" onclick="window.open('servicios/auditoria.php?accion=exportar', '_blank')">Exportar CSV</button>
        <div id="result-auditoria" class="result"></div>
    </div>

    <div class="test-section">
        <h2>⚠️ G) Anomalías/Novedades</h2>
        <p><em>Pendiente - Se implementará después con el formulario del compañero.</em></p>
        <div class="test-item warning">
            La tabla <code>anomalias</code> ya existe en la BD. El endpoint <code>servicios/anomalias.php</code> está listo.
            Falta integrar el formulario del frontend.
        </div>
    </div>

    <div class="test-section">
        <h2>🔐 F) Interfaces por Rol/Permisos</h2>
        <p>El menú y botones se muestran según los permisos del usuario.</p>
        
        <div class="test-item success">
            <strong>Implementado:</strong> Las vistas ahora usan el sistema de permisos dinámico.
            <ul>
                <li>Menú lateral generado según permisos del rol</li>
                <li>Botones de acción (Crear, Editar, Eliminar) habilitados/deshabilitados según permisos</li>
                <li>Variable JS <code>window.PERMISOS_USUARIO</code> disponible para control en frontend</li>
            </ul>
        </div>
        
        <p><strong>Probar con diferentes roles:</strong></p>
        <ul>
            <li><strong>Administrador:</strong> Ve todos los módulos y todas las acciones</li>
            <li><strong>Almacenista:</strong> Ve módulos de inventario, puede crear movimientos</li>
            <li><strong>Supervisor:</strong> Ve módulos, puede aprobar pero no crear</li>
            <li><strong>Usuario:</strong> Solo puede ver, sin acciones de modificación</li>
        </ul>
    </div>

    <div class="test-section">
        <h2>📁 Archivos Creados/Modificados</h2>
        <table>
            <tr><th>Archivo</th><th>Descripción</th><th>Requerimiento</th></tr>
            <tr><td><code>servicios/guardar_movimiento.php</code></td><td>Movimientos con actualización de stock</td><td>A, B</td></tr>
            <tr><td><code>servicios/ordenes_compra.php</code></td><td>API de órdenes de compra</td><td>C</td></tr>
            <tr><td><code>servicios/devoluciones.php</code></td><td>API de devoluciones</td><td>B</td></tr>
            <tr><td><code>servicios/auditoria.php</code></td><td>API de auditoría global</td><td>E</td></tr>
            <tr><td><code>servicios/imprimir_movimiento.php</code></td><td>Comprobante de movimiento mejorado</td><td>D</td></tr>
            <tr><td><code>servicios/imprimir_orden_compra.php</code></td><td>Comprobante de OC</td><td>D</td></tr>
            <tr><td><code>servicios/imprimir_devolucion.php</code></td><td>Comprobante de devolución</td><td>D</td></tr>
            <tr><td><code>servicios/menu_dinamico.php</code></td><td>Generador de menú según permisos</td><td>F</td></tr>
            <tr><td><code>base-datos/actualizar_movimientos_completo.sql</code></td><td>SQL para actualizar tabla movimientos</td><td>A, B</td></tr>
            <tr><td><code>vistas/*.php</code></td><td>Vistas actualizadas con sistema de permisos</td><td>F</td></tr>
            <tr><td><code>vistas/ordenes_compra.php</code></td><td>Vista completa de Órdenes de Compra</td><td>C</td></tr>
            <tr><td><code>vistas/devoluciones.php</code></td><td>Vista completa de Devoluciones</td><td>B</td></tr>
            <tr><td><code>SOLOjavascript/productos.js</code></td><td>Movimientos rápidos desde productos</td><td>A</td></tr>
        </table>
        
        <h3>🔗 Enlaces Rápidos para Probar</h3>
        <p>
            <a href="vistas/productos.php" target="_blank" class="btn-primary" style="padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;">Productos</a>
            <a href="vistas/movimientos.php" target="_blank" class="btn-primary" style="padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px;">Movimientos</a>
            <a href="vistas/ordenes_compra.php" target="_blank" class="btn-success" style="padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px; background: #28a745; color: white;">Órdenes de Compra</a>
            <a href="vistas/devoluciones.php" target="_blank" class="btn-warning" style="padding: 10px 15px; text-decoration: none; border-radius: 5px; margin: 5px; background: #ffc107; color: #333;">Devoluciones</a>
        </p>
    </div>

    <script>
        // Obtener primer producto disponible
        let productoId = null;
        let ultimaOrdenId = null;
        let ultimaDevolucionId = null;
        
        fetch('servicios/obtener_productos.php')
            .then(r => r.json())
            .then(data => {
                if (data.length > 0) {
                    productoId = data[0].id;
                    console.log('Producto para tests:', productoId);
                }
            });

        function mostrarResultado(elementId, data, isError = false) {
            const el = document.getElementById(elementId);
            el.innerHTML = `<pre style="background: ${isError ? '#f8d7da' : '#d4edda'}">${JSON.stringify(data, null, 2)}</pre>`;
        }

        function testMovimiento(tipo) {
            if (!productoId) {
                alert('No hay productos disponibles para probar');
                return;
            }
            
            const cantidades = { entrada: 10, salida: 5, ajuste: 50, recibido: 15 };
            const formData = new FormData();
            formData.append('tipo', tipo);
            formData.append('fecha', new Date().toISOString().split('T')[0]);
            formData.append('producto', productoId);
            formData.append('cantidad', cantidades[tipo]);
            formData.append('notas', 'Test desde archivo de pruebas');
            
            fetch('servicios/guardar_movimiento.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => mostrarResultado('result-movimiento', data, data.status === 'error'))
                .catch(e => mostrarResultado('result-movimiento', {error: e.message}, true));
        }

        function testCrearDevolucion() {
            if (!productoId) {
                alert('No hay productos disponibles');
                return;
            }
            
            const formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('material_id', productoId);
            formData.append('cantidad', 2);
            formData.append('motivo', 'defectuoso');
            formData.append('descripcion', 'Test de devolución desde archivo de pruebas');
            
            fetch('servicios/devoluciones.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.devolucion_id) ultimaDevolucionId = data.devolucion_id;
                    mostrarResultado('result-devolucion', data, data.status === 'error');
                })
                .catch(e => mostrarResultado('result-devolucion', {error: e.message}, true));
        }

        function testListarDevoluciones() {
            fetch('servicios/devoluciones.php?accion=listar')
                .then(r => r.json())
                .then(data => mostrarResultado('result-devolucion', data, data.status === 'error'))
                .catch(e => mostrarResultado('result-devolucion', {error: e.message}, true));
        }

        function testCrearOrden() {
            if (!productoId) {
                alert('No hay productos disponibles');
                return;
            }
            
            const items = JSON.stringify([
                { material_id: productoId, cantidad: 100, precio_unitario: 25.50 }
            ]);
            
            const formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('proveedor', 'Proveedor Test');
            formData.append('fecha_esperada', new Date(Date.now() + 7*24*60*60*1000).toISOString().split('T')[0]);
            formData.append('items', items);
            formData.append('notas', 'Orden de prueba');
            
            fetch('servicios/ordenes_compra.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.orden_id) ultimaOrdenId = data.orden_id;
                    mostrarResultado('result-orden', data, data.status === 'error');
                })
                .catch(e => mostrarResultado('result-orden', {error: e.message}, true));
        }

        function testListarOrdenes() {
            fetch('servicios/ordenes_compra.php?accion=listar')
                .then(r => r.json())
                .then(data => {
                    if (data.data && data.data.length > 0) {
                        ultimaOrdenId = data.data[0].id;
                    }
                    mostrarResultado('result-orden', data, data.status === 'error');
                })
                .catch(e => mostrarResultado('result-orden', {error: e.message}, true));
        }

        function testVerOrden() {
            if (!ultimaOrdenId) {
                alert('Primero lista las órdenes o crea una');
                return;
            }
            fetch(`servicios/ordenes_compra.php?accion=ver&id=${ultimaOrdenId}`)
                .then(r => r.json())
                .then(data => mostrarResultado('result-orden', data, data.status === 'error'))
                .catch(e => mostrarResultado('result-orden', {error: e.message}, true));
        }

        function abrirComprobante(tipo) {
            let url = '';
            switch(tipo) {
                case 'movimiento':
                    url = 'servicios/imprimir_movimiento.php?id=1';
                    break;
                case 'orden':
                    url = ultimaOrdenId ? `servicios/imprimir_orden_compra.php?id=${ultimaOrdenId}` : 'servicios/imprimir_orden_compra.php?id=1';
                    break;
                case 'devolucion':
                    url = ultimaDevolucionId ? `servicios/imprimir_devolucion.php?id=${ultimaDevolucionId}` : 'servicios/imprimir_devolucion.php?id=1';
                    break;
            }
            window.open(url, '_blank');
            document.getElementById('result-comprobante').innerHTML = `<div class="test-item success">Abriendo: ${url}</div>`;
        }

        function testAuditoria(accion) {
            fetch(`servicios/auditoria.php?accion=${accion}&limite=10`)
                .then(r => r.json())
                .then(data => mostrarResultado('result-auditoria', data, data.status === 'error'))
                .catch(e => mostrarResultado('result-auditoria', {error: e.message}, true));
        }

        function testCrearAnomalia() {
            const formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('titulo', 'Anomalía de prueba');
            formData.append('descripcion', 'Descripción de la anomalía detectada durante pruebas');
            formData.append('tipo', 'discrepancia');
            formData.append('prioridad', 'media');
            if (productoId) formData.append('material_id', productoId);
            
            fetch('servicios/anomalias.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => mostrarResultado('result-anomalia', data, data.status === 'error'))
                .catch(e => mostrarResultado('result-anomalia', {error: e.message}, true));
        }

        function testListarAnomalias() {
            fetch('servicios/anomalias.php?accion=listar')
                .then(r => r.json())
                .then(data => mostrarResultado('result-anomalia', data, data.status === 'error'))
                .catch(e => mostrarResultado('result-anomalia', {error: e.message}, true));
        }
    </script>
</body>
</html>
