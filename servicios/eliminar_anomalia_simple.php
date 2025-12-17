<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit;
}

try {
    // Obtener datos JSON del cuerpo de la petición
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception("ID de anomalía no proporcionado");
    }
    
    $id = intval($input['id']);
    
    if ($id <= 0) {
        throw new Exception("ID de anomalía inválido");
    }
    
    // Incluir configuración de base de datos
    require_once 'conexion.php';
    
    // Crear conexión
    $conn = ConectarDB();
    
    // Configurar charset
    $conn->set_charset("utf8mb4");
    
    // Verificar que la anomalía existe
    $check_sql = "SELECT titulo FROM anomalias WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Error al preparar consulta de verificación: " . $conn->error);
    }
    
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("La anomalía no existe");
    }
    
    $check_stmt->close();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Eliminar registros del historial primero (si existe la tabla)
        $check_historial_table = $conn->query("SHOW TABLES LIKE 'anomalias_historial'");
        if ($check_historial_table && $check_historial_table->num_rows > 0) {
            $delete_historial_sql = "DELETE FROM anomalias_historial WHERE anomalia_id = ?";
            $delete_historial_stmt = $conn->prepare($delete_historial_sql);
            if ($delete_historial_stmt) {
                $delete_historial_stmt->bind_param("i", $id);
                $delete_historial_stmt->execute();
                $delete_historial_stmt->close();
            }
        }
        
        // Eliminar la anomalía
        $delete_sql = "DELETE FROM anomalias WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        if (!$delete_stmt) {
            throw new Exception("Error al preparar consulta de eliminación: " . $conn->error);
        }
        
        $delete_stmt->bind_param("i", $id);
        
        if (!$delete_stmt->execute()) {
            throw new Exception("Error al eliminar la anomalía: " . $delete_stmt->error);
        }
        
        if ($delete_stmt->affected_rows === 0) {
            throw new Exception("No se pudo eliminar la anomalía");
        }
        
        $delete_stmt->close();
        
        // Confirmar transacción
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Anomalía eliminada correctamente'
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        throw $e;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    error_log("Error en eliminar_anomalia_simple.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>