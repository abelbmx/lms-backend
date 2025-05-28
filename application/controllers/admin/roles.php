<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Roles extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('admin/dashboard_model');
        $this->load->model('admin/ingresar_datos_model'); // Para acceder a los pedidos
        $this->load->model('admin/revisar_archivos_model');
        $this->load->model('documents_model');
    }

    public function index()
    {
        if (! $this->ion_auth->logged_in() or ! $this->ion_auth->is_admin()) {
            redirect('auth/login', 'refresh');
        } else {
            /* Título de la página */
            $this->page_title->push('Roles');
            $this->data['pagetitle'] = $this->page_title->show();

            /* Breadcrumbs */
            $this->breadcrumbs->unshift(1, 'Roles', 'admin/roles');
            $this->data['breadcrumb'] = $this->breadcrumbs->show();


            $pedidos = $this->ingresar_datos_model->get_all_pedidos();
            $this->data['pedidos'] = $pedidos;

            // Contar pedidos terminados y pendientes
            $pedidos_terminados = 0;
            $pedidos_pendientes = 0;
            $total_pedidos = count($pedidos);

            foreach ($pedidos as &$pedido) {
                $pedido['documentos_asociados'] = $this->documents_model->get_documents_by_order($pedido['id']);
                if ($pedido['Estado'] == 4 || $pedido['Estado'] == 5) {
                    $pedidos_terminados++;
                } else {
                    $pedidos_pendientes++;
                }
            }

            // Pasar los datos a la vista
            $this->data['total_pedidos'] = $total_pedidos;
            $this->data['pedidos_terminados'] = $pedidos_terminados;
            $this->data['pedidos_pendientes'] = $pedidos_pendientes;

            // Aquí estamos asegurándonos de obtener los pedidos por día
            $this->data['pedidos_por_dia'] = $this->ingresar_datos_model->get_pedidos_por_dia();

            $this->data['documentos_escaneados'] = $this->revisar_archivos_model->get_unlinked_files();
            $this->data['total_documentos_escaneados'] = count($this->data['documentos_escaneados']);

            // Otros datos del dashboard...
            $this->template->admin_render('admin/users/roles', $this->data);
        }
    }
}
