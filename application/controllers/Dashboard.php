
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Verificar autenticación
        if (!$this->session->userdata('logged_in')) {
            redirect('auth/login');
        }

        $this->load->model([
            'Curso_model',
            'Inscripcion_model',
            'Progreso_model',
            'Usuario_model'
        ]);
    }

    public function index()
    {
        $user_id = $this->session->userdata('user_id');
        $user_role = $this->session->userdata('rol');

        $data = array();

        if ($user_role === 'estudiante') {
            // Dashboard para estudiantes
            $data['mis_cursos'] = $this->Inscripcion_model->get_user_courses($user_id);
            $data['cursos_recomendados'] = $this->Curso_model->get_courses(['destacado' => true]);
            $data['progreso_general'] = $this->get_student_progress($user_id);
        } elseif ($user_role === 'instructor') {
            // Dashboard para instructores
            $data['mis_cursos_creados'] = $this->get_instructor_courses($user_id);
            $data['estadisticas'] = $this->get_instructor_stats($user_id);
        } elseif ($user_role === 'administrador') {
            // Dashboard para administradores
            $data['estadisticas_sistema'] = $this->get_system_stats();
            $data['cursos_recientes'] = $this->Curso_model->get_courses();
            $data['usuarios_recientes'] = $this->get_recent_users();
        }

        $data['user_role'] = $user_role;
        $this->load->view('dashboard/index', $data);
    }

    private function get_student_progress($user_id)
    {
        // Obtener progreso general del estudiante
        $this->db->select('AVG(progreso) as progreso_promedio, COUNT(*) as total_cursos');
        $this->db->where('usuario_id', $user_id);
        $this->db->where('estado', 'activa');

        return $this->db->get('inscripciones')->row_array();
    }

    private function get_instructor_courses($instructor_id)
    {
        $this->db->select('c.*, COUNT(i.id) as total_estudiantes');
        $this->db->from('cursos c');
        $this->db->join('inscripciones i', 'c.id = i.curso_id', 'left');
        $this->db->where('c.instructor_id', $instructor_id);
        $this->db->group_by('c.id');
        $this->db->order_by('c.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    private function get_instructor_stats($instructor_id)
    {
        $stats = array();

        // Total de cursos
        $this->db->where('instructor_id', $instructor_id);
        $stats['total_cursos'] = $this->db->count_all_results('cursos');

        // Total de estudiantes
        $this->db->select('COUNT(DISTINCT i.usuario_id) as total');
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->where('c.instructor_id', $instructor_id);
        $result = $this->db->get()->row();
        $stats['total_estudiantes'] = $result->total;

        // Ingresos totales
        $this->db->select('SUM(i.monto_pagado) as total');
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->where('c.instructor_id', $instructor_id);
        $result = $this->db->get()->row();
        $stats['ingresos_totales'] = $result->total ?: 0;

        return $stats;
    }

    private function get_system_stats()
    {
        $stats = array();

        // Estadísticas generales
        $stats['total_usuarios'] = $this->db->count_all('usuarios');
        $stats['total_cursos'] = $this->db->count_all('cursos');
        $stats['total_inscripciones'] = $this->db->count_all('inscripciones');

        // Usuarios por rol
        $this->db->select('r.nombre as rol, COUNT(u.id) as total');
        $this->db->from('usuarios u');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->group_by('r.id');
        $stats['usuarios_por_rol'] = $this->db->get()->result_array();

        // Cursos por categoría
        $this->db->select('c.nombre as categoria, COUNT(cu.id) as total');
        $this->db->from('cursos cu');
        $this->db->join('categorias c', 'cu.categoria_id = c.id');
        $this->db->group_by('c.id');
        $stats['cursos_por_categoria'] = $this->db->get()->result_array();

        return $stats;
    }

    private function get_recent_users($limit = 10)
    {
        $this->db->select('u.*, r.nombre as rol_nombre');
        $this->db->from('usuarios u');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->order_by('u.created_at', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }
}
