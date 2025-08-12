<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sync_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function deleteExtraTables()
    {
        $this->db->update('settings', ['version' => '3.2.10'], ['setting_id' => 1]);
        $this->load->dbforge();
        $this->dbforge->drop_table('billers');
        $this->dbforge->drop_table('customers');
        $this->dbforge->drop_table('suppliers');
        $this->dbforge->drop_table('users_groups');
        $this->dbforge->drop_table('invoice_types');
        $this->dbforge->drop_table('discounts');
        $this->dbforge->drop_table('comment');
        return true;
    }

    public function getAllBillers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('billers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCustomers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('customers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSuppliers()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('suppliers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllTransferItems()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('transfer_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUserGroups()
    {
        $this->db->order_by('id', 'desc');
        $q = $this->db->get('users_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function importBillers()
    {
        $billers = $this->getAllBillers();
        if ($billers) {
            foreach ($billers as $biller) {
                $bid = $biller->id;
                unset($biller->id);
                $biller->group_name = 'biller';
                $this->db->insert('companies', $biller);
                $biller_id = $this->db->insert_id();
                $ids[]     = ['new' => $biller_id, 'old' => $bid];
            }
            if (isset($ids)) {
                krsort($ids);
                foreach ($ids as $id) {
                    $this->db->update('sales', ['biller_id' => $id['new']], ['biller_id' => $id['old']]);
                    $this->db->update('quotes', ['biller_id' => $id['new']], ['biller_id' => $id['old']]);
                }
            }
            return true;
        }
        return false;
    }

    public function importCustomers()
    {
        $customers = $this->getAllCustomers();
        if ($customers) {
            foreach ($customers as $customer) {
                $cid = $customer->id;
                unset($customer->id);
                $customer->group_id            = 3;
                $customer->group_name          = 'customer';
                $customer->customer_group_id   = 1;
                $customer->customer_group_name = 'General';
                $this->db->insert('companies', $customer);
                $customer_id = $this->db->insert_id();
                $ids[]       = ['new' => $customer_id, 'old' => $cid];
            }
            if (isset($ids)) {
                krsort($ids);
                foreach ($ids as $id) {
                    $this->db->update('sales', ['customer_id' => $id['new']], ['customer_id' => $id['old']]);
                    $this->db->update('quotes', ['customer_id' => $id['new']], ['customer_id' => $id['old']]);
                }
            }
            return true;
        }
        return false;
    }

    public function importSuppliers()
    {
        $suppliers = $this->getAllSuppliers();
        if ($suppliers) {
            foreach ($suppliers as $supplier) {
                $sid = $supplier->id;
                unset($supplier->id);
                $supplier->group_id   = 4;
                $supplier->group_name = 'supplier';
                $this->db->insert('companies', $supplier);
                $supplier_id = $this->db->insert_id();
                $ids[]       = ['new' => $supplier_id, 'old' => $sid];
            }
            if (isset($ids)) {
                krsort($ids);
                foreach ($ids as $id) {
                    $this->db->update('purchases', ['supplier_id' => $id['new']], ['supplier_id' => $id['old']]);
                }
            }
            return true;
        }
        return false;
    }

    public function resetDamageProductsTable()
    {
        $this->db->truncate('adjustments');
        return true;
    }

    public function resetDeliveriesTable()
    {
        $this->db->truncate('deliveries');
        return true;
    }

    public function resetProductsTable()
    {
        $this->db->truncate('products');
        return true;
    }

    public function resetPurchasesTable()
    {
        $this->db->truncate('purchases');
        $this->db->truncate('purchase_items');
        return true;
    }

    public function resetQuotesTable()
    {
        $this->db->truncate('quotes');
        $this->db->truncate('quote_items');
        return true;
    }

    public function resetSalesTable()
    {
        $this->db->truncate('sales');
        $this->db->truncate('sale_items');
        return true;
    }

    public function resetTransfersTable()
    {
        $this->db->truncate('transfers');
        $this->db->truncate('transfer_items');
        return true;
    }

    public function updatePurchases()
    {
        $this->db->query('UPDATE ' . $this->db->dbprefix('purchases') . " SET paid=grand_total, status='received', payment_status='paid'");
        return true;
    }

    public function updateQuotes()
    {
        $this->db->query('UPDATE ' . $this->db->dbprefix('quotes') . " SET status='completed'");
        return true;
    }

    public function updateSales()
    {
        $this->db->query('UPDATE ' . $this->db->dbprefix('sales') . " SET paid=grand_total, sale_status='completed', payment_status='paid'");
        return true;
    }

    public function updateTransfers()
    {
        $transfers = $this->getAllTransferItems();
        foreach ($transfers as $transfer) {
            unset($transfer->id, $transfer->product_unit);
            $this->db->insert('purchase_items', $transfer);
        }
        $this->db->truncate('transfer_items');
        $this->db->query('UPDATE ' . $this->db->dbprefix('transfers') . " SET status='completed'");
        return true;
    }

    public function userGroups()
    {
        $ugs = $this->getUserGroups();
        if ($ugs) {
            foreach ($ugs as $ug) {
                if ($ug->group_id > 2) {
                    $this->db->update('users', ['group_id' => ($ug->group_id + 2)], ['id' => $ug->user_id]);
                } else {
                    $this->db->update('users', ['group_id' => $ug->group_id], ['id' => $ug->user_id]);
                }
            }
            return true;
        }
        return false;
    }
    //----------Sync POS-------------
    public function addPOS($pos = false,$pos_items = false,$pos_payments =false, $pos_acc_trans = false, $pos_acc_pay_trans = false, $pos_registers = false,$pos_register_items = false){

        $pushed_sids = false;
        $pushed_reg_ids = false;
        if($pos){
            $sale_items = false;
            $stockmoves = false;
            $acc_trans = false;
            foreach($pos as $row){
                if($row){
                    $old_sale_id = $row['id'];
                    unset($row['id']);
                    $row = (array) $row;
                    $row['pushed'] = 1;
                    if($this->db->insert("sales",$row)){
                        $pushed_sids[] = $old_sale_id;
                        $new_sale_id = $this->db->insert_id();

                        if(isset($pos_items["s_".$old_sale_id]) && $pos_items["s_".$old_sale_id]){
                            foreach($pos_items["s_".$old_sale_id] as $pos_item){
                                $pos_item = (array) $pos_item;
                                unset($pos_item['id']);
                                $pos_item['sale_id'] = $new_sale_id;
                                $sale_items[] = $pos_item;
                            }
                        }

                        /*
                        if(isset($pos_acc_trans["s_".$old_sale_id]) && $pos_acc_trans["s_".$old_sale_id]){
                            foreach($pos_acc_trans["s_".$old_sale_id] as $pos_acc_tran){
                                $pos_acc_tran = (array) $pos_acc_tran;
                                unset($pos_acc_tran['id']);
                                $pos_acc_tran['tran_no'] = $new_sale_id;
                                $acc_trans[] = $pos_acc_tran;
                            }
                        }
                        
                        if(isset($pos_payments["s_".$old_sale_id]) && $pos_payments["s_".$old_sale_id]){
                            foreach($pos_payments["s_".$old_sale_id] as $pos_payment){
                                $pos_payment = (array) $pos_payment;
                                $old_payment_id = $pos_payment['id'];
                                unset($pos_payment['id']);
                                $pos_payment['sale_id'] = $new_sale_id;
                                if($this->db->insert("payments",$pos_payment)){
                                    $new_payment_id = $this->db->insert_id();
                                    if(isset($pos_acc_pay_trans["p_".$old_payment_id]) && $pos_acc_pay_trans["p_".$old_payment_id]){
                                        foreach($pos_acc_pay_trans["p_".$old_payment_id] as $pos_acc_pay_tran){
                                            $pos_acc_pay_tran = (array) $pos_acc_pay_tran;
                                            unset($pos_acc_pay_tran['id']);
                                            $pos_acc_pay_tran['tran_no'] = $new_payment_id;
                                            $acc_trans[] = $pos_acc_pay_tran;
                                        }
                                    }
                                }
                            }
                        }*/
                    }
                }
            }
            
            if($sale_items){
                $this->db->insert_batch("sale_items",$sale_items);
            }
            if($acc_trans){
                $this->db->insert_batch("gl_trans",$acc_trans);
            }
        }
     
       
        if($pos_registers){
            foreach($pos_registers as $pos_register){
                if($pos_register){
                    $old_register_id = $pos_register['id'];
                    unset($pos_register['id']);
                    $pos_register = (array) $pos_register;
                    $pos_register['pushed'] = 1;
                    if($this->db->insert("pos_register",$pos_register)){
                        $pushed_reg_ids[] = $old_register_id;
                        $new_register_id = $this->db->insert_id();
                        if(isset($pos_register_items["r_".$old_register_id]) && $pos_register_items["r_".$old_register_id]){
                            foreach($pos_register_items["r_".$old_register_id] as $pos_register_item){
                                $pos_register_item = (array) $pos_register_item;
                                unset($pos_register_item['id']);
                                $pos_register_item['register_id'] = $new_register_id;
                                //$register_items[] = $pos_register_item;
                            }
                        }
                    }
                }
            }
            /*if($register_items){
                $this->db->insert_batch("pos_register_items",$register_items);
            }*/
        }
         
        if($pushed_sids || $pushed_reg_ids){
            return array(
                "pushed_sids"       =>$pushed_sids,
                "pushed_reg_ids"    =>$pushed_reg_ids);
        }else{
            return false;
        }
        
    }
    //-----------local Sync POS--------------
    function getPOS(){
        $this->db->where("sales.pushed",0);
        $q = $this->db->get_where("sales",array("pos"=>1));
        if ($q->num_rows() > 0) {
            foreach (($q->result_array()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    function getPOSItems(){
        $this->db->select("sale_items.*");
        $this->db->join("sales","sales.id = sale_items.sale_id","INNER");
        $this->db->where("sales.pos",1);
        $this->db->where("sales.pushed",0);
        $q = $this->db->get("sale_items");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data["s_".$row->sale_id][] = $row;
            }
            return $data;
        }
        return false;
    }
    function getPOSPayments(){
        $this->db->select("payments.*");
        $this->db->join("sales","sales.id = payments.sale_id","INNER");
        $this->db->where("sales.pos",1);
        $this->db->where("sales.pushed",0);
        $q = $this->db->get("payments");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data["s_".$row->sale_id][] = $row;
            }
            return $data;
        }
        return false;
    }
    function getPOSRegisters(){
        $this->db->where("status","close");
        $this->db->where("pushed",0);
        $q = $this->db->get("pos_register");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    function getPOSPaymentAccs(){
        $this->db->select("gl_trans.*");
        $this->db->join("payments","gl_trans.tran_no = payments.id","INNER");
        $this->db->join("sales","sales.id = payments.sale_id","INNER");
        $this->db->where("gl_trans.tran_type","Payment");
        $this->db->where("sales.pos",1);
        $this->db->where("sales.pushed",0);
        $q = $this->db->get("gl_trans");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data["p_".$row->tran_no][] = $row;
            }
            return $data;
        }
        return false;
    }
    function getPOSRegisterItems(){
        $this->db->select("pos_register_items.*");
        $this->db->join("pos_register","pos_register.id = pos_register_items.register_id","INNER");
        $this->db->where("pos_register.pushed",0);
        $this->db->where("pos_register.status","close");
        $q = $this->db->get("pos_register_items");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data["r_".$row->register_id][] = $row;
            }
            return $data;
        }
        return false;
    }
    function getPOSAccTrans(){
        $this->db->select("gl_trans.*");
        $this->db->join("sales","sales.id = gl_trans.tran_no","INNER");
        $this->db->where("gl_trans.tran_type","Sale");
        $this->db->where("sales.pos",1);
        $this->db->where("sales.pushed",0);
        $q = $this->db->get("gl_trans");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data["s_".$row->tran_no][] = $row;
            }
            return $data;
        }
        return false;
    }
    function updatePushed($data = false){
        if($data->pushed_sids || $data->pushed_reg_ids){
            if($data->pushed_sids){
                $this->db->where_in("id",$data->pushed_sids);
                $this->db->update("sales",array("pushed"=>1));
            }
            if($data->pushed_reg_ids){
                $this->db->where_in("id",$data->pushed_reg_ids);
                $this->db->update("pos_register",array("pushed"=>1));
            }
            return true;
        }
        return false;
    }
    //---------close local---------
}
