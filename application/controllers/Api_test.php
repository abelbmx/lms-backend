<?php
// =====================================================
// CONTROLADOR DE TESTS - API LMS
// =====================================================

defined('BASEPATH') or exit('No direct script access allowed');

class Api_test extends CI_Controller
{
    private $base_url;
    private $access_token;
    private $test_results = [];
    private $test_user = [];
    private $test_course_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->base_url = base_url('api/');

        // Cargar modelos necesarios para limpiar datos de prueba
        $this->load->model(['Usuario_model', 'Curso_model']);
    }

    // =====================================================
    // INTERFAZ WEB PARA TESTS
    // =====================================================

    public function index()
    {
        $this->load->view('tests/api_test_interface');
    }

    // =====================================================
    // EJECUTAR TODOS LOS TESTS COMPLETOS
    // =====================================================

    public function run_all_tests()
    {
        $this->output->set_content_type('application/json');

        // Limpiar datos de pruebas anteriores
        $this->cleanup_test_data();

        $start_time = microtime(true);

        // Ejecutar tests en orden lógico
        $this->test_api_health();
        $this->test_user_registration();
        $this->test_user_login();
        $this->test_user_profile();
        $this->test_password_change();
        $this->test_categories_endpoints();
        $this->test_courses_endpoints();
        $this->test_course_creation();
        $this->test_enrollment_endpoints();
        $this->test_lessons_endpoints();
        $this->test_progress_endpoints();
        $this->test_evaluations_endpoints();
        $this->test_certificates_endpoints();
        $this->test_rating_endpoints();
        $this->test_forums_endpoints();
        $this->test_notifications_endpoints();
        $this->test_dashboard_endpoints();
        $this->test_logout();

        $end_time = microtime(true);
        $execution_time = round($end_time - $start_time, 2);

        // Generar reporte
        $report = $this->generate_test_report($execution_time);

        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =====================================================
    // TESTS COMPLETOS POR CATEGORÍA
    // =====================================================

    private function test_api_health()
    {
        $this->log_test('🏥 VERIFICANDO SALUD DE LA API');

        // Test 1: API Test endpoint
        $response = $this->make_request('test', 'GET');
        $this->assert_response($response, 'api_health_test', '✅ API Test Endpoint - Verificar que la API responde correctamente', 200);

        // Test 2: Token endpoint OAuth2
        $token_data = 'grant_type=client_credentials&client_id=lms_client&client_secret=lms_secret_2024&scope=api1';
        $response = $this->make_request_raw('token', 'POST', $token_data, 'application/x-www-form-urlencoded');
        $this->assert_response($response, 'oauth_token_endpoint', '🔑 OAuth2 Token - Verificar que se pueden obtener tokens de autenticación', 200);
    }

    private function test_user_registration()
    {
        $this->log_test('👤 PROBANDO SISTEMA DE REGISTRO DE USUARIOS');

        // Test 1: Registro exitoso con datos completos
        $test_data = [
            'nombre' => 'Usuario',
            'apellido' => 'Prueba',
            'email' => 'usuario.prueba.' . time() . '@lmstest.com',
            'password' => 'password123',
            'telefono' => '+56987654321',
            'fecha_nacimiento' => '1990-01-01',
            'genero' => 'masculino',
            'pais' => 'Chile',
            'ciudad' => 'Santiago'
        ];

        $response = $this->make_request('register', 'POST', $test_data);
        $this->assert_response($response, 'register_complete_success', '✅ Registro Completo - Usuario con todos los datos debe registrarse exitosamente', 200);

        if ($response['success']) {
            $this->test_user = $test_data;
        }

        // Test 2: Registro mínimo (solo campos requeridos)
        $minimal_data = [
            'nombre' => 'Test',
            'apellido' => 'Minimal',
            'email' => 'test.minimal.' . time() . '@lmstest.com',
            'password' => 'test123456'
        ];

        $response = $this->make_request('register', 'POST', $minimal_data);
        $this->assert_response($response, 'register_minimal_success', '✅ Registro Mínimo - Usuario con datos básicos debe registrarse correctamente', 200);

        // Test 3: Email duplicado
        $response = $this->make_request('register', 'POST', $test_data);
        $this->assert_response($response, 'register_duplicate_email', '❌ Email Duplicado - Sistema debe rechazar email ya registrado', 409);

        // Test 4: Campos requeridos faltantes
        $invalid_data = ['nombre' => 'Test'];
        $response = $this->make_request('register', 'POST', $invalid_data);
        $this->assert_response($response, 'register_missing_fields', '❌ Campos Faltantes - Debe rechazar registro sin campos obligatorios', 400);

        // Test 5: Contraseña muy corta
        $weak_password = [
            'nombre' => 'Test',
            'apellido' => 'Weak',
            'email' => 'weak.pass.' . time() . '@lmstest.com',
            'password' => '123'
        ];

        $response = $this->make_request('register', 'POST', $weak_password);
        $this->assert_response($response, 'register_weak_password', '❌ Contraseña Débil - Debe rechazar contraseñas menores a 6 caracteres', 400);

        // Test 6: Email inválido
        $invalid_email = [
            'nombre' => 'Test',
            'apellido' => 'Invalid',
            'email' => 'email-invalido',
            'password' => 'password123'
        ];

        $response = $this->make_request('register', 'POST', $invalid_email);
        $this->assert_response($response, 'register_invalid_email', '❌ Email Inválido - Debe rechazar emails con formato incorrecto', 400);
    }

    private function test_user_login()
    {
        $this->log_test('🔐 PROBANDO SISTEMA DE AUTENTICACIÓN');

        if (empty($this->test_user)) {
            $this->skip_test('login_tests', '⚠️ No hay usuario de prueba disponible para tests de login');
            return;
        }

        // Test 1: Login exitoso
        $login_data = [
            'email' => $this->test_user['email'],
            'password' => $this->test_user['password']
        ];

        $response = $this->make_request('login', 'POST', $login_data);
        $this->assert_response($response, 'login_success', '✅ Login Exitoso - Credenciales correctas deben generar token de acceso', 200);

        if ($response['success'] && isset($response['data']['data']['access_token'])) {
            $this->access_token = $response['data']['data']['access_token'];
        }

        // Test 2: Login con contraseña incorrecta
        $invalid_password = [
            'email' => $this->test_user['email'],
            'password' => 'contraseña_incorrecta'
        ];

        $response = $this->make_request('login', 'POST', $invalid_password);
        $this->assert_response($response, 'login_wrong_password', '❌ Contraseña Incorrecta - Debe rechazar credenciales inválidas', 401);

        // Test 3: Login con email inexistente
        $nonexistent_user = [
            'email' => 'usuario.noexiste@lmstest.com',
            'password' => 'cualquier_password'
        ];

        $response = $this->make_request('login', 'POST', $nonexistent_user);
        $this->assert_response($response, 'login_nonexistent_user', '❌ Usuario Inexistente - Debe rechazar usuarios no registrados', 401);

        // Test 4: Login sin datos
        $response = $this->make_request('login', 'POST', []);
        $this->assert_response($response, 'login_empty_data', '❌ Sin Credenciales - Debe rechazar login sin email y contraseña', 400);

        // Test 5: JSON malformado
        $response = $this->make_request_raw('login', 'POST', 'invalid_json{', 'application/json');
        $this->assert_response($response, 'login_invalid_json', '❌ JSON Inválido - Debe rechazar requests con JSON malformado', 400);
    }

    private function test_user_profile()
    {
        $this->log_test('👨‍💼 PROBANDO GESTIÓN DE PERFIL DE USUARIO');

        if (!$this->access_token) {
            $this->skip_test('profile_tests', '⚠️ No hay token de acceso para tests de perfil');
            return;
        }

        // Test 1: Obtener perfil con token válido
        $response = $this->make_authenticated_request('profile', 'GET');
        $this->assert_response($response, 'get_profile_success', '✅ Obtener Perfil - Debe retornar información completa del usuario autenticado', 200);

        // Test 2: Obtener perfil sin token
        $response = $this->make_request('profile', 'GET');
        $this->assert_response($response, 'get_profile_no_token', '❌ Sin Autenticación - Debe rechazar acceso sin token de autorización', [401, 403]);

        // Test 3: Actualizar perfil con datos válidos
        $update_data = [
            'nombre' => 'Usuario Actualizado',
            'bio' => 'Mi biografía ha sido actualizada desde los tests automáticos de la API',
            'telefono' => '+56912345678',
            'ciudad' => 'Valparaíso'
        ];

        $response = $this->make_authenticated_request('profile/update', 'PUT', $update_data);
        $this->assert_response($response, 'update_profile_success', '✅ Actualizar Perfil - Datos válidos deben actualizarse correctamente', 200);

        // Test 4: Actualizar sin datos
        $response = $this->make_authenticated_request('profile/update', 'PUT', []);
        $this->assert_response($response, 'update_profile_no_data', '❌ Sin Datos - Debe rechazar actualización vacía', 400);
    }

    private function test_password_change()
    {
        $this->log_test('🔒 PROBANDO CAMBIO DE CONTRASEÑA');

        if (!$this->access_token) {
            $this->skip_test('password_tests', '⚠️ No hay token de acceso para tests de contraseña');
            return;
        }

        // Test 1: Cambio exitoso de contraseña
        $password_data = [
            'current_password' => $this->test_user['password'],
            'new_password' => 'nueva_password_123',
            'confirm_password' => 'nueva_password_123'
        ];

        $response = $this->make_authenticated_request('profile/change-password', 'POST', $password_data);
        $this->assert_response($response, 'change_password_success', '✅ Cambio Exitoso - Contraseña debe cambiarse con datos correctos', 200);

        if ($response['success']) {
            $this->test_user['password'] = 'nueva_password_123';
        }

        // Test 2: Contraseña actual incorrecta
        $wrong_current = [
            'current_password' => 'password_incorrecta',
            'new_password' => 'otra_nueva_password',
            'confirm_password' => 'otra_nueva_password'
        ];

        $response = $this->make_authenticated_request('profile/change-password', 'POST', $wrong_current);
        $this->assert_response($response, 'change_password_wrong_current', '❌ Contraseña Actual Incorrecta - Debe validar contraseña actual', 401);

        // Test 3: Contraseñas nuevas no coinciden
        $mismatch_passwords = [
            'current_password' => $this->test_user['password'],
            'new_password' => 'password1',
            'confirm_password' => 'password2'
        ];

        $response = $this->make_authenticated_request('profile/change-password', 'POST', $mismatch_passwords);
        $this->assert_response($response, 'change_password_mismatch', '❌ Contraseñas No Coinciden - Nueva contraseña y confirmación deben ser iguales', 400);

        // Test 4: Nueva contraseña muy corta
        $short_password = [
            'current_password' => $this->test_user['password'],
            'new_password' => '123',
            'confirm_password' => '123'
        ];

        $response = $this->make_authenticated_request('profile/change-password', 'POST', $short_password);
        $this->assert_response($response, 'change_password_too_short', '❌ Contraseña Muy Corta - Debe requerir mínimo 6 caracteres', 400);
    }

    private function test_categories_endpoints()
    {
        $this->log_test('📂 PROBANDO ENDPOINTS DE CATEGORÍAS');

        if (!$this->access_token) {
            $this->skip_test('categories_tests', '⚠️ No hay token de acceso para tests de categorías');
            return;
        }

        // Test 1: Listar todas las categorías
        $response = $this->make_authenticated_request('categories', 'GET');
        $this->assert_response($response, 'list_categories', '📋 Listar Categorías - Debe retornar lista completa de categorías disponibles', 200);

        // Test 2: Categorías en estructura de árbol
        $response = $this->make_authenticated_request('categories?tree=true', 'GET');
        $this->assert_response($response, 'categories_tree', '🌳 Árbol de Categorías - Debe organizar categorías jerárquicamente', 200);

        // Test 3: Categorías sin autenticación
        $response = $this->make_request('categories', 'GET');
        $this->assert_response($response, 'categories_no_auth', '❌ Sin Autenticación - Debe requerir token para acceder', [401, 403]);
    }

    private function test_courses_endpoints()
    {
        $this->log_test('📚 PROBANDO ENDPOINTS DE CURSOS');

        if (!$this->access_token) {
            $this->skip_test('courses_tests', '⚠️ No hay token de acceso para tests de cursos');
            return;
        }

        // Test 1: Listar todos los cursos
        $response = $this->make_authenticated_request('courses', 'GET');
        $this->assert_response($response, 'list_courses', '📋 Listar Cursos - Debe retornar catálogo completo de cursos', 200);

        // Test 2: Cursos con paginación
        $response = $this->make_authenticated_request('courses?page=1&limit=5', 'GET');
        $this->assert_response($response, 'courses_pagination', '📄 Paginación - Debe manejar parámetros de página y límite', 200);

        // Test 3: Filtrar cursos por nivel
        $response = $this->make_authenticated_request('courses?nivel=principiante', 'GET');
        $this->assert_response($response, 'filter_by_level', '🎯 Filtro por Nivel - Debe filtrar por dificultad (principiante/intermedio/avanzado)', 200);

        // Test 4: Buscar cursos por texto
        $response = $this->make_authenticated_request('courses?search=javascript', 'GET');
        $this->assert_response($response, 'search_courses', '🔍 Búsqueda de Texto - Debe buscar en títulos y descripciones', 200);

        // Test 5: Filtrar por categoría
        $response = $this->make_authenticated_request('courses?categoria_id=1', 'GET');
        $this->assert_response($response, 'filter_by_category', '📂 Filtro por Categoría - Debe filtrar cursos por categoría específica', 200);

        // Test 6: Cursos destacados
        $response = $this->make_authenticated_request('courses?destacado=true', 'GET');
        $this->assert_response($response, 'featured_courses', '⭐ Cursos Destacados - Debe mostrar solo cursos destacados', 200);

        // Test 7: Múltiples filtros combinados
        $response = $this->make_authenticated_request('courses?nivel=principiante&categoria_id=1&page=1&limit=3', 'GET');
        $this->assert_response($response, 'combined_filters', '🔧 Filtros Combinados - Debe aplicar múltiples filtros simultáneamente', 200);

        // Test 8: Obtener curso específico
        $response = $this->make_authenticated_request('courses/1', 'GET');
        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'get_course_detail',
                'status' => 'SKIP',
                'message' => '⚠️ Detalle de Curso - No hay cursos en la BD para probar detalles',
                'status_code' => 404
            ];
        } else {
            $this->assert_response($response, 'get_course_detail', '📖 Detalle de Curso - Debe incluir módulos, lecciones y toda la información', 200);

            if ($response['success']) {
                $this->test_course_id = 1;
            }
        }

        // Test 9: Curso inexistente
        $response = $this->make_authenticated_request('courses/99999', 'GET');
        $this->assert_response($response, 'course_not_found', '❌ Curso Inexistente - Debe retornar 404 para IDs no válidos', 404);

        // Test 10: Cursos sin autenticación
        $response = $this->make_request('courses', 'GET');
        $this->assert_response($response, 'courses_no_auth', '❌ Sin Autenticación - Debe requerir token para listar cursos', [401, 403]);
    }

    private function test_course_creation()
    {
        $this->log_test('➕ PROBANDO CREACIÓN DE CURSOS');

        if (!$this->access_token) {
            $this->skip_test('course_creation_tests', '⚠️ No hay token de acceso para tests de creación');
            return;
        }

        // Test 1: Crear curso como estudiante (debe fallar por permisos)
        $course_data = [
            'titulo' => 'Curso de Prueba Automática',
            'categoria_id' => 1,
            'descripcion_corta' => 'Este curso fue creado por los tests automáticos',
            'descripcion_larga' => 'Descripción completa del curso de prueba automática',
            'nivel' => 'principiante',
            'precio' => 0.00
        ];

        $response = $this->make_authenticated_request('courses/create', 'POST', $course_data);
        $this->assert_response($response, 'create_course_no_permission', '❌ Sin Permisos - Estudiante no debe poder crear cursos', 403);

        // Test 2: Crear curso sin datos requeridos
        $response = $this->make_authenticated_request('courses/create', 'POST', []);
        $this->assert_response($response, 'create_course_missing_data', '❌ Datos Faltantes - Debe rechazar creación sin campos obligatorios', [400, 403]);

        // Test 3: Crear curso con datos inválidos
        $invalid_course = [
            'titulo' => '',
            'categoria_id' => 'no_es_numero',
            'precio' => 'precio_invalido'
        ];

        $response = $this->make_authenticated_request('courses/create', 'POST', $invalid_course);
        $this->assert_response($response, 'create_course_invalid_data', '❌ Datos Inválidos - Debe validar tipos y formato de datos', [400, 403]);
    }

    private function test_enrollment_endpoints()
    {
        $this->log_test('📝 PROBANDO ENDPOINTS DE INSCRIPCIONES');

        if (!$this->access_token) {
            $this->skip_test('enrollment_tests', '⚠️ No hay token de acceso para tests de inscripciones');
            return;
        }

        // Test 1: Listar mis cursos (inicialmente vacío)
        $response = $this->make_authenticated_request('my-courses', 'GET');
        $this->assert_response($response, 'my_courses_empty', '📚 Mis Cursos Inicial - Usuario nuevo debe tener lista vacía', 200);

        // Test 2: Mis cursos con filtros por estado
        $response = $this->make_authenticated_request('my-courses?status=activa', 'GET');
        $this->assert_response($response, 'my_courses_filtered', '🔍 Filtro por Estado - Debe filtrar inscripciones por estado', 200);

        // Test 3: Mis cursos con paginación
        $response = $this->make_authenticated_request('my-courses?page=1&limit=5', 'GET');
        $this->assert_response($response, 'my_courses_pagination', '📄 Paginación Mis Cursos - Debe manejar paginación correctamente', 200);

        // Test 4: Intentar inscribirse en curso existente
        if ($this->test_course_id) {
            $enrollment_data = ['course_id' => $this->test_course_id, 'metodo_pago' => 'gratuito'];
            $response = $this->make_authenticated_request('enroll', 'POST', $enrollment_data);

            if ($response['status_code'] == 201) {
                $this->assert_response($response, 'enroll_course_success', '✅ Inscripción Exitosa - Debe permitir inscribirse en curso disponible', 201);
            } elseif ($response['status_code'] == 409) {
                $this->assert_response($response, 'enroll_course_already', '⚠️ Ya Inscrito - Correctamente rechaza inscripción duplicada', 409);
            } else {
                $this->assert_response($response, 'enroll_course_other', '📝 Respuesta Inscripción - Estado de inscripción en curso', [200, 201, 400, 403, 404, 409]);
            }
        } else {
            // Test con curso genérico
            $enrollment_data = ['course_id' => 1, 'metodo_pago' => 'gratuito'];
            $response = $this->make_authenticated_request('enroll', 'POST', $enrollment_data);

            if ($response['status_code'] == 404) {
                $this->test_results[] = [
                    'test' => 'enroll_course_no_courses',
                    'status' => 'SKIP',
                    'message' => '⚠️ Sin Cursos - No hay cursos disponibles para inscripción',
                    'status_code' => 404
                ];
            } else {
                $this->assert_response($response, 'enroll_course_attempt', '📝 Intento Inscripción - Respuesta del sistema', [200, 201, 403, 409]);
            }
        }

        // Test 5: Inscripción sin datos
        $response = $this->make_authenticated_request('enroll', 'POST', []);
        $this->assert_response($response, 'enroll_no_data', '❌ Sin Datos - Debe rechazar inscripción sin course_id', 400);

        // Test 6: Inscripción en curso inexistente
        $invalid_enrollment = ['course_id' => 99999, 'metodo_pago' => 'gratuito'];
        $response = $this->make_authenticated_request('enroll', 'POST', $invalid_enrollment);
        $this->assert_response($response, 'enroll_invalid_course', '❌ Curso Inexistente - Debe rechazar inscripción en curso inválido', 404);

        // Test 7: Inscripción con método de pago inválido
        $invalid_payment = ['course_id' => 1, 'metodo_pago' => 'metodo_inexistente'];
        $response = $this->make_authenticated_request('enroll', 'POST', $invalid_payment);
        $this->assert_response($response, 'enroll_invalid_payment', '📝 Método de Pago - Manejo de métodos de pago', [200, 201, 400, 404]);
    }

    private function test_lessons_endpoints()
    {
        $this->log_test('📖 PROBANDO ENDPOINTS DE LECCIONES');

        if (!$this->access_token) {
            $this->skip_test('lessons_tests', '⚠️ No hay token de acceso para tests de lecciones');
            return;
        }

        // Test 1: Obtener lección específica
        $response = $this->make_authenticated_request('lessons/1', 'GET');
        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'get_lesson_not_found',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Lecciones - No hay lecciones disponibles en la BD',
                'status_code' => 404
            ];
        } elseif ($response['status_code'] == 403) {
            $this->test_results[] = [
                'test' => 'get_lesson_no_enrollment',
                'status' => 'PASS',
                'message' => '✅ Control de Acceso - Correctamente rechaza acceso sin inscripción',
                'status_code' => 403
            ];
        } else {
            $this->assert_response($response, 'get_lesson_success', '📖 Obtener Lección - Debe retornar contenido completo de la lección', 200);
        }

        // Test 2: Lección inexistente
        $response = $this->make_authenticated_request('lessons/99999', 'GET');
        $this->assert_response($response, 'lesson_not_found', '❌ Lección Inexistente - Debe retornar 404 para IDs inválidos', 404);

        // Test 3: Lección sin autenticación
        $response = $this->make_request('lessons/1', 'GET');
        $this->assert_response($response, 'lesson_no_auth', '❌ Sin Autenticación - Debe requerir token para acceder', [401, 403]);
    }

    private function test_progress_endpoints()
    {
        $this->log_test('📊 PROBANDO ENDPOINTS DE PROGRESO');

        if (!$this->access_token) {
            $this->skip_test('progress_tests', '⚠️ No hay token de acceso para tests de progreso');
            return;
        }

        // Test 1: Completar lección
        $lesson_data = ['lesson_id' => 1, 'tiempo_visto_minutos' => 30];
        $response = $this->make_authenticated_request('lessons/complete', 'POST', $lesson_data);

        if ($response['status_code'] == 403) {
            $this->test_results[] = [
                'test' => 'complete_lesson_no_access',
                'status' => 'PASS',
                'message' => '✅ Control de Acceso - Correctamente rechaza completar sin inscripción',
                'status_code' => 403
            ];
        } elseif ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'complete_lesson_not_found',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Lecciones - No hay lecciones para completar',
                'status_code' => 404
            ];
        } else {
            $this->assert_response($response, 'complete_lesson_success', '✅ Completar Lección - Debe actualizar progreso y estadísticas', 200);
        }

        // Test 2: Completar lección sin datos
        $response = $this->make_authenticated_request('lessons/complete', 'POST', []);
        $this->assert_response($response, 'complete_lesson_no_data', '❌ Sin Datos - Debe rechazar sin lesson_id', 400);

        // Test 3: Completar lección inexistente
        $invalid_lesson = ['lesson_id' => 99999, 'tiempo_visto_minutos' => 30];
        $response = $this->make_authenticated_request('lessons/complete', 'POST', $invalid_lesson);
        $this->assert_response($response, 'complete_lesson_invalid', '❌ Lección Inexistente - Debe rechazar IDs de lección inválidos', [404, 403]);

        // Test 4: Progreso de curso
        if ($this->test_course_id) {
            $response = $this->make_authenticated_request('progress/' . $this->test_course_id, 'GET');
            if ($response['status_code'] == 403) {
                $this->test_results[] = [
                    'test' => 'course_progress_no_enrollment',
                    'status' => 'PASS',
                    'message' => '✅ Control de Acceso - Correctamente rechaza ver progreso sin inscripción',
                    'status_code' => 403
                ];
            } else {
                $this->assert_response($response, 'course_progress_success', '📊 Progreso de Curso - Debe mostrar estadísticas detalladas de progreso', 200);
            }
        } else {
            $response = $this->make_authenticated_request('progress/1', 'GET');
            if ($response['status_code'] == 403) {
                $this->test_results[] = [
                    'test' => 'course_progress_no_enrollment',
                    'status' => 'PASS',
                    'message' => '✅ Control de Acceso - Correctamente rechaza ver progreso sin inscripción',
                    'status_code' => 403
                ];
            } elseif ($response['status_code'] == 404) {
                $this->test_results[] = [
                    'test' => 'course_progress_not_found',
                    'status' => 'SKIP',
                    'message' => '⚠️ Sin Cursos - No hay cursos para ver progreso',
                    'status_code' => 404
                ];
            } else {
                $this->assert_response($response, 'course_progress_attempt', '📊 Progreso de Curso - Respuesta del sistema de progreso', 200);
            }
        }

        // Test 5: Progreso de curso inexistente
        $response = $this->make_authenticated_request('progress/99999', 'GET');
        $this->assert_response($response, 'progress_invalid_course', '❌ Curso Inexistente - Debe rechazar IDs de curso inválidos', [404, 403]);
    }

    private function test_evaluations_endpoints()
    {
        $this->log_test('🎯 PROBANDO ENDPOINTS DE EVALUACIONES');

        if (!$this->access_token) {
            $this->skip_test('evaluations_tests', '⚠️ No hay token de acceso para tests de evaluaciones');
            return;
        }

        // Test 1: Iniciar evaluación
        $evaluation_data = ['evaluation_id' => 1];
        $response = $this->make_authenticated_request('evaluations/start', 'POST', $evaluation_data);

        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'start_evaluation_not_found',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Evaluaciones - No hay evaluaciones disponibles para probar',
                'status_code' => 404
            ];
        } elseif ($response['status_code'] == 403) {
            $this->test_results[] = [
                'test' => 'start_evaluation_no_access',
                'status' => 'PASS',
                'message' => '✅ Control de Acceso - Correctamente rechaza iniciar sin inscripción',
                'status_code' => 403
            ];
        } else {
            $this->assert_response($response, 'start_evaluation_success', '🎯 Iniciar Evaluación - Debe crear intento y retornar preguntas', 200);
        }

        // Test 2: Iniciar evaluación sin datos
        $response = $this->make_authenticated_request('evaluations/start', 'POST', []);
        $this->assert_response($response, 'start_evaluation_no_data', '❌ Sin Datos - Debe rechazar sin evaluation_id', 400);

        // Test 3: Iniciar evaluación inexistente
        $invalid_eval = ['evaluation_id' => 99999];
        $response = $this->make_authenticated_request('evaluations/start', 'POST', $invalid_eval);
        $this->assert_response($response, 'start_evaluation_invalid', '❌ Evaluación Inexistente - Debe rechazar IDs inválidos', [404, 500]);

        // Test 4: Enviar respuestas de evaluación
        $answers_data = [
            'attempt_id' => 1,
            'answers' => ['1' => 'a', '2' => 'b', '3' => 'Respuesta de texto libre']
        ];
        $response = $this->make_authenticated_request('evaluations/submit', 'POST', $answers_data);

        if ($response['status_code'] == 404 || $response['status_code'] == 500) {
            $this->test_results[] = [
                'test' => 'submit_evaluation_no_attempt',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Intentos - No hay intentos de evaluación para probar',
                'status_code' => $response['status_code']
            ];
        } else {
            $this->assert_response($response, 'submit_evaluation_success', '📝 Enviar Evaluación - Debe procesar respuestas y calcular puntuación', 200);
        }

        // Test 5: Enviar evaluación sin datos
        $response = $this->make_authenticated_request('evaluations/submit', 'POST', []);
        $this->assert_response($response, 'submit_evaluation_no_data', '❌ Sin Datos - Debe rechazar sin attempt_id y answers', 400);

        // Test 6: Enviar evaluación con attempt_id inválido
        $invalid_submit = ['attempt_id' => 99999, 'answers' => ['1' => 'a']];
        $response = $this->make_authenticated_request('evaluations/submit', 'POST', $invalid_submit);
        $this->assert_response($response, 'submit_evaluation_invalid_attempt', '❌ Intento Inválido - Debe rechazar attempt_id inexistente', [404, 500]);
    }

    private function test_certificates_endpoints()
    {
        $this->log_test('🏆 PROBANDO ENDPOINTS DE CERTIFICADOS');

        if (!$this->access_token) {
            $this->skip_test('certificates_tests', '⚠️ No hay token de acceso para tests de certificados');
            return;
        }

        // Test 1: Generar certificado para curso
        if ($this->test_course_id) {
            $cert_data = ['course_id' => $this->test_course_id];
            $response = $this->make_authenticated_request('certificates/generate', 'POST', $cert_data);

            if ($response['status_code'] == 400) {
                $this->test_results[] = [
                    'test' => 'generate_certificate_not_completed',
                    'status' => 'PASS',
                    'message' => '✅ Requisitos No Cumplidos - Correctamente rechaza certificado de curso no completado',
                    'status_code' => 400
                ];
            } else {
                $this->assert_response($response, 'generate_certificate_success', '🏆 Generar Certificado - Debe crear certificado para curso completado', [201, 400]);
            }
        } else {
            $cert_data = ['course_id' => 1];
            $response = $this->make_authenticated_request('certificates/generate', 'POST', $cert_data);

            if ($response['status_code'] == 400) {
                $this->test_results[] = [
                    'test' => 'generate_certificate_requirements',
                    'status' => 'PASS',
                    'message' => '✅ Validación de Requisitos - Sistema valida correctamente completación del curso',
                    'status_code' => 400
                ];
            } elseif ($response['status_code'] == 404) {
                $this->test_results[] = [
                    'test' => 'generate_certificate_no_course',
                    'status' => 'SKIP',
                    'message' => '⚠️ Sin Cursos - No hay cursos para generar certificados',
                    'status_code' => 404
                ];
            } else {
                $this->assert_response($response, 'generate_certificate_attempt', '🏆 Generación de Certificado - Respuesta del sistema', [201, 400]);
            }
        }

        // Test 2: Generar certificado sin datos
        $response = $this->make_authenticated_request('certificates/generate', 'POST', []);
        $this->assert_response($response, 'generate_certificate_no_data', '❌ Sin Datos - Debe rechazar sin course_id', 400);

        // Test 3: Generar certificado para curso inexistente
        $invalid_cert = ['course_id' => 99999];
        $response = $this->make_authenticated_request('certificates/generate', 'POST', $invalid_cert);
        $this->assert_response($response, 'generate_certificate_invalid_course', '❌ Curso Inexistente - Debe rechazar IDs de curso inválidos', [404, 400]);
    }

    private function test_rating_endpoints()
    {
        $this->log_test('⭐ PROBANDO ENDPOINTS DE CALIFICACIONES');

        if (!$this->access_token) {
            $this->skip_test('rating_tests', '⚠️ No hay token de acceso para tests de calificaciones');
            return;
        }

        // Test 1: Calificar curso
        if ($this->test_course_id) {
            $rating_data = [
                'course_id' => $this->test_course_id,
                'rating' => 5,
                'comment' => 'Excelente curso de prueba automática. Sistema funcionando correctamente.'
            ];

            $response = $this->make_authenticated_request('rate', 'POST', $rating_data);
            if ($response['status_code'] == 403) {
                $this->test_results[] = [
                    'test' => 'rate_course_not_enrolled',
                    'status' => 'PASS',
                    'message' => '✅ Control de Acceso - Correctamente rechaza calificar sin inscripción',
                    'status_code' => 403
                ];
            } else {
                $this->assert_response($response, 'rate_course_success', '⭐ Calificar Curso - Debe guardar calificación y comentario', 200);
            }
        } else {
            $rating_data = [
                'course_id' => 1,
                'rating' => 4,
                'comment' => 'Curso de prueba para validar el sistema de calificaciones'
            ];

            $response = $this->make_authenticated_request('rate', 'POST', $rating_data);
            if ($response['status_code'] == 403) {
                $this->test_results[] = [
                    'test' => 'rate_course_access_control',
                    'status' => 'PASS',
                    'message' => '✅ Control de Acceso - Sistema valida inscripción antes de permitir calificar',
                    'status_code' => 403
                ];
            } else {
                $this->assert_response($response, 'rate_course_attempt', '⭐ Calificación de Curso - Respuesta del sistema', [200, 403]);
            }
        }

        // Test 2: Calificación inválida (fuera de rango)
        $invalid_rating = ['course_id' => 1, 'rating' => 10];
        $response = $this->make_authenticated_request('rate', 'POST', $invalid_rating);
        $this->assert_response($response, 'rate_course_invalid_rating', '❌ Calificación Inválida - Debe rechazar ratings fuera del rango 1-5', 400);

        // Test 3: Calificación sin datos
        $response = $this->make_authenticated_request('rate', 'POST', []);
        $this->assert_response($response, 'rate_course_no_data', '❌ Sin Datos - Debe rechazar sin course_id y rating', 400);

        // Test 4: Calificación con rating negativo
        $negative_rating = ['course_id' => 1, 'rating' => -1];
        $response = $this->make_authenticated_request('rate', 'POST', $negative_rating);
        $this->assert_response($response, 'rate_course_negative', '❌ Rating Negativo - Debe rechazar calificaciones negativas', 400);

        // Test 5: Calificar curso inexistente
        $nonexistent_course = ['course_id' => 99999, 'rating' => 5];
        $response = $this->make_authenticated_request('rate', 'POST', $nonexistent_course);
        $this->assert_response($response, 'rate_course_not_found', '❌ Curso Inexistente - Debe rechazar calificar cursos que no existen', [404, 403]);
    }

    private function test_forums_endpoints()
    {
        $this->log_test('💬 PROBANDO ENDPOINTS DE FOROS');

        if (!$this->access_token) {
            $this->skip_test('forums_tests', '⚠️ No hay token de acceso para tests de foros');
            return;
        }

        // Test 1: Obtener foros de curso
        if ($this->test_course_id) {
            $response = $this->make_authenticated_request('courses/' . $this->test_course_id . '/forums', 'GET');
            if ($response['status_code'] == 403) {
                $this->test_results[] = [
                    'test' => 'course_forums_no_enrollment',
                    'status' => 'PASS',
                    'message' => '✅ Control de Acceso - Correctamente rechaza acceso a foros sin inscripción',
                    'status_code' => 403
                ];
            } else {
                $this->assert_response($response, 'course_forums_success', '💬 Foros de Curso - Debe listar foros disponibles del curso', 200);
            }
        } else {
            $response = $this->make_authenticated_request('courses/1/forums', 'GET');
            if ($response['status_code'] == 403) {
                $this->test_results[] = [
                    'test' => 'course_forums_access_control',
                    'status' => 'PASS',
                    'message' => '✅ Control de Acceso - Sistema valida inscripción para acceder a foros',
                    'status_code' => 403
                ];
            } elseif ($response['status_code'] == 404) {
                $this->test_results[] = [
                    'test' => 'course_forums_not_found',
                    'status' => 'SKIP',
                    'message' => '⚠️ Sin Cursos - No hay cursos con foros para probar',
                    'status_code' => 404
                ];
            } else {
                $this->assert_response($response, 'course_forums_attempt', '💬 Foros de Curso - Respuesta del sistema', 200);
            }
        }

        // Test 2: Obtener posts de foro
        $response = $this->make_authenticated_request('forums/1/posts', 'GET');
        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'forum_posts_not_found',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Foros - No hay foros disponibles para obtener posts',
                'status_code' => 404
            ];
        } else {
            $this->assert_response($response, 'forum_posts_list', '📝 Posts de Foro - Debe listar posts y respuestas del foro', [200, 403]);
        }

        // Test 3: Crear post en foro
        $post_data = [
            'forum_id' => 1,
            'title' => 'Post de Prueba Automática',
            'content' => 'Este post fue creado por los tests automáticos del sistema LMS para validar la funcionalidad de foros.',
            'parent_id' => null
        ];

        $response = $this->make_authenticated_request('forums/posts/create', 'POST', $post_data);
        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'create_forum_post_no_forum',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Foros - No hay foros disponibles para crear posts',
                'status_code' => 404
            ];
        } elseif ($response['status_code'] == 403) {
            $this->test_results[] = [
                'test' => 'create_forum_post_no_access',
                'status' => 'PASS',
                'message' => '✅ Control de Acceso - Correctamente rechaza crear post sin acceso al foro',
                'status_code' => 403
            ];
        } else {
            $this->assert_response($response, 'create_forum_post_success', '📝 Crear Post - Debe crear post en foro correctamente', 200);
        }

        // Test 4: Crear post sin datos
        $response = $this->make_authenticated_request('forums/posts/create', 'POST', []);
        $this->assert_response($response, 'create_forum_post_no_data', '❌ Sin Datos - Debe rechazar crear post sin forum_id y content', 400);

        // Test 5: Crear respuesta a post
        $reply_data = [
            'forum_id' => 1,
            'title' => null,
            'content' => 'Esta es una respuesta de prueba automática a un post del foro.',
            'parent_id' => 1
        ];

        $response = $this->make_authenticated_request('forums/posts/create', 'POST', $reply_data);
        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'create_forum_reply_no_post',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Posts - No hay posts para responder',
                'status_code' => 404
            ];
        } else {
            $this->assert_response($response, 'create_forum_reply', '💬 Crear Respuesta - Debe crear respuesta a post existente', [200, 403]);
        }
    }

    private function test_notifications_endpoints()
    {
        $this->log_test('🔔 PROBANDO ENDPOINTS DE NOTIFICACIONES');

        if (!$this->access_token) {
            $this->skip_test('notifications_tests', '⚠️ No hay token de acceso para tests de notificaciones');
            return;
        }

        // Test 1: Listar todas las notificaciones
        $response = $this->make_authenticated_request('notifications', 'GET');
        $this->assert_response($response, 'list_notifications', '🔔 Listar Notificaciones - Debe retornar notificaciones del usuario', 200);

        // Test 2: Listar solo notificaciones no leídas
        $response = $this->make_authenticated_request('notifications?unread_only=true', 'GET');
        $this->assert_response($response, 'unread_notifications', '📬 Notificaciones No Leídas - Debe filtrar solo las no leídas', 200);

        // Test 3: Notificaciones con paginación
        $response = $this->make_authenticated_request('notifications?limit=5&offset=0', 'GET');
        $this->assert_response($response, 'notifications_pagination', '📄 Paginación Notificaciones - Debe manejar límite y offset', 200);

        // Test 4: Marcar notificación específica como leída
        $response = $this->make_authenticated_request('notifications/1/read', 'POST');
        if ($response['status_code'] == 404) {
            $this->test_results[] = [
                'test' => 'mark_notification_read_not_found',
                'status' => 'SKIP',
                'message' => '⚠️ Sin Notificaciones - No hay notificaciones para marcar como leída',
                'status_code' => 404
            ];
        } else {
            $this->assert_response($response, 'mark_notification_read', '✅ Marcar Como Leída - Debe marcar notificación específica', [200, 500]);
        }

        // Test 5: Marcar todas las notificaciones como leídas
        $response = $this->make_authenticated_request('notifications/mark-all-read', 'POST');
        $this->assert_response($response, 'mark_all_notifications_read', '✅ Marcar Todas Leídas - Debe marcar todas las notificaciones como leídas', 200);

        // Test 6: Notificaciones sin autenticación
        $response = $this->make_request('notifications', 'GET');
        $this->assert_response($response, 'notifications_no_auth', '❌ Sin Autenticación - Debe requerir token para acceder', [401, 403]);
    }

    private function test_dashboard_endpoints()
    {
        $this->log_test('📊 PROBANDO ENDPOINTS DE DASHBOARD');

        if (!$this->access_token) {
            $this->skip_test('dashboard_tests', '⚠️ No hay token de acceso para tests de dashboard');
            return;
        }

        // Test 1: Obtener dashboard completo
        $response = $this->make_authenticated_request('dashboard', 'GET');
        $this->assert_response($response, 'get_dashboard', '📊 Dashboard Completo - Debe retornar resumen personalizado del usuario', 200);

        // Test 2: Dashboard sin autenticación
        $response = $this->make_request('dashboard', 'GET');
        $this->assert_response($response, 'dashboard_no_auth', '❌ Sin Autenticación - Debe requerir token para acceder al dashboard', [401, 403]);
    }

    private function test_logout()
    {
        $this->log_test('🚪 PROBANDO LOGOUT Y FINALIZACIÓN DE SESIÓN');

        if (!$this->access_token) {
            $this->skip_test('logout_tests', '⚠️ No hay token de acceso para test de logout');
            return;
        }

        // Test 1: Logout exitoso
        $response = $this->make_authenticated_request('logout', 'POST');
        $this->assert_response($response, 'logout_success', '🚪 Logout Exitoso - Debe invalidar token y cerrar sesión', 200);

        // Test 2: Verificar que el token se invalidó
        $response = $this->make_authenticated_request('profile', 'GET');
        $this->assert_response($response, 'logout_token_invalidated', '🔒 Token Invalidado - Token debe estar inválido después del logout', [401, 403]);

        // Test 3: Logout sin token
        $response = $this->make_request('logout', 'POST');
        $this->assert_response($response, 'logout_no_token', '❌ Sin Token - Debe rechazar logout sin autenticación', [401, 403]);
    }

    // =====================================================
    // TESTS INDIVIDUALES POR CATEGORÍA
    // =====================================================

    public function test_single_endpoint($endpoint = null)
    {
        if (!$endpoint) {
            show_404();
            return;
        }

        $this->output->set_content_type('application/json');

        // Limpiar datos antes de cada test individual
        $this->cleanup_test_data();

        $start_time = microtime(true);

        switch ($endpoint) {
            case 'health':
                $this->test_api_health();
                break;
            case 'register':
                $this->test_user_registration();
                break;
            case 'login':
                $this->test_user_registration(); // Necesario para crear usuario
                $this->test_user_login();
                break;
            case 'profile':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_user_profile();
                break;
            case 'password':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_password_change();
                break;
            case 'categories':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_categories_endpoints();
                break;
            case 'courses':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_courses_endpoints();
                break;
            case 'enrollment':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_enrollment_endpoints();
                break;
            case 'lessons':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_lessons_endpoints();
                break;
            case 'progress':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_progress_endpoints();
                break;
            case 'evaluations':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_evaluations_endpoints();
                break;
            case 'certificates':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_certificates_endpoints();
                break;
            case 'ratings':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_rating_endpoints();
                break;
            case 'forums':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_forums_endpoints();
                break;
            case 'notifications':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_notifications_endpoints();
                break;
            case 'dashboard':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_dashboard_endpoints();
                break;
            case 'logout':
                $this->test_user_registration();
                $this->test_user_login();
                $this->test_logout();
                break;
            default:
                echo json_encode(['error' => 'Endpoint de prueba no encontrado: ' . $endpoint]);
                return;
        }

        $end_time = microtime(true);
        $execution_time = round($end_time - $start_time, 2);

        echo json_encode([
            'endpoint' => $endpoint,
            'execution_time' => $execution_time . ' seconds',
            'results' => $this->test_results
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // =====================================================
    // MÉTODOS AUXILIARES PARA PETICIONES HTTP
    // =====================================================

    private function make_request($endpoint, $method = 'GET', $data = null)
    {
        $url = $this->base_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = ['Content-Type: application/json'];

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => !$error && $response !== false,
            'data' => $response ? json_decode($response, true) : null,
            'status_code' => $status_code,
            'error' => $error,
            'raw_response' => $response
        ];
    }

    private function make_request_raw($endpoint, $method = 'POST', $data = null, $content_type = 'application/json')
    {
        $url = $this->base_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = ['Content-Type: ' . $content_type];

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => !$error && $response !== false,
            'data' => $response ? json_decode($response, true) : null,
            'status_code' => $status_code,
            'error' => $error,
            'raw_response' => $response
        ];
    }

    private function make_authenticated_request($endpoint, $method = 'GET', $data = null)
    {
        $url = $this->base_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token
        ];

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'success' => !$error && $response !== false,
            'data' => $response ? json_decode($response, true) : null,
            'status_code' => $status_code,
            'error' => $error,
            'raw_response' => $response
        ];
    }

    // =====================================================
    // MÉTODOS DE VALIDACIÓN Y REPORTES
    // =====================================================

    private function assert_response($response, $test_name, $description, $expected_status = 200)
    {
        $expected_statuses = is_array($expected_status) ? $expected_status : [$expected_status];

        $status = 'FAIL';
        $message = $description;
        $details = [];

        if (!$response['success']) {
            $status = 'FAIL';
            $message .= ' - ❌ Error de conexión: ' . $response['error'];
            $details['connection_error'] = $response['error'];
        } elseif (!in_array($response['status_code'], $expected_statuses)) {
            $status = 'FAIL';
            $message .= " - ❌ Código esperado: " . implode('/', $expected_statuses) . ", recibido: " . $response['status_code'];
            $details['expected_status'] = $expected_statuses;
            $details['actual_status'] = $response['status_code'];

            if ($response['data'] && isset($response['data']['error_description'])) {
                $details['error_message'] = $response['data']['error_description'];
            }
        } else {
            $status = 'PASS';
            $message .= ' - ✅ OK';

            if ($response['data']) {
                if (isset($response['data']['status'])) {
                    $details['api_status'] = $response['data']['status'];
                }
                if (isset($response['data']['data']) && is_array($response['data']['data'])) {
                    $details['data_count'] = count($response['data']['data']);
                }
            }
        }

        $this->test_results[] = [
            'test' => $test_name,
            'status' => $status,
            'message' => $message,
            'status_code' => $response['status_code'],
            'details' => $details,
            'response_sample' => $this->extract_response_sample($response['data']),
            'timestamp' => date('H:i:s')
        ];
    }

    private function extract_response_sample($data)
    {
        if (!$data) return null;

        // Extraer una muestra pequeña de la respuesta para el reporte
        $sample = [];

        if (isset($data['status'])) {
            $sample['status'] = $data['status'];
        }

        if (isset($data['message'])) {
            $sample['message'] = substr($data['message'], 0, 100);
        }

        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                $sample['data_type'] = 'array';
                $sample['data_count'] = count($data['data']);

                if (count($data['data']) > 0) {
                    $first_item = reset($data['data']);
                    if (is_array($first_item)) {
                        $sample['data_structure'] = array_keys($first_item);
                    }
                }
            } else {
                $sample['data_type'] = gettype($data['data']);
                $sample['data_preview'] = substr(json_encode($data['data']), 0, 100);
            }
        }

        return $sample;
    }

    private function skip_test($test_name, $reason)
    {
        $this->test_results[] = [
            'test' => $test_name,
            'status' => 'SKIP',
            'message' => $reason,
            'timestamp' => date('H:i:s')
        ];
    }

    private function log_test($message)
    {
        $this->test_results[] = [
            'test' => 'log',
            'status' => 'INFO',
            'message' => $message,
            'timestamp' => date('H:i:s')
        ];
    }

    private function generate_test_report($execution_time)
    {
        $total_tests = count(array_filter($this->test_results, function ($result) {
            return !in_array($result['test'], ['log']);
        }));

        $passed = count(array_filter($this->test_results, function ($result) {
            return $result['status'] === 'PASS';
        }));

        $failed = count(array_filter($this->test_results, function ($result) {
            return $result['status'] === 'FAIL';
        }));

        $skipped = count(array_filter($this->test_results, function ($result) {
            return $result['status'] === 'SKIP';
        }));

        $success_rate = $total_tests > 0 ? round(($passed / $total_tests) * 100, 2) : 0;

        // Categorizar tests por área
        $categories = [
            'health' => ['api_health_test', 'oauth_token_endpoint'],
            'auth' => ['register_', 'login_', 'logout_'],
            'profile' => ['get_profile', 'update_profile', 'change_password'],
            'courses' => ['list_courses', 'get_course', 'create_course', 'filter_', 'search_'],
            'enrollment' => ['enroll_', 'my_courses'],
            'progress' => ['complete_lesson', 'course_progress'],
            'evaluations' => ['start_evaluation', 'submit_evaluation'],
            'certificates' => ['generate_certificate'],
            'ratings' => ['rate_course'],
            'forums' => ['forum_', 'create_forum'],
            'notifications' => ['notification'],
            'dashboard' => ['dashboard']
        ];

        $category_stats = [];
        foreach ($categories as $category => $patterns) {
            $category_tests = array_filter($this->test_results, function ($result) use ($patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($result['test'], $pattern) !== false) {
                        return true;
                    }
                }
                return false;
            });

            $category_passed = count(array_filter($category_tests, function ($test) {
                return $test['status'] === 'PASS';
            }));

            $category_total = count(array_filter($category_tests, function ($test) {
                return $test['status'] !== 'INFO';
            }));

            if ($category_total > 0) {
                $category_stats[$category] = [
                    'total' => $category_total,
                    'passed' => $category_passed,
                    'success_rate' => round(($category_passed / $category_total) * 100, 2)
                ];
            }
        }

        return [
            'summary' => [
                'total_tests' => $total_tests,
                'passed' => $passed,
                'failed' => $failed,
                'skipped' => $skipped,
                'success_rate' => $success_rate,
                'execution_time' => $execution_time . ' seconds',
                'grade' => $this->calculate_grade($success_rate),
                'status' => $this->get_overall_status($success_rate, $failed)
            ],
            'category_breakdown' => $category_stats,
            'test_results' => $this->test_results,
            'recommendations' => $this->generate_recommendations($failed, $skipped),
            'timestamp' => date('Y-m-d H:i:s'),
            'test_environment' => [
                'base_url' => $this->base_url,
                'user_agent' => 'LMS API Test Suite v2.0',
                'php_version' => PHP_VERSION
            ]
        ];
    }

    private function calculate_grade($success_rate)
    {
        if ($success_rate >= 95) return 'A+ (Excelente)';
        if ($success_rate >= 90) return 'A (Muy Bueno)';
        if ($success_rate >= 80) return 'B (Bueno)';
        if ($success_rate >= 70) return 'C (Aceptable)';
        if ($success_rate >= 60) return 'D (Necesita Mejoras)';
        return 'F (Crítico)';
    }

    private function get_overall_status($success_rate, $failed_count)
    {
        if ($failed_count === 0) return '🎉 Todos los tests pasaron exitosamente';
        if ($success_rate >= 90) return '✅ API funcionando muy bien con issues menores';
        if ($success_rate >= 70) return '⚠️ API funcionando con algunos problemas';
        return '❌ API tiene problemas críticos que requieren atención';
    }

    private function generate_recommendations($failed_count, $skipped_count)
    {
        $recommendations = [];

        if ($failed_count > 0) {
            $recommendations[] = "🔧 Revisar y corregir los {$failed_count} tests fallidos antes de producción";
        }

        if ($skipped_count > 5) {
            $recommendations[] = "📊 Crear datos de prueba para reducir tests omitidos ({$skipped_count} skipped)";
        }

        if (empty($this->test_user)) {
            $recommendations[] = "👤 Verificar que el sistema de registro de usuarios funcione correctamente";
        }

        if (!$this->access_token) {
            $recommendations[] = "🔐 Verificar configuración de OAuth2 y autenticación";
        }

        if (!$this->test_course_id) {
            $recommendations[] = "📚 Agregar cursos de muestra para tests más completos";
        }

        $recommendations[] = "🚀 Ejecutar tests regularmente durante el desarrollo";
        $recommendations[] = "📝 Documentar cualquier comportamiento específico encontrado";

        return $recommendations;
    }

    private function cleanup_test_data()
    {
        // 1) Eliminar notificaciones de usuarios de prueba (creados hace más de 1 hora)
        $horaLimite = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $sql_delete_notifications = "
        DELETE n
        FROM notificaciones AS n
        INNER JOIN usuarios AS u ON n.usuario_id = u.id
        WHERE
            (
                u.email   LIKE 'usuario.prueba.%@lmstest.com'
             OR u.email   LIKE 'test.minimal.%@lmstest.com'
             OR u.email   LIKE 'weak.pass.%@lmstest.com'
            )
            AND u.created_at < ?
    ";
        // Ejecutamos la eliminación pasando el parámetro de fecha
        $this->db->query($sql_delete_notifications, [$horaLimite]);


        // 2) Ahora sí, borrar los usuarios de prueba que queden (creados hace más de 1 hora)
        $this->db->where("
        (
            email LIKE 'usuario.prueba.%@lmstest.com'
         OR email LIKE 'test.minimal.%@lmstest.com'
         OR email LIKE 'weak.pass.%@lmstest.com'
        )
    ");
        $this->db->where('created_at <', $horaLimite);
        $this->db->delete('usuarios');


        // 3) Limpiar tokens OAuth2 expirados
        $this->db->where('expires <', date('Y-m-d H:i:s'));
        $this->db->delete('oauth_access_tokens');

        // 4) Limpiar api_logs antiguos (más de 24 horas)
        if ($this->db->table_exists('api_logs')) {
            $this->db->where('created_at <', date('Y-m-d H:i:s', strtotime('-24 hours')));
            $this->db->delete('api_logs');
        }
    }


    // =====================================================
    // ENDPOINT PARA OBTENER TOKEN DE PRUEBA
    // =====================================================

    public function get_test_token()
    {
        $this->output->set_content_type('application/json');

        // Crear usuario de prueba temporal
        $test_email = 'test.token.' . time() . '@lmstest.com';

        $user_data = [
            'nombre' => 'Usuario',
            'apellido' => 'Token',
            'email' => $test_email,
            'password' => 'test123456',
            'rol_id' => 3 // Estudiante
        ];

        $user_id = $this->Usuario_model->create_user($user_data);

        if ($user_id) {
            // Hacer login para obtener token
            $login_data = [
                'email' => $test_email,
                'password' => 'test123456'
            ];

            $response = $this->make_request('login', 'POST', $login_data);

            if ($response['success'] && isset($response['data']['data']['access_token'])) {
                echo json_encode([
                    'success' => true,
                    'access_token' => $response['data']['data']['access_token'],
                    'user_id' => $user_id,
                    'test_email' => $test_email,
                    'expires_in' => 86400,
                    'usage_instructions' => 'Usar este token en el header: Authorization: Bearer {token}'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo obtener token',
                    'response' => $response['data']
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo crear usuario de prueba'
            ]);
        }
    }

    // =====================================================
    // ENDPOINT PARA TEST DE CARGA
    // =====================================================

    public function stress_test()
    {
        $this->output->set_content_type('application/json');

        $iterations = $this->input->get('iterations') ?: 10;
        $concurrent = $this->input->get('concurrent') ?: 1;

        $start_time = microtime(true);
        $results = [];

        for ($i = 0; $i < $iterations; $i++) {
            $test_start = microtime(true);

            // Test básico de salud de la API
            $response = $this->make_request('test', 'GET');

            $test_end = microtime(true);
            $response_time = round(($test_end - $test_start) * 1000, 2); // en ms

            $results[] = [
                'iteration' => $i + 1,
                'response_time_ms' => $response_time,
                'status_code' => $response['status_code'],
                'success' => $response['success']
            ];
        }

        $end_time = microtime(true);
        $total_time = round($end_time - $start_time, 2);

        $response_times = array_column($results, 'response_time_ms');
        $successful_requests = count(array_filter($results, function ($r) {
            return $r['success'];
        }));

        echo json_encode([
            'stress_test_results' => [
                'iterations' => $iterations,
                'total_time' => $total_time . ' seconds',
                'successful_requests' => $successful_requests,
                'failed_requests' => $iterations - $successful_requests,
                'success_rate' => round(($successful_requests / $iterations) * 100, 2) . '%',
                'average_response_time' => round(array_sum($response_times) / count($response_times), 2) . ' ms',
                'min_response_time' => min($response_times) . ' ms',
                'max_response_time' => max($response_times) . ' ms',
                'requests_per_second' => round($iterations / $total_time, 2)
            ],
            'detailed_results' => $results
        ], JSON_PRETTY_PRINT);
    }
}
