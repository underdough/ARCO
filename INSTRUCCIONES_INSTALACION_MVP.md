# Instrucciones de Instalación MVP - Sistema ARCO

## Paso 1: Ejecutar SQL de Instalación

Abre phpMyAdmin y ejecuta el siguiente archivo SQL:

```
base-datos/instalar_mvp_completo.sql
```

Este script:
- Corrige la tabla `movimientos` (AUTO_INCREMENT, nuevas columnas)
- Crea las tablas `ordenes_compra`, `orden_detalles`, `devoluciones`
- Crea la tabla `historial_acciones` si no existe
- Registra los nuevos módulos en el sistema de permisos

## Paso 2: Verificar Instalación

Abre en tu navegador:
```
http://localhost/tu-proyecto/verificar_sistema.php
```

Este archivo verificará que todas las tablas y archivos estén correctos.

## Paso 3: Probar el Sistema

### Módulos Disponibles:

| Módulo | URL | Descripción |
|--------|-----|-------------|
| Movimientos | `vistas/movimientos.php` | Gestión de entradas/salidas de stock |
| Productos | `vistas/productos.php` | Gestión de productos con acciones rápidas |
| Órdenes de Compra | `vistas/ordenes_compra.php` | Crear y gestionar OC |
| Devoluciones | `vistas/devoluciones.php` | Solicitar y procesar devoluciones |
| Test Completo | `test_requerimientos.php` | Probar todas las funcionalidades |

### Funcionalidades Implementadas:

1. **Movimientos como motor de stock**
   - Entrada/Salida/Ajuste actualizan automáticamente `materiales.stock`
   - Validación de stock insuficiente en salidas
   - Registro en historial de acciones

2. **Órdenes de Compra**
   - Crear OC con múltiples items
   - Estados: pendiente, parcial, recibida, cancelada
   - Recepción genera movimientos tipo "recibido"

3. **Devoluciones**
   - Solicitar devolución con motivo
   - Aprobar/Rechazar devoluciones
   - Al aprobar, genera movimiento tipo "devolucion"

4. **Comprobantes Imprimibles**
   - `servicios/imprimir_movimiento.php?id=X`
   - `servicios/imprimir_orden_compra.php?id=X`
   - `servicios/imprimir_devolucion.php?id=X`

5. **Auditoría Global**
   - `servicios/auditoria.php?accion=listar`
   - `servicios/auditoria.php?accion=resumen`
   - `servicios/auditoria.php?accion=exportar` (CSV)

6. **Menú Dinámico por Permisos**
   - El menú lateral se genera según los permisos del rol
   - Botones de acción habilitados/deshabilitados según permisos

## Solución de Problemas

### Los movimientos no se muestran
1. Ejecuta `base-datos/instalar_mvp_completo.sql`
2. Verifica que la tabla `movimientos` tenga AUTO_INCREMENT en `id`

### No aparecen Órdenes de Compra o Devoluciones en el menú
1. Ejecuta el SQL de instalación
2. Verifica que los módulos estén registrados en la tabla `modulos`
3. Verifica que tu rol tenga permisos en `rol_permisos`

### Error de permisos
1. Asegúrate de estar logueado
2. Verifica que tu rol tenga los permisos necesarios
3. El rol "administrador" tiene acceso total

## Archivos Clave

```
servicios/
├── guardar_movimiento.php    # Motor de stock
├── filtrar_movimientos.php   # Listar movimientos
├── ordenes_compra.php        # API de OC
├── devoluciones.php          # API de devoluciones
├── auditoria.php             # API de auditoría
├── menu_dinamico.php         # Generador de menú
├── imprimir_*.php            # Comprobantes

vistas/
├── movimientos.php           # Vista de movimientos
├── productos.php             # Vista de productos
├── ordenes_compra.php        # Vista de OC
├── devoluciones.php          # Vista de devoluciones

base-datos/
├── instalar_mvp_completo.sql # Script de instalación
```
