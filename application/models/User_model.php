<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Check if an email already exists in the users table
     * 
     * @param string $email
     * @return bool
     */
    public function is_email_exists($email) {
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->num_rows() > 0;
    }

    /**
     * Insert a new user into the database
     * 
     * @param array $data
     * @return bool
     */
    public function insert_user($data) {
        return $this->db->insert('users', $data);
    }

    /**
     * Get user by email
     * 
     * @param string $email
     * @return object|null
     */
    public function get_user_by_email($email) {
        $query = $this->db->get_where('users', array('email' => $email));
        return $query->row();
    }

    /**
     * Create a new session token for a user
     * 
     * @param int $user_id
     * @param string $token
     * @return bool
     */
    public function create_session($user_id, $token) {
        $now = date('Y-m-d H:i:s');
        $data = array(
            'user_id'    => $user_id,
            'token'      => $token,
            'created_at' => $now,
            'updated_at' => $now
        );
        return $this->db->insert('sessions', $data);
    }
}

