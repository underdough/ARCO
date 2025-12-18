<?php
/**
 * VERIFICACIÓN RÁPIDA DEL SISTEMA MVP
 * Ejecutar: http://localhost/tu-proyecto/verificar_sistema.php
 */
session_start();
include_once "servicios/conexion.php";

// Simular sesión si no existe
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['rol'] = 'administrador';
}

$conexion = ConectarDB();
$errores = [];
$exitos = [];

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Verificación Sistema</title>";
echo "<style>body{font-family:Arial;padding:20px;max-width:900px;margin:0 auto}";
echo ".ok{color:green;background:#d4edda;padding:10px;margin:5px 0;border-radius:5px}";
echo ".error{color:red;background:#f8d7da;padding:10px;margin:5px 0;border-radius:5px}";
echo ".warn{color:#856404;background:#fff3cd;padding:10px;margin:5px 0;border-radius:5px}";
echo "h2{border-bottom:2px solid #007bff;padding-bottom:10px}</style></head><body>";
echo "<h1>🔍 Verificación del Sistema MVP - ARCO</h1>";

// 1. Verificar tablas
echo "<h2>1. Tablas de Base de Datos</h2>";
$tablas = ['materiales', 'movimientos', 'ordenes_compra', 'orden_detalles', 'devoluciones', 'historial_acciones', 'usuarios', 'modulos', 'permisos', 'rol_permisos'];

foreach ($tablas as $tabla) {
    $existe = $conexion->query("SHOW TABLES LIKE '$tabla'")->num_rows > 0;
    if ($existe) {
        $count = $conexion->query("SELECT COUNT(*) as c FROM $tabla")->fetch_assoc()['c'];
        echo "<div class='ok'>✅ <strong>$tabla</strong> - OK ($count registros)</div>";
    } else {
        echo "<div class='error'>❌ <strong>$tabla</strong> - NO EXISTE</div>";
        $errores[] = "Tabla $tabla no existe";
    }
}

// 2. Verificar estructura de movimientos
echo "<h2>2. Estructura de Movimientos</h2>";
$columnas = $conexion->query("SHOW COLUMNS FROM movimientos");
$colsRequeridas = ['id', 'tipo', 'fecha', 'producto_id', 'cantidad', 'usuario_id', 'notas', 'orden_compra_id', 'devolucion_id'];
$colsExistentes = [];

while ($col = $columnas->fetch_assoc()) {
    $colsExistentes[] = $col['Field'];
}

foreach ($colsRequeridas as $col) {
    if (in_array($col, $colsExistentes)) {
        echo "<div class='ok'>✅ Columna <strong>$col</strong> existe</div>";
    } else {
        echo "<div class='error'>❌ Columna <strong>$col</strong> FALTA</div>";
        $errores[] = "Columna $col falta en movimientos";
    }
}

// Verificar AUTO_INCREMENT
$autoInc = $conexion->query("SHOW COLUMNS FROM movimientos WHERE Field = 'id'")->fetch_assoc();
if (strpos($autoInc['Extra'], 'auto_increment') !== false) {
    echo "<div class='ok'>✅ Campo ID tiene AUTO_INCREMENT</div>";
} else {
    echo "<div class='error'>❌ Campo ID NO tiene AUTO_INCREMENT</div>";
    $errores[] = "Campo id no tiene AUTO_INCREMENT";
}

// 3. Verificar servicios
echo "<h2>3. Archivos de Servicios</h2>";
$servicios = [
    'servicios/guardar_movimiento.php',
    'servicios/filtrar_movimientos.php',
    'servicios/obtener_productos.php',
    'servicios/ordenes_compra.php',
    'servicios/devoluciones.php',
    'servicios/auditoria.php',
    'servicios/menu_dinamico.php',
    'servicios/imprimir_movimiento.php',
    'servicios/imprimir_orden_compra.php',
    'servicios/imprimir_devolucion.php'
];

foreach ($servicios as $servicio) {
    if (file_exists($servicio)) {
        echo "<div class='ok'>✅ $servicio</div>";
    } else {
        echo "<div class='error'>❌ $servicio - NO EXISTE</div>";
        $errores[] = "Archivo $servicio no existe";
    }
}

// 4. Verificar vistas
echo "<h2>4. Vistas del Sistema</h2>";
$vistas = [
    'vistas/movimientos.php',
    'vistas/productos.php',
    'vistas/ordenes_compra.php',
    'vistas/devoluciones.php',
    'vistas/dashboard.php',
    'vistas/reportes.php'
];

foreach ($vistas as $vista) {
    if (file_exists($vista)) {
        echo "<div class='ok'>✅ $vista</div>";
    } else {
        echo "<div class='error'>❌ $vista - NO EXISTE</div>";
        $errores[] = "Vista $vista no existe";
    }
}

// 5. Verificar módulos en permisos
echo "<h2>5. Módulos Registrados</h2>";
$modulos = $conexion->query("SELECT nombre, descripcion, activo FROM modulos ORDER BY orden");
if ($modulos && $modulos->num_rows > 0) {
    while ($mod = $modulos->fetch_assoc()) {
        $estado = $mod['activo'] ? '✅' : '⚠️';
        echo "<div class='ok'>$estado <strong>{$mod['nombre']}</strong> - {$mod['descripcion']}</div>";
    }
} else {
    echo "<div class='warn'>⚠️ No hay módulos registrados. Ejecuta el SQL de permisos.</div>";
}

// Resumen
echo "<h2>📊 Resumen</h2>";
if (empty($errores)) {
    echo "<div class='ok' style='font-size:1.2em'>✅ <strong>Sistema configurado correctamente</strong></div>";
    echo "<p>Puedes acceder a:</p><ul>";
    echo "<li><a href='vistas/movimientos.php'>Movimientos</a></li>";
    echo "<li><a href='vistas/productos.php'>Productos</a></li>";
    echo "<li><a href='vistas/ordenes_compra.php'>Órdenes de Compra</a></li>";
    echo "<li><a href='vistas/devoluciones.php'>Devoluciones</a></li>";
    echo "<li><a href='test_requerimientos.php'>Test de Requerimientos</a></li>";
    echo "</ul>";
} else {
    echo "<div class='error' style='font-size:1.2em'>❌ <strong>Se encontraron " . count($errores) . " errores</strong></div>";
    echo "<p><strong>Solución:</strong> Ejecuta el archivo SQL:</p>";
    echo "<pre>base-datos/instalar_mvp_completo.sql</pre>";
    echo "<p>En phpMyAdmin o tu cliente MySQL.</p>";
}

echo "</body></html>";
$conexion->close();
?>
