<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Concretes_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	public function getCustomerLocations($customer_id = false){
		$this->db->where("status",0);
		$q = $this->db->get_where("addresses",array("company_id"=>$customer_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getCustomerQuotations($customer_id = false){
		$this->db->order_by("quotes.id","desc");
		$q = $this->db->get_where("quotes",array("customer_id"=>$customer_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getCustomerLocationByID($id = false){
		$q = $this->db->get_where('addresses',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getDrivers($status = false){
		if($status){
			$this->db->where("status",$status);
		}
		$q = $this->db->get_where('companies',array('group_name'=> 'driver'));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getDriverByID($id = false){
		$q = $this->db->get_where('companies',array('group_name'=> 'driver','id'=> $id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addDriver($data = false){
		if($data && $this->db->insert('con_drivers',$data)){
			return true;
		}
		return false;
	}
	public function updateDriver($id = false, $data = false, $update_trucks = false){
		if($id && $data && $this->db->update('con_drivers',$data,array('id'=>$id))){
			if($update_trucks){
				foreach($update_trucks as $update_truck){
					$this->db->update("con_trucks",(array)$update_truck,array("id"=>$update_truck->id));
				}
			}
			return true;
		}
		return false;
	}
	public function deleteDriver($id = false){
		if($id && $this->db->delete('con_drivers',array('id'=>$id))){
			return true;
		}
		return false;
	}
	
	public function getOfficers(){
		$q = $this->db->get("hr_employees");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getOfficerByID($id = false){
		$q = $this->db->get_where('hr_employees',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addOfficer($data = false,$data_info = false){
		if($data && $this->db->insert('hr_employees',$data)){
			$employee_id = $this->db->insert_id();
			$data_info['employee_id'] = $employee_id;
			$this->db->insert('hr_employees_working_info',$data_info);
			return true;
		}
		return false;
	}
	public function updateOfficer($id = false, $data = false,$data_info = false){

		if($id && $this->db->update('hr_employees',$data,array('id'=>$id))){
			$this->db->update('hr_employees_working_info',$data_info,array('employee_id'=>$id));
			return true;
		}
		return false;
	}
	public function deleteOfficer($id = false){
		if($id && $this->db->delete('hr_employees',array('id'=>$id))){
			$this->db->delete('hr_employees_working_info',array('employee_id'=>$id));
			return true;
		}
		return false;
	}
	
	public function getTrucks($type = false, $driver = false){
		if($type){
			$this->db->where("con_trucks.type",$type);
		}
		if($driver){
			$this->db->where("con_trucks.driver_id >",0);
		}
		$q = $this->db->get("con_trucks");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getTruckByID($id = false){
		$q = $this->db->get_where('con_trucks',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addTruck($data = false){
		if($data && $this->db->insert('con_trucks',$data)){
			return true;
		}
		return false;
	}
	public function updateTruck($id = false, $data = false){
		if($id && $data && $this->db->update('con_trucks',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteTruck($id = false){
		if($id && $this->db->delete('con_trucks',array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function getSlumps(){
		$q = $this->db->get_where('custom_field',array('code'=>'slumps'));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getSlumpByID($id = false){
		$q = $this->db->get_where('custom_field',array('code'=>'slumps','id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addSlump($data = false){
		if($data && $this->db->insert('con_slumps',$data)){
			return true;
		}
		return false;
	}
	public function updateSlump($id = false, $data = false){
		if($id && $data && $this->db->update('con_slumps',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteSlump($id = false){
		if($id && $this->db->delete('con_slumps',array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function getCastingTypes(){
		$q = $this->db->get("con_casting_types");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getCastingTypeByID($id = false){
		$q = $this->db->get_where('custom_field',array('code'=>'casting_types','id'=>$id));

		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addCastingType($data = false){
		if($data && $this->db->insert('con_casting_types',$data)){
			return true;
		}
		return false;
	}
	public function updateCastingType($id = false, $data = false){
		if($id && $data && $this->db->update('con_casting_types',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteCastingType($id = false){
		if($id && $this->db->delete('con_casting_types',array('id'=>$id))){
			return true;
		}
		return false;
	}
	
	public function getDeliveryByID($id = false){
		$q = $this->db->get_where('con_deliveries',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function addDelivery($data = false, $stockmoves = false, $used_fuels, $accTrans = false)
	{
		$this->db->trans_start();
		if ($data && $this->db->insert("con_deliveries", $data)) {
			$delivery_id = $this->db->insert_id();
			if ($used_fuels) {
				foreach ($used_fuels as $used_fuel) {
					$used_fuel['delivery_id'] = $delivery_id;
					$this->db->insert("used_fuels",$used_fuel);
				}
			}
			if ($stockmoves) {
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $delivery_id;
					$this->db->insert("stock_movement", $stockmove);
					if ($this->site->stockMovement_isOverselling($stockmove)) {
                        return false;
                    }
				}
			}
			if ($accTrans) {
				foreach ($accTrans as $accTran) {
					$accTran['tran_no'] = $delivery_id;
					$this->db->insert("gl_trans", $accTran);
				}
			}
			$this->synceSaleOrder($data["biller_id"], $data["customer_id"], $data["location_id"], $data["date"], $data["stregth_id"], $data["quantity"], $delivery_id);
		}
		$this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the delivery (Add:Concretes_model.php)');
        } else {
            return $delivery_id;
        }
        return false;
	}
	
	public function updateDeliveryStatus($id = false, $data = false)
	{
		if ($id && $this->db->update("con_deliveries", $data, array("id" => $id))) {
			return true;
		}
		return false;
	}
	
	public function updateDelivery($id = false, $data = false, $stockmoves = false, $used_fuels = false, $accTrans = false) 
	{
		$this->db->trans_start();
        $oitems   = $this->site->getStockMovementByTransactionID($id);
		$delivery = $this->getDeliveryByID($id);
		if ($delivery && $this->db->update("con_deliveries", $data, array("id" => $id))) {  
			$this->db->delete("used_fuels", array("delivery_id" => $id));
			$this->site->delete_stock_movement('CDelivery', $id);
			$this->site->deleteAccTran('CDelivery', $id);
			if ($used_fuels) {
				$this->db->insert_batch("used_fuels", $used_fuels);
			}
			if ($stockmoves) {
				foreach ($stockmoves as $stockmove) {
					$this->db->insert("stock_movement", $stockmove);
					if ($this->site->stockMovement_isOverselling($stockmove)) {
                        return false;
                    }
				}
				foreach ($oitems as $oitem) {
                    if ($this->site->stockMovement_isOverselling($oitem)) {
                        return false;
                    }
                }
			}
			if ($accTrans) {
				foreach ($accTrans as $accTran) {
					$this->db->insert("gl_trans", $accTran);
				}
			}
			$this->removeConcreateQty($id, $delivery->quantity);
			$this->synceSaleOrder($data["biller_id"], $data["customer_id"], $data["location_id"], $data["date"], $data["stregth_id"], $data["quantity"],$id);
		}
		$this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while updating the delivery (Add:Concretes_model.php)');
        } else {
            return $id;
        }
        return false;
	}
	
	public function deleteDelivery($id = false)
	{
		$delivery = $this->getDeliveryByID($id);
		if ($delivery && $this->db->delete("con_deliveries", array("id" => $id))) {
			$this->db->delete("used_fuels", array("delivery_id" => $id));
			$this->site->delete_stock_movement('CDelivery', $id);
			$this->site->deleteAccTran('CDelivery', $id);
			$this->removeConcreateQty($id, $delivery->quantity);
			return true;
		}
		return false;
	}
	
	public function getLastDelivery($customer_id = false)
	{
		if ($customer_id) {
			$this->db->where("con_deliveries.customer_id",$customer_id);
		}
		$this->db->order_by("con_deliveries.id","desc");
		$this->db->limit(1);
		$q = $this->db->get("con_deliveries");
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function syncDelivery($delivery_id = false, $data = false, $used_fuels = false)
	{
		$this->db->update("con_deliveries", $data,array("id" => $delivery_id));
		$this->db->delete("used_fuels", array("delivery_id" => $delivery_id));
		if ($used_fuels) {
			$this->db->insert_batch("used_fuels", $used_fuels);
		}
		return true;
	}
	
	public function getDayQuantity($id = false,$biller_id = false, $date = false, $customer_id = false, $stregth_id = false, $location_id = false, $group_id = false)
	{
		if ($id && $biller_id && $date && $customer_id && $stregth_id) {
			if ($group_id > 0) {
				$this->db->where("con_deliveries.group_id", $group_id);
			}
			$this->db->where("con_deliveries.biller_id", $biller_id);
			$this->db->where("con_deliveries.status!=","spoiled");
			$this->db->where("con_deliveries.id <=",$id);
			$this->db->where("date(".$this->db->dbprefix('con_deliveries').".date)", date($date));
			$this->db->where("con_deliveries.customer_id", $customer_id);
			$this->db->where("con_deliveries.stregth_id", $stregth_id);
			$this->db->where("IFNULL(".$this->db->dbprefix('con_deliveries').".location_id,'')", $location_id);
			$this->db->select("
								SUM(".$this->db->dbprefix('con_deliveries').".quantity) as total_quantity, 
								SUM(".$this->db->dbprefix('con_deliveries').".markup_qty) as total_markup_qty, 
								count(".$this->db->dbprefix('con_deliveries').".id) as delivery_times
							");
			$q = $this->db->get("con_deliveries");
			if ($q->num_rows() > 0) {
				return $q->row();
			}
			return FALSE;
		}
	}
	public function getStregths(){
		$allow_category = $this->site->getCategoryByProject();
		if ($allow_category) {
			$this->db->where_in("products.category_id", $allow_category);
		}
		$this->db->where("products.stregth",1);
		$q = $this->db->get("products");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getProducts(){
		$allow_category = $this->site->getCategoryByProject();
		if ($allow_category) {
			$this->db->where_in("products.category_id", $allow_category);
		}
		$this->db->where("products.stregth",0);
		$q = $this->db->get("products");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getStregthByID($id = false){
		$q = $this->db->get_where('products',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	function getBomProductByStandProduct($pid = false, $biller_id = false)
	{
        $where = "";
        if ($biller_id) {
            $where = " AND IFNULL(".$this->db->dbprefix("bom_products").".biller_id,0) IN (0,".$biller_id.")";
        }
		$q = $this->db->query('
							SELECT
							'.$this->db->dbprefix("bom_products").'.product_id,
							'.$this->db->dbprefix("bom_products").'.unit_id,
							'.$this->db->dbprefix("bom_products").'.quantity * unit_qty AS quantity,
							'.$this->db->dbprefix("product_units").'.unit_qty,
							'.$this->db->dbprefix("units").'.`code`,
							'.$this->db->dbprefix("products").'.cost,
							'.$this->db->dbprefix("products").'.code as product_code,
							'.$this->db->dbprefix("products").'.type as product_type,
							'.$this->db->dbprefix("products").'.name as product_name,
							'.$this->Settings->accounting_method.' AS accounting_method
						FROM
							'.$this->db->dbprefix("bom_products").'
						INNER JOIN '.$this->db->dbprefix("product_units").' ON '.$this->db->dbprefix("product_units").'.product_id = '.$this->db->dbprefix("bom_products").'.product_id
						AND '.$this->db->dbprefix("product_units").'.unit_id = '.$this->db->dbprefix("bom_products").'.unit_id
						INNER JOIN '.$this->db->dbprefix("units").' ON '.$this->db->dbprefix("units").'.id = '.$this->db->dbprefix("product_units").'.unit_id
						INNER JOIN '.$this->db->dbprefix("products").' ON '.$this->db->dbprefix("products").'.id = '.$this->db->dbprefix("bom_products").'.product_id
						WHERE
							'.$this->db->dbprefix("bom_products").'.standard_product_id = "'.$pid.'" '.$where.'');
		if ($q->num_rows() > 0) {
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getDeliveries($biller_id = false,$project_id = false,$warehouse_id = false,$customer_id = false,$location_id = false,$from_date = false,$to_date = false, $sale_status = false, $sale_id = false, $spoiled = false, $quotation_id = false){
		if(!$quotation_id){
			$quotation_id = 0;
		}
		if($biller_id){
			$this->db->where("con_deliveries.biller_id",$biller_id);
		}
		if($project_id){
			$this->db->where("con_deliveries.project_id",$project_id);
		}
		if($warehouse_id){
			$this->db->where("con_deliveries.warehouse_id",$warehouse_id);
		}
		if($customer_id){
			$this->db->where("con_deliveries.customer_id",$customer_id);
		}
		if($location_id){
			$this->db->where("con_deliveries.location_id",$location_id);
		}
		if($from_date){
			$this->db->where("con_deliveries.date >=",$from_date);
		}
		if($to_date){
			$this->db->where("con_deliveries.date <=",$to_date);
		}
		if($spoiled){
			$this->db->where("con_deliveries.status !=","spoiled");
		}
		if($sale_status){
			if($sale_id){
				$this->db->join("con_sale_items","con_deliveries.id = con_sale_items.con_delivery_id AND con_sale_items.sale_id = ".$sale_id,"LEFT");
				$this->db->where("(".$this->db->dbprefix('con_deliveries').".sale_status = '".$sale_status."' OR ".$this->db->dbprefix('con_sale_items').".con_delivery_id > 0)");
			}else{
				$this->db->where("con_deliveries.sale_status",$sale_status);
			}
			
		}
		$this->db->select("con_deliveries.*,products.code,IFNULL(quote_items.unit_price,".$this->db->dbprefix('products').".price) as price");
		$this->db->join("products","products.id = con_deliveries.stregth_id","left");
		$this->db->join("(SELECT product_id,unit_price FROM ".$this->db->dbprefix('quote_items')." WHERE quote_id = ".$quotation_id." GROUP BY product_id) as quote_items", "quote_items.product_id = con_deliveries.stregth_id","LEFT");
		$q = $this->db->get("con_deliveries");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$row->date = $this->bpas->hrld($row->date);
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function synceDelivery($biller_id = false, $warehouse_id = false, $customer_id = false, $location_id = false, $from_date = false, $to_date = false){
		$where = "";
		if($biller_id){
			$where .= " AND ".$this->db->dbprefix('con_deliveries').".biller_id = ".$biller_id;
		}
		if($warehouse_id){
			$where .= " AND ".$this->db->dbprefix('con_deliveries').".warehouse_id = ".$warehouse_id;
		}
		if($customer_id){
			$where .= " AND ".$this->db->dbprefix('con_deliveries').".customer_id = ".$customer_id;
		}
		if($location_id){
			$where .= " AND ".$this->db->dbprefix('con_deliveries').".location_id = ".$location_id;
		}
		if($from_date){
			$where .= " AND ".$this->db->dbprefix('con_deliveries').".date >='".$from_date."'";
		}
		if($to_date){
			$where .= " AND ".$this->db->dbprefix('con_deliveries').".date <='".$to_date."'";
		}
		$this->db->query("UPDATE ".$this->db->dbprefix('con_deliveries')."
							LEFT JOIN ".$this->db->dbprefix('con_sale_items')." ON ".$this->db->dbprefix('con_sale_items').".con_delivery_id = ".$this->db->dbprefix('con_deliveries').".id 
							SET ".$this->db->dbprefix('con_deliveries').".sale_status = IF(IFNULL( ".$this->db->dbprefix('con_sale_items').".con_delivery_id, '' ) = '','pending','completed')
							WHERE 1=1 ".$where."
						");
		return true;
	}
	public function getSaleByID($id = false){
		$q = $this->db->get_where('con_sales',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSummarySaleItems($id = false){
		$this->db->select("product_code,product_name,unit_price,sum(unit_quantity) as unit_quantity, sum(subtotal) as subtotal");
		$this->db->group_by("product_id,unit_price");
		$q = $this->db->get_where('con_sale_items',array('sale_id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSaleItems($id = false){
		$this->db->select("con_sale_items.*,con_deliveries.reference_no, con_deliveries.date, con_deliveries.casting_type_name,con_deliveries.markup_qty");
		$this->db->join("con_deliveries","con_deliveries.id = con_sale_items.con_delivery_id","LEFT");
		$q = $this->db->get_where('con_sale_items',array('sale_id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getSaleDailyItems($id = false){
		$this->db->select("sum(".$this->db->dbprefix('con_sale_items').".quantity) as quantity,
							con_sale_items.product_name,
							con_sale_items.product_code,
							con_sale_items.unit_price,
							con_deliveries.date,
							con_deliveries.location_name
						");
		$this->db->group_by("con_sale_items.product_id,con_sale_items.unit_price, con_deliveries.date, con_deliveries.location_id");
		$this->db->join("con_deliveries","con_deliveries.id = con_sale_items.con_delivery_id","LEFT");
		$q = $this->db->get_where('con_sale_items',array('sale_id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getSaleSummaryItems($id = false){
		$this->db->select("sum(".$this->db->dbprefix('con_sale_items').".quantity) as quantity,
							con_sale_items.product_name,
							con_sale_items.product_code,
							con_sale_items.unit_price
						");
		$this->db->group_by("con_sale_items.product_id,con_sale_items.unit_price");
		$this->db->join("con_deliveries","con_deliveries.id = con_sale_items.con_delivery_id","LEFT");
		$q = $this->db->get_where('con_sale_items',array('sale_id'=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function addSale($data = false, $items = false, $accTrans = false){
		if($data && $this->db->insert("con_sales",$data)){
			$sale_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["sale_id"] = $sale_id;
					$this->db->insert("con_sale_items",$item);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran["tran_no"] = $sale_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			$this->synceDelivery($data["biller_id"],$data["warehouse_id"],$data["customer_id"],$data["location_id"],$data["from_date"],$data["to_date"]);
			return true;
		}
		return false;
	}
	public function updateSale($id = false, $data = false, $items = false, $accTrans = false){
		if($id && $this->db->update("con_sales",$data,array("id"=>$id))){
			$this->db->delete("con_sale_items",array("sale_id"=>$id));
			$this->site->deleteAccTran("CSale",$id);
			if($items){
				$this->db->insert_batch("con_sale_items",$items);
			}
			if($accTrans){
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->synceDelivery();
			return true;
		}
		return false;
	}
	public function deleteSale($id = false){
		$sale = $this->getSaleByID($id);
		if($id && $this->db->delete("con_sales",array("id"=>$id))){
			$this->db->delete("con_sale_items",array("sale_id"=>$id));
			$this->site->deleteAccTran("CSale",$id);
			$this->synceDelivery($sale->biller_id,$sale->warehouse_id,$sale->customer_id,$sale->location_id,$sale->from_date,$sale->to_date);
			return true;
		}
		return false;
	}

	public function getOfficerNames($term = false, $biller_id = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		if($biller_id){
			$this->db->where("hr_employees_working_info.biller_id",$biller_id);
		}
        $this->db->select('hr_employees.*', FALSE);
        $this->db->group_by('hr_employees.id');
        $this->db->where("({$this->db->dbprefix('hr_employees')}.lastname_kh LIKE '%" . $term . "%' OR {$this->db->dbprefix('hr_employees')}.lastname LIKE '%" . $term . "%')");
        $this->db->limit($limit);

        $this->db->join("hr_employees_working_info", "hr_employees_working_info.employee_id = hr_employees.id", "left");
        $q = $this->db->get('hr_employees');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
    }
	
	public function getDriverNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select('companies.*', FALSE);
        $this->db->group_by('companies.id');
        $this->db->where("({$this->db->dbprefix('companies')}.company LIKE '%" . $term . "%' OR {$this->db->dbprefix('companies')}.name LIKE '%" . $term . "%')");
        $this->db->where('group_name','driver');
        $this->db->limit($limit);
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
    }
	
	public function getTruckNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select('con_trucks.*', FALSE);
        $this->db->group_by('con_trucks.id');
        $this->db->where("({$this->db->dbprefix('con_trucks')}.code LIKE '%" . $term . "%' OR {$this->db->dbprefix('con_trucks')}.plate LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('con_trucks');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
    }
	
	public function getStregthNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
        $this->db->select('products.*', FALSE);
        $this->db->group_by('products.id');
        $this->db->where("type = 'bom' AND stregth = 1 AND status = 1 AND ". "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
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
	
	
	public function getRawMaterialNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		$this->db->where('products.status',1);
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, unit,' . $this->db->dbprefix('products') . '.name as name')
            ->where("type = 'raw_material' AND "
                . "(" . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')")
            ->group_by('products.id')->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	
	public function getFuelByID($id = false){
		$q = $this->db->get_where("con_fuels",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getFuelItems($fuel_id = false){
		if($fuel_id){
			$this->db->where("fuel_id",$fuel_id);
		}
		$q = $this->db->get("con_fuel_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addFuel($data = false, $items = false, $stockmoves = false, $used_fuels=false, $accTrans = false){
		if($data && $this->db->insert("con_fuels",$data)){
			$fuel_id = $this->db->insert_id();
			foreach($items as $item){
				$item["fuel_id"] = $fuel_id;
				$this->db->insert("con_fuel_items",$item);
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove["transaction_id"] = $fuel_id;
					$this->db->insert("stock_movement",$stockmove);
				}
			}
			if($used_fuels){
				foreach($used_fuels as $used_fuel){
					$used_fuel['fuel_id'] = $fuel_id;
					$this->db->insert("used_fuels",$used_fuel);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran["tran_no"] = $fuel_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			$this->synceFuel($fuel_id);
			//$this->site->updateReference();
			return true;
		}
		return false;
	}
	public function updateFuel($id = false, $data = false, $items = false, $stockmoves = false, $used_fuels=false, $accTrans = false){
		if($id && $this->db->update("con_fuels",$data,array("id"=>$id))){
			$this->db->delete("con_fuel_items",array("fuel_id"=>$id));
			$this->db->delete("used_fuels",array("fuel_id"=>$id));
			$this->site->delete_stock_movement('CFuel',$id);
			$this->site->deleteAccTran('CFuel',$id);
			if($items){
				$this->db->insert_batch("con_fuel_items",$items);
			}
			if($stockmoves){
				$this->db->insert_batch("stock_movement",$stockmoves);
			}
			if($used_fuels){
				$this->db->insert_batch("used_fuels",$used_fuels);
			}
			if($accTrans){
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->synceFuel($id);
			return true;
		}
		return false;
	}
	public function deleteFuel($id = false){
		if($id && $this->db->delete("con_fuels",array("id"=>$id))){
			$this->db->delete("con_fuel_items",array("fuel_id"=>$id));
			$this->db->delete("used_fuels",array("fuel_id"=>$id));
			$this->site->delete_stock_movement('CFuel',$id);
			$this->site->deleteAccTran('CFuel',$id);
			$this->synceFuel($id);
			return true;
		}
		return false;
	}
	
	
	public function getFuelItem($fuel_id = false, $truck_id = false){
		if($fuel_id){
			$this->db->where("con_fuel_items.fuel_id",$fuel_id);
		}
		if($truck_id){
			$this->db->where("con_fuel_items.truck_id",$truck_id);
		}
		$q = $this->db->get("con_fuel_items");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getLastFuel($fuel_id = false){
		$this->db->where("con_fuels.id <",$fuel_id);
		$this->db->limit(1);
		$this->db->order_by("con_fuels.date,con_fuels.id","desc");
		$q = $this->db->get("con_fuels");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getPreviousFuel($fuel_id = false){
		$this->db->where("con_fuels.id >",$fuel_id);
		$this->db->limit(1);
		$this->db->order_by("con_fuels.date,con_fuels.id");
		$q = $this->db->get("con_fuels");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function synceFuel($fuel_id = false){
		$last_fuel = $this->getLastFuel($fuel_id);
		$fuel = $this->getFuelByID($fuel_id);
		if($fuel == false){
			$previous_fuel = $this->getPreviousFuel($fuel_id);
			if($previous_fuel){
				$fuel_id = $previous_fuel->id;
			}
		}
		if($last_fuel){
			$last_fuel_items = $this->getFuelItems($last_fuel->id);
			if($last_fuel_items){
				foreach($last_fuel_items as $last_fuel_item){
					$fuel_item = $this->getFuelItem($fuel_id,$last_fuel_item->truck_id);
					if($fuel_item){
						$this->db->update("con_fuel_items",array("to_date"=>$fuel_item->from_date),array("id"=>$last_fuel_item->id));
					}else{
						$this->db->update("con_fuel_items",array("to_date"=>null),array("id"=>$last_fuel_item->id));
					}
				}
			}
		}
	}
	
	public function getAllFuleItems($post = null){
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('con_fuels.biller_id', $post['biller']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('con_fuels.warehouse_id', $post['warehouse']);
		}
		if(isset($post['pump']) && $post['pump']){
			$this->db->where('con_fuel_items.truck_id', $post['pump']);
		}
		if(isset($post['truck']) && $post['truck']){
			$this->db->where('con_fuel_items.truck_id', $post['truck']);
		}
		if(isset($post['driver']) && $post['driver']){
			$this->db->where('con_fuel_items.driver_id', $post['driver']);
		}
		if(isset($post['start_date']) && $post['start_date']){
			$this->db->where('con_fuels.date >=', $this->bpas->fld($post['start_date']));
		}else{
			$this->db->where('con_fuels.date >=', $this->bpas->fld($this->bpas->hrld(date('Y-m-d H:i:s'))));
		}
		if(isset($post['end_date']) && $post['end_date']){
			$this->db->where('con_fuels.date <=', $this->bpas->fld($post['end_date']));
		}else{
			$this->db->where('con_fuels.date <=', $this->bpas->fld($this->bpas->hrld(date('Y-m-d H:i:s'))));
		}
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('con_fuels.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('con_fuels.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids =  json_decode($this->session->userdata('warehouse_id'));
			$warehouse_ids[] = 99999;
			$this->db->where_in('con_fuels.warehouse_id', $warehouse_ids);
		}
		
		$this->db->select("con_fuel_items.*,con_fuels.reference_no,con_fuels.date");
		$this->db->join("con_fuels","con_fuels.id = con_fuel_items.fuel_id","inner");
		$q = $this->db->get("con_fuel_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getSaleDetails($post = null)
    {
		$user = $this->site->getUser($this->session->userdata("user_id"));
		if(isset($post['user']) && $post['user']){
			$this->db->where('con_sales.created_by', $post['user']);
		}
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('con_sales.biller_id', $post['biller']);
		}
		if(isset($post['project']) && $post['project']){
			$this->db->where('con_sales.project_id', $post['project']);
		}
		if(isset($post['customer']) && $post['customer']){
			$this->db->where('con_sales.customer_id', $post['customer']);
		}
		if(isset($post['product']) && $post['product']){
			$this->db->where('con_sale_items.product_id', $post['product']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('con_sales.warehouse_id', $post['warehouse']);
		}
		if (isset($post['start_date']) && $post['start_date']) {
            $this->db->where('date >=', $this->bpas->fld($post['start_date']));
        }else{
			$this->db->where('date(date) >=', date('Y-m-d'));
		}
		if (isset($post['end_date']) && $post['end_date']) {
			$this->db->where('date <=', $this->bpas->fld($post['end_date'],false,1));
        }else{
			$this->db->where('date(date) <=', date('Y-m-d'));
		}
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = array();
			if($projects){
				foreach($projects as $pr){
					$project_details[] = $pr;
				}
			}
			if(!isset($post['project'])){
				if($project_details){
					$this->db->where_in('con_sales.project_id', $project_details);
				}
			}
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('con_sales.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('con_sales.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('con_sales.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
    	$this->db->select("con_sales.*", false)
                ->from("con_sales")
				->join('con_sale_items', 'con_sales.id = con_sale_items.sale_id', 'left')
				->order_by("con_sales.id","desc")
				->group_by("con_sales.id");				
		$q = $this->db->get();		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	
	public function getAllCategoriesByInventoryInOut($category_id = false)
	{		
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in('categories.id',$allow_category);
		}
		if($category_id){
			$this->db->where("id", $category_id);
		}
		$this->db->order_by("parent_id");
		$q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getProductBySales($category_id = false, $start_date = false, $end_date = false, $product = false, $warehouse_id = false, $biller = false,  $customer = false)
	{
		$user = $this->site->getUser($this->session->userdata("user_id"));
		$sql = "";
		
		if ($category_id) {
            $sql .= " AND ".$this->db->dbprefix('products').".category_id = {$category_id}";			
        }
		if ($biller) {
            $sql .= " AND ".$this->db->dbprefix('con_sales').".biller_id = {$biller}";			
        }
		if ($customer) {
            $sql .= " AND ".$this->db->dbprefix('con_sales').".customer_id = {$customer}";			
        }
		if($product){
			$sql .= " AND ".$this->db->dbprefix('con_sale_items').".product_id= {$product}";
		}
		if ($start_date) {
			$sql .= " AND date >= '{$this->bpas->fld($start_date)}'";
        }
		if($end_date){
			$sql .= " AND date <= '{$this->bpas->fld($end_date,false,1)}'";
		}
		if(!$start_date && !$end_date){
			$sql .= " AND date(date) = '".date('Y-m-d')."' ";
		}
		if ($warehouse_id) {
            $sql .= " AND ".$this->db->dbprefix('con_sales').".warehouse_id = {$warehouse_id}";			
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$sql .= " AND ".$this->db->dbprefix('con_sales').".created_by = {$this->session->userdata('user_id')}";
		}
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$sql .= " AND ".$this->db->dbprefix('con_sales').".biller_id = {$this->session->userdata('biller_id')}";
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$warehouse_ids = str_replace('[','(',$this->session->userdata('warehouse_id'));
			$warehouse_ids = str_replace(']',')',$warehouse_ids);
			$sql .= " AND ".$this->db->dbprefix('con_sales').".warehouse_id IN ".$warehouse_ids;
		}
		
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = "";
			if($projects){
				foreach($projects as $pr){
					$project_details .= $pr.",";
				}
			}
			
			if(!$projects && $projects[0] != 'all'){
				$rtrim = rtrim($project_details,",");
				if($rtrim){
					$sql .= " AND ".$this->db->dbprefix('con_sales').".project_id IN ({$rtrim})";
				}
				
			}

		}
		$this->db->query("SET group_concat_max_len = 10000000");
		$result = $this->db->query("SELECT
										".$this->db->dbprefix('con_sale_items').".product_id,
										".$this->db->dbprefix('con_sale_items').".product_code,
										".$this->db->dbprefix('con_sale_items').".product_type,
										".$this->db->dbprefix('con_sale_items').".unit_price,
										".$this->db->dbprefix('con_sale_items').".product_unit_id,
										GROUP_CONCAT(".$this->db->dbprefix('con_sale_items').".raw_materials SEPARATOR'#') as raw_materials,
										product_name,
										sum(".$this->db->dbprefix('con_sale_items').".cost * ".$this->db->dbprefix('con_sale_items').".quantity) as cost,
										sum(".$this->db->dbprefix('con_sale_items').".unit_price)  / count(".$this->db->dbprefix('con_sale_items').".id) as price,
										SUM(".$this->db->dbprefix('con_sale_items').".quantity) as quantity,
										SUM(".$this->db->dbprefix('con_sale_items').".subtotal) as subtotal,
										".$this->db->dbprefix('products').".quantity as stock_quantity,
										".$this->db->dbprefix('con_sales').".reference_no,
										".$this->db->dbprefix('con_sales').".customer
									FROM
										".$this->db->dbprefix('con_sale_items')."
									INNER JOIN ".$this->db->dbprefix('con_sales')." ON ".$this->db->dbprefix('con_sales').".id = ".$this->db->dbprefix('con_sale_items').".sale_id
									LEFT JOIN ".$this->db->dbprefix('products')." ON ".$this->db->dbprefix('products').".id = product_id
									WHERE 1=1 {$sql}
									GROUP BY
										".$this->db->dbprefix('products').".id, 
										unit_price, 
										product_unit_id")->result();
		return $result;
	}
	
	public function getDeliveryStockmoves($post = null)
    {
		$user = $this->site->getUser($this->session->userdata("user_id"));
		if(isset($post['user']) && $post['user']){
			$this->db->where('con_deliveries.created_by', $post['user']);
		}
		if(isset($post['biller']) && $post['biller']){
			$this->db->where('con_deliveries.biller_id', $post['biller']);
		}
		if(isset($post['project']) && $post['project']){
			$this->db->where('con_deliveries.project_id', $post['project']);
		}
		if(isset($post['customer']) && $post['customer']){
			$this->db->where('con_deliveries.customer_id', $post['customer']);
		}
		if(isset($post['product']) && $post['product']){
			$this->db->where('con_deliveries.stregth_id', $post['product']);
		}
		if(isset($post['warehouse']) && $post['warehouse']){
			$this->db->where('con_deliveries.warehouse_id', $post['warehouse']);
		}
		if (isset($post['start_date']) && $post['start_date']) {
            $this->db->where('con_deliveries.date >=', $this->bpas->fld($post['start_date']));
        }else{
			$this->db->where('date('.$this->db->dbprefix('con_deliveries').'.date) >=', date('Y-m-d'));
		}
		if (isset($post['end_date']) && $post['end_date']) {
			$this->db->where('con_deliveries.date <=', $this->bpas->fld($post['end_date'],false,1));
        }else{
			$this->db->where('date('.$this->db->dbprefix('con_deliveries').'.date) <=', date('Y-m-d'));
		}
		if (!$this->Owner && !$this->Admin && $this->Settings->project) {
			$projects = json_decode($user->project_ids); 
			$project_details = array();
			if($projects){
				foreach($projects as $pr){
					$project_details[] = $pr;
				}
			}
			if(!isset($post['project'])){
				if($project_details){
					$this->db->where_in('con_deliveries.project_id', $project_details);
				}
			}
		}
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
			$this->db->where('con_deliveries.created_by', $this->session->userdata('user_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('con_deliveries.biller_id', $this->session->userdata('biller_id'));
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
			$this->db->where_in('con_deliveries.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
		}
    	$this->db->select("
							stock_movement.product_id,
							con_deliveries.customer_id,
							con_deliveries.location_id,
							date(".$this->db->dbprefix('con_deliveries').".date) as date,
							con_deliveries.customer,
							con_deliveries.location_name,
							con_deliveries.stregth_name,
							con_deliveries.stregth_id,
							sum(".$this->db->dbprefix('con_deliveries').".quantity) as delivery_quantity,
							count(".$this->db->dbprefix('con_deliveries').".id) as delivery_times,
							products.code as product_code,
							products.name as product_name,
							sum(".$this->db->dbprefix('stock_movement').".quantity *(-1)) as quantity", false)
                ->from("stock_movement")
				->join("con_deliveries", "con_deliveries.id = stock_movement.transaction_id", "left")
				->join("products", "products.id = stock_movement.product_id", "left")
				->where("stock_movement.transaction","CDelivery")
				->order_by("con_deliveries.date,stock_movement.product_id","desc")
				->group_by("con_deliveries.customer_id,con_deliveries.location_id,con_deliveries.stregth_id,stock_movement.product_id,date(".$this->db->dbprefix('con_deliveries').".date)");				
		$q = $this->db->get();		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	
	public function getRawMeterialOut($product_id = false, $warehouse_id = false, $from_date = false, $to_date = false){
		$this->db->select("sum(".$this->db->dbprefix('stock_movement').".quantity * (-1) ) AS system_qty");
		$this->db->where("stock_movement.transaction","CDelivery");
		$this->db->where("stock_movement.product_id",$product_id);
		$this->db->where("stock_movement.warehouse_id",$warehouse_id);
		$this->db->where("stock_movement.date >=",$from_date);
		$this->db->where("stock_movement.date <=",$to_date);
		$q = $this->db->get("stock_movement");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getDailyStockmove($date = false, $cf6 = false, $warehouse_id = false){
		$this->db->select("sum(".$this->db->dbprefix('stock_movement').".quantity * (-1) ) AS quantity,stock_movement.product_id,products.name,products.code");
		$this->db->join("products","stock_movement.product_id = products.id","inner");
		$this->db->where("stock_movement.transaction","CDelivery");
		$this->db->where("stock_movement.warehouse_id",$warehouse_id);
		$this->db->where("date(".$this->db->dbprefix('stock_movement').".date)",$date);
		$this->db->where("products.cf6",$cf6);
		$q = $this->db->get("stock_movement");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getProductByCF6($cf6 = false){
		$q = $this->db->get_where("products",array("cf6"=>$cf6));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getAdjustmentByDate($date = false, $warehouse_id = false){
		$this->db->join("con_adjustment_items","con_adjustment_items.adjustment_id = con_adjustments.id","INNER");
		$this->db->where("con_adjustments.warehouse_id",$warehouse_id);
		$this->db->where("con_adjustment_items.date", $date);
		$q = $this->db->get("con_adjustments");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getAdjustmentByID($id = false){
		$q = $this->db->get_where("con_adjustments",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getAdjustmentItems($adjustment_id = false){
		$q = $this->db->get_where("con_adjustment_items",array("adjustment_id"=>$adjustment_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addAdjustment($data = false, $items = false)
	{
		if ($this->db->insert("con_adjustments", $data)) {
			$adjustment_id = $this->db->insert_id();
			foreach ($items as $items) {
				$items["adjustment_id"] = $adjustment_id;
				$this->db->insert("con_adjustment_items", $items);
			}
			return true;
		}
		return false;
	}
	
	public function updateAdjustment($id = false, $data = false, $items = false)
	{
		if ($this->db->update("con_adjustments", $data, array("id"=>$id))) {
			$this->db->delete("con_adjustment_items", array("adjustment_id" => $id));
			if ($items) {
				$this->db->insert_batch("con_adjustment_items", $items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteAdjustment($id = false)
	{
		if ($this->db->delete("con_adjustments", array("id" => $id))) {
			$this->db->delete("con_adjustment_items", array("adjustment_id" => $id));
			$this->site->delete_stock_movement('CAdjustment', $id);
			$this->site->deleteAccTran('CAdjustment', $id);
			return true;
		}
		return false;
	}
	
	public function approveAdjustment($id = false, $data = false, $stockmoves = false, $accTrans = false)
    {
        if ($this->db->update('con_adjustments', $data, array('id' => $id))) {
			if ($stockmoves) {
				$this->db->insert_batch('stock_movement', $stockmoves);
			}
			if ($accTrans) {
				$this->db->insert_batch('gl_trans', $accTrans);
			}
            return true;
        }
        return false;
    }
	
	public function getErrorByID($id = false)
	{
		$q = $this->db->get_where("con_errors",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function getErrorItems($error_id = false)
	{
		if ($error_id) {
			$this->db->where("error_id",$error_id);
		}
		$q = $this->db->get("con_error_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function getErrorMaterials($error_id = false){
		if($error_id){
			$this->db->where("error_id",$error_id);
		}
		$q = $this->db->get("con_error_materials");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addError($data = false, $items = false, $materials = false, $stockmoves = false, $accTrans = false){
		if($data && $this->db->insert("con_errors",$data)){
			$error_id = $this->db->insert_id();
			foreach($items as $item){
				$error_item_id = $item["error_item_id"];
				unset($item["error_item_id"]);
				$item["error_id"] = $error_id;
				$this->db->insert("con_error_items",$item);
				$new_error_item_id = $this->db->insert_id();
				if($materials[$error_item_id]){
					foreach($materials[$error_item_id] as $material){
						$material["error_id"] = $error_id;
						$material["error_item_id"] = $new_error_item_id;
						$this->db->insert("con_error_materials",$material);
					}
				}
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove["transaction_id"] = $error_id;
					$this->db->insert("stock_movement",$stockmove);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran["tran_no"] = $error_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			return true;
		}
		return false;
	}
	public function updateError($id = false, $data = false, $items = false, $materials = false, $stockmoves = false, $accTrans = false){
		if($id && $this->db->update("con_errors",$data,array("id"=>$id))){
			$this->db->delete("con_error_items",array("error_id"=>$id));
			$this->db->delete("con_error_materials",array("error_id"=>$id));
			$this->site->delete_stock_movement('CError',$id);
			$this->site->deleteAccTran('CError',$id);
			if($items){
				foreach($items as $item){
					$error_item_id = $item["error_item_id"];
					unset($item["error_item_id"]);
					$this->db->insert("con_error_items",$item);
					$new_error_item_id = $this->db->insert_id();
					if($materials[$error_item_id]){
						foreach($materials[$error_item_id] as $material){
							$material["error_id"] = $id;
							$material["error_item_id"] = $new_error_item_id;
							$this->db->insert("con_error_materials",$material);
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
			return true;
		}
		return false;
	}
	public function deleteError($id = false){
		if($id && $this->db->delete("con_errors",array("id"=>$id))){
			$this->db->delete("con_error_items",array("error_id"=>$id));
			$this->db->delete("con_error_materials",array("error_id"=>$id));
			$this->site->delete_stock_movement('CError',$id);
			$this->site->deleteAccTran('CError',$id);
			return true;
		}
		return false;
	}
	public function getCreditSale($customer_id = false, $credit_day = false)
	{
        $where = "";
        if($credit_day){
            $where = " AND DATE(".$this->db->dbprefix('sales').".date) <= '".date('Y-m-d')."' - INTERVAL ".$credit_day." DAY";
        }
		$q = $this->db->query("SELECT
									SUM(ROUND((grand_total-(IFNULL(bpas_payments.paid,0))-(IFNULL(bpas_payments.discount,0))-(IFNULL(bpas_return.total_return + total_return_paid,0))),".$this->Settings->decimals.")) as balance,
									SUM(IF((ROUND((grand_total-(IFNULL(bpas_payments.paid,0))-(IFNULL(bpas_payments.discount,0))-(IFNULL(bpas_return.total_return + total_return_paid,0))),".$this->Settings->decimals.")) > 0,(ROUND((grand_total-(IFNULL(bpas_payments.paid,0))-(IFNULL(bpas_payments.discount,0))-(IFNULL(bpas_return.total_return + total_return_paid,0))),".$this->Settings->decimals.")) / sale_items.unit_price,0)) as balance_qty
								FROM
									".$this->db->dbprefix('sales')."
								LEFT JOIN ( SELECT sum(subtotal / quantity) AS unit_price, sale_id FROM ".$this->db->dbprefix('sale_items')." GROUP BY sale_id ) AS sale_items ON sale_items.sale_id = ".$this->db->dbprefix('sales').".id	
								LEFT JOIN (
									SELECT
										sale_id,
										IFNULL(sum(amount), 0) AS paid,
										IFNULL(sum(discount), 0) AS discount
									FROM
										bpas_payments
									GROUP BY
										sale_id
								) AS bpas_payments ON bpas_payments.sale_id = ".$this->db->dbprefix('sales').".id
								LEFT JOIN (
									SELECT
										sum(abs(grand_total)) AS total_return,
										sum(paid) AS total_return_paid,
										sale_id
									FROM
										".$this->db->dbprefix('sales')."
									WHERE
										".$this->db->dbprefix('sales').".sale_id > 0
									AND ".$this->db->dbprefix('sales').".sale_status = 'returned'
									GROUP BY
										".$this->db->dbprefix('sales').".sale_id
								) AS bpas_return ON bpas_return.sale_id = ".$this->db->dbprefix('sales').".id
								WHERE
									".$this->db->dbprefix('sales').".customer_id = '".$customer_id."'
								AND (".$this->db->dbprefix('sales').".sale_id IS NULL OR ".$this->db->dbprefix('sales').".sale_id = 0)
								".$where."
								");
			if($q->num_rows() > 0){
				return $q->row();
			}
			return false;

	}
	public function getCreditDelivery($customer_id = false, $credit_day = false){
		$where = "";
		if($credit_day){
			$this->db->where("DATE(".$this->db->dbprefix('con_deliveries').".date) <= '".date('Y-m-d')."' - INTERVAL ".$credit_day." DAY");
        }
		$this->db->where("con_deliveries.customer_id",$customer_id);
		$this->db->where("con_deliveries.sale_status","pending");
		$this->db->where("con_deliveries.status !=","spoiled");
		$this->db->select("SUM(".$this->db->dbprefix('con_deliveries').".quantity) as balance_qty,
							SUM(".$this->db->dbprefix('con_deliveries').".quantity * IFNULL(quotation.unit_price,".$this->db->dbprefix('products').".price)) as balance
						");
		$this->db->join("products","products.id = con_deliveries.stregth_id","LEFT");
		$this->db->join("(SELECT
							customer_id,
							".$this->db->dbprefix('quote_items').".product_id,
							".$this->db->dbprefix('quote_items').".unit_price
						FROM
							".$this->db->dbprefix('quotes')."
							INNER JOIN ".$this->db->dbprefix('quote_items')." ON ".$this->db->dbprefix('quote_items').".quote_id = ".$this->db->dbprefix('quotes').".id 
						GROUP BY
							customer_id,
							product_id 
						ORDER BY
							".$this->db->dbprefix('quotes').".id DESC) as quotation","quotation.customer_id = con_deliveries.customer_id AND quotation.product_id = con_deliveries.stregth_id","LEFT");
		$q = $this->db->get("con_deliveries");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getCustomers(){
		$q = $this->db->get_where("companies",array("group_id"=>3));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSaleItemByDelivery($delivery_id = false){
		$q = $this->db->get_where("con_sale_items",array("con_delivery_id"=>$delivery_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getSaleConcreteItemByDelivery($con_sale_id = false){
		$q = $this->db->get_where("sale_concrete_items",array("con_sale_id"=>$con_sale_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getDeliveryByReference($reference_no = false, $delivery_id = false){
		if($delivery_id){
			$this->db->where("con_deliveries.id !=", $delivery_id);
		}
		$q = $this->db->get_where("con_deliveries",array("reference_no"=>trim($reference_no)));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getDeliveryBySeal($seal_number = false, $delivery_id = false){
		if($delivery_id){
			$this->db->where("con_deliveries.id !=", $delivery_id);
		}
		$q = $this->db->get_where("con_deliveries",array("seal_number"=>trim($seal_number)));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getMissionTypes(){
		$q = $this->db->get('con_mission_types');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getMissionTypeByID($id = false){
		$q = $this->db->get_where('con_mission_types',array('id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addMissionType($data = false){
		if($data && $this->db->insert('con_mission_types',$data)){
			return true;
		}
		return false;
	}
	public function updateMissionType($id = false, $data = false){
		if($id && $data && $this->db->update('con_mission_types',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteMissionType($id = false){
		if($id && $this->db->delete('con_mission_types',array('id'=>$id))){
			return true;
		}
		return false;
	}
	
	public function getMovingWaitingByID($id = false){
		$q = $this->db->get_where("con_moving_waitings",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getMovingWaitingItems($moving_waiting_id = false){
		if($moving_waiting_id){
			$this->db->where("moving_waiting_id",$moving_waiting_id);
		}
		$q = $this->db->get("con_moving_waiting_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addMovingWaiting($data = false, $items = false, $used_fuels = false){
		if($data && $this->db->insert("con_moving_waitings",$data)){
			$moving_waiting_id = $this->db->insert_id();
			foreach($items as $item){
				$item["moving_waiting_id"] = $moving_waiting_id;
				$this->db->insert("con_moving_waiting_items",$item);
			}
			if($used_fuels){
				foreach($used_fuels as $used_fuel){
					$used_fuel['moving_waiting_id'] = $moving_waiting_id;
					$this->db->insert("used_fuels",$used_fuel);
				}
			}
			return true;
		}
		return false;
	}
	public function updateMovingWaiting($id = false, $data = false, $items = false, $used_fuels = false){
		if($id && $this->db->update("con_moving_waitings",$data,array("id"=>$id))){
			$this->db->delete("con_moving_waiting_items",array("moving_waiting_id"=>$id));
			$this->db->delete("used_fuels",array("moving_waiting_id"=>$id));
			if($items){
				$this->db->insert_batch("con_moving_waiting_items",$items);
			}
			if($used_fuels){
				$this->db->insert_batch("used_fuels",$used_fuels);
			}
			return true;
		}
		return false;
	}
	public function deleteMovingWaiting($id = false){
		if($id && $this->db->delete("con_moving_waitings",array("id"=>$id))){
			$this->db->delete("con_moving_waiting_items",array("moving_waiting_id"=>$id));
			$this->db->delete("used_fuels",array("moving_waiting_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function getMissionByID($id = false){
		$q = $this->db->get_where("con_missions",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getMissionItems($mission_id = false){
		if($mission_id){
			$this->db->where("mission_id",$mission_id);
		}
		$q = $this->db->get("con_mission_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	public function addMission($data = false, $items = false, $used_fuels = false, $payment = false, $accTrans = false){
		if($data && $this->db->insert("con_missions",$data)){
			$mission_id = $this->db->insert_id();
			foreach($items as $item){
				$item["mission_id"] = $mission_id;
				$this->db->insert("con_mission_items",$item);
			}
			if($used_fuels){
				foreach($used_fuels as $used_fuel){
					$used_fuel['mission_id'] = $mission_id;
					$this->db->insert("used_fuels",$used_fuel);
				}
			}
			if($payment){
				$payment["transaction_id"] = $mission_id;
				$this->db->insert("payments",$payment);
				$payment_id = $this->db->insert_id();
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['tran_no'] = $payment_id;
						$this->db->insert("gl_trans",$accTran);
					}
				}
			}
			return true;
		}
		return false;
	}
	
	
	public function updateMission($id = false, $data = false, $items = false, $used_fuels = false, $payment = false, $accTrans = false){
		if($id && $this->db->update("con_missions",$data,array("id"=>$id))){
			$this->db->delete("con_mission_items",array("mission_id"=>$id));
			$this->db->delete("used_fuels",array("mission_id"=>$id));
			$this->deleteMissionPaymentByMission($id);
			if($items){
				$this->db->insert_batch("con_mission_items",$items);
			}
			if($used_fuels){
				$this->db->insert_batch("used_fuels",$used_fuels);
			}
			if($payment){
				$this->db->insert("payments",$payment);
				$payment_id = $this->db->insert_id();
				if($accTrans){
					foreach($accTrans as $accTran){
						$accTran['tran_no'] = $payment_id;
						$this->db->insert("gl_trans",$accTran);
					}
				}
			}
			return true;
		}
		return false;
	}
	public function deleteMission($id = false){
		if($id && $this->db->delete("con_missions",array("id"=>$id))){
			$this->db->delete("con_mission_items",array("mission_id"=>$id));
			$this->db->delete("used_fuels",array("mission_id"=>$id));
			$this->deleteMissionPaymentByMission($id);
			return true;
		}
		return false;
	}
	
	public function getPaymentByMission($mission_id = false){
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->where("transaction_id",$mission_id);
		$this->db->where("transaction","Con Mission");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function deleteMissionPaymentByMission($mission_id = false){
		$payments = $this->getPaymentByMission($mission_id);
		if($this->db->delete("payments",array("transaction"=>"Con Mission","transaction_id"=>$mission_id))){
			if($payments){
				foreach($payments as $payment){
					$this->site->deleteAccTran("Payment",$payment->id);
				}
				
			}
			return true;
		}
		return false;
	}
	
	public function getDriverUsedFuels($biller_id = false, $project_id = false, $from_date = false, $to_date = false, $fuel_expense_id = false){
		if($biller_id){
			$this->db->where("used_fuels.biller_id",$biller_id);
		}
		if($project_id){
			$this->db->where("used_fuels.project_id",$project_id);
		}
		if($from_date){
			$this->db->where("used_fuels.date >=",$from_date);
		}
		if($to_date){
			$this->db->where("used_fuels.date <=",$to_date);
		}
		if($fuel_expense_id){
			$this->db->where("(IFNULL(".$this->db->dbprefix('used_fuels').".fuel_expense_id,0) = 0 OR ".$this->db->dbprefix('used_fuels').".fuel_expense_id = ".$fuel_expense_id.")");
		}else{
			$this->db->where("IFNULL(".$this->db->dbprefix('used_fuels').".fuel_expense_id,0)",0);
		}

		$this->db->select("
							used_fuels.driver_id,
							used_fuels.truck_id,
							con_drivers.full_name_kh,
							con_drivers.full_name,
							con_trucks.code as truck_code,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".in_range_litre,0)) as in_range_litre,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".out_range_litre,0)) as out_range_litre,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".pump_litre,0)) as pump_litre,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".waiting_litre,0)) as waiting_litre,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".moving_litre,0)) as moving_litre,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".mission_litre,0)) as mission_litre,
							SUM(IFNULL(".$this->db->dbprefix('used_fuels').".fuel_litre,0)) as fuel_litre,
							GROUP_CONCAT(".$this->db->dbprefix('used_fuels').".id) as used_fuel_ids
						");
		$this->db->join("con_drivers","con_drivers.id = used_fuels.driver_id","inner");
		$this->db->join("con_trucks","con_trucks.id = used_fuels.truck_id","inner");
		$this->db->group_by("used_fuels.driver_id");
		$this->db->order_by("con_trucks.code");
		$q = $this->db->get("used_fuels");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function getFuelExpenseByID($id = false){
		$q = $this->db->get_where("con_fuel_expenses",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getFuelExpenseItems($fuel_expense_id = false){
		$this->db->select("con_fuel_expense_items.*,con_drivers.full_name_kh,con_drivers.full_name,con_trucks.code as truck_code");
		$this->db->join("con_drivers","con_drivers.id = con_fuel_expense_items.driver_id","left");
		$this->db->join("con_trucks","con_trucks.id = con_fuel_expense_items.truck_id","left");
		$this->db->order_by("con_fuel_expense_items.subtotal","desc");
		$q = $this->db->get_where("con_fuel_expense_items",array("fuel_expense_id"=>$fuel_expense_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function addFuelExpense($data = false, $items = false, $accTrans = false){
		if($this->db->insert("con_fuel_expenses",$data)){
			$fuel_expense_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['fuel_expense_id'] = $fuel_expense_id;
					$this->db->insert("con_fuel_expense_items",$item);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['tran_no'] = $fuel_expense_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			$this->sysFuelExpense($fuel_expense_id);
			return true;
		}
		return false;
	}
	public function updateFuelExpense($id = false, $data = false, $items = false, $accTrans = false){
		if($id && $this->db->update("con_fuel_expenses",$data,array("id"=>$id))){
			$this->db->delete("con_fuel_expense_items",array("fuel_expense_id"=>$id));
			$this->site->deleteAccTran("Fuel Expense",$id);
			if($items){
				$this->db->insert_batch("con_fuel_expense_items",$items);
			}
			if($accTrans){
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->sysFuelExpense($id);
			$this->sysFuelExpensePayment($id);
			return true;
		}
		return false;
	}
	
	public function deleteFuelExpense($id = false){
		$payments = $this->getPaymentByFuelExpense($id);
		if($id && $this->db->delete("con_fuel_expenses",array("id"=>$id))){
			$this->db->delete("con_fuel_expense_items",array("fuel_expense_id"=>$id));
			$this->site->deleteAccTran("Fuel Expense",$id);
			$this->sysFuelExpense($id);
			if($payments){
				foreach($payments as $payment){
					$this->deleteFuelExpensePayment($payment->id);
				}
			}
			return true;
		}
		return false;
	}
	
	public function sysFuelExpense($fuel_expense_id = false){
		$this->db->update("used_fuels",array("fuel_expense_id"=>0),array("fuel_expense_id"=>$fuel_expense_id));
		$q = $this->db->select("GROUP_CONCAT(used_fuel_ids) as used_fuel_ids")->get_where("con_fuel_expense_items",array("fuel_expense_id"=>$fuel_expense_id));
		if ($q->num_rows() > 0) {
			$used_fuel_ids = $q->row()->used_fuel_ids;
			if($used_fuel_ids){
				$this->db->query("UPDATE ".$this->db->dbprefix('used_fuels')." SET fuel_expense_id=".$fuel_expense_id." WHERE id IN(".$used_fuel_ids.")");
			}
        }
	}
	
	public function getPaymentByID($id = false){
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as cash_account");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$q = $this->db->get_where("payments",array("payments.id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getPaymentByFuelExpense($fuel_expense_id = false){
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->where("transaction_id",$fuel_expense_id);
		$this->db->where("transaction","Fuel Expense");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}

	
	public function addFuelExpensePayment($payment = false, $accTrans = false){
		if($this->db->insert("payments",$payment)){
			$payment_id = $this->db->insert_id();
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran["tran_no"] = $payment_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			$this->sysFuelExpensePayment($payment['transaction_id']);
			return true;
		}
		return false;
	}
	
	public function updateFuelExpensePayment($id = false, $payment = false, $accTrans = false){
		if($id && $this->db->update("payments",$payment,array("id"=>$id))){
			$this->site->deleteAccTran("Payment",$id);
			if($accTrans){
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->sysFuelExpensePayment($payment['transaction_id']);
			return true;
		}
		return false;
	}
	
	public function deleteFuelExpensePayment($id = false){
		$payment = $this->getPaymentByID($id);
		if($id && $this->db->delete("payments",array("id"=>$id))){
			$this->site->deleteAccTran("Payment",$id);
			$this->sysFuelExpensePayment($payment->transaction_id);
			return true;
		}
		return false;
	}
	
	public function sysFuelExpensePayment($fuel_expense_id = false){
		$expense = $this->getFuelExpenseByID($fuel_expense_id);
		$data["payment_status"] = "pending";
		if($expense){
			$paid = 0;
			$balance = $expense->grand_total;
			$payment_status = "pending";
			$payments = $this->getPaymentByFuelExpense($fuel_expense_id);
			if($payments){
				foreach($payments as $payment){
					$paid += $payment->amount;
				}
				$balance = $this->bpas->formatDecimal($expense->grand_total - $paid);
				if($balance == 0){
					$payment_status = "paid";
				}else if($paid != 0){
					$payment_status = "partial";
				}
			}
			$data = array("paid"=>$paid,"balance"=>$balance,"payment_status"=>$payment_status);
			$this->db->update("con_fuel_expenses",$data,array("id"=>$fuel_expense_id));
		}
	}
	
	public function getOfficerByBiller($biller_id = false){
		$q = $this->db->get_where("con_officers",array("biller_id"=>$biller_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	

	
	public function getConDeliveries($biller_id = false, $project_id = false, $from_date = false, $to_date = false, $pump = false){
		if($biller_id){
			$this->db->where("con_deliveries.biller_id",$biller_id);
		}
		if($project_id){
			$this->db->where("con_deliveries.project_id",$project_id);
		}
		if($from_date){
			$this->db->where("con_deliveries.date >=",$from_date);
		}
		if($to_date){
			$this->db->where("con_deliveries.date <=",$to_date);
		}
		if($pump){
			$this->db->where("con_deliveries.pump_id >",0);
		}
		$this->db->select("con_deliveries.id,
							date(".$this->db->dbprefix('con_deliveries').".date) as date,
							con_deliveries.quantity,
							con_deliveries.truck_id,
							con_deliveries.pump_driver_id,
							con_deliveries.pump_driver_name,
							con_deliveries.driver_assistant,
							con_deliveries.driver_id,
							con_deliveries.driver_name,
							con_deliveries.departure_time");
		$q = $this->db->get("con_deliveries");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function getArrayAbsents($biller_id = false, $project_id = false, $from_date = false, $to_date = false){
		if($biller_id){
			$this->db->where("con_absents.biller_id",$biller_id);
		}
		if($project_id){
			$this->db->where("con_absents.project_id",$project_id);
		}
		if($from_date){
			$this->db->where("con_absent_items.absent_date >=",$from_date);
		}
		if($to_date){
			$this->db->where("con_absent_items.absent_date <=",$to_date);
		}
		$this->db->select("con_absents.type,con_absent_items.absent_date,con_absent_items.officer_id,con_absent_items.driver_id");
		$this->db->join("con_absents","con_absents.id = con_absent_items.absent_id","inner");
		$q = $this->db->get("con_absent_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				if($row->type=="driver"){
					$data[$row->type][$row->driver_id][$row->absent_date] = $row;
				}else{
					$data[$row->type][$row->officer_id][$row->absent_date] = $row;
				}
                
            }
            return $data;
        }
		return false;
	}
	
	public function getCommissionDeliveries($type = false, $commission_id = false){
		$this->db->where("con_commision_deliveries.commission_id !=", $commission_id);
		$this->db->where("con_commissions.commission_type",$type);
		$this->db->select("con_commision_deliveries.*");
		$this->db->join("con_commissions","con_commissions.id = con_commision_deliveries.commission_id","inner");
		$q = $this->db->get("con_commision_deliveries");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				if($type=="officer"){
					$data[$row->delivery_id][$row->officer_id] = $row;
				}else{
					$data[$row->delivery_id][$row->driver_id] = $row;
				}
               
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getArrayDrivers(){
		$q = $this->db->get("con_drivers");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getArrayTrucks(){
		$q = $this->db->get("con_trucks");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addCommission($data = false, $items = false, $commission_deliveries = false, $accTrans = false){
		if($this->db->insert("con_commissions",$data)){
			$commission_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['commission_id'] = $commission_id;
					$this->db->insert("con_commission_items",$item);
				}
			}
			if($commission_deliveries){
				$con_deliveries = false;
				foreach($commission_deliveries as $commission_delivery){
					$commission_delivery['commission_id'] = $commission_id;
					$con_deliveries[] = $commission_delivery;
				}
				if($con_deliveries){
					$this->db->insert_batch('con_commision_deliveries',$con_deliveries);
				}
			}
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['tran_no'] = $commission_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			return true;
		}
		return false;
	}

	
	public function updateCommission($id = false, $data = false, $items = false, $commission_deliveries = false, $accTrans = false){
		if($id && $this->db->update("con_commissions",$data,array("id"=>$id))){
			$this->db->delete("con_commission_items",array("commission_id"=>$id));
			$this->db->delete("con_commision_deliveries",array("commission_id"=>$id));
			$this->site->deleteAccTran("CCommission",$id);
			if($items){
				$this->db->insert_batch("con_commission_items",$items);
			}
			if($commission_deliveries){
				$this->db->insert_batch("con_commision_deliveries",$commission_deliveries);
			}
			if($accTrans){
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->sysCommissionPayment($id);
			return true;
		}
		return false;
	}
	
	public function deleteCommission($id = false){
		$payments = $this->getPaymentByCommission($id);
		if($id && $this->db->delete("con_commissions",array("id"=>$id))){
			$this->db->delete("con_commission_items",array("commission_id"=>$id));
			$this->db->delete("con_commision_deliveries",array("commission_id"=>$id));
			$this->site->deleteAccTran("CCommission",$id);
			if($payments){
				foreach($payments as $payment){
					$this->deleteCommissionPayment($payment->id);
				}
			}
			return true;
		}
		return false;
	}
	

	public function getCommissionByID($id = false){
		$q = $this->db->get_where("con_commissions",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getCommissionItems($commission_id = false){
		$this->db->select("con_commission_items.*, 
							CONCAT(".$this->db->dbprefix('con_officers').".full_name_kh,' - ',".$this->db->dbprefix('con_officers').".full_name) as officer_name,
							CONCAT(".$this->db->dbprefix('con_drivers').".full_name_kh,' - ',".$this->db->dbprefix('con_drivers').".full_name) as driver_name,
						");
		$this->db->join("con_officers","con_officers.id = con_commission_items.officer_id","left");
		$this->db->join("con_drivers","con_drivers.id = con_commission_items.driver_id","left");
		$q = $this->db->get_where("con_commission_items",array("commission_id"=>$commission_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	
	
	public function getPaymentByCommission($commission_id = false){
		$this->db->select("payments.*, IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by");
		$this->db->where("transaction_id",$commission_id);
		$this->db->where("transaction","CCommission");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}

	public function sysCommissionPayment($commission_id = false){
		$commission = $this->getCommissionByID($commission_id);
		$data["payment_status"] = "pending";
		if($commission){
			$paid = 0;
			$balance = $commission->grand_total;
			$payment_status = "pending";
			$payments = $this->getPaymentByCommission($commission_id);
			if($payments){
				foreach($payments as $payment){
					$paid += $payment->amount;
				}
				$balance = $this->bpas->formatDecimal($commission->grand_total - $paid);
				if($balance == 0){
					$payment_status = "paid";
				}else if($paid != 0){
					$payment_status = "partial";
				}
			}
			$data = array("paid"=>$paid,"balance"=>$balance,"payment_status"=>$payment_status);
			$this->db->update("con_commissions",$data,array("id"=>$commission_id));
		}
	}
	
	public function addCommissionPayment($payment = false, $accTrans = false){
		if($this->db->insert("payments",$payment)){
			$payment_id = $this->db->insert_id();
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran["tran_no"] = $payment_id;
					$this->db->insert("gl_trans",$accTran);
				}
			}
			$this->sysCommissionPayment($payment['transaction_id']);
			return true;
		}
		return false;
	}
	
	public function updateCommissionPayment($id = false, $payment = false, $accTrans = false){
		if($id && $this->db->update("payments",$payment,array("id"=>$id))){
			$this->site->deleteAccTran("Payment",$id);
			if($accTrans){
				$this->db->insert_batch("gl_trans",$accTrans);
			}
			$this->sysCommissionPayment($payment['transaction_id']);
			return true;
		}
		return false;
	}
	
	public function deleteCommissionPayment($id = false){
		$payment = $this->getPaymentByID($id);
		if($id && $this->db->delete("payments",array("id"=>$id))){
			$this->site->deleteAccTran("Payment",$id);
			$this->sysCommissionPayment($payment->transaction_id);
			return true;
		}
		return false;
	}
	
	public function getAbsentByID($id = false){
		$q = $this->db->get_where("con_absents",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getAbsentItems($absent_id = false){
		if($absent_id){
			$this->db->where("absent_id",$absent_id);
		}
		$this->db->select("con_absent_items.*,
							IFNULL(".$this->db->dbprefix('con_officers').".full_name_kh,".$this->db->dbprefix('con_drivers').".full_name_kh) as full_name_kh,
							IFNULL(".$this->db->dbprefix('con_officers').".full_name,".$this->db->dbprefix('con_drivers').".full_name) as full_name,
							IFNULL(".$this->db->dbprefix('con_officers').".position,'Driver') as position
						");
		$this->db->join("con_officers","con_officers.id = con_absent_items.officer_id","left");
		$this->db->join("con_drivers","con_drivers.id = con_absent_items.driver_id","left");
		$q = $this->db->get("con_absent_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function addAbsent($data = false, $items = false){
		if($data && $this->db->insert("con_absents",$data)){
			$absent_id = $this->db->insert_id();
			foreach($items as $item){
				$item["absent_id"] = $absent_id;
				$this->db->insert("con_absent_items",$item);
			}
			return true;
		}
		return false;
	}
	
	public function updateAbsent($id = false, $data = false, $items = false){
		if($id && $this->db->update("con_absents",$data,array("id"=>$id))){
			$this->db->delete("con_absent_items",array("absent_id"=>$id));
			if($items){
				$this->db->insert_batch("con_absent_items",$items);
			}
			return true;
		}
		return false;
	}
	public function deleteAbsent($id = false){
		if($id && $this->db->delete("con_absents",array("id"=>$id))){
			$this->db->delete("con_absent_items",array("absent_id"=>$id));
			return true;
		}
		return false;
	}
	
	
	public function getCustomerIndexCode() {
        $q = $this->db->get_where('companies', array('group_name' => "customer"));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->code] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getStrenthIndexCode() {
        $q = $this->db->get_where('products', array('stregth' => 1));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->code] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAddressIndexName() {
        $q = $this->db->get('addresses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->company_id][$row->name] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getTruckIndexCode() {
        $q = $this->db->get('con_trucks');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->code] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getTruckIndexPlate() {
        $q = $this->db->get('con_trucks');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->plate] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getSlumpIndexName() {
        $q = $this->db->get('con_slumps');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->name] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getCastingIndexName() {
        $q = $this->db->get('con_casting_types');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->name] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getDriverIndexID() {
        $q = $this->db->get('con_drivers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getProductAccIndexProduct(){
        $q = $this->db->get('acc_product');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->product_id] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	function getBomProductIndexProduct(){
		$q = $this->db->query('SELECT
							'.$this->db->dbprefix("bom_products").'.standard_product_id,
							'.$this->db->dbprefix("bom_products").'.product_id,
							'.$this->db->dbprefix("bom_products").'.unit_id,
							'.$this->db->dbprefix("bom_products").'.quantity * unit_qty AS quantity,
							'.$this->db->dbprefix("product_units").'.unit_qty,
							'.$this->db->dbprefix("units").'.`code`,
							'.$this->db->dbprefix("products").'.cost,
							'.$this->db->dbprefix("products").'.code as product_code,
							'.$this->db->dbprefix("products").'.type as product_type,
							'.$this->db->dbprefix("products").'.name as product_name,
							'.$this->db->dbprefix("products").'.accounting_method
						FROM
							'.$this->db->dbprefix("bom_products").'
						INNER JOIN '.$this->db->dbprefix("product_units").' ON '.$this->db->dbprefix("product_units").'.product_id = '.$this->db->dbprefix("bom_products").'.product_id
						AND '.$this->db->dbprefix("product_units").'.unit_id = '.$this->db->dbprefix("bom_products").'.unit_id
						INNER JOIN '.$this->db->dbprefix("units").' ON '.$this->db->dbprefix("units").'.id = '.$this->db->dbprefix("product_units").'.unit_id
						INNER JOIN '.$this->db->dbprefix("products").' ON '.$this->db->dbprefix("products").'.id = '.$this->db->dbprefix("bom_products").'.product_id');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[$row->standard_product_id][] = $row;
			}
			return $data;
		}
		return false;
	}

		
	public function importDelivery($deliveries = false, $stockmoves = false, $used_fuels = false, $accTrans = false){
		if($deliveries){
			$tStocmkoves = false;
			$tUsedfuels = false;
			$tAccTrans = false;
			foreach($deliveries as $index => $delivery){
				if($this->db->insert("con_deliveries",$delivery)){
					$delivery_id = $this->db->insert_id();
					if($stockmoves[$index]){
						foreach($stockmoves[$index] as $stockmove){
							$stockmove["transaction_id"] = $delivery_id;
							$tStocmkoves[] = $stockmove;
						}
					}
					if($used_fuels[$index]){
						foreach($used_fuels[$index] as $used_fuel){
							$used_fuel["delivery_id"] = $delivery_id;
							$tUsedfuels[] = $used_fuel;
						}
					}
					if($accTrans[$index]){
						foreach($accTrans[$index] as $accTran){
							$accTran["transaction_id"] = $delivery_id;
							$tAccTrans[] = $accTran;
						}
					}
				}
			}
			if($tStocmkoves){
				$this->db->insert_batch("stock_movement",$tStocmkoves);
			}
			if($tUsedfuels){
				$this->db->insert_batch("used_fuels",$tUsedfuels);
			}
			if($tAccTrans){
				$this->db->insert_batch("gl_trans",$tAccTrans);
			}
			return true;
		}
		return false;
	}
	
	
	public function getGroups(){
		$q = $this->db->get_where('custom_field',array('code' =>'groups'));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getGroupByID($id = false){
		$q = $this->db->get_where('custom_field',array('code' =>'groups','id'=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addGroup($data = false){
		if($data && $this->db->insert('con_groups',$data)){
			return true;
		}
		return false;
	}
	public function updateGroup($id = false, $data = false){
		if($id && $data && $this->db->update('con_groups',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteGroup($id = false){
		if($id && $this->db->delete('con_groups',array('id'=>$id))){
			return true;
		}
		return false;
	}
	
	public function getOperators(){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('hr_employees.biller_id', $this->session->userdata('biller_id'));
		}
		$this->db->where(array(
			"hr_employees.module_type" 	=> 'concrete',
			"hr_employees.operator" 	=> 1
		));
		$q = $this->db->get("hr_employees");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSOQuantity($biller_id = false, $customer_id = false, $location_id = false, $date = false, $product_id = false){
		$this->db->where_in("sales_order.order_status",array("approved","partial"));
		if($biller_id){
			$this->db->where("sales_order.biller_id",$biller_id);
		}
		if($customer_id){
			$this->db->where("sales_order.customer_id",$customer_id);
		}
		if($location_id){
			$this->db->where("sales_order.location_id",$location_id);
		}
		if($date){
			$this->db->where("sales_order.date <=",$date);
		}
		if($product_id){
			$this->db->where("sale_order_items.product_id",$product_id);
		}
		$this->db->select("SUM(".$this->db->dbprefix('sale_order_items').".quantity - IFNULL(".$this->db->dbprefix('sale_order_items').".concrete_qty, 0 )) AS quantity");
		$this->db->join("sale_order_items","sale_order_items.sale_order_id = sales_order.id","LEFT");
		$q = $this->db->get("sales_order");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSaleOrderItems($biller_id = false, $customer_id = false, $location_id = false, $date = false, $product_id = false){
		$this->db->where_in("sales_order.order_status",array("approved","partial"));
		if($biller_id){
			$this->db->where("sales_order.biller_id",$biller_id);
		}
		if($customer_id){
			$this->db->where("sales_order.customer_id",$customer_id);
		}
		if($location_id){
			$this->db->where("sales_order.location_id",$location_id);
		}
		if($date){
			$this->db->where("sales_order.date <=",$date);
		}
		if($product_id){
			$this->db->where("sale_order_items.product_id",$product_id);
		}
		$this->db->select("
			sale_order_items.sale_order_id,
			sale_order_items.id,
			IFNULL(".$this->db->dbprefix('sale_order_items').".concrete_qty,0) as concrete_qty,
			IFNULL(".$this->db->dbprefix('sale_order_items').".quantity,0) - IFNULL(".$this->db->dbprefix('sale_order_items').".concrete_qty,0) as balance_qty,
			sale_order_items.concrete_ids
		");
		$this->db->join("sale_order_items","sale_order_items.sale_order_id = sales_order.id","LEFT");
		$q = $this->db->get("sales_order");
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function synceSaleOrder($biller_id = false, $customer_id = false, $location_id = false, $date = false, $product_id = false, $quantity = 0, $delivery_id = false){
		$sale_order_items = $this->getSaleOrderItems($biller_id,$customer_id,$location_id,$date,$product_id);
		if($sale_order_items){
			foreach($sale_order_items as $sale_order_item){
				if($quantity > 0){
					if($quantity > $sale_order_item->balance_qty){
						$update_qty = $sale_order_item->balance_qty;
						$quantity = $quantity - $sale_order_item->balance_qty;
					}else{
						$update_qty = $quantity;
						$quantity = 0;
					}
					
					if($sale_order_item->concrete_ids){
						$concrete_ids = $sale_order_item->concrete_ids.",".$delivery_id;
					}else{
						$concrete_ids = $delivery_id;
					}
					
					$update_qty = $this->bpas->formatDecimal($update_qty + $sale_order_item->concrete_qty);
					if($this->db->update("sale_order_items",array("concrete_qty"=>$update_qty,"concrete_ids"=>$concrete_ids),array("id"=>$sale_order_item->id))){
						$this->synceSaleOrderStatus($sale_order_item->sale_order_id);
					}
				}
			}
		}
	}
	
	public function synceSaleOrderStatus($sale_order_id = false){
		$this->db->query("UPDATE ".$this->db->dbprefix("sales_order")."
							INNER JOIN ( SELECT SUM(IFNULL(quantity,0)) as quantity, SUM(IFNULL(concrete_qty,0)) as concrete_qty, sale_order_id FROM ".$this->db->dbprefix("sale_order_items")." WHERE sale_order_id = ".$sale_order_id." ) AS sale_order_items ON sale_order_items.sale_order_id = ".$this->db->dbprefix("sales_order").".id 
							SET ".$this->db->dbprefix("sales_order").".`status` = IF(sale_order_items.concrete_qty = 0, 'approved', IF(sale_order_items.concrete_qty >= sale_order_items.quantity,'completed','partial')) 
							WHERE
								".$this->db->dbprefix("sales_order").".id = ".$sale_order_id."
						");
	}
	
	public function removeConcreateQty($delivery_id = false, $quantity = false){
		$so_items = $this->getSOItemsByDelivery($delivery_id);
		if($so_items){
			foreach($so_items as $so_item){
				if($quantity > 0){
					if($so_item->concrete_qty > $quantity){
						$concrete_qty = $so_item->concrete_qty - $quantity;
						$quantity = 0;
					}else{
						$concrete_qty = 0;
						$quantity = $quantity - $so_item->concrete_qty;
					}
					$concrete_ids = str_replace(",".$delivery_id,"",$so_item->concrete_ids);
					$concrete_ids = str_replace($delivery_id,"",$concrete_ids);
					$this->db->update("sale_order_items",array("concrete_qty"=>$concrete_qty,"concrete_ids"=>$concrete_ids),array("id"=>$so_item->id));
					$this->synceSaleOrderStatus($so_item->sale_order_id);
				}
			}
		}
	}
	
	public function getSOItemsByDelivery($delivery_id = false){
		$this->db->select("sale_order_items.id,sale_order_items.sale_order_id,sale_order_items.quantity,sale_order_items.concrete_qty,sale_order_items.concrete_ids");
		$this->db->where("sale_order_items.concrete_ids LIKE '%".$delivery_id."%'");
		$this->db->order_by("sale_order_items.id","desc");
		$q = $this->db->get("sale_order_items");
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$concrete_ids = explode(",",$row->concrete_ids);
				if(in_array($delivery_id, $concrete_ids)){
					$data[] = $row;
				}
            }
            return $data;
        }
        return FALSE;
	}
	public function getBillerKilometer($biller_id = false, $address_id = false){
		$this->db->where("biller_id",$biller_id);
		$this->db->where("address_id",$address_id);
		$q = $this->db->get("kilometers");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

}











