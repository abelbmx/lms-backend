<?php
// application/models/Token_model.php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo de Tokens para manejo de refresh tokens
 */
class Token_model extends CI_Model
{
    private $table = 'refresh_tokens';
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->create_table_if_not_exists();
    }
    
    /**
     * Guardar refresh token
     */
    public function save_refresh_token($user_id, $refresh_token, $expires_in = 604800) // 7 días
    {
        $data = [
            'user_id' => $user_id,
            'token' => $refresh_token,
            'expires_at' => date('Y-m-d H:i:s', time() + $expires_in),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Validar refresh token
     */
    public function validate_refresh_token($refresh_token)
    {
        $this->db->where('token', $refresh_token);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->where('revoked', 0);
        
        $query = $this->db->get($this->table);
        
        return $query->row_array();
    }
    
    /**
     * Revocar refresh token
     */
    public function revoke_refresh_token($refresh_token)
    {
        $data = [
            'revoked' => 1,
            'revoked_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('token', $refresh_token);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Revocar todos los tokens de un usuario
     */
    public function revoke_user_tokens($user_id)
    {
        $data = [
            'revoked' => 1,
            'revoked_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('user_id', $user_id);
        $this->db->where('revoked', 0);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Limpiar tokens expirados
     */
    public function cleanup_expired_tokens()
    {
        $this->db->where('expires_at <', date('Y-m-d H:i:s'));
        return $this->db->delete($this->table);
    }
    
    /**
     * Obtener tokens activos de un usuario
     */
    public function get_user_active_tokens($user_id)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->where('revoked', 0);
        $this->db->order_by('created_at', 'DESC');
        
        return $this->db->get($this->table)->result_array();
    }
    
    /**
     * Contar tokens activos del sistema
     */
    public function count_active_tokens()
    {
        $this->db->where('expires_at >', date('Y-m-d H:i:s'));
        $this->db->where('revoked', 0);
        
        return $this->db->count_all_results($this->table);
    }
    
    /**
     * Revocar token de acceso (para logout)
     */
    public function revoke_token($access_token)
    {
        // Para tokens JWT, normalmente se mantiene una blacklist
        // Aquí implementamos una tabla simple de tokens revocados
        $blacklist_table = 'token_blacklist';
        
        $data = [
            'token' => $access_token,
            'revoked_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert($blacklist_table, $data);
    }
    
    /**
     * Verificar si un token está en la blacklist
     */
    public function is_token_blacklisted($access_token)
    {
        $blacklist_table = 'token_blacklist';
        
        $this->db->where('token', $access_token);
        $query = $this->db->get($blacklist_table);
        
        return $query->num_rows() > 0;
    }
    
    /**
     * Crear tablas si no existen
     */
    public function create_table_if_not_exists()
    {
        // Tabla de refresh tokens (nueva, no está en tu BD)
        if (!$this->db->table_exists($this->table)) {
            $sql = "
                CREATE TABLE {$this->table} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    revoked TINYINT(1) DEFAULT 0,
                    revoked_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_token (token),
                    INDEX idx_user_id (user_id),
                    INDEX idx_expires_at (expires_at),
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ";
            
            $this->db->query($sql);
        }
        
        // Tabla de blacklist para access tokens (nueva)
        if (!$this->db->table_exists('token_blacklist')) {
            $sql = "
                CREATE TABLE token_blacklist (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    token TEXT NOT NULL,
                    revoked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_revoked_at (revoked_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ";
            
            $this->db->query($sql);
        }
    }
}
