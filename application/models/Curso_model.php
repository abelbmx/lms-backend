<?php
// application/models/Curso_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para la gestión de cursos
 */
class Curso_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener curso por ID con información completa
     */
    public function get_curso($curso_id)
    {
        $this->db->select('cursos.*, 
                          categorias.nombre as categoria_nombre,
                          CONCAT(usuarios.nombre, " ", usuarios.apellido) as instructor_nombre,
                          usuarios.avatar as instructor_avatar,
                          usuarios.bio as instructor_bio');
        $this->db->from('cursos');
        $this->db->join('categorias', 'categorias.id = cursos.categoria_id', 'left');
        $this->db->join('usuarios', 'usuarios.id = cursos.instructor_id', 'left');
        $this->db->where('cursos.id', $curso_id);

        $query = $this->db->get();

        if ($query->num_rows() === 0) {
            return null;
        }

        $curso = $query->row_array();
        
        // Convertir campos JSON si existen
        $curso['requisitos'] = $curso['requisitos'] ? json_decode($curso['requisitos'], true) : [];
        $curso['objetivos'] = $curso['objetivos'] ? json_decode($curso['objetivos'], true) : [];
        $curso['tags'] = $curso['tags'] ? json_decode($curso['tags'], true) : [];

        return $curso;
    }

    /**
     * Listar cursos con filtros y paginación
     */
    public function list_cursos($filters = [], $limit = 20, $offset = 0)
    {
        $this->db->select('cursos.id, cursos.titulo, cursos.slug, cursos.descripcion_corta,
                          cursos.imagen_portada, cursos.nivel, cursos.precio, cursos.precio_oferta,
                          cursos.duracion_horas, cursos.estado, cursos.destacado,
                          cursos.calificacion_promedio, cursos.total_estudiantes,
                          cursos.fecha_publicacion, cursos.created_at,
                          categorias.nombre as categoria_nombre,
                          CONCAT(usuarios.nombre, " ", usuarios.apellido) as instructor_nombre');
        $this->db->from('cursos');
        $this->db->join('categorias', 'categorias.id = cursos.categoria_id', 'left');
        $this->db->join('usuarios', 'usuarios.id = cursos.instructor_id', 'left');

        // Aplicar filtros
        if (!empty($filters['categoria_id'])) {
            $this->db->where('cursos.categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['nivel'])) {
            $this->db->where('cursos.nivel', $filters['nivel']);
        }

        if (!empty($filters['instructor_id'])) {
            $this->db->where('cursos.instructor_id', $filters['instructor_id']);
        }

        if (!empty($filters['estado'])) {
            $this->db->where('cursos.estado', $filters['estado']);
        } else {
            // Por defecto, solo mostrar cursos publicados
            $this->db->where('cursos.estado', 'publicado');
        }

        if (!empty($filters['destacado'])) {
            $this->db->where('cursos.destacado', 1);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('cursos.titulo', $filters['search']);
            $this->db->or_like('cursos.descripcion_corta', $filters['search']);
            $this->db->or_like('categorias.nombre', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['precio_min'])) {
            $this->db->where('cursos.precio >=', $filters['precio_min']);
        }

        if (!empty($filters['precio_max'])) {
            $this->db->where('cursos.precio <=', $filters['precio_max']);
        }

        // Ordenamiento
        $order_by = $filters['order_by'] ?? 'created_at';
        $order_dir = $filters['order_dir'] ?? 'DESC';
        $this->db->order_by("cursos.{$order_by}", $order_dir);

        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Contar cursos con filtros
     */
    public function count_cursos($filters = [])
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('cursos');
        $this->db->join('categorias', 'categorias.id = cursos.categoria_id', 'left');

        // Aplicar los mismos filtros que en list_cursos
        if (!empty($filters['categoria_id'])) {
            $this->db->where('cursos.categoria_id', $filters['categoria_id']);
        }

        if (!empty($filters['nivel'])) {
            $this->db->where('cursos.nivel', $filters['nivel']);
        }

        if (!empty($filters['instructor_id'])) {
            $this->db->where('cursos.instructor_id', $filters['instructor_id']);
        }

        if (!empty($filters['estado'])) {
            $this->db->where('cursos.estado', $filters['estado']);
        } else {
            $this->db->where('cursos.estado', 'publicado');
        }

        if (!empty($filters['destacado'])) {
            $this->db->where('cursos.destacado', 1);
        }

        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('cursos.titulo', $filters['search']);
            $this->db->or_like('cursos.descripcion_corta', $filters['search']);
            $this->db->or_like('categorias.nombre', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['precio_min'])) {
            $this->db->where('cursos.precio >=', $filters['precio_min']);
        }

        if (!empty($filters['precio_max'])) {
            $this->db->where('cursos.precio <=', $filters['precio_max']);
        }

        $query = $this->db->get();
        $result = $query->row();
        return $result->total;
    }

    /**
     * Crear nuevo curso
     */
    public function create_curso($curso_data)
    {
        // Generar slug único
        if (!isset($curso_data['slug'])) {
            $curso_data['slug'] = $this->generate_unique_slug($curso_data['titulo']);
        }

        // Convertir arrays a JSON
        if (isset($curso_data['requisitos']) && is_array($curso_data['requisitos'])) {
            $curso_data['requisitos'] = json_encode($curso_data['requisitos']);
        }

        if (isset($curso_data['objetivos']) && is_array($curso_data['objetivos'])) {
            $curso_data['objetivos'] = json_encode($curso_data['objetivos']);
        }

        if (isset($curso_data['tags']) && is_array($curso_data['tags'])) {
            $curso_data['tags'] = json_encode($curso_data['tags']);
        }

        $this->db->insert('cursos', $curso_data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar curso
     */
    public function update_curso($curso_id, $curso_data)
    {
        // Convertir arrays a JSON
        if (isset($curso_data['requisitos']) && is_array($curso_data['requisitos'])) {
            $curso_data['requisitos'] = json_encode($curso_data['requisitos']);
        }

        if (isset($curso_data['objetivos']) && is_array($curso_data['objetivos'])) {
            $curso_data['objetivos'] = json_encode($curso_data['objetivos']);
        }

        if (isset($curso_data['tags']) && is_array($curso_data['tags'])) {
            $curso_data['tags'] = json_encode($curso_data['tags']);
        }

        $curso_data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $curso_id);
        return $this->db->update('cursos', $curso_data);
    }

    /**
     * Eliminar curso (soft delete)
     */
    public function delete_curso($curso_id)
    {
        $this->db->set('estado', 'archivado');
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('id', $curso_id);
        return $this->db->update('cursos');
    }

    /**
     * Obtener cursos por instructor
     */
    public function get_cursos_by_instructor($instructor_id, $limit = 10, $offset = 0)
    {
        return $this->list_cursos([
            'instructor_id' => $instructor_id
        ], $limit, $offset);
    }

    /**
     * Obtener cursos destacados
     */
    public function get_cursos_destacados($limit = 6)
    {
        return $this->list_cursos([
            'destacado' => true,
            'order_by' => 'calificacion_promedio',
            'order_dir' => 'DESC'
        ], $limit, 0);
    }

    /**
     * Buscar cursos por término
     */
    public function search_cursos($search_term, $limit = 20, $offset = 0)
    {
        return $this->list_cursos([
            'search' => $search_term
        ], $limit, $offset);
    }

    /**
     * Generar slug único
     */
    private function generate_unique_slug($titulo)
    {
        $slug = url_title($titulo, 'dash', TRUE);
        $original_slug = $slug;
        $counter = 1;

        while ($this->slug_exists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si slug existe
     */
    private function slug_exists($slug)
    {
        $this->db->where('slug', $slug);
        $query = $this->db->get('cursos');
        return $query->num_rows() > 0;
    }

    /**
     * Obtener estadísticas de cursos
     */
    public function get_curso_stats()
    {
        // Total de cursos
        $this->db->select('COUNT(*) as total');
        $this->db->from('cursos');
        $this->db->where('estado', 'publicado');
        $query = $this->db->get();
        $total = $query->row()->total;

        // Cursos por categoría
        $this->db->select('categorias.nombre as categoria, COUNT(*) as cantidad');
        $this->db->from('cursos');
        $this->db->join('categorias', 'categorias.id = cursos.categoria_id', 'left');
        $this->db->where('cursos.estado', 'publicado');
        $this->db->group_by('categorias.id');
        $query = $this->db->get();
        $by_category = [];

        foreach ($query->result() as $row) {
            $by_category[$row->categoria] = $row->cantidad;
        }

        // Cursos por nivel
        $this->db->select('nivel, COUNT(*) as cantidad');
        $this->db->from('cursos');
        $this->db->where('estado', 'publicado');
        $this->db->group_by('nivel');
        $query = $this->db->get();
        $by_level = [];

        foreach ($query->result() as $row) {
            $by_level[$row->nivel] = $row->cantidad;
        }

        // Cursos más populares
        $this->db->select('titulo, total_estudiantes');
        $this->db->from('cursos');
        $this->db->where('estado', 'publicado');
        $this->db->order_by('total_estudiantes', 'DESC');
        $this->db->limit(5);
        $query = $this->db->get();
        $populares = $query->result_array();

        return [
            'total' => $total,
            'by_category' => $by_category,
            'by_level' => $by_level,
            'populares' => $populares
        ];
    }

    /**
     * Actualizar estadísticas del curso
     */
    public function update_curso_stats($curso_id)
    {
        // Esto se puede expandir para calcular estadísticas reales
        // Por ahora es un placeholder
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('id', $curso_id);
        return $this->db->update('cursos');
    }
}
