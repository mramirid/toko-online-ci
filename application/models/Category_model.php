<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Category_model extends MY_Model
{
    // Tidak perlu definisi (override) nama table secara manual karena nama model = nama table

    public function getDefaultValues()
    {
        return [
            'id'    => '',
            'slug'  => '',
            'title' => ''
        ];
    }

    public function getValidationRules()
    {
        $validationRules = [
            [
                'field' => 'slug',
                'label' => 'Slug',
                'rules' => 'trim|required|callback_unique_slug'
            ],
            [
                'field' => 'title',
                'label' => 'Kategori',
                'rules' => 'trim|required'
            ]
        ];
        
        return $validationRules;
    }
}

/* End of file Category_model.php */
