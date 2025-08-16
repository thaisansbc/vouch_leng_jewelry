<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Repairs_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	
    public function getProductNames($term = false, $warehouse_id = false, $brand_id=0, $model_id=0, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		$this->db->where('products.inactive !=',1);
        $this->db->select('products.*, warehouses_products.quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')            
			->where("brand",$brand_id)
            ->where("model",$model_id)
			->where('products.type != ','raw_material')
            ->where('products.type != ','asset')
            ->where('products.type != ','combo')
			->group_by('products.id');
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getProductByCode($code = false)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWHProduct($id = false, $warehouse_id=NULL)
    {
        $this->db->where('products.inactive !=',1);
        $this->db->select('products.*, warehouses_products.quantity')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('products.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getItemByID($id = false)
    {
        $q = $this->db->get_where('repair_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllRepairItemsWithDetails($repair_id = false)
    {
        $this->db->select('repair_items.*, products.details');
        $this->db->join('products', 'products.id=repair_items.product_id', 'left');
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('repairs_items', array('repair_id' => $repair_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getRepairByID($id = false)
    {
        $q = $this->db->get_where('repairs', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
	public function getAllRepairItems($repair_id = false)
    {
        $this->db->select('repair_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.product_details as details, product_variants.name as variant,units.name as unit_name')
            ->join('products', 'products.id=repair_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=repair_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=repair_items.tax_rate_id', 'left')
			->join('units','units.id = repair_items.product_unit_id','left')
            ->group_by('repair_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('repair_items', array('repair_id' => $repair_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addRepair($data = array(), $items = array())
    {
        if ($this->db->insert('repairs', $data)) {
            $repair_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['repair_id'] = $repair_id;
                $this->db->insert('repair_items', $item);
            }
            if($data['check_id'] && $data['check_id'] > 0){
                $this->db->where("id", $data['check_id'])->update("repair_checks", array("repair_reference_no" =>$data['reference_no'], "status"=>"received"));
            }
            return $repair_id;
        }
        return false;
    }

    public function updateRepair($id = false, $data = false, $items = array())
    {
        if ($this->db->update('repairs', $data, array('id' => $id)) && $this->db->delete('repair_items', array('repair_id' => $id))) {
            foreach ($items as $item) {
                $item['repair_id'] = $id;
                $this->db->insert('repair_items', $item);
            }
            return true;
        }
        return false;
    }

    public function deleteRepair($id = false)
    {
        if ($this->db->delete('repair_items', array('repair_id' => $id)) && $this->db->delete('repairs', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getProductByName($name = false)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getWarehouseProductQuantity($warehouse_id = false, $product_id = false)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductComboItems($pid = false, $warehouse_id = false)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->where('warehouses_products.warehouse_id', $warehouse_id)
            ->group_by('combo_items.id');
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }

    public function getProductOptions($product_id = false, $warehouse_id = false)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->where('warehouses_products_variants.quantity >', 0)
            ->group_by('product_variants.id');
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductOptionByID($id = false)
    {
        $q = $this->db->get_where('product_variants', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function getCustomerPrice($product_id = false,$customer_id = false)
	{
		$q = $this->db->get_where('customer_product_prices',array('customer_id'=>$customer_id,'product_id'=>$product_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
    }
    
	public function getAllBrands()
	{
		$q = $this->db->get("brands");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getBrandByID($id = false)
	{
		$q = $this->db->where("id",$id)->get("brands");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getModelByID($id = false)
	{
		$q = $this->db->where("id",$id)->get("models");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getModelsByBrandID($id =false)
	{
		$q = $this->db->where("brand_id",$id)->get("models");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getCustomerByPhone($phone = false)
	{
		$q = $this->db->where("phone",trim($phone))->get("companies");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
	
	public function getAllMachineTypes()
	{
		$q = $this->db->get("machine_types");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getMachineTypeByID($id = false)
	{
		$q = $this->db->where("id",$id)->get("machine_types");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}
    
	public function getMemberCardCode($card_no = false)
	{
		$q = $this->db->where("card_no",trim($card_no))->get("member_cards");
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row;
		}
		return false;
	}

	public function getAllTechnicians()
	{
		$this->db->where('technician',1);
		$q = $this->db->get("users");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getDiagnostics()
    {
		$q = $this->db->get("repair_diagnostics");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addCheck($data = false, $items = false){
        if($this->db->insert("repair_checks", $data)){
            $insert_id = $this->db->insert_id();
            if($items){
                foreach($items as $item){
                    $item['check_id'] = $insert_id;
                    $this->db->insert("repair_check_items", $item);
                }
                return true;
            }
            return FALSE;
        }
    }

    public function updateCheck($id = false, $data = false, $items = false){
        if($this->db->where("id", $id)->update("repair_checks", $data)){
            if($items){
                $this->db->where("check_id", $id)->delete("repair_check_items");
                foreach($items as $item){
                    $item['check_id'] = $id;
                    $this->db->insert("repair_check_items", $item);
                }
                return true;
            }
            return FALSE;
        }
    }


    public function getCheckByID($id = false)
    {
		$q = $this->db->where("id", $id)->get("repair_checks");
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row;
        }
        return FALSE;
    }

    public function getCheckItems($id = false)
    {
		$q = $this->db->where("check_id", $id)->get("repair_check_items");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getDiagnosticByID($id = false)
    {
		$q = $this->db->where("id", $id)->get("repair_diagnostics");
        if ($q->num_rows() > 0) {
            $row = $q->row();
            return $row;
        }
        return FALSE;
    }

    public function deleteCheck($id = false)
    {
        if ($this->db->delete('repair_check_items', array('check_id' => $id)) && $this->db->delete('repair_checks', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getCheckStatus(){
        $q = $this->db->where("status","pending")->get("repair_checks");
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function addProblem($data = false, $items = false, $account=null)
    {
        if($this->db->insert("products",$data)){
            $problem_id = $this->db->insert_id(); 
            if($items){
                foreach($items as $item){
                    $item['product_id'] = $problem_id;
                    $this->db->insert("product_prices",$item);
                }
            }
            if($account){
				$account['product_id'] = $problem_id;
				$this->db->insert('acc_product', $account);
			}
            return true;
        }
        return false;
    }

    public function deleteProblem($id = false){
        if($this->db->where("id",$id)->delete("products")){
            $this->db->where("product_id", $id)->delete("product_prices");
            $this->db->where("product_id", $id)->delete("acc_product");
            return true;
        }
        return false;
    }

    public function getProblemByID($id = false){
        $q = $this->db->where("id",$id)->get("products");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function updateProblem($id = false, $data = false, $items = false, $account=null){
        if($this->db->where("id",$id)->update("products",$data)){
            if($items){
                $this->db->where("product_id", $id)->delete("product_prices");
                foreach($items as $item){
                    $item['product_id'] = $id;
                    $this->db->insert("product_prices",$item);
                }
            }
            if($account){
                $this->db->where("product_id", $id)->delete("acc_product");
				$account['product_id'] = $id;
				$this->db->insert('acc_product', $account);
			}
            return true;
        }
        return false;
    }

    public function addProblems($data = false){
        if($this->db->insert_batch("products", $data)){
            return true;
        }
        return false;
    }

    public function getBrandByCode($code = false){
        $q = $this->db->where("code",$code)->get("brands");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function getModelByCode($code = false){
        $q = $this->db->where("code",$code)->get("models");
        if($q->num_rows() > 0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function getProblemByCode($code = false){
        $q = $this->db->where("code",$code)->get("products");
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }

    public function deleteDiagnostic($id = false){
        if($this->db->where("id",$id)->delete("repair_diagnostics")){
            return true;
        }
        return false;
    }

    public function addDiagnostic($data = false){
        if($this->db->insert("repair_diagnostics", $data)){
            return true;
        }
        return false;
    }

    public function updateDiagnostic($id = false,$data = false){
        if($this->db->where("id",$id)->update("repair_diagnostics", $data)){
            return true;
        }
        return false;
    }

    public function addDiagnostics($data = false){
        if($this->db->insert_batch("repair_diagnostics", $data)){
            return true;
        }
        return false;
    }

    public function getPriceGroups(){
        $q = $this->db
                      ->select('machine_types.name as machine_type, machine_types.price_group_id, price_groups.name')
                      ->join('price_groups','machine_types.price_group_id=price_groups.id','inner')
                      ->get("machine_types");
        if($q->num_rows() > 0 ){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getAllPriceGroups()
    {
        $q = $this->db->get('price_groups');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getPriceGroupByID($id = false)
    {
        $q = $this->db->get_where('price_groups', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
    public function getProblemPrice($price_group_id = false, $problem_id = false)
    {
        $q = $this->db->where("price_group_id",$price_group_id)
                      ->where("product_id", $problem_id)
                      ->get("product_prices");
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
    
    public function getDiagnosticNames($term = false, $brand_id=0, $model_id=0, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select('repair_diagnostics.*');
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%')");
        $this->db->where("brand", $brand_id);
        $this->db->where("model", $model_id);
        $this->db->limit($limit);
        $q = $this->db->get('repair_diagnostics');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getWHDiagnostic($diagnostic_id=0, $brand_id=0, $model_id=0, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select('repair_diagnostics.*');
        $this->db->where("repair_diagnostics.id", $diagnostic_id);
        $this->db->where("brand", $brand_id);
        $this->db->where("model", $model_id);
        $this->db->limit($limit);
        $q = $this->db->get('repair_diagnostics');
        if ($q->num_rows() > 0) {
            $row  = $q->row();
            return $row;
        }
    }

    public function getRepairItemByID($id=false)
    {
        $q = $this->db->select('
							repair_items.*, 
							repairs.imei_number, 
							repairs.model_id, 
							repairs.brand_id, 
							repairs.staff_note, 
							repairs.created_by,
							repairs.customer,
							repairs.phone,
							repairs.reference_no
							')
					 ->where("repair_items.id", $id)
					 ->join('repairs','repairs.id=repair_id','left')
					 ->get("repair_items");
        if($q->num_rows()>0){
            $row = $q->row();
            return $row;
        }
        return false;
    }
	
	public function updateStatus($id = false, $status = false, $note = false)
    {
		$repair_item= $this->getRepairItemByID($id);
        if ($this->db->update('repair_items', array('problem_status' => $status, 'problem_note' => $note), array('id' => $id))) {
			if($repair_item){
				$this->synRepairStatus($repair_item->repair_id);
			}
			return true;
        }
        return false;
    }
	
	public function synRepairStatus($id=false)
	{
		$q = $this->db->where("repair_id", $id)->get("repair_items");
		if($q->num_rows()>0){
			$num_row = (int)$q->num_rows();
			$count = 0;
			foreach($q->result() as $row){
				if($row->problem_status=='done'){
					$partial = 1;
				}else{
					$partial = 0;
				}
				$count += $partial;
			}
			if($count >= $num_row){
				$this->db->where("id", $id)->update("repairs", array("status"=>"done"));
			}else{
				$this->db->where("id", $id)->update("repairs", array("status"=>"repairing"));
			}
		}
		return false;
	}
	
	
	public function deleteMachineType($id = false)
	{
		if($this->db->where("id", $id)->delete("machine_types")){
			return true;
		}
		return false;
	}
	
	public function updateMachineType($id = false, $data = array())
    {
        if ($this->db->update("machine_types", $data, array('id' => $id))) {
            return true;
        }
        return false;
    }
	
	public function addMachineType($data = false)
    {
        if ($this->db->insert("machine_types", $data)) {
            return true;
        }
        return false;
    }

    public function getProblemPriceByMachineTypes($machine_type_id =false)
    {
        if($machine_type_id){
            $this->db->where("machine_types.id", $machine_type_id);
        }
        $q = $this->db->select("machine_types.id, machine_types.price_group_id, product_prices.product_id, product_prices.price")
                      ->from("machine_types")
                      ->join("price_groups","price_groups.id=machine_types.price_group_id","left")
                      ->join("product_prices","product_prices.price_group_id=price_groups.id","left")
                      ->get();
        if($q->num_rows()>0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    
    public function getProblemAccByProblemId($problem_id = false)
    {
        $q = $this->db->get_where('acc_product', array('product_id' => $problem_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

}















