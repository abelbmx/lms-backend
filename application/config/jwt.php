<?php
// application/config/jwt.php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| JWT Configuration
|--------------------------------------------------------------------------
*/

// Clave secreta para firmar tokens (CAMBIAR EN PRODUCCIÓN)
$config['jwt_secret_key'] = 'lms-super-secret-key-change-in-production-12345678901234567890';

// Algoritmo de encriptación
$config['jwt_algorithm'] = 'HS256';

// Tiempo de vida del access token (en segundos)
$config['jwt_access_token_expire'] = 86400; // 24 horas

// Tiempo de vida del refresh token (en segundos)
$config['jwt_refresh_token_expire'] = 604800; // 7 días

// Issuer del token
$config['jwt_issuer'] = 'lms-api';

// Audience del token
$config['jwt_audience'] = 'lms-frontend';

// Configuración OAuth2 (para compatibilidad con tu BD existente)
$config['oauth_clients'] = [
    'lms_client' => [
        'secret' => 'lms_secret_2024',
        'grants' => ['password', 'refresh_token', 'client_credentials']
    ]
];
