<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Money_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addExchangeCurrency($data)
    {
        if ($this->db->insert('money_exchange', $data)) {
            return true;
        }
        return false;
    }
    public function updateExchange($id = false, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update("money_exchange", $data)) {
            return true;
        }
        return false;
    }
    public function delete_exchange($id = false)
    {
        if ($this->db->delete("money_exchange", array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
    public function getExchangeByID($id = false)
    {
        $q = $this->db->get_where('money_exchange', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductNames($term, $warehouse_id, $pos = false, $limit = 15)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
    
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
            $this->db->select("
                    products.*, 
                    FWP.quantity as quantity, categories.id as category_id, categories.name as category_name"
                , false)
                ->join($wp, 'FWP.product_id = products.id', 'left')
                ->join('categories', 'categories.id=products.category_id', 'left')
                ->group_by('products.id');

        $this->db->where("
            (
                {$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR
                {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR
                {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR
                concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'
            )
        ");
            
        
        $this->db->order_by('products.name ASC');

        $this->db->where("{$this->db->dbprefix('products')}.type", 'service');
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getAllDiffCurrencies($code=null)
    {
        if ($code) {
            $this->db->where('code !=', $code);
        }
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addTransferRange($data = false, $company_id = false)
    {
        if($data && $company_id){
            $this->db->delete('money_transfer_range',array('company_id' => $company_id));
            $this->db->insert_batch('money_transfer_range',$data);
            return true;
        }
        return false;
    }
    public function getTransferRange($company_id = false)
    {
        $this->db->select('money_transfer_range.*')
        ->where('money_transfer_range.company_id',$company_id);
        $q = $this->db->get('money_transfer_range');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getMoneyExchangeRate()
    {
        $this->db->select('money_exchange_rate.*');
        $q = $this->db->get('money_exchange_rate');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addMoneyExchangeRate($data = false)
    {
        if($data){
             $this->db->truncate('money_exchange_rate');
            $this->db->insert_batch('money_exchange_rate',$data);
            return true;
        }
        return false;
    }
    public function getCurrencyName($term = false, $limit = false)
    {

        $this->db->select('*')
        ->where($this->db->dbprefix('currencies') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%'");
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getMoney_exchange_rate($from_currency, $to_currency)
    {
        $q = $this->db->get_where('money_exchange_rate', ['from_currency' => $from_currency, 'to_currency' => $to_currency], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function addExchange($data = [], $items = [], $stockmoves = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null, $module = null)
    {  
        $this->db->trans_start();
        if ($this->db->insert('money_exchange', $data)) {
            $sale_id = $this->db->insert_id();
            //=========Add Accounting =========//
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //=========End Accounting =========//
        
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            } elseif ($this->site->getReference('st') == $data['reference_no']) {
                $this->site->updateReference('st');
            }
            
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('money_exchange_items', $item);
                $sale_item_id = $this->db->insert_id();
            }
            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                $payment['sale_id']      = $sale_id;
                $payment['reference_no'] = !empty($payment['reference_no']) ? $payment['reference_no'] : $this->site->getReference('pay');
            
                    $this->db->insert('payments', $payment);
                
                //=========Add Accounting =========//
                $payment_id = $this->db->insert_id();
                if ($accTranPayments) {
                    foreach ($accTranPayments as $accTranPayment) {
                        $accTranPayment['tran_no']      = $sale_id;
                        $accTranPayment['payment_id']   = $payment_id;
                        $accTranPayment['reference_no'] = !empty($accTranPayment['reference_no']) ? $accTranPayment['reference_no'] : $payment['reference_no'];
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                //=========End Accounting =========//
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                if ($this->site->getReference('pp') == $payment['reference_no']) {
                    $this->site->updateReference('pp');
                }
                $this->site->syncSalePayments($sale_id);
            }
            $this->site->sendTelegram("sale",$sale_id,"added");
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }
        return false;
    }
}