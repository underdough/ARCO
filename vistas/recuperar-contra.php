<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - ARCO</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/login-pure.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .recovery-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .recovery-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .recovery-header i {
            font-size: 48px;
            color: var(--color-primario);
            margin-bottom: 1rem;
        }
        
        .recovery-header h2 {
            color: var(--texto);
            margin-bottom: 0.5rem;
        }
        
        .recovery-header p {
            color: var(--texto-secundario);
            font-size: 0.95rem;
        }
        
        .success-message {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }
        
        .success-message i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="cont-auten">
        <div class="img-auten">
            <div>
                <h2>ARCO</h2>
                <p>Sistema de Gestión de Inventarios</p>
                <img src="../componentes/img/logo2.png" alt="Logo ARCO" style="max-width: 150px;">
            </div>
        </div>
        
        <div class="form-auten">
            <div class="recovery-container">
                <div class="recovery-header">
                    <i class="fas fa-key"></i>
                    <h2>Recuperar Contraseña</h2>
                    <p>Ingresa tu correo electrónico y te enviaremos instrucciones para restablecer tu contraseña</p>
                </div>
                
                <div id="alertContainer"></div>
                <div id="successMessage" class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Se ha enviado un correo con instrucciones para restablecer tu contraseña</span>
                </div>
                
                <form id="formRecuperar" action="../servicios/recuperar_contrasena.php" method="POST">
                    <div class="inputs-login">
                        <label for="correo" class="txt-form">Correo Electrónico</label>
                        <input type="email" class="input-form" id="correo" name="correo" 
                               placeholder="tu@correo.com" required>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Enviar Instrucciones
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="../login.html" class="txt-olvidado">
                            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alerta alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
            `;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }
        
        document.getElementById('formRecuperar').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            fetch('../servicios/recuperar_contrasena.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta del servidor:', data);
                
                if (data.success) {
                    const successMsg = document.getElementById('successMessage');
                    successMsg.style.display = 'block';
                    document.getElementById('formRecuperar').style.display = 'none';
                    
                    // Si hay link de desarrollo, mostrarlo
                    if (data.debug && data.debug.link) {
                        const debugInfo = document.createElement('div');
                        debugInfo.style.cssText = 'background: #fef3c7; border: 1px solid #fbbf24; padding: 1rem; border-radius: 8px; margin-top: 1rem;';
                        debugInfo.innerHTML = `
                            <p style="margin: 0 0 0.5rem 0; font-weight: 600; color: #92400e;">
                                <i class="fas fa-info-circle"></i> Modo Desarrollo
                            </p>
                            <p style="margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #78350f;">
                                El email puede no enviarse en desarrollo. Usa este enlace:
                            </p>
                            <a href="${data.debug.link}" style="display: block; padding: 0.5rem; background: white; border-radius: 4px; color: #2563eb; text-decoration: none; word-break: break-all; font-size: 0.85rem;">
                                ${data.debug.link}
                            </a>
                        `;
                        document.querySelector('.recovery-container').appendChild(debugInfo);
                    }
                } else {
                    showAlert(data.message || 'Error al procesar la solicitud', 'danger');
                    
                    // Mostrar detalles del error en consola
                    if (data.debug) {
                        console.error('Detalles del error:', data.debug);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error de conexión. Verifica la consola del navegador para más detalles.', 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Instrucciones';
            });
        });
        
        // Verificar mensajes en URL
        const urlParams = new URLSearchParams(window.location.search);
        const successMsg = urlParams.get('success');
        const errorMsg = urlParams.get('error');
        
        if (successMsg) {
            document.getElementById('successMessage').style.display = 'block';
            document.getElementById('formRecuperar').style.display = 'none';
        }
        
        if (errorMsg) {
            showAlert(decodeURIComponent(errorMsg), 'danger');
        }
    </script>
</body>
</html>