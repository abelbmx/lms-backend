<?php
// application/hooks/Cors.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cors {
    
    public function set_cors_headers() {
        // Permitir orígenes específicos (desarrollo y producción)
        $allowed_origins = [
            'http://localhost:3000',    // React dev
            'http://localhost:5173',    // Vite dev
            'http://127.0.0.1:3000',
            'http://127.0.0.1:5173',
            'https://tu-dominio.com'    // Producción
        ];
        
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: " . $origin);
        }
        
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 86400"); // 24 horas
        
        // Manejar preflight requests (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}
