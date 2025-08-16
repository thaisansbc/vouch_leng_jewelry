<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pos_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
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

    public function getProductsInOut($category =null, $warehouse  =null,$start_date  =null,$end_date  =null)
    {
        $start_date = $this->session->userdata('register_open_time');
        $end_date_ = date('Y-m-d H:i:s');
        $sec = strtotime($end_date_);  
        $end_date = date ("Y-m-d H:i", $sec);  
        $end_date = $end_date . ":59";
        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
        // WHERE p.status != 'pending' AND p.status != 'ordered'
        $sp = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM(item_discount) totalDiscount, SUM(item_tax) totalTax, SUM( si.subtotal ) totalSale from ' . $this->db->dbprefix('sales') . ' s JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id ';
        if ($start_date ) {
            $sp .= ' WHERE ';
            if ($start_date) {
                $pp .= " AND p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
            }
            if ($warehouse) {
                $pp .= " AND pi.warehouse_id = ".$warehouse." ";
                $sp .= " AND si.warehouse_id = ".$warehouse." ";
            }
        }
        if (!$this->Owner && !$this->Admin) {
            $sp .= " AND s.created_by = ".$this->session->userdata('user_id')."";
            // $this->db->where($this->db->dbprefix('sale_items') . '.created_by', $this->session->userdata('user_id'));
        }
        $pp .= ' GROUP BY pi.product_id ) PCosts';
        $sp .= ' GROUP BY si.product_id ) PSales';
        $this->db->select("products.code, products.name,categories.name as category,
            PCosts.purchasedQty as purchasedQty,PCosts.totalPurchase as totalPurchase,
            PSales.soldQty as soldQty, PSales.totalSale as totalSale,PSales.totalTax as totalTax,PSales.totalDiscount as totalDiscount,
            PCosts.balacneQty as balacneQty, PCosts.balacneValue as balacneValue,
            products.id as id", false)
            ->join($sp, 'products.id = PSales.product_id', 'left')
            ->join($pp, 'products.id = PCosts.product_id', 'left')
            ->join("categories","categories.id=products.category_id","LEFT")
            ->where('PSales.soldQty >',0);
        //    ->group_by('products.code')
        if ($category) {
            $this->db->where($this->db->dbprefix('products') . '.category_id', $category);
        }
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCategoryInOuts($start_date = null, $end_date = null)
    {
        $start_date = $this->session->userdata('register_open_time');
        $end_date_ = date('Y-m-d H:i:s');
        $sec = strtotime($end_date_);  
        $end_date = date ("Y-m-d H:i", $sec);  
        $end_date = $end_date . ":59";
        $pp = "( SELECT product_id, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
        $sp = '( SELECT si.product_id, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from ' . $this->db->dbprefix('sales') . ' s JOIN ' . $this->db->dbprefix('sale_items') . ' si on s.id = si.sale_id ';
        if ($start_date) {
            $sp .= ' WHERE ';
            if ($start_date) {
                $pp .= " AND p.date >= '".$start_date."' AND p.date < '".$end_date."' ";
                $sp .= "s.date >= '".$start_date."' AND s.date < '".$end_date."' ";
            }
        }
        if (!$this->Owner && !$this->Admin) {
            $sp .= " AND s.created_by = ".$this->session->userdata('user_id')."";
        }
        $pp .= ' GROUP BY pi.product_id ) PCosts';
        $sp .= ' GROUP BY si.product_id ) PSales';
            $this->db->select("categories.id as category_id,categories.name as category", false)
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->join("categories","categories.id=products.category_id","LEFT")
                ->where('PSales.soldQty >',0)
                ->order_by('products.category_id')
                ->group_by('products.category_id');
        $q = $this->db->get("products");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($payment = [], $customer_id = null, $accTranPayments = array())
    {
        if (isset($payment['sale_id']) && isset($payment['paid_by']) && isset($payment['amount'])) {
            $payment['pos_paid'] = $payment['amount'];
            $inv                 = $this->getInvoiceByID($payment['sale_id']);
            $paid                = $inv->paid + $payment['amount'];
            if ($payment['paid_by'] == 'ppp') {
                $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                $result    = $this->paypal($payment['amount'], $card_info, '', $payment['sale_id']);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date']           = $this->bpas->fld($result['created_at']);
                    $payment['amount']         = $result['amount'];
                    $payment['currency']       = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $payment_id = $this->db->insert_id();
                    //=========Add Accounting =========//
                    if($accTranPayments){
                        foreach($accTranPayments as $accTranPayment){
                            $accTranPayment['tran_no']= $payment_id;
                            $this->db->insert('gl_trans', $accTranPayment);
                        }
                    }
                    //=========End Accounting =========//
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    if (!empty($result['message'])) {
                        foreach ($result['message'] as $m) {
                            $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                        }
                    } else {
                        $msg[] = lang('paypal_empty_error');
                    }
                }
            } elseif ($payment['paid_by'] == 'stripe') {
                $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                $result    = $this->stripe($payment['amount'], $card_info);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date']           = $this->bpas->fld($result['created_at']);
                    $payment['amount']         = $result['amount'];
                    $payment['currency']       = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $payment_id = $this->db->insert_id();
                    //=========Add Accounting =========//
                    if($accTranPayments){
                        foreach($accTranPayments as $accTranPayment){
                            $accTranPayment['tran_no']= $payment_id;
                            $this->db->insert('gl_trans', $accTranPayment);
                        }
                    }
                    //=========End Accounting =========//
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                }
            } elseif ($payment['paid_by'] == 'authorize') {
                $authorize_arr                 = ['x_card_num' => $payment['cc_no'], 'x_exp_date' => ($payment['cc_month'] . '/' . $payment['cc_year']), 'x_card_code' => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $inv->id, 'x_description' => 'Sale Ref ' . $inv->reference_no . ' and Payment Ref ' . $payment['reference_no']];
                list($first_name, $last_name)  = explode(' ', $payment['cc_holder'], 2);
                $authorize_arr['x_first_name'] = $first_name;
                $authorize_arr['x_last_name']  = $last_name;
                $result                        = $this->authorize($authorize_arr);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['approval_code']  = $result['approval_code'];
                    $payment['date']           = $this->bpas->fld($result['created_at']);
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
                    $payment_id = $this->db->insert_id();
                    //=========Add Accounting =========//
                    if($accTranPayments){
                        foreach($accTranPayments as $accTranPayment){
                            $accTranPayment['tran_no']= $payment_id;
                            $this->db->insert('gl_trans', $accTranPayment);
                        }
                    }
                    //=========End Accounting =========//
                    $paid += $payment['amount'];
                } else {
                    $msg[] = lang('payment_failed');
                    $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                }
            } else {
                if ($payment['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($payment['cc_no']);
                    $this->db->update('gift_cards', ['balance' => ($gc->balance - $payment['amount'])], ['card_no' => $payment['cc_no']]);
                } elseif ($customer_id && $payment['paid_by'] == 'deposit') {
                    $customer = $this->site->getCompanyByID($customer_id);
                    $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $payment['amount'])], ['id' => $customer_id]);
                }
                unset($payment['cc_cvv2']);
                $this->db->insert('payments', $payment);
                $payment_id = $this->db->insert_id();
                //=========Add Accounting =========//
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no']= $payment_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                //=========End Accounting =========//
                $paid += $payment['amount'];
            }
            if (!isset($msg)) {
                if ($this->site->getReference('pay') == $data['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($payment['sale_id']);
                return ['status' => 1, 'msg' => ''];
            }
            return ['status' => 0, 'msg' => $msg];
        }
        return false;
    }

    public function addPrinter($data = [])
    {
        if ($this->db->insert('printers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function addQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($warehouse_quantity = $this->getProductQuantity($product_id, $warehouse_id)) {
            $new_quantity = $warehouse_quantity['quantity'] - $quantity;
            if ($this->updateQuantity($product_id, $warehouse_id, $new_quantity)) {
                $this->site->syncProductQty($product_id, $warehouse_id);
                return true;
            }
        } else {
            if ($this->insertQuantity($product_id, $warehouse_id, -$quantity)) {
                $this->site->syncProductQty($product_id, $warehouse_id);
                return true;
            }
        }
        return false;
    }

    public function addSale($data = [], $items = [], $items_addon = [], $stockmoves = [], $payments = [], $sid = null, $accTrans = array(), $accTranPayments = array(), $note_id = null)
    {
        // var_dump($payments);
        // exit();
        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            //=======Add accounting =======//
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //=======End accounting =======//
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $row_id = $item['row_id'];
                unset($item['row_id']);
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                $combo_items = $this->getProductComboItems($item['product_id'], $data['warehouse_id']); 
                if (!empty($combo_items)) {
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') { 
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
                                'net_unit_price'  => $combo_item->unit_price,
                                'unit_price'      => $combo_item->unit_price,
                                'currency'        => 'usd',
                                'tax_rate'        => null,
                                'option_id'       => null,
                                'subtotal'        => $combo_item->unit_price * $item['quantity']
                            );
                            $this->db->insert('sale_combo_items', $item_combo);
                        }
                    }
                }
                foreach ($items_addon as $item_addon) {
                    $item_addon['sale_id'] = $sale_id;
                    if ($row_id == $item_addon['addon_row_id']) {
                        $item_addon['sale_item_id'] = $sale_item_id;
                        unset($item_addon['addon_row_id']);
                        $this->db->insert('sale_addon_items', $item_addon);
                    }
                }
            }
            if ($stockmoves) {
                foreach ($stockmoves as $stockmove) {
                    if ($stockmove['product_type'] != 'combo') {
                        $stockmove['transaction_id'] = $sale_id;
                        $this->db->insert('stock_movement', $stockmove);
                        if ($this->site->stockMovement_isOverselling($stockmove)) {
                            $this->session->set_flashdata('error', sprintf(lang('quantity_out_of_stock')));
                            redirect($_SERVER['HTTP_REFERER']);  
                        }
                    }
                }
            }
            if ($sid) {
                $this->deleteBill($sid, $note_id);
            }

            if($this->Settings->apoint_option=='qty'){
                $this->bpas->update_award_points_byQty($sale_id,$data['customer_id'],$data['created_by']);
            }else{
                $this->bpas->update_award_points($data['grand_total'],$data['customer_id'],$data['created_by']);
            }
            
            $this->site->updateReference('pos');
            $this->site->updateReference('bill');
            $msg = [];
            if (!empty($payments)) {
                $paid = 0;
                foreach ($payments as $payment) {
                    if (!empty($payment) && isset($payment['amount'])) {
                        $payment['sale_id']      = $sale_id;
                        $payment['reference_no'] = $this->site->getReference('pay');
                        $payment_id = -1;
                        if ($payment['paid_by'] == 'ppp') {
                            $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                            $result    = $this->paypal($payment['amount'], $card_info, '', $sale_id);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date']           = $this->bpas->fld($result['created_at']);
                                $payment['amount']         = $result['amount'];
                                $payment['currency']       = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $payment_id = $this->db->insert_id();
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                if (!empty($result['message'])) {
                                    foreach ($result['message'] as $m) {
                                        $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                                    }
                                } else {
                                    $msg[] = lang('paypal_empty_error');
                                }
                            }
                            $payment_id = $this->db->insert_id();
                        } elseif ($payment['paid_by'] == 'stripe') {
                            $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                            $result    = $this->stripe($payment['amount'], $card_info);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date']           = $this->bpas->fld($result['created_at']);
                                $payment['amount']         = $result['amount'];
                                $payment['currency']       = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $payment_id = $this->db->insert_id();
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                            }
                            $payment_id = $this->db->insert_id();
                        } elseif ($payment['paid_by'] == 'authorize') {
                            $authorize_arr                 = ['x_card_num' => $payment['cc_no'], 'x_exp_date' => ($payment['cc_month'] . '/' . $payment['cc_year']), 'x_card_code' => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $sale_id, 'x_description' => 'Sale Ref ' . $data['reference_no'] . ' and Payment Ref ' . $payment['reference_no']];
                            list($first_name, $last_name)  = explode(' ', $payment['cc_holder'], 2);
                            $authorize_arr['x_first_name'] = $first_name;
                            $authorize_arr['x_last_name']  = $last_name;
                            $result                        = $this->authorize($authorize_arr);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['approval_code']  = $result['approval_code'];
                                $payment['date']           = $this->bpas->fld($result['created_at']);
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                            }
                            $payment_id = $this->db->insert_id();
                        } else {
                            if ($payment['paid_by'] == 'gift_card') {
                                // $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
                                $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                                $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
                                unset($payment['gc_balance']);
                            } elseif ($payment['paid_by'] == 'deposit') {
                                $customer = $this->site->getCompanyByID($data['customer_id']);
                                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $payment['amount'])], ['id' => $customer->id]);
                            }
                            unset($payment['cc_cvv2']);
                            $this->db->insert('payments', $payment);
                            $payment_id = $this->db->insert_id();
                            $this->site->updateReference('pay');
                            $paid += $payment['amount'];
                        }
                        //----------accounting-----
                        if ($accTranPayments) {
                            foreach($accTranPayments as $accTranPayment){
                                $accTranPayment['tran_no'] = $payment_id;
                                $accTranPayment['reference_no'] = $payment['reference_no'];
                                $this->db->insert('gl_trans', $accTranPayment);
                            }
                        }
                    }
                }
                $this->site->syncSalePayments($sale_id);
            }
            if ($this->pos_settings != 1 && $data['grand_total'] != 0) {
                $inv = $this->getInvoiceByID($sale_id);
                if ($inv->paid < $inv->grand_total) {
                    $this->session->set_flashdata('error', 'Please check payment.');
                    redirect($_SERVER['HTTP_REFERER']);    
                }
            }
            $this->site->sendTelegram("sale",$sale_id,"added");
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Pos_model.php)');
        } else {
            return ['sale_id' => $sale_id, 'message' => $msg];
        }
        return false;
    }

    public function addSale_02_10_2023($data = [], $items = [], $items_addon = [],  $payments = [], $sid = null,$accTrans = array(), $accTranPayments = array(), $note_id = null)
    {
        $this->db->trans_start();
        $all_items = array_merge($items, $items_addon);
        $cost = $this->site->costing($all_items);
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            //=======Add accounting =======//
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //=======End accounting =======//
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $row_id = $item['row_id'];
                unset($item['row_id']);
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                $combo_items = $this->getProductComboItems($item['product_id'], $data['warehouse_id']); 
                if (!empty($combo_items)) {
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') { 
                            $item_combo = array(
                                'sale_product_id'   => $item['product_id'],
                                'sale_id'           => $sale_id,
                                'sale_item_id'      => $sale_item_id,
                                'product_id'        => $combo_item->id,
                                'product_code'      => $combo_item->code,
                                'product_name'      => $combo_item->name,
                                'product_type'      => $combo_item->type,
                                'warehouse_id'      => $data['warehouse_id'],
                                'quantity'          => $combo_item->qty * $item['quantity'],
                                'net_unit_price'    => $combo_item->unit_price,
                                'unit_price'        => $combo_item->unit_price,
                                'currency'          => 'usd',
                                'tax_rate'          => null,
                                'option_id'         => null,
                                'subtotal'          => $combo_item->unit_price * $item['quantity']);
                            $this->db->insert('sale_combo_items', $item_combo);
                        }
                    }
                }
                foreach ($items_addon as $item_addon) {
                    $item_addon['sale_id'] = $sale_id;
                    if($row_id == $item_addon['addon_row_id']){
                        $item_addon['sale_item_id'] = $sale_item_id;
                        unset($item_addon['addon_row_id']);
                        $this->db->insert('sale_addon_items', $item_addon);
                        if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item_addon['product_id'])) {
                            $item_costs = $this->site->item_costing($item_addon);
                            foreach ($item_costs as $item_cost) {
                                if ($pi = $this->site->getPurchaseItemByID($item_cost['purchase_item_id'])) {
                                    $item_cost['expiry'] = $pi->expiry;
                                }
                                if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                                    $item_cost['sale_item_id'] = $sale_item_id;
                                    $item_cost['sale_id']      = $sale_id;
                                    $item_cost['date']         = date('Y-m-d', strtotime($data['date']));
                                    if (!isset($item_cost['pi_overselling'])) {
                                        $this->db->insert('costing', $item_cost);
                                    }
                                } else {
                                    foreach ($item_cost as $ic) {
                                        $ic['sale_item_id'] = $sale_item_id;
                                        $ic['sale_id']      = $sale_id;
                                        $ic['date']         = date('Y-m-d', strtotime($data['date']));
                                        if (!isset($ic['pi_overselling'])) {
                                            $this->db->insert('costing', $ic);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($data['sale_status'] == 'completed' && $this->site->getProductByID($item['product_id'])) {
                    $item_costs = $this->site->item_costing($item);
                    // $this->bpas->print_arrays($item_costs);
                    foreach ($item_costs as $item_cost) {
                        if ($pi = $this->site->getPurchaseItemByID($item_cost['purchase_item_id'])) {
                            $item_cost['expiry'] = $pi->expiry;
                        }
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $sale_id;
                            $item_cost['date']         = date('Y-m-d', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $sale_id;
                                $ic['date']         = date('Y-m-d', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }
            if ($data['sale_status'] == 'completed') {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncQuantity($sale_id);
            if ($sid) {
                $this->deleteBill($sid, $note_id);
            }
            $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            $this->site->updateReference('pos');
            $this->site->updateReference('bill');
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Pos_model.php)');
        } else {
            $msg = [];
            if (!empty($payments)) {
                $paid = 0;
                foreach ($payments as $payment) {
                    if (!empty($payment) && isset($payment['amount'])) {
                        $payment['sale_id']      = $sale_id;
                        $payment['reference_no'] = $this->site->getReference('pay');
                        $payment_id = -1;
                        if ($payment['paid_by'] == 'ppp') {
                            $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                            $result    = $this->paypal($payment['amount'], $card_info, '', $sale_id);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date']           = $this->bpas->fld($result['created_at']);
                                $payment['amount']         = $result['amount'];
                                $payment['currency']       = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $payment_id = $this->db->insert_id();
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                if (!empty($result['message'])) {
                                    foreach ($result['message'] as $m) {
                                        $msg[] = '<p class="text-danger">' . $m['L_ERRORCODE'] . ': ' . $m['L_LONGMESSAGE'] . '</p>';
                                    }
                                } else {
                                    $msg[] = lang('paypal_empty_error');
                                }
                            }
                            $payment_id = $this->db->insert_id();
                        } elseif ($payment['paid_by'] == 'stripe') {
                            $card_info = ['number' => $payment['cc_no'], 'exp_month' => $payment['cc_month'], 'exp_year' => $payment['cc_year'], 'cvc' => $payment['cc_cvv2'], 'type' => $payment['cc_type']];
                            $result    = $this->stripe($payment['amount'], $card_info);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['date']           = $this->bpas->fld($result['created_at']);
                                $payment['amount']         = $result['amount'];
                                $payment['currency']       = $result['currency'];
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $payment_id = $this->db->insert_id();
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['code'] . ': ' . $result['message'] . '</p>';
                            }
                            $payment_id = $this->db->insert_id();
                        } elseif ($payment['paid_by'] == 'authorize') {
                            $authorize_arr                 = ['x_card_num' => $payment['cc_no'], 'x_exp_date' => ($payment['cc_month'] . '/' . $payment['cc_year']), 'x_card_code' => $payment['cc_cvv2'], 'x_amount' => $payment['amount'], 'x_invoice_num' => $sale_id, 'x_description' => 'Sale Ref ' . $data['reference_no'] . ' and Payment Ref ' . $payment['reference_no']];
                            list($first_name, $last_name)  = explode(' ', $payment['cc_holder'], 2);
                            $authorize_arr['x_first_name'] = $first_name;
                            $authorize_arr['x_last_name']  = $last_name;
                            $result                        = $this->authorize($authorize_arr);
                            if (!isset($result['error'])) {
                                $payment['transaction_id'] = $result['transaction_id'];
                                $payment['approval_code']  = $result['approval_code'];
                                $payment['date']           = $this->bpas->fld($result['created_at']);
                                unset($payment['cc_cvv2']);
                                $this->db->insert('payments', $payment);
                                $this->site->updateReference('pay');
                                $paid += $payment['amount'];
                            } else {
                                $msg[] = lang('payment_failed');
                                $msg[] = '<p class="text-danger">' . $result['msg'] . '</p>';
                            }
                            $payment_id = $this->db->insert_id();
                        } else {
                            if ($payment['paid_by'] == 'gift_card') {
                                // $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
                                $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                                $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
                                unset($payment['gc_balance']);
                            } elseif ($payment['paid_by'] == 'deposit') {
                                $customer = $this->site->getCompanyByID($data['customer_id']);
                                $this->db->update('companies', ['deposit_amount' => ($customer->deposit_amount - $payment['amount'])], ['id' => $customer->id]);
                            }
                            unset($payment['cc_cvv2']);
                            $this->db->insert('payments', $payment);
                            $payment_id = $this->db->insert_id();
                            $this->site->updateReference('pay');
                            $paid += $payment['amount'];
                        }
                        //----------accounting-----
                        if($accTranPayments){
                            foreach($accTranPayments as $accTranPayment){
                                $accTranPayment['tran_no'] = $payment_id;
                                $accTranPayment['reference_no'] = $payment['reference_no'];
                                $this->db->insert('gl_trans', $accTranPayment);
                            }
                        }
                    }
                }
                $this->site->syncSalePayments($sale_id);
            }
            return ['sale_id' => $sale_id, 'message' => $msg];
        }
        return false;
    }

    public function authorize($authorize_data)
    {
        $this->load->library('authorize_net');
        // $authorize_data = array( 'x_card_num' => '4111111111111111', 'x_exp_date' => '12/20', 'x_card_code' => '123', 'x_amount' => '25', 'x_invoice_num' => '15454', 'x_description' => 'References');
        $this->authorize_net->setData($authorize_data);
        if ($this->authorize_net->authorizeAndCapture()) {
            $result = [
                'transaction_id' => $this->authorize_net->getTransactionId(),
                'approval_code'  => $this->authorize_net->getApprovalCode(),
                'created_at'     => date($this->dateFormats['php_ldate']),
            ];
            return $result;
        } else {
            return ['error' => 1, 'msg' => $this->authorize_net->getError()];
        }
    }

    public function bills_count()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        return $this->db->count_all_results('suspended_bills');
    }

    public function closeRegister($rid, $user_id, $data)
    {
        if (!$rid) {
            $rid = $this->session->userdata('register_id');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        if ($data['transfer_opened_bills'] == -1) {
            $this->db->delete('suspended_bills', ['created_by' => $user_id]);
        } elseif ($data['transfer_opened_bills'] != 0) {
            $this->db->update('suspended_bills', ['created_by' => $data['transfer_opened_bills']], ['created_by' => $user_id]);
        }
        if ($this->db->update('pos_register', $data, ['id' => $rid, 'user_id' => $user_id])) {
            return true;
        }
        return false;
    }   

    public function addTmpSuspendItem($array = [], $s_items = [])
    {    
        $data = array();
        $this->db->select('*,COUNT(unit_quantity) as unit_qty,COUNT(product_id)');
        $this->db->group_by('product_id');
        $this->db->having('COUNT(product_id) >= 1');
        $q = $this->db->get('split_items');
        if ($q->num_rows() > 0) {
             foreach (($q->result()) as $row) {
                // $data[] = $row;
                $data[] = array(
                     'suspend_id'          => $row->suspend_id,
                     'quantity'            => $row->unit_qty,
                     'unit_quantity'       => $row->unit_qty,
                     'product_id'          => $row->product_id,
                     'product_code'        => $row->product_code,
                     'product_name'        => $row->product_name,
                     'net_unit_price'      => $row->net_unit_price,
                     'unit_price'          => $row->unit_price,
                     'subtotal'            => $row->subtotal,
                     'real_unit_price'     => $row->real_unit_price,
                     'comment'             => $row->comment,
                     'product_unit_code'   => $row->product_unit_code,
                     'product_unit_id'     => $row->product_unit_id,
                     'product_type'        => $row->product_type,
                     'option_id'           => $row->option_id,
                     'serial_no'           => $row->serial_no,
                     'item_discount'       => $row->item_discount,
                     'discount'            => $row->discount,
                     'tax'                 => $row->tax,
                     'tax_rate_id'         => $row->tax_rate_id,
                     'free'                => $row->free,
                     'item_tax'            => $row->item_tax,
                     'warehouse_id'        => $row->warehouse_id,
                     'product_second_name' => $row->product_second_name,
                     'gst'                 => $row->gst,
                     'cgst'                => $row->cgst,
                     'sgst'                => $row->sgst,
                     'igst'                => $row->igst,
                     'weight'              => $row->weight,
                     'total_weight'        => ($row->weight * $row->unit_qty),
                     'row_id'              => $row->row_id,
                );
            }
        }
        $bool = false;
        foreach ($data as $value) {
            $this->db->insert('suspended_items', $value);
            $suspend_id      = $value['suspend_id'];
            $suspend_item_id = $this->db->insert_id();
            foreach ($s_items as $item) {
                if($item['row_id'] == $value['row_id']){
                    $this->db->select('*');
                    $this->db->where('suspend_id', $item['suspend_id']);  
                    $this->db->where('suspend_item_id', $item['id']);  
                    $query = $this->db->get('suspended_addon_items');
                    if ($query->num_rows() > 0) {
                        foreach (($query->result()) as $result) {
                            if($item['unit_quantity'] == 1){
                                $this->db->update('suspended_addon_items', ['suspend_id' => $suspend_id, 'suspend_item_id' => $suspend_item_id], ['id' => $result->id]);
                            } else {
                                $addon_item = array(
                                    'suspend_id'         => $suspend_id,
                                    'suspend_item_id'    => $suspend_item_id,
                                    'suspend_product_id' => $result->suspend_product_id,
                                    'product_id'         => $result->product_id,
                                    'product_code'       => $result->product_code,
                                    'product_name'       => $result->product_name,
                                    'product_type'       => $result->product_type,
                                    'warehouse_id'       => $result->warehouse_id,
                                    'quantity'           => $result->quantity,
                                    'net_unit_price'     => $result->net_unit_price,
                                    'unit_price'         => $result->unit_price,
                                    'tax_rate'           => $result->tax_rate,
                                    'currency'           => $result->currency,
                                    'subtotal'           => $result->subtotal,
                                    'option_id'          => $result->option_id,
                                    'addon_row_id'       => $result->addon_row_id);
                                $this->db->insert('suspended_addon_items', $addon_item);
                            }
                        }
                    }
                }
            }
            $bool = true;
        }
        if ($bool) {
            $this->db->truncate('split_items');
            return true;
        }
        return false;
    }

    public function get_suspendNoteTmp($id)
    {
        $this->db->select();
		$q = $this->db->get_where('suspended_note',array('note_id'=>$id,'tmp'=>'1'));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteBill($id, $note_id = null)
    {
        $tmp = $this->get_suspendNoteTmp($note_id); 
        if($tmp != null){
            $this->db->delete('suspended_note', ['note_id' => $tmp->note_id]);

        }
        if ($this->db->delete('suspended_items', ['suspend_id' => $id]) && 
            $this->db->delete('suspended_bills', ['id' => $id])) {
          
                return true;
        }

        return false;
    }

    public function deletePrinter($id)
    {
        if ($this->db->delete('printers', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function fetch_bills($limit, $start)
    {
        if (!$this->Owner && !$this->Admin) {
            $this->db->where('created_by', $this->session->userdata('user_id'));
        }
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'asc');
        $query = $this->db->get('suspended_bills');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function fetch_sales($limit, $start)
    {
        $this->db->limit($limit, $start);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('sales');

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllBillerCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'biller']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getidmembercard($card_no)
    {
        $q = $this->db->get_where('member_cards', ['card_no' => $card_no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getidcoupon($card_no)
    {
        $q = $this->db->get_where('coupon', ['card_no' => $card_no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllCustomerCompanies()
    {
        $q = $this->db->get_where('companies', ['group_name' => 'customer']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

   

    public function getAllPrinters()
    {
        $q = $this->db->get('printers');
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->query('SELECT * FROM products ORDER BY id');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getAllSales()
    {
        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
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

    public function getCosting()
    {
        $date = date('Y-m-d');
        $this->db->select('SUM( COALESCE( purchase_unit_cost, 0 ) * quantity ) AS cost, SUM( COALESCE( sale_unit_price, 0 ) * quantity ) AS sales, SUM( COALESCE( purchase_net_unit_cost, 0 ) * quantity ) AS net_cost, SUM( COALESCE( sale_net_unit_price, 0 ) * quantity ) AS net_sales', false)
            ->where('date', $date);

        $q = $this->db->get('costing');
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

    public function getInvoiceByIDTax($id)
    {
        $this->db->select('sales.*,tax_rates.name as tax_name')
        ->join('tax_rates', 'tax_rates.id = sales.order_tax_id', 'left');

        $q = $this->db->get_where('sales', ['sales.id' => $id]);

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllInvoiceItems($sale_id)
    {
        if ($this->pos_settings->item_order == 0) {
            $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name,categories.name as category_name, tax_rates.rate as tax_rate,product_variants.name as variant, products.details as details, 
                products.hsn_code as hsn_code,
                IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
                products.second_name as second_name
            ')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('id', 'asc');
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('sale_items.id')
            ->order_by('categories.id', 'asc');
        }
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllInvoiceItemsGroup($sale_id)
    {
        if ($this->pos_settings->item_order == 0) {
            $this->db->select("
                sale_items.*, 
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                categories.name as category_name, product_variants.name as variant, products.details as details, products.hsn_code as hsn_code,
                IF({$this->db->dbprefix('products')}.currency = 'KHR', '៛', '$') as currency,
                products.second_name as second_name,
                SUM({$this->db->dbprefix('sale_items')}.quantity) AS quantity,
                SUM({$this->db->dbprefix('sale_items')}.unit_quantity) AS unit_quantity,
                SUM({$this->db->dbprefix('sale_items')}.item_tax) AS item_tax,
                SUM({$this->db->dbprefix('sale_items')}.item_discount) AS item_discount,
                SUM({$this->db->dbprefix('sale_items')}.subtotal) AS subtotal
            ")
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->group_by('sale_items.product_id, sale_items.product_unit_code, sale_items.net_unit_price, sale_items.option_id, sale_items.square, (IF('.$this->db->dbprefix('sale_items').'.quantity>0,"sale","return"))')
            ->order_by('id', 'asc');
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select("
                sale_items.*, 
                tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details, products.hsn_code as hsn_code, 
                products.second_name as second_name,
                SUM({$this->db->dbprefix('sale_items')}.quantity) AS quantity,
                SUM({$this->db->dbprefix('sale_items')}.unit_quantity) AS unit_quantity,
                SUM({$this->db->dbprefix('sale_items')}.item_tax) AS item_tax,
                SUM({$this->db->dbprefix('sale_items')}.item_discount) AS item_discount,
                SUM({$this->db->dbprefix('sale_items')}.subtotal) AS subtotal
            ")
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->group_by('sale_items.product_id, sale_items.product_unit_code, sale_items.net_unit_price, sale_items.option_id, sale_items.square, (IF('.$this->db->dbprefix('sale_items').'.quantity>0,"sale","return"))')
            ->order_by('categories.id', 'asc');
        }
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getStockTypeByID($id)
    {
        $q = $this->db->get_where('stock_type', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }
    public function getInvoicePayments($sale_id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getOpenBillByID($id)
    {
        $q = $this->db->get_where('suspended_bills', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getOpenRegisters()
    {
        $this->db->select('date, user_id, cash_in_hand, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' - ', " . $this->db->dbprefix('users') . '.email) as user', false)
            ->join('users', 'users.id=pos_register.user_id', 'left');
        $q = $this->db->get_where('pos_register', ['status' => 'open']);
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPrinterByID($id)
    {
        $q = $this->db->get_where('printers', ['id' => $id], 1);
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
    public function getProductComboItems($pid, $warehouse_id)
    {
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
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if (!$this->Settings->overselling && !$all) {
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

    public function getProductsByCode($code)
    {
        $this->db->like('code', $code, 'both')->order_by('code');
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterAuthorizeSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'authorize');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterABASales($date, $user_id = null,$type_of_payment = null)
    {   
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id = payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('sales.date >', $date)
            ->where('payments.date >', $date)
            ->where('paid_by', $type_of_payment);
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCashAccountByBank($date, $user_id = null){
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('
            '.$this->db->dbprefix("cash_accounts").'.code,
            '.$this->db->dbprefix("cash_accounts").'.name,
            SUM( COALESCE( grand_total, 0 ) ) AS total, 
            SUM( COALESCE( amount, 0 ) ) AS paid', false)


        ->join('payments', 'payments.paid_by=cash_accounts.code', 'left')
        ->join('sales', 'sales.id = payments.sale_id', 'left');

        $this->db->where('payments.created_by', $user_id);
        $this->db->where('payments.type', 'received');
        $this->db->where('sales.date >', $date);
        $this->db->where('payments.date >', $date);
        $this->db->group_by('cash_accounts.code');

        $this->db->order_by("order");
        $q = $this->db->get("cash_accounts");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getRegisterTotalTrans($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('sales') . '.id) as total_trans', false)
            //->join('payments', 'sales.id=payments.sale_id', 'left')
            ->where('date >', $date)->where('created_by', $user_id);

        $q = $this->db->get('sales');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCashRefunds($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('payments.type', 'returned')->where('payments.date >', $date)->where('paid_by', 'cash');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCashSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'cash');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterCCSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')
            ->where('payments.date >', $date)
            ->where('paid_by', 'CC');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterChSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'Cheque');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterExpenses($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false)
            ->where('date >', $date);
        $this->db->where('created_by', $user_id);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterGCSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'gift_card');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterPPPSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'ppp');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterRefunds($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('payments.type', 'returned')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterReturns($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
        ->where('date >', $date)
        ->where('returns.created_by', $user_id);

        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
      $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total,SUM(order_discount) AS discount,SUM(order_tax) AS tax, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterStripeSales($date, $user_id = null)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'stripe');
        $this->db->where('payments.created_by', $user_id);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleItems($id)
    {
        $q = $this->db->get_where('sale_items', ['sale_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSettingByID($id)
    {
        $this->db->where('id', $id);
        $q = $this->db->get('pos_settings');
        return $q->result();
    }
    public function getSuspendedSaleItems($id)
    {
         $this->db->where('suspend_id', $id);
        $this->db->order_by('product_id');
        $q = $this->db->get('suspended_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getSuspendedSales($user_id = null)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('suspended_bills', ['created_by' => $user_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getTodayAuthorizeSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'authorize');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayCashRefunds()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('payments.type', 'returned')->where('payments.date >', $date)->where('paid_by', 'cash');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayCashSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'cash');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayCCSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'CC');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayChSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'Cheque');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayExpenses()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( amount, 0 ) ) AS total', false)
            ->where('date >', $date);

        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayPPPSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'ppp');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayRefunds()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS returned', false)
            ->join('sales', 'sales.id=payments.return_id', 'left')
            ->where('payments.type', 'returned')->where('payments.date >', $date);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayReturns()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total', false)
            ->where('date >', $date);

        $q = $this->db->get('returns');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodaySales()
    {
        $sdate = date('Y-m-d 00:00:00');
        $edate = date('Y-m-d 23:59:59');
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('sales.date >=', $sdate)->where('payments.date <=', $edate);

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getTodayStripeSales()
    {
        $date = date('Y-m-d 00:00:00');
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', false)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('payments.type', 'received')->where('payments.date >', $date)->where('paid_by', 'stripe');

        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUsers()
    {
        $q = $this->db->get_where('users', ['company_id' => null]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWHProduct($code, $warehouse_id)
    {
        $this->db->select('products.*, warehouses_products.quantity, categories.id as category_id, categories.name as category_name')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
            ->where('hide_pos !=', 1)
            ->group_by('products.id');
        $q = $this->db->get_where('products', ['products.code' => $code]);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->insert('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
            return true;
        }
        return false;
    }

    public function openRegister($data)
    {
        if ($this->db->insert('pos_register', $data)) {
            return true;
        }
        return false;
    }

    public function paypal($amount = null, $card_info = [], $desc = '', $sale_id = null)
    {
        $this->load->admin_model('paypal_payments');
        //$card_info = array( "number" => "5522340006063638", "exp_month" => 2, "exp_year" => 2016, "cvc" => "456", 'type' => 'MasterCard' );
        //$amount = $amount ? $amount : 30.00;
        if ($amount && !empty($card_info)) {
            $data = $this->paypal_payments->Do_direct_payment($amount, $this->default_currency->code, $card_info, $desc, $sale_id);
            if (!isset($data['error'])) {
                $result = ['transaction_id' => $data['TRANSACTIONID'],
                    'created_at'            => date($this->dateFormats['php_ldate'], strtotime($data['TIMESTAMP'])),
                    'amount'                => $data['AMT'],
                    'currency'              => strtoupper($data['CURRENCYCODE']),
                ];
                return $result;
            } else {
                return $data;
            }
        }
        return false;
    }

    public function products_count($category_id, $subcategory_id = null, $brand_id = null, $warehouse_id = null, $term_product = null, $term_category = null)
    {
        if ($warehouse_id) {
            $this->db->select(" {$this->db->dbprefix('products')}.* ");
            $this->db->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
            $this->db->group_by('warehouses_products.product_id');
        } else {
            $this->db->select(" {$this->db->dbprefix('products')}.* ");
        }
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        if ($brand_id) {
            $this->db->where('brand', $brand_id);
        }
        if ($term_product) {
            $this->db->group_start();
            $this->db->like("LOWER({$this->db->dbprefix('products')}.code)", strtolower($term_product), 'both');
            $this->db->or_like("LOWER({$this->db->dbprefix('products')}.name)", strtolower($term_product), 'both');
            $this->db->group_end();
        }
        if ($term_category) {
            $this->db->group_start();
            $this->db->like("LOWER({$this->db->dbprefix('categories')}.code)", strtolower($term_category), 'both');
            $this->db->or_like("LOWER({$this->db->dbprefix('categories')}.name)", strtolower($term_category), 'both');
            $this->db->group_end();
            $this->db->join('categories', 'categories.id = products.category_id', 'inner');
        }
        $this->db->where('hide_pos !=', 1);
        $this->db->where("{$this->db->dbprefix('products')}.type !=", 'asset');
        $this->db->from('products');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = null, $brand_id = null, $warehouse_id = null, $term_product = null, $term_category = null)
    {
        $this->db->limit($limit, $start);
        if ($warehouse_id) {
            $this->db->select(" {$this->db->dbprefix('products')}.*, {$this->db->dbprefix('warehouses_products')}.quantity AS quantity ");
            $this->db->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left');
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
            $this->db->group_by('warehouses_products.product_id');
        } else {
            $this->db->select(" {$this->db->dbprefix('products')}.* ");
        }
        if ($brand_id) {
            $this->db->where('brand', $brand_id);
        } elseif ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        if ($term_product) {
            $this->db->group_start();
            $this->db->like("LOWER({$this->db->dbprefix('products')}.code)", strtolower($term_product), 'both');
            $this->db->or_like("LOWER({$this->db->dbprefix('products')}.name)", strtolower($term_product), 'both');
            $this->db->group_end();
        }
        if ($term_category) {
            $this->db->group_start();
            $this->db->like("LOWER({$this->db->dbprefix('categories')}.code)", strtolower($term_category), 'both');
            $this->db->or_like("LOWER({$this->db->dbprefix('categories')}.name)", strtolower($term_category), 'both');
            $this->db->group_end();
            $this->db->join('categories', 'categories.id = products.category_id', 'inner');
        }
        $this->db->where('hide_pos !=', 1);
        $this->db->where("{$this->db->dbprefix('products')}.type !=", 'asset');
        $this->db->order_by('name', 'asc');
        $query = $this->db->get('products');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function registerDataByID($id)
    {
       
        $q = $this->db->get_where('pos_register', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function registerData($user_id)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('pos_register', ['user_id' => $user_id, 'status' => 'open'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function sales_count()
    {
        return $this->db->count_all('sales');
    }

    public function stripe($amount = 0, $card_info = [], $desc = '')
    {
        $this->load->admin_model('stripe_payments');
        //$card_info = array( "number" => "4242424242424242", "exp_month" => 1, "exp_year" => 2016, "cvc" => "314" );
        //$amount = $amount ? $amount*100 : 3000;
        unset($card_info['type']);
        $amount = $amount * 100;
        if ($amount && !empty($card_info)) {
            $token_info = $this->stripe_payments->create_card_token($card_info);
            if (!isset($token_info['error'])) {
                $token = $token_info->id;
                $data  = $this->stripe_payments->insert($token, $desc, $amount, $this->default_currency->code);
                if (!isset($data['error'])) {
                    $result = ['transaction_id' => $data->id,
                        'created_at'            => date($this->dateFormats['php_ldate'], $data->created),
                        'amount'                => ($data->amount / 100),
                        'currency'              => strtoupper($data->currency),
                    ];
                    return $result;
                } else {
                    return $data;
                }
            } else {
                return $token_info;
            }
        }
        return false;
    }

    public function suspendSale($data = [], $items = [], $items_addon = [], $did = null)
    {
        $sData = [
            'count'             => $data['total_items'],
            'biller_id'         => $data['biller_id'],
            'customer_id'       => $data['customer_id'],
            'warehouse_id'      => $data['warehouse_id'],
            'customer'          => $data['customer'],
            'date'              => $data['date'],
            'suspend_note'      => $data['suspend_note'],
            'total'             => $data['grand_total'],
            'order_tax_id'      => $data['order_tax_id'],
            'order_discount_id' => $data['order_discount_id'],
            'created_by'        => $this->session->userdata('user_id'),
        ];

        if ($did) {
            if ($this->db->update('suspended_bills', $sData, ['id' => $did]) && $this->db->delete('suspended_items', ['suspend_id' => $did]) && $this->db->delete('suspended_addon_items', ['suspend_id' => $did])) {
                $addOn = ['suspend_id' => $did];
                end($addOn);
                 //------
                $this->db->select('bill_status');
                $this->db->where('id', $did);
                $q = $this->db->get('suspended_bills');
                if($q->num_rows()) {
                    $q = $q->row();
                    if($q->bill_status < 1){
                        $this->site->updateReference('bill');
                        $Bdata = array('bill_status'  => 1);
                            $this->db->where(array('id'=> $did));
                            $this->db->update("suspended_bills", $Bdata);
                    }
                }
                //-----
            
                foreach ($items as &$var) {
                    $var = array_merge($addOn, $var);
                }

                if(!empty($items_addon)){
                    foreach ($items_addon as &$var) {
                        $var = array_merge($addOn, $var);
                    }
                }
                
                $bool = false;
                foreach ($items as $item) {
                    $this->db->insert('suspended_items', $item);
                    $suspended_item_id = $this->db->insert_id();
                    if($items_addon){
                        foreach ($items_addon as $item_addon) {
                            if($item['row_id'] == $item_addon['addon_row_id']){
                                $item_addon['suspend_item_id'] = $suspended_item_id;
                                $this->db->insert('suspended_addon_items', $item_addon);
                            }
                        }
                    }
                    $bool = true;
                }
                if($bool){
                    return true;
                }
            }
        } else {
            if ($this->db->insert('suspended_bills', $sData,1)) {
                $suspend_id = $this->db->insert_id();
                $addOn      = ['suspend_id' => $suspend_id];
                end($addOn);
                foreach ($items as &$var) {
                    $var = array_merge($addOn, $var);
                }
               $bool = false;
                foreach ($items as $item) {
                    $this->db->insert('suspended_items', $item);
                    $suspended_item_id = $this->db->insert_id();
                    if($items_addon){
                        foreach ($items_addon as $item_addon) {
                            $item_addon['suspend_id'] = $suspend_id;
                            if($item['row_id'] == $item_addon['addon_row_id']){
                                $item_addon['suspend_item_id'] = $suspended_item_id;
                                $this->db->insert('suspended_addon_items', $item_addon);
                            }
                        }
                    }
                    $bool = true;
                }
                if($bool){
                    return true;
                }
                $this->site->updateReference('bill');
            }
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

    public function updatePrinter($id, $data = [])
    {
        if ($this->db->update('printers', $data, ['id' => $id])) {
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

    public function updateProductQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->addQuantity($product_id, $warehouse_id, $quantity)) {
            return true;
        }

        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->update('warehouses_products', ['quantity' => $quantity], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id])) {
            return true;
        }
        return false;
    }

    public function updateSetting($data)
    {
        $this->db->where('pos_id', '1');
        if ($this->db->update('pos_settings', $data)) {
            return true;
        }
        return false;
    }
    //-----------POS--
    public function get_biller_by_user($user_id){
        $this->db->select("address")
            ->join('users', 'users.biller_id=companies.id');
        $q = $this->db->get_where('companies', array('users.id' => $user_id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
    public function check_biller($bill_id,$user_id){
        $this->db->select("reference");
        $q = $this->db->get_where('audit_bill', array(
            'reference' => $bill_id,
            'user_id'=>$user_id
            ));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
    public function check_biller_order($bill_id,$user_id){
        $this->db->select("reference");
        $q = $this->db->get_where('audit_order', array(
            'reference' => $bill_id,
            'user_id'=>$user_id
            ));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
    public function getStockType($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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
    
    function exchange_rate()
    {
        $q = $this->db->get('currencies', array('code' => 'KHR'), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    /*** Update Room Price based on minutely rate ****/
  
    public function updateRoomPriceMinutely($param)
    {
        $startedTime = $this->db->get_where('suspended_bills', array('id' => $param))->row()->start_date;
        $getStartedTime = strtotime($startedTime);
        $getEndedTime = strtotime('now');
        $q = $this->db->get_where('suspended_items', array('suspend_id' => $param), 1);
        if ($q->num_rows() > 0) {
                $data = $q->row();
          $per_hour=$data->net_unit_price;
            }else{
          $per_hour=0;
        }
        $timeTaken = abs(($getEndedTime - $getStartedTime) / (60*60));
        $this->db->where(array('suspend_id' => $param, 'product_code' => 'Time'));
        if($this->db->update('suspended_items' , array(
            'quantity' => $timeTaken,
            'subtotal' => ($per_hour * $timeTaken),
            'unit_quantity' => $timeTaken
            ))){
          return true;
        }
        return false;
    }

     public function getAddOnItemsByPID($id){

        $q = $this->db->get_where('addon_items', ['product_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
    }

    public function getAllP(){
        $q = $this->db->get_where('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
    }

    public function getAddonitemsNote($id, $row_id){
        $q = $this->db->get_where('addon_items_note', ['suspend_id' => $id, 'row_id' => $row_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false; 
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

    public function addMarking($id) 
    {
        $this->db->update('sales', ['produce_status' => 'making'], ['id' => $id]);
        return true;
    }

     public function getSuspendedSaleAddOnItems($id)
    {
        $this->db->where('suspend_id', $id);
        $this->db->order_by('suspend_product_id');
        $q = $this->db->get('suspended_addon_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getSuspendedSaleAddOnItemsByItemRowID($item_row_id)
    {
        $this->db->where('item_row_id', $item_row_id);
        $this->db->order_by('item_row_id');
        $q = $this->db->get('suspended_addon_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getComboItems($item_id){
        $this->db->select("products.name as product_name,combo_items.quantity as quantity")
            ->join('products', 'products.code = combo_items.item_code');
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $item_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;

    }
    function getAuditOrderItemsByItemRowID($item_row_id)
    {
        $q = $this->db->get_where('audit_order_item', array('item_row_id' => $item_row_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    function getAuditBillItemByReference($reference)
    {
        $this->db->order_by('print_index');
        $q = $this->db->get_where('audit_bill_item', array('reference' => $reference));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAllCustomerStockPendings()
    {
        $date = date('Y-m-d');
        $this->db->select('COUNT(id) as alert_num')
                 ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
                 ->where('expiry >', $date)
                 ->where('status', 'pending');
                 
        $q = $this->db->get('customer_stocks');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return $res->alert_num;
        }
        return FALSE;
    }
    public function addCustomerStockExpired()
    {
        $date = date('Y-m-d');
        $this->db->select('id')
                 ->where('expiry !=', NULL)->where('expiry !=', '0000-00-00')
                 ->where('expiry <', $date);
        $q = $this->db->get('customer_stocks');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $this->db->update("customer_stocks", array("status"=>"expired"), array("id"=>$row->id));
            }
            return true;
        }
        return FALSE;
    }
    public function addCustomerStock($data = false, $items = false, $stockmoves = false, $accTrans = NULL, $product_serials = NULL)
    {
        if($this->db->insert("customer_stocks", $data)){
            
            if($product_serials){
                foreach($product_serials as $product_serial){
                    $product_serial['adjustment_id'] = $adjustment_id;
                    $this->db->insert('product_serials', $product_serial);
                }
            }
            
            if($items){
                $customer_stock_id = $this->db->insert_id();
                foreach($items as $item){
                    $item['customer_stock_id'] = $customer_stock_id;
                    $this->db->insert("customer_stock_items", $item);
                }
            }
            if($stockmoves){
                foreach($stockmoves as $stockmove){
                    $stockmove['transaction_id'] = $customer_stock_id;
                    $this->db->insert('stock_movement', $stockmove);
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $customer_stock_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            return true;
        }
        return false;
    }
    
    public function updateCustomerStock($id = false, $data = false, $items = false, $stockmoves = false, $accTrans = NULL, $product_serials = NULL)
    {
        if($this->db->update("customer_stocks", $data, array("id"=>$id))){
            
            if($product_serials){
                foreach($product_serials as $product_serial){
                    $product_serial['adjustment_id'] = $id;
                    $this->db->insert('product_serials', $product_serial);
                }
            }
            
            if($items){
                $this->db->delete("customer_stock_items", array("customer_stock_id"=>$id));
                foreach($items as $item){
                    $item['customer_stock_id'] = $id;
                    $this->db->insert("customer_stock_items", $item);
                }
            }
            if($stockmoves){
                $this->db->delete("stock_movement", array("transaction_id"=>$id));
                foreach($stockmoves as $stockmove){
                    $stockmove['transaction_id'] = $id;
                    $this->db->insert('stock_movement', $stockmove);
                }
            }
            if($accTrans){
                $this->db->delete("gl_trans", array("tran_no"=>$id));
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            return true;
        }
        return false;
    }
    public function deleteCustomerStock($id = false)
    {
        if($this->db->delete("customer_stocks", array("id"=>$id))){
            $this->db->delete("customer_stock_items", array("customer_stock_id" => $id));
            
            $this->db->delete("stock_movement", array("transaction_id" => $id, "transaction" => "CustomerStock"));
            $this->db->delete("stock_movement", array("transaction_id" => $id, "transaction" => "CustomerStockReturn"));
            
            $this->db->delete("gl_trans", array("tran_no" => $id, "tran_type" => "CustomerStock"));
            $this->db->delete("gl_trans", array("tran_no" => $id, "tran_type" => "CustomerStockReturn"));
            
            return true;
        }
        return false;
    }
    public function getCustomerStockByID($id = false)
    {
        $q = $this->db->select("customer_stocks.*, GROUP_CONCAT(bpas_products.name) as description")
                    ->join("customer_stock_items","customer_stock_id=customer_stocks.id","left")
                    ->join("products","product_id=products.id","left")
                    ->group_by("customer_stocks.id")
                    ->where(array("customer_stocks.id"=>$id))
                    ->get("customer_stocks");
                    
        if($q->num_rows() >0){
            return $q->row();
        }
        return false;
    }
    public function getCustomerStockItems($id = false)
    {
        $this->db->select('customer_stock_items.*, products.code as product_code, products.name as product_name, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=customer_stock_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=customer_stock_items.option_id', 'left')
            ->group_by('customer_stock_items.id')
            ->order_by('id', 'asc');

        $this->db->where('customer_stock_id', $id);
        $q = $this->db->get('customer_stock_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function returnCustomerStock($id = false, $stockmoves = NULL, $accTrans = NULL)
    {
        if($this->db->update("customer_stocks",array("status" => "returned", "returned_at" => date("Y-m-d H:i") , "returned_by"=> $this->session->userdata("user_id")),array("id"=>$id))){
            
            if($stockmoves){
                foreach($stockmoves as $stockmove){
                    $stockmove['transaction_id'] = $id;
                    $this->db->insert('stock_movement', $stockmove);
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            
            return true;
        }
        return false;
    }
    public function transferCustomerStock($id = false, $data = false, $products = false)
    {
        if($this->db->update("customer_stocks",$data,array("id"=>$id))){
            if($products){
                $suspend_bill = $this->getSuspendBillByTableID($data['table_id']);
                foreach($products as $product){
                    $product['suspend_id'] = $suspend_bill->id;
                    $product['customer_stock_id'] = $id;
                    $this->db->insert('suspended_items', $product);
                }
            }
            return true;
        }
        return false;
    }
    public function cancelCustomerStock($id = false)
    {
        if($this->db->update("customer_stocks",array("status" => "pending"),array("id"=>$id))){
            $this->db->delete("suspended_items", array("customer_stock_id"=>$id));
            return true;
        }
        return false;
    }

    public function getAllSuspendBills()
    {
        $q = $this->db->group_by("table_id")->get("suspended_bills");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}
