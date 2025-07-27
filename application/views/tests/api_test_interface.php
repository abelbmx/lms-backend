<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 LMS API Test Suite - Sistema de Pruebas Completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism-tomorrow.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --test-pass: #198754;
            --test-fail: #dc3545;
            --test-skip: #ffc107;
            --test-info: #0dcaf0;
        }

        body {
            background: #f8f9fa;
        }

        .test-pass {
            color: var(--test-pass);
        }

        .test-fail {
            color: var(--test-fail);
        }

        .test-skip {
            color: var(--test-skip);
        }

        .test-info {
            color: var(--test-info);
        }

        .log-container {
            max-height: 600px;
            overflow-y: auto;
            background: #1e1e1e;
            color: #fff;
            font-family: 'Courier New', monospace;
            padding: 15px;
            border-radius: 8px;
        }

        .endpoint-card {
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .endpoint-card:hover {
            transform: translateY(-2px);
        }

        .response-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            max-height: 400px;
            overflow-y: auto;
        }

        .test-category {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin: 10px 0;
        }

        .progress-ring {
            transform: rotate(-90deg);
        }

        .btn-test {
            margin: 2px;
            border-radius: 20px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }

        .test-result-item {
            border-left: 3px solid #dee2e6;
            padding: 8px 12px;
            margin: 5px 0;
            background: white;
            border-radius: 0 8px 8px 0;
            transition: all 0.3s ease;
        }

        .test-result-item:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .test-result-item.pass {
            border-left-color: var(--test-pass);
        }

        .test-result-item.fail {
            border-left-color: var(--test-fail);
        }

        .test-result-item.skip {
            border-left-color: var(--test-skip);
        }

        .test-result-item.info {
            border-left-color: var(--test-info);
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-right: 10px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .category-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .category-stat-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-pass {
            background-color: var(--test-pass);
        }

        .status-fail {
            background-color: var(--test-fail);
        }

        .status-skip {
            background-color: var(--test-skip);
        }

        .status-info {
            background-color: var(--test-info);
        }

        .test-details {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-top: 8px;
            font-size: 0.9em;
        }

        .grade-badge {
            font-size: 1.2em;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 20px;
        }

        .recommendations {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-4">
                    <h1 class="display-4">🧪 LMS API Test Suite</h1>
                    <p class="lead">Sistema Completo de Pruebas Automáticas para API</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Panel de Control Principal -->
            <div class="col-lg-8">
                <!-- Controles Principales -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-rocket"></i> Panel de Control Principal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <button class="btn btn-success btn-lg w-100" onclick="runAllTests()">
                                    <i class="fas fa-play-circle"></i> Ejecutar Todos los Tests
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-info btn-lg w-100" onclick="getTestToken()">
                                    <i class="fas fa-key"></i> Obtener Token de Prueba
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-warning btn-lg w-100" onclick="runStressTest()">
                                    <i class="fas fa-tachometer-alt"></i> Test de Carga
                                </button>
                            </div>
                        </div>

                        <!-- Tests por Categoría -->
                        <div class="test-categories">
                            <h6><i class="fas fa-list"></i> Tests por Categoría:</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('health')">
                                        🏥 Salud API
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('register')">
                                        👤 Registro
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('login')">
                                        🔐 Login
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('profile')">
                                        👨‍💼 Perfil
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('password')">
                                        🔒 Contraseña
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('categories')">
                                        📂 Categorías
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('courses')">
                                        📚 Cursos
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('enrollment')">
                                        📝 Inscripciones
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('lessons')">
                                        📖 Lecciones
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('progress')">
                                        📊 Progreso
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('evaluations')">
                                        🎯 Evaluaciones
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('certificates')">
                                        🏆 Certificados
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('ratings')">
                                        ⭐ Calificaciones
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('forums')">
                                        💬 Foros
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('notifications')">
                                        🔔 Notificaciones
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('dashboard')">
                                        📊 Dashboard
                                    </button>
                                    <button class="btn btn-outline-primary btn-test w-100" onclick="testEndpoint('logout')">
                                        🚪 Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resultados de Tests -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-chart-line"></i> Resultados de Tests</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportResults()">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearResults()">
                                <i class="fas fa-trash"></i> Limpiar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Estadísticas Generales -->
                        <div id="testStats" style="display: none;">
                            <div class="stats-card p-3 mb-3">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h3 id="totalTests">0</h3>
                                        <small>Tests Totales</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 id="passedTests" class="text-light">0</h3>
                                        <small>Exitosos</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 id="failedTests" class="text-light">0</h3>
                                        <small>Fallidos</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h3 id="successRate">0%</h3>
                                        <small>Tasa de Éxito</small>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <small><i class="fas fa-clock"></i> Tiempo: <span id="executionTime">-</span></small>
                                    </div>
                                    <div class="col-md-6">
                                        <span id="gradeDisplay" class="grade-badge">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas por Categoría -->
                            <div id="categoryStats" class="category-stats"></div>

                            <!-- Recomendaciones -->
                            <div id="recommendations" class="recommendations" style="display: none;">
                                <h6><i class="fas fa-lightbulb"></i> Recomendaciones:</h6>
                                <ul id="recommendationsList"></ul>
                            </div>
                        </div>

                        <!-- Estado de Ejecución -->
                        <div id="testStatus" class="text-center py-4">
                            <p class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Selecciona una opción de prueba para comenzar
                            </p>
                        </div>

                        <!-- Resultados Detallados -->
                        <div id="testResults"></div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4">
                <!-- Log en Tiempo Real -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6><i class="fas fa-terminal"></i> Log en Tiempo Real</h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="realTimeLog" class="log-container">
                            <div class="text-info">
                                === LMS API Test Suite ===<br>
                                Sistema iniciado y listo para ejecutar pruebas.<br>
                                Selecciona una opción para comenzar...<br>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-cog"></i> Configuración</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">URL Base API:</label>
                            <input type="text" class="form-control" id="apiBaseUrl" value="" placeholder="https://tu-api.com/api/">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Token de Prueba:</label>
                            <input type="text" class="form-control" id="testToken" placeholder="Token opcional para tests autenticados">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Iteraciones (Stress Test):</label>
                            <input type="number" class="form-control" id="stressIterations" value="10" min="1" max="100">
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100" onclick="saveConfig()">
                            <i class="fas fa-save"></i> Guardar Configuración
                        </button>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-info"></i> Información del Sistema</h6>
                    </div>
                    <div class="card-body">
                        <div id="systemInfo">
                            <small class="text-muted">
                                <div><strong>Version:</strong> LMS API Test Suite v2.0</div>
                                <div><strong>Fecha:</strong> <span id="currentDate"></span></div>
                                <div><strong>Estado:</strong> <span class="text-success">✅ Conectado</span></div>
                                <div><strong>Tests Disponibles:</strong> 60+</div>
                                <div><strong>Categorías:</strong> 12</div>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooltips para mejor UX -->
    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1050;">
        <div id="progressToast" class="toast" role="alert">
            <div class="toast-header">
                <i class="fas fa-cog fa-spin me-2"></i>
                <strong class="me-auto">Ejecutando Tests</strong>
            </div>
            <div class="toast-body">
                <div class="progress">
                    <div id="testProgress" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts de librerías -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-json.min.js"></script>

    <!-- Bloque principal de JavaScript: Definición de todas las funciones -->
    <script>
        // Variables globales
        let currentResults = null;
        let isTestRunning = false;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            loadConfig();
            updateCurrentDate();
            logMessage('Sistema inicializado correctamente.', 'info');
        });

        // Función principal para ejecutar todos los tests
        async function runAllTests() {
            if (isTestRunning) {
                alert('Ya hay tests ejecutándose. Espera a que terminen.');
                return;
            }

            isTestRunning = true;
            clearResults();
            showLoading('Ejecutando suite completa de tests...');
            logMessage('🚀 Iniciando ejecución completa de tests...', 'info');

            try {
                const response = await fetch('api/run_all_tests', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                displayTestResults(data);
                logMessage('✅ Suite de tests completada exitosamente.', 'success');
            } catch (error) {
                logMessage('❌ Error ejecutando tests: ' + error.message, 'error');
                document.getElementById('testStatus').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error ejecutando tests: ${error.message}
                </div>
            `;
            } finally {
                isTestRunning = false;
            }
        }

        // Función para ejecutar test de endpoint específico
        async function testEndpoint(endpoint) {
            if (isTestRunning) {
                alert('Ya hay tests ejecutándose. Espera a que terminen.');
                return;
            }

            isTestRunning = true;
            clearResults();
            showLoading(`Ejecutando tests de ${endpoint}...`);
            logMessage(`🔍 Ejecutando tests de categoría: ${endpoint}`, 'info');

            try {
                const response = await fetch(`api/test_single_endpoint/${endpoint}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                displayTestResults(data);
                logMessage(`✅ Tests de ${endpoint} completados.`, 'success');
            } catch (error) {
                logMessage(`❌ Error en tests de ${endpoint}: ` + error.message, 'error');
                document.getElementById('testStatus').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error ejecutando tests de ${endpoint}: ${error.message}
                </div>
            `;
            } finally {
                isTestRunning = false;
            }
        }

        // Función para obtener token de prueba
        async function getTestToken() {
            showLoading('Obteniendo token de prueba...');
            logMessage('🔑 Solicitando token de prueba...', 'info');

            try {
                const response = await fetch('api/get_test_token', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('testToken').value = data.access_token;
                    logMessage('✅ Token de prueba obtenido exitosamente.', 'success');

                    document.getElementById('testStatus').innerHTML = `
                    <div class="alert alert-success">
                        <h6><i class="fas fa-key"></i> Token de Prueba Generado</h6>
                        <p><strong>Token:</strong> ${data.access_token.substring(0, 20)}...</p>
                        <p><strong>Email:</strong> ${data.test_email}</p>
                        <p><strong>Expira en:</strong> 24 horas</p>
                        <small class="text-muted">El token se ha guardado automáticamente en la configuración.</small>
                    </div>
                `;
                } else {
                    throw new Error(data.error || 'No se pudo obtener token');
                }
            } catch (error) {
                logMessage('❌ Error obteniendo token: ' + error.message, 'error');
                document.getElementById('testStatus').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error obteniendo token: ${error.message}
                </div>
            `;
            }
        }

        // Función para ejecutar test de carga
        async function runStressTest() {
            if (isTestRunning) {
                alert('Ya hay tests ejecutándose. Espera a que terminen.');
                return;
            }

            const iterations = document.getElementById('stressIterations').value || 10;
            isTestRunning = true;
            showLoading(`Ejecutando test de carga (${iterations} iteraciones)...`);
            logMessage(`🔥 Iniciando test de carga con ${iterations} iteraciones...`, 'info');

            try {
                const response = await fetch(`api/stress_test?iterations=${iterations}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                displayStressTestResults(data);
                logMessage('✅ Test de carga completado.', 'success');
            } catch (error) {
                logMessage('❌ Error en test de carga: ' + error.message, 'error');
                document.getElementById('testStatus').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error en test de carga: ${error.message}
                </div>
            `;
            } finally {
                isTestRunning = false;
            }
        }

        // Función para mostrar resultados de tests
        function displayTestResults(data) {
            currentResults = data;

            if (data.summary) {
                // Mostrar estadísticas generales
                document.getElementById('testStats').style.display = 'block';
                document.getElementById('totalTests').textContent = data.summary.total_tests;
                document.getElementById('passedTests').textContent = data.summary.passed;
                document.getElementById('failedTests').textContent = data.summary.failed;
                document.getElementById('successRate').textContent = data.summary.success_rate + '%';
                document.getElementById('executionTime').textContent = data.summary.execution_time;

                // Mostrar calificación
                const gradeElement = document.getElementById('gradeDisplay');
                gradeElement.textContent = data.summary.grade;
                gradeElement.className = 'grade-badge ' + getGradeClass(data.summary.success_rate);

                // Mostrar estadísticas por categoría
                displayCategoryStats(data.category_breakdown);

                // Mostrar recomendaciones
                displayRecommendations(data.recommendations);
            }

            // Mostrar resultados detallados
            displayDetailedResults(data.test_results || data.results);
            document.getElementById('testStatus').style.display = 'none';
        }

        // Función para mostrar resultados de test de carga
        function displayStressTestResults(data) {
            const results = data.stress_test_results;

            document.getElementById('testStatus').innerHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-tachometer-alt"></i> Resultados del Test de Carga</h6>
                <div class="row text-center">
                    <div class="col-md-3">
                        <strong>${results.iterations}</strong><br>
                        <small>Iteraciones</small>
                    </div>
                    <div class="col-md-3">
                        <strong>${results.success_rate}</strong><br>
                        <small>Tasa de Éxito</small>
                    </div>
                    <div class="col-md-3">
                        <strong>${results.average_response_time}</strong><br>
                        <small>Tiempo Promedio</small>
                    </div>
                    <div class="col-md-3">
                        <strong>${results.requests_per_second}</strong><br>
                        <small>Req/Seg</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <small><strong>Tiempo Total:</strong> ${results.total_time}</small><br>
                        <small><strong>Tiempo Mín:</strong> ${results.min_response_time}</small>
                    </div>
                    <div class="col-md-6">
                        <small><strong>Requests Exitosos:</strong> ${results.successful_requests}</small><br>
                        <small><strong>Tiempo Máx:</strong> ${results.max_response_time}</small>
                    </div>
                </div>
            </div>
        `;
        }

        // Función para mostrar estadísticas por categoría
        function displayCategoryStats(categoryBreakdown) {
            if (!categoryBreakdown) return;

            const container = document.getElementById('categoryStats');
            container.innerHTML = '';

            Object.entries(categoryBreakdown).forEach(([category, stats]) => {
                const card = document.createElement('div');
                card.className = 'category-stat-card';
                card.innerHTML = `
                <h6>${category.toUpperCase()}</h6>
                <div class="h4 ${stats.success_rate >= 80 ? 'text-success' : stats.success_rate >= 60 ? 'text-warning' : 'text-danger'}">
                    ${stats.success_rate}%
                </div>
                <small>${stats.passed}/${stats.total} tests</small>
            `;
                container.appendChild(card);
            });
        }

        // Función para mostrar recomendaciones
        function displayRecommendations(recommendations) {
            if (!recommendations || recommendations.length === 0) return;

            const container = document.getElementById('recommendations');
            const list = document.getElementById('recommendationsList');

            list.innerHTML = '';
            recommendations.forEach(rec => {
                const li = document.createElement('li');
                li.textContent = rec;
                list.appendChild(li);
            });

            container.style.display = 'block';
        }

        // Función para mostrar resultados detallados
        function displayDetailedResults(results) {
            if (!results) return;

            const container = document.getElementById('testResults');
            container.innerHTML = '';

            results.forEach(result => {
                if (result.test === 'log') {
                    // Mostrar logs como separadores
                    const logElement = document.createElement('div');
                    logElement.className = 'test-category';
                    logElement.innerHTML = `<h6>${result.message}</h6>`;
                    container.appendChild(logElement);
                } else {
                    // Mostrar resultado del test
                    const testElement = document.createElement('div');
                    testElement.className = `test-result-item ${result.status.toLowerCase()}`;

                    testElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="status-indicator status-${result.status.toLowerCase()}"></span>
                            <strong>${result.test}</strong>
                            <div class="mt-1">${result.message}</div>
                            ${result.timestamp ? `<small class="text-muted">⏱️ ${result.timestamp}</small>` : ''}
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary">${result.status_code || '-'}</span>
                        </div>
                    </div>
                    ${result.details ? createDetailsSection(result.details) : ''}
                `;

                    container.appendChild(testElement);
                }
            });
        }

        // Función para crear sección de detalles
        function createDetailsSection(details) {
            return `
            <div class="test-details mt-2">
                <small>
                    ${Object.entries(details).map(([key, value]) =>
                        `<div><strong>${key}:</strong> ${JSON.stringify(value)}</div>`
                    ).join('')}
                </small>
            </div>
        `;
        }

        // Función para obtener clase CSS según la calificación
        function getGradeClass(successRate) {
            if (successRate >= 90) return 'bg-success';
            if (successRate >= 70) return 'bg-warning';
            return 'bg-danger';
        }

        // Función para mostrar estado de carga
        function showLoading(message) {
            document.getElementById('testStatus').innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner"></div>
                <div class="mt-2">${message}</div>
            </div>
        `;
            document.getElementById('testStatus').style.display = 'block';
        }

        // Función para limpiar resultados
        function clearResults() {
            document.getElementById('testStats').style.display = 'none';
            document.getElementById('testResults').innerHTML = '';
            document.getElementById('recommendations').style.display = 'none';
            currentResults = null;
        }

        // Función para exportar resultados
        function exportResults() {
            if (!currentResults) {
                alert('No hay resultados para exportar.');
                return;
            }

            const dataStr = JSON.stringify(currentResults, null, 2);
            const dataBlob = new Blob([dataStr], {
                type: 'application/json'
            });

            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `lms-api-test-results-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.json`;
            link.click();

            logMessage('📊 Resultados exportados exitosamente.', 'success');
        }

        // Función para logging en tiempo real
        function logMessage(message, type = 'info') {
            const log = document.getElementById('realTimeLog');
            const timestamp = new Date().toLocaleTimeString();

            const logEntry = document.createElement('div');
            logEntry.className = `text-${getLogColor(type)}`;
            logEntry.innerHTML = `[${timestamp}] ${message}<br>`;

            log.appendChild(logEntry);
            log.scrollTop = log.scrollHeight;
        }

        // Función para obtener color de log según tipo
        function getLogColor(type) {
            switch (type) {
                case 'success':
                    return 'success';
                case 'error':
                    return 'danger';
                case 'warning':
                    return 'warning';
                default:
                    return 'info';
            }
        }

        // Función para guardar configuración
        function saveConfig() {
            const config = {
                apiBaseUrl: document.getElementById('apiBaseUrl').value,
                testToken: document.getElementById('testToken').value,
                stressIterations: document.getElementById('stressIterations').value
            };

            localStorage.setItem('lmsTestConfig', JSON.stringify(config));
            logMessage('⚙️ Configuración guardada.', 'success');

            // Mostrar confirmación visual
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }

        // Función para cargar configuración
        function loadConfig() {
            const saved = localStorage.getItem('lmsTestConfig');
            if (saved) {
                const config = JSON.parse(saved);
                document.getElementById('apiBaseUrl').value = config.apiBaseUrl || '';
                document.getElementById('testToken').value = config.testToken || '';
                document.getElementById('stressIterations').value = config.stressIterations || 10;
                logMessage('⚙️ Configuración cargada desde almacenamiento local.', 'info');
            }
        }

        // Función para actualizar fecha actual
        function updateCurrentDate() {
            document.getElementById('currentDate').textContent = new Date().toLocaleDateString('es-CL');
        }

        // Función para manejar teclas de acceso rápido
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'Enter':
                        e.preventDefault();
                        if (!isTestRunning) runAllTests();
                        break;
                    case 'k':
                        e.preventDefault();
                        if (!isTestRunning) getTestToken();
                        break;
                    case 's':
                        e.preventDefault();
                        if (!isTestRunning) runStressTest();
                        break;
                    case 'e':
                        e.preventDefault();
                        exportResults();
                        break;
                    case 'l':
                        e.preventDefault();
                        clearResults();
                        break;
                }
            }
        });

        // Función para mostrar ayuda de teclas
        function showKeyboardHelp() {
            alert(`Atajos de Teclado:     
            Ctrl/Cmd + Enter: Ejecutar todos los tests
            Ctrl/Cmd + K: Obtener token de prueba
            Ctrl/Cmd + S: Test de carga
            Ctrl/Cmd + E: Exportar resultados
            Ctrl/Cmd + L: Limpiar resultados`);
        }

        // Auto-refresh de la página cada 30 minutos para mantener la sesión
        setInterval(() => {
            logMessage('🔄 Auto-refresh de sesión (30 min).', 'info');
        }, 30 * 60 * 1000);

        // Manejar errores globales
        window.addEventListener('error', function(e) {
            logMessage(`❌ Error JavaScript: ${e.message}`, 'error');
        });

        // Manejar errores de red
        window.addEventListener('unhandledrejection', function(e) {
            logMessage(`❌ Error de red: ${e.reason}`, 'error');
        });

        // Función para verificar estado de la API
        async function checkApiHealth() {
            try {
                const response = await fetch('api/test', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (response.ok) {
                    document.querySelector('#systemInfo .text-success').innerHTML = '✅ Conectado';
                    logMessage('✅ API disponible.', 'success');
                } else {
                    throw new Error('API no responde correctamente');
                }
            } catch (error) {
                document.querySelector('#systemInfo .text-success').innerHTML = '❌ Desconectado';
                logMessage('❌ API no disponible: ' + error.message, 'error');
            }
        }

        // Verificar estado cada 5 minutos
        setInterval(checkApiHealth, 5 * 60 * 1000);

        // Verificar estado inicial después de 2 segundos
        setTimeout(checkApiHealth, 2000);

        // Funciones adicionales para mejor UX
        function showTooltips() {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
        }

        // Mostrar progreso durante tests largos
        function updateProgress(current, total) {
            const percentage = Math.round((current / total) * 100);
            const progressBar = document.getElementById('testProgress');
            if (progressBar) {
                progressBar.style.width = percentage + '%';
                progressBar.textContent = percentage + '%';
            }
        }

        // Función para formatear JSON en resultados
        function formatJSON(obj) {
            return JSON.stringify(obj, null, 2);
        }

        // Función para copiar resultados al portapapeles
        async function copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                logMessage('📋 Resultado copiado al portapapeles.', 'success');
            } catch (err) {
                logMessage('❌ Error copiando al portapapeles.', 'error');
            }
        }

        // Agregar listener para botón de ayuda (después de cargarse la página)
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar botón de ayuda si no existe
            const helpButton = document.createElement('button');
            helpButton.className = 'btn btn-outline-info btn-sm position-fixed';
            helpButton.style.cssText = 'bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px;';
            helpButton.innerHTML = '<i class="fas fa-question"></i>';
            helpButton.onclick = showKeyboardHelp;
            helpButton.title = 'Ayuda y atajos de teclado';
            document.body.appendChild(helpButton);
        });

        // Mejorar el scroll automático en logs
        function autoScrollLog() {
            const log = document.getElementById('realTimeLog');
            const isScrolledToBottom = log.scrollHeight - log.clientHeight <= log.scrollTop + 1;
            if (isScrolledToBottom) {
                log.scrollTop = log.scrollHeight;
            }
        }

        // Función para resaltar sintaxis JSON sin usar backticks/template literals
        function highlightJSON(json) {
            if (typeof json !== 'string') {
                json = JSON.stringify(json, null, 2);
            }
            const pre = document.createElement('pre');
            const code = document.createElement('code');
            code.className = 'language-json';
            code.textContent = json;
            pre.appendChild(code);
            return pre.outerHTML;
        }
    </script>

</body>

</html>
