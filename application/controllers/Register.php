<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Register extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $is_login = $this->session->userdata('is_login');
        
        if ($is_login) {
            redirect(base_url());   // Jika sudah login, redirect ke home
            return;
        }
    }

    public function index()
    {
        // Apakah ada post ke controller ini
        if (!$_POST) {
            $input = (object) $this->register->getDefaultValues();
        } else {
            $input = (object) $this->input->post(null, true);
        }

        if (!$this->register->validate()) {
            // Jika validasi gagal maka arahkan ke form register lagi
            $data['title'] = 'Register';
            $data['input'] = $input;
            $data['page'] = 'pages/auth/register';

            $this->view($data);

            return;
        }

        // Input data
        if ($this->register->run($input)) {
            $this->session->set_flashdata('success', 'Berhasil melakukan registrasi');
            redirect(base_url());
        } else {
            $this->session->set_flashdata('error', 'Oops terjadi suatu kesalahan');
            redirect(base_url('register'));
        }
    }
}

/* End of file Register.php */
