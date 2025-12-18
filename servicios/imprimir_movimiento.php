<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID de movimiento no proporcionado.");
}

$id = (int) $_GET['id'];
$conexion = ConectarDB();

// Obtener datos de empresa
$sqlEmpresa = "SELECT * FROM empresa WHERE id = 2";
$resEmpresa = $conexion->query($sqlEmpresa);
$empresa = $resEmpresa && $resEmpresa->num_rows > 0 ? $resEmpresa->fetch_assoc() : null;

$sql = "SELECT 
            m.id, 
            m.fecha, 
            m.tipo, 
            m.producto_id, 
            m.cantidad, 
            m.notas,
            m.orden_compra_id,
            m.devolucion_id,
            mat.nombre_material,
            mat.stock as stock_actual,
            u.nombre AS usuario_nombre
        FROM movimientos m
        LEFT JOIN usuarios u ON m.usuario_id = u.id_usuarios
        LEFT JOIN materiales mat ON m.producto_id = mat.id_material
        WHERE m.id = $id";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    $mov = $resultado->fetch_assoc();
} else {
    die("Movimiento no encontrado.");
}

// Etiquetas según tipo
$tipoLabels = [
    'entrada' => 'Entrada de Inventario',
    'salida' => 'Salida de Inventario',
    'ajuste' => 'Ajuste de Inventario',
    'recibido' => 'Recepción de Material',
    'devolucion' => 'Devolución de Material'
];
$tipoLabel = isset($tipoLabels[$mov['tipo']]) ? $tipoLabels[$mov['tipo']] : ucfirst($mov['tipo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante - <?= $tipoLabel ?></title>
    <link rel="stylesheet" href="../componentes/imprimir_mov.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .comprobante-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .comprobante-info div { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f5f5f5; }
        .tipo-entrada { color: #28a745; }
        .tipo-salida { color: #dc3545; }
        .tipo-ajuste { color: #ffc107; }
        .tipo-recibido { color: #17a2b8; }
        .tipo-devolucion { color: #6c757d; }
        .footer { margin-top: 40px; display: flex; justify-content: space-between; }
        .firma { width: 200px; text-align: center; border-top: 1px solid #333; padding-top: 5px; }
        @media print { .no-print { display: none; } }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.print();
        });
    </script>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; gap: 20px; justify-content: center; margin-bottom: 15px;">
            <?php if ($empresa && !empty($empresa['logo'])): ?>
            <img src="<?= htmlspecialchars($empresa['logo']) ?>" alt="Logo" style="max-height: 80px; max-width: 150px;">
            <?php endif; ?>
            <div>
                <h1 style="margin: 0;"><?= $empresa ? htmlspecialchars($empresa['nombre']) : 'ARCO' ?></h1>
                <p style="margin: 5px 0; color: #666;">NIT: <?= $empresa ? htmlspecialchars($empresa['nif']) : '' ?></p>
            </div>
        </div>
        <p><?= $empresa ? htmlspecialchars($empresa['direccion']) : '' ?></p>
        <p><?= $empresa ? htmlspecialchars($empresa['telefono']) : '' ?> | <?= $empresa ? htmlspecialchars($empresa['email']) : '' ?></p>
    </div>
    
    <h2 style="text-align: center;">COMPROBANTE DE <?= strtoupper($tipoLabel) ?></h2>
    
    <div class="comprobante-info">
        <div>
            <p><strong>N° Comprobante:</strong> MOV-<?= str_pad($mov['id'], 6, '0', STR_PAD_LEFT) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($mov['fecha'])) ?></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Tipo:</strong> <span class="tipo-<?= $mov['tipo'] ?>"><?= $tipoLabel ?></span></p>
            <p><strong>Usuario:</strong> <?= htmlspecialchars($mov['usuario_nombre']) ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Stock Actual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($mov['nombre_material']) ?></td>
                <td><?= $mov['cantidad'] ?></td>
                <td><?= $mov['stock_actual'] ?></td>
            </tr>
        </tbody>
    </table>
    
    <?php if ($mov['orden_compra_id']): ?>
    <p><strong>Orden de Compra Relacionada:</strong> OC-<?= $mov['orden_compra_id'] ?></p>
    <?php endif; ?>
    
    <?php if ($mov['devolucion_id']): ?>
    <p><strong>Devolución Relacionada:</strong> DEV-<?= $mov['devolucion_id'] ?></p>
    <?php endif; ?>
    
    <?php if ($mov['notas']): ?>
    <p><strong>Observaciones:</strong> <?= htmlspecialchars($mov['notas']) ?></p>
    <?php endif; ?>
    
    <div class="footer">
        <div class="firma">Entrega</div>
        <div class="firma">Recibe</div>
        <div class="firma">Autoriza</div>
    </div>
    
    <p style="text-align: center; margin-top: 30px; font-size: 12px; color: #999;">
        Impreso el <?= date('d/m/Y H:i:s') ?>
    </p>
    
    <br>
    <button class="no-print" onclick="window.history.back()">Volver</button>

    <button class="no-print" onclick="window.location.href='../vistas/movimientos.php'">Volver</button>
</body>
</html>
