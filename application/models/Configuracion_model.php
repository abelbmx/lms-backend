<?php
defined('BASEPATH') or exit('No direct script access allowed');

// =====================================================
// MODELO: Configuracion_model (Nuevo - para settings)
// =====================================================

class Configuracion_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_setting($key, $default = null)
    {
        $this->db->where('clave', $key);
        $setting = $this->db->get('configuracion')->row_array();
        
        if (!$setting) return $default;
        
        // Convertir según el tipo
        switch ($setting['tipo']) {
            case 'boolean':
                return $setting['valor'] === 'true' || $setting['valor'] === '1';
            case 'number':
                return is_numeric($setting['valor']) ? (float)$setting['valor'] : $default;
            case 'json':
                return json_decode($setting['valor'], true) ?: $default;
            default:
                return $setting['valor'];
        }
    }

    public function set_setting($key, $value, $description = '', $type = 'string')
    {
        // Convertir valor según tipo
        switch ($type) {
            case 'boolean':
                $value = $value ? 'true' : 'false';
                break;
            case 'number':
                $value = (string)$value;
                break;
            case 'json':
                $value = json_encode($value);
                break;
            default:
                $value = (string)$value;
        }

        $data = [
            'clave' => $key,
            'valor' => $value,
            'descripcion' => $description,
            'tipo' => $type,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Verificar si ya existe
        $existing = $this->db->get_where('configuracion', ['clave' => $key])->row();
        
        if ($existing) {
            $this->db->where('clave', $key);
            return $this->db->update('configuracion', $data);
        } else {
            return $this->db->insert('configuracion', $data);
        }
    }

    public function get_all_settings($group = null)
    {
        $this->db->select('*');
        $this->db->from('configuracion');
        
        if ($group) {
            $this->db->like('clave', $group . '_', 'after');
        }
        
        $this->db->order_by('clave', 'ASC');
        $settings = $this->db->get()->result_array();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['clave']] = $this->format_setting_value($setting);
        }
        
        return $result;
    }

    public function delete_setting($key)
    {
        $this->db->where('clave', $key);
        return $this->db->delete('configuracion');
    }

    public function get_settings_by_group($group)
    {
        $this->db->like('clave', $group . '_', 'after');
        $settings = $this->db->get('configuracion')->result_array();
        
        $result = [];
        foreach ($settings as $setting) {
            $clean_key = str_replace($group . '_', '', $setting['clave']);
            $result[$clean_key] = $this->format_setting_value($setting);
        }
        
        return $result;
    }

    private function format_setting_value($setting)
    {
        switch ($setting['tipo']) {
            case 'boolean':
                return $setting['valor'] === 'true' || $setting['valor'] === '1';
            case 'number':
                return is_numeric($setting['valor']) ? (float)$setting['valor'] : 0;
            case 'json':
                return json_decode($setting['valor'], true) ?: [];
            default:
                return $setting['valor'];
        }
    }

    // Métodos para configuraciones específicas del LMS
    public function get_site_settings()
    {
        return [
            'nombre' => $this->get_setting('sitio_nombre', 'Mi LMS'),
            'descripcion' => $this->get_setting('sitio_descripcion', 'Sistema de gestión de aprendizaje'),
            'email' => $this->get_setting('sitio_email', 'admin@milms.com'),
            'telefono' => $this->get_setting('sitio_telefono', ''),
            'direccion' => $this->get_setting('sitio_direccion', ''),
            'logo' => $this->get_setting('sitio_logo', ''),
            'favicon' => $this->get_setting('sitio_favicon', ''),
            'timezone' => $this->get_setting('sitio_timezone', 'America/Santiago'),
            'idioma' => $this->get_setting('idioma_defecto', 'es'),
            'moneda' => $this->get_setting('moneda_defecto', 'USD')
        ];
    }

    public function get_email_settings()
    {
        return [
            'smtp_host' => $this->get_setting('email_smtp_host', 'localhost'),
            'smtp_port' => $this->get_setting('email_smtp_port', 587, 'number'),
            'smtp_user' => $this->get_setting('email_smtp_user', ''),
            'smtp_pass' => $this->get_setting('email_smtp_pass', ''),
            'smtp_crypto' => $this->get_setting('email_smtp_crypto', 'tls'),
            'from_email' => $this->get_setting('email_from_email', 'noreply@milms.com'),
            'from_name' => $this->get_setting('email_from_name', 'Mi LMS')
        ];
    }

    public function get_course_settings()
    {
        return [
            'auto_approve' => $this->get_setting('curso_auto_aprobar', false, 'boolean'),
            'max_file_size' => $this->get_setting('curso_max_archivo_mb', 100, 'number'),
            'allowed_formats' => $this->get_setting('curso_formatos_permitidos', 'mp4,pdf,doc,docx,ppt,pptx', 'string'),
            'certificados_habilitados' => $this->get_setting('certificados_habilitados', true, 'boolean'),
            'calificacion_minima' => $this->get_setting('calificacion_minima_aprobacion', 70, 'number'),
            'max_intentos_evaluacion' => $this->get_setting('max_intentos_evaluacion', 3, 'number')
        ];
    }

    public function update_site_settings($settings)
    {
        $this->db->trans_start();
        
        foreach ($settings as $key => $value) {
            $this->set_setting('sitio_' . $key, $value);
        }
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function update_email_settings($settings)
    {
        $this->db->trans_start();
        
        foreach ($settings as $key => $value) {
            $this->set_setting('email_' . $key, $value);
        }
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
