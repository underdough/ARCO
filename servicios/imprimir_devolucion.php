<?php
require_once 'conexion.php';

if (!isset($_GET['id'])) {
    die("ID de devolución no proporcionado.");
}

$id = (int) $_GET['id'];
$conexion = ConectarDB();

// Obtener datos de empresa
$sqlEmpresa = "SELECT * FROM empresa WHERE id = 2";
$resEmpresa = $conexion->query($sqlEmpresa);
$empresa = $resEmpresa && $resEmpresa->num_rows > 0 ? $resEmpresa->fetch_assoc() : null;

// Obtener devolución
$sql = "SELECT d.*, m.nombre_material,
        us.nombre as solicitante_nombre,
        up.nombre as procesador_nombre
        FROM devoluciones d
        LEFT JOIN materiales m ON d.material_id = m.id_material
        LEFT JOIN usuarios us ON d.usuario_solicita = us.id_usuarios
        LEFT JOIN usuarios up ON d.usuario_procesa = up.id_usuarios
        WHERE d.id = $id";

$resultado = $conexion->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
    die("Devolución no encontrada.");
}

$dev = $resultado->fetch_assoc();

$motivoLabels = [
    'defectuoso' => 'Producto Defectuoso',
    'incorrecto' => 'Producto Incorrecto',
    'no_requerido' => 'No Requerido',
    'vencido' => 'Producto Vencido',
    'otro' => 'Otro'
];

$estadoLabels = [
    'pendiente' => ['label' => 'Pendiente', 'color' => '#ffc107'],
    'procesada' => ['label' => 'Procesada', 'color' => '#28a745'],
    'rechazada' => ['label' => 'Rechazada', 'color' => '#dc3545']
];

$motivoLabel = $motivoLabels[$dev['motivo']] ?? $dev['motivo'];
$estadoInfo = $estadoLabels[$dev['estado']] ?? ['label' => $dev['estado'], 'color' => '#666'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Devolución - <?= htmlspecialchars($dev['numero_devolucion']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; color: #666; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-grid div { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f5f5f5; width: 30%; }
        .estado { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; }
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
    
    <h2 style="text-align: center;">COMPROBANTE DE DEVOLUCIÓN</h2>
    
    <div class="info-grid">
        <div>
            <p><strong>N° Devolución:</strong> <?= htmlspecialchars($dev['numero_devolucion']) ?></p>
            <p><strong>Fecha Solicitud:</strong> <?= date('d/m/Y H:i', strtotime($dev['fecha_solicitud'])) ?></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Estado:</strong> <span class="estado" style="background: <?= $estadoInfo['color'] ?>"><?= $estadoInfo['label'] ?></span></p>
        </div>
    </div>
    
    <table>
        <tr><th>Material</th><td><?= htmlspecialchars($dev['nombre_material']) ?></td></tr>
        <tr><th>Cantidad</th><td><?= $dev['cantidad'] ?> unidades</td></tr>
        <tr><th>Motivo</th><td><?= $motivoLabel ?></td></tr>
        <tr><th>Descripción</th><td><?= htmlspecialchars($dev['descripcion']) ?: '-' ?></td></tr>
        <tr><th>Solicitado por</th><td><?= htmlspecialchars($dev['solicitante_nombre']) ?></td></tr>
        <?php if ($dev['procesador_nombre']): ?>
        <tr><th>Procesado por</th><td><?= htmlspecialchars($dev['procesador_nombre']) ?></td></tr>
        <tr><th>Fecha Procesado</th><td><?= date('d/m/Y H:i', strtotime($dev['fecha_procesado'])) ?></td></tr>
        <?php endif; ?>
        <?php if ($dev['notas']): ?>
        <tr><th>Observaciones</th><td><?= htmlspecialchars($dev['notas']) ?></td></tr>
        <?php endif; ?>
    </table>
    
    <div class="footer">
        <div class="firma">Solicitante</div>
        <div class="firma">Autoriza</div>
        <div class="firma">Almacén</div>
    </div>
    
    <p style="text-align: center; margin-top: 30px; font-size: 12px; color: #999;">
        Impreso el <?= date('d/m/Y H:i:s') ?>
    </p>
    
    <br>
    <button class="no-print" onclick="window.history.back()">Volver</button>
</body>
</html>
