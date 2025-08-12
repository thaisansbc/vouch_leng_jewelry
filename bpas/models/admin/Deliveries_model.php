<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Deliveries_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	public function addPayment($data = [])
    {
        if ($this->db->update('deliveries', $data)) {
            // if ($this->site->getReference('do') == $data['do_reference_no']) {
            //     $this->site->updateReference('do');
            // }
            return true;
        }
        return false;
    }
	public function synDeliveries($sale_id = false, $sale_order_id = false)
	{
		if ($sale_id) {
			$q = $this->db->join('delivery_items','delivery_items.delivery_id=deliveries.id','left')->where("sale_id",$sale_id)->get("deliveries");
			$delivered_quantity = 0;
			foreach($q->result() as $delivery){
				$delivered_quantity += $delivery->quantity;
			}
			$s = $this->db->where(array("sale_id"=>$sale_id))->get("sale_items");
			$saled_quantity = 0;
			foreach($s->result() as $sale){
				$saled_quantity += ($sale->quantity + $sale->foc);
			}
			if ($delivered_quantity >= $saled_quantity) {
				$this->db->update("sales", array("delivery_status" => "completed"), array("id" => $sale_id));
			} elseif ($delivered_quantity < $saled_quantity && $delivered_quantity > 0){
				$this->db->update("sales", array("delivery_status" => "partial"), array("id" => $sale_id));
			} else {
				$this->db->update("sales", array("delivery_status" => "pending"), array("id" => $sale_id));
			}
		}
		if ($sale_order_id) {
			$q = $this->db->join('delivery_items','delivery_items.delivery_id=deliveries.id','left')->where("sale_order_id", $sale_order_id)->get("deliveries");
			$delivered_quantity = 0;
			foreach($q->result() as $delivery){
				$delivered_quantity += $delivery->quantity;
			}
			$so = $this->db->where(array("sale_order_id"=>$sale_order_id))->get("sale_order_items");
			$sale_ordered_quantity = 0;
			foreach($so->result() as $sale_order){
				$sale_ordered_quantity += ($sale_order->quantity + $sale_order->foc);
			}
			if ($delivered_quantity >= $sale_ordered_quantity){
				$this->db->update("sales_order", array("delivery_status" => "completed"), array("id" => $sale_order_id));
			} elseif ($delivered_quantity < $sale_ordered_quantity && $delivered_quantity > 0){
				$this->db->update("sales_order", array("delivery_status" => "partial"), array("id" => $sale_order_id));
			} else {
				$this->db->update("sales_order", array("delivery_status" => "pending"), array("id" => $sale_order_id));
			}
		}
	}
	
	public function addDelivery($data = array(), $items = array())
    {
        if ($this->db->insert('deliveries', $data)) {
			$delivery_id = $this->db->insert_id();
			if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
			foreach($items as $item){
				$item['delivery_id'] = $delivery_id;
				$this->db->insert('delivery_items', $item);
			}
			if($data['sale_id']){
				$this->synDeliveries($data['sale_id'], 0);
			}
			if($data['sale_order_id']){
				$this->synDeliveries(0, $data['sale_order_id']);
			}
			$this->site->sendTelegram("delivery", $delivery_id, "added");
            return true;
        }
        return false;
    } 
	
	public function getProductComboItems($pid = false, $warehouse_id = NULL)
    {
        $this->db->select('products.*, products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name,products.type as type, products.price as price, warehouses_products.quantity as quantity, combo_items.option_id')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', array('combo_items.product_id' => $pid));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function updateDelivery($delivery_id = false, $data = array(), $items = array(), $stockmoves = array())
    {
        if ($this->db->update('deliveries', $data, array('id' => $delivery_id)) && 
			$this->db->delete('delivery_items', array('delivery_id' => $delivery_id))){
			$this->site->delete_stock_movement('Delivery',$delivery_id);
			foreach($items as $item){
				$item['delivery_id'] = $delivery_id;
				$this->db->insert('delivery_items', $item);
			}
			
			$delivery = $this->getDeliveryByID($delivery_id);
			if($delivery->sale_id){
				$this->synDeliveries($delivery->sale_id,0);
			}
			if($delivery->sale_order_id){
				$this->synDeliveries(0,$delivery->sale_order_id);
			}

			$this->site->sendTelegram("delivery",$delivery_id,"updated");
            return true;
        }
        return false;
    } 
	
	public function getDeliveryItems($delivery_id = false){
		$q = $this->db->get_where('delivery_items',array('delivery_id'=>$delivery_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	

	public function updateDeliveryStatus($id = false)
	{
		if($id && $this->db->update("deliveries", array("status"=>"pending"), array("id"=>$id))){
			$delivery = $this->getDeliveryByID($id);
			$delivery_items = $this->getDeliveryItems($id);
			if($delivery_items){
				$stockmoves = false;
				$accTrans = false;
				foreach($delivery_items as $delivery_item){
					$product_detail =  $this->site->getProductByID($delivery_item->product_id);
					if($product_detail){
						
						if($product_detail->type=='bom'){
							$product_boms = $this->sales_model->getBomProductByStandProduct($product_detail->id);
							if($product_boms){
								foreach($product_boms as $product_bom){
									$product_bom_cost += ($product_bom->quantity * $product_bom->cost);
									$stockmoves[] = array(
												'transaction' => 'Delivery',
												'transaction_id' => $delivery->id,
												'product_id' => $product_bom->product_id,
												'product_code' => $product_bom->product_code,
												'product_type'    => $product_bom->product_type,
												'option_id' => '',
												'quantity' => ($delivery_item->quantity * $product_bom->quantity) * -1,
												'unit_quantity' => $product_bom->unit_qty,
												'unit_code' => $product_bom->code,
												'unit_id' => $product_bom->unit_id,
												'warehouse_id' => $delivery->warehouse_id,
												'date' => $delivery->date,
												'real_unit_cost' => $product_bom->cost,
												'serial_no' => '',
												'reference_no' => $delivery->do_reference_no,
												'user_id' => $this->session->userdata('user_id'),
												'expiry' => ''
											);

											
									//=======accounting=========//
										if($this->Settings->accounting == 1){	
											$productAcc = $this->site->getProductAccByProductId($product_bom->product_id);
											$accTrans[] = array(
												'transaction' => 'Delivery',
												'transaction_id' => $delivery->id,
												'transaction_date' => $delivery->date,
												'reference' => $delivery->do_reference_no,
												'account' => $productAcc->stock_acc,
												'amount' => -($product_bom->cost * ($delivery_item->quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($delivery_item->quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $delivery->note,
												'biller_id' => $delivery->biller_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $delivery->customer_id,
											);
											
											$accTrans[] = array(
												'transaction' => 'Delivery',
												'transaction_id' => $delivery->id,
												'transaction_date' => $delivery->date,
												'reference' => $delivery->do_reference_no,
												'account' => $productAcc->cost_acc,
												'amount' => ($product_bom->cost * ($delivery_item->quantity * $product_bom->quantity)),
												'narrative' => 'Product Code: '.$product_bom->product_code.'#'.'Qty: '.($delivery_item->quantity * $product_bom->quantity).'#'.'Cost: '.$product_bom->cost,
												'description' => $delivery->note,
												'biller_id' => $delivery->biller_id,
												'user_id' => $this->session->userdata('user_id'),
												'customer_id' => $delivery->customer_id,
											);
										}
									//============end accounting=======//		
								}
							}
						}else{
							$unit = $this->site->getProductUnit($product_detail->id, $delivery_item->product_unit_id);
							$stockmoves[] = array(
										'transaction' => 'Delivery',
										'transaction_id' => $delivery->id,
										'product_id' => $product_detail->id,
										'product_code' => $product_detail->code,
										'product_type' => $product_detail->type,
										'option_id' => $delivery_item->option_id,
										'quantity' => $delivery_item->quantity * (-1),
										'unit_quantity' => $unit->unit_qty,
										'unit_code' => $unit->code,
										'unit_id' => $delivery_item->product_unit_id,
										'warehouse_id' => $delivery->warehouse_id,
										'date' => $delivery->date,
										'real_unit_cost' => $product_detail->cost,
										'serial_no' => $delivery_item->serial_no,
										'reference_no' => $delivery->do_reference_no,
										'user_id' => $this->session->userdata('user_id'),
										'expiry' => $delivery_item->expiry
									);
							//========accounting=========//
								if($this->Settings->accounting == 1){	
									$productAcc = $this->site->getProductAccByProductId($product_detail->id);
									$accTrans[] = array(
										'transaction' => 'Delivery',
										'transaction_id' => $delivery->id,
										'transaction_date' => $delivery->date,
										'reference' => $delivery->do_reference_no,
										'account' => $productAcc->stock_acc,
										'amount' => -($product_detail->cost * $delivery_item->quantity),
										'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$delivery_item->quantity.'#'.'Cost: '.$product_detail->cost,
										'description' => $delivery->note,
										'biller_id' => $delivery->biller_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $delivery->customer_id,
									);
									$accTrans[] = array(
										'transaction' => 'Delivery',
										'transaction_id' => $delivery->id,
										'transaction_date' => $delivery->date,
										'reference' => $delivery->do_reference_no,
										'account' => $productAcc->cost_acc,
										'amount' => ($product_detail->cost * $delivery_item->quantity),
										'narrative' => 'Product Code: '.$product_detail->code.'#'.'Qty: '.$delivery_item->quantity.'#'.'Cost: '.$product_detail->cost,
										'description' => $delivery->note,
										'biller_id' => $delivery->biller_id,
										'user_id' => $this->session->userdata('user_id'),
										'customer_id' => $delivery->customer_id,
									);
								}
							//============end accounting=======//
						}
					}	
				}
				
				if($stockmoves){
					$this->db->insert_batch('stockmoves',$stockmoves);
				}
				// if($accTrans){
				// 	$this->db->insert_batch('acc_tran',$accTrans);
				// }
			}
				
						
			return true;
		}
		return false;
	}
	
	public function deleteDelivery($id = false)
    {
		if($id && $id > 0){
			$delivery = $this->getDeliveryByID($id);
			if ($this->db->delete('deliveries', array('id' => $id))) {
				$this->db->delete('delivery_items', array('delivery_id' => $id));
				$this->site->delete_stock_movement('Delivery', $id);
				$this->site->deleteAccTran('Delivery', $id);
				$this->synDeliveries($delivery->sale_id, $delivery->sale_order_id);
				$this->site->sendTelegram("delivery", $id, "deleted",$delivery);
				return true;
			}
		}
        return FALSE;
    }
	
	public function getDeliveryByID($id = false)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getAllDeliveryItems($id = false)
    {
        $this->db->select('
							delivery_items.*, 
							tax_rates.code as tax_code, 
							tax_rates.name as tax_name, 
							tax_rates.rate as tax_rate, 
							delivery_items.net_unit_price, 
							delivery_items.unit_price, 
							delivery_items.real_unit_price as real_unit_price, 
							products.unit, 
							products.image, 
							products.details as details, 
							product_variants.name as variant, 
							deliveries.sale_id, 
							deliveries.sale_order_id,
							units.name as unit_name')
            ->join('products', 'products.id=delivery_items.product_id', 'left')
			->join('deliveries', 'deliveries.id=delivery_items.delivery_id', 'left')
            ->join('product_variants', 'product_variants.id=delivery_items.option_id', 'left')
			->join('tax_rates', 'tax_rates.id=delivery_items.tax_rate_id', 'left')
			->join('units','units.id = delivery_items.product_unit_id','left')
			->where('delivery_items.unit_quantity <>',0)
            ->group_by('delivery_items.id')
            ->order_by('id', 'asc');
			
        $q = $this->db->get_where('delivery_items', array('delivery_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllGroupsDeliveryItems($groups_delivery = false)
    {
        $this->db->select('
							delivery_items.delivery_id,
							delivery_items.product_id,
							delivery_items.parent_id,
							delivery_items.product_code,
							delivery_items.product_name,
							delivery_items.product_type,
							delivery_items.option_id,
							delivery_items.net_unit_price,
							delivery_items.unit_price,
							delivery_items.real_quantity,
							delivery_items.warehouse_id,
							delivery_items.item_tax,
							delivery_items.tax_rate_id,
							delivery_items.tax,
							delivery_items.item_discount,
							delivery_items.serial_no,
							delivery_items.real_unit_price,
							delivery_items.product_unit_id,
							delivery_items.product_unit_code,
							delivery_items.expiry,
							SUM('.$this->db->dbprefix('delivery_items').'.quantity) as quantity,
							('.$this->db->dbprefix('delivery_items').'.discount) as discount,
							SUM('.$this->db->dbprefix('delivery_items').'.subtotal) as subtotal,
							SUM('.$this->db->dbprefix('delivery_items').'.unit_quantity) as unit_quantity,
							delivery_items.height,
							delivery_items.width,
							delivery_items.square, 
							delivery_items.square_qty, 
							tax_rates.code as tax_code, 
							tax_rates.name as tax_name, 
							tax_rates.rate as tax_rate, 
							products.unit, 
							products.image, 
							products.details as details, 
							product_variants.name as variant, 
							deliveries.sale_id, 
							deliveries.sale_order_id')
            ->join('products', 'products.id=delivery_items.product_id', 'left')
			->join('deliveries', 'deliveries.id=delivery_items.delivery_id', 'left')
            ->join('product_variants', 'product_variants.id=delivery_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=delivery_items.tax_rate_id', 'left')
            ->group_by('delivery_items.product_id, delivery_items.unit_price,delivery_items.expiry, delivery_items.discount')
            ->order_by('deliveries.id', 'asc');
			
		if($groups_delivery){
			$this->db->where_in("delivery_id", $groups_delivery);
		}
        $q = $this->db->get('delivery_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllInvoiceItemsWithDeliveries($sale_id = false)
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
			
        if ($sale_id && !isset($return_id)) {
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
	
	public function getAllSOItemsWithDeliveries($sale_order_id = false)
    {
        $this->db->select('sale_order_items.*,
							sale_order_items.id as sale_order_item_id,
							sale_order_items.tax_rate_id as tax_rate_id,
							sale_order_items.tax as tax,
							SUM('.$this->db->dbprefix('sale_order_items').'.quantity) as quantity,
							IFNULL(SUM('.$this->db->dbprefix('sale_order_items').'.foc),0) as foc,
							('.$this->db->dbprefix('sale_order_items').'.discount) as discount,
							SUM('.$this->db->dbprefix('sale_order_items').'.subtotal) as subtotal,
							SUM('.$this->db->dbprefix('sale_order_items').'.unit_quantity) as unit_quantity,
							deliveries.delivered_quantity
						')
            ->join('(SELECT
						'.$this->db->dbprefix('deliveries').'.sale_order_id AS sale_order_id,
						'.$this->db->dbprefix('delivery_items').'.sale_order_item_id,
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
						'.$this->db->dbprefix('deliveries').'.sale_order_id,
						'.$this->db->dbprefix('delivery_items').'.sale_order_item_id
					) as deliveries',
					'
						deliveries.sale_order_id = sale_order_items.sale_order_id AND 
						deliveries.sale_order_item_id = sale_order_items.id
					', 'left')
            ->group_by('sale_order_items.sale_order_id, sale_order_items.id')
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
}