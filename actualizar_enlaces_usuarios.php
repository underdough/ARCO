<?php
/**
 * Script de Actualización de Enlaces
 * Actualiza referencias de Usuario.php a gestion_usuarios.php
 * 
 * Uso: php actualizar_enlaces_usuarios.php
 * O acceder desde navegador: http://localhost/ARCO/actualizar_enlaces_usuarios.php
 */

// Configuración
$archivos_a_actualizar = [
    'vistas/productos.php',
    'vistas/categorias.php',
    'vistas/movimientos.php',
    'vistas/reportes.php',
    'vistas/configuracion.php',
    'vistas/Usuario.php'
];

$buscar = 'Usuario.php';
$reemplazar = 'gestion_usuarios.php';

// Colores para terminal
$verde = "\033[32m";
$rojo = "\033[31m";
$azul = "\033[34m";
$amarillo = "\033[33m";
$reset = "\033[0m";

// Detectar si se ejecuta desde navegador
$es_web = php_sapi_name() !== 'cli';

if ($es_web) {
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Actualización de Enlaces - ARCO</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            color: #4CAF50;
            padding: 10px;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            margin: 10px 0;
        }
        .error {
            color: #f44336;
            padding: 10px;
            background: #ffebee;
            border-left: 4px solid #f44336;
            margin: 10px 0;
        }
        .info {
            color: #2196F3;
            padding: 10px;
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            margin: 10px 0;
        }
        .warning {
            color: #ff9800;
            padding: 10px;
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            margin: 10px 0;
        }
        .resultado {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 32px;
            color: #4CAF50;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Actualización de Enlaces - Sistema de Usuarios</h1>
        <div class='info'>
            <strong>Proceso:</strong> Actualizando referencias de 'Usuario.php' a 'gestion_usuarios.php'
        </div>";
}

// Función para imprimir mensajes
function imprimir($mensaje, $tipo = 'info') {
    global $es_web, $verde, $rojo, $azul, $amarillo, $reset;
    
    if ($es_web) {
        echo "<div class='$tipo'>$mensaje</div>";
    } else {
        $color = $reset;
        switch ($tipo) {
            case 'success': $color = $verde; break;
            case 'error': $color = $rojo; break;
            case 'info': $color = $azul; break;
            case 'warning': $color = $amarillo; break;
        }
        echo $color . $mensaje . $reset . "\n";
    }
}

// Iniciar proceso
imprimir("Iniciando actualización de enlaces...", 'info');
imprimir("", 'info');

$actualizados = 0;
$sin_cambios = 0;
$no_encontrados = 0;
$errores = 0;

foreach ($archivos_a_actualizar as $archivo) {
    if (file_exists($archivo)) {
        try {
            $contenido = file_get_contents($archivo);
            $contenido_original = $contenido;
            
            // Contar ocurrencias
            $ocurrencias = substr_count($contenido, $buscar);
            
            if ($ocurrencias > 0) {
                // Realizar reemplazo
                $contenido_nuevo = str_replace($buscar, $reemplazar, $contenido);
                
                // Guardar archivo
                if (file_put_contents($archivo, $contenido_nuevo)) {
                    imprimir("✅ Actualizado: $archivo ($ocurrencias ocurrencias)", 'success');
                    $actualizados++;
                } else {
                    imprimir("❌ Error al escribir: $archivo", 'error');
                    $errores++;
                }
            } else {
                imprimir("ℹ️ Sin cambios: $archivo", 'info');
                $sin_cambios++;
            }
        } catch (Exception $e) {
            imprimir("❌ Error procesando $archivo: " . $e->getMessage(), 'error');
            $errores++;
        }
    } else {
        imprimir("⚠️ No encontrado: $archivo", 'warning');
        $no_encontrados++;
    }
}

// Resumen
if ($es_web) {
    echo "<div class='stats'>
        <div class='stat-card'>
            <h3>$actualizados</h3>
            <p>Actualizados</p>
        </div>
        <div class='stat-card'>
            <h3>$sin_cambios</h3>
            <p>Sin cambios</p>
        </div>
        <div class='stat-card'>
            <h3>$errores</h3>
            <p>Errores</p>
        </div>
    </div>";
}

imprimir("", 'info');
imprimir("═══════════════════════════════════════", 'info');
imprimir("RESUMEN DE ACTUALIZACIÓN", 'info');
imprimir("═══════════════════════════════════════", 'info');
imprimir("Archivos actualizados: $actualizados", 'success');
imprimir("Archivos sin cambios: $sin_cambios", 'info');
imprimir("Archivos no encontrados: $no_encontrados", 'warning');
imprimir("Errores: $errores", $errores > 0 ? 'error' : 'info');
imprimir("═══════════════════════════════════════", 'info');

if ($actualizados > 0) {
    imprimir("", 'info');
    imprimir("✅ Proceso completado exitosamente", 'success');
    imprimir("", 'info');
    imprimir("Próximos pasos:", 'info');
    imprimir("1. Verificar que los archivos se actualizaron correctamente", 'info');
    imprimir("2. Acceder a: http://localhost/ARCO/vistas/gestion_usuarios.php", 'info');
    imprimir("3. Probar todas las funcionalidades", 'info');
    imprimir("4. Verificar que los enlaces en el menú funcionan", 'info');
} else if ($sin_cambios === count($archivos_a_actualizar)) {
    imprimir("", 'info');
    imprimir("ℹ️ Todos los archivos ya están actualizados", 'info');
} else if ($errores > 0) {
    imprimir("", 'error');
    imprimir("❌ Se encontraron errores durante el proceso", 'error');
    imprimir("Revisar permisos de archivos y volver a intentar", 'error');
}

if ($es_web) {
    echo "
        <div class='resultado'>
            <h3>Siguiente Paso</h3>
            <p>Acceder al nuevo sistema de gestión de usuarios:</p>
            <p><a href='vistas/gestion_usuarios.php' style='color: #4CAF50; font-weight: bold;'>
                → Ir a Gestión de Usuarios
            </a></p>
        </div>
    </div>
</body>
</html>";
}
?>
