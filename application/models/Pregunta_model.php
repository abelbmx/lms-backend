<?php
// application/models/Pregunta_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para gestionar preguntas de evaluaciones
 */
class Pregunta_model extends CI_Model
{
    protected $table = 'preguntas';
    protected $opciones_table = 'opciones_respuesta';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener preguntas por evaluación
     */
    public function get_by_evaluacion($evaluacion_id, $incluir_opciones = true)
    {
        $this->db->select('*')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id)
                 ->order_by('orden', 'ASC');
        
        $preguntas = $this->db->get()->result_array();

        if ($incluir_opciones) {
            foreach ($preguntas as &$pregunta) {
                $pregunta['opciones'] = $this->get_opciones_pregunta($pregunta['id']);
            }
        }

        return $preguntas;
    }

    /**
     * Obtener pregunta específica
     */
    public function get_pregunta($pregunta_id, $incluir_opciones = true)
    {
        $this->db->select('p.*, e.titulo as evaluacion_titulo')
                 ->from($this->table . ' p')
                 ->join('evaluaciones e', 'p.evaluacion_id = e.id')
                 ->where('p.id', $pregunta_id);
        
        $pregunta = $this->db->get()->row_array();

        if ($pregunta && $incluir_opciones) {
            $pregunta['opciones'] = $this->get_opciones_pregunta($pregunta_id);
        }

        return $pregunta;
    }

    /**
     * Crear nueva pregunta
     */
    public function crear_pregunta($data)
    {
        // Establecer orden si no se proporciona
        if (!isset($data['orden'])) {
            $data['orden'] = $this->get_siguiente_orden($data['evaluacion_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar pregunta
     */
    public function actualizar_pregunta($pregunta_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $pregunta_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Eliminar pregunta
     */
    public function eliminar_pregunta($pregunta_id)
    {
        // Eliminar opciones primero (por foreign key)
        $this->db->where('pregunta_id', $pregunta_id);
        $this->db->delete($this->opciones_table);

        // Eliminar pregunta
        $this->db->where('id', $pregunta_id);
        return $this->db->delete($this->table);
    }

    /**
     * Obtener opciones de una pregunta
     */
    public function get_opciones_pregunta($pregunta_id, $incluir_correctas = true)
    {
        if ($incluir_correctas) {
            $this->db->select('*');
        } else {
            $this->db->select('id, texto_opcion, orden');
        }
        
        $this->db->from($this->opciones_table)
                 ->where('pregunta_id', $pregunta_id)
                 ->order_by('orden', 'ASC');
        
        return $this->db->get()->result_array();
    }

    /**
     * Crear opción de respuesta
     */
    public function crear_opcion($data)
    {
        // Establecer orden si no se proporciona
        if (!isset($data['orden'])) {
            $data['orden'] = $this->get_siguiente_orden_opcion($data['pregunta_id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->opciones_table, $data);
        return $this->db->insert_id();
    }

    /**
     * Actualizar opción de respuesta
     */
    public function actualizar_opcion($opcion_id, $data)
    {
        $this->db->where('id', $opcion_id);
        return $this->db->update($this->opciones_table, $data);
    }

    /**
     * Eliminar opción de respuesta
     */
    public function eliminar_opcion($opcion_id)
    {
        $this->db->where('id', $opcion_id);
        return $this->db->delete($this->opciones_table);
    }

    /**
     * Reordenar preguntas de una evaluación
     */
    public function reordenar_preguntas($evaluacion_id, $orden_preguntas)
    {
        foreach ($orden_preguntas as $orden => $pregunta_id) {
            $this->db->where('id', $pregunta_id)
                     ->where('evaluacion_id', $evaluacion_id)
                     ->update($this->table, ['orden' => $orden + 1]);
        }
        return true;
    }

    /**
     * Reordenar opciones de una pregunta
     */
    public function reordenar_opciones($pregunta_id, $orden_opciones)
    {
        foreach ($orden_opciones as $orden => $opcion_id) {
            $this->db->where('id', $opcion_id)
                     ->where('pregunta_id', $pregunta_id)
                     ->update($this->opciones_table, ['orden' => $orden + 1]);
        }
        return true;
    }

    /**
     * Duplicar pregunta con sus opciones
     */
    public function duplicar_pregunta($pregunta_id, $nueva_evaluacion_id = null)
    {
        $pregunta_original = $this->get_pregunta($pregunta_id, true);
        if (!$pregunta_original) {
            return false;
        }

        // Preparar datos para nueva pregunta
        $nueva_pregunta = $pregunta_original;
        unset($nueva_pregunta['id'], $nueva_pregunta['created_at'], $nueva_pregunta['updated_at']);
        
        if ($nueva_evaluacion_id) {
            $nueva_pregunta['evaluacion_id'] = $nueva_evaluacion_id;
        }

        $nueva_pregunta['orden'] = $this->get_siguiente_orden($nueva_pregunta['evaluacion_id']);

        // Crear nueva pregunta
        $nueva_pregunta_id = $this->crear_pregunta($nueva_pregunta);

        // Duplicar opciones
        foreach ($pregunta_original['opciones'] as $opcion) {
            unset($opcion['id'], $opcion['created_at']);
            $opcion['pregunta_id'] = $nueva_pregunta_id;
            $this->crear_opcion($opcion);
        }

        return $nueva_pregunta_id;
    }

    /**
     * Validar respuesta de una pregunta
     */
    public function validar_respuesta($pregunta_id, $respuesta_usuario)
    {
        $pregunta = $this->get_pregunta($pregunta_id, true);
        if (!$pregunta) {
            return null;
        }

        $es_correcta = false;
        $puntos_obtenidos = 0;

        switch ($pregunta['tipo']) {
            case 'multiple_choice':
            case 'verdadero_falso':
                foreach ($pregunta['opciones'] as $opcion) {
                    if ($opcion['id'] == $respuesta_usuario && $opcion['es_correcta']) {
                        $es_correcta = true;
                        $puntos_obtenidos = $pregunta['puntos'];
                        break;
                    }
                }
                break;

            case 'texto_corto':
            case 'texto_largo':
                // Para preguntas de texto, se requiere calificación manual
                return [
                    'requiere_calificacion_manual' => true,
                    'respuesta_usuario' => $respuesta_usuario,
                    'puntos_maximos' => $pregunta['puntos']
                ];

            case 'matching':
                // Implementar lógica para preguntas de emparejamiento
                // Por ahora, requiere calificación manual
                return [
                    'requiere_calificacion_manual' => true,
                    'respuesta_usuario' => $respuesta_usuario,
                    'puntos_maximos' => $pregunta['puntos']
                ];
        }

        return [
            'es_correcta' => $es_correcta,
            'puntos_obtenidos' => $puntos_obtenidos,
            'puntos_maximos' => $pregunta['puntos'],
            'requiere_calificacion_manual' => false
        ];
    }

    /**
     * Obtener estadísticas de una pregunta
     */
    public function get_estadisticas_pregunta($pregunta_id)
    {
        // Esta consulta requiere datos de intentos de evaluación
        // Se implementaría cuando tengamos los datos de respuestas
        $this->db->select('
            COUNT(*) as total_respuestas,
            AVG(CASE WHEN correcto = 1 THEN 1 ELSE 0 END) * 100 as porcentaje_acierto
        ')
        ->from('respuestas_usuario') // Tabla hipotética
        ->where('pregunta_id', $pregunta_id);

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Buscar preguntas
     */
    public function buscar_preguntas($termino, $filtros = [])
    {
        $this->db->select('p.*, e.titulo as evaluacion_titulo')
                 ->from($this->table . ' p')
                 ->join('evaluaciones e', 'p.evaluacion_id = e.id')
                 ->like('p.pregunta', $termino);

        if (!empty($filtros['tipo'])) {
            $this->db->where('p.tipo', $filtros['tipo']);
        }

        if (!empty($filtros['evaluacion_id'])) {
            $this->db->where('p.evaluacion_id', $filtros['evaluacion_id']);
        }

        $this->db->order_by('p.created_at', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener preguntas por tipo
     */
    public function get_by_tipo($tipo, $limit = null)
    {
        $this->db->select('p.*, e.titulo as evaluacion_titulo')
                 ->from($this->table . ' p')
                 ->join('evaluaciones e', 'p.evaluacion_id = e.id')
                 ->where('p.tipo', $tipo)
                 ->order_by('p.created_at', 'DESC');

        if ($limit) {
            $this->db->limit($limit);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener preguntas más difíciles (menor porcentaje de acierto)
     */
    public function get_preguntas_dificiles($limit = 10)
    {
        // Implementar cuando tengamos estadísticas de respuestas
        $this->db->select('p.*, e.titulo as evaluacion_titulo, 
                          AVG(CASE WHEN r.es_correcta = 1 THEN 1 ELSE 0 END) * 100 as porcentaje_acierto')
                 ->from($this->table . ' p')
                 ->join('evaluaciones e', 'p.evaluacion_id = e.id')
                 ->join('respuestas_usuario r', 'p.id = r.pregunta_id', 'left') // Tabla hipotética
                 ->group_by('p.id')
                 ->order_by('porcentaje_acierto', 'ASC')
                 ->limit($limit);

        $query = $this->db->get();
        return $query->result_array();
    }

    // Métodos auxiliares privados

    /**
     * Obtener el siguiente número de orden para una evaluación
     */
    private function get_siguiente_orden($evaluacion_id)
    {
        $this->db->select_max('orden')
                 ->where('evaluacion_id', $evaluacion_id);
        $query = $this->db->get($this->table);
        $result = $query->row_array();
        
        return ($result['orden'] ?? 0) + 1;
    }

    /**
     * Obtener el siguiente número de orden para opciones de una pregunta
     */
    private function get_siguiente_orden_opcion($pregunta_id)
    {
        $this->db->select_max('orden')
                 ->where('pregunta_id', $pregunta_id);
        $query = $this->db->get($this->opciones_table);
        $result = $query->row_array();
        
        return ($result['orden'] ?? 0) + 1;
    }

    /**
     * Verificar si la pregunta existe
     */
    public function existe($pregunta_id)
    {
        $this->db->where('id', $pregunta_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Verificar si la opción existe
     */
    public function opcion_existe($opcion_id)
    {
        $this->db->where('id', $opcion_id);
        $query = $this->db->get($this->opciones_table);
        return $query->num_rows() > 0;
    }

    /**
     * Contar preguntas por evaluación
     */
    public function contar_preguntas($evaluacion_id)
    {
        $this->db->select('COUNT(*) as total')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id);

        $query = $this->db->get();
        $result = $query->row_array();
        
        return $result['total'] ?? 0;
    }

    /**
     * Calcular puntos totales de una evaluación
     */
    public function calcular_puntos_totales($evaluacion_id)
    {
        $this->db->select('SUM(puntos) as total_puntos')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id);

        $query = $this->db->get();
        $result = $query->row_array();
        
        return $result['total_puntos'] ?? 0;
    }

    /**
     * Obtener distribución de tipos de pregunta en una evaluación
     */
    public function get_distribucion_tipos($evaluacion_id)
    {
        $this->db->select('tipo, COUNT(*) as cantidad')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id)
                 ->group_by('tipo');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener preguntas con opciones (alias para compatibilidad)
     * Este método es requerido por el controlador Evaluaciones
     */
    public function get_preguntas_con_opciones($evaluacion_id)
    {
        // Usar el método existente get_by_evaluacion
        return $this->get_by_evaluacion($evaluacion_id, true);
    }

    /**
     * Obtener preguntas con opciones para el frontend (sin respuestas correctas)
     */
    public function get_preguntas_para_examen($evaluacion_id)
    {
        $preguntas = $this->get_by_evaluacion($evaluacion_id, false);
        
        foreach ($preguntas as &$pregunta) {
            // Solo incluir opciones sin revelar cuáles son correctas
            $pregunta['opciones'] = $this->get_opciones_pregunta($pregunta['id'], false);
        }

        return $preguntas;
    }
}
