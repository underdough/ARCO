# 📋 Información de Empresa en Comprobantes

**Fecha:** Diciembre 17, 2025  
**Versión:** 2.0.0  
**Estado:** ✅ Completado

## 📝 Descripción

Se ha implementado la visualización de la información de la empresa en todos los comprobantes de impresión del sistema (movimientos, órdenes de compra y devoluciones).

## 🎯 Objetivo

Proporcionar información completa de la empresa en los comprobantes para:
- Identificación clara de la empresa
- Información de contacto
- Profesionalismo en los documentos
- Trazabilidad de documentos

## 📊 Cambios Realizados

### 1. Función Helper en `servicios/conexion.php`

Se agregó la función `obtenerInfoEmpresa()` para centralizar la obtención de información de la empresa:

```php
function obtenerInfoEmpresa() {
    $conexion = ConectarDB();
    
    $empresa = [
        'nombre' => 'ARCO',
        'nif' => '',
        'direccion' => '',
        'ciudad' => '',
        'telefono' => '',
        'email' => ''
    ];
    
    $sql = "SELECT * FROM empresa WHERE id = 2";
    $result = $conexion->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $empresa = $result->fetch_assoc();
    }
    
    $conexion->close();
    return $empresa;
}
```

### 2. Servicios de Impresión Actualizados

#### `servicios/imprimir_movimiento.php`
- ✅ Obtiene información de la empresa
- ✅ Muestra nombre, dirección y teléfono en el encabezado
- ✅ Información visible en comprobantes de movimientos

#### `servicios/imprimir_orden_compra.php`
- ✅ Obtiene información de la empresa
- ✅ Muestra nombre, dirección y teléfono en el encabezado
- ✅ Información visible en comprobantes de órdenes de compra

#### `servicios/imprimir_devolucion.php`
- ✅ Obtiene información de la empresa
- ✅ Muestra nombre, dirección y teléfono en el encabezado
- ✅ Información visible en comprobantes de devoluciones

## 📍 Información Mostrada en Comprobantes

### Encabezado de Comprobante
```
┌─────────────────────────────────────┐
│         NOMBRE DE LA EMPRESA        │
│         Dirección de la empresa     │
│         Teléfono de contacto        │
└─────────────────────────────────────┘
```

### Datos Incluidos
- **Nombre de la Empresa**: Nombre registrado en configuración
- **Dirección**: Dirección completa de la empresa
- **Teléfono**: Número de contacto principal
- **NIF/CIF**: Identificación fiscal (si está configurado)
- **Email**: Correo de contacto (si está configurado)

## 🔧 Configuración

La información de la empresa se configura en:
- **Módulo**: Configuración del Sistema
- **Sección**: Información de la Empresa
- **Archivo**: `vistas/configuracion.php`

### Campos Configurables
- Nombre de la Empresa
- NIF/CIF
- Dirección
- Ciudad
- Teléfono
- Email

## 📄 Comprobantes Afectados

### 1. Movimientos de Inventario
- Entrada de Inventario
- Salida de Inventario
- Ajuste de Inventario
- Recepción de Material
- Devolución de Material

**Archivo**: `servicios/imprimir_movimiento.php`

### 2. Órdenes de Compra
- Comprobante de Orden de Compra
- Información del proveedor
- Detalles de materiales
- Total de la orden

**Archivo**: `servicios/imprimir_orden_compra.php`

### 3. Devoluciones
- Comprobante de Devolución
- Motivo de la devolución
- Información del material
- Estado de la devolución

**Archivo**: `servicios/imprimir_devolucion.php`

## 🎨 Formato de Comprobantes

### Estructura General
```
┌─────────────────────────────────────┐
│    INFORMACIÓN DE LA EMPRESA        │
├─────────────────────────────────────┤
│    TIPO DE COMPROBANTE              │
├─────────────────────────────────────┤
│    INFORMACIÓN DEL DOCUMENTO        │
├─────────────────────────────────────┤
│    DETALLES Y DATOS                 │
├─────────────────────────────────────┤
│    FIRMAS Y AUTORIZACIONES          │
├─────────────────────────────────────┤
│    FECHA Y HORA DE IMPRESIÓN        │
└─────────────────────────────────────┘
```

## 🔒 Seguridad

- ✅ Datos escapados con `htmlspecialchars()`
- ✅ Validación de IDs
- ✅ Conexión segura a base de datos
- ✅ Sin exposición de datos sensibles

## 📊 Beneficios

1. **Profesionalismo**: Comprobantes con información completa de la empresa
2. **Trazabilidad**: Identificación clara de la empresa emisora
3. **Contacto**: Información de contacto disponible en documentos
4. **Consistencia**: Información centralizada y actualizable
5. **Flexibilidad**: Fácil actualización desde configuración

## 🚀 Próximos Pasos

1. Configurar información de la empresa en el módulo de Configuración
2. Probar impresión de comprobantes
3. Verificar que la información se muestre correctamente
4. Ajustar estilos si es necesario

## 📝 Notas

- La información se obtiene de la tabla `empresa` con `id = 2`
- Si no hay información configurada, se muestra "ARCO" como nombre por defecto
- Los comprobantes se pueden imprimir directamente desde el navegador
- La información se actualiza automáticamente al cambiar la configuración

---

**Implementación completada exitosamente** ✅
