<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos']);
    exit;
}

require_once 'conexion.php';

// Obtener datos JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['rol']) || !isset($data['cambios'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$rol = $data['rol'];
$cambios = $data['cambios'];

try {
    $conn = ConectarDB();
    $conn->begin_transaction();
    
    $cambios_realizados = 0;
    
    foreach ($cambios as $cambio) {
        $id_modulo = $cambio['id_modulo'];
        $codigo_permiso = $cambio['permiso'];
        $activo = $cambio['activo'] ? 1 : 0;
        
        // Obtener ID del permiso
        $sql_permiso = "SELECT id_permiso FROM permisos WHERE codigo = ?";
        $stmt_permiso = $conn->prepare($sql_permiso);
        $stmt_permiso->bind_param("s", $codigo_permiso);
        $stmt_permiso->execute();
        $result_permiso = $stmt_permiso->get_result();
        
        if ($result_permiso->num_rows === 0) {
            $stmt_permiso->close();
            continue;
        }
        
        $id_permiso = $result_permiso->fetch_assoc()['id_permiso'];
        $stmt_permiso->close();
        
        // Verificar si ya existe el registro
        $sql_existe = "SELECT id, activo FROM rol_modulo_permisos 
                      WHERE rol = ? AND id_modulo = ? AND id_permiso = ?";
        $stmt_existe = $conn->prepare($sql_existe);
        $stmt_existe->bind_param("sii", $rol, $id_modulo, $id_permiso);
        $stmt_existe->execute();
        $result_existe = $stmt_existe->get_result();
        
        if ($result_existe->num_rows > 0) {
            // Actualizar registro existente
            $registro = $result_existe->fetch_assoc();
            $valor_anterior = $registro['activo'];
            
            if ($valor_anterior != $activo) {
                $sql_update = "UPDATE rol_modulo_permisos 
                              SET activo = ? 
                              WHERE rol = ? AND id_modulo = ? AND id_permiso = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("isii", $activo, $rol, $id_modulo, $id_permiso);
                $stmt_update->execute();
                $stmt_update->close();
                
                // Registrar en auditoría
                $accion = $activo ? 'asignar' : 'revocar';
                $sql_audit = "INSERT INTO auditoria_permisos 
                             (rol, id_modulo, id_permiso, accion, valor_anterior, valor_nuevo, realizado_por) 
                             VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_audit = $conn->prepare($sql_audit);
                $stmt_audit->bind_param("siisiii", $rol, $id_modulo, $id_permiso, $accion, $valor_anterior, $activo, $_SESSION['usuario_id']);
                $stmt_audit->execute();
                $stmt_audit->close();
                
                $cambios_realizados++;
            }
        } else {
            // Insertar nuevo registro
            if ($activo) {
                $sql_insert = "INSERT INTO rol_modulo_permisos 
                              (rol, id_modulo, id_permiso, activo, asignado_por) 
                              VALUES (?, ?, ?, 1, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("siii", $rol, $id_modulo, $id_permiso, $_SESSION['usuario_id']);
                $stmt_insert->execute();
                $stmt_insert->close();
                
                // Registrar en auditoría
                $sql_audit = "INSERT INTO auditoria_permisos 
                             (rol, id_modulo, id_permiso, accion, valor_nuevo, realizado_por) 
                             VALUES (?, ?, ?, 'asignar', 1, ?)";
                $stmt_audit = $conn->prepare($sql_audit);
                $stmt_audit->bind_param("siii", $rol, $id_modulo, $id_permiso, $_SESSION['usuario_id']);
                $stmt_audit->execute();
                $stmt_audit->close();
                
                $cambios_realizados++;
            }
        }
        
        $stmt_existe->close();
    }
    
    $conn->commit();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Se realizaron $cambios_realizados cambios",
        'cambios_realizados' => $cambios_realizados
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar permisos: ' . $e->getMessage()
    ]);
}
?>
