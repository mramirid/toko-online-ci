<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends MY_Controller 
{
    private $id;

    public function __construct()
    {
        parent::__construct();
        
        $is_login = $this->session->userdata('is_login');
        $this->id = $this->session->userdata('id');

        if (!$is_login) {   // Jika ternyata belum ada session
            redirect(base_url());
            return;
        }
    }

    public function index()
    {
        $data['title']      = 'Keranjang Belanja';
        $data['content']    = $this->cart->select([
                'cart.id', 'cart.qty', 'cart.subtotal',
                'product.title', 'product.image', 'product.price'
            ])
            ->join('product')
            ->where('cart.id_user', $this->id)
            ->get();
        $data['page']       = 'pages/cart/index';

        return $this->view($data);
    }

    /**
     * Menambah produk beserta kuantitasnya di home
     */
    public function add()
    {
        if (!$_POST || $this->input->post('qty') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url());
        } else {
            $input              = (object) $this->input->post(null, true);

            // Mengambil data produk yang dipilih, untuk mendapatkan price
            $this->cart->table  = 'product';
            $product            = $this->cart->where('id', $input->id_product)->first();

            // Ambil cart untuk dicek apakah user sudah pesan
            $this->cart->table  = 'cart';
            $cart               = $this->cart->where('id_user', $this->id)->where('id_product', $input->id_product)->first();

            $subtotal           = $product->price * $input->qty;

            if ($cart) {    // Jika ternyata user sudah pesan, maka update cart
                $data = [
                    'qty'       => $cart->qty + $input->qty,
                    'subtotal'  => $cart->subtotal + $subtotal
                ];

                if ($this->cart->where('id', $cart->id)->update($data)) {   // Jika update berhasil
                    $this->session->set_flashdata('success', 'Produk berhasil ditambahkan');
                } else {
                    $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
                }

                redirect(base_url());
            }

            // --- Insert cart baru ---
            $data = [
                'id_user'       => $this->id,
                'id_product'    => $input->id_product,
                'qty'           => $input->qty,
                'subtotal'      => $subtotal
            ];

            if ($this->cart->create($data)) {   // Jika insert berhasil
                $this->session->set_flashdata('success', 'Produk berhasil ditambahkan');
            } else {
                $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            }

            redirect(base_url());
        }
    }

    /**
     * Update kuantitas di keranjang belanja
     */
    public function update($id)
    {
        if (!$_POST || $this->input->post('qty') < 1) {
            $this->session->set_flashdata('error', 'Kuantitas tidak boleh kosong');
            redirect(base_url('cart/index'));
        }

        $data['content']    = $this->cart->where('id', $id)->first();   // Mengambil data dari cart

        if (!$data['content']) {
            $this->session->set_flashdata('warning', 'Data tidak ditemukan');
            redirect(base_url('cart/index'));
        }

        // Mengambil data produk yang dipilih, untuk mendapatkan price
        $this->cart->table  = 'product';
        $product            = $this->cart->where('id', $data['content']->id_product)->first();

        // Menghitung subtotal baru
        $data['input']      = (object) $this->input->post(null, true);
        $subtotal           = $data['input']->qty * $product->price;

        // Update data
        $cart = [
            'qty'       => $data['input']->qty,
            'subtotal'  => $subtotal
        ];

        $this->cart->table  = 'cart';
        if ($this->cart->where('id', $id)->update($cart)) {   // Jika update berhasil
            $this->session->set_flashdata('success', 'Kuantitas berhasil diubah');
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
        }

        redirect(base_url('cart/index'));
    }

    /**
     * Delete suatu cart di halaman cart
     */
    public function delete($id)
    {
        if (!$_POST) {
            // Jika diakses tidak dengan menggunakan method post, kembalikan ke home (forbidden)
            redirect(base_url('cart/index'));
        }

        if (!$this->cart->where('id', $id)->first()) {  // Jika data tidak ditemukan
            $this->session->set_flashdata('warning', 'Maaf data tidak ditemukan');
            redirect(base_url('cart/index'));
        }

        if ($this->cart->where('id', $id)->delete()) {  // // Lakukan delete & Jika delete berhasil
            $this->session->set_flashdata('success', 'Cart berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Oops, terjadi suatu kesalahan');
        }

        redirect(base_url('cart/index'));
    }
}

/* End of file Cart.php */
