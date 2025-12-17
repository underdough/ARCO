<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

// Verificar que se proporcione el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID de anomalía no proporcionado'
    ]);
    exit;
}

try {
    // Incluir configuración de base de datos
    require_once 'conexion.php';
    
    // Crear conexión
    $conn = ConectarDB();
    
    // Configurar charset
    $conn->set_charset("utf8mb4");
    
    $id = intval($_GET['id']);
    
    // Verificar si existen los campos adicionales
    $check_columns = $conn->query("SHOW COLUMNS FROM anomalias LIKE 'impacto'");
    $tiene_impacto = $check_columns && $check_columns->num_rows > 0;
    
    $check_codigo = $conn->query("SHOW COLUMNS FROM anomalias LIKE 'codigo_seguimiento'");
    $tiene_codigo = $check_codigo && $check_codigo->num_rows > 0;
    
    $check_materiales = $conn->query("SHOW COLUMNS FROM anomalias LIKE 'materiales_afectados'");
    $tiene_materiales = $check_materiales && $check_materiales->num_rows > 0;
    
    $check_responsable = $conn->query("SHOW COLUMNS FROM anomalias LIKE 'responsable_asignado'");
    $tiene_responsable = $check_responsable && $check_responsable->num_rows > 0;
    
    // Construir consulta dinámica según campos disponibles
    $campos_adicionales = "";
    if ($tiene_impacto) $campos_adicionales .= ", a.impacto";
    if ($tiene_codigo) $campos_adicionales .= ", a.codigo_seguimiento";
    if ($tiene_materiales) $campos_adicionales .= ", a.materiales_afectados";
    if ($tiene_responsable) $campos_adicionales .= ", a.responsable_asignado";
    
    // Consulta para obtener la anomalía específica
    $sql = "SELECT 
                a.id,
                a.titulo,
                a.descripcion,
                a.prioridad,
                a.categoria,
                a.ubicacion,
                a.estado,
                a.fecha_creacion,
                a.fecha_actualizacion,
                a.fecha_resolucion,
                a.notas_resolucion,
                a.usuario_creador,
                a.usuario_asignado
                $campos_adicionales,
                COALESCE(CONCAT(u_creador.nombre, ' ', u_creador.apellido), 'Usuario desconocido') as nombre_creador,
                COALESCE(CONCAT(u_asignado.nombre, ' ', u_asignado.apellido), NULL) as nombre_asignado
            FROM anomalias a
            LEFT JOIN usuarios u_creador ON a.usuario_creador = u_creador.id_usuarios
            LEFT JOIN usuarios u_asignado ON a.usuario_asignado = u_asignado.id_usuarios
            WHERE a.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Anomalía no encontrada");
    }
    
    $anomalia = $result->fetch_assoc();
    
    // Agregar valores por defecto si los campos no existen
    if (!isset($anomalia['impacto'])) {
        $anomalia['impacto'] = 'medio';
    }
    if (!isset($anomalia['codigo_seguimiento'])) {
        $anomalia['codigo_seguimiento'] = 'ANO-' . str_pad($anomalia['id'], 6, '0', STR_PAD_LEFT);
    }
    if (!isset($anomalia['materiales_afectados'])) {
        $anomalia['materiales_afectados'] = null;
    }
    if (!isset($anomalia['responsable_asignado'])) {
        $anomalia['responsable_asignado'] = null;
    }
    
    // Formatear fechas
    if ($anomalia['fecha_creacion']) {
        $anomalia['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($anomalia['fecha_creacion']));
    }
    
    if ($anomalia['fecha_actualizacion']) {
        $anomalia['fecha_actualizacion_formateada'] = date('d/m/Y H:i', strtotime($anomalia['fecha_actualizacion']));
    }
    
    if ($anomalia['fecha_resolucion']) {
        $anomalia['fecha_resolucion_formateada'] = date('d/m/Y H:i', strtotime($anomalia['fecha_resolucion']));
    }
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'anomalia' => $anomalia
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_anomalia.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>