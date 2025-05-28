<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Inscripcion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function is_enrolled($user_id, $course_id)
    {
        $this->db->where('usuario_id', $user_id);
        $this->db->where('curso_id', $course_id);
        return $this->db->count_all_results('inscripciones') > 0;
    }

    public function enroll_user($user_id, $course_id, $metodo_pago = 'gratuito', $monto_pagado = 0.00)
    {
        $this->db->trans_start();

        // Crear inscripción
        $enrollment_data = [
            'usuario_id' => $user_id,
            'curso_id' => $course_id,
            'metodo_pago' => $metodo_pago,
            'monto_pagado' => $monto_pagado,
            'fecha_inscripcion' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('inscripciones', $enrollment_data);
        $enrollment_id = $this->db->insert_id();

        // Crear progreso inicial para todas las lecciones
        $this->db->select('l.id');
        $this->db->from('lecciones l');
        $this->db->join('modulos m', 'l.modulo_id = m.id');
        $this->db->where('m.curso_id', $course_id);
        $this->db->where('l.activa', 1);

        $lessons = $this->db->get()->result_array();

        foreach ($lessons as $lesson) {
            $progress_data = [
                'inscripcion_id' => $enrollment_id,
                'leccion_id' => $lesson['id'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('progreso_lecciones', $progress_data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return false;
        }

        return $enrollment_id;
    }

    public function get_user_courses($user_id)
    {
        $this->db->select('i.*, c.titulo, c.descripcion_corta, c.imagen_portada, c.duracion_horas, u.nombre as instructor_nombre, u.apellido as instructor_apellido');
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('usuarios u', 'c.instructor_id = u.id');
        $this->db->where('i.usuario_id', $user_id);
        $this->db->where('i.estado', 'activa');
        $this->db->order_by('i.fecha_inscripcion', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_enrollment($user_id, $course_id)
    {
        $this->db->where('usuario_id', $user_id);
        $this->db->where('curso_id', $course_id);
        return $this->db->get('inscripciones')->row_array();
    }
}
