# Especificación de Requerimientos de Software (SRS)
## Sistema ARCO - Gestión de Inventarios

**Versión:** 2.0  
**Fecha:** 15 de Diciembre de 2025  
**Estándar:** IEEE 830-1998  

---

## 1. Introducción

### 1.1 Propósito
Este documento especifica los requerimientos funcionales y no funcionales del Sistema ARCO (Administración y Registro de Control de Operaciones), un sistema web para la gestión integral de inventarios empresariales.

### 1.2 Alcance
El Sistema ARCO permitirá:
- Gestión completa de inventarios y productos
- Control de movimientos de entrada y salida
- Administración de usuarios con roles diferenciados
- Autenticación de dos factores (2FA)
- Generación de reportes y auditorías
- Gestión de órdenes de compra y devoluciones

### 1.3 Definiciones, Acrónimos y Abreviaciones
- **ARCO**: Administración y Registro de Control de Operaciones
- **2FA**: Autenticación de Dos Factores
- **SRS**: Especificación de Requerimientos de Software
- **MVC**: Modelo-Vista-Controlador

### 1.4 Referencias
- IEEE Std 830-1998: Práctica Recomendada para Especificaciones de Requerimientos de Software

---

## 2. Descripción General

### 2.1 Perspectiva del Producto
Sistema web desarrollado en PHP con base de datos MySQL, siguiendo arquitectura MVC para la gestión integral de inventarios empresariales.

### 2.2 Funciones del Producto
- **Gestión de Inventarios**: Control de productos, categorías y stock
- **Control de Movimientos**: Registro de entradas, salidas y transferencias
- **Administración de Usuarios**: Gestión de roles y permisos
- **Seguridad**: Autenticación de dos factores y auditoría
- **Reportes**: Generación de informes y comprobantes

### 2.3 Características de los Usuarios

#### 2.3.1 Administrador del Sistema
- **Descripción**: Usuario con acceso completo al sistema
- **Responsabilidades**: Configuración general, gestión de usuarios, auditorías
- **Conocimientos**: Técnicos avanzados en sistemas

#### 2.3.2 Administrador de Almacén
- **Descripción**: Responsable de la gestión operativa del inventario
- **Responsabilidades**: Gestión de productos, movimientos, reportes
- **Conocimientos**: Gestión de inventarios, procesos logísticos

#### 2.3.3 Supervisor
- **Descripción**: Usuario con permisos de supervisión y control
- **Responsabilidades**: Revisión de movimientos, aprobaciones, reportes
- **Conocimientos**: Procesos de control y supervisión

#### 2.3.4 Almacenista
- **Descripción**: Usuario operativo del almacén
- **Responsabilidades**: Registro de movimientos, consultas de inventario
- **Conocimientos**: Operaciones básicas de almacén

#### 2.3.5 Funcionario de Almacén
- **Descripción**: Usuario con acceso limitado para consultas
- **Responsabilidades**: Consulta de inventarios y reportes básicos
- **Conocimientos**: Operaciones básicas del sistema

---

## 3. Requerimientos Específicos

### 3.1 Requerimientos Funcionales

#### RF-001: Autenticación de Dos Factores
**Prioridad**: Alta  
**Descripción**: El sistema debe implementar autenticación de dos factores mediante correo electrónico o SMS.

**Criterios de Aceptación**:
- El usuario puede elegir entre verificación por email o SMS
- Los códigos de verificación expiran en 10 minutos
- El sistema envía códigos de 6 dígitos numéricos
- Se registra cada intento de verificación

#### RF-002: Gestión de Roles y Permisos
**Prioridad**: Alta  
**Descripción**: El sistema debe permitir la creación, edición y asignación de roles con permisos específicos.

**Criterios de Aceptación**:
- Cinco roles predefinidos: Administrador del Sistema, Administrador de Almacén, Supervisor, Almacenista, Funcionario
- Permisos granulares por funcionalidad
- Interfaz de administración para gestión de roles
- Auditoría de cambios en permisos

#### RF-003: Gestión de Órdenes de Compra
**Prioridad**: Media  
**Descripción**: El sistema debe permitir verificar y gestionar información de órdenes de compra.

**Criterios de Aceptación**:
- Registro de proveedor, materiales, cantidades, precios
- Estados: pendiente, recibida, cancelada
- Integración con inventario existente
- Trazabilidad completa de órdenes

#### RF-004: Formulario de Anomalías y Novedades
**Prioridad**: Media  
**Descripción**: El sistema debe proporcionar un formulario para reportar incidentes e irregularidades.

**Criterios de Aceptación**:
- Categorización de tipos de anomalías
- Campos para descripción detallada
- Asignación de responsables para seguimiento
- Notificaciones automáticas

#### RF-005: Generación de Comprobantes Mejorada
**Prioridad**: Media  
**Descripción**: El sistema debe generar comprobantes detallados de movimientos de materiales.

**Criterios de Aceptación**:
- Información completa: materiales, cantidades, responsables, destinos
- Formato PDF para impresión
- Numeración consecutiva automática
- Firma digital opcional

#### RF-006: Gestión de Devoluciones
**Prioridad**: Media  
**Descripción**: El sistema debe registrar y gestionar devoluciones de materiales.

**Criterios de Aceptación**:
- Motivos de devolución categorizados
- Actualización automática de inventario
- Trazabilidad de materiales devueltos
- Reportes de devoluciones

#### RF-007: Gestión de Materiales Recibidos
**Prioridad**: Media  
**Descripción**: El sistema debe registrar materiales recibidos en el almacén.

**Criterios de Aceptación**:
- Registro desde órdenes de compra
- Verificación de cantidades recibidas
- Actualización automática de inventario
- Documentación de recepción

#### RF-008: Registro de Auditoría
**Prioridad**: Alta  
**Descripción**: El sistema debe mantener un registro detallado de todas las acciones de usuarios.

**Criterios de Aceptación**:
- Registro de fecha, hora, usuario, acción, datos afectados
- Acceso exclusivo para administradores
- Búsqueda y filtrado de registros
- Exportación de logs de auditoría

### 3.2 Requerimientos No Funcionales

#### RNF-001: Seguridad
- Encriptación de contraseñas con algoritmos seguros
- Sesiones con timeout automático
- Validación de entrada en todos los formularios
- Protección contra inyección SQL y XSS

#### RNF-002: Usabilidad
- Interfaz intuitiva y responsive
- Tiempo de respuesta menor a 3 segundos
- Compatibilidad con navegadores modernos
- Accesibilidad web (WCAG 2.1)

#### RNF-003: Rendimiento
- Soporte para 100 usuarios concurrentes
- Base de datos optimizada con índices
- Caché de consultas frecuentes
- Compresión de archivos estáticos

#### RNF-004: Mantenibilidad
- Código documentado y estructurado
- Arquitectura MVC clara
- Logs de errores detallados
- Respaldos automáticos de base de datos

---

## 4. Arquitectura del Sistema

### 4.1 Arquitectura General
El sistema sigue el patrón MVC (Modelo-Vista-Controlador):

```
/arco-sistema/
├── /aplicacion/
│   ├── /controladores/     # Lógica de control
│   ├── /modelos/          # Lógica de negocio y datos
│   ├── /vistas/           # Interfaces de usuario
│   └── /servicios/        # Servicios auxiliares
├── /recursos/
│   ├── /estilos/          # Archivos CSS
│   ├── /scripts/          # Archivos JavaScript
│   └── /imagenes/         # Recursos gráficos
├── /configuracion/        # Archivos de configuración
├── /documentacion/        # Documentación del sistema
└── /base-datos/          # Scripts SQL
```

### 4.2 Tecnologías Utilizadas
- **Backend**: PHP 8.0+
- **Base de Datos**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Servidor Web**: Apache/Nginx

---

## 5. Interfaces del Sistema

### 5.1 Interfaces de Usuario
- **Login con 2FA**: Autenticación segura
- **Dashboard**: Panel principal por rol
- **Gestión de Inventarios**: CRUD de productos y categorías
- **Movimientos**: Registro de entradas y salidas
- **Reportes**: Generación y visualización
- **Administración**: Gestión de usuarios y configuración

### 5.2 Interfaces de Hardware
- **Servidor**: Mínimo 4GB RAM, 2 CPU cores
- **Almacenamiento**: 50GB disponibles
- **Red**: Conexión estable a internet

### 5.3 Interfaces de Software
- **Sistema Operativo**: Linux/Windows Server
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **Base de Datos**: MySQL 8.0+ o MariaDB 10.5+

---

## 6. Restricciones de Diseño

### 6.1 Restricciones Tecnológicas
- Desarrollo en PHP sin frameworks externos pesados
- Compatibilidad con MySQL/MariaDB
- Responsive design para dispositivos móviles

### 6.2 Restricciones de Seguridad
- Cumplimiento con estándares de seguridad web
- Encriptación de datos sensibles
- Auditoría completa de acciones

---

## 7. Atributos de Calidad

### 7.1 Confiabilidad
- Disponibilidad del 99.5%
- Recuperación automática ante fallos
- Respaldos diarios automáticos

### 7.2 Escalabilidad
- Arquitectura preparada para crecimiento
- Optimización de consultas de base de datos
- Caché implementado estratégicamente

---

## 8. Apéndices

### 8.1 Glosario de Términos
- **Inventario**: Conjunto de productos almacenados
- **Movimiento**: Transacción de entrada o salida de productos
- **Rol**: Conjunto de permisos asignados a un usuario
- **Auditoría**: Registro de acciones para control y seguimiento

### 8.2 Casos de Uso Principales
1. Autenticación de usuario con 2FA
2. Registro de entrada de productos
3. Generación de reportes de inventario
4. Gestión de roles y permisos
5. Registro de anomalías

---

**Documento elaborado según estándar IEEE 830-1998**  
**Sistema ARCO - Gestión de Inventarios**