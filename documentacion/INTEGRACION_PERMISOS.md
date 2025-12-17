# Guía de Integración del Sistema de Permisos

## Descripción General

Esta guía explica cómo integrar el sistema de permisos granulares en las vistas del sistema ARCO. El sistema permite controlar el acceso a módulos y acciones específicas según el rol del usuario.

## Arquitectura del Sistema

### Componentes Principales

1. **Base de Datos** (`base-datos/sistema_permisos_completo.sql`)
   - 5 tablas: modulos, permisos, modulo_permisos, rol_permisos, auditoria_permisos
   - 10 módulos del sistema
   - 8 tipos de permisos
   - Permisos predefinidos para 5 roles

2. **Servicios PHP**
   - `servicios/verificar_permisos.php` - Funciones de verificación
   - `servicios/middleware_permisos.php` - Middleware de protección
   - `servicios/obtener_permisos_usuario.php` - API JSON

3. **Vistas Protegidas**
   - Archivos PHP con sufijo `_protegido.php`
   - JavaScript con control de permisos

## Integración en Vistas PHP

### Paso 1: Incluir Middleware

```php
<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.html');
    exit();
}

// Incluir middleware de permisos
require_once '../servicios/middleware_permisos.php';

// Verificar acceso al módulo
verificarAccesoModulo('nombre_modulo');

// Obtener permisos del usuario
$permisos = obtenerPermisosUsuario('nombre_modulo');
$infoUsuario = obtenerInfoUsuario();
?>
```

### Paso 2: Pasar Permisos a JavaScript

```php
<script>
    // Permisos del usuario disponibles en JavaScript
    window.userPermissions = <?php echo generarPermisosJS('nombre_modulo'); ?>;
    window.userInfo = <?php echo json_encode($infoUsuario); ?>;
</script>
```

### Paso 3: Controlar Visibilidad de Elementos

```php
<!-- Botón visible solo si tiene permiso de crear -->
<button id="btnCreate" <?php echo mostrarSiTienePermiso('nombre_modulo', 'crear'); ?>>
    <i class="fas fa-plus"></i> Crear
</button>

<!-- Botón visible solo si tiene permiso de exportar -->
<button id="btnExport" <?php echo mostrarSiTienePermiso('nombre_modulo', 'exportar'); ?>>
    <i class="fas fa-file-export"></i> Exportar
</button>
```

## Integración en JavaScript

### Paso 1: Aplicar Permisos al Cargar

```javascript
document.addEventListener('DOMContentLoaded', function() {
    applyPermissions();
    loadData();
});

function applyPermissions() {
    const permisos = window.userPermissions || {};
    
    // Ocultar botones según permisos
    if (!permisos.crear) {
        hideElement('#btnCreate');
    }
    
    if (!permisos.exportar) {
        hideElement('#btnExport');
    }
    
    console.log('Permisos aplicados:', permisos);
}
```

### Paso 2: Verificar Permisos en Acciones

```javascript
function createItem() {
    if (!window.userPermissions.crear) {
        showNotification('No tiene permisos para crear', 'error');
        return;
    }
    
    // Continuar con la acción
}

function editItem(id) {
    if (!window.userPermissions.editar) {
        showNotification('No tiene permisos para editar', 'error');
        return;
    }
    
    // Continuar con la acción
}

function deleteItem(id) {
    if (!window.userPermissions.eliminar) {
        showNotification('No tiene permisos para eliminar', 'error');
        return;
    }
    
    // Continuar con la acción
}
```

### Paso 3: Renderizar Botones Según Permisos

```javascript
function renderActionButtons(item) {
    const permisos = window.userPermissions || {};
    let buttons = '';
    
    if (permisos.editar) {
        buttons += `<button class="edit" data-id="${item.id}">
            <i class="fas fa-edit"></i>
        </button>`;
    }
    
    if (permisos.eliminar) {
        buttons += `<button class="delete" data-id="${item.id}">
            <i class="fas fa-trash"></i>
        </button>`;
    }
    
    if (!permisos.editar && !permisos.eliminar) {
        buttons = '<span>Sin acciones</span>';
    }
    
    return buttons;
}
```

## Funciones del Middleware

### Funciones PHP Disponibles

```php
// Verificar acceso a módulo (redirige si no tiene permiso)
verificarAccesoModulo($modulo);

// Obtener permisos del usuario para un módulo
$permisos = obtenerPermisosUsuario($modulo);
// Retorna: ['ver', 'crear', 'editar', ...]

// Verificar permiso específico
$tiene = usuarioTienePermiso($modulo, $permiso);
// Retorna: true/false

// Mostrar elemento si tiene permiso
echo mostrarSiTienePermiso($modulo, $permiso);
// Retorna: '' o 'style="display: none;"'

// Deshabilitar elemento si no tiene permiso
echo deshabilitarSiNoTienePermiso($modulo, $permiso);
// Retorna: '' o 'disabled'

// Obtener información del usuario
$info = obtenerInfoUsuario();
// Retorna: ['id', 'nombre', 'apellido', 'rol', ...]

// Generar JSON de permisos para JavaScript
echo generarPermisosJS($modulo);
// Retorna: {"ver":true,"crear":true,...}
```

## Permisos por Rol

### Administrador
- Acceso completo a todos los módulos
- Todos los permisos: ver, crear, editar, eliminar, exportar, importar, aprobar, auditar

### Gerente
- Acceso amplio excepto gestión completa de usuarios
- Productos: ver, crear, editar, exportar, importar
- Movimientos: todos los permisos
- Usuarios: solo ver

### Supervisor
- Supervisión y aprobación
- Productos: ver, exportar
- Movimientos: ver, aprobar, exportar
- Órdenes de compra: ver, aprobar

### Almacenista
- Gestión operativa de inventario
- Productos: ver, crear, editar
- Movimientos: ver, crear, editar
- Recepción: ver, crear, editar

### Usuario
- Acceso básico de consulta
- Todos los módulos: solo ver

## Ejemplo Completo: Productos

### Vista PHP (`productos_protegido.php`)

```php
<?php
session_start();
require_once '../servicios/middleware_permisos.php';
verificarAccesoModulo('productos');
$permisos = obtenerPermisosUsuario('productos');
$infoUsuario = obtenerInfoUsuario();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Productos</title>
    <script>
        window.userPermissions = <?php echo generarPermisosJS('productos'); ?>;
        window.userInfo = <?php echo json_encode($infoUsuario); ?>;
    </script>
</head>
<body>
    <button id="btnCreate" <?php echo mostrarSiTienePermiso('productos', 'crear'); ?>>
        Crear Producto
    </button>
    
    <script src="productos_protegido.js"></script>
</body>
</html>
```

### JavaScript (`productos_protegido.js`)

```javascript
document.addEventListener('DOMContentLoaded', function() {
    applyPermissions();
    loadProducts();
});

function applyPermissions() {
    const permisos = window.userPermissions || {};
    
    if (!permisos.crear) {
        hideElement('#btnCreate');
    }
}

function createProduct() {
    if (!window.userPermissions.crear) {
        showNotification('No tiene permisos', 'error');
        return;
    }
    
    // Crear producto
}
```

## Módulos Disponibles

1. **dashboard** - Panel de Control
2. **productos** - Gestión de Productos
3. **categorias** - Gestión de Categorías
4. **movimientos** - Movimientos de Inventario
5. **usuarios** - Gestión de Usuarios
6. **reportes** - Reportes y Estadísticas
7. **configuracion** - Configuración del Sistema
8. **ordenes_compra** - Órdenes de Compra
9. **devoluciones** - Gestión de Devoluciones
10. **recepcion** - Recepción de Materiales

## Permisos Disponibles

1. **ver** - Ver/Consultar información
2. **crear** - Crear/Agregar nuevos registros
3. **editar** - Editar/Modificar registros
4. **eliminar** - Eliminar registros
5. **exportar** - Exportar datos a archivos
6. **importar** - Importar datos desde archivos
7. **aprobar** - Aprobar operaciones
8. **auditar** - Ver registros de auditoría

## Mejores Prácticas

1. **Siempre verificar permisos en el backend**
   - No confiar solo en la verificación del frontend
   - Usar `verificarAccesoModulo()` al inicio de cada vista

2. **Verificar permisos en acciones críticas**
   - Crear, editar, eliminar deben verificar permisos
   - Mostrar mensajes claros al usuario

3. **Ocultar elementos según permisos**
   - Usar `mostrarSiTienePermiso()` en PHP
   - Usar `applyPermissions()` en JavaScript

4. **Registrar acciones en auditoría**
   - Todas las acciones importantes deben registrarse
   - Incluir usuario, fecha, hora y detalles

5. **Mensajes de error claros**
   - Informar al usuario por qué no puede realizar una acción
   - Sugerir contactar al administrador si necesita permisos

## Solución de Problemas

### Error: "No tiene permisos para acceder a este módulo"
- Verificar que el usuario tenga el rol correcto
- Verificar que el rol tenga permisos asignados en `rol_permisos`
- Verificar que el módulo esté activo

### Los botones no se ocultan
- Verificar que `window.userPermissions` esté definido
- Verificar que `applyPermissions()` se ejecute al cargar
- Revisar la consola del navegador para errores

### Permisos no se aplican correctamente
- Verificar que la sesión esté iniciada
- Verificar que `$_SESSION['rol']` esté definido
- Ejecutar el script SQL de permisos nuevamente

## Archivos Creados

1. `vistas/productos_protegido.php` - Vista de productos con permisos
2. `vistas/categorias_protegido.php` - Vista de categorías con permisos
3. `servicios/middleware_permisos.php` - Middleware de protección
4. `SOLOjavascript/productos_protegido.js` - JavaScript con control de permisos
5. `SOLOjavascript/categorias_protegido.js` - JavaScript con control de permisos
6. `documentacion/INTEGRACION_PERMISOS.md` - Esta guía

## Próximos Pasos

1. Integrar permisos en las vistas restantes:
   - movimientos.php
   - reportes.php
   - configuracion.php

2. Crear interfaz de gestión de permisos para administradores

3. Implementar auditoría completa de acciones

4. Crear reportes de uso por rol

## Soporte

Para más información, consultar:
- `documentacion/SISTEMA_PERMISOS.md` - Documentación completa del sistema
- `SISTEMA_PERMISOS_RESUMEN.md` - Resumen ejecutivo
- `ejemplos/ejemplo_uso_permisos.php` - Ejemplos de uso
