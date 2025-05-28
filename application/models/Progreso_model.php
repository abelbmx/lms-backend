<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Progreso_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function mark_lesson_complete($user_id, $lesson_id, $tiempo_visto = 0)
    {
        // Obtener inscripción
        $this->db->select('i.id as inscripcion_id');
        $this->db->from('inscripciones i');
        $this->db->join('lecciones l', '1=1');
        $this->db->join('modulos m', 'l.modulo_id = m.id');
        $this->db->where('i.usuario_id', $user_id);
        $this->db->where('m.curso_id = i.curso_id');
        $this->db->where('l.id', $lesson_id);

        $enrollment = $this->db->get()->row_array();

        if (!$enrollment) {
            return false;
        }

        $update_data = [
            'completada' => 1,
            'tiempo_visto_minutos' => $tiempo_visto,
            'fecha_completado' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('inscripcion_id', $enrollment['inscripcion_id']);
        $this->db->where('leccion_id', $lesson_id);

        return $this->db->update('progreso_lecciones', $update_data);
    }

    public function get_course_progress($user_id, $course_id)
    {
        // Obtener inscripción
        $enrollment = $this->db->get_where('inscripciones', [
            'usuario_id' => $user_id,
            'curso_id' => $course_id
        ])->row_array();

        if (!$enrollment) {
            return null;
        }

        // Obtener progreso detallado
        $this->db->select('pl.*, l.titulo as leccion_titulo, l.duracion_minutos, m.titulo as modulo_titulo');
        $this->db->from('progreso_lecciones pl');
        $this->db->join('lecciones l', 'pl.leccion_id = l.id');
        $this->db->join('modulos m', 'l.modulo_id = m.id');
        $this->db->where('pl.inscripcion_id', $enrollment['id']);
        $this->db->order_by('m.orden, l.orden');

        $lessons_progress = $this->db->get()->result_array();

        // Calcular estadísticas
        $total_lessons = count($lessons_progress);
        $completed_lessons = array_filter($lessons_progress, function ($lesson) {
            return $lesson['completada'] == 1;
        });
        $completed_count = count($completed_lessons);
        $progress_percentage = $total_lessons > 0 ? ($completed_count / $total_lessons) * 100 : 0;

        return [
            'enrollment' => $enrollment,
            'progress_percentage' => round($progress_percentage, 2),
            'total_lessons' => $total_lessons,
            'completed_lessons' => $completed_count,
            'lessons_detail' => $lessons_progress
        ];
    }

    public function update_course_progress($enrollment_id)
    {
        // Calcular progreso general del curso
        $this->db->select('COUNT(*) as total, SUM(completada) as completed');
        $this->db->where('inscripcion_id', $enrollment_id);
        $stats = $this->db->get('progreso_lecciones')->row_array();

        $progress = $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0;

        // Actualizar inscripción
        $update_data = ['progreso' => round($progress, 2)];

        // Si completó el 100%, marcar como completado
        if ($progress >= 100) {
            $update_data['estado'] = 'completada';
            $update_data['fecha_completado'] = date('Y-m-d H:i:s');
        }

        $this->db->where('id', $enrollment_id);
        return $this->db->update('inscripciones', $update_data);
    }
}
