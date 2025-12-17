<?php
/**
 * API de Devoluciones
 * Acciones: listar, crear, procesar
 */
session_start();
header('Content-Type: application/json');

include_once "conexion.php";
include_once "registrar_historial.php";

$conexion = ConectarDB();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : 'listar';

switch ($accion) {
    case 'listar':
        listarDevoluciones($conexion);
        break;
    case 'crear':
        crearDevolucion($conexion, $usuario_id);
        break;
    case 'procesar':
        procesarDevolucion($conexion, $usuario_id);
        break;
    case 'ver':
        verDevolucion($conexion);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function listarDevoluciones($conexion) {
    $sql = "SELECT d.*, m.nombre_material, 
            us.nombre as solicitante_nombre,
            up.nombre as procesador_nombre
            FROM devoluciones d
            LEFT JOIN materiales m ON d.material_id = m.id_material
            LEFT JOIN usuarios us ON d.usuario_solicita = us.id_usuarios
            LEFT JOIN usuarios up ON d.usuario_procesa = up.id_usuarios
            ORDER BY d.fecha_solicitud DESC";
    
    $resultado = $conexion->query($sql);
    $devoluciones = [];
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $devoluciones[] = $fila;
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $devoluciones]);
}

function crearDevolucion($conexion, $usuario_id) {
    if (!isset($_POST['material_id']) || !isset($_POST['cantidad']) || !isset($_POST['motivo'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
    $material_id = (int) $_POST['material_id'];
    $cantidad = (int) $_POST['cantidad'];
    $motivo = $conexion->real_escape_string($_POST['motivo']);
    $descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string($_POST['descripcion']) : '';
    $notas = isset($_POST['notas']) ? $conexion->real_escape_string($_POST['notas']) : '';
    
    // Validar motivo
    $motivosValidos = ['defectuoso', 'incorrecto', 'no_requerido', 'vencido', 'otro'];
    if (!in_array($motivo, $motivosValidos)) {
        echo json_encode(['status' => 'error', 'message' => 'Motivo no válido']);
        return;
    }
    
    // Generar número de devolución
    $numero = 'DEV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $sql = "INSERT INTO devoluciones (numero_devolucion, material_id, cantidad, motivo, descripcion, estado, usuario_solicita, notas)
            VALUES ('$numero', $material_id, $cantidad, '$motivo', '$descripcion', 'pendiente', $usuario_id, '$notas')";
    
    if ($conexion->query($sql)) {
        $devolucion_id = $conexion->insert_id;
        
        // Obtener nombre del material
        $resMat = $conexion->query("SELECT nombre_material FROM materiales WHERE id_material = $material_id");
        $mat = $resMat->fetch_assoc();
        
        registrarHistorial($usuario_id, 'crear_devolucion', "Solicitó devolución $numero de $cantidad unidades de {$mat['nombre_material']}");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Devolución creada',
            'devolucion_id' => $devolucion_id,
            'numero_devolucion' => $numero
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al crear: ' . $conexion->error]);
    }
}

function procesarDevolucion($conexion, $usuario_id) {
    if (!isset($_POST['id']) || !isset($_POST['accion_procesar'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
    $id = (int) $_POST['id'];
    $accion_procesar = $_POST['accion_procesar']; // aprobar o rechazar
    
    // Obtener datos de la devolución
    $sql = "SELECT * FROM devoluciones WHERE id = $id AND estado = 'pendiente'";
    $resultado = $conexion->query($sql);
    
    if (!$resultado || $resultado->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Devolución no encontrada o ya procesada']);
        return;
    }
    
    $devolucion = $resultado->fetch_assoc();
    
    $conexion->begin_transaction();
    
    try {
        if ($accion_procesar === 'aprobar') {
            // Verificar stock suficiente
            $resMat = $conexion->query("SELECT stock, nombre_material FROM materiales WHERE id_material = {$devolucion['material_id']} FOR UPDATE");
            $material = $resMat->fetch_assoc();
            
            if ($material['stock'] < $devolucion['cantidad']) {
                throw new Exception("Stock insuficiente para procesar devolución");
            }
            
            // Descontar stock
            $nuevoStock = $material['stock'] - $devolucion['cantidad'];
            $conexion->query("UPDATE materiales SET stock = $nuevoStock WHERE id_material = {$devolucion['material_id']}");
            
            // Crear movimiento de tipo devolucion
            $fecha = date('Y-m-d');
            $sqlMov = "INSERT INTO movimientos (tipo, fecha, producto_id, cantidad, usuario_id, notas, devolucion_id)
                       VALUES ('devolucion', '$fecha', {$devolucion['material_id']}, {$devolucion['cantidad']}, $usuario_id, 
                               'Devolución {$devolucion['numero_devolucion']} - {$devolucion['motivo']}', $id)";
            $conexion->query($sqlMov);
            
            // Actualizar estado
            $conexion->query("UPDATE devoluciones SET estado = 'procesada', usuario_procesa = $usuario_id, fecha_procesado = NOW() WHERE id = $id");
            
            registrarHistorial($usuario_id, 'procesar_devolucion', "Aprobó devolución {$devolucion['numero_devolucion']}");
            
        } else {
            // Rechazar
            $conexion->query("UPDATE devoluciones SET estado = 'rechazada', usuario_procesa = $usuario_id, fecha_procesado = NOW() WHERE id = $id");
            registrarHistorial($usuario_id, 'rechazar_devolucion', "Rechazó devolución {$devolucion['numero_devolucion']}");
        }
        
        $conexion->commit();
        echo json_encode(['status' => 'success', 'message' => 'Devolución procesada']);
        
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function verDevolucion($conexion) {
    if (!isset($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
        return;
    }
    
    $id = (int) $_GET['id'];
    
    $sql = "SELECT d.*, m.nombre_material, 
            us.nombre as solicitante_nombre,
            up.nombre as procesador_nombre
            FROM devoluciones d
            LEFT JOIN materiales m ON d.material_id = m.id_material
            LEFT JOIN usuarios us ON d.usuario_solicita = us.id_usuarios
            LEFT JOIN usuarios up ON d.usuario_procesa = up.id_usuarios
            WHERE d.id = $id";
    
    $resultado = $conexion->query($sql);
    
    if ($resultado && $resultado->num_rows > 0) {
        echo json_encode(['status' => 'success', 'data' => $resultado->fetch_assoc()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Devolución no encontrada']);
    }
}
