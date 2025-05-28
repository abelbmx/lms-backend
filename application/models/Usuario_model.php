<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Usuario_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_user_by_id($user_id)
    {
        $this->db->select('u.*, r.nombre as rol_nombre, r.permisos as rol_permisos');
        $this->db->from('usuarios u');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->where('u.id', $user_id);
        return $this->db->get()->row_array();
    }

    public function get_user_by_email($email)
    {
        $this->db->select('u.*, r.nombre as rol_nombre, r.permisos as rol_permisos');
        $this->db->from('usuarios u');
        $this->db->join('roles r', 'u.rol_id = r.id');
        $this->db->where('u.email', $email);
        return $this->db->get()->row_array();
    }

    public function create_user($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('usuarios', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function update_user($user_id, $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $user_id);
        return $this->db->update('usuarios', $data);
    }

    public function verify_password($user_id, $password)
    {
        $user = $this->get_user_by_id($user_id);
        if ($user) {
            return password_verify($password, $user['password']);
        }
        return false;
    }

    public function update_last_access($user_id)
    {
        $this->db->where('id', $user_id);
        return $this->db->update('usuarios', ['ultimo_acceso' => date('Y-m-d H:i:s')]);
    }
}
