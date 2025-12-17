<?php
session_start();
require_once 'conexion.php';
require_once 'verificar_permisos.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para gestionar permisos']);
    exit;
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

$conn = ConectarDB();

switch ($accion) {
    case 'listar_permisos':
        listarPermisos($conn);
        break;
    
    case 'listar_permisos_rol':
        listarPermisosRol($conn);
        break;
    
    case 'asignar_permiso':
        asignarPermiso($conn);
        break;
    
    case 'revocar_permiso':
        revocarPermiso($conn);
        break;
    
    case 'obtener_matriz_permisos':
        obtenerMatrizPermisos($conn);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

$conn->close();

/**
 * Listar todos los permisos disponibles
 */
function listarPermisos($conn) {
    $sql = "SELECT * FROM permisos WHERE activo = 1 ORDER BY modulo, accion";
    $resultado = $conn->query($sql);
    
    $permisos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $permisos[] = $fila;
    }
    
    echo json_encode([
        'success' => true,
        'permisos' => $permisos
    ]);
}

/**
 * Listar permisos de un rol específico
 */
function listarPermisosRol($conn) {
    $rol = $_GET['rol'] ?? '';
    
    if (empty($rol)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Rol no especificado']);
        return;
    }
    
    $sql = "SELECT p.*, 
            (SELECT COUNT(*) FROM roles_permisos WHERE rol = ? AND id_permiso = p.id_permiso) as asignado
            FROM permisos p
            WHERE p.activo = 1
            ORDER BY p.modulo, p.accion";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $permisos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $fila['asignado'] = (bool)$fila['asignado'];
        $permisos[] = $fila;
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'rol' => $rol,
        'permisos' => $permisos
    ]);
}

/**
 * Asignar permiso a un rol
 */
function asignarPermiso($conn) {
    $rol = $_POST['rol'] ?? '';
    $id_permiso = $_POST['id_permiso'] ?? 0;
    
    if (empty($rol) || $id_permiso <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    
    // Verificar si ya existe
    $sql_check = "SELECT COUNT(*) as existe FROM roles_permisos WHERE rol = ? AND id_permiso = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("si", $rol, $id_permiso);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($resultado['existe'] > 0) {
        echo json_encode(['success' => false, 'message' => 'El permiso ya está asignado a este rol']);
        return;
    }
    
    // Asignar permiso
    $sql = "INSERT INTO roles_permisos (rol, id_permiso, asignado_por) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $rol, $id_permiso, $_SESSION['usuario_id']);
    
    if ($stmt->execute()) {
        // Registrar en auditoría
        $sql_audit = "INSERT INTO auditoria_permisos (rol, id_permiso, accion, realizado_por, detalles) 
                      VALUES (?, ?, 'asignar', ?, 'Permiso asignado al rol')";
        $stmt_audit = $conn->prepare($sql_audit);
        $stmt_audit->bind_param("sii", $rol, $id_permiso, $_SESSION['usuario_id']);
        $stmt_audit->execute();
        $stmt_audit->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Permiso asignado correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al asignar permiso']);
    }
    
    $stmt->close();
}

/**
 * Revocar permiso de un rol
 */
function revocarPermiso($conn) {
    $rol = $_POST['rol'] ?? '';
    $id_permiso = $_POST['id_permiso'] ?? 0;
    
    if (empty($rol) || $id_permiso <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    
    // No permitir revocar permisos del administrador
    if ($rol === 'administrador') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No se pueden modificar permisos del administrador']);
        return;
    }
    
    // Revocar permiso
    $sql = "DELETE FROM roles_permisos WHERE rol = ? AND id_permiso = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rol, $id_permiso);
    
    if ($stmt->execute()) {
        // Registrar en auditoría
        $sql_audit = "INSERT INTO auditoria_permisos (rol, id_permiso, accion, realizado_por, detalles) 
                      VALUES (?, ?, 'revocar', ?, 'Permiso revocado del rol')";
        $stmt_audit = $conn->prepare($sql_audit);
        $stmt_audit->bind_param("sii", $rol, $id_permiso, $_SESSION['usuario_id']);
        $stmt_audit->execute();
        $stmt_audit->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Permiso revocado correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al revocar permiso']);
    }
    
    $stmt->close();
}

/**
 * Obtener matriz completa de permisos (todos los roles y permisos)
 */
function obtenerMatrizPermisos($conn) {
    // Obtener todos los roles
    $roles = ['administrador', 'gerente', 'supervisor', 'almacenista', 'recepcionista', 'usuario'];
    
    // Obtener todos los permisos agrupados por módulo
    $sql = "SELECT * FROM permisos WHERE activo = 1 ORDER BY modulo, accion";
    $resultado = $conn->query($sql);
    
    $permisos_por_modulo = [];
    while ($fila = $resultado->fetch_assoc()) {
        $modulo = $fila['modulo'];
        if (!isset($permisos_por_modulo[$modulo])) {
            $permisos_por_modulo[$modulo] = [];
        }
        $permisos_por_modulo[$modulo][] = $fila;
    }
    
    // Obtener asignaciones actuales
    $sql_asignaciones = "SELECT rol, id_permiso FROM roles_permisos";
    $resultado_asig = $conn->query($sql_asignaciones);
    
    $asignaciones = [];
    while ($fila = $resultado_asig->fetch_assoc()) {
        $key = $fila['rol'] . '_' . $fila['id_permiso'];
        $asignaciones[$key] = true;
    }
    
    echo json_encode([
        'success' => true,
        'roles' => $roles,
        'permisos_por_modulo' => $permisos_por_modulo,
        'asignaciones' => $asignaciones
    ]);
}
?>
