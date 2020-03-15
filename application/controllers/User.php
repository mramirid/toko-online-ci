<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
        
        $role = $this->session->userdata('role');

        if ($role != 'admin') {
            redirect(base_url('/'));
            return;
        }
    }

    public function index($page = null)
    {
        $data['title']      = 'Admin: Pengguna';
        $data['content']    = $this->user->paginate($page)->get();
        $data['total_rows'] = $this->user->count();
        $data['pagination'] = $this->user->makePagination(base_url('user'), 2, $data['total_rows']);
        $data['page']       = 'pages/user/index';

        $this->view($data);
    }

    public function search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword', $this->input->post('keyword'));
        } else {
            redirect(base_url('user'));
        }

        $keyword = $this->session->userdata('keyword');

        $data['title']      = 'Admin: Pengguna';
        $data['content']    = $this->user
            ->like('name', $keyword)
            ->orLike('email', $keyword)   // Tidak hanya mencari berdasarkan name melainkan emai juga
            ->paginate($page)
            ->get();
        $data['total_rows'] = $this->user->like('name', $keyword)->orLike('email', $keyword)->count();
        $data['pagination'] = $this->user->makePagination(base_url('user/search'), 3, $data['total_rows']);
        $data['page']       = 'pages/user/index';

        $this->view($data);
    }

    public function reset()
    {
        $this->session->unset_userdata('keyword');  // Clear dulu keyword dari session   
        redirect(base_url('user'));
    }

    public function create()
    {
        if (!$_POST) {
            $input = (object) $this->user->getDefaultValues();
        } else {
            $input = (object) $this->input->post(null, true);

            // Pertama kali user ditambah lakukan validasi password
            $this->load->library('form_validation');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

            $input->password = hashEncrypt($input->password);   // Encrypt password
        }

        if (!empty($_FILES) && $_FILES['image']['name'] !== '') {                     // Jika upload'an tidak kosong
            $imageName  = url_title($input->name, '-', true) . '-' . date('YmdHis');  // Membuat slug
            $upload     = $this->user->uploadImage('image', $imageName);              // Mulai upload
            if ($upload) {
                // Jika upload berhasil, pasang nama file yang diupload ke dalam database
                $input->image   = $upload['file_name'];
            } else {
                redirect(base_url('user/create'));
            }
        }

        if (!$this->user->validate()) {
            $data['title']          = 'Tambah Pengguna';
            $data['input']          = $input;
            $data['form_action']    = base_url('user/create');
            $data['page']           = 'pages/user/form';

            $this->view($data);
            return;
        }

        if ($this->user->create($input)) {   // Input data
            $this->session->set_flashdata('success', 'Data berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url('user'));
    }

    public function edit($id)
    {
        $data['content'] = $this->user->where('id', $id)->first();

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('user'));
        }

        if (!$_POST) {
            $data['input'] = $data['content'];
        } else {
            $data['input'] = (object) $this->input->post(null, true);

            if ($data['input']->password !== '') {
                // Jika password tidak kosong, berati user mengubah password lalu encrypt
                $data['input']->password = hashEncrypt($data['input']->password);
            } else {
                // Jika tidak kosong berati user tidak mengubah password
                $data['input']->password = $data['content']->password;
            }
        }

        if (!empty($_FILES) && $_FILES['image']['name'] !== '') {   // Jika upload'an tidak kosong
            $imageName  = url_title($data['input']->name, '-', true) . '-' . date('YmdHis');  // Membuat slug
            $upload     = $this->user->uploadImage('image', $imageName);    // Mulai upload
            if ($upload) {
                if ($data['content']->image !== '') {
                    // Jika data di database ini memiliki gambar, maka hapus dulu file gambarnya
                    $this->user->deleteImage($data['content']->image);
                }
                // Jika upload berhasil, pasang nama file yang diupload ke dalam database
                $data['input']->image   = $upload['file_name'];
            } else {
                redirect(base_url("user/edit/$id"));
            }
        }

        if (!$this->user->validate()) {
            $data['title']          = 'Ubah Pengguna';
            $data['form_action']    = base_url("user/edit/$id");
            $data['page']           = 'pages/user/form';

            $this->view($data);
            return;
        }

        if ($this->user->where('id', $id)->update($data['input'])) {   // Update data
            $this->session->set_flashdata('success', 'Data berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url('user'));
    }

    public function delete($id)
    {
        if (!$_POST) {
            redirect(base_url('user'));
        }

        $user = $this->user->where('id', $id)->first();

        if (!$user) {
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('user'));
        }

        if ($this->user->where('id', $id)->delete()) {   // Lakukan penghapusan di db
            $this->user->deleteImage($user->image);      // Lakukan penghapusan gambar
            $this->session->set_flashdata('success', 'Data berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('user'));
    }

    public function unique_email()
    {
        $email  = $this->input->post('email');
        $id     = $this->input->post('id');
        $user   = $this->user->where('email', $email)->first(); // Akan terisi jika terdapat email yang sama

        if ($user) {
            if ($id == $user->id) {  // Keperluan edit tidak perlu ganti email, jadi tidak masalah
                return true;
            }

            // Jika terdapat suatu nilai pada $user, berikan pesan error pertanda email sudah ada di db
            $this->load->library('form_validation');
            $this->form_validation->set_message('unique_email', '%s sudah digunakan');
            return false;
        }

        return true;
    }
}

/* End of file User.php */
