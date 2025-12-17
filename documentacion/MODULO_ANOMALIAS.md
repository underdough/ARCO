# Módulo de Anomalías - Sistema ARCO

## Descripción
El módulo de Anomalías permite registrar, gestionar y dar seguimiento a incidencias, problemas y novedades del sistema de inventario ARCO.

## Archivos Creados

### 1. Interfaz de Usuario
- **`vistas/anomalias.php`** - Interfaz principal del módulo con formulario y listado de anomalías

### 2. Servicios Backend
- **`servicios/obtener_anomalias.php`** - Obtiene todas las anomalías con estadísticas
- **`servicios/guardar_anomalia.php`** - Crea y edita anomalías
- **`servicios/eliminar_anomalia.php`** - Elimina anomalías del sistema

### 3. Base de Datos
- **`base-datos/crear_tabla_anomalias.sql`** - Script completo con tablas, índices y vistas
- **`base-datos/anomalias_simple.sql`** - Script simplificado para creación rápida

### 4. Modificaciones
- **`vistas/dashboard.php`** - Agregado enlace al módulo de anomalías en el menú lateral

## Instalación

### Paso 1: Crear las Tablas
Ejecuta uno de estos scripts SQL en phpMyAdmin o tu cliente MySQL:

```sql
-- Opción 1: Script completo (recomendado)
-- Ejecutar: base-datos/crear_tabla_anomalias.sql

-- Opción 2: Script simplificado
-- Ejecutar: base-datos/anomalias_simple.sql
```

### Paso 2: Verificar Conexión
Asegúrate de que el archivo `servicios/conexion.php` funcione correctamente y que la extensión mysqli esté habilitada en PHP.

### Paso 3: Acceder al Módulo
1. Inicia sesión en el sistema ARCO
2. Ve al Dashboard
3. Haz clic en "Anomalías" en el menú lateral

## Funcionalidades

### Gestión de Anomalías
- ✅ **Crear anomalías** - Formulario completo con validaciones
- ✅ **Editar anomalías** - Modificar anomalías existentes
- ✅ **Eliminar anomalías** - Eliminar con confirmación
- ✅ **Listar anomalías** - Vista en tarjetas con filtros por prioridad
- ✅ **Historial de cambios** - Seguimiento de modificaciones

### Campos de Anomalía
- **Título** (obligatorio, máx. 100 caracteres)
- **Descripción** (obligatorio, texto largo)
- **Prioridad** (baja, media, urgente)
- **Categoría** (inventario, sistema, usuario, hardware, proceso, otro)
- **Ubicación** (módulo o lugar afectado)
- **Estado** (abierta, en_proceso, resuelta, cerrada)
- **Fechas** (creación, actualización, resolución)
- **Usuarios** (creador, asignado)

### Características de la Interfaz
- 🎨 **Diseño responsivo** - Funciona en desktop y móvil
- 🏷️ **Código de colores** - Prioridades visuales (rojo=urgente, amarillo=media, verde=baja)
- 📱 **Tarjetas interactivas** - Hover effects y animaciones
- 🔍 **Vista detallada** - Modal con información completa
- ⚡ **Carga dinámica** - AJAX para mejor experiencia

## Estructura de Base de Datos

### Tabla `anomalias`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- titulo (VARCHAR(100), NOT NULL)
- descripcion (TEXT, NOT NULL)
- prioridad (ENUM: 'baja', 'media', 'urgente')
- categoria (VARCHAR(50))
- ubicacion (VARCHAR(100))
- estado (ENUM: 'abierta', 'en_proceso', 'resuelta', 'cerrada')
- usuario_creador (INT, FK a usuarios.id)
- usuario_asignado (INT, FK a usuarios.id)
- fecha_creacion (TIMESTAMP)
- fecha_actualizacion (TIMESTAMP)
- fecha_resolucion (TIMESTAMP)
- notas_resolucion (TEXT)
```

### Tabla `anomalias_historial`
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- anomalia_id (INT, FK a anomalias.id)
- campo_modificado (VARCHAR(50))
- valor_anterior (TEXT)
- valor_nuevo (TEXT)
- usuario_modificador (INT, FK a usuarios.id)
- fecha_modificacion (TIMESTAMP)
- comentario (TEXT)
```

## API Endpoints

### GET `/servicios/obtener_anomalias.php`
Obtiene todas las anomalías con estadísticas.

**Respuesta:**
```json
{
  "success": true,
  "anomalias": [...],
  "estadisticas": {
    "total": 10,
    "urgentes": 2,
    "medias": 5,
    "bajas": 3,
    "abiertas": 7,
    "resueltas": 3
  }
}
```

### POST `/servicios/guardar_anomalia.php`
Crea o edita una anomalía.

**Parámetros:**
- `id` (opcional) - ID para editar
- `titulo` (requerido)
- `descripcion` (requerido)
- `prioridad` (requerido)
- `categoria` (opcional)
- `ubicacion` (opcional)

### POST `/servicios/eliminar_anomalia.php`
Elimina una anomalía.

**Parámetros JSON:**
```json
{
  "id": 123
}
```

## Seguridad

- ✅ **Autenticación** - Verificación de sesión en todos los endpoints
- ✅ **Validación** - Validación de datos en frontend y backend
- ✅ **SQL Injection** - Uso de prepared statements
- ✅ **XSS Protection** - Escape de datos con htmlspecialchars
- ✅ **CSRF** - Verificación de métodos HTTP
- ✅ **Logs** - Registro de acciones en historial_acciones

## Próximas Mejoras

### Funcionalidades Adicionales
- [ ] **Asignación de usuarios** - Asignar anomalías a usuarios específicos
- [ ] **Notificaciones** - Alertas por email cuando se crean anomalías urgentes
- [ ] **Comentarios** - Sistema de comentarios en anomalías
- [ ] **Archivos adjuntos** - Subir imágenes o documentos
- [ ] **Filtros avanzados** - Filtrar por fecha, usuario, estado, etc.
- [ ] **Dashboard de anomalías** - Estadísticas y gráficos
- [ ] **Exportar reportes** - PDF/Excel de anomalías
- [ ] **Estados personalizados** - Configurar estados según empresa

### Mejoras Técnicas
- [ ] **Paginación** - Para manejar muchas anomalías
- [ ] **Búsqueda** - Buscar por título o descripción
- [ ] **Cache** - Optimizar consultas frecuentes
- [ ] **API REST** - Endpoints más robustos
- [ ] **Websockets** - Actualizaciones en tiempo real

## Solución de Problemas

### Error: "Class mysqli not found"
1. Verificar que XAMPP esté ejecutándose
2. Habilitar extensión mysqli en php.ini
3. Reiniciar Apache

### Error: "Table 'anomalias' doesn't exist"
1. Ejecutar el script SQL en phpMyAdmin
2. Verificar que la base de datos 'arco_bdd' exista
3. Verificar permisos de usuario MySQL

### Error: "Usuario no autenticado"
1. Verificar que la sesión esté iniciada
2. Comprobar configuración de sesiones PHP
3. Verificar cookies del navegador

## Contacto y Soporte

Para reportar problemas o sugerir mejoras en el módulo de anomalías, documenta el issue con:
1. Descripción del problema
2. Pasos para reproducir
3. Mensajes de error (si los hay)
4. Navegador y versión
5. Configuración del servidor

---

**Fecha de creación:** 17 de diciembre de 2024  
**Versión:** 1.0  
**Autor:** Sistema ARCO - Módulo de Anomalías