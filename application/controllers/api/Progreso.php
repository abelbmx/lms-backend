<?php
// application/controllers/api/Progreso.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Progreso para la API
 */
class Progreso extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Progreso_leccion_model');
        $this->load->model('Inscripcion_model');
        $this->load->model('Curso_model');
        $this->load->model('Leccion_model');
    }

    /**
     * Obtener estadísticas generales de progreso del usuario
     * GET /api/progreso/estadisticas
     */
    public function estadisticas()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            
            // Obtener estadísticas desde el modelo
            $estadisticas = $this->Progreso_leccion_model->get_estadisticas_usuario($usuario_id);
            
            // Si no hay datos, devolver estructura por defecto
            if (!$estadisticas) {
                $estadisticas = [
                    'cursosEnProgreso' => 0,
                    'cursosCompletados' => 0,
                    'tiempoTotalEstudio' => 0,
                    'certificadosObtenidos' => 0,
                    'promedioCalificaciones' => 0,
                    'leccionesCompletadas' => 0,
                    'evaluacionesAprobadas' => 0,
                    'rachaActual' => 0,
                    'tiempoPromedioSesion' => 0
                ];
            }
            
            $this->response_success($estadisticas, 'Estadísticas obtenidas exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener resumen de progreso de todos los cursos
     * GET /api/progreso/resumen
     */
    public function resumen()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $resumen = $this->Progreso_leccion_model->get_resumen_progreso($usuario_id);
            
            $this->response_success($resumen, 'Resumen de progreso obtenido exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener resumen: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener progreso detallado de un curso
     * GET /api/progreso/curso/{curso_id}
     */
    public function curso($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            
            // Verificar que el usuario esté inscrito en el curso
            $inscripcion = $this->Inscripcion_model->verificar_inscripcion($usuario_id, $curso_id);
            if (!$inscripcion) {
                $this->response_error('No estás inscrito en este curso', 403);
            }
            
            $progreso = $this->Progreso_leccion_model->get_progreso_curso($curso_id, $usuario_id);
            
            $this->response_success($progreso, 'Progreso del curso obtenido exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener progreso del curso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar progreso de una lección
     * POST /api/progreso/leccion/{leccion_id}
     */
    public function actualizar_leccion($leccion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input();
            $usuario_id = $this->user_data['id'];
            
            // Verificar que la lección existe
            $leccion = $this->Leccion_model->get_leccion($leccion_id);
            if (!$leccion) {
                $this->response_error('Lección no encontrada', 404);
            }
            
            // Verificar acceso del usuario a la lección
            if (!$this->Leccion_model->verificar_acceso($leccion_id, $usuario_id)) {
                $this->response_error('No tienes acceso a esta lección', 403);
            }
            
            $progreso_data = [
                'tiempo_visto_minutos' => isset($input_data['tiempo_visto_minutos']) ? (int)$input_data['tiempo_visto_minutos'] : 0,
                'ultima_posicion_segundo' => isset($input_data['ultima_posicion_segundo']) ? (int)$input_data['ultima_posicion_segundo'] : 0,
                'completada' => isset($input_data['completada']) ? (bool)$input_data['completada'] : false
            ];
            
            $resultado = $this->Progreso_leccion_model->actualizar_progreso($leccion_id, $usuario_id, $progreso_data);
            
            if ($resultado) {
                // Obtener el progreso actualizado
                $progreso_actualizado = $this->Progreso_leccion_model->get_progreso_leccion($leccion_id, $usuario_id);
                $this->response_success($progreso_actualizado, 'Progreso actualizado exitosamente');
            } else {
                $this->response_error('Error al actualizar progreso', 500);
            }
            
        } catch (Exception $e) {
            $this->response_error('Error al actualizar progreso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener progreso de lecciones de un curso
     * GET /api/progreso/curso/{curso_id}/lecciones
     */
    public function lecciones($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            
            // Verificar acceso al curso
            $inscripcion = $this->Inscripcion_model->verificar_inscripcion($usuario_id, $curso_id);
            if (!$inscripcion) {
                $this->response_error('No estás inscrito en este curso', 403);
            }
            
            $progreso_lecciones = $this->Progreso_leccion_model->get_progreso_lecciones_curso($curso_id, $usuario_id);
            
            $this->response_success($progreso_lecciones, 'Progreso de lecciones obtenido exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener progreso de lecciones: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener actividad reciente del usuario
     * GET /api/progreso/actividad
     */
    public function actividad()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $limite = $this->input->get('limite') ? (int)$this->input->get('limite') : 10;
            
            $actividad = $this->Progreso_leccion_model->get_actividad_reciente($usuario_id, $limite);
            
            $this->response_success($actividad, 'Actividad reciente obtenida exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener actividad: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas de tiempo de estudio
     * GET /api/progreso/tiempo
     */
    public function tiempo()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $periodo = $this->input->get('periodo') ?: 'mes'; // semana, mes, año
            
            $estadisticas_tiempo = $this->Progreso_leccion_model->get_estadisticas_tiempo($usuario_id, $periodo);
            
            $this->response_success($estadisticas_tiempo, 'Estadísticas de tiempo obtenidas exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener estadísticas de tiempo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener ranking del usuario
     * GET /api/progreso/ranking
     */
    public function ranking()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $ranking = $this->Progreso_leccion_model->get_ranking_usuario($usuario_id);
            
            $this->response_success($ranking, 'Ranking obtenido exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener ranking: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Registrar sesión de estudio
     * POST /api/progreso/sesion
     */
    public function sesion()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input(['leccion_id', 'tiempo_minutos']);
            $usuario_id = $this->user_data['id'];
            
            $sesion_data = [
                'usuario_id' => $usuario_id,
                'leccion_id' => (int)$input_data['leccion_id'],
                'tiempo_minutos' => (int)$input_data['tiempo_minutos'],
                'fecha_sesion' => isset($input_data['fecha_sesion']) ? $input_data['fecha_sesion'] : date('Y-m-d H:i:s')
            ];
            
            $resultado = $this->Progreso_leccion_model->registrar_sesion($sesion_data);
            
            if ($resultado) {
                $this->response_success(null, 'Sesión registrada exitosamente');
            } else {
                $this->response_error('Error al registrar sesión', 500);
            }
            
        } catch (Exception $e) {
            $this->response_error('Error al registrar sesión: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener progreso comparativo
     * GET /api/progreso/comparativo
     */
    public function comparativo()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $comparativo = $this->Progreso_leccion_model->get_progreso_comparativo($usuario_id);
            
            $this->response_success($comparativo, 'Progreso comparativo obtenido exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener progreso comparativo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reiniciar progreso de un curso
     * POST /api/progreso/curso/{curso_id}/reiniciar
     */
    public function reiniciar($curso_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input(['confirmar']);
            $usuario_id = $this->user_data['id'];
            
            if (!$input_data['confirmar']) {
                $this->response_error('Debes confirmar el reinicio del progreso', 400);
            }
            
            // Verificar inscripción
            $inscripcion = $this->Inscripcion_model->verificar_inscripcion($usuario_id, $curso_id);
            if (!$inscripcion) {
                $this->response_error('No estás inscrito en este curso', 403);
            }
            
            $resultado = $this->Progreso_leccion_model->reiniciar_progreso_curso($curso_id, $usuario_id);
            
            if ($resultado) {
                $this->response_success(null, 'Progreso reiniciado exitosamente');
            } else {
                $this->response_error('Error al reiniciar progreso', 500);
            }
            
        } catch (Exception $e) {
            $this->response_error('Error al reiniciar progreso: ' . $e->getMessage(), 500);
        }
    }
}
