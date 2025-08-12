<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Loans extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
		if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->admin_load('loans', $this->Settings->user_language);
        $this->load->library('form_validation');
		$this->load->admin_model('loans_model');
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
		$this->loans_model->synLoanPenalty();
		$this->load->admin_model('accounts_model');
    }
	
	public function index($biller_id = NULL, $currency_code = NULL)
	{
		$this->bpas->checkPermissions();
		if($biller_id == 0){
			$biller_id = null;
		}
		// if($currency_code == 0){
		// 	$currency_code = null;
		// }
		// var_dump($currency_code);
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
		$this->data['currencies'] = $this->site->getAllCurrencies();
		$this->data['currency'] = $currency_code ? $this->site->getCurrencyByCode($currency_code) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loan')), array('link' => '#', 'page' => lang('loans')));
		$meta = array('page_title' => lang('loans'), 'bc' => $bc);
        $this->page_construct('loans/index', $meta, $this->data);
	}
	
	public function getLoans($biller_id = NULL, $currency_code = NULL)
    {

	    $this->bpas->checkPermissions("index");
        $this->load->library('datatables');
	    $view_loan_link = anchor('admin/loans/view/$1', '<i class="fa fa-file-text-o"></i> ' .lang('loan_details'),' class="view-loan"');
	    $view_gps_link = anchor('admin/loans/view/$1#gps', '<i class="fa fa-map-marker"></i> ' .lang('gps'),' class="gps"');
		$add_schedule_link = '';
		if($this->Admin || $this->Owner || $this->GP['loans-edit']){
			$add_schedule_link = anchor('admin/loans/add_schedule/$1', '<i class="fa fa-plus-circle"></i> ' .lang('add_schedule'),' class="add-schedule"');
	    }
		$view_transaction_link = '';
	    if($this->Admin || $this->Owner || $this->GP['loans-payments']){
			$view_transaction_link = anchor('admin/loans/view/$1#transactions', '<i class="fa fa-file-text-o"></i> ' .lang('transactions'),' class="transactions"');
	    }
		$view_collateral_link = '';
	    if($this->Admin || $this->Owner || $this->GP['loans-collaterals']){
			$view_collateral_link = anchor('admin/loans/view/$1#collaterals', '<i class="fa fa-file-text-o"></i> ' .lang('collaterals'),' class="collaterals"');
	    }
		$view_guarantor_link = '';
	    if($this->Admin || $this->Owner || $this->GP['loans-guarantors']){
			$view_guarantor_link = anchor('admin/loans/view/$1#guarantors', '<i class="fa fa-file-text-o"></i> ' .lang('guarantors'),' class="guarantors"');
	    }
		$edit_schedule_link = '';
	    if($this->Admin || $this->Owner || $this->GP['loans-schedule-edit']){
			$edit_schedule_link = anchor('admin/loans/edit_schedule/$1', '<i class="fa fa-edit"></i> ' .lang('edit_schedule'),' class="edit-schedule"');
	    }
		
		$suspend_loan_link = "<a href='#' class='po suspend-loan' title='<b>" . lang("suspend_loan") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('loans/suspend_loan/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-edit\"></i> "
			. lang('suspend_loan') . "</a>";
		
		$payoff_loan_link = "<a href='#' class='po payoff-loan' title='<b>" . lang("payoff_loan") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('loans/payoff_loan/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-edit\"></i> "
			. lang('payoff_loan') . "</a>";
			
	    $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_loan_link.'</li>
						<li>'.$add_schedule_link.'</li>
						<li>'.$edit_schedule_link.'</li>
						<li>'.$view_transaction_link.'</li>
						<li>'.$view_collateral_link.'</li>
						<li>'.$view_guarantor_link.'</li>
						<li>'.$suspend_loan_link.'</li>
						<li>'.$payoff_loan_link.'</li>
						<li>'.$view_gps_link.'</li></ul>
				</div></div>';
				
		$LA = '(SELECT loan_id, IFNULL(SUM(amount),0) AS principal_paid, IFNULL(SUM(interest_paid),0) AS interest_paid FROM '.$this->db->dbprefix('payments').' WHERE type = "received" GROUP BY loan_id) as bpas_payments';
        $this->datatables->select("
        	loans.id as id,
        	loans.reference_no as loan_reference_no,
        	loan_applications.reference_no as application_no,
        	loan_borrowers.code as code,
        	loans.borrower as borrower,
        	CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
        	loan_products.name as loan_product,
        	loans.principal_amount as principal_amount,
        	IFNULL(bpas_loans.principal_amount,0) - ROUND(IFNULL(bpas_payments.principal_paid,0)) as outstanding_balance,
        	loans.currency as currency,
        	loans.disbursed_at as disbursed_at,
        	DATE_ADD(payment_date, INTERVAL (bpas_loans.term*bpas_loans.frequency) DAY) as maturity_date,
        	CONCAT(bpas_loan_officers.last_name,' ',bpas_loan_officers.first_name) as loan_officer,
        	CONCAT(bpas_tellers.last_name,' ',bpas_tellers.first_name) as teller,
        	loans.status as status
        	")
            ->from("loans")
			->join("loan_applications","loan_applications.id=application_id","left")
			->join("loan_borrowers","loan_borrowers.id=loans.borrower_id","left")
			->join("loan_products","loan_products.id=loans.loan_product_id","left")
			->join("users as bpas_loan_officers","bpas_loan_officers.id=loans.loan_officer_id","left")
			->join("users as bpas_tellers","bpas_tellers.id=loans.teller_id","left")
			->join($LA, 'bpas_payments.loan_id=loans.id', 'left');
			if ($biller_id) {
				$this->datatables->where('loans.biller_id', $biller_id);
			}
			if ($currency_code) {
				$this->datatables->where('loans.currency', $currency_code);
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('loans.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('loans.biller_id',$this->session->userdata('biller_id'));
			}
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
		public function loan_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('loans.id' , $_POST['val']);
			}
			$this->db
				->select("
					loans.id as id,
					loans.reference_no as loan_reference_no,
					loan_applications.reference_no as application_no,
					loan_borrowers.code,
					loans.borrower,
					CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
					loan_products.name as loan_product,
					loans.principal_amount,
					IFNULL(bpas_loans.principal_amount,0) - ROUND(IFNULL(bpas_payments.principal_paid,0)) as outstanding_balance,
					loans.currency,
					loans.disbursed_at,
					DATE_ADD(payment_date, INTERVAL (bpas_loans.term*bpas_loans.frequency) DAY) as maturity_date,
					CONCAT(bpas_loan_officers.last_name,' ',bpas_loan_officers.first_name) as loan_officer,
					CONCAT(bpas_tellers.last_name,' ',bpas_tellers.first_name) as teller,
					loans.status")
				->from("loans")
				->join("loan_applications","loan_applications.id=application_id","left")
				->join("loan_borrowers","loan_borrowers.id=loans.borrower_id","left")
				->join("loan_products","loan_products.id=loans.loan_product_id","left")
				->join("users as bpas_loan_officers","bpas_loan_officers.id=loans.loan_officer_id","left")
				->join("users as bpas_tellers","bpas_tellers.id=loans.teller_id","left")
				->join('(SELECT 
								loan_id,
								IFNULL(SUM(amount),0) AS principal_paid,
								IFNULL(SUM(interest_paid),0) AS interest_paid
							FROM
								'.$this->db->dbprefix('payments').'
							WHERE type = "received"
							GROUP BY loan_id) as bpas_payments', 'bpas_payments.loan_id=loans.id', 'left');
							
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('loans'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('loan_reference_no'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('application_no'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('borrower'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('gender'));
						$this->excel->getActiveSheet()->SetCellValue('F1', lang('loan_product'));
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('principal_amount'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('outstanding_amount'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('currency'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('disbursed_date'));
						$this->excel->getActiveSheet()->SetCellValue('K1', lang('maturity_date'));
						$this->excel->getActiveSheet()->SetCellValue('L1', lang('loan_officer'));
						$this->excel->getActiveSheet()->SetCellValue('M1', lang('teller'));
						$this->excel->getActiveSheet()->SetCellValue('N1', lang('status'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1:M1")->applyFromArray($style)->getFont()->setBold(true);
						$row = 2;
						foreach ($q->result() as $loan){
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $loan->loan_reference_no);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $loan->application_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $loan->code);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $loan->borrower);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $loan->gender);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $loan->loan_product);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatMoney($loan->principal_amount));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($loan->outstanding_balance));
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $loan->currency);
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->hrsd($loan->disbursed_at));
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->hrsd($loan->maturity_date));
							$this->excel->getActiveSheet()->SetCellValue('L' . $row, $loan->loan_officer);
							$this->excel->getActiveSheet()->SetCellValue('M' . $row, $loan->teller);
							$this->excel->getActiveSheet()->SetCellValue('N' . $row, $loan->status);
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(16);
						$filename = 'loans_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_loan_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function suspend_loan($id = NULL, $suspend = FALSE)
	{
		$this->bpas->checkPermissions("suspend");
		$loan = $this->loans_model->getloanByID($id);
		if($loan->status != 'active'){
			$this->session->set_flashdata('error', lang("loan_cannot_suspend"));
			$this->bpas->md();
		}
        if ($this->loans_model->suspendLoan($id, $suspend)) {
			if ($this->input->is_ajax_request()) {
				echo lang("loan_suspended"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("loan_suspended"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
	}
	
	public function payoff_loan($id = NULL, $payoff = FALSE)
	{
		$this->bpas->checkPermissions("payoff");
		$loan = $this->loans_model->getloanByID($id);
		$principal_paid = $this->loans_model->getPrincipalPaidByLoanID($id);
		if($loan->status != 'active'){
			$this->session->set_flashdata('error', lang("loan_cannot_payoff"));
			$this->bpas->md();
		}else if($loan->principal_amount > $principal_paid){
			$this->session->set_flashdata('error', lang("loan_cannot_payoff"));
			$this->bpas->md();
		}
        if ($this->loans_model->payoffLoan($id, $payoff)) {
			if ($this->input->is_ajax_request()) {
				echo lang("loan_payoff"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("loan_payoff"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
	}
	
	public function borrowers()
    {
		$this->bpas->checkPermissions("borrowers");
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loan')), array('link' => '#', 'page' => lang('borrowers')));
        $meta = array('page_title' => lang('borrowers'), 'bc' => $bc);
        $this->page_construct('loans/borrowers', $meta, $this->data);
    }
	
	public function getBorrowers()
    {
        $this->bpas->checkPermissions("borrowers");
        $this->load->library('datatables');
		$view_borrower_link = anchor('admin/loans/view_borrower/$1', '<i class="fa fa-file-text-o"></i> ' .lang('borrower_details'),' class="view-borrower"');
		$edit_borrower_link = anchor('admin/loans/edit_borrower/$1', '<i class="fa fa-edit"></i> ' .lang('edit_borrower'),' class="edit-borrower" ');
		$delete_borrower_link = "<a href='#' class='po delete-borrower' title='<b>" . lang("delete_borrower") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_borrower/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_borrower') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_borrower_link.'</li>
						<li>'.$edit_borrower_link . '</li>
						<li>'.$delete_borrower_link . '</li>
					</ul>
				</div></div>';
        $this->datatables
            ->select("
				loan_borrowers.id as id,
				loan_borrowers.code,
				loan_unique_types.name as unique_type,
				loan_borrowers.unique_no,
				CONCAT(bpas_loan_borrowers.last_name,' ',bpas_loan_borrowers.first_name) as name,
				CONCAT(UCASE(LEFT(gender, 1)),SUBSTRING(gender, 2)) as gender,
				loan_borrowers.phone,
				bpas_loans.cycle,
				IFNULL(bpas_provinces.name,'') as province,
				IFNULL(bpas_districts.name,'') as district,
				IFNULL(bpas_communces.name,'') as commune,
				IFNULL(bpas_villages.name,'') as village
				")
            ->from("loan_borrowers")
			->join("loan_unique_types","loan_unique_types.id=unique_type_id","left")
			->join("(SELECT 
								borrower_id, 
								IFNULL(COUNT(id),'n/a') as cycle 
						FROM bpas_loans GROUP BY borrower_id
					) as bpas_loans","bpas_loans.borrower_id=loan_borrowers.id","left")
			->join('locations as bpas_countries','loan_borrowers.country_id = bpas_countries.id','left')
			->join('locations as bpas_provinces','loan_borrowers.province_id = bpas_provinces.id','left')
			->join('locations as bpas_districts','loan_borrowers.district_id = bpas_districts.id','left')
			->join('locations as bpas_communces','loan_borrowers.commune_id = bpas_communces.id','left')
			->join('locations as bpas_villages','loan_borrowers.village_id = bpas_villages.id','left')
			->where('loan_borrowers.type','Customer')
			->group_by("loan_borrowers.id");
			
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('created_by', $this->session->userdata('user_id'));
			}
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function borrower_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('loan_borrowers.id' , $_POST['val']);
			}
			$this->db
				->select("
					loan_borrowers.id as id,
					loan_borrowers.code,
					loan_unique_types.name as unique_type,
					loan_borrowers.unique_no,
					CONCAT(bpas_loan_borrowers.last_name,' ',bpas_loan_borrowers.first_name) as name,
					CONCAT(UCASE(LEFT(gender, 1)),SUBSTRING(gender, 2)) as gender,
					loan_borrowers.phone,
					bpas_loans.cycle,
					IFNULL(bpas_provinces.name,'') as province,
					IFNULL(bpas_districts.name,'') as district,
					IFNULL(bpas_communces.name,'') as commune,
					IFNULL(bpas_villages.name,'') as village
				")
            ->from("loan_borrowers")
			->join("loan_unique_types","loan_unique_types.id=unique_type_id","left")
			->join("(SELECT 
								borrower_id, 
								IFNULL(COUNT(id),'n/a') as cycle 
						FROM bpas_loans GROUP BY borrower_id
					) as bpas_loans","bpas_loans.borrower_id=loan_borrowers.id","left")
			->join('locations as bpas_countries','loan_borrowers.country_id = bpas_countries.id','left')
			->join('locations as bpas_provinces','loan_borrowers.province_id = bpas_provinces.id','left')
			->join('locations as bpas_districts','loan_borrowers.district_id = bpas_districts.id','left')
			->join('locations as bpas_communces','loan_borrowers.commune_id = bpas_communces.id','left')
			->join('locations as bpas_villages','loan_borrowers.village_id = bpas_villages.id','left');
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('borrowers');
                    foreach ($_POST['val'] as $id) {
                        $this->loans_model->deleteBorrower($id);
                    }
                    $this->session->set_flashdata('message', lang("borrowers_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);

                }else if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('borrowers'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('borrower_code'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('unique_type'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('unique_no'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('full_name'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('gender'));
						$this->excel->getActiveSheet()->SetCellValue('F1', lang('phone'));
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('cycle'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('province'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('district'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('commune'));
						$this->excel->getActiveSheet()->SetCellValue('K1', lang('village'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($style)->getFont()->setBold(true);
						$row = 2;
						foreach ($q->result() as $borrower){
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $borrower->code);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $borrower->unique_type);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $borrower->unique_no);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $borrower->name);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $borrower->gender);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $borrower->phone);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $borrower->cycle);
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $borrower->province);
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $borrower->district);
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $borrower->commune);
							$this->excel->getActiveSheet()->SetCellValue('K' . $row, $borrower->village);
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(18);
						$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
						$filename = 'borrowers_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_repayment_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
    
	public function delete_borrower($id = null)
    {
        $this->bpas->checkPermissions("borrowers");
        if ($this->loans_model->deleteBorrower($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("borrower_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("borrower_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function add_borrower()
	{
		$this->bpas->checkPermissions("borrowers");
		$this->form_validation->set_rules('unique_no', lang("unique_no"), 'required|is_unique[loan_borrowers.unique_no]');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		$this->form_validation->set_rules('phone', lang("phone"), 'required');
		$this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[loan_borrowers.email]');
        if ($this->form_validation->run() == true) {
			
			$code = $this->input->post('code') ? $this->input->post('code') : $this->site->getReference('borrower');
			$unique_type = $this->input->post('unique_type',true);
			$unique_no = $this->input->post('unique_no',true);
			$first_name = $this->input->post('first_name',true);
			$last_name = $this->input->post('last_name',true);
			$gender = $this->input->post('gender',true);
			$phone = $this->input->post('phone',true);
			$email = $this->input->post('email',true);
			$dob = $this->input->post('dob',true);
			$working_status = $this->input->post('working_status',true);
			$marital_status = $this->input->post('marital_status',true);
			$country = $this->input->post('country',true);
			$province = $this->input->post('province',true);
			$district = $this->input->post('district',true);
			$commune = $this->input->post('commune',true);
			$village = $this->input->post('village',true);
			$street_no = $this->input->post('street_no',true);
			$home_no = $this->input->post('home_no',true);
			$address = $this->input->post('address',true);
			$note = $this->input->post('note',true);
			$data = array(
				'code' => $code,
				'unique_type_id' => $unique_type,
                'unique_no' => $unique_no,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'gender' => $gender,
				'phone' => $phone,
				'email' => $email,
				'dob' => $this->bpas->fld($dob),
				'country_id' => $country,
				'province_id' => $province,
				'district_id' => $district,
				'commune_id' => $commune,
				'village_id' => $village,
				'street_no' => $street_no,
                'home_no' => $home_no,
				'note' => $note,
				'marital_status' => $marital_status,
				'working_status' => $working_status,
				'created_by' => $this->session->userdata("user_id"),
            );
			
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['photo'] = $photo;
				$this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
			
        } elseif ($this->input->post('add_borrower')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->addBorrower($data)) {
        	if($this->input->post('code') == ""){
        		$this->site->updateReference('borrower');
        	}
            $this->session->set_flashdata('message', lang("borrower_added"));
            admin_redirect('loans/borrowers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['unique_types'] = $this->loans_model->getUniqueTypes();
			$this->data['working_status'] = $this->loans_model->getWorkingStatus();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('add_borrower')));
			$meta = array('page_title' => lang('add_borrower'), 'bc' => $bc);
			$this->page_construct('loans/add_borrower', $meta, $this->data);
        }
	}
	
	public function get_countries()
	{
		$id = $this->input->get("id")?$this->input->get("id"):0;
		$countries = $this->loans_model->getLocations(0, 'country');
		$opt_countries = array();
		if($countries){
			foreach($countries as $country){
				$opt_countries[$country->id] = $country->name;
			}
		}
		$opt = form_dropdown('country', $opt_countries, (isset($_POST['country']) ? $_POST['country'] : $id), 'id="brcountry" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function get_provinces()
	{
		$id = $this->input->get("id")?$this->input->get("id"):0;
		$country_id = $this->input->get('country_id');
		$provinces = $this->loans_model->getLocations($country_id, 'province');
		$opt_provinces = array(lang('select')." ".lang('province'));
		if($provinces){
			foreach($provinces as $province){
				$opt_provinces[$province->id] = $province->name;
			}
		}
		$opt = form_dropdown('province', $opt_provinces, (isset($_POST['province']) ? $_POST['province'] : $id), 'id="brprovince" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function get_districts()
	{
		$id = $this->input->get("id")?$this->input->get("id"):0;
		$province_id = $this->input->get('province_id');
		$districts = $this->loans_model->getLocations($province_id, 'district');
		$opt_districts = array(lang('select')." ".lang('district'));
		if($districts){
			foreach($districts as $district){
				$opt_districts[$district->id] = $district->name;
			}
		}
		$opt = form_dropdown('district', $opt_districts, (isset($_POST['district']) ? $_POST['district'] : $id), 'id="brdistrict" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function get_communces()
	{
		$id = $this->input->get("id")?$this->input->get("id"):0;
		$district_id = $this->input->get('district_id');
		$communes = $this->loans_model->getLocations($district_id, 'commune');
		$opt_communes = array(lang('select')." ".lang('commune'));
		if($communes){
			foreach($communes as $commune){
				$opt_communes[$commune->id] = $commune->name;
			}
		}
		$opt = form_dropdown('commune', $opt_communes, (isset($_POST['commune']) ? $_POST['commune'] : $id), 'id="brcommune" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function get_villages()
	{
		$id = $this->input->get("id")?$this->input->get("id"):0;
		$commune_id = $this->input->get('commune_id');
		$villages = $this->loans_model->getLocations($commune_id, 'village');
		$opt_villages =array(lang('select')." ".lang('village'));
		if($villages){
			foreach($villages as $village){
				$opt_villages[$village->id] = $village->name;
			}
		}
		$opt = form_dropdown('village', $opt_villages, (isset($_POST['village']) ? $_POST['village'] : $id), 'id="brvillage" class="form-control"');
		echo json_encode(array("result" => $opt));
	}
	
	public function edit_borrower($id = null)
	{
		$this->bpas->checkPermissions("borrowers");
		$unique_no = $this->input->post('unique_no',true);
		$email = $this->input->post('email',true);
		$borrower = $this->loans_model->getBorrowerByID($id);
		$form_nric = '';
		if($borrower->unique_no != $unique_no){
			$form_nric = "|is_unique[loan_borrowers.unique_no]";
		}
		$form_email = '';
		if($borrower->email != $email){
			$form_email = "|is_unique[loan_borrowers.email]";
		}
		$this->form_validation->set_rules('unique_no', lang("unique_no"), 'required'.$form_nric);
		$this->form_validation->set_rules('email', lang("email"), 'trim'.$form_email);
		$this->form_validation->set_rules('phone', lang("phone"), 'required');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
			$unique_type = $this->input->post('unique_type',true);
			$unique_no = $this->input->post('unique_no',true);
			$first_name = $this->input->post('first_name',true);
			$last_name = $this->input->post('last_name',true);
			$gender = $this->input->post('gender',true);
			$phone = $this->input->post('phone',true);
			$email = $this->input->post('email',true);
			$dob = $this->input->post('dob',true);
			$working_status = $this->input->post('working_status',true);
			$marital_status = $this->input->post('marital_status',true);
			$country = $this->input->post('country',true);
			$province = $this->input->post('province',true);
			$district = $this->input->post('district',true);
			$commune = $this->input->post('commune',true);
			$village = $this->input->post('village',true);
			$street_no = $this->input->post('street_no',true);
			$home_no = $this->input->post('home_no',true);
			$address = $this->input->post('address',true);
			$note = $this->input->post('note',true);
			$data = array(
				'unique_type_id' => $unique_type,
                'unique_no' => $unique_no,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'gender' => $gender,
				'phone' => $phone,
				'email' => $email,
				'dob' => $this->bpas->fld($dob),
				'country_id' => $country,
				'province_id' => $province,
				'district_id' => $district,
				'commune_id' => $commune,
				'village_id' => $village,
				'street_no' => $street_no,
                'home_no' => $home_no,
				'note' => $note,
				'marital_status' => $marital_status,
				'working_status' => $working_status,
				'created_by' => $this->session->userdata("user_id"),
            );
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['photo'] = $photo;
				$this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
			
        } elseif ($this->input->post('edit_borrower')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateBorrower($id, $data)) {
            $this->session->set_flashdata('message', lang("borrower_updated"));
            admin_redirect('loans/borrowers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
			$this->data['borrower']= $borrower;
			$this->data['unique_types'] = $this->loans_model->getUniqueTypes();
			$this->data['working_status'] = $this->loans_model->getWorkingStatus();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('add_borrower')));
			$meta = array('page_title' => lang('edit_borrower'), 'bc' => $bc);
			$this->page_construct('loans/edit_borrower', $meta, $this->data);
        }
	}
	
	public function view_borrower($id = null)
	{
		$this->bpas->checkPermissions("borrowers");
		$borrower = $this->loans_model->getBorrowerByID($id);
		if(isset($_POST['update_gps'])){
			$data = array(
				"latitude" => $this->input->post('latitude'),
				"longitude" => $this->input->post('longitude'),
			);
			if($this->loans_model->updateBorrower($id, $data)){
				$this->session->set_flashdata('message', lang("borrower_updated"));
				admin_redirect('loans/view_borrower/'.$id."#gps");
			}
		}
		$this->data['borrower'] = $borrower;
		$this->data['unique_type'] = $this->loans_model->getUniqueType($borrower->unique_type_id);
		$this->data['country'] = $this->loans_model->getLocationByID($borrower->country_id); 
		$this->data['province'] = $this->loans_model->getLocationByID($borrower->province_id);
		$this->data['district'] = $this->loans_model->getLocationByID($borrower->district_id);
		$this->data['commune'] = $this->loans_model->getLocationByID($borrower->commune_id);
		$this->data['village'] = $this->loans_model->getLocationByID($borrower->village_id);
		$this->data['working'] = $this->loans_model->getWorkingStatusByID($borrower->working_status);
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('borrower_details')));
        $meta = array('page_title' => lang('borrower_details'), 'bc' => $bc);
        $this->page_construct('loans/view_borrower', $meta, $this->data);
	}
	
	public function borrower_suggestions($term = NULL, $limit = NULL)
    {
        if ($this->input->get('term') || $this->input->get('term') == 0) {
            $term = $this->input->get('term', TRUE);
        }
        if (strlen($term) < 1) {
            return FALSE;
        }
        $limit = $this->Settings->rows_per_page;
        $rows['results'] = $this->loans_model->getBorrowerSuggestions($term, $limit);
        $this->bpas->send_json($rows);
    }
	
	public function getBorrower($id = NULL)
    {
        $row = $this->loans_model->getBorrowerByID($id, "Customer");
        $this->bpas->send_json(array(array('id' => $row->id, 'text' => $row->code . ' - ' .$row->last_name.' '.$row->first_name)));
    }
	
	public function borrower_types()
    {
		$this->bpas->checkPermissions('borrower_types');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('borrower_types')));
        $meta = array('page_title' => lang('borrower_types'), 'bc' => $bc);
        $this->page_construct('loans/borrower_types', $meta, $this->data);
    }
	
	public function getBorrowerTypes()
    {
        $this->bpas->checkPermissions("borrower_types");
        $this->load->library('datatables');
		$edit_borrower_type_link = anchor('admin/loans/edit_borrower_type/$1', '<i class="fa fa-edit"></i> ' .lang('edit_borrower_type'),' class="edit-borrower_type" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_borrower_type_link = "<a href='#' class='po delete-borrower_type' title='<b>" . lang("delete_borrower_type") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_borrower_type/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_borrower_type') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$edit_borrower_type_link . '</li>
						<li>'.$delete_borrower_type_link . '</li>
					</ul>
				</div></div>';
        $this->datatables
            ->select("
				loan_borrower_types.id as id,
				loan_borrower_types.name,
				loan_borrower_types.description")
            ->from("loan_borrower_types");
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function borrower_type_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('loan_borrower_types.id' , $_POST['val']);
			}
			$this->db
            ->select("
				loan_borrower_types.id as id,
				loan_borrower_types.name,
				loan_borrower_types.description")
            ->from("loan_borrower_types");
			
			$q = $this->db->get();
			if(isset($_POST['val'])){
				
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('borrower_types');
                    foreach ($_POST['val'] as $id) {
                        $this->loans_model->deleteBorrowerType($id);
                    }
                    $this->session->set_flashdata('message', lang("borrower_types_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);

                }else if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('borrower_types'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1:B1")->applyFromArray($style)->getFont()->setBold(true);
						$row = 2;
						foreach ($q->result() as $borrower_type){
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $borrower_type->name);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $borrower_type->description);
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
						$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
						$filename = 'borrower_types_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_repayment_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function delete_borrower_type($id = null)
    {
        $this->bpas->checkPermissions("borrower_types");
        if ($this->loans_model->deleteBorrowerType($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("borrower_types_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("borrower_types_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function add_borrower_type()
	{
		$this->bpas->checkPermissions("borrower_types");
		$this->form_validation->set_rules('name', lang("name"), 'required|trim|is_unique[loan_borrower_types.name]');
		if ($this->form_validation->run() == true) {
			$name = $this->input->post('name',true);
			$description = $this->input->post('description',true);
			$data = array(
                'name' => $name,
                'description' => $description,
            );
        } elseif ($this->input->post('add_borrower_type')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->addBorrowerType($data)) {
            $this->session->set_flashdata('message', lang("group_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/add_borrower_type', $this->data);
        }
	}
	
	public function edit_borrower_type($id = NULL)
	{
		$this->bpas->checkPermissions("borrower_types");
		$name = $this->input->post('name',true);
		$borrower_type = $this->loans_model->getBorrowerTypeByID($id);
		$form_name = '';
		if($borrower_type->name != $name){
			$form_name = '|trim|is_unique[loan_borrower_types.name]';
		}
		$this->form_validation->set_rules('name', lang("name"), 'required'.$form_name);
		if ($this->form_validation->run() == true) {
			$description = $this->input->post('description',true);
			$data = array(
                'name' => $name,
                'description' => $description,
            );
        } elseif ($this->input->post('edit_borrower_type')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateBorrowerType($id, $data)) {
            $this->session->set_flashdata('message', lang("borrower_type_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['borrower_type'] = $borrower_type;
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/edit_borrower_type', $this->data);
        }
	}
	
	public function add_schedule($id = NULL)
	{
		$this->bpas->checkPermissions("schedule-add");
		$loan = $this->loans_model->getLoanByID($id);
		if($loan->status != 'pending'){
			$this->session->set_flashdata('error', lang("schedule_cannot_add"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        if ($this->form_validation->run() == true) {
			$items = array();
			for($m = 0; $m < count($_POST['tperiod']); $m++){
				$items[] = array(
						'period' => $_POST['tperiod'][$m],
						'interest' => $_POST['trate'][$m],
						'principal' => $_POST['tprincipal'][$m],
						'payment' => $_POST['tpayment'][$m],
						'balance' => $_POST['tbalance'][$m],
						'deadline' => $this->bpas->fld($_POST['tdeadline'][$m]),
					);
			}
			if($items){
				$loan_product = $this->loans_model->getLoanProductByID($loan->loan_product_id);
				$charges = $this->loans_model->getFeeCharge(json_decode($loan_product->charge_ids),1);
				foreach($items as $k=>$item){
					$total_fee = 0;
					if($charges){
						foreach($charges as $charge){
							if($charge->calculate == 1){
								$fee_charge = ($charge->amount * $items[$k]['principal']) / 100;
							}else if($charge->calculate == 2){
								$fee_charge = ($charge->amount * ($items[$k]['principal']+$items[$k]['interest'])) / 100;
							}else if($charge->calculate == 3){
								$fee_charge = ($charge->amount * $items[$k]['interest']) / 100;
							}else if($charge->calculate == 4){
								$fee_charge = ($charge->amount * $total_payment) / 100;
							}else if($charge->calculate == 5){
								$fee_charge = ($charge->amount * $loan->principal_amount) / 100;
							}else{
								$fee_charge = $charge->amount;
							}
							$total_fee += $fee_charge;
						}
					}
					$items[$k]['fee_charge'] = $total_fee;
				}
			}
		}
		if ($this->form_validation->run() == true && $this->loans_model->addSchedule($id, $items)) {
			$this->session->set_flashdata('message', lang("loan_added"));
			admin_redirect("loans");
		}else{
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$this->data['id'] = $id;
			$this->data['loan'] = $loan;
			$this->data['loan_products'] = $this->loans_model->getLoanProducts(); 
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['frequencies'] = $this->loans_model->getFrequencies();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('add_schedule')));
			$meta = array('page_title' => lang('add_schedule'), 'bc' => $bc);
			$this->page_construct('loans/add_schedule', $meta, $this->data);
		}
	}
	
	public function edit_schedule($id = NULL)
	{
		$this->bpas->checkPermissions("schedule-edit");
		$loan = $this->loans_model->getLoanByID($id);
		if($loan->status != 'active'){
			$this->session->set_flashdata('error', lang("schedule_cannot_edit"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['loans-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$term = $this->input->post("term", true);
			$frequency = $this->input->post("frequency", true);
			$interest_rate = $this->input->post("interest_rate", true);
			$interest_period = $this->input->post("interest_period");
			$interest_method = $this->input->post("interest_method");
			$payment_date = $this->input->post("payment_date");
			$data = array(
				"term" => $term,
				"frequency" => $frequency,
				"interest_rate" => $interest_rate,
				"interest_period" => $interest_period,
				"interest_method" => $interest_method,
				"payment_date" => $this->bpas->fld($payment_date),
				"updated_by" => $this->session->userdata("user_id"),
				"updated_at" => date('Y-m-d H:i:s')
			);
			$interest_rate_max = $this->input->post("interest_rate_max",true);
			if($interest_rate > $interest_rate_max){
				$this->session->set_flashdata('error', lang("must_less_than_interest_rate_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$term_max = $this->input->post("term_max",true);
			if($term > $term_max){
				$this->session->set_flashdata('error', lang("must_less_than_term_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$items = array();
			for($m = 0; $m < count($_POST['tperiod']); $m++){
				$items[] = array(
						'period' => $_POST['tperiod'][$m],
						'interest' => $_POST['trate'][$m],
						'principal' => $_POST['tprincipal'][$m],
						'payment' => $_POST['tpayment'][$m],
						'balance' => $_POST['tbalance'][$m],
						'deadline' => $this->bpas->fld($_POST['tdeadline'][$m]),
					);
			}
			if($items){
				$loan_product = $this->loans_model->getLoanProductByID($loan->loan_product_id);
				$charges = $this->loans_model->getFeeCharge(json_decode($loan_product->charge_ids),1);
				foreach($items as $k=>$item){
					$total_fee = 0;
					if($charges){
						foreach($charges as $charge){
							if($charge->calculate == 1){
								$fee_charge = ($charge->amount * $items[$k]['principal']) / 100;
							}else if($charge->calculate == 2){
								$fee_charge = ($charge->amount * ($items[$k]['principal']+$items[$k]['interest'])) / 100;
							}else if($charge->calculate == 3){
								$fee_charge = ($charge->amount * $items[$k]['interest']) / 100;
							}else if($charge->calculate == 4){
								$fee_charge = ($charge->amount * $total_payment) / 100;
							}else if($charge->calculate == 5){
								$fee_charge = ($charge->amount * $loan->principal_amount) / 100;
							}else{
								$fee_charge = $charge->amount;
							}
							$total_fee += $fee_charge;
						}
					}
					$items[$k]['fee_charge'] = $total_fee;
				}
			}
		}
		if ($this->form_validation->run() == true && $this->loans_model->updateSchedule($id, $data, $items)) {
			$this->session->set_flashdata('message', lang("loan_added"));
			admin_redirect("loans");
		}else{
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$this->data['id'] = $id;
			$this->data['loan'] = $loan;
			$this->data['loan_items'] = $this->loans_model->getLoanItemsByLoanID($id);
			$this->data['loan_products'] = $this->loans_model->getLoanProducts(); 
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['frequencies'] = $this->loans_model->getFrequencies();
			$this->data['interest_method'] = $this->site->getAllInterestmethod();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('edit_schedule')));
			$meta = array('page_title' => lang('edit_schedule'), 'bc' => $bc);
			$this->page_construct('loans/edit_schedule', $meta, $this->data);
		}
	}
	
	public function view($id = NULL)
	{
		$loan = $this->loans_model->getLoanByID($id);
		$borrower = $this->loans_model->getBorrowerByID($loan->borrower_id);
		$borrower_type = $this->loans_model->getBorrowerTypeByID($loan->borrower_type_id);
		$product = $this->loans_model->getLoanProductByID($loan->loan_product_id);
		$this->data['id'] = $id;
		$this->data['borrower'] = $borrower;
		$this->data['borrower_type'] = $borrower_type;
		$this->data['loan'] = $loan;
		$this->data['product'] = $product;
		$this->data['country'] = $this->loans_model->getLocationByID($borrower->country_id); 
		$this->data['province'] = $this->loans_model->getLocationByID($borrower->province_id);
		$this->data['district'] = $this->loans_model->getLocationByID($borrower->district_id);
		$this->data['commune'] = $this->loans_model->getLocationByID($borrower->commune_id);
		$this->data['village'] = $this->loans_model->getLocationByID($borrower->village_id);
		$this->data['interest_method'] = $this->site->getAllInterestmethod();
		$this->data['interest_period'] = $this->site->getAllInterestperiod();
		$this->data['working'] = $this->loans_model->getWorkingStatusByID($borrower->working_status);
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('loan_details')));
        $meta = array('page_title' => lang('loan_details'), 'bc' => $bc);
        $this->page_construct('loans/view', $meta, $this->data);
	}
	
	public function getRepayments($id = NULL)
	{
		$this->bpas->checkPermissions("payments");
        $this->load->library('datatables');
		$add_payment_link = anchor('admin/loans/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$payments_link = anchor('admin/loans/view_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="view-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$add_payment_link . '</li>
						<li>'.$payments_link . '</li>
					</ul>
				</div></div>';
		
        $this->datatables->select("
				loan_items.id as id,
				loan_items.period,
				loan_items.deadline,
				loan_items.payment,
				loan_items.interest,
				loan_items.principal,
				loan_items.fee_charge,
				loan_items.penalty,
				loan_items.balance,
				SUM(IFNULL(bpas_payments.amount,0)) + SUM(IFNULL(bpas_payments.interest_paid,0)) as payment_paid,
				SUM(IFNULL(bpas_payments.interest_paid,0)) as interest_paid,
				SUM(IFNULL(bpas_payments.amount,0)) as principal_paid,
				IF(DATEDIFF(SYSDATE(),deadline)>0,DATEDIFF(SYSDATE(),deadline),0) AS overdue,
				loan_items.status")
            ->from("loan_items")
			->join('payments', 'payments.loan_item_id=loan_items.id', 'left')
			->where("loan_items.loan_id", $id)
			->group_by('loan_items.id');
			
		$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	public function repayment_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('loan_items.id' , $_POST['val']);
			}
			$this->db->select("
				loan_items.id as id,
				loan_items.period,
				loan_items.deadline,
				loan_items.payment,
				loan_items.interest,
				loan_items.principal,
				loan_items.fee_charge,
				loan_items.penalty,
				loan_items.balance,
				SUM(IFNULL(bpas_payments.amount,0)) + SUM(IFNULL(bpas_payments.interest_paid,0)) as payment_paid,
				SUM(IFNULL(bpas_payments.interest_paid,0)) as interest_paid,
				SUM(IFNULL(bpas_payments.amount,0)) as principal_paid,
				loan_items.status")
            ->from("loan_items")
			->join('payments', 'payments.loan_item_id=loan_items.id', 'left')
			->group_by('loan_items.id');
			$q = $this->db->get();
			
			if(isset($_POST['val'])){
				if (!empty($q)) {
					if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('loans'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('#'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('deadline'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('payment'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('interest'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('principal'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('fee_charge'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('penalty'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_paid'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('interest_paid'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('principal_paid'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('status'));
                    $style = array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						)
					);
					$this->excel->getActiveSheet()->getStyle("A1:L1")->applyFromArray($style)->getFont()->setBold(true);
					$row = 2;
                    foreach ($q->result() as $loan){
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $loan->period);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($loan->deadline));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $loan->payment);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $loan->interest);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $loan->principal);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $loan->fee_charge);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $loan->penalty);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $loan->balance);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $loan->payment_paid);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $loan->interest_paid);
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $loan->principal_paid);
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $loan->status);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'repayment_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
			}
            } else {
                $this->session->set_flashdata('error', lang("no_repayment_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function getTransactions($id = NULL)
	{
		$this->bpas->checkPermissions("payments");
		$this->load->library('datatables');
		$this->datatables
			->select("
				loan_items.period,
				payments.date,
				payments.reference_no,
				IFNULL((bpas_payments.amount) + (bpas_payments.interest_paid),0) as payment_paid,
				IFNULL(bpas_payments.interest_paid,0) as interest_paid,
				IFNULL(bpas_payments.amount,0) as principal_paid,
				IFNULL(bpas_payments.fee_charge,0) as fee_charge_paid,
				IFNULL(bpas_payments.penalty_paid,0) as penalty_paid,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				payments.paid_by,
				payments.type,
				payments.id")
			->from('payments')
			->join('loan_items', 'loan_items.id=payments.loan_item_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->where("payments.loan_id",$id);
		echo $this->datatables->generate();
	}
	
	public function transaction_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db->select("
				loan_items.period,
				payments.date,
				payments.reference_no,
				(bpas_payments.amount) + (bpas_payments.interest_paid) as payment_paid,
				payments.interest_paid,
				payments.amount as principal_paid,
				payments.fee_charge as fee_charge_paid,
				payments.penalty_paid,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				payments.paid_by,
				payments.type")
			->from('payments')
			->join('loan_items', 'loan_items.id=payments.loan_item_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->where("payments.loan_id",$id);
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
				$this->excel->getActiveSheet()->setTitle(lang('transactions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('#'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('payment_paid'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('interest_paid'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('principal_paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('fee_charge_paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('penalty_paid'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('type'));
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
				$this->excel->getActiveSheet()->getStyle("A1:K1")->applyFromArray($style)->getFont()->setBold(true);
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->period);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->payment_paid);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->interest_paid);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->principal_paid);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->fee_charge_paid);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->penalty_paid);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->paid_by);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->type);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'transactions_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);

			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function add_payment($id = NULL)
    {
		$this->bpas->checkPermissions('payments');
		$loan_item = $this->loans_model->getLoanItemsByID($id);
		$loan = $this->loans_model->getLoanByID($loan_item->loan_id);
		if ($loan->status != 'active') {
			$this->session->set_flashdata('error', lang("loan_cannot_add_payment"));
			$this->bpas->md();
		}else if ($loan_item->status == 'paid') {
			$this->session->set_flashdata('error', lang("loan_cannot_add_payment"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$loan->biller_id);
            if ($this->Owner || $this->Admin || $GP['loans-date']) {
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
								"amount" 	=> $camounts[$key],
								"currency" 	=> $currency[$key],
								"rate" 		=> $rate[$key],
							);
				}
			}
            $payment = array(
                'date' => $date,
				'loan_id' => $loan->id,
				'loan_item_id' => $loan_item->id,
                'reference_no' => $reference_no,
                'amount' => $this->input->post('principal-paid'),
				'interest_paid' => $this->input->post('interest-paid'),
				'fee_charge' => $this->input->post('fee-charge'),
				'penalty_paid' => $this->input->post('penalty-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'received',
				'currency' => json_encode($currencies),
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
			
			if($this->Settings->accounting == 1){
				$loanAcc = $this->loans_model->getLoanProductByID($loan->loan_product_id);
				$accTrans[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $loanAcc->loan_portfolio_account,
						'amount' => -($this->input->post('principal-paid')),
						'narrative' => 'Loan Payment '.$loan->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $loan->biller_id,
						'user_id' => $this->session->userdata('user_id')
					);
				$interest_paid = $this->input->post('interest-paid');
				$penalty_paid = $this->input->post('penalty-paid');
				if($interest_paid > 0){
					$accTrans[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $loanAcc->interest_income_account,
						'amount' => -($interest_paid),
						'narrative' => 'Loan Payment '.$loan->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $loan->biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
				$accTrans[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $this->input->post('paying_to'),
						'amount' => ($this->input->post('principal-paid') + $interest_paid + $penalty_paid),
						'narrative' => 'Loan Payment '.$loan->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $loan->biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				if($penalty_paid > 0){
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $loanAcc->penalty_income_account,
							'amount' => -($penalty_paid),
							'narrative' => 'Loan Payment '.$loan->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $loan->biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
				}
			}else{
				$accTrans =[];
			}
		}
		if ($this->form_validation->run() == true && $this->loans_model->addPayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#repayments");
        } else {
			$loan_product = $this->loans_model->getLoanProductByID($loan->loan_product_id);
			$this->data['id'] = $id;
			$this->data['loan_item'] = $loan_item;
			$this->data['loan'] = $loan;
			$this->data['payments'] = $this->loans_model->getPaymentByLoanItemID($id);
			$this->data['loan_product'] = $loan_product;
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$loan_product->default_bank_account,'1');
			}
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'loans/add_payment', $this->data);
        }
    }
	
	public function view_payments($id = NULL)
    {
		$this->bpas->checkPermissions('payments');
		$loan_item = $this->loans_model->getLoanItemsByID($id);
		$loan = $this->loans_model->getLoanByID($loan_item->loan_id);
		$this->data['loan'] = $loan;
		$this->data['loan_item'] = $loan_item;
		$this->data['payments'] = $this->loans_model->getPaymentByLoanItemID($id);
        $this->load->view($this->theme . 'loans/view_payments', $this->data);
    }
	
	public function delete_payment($id = NULL)
    {
        $this->bpas->checkPermissions('payments', true);
		$payment = $this->loans_model->getPaymentByID($id);
		$loan = $this->loans_model->getloanByID($payment->loan_id);
		if ($loan->status != 'active') {
			$this->session->set_flashdata('error', lang("loan_cannot_delete_payment"));
			$this->bpas->md();
		}
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->loans_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#repayments");
        }
    }
	
	public function edit_payment($id = NULL)
    {
		$this->bpas->checkPermissions('payments', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->loans_model->getPaymentByID($id);
		$loan = $this->loans_model->getloanByID($payment->loan_id);
		if ($loan->status != 'active') {
			$this->session->set_flashdata('error', lang("loan_cannot_edit_payment"));
			$this->bpas->md();
		}
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $GP['loans-date']) {
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
			$reference_no = $this->input->post('reference_no');
            $payment = array(
                'date' => $date,
                'reference_no' => $reference_no,
                'amount' => $this->input->post('principal-paid'),
				'interest_paid' => $this->input->post('interest-paid'),
				'fee_charge' => $this->input->post('fee-charge'),
				'penalty_paid' => $this->input->post('penalty-paid'),
				'discount' => $this->input->post('discount'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
				'currency' => json_encode($currencies),
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
			
			if($this->Settings->accounting == 1){
				$loanAcc = $this->loans_model->getLoanProductByID($loan->loan_product_id);
				$accTrans[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $loanAcc->loan_portfolio_account,
						'amount' => -($this->input->post('principal-paid')),
						'narrative' => 'Loan Payment '.$loan->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $loan->biller_id,
						'user_id' => $this->session->userdata('user_id')
					);
				$interest_paid = $this->input->post('interest-paid');
				$penalty_paid = $this->input->post('penalty-paid');
				if($interest_paid > 0){
					$accTrans[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $loanAcc->interest_income_account,
						'amount' => -($interest_paid),
						'narrative' => 'Loan Payment '.$loan->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $loan->biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				}
				$accTrans[] = array(
						'transaction' => 'Payment',
						'transaction_date' => $date,
						'reference' => $reference_no,
						'account' => $this->input->post('paying_to'),
						'amount' => ($this->input->post('principal-paid') + $interest_paid + $penalty_paid),
						'narrative' => 'Loan Payment '.$loan->reference_no,
						'description' => $this->input->post('note'),
						'biller_id' => $loan->biller_id,
						'user_id' => $this->session->userdata('user_id'),
					);
				if($penalty_paid > 0){
					$accTrans[] = array(
							'transaction' => 'Payment',
							'transaction_date' => $date,
							'reference' => $reference_no,
							'account' => $loanAcc->penalty_income_account,
							'amount' => -($penalty_paid),
							'narrative' => 'Loan Payment '.$loan->reference_no,
							'description' => $this->input->post('note'),
							'biller_id' => $loan->biller_id,
							'user_id' => $this->session->userdata('user_id'),
						);
				}
			}

        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]."#repayments");
        }
        if ($this->form_validation->run() == true && $this->loans_model->updatePayment($id, $payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#repayments");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
			$this->data['loan_item'] = $this->loans_model->getLoanItemsByID($payment->loan_item_id);
			$this->data['loan_product'] = $this->loans_model->getLoanProductByID($loan->loan_product_id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$this->Settings->default_cash,'1');
			}
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/edit_payment', $this->data);
        }
    }
	
	public function payment_note($id = null)
    {
		$this->bpas->checkPermissions("payments", true);
        $payment = $this->loans_model->getPaymentByID($id);
        $loan = $this->loans_model->getLoanByID($payment->loan_id);
        $this->data['biller'] = $this->site->getCompanyByID($loan->biller_id);
        $this->data['borrower'] = $this->loans_model->getBorrowerByID($loan->borrower_id);
        $this->data['loan'] = $loan;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang("payment_note");
        $this->load->view($this->theme . 'loans/payment_note', $this->data);
    }
	
	public function add_multi_payment($id = null)
	{
		$this->bpas->checkPermissions("payments", true);
		$this->load->helper('security');
		if ($this->input->get('id')) {
			$id = $this->input->get('id');
		}
		$ids = explode('LoanID',$id);
		$loans = $this->loans_model->getMultiLoansByID($ids);
		if ($loans[0]->status != 'active') {
			$this->session->set_flashdata('error', lang("loan_cannot_add_payment"));
			$this->bpas->md();
		}else if(!$loans){
			$this->session->set_flashdata('error', lang("loan_cannot_add_payment"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
		$this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
		$this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
		if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin  || $this->bpas->GP['loans-date']) {
				$date = $this->bpas->fld(trim($this->input->post('date')));
			} else {
				$date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
			}
			$photo = "";
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
					admin_redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
			}
			
			$total_principal = $this->input->post('principal-paid');
			$total_interest = $this->input->post('interest-paid');
			$total_fee = $this->input->post('fee-charge');
			$total_penalty = $this->input->post('penalty-paid');
			$camounts = $this->input->post("c_amount");
			$loan_item = $this->loans_model->getLoanItemsByID($ids[0]);
			$loan = $this->loans_model->getLoanByID($loan_item->loan_id);
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay',$loan->biller_id);
			$currencies = array();
			$paid_currencies = array();
			if(!empty($camounts)){
				foreach($camounts as $key => $camount){
					$currency = $this->input->post("currency");
					$rate = $this->input->post("rate");
					$paid_currencies[$currency[$key]] = array(
								"amount" => $camounts[$key],
								"currency" => $currency[$key],
								"rate" => $rate[$key],
							);
				}
			}
			$cur_def = $this->site->getCurrencyByCode($this->Settings->default_currency);
			for($i=0; $i<count($ids); $i++){
				if($total_principal > 0 || $total_interest > 0 || $total_fee > 0 || $total_penalty > 0){
					$bloan = $this->loans_model->getLoanBalanceByID($ids[$i]);
					if($bloan){
						$principal = $bloan->principal - $bloan->paid;
						$amt_principal = $principal;
						if($total_principal > $amt_principal){
							$pay_principal = $amt_principal;
							$total_principal = $total_principal - $amt_principal;
						}else{
							$pay_principal = $total_principal;
							$total_principal = 0;
						}
						
						$interest = $bloan->interest - $bloan->interest_paid;
						$amt_interest = $interest;
						if($total_interest > $amt_interest){
							$pay_interest = $amt_interest;
							$total_interest = $total_interest - $amt_interest;
						}else{
							$pay_interest = $total_interest;
							$total_interest = 0;
						}
						
						$fee_charge = $bloan->fee_charge - $bloan->fee_charge_paid;
						$amt_fee_charge = $fee_charge;
						if($total_fee > $amt_fee_charge){
							$pay_fee_charge = $amt_fee_charge;
							$total_fee = $total_fee - $amt_fee_charge;
						}else{
							$pay_fee_charge = $total_fee;
							$total_fee = 0;
						}
						
						$penalty = $bloan->penalty - $bloan->penalty_paid;
						$amt_penalty = $penalty;
						if($total_penalty > $amt_penalty){
							$pay_penalty_paid = $amt_penalty;
							$total_penalty = $total_penalty - $amt_penalty;
						}else{
							$pay_penalty_paid = $total_penalty;
							$total_penalty = 0;
						}
						
						$currencies = array();
						if(!empty($camounts)){
							$total_paid = $pay_principal + $pay_interest + $pay_fee_charge + $pay_penalty_paid;
							foreach($paid_currencies as $cur_code => $paid_currencie){
								$paid_cur = $paid_currencie['amount'];
								if($paid_cur > 0){
									if($cur_code != $cur_def->code){
										if($paid_currencie['rate'] > $cur_def->rate){
											$paid_cur = $paid_cur / $paid_currencie['rate'];
										}else{
											$paid_cur = $paid_cur * $cur_def->rate;
										}
									}
									if($paid_cur >= $total_paid && $total_paid > 0){
										$paid_currencie['amount'] = $total_paid;
										if($cur_code != $cur_def->code){
											if($paid_currencie['rate'] > $cur_def->rate){
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) * $paid_currencie['rate'];
											}else{
												$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid) / $cur_def->rate;
											}
										}else{
											$paid_currencies[$cur_code]['amount'] = ($paid_cur - $total_paid);
										}
										$total_paid = 0;
									}else{
										if($total_paid > 0){
											$paid_currencie['amount'] = $paid_cur;
											$paid_currencies[$cur_code]['amount'] = 0;
											$total_paid = $total_paid - $paid_cur;
										}else{
											$paid_currencie['amount'] = 0;
										}
									}
								}								
								if($cur_code != $cur_def->code){
									if($paid_currencie['rate'] > $cur_def->rate){
										$paid_currencie['amount'] = $paid_currencie['amount'] * $paid_currencie['rate'];
									}else{
										$paid_currencie['amount'] = $paid_currencie['amount'] / $cur_def->rate;
									}
								}
								$currencies[] = $paid_currencie;
							}
						}
						
						$payment[] = array(
							'date' 		    => $date,
							'loan_id' 	    => $bloan->loan_id,
							'loan_item_id'  => $bloan->id,
							'reference_no'  => $reference_no,
							'amount' 	    => $pay_principal,
							'interest_paid' => $pay_interest,
							'fee_charge' 	=> $pay_fee_charge,
							'penalty_paid' 	=> $pay_penalty_paid,
							'paid_by' => $this->input->post('paid_by'),
							'cheque_no' => $this->input->post('cheque_no'),
							'cc_no' => $this->input->post('pcc_no'),
							'cc_holder' => $this->input->post('pcc_holder'),
							'cc_month' => $this->input->post('pcc_month'),
							'cc_year' => $this->input->post('pcc_year'),
							'cc_type' => $this->input->post('pcc_type'),
							'note' => $this->input->post('note'),
							'created_by' => $this->session->userdata('user_id'),
							'type' => 'received',
							'currency' => json_encode($currencies),
							'account_code' => $this->input->post('paying_to'),
							'attachment' => $photo,
						);
						if($this->Settings->accounting == 1){
							$loanAcc = $this->loans_model->getLoanProductByID($loan->loan_product_id);
							$accTrans[$bloan->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $loanAcc->loan_portfolio_account,
									'amount' => -($pay_principal),
									'narrative' => 'Loan Payment '.$loan->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $loan->biller_id,
									'user_id' => $this->session->userdata('user_id')
								);
							if($pay_interest > 0){
								$accTrans[$bloan->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $loanAcc->interest_income_account,
									'amount' => -($pay_interest),
									'narrative' => 'Loan Payment '.$loan->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $loan->biller_id,
									'user_id' => $this->session->userdata('user_id'),
								);
							}
							$accTrans[$bloan->id][] = array(
									'transaction' => 'Payment',
									'transaction_date' => $date,
									'reference' => $reference_no,
									'account' => $this->input->post('paying_to'),
									'amount' => ($pay_principal+$pay_interest),
									'narrative' => 'Loan Payment '.$loan->reference_no,
									'description' => $this->input->post('note'),
									'biller_id' => $loan->biller_id,
									'user_id' => $this->session->userdata('user_id'),
								);
						}
					}
				}
			}
		} elseif ($this->input->post('add_payment')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($this->form_validation->run() == true && $this->loans_model->addMultiPayment($payment, $accTrans)) {
			$this->session->set_flashdata('message', lang("payment_added"));
			admin_redirect($_SERVER["HTTP_REFERER"]."#repayments");
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['loans'] = $loans;

			$this->data['payment_ref'] = ''; 
			$this->data['modal_js'] = $this->site->modal_js();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$this->Settings->default_cash,'1');
			}
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->load->view($this->theme . 'loans/add_multi_payment', $this->data);
		}
	}

	public function missed_repayments()
	{
		$this->bpas->checkPermissions("payments");
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loan')), array('link' => '#', 'page' => lang('missed_repayments')));
        $meta = array('page_title' => lang('loans'), 'bc' => $bc);
        $this->page_construct('loans/missed_repayments', $meta, $this->data);
	}
	
	public function getMissedRepayments()
    {		
		$this->bpas->checkPermissions('payments');
		$add_payment_link = anchor('admin/loans/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$payments_link = anchor('admin/loans/view_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="view-payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$add_payment_link . '</li>
						<li>'.$payments_link . '</li>
					</ul>
				</div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
				loan_items.id as id,
				loan_items.period,
				loan_items.deadline,
				loans.reference_no,
				CONCAT(bpas_loan_borrowers.last_name,' ',bpas_loan_borrowers.first_name,' [ <small style=color:#FF5454>',bpas_loan_borrowers.phone,'</small> ] ') as borrower,
				loan_items.payment,
				loan_items.interest, 
				loan_items.principal,
				loan_items.balance,
				SUM(IFNULL(bpas_payments.amount,0)) + SUM(IFNULL(bpas_payments.interest_paid,0)) as payment_paid,
				SUM(IFNULL(bpas_payments.interest_paid,0)) as interest_paid,
				SUM(IFNULL(bpas_payments.amount,0)) as principal_paid,
				SUM(IFNULL(bpas_payments.penalty_paid,0)) as penalty_paid,
				loans.currency,
				DATEDIFF(SYSDATE(),deadline) AS overdue,
				loan_items.status")
            ->from('loan_items')
			->join('loans', 'loan_items.loan_id=loans.id', 'left')
			->join('loan_borrowers','loan_borrowers.id=loans.borrower_id','left')
			->join('payments', 'payments.loan_item_id=loan_items.id', 'left')
			->group_by('loan_items.id');
			
		$loan_alert_days = ($this->Settings->loan_alert_days?$this->Settings->loan_alert_days:0);
		$this->datatables->where('DATE_SUB('.$this->db->dbprefix('loan_items').'.`deadline`, INTERVAL '.$loan_alert_days.' DAY) <=', date("Y-m-d"));
		$this->datatables->where('loan_items.status !=','paid');
		$this->datatables->where('loan_items.status !=','payoff');
		$this->datatables->where('loans.status !=','payoff');
		$this->datatables->where('loans.status !=','completed');
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function missed_repayment_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{
			if(isset($_POST['val'])){
				$this->db->where_in('loan_items.id' , $_POST['val']);
			}
			$this->db->select("
				loan_items.id as id,
				loan_items.period,
				loans.reference_no,
				loans.borrower,
				loans.currency,
				loan_items.deadline, 
				loan_items.payment,
				loan_items.interest, 
				loan_items.principal,
				loan_items.balance,
				(SUM(IFNULL(bpas_payments.amount,0)) + SUM(IFNULL(bpas_payments.interest_paid,0))) as payment_paid,
				SUM(IFNULL(bpas_payments.interest_paid,0)) as interest_paid,
				SUM(IFNULL(bpas_payments.amount,0)) as principal_paid,
				SUM(IFNULL(bpas_payments.penalty_paid,0)) as penalty_paid,
				DATEDIFF(SYSDATE(),deadline) AS overdue,
				loan_items.status")
            ->from('loan_items')
			->join('loans', 'loan_items.loan_id=loans.id', 'left')
			->join('payments', 'payments.loan_item_id=loan_items.id', 'left')
			->group_by('loan_items.id');
			$q = $this->db->get();
			
			if(isset($_POST['val'])){
				if (!empty($q)) {
					if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $styleArray = array(
					'font'  => array(
						'bold'  => true,
						'size'  => 10,
					));
                    $row1 = 3;
					$this->excel->getActiveSheet()->getStyle('A1:S1')->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('A2:S2')->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('A3:S3')->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('A4:S4')->applyFromArray($styleArray);
					$this->excel->getActiveSheet()->getStyle('A5:S5')->applyFromArray($styleArray);
					$this->excel->setActiveSheetIndex(0);
	                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Company Name'));
	                $this->excel->getActiveSheet()->SetCellValue('A2', lang('Active Loan Missed Repayments'));
					// $this->excel->getActiveSheet()->SetCellValue('A3', lang('Report Period'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang($this->Settings->site_name));
					// $this->excel->getActiveSheet()->SetCellValue('B3', lang(($start_date ? $start_date : '') . ' - ' . ($end_date ? $end_date : '')));
                    $this->excel->getActiveSheet()->setTitle(lang('loans'));
                    $this->excel->getActiveSheet()->SetCellValue('A'.$row1 , lang('#'));
                    $this->excel->getActiveSheet()->SetCellValue('B'.$row1 , lang('deadline'));
					$this->excel->getActiveSheet()->SetCellValue('C'.$row1 , lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D'.$row1 , lang('borrower'));
                    $this->excel->getActiveSheet()->SetCellValue('E'.$row1 , lang('payment'));
                    $this->excel->getActiveSheet()->SetCellValue('F'.$row1 , lang('interest'));
                    $this->excel->getActiveSheet()->SetCellValue('G'.$row1 , lang('principal'));
					$this->excel->getActiveSheet()->SetCellValue('H'.$row1 , lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I'.$row1 , lang('payment_paid'));
					$this->excel->getActiveSheet()->SetCellValue('J'.$row1 , lang('interest_paid'));
					$this->excel->getActiveSheet()->SetCellValue('K'.$row1 , lang('principal_paid'));
					$this->excel->getActiveSheet()->SetCellValue('L'.$row1 , lang('penalty_paid'));
					$this->excel->getActiveSheet()->SetCellValue('M'.$row1 , lang('overdue'));
					$this->excel->getActiveSheet()->SetCellValue('N'.$row1 , lang('currency'));
					$this->excel->getActiveSheet()->SetCellValue('O'.$row1 , lang('status'));
                    // foreach ($q->result() as $loan){
                    	$n = 0;
					$m = 0;
					$currencies = $this->site->getAllCurrencies();
					foreach ($currencies as $currency) {
						$total_payment = 0;
						$total_interest = 0;
						$total_principal = 0;
						$total_balance = 0;

		                $principal_paid = 0;
						$interest_paid = 0;
						$payment_paid = 0;
						$penalty_paid = 0;
	                	$row = 4 + $n + $m;
	                	$m++;
		                // foreach ($data as $data_row){
	                	foreach ($q->result() as $loan){
		                	if($currency->code == $loan->currency){
		                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $loan->period);
								$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($loan->deadline));
		                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $loan->reference_no);
		                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $loan->borrower);
		                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $loan->payment);
		                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $loan->interest);
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $loan->principal);
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $loan->balance);
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $loan->payment_paid);
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $loan->interest_paid);
								$this->excel->getActiveSheet()->SetCellValue('K' . $row, $loan->principal_paid);
								$this->excel->getActiveSheet()->SetCellValue('L' . $row, $loan->penalty_paid);
								$this->excel->getActiveSheet()->SetCellValue('M' . $row, $loan->overdue);
								$this->excel->getActiveSheet()->SetCellValue('N' . $row, $loan->currency);
								$this->excel->getActiveSheet()->SetCellValue('O' . $row, $loan->status);
								$total_payment += $loan->payment;
								$total_interest += $loan->interest;
								$total_principal += $loan->principal;
								$total_balance += $loan->balance;

								$principal_paid += $loan->principal_paid;
								$interest_paid += $loan->interest_paid;
								$payment_paid += $loan->payment_paid;
								$penalty_paid += $loan->penalty_paid;
		                        $row++;
                    			$n++;
			                    }
			                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, lang("subtotal"));
			                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($total_payment));
								$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($total_interest));
								$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($total_principal));
								$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($total_balance));
								$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($principal_paid));
								$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($interest_paid));
								$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($payment_paid));
								$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($penalty_paid));
			                }
			            }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'missed_repayment_' . date('Y_m_d_H_i_s');
					$this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
			}
            } else {
                $this->session->set_flashdata('error', lang("no_missed_repayment_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function getBorrowerLoans($id = null)
	{
	   $view_loan_link = anchor('admin/loans/view/$1', '<i class="fa fa-eye"></i> ' .lang('loan_details'),' class="view-loan"');
	   $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_loan_link.'</li>
					</ul>
				</div></div>';
				
       $this->load->library('datatables');
       $this->datatables
            ->select("
				loans.id as id,
				loans.date,
				loans.reference_no,
				IFNULL(principal_amount, loan_items.principal) as principal,
				loan_items.interest,
				loan_items.payment,
				(principal_paid + interest_paid) as paid,
				(payment - IFNULL(principal_paid + interest_paid, 0)) as balance,
				loans.payment_date,
				loans.status")
            ->from("loans")
			->join('(SELECT 
							loan_id,
							IFNULL(SUM(payment),0) AS payment,
							IFNULL(SUM(interest),0) AS interest,
							IFNULL(SUM(principal),0) AS principal
						FROM
							'.$this->db->dbprefix('loan_items').'
						GROUP BY loan_id) as loan_items', 'loan_items.loan_id=loans.id', 'left')
			->join('(SELECT 
							loan_id,
							IFNULL(SUM(amount),0) AS principal_paid,
							IFNULL(SUM(interest_paid),0) AS interest_paid
						FROM
							'.$this->db->dbprefix('payments').'
						GROUP BY loan_id) as payments', 'payments.loan_id=loans.id', 'left')
			->where("borrower_id", $id);
        $this->datatables->add_column("Actions", $action, "id");
		$this->datatables->unset_column("id");
        echo $this->datatables->generate();
	}
	
	public function borrower_loan_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			
			$this->db
            ->select("
				loans.id as id,
				loans.date,
				loans.reference_no,
				IFNULL(principal_amount, loan_items.principal) as principal,
				loan_items.interest,
				loan_items.payment,
				(principal_paid + interest_paid) as paid,
				(payment - IFNULL(principal_paid + interest_paid, 0)) as balance,
				loans.payment_date,
				loans.status")
            ->from("loans")
			->join('(SELECT 
							loan_id,
							IFNULL(SUM(payment),0) AS payment,
							IFNULL(SUM(interest),0) AS interest,
							IFNULL(SUM(principal),0) AS principal
						FROM
							'.$this->db->dbprefix('loan_items').'
						GROUP BY loan_id) as loan_items', 'loan_items.loan_id=loans.id', 'left')
			->join('(SELECT 
							loan_id,
							IFNULL(SUM(amount),0) AS principal_paid,
							IFNULL(SUM(interest_paid),0) AS interest_paid
						FROM
							'.$this->db->dbprefix('payments').'
						GROUP BY loan_id) as payments', 'payments.loan_id=loans.id', 'left')
			->where("borrower_id", $id);
			
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
				$this->excel->getActiveSheet()->setTitle(lang('borrower_loans'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('principal'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('interest'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('payment'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('first_payment_date'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->principal);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->interest);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->payment);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->paid);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->balance);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->hrsd($data_row->payment_date));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->status);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'borrower_loans_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function getBorrowerPayments($id = null)
	{
		$this->load->library('datatables');
		$this->datatables
			->select("
				payments.date,
				loans.reference_no as loan_reference_no,
				payments.reference_no,
				(bpas_payments.amount) + (bpas_payments.interest_paid) as payment_paid,
				payments.interest_paid,
				payments.amount as principal_paid,
				payments.penalty_paid,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				payments.paid_by,
				payments.type,
				payments.id
				")
			->from('payments')
			->join('loans', 'loans.id=payments.loan_id', 'left')
			->join('loan_items', 'loan_items.id=payments.loan_item_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->where("loans.borrower_id",$id);
			
		echo $this->datatables->generate();
	}
	
	public function borrower_payment_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db->select("
				payments.date,
				loans.reference_no as loan_reference_no,
				payments.reference_no,
				(bpas_payments.amount) + (bpas_payments.interest_paid) as payment_paid,
				payments.interest_paid,
				payments.amount as principal_paid,
				payments.penalty_paid,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				payments.paid_by,
				payments.type")
			->from('payments')
			->join('loans', 'loans.id=payments.loan_id', 'left')
			->join('loan_items', 'loan_items.id=payments.loan_item_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->where("loans.borrower_id",$id);
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
				$this->excel->getActiveSheet()->setTitle(lang('transactions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('loan_reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('payment_paid'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('interest_paid'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('principal_paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('penalty_paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('type'));
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->loan_reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->payment_paid);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->interest_paid);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->principal_paid);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->penalty_paid);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->paid_by);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->type);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'borrower_payments_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function loan_products()
    {
		$this->bpas->checkPermissions('loan_products');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loan')), array('link' => '#', 'page' => lang('loan_products')));
        $meta = array('page_title' => lang('loan_products'), 'bc' => $bc);
        $this->page_construct('loans/loan_products', $meta, $this->data);
    }
	
	public function getLoanProducts()
    {
        $this->bpas->checkPermissions("loan_products");
        $this->load->library('datatables');
		$edit_loan_product_link = anchor('admin/loans/edit_loan_product/$1', '<i class="fa fa-edit"></i> ' .lang('edit_loan_product'),' class="edit-loan_product"');
		$delete_loan_product_link = "<a href='#' class='po delete-loan_product' title='<b>" . lang("delete_loan_product") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_loan_product/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_loan_product') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$edit_loan_product_link . '</li>
						<li>'.$delete_loan_product_link . '</li>
					</ul>
				</div></div>';
				
        $this->datatables
            ->select("
				id,
				name,
				CONCAT(interest_rate_min,' - ', interest_rate_max) as interest_rate,
				CONCAT(term_min,' - ', term_max) as term,
				frequency,
				CONCAT(principal_amount_min, ' - ', currency, ' - ', principal_amount_max, ' - ', currency) as principal_amount
				")
            ->from("loan_products");
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function loan_product_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('id' , $_POST['val']);
			}
			$this->db
				->select("
					id,
					name,
					CONCAT(interest_rate_min,' - ', interest_rate_max) as interest_rate,
					CONCAT(term_min,' - ', term_max) as term,
					frequency,
					CONCAT(principal_amount_min,' - ', principal_amount_max) as principal_amount")
				->from("loan_products");
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('loan_products');
                    foreach ($_POST['val'] as $id) {
                        $this->loans_model->deleteLoanProduct($id);
                    }
                    $this->session->set_flashdata('message', lang("loan_products_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('loan_products'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('interest_rate'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('term'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('frequency'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('principal_amount'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1:E1")->applyFromArray($style)
													  ->getFont()
													  ->setBold(true);
						$row = 2;
						foreach ($q->result() as $loan_product){
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $loan_product->name);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $loan_product->interest_rate);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $loan_product->term);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $loan_product->frequency);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $loan_product->principal_amount);
							$style = array(
									'alignment' => array(
										'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
									)
								);
							$this->excel->getActiveSheet()->getStyle("B".$row.":E".$row."")->applyFromArray($style);
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
						$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
						$filename = 'loan_products_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_loan_product_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function add_loan_product()
	{
		$this->bpas->checkPermissions("loan_products");
		$this->form_validation->set_rules('name', lang("name"), 'required|trim|is_unique[loan_products.name]');
		$this->form_validation->set_rules('frequency', lang("frequency"), 'required');
		if ($this->form_validation->run() == true) {
			$name = $this->input->post('name',true);
			$grace_interest_charge = $this->input->post('grace_interest_charge',true);
			$interest_rate_default = $this->input->post('interest_rate_default',true);
			$principal_amount_default = $this->input->post('principal_amount_default',true);
			$term_default = $this->input->post('term_default',true);
			$interest_rate_min = $this->input->post('interest_rate_min',true);
			$principal_amount_min = $this->input->post('principal_amount_min',true);
			$term_min = $this->input->post('term_min',true);
			$interest_rate_max = $this->input->post('interest_rate_max',true);
			$principal_amount_max = $this->input->post('principal_amount_max',true);
			$term_max = $this->input->post('term_max',true);
			$frequency = $this->input->post('frequency',true);
			$interest_period = $this->input->post('interest_period',true);
			$interest_method = $this->input->post('interest_method',true);
			$late_repayment_penalty_recurring = $this->input->post('late_repayment_penalty_recurring',true);
			$late_repayment_penalty_calculate = $this->input->post('late_repayment_penalty_calculate',true);
			$late_repayment_penalty_amount = $this->input->post('late_repayment_penalty_amount',true);
			$late_repayment_penalty_period = $this->input->post('late_repayment_penalty_period',true);
			$charge_ids = $this->input->post('charge_ids',true);
			$currency = $this->input->post('currency',true);
			if ($principal_amount_min > $principal_amount_max) {
				$this->session->set_flashdata('error', lang("must_less_than_principal_amount_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if ($interest_rate_min > $interest_rate_max) {
				$this->session->set_flashdata('error', lang("must_less_than_interest_rate_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if ($term_min > $term_max) {
				$this->session->set_flashdata('error', lang("must_less_than_term_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$data = array(
                'name' => $name,
                'currency' => $currency,
				'grace_interest_charge' => $grace_interest_charge,
				'interest_rate_default' => $interest_rate_default,
				'principal_amount_default' => $principal_amount_default,
				'term_default' => $term_default,
                'interest_rate_min' => $interest_rate_min,
				'principal_amount_min' => $principal_amount_min,
				'term_min' => $term_min,
				'interest_rate_max' => $interest_rate_max,
				'principal_amount_max' => $principal_amount_max,
				'term_max' => $term_max,
				'frequency' => $frequency,
				'interest_period' => $interest_period,
				'interest_method' => $interest_method,
				'late_repayment_penalty_recurring' => $late_repayment_penalty_recurring,
				'late_repayment_penalty_calculate' => $late_repayment_penalty_calculate,
				'late_repayment_penalty_amount' => $late_repayment_penalty_amount,
				'late_repayment_penalty_period' => $late_repayment_penalty_period,
				'charge_ids' => json_encode($charge_ids)
            );
			if($this->Settings->accounting==1){
				$data['default_bank_account'] = $this->input->post('default_bank_account',true);
				$data['loan_portfolio_account'] = $this->input->post('loan_portfolio',true);
				$data['interest_receivable_account'] = $this->input->post('interest_receivable',true);
				$data['interest_income_account'] = $this->input->post('interest_income',true);
				$data['penalty_income_account'] = $this->input->post('penalty_income',true);
			}
			
        } elseif ($this->input->post('add_loan_product')) {
            $this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->addLoanProduct($data)) {
            $this->session->set_flashdata('message', lang("loan_product_added"));
            admin_redirect('loans/loan_products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($this->Settings->accounting == 1){
				$this->data['default_bank_account'] = $this->site->getAccount('','','1');
				$this->data['loan_portfolio'] = $this->site->getAccount(array('AS'));
				$this->data['interest_receivable'] = $this->site->getAccount(array('AS'));
				$this->data['penalty_income'] = $this->site->getAccount(array('OI'));
				$this->data['interest_income'] = $this->site->getAccount(array('RE'));
			}

			$this->data['interest_method'] = $this->site->getAllInterestmethod();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$this->data['currencies'] = $this->site->getAllCurrencies(); 
			$this->data['loan_products'] = $this->loans_model->getLoanProducts(); 
			$this->data['frequencies'] = $this->loans_model->getFrequencies();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('add_loan_product')));
			$meta = array('page_title' => lang('add_loan_product'), 'bc' => $bc);
			$this->page_construct('loans/add_loan_product', $meta, $this->data);
        }
	}
	
	public function delete_loan_product($id = null)
    {
        $this->bpas->checkPermissions("loan_products");
        if ($this->loans_model->deleteLoanProduct($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("loan_product_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("loan_product_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function edit_loan_product($id = null)
	{
		$this->bpas->checkPermissions("loan_products");
		$name = $this->input->post('name',true);
		$loan_product = $this->loans_model->getLoanProductByID($id);
		$form_name = '';
		if($loan_product->name != $name){
			$form_name = '|trim|is_unique[loan_products.name]';
		}
		$this->form_validation->set_rules('name', lang("name"), 'required'.$form_name);
		$this->form_validation->set_rules('frequency', lang("frequency"), 'required');
		if ($this->form_validation->run() == true) {
			$grace_interest_charge = $this->input->post('grace_interest_charge',true);
			$interest_rate_default = $this->input->post('interest_rate_default',true);
			$principal_amount_default = $this->input->post('principal_amount_default',true);
			$term_default = $this->input->post('term_default',true);
			$interest_rate_min = $this->input->post('interest_rate_min',true);
			$principal_amount_min = $this->input->post('principal_amount_min',true);
			$term_min = $this->input->post('term_min',true);
			$interest_rate_max = $this->input->post('interest_rate_max',true);
			$principal_amount_max = $this->input->post('principal_amount_max',true);
			$term_max = $this->input->post('term_max',true);
			$currency = $this->input->post("currency", true);
			$frequency = $this->input->post('frequency',true);
			$interest_period = $this->input->post('interest_period',true);
			$interest_method = $this->input->post('interest_method',true);
			$late_repayment_penalty_recurring = $this->input->post('late_repayment_penalty_recurring',true);
			$late_repayment_penalty_calculate = $this->input->post('late_repayment_penalty_calculate',true);
			$late_repayment_penalty_amount = $this->input->post('late_repayment_penalty_amount',true);
			$late_repayment_penalty_period = $this->input->post('late_repayment_penalty_period',true);
			$charge_ids = $this->input->post('charge_ids',true);
			if ($principal_amount_min > $principal_amount_max) {
				$this->session->set_flashdata('error', lang("must_less_than_principal_amount_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if ($interest_rate_min > $interest_rate_max) {
				$this->session->set_flashdata('error', lang("must_less_than_interest_rate_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if ($term_min > $term_max) {
				$this->session->set_flashdata('error', lang("must_less_than_term_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$data = array(
                'name' => $name,
				'grace_interest_charge' => $grace_interest_charge,
				'interest_rate_default' => $interest_rate_default,
				"currency" => $currency,
				'principal_amount_default' => $principal_amount_default,
				'term_default' => $term_default,
                'interest_rate_min' => $interest_rate_min,
				'principal_amount_min' => $principal_amount_min,
				'term_min' => $term_min,
				'interest_rate_max' => $interest_rate_max,
				'principal_amount_max' => $principal_amount_max,
				'term_max' => $term_max,
				'frequency' => $frequency,
				'interest_period' => $interest_period,
				'interest_method' => $interest_method,
				'late_repayment_penalty_recurring' => $late_repayment_penalty_recurring,
				'late_repayment_penalty_calculate' => $late_repayment_penalty_calculate,
				'late_repayment_penalty_amount' => $late_repayment_penalty_amount,
				'late_repayment_penalty_period' => $late_repayment_penalty_period,
				'charge_ids' => $charge_ids?json_encode($charge_ids):null,
            );
			if($this->Settings->accounting==1){
				$data['default_bank_account'] = $this->input->post('default_bank_account',true);
				$data['loan_portfolio_account'] = $this->input->post('loan_portfolio',true);
				$data['interest_receivable_account'] = $this->input->post('interest_receivable',true);
				$data['interest_income_account'] = $this->input->post('interest_income',true);
				$data['penalty_income_account'] = $this->input->post('penalty_income',true);
			}
			
        } elseif ($this->input->post('edit_loan_product')) {
            $this->session->set_flashdata('error', validation_errors());
           admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateLoanProduct($id,$data)) {
            $this->session->set_flashdata('message', lang("loan_product_updated"));
			 admin_redirect('loans/loan_products');
        } else {
			$pr = array();
			$items = $this->loans_model->getLoanChargeByIDs(json_decode($loan_product->charge_ids));
			if($items){
				foreach($items as $item){
					$charge_types = lang('charge_types');
					$charge_calculates = lang('charge_calculates');
					$type = $charge_types[$item->type];
					$calculate = $charge_calculates[$item->calculate];
					if($item->calculate==0){
						$amount = $this->bpas->formatDecimal($item->amount);
					}else{
						$amount = $item->amount."%";
					}
					$pr[$item->id] = array('id' => $item->id, 'label' => $item->name, 'type' => $type, 'calculate'=> $calculate, 'amount' => $amount);
				}
			}
			if($this->Settings->accounting == 1){
				$this->data['default_bank_account'] = $this->site->getAccount('',$loan_product->default_bank_account,'1');
				$this->data['loan_portfolio'] = $this->site->getAccount(array('AS'), $loan_product->loan_portfolio_account);
				$this->data['interest_receivable'] = $this->site->getAccount(array('AS'), $loan_product->interest_receivable_account);
				$this->data['penalty_income'] = $this->site->getAccount(array('OI'), $loan_product->penalty_income_account);
				$this->data['interest_income'] = $this->site->getAccount(array('RE'), $loan_product->interest_income_account);
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['items'] = json_encode($pr);
			$this->data['currencies'] = $this->site->getAllCurrencies(); 
			$this->data['loan_product'] = $loan_product;

			$this->data['loan_products'] = $this->loans_model->getLoanProducts();
			$this->data['frequencies'] = $this->loans_model->getFrequencies();
			$this->data['interest_method'] = $this->site->getAllInterestmethod();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$this->data['charges'] = $this->loans_model->getLoanChargeByIDs(json_decode($loan_product->charge_ids));
		
			$this->data['modal_js'] = $this->site->modal_js();			
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('edit_loan_product')));
			$meta = array('page_title' => lang('edit_loan_product'), 'bc' => $bc);
			$this->page_construct('loans/edit_loan_product', $meta, $this->data);
        }
	}
	
	public function get_loan_product()
	{
		$id = $this->input->get("id");
		if($id){
			$loan = $this->loans_model->getLoanProductByID($id);
			echo json_encode($loan);
		}
	}
	
	public function charges()
    {
		$this->bpas->checkPermissions('charges');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loan')), array('link' => '#', 'page' => lang('charges')));
        $meta = array('page_title' => lang('charges'), 'bc' => $bc);
        $this->page_construct('loans/charges', $meta, $this->data);
    }
	
	public function getCharges()
    {
        $this->bpas->checkPermissions("charges");
        $this->load->library('datatables');
		$edit_charge_link = anchor('admin/loans/edit_charge/$1', '<i class="fa fa-edit"></i> ' .lang('edit_charge'),' class="edit-charge" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_charge_link = "<a href='#' class='po delete-charge_type' title='<b>" . lang("delete_charge") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_charge/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_charge') . "</a>";
			
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$edit_charge_link . '</li>
						<li>'.$delete_charge_link . '</li>
					</ul>
				</div></div>';
				
        $this->datatables
            ->select("
				loan_charges.id as id,
				loan_charges.name,
				loan_charges.type,
				loan_charges.calculate,
				IF(calculate=0,amount, concat(amount,'%')) as amount")
            ->from("loan_charges");
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function delete_charge($id = null)
    {
        $this->bpas->checkPermissions("charges");
        if ($this->loans_model->deleteCharge($id, $data)) {
			if ($this->input->is_ajax_request()) {
				echo lang("charge_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("charge_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function add_charge()
	{
		$this->bpas->checkPermissions("charges");
		$this->form_validation->set_rules('name', lang("name"), 'required|trim|is_unique[loan_charges.name]');
		if ($this->form_validation->run() == true) {
			$name = $this->input->post('name',true);
			$type = $this->input->post('type',true);
			$calculate = $this->input->post('calculate', true);
			$amount = $this->input->post('amount', true);
			$data = array(
                'name' => $name,
                'type' => $type,
				'calculate' => $calculate,
				'amount' => $amount,
            );
			if($this->Settings->accounting==1){
				$data['fee_income_account'] = $this->input->post('fee_income',true);
			}
        } elseif ($this->input->post('add_charge')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->addCharge($data)) {
            $this->session->set_flashdata('message', lang("charge_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			if($this->Settings->accounting == 1){
				$this->data['fee_income'] = $this->site->getAccount(array('OI'));
			}
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/add_charge', $this->data);
        }
	}
	
	public function edit_charge($id = null)
	{
		$this->bpas->checkPermissions("charges");
		$name = $this->input->post('name',true);
		$charge = $this->loans_model->getChargeByID($id);
		$form_name = '';
		if($charge->name != $name){
			$form_name .='|is_unique[loan_charges.name]';
		}
		$this->form_validation->set_rules('name', lang("name"), 'required|trim'.$form_name);
		if ($this->form_validation->run() == true) {
			$type = $this->input->post('type',true);
			$calculate = $this->input->post('calculate', true);
			$amount = $this->input->post('amount', true);
			$data = array(
                'name' => $name,
                'type' => $type,
				'calculate' => $calculate,
				'amount' => $amount,
            );
			if($this->Settings->accounting==1){
				$data['fee_income_account'] = $this->input->post('fee_income',true);
			}
        } elseif ($this->input->post('edit_charge')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateCharge($id, $data)) {
            $this->session->set_flashdata('message', lang("charge_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
			if($this->Settings->accounting == 1){
				$this->data['fee_income'] = $this->site->getAccount(array('OI'), $charge->fee_income_account);
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['charge'] = $charge;
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/edit_charge', $this->data);
        }
	}
	
	public function charge_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('loan_charges.id' , $_POST['val']);
			}
			$this->db
				->select("
					loan_charges.id as id,
					loan_charges.name,
					loan_charges.type,
					loan_charges.calculate,
					loan_charges.amount")
				->from("loan_charges");
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('charges');
                    foreach ($_POST['val'] as $id) {
                        $this->loans_model->deleteCharge($id);
                    }
                    $this->session->set_flashdata('message', lang("charges_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('charges'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('type'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('calculate'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1:D1")->applyFromArray($style)->getFont()->setBold(true);
						$row = 2;
						foreach ($q->result() as $charge){
							$charge_types = lang("charge_types");
							$charge_calculates = lang("charge_calculates");
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $charge->name);
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $charge_types[$charge->type]);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $charge_calculates[$charge->calculate]);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($charge->amount));
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
						$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
						$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
						$filename = 'charges_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_repayment_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $rows = $this->loans_model->getChargeNames($term);
        if ($rows) {
            foreach ($rows as $row) {
				$charge_types = lang('charge_types');
				$charge_calculates = lang('charge_calculates');
				$type = $charge_types[$row->type];
				$calculate = $charge_calculates[$row->calculate];
				if($row->calculate==0){
					$amount = $this->bpas->formatDecimal($row->amount);
				}else{
					$amount = $row->amount."%";
				}
				$pr[] = array('id' => $row->id, 'label' => $row->name, 'type' => $type, 'calculate'=> $calculate, 'amount' => $amount);
			}
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	
	public function calculator()
	{
		$this->data['loan_products'] = $this->loans_model->getLoanProducts(); 
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['frequencies'] = $this->loans_model->getFrequencies();
		$this->data['interest_period'] = $this->site->getAllInterestperiod();
		$this->data['interest_method'] = $this->site->getAllInterestmethod();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('calculator')));
		$meta = array('page_title' => lang('calculator'), 'bc' => $bc);
		$this->page_construct('loans/calculator', $meta, $this->data);
	}
	
	public function working_status()
    {
		$this->bpas->checkPermissions('working_status');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('working_status')));
        $meta = array('page_title' => lang('working_status'), 'bc' => $bc);
        $this->page_construct('loans/working_status', $meta, $this->data);
    }
	
	public function getWorkingStatus()
    {
        $this->bpas->checkPermissions("working_status");
        $this->load->library('datatables');
		$edit_working_status_link = anchor('admin/loans/edit_working_status/$1', '<i class="fa fa-edit"></i> ' .lang('edit_working_status'),' class="edit-working_status" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_working_status_link = "<a href='#' class='po delete-working_status' title='<b>" . lang("delete_working_status") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_working_status/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_working_status') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$edit_working_status_link . '</li>
						<li>'.$delete_working_status_link . '</li>
					</ul>
				</div></div>';
        $this->datatables
            ->select("loan_working_status.id as id,
					loan_working_status.name")
            ->from("loan_working_status");
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function add_working_status()
	{
		$this->bpas->checkPermissions("working_status");
		$this->form_validation->set_rules('name', lang("name"), 'required|trim|is_unique[loan_working_status.name]');
		if ($this->form_validation->run() == true) {
			$name = $this->input->post('name',true);
			$data = array(
                'name' => $name,
            );
        } elseif ($this->input->post('add_working_status')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->addWorkingStatus($data)) {
            $this->session->set_flashdata('message', lang("working_status_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/add_working_status', $this->data);
        }
	}
	
	public function delete_working_status($id = null)
    {
        $this->bpas->checkPermissions("working_status");
        if ($this->loans_model->deleteWorkingStatus($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("working_status_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("working_status_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function working_status_actions()
	{
		 if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{	
			if(isset($_POST['val'])){
				$this->db->where_in('loan_working_status.id' , $_POST['val']);
			}
			$this->db
					->select("
						loan_working_status.id as id,
						loan_working_status.name")
					->from("loan_working_status");
			
			$q = $this->db->get();
			if(isset($_POST['val'])){
				
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('working_status');
                    foreach ($_POST['val'] as $id) {
                        $this->loans_model->deleteWorkingStatus($id);
                    }
                    $this->session->set_flashdata('message', lang("working_status_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);

                }else if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('working_status'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1")->applyFromArray($style)
													  ->getFont()
													  ->setBold(true);
						$row = 2;
						foreach ($q->result() as $working_status){
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $working_status->name);
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
						$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
						$filename = 'working_status_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_repayment_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function edit_working_status($id = NULL)
	{
		$this->bpas->checkPermissions("working_status");
		$name = $this->input->post('name',true);
		$working_status = $this->loans_model->getWorkingStatusByID($id);
		$form_name = '';
		if($working_status->name != $name){
			$form_name = '|trim|is_unique[loan_working_status.name]';
		}
		$this->form_validation->set_rules('name', lang("name"), 'required'.$form_name);
		if ($this->form_validation->run() == true) {
			$data = array(
                'name' => $name,
            );
        } elseif ($this->input->post('edit_working_status')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateWorkingStatus($id, $data)) {
            $this->session->set_flashdata('message', lang("working_status_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['working_status'] = $working_status;
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/edit_working_status', $this->data);
        }
	}

	public function getBorrowerRef($field) 
	{
        $q = $this->db->get('order_ref');
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
				case 'borrower':
                    $prefix = $this->Settings->borrower_prefix;
                    break;	
                default:
                    $prefix = '';
            }
            $ref_no = (!empty($prefix)) ? $prefix . '/' : '';
            $ref_no .= sprintf("%04s", $ref->{$field});
		//	$this->updateBorrowerRef($field);
            return $ref_no;
        }
        return FALSE;
    }

	public function updateBorrowerRef($field, $bill_id = null) 
	{
        $q = $this->db->get('order_ref');
        if ($q->num_rows() > 0) {
            $ref = $q->row();
			$this->db->update('order_ref', array($field => $ref->{$field} + 1));
            return TRUE;
        }
        return FALSE;
    }
	
	public function get_loan()
	{
		$borrower = $this->input->get("borrower");
		$row = $this->loans_model->getLoanByBorrower($borrower);
        $this->bpas->send_json($row);
	}
	
	public function applications($biller_id = NULL)
	{
		$this->bpas->checkPermissions("applications-index");
		if($biller_id == 0){
			$biller_id = null;
		}
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loan')), array('link' => '#', 'page' => lang('applications')));
		$meta = array('page_title' => lang('applications'), 'bc' => $bc);
        $this->page_construct('loans/applications', $meta, $this->data);
	}
	
	public function getApplications($biller_id = NULL)
    {
		$this->bpas->checkPermissions("applications-index");
        $this->load->library('datatables');
	    $view_application_link = anchor('admin/loans/view_application/$1', '<i class="fa fa-file-text-o"></i> ' .lang('application_details'),' class="view-application"');
	    $edit_application_link = anchor('admin/loans/edit_application/$1', '<i class="fa fa-edit"></i> ' .lang('edit_application'),' class="edit-application"');
	    $delete_application_link = "<a href='#' class='po delete-application' title='<b>" . lang("delete_application") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('loans/delete_application/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_application') . "</a>";
		
		$approve_application_link = "<a href='#' class='po approve-application' title='<b>" . lang("approve_application") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('loans/approve_application/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-edit\"></i> "
			. lang('approve_application') . "</a>";
		
		$decline_application_link = "<a href='#' class='po decline-application' title='<b>" . lang("decline_application") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('loans/decline_application/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-edit\"></i> "
			. lang('decline_application') . "</a>";
		
		$add_disburse_link = anchor('admin/loans/add_disburse/$1', '<i class="fa fa-file-text-o"></i> ' .lang('add_disburse'),' class="add-disburse"');
		
	   $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_application_link.'</li>
						<li>'.$edit_application_link . '</li>
						<li>'.$approve_application_link . '</li>
						<li>'.$decline_application_link . '</li>
						<li>'.$add_disburse_link . '</li>
						<li>'.$delete_application_link . '</li>
					</ul>
				</div></div>';
				
        $this->datatables
            ->select("
				loan_applications.id as id,
				loan_applications.date,
				loan_applications.reference_no,
				loan_borrowers.code,
				loan_applications.borrower,
				CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
				loan_products.name as loan_product,
				loan_applications.principal_amount,
				loan_applications.currency,
				concat(bpas_loan_officers.last_name,' ',bpas_loan_officers.first_name) as loan_officer,
				concat(bpas_tellers.last_name,' ',bpas_tellers.first_name) as teller,
				loan_applications.status")
            ->from("loan_applications")
			->join("loan_borrowers","loan_borrowers.id=borrower_id","left")
			->join("loan_products","loan_products.id=loan_product_id","left")
			->join("users as bpas_loan_officers","bpas_loan_officers.id=loan_officer_id","left")
			->join("users as bpas_tellers","bpas_tellers.id=teller_id","left")
			->where("loan_applications.status !=", "completed");
			
			if ($biller_id) {
				$this->datatables->where('loan_applications.biller_id', $biller_id);
			}
			
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('loan_applications.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('loan_applications.biller_id',$this->session->userdata('biller_id'));
			}
			
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function application_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true)
		{
			if(isset($_POST['val'])){
				$this->db->where_in('loan_applications.id' , $_POST['val']);
			}
			$this->db
				->select("
				loan_applications.id as id,
				loan_applications.date,
				loan_applications.reference_no,
				loan_applications.currency,
				loan_borrowers.code,
				loan_applications.borrower,
				CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
				loan_products.name as loan_product,
				loan_applications.principal_amount,
				concat(bpas_loan_officers.last_name,' ',bpas_loan_officers.first_name) as loan_officer,
				concat(bpas_tellers.last_name,' ',bpas_tellers.first_name) as teller,
				loan_applications.status")
            ->from("loan_applications")
			->join("loan_borrowers","loan_borrowers.id=borrower_id","left")
			->join("loan_products","loan_products.id=loan_product_id","left")
			->join("users as bpas_loan_officers","bpas_loan_officers.id=loan_officer_id","left")
			->join("users as bpas_tellers","bpas_tellers.id=teller_id","left")
			->where("loan_applications.status !=", "completed");
			
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->loans_model->deleteApplication($id);
                    }
                    $this->session->set_flashdata('message', lang("applications_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
						$this->load->library('excel');
						$this->excel->setActiveSheetIndex(0);
						$this->excel->getActiveSheet()->setTitle(lang('applications'));
						$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
						$this->excel->getActiveSheet()->SetCellValue('B1', lang('application_no'));
						$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
						$this->excel->getActiveSheet()->SetCellValue('D1', lang('borrower'));
						$this->excel->getActiveSheet()->SetCellValue('E1', lang('gender'));
						$this->excel->getActiveSheet()->SetCellValue('F1', lang('loan_product'));
						$this->excel->getActiveSheet()->SetCellValue('G1', lang('principal_amount'));
						$this->excel->getActiveSheet()->SetCellValue('H1', lang('loan_officer'));
						$this->excel->getActiveSheet()->SetCellValue('I1', lang('teller'));
						$this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));
						$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
						$this->excel->getActiveSheet()->getStyle("A1:L1")->applyFromArray($style)->getFont()->setBold(true);
						$row = 2;
						foreach ($q->result() as $application){
							$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($application->date));
							$this->excel->getActiveSheet()->SetCellValue('B' . $row, $application->reference_no);
							$this->excel->getActiveSheet()->SetCellValue('C' . $row, $application->code);
							$this->excel->getActiveSheet()->SetCellValue('D' . $row, $application->borrower);
							$this->excel->getActiveSheet()->SetCellValue('E' . $row, $application->gender);
							$this->excel->getActiveSheet()->SetCellValue('F' . $row, $application->loan_product);
							$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatMoney($application->principal_amount));
							$this->excel->getActiveSheet()->SetCellValue('H' . $row, $application->loan_officer);
							$this->excel->getActiveSheet()->SetCellValue('I' . $row, $application->teller);
							$this->excel->getActiveSheet()->SetCellValue('J' . $row, $application->status);
							$row++;
						}
						$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
						$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
						$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
						$filename = 'applications_' . date('Y_m_d_H_i_s');
						$this->load->helper('excel');
						create_excel($this->excel, $filename);
				}
            } else {
                $this->session->set_flashdata('error', lang("no_loan_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function add_application()
    {
		$this->bpas->checkPermissions("applications-add");
		$this->form_validation->set_rules('borrower', lang("borrower"), 'required');
		$this->form_validation->set_rules('loan_product', lang("loan_product"), 'required');
		$this->form_validation->set_rules('borrower_type', lang("borrower_type"), 'required');
		$this->form_validation->set_rules('principal_amount', lang("principal_amount"), 'required');
		$this->form_validation->set_rules('interest_rate', lang("interest_rate"), 'required');
		$this->form_validation->set_rules('interest_method', lang("interest_method"), 'required');
		$this->form_validation->set_rules('interest_period', lang("interest_period"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post("biller");
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company ? $biller_details->company : $biller_details->name;
			$borrower_id = $this->input->post("borrower");
			$borrower_details = $this->loans_model->getBorrowerByID($borrower_id);
			$borrower = $borrower_details->last_name.' '.$borrower_details->first_name;
			$principal_amount = $this->input->post("principal_amount", true);
			$grace_interest_charge = $this->input->post("grace_interest_charge", true);
			$interest_rate = $this->input->post("interest_rate", true);
			$term = $this->input->post("term", true);
			$currency = $this->input->post("currency", true);
			$frequency = $this->input->post("frequency", true);
			$interest_period = $this->input->post("interest_period");
			$interest_method = $this->input->post("interest_method");
			$insurance_rate = $this->input->post("insurance_rate");
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('app',$biller_id);
			$loan_product = $this->input->post('loan_product');
			$principal_amount_max = $this->input->post("principal_amount_max",true);
			$borrower_type = $this->input->post('borrower_type');
			$loan_officer = $this->input->post('loan_officer');
			$teller = $this->input->post('teller');
			if($principal_amount > $principal_amount_max){
				$this->session->set_flashdata('error', lang("must_less_than_principal_amount_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$interest_rate_max = $this->input->post("interest_rate_max",true);
			if($interest_rate > $interest_rate_max){
				$this->session->set_flashdata('error', lang("must_less_than_interest_rate_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($insurance_rate > $interest_rate){
				$this->session->set_flashdata('error', lang("insurance_rate_must_less_than_interest_rate"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$term_max = $this->input->post("term_max",true);
			if($term > $term_max){
				$this->session->set_flashdata('error', lang("must_less_than_term_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$data = array(
				"date" => $date,
				"reference_no" => $reference_no,
				"biller_id" => $biller_id,
				"biller" => $biller,
				"borrower_id" => $borrower_id,
				"currency" => $currency,
				"borrower" => $borrower,
				"principal_amount" => $principal_amount,
				"grace_interest_charge" => $grace_interest_charge,
				"interest_rate" => $interest_rate,
				"term" => $term,
				"frequency" => $frequency,
				"interest_period" => $interest_period,
				"interest_method" => $interest_method,
				"insurance_rate" => $insurance_rate,
				"loan_product_id" => $loan_product,
				"loan_officer_id" => $loan_officer,
				"teller_id" => $teller,
				"borrower_type_id" => $borrower_type,
				"created_by" => $this->session->userdata("user_id"),
				"status" => $this->input->post('status',true)
			);
		}
		if ($this->form_validation->run() == true && $id = $this->loans_model->addApplication($data)) {
			$this->site->updateReference('app', $biller_id);
			$this->session->set_flashdata('message', lang("application_added"));
			admin_redirect("loans/applications");
		}else{
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$this->data['loan_products'] = $this->loans_model->getLoanProducts(); 
			$this->data['borrower_types'] = $this->loans_model->getAllBorrowerTypes();
			$this->data['loan_officers'] = $this->site->getAllUsers();
			$this->data['interest_method'] = $this->site->getAllInterestmethod();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['frequencies'] = $this->loans_model->getFrequencies();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('add_application')));
			$meta = array('page_title' => lang('add_application'), 'bc' => $bc);
			$this->page_construct('loans/add_application', $meta, $this->data);
		}
    }
	
	public function edit_application($id = false)
    {
		$this->bpas->checkPermissions("applications-edit");
		$application = $this->loans_model->getApplicationByID($id);
		if($application->status != 'applied'){
			$this->session->set_flashdata('error', lang("application_cannot_edit"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('borrower', lang("borrower"), 'required');
		$this->form_validation->set_rules('loan_product', lang("loan_product"), 'required');
		$this->form_validation->set_rules('borrower_type', lang("borrower_type"), 'required');
		$this->form_validation->set_rules('principal_amount', lang("principal_amount"), 'required');
		$this->form_validation->set_rules('interest_rate', lang("interest_rate"), 'required');
		$this->form_validation->set_rules('interest_method', lang("interest_method"), 'required');
		$this->form_validation->set_rules('interest_period', lang("interest_period"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post("biller");
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company ? $biller_details->company : $biller_details->name;
			$borrower_id = $this->input->post("borrower");
			$borrower_details = $this->loans_model->getBorrowerByID($borrower_id);
			$borrower = $borrower_details->last_name.' '.$borrower_details->first_name;
			$principal_amount = $this->input->post("principal_amount", true);
			$grace_interest_charge = $this->input->post("grace_interest_charge", true);
			$interest_rate = $this->input->post("interest_rate", true);
			$insurance_rate = $this->input->post("insurance_rate", true);
			$term = $this->input->post("term", true);
			$frequency = $this->input->post("frequency", true);
			$currency = $this->input->post("currency", true);
			$interest_period = $this->input->post("interest_period");
			$interest_method = $this->input->post("interest_method");
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('app',$biller_id);
			$loan_product = $this->input->post('loan_product');
			$product = $this->loans_model->getLoanProductByID($loan_product);
			$principal_amount_max = $this->input->post("principal_amount_max",true);
			$borrower_type = $this->input->post('borrower_type');
			$loan_officer = $this->input->post('loan_officer');
			$teller = $this->input->post('teller');
			if($principal_amount > $principal_amount_max){
				$this->session->set_flashdata('error', lang("must_less_than_principal_amount_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$interest_rate_max = $this->input->post("interest_rate_max",true);
			if($interest_rate > $interest_rate_max){
				$this->session->set_flashdata('error', lang("must_less_than_interest_rate_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($insurance_rate > $interest_rate){
				$this->session->set_flashdata('error', lang("insurance_rate_must_less_than_interest_rate"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$term_max = $this->input->post("term_max",true);
			if($term > $term_max){
				$this->session->set_flashdata('error', lang("must_less_than_term_max"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$data = array(
				"reference_no" 			=> $reference_no,
				"biller_id" 			=> $biller_id,
				"biller" 				=> $biller,
				"borrower_id" 			=> $borrower_id,
				"borrower" 				=> $borrower,
				"currency" 				=> ($currency ? $currency : $product->currency),
				"principal_amount" 		=> $principal_amount,
				"grace_interest_charge" => $grace_interest_charge,
				"interest_rate" 		=> $interest_rate,
				"insurance_rate" 		=> $insurance_rate,
				"term" 					=> $term,
				"frequency" 			=> $frequency,
				"interest_period" 		=> $interest_period,
				"interest_method" 		=> $interest_method,
				"loan_product_id" 		=> $loan_product,
				"teller_id" 			=> $teller,
				"loan_officer_id" 		=> $loan_officer,
				"borrower_type_id" 		=> $borrower_type,
				"updated_at" 			=> $date,
				"updated_by" 			=> $this->session->userdata("user_id"),
				"status" 				=> $this->input->post('status',true)
			);
		
		}
		if ($this->form_validation->run() == true && $this->loans_model->updateApplication($id,$data)) {
			$this->session->set_flashdata('message', lang("application_updated"));
			admin_redirect("loans/applications");
		}else{
			$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$this->data['id'] = $id;
			$this->data['application'] = $application;
			$this->data['loan_product'] = $this->loans_model->getLoanProductByID($application->loan_product_id);
			$this->data['loan_products'] = $this->loans_model->getLoanProducts();
			$this->data['borrower_types'] = $this->loans_model->getAllBorrowerTypes();
			$this->data['interest_method'] = $this->site->getAllInterestmethod();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$this->data['loan_officers'] = $this->site->getAllUsers();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['frequencies'] = $this->loans_model->getFrequencies();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('edit_application')));
			$meta = array('page_title' => lang('edit_application'), 'bc' => $bc);
			$this->page_construct('loans/edit_application', $meta, $this->data);
		}
    }
	
	public function delete_application($id = null)
    {
		$this->bpas->checkPermissions("applications-delete");
		$application = $this->loans_model->getApplicationByID($id);
		if($application->status != 'applied'){
			$this->session->set_flashdata('error', lang("application_cannot_delete"));
			$this->bpas->md();
		}
        if ($this->loans_model->deleteApplication($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("application_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("application_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function decline_application($id = NULL, $decline = FALSE)
	{
		$this->bpas->checkPermissions("applications-decline");
		$application = $this->loans_model->getApplicationByID($id);
		if($application->status != 'applied'){
			$this->session->set_flashdata('error', lang("application_cannot_decline"));
			$this->bpas->md();
		}
        if ($this->loans_model->declineApplication($id, $decline)) {
			if ($this->input->is_ajax_request()) {
				echo lang("application_declined"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("application_declined"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
	}
	
	public function approve_application($id = NULL, $approve = FALSE)
	{
		$this->bpas->checkPermissions("applications-approve");
		$application = $this->loans_model->getApplicationByID($id);
		if($application->status != 'applied'){
			$this->session->set_flashdata('error', lang("application_cannot_approve"));
			$this->bpas->md();
		}
        if ($this->loans_model->approveApplication($id, $approve)) {
			if ($this->input->is_ajax_request()) {
				echo lang("application_approved"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("application_approved"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
	}

	public function view_application($id = NULL)
	{
		$application = $this->loans_model->getApplicationByID($id);
		$borrower = $this->loans_model->getBorrowerByID($application->borrower_id);
		$borrower_type = $this->loans_model->getBorrowerTypeByID($application->borrower_type_id);
		$product = $this->loans_model->getLoanProductByID($application->loan_product_id);
		$this->data['id'] = $id;
		$this->data['borrower'] = $borrower;
		$this->data['borrower_type'] = $borrower_type;
		$this->data['application'] = $application;
		$this->data['product'] = $product;
		$this->data['country'] = $this->loans_model->getLocationByID($borrower->country_id); 
		$this->data['province'] = $this->loans_model->getLocationByID($borrower->province_id);
		$this->data['district'] = $this->loans_model->getLocationByID($borrower->district_id);
		$this->data['commune'] = $this->loans_model->getLocationByID($borrower->commune_id);
		$this->data['village'] = $this->loans_model->getLocationByID($borrower->village_id);
		$this->data['interest_method'] = $this->site->getAllInterestmethod();
		$this->data['interest_period'] = $this->site->getAllInterestperiod();
		$this->data['working'] = $this->loans_model->getWorkingStatusByID($borrower->working_status);
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('loans')), array('link' => '#', 'page' => lang('application_details')));
        $meta = array('page_title' => lang('application_details'), 'bc' => $bc);
        $this->page_construct('loans/view_application', $meta, $this->data);
	}
	
	public function getGuarantors($id = null)
    {
        $this->bpas->checkPermissions("guarantors");
        $this->load->library('datatables');
		$view_guarantor_link = anchor('admin/loans/view_guarantor/$1', '<i class="fa fa-file-text-o"></i> ' .lang('view_guarantor'),' class="view-guarantor" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_guarantor_link = anchor('admin/loans/edit_guarantor/$1', '<i class="fa fa-edit"></i> ' .lang('edit_guarantor'),' class="edit-guarantor" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_guarantor_link = "<a href='#' class='po delete-guarantor' title='<b>" . lang("delete_guarantor") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_guarantor/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_guarantor') . "</a>";
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_guarantor_link . '</li>
						<li>'.$edit_guarantor_link . '</li>
						<li>'.$delete_guarantor_link . '</li>
					</ul>
				</div></div>';
        $this->datatables
            ->select("
				loan_borrowers.id as id,
				loan_unique_types.name as unique_type,
				loan_borrowers.unique_no,
				CONCAT(bpas_loan_borrowers.last_name,' ',bpas_loan_borrowers.first_name) as name,
				CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
				loan_borrowers.phone,
				loan_borrowers.note")
            ->from("loan_borrowers")
			->join("loan_unique_types","loan_unique_types.id=unique_type_id","left")
			->join("users","users.id=loan_borrowers.created_by","left")
			->where("loan_borrowers.type","Guarantor")
			->where("application_id", $id);
			$this->datatables->unset_column("id");
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function guarantors_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db->select("
					loan_borrowers.id as id,
					loan_unique_types.name as unique_type,
					loan_borrowers.unique_no,
					CONCAT(bpas_loan_borrowers.last_name,' ',bpas_loan_borrowers.first_name) as name,
					CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
					loan_borrowers.phone,
					loan_borrowers.note")
				->from("loan_borrowers")
				->join("loan_unique_types","loan_unique_types.id=unique_type_id","left")
				->join("users","users.id=loan_borrowers.created_by","left")
				->where("loan_borrowers.type","guarantor")
				->where("application_id", $id);
				
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
				$this->excel->getActiveSheet()->setTitle(lang('guarantors'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('unique_type'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('unique_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('full_name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('gender'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
				$this->excel->getActiveSheet()->getStyle("A1:F1")->applyFromArray($style)->getFont()->setBold(true);
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->unique_type);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->unique_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->gender);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->phone);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($data_row->note));
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'guarantors_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function add_guarantor($application_id = null)
	{
		$this->bpas->checkPermissions("guarantors");
		$this->form_validation->set_rules('unique_no', lang("unique_no"), 'required|trim|is_unique[loan_borrowers.unique_no]');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		$this->form_validation->set_rules('phone', lang("phone"), 'required');
		$this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[loan_borrowers.email]');
        if ($this->form_validation->run() == true) {
			$unique_no = $this->input->post('unique_no',true);
			$unique_type = $this->input->post('unique_type',true);
			$first_name = $this->input->post('first_name',true);
			$last_name = $this->input->post('last_name',true);
			$gender = $this->input->post('gender',true);
			$phone = $this->input->post('phone',true);
			$email = $this->input->post('email',true);
			$dob = $this->input->post('dob',true);
			$working_status = $this->input->post('working_status',true);
			$marital_status = $this->input->post('marital_status',true);
			$country = $this->input->post('country',true);
			$province = $this->input->post('province',true);
			$district = $this->input->post('district',true);
			$commune = $this->input->post('commune',true);
			$village = $this->input->post('village',true);
			$street_no = $this->input->post('street_no',true);
			$home_no = $this->input->post('home_no',true);
			$address = $this->input->post('address',true);
			$note = $this->input->post('note',true);
			$data = array(
				'application_id' => $application_id,
				'type' => 'Guarantor',
				'unique_type_id' => $unique_type,
                'unique_no' => $unique_no,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'gender' => $gender,
				'phone' => $phone,
				'email' => $email,
				'dob' => $this->bpas->fld($dob),
				'country_id' => $country,
				'province_id' => $province,
				'district_id' => $district,
				'commune_id' => $commune,
				'village_id' => $village,
				'street_no' => $street_no,
                'home_no' => $home_no,
				'note' => $note,
				'address' => $address,
				'marital_status' => $marital_status,
				'working_status' => $working_status,
				'created_by' => $this->session->userdata("user_id"),
				'created_at' => date("Y-m-d H:i"),
            );
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['photo'] = $photo;
				$this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
			
			
        } elseif ($this->input->post('add_guarantor')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]."#guarantors");
        }
        if ($this->form_validation->run() == true && $this->loans_model->addBorrower($data)) {
            $this->session->set_flashdata('message', lang("guarantor_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#guarantors");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['application_id'] = $application_id;
			$this->data['unique_types'] = $this->loans_model->getUniqueTypes();
			$this->data['working_status'] = $this->loans_model->getWorkingStatus();
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/add_guarantor', $this->data);
        }
	}
	
	public function delete_guarantor($id = null)
    {
        $this->bpas->checkPermissions("guarantors");
        if ($this->loans_model->deleteBorrower($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("guarantor_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("guarantor_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function edit_guarantor($id = null)
	{
		$this->bpas->checkPermissions("guarantors");
		$unique_no = $this->input->post('unique_no',true);
		$email = $this->input->post('email',true);
		$guarantor = $this->loans_model->getBorrowerByID($id);
		$form_nric = '';
		if($guarantor->unique_no != $unique_no){
			$form_nric = "|is_unique[loan_borrowers.unique_no]";
		}
		$form_email = '';
		if($guarantor->email != $email){
			$form_email = "|is_unique[loan_borrowers.email]";
		}
		$this->form_validation->set_rules('unique_no', lang("unique_no"), 'required'.$form_nric);
		$this->form_validation->set_rules('email', lang("email"), 'trim'.$form_email);
		$this->form_validation->set_rules('phone', lang("phone"), 'required');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		
        if ($this->form_validation->run() == true) {
			$unique_no = $this->input->post('unique_no',true);
			$unique_type = $this->input->post('unique_type',true);
			$first_name = $this->input->post('first_name',true);
			$last_name = $this->input->post('last_name',true);
			$gender = $this->input->post('gender',true);
			$phone = $this->input->post('phone',true);
			$email = $this->input->post('email',true);
			$dob = $this->input->post('dob',true);
			$working_status = $this->input->post('working_status',true);
			$marital_status = $this->input->post('marital_status',true);
			$country = $this->input->post('country',true);
			$province = $this->input->post('province',true);
			$district = $this->input->post('district',true);
			$commune = $this->input->post('commune',true);
			$village = $this->input->post('village',true);
			$street_no = $this->input->post('street_no',true);
			$home_no = $this->input->post('home_no',true);
			$address = $this->input->post('address',true);
			$note = $this->input->post('note',true);
			$data = array(
                'unique_no' => $unique_no,
				'unique_type_id' => $unique_type,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'gender' => $gender,
				'phone' => $phone,
				'email' => $email,
				'dob' => $this->bpas->fld($dob),
				'country_id' => $country,
				'province_id' => $province,
				'district_id' => $district,
				'commune_id' => $commune,
				'village_id' => $village,
				'street_no' => $street_no,
                'home_no' => $home_no,
				'note' => $note,
				'address' => $address,
				'marital_status' => $marital_status,
				'working_status' => $working_status
            );
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('photo')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['photo'] = $photo;
				$this->load->library('image_lib');
                $config['image_library'] = 'gd2';
                $config['source_image'] = $this->upload_path . $photo;
                $config['new_image'] = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = TRUE;
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
			
        } elseif ($this->input->post('edit_guarantor')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]."#guarantors");
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateBorrower($id, $data)) {
            $this->session->set_flashdata('message', lang("guarantor_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#guarantors");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['id'] = $id;
			$this->data['guarantor'] = $guarantor;
			$this->data['unique_types'] = $this->loans_model->getUniqueTypes();
			$this->data['working_status'] = $this->loans_model->getWorkingStatus();
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/edit_guarantor', $this->data);
        }
	}
	
	public function view_guarantor($id = null)
	{
		$this->bpas->checkPermissions("guarantors");
		$guarantor = $this->loans_model->getBorrowerByID($id);
        $this->data['guarantor'] = $guarantor;
		$this->data['unique_type'] = $this->loans_model->getUniqueType($guarantor->unique_type_id);
		$this->data['country'] = $this->loans_model->getLocationByID($guarantor->country_id); 
		$this->data['province'] = $this->loans_model->getLocationByID($guarantor->province_id);
		$this->data['district'] = $this->loans_model->getLocationByID($guarantor->district_id);
		$this->data['commune'] = $this->loans_model->getLocationByID($guarantor->commune_id);
		$this->data['village'] = $this->loans_model->getLocationByID($guarantor->village_id);
		$this->data['working'] = $this->loans_model->getWorkingStatusByID($guarantor->working_status);
        $this->data['page_title'] = lang("view_guarantor");
        $this->load->view($this->theme . 'loans/view_guarantor', $this->data);
	}
	
	public function getCollaterals($id = null)
    {
        $this->bpas->checkPermissions("collaterals");
        $this->load->library('datatables');
		$view_collateral_link = anchor('admin/loans/view_collateral/$1', '<i class="fa fa-file-text-o"></i> ' .lang('view_guarantor'),' class="view-collateral" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_collateral_link = anchor('admin/loans/edit_collateral/$1', '<i class="fa fa-edit"></i> ' .lang('edit_collateral'),' class="edit-collateral" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$delete_collateral_link = "<a href='#' class='po delete-collateral' title='<b>" . lang("delete_collateral") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('loans/delete_collateral/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_collateral') . "</a>";
			
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_collateral_link . '</li>
						<li>'.$edit_collateral_link . '</li>
						<li>'.$delete_collateral_link . '</li>
					</ul>
				</div></div>';
				
        $this->datatables->select("
					id,
					name,
					value,
					model,
					serial_number,
					description,
					registered_date,
					registered_by,
					attachment")
            ->from("loan_collaterals")
			->where("application_id", $id);
			$this->datatables->unset_column("id");
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function add_collateral($id = null)
	{
		$this->bpas->checkPermissions("collaterals");
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('value', lang("value"), 'required');
		if ($this->form_validation->run() == true) {
			$name = $this->input->post('name',true);
			$value = $this->input->post('value',true);
			$description = $this->input->post('description',true);
			$model = $this->input->post('model',true);
			$serial_number = $this->input->post('serial_number',true);
			$registered_date = $this->input->post('registered_date',true);
			$registered_by = $this->input->post('registered_by',true);
			$data = array(
                'name' => $name,
                'value' => $value,
				'description' => $description,
				'model' => $model,
				'serial_number' => $serial_number,
				'registered_date' => $this->bpas->fld($registered_date),
				'registered_by' => $registered_by,
				'application_id' => $id,
            );
			
			if ($_FILES['attachment']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('attachment')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			
        } elseif ($this->input->post('add_collateral')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]."#collaterals");
        }
        if ($this->form_validation->run() == true && $this->loans_model->addCollateral($data)) {
            $this->session->set_flashdata('message', lang("collateral_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#collaterals");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/add_collateral', $this->data);
        }
	}
	
	public function delete_collateral($id = null)
    {
        $this->bpas->checkPermissions("collaterals");
        if ($this->loans_model->deleteCollateral($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("collateral_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("collateral_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function edit_collateral($id = null)
	{
		$this->bpas->checkPermissions("collaterals");
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('value', lang("value"), 'required');
		if ($this->form_validation->run() == true) {
			$name = $this->input->post('name',true);
			$value = $this->input->post('value',true);
			$description = $this->input->post('description',true);
			$model = $this->input->post('model',true);
			$serial_number = $this->input->post('serial_number',true);
			$registered_date = $this->input->post('registered_date',true);
			$registered_by = $this->input->post('registered_by',true);
			$data = array(
                'name' => $name,
                'value' => $value,
				'description' => $description,
				'model' => $model,
				'serial_number' => $serial_number,
				'registered_date' => $this->bpas->fld($registered_date),
				'registered_by' => $registered_by,
            );
			if ($_FILES['attachment']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('attachment')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			
        } elseif ($this->input->post('edit_collateral')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]."#collaterals");
        }
        if ($this->form_validation->run() == true && $this->loans_model->updateCollateral($id, $data)) {
            $this->session->set_flashdata('message', lang("collateral_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]."#collaterals");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['collateral'] = $this->loans_model->getCollateralByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'loans/edit_collateral', $this->data);
        }
	}
	
	public function collaterals_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$this->db->select("
						name,
						value,
						model,
						serial_number,
						description,
						registered_date,
						registered_by")
				->from("loan_collaterals")
				->where("application_id", $id);
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
				$this->excel->getActiveSheet()->setTitle(lang('collaterals'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('value'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('model'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('serial_number'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('description'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('registered_date'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('registered_by'));
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
				$this->excel->getActiveSheet()->getStyle("A1:G1")->applyFromArray($style)->getFont()->setBold(true);
				$row = 2;
				foreach ($data as $data_row){
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->value);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->model);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->serial_number);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($data_row->description));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrld($data_row->registered_date));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->registered_by);
					$row++;
				}
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$filename = 'collaterals_'.date("Y_m_d_H_i_s");
				$this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$this->load->helper('excel');
				create_excel($this->excel, $filename);

			}
			$this->session->set_flashdata('error', lang('nothing_found'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
	}
	
	public function view_collateral($id = null)
	{
		$this->bpas->checkPermissions("collaterals");
        $this->data['collateral'] = $this->loans_model->getCollateralByID($id);
        $this->data['page_title'] = lang("view_guarantor");
        $this->load->view($this->theme . 'loans/view_collateral', $this->data);
	}
	public function application_agreement($id = false)
	{
		$this->bpas->checkPermissions("applications-agreement");
		$this->data['id'] = $id;
		// $this->data['loan'] = $this->loans_model->getLoanByID($id);
		// $this->data['loan_items'] = $this->loans_model->getLoanItemsByLoanID($id);
		$this->data['application'] = $this->loans_model->getApplicationByID($this->data['id']);
		$this->data['loan_officer'] = $this->site->getUser($this->data['application']->loan_officer_id);
		$this->data['biller'] = $this->site->getCompanyByID($this->data['application']->biller_id);
		$this->data['borrower'] = $this->loans_model->getBorrowerByID($this->data['application']->borrower_id);
		$this->data['country'] = $this->loans_model->getLocationByID($this->data['borrower']->country_id); 
		$this->data['province'] = $this->loans_model->getLocationByID($this->data['borrower']->province_id);
		$this->data['district'] = $this->loans_model->getLocationByID($this->data['borrower']->district_id);
		$this->data['commune'] = $this->loans_model->getLocationByID($this->data['borrower']->commune_id);
		$this->data['village'] = $this->loans_model->getLocationByID($this->data['borrower']->village_id);
		$this->load->view($this->theme . 'loans/application_agreement', $this->data);
	}
	public function payment_schedule($id = false)
	{
		$this->bpas->checkPermissions("payment-schedule");
		$this->data['id'] = $id;
		$this->data['loan'] = $this->loans_model->getLoanByID($id);
		$this->data['loan_items'] = $this->loans_model->getLoanItemsByLoanID($id);
		$this->data['application'] = $this->loans_model->getApplicationByID($this->data['loan']->application_id);
		$this->data['loan_officer'] = $this->site->getUser($this->data['loan']->loan_officer_id);
		$this->data['biller'] = $this->site->getCompanyByID($this->data['loan']->biller_id);
		$this->data['borrower'] = $this->loans_model->getBorrowerByID($this->data['loan']->borrower_id);
		$this->data['country'] = $this->loans_model->getLocationByID($this->data['borrower']->country_id); 
		$this->data['province'] = $this->loans_model->getLocationByID($this->data['borrower']->province_id);
		$this->data['district'] = $this->loans_model->getLocationByID($this->data['borrower']->district_id);
		$this->data['commune'] = $this->loans_model->getLocationByID($this->data['borrower']->commune_id);
		$this->data['village'] = $this->loans_model->getLocationByID($this->data['borrower']->village_id);
		$this->load->view($this->theme . 'loans/payment_schedule', $this->data);
	}
	
	public function add_disburse($application_id = null)
	{
		$this->bpas->checkPermissions("applications-disburse");
		$application = $this->loans_model->getApplicationByID($application_id);
		if($application->status != 'approved'){
			$this->session->set_flashdata('error', lang("application_cannot_disburse"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('first_payment_date', lang("first_payment_date"), 'required');
		$this->form_validation->set_rules('disbursement_date', lang("disbursement_date"), 'required');
		$this->form_validation->set_rules('total_disbursement', lang("total_disbursement"), 'required');
        if ($this->form_validation->run() == true) {
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ln', $application->biller_id);
			$pay_reference_no = $this->site->getReference('ppay',$application->biller_id);
			$disburse_amount = $this->input->post('total_disbursement');
			$fee_charge_amount = $this->input->post('charge_fee');
			$disbursed_date = $this->input->post('disbursement_date');
			$payment_date = $this->input->post('first_payment_date');
			$all_interest_period = $this->site->getAllInterestperiod();
			$ipd = array();
			foreach($all_interest_period as $period){
				$ipd[$period->id] = $period->day;
			}
			$data = array(
				"date" 					=> date("Y-m-d H:i"),
				"application_id" 		=> $application_id,
				"reference_no" 			=> $reference_no,
				"biller_id" 			=> $application->biller_id,
				"currency" 				=> $application->currency,
				"biller" 				=> $application->biller,
				"borrower_id" 			=> $application->borrower_id,
				"borrower" 				=> $application->borrower,
				"principal_amount" 		=> $application->principal_amount,
				"grace_interest_charge" => $application->grace_interest_charge,
				"interest_rate" 		=> $application->interest_rate,
				"term" 					=> $application->term,
				"frequency" 			=> $application->frequency,
				"interest_period" 		=> $application->interest_period,
				"insurance_rate" 		=> $application->insurance_rate,
				"interest_method" 		=> $application->interest_method,
				"loan_product_id" 		=> $application->loan_product_id,
				"loan_officer_id" 		=> $application->loan_officer_id,
				"teller_id" 			=> $application->teller_id,
				"borrower_type_id" 		=> $application->borrower_type_id,
				"payment_date" 			=> $this->bpas->fld($payment_date),
				"disburse_amount" 		=> $disburse_amount,
				"disbursed_at" 			=> $this->bpas->fld($disbursed_date),
				"disbursed_by" 			=> $this->session->userdata("user_id"),
				"created_by" 			=> $this->session->userdata("user_id")
			);
			$payment = array(
                'date' 				=> $this->bpas->fld($disbursed_date),
                'reference_no' 		=> $pay_reference_no,
                'amount' 			=> $disburse_amount,
                'fee_charge' 		=> $fee_charge_amount,
                'paid_by' 			=> $this->input->post('paid_by'),
                'created_by' 		=> $this->session->userdata('user_id'),
                'type' 				=> 'sent',
            ); 
			$principal_amount = (float)$application->principal_amount;
			$grace_interest_charge = (int)$application->grace_interest_charge;
			$interest_rate = (float)$application->interest_rate;
			$term = (int)$application->term;
			$frequency = (int)$application->frequency;
			$interest_method = (int)$application->interest_method;
			$interest_period = (int)$application->interest_period;
			$balance = (float)$application->principal_amount;
			$period = $ipd;
			$items = array();
			$total_payment = 0;
			$total_interest = 0;
			$x = 0; 
			$start_day_pay = date('d', strtotime($this->bpas->fld($payment_date)));
			$convert_middle_date = date('15/m/Y', strtotime($this->bpas->fld($payment_date)));
			if($interest_method != 5){
				if($interest_method == 1){
					$rate = ($interest_rate / 100) * ($frequency / $period[$interest_period]);
					$rate_paid = pow(( 1 + $rate),$term);
					$payment_paid = ($balance * $rate) * $rate_paid / ($rate_paid - 1); 
					if($interest_rate <= 0){
						$payment_paid = $balance / $term;
					}
					for($i = 1; $i <= $term; $i++){
						
						if($i == 1){
							$deadline = strtotime($this->bpas->fld($payment_date));
							$deadline_convert_middle_date = strtotime($this->bpas->fld($convert_middle_date));
						}else{
							$deadline = strtotime("+".$x." day", strtotime($this->bpas->fld($payment_date)));
							if($frequency == 30){ 
                                $deadline = strtotime("+".$x." month", strtotime($this->bpas->fld($payment_date)));
                                $deadline_convert_middle_date = strtotime("+".$x." month", strtotime($this->bpas->fld($convert_middle_date)));
                                if(date("m", $deadline_convert_middle_date) != date("m", $deadline)){
                                	$d = date("d", $deadline);
                                	$deadline = strtotime("-".$d." day", strtotime($this->bpas->fld(date("d/m/Y", $deadline))));
                                }
							}
						}
						$rate = $balance*($interest_rate/100) * $frequency/$period[$interest_period];
						if($grace_interest_charge >= $i){
							$rate = 0;
						}
						$principal = $payment_paid - $rate;
						$balance -= $principal;
						if($balance <= 0){
							$principal = $principal + $balance;
							$payment_paid = $principal + $rate;
							$balance = 0;
						}
						$items[] = array(
							'period' => $i,
							'interest' => $this->bpas->formatDecimal($rate),
							'principal' => $this->bpas->formatDecimal($principal),
							'payment' => $this->bpas->formatDecimal($payment_paid),
							'balance' => $this->bpas->formatDecimal($balance),
							'deadline' => date("Y-m-d", $deadline),
						);
						$total_payment 	+= $payment_paid;
						$total_interest += $rate;
						// $x += $frequency;
						if($frequency == 30){
							$x += 1;
						}else{
							$x += $frequency;
						}
					} 
				}else if($interest_method == 2){
					$principal = ($balance / $term);
					for($i = 1; $i <= $term; $i++){
						if($i == 1){
							$deadline = strtotime($this->bpas->fld($payment_date));
							$deadline_convert_middle_date = strtotime($this->bpas->fld($convert_middle_date));
						}else{
							$deadline = strtotime("+".$x." day", strtotime($this->bpas->fld($payment_date)));
							if($frequency == 30){
								$deadline = strtotime("+".$x." month", strtotime($this->bpas->fld($payment_date)));
								$deadline_convert_middle_date = strtotime("+".$x." month", strtotime($this->bpas->fld($convert_middle_date)));
                                if(date("m", $deadline_convert_middle_date) != date("m", $deadline)){
                                	$d = date("d", $deadline);
                                	$deadline = strtotime("-".$d." day", strtotime($this->bpas->fld(date("d/m/Y", $deadline))));
                                }
							}
						}
						$rate = ($balance * ($interest_rate / 100)) * ($frequency / $period[$interest_period]);
						if($grace_interest_charge >= $i){
							$rate = 0;
						}
						$payment_paid = ($principal + $rate);
						$balance -= $principal;
						if($balance <= 0){
							$principal = $principal + $balance;
							$payment_paid = $principal + $rate;
							$balance = 0;
						}
						$items[] = array(
							'period' 	=> $i,
							'interest' 	=> $this->bpas->formatDecimal($rate),
							'principal' => $this->bpas->formatDecimal($principal),
							'payment' 	=> $this->bpas->formatDecimal($payment_paid),
							'balance' 	=> $this->bpas->formatDecimal($balance),
							'deadline' 	=> date("Y-m-d", $deadline),
						);
						$total_payment += $payment_paid;
						$total_interest += $rate;
						if($frequency == 30){
							$x += 1;
						}else{
							$x += $frequency;
						}
					}
				}else if($interest_method == 3){
					$principal = ($principal_amount / $term);
					for($i = 1; $i <= $term; $i++){
					
						if($i == 1){
							$deadline = strtotime($this->bpas->fld($payment_date));
							$deadline_convert_middle_date = strtotime($this->bpas->fld($convert_middle_date));
						}else{
							$deadline = strtotime("+".$x." day", strtotime($this->bpas->fld($payment_date)));
							if($frequency == 30){
								$deadline = strtotime("+".$x." months", strtotime($this->bpas->fld($payment_date)));
								$deadline_convert_middle_date = strtotime("+".$x." month", strtotime($this->bpas->fld($convert_middle_date)));
                                if(date("m", $deadline_convert_middle_date) != date("m", $deadline)){
                                	$d = date("d", $deadline);
                                	$deadline = strtotime("-".$d." day", strtotime($this->bpas->fld(date("d/m/Y", $deadline))));
                                }
							}
						}
						$rate = ($principal_amount * ($interest_rate / 100)) * ($frequency / $period[$interest_period]);
						if($grace_interest_charge >= $i){
							$rate = 0;
						}
						$payment_paid = ($principal + $rate);
						$balance -= $principal;
						if($balance <= 0){
							$principal = $principal + $balance;
							$payment_paid = $principal + $rate;
							$balance = 0;
						}
						$items[] = array(
							'period' 	=> $i,
							'interest' 	=> $this->bpas->formatDecimal($rate),
							'principal' => $this->bpas->formatDecimal($principal),
							'payment' 	=> $this->bpas->formatDecimal($payment_paid),
							'balance' 	=> $this->bpas->formatDecimal($balance),
							'deadline' 	=> date("Y-m-d", $deadline),
						);
						$total_payment  += $payment_paid;
						$total_interest += $rate;
						if($frequency == 30){
							$x += 1;
						}else{
							$x += $frequency;
						}
					}
				}else if($interest_method==4){
					$principal = ($principal_amount / $term);
					for($i = 1; $i <= $term; $i++){
						if($i == 1){
							$deadline = strtotime($this->bpas->fld($payment_date));
							$deadline_convert_middle_date = strtotime($this->bpas->fld($convert_middle_date));
						}else{
							$deadline = strtotime("+".$x." day", strtotime($this->bpas->fld($payment_date)));
							if($frequency == 30){
								$deadline = strtotime("+".$x." month", strtotime($this->bpas->fld($payment_date)));
								$deadline_convert_middle_date = strtotime("+".$x." month", strtotime($this->bpas->fld($convert_middle_date)));
                                if(date("m", $deadline_convert_middle_date) != date("m", $deadline)){
                                	$d = date("d", $deadline);
                                	$deadline = strtotime("-".$d." day", strtotime($this->bpas->fld(date("d/m/Y", $deadline))));
                                }
							}
						}
						$principal = 0;
						if($term == $i){
							$principal = $principal_amount;
						}
						$rate = ($principal_amount * ($interest_rate / 100));
						if($grace_interest_charge >= $i){
							$rate = 0;
						}
						$payment_paid = ($principal + $rate);
						$balance -= $principal;
						if($i == $term){
							$principal = $principal + $balance;
							$balance = 0;
						}
						$items[] = array(
							'period' 	=> $i,
							'interest' 	=> $this->bpas->formatDecimal($rate),
							'principal' => $this->bpas->formatDecimal($principal),
							'payment' 	=> $this->bpas->formatDecimal($payment_paid),
							'balance' 	=> $this->bpas->formatDecimal($balance),
							'deadline' 	=> date("Y-m-d", $deadline),
						);
						
						$total_payment += $payment_paid;
						$total_interest += $rate;
						if($frequency == 30){
							$x += 1;
						}else{
							$x += $frequency;
						} 
					}
				}
				
				if($items){
					$loan_product = $this->loans_model->getLoanProductByID($application->loan_product_id);
					$charges = $this->loans_model->getFeeCharge(json_decode($loan_product->charge_ids),1);
					foreach($items as $k=>$item){
						$total_fee = 0;
						if($charges){
							foreach($charges as $charge){
								if($charge->calculate == 1){
									$fee_charge = ($charge->amount * $items[$k]['principal']) / 100;
								}else if($charge->calculate == 2){
									$fee_charge = ($charge->amount * ($items[$k]['principal']+$items[$k]['interest'])) / 100;
								}else if($charge->calculate == 3){
									$fee_charge = ($charge->amount * $items[$k]['interest']) / 100;
								}else if($charge->calculate == 4){
									$fee_charge = ($charge->amount * $total_payment) / 100;
								}else if($charge->calculate == 5){
									$fee_charge = ($charge->amount * $application->principal_amount) / 100;
								}else{
									$fee_charge = $charge->amount;
								}
								$total_fee += $fee_charge;
							}
						}
						$items[$k]['fee_charge'] = $total_fee;
					}
				}

			}

			// $this->bpas->print_arrays($data, $items, $payment);
			
        } elseif ($this->input->post('add_disburse')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->loans_model->addDisburse($data, $items, $payment)) {
        	$this->site->updateReference('ln');
			$this->site->updateReference('ppay');
            $this->session->set_flashdata('message', lang("disburse_added"));
			admin_redirect('loans');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$loan_product = $this->loans_model->getLoanProductByID($application->loan_product_id);
			$this->data['application_id'] = $application_id;
			$this->data['application'] = $application;
			$this->data['charges'] = $this->loans_model->getFeeCharge(json_decode($loan_product->charge_ids));
			$this->data['paid_by'] = $this->accounts_model->getAllChartAccountBank();
			$this->data['borrower'] = $this->loans_model->getBorrowerByID($application->borrower_id);
			$this->data['check_collateral'] = $this->loans_model->getCheckCollaterals($application->id);
			$this->data['check_guarantor'] = $this->loans_model->getCheckGuarantors($application->id);
			$this->data['loan_products'] = $this->loans_model->getLoanProducts(); 
			$this->data['borrower_types'] = $this->loans_model->getAllBorrowerTypes();
			$this->data['loan_officers'] = $this->site->getAllUsers();
			$this->data['frequencies'] 	   = $this->loans_model->getFrequencies();
			$this->data['interest_period'] = $this->site->getAllInterestperiod();
			$this->data['interest_method'] = $this->site->getAllInterestmethod();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['modal_js'] = $this->site->modal_js();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('loans'), 'page' => lang('loans')), array('link' => '#', 'page' => lang('add_disburse')));
			$meta = array('page_title' => lang('add_disburse'), 'bc' => $bc);
			$this->page_construct('loans/add_disburse', $meta, $this->data);
        }
	}
	
	
}
