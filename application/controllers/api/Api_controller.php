<?php
// application/controllers/api/Api_controller.php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controlador base para todos los endpoints de la API
 * Maneja CORS, autenticación, validación y respuestas estándar
 */
class Api_controller extends CI_Controller
{
    protected $user_id = null;
    protected $user_data = null;
    protected $access_token = null;
    protected $allowed_methods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    protected $require_auth = true;
    
    public function __construct()
    {
        parent::__construct();
        
        // Configurar CORS antes que cualquier otra cosa
        $this->configure_cors();
        
        // Manejar preflight OPTIONS request
        if ($this->input->method() === 'options') {
            $this->handle_preflight();
            return;
        }
        
        // Cargar librerías necesarias
        $this->load->library(['jwt_lib', 'response_lib']);
        $this->load->model(['Usuario_model', 'Token_model']);
        $this->load->helper(['url', 'security']);
        
        // Configurar logs de API
        $this->setup_api_logging();
        
        // Validar autenticación si es requerida
        if ($this->require_auth) {
            $this->validate_authentication();
        }
        
        // Validar método HTTP
        $this->validate_http_method();
        
        // Log de la petición
        $this->log_api_request();
    }
    
    /**
     * Configurar headers CORS
     */
    private function configure_cors()
    {
        // Configurar dominios permitidos (ajustar según tus necesidades)
        $allowed_origins = [
            'http://localhost:3000',
            'http://localhost:5173',
            'http://localhost:8080',
            'https://tu-dominio.com'
        ];
        
        $origin = $this->input->get_request_header('Origin');
        
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: *"); // Para desarrollo
        }
        
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // 24 horas
        
        // Headers adicionales para la API
        header("Content-Type: application/json; charset=utf-8");
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");
    }
    
    /**
     * Manejar petición preflight OPTIONS
     */
    private function handle_preflight()
    {
        http_response_code(200);
        exit();
    }
    
    /**
     * Configurar logging de API
     */
    private function setup_api_logging()
    {
        // Crear tabla de logs si no existe
        if (!$this->db->table_exists('api_logs')) {
            $this->create_api_logs_table();
        }
    }
    
    /**
     * Validar autenticación JWT
     */
    private function validate_authentication()
    {
        $auth_header = $this->input->get_request_header('Authorization');
        
        if (!$auth_header) {
            $this->response_error('Token de autorización requerido', 401);
        }
        
        // Extraer token del header "Bearer TOKEN"
        if (!preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $this->response_error('Formato de token inválido', 401);
        }
        
        $token = $matches[1];
        $this->access_token = $token;
        
        try {
            // Validar token JWT
            $decoded = $this->jwt_lib->decode($token);
            
            if (!$decoded || !isset($decoded->user_id)) {
                $this->response_error('Token inválido', 401);
            }
            
            // Verificar que el usuario existe y está activo
            $user = $this->Usuario_model->get_user($decoded->user_id);
            
            if (!$user || $user['estado'] !== 'activo') {
                $this->response_error('Usuario no válido o inactivo', 401);
            }
            
            $this->user_id = $decoded->user_id;
            $this->user_data = $user;
            
            // Actualizar último acceso
            $this->Usuario_model->update_last_access($this->user_id);
            
        } catch (Exception $e) {
            $this->response_error('Token expirado o inválido: ' . $e->getMessage(), 401);
        }
    }
    
    /**
     * Validar método HTTP permitido
     */
    private function validate_http_method()
    {
        $method = $this->input->method(true);
        
        if (!in_array($method, $this->allowed_methods)) {
            $this->response_error('Método HTTP no permitido', 405);
        }
    }
    
    /**
     * Registrar petición de API en logs
     */
    private function log_api_request()
    {
        $log_data = [
            'endpoint' => uri_string(),
            'method' => $this->input->method(true),
            'user_id' => $this->user_id,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'request_data' => json_encode($this->get_request_data()),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('api_logs', $log_data);
    }
    
    /**
     * Obtener datos de la petición
     */
    protected function get_request_data()
    {
        $method = $this->input->method(true);
        
        switch ($method) {
            case 'GET':
                return $this->input->get();
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $input = file_get_contents('php://input');
                return json_decode($input, true) ?: [];
            default:
                return [];
        }
    }
    
    /**
     * Validar entrada JSON
     */
    protected function validate_json_input($required_fields = [])
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->response_error('JSON inválido: ' . json_last_error_msg(), 400);
        }
        
        // Validar campos requeridos
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->response_error("El campo '$field' es requerido", 400);
            }
        }
        
        return $data;
    }
    
    /**
     * Validar parámetros GET
     */
    protected function validate_get_params($required_params = [])
    {
        $params = $this->input->get();
        
        foreach ($required_params as $param) {
            if (!isset($params[$param]) || empty($params[$param])) {
                $this->response_error("El parámetro '$param' es requerido", 400);
            }
        }
        
        return $params;
    }
    
    /**
     * Respuesta exitosa estándar
     */
    protected function response_success($data = null, $message = 'Operación exitosa', $status_code = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c'),
            'request_id' => uniqid()
        ];
        
        http_response_code($status_code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Respuesta de error estándar
     */
    protected function response_error($message = 'Error en la operación', $status_code = 400, $error_code = null)
    {
        $response = [
            'status' => 'error',
            'error' => $message,
            'error_description' => $message,
            'error_code' => $error_code,
            'timestamp' => date('c'),
            'request_id' => uniqid()
        ];
        
        // Log del error
        log_message('error', 'API Error: ' . $message . ' | Code: ' . $status_code . ' | Endpoint: ' . uri_string());
        
        http_response_code($status_code);
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Respuesta paginada
     */
    protected function response_paginated($data, $total, $page, $limit, $message = 'Datos obtenidos exitosamente')
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
        
        http_response_code(200);
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Validar permisos de usuario
     */
    protected function validate_permission($required_role = null, $resource_owner_id = null)
    {
        if (!$this->user_data) {
            $this->response_error('Usuario no autenticado', 401);
        }
        
        $user_role = $this->user_data['rol'];
        
        // Super admin puede hacer todo
        if ($user_role === 'superadmin') {
            return true;
        }
        
        // Validar rol específico
        if ($required_role && $user_role !== $required_role) {
            $this->response_error('Permisos insuficientes', 403);
        }
        
        // Validar propiedad del recurso
        if ($resource_owner_id && $this->user_id != $resource_owner_id) {
            $this->response_error('No tienes permisos para acceder a este recurso', 403);
        }
        
        return true;
    }
    
    /**
     * Crear tabla de logs si no existe
     */
    private function create_api_logs_table()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS api_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                endpoint VARCHAR(255) NOT NULL,
                method VARCHAR(10) NOT NULL,
                user_id INT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent TEXT,
                request_data TEXT,
                response_code INT DEFAULT NULL,
                execution_time DECIMAL(8,3) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_endpoint (endpoint),
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Validar rate limiting (limitar peticiones por minuto)
     */
    protected function validate_rate_limit($max_requests = 60, $window_minutes = 1)
    {
        $identifier = $this->user_id ?: $this->input->ip_address();
        $window_start = date('Y-m-d H:i:s', strtotime("-{$window_minutes} minutes"));
        
        $this->db->where('created_at >', $window_start);
        $this->db->where('ip_address', $this->input->ip_address());
        if ($this->user_id) {
            $this->db->where('user_id', $this->user_id);
        }
        $request_count = $this->db->count_all_results('api_logs');
        
        if ($request_count >= $max_requests) {
            $this->response_error('Demasiadas peticiones, intenta más tarde', 429);
        }
    }
    
    /**
     * Sanitizar entrada de datos
     */
    protected function sanitize_input($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize_input'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar formato de email
     */
    protected function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generar token CSRF
     */
    protected function generate_csrf_token()
    {
        return bin2hex(random_bytes(32));
    }
}
