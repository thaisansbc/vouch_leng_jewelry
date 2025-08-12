<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases_order_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('approved_model');

//        $this->default_biller_id = $this->site->default_biller_id();
    }
	public function getVariantQtyById($id) {
		$q = $this->db->get_where('product_variants', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addPurchaseRequest($data, $items)
    {
		if ($this->db->insert('purchases_request', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('purchase_request_items', $item);
      
                if($item['option_id']) {
                    $this->db->update('product_variants', array('cost' => $item['real_unit_cost']), array('id' => $item['option_id'], 'product_id' => $item['product_id']));
                }
                if ($data['status'] == 'received' || $data['status'] == 'returned') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }
            return true;
        }
        return false;
    }

	public function getAllPurchaseRequestItems($purchase_id)
    {
        $this->db->select('purchase_request_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=purchase_request_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_request_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_request_items.tax_rate_id', 'left')
            ->group_by('purchase_request_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_request_items', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllPurchaseRequestItems_create($purchase_id)
    {
        $this->db->select('purchase_request_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant,companies.name')
            ->join('products', 'products.id=purchase_request_items.product_id', 'left')
			->join('companies', 'companies.id=purchase_request_items.supplier_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_request_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_request_items.tax_rate_id', 'left')
            ->group_by('purchase_request_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('purchase_request_items', array('purchase_id' => $purchase_id,'create_status'=>'0'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getPurchase_order_detail_ByID($id)
    {
        $this->db->select('purchases_order.*,
                companies.city,companies.phone,companies.state,
                projects.project_name,warehouses.name as warehouse_name,

                purchase_order_items.purchase_id,purchase_order_items.product_code,
                purchase_order_items.product_name,purchase_order_items.unit_cost,purchase_order_items.quantity,
                purchase_order_items.product_unit_code,purchase_order_items.subtotal')
        
            ->join('purchase_order_items', 'purchases_order.id=purchase_order_items.purchase_id', 'left')
            ->join('projects', 'projects.project_id=purchases_order.project_id', 'left')
            ->join('warehouses', 'warehouses.id=purchases_order.warehouse_id', 'left')
            ->join('companies', 'companies.id=purchases_order.supplier_id', 'left');
        $q = $this->db->get_where('purchases_order', array('purchase_order_items.purchase_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

	
	public function getPurchaseRequestByID($id)
    {
        $q = $this->db->get_where('bpas_purchases_request', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function UpdatePurchaseRequest($id, $data, $items = array())
    {
        $opurchase = $this->getPurchaseRequestByID($id);
        $oitems = $this->getAllPurchaseRequestItems($id);
        if ($this->db->update('purchases_request', $data, array('id' => $id)) && $this->db->delete('purchase_request_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('purchase_request_items', $item);
                if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }
            }
            
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
              
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0-$oitem->quantity), 'cost' => $oitem->real_unit_cost));
                }
            }
            $this->site->syncPurchasePayments($id);
            return true;
        }

        return false;
    }
	
	 public function deletePurchaseRequest($id)
    {
        if ($this->db->delete('purchase_request_items', array('purchase_id' => $id)) && $this->db->delete('purchases_request', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
	
	public function getPurchaseRequestId($id){
		$this->db->select('id, date, reference_no, supplier, status, grand_total,order_status ');
		$this->db->from('purchases_request');
		//$this->db->join('gl_sections', 'gl_sections.sectionid=gl_charts.sectionid','INNER');
		$this->db->where('purchases_request.id' , $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
    public function getPurchaseorderByID($id)
    {
        $this->db->select('*, bpas_purchases_order.biller_id as biller_id, bpas_purchases_order.warehouse_id as warehouse_id')
            ->join('projects', 'projects.project_id=purchases_order.project_id', 'left');
        $q = $this->db->get_where('purchases_order', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllPurchase_orderItems($purchase_id)
    {
        $this->db->select('purchase_order_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.details as details, product_variants.name as variant, products.hsn_code as hsn_code, products.second_name as second_name')
            ->join('products', 'products.id=purchase_order_items.product_id', 'left')
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

    public function getAllPurchaseOrderItems($purchase_order_id = false)
    {
        $this->db->select('purchase_order_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.unit, products.image, products.details as details, product_variants.name as variant, units.name as unit_name,products.quantity as qoh')
            ->join('products', 'products.id=purchase_order_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=purchase_order_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=purchase_order_items.tax_rate_id', 'left')
            ->join('units','units.id = purchase_order_items.product_unit_id','left')
            ->group_by('purchase_order_items.id')
            ->order_by('id', 'desc');
        $q = $this->db->get_where('purchase_order_items', array('purchase_id' => $purchase_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function addPurchaseorder($id, $data, $items = array())
    {
        if ($this->db->insert('purchases_order', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('po') == $data['reference_no']) {
                $this->site->updateReference('po');
            }
            foreach ($items as $item) {
                unset($item['quantity_received']);
                $item['quantity_received'] = 0;
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $aprroved['purchase_order_id'] = $purchase_id;
                // $this->approved_model->addApporved($aprroved);

                $this->db->insert('purchase_order_items', $item);
            }
            if($id){
                $POI = $this->site->getPOI_By_PRID($id);
                $PRI = $this->site->getPRI_By_PRID($id);
                $status = 'completed';
                foreach($PRI as $pr_item){
                    $key = array_search($pr_item->id, array_column($POI, 'pri_id'));
                    if($key !== false){
                        if($pr_item->quantity > $POI[$key]->quantity){
                            $status = 'partial';
                            break;
                        }
                    } else {
                        $status = 'partial';
                        break;
                    }
                }

                $this->db->update('purchases_request', array('order_status' => $status), array('id' => $id));
            }
            return true;
        }
        return false;
    }
    public function UpdatePurchaseorder($id, $data, $items = array())
    {
        $opurchase = $this->getPurchaseorderByID($id);
        $oitems = $this->getAllPurchase_orderItems($id);
        if ($this->db->update('purchases_order', $data, array('id' => $id)) && $this->db->delete('purchase_order_items', array('purchase_id' => $id))) {
            $purchase_id = $id;
            foreach ($items as $item) {
                $item['purchase_id'] = $id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $this->db->insert('purchase_order_items', $item);
                if($id){
                    $POI = $this->site->getPOI_By_PRID($id);
                    $PRI = $this->site->getPRI_By_PRID($id);
                    $status = 'completed';
                    if (!empty($PRI)){
                        foreach($PRI as $pr_item){
                            if (!empty($POI)) {
                                $key = array_search($pr_item->id, array_column($POI, 'pri_id'));
                                if($key !== false){
                                    if($pr_item->quantity > $POI[$key]['quantity']){
                                        $status = 'partial';
                                        break;
                                    }
                                } else {
                                    $status = 'partial';
                                    break;
                                }
                            }
                        }
                    }
                    $this->db->update('purchases_request', array('order_status' => $status), array('id' => $id));
                }
   
            }
        
            $this->site->syncPurchasePayments($id);
            return true;
        }

        return false;
    }
    public function deletePurchaseOrder($id = false)
    {
        $po = $this->getPurchaseOrderByID($id);
        if ($this->db->delete('purchase_orders', array('id' => $id,'status !=' => 'completed'))) {
            $this->db->delete('purchase_order_items', array('purchase_order_id' => $id));
            $payments = $this->getPOPayments($id);
            $this->db->delete('payments',array('purchase_order_id' =>$id));
            if($payments){
                foreach($payments as $payment){
                    $this->site->deleteAccTran('Purchase Order Deposit',$payment->id);
                }
            }
            $this->syncPR(false,$po->pr_id);
            return true;
        }
        return FALSE;
    }
    public function getPOPayments($purchase_order_id = false){
        $q = $this->db->get_where("payments",array("purchase_order_id"=>$purchase_order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function syncPR($po_id = false, $pr_id = false){
        if(!$pr_id){
            $po = $this->getPurchaseOrderByID($po_id);
            $pr_id = $po->pr_id;
        }
        if($pr_id){
            $this->db->update("purchase_request_items",array("po_quantity"=>0),array("purchase_request_id"=>$pr_id));
            $status = "approved";
            
            
            $this->db->select("product_id, sum(quantity) as quantity");
            $this->db->join("purchase_orders","purchase_orders.id = purchase_order_items.purchase_order_id","INNER");
            $this->db->where("purchase_orders.pr_id",$pr_id);
            $this->db->group_by("product_id");
            $q = $this->db->get("purchase_order_items");
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $this->db->update("purchase_request_items",array("po_quantity"=>$row->quantity),array("purchase_request_id"=>$pr_id,"product_id"=>$row->product_id));
                }
                $approved = 0;
                $partial = 0;
                $completed = 0;
                $pr_items = $this->db->get_where("purchase_request_items",array("purchase_request_id"=>$pr_id));
                if ($pr_items->num_rows() > 0) {
                    foreach (($pr_items->result()) as $pr_item) {
                        if($this->config->item("pr_approve_item")){
                            $pr_item->quantity = $pr_item->quantity_approved;
                        }
                        if($pr_item->po_quantity >= $pr_item->quantity){
                            $completed++;
                        }else if($pr_item->po_quantity > 0){
                            $partial++;
                        }else{
                            $approved++;
                        }
                    }
                }
                if($partial > 0){
                    $status = "partial";
                }else if($approved > 0){
                    $status = "approved";
                }else{
                    $status = "completed";
                }
                
            }
            $this->db->update("purchase_requests",array("status"=>$status),array("id"=>$pr_id));
        }
    }
    public function deleteorder($id)
    {
        if ($this->db->delete('purchase_order_items', array('purchase_id' => $id)) && $this->db->delete('purchases_order', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
}
