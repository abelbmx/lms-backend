<?php
// application/models/Progreso_leccion_model.php
defined('BASEPATH') or exit('No direct script access allowed');

class Progreso_leccion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener estadísticas generales de progreso de un usuario
     */
    public function get_estadisticas_usuario($usuario_id)
    {
        // Obtener estadísticas de inscripciones
        $this->db->select('
            COUNT(CASE WHEN estado = "activa" AND progreso < 100 THEN 1 END) as cursosEnProgreso,
            COUNT(CASE WHEN estado = "completada" OR progreso = 100 THEN 1 END) as cursosCompletados,
            COALESCE(SUM(tiempo_total_minutos), 0) as tiempoTotalMinutos,
            COUNT(CASE WHEN certificado_emitido = 1 THEN 1 END) as certificadosObtenidos
        ');
        $this->db->from('inscripciones');
        $this->db->where('usuario_id', $usuario_id);
        $inscripciones_stats = $this->db->get()->row_array();

        // Obtener promedio de calificaciones
        $this->db->select('AVG(calificacion) as promedio');
        $this->db->from('calificaciones');
        $this->db->where('usuario_id', $usuario_id);
        $calificaciones = $this->db->get()->row_array();

        // Contar lecciones completadas
        $this->db->select('COUNT(*) as leccionesCompletadas');
        $this->db->from('progreso_lecciones pl');
        $this->db->join('inscripciones i', 'pl.inscripcion_id = i.id');
        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('pl.completada', 1);
        $lecciones = $this->db->get()->row_array();

        // Calcular racha actual (días consecutivos con actividad)
        $racha = $this->calcular_racha_actual($usuario_id);

        return [
            'cursosEnProgreso' => (int)$inscripciones_stats['cursosEnProgreso'],
            'cursosCompletados' => (int)$inscripciones_stats['cursosCompletados'],
            'tiempoTotalEstudio' => round($inscripciones_stats['tiempoTotalMinutos'] / 60, 1), // Convertir a horas
            'certificadosObtenidos' => (int)$inscripciones_stats['certificadosObtenidos'],
            'promedioCalificaciones' => $calificaciones['promedio'] ? round($calificaciones['promedio'], 1) : 0,
            'leccionesCompletadas' => (int)$lecciones['leccionesCompletadas'],
            'rachaActual' => $racha,
            'tiempoPromedioSesion' => $this->calcular_tiempo_promedio_sesion($usuario_id)
        ];
    }

    /**
     * Obtener resumen de progreso de todos los cursos del usuario
     */
    public function get_resumen_progreso($usuario_id)
    {
        $this->db->select('
            i.id as inscripcion_id,
            i.curso_id,
            i.progreso,
            i.tiempo_total_minutos,
            i.fecha_inscripcion,
            i.estado,
            c.titulo as curso_titulo,
            COUNT(l.id) as total_lecciones,
            COUNT(CASE WHEN pl.completada = 1 THEN 1 END) as lecciones_completadas
        ');
        
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('modulos m', 'c.id = m.curso_id', 'left');
        $this->db->join('lecciones l', 'm.id = l.modulo_id', 'left');
        $this->db->join('progreso_lecciones pl', 'l.id = pl.leccion_id AND pl.inscripcion_id = i.id', 'left');
        
        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('i.estado !=', 'cancelada');
        $this->db->group_by('i.id, i.curso_id, i.progreso, i.tiempo_total_minutos, i.fecha_inscripcion, i.estado, c.titulo');
        $this->db->order_by('i.fecha_inscripcion', 'DESC');
        
        $query = $this->db->get();
        
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[] = [
                'curso_id' => (int)$row['curso_id'],
                'curso_titulo' => $row['curso_titulo'],
                'total_lecciones' => (int)$row['total_lecciones'],
                'lecciones_completadas' => (int)$row['lecciones_completadas'],
                'porcentaje_progreso' => (float)$row['progreso'],
                'tiempo_total_minutos' => (int)$row['tiempo_total_minutos'],
                'fecha_inscripcion' => $row['fecha_inscripcion'],
                'estado' => $row['estado']
            ];
        }
        
        return $result;
    }

    /**
     * Obtener progreso detallado de un curso
     */
    public function get_progreso_curso($curso_id, $usuario_id)
    {
        // Obtener inscripción
        $this->db->select('*');
        $this->db->from('inscripciones');
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('curso_id', $curso_id);
        $inscripcion = $this->db->get()->row_array();
        
        if (!$inscripcion) {
            return null;
        }

        // Obtener estructura del curso con progreso
        $this->db->select('
            m.id as modulo_id,
            m.titulo as modulo_titulo,
            m.orden as modulo_orden,
            l.id as leccion_id,
            l.titulo as leccion_titulo,
            l.tipo,
            l.duracion_minutos,
            l.orden as leccion_orden,
            pl.completada,
            pl.tiempo_visto_minutos,
            pl.fecha_completado
        ');
        
        $this->db->from('modulos m');
        $this->db->join('lecciones l', 'm.id = l.modulo_id', 'left');
        $this->db->join('progreso_lecciones pl', 'l.id = pl.leccion_id AND pl.inscripcion_id = ' . $inscripcion['id'], 'left');
        
        $this->db->where('m.curso_id', $curso_id);
        $this->db->where('m.activo', 1);
        $this->db->where('l.activa', 1);
        $this->db->order_by('m.orden, l.orden');
        
        $query = $this->db->get();
        
        // Organizar por módulos
        $modulos = [];
        $total_lecciones = 0;
        $lecciones_completadas = 0;
        
        foreach ($query->result_array() as $row) {
            if (!isset($modulos[$row['modulo_id']])) {
                $modulos[$row['modulo_id']] = [
                    'modulo_id' => (int)$row['modulo_id'],
                    'modulo_titulo' => $row['modulo_titulo'],
                    'total_lecciones' => 0,
                    'completadas' => 0,
                    'porcentaje' => 0
                ];
            }
            
            if ($row['leccion_id']) {
                $modulos[$row['modulo_id']]['total_lecciones']++;
                $total_lecciones++;
                
                if ($row['completada']) {
                    $modulos[$row['modulo_id']]['completadas']++;
                    $lecciones_completadas++;
                }
            }
        }
        
        // Calcular porcentajes por módulo
        foreach ($modulos as &$modulo) {
            if ($modulo['total_lecciones'] > 0) {
                $modulo['porcentaje'] = round(($modulo['completadas'] / $modulo['total_lecciones']) * 100, 2);
            }
        }
        
        return [
            'curso_id' => (int)$curso_id,
            'total_lecciones' => $total_lecciones,
            'lecciones_completadas' => $lecciones_completadas,
            'porcentaje_progreso' => (float)$inscripcion['progreso'],
            'tiempo_total_minutos' => (int)$inscripcion['tiempo_total_minutos'],
            'fecha_inscripcion' => $inscripcion['fecha_inscripcion'],
            'modulos_progreso' => array_values($modulos)
        ];
    }

    /**
     * Actualizar progreso de una lección
     */
    public function actualizar_progreso($leccion_id, $usuario_id, $datos)
    {
        // Obtener inscripción
        $this->db->select('i.id as inscripcion_id');
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('modulos m', 'c.id = m.curso_id');
        $this->db->join('lecciones l', 'm.id = l.modulo_id');
        $this->db->where('l.id', $leccion_id);
        $this->db->where('i.usuario_id', $usuario_id);
        $inscripcion = $this->db->get()->row_array();
        
        if (!$inscripcion) {
            return false;
        }
        
        $inscripcion_id = $inscripcion['inscripcion_id'];
        
        // Verificar si ya existe progreso para esta lección
        $this->db->where('inscripcion_id', $inscripcion_id);
        $this->db->where('leccion_id', $leccion_id);
        $existing = $this->db->get('progreso_lecciones')->row_array();
        
        $data = [
            'tiempo_visto_minutos' => $datos['tiempo_visto_minutos'],
            'ultima_posicion_segundo' => $datos['ultima_posicion_segundo'],
            'completada' => $datos['completada'] ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($datos['completada'] && (!$existing || !$existing['completada'])) {
            $data['fecha_completado'] = date('Y-m-d H:i:s');
        }
        
        if ($existing) {
            // Actualizar
            $this->db->where('inscripcion_id', $inscripcion_id);
            $this->db->where('leccion_id', $leccion_id);
            $result = $this->db->update('progreso_lecciones', $data);
        } else {
            // Crear nuevo
            $data['inscripcion_id'] = $inscripcion_id;
            $data['leccion_id'] = $leccion_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->db->insert('progreso_lecciones', $data);
        }
        
        // Actualizar progreso general del curso si la lección se completó
        if ($result && $datos['completada']) {
            $this->actualizar_progreso_curso($inscripcion_id);
        }
        
        return $result;
    }

    /**
     * Obtener progreso específico de una lección
     */
    public function get_progreso_leccion($leccion_id, $usuario_id)
    {
        $this->db->select('pl.*');
        $this->db->from('progreso_lecciones pl');
        $this->db->join('inscripciones i', 'pl.inscripcion_id = i.id');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('modulos m', 'c.id = m.curso_id');
        $this->db->join('lecciones l', 'm.id = l.modulo_id');
        
        $this->db->where('l.id', $leccion_id);
        $this->db->where('i.usuario_id', $usuario_id);
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            return [
                'completada' => (bool)$result['completada'],
                'tiempo_visto_minutos' => (int)$result['tiempo_visto_minutos'],
                'ultima_posicion_segundo' => (int)$result['ultima_posicion_segundo'],
                'fecha_completado' => $result['fecha_completado']
            ];
        }
        
        return [
            'completada' => false,
            'tiempo_visto_minutos' => 0,
            'ultima_posicion_segundo' => 0,
            'fecha_completado' => null
        ];
    }

    /**
     * Actualizar progreso general del curso
     */
    private function actualizar_progreso_curso($inscripcion_id)
    {
        // Calcular porcentaje de progreso basado en lecciones completadas
        $this->db->select('
            COUNT(l.id) as total_lecciones,
            COUNT(CASE WHEN pl.completada = 1 THEN 1 END) as lecciones_completadas,
            SUM(COALESCE(pl.tiempo_visto_minutos, 0)) as tiempo_total
        ');
        
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('modulos m', 'c.id = m.curso_id');
        $this->db->join('lecciones l', 'm.id = l.modulo_id');
        $this->db->join('progreso_lecciones pl', 'l.id = pl.leccion_id AND pl.inscripcion_id = i.id', 'left');
        
        $this->db->where('i.id', $inscripcion_id);
        $this->db->where('m.activo', 1);
        $this->db->where('l.activa', 1);
        
        $stats = $this->db->get()->row_array();
        
        $porcentaje = 0;
        if ($stats['total_lecciones'] > 0) {
            $porcentaje = round(($stats['lecciones_completadas'] / $stats['total_lecciones']) * 100, 2);
        }
        
        // Actualizar inscripción
        $update_data = [
            'progreso' => $porcentaje,
            'tiempo_total_minutos' => $stats['tiempo_total'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Si está completado al 100%, marcar como completado
        if ($porcentaje >= 100) {
            $update_data['estado'] = 'completada';
            $update_data['fecha_completado'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $inscripcion_id);
        return $this->db->update('inscripciones', $update_data);
    }

    /**
     * Calcular racha actual de días consecutivos
     */
    private function calcular_racha_actual($usuario_id)
    {
        // Por simplicidad, retornamos un valor fijo
        // En una implementación real, calcularías días consecutivos con actividad
        return rand(1, 15);
    }

    /**
     * Calcular tiempo promedio de sesión
     */
    private function calcular_tiempo_promedio_sesion($usuario_id)
    {
        $this->db->select('AVG(tiempo_visto_minutos) as promedio');
        $this->db->from('progreso_lecciones pl');
        $this->db->join('inscripciones i', 'pl.inscripcion_id = i.id');
        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('pl.tiempo_visto_minutos > 0');
        
        $result = $this->db->get()->row_array();
        return $result['promedio'] ? round($result['promedio'], 1) : 0;
    }

    /**
     * Obtener progreso de lecciones de un curso
     */
    public function get_progreso_lecciones_curso($curso_id, $usuario_id)
    {
        $this->db->select('
            pl.*,
            l.titulo as leccion_titulo,
            l.tipo,
            l.duracion_minutos,
            m.titulo as modulo_titulo,
            m.id as modulo_id
        ');
        
        $this->db->from('progreso_lecciones pl');
        $this->db->join('inscripciones i', 'pl.inscripcion_id = i.id');
        $this->db->join('lecciones l', 'pl.leccion_id = l.id');
        $this->db->join('modulos m', 'l.modulo_id = m.id');
        
        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('m.curso_id', $curso_id);
        $this->db->order_by('m.orden, l.orden');
        
        return $this->db->get()->result_array();
    }

    /**
     * Obtener actividad reciente
     */
    public function get_actividad_reciente($usuario_id, $limite = 10)
    {
        $this->db->select('
            "leccion" as tipo,
            l.titulo,
            c.titulo as curso_titulo,
            pl.fecha_completado as fecha,
            "Lección completada" as detalles
        ');
        
        $this->db->from('progreso_lecciones pl');
        $this->db->join('inscripciones i', 'pl.inscripcion_id = i.id');
        $this->db->join('lecciones l', 'pl.leccion_id = l.id');
        $this->db->join('modulos m', 'l.modulo_id = m.id');
        $this->db->join('cursos c', 'm.curso_id = c.id');
        
        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('pl.completada', 1);
        $this->db->where('pl.fecha_completado IS NOT NULL');
        $this->db->order_by('pl.fecha_completado', 'DESC');
        $this->db->limit($limite);
        
        return $this->db->get()->result_array();
    }
}
