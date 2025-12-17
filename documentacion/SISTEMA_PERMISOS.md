# Sistema de Permisos Granulares - ARCO

## 📋 Descripción General

Sistema completo de permisos que permite controlar el acceso de los usuarios a cada módulo y acción del sistema ARCO, basado en roles.

## 🎯 Características

- ✅ **Permisos granulares** por módulo y acción
- ✅ **5 roles predefinidos** con permisos específicos
- ✅ **10 módulos** del sistema
- ✅ **8 tipos de permisos** (ver, crear, editar, eliminar, exportar, importar, aprobar, auditar)
- ✅ **Auditoría completa** de cambios en permisos
- ✅ **Fácil integración** en vistas y servicios
- ✅ **API PHP** para verificación de permisos

## 📊 Estructura del Sistema

### Tablas Creadas

1. **modulos**: Módulos del sistema
2. **permisos**: Tipos de permisos/acciones
3. **modulo_permisos**: Permisos disponibles por módulo
4. **rol_permisos**: Permisos asignados a cada rol
5. **auditoria_permisos**: Registro de cambios en permisos

### Módulos del Sistema

| Módulo | Descripción | Permisos Disponibles |
|--------|-------------|---------------------|
| dashboard | Panel de Control | ver |
| productos | Gestión de Productos | ver, crear, editar, eliminar, exportar, importar |
| categorias | Gestión de Categorías | ver, crear, editar, eliminar |
| movimientos | Movimientos de Inventario | ver, crear, editar, aprobar, exportar |
| usuarios | Gestión de Usuarios | ver, crear, editar, eliminar, auditar |
| reportes | Reportes y Estadísticas | ver, crear, exportar |
| configuracion | Configuración del Sistema | ver, editar |
| ordenes_compra | Órdenes de Compra | ver, crear, editar, aprobar, exportar |
| devoluciones | Gestión de Devoluciones | ver, crear, editar, aprobar |
| recepcion | Recepción de Materiales | ver, crear, editar |

### Tipos de Permisos

| Código | Nombre | Descripción |
|--------|--------|-------------|
| ver | Ver | Ver/Consultar información |
| crear | Crear | Crear/Agregar nuevos registros |
| editar | Editar | Editar/Modificar registros existentes |
| eliminar | Eliminar | Eliminar registros |
| exportar | Exportar | Exportar datos a archivos |
| importar | Importar | Importar datos desde archivos |
| aprobar | Aprobar | Aprobar operaciones |
| auditar | Auditar | Ver registros de auditoría |

## 👥 Permisos por Rol

### 👑 Administrador
**Acceso completo a todos los módulos y acciones**

- Dashboard: ver
- Productos: ver, crear, editar, eliminar, exportar, importar
- Categorías: ver, crear, editar, eliminar
- Movimientos: ver, crear, editar, aprobar, exportar
- Usuarios: ver, crear, editar, eliminar, auditar
- Reportes: ver, crear, exportar
- Configuración: ver, editar
- Órdenes de Compra: ver, crear, editar, aprobar, exportar
- Devoluciones: ver, crear, editar, aprobar
- Recepción: ver, crear, editar

### 💼 Gerente
**Acceso amplio excepto gestión completa de usuarios**

- Dashboard: ver
- Productos: ver, crear, editar, exportar, importar
- Categorías: ver, crear, editar
- Movimientos: ver, crear, editar, aprobar, exportar
- Usuarios: ver (solo consulta)
- Reportes: ver, crear, exportar
- Configuración: ver, editar
- Órdenes de Compra: ver, crear, editar, aprobar, exportar
- Devoluciones: ver, crear, editar, aprobar
- Recepción: ver, crear, editar

### 👁️ Supervisor
**Supervisión y aprobación de operaciones**

- Dashboard: ver
- Productos: ver, exportar
- Categorías: ver
- Movimientos: ver, aprobar, exportar
- Reportes: ver, exportar
- Órdenes de Compra: ver, aprobar
- Devoluciones: ver, aprobar
- Recepción: ver

### 📦 Almacenista
**Gestión operativa de inventario**

- Dashboard: ver
- Productos: ver, crear, editar
- Categorías: ver
- Movimientos: ver, crear, editar
- Reportes: ver
- Recepción: ver, crear, editar
- Devoluciones: ver, crear

### 👤 Usuario
**Acceso básico de consulta**

- Dashboard: ver
- Productos: ver
- Categorías: ver
- Movimientos: ver
- Reportes: ver

## 🚀 Instalación

### Paso 1: Ejecutar Script SQL

```bash
mysql -u root -p arco_bdd < base-datos/sistema_permisos_completo.sql
```

### Paso 2: Verificar Instalación

```sql
-- Ver módulos creados
SELECT * FROM modulos ORDER BY orden;

-- Ver permisos creados
SELECT * FROM permisos;

-- Ver permisos de un rol
SELECT 
    rp.rol,
    m.nombre AS modulo,
    p.nombre AS permiso
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador'
ORDER BY m.orden;
```

## 💻 Uso en PHP

### Incluir el Sistema

```php
require_once '../servicios/verificar_permisos.php';
```

### Verificar Permiso Específico

```php
$rol = $_SESSION['rol'];

// Verificar si puede crear productos
if (tienePermiso($rol, 'productos', 'crear')) {
    // Mostrar botón de crear
    echo '<button>Nuevo Producto</button>';
}
```

### Verificar Acceso a Módulo

```php
// Verificar que puede acceder al módulo
if (!puedeAccederModulo($_SESSION['rol'], 'productos')) {
    header("Location: dashboard.php?error=Sin acceso");
    exit;
}
```

### Obtener Todos los Permisos de un Módulo

```php
$permisos = obtenerPermisosModulo($_SESSION['rol'], 'productos');
// Retorna: ['ver', 'crear', 'editar', ...]
```

### Obtener Módulos Accesibles

```php
$modulos = obtenerModulosAccesibles($_SESSION['rol']);
// Retorna array de módulos con sus permisos
```

### Middleware (Proteger Servicios)

```php
// En servicios/crear_producto.php
require_once 'verificar_permisos.php';

// Verificar permiso antes de continuar
requierePermiso('productos', 'crear');

// Si llega aquí, tiene permiso
// Continuar con la lógica
```

## 🎨 Uso en Vistas

### Mostrar Botones Según Permisos

```php
<?php
$puede_crear = tienePermiso($_SESSION['rol'], 'productos', 'crear');
$puede_editar = tienePermiso($_SESSION['rol'], 'productos', 'editar');
$puede_eliminar = tienePermiso($_SESSION['rol'], 'productos', 'eliminar');
?>

<div class="action-buttons">
    <?php if ($puede_crear): ?>
        <button onclick="crearProducto()">Nuevo Producto</button>
    <?php endif; ?>
</div>

<table>
    <tr>
        <td>Producto A</td>
        <td>
            <?php if ($puede_editar): ?>
                <button>Editar</button>
            <?php endif; ?>
            
            <?php if ($puede_eliminar): ?>
                <button>Eliminar</button>
            <?php endif; ?>
        </td>
    </tr>
</table>
```

### Generar Menú Dinámico

```php
<?php
$modulos = obtenerModulosAccesibles($_SESSION['rol']);

foreach ($modulos as $modulo):
?>
    <a href="<?php echo $modulo['ruta']; ?>">
        <i class="fas <?php echo $modulo['icono']; ?>"></i>
        <?php echo $modulo['descripcion']; ?>
    </a>
<?php endforeach; ?>
```

## 🌐 Uso en JavaScript

### Obtener Permisos vía AJAX

```javascript
fetch('../servicios/obtener_permisos_usuario.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Rol:', data.rol);
            console.log('Módulos:', data.modulos);
            console.log('Permisos:', data.matriz_permisos);
            
            // Actualizar interfaz según permisos
            actualizarInterfaz(data);
        }
    });
```

### Habilitar/Deshabilitar Botones

```javascript
function actualizarInterfaz(permisos) {
    const moduloProductos = permisos.matriz_permisos.productos;
    
    if (!moduloProductos.crear || !moduloProductos.crear.activo) {
        document.querySelector('.btn-crear').disabled = true;
    }
    
    if (!moduloProductos.eliminar || !moduloProductos.eliminar.activo) {
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.disabled = true;
        });
    }
}
```

## 📝 Ejemplos Completos

Ver archivo: `ejemplos/ejemplo_uso_permisos.php`

## 🔧 Funciones Disponibles

### tienePermiso($rol, $modulo, $permiso)
Verifica si un rol tiene un permiso específico en un módulo.

**Parámetros:**
- `$rol`: Rol del usuario
- `$modulo`: Nombre del módulo
- `$permiso`: Código del permiso

**Retorna:** `bool`

### obtenerPermisosModulo($rol, $modulo)
Obtiene todos los permisos de un rol para un módulo.

**Retorna:** `array` de códigos de permisos

### obtenerModulosAccesibles($rol)
Obtiene todos los módulos a los que un rol tiene acceso.

**Retorna:** `array` de módulos con sus permisos

### puedeAccederModulo($rol, $modulo)
Verifica si un usuario puede acceder a un módulo.

**Retorna:** `bool`

### requierePermiso($modulo, $permiso)
Middleware que verifica permisos y redirige si no los tiene.

### obtenerMatrizPermisos($rol)
Obtiene matriz completa de permisos para debugging.

**Retorna:** `array` asociativo

## 🔐 Seguridad

### Mejores Prácticas

1. **Siempre verificar permisos** en backend (PHP)
2. **Verificar en frontend** solo para UX
3. **Usar middleware** en servicios críticos
4. **Auditar cambios** en permisos
5. **Revisar permisos** regularmente

### Ejemplo de Protección Completa

```php
// En la vista
<?php
require_once '../servicios/verificar_permisos.php';

if (!puedeAccederModulo($_SESSION['rol'], 'productos')) {
    header("Location: dashboard.php?error=Sin acceso");
    exit;
}

$puede_crear = tienePermiso($_SESSION['rol'], 'productos', 'crear');
?>

<!-- En el servicio -->
<?php
// servicios/crear_producto.php
require_once 'verificar_permisos.php';

requierePermiso('productos', 'crear');

// Continuar con la lógica
?>
```

## 📊 Consultas Útiles

### Ver Permisos de un Rol

```sql
SELECT 
    m.nombre AS modulo,
    GROUP_CONCAT(p.nombre ORDER BY p.nombre) AS permisos
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'almacenista' AND rp.activo = 1
GROUP BY m.nombre
ORDER BY m.orden;
```

### Comparar Permisos entre Roles

```sql
SELECT 
    m.nombre AS modulo,
    MAX(CASE WHEN rp.rol = 'administrador' THEN 1 ELSE 0 END) AS admin,
    MAX(CASE WHEN rp.rol = 'gerente' THEN 1 ELSE 0 END) AS gerente,
    MAX(CASE WHEN rp.rol = 'supervisor' THEN 1 ELSE 0 END) AS supervisor,
    MAX(CASE WHEN rp.rol = 'almacenista' THEN 1 ELSE 0 END) AS almacenista,
    MAX(CASE WHEN rp.rol = 'usuario' THEN 1 ELSE 0 END) AS usuario
FROM modulos m
LEFT JOIN rol_permisos rp ON m.id_modulo = rp.id_modulo AND rp.activo = 1
GROUP BY m.nombre
ORDER BY m.orden;
```

### Auditoría de Cambios

```sql
SELECT 
    ap.fecha_accion,
    ap.rol,
    ap.accion,
    m.nombre AS modulo,
    p.nombre AS permiso,
    CONCAT(u.nombre, ' ', u.apellido) AS realizado_por
FROM auditoria_permisos ap
LEFT JOIN modulos m ON ap.id_modulo = m.id_modulo
LEFT JOIN permisos p ON ap.id_permiso = p.id_permiso
LEFT JOIN usuarios u ON ap.realizado_por = u.id_usuarios
ORDER BY ap.fecha_accion DESC
LIMIT 50;
```

## 🎓 Casos de Uso

### Caso 1: Almacenista Registra Entrada

```php
// Vista: movimientos.php
if (!tienePermiso($_SESSION['rol'], 'movimientos', 'crear')) {
    echo "No puede registrar movimientos";
    exit;
}

// Mostrar formulario de entrada
```

### Caso 2: Supervisor Aprueba Movimiento

```php
// Vista: movimientos.php
if (tienePermiso($_SESSION['rol'], 'movimientos', 'aprobar')) {
    echo '<button onclick="aprobar()">Aprobar</button>';
}

// Servicio: aprobar_movimiento.php
requierePermiso('movimientos', 'aprobar');
// Procesar aprobación
```

### Caso 3: Gerente Exporta Reporte

```php
// Vista: reportes.php
if (tienePermiso($_SESSION['rol'], 'reportes', 'exportar')) {
    echo '<button onclick="exportar()">Exportar PDF</button>';
}
```

## 🔄 Actualización del archivo de requerimientos

El sistema de permisos cumple con el requerimiento:

✅ **Gestionar permisos a los usuarios**: Sistema completo implementado
- Permisos granulares por módulo y acción
- 5 roles con permisos específicos
- Fácil integración en vistas y servicios
- Auditoría de cambios
- API PHP completa

---

**Fecha de creación:** Diciembre 2025  
**Versión:** 1.0  
**Estado:** ✅ Completado y funcional
