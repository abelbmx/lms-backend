<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Administracion extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		/* Load :: Common */
		$this->lang->load('admin/Tarifas');

		/* Title Page :: Common */
		$this->page_title->push(lang('tarifas'));
		$this->data['pagetitle'] = $this->page_title->show();

    $this->load->model(array('model_cliente'));

		/* Breadcrumbs :: Common */
		$this->breadcrumbs->unshift(1, lang('tarifas'), 'admin/tarifas');
	}


	public function index()
	{
		if ( ! $this->ion_auth->logged_in() OR ! $this->ion_auth->is_admin())
		{
			redirect('auth/login', 'refresh');
		}
		else
		{
			/* Breadcrumbs */
			$this->data['breadcrumb'] = $this->breadcrumbs->show();

			/* Get all users */
			$this->data['users'] = $this->model_cliente->tarifas();


			/* Load Template */
			$this->template->admin_render('admin/tarifas/index', $this->data);
		}
	}


}
