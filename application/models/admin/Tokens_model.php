<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tokens_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        // Cargar la base de datos
        $this->load->database();
    }

    /**
     * Obtiene todos los tokens asociados a un client_id específico.
     *
     * @param string $client_id
     * @return array
     */
    public function get_tokens_by_client($client_id)
    {
        $this->db->where('client_id', $client_id);
        $query = $this->db->get('oauth_access_tokens');
        return $query->result_array();
    }

    /**
     * Obtiene todos los tokens emitidos.
     *
     * @return array
     */
    public function get_all_tokens()
    {
        $query = $this->db->get('oauth_access_tokens');
        return $query->result_array();
    }

    // Puedes añadir más métodos para filtrar tokens, contar usos, etc.
}
