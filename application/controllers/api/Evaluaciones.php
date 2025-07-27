<?php
// application/controllers/api/Evaluaciones.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Evaluaciones para la API
 */
class Evaluaciones extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Evaluacion_model');
        $this->load->model('Intento_evaluacion_model');
        $this->load->model('Inscripcion_model');
        $this->load->model('Pregunta_model');
    }

    /**
     * Obtener evaluaciones pendientes del usuario
     * GET /api/evaluaciones/pendientes
     */
    public function pendientes()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $curso_id = $this->input->get('curso_id');

            $evaluaciones = $this->Evaluacion_model->get_evaluaciones_pendientes($usuario_id, $curso_id);

            $this->response_success($evaluaciones, 'Evaluaciones pendientes obtenidas exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener evaluaciones pendientes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener evaluaciones por curso
     * GET /api/evaluaciones/curso/{curso_id}
     */
    public function por_curso($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $incluir_progreso = $this->input->get('incluir_progreso') === 'true';

            // Verificar acceso al curso
            $inscripcion = $this->Inscripcion_model->verificar_inscripcion($usuario_id, $curso_id);
            if (!$inscripcion) {
                $this->response_error('No estás inscrito en este curso', 403);
            }

            $evaluaciones = $this->Evaluacion_model->get_evaluaciones_por_curso($curso_id, $usuario_id, $incluir_progreso);

            $this->response_success($evaluaciones, 'Evaluaciones del curso obtenidas exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener evaluaciones del curso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener evaluación específica
     * GET /api/evaluaciones/{evaluacion_id}
     */
    public function show($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $evaluacion = $this->Evaluacion_model->get_evaluacion_completa($evaluacion_id, $usuario_id);

            if (!$evaluacion) {
                $this->response_error('Evaluación no encontrada', 404);
            }

            // Verificar acceso
            if (!$this->Evaluacion_model->verificar_acceso($evaluacion_id, $usuario_id)) {
                $this->response_error('No tienes acceso a esta evaluación', 403);
            }

            $this->response_success($evaluacion, 'Evaluación obtenida exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener evaluación: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Iniciar un intento de evaluación
     * POST /api/evaluaciones/{evaluacion_id}/iniciar
     */
    public function iniciar($evaluacion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];

            // Verificar que la evaluación existe y el usuario tiene acceso
            $evaluacion = $this->Evaluacion_model->get_evaluacion($evaluacion_id);
            if (!$evaluacion) {
                $this->response_error('Evaluación no encontrada', 404);
            }

            if (!$this->Evaluacion_model->verificar_acceso($evaluacion_id, $usuario_id)) {
                $this->response_error('No tienes acceso a esta evaluación', 403);
            }

            // Verificar si puede tomar la evaluación
            $puede_tomar = $this->Evaluacion_model->puede_tomar_evaluacion($evaluacion_id, $usuario_id);
            if (!$puede_tomar['puede_tomar']) {
                $this->response_error($puede_tomar['motivo'], 400);
            }

            // Crear nuevo intento
            $intento_id = $this->Intento_evaluacion_model->crear_intento($evaluacion_id, $usuario_id);

            if (!$intento_id) {
                $this->response_error('Error al iniciar evaluación', 500);
            }

            // Obtener el intento creado con las preguntas
            $intento = $this->Intento_evaluacion_model->get_intento_con_preguntas($intento_id);

            $this->response_success($intento, 'Evaluación iniciada exitosamente', 201);
        } catch (Exception $e) {
            $this->response_error('Error al iniciar evaluación: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Enviar respuestas de evaluación
     * POST /api/evaluaciones/{evaluacion_id}/enviar
     */
    public function enviar($evaluacion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input(['respuestas']);
            $usuario_id = $this->user_data['id'];

            // Buscar intento en progreso
            $intento = $this->Intento_evaluacion_model->get_intento_en_progreso($evaluacion_id, $usuario_id);
            if (!$intento) {
                $this->response_error('No hay un intento en progreso para esta evaluación', 400);
            }

            // Procesar respuestas y calcular puntuación
            $resultado = $this->Intento_evaluacion_model->procesar_respuestas($intento['id'], $input_data['respuestas']);

            if (!$resultado) {
                $this->response_error('Error al procesar respuestas', 500);
            }

            // Obtener resultado completo
            $resultado_completo = $this->Intento_evaluacion_model->get_resultado_completo($intento['id']);

            $this->response_success($resultado_completo, 'Respuestas enviadas exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al enviar respuestas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener resultados de evaluación
     * GET /api/evaluaciones/{evaluacion_id}/resultados
     */
    public function resultados($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->input->get('usuario_id') ?: $this->user_data['id'];

            // Verificar permisos
            if ($usuario_id != $this->user_data['id'] && $this->user_data['rol'] !== 'administrador') {
                $this->response_error('No tienes permisos para ver estos resultados', 403);
            }

            $resultados = $this->Intento_evaluacion_model->get_resultados_evaluacion($evaluacion_id, $usuario_id);

            $this->response_success($resultados, 'Resultados obtenidos exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener resultados: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener historial de intentos
     * GET /api/evaluaciones/intentos
     */
    public function intentos()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $evaluacion_id = $this->input->get('evaluacion_id');

            $intentos = $this->Intento_evaluacion_model->get_historial_intentos($usuario_id, $evaluacion_id);

            $this->response_success($intentos, 'Historial de intentos obtenido exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verificar si puede tomar evaluación
     * GET /api/evaluaciones/{evaluacion_id}/verificar
     */
    public function verificar($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $verificacion = $this->Evaluacion_model->puede_tomar_evaluacion($evaluacion_id, $usuario_id);

            $this->response_success($verificacion, 'Verificación completada');
        } catch (Exception $e) {
            $this->response_error('Error al verificar disponibilidad: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Guardar progreso temporal
     * POST /api/evaluaciones/{evaluacion_id}/guardar-progreso
     */
    public function guardar_progreso($evaluacion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input(['respuestas']);
            $usuario_id = $this->user_data['id'];

            // Buscar intento en progreso
            $intento = $this->Intento_evaluacion_model->get_intento_en_progreso($evaluacion_id, $usuario_id);
            if (!$intento) {
                $this->response_error('No hay un intento en progreso', 400);
            }

            $resultado = $this->Intento_evaluacion_model->guardar_progreso($intento['id'], $input_data['respuestas']);

            if ($resultado) {
                $this->response_success(null, 'Progreso guardado exitosamente');
            } else {
                $this->response_error('Error al guardar progreso', 500);
            }
        } catch (Exception $e) {
            $this->response_error('Error al guardar progreso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Recuperar progreso guardado
     * GET /api/evaluaciones/{evaluacion_id}/progreso
     */
    public function progreso($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];

            $intento = $this->Intento_evaluacion_model->get_intento_en_progreso($evaluacion_id, $usuario_id);
            if (!$intento) {
                $this->response_success(['respuestas' => null], 'No hay progreso guardado');
                return;
            }

            $progreso = $this->Intento_evaluacion_model->get_progreso_guardado($intento['id']);

            $this->response_success($progreso, 'Progreso recuperado exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al recuperar progreso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Abandonar evaluación en progreso
     * POST /api/evaluaciones/{evaluacion_id}/abandonar
     */
    public function abandonar($evaluacion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];

            $intento = $this->Intento_evaluacion_model->get_intento_en_progreso($evaluacion_id, $usuario_id);
            if (!$intento) {
                $this->response_error('No hay un intento en progreso', 400);
            }

            $resultado = $this->Intento_evaluacion_model->abandonar_intento($intento['id']);

            if ($resultado) {
                $this->response_success(null, 'Evaluación abandonada');
            } else {
                $this->response_error('Error al abandonar evaluación', 500);
            }
        } catch (Exception $e) {
            $this->response_error('Error al abandonar evaluación: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas de evaluaciones del usuario
     * GET /api/evaluaciones/estadisticas
     */
    public function estadisticas()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $estadisticas = $this->Evaluacion_model->get_estadisticas_usuario($usuario_id);

            $this->response_success($estadisticas, 'Estadísticas obtenidas exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear nueva evaluación (solo instructores/admin)
     * POST /api/evaluaciones
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }


        try {
            $input_data = $this->validate_json_input(['titulo', 'tipo']);

            $evaluacion_data = [
                'titulo' => $this->sanitize_input($input_data['titulo']),
                'tipo' => $input_data['tipo'],
                'curso_id' => isset($input_data['curso_id']) ? (int)$input_data['curso_id'] : null,
                'leccion_id' => isset($input_data['leccion_id']) ? (int)$input_data['leccion_id'] : null,
                'descripcion' => isset($input_data['descripcion']) ? $this->sanitize_input($input_data['descripcion']) : null,
                'tiempo_limite_minutos' => isset($input_data['tiempo_limite_minutos']) ? (int)$input_data['tiempo_limite_minutos'] : null,
                'intentos_permitidos' => isset($input_data['intentos_permitidos']) ? (int)$input_data['intentos_permitidos'] : 1,
                'nota_minima_aprobacion' => isset($input_data['nota_minima_aprobacion']) ? (float)$input_data['nota_minima_aprobacion'] : 60.00,
                'mostrar_resultados' => isset($input_data['mostrar_resultados']) ? (bool)$input_data['mostrar_resultados'] : true,
                'activa' => isset($input_data['activa']) ? (bool)$input_data['activa'] : true
            ];

            $evaluacion_id = $this->Evaluacion_model->crear_evaluacion($evaluacion_data);

            if (!$evaluacion_id) {
                $this->response_error('Error al crear evaluación', 500);
            }

            $evaluacion = $this->Evaluacion_model->get_evaluacion($evaluacion_id);
            $this->response_success($evaluacion, 'Evaluación creada exitosamente', 201);
        } catch (Exception $e) {
            $this->response_error('Error al crear evaluación: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener evaluaciones creadas por el instructor
     * GET /api/evaluaciones/instructor
     */
    public function instructor()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // DEBUG: Verificar datos del usuario
        error_log('🔍 USER DATA: ' . json_encode($this->user_data));
        error_log('🔍 ROL: ' . ($this->user_data['rol'] ?? 'NO_ROL'));
        error_log('🔍 ID: ' . ($this->user_data['id'] ?? 'NO_ID'));

        // ✅ SOLUCIÓN: Validación manual para múltiples roles
        if (!$this->user_data || !isset($this->user_data['rol'])) {
            $this->response_error('Usuario no autenticado', 401);
            return;
        }

        $rol = $this->user_data['rol'];
        $roles_permitidos = ['profesor', 'administrador', 'superadmin'];

        if (!in_array($rol, $roles_permitidos)) {
            error_log('❌ ROL NO PERMITIDO: ' . $rol . ' no está en ' . json_encode($roles_permitidos));
            $this->response_error('Permisos insuficientes', 403);
            return;
        }

        error_log('✅ VALIDACIÓN EXITOSA - ROL: ' . $rol);

        try {
            $usuario_id = $this->user_data['id'];

            if (in_array($rol, ['administrador', 'superadmin'])) {
                error_log('📋 Obteniendo TODAS las evaluaciones para administrador/superadmin');
                $evaluaciones = $this->Intento_evaluacion_model->get_todas_evaluaciones();
            } else {
                error_log('📋 Obteniendo evaluaciones por instructor: ' . $usuario_id);
                $evaluaciones = $this->Intento_evaluacion_model->get_evaluaciones_por_instructor($usuario_id);
            }

            error_log('📊 Total evaluaciones encontradas: ' . count($evaluaciones));
            $this->response_success($evaluaciones, 'Evaluaciones del instructor obtenidas exitosamente');
        } catch (Exception $e) {
            error_log('❌ Error en instructor(): ' . $e->getMessage());
            $this->response_error('Error al obtener evaluaciones del instructor: ' . $e->getMessage(), 500);
        }
    }


      /**
     * Obtener preguntas de una evaluación
     * GET /api/evaluaciones/{evaluacion_id}/preguntas
     */
    public function preguntas($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];

            // Verificar que la evaluación existe
            $evaluacion = $this->Evaluacion_model->get_evaluacion($evaluacion_id);
            if (!$evaluacion) {
                $this->response_error('Evaluación no encontrada', 404);
            }

            // Verificar acceso del usuario
            if (!$this->Evaluacion_model->verificar_acceso($evaluacion_id, $usuario_id)) {
                $this->response_error('No tienes acceso a esta evaluación', 403);
            }

            // Obtener preguntas SIN respuestas correctas (por seguridad)
            $preguntas = $this->Pregunta_model->get_preguntas_para_examen($evaluacion_id);

            $this->response_success($preguntas, 'Preguntas obtenidas exitosamente');
        } catch (Exception $e) {
            $this->response_error('Error al obtener preguntas: ' . $e->getMessage(), 500);
        }
    }
}
