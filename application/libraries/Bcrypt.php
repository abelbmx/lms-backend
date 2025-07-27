<?php
// application/libraries/Bcrypt.php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Librería Bcrypt para CodeIgniter
 * Manejo seguro de contraseñas usando password_hash y password_verify
 */
class Bcrypt
{
    private $cost = 12; // Coste por defecto
    
    public function __construct($params = [])
    {
        if (isset($params['cost'])) {
            $this->cost = $params['cost'];
        }
    }
    
    /**
     * Hashear contraseña
     */
    public function hash($password, $cost = null)
    {
        $cost = $cost ?: $this->cost;
        
        $options = [
            'cost' => $cost
        ];
        
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
    
    /**
     * Alias para hash() - compatibilidad
     */
    public function hash_password($password, $cost = null)
    {
        return $this->hash($password, $cost);
    }
    
    /**
     * Verificar contraseña
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Alias para verify() - compatibilidad
     */
    public function check_password($password, $hash)
    {
        return $this->verify($password, $hash);
    }
    
    /**
     * Verificar si un hash necesita ser rehashed
     */
    public function needs_rehash($hash, $cost = null)
    {
        $cost = $cost ?: $this->cost;
        
        $options = [
            'cost' => $cost
        ];
        
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
    }
    
    /**
     * Obtener información sobre un hash
     */
    public function get_info($hash)
    {
        return password_get_info($hash);
    }
    
    /**
     * Generar hash con coste específico
     */
    public function hash_with_cost($password, $cost)
    {
        return $this->hash($password, $cost);
    }
    
    /**
     * Validar fortaleza de contraseña
     */
    public function validate_password_strength($password)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra minúscula';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra mayúscula';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un número';
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un carácter especial';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'score' => $this->calculate_password_score($password)
        ];
    }
    
    /**
     * Calcular puntuación de fortaleza (1-5)
     */
    private function calculate_password_score($password)
    {
        $score = 0;
        
        // Longitud
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        
        // Complejidad
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score++;
        
        // Penalizar patrones comunes
        if (preg_match('/(.)\1{2,}/', $password)) $score--; // Caracteres repetidos
        if (preg_match('/123|abc|qwe/i', $password)) $score--; // Secuencias
        
        return max(1, min(5, $score));
    }
    
    /**
     * Generar contraseña aleatoria segura
     */
    public function generate_random_password($length = 12, $include_symbols = true)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $chars = $lowercase . $uppercase . $numbers;
        if ($include_symbols) {
            $chars .= $symbols;
        }
        
        $password = '';
        
        // Asegurar que tenga al menos uno de cada tipo
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        
        if ($include_symbols) {
            $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        }
        
        // Completar la longitud
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // Mezclar caracteres
        return str_shuffle($password);
    }
    
    /**
     * Benchmark para determinar el coste óptimo
     */
    public function benchmark_cost($target_time = 0.2)
    {
        $cost = 8;
        
        do {
            $start = microtime(true);
            password_hash('test', PASSWORD_BCRYPT, ['cost' => $cost]);
            $end = microtime(true);
            
            $cost++;
        } while (($end - $start) < $target_time && $cost < 15);
        
        return $cost - 1;
    }
}
