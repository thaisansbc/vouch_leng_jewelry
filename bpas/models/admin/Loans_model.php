<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Loans_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	
	public function deleteBorrower($id = NULL)
	{
		if($this->db->where("id",$id)->delete("loan_borrowers")){
			return true;
		}
		return false;
	}
	
	public function addBorrower($data = FALSE)
	{
		if($this->db->insert("loan_borrowers", $data)){
			return true;
		}
		return false;
	}
	
	public function updateBorrower($id =false, $data = FALSE)
	{
		if($this->db->where("id",$id)->update("loan_borrowers", $data)){
			return true;
		}
		return false;
	}
	
	public function getBorrowerSuggestions($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select("id, CONCAT(code, ' - ',first_name,' ', last_name) as text", FALSE);
        $this->db->where(" (code LIKE '%" . $term . "%' OR last_name LIKE '%" . $term . "%' OR first_name LIKE '%" . $term . "%') ");
        $this->db->where("type","customer");
		$q = $this->db->get('loan_borrowers', $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getAllBorrowerTypes()
	{
		$q = $this->db->get("loan_borrower_types");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getBorrowerTypeByID($id = false)
	{
		$q = $this->db->get_where("loan_borrower_types", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
    public function getBorrowerByID($id = false, $type = null)
	{
		if($type){
			$this->db->where("type", $type);
		}
		$q = $this->db->get_where("loan_borrowers", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function deleteBorrowerType($id = NULL)
	{
		if($this->db->where("id",$id)->delete("loan_borrower_types")){
			return true;
		}
		return false;
	}
	
	public function addBorrowerType($data = FALSE)
	{
		if($this->db->insert("loan_borrower_types", $data)){
			return true;
		}
		return false;
	}
	
	public function updateBorrowerType($id =false, $data = FALSE)
	{
		if($this->db->where("id",$id)->update("loan_borrower_types", $data)){
			return true;
		}
		return false;
	}
	
	public function getFrequencies()
    {
        $q = $this->db->order_by('day','asc')->get('frequency');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


	public function getLoanProducts()
	{
		$q = $this->db->get("loan_products");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getLoanByID($id = false)
	{
		$q = $this->db->get_where("loans", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function addSchedule($id = false, $items = array())
	{
		if($this->db->where("id", $id)->update("loans",array("status" => "active"))){
			if($items){
				foreach($items as $item){
					$item['loan_id'] = $id;
					$this->db->insert("loan_items", $item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateSchedule($id = false, $data = array(), $items = array())
	{
		if($this->db->where("id", $id)->update("loans",$data)){
			$this->db->where("loan_id", $id)->where("status", 'pending')->delete("loan_items");
			foreach($items as $item){
				$item['loan_id'] = $id;
				$this->db->insert("loan_items", $item);
			}
			return true;
		}
		return false;
	}
	
	public function getLoanItemsByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("loan_items");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getLoanItemsByLoanID($id = false)
	{
		$q = $this->db->where('loan_id', $id)->get('loan_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getPaymentByLoanItemID($id = false)
	{
		$q = $this->db->where('loan_item_id', $id)->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addPayment($payments = array(), $accTrans = array())
    {
    
        if ($this->db->insert('payments', $payments)) {
			$insert_id = $this->db->insert_id();
			$this->syncLoanByID($payments['loan_id']);
			$this->syncLoanPayments($payments['loan_item_id']);
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id']= $insert_id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
			return true;
        }
        return false;
    }
	
	public function syncLoanByID($id = false) 
	{
		$amount_paid = 0;
		$payments = $this->db->get_where("payments", array("loan_id" => $id, "type" => "received"))->result();
		foreach($payments as $payment){
			$amount_paid += (double)$payment->amount + (double)$payment->interest_paid + (double)$payment->fee_charge + (double)$payment->penalty_paid;
		}
		$amount_payment = 0;
		$loan_items = $this->db->get_where("loan_items", array("loan_id" => $id))->result();
		foreach($loan_items as $item){
			$amount_payment += (double)$item->payment + (double)$item->fee_charge + (double)$item->penalty;
		}
		if ($this->bpas->formatDecimal($amount_paid) >= $this->bpas->formatDecimal($amount_payment)) {
			$status = 'completed';
		}else{
			$status = 'active';
		}
		if ($this->db->update('loans', array('status' => $status), array('id' => $id))) {
			return true;
		}
		return false;
	}
	
	public function syncLoanPayments($id = false) 
	{
		$loan_item = $this->getLoanItemsByID($id);
		$payments = $this->getPaymentByLoanItemID($id);
		$paid = 0;
		$total_payment = ($loan_item->payment + $loan_item->fee_charge + $loan_item->penalty);
		foreach ($payments as $payment) {
			$paid += $this->bpas->formatDecimal($payment->amount+$payment->interest_paid+$payment->fee_charge+$payment->penalty_paid);
		}
		$status = $paid == 0 ? 'pending' : $loan_item->status;
		if ($this->bpas->formatDecimal($total_payment) == $this->bpas->formatDecimal($paid)) {
			$status = 'paid';
		}elseif ($paid != 0) {
			$status = 'partial';
		}else{
			$status = 'pending';
		}
		if ($this->db->update('loan_items', array('paid' => $paid, 'status' => $status), array('id' => $loan_item->id))) {
			return true;
		}
		return FALSE;
	}
	
	public function getPaymentByID($id = false)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function deletePayment($id = false)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
			$this->syncLoanByID($opay->loan_id);
			$this->syncLoanPayments($opay->loan_item_id);
			
			$this->site->deleteAccTran('Payment',$id);
            return true;
        }
        return FALSE;
    }

	public function updatePayment($id = false, $payments = array(), $accTrans = array())
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $payments, array('id' => $id))) {
			$this->syncLoanByID($opay->loan_id);
			$this->syncLoanPayments($opay->loan_item_id);
			$this->site->deleteAccTran('Payment',$id);
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id']= $id;
					$this->db->insert('acc_tran', $accTran);
				}
			}
            return true;
        }
        return false;
    }

	public function addMultiPayment($data = array(), $accTranPayments= array())
	{
		if($data){
			foreach($data as $row){
				$this->db->insert('payments',$row);
				$payment_id = $this->db->insert_id();
				$this->syncLoanPayments($row['loan_item_id']);
				$this->syncLoanByID($row['loan_id']);
				$accTrans = $accTranPayments[$row['loan_item_id']];
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['transaction_id'] = $payment_id;
						$this->db->insert('acc_tran',$accTran);
					}
				}
			}
			return true;
		}
		return false;
	}

	public function getMultiLoansByID($id = false)
    {
		$this->db->select('
					loan_items.id,
					loan_items.loan_id,
					loan_items.deadline,
					loan_items.interest,
					loan_items.principal,
					loan_items.period,
					loan_items.fee_charge,
					loan_items.penalty,
					loan_items.payment,
					IFNULL(bpas_payments.paid,0) as paid, 
					IFNULL(bpas_payments.interest_paid,0) as interest_paid,
					IFNULL(bpas_payments.fee_charge,0) as fee_charge_paid,
					IFNULL(bpas_payments.penalty_paid,0) as penalty_paid,
					loans.currency,
					loans.status')
		->join('loans','loans.id=loan_items.loan_id','left')
		->join('(SELECT
					loan_item_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(fee_charge),0) AS fee_charge,
					IFNULL(sum(interest_paid),0) AS interest_paid,
					IFNULL(sum(penalty_paid),0) AS penalty_paid
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					loan_item_id) as bpas_payments', 'bpas_payments.loan_item_id=loan_items.id', 'left');
		$this->db->where_in('loan_items.id',$id);
		$this->db->where('loan_items.status !=','paid');
		$this->db->order_by('loan_items.deadline');
        $q = $this->db->get('loan_items');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
    }

	public function getLoanBalanceByID($id = false)
    {
		$this->db->select('
					loan_items.id,
					loan_items.loan_id,
					loan_items.deadline,
					loan_items.interest,
					loan_items.principal,
					loan_items.period,
					loan_items.fee_charge,
					loan_items.penalty,
					loan_items.payment,
					IFNULL(bpas_payments.paid,0) as paid, 
					IFNULL(bpas_payments.interest_paid,0) as interest_paid,
					IFNULL(bpas_payments.fee_charge,0) as fee_charge_paid,
					IFNULL(bpas_payments.penalty_paid,0) as penalty_paid')
		->join('(SELECT
					loan_item_id,
					IFNULL(sum(amount),0) AS paid,
					IFNULL(sum(fee_charge),0) AS fee_charge,
					IFNULL(sum(interest_paid),0) AS interest_paid,
					IFNULL(sum(penalty_paid),0) AS penalty_paid
				FROM
					'.$this->db->dbprefix('payments').'
				GROUP BY
					loan_item_id) as bpas_payments', 'bpas_payments.loan_item_id=loan_items.id', 'left');
		$this->db->where('loan_items.id',$id);
		$this->db->where('loan_items.status !=','paid');
		$this->db->order_by('loan_items.deadline');
        $q = $this->db->get('loan_items');
		
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllLoanItemsByLoanID($id = false)
    {
        $q = $this->db->get_where('loan_items', array("loan_id"=>$id));
        if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[]= $row;
			}
            return $data;
        }
        return false;
    }
	
	public function deleteLoanProduct($id = NULL)
	{
		if($this->db->where("id", $id)->delete("loan_products")){
			return true;
		}
		return false;
	}
	
	public function addLoanProduct($data = array())
	{
		if($this->db->insert("loan_products", $data)){
			return true;
		}
		return false;
	}
	
	public function updateLoanProduct($id = false, $data = array())
	{
		if($this->db->where("id", $id)->update("loan_products", $data)){
			return true;
		}
		return false;
	}
	
	public function getLoanProductByID($id = false)
	{
		$q=$this->db->where("id", $id)->get("loan_products");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function addCollateral($data = array())
	{
		if($this->db->insert("loan_collaterals", $data)){
			return true;
		}
		return false;
	}
	
	public function deleteCollateral($id = null)
	{
		if($this->db->where("id", $id)->delete("loan_collaterals")){
			return true;
		}
		return false;
	}
	
	public function updateCollateral($id = null, $data = array())
	{
		if($this->db->where("id", $id)->update("loan_collaterals", $data)){
			return true;
		}
		return false;
	}
	
	public function getCollateralByID($id = null)
	{
		$q = $this->db->where("id", $id)->get("loan_collaterals");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function deleteCharge($id = null)
	{
		if($this->db->where("id", $id)->delete("loan_charges")){
			return true;
		}
		return false;
	}
	
	public function addCharge($data = array())
	{
		if($this->db->insert("loan_charges", $data)){
			return true;
		}
		return false;
	}
	
	public function getChargeByID($id = null)
	{
		$q = $this->db->where("id", $id)->get("loan_charges");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function updateCharge($id = false, $data = array())
	{
		if($this->db->where("id", $id)->update("loan_charges", $data)){
			return true;
		}
		return false;
	}
	
	public function getChargeNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		$this->db->like("name", $term)->limit($limit);
        $q = $this->db->get('loan_charges');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getLoanChargeByIDs($ids = array())
	{
		if($ids){
			$this->db->where_in("id", $ids);
		}else{
			$this->db->where("id", 0);
		}
		$q = $this->db->get("loan_charges");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getFeeCharge($ids = array(), $type = 0)
	{
		if($ids){
			$this->db->where_in("id", $ids);
		}else{
			$this->db->where("id", 0);
		}
		$q = $this->db->where("type",$type)->get("loan_charges");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addWorkingStatus($data = FALSE)
	{
		if($this->db->insert("loan_working_status", $data)){
			return true;
		}
		return false;
	}
	
	public function getWorkingStatus()
	{
		$q = $this->db->get("loan_working_status");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
	}
	
	public function getWorkingStatusByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("loan_working_status");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
	}

	public function deleteWorkingStatus($id = NULL)
	{
		if($this->db->where("id",$id)->delete("loan_working_status")){
			return true;
		}
		return false;
	}
	
	public function updateWorkingStatus($id =false, $data = FALSE)
	{
		if($this->db->where("id",$id)->update("loan_working_status", $data)){
			return true;
		}
		return false;
	}

	public function getLocations($parent_id = null, $type = null)
	{
		$q = $this->db->where("type", $type)->where("parent_id", $parent_id)->get("locations");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;			  
	}
	
	public function getLocationByID($id = null)
	{
		$q = $this->db->where("id", $id)->get("locations");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;			  
	}
	
	public function addApplication($data = array())
	{	
		if($this->db->insert("loan_applications",$data)){
			$insert_id = $this->db->insert_id();
			return $insert_id;
		}
		return false;
	}
	
	public function getApplicationByID($id = false)
	{
		$q = $this->db->get_where("loan_applications", array("id"=>$id));
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function updateApplication($id = false, $data = array())
	{
		if($this->db->where("id", $id)->update("loan_applications",$data)){
			return true;
		}
		return false;
	}
	
	public function deleteApplication($id = NULL)
	{
		if($this->db->where("id",$id)->delete("loan_applications")){
			return true;
		}
		return false;
	}
	
	public function approveApplication($id = NULL, $unapprove = FALSE)
	{
		if($unapprove){
			$status = array('status' => 'requested');
		}else{
			$status = array('status' => 'approved');
		}
		if($this->db->where("id",$id)->update("loan_applications", $status)){
			return true;
		}
		return false;
	}
	
	public function declineApplication($id = NULL, $undecline = FALSE)
	{
		if($undecline){
			$status = array('status' => 'requested');
		}else{
			$status = array('status' => 'declined');
		}
		if($this->db->where("id",$id)->update("loan_applications", $status)){
			return true;
		}
		return false;
	}
	
	public function addDisburse($data = array(), $items = array(), $payment = array())
	{	

		if(isset($items) && $items){
			$data['status'] = 'active';
		}else{
			$data['status'] = 'pending';
		}
		if($this->db->insert("loans", $data)){
			$loan_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['loan_id'] = $loan_id;
					$this->db->insert("loan_items", $item);
				}
			}
			if(isset($payment) && $payment){
				$payment['loan_id'] = $loan_id;
				$this->db->insert("payments", $payment);
			}
			if(isset($data['application_id']) && $data['application_id']){
				$this->db->where("id",$data['application_id'])->update("loan_applications", array("status"=>"completed"));
			}
			return true;
		}
		return false;
	}
	
	public function getLoanByBorrower($borrower_id=false)
	{
		$q = $this->db->where("borrower_id", $borrower_id)->where("status","active")->get("loans");
		if($q->num_rows() > 0){
			$row = $q->num_rows();
			return $row;
		}
		return false;
	}
	
	public function getUniqueTypes($id = false)
	{
		$q = $this->db->get("loan_unique_types");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getUniqueType($id=false)
	{
		$q = $this->db->where("id", $id)->get("loan_unique_types");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function synLoanPenalty()
	{
		$qloan_item = $this->db
							   ->select("loan_items.*")
							   ->join("loans","loans.id=loan_id","left")
							   ->where("deadline <", date("Y-m-d"))
							   ->where("loans.status","active")
							   ->where("loan_items.status","pending")
							   ->get("loan_items");
							   
		if($qloan_item->num_rows() > 0){
			foreach($qloan_item->result() as $loan_item){
				
				$loan = $this->getLoanByID($loan_item->loan_id);
			
				$loan_product = $this->getLoanProductByID($loan->loan_product_id);
				$now = time();
				$deadline = strtotime($loan_item->deadline);
				$datediff = $now - $deadline;
				$overdue = round($datediff / (60 * 60 * 24));
				if($overdue > ($loan_product->late_repayment_penalty_period)){
					$number_days = $overdue - $loan_product->late_repayment_penalty_period;
					if($loan_product->late_repayment_penalty_recurring > 0){
						$penalty = $number_days / $loan_product->late_repayment_penalty_recurring;
					}else{
						$penalty = $number_days;
					}
					$method = $loan_product->late_repayment_penalty_calculate;
					if($method == 1){
						$penalty_paid = $penalty * ($loan_item->principal * $loan_product->late_repayment_penalty_amount) / 100;
					}else if($method == 2){
						$penalty_paid = $penalty * ($loan_item->payment * $loan_product->late_repayment_penalty_amount) / 100;
					}else if($method == 3){
						// $penalty_paid = $penalty * (($loan_item->payment - $amount_paid) * $loan_product->late_repayment_penalty_amount) / 100;
						$penalty_paid = $penalty * (($loan_item->payment) * $loan_product->late_repayment_penalty_amount) / 100;
					}else{
						$penalty_paid = $penalty * $loan_product->late_repayment_penalty_amount;
					}
					if($penalty_paid >= 0){
						$this->db->where("id", $loan_item->id)->update("loan_items", array("penalty"=>$penalty_paid));
					}
				}
			}
		}
	}
	
	public function getCheckCollaterals($application_id = null)
	{
		$q = $this->db->where("application_id", $application_id)->get("loan_collaterals");
		if($q->num_rows() > 0){
			$num_rows = $q->num_rows();
			return $num_rows;
		}
		return false;
	}
	
	public function getCheckGuarantors($application_id = null)
	{
		$q = $this->db->where("application_id", $application_id)->where("type","Guarantor")->get("loan_borrowers");
		if($q->num_rows() > 0){
			$num_rows = $q->num_rows();
			return $num_rows;
		}
		return false;
	}
		public function getCollaterals($application_id = null)
	{
		$q = $this->db->where("application_id", $application_id)->get("loan_collaterals");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getGuarantors($application_id = null)
	{
		$q = $this->db->where("application_id", $application_id)->where("type","Guarantor")->get("loan_borrowers");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function suspendLoan($id = NULL, $unsuspend = FALSE)
	{
		if($unsuspend){
			$status = array('status' => 'active');
		}else{
			$status = array('status' => 'suspended');
		}
		if($this->db->where("id",$id)->update("loans", $status)){
			return true;
		}
		return false;
	}
	
	public function payoffLoan($id = NULL, $unsuspend = FALSE)
	{
		if($unsuspend){
			$status = array('status' => 'active');
		}else{
			$status = array('status' => 'payoff');
		}
		if($this->db->where("id",$id)->update("loans", $status)){
			return true;
		}
		return false;
	}
	
	public function getPrincipalPaidByLoanID($loan_id = false)
	{
		$q = $this->db->select("SUM(amount) as amount")
					  ->where('type','received')
					  ->where('loan_id', $loan_id)
					  ->get('payments');
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return (double)$row->amount;
        }
        return FALSE;
	}
}
