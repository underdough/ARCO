# Arquitectura del Sistema ARCO
## Gestión de Inventarios

---

## 1. Visión General de la Arquitectura

### 1.1 Patrón Arquitectónico
El Sistema ARCO implementa el patrón **MVC (Modelo-Vista-Controlador)** con una arquitectura de 3 capas:

```
┌─────────────────────────────────────────────────────────┐
│                    CAPA DE PRESENTACIÓN                 │
│  ┌─────────────────┐  ┌─────────────────┐              │
│  │     Vistas      │  │   Recursos Web  │              │
│  │   (HTML/CSS)    │  │   (CSS/JS/IMG)  │              │
│  └─────────────────┘  └─────────────────┘              │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│                   CAPA DE LÓGICA DE NEGOCIO            │
│  ┌─────────────────┐  ┌─────────────────┐              │
│  │  Controladores  │  │    Servicios    │              │
│  │      (PHP)      │  │   Auxiliares    │              │
│  └─────────────────┘  └─────────────────┘              │
└─────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────┐
│                    CAPA DE DATOS                        │
│  ┌─────────────────┐                                    │
│  │     Modelos     │                                    │
│  │      (PHP)      │                                    │
│  └─────────────────┘                                    │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Estructura de Directorios Propuesta

```
/arco-sistema/
├── /aplicacion/                    # Código fuente principal
│   ├── /controladores/            # Controladores MVC
│   │   ├── AutenticacionControlador.php
│   │   ├── InventarioControlador.php
│   │   ├── UsuarioControlador.php
│   │   ├── ReporteControlador.php
│   │   └── ConfiguracionControlador.php
│   │
│   ├── /modelos/                  # Modelos de datos
│   │   ├── Usuario.php
│   │   ├── Producto.php
│   │   ├── Categoria.php
│   │   ├── Movimiento.php
│   │   ├── OrdenCompra.php
│   │   └── Auditoria.php
│   │
│   ├── /vistas/                   # Interfaces de usuario
│   │   ├── /autenticacion/
│   │   │   ├── login.php
│   │   │   ├── verificacion-2fa.php
│   │   │   └── recuperar-contrasena.php
│   │   ├── /dashboard/
│   │   │   └── panel-principal.php
│   │   ├── /inventario/
│   │   │   ├── productos.php
│   │   │   ├── categorias.php
│   │   │   └── movimientos.php
│   │   ├── /administracion/
│   │   │   ├── usuarios.php
│   │   │   ├── roles.php
│   │   │   └── configuracion.php
│   │   └── /reportes/
│   │       ├── inventario.php
│   │       └── auditoria.php
│   │
│   ├── /servicios/                # Servicios auxiliares
│   │   ├── /autenticacion/
│   │   │   ├── AutenticacionDosFactores.php
│   │   │   └── GestorSesiones.php
│   │   ├── /notificaciones/
│   │   │   ├── ServicioEmail.php
│   │   │   └── ServicioSMS.php
│   │   ├── /reportes/
│   │   │   └── GeneradorReportes.php
│   │   └── /utilidades/
│   │       ├── Validador.php
│   │       └── Encriptador.php
│   │
│   └── /middleware/               # Middleware de seguridad
│       ├── AutenticacionMiddleware.php
│       ├── AutorizacionMiddleware.php
│       └── ValidacionMiddleware.php
│
├── /recursos/                     # Recursos estáticos
│   ├── /estilos/                 # Archivos CSS
│   │   ├── /componentes/
│   │   ├── /paginas/
│   │   └── estilos-globales.css
│   │
│   ├── /scripts/                 # Archivos JavaScript
│   │   ├── /componentes/
│   │   ├── /paginas/
│   │   └── scripts-globales.js
│   │
│   └── /imagenes/                # Recursos gráficos
│       ├── /iconos/
│       ├── /logos/
│       └── /ilustraciones/
│
├── /configuracion/               # Configuración del sistema
│   ├── base-datos.php           # Configuración de BD
│   ├── aplicacion.php           # Configuración general
│   ├── seguridad.php           # Configuración de seguridad
│   └── rutas.php               # Definición de rutas
│
├── /base-datos/                 # Scripts de base de datos
│   ├── /migraciones/           # Scripts de migración
│   ├── /semillas/              # Datos iniciales
│   └── esquema-completo.sql    # Esquema completo
│
├── /documentacion/             # Documentación del proyecto
│   ├── especificacion_requerimientos_software.md
│   ├── arquitectura_sistema.md
│   ├── manual_usuario.md
│   └── guia_instalacion.md
│
├── /pruebas/                   # Pruebas del sistema
│   ├── /unitarias/
│   ├── /integracion/
│   └── /funcionales/
│
├── /logs/                      # Archivos de registro
│   ├── aplicacion.log
│   ├── errores.log
│   └── auditoria.log
│
├── /respaldos/                 # Respaldos automáticos
│   └── /base-datos/
│
├── .htaccess                   # Configuración Apache
├── index.php                   # Punto de entrada
└── README.md                   # Documentación principal
```

---

## 2. Componentes Principales

### 2.1 Capa de Presentación

#### 2.1.1 Vistas (Views)
- **Responsabilidad**: Presentar información al usuario
- **Tecnologías**: HTML5, CSS3, JavaScript ES6+
- **Características**:
  - Diseño responsive
  - Accesibilidad web (WCAG 2.1)
  - Componentes reutilizables
  - Validación del lado cliente

#### 2.1.2 Recursos Estáticos
- **Estilos CSS**: Organizados por componentes y páginas
- **Scripts JavaScript**: Funcionalidad interactiva
- **Imágenes**: Recursos gráficos optimizados

### 2.2 Capa de Lógica de Negocio

#### 2.2.1 Controladores (Controllers)
- **Responsabilidad**: Manejar peticiones HTTP y coordinar respuestas
- **Patrón**: Un controlador por módulo funcional
- **Características**:
  - Validación de entrada
  - Manejo de errores
  - Coordinación con modelos y servicios

#### 2.2.2 Servicios Auxiliares
- **Autenticación**: Gestión de login y 2FA
- **Notificaciones**: Email y SMS
- **Reportes**: Generación de documentos
- **Utilidades**: Funciones comunes

#### 2.2.3 Middleware
- **Autenticación**: Verificar sesiones válidas
- **Autorización**: Verificar permisos de rol
- **Validación**: Sanitizar y validar datos

### 2.3 Capa de Datos

#### 2.3.1 Modelos (Models)
- **Responsabilidad**: Representar entidades de negocio
- **Patrón**: Active Record simplificado
- **Características**:
  - Validación de datos
  - Relaciones entre entidades
  - Consultas optimizadas

#### 2.3.2 Base de Datos
- **Motor**: MySQL 8.0+
- **Características**:
  - Índices optimizados
  - Restricciones de integridad
  - Procedimientos almacenados
  - Triggers para auditoría

---

## 3. Flujo de Datos

### 3.1 Flujo de Petición HTTP

```
1. Usuario → Navegador → Petición HTTP
2. .htaccess → index.php (Router)
3. Router → Middleware de Autenticación
4. Middleware → Controlador específico
5. Controlador → Modelo/Servicio
6. Modelo → Base de Datos
7. Base de Datos → Modelo → Controlador
8. Controlador → Vista
9. Vista → Respuesta HTML → Usuario
```

### 3.2 Flujo de Autenticación 2FA

```
1. Usuario ingresa credenciales
2. AutenticacionControlador valida usuario/contraseña
3. Si es válido, genera código 2FA
4. ServicioEmail/SMS envía código
5. Usuario ingresa código de verificación
6. Sistema valida código y crea sesión
7. Redirección al dashboard correspondiente
```

---

## 4. Patrones de Diseño Implementados

### 4.1 MVC (Modelo-Vista-Controlador)
- **Separación de responsabilidades**
- **Mantenibilidad mejorada**
- **Reutilización de componentes**

### 4.2 Repository Pattern
- **Abstracción de acceso a datos**
- **Facilita pruebas unitarias**
- **Intercambiabilidad de fuentes de datos**

### 4.3 Service Layer
- **Lógica de negocio centralizada**
- **Reutilización entre controladores**
- **Transacciones complejas**

### 4.4 Middleware Pattern
- **Procesamiento en cadena**
- **Separación de concerns**
- **Reutilización de validaciones**

---

## 5. Seguridad

### 5.1 Medidas Implementadas

#### 5.1.1 Autenticación
- Contraseñas hasheadas con bcrypt
- Autenticación de dos factores (2FA)
- Sesiones seguras con tokens
- Timeout automático de sesiones

#### 5.1.2 Autorización
- Control de acceso basado en roles (RBAC)
- Permisos granulares por funcionalidad
- Validación en cada petición

#### 5.1.3 Protección de Datos
- Validación y sanitización de entrada
- Prepared statements (anti SQL injection)
- Escape de salida (anti XSS)
- CSRF tokens en formularios

#### 5.1.4 Auditoría
- Registro completo de acciones
- Logs de seguridad
- Monitoreo de intentos fallidos

---

## 6. Rendimiento

### 6.1 Optimizaciones

#### 6.1.1 Base de Datos
- Índices en columnas frecuentemente consultadas
- Consultas optimizadas con EXPLAIN
- Paginación en listados grandes
- Caché de consultas frecuentes

#### 6.1.2 Frontend
- Minificación de CSS/JS
- Compresión de imágenes
- Lazy loading de contenido
- CDN para recursos estáticos

#### 6.1.3 Backend
- Caché de sesiones en memoria
- Pooling de conexiones de BD
- Compresión gzip de respuestas

---

## 7. Escalabilidad

### 7.1 Consideraciones de Diseño

#### 7.1.1 Horizontal
- Arquitectura stateless
- Balanceador de carga preparado
- Sesiones en base de datos/Redis

#### 7.1.2 Vertical
- Código optimizado para memoria
- Consultas eficientes
- Caché estratégico

---

## 8. Monitoreo y Mantenimiento

### 8.1 Logs del Sistema
- **Aplicación**: Eventos generales
- **Errores**: Excepciones y fallos
- **Auditoría**: Acciones de usuarios
- **Rendimiento**: Métricas de tiempo

### 8.2 Respaldos
- Respaldo diario automático de BD
- Versionado de código con Git
- Documentación actualizada

---

**Arquitectura diseñada para escalabilidad, seguridad y mantenibilidad**  
**Sistema ARCO - Gestión de Inventarios**