<?php
// application/models/Leccion_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para gestionar lecciones
 */
class Leccion_model extends CI_Model
{
    protected $table = 'lecciones';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener lecciones por módulo
     */
    public function get_by_modulo($modulo_id, $incluir_inactivas = false)
    {
        $this->db->select('l.*, m.curso_id, m.titulo as modulo_titulo')
                 ->from($this->table . ' l')
                 ->join('modulos m', 'l.modulo_id = m.id')
                 ->where('l.modulo_id', $modulo_id)
                 ->order_by('l.orden', 'ASC');
        
        if (!$incluir_inactivas) {
            $this->db->where('l.activa', 1);
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener una lección específica
     */
    public function get_leccion($leccion_id)
    {
        $this->db->select('l.*, m.curso_id, m.titulo as modulo_titulo, c.titulo as curso_titulo')
                 ->from($this->table . ' l')
                 ->join('modulos m', 'l.modulo_id = m.id')
                 ->join('cursos c', 'm.curso_id = c.id')
                 ->where('l.id', $leccion_id)
                 ->where('l.activa', 1);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Obtener lecciones por curso con progreso del usuario
     */
    public function get_lecciones_con_progreso($curso_id, $usuario_id = null)
    {
        $this->db->select('
            l.id, l.titulo, l.descripcion, l.tipo, l.duracion_minutos, 
            l.es_gratuita, l.orden, l.url_recurso,
            m.id as modulo_id, m.titulo as modulo_titulo, m.orden as modulo_orden,
            ' . ($usuario_id ? 'pl.completada, pl.tiempo_visto_minutos, pl.ultima_posicion_segundo' : 'NULL as completada, NULL as tiempo_visto_minutos, NULL as ultima_posicion_segundo') . '
        ')
        ->from($this->table . ' l')
        ->join('modulos m', 'l.modulo_id = m.id')
        ->where('m.curso_id', $curso_id)
        ->where('l.activa', 1)
        ->where('m.activo', 1);

        if ($usuario_id) {
            $this->db->join('inscripciones i', 'm.curso_id = i.curso_id AND i.usuario_id = ' . (int)$usuario_id, 'left')
                     ->join('progreso_lecciones pl', 'l.id = pl.leccion_id AND pl.inscripcion_id = i.id', 'left');
        }

        $this->db->order_by('m.orden, l.orden');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Crear nueva lección
     */
    public function crear_leccion($data)
    {
        // Generar slug único
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = $this->generar_slug($data['titulo'], $data['modulo_id']);
        }

        // Establecer orden si no se proporciona
        if (!isset($data['orden'])) {
            $data['orden'] = $this->get_siguiente_orden($data['modulo_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar lección
     */
    public function actualizar_leccion($leccion_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $leccion_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Eliminar lección
     */
    public function eliminar_leccion($leccion_id)
    {
        $this->db->where('id', $leccion_id);
        return $this->db->delete($this->table);
    }

    /**
     * Verificar si el usuario tiene acceso a la lección
     */
    public function verificar_acceso($leccion_id, $usuario_id)
    {
        // Obtener información de la lección
        $leccion = $this->get_leccion($leccion_id);
        if (!$leccion) {
            return false;
        }

        // Si es lección gratuita, siempre tiene acceso
        if ($leccion['es_gratuita']) {
            return true;
        }

        // Verificar si está inscrito en el curso
        $this->db->select('1')
                 ->from('inscripciones')
                 ->where('usuario_id', $usuario_id)
                 ->where('curso_id', $leccion['curso_id'])
                 ->where('estado', 'activa');
        
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }

    /**
     * Obtener lección anterior en el curso
     */
    public function get_leccion_anterior($leccion_id)
    {
        $leccion_actual = $this->get_leccion($leccion_id);
        if (!$leccion_actual) return null;

        $this->db->select('l.id, l.titulo, l.slug')
                 ->from($this->table . ' l')
                 ->join('modulos m', 'l.modulo_id = m.id')
                 ->where('m.curso_id', $leccion_actual['curso_id'])
                 ->where('l.activa', 1)
                 ->where('m.activo', 1)
                 ->where('(m.orden < ' . $leccion_actual['modulo_orden'] . ' OR (m.orden = ' . $leccion_actual['modulo_orden'] . ' AND l.orden < ' . $leccion_actual['orden'] . '))')
                 ->order_by('m.orden DESC, l.orden DESC')
                 ->limit(1);

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Obtener lección siguiente en el curso
     */
    public function get_leccion_siguiente($leccion_id)
    {
        $leccion_actual = $this->get_leccion($leccion_id);
        if (!$leccion_actual) return null;

        $this->db->select('l.id, l.titulo, l.slug')
                 ->from($this->table . ' l')
                 ->join('modulos m', 'l.modulo_id = m.id')
                 ->where('m.curso_id', $leccion_actual['curso_id'])
                 ->where('l.activa', 1)
                 ->where('m.activo', 1)
                 ->where('(m.orden > ' . $leccion_actual['modulo_orden'] . ' OR (m.orden = ' . $leccion_actual['modulo_orden'] . ' AND l.orden > ' . $leccion_actual['orden'] . '))')
                 ->order_by('m.orden ASC, l.orden ASC')
                 ->limit(1);

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Reordenar lecciones de un módulo
     */
    public function reordenar_lecciones($modulo_id, $orden_lecciones)
    {
        foreach ($orden_lecciones as $orden => $leccion_id) {
            $this->db->where('id', $leccion_id)
                     ->where('modulo_id', $modulo_id)
                     ->update($this->table, ['orden' => $orden + 1]);
        }
        return true;
    }

    /**
     * Obtener estadísticas de lecciones
     */
    public function get_estadisticas($curso_id = null)
    {
        $this->db->select('
            COUNT(*) as total_lecciones,
            COUNT(CASE WHEN tipo = "video" THEN 1 END) as total_videos,
            COUNT(CASE WHEN tipo = "texto" THEN 1 END) as total_textos,
            COUNT(CASE WHEN tipo = "audio" THEN 1 END) as total_audios,
            COUNT(CASE WHEN es_gratuita = 1 THEN 1 END) as total_gratuitas,
            SUM(duracion_minutos) as duracion_total_minutos
        ')
        ->from($this->table . ' l')
        ->join('modulos m', 'l.modulo_id = m.id')
        ->where('l.activa', 1);

        if ($curso_id) {
            $this->db->where('m.curso_id', $curso_id);
        }

        $query = $this->db->get();
        return $query->row_array();
    }

    // Métodos auxiliares privados

    /**
     * Generar slug único para la lección
     */
    private function generar_slug($titulo, $modulo_id)
    {
        $slug = url_title($titulo, 'dash', true);
        $original_slug = $slug;
        $counter = 1;

        while ($this->slug_existe($slug, $modulo_id)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si existe el slug en el módulo
     */
    private function slug_existe($slug, $modulo_id)
    {
        $this->db->where('slug', $slug)
                 ->where('modulo_id', $modulo_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Obtener el siguiente número de orden para el módulo
     */
    private function get_siguiente_orden($modulo_id)
    {
        $this->db->select_max('orden')
                 ->where('modulo_id', $modulo_id);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        
        return ($result['orden'] ?? 0) + 1;
    }

    /**
     * Verificar si la lección existe
     */
    public function existe($leccion_id)
    {
        $this->db->where('id', $leccion_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Buscar lecciones
     */
    public function buscar_lecciones($termino, $filtros = [])
    {
        $this->db->select('l.*, m.titulo as modulo_titulo, c.titulo as curso_titulo')
                 ->from($this->table . ' l')
                 ->join('modulos m', 'l.modulo_id = m.id')
                 ->join('cursos c', 'm.curso_id = c.id')
                 ->like('l.titulo', $termino)
                 ->or_like('l.descripcion', $termino)
                 ->where('l.activa', 1);

        if (!empty($filtros['tipo'])) {
            $this->db->where('l.tipo', $filtros['tipo']);
        }

        if (!empty($filtros['curso_id'])) {
            $this->db->where('c.id', $filtros['curso_id']);
        }

        if (!empty($filtros['es_gratuita'])) {
            $this->db->where('l.es_gratuita', $filtros['es_gratuita']);
        }

        $this->db->order_by('c.titulo, m.orden, l.orden');
        
        $query = $this->db->get();
        return $query->result_array();
    }
}
