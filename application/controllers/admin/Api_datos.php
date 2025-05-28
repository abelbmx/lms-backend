<?php
defined('BASEPATH') or exit('No direct script access allowed');

use OAuth2\Storage\ClientCredentialsInterface;

class Api_datos extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/Api_accesos_model');
        $this->load->model('admin/Tokens_model'); // Cargar el modelo de tokens
        $this->load->library('form_validation');
    }

    // Listar todas las credenciales API
    public function index()
    {
        if (! $this->ion_auth->logged_in() or ! $this->ion_auth->is_admin()) {
            redirect('auth/login', 'refresh');
        } else {
            /* Título de la página */
            $this->page_title->push('Enviar datos de API');
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs */
            $this->breadcrumbs->unshift(1, 'Enviar datos de API', 'admin/api_datos');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            /* Obtener todos los clientes API */
            $this->data['api_clients'] = $this->Api_accesos_model->get_all_clients();

            /* Obtener historial de tokens para cada cliente */
            foreach ($this->data['api_clients'] as &$client) {
                $client->tokens = $this->Tokens_model->get_tokens_by_client($client->client_id);
            }

            /* Cargar la vista */
            $this->template->admin_render('admin/api_datos/index', $this->data);
        }
    }
}
