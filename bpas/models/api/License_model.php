<?php

defined('BASEPATH') or exit('No direct script access allowed');

class License_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function VerifyLicense($user_name,$license_key,$url_addresses){
        $q = $this->db->get_where('license', array(
                'user_name'     => $user_name,
                'license_key'   => $license_key,
                'url_addresses' => $url_addresses
            ), 1);
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return array("start_date"=>$data->start_date);
        }
        return FALSE;
    }
}
