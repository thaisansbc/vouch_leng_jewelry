<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_api extends CI_Model
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
    
    public function addSale($data = [], $items = [], $stockmoves = [], $payments = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null, $module = null)
    {  
  


        $this->db->trans_start();
        // var_dump($data);
        // exit();
        // var_dump($items);
        // exit();
        if ($this->db->insert('sales', $data)) {
            
            $sale_id = $this->db->insert_id();
           
            //=========Add Accounting =========//
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('pos') == $data['reference_no']) {
                $this->site->updateReference('pos');
            } elseif ($this->site->getReference('s') == $data['reference_no']) {
                $this->site->updateReference('s');
            }

            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $items_addon = $item['addon_items'];
                // var_dump($item);
                // var_dump($items_addon);

                unset($item['addon_items']);

                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($item['quantity'] < 0) {
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($item['product_id']);
                    }
                    if ($cal_cost) {
                        if ($item['option_id']) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                        }
                    }
                }
                // exit();
                if($items_addon){
                    foreach ($items_addon as $item_addon) {
                        $item_addon['sale_id'] = $sale_id;
                        if ($row_id == $item_addon['addon_row_id']) {
                            $item_addon['sale_item_id'] = $sale_item_id;
                            unset($item_addon['addon_row_id']);
                            $this->db->insert('sale_addon_items', $item_addon);
                        }
                    }
                }
               
                
                $combo_items = $this->getProductComboItems($item['product_id'], $data['warehouse_id']); 
                if (!empty($combo_items)) {
                    foreach ($combo_items as $combo_item) {
                        $item_combo = array(
                            'sale_product_id' => $item['product_id'],
                            'sale_id'         => $sale_id,
                            'sale_item_id'    => $sale_item_id,
                            'product_id'      => $combo_item->id,
                            'product_code'    => $combo_item->code,
                            'product_name'    => $combo_item->name,
                            'product_type'    => $combo_item->type,
                            'warehouse_id'    => $data['warehouse_id'],
                            'quantity'        => $combo_item->qty * $item['quantity'],
                            'net_unit_price'  => $combo_item->price,
                            'unit_price'      => $combo_item->price,
                            'currency'        => 'usd',
                            'tax_rate'        => null,
                            'option_id'       => null,
                            'subtotal'        => $combo_item->price * $item['quantity']
                        );
                        $this->db->insert('sale_combo_items', $item_combo);
                    }
                }
                if ($this->Settings->product_option && isset($item['max_serial'])) {
                    $this->db->update('product_options', ['start_no' => $item['serial_no'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                }
                
            } 

            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                if ($data['delivery_id']) {
                    $this->db->update('deliveries', ['sale_id' => $sale_id, 'sale_reference_no' => $data['reference_no'], 'status' => 'completed'], ['id' => $data['delivery_id']]);
                    $this->db->update('sales', ['delivery_status' => 'completed'], ['id' => $sale_id]);
                }
            }
            
            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payments)) {
                foreach ($payments as $payment) {
                    // var_dump($payment);
                    // exit();
                $payment['sale_id']      = $sale_id;
          
                $payment['reference_no'] = !empty($payment['reference_no']) ? $payment['reference_no'] : $this->site->getReference('pay');
                if ($payment['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                    $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update(
                            'companies', 
                            [
                                'deposit_amount' => ($customer->deposit_amount - $payment['amount']),
                                'deposit_amount_usd' => ($customer->deposit_amount_usd - $payment['amount_usd']),
                                'deposit_amount_khr' => ($customer->deposit_amount_khr - $payment['amount_khr']),
                                'deposit_amount_thb' => ($customer->deposit_amount_thb - $payment['amount_thb']),
                            ], 
                            ['id' => $customer->id]);
                    }
                    $this->db->insert('payments', $payment);
                }
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
            }
            
            if ($stockmoves) {
                foreach ($stockmoves as $stockmove) {
                    if ($stockmove['product_type'] != 'combo') {
                        $stockmove['transaction_id'] = $sale_id;
                        $this->db->insert('stock_movement', $stockmove);
                        if ($this->site->stockMovement_isOverselling($stockmove)) {
                            return false;
                        }
                    }
                }
            }
            $customer = $this->site->getCompanyByID($data['customer_id']);
          
            if ($customer->save_point) {
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($data['saleman_by']);
         
            if (isset($staff->save_point) && $staff->save_point) {
                $this->bpas->update_award_points($data['grand_total'], null, $data['saleman_by']);
            }
            if (!empty($staff) && $commission_product) {
                $this->db->update('users', ['commission_product' => ($staff->commission_product + $commission_product)], ['id' => $data['saleman_by']]);
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
    public function getProductComboItems($pid, $warehouse_id){
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, combo_items.unit_price as unit_price, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }
    public function getBillersByID($id)
    {
        return $this->db->get_where('companies', ['group_name'=>'biller','id' => $id], 1)->row();
    }
    public function getPaymentBySaleID($id)
    {
        return $this->db->get_where('payments', ['sale_id' => $id])->result();
    }
    public function getInvoiceByID($id = false)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
}
