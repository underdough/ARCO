/**
 * Chatbot Local - Sistema ARCO
 * Widget flotante de chat para ayuda del sistema
 */

class ChatbotARCO {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.init();
    }

    init() {
        this.createWidget();
        this.attachEventListeners();
        this.loadWelcomeMessage();
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.className = 'chatbot-widget';
        widget.id = 'chatbot-widget';
        widget.innerHTML = `
            <button class="chatbot-button" id="chatbot-toggle" title="Abrir asistente">
                <i class="fas fa-comments"></i>
            </button>
            
            <div class="chatbot-container" id="chatbot-container">
                <div class="chatbot-header">
                    <h3>Asistente ARCO</h3>
                    <button class="chatbot-close" id="chatbot-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="chatbot-messages" id="chatbot-messages">
                    <div class="chatbot-welcome">
                        <i class="fas fa-robot" style="font-size: 24px; color: #395886; margin-bottom: 10px; display: block;"></i>
                        <p>¡Hola! Soy tu asistente. Pregúntame sobre el sistema.</p>
                    </div>
                </div>
                
                <div class="chatbot-input-area">
                    <input 
                        type="text" 
                        class="chatbot-input" 
                        id="chatbot-input" 
                        placeholder="Escribe tu pregunta..."
                        autocomplete="off"
                    >
                    <button class="chatbot-send" id="chatbot-send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(widget);
    }

    attachEventListeners() {
        const toggleBtn = document.getElementById('chatbot-toggle');
        const closeBtn = document.getElementById('chatbot-close');
        const sendBtn = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');

        toggleBtn.addEventListener('click', () => this.toggle());
        closeBtn.addEventListener('click', () => this.close());
        sendBtn.addEventListener('click', () => this.sendMessage());
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        input.addEventListener('focus', () => {
            this.ensureOpen();
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isOpen = true;
        const container = document.getElementById('chatbot-container');
        const button = document.getElementById('chatbot-toggle');
        
        container.classList.add('active');
        button.classList.add('active');
        
        document.getElementById('chatbot-input').focus();
    }

    close() {
        this.isOpen = false;
        const container = document.getElementById('chatbot-container');
        const button = document.getElementById('chatbot-toggle');
        
        container.classList.remove('active');
        button.classList.remove('active');
    }

    ensureOpen() {
        if (!this.isOpen) {
            this.open();
        }
    }

    loadWelcomeMessage() {
        // Mensaje de bienvenida personalizado
        setTimeout(() => {
            this.addMessage('bot', '¿Necesitas ayuda? Puedo responder preguntas sobre módulos, procedimientos y funcionalidades del sistema. ¡Pregúntame lo que necesites!');
        }, 500);
    }

    sendMessage() {
        const input = document.getElementById('chatbot-input');
        const mensaje = input.value.trim();

        if (!mensaje) return;

        // Agregar mensaje del usuario
        this.addMessage('user', mensaje);
        input.value = '';

        // Mostrar indicador de escritura
        this.showTypingIndicator();

        // Enviar al servidor
        this.fetchResponse(mensaje);
    }

    addMessage(sender, text) {
        const messagesContainer = document.getElementById('chatbot-messages');
        
        // Limpiar mensaje de bienvenida si es el primer mensaje
        if (this.messages.length === 0 && sender === 'user') {
            const welcome = messagesContainer.querySelector('.chatbot-welcome');
            if (welcome) {
                welcome.remove();
            }
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        messageDiv.innerHTML = `<div class="message-content">${this.escapeHtml(text)}</div>`;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        this.messages.push({ sender, text, timestamp: new Date() });
    }

    showTypingIndicator() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-content">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;

        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    removeTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    fetchResponse(mensaje) {
        const formData = new FormData();
        formData.append('mensaje', mensaje);

        fetch('../servicios/chatbot_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            this.removeTypingIndicator();
            
            if (data.success) {
                this.addMessage('bot', data.respuesta);
            } else {
                this.addMessage('bot', 'Disculpa, ocurrió un error. Intenta de nuevo.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.removeTypingIndicator();
            this.addMessage('bot', 'No pude conectar con el servidor. Intenta de nuevo.');
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Inicializar chatbot cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.chatbot = new ChatbotARCO();
});
