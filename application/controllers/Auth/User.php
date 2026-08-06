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
}
