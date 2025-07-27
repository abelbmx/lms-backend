<?php
// application/models/Evaluacion_model.php
defined('BASEPATH') or exit('No direct script access allowed');

class Evaluacion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Obtener evaluaciones pendientes del usuario
     */
    public function get_evaluaciones_pendientes($usuario_id, $curso_id = null)
    {
        $this->db->select('
            e.*,
            c.titulo as curso_titulo,
            l.titulo as leccion_titulo,
            COUNT(ie.id) as intentos_realizados,
            MAX(ie.porcentaje) as mejor_puntuacion
        ');

        $this->db->from('evaluaciones e');
        $this->db->join('inscripciones i', 'e.curso_id = i.curso_id');
        $this->db->join('cursos c', 'e.curso_id = c.id', 'left');
        $this->db->join('lecciones l', 'e.leccion_id = l.id', 'left');
        $this->db->join('intentos_evaluacion ie', 'e.id = ie.evaluacion_id AND ie.usuario_id = i.usuario_id', 'left');

        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('i.estado', 'activa');
        $this->db->where('e.activa', 1);

        if ($curso_id) {
            $this->db->where('e.curso_id', $curso_id);
        }

        $this->db->group_by('e.id');
        $this->db->having('COUNT(ie.id) < e.intentos_permitidos OR e.intentos_permitidos = 0');
        $this->db->order_by('e.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Obtener evaluaciones por curso
     */
    public function get_evaluaciones_por_curso($curso_id, $usuario_id, $incluir_progreso = true)
    {
        $this->db->select('e.*, c.titulo as curso_titulo, l.titulo as leccion_titulo');
        $this->db->from('evaluaciones e');
        $this->db->join('cursos c', 'e.curso_id = c.id', 'left');
        $this->db->join('lecciones l', 'e.leccion_id = l.id', 'left');

        $this->db->where('e.curso_id', $curso_id);
        $this->db->where('e.activa', 1);
        $this->db->order_by('e.orden, e.created_at');

        $evaluaciones = $this->db->get()->result_array();

        if ($incluir_progreso && $usuario_id) {
            foreach ($evaluaciones as &$evaluacion) {
                $progreso = $this->get_progreso_evaluacion($evaluacion['id'], $usuario_id);
                $evaluacion = array_merge($evaluacion, $progreso);
            }
        }

        return $evaluaciones;
    }

    /**
     * Obtener evaluación completa
     */
    public function get_evaluacion_completa($evaluacion_id, $usuario_id = null)
    {
        $this->db->select('e.*, c.titulo as curso_titulo, l.titulo as leccion_titulo');
        $this->db->from('evaluaciones e');
        $this->db->join('cursos c', 'e.curso_id = c.id', 'left');
        $this->db->join('lecciones l', 'e.leccion_id = l.id', 'left');
        $this->db->where('e.id', $evaluacion_id);

        $evaluacion = $this->db->get()->row_array();

        if ($evaluacion && $usuario_id) {
            $progreso = $this->get_progreso_evaluacion($evaluacion_id, $usuario_id);
            $evaluacion = array_merge($evaluacion, $progreso);
        }

        return $evaluacion;
    }

    /**
     * Obtener evaluación básica
     */
    public function get_evaluacion($evaluacion_id)
    {
        $this->db->where('id', $evaluacion_id);
        return $this->db->get('evaluaciones')->row_array();
    }

    /**
     * Verificar acceso del usuario a la evaluación
     */
    public function verificar_acceso($evaluacion_id, $usuario_id)
    {
        $this->db->select('1');
        $this->db->from('evaluaciones e');
        $this->db->join('inscripciones i', 'e.curso_id = i.curso_id');
        $this->db->where('e.id', $evaluacion_id);
        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('i.estado', 'activa');
        $this->db->where('e.activa', 1);

        return $this->db->get()->num_rows() > 0;
    }

    /**
     * Verificar si puede tomar la evaluación
     */
    public function puede_tomar_evaluacion($evaluacion_id, $usuario_id)
    {
        $evaluacion = $this->get_evaluacion($evaluacion_id);
        if (!$evaluacion) {
            return ['puede_tomar' => false, 'motivo' => 'Evaluación no encontrada'];
        }

        // Verificar acceso
        if (!$this->verificar_acceso($evaluacion_id, $usuario_id)) {
            return ['puede_tomar' => false, 'motivo' => 'No tienes acceso a esta evaluación'];
        }

        // Contar intentos realizados
        $this->db->where('evaluacion_id', $evaluacion_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('estado !=', 'abandonado');
        $intentos = $this->db->count_all_results('intentos_evaluacion');

        if ($evaluacion['intentos_permitidos'] > 0 && $intentos >= $evaluacion['intentos_permitidos']) {
            return [
                'puede_tomar' => false,
                'motivo' => 'Has agotado el número de intentos permitidos',
                'intentos_restantes' => 0
            ];
        }

        // Verificar si hay un intento en progreso
        $this->db->where('evaluacion_id', $evaluacion_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('estado', 'en_progreso');
        $intento_progreso = $this->db->get('intentos_evaluacion')->row_array();

        if ($intento_progreso) {
            return ['puede_tomar' => false, 'motivo' => 'Ya tienes un intento en progreso'];
        }

        $intentos_restantes = $evaluacion['intentos_permitidos'] > 0 ?
            $evaluacion['intentos_permitidos'] - $intentos : null;

        return [
            'puede_tomar' => true,
            'intentos_restantes' => $intentos_restantes
        ];
    }

    /**
     * Obtener progreso de evaluación para un usuario
     */
    private function get_progreso_evaluacion($evaluacion_id, $usuario_id)
    {
        $this->db->select('
            COUNT(*) as intentos_realizados,
            MAX(porcentaje) as mejor_puntuacion,
            MAX(fecha_fin) as ultimo_intento
        ');
        $this->db->from('intentos_evaluacion');
        $this->db->where('evaluacion_id', $evaluacion_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('estado', 'completado');

        $progreso = $this->db->get()->row_array();

        $puede_tomar = $this->puede_tomar_evaluacion($evaluacion_id, $usuario_id);

        return [
            'intentos_realizados' => (int)$progreso['intentos_realizados'],
            'mejor_puntuacion' => $progreso['mejor_puntuacion'] ? (float)$progreso['mejor_puntuacion'] : null,
            'ultimo_intento' => $progreso['ultimo_intento'],
            'puede_tomar' => $puede_tomar['puede_tomar'],
            'estado_usuario' => $this->determinar_estado_usuario($progreso, $puede_tomar)
        ];
    }

    /**
     * Determinar estado de la evaluación para el usuario
     */
    private function determinar_estado_usuario($progreso, $puede_tomar)
    {
        if ($progreso['intentos_realizados'] == 0) {
            return 'no_iniciada';
        }

        if (!$puede_tomar['puede_tomar'] && strpos($puede_tomar['motivo'], 'progreso') !== false) {
            return 'en_progreso';
        }

        if ($progreso['mejor_puntuacion'] && $progreso['mejor_puntuacion'] >= 70) {
            return 'aprobada';
        } elseif ($progreso['intentos_realizados'] > 0) {
            return 'reprobada';
        }

        return 'completada';
    }

    /**
     * Crear nueva evaluación
     */
    public function crear_evaluacion($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert('evaluaciones', $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Obtener estadísticas de evaluaciones del usuario
     */
    public function get_estadisticas_usuario($usuario_id)
    {
        $this->db->select('
            COUNT(DISTINCT e.id) as total_evaluaciones,
            COUNT(DISTINCT ie.id) as completadas,
            COUNT(CASE WHEN ie.aprobado = 1 THEN 1 END) as aprobadas,
            COUNT(CASE WHEN ie.aprobado = 0 THEN 1 END) as reprobadas,
            AVG(ie.porcentaje) as promedio_calificacion,
            SUM(ie.tiempo_empleado_minutos) as tiempo_total_minutos
        ');

        $this->db->from('evaluaciones e');
        $this->db->join('inscripciones i', 'e.curso_id = i.curso_id');
        $this->db->join('intentos_evaluacion ie', 'e.id = ie.evaluacion_id AND ie.usuario_id = i.usuario_id', 'left');

        $this->db->where('i.usuario_id', $usuario_id);
        $this->db->where('i.estado', 'activa');
        $this->db->where('e.activa', 1);
        $this->db->where('ie.estado', 'completado');

        $result = $this->db->get()->row_array();

        return [
            'total_evaluaciones' => (int)$result['total_evaluaciones'],
            'completadas' => (int)$result['completadas'],
            'aprobadas' => (int)$result['aprobadas'],
            'reprobadas' => (int)$result['reprobadas'],
            'promedio_calificacion' => $result['promedio_calificacion'] ? round($result['promedio_calificacion'], 2) : 0,
            'tiempo_total_minutos' => (int)$result['tiempo_total_minutos']
        ];
    }
}

// =============================================================================
// application/models/Intento_evaluacion_model.php
// =============================================================================

class Intento_evaluacion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Crear nuevo intento
     */
    public function crear_intento($evaluacion_id, $usuario_id)
    {
        $data = [
            'evaluacion_id' => $evaluacion_id,
            'usuario_id' => $usuario_id,
            'fecha_inicio' => date('Y-m-d H:i:s'),
            'estado' => 'en_progreso'
        ];

        if ($this->db->insert('intentos_evaluacion', $data)) {
            return $this->db->insert_id();
        }

        return false;
    }

    /**
     * Obtener intento con preguntas
     */
    public function get_intento_con_preguntas($intento_id)
    {
        $intento = $this->get_intento($intento_id);
        if (!$intento) {
            return null;
        }

        // Obtener preguntas de la evaluación
        $this->db->select('p.*, GROUP_CONCAT(CONCAT(o.id, ":", o.texto_opcion, ":", o.es_correcta) SEPARATOR "|") as opciones');
        $this->db->from('preguntas p');
        $this->db->join('opciones_respuesta o', 'p.id = o.pregunta_id', 'left');
        $this->db->where('p.evaluacion_id', $intento['evaluacion_id']);
        $this->db->group_by('p.id');
        $this->db->order_by('p.orden');

        $preguntas = $this->db->get()->result_array();

        // Formatear opciones
        foreach ($preguntas as &$pregunta) {
            $opciones = [];
            if ($pregunta['opciones']) {
                $opciones_raw = explode('|', $pregunta['opciones']);
                foreach ($opciones_raw as $opcion_raw) {
                    list($id, $texto, $es_correcta) = explode(':', $opcion_raw);
                    $opciones[] = [
                        'id' => (int)$id,
                        'texto_opcion' => $texto,
                        'es_correcta' => (bool)$es_correcta
                    ];
                }
            }
            $pregunta['opciones'] = $opciones;
            unset($pregunta['opciones_raw']);
        }

        $intento['preguntas'] = $preguntas;
        return $intento;
    }

    /**
     * Obtener intento
     */
    public function get_intento($intento_id)
    {
        $this->db->where('id', $intento_id);
        return $this->db->get('intentos_evaluacion')->row_array();
    }

    /**
     * Obtener intento en progreso
     */
    public function get_intento_en_progreso($evaluacion_id, $usuario_id)
    {
        $this->db->where('evaluacion_id', $evaluacion_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('estado', 'en_progreso');
        return $this->db->get('intentos_evaluacion')->row_array();
    }

    /**
     * Procesar respuestas y calcular puntuación
     */
    public function procesar_respuestas($intento_id, $respuestas)
    {
        $intento = $this->get_intento($intento_id);
        if (!$intento || $intento['estado'] !== 'en_progreso') {
            return false;
        }

        // Obtener preguntas y respuestas correctas
        $this->db->select('p.id, p.puntos, o.id as opcion_id, o.es_correcta');
        $this->db->from('preguntas p');
        $this->db->join('opciones_respuesta o', 'p.id = o.pregunta_id');
        $this->db->where('p.evaluacion_id', $intento['evaluacion_id']);
        $preguntas_correctas = $this->db->get()->result_array();

        // Agrupar por pregunta
        $preguntas = [];
        foreach ($preguntas_correctas as $pc) {
            if (!isset($preguntas[$pc['id']])) {
                $preguntas[$pc['id']] = ['puntos' => $pc['puntos'], 'opciones' => []];
            }
            $preguntas[$pc['id']]['opciones'][$pc['opcion_id']] = $pc['es_correcta'];
        }

        // Calcular puntuación
        $puntos_obtenidos = 0;
        $puntos_totales = 0;

        foreach ($preguntas as $pregunta_id => $pregunta) {
            $puntos_totales += $pregunta['puntos'];

            if (isset($respuestas[$pregunta_id])) {
                $respuesta_usuario = $respuestas[$pregunta_id];

                // Para preguntas de opción múltiple
                if (is_array($respuesta_usuario)) {
                    $correctas = 0;
                    $total_opciones = count($pregunta['opciones']);

                    foreach ($respuesta_usuario as $opcion_id) {
                        if (isset($pregunta['opciones'][$opcion_id]) && $pregunta['opciones'][$opcion_id]) {
                            $correctas++;
                        }
                    }

                    if ($correctas > 0 && $correctas == array_sum($pregunta['opciones'])) {
                        $puntos_obtenidos += $pregunta['puntos'];
                    }
                } else {
                    // Para preguntas de una sola respuesta
                    if (isset($pregunta['opciones'][$respuesta_usuario]) && $pregunta['opciones'][$respuesta_usuario]) {
                        $puntos_obtenidos += $pregunta['puntos'];
                    }
                }
            }
        }

        $porcentaje = $puntos_totales > 0 ? ($puntos_obtenidos / $puntos_totales) * 100 : 0;

        // Obtener nota mínima de aprobación
        $this->db->select('nota_minima_aprobacion');
        $this->db->where('id', $intento['evaluacion_id']);
        $evaluacion = $this->db->get('evaluaciones')->row_array();
        $nota_minima = $evaluacion['nota_minima_aprobacion'] ?? 60;

        $aprobado = $porcentaje >= $nota_minima;

        $tiempo_empleado = 0;
        if ($intento['fecha_inicio']) {
            $inicio = new DateTime($intento['fecha_inicio']);
            $fin = new DateTime();
            $tiempo_empleado = $fin->diff($inicio)->i; // minutos
        }

        // Actualizar intento
        $update_data = [
            'fecha_fin' => date('Y-m-d H:i:s'),
            'puntuacion' => $puntos_obtenidos,
            'porcentaje' => round($porcentaje, 2),
            'aprobado' => $aprobado ? 1 : 0,
            'tiempo_empleado_minutos' => $tiempo_empleado,
            'respuestas' => json_encode($respuestas),
            'estado' => 'completado'
        ];

        $this->db->where('id', $intento_id);
        return $this->db->update('intentos_evaluacion', $update_data);
    }

    /**
     * Obtener resultado completo
     */
    public function get_resultado_completo($intento_id)
    {
        $intento = $this->get_intento($intento_id);
        if (!$intento) {
            return null;
        }

        // Obtener preguntas con respuestas
        $this->db->select('p.*, e.mostrar_resultados');
        $this->db->from('preguntas p');
        $this->db->join('evaluaciones e', 'p.evaluacion_id = e.id');
        $this->db->where('p.evaluacion_id', $intento['evaluacion_id']);
        $this->db->order_by('p.orden');
        $preguntas = $this->db->get()->result_array();

        $respuestas_usuario = json_decode($intento['respuestas'], true) ?: [];

        return [
            'intento' => $intento,
            'preguntas' => $preguntas,
            'respuestas_usuario' => $respuestas_usuario,
            'resumen' => [
                'puntos_obtenidos' => $intento['puntuacion'],
                'porcentaje' => $intento['porcentaje'],
                'aprobado' => (bool)$intento['aprobado'],
                'tiempo_empleado' => $intento['tiempo_empleado_minutos'] . ' minutos'
            ]
        ];
    }

    /**
     * Abandonar intento
     */
    public function abandonar_intento($intento_id)
    {
        $data = [
            'estado' => 'abandonado',
            'fecha_fin' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $intento_id);
        return $this->db->update('intentos_evaluacion', $data);
    }

    /**
     * Guardar progreso temporal
     */
    public function guardar_progreso($intento_id, $respuestas)
    {
        $data = [
            'respuestas' => json_encode($respuestas)
        ];

        $this->db->where('id', $intento_id);
        return $this->db->update('intentos_evaluacion', $data);
    }

    /**
     * Obtener progreso guardado
     */
    public function get_progreso_guardado($intento_id)
    {
        $this->db->select('respuestas');
        $this->db->where('id', $intento_id);
        $result = $this->db->get('intentos_evaluacion')->row_array();

        return [
            'respuestas' => $result ? json_decode($result['respuestas'], true) : null
        ];
    }

    /**
     * Obtener historial de intentos
     */
    public function get_historial_intentos($usuario_id, $evaluacion_id = null)
    {
        $this->db->select('ie.*, e.titulo as evaluacion_titulo');
        $this->db->from('intentos_evaluacion ie');
        $this->db->join('evaluaciones e', 'ie.evaluacion_id = e.id');
        $this->db->where('ie.usuario_id', $usuario_id);

        if ($evaluacion_id) {
            $this->db->where('ie.evaluacion_id', $evaluacion_id);
        }

        $this->db->order_by('ie.fecha_inicio', 'DESC');
        return $this->db->get()->result_array();
    }

    /**
     * Obtener resultados de evaluación
     */
    public function get_resultados_evaluacion($evaluacion_id, $usuario_id)
    {
        $this->db->where('evaluacion_id', $evaluacion_id);
        $this->db->where('usuario_id', $usuario_id);
        $this->db->where('estado', 'completado');
        $this->db->order_by('fecha_fin', 'DESC');
        return $this->db->get('intentos_evaluacion')->result_array();
    }

    /**
     * Obtener evaluaciones por instructor
     */
    public function get_evaluaciones_por_instructor($instructor_id)
    {
        $this->db->select('
        e.*,
        c.titulo as curso_titulo,
        c.instructor_id,
        COUNT(ie.id) as total_intentos,
        COUNT(CASE WHEN ie.estado = "completado" THEN 1 END) as intentos_completados
    ');

        $this->db->from('evaluaciones e');
        $this->db->join('cursos c', 'e.curso_id = c.id');
        $this->db->join('intentos_evaluacion ie', 'e.id = ie.evaluacion_id', 'left');

        $this->db->where('c.instructor_id', $instructor_id);
        $this->db->group_by('e.id');
        $this->db->order_by('e.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Obtener todas las evaluaciones (para administradores)
     */
    public function get_todas_evaluaciones()
    {
        $this->db->select('
        e.*,
        c.titulo as curso_titulo,
        u.nombre as instructor_nombre,
        COUNT(ie.id) as total_intentos
    ');

        $this->db->from('evaluaciones e');
        $this->db->join('cursos c', 'e.curso_id = c.id', 'left');
        $this->db->join('usuarios u', 'c.instructor_id = u.id', 'left');
        $this->db->join('intentos_evaluacion ie', 'e.id = ie.evaluacion_id', 'left');

        $this->db->group_by('e.id');
        $this->db->order_by('e.created_at', 'DESC');

        return $this->db->get()->result_array();
    }
}
