<?php
/**
 * Prueba del Chatbot Local
 * Verifica que el chatbot responda correctamente
 */

session_start();

// Simular sesión de usuario para pruebas
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1;
    $_SESSION['nombre'] = 'Test';
    $_SESSION['apellido'] = 'User';
    $_SESSION['rol'] = 'administrador';
}

require_once '../servicios/conexion.php';
$conexion = ConectarDB();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba del Chatbot - ARCO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #395886 0%, #638ECB 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            color: #395886;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .test-section h2 {
            color: #395886;
            margin-bottom: 20px;
            font-size: 18px;
            border-bottom: 2px solid #638ECB;
            padding-bottom: 10px;
        }
        
        .test-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #395886;
        }
        
        .test-item strong {
            color: #395886;
            display: block;
            margin-bottom: 8px;
        }
        
        .test-item p {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .response {
            background: #e8f4f8;
            padding: 10px;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 13px;
            color: #333;
            border-left: 3px solid #638ECB;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #395886 0%, #638ECB 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(57, 88, 134, 0.4);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .loading {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #395886;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .footer {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> Prueba del Chatbot Local</h1>
            <p>Verifica que el chatbot responda correctamente a diferentes preguntas</p>
        </div>
        
        <div class="test-section">
            <h2><i class="fas fa-comments"></i> Pruebas de Respuestas</h2>
            
            <div id="test-results"></div>
            
            <div class="button-group">
                <button class="btn btn-primary" onclick="runTests()">
                    <i class="fas fa-play"></i> Ejecutar Pruebas
                </button>
                <button class="btn btn-secondary" onclick="clearResults()">
                    <i class="fas fa-trash"></i> Limpiar
                </button>
            </div>
        </div>
        
        <div class="test-section">
            <h2><i class="fas fa-comments"></i> Prueba Manual</h2>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; color: #333; font-weight: 600;">
                    Escribe una pregunta:
                </label>
                <input 
                    type="text" 
                    id="manual-input" 
                    placeholder="Ej: ¿Cómo registro un movimiento?"
                    style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif;"
                >
            </div>
            
            <button class="btn btn-primary" onclick="testManual()">
                <i class="fas fa-send"></i> Enviar Pregunta
            </button>
            
            <div id="manual-result" style="margin-top: 15px;"></div>
        </div>
        
        <div class="footer">
            <p><i class="fas fa-info-circle"></i> Prueba del Chatbot ARCO v1.0 - Diciembre 2025</p>
        </div>
    </div>
    
    <script>
        const testQuestions = [
            { pregunta: 'Hola', categoria: 'Saludos' },
            { pregunta: '¿Qué es el Dashboard?', categoria: 'Módulos' },
            { pregunta: '¿Cómo creo una categoría?', categoria: 'Procedimientos' },
            { pregunta: '¿Cómo registro un movimiento?', categoria: 'Procedimientos' },
            { pregunta: '¿Qué es 2FA?', categoria: 'Seguridad' },
            { pregunta: '¿Cuáles son los roles?', categoria: 'Información' },
            { pregunta: '¿Cómo uso los filtros?', categoria: 'Procedimientos' },
            { pregunta: '¿Qué son las anomalías?', categoria: 'Módulos' },
        ];
        
        async function runTests() {
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p style="color: #666;"><span class="loading"></span>Ejecutando pruebas...</p>';
            
            let html = '';
            let successCount = 0;
            
            for (const test of testQuestions) {
                try {
                    const response = await fetch('../servicios/chatbot_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'mensaje=' + encodeURIComponent(test.pregunta)
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        successCount++;
                        html += `
                            <div class="test-item">
                                <strong><i class="fas fa-question-circle"></i> ${test.pregunta}</strong>
                                <p><strong>Categoría:</strong> ${test.categoria}</p>
                                <div class="response">
                                    <strong>Respuesta:</strong> ${data.respuesta}
                                </div>
                                <span class="status success"><i class="fas fa-check"></i> Exitoso</span>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="test-item">
                                <strong><i class="fas fa-question-circle"></i> ${test.pregunta}</strong>
                                <p><strong>Categoría:</strong> ${test.categoria}</p>
                                <span class="status error"><i class="fas fa-times"></i> Error: ${data.error}</span>
                            </div>
                        `;
                    }
                } catch (error) {
                    html += `
                        <div class="test-item">
                            <strong><i class="fas fa-question-circle"></i> ${test.pregunta}</strong>
                            <p><strong>Categoría:</strong> ${test.categoria}</p>
                            <span class="status error"><i class="fas fa-times"></i> Error de conexión</span>
                        </div>
                    `;
                }
            }
            
            html = `
                <div style="margin-bottom: 20px; padding: 15px; background: #d4edda; border-radius: 8px; border-left: 4px solid #28a745;">
                    <strong style="color: #155724;"><i class="fas fa-check-circle"></i> Resultados: ${successCount}/${testQuestions.length} pruebas exitosas</strong>
                </div>
            ` + html;
            
            resultsDiv.innerHTML = html;
        }
        
        async function testManual() {
            const input = document.getElementById('manual-input');
            const resultDiv = document.getElementById('manual-result');
            const pregunta = input.value.trim();
            
            if (!pregunta) {
                resultDiv.innerHTML = '<p style="color: #e74c3c;"><i class="fas fa-exclamation-circle"></i> Por favor ingresa una pregunta</p>';
                return;
            }
            
            resultDiv.innerHTML = '<p style="color: #666;"><span class="loading"></span>Enviando pregunta...</p>';
            
            try {
                const response = await fetch('../servicios/chatbot_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'mensaje=' + encodeURIComponent(pregunta)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="test-item">
                            <strong><i class="fas fa-user"></i> Tu pregunta:</strong>
                            <p>${pregunta}</p>
                            <strong><i class="fas fa-robot"></i> Respuesta del chatbot:</strong>
                            <div class="response">${data.respuesta}</div>
                            <span class="status success"><i class="fas fa-check"></i> Respuesta exitosa</span>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="test-item">
                            <span class="status error"><i class="fas fa-times"></i> Error: ${data.error}</span>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="test-item">
                        <span class="status error"><i class="fas fa-times"></i> Error de conexión: ${error.message}</span>
                    </div>
                `;
            }
        }
        
        function clearResults() {
            document.getElementById('test-results').innerHTML = '';
            document.getElementById('manual-input').value = '';
            document.getElementById('manual-result').innerHTML = '';
        }
        
        // Permitir Enter en el input manual
        document.getElementById('manual-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                testManual();
            }
        });
    </script>
</body>
</html>
