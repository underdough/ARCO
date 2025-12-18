# Corrección de Rutas de Logo - Tarea 13

## Problema Identificado

Se detectaron dos problemas relacionados con las rutas del logo:

### 1. Error Trusted Types (Resuelto)
- **Problema**: Uso de `onload="window.print()"` en atributos HTML
- **Solución**: Cambio a event listener `DOMContentLoaded` en archivos de comprobantes
- **Archivos afectados**:
  - `servicios/imprimir_movimiento.php`
  - `servicios/imprimir_orden_compra.php`
  - `servicios/imprimir_devolucion.php`

### 2. Error 404 en Logo (Resuelto)
- **Problema**: Ruta duplicada `/ARCO//ARCO/recursos/logos/...` causaba error 404
- **Causa raíz**: Inconsistencia entre cómo se guardaba la ruta y cómo se accedía desde diferentes vistas
- **Solución**: Estandarizar rutas absolutas desde la raíz del proyecto

## Cambios Realizados

### Archivo: `servicios/guardar_empresa_mejorado.php`
```php
// ANTES:
return 'recursos/logos/' . $filename;

// DESPUÉS:
return '/ARCO/recursos/logos/' . $filename;
```

### Archivo: `vistas/configuracion.php`
```php
// ANTES:
<img src="../<?= htmlspecialchars($empresa['logo']) ?>" ...>

// DESPUÉS:
<img src="<?= htmlspecialchars($empresa['logo']) ?>" ...>
```

## Archivos de Comprobantes (Sin cambios necesarios)
Los siguientes archivos ya usan la ruta correcta:
- `servicios/imprimir_movimiento.php` ✓
- `servicios/imprimir_orden_compra.php` ✓
- `servicios/imprimir_devolucion.php` ✓

## Instrucciones para el Usuario

### ⚠️ IMPORTANTE: Subir Logo Nuevamente

Debido a que la ruta de almacenamiento ha sido corregida, **debe subir el logo nuevamente** para que se guarde con la ruta correcta:

1. Ir a **Configuración** → **Información de Empresa**
2. Seleccionar el archivo de logo (JPG, PNG, GIF o WebP)
3. Hacer clic en **Guardar Información**
4. El logo se guardará con la ruta correcta: `/ARCO/recursos/logos/empresa_logo_[timestamp].[ext]`

### Verificación

Después de subir el logo:
- ✓ Debe aparecer en la vista previa de Configuración
- ✓ Debe aparecer en todos los comprobantes (movimientos, órdenes, devoluciones)
- ✓ No debe haber errores 404 en la consola del navegador

## Estructura de Rutas

```
/ARCO/
├── recursos/
│   └── logos/
│       └── empresa_logo_[timestamp].[ext]  ← Ruta guardada: /ARCO/recursos/logos/...
├── vistas/
│   └── configuracion.php                   ← Accede directamente a la ruta
└── servicios/
    ├── imprimir_movimiento.php             ← Accede directamente a la ruta
    ├── imprimir_orden_compra.php           ← Accede directamente a la ruta
    └── imprimir_devolucion.php             ← Accede directamente a la ruta
```

## Validaciones Implementadas

- ✓ Tamaño máximo: 5MB
- ✓ Formatos permitidos: JPG, PNG, GIF, WebP
- ✓ Redimensionamiento automático: 500x500px
- ✓ Compresión automática
- ✓ Validación MIME type
- ✓ Escape de datos con `htmlspecialchars()`

## Resumen de Cambios

| Archivo | Cambio | Estado |
|---------|--------|--------|
| `servicios/guardar_empresa_mejorado.php` | Ruta guardada a `/ARCO/recursos/logos/` | ✓ Completado |
| `vistas/configuracion.php` | Acceso directo a ruta sin `../` | ✓ Completado |
| `servicios/imprimir_movimiento.php` | Event listener para print | ✓ Completado |
| `servicios/imprimir_orden_compra.php` | Event listener para print | ✓ Completado |
| `servicios/imprimir_devolucion.php` | Event listener para print | ✓ Completado |

---

**Fecha de actualización**: 18 de diciembre de 2025
**Versión del sistema**: 2.0.0
