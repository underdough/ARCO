<?php
session_start();
require_once 'conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Sesión no válida. Por favor, inicie sesión nuevamente.',
        'redirect' => '../login.html'
    ]);
    exit;
}

$conexion = ConectarDB();

if (!$conexion) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

try {
    // Obtener total de productos activos
    $sql_productos = "SELECT COUNT(*) as total FROM materiales WHERE disponibilidad = 1";
    $resultado_productos = $conexion->query($sql_productos);
    $total_productos = $resultado_productos ? $resultado_productos->fetch_assoc()['total'] : 0;

    // Obtener total de categorías activas
    $sql_categorias = "SELECT COUNT(*) as total FROM categorias WHERE estado = 1";
    $resultado_categorias = $conexion->query($sql_categorias);
    $total_categorias = $resultado_categorias ? $resultado_categorias->fetch_assoc()['total'] : 0;

    // Obtener movimientos de hoy
    $sql_movimientos_hoy = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE()";
    $resultado_movimientos = $conexion->query($sql_movimientos_hoy);
    $movimientos_hoy = $resultado_movimientos ? $resultado_movimientos->fetch_assoc()['total'] : 0;

    // Obtener entradas de hoy
    $sql_entradas = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE() AND tipo = 'entrada'";
    $resultado_entradas = $conexion->query($sql_entradas);
    $entradas_hoy = $resultado_entradas ? $resultado_entradas->fetch_assoc()['total'] : 0;

    // Obtener salidas de hoy
    $sql_salidas = "SELECT COUNT(*) as total FROM movimientos WHERE DATE(creado_en) = CURDATE() AND tipo = 'salida'";
    $resultado_salidas = $conexion->query($sql_salidas);
    $salidas_hoy = $resultado_salidas ? $resultado_salidas->fetch_assoc()['total'] : 0;

    // Obtener productos con stock bajo
    $sql_alertas = "SELECT COUNT(*) as total FROM materiales WHERE stock <= 5 AND stock > 0 AND disponibilidad = 1";
    $resultado_alertas = $conexion->query($sql_alertas);
    $total_alertas = $resultado_alertas ? $resultado_alertas->fetch_assoc()['total'] : 0;

    // Obtener productos agotados
    $sql_agotados = "SELECT COUNT(*) as total FROM materiales WHERE stock = 0 AND disponibilidad = 1";
    $resultado_agotados = $conexion->query($sql_agotados);
    $productos_agotados = $resultado_agotados ? $resultado_agotados->fetch_assoc()['total'] : 0;

    // Obtener actividad reciente con manejo de errores
    $sql_actividad = "
        SELECT 
            m.tipo,
            m.cantidad,
            COALESCE(mat.nombre_material, 'Producto no encontrado') AS producto,
            m.creado_en,
            COALESCE(CONCAT(u.nombre, ' ', u.apellido), 'Usuario Sistema') AS usuario,
            COALESCE(m.notas, '') AS notas
        FROM movimientos m
        LEFT JOIN materiales mat ON m.producto_id = mat.id_material
        LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
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

    // Calcular métricas adicionales con valores por defecto
    $porcentaje_productos = 5; // Valor por defecto
    $nuevas_categorias = 0;

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

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    $conexion->close();
    exit;

} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en dashboard: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Error al cargar datos del dashboard: ' . $e->getMessage(),
        'debug_info' => [
            'message' => $e->getMessage(),
            'file' => basename(__FILE__),
            'line' => $e->getLine()
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Función auxiliar mejorada
function calcularTiempoTranscurrido($fecha) {
    try {
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
    } catch (Exception $e) {
        return "Tiempo no disponible";
    }
}
?>
