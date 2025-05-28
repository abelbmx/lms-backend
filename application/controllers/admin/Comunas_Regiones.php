<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Comunas_Regiones extends Admin_Controller
{

    public function get_options()
    {
        // Ruta al archivo JSON
        $json_file = base_url('assets/comunas-y-regiones/chile.json');

        // Leer el contenido del archivo JSON
        $json_data = file_get_contents($json_file);

        // Enviar los datos JSON como respuesta
        $this->output
            ->set_content_type('application/json')
            ->set_output($json_data);
    }
}
