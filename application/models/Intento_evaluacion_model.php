<?php
// application/models/Intento_evaluacion_model.php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modelo para gestionar intentos de evaluaciones
 */
class Intento_evaluacion_model extends CI_Model
{
    protected $table = 'intentos_evaluacion';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Crear nuevo intento
     */
    public function crear_intento($data)
    {
        $data['fecha_inicio'] = date('Y-m-d H:i:s');
        $data['estado'] = 'en_progreso';
        
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * Obtener intento específico
     */
    public function get_intento($intento_id)
    {
        $this->db->select('ie.*, e.titulo as evaluacion_titulo, e.tiempo_limite_minutos, u.nombre, u.email')
                 ->from($this->table . ' ie')
                 ->join('evaluaciones e', 'ie.evaluacion_id = e.id')
                 ->join('usuarios u', 'ie.usuario_id = u.id')
                 ->where('ie.id', $intento_id);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Obtener intento en progreso
     */
    public function get_intento_en_progreso($evaluacion_id, $usuario_id)
    {
        $this->db->select('*')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id)
                 ->where('usuario_id', $usuario_id)
                 ->where('estado', 'en_progreso')
                 ->order_by('fecha_inicio', 'DESC')
                 ->limit(1);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Actualizar intento
     */
    public function actualizar_intento($intento_id, $data)
    {
        $this->db->where('id', $intento_id);
        return $this->db->update($this->table, $data);
    }

    /**
     * Obtener todos los intentos de un usuario para una evaluación
     */
    public function get_intentos_usuario($evaluacion_id, $usuario_id)
    {
        $this->db->select('*')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id)
                 ->where('usuario_id', $usuario_id)
                 ->order_by('fecha_inicio', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener intentos de una evaluación con información del usuario
     */
    public function get_intentos_evaluacion($evaluacion_id)
    {
        $this->db->select('
            ie.*,
            u.nombre,
            u.email,
            u.avatar
        ')
        ->from($this->table . ' ie')
        ->join('usuarios u', 'ie.usuario_id = u.id')
        ->where('ie.evaluacion_id', $evaluacion_id)
        ->order_by('ie.fecha_inicio', 'DESC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener estadísticas de intentos por evaluación
     */
    public function get_estadisticas_intentos($evaluacion_id)
    {
        $this->db->select('
            COUNT(*) as total_intentos,
            COUNT(DISTINCT usuario_id) as total_usuarios,
            AVG(puntuacion) as puntuacion_promedio,
            AVG(porcentaje) as porcentaje_promedio,
            AVG(tiempo_empleado_minutos) as tiempo_promedio,
            MAX(puntuacion) as puntuacion_maxima,
            MIN(puntuacion) as puntuacion_minima,
            COUNT(CASE WHEN aprobado = 1 THEN 1 END) as total_aprobados,
            COUNT(CASE WHEN estado = "completado" THEN 1 END) as total_completados,
            COUNT(CASE WHEN estado = "en_progreso" THEN 1 END) as total_en_progreso,
            COUNT(CASE WHEN estado = "abandonado" THEN 1 END) as total_abandonados
        ')
        ->from($this->table)
        ->where('evaluacion_id', $evaluacion_id);

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Marcar intentos expirados como abandonados
     */
    public function marcar_intentos_expirados()
    {
        // Obtener evaluaciones con tiempo límite
        $this->db->select('e.id, e.tiempo_limite_minutos')
                 ->from('evaluaciones e')
                 ->where('e.tiempo_limite_minutos >', 0);
        
        $evaluaciones = $this->db->get()->result_array();

        $intentos_actualizados = 0;

        foreach ($evaluaciones as $evaluacion) {
            // Calcular fecha límite
            $fecha_limite = date('Y-m-d H:i:s', strtotime("-{$evaluacion['tiempo_limite_minutos']} minutes"));
            
            // Marcar como abandonados los intentos que pasaron el tiempo límite
            $this->db->where('evaluacion_id', $evaluacion['id'])
                     ->where('estado', 'en_progreso')
                     ->where('fecha_inicio <', $fecha_limite)
                     ->update($this->table, ['estado' => 'abandonado']);
            
            $intentos_actualizados += $this->db->affected_rows();
        }

        return $intentos_actualizados;
    }

    /**
     * Obtener ranking de estudiantes en una evaluación
     */
    public function get_ranking_evaluacion($evaluacion_id, $limit = 10)
    {
        $this->db->select('
            ie.usuario_id,
            u.nombre,
            u.email,
            u.avatar,
            MAX(ie.puntuacion) as mejor_puntuacion,
            MAX(ie.porcentaje) as mejor_porcentaje,
            MIN(ie.tiempo_empleado_minutos) as mejor_tiempo,
            COUNT(ie.id) as total_intentos,
            MAX(ie.fecha_fin) as ultimo_intento
        ')
        ->from($this->table . ' ie')
        ->join('usuarios u', 'ie.usuario_id = u.id')
        ->where('ie.evaluacion_id', $evaluacion_id)
        ->where('ie.estado', 'completado')
        ->group_by('ie.usuario_id')
        ->order_by('mejor_puntuacion DESC, mejor_tiempo ASC')
        ->limit($limit);

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener historial de intentos de un usuario
     */
    public function get_historial_usuario($usuario_id, $limit = 20, $offset = 0)
    {
        $this->db->select('
            ie.*,
            e.titulo as evaluacion_titulo,
            e.tipo,
            c.titulo as curso_titulo
        ')
        ->from($this->table . ' ie')
        ->join('evaluaciones e', 'ie.evaluacion_id = e.id')
        ->join('cursos c', 'e.curso_id = c.id')
        ->where('ie.usuario_id', $usuario_id)
        ->order_by('ie.fecha_inicio', 'DESC')
        ->limit($limit, $offset);

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener promedio de calificaciones de un usuario
     */
    public function get_promedio_usuario($usuario_id, $curso_id = null)
    {
        $this->db->select('
            AVG(ie.porcentaje) as promedio_porcentaje,
            AVG(ie.puntuacion) as promedio_puntuacion,
            COUNT(ie.id) as total_evaluaciones_tomadas,
            COUNT(CASE WHEN ie.aprobado = 1 THEN 1 END) as total_aprobadas
        ')
        ->from($this->table . ' ie')
        ->join('evaluaciones e', 'ie.evaluacion_id = e.id')
        ->where('ie.usuario_id', $usuario_id)
        ->where('ie.estado', 'completado');

        if ($curso_id) {
            $this->db->where('e.curso_id', $curso_id);
        }

        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Verificar si un intento está expirado
     */
    public function esta_expirado($intento_id)
    {
        $this->db->select('
            ie.fecha_inicio,
            e.tiempo_limite_minutos,
            ie.estado
        ')
        ->from($this->table . ' ie')
        ->join('evaluaciones e', 'ie.evaluacion_id = e.id')
        ->where('ie.id', $intento_id);

        $intento = $this->db->get()->row_array();

        if (!$intento || $intento['estado'] !== 'en_progreso') {
            return false;
        }

        if ($intento['tiempo_limite_minutos'] <= 0) {
            return false; // Sin límite de tiempo
        }

        $fecha_inicio = new DateTime($intento['fecha_inicio']);
        $fecha_limite = $fecha_inicio->add(new DateInterval('PT' . $intento['tiempo_limite_minutos'] . 'M'));
        $fecha_actual = new DateTime();

        return $fecha_actual > $fecha_limite;
    }

    /**
     * Obtener tiempo restante para un intento
     */
    public function get_tiempo_restante($intento_id)
    {
        $this->db->select('
            ie.fecha_inicio,
            e.tiempo_limite_minutos
        ')
        ->from($this->table . ' ie')
        ->join('evaluaciones e', 'ie.evaluacion_id = e.id')
        ->where('ie.id', $intento_id);

        $intento = $this->db->get()->row_array();

        if (!$intento || $intento['tiempo_limite_minutos'] <= 0) {
            return null; // Sin límite de tiempo
        }

        $fecha_inicio = new DateTime($intento['fecha_inicio']);
        $fecha_limite = $fecha_inicio->add(new DateInterval('PT' . $intento['tiempo_limite_minutos'] . 'M'));
        $fecha_actual = new DateTime();

        if ($fecha_actual > $fecha_limite) {
            return 0; // Expirado
        }

        $diferencia = $fecha_actual->diff($fecha_limite);
        return ($diferencia->h * 60) + $diferencia->i; // Minutos restantes
    }

    /**
     * Eliminar intentos de evaluación
     */
    public function eliminar_intento($intento_id)
    {
        $this->db->where('id', $intento_id);
        return $this->db->delete($this->table);
    }

    /**
     * Obtener mejor intento de un usuario para una evaluación
     */
    public function get_mejor_intento($evaluacion_id, $usuario_id)
    {
        $this->db->select('*')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id)
                 ->where('usuario_id', $usuario_id)
                 ->where('estado', 'completado')
                 ->order_by('puntuacion DESC, tiempo_empleado_minutos ASC')
                 ->limit(1);
        
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * Contar intentos de usuario para una evaluación
     */
    public function contar_intentos($evaluacion_id, $usuario_id, $solo_completados = false)
    {
        $this->db->select('COUNT(*) as total')
                 ->from($this->table)
                 ->where('evaluacion_id', $evaluacion_id)
                 ->where('usuario_id', $usuario_id);

        if ($solo_completados) {
            $this->db->where('estado', 'completado');
        }

        $query = $this->db->get();
        $result = $query->row_array();
        
        return $result['total'] ?? 0;
    }

    /**
     * Verificar si existe un intento
     */
    public function existe($intento_id)
    {
        $this->db->where('id', $intento_id);
        $query = $this->db->get($this->table);
        return $query->num_rows() > 0;
    }

    /**
     * Obtener intentos que requieren calificación manual
     */
    public function get_intentos_pendientes_calificacion($instructor_id = null)
    {
        $this->db->select('
            ie.*,
            e.titulo as evaluacion_titulo,
            e.tipo,
            c.titulo as curso_titulo,
            c.instructor_id,
            u.nombre as estudiante_nombre,
            u.email as estudiante_email
        ')
        ->from($this->table . ' ie')
        ->join('evaluaciones e', 'ie.evaluacion_id = e.id')
        ->join('cursos c', 'e.curso_id = c.id')
        ->join('usuarios u', 'ie.usuario_id = u.id')
        ->where('ie.estado', 'completado')
        ->where('ie.puntuacion IS NULL') // Pendiente de calificación manual
        ->where_in('e.tipo', ['tarea', 'proyecto']); // Solo tipos que requieren calificación manual

        if ($instructor_id) {
            $this->db->where('c.instructor_id', $instructor_id);
        }

        $this->db->order_by('ie.fecha_fin', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }
}
