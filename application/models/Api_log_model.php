<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api_log_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function insert_log($endpoint, $request_payload, $token_data)
    {
        // Primero verificar si la tabla existe
        if (!$this->db->table_exists('api_logs')) {
            $this->create_api_logs_table();
        }

        $log_data = [
            'endpoint' => $endpoint,
            'request_payload' => $request_payload,
            'token_data' => is_array($token_data) ? json_encode($token_data) : $token_data,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->insert('api_logs', $log_data);
    }

    private function create_api_logs_table()
    {
        $sql = "
        CREATE TABLE api_logs (
            id INT PRIMARY KEY AUTO_INCREMENT,
            endpoint VARCHAR(255) NOT NULL,
            request_payload LONGTEXT,
            token_data TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_endpoint (endpoint),
            INDEX idx_created_at (created_at)
        )";

        $this->db->query($sql);
    }

    public function get_logs($limit = 100, $offset = 0, $endpoint = null)
    {
        if ($endpoint) {
            $this->db->where('endpoint', $endpoint);
        }

        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);

        return $this->db->get('api_logs')->result_array();
    }
}
