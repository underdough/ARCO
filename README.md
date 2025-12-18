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
- **Autenticación de Dos Factores (2FA)** por email
- **Gestión de Roles y Permisos** granular y flexible
- **Encriptación de contraseñas** con Argon2ID
- **Protección CSRF** y validación de entrada
- **Auditoría completa** de acciones de usuarios
- **Dispositivos confiables** para 2FA

### 📦 Gestión de Inventarios
- Control completo de productos y categorías
- Registro de movimientos de entrada y salida
- Alertas automáticas de stock bajo
- Gestión de órdenes de compra y devoluciones
- Trazabilidad completa de materiales
- Paginación y búsqueda avanzada

### 📊 Reportes y Análisis
- Dashboard con estadísticas en tiempo real
- Generación de reportes personalizados
- Exportación en múltiples formatos (PDF, Excel, CSV)
- Gráficos interactivos con Chart.js
- Comprobantes de movimientos

### 👥 Administración de Usuarios
- Cinco roles predefinidos con permisos específicos
- Interfaz de administración intuitiva
- Gestión de sesiones seguras
- Registro de actividad detallado
- Recuperación de contraseña por email

## 🏗️ Estructura del Proyecto

```
/ARCO/
├── /componentes/              # Estilos CSS modularizados
├── /documentacion/            # Documentación del sistema
├── /ejemplos/                 # Ejemplos de uso
├── /recursos/                 # Recursos estáticos (scripts, imágenes)
├── /servicios/                # Servicios backend (PHP)
├── /SOLOjavascript/           # Scripts JavaScript
├── /tests/                    # Archivos de prueba
├── /vistas/                   # Interfaces de usuario (PHP)
├── /vendor/                   # Dependencias Composer
├── .htaccess                  # Configuración Apache
├── composer.json              # Dependencias del proyecto
├── login.html                 # Página de login
└── README.md                  # Este archivo
```

## 🚀 Instalación Rápida

### Requisitos del Sistema

- **PHP**: 8.0 o superior
- **MySQL**: 8.0 o superior (o MariaDB 10.5+)
- **Servidor Web**: Apache 2.4+ con mod_rewrite
- **Extensiones PHP**: PDO, mysqli, mbstring, openssl

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

3. **Importar esquema de base de datos**
   ```bash
   mysql -u root -p arco_bdd < documentacion/instalar_estadisticas.sql
   ```

4. **Configurar conexión a base de datos**
   - Editar `servicios/conexion.php` con tus credenciales

5. **Configurar email (opcional pero recomendado)**
   - Copiar `servicios/config_email.ejemplo.php` a `servicios/config_email.php`
   - Configurar credenciales SMTP
   - Ver [Configuración de Email](documentacion/configuracion_email_produccion.md)

6. **Acceder al sistema**
   - Abrir: `http://localhost/ARCO/login.html`
   - Credenciales por defecto: Ver documentación de instalación

## 🔧 Configuración

### Configuración de Base de Datos

Editar `servicios/conexion.php`:

```php
const SERVIDOR = 'localhost';
const PUERTO = 3306;
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

### Dashboard
- Estadísticas en tiempo real
- Gráficos interactivos
- Resumen de movimientos
- Alertas de stock bajo

### Gestión de Inventario
- **Categorías**: Crear, editar, eliminar categorías
- **Productos**: Gestión completa de materiales
- **Movimientos**: Registro de entrada/salida
- **Órdenes de Compra**: Gestión de compras
- **Devoluciones**: Control de devoluciones

### Reportes
- Reportes de movimientos
- Análisis de inventario
- Reportes de usuarios
- Exportación en múltiples formatos

### Administración
- **Usuarios**: Gestión de cuentas
- **Permisos**: Control de acceso granular
- **Auditoría**: Registro de actividades
- **Configuración**: Parámetros del sistema

## 🧪 Pruebas

Los archivos de prueba se encuentran en la carpeta `/tests`:

```bash
# Archivos de prueba disponibles
tests/test_requerimientos.php
tests/test_api_categorias.php
tests/test_api_productos.php
tests/test_listar_categorias.php
tests/test_listar_productos.php
tests/verificar_campos_anomalias.php
tests/verificar_sistema.php
```

## 📞 Soporte

### Problemas Comunes

**Error de conexión a BD**
- Verificar credenciales en `servicios/conexion.php`
- Verificar que MySQL esté ejecutándose
- Verificar permisos de usuario en BD

**Problemas con 2FA**
- Verificar configuración de email en `servicios/config_email.php`
- Revisar logs de email
- Probar con `servicios/test_email.php`

**Problemas de permisos**
- Verificar tabla `permisos` en base de datos
- Ejecutar script de instalación de permisos
- Revisar documentación de permisos

Ver [Solución de Problemas](documentacion/SOLUCION_PROBLEMAS.md) para más detalles.

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT.

## 🙏 Agradecimientos

Desarrollado con dedicación para la gestión eficiente de inventarios.

---

**Sistema ARCO v2.0** - Gestión de Inventarios Profesional
