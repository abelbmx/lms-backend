<?php
// application/controllers/api/Courses.php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Cursos para la API
 */
class Courses extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Curso_model', 'Usuario_model']);
    }
    
    /**
     * Listar cursos
     * GET /api/courses
     */
    public function list_courses()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }
        
        // Obtener parámetros
        $params = $this->input->get();
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $limit = isset($params['limit']) ? min(50, max(1, intval($params['limit']))) : 12;
        $offset = ($page - 1) * $limit;
        
        // Filtros
        $filters = [];
        if (!empty($params['categoria_id'])) {
            $filters['categoria_id'] = intval($params['categoria_id']);
        }
        if (!empty($params['nivel'])) {
            $filters['nivel'] = $params['nivel'];
        }
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }
        if (!empty($params['destacado'])) {
            $filters['destacado'] = true;
        }
        if (isset($params['precio_min'])) {
            $filters['precio_min'] = floatval($params['precio_min']);
        }
        if (isset($params['precio_max'])) {
            $filters['precio_max'] = floatval($params['precio_max']);
        }
        
        // Obtener cursos y total
        $cursos = $this->Curso_model->list_cursos($filters, $limit, $offset);
        $total = $this->Curso_model->count_cursos($filters);
        
        $this->response_paginated($cursos, $total, $page, $limit, 'Cursos obtenidos exitosamente');
    }
    
    /**
     * Obtener curso específico
     * GET /api/courses/{id}
     */
    public function get_course($curso_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }
        
        $curso = $this->Curso_model->get_curso($curso_id);
        
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }
        
        // Verificar que el curso esté publicado (excepto para el instructor)
        if ($curso['estado'] !== 'publicado' && $curso['instructor_id'] != $this->user_id) {
            // Solo admins e instructores pueden ver cursos no publicados
            if (!in_array($this->user_data['rol'], ['administrador', 'instructor'])) {
                $this->response_error('Curso no disponible', 403);
            }
        }
        
        // Obtener módulos y lecciones
        $curso['modulos'] = $this->Curso_model->get_curso_modulos($curso_id);
        
        // Para cada módulo, obtener sus lecciones
        foreach ($curso['modulos'] as &$modulo) {
            $this->db->select('*');
            $this->db->from('lecciones');
            $this->db->where('modulo_id', $modulo['id']);
            $this->db->where('activa', 1);
            $this->db->order_by('orden', 'ASC');
            $query = $this->db->get();
            $modulo['lecciones'] = $query->result_array();
        }
        
        // Obtener estadísticas del curso
        $curso['estadisticas'] = $this->Curso_model->get_curso_stats($curso_id);
        
        $this->response_success($curso, 'Curso obtenido exitosamente');
    }
    
    /**
     * Crear nuevo curso (solo instructores y admins)
     * POST /api/courses/create
     */
    public function create_course()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }
        
        // Solo instructores y admins pueden crear cursos
        if (!in_array($this->user_data['rol'], ['administrador', 'instructor'])) {
            $this->response_error('Permisos insuficientes para crear cursos', 403);
        }
        
        $input_data = $this->validate_json_input(['titulo', 'categoria_id']);
        
        // Validar datos
        $titulo = $this->sanitize_input($input_data['titulo']);
        $categoria_id = intval($input_data['categoria_id']);
        
        if (strlen($titulo) < 5) {
            $this->response_error('El título debe tener al menos 5 caracteres', 400);
        }
        
        // Verificar que la categoría existe
        $this->db->where('id', $categoria_id);
        $categoria = $this->db->get('categorias')->row_array();
        if (!$categoria) {
            $this->response_error('Categoría no válida', 400);
        }
        
        // Preparar datos del curso
        $curso_data = [
            'titulo' => $titulo,
            'descripcion_corta' => $this->sanitize_input($input_data['descripcion_corta'] ?? ''),
            'descripcion_larga' => $this->sanitize_input($input_data['descripcion_larga'] ?? ''),
            'categoria_id' => $categoria_id,
            'instructor_id' => $this->user_id,
            'nivel' => $input_data['nivel'] ?? 'principiante',
            'precio' => floatval($input_data['precio'] ?? 0),
            'estado' => 'borrador'
        ];
        
        // Validar nivel
        if (!in_array($curso_data['nivel'], ['principiante', 'intermedio', 'avanzado'])) {
            $this->response_error('Nivel inválido', 400);
        }
        
        $curso_id = $this->Curso_model->create_curso($curso_data);
        
        if (!$curso_id) {
            $this->response_error('Error al crear curso', 500);
        }
        
        // Obtener curso creado
        $curso = $this->Curso_model->get_curso($curso_id);
        
        $this->response_success($curso, 'Curso creado exitosamente', 201);
    }
    
    /**
     * Actualizar curso (solo propietario y admins)
     * PUT /api/courses/{id}/update
     */
    public function update_course($curso_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }
        
        // Verificar que el curso existe
        $curso = $this->Curso_model->get_curso($curso_id);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }
        
        // Verificar permisos (propietario o admin)
        if ($curso['instructor_id'] != $this->user_id && $this->user_data['rol'] !== 'administrador') {
            $this->response_error('No tienes permisos para modificar este curso', 403);
        }
        
        $input_data = $this->validate_json_input();
        
        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }
        
        // Campos permitidos para actualizar
        $allowed_fields = ['titulo', 'descripcion_corta', 'descripcion_larga', 'categoria_id', 'nivel', 'precio', 'estado'];
        $update_data = [];
        
        foreach ($allowed_fields as $field) {
            if (isset($input_data[$field])) {
                if ($field === 'categoria_id') {
                    $categoria_id = intval($input_data[$field]);
                    $this->db->where('id', $categoria_id);
                    $categoria = $this->db->get('categorias')->row_array();
                    if (!$categoria) {
                        $this->response_error('Categoría no válida', 400);
                    }
                    $update_data[$field] = $categoria_id;
                } elseif ($field === 'nivel') {
                    if (!in_array($input_data[$field], ['principiante', 'intermedio', 'avanzado'])) {
                        $this->response_error('Nivel inválido', 400);
                    }
                    $update_data[$field] = $input_data[$field];
                } elseif ($field === 'estado') {
                    if (!in_array($input_data[$field], ['borrador', 'revision', 'publicado', 'archivado'])) {
                        $this->response_error('Estado inválido', 400);
                    }
                    $update_data[$field] = $input_data[$field];
                } elseif ($field === 'precio') {
                    $update_data[$field] = floatval($input_data[$field]);
                } else {
                    $update_data[$field] = $this->sanitize_input($input_data[$field]);
                }
            }
        }
        
        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }
        
        if ($this->Curso_model->update_curso($curso_id, $update_data)) {
            // Obtener curso actualizado
            $updated_curso = $this->Curso_model->get_curso($curso_id);
            
            $this->response_success($updated_curso, 'Curso actualizado exitosamente');
        } else {
            $this->response_error('Error al actualizar curso', 500);
        }
    }
    
    /**
     * Eliminar curso (solo propietario y admins)
     * DELETE /api/courses/{id}/delete
     */
    public function delete_course($curso_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }
        
        // Verificar que el curso existe
        $curso = $this->Curso_model->get_curso($curso_id);
        if (!$curso) {
            $this->response_error('Curso no encontrado', 404);
        }
        
        // Verificar permisos (propietario o admin)
        if ($curso['instructor_id'] != $this->user_id && $this->user_data['rol'] !== 'administrador') {
            $this->response_error('No tienes permisos para eliminar este curso', 403);
        }
        
        // Verificar si hay estudiantes inscritos
        $this->db->where('curso_id', $curso_id);
        $this->db->where('estado', 'activa');
        $inscripciones_activas = $this->db->count_all_results('inscripciones');
        
        if ($inscripciones_activas > 0) {
            $this->response_error('No se puede eliminar un curso con estudiantes inscritos', 400);
        }
        
        if ($this->Curso_model->delete_curso($curso_id)) {
            $this->response_success(null, 'Curso eliminado exitosamente');
        } else {
            $this->response_error('Error al eliminar curso', 500);
        }
    }
    
    /**
     * Obtener cursos por categoría
     * GET /api/courses/category/{categoria_id}
     */
    public function courses_by_category($categoria_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }
        
        $params = $this->input->get();
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $limit = isset($params['limit']) ? min(50, max(1, intval($params['limit']))) : 12;
        $offset = ($page - 1) * $limit;
        
        $filters = ['categoria_id' => intval($categoria_id)];
        
        $cursos = $this->Curso_model->list_cursos($filters, $limit, $offset);
        $total = $this->Curso_model->count_cursos($filters);
        
        $this->response_paginated($cursos, $total, $page, $limit, 'Cursos de la categoría obtenidos exitosamente');
    }
    
    /**
     * Buscar cursos
     * GET /api/courses/search
     */
    public function search_courses()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }
        
        $params = $this->validate_get_params(['q']);
        $query = $params['q'];
        $limit = isset($params['limit']) ? min(20, max(1, intval($params['limit']))) : 10;
        
        $cursos = $this->Curso_model->search_cursos($query, $limit);
        
        $this->response_success($cursos, 'Búsqueda realizada exitosamente');
    }
    
    /**
     * Obtener cursos destacados
     * GET /api/courses/featured
     */
    public function featured_courses()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }
        
        $params = $this->input->get();
        $limit = isset($params['limit']) ? min(20, max(1, intval($params['limit']))) : 8;
        
        $filters = ['destacado' => true];
        $cursos = $this->Curso_model->list_cursos($filters, $limit, 0);
        
        $this->response_success($cursos, 'Cursos destacados obtenidos exitosamente');
    }
}
