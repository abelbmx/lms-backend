<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Calificacion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function rate_course($user_id, $course_id, $rating, $comment = '')
    {
        // Verificar si el usuario está inscrito
        $enrollment = $this->db->get_where('inscripciones', [
            'usuario_id' => $user_id,
            'curso_id' => $course_id
        ])->row_array();

        if (!$enrollment) {
            return false;
        }

        $rating_data = [
            'curso_id' => $course_id,
            'usuario_id' => $user_id,
            'calificacion' => $rating,
            'comentario' => $comment,
            'fecha_calificacion' => date('Y-m-d H:i:s')
        ];

        // Verificar si ya existe una calificación
        $existing_rating = $this->db->get_where('calificaciones', [
            'curso_id' => $course_id,
            'usuario_id' => $user_id
        ])->row_array();

        if ($existing_rating) {
            // Actualizar calificación existente
            $this->db->where('id', $existing_rating['id']);
            return $this->db->update('calificaciones', $rating_data);
        } else {
            // Crear nueva calificación
            return $this->db->insert('calificaciones', $rating_data);
        }
    }

    public function get_course_ratings($course_id, $limit = 10, $offset = 0)
    {
        $this->db->select('c.*, u.nombre, u.apellido');
        $this->db->from('calificaciones c');
        $this->db->join('usuarios u', 'c.usuario_id = u.id');
        $this->db->where('c.curso_id', $course_id);
        $this->db->order_by('c.fecha_calificacion', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result_array();
    }

    public function get_rating_stats($course_id)
    {
        $this->db->select('
            AVG(calificacion) as promedio,
            COUNT(*) as total,
            SUM(CASE WHEN calificacion = 5 THEN 1 ELSE 0 END) as cinco_estrellas,
            SUM(CASE WHEN calificacion = 4 THEN 1 ELSE 0 END) as cuatro_estrellas,
            SUM(CASE WHEN calificacion = 3 THEN 1 ELSE 0 END) as tres_estrellas,
            SUM(CASE WHEN calificacion = 2 THEN 1 ELSE 0 END) as dos_estrellas,
            SUM(CASE WHEN calificacion = 1 THEN 1 ELSE 0 END) as una_estrella
        ');
        $this->db->where('curso_id', $course_id);

        return $this->db->get('calificaciones')->row_array();
    }
}
