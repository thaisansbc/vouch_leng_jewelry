<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	
	public function getPaymentReferenceBySaleRef($sale_ref)
	{ 
		$q = $this->db->select('payments.reference_no as paymentRef')
					->from('sales')
					->join('payments', 'sales.id = payments.sale_id')
					->where('sales.reference_no', $sale_ref)
					->get();
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	function getGlTransbyAccount($code){
        $q = $this->db->select('account_code')->from('gl_trans')->where('account_code', $id)->get();
        if ($q->num_rows() > 0) {
            return true;
        }
        return false;
    }
	public function deleteChartAccount($id)
	{
        $q = $this->db->get_where('gl_trans', ['account_code' => $id]);
        if ($q->num_rows() > 0) {
            return false;
        }else{
            $q = $this->db->delete('gl_charts', array('accountcode' => $id));
            return true;
        }
        return FALSE;
	}

    public function getProductNames($term, $warehouse_id, $limit = 5)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate, type, tax_method')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        if ($this->Settings->overselling) {
            $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        } else {
            $this->db->where("type = 'standard' AND warehouses_products.warehouse_id = '" . $warehouse_id . "' AND warehouses_products.quantity > 0 AND "
                . "(name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }
	
	public function getAlltypes()
	{
		$q = $this->db->query("SELECT * from bpas_groups WHERE bpas_groups.id IN (4,5)");
		
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getAllcharts() 
	{
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
	public function getWHProduct($id)
    {
        $this->db->select('products.id, code, name, warehouses_products.quantity, cost, tax_rate')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('products.id');
        $q = $this->db->get_where('products', array('warehouses_products.product_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function addTransfer($data = array(), $items = array())
    {
        $status = $data['status'];
        if ($this->db->insert('transfers', $data)) {
            $transfer_id = $this->db->insert_id();
            if ($this->site->getReference('to') == $data['transfer_no']) {
                $this->site->updateReference('to');
            }
            foreach ($items as $item) {
                $item['transfer_id'] = $transfer_id;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['quantity'], $item['option_id']);
                }
            }

            return true;
        }
        return false;
	}
	
	public function addAccTransfer($data = array(), $items = array())
    {
        if ($this->db->insert('account_transfer', $data)) {
            $account_transfer_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['transfer_account_id'] = $account_transfer_id;
				$this->db->insert('account_transfer_item', $item);
			}
			$this->site->updateReference('pp');
            return true;
        }
        return false;
	}
	public function getAccTransferByID($id)
    {
        $q = $this->db->get_where('account_transfer', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getAllAccTransferItemsByAccID($id)
    {
        $q = $this->db->get_where('account_transfer_item', array('	transfer_account_id' => $id));
        if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function deleteAccTransfer($id){
		if($this->db->delete('account_transfer_item', array('transfer_account_id' => $id)) && $this->db->delete('account_transfer', array('id' => $id))){
			return true;			
		}
		return false;
	}

    public function updateTransfer($id, $data = array(), $items = array())
    {
        $ostatus = $this->resetTransferActions($id);
        $status = $data['status'];
        if ($this->db->update('transfers', $data, array('id' => $id))) {
            $tbl = $ostatus == 'completed' ? 'purchase_items' : 'transfer_items';
            $this->db->delete($tbl, array('transfer_id' => $id));

            foreach ($items as $item) {
                $item['transfer_id'] = $id;
                if ($status == 'completed') {
                    $item['date'] = date('Y-m-d');
                    $item['warehouse_id'] = $data['to_warehouse_id'];
                    $item['status'] = 'received';
                    $this->db->insert('purchase_items', $item);
                } else {
                    $this->db->insert('transfer_items', $item);
                }

                $status = $data['status'];
                if ($status == 'sent' || $status == 'completed') {
                    $this->syncTransderdItem($item['product_id'], $data['from_warehouse_id'], $item['quantity'], $item['option_id']);
                }
            }
            return true;
        }
        return false;
    }

    public function getProductWarehouseOptionQty($option_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products_variants', array('option_id' => $option_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByCategoryID($id)
    {

        $q = $this->db->get_where('products', array('category_id' => $id), 1);
        if ($q->num_rows() > 0) {
            return true;
        }

        return FALSE;
    }
	
	public function getAccountSections()
	{
		$this->db->select("sectionid,sectionname");
		$section = $this->db->get("gl_sections");
		if($section->num_rows() > 0){
			return $section->result_array();	
		}
		return false;
	}
	
	public function getSubAccounts($section_code)
	{
		$this->db->select('accountcode as id, CONCAT(accountcode, " | ", accountname) as text');
        $q = $this->db->get_where("gl_charts", array('sectionid' => $section_code));
        if ($q->num_rows() > 0) {
			$data[] = array('id' => '0', 'text' => 'None');
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
	}
	
	
	public function getpeoplebytype($company)
	{
		if($company == 'emp'){
			$this->db->select("name as id, name as text");
			$q = $this->db->get_where("companies", array('group_name' => 'employee'));
		}else{
			$this->db->select("name as id,CONCAT(name) as text");
			$q = $this->db->get_where("companies", array('group_id' => $company));
		}

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }

        return FALSE;
	}
	
	
	public function addChartAccount($data)
	{
		//$this->bpas->print_arrays($data);
		if ($this->db->insert('gl_charts', $data)) {
            return true;
        }
        return false;
	}
	
	public function updateChartAccount($id,$data)
	{
		//$this->bpas->print_arrays($data);
		$this->db->where('accountcode', $id);
		$q=$this->db->update('gl_charts', $data);
        if ($q) {
            return true;
        }
        return false;
	}

    public function getProductQuantity($product_id, $warehouse = DEFAULT_WAREHOUSE)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse), 1);
        if ($q->num_rows() > 0) {
            return $q->row_array(); //$q->row();
        }
        return FALSE;
    }

    public function insertQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->insert('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'quantity' => $quantity))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }

    public function updateQuantity($product_id, $warehouse_id, $quantity)
    {
        if ($this->db->update('warehouses_products', array('quantity' => $quantity), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id))) {
            $this->site->syncProductQty($product_id, $warehouse_id);
            return true;
        }
        return false;
    }
	
	public function updateSetting($data,$biller_id)
	{
        if($biller_id){
            $q = $this->db->where('biller_id',$biller_id);
        }
		if ($this->db->update('account_settings', $data)) {
            return true;
        }
        return false;
	}

    public function getProductByCode($code)
    {

        $q = $this->db->get_where('products', array('code' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getProductByName($name)
    {

        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }
	
	public function getChartAccountByID($id)
	{
		$this->db->select('gl_charts.accountcode,gl_charts.accountname,gl_charts.parent_acc,gl_charts.sectionid,gl_sections.sectionname, bank,type,gl_charts.cash_flow,gl_charts.nature');
		$this->db->from('gl_charts');
		$this->db->join('gl_sections', 'gl_sections.sectionid=gl_charts.sectionid','INNER');
		$this->db->where('gl_charts.accountcode' , $id);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getAllChartAccount()
	{
		$this->db->select('gl_charts.accountcode,gl_charts.accountname,gl_charts.parent_acc,gl_charts.sectionid');
		$this->db->from('gl_charts');
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }

        return FALSE;
	}
	public function getAllAccounts($term = false, $limit = false)
    {
        $limit = $this->Settings->rows_per_page;
        $this->db->select('id,accountcode as code,accountname as name');
        //$this->db->where('code NOT IN (SELECT parent_code FROM '.$this->db->dbprefix('acc_chart').' WHERE parent_code<> "" GROUP BY parent_code)');
        $this->db->where("(" . $this->db->dbprefix('gl_charts') . ".accountname LIKE '%" . $term . "%' OR accountcode LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('gl_charts') . ".accountname, ' (', accountcode, ')') LIKE '%" . $term . "%')");
        
        $this->db->limit($limit);
        $q = $this->db->get('gl_charts');
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	public function getAllChartAccountIn($section_id)
	{
		$q = $this->db->query("SELECT
									accountcode,
									accountname,
									parent_acc,
									sectionid
								FROM
									bpas_gl_charts
								WHERE
								    sectionid IN ($section_id)");
		
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
	}
	public function getAllChartAccountexpense($section_id)
    {
        $q = $this->db->query("SELECT
                                    accountcode,
                                    accountname,
                                    parent_acc,
                                    sectionid
                                FROM
                                    bpas_gl_charts
                                WHERE
                                    sectionid IN ($section_id)");
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	public function getCustomers()
    {
        $q = $this->db->query("SELECT
									id, company
								FROM
									bpas_companies
								WHERE
									group_name = 'biller'
								");
		
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }
	
	public function getAllChartAccounts()
	{
		$q = $this->db->query("SELECT
									accountcode,
									accountname,
									parent_acc,
									sectionid
								FROM
									bpas_gl_charts
								");
		
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
	}
	
	public function getBillers()
    {
		$this->db->select('company');
		$this->db->from('companies');
		$this->db->join('account_settings', 'account_settings.biller_id=companies.id');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getBillersArray($id)
    {
		$this->db->where_in('id', $id);
		$q = $this->db->get_where('companiess', array('group_name' => 'biller'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getSalename()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_sale=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getsalediscount()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_sale_discount=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getsale_tax()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_sale_tax=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getreceivable()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_receivable=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getpurchases()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_purchase=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getGLYearMonth()
	{
		$query = $this->db->select("MIN(YEAR(tran_date)) AS min_year, MIN(MONTH(tran_date)) AS min_month")
				->get('gl_trans');
		if($query->num_rows() > 0){
			return $query->row();
		}
		return false;
	}
	
	
	public function getpurchase_tax()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_purchase_tax=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	
	public function getpurchasediscount()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_purchase_discount=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getpayable()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_payable=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_sale_freights()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_sale_freight=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_purchase_freights()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_purchase_freight=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
	public function getstocks()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_stock=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getstock_adjust()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_stock_adjust=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_cost()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_cost=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getpayrolls()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_payroll=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_cash()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_cash=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getcredit_card()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_credit_card=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_sale_deposit()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_sale_deposit=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_purchase_deposit()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_purchase_deposit=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getcheque()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_cheque=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_loan()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_loan=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_retained_earning()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_retained_earnings=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function get_cost_of_variance()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_cost_variant=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getInterestIncome()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_interest_income=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getTransferOwner()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_transfer_owner=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getgift_card()
    {
		$this->db->select('accountname');
		$this->db->from('account_settings');
		$this->db->join('gl_charts', 'account_settings.default_gift_card=gl_charts.accountcode');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getAllChartAccountBank(){
		$this->db->select('gl_charts.accountcode,gl_charts.accountname,gl_charts.parent_acc,gl_charts.sectionid');
		$this->db->from('gl_charts');
		$this->db->where('bank', 1);
		$q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }

        return FALSE;
	}
	
	public function updateJournal($rows, $old_reference_no = NULL) {
		//$ids = '';
		//$ref = '';
		//$this->bpas->print_arrays($rows);
		foreach($rows as $data){
			$gl_chart = $this->getChartAccountByID($data['account_code']);	
			if($gl_chart > 0){
				$data['sectionid'] = $gl_chart->sectionid;
				$data['narrative'] = $gl_chart->accountname;
			}
			//$ref = $data['reference_no'];
			
			if($data['tran_id'] != 0){
				$this->db->where('tran_id' , $data['tran_id']);
				$q = $this->db->update('gl_trans', $data);
				if ($q) {
					if($gl_chart->bank == 1){
						$payment = array(
							'date' => $data['tran_date'],
							'biller_id' => $data['biller_id'],
							'transaction_id' => $data['tran_id'],
							'amount' => $data['amount'],
							'reference_no' => $data['reference_no'],
							'paid_by' => $data['narrative'],
							'note' => $data['description'],
							'bank_account' => $data['bank_account'],
							'type' => 'received',
							'created_by' => $this->session->userdata('user_id')
						);
						$this->db->update('payments', $payment, array('transaction_id' => $data['tran_id']));
					}
					//$ids .= $data['tran_id'] . ',';
				}
			}else{
				if($this->db->insert('gl_trans', $data)) {
					$tran_id = $this->db->insert_id();
					if($gl_chart->bank == 1){
						$payment = array(
							'date' => $data['tran_date'],
							'biller_id' => $data['biller_id'],
							'transaction_id' => $tran_id,
							'amount' => $data['amount'],
							'reference_no' => $data['reference_no'],
							'paid_by' => $data['narrative'],
							'note' => $data['description'],
							'type' => 'received',
							'bank_account' => $data['account_code'],
							'created_by' => $this->session->userdata('user_id')
						);
						$this->db->insert('payments', $payment);
					}
					//$ids .= $tran_id . ',';
				}
			}
		}
	}
	
	public function addJournal($rows){
		foreach($rows as $data){
			$gl_chart = $this->getChartAccountByID($data['account_code']);
			if($gl_chart > 0){
				$data['sectionid'] = $gl_chart->sectionid;
				$data['narrative'] = $gl_chart->accountname;
			}
			
			if ($this->db->insert('gl_trans', $data)) {
				$tran_id = $this->db->insert_id();
				
				if ($gl_chart->bank == 1) {
					$payment = array(
						'date' 			=> $data['tran_date'],
						'biller_id' 	=> $data['biller_id'],
						'transaction_id'=> $tran_id,
						'amount' 		=> $data['amount'],
						'reference_no'	=> $data['reference_no'],
						'paid_by' 		=> $data['narrative'],
						'note' 			=> $data['description'],
						'bank_account' 	=> $data['account_code'],
						'type' 			=> 'received',
						'created_by' 	=> $this->session->userdata('user_id')
					);

					$this->db->insert('payments', $payment);
				}
				
				if ($this->site->getReference('jr',$data['biller_id']) == $data['reference_no']) {
					$this->site->updateReference('jr',$data['biller_id']);
				}
				
			}
		}
	}
	
	public function getJournalByTranNoTranID($tran_id, $tran_no){
		$q = $this->db->get_where('gl_trans', array('tran_id' => $tran_id, 'tran_no' => $tran_no), 1);
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->tr;
		}
		return FALSE;
	}
	
	public function getTranNo(){
		/*
		$this->db->query("UPDATE bpas_order_ref
							SET tr = tr + 1
							WHERE
							DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
		*/
		/*
		$q = $this->db->query("SELECT tr FROM bpas_order_ref
									WHERE DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
									*/

		$this->db->select('(COALESCE (MAX(tran_no), 0) + 1) AS tr');
		$q = $this->db->get('gl_trans');
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->tr;
		}
		return FALSE;
	}
	
	public function getTranNoByRef($ref){
		$this->db->select('tran_no');
		$this->db->where('reference_no', $ref);
		$q = $this->db->get('gl_trans');
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->tran_no;
		}
		return FALSE;
	}
	
	public function getTranTypeByRef($ref){
		$this->db->select('tran_type');
		$this->db->where('reference_no', $ref);
		$q = $this->db->get('gl_trans');
		if($q->num_rows() > 0){
			$row = $q->row();
			return $row->tran_type;
		}
		return FALSE;
	}
	
	public function deleteJournalByRef($ref){
		$q = $this->db->delete('gl_trans', array('reference_no' => $ref));
		if($q){
			return true;
		}
		return false;
	}
	
	public function getJournalByRef($ref){
		$this->db->select('gl_trans.*, (IF(bpas_gl_trans.amount > 0, bpas_gl_trans.amount, null)) as debit, 
							(IF(bpas_gl_trans.amount < 0, abs(bpas_gl_trans.amount), null)) as credit');
		$q = $this->db->get_where('gl_trans', array('reference_no' => $ref));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
	}
	
	public function getJournalByTranNo($tran_no){
		$this->db->select('gl_trans.*, (IF(bpas_gl_trans.amount > 0, bpas_gl_trans.amount, null)) as debit, 
							(IF(bpas_gl_trans.amount < 0, abs(bpas_gl_trans.amount), null)) as credit');
		$q = $this->db->get_where('gl_trans', array(
            'tran_no' => $tran_no,
            'tran_type' => 'JOURNAL'
        ));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
	}
    public function getSingleJournalByTranNo($tran_no){
        $this->db->select('gl_trans.*, (IF(bpas_gl_trans.amount > 0, bpas_gl_trans.amount, null)) as debit, 
                            (IF(bpas_gl_trans.amount < 0, abs(bpas_gl_trans.amount), null)) as credit');
        $q = $this->db->get_where('gl_trans', array(
            'tran_no' => $tran_no,
            'tran_type' => 'JOURNAL'
        ),1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
    }
	
    public function getTransferByID($id)
    {

        $q = $this->db->get_where('transfers', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function getAllTransferItems($transfer_id, $status)
    {
        if ($status == 'completed') {
            $this->db->select('purchase_items.*, product_variants.name as variant')
                ->from('purchase_items')
                ->join('product_variants', 'product_variants.id=purchase_items.option_id', 'left')
                ->group_by('purchase_items.id')
                ->where('transfer_id', $transfer_id);
        } else {
            $this->db->select('transfer_items.*, product_variants.name as variant')
                ->from('transfer_items')
                ->join('product_variants', 'product_variants.id=transfer_items.option_id', 'left')
                ->group_by('transfer_items.id')
                ->where('transfer_id', $transfer_id);
        }
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getWarehouseProduct($warehouse_id, $product_id, $variant_id)
    {
        if ($variant_id) {
            $data = $this->getProductWarehouseOptionQty($variant_id, $warehouse_id);
            return $data;
        } else {
            $data = $this->getWarehouseProductQuantity($warehouse_id, $product_id);
            return $data;
        }
        return FALSE;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductOptions($product_id, $warehouse_id, $zero_check = TRUE)
    {
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.cost as cost, product_variants.quantity as total_quantity, warehouses_products_variants.quantity as quantity')
            ->join('warehouses_products_variants', 'warehouses_products_variants.option_id=product_variants.id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->where('warehouses_products_variants.warehouse_id', $warehouse_id)
            ->group_by('product_variants.id');
        if ($zero_check) {
            $this->db->where('warehouses_products_variants.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductComboItems($pid, $warehouse_id)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, warehouses_products.quantity as quantity')
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

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPurchasedItems($product_id, $warehouse_id, $option_id = NULL) 
	{
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->select('id, quantity, quantity_balance, net_unit_cost, unit_cost, item_tax');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('quantity_balance !=', 0);
        if ($option_id) {
            $this->db->where('option_id', $option_id);
        }
        $this->db->group_by('id');
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncTransderdItem($product_id, $warehouse_id, $quantity, $option_id = NULL)
    {
        if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id)) {
            $balance_qty = $quantity;
            foreach ($pis as $pi) {
                if ($balance_qty <= $quantity && $quantity > 0) {
                    if ($pi->quantity_balance >= $quantity) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $this->db->update('purchase_items', array('quantity_balance' => $balance_qty), array('id' => $pi->id));
                        $quantity = 0;
                    } elseif ($quantity > 0) {
                        $quantity = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $this->db->update('purchase_items', array('quantity_balance' => 0), array('id' => $pi->id));
                    }
                }
                if ($quantity == 0) { break; }
            }
        } else {
            $clause = array('purchase_id' => NULL, 'transfer_id' => NULL, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
            if ($pi = $this->site->getPurchasedItem($clause)) {
                $quantity_balance = $pi->quantity_balance - $quantity;
                $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
            } else {
                $clause['quantity'] = 0;
                $clause['item_tax'] = 0;
                $clause['quantity_balance'] = (0 - $quantity);
                $this->db->insert('purchase_items', $clause);
            }
        }
        $this->site->syncQuantity(NULL, NULL, NULL, $product_id);
    }
	
	function getBalanceSheetDetailByAccCode($code = NULL, $section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL) {
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN ($biller_id) "; 
		}
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
			AND '$to_date' ";
		}
		$query = $this->db->query("SELECT
			bpas_gl_trans.tran_type,
			bpas_gl_trans.tran_date,
			bpas_gl_trans.reference_no,
			(
				CASE
				WHEN bpas_gl_trans.tran_type = 'SALES' THEN
					(
						SELECT
							bpas_sales.customer
						FROM
							bpas_sales
						WHERE
							bpas_gl_trans.reference_no = bpas_sales.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES' THEN
					(
						SELECT
							bpas_purchases.supplier
						FROM
							bpas_purchases
						WHERE
							bpas_gl_trans.reference_no = bpas_purchases.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'SALES-RETURN' THEN
					(
						SELECT
							bpas_return_sales.customer
						FROM
							bpas_return_sales
						WHERE
							bpas_return_sales.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES-RETURN' THEN
					(
						SELECT
							bpas_return_purchases.supplier
						FROM
							bpas_return_purchases
						WHERE
							bpas_return_purchases.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'DELIVERY' THEN
					(
						SELECT
							bpas_deliveries.customer
						FROM
							bpas_deliveries
						WHERE
							bpas_deliveries.do_reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				ELSE
					''
				END
			) AS customer,
			(
				CASE
				WHEN bpas_gl_trans.tran_type = 'SALES' THEN
					(
						SELECT
							bpas_sales.note
						FROM
							bpas_sales
						WHERE
							bpas_gl_trans.reference_no = bpas_sales.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES' THEN
					(
						SELECT
							bpas_purchases.note
						FROM
							bpas_purchases
						WHERE
							bpas_gl_trans.reference_no = bpas_purchases.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'SALES-RETURN' THEN
					(
						SELECT
							bpas_return_sales.note
						FROM
							bpas_return_sales
						WHERE
							bpas_return_sales.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES-RETURN' THEN
					(
						SELECT
							bpas_return_purchases.note
						FROM
							bpas_return_purchases
						WHERE
							bpas_return_purchases.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'DELIVERY' THEN
					(
						SELECT
							bpas_deliveries.note
						FROM
							bpas_deliveries
						WHERE
							bpas_deliveries.do_reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				ELSE
					''
				END
			) AS note,
			bpas_gl_trans.account_code,
			bpas_gl_charts.accountname,
			bpas_gl_trans.amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE
			bpas_gl_trans.account_code = '$code'
			AND	bpas_gl_trans.sectionid IN ($section)
			$where_biller 
			$where_date
		HAVING amount <> 0
		");
		return $query;
	}
	
	function getBalanceSheetDetailPurByAccCode($code = NULL, $section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL) {
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
			AND '$to_date' ";
		}
		$query = $this->db->query("SELECT
			bpas_gl_trans.tran_type,
			bpas_gl_trans.tran_date,
			bpas_gl_trans.reference_no,
			(
				CASE
				WHEN bpas_gl_trans.tran_type = 'SALES' THEN
					(
						SELECT
							bpas_sales.customer
						FROM
							bpas_sales
						WHERE
							bpas_gl_trans.reference_no = bpas_sales.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES' OR bpas_gl_trans.tran_type = 'PURCHASE EXPENSE' THEN
					(
						SELECT
							bpas_purchases.supplier
						FROM
							bpas_purchases
						WHERE
							bpas_gl_trans.reference_no = bpas_purchases.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'SALES-RETURN' THEN
					(
						SELECT
							bpas_return_sales.customer
						FROM
							bpas_return_sales
						WHERE
							bpas_return_sales.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES-RETURN' THEN
					(
						SELECT
							bpas_return_purchases.supplier
						FROM
							bpas_return_purchases
						WHERE
							bpas_return_purchases.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'DELIVERY' THEN
					(
						SELECT
							bpas_deliveries.customer
						FROM
							bpas_deliveries
						WHERE
							bpas_deliveries.do_reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'USING STOCK' THEN
					(
						SELECT
							bpas_companies.name
						FROM
							bpas_enter_using_stock
						INNER JOIN bpas_companies ON bpas_companies.id = bpas_enter_using_stock.employee_id
						WHERE
							bpas_enter_using_stock.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'STOCK_ADJUST' THEN
					(
						SELECT
							'' AS customer
						FROM
							bpas_adjustments
						WHERE
							bpas_adjustments.id = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				ELSE
					''
				END
			) AS customer,
			(
				CASE
				WHEN bpas_gl_trans.tran_type = 'SALES' THEN
					(
						SELECT
							bpas_sales.note
						FROM
							bpas_sales
						WHERE
							bpas_gl_trans.reference_no = bpas_sales.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES' OR bpas_gl_trans.tran_type = 'PURCHASE EXPENSE' THEN
					(
						SELECT
							bpas_purchases.note
						FROM
							bpas_purchases
						WHERE
							bpas_gl_trans.reference_no = bpas_purchases.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'SALES-RETURN' THEN
					(
						SELECT
							bpas_return_sales.note
						FROM
							bpas_return_sales
						WHERE
							bpas_return_sales.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'PURCHASES-RETURN' THEN
					(
						SELECT
							bpas_return_purchases.note
						FROM
							bpas_return_purchases
						WHERE
							bpas_return_purchases.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'DELIVERY' THEN
					(
						SELECT
							bpas_deliveries.note
						FROM
							bpas_deliveries
						WHERE
							bpas_deliveries.do_reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'USING STOCK' THEN
					(
						SELECT
							bpas_enter_using_stock.note
						FROM
							bpas_enter_using_stock
						WHERE
							bpas_enter_using_stock.reference_no = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				WHEN bpas_gl_trans.tran_type = 'STOCK_ADJUST' THEN
					(
						SELECT
							bpas_adjustments.note
						FROM
							bpas_adjustments
						WHERE
							bpas_adjustments.id = bpas_gl_trans.reference_no
						LIMIT 0,1
					)
				ELSE
					''
				END
			) AS note,
			bpas_gl_trans.account_code,
			bpas_gl_charts.accountname,
			bpas_gl_trans.amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE
			bpas_gl_trans.account_code = '$code'
			AND	bpas_gl_trans.sectionid IN ($section)
			$where_biller 
			$where_date
		HAVING amount <> 0
		");
		return $query;
	}
	
	public function getStatementByBalaneSheetDate($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
		$where_biller = '';
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
		$where_date = '';
		/*if($from_date && $to_date){
			$where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
			AND '$to_date' "; 
		}*/
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) < '$to_date' "; 
        }
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
			bpas_gl_trans.account_code,
			bpas_gl_charts.sectionid,
			bpas_gl_charts.accountname,
			bpas_gl_charts.parent_acc,
			sum(bpas_gl_trans.amount) AS amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE 
			bpas_gl_charts.sectionid IN ($section)
			$where_biller
			$where_date
		GROUP BY
			bpas_gl_trans.account_code
		");

		return $query;
	}
    public function getStatementByBalaneSheetDateRange($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
        $where_biller = '';
        if($biller_id != NULL){
            $where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
        }
        $where_date = '';
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
            AND '$to_date' "; 
        }
       
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $query = $this->db->query("SELECT
            bpas_gl_trans.account_code,
            bpas_gl_charts.sectionid,
            bpas_gl_charts.accountname,
            bpas_gl_charts.parent_acc,
            sum(bpas_gl_trans.amount) AS amount,
            bpas_gl_trans.biller_id
        FROM
            bpas_gl_trans
        INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
        WHERE 
            bpas_gl_charts.sectionid IN ($section)
            $where_biller
            $where_date
        GROUP BY
            bpas_gl_trans.account_code
        ");

        return $query;
    }
    public function getStatementByBalaneSheetDate_current_year($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
        $where_biller = '';
        if($biller_id != NULL){
            $where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
        }
        $where_date = '';
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
            AND '$to_date' "; 
        }
   
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $query = $this->db->query("SELECT
            bpas_gl_trans.account_code,
            bpas_gl_charts.sectionid,
            bpas_gl_charts.accountname,
            bpas_gl_charts.parent_acc,
            sum(bpas_gl_trans.amount) AS amount,
            bpas_gl_trans.biller_id
        FROM
            bpas_gl_trans
        INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
        WHERE 
            bpas_gl_charts.sectionid IN ($section)
            $where_biller
            $where_date
        GROUP BY
            bpas_gl_trans.account_code
        ");

        return $query;
    }
    public function getStatementByBalaneSheetDate_retain($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
        $where_biller = '';
        if($biller_id != NULL){
            $where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
        }
        $where_date = '';
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) < '$from_date' "; 
        }
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $query = $this->db->query("SELECT
            bpas_gl_trans.account_code,
            bpas_gl_charts.sectionid,
            bpas_gl_charts.accountname,
            bpas_gl_charts.parent_acc,
            sum(bpas_gl_trans.amount) AS amount,
            bpas_gl_trans.biller_id
        FROM
            bpas_gl_trans
        INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
        WHERE 
            bpas_gl_charts.sectionid IN ($section)
            $where_biller
            $where_date
        GROUP BY
            bpas_gl_trans.account_code
        ");

        return $query;
    }
	public function getStatementByBalaneSheetDateByCustomer($section = NULL,$from_date= NULL,$to_date = NULL,$customer_id = NULL){
		$where_customer = '';
		if($customer_id != NULL){
			$where_customer = " AND bpas_gl_trans.customer_id IN($customer_id) "; 
		}
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
			AND '$to_date' "; 
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
			bpas_gl_trans.account_code,
			bpas_gl_trans.sectionid,
			bpas_gl_charts.accountname,
			bpas_gl_charts.parent_acc,
			sum(bpas_gl_trans.amount) AS amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE 
			bpas_gl_trans.sectionid IN ($section)
			$where_customer
			$where_date
		GROUP BY
			bpas_gl_trans.account_code
		");

		return $query;
	}
	public function getStatementByDate($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
		$where_biller = '';
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
			AND '$to_date' "; 
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
			bpas_gl_trans.account_code,
			bpas_gl_trans.sectionid,
			bpas_gl_charts.accountname,
			bpas_gl_charts.parent_acc,
			sum(bpas_gl_trans.amount) AS amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE
			bpas_gl_charts.sectionid IN ($section)
			$where_biller
			$where_date
		GROUP BY
			bpas_gl_trans.account_code
		");

		return $query;
	}
	
	public function getStatementBalaneSheetByDateBill($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
		$where_biller = '';
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
        //  $where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'AND '$to_date' ";
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND date(bpas_gl_trans.tran_date) < '$to_date' "; 
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
			bpas_gl_trans.account_code,
			bpas_gl_charts.sectionid,
			bpas_gl_charts.accountname,
			bpas_gl_charts.parent_acc,
			sum(bpas_gl_trans.amount) AS amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE
			bpas_gl_charts.sectionid IN ($section)
			$where_biller
			$where_date
		GROUP BY
			bpas_gl_trans.account_code,
			biller_id
		");

		return $query;
	}
	public function getStatementBalaneSheetByDateBill_retain($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
        $where_biller = '';
        if($biller_id != NULL){
            $where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
        }
        $where_date = '';
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) < '$from_date' "; 
        }
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $query = $this->db->query("SELECT
            bpas_gl_trans.account_code,
            bpas_gl_charts.sectionid,
            bpas_gl_charts.accountname,
            bpas_gl_charts.parent_acc,
            sum(bpas_gl_trans.amount) AS amount,
            bpas_gl_trans.biller_id
        FROM
            bpas_gl_trans
        INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
        WHERE
            bpas_gl_charts.sectionid IN ($section)
            $where_biller
            $where_date
        GROUP BY
            bpas_gl_trans.account_code,
            biller_id
        ");

        return $query;
    }
	public function getStatementByDateBill($section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
		$where_biller = '';
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND bpas_gl_trans.tran_date BETWEEN '$from_date'
			AND '$to_date' "; 
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
			bpas_gl_trans.account_code,
			bpas_gl_trans.sectionid,
			bpas_gl_charts.accountname,
			bpas_gl_charts.parent_acc,
			sum(bpas_gl_trans.amount) AS amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		WHERE
			bpas_gl_trans.sectionid IN ($section)
			$where_biller
			$where_date
		GROUP BY
			bpas_gl_trans.account_code,
			biller_id
		");

		return $query;
	}

	function getStatementDetailByAccCode($code = NULL, $section = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL) {
		if($biller_id != NULL){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
		$where_date = '';
		if($from_date && $to_date){
			$where_date = " AND bpas_gl_trans.tran_date BETWEEN '$from_date'
			AND '$to_date' ";
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
			bpas_gl_trans.tran_type,
			bpas_gl_trans.tran_date,
			bpas_gl_trans.reference_no,
			(CASE WHEN bpas_sales.customer THEN bpas_sales.customer ELSE bpas_purchases.supplier END) AS customer,
			(CASE WHEN bpas_sales.note THEN bpas_sales.note ELSE bpas_purchases.note END) AS note,
			bpas_companies.company,
			bpas_gl_trans.account_code,
			bpas_gl_charts.accountname,
			bpas_gl_trans.amount,
			bpas_gl_trans.biller_id
		FROM
			bpas_gl_trans
		LEFT JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
		LEFT JOIN bpas_companies ON bpas_gl_trans.biller_id = bpas_companies.id
		LEFT JOIN bpas_sales ON bpas_sales.reference_no = bpas_gl_trans.reference_no
		LEFT JOIN bpas_purchases ON bpas_purchases.reference_no = bpas_gl_trans.reference_no
		WHERE
			bpas_gl_trans.account_code = '$code'
			AND	bpas_gl_trans.sectionid IN ($section)
			$where_biller 
			$where_date
		GROUP BY
			bpas_sales.reference_no,
			bpas_gl_trans.account_code
		HAVING amount <> 0
		");
		return $query;
	}
	
	public function getMonthlyIncomes($excep_acccode = NULL, $section = NULL,$from_date, $to_date, $biller_id = NULL)
	{
		$where_biller = '';
		$where_year = '';
		$where_date = '';
		$where_except_code = '';
		if($biller_id){
			$where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
		}
		if(!$year){
			$year = date('Y');
		}
		if($from_date && $to_date){
			$where_date = " AND gl.tran_date BETWEEN '$from_date'
			AND '$to_date' "; 
		}
		if($excep_acccode){
			$where_except_code = " AND gl.account_code NOT IN($excep_acccode) ";
		}
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$query = $this->db->query("SELECT
									DATE_FORMAT('$from_date','%Y') AS year,
									bpas_gl_trans.biller_id,
									bpas_companies.code,
									bpas_companies.company,
									bpas_companies.name,
									COALESCE(bpas_companies.amount, 0) AS total_amount,
									bpas_companies.period,
									bpas_companies.start_date,
									bpas_companies.end_date,
                                    bpas_companies.begining_balance,
									bpas_gl_trans.account_code,
									bpas_gl_trans.sectionid,
									bpas_gl_charts.accountname,
									bpas_gl_charts.parent_acc,
									COALESCE(january.amount, 0) AS jan,
									COALESCE(febuary.amount, 0) AS feb,
									COALESCE(march.amount, 0) AS mar,
									COALESCE(april.amount, 0) AS apr,
									COALESCE(may.amount, 0) AS may,
									COALESCE(june.amount, 0) AS jun,
									COALESCE(july.amount, 0) AS jul,
									COALESCE(august.amount, 0) AS aug,
									COALESCE(september.amount, 0) AS sep,
									COALESCE(october.amount, 0) AS oct,
									COALESCE(november.amount, 0) AS nov,
									COALESCE(december.amount, 0) AS dece,
									(
										COALESCE(january.amount,0) + COALESCE(febuary.amount,0) + COALESCE(march.amount,0) + COALESCE(april.amount,0) + COALESCE(may.amount,0) + COALESCE(june.amount,0) + COALESCE(july.amount,0) + COALESCE(august.amount,0) + COALESCE(september.amount,0) + COALESCE(october.amount,0) + COALESCE(november.amount,0) + COALESCE(december.amount,0)
									) AS total
								FROM
									bpas_companies
								LEFT JOIN bpas_gl_trans ON bpas_companies.id = bpas_gl_trans.biller_id
								LEFT JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '01'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS january ON january.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '02'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS febuary ON febuary.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '03'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS march ON march.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '04'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS april ON april.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '05'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS may ON may.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '06'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS june ON june.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '07'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS july ON july.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '08'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS august ON august.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '09'
									AND gl.sectionid IN (40, 70)
									AND gl.account_code = '$acc_code' 
									$where_date
									GROUP BY
										gl.biller_id
								) AS september ON september.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '10'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS october ON october.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '11'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_date
									GROUP BY
										gl.biller_id
								) AS november ON november.biller_id = bpas_companies.id
								LEFT JOIN (
									SELECT
										COALESCE(SUM(gl.amount),0) AS amount,
										gl.biller_id
									FROM
										bpas_gl_trans gl
									WHERE
										MONTH (gl.tran_date) = '12'
									AND	gl.sectionid IN ($section)
									$where_except_code
									$where_dates
									GROUP BY
										gl.biller_id
								) AS december ON december.biller_id = bpas_companies.id
								WHERE
									1 = 1
								AND bpas_companies.group_name = 'biller'
								$where_biller
								GROUP BY
									bpas_companies.id
								ORDER BY bpas_companies.id
		");
		return $query;
	}
	
	public function addJournals($rows)
	{		
		 if (!empty($rows)) {
			foreach($rows as $row){
					$this->db->insert('gl_trans', $row);
			}
		  return true;
		}
        return false;
    }
	
	public function addCharts($data = array())
	{
        if ($this->db->insert_batch('gl_charts', $data)) {
            return true;
        }
        return false;
    }
	
	public function getSectionIdByCode($code)
	{
        $q = $this->db->get_where('gl_charts', array('accountcode' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->sectionid;
        }
        return FALSE;
    }
	
	public function getAccountCode($accountcode)
	{
		$this->db->select('accountcode');
		$q = $this->db->get_where('gl_charts', array('accountcode' => $accountcode), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
	}
	
	public function getConditionTax()
	{
		$this->db->where('id','1');
		$q=$this->db->get('condition_tax');
		return $q->result();
	}
	
	public function getConditionTaxById($id)
	{
		$this->db->where('id',$id);
		$q=$this->db->get('condition_tax');
		return $q->row();
	}
	
	public function update_exchange_tax_rate($id,$data)
	{
		$this->db->where('id',$id);
		$update=$this->db->update('condition_tax',$data);
		if($update){
			return true;
		}
	} 
	
	public function getKHM()
	{
		$q = $this->db->get_where('currencies', array('code'=> 'KHM'), 1);
		if($q->num_rows() > 0){
			$q = $q->row();
            return $q->rate;
		}
	}
	
	public function addConditionTax($data)
	{
		if ($this->db->insert('condition_tax', $data)) 
		{
            return true;
        }
        return false;
	}
	
	public function deleteConditionTax($id)
	{
		$q = $this->db->delete('condition_tax', array('id' => $id));
		if($q){
			return true;
		} else{
			return false;
		}
	}
	
	public function getCustomersDepositByCustomerID($customer_id)
	{
		$q = $this->db
    		->select("deposits.id as dep_id, companies.id AS id , date,companies.name, companies.deposit_amount AS amount, paid_by, CONCAT(bpas_users.first_name, ' ', bpas_users.last_name) as created_by", false)
    		->from("deposits")
    		->join('users', 'users.id=deposits.created_by', 'inner')
    		->join('companies', 'deposits.company_id = companies.id', 'inner')
    		->where('deposits.amount <>', 0)
			->where('companies.id', $customer_id)
			->get();
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function ar_by_customer($start_date = null, $end_date = null, $customer2 = null, $balance2 = null, $condition = null, $sale_id = null)
    {
        $w = '';
        if($start_date){
            $w .= " AND (bpas_sales.date) >= '".$start_date." 00:00:00'";
        }
        if($end_date){
            $w .= " AND (bpas_sales.date) <= '".$end_date."23:59:00' ";
        }
        if($customer2){
            $w .= " AND bpas_sales.customer_id = '".$customer2."' ";
        }       
        if(!$balance2){
            $balance2 = "all";
        }
        if($balance2 == "balance0"){
            $w .= " AND bpas_sales.grand_total <= 0 ";
        }
        if($balance2 == "owe"){
            $w .= " AND bpas_sales.grand_total > 0 ";
        }
        if ($condition=='customer') {
            $q = $this->db
            ->select("
                sales.customer,
                sales.customer_id,
                '".$start_date."' AS start_date,
                '".$end_date."' AS end_date,
                '".$balance2."' AS balance ", false)
            ->from("sales")
            ->where("1 = 1 ".$w."")
            ->group_by("customer_id")
            ->order_by("customer", "asc")
            ->get();
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
        } elseif ($condition=='detail') {
            $q = $this->db
            ->select("sales.customer_id,
                        sales.id,
                        sales.customer,
                        sales.customer_id,
                        sales.reference_no,
                        sales.date,
                        sales.grand_total,
                        0 as order_discount,
                        bpas_returns.amount as amount_return,
                        bpas_deposits.amount as amount_deposit
                    ", false)
            ->from("sales")
            ->join("payments bpas_returns","bpas_returns.sale_id = sales.id AND bpas_returns.return_id<>''","left")
            ->join("payments bpas_deposits","bpas_deposits.sale_id = sales.id AND bpas_deposits.deposit_id<>''","left")
            ->where("1 = 1 ".$w."")
            ->group_by("sales.reference_no")
            ->order_by("sales.reference_no", "desc")
            ->get();                
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
        } elseif($condition=='payment') {
            $q = $this->db
            ->select("payments.amount,
                      payments.reference_no,
                      payments.date
                    ", false)
            ->from("payments")
            ->where("payments.sale_id<>", "")
            ->where("payments.sale_id=", $sale_id)
            ->get();
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
        }
    }
	
	public function ap_by_supplier($start_date = null, $end_date = null, $supplier2 = null, $balance2 = null, $condition = null, $purchase_id = null)
    {
        $w = '';
        if($start_date){
            $w .= " AND (bpas_purchases.date) >= '".$start_date." 00:00:00'";
        }
        if($end_date){
            $w .= " AND (bpas_purchases.date) <= '".$end_date."23:59:00' ";
        }
        if($supplier2){
            $w .= " AND bpas_purchases.supplier_id = '".$supplier2."' ";
        }
        if(!$balance2){
            $balance = "all";
        }
        if($balance2 == "balance0"){
            $w .= " AND (bpas_purchases.grand_total - bpas_purchases.paid) = 0 ";
        }
        if($balance2 == "owe"){
            $w .= " AND (bpas_purchases.grand_total - bpas_purchases.paid) != 0 ";
        }
        if($condition=='supplier'){
            $q = $this->db->select("
                    purchases.supplier_id,
                    purchases.supplier,
                    companies.address,
                    '".$start_date."' AS start_date,
                    '".$end_date."' AS end_date,
                    '".$balance2."' AS balance ", false)
            ->from("purchases")
            ->join('companies', 'companies.id=purchases.supplier_id', 'left')
            ->where("1 = 1 ".$w."")
            ->group_by("supplier_id")
            ->order_by("supplier", "asc")
            ->get();
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
        } elseif ($condition=='detail'){
            $q = $this->db->select("
                        purchases.id,
                        purchases.supplier_id,
                        purchases.supplier,
                        purchases.reference_no,
                        purchases.date,
                        purchases.grand_total,
                        0 as order_discount,
                        bpas_returns.amount as amount_return,
                        bpas_deposits.amount as amount_deposit", false)
            ->from("purchases")
            ->join("payments bpas_returns","bpas_returns.purchase_id = purchases.id AND bpas_returns.purchase_return_id<>''","left")
            ->join("payments bpas_deposits","bpas_deposits.purchase_id = purchases.id AND bpas_deposits.purchase_deposit_id<>''","left")
            ->where("1 = 1 ".$w."")
            // ->group_by("purchases.reference_no")
            ->order_by("purchases.reference_no", "desc")
            ->get();
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
        } elseif ($condition=='payment'){
            $q = $this->db->select("payments.amount,
                      payments.reference_no,
                      payments.date
                    ", false)
            ->from("payments")
            ->where("payments.purchase_id<>", "")
            ->where("payments.purchase_id=", $purchase_id)
            ->get();
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
        }
    }
	
	public function increaseTranNo()
	{
		$q = $this->db->get_where('bpas_order_ref',array("DATE_FORMAT(date,'%Y-%m')"=>date('Y-m')),1);
		if($q->num_rows() > 0){
				return $q->row()->tr;
			}
			return false;
	}
	
	public function UpdateincreaseTranNo($tr)
	{
		$q = $this->db->update('bpas_order_ref',array('tr'=>$tr),array("DATE_FORMAT(date,'%Y-%m')"=>date('Y-m')));
		if($q){
				return true;
		}
		return false;
	}

    public function checkrefer($id)
	{
        $q = $this->db->get_where('bpas_sales',array('id'=>$id),1);
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
	
	public function checkreferPur($id)
	{
        $q = $this->db->get_where('bpas_purchases',array('id'=>$id),1);
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }

	public function deleteGltranByAccount($ids = false){
		if($ids){
			$result = $this->db->where_in("tran_id",$ids)->delete("gl_trans");
			if($result) {
				$this->db->where_in("transaction_id",$ids)->delete("payments");
			}
			return $result;
		}
		return false;
	}
	public function ar_by_customerV2($start_date=null, $end_date= null, $customer = null)
    {
		$this->db->select("
                sales.customer_id, 
                sales.customer, 
                companies.phone, 
                companies.address", false);
        $this->db->from("sales");
        $this->db->join("companies", 'companies.id=sales.customer_id', 'left');
           
		if($start_date && $end_date){
		    $this->db->where('sales.date >=',$start_date.' 00:00:00');
            $this->db->where('sales.date <=',$end_date.' 23:59:59');
	    }
		
		$this->db->where('(bpas_sales.grand_total - bpas_sales.paid) != 0');
		
		if($customer){
			$this->db->where('customer_id', $customer);
		}
        $this->db->group_by("customer_id");
        $this->db->order_by("customer", "asc");
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->result();
        }
        return false;
	}
	public function getSaleByCustomerV2($cus_id, $start_date = null, $end_date = null)
    {
		$this->db->select("sales.id, CONCAT(bpas_users.first_name,' ',bpas_users.last_name) as fullname", false)
            ->from("sales")->join("users","users.id=sales.saleman_by","LEFT");
			
			$this->db->where('customer_id', $cus_id);
            if($start_date && $end_date){
                $this->db->where('sales.date >=',$start_date.' 00:00:00');
                $this->db->where('sales.date <=',$end_date.' 23:59:59');
            }
            $this->db->where('(bpas_sales.grand_total - bpas_sales.paid) != 0');
            $this->db->order_by("date", "asc");
            $q = $this->db->get();
            if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
	}
	public function getSaleBySID($id)
    {
            $q = $this->db->get_where("sales",array('id'=>$id),1);
            if($q->num_rows() > 0){
                return $q->row();
            }
            return false;
	}
    public function ap_by_supplierV2($start_date=null, $end_date= null, $supplier = null, $balance = null)
    {
        $this->db->select("purchases.supplier_id, purchases.supplier, companies.address", false);
        $this->db->from("purchases");
        $this->db->join("companies", 'companies.id=purchases.supplier_id', 'left');
        if($start_date && $end_date){
            $this->db->where('date_format(bpas_purchases.date, "%Y-%m-%d") BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        if($balance == "balance0"){
            $this->db->where('(bpas_purchases.grand_total - bpas_purchases.paid) = 0');
        }
        if($balance == "owe"){
            $this->db->where('(bpas_purchases.grand_total - bpas_purchases.paid) != 0');
        }
        if($supplier){
            $this->db->where('supplier_id', $supplier);
        }
        $this->db->group_by("supplier_id");
        $this->db->order_by("supplier", "asc");
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->result();
        }
        return false;
    }
    public function getPurchaseBySupplierV2($sup_id, $start_date = null, $end_date = null, $balance = null)
    {
        $this->db->select("purchases.id", false)->from("purchases");
        $this->db->where('supplier_id', $sup_id);
        if($start_date && $end_date){
            $this->db->where('date_format(bpas_purchases.date,"%Y-%m-%d") BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        if($balance == "balance0"){
            $this->db->where('(bpas_purchases.grand_total - bpas_purchases.paid) = 0');
        }
        if($balance == "owe"){
            $this->db->where('(bpas_purchases.grand_total - bpas_purchases.paid) != 0');
        }
        $this->db->order_by("date", "asc");
        $q = $this->db->get();
        if($q->num_rows() > 0){
            return $q->result();
        }
        return false;
    }
    public function getPurchaseByPID($id)
    {
        $q = $this->db->get_where("purchases", array('id' => $id), 1);
        if($q->num_rows() > 0){
            return $q->row();
        }
        return false;
    }
	public function getPaymentBySID($id){
		$this->db->select("bpas_payments.*,bpas_companies.company as biller", false);
			$this->db->join("bpas_companies","bpas_companies.id=bpas_payments.biller_id","LEFT");
          $this->db->where('sale_id',$id);
			$q = $this->db->get('bpas_payments');
           if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
	}
	public function getReturnBySID($id){
		$this->db->select("bpas_return_sales.*", false);
          $this->db->where('sale_id',$id);
			$q = $this->db->get('bpas_return_sales');
           if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
	}
    public function getGtByID($id){
        $this->db->select("bpas_return_sales.*", false);
          $this->db->where('sale_id',$id);
            $q = $this->db->get('bpas_return_sales');
           if($q->num_rows() > 0){
                return $q->result();
            }
            return false;
    }
    public function getTranByID($type,$tran_no,$refer_no){
        $this->db->select();
        $this->db->where('tran_type',$type);
        $this->db->where('tran_no',$tran_no);
        $this->db->where('reference_no',$refer_no);
        $q = $this->db->get('gl_trans');
        if($q->num_rows() > 0){
            return $q->row();
        }
        return FALSE;
    }
    public function update_GT($payment_id,$data,$accTranPayments = array()){

        if ($this->db->update('payments', $data, ['id' => $payment_id])) {
            
            if($accTranPayments){
                $this->db->insert_batch('multi_transfer', $accTranPayments);
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
          
            return true;
        }
        return false;
    }
    public function update_transfer($payment_id,$data){

        if ($this->db->update('payments', $data, ['id' => $payment_id])) {
            return true;
        }
        return false;
    }
    public function inset_bankcharge($accTranPayments = array()){
        if($accTranPayments){
            $this->db->insert_batch('multi_transfer', $accTranPayments);
            $this->db->insert_batch('gl_trans', $accTranPayments);
             return true;
        }
        return false;
    }
    public function getCombineByPaymentId($id)
    {

        $this->db->select('*');
        $this->db->from('payments');
        if($id){
            $this->db->where_in('id',$id);
        }
        //$this->db->where('sale_status !=', 'returned');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    public function getTranByTranNo($tran_no){
        $this->db->select('*');
        $this->db->where('tran_no',$tran_no);
        $q = $this->db->get('gl_trans');
        if($q->num_rows() > 0){
            return $q->row();
        }
        return FALSE;
    }
    function getGlById($id){
        $this->db->select('*')->from('gl_trans');
        $this->db->where_in('tran_id', $id);
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return false;
    }
    public function addBankReconsile($data = array(), $items = array(),$items1 = array())
    {
      
        if ($this->db->insert('bank_reconsile', $data)) {
            $bank_reconsile_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['reconsile_id'] = $bank_reconsile_id;
                $this->db->insert('bank_reconsile_items', $item);
            }
            //-----------
             foreach ($items1 as $item1) {
                $item1['reconsile_id'] = $bank_reconsile_id;
                $this->db->insert('bank_reconsile_items', $item1);
            }
            return true;
        }
        return false;
    }
    public function add_depreciation($payment_id,$data,$accTranPayments = array()){

        if ($this->db->update('asset_evaluation', $data, ['id' => $payment_id])) {
            
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
            $this->site->updateReference('dp');
            return true;
        }
        return false;
    }

    public function getReconcile($id=null)
    {
        $this->db->select('*');
        $this->db->where('id',$id);
        $q = $this->db->get('bank_reconsile');
        if($q->num_rows() > 0){
            return $q->row();
        }
        return FALSE;
    }
    public function getReconcileItems($id=null)
    {
         $this->db->select('*');
        $this->db->where('reconsile_id',$id);
        $q = $this->db->get('bank_reconsile_items');
        if($q->num_rows() > 0){
            return $q->result();
        }
        return FALSE;
    }
     public function getCashFlow($type = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
        $where_biller = '';
        if($biller_id != NULL){
            $where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
        }
        $where_date = '';
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
            AND '$to_date' "; 
        }
       
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $query = $this->db->query("SELECT
            bpas_gl_trans.account_code,
            bpas_gl_charts.sectionid,
            bpas_gl_charts.accountname,
            bpas_gl_charts.parent_acc,
            sum(bpas_gl_trans.amount) AS amount,
            bpas_gl_trans.biller_id
        FROM
            bpas_gl_trans
        INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
        WHERE 
            bpas_gl_charts.type = $type
            $where_biller
            $where_date
        GROUP BY
            bpas_gl_trans.account_code
        ");

        return $query;
    }
    public function getBusinessActivity($type = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){
        $where_biller = '';
        if($biller_id != NULL){
            $where_biller = " AND bpas_gl_trans.biller_id IN($biller_id) "; 
        }
        $where_date = '';
        if($from_date && $to_date){
            $where_date = " AND date(bpas_gl_trans.tran_date) BETWEEN '$from_date'
            AND '$to_date' "; 
        }
       
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $query = $this->db->query("SELECT
            bpas_gl_trans.account_code,
            bpas_gl_charts.sectionid,
            bpas_gl_charts.accountname,
            bpas_gl_charts.parent_acc,
            sum(bpas_gl_trans.amount) AS amount,
            bpas_gl_trans.biller_id
        FROM
            bpas_gl_trans
        INNER JOIN bpas_gl_charts ON bpas_gl_charts.accountcode = bpas_gl_trans.account_code
        WHERE 
            bpas_gl_trans.activity_type = $type
            $where_biller
            $where_date
        GROUP BY
            bpas_gl_trans.account_code
        ");

        return $query;
    }

    public function getIncome($type = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){

        $this->db->select('
            payments.sale_id, 
            sales.biller_id,
            SUM(COALESCE(amount, 0)) AS total_amount')
            ->where('payments.sale_id !=', null)
            ->where('payments.date >= "'.$from_date.'" ')
            ->where('payments.date <= "'.$to_date.'"');
        if($biller_id != NULL){
            $this->db->where('sales.biller_id >= ' . $biller_id . '" ');
        }
        $this->db->join("sales","sales.id=payments.sale_id","LEFT");
        
        $q = $this->db->get('payments');

        return $q;
    }
    public function getIncomeBeforeyear($type = NULL,$from_date= NULL,$to_date = NULL,$biller_id = NULL){

        $this->db->select('
            payments.sale_id, 
            sales.biller_id,
            SUM(COALESCE(amount, 0)) AS total_amount')
            ->where('payments.sale_id !=', null)
            ->where('payments.date < "'.$from_date.'" ');
        if($biller_id != NULL){
            $this->db->where('sales.biller_id',$biller_id);
        }
        $this->db->join("sales","sales.id=payments.sale_id","LEFT");
        
        $q = $this->db->get('payments');

        return $q;
    }
    //------account-----------
    public function getAccTranAmounts($begin = false){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['account']) && $post['account']){
                $this->db->where("gl_trans.account_code", $post['account']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
            }
        }else{
            if($begin){
                $this->db->where("DATE(tran_date) <=", date("Y-m-d"));
            }else{
                $this->db->where("DATE(tran_date) =", date("Y-m-d"));
            }
            
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }

        $this->db->select('gl_trans.account_code,
                            gl_sections.nature,
                            sum('.$this->db->dbprefix("gl_trans").'.amount) as amount 
                            ')
                ->from('gl_trans')
                ->join('gl_charts','gl_charts.accountcode = gl_trans.account_code','inner')
                ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
                ->group_by('gl_trans.account_code');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAccTranAmountBillers(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['account']) && $post['account']){
                $this->db->where("gl_trans.account", $post['account']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
            }
        }else{
            $this->db->where("DATE(tran_date) =", date("Y-m-d"));
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }

        $this->db->select('gl_trans.account_code,
                            IFNULL('.$this->db->dbprefix("gl_trans").'.biller_id,0) as biller_id,
                            gl_sections.nature,
                            sum('.$this->db->dbprefix("gl_trans").'.amount) as amount 
                            ')
                ->from('gl_trans')
                ->join('gl_charts','gl_charts.accountcode = gl_trans.account_code','inner')
                ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
                ->group_by('gl_trans.account_code,IFNULL('.$this->db->dbprefix("gl_trans").'.biller_id,0)');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAccTranAmountProjects(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['account']) && $post['account']){
                $this->db->where("gl_trans.account_code", $post['account']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
            }
        }else{
            $this->db->where("DATE(tran_date) =", date("Y-m-d"));
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }

        $this->db->select('gl_trans.account_code,
                            IFNULL('.$this->db->dbprefix("gl_trans").'.project_id,0) as project_id,
                            gl_sections.nature,
                            sum('.$this->db->dbprefix("gl_trans").'.amount) as amount 
                            ')
                ->from('gl_trans')
                ->join('gl_charts','gl_charts.accountcode = gl_trans.account_code','inner')
                ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
                ->group_by('gl_trans.account_code,IFNULL('.$this->db->dbprefix("gl_trans").'.project_id,0)');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAmountRetainEarning(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['biller']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $year = date('Y', strtotime($this->bpas->fsd($post['start_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$year);
            }else if(isset($post['end_date']) && $post['end_date']){
                $year = date('Y', strtotime($this->bpas->fsd($post['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$year);
            }else if(isset($post['year']) && $post['year']){
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$post['year']);
            }
        }else{
            $year = date('Y');
            $this->db->where("DATE_FORMAT(tran_date,'%Y')<",$year);
        }
        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getAmountRetainEarningBillers(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $year = date('Y', strtotime($this->bpas->fsd($post['start_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$year);
            }else if(isset($post['end_date']) && $post['end_date']){
                $year = date('Y', strtotime($this->bpas->fsd($post['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$year);
            }
        }else{
            $year = date('Y');
            $this->db->where("DATE_FORMAT(tran_date,'%Y')<",$year);
        }
        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount,IFNULL('.$this->db->dbprefix("gl_trans").'.biller_id,0) as biller_id')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->group_by('IFNULL('.$this->db->dbprefix("gl_trans").'.biller_id,0)')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAmountRetainEarningProjects(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $year = date('Y', strtotime($this->bpas->fsd($post['start_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$year);
            }else if(isset($post['end_date']) && $post['end_date']){
                $year = date('Y', strtotime($this->bpas->fsd($post['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y') <",$year);
            }
        }else{
            $year = date('Y');
            $this->db->where("DATE_FORMAT(tran_date,'%Y')<",$year);
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        
        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount,IFNULL('.$this->db->dbprefix("gl_trans").'.project_id,0) as project_id')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->group_by('IFNULL('.$this->db->dbprefix("gl_trans").'.project_id,0)')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAmountNetIncome(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
                $year = date('Y', strtotime($this->bpas->fsd($post['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
            }
        }else{
            $this->db->where("DATE(tran_date) <=", date("Y-m-d"));
            $year = date('Y');
            $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
        }
        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
 
    public function getAmountNetIncomeBillers(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
                $year = date('Y', strtotime($this->bpas->fsd($post['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
            }
        }else{
            $this->db->where("DATE(tran_date) <=", date("Y-m-d"));
            $year = date('Y');
            $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
        }
        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount,IFNULL('.$this->db->dbprefix("gl_trans").'.biller_id,0) as biller_id')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->group_by('IFNULL('.$this->db->dbprefix("gl_trans").'.biller_id,0)')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAmountNetIncomeProjects(){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
                $year = date('Y', strtotime($this->bpas->fsd($post['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
            }
        }else{
            $this->db->where("DATE(tran_date) <=", date("Y-m-d"));
            $year = date('Y');
            $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }

        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount,IFNULL('.$this->db->dbprefix("gl_trans").'.project_id,0) as project_id')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->group_by('IFNULL('.$this->db->dbprefix("gl_trans").'.project_id,0)')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAccountSectionsByCode($code = false)
    {
        if($code){
            $this->db->where_in('gl_sections.AccountType', $code);
        }
        $q = $this->db->get('gl_sections');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAccountByParent($parent_code = false){
        $this->db->order_by("gl_charts.accountcode");
        $q = $this->db->get_where("gl_charts", array("parent_acc"=>$parent_code));
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getMainAccountBySection($section_id = false){
        if($section_id){
            $this->db->where('gl_charts.sectionid', $section_id);
        }
        $this->db->where('(gl_charts.parent_acc IS NULL OR gl_charts.parent_acc = 0)');
        $q = $this->db->get('gl_charts');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getAccTranByCode($code = false,$start_date = false,$end_date = false,$begin = false,$biller_id = false,$project_id = false){
        $where = ' 1=1';
        $where_main = '1=1';
        $biller_id = (string)$biller_id;
        $project_id = (string)$project_id;
        if($code){
            $where_main .=' AND ('.$this->db->dbprefix('gl_trans').'.account_code = "'.$code.'")';
        }
        if($begin){
            if($start_date){
                $where .=' AND date('.$this->db->dbprefix('gl_trans').'.tran_date) < "'.$start_date.'"';
            }
        }else{
            if($start_date){
                $where .=' AND date('.$this->db->dbprefix('gl_trans').'.tran_date) >= "'.$start_date.'"';
            }
            if($end_date){
                $where .=' AND date('.$this->db->dbprefix('gl_trans').'.tran_date) <= "'.$end_date.'"';
            }
        }
        if($biller_id || $biller_id=='0'){
            $where .=' AND '.$this->db->dbprefix('gl_trans').'.biller_id IN ('.$biller_id.')';
        }
        
        if($project_id || $project_id=='0'){
            $where .=' AND '.$this->db->dbprefix('gl_trans').'.project_id IN ('.$project_id.')';
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $where .=' AND '.$this->db->dbprefix('gl_trans').'.user_id = "'.$this->session->userdata('user_id').'"';
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where .=' AND '.$this->db->dbprefix('gl_trans').'.biller_id = "'.$this->session->userdata('biller_id').'"';
        }
        
        $q = $this->db->query('SELECT
                                    *
                                FROM
                                    '.$this->db->dbprefix('gl_trans').'
                                WHERE '.$where.' AND '.$where_main.'
                                ORDER BY tran_date,tran_id
                            '); 
        
        // $q = $this->db->query('SELECT
        //                             *
        //                         FROM
        //                             (
        //                                 SELECT
        //                                     '.$this->db->dbprefix('gl_trans_').'.*,
        //                                     '.$this->db->dbprefix('gl_charts').'.accountcode,
        //                                     '.$this->db->dbprefix('gl_charts').'.accountname,
        //                                     '.$this->db->dbprefix('gl_charts').'.parent_acc,
        //                                     '.$this->db->dbprefix('gl_sections').'.nature,
        //                                     SUBSTRING_INDEX(lineage, "/", 1) AS line_age
        //                                 FROM
        //                                     '.$this->db->dbprefix('gl_charts').'
        //                                 INNER JOIN '.$this->db->dbprefix('gl_trans').' ON '.$this->db->dbprefix('gl_trans').'.account_code = '.$this->db->dbprefix('gl_chart').'.accountcode
        //                                 INNER JOIN '.$this->db->dbprefix('gl_sections').' ON '.$this->db->dbprefix('gl_sections').'.sectionid = '.$this->db->dbprefix('gl_chart').'.sectionid
        //                                 WHERE '.$where.'
        //                             ) AS gl_trans
        //                         WHERE '.$where_main.'
        //                         ORDER BY tran_date,id
        //                     '); 
        


        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
        
    }
    public function getAccountByCode($code = false) {
        $q = $this->db->get_where('gl_charts', array('accountcode' => $code), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProjectsByID($projects = false){
        $q = $this->db->query("SELECT * FROM ".$this->db->dbprefix('projects')." WHERE project_id IN(".$projects.")");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getBillersByID($billers = false){
        $q = $this->db->query("SELECT * FROM ".$this->db->dbprefix('companies')." WHERE id IN(".$billers.")");
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getMonthAccTranAmounts($last_year = false, $begin = false, $date = false){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['account']) && $post['account']){
                $this->db->where("gl_trans.account_code", $post['account']);
            }
            if(isset($post['year']) && $post['year']){
                if($begin){
                    $this->db->where("YEAR(tran_date) <=", $post['year']);
                }else if($last_year){
                    $this->db->where("YEAR(tran_date) =", ($post['year'] - 1));
                }else{
                    $this->db->where("YEAR(tran_date) =", $post['year']);
                }
                
            }
            if($date && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bms->fsd($post['end_date']));
            }
        }else{
            if($last_year){
                $this->db->where("YEAR(tran_date) =", (date("Y") - 1));
            }else if($begin){
                $this->db->where("YEAR(tran_date) <=", date("Y"));
            }else{
                $this->db->where("YEAR(tran_date) =", date("Y"));
            }

            if($date){
                $this->db->where("DATE(tran_date) <=", date('Y-m-d'));
            }
            
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }



        $this->db->select('gl_trans.account_code as account,
                            gl_sections.nature,
                            sum('.$this->db->dbprefix("gl_trans").'.amount) as amount,
                            MONTH('.$this->db->dbprefix("gl_trans").'.tran_date) as month,
                            YEAR('.$this->db->dbprefix("gl_trans").'.tran_date) as year
                            ')
                ->from('gl_trans')
                ->join('gl_charts','gl_charts.accountcode = gl_trans.account_code','inner')
                ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner');
        if($last_year){
            $this->db->group_by('gl_trans.account_code');
        }else if($begin){
            $this->db->group_by('gl_trans.account_code,MONTH('.$this->db->dbprefix("gl_trans").'.tran_date),YEAR('.$this->db->dbprefix("gl_trans").'.tran_date)');
        }else{
            $this->db->group_by('gl_trans.account_code,MONTH('.$this->db->dbprefix("gl_trans").'.tran_date)');
        }
        
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getMonthAmountNetIncome($last_year = false)
    {
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['year']) && $post['year']){
                $this->db->where("DATE_FORMAT(tran_date,'%Y')",$post['year']);
            }
            
            if($last_year && isset($post['end_date']) && $post['end_date']){
                $year = date('Y', strtotime($this->bms->fsd($_POST['end_date']))) - 1;
                $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
            }else if(isset($post['end_date']) && $post['end_date']){
                $year = date('Y', strtotime($this->bms->fsd($_POST['end_date'])));
                $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
            }
        } else {
            $year = date('Y');
            if($last_year){
                $year = $year - 1;
            }
            $this->db->where("DATE_FORMAT(tran_date,'%Y')",$year);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        $this->db->where('gl_sections.AccountType IN ("RE","CO","EX","OI","OX")');
        $this->db->select('sum(IFNULL('.$this->db->dbprefix('gl_trans').'.amount,0)) as amount, MONTH(tran_date) as month')
            ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
            ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
            ->from('gl_charts')
            ->group_by('MONTH(tran_date)');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach($q->result() as $row){
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAR_Aging ($warehouse = null, $biller = null, $user = null, $customer = null, $start_date = null, $end_date = null) 
    {
        $condition  = "";
        $condition_ = "";
        if ($warehouse) $condition .= " AND bpas_sales.warehouse_id = {$warehouse} ";
        if ($biller) $condition    .= " AND bpas_sales.biller_id = {$biller} ";
        if ($user) $condition      .= " AND bpas_sales.created_by = {$user} ";
        if ($customer) {
            $condition   .= " AND bpas_sales.customer_id = {$customer} ";
            $condition_  .= " AND bpas_companies.id = {$customer} ";
        }
        
        $q = $this->db->query("
                SELECT 
                    bpas_companies.id AS customer_id,
                    bpas_companies.company AS customer_company,
                    bpas_companies.name AS customer_name,
                    COALESCE(AR_CUR.balance, 0)  AS balance_current,
                    COALESCE(AR_1_30.balance, 0) AS balance_1_30,
                    COALESCE(AR_31_60.balance, 0) AS balance_31_60,
                    COALESCE(AR_61_90.balance, 0) AS balance_61_90,
                    COALESCE(AR_91_OVER.balance, 0) AS balance_91_over,
                    (
                        COALESCE(AR_CUR.balance, 0) +
                        COALESCE(AR_1_30.balance, 0) +
                        COALESCE(AR_31_60.balance, 0) +
                        COALESCE(AR_61_90.balance, 0) +
                        COALESCE(AR_91_OVER.balance, 0) 
                    ) AS total_balance
                FROM bpas_companies 
                LEFT JOIN (
                    SELECT 
                        bpas_sales.customer_id, 
                        bpas_sales.customer,
                        COALESCE(SUM(bpas_sales.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_sales.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_sales.grand_total), 0) - COALESCE(SUM(bpas_sales.paid), 0)) AS balance,
                        COUNT(bpas_sales.id) AS ar_number
                    FROM bpas_sales
                    WHERE 
                        bpas_sales.payment_status != 'paid' AND 
                        bpas_sales.payment_status != 'Returned' AND 
                        (COALESCE(bpas_sales.grand_total, 0) - COALESCE(bpas_sales.paid, 0)) <> 0 AND
                        DATE(bpas_sales.date) = CURDATE() 
                        {$condition}
                    GROUP BY bpas_sales.customer_id
                    ORDER BY bpas_sales.customer_id
                ) AR_CUR ON bpas_companies.id = AR_CUR.customer_id
                LEFT JOIN (
                    SELECT 
                        bpas_sales.customer_id, 
                        bpas_sales.customer,
                        COALESCE(SUM(bpas_sales.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_sales.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_sales.grand_total), 0) - COALESCE(SUM(bpas_sales.paid), 0)) AS balance,
                        COUNT(bpas_sales.id) AS ar_number
                    FROM bpas_sales
                    WHERE 
                        bpas_sales.payment_status != 'paid' AND 
                        bpas_sales.payment_status != 'Returned' AND
                        (COALESCE(bpas_sales.grand_total, 0) - COALESCE(bpas_sales.paid, 0)) <> 0 AND
                        (DATE(bpas_sales.date) BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() - INTERVAL 1 DAY)
                        {$condition}
                    GROUP BY bpas_sales.customer_id
                    ORDER BY bpas_sales.customer_id
                ) AR_1_30 ON bpas_companies.id = AR_1_30.customer_id
                LEFT JOIN (
                    SELECT 
                        bpas_sales.customer_id, 
                        bpas_sales.customer,
                        COALESCE(SUM(bpas_sales.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_sales.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_sales.grand_total), 0) - COALESCE(SUM(bpas_sales.paid), 0)) AS balance,
                        COUNT(bpas_sales.id) AS ar_number
                    FROM bpas_sales
                    WHERE 
                        bpas_sales.payment_status != 'paid' AND 
                        bpas_sales.payment_status != 'Returned' AND
                        (COALESCE(bpas_sales.grand_total, 0) - COALESCE(bpas_sales.paid, 0)) <> 0 AND
                        (DATE(bpas_sales.date) BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 31 DAY)
                        {$condition}
                    GROUP BY bpas_sales.customer_id
                    ORDER BY bpas_sales.customer_id
                ) AR_31_60 ON bpas_companies.id = AR_31_60.customer_id
                LEFT JOIN (
                    SELECT 
                        bpas_sales.customer_id, 
                        bpas_sales.customer,
                        COALESCE(SUM(bpas_sales.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_sales.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_sales.grand_total), 0) - COALESCE(SUM(bpas_sales.paid), 0)) AS balance,
                        COUNT(bpas_sales.id) AS ar_number
                    FROM bpas_sales
                    WHERE 
                        bpas_sales.payment_status != 'paid' AND 
                        bpas_sales.payment_status != 'Returned' AND
                        (COALESCE(bpas_sales.grand_total, 0) - COALESCE(bpas_sales.paid, 0)) <> 0 AND
                        (DATE(bpas_sales.date) BETWEEN CURDATE() - INTERVAL 90 DAY AND CURDATE() - INTERVAL 61 DAY)
                        {$condition}
                    GROUP BY bpas_sales.customer_id
                    ORDER BY bpas_sales.customer_id
                ) AR_61_90 ON bpas_companies.id = AR_61_90.customer_id
                LEFT JOIN (
                    SELECT 
                        bpas_sales.customer_id, 
                        bpas_sales.customer,
                        COALESCE(SUM(bpas_sales.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_sales.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_sales.grand_total), 0) - COALESCE(SUM(bpas_sales.paid), 0)) AS balance,
                        COUNT(bpas_sales.id) AS ar_number
                    FROM bpas_sales
                    WHERE 
                        bpas_sales.payment_status != 'paid' AND 
                        bpas_sales.payment_status != 'Returned' AND
                        (COALESCE(bpas_sales.grand_total, 0) - COALESCE(bpas_sales.paid, 0)) <> 0 AND
                        (DATE(bpas_sales.date) BETWEEN CURDATE() - INTERVAL 10000 DAY AND CURDATE() - INTERVAL 91 DAY)
                        {$condition}
                    GROUP BY bpas_sales.customer_id
                    ORDER BY bpas_sales.customer_id
                ) AR_91_OVER ON bpas_companies.id = AR_91_OVER.customer_id
                WHERE 
                    bpas_companies.group_name = 'customer' AND
                    (
                        COALESCE(AR_CUR.balance, 0)     != 0 OR
                        COALESCE(AR_1_30.balance, 0)    != 0 OR
                        COALESCE(AR_31_60.balance, 0)   != 0 OR
                        COALESCE(AR_61_90.balance, 0)   != 0 OR
                        COALESCE(AR_91_OVER.balance, 0) != 0
                    ) 
                    {$condition_}
                ORDER BY bpas_companies.id;
            ");

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAP_Aging ($warehouse = null, $biller = null, $user = null, $supplier = null, $start_date = null, $end_date = null) 
    {
        $condition  = "";
        $condition_ = "";
        if ($warehouse) $condition .= " AND bpas_purchases.warehouse_id = {$warehouse} ";
        if ($biller) $condition    .= " AND bpas_purchases.biller_id = {$biller} ";
        if ($user) $condition      .= " AND bpas_purchases.created_by = {$user} ";
        if ($supplier) {
            $condition   .= " AND bpas_purchases.supplier_id = {$supplier} ";
            $condition_  .= " AND bpas_companies.id = {$supplier} ";
        }
        
        $q = $this->db->query("
                SELECT 
                    bpas_companies.id AS supplier_id,
                    bpas_companies.company AS supplier_company,
                    bpas_companies.name AS supplier_name,
                    COALESCE(AP_CUR.balance, 0)  AS balance_current,
                    COALESCE(AP_1_30.balance, 0) AS balance_1_30,
                    COALESCE(AP_31_60.balance, 0) AS balance_31_60,
                    COALESCE(AP_61_90.balance, 0) AS balance_61_90,
                    COALESCE(AP_91_OVER.balance, 0) AS balance_91_over,
                    (
                        COALESCE(AP_CUR.balance, 0) +
                        COALESCE(AP_1_30.balance, 0) +
                        COALESCE(AP_31_60.balance, 0) +
                        COALESCE(AP_61_90.balance, 0) +
                        COALESCE(AP_91_OVER.balance, 0) 
                    ) AS total_balance
                FROM bpas_companies 
                LEFT JOIN (
                    SELECT 
                        bpas_purchases.supplier_id, 
                        bpas_purchases.supplier,
                        COALESCE(SUM(bpas_purchases.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_purchases.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_purchases.grand_total), 0) - COALESCE(SUM(bpas_purchases.paid), 0)) AS balance,
                        COUNT(bpas_purchases.id) AS ap_number
                    FROM bpas_purchases
                    WHERE 
                        bpas_purchases.payment_status != 'paid' AND 
                        (COALESCE(bpas_purchases.grand_total, 0) - COALESCE(bpas_purchases.paid, 0)) <> 0 AND
                        DATE(bpas_purchases.date) = CURDATE() 
                        {$condition}
                    GROUP BY bpas_purchases.supplier_id
                    ORDER BY bpas_purchases.supplier_id
                ) AP_CUR ON bpas_companies.id = AP_CUR.supplier_id
                LEFT JOIN (
                    SELECT 
                        bpas_purchases.supplier_id, 
                        bpas_purchases.supplier,
                        COALESCE(SUM(bpas_purchases.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_purchases.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_purchases.grand_total), 0) - COALESCE(SUM(bpas_purchases.paid), 0)) AS balance,
                        COUNT(bpas_purchases.id) AS ap_number
                    FROM bpas_purchases
                    WHERE 
                        bpas_purchases.payment_status != 'paid' AND 
                        (COALESCE(bpas_purchases.grand_total, 0) - COALESCE(bpas_purchases.paid, 0)) <> 0 AND
                        (DATE(bpas_purchases.date) BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE() - INTERVAL 1 DAY)
                        {$condition}
                    GROUP BY bpas_purchases.supplier_id
                    ORDER BY bpas_purchases.supplier_id
                ) AP_1_30 ON bpas_companies.id = AP_1_30.supplier_id
                LEFT JOIN (
                    SELECT 
                        bpas_purchases.supplier_id, 
                        bpas_purchases.supplier,
                        COALESCE(SUM(bpas_purchases.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_purchases.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_purchases.grand_total), 0) - COALESCE(SUM(bpas_purchases.paid), 0)) AS balance,
                        COUNT(bpas_purchases.id) AS ap_number
                    FROM bpas_purchases
                    WHERE 
                        bpas_purchases.payment_status != 'paid' AND 
                        (COALESCE(bpas_purchases.grand_total, 0) - COALESCE(bpas_purchases.paid, 0)) <> 0 AND
                        (DATE(bpas_purchases.date) BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE() - INTERVAL 31 DAY)
                        {$condition}
                    GROUP BY bpas_purchases.supplier_id
                    ORDER BY bpas_purchases.supplier_id
                ) AP_31_60 ON bpas_companies.id = AP_31_60.supplier_id
                LEFT JOIN (
                    SELECT 
                        bpas_purchases.supplier_id, 
                        bpas_purchases.supplier,
                        COALESCE(SUM(bpas_purchases.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_purchases.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_purchases.grand_total), 0) - COALESCE(SUM(bpas_purchases.paid), 0)) AS balance,
                        COUNT(bpas_purchases.id) AS ap_number
                    FROM bpas_purchases
                    WHERE 
                        bpas_purchases.payment_status != 'paid' AND 
                        (COALESCE(bpas_purchases.grand_total, 0) - COALESCE(bpas_purchases.paid, 0)) <> 0 AND
                        (DATE(bpas_purchases.date) BETWEEN CURDATE() - INTERVAL 90 DAY AND CURDATE() - INTERVAL 61 DAY)
                        {$condition}
                    GROUP BY bpas_purchases.supplier_id
                    ORDER BY bpas_purchases.supplier_id
                ) AP_61_90 ON bpas_companies.id = AP_61_90.supplier_id
                LEFT JOIN (
                    SELECT 
                        bpas_purchases.supplier_id, 
                        bpas_purchases.supplier,
                        COALESCE(SUM(bpas_purchases.grand_total), 0) AS grand_total,
                        COALESCE(SUM(bpas_purchases.paid), 0) AS paid,
                        (COALESCE(SUM(bpas_purchases.grand_total), 0) - COALESCE(SUM(bpas_purchases.paid), 0)) AS balance,
                        COUNT(bpas_purchases.id) AS ap_number
                    FROM bpas_purchases
                    WHERE 
                        bpas_purchases.payment_status != 'paid' AND 
                        (COALESCE(bpas_purchases.grand_total, 0) - COALESCE(bpas_purchases.paid, 0)) <> 0 AND
                        (DATE(bpas_purchases.date) BETWEEN CURDATE() - INTERVAL 10000 DAY AND CURDATE() - INTERVAL 91 DAY)
                        {$condition}
                    GROUP BY bpas_purchases.supplier_id
                    ORDER BY bpas_purchases.supplier_id
                ) AP_91_OVER ON bpas_companies.id = AP_91_OVER.supplier_id
                WHERE 
                    bpas_companies.group_name = 'supplier' AND
                    (
                        COALESCE(AP_CUR.balance, 0)     != 0 OR
                        COALESCE(AP_1_30.balance, 0)    != 0 OR
                        COALESCE(AP_31_60.balance, 0)   != 0 OR
                        COALESCE(AP_61_90.balance, 0)   != 0 OR
                        COALESCE(AP_91_OVER.balance, 0) != 0
                    ) 
                    {$condition_}
                ORDER BY bpas_companies.id;
            ");

        if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
     //-------------------cashflow------------------
    public function getAllCashflows()
    {
        $q = $this->db->get('acc_cash_flow');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getPaymentReceived($getPrevious=null){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if($getPrevious){
                $this->db->where("DATE(tran_date) <", $this->bpas->fsd($post['start_date']));
            }else{
                if($post['start_date']){
                    $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
                }
                if($post['end_date']){
                    $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
                }
            }
            
        }else{
            if($getPrevious){
                $this->db->where("DATE(tran_date) <", date("Y-m-d"));
            }else{
                $this->db->where("DATE(tran_date)", date("Y-m-d"));
            }   
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        //$this->db->where('gl_sections.code IN ("RE","CO","EX","OI","OX")');
        $this->db->where('gl_charts.bank',1);
        //$this->db->where('gl_trans.amount >',0);

        $this->db->select('IFNULL(sum('.$this->db->dbprefix('gl_trans').'.amount),0) as amount')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getNETIncome($getPrevious=null){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if($getPrevious){
                $this->db->where("DATE(tran_date) <", $this->bpas->fsd($post['start_date']));
            }else{
                if($post['start_date']){
                    $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
                }
                if($post['end_date']){
                    $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
                }
            }
            
        }else{
            if($getPrevious){
                $this->db->where("DATE(tran_date) <", date("Y-m-d"));
            }else{
                $this->db->where("DATE(tran_date)", date("Y-m-d"));
            }   
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        $this->db->where('gl_sections.code IN ("RE","CO","EX","OI","OX")');
        
        $this->db->select('IFNULL(sum('.$this->db->dbprefix('gl_trans').'.amount),0) as amount')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getLastAmountByCashFlow(){
        $post = $this->input->post();
        $where = '';
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $biller_multi = '';
                for($i=0; $i < count($post['biller']); $i++){
                    if($i==0){
                        $biller_multi .= $post['biller'][$i];
                    }else{
                        $biller_multi .= ','.$post['biller'][$i];
                    }
                }
                $where .=' AND '.$this->db->dbprefix('gl_trans').'.biller_id IN ('.$biller_multi.')';
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $project_multi = '';
                for($i=0; $i < count($post['project_multi']); $i++){
                    if($i==0){
                        $project_multi .= $post['project_multi'][$i];
                    }else{
                        $project_multi .= ','.$post['project_multi'][$i];
                    }
                }
                $where .=' AND '.$this->db->dbprefix('gl_trans').'.project_id IN ('.$project_multi.')';
            }
            if($post['start_date']){
                $where .=' AND DATE('.$this->db->dbprefix('gl_trans').'.tran_date) < "'.$this->bpas->fsd($post['start_date']).'"';
            }else{
                $where .=' AND DATE('.$this->db->dbprefix('gl_trans').'.tran_date) < "'.date("Y-m-d").'"';
            }
        }else{
            $where .=' AND DATE('.$this->db->dbprefix('gl_trans').'.tran_date) < "'.date("Y-m-d").'"';
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $where .=' AND '.$this->db->dbprefix('gl_trans').'.biller_id = "'.$this->session->userdata('biller_id').'"';
        }
        
        $q = $this->db->query('SELECT
                                sum(amount * nature) AS amount
                            FROM
                                (
                                    SELECT

                                    IF (
                                        '.$this->db->dbprefix('gl_sections').'.`code` = "LI"
                                        OR '.$this->db->dbprefix('gl_sections').'.`code` = "EQ",
                                        (

                                            IF (
                                                '.$this->db->dbprefix('gl_charts').'.nature = "debit",
                                                1,
                                                - 1
                                            )
                                        ) * (- 1),

                                    IF (
                                        '.$this->db->dbprefix('gl_charts').'.nature = "debit",
                                        1,
                                        - 1
                                    )
                                    ) AS nature,
                                    sum('.$this->db->dbprefix('gl_trans').'.amount) AS amount
                                FROM
                                    '.$this->db->dbprefix('gl_charts').'
                                INNER JOIN '.$this->db->dbprefix('gl_sections').' ON '.$this->db->dbprefix('gl_sections').'.sectionid = '.$this->db->dbprefix('gl_charts').'.sectionid
                                INNER JOIN '.$this->db->dbprefix('gl_trans').' ON '.$this->db->dbprefix('gl_trans').'.account_code = '.$this->db->dbprefix('gl_charts').'.accountcode
                                WHERE
                                    '.$this->db->dbprefix('gl_charts').'.cash_flow != "0"
                                '.$where.'  
                                GROUP BY
                                    '.$this->db->dbprefix('gl_charts').'.accountcode
                                ) AS lastCashFlow');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getAmountByCashFlow($cash_flow = false){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
            }
        }else{
            $this->db->where("DATE(tran_date)", date("Y-m-d"));
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        
       $this->db->where('gl_charts.cash_flow',$cash_flow);
        
        $this->db->select('gl_charts.`accountcode` as code,
                            gl_charts.`accountname` as name,
                            IF('.$this->db->dbprefix('gl_sections').'.`code`= "LI" OR '.$this->db->dbprefix('gl_sections').'.`code`= "EQ",
                            (IF ('.$this->db->dbprefix('gl_charts').'.nature = "debit", 1 ,- 1))*(-1),
                            IF ('.$this->db->dbprefix('gl_charts').'.nature = "debit", 1 ,- 1)) as nature,
                            sum(case when amount >0 then amount else 0 end) as total_debit,
                            sum(case when amount <0 then amount else 0 end) as total_credit,
                            sum('.$this->db->dbprefix('gl_trans').'.amount) AS amount
                    ')
        ->join('gl_sections','gl_sections.sectionid = gl_charts.sectionid','inner')
        ->join('gl_trans','gl_trans.account_code = gl_charts.accountcode','inner')
        ->group_by('gl_charts.accountcode')
        ->order_by('gl_charts.accountcode')
        ->from('gl_charts');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function getEnterJournalByID($id = false)
    {
        $q = $this->db->get_where('account_journals', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getEnterJournalItems($journal_id = false)
    {
        $this->db->select('account_journal_items.*')
            ->group_by('account_journal_items.id')
            ->order_by('amount', 'desc');

        $this->db->where('journal_id', $journal_id);

        $q = $this->db->get('account_journal_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    public function addEnterJournal($data = array(), $items = array(), $accTrans= array())
    {
        if ($this->db->insert('account_journals',$data)) {
            $journal_id = $this->db->insert_id();
            if($items){
                foreach($items as $item){
                    $item['journal_id'] = $journal_id;
                    $this->db->insert('account_journal_items',$item);
                }
            }
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $journal_id;
                    $this->db->insert('gl_trans',$accTran);
                }
            }
            return true;
        }
        return false;
    }
    public function updateEnterJournal($id = false, $data = false, $items = false, $accTrans = false)
    {
        if ($this->db->update('account_journals', $data, array('id' => $id))){
            $this->db->delete('account_journal_items', array('journal_id' => $id));
            $this->site->deleteAccTran('EnterJournal',$id);
            if($items){
                $this->db->insert_batch('account_journal_items',$items);
            }
            if($accTrans){
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            return true;
        }
        return false;
    }
    public function deleteEnterJournal($id = false)
    {
        if($id && $id > 0){
            $enter_journal = $this->getEnterJournalByID($id);
            if ($this->db->delete('account_journals', array('id' => $id))) {
                $this->db->delete('account_journal_items', array('journal_id' => $id));
                $this->site->deleteAccTran('EnterJournal',$id);
                return true;
            }
        }
        return FALSE;
    }
    public function getAmountByAccountCode($cash_flow = false){
        $post = $this->input->post();
        if($post){
            if(isset($post['biller']) && $post['biller']){
                $this->db->where_in("gl_trans.biller_id", $post['biller']);
            }
            if(isset($post['project_multi']) && $post['project_multi']){
                $this->db->where_in("gl_trans.project_id", $post['project_multi']);
            }
            if(isset($post['start_date']) && $post['start_date']){
                $this->db->where("DATE(tran_date) >=", $this->bpas->fsd($post['start_date']));
            }
            if(isset($post['end_date']) && $post['end_date']){
                $this->db->where("DATE(tran_date) <=", $this->bpas->fsd($post['end_date']));
            }
        }else{
            $this->db->where("DATE(tran_date)", date("Y-m-d"));
        }
        
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->db->where("gl_trans.biller_id", $this->session->userdata('biller_id'));
        }
        
        $this->db->where('gl_charts.cash_flow',$cash_flow);
        $this->db->select('sum('.$this->db->dbprefix('gl_trans').'.amount) AS amount')
        ->join('gl_charts','gl_trans.account_code = gl_charts.accountcode','inner')
        ->group_by('gl_charts.accountcode')
        ->from('gl_trans');

        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function getCreditNoteByID($id)
    {
        $q = $this->db->get_where('credit_note', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getCreditNoteItems($id){
        $q = $this->db->get_where('credit_note_items', ['credit_note_id' => $id]);
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAllCreditNoteItems($sale_id, $return_id = null)
    {
        $this->db->select('credit_note_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, 
                product_variants.name as variant, 
                units.code as base_unit_code,
                units.name as unit_name,
                products.slug,
                products.price, 
                sale_units.name as name_unit,
                products.code, 
                products.image, 
                IF('.$this->db->dbprefix('products').'.currency ="KHR", "៛", "$") as currency,
                products.details as details, 
                products.hsn_code as hsn_code, 
                products.second_name as second_name, 
                products.unit as base_unit_id, 
                products.category_id,
                products.subcategory_id,
                products.cf1 as width,
                products.second_name,
                products.weight,
                products.product_details,
                products.cf2 as length,
                products.cf3 as product_cf3,
                products.cf4 as product_cf4,
                products.cf5 as product_cf5,
                products.cf6 as product_cf6,
                options.name as option_name,
                sale_units.name as product_unit_name
            ')
        ->join('products', 'products.id=credit_note_items.product_id', 'left')
        ->join('product_variants', 'product_variants.id=credit_note_items.option_id', 'left')
        ->join('tax_rates', 'tax_rates.id=credit_note_items.tax_rate_id', 'left')
        ->join('units', 'units.id=products.unit', 'left')
        ->join('units sale_units', 'sale_units.id=credit_note_items.product_unit_id', 'left')
        ->join('options', 'options.id=credit_note_items.option_comment_id', 'left')
        ->group_by('credit_note_items.id')
        ->order_by('id', 'asc');
 
        $this->db->where('credit_note_id', $sale_id);
        $q = $this->db->get('credit_note_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getCreditNoteBySaleID($id)
    {
        $q = $this->db->get_where('credit_note', ['sale_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function syncCreditNotePayments($id = null){
        if ($id) {
            $debit_note = $this->accounts_model->getCreditNoteByID($id);
            if ($payments = $this->accounts_model->getCreditNotePayments($id)) {
                $paid        = 0;
                $grand_total = $debit_note->grand_total;

                foreach ($payments as $payment) {
                    $paid += $payment->amount;
                }

                $payment_status = $paid == 0 ? 'pending' : $debit_note->payment_status;
                if ($this->bpas->formatDecimal($grand_total) == 0 || $this->bpas->formatDecimal($grand_total) == $this->bpas->formatDecimal($paid)) {
                    $payment_status = 'paid';
                } elseif ($paid != 0) {
                    $payment_status = 'partial';
                }

                if ($this->db->update('credit_note', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            } else {
                if ($this->db->update('credit_note', ['paid' => 0, 'payment_status' => 'pending'], ['id' => $id])) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    public function getCreditNotePayments($debit_note_id){
        return $this->db->get_where('payments', ['credit_note_id' => $debit_note_id])->result();
    }
    public function addCreditNotePayment($data = [], $customer_id = null, $accTranPayments = [])
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();

            //========== Accounting: GL transactions ==========
            if (!empty($accTranPayments)) {
                foreach ($accTranPayments as $accTranPayment) {
                    $accTranPayment['payment_id'] = $payment_id;
                    $accTranPayment['tran_no']    = $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }

            //========== Update Reference if matched ==========
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }

            //========== Sync Paid and Payment Status in Debit Note ==========
            if (!empty($data['credit_note_id'])) {
                $this->syncCreditNotePayments($data['credit_note_id']);
            }

            //========== Deduct Deposit if Paid By Deposit ==========
            if (!empty($customer_id) && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($customer_id);
                if ($supplier) {
                    $this->db->update('companies', [
                        'deposit_amount'     => ($supplier->deposit_amount     - $data['amount'])
                    ], ['id' => $customer_id]);
                }
            }

            return true;
        }

        return false;
    }
    public function EditCreditNotPayment($id, $data = [],$accTranPayments = array())
    {
        $opay = $this->site->getPaymentByID($id);
        $credit = $this->getCreditNoteByID($data['credit_note_id']);
        $supplier_id = $credit->customer_id;
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->syncCreditNotePayments($data['credit_note_id']);
            if ($opay->paid_by == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount + $opay->amount)], ['id' => $supplier->id]);
            }
            if ($supplier_id && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount - $data['amount'])], ['id' => $supplier_id]);
            }

            //=========Add Accounting========//
            $this->site->deleteAccTran('Payment',$id);
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
              //=========End Accounting========//
            return true;
        }
        return false;
    }

    public function deleteCreditNotePayment($id)
    {
        $opay = $this->site->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->syncCreditNotePayments($opay->credit_note_id);
            //account---
            $this->site->deleteAccTran('Payment',$id);
            //---end account
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
            } elseif ($opay->paid_by == 'deposit') {
                $sale     = $this->getCreditNoteByID($opay->credit_note_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount' => ($customer->deposit_amount + $opay->amount)
                    ], 
                    ['id' => $customer->id]);
            }
            return true;
        }
        return false;
    }

    public function addCreditNote($data = [], $items = [],$payment = [],$accTrans = array()){  

        $this->db->trans_start();
        if ($this->db->insert('credit_note', $data)) {
            $credit_note_id = $this->db->insert_id();

            //=========Add Accounting =========//
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $accTran['tran_no'] = $credit_note_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('cre') == $data['reference_no']) {
                $this->site->updateReference('cre');
            }
            foreach ($items as $item) {
                $item['credit_note_id'] = $credit_note_id;
                $this->db->insert('credit_note_items', $item);
            }
           // $this->site->sendTelegram("credit_note",$credit_note_id,"added");
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $credit_note_id;
        }
        return false;
    }
    public function EditCreditNote($id, $data, $items = [], $stockmoves = [], $accTrans = array())
    {  
        $this->db->trans_start();
        if ($this->db->update('credit_note', $data, ['id' => $id])  && $this->db->delete('credit_note_items', ['credit_note_id' => $id])) {

            $this->site->deleteAccTran('CreditNote', $id);
            if ($accTrans) {
                foreach ($accTrans as $accTran) {
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            // if ($accTranPayments) {
            //     $this->db->insert_batch('gl_trans', $accTranPayments);
            // } 
            foreach ($items as $item) {
                $item['credit_note_id'] = $id;
                $this->db->insert('credit_note_items', $item);
            }
          //  $this->site->syncSalePayments($id); 

           // $this->site->sendTelegram("credit_note",$id,"updated");
        }  
        $this->db->trans_complete(); 
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Sales_model.php)');
        } else {
            return true;      
        }
        return false;
    }
    public function add_void_credit_note($id,$data, $accTrans = array()){   
        if($id){
            $this->db->update('credit_note',
                array('sale_status'=>'voided','voided_date'=>date('Y-m-d H:i:s'),'voided_by'=>$this->session->userdata('user_id')),
                ['id' => $id]);
                //=========Add Accounting =========//
                if ($accTrans) {
                    foreach ($accTrans as $accTran) {
                        $accTran['tran_no'] = $id;
                        $this->db->insert('gl_trans', $accTran);
                    }
                }
                //=========End Accounting =========//
             //   $this->site->sendTelegram("credit_note",$credit_note_id,"void");
        
            
            return true;
        } else{
            return false;
        }
    }
    public function getDebitNoteByID($id)
    {
        $q = $this->db->get_where('debit_note', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getDebitNoteByPurchaseID($id)
    {
        $q = $this->db->get_where('debit_note', ['purchase_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getAllDebitNoteItems($debit_note_id)
    {
        $this->db->select('debit_note_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate,products.type,
            products.unit, products.other_cost, products.currency, products.details as details, product_variants.name as variant, 
            products.hsn_code as hsn_code, products.second_name as second_name,currencies.symbol as symbol')
            ->join('products', 'products.id=debit_note_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=debit_note_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=debit_note_items.tax_rate_id', 'left')
            ->join('currencies', 'currencies.code=products.currency', 'left')
            ->group_by('debit_note_items.id')
            ->order_by('id', 'asc');
        $q = $this->db->get_where('debit_note_items', ['debit_note_id' => $debit_note_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function addDebitNote($data, $items, $accTrans = null)
    {
  
        $this->db->trans_start();
        if ($this->db->insert('debit_note', $data)) {
            $debit_note_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['debit_note_id'] = $debit_note_id;
                $this->db->insert('debit_note_items', $item);             
            }
            //========Add accounting to accounting transaction====== //
            if ($accTrans != null) {
                foreach($accTrans as $accTran) {
                    $accTran['tran_no'] = $debit_note_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            //========End accounting to accounting transaction====== //
            if ($this->site->getReference('deb') == $data['reference_no']) {
                $this->site->updateReference('deb');
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }
    public function updateDebitNote($id, $data, $items = [], $accTrans = null)
    {
        $this->db->trans_start();
        if ($this->db->update('debit_note', $data, ['id' => $id]) && $this->db->delete('debit_note_items', ['debit_note_id' => $id])) {
            $debit_note_id = $id;

            $this->site->deleteStockmoves('DebitNote', $debit_note_id);
            $this->site->deleteAccTran('DebitNote', $debit_note_id);
            if ($accTrans) {
                $this->db->insert_batch('gl_trans', $accTrans);
            }
            foreach ($items as $item) {
                $item['debit_note_id'] = $id;
                $this->db->insert('debit_note_items', $item);
            }
          //  $this->site->syncPurchasePayments($id);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Update:Purchases_model.php)');
        } else {
            return true;
        }
        return false;
    }
    public function add_void_debit_note($id,$data, $accTrans = array()){   
        if($id){
            $this->db->update('debit_note',
                array('status'=>'voided','voided_date'=>date('Y-m-d H:i:s'),'voided_by'=>$this->session->userdata('user_id')),
                ['id' => $id]);
                //=========Add Accounting =========//
                if ($accTrans) {
                    foreach ($accTrans as $accTran) {
                        $accTran['tran_no'] = $id;
                        $this->db->insert('gl_trans', $accTran);
                    }
                }
                //=========End Accounting =========//
             //   $this->site->sendTelegram("credit_note",$credit_note_id,"void");
        
            
            return true;
        } else{
            return false;
        }
    }
    public function getDebitNoteItems($id)
    {
        $q = $this->db->get_where('debit_note_items', ['debit_note_id' => $id]);
        if($q->num_rows() > 0){
            foreach($q->result() as $row){
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function deleteDebitNote($id){
        if ($this->db->delete('debit_note_items', array('debit_note_id' => $id)) && $this->db->delete('debit_note', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }
     public function addDebitNotes($data, $items = [])
    {
        if ($this->db->insert('bpas_debit_note', $data)) {

            $debit_note_id = $this->db->insert_id();
             $this->site->updateReference('deb');

            if (!empty($items)) {
                foreach ($items as $item) {
                    $item_data = [
                        'debit_note_id' => $debit_note_id,
                        'description'   => $item['description'],
                        'unit_cost'     => $item['unit_cost']
                    ];
                    $this->db->insert('bpas_debit_note_items', $item_data);
                }
            }

            return $debit_note_id;
        } else {
            log_message('error', 'Failed to insert debit note: ' . $this->db->last_query());
            log_message('error', 'Error: ' . $this->db->_error_message());
        }
        return false;
    }
    public function updateDebitNotes($id, $data, $items = []){
    
        $this->db->where('id', $id);
        if ($this->db->update('bpas_debit_note', $data)) {
            $this->db->delete('bpas_debit_note_items', ['debit_note_id' => $id]);
           
            if (!empty($items)) {
                foreach ($items as $item) {
                    $item_data = [
                        'debit_note_id' => $id,
                        'description'   => $item['description'],
                        'unit_cost'     => $item['unit_cost']
                    ];
                    $this->db->insert('bpas_debit_note_items', $item_data);
                }
            }

            return true;
        } else {
            log_message('error', 'Failed to update debit note: ' . $this->db->last_query());
            log_message('error', 'Error: ' . $this->db->_error_message());
        }

        return false;
    }
     public function getDebitNotePayments($debit_note_id){
        return $this->db->get_where('payments', ['debit_note_id' => $debit_note_id])->result();
    }
    public function syncDebitNotePayments($id = null){
        if ($id) {
            $debit_note = $this->accounts_model->getDebitNoteByID($id);
            if ($payments = $this->accounts_model->getDebitNotePayments($id)) {
                $paid        = 0;
                $grand_total = $debit_note->grand_total;

                foreach ($payments as $payment) {
                    $paid += $payment->amount;
                }

                $payment_status = $paid == 0 ? 'pending' : $debit_note->payment_status;
                if ($this->bpas->formatDecimal($grand_total) == 0 || $this->bpas->formatDecimal($grand_total) == $this->bpas->formatDecimal($paid)) {
                    $payment_status = 'paid';
                } elseif ($paid != 0) {
                    $payment_status = 'partial';
                }

                if ($this->db->update('debit_note', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
                    return true;
                }
            } else {
                if ($this->db->update('debit_note', ['paid' => 0, 'payment_status' => 'pending'], ['id' => $id])) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    public function addDebitPayment($data = [], $supplier_id = null, $accTranPayments = [])
    {
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();

            //========== Accounting: GL transactions ==========
            if (!empty($accTranPayments)) {
                foreach ($accTranPayments as $accTranPayment) {
                    $accTranPayment['payment_id'] = $payment_id;
                    $accTranPayment['tran_no']    = $payment_id;
                    $this->db->insert('gl_trans', $accTranPayment);
                }
            }

            //========== Update Reference if matched ==========
            if ($this->site->getReference('ppay') == $data['reference_no']) {
                $this->site->updateReference('ppay');
            }

            //========== Sync Paid and Payment Status in Debit Note ==========
            if (!empty($data['debit_note_id'])) {
                $this->syncDebitNotePayments($data['debit_note_id']);
            }

            //========== Deduct Deposit if Paid By Deposit ==========
            if (!empty($supplier_id) && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                if ($supplier) {
                    $this->db->update('companies', [
                        'deposit_amount'     => ($supplier->deposit_amount     - $data['amount']),
                    ], ['id' => $supplier_id]);
                }
            }

            return true;
        }

        return false;
    }
    public function EditDebitNotPayment($id, $data = [],$accTranPayments = array())
    {
        $opay = $this->site->getPaymentByID($id);
        $credit = $this->getDebitNoteByID($data['debit_note_id']);
        $supplier_id = $credit->customer_id;
        if ($this->db->update('payments', $data, ['id' => $id])) {
            $this->syncDebitNotePayments($data['debit_note_id']);
            if ($opay->paid_by == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount + $opay->amount)], ['id' => $supplier->id]);
            }
            if ($supplier_id && $data['paid_by'] == 'deposit') {
                $supplier = $this->site->getCompanyByID($supplier_id);
                $this->db->update('companies', ['deposit_amount' => ($supplier->deposit_amount - $data['amount'])], ['id' => $supplier_id]);
            }

            //=========Add Accounting========//
            $this->site->deleteAccTran('Payment',$id);
            if($accTranPayments){
                $this->db->insert_batch('gl_trans', $accTranPayments);
            }
              //=========End Accounting========//
            return true;
        }
        return false;
    }

    public function deleteDebitNotePayment($id)
    {
        $opay = $this->site->getPaymentByID($id);
        if ($this->db->delete('payments', ['id' => $id])) {
            $this->syncDebitNotePayments($opay->credit_note_id);
            //account---
            $this->site->deleteAccTran('Payment',$id);
            //---end account
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $gc = $this->site->getGiftCardByNO($payment['cc_no']); 
                $this->db->update('gift_cards', ['balance' => $gc->balance-$payment['amount']], ['card_no' => $payment['cc_no']]);
            } elseif ($opay->paid_by == 'deposit') {
                $sale     = $this->getdebitNoteByID($opay->credit_note_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update(
                    'companies', 
                    [
                        'deposit_amount' => ($customer->deposit_amount + $opay->amount)
                    ], 
                    ['id' => $customer->id]);
            }
            return true;
        }
        return false;
    }
}