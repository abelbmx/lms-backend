<?php
defined('BASEPATH') or exit('No direct script access allowed');

// =====================================================
// MODELO: Rol_model (Nuevo - para gestionar roles)
// =====================================================

class Rol_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all_roles()
    {
        $this->db->select('r.*, COUNT(u.id) as total_usuarios');
        $this->db->from('roles r');
        $this->db->join('usuarios u', 'r.id = u.rol_id', 'left');
        $this->db->group_by('r.id');
        $this->db->order_by('r.nombre', 'ASC');
        
        return $this->db->get()->result_array();
    }

    public function get_role_by_id($role_id)
    {
        $this->db->where('id', $role_id);
        return $this->db->get('roles')->row_array();
    }

    public function get_role_by_name($role_name)
    {
        $this->db->where('nombre', $role_name);
        return $this->db->get('roles')->row_array();
    }

    public function create_role($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert('roles', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_role($role_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $role_id);
        return $this->db->update('roles', $data);
    }

    public function delete_role($role_id)
    {
        // Verificar si hay usuarios con este rol
        $this->db->where('rol_id', $role_id);
        $users_count = $this->db->count_all_results('usuarios');
        
        if ($users_count > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar el rol porque hay usuarios asignados'];
        }
        
        $this->db->where('id', $role_id);
        $result = $this->db->delete('roles');
        
        return ['success' => $result, 'message' => $result ? 'Rol eliminado exitosamente' : 'Error al eliminar rol'];
    }

    public function user_has_permission($user_id, $permission)
    {
        $this->db->select('r.permisos');
        $this->db->from('usuarios u');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->where('u.id', $user_id);
        
        $user = $this->db->get()->row_array();
        
        if (!$user) return false;
        
        $permissions = explode(',', $user['permisos']);
        
        // Si tiene permiso 'all', puede hacer todo
        if (in_array('all', $permissions)) return true;
        
        // Verificar permiso específico
        return in_array($permission, $permissions);
    }

    public function get_permissions_list()
    {
        return [
            // Permisos generales
            'all' => 'Todos los permisos',
            
            // Permisos de cursos
            'create_course' => 'Crear cursos',
            'edit_own_course' => 'Editar propios cursos',
            'edit_any_course' => 'Editar cualquier curso',
            'delete_own_course' => 'Eliminar propios cursos',
            'delete_any_course' => 'Eliminar cualquier curso',
            'publish_course' => 'Publicar cursos',
            'view_all_courses' => 'Ver todos los cursos',
            
            // Permisos de lecciones
            'create_lesson' => 'Crear lecciones',
            'edit_lesson' => 'Editar lecciones',
            'delete_lesson' => 'Eliminar lecciones',
            
            // Permisos de estudiantes
            'enroll_course' => 'Inscribirse en cursos',
            'view_own_progress' => 'Ver propio progreso',
            'submit_assignments' => 'Enviar tareas',
            'take_quiz' => 'Tomar evaluaciones',
            'download_certificate' => 'Descargar certificados',
            
            // Permisos de instructor
            'view_students' => 'Ver estudiantes',
            'view_student_progress' => 'Ver progreso de estudiantes',
            'grade_assignments' => 'Calificar tareas',
            'grade_quiz' => 'Calificar evaluaciones',
            'issue_certificates' => 'Emitir certificados',
            'view_course_analytics' => 'Ver analíticas del curso',
            
            // Permisos de evaluaciones
            'create_quiz' => 'Crear evaluaciones',
            'edit_quiz' => 'Editar evaluaciones',
            'delete_quiz' => 'Eliminar evaluaciones',
            
            // Permisos de foros
            'participate_forum' => 'Participar en foros',
            'manage_course_forum' => 'Gestionar foro del curso',
            'moderate_forums' => 'Moderar foros',
            'delete_inappropriate_content' => 'Eliminar contenido inapropiado',
            
            // Permisos administrativos
            'manage_users' => 'Gestionar usuarios',
            'manage_roles' => 'Gestionar roles',
            'manage_categories' => 'Gestionar categorías',
            'view_system_analytics' => 'Ver analíticas del sistema',
            'manage_system_settings' => 'Gestionar configuraciones',
            'view_logs' => 'Ver logs del sistema',
            'generate_reports' => 'Generar reportes',
            
            // Permisos de calificaciones
            'rate_course' => 'Calificar cursos',
            'view_ratings' => 'Ver calificaciones',
            'moderate_ratings' => 'Moderar calificaciones'
        ];
    }
}
