<?php
require_once 'conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$conexion = ConectarDB();

try {
    // Obtener total de productos
    $sql_productos = "SELECT COUNT(*) as total FROM materiales";
    $resultado_productos = $conexion->query($sql_productos);
    $total_productos = $resultado_productos->fetch_assoc()['total'];
    
    // Obtener total de categorías
    $sql_categorias = "SELECT COUNT(*) as total FROM categorias";
    $resultado_categorias = $conexion->query($sql_categorias);
    $total_categorias = $resultado_categorias->fetch_assoc()['total'];
    
    // Obtener movimientos de hoy
    $sql_movimientos_hoy = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE()";
    $resultado_movimientos = $conexion->query($sql_movimientos_hoy);
    $movimientos_hoy = $resultado_movimientos->fetch_assoc()['total'];
    
    // Obtener entradas y salidas de hoy
    $sql_entradas = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE() AND tipo = 'entrada'";
    $resultado_entradas = $conexion->query($sql_entradas);
    $entradas_hoy = $resultado_entradas->fetch_assoc()['total'];
    
    $sql_salidas = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE() AND tipo = 'salida'";
    $resultado_salidas = $conexion->query($sql_salidas);
    $salidas_hoy = $resultado_salidas->fetch_assoc()['total'];
    
    // Obtener productos con stock bajo (alertas)
    $sql_alertas = "SELECT COUNT(*) as total FROM materiales WHERE stock <= minimo_alarma AND stock > 0";
    $resultado_alertas = $conexion->query($sql_alertas);
    $total_alertas = $resultado_alertas->fetch_assoc()['total'];
    
    // Obtener productos agotados
    $sql_agotados = "SELECT COUNT(*) as total FROM materiales WHERE stock = 0";
    $resultado_agotados = $conexion->query($sql_agotados);
    $productos_agotados = $resultado_agotados->fetch_assoc()['total'];
    
    // Obtener actividad reciente (últimos 5 movimientos)
    $sql_actividad = "SELECT 
        m.tipo,
        m.cantidad,
        mat.nombre_material,
        m.creado_en,
        m.notas,
        CASE 
            WHEN m.usuario_id = 2 THEN 'Juan Pérez'
            WHEN m.usuario_id = 4 THEN 'María López'
            ELSE 'Usuario Sistema'
        END as usuario
        FROM movimientos m 
        LEFT JOIN materiales mat ON m.producto_id = mat.id_material 
        ORDER BY m.creado_en DESC 
        LIMIT 5";
    
    $resultado_actividad = $conexion->query($sql_actividad);
    $actividad_reciente = [];
    
    while ($fila = $resultado_actividad->fetch_assoc()) {
        $tiempo_transcurrido = calcularTiempoTranscurrido($fila['creado_en']);
        
        $actividad_reciente[] = [
            'tipo' => $fila['tipo'],
            'cantidad' => $fila['cantidad'],
            'producto' => $fila['nombre_material'],
            'tiempo' => $tiempo_transcurrido,
            'usuario' => $fila['usuario'],
            'notas' => $fila['notas']
        ];
    }
    
    // Calcular porcentaje de cambio (simulado por ahora)
    $porcentaje_productos = rand(5, 20);
    $nuevas_categorias = rand(1, 5);
    
    $respuesta = [
        'success' => true,
        'data' => [
            'total_productos' => (int)$total_productos,
            'total_categorias' => (int)$total_categorias,
            'movimientos_hoy' => (int)$movimientos_hoy,
            'entradas_hoy' => (int)$entradas_hoy,
            'salidas_hoy' => (int)$salidas_hoy,
            'total_alertas' => (int)$total_alertas,
            'productos_agotados' => (int)$productos_agotados,
            'porcentaje_productos' => $porcentaje_productos,
            'nuevas_categorias' => $nuevas_categorias,
            'actividad_reciente' => $actividad_reciente
        ]
    ];
    
    echo json_encode($respuesta);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();

function calcularTiempoTranscurrido($fecha) {
    $ahora = new DateTime();
    $fecha_movimiento = new DateTime($fecha);
    $diferencia = $ahora->diff($fecha_movimiento);
    
    if ($diferencia->days > 0) {
        return "Hace " . $diferencia->days . " día" . ($diferencia->days > 1 ? "s" : "");
    } elseif ($diferencia->h > 0) {
        return "Hace " . $diferencia->h . " hora" . ($diferencia->h > 1 ? "s" : "");
    } elseif ($diferencia->i > 0) {
        return "Hace " . $diferencia->i . " minuto" . ($diferencia->i > 1 ? "s" : "");
    } else {
        return "Hace unos segundos";
    }
}
?>