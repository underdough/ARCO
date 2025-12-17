<?php
session_start();
header('Content-Type: application/json');

include_once "conexion.php";
include_once "registrar_historial.php";

$conexion = ConectarDB();

// Verificamos si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
    exit;
}

$usuario = (int) $_SESSION['usuario_id'];

// Validamos los campos necesarios
if (
    isset($_POST['tipo']) &&
    isset($_POST['fecha']) &&
    isset($_POST['producto']) &&
    isset($_POST['cantidad'])
) {
    $tipo = $conexion->real_escape_string($_POST['tipo']);
    $fecha = $conexion->real_escape_string($_POST['fecha']);
    $producto = (int) $_POST['producto'];
    $cantidad = (int) $_POST['cantidad'];
    $notas = isset($_POST['notas']) ? $conexion->real_escape_string($_POST['notas']) : '';
    
    // Campos opcionales para recibidos/devoluciones
    $orden_compra_id = isset($_POST['orden_compra_id']) ? (int) $_POST['orden_compra_id'] : null;
    $devolucion_id = isset($_POST['devolucion_id']) ? (int) $_POST['devolucion_id'] : null;

    // Validar que la cantidad sea positiva
    if ($cantidad <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'La cantidad debe ser mayor a 0']);
        exit;
    }

    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // Obtener stock actual y nombre del producto
        $sqlStock = "SELECT stock, nombre_material FROM materiales WHERE id_material = $producto FOR UPDATE";
        $resultado = $conexion->query($sqlStock);
        
        if (!$resultado || $resultado->num_rows === 0) {
            throw new Exception('Producto no encontrado');
        }
        
        $productoData = $resultado->fetch_assoc();
        $stockActual = (int) $productoData['stock'];
        $nombreProducto = $productoData['nombre_material'];

        // Calcular nuevo stock según tipo de movimiento
        // Tipos: entrada, salida, ajuste, recibido, devolucion
        $nuevoStock = $stockActual;
        
        switch ($tipo) {
            case 'entrada':
            case 'recibido': // Recibido = entrada desde OC
                $nuevoStock = $stockActual + $cantidad;
                break;
            case 'salida':
                // Validar stock suficiente
                if ($stockActual < $cantidad) {
                    throw new Exception("Stock insuficiente. Stock actual: $stockActual, Cantidad solicitada: $cantidad");
                }
                $nuevoStock = $stockActual - $cantidad;
                break;
            case 'devolucion': // Devolución = salida por producto defectuoso/incorrecto
                if ($stockActual < $cantidad) {
                    throw new Exception("Stock insuficiente para devolución. Stock actual: $stockActual");
                }
                $nuevoStock = $stockActual - $cantidad;
                break;
            case 'ajuste':
                // El ajuste establece el stock al valor indicado
                $nuevoStock = $cantidad;
                break;
            default:
                throw new Exception('Tipo de movimiento no válido');
        }

        // Actualizar stock en materiales
        $sqlUpdate = "UPDATE materiales SET stock = $nuevoStock WHERE id_material = $producto";
        if (!$conexion->query($sqlUpdate)) {
            throw new Exception('Error al actualizar stock: ' . $conexion->error);
        }

        // Insertar movimiento (con referencia opcional a OC o devolución)
        $ocRef = $orden_compra_id ? $orden_compra_id : 'NULL';
        $devRef = $devolucion_id ? $devolucion_id : 'NULL';
        
        $sqlInsert = "INSERT INTO movimientos (tipo, fecha, producto_id, cantidad, usuario_id, notas, orden_compra_id, devolucion_id) 
                      VALUES ('$tipo', '$fecha', $producto, $cantidad, $usuario, '$notas', $ocRef, $devRef)";
        
        if (!$conexion->query($sqlInsert)) {
            throw new Exception('Error al guardar movimiento: ' . $conexion->error);
        }
        
        $movimiento_id = $conexion->insert_id;

        // Si es recibido de OC, actualizar cantidad_recibida en orden_detalles
        if ($tipo === 'recibido' && $orden_compra_id) {
            $sqlUpdateOD = "UPDATE orden_detalles 
                            SET cantidad_recibida = cantidad_recibida + $cantidad 
                            WHERE orden_id = $orden_compra_id AND material_id = $producto";
            $conexion->query($sqlUpdateOD);
        }

        // Confirmar transacción
        $conexion->commit();

        // Registrar en historial de acciones
        $descripcionHistorial = "";
        switch ($tipo) {
            case 'entrada':
                $descripcionHistorial = "Se agregaron $cantidad unidades de $nombreProducto";
                break;
            case 'recibido':
                $descripcionHistorial = "Se recibieron $cantidad unidades de $nombreProducto" . ($orden_compra_id ? " (OC #$orden_compra_id)" : "");
                break;
            case 'salida':
                $descripcionHistorial = "Se retiraron $cantidad unidades de $nombreProducto";
                break;
            case 'devolucion':
                $descripcionHistorial = "Devolución de $cantidad unidades de $nombreProducto";
                break;
            case 'ajuste':
                $descripcionHistorial = "Se ajustó el stock de $nombreProducto a $cantidad unidades (anterior: $stockActual)";
                break;
        }
        registrarHistorial($usuario, $tipo, $descripcionHistorial);

        echo json_encode([
            'status' => 'success', 
            'message' => 'Movimiento guardado correctamente',
            'movimiento_id' => $movimiento_id,
            'stock_anterior' => $stockActual,
            'stock_nuevo' => $nuevoStock
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conexion->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
