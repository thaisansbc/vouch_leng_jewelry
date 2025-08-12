<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Setting_api extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getSystem_Setting()
    {
        return $this->db->get_where('settings')->row();
    }
    public function getPOS_Setting()
    {
        return $this->db->get_where('pos_settings')->row();
    }
    public function getShopSetting()
    {
        return $this->db->get_where('shop_settings')->row();
    }
    public function getAllCategories()
    {
        return $this->db->get_where('categories')->result();
    }
    public function getAllbrands()
    {
        return $this->db->get_where('brands')->result();
    }
    public function getCashAccounts()
    {
        $this->db->order_by("order");
        return $this->db->get_where('cash_accounts')->result();
    }
    public function getAllWarehouse()
    {
        //$this->db->order_by("order");
        return $this->db->get_where('warehouses')->result();
    }
    public function getAllCurrencies()
    {
        //$this->db->order_by("order");
        return $this->db->get_where('currencies')->result();
    }
}
