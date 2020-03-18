<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Checkout extends MY_Controller
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

    public function index($input = null)
    {
        // Mengambil list cart yang akan dicheckout
        $this->checkout->table  = 'cart';
        $data['cart'] = $this->checkout->select([
                'cart.id', 'cart.qty', 'cart.subtotal',
                'product.title', 'product.image', 'product.price'
            ])
            ->join('product')
            ->where('cart.id_user', $this->id)
            ->get();

        if (!$data['cart']) {
            $this->session->set_flashdata('warning', 'Tidak ada produk di dalam keranjang');
            redirect(base_url('home'));
        }

        // Jika input kosong (user belum input), maka isi form dari awal (form kosong)
        $data['input']  = $input ? $input : (object) $this->checkout->getDefaultValues();
        $data['title']  = 'Checkout';
        $data['page']   = 'pages/checkout/index';

        $this->view($data);
    }

    /**
     * Fungsi ini memasukan suatu pesanan ke tabel 'orders' 
     * dan memindahkan list cart user ke 'order_detail'
     */
    public function create()
    {
        if (!$_POST) {
            redirect(base_url('checkout'));
        } else {
            $input = (object) $this->input->post(null, true);
        }

        if (!$this->checkout->validate()) { // Jika validasi gagal, kembalikan ke index dengan kirim last input
            return $this->index($input);
        }

        // Menghitung total dari subtotal order suatu user
        $total = $this->db->select_sum('subtotal')
            ->where('id_user', $this->id)
            ->get('cart')
            ->row()         // Select first row
            ->subtotal;     // Select column subtotal

        // Menyiapkan insert table orders
        $data = [
            'id_user'   => $this->id,
            'date'      => date('Y-m-d'),
            'invoice'   => $this->id . date('YmdHis'),
            'total'     => $total,
            'name'      => $input->name,
            'address'   => $input->address,
            'phone'    => $input->phone,
            'status'    => 'waiting'
        ];

        // Jika insert berhasil, siapkan insert lagi ke dalam order_detail
        if ($id_orders = $this->checkout->create($data)) { 
            // Ambil list cart yang telah dipesan user
            $cart = $this->db->where('id_user', $this->id) 
                ->get('cart')
                ->result_array();

            // Modifikasi tiap cart
            foreach ($cart as $row) {
                $row['id_orders'] = $id_orders;             // Tambah kolom id_order
                unset($row['id'], $row['id_user']);         // Hapus kolom tidak penting
                $this->db->insert('order_detail', $row);    // Insert ke tabel order_detail
            }

            $this->db->delete('cart', ['id_user' => $this->id]);    // Hapus cart user sekarang

            $this->session->set_flashdata('success', 'Data berhasil disimpan');

            $data['title']      = 'Checkout Success';
            $data['content']    = (object) $data;
            $data['page']       = 'pages/checkout/success';

            $this->view($data);
        } else {
            $this->session->set_flashdata('error', 'Oops! Terjadi kesalahan');
            return $this->index($input);    // Kembali ke index dengan kirim last input
        }
    }
}

/* End of file Checkout.php */
