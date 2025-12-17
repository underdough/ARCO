# Cómo Visualizar los Permisos del Sistema

## 🎯 Acceso Rápido

### Opción 1: Interfaz Visual (Recomendado)

1. **Iniciar sesión como Administrador**
   - URL: `login.html`
   - Usuario: Administrador
   - Contraseña: Tu contraseña

2. **Acceder a Gestión de Permisos**
   - URL directa: `vistas/gestion_permisos.php`
   - O desde el menú lateral: **Permisos**

3. **Seleccionar Rol**
   - Usar el selector desplegable
   - Opciones: Administrador, Gerente, Supervisor, Almacenista, Usuario
   - Los permisos se cargan automáticamente

### Opción 2: Base de Datos

Ejecutar consultas SQL directamente:

```sql
-- Ver todos los permisos de un rol
SELECT 
    m.nombre AS modulo,
    p.codigo AS permiso,
    p.nombre AS permiso_nombre,
    rp.activo
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador'
ORDER BY m.orden, p.nombre;

-- Resumen de permisos por rol
SELECT 
    rp.rol,
    COUNT(DISTINCT rp.id_modulo) AS modulos_acceso,
    COUNT(*) AS total_permisos
FROM rol_permisos rp
WHERE rp.activo = 1
GROUP BY rp.rol
ORDER BY total_permisos DESC;
```

### Opción 3: API JSON

Hacer peticiones HTTP a la API:

```javascript
// Obtener permisos de un rol
fetch('../servicios/obtener_permisos_rol.php?rol=administrador')
    .then(response => response.json())
    .then(data => {
        console.log('Permisos:', data);
    });
```

## 📊 Interfaz de Visualización

### Características

1. **Selector de Rol**
   - Dropdown para elegir el rol
   - Botón "Ver Permisos" para actualizar
   - Cambio automático al seleccionar

2. **Resumen de Permisos**
   - Módulos Accesibles
   - Permisos Totales
   - Permisos Activos

3. **Matriz de Permisos**
   - Tabla visual con todos los módulos
   - Columnas por tipo de permiso
   - Iconos ✓ (tiene permiso) y ✗ (no tiene permiso)

4. **Tabla Detallada**
   - Lista completa de módulos
   - Descripción de cada módulo
   - Badges con permisos específicos
   - Estado de activación

## 🔍 Qué Puedes Ver

### Por Rol

#### Administrador
- ✅ Acceso a 10 módulos
- ✅ ~80 permisos totales
- ✅ Todos los permisos activos

#### Gerente
- ✅ Acceso a 9 módulos
- ✅ ~60 permisos totales
- ⚠️ Sin gestión completa de usuarios

#### Supervisor
- ✅ Acceso a 7 módulos
- ✅ ~30 permisos totales
- ⚠️ Enfocado en supervisión y aprobación

#### Almacenista
- ✅ Acceso a 6 módulos
- ✅ ~25 permisos totales
- ⚠️ Gestión operativa de inventario

#### Usuario
- ✅ Acceso a 5 módulos
- ✅ ~10 permisos totales
- ⚠️ Solo consulta

### Por Módulo

La interfaz muestra para cada módulo:
- **Nombre**: Dashboard, Productos, Categorías, etc.
- **Descripción**: Breve descripción del módulo
- **Permisos**: Lista de permisos asignados
- **Estado**: Activo/Inactivo

### Por Permiso

Los tipos de permisos que puedes ver:
1. **Ver** - Consultar información
2. **Crear** - Agregar nuevos registros
3. **Editar** - Modificar registros
4. **Eliminar** - Eliminar registros
5. **Exportar** - Exportar datos
6. **Importar** - Importar datos
7. **Aprobar** - Aprobar operaciones
8. **Auditar** - Ver auditoría

## 📱 Interfaz Responsive

La interfaz se adapta a:
- 💻 Desktop (pantallas grandes)
- 📱 Tablet (pantallas medianas)
- 📱 Móvil (pantallas pequeñas)

## 🎨 Elementos Visuales

### Iconos
- ✅ Check verde: Tiene permiso
- ❌ X roja: No tiene permiso
- 🔒 Candado: Módulo protegido
- 👤 Usuario: Información del usuario

### Colores
- **Verde**: Permisos activos
- **Rojo**: Permisos inactivos
- **Azul**: Información general
- **Morado**: Elementos destacados

### Badges
- **Activo**: Verde con check
- **Inactivo**: Rojo con X
- **Permisos**: Azul con nombre

## 🚀 Funcionalidades

### Búsqueda y Filtrado
- Seleccionar rol específico
- Ver permisos en tiempo real
- Actualización automática

### Estadísticas
- Contador de módulos accesibles
- Contador de permisos totales
- Contador de permisos activos

### Navegación
- Menú lateral con todos los módulos
- Acceso rápido a otras secciones
- Breadcrumbs de navegación

## 📋 Archivos Creados

1. **vistas/gestion_permisos.php**
   - Interfaz principal de visualización
   - Solo accesible para administradores
   - Diseño responsive

2. **componentes/gestion_permisos.css**
   - Estilos de la interfaz
   - Animaciones y transiciones
   - Diseño moderno

3. **componentes/gestion_permisos.js**
   - Lógica de visualización
   - Carga dinámica de permisos
   - Interactividad

4. **servicios/obtener_permisos_rol.php**
   - API para obtener permisos
   - Retorna JSON con datos completos
   - Incluye estadísticas

## 🔐 Seguridad

- ✅ Solo administradores pueden acceder
- ✅ Verificación de sesión
- ✅ Validación de roles
- ✅ Protección contra acceso no autorizado

## 💡 Ejemplos de Uso

### Ver Permisos del Administrador
1. Acceder a `vistas/gestion_permisos.php`
2. Seleccionar "Administrador" en el dropdown
3. Ver matriz completa de permisos

### Comparar Roles
1. Seleccionar "Gerente"
2. Observar permisos
3. Cambiar a "Usuario"
4. Comparar diferencias

### Verificar Módulo Específico
1. Buscar el módulo en la tabla detallada
2. Ver permisos asignados
3. Verificar estado

## 📞 Soporte

Si tienes problemas para visualizar los permisos:

1. **Verificar sesión**: Asegúrate de estar logueado como administrador
2. **Verificar base de datos**: Ejecutar `base-datos/sistema_permisos_completo.sql`
3. **Verificar archivos**: Asegúrate de que todos los archivos existan
4. **Revisar consola**: Abrir consola del navegador (F12) para ver errores

## 🎯 Próximos Pasos

Después de visualizar los permisos, puedes:
1. Probar las vistas protegidas con diferentes roles
2. Crear nuevos usuarios con roles específicos
3. Verificar que los permisos funcionen correctamente
4. Personalizar permisos según necesidades

---

**Acceso Directo**: `vistas/gestion_permisos.php`  
**Requisito**: Sesión iniciada como Administrador  
**Versión**: 2.0  
**Estado**: ✅ Funcional
