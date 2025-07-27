<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// $route['default_controller'] = 'home';
// $route['404_override'] = '';
// $route['translate_uri_dashes'] = FALSE;

// $route['admin'] = 'admin/dashboard';
// $route['admin/prefs/interfaces/(:any)'] = 'admin/prefs/interfaces/$1';
// $route['admin/usuarios'] = 'admin/users/index';

// // Rutas API OAuth2
// $route['api/token'] = 'lms_api/token';
// $route['api/test'] = 'lms_api/test';

// // Rutas de usuario
// $route['api/profile'] = 'lms_api/api_profile';
// $route['api/profile/update'] = 'lms_api/update_profile';

// // Rutas de cursos
// $route['api/courses'] = 'lms_api/api_courses';
// $route['api/courses/(:num)'] = 'lms_api/api_course_detail/$1';
// $route['api/courses/create'] = 'lms_api/create_course';

// // Rutas de inscripciones
// $route['api/enroll'] = 'lms_api/enroll_course';
// $route['api/my-courses'] = 'lms_api/api_my_courses';

// // Rutas de progreso
// $route['api/lesson/complete'] = 'lms_api/complete_lesson';
// $route['api/progress/(:num)'] = 'lms_api/api_course_progress/$1';

// // Rutas de evaluaciones
// $route['api/evaluation/start'] = 'lms_api/start_evaluation';
// $route['api/evaluation/submit'] = 'lms_api/submit_evaluation';

// // Rutas de certificados
// $route['api/certificate/generate'] = 'lms_api/generate_certificate';

// // Rutas de calificaciones
// $route['api/rate'] = 'lms_api/rate_course';
/*
| Agregar estas rutas al archivo routes.php existente

*/

// Rutas por defecto de CodeIgniter
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


// ===== RUTAS DE AUTENTICACIÓN =====
$route['api/register'] = 'lms_api/register';
$route['api/logout'] = 'lms_api/logout';
$route['api/token'] = 'lms_api/token';
$route['api/refresh-token'] = 'lms_api/refresh_token';
$route['api/verify-email/(:any)'] = 'lms_api/verify_email/$1';

// ===== RUTAS DE USUARIO =====
$route['api/profile'] = 'lms_api/api_profile';
$route['api/profile/update'] = 'lms_api/update_profile';
$route['api/profile/change-password'] = 'lms_api/change_password';
$route['api/dashboard'] = 'lms_api/api_dashboard';

// ===== RUTAS DE CURSOS =====
$route['api/courses'] = 'lms_api/api_courses';
$route['api/courses/create'] = 'lms_api/create_course';
$route['api/courses/(:num)'] = 'lms_api/api_course_detail/$1';
$route['api/categories'] = 'lms_api/api_categories';

// ===== RUTAS DE INSCRIPCIONES =====
$route['api/enroll'] = 'lms_api/enroll_course';
$route['api/my-courses'] = 'lms_api/api_my_courses';

// ===== RUTAS DE LECCIONES Y PROGRESO =====
$route['api/lessons/(:num)'] = 'lms_api/api_lesson/$1';
$route['api/lessons/complete'] = 'lms_api/complete_lesson';
$route['api/progress/(:num)'] = 'lms_api/api_course_progress/$1';

// ===== RUTAS DE EVALUACIONES =====
$route['api/evaluations/start'] = 'lms_api/start_evaluation';
$route['api/evaluations/submit'] = 'lms_api/submit_evaluation';

// ===== RUTAS DE CERTIFICADOS =====
$route['api/certificates/generate'] = 'lms_api/generate_certificate';

// ===== RUTAS DE CALIFICACIONES =====
$route['api/rate'] = 'lms_api/rate_course';

// ===== RUTAS DE NOTIFICACIONES =====
$route['api/notifications'] = 'lms_api/api_notifications';
$route['api/notifications/(:num)/read'] = 'lms_api/mark_notification_read/$1';
$route['api/notifications/mark-all-read'] = 'lms_api/mark_all_notifications_read';

// ===== RUTAS DE FOROS =====
$route['api/courses/(:num)/forums'] = 'lms_api/api_course_forums/$1';
$route['api/forums/(:num)/posts'] = 'lms_api/api_forum_posts/$1';
$route['api/forums/posts/create'] = 'lms_api/create_forum_post';

// ===== RUTA DE PRUEBA =====
$route['api/test'] = 'lms_api/test';

// ===== TEST =====
// === RUTAS PARA Api_test ===

// La interfaz web (cuando navegas a http://…/api)
// Mostrar la interfaz web de tests cuando se accede a /api/
$route['api']                            = 'api_test/index';

// Ejecutar todos los tests completos (/api/run_all_tests)
$route['api/run_all_tests']              = 'api_test/run_all_tests';

// Ejecutar un solo endpoint de prueba (/api/test_single_endpoint/<nombre>)
$route['api/test_single_endpoint/(:any)'] = 'api_test/test_single_endpoint/$1';

// Obtener token de prueba (/api/get_test_token)
$route['api/get_test_token']             = 'api_test/get_test_token';

// Ejecutar test de carga (/api/stress_test)
$route['api/stress_test']                = 'api_test/stress_test';


// =====================================================
// RUTAS DE TESTING Y DOCUMENTACIÓN
// =====================================================

// Interfaz web de tests
// $route['api']                            = 'api_test/index';
// $route['api/run_all_tests']              = 'api_test/run_all_tests';
// $route['api/test_single_endpoint/(:any)'] = 'api_test/test_single_endpoint/$1';
// $route['api/get_test_token']             = 'api_test/get_test_token';
// $route['api/stress_test']                = 'api_test/stress_test';

// =====================================================
// RUTAS PRINCIPALES DE LA API LMS
// =====================================================

// OAuth2 y Autenticación
$route['oauth/token']                    = 'api/auth/token';
$route['api/login']                      = 'api/auth/login';
$route['api/register']                   = 'api/auth/register';
$route['api/logout']                     = 'api/auth/logout';
$route['api/refresh']                    = 'api/auth/refresh_token';

// Test de conectividad
$route['api/test']                       = 'api/test/index';
$route['api/health']                     = 'api/test/health';

// =====================================================
// RUTAS DE USUARIOS Y PERFIL
// =====================================================

// Perfil de usuario
$route['api/profile']                    = 'api/users/profile';
$route['api/profile/update']             = 'api/users/update_profile';
$route['api/profile/change-password']    = 'api/users/change_password';
$route['api/user/(:num)']                = 'api/users/get_user/$1';

// Gestión de usuarios (admin)
$route['api/users']                      = 'api/users/list_users';
$route['api/users/create']               = 'api/users/create_user';
$route['api/users/(:num)/update']        = 'api/users/update_user/$1';
$route['api/users/(:num)/delete']        = 'api/users/delete_user/$1';
$route['api/users/(:num)/activate']      = 'api/users/activate_user/$1';
$route['api/users/(:num)/deactivate']    = 'api/users/deactivate_user/$1';

// =====================================================
// RUTAS DE CATEGORÍAS
// =====================================================

// ===== AUTENTICACIÓN =====
$route['api/register'] = 'lms_api/register';
$route['api/logout'] = 'lms_api/logout';
$route['api/token'] = 'lms_api/token';
$route['api/refresh-token'] = 'lms_api/refresh_token';

// ===== PERFIL DE USUARIO =====
$route['api/profile'] = 'lms_api/api_profile';
$route['api/profile/update'] = 'lms_api/update_profile';
$route['api/dashboard'] = 'lms_api/api_dashboard';

// ===== CURSOS =====
$route['api/courses'] = 'lms_api/api_courses';
$route['api/courses/(:num)'] = 'lms_api/api_course_detail/$1';
$route['api/courses/create'] = 'lms_api/create_course';
$route['api/categories'] = 'lms_api/api_categories';

// ===== INSCRIPCIONES - RUTAS PARA TUS SERVICIOS =====
$route['api/inscripciones/usuario'] = 'api/inscripciones/usuario';
$route['api/inscripciones/(:num)'] = 'api/inscripciones/show/$1';
$route['api/inscripciones/(:num)/progreso'] = 'api/inscripciones/progreso/$1';
$route['api/inscripciones'] = 'api/inscripciones/create';

// ===== EVALUACIONES - RUTAS PARA TUS SERVICIOS =====
// CREAR evaluación (POST)
$route['api/evaluaciones']['POST'] = 'api/Evaluaciones/create';

// Rutas existentes corregidas (con mayúsculas consistentes)
$route['api/evaluaciones/pendientes'] = 'api/Evaluaciones/pendientes';
$route['api/evaluaciones/curso/(:num)'] = 'api/Evaluaciones/por_curso/$1';
$route['api/evaluaciones/(:num)/iniciar']['POST'] = 'api/Evaluaciones/iniciar/$1';
$route['api/evaluaciones/(:num)/enviar']['POST'] = 'api/Evaluaciones/enviar/$1';
$route['api/evaluaciones/(:num)/resultados'] = 'api/Evaluaciones/resultados/$1';
$route['api/evaluaciones/(:num)/abandonar']['POST'] = 'api/Evaluaciones/abandonar/$1';
$route['api/evaluaciones/(:num)/guardar-progreso']['POST'] = 'api/Evaluaciones/guardar_progreso/$1';
$route['api/evaluaciones/(:num)/progreso'] = 'api/Evaluaciones/progreso/$1';
$route['api/evaluaciones/(:num)/verificar'] = 'api/Evaluaciones/verificar/$1';
$route['api/evaluaciones/intentos'] = 'api/Evaluaciones/intentos';
$route['api/evaluaciones/estadisticas'] = 'api/Evaluaciones/estadisticas';
$route['api/evaluaciones/(:num)'] = 'api/Evaluaciones/show/$1';
$route['api/evaluaciones/(:num)/preguntas'] = 'api/Evaluaciones/preguntas/$1';

// Evaluaciones del instructor
$route['api/evaluaciones/instructor'] = 'api/Evaluaciones/instructor';

// ===== PROGRESO - RUTAS PARA TUS SERVICIOS =====
$route['api/progreso/estadisticas'] = 'api/progreso/estadisticas';
$route['api/progreso/resumen'] = 'api/progreso/resumen';
$route['api/progreso/curso/(:num)'] = 'api/progreso/curso/$1';
$route['api/progreso/leccion/(:num)'] = 'api/progreso/actualizar_leccion/$1';

// ===== LECCIONES - RUTAS PARA TUS SERVICIOS =====
$route['api/lecciones/curso/(:num)'] = 'api/lecciones/por_curso/$1';
$route['api/lecciones/modulo/(:num)'] = 'api/lecciones/por_modulo/$1';
$route['api/lecciones/(:num)'] = 'api/lecciones/show/$1';
$route['api/lecciones/(:num)/completar'] = 'api/lecciones/completar/$1';

// ===== RUTAS ORIGINALES DEL LMS =====
$route['api/enroll'] = 'lms_api/enroll_course';
$route['api/my-courses'] = 'lms_api/api_my_courses';
$route['api/lessons/(:num)'] = 'lms_api/api_lesson/$1';
$route['api/lessons/complete'] = 'lms_api/complete_lesson';
$route['api/progress/(:num)'] = 'lms_api/api_course_progress/$1';
$route['api/evaluations/start'] = 'lms_api/start_evaluation';
$route['api/evaluations/submit'] = 'lms_api/submit_evaluation';
$route['api/certificates/generate'] = 'lms_api/generate_certificate';
$route['api/rate'] = 'lms_api/rate_course';
$route['api/notifications'] = 'lms_api/api_notifications';
$route['api/notifications/(:num)/read'] = 'lms_api/mark_notification_read/$1';
$route['api/test'] = 'lms_api/test';

// ===== TESTING =====
$route['api'] = 'api_test/index';
$route['api/run_all_tests'] = 'api_test/run_all_tests';
$route['api/test_single_endpoint/(:any)'] = 'api_test/test_single_endpoint/$1';
$route['api/get_test_token'] = 'api_test/get_test_token';
$route['api/stress_test'] = 'api_test/stress_test';

// =============================================================================
// RUTAS EXTENDIDAS (MANTIENES PARA FUNCIONALIDAD FUTURA)
// =============================================================================

// === CATEGORÍAS ===
$route['api/categorias']['GET'] = 'api/categorias/index';
$route['api/categorias']['POST'] = 'api/categorias/create';
$route['api/categorias/([0-9]+)']['GET'] = 'api/categorias/show/$1';
$route['api/categorias/([0-9]+)']['PUT'] = 'api/categorias/update/$1';
$route['api/categorias/([0-9]+)']['DELETE'] = 'api/categorias/delete/$1';

// === CURSOS EXTENDIDOS ===
$route['api/cursos']['GET'] = 'api/cursos/index';
$route['api/cursos']['POST'] = 'api/cursos/create';
$route['api/cursos/([0-9]+)']['GET'] = 'api/cursos/show/$1';
$route['api/cursos/([0-9]+)']['PUT'] = 'api/cursos/update/$1';
$route['api/cursos/([0-9]+)']['DELETE'] = 'api/cursos/delete/$1';
$route['api/cursos/destacados']['GET'] = 'api/cursos/destacados';
$route['api/cursos/buscar']['GET'] = 'api/cursos/buscar';

// === MÓDULOS ===
$route['api/modulos']['GET'] = 'api/modulos/index';
$route['api/modulos']['POST'] = 'api/modulos/create';
$route['api/modulos/([0-9]+)']['GET'] = 'api/modulos/show/$1';
$route['api/modulos/([0-9]+)']['PUT'] = 'api/modulos/update/$1';
$route['api/modulos/([0-9]+)']['DELETE'] = 'api/modulos/delete/$1';
$route['api/modulos/curso/([0-9]+)']['GET'] = 'api/modulos/por_curso/$1';

// === USUARIOS ===
$route['api/usuarios']['GET'] = 'api/usuarios/index';
$route['api/usuarios']['POST'] = 'api/usuarios/create';
$route['api/usuarios/([0-9]+)']['GET'] = 'api/usuarios/show/$1';
$route['api/usuarios/([0-9]+)']['PUT'] = 'api/usuarios/update/$1';
$route['api/usuarios/perfil']['GET'] = 'api/usuarios/perfil';
$route['api/usuarios/perfil']['PUT'] = 'api/usuarios/actualizar_perfil';

// === CERTIFICADOS ===
$route['api/certificados']['GET'] = 'api/certificados/index';
$route['api/certificados/usuario/([0-9]+)']['GET'] = 'api/certificados/por_usuario/$1';
$route['api/certificados/verificar/([a-zA-Z0-9]+)']['GET'] = 'api/certificados/verificar/$1';

// === NOTIFICACIONES ===
$route['api/notificaciones']['GET'] = 'api/notificaciones/index';
$route['api/notificaciones/usuario']['GET'] = 'api/notificaciones/usuario';
$route['api/notificaciones/([0-9]+)/marcar-leida']['POST'] = 'api/notificaciones/marcar_leida/$1';
$route['api/notificaciones/marcar-todas-leidas']['POST'] = 'api/notificaciones/marcar_todas_leidas';


// Rutas específicas para tu servicio cursoService.ts (apuntando a lms_api)
$route['lms_api/api_courses'] = 'lms_api/api_courses';
$route['lms_api/api_course_detail/(:num)'] = 'lms_api/api_course_detail/$1';
$route['lms_api/create_course'] = 'lms_api/create_course';
$route['lms_api/enroll_course'] = 'lms_api/enroll_course';
$route['lms_api/api_my_courses'] = 'lms_api/api_my_courses';

// Módulos
$route['api/cursos/([0-9]+)/modulos']['GET'] = 'api/modulos/by_curso/$1';
$route['api/modulos']['POST'] = 'api/modulos/create';
$route['api/modulos/([0-9]+)']['PUT'] = 'api/modulos/update/$1';
$route['api/modulos/([0-9]+)']['DELETE'] = 'api/modulos/delete/$1';

// Lecciones
$route['api/modulos/([0-9]+)/lecciones']['GET'] = 'api/lecciones/by_modulo/$1';
$route['api/lecciones']['POST'] = 'api/lecciones/create';
$route['api/lecciones/([0-9]+)']['GET'] = 'api/lecciones/show/$1';
$route['api/lecciones/([0-9]+)']['PUT'] = 'api/lecciones/update/$1';
$route['api/lecciones/([0-9]+)']['DELETE'] = 'api/lecciones/delete/$1';
$route['api/lecciones/([0-9]+)/completar']['POST'] = 'api/lecciones/completar/$1';

// =====================================================
// RUTAS DE CURSOS
// =====================================================

// Listado y búsqueda de cursos
$route['api/courses']                    = 'api/courses/list_courses';
$route['api/courses/search']             = 'api/courses/search_courses';
$route['api/courses/featured']           = 'api/courses/featured_courses';
$route['api/courses/category/(:num)']    = 'api/courses/courses_by_category/$1';

// CRUD de cursos
$route['api/courses/create']             = 'api/courses/create_course';
$route['api/courses/(:num)']             = 'api/courses/get_course/$1';
$route['api/courses/(:num)/update']      = 'api/courses/update_course/$1';
$route['api/courses/(:num)/delete']      = 'api/courses/delete_course/$1';
$route['api/courses/(:num)/publish']     = 'api/courses/publish_course/$1';
$route['api/courses/(:num)/unpublish']   = 'api/courses/unpublish_course/$1';

// Contenido del curso
$route['api/courses/(:num)/modules']     = 'api/courses/get_course_modules/$1';
$route['api/courses/(:num)/lessons']     = 'api/courses/get_course_lessons/$1';
$route['api/courses/(:num)/students']    = 'api/courses/get_course_students/$1';

// =====================================================
// RUTAS DE MÓDULOS Y LECCIONES
// =====================================================

// Módulos
$route['api/modules']                    = 'api/modules/list_modules';
$route['api/modules/create']             = 'api/modules/create_module';
$route['api/modules/(:num)']             = 'api/modules/get_module/$1';
$route['api/modules/(:num)/update']      = 'api/modules/update_module/$1';
$route['api/modules/(:num)/delete']      = 'api/modules/delete_module/$1';
$route['api/modules/(:num)/lessons']     = 'api/modules/get_module_lessons/$1';

// Lecciones
$route['api/lessons']                    = 'api/lessons/list_lessons';
$route['api/lessons/create']             = 'api/lessons/create_lesson';
$route['api/lessons/(:num)']             = 'api/lessons/get_lesson/$1';
$route['api/lessons/(:num)/update']      = 'api/lessons/update_lesson/$1';
$route['api/lessons/(:num)/delete']      = 'api/lessons/delete_lesson/$1';
$route['api/lessons/(:num)/complete']    = 'api/lessons/mark_complete/$1';
$route['api/lessons/complete']           = 'api/lessons/mark_complete_post';

// =====================================================
// RUTAS DE INSCRIPCIONES
// =====================================================

// Inscripciones del usuario
$route['api/my-courses']                 = 'api/enrollments/my_courses';
$route['api/enroll']                     = 'api/enrollments/enroll_course';
$route['api/enrollments/(:num)/cancel']  = 'api/enrollments/cancel_enrollment/$1';

// Gestión de inscripciones (admin/instructor)
$route['api/enrollments']                = 'api/enrollments/list_enrollments';
$route['api/enrollments/course/(:num)']  = 'api/enrollments/course_enrollments/$1';
$route['api/enrollments/(:num)/approve'] = 'api/enrollments/approve_enrollment/$1';
$route['api/enrollments/(:num)/reject']  = 'api/enrollments/reject_enrollment/$1';

// =====================================================
// RUTAS DE PROGRESO
// =====================================================

$route['api/progress/(:num)']            = 'api/progress/course_progress/$1';
$route['api/progress/lesson/(:num)']     = 'api/progress/lesson_progress/$1';
$route['api/progress/user/(:num)']       = 'api/progress/user_progress/$1';
$route['api/progress/update']            = 'api/progress/update_progress';

// =====================================================
// RUTAS DE EVALUACIONES
// =====================================================

// Evaluaciones
$route['api/evaluations']                = 'api/evaluations/list_evaluations';
$route['api/evaluations/create']         = 'api/evaluations/create_evaluation';
$route['api/evaluations/(:num)']         = 'api/evaluations/get_evaluation/$1';
$route['api/evaluations/(:num)/update']  = 'api/evaluations/update_evaluation/$1';
$route['api/evaluations/(:num)/delete']  = 'api/evaluations/delete_evaluation/$1';

// Intentos de evaluación
$route['api/evaluations/start']          = 'api/evaluations/start_evaluation';
$route['api/evaluations/submit']         = 'api/evaluations/submit_evaluation';
$route['api/evaluations/attempts/(:num)'] = 'api/evaluations/get_attempt/$1';
$route['api/evaluations/(:num)/attempts'] = 'api/evaluations/evaluation_attempts/$1';

// =====================================================
// RUTAS DE CERTIFICADOS
// =====================================================

$route['api/certificates']               = 'api/certificates/my_certificates';
$route['api/certificates/generate']      = 'api/certificates/generate_certificate';
$route['api/certificates/(:num)']        = 'api/certificates/get_certificate/$1';
$route['api/certificates/verify/(:any)'] = 'api/certificates/verify_certificate/$1';
$route['api/certificates/download/(:num)'] = 'api/certificates/download_certificate/$1';

// =====================================================
// RUTAS DE CALIFICACIONES Y RESEÑAS
// =====================================================

$route['api/rate']                       = 'api/ratings/rate_course';
$route['api/ratings/course/(:num)']      = 'api/ratings/course_ratings/$1';
$route['api/ratings/(:num)/update']      = 'api/ratings/update_rating/$1';
$route['api/ratings/(:num)/delete']      = 'api/ratings/delete_rating/$1';

// =====================================================
// RUTAS DE FOROS
// =====================================================

// Foros de curso
$route['api/courses/(:num)/forums']      = 'api/forums/course_forums/$1';
$route['api/forums/(:num)/posts']        = 'api/forums/forum_posts/$1';

// Gestión de posts
$route['api/forums/posts/create']        = 'api/forums/create_post';
$route['api/forums/posts/(:num)']        = 'api/forums/get_post/$1';
$route['api/forums/posts/(:num)/update'] = 'api/forums/update_post/$1';
$route['api/forums/posts/(:num)/delete'] = 'api/forums/delete_post/$1';
$route['api/forums/posts/(:num)/reply']  = 'api/forums/reply_post/$1';

// =====================================================
// RUTAS DE NOTIFICACIONES
// =====================================================

$route['api/notifications']              = 'api/notifications/list_notifications';
$route['api/notifications/unread']       = 'api/notifications/unread_notifications';
$route['api/notifications/(:num)/read']  = 'api/notifications/mark_read/$1';
$route['api/notifications/mark-all-read'] = 'api/notifications/mark_all_read';
$route['api/notifications/(:num)/delete'] = 'api/notifications/delete_notification/$1';

// =====================================================
// RUTAS DE DASHBOARD Y ESTADÍSTICAS
// =====================================================

$route['api/dashboard']                  = 'api/dashboard/get_dashboard';
$route['api/dashboard/stats']            = 'api/dashboard/get_stats';
$route['api/dashboard/recent-activity']  = 'api/dashboard/recent_activity';

// Estadísticas para administradores
$route['api/admin/stats']                = 'api/admin/admin_stats';
$route['api/admin/users/stats']          = 'api/admin/users_stats';
$route['api/admin/courses/stats']        = 'api/admin/courses_stats';
$route['api/admin/revenue/stats']        = 'api/admin/revenue_stats';

// =====================================================
// RUTAS DE ARCHIVOS Y UPLOADS
// =====================================================

$route['api/upload/image']               = 'api/files/upload_image';
$route['api/upload/video']               = 'api/files/upload_video';
$route['api/upload/document']            = 'api/files/upload_document';
$route['api/files/(:num)']               = 'api/files/get_file/$1';
$route['api/files/(:num)/delete']        = 'api/files/delete_file/$1';

// =====================================================
// RUTAS DE CONFIGURACIÓN
// =====================================================

$route['api/config']                     = 'api/config/get_config';
$route['api/config/update']              = 'api/config/update_config';
$route['api/config/email']               = 'api/config/email_config';
$route['api/config/payment']             = 'api/config/payment_config';

// =====================================================
// RUTAS DE PAGOS (si implementas pagos)
// =====================================================

$route['api/payments/methods']           = 'api/payments/payment_methods';
$route['api/payments/process']           = 'api/payments/process_payment';
$route['api/payments/(:num)/status']     = 'api/payments/payment_status/$1';
$route['api/payments/webhooks/stripe']   = 'api/payments/stripe_webhook';
$route['api/payments/webhooks/paypal']   = 'api/payments/paypal_webhook';

// =====================================================
// RUTAS DE REPORTES
// =====================================================

$route['api/reports/users']              = 'api/reports/users_report';
$route['api/reports/courses']            = 'api/reports/courses_report';
$route['api/reports/enrollments']        = 'api/reports/enrollments_report';
$route['api/reports/revenue']            = 'api/reports/revenue_report';
$route['api/reports/export/(:any)']      = 'api/reports/export_report/$1';

// =====================================================
// MIDDLEWARE DE API (todas las rutas api/ pasan por aquí)
// =====================================================

// Esta ruta debe ir al final para capturar cualquier ruta api/ no definida
$route['api/(.*)']                       = 'api/middleware/handle/$1';
