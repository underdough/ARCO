<?php
// Script para verificar campos de la tabla anomalias
require_once 'servicios/conexion.php';

try {
    $conn = ConectarDB();
    
    echo "<h2>Estructura de la tabla 'anomalias'</h2>";
    
    $result = $conn->query("DESCRIBE anomalias");
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #395886; color: white;'>";
        echo "<th style='padding: 10px;'>Campo</th>";
        echo "<th style='padding: 10px;'>Tipo</th>";
        echo "<th style='padding: 10px;'>Null</th>";
        echo "<th style='padding: 10px;'>Key</th>";
        echo "<th style='padding: 10px;'>Default</th>";
        echo "</tr>";
        
        $campos_importantes = ['impacto', 'codigo_seguimiento', 'materiales_afectados', 'responsable_asignado'];
        $campos_encontrados = [];
        
        while ($row = $result->fetch_assoc()) {
            $campos_encontrados[] = $row['Field'];
            $color = in_array($row['Field'], $campos_importantes) ? '#e8f5e9' : 'white';
            echo "<tr style='background: $color;'>";
            echo "<td style='padding: 8px;'><strong>{$row['Field']}</strong></td>";
            echo "<td style='padding: 8px;'>{$row['Type']}</td>";
            echo "<td style='padding: 8px;'>{$row['Null']}</td>";
            echo "<td style='padding: 8px;'>{$row['Key']}</td>";
            echo "<td style='padding: 8px;'>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3 style='margin-top: 30px;'>Verificación de Campos Importantes:</h3>";
        echo "<ul>";
        foreach ($campos_importantes as $campo) {
            $existe = in_array($campo, $campos_encontrados);
            $icono = $existe ? '✅' : '❌';
            $color = $existe ? 'green' : 'red';
            echo "<li style='color: $color; font-weight: bold;'>$icono Campo '$campo': " . ($existe ? 'EXISTE' : 'NO EXISTE') . "</li>";
        }
        echo "</ul>";
        
        // Verificar si faltan campos
        $campos_faltantes = array_diff($campos_importantes, $campos_encontrados);
        if (!empty($campos_faltantes)) {
            echo "<div style='background: #fff3cd; border: 2px solid #ffc107; padding: 20px; margin-top: 20px; border-radius: 8px;'>";
            echo "<h3 style='color: #856404;'>⚠️ ACCIÓN REQUERIDA</h3>";
            echo "<p>Faltan los siguientes campos en tu tabla:</p>";
            echo "<ul>";
            foreach ($campos_faltantes as $campo) {
                echo "<li><strong>$campo</strong></li>";
            }
            echo "</ul>";
            echo "<p><strong>Solución:</strong> Ejecuta el script SQL:</p>";
            echo "<code style='background: #f8f9fa; padding: 10px; display: block; border-radius: 5px;'>";
            echo "base-datos/actualizar_anomalias_avanzado.sql";
            echo "</code>";
            echo "<p>O ejecuta manualmente:</p>";
            echo "<textarea style='width: 100%; height: 150px; font-family: monospace; padding: 10px;'>";
            echo "ALTER TABLE anomalias \n";
            echo "ADD COLUMN IF NOT EXISTS codigo_seguimiento VARCHAR(20) UNIQUE NOT NULL DEFAULT '',\n";
            echo "ADD COLUMN IF NOT EXISTS materiales_afectados TEXT DEFAULT NULL,\n";
            echo "ADD COLUMN IF NOT EXISTS responsable_asignado INT DEFAULT NULL,\n";
            echo "ADD COLUMN IF NOT EXISTS impacto ENUM('bajo', 'medio', 'alto', 'critico') DEFAULT 'medio';";
            echo "</textarea>";
            echo "</div>";
        } else {
            echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; margin-top: 20px; border-radius: 8px;'>";
            echo "<h3 style='color: #155724;'>✅ TODO CORRECTO</h3>";
            echo "<p>Todos los campos necesarios existen en tu tabla.</p>";
            echo "</div>";
        }
        
    } else {
        echo "<p style='color: red;'>Error al obtener estructura de la tabla: " . $conn->error . "</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1200px;
        margin: 20px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        background: #395886;
        color: white;
        padding: 15px;
        border-radius: 5px;
    }
    table {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>