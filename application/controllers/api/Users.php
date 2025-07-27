<?php
// application/controllers/api/Users.php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Api_controller.php';

/**
 * Controlador de Usuarios para la API
 */
class Users extends Api_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Usuario_model');
        $this->load->library('bcrypt');
    }

    /**
     * Obtener perfil del usuario actual
     * GET /api/profile
     */
    public function profile()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        $user = $this->Usuario_model->get_user($this->user_id);

        if (!$user) {
            $this->response_error('Usuario no encontrado', 404);
        }

        // Remover campos sensibles
        unset($user['password']);

        $this->response_success($user, 'Perfil obtenido exitosamente');
    }

    /**
     * Actualizar perfil del usuario actual
     * PUT /api/profile/update
     */
    public function update_profile()
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        // Campos permitidos para actualizar (TODOS los campos del perfil)
        $allowed_fields = [
            'nombre', 
            'apellido', 
            'telefono', 
            'fecha_nacimiento', 
            'genero', 
            'bio',  // ✅ Usar 'bio' como está en la BD
            'direccion', 
            'pais', 
            'ciudad', 
            'codigo_postal'
        ];
        
        $update_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($input_data[$field])) {
                // Validaciones específicas por campo
                if ($field === 'fecha_nacimiento') {
                    // Validar formato de fecha
                    $fecha = $input_data[$field];
                    if (!empty($fecha)) {
                        $date = DateTime::createFromFormat('Y-m-d', $fecha);
                        if (!$date || $date->format('Y-m-d') !== $fecha) {
                            $this->response_error('Formato de fecha inválido. Use YYYY-MM-DD', 400);
                        }
                        $update_data[$field] = $fecha;
                    } else {
                        $update_data[$field] = null; // Permitir fecha vacía
                    }
                } elseif ($field === 'genero') {
                    // Validar género
                    $genero = $input_data[$field];
                    if (!empty($genero) && !in_array($genero, ['masculino', 'femenino', 'otro'])) {
                        $this->response_error('Género inválido', 400);
                    }
                    $update_data[$field] = !empty($genero) ? $genero : null;
                } elseif ($field === 'telefono') {
                    // Validar teléfono (opcional)
                    $telefono = trim($input_data[$field]);
                    if (!empty($telefono) && !preg_match('/^[\d\-\+\(\)\s]+$/', $telefono)) {
                        $this->response_error('Formato de teléfono inválido', 400);
                    }
                    $update_data[$field] = !empty($telefono) ? $telefono : null;
                } else {
                    // Para otros campos, sanitizar input
                    $value = $this->sanitize_input($input_data[$field]);
                    $update_data[$field] = !empty($value) ? $value : null;
                }
            }
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        // ✅ DEBUG: Ver qué datos se van a actualizar
        log_message('info', 'Updating user ' . $this->user_id . ' with data: ' . json_encode($update_data));

        if ($this->Usuario_model->update_user($this->user_id, $update_data)) {
            // Obtener usuario actualizado
            $updated_user = $this->Usuario_model->get_user($this->user_id);
            unset($updated_user['password']);

            // ✅ DEBUG: Ver qué datos se devuelven después de actualizar
            log_message('info', 'Updated user data: ' . json_encode($updated_user));

            $this->response_success($updated_user, 'Perfil actualizado exitosamente');
        } else {
            $this->response_error('Error al actualizar perfil', 500);
        }
    }

    /**
     * Cambiar contraseña del usuario actual
     * POST /api/profile/change-password
     */
    public function change_password()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        $input_data = $this->validate_json_input(['current_password', 'new_password', 'confirm_password']);

        $current_password = $input_data['current_password'];
        $new_password = $input_data['new_password'];
        $confirm_password = $input_data['confirm_password'];

        // Validar que las nuevas contraseñas coincidan
        if ($new_password !== $confirm_password) {
            $this->response_error('Las contraseñas nuevas no coinciden', 400);
        }

        // Validar longitud de nueva contraseña
        if (strlen($new_password) < 6) {
            $this->response_error('La nueva contraseña debe tener al menos 6 caracteres', 400);
        }

        // Obtener usuario actual
        $user = $this->Usuario_model->get_user($this->user_id);

        if (!$user) {
            $this->response_error('Usuario no encontrado', 404);
        }

        // Verificar contraseña actual
        if (!$this->bcrypt->verify($current_password, $user['password'])) {
            $this->response_error('La contraseña actual es incorrecta', 401);
        }

        // Hashear nueva contraseña
        $new_password_hash = $this->bcrypt->hash($new_password);

        if (!$new_password_hash) {
            $this->response_error('Error al procesar la contraseña', 500);
        }

        // Actualizar contraseña
        if ($this->Usuario_model->change_password($this->user_id, $new_password_hash)) {
            $this->response_success(null, 'Contraseña cambiada exitosamente');
        } else {
            $this->response_error('Error al cambiar contraseña', 500);
        }
    }

    /**
     * Obtener usuario específico (solo para admins)
     * GET /api/user/{id}
     */
    public function get_user($user_id)
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo admins pueden ver otros usuarios
        $this->validate_permission('administrador');

        $user = $this->Usuario_model->get_user($user_id);

        if (!$user) {
            $this->response_error('Usuario no encontrado', 404);
        }

        // Remover campos sensibles
        unset($user['password']);

        $this->response_success($user, 'Usuario obtenido exitosamente');
    }

    /**
     * Listar usuarios (solo para admins)
     * GET /api/users
     */
    public function list_users()
    {
        if ($this->input->method() !== 'get') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo admins pueden listar usuarios
        $this->validate_permission('administrador');

        // Obtener parámetros de filtrado y paginación
        $params = $this->input->get();
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $limit = isset($params['limit']) ? min(100, max(1, intval($params['limit']))) : 20;
        $offset = ($page - 1) * $limit;

        // Filtros
        $filters = [];
        if (!empty($params['rol'])) {
            $filters['rol'] = $params['rol'];
        }
        if (!empty($params['estado'])) {
            $filters['estado'] = $params['estado'];
        }
        if (!empty($params['search'])) {
            $filters['search'] = $params['search'];
        }

        // Obtener usuarios y total
        $users = $this->Usuario_model->list_users($filters, $limit, $offset);
        $total = $this->Usuario_model->count_users($filters);

        // Remover passwords
        foreach ($users as &$user) {
            unset($user['password']);
        }

        $this->response_paginated($users, $total, $page, $limit, 'Usuarios obtenidos exitosamente');
    }

    /**
     * Crear nuevo usuario (solo para admins)
     * POST /api/users/create
     */
    public function create_user()
    {
        if ($this->input->method() !== 'post') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo admins pueden crear usuarios
        $this->validate_permission('administrador');

        $input_data = $this->validate_json_input(['nombre', 'apellido', 'email', 'password', 'rol']);

        // Validar datos
        $nombre = $this->sanitize_input($input_data['nombre']);
        $apellido = $this->sanitize_input($input_data['apellido']);
        $email = trim(strtolower($input_data['email']));
        $password = $input_data['password'];
        $rol = $input_data['rol'];

        // Validaciones
        if (strlen($nombre) < 2) {
            $this->response_error('El nombre debe tener al menos 2 caracteres', 400);
        }

        if (!$this->validate_email($email)) {
            $this->response_error('Formato de email inválido', 400);
        }

        if (strlen($password) < 6) {
            $this->response_error('La contraseña debe tener al menos 6 caracteres', 400);
        }

        if (!in_array($rol, ['administrador', 'profesor', 'alumno', 'moderador'])) {
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
            'password' => $this->bcrypt->hash($password),
            'rol' => $rol,
            'estado' => 'activo',
            'email_verificado' => 1
        ];

        $user_id = $this->Usuario_model->create_user($user_data);

        if (!$user_id) {
            $this->response_error('Error al crear usuario', 500);
        }

        // Obtener usuario creado
        $user = $this->Usuario_model->get_user($user_id);
        unset($user['password']);

        $this->response_success($user, 'Usuario creado exitosamente', 201);
    }

    /**
     * Actualizar usuario (solo para admins)
     * PUT /api/users/{id}/update
     */
    public function update_user($user_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo admins pueden actualizar usuarios
        $this->validate_permission('administrador');

        // Verificar que el usuario existe
        $existing_user = $this->Usuario_model->get_user($user_id);
        if (!$existing_user) {
            $this->response_error('Usuario no encontrado', 404);
        }

        $input_data = $this->validate_json_input();

        if (empty($input_data)) {
            $this->response_error('No hay datos para actualizar', 400);
        }

        // Campos permitidos para actualizar por admins
        $allowed_fields = [
            'nombre', 
            'apellido', 
            'email', 
            'estado', 
            'rol',
            'telefono', 
            'fecha_nacimiento', 
            'genero', 
            'bio',  // ✅ Usar 'bio' como está en la BD
            'direccion', 
            'pais', 
            'ciudad', 
            'codigo_postal'
        ];
        
        $update_data = [];

        foreach ($allowed_fields as $field) {
            if (isset($input_data[$field])) {
                if ($field === 'email') {
                    $email = trim(strtolower($input_data[$field]));
                    if (!$this->validate_email($email)) {
                        $this->response_error('Formato de email inválido', 400);
                    }
                    if ($this->Usuario_model->email_exists($email, $user_id)) {
                        $this->response_error('El email ya está en uso', 409);
                    }
                    $update_data[$field] = $email;
                } elseif ($field === 'fecha_nacimiento') {
                    // Validar formato de fecha
                    $fecha = $input_data[$field];
                    if (!empty($fecha)) {
                        $date = DateTime::createFromFormat('Y-m-d', $fecha);
                        if (!$date || $date->format('Y-m-d') !== $fecha) {
                            $this->response_error('Formato de fecha inválido. Use YYYY-MM-DD', 400);
                        }
                        $update_data[$field] = $fecha;
                    } else {
                        $update_data[$field] = null;
                    }
                } elseif ($field === 'genero') {
                    // Validar género
                    $genero = $input_data[$field];
                    if (!empty($genero) && !in_array($genero, ['masculino', 'femenino', 'otro'])) {
                        $this->response_error('Género inválido', 400);
                    }
                    $update_data[$field] = !empty($genero) ? $genero : null;
                } elseif ($field === 'rol') {
                    // Validar rol
                    if (!in_array($input_data[$field], ['administrador', 'profesor', 'alumno', 'moderador'])) {
                        $this->response_error('Rol inválido', 400);
                    }
                    $update_data[$field] = $input_data[$field];
                } elseif ($field === 'estado') {
                    // Validar estado
                    if (!in_array($input_data[$field], ['activo', 'inactivo', 'suspendido'])) {
                        $this->response_error('Estado inválido', 400);
                    }
                    $update_data[$field] = $input_data[$field];
                } else {
                    $value = $this->sanitize_input($input_data[$field]);
                    $update_data[$field] = !empty($value) ? $value : null;
                }
            }
        }

        if (empty($update_data)) {
            $this->response_error('No hay campos válidos para actualizar', 400);
        }

        if ($this->Usuario_model->update_user($user_id, $update_data)) {
            // Obtener usuario actualizado
            $updated_user = $this->Usuario_model->get_user($user_id);
            unset($updated_user['password']);

            $this->response_success($updated_user, 'Usuario actualizado exitosamente');
        } else {
            $this->response_error('Error al actualizar usuario', 500);
        }
    }

    /**
     * Cambiar estado del usuario (activar/desactivar)
     * PUT /api/users/{id}/toggle-status
     */
    public function toggle_user_status($user_id)
    {
        if ($this->input->method() !== 'put') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo admins pueden cambiar estado
        $this->validate_permission('administrador');

        $input_data = $this->validate_json_input(['estado']);
        $nuevo_estado = $input_data['estado'];

        // Validar estado
        if (!in_array($nuevo_estado, ['activo', 'inactivo', 'suspendido'])) {
            $this->response_error('Estado inválido', 400);
        }

        // Verificar que el usuario existe
        $user = $this->Usuario_model->get_user($user_id);
        if (!$user) {
            $this->response_error('Usuario no encontrado', 404);
        }

        // Actualizar estado
        if ($this->Usuario_model->update_user($user_id, ['estado' => $nuevo_estado])) {
            $this->response_success(null, "Usuario $nuevo_estado exitosamente");
        } else {
            $this->response_error('Error al cambiar estado del usuario', 500);
        }
    }

    /**
     * Eliminar usuario (soft delete)
     * DELETE /api/users/{id}
     */
    public function delete_user($user_id)
    {
        if ($this->input->method() !== 'delete') {
            $this->response_error('Método no permitido', 405);
        }

        // Solo admins pueden eliminar usuarios
        $this->validate_permission('administrador');

        // Verificar que el usuario existe
        $user = $this->Usuario_model->get_user($user_id);
        if (!$user) {
            $this->response_error('Usuario no encontrado', 404);
        }

        // No permitir eliminar superadmins
        if ($user['rol'] === 'superadmin') {
            $this->response_error('No se puede eliminar un superadministrador', 403);
        }

        // No permitir que se elimine a sí mismo
        if ($user_id == $this->user_id) {
            $this->response_error('No puedes eliminarte a ti mismo', 403);
        }

        // Eliminar usuario (soft delete)
        if ($this->Usuario_model->delete_user($user_id)) {
            $this->response_success(null, 'Usuario eliminado exitosamente');
        } else {
            $this->response_error('Error al eliminar usuario', 500);
        }
    }
}
