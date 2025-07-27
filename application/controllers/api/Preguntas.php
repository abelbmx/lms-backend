<?php
// application/controllers/api/Preguntas.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Preguntas para la API
 */
class Preguntas extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Pregunta_model');
        $this->load->model('Evaluacion_model');
    }

    /**
     * Obtener preguntas por evaluación
     * GET /api/preguntas/evaluacion/{evaluacion_id}
     */
    public function por_evaluacion($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // Verificar que la evaluación existe
        $evaluacion = $this->Evaluacion_model->get_evaluacion($evaluacion_id);
        if (!$evaluacion) {
            $this->response_error('Evaluación no encontrada', 404);
        }

        // Verificar permisos
        $es_instructor = in_array($this->user_data['rol'], ['profesor', 'administrador']);
        $incluir_respuestas = $this->input->get('incluir_respuestas') === 'true';

        if ($incluir_respuestas && !$es_instructor && $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para ver las respuestas correctas', 403);
        }

        $preguntas = $this->Pregunta_model->get_by_evaluacion($evaluacion_id, true);

        // Si no es instructor, remover respuestas correctas
        if (!$es_instructor || !$incluir_respuestas) {
            foreach ($preguntas as &$pregunta) {
                unset($pregunta['explicacion']);
                foreach ($pregunta['opciones'] as &$opcion) {
                    unset($opcion['es_correcta']);
                }
            }
        }

        $this->response_success($preguntas, 'Preguntas obtenidas exitosamente');
    }

    /**
     * Obtener pregunta específica
     * GET /api/preguntas/{pregunta_id}
     */
    public function show($pregunta_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, true);

        if (!$pregunta) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        // Verificar permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
        $es_instructor = in_array($this->user_data['rol'], ['profesor', 'administrador']);

        if (!$es_instructor && $evaluacion['instructor_id'] != $this->user_data['id']) {
            // Si no es instructor, remover información sensible
            unset($pregunta['explicacion']);
            foreach ($pregunta['opciones'] as &$opcion) {
                unset($opcion['es_correcta']);
            }
        }

        $this->response_success($pregunta, 'Pregunta obtenida exitosamente');
    }

    /**
     * Crear nueva pregunta
     * POST /api/preguntas
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['evaluacion_id', 'pregunta', 'tipo']);

        // Verificar que la evaluación existe y permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($input_data['evaluacion_id']);
        if (!$evaluacion) {
            $this->response_error('Evaluación no encontrada', 404);
        }

        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para crear preguntas en esta evaluación', 403);
        }

        $pregunta_data = [
            'evaluacion_id' => (int)$input_data['evaluacion_id'],
            'pregunta' => $this->sanitize_input($input_data['pregunta']),
            'tipo' => $this->sanitize_input($input_data['tipo'])
        ];

        // Validar tipo
        $tipos_validos = ['multiple_choice', 'verdadero_falso', 'texto_corto', 'texto_largo', 'matching'];
        if (!in_array($pregunta_data['tipo'], $tipos_validos)) {
            $this->response_error('Tipo de pregunta inválido', 400);
        }

        // Campos opcionales
        $campos_opcionales = ['puntos', 'orden', 'explicacion'];

        foreach ($campos_opcionales as $campo) {
            if (isset($input_data[$campo])) {
                if ($campo === 'puntos' || $campo === 'orden') {
                    $pregunta_data[$campo] = (float)$input_data[$campo];
                } else {
                    $pregunta_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        // Establecer puntos por defecto
        if (!isset($pregunta_data['puntos'])) {
            $pregunta_data['puntos'] = 1.0;
        }

        $pregunta_id = $this->Pregunta_model->crear_pregunta($pregunta_data);

        if (!$pregunta_id) {
            $this->response_error('Error al crear pregunta', 500);
        }

        // Si se proporcionaron opciones, crearlas
        if (isset($input_data['opciones']) && is_array($input_data['opciones'])) {
            foreach ($input_data['opciones'] as $opcion_data) {
                if (isset($opcion_data['texto_opcion'])) {
                    $opcion = [
                        'pregunta_id' => $pregunta_id,
                        'texto_opcion' => $this->sanitize_input($opcion_data['texto_opcion']),
                        'es_correcta' => isset($opcion_data['es_correcta']) ? (bool)$opcion_data['es_correcta'] : false,
                        'orden' => isset($opcion_data['orden']) ? (int)$opcion_data['orden'] : 0
                    ];
                    $this->Pregunta_model->crear_opcion($opcion);
                }
            }
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id);
        $this->response_success($pregunta, 'Pregunta creada exitosamente', 201);
    }

    /**
     * Actualizar pregunta
     * PUT /api/preguntas/{pregunta_id}
     */
    public function update($pregunta_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, false);
        if (!$pregunta) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        // Verificar permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para editar esta pregunta', 403);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        $campos_permitidos = ['pregunta', 'tipo', 'puntos', 'orden', 'explicacion'];
        $update_data = [];

        foreach ($campos_permitidos as $campo) {
            if (isset($input_data[$campo])) {
                if ($campo === 'puntos' || $campo === 'orden') {
                    $update_data[$campo] = (float)$input_data[$campo];
                } else {
                    $update_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        // Validar tipo si se está actualizando
        if (isset($update_data['tipo'])) {
            $tipos_validos = ['multiple_choice', 'verdadero_falso', 'texto_corto', 'texto_largo', 'matching'];
            if (!in_array($update_data['tipo'], $tipos_validos)) {
                $this->response_error('Tipo de pregunta inválido', 400);
            }
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Pregunta_model->actualizar_pregunta($pregunta_id, $update_data)) {
            $pregunta_actualizada = $this->Pregunta_model->get_pregunta($pregunta_id);
            $this->response_success($pregunta_actualizada, 'Pregunta actualizada exitosamente');
        } else {
            $this->response_error('Error al actualizar pregunta', 500);
        }
    }

    /**
     * Eliminar pregunta
     * DELETE /api/preguntas/{pregunta_id}
     */
    public function delete($pregunta_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, false);
        if (!$pregunta) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        // Verificar permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para eliminar esta pregunta', 403);
        }

        if ($this->Pregunta_model->eliminar_pregunta($pregunta_id)) {
            $this->response_success(null, 'Pregunta eliminada exitosamente');
        } else {
            $this->response_error('Error al eliminar pregunta', 500);
        }
    }

    /**
     * Obtener opciones de una pregunta
     * GET /api/preguntas/{pregunta_id}/opciones
     */
    public function opciones($pregunta_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        if (!$this->Pregunta_model->existe($pregunta_id)) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        $incluir_correctas = $this->input->get('incluir_correctas') === 'true';
        
        // Verificar permisos para ver respuestas correctas
        if ($incluir_correctas) {
            $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, false);
            $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
            
            if ($this->user_data['rol'] !== 'administrador' && 
                $evaluacion['instructor_id'] != $this->user_data['id']) {
                $incluir_correctas = false;
            }
        }

        $opciones = $this->Pregunta_model->get_opciones_pregunta($pregunta_id, $incluir_correctas);
        $this->response_success($opciones, 'Opciones obtenidas exitosamente');
    }

    /**
     * Crear opción para una pregunta
     * POST /api/preguntas/{pregunta_id}/opciones
     */
    public function crear_opcion($pregunta_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, false);
        if (!$pregunta) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        // Verificar permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para crear opciones en esta pregunta', 403);
        }

        $input_data = $this->validate_json_input(['texto_opcion']);

        $opcion_data = [
            'pregunta_id' => $pregunta_id,
            'texto_opcion' => $this->sanitize_input($input_data['texto_opcion']),
            'es_correcta' => isset($input_data['es_correcta']) ? (bool)$input_data['es_correcta'] : false,
            'orden' => isset($input_data['orden']) ? (int)$input_data['orden'] : 0
        ];

        $opcion_id = $this->Pregunta_model->crear_opcion($opcion_data);

        if (!$opcion_id) {
            $this->response_error('Error al crear opción', 500);
        }

        $opciones = $this->Pregunta_model->get_opciones_pregunta($pregunta_id);
        $this->response_success($opciones, 'Opción creada exitosamente', 201);
    }

    /**
     * Actualizar opción de respuesta
     * PUT /api/preguntas/opciones/{opcion_id}
     */
    public function actualizar_opcion($opcion_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        if (!$this->Pregunta_model->opcion_existe($opcion_id)) {
            $this->response_error('Opción no encontrada', 404);
        }

        // Obtener pregunta y verificar permisos
        $this->db->select('p.*, e.instructor_id')
                 ->from('opciones_respuesta o')
                 ->join('preguntas p', 'o.pregunta_id = p.id')
                 ->join('evaluaciones e', 'p.evaluacion_id = e.id')
                 ->where('o.id', $opcion_id);
        
        $opcion_info = $this->db->get()->row_array();
        
        if ($this->user_data['rol'] !== 'administrador' && 
            $opcion_info['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para editar esta opción', 403);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        $campos_permitidos = ['texto_opcion', 'es_correcta', 'orden'];
        $update_data = [];

        foreach ($campos_permitidos as $campo) {
            if (isset($input_data[$campo])) {
                if ($campo === 'es_correcta') {
                    $update_data[$campo] = (bool)$input_data[$campo];
                } elseif ($campo === 'orden') {
                    $update_data[$campo] = (int)$input_data[$campo];
                } else {
                    $update_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Pregunta_model->actualizar_opcion($opcion_id, $update_data)) {
            $opciones = $this->Pregunta_model->get_opciones_pregunta($opcion_info['id']);
            $this->response_success($opciones, 'Opción actualizada exitosamente');
        } else {
            $this->response_error('Error al actualizar opción', 500);
        }
    }

    /**
     * Eliminar opción de respuesta
     * DELETE /api/preguntas/opciones/{opcion_id}
     */
    public function eliminar_opcion($opcion_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        if (!$this->Pregunta_model->opcion_existe($opcion_id)) {
            $this->response_error('Opción no encontrada', 404);
        }

        // Obtener pregunta y verificar permisos
        $this->db->select('p.id as pregunta_id, e.instructor_id')
                 ->from('opciones_respuesta o')
                 ->join('preguntas p', 'o.pregunta_id = p.id')
                 ->join('evaluaciones e', 'p.evaluacion_id = e.id')
                 ->where('o.id', $opcion_id);
        
        $opcion_info = $this->db->get()->row_array();
        
        if ($this->user_data['rol'] !== 'administrador' && 
            $opcion_info['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para eliminar esta opción', 403);
        }

        if ($this->Pregunta_model->eliminar_opcion($opcion_id)) {
            $opciones = $this->Pregunta_model->get_opciones_pregunta($opcion_info['pregunta_id']);
            $this->response_success($opciones, 'Opción eliminada exitosamente');
        } else {
            $this->response_error('Error al eliminar opción', 500);
        }
    }

    /**
     * Reordenar preguntas de una evaluación
     * PUT /api/preguntas/evaluacion/{evaluacion_id}/reordenar
     */
    public function reordenar($evaluacion_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $evaluacion = $this->Evaluacion_model->get_evaluacion($evaluacion_id);
        if (!$evaluacion) {
            $this->response_error('Evaluación no encontrada', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para reordenar preguntas en esta evaluación', 403);
        }

        $input_data = $this->validate_json_input(['orden_preguntas']);

        if (!is_array($input_data['orden_preguntas'])) {
            $this->response_error('orden_preguntas debe ser un array', 400);
        }

        if ($this->Pregunta_model->reordenar_preguntas($evaluacion_id, $input_data['orden_preguntas'])) {
            $preguntas = $this->Pregunta_model->get_by_evaluacion($evaluacion_id);
            $this->response_success($preguntas, 'Preguntas reordenadas exitosamente');
        } else {
            $this->response_error('Error al reordenar preguntas', 500);
        }
    }

    /**
     * Duplicar pregunta
     * POST /api/preguntas/{pregunta_id}/duplicar
     */
    public function duplicar($pregunta_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, false);
        if (!$pregunta) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        // Verificar permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para duplicar esta pregunta', 403);
        }

        $input_data = $this->validate_json_input();
        $nueva_evaluacion_id = isset($input_data['evaluacion_id']) ? $input_data['evaluacion_id'] : null;

        // Si se especifica nueva evaluación, verificar permisos
        if ($nueva_evaluacion_id) {
            $nueva_evaluacion = $this->Evaluacion_model->get_evaluacion($nueva_evaluacion_id);
            if (!$nueva_evaluacion) {
                $this->response_error('Nueva evaluación no encontrada', 404);
            }
            
            if ($this->user_data['rol'] !== 'administrador' && 
                $nueva_evaluacion['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para duplicar en la nueva evaluación', 403);
            }
        }

        $nueva_pregunta_id = $this->Pregunta_model->duplicar_pregunta($pregunta_id, $nueva_evaluacion_id);

        if (!$nueva_pregunta_id) {
            $this->response_error('Error al duplicar pregunta', 500);
        }

        $nueva_pregunta = $this->Pregunta_model->get_pregunta($nueva_pregunta_id);
        $this->response_success($nueva_pregunta, 'Pregunta duplicada exitosamente', 201);
    }

    /**
     * Buscar preguntas
     * GET /api/preguntas/buscar
     */
    public function buscar()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $termino = $this->input->get('q');
        if (empty($termino)) {
            $this->response_error('Término de búsqueda requerido', 400);
        }

        $filtros = [
            'tipo' => $this->input->get('tipo'),
            'evaluacion_id' => $this->input->get('evaluacion_id')
        ];

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros, function($value) {
            return $value !== null && $value !== '';
        });

        $preguntas = $this->Pregunta_model->buscar_preguntas($termino, $filtros);

        // Si no es administrador, filtrar solo sus preguntas
        if ($this->user_data['rol'] !== 'administrador') {
            $preguntas = array_filter($preguntas, function($pregunta) {
                return $pregunta['instructor_id'] == $this->user_data['id'];
            });
        }

        $this->response_success($preguntas, 'Búsqueda completada exitosamente');
    }

    /**
     * Obtener preguntas por tipo
     * GET /api/preguntas/tipo/{tipo}
     */
    public function por_tipo($tipo)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $tipos_validos = ['multiple_choice', 'verdadero_falso', 'texto_corto', 'texto_largo', 'matching'];
        if (!in_array($tipo, $tipos_validos)) {
            $this->response_error('Tipo de pregunta inválido', 400);
        }

        $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
        $preguntas = $this->Pregunta_model->get_by_tipo($tipo, $limit);

        // Si no es administrador, filtrar solo sus preguntas
        if ($this->user_data['rol'] !== 'administrador') {
            $preguntas = array_filter($preguntas, function($pregunta) {
                return $pregunta['instructor_id'] == $this->user_data['id'];
            });
        }

        $this->response_success($preguntas, 'Preguntas por tipo obtenidas exitosamente');
    }

    /**
     * Obtener estadísticas de pregunta
     * GET /api/preguntas/{pregunta_id}/estadisticas
     */
    public function estadisticas($pregunta_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $pregunta = $this->Pregunta_model->get_pregunta($pregunta_id, false);
        if (!$pregunta) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        // Verificar permisos
        $evaluacion = $this->Evaluacion_model->get_evaluacion($pregunta['evaluacion_id']);
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para ver estadísticas de esta pregunta', 403);
        }

        $estadisticas = $this->Pregunta_model->get_estadisticas_pregunta($pregunta_id);
        $this->response_success($estadisticas, 'Estadísticas obtenidas exitosamente');
    }

    /**
     * Validar respuesta de pregunta (para uso interno)
     * POST /api/preguntas/{pregunta_id}/validar
     */
    public function validar($pregunta_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $input_data = $this->validate_json_input(['respuesta']);

        if (!$this->Pregunta_model->existe($pregunta_id)) {
            $this->response_error('Pregunta no encontrada', 404);
        }

        $resultado = $this->Pregunta_model->validar_respuesta($pregunta_id, $input_data['respuesta']);

        if ($resultado === null) {
            $this->response_error('Error al validar respuesta', 500);
        }

        $this->response_success($resultado, 'Respuesta validada exitosamente');
    }

    /**
     * Obtener distribución de tipos en una evaluación
     * GET /api/preguntas/evaluacion/{evaluacion_id}/distribucion
     */
    public function distribucion($evaluacion_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $evaluacion = $this->Evaluacion_model->get_evaluacion($evaluacion_id);
        if (!$evaluacion) {
            $this->response_error('Evaluación no encontrada', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador' && 
            $evaluacion['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para ver la distribución de esta evaluación', 403);
        }

        $distribucion = $this->Pregunta_model->get_distribucion_tipos($evaluacion_id);
        $total_preguntas = $this->Pregunta_model->contar_preguntas($evaluacion_id);
        $total_puntos = $this->Pregunta_model->calcular_puntos_totales($evaluacion_id);

        $respuesta = [
            'distribucion_tipos' => $distribucion,
            'total_preguntas' => $total_preguntas,
            'total_puntos' => $total_puntos
        ];

        $this->response_success($respuesta, 'Distribución obtenida exitosamente');
    }

    /**
     * Importar preguntas desde otra evaluación
     * POST /api/preguntas/importar
     */
    public function importar()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['evaluacion_origen', 'evaluacion_destino', 'preguntas_ids']);

        // Verificar que ambas evaluaciones existen y permisos
        $eval_origen = $this->Evaluacion_model->get_evaluacion($input_data['evaluacion_origen']);
        $eval_destino = $this->Evaluacion_model->get_evaluacion($input_data['evaluacion_destino']);

        if (!$eval_origen || !$eval_destino) {
            $this->response_error('Una o ambas evaluaciones no existen', 404);
        }

        if ($this->user_data['rol'] !== 'administrador') {
            if ($eval_origen['instructor_id'] != $this->user_data['id'] || 
                $eval_destino['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para importar entre estas evaluaciones', 403);
            }
        }

        $preguntas_importadas = 0;
        $errores = [];

        foreach ($input_data['preguntas_ids'] as $pregunta_id) {
            $nueva_pregunta_id = $this->Pregunta_model->duplicar_pregunta($pregunta_id, $input_data['evaluacion_destino']);
            
            if ($nueva_pregunta_id) {
                $preguntas_importadas++;
            } else {
                $errores[] = "Error al importar pregunta ID: $pregunta_id";
            }
        }

        $respuesta = [
            'preguntas_importadas' => $preguntas_importadas,
            'total_solicitadas' => count($input_data['preguntas_ids']),
            'errores' => $errores
        ];

        $this->response_success($respuesta, 'Importación completada');
    }
}
