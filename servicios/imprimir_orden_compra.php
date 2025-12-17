<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID de orden no proporcionado.");
}

$id = (int) $_GET['id'];
$conexion = ConectarDB();

// Obtener datos de empresa
$sqlEmpresa = "SELECT * FROM empresa LIMIT 1";
$resEmpresa = $conexion->query($sqlEmpresa);
$empresa = $resEmpresa ? $resEmpresa->fetch_assoc() : null;

// Obtener orden
$sql = "SELECT oc.*, u.nombre AS usuario_nombre
        FROM ordenes_compra oc
        LEFT JOIN usuarios u ON oc.usuario_id = u.id_usuarios
        WHERE oc.id = $id";

$resultado = $conexion->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
    die("Orden no encontrada.");
}

$orden = $resultado->fetch_assoc();

// Obtener detalles
$sqlDetalles = "SELECT od.*, m.nombre_material
                FROM orden_detalles od
                LEFT JOIN materiales m ON od.material_id = m.id_material
                WHERE od.orden_id = $id";
$resDetalles = $conexion->query($sqlDetalles);
$detalles = [];
while ($fila = $resDetalles->fetch_assoc()) {
    $detalles[] = $fila;
}

$estadoLabels = [
    'pendiente' => ['label' => 'Pendiente', 'color' => '#ffc107'],
    'recibida' => ['label' => 'Recibida', 'color' => '#28a745'],
    'cancelada' => ['label' => 'Cancelada', 'color' => '#dc3545'],
    'parcial' => ['label' => 'Parcialmente Recibida', 'color' => '#17a2b8']
];
$estadoInfo = $estadoLabels[$orden['estado']] ?? ['label' => $orden['estado'], 'color' => '#666'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra - <?= htmlspecialchars($orden['numero_orden']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-grid div { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f5f5f5; }
        .total { text-align: right; font-size: 18px; font-weight: bold; margin-top: 10px; }
        .estado { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .firma { width: 200px; text-align: center; border-top: 1px solid #333; padding-top: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1><?= $empresa ? htmlspecialchars($empresa['nombre']) : 'ARCO' ?></h1>
        <p><?= $empresa ? htmlspecialchars($empresa['direccion']) : '' ?></p>
        <p><?= $empresa ? htmlspecialchars($empresa['telefono']) : '' ?></p>
    </div>
    
    <h2 style="text-align: center;">ORDEN DE COMPRA</h2>
    
    <div class="info-grid">
        <div>
            <p><strong>N° Orden:</strong> <?= htmlspecialchars($orden['numero_orden']) ?></p>
            <p><strong>Proveedor:</strong> <?= htmlspecialchars($orden['proveedor']) ?></p>
            <p><strong>Fecha Pedido:</strong> <?= date('d/m/Y', strtotime($orden['fecha_pedido'])) ?></p>
            <?php if ($orden['fecha_esperada']): ?>
            <p><strong>Fecha Esperada:</strong> <?= date('d/m/Y', strtotime($orden['fecha_esperada'])) ?></p>
            <?php endif; ?>
        </div>
        <div style="text-align: right;">
            <p><strong>Estado:</strong> <span class="estado" style="background: <?= $estadoInfo['color'] ?>"><?= $estadoInfo['label'] ?></span></p>
            <p><strong>Solicitado por:</strong> <?= htmlspecialchars($orden['usuario_nombre']) ?></p>
            <p><strong>Fecha Creación:</strong> <?= date('d/m/Y H:i', strtotime($orden['created_at'])) ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Material</th>
                <th>Cantidad Pedida</th>
                <th>Cantidad Recibida</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $i => $det): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($det['nombre_material']) ?></td>
                <td><?= $det['cantidad_pedida'] ?></td>
                <td><?= $det['cantidad_recibida'] ?></td>
                <td>$<?= number_format($det['precio_unitario'], 2) ?></td>
                <td>$<?= number_format($det['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total">
        TOTAL: $<?= number_format($orden['total'], 2) ?>
    </div>
    
    <?php if ($orden['notas']): ?>
    <p><strong>Observaciones:</strong> <?= htmlspecialchars($orden['notas']) ?></p>
    <?php endif; ?>
    
    <div class="footer">
        <div class="firma">Solicitante</div>
        <div class="firma">Aprobado por</div>
        <div class="firma">Proveedor</div>
    </div>
    
    <p style="text-align: center; margin-top: 30px; font-size: 12px; color: #999;">
        Impreso el <?= date('d/m/Y H:i:s') ?>
    </p>
    
    <br>
    <button class="no-print" onclick="window.history.back()">Volver</button>
</body>
</html>
