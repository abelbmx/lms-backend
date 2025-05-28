<?php
namespace OAuth2\Storage;

interface ClientCredentialsInterface
{
    /**
     * Verifica las credenciales del cliente.
     *
     * @param string $client_id
     * @param string|null $client_secret
     * @return bool
     */
    public function checkClientCredentials($client_id, $client_secret = null);

    /**
     * Obtiene el scope del cliente.
     *
     * @param string $client_id
     * @return string|null
     */
    public function getClientScope($client_id);

    /**
     * Determina si el cliente es público.
     *
     * @param string $client_id
     * @return bool
     */
    public function isPublicClient($client_id);
}
