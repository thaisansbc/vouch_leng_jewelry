<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends MY_Controller{
	function __construct()
	{
		parent::__construct();

		if (!$this->loggedIn) {
			$this->session->set_userdata('requested_page', $this->uri->uri_string());
			admin_redirect('login');
		}
		if ($this->Customer || $this->Supplier) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		$this->lang->admin_load('accounts', $this->Settings->user_language);
		$this->load->library('form_validation');
		$this->load->admin_model('companies_model');
		$this->load->admin_model('accounts_model');
		$this->load->admin_model('reports_model');
		$this->load->admin_model('sales_model');
		$this->load->admin_model('purchases_model');
		$this->load->admin_model('products_model');

		if(!$this->Owner && !$this->Admin) {
			$gp = $this->site->checkPermissions();
			$this->permission = $gp[0];
			$this->permission[] = $gp[0];
		} else {
			$this->permission[] = NULL;
		}
	}

	function index($action = NULL)
	{
		$this->bpas->checkPermissions('index', true, 'accounts');

		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['action'] = $action;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('accounts'), 'bc' => $bc);
		$this->page_construct('accounts/index', $meta, $this->data);
	}
	function getChartAccount()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');

		$this->load->library('datatables');
		$this->datatables
		->select("(bpas_gl_charts.accountcode) as id,bpas_gl_charts.accountcode, bpas_gl_charts.accountname, bpas_gl_charts.parent_acc, bpas_gl_sections.sectionname,bank,type")
		->from("bpas_gl_charts")
		->join("bpas_gl_sections","bpas_gl_charts.sectionid=bpas_gl_sections.sectionid","INNER")
		->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_account") . "' href='" . admin_url('account/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_account") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('account/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "bpas_gl_charts.accountcode");
		echo $this->datatables->generate();
	}
	public function add()
	{
		$this->bpas->checkPermissions('add', null, 'accounts');

		//$this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[companies.email]');
		$this->form_validation->set_rules('account_code', $this->lang->line("account_code"), 'numeric|is_unique[gl_charts.accountcode]');

		if ($this->form_validation->run('account/add') == true) {
			
			$data = array(
				'accountcode' 	=> $this->input->post('account_code'),
				'accountname' 	=> $this->input->post('account_name'),
				'parent_acc' 	=> $this->input->post('sub_account'),
				'sectionid' 	=> $this->input->post('account_section'),
				'bank' 			=> $this->input->post('bank_account'),
				'type' 			=> $this->input->post('cash_flow'),
				'cash_flow' 	=> $this->input->post('cash_flow'),
				'nature'		=> $this->input->post('nature'),
			);
		}

		if ($this->form_validation->run() == true && $sid = $this->accounts_model->addChartAccount($data)) {
			$this->session->set_flashdata('message', $this->lang->line("accound_added"));
			$ref = isset($_SERVER["HTTP_REFERER"]) ? explode('?', $_SERVER["HTTP_REFERER"]) : NULL;
			admin_redirect($ref[0] . '?account=' . $sid);
		} else {
			$this->data['sectionacc'] = $this->accounts_model->getAccountSections();
			$this->data['cash_flows'] 	= $this->accounts_model->getAllCashflows();
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'accounts/add', $this->data);
		}
	}
	public function edit($id = NULL)
	{
		//$this->bpas->checkPermissions(false, true);
		$account_details = $this->accounts_model->getChartAccountByID($id);	
        if ($this->input->post('account_code') != $account_details->accountcode) {
			$this->form_validation->set_rules('code', lang("code"), 'is_unique[gl_charts.accountcode]|numeric');
        }else{
			$this->form_validation->set_rules('code', lang("code"), 'numeric');
		}

		$parent_account 	= $this->input->post('sub_acc');
		$acc_code 			= $this->input->post('account_code');
		if($this->input->post('sub_account') != '' || $this->input->post('sub_account') != null){
			$parent_account = $this->input->post('sub_account');
		}
		$section_id = $account_details->sectionid;


		if ($this->form_validation->run() === TRUE) {
			$data = array(
				'accountcode' 	=> $acc_code,
				'accountname' 	=> $this->input->post('account_name'),
				'parent_acc' 	=> $parent_account,
				'sectionid' 	=> $this->input->post('account_section'),
				'bank' 			=> $this->input->post('bank_account'),
				'type' 			=> $this->input->post('cash_flow'),
				'cash_flow' 	=> $this->input->post('cash_flow'),
				'nature'		=> $this->input->post('nature'),
			);
		} elseif ($this->input->post('edit_account')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('account');
        }
		if ($this->form_validation->run() == true && $this->accounts_model->updateChartAccount($acc_code, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("accound_updated"));
			admin_redirect('account');	
        } else {
			$this->data['chart_id'] 	= $id;
			$this->data['account'] 		= $account_details;
			$this->data['sectionacc'] 	= $this->accounts_model->getAccountSections();
			$this->data['subacc'] 		= $this->accounts_model->getSubAccounts($section_id);
			$this->data['cash_flows'] 	= $this->accounts_model->getAllCashflows();
			$this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] 	= $this->site->modal_js();
			$this->load->view($this->theme . 'accounts/edit', $this->data);
        }

	}
	function sections($action = NULL)
	{
		$this->bpas->checkPermissions('index', true, 'accounts');

		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['action'] = $action;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('accounts'), 'bc' => $bc);
		$this->page_construct('accounts/sections', $meta, $this->data);
	}
	
	
	function settings($biller_id=false){
		$this->form_validation->set_rules('biller_id', $this->lang->line("biller"), 'required');

		if ($this->form_validation->run() == true) {

			if($this->input->post('biller') == null){
				$biller = $this->input->post('biller_id');
			}else{
				$biller = $this->input->post('biller');
			}
			if($this->input->post('default_open_balance') == null){
				$open_balance = $this->input->post('open_balance');
			}else{
				$open_balance = $this->input->post('default_open_balance');
			}
			if($this->input->post('default_sale') == null){
				$sale = $this->input->post('sales');
			}else{
				$sale = $this->input->post('default_sale');
			}
			if($this->input->post('default_sale_discount') == null){
				$sale_discount = $this->input->post('sale_discount');
			}else{
				$sale_discount = $this->input->post('default_sale_discount');
			}
			if($this->input->post('default_sale_tax') == null){
				$sale_tax = $this->input->post('dsale_tax');
			}else{
				$sale_tax = $this->input->post('default_sale_tax');
			}
			if($this->input->post('default_receivable') == null){
				$receivable = $this->input->post('receivable');
			}else{
				$receivable = $this->input->post('default_receivable');
			}
			if($this->input->post('default_purchase') == null){
				$dpurchase = $this->input->post('dpurchase');
			}else{
				$dpurchase = $this->input->post('default_purchase');
			}
			if($this->input->post('default_purchase_discount') == null){
				$dpurchase_discount = $this->input->post('dpurchase_discount');
			}else{
				$dpurchase_discount = $this->input->post('default_purchase_discount');
			}
			if($this->input->post('default_purchase_tax') == null){
				$dpurchase_tax = $this->input->post('dpurchase_tax');
			}else{
				$dpurchase_tax = $this->input->post('default_purchase_tax');
			}
			if($this->input->post('default_payable') == null){
				$dpayable = $this->input->post('dpayable');
			}else{
				$dpayable = $this->input->post('default_payable');
			}
			if($this->input->post('default_sale_freight') == null){
				$dsale_freight = $this->input->post('dsale_freight');
			}else{
				$dsale_freight = $this->input->post('default_sale_freight');
			}
			if($this->input->post('default_purchase_freight') == null){
				$dpurchase_freight = $this->input->post('dpurchase_freight');
			}else{
				$dpurchase_freight = $this->input->post('default_purchase_freight');
			}
			if($this->input->post('default_cost') == null){
				$dcost = $this->input->post('dcost');
			}else{
				$dcost = $this->input->post('default_cost');
			}
			if($this->input->post('default_stock') == null){
				$dstock = $this->input->post('dstock');
			}else{
				$dstock = $this->input->post('default_stock');
			}
			if($this->input->post('default_stock_adjust') == null){
				$dstock_adjust = $this->input->post('dstock_adjust');
			}else{
				$dstock_adjust = $this->input->post('default_stock_adjust');
			}
			if($this->input->post('default_payroll') == null){
				$dpayroll = $this->input->post('dpayroll');
			}else{
				$dpayroll = $this->input->post('default_payroll');
			}
			if($this->input->post('default_cash') == null){
				$dcash = $this->input->post('dcash');
			}else{
				$dcash = $this->input->post('default_cash');
			}
			if($this->input->post('default_credit_card') == null){
				$dcredit_card = $this->input->post('dcredit_card');
			}else{
				$dcredit_card = $this->input->post('default_credit_card');
			}
			if($this->input->post('default_gift_card') == null){
				$dgift_card = $this->input->post('dgift_card');
			}else{
				$dgift_card = $this->input->post('default_gift_card');
			}
			if($this->input->post('default_sale_deposit') == null){
				$dsale_deposit = $this->input->post('dsale_deposit');
			}else{
				$dsale_deposit = $this->input->post('default_sale_deposit');
			}
			if($this->input->post('default_purchase_deposit') == null){
				$dpurchase_deposit = $this->input->post('dpurchase_deposit');
			}else{
				$dpurchase_deposit = $this->input->post('default_purchase_deposit');
			}
			if($this->input->post('default_cheque') == null){
				$dcheque = $this->input->post('dcheque');
			}else{
				$dcheque = $this->input->post('default_cheque');
			}
			if($this->input->post('default_loan') == null){
				$dloan = $this->input->post('dloan');
			}else{
				$dloan = $this->input->post('default_loan');
			}
			if($this->input->post('default_retained_earnings') == null){
				$dretained_earning = $this->input->post('dretained_earning');
			}else{
				$dretained_earning = $this->input->post('default_retained_earnings');
			}
			if($this->input->post('default_cost_variant') == null){
				$cost_of_variance = $this->input->post('cost_variant');
			}else{
				$cost_of_variance = $this->input->post('default_cost_variant');
			}
			if($this->input->post('default_interest_income') == null){
				$default_interest_income = $this->input->post('interest_income');
			}else{
				$default_interest_income = $this->input->post('default_interest_income');
			}
			if($this->input->post('default_transfer_owner') == null){
				$default_transfer_owner = $this->input->post('transfer_owner');
			}else{
				$default_transfer_owner = $this->input->post('default_transfer_owner');
			}
			$data = array(
				'biller_id'            		=> $biller,
				'default_open_balance' 		=> $open_balance,
				'default_sale'         		=> $sale, 
				'other_income'         		=> $this->input->post('other_income'),
				'default_sale_discount'		=> $sale_discount,
				'default_sale_tax'    		=> $sale_tax,
				'default_receivable'   		=> $receivable, 
				'default_purchase'     		=> $dpurchase,
				'default_purchase_discount' => $dpurchase_discount, 
				'default_purchase_tax' 		=> $dpurchase_tax, 
				'default_payable'      		=> $dpayable,
				'default_sale_freight'      => $dsale_freight,
				'default_purchase_freight'  => $dpurchase_freight,
				'default_cost'         		=> $dcost, 
				'default_stock' 	   		=> $dstock,
				'default_stock_adjust' 		=> $dstock_adjust, 
				'default_payroll'      		=> $dpayroll,
				'default_cash'         		=> $dcash,
				'default_credit_card'  		=> $dcredit_card,
				'default_gift_card'    		=> $dgift_card,
				'default_sale_deposit' 		=> $dsale_deposit,
				'default_purchase_deposit' 	=> $dpurchase_deposit,
				'default_cheque'       		=> $dcheque,
				'default_loan'         		=> $dloan,
				'default_retained_earnings'	=> $dretained_earning,
				'default_cost_variant'		=> $cost_of_variance,
				'default_interest_income' 	=> $default_interest_income,
				'default_transfer_owner' 	=> $default_transfer_owner,
				'default_payment_pos' 		=> $this->input->post('default_payment_pos'),
				'default_product_tax' 		=> $this->input->post('default_product_tax'),
				'default_product_discount' 	=> $this->input->post('default_product_discount'),
				'default_write_off' 		=> $this->input->post('write_off'),
				'installment_outstanding_acc' => $this->input->post('outstanding_installment'),
				'default_stock_using' 		=> $this->input->post('default_stock_using'),
				'default_expense' 			=> $this->input->post('default_expense'),
				'default_salary_payable' 	=> $this->input->post('default_salary_payable'),
				'default_cost_adjustment'	=> $this->input->post('default_cost_adjustment'),
				'default_convert_account'	=> $this->input->post('default_convert'),
				'credit_note'				=> $this->input->post('credit_note'),
				'debit_note'				=> $this->input->post('debit_note'),

			);
		}
		if ($this->form_validation->run() == true && $this->accounts_model->updateSetting($data,$biller_id)) {  

            $this->session->set_flashdata('message', $this->lang->line("Account settings has been updated"));
            admin_redirect('account/settings/#default_'.$biller_id);
        } else {
			$this->data['get_biller'] 		= $this->site->getAllCompanies('biller');

			$this->data['default'] 			= $this->companies_model->getDefaults($id=null);
			$this->data['chart_accounts'] 	= $this->accounts_model->getAllChartAccounts();
			$this->data['sale_name'] 		= $this->accounts_model->getSalename();
			$this->data['sale_discount'] 	= $this->accounts_model->getsalediscount();
			$this->data['sale_tax'] 		= $this->accounts_model->getsale_tax();
			$this->data['receivable'] 		= $this->accounts_model->getreceivable();
			$this->data['purchases'] 		= $this->accounts_model->getpurchases();
			$this->data['purchase_tax'] 	= $this->accounts_model->getpurchase_tax();
			$this->data['purchasediscount'] = $this->accounts_model->getpurchasediscount();
			$this->data['payable'] 			= $this->accounts_model->getpayable();
			$this->data['get_sale_freight'] = $this->accounts_model->get_sale_freights();
			$this->data['get_purchase_freight'] = $this->accounts_model->get_purchase_freights();
			$this->data['getstock'] 		= $this->accounts_model->getstocks();
			$this->data['stock_adjust'] 	= $this->accounts_model->getstock_adjust();
			$this->data['getcost'] 			= $this->accounts_model->get_cost();
			$this->data['getpayroll'] 		= $this->accounts_model->getpayrolls();
			$this->data['get_cashs'] 		= $this->accounts_model->get_cash();
			$this->data['credit_card'] 		= $this->accounts_model->getcredit_card();
			$this->data['sale_deposit'] 	= $this->accounts_model->get_sale_deposit();
			$this->data['purchased_eposit'] = $this->accounts_model->get_purchase_deposit();
			$this->data['gift_card'] 		= $this->accounts_model->getgift_card();
			$this->data['cheque'] 			= $this->accounts_model->getcheque();
			$this->data['loan'] 			= $this->accounts_model->get_loan();
			$this->data['retained_earning'] = $this->accounts_model->get_retained_earning();
			$this->data['cost_of_variance'] = $this->accounts_model->get_cost_of_variance();
			$this->data['interest_income'] 	= $this->accounts_model->getInterestIncome();
			$this->data['transfer_owner'] 	= $this->accounts_model->getTransferOwner();
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
			$meta = array('page_title' => lang('acount_settings'), 'bc' => $bc);
			$this->page_construct('accounts/settings_multi_biller', $meta, $this->data);
		}
	}
	function list_ac_recevable($warehouse_id = NULL, $datetime = NULL)
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		$this->load->admin_model('reports_model');

		if(isset($_GET['d']) != ""){
			$date = $_GET['d'];
		}else{
			$date = NULL;
		}

		$search_id = NULL;
		if($this->input->get('id')){
			$search_id = $this->input->get('id');
		}

		$this->data['users'] = $this->reports_model->getStaff();
		$this->data['warehouses'] = $this->site->getAllWarehouses();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		if ($this->Owner || $this->Admin) {
			$this->data['warehouses'] = $this->site->getAllWarehouses();
			$this->data['warehouse_id'] = $warehouse_id;
			$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
		} else {
			$this->data['warehouses'] = NULL;
			$this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
			$this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
		}
		$this->data['dt'] = $datetime;
		$this->data['date'] = $date;
		
		$this->data['search_id'] = $search_id;

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('sales'), 'bc' => $bc);
		$this->page_construct('accounts/acc_receivable', $meta, $this->data);
	}

	function list_ar_aging($warehouse_id = NULL) 
	{
		$this->data['users'] = $this->reports_model->getStaff();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['user_id'] = isset($user_id);
		$this->data['warehouses'] = $this->site->getAllWarehouses();

		if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = $this->products_model->getUserWarehouses();
			if($warehouse_id){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			} else {
				//$this->bpas->print_arrays(str_replace(',', '-', $this->session->userdata('warehouse_id')));
				$this->data['warehouse_id'] = str_replace(',', '-', $this->session->userdata('warehouse_id'));
				$this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->products_model->getUserWarehouses() : NULL;
			}
        }

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('accounts')), array('link' => '#', 'page' => lang('list_ar_aging')));
		$meta = array('page_title' => lang('list_ar_aging'), 'bc' => $bc);
		$this->page_construct('accounts/list_ar_aging', $meta, $this->data);
	}

	function getSales_pending($warehouse_id = NULL, $date = NULL)
	{
		if($warehouse_id){
			$warehouse_id = explode('-', $warehouse_id);
		}
		$this->bpas->checkPermissions('list_ar_aging', null, 'account');
		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}
		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}

		$this->load->library('datatables');
		if ($warehouse_id) {
			$this->datatables
			->select("customer_id, sales.date,customer, 
				SUM(IFNULL(grand_total, 0)) AS grand_total,
				SUM(IFNULL(paid, 0)) AS paid,
				SUM(IFNULL(grand_total - paid, 0)) AS balance,
				COUNT(bpas_sales.id) AS ar_number")
			->from('sales')
			->where('payment_status !=', 'paid')
			->where('payment_status !=', 'Returned')
			->where('DATE_SUB('. $this->db->dbprefix('sales')  .'.date, INTERVAL 1 DAY) <= CURDATE()')
			->where_in('warehouse_id', $warehouse_id)
			->group_by('customer_id');
		} else {
			$lsale = "(SELECT
							s.customer_id,
							s.date,
							SUM(IFNULL(s.grand_total, 0)) AS grand_total2,
							SUM(IFNULL(s.paid, 0)) AS paid2,
							SUM(IFNULL(s.grand_total - paid, 0)) AS balance2,
							COUNT(s.id) AS ar_number2
						FROM ".$this->db->dbprefix('sales')." AS s
						WHERE
							s.payment_status != 'Returned'
						AND s.payment_status != 'paid'
						AND DATE_SUB(s.date, INTERVAL 1 DAY) <= CURDATE()
						AND (s.grand_total - s.paid) <> 0
						GROUP BY s.customer_id
					) as bpas_gsale ";

			$this->datatables
				->select("
						bpas_companies.id,sales.date, bpas_sales.customer, 
						grand_total2, paid2, balance2,
						ar_number2
					", FALSE)
				->from('sales')
				->join('companies','sales.customer_id = companies.id', 'left')
				->join($lsale,'companies.id = '.$this->db->dbprefix("gsale").'.customer_id','left');

			if(isset($_REQUEST['d'])){
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('sales.payment_term <>', 0);
			}
			$this->datatables->group_by('bpas_sales.customer_id');
		}
		
		if ($this->permission['sales-index'] = ''){
			if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
				$this->datatables->where('created_by', $this->session->userdata('user_id'));
			} elseif ($this->Customer) {
				$this->datatables->where('customer_id', $this->session->userdata('user_id'));
			}
		}
		if ($user_query) {
			$this->datatables->where('sales.created_by', $user_query);
		}
		if ($reference_no) {
			$this->datatables->where('sales.reference_no', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('sales.biller_id', $biller);
		}
		if ($customer) {
			$this->datatables->where('sales.customer_id', $customer);
		}
		if ($warehouse) {
			$this->datatables->where('sales.warehouse_id', $warehouse);
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_recevable') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}
	
	function list_ar_aging_0_30($warehouse_id = NULL) 
	{
		$this->bpas->checkPermissions('list_ar_aging', null, 'account');
		
		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}

		$this->load->library('datatables');
		if ($warehouse_id) {
			$this->datatables
			->select("customer_id, sales.date, customer, 
					SUM(IFNULL(grand_total, 0)) as grand_total, 
					SUM(IFNULL(paid, 0)) as paid, 
					SUM(IFNULL(grand_total-paid, 0)) as balance, COUNT(id) AS ar_number")
				->from('sales')
				->where('payment_status !=', 'paid')
				->where('payment_status !=', 'Returned')
				->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY')
				->where('warehouse_id', $warehouse_id)
				->group_by('customer_id');
			// ->select("companies.id, customer,
			// 	SUM(
			// 		IFNULL(grand_total, 0)
			// 	) as grand_total, 
			// 	SUM(
			// 		IFNULL(paid, 0)
			// 	) as paid, 
			// 	SUM(
			// 		IFNULL(grand_total-paid, 0)
			// 	) as balance,
			// 	COUNT(
			// 		sales.id
			// 	) as ar_number
			// 	")
			// ->from('sales')
			// ->join('bpas_companies.bill')
			// ->where('payment_status !=', 'paid')
			// ->where('payment_status !=', 'Returned')
			// ->where('warehouse_id', $warehouse_id)		
			// ->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY')
			// ->group_by('customer');
		} else {
			$this->datatables
			->select("companies.id, sales.date, customer, 
				SUM(
					IFNULL(grand_total, 0)
				) as grand_total, 
				SUM(
					IFNULL(paid, 0)
				) as paid, 
				SUM(
					IFNULL(grand_total-paid, 0)
				) as balance,
				COUNT(
					bpas_sales.id
				) as ar_number
				")
			->from('sales')
			->join ('companies', 'sales.customer_id = companies.id', 'left')
			->where('payment_status !=', 'Returned')
			->where('payment_status !=', 'paid')
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY')
			->where('(grand_total-paid) <> ', 0);
			if(isset($_REQUEST['d'])){
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('sales.payment_term <>', 0);
			}
			$this->datatables->group_by('customer_id');
		}

		if ($this->permission['sales-index'] = ''){
			if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
				$this->datatables->where('created_by', $this->session->userdata('user_id'));
			} elseif ($this->Customer) {
				$this->datatables->where('customer_id', $this->session->userdata('user_id'));
			}
		}

		if ($user_query) {
			$this->datatables->where('sales.created_by', $user_query);
		}

		if ($reference_no) {
			$this->datatables->where('sales.reference_no', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('sales.biller_id', $biller);
		}
		if ($customer) {
			$this->datatables->where('sales.customer_id', $customer);
		}
		if ($warehouse) {
			$this->datatables->where('sales.warehouse_id', $warehouse);
		}

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}

		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_recevable/0/30') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}

	function list_ar_aging_30_60($warehouse_id = NULL) 
	{
		$this->bpas->checkPermissions('list_ar_aging', null, 'account');

		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}
		$this->load->library('datatables');
		if ($warehouse_id) {
			$this->datatables
			->select("customer_id, sales.date, customer, 
				SUM(IFNULL(grand_total, 0)) as grand_total, 
				SUM(IFNULL(paid, 0)) as paid, 
				SUM(IFNULL(grand_total-paid, 0)) as balance,COUNT(id) AS ar_number")
			->from('sales')
			->where('payment_status !=', 'paid')
			->where('payment_status !=', 'Returned')
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY')
			->where('warehouse_id', $warehouse_id)
			->group_by('customer_id');

			// ->select("id, customer, 
			// 	SUM(
			// 		IFNULL(grand_total, 0)
			// 	) as grand_total, 
			// 	SUM(
			// 		IFNULL(paid, 0)
			// 	) as paid, 
			// 	SUM(
			// 		IFNULL(grand_total-paid, 0) + IFNULL(grand_total-paid, 0)
			// 	) as balance
			// 	COUNT(
			// 		id 
			// 	) as ar_number
			// 	")
			// ->from('sales')
			// ->where('payment_status !=', 'paid')
			// ->where('payment_status !=', 'Returned')
			// ->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY')
			// ->where('warehouse_id', $warehouse_id)
			// ->group_by('customer');
		} else {
			$this->datatables
			->select("customer_id, sales.date, customer, 
				SUM(
					IFNULL(grand_total, 0)
				) as grand_total, 
				SUM(
					IFNULL(paid, 0)
				) as paid, 
				SUM(
					IFNULL(grand_total-paid, 0)
				) as balance,
				COUNT(
					id 
				) as ar_number
				")
			->from('sales')
			->where('payment_status !=', 'Returned')
			->where('payment_status !=', 'paid')
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY')
			->where('(grand_total-paid) <> ', 0);

			if(isset($_REQUEST['d'])){
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));
				$this->datatables
				->where("date >=", $date)
				->where($this->db->dbprefix('sales') . '.payment_term <>', 0);
			}
			$this->datatables->group_by('customer_id');
		}

		if ($this->permission['sales-index'] = ''){
			if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
				$this->datatables->where('created_by', $this->session->userdata('user_id'));
			} elseif ($this->Customer) {
				$this->datatables->where('customer_id', $this->session->userdata('user_id'));
			}
		}

		if ($user_query) {
			$this->datatables->where('sales.created_by', $user_query);
		}

		if ($reference_no) {
			$this->datatables->where('sales.reference_no', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('sales.biller_id', $biller);
		}
		if ($customer) {
			$this->datatables->where('sales.customer_id', $customer);
		}
		if ($warehouse) {
			$this->datatables->where('sales.warehouse_id', $warehouse);
		}

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}

		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_recevable/0/60') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}

	function list_ar_aging_60_90($warehouse_id = NULL) 
	{
		$this->bpas->checkPermissions('list_ar_aging', null, 'account');

		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}
		

		$this->load->library('datatables');
		if ($warehouse_id) {
			$this->datatables
			->select("customer_id, sales.date, customer, 
				SUM(IFNULL(grand_total, 0)) as grand_total, 
				SUM(IFNULL(paid, 0)) as paid, 
				SUM(IFNULL(grand_total-paid, 0)) as balance")
			->from('sales')
			->where('payment_status !=', 'paid')
			->where('payment_status !=', 'Returned')
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 90 DAY AND curdate() - INTERVAL 60 DAY')
			->where('warehouse_id', $warehouse_id)
			->group_by('customer_id');
		} else {
			$this->datatables
			->select("customer_id, sales.date, customer, 
				SUM(
					IFNULL(grand_total, 0)
				) as grand_total, 
				SUM(
					IFNULL(paid, 0)
				) as paid, 
				SUM(
					IFNULL(grand_total-paid, 0)
				) as balance,
				COUNT(
					id
				) as ar_number
				")
			->from('sales')
			->where('payment_status !=', 'Returned')
			->where('payment_status !=', 'paid')
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 90 DAY AND curdate() - INTERVAL 60 DAY')
			->where('(grand_total-paid) <> ', 0);
			if(isset($_REQUEST['d'])){
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('sales.payment_term <>', 0);
			}
			$this->datatables->group_by('customer_id');
		}

		//$this->datatables->where('pos !=', 1);
		if ($this->permission['sales-index'] = ''){
			if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
				$this->datatables->where('created_by', $this->session->userdata('user_id'));
			} elseif ($this->Customer) {
				$this->datatables->where('customer_id', $this->session->userdata('user_id'));
			}
		}
		if ($user_query) {
			$this->datatables->where('sales.created_by', $user_query);
		}
		if ($customer) {
			$this->datatables->where('sales.id', $customer);
		}
		if ($reference_no) {
			$this->datatables->where('sales.reference_no', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('sales.biller_id', $biller);
		}
		if ($customer) {
			$this->datatables->where('sales.customer_id', $customer);
		}
		if ($warehouse) {
			$this->datatables->where('sales.warehouse_id', $warehouse);
		}

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}

		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_recevable/0/90') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}

	function list_ar_aging_over_90($warehouse_id = NULL) 
	{
		$this->bpas->checkPermissions('list_ar_aging', null, 'account');

		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}

		$this->load->library('datatables');
		if ($warehouse_id) {
			$this->datatables
			->select("customer_id, sales.date, customer, 
				SUM(
					IFNULL(grand_total, 0)
				) as grand_total, 
				SUM(
					IFNULL(paid, 0)
				) as paid, 
				SUM(
					IFNULL(grand_total-paid, 0)
				) as balance,
				COUNT(
					id
				) as ar_number
				")
			->from('sales')
			->where('payment_status !=', 'paid')
			->where('payment_status !=', 'Returned')
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 10000 DAY AND curdate() - INTERVAL 90 DAY')
			->where('warehouse_id', $warehouse_id)
			->group_by('customer_id');
		} else {
			$this->datatables
			->select("customer_id, sales.date, customer, 
				SUM(
					IFNULL(grand_total, 0)
				) as grand_total, 
				SUM(
					IFNULL(paid, 0)
				) as paid, 
				SUM(
					IFNULL(grand_total-paid, 0)
				) as balance,
				COUNT(
					id
				) as ar_number
				")
			->from('sales')
			->where('payment_status !=', 'Returned')
			->where('payment_status !=', 'paid')
			->where('(grand_total-paid) <> ', 0)
			->where('DATE(bpas_sales.date) BETWEEN curdate() - INTERVAL 10000 DAY AND curdate() - INTERVAL 90 DAY');
			if(isset($_REQUEST['d'])){
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('sales.payment_term <>', 0);
			}
			$this->datatables->group_by('customer_id');
		}

		if ($this->permission['sales-index'] = ''){
			if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin) {
				$this->datatables->where('created_by', $this->session->userdata('user_id'));
			} elseif ($this->Customer) {
				$this->datatables->where('customer_id', $this->session->userdata('user_id'));
			}
		}

		if ($user_query) {
			$this->datatables->where('sales.created_by', $user_query);
		}
		if ($customer) {
			$this->datatables->where('sales.id', $customer);
		}
		if ($reference_no) {
			$this->datatables->where('sales.reference_no', $reference_no);
		}
		if ($biller) {
			$this->datatables->where('sales.biller_id', $biller);
		}
		if ($customer) {
			$this->datatables->where('sales.customer_id', $customer);
		}
		if ($warehouse) {
			$this->datatables->where('sales.warehouse_id', $warehouse);
		}

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}

		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_recevable/0/91') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}

	function list_ac_payable($warehouse_id = null, $rows = NULL, $dt = NULL)
	{
		$search_id = NULL;
		if($this->input->get('id')){
			$search_id = $this->input->get('id');
		}

		$this->bpas->checkPermissions('index', true, 'accounts');
		$this->load->admin_model('reports_model');

		if(isset($_GET['d']) != ""){
			$date = $_GET['d'];
		}else{
			$date = NULL;
		}

		$this->data['users'] = $this->reports_model->getStaff();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
			$this->data['warehouses'] = $this->site->getAllWarehouses();
			$this->data['warehouse_id'] = $warehouse_id;
			$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
		} else {
			$this->data['warehouses'] = null;
			$this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
			$this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
		}

		$this->data['dt'] = $dt;
		$this->data['date'] = $date;
		$this->data['search_id'] = $search_id;

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('list_ac_payable'), 'bc' => $bc);
		$this->page_construct('accounts/acc_payable', $meta, $this->data);
	}

	
	function getAccountSections()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');

		$this->load->library('datatables');
		$this->datatables
		->select("sectionid,sectionname,sectionname_kh,AccountType,description")
		->from("gl_sections")->order_by('sectionid','ASC');
		echo $this->datatables->generate();
	}
	function billReceipt()
	{
		$this->bpas->checkPermissions('bill_receipt', null, 'account');

		$this->load->admin_model('reports_model');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['users'] = $this->reports_model->getStaff();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('accounts'), 'page' => lang('accounts')), array('link' => '#', 'page' => lang('Bill Receipt')));
		$meta = array('page_title' => lang('Account'), 'bc' => $bc);
		$this->page_construct('accounts/bill_reciept', $meta, $this->data);
	}

	function getBillReciept($pdf = NULL, $xls = NULL)
	{
		if ($this->input->get('user')) {
			$user = $this->input->get('user');
		} else {
			$user = NULL;
		}		
		if ($this->input->get('supplier')) {
			$supplier = $this->input->get('supplier');
		} else {
			$supplier = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('payment_ref')) {
			$payment_ref = $this->input->get('payment_ref');
		} else {
			$payment_ref = NULL;
		}
		if ($this->input->get('sale_ref')) {
			$sale_ref = $this->input->get('sale_ref');
		} else {
			$sale_ref = NULL;
		}
		if ($this->input->get('purchase_ref')) {
			$purchase_ref = $this->input->get('purchase_ref');
		} else {
			$purchase_ref = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fsd($start_date);
			$end_date = $this->bpas->fsd($end_date);
		}
		
		if ($this->input->get('inv_start_date')) {
			$inv_start_date = $this->input->get('inv_start_date');
		} else {
			$inv_start_date = NULL;
		}
		
		if ($this->input->get('inv_end_date')) {
			$inv_end_date = $this->input->get('inv_end_date');
		} else {
			$inv_end_date = NULL;
		}
		
		if ($inv_start_date) {
			$inv_start_date = $this->bpas->fsd($inv_start_date);
			$inv_end_date = $this->bpas->fsd($inv_end_date);
		}
		
		/*if (!$this->Owner && !$this->Admin) {
			$user = $this->session->userdata('user_id');
		}*/
		if ($pdf || $xls) {

			$this->db
			->select("" . $this->db->dbprefix('payments') . ".date, 
				" . $this->db->dbprefix('payments') . ".reference_no as payment_ref, 
				" . $this->db->dbprefix('sales') . ".reference_no as sale_ref,customer,paid_by, amount, type")
			->from('payments')
			->join('sales', 'payments.sale_id=sales.id', 'left')
			->join('purchases', 'payments.purchase_id=purchases.id', 'left')
			->group_by('payments.id')
			->order_by('payments.date asc');
			$this->db->where('payments.type != "sent"');
				//	$this->db->where('sales.customer !=""');

			if ($user) {
				$this->db->where('payments.created_by', $user);
			}
			if ($customer) {
				$this->db->where('sales.customer_id', $customer);
			}
			if ($supplier) {
				$this->db->where('purchases.supplier_id', $supplier);
			}
			if ($biller) {
				$this->db->where('sales.biller_id', $biller);
			}
			if ($customer) {
				$this->db->where('sales.customer_id', $customer);
			}
			if ($payment_ref) {
				$this->db->like('payments.reference_no', $payment_ref, 'both');
			}
			if ($sale_ref) {
				$this->db->like('sales.reference_no', $sale_ref, 'both');
			}
			if ($purchase_ref) {
				$this->db->like('purchases.reference_no', $purchase_ref, 'both');
			}
			if ($start_date) {
				$this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}
			if ($inv_start_date) {
				$this->db->where('DATE('.$this->db->dbprefix('sales').'.date) BETWEEN "' . $inv_start_date . '" and "' . $inv_end_date . '"');
			}

			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}

			if (!empty($data)) {

				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('payments_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

				$row = 2;
				$total = 0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
					if ($data_row->type == 'returned' || $data_row->type == 'sent') {
						$total -= $data_row->amount;
					} else {
						$total += $data_row->amount;
					}
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
				->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$filename = 'payments_report';
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				if ($pdf) {
					$styleArray = array(
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
								)
							)
						);
					$this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
					$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
					$rendererLibrary = 'MPDF';
					$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
					if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
						die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
							PHP_EOL . ' as appropriate for your directory structure');
					}

					header('Content-Type: application/pdf');
					header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
					header('Cache-Control: max-age=0');

					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
					$objWriter->save('php://output');
					exit();
				}
				if ($xls) {
					ob_clean();
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
					header('Cache-Control: max-age=0');
					ob_clean();
					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
					$objWriter->save('php://output');
					exit();
				}
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		} else {
			$acc = $this->session->userdata('group_id');
			
			$this->load->library('datatables');
			if ($this->Owner || $this->Admin || $acc == 10) {
				$this->datatables
					->select($this->db->dbprefix('payments') . ".id,
						" . $this->db->dbprefix('payments') . ".date AS date,
						" . $this->db->dbprefix('sales') . ".date AS sale_date,
						" . $this->db->dbprefix('payments') . ".reference_no as payment_ref, 
						" . $this->db->dbprefix('sales') . ".reference_no as sale_ref, customer,
						(
						CASE 
						WHEN " . $this->db->dbprefix('payments') . ".note = ' ' THEN 
						".$this->db->dbprefix('sales') . ".suspend_note 
						WHEN " . $this->db->dbprefix('sales') . ".suspend_note != ''  THEN 
						CONCAT(".$this->db->dbprefix('sales') . ".suspend_note, ' - ',  " . $this->db->dbprefix('payments') . ".note) 
						ELSE " . $this->db->dbprefix('payments') . ".note END
						), 
						" . $this->db->dbprefix('payments') . ".paid_by, IF(bpas_payments.type = 'returned', CONCAT('-', bpas_payments.amount), bpas_payments.amount) as amount, " . $this->db->dbprefix('payments') . ".type, bpas_sales.sale_status")
					->from('payments')
					->join('sales', 'payments.sale_id=sales.id', 'left')
					->join('purchases', 'payments.purchase_id=purchases.id', 'left')
					->group_by('payments.id')
					->order_by('sales.id desc');
					/*if($this->session->userdata('biller_id')){
						$this->datatables->where('payments.biller_id',$this->session->userdata('biller_id'));
					}*/
					$this->db->where('payments.type != "sent"');
					$this->db->where('sales.customer !=""');
			} else {
				$this->datatables
					->select($this->db->dbprefix('payments') . ".id,
						" . $this->db->dbprefix('payments') . ".date AS date,
						" . $this->db->dbprefix('sales') . ".date AS sale_date,
						" . $this->db->dbprefix('payments') . ".reference_no as payment_ref, 
						" . $this->db->dbprefix('sales') . ".reference_no as sale_ref, customer,
						(
						CASE 
						WHEN " . $this->db->dbprefix('payments') . ".note = ' ' THEN 
						".$this->db->dbprefix('sales') . ".suspend_note 
						WHEN " . $this->db->dbprefix('sales') . ".suspend_note != ''  THEN 
						CONCAT(".$this->db->dbprefix('sales') . ".suspend_note, ' - ',  " . $this->db->dbprefix('payments') . ".note) 
						ELSE " . $this->db->dbprefix('payments') . ".note END
						), 
						" . $this->db->dbprefix('payments') . ".paid_by, IF(bpas_payments.type = 'returned', CONCAT('-', bpas_payments.amount), bpas_payments.amount) as amount, " . $this->db->dbprefix('payments') . ".type")
					->from('payments')
					->join('sales', 'payments.sale_id=sales.id', 'left')
					->join('purchases', 'payments.purchase_id=purchases.id', 'left')
					->group_by('payments.id')
					->order_by('sales.id desc');
					if($this->session->userdata('biller_id')){
						$this->datatables->where('payments.biller_id',$this->session->userdata('biller_id'));
					}
					$this->db->where('payments.type != "sent"');
					$this->db->where('sales.customer !=""');
					if($this->session->userdata('user_id')){
						$this->datatables->where('payments.created_by', $this->session->userdata('user_id'));
					}
			}			
			
			if (isset($user)) {
				$this->datatables->where('payments.created_by', $user);
			}
			if (isset($customer)) {
				$this->datatables->where('sales.customer_id', $customer);
			}
			if (isset($supplier)) {
				$this->datatables->where('purchases.supplier_id', $supplier);
			}
			if (isset($biller)) {
				$this->datatables->where('sales.biller_id', $biller);
			}
			if (isset($customer)) {
				$this->datatables->where('sales.customer_id', $customer);
			}
			if (isset($payment_ref)) {
				$this->datatables->like('payments.reference_no', $payment_ref, 'both');
			}
			if (isset($sale_ref)) {
				$this->datatables->like('sales.reference_no', $sale_ref, 'both');
			}
			if (isset($customers)){
				$this->datatables->like('sales.customers',$customers,'both');
			}
			if (isset($purchase_ref)) {
				$this->datatables->like('payments.paid_bys', $purchase_ref, 'both');
			}
			if (isset($grand_total)) {
				$this->datatables->like('sales.grand_total', $grand_total, 'both');
			}
			if (isset($start_date)) {
				$this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}
			if (isset($inv_start_date)) {
				$this->db->where('DATE('.$this->db->dbprefix('sales').'.date) BETWEEN "' . $inv_start_date . '" and "' . $inv_end_date . '"');
			}


			echo $this->datatables->generate();

		}

	}

	function list_ap_aging($warehouse_id = NULL)
	{
		
		$this->data['user_id'] = isset($user_id);

		if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			
        } else {
			
            $this->data['warehouses'] = $this->products_model->getUserWarehouses();
			if($warehouse_id){
				$this->data['warehouse_id'] = $warehouse_id;
				$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
			}else{
				//$this->bpas->print_arrays(str_replace(',', '-',$this->session->userdata('warehouse_id')));
				$this->data['warehouse_id'] = str_replace(',', '-',$this->session->userdata('warehouse_id'));
				$this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->products_model->getUserWarehouses() : NULL;
			}
        }
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('accounts')), array('link' => '#', 'page' => lang('list_ap_aging')));
		$meta = array('page_title' => lang('list_ap_aging'), 'bc' => $bc);
		$this->page_construct('accounts/list_ap_aging', $meta, $this->data);
	}

	public function getpending_Purchases($warehouse_id = null)
	{
		$this->bpas->checkPermissions('index', true,'accounts');
		if ($this->input->get('product')) {
			$product = $this->input->get('product');
		} else {
			$product = NULL;
		}
		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('supplier')) {
			$supplier = $this->input->get('supplier');
		} else {
			$supplier = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}
		if ($this->input->get('note')) {
			$note = $this->input->get('note');
		} else {
			$note = NULL;
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}


		$this->load->library('datatables');
		if ($warehouse_id) {

			$this->datatables
			->select("companies.id, purchases.date,companies.name,
				SUM(
					IFNULL(grand_total, 0)
				) AS grand_total,
				SUM(
					IFNULL(paid, 0)
				) AS paid,
				SUM(
					IFNULL(grand_total - paid, 0)
				) AS balance,
				COUNT(
					bpas_purchases.id
				) as ap_number
				")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('warehouse_id', $warehouse_id)
			->where('DATE_SUB('. $this->db->dbprefix('purchases')  .'.date, INTERVAL 1 DAY) <= CURDATE()')
			->group_by('purchases.supplier_id');
		} else {
			$this->datatables
			->select("companies.id, purchases.date,companies.name,
				SUM(
					IFNULL(grand_total, 0)
				) AS grand_total,
				SUM(
					IFNULL(paid, 0)
				) AS paid,
				SUM(
					IFNULL(grand_total - paid, 0)
				) AS balance,
				COUNT(
					bpas_purchases.id
				) as ap_number,
				purchases.date
				")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE_SUB('. $this->db->dbprefix('purchases')  .'.date, INTERVAL 1 DAY) <= CURDATE()')
			->group_by('purchases.supplier_id');
			
			
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('payment_status !=','paid')
				->where('purchases.payment_term <>', 0);
			}

		}

		if ($user_query) {
			$this->datatables->where('purchases.created_by', $user_query);
		}

		if ($product) {
			$this->datatables->like('purchase_items.product_id', $product);
		}
		if ($supplier) {
			$this->datatables->where('purchases.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->datatables->where('purchases.warehouse_id', $warehouse);
		}
		if ($reference_no) {
			$this->datatables->like('purchases.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}

		// if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
	  //           $this->datatables->where('purchases.created_by', $this->session->userdata('user_id'));
	  //       }

		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_payable') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}

	public function list_ap_aging_0_30($warehouse_id = null)
	{
		$this->bpas->checkPermissions('index', true,'accounts');
		if ($this->input->get('product')) {
			$product = $this->input->get('product');
		} else {
			$product = NULL;
		}
		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('supplier')) {
			$supplier = $this->input->get('supplier');
		} else {
			$supplier = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}
		if ($this->input->get('note')) {
			$note = $this->input->get('note');
		} else {
			$note = NULL;
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}

		$this->load->library('datatables');
		if ($warehouse_id) {

			$this->datatables
			->select("companies.id, purchases.date,companies.name,
						SUM(
							IFNULL(grand_total, 0)
						) AS grand_total,
						SUM(
							IFNULL(paid, 0)
						) AS paid,
						SUM(
							IFNULL(grand_total - paid, 0)
						) AS balance,
						COUNT(
							bpas_purchases.id
						) as ap_number
						")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY')
			->where('warehouse_id', $warehouse_id)
			->group_by('purchases.supplier_id');
		} else {
			$this->datatables
			->select("companies.id, purchases.date,companies.name,
						SUM(
							IFNULL(grand_total, 0)
						) AS grand_total,
						SUM(
							IFNULL(paid, 0)
						) AS paid,
						SUM(
							IFNULL(grand_total - paid, 0)
						) AS balance,
						COUNT(
							bpas_purchases.id
						) as ap_number
						")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 30 DAY AND curdate() - INTERVAL 0 DAY')
			->group_by('purchases.supplier_id');
			
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));
				$this->datatables
				->where("date >=", $date)
				->where('payment_status !=','paid')
				->where('purchases.payment_term <>', 0);
			}
		}

		// search options

		if ($user_query) {
			$this->datatables->where('purchases.created_by', $user_query);
		}

		if ($product) {
			$this->datatables->like('purchase_items.product_id', $product);
		}
		if ($supplier) {
			$this->datatables->where('purchases.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->datatables->where('purchases.warehouse_id', $warehouse);
		}
		if ($reference_no) {
			$this->datatables->like('purchases.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}

		$this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_payable/0/0/30') . "'><span class=\"label label-primary\">View</span></a></div>");
		echo $this->datatables->generate();
	}


	public function list_ap_aging_30_60($warehouse_id = null)
	{
		$this->bpas->checkPermissions('index', true,'accounts');
		if ($this->input->get('product')) {
			$product = $this->input->get('product');
		} else {
			$product = NULL;
		}
		if ($this->input->get('user')) {
			$user_query = $this->input->get('user');
		} else {
			$user_query = NULL;
		}
		if ($this->input->get('supplier')) {
			$supplier = $this->input->get('supplier');
		} else {
			$supplier = NULL;
		}
		if ($this->input->get('warehouse')) {
			$warehouse = $this->input->get('warehouse');
		} else {
			$warehouse = NULL;
		}
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}
		if ($this->input->get('note')) {
			$note = $this->input->get('note');
		} else {
			$note = NULL;
		}

		if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
			$user = $this->site->getUser();
			$warehouse_id = $user->warehouse_id;
		}

		$this->load->library('datatables');
		if ($warehouse_id) {

			$this->datatables
			->select("companies.id, purchases.date,companies.name,
								SUM(
									IFNULL(grand_total, 0)
								) AS grand_total,
								SUM(
									IFNULL(paid, 0)
								) AS paid,
								SUM(
									IFNULL(grand_total - paid, 0)
								) AS balance, 
								COUNT(
									bpas_purchases.id
								) as ap_number
								")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY')
			->where('warehouse_id', $warehouse_id);
		} else {
			$this->datatables
			->select("companies.id, purchases.date,companies.name,
								SUM(
									IFNULL(grand_total, 0)
								) AS grand_total,
								SUM(
									IFNULL(paid, 0)
								) AS paid,
								SUM(
									IFNULL(grand_total - paid, 0)
								) AS balance,
								COUNT(
									bpas_purchases.id
								) as ap_number
								")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 60 DAY AND curdate() - INTERVAL 30 DAY')
			->group_by('purchases.supplier_id');
			
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('payment_status !=','paid')
				->where('purchases.payment_term <>', 0);
			}
		}

		// search options
		if ($user_query) {
			$this->datatables->where('purchases.created_by', $user_query);
		}

		if ($product) {
			$this->datatables->like('purchase_items.product_id', $product);
		}
		if ($supplier) {
			$this->datatables->where('purchases.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->datatables->where('purchases.warehouse_id', $warehouse);
		}
		if ($reference_no) {
			$this->datatables->like('purchases.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}

        /*if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }*/
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_payable/0/0/60') . "'><span class=\"label label-primary\">View</span></a></div>");
        echo $this->datatables->generate();
    }

    public function list_ap_aging_60_90($warehouse_id = null)
    {
    	$this->bpas->checkPermissions('index', true,'accounts');
    	if ($this->input->get('product')) {
    		$product = $this->input->get('product');
    	} else {
    		$product = NULL;
    	}
    	if ($this->input->get('user')) {
    		$user_query = $this->input->get('user');
    	} else {
    		$user_query = NULL;
    	}
    	if ($this->input->get('supplier')) {
    		$supplier = $this->input->get('supplier');
    	} else {
    		$supplier = NULL;
    	}
    	if ($this->input->get('warehouse')) {
    		$warehouse = $this->input->get('warehouse');
    	} else {
    		$warehouse = NULL;
    	}
    	if ($this->input->get('reference_no')) {
    		$reference_no = $this->input->get('reference_no');
    	} else {
    		$reference_no = NULL;
    	}
    	if ($this->input->get('start_date')) {
    		$start_date = $this->input->get('start_date');
    	} else {
    		$start_date = NULL;
    	}
    	if ($this->input->get('end_date')) {
    		$end_date = $this->input->get('end_date');
    	} else {
    		$end_date = NULL;
    	}
    	if ($start_date) {
    		$start_date = $this->bpas->fld($start_date);
    		$end_date = $this->bpas->fld($end_date);
    	}
    	if ($this->input->get('note')) {
    		$note = $this->input->get('note');
    	} else {
    		$note = NULL;
    	}

    	if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
    		$user = $this->site->getUser();
    		$warehouse_id = $user->warehouse_id;
    	}
       
		$this->load->library('datatables');
		if ($warehouse_id) {

			$this->datatables
			->select("companies.id, purchases.date,companies.name,
								SUM(
									IFNULL(grand_total, 0)
								) AS grand_total,
								SUM(
									IFNULL(paid, 0)
								) AS paid,
								SUM(
									IFNULL(grand_total - paid, 0)
								) AS balance,
								COUNT(
									bpas_purchases.id
								) as ap_number
								")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 90 DAY AND curdate() - INTERVAL 60 DAY')
			->where('warehouse_id', $warehouse_id)
			->group_by('purchases.supplier_id');
		} else {
			$this->datatables
			->select("companies.id, purchases.date,companies.name,
								SUM(
									IFNULL(grand_total, 0)
								) AS grand_total,
								SUM(
									IFNULL(paid, 0)
								) AS paid,
								SUM(
									IFNULL(grand_total - paid, 0)
								) AS balance,
								COUNT(
									bpas_purchases.id
								) as ap_number
								")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 90 DAY AND curdate() - INTERVAL 60 DAY')
			->group_by('purchases.supplier_id');
			
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('payment_status !=','paid')
				->where('purchases.payment_term <>', 0);
			}
		}

		// search options
		if ($user_query) {
			$this->datatables->where('purchases.created_by', $user_query);
		}

		if ($product) {
			$this->datatables->like('purchase_items.product_id', $product);
		}
		if ($supplier) {
			$this->datatables->where('purchases.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->datatables->where('purchases.warehouse_id', $warehouse);
		}
		if ($reference_no) {
			$this->datatables->like('purchases.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}

        /*if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }*/
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_payable/0/0/90') . "'><span class=\"label label-primary\">View</span></a></div>");
        echo $this->datatables->generate();
    }

    public function list_ap_aging_over_90($warehouse_id = null)
    {
    	$this->bpas->checkPermissions('index', true,'accounts');
    	if ($this->input->get('product')) {
    		$product = $this->input->get('product');
    	} else {
    		$product = NULL;
    	}
    	if ($this->input->get('user')) {
    		$user_query = $this->input->get('user');
    	} else {
    		$user_query = NULL;
    	}
    	if ($this->input->get('supplier')) {
    		$supplier = $this->input->get('supplier');
    	} else {
    		$supplier = NULL;
    	}
    	if ($this->input->get('warehouse')) {
    		$warehouse = $this->input->get('warehouse');
    	} else {
    		$warehouse = NULL;
    	}
    	if ($this->input->get('reference_no')) {
    		$reference_no = $this->input->get('reference_no');
    	} else {
    		$reference_no = NULL;
    	}
    	if ($this->input->get('start_date')) {
    		$start_date = $this->input->get('start_date');
    	} else {
    		$start_date = NULL;
    	}
    	if ($this->input->get('end_date')) {
    		$end_date = $this->input->get('end_date');
    	} else {
    		$end_date = NULL;
    	}
    	if ($start_date) {
    		$start_date = $this->bpas->fld($start_date);
    		$end_date = $this->bpas->fld($end_date);
    	}
    	if ($this->input->get('note')) {
    		$note = $this->input->get('note');
    	} else {
    		$note = NULL;
    	}

    	if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
    		$user = $this->site->getUser();
    		$warehouse_id = $user->warehouse_id;
    	}

		$this->load->library('datatables');
		if ($warehouse_id) {

			$this->datatables
			->select("companies.id, purchases.date,companies.name,
								SUM(
									IFNULL(grand_total, 0)
								) AS grand_total,
								SUM(
									IFNULL(paid, 0)
								) AS paid,
								SUM(
									IFNULL(grand_total - paid, 0)
								) AS balance,
								COUNT(
									bpas_purchases.id
								) as ap_number
								")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 10000 DAY AND curdate() - INTERVAL 90 DAY')
			->where('warehouse_id', $warehouse_id)
			->group_by('purchases.supplier_id');
		} else {
			$this->datatables
			->select("companies.id, purchases.date,companies.name,
								SUM(
									IFNULL(grand_total, 0)
								) AS grand_total,
								SUM(
									IFNULL(paid, 0)
								) AS paid,
								SUM(
									IFNULL(grand_total - paid, 0)
								) AS balance,
								COUNT(
									bpas_purchases.id
								) as ap_number
								")
			->from('purchases')
			->join('companies', 'companies.id = purchases.supplier_id', 'inner')
			->where('payment_status !=','paid')
			->where('DATE(bpas_purchases.date) BETWEEN curdate() - INTERVAL 10000 DAY AND curdate() - INTERVAL 90 DAY')
			->group_by('purchases.supplier_id');
			
			if(isset($_REQUEST['d'])){
				$date_c = date('Y-m-d', strtotime('+3 months'));
				$date = $_GET['d'];
				$date1 = str_replace("/", "-", $date);
				$date =  date('Y-m-d', strtotime($date1));

				$this->datatables
				->where("date >=", $date)
				->where('payment_status !=','paid')
				->where('purchases.payment_term <>', 0);
			}
		}

		if ($user_query) {
			$this->datatables->where('purchases.created_by', $user_query);
		}

		if ($product) {
			$this->datatables->like('purchase_items.product_id', $product);
		}
		if ($supplier) {
			$this->datatables->where('purchases.supplier_id', $supplier);
		}
		if ($warehouse) {
			$this->datatables->where('purchases.warehouse_id', $warehouse);
		}
		if ($reference_no) {
			$this->datatables->like('purchases.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('purchases').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		if ($note) {
			$this->datatables->like('purchases.note', $note, 'both');
		}

        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("'list_ac_recevable'") . "' href='" . admin_url('account/list_ac_payable/0/0/91') . "'><span class=\"label label-primary\">View</span></a></div>");
        echo $this->datatables->generate();
    }

    function list_ap_aging_actions($wh = null, $condition = null)
	{
		if($wh){
			$wh = explode('-', $wh);
		}

		$form_action = '';
		if ($condition == '0_30') {
			$this->form_validation->set_rules('form_action2', lang("form_action"), 'required');	
			$form_action = $this->input->post('form_action2');
		} elseif ($condition == '30_60') {
			$this->form_validation->set_rules('form_action3', lang("form_action"), 'required');
			$form_action = $this->input->post('form_action3');	
		} elseif ($condition == '60_90') {
			$this->form_validation->set_rules('form_action4', lang("form_action"), 'required');	
			$form_action = $this->input->post('form_action4');
		} elseif ($condition == '90_over') {
			$this->form_validation->set_rules('form_action5', lang("form_action"), 'required');	
			$form_action = $this->input->post('form_action5');
		} else {
			$this->form_validation->set_rules('form_action', lang("form_action"), 'required');
			$form_action = $this->input->post('form_action');	
		} 
		
		// $this->form_validation->set_rules('form_action', lang("form_action"), 'required');	
		if ($this->form_validation->run() == true) {
			$ware    = $this->input->post('warehouse2');
			$created = $this->input->post('created_by2');
			$biller  = $this->input->post('biller2');
			if($this->input->post('start_date2')){
				$Sdate = $this->bpas->fld($this->input->post('start_date2'));
			}else{
				$Sdate = null;
			}
			if($this->input->post('end_date2')){
				$Edate = $this->bpas->fld($this->input->post('end_date2'));
			}else{
				$Edate = null;
			}		
			
    		if (!empty($_POST['val'])) {
        		if ($form_action == 'export_excel'|| $form_action == 'export_pdf') {
        			if($this->Owner || $this->Admin){
	        			$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('supplier'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));
						 $styleArray = array(
	                        'font'  => array(
	                            'bold'  => true
	                        )
	                    );
	                    
	                    $this->excel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);
	                   	$row = 2; 
	                   	$sum_grand = $sum_paid = $sum_balance = $sum_arNum = 0;

						foreach ($_POST['val'] as $id) {						
							$account = $this->site->getAPaging($id, $ware, $created, $biller, $Sdate, $Edate, null, $condition);
							$sum_grand += $account->grand_total;
							$sum_paid += $account->paid;
							$sum_balance += $account->balance;
							$sum_arNum += $account->ap_number;
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->supplier);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($account->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($account->paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($account->balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ap_number);
							$new_row = $row+1;
							$this->excel->getActiveSheet()->SetCellValue('B' . $new_row, $this->bpas->formatDecimal($sum_grand));
							$this->excel->getActiveSheet()->SetCellValue('C' . $new_row, $this->bpas->formatDecimal($sum_paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $new_row, $this->bpas->formatDecimal($sum_balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $new_row, $sum_arNum);

							$row++;				
	                	}
	                } else {
	                	$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('list_ap_aging'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('supplier'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));
						$styleArray = array(
	                        'font'  => array(
	                            'bold'  => true
	                        )
	                    );
	                    
	                    $this->excel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);
	                   	$row = 2; 
	                   	$sum_grand = $sum_paid = $sum_balance = $sum_arNum = 0;
						foreach ($_POST['val'] as $id) {						
							$account = $this->site->getARaging($id, $ware, $created, $biller, $Sdate, $Edate, $wh, null, $condition);
							$sum_grand += $account->grand_total;
							$sum_paid += $account->paid;
							$sum_balance += $account->balance;
							$sum_arNum += $account->ap_number;
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->supplier);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($account->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($account->paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($account->balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ap_number);
							$new_row = $row+1;
							$this->excel->getActiveSheet()->SetCellValue('B' . $new_row, $this->bpas->formatDecimal($sum_grand));
							$this->excel->getActiveSheet()->SetCellValue('C' . $new_row, $this->bpas->formatDecimal($sum_paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $new_row, $this->bpas->formatDecimal($sum_balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $new_row, $sum_arNum);

							$row++;				
	                	}
	                }

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'list_ap_aging_' . date('Y_m_d_H_i_s');
					if ($form_action == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($form_action == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	function list_ap_aging_actions2($wh = null)
	{
		if($wh){
			$wh = explode('-', $wh);
		}
		$this->form_validation->set_rules('form_action2', lang("form_action"), 'required');	
		if ($this->form_validation->run() == true) {
			$ware    = $this->input->post('warehouse2');
			$created = $this->input->post('created_by2');
			$biller  = $this->input->post('biller2');
			if($this->input->post('start_date2')){
				$Sdate = $this->bpas->fld($this->input->post('start_date2'));
			}else{
				$Sdate = null;
			}
			if($this->input->post('end_date2')){
				$Edate = $this->bpas->fld($this->input->post('end_date2'));
			} else {
				$Edate = null;
			}		
			
    		if (!empty($_POST['val'])) {
        		if ($this->input->post('form_action2') == 'export_excel2'|| $this->input->post('form_action2') == 'export_pdf2') {
        			if($this->Owner || $this->Admin){
	        			$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('supplier'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));
						 $styleArray = array(
	                        'font'  => array(
	                            'bold'  => true
	                        )
	                    );
	                    
	                    $this->excel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);
	                   	$row = 2; 
	                   	$sum_grand = $sum_paid = $sum_balance = $sum_arNum = 0;

						foreach ($_POST['val'] as $id) {						
							$account = $this->site->getAPaging($id, $ware, $created, $biller, $Sdate, $Edate);
							$sum_grand += $account->grand_total;
							$sum_paid += $account->paid;
							$sum_balance += $account->balance;
							$sum_arNum += $account->ap_number;
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->supplier);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($account->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($account->paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($account->balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ap_number);
							$new_row = $row+1;
							$this->excel->getActiveSheet()->SetCellValue('B' . $new_row, $this->bpas->formatDecimal($sum_grand));
							$this->excel->getActiveSheet()->SetCellValue('C' . $new_row, $this->bpas->formatDecimal($sum_paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $new_row, $this->bpas->formatDecimal($sum_balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $new_row, $sum_arNum);

							$row++;				
	                	}
	                } else {
	                	$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('list_ap_aging'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('supplier'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));
						$styleArray = array(
	                        'font'  => array(
	                            'bold'  => true
	                        )
	                    );
	                    
	                    $this->excel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);
	                   	$row = 2; 
	                   	$sum_grand = $sum_paid = $sum_balance = $sum_arNum = 0;
						foreach ($_POST['val'] as $id) {						
							$account = $this->site->getARaging($id,$ware,$created,$biller,$Sdate,$Edate,$wh);
							$sum_grand += $account->grand_total;
							$sum_paid += $account->paid;
							$sum_balance += $account->balance;
							$sum_arNum += $account->ap_number;
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->supplier);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($account->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($account->paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($account->balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ap_number);
							$new_row = $row+1;
							$this->excel->getActiveSheet()->SetCellValue('B' . $new_row, $this->bpas->formatDecimal($sum_grand));
							$this->excel->getActiveSheet()->SetCellValue('C' . $new_row, $this->bpas->formatDecimal($sum_paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $new_row, $this->bpas->formatDecimal($sum_balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $new_row, $sum_arNum);

							$row++;				
	                	}
	                }

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'list_ap_aging_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action2') == 'export_pdf2') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action2') == 'export_excel2') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}

    function payment_note($id = NULL)
    {
    	$this->load->admin_model('sales_model');
    	$payment = $this->sales_model->getPaymentByID($id);
    	$inv = $this->sales_model->getInvoiceByID($payment->sale_id);
    	$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    	$this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
    	$this->data['inv'] = $inv;
    	$this->data['payment'] = $payment;
		$this->data['rowpay'] = $this->sales_model->getPayments($payment->reference_no);
    	$this->data['page_title'] = $this->lang->line("payment_note");
    	$this->load->view($this->theme . 'accounts/payment_note', $this->data);
    }

	function bill_reciept_form($id = NULL){
		$this->load->admin_model('sales_model');
    	$payment = $this->sales_model->getPaymentByID($id);
    	$inv = $this->sales_model->getInvoiceByID($payment->sale_id);
    	$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    	$this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
    	$this->data['inv'] = $inv;
    	$this->data['payment'] = $payment;
		$this->data['products'] = $this->sales_model->getProductNew($payment->sale_id);
		$this->data['jl_data'] = $this->sales_model->getJoinlease($payment->sale_id);
		$this->data['rowpay'] = $this->sales_model->getPayments($payment->reference_no);
		$this->load->view($this->theme . 'accounts/bill_reciept_form', $this->data);
	}
	
	function bill_reciept_tps($id = NULL){
		$this->load->admin_model('sales_model');
    	$payment = $this->sales_model->getPaymentByID($id);
    	$inv = $this->sales_model->getInvoiceByID($payment->sale_id);
    	$this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
    	$this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
    	$this->data['inv'] = $inv;
    	$this->data['payment'] = $payment;
		$this->data['products'] = $this->sales_model->getProductNew($payment->sale_id);
		$this->data['jl_data'] = $this->sales_model->getJoinlease($payment->sale_id);
		$this->data['rowpay'] = $this->sales_model->getPayments($payment->reference_no);
		$this->load->view($this->theme . 'accounts/bill_reciept_tps', $this->data);
	}
	
    function purchase_note($id = NULL)
    {
    	$this->load->admin_model('sales_model');
    	$purchase = $this->sales_model->getPurchaseByID($id);
    	$inv = $this->sales_model->getInvoiceByID($purchase->id);
    	$this->data['biller'] = $this->site->getCompanyByID($purchase->biller_id);
    	$this->data['customer'] = $this->site->getCompanyByID($purchase->supplier_id);
    	$this->data['inv'] = $inv;
    	$this->data['payment'] = $purchase;
    	$this->data['page_title'] = $this->lang->line("purchase_note");

    	$this->load->view($this->theme . 'accounts/purchase_note', $this->data);
    }

    function account_head($id = NULL)
    {
		$this->data['id'] = $id;
		$this->data['page_title'] = $this->lang->line("account_head");
		$this->load->view($this->theme . 'accounts/account_head', $this->data);
	}
	
	function dataLedger()
	{
		$output = "";
		$start_date = $this->bpas->fsd($_GET['start_date']);
		$end_date = $this->bpas->fsd($_GET['end_date']);
		$id = $_GET['id'];
		$this->db->select('*')->from('gl_charts');
		$this->db->where('accountcode', $id);
		
		$acc = $this->db->get()->result();
		foreach($acc as $val){
			$gl_tranStart = $this->db->select('sum(amount) as startAmount')->from('gl_trans');
			$gl_tranStart->where(array('tran_date < '=> $this->bpas->fld($this->input->post('start_date')), 'account_code'=> $val->accountcode));
			$startAmount = $gl_tranStart->get()->row();
			
			$endAccountBalance = 0;
			$getListGLTran = $this->db->select("*")->from('gl_trans')->where('account_code =', $val->accountcode);
			if ($this->input->post('start_date')) {
				$getListGLTran->where('tran_date >=', $this->bpas->fld($this->input->post('start_date')) );
			}
			if ($this->input->post('end_date')) {
				$getListGLTran->where('tran_date <=', $this->bpas->fld($this->input->post('end_date')) );
			}
			$gltran_list = $getListGLTran->get()->result();
			if($gltran_list) 
			{
				$output.='<tr>';
				$output.='<td colspan="4">Account:'.$val->accountcode . ' ' .$val->accountname.'</td>';
				$output.='<td colspan="2">Begining Account Balance: </td>';
				$output.='<td colspan="2" style="text-align: center;">';
				$output.='$'.abs($startAmount->startAmount);
				$output.='</td>';
				$output.='</tr>';
				foreach($gltran_list as $rw)
				{
					$endAccountBalance += $rw->amount; 
					$output.='<tr>';
					$output.='<td>'.$rw->tran_id.'</td>';
					$output.='<td>'.$rw->reference_no.'</td>';
					$output.='<td>'.$rw->tran_no.'</td>';
					$output.='<td>'.$rw->narrative.'</td>';
					$output.='<td>'.$rw->tran_date.'</td>';
					$output.='<td>'.$rw->tran_type.'</td>';
					$output.='<td>'.($rw->amount > 0 ? $rw->amount : '0.00').'</td>';
					$output.='<td>'.($rw->amount < 1 ? abs($rw->amount) : '0.00').'</td>';
					$output.='</tr>';
				}
				$output.='<tr>';
				$output.='<td colspan="4"> </td>';
				$output.='<td colspan="2">Ending Account Balance: </td>';
				$output.='<td colspan="2">$ '.abs($endAccountBalance).'</td>';
				$output.='</tr>';
			}else{
				$output.='<tr>';
				$output.='<td colspan="8" class="dataTables_empty">No Data</td>';
				$output.='</tr>';
			}
		}
		echo json_encode($output);
	}
	
	function billPayable()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		$this->load->admin_model('reports_model');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['users'] = $this->reports_model->getStaff();
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('accounts'), 'page' => lang('accounts')), array('link' => '#', 'page' => lang('Bill Payable Report')));
		$meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
		$this->page_construct('accounts/bill_payable', $meta, $this->data);
	}
	
	function getBillPaymentReport($pdf = NULL, $xls = NULL)
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		if ($this->input->get('user')) {
			$user = $this->input->get('user');
		} else {
			$user = NULL;
		}
		if ($this->input->get('supplier')) {
			$supplier = $this->input->get('supplier');
		} else {
			$supplier = NULL;
		}
		if ($this->input->get('customer')) {
			$customer = $this->input->get('customer');
		} else {
			$customer = NULL;
		}
		if ($this->input->get('biller')) {
			$biller = $this->input->get('biller');
		} else {
			$biller = NULL;
		}
		if ($this->input->get('payment_ref')) {
			$payment_ref = $this->input->get('payment_ref');
		} else {
			$payment_ref = NULL;
		}
		if ($this->input->get('sale_ref')) {
			$sale_ref = $this->input->get('sale_ref');
		} else {
			$sale_ref = NULL;
		}
		if ($this->input->get('purchase_ref')) {
			$purchase_ref = $this->input->get('purchase_ref');
		} else {
			$purchase_ref = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fsd($start_date);
			$end_date = $this->bpas->fsd($end_date);
		}
		if (!$this->Owner && !$this->Admin) {
			$user = $this->session->userdata('user_id');
		}
		if ($pdf || $xls) {

			$this->db
			->select($this->db->dbprefix('purchases') . ".id, 
				" . $this->db->dbprefix('purchases') . ".date,
				" . $this->db->dbprefix('purchases') . ".reference_no,
				" . $this->db->dbprefix('purchases') . ".supplier as purchase_ref,
				" . $this->db->dbprefix('payments') . ".paid_by,
				" . $this->db->dbprefix('payments') . ".note,
				" . $this->db->dbprefix('purchases') . ".paid,
				" . $this->db->dbprefix('purchases') . ".payment_status")
			->from('purchases')
			->JOIN('payments','purchases.id=payments.purchase_id','left');
                //->group_by('purchases.id');
			if ($this->permission['accounts-index'] = ''){
				if ($user) {
					$this->db->where('payments.created_by', $user);
				}
			}
			if ($customer) {
				$this->db->where('sales.customer_id', $customer);
			}
			if ($supplier) {
				$this->db->where('purchases.supplier_id', $supplier);
			}
			if ($biller) {
				$this->db->where('sales.biller_id', $biller);
			}
			if ($customer) {
				$this->db->where('sales.customer_id', $customer);
			}
			if ($payment_ref) {
				$this->db->like('payments.reference_no', $payment_ref, 'both');
			}
			if ($sale_ref) {
				$this->db->like('sales.reference_no', $sale_ref, 'both');
			}
			if ($purchase_ref) {
				$this->db->like('purchases.reference_no', $purchase_ref, 'both');
			}
			if ($start_date) {
				$this->db->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}

			$q = $this->db->get();
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					$data[] = $row;
				}
			} else {
				$data = NULL;
			}

			if (!empty($data)) {

				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('bill_payable'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('paid'));
                //$this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));

				$row = 2;
				$total = 0;
				$paid=0;
				foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->supplier);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, lang($data_row->paid_by));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
					$paid+=$data_row->paid;
					$total+=$data_row->grand_total;
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("F" . $row)->getBorders()
				->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
				$this->excel->getActiveSheet()->setCellValue('E'.$row,$paid);
				$this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$filename = 'payments_report';
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				if ($pdf) {
					$styleArray = array(
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
								)
							)
						);
					$this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
					$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
					$rendererLibrary = 'MPDF';
					$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
					if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
						die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
							PHP_EOL . ' as appropriate for your directory structure');
					}

					header('Content-Type: application/pdf');
					header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
					header('Cache-Control: max-age=0');

					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
					$objWriter->save('php://output');
					exit();
				}
				if ($xls) {
					ob_clean();
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
					header('Cache-Control: max-age=0');
					ob_clean();
					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
					$objWriter->save('php://output');
					exit();
				}

			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);

		} else {

			$this->load->library('datatables');
			$this->datatables
			->select($this->db->dbprefix('payments') . ".id as pid, 
				" . $this->db->dbprefix('purchases') . ".date,
				" . $this->db->dbprefix('purchases') . ".reference_no as payment_ref,
				" . $this->db->dbprefix('purchases') . ".supplier as purchase_ref,
				" . $this->db->dbprefix('payments') . ".paid_by,
				" . $this->db->dbprefix('payments') . ".note,
				" . $this->db->dbprefix('payments') . ".amount, 
				'paid' as payment_status")
			->from('purchases')
			->where('purchases.paid != 0')
			->JOIN('payments','purchases.id=payments.purchase_id','left');
                //->group_by('purchases.id');
			if ($this->permission['accounts-index'] = ''){
				if ($user) {
					$this->datatables->where('payments.created_by', $user);
				}
			}
			if ($customer) {
				$this->datatables->where('sales.customer_id', $customer);
			}
			if ($supplier) {
				$this->datatables->where('purchases.supplier_id', $supplier);
			}
			if ($biller) {
				$this->datatables->where('sales.biller_id', $biller);
			}
			if ($customer) {
				$this->datatables->where('sales.customer_id', $customer);
			}
			if ($payment_ref) {
				$this->datatables->like('payments.reference_no', $payment_ref, 'both');
			}
			if ($sale_ref) {
				$this->datatables->like('sales.reference_no', $sale_ref, 'both');
			}
			if ($purchase_ref) {
				$this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
			}
			if ($start_date) {
				$this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
			}

			echo $this->datatables->generate();

		}

	}
	
	function listJournal($biller_id = NULL)
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['biller_id'] = $this->session->userdata('biller_id');
		if($this->Owner || $this->Admin){
			$this->data['billers'] = $this->site->getAllBiller();
            $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		}else{
			if(!$this->session->userdata('biller_id')){
				$this->data['billers'] = $this->site->getAllBiller();
			}else{
				$this->data['billers'] = null;
			}
			$this->data['biller'] = $this->session->userdata('biller_id') ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
		}

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('Account'), 'bc' => $bc);
		$this->page_construct('accounts/list_journal', $meta, $this->data);
	}
	function getJournalList($biller_id = NULL)
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
			
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}

		 $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_enter_journal") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" .admin_url('account/delete_enter_journal/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
		
        $this->load->library('datatables');
        $this->datatables
            ->select("
            	{$this->db->dbprefix('account_journals')}.id as id, 
            	{$this->db->dbprefix('account_journals')}.date, 
            	reference_no,
            	companies.name, 
            	CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, note, account_journals.attachment")
            ->from('account_journals')
			->join('companies', 'companies.id=account_journals.biller_id', 'left')
			->join('projects', 'projects.project_id=account_journals.project_id', 'left')
            ->join('users', 'users.id=account_journals.created_by', 'left')
            ->group_by("account_journals.id");
		
     

		if ($biller_id) {
            $this->datatables->where('account_journals.biller_id', $biller_id);
        }

		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('account_journals.biller_id =', $this->session->userdata('biller_id'));
		}

		if ($reference_no) {
			$this->datatables->like('gt.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where('gt.tran_date BETWEEN "' . $start_date .' 00:00:00" AND "' . $end_date .' 23:59:00"');
		}
		if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('gt.created_by', $this->session->userdata('user_id'));
        }
        
		$this->datatables->add_column("Actions", "<div class='text-center'><a href='" . admin_url('account/edit_enter_journal/$1') . "' class='tip' title='" . lang("edit_enter_journal") . "'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "id");

		echo $this->datatables->generate();
	}
	public function view_enterjournal($id)
    {
        $this->bpas->checkPermissions('enter_journals', TRUE);

        $enter_journals = $this->accounts_model->getEnterJournalByID($id);
        if (!$id || !$enter_journals) {
            $this->session->set_flashdata('error', lang('enter_journal_not_found'));
            $this->bpas->md();
        }

        $this->data['inv'] = $enter_journals;
		$this->data['biller'] = $this->site->getCompanyByID($enter_journals->biller_id);
        $this->data['rows'] = $this->accounts_model->getEnterJournalItems($id);
        $this->data['created_by'] = $this->site->getUser($enter_journals->created_by);
        $this->data['updated_by'] = $this->site->getUser($enter_journals->updated_by);
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Enter Journal',$enter_journals->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Enter Journal',$enter_journals->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
		
        $this->load->view($this->theme.'accounts/view_enter_journal', $this->data);
    }
	function add_enter_journal()
    {
        $this->bpas->checkPermissions('enter_journals-add', true);
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$jb_type = $this->input->post('jn_type');
			$type = $this->input->post('type');
			$customer_id = $this->input->post('customer');
			$supplier_id = $this->input->post('supplier');
            if ($this->Owner || $this->Admin || $this->bpas->GP['accountings-enter_journals-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			if($jb_type=='journal'){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('jr',$biller_id);
				$narrative = 'Enter Journal';
			}else if($jb_type=='payment'){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$biller_id);
				$narrative = 'Enter Payment Journal';
			}else{
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$biller_id);
				$narrative = 'Enter Receipt Journal';
			}
			$variant = 0;
            $note = $this->bpas->remove_tags($this->input->post('note'));
            $i = isset($_POST['account_code']) ? sizeof($_POST['account_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $account_code = $_POST['account_code'][$r];
				$account_name = $_POST['account_name'][$r];
				$account_id = $_POST['account_id'][$r];
				$description = $_POST['description'][$r];
				$debit = $_POST['debit'][$r];
				$credit = $_POST['credit'][$r];
				if($debit > 0){
					$amount = $debit;
				}else{
					$amount = $credit * (-1);
				}
				$amount = $this->bpas->formatDecimal($amount);
				$variant += $amount;
                $items[] = array(
					'account_id' => $account_id,
                    'account_code' => $account_code,
					'account_name' =>$account_name,
					'amount' =>$amount,
					'description' =>$description,
                    );
				
				if($type=='customer'){
					$accTrans[] = array(
						'tran_type' => 'EnterJournal',
						'tran_date' => $date,
						'reference_no' => $reference_no,
						'account_code' => $account_code,
						'amount' => $amount,
						'narrative' => $narrative,
						'description' => $description,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'customer_id' => $customer_id
					);
				}else if($type=='supplier'){
					$accTrans[] = array(
						'tran_type' => 'EnterJournal',
						'tran_date' => $date,
						'reference_no' => $reference_no,
						'account_code' => $account_code,
						'amount' => $amount,
						'narrative' => $narrative,
						'description' => $description,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
						'supplier_id' => $supplier_id
					);
				}else{		
					$accTrans[] = array(
						'tran_type' => 'EnterJournal',
						'tran_date' => $date,
						'reference_no' => $reference_no,
						'account_code' => $account_code,
						'amount' => $amount,
						'narrative' => $narrative,
						'description' => $description,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
					);
				}
            }
            if (empty($items) || $this->bpas->formatDecimal($variant) > 0) {
                $this->form_validation->set_rules('account', lang("account"), 'required');
            } else {
                krsort($items);
            }
			if($type=='customer'){
				$data = array(
								'date' => $date,
								'jn_type' => $jb_type,
								'reference_no' => $reference_no,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'note' => $note,
								'created_by' => $this->session->userdata('user_id'),
								'type' => $type,
								'customer_id' => $customer_id,
								);
			}else if($type=='supplier'){
				$data = array(
								'date' => $date,
								'jn_type' => $jb_type,
								'reference_no' => $reference_no,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'note' => $note,
								'created_by' => $this->session->userdata('user_id'),
								'type' => $type,
								'supplier_id' => $supplier_id,
								);
			}else{
				$data = array(
								'date' => $date,
								'jn_type' => $jb_type,
								'reference_no' => $reference_no,
								'biller_id' => $biller_id,
								'project_id' => $project_id,
								'note' => $note,
								'created_by' => $this->session->userdata('user_id'),
								'type' => $type,
								);
			}
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
		
        if ($this->form_validation->run() == true && $this->accounts_model->addEnterJournal($data, $items, $accTrans)) {
            $this->session->set_userdata('remove_jnls', 1);
			$this->session->set_flashdata('message', lang("enter_journal_added"));
            admin_redirect('account/listJournal');
        } else {			
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['projects']		= $this->site->getAllProjects();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('accountings'), 'page' => lang('accounting')),array('link' => admin_url('account/listJournal'), 'page' => lang('enter_journals')), array('link' => '#', 'page' => lang('add_enter_journal')));
            $meta = array('page_title' => lang('add_enter_journal'), 'bc' => $bc);
            $this->page_construct('accounts/add_enter_journal', $meta, $this->data);
        }
    }
	
	function edit_enter_journal($id)
    {
        $this->bpas->checkPermissions('enter_journals-edit', true);
        $enter_journal = $this->accounts_model->getEnterJournalByID($id);
        if (!$id || !$enter_journal) {
            $this->session->set_flashdata('error', lang('enter_journal_not_found'));
            $this->bpas->md();
        }
        $this->form_validation->set_rules('biller', lang("biller"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
			$project_id = $this->input->post('project');
			$jb_type = $this->input->post('jn_type');
			
			$type = $this->input->post('type');
			$customer_id = $this->input->post('customer');
			$supplier_id = $this->input->post('supplier');
            if ($this->Owner || $this->Admin || $this->bpas->GP['accountings-enter_journals-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			if($jb_type=='journal'){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('jr',$biller_id);
				$narrative = 'Enter Journal';
			}else if($jb_type=='payment'){
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$biller_id);
				$narrative = 'Enter Payment Journal';
			}else{
				$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$biller_id);
				$narrative = 'Enter Receipt Journal';
			}
			
			$variant = 0;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $i = isset($_POST['account_code']) ? sizeof($_POST['account_code']) : 0;
			
            for ($r = 0; $r < $i; $r++) {
				$account_id = $_POST['account_id'][$r];
                $account_code = $_POST['account_code'][$r];
				$account_name = $_POST['account_name'][$r];
				$debit = $_POST['debit'][$r];
				$credit = $_POST['credit'][$r];
				$description = $_POST['description'][$r];
				
				if($debit > 0){
					$amount = $debit;
				}else{
					$amount = $credit * (-1);
				}
				$amount = $this->bpas->formatDecimal($amount);
				$variant += $amount;
                $items[] = array(
                    'journal_id' => $id,
					'account_id' => $account_id,
					'account_code' => $account_code,
					'account_name' =>$account_name,
					'amount' =>$amount,
					'description' =>$description,
                    );
				if($type=='customer'){
					$accTrans[] = array(
						'tran_type' => 'EnterJournal',
						'tran_no' => $id,
						'tran_date' => $date,
						'reference_no' => $reference_no,
						'account_code' => $account_code,
						'amount' => $amount,
						'narrative' => $narrative,
						'description' => $description,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'customer_id' => $customer_id,
						'created_by' => $this->session->userdata('user_id'),
					);
				}else if($type=='supplier'){
					$accTrans[] = array(
						'tran_type' => 'EnterJournal',
						'tran_no' => $id,
						'tran_date' => $date,
						'reference_no' => $reference_no,
						'account_code' => $account_code,
						'amount' => $amount,
						'narrative' => $narrative,
						'description' => $description,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'supplier_id' => $supplier_id,
						'created_by' => $this->session->userdata('user_id'),
					);
				}else{
					$accTrans[] = array(
						'tran_type' => 'EnterJournal',
						'tran_no' => $id,
						'tran_date' => $date,
						'reference_no' => $reference_no,
						'account_code' => $account_code,
						'amount' => $amount,
						'narrative' => $narrative,
						'description' => $description,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'created_by' => $this->session->userdata('user_id'),
					);
				}
				
            }

            if (empty($items) || $this->bpas->formatDecimal($variant) > 0) {
                $this->form_validation->set_rules('account', lang("account"), 'required');
            } else {
                krsort($items);
            }
			
			if($type=='customer'){
				$data = array(
							'date' => $date,
							'jn_type' => $jb_type,
							'reference_no' => $reference_no,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'note' => $note,
							'updated_by' => $this->session->userdata('user_id'),
							'updated_at' => date('Y-m-d H:i:s'),
							'type' => $type,
							'customer_id' => $customer_id,
							'supplier_id' => '',
							);
			}else if($type=='supplier'){
				$data = array(
							'date' => $date,
							'jn_type' => $jb_type,
							'reference_no' => $reference_no,
							'biller_id' => $biller_id,
							'project_id' => $project_id,
							'note' => $note,
							'updated_by' => $this->session->userdata('user_id'),
							'updated_at' => date('Y-m-d H:i:s'),
							'type' => $type,
							'supplier_id' => $supplier_id,
							'customer_id' => '',
							);
			}else{
				$data = array(
						'date' => $date,
						'jn_type' => $jb_type,
						'reference_no' => $reference_no,
						'biller_id' => $biller_id,
						'project_id' => $project_id,
						'note' => $note,
						'updated_by' => $this->session->userdata('user_id'),
						'updated_at' => date('Y-m-d H:i:s'),
						'type' => $type,
						'customer_id' => '',
						'supplier_id' => '',
						);
			}
			
            

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->accounts_model->updateEnterJournal($id, $data, $items, $accTrans)) {
            $this->session->set_userdata('remove_jnls', 1);
			$this->session->set_flashdata('message', lang("enter_journal_edited"));
			admin_redirect('account/listJournal');
        } else {
			
            $journal_items = $this->accounts_model->getEnterJournalItems($id);
			
            krsort($journal_items);
            $c = rand(100000, 9999999);
            foreach ($journal_items as $item) {
                $row = json_decode('{}');
                $row->id = $item->account_id;
                $row->code = $item->account_code;
                $row->name = $item->account_name;
				$row->description = $item->description;
				if($item->amount < 0){
					$row->credit = abs($item->amount)-0;
					$row->debit = 0;
				}else{
					$row->credit = 0;
					$row->debit = $item->amount-0;
				}
				
                $pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row);
                $c++;
            }
			
            $this->data['enter_journal'] = $enter_journal;
            $this->data['journal_items'] = json_encode($pr);
            $this->data['projects']		= $this->site->getAllProjects();
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('accountings'), 'page' => lang('accounting')),array('link' => admin_url('account/listJournal'), 'page' => lang('enter_journals')), array('link' => '#', 'page' => lang('edit_enter_journal')));
            $meta = array('page_title' => lang('edit_enter_journal'), 'bc' => $bc);
            $this->page_construct('accounts/edit_enter_journal', $meta, $this->data);

        }
    }
	
	function delete_enter_journal($id = NULL)
    {
        $this->bpas->checkPermissions('enter_journals-delete', true);

        if ($this->accounts_model->deleteEnterJournal($id)) {
        	$this->bpas->send_json(['error' => 0, 'msg' => lang('enter_journal_deleted')]);
        }

    }
    function enter_journal_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {

                    $this->bpas->checkPermissions('enter_journals-delete', true);
                    foreach ($_POST['val'] as $id) {
                        $this->accounts_model->deleteEnterJournal($id);
                    }
                    $this->session->set_flashdata('message', lang("enter_journal_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('enter_journal');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('project'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('items'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $enter_journal = $this->accounts_model->getEnterJournalByID($id);
						
                        $created_by = $this->site->getUser($enter_journal->created_by);
						$biller = $this->site->getCompanyByID($enter_journal->biller_id);
						$project = $this->site->getProjectByID($enter_journal->project_id);
						$items = $this->accounts_model->getEnterJournalItems($id);  
                        $products = '';
                        if ($items) {
                            foreach ($items as $item) {
                                $products .= $item->account_code.' - '.$item->account_name. ' ('.$item->amount.') ' ."\n";
                            }
                        }

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($enter_journal->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $enter_journal->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $biller->company);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $project->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->decode_html($enter_journal->note));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $products);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'enter_journal_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	function transactions()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['biller_id'] = $this->session->userdata('biller_id');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('Account'), 'bc' => $bc);
		$this->page_construct('accounts/list_transactions', $meta, $this->data);
	}

	function getTransactions()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
			
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}

		$this->load->library('datatables');
		$this->datatables
		->select("gt.tran_id, gt.tran_no AS g_tran_no, gt.tran_type, gt.tran_date, 
			gt.reference_no, companies.company, 
			(
				CASE
				WHEN gt.tran_type = 'SALES' THEN
					(
						SELECT
							bpas_companies.name
						FROM
							bpas_sales
						INNER JOIN bpas_companies ON bpas_companies.id = bpas_sales.customer_id
						WHERE
							bpas_sales.reference_no = gt.reference_no
						LIMIT 0,1
					)
				WHEN gt.tran_type = 'PURCHASES' THEN
					(
						SELECT
							bpas_companies.name
						FROM
							bpas_purchases
						INNER JOIN bpas_companies ON bpas_companies.id = bpas_purchases.supplier_id
						WHERE
							bpas_purchases.reference_no = gt.reference_no
						LIMIT 0,1
					)
				WHEN gt.tran_type = 'SALES-RETURN' THEN
					(
						SELECT
							bpas_return_sales.customer
						FROM
							bpas_return_sales
						WHERE
							bpas_return_sales.reference_no = gt.reference_no
						LIMIT 0,1
					)
				WHEN gt.tran_type = 'PURCHASES-RETURN' THEN
					(
						SELECT
							bpas_return_purchases.supplier
						FROM
							bpas_return_purchases
						WHERE
							bpas_return_purchases.reference_no = gt.reference_no
						LIMIT 0,1
					)
				WHEN gt.tran_type = 'DELIVERY' THEN
					(
						SELECT
							bpas_deliveries.customer
						FROM
							bpas_deliveries
						WHERE
							bpas_deliveries.do_reference_no = gt.reference_no
						LIMIT 0,1
					)
				ELSE
					created_name
				END
			) AS name, 
			CONCAT({$this->db->dbprefix('gl_charts')}.accountcode, ' ', {$this->db->dbprefix('gl_charts')}.accountname) as account,
			gt.narrative,
			gt.note as note,
			gt.description as description,
			(IF(gt.amount > 0, gt.amount, IF(gt.amount = 0, 0, null))) as debit, 
			(IF(gt.amount < 0, abs(gt.amount), null)) as credit,
			users.username,
			")
		->from("gl_trans gt")
		->join('companies', 'companies.id = (gt.biller_id)', 'left')
		->join('gl_charts', 'gl_charts.accountcode = (gt.account_code)', 'left')
		->join('users', 'users.id = (gt.created_by)', 'left')
		->order_by('gt.tran_id','DESC');


		$this->datatables->where('gt.tran_type !=','JOURNAL');
		
		/*if($this->session->userdata('biller_id')){
			$this->datatables->where('gt.biller_id',$this->session->userdata('biller_id'));
		}*/
	
		if ($reference_no) {
			$this->datatables->like('gt.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where('gt.tran_date BETWEEN "' . $start_date .' 00:00:00" AND "' . $end_date .' 23:59:00"');
		}
		if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('gt.created_by', $this->session->userdata('user_id'));
        }
        
		$this->datatables->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_journal") . "' href='" . admin_url('account/edit_journal/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a></center>", "g_tran_no");        
		echo $this->datatables->generate();
	}
	
	function tansfer_payment_report()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['biller_id'] = $this->session->userdata('biller_id');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('Account'), 'bc' => $bc);
		$this->page_construct('accounts/payment_transfer_report', $meta, $this->data);
	}
	function getPaymenttransferReport()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');
		if ($this->input->get('reference_no')) {
			$reference_no = $this->input->get('reference_no');
		} else {
			$reference_no = NULL;
		}
		if ($this->input->get('start_date')) {
			$start_date = $this->input->get('start_date');
			
		} else {
			$start_date = NULL;
		}
		if ($this->input->get('end_date')) {
			$end_date = $this->input->get('end_date');
		} else {
			$end_date = NULL;
		}
		if ($start_date) {
			$start_date = $this->bpas->fld($start_date);
			$end_date = $this->bpas->fld($end_date);
		}
		$this->load->library('datatables');
		$this->datatables->select("
				gt.id, 
				gt.tran_no AS g_tran_no, 
				gt.tran_type, 
				gt.tran_date, 
				gt.reference_no, 
				companies.company, 
				gt.account_code, 
				gt.narrative,
				gt.description as description,
				(IF(bpas_gt.amount > 0, bpas_gt.amount, IF(bpas_gt.amount = 0, 0, null))) as debit, 
				(IF(bpas_gt.amount < 0, abs(bpas_gt.amount), null)) as credit,
				users.username,
			")
		->from("multi_transfer bpas_gt")
		->join('companies', 'companies.id = (bpas_gt.biller_id)', 'left')
		->join('users', 'users.id = bpas_gt.created_by', 'left')
		->order_by('gt.id','DESC');
		/* if ($this->session->userdata('biller_id')) {
				$this->datatables->where('gt.biller_id',$this->session->userdata('biller_id'));
		} */
		if ($reference_no) {
			$this->datatables->like('gt.reference_no', $reference_no, 'both');
		}
		if ($start_date) {
			$this->datatables->where('gt.tran_date BETWEEN "' . $start_date .' 00:00:00" AND "' . $end_date .' 23:59:00"');
		}
		if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('gt.created_by', $this->session->userdata('user_id'));
        }
		$this->datatables->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_journal") . "' href='" . admin_url('account/edit_journal/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a></center>", "g_tran_no");        
		echo $this->datatables->generate();
	}
	public function add_journal()
	{
		$this->bpas->checkPermissions('add', true, 'accounts');
		$this->data['type'] 		= $this->accounts_model->getAlltypes();
		$this->data['sectionacc'] 	= $this->accounts_model->getAccountSections();
		$this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['modal_js'] 	= $this->site->modal_js();
		$this->data['sectionacc'] 	= $this->accounts_model->getAllChartAccount();
		$this->data['billers'] 		= $this->site->getAllCompanies('biller');
		$this->data['customers'] 	= $this->site->getCustomers();
		$this->data['invoices'] 	= $this->site->getCustomerInvoices();
		
		if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
			$biller_id = $this->site->get_setting()->default_biller;
			$this->data['biller_id'] = $biller_id;
			$this->data['reference_no'] = $this->site->getReference('jr',$biller_id);
			
		}else{
			
			$biller_id = $this->session->userdata("biller_id");
			$this->data['biller_id'] = $biller_id;
			$this->data['reference_no'] = $this->site->getReference('jr',$biller_id);
		}
		
		$this->data['rate'] = $this->accounts_model->getKHM();
		$this->load->view($this->theme . 'accounts/add_journal', $this->data);
	}
	
	function save_journal()
	{
		$this->form_validation->set_rules('reference', lang("reference"), 'is_unique[gl_trans.reference_no]');
		if ($this->form_validation->run() == true) {
			$account_code 		= $this->input->post('account_section');
			$biller_id 			= $this->input->post('biller_id');
			$reference_no 		= ($this->input->post('reference')? $this->input->post('reference') : $this->site->getReference('jr',$biller_id));
			$date 				= $this->input->post('date');
			$tran_date 			= strtr($date, '/', '-');
			$tran_date 			= date('Y-m-d h:m', strtotime($tran_date));
			$description 		= $this->input->post('description');
			$note 				= $this->input->post('note');
			$debit 				= $this->input->post('debit');
			$credit 			= $this->input->post('credit');
			$created_by_name 	= $this->input->post('name');
			$created_type 		= $this->input->post('type');
			$sale_id 			= $this->input->post('customer_invoice_no');
			$customer_id 		= $this->input->post('customer_invoice');
			$i = 0;

			if ($created_type == 4) {
				$customer 		= $this->site->getCompanyByName($created_by_name, $created_type);
				$customer_id 	= $customer->id;
			}
			

			$tran_no = $this->accounts_model->getTranNo();
			if($this->accounts_model->getTranByTranNo($tran_no)){
				$this->session->set_flashdata('error', $this->lang->line("duplicate_transaction"));
				admin_redirect('account/listJournal');
			}else{			
				$data = array();
				for($i=0;$i<count($account_code);$i++) {
					if($debit[$i]>0) {
						$amount = $debit[$i];
					}
					elseif($credit[$i]>0) {
						$amount = -$credit[$i];
					}
					if(!empty($note[$i]) || $note[$i] != '' || $note[$i]) {
						$description_ = $note[$i];
					}else {
						$description_ = '';//$description;
					}
					$chart_account=$this->site->getChartByID($account_code[$i]);

					$data[] = array(
						'tran_type' 	=> 'JOURNAL',
						'tran_no' 		=> $tran_no,
						'account_code' 	=> $account_code[$i],
						'tran_date' 	=> $tran_date,
						'reference_no' 	=> $reference_no,
						'note' 			=> $description,
						'description' 	=> $description_,
						'amount' 		=> $amount,
						'biller_id' 	=> $biller_id,
						'created_name' 	=> $created_by_name,
						'created_type'	=> $created_type,
						'sale_id' 		=> $sale_id,
						'customer_id' 	=> $customer_id,
						'created_by' 	=> $this->session->userdata('user_id'),
						'activity_type' => $chart_account->type // 1= bussiness, 2 = investing, 3= financing activity
					);
				}
				if ($_FILES['document']['size'] > 0) {
	                $this->load->library('upload');
	                $config['upload_path']   = $this->digital_upload_path;
	                $config['allowed_types'] = $this->digital_file_types;
	                $config['max_size']      = $this->allowed_file_size;
	                $config['overwrite']     = false;
	                $config['encrypt_name']  = true;
	                $this->upload->initialize($config);
	                if (!$this->upload->do_upload('document')) {
	                    $error = $this->upload->display_errors();
	                    $this->session->set_flashdata('error', $error);
	                    redirect($_SERVER['HTTP_REFERER']);
	                }
	                $photo              = $this->upload->file_name;
	                $data['attachment'] = $photo;
	            }
				$this->accounts_model->addJournal($data);
		        $this->session->set_flashdata('message', $this->lang->line("journal_added"));
				admin_redirect('account/listJournal');
			}
	        
		}else{
			$this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
		}
		//$this->bpas->print_arrays($data);
		
	}
	public function edit_journal($tran_no)
	{
		$this->bpas->checkPermissions('edit', true, 'accounts');
		$chart_acc_details = $this->accounts_model->getAllChartAccount();
		foreach($chart_acc_details as $chart){
			$section_id = $chart->sectionid;
		}
		
		$this->data['type'] = $this->accounts_model->getAlltypes();
		$this->data['supplier'] = $chart_acc_details;
		$this->data['sectionacc'] = $chart_acc_details;
		$this->data['journals'] = $this->accounts_model->getJournalByTranNo($tran_no);
		$this->data['subacc'] = $this->accounts_model->getSubAccounts($section_id);
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['customers'] = $this->site->getCustomers();
		$this->data['invoices'] = $this->site->getCustomerInvoices();
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'accounts/edit_journal', $this->data);
	}
	public function updateJournal()
	{
		$this->bpas->checkPermissions('edit', true, 'accounts');
		$account_code 		= $this->input->post('account_section');
		$reference_no 		= $this->input->post('reference_no');
		$old_reference_no 	= $this->input->post('temp_reference_no');
		$biller_id 			= $this->input->post('biller_id');
		$date 				= $this->input->post('date');
		$tran_date 			= strtr($date, '/', '-');
		$tran_date 			= date('Y-m-d h:m', strtotime($tran_date));
		$tran_id 			= $this->input->post('tran_id');
		$description 		= $this->input->post('description');
		$note 				= $this->input->post('note');
		$debit 				= $this->input->post('debit');
		$credit 			= $this->input->post('credit');
		$created_name 		= $this->input->post('name');
		$created_type 		= $this->input->post('type');
		$sale_id 			= $this->input->post('customer_invoice_no');
		$customer_id 		= $this->input->post('customer_invoice');
		$i 					= 0;
		$tran_type 			= '';
		$tran_no_old 		= $this->accounts_model->getTranNoByRef($old_reference_no);		
		$tran_type 			= $this->accounts_model->getTranTypeByRef($old_reference_no);
		if(!$tran_type){
			$tran_type = 'JOURNAL';
		}
		if ($created_type == 3) {
			$customer 		= $this->site->getCompanyByName($created_name, $created_type);
			$customer_id 	= $customer->id;
		}
		$gltans = $this->accounts_model->getJournalByTranNo($tran_no_old);
		$not_account = array();
		foreach($gltans as $key => $gltran){
			if($gltran->account_code != $account_code[$key] && $tran_id[$key] == 0){
				$not_account[] = $gltran->tran_id;
			}
		}
		$data = array();
		for($i=0; $i < count($account_code); $i++){
			
			if($debit[$i]>0) {
				$amount = $debit[$i];
			}
			elseif($credit[$i]>0 ) {
				$amount = -$credit[$i];
			}else{
				$amount = 0;
			}
			if(!empty($note[$i]) || $note[$i] != '' || $note[$i]) {
				$description_ = $note[$i];
			}else {
				$description_ = $description;
			}

			$chart_account=$this->site->getChartByID($account_code[$i]);

			if($tran_id[$i] != 0){
				$data[] = array(
					'tran_type' 	=> $tran_type,
					'tran_no' 		=> $tran_no_old,
					'tran_id' 		=> $tran_id[$i],
					'account_code' 	=> $account_code[$i],
					'tran_date' 	=> $tran_date,
					'reference_no' 	=> $reference_no,
					'note' 	=> $description,
					'description' 	=> $description_,
					'amount' 		=> $amount,
					'biller_id' 	=> $biller_id,
					'created_name' 	=> $created_name,
					'created_type' 	=> $created_type,
					'sale_id' 		=> $sale_id,
					'customer_id' 	=> $customer_id,
					'updated_by' 	=> $this->session->userdata('user_id'),
					'activity_type' => $chart_account->type // 1= bussiness, 2 = investing, 3= financing activity
				);
			}else{
				$data[] = array(
					'tran_type' 	=> $tran_type,
					'tran_no' 		=> $tran_no_old,
					'account_code' 	=> $account_code[$i],
					'tran_date' 	=> $tran_date,
					'reference_no' 	=> $reference_no,
					'note' 	=> $description,
					'description' 	=> $description_,
					'amount' 		=> $amount,
					'biller_id' 	=> $biller_id,
					'created_name' 	=> $created_name,
					'created_type' 	=> $created_type,
					'sale_id' 		=> $sale_id,
					'customer_id' 	=> $customer_id,
					'created_by' 	=> $this->session->userdata('user_id'),
					'activity_type' => $chart_account->type // 1= bussiness, 2 = investing, 3= financing activity
				);
			}
		}
		if ($_FILES['document']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path']   = $this->digital_upload_path;
            $config['allowed_types'] = $this->digital_file_types;
            $config['max_size']      = $this->allowed_file_size;
            $config['overwrite']     = false;
            $config['encrypt_name']  = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('document')) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                redirect($_SERVER['HTTP_REFERER']);
            }
            $photo              = $this->upload->file_name;
            $data['attachment'] = $photo;
        }
		//$this->bpas->print_arrays($data);
		$this->accounts_model->updateJournal($data, $old_reference_no);
		//$this->accounts_model->deleteGltranByAccount($not_account);
		$this->session->set_flashdata('message', $this->lang->line("journal_updated"));
		admin_redirect('account/listJournal');
	}

	public function deleteJournal($id)
	{
		$this->bpas->checkPermissions(NULL, TRUE);

		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}

		if ($this->accounts_model->deleteJournalById($id)) {
			echo $this->lang->line("deleted_journal");
		} else {
			$this->session->set_flashdata('warning', lang('journal_x_deleted_have_account'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 0);</script>");
		}
	}

	public function getSubAccount($section_code = null)
	{
		if ($rows = $this->accounts_model->getSubAccounts($section_code)) {
			$data = json_encode($rows);
		} else {
			$data = false;
		}
		echo $data;
	}
	
	public function getpeoplebytype($company = null)
	{
		if ($rows = $this->accounts_model->getpeoplebytype($company)) {
			$data = json_encode($rows);
		} else {
			$data = false;
		}
		echo $data;
	}
	
	public function getCustomerInvoices($customer = null)
	{
		if ($rows = $this->site->getCustomerInvoices($customer)) {
			$data = json_encode($rows);
		} else {
			$data = false;
		}
		echo $data;
	}
	public function users($company_id = NULL)
	{
		$this->bpas->checkPermissions(false, true);

		if ($this->input->get('id')) {
			$company_id = $this->input->get('id');
		}


		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['modal_js'] = $this->site->modal_js();
		$this->data['company'] = $this->companies_model->getCompanyByID($company_id);
		$this->data['users'] = $this->companies_model->getCompanyUsers($company_id);
		$this->load->view($this->theme . 'suppliers/users', $this->data);

	}

	public function add_user($company_id = NULL)
	{
		$this->bpas->checkPermissions(false, true);

		if ($this->input->get('id')) {
			$company_id = $this->input->get('id');
		}
		$company = $this->companies_model->getCompanyByID($company_id);

		$this->form_validation->set_rules('email', $this->lang->line("email_address"), 'is_unique[users.email]');
		$this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

		if ($this->form_validation->run('companies/add_user') == true) {
			$active = $this->input->post('status');
			$notify = $this->input->post('notify');
			list($username, $domain) = explode("@", $this->input->post('email'));
			$email = strtolower($this->input->post('email'));
			$password = $this->input->post('password');
			$additional_data = array(
				'first_name' => $this->input->post('first_name'),
				'last_name' => $this->input->post('last_name'),
				'phone' => $this->input->post('phone'),
				'gender' => $this->input->post('gender'),
				'company_id' => $company->id,
				'company' => $company->company,
				'group_id' => 3
				);
			$this->load->library('ion_auth');
		} elseif ($this->input->post('add_user')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('suppliers');
		}

		if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
			$this->session->set_flashdata('message', $this->lang->line("user_added"));
			admin_redirect("suppliers");
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['company'] = $company;
			$this->load->view($this->theme . 'suppliers/add_user', $this->data);
		}
	}

	public function import_csv()
	{
		$this->bpas->checkPermissions();
		$this->load->helper('security');
		$this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

		if ($this->form_validation->run() == true) {

			if (DEMO) {
				$this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}

			if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

				$this->load->library('upload');

				$config['upload_path'] = 'assets/uploads/csv/';
				$config['allowed_types'] = 'csv';
				$config['max_size'] = '15360';
				$config['overwrite'] = TRUE;

				$this->upload->initialize($config);

				if (!$this->upload->do_upload('csv_file')) {

					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					admin_redirect("suppliers");
				}

				$csv = $this->upload->file_name;

				$arrResult = array();
				$handle = fopen("assets/uploads/csv/" . $csv, "r");
				if ($handle) {
					while (($row = fgetcsv($handle, 5001, ",")) !== FALSE) {
						$arrResult[] = $row;
					}
					fclose($handle);
				}
				$titles = array_shift($arrResult);

				$keys = array('company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6');

				$final = array();

				foreach ($arrResult as $key => $value) {
					$final[] = array_combine($keys, $value);
				}
				$rw = 2;
				foreach ($final as $csv) {
					if ($this->companies_model->getCompanyByEmail($csv['email'])) {
						$this->session->set_flashdata('error', $this->lang->line("check_supplier_email") . " (" . $csv['email'] . "). " . $this->lang->line("supplier_already_exist") . " (" . $this->lang->line("line_no") . " " . $rw . ")");
						admin_redirect("suppliers");
					}
					$rw++;
				}
				foreach ($final as $record) {
					$record['group_id'] = 4;
					$record['group_name'] = 'supplier';
					$data[] = $record;
				}
                //$this->bpas->print_arrays($data);
			}

		} elseif ($this->input->post('import')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('customers');
		}

		if ($this->form_validation->run() == true && !empty($data)) {
			if ($this->companies_model->addCompanies($data)) {
				$this->session->set_flashdata('message', $this->lang->line("suppliers_added"));
				admin_redirect('suppliers');
			}
		} else {

			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'suppliers/import', $this->data);
		}
	}

	public function delete($id = NULL)
	{
		$this->bpas->checkPermissions(NULL, TRUE);

		/*if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}*/
		if ($this->accounts_model->deleteChartAccount($id)) {
			echo $this->lang->line("deleted_chart_account");
		} else {
			$this->session->set_flashdata('warning', lang('chart_account_x_deleted_have_account'));
			admin_redirect('account');
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : admin_url('welcome')) . "'; }, 0);</script>");
		}
	}

	public function suggestions($term = NULL, $limit = NULL)
	{
        // $this->bpas->checkPermissions('index');
		if ($this->input->get('term')) {
			$term = $this->input->get('term', TRUE);
		}
		$limit = $this->input->get('limit', TRUE);
		$rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
		echo json_encode($rows);
	}

	public function getSupplier($id = NULL)
	{
        // $this->bpas->checkPermissions('index');
		$row = $this->companies_model->getCompanyByID($id);
		echo json_encode(array(array('id' => $row->id, 'text' => $row->company)));
	}

	public function account_actions()
	{
		if (!$this->Owner) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}

		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

		if ($this->form_validation->run() == true) {

			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->accounts_model->deleteChartAccount($id)) {
							$error = true;
						}
					}

					if ($error) {
						$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}

				if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('account'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('account_code'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('account_name'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('parent_account'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('account_section'));
					$styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );
                    
                    $this->excel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($styleArray);
					$row = 2;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getAccountByID($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->accountcode);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->accountname);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->parent_acc);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->sectionname);
						$row++;
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'Account_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function receivable_actions($wh=null)
	{
		if($wh){
			$wh = explode('-', $wh);
		}
		// $this->bpas->print_arrays($wh);
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

		if ($this->form_validation->run() == true) {

			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->accounts_model->deleteChartAccount($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
					}
					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                }

				if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
					if($this->Owner || $this->Admin){
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('acc_receivable'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('shop'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('sale_status'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));
					 $styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );
                    
                    $this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);

					$row = 2;
					$sum_grand = $sum_paid = $sum_balance = 0;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getReceivableByID($id);
						$sum_grand += $account->grand_total;
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->date);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->reference_no." ");
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->sale_status);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->payment_status);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('F' . $new_row, $sum_grand);
						$this->excel->getActiveSheet()->SetCellValue('G' . $new_row, $sum_paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $sum_balance);

						$row++;
					}
				}else{
					// echo "user";exit();
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('acc_receivable'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('shop'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('sale_status'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));
					 $styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );
                    
                    $this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);

					$row = 2;
					$sum_grand = $sum_paid = $sum_balance = 0;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getReceivableByID($id,$wh);
						$sum_grand += $account->grand_total;
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->date);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->reference_no." ");
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->biller);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->sale_status);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->payment_status);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('F' . $new_row, $sum_grand);
						$this->excel->getActiveSheet()->SetCellValue('G' . $new_row, $sum_paid);
						$this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $sum_balance);

						$row++;
					}
				}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(19);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'Acc_Receivable_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('F' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('F' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('F' . $new_row.'')->getFont()->setBold(true);
                    	$this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getFont()->setBold(true);
                    	$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);
                    	
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function combine_pdf($val)
    {
        $this->bpas->checkPermissions('combine_pdf', null, 'sales');

        foreach ($val as $id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->sales_model->getInvoiceByID($id);

            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : NULL;
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv'] = $inv;
            $return = $this->sales_model->getReturnBySID($id);
            $this->data['return_sale'] = $return;
            $this->data['rows'] = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_rows'] = $inv->return_id ? $this->sale_order_model->getAllInvoiceItems($inv->return_id) : NULL;
            $html_data = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (! $this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = array(
                'content' => $html_data,
                'footer' => $this->data['biller']->invoice_footer,
            );
        }

        $name = lang("sales") . ".pdf";
        $this->bpas->generate_pdf($html, $name);

    }
	
	public function reciept_actions()
	{
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

		if ($this->form_validation->run() == true) {
			$from_date = $this->input->post('from_date');
			$to_date = $this->input->post('to_date');
			$inv_from_date = $this->input->post('inv_from_date');
			$inv_to_date = $this->input->post('inv_to_date');

			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->accounts_model->deleteChartAccount($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
					}
					admin_redirect($_SERVER["HTTP_REFERER"]);
				}

				if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);

					$this->excel->getActiveSheet()->mergeCells('A1:I1');
                    $this->excel->getActiveSheet()->setCellValue('A1','Bill Receipt');

                    if ($from_date && $to_date) {
                    	$this->excel->getActiveSheet()->mergeCells('A2:I2');
                    	
                    	if ($inv_from_date && $inv_to_date) {
                    		$this->excel->getActiveSheet()->setCellValue('A2','From: '.$from_date .' To: '. $to_date .'  And  From Inv Date: '. $inv_from_date .' To Inv Date '. $inv_to_date);
                    	} else {
                    		$this->excel->getActiveSheet()->setCellValue('A2','From: '.$from_date .' To: '. $to_date);
                    	}

                    	$this->excel->getActiveSheet()->setTitle(lang('bill_reciept'));
						$this->excel->getActiveSheet()->SetCellValue('A3', lang('date'));
						$this->excel->getActiveSheet()->SetCellValue('B3', lang('invoice_date'));
						$this->excel->getActiveSheet()->SetCellValue('C3', lang('payment_ref'));
						$this->excel->getActiveSheet()->SetCellValue('D3', lang('sale_ref'));
						$this->excel->getActiveSheet()->SetCellValue('E3', lang('customer'));
						$this->excel->getActiveSheet()->SetCellValue('F3', lang('note'));
						$this->excel->getActiveSheet()->SetCellValue('G3', lang('paid_by'));
						$this->excel->getActiveSheet()->SetCellValue('H3', lang('amount'));
						$this->excel->getActiveSheet()->SetCellValue('I3', lang('type'));
                    } else {
						$this->excel->getActiveSheet()->setTitle(lang('bill_reciept'));
						$this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));
						$this->excel->getActiveSheet()->SetCellValue('B2', lang('invoice_date'));
						$this->excel->getActiveSheet()->SetCellValue('C2', lang('payment_ref'));
						$this->excel->getActiveSheet()->SetCellValue('D2', lang('sale_ref'));
						$this->excel->getActiveSheet()->SetCellValue('E2', lang('customer'));
						$this->excel->getActiveSheet()->SetCellValue('F2', lang('note'));
						$this->excel->getActiveSheet()->SetCellValue('G2', lang('paid_by'));
						$this->excel->getActiveSheet()->SetCellValue('H2', lang('amount'));
						$this->excel->getActiveSheet()->SetCellValue('I2', lang('type'));
                    	
                    }


					$styleArray = array(
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 16
                        )
                    );

                    $styleArray2 = array(
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 12
                        )
                    );
                   	
                   	if ($from_date && $to_date) {
                   		$this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
		                $this->excel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($styleArray2);
		                $this->excel->getActiveSheet()->getStyle('A3:I3')->applyFromArray($styleArray2);
		                $this->excel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		                $this->excel->getActiveSheet()->getStyle('A3:I3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		                $this->excel->getActiveSheet()->getStyle('A2:I2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$row = 4;
                   	} else {
                   		$this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
		                $this->excel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($styleArray2);
		                $this->excel->getActiveSheet()->getStyle('A1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		                $this->excel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		                $this->excel->getActiveSheet()->getStyle('A1:I1')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$row = 3;
                   	}

					foreach ($_POST['val'] as $id) {
						$account = $this->site->getRecieptByID($id);

						if ($account->type == 'sent' || $account->type == 'received' || $account->sale_status == 'returned') {
							$sum_amount += $account->amount;
						}

						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($account->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($account->inv_date));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->payment_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->sale_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, strip_tags($account->noted));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->paid_by);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->amount);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->type);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $this->bpas->formatMoney($sum_amount));

						$row++;
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'Bill_Reciept_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);
                    	
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function getBillRecieptAction($pdf = NULL, $xls = null, $biller_id = NULL, $from_date = null, $to_date = null)
	{

		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

		$from_date = $this->input->post('from_date');
		$to_date = $this->input->post('to_date');
		$this->bpas->print_arrays($xls);

		if ($this->form_validation->run() == true) {

			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->accounts_model->deleteChartAccount($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
					}
					admin_redirect($_SERVER["HTTP_REFERER"]);
				}

				if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);

					$this->excel->getActiveSheet()->mergeCells('A1:I1');
                    $this->excel->getActiveSheet()->setCellValue('A1','Bill Receipt');

					$this->excel->getActiveSheet()->setTitle(lang('bill_reciept'));
					$this->excel->getActiveSheet()->SetCellValue('A2', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B2', lang('invoice_date'));
					$this->excel->getActiveSheet()->SetCellValue('C2', lang('payment_ref'));
					$this->excel->getActiveSheet()->SetCellValue('D2', lang('sale_ref'));
					$this->excel->getActiveSheet()->SetCellValue('E2', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('F2', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('G2', lang('paid_by'));
					$this->excel->getActiveSheet()->SetCellValue('H2', lang('amount'));
					$this->excel->getActiveSheet()->SetCellValue('I2', lang('type'));

					$styleArray = array(
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 14
                        )
                    );

                    $styleArray2 = array(
                        'font'  => array(
                            'bold'  => true,
                            'size'  => 12
                        )
                    );
                    
                    $this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
                    $this->excel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($styleArray2);
                    $this->excel->getActiveSheet()->getStyle('A2:I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                   


					$row = 3;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getRecieptByID($id);
						$sum_amount += $account->amount;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($account->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($account->inv_date));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->payment_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->sale_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $account->noted);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->paid_by);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->amount);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->type);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $sum_amount);

						$row++;
					}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'Bill_Reciept_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);
                    	
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function payable_actions($wh=null)
	{
		if($wh){
			$wh = explode('-', $wh);
		}
		// $this->bpas->print_arrays($wh);

		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

		if ($this->form_validation->run() == true) {

			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->accounts_model->deleteChartAccount($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
					}
					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
				if ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf_pay($_POST['val']);
                }
				if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
					if($this->Owner || $this->Admin){
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('acc_payable'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('po_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('pr_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('purchase_status'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
					 $styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );
                    
                    $this->excel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($styleArray);

					$row = 2;
					$sum_paid = $sum_balance = $sum_grand = 0;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getPayableByID($id);
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$sum_grand += $account->grand_total;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->date);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->reference_no." ");
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->order_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->request_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->supplier);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $account->status);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $account->payment_status);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('G' . $new_row, $sum_grand);
						$this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $sum_paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $new_row, $sum_balance);

						$row++;
					}
				}else{
					// echo "user";exit();
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('acc_payable'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('po_no'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('pr_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('supplier'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('purchase_status'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));
					 $styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );
                    
                    $this->excel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($styleArray);

					$row = 2;
					$sum_paid = $sum_balance = $sum_grand = 0;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getPayableByID($id,$wh);
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$sum_grand += $account->grand_total;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->date);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->reference_no." ");
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->order_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->request_ref." ");
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->supplier);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $account->status);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $account->payment_status);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('G' . $new_row, $sum_grand);
						$this->excel->getActiveSheet()->SetCellValue('H' . $new_row, $sum_paid);
						$this->excel->getActiveSheet()->SetCellValue('I' . $new_row, $sum_balance);

						$row++;
					}
				}

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'Acc_Payable_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('I' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('G' . $new_row.'')->getFont()->setBold(true);
                    	$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('H' . $new_row.'')->getFont()->setBold(true);
                    	$this->excel->getActiveSheet()->getStyle('I' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('I' . $new_row.'')->getFont()->setBold(true);
                    	
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function combine_pdf_pay($val)
	{
		$this->bpas->checkPermissions('combine_pdf', NULL, 'purchases');

        foreach ($val as $purchase_id) {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;

            $html[] = array(
                'content' => $this->load->view($this->theme . 'purchases/pdf', $this->data, true),
                'footer' => '',
            );
        }

        $name = lang("purchases") . ".pdf";
        $this->bpas->generate_pdf($html, $name);
	}
	
	public function journal_actions()
	{
		
		// $this->bpas->print_arrays($biller_id);

		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

		if ($this->form_validation->run() == true) {

			if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
					
					$error = false;
					foreach ($_POST['val'] as $id) {
						if (!$this->accounts_model->deleteChartAccount($id)) {
							$error = true;
						}
					}
					if ($error) {
						$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
					} else {
						$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
					}
					admin_redirect($_SERVER["HTTP_REFERER"]);
				}

				if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('journal'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('type'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('project'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('account_code'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('account_name'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('debit'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('credit'));
					$styleArray = array(
                        'font'  => array(
                            'bold'  => true
                        )
                    );
                    $this->excel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($styleArray);

					$row = 2;
					$sum_debit = $sum_credit = 0;
					foreach ($_POST['val'] as $id) {
						$account = $this->site->getJournalByID($id);
						$sum_debit += $account->debit;
						$sum_credit += $account->credit;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->g_tran_no." ");
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->tran_type);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->tran_date);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->reference_no." ");
						$this->excel->getActiveSheet()->SetCellValue('E' .$row, $account->company);
						$this->excel->getActiveSheet()->SetCellValue('F' .$row, $account->NAME);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->account_code." ");
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->narrative);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->description);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $account->debit);
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $account->credit);
						// $this->excel->getActiveSheet()->getStyle('A'.$row.':J'.$row)->applyFromArray($BoStyle);
						$new_row = $row+1;
						$this->excel->getActiveSheet()->SetCellValue('J' . $new_row, $sum_debit);
						$this->excel->getActiveSheet()->SetCellValue('K' . $new_row, $sum_credit);

						$row++;
					}
					// $this->excel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($BoStyle);
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
					$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$filename = 'Journal_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('J' . $new_row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('K' . $new_row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('J' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('J' . $new_row.'')->getFont()->setBold(true);
                    	$this->excel->getActiveSheet()->getStyle('K' . $new_row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
                    	$this->excel->getActiveSheet()->getStyle('K' . $new_row.'')->getFont()->setBold(true);
                    	
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}

	public function import_journal_csv()
	{
		$this->bpas->checkPermissions('import', null, 'accounts');

		$this->load->helper('security');
		$this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

		if ($this->form_validation->run() == true) {

			if (DEMO) {
				$this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}

			if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

				$this->load->library('upload');

				$config['upload_path'] = 'assets/uploads/csv/';
				$config['allowed_types'] = 'csv';
				$config['max_size'] = $this->allowed_file_size;
				$config['overwrite'] = TRUE;

				$this->upload->initialize($config);

				if (!$this->upload->do_upload('csv_file')) {

					$error = $this->upload->display_errors();
					$this->session->set_flashdata('error', $error);
					admin_redirect("account/listJournal");
				}

				$csv = $this->upload->file_name;

				$arrResult = array();
				$handle = fopen("assets/uploads/csv/" . $csv, "r");
				if ($handle) {
					while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
						$arrResult[] = $row;
					}
					fclose($handle);
				}
				$titles = array_shift($arrResult);
			//$this->bpas->print_arrays($arrResult);
				$keys = array('tran_type', 'tran_no', 'tran_date','account_code', 'narrative', 'amount', 'reference_no', 'description', 'biller_id', 'created_by');

				$final = array();

				foreach ($arrResult as $key => $value) {
					$final[] = array_combine($keys, $value);
				}
				
                /*$rw = 2;
                foreach ($final as $csv) {
                    if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                        $this->session->set_flashdata('error', $this->lang->line("check_supplier_email") . " (" . $csv['email'] . "). " . $this->lang->line("supplier_already_exist") . " (" . $this->lang->line("line_no") . " " . $rw . ")");
                        admin_redirect("suppliers");
                    }
                    $rw++;
                }*/
				
				$first = 1;
				$refer = "";
				$i = 0;
				$tr = $this->accounts_model->increaseTranNo();
				
				
                foreach ($final as $record) {    
					
						$record['sectionid'] = $this->accounts_model->getSectionIdByCode(trim($record['account_code']));
						//$date = strtr($record['tran_date'], '/', '-');
					
						$record['tran_date'] = $this->bpas->fld(date('d-m-Y', strtotime($record['tran_date'])));
						if($first == 1){
							$tr = $tr + 1;
							$refer = trim($record['reference_no']);
							$first = 2;
						}
						if($refer == trim($record['reference_no'])){
							if($record['tran_no'] == "" || $record['tran_no']  <= $tr){
								$record['tran_no'] = $tr;
								$i = $tr;
								$refer = trim($record['reference_no']);
							}
						}else{
							$i = $i + 1;
							$record['tran_no'] = $i;
							$tr = $i;
							$refer = trim($record['reference_no']);
						}	
						
						$data[] = $record;
						
                }
				
				$this->accounts_model->UpdateincreaseTranNo($tr);
                //$this->bpas->print_arrays($data);
            }
			 
        } 

        if ($this->form_validation->run() == true && !empty($data)) {
        	if ($this->accounts_model->addJournals($data)) {
        		$this->session->set_flashdata('message', $this->lang->line("journal_added"));
        		admin_redirect('account/listJournal');
        	}
        } else {
        	$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        	$this->data['modal_js'] = $this->site->modal_js();
        	$this->load->view($this->theme . 'accounts/import_journal_csv', $this->data);
        }
    }

    public function import_chart_csv()
    {
    	$this->bpas->checkPermissions();
    	$this->load->helper('security');
    	$this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

    	if ($this->form_validation->run() == true) {

    		if (DEMO) {
    			$this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
    			admin_redirect($_SERVER["HTTP_REFERER"]);
    		}

    		if (isset($_FILES["csv_file"])) /* if($_FILES['userfile']['size'] > 0) */ {

    			$this->load->library('upload');

    			$config['upload_path'] = 'assets/uploads/csv/';
    			$config['allowed_types'] = 'csv';
    			$config['max_size'] = '15360';
    			$config['overwrite'] = TRUE;

    			$this->upload->initialize($config);

    			if (!$this->upload->do_upload('csv_file')) {

    				$error = $this->upload->display_errors();
    				$this->session->set_flashdata('error', $error);
    				admin_redirect("account");
    			}

    			$csv = $this->upload->file_name;

    			$arrResult = array();
    			$handle = fopen("assets/uploads/csv/" . $csv, "r");
    			if ($handle) {
    				while (($row = fgetcsv($handle, 5001, ",")) !== FALSE) {
    					$arrResult[] = $row;
    				}
    				fclose($handle);
    			}
    			$titles = array_shift($arrResult);

    			$keys = array('accountcode','accountname','parent_acc','sectionid','bank','account_tax_id','acc_level','lineage');

    			$final = array();

    			foreach ($arrResult as $key => $value) {
    				$final[] = array_combine($keys, $value);
    			}
                /*$rw = 2;
                foreach ($final as $csv) {
                    if ($this->companies_model->getCompanyByEmail($csv['email'])) {
                        $this->session->set_flashdata('error', $this->lang->line("check_supplier_email") . " (" . $csv['email'] . "). " . $this->lang->line("supplier_already_exist") . " (" . $this->lang->line("line_no") . " " . $rw . ")");
                        admin_redirect("suppliers");
                    }
                    $rw++;
                }*/
                foreach ($final as $record) {             
                	$data[] = $record;
                }
                //$this->bpas->print_arrays($data);
            }

        } 

        if ($this->form_validation->run() == true && !empty($data)) {
        	if ($this->accounts_model->addCharts($data)) {
        		$this->session->set_flashdata('message', $this->lang->line("Chart_Account_Added"));
        		admin_redirect('account');
        	}
        } else {

        	$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        	$this->data['modal_js'] = $this->site->modal_js();
        	$this->load->view($this->theme . 'accounts/import_chart_csv', $this->data);
        }
    }

    public function checkAccount()
	{
    	$accountcode = $this->input->get('code', TRUE);
    	$row = $this->accounts_model->getAccountCode($accountcode);
    	if ($row) {
    		echo 1;
    	} else {
    		echo 0;
    	}
    }
   
    public function selling_tax()
    {
    	$this->bpas->checkPermissions();
    	$this->load->admin_model('reports_model');



    	$this->data['users'] = $this->reports_model->getStaff();
    	$this->data['warehouses'] = $this->site->getAllWarehouses();
    	$this->data['billers'] = $this->site->getAllCompanies('biller');

    	$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    	if ($this->Owner || $this->Admin) {
    		$this->data['warehouses'] = $this->site->getAllWarehouses();
    		$this->data['warehouse_id'] = $warehouse_id;
    		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
    	} else {
    		$this->data['warehouses'] = NULL;
    		$this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
    		$this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
    	}


    	$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('selling_tax')));
    	$meta = array('page_title' => lang('selling_tax'), 'bc' => $bc);
    	$this->page_construct('accounts/selling_tax', $meta, $this->data);
    }

    public function selling_actions()
	{
    	if (!$this->Owner) {
    		$this->session->set_flashdata('warning', lang('access_denied'));
    		admin_redirect($_SERVER["HTTP_REFERER"]);
    	}

    	$this->form_validation->set_rules('form_action', lang("form_action"), 'required');

    	if ($this->form_validation->run() == true) {

    		if (!empty($_POST['val'])) {
    			if ($this->input->post('form_action') == 'delete') {

    				$error = false;
    				foreach ($_POST['val'] as $id) {
    					if (!$this->accounts_model->deleteChartAccount($id)) {
    						$error = true;
    					}
    				}
    				if ($error) {
    					$this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
    				} else {
    					$this->session->set_flashdata('message', $this->lang->line("account_deleted_successfully"));
    				}
    				admin_redirect($_SERVER["HTTP_REFERER"]);
    			}

    			if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

    				$this->load->library('excel');
    				$this->excel->setActiveSheetIndex(0);
    				$this->excel->getActiveSheet()->setTitle(lang('selling_tax'));
    				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
    				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
    				$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
    				$this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
    				$this->excel->getActiveSheet()->SetCellValue('E1', lang('sale_status'));
    				$this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
    				$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
    				$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
    				$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

    				$row = 2;
    				foreach ($_POST['val'] as $id) {
    					$account = $this->site->getSellingByID($id);
    					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->date);
    					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->reference_no);
    					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->biller);
    					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->customer);
    					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->sale_status);
    					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $account->grand_total);
    					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $account->paid);
    					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $account->balance);
    					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $account->payment_status);
    					$row++;
    				}

    				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    				$filename = 'Selling_Tax_' . date('Y_m_d_H_i_s');
    				if ($this->input->post('form_action') == 'export_pdf') {
    					$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
    					$this->excel->getDefaultStyle()->applyFromArray($styleArray);
    					$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    					require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
    					$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
    					$rendererLibrary = 'MPDF';
    					$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
    					if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
    						die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
    							PHP_EOL . ' as appropriate for your directory structure');
    					}

    					header('Content-Type: application/pdf');
    					header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
    					header('Cache-Control: max-age=0');

    					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
    					return $objWriter->save('php://output');
    				}
    				if ($this->input->post('form_action') == 'export_excel') {
    					header('Content-Type: application/vnd.ms-excel');
    					header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    					header('Cache-Control: max-age=0');

    					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
    					return $objWriter->save('php://output');
    				}

    				admin_redirect($_SERVER["HTTP_REFERER"]);
    			}
    		} else {
    			$this->session->set_flashdata('error', $this->lang->line("no_supplier_selected"));
    			admin_redirect($_SERVER["HTTP_REFERER"]);
    		}
    	} else {
    		$this->session->set_flashdata('error', validation_errors());
    		admin_redirect($_SERVER["HTTP_REFERER"]);
    	}
    }

    public function purchasing_tax()
    {
    	$this->bpas->checkPermissions();
    	$this->load->admin_model('reports_model');

    	if(isset($_GET['d']) != ""){
    		$date = $_GET['d'];
    		$this->data['date'] = $date;
    	}

    	$this->data['users'] = $this->reports_model->getStaff();
    	$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    	if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
    		$this->data['warehouses'] = $this->site->getAllWarehouses();
    		$this->data['warehouse_id'] = $warehouse_id;
    		$this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
    	} else {
    		$this->data['warehouses'] = null;
    		$this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
    		$this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
    	}

    	$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchasing_tax')));
    	$meta = array('page_title' => lang('purchasing_tax'), 'bc' => $bc);
    	$this->page_construct('accounts/purchasing_tax', $meta, $this->data);
    }

    public function deposits($action = NULL)
    {
    	$this->bpas->checkPermissions('index', true, 'accounts');

    	$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    	$this->data['action'] = $action;
    	$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
    	$meta = array('page_title' => lang('deposits'), 'bc' => $bc);
    	$this->page_construct('accounts/deposits', $meta, $this->data);
    }

    public function getDeposits()
	{

    	$return_deposit = anchor('customers/return_deposit/$1', '<i class="fa fa-reply"></i> ' . lang('return_deposit'), 'data-toggle="modal" data-target="#myModal2"');
    	$deposit_note = anchor('customers/deposit_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('deposit_note'), 'data-toggle="modal" data-target="#myModal2"');
    	$edit_deposit = anchor('customers/edit_deposit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_deposit'), 'data-toggle="modal" data-target="#myModal2"');
    	$delete_deposit = "<a href='#' class='po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>"
    	. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete_deposit/$1') . "'>"
    	. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
    	. lang('delete_deposit') . "</a>";

    	$action = '<div class="text-center"><div class="btn-group text-left">'
    	. '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
    	. lang('actions') . ' <span class="caret"></span></button>
    	<ul class="dropdown-menu pull-right" role="menu">
    		<li>' . $deposit_note . '</li>
    		<li>' . $edit_deposit . '</li>
    		<li>' . $return_deposit . '</li>
    		<li>' . $delete_deposit . '</li>
    		<ul>
    		</div></div>';

    		$this->load->library('datatables');
    		$this->datatables
    		->select("deposits.id as dep_id, companies.id AS id , date, reference,companies.name,
			 deposits.amount, deposits.note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
    		->from("deposits")
			->join('companies', 'companies.id = deposits.company_id', 'left')
    		->join('users', 'users.id=deposits.created_by', 'left')
    		->where('deposits.amount <>', 0)
    		->add_column("Actions", $action, "dep_id")
			->unset_column('dep_id');

    		echo $this->datatables->generate();
    }

	public function exchange_rate_tax()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');

		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['action'] = $action;
		$this->data['condition_tax']=$this->accounts_model->getConditionTax();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('exchange_rate_tax'), 'bc' => $bc);
		$this->page_construct('accounts/exchange_rate_tax', $meta, $this->data);
	}
	
	public function edit_condition_tax($id)
	{
		$this->bpas->checkPermissions(false, true);

		$this->data['condition_tax'] = $this->accounts_model->getConditionTaxById($id);
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'accounts/edit_condition_tax', $this->data);
	}
	
	public function update_exchange_tax_rate($id)
	{
		$data=array(
			'rate'=>$this->input->post('rate')
			);
		$update=$this->accounts_model->update_exchange_tax_rate($id,$data);
		if($update){
			admin_redirect('account/exchange_rate_tax');
		}
	}
	
	function condition_tax()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');

		if ($this->form_validation->run('account/add_condition_tax') == true) {
			
			$data = array(
				'code' => 'Salary',
				'name' => $this->input->post('name'),
				'rate' => $this->input->post('rate'),
				'reduct_tax' => $this->input->post('reduct_tax'),
				'min_salary' => $this->input->post('min_salary'),
				'max_salary' => $this->input->post('max_salary')
			);
			
		}

		if ($this->form_validation->run() == true && $this->accounts_model->addConditionTax($data)) {
			$this->session->set_flashdata('message', $this->lang->line("condition_tax_added"));
			admin_redirect('account/condition_tax');
		} else {
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
			$meta = array('page_title' => lang('condition_tax'), 'bc' => $bc);
			$this->page_construct('accounts/condition_tax', $meta, $this->data);
		}
	}
	
	function getConditionTax()
	{
		$this->bpas->checkPermissions('index', true, 'accounts');

		$this->load->library('datatables');
		$this->datatables->select("id,code, name, rate, min_salary, max_salary, reduct_tax")
		->from("condition_tax")
		->where("code", "Salary")
		->add_column("Actions", "<center><a class=\"tip\" title='" . $this->lang->line("edit_condition_tax") . "' href='" . admin_url('account/edit_condition/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a>  <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_condition_tax") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('account/delete_condition_tax/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></center>", "id");
		echo $this->datatables->generate();
	}

	function add_condition_tax()
	{
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'accounts/add_condition_tax', $this->data);
	}
	
	function edit_condition($id = null)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_rules('name', $this->lang->line("name"), 'required');
		if ($this->form_validation->run('account/edit_condition_tax') == true) {
			$data = array(
				'code' => 'Salary',
				'name' => $this->input->post('name'),
				'rate' => $this->input->post('rate'),
				'reduct_tax' => $this->input->post('reduct_tax'),
				'min_salary' => $this->input->post('min_salary'),
				'max_salary' => $this->input->post('max_salary')
			);
		
			$ids = $this->input->post('id');
		}
		if ($this->form_validation->run() == true && $this->accounts_model->update_exchange_tax_rate($ids,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("condition_tax_updateed"));
			admin_redirect('account/condition_tax');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['id'] = $id;
			$this->data['data'] = $this->accounts_model->getConditionTaxById($id);
			$this->load->view($this->theme . 'accounts/update_condition_tax', $this->data);
		}
	}
	
	function delete_condition_tax($id)
	{
		$this->bpas->checkPermissions();

		if ($this->accounts_model->deleteConditionTax($id)) {
			$this->session->set_flashdata('message', $this->lang->line("condition_tax_deleted"));
			admin_redirect('account/condition_tax');
		} else {
			$this->session->set_flashdata('message', $this->lang->line("connot_deleted"));
			admin_redirect('account/condition_tax');
		}
	}
	
	function deposits_action()
	{
		if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {

                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deposits'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('paid_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));

                    $row = 2;
					$total_amount = 0;
                    foreach ($_POST['val'] as $id) {
                        $dep = $this->accounts_model->getCustomersDepositByCustomerID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($dep->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $dep->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $dep->amount);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $dep->paid_by);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $dep->created_by);
						$total_amount += $dep->amount;
                        $row++;
                    }
					
					$this->excel->getActiveSheet()->getStyle("C" . $row . ":C" . $row)->getBorders()
						->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $total_amount);

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'deposits_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
                        $this->excel->getDefaultStyle()->applyFromArray($styleArray);
                        $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                        require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
                        $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                        $rendererLibrary = 'MPDF';
                        $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
                        if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                            die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
                                PHP_EOL . ' as appropriate for your directory structure');
                        }

                        header('Content-Type: application/pdf');
                        header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
                        return $objWriter->save('php://output');
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        header('Content-Type: application/vnd.ms-excel');
                        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                        header('Cache-Control: max-age=0');

                        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
                        return $objWriter->save('php://output');
                    }

                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_deposit_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	function ar_by_customer()
	{
		if($this->input->post('start_date')){
			$start_date =  $this->bpas->fld($this->input->post('start_date'));
			$this->data['start_date2'] = trim($start_date);
		} else {
			$start_date =  $this->bpas->fld(date("d/m/Y"));
			$this->data['start_date2'] = trim($start_date);
		}

		//$start_date = $this->input->post('start_date') ? $this->bpas->fld($this->input->post('start_date').' 00:00:00'):$this->bpas->fld(date("d/m/Y H:i:s"));
		//$this->data['start_date2'] = trim($start_date);

		if($this->input->post('end_date')){
			$end_date = $this->bpas->fld($this->input->post('end_date'));
			$this->data['end_date2'] = trim($end_date);
		} else {
			$end_date = $this->bpas->fld(date("d/m/Y"));
			$this->data['end_date2'] = trim($end_date);
		}
		if($this->input->post('customer')){
			$customer = $this->input->post('customer');
			$this->data['customer2'] = $customer;
		}else{
			$customer = null;
			$this->data['customer2'] = 0;
		}
		
		$cust_data[] = "";

		$this->data['customers'] =  $this->accounts_model->ar_by_customerV2($start_date, $end_date, $customer);
		$bc = array( array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')) );
		$meta = array('page_title' => lang('ar_by_customer'), 'bc' => $bc);
		$this->page_construct('accounts/ar_by_customer', $meta, $this->data);
	}

	function arByCustomer($pdf = null, $excel = null, $ar_Statement = null, $customer2 = null, $start_date2 = null, $end_date2 = null, $balance2 = null)
	{        
        if ($pdf || $excel) { 
		   	$cust_data[] = "";
			$customers = $this->accounts_model->ar_by_customer($start_date2, $end_date2, $customer2, $balance2, 'customer');
			$i=0;
			foreach($customers as $cus){
				$customerDatas = $this->accounts_model->ar_by_customer($cus->start_date, $cus->end_date, $cus->customer_id, $cus->balance, 'detail');
				foreach($customerDatas as $cusData){
					$k = 0;
					foreach($customerDatas as $cusDt) {
						$customerDatas[$k]->payments = $this->accounts_model->ar_by_customer($cus->start_date, $cus->end_date, $cus->customer_id, $cus->balance, 'payment', $cusDt->id);
						$k++;
					}
					$cust_data[$i] = array(
						"customerName" => $cus->customer,
						"customerAddr" => $this->site->getCompanyByID($cus->customer_id)->address,
						"customerDatas" => array(
							"supplierId" => $cusData->id,
							"custSO"     => $customerDatas
						)
					);
				}
				$i++;
			}
            if (!empty($cust_data)) {
            	$this->load->library('excel');
	            $this->excel->setActiveSheetIndex(0);
	            $this->excel->getActiveSheet()->setTitle(lang('Sales List'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('reference_no'));
	            $this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
	            $this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
	            $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
	            $this->excel->getActiveSheet()->SetCellValue('E1', lang('return'));
	            $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
	            $this->excel->getActiveSheet()->SetCellValue('G1', lang('deposit'));
	            $this->excel->getActiveSheet()->SetCellValue('H1', lang('discount'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
				$styleArray = array(
	                'font'  => array(
	                    'bold'  => true
	                )
	            );
	            $this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
	            $row = 2;
	            foreach ($cust_data as $data_row) {
	                $this->excel->getActiveSheet()->SetCellValue('A' . $row, "Customer >> ". $data_row["customerName"]);
	                $this->excel->getActiveSheet()->mergeCells('A'.$row.':I'.$row);                    
	                $subTotal = $subReturn = $subDeposit = $subPaid = $subDiscount = $gbalance = 0;
	                foreach ($data_row['customerDatas']['custSO'] as $value) {
	                   	$row++;
	                   	$subTotal += $value->grand_total;
						$subReturn += $value->amount_return;
						$subDeposit += $value->amount_deposit;
						$subDiscount += $value->order_discount;
	                   	$sub_balance = ($value->grand_total - $value->amount_return - $value->amount_deposit - $value->order_discount);
	                   	$gbalance	+= $sub_balance;
	                    $type = (explode('-', $value->reference_no)[0]=='INV'?"Invoice":(explode('/', $value->reference_no)[0]=='SALE'?"Sale":"Not Assigned"));
	                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $value->reference_no." ");
	                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $value->date);
	                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, 'Invoice');
	                    // $this->excel->getActiveSheet()->SetCellValue('C' . $row, $type);
	                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($value->grand_total));
	                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($value->amount_return));
	                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, '');
	                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($value->amount_deposit));
	                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($value->order_discount));
	                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($sub_balance));
	                    if(is_array($value->payments)){
		                    foreach ($value->payments as $cusPmt) {
		                    	$row++;
		                    	$subPaid += abs($cusPmt->amount);
		                    	$typeRV = (explode('/', $cusPmt->reference_no)[0]=='RV'?"Payment":(explode('-', $cusPmt->reference_no)[0]=='RV'?"Payment":"Not Assigned"));
		                    	$this->excel->getActiveSheet()->SetCellValue('A' . $row, $cusPmt->reference_no);
					            $this->excel->getActiveSheet()->SetCellValue('B' . $row, $cusPmt->date);
					            $this->excel->getActiveSheet()->SetCellValue('C' . $row, 'Payment');
					            // $this->excel->getActiveSheet()->SetCellValue('C' . $row, $typeRV);
					            $this->excel->getActiveSheet()->SetCellValue('D' . $row, '');
					            $this->excel->getActiveSheet()->SetCellValue('E' . $row, '');
					            $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($cusPmt->amount));
					            $this->excel->getActiveSheet()->SetCellValue('G' . $row, '');
					            $this->excel->getActiveSheet()->SetCellValue('H' . $row, '');
					            $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($sub_balance - abs($cusPmt->amount)));
					            $gbalance -= abs($cusPmt->amount);
								$sub_balance -= abs($cusPmt->amount);
		                	}
	                 	}
	                }
	                $row++;
	                $this->excel->getActiveSheet()->SetCellValue('C' . $row, 'Total >>');
	                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($subTotal));
	                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($subReturn));
	                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($subPaid));
	                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($subDeposit));
	                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($subDiscount));
	                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($gbalance));
	                if($excel){               
		             	$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
		                $this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
		                $this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
		                $this->excel->getActiveSheet()->getStyle('F' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('F' . $row.'')->getFont()->setBold(true);
		                $this->excel->getActiveSheet()->getStyle('G' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('G' . $row.'')->getFont()->setBold(true);
		                $this->excel->getActiveSheet()->getStyle('H' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('H' . $row.'')->getFont()->setBold(true);
		                $this->excel->getActiveSheet()->getStyle('I' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
		                $this->excel->getActiveSheet()->getStyle('I' . $row.'')->getFont()->setBold(true);
		            }
		            $row++; 
	            }
	            $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	            $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
	            $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
	            $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
	            $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	            $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	            $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	            $filename = lang('ar_by_customer');
	            $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	            if ($pdf) {
	                $styleArray = array(
	                    'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	                );
	                $this->excel->getDefaultStyle()->applyFromArray($styleArray);
	                $this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
	                require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
	                $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
	                $rendererLibrary = 'MPDF';
	                $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
	                if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
	                    die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
	                        PHP_EOL . ' as appropriate for your directory structure');
	                }
	                header('Content-Type: application/pdf');
	                header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
	                header('Cache-Control: max-age=0');
	                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
	                $objWriter->save('php://output');
	                exit();
	            }
	            if ($excel) {
	                ob_clean();
	                header('Content-Type: application/vnd.ms-excel');
	                header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
	                header('Cache-Control: max-age=0');
	                ob_clean();
	                $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
	                $objWriter->save('php://output');
	                exit();
	            }
            } else {
            	$this->session->set_flashdata('error', lang('nothing_found'));
            	admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } elseif ($ar_Statement) {
            $this->data['biller']     = $this->Settings->default_biller ? $this->site->getCompanyByID($this->Settings->default_biller) : null;
          	$this->data['start_date'] = $start_date2;
            $this->data['end_date']   = $end_date2;
            $this->data['balance']    = $balance2;
            $this->data['customers']  = $this->accounts_model->ar_by_customerV2($start_date2, $end_date2, $customer2);
            $this->load->view($this->theme . 'accounts/ar_statements', $this->data);
        }
	}
	
	function ap_by_supplier()
	{
		if($this->input->post('start_date')){
			$start_date =  $this->bpas->fld($this->input->post('start_date'));
			$this->data['start_date2'] = trim($start_date);
		} else {
			$start_date =  $this->bpas->fld(date("d/m/Y"));
			$this->data['start_date2'] = trim($start_date);
		}
		if($this->input->post('end_date')){
			$end_date = $this->bpas->fld($this->input->post('end_date'));
			$this->data['end_date2'] = trim($end_date);
		} else {
			$end_date = $this->bpas->fld(date("d/m/Y"));
			$this->data['end_date2'] = trim($end_date);
		}
		if($this->input->post('supplier')){
			$supplier = $this->input->post('supplier');
			$this->data['supplier2'] = $supplier;
		} else {
			$supplier = null;
			$this->data['supplier2'] = 0;
		}
		if($this->input->post('balance')){
			$balance = $this->input->post('balance');
			$this->data['balance2'] = $balance;
		} else {
			$balance = 'all';
			$this->data['balance2'] = 'all';
		}
		$my_data = NULL;		
		$suppliers = $this->accounts_model->ap_by_supplier($start_date, $end_date, $supplier, $balance, 'supplier');
		$i=0;
		if (!empty($suppliers)) {
			foreach($suppliers as $sup){
				$supplierDatas = $this->accounts_model->ap_by_supplier($sup->start_date, $sup->end_date, $sup->supplier_id, $sup->balance, 'detail');
				foreach($supplierDatas as $suppData){		
					$k = 0;
					foreach($supplierDatas as $sppD) {
						$supplierDatas[$k]->payments = $this->accounts_model->ap_by_supplier($sup->start_date, $sup->end_date, $sup->supplier_id, $sup->balance, 'payment', $sppD->id);;
						$k++;
					}
					$my_data[$i] = array(
						"supplierName" => $sup->supplier,
						"supplierDatas" => array(
							"supplierId" => $suppData->id,
							"suppPO" => $supplierDatas
						)			
					);
				}
				$i++;
			}
		}
		$this->data['my_data'] = $my_data;
		$bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('accounts')));
		$meta = array('page_title' => lang('ap_by_supplier'), 'bc' => $bc);
		$this->page_construct('accounts/ap_by_supplier', $meta, $this->data);
	}
	
	public function checkrefer()
	{
		if($this->input->get('items')){
			$items=$this->input->get('items');
		}else{
			$items = '';
		}
		
		if(is_array($items)){
			$isAuth = 0;
			$first = 1;
			
			for($i=0;$i<sizeof($items);$i++){
				$id = $items[$i]['id'];
				$data=$this->accounts_model->checkrefer($id);
				$new_data = $data->customer;

				if($first == 1){
					$str_old = $new_data;
				}

				if($str_old != $new_data){
					$isAuth = 1;
				}

				$first++;
			}
			echo json_encode(array('isAuth'=>$isAuth));
			exit();
		}
		echo json_encode(2);
	}
	
	public function checkreferPur()
	{
		if($this->input->get('items')){
			$items=$this->input->get('items');
		}else{
			$items = '';
		}
		
		if(is_array($items)){
			$isAuth = 0;
			$first = 1;
			
			for($i=0;$i<sizeof($items);$i++){
				$id = $items[$i]['id'];
				$data=$this->accounts_model->checkreferPur($id);
				$new_data = $data->supplier;

				if($first == 1){
					$str_old = $new_data;
				}

				if($str_old != $new_data){
					$isAuth = 1;
				}

				$first++;
			}
			echo json_encode(array('isAuth'=>$isAuth));
			exit();
		}
		echo json_encode(2);
	}
	
	function list_ar_aging_actions($wh=null)
	{
		if($wh){
			$wh = explode('-', $wh);
		}
	 
		$this->form_validation->set_rules('form_action', lang("form_action"), 'required');	
		if ($this->form_validation->run() == true) {
			$ware = $this->input->post('warehouse2');
			$created = $this->input->post('created_by2');
			$biller = $this->input->post('biller2');
			if($this->input->post('start_date2')){
				$Sdate = $this->bpas->fld($this->input->post('start_date2'));
			}else{
				$Sdate = null;
			}
			if($this->input->post('end_date2')){
				$Edate = $this->bpas->fld($this->input->post('end_date2'));
			}else{
				$Edate = null;
			}		
			
    		if (!empty($_POST['val'])) {
				var_dump($this->input->post('form_action'));
				exit();
        		if ($this->input->post('form_action') == 'export_excel'|| $this->input->post('form_action') == 'export_pdf') {
        			if($this->Owner || $this->Admin){
	        			$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));
						$styleArray = array(
	                        'font'  => array(
	                            'bold'  => true
	                        )
	                    );
	                    
	                    $this->excel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);
	                   	$row = 2; 
	                   	$sum_grand = $sum_paid = $sum_balance = $sum_arNum = 0;
						foreach ($_POST['val'] as $id) {						
							//$account = $this->site->getARaging($id,$ware,$created,$biller,$Sdate,$Edate);

							$account = $this->site->getARaging1($id);
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->customer);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($account->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($account->paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($account->balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ar_number);
							
							$sum_grand += $account->grand_total;
							$sum_paid += $account->paid;
							$sum_balance += $account->balance;
							$sum_arNum += $account->ar_number;
							$row++;				
	                	}

						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($sum_grand));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($sum_paid));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($sum_balance));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sum_arNum);
	                } else {
	                	$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));
						$styleArray = array(
	                        'font'  => array(
	                            'bold'  => true
	                        )
	                    );
	                    
	                    $this->excel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);
	                   	$row = 2; 
	                   	$sum_grand = $sum_paid = $sum_balance = $sum_arNum = 0;
						foreach ($_POST['val'] as $id) {						
							$account = $this->site->getARaging($id,$ware,$created,$biller,$Sdate,$Edate,$wh);
							$sum_grand += $account->grand_total;
							$sum_paid += $account->paid;
							$sum_balance += $account->balance;
							$sum_arNum += $account->ar_number;
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->customer);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($account->grand_total));
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($account->paid));
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($account->balance));
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ar_number);
				
							

							$row++;				
	                	}
	                	$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->formatDecimal($sum_grand));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($sum_paid));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($sum_balance));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sum_arNum);
	                }

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(14);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'list_ar_aging_' . date('Y_m_d_H_i_s');
					if ($this->input->post('form_action') == 'export_pdf') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action') == 'export_excel') {
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_customer_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	function list_ar_aging_actions2()
	{
	 	$this->form_validation->set_rules('form_action2', lang("form_action"), 'required');	
		 if ($this->form_validation->run() == true) {
    		if (!empty($_POST['val'])) {
        		if ($this->input->post('form_action2') == 'export_excel2'|| $this->input->post('form_action2') == 'export_pdf2') {
        			$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));

                   $row = 2;
                   $sum_grand =0;$sum_paid=0;$sum_balance=0;$sum_arNum=0;

					foreach ($_POST['val'] as $id) {						
						$account = $this->site->getARaging2($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ar_number);

						$sum_grand += $account->grand_total;
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$sum_arNum += $account->ar_number;
						$row++;	

                	}
                	//$this->excel->getActiveSheet()->SetCellValue('C' . $row, $total1);
                	$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sum_grand);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sum_paid);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sum_balance);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sum_arNum);

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Bill_Reciept_' . date('Y_m_d_H_i_s');

					if ($this->input->post('form_action') == 'export_pdf2') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action2') == 'export_excel2') {						
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_customer_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
		 $this->session->set_flashdata('error', validation_errors());
		 admin_redirect($_SERVER["HTTP_REFERER"]);
	 	}
	}
	
	function list_ar_aging_actions3()
	{
	 	$this->form_validation->set_rules('form_action3', lang("form_action"), 'required');	
		if ($this->form_validation->run() == true) {
    		if (!empty($_POST['val'])) {
        		if ($this->input->post('form_action3') == 'export_excel3'|| $this->input->post('form_action3') == 'export_pdf3') {
        			$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));

                   	$row = 2;$sum_grand =0;$sum_paid=0;$sum_balance=0;$sum_arNum=0;
					foreach ($_POST['val'] as $id) {						
						$account = $this->site->getARaging3($id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ar_number);
						
						$sum_grand += $account->grand_total;
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$sum_arNum += $account->ar_number;
						$row++;				
                	}

					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sum_grand);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sum_paid);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sum_balance);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sum_arNum);

					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Bill_Reciept_' . date('Y_m_d_H_i_s');

					if ($this->input->post('form_action3') == 'export_pdf3') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action3') == 'export_excel3') {						
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_customer_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
		 	$this->session->set_flashdata('error', validation_errors());
		 	admin_redirect($_SERVER["HTTP_REFERER"]);
	 	}
	}

	function list_ar_aging_actions4()
	{
	 	$this->form_validation->set_rules('form_action4', lang("form_action"), 'required');	
		if ($this->form_validation->run() == true) {
    		if (!empty($_POST['val'])) {
        		if ($this->input->post('form_action4') == 'export_excel4'|| $this->input->post('form_action4') == 'export_pdf4') {
        			$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));

                   $row = 2;$sum_grand =0;$sum_paid=0;$sum_balance=0;$sum_arNum=0;
					foreach ($_POST['val'] as $id) {						
						$account = $this->site->getARaging4($id);
						$sum_grand += $account->grand_total;
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$sum_arNum += $account->ar_number;

						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ar_number);
					

						$row++;				
                	}
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sum_grand);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sum_paid);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sum_balance);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sum_arNum);
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Bill_Reciept_' . date('Y_m_d_H_i_s');

					if ($this->input->post('form_action4') == 'export_pdf4') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action4') == 'export_excel4') {						
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_customer_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
		 $this->session->set_flashdata('error', validation_errors());
		 admin_redirect($_SERVER["HTTP_REFERER"]);
	 	}
	}

	function list_ar_aging_actions5()
	{
	 	$this->form_validation->set_rules('form_action5', lang("form_action"), 'required');	
		if ($this->form_validation->run() == true) {
    		if (!empty($_POST['val'])) {
        		if ($this->input->post('form_action5') == 'export_excel5'|| $this->input->post('form_action5') == 'export_pdf5') {
        			$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('list_ar_aging'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('customer'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('grand_total'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('paid'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('ar_number'));

                   $row = 2;$sum_grand =0;$sum_paid=0;$sum_balance=0;$sum_arNum=0;
					foreach ($_POST['val'] as $id) {						
						$account = $this->site->getARaging5($id);
						$sum_grand += $account->grand_total;
						$sum_paid += $account->paid;
						$sum_balance += $account->balance;
						$sum_arNum += $account->ar_number;

						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $account->customer);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $account->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $account->paid);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $account->balance);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $account->ar_number);
						

						$row++;				
                	}

					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sum_grand);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sum_paid);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $sum_balance);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $sum_arNum);
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
				
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Bill_Reciept_' . date('Y_m_d_H_i_s');

					if ($this->input->post('form_action5') == 'export_pdf5') {
						$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));
						$this->excel->getDefaultStyle()->applyFromArray($styleArray);
						$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
						require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'MPDF';
						$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
								PHP_EOL . ' as appropriate for your directory structure');
						}

						header('Content-Type: application/pdf');
						header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
						
						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
						return $objWriter->save('php://output');
					}
					if ($this->input->post('form_action5') == 'export_excel5') {						
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
						header('Cache-Control: max-age=0');

						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('B' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);

						$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
						return $objWriter->save('php://output');
					}

					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
			} else {
				$this->session->set_flashdata('error', $this->lang->line("no_customer_selected"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
		} else {
		 $this->session->set_flashdata('error', validation_errors());
		 admin_redirect($_SERVER["HTTP_REFERER"]);
	 	}
	}

	function apBySupplier($pdf = null, $excel = null, $ap_statements = null, $start_date2 = null, $end_date2 = null, $supplier2 = null, $balance2 = null)
	{
		if ($pdf || $excel) {
			$my_data[] = "";		
			$suppliers = $this->accounts_model->ap_by_supplier($start_date2, $end_date2, $supplier2, $balance2, 'supplier', null);
			$i=0;
			foreach($suppliers as $sup){
				$supplierDatas = $this->accounts_model->ap_by_supplier($sup->start_date, $sup->end_date, $sup->supplier_id, $sup->balance, 'detail');
				foreach($supplierDatas as $suppData){
					$k = 0;
					foreach($supplierDatas as $sppD) {
						$supplierDatas[$k]->payments = $this->accounts_model->ap_by_supplier($sup->start_date, $sup->end_date, $sup->supplier_id, $sup->balance, 'payment', $sppD->id);;
						$k++;
					}
					$my_data[$i] = array(
						"supplierName" => $sup->supplier,
						"supplierDatas" => array(
							"supplierId" => $suppData->id,
							"suppPO" => $supplierDatas
						)									
					);					
				}
				$i++;
			}
			// $this->bpas->print_arrays($my_data);
			if (!empty($my_data)) {
				$this->load->library('excel');
				$this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('Sales List'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('type'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('return'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('deposit'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('discount'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
				$styleArray = array(
                    'font'  => array(
                        'bold'  => true
                    )
                );    
				$this->excel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);
				$row = 2;
				foreach ($my_data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, "Supplier >> ". $data_row["supplierName"]);
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':I'.$row);                    
					$subTotal = $subReturn = $subDeposit = $subPaid = $subDiscount = $gbalance= 0;
					foreach ($data_row['supplierDatas']['suppPO'] as $value) {
						$row++;
						$subTotal += $value->grand_total;
						$subReturn += $value->amount_return;
						$subDeposit += $value->amount_deposit;
						$subDiscount += $value->order_discount;
						$sub_balance = ($value->grand_total - $value->amount_return - $value->amount_deposit - $value->order_discount);
						$gbalance += $sub_balance;
						$type = (explode('/', $value->reference_no)[0]=='PO'?"Purchase":(explode('/', $value->reference_no)[0]=='PV'?"Payment":"Not Assigned"));
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $value->reference_no." ");
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $value->date);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, 'Purchase');
						// $this->excel->getActiveSheet()->SetCellValue('C' . $row, $type);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $value->grand_total);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $value->amount_return);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row,'');
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $value->amount_deposit);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $value->order_discount);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sub_balance);
						if (is_array($value->payments)) {						
							foreach ($value->payments as $cusPmt) {
								$row++;                    		
								$subPaid += abs($cusPmt->amount);
								$typePV = (explode('/', $cusPmt->reference_no)[0]=='PO'?"Purchase":(explode('/', $cusPmt->reference_no)[0]=='PV'?"Payment":"Not Assigned"));
								$this->excel->getActiveSheet()->SetCellValue('A' . $row, $cusPmt->reference_no." ");                
								$this->excel->getActiveSheet()->SetCellValue('B' . $row, $cusPmt->date);
								$this->excel->getActiveSheet()->SetCellValue('C' . $row, 'Payment');
								// $this->excel->getActiveSheet()->SetCellValue('C' . $row, $typePV);
								$this->excel->getActiveSheet()->SetCellValue('D' . $row, '');
								$this->excel->getActiveSheet()->SetCellValue('E' . $row, '');
								$this->excel->getActiveSheet()->SetCellValue('F' . $row, $cusPmt->amount);
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, '');
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, '');
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $sub_balance - abs($cusPmt->amount));
							}
						}
					}
					$row++;
					$gbalance -= abs($subPaid);
					$sub_balance -= abs($cusPmt->amount);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, 'Total >>');
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $subTotal);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $subReturn);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $subPaid);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $subDeposit);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $subDiscount);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $gbalance);
					if($excel){               
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('C' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('D' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('E' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('F' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('F' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('G' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('G' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('H' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('H' . $row.'')->getFont()->setBold(true);
						$this->excel->getActiveSheet()->getStyle('I' . $row.'')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border:: BORDER_THIN);
						$this->excel->getActiveSheet()->getStyle('I' . $row.'')->getFont()->setBold(true);
					}
					$row++; 
				}

				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$filename = lang('A/P_by_supplier');
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				if ($pdf) {
					$styleArray = array(
						'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
					);
					$this->excel->getDefaultStyle()->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					require_once(APPPATH . "third_party" . DIRECTORY_SEPARATOR . "MPDF" . DIRECTORY_SEPARATOR . "mpdf.php");
					$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
					$rendererLibrary = 'MPDF';
					$rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . $rendererLibrary;
					if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
						die('Please set the $rendererName: ' . $rendererName . ' and $rendererLibraryPath: ' . $rendererLibraryPath . ' values' .
							PHP_EOL . ' as appropriate for your directory structure');
					}
					header('Content-Type: application/pdf');
					header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
					header('Cache-Control: max-age=0');
					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
					$objWriter->save('php://output');
					exit();
				}
				if ($excel) {
					ob_clean();
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
					header('Cache-Control: max-age=0');

					ob_clean();
					$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
					$objWriter->save('php://output');
					$this->session->unset_userdata('Sdate');
					$this->session->unset_userdata('Edate');
					$this->session->unset_userdata('supplier');
					$this->session->unset_userdata('balance');
					exit();
				}
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		} elseif ($ap_statements) {
			$this->data['biller']        = $this->Settings->default_biller ? $this->site->getCompanyByID($this->Settings->default_biller) : null;
          	$this->data['start_date']    = $start_date2;
            $this->data['end_date']      = $end_date2;
            $this->data['balance']       = $balance2;
            $this->data['suppliers']     = $this->accounts_model->ap_by_supplierV2($start_date2, $end_date2, $supplier2, $balance2);
            $this->load->view($this->theme . 'accounts/ap_statements', $this->data);
		}
	}
	public function tansfer($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $payment = $this->sales_model->getPaymentByID($id);
        $sale = $this->sales_model->getInvoiceByID($payment->sale_id);

        /*if ($payment->type == 'returned') {
            $this->session->set_flashdata('warning', lang('payment_was_returned'));
            $this->bpas->md();
        }*/

        if($sale->sale_status == 'returned'){
        	$getTranByID = $this->accounts_model->getTranByID('Payment',$payment->sale_id,$payment->reference_no);
        }else{
	        $getTranByID = $this->accounts_model->getTranByID('Payment',$payment->sale_id,$sale->reference_no);
	    }
	  
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
           
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }

            $payment = [
                'transfer'         => 1,
                'bank_account' => $this->input->post('bank_account')
            ];

            $tran_date 			= $this->bpas->fld(trim($this->input->post('date')));
            $tran_no = $this->accounts_model->getTranNo();
            $reference_no 		=   $this->input->post('reference_no');//$this->site->getReference('jr');
            //=====add accounting=====//
            if($this->Settings->accounting == 1){
            	if($this->input->post('amount-paid') >0){
            		$total_amount = -($this->input->post('amount-paid'));
            		$bank_account_amount = $this->input->post('bank_account_amount');
            		$bank_charge_amount = $this->input->post('bank_charge_amount');
            	}else{
            		$total_amount =  ((-1) * $this->input->post('amount-paid'));
            		$bank_account_amount = $this->input->post('bank_account_amount');
            		$bank_charge_amount = $this->input->post('bank_charge_amount');
            	}

                if ($this->input->post('bank_account_amount')) {
            		$accTranPayments[] = array(
	                    'tran_no' => $tran_no,
	                    'tran_type' => 'JOURNAL',
	                    'tran_date' => $tran_date,
	                    'reference_no' => $reference_no,
	                    'account_code' => $this->accounting_setting->default_cash,
	                    'amount' => $total_amount,
	                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_cash),
	                    'description' => 'Transfer '.$this->input->post('note'),
	                    'biller_id' => $this->session->userdata('biller_id'),
	                    'created_by'  => $this->session->userdata('user_id'),
	                );

                    $accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->input->post('bank_account'),
                        'amount' => $bank_account_amount,
                        'narrative' => $this->site->getAccountName($this->input->post('bank_account')),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' => $this->session->userdata('biller_id'),
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if ($this->input->post('bank_charge_amount')) {
                    $accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->input->post('bank_charge'),
                        'amount' => $bank_charge_amount,
                        'narrative' => $this->site->getAccountName($this->input->post('bank_charge')),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' =>$this->session->userdata('biller_id'),
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }

            }
            //=====end accounting=====//
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->accounts_model->update_GT($id,$payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ExpenseAccounts'] = $this->accounts_model->getAllChartAccountIn('50,60,80');
            $this->data['inv']         = $payment;
            $this->data['payment_trans']         = $getTranByID;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'accounts/transfer_account', $this->data);
        }
    }
    function multi_tansfers()
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        $arr = array();
        if ($this->input->get('data'))
        {
            $arr = explode(',', $this->input->get('data'));
        }

		$this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('bank_account', lang("bank_account"), 'required');
		$this->form_validation->set_rules('bank_charge', lang("bank_charge"), 'xss_clean');

		$this->form_validation->set_rules('biller', lang("biller"), 'required|xss_clean');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

			$accTranPayments = [];
            $tran_date    = $this->bpas->fld(trim($this->input->post('date')));
            $tran_no      = $this->accounts_model->getTranNo();
            $reference_no =  $this->input->post('reference_no'); //$this->site->getReference('jr');
            //=====add accounting=====//
            if($this->Settings->accounting == 1){

            	$total_amount = -($this->input->post('amount-paid'));
        		$bank_account_amount = $this->input->post('bank_account_amount');
        		$bank_charge_amount = $this->input->post('bank_charge_amount');
            	$biller_id = $this->input->post('biller');

            	$bank_account2 = $this->input->post('bank_account2') ? $this->input->post('bank_account2') : '';
            	$bank_account3 = $this->input->post('bank_account3') ? $this->input->post('bank_account3') : '';
            	$bank_account4 = $this->input->post('bank_account4') ? $this->input->post('bank_account4') : '';

        		$bank_account_amount2 = ($bank_account2 && $this->input->post('bank_account_amount2')) ? $this->input->post('bank_account_amount2') : 0;
        		$bank_account_amount3 = ($bank_account3 && $this->input->post('bank_account_amount3')) ? $this->input->post('bank_account_amount3') : 0;
        		$bank_account_amount4 = ($bank_account4 && $this->input->post('bank_account_amount4')) ? $this->input->post('bank_account_amount4') : 0;
            
            	$sum_transfer = $bank_charge_amount + $bank_account_amount + $bank_account_amount2 + $bank_account_amount3 + $bank_account_amount4;
            	
            	if($this->bpas->formatDecimal($sum_transfer) != $this->bpas->formatDecimal($this->input->post('amount-paid'))){
            		$this->session->set_flashdata('error', 'Amount Transfer not Balance');
                    redirect($_SERVER["HTTP_REFERER"]);
            	}

                if ($this->input->post('bank_account_amount')) {
                    $accTranPayments[] = array(
                        'tran_no'		=> $tran_no,
                        'tran_type' 	=> 'JOURNAL',
                        'tran_date' 	=> $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_cash,
                        'amount' 		=> $total_amount,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_cash),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' 	=> $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );

                    $accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->input->post('bank_account'),
                        'amount' => $bank_account_amount,
                        'narrative' => $this->site->getAccountName($this->input->post('bank_account')),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }

                if($bank_account2 && $bank_account_amount2){
                	$accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $bank_account2,
                        'amount' => $bank_account_amount2,
                        'narrative' => $this->site->getAccountName($bank_account2),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($bank_account3 && $bank_account_amount3){
                	$accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->input->post('bank_account'),
                        'amount' => $bank_account_amount3,
                        'narrative' => $this->site->getAccountName($bank_account3),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($bank_account4 && $bank_account_amount4){
                	$accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $bank_account4,
                        'amount' => $bank_account_amount4,
                        'narrative' => $this->site->getAccountName($bank_account4),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }

                //-------------add charge------------

                if ($this->input->post('bank_charge_amount')) {
                    $accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'JOURNAL',
                        'tran_date' => $tran_date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->input->post('bank_charge'),
                        'amount' 		=> $bank_charge_amount,
                        'narrative' 	=> $this->site->getAccountName($this->input->post('bank_charge')),
                        'description' => 'Transfer '.$this->input->post('note'),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }
            //=====end accounting=====//

            $photo = '';
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
            
            if ($this->accounts_model->inset_bankcharge($accTranPayments)) {
				$payment_array = $this->input->post('payment_id');
	            $bank_charge_amount = $this->input->post('bank_charge_amount');
	            $bank_charge_amount_1 = $this->input->post('bank_charge_amount_1');
	            $bank_transfer_amount = $this->input->post('bank_account_amount');
	            $bank_account_amount_1 = $this->input->post('bank_account_amount_1');
	            $i = 0;
	            $accTranPayments =[];
	            foreach($payment_array as $payment_id){
		            $payment_data = [
		                'transfer'         => 1,
		                'bank_account' 		=> $this->input->post('bank_account'),
		                'transfer_amount'	=> $bank_account_amount_1[$i],
		                'bank_charge' 		=> $bank_charge_amount_1[$i],
		            ];

	           		$this->accounts_model->update_transfer($payment_id, $payment_data);
	                $i++;
				}

	            $this->session->set_flashdata('message', lang("payment_added"));
	           	// redirect($_SERVER['HTTP_REFERER']);
	           	admin_redirect('account/tansfer_payment');
			}

			$status = 0;
			$amount_paid = $this->input->post('amount-paid');
			$bank_charge_amount = $this->input->post('bank_charge_amount');
			$bank_account_amount = $this->input->post('bank_account_amount');
			$bank_account_amount2 = $this->input->post('bank_account_amount2');
			$bank_account_amount3 = $this->input->post('bank_account_amount3');
			$bank_account_amount4 = $this->input->post('bank_account_amount4');
			if($amount_paid - ($bank_charge_amount + $bank_account_amount) <= 0){
				$status = 1;
			}

			$acc_transfer = [
				'date'             => $tran_date,
				'reference_no'     => $reference_no,
				'charge_account'   => $this->input->post('bank_charge'),
				'charge_amount'    => $bank_charge_amount,
				'transfer_account' => $this->input->post('bank_account'),
				'transfer_amount'  => $bank_account_amount,
				'transfer_amount2'  => $bank_account_amount2,
				'transfer_amount3'  => $bank_account_amount3,
				'transfer_amount4'  => $bank_account_amount4,
				'status'   		   => $status,
			];

			$item_payment_array 		= $this->input->post('payment_id');
			$item_payment_date 			= $this->input->post('item_payment_date');
			$item_payment_ref_no 		= $this->input->post('item_payment_ref_no');
			$item_payment_paid_by 		= $this->input->post('item_payment_paid_by');
			$item_payment_amount 		= $this->input->post('amount');
			$item_bank_charge_amount 	= $this->input->post('bank_charge_amount_1');
			$item_bank_account_amount 	= $this->input->post('bank_account_amount_1');

			$account_transfer_item = [];
			foreach($item_payment_array as $index => $val){
		        $account_transfer_item[] = array(
		            'date' 				=> $this->bpas->fld(trim($item_payment_date[$index])),
		            'reference_no'		=> $item_payment_ref_no[$index],
					'paid_by' 			=> $item_payment_paid_by[$index],
					'amount' 			=> $item_payment_amount[$index],
					'charge_amount' 	=> $item_bank_charge_amount[$index],
					'transfer_amount' 	=> $item_bank_account_amount[$index],
				);
			}
			
			$this->accounts_model->addAccTransfer($acc_transfer, $account_transfer_item);
			$this->session->set_flashdata('message', lang("payment_added"));
			admin_redirect('account/tansfer_payment');
        } else {
            $setting = $this->site->get_setting();
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $this->data['combine_sales'] = $this->accounts_model->getCombineByPaymentId($arr);
            $this->data['payment_ref'] = $this->site->getReference('so');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
                $biller_id = $this->Settings->default_biller;
                $this->data['reference'] = $this->site->getReference('pp');
            }else{
                $biller_id = $this->session->userdata('biller_id');
                $this->data['reference'] = $this->site->getReference('pp');
            }
            //$this->data['idd'] = $idd;
			$this->data['ExpenseAccounts'] = $this->accounts_model->getAllChartAccountIn('50,60,80');
            $this->data['currency']         = $this->site->getCurrency();
            $this->data['setting'] = $setting;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['receivable'] = "receivable"; 
            $this->load->view($this->theme . 'accounts/multi_tansfer', $this->data);
        }
    }
    function bank_concile_form()
    {

   		// $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        $arr = array();
        if ($this->input->get('data'))
        {
            $arr = explode(',', $this->input->get('data'));
        }
        //----------
        $arr1 = array();
        if ($this->input->get('data1'))
        {
            $arr1 = explode(',', $this->input->get('data1'));
        }
        //-----
        $account_code = $this->input->get('acc') ? $this->input->get('acc') : '';
        $start_date = $this->input->get('start')? $this->input->get('start') : '';
        $end_date = $this->input->get('end') ? $this->input->get('end') : '';


        if ($this->form_validation->run() == true) {

            $this->session->set_flashdata('message', lang("payment_added"));
            admin_redirect('reports/bank_reconcile');

        } else{
        	
            $setting = $this->site->get_setting();
            if($this->session->userdata('biller_id')) {
                $biller_id = $this->session->userdata('biller_id');
            }else {
                $biller_id = $setting->default_biller;
            }
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
       
            $this->data['getTrans'] = $this->accounts_model->getGlById($arr);
            $this->data['getTrans_book'] = $this->accounts_model->getGlById($arr1);
            
            $this->data['payment_ref'] = $this->site->getReference('so');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
                $biller_id = $this->site->get_setting()->default_biller;
            }else{
                $biller_id = $this->session->userdata('biller_id'); 
            }
            $this->data['reference'] = $this->site->getReference('pp');
            //$this->data['idd'] = $idd;
            $this->data['account_code'] = $account_code;
            $this->data['start_date'] = $start_date;
            $this->data['end_date'] = $end_date;

            $this->data['currency']         = $this->site->getCurrency();
            $this->data['setting'] = $setting;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['receivable'] = "receivable"; 

            $this->load->view($this->theme . 'accounts/bank_concile_form', $this->data);
        }
    }
    function bank_concile_save()
    {

   //     $this->bpas->checkPermissions('payments', true);
    

        if ($this->form_validation->run() == true) {

            $this->session->set_flashdata('message', lang("payment_added"));
            admin_redirect('reports/bank_reconcile');

        } else{

        	$data = [
                'account_code' 	=> $this->input->post('account_code'),
                'start_date'	=> $this->bpas->fsd($this->input->post('start_date')),
                'end_date' 		=> $this->bpas->fsd($this->input->post('end_date')),
                'balance_bank' 	=> $this->input->post('ending_bank'),
                'balance_book' 	=> $this->input->post('ending_book'),
                'biller_id'		=> $this->input->post('biller'),
                'created_by'	=> $this->session->userdata('user_id')
            ];

            $adjust_bank 		= $this->input->post('adjust_bank');
			$adjust_book 		= $this->input->post('adjust_book');
			
			$tran_id = $this->input->post('tran_id');
			$amount = $this->input->post('amount');
			$description = $this->input->post('description');
			

			$account_bank_item = [];
			foreach($adjust_bank as $index => $val){
		        $account_bank_item[] = array(
		        	'bank_type'			=> 0,
		            'gl_id' 			=> $tran_id[$index],
		            'amount'		    => $amount[$index],
					'description' 	    => $description[$index],
				);
			}
			//----------------
			$tran_id1 = $this->input->post('tran_id1');
			$amount1 = $this->input->post('amount1');
			$description1 = $this->input->post('description1');

			$account_book_item = [];
			foreach($adjust_book as $index1 => $val1){
		        $account_book_item[] = array(
		        	'bank_type'			=> 1,
		            'gl_id' 			=> $tran_id1[$index1],
		            'amount'		    => $amount1[$index1],
					'description' 	    => $description1[$index1],
				);
			}
            $result = $this->accounts_model->addBankReconsile($data,$account_bank_item,$account_book_item);
            if($result){
            	$this->session->set_flashdata('message', lang("date_has_been_save"));
				admin_redirect('reports/bank_reconcile');
            }

        }
    }
    public function asset_expense($id = null)
    {
     //   $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $payment = $this->site->getevaluationByID($id);
	 
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        if ($this->form_validation->run() == true) {
           	
         
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }

            $payment = [
                'is_expense'        => 1,
                'asset_account' 	=> $this->input->post('asset_account'),
                'expense_account' 	=> $this->input->post('expense_account'),
                'reference_no' 		=> $this->input->post('reference_no'),
                'biller_id'			=> $this->input->post('biller_id'),
                'created_by'		=> $this->session->userdata('user_id'),
            ];

            $tran_date 			= $this->bpas->fld(trim($this->input->post('date')));
            $tran_no = $this->accounts_model->getTranNo();
            $reference_no 		=   $this->input->post('reference_no');//$this->site->getReference('jr');
            //=====add accounting=====//
            if($this->Settings->accounting == 1){
            	
        		$asset_account = $this->input->post('asset_account');
        		$expense_account = $this->input->post('expense_account');
            	
            	$asset_amount = $this->input->post('amount');
            	$expense_amount = (-1)*$this->input->post('amount');

     
            		$accTranPayments[] = array(
	                    'tran_no' 		=> $tran_no,
	                    'tran_type' 	=> 'JOURNAL',
	                    'tran_date' 	=> $tran_date,
	                    'reference_no' 	=> $reference_no,
	                    'account_code' 	=> $asset_account,
	                    'amount' 		=> $asset_amount,
	                    'narrative' 	=> $this->site->getAccountName($asset_account),
	                    'description' 	=> $this->input->post('note'),
	                    'biller_id' 	=> $this->input->post('biller_id'),
	                    'created_by'  	=> $this->session->userdata('user_id'),
	                );

                    $accTranPayments[] = array(
                        'tran_no' 		=> $tran_no,
                        'tran_type' 	=> 'JOURNAL',
                        'tran_date' 	=> $tran_date,
                        'reference_no' 	=> $reference_no,
                        'account_code' 	=> $expense_account,
                        'amount' 		=> $expense_amount,
                        'narrative' 	=> $this->site->getAccountName($expense_account),
                        'description' 	=> $this->input->post('note'),
                        'biller_id' 	=> $this->input->post('biller_id'),
                        'created_by'  	=> $this->session->userdata('user_id'),
                    );
                
            }else{
            	$accTranPayments =[];
            }
            //=====end accounting=====//
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && 
        	$this->accounts_model->add_depreciation($id,$payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('assets');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['ExpenseAccounts'] = $this->accounts_model->getAllChartAccountIn('50,60,80');
            $this->data['sectionacc'] = $this->accounts_model->getAllChartAccount();
            $this->data['inv']         = $payment;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
         //   $this->data['payment_trans']         = $getTranByID;
            $this->data['depreciation_ref'] = $this->site->getReference('dp');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'accounts/asset_expense', $this->data);
        }
    }
    function concile_form_report($id = null){

   //     $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->form_validation->run() == true) {

            $this->session->set_flashdata('message', lang("payment_added"));
            admin_redirect('reports/bank_reconcile');

        } else{
  
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
         
            $this->data['payment_ref'] = $this->site->getReference('so');
            if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')){
                $biller_id = $this->site->get_setting()->default_biller;
            }else{
                $biller_id = $this->session->userdata('biller_id'); 
            }
            $this->data['reference'] = $this->site->getReference('pp');

            $this->data['getReconcile'] = $this->accounts_model->getReconcile($id);
            $this->data['getReconcileItems'] = $this->accounts_model->getReconcileItems($id);
            $this->data['currency']         = $this->site->getCurrency();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['receivable'] = "receivable"; 

            $this->load->view($this->theme . 'accounts/reconcile_form_report', $this->data);
        }
    }
    function bank_reconcile($pdf = NULL, $xls = null, $biller_id = NULL, $start_date = null, $end_date = null)
    {
        $this->bpas->checkPermissions('ledger',NULL,'account_report');      
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();                
        $user = $this->site->getUser();

        $start_date = $this->input->get('sd');
        $end_date = $this->input->get('ed');

        if($this->input->post('submit')){
            if(!$this->input->post('ending_balance')){
                $this->session->set_flashdata('error', lang('please_enter_ending_balance'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
        $biller_id = $biller_id ? $biller_id : $this->input->get('biller');

        
        $this->data['v_account'] = $this->input->post('account') ? $this->input->post('account[]') : $this->input->get('account');
        $this->data['interest_earned_acc'] = $this->input->post('interest_earned_acc') ? $this->input->post('interest_earned_acc') : $this->input->get('interest_earned_acc'); 
        $this->data['service_charge_acc'] = $this->input->post('service_charge_acc') ? $this->input->post('service_charge_acc') : $this->input->get('service_charge_acc'); 

        $get_account_code = $this->input->get('account');
   
        $acc_code = $this->input->post('account') ? $this->input->post('account') : $this->input->get('account'); 

        if (isset($acc_code)) {
            $this->data['multi_account'] = $acc_code;//$get_account_code ? $get_account_code : implode(',', $this->input->post('account[]'));
            $this->data['v_multi_account'] =  $acc_code;//$get_account_code ? $get_account_code : implode('_', $this->input->post('account[]'));
        }else{
            $this->data['multi_account'] = '';
            $this->data['v_multi_account'] = '';
        }

        $this->data['ending_balance'] = $this->input->post('ending_balance')? $this->input->post('ending_balance') : $this->input->get('ending_balance');
        $this->data['service_charge'] = $this->input->post('service_charge')? $this->input->post('service_charge') : $this->input->get('service_charge');
        $this->data['interest_earned'] = $this->input->post('interest_earned')? $this->input->post('interest_earned') : $this->input->get('interest_earned');

        $this->data['start_date'] = $this->input->post('start_date')? $this->input->post('start_date') : $this->input->get('start_date');
        $this->data['end_date'] = $this->input->post('end_date') ? $this->input->post('end_date') : $this->input->get('end_date');
        $this->data['v_form'] = $v_form = "0/0/".$biller_id;
        
        $this->data['have_filter'] = $this->uri->segment(4);

        if($biller_id != NULL){
            $this->data['biller_id'] = $biller_id;
        }else{
            if($user->biller_id){
                //$this->data['biller_id'] = $user->biller_id;
                //$biller_id = $user->biller_id;

                $this->data['biller_id'] = '';
                $biller_id = '';
            }else{
                $this->data['biller_id'] = "";
            }
        }       
        if(!$this->Owner && !$this->Admin) {
            if($user->biller_id){
                $this->data['billers'] = $this->site->getCompanyByArray($user->biller_id);
            }else{
                $this->data['billers'] = $this->site->getAllCompanies('biller');
            }
        }else{
            $this->data['billers'] = $this->site->getAllCompanies('biller');
        }
        
        if ($this->input->get('start_date')) {
            $dt = "From " . $this->input->get('start_date') . " to " . $this->input->get('end_date');
        } else {
            $dt = "Till " . $this->input->get('end_date');
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('bank_reconciliation')));
        $meta = array('page_title' => lang('bank_reconciliation'), 'bc' => $bc);
        $this->page_construct('accounts/bank_resoncile', $meta, $this->data);
    }
    function sync_reconcile()
    {   
        
        // $this->bpas->checkPermissions('ledger',NULL,'account_report');      
        // $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        // $this->data['categories'] = $this->site->getAllCategories();                
        $user = $this->site->getUser();
        $this->form_validation->set_rules('total_credit', lang('total_credit'), 'required');
        $this->form_validation->set_rules('total_balance', lang('total_balance'), 'required');
        $this->form_validation->set_rules('total_difference', lang('total_difference'), 'required');
        $this->form_validation->set_rules('total_Endbalance', lang('total_Endbalance'), 'required');

          if ($this->form_validation->run() == true) {
            $total_debit = $this->input->post('total_debit');
            $total_credit = $this->input->post('total_credit'); 
            $total_endingbalance = $this->input->post('total_Endbalance');
            $total_balance = $this->input->post('total_balance');
            $total_difference = $this->input->post('total_difference');
            $account_code = $this->input->post('account');
            $user_id = $this->session->userdata('user_id');
            $arr_debit = explode(',', $this->input->post('debit_val'));
            $arr_credit = explode(',', $this->input->post('credit_val'));
    		
    		$service_charge = $this->input->post('charge_amount');
    		$service_charge_acc = $this->input->post('charge_amount_acc');

    		$interest_earned = $this->input->post('earned_amount');
    		$interest_earned_acc = $this->input->post('earned_amount_acc');
    		
			$data = [
                'end_date'        	=> $this->bpas->fsd($this->input->post('end_date')),
                'created_by'        => $user_id,
                'account_code'      => $account_code,
                'service_charge'	=> $service_charge,
                'charge_account'	=> $service_charge_acc,
                'interest_earned'	=> $interest_earned,
                'interest_account'	=> $interest_earned_acc,
                'debit_balance'     => $total_debit,
                'credit_balance'    => $total_credit,
                'ending_balance'    => $total_endingbalance,
                'clared_balance'    => $total_balance,
                'difference_balance'=> $total_difference,
            ];
			if($arr_debit == null && $arr_credit == null){
				$this->session->set_flashdata('error', lang("bank_reconciled_error"));
            	admin_redirect('account/bank_reconcile');
			}else{
			if($this->db->insert('bank_reconsile', $data)){
				$id = $this->db->insert_id();

				//-------------add charge------------
                if ($this->input->post('service_charge')) {
                	$tran_no      = $this->accounts_model->getTranNo();
                	$biller_id = $this->Settings->default_biller;

                    $accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'Expense',
                        'tran_date' => $this->bpas->fsd($this->input->post('end_date')),
                        'reference_no' => 'Reconcile-'.$id,
                        'account_code' => $service_charge_acc,
                        'amount' => $service_charge,
                        'narrative' => $this->site->getAccountName($service_charge_acc),
                        'description' => '',
                        'biller_id' =>$biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'reconciled' => 1
                    );
                    $accTranPayments[] = array(
                        'tran_no' => $tran_no,
                        'tran_type' => 'Expense',
                        'tran_date' => $this->bpas->fsd($this->input->post('end_date')),
                        'reference_no' => 'Reconcile-'.$id,
                        'account_code' => $account_code,
                        'amount' => -1* $service_charge,
                        'narrative' => $this->site->getAccountName($account_code),
                        'description' => '',
                        'biller_id' =>$biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'reconciled' => 1
                    );

                    $this->accounts_model->inset_bankcharge($accTranPayments);
                }

				
				for ($i=0; $i < sizeof($arr_debit); $i++) { 
            	    $item_debit = $this->accounts_model->getGl_TranById($arr_debit[$i]);
					if($item_debit){
						$items_debit = [
							'reconsile_id' => $id,
							'gl_id'		   => $item_debit->tran_id,
							'amount'	   => $item_debit->amount,
							'description'  => $item_debit->narrative,
						];
						if($this->db->insert('bank_reconsile_items',$items_debit)){
							$this->accounts_model->updateReconciled($item_debit->tran_id);	
						}
					}
					
            	}
            	for ($k=0; $k < sizeof($arr_credit); $k++) { 
            	    $item_credit = $this->accounts_model->getGl_TranById($arr_credit[$k]);
					if($item_credit){
						$items_credit = [
							'reconsile_id' => $id,
							'gl_id'		   => $item_credit->tran_id,
							'amount'	   => $item_credit->amount,
							'description'  => $item_credit->narrative,
						];
						if($this->db->insert('bank_reconsile_items',$items_credit)){
							$this->accounts_model->updateReconciled($item_credit->tran_id);	
						}
					}
					
            	}
				$this->session->set_flashdata('message', lang("bank_reconciled"));
            	admin_redirect('account/bank_reconcile');
			}else{
				$this->session->set_flashdata('error', lang("bank_reconciled_error"));
            	admin_redirect('account/bank_reconcile');
			}	
			}
			if($this->db->insert('bank_reconsile', $data)){
				$id = $this->db->insert_id();
				for ($i=0; $i < sizeof($arr_debit); $i++) { 
            	    $item_debit = $this->accounts_model->getGl_TranById($arr_debit[$i]);
					if($item_debit){
						$items_debit = [
							'reconsile_id' => $id,
							'gl_id'		   => $item_debit->tran_id,
							'amount'	   => $item_debit->amount,
							'description'  => $item_debit->narrative,
						];
						if($this->db->insert('bank_reconsile_items',$items_debit)){
							$this->accounts_model->updateReconciled($item_debit->tran_id);	
						}
					}
					
            	}
            	for ($k=0; $k < sizeof($arr_credit); $k++) { 
            	    $item_credit = $this->accounts_model->getGl_TranById($arr_credit[$k]);
					if($item_credit){
						$items_credit = [
							'reconsile_id' => $id,
							'gl_id'		   => $item_credit->tran_id,
							'amount'	   => $item_credit->amount,
							'description'  => $item_credit->narrative,
						];
						if($this->db->insert('bank_reconsile_items',$items_credit)){
							$this->accounts_model->updateReconciled($item_credit->tran_id);	
						}
					}
					
            	}
				$this->session->set_flashdata('message', lang("bank_reconciled"));
            	admin_redirect('account/bank_reconcile');
			}
        } elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('account/bank_reconcile');
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('reports'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('bank_reconciliation')));
        $meta = array('page_title' => lang('bank_reconciliation'), 'bc' => $bc);
        $this->page_construct('reports/bank_resoncile', $meta, $this->data);
    }
   
    public function modal_view($code,$start_date,$end_date,$begin,$biller_id,$project_id)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		if($start_date=='x'){
			$start_date = false;
		}
		if($end_date=='x'){
			$end_date = false;
		}
		if($begin=='x'){
			$begin = false;
		}
		if($biller_id=='x'){
			$biller_id = false;
		}else{
            $biller_id = str_replace("a",",",$biller_id);
            if($biller_id){
                $this->data['billers'] = $this->accounts_model->getBillersByID($biller_id);
            }
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->data['biller'] = $this->site->getCompanyByID($this->session->userdata('biller_id'));
		}else{
			$company = $this->site->getAllCompanies('biller');
			$this->data['biller'] = $this->site->getCompanyByID($company[0]->id);
		}
		
		if($project_id=='x'){
			$project_id = false;
		}else{
            $project_id = str_replace("a",",",$project_id);
            if($project_id){
                $this->data['projects'] = $this->accounts_model->getProjectsByID($project_id);
            }
			
		}
		$rows = $this->accounts_model->getAccTranByCode($code,$start_date,$end_date,$begin,$biller_id,$project_id);
		$this->data['rows'] = $rows;
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['account'] = $this->accounts_model->getAccountByCode($code);
        $this->load->view($this->theme . 'accounts/modal_view', $this->data);
    }

    public function ar_aging_form()
    {
    	$this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $warehouse  = $this->input->get('warehouse')  ? $this->input->get('warehouse') : null;
        $biller     = $this->input->get('biller')     ? $this->input->get('biller') : null;
        $customer   = $this->input->get('customer')   ? $this->input->get('customer') : null;
        $user       = $this->input->get('user')       ? $this->input->get('user') : null;
        $start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : null;
        $end_date   = $this->input->get('end_date')   ? $this->bpas->fld($this->input->get('end_date')) : null;

        $this->data['ar_aging'] = $this->accounts_model->getAR_Aging($warehouse, $biller, $user, $customer, $start_date, $end_date);
        $this->data['biller']   = $this->Settings->default_biller ? $this->site->getCompanyByID($this->Settings->default_biller) : null;

        $this->load->view($this->theme . 'accounts/ar_aging_form', $this->data);
    }

    public function ap_aging_form()
    {
    	$this->bpas->checkPermissions('index', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $warehouse  = $this->input->get('warehouse')  ? $this->input->get('warehouse') : null;
        $biller     = $this->input->get('biller')     ? $this->input->get('biller') : null;
        $supplier   = $this->input->get('supplier')   ? $this->input->get('supplier') : null;
        $user       = $this->input->get('user')       ? $this->input->get('user') : null;
        $start_date = $this->input->get('start_date') ? $this->bpas->fld($this->input->get('start_date')) : null;
        $end_date   = $this->input->get('end_date')   ? $this->bpas->fld($this->input->get('end_date')) : null;

        $this->data['ap_aging'] = $this->accounts_model->getAP_Aging($warehouse, $biller, $user, $supplier, $start_date, $end_date);
        $this->data['biller']   = $this->Settings->default_biller ? $this->site->getCompanyByID($this->Settings->default_biller) : null;

        $this->load->view($this->theme . 'accounts/ap_aging_form', $this->data);
    }

    public function ap_requested()
    {
       // $this->bpas->checkPermissions('payments');
        $this->data['error']        = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users']        = $this->site->getStaff();
        $this->data['billers']      = $this->site->getAllCompanies('biller');
        $bc                         = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('payments_report')]];
        $meta                       = ['page_title' => lang('payments_report'), 'bc' => $bc];
        $this->page_construct('accounts/ap_requested', $meta, $this->data);
    }
    public function getAP_Requested($biller_id = false){
		//	$this->bpas->checkPermissions("payments");
        $delete_link = "<a href='#' class='delete_payment po' title='<b>" . $this->lang->line("delete_payment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete_ApRequested/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_payment') . "</a>";
		$approve_link='';
		if($this->Admin || $this->Owner || $this->GP['purchases-payments']){

			$approve_link = anchor('admin/purchases/add_approved_payment/$1', '<i class="fa fa-money"></i> ' . lang('approved'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $approve_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	payments_requested.id as id, 
					DATE_FORMAT({$this->db->dbprefix('payments_requested')}.date, '%Y-%m-%d %T') as date, 
                    " . $this->db->dbprefix('payments_requested') . '.reference_no as payment_ref, 
                    ' . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref,
                    supplier,
					paid_by,
					payments_requested.amount,
					payments_requested.type
					")
			->from("payments_requested")
			->join('purchases', 'payments_requested.purchase_id=purchases.id', 'left');

		$this->datatables->where("payments_requested.type",'pending');
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("payments_requested.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function tansfer_payment(){
        $this->bpas->checkPermissions('payments');
        $this->load->library("pagination");
        $user           = $this->input->post('user') ? $this->input->post('user') : null;
        $supplier       = $this->input->post('supplier') ? $this->input->post('supplier') : null;
        $customer       = $this->input->post('customer') ? $this->input->post('customer') : null;
        $biller         = $this->input->post('biller') ? $this->input->post('biller') : null;
        $payment_ref    = $this->input->post('payment_ref') ? $this->input->post('payment_ref') : null;
        $paid_by        = $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
        $sale_ref       = $this->input->post('sale_ref') ? $this->input->post('sale_ref') : null;
        $purchase_ref   = $this->input->post('purchase_ref') ? $this->input->post('purchase_ref') : null;
        $card           = $this->input->post('card') ? $this->input->post('card') : null;
        $cheque         = $this->input->post('cheque') ? $this->input->post('cheque') : null;
        $transaction_id = $this->input->post('tid') ? $this->input->post('tid') : null;
        $type           = $this->input->post('type') ? $this->input->post('type') : null;
        $start_date     = $this->input->post('start_date') ? $this->input->post('start_date') : null;
        $end_date       = $this->input->post('end_date') ? $this->input->post('end_date') : null;

        if ($start_date) {
            $start_date = $this->bpas->fld($start_date.' 00:00:00');
            $end_date   = $this->bpas->fld($end_date.' 23:59:59');
        }
        $start  = "";
        $end = "";
        $str ="";
        $warehouse_id ='';
        $payment_nums = $this->db->get("bpas_payments")
        ->num_rows();
        $config = array();
        $config['suffix'] = "?v=1".$str;
        $config["base_url"] = admin_url("account/tansfer_payment");
        $config["total_rows"] = $payment_nums;
        $config["ob_set"] = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        $config["per_page"] = $this->Settings->rows_per_page; 
        $config["uri_segment"] = 4;
        $config['full_tag_open'] = '<ul class="pagination pagination-sm">';
        $config['full_tag_close'] = '</ul>';
        $config['next_tag_open'] = '<li class="next">';
        $config['next_tag_close'] = '<li>';
        $config['prev_tag_open'] = '<li class="prev">';
        $config['prev_tag_close'] = '<li>';
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a><li>';
        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '<li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '<li>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';

        $this->pagination->initialize($config);
        $this->data["pagination"] = $this->pagination->create_links();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
   
       // $this->data['get_payments'] = $this->reports_model->get_payments($start,$end,$config["ob_set"],$config["per_page"],$warehouse_id);

        $this->db->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, 
            ".$this->db->dbprefix('payments').'.reference_no as payment_ref, 
            '.$this->db->dbprefix('sales').'.reference_no as sale_ref, 
            '.$this->db->dbprefix('purchases').".reference_no as purchase_ref, 
            paid_by, amount,
            {$this->db->dbprefix('payments')}.bank_account,
            transfer, 
            {$this->db->dbprefix('payments')}.type, 
            {$this->db->dbprefix('payments')}.id as id")
        ->from('payments')
        ->join('sales', 'payments.sale_id=sales.id', 'left')
        ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
        ->group_by('payments.id')
        ->order_by('payments.date desc')
        ->limit($config["per_page"],$config["ob_set"]);
        $this->db->where('payments.transfer', 0);
        //    $this->db->where('sales.return_sale_ref IS NULL');

        if ($user) {
            $this->db->where('payments.created_by', $user);
        }
        if ($card) {
            $this->db->like('payments.cc_no', $card, 'both');
        }
        if ($cheque) {
            $this->db->where('payments.cheque_no', $cheque);
        }
        if ($transaction_id) {
            $this->db->where('payments.transaction_id', $transaction_id);
        }
        if ($customer) {
            $this->db->where('sales.customer_id', $customer);
        }
        if ($supplier) {
            $this->db->where('purchases.supplier_id', $supplier);
        }
        if ($biller) {
            $this->db->where('sales.biller_id', $biller);
        }
        if ($customer) {
            $this->db->where('sales.customer_id', $customer);
        }
        if ($payment_ref) {
            $this->db->like('payments.reference_no', $payment_ref, 'both');
        }
        if ($paid_by) {
            $this->db->where('payments.paid_by', $paid_by);
        }
        if ($type) {
            $this->db->where('payments.type', $type);
        }
        if ($sale_ref) {
            $this->db->like('sales.reference_no', $sale_ref, 'both');
        }
        if ($purchase_ref) {
            $this->db->like('purchases.reference_no', $purchase_ref, 'both');
        }
        if ($start_date) {
            $this->db->where($this->db->dbprefix('payments') . '.date BETWEEN "'.$start_date.'" and "'.$end_date.'"');
        }

        $q = $this->db->get();
        $payment_array = $q->result();

        $output ="";
        $i=1;
        $total=0;
        foreach ($payment_array as  $payment) {
            $output .='<tr class="payment_link" id="'.$payment->id.'">';
            
                $output .='<td><input class="checkbox multi-select input-xs" value="'.$payment->id.'" type="checkbox" name="val[]"></td>';
                $output .='<td>'.$payment->date.'</td>';
                $output .='<td>'.$payment->payment_ref.'</td>';
                $output .='<td>'.$payment->sale_ref.'</td>';
                $output .='<td>'.$payment->purchase_ref.'</td>';
                $output .='<td>'.$payment->paid_by.'</td>';
                $output .='<td>'.$payment->amount.'</td>';
                $output .='<td>'.$payment->type.'</td>';
                if($this->Settings->accounting){
                    $output .='<td>'.$payment->bank_account.'</td>';
                     $output .= '<th><a href="'.admin_url().'account/tansfer/'.$payment->id.'" class="tip btn btn-success btn-xs" title="" data-toggle="modal" data-backdrop="static" data-target="#myModal" data-original-title="Edit">'.lang('transfer_account').'</a></th>';
                }
            $output .='</tr>';
            $i++;
            $total +=$payment->amount;
        }
        $output .='<tr class="active">';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td></td>';
            $output .='<td>'.$total.'</td>';
            $output .='<td></td>';
            if($this->Settings->accounting){
                $output .='<td></td>';
                $output .='<td></td>';
            }
        $output .='</tr>';

        $this->data['data'] = $output;
        //$this->data['showing'] = $this->reports_model->showing($payment_array,$config["per_page"]);

        $this->data['error']        = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users']        = $this->reports_model->getStaff();
        $this->data['billers']      = $this->site->getAllCompanies('biller');
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : false;
        $bc                         = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('account'), 'page' => lang('account')], ['link' => '#', 'page' => lang('tansfer_payment')]];
        $meta                       = ['page_title' => lang('tansfer_payment'), 'bc' => $bc];
        $this->page_construct('accounts/transfer_payment_to_bank', $meta, $this->data);
    }
    public function add_transfer(){
		$this->bpas->checkPermissions("add_tax");
		if($this->input->post('add_tax')){
			$this->form_validation->set_rules('type', $this->lang->line("type"), 'required');
			if ($this->form_validation->run() == true) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
				$biller_id = $this->input->post('biller');
				$type = $this->input->post('type');
				$from_date = $this->bpas->fsd(trim($this->input->post('from_date')));
				$to_date = $this->bpas->fsd(trim($this->input->post('to_date')));
				$t_total = 0;
				$t_vat = 0;
				$t_grand_total = 0;

				$prefix ='Tax';
				$year = date('y',strtotime($this->bpas->fsd($this->input->post('from_date'))));
				$prefix_year=$prefix.$year.'/';
				$total_row = $this->taxs_model->getInvoicesTaxCount($type,$prefix_year);
	
			

				$items = false;
				$data = false;
				$i = isset($_POST['val']) ? sizeof($_POST['val']) : 0;
				if($type=="sale"){
					$data_transactions = $this->taxs_model->getIndexSales($_POST['val']);
				}else if($type=="expense"){
					$data_transactions = $this->taxs_model->getIndexExpenses($_POST['val']);
				}else{
					$data_transactions = $this->taxs_model->getIndexPurchases($_POST['val']);
				}
				$n=1;
				for ($r = 0; $r < $i; $r++) {
					$transaction_id = $_POST['val'][$r];
					$exchange_rate = $_POST['exchange_rate'][$r];
					$tax_reference = $prefix_year.sprintf('%04s', ($total_row+$n));//$_POST['tax_reference'][$r];
					$data_transaction = $data_transactions[$transaction_id];
					if(isset($data_transaction)){
						$t_total += $data_transaction->total;
						$t_vat += $data_transaction->order_tax;
						$t_grand_total += $data_transaction->grand_total;
						$items[] = array(
							"transaction" 		=> $type,
							"transaction_id" 	=> $transaction_id,
							"exchange_rate" 	=> $exchange_rate,
							"name" 				=> $data_transaction->name,
							"reference_no" 		=> $data_transaction->reference_no,
							"tax_reference" 	=> $tax_reference,
							"date" 				=> $data_transaction->date,
							"company" 			=> $data_transaction->company,
							"vat_no" 			=> $data_transaction->vat_no,
							"total" 			=> $data_transaction->total,
							"order_tax" 		=> $data_transaction->order_tax,
							"grand_total" 		=> $data_transaction->grand_total,
							"note" 				=> $data_transaction->note,
							"quantity" 			=> $data_transaction->quantity
						);
					}
					$n++;
				}
				$data = array(
					'date' => $date,
					'biller_id' => $biller_id,
					'type' => $type,
					'from_date' => $from_date,
					'to_date' => $to_date,
					'total' => $t_total,
					'vat' => $t_vat,
					'grand_total' => $t_grand_total,
					'created_by' => $this->session->userdata('user_id'),
					'created_at' => date('Y-m-d H:i:s'),
				);
				if($data && $items && $this->taxs_model->addTax($data, $items)){
					$this->session->set_flashdata('message', $this->lang->line("tax_added"));
					admin_redirect("taxs");
				}else{
					$this->session->set_flashdata('error', $this->lang->line("data_required"));
					redirect($_SERVER['HTTP_REFERER']);
				}
			}
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['ExpenseAccounts'] = $this->accounts_model->getAllChartAccountIn('50,60,80');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('taxs'), 'page' => lang('tax')), array('link' => '#', 'page' => lang('add_transfer')));
			$meta = array('page_title' => lang('add_tax'), 'bc' => $bc);
            $this->page_construct('accounts/add_transfer', $meta, $this->data);
		}
	}
	public function getPaymentTransactions() {
		$biller_id = $this->input->get("biller") ? $this->input->get("biller") : false;
		$type = $this->input->get("type") ? $this->input->get("type") : false;
		$from_date = $this->input->get("from_date") ? $this->bpas->fld($this->input->get("from_date")) : date("Y-m-d H:i");
		$to_date = $this->input->get("to_date") ? $this->bpas->fld($this->input->get("to_date")) : date("Y-m-d H:i");
		$tax_id = $this->input->get("tax_id") ? $this->input->get("tax_id") : 0;
        $this->load->library('datatables');


		$this->datatables->select("
			{$this->db->dbprefix('payments')}.id as id,
			DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, 
        	".$this->db->dbprefix('payments').'.reference_no as payment_ref, 
        	'.$this->db->dbprefix('sales').".reference_no as sale_ref, 
        	paid_by, 
        	amount,
        	{$this->db->dbprefix('payments')}.bank_account,
        	transfer, 
        	{$this->db->dbprefix('payments')}.type, 
        	{$this->db->dbprefix('payments')}.id as id")
	    ->from('payments')
	    ->join('sales', 'payments.sale_id=sales.id', 'left')
	    ->group_by('payments.id')
	    ->order_by('payments.date desc');

	    if ($biller_id) {
	        $this->datatables->where('sales.biller_id', $biller_id);
	    }
	 
	    if($type == "pos"){
	        $this->datatables->where('sales.pos',1);
	    }else{
	    	$this->datatables->where('sales.pos',0);
	    }
	 
	    if ($from_date) {
	        $this->datatables->where($this->db->dbprefix('payments') . '.date BETWEEN "'.$from_date.'" and "'.$to_date.'"');
	    }
    	echo $this->datatables->generate();
    }
    public function chatAccountsuggestions()
    {
        $term = $this->input->get('term', true); 
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->accounts_model->getAllAccounts($sr);

        if ($rows) {
            foreach ($rows as $row) {              
                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row);
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    //------------------
    public function credit_note($biller_id = NULL)
    {
        $this->bpas->checkPermissions('return_sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error'); 
        $this->data['users']            = $this->site->getStaff();
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('sales'), 'page' => lang('sale')),  array('link' => '#', 'page' => lang('credit_note')));
        $meta = array('page_title' => lang('credit_note'), 'bc' => $bc);
        $this->page_construct('accounts/credit_note', $meta, $this->data);
    }
    public function getCreditNote($biller_id = false)
    {
        $this->bpas->checkPermissions('return_sales');

        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
            } else {
                $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
            }
        }
        $user_query     = $this->input->get('user') ? $this->input->get('user') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $reference_no   = $this->input->get('reference_no') ? $this->input->get('reference_no') : NULL;
        $saleman_by     = $this->input->get('saleman_by') ? $this->input->get('saleman_by') : null;
        $product_id     = $this->input->get('product_id') ? $this->input->get('product_id') : null;
        $warehouse_id   = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by   = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $a              = $this->input->get('a') ? $this->input->get('a') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }

        $detail_link = anchor('admin/account/view_credit_note/$1', '<i class="fa fa-money"></i> ' . lang('view_credit_note'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $edit_link            = anchor('admin/account/edit_credit_note/$1', '<i class="fa fa-edit"></i> ' . lang('edit_credit_note'));
 		$payments_link = anchor('admin/account/credit_note_payment/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $add_payment_link = anchor('admin/account/add_payment_credit_note/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="add_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        $delete_link = "<a href='#' class='po add_void' title='<b>" . lang("void_credit_note") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('account/void_credit_note/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('void_credit_note') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li class="hide">' . $payments_link . '</li>
                <li class="hide">' . $add_payment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables->select("{$this->db->dbprefix('credit_note')}.id as id,
            DATE_FORMAT(date, '%Y-%m-%d %T') as date,
            reference_no,
            (SELECT reference_no FROM {$this->db->dbprefix('sales')} WHERE id ={$this->db->dbprefix('credit_note')}.sale_id) as sale_reference,
            customer,
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by,

            {$this->db->dbprefix('credit_note')}.note as note,
            grand_total as grand_total,
            {$this->db->dbprefix('credit_note')}.sale_status,
            attachment,
            sale_id")
            ->from('credit_note')
            ->join('users','users.id=credit_note.created_by','left');


        if ($warehouse_id) {
            $this->datatables->where('credit_note.warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->datatables->where('credit_note.biller_id', $biller_id);
        } 
        if ($reference_no) {
            $this->datatables->where('credit_note.reference_no', $reference_no);
        }
        if ($customer) {
            $this->datatables->where('credit_note.customer_id', $customer);
        }
        if ($saleman_by) {
            $this->datatables->where('credit_note.saleman_by', $saleman_by);
        }
        if ($payment_status) {
            $get_status = explode('_', $payment_status);
            $this->datatables->where_in('credit_note.payment_status', $get_status);
        }
        if ($user_query) {
            $this->datatables->where('credit_note.created_by', $user_query);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('credit_note') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }

        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('credit_note.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $this->datatables->where_in('credit_note.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function add_credit_note($sale_id = null)
    {   
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_invoiceNo']) {
                $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('cre'));
            } else {
                $reference = $this->site->getReference('cre');
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $consignment_id       = $this->input->post('consignment_id');
            $project_id           = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id         = $this->input->post('warehouse');
            $customer_id          = $this->input->post('customer');
            $biller_id            = $this->input->post('biller');
            $total_items          = $this->input->post('total_items');
            $sale_status          = $this->input->post('sale_status');
            $payment_status       = $this->input->post('payment_status');
            $payment_term         = $this->input->post('payment_term')?$this->input->post('payment_term'):null;
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $surcharge          = $this->input->post('surcharge') ? $this->input->post('surcharge') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note               = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note         = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id           = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            $commission_product = 0;
            $text_items         = "";
            $total              = 0;
            $product_tax        = 0;
            $product_discount   = 0;
            $digital            = false;
            $stockmoves         = null;

            $i                  = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                $item_barcode       = isset($_POST['item_barcode'][$r]) ? $_POST['item_barcode'][$r] : null;
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = $_POST['product_weight'][$r];
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;

                $item_option_comment = isset($_POST['product_comment_option'][$r]) ? $_POST['product_comment_option'][$r] : null;
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $_POST['product_expiry'][$r] : null; 
                $saleman_item       = isset($_POST['saleman_item'][$r]) ? $_POST['saleman_item'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByID($item_id) : null;
                    $cost = $product_details->cost;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity);
                    }
                    $product_tax       += $pr_item_tax;
                    $subtotal           = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit               = $this->site->getUnitByID($item_unit);
                    $total_weight       = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');
                    $commission_item    = $this->site->getProductCommissionByID($item_id);
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    if ($commission_item && $saleman) {
                    	$commission_product += $commission_item->price * $item_quantity;
                    }
                    if ($unit->id != $product_details->unit) {
                        $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'option_comment_id' => $item_option_comment,
                        'cost'              => $cost,
                      //  'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'expiry'            => $item_expiry,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                       // 'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                     //   'warranty'          => $item_warranty,
                      //  'weight'            => $item_weight,
                      //  'total_weight'      => $total_weight,
                        'comment'           => $item_detail,
                        'saleman_by'        => $saleman_item,
                      //  'combo_product'     => json_encode($combo_products),
                      //  'commission'        => isset($commission_item->price) ? $commission_item->price * $item_quantity : 0,
                    ];

                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                    $products[] = ($product);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount + $this->bpas->formatDecimal($surcharge)), 4);
            $saleman_award_points = 0;
            $user  = $this->site->getUser($this->session->userdata('user_id'));
            $staff = $this->site->getUser($this->input->post('saleman_by'));

            

            //=======acounting=========//
 
                $accTrans[] = array(
                    'tran_type'     => 'CreditNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->credit_note,
                    'amount'        => $grand_total,
                    'narrative'     => 'Reversal Revenue',
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'customer_id'   => $customer_id,
                    'created_by'    => $this->session->userdata('user_id'),
                );
                $accTrans[] = array(
                    'tran_type'     => 'CreditNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => -$grand_total,
                    'narrative'     => 'Reversal AR'.$reference,
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'customer_id'   => $customer_id,
                    'created_by'    => $this->session->userdata('user_id'),
                );
            //============end accounting=======//
            $payment = false;
            /*
        	if($this->input->post('paid_by')){
        		 $payment = [
	                'date'            => $date,
	                'reference_no'    => $this->site->getReference('pay'),
	                'amount'          => $grand_total,
	                'paid_by'         => $this->input->post('paid_by'),
	                'cheque_no'       => $this->input->post('cheque_no'),
	                'cc_no'           => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
	                'cc_holder'       => $this->input->post('pcc_holder'),
	                'cc_month'        => $this->input->post('pcc_month'),
	                'cc_year'         => $this->input->post('pcc_year'),
	                'cc_type'         => $this->input->post('pcc_type'),
	                'created_by'      => $this->session->userdata('user_id'),
	                'type'            => 'sent',
	                'bank_account'    => $paid_by_account,
	            ];
        	}*/
            $data      = [
                'date'                 => $date,
                'project_id'           => $this->input->post('project'),
                'sale_id'              => $sale_id ? $sale_id : null,
                'reference_no'         => $reference,
                'customer_id'          => $customer_id,
                'customer'             => $customer,
                'biller_id'            => $biller_id,
                'biller'               => $biller,
                'warehouse_id'         => $warehouse_id,
                'note'                 => $note,
                'staff_note'           => $staff_note,
                'total'                => $total,
                'product_discount'     => $product_discount,
                'order_discount_id'    => $this->input->post('order_discount'),
                'order_discount'       => $order_discount,
                'total_discount'       => $total_discount,
                'product_tax'          => $product_tax,
                'order_tax_id'         => $this->input->post('order_tax'),
                'order_tax'            => $order_tax,
                'total_tax'            => $total_tax,
                'shipping'             => $this->bpas->formatDecimal($shipping),
                'grand_total'          => $grand_total,
                'total_items'          => $total_items,
                'sale_status'          => 'issued',
                'payment_status'       => $payment_status,
                'payment_term'         => $payment_term,
                'due_date'             => $due_date,
                'paid'                 => 0,
                'created_by'           => $this->session->userdata('user_id'),
                'hash'                 => hash('sha256', microtime() . mt_rand()),
                'saleman_by'           => $this->input->post('saleman_by'),
            ];
    
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }      
        }
        if ($this->form_validation->run() == true && $this->accounts_model->addCreditNote($data, $products,$payment,$accTrans)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('credit_note_added'));
            admin_redirect('account/credit_note');
        } else {
            if ($sale_id) {
                $getSaleslist        = $this->sales_model->getInvoiceByID($sale_id);
                $items               = $this->sales_model->getAllInvoiceItems($sale_id);
                $sale_items          = [];
                $q_id                = $sale_id;
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $b = false;
                    if (!$sale_id) {
                        if ($sale_items !== false) {
                            $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                            if ($key !== false) {
                                if ($item->unit_quantity > $sale_items[$key]->quantity) {
                                    $item->unit_quantity = $item->unit_quantity - $sale_items[$key]->quantity;
                                } else {
                                    $b = true;
                                }
                            } 
                        }
                        if ($b == true) {
                            continue;
                        }
                    }
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        $row->quantity = $pis->quantity_balance;
                    }
                    $row->id              = $item->product_id;
                    $row->code            = $item->product_code;
                    $row->name            = $item->product_name;
                    $row->type            = $item->product_type;
                    $row->qty             = $item->quantity;
                    $row->base_quantity   = $item->quantity;
                    $row->base_unit       = isset($row->unit) ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price = isset($row->price) ? $row->price : $item->unit_price;
                    $row->unit            = $item->product_unit_id;
                    $row->qty             = $item->unit_quantity;
                    $row->discount        = isset($item->discount)?$item->discount:'0';
                    $row->item_tax        = $item->item_tax > 0 ? $item->item_tax / $item->quantity : 0;
                    $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate        = isset($item->tax_rate_id)?$item->tax_rate_id:NULL;
                    $row->serial          = '';
                    $row->serial_no       = (isset($row->serial_no) ? $row->serial_no : '');
                    $row->option          = $item->option_id;
                    $row->expiry          = $item->expiry;
                    $row->details         = (isset($item->comment) ? $item->comment : '');
                    
                    $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    $row->product_option_comment = $this->sales_model->getProductOptionComment($row->id);
	                $row->comment_option = $item->option_comment_id;

                    $combo_items          = $row->type == 'combo' ? $this->sales_model->getProductComboItems($row->id, $item->warehouse_id) : false;
                    $units                = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                    $ri                   = $this->Settings->item_addition ? $row->id : $c;
                    $set_price            = $this->site->getUnitByProId($row->id);
                    $stock_items          = $this->site->getStockMovementByProductID($row->id, $item->warehouse_id, $row->option);
                    if ($sale_id) {
                        if (!empty($set_price)) {
                            foreach ($set_price as $key => $p) {
                                if ($p->unit_id == $row->unit) {
                                    $set_price[$key]->price = $row->real_unit_price;
                                }
                            }
                        }
                    }
                    $pr[$ri] = [
                        'id' => $ri, 'item_id' => $row->id, 'label'    => $row->name . ' (' . $row->code . ')' . ($row->expiry != null ? ' (' . $row->expiry . ')' : ''), 'row'=> $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'set_price' => $set_price, 'options' => $options, 'pitems' => $stock_items, 'expiry' => $row->expiry 
                    ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['quote']       = $getSaleslist;
                $this->data['inv']         = $getSaleslist;
                $this->data['quote_id']    = $q_id;
            }
            $this->data['projects']        = $this->site->getAllProject();
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['sale_id']         = $sale_id;
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['data']            = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $this->data['mbillers']        = $this->site->getAllCompaniesByBiller('biller', explode(',', $this->data['data']->multi_biller));
            $this->data['agencies']        = $this->site->getAllUsers();
            $this->data['warehouses']      = $this->site->getAllWarehouses();
            $this->data['tax_rates']       = $this->site->getAllTaxRates();
            $this->data['units']           = $this->site->getAllBaseUnits();
            $this->data['group_price']     = json_encode($this->site->getAllGroupPrice());
            $this->data['salemans']        = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['slnumber']        = $this->site->getReference('cre');
            $this->data['sales']           = $this->accounts_model->getRefSales();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_credit_note')]];
            $meta = ['page_title' => lang('add_credit_note'), 'bc' => $bc];
            $this->page_construct('accounts/add_credit_note', $meta, $this->data);
        }
    }
    public function edit_credit_note($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->accounts_model->getCreditNoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->saleman_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id             = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id           = $this->input->post('warehouse');
            $customer_id            = $this->input->post('customer');
            $biller_id              = $this->input->post('biller');
            $total_items            = $this->input->post('total_items');
            $sale_status            = $this->input->post('sale_status');
            $payment_status         = $this->input->post('payment_status');
            $payment_term           = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date           = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details       = $this->site->getCompanyByID($customer_id);
            $customer               = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details         = $this->site->getCompanyByID($biller_id);
            $biller                 = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note             = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product     = 0;
            $total                  = 0;
            $product_tax            = 0;
            $product_discount       = 0;
            $stockmoves             = null;
            $gst_data               = [];
            $total_cgst             = $total_sgst       = $total_igst       = 0;
            
            $i                      = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                // $item_barcode       = isset($_POST['item_barcode'][$r]) ? $_POST['item_barcode'][$r] : null;
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_option_comment = isset($_POST['product_comment_option'][$r]) ? $_POST['product_comment_option'][$r] : null;
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $_POST['product_expiry'][$r] : null; 
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $saleman_item       = isset($_POST['saleman_item'][$r]) ? $_POST['saleman_item'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByID($item_id) : null; 
                    $cost = $product_details ? $product_details->cost : 0;
                    if ($item_type == 'digital') {
                        $digital = true;
                    } 
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = ''; 
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    } 
                    $product_tax       += $pr_item_tax;
                    $subtotal           = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit               = $this->site->getUnitByID($item_unit);
                    $commission_item    = $this->site->getProductCommissionByID($item_id);
                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0;
                    $purchase_unit_cost = $product_details->cost;
                    if ($unit->id != $product_details->unit) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost,$unit);
                    } else {
                        $cost = $cost;
                    }
                 
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'option_comment_id' => $item_option_comment,
                        'cost'              => $cost,
                      //  'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'expiry'            => $item_expiry,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                        'warranty'          => $item_warranty,
                        'comment'           => $item_detail,
                        'saleman_by'        => $saleman_item,
                        // 'item_barcode '     =>  $item_barcode ,
                      //  'combo_product'     => json_encode($combo_products),
                       // 'commission'        => isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0,
                    ];
                    $products[] = ($product);
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);

            //=======acounting=========//
                    $accTrans[] = array(
                        'tran_no' 		=> $id,
                        'tran_type' 	=> 'CreditNote',
                        'tran_date' 	=> $date,
                        'reference_no' 	=> $reference,
                        'account_code' 	=> $this->accounting_setting->credit_note,
                        'amount' 		=> $grand_total,
                        'narrative' 	=> 'Reversal Revenue',
                        'description' 	=> $note,
                        'biller_id' 	=> $biller_id,
                        'project_id' 	=> $project_id,
                        'customer_id' 	=> $customer_id,
                        'created_by'  	=> $this->session->userdata('user_id')
                    );
                
           
                    $accTrans[] = array(
                        'tran_no' 		=> $id,
                        'tran_type' 	=> 'CreditNote',
                        'tran_date' 	=> $date,
                        'reference_no' 	=> $reference,
                        'account_code' 	=> $this->accounting_setting->default_receivable,
                        'amount' 		=> -$grand_total,
                        'narrative' 	=> 'Reversal AR',
                        'description' 	=> $note,
                        'biller_id' 	=> $biller_id,
                        'project_id' 	=> $project_id,
                        'customer_id' 	=> $customer_id,
                        'created_by'  	=> $this->session->userdata('user_id')
                    );
          
            
            //============end accounting=======//
            $data = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'staff_note'          => $staff_note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('order_discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'total_items'         => $total_items,
                'sale_status'         => 'issued',
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'updated_by'          => $this->session->userdata('user_id'),
                'saleman_by'          => $this->input->post('saleman_by'),
                'sale_id'             => $this->input->post('si_reference'),

                'updated_at'          => date('Y-m-d H:i:s'),
            ];
    
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->accounts_model->EditCreditNote($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('credit_note_updated'));
            admin_redirect('account/credit_note');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] 	= $this->accounts_model->getCreditNoteByID($id);
            $inv_items 			= $this->accounts_model->getAllCreditNoteItems($id);
            $c = rand(100000, 9999999);
            if($inv_items){

	            foreach ($inv_items as $item) {
	                $row = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
	                $cate_id = !empty($item->subcategory_id) ? $item->subcategory_id : $item->category_id;
	                if (!$row) {
	                    $row             = json_decode('{}');
	                    $row->tax_method = 0;
	                    $row->quantity   = 0;
	                } else {
	                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
	                }
	                $pis = $this->site->getStockMovement_ProductBalanceQuantity($item->product_id, $item->warehouse_id, $item->option_id);
	                if ($pis) {
	                    $row->quantity = $pis->quantity_balance;
	                }

	                $item->unit_quantity     = $item->unit_quantity;

	                $row->id              = $item->product_id;
	                $row->code            = $item->product_code;
	                $row->name            = $item->product_name;
	                $row->type            = $item->product_type;
	                $row->base_quantity   = $item->quantity;
	                $row->expiry          = $item->expiry;
	                $row->base_unit       = (!empty($row->unit) ? $row->unit : $item->product_unit_id);
	                $row->base_unit_price = (!empty($row->price) ? $row->price : $item->unit_price);
	                $row->unit            = $item->product_unit_id;
	                $row->qty             = $item->unit_quantity;
	                $row->quantity       += $item->quantity;
	                $row->discount        = $item->discount ? $item->discount : '0';
	                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
	                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
	                $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
	                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
	                $row->real_unit_price = $item->real_unit_price;
	                $row->tax_rate        = $item->tax_rate_id;
	                $row->serial          = '';
	                $row->serial_no       = $item->serial_no;
	                $row->max_serial      = $item->max_serial;
	                $row->warranty        = $item->warranty;
	                $row->option          = $item->option_id;
	                $row->addition_type   = $item->addition_type;
	                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);

	                $row->product_option_comment = $this->sales_model->getProductOptionComment($row->id);
	                $row->comment_option = $item->option_comment_id;


	                $row->details         = $item->comment;
	                $row->option_name     = $item->option_name;
	                $row->saleman_item    = $item->saleman_by;
	                $combo_items          = $row->type == 'combo' ? json_decode($item->combo_product) : false;
	                $categories           = false;
	                $categories           = $this->site->getCategoryByID($cate_id);
	                $fiber_type           = $this->sales_model->getFiberTypeById($row->id);
	                $categories->type_id  = isset($row->addition_type) ? $row->addition_type : null;
	                $fibers               = array('fiber' => $categories, 'type' => $fiber_type, );
	                $units                = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
	                $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
	                $ri                   = $this->Settings->item_addition ? $row->id : $c;
	                $set_price            = $this->site->getUnitByProId($row->id);
	                $pr[$ri] = [
	                    'id' => $c, 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' . ($row->expiry != null ?  ' (' . $row->expiry . ')' : ''), 
	                    'category' => (isset($row->category_id) ? $row->category_id : ""), 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 
	                    'units' => $units, 'options' => $options, 'fiber' => $fibers, 'expiry'=> $row->expiry, 'set_price' => $set_price,
	                ];
	                $c++;
	            }
	        }
            $this->data['count']        = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']     = $this->site->getAllProject();
            $this->data['inv_items']    = json_encode($pr);
            $this->data['id']           = $id;
            $this->data['agencies']     = $this->site->getAllUsers();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['units']        = $this->site->getAllBaseUnits();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['zones']        = $this->site->getAllZones();
            $this->data['salemans']     = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['sales']        = $this->sales_model->getRefSales('completed');

            $this->data['getsale'] 		= $this->sales_model->getInvoiceByID($inv->sale_id);


            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_credit_note')]];
            $meta = ['page_title' => lang('edit_credit_note'), 'bc' => $bc];
            $this->page_construct('accounts/edit_credit_note', $meta, $this->data);
        }
    }
    public function view_credit_note($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->accounts_model->getCreditNoteByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer'] 	= $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller'] 		= $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] 	= $this->site->getUser($inv->created_by);
        $this->data['saleman'] 		= $this->site->getUser($inv->saleman_by);
        $this->data['updated_by'] 	= $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] 	= $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] 			= $inv;
        $this->data['sale'] 		= $this->sales_model->getInvoiceByID($inv->sale_id);

      //  $this->data['sale_payments'] = $this->accounts_model->getCreditNotePayments($inv->sale_id);
        $this->data['rows']         = $this->accounts_model->getAllCreditNoteItems($id);
        if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
            $this->data['print'] = 0;
        }else{
            if($this->Settings->limit_print=='1' && $this->site->checkPrint('Sale Return',$inv->id)){
                $this->data['print'] = 1;
            }else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Sale Return',$inv->id)){
                $this->data['print'] = 2;
            }else{
                $this->data['print'] = 0;
            }
        }
        $this->load->view($this->theme . 'accounts/view_credit_note', $this->data);
    }
    public function void_credit_note($id)
	{
		$this->bpas->checkPermissions();
		if($id){
			$row = $this->accounts_model->getCreditNoteByID($id);
			$date 			= date('Y-m-d H:i:s');
			$biller_id      = $row->biller_id;
			$order_discount = $row->order_discount;
			$project_id     = $row->project_id;
			$customer_id    = $row->customer_id;
			$reference      = $row->reference_no;
			$grand_total    = $row->grand_total;
			$note 			= $row->note;
			$data = [];
            //=======acounting=========//
            if ($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type'     => 'CreditNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->credit_note,
                    'amount'        => -$grand_total,
                    'narrative'     => 'Void Revenue',
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'customer_id'   => $customer_id,
                    'created_by'    => $this->session->userdata('user_id'),
                );
                $accTrans[] = array(
                    'tran_type'     => 'CreditNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => $grand_total,
                    'narrative'     => 'Void AR'.$reference,
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'customer_id'   => $customer_id,
                    'created_by'    => $this->session->userdata('user_id'),
                );
                
            }
            //============end accounting=======//
        }
		if ($this->accounts_model->add_void_credit_note($id,$data,$accTrans)) {
			$this->bpas->send_json(['error' => 0, 'msg' => lang('credit_note_has_been_void')]);
			admin_redirect('account/credit_note');
		} else {
			$this->bpas->send_json(['error' => 0, 'msg' => lang('credit_note_has_been_void')]);
			admin_redirect('account/credit_note');
		}
	}
	public function credit_note_payment($id = null)
    {
        $this->bpas->checkPermissions("payments", true);
        $this->data['payments'] = $this->accounts_model->getCreditNotePayments($id);
        $this->data['inv'] = $this->accounts_model->getCreditNoteByID($id);
        $this->load->view($this->theme . 'accounts/payment_credit_note', $this->data);
    }
    public function add_payment_credit_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->bpas->checkPermissions('add',true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->accounts_model->getCreditNoteByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang("sale_already_paid"));
            $this->bpas->md();
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $customer_id = null;
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sales-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }
            $reference_no   = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay',$sale->biller_id);
            $amount_paid    = $this->input->post('amount-paid');
            $principal_paid = $this->input->post('principal-paid');
            $interest_paid  = $this->input->post('interest-paid');
            $amount_discount = $this->input->post('discount');
            $bank_name      ="";
            $account_name   ="";
            $account_number ="";
            $cheque_number  ="";
            $cheque_date    ="";
            $cash_account   = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            if($cash_account->type=="bank"){
                $bank_name = $cash_account->name;
                $account_name = $this->input->post('account_name');
                $account_number = $this->input->post('account_number');
            }else if($cash_account->type=="cheque"){
                $bank_name = $this->input->post('bank_name');
                $cheque_number = $this->input->post('cheque_number');
                $cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
            }
            $paying_from = $cash_account->account_code;
            
            $payment = array(
                'date'          	=> $date,
                'credit_note_id'  	=> $this->input->post('credit_note_id'),
                'reference_no' 		=> $reference_no,
                'amount'        	=> $amount_paid,
                'discount'      	=> $amount_discount,
                'paid_by'       	=> $this->input->post('paid_by'),
                'note' 				=> $this->input->post('note'),
                'created_by' 		=> $this->session->userdata('user_id'),
                'type' 				=> 'returned',
                'currencies' 		=> json_encode($currencies),
                'account_code' 		=> $paying_from,
                'bank_name' 		=> $bank_name,
                'account_name' 		=> $account_name,
                'account_number' 	=> $account_number,
                'cheque_number' 	=> $cheque_number,
                'cheque_date' 		=> $cheque_date,
            );
            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_date'     => $date,
                    'reference_no'  => $reference_no,
                    'account_code'  => -($paymentAcc->default_receivable  ? $paymentAcc->default_receivable : $this->accounting_setting->default_receivable),
                    'amount'        => ($amount_paid+$amount_discount),
                    'narrative'     => 'Sale Return Payment '.$sale->return_sale_ref,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $sale->biller_id,
                    'project_id'    => $sale->project_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $sale->customer_id,
                );

                $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no' => $reference_no,
                        'account_code'  => $paying_from,
                        'amount'        => $amount_paid,
                        'narrative'     => 'Sale Return Payment '.$sale->return_sale_ref,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale->customer_id,
                    );
                if($amount_discount>0){
                    $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $paymentAcc->default_sale_discount,
                        'amount'        => $amount_discount,
                        'narrative'     => 'Sale Return Payment Discount '.$sale->return_sale_ref,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale->customer_id,
                    );
                }
            }
            //=====end accountig=====//

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('add_payment_return')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->accounts_model->addCreditNotePayment($payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("payment_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && abs($sale->paid) == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->bpas->md();
            }
            $this->data['inv'] = $sale;
            $this->data['sale_payments'] = $this->accounts_model->getCreditNotePayments($sale->sale_id);
            $this->data['payment_term'] = $this->site->getPaymentTermsByID($sale->payment_term);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'accounts/add_payment_credit_note', $this->data);
        }
    }
    public function edit_payment_credit_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->site->getPaymentByID($id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->bpas->md();
        }
        // if($this->config->item("receive_payment") && $this->sales_model->getReceivePyamentByPaymentID($id)){
        //     $this->session->set_flashdata('error', lang('x_edit_payment'));
        //     $this->bpas->md();
        // }
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $sale = $this->accounts_model->getCreditNoteByID($this->input->post('credit_note_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                $amount = $this->input->post('amount-paid')-$payment->amount;
                if ( ! $this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sales-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }

            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }
            $payment_amount = $this->input->post('amount-paid');
            $principal_paid = $this->input->post('principal-paid');
            $interest_paid = $this->input->post('interest-paid');
            $discount_amount = $this->input->post('discount');
            
            $bank_name="";
            $account_name="";
            $account_number="";
            $cheque_number="";
            $cheque_date="";
            $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paying_from = $cash_account->account_code;
            if($cash_account->type=="bank"){
                $bank_name = $cash_account->name;
                $account_name = $this->input->post('account_name');
                $account_number = $this->input->post('account_number');
            }else if($cash_account->type=="cheque"){
                $bank_name = $this->input->post('bank_name');
                $cheque_number = $this->input->post('cheque_number');
                $cheque_date = $this->bpas->fsd($this->input->post('cheque_date'));
            }
            $data = array(
                'date' 				=> $date,
                'credit_note_id'  	=> $this->input->post('credit_note_id'),
                'reference_no' 		=> $this->input->post('reference_no'),
                'amount' 			=> $payment_amount,
                'discount' 			=> $discount_amount,
                'paid_by' 			=> $this->input->post('paid_by'),
                'cheque_no' 		=> $this->input->post('cheque_no'),
                'cc_no' 			=> $this->input->post('pcc_no'),
                'cc_holder' 		=> $this->input->post('pcc_holder'),
                'cc_month' 			=> $this->input->post('pcc_month'),
                'cc_year' 			=> $this->input->post('pcc_year'),
                'cc_type' 			=> $this->input->post('pcc_type'),
                'note' 				=> $this->input->post('note'),
                'updated_by' 		=> $this->session->userdata('user_id'),
                'updated_at' 		=> date('Y-m-d H:i:s'),
                'currencies' 		=> json_encode($currencies),
                'account_code' 		=> $paying_from,
                'bank_name' 		=> $bank_name,
                'account_name' 		=> $account_name,
                'account_number' 	=> $account_number,
                'cheque_number' 	=> $cheque_number,
                'cheque_date' 		=> $cheque_date,
            );
            //=====accountig=====//
                if($this->Settings->module_account == 1){
                    $paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
                    $accTranPayments[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('reference_no'),
                            'account_code' => ($paymentAcc->default_receivable  ? $paymentAcc->default_receivable : $this->accounting_setting->default_receivable),
                            'amount' => ($payment_amount+$discount_amount),
                            'narrative' => 'Sale Return Payment '.$sale->return_sale_ref,
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'created_by' => $this->session->userdata('user_id'),
                            'customer_id' => $sale->customer_id,
                        );
                    $accTranPayments[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('reference_no'),
                            'account_code' => $paying_from,
                            'amount' => -$payment_amount,
                            'narrative' => 'Sale Return Payment '.$sale->return_sale_ref,
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'created_by' => $this->session->userdata('user_id'),
                            'customer_id' => $sale->customer_id,
                        );
                    if($this->input->post('discount') != 0){
                        $accTranPayments[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('reference_no'),
                            'account_code' => $paymentAcc->default_sale_discount,
                            'amount' => -$discount_amount,
                            'narrative' => 'Sale Return Payment Discount '.$sale->return_sale_ref,
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'created_by' => $this->session->userdata('user_id'),
                            'customer_id' => $sale->customer_id,
                        );
                    }
                }
            //=====end accountig=====//

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);

        } elseif ($this->input->post('edit_payment_return')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }


        if ($this->form_validation->run() == true && $this->accounts_model->EditCreditNotPayment($id, $data, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
             redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $sale = $this->accounts_model->getCreditNoteByID($payment->credit_note_id);
            $this->data['inv'] = $sale;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
            $this->data['sale_payments'] = $this->accounts_model->getCreditNotePayments($sale->id);
            $this->data['payment_term'] = $this->site->getPaymentTermsByID($sale->payment_term);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'accounts/edit_payment_credit_note', $this->data);
        }
    }
    public function delete_payment_credit_note($id = null)
    {
        $this->bpas->checkPermissions('delete');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->accounts_model->deleteCreditNotePayment($id)) {
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function sale_credit_note($sale_id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['credit_note']      = $this->accounts_model->getCreditNoteBySaleID($sale_id);
        $this->load->view($this->theme . 'accounts/sale_credit_note', $this->data);
    }
    public function purchase_crebit_note($sale_id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['credit_note']      = $this->accounts_model->getDebitNoteByPurchaseID($sale_id);
        $this->load->view($this->theme . 'accounts/purchase_debit_note', $this->data);
    }
	public function debit_note($biller_id = null)
    {
        $this->bpas->checkPermissions();

        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || empty($count)) {
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['biller_id'] = $biller_id;
            $this->data['biller']    = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['billers']   = $this->site->getAllCompanies('biller');
            } else {
                $this->data['billers']   = null;
            }
            $this->data['count_billers'] = $count;
            $this->data['user_biller']   = (isset($count) && count($count) == 1) ? $this->site->getCompanyByID($this->session->userdata('biller_id')) : null;
            $this->data['biller_id']     = $biller_id;
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        }

        $this->data['warehouse_id'] = null;
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('debit_note')]];
        $meta = ['page_title' => lang('debit_note'), 'bc' => $bc];
        $this->page_construct('accounts/debit_note', $meta, $this->data);
    }
    public function getDebitNote($biller_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
            } else {
                $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
            }
        }
        $a                  = $this->input->get('a') ? $this->input->get('a') : null;
        $detail_link        = anchor('admin/account/modal_view_debit_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_debit_note'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $payments_link      = anchor('admin/account/debit_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link   = anchor('admin/account/add_debit_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link         = anchor('admin/account/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/account/edit_debit_note/$1', '<i class="fa fa-edit"></i> ' . lang('edit_debit_note'));
        $pdf_link           = anchor('admin/account/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link        = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_debit_note') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('account/void_debit_note/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('void_debit_note') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li> 
            <li class="hide">' . $payments_link . '</li> 
            <li class="hide">' . $add_payment_link . '</li>
            <li class="edit">' . $edit_link . '</li>
            <li class="delete">' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select("debit_note.id, 
                DATE_FORMAT({$this->db->dbprefix('debit_note')}.date, '%Y-%m-%d %T') as date, 
                {$this->db->dbprefix('debit_note')}.reference_no,
                (SELECT reference_no FROM {$this->db->dbprefix('purchases')} WHERE id ={$this->db->dbprefix('debit_note')}.purchase_id) as purchase_reference,
                {$this->db->dbprefix('debit_note')}.supplier, 
                abs({$this->db->dbprefix('debit_note')}.grand_total) as grand_total, 
                {$this->db->dbprefix('debit_note')}.note as note,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by,
                {$this->db->dbprefix('debit_note')}.status, 
                {$this->db->dbprefix('debit_note')}.attachment");
        
        $this->datatables->from('debit_note');
        $this->datatables->join('projects', 'debit_note.project_id = projects.project_id', 'left');
        $this->datatables->join('users','users.id=debit_note.created_by','left');
        if ($biller_id) {
            $this->datatables->where_in('debit_note.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_purchases.created_by, '" . $this->session->userdata('user_id') . "')");
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }

        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;
            if (count($alert_ids) > 1) {
                $this->datatables->where_in('debit_note.id', $alert_ids);
            } else {
                $this->datatables->where('debit_note.id', $alert_id);
            }
        }
        $this->datatables->add_column("Actions", $action, "debit_note.id");
        echo $this->datatables->generate();
    }
    public function modal_view_debit_note($purchase_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->accounts_model->getDebitNoteByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['rows']            = $this->accounts_model->getAllDebitNoteItems($purchase_id);
        $this->data['supplier']        = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse']       = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']             = $inv;
        $this->data['purchase']      = $this->accounts_model->getDebitNoteByID($inv->purchase_id);
        $this->data['currencys']       = $this->site->getAllCurrencies();
        $this->data['payments']        = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['updated_by']      = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);

        $this->load->view($this->theme . 'accounts/modal_view_debit_note', $this->data);
    }
    public function add_debit_note($purchase_id = null)
    {
        $this->bpas->checkPermissions();
        $this->session->unset_userdata('csrf_token');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $order_referent = $this->accounts_model->getDebitNoteByID($purchase_id);

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('deb');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $biller_id        = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term') ? $this->input->post('payment_term') : null;
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date     = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date     = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $qtycount         = 0;
            
            $i                = sizeof($_POST['product']);
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]) ;
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_description   = $_POST['description'][$r] ? $_POST['description'][$r] : null;
                $item_weight        = $_POST['weight'][$r];

                $item_addition_type = '';
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    // $product_details = $this->purchases_model->getProductByCode($item_code);
                    $product_details = $this->purchases_model->getProductByID($item_id);
                    $item_type       = $product_details->type;
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $product  = [
                        'product_id'        => $product_details->id,
                        'product_type'      => $product_details->type,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $status == 'pending' ? $item_quantity : 0,
                        'quantity_received' => $status == 'received' ? $item_quantity : 0,
                        'weight'            => $item_weight,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $status,
                        'supplier_part_no'  => $supplier_part_no,
                        'addition_type'     => $item_addition_type,
                        'description'       => $item_description,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $product['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $product['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }

                    $products[] = ($product);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal((($total + $total_tax + $this->bpas->formatDecimal($shipping)) - $order_discount), 4);
            //======= Add Acounting for total purchase=========//   
            if ($this->Settings->module_account == 1) {

                $accTrans[] = array(
                    'tran_type'     => 'DebitNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->debit_note,
                    'amount'        => -$grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->debit_note),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                $accTrans[] = array(
                    'tran_type'     => 'DebitNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
            
            }   
            //==================end accounting===========//
            $payment = false;
        	/*if($this->input->post('paid_by')){
        		 $payment = [
	                'date'            => $date,
	                'reference_no'    => $this->site->getReference('pay'),
	                'amount'          => $grand_total,
	                'paid_by'         => $this->input->post('paid_by'),
	                'cheque_no'       => $this->input->post('cheque_no'),
	                'cc_no'           => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
	                'cc_holder'       => $this->input->post('pcc_holder'),
	                'cc_month'        => $this->input->post('pcc_month'),
	                'cc_year'         => $this->input->post('pcc_year'),
	                'cc_type'         => $this->input->post('pcc_type'),
	                'created_by'      => $this->session->userdata('user_id'),
	                'type'            => 'sent',
	                'bank_account'    => $paid_by_account,
	            ];
        	}*/

            $data = [
                'purchase_id'       => !empty($purchase_id) ? $purchase_id : null,
                'biller_id'         => $biller_id,
                'project_id'        => $this->input->post('project'),
                'reference_no'      => $reference,
                'order_ref'         => !empty($order_referent->reference_no)? $order_referent->reference_no :'',
                'date'              => $date,
                'supplier_id'       => $supplier_id,
                'supplier'          => $supplier,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->bpas->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'status'            => 'issued',
                'created_by'        => $this->session->userdata('user_id'),
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
            ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->accounts_model->addDebitNote($data, $products, $accTrans, 0)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('debit_note_added'));
            admin_redirect('account/debit_note');
        } else {
            if ($purchase_id) {
                $this->data['quote'] = $this->purchases_model->getPurchaseByID($purchase_id);

                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_model->getAllPurchaseItems($purchase_id);

                $row_item = 0;
                $maxQtyInRow = 4;
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    $cate_id = $row->subcategory_id ? $row->subcategory_id:$row->category_id;
                    if ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }
                        $row->id             = $item->product_id;
                        $row->code           = $item->product_code;
                        $row->name           = $item->product_name;
                        $row->base_quantity  = $item->quantity;
                        $row->base_unit      = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit           = $item->product_unit_id;
                        $row->qty            = $item->unit_quantity;
                        $row->order_qty      = $item->unit_quantity;
                        $row->option         = $item->option_id;
                        $row->discount       = $item->discount ? $item->discount : '0';
                        $supplier_cost       = 0;
                        $row->cost           = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                        $row->real_unit_cost = $item->real_unit_cost;
                        $row->tax_rate       = $item->tax_rate_id;
                        $row->expiry         = '';
                        $row->total_purchase_qty = $item->quantity_balance;
                        $row->description    = $item->description ? $item->description :'';
                        $options             = $this->purchases_model->getProductOptions($row->id);
                        $units               = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate            = $this->site->getTaxRateByID($row->tax_rate);
                        $categories          = $this->site->getCategoryByID($cate_id);
                        $ri                  = $this->Settings->item_addition ? $row->id : $c;
                        $set_price           = $this->site->getUnitByProId($row->id);
                        $pr[$ri]   = array(
                            'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories
                        );
                        $c++;
                    }
                }
                $this->data['purchase_items'] = json_encode($pr);
                $this->data['orderid']  = $purchase_id;
                $this->data['purchase'] = $this->data['quote'];
            }

            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if (!$purchase_id){
                $this->data['purchase_id'] = "";
            }
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber']   = $this->site->getReference('rep');
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['purchases'] = $this->accounts_model->getRefPurchases();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase')]];
            $meta               = ['page_title' => lang('add_debit_note'), 'bc' => $bc];
            $this->page_construct('accounts/add_debit_note', $meta, $this->data);
        }
    }
    public function edit_debit_note($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->accounts_model->getDebitNoteByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('project', $this->lang->line('project'), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $warehouse_id     = $this->input->post('warehouse');
            $supplier_id      = $this->input->post('supplier');
            $status           = $this->input->post('status');
            $biller_id        = $this->input->post('biller') ? $this->input->post('biller') : $this->Settings->default_biller;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier         = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term     = $this->input->post('payment_term') ? $this->input->post('payment_term') : null;
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            } else {
                $due_date = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $partial          = false;
            $qtycount         = 0;
            $i                = sizeof($_POST['product']);
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_code          = $_POST['product'][$r];
                $item_net_cost      = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost     = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received  = $_POST['received_base_quantity'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r]) && $_POST['expiry'][$r] != 'false' && $_POST['expiry'][$r] != 'undefined' && $_POST['expiry'][$r] != 'null' && $_POST['expiry'][$r] != 'NULL' && $_POST['expiry'][$r] != '00/00/0000' && $_POST['expiry'][$r] != '' ? $this->bpas->fsd($_POST['expiry'][$r]) : null; 
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $supplier_part_no   = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_description = $_POST['description'][$r] ? $_POST['description'][$r]:null;
                $item_addition_type = '';
                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang('received_more_than_ordered'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty       = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost        = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax    = 0;
                    $tax         = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);  
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $item     = [
                        'product_id'        => $product_details->id,
                        'product_type'      => $product_details->type,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => ($status == 'partial' || $status == 'received') ? $balance_qty : 0,
                        'quantity_received' => ($status == 'partial' || $status == 'received') ? $quantity_received : 0,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'supplier_part_no'  => $supplier_part_no,
                        'date'              => date('Y-m-d', strtotime($date)),
                        'addition_type'     => $item_addition_type,
                        'description'       => $item_description,
                    ];
                    if ($unit->id != $product_details->unit) {
                        $item['base_unit_cost'] = $this->site->convertToBase($unit, ($item_net_cost + $item_tax));
                    } else {
                        $item['base_unit_cost'] = ($item_net_cost + $item_tax);
                    }
                    $items[] = ($item + $gst_data);
                    $total += $item_net_cost * $item_unit_quantity;
                }
            }
            if (empty($items)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                foreach ($items as $item) {
                    $item['status'] = ($status == 'partial' || $status == 'received') ? 'received' : $status;
                    $products[]     = $item;
                }
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'),($total + $product_tax - $order_discount));
            $total_tax      = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            
            if ($this->Settings->module_account == 1) {
           		 $accTrans[] = array(
           		 	'tran_no'       => $id,
                    'tran_type'     => 'DebitNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->debit_note,
                    'amount'        => -$grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->debit_note),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                $accTrans[] = array(
                	'tran_no'       => $id,
                    'tran_type'     => 'DebitNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );   
            }   
            $data = [
                'date'                => $date,
                'biller_id'           => $biller_id,
                'reference_no'        => $reference,
                'project_id'          => $this->input->post('project'),
                'purchase_id'         => $this->input->post('pu_reference'),
                'supplier_id'         => $supplier_id,
                'supplier'            => $supplier,
                'warehouse_id'        => $warehouse_id,
                'note'                => $note,
                'total'               => $total,
                'product_discount'    => $product_discount,
                'order_discount_id'   => $this->input->post('discount'),
                'order_discount'      => $order_discount,
                'total_discount'      => $total_discount,
                'product_tax'         => $product_tax,
                'order_tax_id'        => $this->input->post('order_tax'),
                'order_tax'           => $order_tax,
                'total_tax'           => $total_tax,
                'shipping'            => $this->bpas->formatDecimal($shipping),
                'grand_total'         => $grand_total,
                'status'              => $status,
                'updated_by'          => $this->session->userdata('user_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'payment_term'        => $payment_term,
                'due_date'            => $due_date, 
            ];
            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->accounts_model->updateDebitNote($id, $data, $products,$accTrans)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('debit_note_updated'));
            admin_redirect('account/debit_note');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $inv;
            $inv_items = $this->accounts_model->getAllDebitNoteItems($id);
            $pr = false;
            if($inv_items){
	            $c = rand(100000, 9999999);
	            foreach ($inv_items as $item) {
	                $row                     = $this->site->getProductByID($item->product_id);
	                $cate_id                 = $row->subcategory_id?$row->subcategory_id:$row->category_id;
	                $row->expiry             = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
	                $row->base_quantity      = $item->quantity;
	                $row->base_unit          = $row->unit ? $row->unit : $item->product_unit_id;
	                $row->base_unit_cost     = $item->unit_cost;
	                $row->weight             = $item->weight;
	                $row->unit               = $item->product_unit_id;
	                $row->qty                = $item->unit_quantity;
	                $row->oqty               = $item->quantity;
	                $row->supplier_part_no   = $item->supplier_part_no;
	                $row->received           = $item->quantity_received ? $item->quantity_received : $item->quantity;
	                $row->quantity_balance   = $item->quantity_balance + ($item->quantity - $row->received);
	                $row->discount           = $item->discount ? $item->discount : '0';
	                $options                 = $this->purchases_model->getProductOptions($row->id);
	                $row->option             = $item->option_id;
	                $row->real_unit_cost     = $item->real_unit_cost;
	                $row->cost               = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
	                $row->tax_rate           = $item->tax_rate_id;
	                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
	                $row->addition_type      = $item->addition_type;
	                $row->total_purchase_qty = $item->quantity_balance + $row->base_quantity;
	                $row->description    = $item->description;
	                $units                   = $this->site->getUnitsByBUID($row->base_unit);
	                $tax_rate                = $this->site->getTaxRateByID($row->tax_rate);
	                $categories              = $this->site->getCategoryByID($cate_id);
	                $ri                      = $this->Settings->item_addition ? $row->id : $c;
	                $set_price = $this->site->getUnitByProId($row->id);
	                $pr[$ri] = [
	                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
	                    'row' => $row, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $categories, ];
	                $c++;
	            }
	        }
            $this->data['inv_items']        = json_encode($pr);
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['id']               = $id;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['suppliers']        = $this->site->getAllCompanies('supplier');
            $this->data['purchase']         = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories']       = $this->site->getAllCategories();
            $this->data['tax_rates']        = $this->site->getAllTaxRates();
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['projects']         = $this->site->getAllProject();
            $this->data['purchases'] = $this->accounts_model->getRefPurchases();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc                 = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('account'), 'page' => lang('accounting')], ['link' => '#', 'page' => lang('edit_edit_debit_note')]];
            $meta               = ['page_title' => lang('edit_debit_note'), 'bc' => $bc];
            $this->page_construct('accounts/edit_debit_note', $meta, $this->data);
        }
    }
    public function void_debit_note($id)
	{
		$this->bpas->checkPermissions();
		if($id){
			$row = $this->accounts_model->getDebitNoteByID($id);
			$date 			= date('Y-m-d H:i:s');
			$biller_id      = $row->biller_id;
			$order_discount = $row->order_discount;
			$project_id     = $row->project_id;
			$supplier_id    = $row->supplier_id;
			$reference      = $row->reference_no;
			$grand_total    = $row->grand_total;
			$note 			= $row->note;
			$data = [];
            //=======acounting=========//
            if ($this->Settings->module_account == 1) {
                $accTrans[] = array(
                    'tran_type'     => 'DebitNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->debit_note,
                    'amount'        => $grand_total,
                    'narrative'     => 'Void '.$this->site->getAccountName($this->accounting_setting->debit_note),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                $accTrans[] = array(
                    'tran_type'     => 'DebitNote',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => -$grand_total,
                    'narrative'     => 'Void '.$this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $note,
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'supplier_id'   => $supplier_id,
                    'created_by'    => $this->session->userdata('user_id')
                );
                
            }
            //============end accounting=======//
        }
		if ($this->accounts_model->add_void_debit_note($id,$data,$accTrans)) {
			$this->bpas->send_json(['error' => 0, 'msg' => lang('debit_note_has_been_void')]);
			admin_redirect('account/debit_note');
		} else {
			$this->bpas->send_json(['error' => 0, 'msg' => lang('debit_note_has_been_void')]);
			admin_redirect('account/debit_note');
		}
	}
    public function debit_payments($id = null,$ex_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['inv'] = $this->accounts_model->getDebitNoteByID($id);
        $this->load->view($this->theme . 'purchases/debit_payments', $this->data); 
    }
    public function add_debit_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->accounts_model->getDebitNoteByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang('purchase_already_paid'));
            $this->bpas->md();
        }
        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;

            if ($this->input->post('paid_by') == 'deposit') {
                //$customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($purchase->supplier_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $reference_no = $this->site->CheckedPaymentReference($this->input->post('reference_no'), $this->site->getReference('ppay'));
            $currencies = array();  
            $camounts = $this->input->post("c_amount");         
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }

            $payment = [
                'date'         => $date,
                'purchase_id'  => $this->input->post('purchase_id'),
                'reference_no' => $reference_no,
                'amount'       => -$this->input->post('amount-paid'),
                'discount'     => -$this->input->post('discount'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->bpas->clear_tags($this->input->post('note')),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'sent',
                'bank_account' => $paid_by_account,
                'currencies'   => json_encode($currencies),
            ];

            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $biller_id = $purchase->biller_id ? $purchase->biller_id: $this->Settings->default_biller;
                $narrative = $this->site->getAccountName($this->accounting_setting->default_payable);

                $payment_from_account = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;


                $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_payable,
                        'amount'        => ($this->input->post('amount-paid')+$this->input->post('discount')) * (-1),
                        'narrative'     => $narrative,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $purchase->project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                    );
                $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $payment_from_account,
                        'amount'        => $this->input->post('amount-paid'),
                        'narrative'     => $this->site->getAccountName($payment_from_account),
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $purchase->project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($payment_from_account)
                    );
            }
            //=====end accountig=====//

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addDebitPayment($payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_added'));
            admin_redirect('account/debit_note'); 
        } else {
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']         = $purchase;
            $this->data['payment_ref'] = $this->site->getReference('ppay');
            $this->data['modal_js']    = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'accounts/add_debit_payment', $this->data);
        }
    }
    public function edit_debit_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase_id = $this->input->post('purchase_id');
        $purchase = $this->accounts_model->getDebitNoteByID($purchase_id);
        
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $paid_by = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = $paid_by->account_code;

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $currencies = array();  
            $camounts = $this->input->post("c_amount");         
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                            "amount" => $camounts[$key],
                            "currency" => $currency[$key],
                            "rate" => $rate[$key],
                        );
                }
            }
            $payment = [
                'date'         => $date,
                'purchase_id'  => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => -$this->input->post('amount-paid'),
                'discount'     => -$this->input->post('discount'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->bpas->clear_tags($this->input->post('note')),
                'bank_account' => $paid_by_account,
                'currencies'   => json_encode($currencies),

            ];
            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $biller_id = $purchase->biller_id ? $purchase->biller_id: $this->Settings->default_biller;

                $payment_from_account = isset($paid_by_account) ? $paid_by_account : $this->accounting_setting->default_cash ;
                
                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $this->input->post('reference_no')?$this->input->post('reference_no'):$this->site->getReference('ppay'),
                    'account_code'  => $this->accounting_setting->default_payable,
                    'amount'        => abs(($this->input->post('amount-paid') + $this->input->post('discount'))),
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_payable),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $biller_id,
                    'project_id'    => $purchase->project_id,
                    'supplier_id'   => $purchase->supplier_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                );
                $accTranPayments[] = array(
                    'tran_type'     => 'Payment',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $this->input->post('reference_no')?$this->input->post('reference_no'):$this->site->getReference('ppay'),
                    'account_code'  => $payment_from_account,
                    'amount'        => $this->input->post('amount-paid'),
                    'narrative'     => $this->site->getAccountName($payment_from_account),
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $biller_id,
                    'project_id'    => $purchase->project_id,
                    'supplier_id'   => $purchase->supplier_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->input->post('bank_account'))
                );
                if($this->input->post('discount') != 0){
                    $accTranPayments[] = array(
                        'tran_type'     => 'Payment',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $this->accounting_setting->default_purchase_discount,
                        'amount'        => $this->input->post('discount'),
                        'narrative'     => 'Purchase Payment Discount '.$reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'supplier_id'   => $purchase->supplier_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_purchase_discount)
                    );
                }
            }
            //=====end accountig=====//
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->bpas->print_arrays($payment);
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && 
            $this->purchases_model->updatePayment($id, $payment,$accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('account/debit_note'); 
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment']  = $this->purchases_model->getPaymentByID($id);
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'accounts/edit_debit_payment', $this->data);
        }
    }
}	
