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
    // Incluir configuración de base de datos
    require_once 'conexion.php';
    
    // Crear conexión
    $conn = ConectarDB();
    
    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    // Configurar charset
    $conn->set_charset("utf8mb4");
    
    // Obtener datos del formulario
    $id = isset($_POST['id']) && !empty($_POST['id']) ? intval($_POST['id']) : null;
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $prioridad = $_POST['prioridad'] ?? '';
    $categoria = trim($_POST['categoria'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];
    
    // Validaciones
    if (empty($titulo)) {
        throw new Exception("El título es obligatorio");
    }
    
    if (strlen($titulo) > 100) {
        throw new Exception("El título no puede exceder 100 caracteres");
    }
    
    if (empty($descripcion)) {
        throw new Exception("La descripción es obligatoria");
    }
    
    if (empty($prioridad) || !in_array($prioridad, ['baja', 'media', 'urgente'])) {
        throw new Exception("La prioridad es obligatoria y debe ser válida");
    }
    
    if (!empty($categoria) && strlen($categoria) > 50) {
        throw new Exception("La categoría no puede exceder 50 caracteres");
    }
    
    if (!empty($ubicacion) && strlen($ubicacion) > 100) {
        throw new Exception("La ubicación no puede exceder 100 caracteres");
    }
    
    // Preparar consulta según si es creación o edición
    if ($id) {
        // Editar anomalía existente
        
        // Primero verificar que la anomalía existe y obtener datos actuales para el historial
        $check_sql = "SELECT * FROM anomalias WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("La anomalía no existe");
        }
        
        $anomalia_actual = $result->fetch_assoc();
        $check_stmt->close();
        
        // Actualizar anomalía
        $sql = "UPDATE anomalias SET 
                    titulo = ?, 
                    descripcion = ?, 
                    prioridad = ?, 
                    categoria = ?, 
                    ubicacion = ?,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $titulo, $descripcion, $prioridad, $categoria, $ubicacion, $id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar la anomalía: " . $stmt->error);
        }
        
        // Registrar cambios en el historial
        $cambios = [];
        if ($anomalia_actual['titulo'] !== $titulo) {
            $cambios[] = ['campo' => 'titulo', 'anterior' => $anomalia_actual['titulo'], 'nuevo' => $titulo];
        }
        if ($anomalia_actual['descripcion'] !== $descripcion) {
            $cambios[] = ['campo' => 'descripcion', 'anterior' => $anomalia_actual['descripcion'], 'nuevo' => $descripcion];
        }
        if ($anomalia_actual['prioridad'] !== $prioridad) {
            $cambios[] = ['campo' => 'prioridad', 'anterior' => $anomalia_actual['prioridad'], 'nuevo' => $prioridad];
        }
        if ($anomalia_actual['categoria'] !== $categoria) {
            $cambios[] = ['campo' => 'categoria', 'anterior' => $anomalia_actual['categoria'], 'nuevo' => $categoria];
        }
        if ($anomalia_actual['ubicacion'] !== $ubicacion) {
            $cambios[] = ['campo' => 'ubicacion', 'anterior' => $anomalia_actual['ubicacion'], 'nuevo' => $ubicacion];
        }
        
        // Insertar cambios en el historial
        if (!empty($cambios)) {
            $historial_sql = "INSERT INTO anomalias_historial (anomalia_id, campo_modificado, valor_anterior, valor_nuevo, usuario_modificador, comentario) VALUES (?, ?, ?, ?, ?, ?)";
            $historial_stmt = $conn->prepare($historial_sql);
            
            foreach ($cambios as $cambio) {
                $comentario = "Modificación desde interfaz web";
                $historial_stmt->bind_param("isssss", 
                    $id, 
                    $cambio['campo'], 
                    $cambio['anterior'], 
                    $cambio['nuevo'], 
                    $usuario_id, 
                    $comentario
                );
                $historial_stmt->execute();
            }
            $historial_stmt->close();
        }
        
        $stmt->close();
        
        // Registrar en historial de acciones del sistema (si existe la tabla)
        $check_historial = $conn->query("SHOW TABLES LIKE 'historial_acciones'");
        if ($check_historial && $check_historial->num_rows > 0) {
            $accion_sql = "INSERT INTO historial_acciones (usuario_id, accion, descripcion, fecha) VALUES (?, ?, ?, NOW())";
            $accion_stmt = $conn->prepare($accion_sql);
            if ($accion_stmt) {
                $accion_descripcion = "Editó la anomalía: " . $titulo;
                $accion_tipo = "editar_anomalia";
                $accion_stmt->bind_param("iss", $usuario_id, $accion_tipo, $accion_descripcion);
                $accion_stmt->execute();
                $accion_stmt->close();
            }
        }
        
        $mensaje = "Anomalía actualizada correctamente";
        
    } else {
        // Crear nueva anomalía
        $sql = "INSERT INTO anomalias (titulo, descripcion, prioridad, categoria, ubicacion, usuario_creador) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $titulo, $descripcion, $prioridad, $categoria, $ubicacion, $usuario_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear la anomalía: " . $stmt->error);
        }
        
        $nuevo_id = $conn->insert_id;
        $stmt->close();
        
        // Registrar en historial de acciones del sistema (si existe la tabla)
        $check_historial = $conn->query("SHOW TABLES LIKE 'historial_acciones'");
        if ($check_historial && $check_historial->num_rows > 0) {
            $accion_sql = "INSERT INTO historial_acciones (usuario_id, accion, descripcion, fecha) VALUES (?, ?, ?, NOW())";
            $accion_stmt = $conn->prepare($accion_sql);
            if ($accion_stmt) {
                $accion_descripcion = "Creó la anomalía: " . $titulo;
                $accion_tipo = "crear_anomalia";
                $accion_stmt->bind_param("iss", $usuario_id, $accion_tipo, $accion_descripcion);
                $accion_stmt->execute();
                $accion_stmt->close();
            }
        }
        
        $mensaje = "Anomalía creada correctamente";
        $id = $nuevo_id;
    }
    
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'anomalia_id' => $id
    ]);
    
} catch (Exception $e) {
    error_log("Error en guardar_anomalia.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>