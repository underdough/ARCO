# Sistema de Filtros en Gestión de Categorías

## Cambios Implementados

### 1. Panel de Filtros (Frontend)
Se agregó un panel de filtros en `vistas/categorias.php` con las siguientes opciones:

- **Estado**: Filtrar por categorías activas, inactivas o todas
- **Ordenar por**: 6 opciones de ordenamiento
  - Más recientes (ID descendente)
  - Más antiguos (ID ascendente)
  - Nombre (A-Z)
  - Nombre (Z-A)
  - Más productos
  - Menos productos

### 2. Funcionalidad JavaScript
Se implementaron las siguientes funciones:

- **Toggle del panel**: Mostrar/ocultar panel de filtros al hacer clic en el botón "Filtrar"
- **Aplicar filtros**: Recargar categorías con los filtros seleccionados
- **Limpiar filtros**: Resetear todos los filtros y recargar categorías
- **Búsqueda en tiempo real**: Filtrar por nombre o descripción mientras se escribe

### 3. Backend (servicios/listar_categorias.php)
Se actualizó el servicio para soportar los siguientes parámetros GET:

- `estado`: Filtrar por estado (0 = inactivas, 1 = activas, vacío = todas)
- `orden`: Ordenamiento (id_asc, id_desc, nombre_asc, nombre_desc, productos_asc, productos_desc)
- `busqueda`: Búsqueda por nombre o descripción de categoría

### 4. Características
- Los filtros se aplican en el servidor (no solo en el frontend)
- La paginación se mantiene funcional con los filtros aplicados
- El total de registros se actualiza según los filtros
- Notificaciones visuales al aplicar o limpiar filtros
- Animación suave al mostrar/ocultar el panel

## Uso

1. Hacer clic en el botón "Filtrar" para mostrar el panel de filtros
2. Seleccionar el estado y/o ordenamiento deseado
3. Hacer clic en "Aplicar" para aplicar los filtros
4. Hacer clic en "Limpiar" para resetear todos los filtros
5. Usar la barra de búsqueda para filtrar por nombre o descripción

## Archivos Modificados

- `vistas/categorias.php`: Agregado panel de filtros y funcionalidad JavaScript
- `servicios/listar_categorias.php`: Agregado soporte para parámetros de filtro y ordenamiento

## Fecha de Implementación
17 de diciembre de 2025
