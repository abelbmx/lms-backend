<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Foro_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_course_forums($course_id)
    {
        $this->db->select('f.*, u.nombre, u.apellido, COUNT(p.id) as total_posts');
        $this->db->from('foros f');
        $this->db->join('usuarios u', 'f.creado_por = u.id');
        $this->db->join('posts_foro p', 'f.id = p.foro_id', 'left');
        $this->db->where('f.curso_id', $course_id);
        $this->db->where('f.activo', 1);
        $this->db->group_by('f.id');
        $this->db->order_by('f.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    public function get_forum_posts($forum_id, $limit = 20, $offset = 0)
    {
        $this->db->select('p.*, u.nombre, u.apellido, u.avatar');
        $this->db->from('posts_foro p');
        $this->db->join('usuarios u', 'p.usuario_id = u.id');
        $this->db->where('p.foro_id', $forum_id);
        $this->db->where('p.parent_id IS NULL'); // Solo posts principales
        $this->db->order_by('p.created_at', 'DESC');
        $this->db->limit($limit, $offset);

        $posts = $this->db->get()->result_array();

        // Obtener respuestas para cada post
        foreach ($posts as &$post) {
            $post['respuestas'] = $this->get_post_replies($post['id']);
        }

        return $posts;
    }

    public function get_post_replies($post_id)
    {
        $this->db->select('p.*, u.nombre, u.apellido, u.avatar');
        $this->db->from('posts_foro p');
        $this->db->join('usuarios u', 'p.usuario_id = u.id');
        $this->db->where('p.parent_id', $post_id);
        $this->db->order_by('p.created_at', 'ASC');

        return $this->db->get()->result_array();
    }

    public function create_post($forum_id, $user_id, $title, $content, $parent_id = null)
    {
        $post_data = [
            'foro_id' => $forum_id,
            'usuario_id' => $user_id,
            'titulo' => $title,
            'contenido' => $content,
            'parent_id' => $parent_id,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('posts_foro', $post_data);
        return $this->db->insert_id();
    }
}
