<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sales_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    public function addSale($data = [], $items = [], $stockmoves = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null, $module = null)
    {  
        $this->db->trans_start();
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
            if ($module =='module_rental') {
                if ($this->site->getReference('ren') == $data['reference_no']) {
                    $this->site->updateReference('ren');
                }
            } else {
                if ($this->site->getReference('so') == $data['reference_no']) {
                    $this->site->updateReference('so');
                } elseif ($this->site->getReference('st') == $data['reference_no']) {
                    $this->site->updateReference('st');
                }
            }
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
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
                $aprroved['purchase_request_id'] = $sale_id;
                $this->db->insert('approved', $aprroved);
            }
            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                if ($data['delivery_id']) {
                    $this->db->update('deliveries', ['sale_id' => $sale_id, 'sale_reference_no' => $data['reference_no'], 'status' => 'completed'], ['id' => $data['delivery_id']]);
                    $this->db->update('sales', ['delivery_status' => 'completed'], ['id' => $sale_id]);
                }
            }
            if ($data['payment_status'] == 'paid') {
                $this->site->update_property_status($sale_id, 'sold');
            }
            if ($data['payment_status'] == 'booking' || $data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
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
            if($this->Settings->module_fuel && $data['fuel_customers']){
                $fuel_customers = json_decode($data['fuel_customers']);
                $this->db->where_in("id",$fuel_customers)->update("fuel_customers",array("status"=>"completed"));
                foreach($fuel_customers as $fuel_customer){
                    $this->site->deleteStockmoves('FuelCustomer',$fuel_customer);
                    $this->site->deleteAccTran('FuelCustomer',$fuel_customer);
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
    public function updateSale($id, $data, $items = [], $stockmoves = [], $accTrans = array(), $accTranPayments = array(), $commission_product = null, $module = null)
    {  
    
        $this->db->trans_start();
        $this->resetSaleActionsBySaleID($id);
        $oitems = $this->site->getStockMovementByTransactionID($id);
        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sale_combo_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $this->site->deleteStockmoves('Sale', $id);
            $this->site->deleteAccTran('Sale', $id);
            $this->site->deleteSalePayment('Payment', $id);
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ($accTranPayments) {
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            $commission_product = 0;
            foreach ($items as $item) {
                $this->db->update('product_options', ['start_no' => $item['max_serial'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                $commission_product += $item['commission'];
                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id(); 
                $combo_items  = $this->getProductComboItems($item['product_id'], $data['warehouse_id']); 
                if (!empty($combo_items)) {
                    foreach ($combo_items as $combo_item) {
                        $item_combo = array(
                            'sale_product_id'=> $item['product_id'],
                            'sale_id'        => $id,
                            'sale_item_id'   => $sale_item_id,
                            'product_id'     => $combo_item->id,
                            'product_code'   => $combo_item->code,
                            'product_name'   => $combo_item->name,
                            'product_type'   => $combo_item->type,
                            'warehouse_id'   => $data['warehouse_id'],
                            'quantity'       => $combo_item->qty * $item['quantity'],
                            'net_unit_price' => $combo_item->price,
                            'unit_price'     => $combo_item->price,
                            'currency'       => 'usd',
                            'tax_rate'       => null,
                            'option_id'      => null,
                            'subtotal'       => $combo_item->price * $item['quantity']
                        );
                        $this->db->insert('sale_combo_items', $item_combo);
                    }
                }
            } 
            if ($stockmoves) {
                foreach($stockmoves as $stockmove) {
                    if ($stockmove['product_type'] != 'combo') {
                        $this->db->insert('stock_movement', $stockmove);
                        if ($this->site->stockMovement_isOverselling($stockmove)) {
                            return false;
                        }
                    }
                }
            }
            foreach ($oitems as $oitem) {
                if ($this->site->stockMovement_isOverselling($oitem)) {
                    return false;
                }
            }
            $this->site->syncSalePayments($id); 
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if ($customer->save_point) {
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($data['saleman_by']);
            if ($staff->save_point) {
                $this->bpas->update_award_points($data['grand_total'], null, $data['saleman_by']);
            }
            $this->db->update('users', ['commission_product' => ($staff->commission_product + $commission_product)], ['id' => $data['saleman_by']]);

            $this->site->sendTelegram("sale",$id,"updated");
        }  
        $this->db->trans_complete(); 
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            $request_sale = $this->getEditSaleRequestBySaleID($id);
            if ($request_sale == false) {  
                return true;
            } else {
                $this->db->update('sales_edit_request', ['active' => 0,'created_by' => $this->session->userdata('user_id')], ['id' => $request_sale->id]);
                return true;
            }        
        }
        return false;
    }
    
    public function deleteSale($id)
    {
        $this->db->trans_start();
        $this->resetSaleActionsBySaleID($id);
        $inv   = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        if ($this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sales', ['id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $this->db->delete('sales', ['sale_id' => $id]);
            $this->db->delete('payments', ['sale_id' => $id]);
            //---add account
            $this->site->deleteAccTran('Sale', $id);
            $this->site->deleteAccTran('Payment', $id);
            $this->site->deleteAccTran('SaleReturn', $id);
            //---end account---
            $this->site->deleteStockmoves('Sale', $id);
            $this->site->deleteStockmoves('SaleReturn', $id);
            if ($this->Settings->accounting_method == '0') {
                foreach ($items as $item) {
                    $this->site->updateFifoCost($item->product_id);
                }
            } else if ($this->Settings->accounting_method == '1') {
                foreach ($items as $item) {
                    $this->site->updateLifoCost($item->product_id);
                }
            }

            if($this->Settings->module_installment==1){
                $installment = $this->getInstallmentBySaleID($inv->sale_id);
                if($installment && $installment->sale_id > 0){
                    $this->db->where("sale_id", $installment->sale_id)->update("installments", array("status"=>"active"));
                }
            }

            if($this->Settings->module_fuel && $inv->fuel_customers){
                $stockmoves = false;
                $accTrans = false;
                $fuel_customers = json_decode($inv->fuel_customers);
                if($fuel_customers){    
                    $this->db->where_in("id",$fuel_customers)->update("fuel_customers",array("status"=>"pending"));
                    $fuel_customers = $this->getFuelCustomerByIDs($fuel_customers);
                    if($fuel_customers){
                        foreach($fuel_customers as $fuel_customer){
                            $fuel_customer_items = $this->getFuelCustomerItems($fuel_customer->id);
                            if($fuel_customer_items){
                                foreach($fuel_customer_items as $fuel_customer_item){
                                    $product_details = $this->site->getProductByID($fuel_customer_item->product_id);
                                    $unit = $this->site->getProductUnit($product_details->id, $product_details->unit);
                                    $stockmoves[] = array(
                                                        'transaction' => 'FuelCustomer',
                                                        'transaction_id' => $fuel_customer->id,
                                                        'reference_no' => $fuel_customer->reference,
                                                        'product_id' => $product_details->id,
                                                        'product_code' => $product_details->code,
                                                        'product_type' => $product_details->type,
                                                        'quantity' => $fuel_customer_item->quantity * (-1),
                                                        'unit_quantity' => $unit->unit_qty,
                                                        'unit_code' => $unit->code,
                                                        'unit_id' => $product_details->unit,
                                                        'warehouse_id' => $fuel_customer->warehouse_id,
                                                        'date' => $fuel_customer->date,
                                                        'real_unit_cost' => $product_details->cost,
                                                        'user_id' => $fuel_customer->created_by,
                                                    );
                                    //========accounting=========//
                                        $productAcc = $this->site->getProductAccByProductId($product_details->id);
                                        if($this->Settings->module_account == 1){       
                                            $accTrans[] = array(
                                                'tran_type'     => 'FuelCustomer',
                                                'tran_no'       => $fuel_customer->id,
                                                'tran_date'     => $fuel_customer->date,
                                                'reference_no'  => $fuel_customer->reference,
                                                'account_code'  => $productAcc->stock_acc,
                                                'amount'        => -($product_details->cost * $fuel_customer_item->quantity),
                                                'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$fuel_customer_item->quantity.'#'.'Cost: '.$product_details->cost,
                                                'description'   => $fuel_customer->note,
                                                'biller_id'     => $fuel_customer->biller_id,
                                                'user_id'       => $fuel_customer->created_by,
                                                'customer_id'   => $fuel_customer->customer_id,
                                            );
                                            $accTrans[] = array(
                                                'tran_type'     => 'FuelCustomer',
                                                'tran_no'       => $fuel_customer->id,
                                                'tran_date'     => $fuel_customer->date,
                                                'reference_no'  => $fuel_customer->reference,
                                                'account_code'  => $productAcc->cost_acc,
                                                'amount'        => ($product_details->cost * $fuel_customer_item->quantity),
                                                'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$fuel_customer_item->quantity.'#'.'Cost: '.$product_details->cost,
                                                'description'   => $fuel_customer->note,
                                                'biller_id'     => $fuel_customer->biller_id,
                                                'user_id'       => $fuel_customer->created_by,
                                                'customer_id'   => $fuel_customer->customer_id,
                                            );
                                        }
                                    //============end accounting=======//
                                }
                            }
                        }
                    }
                    
                }
                if($stockmoves){
                    $this->db->insert_batch("stock_movement",$stockmoves);
                }
                if($accTrans){
                    $this->db->insert_batch("gl_trans",$accTrans);
                }
            }
            if($inv->sale_id > 0){
                $this->site->syncSalePayments($inv->sale_id);
            }

            $this->site->sendTelegram("sale",$id,"deleted",$inv);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function removeSale($id, $data= [])
    {  
        $this->db->trans_start();
        $this->resetSaleActionsBySaleID($id);
        $data['hide'] = 0;
        if ($this->db->update('sale_items', $data, ['sale_id' => $id]) && $this->db->update('sales', $data, ['id' => $id]) && $this->db->update('costing', $data, ['sale_id' => $id])) {
            $this->db->update('sales', $data, ['sale_id' => $id]);
            $this->db->update('payments', $data, ['sale_id' => $id]);
            //---add account
            $this->site->removeAccTran('Sale', $id);
            $this->site->removeAccTran('SaleReturn', $id);
            //---end account---
            $this->site->deleteStockmoves('Sale', $id);


        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Remove:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function addEditSaleRequest($data = [])
    {
        if ($this->db->insert('sales_edit_request', $data)) {
            if ($this->site->getReference('esq') == $data['reference_no']) {
                $this->site->updateReference('esq');
            }
            return true;
        }
        return false;
    }

    public function updateEditSaleRequest($id, $data = [])
    {
        if ($this->db->update('sales_edit_request', $data, ['id'=> $id])) {
            return true;
        }
        return false;
    }
    /* ----------------- Gift Cards --------------------- */

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
            //------down payment----------
            if($data['down_payment_id']){
                $this->db->update('down_payments', ['status'=> 1], ['id' => $data['down_payment_id']] );
            }
            //--------// down payment---------
            //=========Add Accounting =========//
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['payment_id']= $payment_id;
                    $accTranPayment['tran_no']= $payment_id;
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
        $this->db->trans_start();
        if ($this->db->insert('maintenance', $data)) {
            $main_id = $this->db->insert_id();
			$term 	 = $data['term'];
            $frequency = $this->site->getcustomfieldById($data['frequency']);
			$start_date = $data['maintenance_date'];
			$stdate = $start_date;
			for($month=1;$month <= $term;$month++){ 
				if($month == 1){
					$run_term = 0;
				}else{
					$run_term = $frequency->description;
				}
				$date_term = date('Y-m-d', strtotime('+' . $run_term . ' month', strtotime($stdate)));
				$stdate = $date_term;
                $this->db->insert('maintenance_items', ['main_id'=>$main_id, 'date'=>$stdate, 'note'=>'', 'status'=>'pending']);
            }
            $this->site->updateReference('main');
            // return true;
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (addMaintenance:Sales_model.php)');
        } else {
            return $main_id;
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
   
    public function UpdateMaintenance($data = [], $id = null){
        if ($this->db->update('maintenance', $data, ['id' => $id])) {
            /*
            $payment_id = $this->db->insert_id();
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }*/
            return true;
        }
        return false;
    }
    public function delete_maintenance($id)
    {
        if ($this->db->delete('maintenance', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function removeRejected($id, $data = [])
    {
        if ($this->db->update('sales_edit_request',$data, ['id' => $id])) {
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
            $this->site->deleteAccTran('Payment',$id);
            //---end account
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                // $this->db->update('gift_cards', ['balance' => ($gc->balance + $opay->amount)], ['card_no' => $opay->cc_no]);
                $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
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

    public function getAllInvoiceTicket($sale_id, $return_id = null)
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

    public function getAllInvoiceItemsRoom($sale_id, $return_id = null)
    {
        $this->db->select('sale_items.*,
                suspended_note.bed,
                suspended_note.name,suspended_note.booking,
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                units.name as unit_name,
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
            $this->db->where('sale_items.sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_items.sale_id', $return_id);
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
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                units.name as unit_name,
                products.slug,
                products.price, 
                sale_units.name as name_unit,
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
                options.name as option_name,
                sale_units.name as product_unit_name
            ')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('units sale_units', 'sale_units.id=sale_items.product_unit_id', 'left')
        ->join('options', 'options.id=sale_items.option_comment_id', 'left')
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
        $this->db->select('sale_items.*, products.details, product_variants.name as variant, units.name as unit_name');
        $this->db->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('units', 'units.id=sale_items.product_unit_id', 'left')
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

    public function getCostingLines($sale_item_id, $product_id, $sale_id = null,$expiry = null)
    {
        if ($sale_id) {
            $this->db->where('sale_id', $sale_id);
        }
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('id', $orderby);
         if($expiry != null){
             $q = $this->db->get_where('costing', ['sale_item_id' => $sale_item_id, 'product_id' => $product_id, 'expiry' => $expiry]);
         }else{
             $q = $this->db->get_where('costing', ['sale_item_id' => $sale_item_id, 'product_id' => $product_id]);
         }
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
    public function getTaxDeclareBySaleID($id)
    {
        $q = $this->db->get_where('tax_items', ['transaction_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getInvoicesByID($id)
    {
        $si = "( SELECT sale_id, product_id,GROUP_CONCAT(
            CONCAT( {$this->db->dbprefix('sale_items')}.product_code) ) as item_name 
            from {$this->db->dbprefix('sale_items')} ";
        $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";

        $this->db->select('sales.*,FSI.item_name as iqty, ');
        $this->db->join($si, 'FSI.sale_id=sales.id', 'left');
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
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

    public function getInvoicedownPayments($sale_id,$product_id)
    {
        $this->db->select('down_payments.* , users.first_name, users.last_name')
        ->join('users','users.id = down_payments.created_by','left');
        if($product_id){
            $q = $this->db->get_where('down_payments', ['product_id' => $product_id]);
        }else{
            $q = $this->db->get_where('down_payments', ['sale_id' => $sale_id]);
        }
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
        $this->db->where('transaction is NULL');
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
    public function getMemberCardCode($code)
    {
        $q = $this->db->get_where('member_cards', ['card_no' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
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
        $this->db->select('warehouses_products.*, product_rack.name as rack');
        $this->db->join('product_rack', 'warehouses_products.rack_id=product_rack.id', 'left');
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

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
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
        $this->db->select('
                products.id as id, combo_items.item_code as code, products.name as name, products.type as type,
                combo_items.quantity as qty, 
                combo_items.quantity as width, 
                combo_items.unit_price as price,  
                combo_items.unit_price as unit_price,
                warehouses_products.quantity as quantity,
                1 as height
            ')
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
            /*if($warehouse_id){
                $this->db->where('suspended_note.warehouse_id',$warehouse_id);
            }*/
            
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

    public function getProductNames_12_07_2022($term, $warehouse_id, $pos = false, $limit = 15)
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

    public function getProductNames($term, $warehouse_id, $pos = false, $limit = 15)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        if ($this->Settings->multiple_code_unit != 0) {
            $sub_q = " ( 
                        SELECT 
                            mcp.product_id, 
                            mcp.product_code,  
                            mcp.unit_id,  
                            mcp.cost,  
                            mcp.price  
                        FROM {$this->db->dbprefix('cost_price_by_units')} mcp
                        WHERE 
                            mcp.product_code IS NOT NULL AND
                            mcp.product_code != '' AND
                            mcp.product_code != 0 AND
                            mcp.product_code LIKE '%" . $term . "%'
                        GROUP BY mcp.product_id
                    ) bpas_CP ";
            $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
            $this->db->select("
                    products.*, 
                    COALESCE({$this->db->dbprefix('CP')}.product_code, {$this->db->dbprefix('products')}.code) AS code, 
                    COALESCE({$this->db->dbprefix('CP')}.unit_id, {$this->db->dbprefix('products')}.sale_unit) AS sale_unit, 
                    FWP.quantity as quantity, categories.id as category_id, categories.name as category_name"
                , false)
                ->join($wp, 'FWP.product_id = products.id', 'left')
                ->join('categories', 'categories.id=products.category_id', 'left')
                ->join($sub_q, 'CP.product_id = products.id', 'left')
                ->group_by('products.id');
            if ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) {
                $this->db->where("
                    (
                        {$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR
                        {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR
                        {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR
                        concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%' OR
                        {$this->db->dbprefix('CP')}.product_code LIKE '%" . $term . "%'
                    )
                ");
            } else {
                $this->db->where("
                    (
                        {$this->db->dbprefix('products')}.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                        . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR 
                        {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR 
                        {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR 
                        concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%' OR
                        {$this->db->dbprefix('CP')}.product_code LIKE '%" . $term . "%'
                    )
                ");
            }
        } else {
            $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
            $this->db->select("
                    products.*, 
                    FWP.quantity as quantity, categories.id as category_id, categories.name as category_name"
                , false)
                ->join($wp, 'FWP.product_id = products.id', 'left')
                ->join('categories', 'categories.id=products.category_id', 'left')
                ->group_by('products.id');
            if ($this->Settings->overselling && (!empty($warehouse) && $warehouse->overselling)) {
                $this->db->where("
                    (
                        {$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR
                        {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR
                        {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR
                        concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'
                    )
                ");
            } else {
                $this->db->where("
                    (
                        {$this->db->dbprefix('products')}.track_quantity = 0 OR FWP.quantity > 0) AND FWP.warehouse_id = '" . $warehouse_id . "' AND "
                        . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR 
                        {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR 
                        {$this->db->dbprefix('products')}.item_code LIKE '%" . $term . "%' OR 
                        concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'
                    )
                ");
            }
        }
        $this->db->order_by('products.name ASC');
        if ($pos) {
            $this->db->where('hide_pos !=', 1);
        }
        $this->db->where("{$this->db->dbprefix('products')}.type !=", 'asset');
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
    public function getProductOptionComment($id)
    {
        $q = $this->db->get_where('product_options', ['product_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
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
        $this->db->select(" {$this->db->dbprefix('products')}.*, {$this->db->dbprefix('warehouses_products')}.quantity as quantity ")
        ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
        $q = $this->db->get_where('products', ['warehouses_products.product_id' => $pid, 'warehouses_products.warehouse_id' => $wid]);
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
                            // $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
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
                        $total_points = $sale->grand_total >= 0 ? $customer->award_points - $points : $customer->award_points + (-1 * $points);
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $sale->customer_id]);
                    }
                }
                $staff = $this->site->getUser($sale->saleman_by);
                if ($staff->save_point) {
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
                        // $pi = null;
                        // if ($costing->purchase_id) {
                        //     $purchase_items = $this->getPurchaseItems($costing->purchase_id);
                        //     foreach ($purchase_items as $row) {
                        //         if ($row->product_id == $costing->product_id && $row->option_id == $costing->option_id) {
                        //             $pi = $row;
                        //         }
                        //     }
                        // } elseif ($costing->transfer_id) {
                        //     $purchase_items = $this->getTransferItems($costing->transfer_id);
                        //     foreach ($purchase_items as $row) {
                        //         if ($row->product_id == $costing->product_id && $row->option_id == $costing->option_id) {
                        //             $pi = $row;
                        //         }
                        //     }
                        // }
                        // if ($pi) {
                        //     $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        // } else {
                        //     $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : null, 'purchase_id' => null, 'transfer_id' => null, 'warehouse_id' => $sale->warehouse_id]);
                        //     $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        // }
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

    public function UpdateCostingAndPurchaseItem($return_item, $product_id, $quantity, $expiry = null)
    {
        $bln_quantity = $quantity;
     
        if ($costings = $this->getCostingLines($return_item['id'], $product_id, null,$expiry)) {
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
        $clause = ['product_id' => $product_id,'expiry'=>$expiry, 'warehouse_id' => $return_item['warehouse_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $return_item['option_id']];
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
            $this->site->deleteAccTran('Payment',$id);
            //$this->site->deleteAccTranPayment('Payment',$id);
            
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

    public function updateRent($id, $data, $items = [], $accTrans = array(),$accTranPayments = array(),$commission_product = null)
    {  
        $this->db->trans_start();
        $this->resetSaleActionsRoom($id, false, true);
    
        if ($data['sale_status'] == 'completed') {
            $this->Settings->overselling = true;
            $cost                        = $this->site->costing($items, true);
        }
        $saleman_commission = $this->site->getCommissionsBySaleID($id);
        $saleman = $this->site->getUser($data['saleman_by']);

        if($data['reference_no'] != $this->getSaleById($id)->reference_no){
            if($data['order_tax_id'] == 1){
                $this->site->updateReference('so');
            } else {
                $this->site->updateReference('st');
            }
        }

        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $total_commissions = $saleman->commission_product - $saleman_commission->commission;
            $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $data['saleman_by']]);
            $this->site->deleteAccTran('Sale',$id);

            //if($data['paid'] == 0){
                $this->site->deleteSalePayment('Payment',$id);
            //}
            
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
            $sale = $this->getInvoiceByID($id);
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($sale->saleman_by);
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

    public function updateTicket($id, $data, $items = [], $accTrans = array(), $accTranPayments = array(),$commission_product = null)
    {  
        $this->db->trans_start();
        $this->resetSaleActionsRoom($id, false, true);
        if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
            $this->Settings->overselling = true;
            $cost                        = $this->site->costing($items, true);
        }
        $saleman_commission = $this->site->getCommissionsBySaleID($id);
        $saleman = $this->site->getUser($data['saleman_by']);
        if($data['reference_no'] != $this->getSaleById($id)->reference_no){
            if($data['order_tax_id'] == 1){
                $this->site->updateReference('so');
            } else {
                $this->site->updateReference('st');
            }
        }
        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $total_commissions = $saleman->commission_product - $saleman_commission->commission;
            $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $data['saleman_by']]);
            $this->site->deleteAccTran('Sale',$id);
            //if($data['paid'] == 0){
                $this->site->deleteSalePayment('Payment',$id);
            //}
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            if ($accTranPayments) {
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
            $sale = $this->getInvoiceByID($id);
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($sale->saleman_by);
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
    public function resetSaleActionsBySaleID($id) 
    {
        if (!empty($id) && $sale = $this->getInvoiceByID($id)) {
            $customer = $this->site->getCompanyByID($sale->customer_id);
            if ($customer->save_point) {
                if (!empty($this->Settings->each_spent)) {
                    $points       = floor(($sale->grand_total / $this->Settings->each_spent) * $this->Settings->ca_point);
                    $total_points = $sale->grand_total >= 0?$customer->award_points - $points : $customer->award_points + (-1 * $points);
                    $this->db->update('companies', ['award_points' => $total_points], ['id' => $sale->customer_id]);
                }
            }
            $staff = $this->site->getUser($sale->saleman_by);
            if ($staff->save_point) {
                if (!empty($this->Settings->each_sale)) {
                    $points       = floor(($sale->grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                    $total_points = $sale->grand_total >= 0 ? $staff->award_points - $points : $staff->award_points + (-1 * $points);
                    $this->db->update('users', ['award_points' => $total_points], ['id' => $sale->saleman_by]);
                }
            }
            $saleman_commission = $this->site->getCommissionsBySaleID($id);
            $saleman = $this->site->getUser($sale->saleman_by);
            if (!empty($saleman) && $saleman_commission) {
                $total_commissions = $saleman->commission_product - $saleman_commission->commission;
                $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $sale->saleman_by]);
            }
        }
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

    public function addCombinePayment($datas = array(),$accTranPayments = array())
    {

        if($datas){
            foreach($datas as $data){
                $sale = $this->getInvoiceByID($data['sale_id']);
                $customer_id = $sale->customer_id;

                $this->db->insert('payments', $data);
                $payment_id =   $this->db->insert_id();
                $this->site->syncSalePayments($data['sale_id']);

                $accTrans = $accTranPayments[$data['sale_id']];
                if($accTrans){
                    foreach($accTrans as $accTran){
                        $accTran['tran_no'] = $payment_id;
                        $this->db->insert('gl_trans',$accTran);
                    }
                }
                if ($this->site->getReference('pp',$data['biller_id']) == $data['reference_no']) {
                    $this->site->updateReference('pp',$data['biller_id']);
                }
                if ($data['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardbyNO($data['cc_no']);
                    $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
                }
                if($data['paid_by'] == 'deposit'){
                    $deposit = $this->site->getDepositByCompanyID($customer_id);
                    $deposit_balance = $deposit->deposit_amount;
                    $deposit_balance = $deposit_balance - abs($data['amount']);
                    if ($this->db->update('companies', array('deposit_amount' => $deposit_balance), array('id' => $customer_id))) { 
                        //$this->db->update('deposits', array('amount' => $deposit_balance), array('company_id' => $deposit_customer_id));
                    }
                }
               
            }
            return true;
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
            $reschedule = $this->db->get_where('loan_reschedule', array('id' => $result->reschedule_id));
            if ($reschedule->num_rows() > 0) {
                $sche = $reschedule->row();
                $deposit_amount = $sche->deposit_amount - (float)$result->monthly_payment;
                $this->db->update('loan_reschedule', 
                    array('deposit_amount'=> $deposit_amount), 
                    array('id' => $result->reschedule_id)
                );
            }
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
        if ($this->db->update('sales', $data, ['id' => $id]) ) {
            $this->site->deleteAccTran('Sale',$id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['tran_no'] = $sale_id;
                    $accTranPayment['payment_id'] = $sale_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
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
                if ($data['sale_status'] == 'completed') {
                    $this->db->update('suspended_note', ['sale_id'=>$sale_id,'status' => 1],['note_id' => $item['product_id']]);
                }else{
                    $this->db->update('suspended_note', ['sale_id'=>$sale_id,'booking'=>'booking','status' => 1], ['note_id' => $item['product_id']]);
                }
                
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
                    // $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
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

    public function addTicket($data = [], $items = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null)
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

                // $this->db->update('suspended_note', ['status' => 1], ['note_id' => $item['product_id']]);
                $this->db->insert('reservation', [
                    'note_id'       =>  $item['product_id'],
                    'from'          =>  $item['from_id'],
                    'destination'   =>  $item['destination_id'],
                    'timeout'       =>  $item['timeout_id'],
                    'sale_id'       =>  $sale_id,
                    'checkIn'       =>  $item['date_booking_ticket'],
                    'checkIn_by'    =>  $this->session->userdata('user_id'),
                    'duration'      =>  $item['quantity']
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
                    // $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
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
    public function getEditSaleRequestBySaleID($id)
    {
        $q = $this->db->get_where('sales_edit_request', ['active'=>1, 'sale_id' => $id, 'created_by' => $this->session->userdata('user_id')], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getEditSaleRequestByID($id)
    {
        $q = $this->db->get_where('sales_edit_request', ['active'=>1, 'id' => $id], 1);
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
    public function addMemberCard($data = [])
    {
        if ($this->db->insert('member_cards', $data)) {
            return true;
        }
        return false;
    }
    public function updateMemberCard($id, $data = [])
    {
        $this->db->where('id', $id);
        if ($this->db->update('member_cards', $data)) {
            return true;
        }
        return false;
    }
       public function addMultiMemberCard($data = [])
    { 
        if ($this->db->insert_batch('member_cards', $data)) {
            return true;
        }
        return false;
    }
     public function deleteMemberCard($id)
    {
        if ($this->db->delete('member_cards', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getAllSOItemsWithDeliveries($sale_order_id = false)
    {
        $this->db->select('sale_order_items.*,
                            SUM('.$this->db->dbprefix('sale_order_items').'.quantity) as quantity,
                            IFNULL(SUM('.$this->db->dbprefix('sale_order_items').'.foc),0) as foc,
                            ('.$this->db->dbprefix('sale_order_items').'.discount) as discount,
                            SUM('.$this->db->dbprefix('sale_order_items').'.subtotal) as subtotal,
                            SUM('.$this->db->dbprefix('sale_order_items').'.unit_quantity) as unit_quantity,
                            credit_note.delivered_quantity
                            ')
            ->join('(SELECT
                        '.$this->db->dbprefix('credit_note').'.sale_order_id AS sale_order_id,
                        '.$this->db->dbprefix('delivery_items').'.product_id,
                        SUM('.$this->db->dbprefix('delivery_items').'.quantity) AS delivered_quantity,
                        '.$this->db->dbprefix('delivery_items').'.unit_price AS unit_price
                    FROM
                        '.$this->db->dbprefix('credit_note').'
                    INNER JOIN '.$this->db->dbprefix('delivery_items').' ON '.$this->db->dbprefix('delivery_items').'.delivery_id = '.$this->db->dbprefix('credit_note').'.id
                    WHERE
                        '.$this->db->dbprefix('credit_note').'.id <> ""
                    GROUP BY
                        '.$this->db->dbprefix('credit_note').'.sale_order_id,
                        '.$this->db->dbprefix('delivery_items').'.product_id,
                        '.$this->db->dbprefix('delivery_items').'.unit_price) 
                    as credit_note','credit_note.sale_order_id = sale_order_items.sale_order_id
                    AND credit_note.product_id = sale_order_items.product_id
                    AND credit_note.unit_price = sale_order_items.unit_price', 'left')
            ->group_by('sale_order_items.product_id,sale_order_items.unit_price,sale_order_items.discount')
            ->order_by('id', 'asc');
            
        if ($sale_order_id) {
            $this->db->where('sale_order_items.sale_order_id', $sale_order_id);
        } 
        $q = $this->db->get('sale_order_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllInvoiceItemsWithDeliveries($sale_id = false,  $return_id = null)
    {
        $this->db->select('sale_items.*,
                            sale_items.id as sale_item_id,
                            SUM('.$this->db->dbprefix('sale_items').'.quantity) as quantity,
                            IFNULL(SUM('.$this->db->dbprefix('sale_items').'.foc), 0) as foc,
                            ('.$this->db->dbprefix('sale_items').'.discount) as discount,
                            SUM('.$this->db->dbprefix('sale_items').'.subtotal) as subtotal,
                            SUM('.$this->db->dbprefix('sale_items').'.unit_quantity) as unit_quantity,
                            deliveries.delivered_quantity
                        ')
            ->join('
                    (
                        SELECT
                            '.$this->db->dbprefix('deliveries').'.sale_id AS sale_id,
                            '.$this->db->dbprefix('delivery_items').'.sale_item_id,
                            '.$this->db->dbprefix('delivery_items').'.product_id,
                            '.$this->db->dbprefix('delivery_items').'.product_unit_id,
                            '.$this->db->dbprefix('delivery_items').'.expiry,
                            SUM('.$this->db->dbprefix('delivery_items').'.quantity) AS delivered_quantity,
                            '.$this->db->dbprefix('delivery_items').'.unit_price AS unit_price
                        FROM
                            '.$this->db->dbprefix('deliveries').'
                        INNER JOIN '.$this->db->dbprefix('delivery_items').' ON '.$this->db->dbprefix('delivery_items').'.delivery_id = '.$this->db->dbprefix('deliveries').'.id
                        WHERE
                            '.$this->db->dbprefix('deliveries').'.id <> ""
                        GROUP BY
                            '.$this->db->dbprefix('deliveries').'.sale_id,
                            '.$this->db->dbprefix('delivery_items').'.sale_item_id
                    ) as deliveries',
                    '
                        deliveries.sale_id = sale_items.sale_id AND 
                        deliveries.sale_item_id = sale_items.id
                    ', 'left')
            ->group_by('sale_items.sale_id, sale_items.id')
            ->order_by('id', 'asc');
            
        if ($sale_id) {
            $this->db->where('sale_items.sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_items.sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllInvoiceItemsWithCreditItems($sale_id = false,  $return_id = null)
    {
        $this->db->select('sale_items.*,
                            sale_items.id as sale_item_id,
                            SUM('.$this->db->dbprefix('sale_items').'.quantity) as quantity,
                            IFNULL(SUM('.$this->db->dbprefix('sale_items').'.foc), 0) as foc,
                            ('.$this->db->dbprefix('sale_items').'.discount) as discount,
                            SUM('.$this->db->dbprefix('sale_items').'.subtotal) as subtotal,
                            SUM('.$this->db->dbprefix('sale_items').'.unit_quantity) as unit_quantity,
                            credit_note.credit_items_quantity
                        ')
            ->join('
                    (
                        SELECT
                            '.$this->db->dbprefix('credit_note').'.sale_id AS sale_id,
                            '.$this->db->dbprefix('credit_note_items').'.sale_item_id,
                            '.$this->db->dbprefix('credit_note_items').'.product_id,
                            '.$this->db->dbprefix('credit_note_items').'.product_unit_id,
                            '.$this->db->dbprefix('credit_note_items').'.expiry,
                            SUM('.$this->db->dbprefix('credit_note_items').'.quantity) AS credit_items_quantity,
                            '.$this->db->dbprefix('credit_note_items').'.unit_price AS unit_price
                        FROM
                            '.$this->db->dbprefix('credit_note').'
                        INNER JOIN '.$this->db->dbprefix('credit_note_items').' ON '.$this->db->dbprefix('credit_note_items').'.credit_note_id = '.$this->db->dbprefix('credit_note').'.id
                        WHERE
                            '.$this->db->dbprefix('credit_note').'.id <> ""
                        GROUP BY
                            '.$this->db->dbprefix('credit_note').'.sale_id,
                            '.$this->db->dbprefix('credit_note_items').'.sale_item_id
                    ) as credit_note',
                    '
                        credit_note.sale_id = sale_items.sale_id AND 
                        credit_note.sale_item_id = sale_items.id
                    ', 'left')
            ->group_by('sale_items.sale_id, sale_items.id')
            ->order_by('id', 'asc');
            
        if ($sale_id) {
            $this->db->where('sale_items.sale_id', $sale_id);
        } elseif ($return_id) {
            $this->db->where('sale_items.sale_id', $return_id);
        }
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function addCreditNote($data = [], $items = [], $stockmoves = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null, $module = null)
    {  
        $this->db->trans_start();
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
            if ($this->site->getReference('re') == $data['reference_no']) {
                $this->site->updateReference('re');
            }
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
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

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
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
    public function EditCreditNote($id, $data, $items = [], $stockmoves = [], $accTrans = array(), $accTranPayments = array(), $commission_product = null)
    {  
        $this->db->trans_start();
        $this->resetSaleActionsBySaleID($id);
        $oitems = $this->site->getStockMovementByTransactionID($id);
        if ($this->db->update('sales', $data, ['id' => $id]) && $this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sale_combo_items', ['sale_id' => $id]) && $this->db->delete('costing', ['sale_id' => $id])) {
            $this->site->deleteStockmoves('Sale', $id);
            $this->site->deleteAccTran('Sale', $id);
            $this->site->deleteSalePayment('Payment', $id);
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ($accTranPayments) {
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            $commission_product = 0;
            foreach ($items as $item) {
                $this->db->update('product_options', ['start_no' => $item['max_serial'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                $commission_product += $item['commission'];
                $item['sale_id'] = $id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id(); 
                $combo_items  = $this->getProductComboItems($item['product_id'], $data['warehouse_id']); 
                if (!empty($combo_items)) {
                    foreach ($combo_items as $combo_item) {
                        $item_combo = array(
                            'sale_product_id'=> $item['product_id'],
                            'sale_id'        => $id,
                            'sale_item_id'   => $sale_item_id,
                            'product_id'     => $combo_item->id,
                            'product_code'   => $combo_item->code,
                            'product_name'   => $combo_item->name,
                            'product_type'   => $combo_item->type,
                            'warehouse_id'   => $data['warehouse_id'],
                            'quantity'       => $combo_item->qty * $item['quantity'],
                            'net_unit_price' => $combo_item->price,
                            'unit_price'     => $combo_item->price,
                            'currency'       => 'usd',
                            'tax_rate'       => null,
                            'option_id'      => null,
                            'subtotal'       => $combo_item->price * $item['quantity']
                        );
                        $this->db->insert('sale_combo_items', $item_combo);
                    }
                }
            } 
            if ($stockmoves) {
                foreach($stockmoves as $stockmove) {
                    if ($stockmove['product_type'] != 'combo') {
                        $this->db->insert('stock_movement', $stockmove);
                        if ($this->site->stockMovement_isOverselling($stockmove)) {
                            return false;
                        }
                    }
                }
            }
            foreach ($oitems as $oitem) {
                if ($this->site->stockMovement_isOverselling($oitem)) {
                    return false;
                }
            }
            $this->site->syncSalePayments($id); 
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if ($customer->save_point) {
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            $staff = $this->site->getUser($data['saleman_by']);
            if ($staff->save_point) {
                $this->bpas->update_award_points($data['grand_total'], null, $data['saleman_by']);
            }
            $this->db->update('users', ['commission_product' => ($staff->commission_product + $commission_product)], ['id' => $data['saleman_by']]);

            $this->site->sendTelegram("sale",$id,"updated");
        }  
        $this->db->trans_complete(); 
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;      
        }
        return false;
    }
    public function getCreditNoteByID($id)
    {
        $q = $this->db->get_where('credit_note', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllCreditNoteItems($id = false)
    {
        $this->db->select('
                            credit_note_items.*, 
                            tax_rates.code as tax_code, 
                            tax_rates.name as tax_name, 
                            tax_rates.rate as tax_rate, 
                            products.unit, 
                            products.image, 
                            products.details as details, 
                            product_variants.name as variant, 
                            credit_note.sale_id, 
                            units.name as unit_name')
            ->join('products', 'products.id=credit_note_items.product_id', 'left')
            ->join('credit_note', 'credit_note.id=credit_note_items.credit_note_id', 'left')
            ->join('product_variants', 'product_variants.id=credit_note_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=credit_note_items.tax_rate_id', 'left')
            ->join('units','units.id = credit_note_items.product_unit_id','left')
            ->where('credit_note_items.unit_quantity <>',0)
            ->group_by('credit_note_items.id')
            ->order_by('id', 'asc');
            
        $q = $this->db->get_where('credit_note_items', array('credit_note_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function deleteCreditNote($id)
    {
        if ($this->db->delete('credit_note', ['id' => $id])) {
            $this->db->delete('credit_note_items', array('credit_note_id' => $id));
            return true;
        }
        return false;
    }

    public function checkCreditNote($id) 
    {
        $q = $this->db->query("
                SELECT (COALESCE(sale_items.quantity, 0) - COALESCE(credit_note.quantity, 0)) AS quantity 
                FROM {$this->db->dbprefix('sales')}
                LEFT JOIN (
                    SELECT {$this->db->dbprefix('sale_items')}.sale_id, COALESCE(SUM({$this->db->dbprefix('sale_items')}.quantity), 0) AS quantity 
                    FROM {$this->db->dbprefix('sale_items')} 
                    GROUP BY {$this->db->dbprefix('sale_items')}.sale_id
                ) sale_items ON sale_items.sale_id = {$this->db->dbprefix('sales')}.id
                LEFT JOIN (
                    SELECT {$this->db->dbprefix('credit_note')}.sale_id, COALESCE(SUM(credit_note_items.quantity), 0) AS quantity FROM {$this->db->dbprefix('credit_note')}
                    LEFT JOIN (
                        SELECT {$this->db->dbprefix('credit_note_items')}.credit_note_id, COALESCE(SUM({$this->db->dbprefix('credit_note_items')}.quantity), 0) AS quantity 
                        FROM {$this->db->dbprefix('credit_note_items')} 
                        GROUP BY {$this->db->dbprefix('credit_note_items')}.credit_note_id
                    ) credit_note_items ON credit_note_items.credit_note_id = {$this->db->dbprefix('credit_note')}.id
                    GROUP BY {$this->db->dbprefix('credit_note')}.sale_id  
                ) credit_note ON credit_note.sale_id = {$this->db->dbprefix('sales')}.id
                WHERE {$this->db->dbprefix('sales')}.id = " . $id. " 
                GROUP BY {$this->db->dbprefix('sales')}.id
                LIMIT 1
            ");
        if($q->num_rows() > 0) {
            if($q->row()->quantity > 0) return false;
            else return true;
        }
        return false;
    }
    //-------------consignment---------
    public function getConsignmentItemByID($id = false, $product_id = false, $expiry = false, $serial_no = false)
    {
        if($product_id){
            $this->db->where("product_id",$product_id);
        }
        if($expiry){
            $this->db->where("IFNULL(expiry,'0000-00-00')",$expiry);
        }
        if($serial_no){
            $this->db->where("serial_no",$serial_no);
        }
        
        $this->db->select("(IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) as quantity");
        $this->db->where("(IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) > ", 0);
        $q = $this->db->get_where('consignment_items', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getSaleItemByConsigmentID($consignment_item_id = false)
    {
        $q = $this->db->get_where('sale_items',array('consignment_item_id'=>$consignment_item_id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    public function getConsignmentByID($id = false)
    {
        $q = $this->db->get_where('consignments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getConsigmentItems($consignment_id = false)
    {
        $this->db->order_by('id', 'desc');
        $this->db->select("consignment_items.*, (IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) as quantity");
        $this->db->where("(IFNULL(quantity,0) - IFNULL(return_qty,0) - IFNULL(sale_qty,0)) > ", 0);
        $q = $this->db->get_where('consignment_items', array('consignment_id' => $consignment_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function addConsignment($data = false, $items = false, $stockmoves = false , $accTrans = false)
    {
        if ($this->db->insert('consignments', $data)) {
            $consignment_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['consignment_id'] = $consignment_id;
                $this->db->insert('consignment_items', $item);
            }
            
            if($stockmoves){
                foreach($stockmoves as $stockmove){
                    $stockmove['transaction_id'] = $consignment_id;
                    $this->db->insert('stock_movement', $stockmove);
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['transaction_id'] = $consignment_id;
                    $this->db->insert('acc_tran', $accTran);
                }
            }
            
            if($data['consignment_id'] > 0){
                $this->site->syncConsignment($data['consignment_id']);
            }
            
            return true;
        }
        return false;
    }
    public function updateConsignment($id = false,$data = false, $items = false, $stockmoves = false , $accTrans = false)
    {
        if ($id && $id > 0 && $this->db->update('consignments', $data, array('id'=>$id))) {
            $this->db->delete('consignment_items',array('consignment_id'=>$id));
            $this->site->deleteAccTran('Consignment',$id);
            $this->site->deleteStockmoves('Consignment',$id);
            if($items){
                $this->db->insert_batch('consignment_items',$items);
            }
            if($stockmoves){
                $this->db->insert_batch('stock_movement',$stockmoves);
            }
            if($accTrans){
                $this->db->insert_batch('acc_tran',$accTrans);
            }
            return true;
        }
        return false;
    }
    public function deleteConsignment($id = false){
        $consignment = $this->getConsignmentByID($id);
        $consignment_returns = $this->getConsignmentByConsignID($id);
        if($id && $id > 0 && $this->db->delete('consignments',array('id'=>$id))){
            $this->db->delete('consignment_items',array('consignment_id'=>$id));
            $this->site->deleteAccTran('Consignment',$id);
            
            if($consignment_returns){
                foreach($consignment_returns as $consignment_returns){
                    $this->db->delete('consignments',array('id'=>$consignment_returns->id));
                    $this->db->delete('consignment_items',array('consignment_id'=>$consignment_returns->id));
                    $this->site->deleteAccTran('Consignment',$consignment_returns->id);
                }
            }
            if($consignment->consignment_id > 0){
                $consignment_id = $consignment->consignment_id;
            }else{
                $consignment_id = $id;
            }
            
            $this->site->syncConsignment($consignment_id);
            return true;
        }
        return false;
    }
    public function getConsignmentByConsignID($consignment_id = false){
        $q = $this->db->get_where('consignments',array('consignment_id'=>$consignment_id));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getChipmongSaleItems($tran_id, $group_tran = false)
    {
        $payments = " ( 
                SELECT 
                    {$this->db->dbprefix('payments')}.sale_id,
                    COALESCE(SUM(
                        IF (
                            {$this->db->dbprefix('payments')}.paid_by = 'cash',
                            COALESCE({$this->db->dbprefix('payments')}.amount, 0),
                            0
                        )
                    )) AS net_cash_sales,
                    COALESCE(SUM(
                        IF (
                            {$this->db->dbprefix('payments')}.paid_by = 'cash',
                            COALESCE(SUBSTRING_INDEX({$this->db->dbprefix('payments')}.paid_amount, ',', 1), 0),
                            0
                        )
                    )) AS total_amount_usd,
                    COALESCE(SUM(
                        IF (
                            {$this->db->dbprefix('payments')}.paid_by = 'cash',
                            COALESCE(SUBSTRING_INDEX(SUBSTRING_INDEX({$this->db->dbprefix('payments')}.paid_amount, ',', -2), ',', 1), 0),
                            0
                        )
                    )) AS total_amount_khr,
                    COALESCE(
                        SUM(
                            IF(
                                {$this->db->dbprefix('payments')}.paid_by = 'CC',
                                COALESCE({$this->db->dbprefix('payments')}.pos_paid, 0),
                                0
                            )
                        )
                    ) AS creditcard_amount,
                    COALESCE(
                        SUM(
                            IF(
                                ({$this->db->dbprefix('payments')}.paid_by != 'CC' AND {$this->db->dbprefix('payments')}.paid_by != 'cash'),
                                COALESCE({$this->db->dbprefix('payments')}.pos_paid, 0),
                                0
                            )
                        )
                    ) AS other_amount,
                    COALESCE(
                        COUNT(
                            IF(
                                {$this->db->dbprefix('payments')}.paid_by = 'CC',
                                {$this->db->dbprefix('payments')}.id,
                                NULL
                            )
                        )
                    ) AS creditcard_transaction,
                    COALESCE(SUBSTRING_INDEX(SUBSTRING_INDEX({$this->db->dbprefix('payments')}.currency_rate, ',', -2), ',', 1), 0) AS exchange_rate
                FROM {$this->db->dbprefix('payments')} 
                WHERE {$this->db->dbprefix('payments')}.sale_id IS NOT NULL 
                GROUP BY {$this->db->dbprefix('payments')}.sale_id ) bpas_pay ";

        $this->db->select("
                {$this->db->dbprefix('chipmong')}.generate_time as id, 
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('sales')}.reference_no, 
                {$this->db->dbprefix('sales')}.biller, 
                {$this->db->dbprefix('sales')}.customer, 
                {$this->db->dbprefix('sales')}.grand_total, 
                COALESCE(SUM({$this->db->dbprefix('sales')}.grand_total), 0) AS gross_sale,
                COALESCE(SUM({$this->db->dbprefix('sales')}.total_tax), 0) AS tax_amount,
                COALESCE(SUM({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.total_tax), 0) AS net_sale,
                COALESCE(SUM({$this->db->dbprefix('pay')}.net_cash_sales), 0) AS net_cash_sales,
                COALESCE(SUM({$this->db->dbprefix('pay')}.total_amount_usd), 0) AS cash_usd,
                COALESCE(SUM({$this->db->dbprefix('pay')}.total_amount_khr), 0) AS cash_khr,
                COALESCE(SUM({$this->db->dbprefix('pay')}.creditcard_amount), 0) AS creditcard_amount,
                COALESCE(SUM({$this->db->dbprefix('pay')}.other_amount), 0) AS other_amount,
                COALESCE(SUM({$this->db->dbprefix('pay')}.creditcard_transaction), 0) AS creditcard_transaction,
                COUNT({$this->db->dbprefix('sales')}.id) AS total_transaction,
                0 AS deposit_usd,
                0 AS deposit_khr, 
                {$this->db->dbprefix('sales')}.currency_rate_kh AS exchange_rate,
                {$this->db->dbprefix('chipmong')}.push")
            ->join('chipmong','chipmong.sale_id = sales.id','inner')
            ->join($payments, 'bpas_pay.sale_id = sales.id', 'left');
        $this->db->where('chipmong.generate_time', $tran_id);
        if ($group_tran) {
            $this->db->group_by('chipmong.generate_time');
        } else {
            $this->db->group_by('sales.id');    
        }
        $this->db->order_by('sales.id', 'desc');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function updateChipmongStatus($tran_id)
    {
        if (!empty($tran_id) && $tran_id != '') {
            if ($this->db->update('chipmong', ['push' => 1], ['generate_time' => $tran_id])) {
                return true;
            }
        }
        return false;
    }

    public function deleteChipmong($tran_id)
    {
        if (!empty($tran_id) && $tran_id != '') {
            $q = $this->db->get_where('chipmong', ['generate_time' => $tran_id]);
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    if (!empty($row->sale_id) && $row->sale_id != '') {
                        $this->db->update('sales', ['chipmong' => 0], ['id' => $row->sale_id]);
                    }
                }
            }
            $this->db->delete('chipmong', ['generate_time' => $tran_id]);
            return true;
        }
        return false;
    }

    public function getChipmongDailySales($biller_id = false, $start_date = false, $end_date = false, $pos = false)
    {
        $payments = " ( 
                SELECT 
                    {$this->db->dbprefix('payments')}.sale_id,
                    COALESCE(SUM(
                        IF (
                            {$this->db->dbprefix('payments')}.paid_by = 'cash',
                            COALESCE({$this->db->dbprefix('payments')}.amount, 0),
                            0
                        )
                    )) AS net_cash_sales,
                    COALESCE(SUM(
                        IF (
                            {$this->db->dbprefix('payments')}.paid_by = 'cash',
                            COALESCE(SUBSTRING_INDEX({$this->db->dbprefix('payments')}.paid_amount, ',', 1), 0),
                            0
                        )
                    )) AS total_amount_usd,
                    COALESCE(SUM(
                        IF (
                            {$this->db->dbprefix('payments')}.paid_by = 'cash',
                            COALESCE(SUBSTRING_INDEX(SUBSTRING_INDEX({$this->db->dbprefix('payments')}.paid_amount, ',', -2), ',', 1), 0),
                            0
                        )
                    )) AS total_amount_khr,
                    COALESCE(
                        SUM(
                            IF(
                                {$this->db->dbprefix('payments')}.paid_by = 'CC',
                                COALESCE({$this->db->dbprefix('payments')}.pos_paid, 0),
                                0
                            )
                        )
                    ) AS creditcard_amount,
                    COALESCE(
                        SUM(
                            IF(
                                ({$this->db->dbprefix('payments')}.paid_by != 'CC' AND {$this->db->dbprefix('payments')}.paid_by != 'cash'),
                                COALESCE({$this->db->dbprefix('payments')}.pos_paid, 0),
                                0
                            )
                        )
                    ) AS other_amount,
                    COALESCE(
                        COUNT(
                            IF(
                                {$this->db->dbprefix('payments')}.paid_by = 'CC',
                                {$this->db->dbprefix('payments')}.id,
                                NULL
                            )
                        )
                    ) AS creditcard_transaction,
                    COALESCE(SUBSTRING_INDEX(SUBSTRING_INDEX({$this->db->dbprefix('payments')}.currency_rate, ',', -2), ',', 1), 0) AS exchange_rate
                FROM {$this->db->dbprefix('payments')} 
                WHERE {$this->db->dbprefix('payments')}.sale_id IS NOT NULL 
                GROUP BY {$this->db->dbprefix('payments')}.sale_id ) bpas_pay ";

        $this->db->select("
                {$this->db->dbprefix('sales')}.date,
                {$this->db->dbprefix('sales')}.biller_id,
                GROUP_CONCAT({$this->db->dbprefix('sales')}.id SEPARATOR ',') AS sale_ids,
                COALESCE(SUM({$this->db->dbprefix('sales')}.grand_total), 0) AS gross_sale,
                COALESCE(SUM({$this->db->dbprefix('sales')}.total_tax), 0) AS tax_amount,
                COALESCE(SUM({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.total_tax), 0) AS net_sale,
                COALESCE(SUM({$this->db->dbprefix('pay')}.net_cash_sales), 0) AS net_cash_sales,
                COALESCE(SUM({$this->db->dbprefix('pay')}.total_amount_usd), 0) AS cash_usd,
                COALESCE(SUM({$this->db->dbprefix('pay')}.total_amount_khr), 0) AS cash_khr,
                COALESCE(SUM({$this->db->dbprefix('pay')}.creditcard_amount), 0) AS creditcard_amount,
                COALESCE(SUM({$this->db->dbprefix('pay')}.other_amount), 0) AS other_amount,
                COALESCE(SUM({$this->db->dbprefix('pay')}.creditcard_transaction), 0) AS creditcard_transaction,
                COUNT({$this->db->dbprefix('sales')}.id) AS total_transaction,
                0 AS deposit_usd,
                0 AS deposit_khr, 
                {$this->db->dbprefix('sales')}.currency_rate_kh AS currency_rate_kh")
        ->join($payments, 'bpas_pay.sale_id = sales.id', 'left');
        if ($biller_id) {
            $this->db->where('sales.biller_id', $biller_id);
        }
        if ($pos) {
            $this->db->where('sales.pos', 1);
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('sales'). '.date >= "' . $start_date . '"');
            $this->db->where($this->db->dbprefix('sales'). '.date <= "' . $end_date . '"');
        }
        $this->db->where(array('sale_status !=' => 'pending'));
        $this->db->where(array('chipmong !=' => 1));
        $this->db->limit(1);
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            if (empty($q->row()->sale_ids) || $q->row()->sale_ids == '') return false;
            return $q->row();
        }
        return false;
    }

    public function updateChipmongSalesStatus($sale_ids)
    {
        if (!empty($sale_ids) && $sale_ids != '') {
            foreach ($sale_ids as $id) {
                $this->db->update('sales', ['chipmong' => 1], ['id' => $id]);
            }
            return true;
        }
        return false;
    }

    public function getChipmongSalesDetails($ids)
    {
        $this->db->select('sales.*');
        $this->db->where("sales.id IN ({$ids})");
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getChipmongByID($id)
    {
        $q = $this->db->get_where('chipmong', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function importSale($sales = false, $sale_items = false ,$accTrans = false){
        if($sales && $sale_items){
            foreach($sales as $index => $sale){
                if($this->db->insert("sales",$sale)){
                    $sale_id = $this->db->insert_id();
                    if($sale_items[$index]){
                        if($this->Settings->product_expiry == '1' && $sale_items[$index]){
                            $checkExpiry = $this->site->checkExpiry($stockmoves[$index], $sale_items[$index],'POS');
                            $sale_items[$index] = $checkExpiry['expiry_items'];
                        }
                        foreach($sale_items[$index] as $sale_item){
                            unset($sale_item['unit_qty']);
                            $sale_item["sale_id"] = $sale_id;
                            $this->db->insert("sale_items",$sale_item);
                        }
                    }
                    if($accTrans[$index]){
                        foreach($accTrans[$index] as $accTran){
                            $accTran["tran_id"] = $sale_id;
                            $this->db->insert("gl_trans",$accTran);
                        }
                    }
                }

            }
            return true;
        }
        return false;
    }
    public function addDeposit($data = array(), $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['tran_no']= $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }
    public function updateDeposit($id = false, $data = array(), $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getDepositByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->deleteAccTran('SaleDeposit',$id);
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }

            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }
    public function getDepositByID($id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('payments.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function deleteDeposit($id = false)
    {
        $opay = $this->getDepositByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->deleteAccTran('SaleDeposit',$id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return FALSE;
    }
    public function getSaleDeposits($sale_id = false){
        $this->db->select("payments.*, 
            IFNULL(".$this->db->dbprefix('cash_accounts').".name,
            ".$this->db->dbprefix('payments').".paid_by) as paid_by");
        $this->db->where('transaction', 'SaleDeposit');
        $this->db->order_by('id', 'desc');
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if($q->num_rows() > 0 ){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getReceivePaymentByID($id = false){
        $q = $this->db->get_where("receive_payments",array("id"=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getReceivePaymentItems($receive_id = false){
        $q = $this->db->get_where('receive_payment_items', array('receive_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function addReceivePyament($data = false, $items = false){
        if($this->db->insert("receive_payments",$data)){
            $receive_id = $this->db->insert_id();
            if($items){
                foreach($items as $item){
                    $item["receive_id"] = $receive_id;
                    $this->db->insert("receive_payment_items",$item);
                }
            }
            return true;
        }
        return false;
    }
    
    public function updateReceivePyament($id = false, $data = false, $items = false){
        if($this->db->update("receive_payments",$data,array("id"=>$id))){
            $this->db->delete("receive_payment_items",array("receive_id"=>$id));
            if($items){
                $this->db->insert_batch("receive_payment_items",$items);
            }
            return true;
        }
        return false;
    }
    
    public function deleteReceivePayment($id = false){
        if($id && $this->db->delete("receive_payments",array("id"=>$id))){
            $this->db->delete("receive_payment_items",array("receive_id"=>$id));
            return true;
        }
        return false;
    }
    
    public function updateReceiveStatus($id = false, $data = false){
        if($this->db->update("receive_payments",$data,array("id"=>$id))){
            return true;
        }
        return false;
    }
    
    public function updateMultiReceiveStatus($ids = false, $data = false){
        if($ids && $data){
            $this->db->where_in("id",$ids);
            $this->db->where("status","checked");
            if($this->db->update("receive_payments",$data)){
                return true;
            }
        }
        return false;
    }
    
    public function getReceivePyamentByPaymentID($payment_id = false){
        $q = $this->db->get_where("receive_payment_items",array("payment_id"=>$payment_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSalePayments($biller_id = false,$created_by = false,$from_date = false,$to_date = false, $paid_by = false, $type = false, $receive_id = false){
        if($biller_id){
            $this->db->where("sales.biller_id",$biller_id);
        }
        if($created_by){
            $this->db->where("payments.created_by",$created_by);
        }
        if($paid_by){
            $this->db->where_in("payments.paid_by",$paid_by);
        }
        if($from_date){
            $this->db->where("date(".$this->db->dbprefix('payments').".date) >=",$from_date);
        }
        if($to_date){
            $this->db->where("date(".$this->db->dbprefix('payments').".date) <=",$to_date);
        }
        if($type){
            if($type=="sale"){
                $this->db->where("IFNULL(".$this->db->dbprefix('sales').".pos,0)",0);
            }else{
                $this->db->where("IFNULL(".$this->db->dbprefix('sales').".pos,0)",1);
            }
        }
        if($receive_id){
            $this->db->where($this->db->dbprefix('payments').".id NOT IN (SELECT payment_id FROM ".$this->db->dbprefix('receive_payment_items')." WHERE receive_id !=".$receive_id.")");
        }else{
            $this->db->where($this->db->dbprefix('payments').".id NOT IN (SELECT payment_id FROM ".$this->db->dbprefix('receive_payment_items').")");
        }
        $this->db->select("
                            payments.id,
                            payments.date,
                            payments.reference_no as payment_ref,
                            payments.amount,
                            sales.reference_no as sale_ref,
                            sales.customer,
                            CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
                            payments.paid_by as paid_by
                        ");
        $this->db->join("sales","sales.id = payments.sale_id","inner");
        $this->db->join("users","users.id = payments.created_by","left");
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $this->db->group_by("payments.id");
        $q = $this->db->get("payments");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPrescriptionByID($id)
    {
        $q = $this->db->get_where('prescription', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPrescriptionItems($quote_id)
    {
        $this->db->select('prescription_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name, products.type as product_type')
            ->join('products', 'products.id=prescription_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=prescription_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=prescription_items.tax_rate_id', 'left')
            ->group_by('prescription_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('prescription_items', ['sale_id' => $quote_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCustomerPrice($product_id = false,$customer_id = false)
    {
        $q = $this->db->get_where('customer_product_prices',array('customer_id'=>$customer_id,'product_id'=>$product_id));
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
    //-------------fuel---------------
    public function getTankNozzlesByTankID($tank_id = false)
    {
        $q = $this->db->select("
                            tank_nozzles.id,
                            tank_nozzles.tank_id,
                            tank_nozzles.product_id,
                            tank_nozzles.nozzle_no,
                            tank_nozzles.nozzle_start_no,
                            products.code as product_code,
                            products.name as product_name,
                            products.price as unit_price
    
                        ")
                      ->where("tank_id", $tank_id)
                      ->join("products",'products.id=product_id','left')
                      ->get("tank_nozzles");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getWarehouseProductByNozzles($tank_id = false,$nozzle_no = false,$product_id = false)
    {
        $q = $this->db->get_where("tank_nozzles",["tank_id"=>$tank_id,"product_id"=>$product_id,"nozzle_no"=>$nozzle_no]);
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getTankByID($id = false)
    {
        $q = $this->db->where("id", $id)->get("tanks");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    
    public function getTankNames($term = false, $warehouse_id = false, $limit = false)
    {
        $limit = $this->Settings->rows_per_page;
        if($warehouse_id){
            $this->db->where("tanks.warehouse_id",$warehouse_id);
        }
        $this->db->where("IFNULL(inactive,0)",0);
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('tanks');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getFuelSaleQuantityItem($tank_id = false, $nozzle_no = false)
    {
        $q = $this->db->select("MAX(nozzle_end_no) as quantity")
                      ->where("tank_id", $tank_id)
                      ->where("nozzle_id", $nozzle_no)
                      ->get("fuel_sale_items");
        if($q->num_rows()>0){
            $row = $q->row();
            
            return $row;
        }
        return false;
    }
    public function addFuelSale($data = false, $items = false, $stockmoves = false, $accTrans = false)
    {

        if($this->db->insert("fuel_sales", $data)){
            $id = $this->db->insert_id();
            if($items){
                foreach($items as $item){
                    $item['fuel_sale_id'] = $id;
                    $this->db->insert("fuel_sale_items",$item);
                    $this->db->query("UPDATE ".$this->db->dbprefix('fuel_customer_items')."
                                    INNER JOIN ".$this->db->dbprefix('fuel_customers')." ON ".$this->db->dbprefix('fuel_customers').".id = ".$this->db->dbprefix('fuel_customer_items').".fuel_customer_id 
                                    SET ".$this->db->dbprefix('fuel_customer_items').".fuel_sale_id = ".$id." 
                                    WHERE
                                        ".$this->db->dbprefix('fuel_customers').".saleman_id = ".$data['saleman_id']."
                                        AND ".$this->db->dbprefix('fuel_customer_items').".nozzle_id = ".$item['nozzle_id']."
                                        AND IFNULL(".$this->db->dbprefix('fuel_customer_items').".fuel_sale_id,0) = 0
                                ");
                }
            }
            if($stockmoves){
                foreach($stockmoves as $stockmove){
                    $stockmove['transaction_id'] = $id;
                    $this->db->insert("stock_movement",$stockmove);
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $id;
                    $this->db->insert("gl_trans",$accTran);
                }
            }

            if ($this->site->getReference('fuel',$data['biller_id']) == $data['reference_no']) {
                $this->site->updateReference('fuel');
            }
            return true;
        }
        return false;
    }
    
    public function updateFuelSale($id = false, $data = false, $items = false, $stockmoves = false, $accTrans = false)
    {
        if($this->db->where("id", $id)->update("fuel_sales", $data)){
            $this->site->deleteStockmoves('FuelSale',$id);
            $this->site->deleteAccTran('FuelSale',$id);
            if($items){
                $this->db->where("fuel_sale_id", $id)->delete("fuel_sale_items");
                foreach($items as $item){
                    $item['fuel_sale_id'] = $id;
                    $this->db->insert("fuel_sale_items",$item);
                }
            }
            if($stockmoves){
                $this->db->insert_batch("stock_movement",$stockmoves);
            }
            if($accTrans){
                $this->db->insert_batch("gl_trans",$accTrans);
            }
            return true;
        }
        return false;
    }
    public function getFuelCustomerNozzleQuantity($salesman_id = false, $nozzle_id = false){
        if($salesman_id){
            $this->db->where("fuel_customers.saleman_id",$salesman_id);
        }
        if($nozzle_id){
            $this->db->where("fuel_customer_items.nozzle_id",$nozzle_id);
        }
        $this->db->where("IFNULL(".$this->db->dbprefix('fuel_customer_items').".fuel_sale_id,0)",0);
        $this->db->select("sum(".$this->db->dbprefix("fuel_customer_items").".quantity) as quantity,sum(".$this->db->dbprefix("fuel_customer_items").".quantity * ".$this->db->dbprefix("fuel_customer_items").".unit_price) as amount");
        $this->db->join("fuel_customers","fuel_customers.id = fuel_customer_items.fuel_customer_id","INNER");
        $q = $this->db->get("fuel_customer_items");
        if($q->num_rows() >0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function addFuelCustomer($data = false, $items = false, $stockmoves = false, $acctrans = false)
    {
        if($this->db->insert("fuel_customers", $data)){
            $fuel_customer_id = $this->db->insert_id();
            if($items){
                foreach($items as $item){
                    $item["fuel_customer_id"] = $fuel_customer_id;
                    $this->db->insert("fuel_customer_items",$item);
                }
            }
            if($stockmoves){
                foreach($stockmoves as $stockmove){
                    $stockmove["transaction_id"] = $fuel_customer_id;
                    $this->db->insert("stock_movement",$stockmove);
                }
            }
            if($acctrans){
                foreach($acctrans as $acctran){
                    $acctran["tran_no"] = $fuel_customer_id;
                    $this->db->insert("gl_trans",$acctran);
                }
            }

            if ($this->site->getReference('cfuel',$data['biller_id']) == $data['reference']) {
                $this->site->updateReference('cfuel');
            }
            return true;
        }
        return false;
    }
    public function updateFuelCustomer($id = false,$data = false, $items = false, $stockmoves = false, $acctrans = false){
        if($this->db->update("fuel_customers", $data, array("id"=>$id))){
            $this->db->delete("fuel_customer_items",array("fuel_customer_id"=>$id));
            $this->site->deleteStockmoves('FuelCustomer',$id);
            $this->site->deleteAccTran('FuelCustomer',$id);
            if($items){
                $this->db->insert_batch("fuel_customer_items",$items);
                foreach($items as $item){
                    if($item["fuel_sale_id"] > 0){
                        $fuel_customer = $this->getGrandTotalFuelCustomer($item["fuel_sale_id"],$item["product_id"],$item["nozzle_id"]);
                        if($fuel_customer){
                            $this->db->query("UPDATE ".$this->db->dbprefix('fuel_sale_items')."
                                    INNER JOIN ".$this->db->dbprefix('fuel_sales')." ON ".$this->db->dbprefix('fuel_sales').".id = ".$this->db->dbprefix('fuel_sale_items').".fuel_sale_id 
                                    SET ".$this->db->dbprefix('fuel_sale_items').".customer_amount = ".$fuel_customer->grand_total." 
                                    WHERE
                                        ".$this->db->dbprefix('fuel_sales').".saleman_id = ".$data['saleman_id']."
                                        AND ".$this->db->dbprefix('fuel_sale_items').".nozzle_id = ".$item['nozzle_id']."
                                        AND ".$this->db->dbprefix('fuel_sale_items').".fuel_sale_id = ".$item['fuel_sale_id']."
                                ");
                        }                   
                    }
                }
            }
            if($stockmoves){
                $this->db->insert_batch("stock_movement",$stockmoves);
            }
            if($acctrans){
                $this->db->insert_batch("gl_trans",$acctrans);
            }
            return true;
        }
        return false;
    }
    
    public function deleteFuelCustomer($id = false){
        if($id && $id > 0){
            if($this->db->delete("fuel_customers",array("id"=>$id))){
                $this->db->delete("fuel_customer_items",array("fuel_customer_id"=>$id));
                $this->site->deleteStockmoves('FuelCustomer',$id);
                $this->site->deleteAccTran('FuelCustomer',$id);
                return true;
            }
        }
        return false;
    }
    public function getFuelCustomerByID($id = false){
        $this->db->select("*,fuel_customers.saleman_id as saleman_by");

        $q = $this->db->get_where("fuel_customers",array("id"=>$id));
        if($q->num_rows() >0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getFuelCustomerByIDs($ids = false){
        if($ids){
            $this->db->where_in("id",$ids);
            $q = $this->db->get("fuel_customers");
            if($q->num_rows() >0){
                foreach($q->result() as $row){
                    $data[] = $row;
                }
                return $data;
            }
        }
        return false;
    }
    public function getFuelCustomerItemsForSale($fuel_customer_id = false){
        $this->db->select("
                            products.id as product_id,
                            products.code as product_code,
                            products.name as product_name,
                            products.type as product_type,
                            fuel_customer_items.unit_price as real_unit_price,
                            fuel_customer_items.unit_price as net_unit_price,
                            fuel_customer_items.unit_price,
                            fuel_customer_items.quantity,
                            fuel_customer_items.quantity as unit_quantity,
                            products.unit as product_unit_id,
                            fuel_customers.warehouse_id,
                            fuel_customers.date as fuel_customer_date,
                            fuel_customers.reference as fuel_customer_reference,
                            '' as option_id,
                            0 as item_discount
                        ");
        $this->db->where_in("fuel_customer_items.fuel_customer_id",$fuel_customer_id);
        $this->db->join("products","products.id = fuel_customer_items.product_id","inner");
        $this->db->join("fuel_customers","fuel_customers.id = fuel_customer_items.fuel_customer_id","inner");
        $q = $this->db->get("fuel_customer_items");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getFuelCustomerItems($fuel_customer_id = false){
        $this->db->select("fuel_customer_items.*,tanks.name as tank_name,tanks.code as tank_code,products.name as product_name, products.code as product_code, customer_trucks.name as truck_name, customer_trucks.plate_number");
        $this->db->join("tanks","tanks.id = fuel_customer_items.tank_id","LEFT");
        $this->db->join("customer_trucks","customer_trucks.id = fuel_customer_items.truck_id","LEFT");
        $this->db->join("products","products.id = fuel_customer_items.product_id","LEFT");
        $this->db->where("fuel_customer_items.fuel_customer_id",$fuel_customer_id);
        $q = $this->db->get("fuel_customer_items");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getFuelTimes()
    {
        $q = $this->db->get("fuel_times");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getFuelItemsBySaleman($saleman = false)
    {
        $this->db->select("tank_nozzles.*, tanks.code, tanks.name")
                ->join("tanks","tanks.id=tank_id","inner")
                ->join("tank_nozzle_salesmans","tank_nozzle_salesmans.tank_id = tank_nozzles.tank_id AND tank_nozzle_salesmans.nozzle_id = tank_nozzles.id","inner")
                ->where("tank_nozzle_salesmans.saleman_id",$saleman)
                ->where("IFNULL(".$this->db->dbprefix('tanks').".inactive,0)",0)
                ->group_by("tank_nozzles.id")
                ->order_by("tanks.name,tank_nozzles.nozzle_no");
        $q = $this->db->get("tank_nozzles");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getFuelSaleByInoiceID($id = false)
    {
        $q = $this->db->where("fuel_sales.id", $id)
        ->select("fuel_sales.date as date,fuel_sales.reference_no as reference_no, biller_id,biller,saleman_id,saleman_id as saleman_by,warehouse_id,total,quantity,note, SUM(quantity) as quantity")
        ->join("fuel_sale_items","fuel_sales.id=fuel_sale_items.fuel_sale_id","left")
        ->get("fuel_sales");
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getFuelSaleByID($id = false)
    {
        $q = $this->db->where("fuel_sales.id", $id)
                      ->select("fuel_sales.*, SUM(quantity) as quantity")
                      ->join("fuel_sale_items","fuel_sales.id=fuel_sale_items.fuel_sale_id","left")
                      ->get("fuel_sales");
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getAllInvoiceItems____($sale_id, $return_id = null)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                units.name as unit_name,
                products.slug,
                products.price, 
                sale_units.name as name_unit,
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
                options.name as option_name,
                sale_units.name as product_unit_name
            ')
        ->join('products', 'products.id=sale_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('units sale_units', 'sale_units.id=sale_items.product_unit_id', 'left')
        ->join('options', 'options.id=sale_items.option_comment_id', 'left')
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
    public function getAllFuelSaleItems($id = false)
    {
        $q = $this->db
                      ->select('
                            fuel_sale_items.fuel_sale_id,
                            fuel_sale_items.tank_id,
                            fuel_sale_items.product_id,
                            fuel_sale_items.nozzle_id,
                            fuel_sale_items.nozzle_no,
                            fuel_sale_items.nozzle_start_no,
                            fuel_sale_items.nozzle_end_no,
                            fuel_sales.warehouse_id,
                            SUM(bpas_fuel_sale_items.quantity) - IFNULL(bpas_sale_items.quantity,0) as quantity,
                            SUM(bpas_fuel_sale_items.quantity) - IFNULL(bpas_sale_items.quantity,0) as unit_quantity,
                            products.id as product_id,
                            products.code as product_code,
                            products.name as product_name,
                            products.type as product_type,
                            products.sale_unit as product_unit_id,
                            fuel_sale_items.unit_price as unit_price,
                            fuel_sale_items.unit_price as net_unit_price,
                            fuel_sale_items.unit_price as real_unit_price,
                            0 as option_id,
                            0 as item_discount
                        ')
                      ->from("fuel_sale_items")
                      ->join('fuel_sales','fuel_sales.id=fuel_sale_id','left')
                      ->join('tanks','tanks.id=tank_id','left')
                      ->join('products','products.id=product_id','left')
                      ->join('(SELECT 
                                        fuel_sale_id,
                                        product_id,
                                        SUM(quantity) as quantity
                                    FROM bpas_sale_items
                                    LEFT JOIN bpas_sales ON bpas_sales.id= bpas_sale_items.sale_id
                                        GROUP BY product_id,bpas_sales.fuel_sale_id
                                    ) as bpas_sale_items','bpas_sale_items.product_id=fuel_sale_items.product_id AND bpas_sale_items.fuel_sale_id=fuel_sales.id','left')
                      ->where("fuel_sales.id", $id)
                      ->order_by('nozzle_no, product_name','asc')
                      ->group_by('products.id, unit_price')
                      //->having('unit_quantity >', 0)
                      ->get();
                      
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getFuelTimeByID($id = null)
    {
        $q = $this->db->where("id", $id)->get("fuel_times");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function getFuelSaleItemsByFuelSaleID($id = false)
    {
        $q = $this->db->select('
                        fuel_sale_items.fuel_sale_id,
                        fuel_sale_items.tank_id,
                        fuel_sale_items.product_id,
                        fuel_sale_items.nozzle_id,
                        fuel_sale_items.nozzle_no,
                        fuel_sale_items.nozzle_start_no,
                        fuel_sale_items.nozzle_end_no,
                        fuel_sale_items.quantity,
                        fuel_sale_items.using_qty,
                        fuel_sale_items.customer_qty,
                        fuel_sale_items.customer_amount,
                        fuel_sale_items.unit_price,
                        fuel_sale_items.subtotal,
                        tanks.code as tank_code,
                        tanks.name as tank_name,
                        tanks.code as tank_code,
                        tanks.name as tank_name,
                        products.code as product_code,
                        products.name as product_name')
                      ->where("fuel_sale_id", $id)
                      ->join('tanks','tanks.id=tank_id','left')
                      ->join('products','products.id=product_id','left')
                      ->order_by('nozzle_no, product_name','asc')
                      ->get("fuel_sale_items");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getSaleByFuelID($id = false)
    {
        $q = $this->db->where("fuel_sale_id", $id)->get("sales");
        if($q->num_rows() >0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function deleteFuelSale($id = false)
    {
        if($id && $id > 0){ 
            if($this->db->where("id", $id)->delete("fuel_sales")){
                $this->db->where("fuel_sale_id", $id)->delete("fuel_sale_items");
                $this->db->update("fuel_customer_items",array("fuel_sale_id"=>0),array("fuel_sale_id"=>$id));
                $this->site->deleteStockmoves('FuelSale',$id);
                $this->site->deleteAccTran('FuelSale',$id);
                return true;
            }
        }
        return false;
    }
    public function getMaintenanceItemByID($id = null)
    {
        $q = $this->db->where("id", $id)->get("maintenance_items");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    public function updateMaintenance_status($id = null, $data = []){
        if ($this->db->update('maintenance_items', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function delete_maintenance_item($id)
    {
        if ($this->db->delete('maintenance_items', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function add_generate($data = array())
    {

        if ($this->db->insert('sale_generate', $data)) {
            $quote_id = $this->db->insert_id();
            if ($this->site->getReference('gen') == $data['reference_no']) {
                $this->site->updateReference('gen');
            }
            //$this->site->sendTelegram("quotation",$quote_id,"added");
            return true;
        }
        return false;
    }
     public function deleteGenerate($id = false)
    {
        //$quotation = $this->getQuoteByID($id);
        if ($this->db->delete('sale_generate', array('id' => $id))) {
            //$this->site->sendTelegram("quotation",$id,"deleted",$quotation);
            return true;
        }
        return FALSE;
    }
    public function getSaleReturnAmount($sale_id = false){
        $this->db->select("sum(grand_total * (-1)) as grand_total");
        $q = $this->db->get_where("sales",array("sale_id"=>$sale_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSaleItemWithReturns($sale_id = false)
    {
        if ($sale_id) {
            $this->db->where('sale_items.sale_id', $sale_id);
        }
        $this->db->select('sale_items.sale_id,
                            sale_items.product_id,
                            sale_items.product_code,
                            sale_items.product_name,
                            sale_items.product_type,
                            sale_items.expiry,
                            sale_items.product_unit_id,
                            sale_items.discount,
                            sale_items.tax_rate_id,
                            sale_items.option_id,
                            sale_items.comment,
                            sale_items.warehouse_id,
                            SUM(bpas_sale_items.net_unit_price * bpas_sale_items.unit_quantity) / SUM(bpas_sale_items.unit_quantity) AS net_unit_price,
                            SUM(bpas_sale_items.unit_price * bpas_sale_items.unit_quantity) / SUM(bpas_sale_items.unit_quantity) AS unit_price,
                            SUM(bpas_sale_items.real_unit_price * bpas_sale_items.quantity) / SUM(bpas_sale_items.quantity) AS real_unit_price,
                            SUM(bpas_sale_items.unit_quantity) as unit_quantity,
                            SUM(bpas_sale_items.quantity) as quantity,
                            SUM(bpas_sale_items.item_discount) as item_discount,
                            SUM(bpas_sale_items.item_tax) as item_tax,
                            SUM(bpas_sale_items.foc) as foc,
                            sale_returns.return_quantity
                        ')
                    ->join('(SELECT
                                bpas_sales.sale_id,
                                bpas_sale_items.product_id,
                                IFNULL(bpas_sale_items.expiry,"0000-00-00") as expiry,
                                SUM(bpas_sale_items.quantity * (-1)) as return_quantity
                            FROM
                                bpas_sales
                                INNER JOIN bpas_sale_items ON bpas_sale_items.sale_id = bpas_sales.id 
                            WHERE
                                bpas_sales.sale_id > 0 
                            GROUP BY
                                bpas_sales.sale_id,
                                bpas_sale_items.product_id,
                                IFNULL(bpas_sale_items.expiry,"0000-00-00")
                            ) as sale_returns','sale_returns.sale_id = sale_items.sale_id AND sale_returns.product_id = sale_items.product_id AND sale_returns.expiry = IFNULL('.$this->db->dbprefix('sale_items').'.expiry,"0000-00-00")','left')
                    ->group_by('sale_items.id,IFNULL('.$this->db->dbprefix('sale_items').'.expiry,"0000-00-00")');
        $q = $this->db->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPaymentsBySale($sale_id = false)
    {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getInvoiceBalanceByID($id = false)
    {
		$this->db->select('sales.project_id,sales.customer_id,sales.biller_id,sales.id,sales.date,sales.reference_no,sales.grand_total, IFNULL(bpas_payments.paid,0) as paid, IFNULL(bpas_payments.discount,0) as discount,total_return,paid_return,sales.ar_account')
		->join('(select sale_id, abs(sum(grand_total)) as total_return, abs(sum(paid)) as paid_return from '.$this->db->dbprefix('sales').' group by sale_id) as sale_return','sale_return.sale_id=sales.id','left')
		->join('(SELECT
					sale_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(discount),0) AS discount
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					sale_id) as bpas_payments', 'bpas_payments.sale_id=sales.id', 'left');
		$this->db->where('sales.id',$id);
		$this->db->where('sales.payment_status!=','paid');
		$this->db->order_by('sales.date');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getSaleReturnByID($id = false)
    {
        $this->db->select('sales.*,sales.credit_amount AS grand_total')->where('id',$id);
        $q= $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPaymentTermsByDueDay($id = NULL)
    {
        $q = $this->db->where('due_day', $id)->get('payment_terms');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getComboProducts($pid = false){
		$this->db->select('
            products.id,products.name,products.code,
            combo_items.unit_price as price, combo_items.quantity as qty, combo_items.quantity as width, 1 as height');
		$this->db->join('products','products.code = combo_items.item_code','inner');
		$this->db->where('combo_items.product_id',$pid);
		$q = $this->db->get('combo_items');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
     public function getRefSales($delivery_status = false, $sale_status= false)
    {
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $biller_ids = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $biller_ids = $user->biller_id ? ((array) $user->biller_id) : null;
        }
        $warehouse_ids = explode(',', $this->session->userdata('warehouse_id'));
        $this->db->select('sales.*');
        if ($sale_status) {
            $this->db->where('sales.sale_status',$sale_status);
        }        
        if ((!$this->Owner && !$this->Admin) && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('sales.warehouse_id', $warehouse_ids);
        }
        if ((!$this->Owner && !$this->Admin) && !empty($biller_ids)) {
            $this->db->where_in('sales.biller_id', $biller_ids);
        }
        $this->db->where('sale_status !=', 'draft');
        $this->db->where('sale_status !=', 'returned');
        $this->db->where('sales.module_type', 'inventory');
        $this->db->where('sales.store_sale !=', 1);
        $this->db->where('sales.hide', 1);
        $this->db->order_by('id','desc');
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductSerialDetailsByProductId($product_id = false, $warehouse_id = false, $serial = false)
    {
        if($warehouse_id){
            $this->db->where("warehouse_id", $warehouse_id);
        }
        if($serial){
            $this->db->where("(serial='".$serial."' OR inactive='0' OR ISNULL(inactive))");
        }else{
            $this->db->where("(inactive='0' OR ISNULL(inactive))");
        }
        $q = $this->db->where("product_id",$product_id)->get("product_serials");
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}