<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Register_model extends MY_Model
{
    protected $table = 'user';  // Tabel ditentukan manual karena nama class model bukan nama tabel

    /**
     * Untuk mendapatkan default values saat form register diload
     */
    public function getDefaultValues()
    {
        return [
            'name'      => '',
            'email'     => '',
            'password'  => '',
            'role'      => '',
            'is_active' => ''
        ];
    }

    public function getValidationRules()
    {
        $validationRules = [
            [
                'field' => 'name',  // Sesuai kolom pada tabel
                'label' => 'Nama',  // Nama yang mewakili field tersebut
                'rules' => 'trim|required'
            ],
            [
                'field' => 'email',
                'label' => 'E-Mail',
                'rules' => 'trim|required|valid_email|is_unique[user.email]', // is_unique: harus unik pada kolom email
                'errors' => [
                    'is_unique' => 'This %s already exists.'
                ]
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|min_length[8]'
            ],
            [
                'field' => 'password_confirmation',
                'label' => 'Konfirmasi Password',
                'rules' => 'required|matches[password]' // matches: harus sama sama field password di atas
            ]
        ];

        return $validationRules;
    }

    public function run($input)
    {
        $data = [
            'name'      => $input->name,
            'email'     => strtolower($input->email),
            'password'  => hashEncrypt($input->password),
            'role'      => 'member'
        ];

        $user = $this->create($data);   // Insert database

        $sess_data = [
            'id'        => $user,
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_login'  => true
        ];

        $this->session->set_userdata($sess_data);

        return true;
    }
}

/* End of file Register_model.php */
