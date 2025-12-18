# 🧪 Archivos de Prueba - Sistema ARCO

Esta carpeta contiene archivos de prueba para validar el funcionamiento del sistema ARCO.

## 📋 Archivos de Prueba

### Pruebas de API

#### `test_api_categorias.php`
Prueba el endpoint de listado de categorías con paginación.
- Verifica que la API devuelva JSON válido
- Valida la estructura de respuesta
- Comprueba paginación

**Uso:**
```bash
php tests/test_api_categorias.php
```

#### `test_api_productos.php`
Prueba el endpoint de listado de productos con búsqueda y ordenamiento.
- Verifica que la API devuelva JSON válido
- Valida estructura de productos
- Comprueba búsqueda y filtros

**Uso:**
```bash
php tests/test_api_productos.php
```

### Pruebas de Servicios

#### `test_listar_categorias.php`
Prueba el servicio de listado de categorías.
- Verifica conexión a base de datos
- Valida consultas SQL
- Comprueba estructura de datos

**Uso:**
```bash
php tests/test_listar_categorias.php
```

#### `test_listar_productos.php`
Prueba el servicio de listado de productos.
- Verifica conexión a base de datos
- Valida consultas SQL
- Comprueba estructura de datos

**Uso:**
```bash
php tests/test_listar_productos.php
```

#### `test_email.php`
Prueba la configuración de envío de emails.
- Verifica credenciales SMTP
- Prueba envío de email de prueba
- Valida configuración

**Uso:**
```bash
php tests/test_email.php
```

### Pruebas de Sistema

#### `test_requerimientos.php`
Verifica que se cumplan todos los requerimientos del sistema.
- Valida extensiones PHP requeridas
- Comprueba permisos de archivos
- Verifica configuración

**Uso:**
```bash
php tests/test_requerimientos.php
```

#### `verificar_sistema.php`
Realiza verificación general del sistema.
- Estado de base de datos
- Configuración de seguridad
- Módulos disponibles

**Uso:**
```bash
php tests/verificar_sistema.php
```

#### `verificar_campos_anomalias.php`
Verifica la estructura de la tabla de anomalías.
- Valida campos de la tabla
- Comprueba tipos de datos
- Verifica índices

**Uso:**
```bash
php tests/verificar_campos_anomalias.php
```

### Pruebas de Debug

#### `test_categorias_debug.php`
Herramienta de debug para categorías.
- Muestra estructura de datos
- Valida consultas
- Genera reportes de debug

**Uso:**
```bash
php tests/test_categorias_debug.php
```

## 🚀 Cómo Ejecutar las Pruebas

### Desde línea de comandos

```bash
# Ejecutar una prueba específica
php tests/test_requerimientos.php

# Ejecutar todas las pruebas
for file in tests/test_*.php; do php "$file"; done
```

### Desde el navegador

Acceder a través de HTTP (si está configurado):
```
http://localhost/ARCO/tests/test_requerimientos.php
```

## ✅ Checklist de Pruebas

Antes de poner el sistema en producción, ejecutar:

- [ ] `test_requerimientos.php` - Verificar requisitos
- [ ] `verificar_sistema.php` - Verificar sistema general
- [ ] `test_email.php` - Verificar configuración de email
- [ ] `test_listar_categorias.php` - Verificar categorías
- [ ] `test_listar_productos.php` - Verificar productos
- [ ] `test_api_categorias.php` - Verificar API categorías
- [ ] `test_api_productos.php` - Verificar API productos

## 📊 Interpretación de Resultados

### Resultado Exitoso
```
✓ Prueba completada exitosamente
✓ Todos los validaciones pasaron
```

### Resultado con Errores
```
✗ Error: [descripción del error]
✗ Verificar: [recomendación]
```

## 🔧 Solución de Problemas

### Error de conexión a base de datos
- Verificar credenciales en `servicios/conexion.php`
- Verificar que MySQL esté ejecutándose
- Verificar permisos de usuario

### Error de email
- Verificar configuración en `servicios/config_email.php`
- Verificar credenciales SMTP
- Revisar logs de email

### Error de permisos
- Verificar permisos de archivos
- Verificar permisos de carpetas
- Ejecutar con permisos adecuados

## 📝 Notas

- Las pruebas no modifican datos de producción
- Se recomienda ejecutar en ambiente de desarrollo
- Algunos tests requieren conexión a base de datos
- Algunos tests requieren configuración de email

---

**Última actualización:** Diciembre 2025
