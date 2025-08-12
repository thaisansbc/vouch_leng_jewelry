<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Promos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPromo($data = [],$data_buy = [], $data_get = [])
    {
        // var_dump($data);
       
        
        if ($this->db->insert('promos', $data)) {
            $cid = $this->db->insert_id();
           $i = sizeof($data_buy);
            for ($r = 0; $r < $i; $r++) {
                $product = $this->site->getProductByID($_POST['product2buy'][$r]);
                $array_buy[] = [
                    'qty'           => $_POST['qtytosale'][$r],
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_code'  => $product->code,
                    'type'          => 'product2buy',
                    'promos_id'          => $cid,
                ];
            }
            $data_get = $this->input->post('product2get[]');
            $j = sizeof($data_get);
            for ($r = 0; $r < $j; $r++) {
                $product = $this->site->getProductByID($_POST['product2get'][$r]);
                $array_get[] = [
                    'qty'           => $_POST['qtytoget'][$r],
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_code'  => $product->code,
                    'type'          => 'product2get',
                    'promos_id'          => $cid,
                ];
            }
         
        // exit();
            if($this->db->insert_batch('promos_items', $array_buy) &&
            $this->db->insert_batch('promos_items', $array_get)){
            return $cid;
            }
        }
        return false;
    }
    public function updatePromo($id, $data = [], $data_buy = [], $data_get = [])
        {   
            $this->db->where('id', $id);
            if ($this->db->update('promos', $data) && $this->db->delete('promos_items', ['promos_id' => $id])) {
                $i = sizeof($data_buy);
            for ($r = 0; $r < $i; $r++) {
                $product = $this->site->getProductByID($_POST['product2buy'][$r]);
                $array_buy[] = [
                    'qty'           => $_POST['qtytosale'][$r],
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_code'  => $product->code,
                    'type'          => 'product2buy',
                    'promos_id'          => $id,
                ];
            }
            $data_get = $this->input->post('product2get[]');
            $j = sizeof($data_get);
            for ($r = 0; $r < $j; $r++) {
                $product = $this->site->getProductByID($_POST['product2get'][$r]);
                $array_get[] = [
                    'qty'           => $_POST['qtytoget'][$r],
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_code'  => $product->code,
                    'type'          => 'product2get',
                    'promos_id'          => $id,
                ];
            }
            
                if($this->db->insert_batch('promos_items', $array_buy) &&
                    $this->db->insert_batch('promos_items', $array_get)){
                    return true;
                }
            }
            return false;
        }
    public function addPromos($data = [])
    {
        if ($this->db->insert_batch('promos', $data)) {
            return true;
        }
        return false;
    }

    public function deletePromo($id)
    {
        if ($this->db->delete('promos', ['id' => $id]) && $this->db->delete('promos_items', ['promos_id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllPromos()
    {
        $q = $this->db->get('promos');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductPromosByProductID($id, $qty)
    {
           if($qty != -1){
        $this->db->where('qty <=', $qty);
        }
        $q = $this->db->get_where('promos_items', ['product_id' => $id,'type'=>"product2buy"]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPromosByProduct($pId, $qty)
    {   
        $bl = $this->getProductPromosByProductID($pId, $qty);
        $today = date('Y-m-d H:i:s');
        $this->db->group_start()->where('end_date >=', $today)->or_where('end_date IS NULL')->group_end();
        $this->db->group_start()->where('start_date <=', $today)->or_where('start_date IS NULL')->group_end();
        // $this->db->where('start_date', $today)->or_where('start_date IS NULL')->group_end();
        $this->db->join('promos_items', 'promos_items.promos_id = promos.id', 'left');
        if(!empty($bl)){
        $q = $this->db->get_where('promos', ['promos.id' => $bl->promos_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if($row->type == "product2get"){
                    $data[] = $row;
                }
               }
            return $data;
            }
        }
        return false;
    }
    public function getPromosItemByID($pId, $qty)
    {   
        $bl = $this->getProductPromosByProductID($pId, $qty);
        $today = date('Y-m-d');
        $this->db
        ->group_start()->where('start_date <=', $today)->or_where('start_date IS NULL')->group_end()
        ->group_start()->where('end_date >=', $today)->or_where('end_date IS NULL')->group_end();
        $this->db->join('promos_items', 'promos_items.promos_id = promos.id', 'left');
        if(!empty($bl)){
        $q = $this->db->get_where('promos', ['promos.id' => $bl->promos_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if($row->type == "product2buy"){
                    $data[] = $row;
                }
            }
            return $data;
        }
        }
        return false;
    }
     public function getPromotionByProduct($wh, $category_id, $product_id = null){
        
        $today = date('Y-m-d');
        $this->db
        ->group_start()->where('promotions.start_date <=', $today)->or_where('promotions.start_date IS NULL')->group_end()
        ->group_start()->where('promotions.end_date >=', $today)->or_where('promotions.end_date IS NULL')->group_end();
        $this->db->join('promotions', 'promotion_categories.promotion_id = promotions.id', 'left');
        $this->db->join('products', 'products.category_id = promotion_categories.category_id', 'left');
        $this->db->where("warehouse_id", $wh);
        $this->db->where("products.id", $product_id);
        $q = $this->db->get_where('promotion_categories', ['promotion_categories.category_id' => $category_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                    $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    // public function getPromotionByProduct($wh, $category_id){
    //     $today = date('Y-m-d');
    //     $this->db
    //     ->group_start()->where('start_date <=', $today)->or_where('start_date IS NULL')->group_end()
    //     ->group_start()->where('end_date >=', $today)->or_where('end_date IS NULL')->group_end();
    //     $this->db->join('promotions', 'promotion_categories.promotion_id = promotions.id', 'left');
    //     $this->db->where("warehouse_id", $wh);
    //     $q = $this->db->get_where('promotion_categories', ['category_id' => $category_id]);
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //                 $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return false;
    // }
    public function getPromosItemByItemPOS($pId, $qty)
    {   
        $bl = $this->getProductPromosByProductID($pId, $qty);
        $today = date('Y-m-d');
        $this->db
        ->group_start()->where('start_date <=', $today)->or_where('start_date IS NULL')->group_end()
        ->group_start()->where('end_date >=', $today)->or_where('end_date IS NULL')->group_end();
        $this->db->join('promos_items', 'promos_items.promos_id = promos.id', 'left');
        if(!empty($bl)){
        $q = $this->db->get_where('promos', ['promos.id' => $bl->promos_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if($row->type == "product2buy"){
                    $data[] = $row;
                }
            }
            return $data;
        }
        }
        return false;
    }
    public function getPromoByID($id)
    {
        $q = $this->db->get_where('promos', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    // public function getPromosByProduct($pId)
    // {
    //      $today = date('Y-m-d h:i:s');
    //     $this->db
    //     ->group_start()->where('start_date <=', $today)->or_where('start_date IS NULL')->group_end()
    //     ->group_start()->where('end_date >=', $today)->or_where('end_date IS NULL')->group_end();
    //     $q = $this->db->get_where('promos', ['product2buy' => $pId]);
    //     if ($q->num_rows() > 0) {
    //         foreach (($q->result()) as $row) {
    //             $data[] = $row;
    //         }
    //         return $data;
    //     }
    //     return false;
    // }

    // public function updatePromo($id, $data = [])
    // {
    //     $this->db->where('id', $id);
    //     if ($this->db->update('promos', $data)) {
    //         return true;
    //     }
    //     return false;
    // }
    public function getPromotionByMultiProductUnit($wh, $product_code, $product_id){
        
        $today = date('Y-m-d');
        $this->db
        ->group_start()->where('promotions.start_date <=', $today)->or_where('promotions.start_date IS NULL')->group_end()
        ->group_start()->where('promotions.end_date >=', $today)->or_where('promotions.end_date IS NULL')->group_end();
        $this->db->join('promotions', 'promotion_categories.promotion_id = promotions.id', 'left');
        $this->db->join('products', 'products.id = promotion_categories.product_id', 'left');
        $this->db->where("warehouse_id", $wh);
        $this->db->where("products.id", $product_id);
        $q = $this->db->get_where('promotion_categories', ['promotion_categories.product_code' => $product_code]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                    $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}
