<?php
/**
 * API de Órdenes de Compra
 * Acciones: listar, crear, ver, actualizar_estado, recibir
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
        listarOrdenes($conexion);
        break;
    case 'crear':
        crearOrden($conexion, $usuario_id);
        break;
    case 'ver':
        verOrden($conexion);
        break;
    case 'actualizar_estado':
        actualizarEstado($conexion, $usuario_id);
        break;
    case 'recibir':
        recibirOrden($conexion, $usuario_id);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function listarOrdenes($conexion) {
    $sql = "SELECT oc.*, u.nombre as usuario_nombre,
            (SELECT COUNT(*) FROM orden_detalles WHERE orden_id = oc.id) as total_items
            FROM ordenes_compra oc
            LEFT JOIN usuarios u ON oc.usuario_id = u.id_usuarios
            ORDER BY oc.created_at DESC";
    
    $resultado = $conexion->query($sql);
    $ordenes = [];
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $ordenes[] = $fila;
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $ordenes]);
}

function crearOrden($conexion, $usuario_id) {
    if (!isset($_POST['proveedor']) || !isset($_POST['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
    $proveedor = $conexion->real_escape_string($_POST['proveedor']);
    $fecha_pedido = date('Y-m-d');
    $fecha_esperada = isset($_POST['fecha_esperada']) ? $conexion->real_escape_string($_POST['fecha_esperada']) : null;
    $notas = isset($_POST['notas']) ? $conexion->real_escape_string($_POST['notas']) : '';
    $items = json_decode($_POST['items'], true);
    
    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'Debe agregar al menos un item']);
        return;
    }
    
    $conexion->begin_transaction();
    
    try {
        // Generar número de orden
        $numero_orden = 'OC-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Calcular total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['cantidad'] * $item['precio_unitario'];
        }
        
        // Insertar orden
        $fechaEsp = $fecha_esperada ? "'$fecha_esperada'" : "NULL";
        $sql = "INSERT INTO ordenes_compra (numero_orden, proveedor, fecha_pedido, fecha_esperada, estado, total, usuario_id, notas)
                VALUES ('$numero_orden', '$proveedor', '$fecha_pedido', $fechaEsp, 'pendiente', $total, $usuario_id, '$notas')";
        
        if (!$conexion->query($sql)) {
            throw new Exception('Error al crear orden: ' . $conexion->error);
        }
        
        $orden_id = $conexion->insert_id;
        
        // Insertar detalles
        foreach ($items as $item) {
            $material_id = (int) $item['material_id'];
            $cantidad = (int) $item['cantidad'];
            $precio = (float) $item['precio_unitario'];
            $subtotal = $cantidad * $precio;
            
            $sqlDetalle = "INSERT INTO orden_detalles (orden_id, material_id, cantidad_pedida, precio_unitario, subtotal)
                           VALUES ($orden_id, $material_id, $cantidad, $precio, $subtotal)";
            
            if (!$conexion->query($sqlDetalle)) {
                throw new Exception('Error al agregar item: ' . $conexion->error);
            }
        }
        
        $conexion->commit();
        
        registrarHistorial($usuario_id, 'crear_oc', "Creó orden de compra $numero_orden por \$$total");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Orden creada correctamente',
            'orden_id' => $orden_id,
            'numero_orden' => $numero_orden
        ]);
        
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

function verOrden($conexion) {
    if (!isset($_GET['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'ID requerido']);
        return;
    }
    
    $id = (int) $_GET['id'];
    
    // Obtener orden
    $sql = "SELECT oc.*, u.nombre as usuario_nombre
            FROM ordenes_compra oc
            LEFT JOIN usuarios u ON oc.usuario_id = u.id_usuarios
            WHERE oc.id = $id";
    
    $resultado = $conexion->query($sql);
    
    if (!$resultado || $resultado->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Orden no encontrada']);
        return;
    }
    
    $orden = $resultado->fetch_assoc();
    
    // Obtener detalles
    $sqlDetalles = "SELECT od.*, m.nombre_material
                    FROM orden_detalles od
                    LEFT JOIN materiales m ON od.material_id = m.id_material
                    WHERE od.orden_id = $id";
    
    $resDetalles = $conexion->query($sqlDetalles);
    $detalles = [];
    
    if ($resDetalles) {
        while ($fila = $resDetalles->fetch_assoc()) {
            $detalles[] = $fila;
        }
    }
    
    $orden['detalles'] = $detalles;
    
    echo json_encode(['status' => 'success', 'data' => $orden]);
}

function actualizarEstado($conexion, $usuario_id) {
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
    $id = (int) $_POST['id'];
    $estado = $conexion->real_escape_string($_POST['estado']);
    
    $estadosValidos = ['pendiente', 'recibida', 'cancelada', 'parcial'];
    if (!in_array($estado, $estadosValidos)) {
        echo json_encode(['status' => 'error', 'message' => 'Estado no válido']);
        return;
    }
    
    $sql = "UPDATE ordenes_compra SET estado = '$estado' WHERE id = $id";
    
    if ($conexion->query($sql)) {
        registrarHistorial($usuario_id, 'actualizar_oc', "Cambió estado de OC #$id a $estado");
        echo json_encode(['status' => 'success', 'message' => 'Estado actualizado']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar']);
    }
}

function recibirOrden($conexion, $usuario_id) {
    // Recibir items de una OC y crear movimientos de entrada
    if (!isset($_POST['orden_id']) || !isset($_POST['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        return;
    }
    
    $orden_id = (int) $_POST['orden_id'];
    $items = json_decode($_POST['items'], true);
    
    $conexion->begin_transaction();
    
    try {
        foreach ($items as $item) {
            $material_id = (int) $item['material_id'];
            $cantidad = (int) $item['cantidad_recibida'];
            
            if ($cantidad <= 0) continue;
            
            // Actualizar stock
            $sqlStock = "UPDATE materiales SET stock = stock + $cantidad WHERE id_material = $material_id";
            $conexion->query($sqlStock);
            
            // Actualizar cantidad recibida en detalle
            $sqlDetalle = "UPDATE orden_detalles 
                           SET cantidad_recibida = cantidad_recibida + $cantidad 
                           WHERE orden_id = $orden_id AND material_id = $material_id";
            $conexion->query($sqlDetalle);
            
            // Crear movimiento de tipo recibido
            $fecha = date('Y-m-d');
            $sqlMov = "INSERT INTO movimientos (tipo, fecha, producto_id, cantidad, usuario_id, notas, orden_compra_id)
                       VALUES ('recibido', '$fecha', $material_id, $cantidad, $usuario_id, 'Recepción de OC #$orden_id', $orden_id)";
            $conexion->query($sqlMov);
        }
        
        // Verificar si la orden está completa
        $sqlVerificar = "SELECT 
                            SUM(cantidad_pedida) as total_pedido,
                            SUM(cantidad_recibida) as total_recibido
                         FROM orden_detalles WHERE orden_id = $orden_id";
        $res = $conexion->query($sqlVerificar);
        $totales = $res->fetch_assoc();
        
        $nuevoEstado = 'parcial';
        if ($totales['total_recibido'] >= $totales['total_pedido']) {
            $nuevoEstado = 'recibida';
        }
        
        $conexion->query("UPDATE ordenes_compra SET estado = '$nuevoEstado' WHERE id = $orden_id");
        
        $conexion->commit();
        
        registrarHistorial($usuario_id, 'recibir_oc', "Recibió items de OC #$orden_id");
        
        echo json_encode(['status' => 'success', 'message' => 'Recepción registrada', 'nuevo_estado' => $nuevoEstado]);
        
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
