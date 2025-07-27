<?php
// application/controllers/api/Lecciones.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Lecciones para la API
 */
class Lecciones extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leccion_model');
        $this->load->model('Modulo_model');
        $this->load->model('Progreso_leccion_model');
    }

    /**
     * Obtener lecciones por módulo
     * GET /api/lecciones/modulo/{modulo_id}
     */
    public function por_modulo($modulo_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        if (!$this->Modulo_model->existe($modulo_id)) {
            $this->response_error('Módulo no encontrado', 404);
        }

        $incluir_progreso = $this->input->get('incluir_progreso') === 'true';
        $usuario_id = $incluir_progreso ? $this->user_data['id'] : null;

        $lecciones = $this->Leccion_model->get_by_modulo($modulo_id);

        // Si se solicita progreso, agregarlo a cada lección
        if ($incluir_progreso && $usuario_id) {
            foreach ($lecciones as &$leccion) {
                $progreso = $this->Progreso_leccion_model->get_progreso_leccion($leccion['id'], $usuario_id);
                $leccion['progreso'] = $progreso;
            }
        }

        $this->response_success($lecciones, 'Lecciones obtenidas exitosamente');
    }

    /**
     * Obtener lecciones por curso con estructura de módulos
     * GET /api/lecciones/curso/{curso_id}
     */
    public function por_curso($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        try {
            $incluir_progreso = $this->input->get('incluir_progreso') === 'true';
            $usuario_id = $incluir_progreso ? $this->user_data['id'] : null;

            // Usar el método correcto del modelo
            $lecciones = $this->Leccion_model->get_lecciones_con_progreso($curso_id, $usuario_id);

            // Organizar lecciones por módulos
            $modulos = [];
            foreach ($lecciones as $leccion) {
                $modulo_id = $leccion['modulo_id'];
                
                if (!isset($modulos[$modulo_id])) {
                    $modulos[$modulo_id] = [
                        'id' => $modulo_id,
                        'titulo' => $leccion['modulo_titulo'],
                        'orden' => $leccion['modulo_orden'],
                        'activo' => true,
                        'curso_id' => $curso_id,
                        'lecciones' => []
                    ];
                }

                // Formatear progreso si incluir_progreso es true
                if ($incluir_progreso && $usuario_id) {
                    $leccion['progreso'] = [
                        'completada' => (bool)$leccion['completada'],
                        'tiempo_visto_minutos' => (int)($leccion['tiempo_visto_minutos'] ?: 0),
                        'ultima_posicion_segundo' => (int)($leccion['ultima_posicion_segundo'] ?: 0),
                        'fecha_completado' => $leccion['fecha_completado'] ?? null
                    ];
                }

                // Limpiar datos que no necesitamos en la respuesta
                unset($leccion['modulo_id'], $leccion['modulo_titulo'], $leccion['modulo_orden'], 
                      $leccion['completada'], $leccion['tiempo_visto_minutos'], 
                      $leccion['ultima_posicion_segundo'], $leccion['fecha_completado']);
                
                $modulos[$modulo_id]['lecciones'][] = $leccion;
            }

            // Convertir a array indexado y ordenar
            $modulos = array_values($modulos);
            usort($modulos, function($a, $b) {
                return $a['orden'] - $b['orden'];
            });

            $this->response_success($modulos, 'Estructura del curso obtenida exitosamente');

        } catch (Exception $e) {
            $this->response_error('Error al obtener lecciones: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lección específica
     * GET /api/lecciones/{leccion_id}
     */
    public function show($leccion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $leccion = $this->Leccion_model->get_leccion($leccion_id);

        if (!$leccion) {
            $this->response_error('Lección no encontrada', 404);
        }

        // Verificar acceso del usuario a la lección
        if (!$this->Leccion_model->verificar_acceso($leccion_id, $this->user_data['id'])) {
            $this->response_error('No tienes acceso a esta lección', 403);
        }

        // Agregar progreso del usuario si está autenticado
        $progreso = $this->Progreso_leccion_model->get_progreso_leccion($leccion_id, $this->user_data['id']);
        $leccion['progreso'] = $progreso;

        // Agregar navegación (lección anterior y siguiente)
        $leccion['navegacion'] = [
            'anterior' => $this->Leccion_model->get_leccion_anterior($leccion_id),
            'siguiente' => $this->Leccion_model->get_leccion_siguiente($leccion_id)
        ];

        $this->response_success($leccion, 'Lección obtenida exitosamente');
    }

    /**
     * Crear nueva lección
     * POST /api/lecciones
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo instructores y administradores pueden crear lecciones
        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['titulo', 'modulo_id', 'tipo']);

        // Verificar que el módulo existe y pertenece al instructor
        $modulo = $this->Modulo_model->get_modulo($input_data['modulo_id']);
        if (!$modulo) {
            $this->response_error('Módulo no encontrado', 404);
        }

        // Verificar permisos sobre el curso
        if ($this->user_data['rol'] !== 'administrador') {
            $this->load->model('Curso_model');
            $curso = $this->Curso_model->get_curso($modulo['curso_id']);
            if (!$curso || $curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para crear lecciones en este módulo', 403);
            }
        }

        $leccion_data = [
            'titulo' => $this->sanitize_input($input_data['titulo']),
            'modulo_id' => (int)$input_data['modulo_id'],
            'tipo' => $this->sanitize_input($input_data['tipo'])
        ];

        // Validar tipo de lección
        $tipos_validos = ['video', 'texto', 'audio', 'presentacion', 'documento', 'enlace'];
        if (!in_array($leccion_data['tipo'], $tipos_validos)) {
            $this->response_error('Tipo de lección inválido', 400);
        }

        // Campos opcionales
        $campos_opcionales = [
            'descripcion', 'contenido', 'url_recurso', 'duracion_minutos', 
            'orden', 'es_gratuita', 'slug'
        ];

        foreach ($campos_opcionales as $campo) {
            if (isset($input_data[$campo])) {
                if ($campo === 'duracion_minutos' || $campo === 'orden') {
                    $leccion_data[$campo] = (int)$input_data[$campo];
                } elseif ($campo === 'es_gratuita') {
                    $leccion_data[$campo] = (bool)$input_data[$campo];
                } else {
                    $leccion_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        $leccion_id = $this->Leccion_model->crear_leccion($leccion_data);

        if (!$leccion_id) {
            $this->response_error('Error al crear lección', 500);
        }

        $leccion = $this->Leccion_model->get_leccion($leccion_id);
        $this->response_success($leccion, 'Lección creada exitosamente', 201);
    }

    /**
     * Actualizar lección
     * PUT /api/lecciones/{leccion_id}
     */
    public function update($leccion_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $leccion = $this->Leccion_model->get_leccion($leccion_id);
        if (!$leccion) {
            $this->response_error('Lección no encontrada', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador') {
            $this->load->model('Curso_model');
            $curso = $this->Curso_model->get_curso($leccion['curso_id']);
            if (!$curso || $curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para editar esta lección', 403);
            }
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        $campos_permitidos = [
            'titulo', 'descripcion', 'contenido', 'url_recurso', 
            'tipo', 'duracion_minutos', 'orden', 'es_gratuita', 'activa'
        ];

        $update_data = [];

        foreach ($campos_permitidos as $campo) {
            if (isset($input_data[$campo])) {
                if (in_array($campo, ['duracion_minutos', 'orden'])) {
                    $update_data[$campo] = (int)$input_data[$campo];
                } elseif (in_array($campo, ['es_gratuita', 'activa'])) {
                    $update_data[$campo] = (bool)$input_data[$campo];
                } else {
                    $update_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        // Validar tipo si se está actualizando
        if (isset($update_data['tipo'])) {
            $tipos_validos = ['video', 'texto', 'audio', 'presentacion', 'documento', 'enlace'];
            if (!in_array($update_data['tipo'], $tipos_validos)) {
                $this->response_error('Tipo de lección inválido', 400);
            }
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Leccion_model->actualizar_leccion($leccion_id, $update_data)) {
            $leccion_actualizada = $this->Leccion_model->get_leccion($leccion_id);
            $this->response_success($leccion_actualizada, 'Lección actualizada exitosamente');
        } else {
            $this->response_error('Error al actualizar lección', 500);
        }
    }

    /**
     * Eliminar lección
     * DELETE /api/lecciones/{leccion_id}
     */
    public function delete($leccion_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        $leccion = $this->Leccion_model->get_leccion($leccion_id);
        if (!$leccion) {
            $this->response_error('Lección no encontrada', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador') {
            $this->load->model('Curso_model');
            $curso = $this->Curso_model->get_curso($leccion['curso_id']);
            if (!$curso || $curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para eliminar esta lección', 403);
            }
        }

        if ($this->Leccion_model->eliminar_leccion($leccion_id)) {
            $this->response_success(null, 'Lección eliminada exitosamente');
        } else {
            $this->response_error('Error al eliminar lección', 500);
        }
    }

    /**
     * Reordenar lecciones de un módulo
     * PUT /api/lecciones/reordenar/{modulo_id}
     */
    public function reordenar($modulo_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['orden_lecciones']);

        if (!is_array($input_data['orden_lecciones'])) {
            $this->response_error('orden_lecciones debe ser un array', 400);
        }

        // Verificar permisos sobre el módulo
        $modulo = $this->Modulo_model->get_modulo($modulo_id);
        if (!$modulo) {
            $this->response_error('Módulo no encontrado', 404);
        }

        if ($this->user_data['rol'] !== 'administrador') {
            $this->load->model('Curso_model');
            $curso = $this->Curso_model->get_curso($modulo['curso_id']);
            if (!$curso || $curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para reordenar lecciones en este módulo', 403);
            }
        }

        if ($this->Leccion_model->reordenar_lecciones($modulo_id, $input_data['orden_lecciones'])) {
            $lecciones = $this->Leccion_model->get_by_modulo($modulo_id);
            $this->response_success($lecciones, 'Lecciones reordenadas exitosamente');
        } else {
            $this->response_error('Error al reordenar lecciones', 500);
        }
    }

    /**
     * Buscar lecciones
     * GET /api/lecciones/buscar
     */
    public function buscar()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $termino = $this->input->get('q');
        if (empty($termino)) {
            $this->response_error('Término de búsqueda requerido', 400);
        }

        $filtros = [
            'tipo' => $this->input->get('tipo'),
            'curso_id' => $this->input->get('curso_id'),
            'es_gratuita' => $this->input->get('es_gratuita')
        ];

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros, function($value) {
            return $value !== null && $value !== '';
        });

        $lecciones = $this->Leccion_model->buscar_lecciones($termino, $filtros);

        $this->response_success($lecciones, 'Búsqueda completada exitosamente');
    }

    /**
     * Obtener estadísticas de lecciones
     * GET /api/lecciones/estadisticas
     */
    public function estadisticas()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $curso_id = $this->input->get('curso_id');
        
        // Si no es administrador, solo puede ver estadísticas de sus cursos
        if ($this->user_data['rol'] !== 'administrador' && $curso_id) {
            $this->load->model('Curso_model');
            $curso = $this->Curso_model->get_curso($curso_id);
            if (!$curso || $curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para ver estadísticas de este curso', 403);
            }
        }

        $estadisticas = $this->Leccion_model->get_estadisticas($curso_id);

        $this->response_success($estadisticas, 'Estadísticas obtenidas exitosamente');
    }

    /**
     * Marcar lección como vista/completada
     * POST /api/lecciones/{leccion_id}/completar
     */
    public function completar($leccion_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Verificar que la lección existe y el usuario tiene acceso
        if (!$this->Leccion_model->verificar_acceso($leccion_id, $this->user_data['id'])) {
            $this->response_error('No tienes acceso a esta lección', 403);
        }

        $input_data = $this->validate_json_input();

        $progreso_data = [
            'tiempo_visto_minutos' => isset($input_data['tiempo_visto_minutos']) ? (int)$input_data['tiempo_visto_minutos'] : 0,
            'ultima_posicion_segundo' => isset($input_data['ultima_posicion_segundo']) ? (int)$input_data['ultima_posicion_segundo'] : 0,
            'completada' => isset($input_data['completada']) ? (bool)$input_data['completada'] : true
        ];

        $resultado = $this->Progreso_leccion_model->actualizar_progreso(
            $leccion_id, 
            $this->user_data['id'], 
            $progreso_data
        );

        if ($resultado) {
            $this->response_success(null, 'Progreso actualizado exitosamente');
        } else {
            $this->response_error('Error al actualizar progreso', 500);
        }
    }
}
