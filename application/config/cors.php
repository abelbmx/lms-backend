<?php
// application/config/cors.php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
|
| Configuración para Cross-Origin Resource Sharing
|
*/

// Dominios permitidos para CORS
$config['cors_allowed_origins'] = [
    'http://localhost:3000',
    'http://localhost:5173',
    'http://localhost:8080',
    'https://tu-dominio.com',
    'https://www.tu-dominio.com'
];

// Métodos HTTP permitidos
$config['cors_allowed_methods'] = [
    'GET',
    'POST',
    'PUT',
    'DELETE',
    'OPTIONS',
    'PATCH'
];

// Headers permitidos
$config['cors_allowed_headers'] = [
    'Content-Type',
    'Authorization',
    'X-Requested-With',
    'Accept',
    'Origin',
    'Access-Control-Request-Method',
    'Access-Control-Request-Headers'
];

// Headers expuestos
$config['cors_exposed_headers'] = [
    'Authorization',
    'Content-Type'
];

// Permitir credenciales
$config['cors_allow_credentials'] = true;

// Tiempo de cache para preflight (en segundos)
$config['cors_max_age'] = 86400; // 24 horas

// Habilitar CORS para toda la aplicación
$config['cors_enable_all_routes'] = true;

// Rutas específicas que requieren CORS
$config['cors_specific_routes'] = [
    'api/*'
];
