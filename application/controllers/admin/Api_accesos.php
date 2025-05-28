<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use OAuth2\Storage\ClientCredentialsInterface;

class Api_accesos extends Admin_Controller {

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
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Título de la página */
            $this->page_title->push('Accesos API');
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs */
            $this->breadcrumbs->unshift(1, 'Accesos API', 'admin/api_accesos');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            /* Obtener todos los clientes API */
            $this->data['api_clients'] = $this->Api_accesos_model->get_all_clients();

            /* Obtener historial de tokens para cada cliente */
            foreach ($this->data['api_clients'] as &$client) {
                $client->tokens = $this->Tokens_model->get_tokens_by_client($client->client_id);
            }

            /* Cargar la vista */
            $this->template->admin_render('admin/api_accesos/index', $this->data);
        }
    }

    // Crear una nueva credencial API
    public function crear()
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            /* Título de la página */
            $this->page_title->push('Crear Acceso API');
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs */
            $this->breadcrumbs->unshift(1, 'Accesos API', 'admin/api_accesos');
            $this->breadcrumbs->unshift(2, 'Crear Acceso API', 'admin/api_accesos/crear');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();

            /* Validación del formulario */
            $this->form_validation->set_rules('redirect_uri', 'Redirect URI', 'trim|required|valid_url');
            $this->form_validation->set_rules('scope', 'Scope', 'trim');

            if ($this->form_validation->run() == TRUE)
            {
                // Generar client_id y client_secret
                $client_id = bin2hex(random_bytes(16)); // Genera un client_id de 32 caracteres hexadecimales
                $client_secret_plain = bin2hex(random_bytes(32)); // Genera un client_secret de 64 caracteres hexadecimales

                // Hashear el client_secret
                $client_secret_hashed = password_hash($client_secret_plain, PASSWORD_BCRYPT);

                $data = array(
                    'client_id' => $client_id,
                    'client_secret' => $client_secret_hashed,
                    'redirect_uri' => $this->input->post('redirect_uri'),
                    'scope' => $this->input->post('scope') ? $this->input->post('scope') : 'api1',
                    'grant_types' => 'client_credentials', // Puedes ajustar según los grant types que necesitas
                    'user_id' => NULL // Puedes asociar un user_id si es necesario
                );

                $insert_id = $this->Api_accesos_model->insert_client($data);

                if ($insert_id)
                {
                    // Mostrar las credenciales al usuario una vez creadas
                    $this->session->set_flashdata('message', 'Acceso API creado exitosamente.');
                    $this->data['new_client'] = array(
                        'client_id' => $client_id,
                        'client_secret' => $client_secret_hashed // Mostrar el client_secret hasheado
                    );
                    $this->template->admin_render('admin/api_accesos/crear', $this->data);
                }
                else
                {
                    $this->session->set_flashdata('error', 'Hubo un error al crear el acceso API.');
                    redirect('admin/api_accesos', 'refresh');
                }
            }
            else
            {
                /* Cargar la vista */
                $this->template->admin_render('admin/api_accesos/crear', $this->data);
            }
        }
    }

    // Eliminar una credencial API
    public function eliminar($client_id = NULL)
    {
        if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
        {
            redirect('auth/login', 'refresh');
        }
        else
        {
            if ($client_id)
            {
                $deleted = $this->Api_accesos_model->delete_client($client_id);
                if ($deleted)
                {
                    $this->session->set_flashdata('message', 'Acceso API eliminado exitosamente.');
                }
                else
                {
                    $this->session->set_flashdata('error', 'No se pudo eliminar el acceso API.');
                }
                redirect('admin/api_accesos', 'refresh');
            }
            else
            {
                show_404();
            }
        }
    }
}
