<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Evaluacion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function start_evaluation($user_id, $evaluation_id)
    {
        // Verificar si puede tomar la evaluación
        $evaluation = $this->get_evaluation($evaluation_id);
        if (!$evaluation) {
            return false;
        }

        // Contar intentos previos
        $this->db->where('usuario_id', $user_id);
        $this->db->where('evaluacion_id', $evaluation_id);
        $attempts_count = $this->db->count_all_results('intentos_evaluacion');

        if ($evaluation['intentos_permitidos'] > 0 && $attempts_count >= $evaluation['intentos_permitidos']) {
            return false; // Ya agotó los intentos
        }

        // Crear nuevo intento
        $attempt_data = [
            'evaluacion_id' => $evaluation_id,
            'usuario_id' => $user_id,
            'fecha_inicio' => date('Y-m-d H:i:s'),
            'estado' => 'en_progreso'
        ];

        $this->db->insert('intentos_evaluacion', $attempt_data);
        return $this->db->insert_id();
    }

    public function get_evaluation($evaluation_id)
    {
        return $this->db->get_where('evaluaciones', ['id' => $evaluation_id])->row_array();
    }

    public function get_evaluation_questions($evaluation_id)
    {
        $this->db->select('p.*, GROUP_CONCAT(CONCAT(o.id, ":", o.texto_opcion) SEPARATOR "|") as opciones');
        $this->db->from('preguntas p');
        $this->db->join('opciones_respuesta o', 'p.id = o.pregunta_id', 'left');
        $this->db->where('p.evaluacion_id', $evaluation_id);
        $this->db->group_by('p.id');
        $this->db->order_by('p.orden');

        $questions = $this->db->get()->result_array();

        // Formatear opciones
        foreach ($questions as &$question) {
            if ($question['opciones']) {
                $options = [];
                $opciones_raw = explode('|', $question['opciones']);
                foreach ($opciones_raw as $opcion) {
                    list($id, $texto) = explode(':', $opcion, 2);
                    $options[] = ['id' => $id, 'texto' => $texto];
                }
                $question['opciones'] = $options;
            } else {
                $question['opciones'] = [];
            }
        }

        return $questions;
    }

    public function submit_evaluation($attempt_id, $answers)
    {
        // Obtener intento
        $attempt = $this->db->get_where('intentos_evaluacion', ['id' => $attempt_id])->row_array();
        if (!$attempt || $attempt['estado'] !== 'en_progreso') {
            return false;
        }

        // Obtener evaluación
        $evaluation = $this->get_evaluation($attempt['evaluacion_id']);
        if (!$evaluation) {
            return false;
        }

        // Calcular puntuación
        $score = $this->calculate_score($attempt['evaluacion_id'], $answers);
        $percentage = $score['percentage'];
        $passed = $percentage >= $evaluation['nota_minima_aprobacion'];

        // Actualizar intento
        $update_data = [
            'fecha_fin' => date('Y-m-d H:i:s'),
            'puntuacion' => $score['points'],
            'porcentaje' => $percentage,
            'aprobado' => $passed ? 1 : 0,
            'respuestas' => json_encode($answers),
            'estado' => 'completado'
        ];

        $this->db->where('id', $attempt_id);
        $this->db->update('intentos_evaluacion', $update_data);

        return [
            'score' => $score['points'],
            'percentage' => $percentage,
            'passed' => $passed,
            'max_score' => $score['max_points']
        ];
    }

    private function calculate_score($evaluation_id, $answers)
    {
        $this->db->select('p.id, p.puntos, GROUP_CONCAT(CASE WHEN o.es_correcta = 1 THEN o.id END) as correct_options');
        $this->db->from('preguntas p');
        $this->db->join('opciones_respuesta o', 'p.id = o.pregunta_id', 'left');
        $this->db->where('p.evaluacion_id', $evaluation_id);
        $this->db->group_by('p.id');

        $questions = $this->db->get()->result_array();

        $total_points = 0;
        $max_points = 0;

        foreach ($questions as $question) {
            $max_points += $question['puntos'];
            
            $question_id = $question['id'];
            $correct_options = explode(',', $question['correct_options']);
            $correct_options = array_filter($correct_options);

            if (isset($answers[$question_id])) {
                $user_answer = $answers[$question_id];
                
                // Verificar si la respuesta es correcta
                if (is_array($user_answer)) {
                    // Respuesta múltiple
                    $correct = array_diff($correct_options, $user_answer) === array_diff($user_answer, $correct_options);
                } else {
                    // Respuesta única
                    $correct = in_array($user_answer, $correct_options);
                }

                if ($correct) {
                    $total_points += $question['puntos'];
                }
            }
        }

        return [
            'points' => $total_points,
            'max_points' => $max_points,
            'percentage' => $max_points > 0 ? ($total_points / $max_points) * 100 : 0
        ];
    }
}
