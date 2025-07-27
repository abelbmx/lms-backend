<?php
// application/models/Usuario_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para la gestión de usuarios
 */
class Usuario_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener usuario por ID
     * 
     * @param int $user_id ID del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    public function get_user($user_id)
    {
        // ✅ INCLUIR TODOS LOS CAMPOS
        $this->db->select('usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.email, 
                          roles.nombre as rol, usuarios.estado, usuarios.telefono, 
                          usuarios.bio, usuarios.pais, usuarios.ciudad, usuarios.direccion,
                          usuarios.codigo_postal, usuarios.fecha_nacimiento, usuarios.genero,
                          usuarios.avatar, usuarios.password, usuarios.email_verificado,
                          usuarios.created_at as fecha_creacion, 
                          usuarios.ultimo_acceso');
        $this->db->from('usuarios');
        $this->db->join('roles', 'roles.id = usuarios.rol_id', 'left');
        $this->db->where('usuarios.id', $user_id);

        $query = $this->db->get();

        if ($query->num_rows() === 0) {
            return null;
        }

        return $query->row_array();
    }

    /**
     * Obtener usuario por email
     * 
     * @param string $email Email del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    public function get_user_by_email($email)
    {
        // ✅ INCLUIR TODOS LOS CAMPOS
        $this->db->select('usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.email, 
                          roles.nombre as rol, usuarios.estado, usuarios.telefono, 
                          usuarios.bio, usuarios.pais, usuarios.ciudad, usuarios.direccion,
                          usuarios.codigo_postal, usuarios.fecha_nacimiento, usuarios.genero,
                          usuarios.avatar, usuarios.password, usuarios.email_verificado,
                          usuarios.created_at as fecha_creacion, 
                          usuarios.ultimo_acceso');
        $this->db->from('usuarios');
        $this->db->join('roles', 'roles.id = usuarios.rol_id', 'left');
        $this->db->where('usuarios.email', $email);

        $query = $this->db->get();

        if ($query->num_rows() === 0) {
            return null;
        }

        return $query->row_array();
    }

    /**
     * Verificar si un email ya existe
     * 
     * @param string $email Email a verificar
     * @param int|null $exclude_id ID de usuario a excluir (para actualización)
     * @return bool True si el email existe
     */
    public function email_exists($email, $exclude_id = null)
    {
        $this->db->select('id');
        $this->db->from('usuarios');
        $this->db->where('email', $email);

        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }

        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Crear nuevo usuario
     * 
     * @param array $user_data Datos del usuario
     * @return int|bool ID del usuario creado o false en caso de error
     */
    public function create_user($user_data)
    {
        // Obtener el rol_id basado en el nombre del rol
        if (isset($user_data['rol'])) {
            $this->db->select('id');
            $this->db->from('roles');
            $this->db->where('nombre', $user_data['rol']);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $role = $query->row();
                $user_data['rol_id'] = $role->id;
            } else {
                // Rol por defecto (alumno) si no se encuentra el rol
                $this->db->select('id');
                $this->db->from('roles');
                $this->db->where('nombre', 'alumno');
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $role = $query->row();
                    $user_data['rol_id'] = $role->id;
                }
            }

            // Remover el campo rol ya que no existe en la tabla
            unset($user_data['rol']);
        }

        $this->db->insert('usuarios', $user_data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar usuario
     * 
     * @param int $user_id ID del usuario
     * @param array $user_data Datos a actualizar
     * @return bool Resultado de la operación
     */
    public function update_user($user_id, $user_data)
    {
        // Obtener el rol_id basado en el nombre del rol
        if (isset($user_data['rol'])) {
            $this->db->select('id');
            $this->db->from('roles');
            $this->db->where('nombre', $user_data['rol']);
            $query = $this->db->get();

            if ($query->num_rows() > 0) {
                $role = $query->row();
                $user_data['rol_id'] = $role->id;
            }

            // Remover el campo rol ya que no existe en la tabla
            unset($user_data['rol']);
        }

        // ✅ AGREGAR TIMESTAMP DE ACTUALIZACIÓN
        $user_data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $user_id);
        $result = $this->db->update('usuarios', $user_data);
        
        // ✅ DEBUG: Ver qué se está ejecutando
        if (!$result) {
            log_message('error', 'Error updating user: ' . $this->db->last_query());
            log_message('error', 'DB Error: ' . $this->db->error()['message']);
        } else {
            log_message('info', 'User updated successfully: ' . $this->db->last_query());
        }
        
        return $result;
    }

    /**
     * Cambiar contraseña de usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $new_password Nueva contraseña (ya hasheada)
     * @return bool Resultado de la operación
     */
    public function change_password($user_id, $new_password)
    {
        $this->db->set('password', $new_password);
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('id', $user_id);
        return $this->db->update('usuarios');
    }

    /**
     * Actualizar último acceso del usuario
     * 
     * @param int $user_id ID del usuario
     * @return bool Resultado de la operación
     */
    public function update_last_access($user_id)
    {
        $this->db->set('ultimo_acceso', date('Y-m-d H:i:s'));
        $this->db->where('id', $user_id);
        return $this->db->update('usuarios');
    }
    
    /**
     * Listar usuarios con filtros
     * 
     * @param array $filters Filtros a aplicar
     * @param int $limit Límite de resultados
     * @param int $offset Offset para paginación
     * @return array Lista de usuarios
     */
    public function list_users($filters = [], $limit = 20, $offset = 0)
    {
        // ✅ INCLUIR TODOS LOS CAMPOS EN LA LISTA TAMBIÉN
        $this->db->select('usuarios.id, usuarios.nombre, usuarios.apellido, usuarios.email, 
                          roles.nombre as rol, usuarios.estado, usuarios.telefono, 
                          usuarios.bio, usuarios.pais, usuarios.ciudad, usuarios.direccion,
                          usuarios.codigo_postal, usuarios.fecha_nacimiento, usuarios.genero,
                          usuarios.avatar, usuarios.email_verificado,
                          usuarios.created_at as fecha_creacion, 
                          usuarios.ultimo_acceso');
        $this->db->from('usuarios');
        $this->db->join('roles', 'roles.id = usuarios.rol_id', 'left');

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('usuarios.nombre', $filters['search']);
            $this->db->or_like('usuarios.apellido', $filters['search']);
            $this->db->or_like('usuarios.email', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['rol'])) {
            $this->db->where('roles.nombre', $filters['rol']);
        }

        if (!empty($filters['estado'])) {
            $this->db->where('usuarios.estado', $filters['estado']);
        }

        $this->db->limit($limit, $offset);
        $this->db->order_by('usuarios.id', 'DESC');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Contar total de usuarios con filtros
     * 
     * @param array $filters Filtros a aplicar
     * @return int Total de usuarios
     */
    public function count_users($filters = [])
    {
        $this->db->select('COUNT(*) as total');
        $this->db->from('usuarios');
        $this->db->join('roles', 'roles.id = usuarios.rol_id', 'left');

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('usuarios.nombre', $filters['search']);
            $this->db->or_like('usuarios.apellido', $filters['search']);
            $this->db->or_like('usuarios.email', $filters['search']);
            $this->db->group_end();
        }

        if (!empty($filters['rol'])) {
            $this->db->where('roles.nombre', $filters['rol']);
        }

        if (!empty($filters['estado'])) {
            $this->db->where('usuarios.estado', $filters['estado']);
        }

        $query = $this->db->get();
        $result = $query->row();
        return $result->total;
    }

    /**
     * Eliminar usuario (soft delete cambiando estado)
     * 
     * @param int $user_id ID del usuario
     * @return bool Resultado de la operación
     */
    public function delete_user($user_id)
    {
        $this->db->set('estado', 'inactivo');
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('id', $user_id);
        return $this->db->update('usuarios');
    }

    /**
     * Obtener estadísticas de usuarios
     * 
     * @return array Estadísticas de usuarios
     */
    public function get_user_stats()
    {
        // Total de usuarios
        $this->db->select('COUNT(*) as total');
        $this->db->from('usuarios');
        $query = $this->db->get();
        $total = $query->row()->total;

        // Usuarios por rol
        $this->db->select('roles.nombre as rol, COUNT(*) as cantidad');
        $this->db->from('usuarios');
        $this->db->join('roles', 'roles.id = usuarios.rol_id', 'left');
        $this->db->group_by('roles.nombre');
        $query = $this->db->get();
        $by_role = [];

        foreach ($query->result() as $row) {
            $by_role[$row->rol] = $row->cantidad;
        }

        // Usuarios por estado
        $this->db->select('estado, COUNT(*) as cantidad');
        $this->db->from('usuarios');
        $this->db->group_by('estado');
        $query = $this->db->get();
        $by_status = [];

        foreach ($query->result() as $row) {
            $by_status[$row->estado] = $row->cantidad;
        }

        // Usuarios nuevos en los últimos 30 días
        $this->db->select('COUNT(*) as total');
        $this->db->from('usuarios');
        $this->db->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
        $query = $this->db->get();
        $new_users = $query->row()->total;

        return [
            'total' => $total,
            'by_role' => $by_role,
            'by_status' => $by_status,
            'new_users' => $new_users
        ];
    }
}
