<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_store_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addDelivery($data = [])
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return true;
        }
        return false;
    }

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = [], $ca_data = [], $sa_data = [])
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', ['award_points' => $ca_data['points']], ['id' => $ca_data['customer']]);
            } elseif (!empty($sa_data)) {
                $this->db->update('users', ['award_points' => $sa_data['points']], ['id' => $sa_data['user']]);
            }
            return true;
        }
        return false;
    }

    public function addOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }
    
    public function addDownPayment($data = [])
    {
        if ($this->db->insert('down_payments', $data)) {
            return true;
        }
        return false;
    }

    public function addPayment($data = [], $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            $sales = $this->site->getSaleById($data['sale_id']);
           
            if($sales->buy_status =='booking'){
            $sale_items = $this->site->getItemBySaleID($data['sale_id']);
            $product = $this->site->getProductByID($sale_items->product_id);
            $this->db->update('products', ['quantity'=>-1],  ['id' => $product->id]);
            $sdata =['buy_status'=>'due'];
            $this->db->update('sales', $sdata, ['id' => $data['sale_id']] );
            }
            //=========Add Accounting =========//
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['payment_id']= $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount'     => ($customer->deposit_amount - $data['amount']),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd - $data['amount_usd']),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr - $data['amount_khr']),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb - $data['amount_thb']),
                    ], 
                    ['id' => $customer_id]);
            }

            return true;
        }
        return false;
    }
    // public function addPayment($data = [], $customer_id = null, $accTranPayments = array())
    // {
    //     if ($this->db->insert('payments', $data)) {
    //         $payment_id = $this->db->insert_id();
    //         //=========Add Accounting =========//
    //         if($accTranPayments){
    //             foreach($accTranPayments as $accTranPayment){
    //                 $accTranPayment['tran_no']= $payment_id;
    //                 $this->db->insert('gl_trans', $accTranPayment);
    //             }
    //         }
    //         //=========End Accounting =========//
    //         if ($this->site->getReference('pay') == $data['reference_no']) {
    //             $this->site->updateReference('pay');
    //         }
    //         $this->site->syncSalePayments($data['sale_id']);
    //         if ($data['paid_by'] == 'gift_card') {
    //             $gc = $this->site->getGiftCardByNO($data['cc_no']);
    //             $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
    //         } elseif ($customer_id && $data['paid_by'] == 'deposit') {
    //             $customer = $this->site->getCompanyByID($customer_id);
    //             $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $data['amount'])], ['id' => $customer_id]);
    //         }
    //         return true;
    //     }
    //     return false;
    // }

    public function get_down_Paymentamounts($sale_id)
    {
        $this->db->select('down_payments.amount as total_amount', false)
            ->where('sale_id' , $sale_id);
        $q = $this->db->get('down_payments');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function addMaintenance($data = [], $customer_id = null)
    {
        if ($this->db->insert('maintenance', $data)) {
            $this->site->updateReference('main');
            return true;
        }
        return false;
    }
    public function get_down_Paymentamount($sale_id)
    {
        $this->db->select('SUM(COALESCE(amount, 0)) as total_amount', false)
            ->where('sale_id' , $sale_id);
        $q = $this->db->get('down_payments');

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function UpdateMaintenance($data = [], $id = null)
    {
        if ($this->db->update('maintenance', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteDelivery($id)
    {
        if ($this->db->delete('deliveries', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteDownpayment($id)
    {
        if ($this->db->delete('down_payments', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncSalePayments($opay->sale_id);
            //account---
         //   $this->site->deleteAccTran('Payment',$id);
            $this->site->deleteAccTranPayment('Payment',$id);
            
            //---end account
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                $sale     = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount' => ($customer->deposit_amount + $opay->amount),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd + $opay->amount_usd),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr + $opay->amount_khr),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb + $opay->amount_thb),
                    ], 
                    ['id' => $customer->id]);
            }
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', ['card_id' => $card_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItemsRoom($sale_id, $return_id = null)
    {
        $this->db->select('sale_items.*,suspended_note.bed,suspended_note.name,tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                products.slug,
                products.price, 
                products.code, 
                products.image, 
                IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
                products.details as details, 
                products.hsn_code as hsn_code, 
                products.second_name as second_name, 
                products.unit as base_unit_id, 
                products.category_id,
                products.subcategory_id,
                products.cf1 as width,
                products.second_name,
                products.weight,
                products.product_details,
                products.cf2 as length,
                products.cf3 as product_cf3,
                products.cf4 as product_cf4,
                products.cf5 as product_cf5,
                products.cf6 as product_cf6,
                options.name as option_name
            ')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('suspended_note', 'suspended_note.note_id=sale_items.product_id', 'left')
        ->join('options', 'options.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id')
        ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }


    public function getAllInvoiceItems($sale_id, $return_id = null)
    {
        $this->db->select('sale_items.*, 
                COALESCE('.$this->db->dbprefix('purchase_items').'.quantity, 0) as p_quantity,
                COALESCE('.$this->db->dbprefix('purchase_items').'.quantity_balance, 0) as p_quantity_balance,
                COALESCE('.$this->db->dbprefix('purchase_items').'.quantity_received, 0) as p_quantity_received,
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                products.slug,
                products.price, 
                products.code, 
                products.image, 
                IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
                products.details as details, 
                products.hsn_code as hsn_code, 
                products.second_name as second_name, 
                products.unit as base_unit_id, 
                products.category_id,
                products.subcategory_id,
                products.cf1 as width,
                products.second_name,
                products.weight,
                products.product_details,
                products.cf2 as length,
                products.cf3 as product_cf3,
                products.cf4 as product_cf4,
                products.cf5 as product_cf5,
                products.cf6 as product_cf6,
                options.name as option_name
            ')
        ->join('purchase_items', 'sale_items.sale_id=purchase_items.store_sale_id AND sale_items.id=purchase_items.si_id AND sale_items.to_warehouse_id = purchase_items.warehouse_id', 'left')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('options', 'options.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id')
        ->order_by('id', 'asc');
        if ($sale_id && !$return_id) {
            $this->db->where('sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExchange_rate($code)
    {   
        $this->db->where(array('code' => $code));
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPrinterByID($id)
    {
        $q = $this->db->get_where('printers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }


    public function getAllInvoiceItemsWithDetails($sale_id)
    {
        $this->db->select('sale_items.*, products.details, product_variants.name as variant');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->group_by('sale_items.id');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', ['quote_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCostingLines($sale_item_id, $product_id, $sale_id = null)
    {
        if ($sale_id) {
            $this->db->where('sale_id', $sale_id);
        }
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('id', $orderby);
        $q = $this->db->get_where('costing', ['sale_item_id' => $sale_item_id, 'product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDriverByID($id)
    {
        $this->db->select('deliveries.*,companies.id, companies.name');
        $this->db->from('deliveries');
        $this->db->join('companies', 'companies.id = deliveries.delivered_by');
        $this->db->where('deliveries.id',$id);
        $q = $this->db->get();
        //$q = $this->db->get_where('companies', array('group_name' => 'driver'));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    function getPaymentBySaleID($sale_id){
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getInvoice_detail_ByID($id)
    {
        $this->db->select('sales.id,sales.date,sales.reference_no,sales.biller,sales.customer,
                sale_items.product_code,sale_items.product_name,
                sale_items.unit_price,
                sale_items.quantity,sale_items.unit_quantity,
                sale_items.product_type,
                sale_items.product_unit_code
            ');
        $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
        $q = $this->db->get_where('sales', array('sale_items.sale_id' => $id));
        if ($q->num_rows() > 0) {
             return $q->row();
        }
        return FALSE;
    }

    public function update_loan($id)
    {
        $q = $this->db->get_where('loan_payment', array('loan_id' => $id));
        if ($q->num_rows() > 0) {
            $result = $q->row();
            $data = array(
                'paid' => $result->monthly_payment,
                'status' => 'paid'
            );
            
            // update deposit_amount reschedule
            $reschedule = $this->db->get_where('loan_reschedule', array('id' => $result->reschedule_id));
            if ($reschedule->num_rows() > 0) {
                $sche = $reschedule->row();
                $deposit_amount = $sche->deposit_amount + (float)$result->monthly_payment;
                $this->db->update('loan_reschedule', 
                    array('deposit_amount'=> $deposit_amount), 
                    array('id' => $result->reschedule_id)
                );
            }
            
            // update loan_payment
            if($this->db->update('loan_payment', $data, array('loan_id' => $id))) {
                return true;
            }
        }

        return false;
    }

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
        ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getInvoicedownPayments($sale_id)
    {
        $this->db->select('down_payments.* , users.first_name, users.last_name')
        ->join('users','users.id = down_payments.created_by','left');

        $q = $this->db->get_where('down_payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getInvoicedownPaymentstatus($sale_id)
    {
        $this->db->select('down_payments.* , users.first_name, users.last_name')
        ->join('users','users.id = down_payments.created_by','left');
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get_where('down_payments', ['sale_id' => $sale_id , 'status' => 0]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function getdownPayment($id)
    {
        $q = $this->db->get_where('down_payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    
    
    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getInvoicePaymentsGroupByTerm($sale_id)
    {
        $this->db->select("id, date,  reference_no, sale_id, loan_id, step_id, return_id, purchase_id, SUM(amount) AS amount, SUM(penalty) AS penalty, payment_term");
        $this->db->group_by('payment_term');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function getMaintenanceByID($id)
    {
        $q = $this->db->get_where('maintenance', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getMaintenanceBySaleID($sale_id)
    {
        $q = $this->db->get_where('maintenance', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getItemByID($id)
    {
        $q = $this->db->get_where('sale_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getItemRack($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            $wh = $q->row();
            return $wh->rack;
        }
        return false;
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDownPaymentByID($id)
    {
        $q = $this->db->get_where('down_payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }


    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', ['id' => 1]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSuspendComboItems($sid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, combo_items.unit_price, products.name as name,products.type as type, warehouses_products.quantity as quantity')
        ->join('products', 'products.code=combo_items.item_code', 'left')
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
        ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $sid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, combo_items.unit_price, products.name as name,products.type as type, warehouses_products.quantity as quantity')
        ->join('products', 'products.code=combo_items.item_code', 'left')
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
        ->group_by('combo_items.id');
        if ($warehouse_id) {
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

    public function getProductNames_($term, $warehouse_id, $pos = false, $limit = 5)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);

        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', false)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        }
        // $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getsuspend_note($term, $warehouse_id, $pos = false, $limit = 15)
    {
        $this->db->select('suspended_note.*,suspended_note.note_id as id,floors.name as floor_name', false)
            ->join('floors', 'floors.id=suspended_note.floor', 'left')
            ->where('suspended_note.status', 0)
            ->where("{$this->db->dbprefix('suspended_note')}.name LIKE '%" . $term . "%'");
            if($warehouse_id){
                $this->db->where('suspended_note.warehouse_id',$warehouse_id);
            }
            
            $this->db->group_by('suspended_note.note_id');

        $this->db->limit($limit);
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductNames($term, $warehouse_id, $pos = false, $limit = 15)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);

        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', false)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '" . $term . "%')");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '" . $term . "%')");
        }
        // $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPropertyNames($term, $warehouse_id, $pos = false, $limit = 5)
    {
        $this->db->select('products.*, categories.id as category_id, categories.name as category_name', false)
            // ->join('warehouses_products FWP', 'FWP.product_id=products.id', 'left')
        ->join('categories', 'categories.id=products.category_id', 'left')
        ->group_by('products.id');
        // if ($this->Settings->overselling) {
        $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        // } else {
        //     $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%')");
        // }
        $this->db->where('module_type', 'property');
        $this->db->where('quantity ', 1);

        // $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);

        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductQuantity($product_id, $warehouse)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse], 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return false;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', ['name' => $name, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariants($product_id)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getReturnBySID($sale_id)
    {
        $q = $this->db->get_where('sales', ['sale_id' => $sale_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleCosting($sale_id)
    {
        $q = $this->db->get_where('costing', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', ['id' => 1]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProduct($pid, $wid)
    {
        $this->db->select($this->db->dbprefix('products') . '.*, ' . $this->db->dbprefix('warehouses_products') . '.quantity as quantity')
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['warehouses_products.product_id' => $pid, 'warehouses_products.id' => $wid]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function resetSaleActionsRoom($id, $return_id = null, $check_return = null)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if (($sale->sale_status == 'returned') || ($sale->return_id != null)) {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            } else if ($sale->sale_status == 'completed' || $sale->sale_status == 'consignment') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        if ($pi = $this->getPurchaseItemByID($costing->purchase_item_id)) {
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                            // $this->db->update('purchase_items', ['quantity_balance' => ($costing->quantity_balance + $costing->quantity)], ['id' => $pi->id]);
                        } else {
                            // $sale_item = $this->getSaleItemByID($costing->sale_item_id);
                            $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : null, 'purchase_id' => null, 'transfer_id' => null, 'warehouse_id' => $sale->warehouse_id]);
                                $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        }
                    }
                    $this->db->delete('costing', ['id' => $costing->id]);
                }

                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(null, null, $items);
     
                $customer = $this->site->getCompanyByID($sale->customer_id);
                if($customer->save_point){
                    if (!empty($this->Settings->each_spent)) {
                        $points       = floor(($sale->grand_total / $this->Settings->each_spent) * $this->Settings->ca_point);
                        $total_points = $sale->grand_total >= 0?$customer->award_points - $points:$customer->award_points + (-1*$points);
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $sale->customer_id]);
                    }
                }
            
                
                $staff = $this->site->getUser($sale->saleman_by);
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $points       = floor(($sale->grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                        $total_points = $sale->grand_total >= 0?$staff->award_points - $points:$staff->award_points + (-1*$points);
                        $this->db->update('users', ['award_points' => $total_points], ['id' => $sale->saleman_by]);
                    }
                }
                return $items;
            }
        }
    }


    public function resetSaleActions($id, $return_id = null, $check_return = null)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if (($sale->sale_status == 'returned') || ($sale->return_id != null)) {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
            } else if ($sale->sale_status == 'completed' || $sale->sale_status == 'consignment') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        if ($pi = $this->getPurchaseItemByID($costing->purchase_item_id)) {
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                            // $this->db->update('purchase_items', ['quantity_balance' => ($costing->quantity_balance + $costing->quantity)], ['id' => $pi->id]);
                        } else {
                            // $sale_item = $this->getSaleItemByID($costing->sale_item_id);
                            $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : null, 'purchase_id' => null, 'transfer_id' => null, 'warehouse_id' => $sale->warehouse_id]);
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        }
                    }
                    $this->db->delete('costing', ['id' => $costing->id]);
                }
                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(null, null, $items);

                $customer = $this->site->getCompanyByID($sale->customer_id);
                if($customer->save_point){
                    if (!empty($this->Settings->each_spent)) {
                        $points       = floor(($sale->grand_total / $this->Settings->each_spent) * $this->Settings->ca_point);
                        $total_points = $sale->grand_total >= 0?$customer->award_points - $points:$customer->award_points + (-1*$points);
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $sale->customer_id]);
                    }
                }
                $staff = $this->site->getUser($sale->saleman_by);
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $points       = floor(($sale->grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                        $total_points = $sale->grand_total >= 0?$staff->award_points - $points:$staff->award_points + (-1*$points);
                        $this->db->update('users', ['award_points' => $total_points], ['id' => $sale->saleman_by]);
                    }
                }
                return $items;
            }
        }
    }

    public function syncQuantity($sale_id)
    {
        if ($sale_items = $this->getAllInvoiceItems($sale_id)) {
            foreach ($sale_items as $item) {
                $this->site->syncProductQty($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->site->syncVariantQty($item->option_id, $item->warehouse_id);
                }
            }
        }
    }

    public function topupGiftCard($data = [], $card_data = null)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, ['id' => $data['card_id']]);
            return true;
        }
        return false;
    }

    public function UpdateCostingAndPurchaseItem($return_item, $product_id, $quantity)
    {
        $bln_quantity = $quantity;
     
        if ($costings = $this->getCostingLines($return_item['id'], $product_id)) {
            foreach ($costings as $costing) {
                if ($costing->quantity > $bln_quantity && $bln_quantity != 0) {
                    $qty = $costing->quantity - $bln_quantity;
                    $bln = $costing->quantity_balance && $costing->quantity_balance >= $bln_quantity ? $costing->quantity_balance - $bln_quantity : 0;
                    $this->db->update('costing', ['quantity' => $qty, 'quantity_balance' => $bln], ['id' => $costing->id]);
                    $bln_quantity = 0;
                    break;
                } elseif ($costing->quantity <= $bln_quantity && $bln_quantity != 0) {
                    $this->db->delete('costing', ['id' => $costing->id]);
                    $bln_quantity = ($bln_quantity - $costing->quantity);
                }
            }
        }
        $clause = ['product_id' => $product_id, 'warehouse_id' => $return_item['warehouse_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $return_item['option_id']];
        $this->site->setPurchaseItem($clause, $quantity);
        $this->site->syncQuantity(null, null, null, $product_id);
    }

    public function updateDelivery($id, $data = [])
    {
        if ($this->db->update('deliveries', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function updateOptionQuantity($option_id, $quantity)
    {
        if ($option = $this->getProductOptionByID($option_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('product_variants', ['quantity' => $nq], ['id' => $option_id])) {
                return true;
            }
        }
        return false;
    }

    public function updateDpayment($id, $data = [])
    {
        if ($this->db->update('down_payments', $data, ['id' => $id],1)) {
            return true;
        }
        return false;
    }
    public function updatePayment($id, $data = [], $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);

        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncSalePayments($data['sale_id']);
            // $this->site->deleteAccTran('Payment',$id);
            $this->site->deleteAccTranPayment('Payment',$id);
            
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale        = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update(
                    'companies',
                    [
                        'deposit_amount' => ($customer->deposit_amount + $opay->amount),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd + $opay->amount_usd),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr + $opay->amount_khr),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb + $opay->amount_thb)
                    ], 
                    ['id' => $customer->id]);
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', ['balance' => ($gc->balance - $data['amount'])], ['card_no' => $data['cc_no']]);
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount' => ($customer->deposit_amount - $data['amount']),
                        'deposit_amount_usd' => ($customer->deposit_amount_usd - $data['amount_usd']),
                        'deposit_amount_khr' => ($customer->deposit_amount_khr - $data['amount_khr']),
                        'deposit_amount_thb' => ($customer->deposit_amount_thb - $data['amount_thb'])
                    ], 
                    ['id' => $customer_id]);
            }
            return true;
        }
        return false;
    }

    public function updateProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq])) {
                $this->site->syncVariantQty($option_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        $this->db->trans_start();
        $sale  = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost  = [];
        if ($status == 'completed' && $sale->sale_status != 'completed') {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }
        if ($status != 'completed' && $sale->sale_status == 'completed') {
            $this->resetSaleActions($id);
        }
        if ($this->db->update('sales', ['sale_status' => $status, 'note' => $note], ['id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            if ($status == 'completed' && $sale->sale_status != 'completed') {
                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_id']      = $id;
                            $item_cost['date']         = date('Y-m-d', strtotime($sale->date));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }
            }
            if (!empty($cost)) {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncQuantity($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdataStatus:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }
    public function getFiberTypeById($product_id, $purchase_id=null){
        $this->db->select('id,addition_type as name,quantity_balance as qty')
        ->where('product_id', $product_id)
        ->where('addition_type !=', null);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCombinePaymentById($id)
    {
        $this->db->select('id, date, reference_no, biller, customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status');
        $this->db->from('sales');
        $this->db->where_in('id', $id);
        $this->db->where('paid < grand_total');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getSaleById($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getCombinePaymentBySaleId($id)
    {
        $this->db->select('sales.id, sales.date,sales.total, sales.reference_no, sales.biller, sales.customer, sales.sale_status, sales.grand_total, sales.paid, (bpas_sales.grand_total-bpas_sales.paid-COALESCE(bpas_return_sales.grand_total,0)) as balance, sales.payment_status');
        $this->db->from('sales');
        $this->db->join('return_sales', 'return_sales.id = sales.return_id', 'left');
        $this->db->where_in('sales.id', $id);
        $this->db->where('sales.paid < sales.grand_total');
        //$this->db->where('sale_status !=', 'returned');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function getSampleSaleRefByProductID($product_id){
        $q = $this->db->select('MAX(reference_no) AS reference_no')
        ->join('sale_items', 'sale_items.sale_id = sales.id', 'left')
        ->where('sale_items.product_id', $product_id)
        ->get('sales');
        if($q->num_rows() > 0){
            return $q->row()->reference_no;
        }
    }
    public function addPaymentMulti($data = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            if ($this->site->getReference('pp',$data['biller_id']) == $data['reference_no']) {
                $this->site->updateReference('pp',$data['biller_id']);
            }
            $this->site->syncPurchasePayments($data['purchase_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardbyNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            }
            if($data['paid_by'] == 'deposit'){
                $deposit = $this->site->getDepositByCompanyID($deposit_customer_id);
                $deposit_balance = $deposit->deposit_amount;
                $deposit_balance = $deposit_balance - abs($data['amount']);
                if($this->db->update('companies', array('deposit_amount' => $deposit_balance), array('id' => $deposit_customer_id))){
                    //$this->db->update('deposits', array('amount' => $deposit_balance), array('company_id' => $deposit_customer_id));
                }
            }
            return $payment_id;
        }
        return false;
    }
    
    public function addSalePaymentMulti($data = array())
    {
        //$this->bpas->print_arrays($data);

        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            if ($this->site->getReference('pp',$data['biller_id']) == $data['reference_no']) {
                $this->site->updateReference('pp',$data['biller_id']);
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardbyNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            }
            if($data['paid_by'] == 'deposit'){
                $deposit = $this->site->getDepositByCompanyID($deposit_customer_id);
                $deposit_balance = $deposit->deposit_amount;
                $deposit_balance = $deposit_balance - abs($data['amount']);
                if($this->db->update('companies', array('deposit_amount' => $deposit_balance), array('id' => $deposit_customer_id))){
                    //$this->db->update('deposits', array('amount' => $deposit_balance), array('company_id' => $deposit_customer_id));
                }
            }



            return $payment_id;
        }
        return false;
    }

    public function addCombinePayment($data = array())
    {
        //$this->bpas->print_arrays($data);
        // exit();

        if ($this->db->insert('payments', $data)) {
            $payment_id =   $this->db->insert_id();
            if ($this->site->getReference('pp',$data['biller_id']) == $data['reference_no']) {
                $this->site->updateReference('pp',$data['biller_id']);
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardbyNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            }
            if($data['paid_by'] == 'deposit'){
                $deposit = $this->site->getDepositByCompanyID($deposit_customer_id);
                $deposit_balance = $deposit->deposit_amount;
                $deposit_balance = $deposit_balance - abs($data['amount']);
                if($this->db->update('companies', array('deposit_amount' => $deposit_balance), array('id' => $deposit_customer_id))){
                    //$this->db->update('deposits', array('amount' => $deposit_balance), array('company_id' => $deposit_customer_id));
                }
            }

            // if ($this->db->where('id',$data['sale_id'])->update('sales', array('payment_status' => 'paid') )) {

            // }


            return $payment_id;
        }
        return false;
    }
    
    
    
    
    public function addSalePaymentLoan($data = array())
    {
        $id = $data['id'];

        if ($this->db->update('sales', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
    public function getTotalSalesDue($customer_id,$currency_code)
    {
        $this->db->select('
            SUM(COALESCE(grand_total, 0)) as total_amount, 
            SUM(COALESCE(paid, 0)) as paid', FALSE)->where(array('customer_id' => $customer_id));

        if($currency_code){
            $this->db->where('currency', $currency_code);
        }      
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getSalesTotals_usd($customer_id,$currency_code)
    {

        $this->db->select('
            SUM(COALESCE(grand_total, 0)) as total_amount, 
            SUM(COALESCE(paid, 0)) as paid', FALSE)
        ->where(array('customer_id' => $customer_id,'currency'=> $currency_code));
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSalesTotals_khm($customer_id,$currency_code)
    {

        $this->db->select("
            SUM(COALESCE(grand_total, 0) * currency_rate_kh) as total_amount, 
            SUM(COALESCE(paid, 0) * currency_rate_kh) as paid", FALSE)
        ->where(array('customer_id' => $customer_id,'currency'=> $currency_code));
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getSalesTotals_bat($customer_id,$currency_code)
    {

        $this->db->select('
            SUM(COALESCE(grand_total, 0) * currency_rate_bat) as total_amount, 
            SUM(COALESCE(paid, 0) * currency_rate_bat) as paid', FALSE)
        ->where(array('customer_id' => $customer_id,'currency'=> $currency_code));
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getPaymentForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.loan_id, payments.amount, payments.cc_no, 
            payments.cheque_no, payments.reference_no,payments.currency,
            payments.currency_rate,payments.paid_amount,payments.note,users.first_name,
             users.last_name, type,payments.sale_id')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id,'loan_id' => null),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getsale_booking($sale_id)
    {
        $this->db->select('payments.*');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id,'type' =>'booking'),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getPaymentForAgreement($sale_id)
    {
        $this->db->select('payments.*,users.first_name, users.last_name, type,payments.sale_id')
        ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addAttachment($data = array(), $id)
    {
        if ($this->db->update('sales', $data, array('id' => $id))) {
            return true;
        }
    }
    public function getlistLoanByID($sale_id)
    {
        $this->db->order_by('loan_id', 'asc');
        $q = $this->db->get_where('loan_payment', array('loan_id' => $sale_id));
        if ($q->num_rows() > 0) {
            return  $q->row();
        }
    }
    public function getLoanBySaleID_x_Term($sale_id, $term)
    {
        $q = $this->db->get_where('loan_payment', ['sale_id' => $sale_id, 'payment_term' => $term], 1);
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getLoanBySaleID_GroupBYTerm($sale_id)
    {
        $this->db
            ->where('sale_id', $sale_id)
            ->group_by('sale_id, payment_term');
        $q = $this->db->get('loan_payment');
        if($q->num_rows() > 0){
            foreach (($q->result()) as $key => $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function cancel_update_loan($id)
    {
        $q = $this->db->get_where('loan_payment', array('loan_id' => $id));
        if ($q->num_rows() > 0) {
            $result = $q->row();
            $data = array(
                'paid' => 0,
                'status' => 'pending'
            );

            // update deposit_amount reschedule
            $reschedule = $this->db->get_where('loan_reschedule', array('id' => $result->reschedule_id));
            if ($reschedule->num_rows() > 0) {
                $sche = $reschedule->row();
                $deposit_amount = $sche->deposit_amount - (float)$result->monthly_payment;
                $this->db->update('loan_reschedule', 
                    array('deposit_amount'=> $deposit_amount), 
                    array('id' => $result->reschedule_id)
                );
            }
            // update loan_payment
            if($this->db->update('loan_payment', $data, array('loan_id' => $id))) {
                return true;
            }
        }

        return false;
    }
     public function getsale_detail_ByID($id)
    {
        $this->db->select('
                sales.id,sales.date,
                sales.reference_no,sales.biller,
                sales.customer,note,
                biller_id,project_id,
                created_by,customer_id,sale_status,
                order_discount,order_tax,shipping,
                
                sale_items.product_id,
                sale_items.product_code,
                sale_items.product_name,
                sale_items.unit_price,
                sale_items.quantity,
                sale_items.unit_quantity,
                sale_items.product_type,
                sale_items.product_unit_code,
                sale_items.item_tax

            ');
        $this->db->join('sale_items', 'sales.id=sale_items.sale_id', 'left');
        $q = $this->db->get_where('sales', array('sale_items.sale_id' => $id));
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    public function syncAcc_Sale($id, $data, $items = [], $accTrans = array())
    {
        $this->db->trans_start();
        $this->resetSaleActions($id, false, true);

        if ($this->db->update('sales', $data, ['id' => $id]) ) {
            $this->site->deleteAccTran('Sale',$id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }
    public function getPaymentermID($id)
    {
        $q = $this->db->get_where('payment_term', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSaleOrderById($sale_order_id){
        $this->db->select('sales_order.*');
        $q = $this->db->get_where('sales_order', array('sales_order.id' => $sale_order_id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getLastDrawImage($id, $customer_id)
    {   
        $this->db->order_by('id', 'DESC');
        $q = $this->db->get_where('sales', [
            'customer_id' => $customer_id,
            'image !=' =>''
        ], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSalesTotals($customer_id)
    {
        $this->db->select('SUM(COALESCE(grand_total, 0)) as total_amount, SUM(COALESCE(paid, 0)) as paid', false)
            ->where('customer_id', $customer_id);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSalesBalance_Items_($id)
    {
        $this->db
            ->select("{$this->db->dbprefix('sale_items')}.product_id, {$this->db->dbprefix('sale_items')}.product_code, {$this->db->dbprefix('sale_items')}.product_name, COALESCE(SUM({$this->db->dbprefix('sale_items')}.quantity), 0) AS quantity, {$this->db->dbprefix('sale_items')}.expiry")
            ->from('sales')
            ->join('sale_items', 'sales.id=sale_items.sale_id', 'left')
            ->group_start()
                ->where('sales.id', $id)
                ->or_where('sales.sale_id', $id)
            ->group_end()
            ->group_by('sale_items.product_id')
            ->group_by('sale_items.expiry')
            ->group_by('sale_items.id')
            ->order_by('sale_items.product_code', 'ASC');
        $q = $this->db->get();
        if($q->num_rows() > 0){
            foreach (($q->result()) as $key => $row) {
                if($row->quantity > 0) $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleBalance_Items_($id) 
    {
        $this->db
            ->select("{$this->db->dbprefix('costing')}.product_id, {$this->db->dbprefix('costing')}.sale_item_id, COALESCE(SUM({$this->db->dbprefix('costing')}.quantity), 0) AS quantity")
            ->where('costing.sale_id', $id)
            ->group_by('costing.sale_item_id');

        $q = $this->db->get('costing');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $key => $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSaleBalance_Items($id)
    {
        $sub_q = " ( 
                    SELECT COALESCE(SUM(si.quantity), 0) 
                    FROM {$this->db->dbprefix("sale_items")} AS si 
                    LEFT JOIN {$this->db->dbprefix("sales")} AS s ON si.sale_id = s.id 
                    WHERE s.sale_id = {$id} AND si.sale_item_id = {$this->db->dbprefix("sale_items")}.id 
                    GROUP BY si.sale_item_id LIMIT 1 
                ) ";

        $this->db->select("sale_items.*,
                (bpas_sale_items.quantity + COALESCE({$sub_q}, 0)) AS quantity,
                bpas_sale_items.quantity AS real_saleItem_quantity,
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                products.slug,
                products.price, 
                products.code, 
                products.image, 
                products.details as details, 
                products.hsn_code as hsn_code, 
                products.second_name as second_name, 
                products.unit as base_unit_id, 
                products.category_id,
                products.subcategory_id,
                products.cf1 as width,
                products.second_name,
                products.weight,
                products.product_details,
                products.cf2 as length,
                products.cf3 as product_cf3,
                products.cf4 as product_cf4,
                products.cf5 as product_cf5,
                products.cf6 as product_cf6
            ")
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('sales', 'sales.id=sale_items.sale_id', 'left')
        ->where('sales.id', $id)
        ->group_by('sale_items.id')
        ->order_by('sale_items.product_code', 'ASC');

        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if($row->quantity > 0) $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function checkReturned($id)
    {
        $q = $this->db->query("
                SELECT COALESCE(SUM({$this->db->dbprefix('sale_items')}.quantity), 0) AS quantity 
                FROM {$this->db->dbprefix('sales')}
                LEFT JOIN {$this->db->dbprefix('sale_items')} ON {$this->db->dbprefix('sales')}.id = {$this->db->dbprefix('sale_items')}.sale_id
                WHERE {$this->db->dbprefix('sales')}.id = " . $id. " OR {$this->db->dbprefix('sales')}.sale_id = " . $id . "
                GROUP BY {$this->db->dbprefix('sales')}.id OR {$this->db->dbprefix('sales')}.sale_id
                LIMIT 1
            ");

        if($q->num_rows() > 0) {
            if($q->row()->quantity > 0) return false;
            else return true;
        }
        return false;
    }

    public function getWHProductById($id, $warehouse_id)
    {
        $this->db->select('products.*, warehouses_products.quantity, categories.id as category_id, categories.name as category_name')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', ['products.id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }
    public function getRoomNames($term, $limit = 100)
    {
        $this->db->select('suspended_note.name');
        $this->db->where("({$this->db->dbprefix('suspended_note')}.name LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('suspended_note');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    
    public function addRent($data = [], $items = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null)
    {
        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();

            //=========Add Accounting =========//
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ($data['paid'] == 0 && (isset($payment['amount']) && $payment['amount'] == '')) {
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id'] = $sale_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
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
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();

                $this->db->update('suspended_note', ['status' => 1], ['note_id' => $item['product_id']]);
                $this->db->insert('reservation', [
                    'note_id'   =>  $item['product_id'],
                    'sale_id'   =>  $sale_id,
                    'checkIn'  =>  $item['check_in'],
                    'checkIn_by'=>  $this->session->userdata('user_id'),
                    'duration'  =>  $item['quantity']
                ]);
            }

            // if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
            //     $this->site->syncPurchaseItems($cost);
            // }

            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);

                    
                    //$this->UpdateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity']);
                    
                }

                $q=$this->db->get_where('sales', ['id' => $data['sale_id']],1);
                if ($q->num_rows() > 0) {
                    $return_sale_total_ = ($q->row()->return_sale_total ? $q->row()->return_sale_total : 0);
                }
                
                $this->db->update('sales', ['return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => ($data['grand_total'] + $return_sale_total_), 'return_id' => $sale_id], ['id' => $data['sale_id']]);

                $customer = $this->site->getCompanyByID($data['customer_id']);
                if($customer->save_point){
                    if (!empty($this->Settings->each_spent)) {
                        $points       = floor(($data['grand_total'] / $this->Settings->each_spent) * $this->Settings->ca_point);
                        $total_points = $customer->award_points + $points;
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $data['customer_id']]);
                    }
                }
                $staff = $this->site->getUser($data['saleman_by']);
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $points       = floor(($data['grand_total'] / $this->Settings->each_sale) * $this->Settings->sa_point);
                        $total_points = $staff->award_points + $points;
                        $this->db->update('users', ['award_points' => $total_points], ['id' => $data['saleman_by']]);
                    }
                }
            }
            $staff = $this->site->getUser($data['saleman_by']);
               
            if ($data['payment_status'] == 'paid') {
                $this->site->update_property_status($sale_id,'sold');
            }
            if ($data['payment_status'] == 'booking' || $data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
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

                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id']= $payment_id;
                        if (empty($accTranPayment['reference_no'])) {
                            $accTranPayment['reference_no'] = $payment['reference_no'];
                        }
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

            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($data['saleman_by']);
            if(isset($staff->save_point) && $staff->save_point){
                $this->bpas->update_award_points($data['grand_total'], null, $data['saleman_by']);
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
    public function getSaleRefenceByCustomer($customer_id = false)
    {
        $this->db->where("sale_status !=","returned");
        if($customer_id){
            $this->db->where("customer_id",$customer_id);
        }
        $q = $this->db->get("sales");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getInvoiceCommissions($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('commissions', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function addCommission($data = [])
    {
        if ($this->db->insert('commissions', $data,['id'=> $data['sale_id']])) {
            return true;
        }
        return false;
    }
    public function getCommissionByID($id)
    {
        $q = $this->db->get_where('commissions', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function deleteCommission($id)
    {
        if ($this->db->delete('commissions', ['id' => $id])) {
            return true;
        }
        return false;
    }
     public function updatePaymentCommission($id, $data = [], $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->update('payments', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updatePaymentCommissionStatus($id, $data = [])
    {
        if ($this->db->update('commissions', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function updateCommission($id, $data = [])
    {
        if ($this->db->update('commissions', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }



    /////////////////////
    public function getWarehouseByBiller($biller_id)
    {
        $this->db->select(" {$this->db->dbprefix('warehouses')}.* ");
        $this->db->from('companies');
        $this->db->join('warehouses', 'warehouses.id=companies.warehouse_id', 'inner');
        $this->db->where('companies.id', $biller_id);
        $this->db->limit(1);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function addSale($data = [], $items = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null, $store_items = [])
    {  
        if (empty($si_return)) {
            $cost = $this->site->costing($items);
            // $this->bpas->print_arrays($cost);
        }
        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            //=========Add Accounting =========//
            if($accTrans) {
                foreach($accTrans as $accTran) {
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ($data['paid'] == 0 && (isset($payment['amount']) && $payment['amount'] == '')) {
                if($accTranPayments) {
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no']    = $sale_id;
                        $accTranPayment['payment_id'] = $sale_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('sp') == $data['reference_no']) {
                $this->site->updateReference('sp');
            }
            foreach ($items as $key => $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                $store_items[$key]['si_id'] = $sale_item_id;

                if ($this->Settings->product_option && isset($item['max_serial'])) {
                    $this->db->update('product_options', ['start_no' => $item['serial_no'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                }
                $aprroved['purchase_request_id'] = $sale_id;
                $this->db->insert('approved', $aprroved);
                if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment' && empty($si_return)) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $sale_id;
                            $item_cost['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $sale_id;
                                $ic['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }
            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                $this->site->syncPurchaseItems($cost);
            }
            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo' && $product->module_type != "property") {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->UpdateCostingAndPurchaseItem($return_item, $combo_item->id, ($return_item['quantity'] * $combo_item->qty));
                        }
                    } elseif($product->module_type == "property"){
                        $property = $this->site->getProductByID($product->id);
                        $data_pro = array(
                            'code'              => $property->code,
                            'name'              => $property->name,
                            'unit'              => $property->unit,
                            'barcode'           => $property->barcode,
                            'cost'              => $property->cost,
                            'price'             => $property->price,
                            'currency'          => $property->currency,
                            'other_cost'        => $property->other_cost,
                            'other_price'       => $property->other_price,
                            'alert_quantity'    => $property->alert_quantity,
                            'image'             => $property->image,
                            'category_id'       => $property->category_id,
                            'subcategory_id'    => $property->subcategory_id,
                            'max_serial'        => $property->max_serial,
                            'product_details'   => $property->product_details,
                            'cf1'               => $property->cf1,
                            'cf2'               => $property->cf2,
                            'cf3'               => $property->cf3,
                            'cf4'               => $property->cf4,
                            'cf5'               => $property->cf5,
                            'cf6'               => $property->cf6,
                            'tax_rate'          => $property->price,
                            'currency'          => $property->currency,
                            'type'              => $property->type,
                            'module_type'       => $property->module_type,
                            'image'             => $property->image,
                            'track_quantity'    => $property->track_quantity,
                            'serial_no'         => $property->serial_no,
                            'details'           => $property->details,

                            'warehouse'         => $property->warehouse,
                            'barcode_symbology' => $property->barcode_symbology,
                            'file'              => $property->file,
                            'product_details'   => $property->product_details,
                            'tax_method'        => $property->tax_method,
                            'supplier1'         => $property->supplier1,
                            'supplier1price'    => $property->supplier1price,
                            'supplier2'         => $property->supplier2,
                            'supplier2price'    => $property->supplier2price,
                            'supplier3'         => $property->supplier3,
                            'supplier3price'    => $property->supplier3price,
                            'supplier4'         => $property->supplier4,
                            'supplier4price'    => $property->supplier4price,
                            'supplier5'         => $property->supplier5,
                            'supplier5price'    => $property->supplier5price,
                            'promotion'         => $property->promotion,

                            'promo_price'       => $property->promo_price,
                            'end_date'          => $property->end_date,
                            'supplier1_part_no' => $property->supplier1_part_no,
                            'supplier2_part_no' => $property->supplier2_part_no,
                            'supplier3_part_no' => $property->supplier3_part_no,
                            'supplier4_part_no' => $property->supplier4_part_no,
                            'supplier5_part_no' => $property->supplier5_part_no,
                            'sale_unit'         => $property->sale_unit,
                            'purchase_unit'     => $property->purchase_unit,
                            'brand'             => $property->brand,
                            'slug'              => $property->slug,
                            'featured'          => $property->featured,
                            'weight'            => $property->weight,
                            'hsn_code'          => $property->hsn_code,
                            'views'             => $property->views,
                            'quantity'          => 1,
                            'overselling'       => $property->overselling,
                            'hide'              => $property->hide,
                            'second_name'       => $property->second_name,
                            'hide_pos'          => $property->hide_pos,
                            'asset'             => $property->asset,
                            'stock_type'        => $property->stock_type,
                            'gender'            => $property->gender,
                            'project_id'        => $property->project_id,
                            // 'project_id'     => $property->project_id,
                            'booking'           => $property->booking,
                            'blocking'          => $property->blocking,
                        );   
                        $this->UpdateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity']);
                        $this->db->delete('products', ['id' => $return_item['product_id']]);
                        $this->db->insert('products', $data_pro);
                    } else {
                        $this->UpdateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity']);
                    }
                }

                $q=$this->db->get_where('sales', ['id' => $data['sale_id']], 1);
                if ($q->num_rows() > 0) {
                    $return_sale_total_ = ($q->row()->return_sale_total ? $q->row()->return_sale_total : 0);
                }
                $this->db->update('sales', ['return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => ($data['grand_total'] + $return_sale_total_), 'return_id' => $sale_id], ['id' => $data['sale_id']]);
                $customer = $this->site->getCompanyByID($data['customer_id']);
                if($customer->save_point){
                    if (!empty($this->Settings->each_spent)) {
                        $points       = floor(((-1 * $data['grand_total']) / $this->Settings->each_spent) * $this->Settings->ca_point);
                        $total_points = $customer->award_points - $points;
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $data['customer_id']]);
                    }
                }
                if(isset($data['saleman_by'])){
                    $staff = $this->site->getUser($data['saleman_by']);
                }
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $points       = floor(((-1 * $data['grand_total']) / $this->Settings->each_sale) * $this->Settings->sa_point);
                        $total_points = $staff->award_points - $points;
                        $this->db->update('users', ['award_points' => $total_points], ['id' => $data['saleman_by']]);
                    }
                }
            }
            if(isset($data['saleman_by'])){
                $staff = $this->site->getUser($data['saleman_by']);
            }
            if($commission_product){
                $commission        = $commission_product;
                $total_commissions = $staff->commission_product + $commission;
                $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $data['saleman_by']]);
            }
            if ($data['payment_status'] == 'booking' || $data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if ($data['payment_status'] == 'paid') {
                    $this->site->update_property_status($sale_id, 'sold');
                }
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update(
                            'companies', 
                            [
                                'deposit_amount'     => ($customer->deposit_amount - $payment['amount']),
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
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id']= $payment_id;
                        if (empty($accTranPayment['reference_no'])) {
                            $accTranPayment['reference_no'] = $payment['reference_no'];
                        }
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

            $this->site->syncQuantity($sale_id, null, null, null, $payment_status);
            $this->addStock_to_Store($sale_id, $store_items, $data['sale_status']);

            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($data['saleman_by']);
            if(isset($staff->save_point) && $staff->save_point){
                $this->bpas->update_award_points($data['grand_total'], null, $data['saleman_by']);
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

    public function updateSale($id, $data, $items = [], $accTrans = array(), $accTranPayments = array(), $commission_product = null, $store_items = [])
    {  
        $this->db->trans_start();
        $this->resetSaleActions($id, false, true);
        if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
            $this->Settings->overselling = true;
            $cost                        = $this->site->costing($items, true);
        }
        $saleman_commission = $this->site->getCommissionsBySaleID($id);
        $saleman = $this->site->getUser($data['saleman_by']);
        if($data['reference_no'] != $this->getSaleById($id)->reference_no){
            $this->site->updateReference('sp');
        }
        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $total_commissions = $saleman->commission_product - $saleman_commission->commission;
            $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $data['saleman_by']]);
            $this->site->deleteAccTran('Sale',$id);
            $this->site->deleteSalePayment('Payment', $id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            $commission_product = 0;
            foreach ($items as $item) {
                $this->db->update('product_options', ['start_no' => $item['max_serial'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                $commission_product += $item['commission'];
                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if (($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') && $this->site->getProductByID($item['product_id'])) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $id;
                            $item_cost['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $id;
                                $item_cost['date']  = date('Y-m-d H:i:s', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }
            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncSalePayments($id);
            $this->site->syncQuantity($id);
            $this->updateStock_to_Store($id, $store_items, $data['sale_status']);

            $sale     = $this->getInvoiceByID($id);
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff    = $this->site->getUser($sale->saleman_by);
            if($staff->save_point){
                $this->bpas->update_award_points($data['grand_total'], null, $sale->saleman_by);
            }
            $ucommission = $staff->commission_product + $commission_product;
            $this->db->update('users', ['commission_product' => $ucommission], ['id' => $sale->saleman_by]);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function deleteSale($id)
    {
        $this->db->trans_start();
        $sale_items = $this->resetSaleActions($id);
        $saleman_commission = $this->site->getCommissionsBySaleID($id);
        $sale = $this->site->getSaleById($id);
        $saleman = $this->site->getUser($sale->saleman_by);
       
        if ($this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sales', ['id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $total_commissions = $saleman->commission_product - $saleman_commission->commission;
            $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $sale->saleman_by]);
            $this->db->delete('sales', ['sale_id' => $id]);
            $this->db->delete('payments', ['sale_id' => $id]);
            //---add account
            $this->site->deleteAccTran('Sale', $id);
            $this->site->deleteAccTran('Payment', $id);
            $this->site->deleteAccTran('SaleReturn', $id);
            //---end account---
            $this->site->syncQuantity(null, null, $sale_items);
            $this->deleteStock_from_Store($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function removeSale($id, $data = [])
    {
        $this->db->trans_start();
        $sale_items = $this->resetSaleActions($id);
        $data['hide'] = 0;
        $saleman_commission = $this->site->getCommissionsBySaleID($id);
        $sale = $this->site->getSaleById($id);
        $saleman = $this->site->getUser($sale->saleman_by);
        if ($this->db->update('sale_items', $data, ['sale_id' => $id]) && $this->db->update('sales', $data, ['id' => $id]) && $this->db->update('costing', $data, ['sale_id' => $id])) {
            $this->db->update('sales', $data, ['sale_id' => $id]);
            $this->db->update('payments', $data, ['sale_id' => $id]);
            //---add account
            $this->site->removeAccTran('Sale', $id);
            $this->site->removeAccTran('SaleReturn', $id);
            //---end account---
            $this->site->syncQuantity(null, null, $sale_items);
            $this->deleteStock_from_Store($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Remove:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function addStock_to_Store($id, $items, $status)
    {
        if ($status == 'completed' || $status == 'returned') {
            foreach ($items as $item) {
                $item['store_sale_id'] = $id;
                $this->db->insert('purchase_items', $item);
                if ($item['option_id']) {
                    $this->db->update('product_variants', ['cost' => $item['base_unit_cost']], ['id' => $item['option_id'], 'product_id' => $item['product_id']]);
                }
                $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['base_unit_cost'] ?? $item['real_unit_cost']]);                
            }
            $this->site->syncQuantity(null, null, null, null, null, $id);
        }
    }

    public function updateStock_to_Store($id, $items, $status)
    {
        $oitems = $this->site->getAllStoreItems($id);
        $this->db->delete('purchase_items', ['store_sale_id' => $id]);
        if ($status == 'completed') {
            foreach ($items as $item) {
                $item['store_sale_id'] = $id;
                $this->db->insert('purchase_items', $item);
                if ($item['option_id']) {
                    $this->db->update('product_variants', ['cost' => $item['base_unit_cost']], ['id' => $item['option_id'], 'product_id' => $item['product_id']]);
                }
                $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['base_unit_cost'] ?? $item['real_unit_cost']]);                
            }
        }
        $this->site->syncQuantity(null, null, $oitems);
        if ($status == 'completed') {
            $this->site->syncQuantity(null, null, null, null, null, $id);
            foreach ($oitems as $oitem) {
                $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->base_unit_cost ?? $oitem->real_unit_cost]);
            }
        }
    }

    public function deleteStock_from_Store($id)
    {
        $store_items = $this->site->getAllStoreItems($id);
        if ($this->db->delete('purchase_items', ['store_sale_id' => $id])) {
            foreach ($store_items as $oitem) {
                $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost]);
                $received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
                if ($oitem->quantity_balance < $received) {
                    $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id];
                    $this->site->setPurchaseItem($clause, ($oitem->quantity_balance - $received));
                }
            }
            $this->site->syncQuantity(null, null, $store_items);
        }
    }

    public function updateAVCO($data)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
            $total_cost     = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', ['avg_cost' => $avg_cost], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']]);
            }
        } else {
            $this->db->insert('warehouses_products', ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0]);
        }
    }
}