<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Curso_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_courses($filters = [])
    {
        $this->db->select('c.*, u.nombre as instructor_nombre, u.apellido as instructor_apellido, cat.nombre as categoria_nombre');
        $this->db->from('cursos c');
        $this->db->join('usuarios u', 'c.instructor_id = u.id');
        $this->db->join('categorias cat', 'c.categoria_id = cat.id');
        $this->db->where('c.estado', 'publicado');

        if (isset($filters['categoria_id'])) {
            $this->db->where('c.categoria_id', $filters['categoria_id']);
        }

        if (isset($filters['nivel'])) {
            $this->db->where('c.nivel', $filters['nivel']);
        }

        if (isset($filters['destacado'])) {
            $this->db->where('c.destacado', 1);
        }

        if (isset($filters['search'])) {
            $this->db->group_start();
            $this->db->like('c.titulo', $filters['search']);
            $this->db->or_like('c.descripcion_corta', $filters['search']);
            $this->db->or_like('c.tags', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('c.destacado', 'DESC');
        $this->db->order_by('c.calificacion_promedio', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_course_detail($course_id)
    {
        $this->db->select('c.*, u.nombre as instructor_nombre, u.apellido as instructor_apellido, u.bio as instructor_bio, cat.nombre as categoria_nombre');
        $this->db->from('cursos c');
        $this->db->join('usuarios u', 'c.instructor_id = u.id');
        $this->db->join('categorias cat', 'c.categoria_id = cat.id');
        $this->db->where('c.id', $course_id);

        $course = $this->db->get()->row_array();

        if ($course) {
            // Obtener módulos y lecciones
            $this->db->select('m.*, COUNT(l.id) as total_lecciones');
            $this->db->from('modulos m');
            $this->db->join('lecciones l', 'm.id = l.modulo_id', 'left');
            $this->db->where('m.curso_id', $course_id);
            $this->db->where('m.activo', 1);
            $this->db->group_by('m.id');
            $this->db->order_by('m.orden', 'ASC');

            $modules = $this->db->get()->result_array();

            foreach ($modules as &$module) {
                $this->db->select('*');
                $this->db->from('lecciones');
                $this->db->where('modulo_id', $module['id']);
                $this->db->where('activa', 1);
                $this->db->order_by('orden', 'ASC');

                $module['lecciones'] = $this->db->get()->result_array();
            }

            $course['modulos'] = $modules;
        }

        return $course;
    }

    public function create_course($data)
    {
        $data['slug'] = $this->generate_slug($data['titulo']);
        $data['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('cursos', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_course($course_id, $data)
    {
        if (isset($data['titulo'])) {
            $data['slug'] = $this->generate_slug($data['titulo']);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $course_id);
        return $this->db->update('cursos', $data);
    }

    private function generate_slug($title)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        // Verificar si el slug ya existe
        $counter = 1;
        $original_slug = $slug;

        while ($this->slug_exists($slug)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slug_exists($slug)
    {
        $this->db->where('slug', $slug);
        return $this->db->count_all_results('cursos') > 0;
    }
}
