
<?php
// =====================================================
// ARCHIVO: application/libraries/OAuth2Server.php
// =====================================================

defined('BASEPATH') or exit('No direct script access allowed');

use OAuth2\Server;
use OAuth2\Storage\Pdo;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;
use OAuth2\GrantType\RefreshToken;

class OAuth2Server
{
    protected $server;
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();

        // Configuración de la base de datos para OAuth2
        $dsn = 'mysql:dbname=' . $this->CI->db->database . ';host=' . $this->CI->db->hostname . ';charset=utf8mb4';
        $username = $this->CI->db->username;
        $password = $this->CI->db->password;

        // Crear storage PDO personalizado
        $storage = new CustomPdoStorage(array(
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password
        ));

        // Crear servidor OAuth2
        $this->server = new Server($storage, array(
            'enforce_state' => false,
            'allow_implicit' => true,
            'access_lifetime' => 3600 * 24, // 24 horas
            'refresh_token_lifetime' => 3600 * 24 * 30, // 30 días
            'always_issue_new_refresh_token' => true,
            'unset_refresh_token_after_use' => true
        ));

        // Agregar grant types
        $this->server->addGrantType(new ClientCredentials($storage));
        $this->server->addGrantType(new UserCredentials($storage));
        $this->server->addGrantType(new RefreshToken($storage, [
            'always_issue_new_refresh_token' => true,
            'unset_refresh_token_after_use' => true
        ]));

        $this->create_oauth_tables();
        $this->insert_default_data();
    }

    public function getServer()
    {
        return $this->server;
    }

    private function create_oauth_tables()
    {
        // Crear tablas OAuth2 si no existen
        $tables = [
            'oauth_clients' => "
                CREATE TABLE IF NOT EXISTS oauth_clients (
                    client_id VARCHAR(80) NOT NULL,
                    client_secret VARCHAR(80),
                    redirect_uri VARCHAR(2000),
                    grant_types VARCHAR(80),
                    scope VARCHAR(4000),
                    user_id VARCHAR(80),
                    PRIMARY KEY (client_id)
                )",
            'oauth_access_tokens' => "
                CREATE TABLE IF NOT EXISTS oauth_access_tokens (
                    access_token VARCHAR(40) NOT NULL,
                    client_id VARCHAR(80) NOT NULL,
                    user_id VARCHAR(80),
                    expires TIMESTAMP NOT NULL,
                    scope VARCHAR(4000),
                    PRIMARY KEY (access_token)
                )",
            'oauth_refresh_tokens' => "
                CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
                    refresh_token VARCHAR(40) NOT NULL,
                    client_id VARCHAR(80) NOT NULL,
                    user_id VARCHAR(80),
                    expires TIMESTAMP NOT NULL,
                    scope VARCHAR(4000),
                    PRIMARY KEY (refresh_token)
                )",
            'oauth_users' => "
                CREATE TABLE IF NOT EXISTS oauth_users (
                    username VARCHAR(80) PRIMARY KEY,
                    password VARCHAR(80),
                    first_name VARCHAR(80),
                    last_name VARCHAR(80),
                    email VARCHAR(80),
                    email_verified BOOLEAN,
                    scope VARCHAR(4000)
                )",
            'oauth_scopes' => "
                CREATE TABLE IF NOT EXISTS oauth_scopes (
                    scope VARCHAR(80) NOT NULL,
                    is_default BOOLEAN,
                    PRIMARY KEY (scope)
                )",
            'oauth_authorization_codes' => "
                CREATE TABLE IF NOT EXISTS oauth_authorization_codes (
                    authorization_code VARCHAR(40) NOT NULL,
                    client_id VARCHAR(80) NOT NULL,
                    user_id VARCHAR(80),
                    redirect_uri VARCHAR(2000),
                    expires TIMESTAMP NOT NULL,
                    scope VARCHAR(4000),
                    id_token VARCHAR(1000),
                    PRIMARY KEY (authorization_code)
                )"
        ];

        foreach ($tables as $table => $sql) {
            if (!$this->CI->db->table_exists($table)) {
                $this->CI->db->query($sql);
            }
        }
    }

    private function insert_default_data()
    {
        // Cliente por defecto
        $client = $this->CI->db->get_where('oauth_clients', array('client_id' => 'lms_client'))->row();

        if (!$client) {
            $client_data = array(
                'client_id' => 'lms_client',
                'client_secret' => 'lms_secret_2024',
                'redirect_uri' => '',
                'grant_types' => 'client_credentials user_credentials refresh_token',
                'scope' => 'api1 create_course enroll_course manage_own_courses view_students grade_assignments moderate_forums'
            );
            $this->CI->db->insert('oauth_clients', $client_data);
        }

        // Scopes por defecto
        $scopes = [
            'api1' => true,
            'create_course' => false,
            'enroll_course' => true,
            'manage_own_courses' => false,
            'view_students' => false,
            'grade_assignments' => false,
            'moderate_forums' => false,
            'manage_users' => false,
            'system_admin' => false
        ];

        foreach ($scopes as $scope => $is_default) {
            $existing = $this->CI->db->get_where('oauth_scopes', array('scope' => $scope))->row();
            if (!$existing) {
                $this->CI->db->insert('oauth_scopes', array(
                    'scope' => $scope,
                    'is_default' => $is_default
                ));
            }
        }
    }
}

// =====================================================
// CLASE PERSONALIZADA PARA STORAGE PDO
// (Se incluye en el mismo archivo)
// =====================================================

class CustomPdoStorage extends Pdo
{
    public function checkUserCredentials($username, $password)
    {
        $CI = &get_instance();
        $CI->load->model('Usuario_model');

        $user = $CI->Usuario_model->get_user_by_email($username);

        if ($user && password_verify($password, $user['password']) && $user['estado'] === 'activo') {
            return true;
        }

        return false;
    }

    public function getUserDetails($username)
    {
        $CI = &get_instance();
        $CI->load->model(['Usuario_model', 'Rol_model']);

        $user = $CI->Usuario_model->get_user_by_email($username);

        if ($user) {
            // Obtener scopes basados en el rol
            $role = $CI->Rol_model->get_role_by_id($user['rol_id']);
            $permissions = explode(',', $role['permisos']);

            $scope_mapping = [
                'all' => 'api1 create_course enroll_course manage_own_courses view_students grade_assignments moderate_forums manage_users system_admin',
                'create_course' => 'api1 create_course',
                'enroll_course' => 'api1 enroll_course',
                'manage_own_courses' => 'api1 manage_own_courses',
                'view_students' => 'api1 view_students',
                'grade_assignments' => 'api1 grade_assignments',
                'moderate_forums' => 'api1 moderate_forums'
            ];

            $scopes = ['api1']; // Scope básico

            foreach ($permissions as $permission) {
                if (isset($scope_mapping[$permission])) {
                    $additional_scopes = explode(' ', $scope_mapping[$permission]);
                    $scopes = array_merge($scopes, $additional_scopes);
                }
            }

            return [
                'user_id' => $user['id'],
                'scope' => implode(' ', array_unique($scopes))
            ];
        }

        return false;
    }
}
