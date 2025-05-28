<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property OAuth2\Storage\ClientCredentialsInterface $clientCredentialsInterface
 */

 use OAuth2\Storage\ClientCredentialsInterface;

 class Api_accesos_model extends CI_Model implements ClientCredentialsInterface {

    private $table = 'oauth_clients';

    public function __construct()
    {
        parent::__construct();
    }

    // Obtener todos los clientes API
    public function get_all_clients()
    {
        $query = $this->db->get($this->table);
        return $query->result();
    }

    // Insertar un nuevo cliente API
    public function insert_client($data)
    {
        if ($this->db->insert($this->table, $data))
        {
            return true;
        }
        return FALSE;
    }

    // Eliminar un cliente API por client_id
    public function delete_client($client_id)
    {
        $this->db->where('client_id', $client_id);
        return $this->db->delete($this->table);
    }

    // Obtener un cliente API por client_id (requerido por OAuth2)
    public function getClient($client_id, $redirect_uri = NULL, $grant_type = NULL)
    {
        $this->db->where('client_id', $client_id);
        if ($redirect_uri !== NULL) {
            $this->db->where('redirect_uri', $redirect_uri);
        }
        $query = $this->db->get($this->table);
        $client = $query->row();

        if ($client)
        {
            return array(
                'client_id'     => $client->client_id,
                'client_secret' => $client->client_secret,
                'redirect_uri'  => $client->redirect_uri,
                'scope'         => $client->scope
            );
        }

        return false;
    }

    // Implementación de la interfaz ClientCredentialsInterface
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $this->db->where('client_id', $client_id);
        $query = $this->db->get($this->table);
        $client = $query->row();

        if ($client && password_verify($client_secret, $client->client_secret))
        {
            return true;
        }

        return false;
    }

    public function getClientScope($client_id)
    {
        $this->db->where('client_id', $client_id);
        $query = $this->db->get($this->table);
        $client = $query->row();

        if ($client)
        {
            return $client->scope;
        }

        return null;
    }

    public function isPublicClient($client_id)
    {
        // Implementa lógica para determinar si el cliente es público
        return false;
    }

    /**
     * Obtener detalles del cliente por client_id
     *
     * @param string $client_id
     * @return array|false
     */
    public function getClientDetails($client_id) {
        $this->db->where('client_id', $client_id);
        $query = $this->db->get('oauth_clients'); // Asegúrate de que el nombre de la tabla es correcto
        if ($query->num_rows() === 1) {
            return $query->row_array();
        }
        return false;
    }

    /**
     * Obtener detalles del cliente por public_id
     *
     * @param string $client_id
     * @return array|false
     */
    public function getClientDetailsByPublicId($client_id) {
        return $this->getClientDetails($client_id);
    }

    // Implementa otros métodos requeridos por la interfaz si es necesario
}
