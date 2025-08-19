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

    // Obtener entradas de hoy
    $sql_entradas = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE() AND tipo = 'entrada'";
    $resultado_entradas = $conexion->query($sql_entradas);
    $entradas_hoy = $resultado_entradas->fetch_assoc()['total'];

    // Obtener salidas de hoy
    $sql_salidas = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE() AND tipo = 'salida'";
    $resultado_salidas = $conexion->query($sql_salidas);
    $salidas_hoy = $resultado_salidas->fetch_assoc()['total'];

    // Obtener productos con stock bajo (asumiendo que existe una columna minimo_alarma)
    $sql_alertas = "SELECT COUNT(*) as total FROM materiales WHERE stock <= 5 AND stock > 0";
    $resultado_alertas = $conexion->query($sql_alertas);
    $total_alertas = $resultado_alertas->fetch_assoc()['total'];

    // Obtener productos agotados
    $sql_agotados = "SELECT COUNT(*) as total FROM materiales WHERE stock = 0";
    $resultado_agotados = $conexion->query($sql_agotados);
    $productos_agotados = $resultado_agotados->fetch_assoc()['total'];

    // Obtener actividad reciente SOLO de movimientos (sin historial_acciones)
    $sql_actividad = "
        SELECT 
            m.tipo,
            m.cantidad,
            mat.nombre_material AS producto,
            m.creado_en,
            CASE 
                WHEN m.usuario_id = 2 THEN 'Juan Pérez'
                WHEN m.usuario_id = 4 THEN 'María López'
                ELSE 'Usuario Sistema'
            END AS usuario,
            m.notas
        FROM movimientos m
        LEFT JOIN materiales mat ON m.producto_id = mat.id_material
        WHERE mat.id_material IS NOT NULL
        ORDER BY m.creado_en DESC
        LIMIT 5
    ";

    $resultado_actividad = $conexion->query($sql_actividad);
    $actividad_reciente = [];

    if ($resultado_actividad && $resultado_actividad->num_rows > 0) {
        while ($fila = $resultado_actividad->fetch_assoc()) {
            $tiempo_transcurrido = calcularTiempoTranscurrido($fila['creado_en']);
            $actividad_reciente[] = [
                'tipo' => $fila['tipo'],
                'cantidad' => $fila['cantidad'],
                'producto' => $fila['producto'],
                'tiempo' => $tiempo_transcurrido,
                'usuario' => $fila['usuario'],
                'notas' => $fila['notas']
            ];
        }
    }

    // Si no hay actividad reciente, crear un mensaje por defecto
    if (empty($actividad_reciente)) {
        $actividad_reciente[] = [
            'tipo' => 'info',
            'cantidad' => '',
            'producto' => 'No hay movimientos registrados',
            'tiempo' => 'Sin actividad',
            'usuario' => 'Sistema',
            'notas' => ''
        ];
    }

    // Datos simulados (puedes ajustar si tienes métricas reales)
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
    $conexion->close();
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ]);
    exit;
}

// Función auxiliar
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
