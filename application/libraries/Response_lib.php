<?php
// application/libraries/Response_lib.php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librería de respuestas para API
 * Maneja respuestas JSON estándar
 */
class Response_lib
{
    private $CI;
    
    public function __construct()
    {
        $this->CI =& get_instance();
    }
    
    /**
     * Respuesta exitosa
     */
    public function success($data = null, $message = 'Operación exitosa', $status_code = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c'),
            'request_id' => uniqid()
        ];
        
        $this->output_json($response, $status_code);
    }
    
    /**
     * Respuesta de error
     */
    public function error($message = 'Error en la operación', $status_code = 400, $error_code = null)
    {
        $response = [
            'status' => 'error',
            'error' => $message,
            'error_description' => $message,
            'error_code' => $error_code,
            'timestamp' => date('c'),
            'request_id' => uniqid()
        ];
        
        $this->output_json($response, $status_code);
    }
    
    /**
     * Respuesta paginada
     */
    public function paginated($data, $total, $page, $limit, $message = 'Datos obtenidos exitosamente')
    {
        $total_pages = ceil($total / $limit);
        
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'total_pages' => $total_pages,
                'total_items' => (int)$total,
                'items_per_page' => (int)$limit,
                'has_next' => $page < $total_pages,
                'has_previous' => $page > 1
            ],
            'timestamp' => date('c')
        ];
        
        $this->output_json($response, 200);
    }
    
    /**
     * Salida JSON
     */
    private function output_json($response, $status_code = 200)
    {
        $this->CI->output
            ->set_status_header($status_code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
