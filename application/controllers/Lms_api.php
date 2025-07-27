<?php
// =====================================================
// CONTROLADOR PRINCIPAL - LMS_API COMPLETA
// =====================================================

defined('BASEPATH') or exit('No direct script access allowed');

use OAuth2\Request;
use OAuth2\Response;

class Lms_api extends CI_Controller
{
    protected $server;

    public function __construct()
    {
        parent::__construct();

        // Carga la biblioteca OAuth2Server
        $this->load->library('OAuth2Server');
        $this->server = $this->oauth2server->getServer();

        // Cargar todos los modelos del LMS
        $this->load->model([
            'Usuario_model',
            'Curso_model',
            'Leccion_model',
            'Inscripcion_model',
            'Evaluacion_model',
            'Progreso_model',
            'Certificado_model',
            'Calificacion_model',
            'Foro_model',
            'Notificacion_model',
            'Api_log_model',
            'Modulo_model',
            'Categoria_model',
            'Rol_model',
            'Configuracion_model',
            'Transaccion_model',
            'Reporte_model'
        ]);

        // Configurar headers CORS
        $this->set_cors_headers();
    }

    private function set_cors_headers()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
    }

    // =====================================================
    // SISTEMA DE AUTENTICACIÓN Y TOKENS
    // =====================================================

    // Obtener token OAuth2
    public function token()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        $raw_input = file_get_contents('php://input');
        $this->log_request('token', $raw_input, []);

        $this->server->handleTokenRequest($request, $response)->send();
    }

    // Login con email y password - Genera token de usuario
    public function login()
    {
        try {
            $raw_input = file_get_contents('php://input');
            $this->log_request('login', $raw_input, []);

            $input = json_decode($raw_input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->send_error_response(400, 'invalid_json', 'JSON inválido: ' . json_last_error_msg());
                return;
            }

            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;

            if (!$email || !$password) {
                $this->send_error_response(400, 'missing_credentials', 'Email y password son requeridos.');
                return;
            }

            // Validar credenciales
            $user = $this->Usuario_model->get_user_by_email($email);
            if (!$user || !password_verify($password, $user['password'])) {
                $this->send_error_response(401, 'invalid_credentials', 'Credenciales inválidas.');
                return;
            }

            if ($user['estado'] !== 'activo') {
                $this->send_error_response(403, 'account_disabled', 'Cuenta deshabilitada.');
                return;
            }

            // Actualizar último acceso
            $this->Usuario_model->update_last_access($user['id']);

            // Generar token de acceso manual (sin OAuth2 para simplificar)
            $access_token = $this->generate_access_token($user);

            // Guardar token en base de datos oauth_access_tokens
            $this->save_access_token($access_token, $user['id']);

            // Preparar respuesta
            $user_data = $user;
            unset($user_data['password'], $user_data['token_verificacion']);

            $this->send_success_response([
                'access_token' => $access_token,
                'token_type' => 'Bearer',
                'expires_in' => 3600 * 24, // 24 horas
                'user' => $user_data,
                'scope' => $this->get_user_scopes($user['rol_nombre'])
            ], 'Login exitoso');
        } catch (Exception $e) {
            $this->send_error_response(500, 'server_error', 'Error interno del servidor: ' . $e->getMessage());
        }
    }

    // Registro de nuevo usuario
    public function register()
    {
        try {
            $raw_input = file_get_contents('php://input');
            $this->log_request('register', $raw_input, []);

            $input = json_decode($raw_input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->send_error_response(400, 'invalid_json', 'JSON inválido: ' . json_last_error_msg());
                return;
            }

            // Validar campos requeridos
            $required_fields = ['nombre', 'apellido', 'email', 'password'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    $this->send_error_response(400, 'missing_field', "El campo '{$field}' es requerido.");
                    return;
                }
            }

            // Validar email único
            if ($this->Usuario_model->get_user_by_email($input['email'])) {
                $this->send_error_response(409, 'email_exists', 'El email ya está registrado.');
                return;
            }

            // Validar password
            if (strlen($input['password']) < 6) {
                $this->send_error_response(400, 'weak_password', 'La contraseña debe tener al menos 6 caracteres.');
                return;
            }

            // Obtener rol de estudiante por defecto
            $student_role = $this->Rol_model->get_role_by_name('estudiante');
            if (!$student_role) {
                $this->send_error_response(500, 'config_error', 'Error de configuración del sistema.');
                return;
            }

            // Preparar datos del usuario
            $user_data = [
                'nombre' => $input['nombre'],
                'apellido' => $input['apellido'],
                'email' => $input['email'],
                'password' => $input['password'], // Se hashea en el modelo
                'rol_id' => $student_role['id'],
                'token_verificacion' => bin2hex(random_bytes(32))
            ];

            // Agregar campos opcionales
            $optional_fields = ['telefono', 'fecha_nacimiento', 'genero', 'pais', 'ciudad'];
            foreach ($optional_fields as $field) {
                if (isset($input[$field])) {
                    $user_data[$field] = $input[$field];
                }
            }

            $user_id = $this->Usuario_model->create_user($user_data);

            if ($user_id) {
                // Enviar notificación de bienvenida
                $this->Notificacion_model->create_notification(
                    $user_id,
                    '¡Bienvenido al LMS!',
                    'Tu cuenta ha sido creada exitosamente. Comienza explorando nuestros cursos.',
                    'success',
                    '/courses'
                );

                $this->send_success_response([
                    'user_id' => $user_id,
                    'message' => 'Usuario registrado exitosamente',
                    'verification_required' => !empty($user_data['token_verificacion'])
                ], 'Registro exitoso');
            } else {
                $this->send_error_response(500, 'registration_failed', 'Error al registrar usuario.');
            }
        } catch (Exception $e) {
            $this->send_error_response(500, 'server_error', 'Error interno del servidor: ' . $e->getMessage());
        }
    }

    // Refresh token
    public function refresh_token()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        $raw_input = file_get_contents('php://input');
        $this->log_request('refresh_token', $raw_input, []);

        // Usar OAuth2 para refresh token
        $this->server->handleTokenRequest($request, $response)->send();
    }

    // Logout - invalidar token
    public function logout()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('logout', '', $token);

        // Invalidar token
        $access_token = $this->get_bearer_token();
        if ($access_token) {
            $this->invalidate_access_token($access_token);
        }

        $this->send_success_response([], 'Logout exitoso');
    }

    // Verificar email
    public function verify_email($verification_token)
    {
        try {
            $this->log_request('verify_email', $verification_token, []);

            $this->db->where('token_verificacion', $verification_token);
            $user = $this->db->get('usuarios')->row_array();

            if (!$user) {
                $this->send_error_response(404, 'invalid_token', 'Token de verificación inválido.');
                return;
            }

            // Actualizar usuario como verificado
            $this->db->where('id', $user['id']);
            $this->db->update('usuarios', [
                'email_verificado' => 1,
                'token_verificacion' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->send_success_response([], 'Email verificado exitosamente');
        } catch (Exception $e) {
            $this->send_error_response(500, 'server_error', 'Error interno del servidor: ' . $e->getMessage());
        }
    }

    // =====================================================
    // ENDPOINTS DE USUARIOS
    // =====================================================

    // Obtener perfil del usuario autenticado
    public function api_profile()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_profile', '', $token);

        $user_id = $token['user_id'];
        $user = $this->Usuario_model->get_user_by_id($user_id);

        if (!$user) {
            $this->send_error_response(404, 'user_not_found', 'Usuario no encontrado.');
            return;
        }

        // Remover información sensible
        unset($user['password'], $user['token_verificacion']);

        // Agregar estadísticas del usuario
        $user['estadisticas'] = $this->get_user_statistics($user_id);

        $this->send_success_response($user);
    }

    // Actualizar perfil del usuario
    public function update_profile()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('update_profile', $raw_input, $token);

        $user_id = $token['user_id'];
        $input = json_decode($raw_input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->send_error_response(400, 'invalid_json', 'JSON inválido: ' . json_last_error_msg());
            return;
        }

        // Validar email único si se está cambiando
        if (isset($input['email'])) {
            $existing_user = $this->Usuario_model->get_user_by_email($input['email']);
            if ($existing_user && $existing_user['id'] != $user_id) {
                $this->send_error_response(409, 'email_exists', 'El email ya está en uso.');
                return;
            }
        }

        // Campos que se pueden actualizar
        $allowed_fields = ['nombre', 'apellido', 'email', 'telefono', 'fecha_nacimiento', 'genero', 'bio', 'direccion', 'pais', 'ciudad', 'codigo_postal'];
        $update_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $update_data[$field] = $input[$field];
            }
        }

        if (empty($update_data)) {
            $this->send_error_response(400, 'no_data', 'No hay datos para actualizar.');
            return;
        }

        $result = $this->Usuario_model->update_user($user_id, $update_data);

        if ($result) {
            $this->send_success_response([], 'Perfil actualizado exitosamente.');
        } else {
            $this->send_error_response(500, 'update_failed', 'Error al actualizar el perfil.');
        }
    }

    // Cambiar contraseña
    public function change_password()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('change_password', '[PASSWORD HIDDEN]', $token);

        $input = json_decode($raw_input, true);
        $user_id = $token['user_id'];

        $current_password = $input['current_password'] ?? null;
        $new_password = $input['new_password'] ?? null;
        $confirm_password = $input['confirm_password'] ?? null;

        if (!$current_password || !$new_password || !$confirm_password) {
            $this->send_error_response(400, 'missing_fields', 'Todos los campos de contraseña son requeridos.');
            return;
        }

        if ($new_password !== $confirm_password) {
            $this->send_error_response(400, 'password_mismatch', 'Las contraseñas no coinciden.');
            return;
        }

        if (strlen($new_password) < 6) {
            $this->send_error_response(400, 'weak_password', 'La nueva contraseña debe tener al menos 6 caracteres.');
            return;
        }

        // Verificar contraseña actual
        if (!$this->Usuario_model->verify_password($user_id, $current_password)) {
            $this->send_error_response(401, 'invalid_current_password', 'La contraseña actual es incorrecta.');
            return;
        }

        // Actualizar contraseña
        $result = $this->Usuario_model->update_user($user_id, ['password' => $new_password]);

        if ($result) {
            $this->send_success_response([], 'Contraseña actualizada exitosamente.');
        } else {
            $this->send_error_response(500, 'update_failed', 'Error al actualizar la contraseña.');
        }
    }

    // =====================================================
    // ENDPOINTS DE CURSOS EXTENDIDOS
    // =====================================================

    // Obtener todos los cursos disponibles con filtros avanzados
    public function api_courses()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_courses', '', $token);

        // Parámetros de filtrado
        $filters = [
            'categoria_id' => $this->input->get('categoria_id'),
            'nivel' => $this->input->get('nivel'),
            'destacado' => $this->input->get('destacado'),
            'search' => $this->input->get('search'),
            'instructor_id' => $this->input->get('instructor_id'),
            'precio_min' => $this->input->get('precio_min'),
            'precio_max' => $this->input->get('precio_max'),
            'duracion_min' => $this->input->get('duracion_min'),
            'duracion_max' => $this->input->get('duracion_max'),
            'calificacion_min' => $this->input->get('calificacion_min')
        ];

        // Parámetros de paginación
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 20;
        $offset = ($page - 1) * $limit;

        // Orden
        $sort_by = $this->input->get('sort_by') ?: 'created_at';
        $sort_order = $this->input->get('sort_order') ?: 'DESC';

        $filters = array_filter($filters, function ($value) {
            return $value !== null && $value !== '';
        });

        $courses = $this->Curso_model->get_courses($filters, $limit, $offset, $sort_by, $sort_order);
        $total_courses = $this->Curso_model->count_courses($filters);

        $this->send_success_response([
            'data' => $courses,
            'pagination' => [
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => $total_courses,
                'pages' => ceil($total_courses / $limit)
            ],
            'filters_applied' => $filters
        ]);
    }

    // Obtener categorías
    public function api_categories()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_categories', '', $token);

        $include_tree = $this->input->get('tree') === 'true';

        if ($include_tree) {
            $categories = $this->Categoria_model->get_categories_tree();
        } else {
            $categories = $this->Categoria_model->get_all_categories();
        }

        $this->send_success_response($categories);
    }

    // Obtener detalles de curso con información extendida
    public function api_course_detail($course_id)
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_course_detail', '', $token);

        $course = $this->Curso_model->get_course_detail($course_id);

        if (!$course) {
            $this->send_error_response(404, 'course_not_found', 'Curso no encontrado.');
            return;
        }

        $user_id = $token['user_id'];

        // Verificar si el usuario está inscrito
        $course['user_enrolled'] = $this->Inscripcion_model->is_enrolled($user_id, $course_id);

        // Si está inscrito, obtener su progreso
        if ($course['user_enrolled']) {
            $course['user_progress'] = $this->Progreso_model->get_course_progress($user_id, $course_id);
        }

        // Obtener calificaciones del curso
        $course['ratings'] = $this->Calificacion_model->get_course_ratings($course_id, 5);
        $course['rating_stats'] = $this->Calificacion_model->get_rating_stats($course_id);

        // Verificar si el usuario ya calificó
        $course['user_rating'] = $this->get_user_course_rating($user_id, $course_id);

        // Cursos relacionados
        $course['related_courses'] = $this->get_related_courses($course_id, $course['categoria_id'], 4);

        $this->send_success_response($course);
    }

    // =====================================================
    // ENDPOINTS DE LECCIONES
    // =====================================================

    // Obtener lección específica con contenido
    public function api_lesson($lesson_id)
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_lesson', '', $token);

        $user_id = $token['user_id'];
        $lesson = $this->Leccion_model->get_lesson_by_id($lesson_id);

        if (!$lesson) {
            $this->send_error_response(404, 'lesson_not_found', 'Lección no encontrada.');
            return;
        }

        // Verificar si el usuario está inscrito en el curso
        if (!$this->Inscripcion_model->is_enrolled($user_id, $lesson['curso_id'])) {
            $this->send_error_response(403, 'not_enrolled', 'No estás inscrito en este curso.');
            return;
        }

        // Obtener progreso de la lección
        $lesson['user_progress'] = $this->get_lesson_progress($user_id, $lesson_id);

        // Obtener lecciones siguiente y anterior
        $lesson['next_lesson'] = $this->Leccion_model->get_next_lesson($lesson_id);
        $lesson['previous_lesson'] = $this->Leccion_model->get_previous_lesson($lesson_id);

        $this->send_success_response($lesson);
    }

    // =====================================================
    // ENDPOINTS DE NOTIFICACIONES
    // =====================================================

    // Obtener notificaciones del usuario
    public function api_notifications()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_notifications', '', $token);

        $user_id = $token['user_id'];
        $unread_only = $this->input->get('unread_only') === 'true';
        $limit = $this->input->get('limit') ?: 50;
        $offset = $this->input->get('offset') ?: 0;

        $notifications = $this->Notificacion_model->get_user_notifications($user_id, $unread_only, $limit, $offset);
        $unread_count = $this->Notificacion_model->get_unread_count($user_id);

        $this->send_success_response([
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    }

    // Marcar notificación como leída
    public function mark_notification_read($notification_id)
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('mark_notification_read', '', $token);

        $user_id = $token['user_id'];
        $result = $this->Notificacion_model->mark_as_read($notification_id, $user_id);

        if ($result) {
            $this->send_success_response([], 'Notificación marcada como leída.');
        } else {
            $this->send_error_response(500, 'update_failed', 'Error al actualizar la notificación.');
        }
    }

    // Marcar todas las notificaciones como leídas
    public function mark_all_notifications_read()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('mark_all_notifications_read', '', $token);

        $user_id = $token['user_id'];
        $result = $this->Notificacion_model->mark_all_as_read($user_id);

        if ($result) {
            $this->send_success_response([], 'Todas las notificaciones marcadas como leídas.');
        } else {
            $this->send_error_response(500, 'update_failed', 'Error al actualizar las notificaciones.');
        }
    }

    // =====================================================
    // ENDPOINTS DE FOROS
    // =====================================================

    // Obtener foros de un curso
    public function api_course_forums($course_id)
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_course_forums', '', $token);

        $user_id = $token['user_id'];

        // Verificar inscripción
        if (!$this->Inscripcion_model->is_enrolled($user_id, $course_id)) {
            $this->send_error_response(403, 'not_enrolled', 'No estás inscrito en este curso.');
            return;
        }

        $forums = $this->Foro_model->get_course_forums($course_id);
        $this->send_success_response($forums);
    }

    // Obtener posts de un foro
    public function api_forum_posts($forum_id)
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_forum_posts', '', $token);

        $limit = $this->input->get('limit') ?: 20;
        $offset = $this->input->get('offset') ?: 0;

        $posts = $this->Foro_model->get_forum_posts($forum_id, $limit, $offset);
        $this->send_success_response($posts);
    }

    // Crear post en foro
    public function create_forum_post()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('create_forum_post', $raw_input, $token);

        $input = json_decode($raw_input, true);
        $user_id = $token['user_id'];

        $forum_id = $input['forum_id'] ?? null;
        $title = $input['title'] ?? null;
        $content = $input['content'] ?? null;
        $parent_id = $input['parent_id'] ?? null;

        if (!$forum_id || !$content) {
            $this->send_error_response(400, 'missing_fields', 'Forum ID y contenido son requeridos.');
            return;
        }

        $post_id = $this->Foro_model->create_post($forum_id, $user_id, $title, $content, $parent_id);

        if ($post_id) {
            $this->send_success_response(['post_id' => $post_id], 'Post creado exitosamente.');
        } else {
            $this->send_error_response(500, 'creation_failed', 'Error al crear el post.');
        }
    }

    // =====================================================
    // ENDPOINTS DE DASHBOARD Y ESTADÍSTICAS
    // =====================================================

    // Dashboard del usuario
    public function api_dashboard()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_dashboard', '', $token);

        $user_id = $token['user_id'];
        $user = $this->Usuario_model->get_user_by_id($user_id);

        $dashboard_data = [
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'rol' => $user['rol_nombre']
            ],
            'statistics' => $this->get_user_statistics($user_id),
            'recent_courses' => $this->get_user_recent_courses($user_id, 5),
            'recent_notifications' => $this->Notificacion_model->get_user_notifications($user_id, false, 5),
            'upcoming_deadlines' => $this->get_upcoming_deadlines($user_id),
            'recommended_courses' => $this->get_recommended_courses($user_id, 5)
        ];

        $this->send_success_response($dashboard_data);
    }

    // =====================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =====================================================

    private function generate_access_token($user)
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['rol_nombre'],
            'iat' => time(),
            'exp' => time() + (3600 * 24) // 24 horas
        ];

        // Token simple basado en hash (en producción usar JWT)
        $token = base64_encode(json_encode($payload)) . '.' . hash_hmac('sha256', json_encode($payload), 'your_secret_key_here');

        return $token;
    }

    private function save_access_token($access_token, $user_id)
    {
        $data = [
            'access_token' => $access_token,
            'client_id' => 'lms_client',
            'user_id' => $user_id,
            'expires' => date('Y-m-d H:i:s', time() + (3600 * 24)),
            'scope' => $this->get_user_scopes_string($user_id)
        ];

        $this->db->insert('oauth_access_tokens', $data);
    }

    private function invalidate_access_token($access_token)
    {
        $this->db->where('access_token', $access_token);
        $this->db->delete('oauth_access_tokens');
    }

    private function get_bearer_token()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $matches = [];
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches);
            return isset($matches[1]) ? $matches[1] : null;
        }
        return null;
    }

    private function get_user_scopes($role_name)
    {
        $role = $this->Rol_model->get_role_by_name($role_name);
        if (!$role) return ['api1'];

        $permissions = explode(',', $role['permisos']);

        // Mapear permisos a scopes OAuth2
        $scope_mapping = [
            'all' => ['api1', 'create_course', 'enroll_course', 'manage_own_courses', 'view_students', 'grade_assignments', 'moderate_forums'],
            'create_course' => ['create_course'],
            'enroll_course' => ['enroll_course'],
            'manage_own_courses' => ['manage_own_courses'],
            'view_students' => ['view_students'],
            'grade_assignments' => ['grade_assignments'],
            'moderate_forums' => ['moderate_forums']
        ];

        $scopes = ['api1']; // Scope básico

        foreach ($permissions as $permission) {
            if (isset($scope_mapping[$permission])) {
                $scopes = array_merge($scopes, $scope_mapping[$permission]);
            }
        }

        return array_unique($scopes);
    }

    private function get_user_scopes_string($user_id)
    {
        $user = $this->Usuario_model->get_user_by_id($user_id);
        $scopes = $this->get_user_scopes($user['rol_nombre']);
        return implode(' ', $scopes);
    }

    private function get_user_statistics($user_id)
    {
        $stats = [];

        // Cursos inscritos
        $this->db->where('usuario_id', $user_id);
        $stats['total_inscripciones'] = $this->db->count_all_results('inscripciones');

        // Cursos completados
        $this->db->where('usuario_id', $user_id);
        $this->db->where('estado', 'completada');
        $stats['cursos_completados'] = $this->db->count_all_results('inscripciones');

        // Cursos en progreso
        $this->db->where('usuario_id', $user_id);
        $this->db->where('estado', 'activa');
        $this->db->where('progreso >', 0);
        $this->db->where('progreso <', 100);
        $stats['cursos_en_progreso'] = $this->db->count_all_results('inscripciones');

        // Certificados obtenidos
        $this->db->where('usuario_id', $user_id);
        $this->db->where('certificado_emitido', 1);
        $stats['certificados_obtenidos'] = $this->db->count_all_results('inscripciones');

        // Tiempo total de estudio (en horas)
        $this->db->select('SUM(tiempo_total_minutos) as total_minutos');
        $this->db->where('usuario_id', $user_id);
        $result = $this->db->get('inscripciones')->row();
        $stats['tiempo_total_horas'] = $result->total_minutos ? round($result->total_minutos / 60, 1) : 0;

        // Progreso promedio
        $this->db->select('AVG(progreso) as progreso_promedio');
        $this->db->where('usuario_id', $user_id);
        $this->db->where('estado', 'activa');
        $result = $this->db->get('inscripciones')->row();
        $stats['progreso_promedio'] = $result->progreso_promedio ? round($result->progreso_promedio, 1) : 0;

        return $stats;
    }

    private function get_user_recent_courses($user_id, $limit = 5)
    {
        $this->db->select('i.*, c.titulo, c.imagen_portada, c.duracion_horas');
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->where('i.usuario_id', $user_id);
        $this->db->order_by('i.fecha_inscripcion', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    private function get_upcoming_deadlines($user_id)
    {
        // Obtener evaluaciones próximas a vencer
        $this->db->select('e.titulo, e.tiempo_limite_minutos, c.titulo as curso_titulo, ie.fecha_inicio');
        $this->db->from('intentos_evaluacion ie');
        $this->db->join('evaluaciones e', 'ie.evaluacion_id = e.id');
        $this->db->join('cursos c', 'e.curso_id = c.id');
        $this->db->where('ie.usuario_id', $user_id);
        $this->db->where('ie.estado', 'en_progreso');
        $this->db->where('e.tiempo_limite_minutos IS NOT NULL');
        $this->db->order_by('ie.fecha_inicio', 'ASC');
        $this->db->limit(5);

        return $this->db->get()->result_array();
    }

    private function get_recommended_courses($user_id, $limit = 5)
    {
        // Obtener cursos recomendados basados en categorías de cursos inscritos
        $this->db->select('DISTINCT c.*');
        $this->db->from('cursos c');
        $this->db->join('categorias cat', 'c.categoria_id = cat.id');
        $this->db->join('inscripciones i', 'cat.id IN (
            SELECT c2.categoria_id FROM cursos c2 
            JOIN inscripciones i2 ON c2.id = i2.curso_id 
            WHERE i2.usuario_id = ' . (int)$user_id . '
        )', 'left', false);
        $this->db->where('c.estado', 'publicado');
        $this->db->where('c.id NOT IN (
            SELECT curso_id FROM inscripciones WHERE usuario_id = ' . (int)$user_id . '
        )', null, false);
        $this->db->order_by('c.destacado DESC, c.calificacion_promedio DESC, c.total_estudiantes DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    private function get_lesson_progress($user_id, $lesson_id)
    {
        $this->db->select('pl.*');
        $this->db->from('progreso_lecciones pl');
        $this->db->join('inscripciones i', 'pl.inscripcion_id = i.id');
        $this->db->where('i.usuario_id', $user_id);
        $this->db->where('pl.leccion_id', $lesson_id);

        return $this->db->get()->row_array();
    }

    private function get_user_course_rating($user_id, $course_id)
    {
        $this->db->where('usuario_id', $user_id);
        $this->db->where('curso_id', $course_id);
        return $this->db->get('calificaciones')->row_array();
    }

    private function get_related_courses($course_id, $category_id, $limit = 4)
    {
        $this->db->select('*');
        $this->db->from('cursos');
        $this->db->where('categoria_id', $category_id);
        $this->db->where('id !=', $course_id);
        $this->db->where('estado', 'publicado');
        $this->db->order_by('calificacion_promedio', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    // =====================================================
    // MÉTODOS DE RESPUESTA ESTANDARIZADOS
    // =====================================================

    private function send_success_response($data = [], $message = 'Operación exitosa', $status_code = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ];

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status_code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function send_error_response($status_code, $error_code, $message, $details = null)
    {
        $response = [
            'status' => 'error',
            'error' => $error_code,
            'error_description' => $message,
            'timestamp' => date('c')
        ];

        if ($details) {
            $response['details'] = $details;
        }

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status_code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // =====================================================
    // MÉTODOS HEREDADOS DEL CONTROLADOR ORIGINAL
    // =====================================================

    // Inscribirse en un curso
    public function enroll_course()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('enroll_course', $raw_input, $token);

        $required_scope = 'enroll_course';
        if (!isset($token['scope']) || strpos($token['scope'], $required_scope) === false) {
            $this->send_error_response(403, 'insufficient_scope', 'El token no tiene el scope necesario para inscribirse.');
            return;
        }

        $input = json_decode($raw_input, true);
        $course_id = $input['course_id'] ?? null;
        $metodo_pago = $input['metodo_pago'] ?? 'gratuito';
        $monto_pagado = $input['monto_pagado'] ?? 0.00;

        if (!$course_id) {
            $this->send_error_response(400, 'invalid_request', 'El campo course_id es requerido.');
            return;
        }

        $user_id = $token['user_id'];

        // Verificar si el curso existe
        $course = $this->Curso_model->get_course_detail($course_id);
        if (!$course) {
            $this->send_error_response(404, 'course_not_found', 'Curso no encontrado.');
            return;
        }

        // Verificar si ya está inscrito
        if ($this->Inscripcion_model->is_enrolled($user_id, $course_id)) {
            $this->send_error_response(409, 'already_enrolled', 'Ya estás inscrito en este curso.');
            return;
        }

        // Verificar límite de estudiantes
        if ($course['max_estudiantes'] && $course['total_estudiantes'] >= $course['max_estudiantes']) {
            $this->send_error_response(409, 'course_full', 'El curso ha alcanzado su límite de estudiantes.');
            return;
        }

        $this->db->trans_start();

        // Si el curso tiene precio, crear transacción
        if ($course['precio'] > 0 && $metodo_pago !== 'gratuito') {
            $transaction_id = $this->Transaccion_model->create_transaction(
                $user_id,
                $course_id,
                $monto_pagado ?: $course['precio'],
                $course['moneda'],
                $metodo_pago
            );

            if (!$transaction_id) {
                $this->db->trans_rollback();
                $this->send_error_response(500, 'payment_error', 'Error al procesar el pago.');
                return;
            }

            // En un caso real, aquí se procesaría el pago con la pasarela
            // Por ahora, marcamos como completado
            $this->Transaccion_model->update_transaction_status($transaction_id, 'completado');
        }

        $enrollment_id = $this->Inscripcion_model->enroll_user($user_id, $course_id, $metodo_pago, $monto_pagado);

        if ($enrollment_id) {
            // Crear notificación
            $this->Notificacion_model->notify_course_enrollment($user_id, $course['titulo'], $course_id);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->send_error_response(500, 'enrollment_error', 'Error al procesar la inscripción.');
                return;
            }

            $this->send_success_response([
                'enrollment_id' => $enrollment_id,
                'course_title' => $course['titulo']
            ], 'Inscripción exitosa.');
        } else {
            $this->db->trans_rollback();
            $this->send_error_response(500, 'server_error', 'Error al procesar la inscripción.');
        }
    }

    // Obtener cursos del usuario
    public function api_my_courses()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_my_courses', '', $token);

        $user_id = $token['user_id'];
        $status = $this->input->get('status'); // activa, completada, cancelada
        $page = $this->input->get('page') ?: 1;
        $limit = $this->input->get('limit') ?: 20;

        $courses = $this->Inscripcion_model->get_user_courses($user_id, $status, $limit, ($page - 1) * $limit);

        // Agregar progreso detallado para cada curso
        foreach ($courses as &$course) {
            $course['detailed_progress'] = $this->Progreso_model->get_course_progress($user_id, $course['curso_id']);
        }

        $this->send_success_response($courses);
    }

    // Marcar lección como completada
    public function complete_lesson()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('complete_lesson', $raw_input, $token);

        $input = json_decode($raw_input, true);
        $lesson_id = $input['lesson_id'] ?? null;
        $tiempo_visto = $input['tiempo_visto_minutos'] ?? 0;

        if (!$lesson_id) {
            $this->send_error_response(400, 'invalid_request', 'El campo lesson_id es requerido.');
            return;
        }

        $user_id = $token['user_id'];

        // Verificar que la lección existe y el usuario está inscrito
        $lesson = $this->Leccion_model->get_lesson_by_id($lesson_id);
        if (!$lesson) {
            $this->send_error_response(404, 'lesson_not_found', 'Lección no encontrada.');
            return;
        }

        if (!$this->Inscripcion_model->is_enrolled($user_id, $lesson['curso_id'])) {
            $this->send_error_response(403, 'not_enrolled', 'No estás inscrito en este curso.');
            return;
        }

        $result = $this->Progreso_model->mark_lesson_complete($user_id, $lesson_id, $tiempo_visto);

        if ($result) {
            // Verificar si se completó el curso
            $course_progress = $this->Progreso_model->get_course_progress($user_id, $lesson['curso_id']);

            if ($course_progress['progress_percentage'] >= 100) {
                // Notificar curso completado
                $course = $this->Curso_model->get_course_detail($lesson['curso_id']);
                $this->Notificacion_model->notify_course_completed($user_id, $course['titulo'], $lesson['curso_id']);
            } else {
                // Notificar lección completada
                $this->Notificacion_model->notify_lesson_completed($user_id, $lesson['titulo'], $lesson['curso_id']);
            }

            $this->send_success_response([
                'lesson_completed' => true,
                'course_progress' => $course_progress['progress_percentage']
            ], 'Lección marcada como completada.');
        } else {
            $this->send_error_response(500, 'server_error', 'Error al actualizar el progreso.');
        }
    }

    // Obtener progreso de un curso
    public function api_course_progress($course_id)
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $this->log_request('api_course_progress', '', $token);

        $user_id = $token['user_id'];

        // Verificar inscripción
        if (!$this->Inscripcion_model->is_enrolled($user_id, $course_id)) {
            $this->send_error_response(403, 'not_enrolled', 'No estás inscrito en este curso.');
            return;
        }

        $progress = $this->Progreso_model->get_course_progress($user_id, $course_id);

        if ($progress) {
            $this->send_success_response($progress);
        } else {
            $this->send_error_response(404, 'progress_not_found', 'Progreso no encontrado.');
        }
    }

    // Crear nuevo curso (solo instructores)
    public function create_course()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('create_course', $raw_input, $token);

        // Verificar scope de instructor
        $required_scope = 'create_course';
        if (!isset($token['scope']) || strpos($token['scope'], $required_scope) === false) {
            $this->send_error_response(403, 'insufficient_scope', 'El token no tiene el scope necesario para crear cursos.');
            return;
        }

        $input = json_decode($raw_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->send_error_response(400, 'invalid_json', 'JSON inválido: ' . json_last_error_msg());
            return;
        }

        $errors = $this->validate_course_input($input);
        if (!empty($errors)) {
            $this->send_error_response(400, 'invalid_request', implode(', ', $errors));
            return;
        }

        $input['instructor_id'] = $token['user_id'];
        $course_id = $this->Curso_model->create_course($input);

        if ($course_id) {
            $this->send_success_response([
                'course_id' => $course_id,
                'slug' => $input['slug'] ?? null
            ], 'Curso creado exitosamente.', 201);
        } else {
            $this->send_error_response(500, 'server_error', 'Error al crear el curso.');
        }
    }

    // Iniciar evaluación
    public function start_evaluation()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('start_evaluation', $raw_input, $token);

        $input = json_decode($raw_input, true);
        $evaluation_id = $input['evaluation_id'] ?? null;

        if (!$evaluation_id) {
            $this->send_error_response(400, 'invalid_request', 'El campo evaluation_id es requerido.');
            return;
        }

        $user_id = $token['user_id'];
        $attempt_id = $this->Evaluacion_model->start_evaluation($user_id, $evaluation_id);

        if ($attempt_id) {
            $questions = $this->Evaluacion_model->get_evaluation_questions($evaluation_id);
            $evaluation = $this->Evaluacion_model->get_evaluation($evaluation_id);

            $this->send_success_response([
                'attempt_id' => $attempt_id,
                'evaluation' => $evaluation,
                'questions' => $questions,
                'start_time' => date('c')
            ], 'Evaluación iniciada exitosamente.');
        } else {
            $this->send_error_response(500, 'server_error', 'Error al iniciar la evaluación.');
        }
    }

    // Enviar respuestas de evaluación
    public function submit_evaluation()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('submit_evaluation', $raw_input, $token);

        $input = json_decode($raw_input, true);
        $attempt_id = $input['attempt_id'] ?? null;
        $answers = $input['answers'] ?? [];

        if (!$attempt_id || empty($answers)) {
            $this->send_error_response(400, 'invalid_request', 'Los campos attempt_id y answers son requeridos.');
            return;
        }

        $result = $this->Evaluacion_model->submit_evaluation($attempt_id, $answers);

        if ($result) {
            // Crear notificación de calificación
            $user_id = $token['user_id'];
            $this->Notificacion_model->notify_assignment_graded(
                $user_id,
                'Evaluación',
                $result['percentage'],
                0 // course_id se podría obtener del attempt
            );

            $this->send_success_response([
                'score' => $result['score'],
                'max_score' => $result['max_score'],
                'percentage' => $result['percentage'],
                'passed' => $result['passed'],
                'submit_time' => date('c')
            ], 'Evaluación enviada exitosamente.');
        } else {
            $this->send_error_response(500, 'server_error', 'Error al procesar la evaluación.');
        }
    }

    // Generar certificado
    public function generate_certificate()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('generate_certificate', $raw_input, $token);

        $input = json_decode($raw_input, true);
        $course_id = $input['course_id'] ?? null;

        if (!$course_id) {
            $this->send_error_response(400, 'invalid_request', 'El campo course_id es requerido.');
            return;
        }

        $user_id = $token['user_id'];
        $certificate = $this->Certificado_model->generate_certificate($user_id, $course_id);

        if ($certificate) {
            // Notificar certificado disponible
            $course = $this->Curso_model->get_course_detail($course_id);
            $this->Notificacion_model->notify_certificate_available($user_id, $course['titulo'], $certificate['id']);

            $this->send_success_response($certificate, 'Certificado generado exitosamente.', 201);
        } else {
            $this->send_error_response(400, 'requirements_not_met', 'No cumples los requisitos para obtener el certificado.');
        }
    }

    // Calificar curso
    public function rate_course()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('rate_course', $raw_input, $token);

        $input = json_decode($raw_input, true);
        $course_id = $input['course_id'] ?? null;
        $rating = $input['rating'] ?? null;
        $comment = $input['comment'] ?? '';

        if (!$course_id || !$rating || $rating < 1 || $rating > 5) {
            $this->send_error_response(400, 'invalid_request', 'Los campos course_id y rating (1-5) son requeridos.');
            return;
        }

        $user_id = $token['user_id'];

        // Verificar inscripción
        if (!$this->Inscripcion_model->is_enrolled($user_id, $course_id)) {
            $this->send_error_response(403, 'not_enrolled', 'Debes estar inscrito en el curso para calificarlo.');
            return;
        }

        $result = $this->Calificacion_model->rate_course($user_id, $course_id, $rating, $comment);

        if ($result) {
            $this->send_success_response([
                'rating' => $rating,
                'comment' => $comment
            ], 'Calificación enviada exitosamente.');
        } else {
            $this->send_error_response(500, 'server_error', 'Error al enviar la calificación.');
        }
    }

    // =====================================================
    // MÉTODOS DE VALIDACIÓN
    // =====================================================

    private function validate_course_input($input)
    {
        $errors = [];

        $required_fields = ['titulo', 'categoria_id', 'descripcion_corta'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                $errors[] = "El campo '{$field}' es obligatorio.";
            }
        }

        if (isset($input['precio']) && !is_numeric($input['precio'])) {
            $errors[] = "El campo 'precio' debe ser numérico.";
        }

        if (isset($input['duracion_horas']) && !is_numeric($input['duracion_horas'])) {
            $errors[] = "El campo 'duracion_horas' debe ser numérico.";
        }

        if (isset($input['max_estudiantes']) && (!is_numeric($input['max_estudiantes']) || $input['max_estudiantes'] < 0)) {
            $errors[] = "El campo 'max_estudiantes' debe ser un número positivo.";
        }

        return $errors;
    }

    private function log_request($endpoint, $request_payload, $token_data)
    {
        $this->Api_log_model->insert_log($endpoint, $request_payload, $token_data);
    }

    // Método de prueba
    public function test()
    {
        $this->send_success_response(['message' => 'LMS API está funcionando correctamente', 'version' => '2.0'], 'Test exitoso');
    }
}
