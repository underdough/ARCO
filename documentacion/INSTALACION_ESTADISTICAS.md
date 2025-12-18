# Instalación del Módulo de Estadísticas

## Descripción
El módulo de estadísticas proporciona visualizaciones interactivas y métricas clave del sistema ARCO, incluyendo:
- Estadísticas principales (productos, movimientos, stock, alertas)
- Gráficos de movimientos por mes
- Distribución de productos por categoría
- Stock por categoría
- Tipos de movimientos

## Acceso Restringido
Solo pueden acceder: **Administrador**, **Gerente** y **Supervisor**

## Archivos Creados
1. `vistas/estadisticas.php` - Interfaz de usuario con gráficos interactivos
2. `servicios/estadisticas_data.php` - API backend para proveer datos
3. `servicios/menu_dinamico.php` - Actualizado para incluir enlace a estadísticas

## Instalación en Base de Datos

### Paso 1: Agregar el módulo de estadísticas
Ejecuta el siguiente SQL en tu base de datos `arco_bdd`:

```sql
-- Insertar módulo de estadísticas
INSERT INTO modulos (nombre, descripcion, icono, ruta, orden, activo) 
VALUES ('estadisticas', 'Estadísticas', 'fa-chart-line', 'estadisticas.php', 7, 1);

-- Obtener el ID del módulo recién creado
SET @modulo_id = LAST_INSERT_ID();

-- Obtener IDs de permisos
SET @permiso_ver = (SELECT id_permiso FROM permisos WHERE codigo = 'ver' LIMIT 1);
SET @permiso_exportar = (SELECT id_permiso FROM permisos WHERE codigo = 'exportar' LIMIT 1);

-- Asignar permisos a Administrador
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
VALUES 
('administrador', @modulo_id, @permiso_ver, 1),
('administrador', @modulo_id, @permiso_exportar, 1);

-- Asignar permisos a Gerente
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
VALUES 
('gerente', @modulo_id, @permiso_ver, 1),
('gerente', @modulo_id, @permiso_exportar, 1);

-- Asignar permisos a Supervisor
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo) 
VALUES 
('supervisor', @modulo_id, @permiso_ver, 1),
('supervisor', @modulo_id, @permiso_exportar, 1);
```

### Paso 2: Verificar la instalación
```sql
-- Verificar que el módulo fue creado
SELECT * FROM modulos WHERE nombre = 'estadisticas';

-- Verificar permisos asignados
SELECT rp.*, m.nombre as modulo, p.nombre as permiso
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE m.nombre = 'estadisticas';
```

## Uso del Módulo

### Acceso
1. Inicia sesión con un usuario que tenga rol de Administrador, Gerente o Supervisor
2. En el menú lateral, busca "Estadísticas" en la sección de Reportes
3. Haz clic para acceder al módulo

### Funcionalidades

#### Tarjetas de Estadísticas Principales
- **Total Productos**: Cantidad total de productos con cambio porcentual vs mes anterior
- **Movimientos del Mes**: Total de movimientos del mes actual con comparación
- **Stock Total**: Suma total del stock con tendencia
- **Alertas de Stock**: Productos con stock bajo (≤10 unidades)

#### Gráficos Interactivos
1. **Movimientos por Mes**: Gráfico de líneas mostrando entradas y salidas mensuales
   - Filtro por año (2023, 2024, 2025)
   
2. **Productos por Categoría**: Gráfico de dona mostrando distribución de productos
   - Top 10 categorías
   
3. **Stock por Categoría**: Gráfico de barras con stock total por categoría
   - Top 10 categorías
   
4. **Movimientos por Tipo**: Gráfico de pastel con tipos de movimientos
   - Filtros: Últimos 7, 30 o 90 días

### Actualización de Datos
Los datos se cargan automáticamente al entrar al módulo. Los gráficos se actualizan dinámicamente al cambiar los filtros.

## Endpoints de la API

### `estadisticas_data.php?tipo=resumen`
Retorna estadísticas principales:
```json
{
  "success": true,
  "data": {
    "total_productos": 150,
    "cambio_productos": 5.2,
    "movimientos_mes": 45,
    "cambio_movimientos": -3.1,
    "stock_total": 2500,
    "cambio_stock": 8.5,
    "alertas_stock": 12
  }
}
```

### `estadisticas_data.php?tipo=movimientos_mes&year=2025`
Retorna movimientos mensuales por tipo:
```json
{
  "success": true,
  "data": {
    "entradas": [10, 15, 20, ...],
    "salidas": [8, 12, 18, ...]
  }
}
```

### `estadisticas_data.php?tipo=categorias`
Retorna productos por categoría:
```json
{
  "success": true,
  "data": {
    "labels": ["Electrónica", "Oficina", ...],
    "values": [45, 32, ...]
  }
}
```

### `estadisticas_data.php?tipo=stock_categorias`
Retorna stock por categoría:
```json
{
  "success": true,
  "data": {
    "labels": ["Electrónica", "Oficina", ...],
    "values": [850, 620, ...]
  }
}
```

### `estadisticas_data.php?tipo=tipos_movimiento&dias=30`
Retorna movimientos por tipo en período:
```json
{
  "success": true,
  "data": {
    "labels": ["Entrada", "Salida", ...],
    "values": [120, 95, ...]
  }
}
```

## Tecnologías Utilizadas
- **Chart.js**: Librería para gráficos interactivos
- **PHP**: Backend y API
- **MySQL**: Base de datos
- **CSS3**: Estilos responsive
- **JavaScript**: Interactividad y llamadas AJAX

## Responsive Design
El módulo está completamente optimizado para:
- Escritorio (≥1200px)
- Tablets (768px - 1199px)
- Móviles (<768px)

## Seguridad
- Verificación de sesión activa
- Control de acceso por rol
- Validación de permisos en backend
- Protección contra SQL injection con prepared statements
- Headers de seguridad JSON

## Solución de Problemas

### El módulo no aparece en el menú
1. Verifica que ejecutaste el SQL de instalación
2. Confirma que tu usuario tiene rol autorizado (administrador, gerente o supervisor)
3. Cierra sesión y vuelve a iniciar

### Los gráficos no cargan
1. Verifica que `servicios/estadisticas_data.php` existe
2. Revisa la consola del navegador (F12) para errores
3. Confirma que la conexión a la base de datos funciona

### Error 403 al acceder
Tu usuario no tiene permisos. Solo administradores, gerentes y supervisores pueden acceder.

### Los datos no se actualizan
1. Verifica que hay datos en las tablas `materiales` y `movimientos`
2. Revisa los filtros de fecha en los gráficos
3. Actualiza la página (F5)

## Mantenimiento

### Agregar nuevos gráficos
1. Agrega un nuevo `case` en `estadisticas_data.php`
2. Crea la función de consulta correspondiente
3. Agrega el contenedor HTML en `estadisticas.php`
4. Implementa la función de renderizado en JavaScript

### Modificar permisos
```sql
-- Agregar acceso a otro rol
INSERT INTO rol_permisos (rol, id_modulo, id_permiso, activo)
SELECT 'nuevo_rol', id_modulo, id_permiso, 1
FROM modulos m, permisos p
WHERE m.nombre = 'estadisticas' AND p.codigo = 'ver';
```

## Próximas Mejoras
- [ ] Exportación de gráficos a PDF
- [ ] Comparación entre períodos
- [ ] Filtros avanzados por categoría
- [ ] Predicciones de stock
- [ ] Alertas automáticas
- [ ] Dashboard personalizable

---

**Fecha de creación**: Diciembre 2025  
**Versión**: 1.0  
**Sistema**: ARCO - Gestión de Inventario
