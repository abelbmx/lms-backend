<?php
// =====================================================
// CONTROLADOR PRINCIPAL - LMS_API
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
            'Api_log_model'
        ]);
    }

    // =====================================================
    // ENDPOINT PARA TOKENS
    // =====================================================
    public function token()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        $raw_input = file_get_contents('php://input');
        $this->log_request('token', $raw_input, []);

        $this->server->handleTokenRequest($request, $response)->send();
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
        $raw_input = file_get_contents('php://input');
        $this->log_request('api_profile', $raw_input, $token);

        $user_id = $token['user_id'];
        $user = $this->Usuario_model->get_user_by_id($user_id);

        if (!$user) {
            $response->setError(404, 'user_not_found', 'Usuario no encontrado.');
            $response->send();
            exit;
        }

        // Remover información sensible
        unset($user['password'], $user['token_verificacion']);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $user
        ], JSON_UNESCAPED_UNICODE);
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
            $response->setError(400, 'invalid_json', 'JSON inválido: ' . json_last_error_msg());
            $response->send();
            exit;
        }

        $result = $this->Usuario_model->update_user($user_id, $input);

        if ($result) {
            $response->setStatusCode(200);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Perfil actualizado exitosamente.'
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al actualizar el perfil.');
        }
        $response->send();
    }

    // =====================================================
    // ENDPOINTS DE CURSOS
    // =====================================================

    // Obtener todos los cursos disponibles
    public function api_courses()
    {
        $request = Request::createFromGlobals();
        $response = new Response();

        if (!$this->server->verifyResourceRequest($request, $response)) {
            $response->send();
            exit;
        }

        $token = $this->server->getAccessTokenData($request);
        $raw_input = file_get_contents('php://input');
        $this->log_request('api_courses', $raw_input, $token);

        // Parámetros de filtrado opcionales
        $categoria_id = $this->input->get('categoria_id');
        $nivel = $this->input->get('nivel');
        $destacado = $this->input->get('destacado');
        $search = $this->input->get('search');

        $filters = array_filter([
            'categoria_id' => $categoria_id,
            'nivel' => $nivel,
            'destacado' => $destacado,
            'search' => $search
        ]);

        $courses = $this->Curso_model->get_courses($filters);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $courses,
            'count' => count($courses)
        ], JSON_UNESCAPED_UNICODE);
    }

    // Obtener detalles de un curso específico
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
            $response->setError(404, 'course_not_found', 'Curso no encontrado.');
            $response->send();
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $course
        ], JSON_UNESCAPED_UNICODE);
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
            $response->setError(403, 'insufficient_scope', 'El token no tiene el scope necesario para crear cursos.');
            $response->send();
            exit;
        }

        $input = json_decode($raw_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response->setError(400, 'invalid_json', 'JSON inválido: ' . json_last_error_msg());
            $response->send();
            exit;
        }

        $errors = $this->validate_course_input($input);
        if (!empty($errors)) {
            $response->setError(400, 'invalid_request', implode(', ', $errors));
            $response->send();
            exit;
        }

        $input['instructor_id'] = $token['user_id'];
        $course_id = $this->Curso_model->create_course($input);

        if ($course_id) {
            $response->setStatusCode(201);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Curso creado exitosamente.',
                'course_id' => $course_id
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al crear el curso.');
        }
        $response->send();
    }

    // =====================================================
    // ENDPOINTS DE INSCRIPCIONES
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
            $response->setError(403, 'insufficient_scope', 'El token no tiene el scope necesario para inscribirse.');
            $response->send();
            exit;
        }

        $input = json_decode($raw_input, true);
        $course_id = $input['course_id'] ?? null;
        $metodo_pago = $input['metodo_pago'] ?? 'gratuito';
        $monto_pagado = $input['monto_pagado'] ?? 0.00;

        if (!$course_id) {
            $response->setError(400, 'invalid_request', 'El campo course_id es requerido.');
            $response->send();
            exit;
        }

        $user_id = $token['user_id'];

        // Verificar si ya está inscrito
        if ($this->Inscripcion_model->is_enrolled($user_id, $course_id)) {
            $response->setError(409, 'already_enrolled', 'Ya estás inscrito en este curso.');
            $response->send();
            exit;
        }

        $enrollment_id = $this->Inscripcion_model->enroll_user($user_id, $course_id, $metodo_pago, $monto_pagado);

        if ($enrollment_id) {
            $response->setStatusCode(201);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Inscripción exitosa.',
                'enrollment_id' => $enrollment_id
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al procesar la inscripción.');
        }
        $response->send();
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
        $courses = $this->Inscripcion_model->get_user_courses($user_id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $courses
        ], JSON_UNESCAPED_UNICODE);
    }

    // =====================================================
    // ENDPOINTS DE PROGRESO
    // =====================================================

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
            $response->setError(400, 'invalid_request', 'El campo lesson_id es requerido.');
            $response->send();
            exit;
        }

        $user_id = $token['user_id'];
        $result = $this->Progreso_model->mark_lesson_complete($user_id, $lesson_id, $tiempo_visto);

        if ($result) {
            $response->setStatusCode(200);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Lección marcada como completada.'
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al actualizar el progreso.');
        }
        $response->send();
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
        $progress = $this->Progreso_model->get_course_progress($user_id, $course_id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $progress
        ], JSON_UNESCAPED_UNICODE);
    }

    // =====================================================
    // ENDPOINTS DE EVALUACIONES
    // =====================================================

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
            $response->setError(400, 'invalid_request', 'El campo evaluation_id es requerido.');
            $response->send();
            exit;
        }

        $user_id = $token['user_id'];
        $attempt_id = $this->Evaluacion_model->start_evaluation($user_id, $evaluation_id);

        if ($attempt_id) {
            $questions = $this->Evaluacion_model->get_evaluation_questions($evaluation_id);

            $response->setStatusCode(200);
            $response->setParameter('body', [
                'status' => 'success',
                'attempt_id' => $attempt_id,
                'questions' => $questions
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al iniciar la evaluación.');
        }
        $response->send();
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
            $response->setError(400, 'invalid_request', 'Los campos attempt_id y answers son requeridos.');
            $response->send();
            exit;
        }

        $result = $this->Evaluacion_model->submit_evaluation($attempt_id, $answers);

        if ($result) {
            $response->setStatusCode(200);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Evaluación enviada exitosamente.',
                'score' => $result['score'],
                'passed' => $result['passed']
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al procesar la evaluación.');
        }
        $response->send();
    }

    // =====================================================
    // ENDPOINTS DE CERTIFICADOS
    // =====================================================

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
            $response->setError(400, 'invalid_request', 'El campo course_id es requerido.');
            $response->send();
            exit;
        }

        $user_id = $token['user_id'];
        $certificate = $this->Certificado_model->generate_certificate($user_id, $course_id);

        if ($certificate) {
            $response->setStatusCode(201);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Certificado generado exitosamente.',
                'certificate' => $certificate
            ]);
        } else {
            $response->setError(400, 'requirements_not_met', 'No cumples los requisitos para obtener el certificado.');
        }
        $response->send();
    }

    // =====================================================
    // ENDPOINTS DE CALIFICACIONES
    // =====================================================

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
            $response->setError(400, 'invalid_request', 'Los campos course_id y rating (1-5) son requeridos.');
            $response->send();
            exit;
        }

        $user_id = $token['user_id'];
        $result = $this->Calificacion_model->rate_course($user_id, $course_id, $rating, $comment);

        if ($result) {
            $response->setStatusCode(200);
            $response->setParameter('body', [
                'status' => 'success',
                'message' => 'Calificación enviada exitosamente.'
            ]);
        } else {
            $response->setError(500, 'server_error', 'Error al enviar la calificación.');
        }
        $response->send();
    }

    // =====================================================
    // MÉTODOS PRIVADOS DE VALIDACIÓN
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

        return $errors;
    }

    private function log_request($endpoint, $request_payload, $token_data)
    {
        $this->Api_log_model->insert_log($endpoint, $request_payload, $token_data);
    }

    // Método de prueba
    public function test()
    {
        echo "LMS API controller is working correctly.";
    }
}
