<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Model extends CI_Model
{
    protected $table = '';
    protected $perPage = 5; // Banyak data tiap halaman

    public function __construct()
    {
        parent::__construct();

        if (!$this->table) {    // Jika nilai table kosong 
            $this->table = strtolower(
                // ubah jadi huruf kecil dan hapus suffix '_model'
                // param 3 mengarahkan ke model yang digunakan
                str_replace('_model', '', get_class($this))
            );
        }
    }

    // Form validation
    public function validate()
    {
        $this->load->library('form_validation');

        // Pesan error
        $this->form_validation->set_error_delimiters(
            '<small class="form-text text-danger">',
            '</small>'
        );

        // Panggil rules
        // getValidationRules() diletakkan di file-file model turunan nanti karena tiap model punya validationnya sendiri
        $validationRules = $this->getValidationRules();

        $this->form_validation->set_rules($validationRules);    // Set rules dari model2 nanti

        return $this->form_validation->run();  // Jalankan validasi
    }

    /* ================= Query Builder ================= */

    public function select($columns)
    {
        // Param columns beripe array
        $this->db->select($columns);
        return $this;
    }

    public function where($column, $condition)
    {
        $this->db->where($column, $condition);
        return $this;
    }

    public function like($column, $condition)
    {
        $this->db->like($column, $condition);
        return $this;
    }

    public function orLike($column, $condition)
    {
        $this->db->or_like($column, $condition);
        return $this;
    }

    public function join($table, $type = 'left')
    {
        // Param 1: table yang ingin digabungkan
        // Param 2 misal: mencari produk berdasarkan kategori --> "product.id_category = category.id"
        $this->db->join($table, "$this->table.id_$table = $table.id", $type);
        return $this;
    }

    public function orderBy($column, $order = 'asc')
    {
        $this->db->order_by($column, $order);
        return $this;
    }

    /* ------ Methods output ------ */

    public function first()
    {
        // Menghasilkan 1 row berupa objek
        return $this->db->get($this->table)->row();
    }

    public function get()
    {
        // Menghasilkan banyak row berupa objek
        return $this->db->get($this->table)->result();
    }

    public function count()
    {
        return $this->db->count_all_results($this->table);
    }

    /* ------ Methods C-U-D ------ */

    public function create($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();  // Mendapatkan id dari data yang diinsert
    }

    public function update($data)
    {
        return $this->db->update($this->table, $data);
    }

    public function delete()
    {
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    public function paginate($page)
    {
        $this->db->limit(
            $this->perPage,                     // Limit data yang dimunculkam
            $this->calculateRealOffset($page)   // Mulai dari
        );
        return $this;
    }

    public function calculateRealOffset($page)
    {
        if (is_null($page) || empty($page)) {
            $offset = 0;    // Jika page kosong, mulai dari data pertama
        } else {
            $offset = ($page * $this->perPage) - $this->perPage;
        }

        return $offset;
    }

    public function makePagination($baseUrl, $uriSegment, $totalRows = null)
    {
        $this->load->library('pagination');

        $config = [
            'base_url'          => $baseUrl,
            'uri_segment'       => $uriSegment,
            'per_page'          => $this->perPage,
            'total_rows'        => $totalRows,
            'use_page_numbers'  => true,

            // Desain pagination dengan Boostrap v4
            'full_tag_open'     => '<ul class="pagination">',
            'full_tag_close'    => '</ul>',
            'attributes'        => ['class' => 'page-link'],    // Atribut dari setiap pagination
            'first_link'        => false,                       // Tidak ada menu ke first page
            'last_link'         => false,                       // Tidak ada menu ke last page
            'first_tag_open'    => '<li class="page-item">',     // Memulai baris halaman (1, 2, 3, ...)
            'first_tag_close'   => '</li>',
            'prev_link'         => '&laquo',
            'prev_tag_open'     => '<li class="page-item">',    // Link untuk menuju halaman sebelumnya
            'prev_tag_close'    => '</li>',
            'next_link'         => '&raquo',
            'next_tag_open'     => '<li class="page-item">',    // Link untuk menuju halaman selanjutnya
            'next_tag_close'    => '</li>',
            'last_tag_open'     => '<li class="page-item">',
            'last_tag_close'    => '</li>',
            'cur_tag_open'      => '<li class="page-item active"><a href="#" class="page-link">',
            'cur_tag_close'     => '<span class="sr-only">(current)</span></a></li>',
            'num_tag_open'      => '<li class="page-item">',
            'num_tag_close'     => '</li>'
        ];

        $this->pagination->initialize($config);

        return $this->pagination->create_links();     // Generate pagination
    }
}

/* End of file MY_Model.php */
