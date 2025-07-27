<?php
defined('BASEPATH') or exit('No direct script access allowed');

// =====================================================
// MODELO: Reporte_model (Nuevo - para reportes y analytics)
// =====================================================

class Reporte_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_general_stats()
    {
        $stats = [];

        // Usuarios
        $stats['total_usuarios'] = $this->db->count_all('usuarios');
        
        $this->db->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
        $stats['nuevos_usuarios_mes'] = $this->db->count_all_results('usuarios');

        // Cursos
        $stats['total_cursos'] = $this->db->count_all('cursos');
        
        $this->db->where('estado', 'publicado');
        $stats['cursos_publicados'] = $this->db->count_all_results('cursos');

        // Inscripciones
        $stats['total_inscripciones'] = $this->db->count_all('inscripciones');
        
        $this->db->where('estado', 'completada');
        $stats['cursos_completados'] = $this->db->count_all_results('inscripciones');

        // Certificados
        $this->db->where('certificado_emitido', 1);
        $stats['certificados_emitidos'] = $this->db->count_all_results('inscripciones');

        // Ingresos
        $this->db->select('SUM(monto) as total');
        $this->db->where('estado', 'completado');
        $revenue = $this->db->get('transacciones')->row();
        $stats['ingresos_totales'] = $revenue->total ?: 0;

        return $stats;
    }

    public function get_user_activity_report($days = 30)
    {
        $this->db->select('
            DATE(created_at) as fecha,
            COUNT(*) as nuevos_usuarios
        ');
        $this->db->from('usuarios');
        $this->db->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('fecha', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_course_enrollment_report($days = 30)
    {
        $this->db->select('
            DATE(i.fecha_inscripcion) as fecha,
            COUNT(*) as inscripciones,
            COUNT(DISTINCT i.usuario_id) as usuarios_unicos
        ');
        $this->db->from('inscripciones i');
        $this->db->where('i.fecha_inscripcion >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->group_by('DATE(i.fecha_inscripcion)');
        $this->db->order_by('fecha', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_popular_courses($limit = 10)
    {
        $this->db->select('
            c.id, c.titulo, c.imagen_portada,
            COUNT(i.id) as total_inscripciones,
            AVG(cal.calificacion) as calificacion_promedio,
            COUNT(cal.id) as total_calificaciones
        ');
        $this->db->from('cursos c');
        $this->db->join('inscripciones i', 'c.id = i.curso_id', 'left');
        $this->db->join('calificaciones cal', 'c.id = cal.curso_id', 'left');
        $this->db->where('c.estado', 'publicado');
        $this->db->group_by('c.id');
        $this->db->order_by('total_inscripciones', 'DESC');
        $this->db->limit($limit);

        return $this->db->get()->result_array();
    }

    public function get_instructor_performance($instructor_id = null, $days = 30)
    {
        $this->db->select('
            u.id, u.nombre, u.apellido,
            COUNT(DISTINCT c.id) as total_cursos,
            COUNT(DISTINCT i.id) as total_inscripciones,
            AVG(cal.calificacion) as calificacion_promedio,
            SUM(t.monto) as ingresos_generados
        ');
        $this->db->from('usuarios u');
        $this->db->join('cursos c', 'u.id = c.instructor_id', 'left');
        $this->db->join('inscripciones i', 'c.id = i.curso_id', 'left');
        $this->db->join('calificaciones cal', 'c.id = cal.curso_id', 'left');
        $this->db->join('transacciones t', 'c.id = t.curso_id AND t.estado = "completado"', 'left');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->where('r.nombre', 'instructor');

        if ($instructor_id) {
            $this->db->where('u.id', $instructor_id);
        }

        if ($days) {
            $this->db->where('i.fecha_inscripcion >=', date('Y-m-d', strtotime("-{$days} days")));
        }

        $this->db->group_by('u.id');
        $this->db->order_by('total_inscripciones', 'DESC');

        return $instructor_id ? $this->db->get()->row_array() : $this->db->get()->result_array();
    }

    public function get_category_performance()
    {
        $this->db->select('
            cat.id, cat.nombre,
            COUNT(DISTINCT c.id) as total_cursos,
            COUNT(DISTINCT i.id) as total_inscripciones,
            AVG(cal.calificacion) as calificacion_promedio
        ');
        $this->db->from('categorias cat');
        $this->db->join('cursos c', 'cat.id = c.categoria_id', 'left');
        $this->db->join('inscripciones i', 'c.id = i.curso_id', 'left');
        $this->db->join('calificaciones cal', 'c.id = cal.curso_id', 'left');
        $this->db->where('cat.activo', 1);
        $this->db->group_by('cat.id');
        $this->db->order_by('total_inscripciones', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_completion_rates($course_id = null)
    {
        $this->db->select('
            c.id, c.titulo,
            COUNT(i.id) as total_inscripciones,
            COUNT(CASE WHEN i.estado = "completada" THEN 1 END) as completadas,
            ROUND((COUNT(CASE WHEN i.estado = "completada" THEN 1 END) * 100.0 / COUNT(i.id)), 2) as tasa_completacion
        ');
        $this->db->from('cursos c');
        $this->db->join('inscripciones i', 'c.id = i.curso_id', 'left');
        $this->db->where('c.estado', 'publicado');

        if ($course_id) {
            $this->db->where('c.id', $course_id);
        }

        $this->db->group_by('c.id');
        $this->db->having('total_inscripciones >', 0);
        $this->db->order_by('tasa_completacion', 'DESC');

        return $course_id ? $this->db->get()->row_array() : $this->db->get()->result_array();
    }

    public function export_users_csv($start_date = null, $end_date = null)
    {
        $this->db->select('
            u.id, u.nombre, u.apellido, u.email, 
            u.fecha_nacimiento, u.genero, u.pais, u.ciudad,
            r.nombre as rol, u.estado, u.created_at,
            COUNT(DISTINCT i.id) as cursos_inscritos,
            COUNT(DISTINCT CASE WHEN i.estado = "completada" THEN i.id END) as cursos_completados
        ');
        $this->db->from('usuarios u');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->join('inscripciones i', 'u.id = i.usuario_id', 'left');

        if ($start_date) {
            $this->db->where('DATE(u.created_at) >=', $start_date);
        }

        if ($end_date) {
            $this->db->where('DATE(u.created_at) <=', $end_date);
        }

        $this->db->group_by('u.id');
        $this->db->order_by('u.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_learning_progress_report($user_id = null, $course_id = null)
    {
        $this->db->select('
            u.nombre, u.apellido, u.email,
            c.titulo as curso_titulo,
            i.progreso, i.estado, i.fecha_inscripcion, i.fecha_completado,
            COUNT(pl.id) as lecciones_total,
            COUNT(CASE WHEN pl.completada = 1 THEN 1 END) as lecciones_completadas,
            SUM(pl.tiempo_visto_minutos) as tiempo_total_minutos
        ');
        $this->db->from('inscripciones i');
        $this->db->join('usuarios u', 'i.usuario_id = u.id');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('progreso_lecciones pl', 'i.id = pl.inscripcion_id', 'left');

        if ($user_id) {
            $this->db->where('u.id', $user_id);
        }

        if ($course_id) {
            $this->db->where('c.id', $course_id);
        }

        $this->db->group_by('i.id');
        $this->db->order_by('i.fecha_inscripcion', 'DESC');

        return $this->db->get()->result_array();
    }
}
