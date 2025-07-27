<?php
// application/models/Inscripcion_model.php - VERSION SIMPLIFICADA
defined('BASEPATH') or exit('No direct script access allowed');

class Inscripcion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener inscripciones de un usuario con filtros opcionales
     */
    public function get_inscripciones_usuario($usuario_id, $filtros = [])
    {
        $this->db->select('
            i.*,
            c.id as curso_id,
            c.titulo as curso_titulo,
            c.descripcion_corta,
            c.imagen_portada,
            c.nivel,
            c.duracion_horas,
            c.calificacion_promedio,
            c.precio,
            c.precio_oferta,
            c.instructor_id,
            cat.nombre as categoria_nombre
        ');
        
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id', 'left');
        $this->db->join('categorias cat', 'c.categoria_id = cat.id', 'left');
        
        $this->db->where('i.usuario_id', $usuario_id);
        
        // Aplicar filtros
        if (isset($filtros['estado'])) {
            $this->db->where('i.estado', $filtros['estado']);
        }
        
        if (isset($filtros['categoria'])) {
            $this->db->where('cat.slug', $filtros['categoria']);
        }
        
        if (isset($filtros['nivel'])) {
            $this->db->where('c.nivel', $filtros['nivel']);
        }
        
        // Ordenamiento
        $orden = isset($filtros['orden']) ? $filtros['orden'] : 'fecha_inscripcion';
        $direccion = isset($filtros['direccion']) ? $filtros['direccion'] : 'desc';
        $this->db->order_by("i.{$orden}", $direccion);
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->result_array();
            
            // Formatear datos para el frontend
            foreach ($result as &$inscripcion) {
                $inscripcion['curso'] = [
                    'id' => $inscripcion['curso_id'],
                    'titulo' => $inscripcion['curso_titulo'],
                    'descripcion_corta' => $inscripcion['descripcion_corta'],
                    'imagen_portada' => $inscripcion['imagen_portada'],
                    'categoria' => $inscripcion['categoria_nombre'] ?: 'General',
                    'nivel' => $inscripcion['nivel'],
                    'instructor' => 'Instructor', // Por ahora fijo para evitar errores
                    'duracion_horas' => (int)$inscripcion['duracion_horas'],
                    'calificacion_promedio' => (float)$inscripcion['calificacion_promedio'],
                    'precio' => (float)$inscripcion['precio'],
                    'precio_oferta' => $inscripcion['precio_oferta'] ? (float)$inscripcion['precio_oferta'] : null
                ];
                
                // IMPORTANTE: Mantener curso_id en el nivel superior para el frontend
                // NO eliminar curso_id del array principal
                
                // Limpiar solo los campos duplicados, NO curso_id
                unset($inscripcion['curso_titulo'], 
                     $inscripcion['descripcion_corta'], $inscripcion['imagen_portada'],
                     $inscripcion['nivel'], $inscripcion['duracion_horas'],
                     $inscripcion['calificacion_promedio'], $inscripcion['precio'],
                     $inscripcion['precio_oferta'], $inscripcion['categoria_nombre'],
                     $inscripcion['instructor_id']);
            }
            
            return $result;
        }
        
        return [];
    }

    /**
     * Obtener inscripción específica
     */
    public function get_inscripcion($inscripcion_id)
    {
        $this->db->where('id', $inscripcion_id);
        $query = $this->db->get('inscripciones');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Obtener inscripción con detalles completos
     */
    public function get_inscripcion_detalle($inscripcion_id)
    {
        $this->db->select('
            i.*,
            c.*,
            cat.nombre as categoria_nombre
        ');
        
        $this->db->from('inscripciones i');
        $this->db->join('cursos c', 'i.curso_id = c.id');
        $this->db->join('categorias cat', 'c.categoria_id = cat.id', 'left');
        
        $this->db->where('i.id', $inscripcion_id);
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            
            // Formatear respuesta
            $inscripcion = [
                'id' => $result['id'],
                'usuario_id' => $result['usuario_id'],
                'curso_id' => $result['curso_id'],
                'fecha_inscripcion' => $result['fecha_inscripcion'],
                'fecha_completado' => $result['fecha_completado'],
                'progreso' => (float)$result['progreso'],
                'estado' => $result['estado'],
                'calificacion_final' => $result['calificacion_final'] ? (float)$result['calificacion_final'] : null,
                'tiempo_total_minutos' => (int)$result['tiempo_total_minutos'],
                'certificado_emitido' => (bool)$result['certificado_emitido'],
                'fecha_certificado' => $result['fecha_certificado'],
                'metodo_pago' => $result['metodo_pago'],
                'monto_pagado' => (float)$result['monto_pagado'],
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at'],
                'curso_completo' => [
                    'id' => $result['curso_id'],
                    'titulo' => $result['titulo'],
                    'descripcion_corta' => $result['descripcion_corta'],
                    'descripcion_larga' => $result['descripcion_larga'],
                    'imagen_portada' => $result['imagen_portada'],
                    'categoria' => $result['categoria_nombre'] ?: 'General',
                    'nivel' => $result['nivel'],
                    'instructor' => 'Instructor', // Por ahora fijo
                    'duracion_horas' => (int)$result['duracion_horas'],
                    'precio' => (float)$result['precio'],
                    'calificacion_promedio' => (float)$result['calificacion_promedio']
                ]
            ];
            
            return $inscripcion;
        }
        
        return null;
    }

    /**
     * Verificar si un usuario está inscrito en un curso
     */
    public function verificar_inscripcion($usuario_id, $curso_id)
    {
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('curso_id', $curso_id);
        $query = $this->db->get('inscripciones');
        
        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
        
        return null;
    }

    /**
     * Crear nueva inscripción
     */
    public function crear_inscripcion($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if ($this->db->insert('inscripciones', $data)) {
            return $this->db->insert_id();
        }
        
        return false;
    }

    /**
     * Actualizar inscripción
     */
    public function actualizar_inscripcion($inscripcion_id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $inscripcion_id);
        return $this->db->update('inscripciones', $data);
    }

    /**
     * Cancelar inscripción
     */
    public function cancelar_inscripcion($inscripcion_id, $motivo = null)
    {
        $data = [
            'estado' => 'cancelada',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($motivo) {
            $data['motivo_cancelacion'] = $motivo;
        }
        
        $this->db->where('id', $inscripcion_id);
        return $this->db->update('inscripciones', $data);
    }

    /**
     * Reactivar inscripción
     */
    public function reactivar_inscripcion($inscripcion_id)
    {
        $data = [
            'estado' => 'activa',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $inscripcion_id);
        return $this->db->update('inscripciones', $data);
    }

    /**
     * Completar curso
     */
    public function completar_curso($inscripcion_id)
    {
        $data = [
            'estado' => 'completada',
            'fecha_completado' => date('Y-m-d H:i:s'),
            'progreso' => 100,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $inscripcion_id);
        return $this->db->update('inscripciones', $data);
    }

    /**
     * Obtener progreso detallado de una inscripción
     */
    public function get_progreso_detallado($inscripcion_id)
    {
        // Obtener información básica de la inscripción
        $inscripcion = $this->get_inscripcion($inscripcion_id);
        if (!$inscripcion) {
            return null;
        }

        // Por ahora retornamos estructura básica
        return [
            'inscripcion' => $inscripcion,
            'total_lecciones' => 0,
            'lecciones_completadas' => 0,
            'porcentaje_progreso' => (float)$inscripcion['progreso'],
            'tiempo_total_minutos' => (int)$inscripcion['tiempo_total_minutos'],
            'progreso_lecciones' => []
        ];
    }

    /**
     * Obtener estadísticas del usuario
     */
    public function get_estadisticas_usuario($usuario_id)
    {
        // Contar inscripciones por estado
        $this->db->select('estado, COUNT(*) as cantidad');
        $this->db->from('inscripciones');
        $this->db->where('usuario_id', $usuario_id);
        $this->db->group_by('estado');
        $query = $this->db->get();
        
        $estados = [];
        foreach ($query->result_array() as $row) {
            $estados[$row['estado']] = (int)$row['cantidad'];
        }

        // Calcular tiempo total y progreso promedio
        $this->db->select('
            AVG(progreso) as progreso_promedio,
            SUM(tiempo_total_minutos) as tiempo_total
        ');
        $this->db->from('inscripciones');
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('estado !=', 'cancelada');
        $query = $this->db->get();
        $totales = $query->row_array();

        // Contar certificados
        $this->db->from('inscripciones');
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('certificado_emitido', 1);
        $certificados = $this->db->count_all_results();

        return [
            'total' => array_sum($estados),
            'activas' => isset($estados['activa']) ? $estados['activa'] : 0,
            'completadas' => isset($estados['completada']) ? $estados['completada'] : 0,
            'canceladas' => isset($estados['cancelada']) ? $estados['cancelada'] : 0,
            'progreso_promedio' => $totales['progreso_promedio'] ? round($totales['progreso_promedio'], 2) : 0,
            'tiempo_total_horas' => $totales['tiempo_total'] ? round($totales['tiempo_total'] / 60, 2) : 0,
            'certificados_obtenidos' => $certificados
        ];
    }

    /**
     * Eliminar inscripción (soft delete)
     */
    public function eliminar_inscripcion($inscripcion_id)
    {
        $data = [
            'estado' => 'cancelada',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $inscripcion_id);
        return $this->db->update('inscripciones', $data);
    }
}
