<?php
use OAuth2\Storage\Pdo;
use OAuth2\Server;
use OAuth2\GrantType\ClientCredentials;

class OAuth2Server {

    private $server;

    public function __construct()
    {
        // Configuración de almacenamiento usando PDO
        $storage = new Pdo(array(
            'dsn' => 'mysql:dbname=' . get_instance()->db->database . ';host=' . get_instance()->db->hostname,
            'username' => get_instance()->db->username,
            'password' => get_instance()->db->password
        ));

        // Crear el servidor OAuth2
        $this->server = new Server($storage, array(
            'allow_implicit' => false,
        ));

        // Añadir el grant type client_credentials
        $this->server->addGrantType(new ClientCredentials($storage));
    }

    public function getServer()
    {
        return $this->server;
    }
}
