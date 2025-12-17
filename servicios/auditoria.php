<?php
/**
 * API de Auditoría Global
 * Solo accesible para administradores
 */
session_start();
header('Content-Type: application/json');

include_once "conexion.php";

$conexion = ConectarDB();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

// Verificar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Solo administradores.']);
    exit;
}

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : 'listar';

switch ($accion) {
    case 'listar':
        listarAuditoria($conexion);
        break;
    case 'resumen':
        resumenAuditoria($conexion);
        break;
    case 'exportar':
        exportarAuditoria($conexion);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

function listarAuditoria($conexion) {
    // Filtros opcionales
    $filtroTipo = isset($_GET['tipo']) ? $conexion->real_escape_string($_GET['tipo']) : '';
    $filtroUsuario = isset($_GET['usuario_id']) ? (int) $_GET['usuario_id'] : 0;
    $filtroFechaDesde = isset($_GET['fecha_desde']) ? $conexion->real_escape_string($_GET['fecha_desde']) : '';
    $filtroFechaHasta = isset($_GET['fecha_hasta']) ? $conexion->real_escape_string($_GET['fecha_hasta']) : '';
    $limite = isset($_GET['limite']) ? (int) $_GET['limite'] : 100;
    $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
    $offset = ($pagina - 1) * $limite;
    
    $where = [];
    
    if ($filtroTipo) {
        $where[] = "h.tipo_accion LIKE '%$filtroTipo%'";
    }
    if ($filtroUsuario) {
        $where[] = "h.usuario_id = $filtroUsuario";
    }
    if ($filtroFechaDesde) {
        $where[] = "DATE(h.fecha) >= '$filtroFechaDesde'";
    }
    if ($filtroFechaHasta) {
        $where[] = "DATE(h.fecha) <= '$filtroFechaHasta'";
    }
    
    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Contar total
    $sqlCount = "SELECT COUNT(*) as total FROM historial_acciones h $whereClause";
    $resCount = $conexion->query($sqlCount);
    $total = $resCount->fetch_assoc()['total'];
    
    // Obtener registros
    $sql = "SELECT h.*, u.nombre as usuario_nombre, u.rol as usuario_rol
            FROM historial_acciones h
            LEFT JOIN usuarios u ON h.usuario_id = u.id_usuarios
            $whereClause
            ORDER BY h.fecha DESC
            LIMIT $limite OFFSET $offset";
    
    $resultado = $conexion->query($sql);
    $registros = [];
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            // Categorizar el tipo de acción
            $fila['categoria'] = categorizarAccion($fila['tipo_accion']);
            $registros[] = $fila;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $registros,
        'total' => $total,
        'pagina' => $pagina,
        'limite' => $limite,
        'total_paginas' => ceil($total / $limite)
    ]);
}

function resumenAuditoria($conexion) {
    // Resumen de actividad por tipo
    $sqlTipos = "SELECT tipo_accion, COUNT(*) as cantidad 
                 FROM historial_acciones 
                 GROUP BY tipo_accion 
                 ORDER BY cantidad DESC";
    $resTipos = $conexion->query($sqlTipos);
    $porTipo = [];
    while ($fila = $resTipos->fetch_assoc()) {
        $porTipo[] = $fila;
    }
    
    // Actividad por usuario (últimos 30 días)
    $sqlUsuarios = "SELECT u.nombre, u.rol, COUNT(*) as acciones
                    FROM historial_acciones h
                    JOIN usuarios u ON h.usuario_id = u.id_usuarios
                    WHERE h.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY h.usuario_id
                    ORDER BY acciones DESC
                    LIMIT 10";
    $resUsuarios = $conexion->query($sqlUsuarios);
    $porUsuario = [];
    while ($fila = $resUsuarios->fetch_assoc()) {
        $porUsuario[] = $fila;
    }
    
    // Actividad por día (últimos 7 días)
    $sqlDias = "SELECT DATE(fecha) as dia, COUNT(*) as acciones
                FROM historial_acciones
                WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(fecha)
                ORDER BY dia DESC";
    $resDias = $conexion->query($sqlDias);
    $porDia = [];
    while ($fila = $resDias->fetch_assoc()) {
        $porDia[] = $fila;
    }
    
    // Totales
    $sqlTotal = "SELECT COUNT(*) as total FROM historial_acciones";
    $total = $conexion->query($sqlTotal)->fetch_assoc()['total'];
    
    $sqlHoy = "SELECT COUNT(*) as total FROM historial_acciones WHERE DATE(fecha) = CURDATE()";
    $hoy = $conexion->query($sqlHoy)->fetch_assoc()['total'];
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_registros' => $total,
            'acciones_hoy' => $hoy,
            'por_tipo' => $porTipo,
            'por_usuario' => $porUsuario,
            'por_dia' => $porDia
        ]
    ]);
}

function exportarAuditoria($conexion) {
    // Exportar a CSV (descarga)
    $filtroFechaDesde = isset($_GET['fecha_desde']) ? $conexion->real_escape_string($_GET['fecha_desde']) : date('Y-m-01');
    $filtroFechaHasta = isset($_GET['fecha_hasta']) ? $conexion->real_escape_string($_GET['fecha_hasta']) : date('Y-m-d');
    
    $sql = "SELECT h.id, h.fecha, h.tipo_accion, h.descripcion, u.nombre as usuario, u.rol
            FROM historial_acciones h
            LEFT JOIN usuarios u ON h.usuario_id = u.id_usuarios
            WHERE DATE(h.fecha) BETWEEN '$filtroFechaDesde' AND '$filtroFechaHasta'
            ORDER BY h.fecha DESC";
    
    $resultado = $conexion->query($sql);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=auditoria_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    fputcsv($output, ['ID', 'Fecha', 'Tipo Acción', 'Descripción', 'Usuario', 'Rol']);
    
    while ($fila = $resultado->fetch_assoc()) {
        fputcsv($output, $fila);
    }
    
    fclose($output);
    exit;
}

function categorizarAccion($tipo) {
    $categorias = [
        'movimientos' => ['entrada', 'salida', 'ajuste', 'recibido', 'devolucion'],
        'ordenes_compra' => ['crear_oc', 'actualizar_oc', 'recibir_oc'],
        'devoluciones' => ['crear_devolucion', 'procesar_devolucion', 'rechazar_devolucion'],
        'anomalias' => ['crear_anomalia', 'actualizar_anomalia'],
        'productos' => ['agregar_producto', 'editar_producto', 'eliminar_producto', 'desactivado'],
        'categorias' => ['crear', 'editar', 'eliminar'],
        'usuarios' => ['crear_usuario', 'editar_usuario', 'eliminar_usuario']
    ];
    
    foreach ($categorias as $cat => $tipos) {
        if (in_array($tipo, $tipos)) {
            return $cat;
        }
    }
    
    return 'otros';
}
