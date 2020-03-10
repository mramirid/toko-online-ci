<?php

/**
 * Fungsi untuk memuat data dengan format option (Drowdown)
 * Param table   : table mana yang akan dimuat
 * Param columns : berupa key column apa saja yang diambil
 */
function getDropdownList($table, $columns)
{
    $CI =& get_instance();  // Memanggil core system dari CI, agar kita dapat memanggil class2 dari CI
    $query = $CI->db->select($columns)->from($table)->get();

    if ($query->num_rows() >= 1) {
        // '' sebagai value dari option select & select akan muncul di browser
        $option1 = ['' => '- Select -'];
        $option2 = array_column($query->result_array(), $columns[1], $columns[0]);   // Param 2 & 3 adalah key
        $options = $option1 + $option2;

        return $options;
    }

    return $options = ['' => '- Select -'];
}

/**
 * Untuk meload kategori dari table kategori
 */
function getCategories()
{
    $CI =& get_instance();
    $query = $CI->db->get('category')->result();
    return $query;
}

/**
 * Menghitung jumlah cart (pada navbar) sesuai dengan id user yang login
 */
function getCart()
{
    $CI =& get_instance();
    $userId = $CI->session->userdata('id');

    if ($userId) {
        // Hitung banyak cart suatu user
        $query = $CI->db->where('id_user', $userId)->count_all_results('cart');
        return $query;
    }

    return false;
}

/**
 * Mengenkripsi input
 */
function hashEncrypt($input)
{
    $hash = password_hash($input, PASSWORD_DEFAULT);
    return $hash;
}

/**
 * Mendecrypt hash password dari table user
 * Mengembalikan true jika plain-text sama
 */
function hashEncryptVerify($input, $hash)
{
    if (password_verify($input, $hash)) {
        return true;
    } else {
        return false;
    }
}