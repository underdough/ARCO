# Migración del Menú - Gestión de Usuarios

## Descripción

Este documento explica cómo actualizar los enlaces del menú en todas las vistas para que apunten a la nueva gestión de usuarios mejorada.

## Opción 1: Usar Ambos Sistemas (Recomendado)

Mantener ambos sistemas disponibles durante un período de transición:

### Sistema Antiguo
- URL: `vistas/Usuario.php`
- Nombre: "Usuarios (Clásico)"

### Sistema Nuevo
- URL: `vistas/gestion_usuarios.php`
- Nombre: "Gestión de Usuarios"

### Actualización del Menú

En cada archivo de vista (`dashboard.php`, `productos.php`, `categorias.php`, etc.), actualizar el menú:

```php
<!-- Menú antiguo -->
<a href="Usuario.php" class="menu-item">
    <i class="fas fa-users"></i>
    <span class="menu-text">Usuarios</span>
</a>

<!-- Cambiar por -->
<a href="gestion_usuarios.php" class="menu-item">
    <i class="fas fa-users-cog"></i>
    <span class="menu-text">Gestión de Usuarios</span>
</a>
```

## Opción 2: Reemplazar Completamente

Si desea reemplazar completamente el sistema antiguo:

### Paso 1: Renombrar archivo antiguo (backup)
```bash
# En la carpeta vistas/
mv Usuario.php Usuario_old.php
```

### Paso 2: Crear redirección
Crear archivo `vistas/Usuario.php` con redirección:

```php
<?php
// Redirección automática al nuevo sistema
header("Location: gestion_usuarios.php");
exit;
?>
```

### Paso 3: Actualizar todos los enlaces

Buscar y reemplazar en todos los archivos:
- `Usuario.php` → `gestion_usuarios.php`

## Archivos a Actualizar

Lista de archivos que contienen enlaces al menú de usuarios:

1. ✅ `vistas/gestion_usuarios.php` - Ya actualizado
2. ⏳ `vistas/dashboard.php`
3. ⏳ `vistas/productos.php`
4. ⏳ `vistas/categorias.php`
5. ⏳ `vistas/movimientos.php`
6. ⏳ `vistas/reportes.php`
7. ⏳ `vistas/configuracion.php`

## Script de Actualización Automática

### Para Windows (PowerShell)

```powershell
# Guardar como: actualizar_menu.ps1

$archivos = @(
    "vistas/dashboard.php",
    "vistas/productos.php",
    "vistas/categorias.php",
    "vistas/movimientos.php",
    "vistas/reportes.php",
    "vistas/configuracion.php"
)

foreach ($archivo in $archivos) {
    if (Test-Path $archivo) {
        $contenido = Get-Content $archivo -Raw
        $contenido = $contenido -replace 'href="Usuario\.php"', 'href="gestion_usuarios.php"'
        $contenido = $contenido -replace 'fa-users"', 'fa-users-cog"'
        Set-Content $archivo $contenido
        Write-Host "✅ Actualizado: $archivo"
    } else {
        Write-Host "❌ No encontrado: $archivo"
    }
}

Write-Host "`n✅ Actualización completada"
```

### Para Linux/Mac (Bash)

```bash
#!/bin/bash
# Guardar como: actualizar_menu.sh

archivos=(
    "vistas/dashboard.php"
    "vistas/productos.php"
    "vistas/categorias.php"
    "vistas/movimientos.php"
    "vistas/reportes.php"
    "vistas/configuracion.php"
)

for archivo in "${archivos[@]}"; do
    if [ -f "$archivo" ]; then
        sed -i 's/href="Usuario\.php"/href="gestion_usuarios.php"/g' "$archivo"
        sed -i 's/fa-users"/fa-users-cog"/g' "$archivo"
        echo "✅ Actualizado: $archivo"
    else
        echo "❌ No encontrado: $archivo"
    fi
done

echo ""
echo "✅ Actualización completada"
```

## Actualización Manual

Si prefiere actualizar manualmente, siga estos pasos para cada archivo:

### 1. Abrir archivo de vista
Por ejemplo: `vistas/dashboard.php`

### 2. Buscar el enlace de usuarios
```php
<a href="Usuario.php" class="menu-item">
    <i class="fas fa-users"></i>
    <span class="menu-text">Usuarios</span>
</a>
```

### 3. Reemplazar con el nuevo enlace
```php
<a href="gestion_usuarios.php" class="menu-item">
    <i class="fas fa-users-cog"></i>
    <span class="menu-text">Gestión de Usuarios</span>
</a>
```

### 4. Guardar y verificar

## Verificación

Después de actualizar, verificar:

1. ✅ Todos los enlaces del menú funcionan
2. ✅ El enlace "Usuarios" apunta a `gestion_usuarios.php`
3. ✅ El icono cambió a `fa-users-cog`
4. ✅ La página carga correctamente
5. ✅ No hay errores en consola (F12)

## Rollback (Volver Atrás)

Si necesita volver al sistema antiguo:

### Opción 1: Si hizo backup
```bash
# Restaurar archivo original
mv Usuario_old.php Usuario.php
```

### Opción 2: Revertir cambios en menú
Cambiar todos los enlaces de vuelta:
- `gestion_usuarios.php` → `Usuario.php`
- `fa-users-cog` → `fa-users`

## Compatibilidad

### Sistema Antiguo (Usuario.php)
- ✅ Sigue funcionando
- ✅ No se ve afectado
- ✅ Puede usarse en paralelo

### Sistema Nuevo (gestion_usuarios.php)
- ✅ Funciona independientemente
- ✅ No afecta al antiguo
- ✅ Puede coexistir

## Recomendaciones

1. **Período de Transición**: Mantener ambos sistemas por 1-2 semanas
2. **Capacitación**: Entrenar a administradores en el nuevo sistema
3. **Feedback**: Recoger opiniones de usuarios
4. **Migración Gradual**: Cambiar enlaces progresivamente
5. **Backup**: Siempre hacer backup antes de cambios

## Soporte

Para problemas durante la migración:
- Revisar logs de PHP
- Verificar permisos de archivos
- Consultar `documentacion/SOLUCION_PROBLEMAS.md`

---

**Última actualización:** Diciembre 2025  
**Versión:** 2.0
