<?php
defined('BASEPATH') or exit('No direct script access allowed');

use OAuth2\Server;
use OAuth2\Storage\Pdo;
use OAuth2\Storage\Memory;
use OAuth2\GrantType\ClientCredentials;
use OAuth2\GrantType\UserCredentials;

class OAuth2Server
{
    protected $server;

    public function __construct()
    {
        $CI = &get_instance();

        // Configuración de la base de datos para OAuth2
        $dsn = 'mysql:dbname=' . $CI->db->database . ';host=' . $CI->db->hostname;
        $username = $CI->db->username;
        $password = $CI->db->password;

        // Crear storage PDO
        $storage = new Pdo(array(
            'dsn' => $dsn,
            'username' => $username,
            'password' => $password
        ));

        // Crear servidor OAuth2
        $this->server = new Server($storage, array(
            'enforce_state' => false,
            'allow_implicit' => true,
            'access_lifetime' => 3600 * 24, // 24 horas
            'refresh_token_lifetime' => 3600 * 24 * 30 // 30 días
        ));

        // Agregar grant types
        $this->server->addGrantType(new ClientCredentials($storage));
        $this->server->addGrantType(new UserCredentials($storage));

        $this->create_oauth_tables();
        $this->insert_default_client();
    }

    public function getServer()
    {
        return $this->server;
    }

    private function create_oauth_tables()
    {
        $CI = &get_instance();

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
                    username VARCHAR(80),
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
                )"
        ];

        foreach ($tables as $table => $sql) {
            if (!$CI->db->table_exists($table)) {
                $CI->db->query($sql);
            }
        }
    }

    private function insert_default_client()
    {
        $CI = &get_instance();

        // Verificar si ya existe el cliente por defecto
        $client = $CI->db->get_where('oauth_clients', array('client_id' => 'lms_client'))->row();

        if (!$client) {
            // Insertar cliente por defecto
            $client_data = array(
                'client_id' => 'lms_client',
                'client_secret' => 'lms_secret_2024',
                'redirect_uri' => '',
                'grant_types' => 'client_credentials user_credentials',
                'scope' => 'api1 create_course enroll_course manage_own_courses view_students grade_assignments moderate_forums'
            );
            $CI->db->insert('oauth_clients', $client_data);
        }

        // Insertar scopes por defecto
        $scopes = [
            'api1' => true,
            'create_course' => false,
            'enroll_course' => true,
            'manage_own_courses' => false,
            'view_students' => false,
            'grade_assignments' => false,
            'moderate_forums' => false
        ];

        foreach ($scopes as $scope => $is_default) {
            $existing = $CI->db->get_where('oauth_scopes', array('scope' => $scope))->row();
            if (!$existing) {
                $CI->db->insert('oauth_scopes', array(
                    'scope' => $scope,
                    'is_default' => $is_default
                ));
            }
        }
    }
}
