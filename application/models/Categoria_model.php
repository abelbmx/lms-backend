<?php
// application/models/Categoria_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para la gestión de categorías
 */
class Categoria_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener todas las categorías
     */
    public function get_all()
    {
        $this->db->select('id, nombre, descripcion, icono, color, activa');
        $this->db->from('categorias');
        $this->db->where('activa', 1);
        $this->db->order_by('nombre', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener categoría por ID
     */
    public function get_by_id($categoria_id)
    {
        $this->db->select('*');
        $this->db->from('categorias');
        $this->db->where('id', $categoria_id);
        $this->db->where('activa', 1);

        $query = $this->db->get();

        if ($query->num_rows() === 0) {
            return null;
        }

        return $query->row_array();
    }

    /**
     * Verificar si una categoría existe
     */
    public function exists($categoria_id)
    {
        $this->db->select('id');
        $this->db->from('categorias');
        $this->db->where('id', $categoria_id);
        $this->db->where('activa', 1);

        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Crear nueva categoría
     */
    public function create($categoria_data)
    {
        $this->db->insert('categorias', $categoria_data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar categoría
     */
    public function update($categoria_id, $categoria_data)
    {
        $categoria_data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $categoria_id);
        return $this->db->update('categorias', $categoria_data);
    }

    /**
     * Eliminar categoría (soft delete)
     */
    public function delete($categoria_id)
    {
        $this->db->set('activa', 0);
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('id', $categoria_id);
        return $this->db->update('categorias');
    }

    /**
     * Obtener categorías con cantidad de cursos
     */
    public function get_with_course_count()
    {
        $this->db->select('categorias.id, categorias.nombre, categorias.descripcion, 
                          categorias.icono, categorias.color, 
                          COUNT(cursos.id) as total_cursos');
        $this->db->from('categorias');
        $this->db->join('cursos', 'cursos.categoria_id = categorias.id AND cursos.estado = "publicado"', 'left');
        $this->db->where('categorias.activa', 1);
        $this->db->group_by('categorias.id');
        $this->db->order_by('categorias.nombre', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }
}
