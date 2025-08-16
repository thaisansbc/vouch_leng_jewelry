<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchases_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_Expense($data = array(), $items=array(), $accTrans = array(), $payment = array())
    {
        if ($this->db->insert('expenses', $data)) {
            $expense_id = $this->db->insert_id();
            if ($items) {
                foreach ($items as $item) {
                    $item['expense_id'] = $expense_id;
                    $this->db->insert('expense_items', $item);
                }
            }
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $expense_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            // if ($payment) {
            //     $payment['expense_id'] = $expense_id;
            //     $this->db->insert('payments',$payment);
            // }
            $this->site->updateReference('ex');
            return true;
        }
        return false;
    }

    public function addExpense($data, $accTrans = array())
    {
        foreach ($data as $item) {
            $this->db->insert('expenses', $item);
            //========Add Accounting=========//
            $expense_id = $this->db->insert_id();
            //========End Accounting=========//
        }
        if ($accTrans) {
            foreach ($accTrans as $accTran) {
                $accTran['tran_no'] = $expense_id;
                $this->db->insert('gl_trans', $accTran);
            }
        }
        $this->site->updateReference('ex');
        return true;
    }
    public function addBudget($data)
    {
        $this->db->insert('budgets', $data);
        $this->site->updateReference('bg');
        return true;
    }
    public function getBudgetByID($id)
    {
        $q = $this->db->get_where('budgets', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getBudgetByReference($reference)
    {
        $q = $this->db->get_where('budgets', ['reference' => $reference]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function updateBudgetByReference($id, $data)
    {
        if($this->db->update('budgets', $data, ['id' => $id])){
            return true;
        }
        return false;
    }
    public function deleteBudgetByReference($reference)
    {
        $this->db->delete('budgets', ['reference' => $reference]);
    }
    public function getAllBudgets(){
        $q = $this->db->get('budgets');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function updateExpenseByReference($id, $data, $accTrans = array())
    {
        foreach ($data as $item) {
            $this->db->insert('expenses', $item);
            $expense_id = $this->db->insert_id();
        }
        if($accTrans) {
        foreach ($accTrans as $accTran) {
        $accTran['tran_no'] = $expense_id;
                $this->db->insert('gl_trans', $accTran); }
        }
        return true;
    }
    public function deleteExpenseByReference($reference)
    {
        $this->db->delete('expenses', ['reference' => $reference]);
    }
    public function getExpenseByReference($reference)
    {
        //$this->db->get_where('expense_categories', ['reference' => $reference]);
        $this->db->select('*')->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left');
        $q = $this->db->get_where('expenses', ['reference' => $reference]);

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllExpensesBudget()
    {
        $q = $this->db->get('expenses_budget');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getExpenseBudgetByID($id)
    {
        $q = $this->db->get_where('expenses_budget', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllExpensesByBudgetID($id = null)
    {
        $q = $this->db->get_where('expenses_budget', ['budget_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getExpenseBudgetByReference($reference)
    {
        $this->db->select('*')
        ->join('expense_categories', 'expense_categories.id=expenses_budget.category_id', 'left');
        $q = $this->db->get_where('expenses_budget', ['reference' => $reference]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addExpenseBudget($data, $accTrans = array())
    {
        
        foreach ($data as $item) {
            $this->db->insert('expenses_budget', $item);
            //========Add Accounting=========//
            $expense_budget_id = $this->db->insert_id();
            //========End Accounting=========//
        }
        if ($accTrans) {
            foreach ($accTrans as $accTran) {
                $accTran['tran_no'] = $expense_budget_id;
                $this->db->insert('gl_trans', $accTran);
            }
        }
        $this->site->updateReference('exb');
        return true;
    }

    public function updateExpenseBudgetByReference($id, $data, $accTrans = array())
    {
        foreach ($data as $item) {
            $this->db->insert('expenses_budget', $item);
            $expense_budget_id = $this->db->insert_id();
        }
        if($accTrans) {
            foreach ($accTrans as $accTran) {
                $accTran['tran_no'] = $expense_budget_id;
                $this->db->insert('gl_trans', $accTran); 
            }
        }
        return true;
    }
    public function deleteExpenseBudgetByReference($reference)
    {
        $this->db->delete('expenses_budget', ['reference' => $reference]);
    }
    public function addPayment($data = [], $accTranPayments = array())
    {   
        if (!$this->Owner && !$this->Admin && 
                $this->config->item('requested_ap') && 
                $this->GP['purchases-payments_requested']) {
            $purchase = $this->getPurchaseByID($data['purchase_id']);
            $purchase_balance = $purchase->grand_total - $purchase->paid;
      
            $ap_payment= $this->syncAPPurchasePayments($data['purchase_id']);
            $request_payment = $ap_payment + $data['amount'];
            if($purchase_balance > $request_payment){
                 $data['type']= 'pending';
                $this->db->insert('payments_requested', $data);
                return true;
            }
        }else{
            if ($this->db->insert('payments', $data)) {
                //---------accounting-----
                $payment_id = $this->db->insert_id();
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no']= $payment_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                if ($this->site->getReference('ppay') == $data['reference_no']) {
                    $this->site->updateReference('ppay');
                }
                $this->site->syncPurchasePayments($data['purchase_id']);
                $purchase = $this->getPurchaseByID($data['purchase_id']);
                if ($purchase->supplier_id && $data['paid_by'] == 'deposit') {
                    $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                    $this->db->update('companies', 
                                ['deposit_amount' => ($supplier->deposit_amount - $data['amount'])], 
                                ['id' => $purchase->supplier_id]);
                }
                return true;
            }
        }
        return false;
    }
    public function addProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity + $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                return true;
            }
        } else {
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity])) {
                return true;
            }
        }
        return false;
    }

    public function addPurchase($data, $items, $accTrans = null, $return_id = null, $stockmoveReturns = false)
    {
        $this->db->trans_start();
        if($return_id){
            $this->db->delete('purchases', array('id' => $return_id));
            $this->db->delete('purchase_items', array('purchase_id' => $return_id));
            $this->site->deleteStockmoves('Purchases', $return_id);
            $this->site->deleteAccTran('Purchases', $return_id);
            $data['id'] = $return_id;
        }
        if ($this->db->insert('purchases', $data)) {
            $purchase_id = $this->db->insert_id();
            //========Add accounting to accounting transaction====== //
            if ($accTrans != null) {
                foreach($accTrans as $accTran) {
                    $accTran['tran_no'] = $purchase_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //========End accounting to accounting transaction====== //
            if ($this->site->getReference('p') == $data['reference_no']) {
                $this->site->updateReference('p');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $this->db->insert('purchase_items', $item);
                $purchase_item_id = $this->db->insert_id();
                if ($data['status'] == 'received') {
                    $stockmove = array(
                        'transaction'    => 'Purchases',
                        'transaction_id' => $purchase_id,
                        'product_id'     => $item['product_id'],
                        'product_type'   => $item['product_type'],
                        'product_code'   => $item['product_code'],
                        'product_name'   => $item['product_name'],
                        'option_id'      => $item['option_id'],
                        'quantity'       => $item['quantity'],
                        'unit_quantity'  => $item['unit_quantity'],
                        'unit_code'      => $item['product_unit_code'],
                        'unit_id'        => $item['product_unit_id'],
                        'warehouse_id'   => $data['warehouse_id'],
                        'expiry'         => $item['expiry'],
                        'date'           => $data['date'],
                        'real_unit_cost' => $item['base_unit_cost'],
                        'serial_no'      => null,
                        'reference_no'   => $data['reference_no'],
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    $this->db->insert('stock_movement', $stockmove);
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($item['product_id'], "Purchases", $purchase_id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($item['product_id'], "Purchases", $purchase_id);
                    }
                    if ($cal_cost) {
                        if ($item['option_id']) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                        }
                    }
                }
            }
            $this->syncPO(false, $data['purchase_order_id']);
            $this->syncPl(false, $data['project_plan_id']);
            if ($data['status'] == 'returned') {
                if ($stockmoveReturns) {  
                    foreach ($stockmoveReturns as $stockmoveReturn) {
                        $purchase_item_id = $stockmoveReturn['purchase_item_id'];
                        unset($stockmoveReturn['purchase_item_id']);
                        $stockmoveReturn['transaction_id'] = $purchase_id;
                        $this->db->insert('stock_movement', $stockmoveReturn);
                    }
                }
                $this->db->update('purchases', ['return_purchase_ref' => $data['return_purchase_ref'], 'surcharge' => $data['surcharge'], 'return_purchase_total' => $data['grand_total'], 'return_id' => $purchase_id], ['id' => $data['purchase_id']]);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getPOstatusByID($id) 
    {
        $q = $this->db->select('SUM(quantity - quantity_received) as balance, SUM(quantity) as quantity')->get_where('purchase_order_items', array('purchase_id' => $id));
        if($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProPlanstatusByID($id) 
    {
        $q = $this->db->select('SUM(quantity - quantity_received) as balance, SUM(quantity) as quantity')->get_where('projects_plan_items', array('project_plan_id' => $id));
        if($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function calculatePurchaseTotals($id, $return_id, $surcharge)
    {
        $purchase = $this->getPurchaseByID($id);
        $items    = $this->getAllPurchaseItems($id);
        if (!empty($items)) {
            $total            = 0;
            $product_tax      = 0;
            $order_tax        = 0;
            $product_discount = 0;
            $order_discount   = 0;
            foreach ($items as $item) {
                $product_tax      += $item->item_tax;
                $product_discount += $item->item_discount;
                $total            += $item->net_unit_cost * $item->quantity;
            }
            if ($purchase->order_discount_id) {
                $percentage        = '%';
                $order_discount_id = $purchase->order_discount_id;
                $opos              = strpos($order_discount_id, $percentage);
                if ($opos !== false) {
                    $ods            = explode('%', $order_discount_id);
                    $order_discount = (($total + $product_tax) * (float)($ods[0])) / 100;
                } else {
                    $order_discount = $order_discount_id;
                }
            }
            if ($purchase->order_tax_id) {
                $order_tax_id = $purchase->order_tax_id;
                if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                    if ($order_tax_details->type == 2) {
                        $order_tax = $order_tax_details->rate;
                    }
                    if ($order_tax_details->type == 1) {
                        $order_tax = (($total + $product_tax - $order_discount) * $order_tax_details->rate) / 100;
                    }
                }
            }
            $total_discount = $order_discount + $product_discount;
            $total_tax      = $product_tax    + $order_tax;
            $grand_total    = $total          + $total_tax             + $purchase->shipping - $order_discount             + $surcharge;
            $data           = [
                'total'            => $total,
                'product_discount' => $product_discount,
                'order_discount'   => $order_discount,
                'total_discount'   => $total_discount,
                'product_tax'      => $product_tax,
                'order_tax'        => $order_tax,
                'total_tax'        => $total_tax,
                'grand_total'      => $grand_total,
                'return_id'        => $return_id,
                'surcharge'        => $surcharge,
            ];
            if ($this->db->update('purchases', $data, ['id' => $id])) {
                return true;
            }
        } else {
            $this->db->delete('purchases', ['id' => $id]);
        }
        return false;
    }

    public function deleteExpense($id)
    {
        if ($this->db->delete('expenses', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncPurchasePayments($opay->purchase_id);
            if ($opay->paid_by == 'deposit') {
                $purchase     = $this->getPurchaseByID($opay->purchase_id);
                $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount + $opay->amount)], ['id' => $supplier->id]);
            }
            return true;
        }
        return false;
    }

    public function deletePurchase($id)
    {
        $this->db->trans_start();
        $purchase       = $this->getPurchaseByID($id);
        $purchase_items = $this->site->getAllPurchaseItems($id);
        if ($this->db->delete('purchase_items', ['purchase_id' => $id]) && $this->db->delete('purchases', ['id' => $id])) {
            //=========Add Accounting ========//
            $this->site->deleteAccTran('Purchases',$id);
            $this->site->deleteAccTran('Shipping',$id);
            $pur_payments = $this->getPurchasePayments($id);
            if ($pur_payments) {
                $this->db->delete('payments', array('purchase_id' => $id));
                foreach ($pur_payments as $pur_payment) {
                    $this->site->deleteAccTran('Payment', $pur_payment->id);
                }
            }
            //=========End Accounting ========//
            $purchases = $this->getPurchaseByPurchaseId($id);
            if ($purchases) {
                foreach ($purchases as $row_purchase) {
                    $this->db->delete('purchases', array('id' => $row_purchase->id));
                    $this->db->delete('purchase_items', array('purchase_id' => $row_purchase->id));
                    $this->site->deleteStockmoves('Purchases', $row_purchase->id);
                    //=========Add Accounting ========//
                    $this->site->deleteAccTran('Purchases', $row_purchase->id);
                    $this->site->deleteAccTran('Shipping', $row_purchase->id);
                    $pur_payments = $this->getPurchasePayments($row_purchase->id);
                    if ($pur_payments) {
                        $this->db->delete('payments', array('purchase_id' => $id));
                        foreach ($pur_payments as $pur_payment) {
                            $this->site->deleteAccTran('Payment', $pur_payment->id);
                        }
                    }
                    //=========End Accounting ========//
                }
            }
            $pur_receives = $this->getReceivByPurchaseID($id);
            if ($pur_receives) {
                foreach ($pur_receives as $pur_receive) {
                    $this->deleteReceive($pur_receive->id);
                }
            }
            $this->site->deleteStockmoves('Purchases', $id);
            if ($purchase->status == 'received' || $purchase->status == 'partial') {
                foreach ($purchase_items as $oitem) {
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($oitem->product_id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($oitem->product_id);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($oitem->product_id);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($oitem->product_id);
                    }
                    if ($cal_cost) {
                        if ($oitem->option_id) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $oitem->option_id, 'product_id' => $oitem->product_id));
                        }
                    }
                    $received = $oitem->quantity_received ? $oitem->quantity_received : $oitem->quantity;
                    if ($oitem->quantity_balance < $received) {
                        $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'option_id' => $oitem->option_id];
                        $this->site->setPurchaseItem($clause, ($oitem->quantity_balance - $received));
                    }
                }
            }
            $this->syncPO(false, $purchase->purchase_order_id);
            $this->syncPl(false, $purchase->project_plan_id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getAllProducts()
    {
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $this->db->select('purchase_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate,products.type,
            products.unit, products.other_cost, products.currency, products.details as details, product_variants.name as variant, 
            products.hsn_code as hsn_code, products.second_name as second_name,currencies.symbol as symbol')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->join('currencies', 'currencies.code=products.currency', 'left')
            ->group_by('purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
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

    public function getAllReturnItems($return_id)
    {
        $this->db->select('return_purchase_items.*, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=return_purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=return_purchase_items.option_id', 'left')
            ->group_by('return_purchase_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('return_purchase_items', ['return_id' => $return_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getExpenseByID($id)
    {
        $q = $this->db->get_where('expenses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getExpenseMultiByID($id)
    {
        $this->db->where("FIND_IN_SET(".$id.", id)");
        $q = $this->db->get('expenses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getExpenseCategories()
    {
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getExpenseCategoryByID($id)
    {
        $q = $this->db->get_where('expense_categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getOverSoldCosting($product_id)
    {
        $q = $this->db->get_where('costing', ['overselling' => 1]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getPaymentsForPurchase($purchase_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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

    public function getProductNames_($term, $limit = 5)
    {
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR supplier1_part_no LIKE '%" . $term . "%' OR supplier2_part_no LIKE '%" . $term . "%' OR supplier3_part_no LIKE '%" . $term . "%' OR supplier4_part_no LIKE '%" . $term . "%' OR supplier5_part_no LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductNames($term, $limit = 15)
    {
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

            $this->db->select("
                products.*, 
                COALESCE({$this->db->dbprefix('CP')}.product_code, {$this->db->dbprefix('products')}.code) AS code,  
                COALESCE({$this->db->dbprefix('CP')}.unit_id, {$this->db->dbprefix('products')}.purchase_unit) AS purchase_unit,
            ");
            $this->db->join($sub_q, 'CP.product_id = products.id', 'left');
        }
        $this->db->where(" (type = 'standard' || type = 'book' || type = 'raw_material') AND
        (
            name LIKE '" . $term . "%' 
            OR code LIKE '" . $term . "%' 
            " . ($this->Settings->multiple_code_unit != 0 ? (" OR {$this->db->dbprefix('CP')}.product_code LIKE '%" . $term . "%' ") : "") . "  
            OR supplier1_part_no LIKE '%" . $term . "%' 
            OR supplier2_part_no LIKE '%" . $term . "%' 
            OR supplier3_part_no LIKE '%" . $term . "%' 
            OR supplier4_part_no LIKE '%" . $term . "%' 
            OR supplier5_part_no LIKE '%" . $term . "%' 
            OR concat(name, ' (', code, ')') LIKE '" . $term . "%')"
        );
        $this->db->group_by('products.id');
        $this->db->limit($limit);
        $q = $this->db->get('products');
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

    public function getProductOptions($product_id)
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

    public function getProductsByCode($code)
    {
        $this->db->select('*')->from('products')->like('code', $code, 'both');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', ['name' => $name, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getPurcahseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getallPurchase($id = null, $wh = null)
    {
        $this->db
            ->select("
                        purchases.*, products.*,
                        purchase_items.product_code,purchase_items.product_name,
		                purchase_items.unit_cost,purchase_items.quantity,
		                purchases.status,purchase_items.subtotal,units.name as unit_code,
                        users.username, tax_rates.name AS tax_name, warehouses.name AS ware_name")
            ->from('purchases')
            ->join('purchase_items', 'purchase_items.purchase_id = purchases.id', 'inner')
            ->join('products', 'bpas_products.id = purchase_items.product_id', 'left')
            ->join('units', 'products.unit = units.id', 'left')
            // ->join('companies', 'companies.id = purchases.biller_id', 'inner')
            ->join('users', 'purchases.created_by = users.id', 'left')
            ->join('tax_rates', 'purchases.order_tax_id = tax_rates.id', 'left')
            ->join('warehouses', 'purchases.warehouse_id = warehouses.id', 'left')
            ->where('purchases.id', $id);
        if ($wh) {
            $this->db->where_in('bpas_purchases.warehouse_id', $wh);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPurchase_detail_ByID($id)
    {
        $this->db->select('purchase_items.*, purchases.*')
            ->join('purchase_items', 'purchases.id=purchase_items.purchase_id', 'left');
        $q = $this->db->get_where('purchases', array('purchase_items.purchase_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPurchasePayments($purchase_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
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
        $q = $this->db->get_where('return_purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
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

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['warehouse_id' => $warehouse_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function resetProductOptionQuantity($option_id, $warehouse_id, $quantity, $product_id)
    {
        if ($option = $this->getProductWarehouseOptionQty($option_id, $warehouse_id)) {
            $nq = $option->quantity - $quantity;
            if ($this->db->update('warehouses_products_variants', ['quantity' => $nq], ['option_id' => $option_id, 'warehouse_id' => $warehouse_id])) {
                return true;
            }
        } else {
            $nq = 0 - $quantity;
            if ($this->db->insert('warehouses_products_variants', ['option_id' => $option_id, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $nq])) {
                return true;
            }
        }
        return false;
    }

    public function returnPurchase($data = [], $items = [])
    {
        $purchase_items = $this->site->getAllPurchaseItems($data['purchase_id']);

        if ($this->db->insert('return_purchases', $data)) {
            $return_id = $this->db->insert_id();
            if ($this->site->getReference('rep') == $data['reference_no']) {
                $this->site->updateReference('rep');
            }
            foreach ($items as $item) {
                $item['return_id'] = $return_id;
                $this->db->insert('return_purchase_items', $item);

                if ($purchase_item = $this->getPurcahseItemByID($item['purchase_item_id'])) {
                    if ($purchase_item->quantity == $item['quantity']) {
                        $this->db->delete('purchase_items', ['id' => $item['purchase_item_id']]);
                    } else {
                        $nqty          = $purchase_item->quantity          - $item['quantity'];
                        $bqty          = $purchase_item->quantity_balance  - $item['quantity'];
                        $rqty          = $purchase_item->quantity_received - $item['quantity'];
                        $tax           = $purchase_item->unit_cost         - $purchase_item->net_unit_cost;
                        $discount      = $purchase_item->item_discount / $purchase_item->quantity;
                        $item_tax      = $tax                      * $nqty;
                        $item_discount = $discount                 * $nqty;
                        $subtotal      = $purchase_item->unit_cost * $nqty;
                        $this->db->update('purchase_items', ['quantity' => $nqty, 'quantity_balance' => $bqty, 'quantity_received' => $rqty, 'item_tax' => $item_tax, 'item_discount' => $item_discount, 'subtotal' => $subtotal], ['id' => $item['purchase_item_id']]);
                    }
                }
            }
            $this->calculatePurchaseTotals($data['purchase_id'], $return_id, $data['surcharge']);
            $this->site->syncQuantity(null, null, $purchase_items);
            $this->site->syncQuantity(null, $data['purchase_id']);
            return true;
        }
        return false;
    }

    public function updateAVCO_21_03_2023($data)
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

    public function updateAVCO($data)
    {
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
            $total_cost     = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('warehouses_products', ['avg_cost' => $avg_cost], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']]);
            } else {
                $this->db->update('warehouses_products', ['avg_cost' => 0], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']]);
            }
        } else {
            $this->db->insert('warehouses_products', ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0]);
        }
    }

    public function updateExpense($id, $data,$accTrans = array())
    {
    
        if ($this->db->update('expenses', $data, ['id' => $id])) {
            //==========Add Accounting =======//
            $this->site->deleteAccTran('Expense',$id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            //==========End Accounting =======//
            return true;
        }
        return false;
    }
    public function update_Expense($id = false, $data = array(),$items=array(), $accTrans = array(), $payment = array())
    {
        if ($this->db->update('expenses', $data, array('id' => $id))) {
            $this->site->deleteAccTran('Expense',$id);
            $this->db->delete('expense_items',array('expense_id' => $id));
            if($items){
                $this->db->insert_batch('expense_items', $items);
            }
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            // if($payment){
            //     $this->db->delete('payments',array('expense_id' => $id));
            //     $this->db->insert('payments', $payment);
            // }
            // $this->site->syncExpensePayments($id);
            // if($this->config->item("expense_request")){
            //     $expense = $this->getExpenseByID($id);
            //     $this->sysnceExpenseRequest($expense->request_id);
            // }
            return true;
        }
        return false;
    }
    public function updatePayment($id, $data = [],$accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);
        $purchase = $this->getPurchaseByID($data['purchase_id']);
        $supplier_id = $purchase->supplier_id;
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncPurchasePayments($data['purchase_id']);
            if ($opay->paid_by == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount + $opay->amount)], ['id' => $supplier->id]);
            }
            if ($supplier_id && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount - $data['amount'])], ['id' => $supplier_id]);
            }

            //=========Add Accounting========//
            $this->site->deleteAccTran('Payment',$id);
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
              //=========End Accounting========//
            return true;
        }
        return false;
    }

    public function updatePurchase_21_03_2023($id, $data, $items = [], $accTrans = null)
    {
        $this->db->trans_start();
        $opurchase = $this->getPurchaseByID($id);
        $oitems    = $this->getAllPurchaseItems($id);
        if ($this->db->update('purchases', $data, ['id' => $id]) && $this->db->delete('purchase_items', ['purchase_id' => $id])) {
            $purchase_id = $id;
            //=======Add Accounting =======//
            $this->site->deleteAccTran('purchases', $purchase_id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            //=======End Accounting =======//
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                $this->db->insert('purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', ['cost' => $item['base_unit_cost']], ['id' => $item['product_id']]);
                    // $this->db->update('cost_price_by_units', ['cost' => $item['base_unit_cost']], ['product_id' => $item['product_id'], 'unit_id' => $item['product_unit_id']]);
                    // $this->db->update('products', ['cost' => $item['real_unit_cost']], ['id' => $item['product_id']]);
                }
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['base_unit_cost']]);
                    // $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']]);
                }
            }
            $this->site->syncQuantity(null, null, $oitems);
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(null, $id);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->base_unit_cost]);
                    // $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost]);
                }
            }
            $this->site->syncPurchasePayments($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }

        return false;
    }

    public function updatePurchase_02_06_2023($id, $data, $items = [], $accTrans = null)
    {
        $this->db->trans_start();
        $opurchase = $this->getPurchaseByID($id);
        $oitems    = $this->getAllPurchaseItems($id);
        if ($this->db->update('purchases', $data, ['id' => $id])) {
            $purchase_id = $id;
            $this->site->deleteAccTran('purchases', $purchase_id);
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            if ($opurchase->status == 'received' || $opurchase->status == 'partial') {
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->base_unit_cost]);
                }
            }
            $this->db->delete('purchase_items', ['purchase_id' => $id]);
            $this->site->syncQuantity(null, null, $oitems);
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                $this->db->insert('purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', ['cost' => $item['base_unit_cost']], ['id' => $item['product_id']]);
                }
            }
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                foreach ($items as $item) {
                    $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['base_unit_cost']]);
                }
                $this->site->syncQuantity(null, $id);
            }
            $this->site->syncPurchasePayments($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function updatePurchase($id, $data, $items = [], $accTrans = null)
    {
        $this->db->trans_start();
        if ($this->db->update('purchases', $data, ['id' => $id]) && $this->db->delete('purchase_items', ['purchase_id' => $id])) {
            $purchase_id = $id;
            $this->site->deleteStockmoves('Purchases', $purchase_id);
            $this->site->deleteAccTran('purchases', $purchase_id);
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            $pur_receives = $this->getReceivByPurchaseID($id);
            foreach ($items as $item) {
                if (!$pur_receives && ($data['status'] == 'received' || $data['status'] == 'partial')) {
                    $stockmove = array(
                        'transaction'    => 'Purchases',
                        'transaction_id' => $purchase_id,
                        'product_id'     => $item['product_id'],
                        'product_type'   => $item['product_type'],
                        'product_code'   => $item['product_code'],
                        'product_name'   => $item['product_name'],
                        'option_id'      => $item['option_id'],
                        'quantity'       => $item['quantity_received'],
                        'unit_quantity'  => $item['unit_quantity'],
                        'unit_code'      => $item['product_unit_code'],
                        'unit_id'        => $item['product_unit_id'],
                        'warehouse_id'   => $data['warehouse_id'],
                        'expiry'         => $item['expiry'],
                        'date'           => $data['date'],
                        'real_unit_cost' => $item['base_unit_cost'],
                        'serial_no'      => null,
                        'reference_no'   => $data['reference_no'],
                        'user_id'        => $this->session->userdata('user_id'),
                    );
                    $this->db->insert('stock_movement', $stockmove);
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($item['product_id'], "Purchases", $purchase_id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($item['product_id'], "Purchases", $purchase_id);
                    }                    
                    if ($cal_cost) {
                        if ($item['option_id']) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                        }
                    }
                }
                $item['purchase_id'] = $id;
                $this->db->insert('purchase_items', $item);
            }
            $this->syncPO($id);
            $this->syncPl($id);
            $this->site->syncPurchasePayments($id);
            if ($pur_receives) { 
                $this->sysReceiveQuantity($id);
            }
            $this->synPurchaseCost($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function cal_avg_cost($old_cost, $new_cost) 
    {
        $total_cost     = (($old_cost['quantity'] * $old_cost['avg_cost']) + ($new_cost['quantity'] * $new_cost['avg_cost']));
        $total_quantity = ($old_cost['quantity'] + $new_cost['quantity']);
        if (!empty($total_quantity)) {
            return array('product_id' => $old_cost['product_id'], 'warehouse_id' => $old_cost['warehouse_id'], 'quantity' => $total_quantity, 'avg_cost' => ($total_cost / $total_quantity));
        } else {
            return array('product_id' => $old_cost['product_id'], 'warehouse_id' => $old_cost['warehouse_id'], 'quantity' => 0, 'avg_cost' => 0);
        }
    }

    public function update_average_cost($data)
    {
        if (!empty($data)) {
            if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
                $this->db->update('warehouses_products', ['avg_cost' => $data['avg_cost']], ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']]);
            } else {
                $this->db->insert('warehouses_products', ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['avg_cost'], 'quantity' => 0]);
            }
        }
    }

    public function editExpiry($data, $quantity)
    {
        $this->db->trans_start();
        if($quantity == $data['quantity_balance']){
            $this->db->update('purchase_items', ['expiry' => $data['expiry']], ['id' => $data['id']]);
        } else {
            $purchase = $this->getPurchaseItemByID($data['id']);
            $item = [
                'id'                => $data['id'],
                'quantity_balance'  => $purchase->quantity_balance - $data['quantity_balance'],
                'quantity'          => $purchase->quantity - $data['quantity'],
                'item_tax'          => $purchase->item_tax - $data['item_tax'],
                'item_discount'     => $purchase->item_discount - $data['item_discount'],
                'subtotal'          => (($purchase->quantity - $data['quantity']) * $purchase->unit_cost),
                'quantity_received' => $purchase->quantity_balance - $data['quantity_received'],
                'unit_quantity'     => $purchase->quantity_balance - $data['unit_quantity'],
            ];
            if($this->db->update('purchase_items', $item, ['id' => $data['id']])){
                unset($data['id']);
                $clause['product_id'] = $data["product_id"];
                if ($data["purchase_id"] != null) {
                    $clause['purchase_id'] = $data["purchase_id"];
                } else {
                    $clause['transfer_id'] = $data["transfer_id"];
                }
                $clause['expiry'] = $data["expiry"];
                if ($pi = $this->site->getPurchasedItem($clause)) {
                    $subtotal = $pi->subtotal + $data['subtotal'];
                    $quantity_balance = $pi->quantity_balance + $data["quantity_balance"];
                    $item_tax = $pi->item_tax + $data["item_tax"];
                    $item_discount = $pi->item_discount + $data["item_discount"];
                    $quantity = $pi->quantity + $data["quantity"];
                    $quantity_received = $pi->quantity_received + $data["quantity_received"];
                    $unit_quantity = $pi->unit_quantity + $data["unit_quantity"];
                    log_message('error', 'More than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $data["quantity_balance"] . ' PI: ' . print_r($pi, true));
                    $pi_data['quantity_balance']= $quantity_balance;
                    $pi_data['quantity']= $quantity;
                    $pi_data['subtotal']= $subtotal;
                    $pi_data['item_tax']= $item_tax;
                    $pi_data['item_discount']= $item_discount;
                    $pi_data['unit_quantity']= $unit_quantity;
                    $pi_data['quantity_received']= $quantity_received;
                    $this->db->update('purchase_items', $pi_data, ['id' => $pi->id]);
                } else {
                    $this->db->insert('purchase_items', $data);
                }
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status, $note)
    {
        $this->db->trans_start();
        $purchase = $this->getPurchaseByID($id);
        $items    = $this->site->getAllPurchaseItems($id);
        if ($this->db->update('purchases', ['status' => $status, 'note' => $note], ['id' => $id])) {
            if (($purchase->status != 'received' || $purchase->status != 'partial') && ($status == 'received' || $status == 'partial')) {
                foreach ($items as $item) {
                    $qb = $status == 'received' ? ($item->quantity_balance + ($item->quantity - $item->quantity_received)) : $item->quantity_balance;
                    $qr = $status == 'received' ? $item->quantity : $item->quantity_received;
                    $this->db->update('purchase_items', ['status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr], ['id' => $item->id]);
                    $this->updateAVCO(['product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost]);
                }
                $this->site->syncQuantity(null, null, $items);
            } elseif (($purchase->status == 'received' || $purchase->status == 'partial') && ($status == 'ordered' || $status == 'pending')) {
                foreach ($items as $item) {
                    $qb = 0;
                    $qr = 0;
                    $this->db->update('purchase_items', ['status' => $status, 'quantity_balance' => $qb, 'quantity_received' => $qr], ['id' => $item->id]);
                    $this->updateAVCO(['product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'quantity' => $item->quantity, 'cost' => $item->real_unit_cost]);
                }
                $this->site->syncQuantity(null, null, $items);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (UpdateStatus:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getPurchaseOrderByID($id)
    {
        $this->db->select('purchases_order.*, payment_term.description, warehouses.name AS Wname,companies.company,companies.name AS username, tax_rates.name AS tax_name, warehouses.name AS ware_name, purchases_request.reference_no as pr_referemce_no')
                ->join('payment_term','purchases_order.payment_term = payment_term.id','left')
                ->join('warehouses','purchases_order.warehouse_id= warehouses.id','left')
                ->join('companies','purchases_order.biller_id = companies.id','left')
                ->join('users','purchases_order.created_by = users.id','left')
                ->join('tax_rates','purchases_order.order_tax_id = tax_rates.id','left')
                ->join('purchases_request','purchases_order.request_id = purchases_request.id','left')
                ->where('purchases_order.id',$id);
        $q = $this->db->get('purchases_order');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPaymentByPurchaseID($id)
    {
        $q = $this->db->get_where('payments', array('purchase_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getPurchasesOrderbyID($id)
    {
         $q= $this->db->get_where('purchases_order',array('id'=>$id),1);
          if ($q->num_rows() > 0) {
            return $q->row();
          }
          return FALSE;
    }
    
    public function getPurchasesReqestbyID($id)
    {
         $q= $this->db->get_where('purchases_request',array('id'=>$id),1);
          if ($q->num_rows() > 0) {
            return $q->row();
          }
          return FALSE;
    }

    public function getAllPurchaseOrderItems_order($purchase_id)
    {
        $this->db->select(' purchase_order_items.id,
                            purchase_order_items.purchase_id,
                            purchase_order_items.transfer_id,
                            purchase_order_items.product_id,
                            purchase_order_items.product_code,
                            purchase_order_items.product_name,
                            purchase_order_items.option_id,
                            purchase_order_items.net_unit_cost,
                            purchase_order_items.quantity as po_qty,
                            (bpas_purchase_order_items.quantity - bpas_purchase_order_items.quantity_po) AS quantity,
                            purchase_order_items.quantity_po,
                            purchase_order_items.warehouse_id,
                            purchase_order_items.item_tax,
                            purchase_order_items.tax_rate_id,
                            purchase_order_items.tax,
                            purchase_order_items.discount,
                            purchase_order_items.item_discount,
                            purchase_order_items.expiry,
                            purchase_order_items.subtotal,
                            purchase_order_items.quantity_balance,
                            purchase_order_items.date,
                            purchase_order_items.`status`,
                            purchase_order_items.unit_cost,
                            purchase_order_items.real_unit_cost,
                            purchase_order_items.quantity_received,
                            purchase_order_items.supplier_part_no,
                            purchase_order_items.supplier_id,
                            purchase_order_items.price, 
                            tax_rates.code as tax_code, 
                            tax_rates.name as tax_name, 
                            tax_rates.rate as tax_rate, 
                            products.unit, 
                            products.details as details,
                            products.image,
                            products.name as pname, 
                            product_variants.name as variant,companies.name')
            ->join('products', 'products.id=purchase_order_items.product_id', 'left')
            ->join('companies', 'companies.id=purchase_order_items.supplier_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_order_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_order_items.tax_rate_id', 'left')
            ->group_by('purchase_order_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_order_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPurcahseItemByPurchaseID($id)
    {
        $q = $this->db->get_where('purchase_items', array('purchase_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseOrderByProducId($product_id)
    {
        $this->db->select('purchase_order_items.*, SUM(quantity) as qty')
            ->where('product_id', $product_id)
            ->where('purchases_order.status', 'approved')
            ->join('purchases_order', 'purchases_order.id=purchase_order_items.purchase_id');
        $q = $this->db->get('purchase_order_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addOpeningAP($purchases, $deposits, $da)
    {
        /*
        $this->db->trans_start();
        //$this->bpas->print_arrays($purchases, $deposit);
        if ($this->db->insert_batch('purchases', $purchases)) {
            if($this->db->insert_batch('deposits', $deposits)){
                $this->site->syncDeposits($da);
            }
            $this->db->trans_complete();
            return true;
        }
        */
        
        $this->db->trans_start();
        if ($this->db->insert_batch('purchases', $purchases)) {
            if($deposits){
                
                foreach($deposits as $deposit){
                    
                    if(!empty($deposit['amount']) && $deposit['amount'] > 0)
                    {
                        $this->db->insert('deposits', $deposit);
                        $pur_deposit_id = $this->db->insert_id();                       
                        $payment = array(
                            'date' => $deposit['date'],
                            'reference_no' => $deposit['reference'],
                            'amount' => $deposit['amount'],
                            'paid_by' => 'cash',
                            'created_by' => $deposit['created_by'],
                            'type' => 'received',
                            'biller_id' => $deposit['biller_id'],
                            'purchase_deposit_id' => $pur_deposit_id,
                            'opening' => $deposit['opening']
                        );
                        $this->db->insert('payments', $payment);    
                    }
                }
                
                $this->site->syncDeposits($da);
            }
            $this->db->trans_complete();
            return true;
        }
        
        
        
        return false;
    }
    public function addPurchasePaymentMulti($datas = array(),$accTranPayments = array())
    {
        // echo '<pre>';
        // print_r($datas);
        // exit();
        if($datas){
            foreach($datas as $data){
                // if (!$this->Owner && !$this->Admin && $this->config->item('requested_ap') && $this->GP['purchases-payments_requested']) {
                //   $purchase = $this->getPurchaseByID($data['purchase_id']);
                //   $purchase_balance = $purchase->grand_total - $purchase->paid;

                //   $ap_payment= $this->syncAPPurchasePayments($data['purchase_id']);
                //   $request_payment = $ap_payment + $data['amount'];
                //   if($purchase_balance > $request_payment){
                //        $data['type']= 'pending';
                //       $this->db->insert('payments_requested', $data);
                //       return true;
                //   }
                // }else{
                    $this->db->insert('payments', $data);
                    $payment_id = $this->db->insert_id();
                    $this->site->syncPurchasePayments($data['purchase_id']);

                    $accTrans = $accTranPayments[$data['purchase_id']];
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
                        $deposit = $this->site->getDepositByCompanyID($deposit_customer_id);
                        $deposit_balance = $deposit->deposit_amount;
                        $deposit_balance = $deposit_balance - abs($data['amount']);
                        if($this->db->update('companies', array('deposit_amount' => $deposit_balance), array('id' => $deposit_customer_id))){
                            //$this->db->update('deposits', array('amount' => $deposit_balance), array('company_id' => $deposit_customer_id));
                        }
                    }
                //}

            }
            return true;
        }
        return false;
    }
    public function getCombinePaymentPurById($id)
    {
        $this->db->select('id, date, reference_no, supplier,status, grand_total, paid, (grand_total-paid) as balance, payment_status');
        $this->db->from('bpas_purchases');
        $this->db->where_in('id', $id);
        $this->db->where('paid < grand_total');
        $q = $this->db->get();
         if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
    //----------------assets------------
    public function getAssetNames($term, $limit = 5)
    {
        $this->db->where("type = 'asset' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR supplier1_part_no LIKE '%" . $term . "%' OR supplier2_part_no LIKE '%" . $term . "%' OR supplier3_part_no LIKE '%" . $term . "%' OR supplier4_part_no LIKE '%" . $term . "%' OR supplier5_part_no LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");

        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function updateAssetPurchase($id, $data, $items = [], $accTrans = null)
    {
        $this->db->trans_start();
        $opurchase = $this->getPurchaseByID($id);
        $oitems    = $this->getAllPurchaseItems($id);
        if ($this->db->update('purchases', $data, ['id' => $id]) && $this->db->delete('purchase_items', ['purchase_id' => $id])) {
            $purchase_id = $id;
            //=======Add Accounting =======//
            $this->site->deleteAccTran('ExpenseAsset',$purchase_id);
            $this->deleteDPByPurchaseID($purchase_id);
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
             //=======End Accounting =======//
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id']   = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : null;
                $this->db->insert('purchase_items', $item);
                if ($this->Settings->update_cost) {
                    $this->db->update('products', ['cost' => $item['real_unit_cost']], ['id' => $item['product_id']]);
                }
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']]);
                }
            }
            $this->site->syncQuantity(null, null, $oitems);
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
                $this->site->syncQuantity(null, $id);
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $oitem->real_unit_cost]);
                }
            }
            $this->site->syncPurchasePayments($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }

        return false;
    }
    public function deleteDPByPurchaseID($id)
    {   
        if ($this->db->delete('asset_evaluation', ['expense_id' => $id])) {
            $this->site->deleteDpByexpense('Depreciation',$id);
            return true;
        }
        return false;
    }

    public function add_stock_received($id, $data, $items) 
    {
        if (!empty($data)) {
            if ($this->db->insert('stock_received', $data)) {
                $insert_id = $this->db->insert_id();
                foreach($items as $item) {
                    $base_unit_cost = $item['base_unit_cost']; unset($item['base_unit_cost']);
                    $item['stock_received_id'] = $insert_id;
                    $q = $this->db->get_where('purchase_items', ['id' => $item['purchase_item_id']], 1);
                    if ($q->num_rows() > 0) {
                        $pi = $q->row();
                        $balance  = $pi->quantity_balance  + $item['quantity'];
                        $received = $pi->quantity_received + $item['quantity'];
                        $this->db->update('purchase_items', ['quantity_balance' => $balance, 'quantity_received' => $received, 'status' => 'received'], ['id' => $pi->id]);
                    }
                    $this->db->insert('stock_received_items', $item);
                    $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $data['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $base_unit_cost]);
                }
                $this->set_purchase_status($id);
                $this->site->syncQuantity(null, $id);
                $this->site->updateReference('str');
            }
            return true;
        }
        return false;
    }

    public function update_stock_received($purchase_id, $stock_received_id, $data, $items) 
    {
        if (!empty($data)) {
            $o_str  = $this->getStockInByID($stock_received_id);
            $o_stri = $this->getStockReceivedItems($stock_received_id);
            if ($this->db->update('stock_received', $data, ['id' => $stock_received_id]) && $this->db->delete('stock_received_items', ['stock_received_id' => $stock_received_id])) {
                foreach($o_stri as $oitem) {
                    $q = $this->db->get_where('purchase_items', ['id' => $oitem->purchase_item_id], 1);
                    if ($q->num_rows() > 0) {
                        $pi = $q->row();
                        $balance  = $pi->quantity_balance  - $oitem->quantity;
                        $received = $pi->quantity_received - $oitem->quantity;
                        $this->db->update('purchase_items', ['quantity_balance' => $balance, 'quantity_received' => $received, 'status' => 'received'], ['id' => $pi->id]);
                        $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $data['warehouse_id'], 'quantity' => (0 - $oitem->quantity), 'cost' => $pi->base_unit_cost]);
                    }
                }
                $this->site->syncQuantity(null, $purchase_id); 
                foreach($items as $item) {
                    $base_unit_cost = $item['base_unit_cost']; unset($item['base_unit_cost']);
                    $item['stock_received_id'] = $stock_received_id;
                    $q = $this->db->get_where('purchase_items', ['id' => $item['purchase_item_id']], 1);
                    if ($q->num_rows() > 0) {
                        $pi = $q->row();
                        $balance  = $pi->quantity_balance  + $item['quantity'];
                        $received = $pi->quantity_received + $item['quantity'];
                        $this->db->update('purchase_items', ['quantity_balance' => $balance, 'quantity_received' => $received, 'status' => 'received'], ['id' => $pi->id]);
                    }
                    $this->db->insert('stock_received_items', $item);
                    $this->updateAVCO(['product_id' => $item['product_id'], 'warehouse_id' => $data['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $base_unit_cost]);
                }
                $this->site->syncQuantity(null, $purchase_id); 
                $this-> set_purchase_status($purchase_id);
            }
            return true;
        }
        return false;
    }

    public function delete_stock_received($id) 
    {
        $o_str  = $this->getStockInByID($id);
        $o_stri = $this->getStockReceivedItems($id);
        if ($this->db->delete('stock_received', ['id' => $id]) && $this->db->delete('stock_received_items', ['stock_received_id' => $id])) {
            foreach ($o_stri as $oitem) {
                $pi = $this->getPurchaseItemByID($oitem->purchase_item_id);
                if ($pi) {
                    $this->updateAVCO(['product_id' => $oitem->product_id, 'warehouse_id' => $pi->warehouse_id, 'quantity' => (0 - $oitem->quantity), 'cost' => $pi->real_unit_cost]);
                    $balance  = $pi->quantity_balance  - $oitem->quantity;
                    $received = $pi->quantity_received - $oitem->quantity;
                    $this->db->update('purchase_items', ['quantity_balance' => $balance, 'quantity_received' => $received], ['id' => $pi->id]);
                }
            }
            $this->set_purchase_status($o_str->purchase_id);
            $this->site->syncQuantity(null, $o_str->purchase_id);
            return true;
        }
        return false;
    }

    public function set_purchase_status($id) 
    {
        $this->db->select("COALESCE(SUM({$this->db->dbprefix('purchase_items')}.quantity), 0) AS quantity, COALESCE(SUM({$this->db->dbprefix('purchase_items')}.quantity_received), 0) AS received");
        $this->db->where('purchase_items.purchase_id', $id);
        $this->db->group_by('purchase_items.purchase_id');
        $this->db->limit(1);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $result = $q->row();
            $quantity = $result->quantity;
            $received = $result->received;
            if ($received == 0) {
                $status = 'pending';
            } elseif ($quantity > $received && $received != 0) {
                $status = 'partial';
            } else {
                $status = 'received';
            }
            $this->db->update('purchases', ['status' => $status], ['id' => $id]);
        }
    }

    public function getAllStockInByPurchaseID($id) 
    {
        $this->db->select("{$this->db->dbprefix('stock_received')}.*, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) AS created_by")
            ->join('users', 'users.id=stock_received.created_by', 'left')
            ->where('purchase_id', $id)
            ->order_by('date');
        $q = $this->db->get('stock_received');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockInByID($id) 
    {
        $q = $this->db->get_where('stock_received', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getStockInItems($id)
    {
        $this->db->select('purchase_items.*, stock_received_items.quantity AS stock_received_qty, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit,products.other_cost, products.currency, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name,currencies.symbol as symbol')
            ->join('purchase_items', 'purchase_items.id=stock_received_items.purchase_item_id', 'left')
            ->join('products', 'products.id=purchase_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_items.tax_rate_id', 'left')
            ->join('currencies', 'currencies.code=products.currency', 'left')
            ->where('stock_received_id', $id)
            ->order_by('id', 'asc');

        $q = $this->db->get('stock_received_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStockReceivedItems($id)
    {
        $q = $this->db->get_where('stock_received_items', ['stock_received_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function checkStockReceived($id) 
    {
        $q = $this->db->get_where('stock_received', ['purchase_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }
    public function syncAPPurchasePayments($purchase_id)
    {
        $paid     = 0;
        $q = $this->db->get_where('payments_requested', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $paid += $row->amount;
            }
            return $paid;
        }
    }
    public function getRequestAPByID($id)
    {
        $q = $this->db->get_where('payments_requested', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }
    public function deleteAPRequestedPayment($id)
    {
        if ($this->db->delete('payments_requested', ['id' => $id])) {
            return true;
        }
        return false;
    }
    public function getProjectPlanByID($id)
    {
        $q = $this->db->get_where('projects_plan', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAllProjectPlanItems($purchase_id)
    {
        $this->db->select('projects_plan_items.*,
            projects_plan_items.quantity as quantity1,
            projects_plan_items.unit_quantity as unit_quantity1, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=projects_plan_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=projects_plan_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=projects_plan_items.tax_rate_id', 'left')
            ->group_by('projects_plan_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('projects_plan_items', array('project_plan_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function sysnceExpenseRequest($id = false){
        if($id > 0){
            $this->db->query("UPDATE ".$this->db->dbprefix('expense_requests')."
                            LEFT JOIN ".$this->db->dbprefix('expenses')." ON ".$this->db->dbprefix('expenses').".request_id = ".$this->db->dbprefix('expense_requests').".id 
                            SET ".$this->db->dbprefix('expense_requests').".`status` = IF ( ".$this->db->dbprefix('expenses').".id > 0, 'completed', 'approved') 
                            WHERE
                                ".$this->db->dbprefix('expense_requests').".id = ".$id."
                            ");
        }
    }
    public function getExpenseNames($term = false, $limit = false)
    {
        $limit = $this->Settings->rows_per_page;
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->where($this->db->dbprefix("expense_categories").".id NOT IN (SELECT parent_id FROM ".$this->db->dbprefix('expense_categories')." WHERE IFNULL(parent_id,0) > 0 GROUP BY parent_id)");
        $this->db->limit($limit);
        $q = $this->db->get('expense_categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getExpenseItems($id = false){
        $q = $this->db->get_where('expense_items',array('expense_id'=>$id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getExpenseRequestByID($id = false){
        $q = $this->db->get_where("expense_requests",array("id"=>$id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getExpenseRequestItems($id = false){
        $q = $this->db->get_where('expense_request_items',array('expense_request_id'=>$id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getExpensePayments($expense_id = false)
    {
        $this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
        $this->db->order_by('id', 'desc');
        $this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('expense_id' => $expense_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function addExpensePayment($data = [], $accTranPayments = array())
    {   
       
        if ($this->db->insert('payments', $data)) {
            //---------accounting-----
            $payment_id = $this->db->insert_id();
            if($accTranPayments){
                foreach($accTranPayments as $accTranPayment){
                    $accTranPayment['tran_no']= $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }
            $this->site->syncExpensePayments($data['expense_id']);
            $purchase = $this->getExpenseByID($data['expense_id']);
            if ($purchase->supplier_id && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                $this->db->update('companies', 
                            ['deposit_amount' => ($supplier->deposit_amount - $data['amount'])], 
                            ['id' => $purchase->supplier_id]);
            }
            return true;
        }
    }

    public function updateExpensePayment($id, $data = [],$accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);
        $purchase = $this->getExpenseByID($data['expense_id']);
        $supplier_id = $purchase->supplier_id;
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncExpensePayments($data['expense_id']);
            if ($opay->paid_by == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount + $opay->amount)], ['id' => $supplier->id]);
            }
            if ($supplier_id && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount - $data['amount'])], ['id' => $supplier_id]);
            }

            //=========Add Accounting========//
            $this->site->deleteAccTran('Payment',$id);
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
              //=========End Accounting========//
            return true;
        }
        return false;
    }

    public function deleteExpensePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->site->syncExpensePayments($opay->expense_id);
            if ($opay->paid_by == 'deposit') {
                $purchase = $this->getExpenseByID($opay->expense_id);
                $supplier = $this->site->getCompanyByID($purchase->supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount + $opay->amount)], ['id' => $supplier->id]);
            }
            return true;
        }
        return false;
    }

    public function getAllPurchaseItemsQty($purchase_id)
    {
        $this->db->select(" SUM({$this->db->dbprefix('purchase_items')}.quantity) as total_qty ");
        $this->db->limit(1);
        $q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            return $q->row()->total_qty;
        }
        return false;
    }

    public function getReceivByPurchaseID($purchase_id = false)
    {
        $q = $this->db->get_where("stock_received", array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getReceiveItemByReceiveID($stock_received_id = false)
    {
        $q = $this->db->get_where("stock_received_items", array("stock_received_id" => $stock_received_id));
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncPO($pu_id = false, $po_id = false)
    {
        if (!$po_id) {
            $po    = $this->getPurchaseByID($pu_id);
            $po_id = ($po ? $po->purchase_order_id : false);
        }
        if ($po_id) {
            $this->db->update("purchase_order_items", array("quantity_received" => 0), array("purchase_id" => $po_id));
            $this->db->select("product_id, sum(quantity) as quantity");
            $this->db->join("purchases", "purchases.id = purchase_items.purchase_id", "INNER");
            $this->db->where("purchases.purchase_order_id", $po_id);
            $this->db->group_by("product_id");
            $q = $this->db->get("purchase_items");
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $this->db->update("purchase_order_items", array("quantity_received" => $row->quantity), array("purchase_id" => $po_id, "product_id" => $row->product_id));
                }
            }
            $qu_balance = $this->getPOstatusByID($po_id);            
            if ($qu_balance->balance <= 0) {
                $status = array('order_status' => 'completed');
            } else if ($qu_balance->balance > 0 && $qu_balance->balance != $qu_balance->quantity) {
                $status = array('order_status' => 'partial');
            } else {
                $status = array('order_status' => 'pending');
            }
            $this->db->update('purchases_order', $status, array('id' => $po_id));
        }
    }

    public function syncPl($pu_id = false, $pl_id = false)
    {
        if (!$pl_id) {
            $po    = $this->getPurchaseByID($pu_id);
            $po_id = ($po ? $po->project_plan_id : false);
        }
        if ($pl_id) {
            $this->db->update("projects_plan_items", array("quantity_received" => 0), array("project_plan_id" => $pl_id));
            $this->db->select("product_id, sum(quantity) as quantity");
            $this->db->join("purchases", "purchases.id = purchase_items.purchase_id", "INNER");
            $this->db->where("purchases.project_plan_id", $pl_id);
            $this->db->group_by("product_id");
            $q = $this->db->get("purchase_items");
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $this->db->update("projects_plan_items", array("quantity_received" => $row->quantity), array("project_plan_id" => $pl_id, "product_id" => $row->product_id));
                }
            }
            $qu_balance = $this->getProPlanstatusByID($pl_id);            
            if ($qu_balance->balance <= 0) {
                $status = array('order_status' => 'completed');
            } else if($qu_balance->balance > 0 && $qu_balance->balance != $qu_balance->quantity) {
                $status = array('order_status' => 'partial');
            } else {
                $status = array('order_status' => 'pending');
            }
            $this->db->update('projects_plan', $status, array('id' => $pl_id));
        }
    }

    public function sysReceiveQuantity($purchase_id = NULL)
    {
        $items = $this->getReceiveItemsByPurchaseID($purchase_id);
        $purchase_status = 'received';
        $recieve_qty  = 0;
        $purchase_qty = 0;
        if ($items) {
            foreach ($items as $item) {
                $product_unit = $this->site->getProductUnit($item->product_id, $item->product_unit_id);
                if ($product_unit) {
                    $unit_qty = $product_unit->unit_qty;
                } else {
                    $unit_qty = 1;
                }
                $quantity_received = $item->received * $unit_qty;
                if ((float)$item->unit_quantity == $quantity_received) {
                    $item_status = 'received';
                } else if ($quantity_received > 0 && (float)$item->unit_quantity > $quantity_received) {
                    $item_status = 'partial';
                } else if ($quantity_received == 0) {
                    $item_status = 'pending';
                }
                $this->db->where('purchase_id', $item->purchase_id);
                $this->db->where('product_id', $item->product_id);
                $this->db->where('product_unit_id', $item->product_unit_id);
                $this->db->update("purchase_items", array("quantity_received" => ($quantity_received), "status" => ($item_status)));
                $recieve_qty  += ($item->received ? $item->received : 0);
                $purchase_qty += ($item->unit_quantity ? $item->unit_quantity : 0);
            }   
        }
        if ($recieve_qty == 0) {
            $purchase_status = 'pending';
        } else if ($purchase_qty > $recieve_qty) {
            $purchase_status = 'partial';
        }
        if ($purchase_status && $purchase_id) {
            $this->db->update('purchases', array('status' => $purchase_status), array('id' => $purchase_id));
        }
    }

    public function sysReceivePOQuantity($purchase_order_id = NULL)
    {
        $items = $this->getReceiveItemsByPOID($purchase_order_id);
        $recieve_qty = 0;
        $po_qty      = 0;
        foreach ($items as $item) {
            $product_unit = $this->site->getProductUnit($item->product_id, $item->product_unit_id);
            if ($product_unit) {
                $unit_qty = $product_unit->operation_value;
            } else {
                $unit_qty = 1;
            }
            $this->db->where('purchase_id', $item->purchase_order_id);
            $this->db->where('product_id', $item->product_id);
            $this->db->where('product_unit_id', $item->product_unit_id);
            $this->db->where('unit_price', $item->unit_price);
            $this->db->update("purchase_order_items", array("quantity_received" => ($item->received * $unit_qty)));
            $recieve_qty += ($item->received ? $item->received : 0);
            $po_qty += ($item->unit_quantity ? $item->unit_quantity : 0);
        }
        if ($recieve_qty == 0) { 
            $purchase_status = 'pending';
        } else if($po_qty > $recieve_qty) {
            $purchase_status = 'partial';
        } else {
            $purchase_status = 'completed';
        }
        if ($purchase_order_id) {
            $this->db->update('purchases_order', array('order_status' => $purchase_status), array('id' => $purchase_order_id));
        }
    }

    public function getReceiveItemsByPOID($purchase_order_id = NULL)
    {
        $q = $this->db->select('
                        purchase_order_items.*,purchase_order_items.real_unit_price as real_unit_cost, purchase_order_items.unit_price as unit_cost,
                        sum( '.$this->db->dbprefix('purchase_order_items').'.quantity ) AS quantity,
                        sum( '.$this->db->dbprefix('purchase_order_items').'.unit_quantity ) AS unit_quantity, 
                        receives.quantity AS received')
                    ->from('purchase_order_items')
                    ->join('purchases_order','purchase_order_items.purchase_id = purchases_order.id','inner')
                    ->join('(
                                SELECT
                                    purchase_order_id,
                                    product_id,
                                    product_unit_id,
                                    unit_cost,
                                    sum(unit_quantity) AS quantity
                                FROM
                                    '.$this->db->dbprefix('stock_received').'
                                LEFT JOIN '.$this->db->dbprefix('stock_received_items').' ON '.$this->db->dbprefix('stock_received').'.id = '.$this->db->dbprefix('stock_received_items').'.stock_received_id
                                WHERE '.$this->db->dbprefix('stock_received').'.purchase_order_id > 0
                                GROUP BY 
                                    product_id,
                                    unit_cost,
                                    purchase_order_id
                                ) as receives','receives.purchase_order_id = purchases_order.id
                                AND receives.unit_cost = purchase_order_items.unit_price
                                AND receives.product_unit_id = purchase_order_items.product_unit_id
                                AND receives.product_id = purchase_order_items.product_id', 'left')
                    ->where('purchase_order_items.purchase_order_id', $purchase_order_id)
                    ->group_by('purchase_order_items.product_id, purchase_order_items.purchase_order_id, purchase_order_items.unit_price, purchase_order_items.product_unit_id')
                    ->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $item) {
                $data[] = $item;
            }
        }
        return $data;
    }

    public function getReceiveItemsByPurchaseID($purchase_id = NULL)
    {
        $q = $this->db->select('
                        purchase_items.*,
                        sum( '.$this->db->dbprefix('purchase_items').'.quantity ) AS quantity,
                        sum( '.$this->db->dbprefix('purchase_items').'.unit_quantity ) AS unit_quantity, 
                        receives.quantity AS received')
                    ->from('purchase_items')
                    ->join('purchases','purchase_items.purchase_id = purchases.id','left')
                    ->join('(
                                SELECT
                                    purchase_id,
                                    product_id,
                                    product_unit_id,
                                    '.$this->db->dbprefix('stock_received_items').'.serial_no,
                                    sum(unit_quantity) AS quantity
                                FROM '.$this->db->dbprefix('stock_received').'
                                LEFT JOIN '.$this->db->dbprefix('stock_received_items').' ON '.$this->db->dbprefix('stock_received').'.id = '.$this->db->dbprefix('stock_received_items').'.stock_received_id
                                WHERE '.$this->db->dbprefix('stock_received').'.purchase_id > 0
                                GROUP BY 
                                    product_id,
                                    product_unit_id,
                                    purchase_id,
                                    IFNULL('.$this->db->dbprefix('stock_received_items').'.serial_no,"")
                            ) as receives', '
                            receives.purchase_id = purchases.id AND 
                            receives.product_unit_id = purchase_items.product_unit_id AND 
                            ('.$this->db->dbprefix('purchase_items').'.serial_no IS NULL OR '.$this->db->dbprefix('purchase_items').'.serial_no = receives.serial_no) AND 
                            receives.product_id = purchase_items.product_id', 'left')
                    ->where('purchase_items.purchase_id', $purchase_id)
                    ->group_by('
                        purchase_items.product_id,
                        purchase_items.purchase_id,
                        purchase_items.product_unit_id,
                        IFNULL('.$this->db->dbprefix('purchase_items').'.expiry, "0000-00-00"),
                        IFNULL('.$this->db->dbprefix('purchase_items').'.serial_no, "")
                    ')->get();                      
        if ($q->num_rows() > 0) {
            foreach($q->result() as $item){
                $data[] = $item;
            }
            return $data;
        }
       return false;
    }

    public function getReceiveByPurchaseID($purchase_id = false)
    {
        $this->db->where("purchase_id", $purchase_id);
        $q = $this->db->get_where("stock_received");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function synPurchaseCost($purchase_id = false)
    {
        $receives   = $this->getReceiveByPurchaseID($purchase_id);
        $purchase   = $this->getPurchaseByID($purchase_id);
        $receiveAcc = false;
        if ($this->Settings->accounting == 1) {
            $receiveAcc = $this->site->getAccountSettingByBiller($purchase->biller_id);
        }
        if ($receives) {
            foreach ($receives as $receive) {
                $receive_items = $this->getReceiveItemByReceiveID($receive->id);
                $accTrans = false;
                $total_prepaid = 0;
                $this->db->where("stock_received_id", $receive->id)->delete('stock_received_items');
                $this->db->delete('product_serials', array('receive_id' => $receive->id));
                $this->site->deleteStockmoves('Receives', $receive->id);
                $this->site->deleteAccTran('Receives', $receive->id);
                $products = false;
                foreach ($receive_items as $receive_item) {
                    $product_details = $this->getProductByID($receive_item->product_id);
                    $purchase_item   = $this->getPurchaseItemCosts($purchase_id,$receive_item->product_id,$receive_item->product_unit_id);
                    $freight         = $this->getPurchaseProductFreight($receive_item->product_id, $purchase_id);
                    $freight_cost    = ($freight ? $freight->freight_cost : 0);
                    $unit            = $this->site->getProductUnit($receive_item->product_id, $receive_item->product_unit_id);
                    $products = array(
                        'stock_received_id' => $receive->id,
                        'product_id'        => $receive_item->product_id,
                        'product_code'      => $receive_item->product_code,
                        'product_name'      => $receive_item->product_name,
                        'option_id'         => $receive_item->option_id,
                        'net_unit_cost'     => $purchase_item->net_unit_cost,
                        'unit_cost'         => $purchase_item->unit_cost,
                        'quantity'          => $receive_item->quantity,
                        'weight'            => $receive_item->weight,
                        'product_unit_id'   => $receive_item->product_unit_id,
                        'product_unit_code' => $receive_item->product_unit_code,
                        'unit_quantity'     => $receive_item->unit_quantity,
                        'warehouse_id'      => $receive_item->warehouse_id,
                        'item_tax'          => $receive_item->item_tax,
                        'tax_rate_id'       => $receive_item->tax_rate_id,
                        'tax'               => $receive_item->tax,
                        'discount'          => $receive_item->discount,
                        'item_discount'     => $receive_item->item_discount,
                        'subtotal'          => ($purchase_item->unit_cost * $receive_item->unit_quantity),
                        'real_unit_cost'    => $purchase_item->real_unit_cost,
                        'product_type'      => $receive_item->product_type,
                        'parent_id'         => $receive_item->parent_id,
                        'serial_no'         => $receive_item->serial_no,
                        'comment'           => $receive_item->comment,
                        'expiry'            => $receive_item->expiry,
                        'sup_qty'           => $receive_item->sup_qty,
                        'purchase_item_id'  => (isset($receive_item->purchase_item_id) ? $receive_item->purchase_item_id : null)
                    );
                    $this->db->insert('stock_received_items', $products);
                    $serial_no = null;
                    if ($receive_item->serial_no != '') {
                        $serial_no = $receive_item->serial_no;
                        $product_serial = $this->getProductReceiveSerial($serial_no, $receive_item->product_id, $receive->warehouse_id, $receive->id);
                        if (!$product_serial) {
                            $purchase_serial = $this->getProductSerialByReceiveID($serial_no, $receive->id);
                            if ($purchase_serial) {
                                $product_serials[] = array(
                                    'product_id' => $product_details->id,
                                    'warehouse_id' => $receive->warehouse_id,
                                    'date' => $receive->date,
                                    'serial' => $serial_no,
                                    'cost' => ($purchase_item->real_unit_cost + $freight_cost),
                                    'price' => $purchase_serial->price,
                                    'color' => $purchase_serial->color,
                                    'description' => $purchase_serial->description,
                                    'supplier_id' => $purchase->supplier_id,
                                    'supplier' => $purchase->supplier,
                                    'purchase_id' => $purchase->id,
                                    'receive_id' => $receive->id
                                );
                            } else {
                                $product_serials = array(
                                    'product_id' => $product_details->id,
                                    'warehouse_id' => $receive->warehouse_id,
                                    'date' => $receive->date,
                                    'serial' => $serial_no,
                                    'cost' => ($purchase_item->real_unit_cost + $freight_cost),
                                    'price' => $product_details->price,
                                    'supplier_id' => $purchase->supplier_id,
                                    'supplier' => $purchase->supplier,
                                    'purchase_id' => $purchase->id,
                                    'receive_id' => $receive->id
                                );
                            }
                            $serial_no = null;
                            $this->db->insert('product_serials', $product_serials);
                        }
                    }
                    $stockmove = array(
                        'transaction'    => 'Receives',
                        'transaction_id' => $receive->id,
                        'product_id'     => $receive_item->product_id,
                        'product_type'   => $receive_item->product_type,
                        'product_code'   => $receive_item->product_code,
                        'product_name'   => $receive_item->product_name,
                        'option_id'      => $receive_item->option_id,
                        'quantity'       => $receive_item->quantity,
                        'weight'         => $receive_item->weight,
                        'unit_quantity'  => $unit->unit_qty,
                        'unit_code'      => $receive_item->product_unit_code,
                        'unit_id'        => $receive_item->product_unit_id,
                        'warehouse_id'   => $receive->warehouse_id,
                        'expiry'         => $receive_item->expiry,
                        'date'           => $receive->date,
                        'serial_no'      => $serial_no,
                        'real_unit_cost' => ($purchase_item->real_unit_cost + $freight_cost),
                        'reference_no'   => $receive->re_reference_no,
                        'user_id'        => $receive->created_by
                    );
                    $this->db->insert('stock_movement', $stockmove);
                    if ($receiveAcc) {
                        $productAcc = $this->site->getProductAccByProductId($receive_item->product_id);
                        if($receive_item->sup_qty > 0 && $receive_item->unit_quantity > $receive_item->sup_qty){
                            $total_prepaid += ($purchase_item->unit_cost * $receive_item->sup_qty);
                            $adjustment_qty = $receive_item->unit_quantity - $receive_item->sup_qty;
                            $accTrans[] = array(
                                'tran_type' => 'Receives',
                                'tran_id' => $receive->id,
                                'tran_date' => $receive->date,
                                'reference_no' => $receive->re_reference_no,
                                'account_code' => $productAcc->adjustment_acc,
                                'amount' => ($purchase_item->unit_cost * $adjustment_qty) * (-1),
                                'narrative' => 'Product Code: '.$receive_item->product_code.'#'.'Qty: '.$adjustment_qty.'#'.'Cost: '.$purchase_item->unit_cost,
                                'description' => $receive->note,
                                'biller_id' => $purchase->biller_id,
                                'project_id' => $purchase->project_id,
                                'created_by' => $receive->created_by
                            );
                        } else {
                            $total_prepaid += ($purchase_item->unit_cost * $receive_item->unit_quantity);
                        }
                        $accTrans[] = array(
                            'tran_type'    => 'Receives',
                            'tran_id'      => $receive->id,
                            'tran_date'    => $receive->date,
                            'reference_no' => $receive->re_reference_no,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => ($purchase_item->unit_cost * $receive_item->unit_quantity),
                            'narrative'    => 'Product Code: '.$receive_item->product_code.'#'.'Qty: '.$receive_item->unit_quantity.'#'.'Cost: '.$purchase_item->unit_cost,
                            'description'  => $receive->note,
                            'biller_id'    => $purchase->biller_id,
                            'project_id'   => $purchase->project_id,
                            'created_by'   => $receive->created_by
                        );
                    }
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($receive_item->product_id, "Receives", $receive->id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($receive_item->product_id);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($receive_item->product_id);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($receive_item->product_id, "Receives", $receive->id);
                    }
                }
                if ($accTrans) {
                    $accTrans[] = array(
                        'tran_type'    => 'Receives',
                        'tran_id'      => $receive->id,
                        'tran_date'    => $receive->date,
                        'reference_no' => $receive->re_reference_no,
                        // 'account_code' => $receiveAcc->prepaid_acc,
                        'amount'       => -$total_prepaid,
                        'narrative'    => 'Purchase Prepaid',
                        'description'  => $receive->note,
                        'biller_id'    => $purchase->biller_id,
                        'project_id'   => $purchase->project_id,
                        'created_by'   => $receive->created_by
                    );
                    // $this->db->insert_batch('gl_trans', $accTrans);
                }
            }
        }
    }

    public function getPurchaseItemCosts($purchase_id = false, $product_id = false, $unit_id = false) 
    {
        $this->db->select("
                purchase_items.*, 
                (sum(unit_cost * unit_quantity) / sum(unit_quantity)) as unit_cost,
                (sum(net_unit_cost * unit_quantity) / sum(unit_quantity)) as net_unit_cost,
                (sum(real_unit_cost * quantity) / sum(quantity)) as real_unit_cost
            ");
        $this->db->where("purchase_id",$purchase_id);
        $this->db->where("product_id",$product_id);
        if ($unit_id) {
            $this->db->where("product_unit_id",$unit_id);
        }
        $q = $this->db->get("purchase_items");
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchaseProductFreight($product_id = false, $purchase_id = false, $receive_id= false)
    {
        if ($product_id && $purchase_id) {
            $this->db->select('(sum(cost * quantity) / sum(quantity)) as freight_cost');
            if ($receive_id) { 
                $this->db->where('(purchase_id="'.$purchase_id.'" OR receive_id="'.$receive_id.'")');
            } else {
                $this->db->where('purchase_id',$purchase_id);
            }
            $this->db->where('product_id',$product_id);
            $q = $this->db->get('purchase_shipping_items');
            if($q->num_rows() > 0){
                return $q->row();
            }
        }
        return false;
    }

    public function getProductSerial($serial = false, $product_id = false, $warehouse_id = false, $purchase_id = false)
    {
        if ($warehouse_id) {
            $this->db->where("warehouse_id", $warehouse_id);
        }
        if ($serial) {
            $this->db->where("serial", $serial);
        }
        if ($product_id) {
            $this->db->where("product_id", $product_id);
        }
        if ($purchase_id) {
            $this->db->where("IFNULL(purchase_id, 0) !=", $purchase_id);
        }
        $q = $this->db->get('product_serials');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;   
    }
    
    public function getProductReceiveSerial($serial = false, $product_id = false, $warehouse_id = false, $receive_id = false)
    {
        if($warehouse_id){
            $this->db->where("warehouse_id", $warehouse_id);
        }
        if($serial){
            $this->db->where("serial", $serial);
        }
        if($product_id){
            $this->db->where("product_id", $product_id);
        }
        if($receive_id){
            $this->db->where("IFNULL(receive_id, 0) !=", $receive_id);
        }
        
        $q = $this->db->get('product_serials');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;   
    }

    public function getProductSerialByPurchaseID($serial = false, $purchase_id = false)
    {
        $q = $this->db->get_where('product_serials', array('serial' => $serial, 'purchase_id' => $purchase_id));
        if($q->num_rows() > 0 ){
            return $q->row();
        }
        return false;
    }

    public function getProductSerialByReceiveID($serial = false, $receive_id = false)
    {
        $q = $this->db->get_where('product_serials', array('serial' => $serial, 'receive_id' => $receive_id));
        if ($q->num_rows() > 0 ) {
            return $q->row();
        }
        return false;
    }

    public function getPurchaseByPurchaseId($purchase_id = false)
    {
        $q = $this->db->get_where('purchases', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getRefPurchaseRC()
    {
        $this->db->select('id,reference_no');
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->db->where_in('purchases_order.warehouse_id',json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where('purchases_order.biller_id',$this->session->userdata('biller_id'));
        }
        $this->db->where_in('purchases_order.status', array('approved','partial'));
        $this->db->where('purchases_order.received !=', 2);
        $this->db->order_by('id','desc');
        $q = $this->db->get('purchases_order');
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addReceive($data = array(), $items = array())
    {
        $this->db->trans_start();
        if ($this->db->insert('stock_received', $data)) {
            $receive_id    = $this->db->insert_id();
            $this->site->updateReference('str');
            $accTrans      = false;
            $receiveAcc    = false;
            $total_prepaid = 0;
            if ($this->Settings->accounting == 1) {
                $receiveAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
            }

            foreach ($items as $item) {

                $item['stock_received_id'] = $receive_id;
                $reference_no = $item['reference_no'];
                $user_id      = $item['user_id'];
                $unit_qty     = $item['unit_qty'];
                unset($item['reference_no']);
                unset($item['user_id']);
                unset($item['unit_qty']);
                $this->db->insert('stock_received_items', $item);
                if ($data['purchase_id'] > 0) {
                    $purchase_id = $data['purchase_id'];
                } else {
                    $freight_cost = 0;
                    $serial_no    = null;
                    if ($item['serial_no'] != '') {
                        $serial_no = $item['serial_no'];
                        $product_serial = $this->getProductReceiveSerial($serial_no, $item['product_id'], $data['warehouse_id']);
                        if (!$product_serial) {
                            $product_details = $this->getProductByCode($item['product_code']);
                            $product_serials = array(
                                'product_id'   => $item['product_id'],
                                'warehouse_id' => $data['warehouse_id'],
                                'date'         => $data['date'],
                                'serial'       => $serial_no,
                                'cost'         => ($item['real_unit_cost'] + $freight_cost),
                                'price'        => $product_details->price,
                                'supplier_id'  => $data["supplier_id"],
                                'supplier'     => $data["supplier"],
                                'purchase_id'  => $data["purchase_id"],
                                'receive_id'   => $receive_id,
                            );
                            $serial_no = null;
                            $this->db->insert('product_serials', $product_serials);
                        }
                    }
                    $stockmove = array(
                        'transaction'    => 'Receives',
                        'transaction_id' => $receive_id,
                        'product_id'     => $item['product_id'],
                        'product_type'   => $item['product_type'],
                        'product_code'   => $item['product_code'],
                        'product_name'   => $item['product_name'],
                        'option_id'      => $item['option_id'],
                        'quantity'       => $item['quantity'],
                        'unit_quantity'  => $unit_qty,
                        'weight'         => $item['weight'],
                        'unit_code'      => $item['product_unit_code'],
                        'unit_id'        => $item['product_unit_id'],
                        'warehouse_id'   => $data['warehouse_id'],
                        'expiry'         => $item['expiry'],
                        'date'           => $data['date'],
                        'serial_no'      => $serial_no,
                        'real_unit_cost' => ($item['real_unit_cost'] + $freight_cost),
                        'reference_no'   => $reference_no,
                        'user_id'        => $user_id
                    );
                    $this->db->insert('stock_movement', $stockmove);
                    if ($receiveAcc) {
                        $productAcc = $this->site->getProductAccByProductId($item['product_id']);
                        $total_prepaid += ($item['unit_cost'] * $item['unit_quantity']);
                        $accTrans[] = array(
                            'tran_type'    => 'Receives',
                            'tran_id'      => $receive_id,
                            'tran_date'    => $data['date'],
                            'reference_no' => $data['re_reference_no'],
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => ($item['unit_cost'] * $item['unit_quantity']),
                            'narrative'    => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item['unit_quantity'].'#'.'Cost: '.$item['unit_cost'],
                            'description'  => $data['note'],
                            'biller_id'    => $data['biller_id'],
                            'project_id'   => $data['project_id'],
                            'created_by'   => $user_id
                        );
                    }
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($item['product_id'], "Receives", $receive_id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($item['product_id'], "Receives", $receive_id);
                    }
                    if ($cal_cost) {
                        if ($item['option_id']) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                        }
                    }
                }
            }
            if ($accTrans) {
                $accTrans[] = array(
                        'tran_type'    => 'Receives',
                        'tran_id'      => $receive_id,
                        'tran_date'    => $data['date'],
                        'reference_no' => $data['re_reference_no'],
                        // 'account_code' => $receiveAcc->prepaid_acc,
                        'amount'       => -$total_prepaid,
                        'narrative'    => 'Purchase Prepaid',
                        'description'  => $data['note'],
                        'biller_id'    => $data['biller_id'],
                        'project_id'   => $data['project_id'],
                        'created_by'   => $user_id
                );
                // $this->db->insert_batch('gl_trans', $accTrans);
            }
            if ($purchase_id > 0 ) {
                $this->sysReceiveQuantity($purchase_id);
                $this->synPurchaseCost($purchase_id);
                if ($data['si_reference_no'] != "") {
                    $this->db->update("purchases", array("si_reference_no" => $data['si_reference_no']), array("id" => $purchase_id));
                }
            } else {
                $this->sysReceivePOQuantity($data['purchase_order_id']);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the purchase (addReceive:Purchases_model.php)');
        } else {
            return $purchase_id;
        } 
        return false;
    }

    public function updateReceive($id = false, $data = array(), $items = array())
    {
        if ($this->db->where("id", $id)->update('stock_received', $data)) {
            $accTrans      = false;
            $receiveAcc    = false;
            $total_prepaid = 0;
            if ($this->Settings->accounting == 1) {
                $receiveAcc = $this->site->getAccountSettingByBiller($data['biller_id']);
            }
            $this->db->where("stock_received_id", $id)->delete('stock_received_items');
            $this->db->delete('product_serials', array('receive_id' => $id));
            $this->site->deleteStockmoves('Receives', $id);
            $this->site->deleteAccTran('Receives', $id);
            foreach ($items as $item) {
                $item['stock_received_id'] = $id;
                $reference_no = $item['reference_no'];
                $user_id      = $item['user_id'];
                $unit_qty     = $item['unit_qty'];
                unset($item['reference_no']);
                unset($item['user_id']);
                unset($item['unit_qty']);
                $this->db->insert('stock_received_items', $item);
                if ($data['purchase_id'] > 0) {
                    $purchase_id = $data['purchase_id'];
                } else {
                    $freight_cost = 0;
                    $serial_no    = null;
                    if ($item['serial_no']!='') {
                        $serial_no = $item['serial_no'];
                        $product_serial = $this->getProductReceiveSerial($serial_no,$item['product_id'],$data['warehouse_id'],$id);
                        if (!$product_serial) {
                            $purchase_serial = $this->getProductSerialByReceiveID($serial_no,$id);
                            $product_details = $this->getProductByCode($item['product_code']);
                            if($purchase_serial){
                                $product_serials[] = array(
                                    'product_id'   => $product_details->id,
                                    'warehouse_id' => $data['warehouse_id'],
                                    'date'         => $data['date'],
                                    'serial'       => $serial_no,
                                    'cost'         => ($item['real_unit_cost'] + $freight_cost),
                                    'price'        => $purchase_serial->price,
                                    'color'        => $purchase_serial->color,
                                    'description'  => $purchase_serial->description,
                                    'supplier_id'  => $data["supplier_id"],
                                    'supplier'     => $data["supplier"],
                                    'purchase_id'  => $data["purchase_id"],
                                    'receive_id'   => $id,
                                );
                            } else {
                                $product_serials = array(
                                    'product_id'   => $item['product_id'],
                                    'warehouse_id' => $data['warehouse_id'],
                                    'date'         => $data['date'],
                                    'serial'       => $serial_no,
                                    'cost'         => ($item['real_unit_cost'] + $freight_cost),
                                    'price'        => $product_details->price,
                                    'supplier_id'  => $data["supplier_id"],
                                    'supplier'     => $data["supplier"],
                                    'purchase_id'  => $data["purchase_id"],
                                    'receive_id'   => $id,
                                );
                            }
                            $serial_no = null;
                            $this->db->insert('product_serials', $product_serials);
                        }
                    }
                    $stockmove = array(
                        'transaction'    => 'Receives',
                        'transaction_id' => $item['receive_id'],
                        'product_id'     => $item['product_id'],
                        'product_type'   => $item['product_type'],
                        'product_code'   => $item['product_code'],
                        'product_name'   => $item['product_name'],
                        'option_id'      => $item['option_id'],
                        'quantity'       => $item['quantity'],
                        'weight'         => $item['weight'],
                        'unit_quantity'  => $unit_qty,
                        'unit_code'      => $item['product_unit_code'],
                        'unit_id'        => $item['product_unit_id'],
                        'warehouse_id'   => $data['warehouse_id'],
                        'expiry'         => $item['expiry'],
                        'date'           => $data['date'],
                        'serial_no'      => $serial_no,
                        'real_unit_cost' => ($item['real_unit_cost'] + $freight_cost),
                        'reference_no'   => $reference_no,
                        'user_id'        => $user_id
                    );
                    $this->db->insert('stock_movement', $stockmove);
                    if ($receiveAcc) {
                        $productAcc = $this->site->getProductAccByProductId($item['product_id']);
                        $total_prepaid += ($item['unit_cost'] * $item['unit_quantity']);
                        $accTrans[] = array(
                            'tran_type'    => 'Receives',
                            'tran_id'      => $id,
                            'tran_date'    => $data['date'],
                            'reference_no' => $data['re_reference_no'],
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount'       => ($item['unit_cost'] * $item['unit_quantity']),
                            'narrative'    => 'Product Code: '.$product_details->code.'#'.'Qty: '.$item['unit_quantity'].'#'.'Cost: '.$item['unit_cost'],
                            'description'  => $data['note'],
                            'biller_id'    => $data['biller_id'],
                            'project_id'   => $data['project_id'],
                            'created_by'   => $user_id
                        );
                    }
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($item['product_id'], "Receives", $id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($item['product_id']);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($item['product_id'], "Receives", $id);
                    }
                    if ($cal_cost) {
                        if ($item['option_id']) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                        }
                    }
                }
            }
            if ($accTrans) {
                $accTrans[] = array(
                    'transaction'      => 'Receives',
                    'transaction_id'   => $id,
                    'transaction_date' => $data['date'],
                    'reference'        => $data['re_reference_no'],
                    // 'account'          => $receiveAcc->prepaid_acc,
                    'amount'           => -$total_prepaid,
                    'narrative'        => 'Purchase Prepaid',
                    'description'      => $data['note'],
                    'biller_id'        => $data['biller_id'],
                    'project_id'       => $data['project_id'],
                    'user_id'          => $user_id
                );
                // $this->db->insert_batch('acc_tran', $accTrans);
            }
            if ($purchase_id > 0 ) {
                $this->sysReceiveQuantity($purchase_id);
                $this->synPurchaseCost($purchase_id);
                if ($data['si_reference_no'] != "") {
                    $this->db->update("purchases", array("si_reference_no" => $data['si_reference_no']), array("id" => $purchase_id));
                }
            } else {
                $this->sysReceivePOQuantity($data['purchase_order_id']);
            }
            return true;
        }
        return false;
    }

    public function deleteReceive($id = false)
    {
        if ($id && $id > 0) {
            $receive       = $this->getReceiveByID($id);
            $purchases     = $this->getPurchaseByReceiveId($id);
            $receive_items = $this->getReceiveItemByReceiveID($id);
            if ($this->db->where("id", $id)->delete("stock_received")) {
                $this->db->where('stock_received_id', $id)->delete("stock_received_items");
                $this->db->delete('product_serials', array('receive_id' => $id));
                $this->site->deleteAccTran('Receives', $id);
                if ($receive->purchase_id > 0) {
                    $this->sysReceiveQuantity($receive->purchase_id);
                } else {
                    $this->sysReceivePOQuantity($receive->purchase_order_id);
                }
                $this->site->deleteStockmoves('Receives', $id);
                foreach ($receive_items as $oitem) {
                    if ($this->Settings->accounting_method == '2') {
                        $cal_cost = $this->site->updateAVGCost($oitem->product_id);
                    } else if ($this->Settings->accounting_method == '1') {
                        $cal_cost = $this->site->updateLifoCost($oitem->product_id);
                    } else if ($this->Settings->accounting_method == '0') {
                        $cal_cost = $this->site->updateFifoCost($oitem->product_id);
                    } else if ($this->Settings->accounting_method == '3') {
                        $cal_cost = $this->site->updateProductMethod($oitem->product_id);
                    }
                    if ($cal_cost) {
                        if ($oitem->option_id) {
                            $this->db->update('product_variants', array('cost' => $cal_cost), array('id' => $oitem->option_id, 'product_id' => $oitem->product_id));
                        }
                    }
                }
                if ($purchases) {
                    foreach ($purchases as $purchase) {
                        $this->db->delete('purchases', array('id' => $purchase->id));
                        $pur_payments = $this->getPurchasePayments($purchase->id);
                        if ($pur_payments) {
                            $this->db->delete('payments', array('purchase_id' => $purchase->id));
                            foreach ($pur_payments as $pur_payment) {
                                $this->site->deleteAccTran('Payment', $pur_payment->id);
                            }
                        }
                    }
                }
                return true;
            }
        }
        return FALSE;
    }

    public function getReceiveByID($id = NULL)
    {
        $q = $this->db->get_where("stock_received", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllReceiveItems($receive_id = NULL)
    {
        $this->db->select("
                    stock_received_items.*, 
                    COALESCE((
                        SELECT SUM(quantity) 
                        FROM {$this->db->dbprefix('stock_received_items')} ri 
                        WHERE ri.stock_received_id = {$this->db->dbprefix('stock_received')}.id AND ri.purchase_item_id = {$this->db->dbprefix('purchase_items')}.id
                        GROUP BY ri.purchase_item_id 
                        LIMIT 1
                    ), 0) AS tqty,
                    tax_rates.code as tax_code, 
                    tax_rates.name as tax_name,
                    tax_rates.rate as tax_rate, 
                    products.unit, 
                    products.details as details, 
                    product_variants.name as variant,
                    units.name as unit_name,
                    ({$this->db->dbprefix('purchase_items')}.unit_quantity - {$this->db->dbprefix('purchase_items')}.quantity_received) AS purchase_qty,
                    purchase_items.id as purchase_item_id
                ")
            ->join('stock_received','stock_received.id = stock_received_items.stock_received_id', 'inner')
            ->join('purchase_items','purchase_items.purchase_id = stock_received.purchase_id
                    AND purchase_items.product_unit_id = stock_received_items.product_unit_id
                    AND purchase_items.product_id = stock_received_items.product_id', 'inner')
            ->join('products', 'products.id=stock_received_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=stock_received_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=stock_received_items.tax_rate_id', 'left')
            ->join('units', 'units.id=stock_received_items.product_unit_id', 'left')
            ->where('stock_received_items.unit_quantity !=',0)
            ->group_by('stock_received_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('stock_received_items', array('stock_received_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getAllReceivePOItems($receive_id = NULL)
    {
        $this->db->select('
                    stock_received_items.*, 
                    tax_rates.code as tax_code, 
                    tax_rates.name as tax_name,
                    tax_rates.rate as tax_rate, 
                    products.unit, 
                    products.details as details, 
                    product_variants.name as variant,
                    units.name as unit_name,
                    ('.$this->db->dbprefix('purchase_order_items').'.unit_quantity - '.$this->db->dbprefix('purchase_order_items').'.quantity_received) AS purchase_qty')
            ->join('receives','stock_received.id = stock_received_items.stock_received_id','inner')
            ->join('purchase_order_items','purchase_order_items.purchase_order_id = stock_received.purchase_order_id
            AND purchase_order_items.unit_price = stock_received_items.unit_cost
            AND purchase_order_items.product_unit_id = stock_received_items.product_unit_id
            AND purchase_order_items.product_id = stock_received_items.product_id','inner')
            ->join('products', 'products.id=stock_received_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=stock_received_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=stock_received_items.tax_rate_id', 'left')
            ->join('units', 'units.id=stock_received_items.product_unit_id', 'left')
            ->where('stock_received_items.unit_quantity !=',0)
            ->group_by('stock_received_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('stock_received_items', array('stock_received_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getPurchaseByReceiveId($receive_id = false)
    {
        $q = $this->db->get_where('purchases', array('receive_id' => $receive_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
}