<?php
defined('BASEPATH') or exit('No direct script access allowed');

// =====================================================
// MODELO: Notificacion_model
// =====================================================

class Notificacion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function create_notification($user_id, $title, $message, $type = 'info', $url = null, $extra_data = null)
    {
        $data = [
            'usuario_id' => $user_id,
            'titulo' => $title,
            'mensaje' => $message,
            'tipo' => $type,
            'url_accion' => $url,
            'datos_extra' => is_array($extra_data) ? json_encode($extra_data) : $extra_data,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->insert('notificaciones', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function get_user_notifications($user_id, $unread_only = false, $limit = 50, $offset = 0)
    {
        $this->db->select('*');
        $this->db->from('notificaciones');
        $this->db->where('usuario_id', $user_id);
        
        if ($unread_only) {
            $this->db->where('leida', 0);
        }
        
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result_array();
    }

    public function mark_as_read($notification_id, $user_id = null)
    {
        $this->db->where('id', $notification_id);
        
        if ($user_id) {
            $this->db->where('usuario_id', $user_id);
        }
        
        return $this->db->update('notificaciones', ['leida' => 1]);
    }

    public function mark_all_as_read($user_id)
    {
        $this->db->where('usuario_id', $user_id);
        $this->db->where('leida', 0);
        
        return $this->db->update('notificaciones', ['leida' => 1]);
    }

    public function delete_notification($notification_id, $user_id = null)
    {
        $this->db->where('id', $notification_id);
        
        if ($user_id) {
            $this->db->where('usuario_id', $user_id);
        }
        
        return $this->db->delete('notificaciones');
    }

    public function get_unread_count($user_id)
    {
        $this->db->where('usuario_id', $user_id);
        $this->db->where('leida', 0);
        
        return $this->db->count_all_results('notificaciones');
    }

    // Métodos para notificaciones automáticas del sistema
    public function notify_course_enrollment($user_id, $course_title, $course_id)
    {
        return $this->create_notification(
            $user_id,
            'Inscripción exitosa',
            "Te has inscrito exitosamente en el curso: {$course_title}",
            'success',
            "/courses/{$course_id}",
            ['course_id' => $course_id, 'action' => 'enrollment']
        );
    }

    public function notify_lesson_completed($user_id, $lesson_title, $course_id)
    {
        return $this->create_notification(
            $user_id,
            'Lección completada',
            "Has completado la lección: {$lesson_title}",
            'success',
            "/courses/{$course_id}",
            ['course_id' => $course_id, 'action' => 'lesson_completed']
        );
    }

    public function notify_course_completed($user_id, $course_title, $course_id)
    {
        return $this->create_notification(
            $user_id,
            '¡Curso completado!',
            "¡Felicitaciones! Has completado el curso: {$course_title}",
            'success',
            "/certificates",
            ['course_id' => $course_id, 'action' => 'course_completed']
        );
    }

    public function notify_certificate_available($user_id, $course_title, $certificate_id)
    {
        return $this->create_notification(
            $user_id,
            'Certificado disponible',
            "Tu certificado del curso '{$course_title}' está listo para descargar",
            'info',
            "/certificates/{$certificate_id}",
            ['certificate_id' => $certificate_id, 'action' => 'certificate_ready']
        );
    }

    public function notify_new_course_available($user_id, $course_title, $course_id)
    {
        return $this->create_notification(
            $user_id,
            'Nuevo curso disponible',
            "Nuevo curso que te puede interesar: {$course_title}",
            'info',
            "/courses/{$course_id}",
            ['course_id' => $course_id, 'action' => 'new_course']
        );
    }

    public function notify_assignment_graded($user_id, $assignment_title, $score, $course_id)
    {
        return $this->create_notification(
            $user_id,
            'Evaluación calificada',
            "Tu evaluación '{$assignment_title}' ha sido calificada. Puntuación: {$score}%",
            'info',
            "/courses/{$course_id}",
            ['course_id' => $course_id, 'score' => $score, 'action' => 'assignment_graded']
        );
    }

    // Notificaciones masivas
    public function send_bulk_notification($user_ids, $title, $message, $type = 'info', $url = null)
    {
        $this->db->trans_start();
        
        $success_count = 0;
        foreach ($user_ids as $user_id) {
            if ($this->create_notification($user_id, $title, $message, $type, $url)) {
                $success_count++;
            }
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return false;
        }
        
        return $success_count;
    }

    public function cleanup_old_notifications($days = 30)
    {
        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->db->where('created_at <', $date_limit);
        $this->db->where('leida', 1);
        
        return $this->db->delete('notificaciones');
    }
}
