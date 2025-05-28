<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Usuario_model');
        $this->load->library('form_validation');
    }

    // Login de usuario
    public function login()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run() === TRUE) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');

                $user = $this->Usuario_model->get_user_by_email($email);

                if ($user && password_verify($password, $user['password'])) {
                    // Login exitoso
                    $this->Usuario_model->update_last_access($user['id']);

                    // Crear sesión
                    $session_data = array(
                        'user_id' => $user['id'],
                        'email' => $user['email'],
                        'nombre' => $user['nombre'],
                        'rol' => $user['rol_nombre'],
                        'logged_in' => TRUE
                    );
                    $this->session->set_userdata($session_data);

                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(array(
                            'status' => 'success',
                            'message' => 'Login exitoso',
                            'user' => array(
                                'id' => $user['id'],
                                'nombre' => $user['nombre'],
                                'email' => $user['email'],
                                'rol' => $user['rol_nombre']
                            )
                        )));
                } else {
                    $this->output
                        ->set_status_header(401)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(array(
                            'status' => 'error',
                            'message' => 'Credenciales inválidas'
                        )));
                }
            } else {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status' => 'error',
                        'message' => 'Datos inválidos',
                        'errors' => validation_errors()
                    )));
            }
        } else {
            // Mostrar formulario de login
            $this->load->view('auth/login');
        }
    }

    // Registro de usuario
    public function register()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('nombre', 'Nombre', 'required|min_length[2]');
            $this->form_validation->set_rules('apellido', 'Apellido', 'required|min_length[2]');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[usuarios.email]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('password_confirm', 'Confirmar Password', 'required|matches[password]');

            if ($this->form_validation->run() === TRUE) {
                // Obtener rol de estudiante por defecto
                $student_role = $this->db->get_where('roles', array('nombre' => 'estudiante'))->row();

                $user_data = array(
                    'nombre' => $this->input->post('nombre'),
                    'apellido' => $this->input->post('apellido'),
                    'email' => $this->input->post('email'),
                    'password' => $this->input->post('password'),
                    'rol_id' => $student_role ? $student_role->id : 3,
                    'token_verificacion' => bin2hex(random_bytes(32))
                );

                $user_id = $this->Usuario_model->create_user($user_data);

                if ($user_id) {
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode(array(
                            'status' => 'success',
                            'message' => 'Usuario registrado exitosamente',
                            'user_id' => $user_id
                        )));
                } else {
                    $this->output
                        ->set_status_header(500)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(array(
                            'status' => 'error',
                            'message' => 'Error al registrar usuario'
                        )));
                }
            } else {
                $this->output
                    ->set_status_header(400)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'status' => 'error',
                        'message' => 'Datos inválidos',
                        'errors' => validation_errors()
                    )));
            }
        } else {
            // Mostrar formulario de registro
            $this->load->view('auth/register');
        }
    }

    // Logout
    public function logout()
    {
        $this->session->sess_destroy();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => 'success',
                'message' => 'Sesión cerrada exitosamente'
            )));
    }

    // Verificar email
    public function verify_email($token)
    {
        $user = $this->db->get_where('usuarios', array('token_verificacion' => $token))->row();

        if ($user) {
            $this->db->where('id', $user->id);
            $this->db->update('usuarios', array(
                'email_verificado' => 1,
                'token_verificacion' => null
            ));

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Email verificado exitosamente'
                )));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Token de verificación inválido'
                )));
        }
    }
}
