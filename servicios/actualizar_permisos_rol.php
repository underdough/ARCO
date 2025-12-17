<?php
/**
 * Servicio para Actualizar Permisos de un Rol
 * Solo accesible por administradores
 */

session_start();
header('Content-Type: application/json');

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode([
        'success' => false,
        'error' => 'No tiene permisos para realizar esta acción'
    ]);
    exit;
}

require_once 'conexion.php';

try {
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['rol']) || !isset($input['permisos'])) {
        throw new Exception('Datos incompletos');
    }
    
    $rol = $input['rol'];
    $permisos = $input['permisos']; // Array de {id_modulo, id_permiso, activo}
    
    $conn = conectarDB();
    $conn->begin_transaction();
    
    $actualizados = 0;
    $errores = [];
    
    foreach ($permisos as $permiso) {
        $id_modulo = $permiso['id_modulo'];
        $id_permiso = $permiso['id_permiso'];
        $activo = $permiso['activo'] ? 1 : 0;
        
        // Verificar si existe el registro
        $sql_check = "SELECT id, activo FROM rol_permisos 
                      WHERE rol = ? AND id_modulo = ? AND id_permiso = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sii", $rol, $id_modulo, $id_permiso);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar registro existente
            $row = $result->fetch_assoc();
            $valor_anterior = $row['activo'];
            
            if ($valor_anterior != $activo) {
                $sql_update = "UPDATE rol_permisos SET activo = ? 
                               WHERE rol = ? AND id_modulo = ? AND id_permiso = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("isii", $activo, $rol, $id_modulo, $id_permiso);
                
                if ($stmt_update->execute()) {
                    $actualizados++;
                    
                    // Registrar en auditoría
                    $accion = $activo ? 'activar' : 'desactivar';
                    $sql_audit = "INSERT INTO auditoria_permisos 
                                  (rol, id_modulo, id_permiso, accion, valor_anterior, valor_nuevo, realizado_por, ip_address)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_audit = $conn->prepare($sql_audit);
                    $usuario_id = $_SESSION['usuario_id'];
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $stmt_audit->bind_param("siisssis", $rol, $id_modulo, $id_permiso, $accion, 
                                           $valor_anterior, $activo, $usuario_id, $ip);
                    $stmt_audit->execute();
                }
                $stmt_update->close();
            }
        } else {
            // Insertar nuevo registro si activo = 1
            if ($activo == 1) {
                $sql_insert = "INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
                               VALUES (?, ?, ?, 1)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("sii", $rol, $id_modulo, $id_permiso);
                
                if ($stmt_insert->execute()) {
                    $actualizados++;
                    
                    // Registrar en auditoría
                    $sql_audit = "INSERT INTO auditoria_permisos 
                                  (rol, id_modulo, id_permiso, accion, valor_nuevo, realizado_por, ip_address)
                                  VALUES (?, ?, ?, 'asignar', '1', ?, ?)";
                    $stmt_audit = $conn->prepare($sql_audit);
                    $usuario_id = $_SESSION['usuario_id'];
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $stmt_audit->bind_param("siiis", $rol, $id_modulo, $id_permiso, $usuario_id, $ip);
                    $stmt_audit->execute();
                }
                $stmt_insert->close();
            }
        }
        
        $stmt_check->close();
    }
    
    $conn->commit();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'message' => "Permisos actualizados correctamente",
        'actualizados' => $actualizados
    ]);
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
        $conn->close();
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
