<?php
/**
 * API del Chatbot Local - Sistema ARCO
 * Responde preguntas sobre el sistema y proporciona ayuda
 */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

header('Content-Type: application/json');

require_once 'conexion.php';
$conexion = ConectarDB();

try {
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    
    if (empty($mensaje)) {
        throw new Exception('Mensaje vacío');
    }
    
    // Obtener información del usuario
    $usuario_id = $_SESSION['usuario_id'];
    $rol = $_SESSION['rol'] ?? 'usuario';
    
    // Procesar mensaje y obtener respuesta
    $respuesta = procesarMensaje($mensaje, $rol, $conexion);
    
    echo json_encode([
        'success' => true,
        'respuesta' => $respuesta,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();

/**
 * Procesa el mensaje del usuario y retorna respuesta
 */
function procesarMensaje($mensaje, $rol, $conexion) {
    $mensaje_lower = strtolower($mensaje);
    
    // Respuestas por palabras clave
    $respuestas = [
        // Saludos
        ['palabras' => ['hola', 'buenos', 'hi', 'hey'], 'respuesta' => '¡Hola! Soy el asistente del sistema ARCO. ¿En qué puedo ayudarte? Puedo responder preguntas sobre módulos, procedimientos o información del sistema con palabras claves como, para Módulos: dashboard/inicio, Procedimientos: permisos, Funcionalidades: 2FA.'],
        
        // Dashboard
        ['palabras' => ['dashboard', 'inicio', 'panel'], 'respuesta' => '📊 El Dashboard es tu panel principal. Muestra estadísticas en tiempo real, gráficos de movimientos, alertas de stock bajo y accesos rápidos a los módulos. Puedes ver tu nombre y rol en la esquina superior derecha.'],
        
        // Categorías
        ['palabras' => ['categoría', 'categorias', 'categoria'], 'respuesta' => '📁 En Gestión de Categorías puedes crear, editar y eliminar categorías. Incluye filtros por estado (activas/inactivas) y ordenamiento por nombre, productos o fecha. Usa la búsqueda para encontrar rápidamente.'],
        
        // Productos
        ['palabras' => ['producto', 'productos', 'material', 'materiales'], 'respuesta' => '📦 En Gestión de Productos administras tu catálogo. Puedes crear productos, asignarles categoría, definir stock inicial, ubicación y estado. Usa la búsqueda y filtros para encontrar productos rápidamente.'],
        
        // Movimientos
        ['palabras' => ['movimiento', 'movimientos', 'entrada', 'salida', 'registro'], 'respuesta' => '↔️ Los Movimientos registran todas las operaciones de entrada y salida. Puedes registrar movimientos, filtrar por fecha/tipo/producto, ver historial e imprimir comprobantes. Cada movimiento actualiza automáticamente el stock.'],
        
        // Órdenes
        ['palabras' => ['orden', 'ordenes', 'compra', 'proveedor'], 'respuesta' => '🛒 Las Órdenes de Compra te permiten gestionar compras a proveedores. Puedes crear órdenes, agregar múltiples productos, cambiar estado (Pendiente/Recibida/Cancelada) e imprimir documentos.'],
        
        // Devoluciones
        ['palabras' => ['devolución', 'devolucion', 'devolver'], 'respuesta' => '↩️ En Devoluciones registras productos devueltos. Especifica el motivo (Defecto, Cambio, Exceso), cantidad y descripción. Los estados son: Registrada, Procesada, Rechazada, Reembolsada.'],
        
        // Anomalías
        ['palabras' => ['anomalía', 'anomalia', 'problema', 'incidencia'], 'respuesta' => '⚠️ Las Anomalías registran problemas en el inventario (Faltante, Sobrante, Dañado, Vencido). Puedes crear anomalías, asignar responsables, cambiar estado y ver reportes de resolución.'],
        
        // Estadísticas
        ['palabras' => ['estadística', 'estadisticas', 'gráfico', 'grafico', 'análisis', 'analisis'], 'respuesta' => '📈 El módulo de Estadísticas muestra 5 gráficos interactivos: Resumen general, Movimientos por mes, Distribución por categorías, Stock por categorías y Tipos de movimiento. Acceso: Administrador, Gerente, Supervisor.'],
        
        // Reportes
        ['palabras' => ['reporte', 'reportes', 'informe', 'informes', 'exportar'], 'respuesta' => '📄 En Reportes puedes generar reportes personalizados de movimientos, inventario, usuarios y anomalías. Exporta en PDF, Excel o CSV. Usa filtros por fecha, categoría y otros criterios.'],
        
        // Usuarios
        ['palabras' => ['usuario', 'usuarios', 'cuenta', 'cuentas'], 'respuesta' => '👥 En Gestión de Usuarios administras cuentas. Puedes crear usuarios, asignar roles (Administrador, Gerente, Supervisor, Almacenista, Funcionario), cambiar estado y resetear contraseñas.'],
        
        // Permisos
        ['palabras' => ['permiso', 'permisos', 'acceso', 'rol', 'roles'], 'respuesta' => '🔐 El sistema de Permisos controla qué puede hacer cada usuario. Cada módulo tiene permisos: Ver, Crear, Editar, Eliminar. Puedes asignar permisos por rol o por usuario individual.'],
        
        // Configuración
        ['palabras' => ['configuración', 'configuracion', 'empresa', 'backup', 'seguridad'], 'respuesta' => '⚙️ En Configuración del Sistema puedes: Editar datos de empresa (para comprobantes), configurar email SMTP, ajustar seguridad, crear backups y gestionar auditoría.'],
        
        // 2FA
        ['palabras' => ['2fa', 'dos factores', 'autenticación', 'autenticacion', 'código', 'codigo'], 'respuesta' => '🔒 El sistema usa Autenticación de Dos Factores (2FA). Al iniciar sesión, recibes un código de 6 dígitos por email. Ingrésalo para completar la autenticación. Puedes marcar dispositivos como "confiables" por 30 días.'],
        
        // Contraseña
        ['palabras' => ['contraseña', 'contrasena', 'olvide', 'olvidé', 'reset'], 'respuesta' => '🔑 Si olvidaste tu contraseña, en la pantalla de login haz clic en "¿Olvidaste tu contraseña?". Ingresa tu email y recibirás un enlace para restablecerla. El enlace expira en 24 horas.'],
        
        // Búsqueda
        ['palabras' => ['buscar', 'búsqueda', 'busqueda', 'encontrar', 'search'], 'respuesta' => '🔍 Usa la barra de búsqueda en cada módulo para encontrar rápidamente. Busca por nombre, descripción o cualquier campo visible. La búsqueda es en tiempo real mientras escribes.'],
        
        // Filtros
        ['palabras' => ['filtro', 'filtros', 'filtrar', 'ordenar'], 'respuesta' => '🎯 Los filtros te permiten organizar datos. Haz clic en "Filtrar" para ver opciones. Puedes filtrar por estado, fecha, categoría y ordenar por nombre, cantidad, fecha, etc.'],
        
        // Paginación
        ['palabras' => ['página', 'pagina', 'paginación', 'paginacion', 'siguiente', 'anterior'], 'respuesta' => '📑 La paginación muestra 10 registros por página. Usa los botones de navegación para ir a otras páginas. Se muestran máximo 5 botones de página visibles.'],
        
        // Impresión
        ['palabras' => ['imprimir', 'impresión', 'impresion', 'pdf', 'comprobante'], 'respuesta' => '🖨️ Puedes imprimir comprobantes de movimientos, órdenes y devoluciones. Haz clic en el icono de impresora. Se genera un PDF con información de empresa y detalles de la operación.'],
        
        // Stock
        ['palabras' => ['stock', 'inventario', 'cantidad', 'disponible'], 'respuesta' => '📊 El stock se actualiza automáticamente con cada movimiento. Puedes ver el stock actual en Productos. El sistema alerta cuando el stock está bajo. Configura el mínimo en Configuración.'],
        
        // Ayuda general
        ['palabras' => ['ayuda', 'help', 'cómo', 'como', 'qué', 'que'], 'respuesta' => '💡 Puedo ayudarte con: módulos del sistema, procedimientos, permisos, seguridad, búsqueda, filtros y más. ¿Hay algo específico que necesites?'],
    ];
    
    // Buscar coincidencia
    foreach ($respuestas as $item) {
        foreach ($item['palabras'] as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return $item['respuesta'];
            }
        }
    }
    
    // Si no encuentra coincidencia, buscar en documentación
    $respuesta_bd = buscarEnDocumentacion($mensaje, $conexion);
    if ($respuesta_bd) {
        return $respuesta_bd;
    }
    
    // Respuesta por defecto
    return 'No entendí tu pregunta. 🤔 Escribe "opciones" para ver un menú de temas que puedo ayudarte, o reformula tu pregunta. Estoy aquí para ayudarte.';
}

/**
 * Genera un menú de opciones amigables
 */
function generarOpciones() {
    return "📋 **Aquí están los temas que puedo ayudarte:**\n\n" .
           "📊 **Módulos Principales:**\n" .
           "• Dashboard - Panel principal\n" .
           "• Categorías - Gestión de categorías\n" .
           "• Productos - Gestión de productos\n" .
           "• Movimientos - Registro de entrada/salida\n" .
           "• Órdenes de Compra - Gestión de compras\n" .
           "• Devoluciones - Control de devoluciones\n" .
           "• Anomalías - Registro de problemas\n" .
           "• Estadísticas - Gráficos y análisis\n" .
           "• Reportes - Generación de reportes\n\n" .
           "👥 **Administración:**\n" .
           "• Usuarios - Gestión de cuentas\n" .
           "• Permisos - Control de acceso\n" .
           "• Configuración - Parámetros del sistema\n\n" .
           "🔒 **Seguridad:**\n" .
           "• 2FA - Autenticación de dos factores\n" .
           "• Contraseña - Recuperación de contraseña\n\n" .
           "💡 **Procedimientos:**\n" .
           "• Búsqueda - Cómo buscar información\n" .
           "• Filtros - Cómo usar filtros\n" .
           "• Impresión - Cómo imprimir comprobantes\n\n" .
           "¿Sobre cuál tema necesitas ayuda?";
}

/**
 * Busca información en la base de datos
 */
function buscarEnDocumentacion($mensaje, $conexion) {
    // Búsqueda simple en tabla de categorías
    $query = "SELECT nombre_cat FROM categorias WHERE nombre_cat LIKE ? LIMIT 1";
    $stmt = $conexion->prepare($query);
    
    if ($stmt) {
        $search = "%{$mensaje}%";
        $stmt->bind_param('s', $search);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return "Encontré la categoría: " . htmlspecialchars($row['nombre_cat']) . ". ¿Necesitas más información sobre esta categoría?";
        }
    }
    
    return null;
}
