<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pos_api extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function countSales($filters = [], $ref = null)
    {
        if ($filters['customer']) {
            $this->db->where('customer', $filters['customer']);
        }
        if ($filters['customer_id']) {
            $this->db->where('customer_id', $filters['customer_id']);
        }
        if ($filters['start_date']) {
            $this->db->where('date >=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $this->db->where('date <=', $filters['end_date']);
        }
        $this->db->where('pos',1);
        $this->db->from('sales');
        return $this->db->count_all_results();
    }

    public function getProductVariantByID($id)
    {
        return $this->db->get_where('product_variants', ['id' => $id], 1)->row();
    }

    public function getSale($filters)
    {
        if (!empty($sales = $this->getSales($filters))) {
            return array_values($sales)[0];
        }
        return false;
    }

    public function getPrintOrderItems()
    {
        return $this->db->get_where('print_order_items', ['status' => 1])->result();
    }

    public function getSaleItems($sale_id)
    {
        return $this->db->get_where('sale_items', ['sale_id' => $sale_id])->result();
    }

    public function getSales($filters = [])
    {
        if ($filters['customer']) {
            $this->db->where('customer', $filters['customer']);
        }
        if ($filters['customer_id']) {
            $this->db->where('customer_id', $filters['customer_id']);
        }
        if ($filters['start_date']) {
            $this->db->where('date >=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $this->db->where('date <=', $filters['end_date']);
        }
        if ($filters['reference']) {
            $this->db->where('reference_no', $filters['reference']);
        } else {
            $this->db->order_by($filters['order_by'][0], $filters['order_by'][1] ? $filters['order_by'][1] : 'desc');
            $this->db->limit($filters['limit'], ($filters['start'] - 1));
        }
        $this->db->where('pos',1);
        return $this->db->get('sales')->result();
    }

    public function getUser($id)
    {
        $uploads_url = base_url('assets/uploads/');
        $this->db->select("CONCAT('{$uploads_url}', avatar) as avatar_url, email, first_name, gender, id, last_name, username");
        return $this->db->get_where('users', ['id' => $id], 1)->row();
    }

    public function getWarehouseByID($id)
    {
        return $this->db->get_where('warehouses', ['id' => $id], 1)->row();
    }
    public function getBillerByID($id)
    {
        return $this->db->get_where('companies', ['group_name'=>'biller','id' => $id], 1)->row();
    }
    public function getAllInvoiceItemsAddon($sale_id){
        $q = $this->db->get_where('sale_addon_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}
