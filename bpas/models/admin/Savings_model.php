<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Savings_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	
	public function deleteSavingProduct($id = false)
	{
		if($id > 0){
			if($this->db->where("id",$id)->delete("saving_products")){
				return true;
			}
		}
		return false;
	}
	
	public function getSavingProductByID($id = false)
	{
		$q = $this->db->where("id",$id)->get("saving_products");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function addSavingProduct($data = false)
	{
		if($this->db->insert("saving_products", $data)){
			return true;
		}
		return false;
	}
	
	public function updateSavingProduct($id = false, $data = false)
	{
		if($this->db->where("id", $id)->update("saving_products", $data)){
			return true;
		}
		return false;
	}
	
	public function addSaving($data = false, $payments = false)
	{
		if($this->db->insert("savings", $data)){
			$insert_id = $this->db->insert_id();
			if($payments){
				foreach($payments as $payment){
					$payment['saving_id'] = $insert_id;
					$this->db->insert("payments", $payment);
				}
			}
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
	
	public function getSavingProducts()
	{
		$q = $this->db->get("saving_products");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
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

	public function deleteSaving($id = false)
	{
		if($id > 0){
			if($this->db->where("id",$id)->delete("savings")){
				$this->db->where("saving_id", $id)->delete("payments");
				return true;
			}
		}
		return false;
	}
	
	public function getSavingByID($id = false)
	{
		$q = $this->db->where("id",$id)->get("savings");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function updateSaving($id = false, $data = false)
	{
		if($this->db->where("id",$id)->update("savings", $data)){
			return true;
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
	
	public function getWorkingStatusByID($id = false)
	{
		$q = $this->db->where("id", $id)->get("loan_working_status");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
	}

	public function getPaymentBalance($saving_id = false)
	{
		$q = $this->db->where("saving_id", $saving_id)->get("payments");
		if($q->num_rows() > 0){
			$balance = 0;
			foreach($q->result() as $row){
				$amount = 0;
				if($row->transaction_type == 'in'){
					$amount = $row->amount;
				}
				if($row->transaction_type == 'out'){
					$amount = -($row->amount);
				}
				$balance += $amount;
			}
			return $balance;
		}
		return false;
	}
	
	public function addDeposit($payments =false)
	{
		if($payments){
			foreach($payments as $payment){
				$this->db->insert("payments", $payment);
			}
			return true;
		}
		return false;
	}
	
	public function getSavingAccounts()
	{
		$q = $this->db->where("status","active")->get("savings");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getLastTran()
	{
		$q = $this->db->select("COUNT(transaction_id) as tran")->where("saving_id >", 0)->get("payments");
		if($q->num_rows() > 0){
			$ref  = $q->row();
			return (double)$ref->tran + 1;
		}
		return false;
	}
	
	public function getLastTranBySavingID($saving_id = false)
	{
		$q = $this->db->select("MAX(transaction_id) as tran")->where("saving_id", $saving_id)->get("payments");
		if($q->num_rows() > 0){
			$ref  = $q->row();
			return (double)$ref->tran;
		}
		return false;
	}
	
	public function getPaymentByTran($transaction_id = false)
	{
		$q = $this->db->where("transaction_id",$transaction_id)->get("payments");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function cancelLastOperation($transaction_id = false, $payments = false)
	{
		if($payments){
			$this->db->where("transaction_id",$transaction_id)->delete("payments");
			foreach($payments as $payment){
				$this->db->insert("savings_cancel_transactions", $payment);
			}
			return true;
		}
		return false;
	}
	
	public function closeSaving($id = false, $data = false)
	{
		if($this->db->where("id",$id)->update("savings", $data)){
			return true;
		}
		return false;
	}
	
	
	
	
	
	
	
	
}

