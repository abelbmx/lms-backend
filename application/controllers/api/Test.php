<?php
// application/controllers/api/Test.php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Test para verificar conectividad de la API
 */
class Test extends Api_controller
{
    public function __construct()
    {
        // No requerir autenticación para endpoints de test
        $this->require_auth = false;
        parent::__construct();
    }
    
    /**
     * Test básico de conectividad
     * GET /api/test
     */
    public function index()
    {
        $response_data = [
            'message' => 'API LMS funcionando correctamente',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => ENVIRONMENT,
            'php_version' => PHP_VERSION,
            'codeigniter_version' => CI_VERSION
        ];
        
        $this->response_success($response_data, 'Test de conectividad exitoso');
    }
    
    /**
     * Health check detallado
     * GET /api/health
     */
    public function health()
    {
        $health_data = [
            'status' => 'healthy',
            'services' => [
                'database' => $this->check_database(),
                'filesystem' => $this->check_filesystem(),
                'memory' => $this->check_memory()
            ],
            'uptime' => $this->get_uptime(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->response_success($health_data, 'Health check completado');
    }
    
    /**
     * Verificar conexión a base de datos
     */
    private function check_database()
    {
        try {
            $this->db->query('SELECT 1');
            return [
                'status' => 'healthy',
                'response_time' => $this->measure_db_response_time()
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar sistema de archivos
     */
    private function check_filesystem()
    {
        $upload_path = FCPATH . 'uploads/';
        
        return [
            'status' => is_writable($upload_path) ? 'healthy' : 'warning',
            'writable' => is_writable($upload_path),
            'exists' => file_exists($upload_path)
        ];
    }
    
    /**
     * Verificar uso de memoria
     */
    private function check_memory()
    {
        $memory_usage = memory_get_usage(true);
        $memory_limit = ini_get('memory_limit');
        
        return [
            'usage_bytes' => $memory_usage,
            'usage_mb' => round($memory_usage / 1024 / 1024, 2),
            'limit' => $memory_limit,
            'percentage' => round(($memory_usage / $this->parse_memory_limit($memory_limit)) * 100, 2)
        ];
    }
    
    /**
     * Obtener uptime del servidor
     */
    private function get_uptime()
    {
        if (function_exists('sys_getloadavg')) {
            $uptime_file = '/proc/uptime';
            if (file_exists($uptime_file)) {
                $uptime = file_get_contents($uptime_file);
                $uptime = explode(' ', $uptime);
                return round($uptime[0] / 3600, 2) . ' horas';
            }
        }
        
        return 'No disponible';
    }
    
    /**
     * Medir tiempo de respuesta de BD
     */
    private function measure_db_response_time()
    {
        $start = microtime(true);
        $this->db->query('SELECT 1');
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2) . 'ms';
    }
    
    /**
     * Parsear límite de memoria
     */
    private function parse_memory_limit($limit)
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $limit = (int) $limit;
        
        switch($last) {
            case 'g':
                $limit *= 1024;
            case 'm':
                $limit *= 1024;
            case 'k':
                $limit *= 1024;
        }
        
        return $limit;
    }
}
