# 📚 Guía Completa de Módulos - Sistema ARCO

Documentación detallada de cómo funciona cada módulo del sistema de gestión de inventarios.

## 📑 Índice de Módulos

1. [Autenticación y Login](#autenticación-y-login)
2. [Dashboard](#dashboard)
3. [Gestión de Categorías](#gestión-de-categorías)
4. [Gestión de Productos](#gestión-de-productos)
5. [Movimientos de Inventario](#movimientos-de-inventario)
6. [Órdenes de Compra](#órdenes-de-compra)
7. [Devoluciones](#devoluciones)
8. [Gestión de Anomalías](#gestión-de-anomalías)
9. [Estadísticas](#estadísticas)
10. [Reportes](#reportes)
11. [Gestión de Usuarios](#gestión-de-usuarios)
12. [Gestión de Permisos](#gestión-de-permisos)
13. [Configuración del Sistema](#configuración-del-sistema)

---

## Autenticación y Login

### Descripción General
El módulo de autenticación es la puerta de entrada al sistema. Implementa seguridad de dos factores (2FA) y gestión de sesiones seguras(solo si lo activa desde la configuración).

### Archivos Principales
- `login.html` - Interfaz de login
- `servicios/autenticador.php` - Lógica de autenticación
- `servicios/two_factor_auth.php` - Sistema 2FA
- `servicios/recuperar_contrasena.php` - Recuperación de contraseña

### Flujo de Funcionamiento

**1. Inicio de Sesión**
- Usuario ingresa número de documento y contraseña en `login.html`
- Credenciales se envían a `autenticador.php`
Solo si activa el 2FA en configuraciones del sistema:
- Sistema verifica usuario en base de datos
- Contraseña se valida con hash Argon2ID
- Si es correcto, se genera código 2FA
- Si todo el proceso se hizo correctamente, entra al aplicativo

**2. Autenticación de Dos Factores**
- Sistema envía código de 6 dígitos al email del usuario
- Usuario ingresa código en pantalla de verificación
- Código se valida en `two_factor_auth.php`
- Si es correcto, se crea sesión segura

**3. Recuperación de Contraseña**
- Usuario solicita recuperación en login
- Sistema genera token único y lo envía por email
- Usuario accede a `vistas/restablecer-contra.php` con token
- Ingresa nueva contraseña
- Sistema actualiza contraseña con hash Argon2ID

### Características de Seguridad
- Contraseñas hasheadas con Argon2ID
- Códigos 2FA con expiración de 10 minutos
- Tokens de recuperación únicos y con expiración
- Sesiones con timeout automático
- Protección CSRF en formularios
- Validación de entrada en servidor

---

## Dashboard

### Descripción General
Panel principal del sistema que muestra estadísticas en tiempo real, resumen de inventario y accesos rápidos a módulos principales.

### Archivo Principal
- `vistas/dashboard.php` - Interfaz del dashboard
- `servicios/obtener_dashboard.php` - API de datos

### Funcionalidades

**1. Información del Usuario**
- Muestra nombre completo del usuario
- Muestra rol del usuario (Administrador, Gerente, etc.)
- Información visible en esquina superior derecha

**2. Estadísticas Principales**
- Total de productos en inventario
- Total de categorías
- Movimientos del día
- Stock bajo (productos con cantidad mínima)

**3. Gráficos Interactivos**
- Movimientos últimos 7 días (gráfico de líneas)
- Distribución por categoría (gráfico de dona)
- Productos por estado (gráfico de barras)

**4. Accesos Rápidos**
- Botones para acceder a módulos principales
- Disponibilidad según permisos del usuario
- Iconos intuitivos y colores del sistema

**5. Alertas**
- Productos con stock bajo
- Órdenes pendientes
- Anomalías sin resolver

### Flujo de Datos
```
Usuario accede → Dashboard carga → obtener_dashboard.php
→ Consulta BD → Retorna JSON → Dashboard renderiza datos
```

---

## Gestión de Categorías

### Descripción General
Módulo para crear, editar, eliminar y organizar categorías de productos. Incluye filtros avanzados y paginación.

### Archivos Principales
- `vistas/categorias.php` - Interfaz de categorías
- `servicios/listar_categorias.php` - API con paginación y filtros
- `servicios/agregar_categoria.php` - Crear categoría
- `servicios/editar_categoria.php` - Editar categoría
- `servicios/eliminar_categoria.php` - Eliminar categoría

### Funcionalidades

**1. Listar Categorías**
- Tabla con todas las categorías
- Paginación: 10 registros por página
- Máximo 5 botones de página visibles
- Información mostrada: ID, Nombre, Descripción, Cantidad de productos, Estado

**2. Filtros Avanzados**
- **Por Estado**: Todas, Activas, Inactivas
- **Ordenamiento**:
  - Más recientes (ID descendente)
  - Más antiguos (ID ascendente)
  - Nombre A-Z
  - Nombre Z-A
  - Más productos
  - Menos productos

**3. Búsqueda en Tiempo Real**
- Busca por nombre de categoría
- Busca por descripción
- Filtra mientras se escribe

**4. Crear Categoría**
- Modal con formulario
- Campos: Nombre, Descripción, Cantidad de productos, Estado
- Validación en cliente y servidor
- Mensaje de confirmación

**5. Editar Categoría**
- Clic en icono de editar
- Modal se llena con datos actuales
- Actualiza en base de datos
- Recarga tabla automáticamente

**6. Eliminar Categoría**
- Confirmación antes de eliminar
- Elimina de base de datos
- Actualiza tabla automáticamente

### Flujo de Datos
```
Usuario → Selecciona filtros → cargarCategorias()
→ listar_categorias.php (con parámetros)
→ Consulta BD con WHERE y ORDER BY
→ Retorna JSON con datos y total
→ Renderiza tabla con paginación
```

### Permisos Requeridos
- Ver: Todos los roles
- Crear: Administrador, Gerente
- Editar: Administrador, Gerente
- Eliminar: Administrador

---

## Gestión de Productos

### Descripción General
Módulo para gestionar el catálogo de productos/materiales. Incluye control de stock, ubicación y trazabilidad.

### Archivos Principales
- `vistas/productos.php` - Interfaz de productos
- `servicios/listar_productos.php` - API con paginación
- `servicios/agregar_producto.php` - Crear producto
- `servicios/editar_producto.php` - Editar producto
- `servicios/eliminar_producto.php` - Eliminar producto

### Funcionalidades

**1. Listar Productos**
- Tabla con todos los productos
- Paginación: 10 registros por página
- Información: ID, Nombre, Categoría, Stock, Ubicación, Estado

**2. Búsqueda y Filtros**
- Búsqueda por nombre de producto
- Filtro por categoría
- Filtro por estado (activo/inactivo)
- Ordenamiento por nombre, stock, fecha

**3. Crear Producto**
- Modal con formulario
- Campos: Nombre, Categoría, Stock inicial, Ubicación, Descripción, Estado
- Validación de datos
- Asignación automática de ID

**4. Editar Producto**
- Actualizar información del producto
- Cambiar categoría
- Ajustar stock
- Cambiar ubicación

**5. Eliminar Producto**
- Confirmación antes de eliminar
- Opción de desactivar en lugar de eliminar
- Mantiene historial de movimientos

### Permisos Requeridos
- Ver: Todos los roles
- Crear: Administrador, Gerente, Supervisor
- Editar: Administrador, Gerente, Supervisor
- Eliminar: Administrador

---

## Movimientos de Inventario

### Descripción General
Módulo central para registrar todas las operaciones de entrada y salida de productos. Genera comprobantes y mantiene trazabilidad completa.

### Archivos Principales
- `vistas/movimientos.php` - Interfaz de movimientos
- `servicios/guardar_movimiento.php` - Registrar movimiento
- `servicios/obtener_movimientos.php` - Listar movimientos
- `servicios/imprimir_movimiento.php` - Generar comprobante
- `servicios/filtrar_movimientos.php` - Filtros avanzados

### Funcionalidades

**1. Registrar Movimiento**
- Modal para nuevo movimiento
- Campos:
  - Tipo: Entrada, Salida, Ajuste
  - Producto: Seleccionar de lista
  - Cantidad: Número de unidades
  - Motivo: Compra, Venta, Devolución, Ajuste, etc.
  - Observaciones: Notas adicionales
  - Fecha: Automática o manual

**2. Validaciones**
- Cantidad debe ser positiva
- Producto debe existir
- Stock suficiente para salidas
- Campos obligatorios completos

**3. Listar Movimientos**
- Tabla con historial de movimientos
- Información: ID, Fecha, Producto, Tipo, Cantidad, Motivo, Usuario
- Paginación: 10 registros por página
- Ordenamiento por fecha (más recientes primero)

**4. Filtros Avanzados**
- Por rango de fechas
- Por tipo de movimiento
- Por producto
- Por categoría
- Por usuario que realizó el movimiento

**5. Impresión de Comprobante**
- Genera PDF con detalles del movimiento
- Incluye información de empresa
- Muestra: Fecha, Producto, Cantidad, Motivo, Usuario
- Código QR con ID del movimiento (opcional)

**6. Actualización de Stock**
- Entrada: Suma cantidad al stock
- Salida: Resta cantidad del stock
- Ajuste: Modifica stock según valor ingresado
- Alertas si stock queda bajo mínimo

### Flujo de Datos
```
Usuario ingresa datos → Validación cliente
→ guardar_movimiento.php → Validación servidor
→ Actualiza tabla movimientos → Actualiza stock en productos
→ Registra en auditoría → Retorna confirmación
```

### Permisos Requeridos
- Ver: Todos los roles
- Crear: Administrador, Gerente, Supervisor, Almacenista
- Editar: Administrador, Gerente
- Eliminar: Administrador
- Imprimir: Todos los roles

---

## Órdenes de Compra

### Descripción General
Módulo para gestionar órdenes de compra a proveedores. Incluye seguimiento de estado y generación de documentos.

### Archivos Principales
- `vistas/ordenes_compra.php` - Interfaz de órdenes
- `servicios/ordenes_compra.php` - API de órdenes
- `servicios/imprimir_orden_compra.php` - Generar comprobante

### Funcionalidades

**1. Crear Orden de Compra**
- Modal con formulario
- Campos:
  - Proveedor: Nombre o seleccionar de lista
  - Productos: Agregar múltiples productos
  - Cantidad por producto
  - Precio unitario
  - Fecha de entrega esperada
  - Observaciones

**2. Listar Órdenes**
- Tabla con todas las órdenes
- Estados: Pendiente, Recibida, Cancelada
- Información: ID, Fecha, Proveedor, Total, Estado

**3. Cambiar Estado**
- Pendiente → Recibida (cuando llega la orden)
- Pendiente → Cancelada (si se cancela)
- Recibida → Actualiza stock automáticamente

**4. Impresión de Orden**
- Genera PDF con detalles
- Incluye información de empresa
- Lista de productos con cantidades y precios
- Total de la orden

### Permisos Requeridos
- Ver: Administrador, Gerente, Supervisor
- Crear: Administrador, Gerente
- Editar: Administrador, Gerente
- Eliminar: Administrador

---

## Devoluciones

### Descripción General
Módulo para registrar devoluciones de productos. Puede ser por defecto, cambio o exceso de compra.

### Archivos Principales
- `vistas/devoluciones.php` - Interfaz de devoluciones
- `servicios/devoluciones.php` - API de devoluciones
- `servicios/imprimir_devolucion.php` - Generar comprobante

### Funcionalidades

**1. Registrar Devolución**
- Modal con formulario
- Campos:
  - Producto: Seleccionar de lista
  - Cantidad: Unidades a devolver
  - Motivo: Defecto, Cambio, Exceso, Otro
  - Descripción del problema
  - Observaciones

**2. Listar Devoluciones**
- Tabla con historial de devoluciones
- Información: ID, Fecha, Producto, Cantidad, Motivo, Estado

**3. Estados de Devolución**
- Registrada: Inicial
- Procesada: Revisada y aceptada
- Rechazada: No cumple criterios
- Reembolsada: Dinero devuelto

**4. Impresión de Comprobante**
- Genera PDF con detalles
- Incluye información de empresa
- Muestra motivo y descripción
- Firma de autorización

### Permisos Requeridos
- Ver: Todos los roles
- Crear: Administrador, Gerente, Supervisor, Almacenista
- Procesar: Administrador, Gerente
- Eliminar: Administrador

---

## Gestión de Anomalías

### Descripción General
Módulo para registrar y dar seguimiento a problemas o inconsistencias en el inventario.

### Archivos Principales
- `vistas/anomalias.php` - Interfaz de anomalías
- `servicios/guardar_anomalia.php` - Registrar anomalía
- `servicios/obtener_anomalias.php` - Listar anomalías
- `servicios/cambiar_estado_anomalia.php` - Cambiar estado

### Funcionalidades

**1. Registrar Anomalía**
- Modal con formulario
- Campos:
  - Tipo: Faltante, Sobrante, Dañado, Vencido, Otro
  - Producto: Seleccionar de lista
  - Cantidad: Unidades afectadas
  - Descripción: Detalles del problema
  - Ubicación: Dónde se encontró
  - Responsable: Usuario asignado

**2. Listar Anomalías**
- Tabla con todas las anomalías
- Información: ID, Fecha, Tipo, Producto, Cantidad, Estado, Responsable

**3. Estados de Anomalía**
- Registrada: Inicial
- En Investigación: Asignada a responsable
- Resuelta: Problema solucionado
- Cerrada: Documentada y archivada

**4. Seguimiento**
- Historial de cambios de estado
- Comentarios y notas
- Asignación de responsables
- Fechas de resolución

**5. Reportes de Anomalías**
- Análisis de anomalías por tipo
- Estadísticas de resolución
- Productos con más anomalías
- Tendencias en el tiempo

### Permisos Requeridos
- Ver: Todos los roles
- Crear: Todos los roles
- Asignar: Administrador, Gerente, Supervisor
- Resolver: Administrador, Gerente, Supervisor
- Eliminar: Administrador

---

## Estadísticas

### Descripción General
Módulo de análisis con gráficos interactivos que muestran tendencias y métricas del inventario.

### Archivos Principales
- `vistas/estadisticas.php` - Interfaz de estadísticas
- `servicios/estadisticas_data.php` - API de datos
- `componentes/estadisticas.css` - Estilos

### Gráficos Disponibles

**1. Resumen General**
- Total de productos
- Total de categorías
- Movimientos del mes
- Stock bajo

**2. Movimientos por Mes**
- Gráfico de líneas
- Últimos 12 meses
- Muestra tendencia de movimientos
- Entradas vs Salidas

**3. Distribución por Categorías**
- Gráfico de barras
- Cantidad de productos por categoría
- Identifica categorías principales

**4. Stock por Categorías**
- Gráfico de dona
- Valor total de stock por categoría
- Proporciones visuales

**5. Tipos de Movimiento**
- Gráfico de barras horizontales
- Cantidad de movimientos por tipo
- Entrada, Salida, Ajuste, etc.

### Características
- Gráficos interactivos con Chart.js
- Leyendas y etiquetas claras
- Colores del sistema ARCO
- Responsive en dispositivos móviles
- Acceso restringido a Administrador, Gerente y Supervisor

### Flujo de Datos
```
Usuario accede → estadisticas.php carga
→ estadisticas_data.php → Consulta BD
→ Retorna datos agregados → Chart.js renderiza gráficos
```

---

## Reportes

### Descripción General
Módulo para generar reportes personalizados con filtros avanzados y exportación en formato PDF para impresión.

### Archivos Principales
- `vistas/reportes.php` - Interfaz de reportes
- `servicios/generar_reporte.php` - Generar reporte
- `servicios/descargar_reporte.php` - Descargar archivo

### Tipos de Reportes

**1. Reporte de Movimientos**
- Filtros: Fecha inicio/fin, Tipo, Producto, Categoría
- Información: ID, Fecha, Producto, Tipo, Cantidad, Motivo, Usuario
- Totales: Cantidad total, Valor total

**2. Reporte de Inventario**
- Estado actual del stock
- Productos por categoría
- Ubicación de productos
- Valor total del inventario

**3. Reporte de Usuarios**
- Actividad de usuarios
- Movimientos realizados
- Accesos al sistema
- Cambios realizados

**4. Reporte de Anomalías**
- Anomalías por período
- Tipos de anomalías
- Resolución de anomalías
- Responsables

### Formatos de Exportación
- **PDF**: Documento formateado con logo y datos de empresa

### Características
- Filtros avanzados por fecha
- Selección de columnas a mostrar
- Ordenamiento personalizado
- Vista previa antes de descargar
- Descarga automática del archivo

### Permisos Requeridos
- Ver: Todos los roles
- Generar: Administrador, Gerente, Supervisor
- Descargar: Todos los roles

---

## Gestión de Usuarios

### Descripción General
Módulo para administrar cuentas de usuario, asignación de roles y control de acceso.

### Archivos Principales
- `vistas/gestion_usuarios.php` - Interfaz de usuarios
- `servicios/listar_usuarios_mejorado.php` - Listar usuarios
- `servicios/agregar_usuario.php` - Crear usuario
- `servicios/editar_usuario.php` - Editar usuario
- `servicios/eliminar_usuario.php` - Eliminar usuario

### Funcionalidades

**1. Listar Usuarios**
- Tabla con todos los usuarios
- Información: ID, Nombre, Email, Rol, Estado, Fecha de creación
- Búsqueda por nombre o email
- Paginación: 10 registros por página

**2. Crear Usuario**
- Modal con formulario
- Campos:
  - Nombre completo
  - Email (único)
  - Rol: Administrador, Gerente, Supervisor, Almacenista, Funcionario
  - Contraseña (generada automáticamente)
  - Estado: Activo/Inactivo
- Envía email con credenciales temporales

**3. Editar Usuario**
- Actualizar información personal
- Cambiar rol
- Cambiar estado (activo/inactivo)
- Resetear contraseña

**4. Eliminar Usuario**
- Confirmación antes de eliminar
- Opción de desactivar en lugar de eliminar
- Mantiene historial de actividades

**5. Roles de Usuario**
- **Administrador**: Acceso completo a todos los módulos
- **Gerente**: Gestión completa de inventario y usuarios
- **Supervisor**: Supervisión y reportes
- **Almacenista**: Operaciones de inventario
- **Usuario**: Consultas y reportes básicos

### Permisos Requeridos
- Ver: Administrador, Gerente
- Crear: Administrador
- Editar: Administrador, Gerente (solo usuarios de menor rango)
- Eliminar: Administrador
- Cambiar rol: Administrador

---

## Gestión de Permisos

### Descripción General
Sistema granular de control de acceso que define qué puede hacer cada usuario en cada módulo.

### Archivos Principales
- `vistas/gestion_permisos.php` - Interfaz de permisos
- `servicios/middleware_permisos.php` - Validación de permisos
- `servicios/gestionar_permisos.php` - API de permisos
- `servicios/obtener_permisos_usuario.php` - Obtener permisos

### Funcionalidades

**1. Permisos por Módulo**
- Ver: Acceso a lectura
- Crear: Crear nuevos registros
- Editar: Modificar registros existentes
- Eliminar: Borrar registros

**2. Asignación de Permisos**
- Por rol: Todos los usuarios con ese rol heredan permisos
- Por usuario: Permisos específicos para usuario individual
- Combinación: Permisos de rol + permisos individuales

**3. Módulos Controlados**
- Dashboard
- Categorías
- Productos
- Movimientos
- Órdenes de Compra
- Devoluciones
- Anomalías
- Estadísticas
- Reportes
- Usuarios
- Permisos
- Configuración

**4. Middleware de Protección**
- Valida permisos en cada acceso
- Redirige si no tiene permiso
- Registra intentos no autorizados
- Muestra mensaje de acceso denegado

**5. Interfaz de Gestión**
- Tabla con módulos y permisos
- Checkboxes para cada permiso
- Guardar cambios automáticamente
- Vista por rol o por usuario

### Flujo de Validación
```
Usuario accede a módulo → middleware_permisos.php
→ Obtiene permisos del usuario → Valida permiso requerido
→ Si tiene permiso: Carga módulo
→ Si no tiene: Redirige a dashboard con mensaje
```

### Permisos Requeridos
- Ver: Administrador, Gerente
- Editar: Administrador
- Aplicar: Administrador

---

## Configuración del Sistema

### Descripción General
Módulo para configurar parámetros generales del sistema, información de empresa y opciones de seguridad.

### Archivos Principales
- `vistas/configuracion.php` - Interfaz de configuración
- `servicios/guardar_empresa.php` - Guardar datos de empresa
- `servicios/crear_copia_ahora.php` - Backup manual

### Configuraciones Disponibles

**1. Información de Empresa**
- Nombre de empresa
- Dirección
- Teléfono
- Email
- NIT
- Logo (opcional)
- Datos mostrados en comprobantes

**2. Configuración de Email**
- Servidor SMTP
- Puerto
- Usuario
- Contraseña de aplicación
- Remitente
- Prueba de conexión

**3. Configuración de Seguridad**
- Expiración de sesión (minutos)
- Intentos de login fallidos permitidos
- Bloqueo temporal después de intentos fallidos
- Requerir 2FA para todos los usuarios

**4. Configuración de Inventario**
- Stock mínimo por defecto
- Unidad de medida por defecto
- Alertas de stock bajo
- Notificaciones automáticas

**5. Copias de Seguridad**
- Crear backup manual
- Programar backups automáticos
- Descargar backup
- Restaurar desde backup

**6. Auditoría**
- Registrar todas las acciones
- Retención de logs (días)
- Exportar logs
- Limpiar logs antiguos

### Características
- Validación de datos
- Confirmación antes de guardar
- Mensajes de éxito/error
- Prueba de conexión para email
- Descarga de backups

### Permisos Requeridos
- Ver: Administrador
- Editar: Administrador
- Crear backup: Administrador
- Restaurar: Administrador

---

## Flujo General del Sistema

### Acceso a Módulos
```
1. Usuario inicia sesión (login.html)
2. Autenticación 2FA (two_factor_auth.php)
3. Sesión creada
4. Accede a Dashboard (dashboard.php)
5. Selecciona módulo del menú
6. middleware_permisos.php valida acceso
7. Si tiene permiso: Carga módulo
8. Si no: Redirige a dashboard
```

### Flujo de Datos
```
Frontend (HTML/JS) → API (servicios/*.php)
→ Validación → Base de Datos
→ Retorna JSON → Frontend renderiza
```

### Seguridad en Capas
```
1. Autenticación: Login + 2FA
2. Autorización: Permisos por módulo
3. Validación: Cliente + Servidor
4. Encriptación: Contraseñas Argon2ID
5. Auditoría: Registro de todas las acciones
```

---

## Resumen de Permisos por Rol

| Módulo | Admin | Gerente | Supervisor | Almacenista | Funcionario |
|--------|-------|---------|------------|-------------|------------|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ |
| Categorías | CRUD | CRUD | R | R | R |
| Productos | CRUD | CRUD | R | R | R |
| Movimientos | CRUD | CRUD | R | CRE | R |
| Órdenes | CRUD | CRUD | R | - | R |
| Devoluciones | CRUD | CRUD | R | CR | R |
| Anomalías | CRUD | CRUD | CRUD | CR | R |
| Estadísticas | ✓ | ✓ | ✓ | - | - |
| Reportes | ✓ | ✓ | ✓ | - | R |
| Usuarios | CRUD | R | - | - | - |
| Permisos | CRUD | - | - | - | - |
| Configuración | CRUD | - | - | - | - |

**Leyenda**: C=Crear, R=Leer, U=Actualizar, D=Eliminar, E=Especial

---

## Conclusión

El sistema ARCO está diseñado con una arquitectura modular que permite:
- Gestión completa de inventarios
- Control de acceso granular
- Trazabilidad total de operaciones
- Reportes y análisis avanzados
- Seguridad en múltiples capas
- Escalabilidad y mantenibilidad

Cada módulo funciona de forma independiente pero integrada, permitiendo un flujo de trabajo eficiente y seguro.
