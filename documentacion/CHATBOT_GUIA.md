# 🤖 Guía del Chatbot Local - Sistema ARCO

Documentación completa del asistente virtual integrado en el sistema.

## 📋 Descripción General

El Chatbot Local es un asistente virtual que proporciona ayuda inmediata sobre el sistema ARCO. Responde preguntas frecuentes, guía en procedimientos y busca información en la base de datos.

### Características

- ✅ Disponible en todos los módulos
- ✅ Responde preguntas sobre funcionalidades
- ✅ Búsqueda en base de datos
- ✅ Interfaz flotante y no invasiva
- ✅ Historial de conversación
- ✅ Respuestas contextuales
- ✅ Funciona sin conexión a internet
- ✅ Responsive en dispositivos móviles

---

## 🚀 Cómo Usar el Chatbot

### Abrir el Chatbot

1. Busca el botón flotante en la esquina inferior derecha
2. Haz clic en el icono de comentarios (💬)
3. Se abrirá la ventana de chat

### Hacer una Pregunta

1. Escribe tu pregunta en el campo de entrada
2. Presiona Enter o haz clic en el botón de enviar
3. El chatbot responderá automáticamente

### Cerrar el Chatbot

- Haz clic en la X en la esquina superior derecha
- O haz clic nuevamente en el botón flotante

---

## 💬 Preguntas Que Puedes Hacer

### Saludos
- "Hola"
- "Buenos días"
- "¿Cómo estás?"

### Sobre Módulos
- "¿Qué es el Dashboard?"
- "¿Cómo uso Categorías?"
- "¿Cómo registro un movimiento?"
- "¿Qué son las Órdenes de Compra?"
- "¿Cómo veo Estadísticas?"

### Sobre Procedimientos
- "¿Cómo crear un producto?"
- "¿Cómo filtrar categorías?"
- "¿Cómo imprimir un comprobante?"
- "¿Cómo buscar un movimiento?"

### Sobre Seguridad
- "¿Qué es 2FA?"
- "¿Cómo recupero mi contraseña?"
- "¿Qué son los permisos?"

### Sobre Información General
- "¿Cuáles son los roles?"
- "¿Cómo funciona el stock?"
- "¿Qué es una anomalía?"

---

## 🎯 Ejemplos de Conversación

### Ejemplo 1: Pregunta sobre Movimientos
```
Usuario: ¿Cómo registro un movimiento?
Chatbot: Los Movimientos registran todas las operaciones de entrada 
y salida de productos. Puedes registrar movimientos, filtrar por 
fecha/tipo/producto, ver historial e imprimir comprobantes. 
Cada movimiento actualiza automáticamente el stock.
```

### Ejemplo 2: Pregunta sobre Filtros
```
Usuario: ¿Cómo uso los filtros?
Chatbot: Los filtros te permiten organizar datos. Haz clic en 
"Filtrar" para ver opciones. Puedes filtrar por estado, fecha, 
categoría y ordenar por nombre, cantidad, fecha, etc.
```

### Ejemplo 3: Búsqueda en Base de Datos
```
Usuario: ¿Existe la categoría Electrónicos?
Chatbot: Encontré la categoría: Electrónicos. ¿Necesitas más 
información sobre esta categoría?
```

---

## 🔧 Archivos del Chatbot

### Backend
- `servicios/chatbot_api.php` - API que procesa mensajes y retorna respuestas

### Frontend
- `componentes/chatbot.css` - Estilos del widget
- `componentes/chatbot.js` - Lógica del chatbot

### Integración
- `servicios/incluir_chatbot.php` - Script para incluir en vistas

---

## 📝 Cómo Integrar el Chatbot en una Vista

### Opción 1: Incluir el archivo (Recomendado)

Antes de `</body>` en tu vista PHP:

```php
<?php
    // Incluir chatbot
    require_once '../servicios/incluir_chatbot.php';
?>
```

### Opción 2: Incluir manualmente

Antes de `</body>`:

```html
<link rel="stylesheet" href="../componentes/chatbot.css">
<script src="../componentes/chatbot.js"></script>
```

---

## 🎨 Personalización

### Cambiar Colores

Edita `componentes/chatbot.css`:

```css
/* Cambiar color primario */
background: linear-gradient(135deg, #TU_COLOR_1 0%, #TU_COLOR_2 100%);
```

### Cambiar Mensaje de Bienvenida

Edita `componentes/chatbot.js`, método `loadWelcomeMessage()`:

```javascript
loadWelcomeMessage() {
    setTimeout(() => {
        this.addMessage('bot', 'Tu mensaje personalizado aquí');
    }, 500);
}
```

### Agregar Nuevas Respuestas

Edita `servicios/chatbot_api.php`, función `procesarMensaje()`:

```php
['palabras' => ['palabra1', 'palabra2'], 'respuesta' => 'Tu respuesta aquí'],
```

---

## 🔍 Cómo Funciona

### Flujo de Procesamiento

```
1. Usuario escribe mensaje
   ↓
2. JavaScript envía a chatbot_api.php
   ↓
3. PHP procesa el mensaje
   ↓
4. Busca coincidencias en palabras clave
   ↓
5. Si no encuentra, busca en base de datos
   ↓
6. Retorna respuesta en JSON
   ↓
7. JavaScript muestra respuesta en chat
```

### Búsqueda de Palabras Clave

El chatbot busca palabras clave en el mensaje del usuario:

```php
// Ejemplo
['palabras' => ['movimiento', 'movimientos', 'entrada', 'salida'],
 'respuesta' => 'Los Movimientos registran...']
```

Si el usuario escribe "¿Cómo registro una entrada?", el chatbot detecta la palabra "entrada" y retorna la respuesta.

### Búsqueda en Base de Datos

Si no encuentra coincidencia en palabras clave, busca en la BD:

```php
// Busca en tabla categorias
SELECT nombre_cat FROM categorias WHERE nombre_cat LIKE ?
```

---

## 🛡️ Seguridad

### Autenticación
- Solo usuarios autenticados pueden usar el chatbot
- Valida sesión en `chatbot_api.php`

### Validación
- Escapa HTML para prevenir XSS
- Valida entrada en servidor
- Retorna JSON seguro

### Privacidad
- No almacena mensajes en BD
- Historial solo en sesión del navegador
- Se borra al cerrar sesión

---

## 📱 Responsive

El chatbot se adapta a diferentes tamaños de pantalla:

- **Desktop**: 380px × 500px
- **Tablet**: Ajusta automáticamente
- **Mobile**: 100vw - 40px, altura 400px

---

## ⚙️ Configuración Avanzada

### Cambiar Posición

En `componentes/chatbot.css`:

```css
.chatbot-widget {
    bottom: 20px;  /* Distancia desde abajo */
    right: 20px;   /* Distancia desde derecha */
}
```

### Cambiar Tamaño

En `componentes/chatbot.css`:

```css
.chatbot-container {
    width: 380px;   /* Ancho */
    height: 500px;  /* Alto */
}
```

### Cambiar Animación

En `componentes/chatbot.js`, método `open()`:

```javascript
container.classList.add('active');  // Agrega clase con animación
```

---

## 🐛 Solución de Problemas

### El chatbot no aparece
- Verifica que estés autenticado
- Revisa la consola del navegador (F12)
- Asegúrate de incluir los archivos CSS y JS

### El chatbot no responde
- Verifica que `chatbot_api.php` sea accesible
- Revisa los logs del servidor
- Comprueba la conexión a internet

### Las respuestas no son correctas
- Verifica las palabras clave en `chatbot_api.php`
- Prueba con palabras diferentes
- Revisa la base de datos

### El chatbot se ve mal
- Limpia caché del navegador (Ctrl+Shift+Del)
- Verifica que `chatbot.css` se cargue correctamente
- Comprueba compatibilidad del navegador

---

## 📊 Estadísticas

El chatbot puede rastrear:
- Preguntas más frecuentes
- Temas de mayor interés
- Problemas comunes

Para implementar estadísticas, modifica `chatbot_api.php` para guardar en BD.

---

## 🚀 Mejoras Futuras

Posibles mejoras:

1. **Integración con IA**: Usar OpenAI para respuestas más inteligentes
2. **Estadísticas**: Guardar preguntas para análisis
3. **Sugerencias**: Mostrar preguntas sugeridas
4. **Múltiples idiomas**: Soporte para otros idiomas
5. **Transferencia a humano**: Opción de hablar con soporte
6. **Integración con módulos**: Acciones directas desde el chat

---

## 📞 Soporte

Para problemas o sugerencias sobre el chatbot:

1. Revisa esta documentación
2. Consulta [Solución de Problemas](SOLUCION_PROBLEMAS.md)
3. Contacta al equipo de desarrollo

---

**Chatbot ARCO v1.0** - Asistente Virtual del Sistema  
*Ayuda inmediata disponible 24/7*
