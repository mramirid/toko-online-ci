<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Category extends MY_Controller
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
        $data['title']      = 'Admin: Category';
        $data['content']    = $this->category->paginate($page)->get();  // Mengambil data-data dalam bentuk objek
        $data['total_rows'] = $this->category->count();
        $data['pagination'] = $this->category->makePagination(          // Generate pagination link
            base_url('category'),
            2,                      // Offset ada pada url segment ke-2 (udah diatur di route)
            $data['total_rows']
        );
        $data['page']       = 'pages/category/index';

        $this->view($data);
    }

    public function search($page = null)
    {
        if (isset($_POST['keyword'])) {
            $this->session->set_userdata('keyword', $this->input->post('keyword'));
        } else {
            redirect(base_url('category'));
        }

        $keyword = $this->session->userdata('keyword');

        $data['title']      = 'Admin: Category';
        $data['content']    = $this->category->like('title', $keyword)->paginate($page)->get();
        $data['total_rows'] = $this->category->like('title', $keyword)->count();
        $data['pagination'] = $this->category->makePagination(base_url('category/search'), 3, $data['total_rows']);
        $data['page']       = 'pages/category/index';

        $this->view($data);
    }

    public function reset()
    {
        $this->session->unset_userdata('keyword');  // Clear dulu keyword dari session   
        redirect(base_url('category'));
    }

    public function create()
    {
        if (!$_POST) {
            $input = (object) $this->category->getDefaultValues();
        } else {
            $input = (object) $this->input->post(null, true);
        }

        if (!$this->category->validate()) {
            $data['title']          = 'Tambah Kategori';
            $data['input']          = $input;
            $data['form_action']    = base_url('category/create');
            $data['page']           = 'pages/category/form';

            $this->view($data);
            return;
        }

        if ($this->category->create($input)) {
            $this->session->set_flashdata('success', 'Data berhasil disimpan');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi suatu kesalahan');
        }

        redirect(base_url('category'));
    }

    public function edit($id)
    {
        $data['content'] = $this->category->where('id', $id)->first();  // Ambil data dari id yang terpilih

        if (!$data['content']) {    // Jika data tidak ada di db
            $this->session->set_flashdata('warning', 'Maaf, data tidak ditemukan');
            redirect(base_url('category'));
        }

        if (!$_POST) {  // Jika tidak ada post berati user baru mulai edit
            $data['input'] = $data['content'];
        } else {
            $data['input'] = (object) $this->input->post(null, true);
        }

        if (!$this->category->validate()) { // Jika tidak ada post ini tidak akan dieksekusi
            $data['title']          = 'Ubah Kategori';
            $data['form_action']    = base_url("category/edit/$id");
            $data['page']           = 'pages/category/form';

            $this->view($data);             // Lanjutkan ke form edit
            return;
        }

        if ($this->category->where('id', $id)->update($data['input'])) {    // Lakukan input & Jika input berhasil
            $this->session->set_flashdata('success', 'Data berhasil diperbaharui');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('category'));
    }

    public function delete($id)
    {
        if (!$_POST) {
            // Jika diakses tidak dengan menggunakan method post, kembalikan ke home (forbidden)
            redirect(base_url('category'));
        }

        if (!$this->category->where('id', $id)->first()) {  // Jika data tidak ditemukan
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('category'));
        }

        if ($this->category->where('id', $id)->delete()) {  // // Lakukan delete & Jika delete berhasil
            $this->session->set_flashdata('success', 'Data sudah berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('category'));
    }

    public function unique_slug()
    {
        $slug       = $this->input->post('slug');
        $id         = $this->input->post('id');
        $category   = $this->category->where('slug', $slug)->first(); // Akan terisi jika terdapat slug yang sama

        if ($category) {
            if ($id == $category->id) {  // Keperluan edit tidak perlu ganti slug, jadi tidak masalah
                return true;
            }

            // Jika terdapat suatu nilai pada $category, berikan pesan error pertanda slug sudah ada di db
            $this->load->library('form_validation');
            $this->form_validation->set_message('unique_slug', '%s sudah digunakan');
            return false;
        }

        return true;
    }
}

/* End of file Category.php */
