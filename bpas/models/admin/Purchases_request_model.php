<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Purchases_request_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('approved_model');
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
		//$this->erp->print_arrays($data, $items);
		if ($this->db->insert('purchases_request', $data)) {
            $purchase_id = $this->db->insert_id();
            if ($this->site->getReference('pr') == $data['reference_no']) {
                $this->site->updateReference('pr');
            }
            foreach ($items as $item) {
                $item['purchase_id'] = $purchase_id;
                $item['option_id'] = !empty($item['option_id']) && is_numeric($item['option_id']) ? $item['option_id'] : NULL;
                $aprroved['purchase_request_id']= $purchase_id;
                // $this->approved_model->addApporved($aprroved);
                $this->db->insert('purchase_request_items', $item);
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
	public function getPurchaseRequestByID($id)
    {
        $this->db->select('*, bpas_purchases_request.biller_id as biller_id, bpas_purchases_request.warehouse_id as warehouse_id')
            ->join('projects', 'projects.project_id = purchases_request.project_id', 'left');
		$q = $this->db->get_where('purchases_request', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getPurchaseRequestByID_($id)
    {
		$q = $this->db->get_where('purchases_request', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getPurchase_detail_ByID($id)
    {
        $this->db->select('purchases_request.*,
                companies.city,companies.phone,companies.state,
                projects.project_name,warehouses.name as warehouse_name,

                purchase_request_items.purchase_id,purchase_request_items.product_code,
                purchase_request_items.product_name,purchase_request_items.unit_cost,purchase_request_items.quantity,
                purchase_request_items.product_unit_code,purchase_request_items.subtotal')
        
            ->join('purchase_request_items', 'purchases_request.id=purchase_request_items.purchase_id', 'left')
            ->join('projects', 'projects.project_id=purchases_request.project_id', 'left')
            ->join('warehouses', 'warehouses.id=purchases_request.warehouse_id', 'left')
            ->join('companies', 'companies.id=purchases_request.supplier_id', 'left');
        $q = $this->db->get_where('purchases_request', array('purchase_request_items.purchase_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
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
                /*if ($data['status'] == 'received' || $data['status'] == 'partial') {
                    $this->updateAVCO(array('product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'quantity' => $item['quantity'], 'cost' => $item['real_unit_cost']));
                }*/
            }
            /*
            if ($data['status'] == 'received' || $data['status'] == 'partial') {
    
                foreach ($oitems as $oitem) {
                    $this->updateAVCO(array('product_id' => $oitem->product_id, 'warehouse_id' => $oitem->warehouse_id, 'quantity' => (0-$oitem->quantity), 'cost' => $oitem->real_unit_cost));
                }
            }
            $this->site->syncPurchasePayments($id);
            */
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
}
