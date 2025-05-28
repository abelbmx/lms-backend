

// =====================================================
// MODELO: Usuario_model
// =====================================================



// =====================================================
// MODELO: Curso_model
// =====================================================



// =====================================================
// MODELO: 
// =====================================================



// =====================================================
// MODELO: Progreso_model
// =====================================================



// =====================================================
// MODELO: Evaluacion_model
// =====================================================



// =====================================================
// MODELO: Certificado_model
// =====================================================



// =====================================================
// MODELO: Calificacion_model
// =====================================================



// =====================================================
// MODELO: Foro_model  
// =====================================================



// =====================================================
// MODELO: Api_log_model
// =====================================================



// =====================================================
// ARCHIVO: application/config/routes.php
// =====================================================

/*
| Agregar estas rutas al archivo routes.php existente
*/

// Rutas API OAuth2
$route['api/token'] = 'lms_api/token';
$route['api/test'] = 'lms_api/test';

// Rutas de usuario
$route['api/profile'] = 'lms_api/api_profile';
$route['api/profile/update'] = 'lms_api/update_profile';

// Rutas de cursos
$route['api/courses'] = 'lms_api/api_courses';
$route['api/courses/(:num)'] = 'lms_api/api_course_detail/$1';
$route['api/courses/create'] = 'lms_api/create_course';

// Rutas de inscripciones
$route['api/enroll'] = 'lms_api/enroll_course';
$route['api/my-courses'] = 'lms_api/api_my_courses';

// Rutas de progreso
$route['api/lesson/complete'] = 'lms_api/complete_lesson';
$route['api/progress/(:num)'] = 'lms_api/api_course_progress/$1';

// Rutas de evaluaciones
$route['api/evaluation/start'] = 'lms_api/start_evaluation';
$route['api/evaluation/submit'] = 'lms_api/submit_evaluation';

// Rutas de certificados
$route['api/certificate/generate'] = 'lms_api/generate_certificate';

// Rutas de calificaciones
$route['api/rate'] = 'lms_api/rate_course';

// =====================================================
// ARCHIVO: application/libraries/OAuth2Server.php
// =====================================================



// =====================================================
// ARCHIVO: application/controllers/Auth.php
// Controlador para autenticación y registro
// =====================================================



// =====================================================
// ARCHIVO: application/controllers/Dashboard.php
// Dashboard principal del LMS
// =====================================================


// =====================================================
// ARCHIVO: application/config/autoload.php
// Configuración de autoload (agregar a archivo existente)
// =====================================================

/*
| Agregar estas líneas a la configuración existente:
*/

// Libraries to autoload
$autoload['libraries'] = array('database', 'session', 'form_validation', 'upload', 'email');

// Helper files to autoload
$autoload['helper'] = array('url', 'form', 'security', 'string', 'text');

// =====================================================
// ARCHIVO: application/config/database.php
// Configuración de base de datos (modificar existente)
// =====================================================

/*
| Asegúrate de que la configuración apunte a tu base de datos LMS:
*/

$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'tu_usuario',
    'password' => 'tu_password',
    'database' => 'lms_system',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);

// =====================================================
// ARCHIVO: .htaccess
// Configuración Apache (crear en la raíz del proyecto)
// =====================================================

RewriteEngine On

# Remove index.php from URLs
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# CORS headers para API
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"

# Handle preflight OPTIONS requests
RewriteCond %{REQUEST_METHOD} ^OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

// =====================================================
// ARCHIVO: application/views/auth/login.php
// Vista de login
// =====================================================


// =====================================================
// DOCUMENTACIÓN DE USO DE LA API
// =====================================================

/*
=== GUÍA DE USO DEL API LMS ===

1. OBTENER TOKEN DE ACCESO:
POST /api/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials&client_id=lms_client&client_secret=lms_secret_2024&scope=api1

2. USAR TOKEN EN REQUESTS:
Authorization: Bearer {access_token}

3. ENDPOINTS PRINCIPALES:

USUARIOS:
- GET /api/profile - Obtener perfil del usuario
- POST /api/profile/update - Actualizar perfil

CURSOS:
- GET /api/courses - Listar cursos
- GET /api/courses/123 - Detalle de curso
- POST /api/courses/create - Crear curso (scope: create_course)

INSCRIPCIONES:
- POST /api/enroll - Inscribirse en curso
- GET /api/my-courses - Mis cursos

PROGRESO:
- POST /api/lesson/complete - Completar lección
- GET /api/progress/123 - Progreso de curso

EVALUACIONES:
- POST /api/evaluation/start - Iniciar evaluación
- POST /api/evaluation/submit - Enviar respuestas

CERTIFICADOS:
- POST /api/certificate/generate - Generar certificado

CALIFICACIONES:
- POST /api/rate - Calificar curso

4. EJEMPLO DE USO:

// Obtener token
const tokenResponse = await fetch('/api/token', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: 'grant_type=client_credentials&client_id=lms_client&client_secret=lms_secret_2024&scope=api1'
});

const tokenData = await tokenResponse.json();
const accessToken = tokenData.access_token;

// Usar token para obtener cursos
const coursesResponse = await fetch('/api/courses', {
    headers: {
        'Authorization': `Bearer ${accessToken}`
    }
});

const courses = await coursesResponse.json();

=== ESTRUCTURA DE RESPUESTAS ===

Exitosa:
{
    "status": "success",
    "data": {...},
    "message": "Operación exitosa"
}

Error:
{
    "error": "error_code",
    "error_description": "Descripción del error"
}

*/

?>
