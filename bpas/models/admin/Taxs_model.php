<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Taxs_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

	public function getIndexSales($sale_id = false){
		if($sale_id){
			$this->db->where_in("sales.id",$sale_id);
		}
		$this->db->select("
							sales.id,
							sales.date,
							sales.reference_no,
							sales.grand_total,
							(IFNULL(".$this->db->dbprefix("sales").".total,0) - IFNULL(".$this->db->dbprefix("sales").".order_discount,0)) as total,
							sales.order_tax,
							IFNULL(".$this->db->dbprefix('sales').".note,".$this->db->dbprefix('sales').".fee_type) as note,
							SUM(".$this->db->dbprefix("sale_items").".quantity) as quantity,
							companies.name, 
							companies.company, 
							companies.vat_no, 
							companies.phone
						");
		$this->db->join("sale_items","sale_items.sale_id = sales.id","left");
		$this->db->join("companies","companies.id = sales.customer_id","left");
		$this->db->group_by("sales.id");
		$q = $this->db->get("sales");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->id] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getIndexExpenses($expense_id = false){
		if($expense_id){
			$this->db->where_in("expenses.id",$expense_id);
		}
		$this->db->select("
							expenses.id,
							expenses.date,
							expenses.reference as reference_no,
							expenses.grand_total,
							(IFNULL(".$this->db->dbprefix("expenses").".amount,0) - IFNULL(".$this->db->dbprefix("expenses").".order_discount,0)) as total,
							expenses.order_tax,
							expenses.note,
							SUM(".$this->db->dbprefix("expense_items").".quantity) as quantity,
							companies.name, 
							companies.company, 
							companies.vat_no, 
							companies.phone
						");
		$this->db->join("expense_items","expense_items.expense_id = expenses.id","left");
		$this->db->join("companies","companies.id = expenses.supplier_id","left");
		$this->db->group_by("expenses.id");
		$q = $this->db->get("expenses");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->id] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getIndexPurchases($purchase_id = false){
		if($purchase_id){
			$this->db->where_in("purchases.id",$purchase_id);
		}
		$this->db->select("
							purchases.id,
							purchases.date,
							purchases.reference_no,
							purchases.grand_total,
							purchases.status,
							(IFNULL(".$this->db->dbprefix("purchases").".total,0) - IFNULL(".$this->db->dbprefix("purchases").".order_discount,0)) as total,
							purchases.order_tax,
							purchases.note,
							SUM(".$this->db->dbprefix("purchase_items").".quantity) as quantity,
							companies.name, 
							companies.company, 
							companies.vat_no, 
							companies.phone
						");
		$this->db->join("purchase_items","purchase_items.purchase_id = purchases.id","left");				
		$this->db->join("companies","companies.id = purchases.supplier_id","left");
		$this->db->group_by("purchases.id");
		$q = $this->db->get("purchases");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->id] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getInvoicesTaxCount($type = null,$prefix_year = null){
		$this->db->select("COUNT(".$this->db->dbprefix("tax_items").".id) as total_row");	
		$this->db->where('tax_items.transaction', $type);
		$this->db->like('tax_items.tax_reference', $prefix_year);		
		$q = $this->db->get("tax_items");
		if($q->num_rows() > 0){
			return $q->row()->total_row;
		}
		return false;
	}
	public function getTaxByID($id = false){
		$q = $this->db->get_where("taxs",array("id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getTaxItems($id = false){
		$q = $this->db->get_where("tax_items",array("tax_id"=>$id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function addTax($data = false, $items = false){
	
		if($data && $this->db->insert("taxs",$data)){
			$tax_id = $this->db->insert_id();
			foreach($items as $item){
				$item["tax_id"] 		= $tax_id;
				//$item["tax_reference"] 	=  $this->site->getReference('tax_sale');
				$this->db->insert("tax_items",$item);
				$this->db->update("sales", ["declare_tax" => 1] , ["id" => $item["transaction_id"]]);
	            //$this->site->updateReference('tax_sale');
			}
			return true;
		}
		return false;
	}
	
	public function updateTax($id = false, $data = false, $items = false){
		if($id && $this->db->update("taxs", $data , array( "id" => $id))){
			$this->db->delete("tax_items", array("tax_id" => $id));

			foreach($items as $item){
				$this->db->insert("tax_items",$item);
			    $this->db->update("sales", ["declare_tax" => 1] , ["id" => $item["transaction_id"]]);
			}
			// if($items){
			// 	$this->db->insert_batch("tax_items",$items);
			// }
			return true;
		}
		return false;
	}
	
	public function deleteTax($id = false , $transaction_id){
		if($id && $this->db->delete("taxs",array("id"=>$id))){

			$transaction_id = $this->site->getTransactionsId($id);
			$this->db->set("declare_tax" , 0 );
			$this->db->where_in("id",$transaction_id);
			$this->db->update("sales");
			
			$this->db->delete("tax_items",array("tax_id"=>$id));
			
			return true;
		}
		return false;
	}
	
	public function getTaxs($type = false, $biller_id = false, $month = false, $year = false){
		if($type){
			if($type=="purchase"){
				$this->db->where_in("taxs.type",array("purchase","expense"));
			}else{
				$this->db->where("taxs.type",$type);
			}
			
		}
		if($biller_id){
			$this->db->where("taxs.biller_id",$biller_id);
		}
		if($month){
			$this->db->where("month(".$this->db->dbprefix('tax_items').".date)",$month);
		}
		if($year){
			$this->db->where("year(".$this->db->dbprefix('tax_items').".date)",$year);
		}
		
		$this->db->select("tax_items.*");
		$this->db->join("taxs","taxs.id = tax_items.tax_id","INNER");
		$this->db->order_by("tax_items.tax_reference",'DESC');
		$q = $this->db->get("tax_items");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
}