<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function register() {
        // Read raw JSON input
        $input = json_decode(trim(file_get_contents('php://input')), true);

        if (!$input || empty($input['name']) || empty($input['email']) || empty($input['password'])) {
            $this->output
                 ->set_status_header(400)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Data tidak lengkap')));
            return;
        }

        $name = trim($input['name']);
        $email = trim($input['email']);
        $password = $input['password'];

        // Check if email already exists
        if ($this->User_model->is_email_exists($email)) {
            $this->output
                 ->set_status_header(400)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Email sudah terdaftar')));
            return;
        }

        // Hash password with BCRYPT
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $now = date('Y-m-d H:i:s');

        $data = array(
            'name'       => $name,
            'email'      => $email,
            'password'   => $hashed_password,
            'created_at' => $now,
            'updated_at' => $now
        );

        $inserted = $this->User_model->insert_user($data);

        if ($inserted) {
            $this->output
                 ->set_status_header(200)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('data' => 'OK')));
        } else {
            $this->output
                 ->set_status_header(500)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Gagal menyimpan data user')));
        }
    }

    public function login() {
        // Read raw JSON input
        $input = json_decode(trim(file_get_contents('php://input')), true);

        if (!$input || empty($input['email']) || empty($input['password'])) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Email atau password salah')));
            return;
        }

        $email = trim($input['email']);
        $password = $input['password'];

        $user = $this->User_model->get_user_by_email($email);

        if (!$user || !password_verify($password, $user->password)) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Email atau password salah')));
            return;
        }

        // Generate UUID v4
        $token = $this->generate_uuid();

        // Create session in database
        $created = $this->User_model->create_session($user->id, $token);

        if ($created) {
            $this->output
                 ->set_status_header(200)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('data' => $token)));
        } else {
            $this->output
                 ->set_status_header(500)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Gagal membuat session')));
        }
    }

    public function current() {
        // Get Authorization header
        $auth_header = $this->input->get_request_header('Authorization', TRUE);

        if (!$auth_header || strpos($auth_header, 'Bearer ') !== 0) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Unauthorized')));
            return;
        }

        // Extract token
        $token = substr($auth_header, 7);

        if (empty($token)) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Unauthorized')));
            return;
        }

        // Look up user by token
        $user = $this->User_model->get_user_by_token($token);

        if (!$user) {
            $this->output
                 ->set_status_header(401)
                 ->set_content_type('application/json', 'utf-8')
                 ->set_output(json_encode(array('error' => 'Unauthorized')));
            return;
        }

        $data = array(
            'id'         => (int) $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'created_at' => $user->created_at
        );

        $this->output
             ->set_status_header(200)
             ->set_content_type('application/json', 'utf-8')
             ->set_output(json_encode(array('data' => $data)));
    }

    private function generate_uuid() {

        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

