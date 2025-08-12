<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Loan_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	//--------------loan--------
	public function getInvoicePayments_loan($sale_id)
    {
        $this->db->order_by('loan_id', 'asc');
        $q = $this->db->get_where('loan_payment', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getPayments_step_payment($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('step_payment', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function delete_loan($id)
    {
        if ($this->db->delete('loan_payment', array('sale_id' => $id))) {
            return true;
        }
        return FALSE;
    }
	public function getInvoiceByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function get_commission_ByID($id)
    {
		
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function getloanByID($id)
    {
		$this->db->select('loan_payment.sale_id as sale_id, register_date as date,
						name,
						loan_payment.reference as refer,
                        sales.reference_no as reference_no,
						SUM('.$this->db->dbprefix('loan_payment').'.paid) as paid,
						SUM(monthly_payment) as loan_payment,
						monthly_payment,
						SUM(interest) as total_interest,
                        loan_payment.pay_date as pay_date,
						IF(SUM('.$this->db->dbprefix('loan_payment').'.paid) >= SUM(monthly_payment), "completed", "due") as status
						')
                ->join('companies', 'companies.id=loan_payment.customer_id', 'left')
                ->join('sales', 'sales.id=loan_payment.sale_id', 'left')
                ->group_by('loan_payment.sale_id');

        $q = $this->db->get_where('loan_payment', array('loan_payment.sale_id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getAllInvoiceItems($sale_id, $return_id = NULL)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
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
        return FALSE;
    }
    public function getAllLoanByUserId($status, $return_id = NULL){
        $date1 = date('Y-m-d', strtotime('+7 day'));
        $date2 = date('Y-m-d');
        $this->db->select('loan_payment.*, name')
            ->join('companies', 'companies.id = loan_payment.customer_id')
            ->group_by('loan_id')->order_by('pay_date', 'asc');
        if($status == 'alert'){
            $this->db->where('pay_date <=', $date1);
        }else if($status == 'exp_alert'){
            $this->db->where('pay_date', $date2);
        }else if($status == 'late_exp'){
            $this->db->where('pay_date <', $date2);
        }else{
            $this->db->where('pay_date !=', null);
        }
        $q = $this->db->get('loan_payment');
        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function updatePaymentLoan($payment = array(), $customer_id = null){
        $csize = sizeof($payment);
            for ($i=0; $i < $csize; $i++) { 
                $sd = $payment[$i];
                $this->updatePayment_loan($sd['id'], $payment[$i], $customer_id);
            }
        return true;
    }
    public function updatePayment_loan($id = null, $data = array(), $customer_id = null)
    {
       $opay = $this->getPaymentByID($id);

        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->site->syncSalePayments($data['sale_id']);
            // $this->site->deleteAccTran('Payment',$id);
            $this->site->deleteAccTranPayment('Payment',$id);
            
            if(isset($accTranPayments)){
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
    public function addPaymentLoan($payment = array(), $customer_id = null){
        $csize = sizeof($payment);
            for ($i=0; $i < $csize; $i++) { 
               $this->addPayment_loan($payment[$i], $customer_id);
            }
        return true;
    }
    public function addPayment_loan($payment = array(), $customer_id = null)
    {
        if (isset($payment['sale_id']) && isset($payment['paid_by']) && isset($payment['amount'])) {
            $payment['pos_paid'] = $payment['amount'];
            $inv = $this->getInvoiceByID($payment['sale_id']);
            $paid = $inv->paid + $payment['amount'];
            if ($payment['paid_by'] == 'ppp') {
                $card_info = array("number" => $payment['cc_no'], "exp_month" => $payment['cc_month'], "exp_year" => $payment['cc_year'], "cvc" => $payment['cc_cvv2'], 'type' => $payment['cc_type']);
                $result = $this->paypal($payment['amount'], $card_info);
                if (!isset($result['error'])) {
                    $payment['transaction_id'] = $result['transaction_id'];
                    $payment['date'] = $this->bpas->fld($result['created_at']);
                    $payment['amount'] = $result['amount'];
                    $payment['currency'] = $result['currency'];
                    unset($payment['cc_cvv2']);
                    $this->db->insert('payments', $payment);
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
        }else {
                if ($payment['paid_by'] == 'gift_card') {
                    $gc = $this->site->getGiftCardByNO($payment['cc_no']);
                    $this->db->update('gift_cards', array('balance' => ($gc->balance - $payment['amount'])), array('card_no' => $payment['cc_no']));
                } elseif ($customer_id && $payment['paid_by'] == 'deposit') {
                    $customer = $this->site->getCompanyByID($customer_id);
                    $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payment['amount'])), array('id' => $customer_id));
                }
                unset($payment['cc_cvv2']);
                $this->db->insert('payments', $payment);
                $paid += $payment['amount'];
        }
            $pid= $this->db->insert_id();
            if (!isset($msg)) {
                $this->site->updateReference('pay');
                $this->site->syncSalePayments($payment['sale_id']);
                return array('status' => 1, 'msg' => '', 'payment_id' => $pid);
            }
            return array('status' => 0, 'msg' => $msg);

        }
        return false;
    }
    public function addPayment_step($payment = array(), $customer_id = null)
    {
         if (isset($payment['amount'])) {
            $payment['pos_paid'] = $payment['amount'];
       
            unset($payment['cc_cvv2']);
            $this->db->insert('payments', $payment);
          
            $pid= $this->db->insert_id();
            if (!isset($msg)) {
                $this->site->updateReference('pay');               
                $this->site->syncSalePayments($payment['sale_id']);
                return array('status' => 1, 'msg' => '', 'payment_id' => $pid);
            }
            return array('status' => 0, 'msg' => $msg);

        }
        return false;
    }
     public function getPaymentsByLoanID($id)
    {
        $q = $this->db->get_where('payments', array('loan_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPaymentByLoanID($id)
    {
        $q = $this->db->get_where('payments', array('loan_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getLoanPaymentByID($id)
    {
        $this->db->select('payments.date, payments.amount as paid_amount,loan_payment.*');
        $this->db->join('loan_payment', 'payments.loan_id=loan_payment.loan_id');
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getStepPaymentByID($id)
    {
        $q = $this->db->get_where('step_payment', array('sale_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getListPaymentByID($id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('step_payment', ['sale_id' => $id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function update_step($id)
    {
        $q = $this->db->get_where('step_payment', array('id' => $id));
        if ($q->num_rows() > 0) {
            $result = $q->row();
            $data = array(
                'paid' => $result->monthly_payment,
                'status' => 'paid'
                );
            if($this->db->update('step_payment', $data, array('id' => $id))) {
                return true;
            }
        }

        return false;
    }
    public function getlistStepByID($step_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('step_payment', array('id' => $step_id));
        if ($q->num_rows() > 0) {
            return  $q->row();
        }
    }
    public function getStepByID($id)
    {
        $q = $this->db->get_where('step_payment', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function cancel_update_step($id)
    {
        

        $q = $this->db->get_where('step_payment', array('id' => $id));
        if ($q->num_rows() > 0) {
            $result = $q->row();
            $data = array(
                'paid' => 0,
                'status' => 'pending'
                );
            if($this->db->update('step_payment', $data, array('id' => $id))) {
                return true;
            }
            
        }

        return false;
    }
    public function getPaymentLoanByID($id)
    {
        $q = $this->db->get_where('payments', ['loan_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getPaymentStepByID($id)
    {
        $q = $this->db->get_where('payments', ['step_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getAllScheduleByUser($status, $start = NULL,$end=null){
        $date1 = date('Y-m-d', strtotime('+1 month'));
        $date2 = date('Y-m-d');
        $this->db->select('loan_payment.*, name')
            ->join('companies', 'companies.id = loan_payment.customer_id')
            ->group_by('loan_id')
            ->order_by('pay_date', 'asc');

        if($status == 'alert'){
            $this->db->where('pay_date <=', $date1);
        }else if($status == 'exp_alert'){
            $this->db->where('pay_date', $date2);
        }else if($status == 'late_exp'){
            $this->db->where('pay_date <', $date2);
        }else{
            $this->db->where('pay_date !=', null);
        }
        if($start){
            $this->db->where('pay_date BETWEEN ' . $start . ' and ' . $end);
        }
        $q = $this->db->get('loan_payment');

        if($q->num_rows() > 0){
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }

        return FALSE;
    }
}
