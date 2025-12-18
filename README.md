# 📦 Sistema ARCO - Gestión de Inventarios

**Administración y Registro de Control de Operaciones**

Sistema web moderno para la gestión integral de inventarios empresariales con seguridad avanzada, autenticación de dos factores y control de acceso basado en roles.

[![Versión](https://img.shields.io/badge/versión-2.0.0-blue.svg)](#)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg)](https://mysql.com)
[![Licencia](https://img.shields.io/badge/licencia-MIT-green.svg)](#)

---

## 📋 Descripción

ARCO es un sistema web completo para la gestión integral de inventarios empresariales. Incluye funcionalidades avanzadas de seguridad, autenticación de dos factores, gestión de roles y auditoría completa de operaciones.

## ✨ Características Principales

### 🔐 Seguridad Avanzada
- **Autenticación de Dos Factores (2FA)** por email con códigos de 6 dígitos
- **Gestión de Roles y Permisos** granular y flexible por módulo
- **Encriptación de contraseñas** con Argon2ID
- **Protección CSRF** y validación de entrada en todos los formularios
- **Auditoría completa** de acciones de usuarios con timestamps
- **Dispositivos confiables** para 2FA (recordar por 30 días)
- **Middleware de permisos** automático en todas las vistas
- **Content Security Policy** configurado en .htaccess
- **Sesiones seguras** con tokens únicos y expiración

### 📦 Gestión de Inventarios
- Control completo de productos y categorías con CRUD
- Registro de movimientos de entrada y salida con comprobantes
- Alertas automáticas de stock bajo configurables
- Gestión de órdenes de compra y devoluciones
- Trazabilidad completa de materiales por lote
- **Paginación inteligente** (10 registros por página, máximo 5 botones)
- **Búsqueda en tiempo real** por nombre y descripción
- **Filtros avanzados** por estado, fecha, categoría y ordenamiento
- Impresión de comprobantes con información de empresa
- Control de ubicaciones físicas en almacén

### 📊 Reportes y Análisis
- **Dashboard interactivo** con estadísticas en tiempo real
- **Módulo de Estadísticas** con 5 gráficos diferentes (Chart.js):
  - Resumen general del sistema
  - Movimientos por mes (líneas)
  - Distribución por categorías (barras)
  - Stock por categorías (dona)
  - Tipos de movimiento (barras horizontales)
- Generación de reportes personalizados por período
- Exportación en múltiples formatos (PDF, Excel, CSV)
- Comprobantes de movimientos, órdenes y devoluciones
- Análisis de anomalías y resolución

### 👥 Administración de Usuarios
- **Cinco roles predefinidos** con permisos específicos:
  - Administrador (acceso completo)
  - Gerente (gestión completa)
  - Supervisor (supervisión y reportes)
  - Almacenista (operaciones de inventario)
  - Funcionario (consultas y reportes básicos)
- **Visualización de nombre y rol** en todos los módulos
- Interfaz de administración intuitiva con búsqueda
- Gestión de sesiones seguras con expiración
- Registro de actividad detallado por usuario
- Recuperación de contraseña por email con token único
- Activación/desactivación de cuentas
- Gestión de permisos individuales o por rol

## 🏗️ Estructura del Proyecto

```
/ARCO/
├── /componentes/              # Estilos CSS y JavaScript modularizados
│   ├── /img/                  # Imágenes y recursos gráficos
│   ├── categorias.css         # Estilos de gestión de categorías
│   ├── configuracion.css      # Estilos de configuración
│   ├── dashboard.css          # Estilos del dashboard
│   ├── estadisticas.css       # Estilos de estadísticas
│   ├── gestion_permisos.css   # Estilos de permisos
│   ├── gestion_usuarios.js    # Lógica de gestión de usuarios
│   ├── login-pure.css         # Estilos de login
│   ├── modal-common.css       # Estilos de modales
│   ├── movimientos.css        # Estilos de movimientos
│   ├── productos.css          # Estilos de productos
│   └── reportes.css           # Estilos de reportes
│
├── /documentacion/            # Documentación completa del sistema
│   ├── INICIO_RAPIDO.md       # Guía de inicio rápido
│   ├── COMO_CONFIGURAR_EMAIL.md
│   ├── configuracion_email_produccion.md
│   ├── SISTEMA_2FA.md         # Documentación de 2FA
│   ├── SISTEMA_PERMISOS.md    # Sistema de permisos
│   ├── SISTEMA_ARCO_GUIA.md   # Guía completa del sistema
│   ├── arquitectura_sistema.md
│   ├── especificacion_requerimientos_software.md
│   ├── SOLUCION_PROBLEMAS.md
│   ├── INDICE_DOCUMENTACION.md
│   ├── INFORMACION_EMPRESA_COMPROBANTES.md
│   ├── instalar_estadisticas.sql
│   └── INSTRUCCIONES_INSTALACION_MVP.md
│
├── /ejemplos/                 # Ejemplos de implementación
│   └── ejemplo_uso_permisos.php
│
├── /recursos/                 # Recursos estáticos
│   └── /scripts/              # Scripts auxiliares
│
├── /servicios/                # Servicios backend
│   ├── /librerias/            # Librerías externas
│   ├── /reportes/             # Reportes generados
│   ├── conexion.php           # Conexión a base de datos
│   ├── autenticador.php       # Sistema de autenticación
│   ├── middleware_permisos.php # Control de acceso
│   ├── menu_dinamico.php      # Generación de menú
│   ├── email_sender.php       # Envío de emails
│   ├── two_factor_auth.php    # Autenticación 2FA
│   ├── auditoria.php          # Sistema de auditoría
│   ├── estadisticas_data.php  # API de estadísticas
│   ├── listar_categorias.php  # API de categorías
│   ├── listar_productos.php   # API de productos
│   ├── guardar_movimiento.php # Registro de movimientos
│   ├── imprimir_movimiento.php
│   ├── imprimir_orden_compra.php
│   ├── imprimir_devolucion.php
│   └── [70+ servicios más]
│
├── /SOLOjavascript/           # Scripts JavaScript modulares
│   ├── productos.js           # Lógica de productos
│   ├── productos_protegido.js
│   └── categorias_protegido.js
│
├── /tests/                    # Suite de pruebas
│   ├── README.md              # Documentación de pruebas
│   ├── test_api_categorias.php
│   ├── test_api_productos.php
│   ├── test_email.php
│   ├── test_requerimientos.php
│   ├── verificar_sistema.php
│   └── [más archivos de prueba]
│
├── /vistas/                   # Interfaces de usuario (Frontend)
│   ├── dashboard.php          # Panel principal
│   ├── categorias.php         # Gestión de categorías
│   ├── productos.php          # Gestión de productos
│   ├── movimientos.php        # Registro de movimientos
│   ├── ordenes_compra.php     # Órdenes de compra
│   ├── devoluciones.php       # Devoluciones
│   ├── anomalias.php          # Gestión de anomalías
│   ├── anomalias_reportes.php # Reportes de anomalías
│   ├── estadisticas.php       # Estadísticas y gráficos
│   ├── reportes.php           # Generación de reportes
│   ├── gestion_usuarios.php   # Administración de usuarios
│   ├── gestion_permisos.php   # Gestión de permisos
│   ├── configuracion.php      # Configuración del sistema
│   └── recuperar-contra.php   # Recuperación de contraseña
│
├── /vendor/                   # Dependencias Composer
│   ├── /phpmailer/            # PHPMailer para emails
│   └── autoload.php
│
├── .htaccess                  # Configuración Apache (seguridad)
├── .gitignore                 # Archivos ignorados por Git
├── composer.json              # Dependencias del proyecto
├── composer.lock              # Versiones bloqueadas
├── login.html                 # Página de inicio de sesión
├── CAMBIOS_REALIZADOS.md      # Registro de cambios
├── CAMBIOS_USUARIO_ROL.md     # Cambios en sistema de roles
└── README.md                  # Este archivo
```

## 🚀 Instalación Rápida

### Requisitos del Sistema

- **PHP**: 8.0 o superior
- **MySQL**: 8.0 o superior (o MariaDB 10.5+)
- **Servidor Web**: Apache 2.4+ con mod_rewrite habilitado
- **Extensiones PHP**: PDO, mysqli, mbstring, openssl, json, session
- **Composer**: Para gestión de dependencias
- **Navegador**: Chrome, Firefox, Edge o Safari (versiones recientes)

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   git clone <repositorio>
   cd ARCO
   ```

2. **Crear base de datos**
   ```bash
   mysql -u root -p -e "CREATE DATABASE arco_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. **Instalar dependencias con Composer**
   ```bash
   composer install
   ```
   Esto instalará PHPMailer y otras dependencias necesarias.

4. **Importar esquema de base de datos**
   ```bash
   mysql -u root -p arco_bdd < documentacion/instalar_estadisticas.sql
   ```

5. **Configurar conexión a base de datos**
   - Editar `servicios/conexion.php` con tus credenciales:
   ```php
   const SERVIDOR = 'localhost';
   const PUERTO = <!--puerto que desee usar: 3306(predeterminado por MYSQL)-->;
   const NOMBRE_BD = 'arco_bdd';
   const USUARIO = 'tu_usuario';
   const CONTRASEÑA = 'tu_contraseña';
   ```

6. **Configurar email (opcional pero recomendado para 2FA)**
   - Copiar `servicios/config_email.ejemplo.php` a `servicios/config_email.php`
   - Configurar credenciales SMTP
   - Probar con: `http://localhost/ARCO/tests/test_email.php`
   - Ver [Configuración de Email](documentacion/configuracion_email_produccion.md)

7. **Configurar información de empresa (para comprobantes)**
   - Acceder al módulo de Configuración
   - Completar datos de la empresa (nombre, dirección, teléfono, NIF, email)
   - Ver [Información de Empresa](documentacion/INFORMACION_EMPRESA_COMPROBANTES.md)

8. **Acceder al sistema**
   - Abrir: `http://localhost/ARCO/login.html`
   - Credenciales por defecto: Ver [Instrucciones de Instalación](documentacion/INSTRUCCIONES_INSTALACION_MVP.md)

## 🔧 Configuración

### Configuración de Base de Datos

Editar `servicios/conexion.php`:

```php
const SERVIDOR = 'localhost';
const PUERTO = <!--puerto que desee usar: 3306(predeterminado por MYSQL)-->;
const NOMBRE_BD = 'arco_bdd';
const USUARIO = 'tu_usuario';
const CONTRASEÑA = 'tu_contraseña';
```

### Configuración de Email

Para habilitar 2FA y recuperación de contraseña:

1. Copiar `servicios/config_email.ejemplo.php` a `servicios/config_email.php`
2. Configurar credenciales SMTP
3. Probar con: `servicios/test_email.php`

**Proveedores Soportados:**
- Gmail (con contraseña de aplicación)
- Outlook/Hotmail
- Office 365
- SendGrid
- Mailgun
- Servidor SMTP personalizado

Ver [Guía Completa de Email](documentacion/configuracion_email_produccion.md) para instrucciones detalladas.

## 👤 Roles de Usuario

| Rol | Permisos | Funciones |
|-----|----------|-----------|
| **Administrador** | Acceso completo | Configuración, usuarios, auditoría |
| **Gerente** | Gestión completa | Inventario, reportes, usuarios |
| **Supervisor** | Supervisión | Revisión, aprobaciones, reportes |
| **Almacenista** | Operaciones | Movimientos, consultas |
| **Funcionario** | Consultas | Ver inventarios, reportes básicos |

## 📚 Documentación

### Documentos Principales

- [**Inicio Rápido**](documentacion/INICIO_RAPIDO.md) - Configuración en 5 minutos
- [**Cómo Configurar Email**](documentacion/COMO_CONFIGURAR_EMAIL.md) - Guía paso a paso
- [**Configuración Email Producción**](documentacion/configuracion_email_produccion.md) - Guía completa SMTP
- [**Especificación de Requerimientos**](documentacion/especificacion_requerimientos_software.md) - SRS IEEE 830
- [**Arquitectura del Sistema**](documentacion/arquitectura_sistema.md) - Diseño técnico
- [**Solución de Problemas**](documentacion/SOLUCION_PROBLEMAS.md) - Troubleshooting
- [**Sistema de Permisos**](documentacion/SISTEMA_PERMISOS.md) - Gestión de acceso
- [**Sistema 2FA**](documentacion/SISTEMA_2FA.md) - Autenticación de dos factores

### Índice Completo

Ver [Índice de Documentación](documentacion/INDICE_DOCUMENTACION.md) para navegación completa.

## 🔒 Seguridad

### Medidas Implementadas

- **Contraseñas**: Hash Argon2ID con salt
- **Sesiones**: Tokens seguros con expiración
- **2FA**: Códigos de 6 dígitos con expiración de 10 minutos
- **CSRF**: Tokens únicos por formulario
- **XSS**: Sanitización y escape de datos
- **SQL Injection**: Prepared statements
- **Headers**: Configuración de seguridad automática
- **Content Security Policy**: Protección contra ataques

### Checklist de Seguridad

- [ ] Cambiar credenciales de base de datos
- [ ] Configurar SSL/HTTPS
- [ ] Establecer contraseñas seguras
- [ ] Configurar respaldos automáticos
- [ ] Probar funcionalidad de 2FA
- [ ] Verificar permisos de archivos
- [ ] Configurar firewall

## 📊 Módulos Principales

### 📈 Dashboard (`vistas/dashboard.php`)
- Estadísticas en tiempo real con Chart.js
- Gráficos interactivos de movimientos
- Resumen de inventario actual
- Alertas de stock bajo automáticas
- Indicadores clave de rendimiento (KPIs)
- Visualización de nombre y rol del usuario

### 📦 Gestión de Inventario
- **Categorías** (`vistas/categorias.php`): 
  - Crear, editar, eliminar categorías
  - Filtros por estado (activas/inactivas)
  - Ordenamiento múltiple (nombre, productos, fecha)
  - Búsqueda en tiempo real
  - Paginación (10 registros por página)
  
- **Productos** (`vistas/productos.php`): 
  - Gestión completa de materiales
  - Control de stock y ubicación
  - Búsqueda avanzada y filtros
  - Paginación y ordenamiento
  - Trazabilidad completa
  
- **Movimientos** (`vistas/movimientos.php`): 
  - Registro de entrada/salida
  - Filtros por fecha, tipo y categoría
  - Impresión de comprobantes
  - Historial completo
  
- **Órdenes de Compra** (`vistas/ordenes_compra.php`): 
  - Gestión de órdenes
  - Seguimiento de estados
  - Impresión de documentos
  
- **Devoluciones** (`vistas/devoluciones.php`): 
  - Control de devoluciones
  - Registro de motivos
  - Comprobantes de devolución

### 🚨 Gestión de Anomalías
- **Anomalías** (`vistas/anomalias.php`):
  - Registro de incidencias
  - Seguimiento de estados
  - Asignación de responsables
  
- **Reportes de Anomalías** (`vistas/anomalias_reportes.php`):
  - Análisis de incidencias
  - Estadísticas de resolución
  - Exportación de reportes

### 📊 Estadísticas (`vistas/estadisticas.php`)
- Resumen general del sistema
- Movimientos por mes (gráfico de líneas)
- Distribución por categorías (gráfico de barras)
- Stock por categorías (gráfico de dona)
- Tipos de movimiento (gráfico de barras horizontales)
- Acceso restringido a administrador, gerente y supervisor

### 📄 Reportes (`vistas/reportes.php`)
- Reportes de movimientos personalizados
- Análisis de inventario por período
- Reportes de usuarios y actividad
- Exportación en múltiples formatos (PDF, Excel, CSV)
- Filtros avanzados por fecha y categoría

### 👥 Administración
- **Usuarios** (`vistas/gestion_usuarios.php`): 
  - Gestión completa de cuentas
  - Asignación de roles
  - Activación/desactivación
  - Visualización de nombre y rol
  
- **Permisos** (`vistas/gestion_permisos.php`): 
  - Control de acceso granular por módulo
  - Permisos: ver, crear, editar, eliminar
  - Gestión por rol o usuario individual
  - Middleware de protección automático
  
- **Auditoría** (`servicios/auditoria.php`): 
  - Registro automático de actividades
  - Trazabilidad completa de cambios
  - Consulta de historial
  
- **Configuración** (`vistas/configuracion.php`): 
  - Parámetros del sistema
  - Información de empresa (para comprobantes)
  - Configuración de copias de seguridad
  - Configuración de notificaciones
  - Gestión de 2FA

## 🧪 Pruebas

Los archivos de prueba se encuentran en la carpeta `/tests`. Ver [tests/README.md](tests/README.md) para documentación completa.

### Archivos de Prueba Disponibles

```bash
# Pruebas de API
tests/test_api_categorias.php      # Prueba API de categorías
tests/test_api_productos.php       # Prueba API de productos
tests/test_listar_categorias.php   # Prueba paginación de categorías
tests/test_listar_productos.php    # Prueba paginación de productos

# Pruebas de Sistema
tests/test_requerimientos.php      # Verificación de requerimientos
tests/verificar_sistema.php        # Verificación general del sistema
tests/verificar_campos_anomalias.php # Verificación de anomalías

# Pruebas de Email
tests/test_email.php               # Prueba configuración de email

# Pruebas de Debug
tests/test_categorias_debug.php    # Debug de categorías
```

### Ejecutar Pruebas

```bash
# Acceder desde el navegador
http://localhost/ARCO/tests/test_requerimientos.php
http://localhost/ARCO/tests/verificar_sistema.php
http://localhost/ARCO/tests/test_email.php
```

## 📞 Soporte y Solución de Problemas

### Problemas Comunes

**Error de conexión a BD**
- Verificar credenciales en `servicios/conexion.php`
- Verificar que MySQL esté ejecutándose: `mysql -u root -p`
- Verificar permisos de usuario en BD
- Verificar que la base de datos `arco_bdd` exista

**Problemas con 2FA**
- Verificar configuración de email en `servicios/config_email.php`
- Probar envío con: `http://localhost/ARCO/tests/test_email.php`
- Revisar logs en `servicios/log_empresa.txt`
- Verificar que el puerto SMTP no esté bloqueado por firewall
- Para Gmail: usar contraseña de aplicación, no contraseña normal

**Problemas de permisos**
- Verificar tabla `permisos` en base de datos
- Ejecutar: `http://localhost/ARCO/servicios/instalar_permisos.php`
- Verificar que el usuario tenga rol asignado
- Revisar [Sistema de Permisos](documentacion/SISTEMA_PERMISOS.md)

**Error 500 en módulos**
- Verificar logs de PHP: `php -l archivo.php`
- Verificar logs de Apache
- Verificar que todas las tablas existan en la BD
- Ejecutar: `http://localhost/ARCO/tests/verificar_sistema.php`

**Problemas con filtros o paginación**
- Limpiar caché del navegador (Ctrl+Shift+Del)
- Verificar consola del navegador (F12)
- Verificar que los servicios PHP respondan correctamente
- Probar con: `http://localhost/ARCO/tests/test_listar_categorias.php`

**Comprobantes sin información de empresa**
- Acceder a Configuración del Sistema
- Completar datos de empresa
- Verificar que el registro con id=2 exista en tabla `empresa`
- Ver [Información de Empresa](documentacion/INFORMACION_EMPRESA_COMPROBANTES.md)

Ver [Solución de Problemas](documentacion/SOLUCION_PROBLEMAS.md) para guía completa.

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT.

## 📝 Registro de Cambios

Ver archivos de cambios para detalles de actualizaciones:
- [CAMBIOS_REALIZADOS.md](CAMBIOS_REALIZADOS.md) - Historial completo de cambios
- [CAMBIOS_USUARIO_ROL.md](CAMBIOS_USUARIO_ROL.md) - Cambios en sistema de roles y filtros

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 8.0+
- **Base de Datos**: MySQL 8.0+ / MariaDB 10.5+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Librerías**:
  - PHPMailer 6.x (envío de emails)
  - Chart.js 3.x (gráficos interactivos)
  - Font Awesome 6.x (iconos)
  - Google Fonts (Poppins)
- **Servidor**: Apache 2.4+ con mod_rewrite
- **Gestión de Dependencias**: Composer

## 🤝 Contribuciones

Este es un proyecto privado. Para sugerencias o reportes de bugs, contactar al equipo de desarrollo.

## 🙏 Agradecimientos

Desarrollado con dedicación para la gestión eficiente de inventarios empresariales.

---

**Sistema ARCO v2.0** - Gestión de Inventarios Profesional  
*Administración y Registro de Control de Operaciones*

© 2024-2025 - Todos los derechos reservados
