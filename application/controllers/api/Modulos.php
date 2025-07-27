<?php
// application/controllers/api/Modulos.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Módulos para la API
 */
class Modulos extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Modulo_model');
        $this->load->model('Curso_model');
    }

    /**
     * Obtener módulos por curso
     * GET /api/modulos/curso/{curso_id}
     */
    public function por_curso($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // Verificar que el curso existe
        $curso = $this->Curso_model->get_curso($curso_id);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }

        $incluir_lecciones = $this->input->get('incluir_lecciones') === 'true';
        
        if ($incluir_lecciones) {
            $modulos = $this->Modulo_model->get_modulos_con_lecciones($curso_id);
        } else {
            $modulos = $this->Modulo_model->get_by_curso($curso_id);
        }

        $this->response_success($modulos, 'Módulos del curso obtenidos exitosamente');
    }

    /**
     * Obtener módulo específico
     * GET /api/modulos/{modulo_id}
     */
    public function show($modulo_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $modulo = $this->Modulo_model->get_modulo($modulo_id);

        if (!$modulo) {
            $this->response_error('Módulo no encontrado', 404);
        }

        // Agregar información adicional
        $incluir_lecciones = $this->input->get('incluir_lecciones') === 'true';
        
        if ($incluir_lecciones) {
            $this->load->model('Leccion_model');
            $modulo['lecciones'] = $this->Leccion_model->get_by_modulo($modulo_id);
        }

        $modulo['total_lecciones'] = $this->Modulo_model->contar_lecciones($modulo_id);
        $modulo['duracion_total_minutos'] = $this->Modulo_model->calcular_duracion_total($modulo_id);

        $this->response_success($modulo, 'Módulo obtenido exitosamente');
    }

    /**
     * Crear nuevo módulo
     * POST /api/modulos
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo instructores y administradores pueden crear módulos
        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['titulo', 'curso_id']);

        // Verificar que el curso existe y permisos
        $curso = $this->Curso_model->get_curso($input_data['curso_id']);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }

        // Verificar permisos sobre el curso
        if ($this->user_data['rol'] !== 'administrador' && 
            $curso['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para crear módulos en este curso', 403);
        }

        $modulo_data = [
            'titulo' => $this->sanitize_input($input_data['titulo']),
            'curso_id' => (int)$input_data['curso_id']
        ];

        // Campos opcionales
        $campos_opcionales = ['descripcion', 'orden', 'activo'];

        foreach ($campos_opcionales as $campo) {
            if (isset($input_data[$campo])) {
                if ($campo === 'orden') {
                    $modulo_data[$campo] = (int)$input_data[$campo];
                } elseif ($campo === 'activo') {
                    $modulo_data[$campo] = (bool)$input_data[$campo];
                } else {
                    $modulo_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        $modulo_id = $this->Modulo_model->crear_modulo($modulo_data);

        if (!$modulo_id) {
            $this->response_error('Error al crear módulo', 500);
        }

        $modulo = $this->Modulo_model->get_modulo($modulo_id);
        $this->response_success($modulo, 'Módulo creado exitosamente', 201);
    }

    /**
     * Actualizar módulo
     * PUT /api/modulos/{modulo_id}
     */
    public function update($modulo_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $modulo = $this->Modulo_model->get_modulo($modulo_id);
        if (!$modulo) {
            $this->response_error('Módulo no encontrado', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador' && 
            $modulo['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para editar este módulo', 403);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        $campos_permitidos = ['titulo', 'descripcion', 'orden', 'activo'];
        $update_data = [];

        foreach ($campos_permitidos as $campo) {
            if (isset($input_data[$campo])) {
                if ($campo === 'orden') {
                    $update_data[$campo] = (int)$input_data[$campo];
                } elseif ($campo === 'activo') {
                    $update_data[$campo] = (bool)$input_data[$campo];
                } else {
                    $update_data[$campo] = $this->sanitize_input($input_data[$campo]);
                }
            }
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Modulo_model->actualizar_modulo($modulo_id, $update_data)) {
            $modulo_actualizado = $this->Modulo_model->get_modulo($modulo_id);
            $this->response_success($modulo_actualizado, 'Módulo actualizado exitosamente');
        } else {
            $this->response_error('Error al actualizar módulo', 500);
        }
    }

    /**
     * Eliminar módulo
     * DELETE /api/modulos/{modulo_id}
     */
    public function delete($modulo_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        $modulo = $this->Modulo_model->get_modulo($modulo_id);
        if (!$modulo) {
            $this->response_error('Módulo no encontrado', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador' && 
            $modulo['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para eliminar este módulo', 403);
        }

        // Verificar si el módulo tiene lecciones
        $total_lecciones = $this->Modulo_model->contar_lecciones($modulo_id);
        if ($total_lecciones > 0) {
            $forzar = $this->input->get('force') === 'true';
            if (!$forzar) {
                $this->response_error('El módulo contiene lecciones. Use force=true para eliminar', 400);
            }
        }

        if ($this->Modulo_model->eliminar_modulo($modulo_id)) {
            $this->response_success(null, 'Módulo eliminado exitosamente');
        } else {
            $this->response_error('Error al eliminar módulo', 500);
        }
    }

    /**
     * Reordenar módulos de un curso
     * PUT /api/modulos/reordenar/{curso_id}
     */
    public function reordenar($curso_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['orden_modulos']);

        if (!is_array($input_data['orden_modulos'])) {
            $this->response_error('orden_modulos debe ser un array', 400);
        }

        // Verificar permisos sobre el curso
        $curso = $this->Curso_model->get_curso($curso_id);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }

        if ($this->user_data['rol'] !== 'administrador' && 
            $curso['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para reordenar módulos en este curso', 403);
        }

        if ($this->Modulo_model->reordenar_modulos($curso_id, $input_data['orden_modulos'])) {
            $modulos = $this->Modulo_model->get_by_curso($curso_id);
            $this->response_success($modulos, 'Módulos reordenados exitosamente');
        } else {
            $this->response_error('Error al reordenar módulos', 500);
        }
    }

    /**
     * Duplicar módulo
     * POST /api/modulos/{modulo_id}/duplicar
     */
    public function duplicar($modulo_id)
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        $modulo = $this->Modulo_model->get_modulo($modulo_id);
        if (!$modulo) {
            $this->response_error('Módulo no encontrado', 404);
        }

        // Verificar permisos
        if ($this->user_data['rol'] !== 'administrador' && 
            $modulo['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para duplicar este módulo', 403);
        }

        $input_data = $this->validate_json_input();
        $nuevo_curso_id = isset($input_data['curso_id']) ? $input_data['curso_id'] : null;

        // Si se especifica nuevo curso, verificar permisos
        if ($nuevo_curso_id) {
            $nuevo_curso = $this->Curso_model->get_curso($nuevo_curso_id);
            if (!$nuevo_curso) {
                $this->response_error('Nuevo curso no encontrado', 404);
            }
            
            if ($this->user_data['rol'] !== 'administrador' && 
                $nuevo_curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para duplicar en el nuevo curso', 403);
            }
        }

        $nuevo_modulo_id = $this->Modulo_model->duplicar_modulo($modulo_id, $nuevo_curso_id);

        if (!$nuevo_modulo_id) {
            $this->response_error('Error al duplicar módulo', 500);
        }

        $nuevo_modulo = $this->Modulo_model->get_modulo($nuevo_modulo_id);
        $this->response_success($nuevo_modulo, 'Módulo duplicado exitosamente', 201);
    }

    /**
     * Buscar módulos
     * GET /api/modulos/buscar
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
            'curso_id' => $this->input->get('curso_id')
        ];

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros, function($value) {
            return $value !== null && $value !== '';
        });

        $modulos = $this->Modulo_model->buscar_modulos($termino, $filtros);

        // Si no es administrador, filtrar solo sus módulos
        if ($this->user_data['rol'] !== 'administrador') {
            $this->load->model('Curso_model');
            $modulos = array_filter($modulos, function($modulo) {
                $curso = $this->Curso_model->get_curso($modulo['curso_id']);
                return $curso && $curso['instructor_id'] == $this->user_data['id'];
            });
        }

        $this->response_success($modulos, 'Búsqueda completada exitosamente');
    }

    /**
     * Obtener estadísticas de módulos
     * GET /api/modulos/estadisticas
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
            $curso = $this->Curso_model->get_curso($curso_id);
            if (!$curso || $curso['instructor_id'] != $this->user_data['id']) {
                $this->response_error('No tienes permisos para ver estadísticas de este curso', 403);
            }
        }

        $estadisticas = $this->Modulo_model->get_estadisticas($curso_id);

        $this->response_success($estadisticas, 'Estadísticas obtenidas exitosamente');
    }

    /**
     * Listar todos los módulos (con paginación)
     * GET /api/modulos
     */
    public function index()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $this->validate_permission(['profesor', 'administrador']);

        // Parámetros de paginación
        $page = $this->input->get('page') ? intval($this->input->get('page')) : 1;
        $limit = $this->input->get('limit') ? intval($this->input->get('limit')) : 20;
        $offset = ($page - 1) * $limit;

        $curso_id = $this->input->get('curso_id');

        $this->db->select('m.*, c.titulo as curso_titulo, c.instructor_id')
                 ->from('modulos m')
                 ->join('cursos c', 'm.curso_id = c.id')
                 ->where('m.activo', 1);

        if ($curso_id) {
            $this->db->where('m.curso_id', $curso_id);
        }

        // Si no es administrador, solo sus módulos
        if ($this->user_data['rol'] !== 'administrador') {
            $this->db->where('c.instructor_id', $this->user_data['id']);
        }

        // Contar total
        $total_query = clone $this->db;
        $total = $total_query->count_all_results();

        // Obtener datos paginados
        $this->db->order_by('c.titulo, m.orden')
                 ->limit($limit, $offset);

        $modulos = $this->db->get()->result_array();

        $response_data = [
            'data' => $modulos,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];

        $this->response_success($response_data, 'Módulos obtenidos exitosamente');
    }
}
