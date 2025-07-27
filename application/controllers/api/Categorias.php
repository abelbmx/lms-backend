<?php
// application/controllers/api/Categorias.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Categorías para la API
 */
class Categorias extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Categoria_model');
    }

    /**
     * Listar todas las categorías
     * GET /api/categorias
     */
    public function index()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $include_count = $this->input->get('include_count');
        
        if ($include_count === 'true') {
            $categorias = $this->Categoria_model->get_with_course_count();
        } else {
            $categorias = $this->Categoria_model->get_all();
        }

        $this->response_success($categorias, 'Categorías obtenidas exitosamente');
    }

    /**
     * Obtener categoría específica
     * GET /api/categorias/{id}
     */
    public function show($categoria_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $categoria = $this->Categoria_model->get_by_id($categoria_id);

        if (!$categoria) {
            $this->response_error('Categoría no encontrada', 404);
        }

        $this->response_success($categoria, 'Categoría obtenida exitosamente');
    }

    /**
     * Crear nueva categoría
     * POST /api/categorias
     */
    public function create()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo administradores pueden crear categorías
        $this->validate_permission('administrador');

        $input_data = $this->validate_json_input(['nombre']);

        $categoria_data = [
            'nombre' => $this->sanitize_input($input_data['nombre']),
            'slug' => url_title($input_data['nombre'], 'dash', TRUE)
        ];

        // Campos opcionales
        if (isset($input_data['descripcion'])) {
            $categoria_data['descripcion'] = $this->sanitize_input($input_data['descripcion']);
        }

        if (isset($input_data['icono'])) {
            $categoria_data['icono'] = $this->sanitize_input($input_data['icono']);
        }

        if (isset($input_data['color'])) {
            $categoria_data['color'] = $this->sanitize_input($input_data['color']);
        }

        if (isset($input_data['orden'])) {
            $categoria_data['orden'] = intval($input_data['orden']);
        }

        // Validaciones
        if (strlen($categoria_data['nombre']) < 3) {
            $this->response_error('El nombre debe tener al menos 3 caracteres', 400);
        }

        $categoria_id = $this->Categoria_model->create($categoria_data);

        if (!$categoria_id) {
            $this->response_error('Error al crear categoría', 500);
        }

        $categoria = $this->Categoria_model->get_by_id($categoria_id);
        $this->response_success($categoria, 'Categoría creada exitosamente', 201);
    }

    /**
     * Actualizar categoría
     * PUT /api/categorias/{id}
     */
    public function update($categoria_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo administradores pueden actualizar categorías
        $this->validate_permission('administrador');

        $categoria = $this->Categoria_model->get_by_id($categoria_id);
        if (!$categoria) {
            $this->response_error('Categoría no encontrada', 404);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        $allowed_fields = ['nombre', 'descripcion', 'icono', 'color', 'orden'];
        $update_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($input_data[$field])) {
                if ($field === 'orden') {
                    $update_data[$field] = intval($input_data[$field]);
                } else {
                    $update_data[$field] = $this->sanitize_input($input_data[$field]);
                }
            }
        }

        // Actualizar slug si se cambió el nombre
        if (isset($update_data['nombre'])) {
            $update_data['slug'] = url_title($update_data['nombre'], 'dash', TRUE);
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Categoria_model->update($categoria_id, $update_data)) {
            $updated_categoria = $this->Categoria_model->get_by_id($categoria_id);
            $this->response_success($updated_categoria, 'Categoría actualizada exitosamente');
        } else {
            $this->response_error('Error al actualizar categoría', 500);
        }
    }

    /**
     * Eliminar categoría
     * DELETE /api/categorias/{id}
     */
    public function delete($categoria_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo administradores pueden eliminar categorías
        $this->validate_permission('administrador');

        $categoria = $this->Categoria_model->get_by_id($categoria_id);
        if (!$categoria) {
            $this->response_error('Categoría no encontrada', 404);
        }

        // Verificar que no tenga cursos asociados
        $this->load->model('Curso_model');
        $cursos_count = $this->Curso_model->count_cursos(['categoria_id' => $categoria_id]);
        
        if ($cursos_count > 0) {
            $this->response_error('No se puede eliminar una categoría que tiene cursos asociados', 400);
        }

        if ($this->Categoria_model->delete($categoria_id)) {
            $this->response_success(null, 'Categoría eliminada exitosamente');
        } else {
            $this->response_error('Error al eliminar categoría', 500);
        }
    }
}
