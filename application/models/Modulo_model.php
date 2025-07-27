<?php
// application/models/Modulo_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para gestionar módulos de cursos
 */
class Modulo_model extends CI_Model
{
    protected $table = 'modulos';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener módulos por curso
     */
    public function get_by_curso($curso_id, $incluir_inactivos = false)
    {
        $this->db->select('*')
                 ->from($this->table)
                 ->where('curso_id', $curso_id)
                 ->order_by('orden', 'ASC');
        
        if (!$incluir_inactivos) {
            $this->db->where('activo', 1);
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener módulo específico
     */
    public function get_modulo($modulo_id)
    {
        $this->db->select('m.*, c.titulo as curso_titulo, c.instructor_id')
                 ->from($this->table . ' m')
                 ->join('cursos c', 'm.curso_id = c.id')
                 ->where('m.id', $modulo_id);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Obtener módulos con lecciones
     */
    public function get_modulos_con_lecciones($curso_id)
    {
        $modulos = $this->get_by_curso($curso_id);
        
        foreach ($modulos as &$modulo) {
            $this->db->select('*')
                     ->from('lecciones')
                     ->where('modulo_id', $modulo['id'])
                     ->where('activa', 1)
                     ->order_by('orden', 'ASC');
            
            $modulo['lecciones'] = $this->db->get()->result_array();
        }
        
        return $modulos;
    }

    /**
     * Crear nuevo módulo
     */
    public function crear_modulo($data)
    {
        // Establecer orden si no se proporciona
        if (!isset($data['orden'])) {
            $data['orden'] = $this->get_siguiente_orden($data['curso_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar módulo
     */
    public function actualizar_modulo($modulo_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $modulo_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Eliminar módulo
     */
    public function eliminar_modulo($modulo_id)
    {
        $this->db->where('id', $modulo_id);
        return $this->db->delete($this->table);
    }

    /**
     * Reordenar módulos de un curso
     */
    public function reordenar_modulos($curso_id, $orden_modulos)
    {
        foreach ($orden_modulos as $orden => $modulo_id) {
            $this->db->where('id', $modulo_id)
                     ->where('curso_id', $curso_id)
                     ->update($this->table, ['orden' => $orden + 1]);
        }
        return true;
    }

    /**
     * Obtener estadísticas de módulos
     */
    public function get_estadisticas($curso_id = null)
    {
        $this->db->select('
            COUNT(*) as total_modulos,
            AVG((SELECT COUNT(*) FROM lecciones WHERE modulo_id = m.id AND activa = 1)) as promedio_lecciones_por_modulo
        ')
        ->from($this->table . ' m')
        ->where('activo', 1);

        if ($curso_id) {
            $this->db->where('curso_id', $curso_id);
        }

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Duplicar módulo con sus lecciones
     */
    public function duplicar_modulo($modulo_id, $nuevo_curso_id = null)
    {
        $modulo_original = $this->get_modulo($modulo_id);
        if (!$modulo_original) {
            return false;
        }

        // Preparar datos para nuevo módulo
        $nuevo_modulo = $modulo_original;
        unset($nuevo_modulo['id'], $nuevo_modulo['created_at'], $nuevo_modulo['updated_at']);
        
        if ($nuevo_curso_id) {
            $nuevo_modulo['curso_id'] = $nuevo_curso_id;
        }

        $nuevo_modulo['orden'] = $this->get_siguiente_orden($nuevo_modulo['curso_id']);

        // Crear nuevo módulo
        $nuevo_modulo_id = $this->crear_modulo($nuevo_modulo);

        // Duplicar lecciones
        $this->load->model('Leccion_model');
        $lecciones = $this->Leccion_model->get_by_modulo($modulo_id);
        
        foreach ($lecciones as $leccion) {
            unset($leccion['id'], $leccion['created_at'], $leccion['updated_at']);
            $leccion['modulo_id'] = $nuevo_modulo_id;
            $this->Leccion_model->crear_leccion($leccion);
        }

        return $nuevo_modulo_id;
    }

    /**
     * Verificar si el módulo existe
     */
    public function existe($modulo_id)
    {
        $this->db->where('id', $modulo_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Buscar módulos
     */
    public function buscar_modulos($termino, $filtros = [])
    {
        $this->db->select('m.*, c.titulo as curso_titulo')
                 ->from($this->table . ' m')
                 ->join('cursos c', 'm.curso_id = c.id')
                 ->like('m.titulo', $termino)
                 ->or_like('m.descripcion', $termino)
                 ->where('m.activo', 1);

        if (!empty($filtros['curso_id'])) {
            $this->db->where('m.curso_id', $filtros['curso_id']);
        }

        $this->db->order_by('c.titulo, m.orden');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    // Métodos auxiliares privados

    /**
     * Obtener el siguiente número de orden para un curso
     */
    private function get_siguiente_orden($curso_id)
    {
        $this->db->select_max('orden')
                 ->where('curso_id', $curso_id);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        
        return ($result['orden'] ?? 0) + 1;
    }

    /**
     * Contar lecciones en un módulo
     */
    public function contar_lecciones($modulo_id)
    {
        $this->db->select('COUNT(*) as total')
                 ->from('lecciones')
                 ->where('modulo_id', $modulo_id)
                 ->where('activa', 1);

        $query = $this->db->get();
        $result = $query->row_array();
        
        return $result['total'] ?? 0;
    }

    /**
     * Calcular duración total del módulo
     */
    public function calcular_duracion_total($modulo_id)
    {
        $this->db->select('SUM(duracion_minutos) as duracion_total')
                 ->from('lecciones')
                 ->where('modulo_id', $modulo_id)
                 ->where('activa', 1);

        $query = $this->db->get();
        $result = $query->row_array();
        
        return $result['duracion_total'] ?? 0;
    }
}
