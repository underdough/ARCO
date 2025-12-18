# 🤖 Chatbot Local - Archivos de Componentes

## Archivos Incluidos

### 1. chatbot.css
Estilos del widget flotante del chatbot.

**Características:**
- Widget flotante en esquina inferior derecha
- Animaciones suaves
- Responsive para móviles
- Colores del sistema ARCO
- Scroll personalizado

### 2. chatbot.js
Lógica del chatbot en el navegador.

**Funcionalidades:**
- Gestión de interfaz
- Envío de mensajes
- Historial de conversación
- Indicador de escritura
- Manejo de errores

## Cómo Integrar en una Vista

### Opción 1: Incluir antes de </body>

```html
<!-- Chatbot Widget -->
<link rel="stylesheet" href="../componentes/chatbot.css">
<script src="../componentes/chatbot.js"></script>
```

### Opción 2: Usar el archivo incluir_chatbot.php

```php
<?php
    require_once '../servicios/incluir_chatbot.php';
?>
```

## Personalización

### Cambiar Colores Primarios

En `chatbot.css`, busca:
```css
background: linear-gradient(135deg, #395886 0%, #638ECB 100%);
```

Reemplaza con tus colores:
```css
background: linear-gradient(135deg, #TU_COLOR_1 0%, #TU_COLOR_2 100%);
```

### Cambiar Posición

En `chatbot.css`:
```css
.chatbot-widget {
    bottom: 20px;  /* Cambiar distancia desde abajo */
    right: 20px;   /* Cambiar distancia desde derecha */
}
```

### Cambiar Tamaño

En `chatbot.css`:
```css
.chatbot-container {
    width: 380px;   /* Ancho del chat */
    height: 500px;  /* Alto del chat */
}
```

## Estructura del Widget

```
┌─────────────────────────────┐
│ Asistente ARCO         [×]  │  ← Header
├─────────────────────────────┤
│                             │
│  Hola, ¿cómo estás?        │  ← Mensaje del bot
│                             │
│              Bien, gracias  │  ← Mensaje del usuario
│                             │
├─────────────────────────────┤
│ [Escribe tu pregunta...] [→]│  ← Input
└─────────────────────────────┘
```

## Flujo de Funcionamiento

```
1. Usuario escribe mensaje
   ↓
2. JavaScript captura el mensaje
   ↓
3. Envía a servicios/chatbot_api.php
   ↓
4. PHP procesa y retorna respuesta
   ↓
5. JavaScript muestra respuesta
   ↓
6. Se agrega al historial
```

## Clases CSS Disponibles

- `.chatbot-widget` - Contenedor principal
- `.chatbot-button` - Botón flotante
- `.chatbot-container` - Ventana de chat
- `.chatbot-header` - Encabezado
- `.chatbot-messages` - Área de mensajes
- `.message` - Mensaje individual
- `.message.user` - Mensaje del usuario
- `.message.bot` - Mensaje del bot
- `.chatbot-input-area` - Área de entrada
- `.chatbot-input` - Campo de texto
- `.chatbot-send` - Botón de envío

## Eventos JavaScript

### Abrir chatbot
```javascript
window.chatbot.open();
```

### Cerrar chatbot
```javascript
window.chatbot.close();
```

### Agregar mensaje
```javascript
window.chatbot.addMessage('bot', 'Tu mensaje aquí');
```

### Enviar mensaje
```javascript
window.chatbot.sendMessage();
```

## Solución de Problemas

### El chatbot no aparece
- Verifica que los archivos CSS y JS se carguen correctamente
- Abre la consola (F12) y busca errores
- Verifica que estés autenticado

### El chatbot no responde
- Verifica que `chatbot_api.php` sea accesible
- Revisa la consola del navegador
- Comprueba la conexión a internet

### Estilos no se aplican
- Limpia caché del navegador (Ctrl+Shift+Del)
- Verifica que `chatbot.css` se cargue correctamente
- Comprueba que no haya conflictos de CSS

## Compatibilidad

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Navegadores móviles modernos

## Rendimiento

- Tamaño: ~15KB (CSS + JS)
- Carga: Asincrónica, no bloquea página
- Memoria: Mínima, se limpia al cerrar sesión
- Velocidad: Respuestas instantáneas

## Seguridad

- Valida autenticación en servidor
- Escapa HTML para prevenir XSS
- No almacena datos sensibles
- Historial solo en sesión del navegador

---

**Chatbot ARCO v1.0** - Componentes del Sistema
