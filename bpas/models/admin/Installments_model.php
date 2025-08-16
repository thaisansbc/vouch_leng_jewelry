<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Installments_model extends CI_Model
{
	
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllFrequencies()
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
	
	public function getAllInstallmentItemsByID($id = false)
    {
        $q = $this->db->select("
							installment_items.id,
							installment_items.installment_id,
							installment_items.period,
							installment_items.deadline,
							installment_items.payment,
							installment_items.principal,
							installment_items.interest,
							installment_items.balance,
							installment_items.paid,
							installment_items.`status`,
							installment_items.note,
							payments.reference_no as payment_no,
							payments.penalty_paid,
							DATE(payments.date) as payment_date")
					  ->join('installments','installments.id=installment_items.installment_id','left')
					  ->join('(SELECT reference_no,date,installment_item_id,sum(penalty_paid) as penalty_paid FROM '.$this->db->dbprefix("payments").' GROUP BY installment_item_id) as payments','payments.installment_item_id=installment_items.id','left')
					  ->where("installments.id",$id)
					  ->group_by("installment_items.id")
					  ->get('installment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getSaleItemBySaleID($sale_id = false)
	{
		$q = $this->db->where('sale_id', $sale_id)->get('sale_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getInstallmentItemByInsta($installment_id = false){
		$q = $this->db->get_where("installment_items",array("installment_id" => $installment_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getPaymentInstaItemGroupByItem($installment_id = false){
		$this->db->where("installment_id",$installment_id);
		$this->db->select("
							installment_item_id,
							sum(amount + discount) as principal_paid,
							sum(interest_paid) as interest_paid,
							sum(penalty_paid) as penalty_paid,
						");
		$this->db->group_by("installment_item_id");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->installment_item_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function syncInstallmentPayments($installment_id = false)
	{
		$installment_items = $this->getInstallmentItemByInsta($installment_id);
		if ($installment_items) {
			$payments = $this->getPaymentInstaItemGroupByItem($installment_id);
			$total_amount = 0;
			$total_paid = 0;
			$over_principal = 0;
			$over_interest = 0;
			foreach($installment_items as $installment_item){
				$principal_paid = 0;
				$interest_paid = 0;
				$penalty_paid = 0;
				if (isset($payments[$installment_item->id])) {
					$principal_paid = $payments[$installment_item->id]->principal_paid;
					$interest_paid = $payments[$installment_item->id]->interest_paid;
					$penalty_paid = $payments[$installment_item->id]->penalty_paid;
				}
				$total_principal = $principal_paid + $over_principal;
				if ($total_principal >  $installment_item->principal){
					$principal_paid = $installment_item->principal;
					$over_principal = ($total_principal - $installment_item->principal);
				} else if ($over_principal > 0){
					$principal_paid = $total_principal;
					$over_principal = 0;
				}
				$total_interest = $interest_paid + $over_interest;
				if ($total_interest >  $installment_item->interest){
					$interest_paid = $installment_item->interest;
					$over_interest = ($total_interest - $installment_item->interest);
				} else if ($over_interest > 0){
					$interest_paid = $total_interest;
					$over_interest = 0;
				}
				$paid = $principal_paid + $interest_paid + $penalty_paid;
				if ($this->bpas->formatDecimal($paid) >= $this->bpas->formatDecimal($installment_item->payment)) {
					$status = "paid";
				} elseif (($this->bpas->formatDecimal($paid) != 0) && ($this->bpas->formatDecimal($paid) < $this->bpas->formatDecimal($installment_item->payment))) {
					$status = "partial";
				} else {
					$status = "pending";
				}
				$data = array(
					"paid" 			 => $paid,
					"principal_paid" => $principal_paid,
					"interest_paid"  => $interest_paid,
					"penalty_paid"   => $penalty_paid,
					"status" 		 => $status
				);
				$total_amount += $installment_item->payment;
				$total_paid   += $paid;
				$this->db->update("installment_items",$data,array("id"=>$installment_item->id));
			}
			if ($this->bpas->formatDecimal($total_paid) >= $this->bpas->formatDecimal($total_amount)) {
				$install_status = 'completed';
			} else {
				$install_status = 'active';
			}
			$this->db->update('installments', array('status' => $install_status), array('id' => $installment_id));
		}
	}
	
	public function getPaymentByInstallmentItemID($installment_item_id = false)
	{
		$q = $this->db->where('installment_item_id', $installment_item_id)->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addInstallments($items = array(), $data = array(), $accTrans = array())
	{
		if($items){
			if($this->db->insert("installments",$data)){
				$insert_id = $this->db->insert_id();
				if ($this->site->getReference('inst') == $data['reference_no']) {
                	$this->site->updateReference('inst');
            	}
				foreach($items as $row){
					$row['installment_id'] = $insert_id;
					$this->db->insert("installment_items",$row);
				}
				$this->db->update("sales", array("installment" => 1), array("id"=> $data['sale_id']));
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['tran_no']= $insert_id;
						$this->db->insert('gl_trans', $accTran);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateInstallments($id = false, $items = array(), $data = array(), $accTrans = array())
	{
		if($items){
			$this->db->where("installment_id", $id)->where("status", 'pending')->delete("installment_items");
			if($this->db->where("id", $id)->update("installments",$data)){
				foreach($items as $row){
					$row['installment_id'] = $id;
					$this->db->insert("installment_items",$row);
				}
				$this->db->update("sales", array("installment" => 1), array("id"=> $data['sale_id']));
			}
			
			if($accTrans){
				$this->site->deleteAccTran('Installment',$id);
				foreach($accTrans as $accTran){
					$accTran['tran_no']= $id;
					$this->db->insert('gl_trans', $accTran);
				}
			}
			return true;
		}
		return false;
	}
	
	public function getInstallmentItemsByID($id = false)
    {
        $q = $this->db->where('id', $id)->get('installment_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getInstallmentByID($id = false)
    {
        $q = $this->db->where('id', $id)->get('installments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function deleteInstallmentByID($id = false)
	{
		$installment = $this->getInstallmentByID($id);
		if($installment && $this->db->where("id", $id)->delete("installments")){
			$this->db->where("installment_id", $id)->delete("installment_items");
			$this->db->where("installment_id", $id)->delete("payments");
			$this->db->where("id", $installment->sale_id)->update("sales", array("installment"=>NULL));
			$this->site->syncSalePayments($installment->sale_id);
			$this->site->deleteAccTran('Installment',$installment->id);
			return true;
		}
	}
	
	public function getSaleByID($id = NULL)
    {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addPayment($data = array(), $customer_id = null, $accTranPayments = array())
    {
        if ($this->db->insert('payments', $data)) {
			$payment_id = $this->db->insert_id();
			if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
			if ($accTranPayments) {
				foreach($accTranPayments as $accTranPayment) {
					$accTranPayment['tran_no']= $payment_id;
					$this->db->insert('gl_trans', $accTranPayment);
				}
			}
			$this->site->syncSalePayments($data['sale_id']);
			$this->syncInstallmentPayments($data['installment_id']);
			if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount - $data['amount'])), array('id' => $customer_id));
            }
			return true;
        }
        return false;
    }
	
	public function updatePayment($id = false, $data = array(), $customer_id = null, $accTranPayments = array())
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
			$this->site->deleteAccTran('Payment',$id);
			if($accTranPayments){
				$this->db->insert_batch('gl_trans', $accTranPayments);
			}
            $this->site->syncSalePayments($opay->sale_id);
			$this->syncInstallmentPayments($opay->installment_id);
			if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $installment = $this->getInstallmentByID($opay->installment_id);
                    $customer_id = $installment->customer_id;
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
	
	public function deletePayment($id = false)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
			$this->site->deleteAccTran('Payment',$id);
            $this->site->syncSalePayments($opay->sale_id);
			$this->syncInstallmentPayments($opay->installment_id);
			if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $installment = $this->getInstallmentByID($opay->installment_id);
                $customer = $this->site->getCompanyByID($installment->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return FALSE;
    }
	
	public function getPaymentByID($id = false)
    {
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
        $q = $this->db->get_where('payments', array('payments.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function updateStatusInstallmentByID($id = false)
	{
		$installment = $this->getInstallmentByID($id);
		if($installment->status == "inactive"){
			$status = "active";
		}else{
			$status = "inactive";
		}
		if($this->db->where("id", $id)->update("installments", array("status"=>$status))){
			return true;
		}
	}
	
	public function getInvoicePaymentsByInstallmentItemID($installment_item_id = false)
    {
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->order_by('id', 'desc');
        $q = $this->db->get_where('payments', array('payments.installment_item_id' => $installment_item_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
    }
	
	public function getMultiInstallmentsByID($id = false)
    {
		$this->db->select('
					installment_items.id,
					installment_items.installment_id,
					installment_items.deadline,
					installment_items.interest,
					installment_items.principal,
					installment_items.period,
					installment_items.payment,
					installment_items.principal_paid as paid,
					installment_items.interest_paid,
					installments.status,
					installments.customer_id,
					installments.customer,
					installments.reference_no,
					installments.sale_id,
					')
		->join('installments','installments.id=installment_items.installment_id','left');
		$this->db->where_in('installment_items.id',$id);
		$this->db->where('installment_items.status !=','paid');
		$this->db->order_by('installment_items.deadline');
        $q = $this->db->get('installment_items');
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
        }
        return FALSE;
    }
	
	public function getInstallmentBalanceByID($id = false)
    {
		$this->db->select('
					installment_items.id,
					installment_items.installment_id,
					installment_items.deadline,
					installment_items.interest,
					installment_items.principal,
					installment_items.period,
					installment_items.payment,
					installment_items.principal_paid as paid,
					installment_items.interest_paid,
					installment_items.penalty,
					installment_items.penalty_paid');
		$this->db->where('installment_items.id',$id);
		$this->db->where('installment_items.status !=','paid');
		$this->db->order_by('installment_items.deadline');
        $q = $this->db->get('installment_items');
		
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addMultiPayment($data = false, $accTranPayments = array())
	{
		if($data){
			foreach($data as $row){
				$this->db->insert('payments',$row);
				$payment_id = $this->db->insert_id();
				$this->site->syncSalePayments($row['sale_id']);
				$this->syncInstallmentPayments($row['installment_id']);
				$accTrans = $accTranPayments[$row['installment_item_id']];
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['tran_no'] = $payment_id;
						$this->db->insert('gl_trans',$accTran);
					}
				}
				if ($this->site->getReference('pay') == $row['reference_no']) {
	                $this->site->updateReference('pay');
	            }
			}
			
			return true;
		}
		return false;
	}
	
	public function getAllInstallmentItemsByInstallmentID($id = false)
    {
        $q = $this->db->get_where('installment_items', array("installment_id"=>$id));
        if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[]= $row;
			}
            return $data;
        }
        return false;
    }
	public function getBalancePrincipal($installment_id = false)
	{
		$q = $this->db->get_where('installment_items', array("installment_id"=>$installment_id, "status <>"=>"paid"));
        if ($q->num_rows() > 0) {
			$principal = 0;
			foreach($q->result() as $row){
				$principal += $row->principal;
			}
            return $principal;
        }
        return false;
	}

	public function getHoliday()
	{	
		$q = $this->db->get_where("calendar", array("holiday"=>1));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getInstallmentBySaleID($sale_id = false)
	{
		$q = $this->db->where("sale_id", $sale_id)->where('status','active')->get("installments");
		if($q->num_rows() > 0){
			$nums = $q->num_rows();
			return $nums;
		}
		return false;
	}
	
	public function getPaymentByInstallmentID($installment_id = false)
	{
		$q = $this->db->where('installment_id', $installment_id)->where('type','received')->get('payments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getAssignationByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("installment_assigns");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function deleteAssignation($id = false)
	{
		$assign = $this->getAssignationByID($id);
		if($this->db->where("id", $id)->delete("installment_assigns")){
			
			if($assign){
				$installment = $this->getInstallmentByID($assign->installment_id);
				$customer = $this->site->getCompanyByID($assign->old_customer);
				$attributes = array(
						'customer_id' => $customer->id,
						'customer' => ($customer->company?$customer->company:$customer->name),
					);
				$this->db->where("id", $installment->id)->update("installments", $attributes);
				$this->db->where("id", $installment->sale_id)->update("sales", $attributes);
			}
			
			return true;
		}
		return false;
	}
	
	public function addAssignation($data = array())
	{
		if($this->db->insert("installment_assigns", $data)){
			$customer = $this->site->getCompanyByID($data['new_customer']);
			if($customer){
				$installment = $this->getInstallmentByID($data['installment_id']);
				$attributes = array(
						'customer_id' => $customer->id,
						'customer' => ($customer->company?$customer->company:$customer->name),
					);
				$this->db->where("id", $installment->id)->update("installments", $attributes);
				$this->db->where("id", $installment->sale_id)->update("sales", $attributes);
			}
			return true;
		}
		return false;
	}
	
	public function getMaxAssignation()
	{
		$q = $this->db->select("MAX(id) as id")->get("installment_assigns");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function payOff($id = NULL, $unsuspend = FALSE)
	{
		if($unsuspend){
			$status = array('status' => 'active');
		}else{
			$status = array('status' => 'payoff');
		}
		if($this->db->where("id",$id)->update("installments", $status)){
			return true;
		}
		return false;
	}
	
	public function getPrincipalPaidByInstallmentID($installment_id = false)
	{
		$q = $this->db->select("SUM(amount) as amount")
					  ->where('type','received')
					  ->where('installment_id', $installment_id)
					  ->get('payments');
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return (double)$row->amount;
        }
        return FALSE;
	}
	
	public function getRefSales(){
		$q = $this->db->query("SELECT
								".$this->db->dbprefix('sales').".`id` AS `id`,
								".$this->db->dbprefix('sales').".`reference_no`,
								IF
								(
									(
										round((
												grand_total -(
													IFNULL( bpas_payments.paid, 0 ))-(
													IFNULL( bpas_payments.discount, 0 ))-(
												IFNULL( bpas_return.total_return + total_return_paid, 0 ))),
											".$this->Settings->decimals." 
										) = 0 
									),
									'paid',
								IF
									(
										(
											(
												grand_total -(
													IFNULL( bpas_payments.paid, 0 ))-(
													IFNULL( bpas_payments.discount, 0 ))-(
												IFNULL( bpas_return.total_return + total_return_paid, 0 ))) = grand_total 
										),
										'pending',
										'partial' 
								)) AS payment_status 
							FROM
								".$this->db->dbprefix('sales')."
								LEFT JOIN ".$this->db->dbprefix('installments')." ON ".$this->db->dbprefix('installments').".sale_id = ".$this->db->dbprefix('sales').".id  AND ".$this->db->dbprefix('installments').".status = 'active'
								LEFT JOIN (
									SELECT
										sale_id,
										SUM(
										ABS( grand_total )) AS total_return,
										SUM( paid ) AS total_return_paid 
									FROM
										".$this->db->dbprefix('sales')." 
									WHERE
										sale_status = 'returned' 
									GROUP BY
										sale_id 
								) AS bpas_return ON `bpas_return`.`sale_id` = ".$this->db->dbprefix('sales').".`id`
								LEFT JOIN (
								SELECT
									sale_id,
									IFNULL( SUM( amount ), 0 ) AS paid,
									IFNULL( SUM( discount ), 0 ) AS discount 
								FROM
									".$this->db->dbprefix('payments')."  
								GROUP BY
									sale_id 
								) AS bpas_payments ON `bpas_payments`.`sale_id` = ".$this->db->dbprefix('sales').".`id` 
							WHERE
								IFNULL( ".$this->db->dbprefix('sales').".type, '' ) != 'concrete' 
								AND `pos` != 1 
								AND `sale_status` != 'draft' 
								AND `sale_status` != 'returned' 

							GROUP BY ".$this->db->dbprefix('sales').".id	
							HAVING payment_status != 'paid'
							ORDER BY ".$this->db->dbprefix('sales').".id desc");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	// AND IFNULL ( ".$this->db->dbprefix('installments').".id, 0 ) = 0 
	
	public function getStudentByID($student_id = false){
		$q = $this->db->get_where("sh_students",array("id"=>$student_id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function getStudentBanks($family_id = false){
		$this->db->where("family_id",$family_id);
		$q = $this->db->get("sh_student_banks");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getGradeByID($id = false){
		$q = $this->db->get_where("sh_grades",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getReceivePyamentByPaymentID($payment_id = false){
		$q = $this->db->get_where("receive_payment_items",array("payment_id"=>$payment_id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getFrequencyDeadlines($frequency_id = false){
		$this->db->where("frequency_id",$frequency_id);
		$q = $this->db->get("frequency_deadlines");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getFrequencyByID($id = false){
		$q = $this->db->get_where("frequency",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
	}
	
	public function checkPreviousPyament($id = false,$installment_id = false)
    {
		$this->db->where("installment_id",$installment_id);
		$this->db->where("id <",$id);
		$this->db->where("status !=","paid");
        $q = $this->db->get('installment_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	public function deletePenalty($id = false)
	{
		if($this->db->where("id",$id)->delete('installments_penalty')){
			return true;
		}
		return false;
	}
	public function getPenaltyByID($id = false)
	{
		$q = $this->db->get_where('installments_penalty', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}

	public function updatePenalty($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('installments_penalty', $data)){
			return true;
		}
		return false;
	}

	public function addPenalty($data = array())
	{
		if($this->db->insert('installments_penalty',$data)){
			return true;
		}
		return false;
	}

	public function getPenalty()
	{
		$q = $this->db->get('installments_penalty');
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
}