# 🔧 Solución: Permisos No Aparecen

## Problema
Los permisos muestran 0 en todas las estadísticas y no aparece ningún dato en la matriz.

## Causa Probable
Las tablas de permisos no existen en la base de datos o están vacías.

## ✅ Solución Paso a Paso

### Paso 1: Verificar Base de Datos

1. **Abrir phpMyAdmin o MySQL Workbench**
2. **Seleccionar la base de datos** `arco_bdd`
3. **Verificar que existan estas tablas:**
   - `modulos`
   - `permisos`
   - `modulo_permisos`
   - `rol_permisos`
   - `auditoria_permisos`

### Paso 2: Ejecutar Script SQL

Si las tablas NO existen o están vacías:

**Opción A: Desde phpMyAdmin**
```
1. Abrir phpMyAdmin
2. Seleccionar base de datos "arco_bdd"
3. Ir a pestaña "SQL"
4. Abrir el archivo: base-datos/sistema_permisos_completo.sql
5. Copiar todo el contenido
6. Pegar en el editor SQL
7. Hacer clic en "Continuar" o "Go"
```

**Opción B: Desde línea de comandos**
```bash
# Windows (CMD)
cd C:\laragon\www\ARCO
mysql -u root -p arco_bdd < base-datos/sistema_permisos_completo.sql

# O si tienes contraseña
mysql -u root -pTU_CONTRASEÑA arco_bdd < base-datos/sistema_permisos_completo.sql
```

### Paso 3: Verificar Instalación

**Opción A: Usar el botón Debug**
```
1. Ir a: http://localhost/ARCO/vistas/gestion_permisos.php
2. Hacer clic en el botón rojo "Debug"
3. Ver el mensaje que aparece
4. Debe mostrar:
   - Módulos: 10 registros
   - Permisos: 8 registros
   - Rol-Permisos: ~150 registros
   - Permisos Admin: ~80
```

**Opción B: Consulta SQL directa**
```sql
-- En phpMyAdmin o MySQL Workbench
USE arco_bdd;

-- Verificar módulos
SELECT COUNT(*) as total FROM modulos;
-- Debe retornar: 10

-- Verificar permisos
SELECT COUNT(*) as total FROM permisos;
-- Debe retornar: 8

-- Verificar rol_permisos
SELECT COUNT(*) as total FROM rol_permisos;
-- Debe retornar: ~150

-- Verificar permisos de administrador
SELECT COUNT(*) as total FROM rol_permisos WHERE rol = 'administrador';
-- Debe retornar: ~80
```

### Paso 4: Recargar la Página

```
1. Ir a: http://localhost/ARCO/vistas/gestion_permisos.php
2. Presionar F5 o Ctrl+R para recargar
3. Abrir consola del navegador (F12)
4. Ver pestaña "Console"
5. Buscar mensajes de error
```

## 🔍 Verificación de Errores

### Error 1: "Tabla no existe"
**Mensaje:** `Table 'arco_bdd.modulos' doesn't exist`

**Solución:**
```
Ejecutar el script SQL completo:
base-datos/sistema_permisos_completo.sql
```

### Error 2: "No hay permisos asignados"
**Mensaje:** `No hay permisos asignados a este rol`

**Solución:**
```sql
-- Verificar si hay datos en rol_permisos
SELECT * FROM rol_permisos WHERE rol = 'administrador' LIMIT 5;

-- Si está vacío, ejecutar el script SQL completo
```

### Error 3: "Error de conexión"
**Mensaje:** `Error de conexión al cargar permisos`

**Solución:**
```
1. Verificar que el servidor esté corriendo (Apache + MySQL)
2. Verificar que exista: servicios/obtener_permisos_rol.php
3. Verificar que exista: servicios/verificar_permisos.php
4. Verificar que exista: servicios/conexion.php
```

### Error 4: "Response no es JSON válido"
**Mensaje:** `La respuesta no es JSON válido`

**Solución:**
```
1. Abrir directamente en el navegador:
   http://localhost/ARCO/servicios/obtener_permisos_rol.php?rol=administrador

2. Ver qué mensaje aparece
3. Si hay un error de PHP, corregirlo
4. Si dice "Rol no especificado", la URL está mal
```

## 📊 Resultado Esperado

Después de ejecutar el script SQL, deberías ver:

### Estadísticas
```
Módulos Accesibles: 10
Permisos Totales: 80
Permisos Activos: 80
```

### Matriz de Permisos
```
Dashboard    ✓ Ver
Productos    ✓ Ver  ✓ Crear  ✓ Editar  ✓ Eliminar  ✓ Exportar  ✓ Importar
Categorías   ✓ Ver  ✓ Crear  ✓ Editar  ✓ Eliminar
Movimientos  ✓ Ver  ✓ Crear  ✓ Editar  ✓ Aprobar  ✓ Exportar
Usuarios     ✓ Ver  ✓ Crear  ✓ Editar  ✓ Eliminar  ✓ Auditar
Reportes     ✓ Ver  ✓ Crear  ✓ Exportar
Configuración ✓ Ver  ✓ Editar
Órdenes Compra ✓ Ver  ✓ Crear  ✓ Editar  ✓ Aprobar  ✓ Exportar
Devoluciones  ✓ Ver  ✓ Crear  ✓ Editar  ✓ Aprobar
Recepción    ✓ Ver  ✓ Crear  ✓ Editar
```

### Tabla Detallada
Debe mostrar 10 módulos con sus respectivos permisos en badges verdes.

## 🛠️ Herramientas de Debug

### 1. Botón Debug (Recomendado)
```
1. Ir a gestion_permisos.php
2. Hacer clic en botón rojo "Debug"
3. Ver mensaje con estadísticas de BD
```

### 2. Script de Verificación
```
Abrir en navegador:
http://localhost/ARCO/servicios/verificar_permisos_db.php

Ver JSON con estado de las tablas
```

### 3. Consola del Navegador
```
1. Presionar F12
2. Ir a pestaña "Console"
3. Recargar la página
4. Ver mensajes de log:
   - "Cargando permisos para rol: administrador"
   - "Response status: 200"
   - "Datos parseados: {...}"
```

### 4. Consola SQL
```sql
-- Ver todos los módulos
SELECT * FROM modulos ORDER BY orden;

-- Ver todos los permisos
SELECT * FROM permisos;

-- Ver permisos de administrador
SELECT 
    m.nombre AS modulo,
    p.codigo AS permiso,
    rp.activo
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador'
ORDER BY m.orden, p.nombre;
```

## 📝 Checklist de Verificación

- [ ] Servidor Apache corriendo
- [ ] Servidor MySQL corriendo
- [ ] Base de datos `arco_bdd` existe
- [ ] Tabla `modulos` existe y tiene 10 registros
- [ ] Tabla `permisos` existe y tiene 8 registros
- [ ] Tabla `rol_permisos` existe y tiene ~150 registros
- [ ] Archivo `servicios/obtener_permisos_rol.php` existe
- [ ] Archivo `servicios/verificar_permisos.php` existe
- [ ] Archivo `servicios/conexion.php` existe
- [ ] Sesión iniciada como administrador
- [ ] Consola del navegador sin errores

## 🎯 Solución Rápida (1 minuto)

```bash
# 1. Abrir terminal en la carpeta del proyecto
cd C:\laragon\www\ARCO

# 2. Ejecutar script SQL
mysql -u root -p arco_bdd < base-datos/sistema_permisos_completo.sql

# 3. Recargar página en el navegador
# Presionar F5 en: http://localhost/ARCO/vistas/gestion_permisos.php
```

## 📞 Si Aún No Funciona

1. **Hacer clic en botón "Debug"** y copiar el mensaje
2. **Abrir consola del navegador (F12)** y copiar los errores
3. **Ejecutar esta consulta SQL:**
   ```sql
   SELECT 
       (SELECT COUNT(*) FROM modulos) as modulos,
       (SELECT COUNT(*) FROM permisos) as permisos,
       (SELECT COUNT(*) FROM rol_permisos) as rol_permisos,
       (SELECT COUNT(*) FROM rol_permisos WHERE rol='administrador') as admin_permisos;
   ```
4. **Compartir los resultados** para diagnóstico

---

**Archivo SQL:** `base-datos/sistema_permisos_completo.sql`  
**Script Debug:** `servicios/verificar_permisos_db.php`  
**Vista:** `vistas/gestion_permisos.php`
