<?php
// application/controllers/api/Cursos.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Cursos para la API
 */
class Cursos extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Curso_model');
        $this->load->model('Categoria_model');
        $this->load->model('Modulo_model');
        $this->load->model('Leccion_model');
    }

    /**
     * Listar todos los cursos con filtros
     * GET /api/cursos
     */
    public function index()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // Obtener parámetros de filtro
        $filtros = [
            'search' => $this->input->get('search'),
            'categoria_id' => $this->input->get('categoria'),
            'nivel' => $this->input->get('nivel'),
            'instructor_id' => $this->input->get('instructor'),
            'estado' => $this->input->get('estado'),
            'destacado' => $this->input->get('destacado'),
            'precio_min' => $this->input->get('precio_min'),
            'precio_max' => $this->input->get('precio_max')
        ];

        // Parámetros de paginación
        $page = $this->input->get('page') ? intval($this->input->get('page')) : 1;
        $limit = $this->input->get('limit') ? intval($this->input->get('limit')) : 20;
        $offset = ($page - 1) * $limit;

        // Limpiar filtros vacíos
        $filtros = array_filter($filtros, function($value) {
            return $value !== null && $value !== '';
        });

        $cursos = $this->Curso_model->list_cursos($filtros, $limit, $offset);
        $total = $this->Curso_model->count_cursos($filtros);

        $response_data = [
            'data' => $cursos,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ];

        $this->response_success($response_data, 'Cursos obtenidos exitosamente');
    }

    /**
     * Obtener curso específico
     * GET /api/cursos/{id}
     */
    public function show($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $curso = $this->Curso_model->get_curso($curso_id);

        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }

        // Incluir módulos y lecciones
        $modulos = $this->Modulo_model->get_by_curso($curso_id);
        foreach ($modulos as &$modulo) {
            $modulo['lecciones'] = $this->Leccion_model->get_by_modulo($modulo['id']);
        }
        $curso['modulos'] = $modulos;

        $this->response_success($curso, 'Curso obtenido exitosamente');
    }

    /**
     * Crear nuevo curso
     * POST /api/cursos
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Validar permisos (solo instructores y administradores)
        $this->validate_permission(['profesor', 'administrador']);

        $input_data = $this->validate_json_input(['titulo', 'descripcion_corta', 'categoria_id', 'nivel']);

        $curso_data = [
            'titulo' => $this->sanitize_input($input_data['titulo']),
            'descripcion_corta' => $this->sanitize_input($input_data['descripcion_corta']),
            'categoria_id' => intval($input_data['categoria_id']),
            'nivel' => $this->sanitize_input($input_data['nivel']),
            'instructor_id' => $this->user_data['id'], // Usuario autenticado
            'estado' => 'borrador',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Campos opcionales
        $optional_fields = [
            'descripcion_larga', 'imagen_portada', 'video_preview', 
            'precio', 'precio_oferta', 'duracion_horas', 'idioma',
            'requisitos', 'objetivos', 'tags', 'max_estudiantes', 
            'certificado_disponible'
        ];

        foreach ($optional_fields as $field) {
            if (isset($input_data[$field])) {
                if (in_array($field, ['precio', 'precio_oferta', 'duracion_horas', 'max_estudiantes'])) {
                    $curso_data[$field] = floatval($input_data[$field]);
                } elseif ($field === 'certificado_disponible') {
                    $curso_data[$field] = boolval($input_data[$field]);
                } else {
                    $curso_data[$field] = $this->sanitize_input($input_data[$field]);
                }
            }
        }

        // Validaciones
        if (!in_array($curso_data['nivel'], ['principiante', 'intermedio', 'avanzado'])) {
            $this->response_error('Nivel inválido', 400);
        }

        if (!$this->Categoria_model->exists($curso_data['categoria_id'])) {
            $this->response_error('Categoría no encontrada', 400);
        }

        $curso_id = $this->Curso_model->create_curso($curso_data);

        if (!$curso_id) {
            $this->response_error('Error al crear curso', 500);
        }

        $curso = $this->Curso_model->get_curso($curso_id);
        $this->response_success($curso, 'Curso creado exitosamente', 201);
    }

    /**
     * Actualizar curso
     * PUT /api/cursos/{id}
     */
    public function update($curso_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $curso = $this->Curso_model->get_curso($curso_id);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }

        // Validar permisos (solo el instructor del curso o administradores)
        if ($this->user_data['rol'] !== 'administrador' && $curso['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para editar este curso', 403);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        $allowed_fields = [
            'titulo', 'descripcion_corta', 'descripcion_larga', 'imagen_portada', 
            'video_preview', 'categoria_id', 'nivel', 'precio', 'precio_oferta', 
            'duracion_horas', 'idioma', 'requisitos', 'objetivos', 'tags', 
            'max_estudiantes', 'certificado_disponible', 'estado', 'destacado'
        ];

        $update_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($input_data[$field])) {
                if (in_array($field, ['precio', 'precio_oferta', 'duracion_horas', 'max_estudiantes', 'categoria_id'])) {
                    $update_data[$field] = floatval($input_data[$field]);
                } elseif (in_array($field, ['certificado_disponible', 'destacado'])) {
                    $update_data[$field] = boolval($input_data[$field]);
                } else {
                    $update_data[$field] = $this->sanitize_input($input_data[$field]);
                }
            }
        }

        // Validaciones
        if (isset($update_data['nivel']) && !in_array($update_data['nivel'], ['principiante', 'intermedio', 'avanzado'])) {
            $this->response_error('Nivel inválido', 400);
        }

        if (isset($update_data['categoria_id']) && !$this->Categoria_model->exists($update_data['categoria_id'])) {
            $this->response_error('Categoría no encontrada', 400);
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Curso_model->update_curso($curso_id, $update_data)) {
            $updated_curso = $this->Curso_model->get_curso($curso_id);
            $this->response_success($updated_curso, 'Curso actualizado exitosamente');
        } else {
            $this->response_error('Error al actualizar curso', 500);
        }
    }

    /**
     * Eliminar curso
     * DELETE /api/cursos/{id}
     */
    public function delete($curso_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        $curso = $this->Curso_model->get_curso($curso_id);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }

        // Validar permisos
        if ($this->user_data['rol'] !== 'administrador' && $curso['instructor_id'] != $this->user_data['id']) {
            $this->response_error('No tienes permisos para eliminar este curso', 403);
        }

        if ($this->Curso_model->delete_curso($curso_id)) {
            $this->response_success(null, 'Curso eliminado exitosamente');
        } else {
            $this->response_error('Error al eliminar curso', 500);
        }
    }

    /**
     * Obtener cursos destacados
     * GET /api/cursos/destacados
     */
    public function destacados()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $limit = $this->input->get('limit') ? intval($this->input->get('limit')) : 6;
        $cursos = $this->Curso_model->get_cursos_destacados($limit);

        $this->response_success($cursos, 'Cursos destacados obtenidos exitosamente');
    }

    /**
     * Obtener estadísticas de cursos
     * GET /api/cursos/stats
     */
    public function stats()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo administradores
        $this->validate_permission('administrador');

        $stats = $this->Curso_model->get_curso_stats();
        $this->response_success($stats, 'Estadísticas obtenidas exitosamente');
    }
}
