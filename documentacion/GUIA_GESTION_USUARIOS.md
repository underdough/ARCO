# Guía de Gestión Avanzada de Usuarios

## Descripción General

El sistema de gestión de usuarios de ARCO permite al administrador tener control total sobre las cuentas de usuario del sistema, incluyendo la creación, edición, asignación de roles, cambio de estado y eliminación de cuentas.

## Características Principales

### 1. **Información del Usuario**

Cada usuario en el sistema contiene la siguiente información:

- **Número de Documento de Identidad**: Identificador único del usuario
- **Nombre y Apellido**: Información personal del usuario
- **Correo Electrónico**: Para comunicaciones y recuperación de contraseña
- **Teléfono**: Número de contacto (opcional)
- **Rol**: Define los permisos y accesos del usuario en el sistema
- **Cargo/Área**: Organización del inventario o departamento al que pertenece
- **Estado**: Activo, Inactivo o Suspendido

### 2. **Roles Disponibles**

El sistema cuenta con 5 roles predefinidos:

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Administrador** | Control total del sistema | Acceso completo a todas las funcionalidades |
| **Gerente** | Gestión de alto nivel | Acceso a reportes, configuración y supervisión |
| **Supervisor** | Supervisión de operaciones | Acceso a movimientos, productos y reportes |
| **Almacenista** | Gestión de inventario | Acceso a productos, categorías y movimientos |
| **Usuario** | Acceso básico | Consulta de información básica |

### 3. **Estados de Usuario**

- **ACTIVO**: Usuario puede acceder al sistema normalmente
- **INACTIVO**: Usuario no puede acceder al sistema (desactivado temporalmente)
- **SUSPENDIDO**: Usuario suspendido por razones administrativas

## Funcionalidades del Administrador

### 1. Crear Usuario

**Proceso:**

1. Hacer clic en el botón "Nuevo Usuario"
2. Completar el formulario con la información requerida:
   - Nombre y apellido
   - Número de documento (6-12 dígitos)
   - Correo electrónico válido
   - Teléfono (opcional)
   - Seleccionar rol apropiado
   - Definir cargo o área
   - Establecer contraseña (8-20 caracteres)
   - Confirmar contraseña
3. Hacer clic en "Crear Usuario"

**Validaciones:**
- El número de documento debe ser único
- El correo electrónico debe ser único
- Las contraseñas deben coincidir
- Todos los campos marcados con * son obligatorios

### 2. Editar Usuario

**Proceso:**

1. Localizar el usuario en la tabla
2. Hacer clic en el botón de editar (icono de lápiz)
3. Modificar la información necesaria
4. Hacer clic en "Guardar Cambios"

**Campos editables:**
- Nombre y apellido
- Número de documento
- Correo electrónico
- Teléfono
- Rol
- Cargo/Área
- Estado

**Nota:** No se puede editar la contraseña desde esta pantalla. El usuario debe usar la función de "Recuperar Contraseña".

### 3. Cambiar Estado de Usuario

**Proceso:**

1. Localizar el usuario en la tabla
2. Hacer clic en el botón de cambiar estado (icono de toggle)
3. Confirmar el cambio de estado

**Ciclo de estados:**
- ACTIVO → INACTIVO → SUSPENDIDO → ACTIVO

**Casos de uso:**
- **Desactivar (INACTIVO)**: Cuando un empleado ya no trabaja en la empresa
- **Suspender**: Cuando hay una investigación o situación temporal
- **Activar**: Restaurar acceso al usuario

### 4. Eliminar Usuario

**Proceso:**

1. Localizar el usuario en la tabla
2. Hacer clic en el botón de eliminar (icono de papelera)
3. Confirmar la eliminación

**⚠️ ADVERTENCIA:**
- La eliminación es permanente y no se puede deshacer
- Se recomienda desactivar usuarios en lugar de eliminarlos
- No se puede eliminar la propia cuenta de administrador

### 5. Búsqueda y Filtrado

#### Búsqueda por Texto

El campo de búsqueda permite encontrar usuarios por:
- Nombre
- Apellido
- Correo electrónico
- Número de documento

**Características:**
- Búsqueda en tiempo real (se actualiza mientras escribe)
- No distingue entre mayúsculas y minúsculas
- Busca coincidencias parciales

#### Filtros

**Filtro por Rol:**
- Todos los roles
- Administrador
- Gerente
- Supervisor
- Almacenista
- Usuario

**Filtro por Estado:**
- Todos los estados
- Activo
- Inactivo
- Suspendido

**Combinación de filtros:**
Los filtros se pueden combinar para búsquedas más específicas. Por ejemplo:
- Buscar "Juan" + Rol "Almacenista" + Estado "Activo"

### 6. Estadísticas

El panel de estadísticas muestra:
- **Total Usuarios**: Cantidad total de usuarios en el sistema
- **Activos**: Usuarios con estado ACTIVO
- **Inactivos**: Usuarios con estado INACTIVO o SUSPENDIDO

Las estadísticas se actualizan automáticamente al aplicar filtros.

## Auditoría y Seguridad

### Sistema de Auditoría

Todas las acciones realizadas sobre usuarios quedan registradas en el sistema:

- **Creación de usuario**: Quién creó el usuario y cuándo
- **Modificación**: Qué campos se modificaron, valores anteriores y nuevos
- **Cambio de estado**: Cambios entre ACTIVO, INACTIVO y SUSPENDIDO
- **Eliminación**: Registro de usuarios eliminados

### Información de Auditoría Incluye:

- Usuario que realizó la acción
- Fecha y hora de la acción
- Tipo de acción (crear, editar, eliminar, activar, desactivar, suspender)
- Campos modificados
- Valores anteriores y nuevos
- Dirección IP (cuando esté disponible)

## Mejores Prácticas

### 1. Creación de Usuarios

✅ **Hacer:**
- Asignar el rol más restrictivo necesario
- Usar correos electrónicos corporativos
- Definir claramente el cargo/área
- Establecer contraseñas seguras

❌ **Evitar:**
- Crear múltiples cuentas de administrador innecesariamente
- Usar correos personales
- Dejar campos opcionales vacíos sin razón

### 2. Gestión de Roles

✅ **Hacer:**
- Revisar periódicamente los roles asignados
- Actualizar roles cuando cambien las responsabilidades
- Documentar por qué se asigna cada rol

❌ **Evitar:**
- Dar permisos de administrador a todos
- Mantener roles desactualizados

### 3. Manejo de Estados

✅ **Hacer:**
- Desactivar usuarios que ya no trabajan en la empresa
- Suspender temporalmente en caso de investigaciones
- Documentar razones de suspensión

❌ **Evitar:**
- Eliminar usuarios sin antes desactivarlos
- Dejar usuarios inactivos como ACTIVO

### 4. Seguridad

✅ **Hacer:**
- Revisar regularmente la lista de usuarios activos
- Verificar que no haya cuentas duplicadas
- Mantener actualizada la información de contacto
- Revisar los registros de auditoría periódicamente

❌ **Evitar:**
- Compartir credenciales entre usuarios
- Mantener cuentas activas de personas que ya no trabajan
- Ignorar actividad sospechosa

## Solución de Problemas

### Problema: No puedo crear un usuario

**Posibles causas:**
- El número de documento ya existe
- El correo electrónico ya está registrado
- Las contraseñas no coinciden
- Campos obligatorios vacíos

**Solución:**
- Verificar que el documento y correo sean únicos
- Asegurarse de completar todos los campos requeridos
- Verificar que las contraseñas coincidan

### Problema: No puedo editar un usuario

**Posibles causas:**
- No tiene permisos de administrador
- El usuario no existe
- Conflicto con documento o correo de otro usuario

**Solución:**
- Verificar que tiene rol de administrador
- Recargar la página
- Verificar que documento y correo sean únicos

### Problema: No aparecen usuarios en la búsqueda

**Posibles causas:**
- Filtros muy restrictivos
- Error de conexión
- No hay usuarios que coincidan

**Solución:**
- Limpiar todos los filtros
- Verificar conexión a internet
- Recargar la página

## Acceso al Sistema

**URL de acceso:** `/vistas/gestion_usuarios.php`

**Requisitos:**
- Sesión iniciada
- Rol de Administrador

**Navegación:**
Desde el menú lateral: Usuarios → Gestión de Usuarios

## Soporte Técnico

Para problemas técnicos o preguntas sobre el sistema de gestión de usuarios, contactar al equipo de desarrollo o consultar la documentación técnica en `/documentacion/`.

---

**Última actualización:** Diciembre 2025  
**Versión del sistema:** 2.0
