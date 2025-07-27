<?php
// application/controllers/api/Auth.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Autenticación para la API
 * Maneja login, registro, logout y renovación de tokens
 */
class Auth extends Api_controller
{
    public function __construct()
    {
        // No requerir autenticación para estos endpoints
        $this->require_auth = false;
        parent::__construct();

        $this->load->model(['Usuario_model', 'Token_model']);
        $this->load->library(['jwt_lib', 'bcrypt']);
    }

    /**
     * Endpoint OAuth2 para obtener token
     * POST /oauth/token
     */
    public function token()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Obtener datos del request (puede ser JSON o form-data)
        $content_type = $this->input->get_request_header('Content-Type');

        if (strpos($content_type, 'application/x-www-form-urlencoded') !== false) {
            // Datos como form-data
            $grant_type = $this->input->post('grant_type');
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $client_id = $this->input->post('client_id');
            $client_secret = $this->input->post('client_secret');
            $refresh_token = $this->input->post('refresh_token');
        } else {
            // Datos como JSON
            $input_data = $this->validate_json_input(['grant_type']);
            $grant_type = $input_data['grant_type'];
            $username = $input_data['username'] ?? null;
            $password = $input_data['password'] ?? null;
            $client_id = $input_data['client_id'] ?? null;
            $client_secret = $input_data['client_secret'] ?? null;
            $refresh_token = $input_data['refresh_token'] ?? null;
        }

        // Validar client credentials
        if (!$this->validate_client_credentials($client_id, $client_secret)) {
            $this->response_error('Credenciales de cliente inválidas', 401);
        }

        switch ($grant_type) {
            case 'password':
                $this->handle_password_grant($username, $password);
                break;
            case 'refresh_token':
                $this->handle_refresh_token_grant($refresh_token);
                break;
            case 'client_credentials':
                $this->handle_client_credentials_grant();
                break;
            default:
                $this->response_error('Tipo de grant no soportado', 400);
        }
    }

    /**
     * Login directo (alternativo al OAuth)
     * POST /api/login
     */
    public function login()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $input_data = $this->validate_json_input(['password']); // Solo requerir password

        // Aceptar tanto email como username
        $email = $input_data['email'] ?? $input_data['username'] ?? null;
        $password = $input_data['password'];

        if (!$email) {
            $this->response_error('Email o username requerido', 400);
        }

        $email = trim($email);

        // Validar formato de email
        if (!$this->validate_email($email)) {
            $this->response_error('Formato de email inválido', 400);
        }

        // Buscar usuario por email
        $user = $this->Usuario_model->get_user_by_email($email);

        if (!$user) {
            $this->response_error('Credenciales incorrectas', 401);
        }

        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            $this->response_error('Credenciales incorrectas', 401);
        }

        // Verificar que el usuario esté activo
        if ($user['estado'] !== 'activo') {
            $this->response_error('Usuario inactivo', 401);
        }

        // Generar tokens
        $tokens = $this->generate_tokens($user);

        // Actualizar último acceso
        $this->Usuario_model->update_last_access($user['id']);

        $response_data = [
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'refresh_token' => $tokens['refresh_token'],
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'rol' => $user['rol']
            ]
        ];

        $this->response_success($response_data, 'Login exitoso');
    }

    /**
     * Registro de nuevo usuario
     * POST /api/register
     */
    public function register()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $input_data = $this->validate_json_input(['nombre', 'apellido', 'email', 'password']);

        // Validar y sanitizar datos
        $nombre = $this->sanitize_input($input_data['nombre']);
        $apellido = $this->sanitize_input($input_data['apellido']);
        $email = trim(strtolower($input_data['email']));
        $password = $input_data['password'];
        $rol = $input_data['rol'] ?? 'alumno';
        $telefono = $this->sanitize_input($input_data['telefono'] ?? '');

        // Validaciones
        if (strlen($nombre) < 2) {
            $this->response_error('El nombre debe tener al menos 2 caracteres', 400);
        }

        if (strlen($apellido) < 2) {
            $this->response_error('El apellido debe tener al menos 2 caracteres', 400);
        }

        if (!$this->validate_email($email)) {
            $this->response_error('Formato de email inválido', 400);
        }

        if (strlen($password) < 6) {
            $this->response_error('La contraseña debe tener al menos 6 caracteres', 400);
        }

        if (!in_array($rol, ['alumno', 'profesor'])) {
            $this->response_error('Rol inválido', 400);
        }

        // Verificar que el email no esté en uso
        if ($this->Usuario_model->email_exists($email)) {
            $this->response_error('El email ya está registrado', 409);
        }

        // Crear usuario
        $user_data = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'password' => $this->bcrypt->hash_password($password),
            'rol' => $rol,
            'telefono' => $telefono,
            'estado' => 'activo',
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];

        $user_id = $this->Usuario_model->create_user($user_data);

        if (!$user_id) {
            $this->response_error('Error al crear usuario', 500);
        }

        // Obtener datos del usuario creado
        $user = $this->Usuario_model->get_user($user_id);

        // Generar tokens para auto-login
        $tokens = $this->generate_tokens($user);

        $response_data = [
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'rol' => $user['rol']
            ],
            'token' => $tokens['access_token']
        ];

        $this->response_success($response_data, 'Usuario registrado exitosamente', 201);
    }

    /**
     * Cerrar sesión
     * POST /api/logout
     */
    public function logout()
    {
        // Para logout, sí necesitamos autenticación
        $this->require_auth = true;
        $this->validate_authentication();

        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Invalidar el token actual
        if ($this->access_token) {
            $this->Token_model->revoke_token($this->access_token);
        }

        // También invalidar todos los tokens del usuario si se desea
        // $this->Token_model->revoke_user_tokens($this->user_id);

        $this->response_success(null, 'Logout exitoso');
    }

    /**
     * Renovar token de acceso
     * POST /api/refresh
     */
    public function refresh_token()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $input_data = $this->validate_json_input(['refresh_token']);
        $refresh_token = $input_data['refresh_token'];

        $this->handle_refresh_token_grant($refresh_token);
    }

    /**
     * Manejar grant type "password"
     */
    private function handle_password_grant($username, $password)
    {
        if (!$username || !$password) {
            $this->response_error('Usuario y contraseña requeridos', 400);
        }

        // Buscar usuario por email
        $user = $this->Usuario_model->get_user_by_email($username);

        if (!$user || !$this->bcrypt->check_password($password, $user['password'])) {
            $this->response_error('Credenciales incorrectas', 401);
        }

        if ($user['estado'] !== 'activo') {
            $this->response_error('Usuario inactivo', 401);
        }

        // Generar tokens
        $tokens = $this->generate_tokens($user);

        // Actualizar último acceso
        $this->Usuario_model->update_last_access($user['id']);

        $response_data = [
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'refresh_token' => $tokens['refresh_token'],
            'scope' => 'api'
        ];

        $this->response_success($response_data);
    }

    /**
     * Manejar grant type "refresh_token"
     */
    private function handle_refresh_token_grant($refresh_token)
    {
        if (!$refresh_token) {
            $this->response_error('Refresh token requerido', 400);
        }

        // Validar refresh token
        $token_data = $this->Token_model->validate_refresh_token($refresh_token);

        if (!$token_data) {
            $this->response_error('Refresh token inválido o expirado', 401);
        }

        // Obtener usuario
        $user = $this->Usuario_model->get_user($token_data['user_id']);

        if (!$user || $user['estado'] !== 'activo') {
            $this->response_error('Usuario no válido', 401);
        }

        // Revocar el refresh token usado
        $this->Token_model->revoke_refresh_token($refresh_token);

        // Generar nuevos tokens
        $tokens = $this->generate_tokens($user);

        $response_data = [
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in' => 86400,
            'refresh_token' => $tokens['refresh_token'],
            'scope' => 'api'
        ];

        $this->response_success($response_data);
    }

    /**
     * Manejar grant type "client_credentials"
     */
    private function handle_client_credentials_grant()
    {
        // Para aplicaciones, no usuarios específicos
        $tokens = $this->generate_client_tokens();

        $response_data = [
            'access_token' => $tokens['access_token'],
            'token_type' => 'Bearer',
            'expires_in' => 3600, // 1 hora para client credentials
            'scope' => 'api'
        ];

        $this->response_success($response_data);
    }

    /**
     * Validar credenciales del cliente OAuth2
     */
    private function validate_client_credentials($client_id, $client_secret)
    {
        // Definir clientes permitidos (en producción, esto debería estar en BD)
        $valid_clients = [
            'lms_client' => 'lms_secret_2024',
            'mobile_app' => 'mobile_secret_2024',
            'web_app' => 'web_secret_2024'
        ];

        return isset($valid_clients[$client_id]) &&
            $valid_clients[$client_id] === $client_secret;
    }

    /**
     * Generar tokens de acceso y refresh para usuario
     */
    private function generate_tokens($user)
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'exp' => time() + 86400 // 24 horas
        ];

        $access_token = $this->jwt_lib->encode($payload);
        $refresh_token = bin2hex(random_bytes(32));

        // Guardar refresh token en BD
        $this->Token_model->save_refresh_token($user['id'], $refresh_token);

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token
        ];
    }

    /**
     * Generar tokens para client credentials
     */
    private function generate_client_tokens()
    {
        $payload = [
            'client_id' => 'api_client',
            'scope' => 'api',
            'exp' => time() + 3600 // 1 hora
        ];

        $access_token = $this->jwt_lib->encode($payload);

        return [
            'access_token' => $access_token
        ];
    }
}
