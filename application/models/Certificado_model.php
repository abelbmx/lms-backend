<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Certificado_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function generate_certificate($user_id, $course_id)
    {
        // Verificar si el usuario completó el curso
        $enrollment = $this->db->get_where('inscripciones', [
            'usuario_id' => $user_id,
            'curso_id' => $course_id,
            'estado' => 'completada'
        ])->row_array();

        if (!$enrollment) {
            return false; // No ha completado el curso
        }

        // Verificar si ya existe un certificado
        $existing_certificate = $this->db->get_where('certificados', [
            'inscripcion_id' => $enrollment['id']
        ])->row_array();

        if ($existing_certificate) {
            return $existing_certificate;
        }

        // Generar código único del certificado
        $certificate_code = $this->generate_certificate_code();

        // Crear certificado
        $certificate_data = [
            'inscripcion_id' => $enrollment['id'],
            'codigo_certificado' => $certificate_code,
            'fecha_emision' => date('Y-m-d H:i:s'),
            'verificado' => 1,
            'plantilla_utilizada' => 'default'
        ];

        $this->db->insert('certificados', $certificate_data);
        $certificate_id = $this->db->insert_id();

        // Actualizar inscripción
        $this->db->where('id', $enrollment['id']);
        $this->db->update('inscripciones', [
            'certificado_emitido' => 1,
            'fecha_certificado' => date('Y-m-d H:i:s')
        ]);

        // Obtener datos completos del certificado
        return $this->get_certificate_details($certificate_id);
    }

    public function get_certificate_details($certificate_id)
    {
        $this->db->select('c.*, i.fecha_completado, u.nombre, u.apellido, cu.titulo as curso_titulo, inst.nombre as instructor_nombre, inst.apellido as instructor_apellido');
        $this->db->from('certificados c');
        $this->db->join('inscripciones i', 'c.inscripcion_id = i.id');
        $this->db->join('usuarios u', 'i.usuario_id = u.id');
        $this->db->join('cursos cu', 'i.curso_id = cu.id');
        $this->db->join('usuarios inst', 'cu.instructor_id = inst.id');
        $this->db->where('c.id', $certificate_id);

        return $this->db->get()->row_array();
    }

    public function verify_certificate($certificate_code)
    {
        return $this->db->get_where('certificados', [
            'codigo_certificado' => $certificate_code,
            'verificado' => 1
        ])->row_array();
    }

    private function generate_certificate_code()
    {
        do {
            $code = 'CERT-' . strtoupper(uniqid()) . '-' . date('Y');
        } while ($this->certificate_code_exists($code));

        return $code;
    }

    private function certificate_code_exists($code)
    {
        $this->db->where('codigo_certificado', $code);
        return $this->db->count_all_results('certificados') > 0;
    }
}
