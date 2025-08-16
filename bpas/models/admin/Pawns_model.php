<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pawns_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
	public function getProductNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id",$allow_category);
		}
		//$this->db->where('products.inactive !=',1);
        $this->db->where("(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getProductSerialDetailsByProductId($product_id = false, $warehouse_id = false, $serial = false)
	{		
		if($warehouse_id){
			$this->db->where("warehouse_id", $warehouse_id);
		}
		if($serial){
			$this->db->where("(serial='".$serial."' OR inactive='1')");
		}else{
			$this->db->where("inactive", 1);
		}
		$products_detail = $this->db->where("product_id",$product_id)->get("product_serials")->result();
		return $products_detail;
	}
	
	public function getPawnItemSerial($product_id=false, $pawn_id = false, $serial_no = false)
	{
		if($product_id){
			$this->db->where("product_id", $product_id);
		}
		if($pawn_id){
			$this->db->where("pawn_id", $pawn_id);
		}
		if($serial_no){
			$this->db->where("serial_no", $serial_no);
		}
		$q = $this->db->get('pawn_items');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getProductByCode($code = false)
    {
        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getProductSerial($serial=false, $product_id = false, $warehouse_id = false, $pawn_id = false)
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
		if($pawn_id){
			$this->db->where("pawn_id !=", $pawn_id);
		}
		
		$q = $this->db->get('product_serials');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;	
	}
	
	public function getPaymentTermsByDueDay($id = NULL)
	{
        $q = $this->db->where('due_day', $id)->get('payment_terms');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function getPawnByID($id = false){
		$q = $this->db->get_where('pawns',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getAllPawnItems($pawn_id = false){
		$q = $this->db->get_where('pawn_items',array('pawn_id'=>$pawn_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getPawnItemWIthPurchase($pawn_id = false){
		$q = $this->db->query('SELECT
									bpas_pawn_items.*, 
									bpas_pawns.customer,
									bpas_pawns.reference_no,
									bpas_pawns.date,
									bpas_pawn_purchase_items.quantity AS purchase_qty,
									bpas_pawn_return_items.quantity AS return_qty,
									bpas_pawn_purchase_items.purchase_amount AS purchase_amount,
									bpas_pawn_return_items.return_amount AS return_amount
								FROM
									`bpas_pawn_items`
								INNER JOIN bpas_pawns ON bpas_pawns.id = bpas_pawn_items.pawn_id
								LEFT JOIN (
									SELECT
										bpas_pawn_purchase_items.pawn_id,
										bpas_pawn_purchase_items.product_id,
										bpas_pawn_purchase_items.product_unit_id,
										bpas_pawn_purchase_items.serial_no,
										bpas_pawn_purchase_items.expiry,
										bpas_pawn_purchase_items.pawn_price,
										bpas_pawn_purchase_items.pawn_rate,
										sum(
											bpas_pawn_purchase_items.quantity
										) AS quantity,
										sum(
											bpas_pawn_purchase_items.price * bpas_pawn_purchase_items.quantity
										) AS purchase_amount
									FROM
										bpas_pawn_purchase_items
									GROUP BY
										pawn_id,
										product_id,
										product_unit_id,
										serial_no,
										expiry,
										pawn_price,
										pawn_rate
								) AS bpas_pawn_purchase_items ON bpas_pawn_items.pawn_id = bpas_pawn_purchase_items.pawn_id
								AND bpas_pawn_items.product_id = bpas_pawn_purchase_items.product_id
								AND bpas_pawn_items.product_unit_id = bpas_pawn_purchase_items.product_unit_id
								AND bpas_pawn_items.serial_no = bpas_pawn_purchase_items.serial_no
								AND bpas_pawn_items.expiry = bpas_pawn_purchase_items.expiry
								AND bpas_pawn_items.price = bpas_pawn_purchase_items.pawn_price
								AND bpas_pawn_items.rate = bpas_pawn_purchase_items.pawn_rate
								LEFT JOIN (
									SELECT
										bpas_pawn_return_items.pawn_id,
										bpas_pawn_return_items.product_id,
										bpas_pawn_return_items.product_unit_id,
										bpas_pawn_return_items.serial_no,
										bpas_pawn_return_items.expiry,
										bpas_pawn_return_items.pawn_price,
										bpas_pawn_return_items.pawn_rate,
										sum(
											bpas_pawn_return_items.quantity
										) AS quantity,
										sum(
											bpas_pawn_return_items.return_amount
										) AS return_amount
									FROM
										bpas_pawn_return_items
									GROUP BY
										pawn_id,
										product_id,
										product_unit_id,
										serial_no,
										expiry,
										pawn_price,
										pawn_rate
								) AS bpas_pawn_return_items ON bpas_pawn_items.pawn_id = bpas_pawn_return_items.pawn_id
								AND bpas_pawn_items.product_id = bpas_pawn_return_items.product_id
								AND bpas_pawn_items.product_unit_id = bpas_pawn_return_items.product_unit_id
								AND bpas_pawn_items.serial_no = bpas_pawn_return_items.serial_no
								AND bpas_pawn_items.expiry = bpas_pawn_return_items.expiry
								AND bpas_pawn_items.price = bpas_pawn_return_items.pawn_price
								AND bpas_pawn_items.rate = bpas_pawn_return_items.pawn_rate
								WHERE bpas_pawn_items.pawn_id="'.$pawn_id.'"
							');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function addPawn($data = false, $items = false, $payment = false, $accTrans = false)
    {
        if ($this->db->insert('pawns', $data)) {
            $pawn_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['pawn_id'] = $pawn_id;
					$this->db->insert('pawn_items', $item);
				}
			}
			
			if($payment){
				$payment['pawn_id'] = $pawn_id;
				$this->db->insert('payments', $payment);
			}
			
			// if($accTrans){
			// 	foreach($accTrans as $accTran){
			// 		$accTran['tran_no'] = $pawn_id;
			// 		$this->db->insert('gl_trans',$accTran);
			// 	}
			// }
			

            return true;
        }
        return false;
    }
	
	public function updatePawn($id = false, $data = false, $items = false, $payment = false, $accTrans = false)
    {
        if ($this->db->update('pawns', $data, array('id' => $id))) {
			$this->db->delete('pawn_items',array('pawn_id' => $id));
			$this->db->where('pawn_id = "'.$id.'" AND (pawn_rate_id IS NULL or pawn_rate_id <= 0)')->delete('payments');
			if($items){
				$this->db->insert_batch('pawn_items',$items);
			}
			if($payment){
				$this->db->insert('payments',$payment);
			}
			if($accTrans){
				$this->site->deleteAccTran('Pawn',$id);
				$this->db->insert_batch('acc_tran',$accTrans);
			}
            return true;
        }
        return false;
    }
	

	public function deletePawn($id = false){
		if($this->db->delete('pawns', array('id' => $id))){
			
			$pawn_rates = $this->getRatePaymentByPawn($id);
			if($pawn_rates){
				foreach($pawn_rates as $pawn_rate){
					$this->deletePaymentRate($pawn_rate->id);
				}
			}
			
			$pawn_returns = $this->getPawnReturnByPawn($id);
			if($pawn_returns){
				foreach($pawn_returns as $pawn_return){
					$this->deletePawnReturn($pawn_return->id);
				}
			}
			
			
			$pawn_purchases = $this->getPawnPurchaseByPawn($id);
			if($pawn_purchases){
				foreach($pawn_purchases as $pawn_purchase){
					$this->deletePawnPurchase($pawn_purchase->id);
				}
			}

			$this->db->delete('pawn_items',array('pawn_id' => $id));
			$this->db->delete('product_serials', array('pawn_id' => $id));
			$this->db->delete('payments', array('pawn_id' => $id));
			$this->site->deleteStockmoves('Pawns',$id);
			$this->site->deleteAccTran('Pawn',$id);
			$this->site->deleteAccTran('Pawn Close',$id);
			return true;
		}
		return false;
	}
	
	public function closePawn($id = false){
		if($this->db->update('pawns',array('status'=>'closed'),array('id'=>$id))){
			$pawn_items = $this->getPawnItemWIthPurchase($id);
			if($pawn_items){
				$pawn = $this->getPawnByID($id);
				if($this->Settings->accounting == 1){
					$pawnAcc = $this->site->getAccountSettingByBiller($pawn->biller_id);
				}
				foreach($pawn_items as $pawn_item){
					$quantity = $pawn_item->quantity - $pawn_item->purchase_qty - $pawn_item->return_qty;
					if($quantity > 0){
						$ori_principal = $pawn_item->price * $pawn_item->quantity;;
						$ret_principal = $pawn_item->return_amount;
						$pur_principal = $pawn_item->purchase_amount;
						$principal = $ori_principal - $ret_principal - $pur_principal;
						$pawn_cost = $principal / $quantity;
						
						$stockmove = array(
							'transaction' => 'Pawns',
							'transaction_id' => $id,
							'product_id' => $pawn_item->product_id,
							'product_code' => $pawn_item->product_code,
							'quantity' => $quantity,
							'unit_quantity' => $pawn_item->unit_quantity,
							'warehouse_id' => $pawn->warehouse_id,
							'unit_id' => $pawn_item->product_unit_id,
							'date' => date('Y-m-d H:i:s'),
							'expiry' => $pawn_item->expiry,
							'serial_no' => $pawn_item->serial_no,
							'real_unit_cost' => $pawn_cost,
							'reference_no' => $pawn->reference_no,
							'user_id' => $this->session->userdata('user_id'),
						); 
						$this->db->insert('stockmoves',$stockmove);
						if($this->Settings->accounting_method == '2'){
							$this->site->updateAVGCost($stockmove['product_id'],"Pawns",$id);
						}else if($this->Settings->accounting_method == '1'){
							$this->site->updateLifoCost($stockmove['product_id']);
						}else if($this->Settings->accounting_method == '0'){
							$this->site->updateFifoCost($stockmove['product_id']);
						}else if($this->Settings->accounting_method == '3'){
							$this->site->updateProductMethod($stockmove['product_id'],"Pawns",$id);
						}							
						
						if($this->Settings->accounting == 1){
							$productAcc = $this->site->getProductAccByProductId($pawn_item->product_id);
							if($productAcc){
								$accTrans[] = array(
									'transaction' => 'Pawn Close',
									'transaction_id' => $id,
									'transaction_date' => date('Y-m-d H:i:s'),
									'reference' => $pawn->reference_no,
									'account' => $pawnAcc->pawn_stock_acc,
									'amount' => -($pawn_cost * $quantity),
									'narrative' => 'Product Code: '.$pawn_item->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$pawn_cost,
									'biller_id' => $pawn->biller_id,
									'project_id' => $pawn->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' =>  $pawn->customer_id,
								);
								$accTrans[] = array(
									'transaction' => 'Pawn Close',
									'transaction_id' => $id,
									'transaction_date' => date('Y-m-d H:i:s'),
									'reference' => $pawn->reference_no,
									'account' => $productAcc->stock_acc,
									'amount' => ($pawn_cost * $quantity),
									'narrative' => 'Product Code: '.$pawn_item->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$pawn_cost,
									'biller_id' => $pawn->biller_id,
									'project_id' => $pawn->project_id,
									'user_id' => $this->session->userdata('user_id'),
									'customer_id' =>  $pawn->customer_id,
								);
							}
						}
						
					}
				}
				
				if($accTrans){
					$this->db->insert_batch('acc_tran',$accTrans);
				}
			}
			return true;
		}
		return false;
	}
	
	public function addPawnPurchase($data=array(),$product=array(),$payment=array(), $stockmoves=array(), $product_serials=array(), $accTrans = false){
		if($this->db->insert('pawn_purchases',$data)){
			$pawn_pur_id = $this->db->insert_id();
			if($product){
				foreach($product as $row){
					$row['pawn_pur_id'] = $pawn_pur_id;
					$this->db->insert('pawn_purchase_items',$row);
				}
			}
			if($stockmoves){
				foreach($stockmoves as $stockmove){
					$stockmove['transaction_id'] = $pawn_pur_id;
					$this->db->insert('stockmoves',$stockmove);
					if($this->Settings->accounting_method == '2'){
						$this->site->updateAVGCost($stockmove['product_id'],"Pawns",$pawn_pur_id);
					}else if($this->Settings->accounting_method == '1'){
						$this->site->updateLifoCost($stockmove['product_id']);
					}else if($this->Settings->accounting_method == '0'){
						$this->site->updateFifoCost($stockmove['product_id']);
					}else if($this->Settings->accounting_method == '3'){
						$this->site->updateProductMethod($stockmove['product_id'],"Pawns",$pawn_pur_id);
					}
				}
			}
			if($payment){
				$payment['pawn_purchase_id'] = $pawn_pur_id;
				$this->db->insert('payments',$payment);
			}
			if($product_serials){
				$this->db->insert_batch('product_serials',$product_serials);
			}
			
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $pawn_pur_id;
					$this->db->insert('acc_tran',$accTran);
				}
				
			}
			
			$this->syncePawnStatus($data['pawn_id']);
			return true;
		}
		return false;
	}
	
	
	
	public function addPawnReturn($data=array(),$product=array(),$payment=array(),$rate_data=array(), $rate_products=array(), $rate_payment=array(), $accTrans = false, $accRateTrans = false){
		
		if($this->db->insert('pawn_returns',$data)){
			$pawn_return_id = $this->db->insert_id();
			if($product){
				foreach($product as $row){
					$row['pawn_return_id'] = $pawn_return_id;
					$this->db->insert('pawn_return_items',$row);
				}
			}
			if($payment){
				$payment['pawn_return_id'] = $pawn_return_id;
				$this->db->insert('payments',$payment);
			}
			
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $pawn_return_id;
					$this->db->insert('acc_tran',$accTran);
				}
			}
			
			if($rate_data){
				$this->db->insert('pawn_rates',$rate_data);
				$pawn_rate_id = $this->db->insert_id();
				if($rate_products){
					foreach($rate_products as $rate_product){
						$rate_product['pawn_rate_id'] = $pawn_rate_id;
						$this->db->insert('pawn_rate_items',$rate_product);
					}
				}
				if($rate_payment){
					$rate_payment['pawn_rate_id'] = $pawn_rate_id;
					$this->db->insert('payments',$rate_payment);
				}
				
				if($accRateTrans){
					foreach($accRateTrans as $accRateTran){
						$accRateTran['transaction_id'] = $pawn_rate_id;
						$this->db->insert('acc_tran',$accRateTran);
					}
				}
			}

			$this->syncePawnStatus($data['pawn_id']);
			return true;
		}
		return false;
	}
	
	public function deletePawnReturn($id = false)
    {
		$pawn_return = $this->getPawnReturnByID($id);
		if($this->db->delete('pawn_returns',array('id'=>$id))){
			$this->db->delete('pawn_return_items',array('pawn_return_id'=>$id));
			$this->db->delete('payments',array('pawn_return_id'=>$id));
			$this->site->deleteAccTran('Pawn Return',$id);
			$this->syncePawnStatus($pawn_return->pawn_id);
			return true;
		}
        return FALSE;
    }
	
	public function deletePawnPurchase($id = false)
    {
		$pawn_purchase = $this->getPawnPurchaseByID($id);
		if($this->db->delete('pawn_purchases',array('id'=>$id))){
			$this->db->delete('pawn_purchase_items',array('pawn_pur_id'=>$id));
			$this->db->delete('payments',array('pawn_purchase_id'=>$id));
			$this->site->deleteAccTran('Pawn Purchase',$id);
			$this->site->deleteStockmoves('Pawn Purchase',$id);
			$this->syncePawnStatus($pawn_purchase->pawn_id);
			return true;
		}
        return FALSE;
    }
	
	
	public function addPaymentRate($data=array(),$product=array(),$payment=array(), $pawn_items=array(), $accTrans = false){
		
		if($this->db->insert('pawn_rates',$data)){
			$pawn_rate_id = $this->db->insert_id();
			if($product){
				foreach($product as $row){
					$row['pawn_rate_id'] = $pawn_rate_id;
					$this->db->insert('pawn_rate_items',$row);
				}
			}
			if($payment){
				$payment['pawn_rate_id'] = $pawn_rate_id;
				$this->db->insert('payments',$payment);
			}
			
			if($pawn_items){
				foreach($pawn_items as $pawn_item){
					$this->db->update('pawn_items',array('next_date'=>$pawn_item['next_date']),array('id'=>$pawn_item['pawn_item_id']));
				}
			}
			
			if($accTrans){
				foreach($accTrans as $accTran){
					$accTran['transaction_id'] = $pawn_rate_id;
					$this->db->insert('acc_tran',$accTran);
				}
			}
			
			$this->syncePawnStatus($data['pawn_id']);
			return true;
		}
		return false;
	}

	
	public function updatePaymentRate($id = false, $data=array(),$product=array(),$payment=array(), $pawn_items=array(), $accTrans = false)
    {
        if ($this->db->update('pawn_rates', $data, array('id' => $id))) {
			$this->db->delete('pawn_rate_items',array('pawn_rate_id'=>$id));
			$this->db->delete('payments',array('pawn_rate_id'=>$id));
			if($product){
				$this->db->insert_batch('pawn_rate_items',$product);
			}
			if($payment){
				$this->db->insert('payments',$payment);
			}
			if($pawn_items){
				foreach($pawn_items as $pawn_item){
					$this->db->update('pawn_items',array('next_date'=>$pawn_item['next_date']),array('id'=>$pawn_item['pawn_item_id']));
				}
			}
			
			if($accTrans){
				$this->site->deleteAccTran('Pawn Rate',$id);
				$this->db->insert_batch('acc_tran',$accTrans);
			}
			
			$this->syncePawnStatus($data['pawn_id']);
            return true;
        }
        return false;
    }
	
	public function deletePaymentRate($id = false)
    {
		$payment = $this->getPawnPaymentByID($id);
		if($this->db->delete('pawn_rates',array('id'=>$id))){
			$this->db->delete('pawn_rate_items',array('pawn_rate_id'=>$id));
			$this->db->delete('payments',array('pawn_rate_id'=>$id));
			$this->site->deleteAccTran('Pawn Rate',$id);
			$this->syncePawnStatus($payment->pawn_id);
			return true;
		}
        return FALSE;
    }
	
	public function getPawnRatePayments($pawn_id = false)
    {
		$this->db->select('payments.*')
						->where('pawn_id',$pawn_id)
						->where('type','pawn_rate')
						->order_by('id', 'asc');
		$q = $this->db->get('payments');				
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	
	public function getPawnRateItems($rate_id = false){
		$q = $this->db->get_where('pawn_rate_items',array('pawn_rate_id'=>$rate_id));				
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
	}
	
	public function getPawnPaymentByID($id = false){
		$q = $this->db->get_where('pawn_rates',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
			
		}
		return false;
	}
	
	public function getRatePaymentByPawn($pawn_id = false)
    {
		$q = $this->db->get_where('pawn_rates',array('pawn_id'=>$pawn_id));			
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getPawnReturnByPawn($pawn_id = false)
    {
		$q = $this->db->get_where('pawn_returns',array('pawn_id'=>$pawn_id));			
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getPawnPurchaseByPawn($pawn_id = false)
    {
		$q = $this->db->get_where('pawn_purchases',array('pawn_id'=>$pawn_id));			
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	
	public function getPaymentByID($id = false){
		$q = $this->db->get_where('payments',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
			
		}
		return false;
		
	}
	
	public function getPawnStockmoves($pawn_id = false){
		$q = $this->db->get_where('stockmoves',array('transaction_id'=>$pawn_id,'transaction'=>'Pawns'));
		if($q->num_rows() > 0){
			foreach($q->result_array() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function syncePawnStatus($pawn_id = false){
		$payments = $this->getPawnRatePayments($pawn_id);
		$payment_rate = 0;
		if($payments){
			foreach($payments as $payment){
				$payment_rate += $payment->amount;
			}
		}
		$pawn_qty = 0;
		$pawn_pur = 0;
		$pawn_ret = 0;
		$pawn_items = $this->getPawnItemWIthPurchase($pawn_id);
		if($pawn_items){
			foreach($pawn_items as $pawn_item){
				$pawn_qty += $pawn_item->quantity;
				$pawn_pur += $pawn_item->purchase_qty;
				$pawn_ret += $pawn_item->return_qty;
			}
		}
		if($pawn_qty == ($pawn_pur+$pawn_ret)){
			$this->db->update('pawns',array('payment_rate'=>$payment_rate,'status'=>'completed'),array('id'=>$pawn_id));
		}else if(($pawn_pur+$pawn_ret) > 0){
			$this->db->update('pawns',array('payment_rate'=>$payment_rate,'status'=>'partial'),array('id'=>$pawn_id));
		}else{
			$this->db->update('pawns',array('payment_rate'=>$payment_rate,'status'=>'pending'),array('id'=>$pawn_id));
		} 
	}

	public function getPawnPurchaseByID($id = false){
		$this->db->select('pawn_purchases.*,pawns.reference_no as pawn_ref')
		->join('pawns','pawns.id=pawn_purchases.pawn_id','inner')
		->where('pawn_purchases.id',$id);
		$q = $this->db->get('pawn_purchases');
		if($q->num_rows()>0){
			return $q->row();
		}
		return false;
	}
	
	public function getPawnReturnByID($id = false){
		$this->db->select('pawn_returns.*,pawns.reference_no as pawn_ref')
		->join('pawns','pawns.id=pawn_returns.pawn_id','inner')
		->where('pawn_returns.id',$id);
		$q = $this->db->get('pawn_returns');
		if($q->num_rows()>0){
			return $q->row();
		}
		return false;
	}
	
	public function getPawnInfo($id = false){
		$this->db->select('pawns.*,pawn_rates.id as rate_id,pawn_returns.id as return_id,pawn_purchases.id as purchase_id')
		->join('pawn_rates','pawns.id = pawn_rates.pawn_id','left')
		->join('pawn_returns','pawns.id = pawn_returns.pawn_id','left')
		->join('pawn_purchases','pawns.id = pawn_purchases.pawn_id','left')
		->where('pawns.id',$id)
		->group_by('pawns.id');
		$q = $this->db->get('pawns');
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getAllPurhcasePawnItems($pur_pawn_id = false){
		$q = $this->db->get_where('pawn_purchase_items',array('pawn_pur_id'=>$pur_pawn_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getAllReturnPawnItems($return_pawn_id = false){
		$q = $this->db->get_where('pawn_return_items',array('pawn_return_id'=>$return_pawn_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getCategoryByID($id = false)
    {
        $q = $this->db->get_where('categories', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addProduct($addProduct = false){
		if($this->db->insert('products', $addProduct)){
            $product_id = $this->db->insert_id();
			$product_units = array('product_id'=>$product_id,
									'unit_id'=>$addProduct['unit'],
									'unit_qty'=>1
									);
			$this->db->insert('product_units', $product_units);
			if($this->Settings->accounting == 1){
				$catd = $this->getCategoryByID($addProduct['category_id']);
				if($catd){
					$product_acc = array(
										'product_id' => $product_id,
										'type' => 'standard',
										'stock_acc' => $catd->stock_acc,
										'adjustment_acc' => $catd->adjustment_acc,
										'usage_acc' => $catd->usage_acc,
										'cost_acc' => $catd->cost_acc,
										'discount_acc' => $catd->discount_acc,
										'sale_acc' => $catd->sale_acc,
										'expense_acc' => $catd->expense_acc,
										'pawn_acc' => $catd->pawn_acc,
									);
					$this->db->insert('acc_product',$product_acc);					
				}
			}
			return $product_id;
		}
		return false;
	}

	
}



















