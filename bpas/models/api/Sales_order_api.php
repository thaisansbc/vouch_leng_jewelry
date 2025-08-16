<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_order_api extends CI_Model
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
        $this->db->from('sales_order');
        return $this->db->count_all_results();
    }
    public function getSalesOrder($filters = [])
    {
        if ($filters['customer']) {
            $this->db->where('customer', $filters['customer']);
        }
        if ($filters['customer_id']) {
            $this->db->where('customer_id', $filters['customer_id']);
        }
        if ($filters['created_by']) {
            $this->db->where('created_by', $filters['created_by']);
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

        return $this->db->get('sales_order')->result();
    }
    public function getSaleOrderItems($sale_id)
    {
        return $this->db->get_where('sale_order_items', ['sale_order_id' => $sale_id])->result();
    }
    public function getWarehouseByID($id)
    {
        return $this->db->get_where('warehouses', ['id' => $id], 1)->row();
    }
    public function getBillersByID($id)
    {
        return $this->db->get_where('companies', ['group_name'=>'biller','id' => $id], 1)->row();
    }
    public function getPaymentBySaleID($id)
    {
        return $this->db->get_where('payments', ['sale_order_id' => $id])->result();
    }
     public function getUser($id)
    {
        $uploads_url = base_url('assets/uploads/');
        $this->db->select("CONCAT('{$uploads_url}', avatar) as avatar_url, email, first_name, gender, id, last_name, username");
        return $this->db->get_where('users', ['id' => $id], 1)->row();
    }
    public function addSale($data = [], $items = [], $payment = [], $si_return = [])
    {
        $this->db->trans_start();
        if ($this->db->insert('sales_order', $data)) {
            $sale_id = $this->db->insert_id();
            //$item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;

            if ($this->site->getReference('sr') == $data['reference_no']) {
                $this->site->updateReference('sr');
            }
            foreach ($items as $item) {
                $item['sale_order_id'] = $sale_id;
                $this->db->insert('sale_order_items', $item);
                
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }
    public function getProductVariantByID($id)
    {
        return $this->db->get_where('product_variants', ['id' => $id], 1)->row();
    }
}
