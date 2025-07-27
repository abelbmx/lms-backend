<?php
// application/libraries/Jwt_lib.php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librería JWT para CodeIgniter
 * Implementación simple de JSON Web Tokens
 */
class Jwt_lib
{
    private $CI;
    private $secret_key;
    private $algorithm = 'HS256';
    
    public function __construct()
    {
        $this->CI =& get_instance();
        
        // Obtener clave secreta de configuración
        $this->secret_key = $this->CI->config->item('jwt_secret_key') ?: 'your-secret-key-change-this-in-production';
    }
    
    /**
     * Codificar JWT
     */
    public function encode($payload, $algorithm = null)
    {
        $algorithm = $algorithm ?: $this->algorithm;
        
        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => $algorithm
        ];
        
        $header_encoded = $this->base64url_encode(json_encode($header));
        $payload_encoded = $this->base64url_encode(json_encode($payload));
        
        // Signature
        $signature = $this->sign($header_encoded . '.' . $payload_encoded, $algorithm);
        
        return $header_encoded . '.' . $payload_encoded . '.' . $signature;
    }
    
    /**
     * Decodificar JWT
     */
    public function decode($token, $verify = true)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Token inválido');
        }
        
        list($header_encoded, $payload_encoded, $signature) = $parts;
        
        // Decodificar header y payload
        $header = json_decode($this->base64url_decode($header_encoded), true);
        $payload = json_decode($this->base64url_decode($payload_encoded), true);
        
        if (!$header || !$payload) {
            throw new Exception('Token malformado');
        }
        
        // Verificar firma si es requerido
        if ($verify) {
            $expected_signature = $this->sign($header_encoded . '.' . $payload_encoded, $header['alg']);
            
            if (!$this->constant_time_compare($signature, $expected_signature)) {
                throw new Exception('Firma inválida');
            }
        }
        
        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expirado');
        }
        
        return (object) $payload;
    }
    
    /**
     * Verificar si un token es válido sin decodificar
     */
    public function verify($token)
    {
        try {
            $this->decode($token);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener payload sin verificar (para debugging)
     */
    public function get_payload($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        return json_decode($this->base64url_decode($parts[1]), true);
    }
    
    /**
     * Firmar datos
     */
    private function sign($data, $algorithm)
    {
        switch ($algorithm) {
            case 'HS256':
                return $this->base64url_encode(hash_hmac('sha256', $data, $this->secret_key, true));
            case 'HS384':
                return $this->base64url_encode(hash_hmac('sha384', $data, $this->secret_key, true));
            case 'HS512':
                return $this->base64url_encode(hash_hmac('sha512', $data, $this->secret_key, true));
            default:
                throw new Exception('Algoritmo no soportado');
        }
    }
    
    /**
     * Codificación Base64 URL-safe
     */
    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodificación Base64 URL-safe
     */
    private function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
    
    /**
     * Comparación en tiempo constante para evitar timing attacks
     */
    private function constant_time_compare($a, $b)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }
        
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        
        return $result === 0;
    }
    
    /**
     * Generar token con tiempo de vida específico
     */
    public function generate_token($user_data, $expires_in = 86400)
    {
        $payload = array_merge($user_data, [
            'iat' => time(),
            'exp' => time() + $expires_in
        ]);
        
        return $this->encode($payload);
    }
    
    /**
     * Verificar si un token está a punto de expirar
     */
    public function is_expiring_soon($token, $threshold = 3600)
    {
        try {
            $payload = $this->decode($token, false);
            
            if (!isset($payload->exp)) {
                return false;
            }
            
            return ($payload->exp - time()) < $threshold;
        } catch (Exception $e) {
            return true;
        }
    }
    
    /**
     * Obtener tiempo restante de un token
     */
    public function get_time_remaining($token)
    {
        try {
            $payload = $this->decode($token, false);
            
            if (!isset($payload->exp)) {
                return null;
            }
            
            return max(0, $payload->exp - time());
        } catch (Exception $e) {
            return 0;
        }
    }
}
