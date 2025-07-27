<?php
// application/controllers/api/Inscripciones.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Inscripciones para la API
 */
class Inscripciones extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Inscripcion_model');
        $this->load->model('Curso_model');
        $this->load->model('Progreso_leccion_model');
    }

    /**
     * Obtener inscripciones del usuario autenticado
     * GET /api/inscripciones/usuario
     */
    public function usuario()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            
            // Obtener filtros opcionales
            $estado = $this->input->get('estado');
            $categoria = $this->input->get('categoria');
            $nivel = $this->input->get('nivel');
            $orden = $this->input->get('orden', TRUE) ?: 'fecha_inscripcion';
            $direccion = $this->input->get('direccion', TRUE) ?: 'desc';
            
            $filtros = [
                'estado' => $estado,
                'categoria' => $categoria,
                'nivel' => $nivel,
                'orden' => $orden,
                'direccion' => $direccion
            ];
            
            // Limpiar filtros vacíos
            $filtros = array_filter($filtros, function($value) {
                return $value !== null && $value !== '';
            });
            
            $inscripciones = $this->Inscripcion_model->get_inscripciones_usuario($usuario_id, $filtros);
            
            $this->response_success($inscripciones, 'Inscripciones obtenidas exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener inscripciones: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener inscripción específica
     * GET /api/inscripciones/{id}
     */
    public function show($inscripcion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $inscripcion = $this->Inscripcion_model->get_inscripcion_detalle($inscripcion_id);
            
            if (!$inscripcion) {
                $this->response_error('Inscripción no encontrada', 404);
            }
            
            // Verificar que la inscripción pertenece al usuario o es admin
            if ($inscripcion['usuario_id'] != $this->user_data['id'] && $this->user_data['rol'] !== 'administrador') {
                $this->response_error('No tienes acceso a esta inscripción', 403);
            }
            
            $this->response_success($inscripcion, 'Inscripción obtenida exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener inscripción: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear nueva inscripción
     * POST /api/inscripciones
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input(['curso_id']);
            
            $curso_id = (int)$input_data['curso_id'];
            $usuario_id = $this->user_data['id'];
            
            // Verificar que el curso existe
            $curso = $this->Curso_model->get_curso($curso_id);
            if (!$curso) {
                $this->response_error('Curso no encontrado', 404);
            }
            
            // Verificar si ya está inscrito
            $inscripcion_existente = $this->Inscripcion_model->verificar_inscripcion($usuario_id, $curso_id);
            if ($inscripcion_existente) {
                $this->response_error('Ya estás inscrito en este curso', 400);
            }
            
            // Datos de la inscripción
            $inscripcion_data = [
                'usuario_id' => $usuario_id,
                'curso_id' => $curso_id,
                'metodo_pago' => isset($input_data['metodo_pago']) ? $input_data['metodo_pago'] : null,
                'monto_pagado' => $curso['precio'] ?? 0,
                'estado' => 'activa'
            ];
            
            $inscripcion_id = $this->Inscripcion_model->crear_inscripcion($inscripcion_data);
            
            if (!$inscripcion_id) {
                $this->response_error('Error al crear inscripción', 500);
            }
            
            $nueva_inscripcion = $this->Inscripcion_model->get_inscripcion_detalle($inscripcion_id);
            $this->response_success($nueva_inscripcion, 'Inscripción creada exitosamente', 201);
            
        } catch (Exception $e) {
            $this->response_error('Error al crear inscripción: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener progreso de una inscripción
     * GET /api/inscripciones/{id}/progreso
     */
    public function progreso($inscripcion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $inscripcion = $this->Inscripcion_model->get_inscripcion($inscripcion_id);
            
            if (!$inscripcion) {
                $this->response_error('Inscripción no encontrada', 404);
            }
            
            // Verificar permisos
            if ($inscripcion['usuario_id'] != $this->user_data['id'] && $this->user_data['rol'] !== 'administrador') {
                $this->response_error('No tienes acceso a esta inscripción', 403);
            }
            
            $progreso = $this->Inscripcion_model->get_progreso_detallado($inscripcion_id);
            
            $this->response_success($progreso, 'Progreso obtenido exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener progreso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancelar inscripción
     * PUT /api/inscripciones/{id}/cancelar
     */
    public function cancelar($inscripcion_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $input_data = $this->validate_json_input();
            
            $inscripcion = $this->Inscripcion_model->get_inscripcion($inscripcion_id);
            
            if (!$inscripcion) {
                $this->response_error('Inscripción no encontrada', 404);
            }
            
            // Verificar permisos
            if ($inscripcion['usuario_id'] != $this->user_data['id'] && $this->user_data['rol'] !== 'administrador') {
                $this->response_error('No tienes permisos para cancelar esta inscripción', 403);
            }
            
            if ($inscripcion['estado'] !== 'activa') {
                $this->response_error('Solo se pueden cancelar inscripciones activas', 400);
            }
            
            $resultado = $this->Inscripcion_model->cancelar_inscripcion($inscripcion_id, $input_data['motivo'] ?? null);
            
            if ($resultado) {
                $this->response_success(null, 'Inscripción cancelada exitosamente');
            } else {
                $this->response_error('Error al cancelar inscripción', 500);
            }
            
        } catch (Exception $e) {
            $this->response_error('Error al cancelar inscripción: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reactivar inscripción
     * PUT /api/inscripciones/{id}/reactivar
     */
    public function reactivar($inscripcion_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $inscripcion = $this->Inscripcion_model->get_inscripcion($inscripcion_id);
            
            if (!$inscripcion) {
                $this->response_error('Inscripción no encontrada', 404);
            }
            
            // Verificar permisos (solo admin puede reactivar)
            if ($this->user_data['rol'] !== 'administrador') {
                $this->response_error('No tienes permisos para reactivar inscripciones', 403);
            }
            
            if ($inscripcion['estado'] !== 'cancelada') {
                $this->response_error('Solo se pueden reactivar inscripciones canceladas', 400);
            }
            
            $resultado = $this->Inscripcion_model->reactivar_inscripcion($inscripcion_id);
            
            if ($resultado) {
                $this->response_success(null, 'Inscripción reactivada exitosamente');
            } else {
                $this->response_error('Error al reactivar inscripción', 500);
            }
            
        } catch (Exception $e) {
            $this->response_error('Error al reactivar inscripción: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Marcar curso como completado
     * POST /api/inscripciones/{id}/completar
     */
    public function completar($inscripcion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $inscripcion = $this->Inscripcion_model->get_inscripcion($inscripcion_id);
            
            if (!$inscripcion) {
                $this->response_error('Inscripción no encontrada', 404);
            }
            
            // Verificar permisos
            if ($inscripcion['usuario_id'] != $this->user_data['id'] && $this->user_data['rol'] !== 'administrador') {
                $this->response_error('No tienes permisos para completar esta inscripción', 403);
            }
            
            if ($inscripcion['estado'] !== 'activa') {
                $this->response_error('Solo se pueden completar inscripciones activas', 400);
            }
            
            $resultado = $this->Inscripcion_model->completar_curso($inscripcion_id);
            
            if ($resultado) {
                $this->response_success(null, 'Curso marcado como completado');
            } else {
                $this->response_error('Error al completar curso', 500);
            }
            
        } catch (Exception $e) {
            $this->response_error('Error al completar curso: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verificar si usuario está inscrito en un curso
     * GET /api/inscripciones/verificar/{curso_id}
     */
    public function verificar($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $inscripcion = $this->Inscripcion_model->verificar_inscripcion($usuario_id, $curso_id);
            
            $resultado = [
                'inscrito' => $inscripcion ? true : false,
                'inscripcion' => $inscripcion
            ];
            
            $this->response_success($resultado, 'Verificación completada');
            
        } catch (Exception $e) {
            $this->response_error('Error al verificar inscripción: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener estadísticas de inscripciones del usuario
     * GET /api/inscripciones/estadisticas
     */
    public function estadisticas()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $usuario_id = $this->user_data['id'];
            $estadisticas = $this->Inscripcion_model->get_estadisticas_usuario($usuario_id);
            
            $this->response_success($estadisticas, 'Estadísticas obtenidas exitosamente');
            
        } catch (Exception $e) {
            $this->response_error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}
