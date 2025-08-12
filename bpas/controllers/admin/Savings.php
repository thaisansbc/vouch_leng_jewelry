<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Savings extends MY_Controller
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
        $this->lang->admin_load('savings', $this->Settings->user_language);
        $this->load->library('form_validation');
		$this->load->admin_model('savings_model');
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }
	
	public function index($biller_id = NULL)
	{
		$this->bpas->checkPermissions();
		if($biller_id == 0){
			$biller_id = null;
		}
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('savings')));
		$meta = array('page_title' => lang('savings'), 'bc' => $bc);
        $this->page_construct('savings/index', $meta, $this->data);
	}
	
	public function getSavings($biller_id = NULL)
    {
	    $this->bpas->checkPermissions("index");
        $this->load->library('datatables');
	    $view_saving_link = anchor('admin/savings/view/$1', '<i class="fa fa-file-text-o"></i> ' .lang('saving_details'),' class="view-saving"');
		$transfer_saving_link = anchor('admin/savings/add_transfer/$1', '<i class="fa fa-exchange"></i> ' .lang('transfer'),' class="transfer-saving" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$deposit_saving_link = anchor('admin/savings/add_deposit/$1', '<i class="fa fa-usd"></i> ' .lang('deposit'),' class="deposit-saving" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$withdraw_saving_link = anchor('admin/savings/add_withdraw/$1', '<i class="fa fa-paper-plane-o"></i> ' .lang('withdraw'),' class="withdraw-saving" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$edit_saving_link = anchor('admin/savings/edit/$1', '<i class="fa fa-edit"></i> ' .lang('edit_saving'),' class="edit-saving"');
	    $cancel_last_transaction_link = anchor('admin/savings/cancel_last_transaction/$1', '<i class="fa fa-reply-all"></i> ' .lang('cancel_last_transaction'),' class="transfer-saving" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
		$close_saving_link = anchor('admin/savings/close/$1', '<i class="fa fa-times"></i> ' .lang('close_account'),' class="close-saving" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
			
		$delete_saving_link = "<a href='#' class='po' title='<b>" . lang("delete_saving") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('savings/delete/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_saving') . "</a>";
			
		$action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$view_saving_link.'</li>
						<li>'.$deposit_saving_link.'</li>
						<li>'.$withdraw_saving_link.'</li>
						<li>'.$transfer_saving_link.'</li>
						<li>'.$cancel_last_transaction_link.'</li>
						<li>'.$close_saving_link.'</li>
						<li>'.$edit_saving_link.'</li>
						<li>'.$delete_saving_link.'</li>
					</ul>
				</div></div>';
				
        $this->datatables
            ->select("
				savings.id as id,
				savings.reference_no,
				loan_borrowers.code as code,
				savings.borrower,
				CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
				saving_products.name as saving_product,
				IFNULL(bpas_cashins.amount,0)-IFNULL(bpas_cashouts.amount,0) as ledger_balance,
				bpas_last_transactions.date,
				savings.status")
            ->from("savings")
			->join('loan_borrowers','loan_borrowers.id=savings.borrower_id','left')
			->join("saving_products","saving_products.id=savings.saving_product_id","left")
			->join('(SELECT 
							saving_id,
							SUM(amount) as amount
						FROM bpas_payments WHERE transaction_type="in" GROUP BY saving_id)
						as bpas_cashins
						','bpas_cashins.saving_id=savings.id','left')
			->join('(SELECT 
							saving_id,
							SUM(amount) as amount
						FROM bpas_payments WHERE transaction_type="out" GROUP BY saving_id)
						as bpas_cashouts
						','bpas_cashouts.saving_id=savings.id','left')
			->join('(SELECT 
							saving_id,
							MAX(date) as date
						FROM bpas_payments GROUP BY saving_id)
						as bpas_last_transactions
						','bpas_last_transactions.saving_id=savings.id','left');
			
			if ($biller_id) {
				$this->datatables->where('savings.biller_id', $biller_id);
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('savings.created_by', $this->session->userdata('user_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('savings.biller_id',$this->session->userdata('biller_id'));
			}
			$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
	
	public function saving_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true){
			if(isset($_POST['val'])){
				$this->db->where_in('savings.id' , $_POST['val']);
			}
			$this->db
				->select("
				savings.id as id,
				savings.reference_no,
				loan_borrowers.code as code,
				savings.borrower,
				CONCAT(UCASE(LEFT(bpas_loan_borrowers.gender, 1)),SUBSTRING(bpas_loan_borrowers.gender, 2)) as gender,
				saving_products.name as saving_product,
				IFNULL(bpas_cashins.amount,0)-IFNULL(bpas_cashouts.amount,0) as ledger_balance,
				bpas_last_transactions.date as last_transaction,
				savings.status")
            ->from("savings")
			->join('loan_borrowers','loan_borrowers.id=savings.borrower_id','left')
			->join("saving_products","saving_products.id=savings.saving_product_id","left")
			->join('(SELECT 
							saving_id,
							SUM(amount) as amount
						FROM bpas_payments WHERE type="received" GROUP BY saving_id)
						as bpas_cashins
						','bpas_cashins.saving_id=savings.id','left')
			->join('(SELECT 
							saving_id,
							SUM(amount) as amount
						FROM bpas_payments WHERE type="sent" GROUP BY saving_id)
						as bpas_cashouts
						','bpas_cashouts.saving_id=savings.id','left')
			->join('(SELECT 
							saving_id,
							MAX(date) as date
						FROM bpas_payments GROUP BY saving_id)
						as bpas_last_transactions
						','bpas_last_transactions.saving_id=savings.id','left');
			
			$q = $this->db->get();
			if(isset($_POST['val'])){
				if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->savings_model->deleteSaving($id);
                    }
                    $this->session->set_flashdata('message', lang("savings_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
					$this->load->library('excel');
					$this->excel->setActiveSheetIndex(0);
					$this->excel->getActiveSheet()->setTitle(lang('savings'));
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('account_no'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('borrower_code'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('borrower'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('gender'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('saving_product'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('ledger_balance'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('last_transaction'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
					$style = array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						)
					);
					$this->excel->getActiveSheet()->getStyle("A1:H1")->applyFromArray($style)->getFont()->setBold(true);
					$row = 2;
					foreach ($q->result() as $saving){
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $saving->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $saving->code);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $saving->borrower);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $saving->gender);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $saving->saving_product);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatMoney($saving->ledger_balance));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->hrld($saving->last_transaction));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($saving->status));
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
					$filename = 'savings_' . date('Y_m_d_H_i_s');
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
	
	public function saving_products($biller_id = NULL)
	{
		$this->bpas->checkPermissions("saving_products");
		if($biller_id == 0){
			$biller_id = null;
		}
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('savings'), 'page' => lang('savings') , 'page' => lang('saving_products')));
		$meta = array('page_title' => lang('saving_products'), 'bc' => $bc);
        $this->page_construct('savings/saving_products', $meta, $this->data);
	}
	
	public function getSavingProducts($biller_id = NULL)
    {
		$this->bpas->checkPermissions("saving_products");
        $this->load->library('datatables');
	    $edit_saving_product_link = anchor('admin/savings/edit_saving_product/$1', '<i class="fa fa-edit"></i> ' .lang('edit_saving_product'));
		$delete_saving_product_link = "<a href='#' class='po' title='<b>" . lang("delete_saving_product") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('savings/delete_saving_product/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
			. lang('delete_saving_product') . "</a>";
	    $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>'.$edit_saving_product_link.'</li>
						<li>'.$delete_saving_product_link.'</li>
					</ul>
				</div></div>';
        $this->datatables
            ->select("
				saving_products.id as id,
				saving_products.name,
				CONCAT(bpas_saving_products.initial_amount_min,' - ',bpas_saving_products.initial_amount_max) as initail_amount,
				CONCAT(bpas_saving_products.interest_rate_min,' - ',bpas_saving_products.interest_rate_max) as interest_rate,
				CONCAT(bpas_saving_products.withdraw_min,' - ',bpas_saving_products.withdraw_max) as withdraw,
				CONCAT(bpas_saving_products.deposit_min,' - ',bpas_saving_products.deposit_max) as deposit,
				CONCAT(bpas_saving_products.transfer_min,' - ',bpas_saving_products.transfer_max) as transfer,
				CONCAT(bpas_saving_products.balance_min,' - ',bpas_saving_products.balance_max) as balance")
            ->from("saving_products");
			
			if ($biller_id) {
				$this->datatables->where('loans.biller_id', $biller_id);
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
	
	public function delete_saving_product($id = null)
    {
        $this->bpas->checkPermissions("saving_products");
        if ($this->savings_model->deleteSavingProduct($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("saving_product_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("saving_product_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function saving_product_actions()
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])){
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteSavingProduct($id);
                    }
                    $this->session->set_flashdata('message', lang("saving_product_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('saving_products'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('initial_amount'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('interest_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('withdraw'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('deposit'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('transfer'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
					$style = array(
							'alignment' => array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							)
						);
					$this->excel->getActiveSheet()->getStyle("A1:G1")->applyFromArray($style)->getFont()->setBold(true);
                    $row = 2;
                    foreach ($_POST['val'] as $id){
						$saving_product = $this->savings_model->getSavingProductByID($id);
						$initial_amount = $saving_product->initial_amount_min.' - '.$saving_product->initial_amount_max;
						$interest_rate = $saving_product->interest_rate_min.' - '.$saving_product->interest_rate_max;
						$withdraw = $saving_product->withdraw_min.' - '.$saving_product->withdraw_max;
						$deposit = $saving_product->deposit_min.' - '.$saving_product->deposit_max;
						$transfer = $saving_product->transfer_min.' - '.$saving_product->transfer_max;
						$balance = $saving_product->balance_min.' - '.$saving_product->balance_max;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $saving_product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $initial_amount);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $interest_rate);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $withdraw);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $deposit);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $transfer);
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $balance);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'saving_products_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_savings_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	
	public function add_saving_product()
	{
		$this->bpas->checkPermissions("saving_products");
		$this->form_validation->set_rules('name', lang("name"), 'required|trim|is_unique[saving_products.name]');
		if ($this->form_validation->run() == true) {
			
			$name = $this->input->post('name',true);
			$interest_period = $this->input->post('interest_period',true);
			$initial_amount_min = $this->input->post('initial_amount_min',true);
			$initial_amount_max = $this->input->post('initial_amount_max',true);
			$interest_rate_min = $this->input->post('interest_rate_min',true);
			$interest_rate_max = $this->input->post('interest_rate_max',true);
			$balance_min = $this->input->post('balance_min',true);
			$balance_max = $this->input->post('balance_max',true);
			$withdraw_min = $this->input->post('withdraw_min',true);
			$withdraw_max = $this->input->post('withdraw_max',true);
			$withdraw_fee_min = $this->input->post('withdraw_fee_min',true);
			$withdraw_fee_max = $this->input->post('withdraw_fee_max',true);
			$deposit_min = $this->input->post('deposit_min',true);
			$deposit_max = $this->input->post('deposit_max',true);
			$deposit_fee_min = $this->input->post('deposit_fee_min',true);
			$deposit_fee_max = $this->input->post('deposit_fee_max',true);
			$transfer_min = $this->input->post('transfer_min',true);
			$transfer_max = $this->input->post('transfer_max',true);
			$transfer_fee_min = $this->input->post('transfer_fee_min',true);
			$transfer_fee_max = $this->input->post('transfer_fee_max',true);
			$entry_fee_min = $this->input->post('entry_fee_min',true);
			$entry_fee_max = $this->input->post('entry_fee_max',true);
			if($initial_amount_max < $initial_amount_min){
				$this->session->set_flashdata('error', lang("initial_amount_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($interest_rate_max < $interest_rate_min){
				$this->session->set_flashdata('error', lang("interest_rate_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($balance_max < $balance_min){
				$this->session->set_flashdata('error', lang("balance_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($withdraw_max < $withdraw_min){
				$this->session->set_flashdata('error', lang("withdraw_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($withdraw_fee_max < $withdraw_fee_min){
				$this->session->set_flashdata('error', lang("withdraw_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($deposit_max < $deposit_min){
				$this->session->set_flashdata('error', lang("deposit_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($deposit_fee_max < $deposit_fee_min){
				$this->session->set_flashdata('error', lang("deposit_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($transfer_max < $transfer_min){
				$this->session->set_flashdata('error', lang("transfer_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($transfer_fee_max < $transfer_fee_min){
				$this->session->set_flashdata('error', lang("transfer_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($entry_fee_max < $entry_fee_min){
				$this->session->set_flashdata('error', lang("entry_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$data = array(
				"name" => $name,
				"initial_amount_min" => $initial_amount_min,
				"initial_amount_max" => $initial_amount_max,
				"interest_rate_min" => $interest_rate_min,
				"interest_rate_max" => $interest_rate_max,
				"interest_period" => $interest_period,
				"balance_min" => $balance_min,
				"balance_max" => $balance_max,
				"withdraw_min" => $withdraw_min,
				"withdraw_max" => $withdraw_max,
				"withdraw_fee_min" => $withdraw_fee_min,
				"withdraw_fee_max" => $withdraw_fee_max,
				"deposit_min" => $deposit_min,
				"deposit_max" => $deposit_max,
				"deposit_fee_min" => $deposit_fee_min,
				"deposit_fee_max" => $deposit_fee_max,
				"transfer_min" => $transfer_min,
				"transfer_max" => $transfer_max,
				"transfer_fee_min" => $transfer_fee_min,
				"transfer_fee_max" => $transfer_fee_max,
				"entry_fee_min" => $entry_fee_min,
				"entry_fee_max" => $entry_fee_max
			);
			
        } elseif ($this->input->post('add_saving_product')) {
            $this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->savings_model->addSavingProduct($data)) {
            $this->session->set_flashdata('message', lang("saving_product_added"));
            admin_redirect('savings/saving_products');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('savings')), array('link' => '#', 'page' => lang('add_saving_product')));
			$meta = array('page_title' => lang('add_saving_product'), 'bc' => $bc);
			$this->page_construct('savings/add_saving_product', $meta, $this->data);
        }
	}
	
	public function edit_saving_product($id = false)
	{
		$this->bpas->checkPermissions("saving_products");
		$saving_product = $this->savings_model->getSavingProductByID($id);
		$name = $this->input->post('name',true);
		$name_valid = '';
		if($name != $saving_product->name){
			$name_valid .= '|is_unique[saving_products.name]';
		}
		$this->form_validation->set_rules('name', lang("name"), 'required|trim'.$name_valid);
		if ($this->form_validation->run() == true) {
			
			$name = $this->input->post('name',true);
			$interest_period = $this->input->post('interest_period',true);
			$initial_amount_min = $this->input->post('initial_amount_min',true);
			$initial_amount_max = $this->input->post('initial_amount_max',true);
			$interest_rate_min = $this->input->post('interest_rate_min',true);
			$interest_rate_max = $this->input->post('interest_rate_max',true);
			$balance_min = $this->input->post('balance_min',true);
			$balance_max = $this->input->post('balance_max',true);
			$withdraw_min = $this->input->post('withdraw_min',true);
			$withdraw_max = $this->input->post('withdraw_max',true);
			$withdraw_fee_min = $this->input->post('withdraw_fee_min',true);
			$withdraw_fee_max = $this->input->post('withdraw_fee_max',true);
			$deposit_min = $this->input->post('deposit_min',true);
			$deposit_max = $this->input->post('deposit_max',true);
			$deposit_fee_min = $this->input->post('deposit_fee_min',true);
			$deposit_fee_max = $this->input->post('deposit_fee_max',true);
			$transfer_min = $this->input->post('transfer_min',true);
			$transfer_max = $this->input->post('transfer_max',true);
			$transfer_fee_min = $this->input->post('transfer_fee_min',true);
			$transfer_fee_max = $this->input->post('transfer_fee_max',true);
			$entry_fee_min = $this->input->post('entry_fee_min',true);
			$entry_fee_max = $this->input->post('entry_fee_max',true);
			
			if($initial_amount_max < $initial_amount_min){
				$this->session->set_flashdata('error', lang("initial_amount_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($interest_rate_max < $interest_rate_min){
				$this->session->set_flashdata('error', lang("interest_rate_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($balance_max < $balance_min){
				$this->session->set_flashdata('error', lang("balance_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($withdraw_max < $withdraw_min){
				$this->session->set_flashdata('error', lang("withdraw_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($withdraw_fee_max < $withdraw_fee_min){
				$this->session->set_flashdata('error', lang("withdraw_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($deposit_max < $deposit_min){
				$this->session->set_flashdata('error', lang("deposit_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($deposit_fee_max < $deposit_fee_min){
				$this->session->set_flashdata('error', lang("deposit_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($transfer_max < $transfer_min){
				$this->session->set_flashdata('error', lang("transfer_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($transfer_fee_max < $transfer_fee_min){
				$this->session->set_flashdata('error', lang("transfer_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			if($entry_fee_max < $entry_fee_min){
				$this->session->set_flashdata('error', lang("entry_fee_max_must_greater_than_min"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			$data = array(
				"name" => $name,
				"initial_amount_min" => $initial_amount_min,
				"initial_amount_max" => $initial_amount_max,
				"interest_rate_min" => $interest_rate_min,
				"interest_rate_max" => $interest_rate_max,
				"interest_period" => $interest_period,
				"balance_min" => $balance_min,
				"balance_max" => $balance_max,
				"withdraw_min" => $withdraw_min,
				"withdraw_max" => $withdraw_max,
				"withdraw_fee_min" => $withdraw_fee_min,
				"withdraw_fee_max" => $withdraw_fee_max,
				"deposit_min" => $deposit_min,
				"deposit_max" => $deposit_max,
				"deposit_fee_min" => $deposit_fee_min,
				"deposit_fee_max" => $deposit_fee_max,
				"transfer_min" => $transfer_min,
				"transfer_max" => $transfer_max,
				"transfer_fee_min" => $transfer_fee_min,
				"transfer_fee_max" => $transfer_fee_max,
				"entry_fee_min" => $entry_fee_min,
				"entry_fee_max" => $entry_fee_max
			);
			
        } elseif ($this->input->post('edit_saving_product')) {
            $this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->savings_model->updateSavingProduct($id, $data)) {
            $this->session->set_flashdata('message', lang("saving_product_updated"));
            admin_redirect('savings/saving_products');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id']= $id;
			$this->data['saving_product'] = $saving_product;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('savings')), array('link' => '#', 'page' => lang('edit_saving_product')));
			$meta = array('page_title' => lang('edit_saving_product'), 'bc' => $bc);
			$this->page_construct('savings/edit_saving_product', $meta, $this->data);
        }
	}
	
	public function add()
	{
		$this->bpas->checkPermissions("add");
		$this->form_validation->set_rules('date', lang("date"), 'required');
		$this->form_validation->set_rules('initial_amount', lang("initial_amount"), 'required');
		if ($this->form_validation->run() == true) {
			$date = $this->input->post('date',true);
			
			if ($this->Owner || $this->Admin || $GP['savings-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post("biller");
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company ? $biller_details->company : $biller_details->name;
			$borrower_id = $this->input->post("borrower");
			$borrower_details = $this->savings_model->getBorrowerByID($borrower_id);
			$borrower = $borrower_details->last_name.' '.$borrower_details->first_name;
			$saving_product = $this->input->post('saving_product',true);
			$initial_amount = $this->input->post('initial_amount',true);
			$interest_rate = $this->input->post('interest_rate',true);
			$interest_period = $this->input->post('interest_period',true);
			$entry_fee = $this->input->post('entry_fee',true);
			$withdraw_fee = $this->input->post('withdraw_fee',true);
			$deposit_fee = $this->input->post('deposit_fee',true);
			$transfer_fee = $this->input->post('transfer_fee',true);
			$saving_officer = $this->input->post('saving_officer',true);
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sav',$biller_id);
			
			$entry_fee_min = $this->input->post('entry_fee_min');
			$entry_fee_max = $this->input->post('entry_fee_max');
			if($entry_fee > $entry_fee_max || $entry_fee < $entry_fee_min){
				$this->session->set_flashdata('error', lang("entry_fee_not_invalid"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			
			$deposit_fee_min = $this->input->post('deposit_fee_min');
			$deposit_fee_max = $this->input->post('deposit_fee_max');
			if($deposit_fee > $deposit_fee_max || $deposit_fee < $deposit_fee_min){
				$this->session->set_flashdata('error', lang("deposit_fee_not_invalid"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			
			$withdraw_fee_min = $this->input->post('withdraw_fee_min');
			$withdraw_fee_max = $this->input->post('withdraw_fee_max');
			if($withdraw_fee > $withdraw_fee_max || $withdraw_fee < $withdraw_fee_min){
				$this->session->set_flashdata('error', lang("withdraw_fee_not_invalid"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			
			$transfer_fee_min = $this->input->post('transfer_fee_min');
			$transfer_fee_max = $this->input->post('transfer_fee_max');
			if($transfer_fee > $transfer_fee_max || $transfer_fee < $transfer_fee_min){
				$this->session->set_flashdata('error', lang("transfer_fee_not_invalid"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			
			$initial_amount_min = $this->input->post('initial_amount_min');
			$initial_amount_max = $this->input->post('initial_amount_max');
			if($initial_amount > $initial_amount_max || $initial_amount < $initial_amount_min){
				$this->session->set_flashdata('error', lang("initial_amount_not_invalid"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			
			$interest_rate_min = $this->input->post('interest_rate_min');
			$interest_rate_max = $this->input->post('interest_rate_max');
			if($interest_rate > $interest_rate_max || $interest_rate < $interest_rate_min){
				$this->session->set_flashdata('error', lang("interest_rate_not_invalid"));
				admin_redirect($_SERVER["HTTP_REFERER"]);
			}
			
			$data = array(
				"date" => $date,
				"reference_no" => $reference_no,
				"biller" => $biller,
				"biller_id" => $biller_id,
				"borrower" => $borrower,
				"borrower_id" => $borrower_id,
				"saving_product_id" => $saving_product,
				"saving_officer_id"=> $saving_officer,
				"initial_amount" => $initial_amount,
				"interest_rate" => $interest_rate,
				"interest_period"=> $interest_period,
				"entry_fee"=> $entry_fee,
				"withdraw_fee"=> $withdraw_fee,
				"deposit_fee"=> $deposit_fee,
				"transfer_fee"=> $transfer_fee,
				"created_by" => $this->session->userdata("user_id"),
			);
			
			if($initial_amount > 0){
				$tr_reference_no = $this->site->getReference('sav_tr',$biller_id);
				$transaction_id = $this->savings_model->getLastTran();
				
				$payment[] = array(
					'date' => $date,
					'reference_no' => $tr_reference_no,
					'amount' => $initial_amount,
					'transaction_id' => $transaction_id,
					'transaction' => 'Initial Deposit',
					'transaction_type' => 'in',
					'paid_by' => 'cash',
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'received'
				);
				if($entry_fee > 0){
					$payment[] = array(
						'date' => $date,
						'reference_no' => $tr_reference_no,
						'amount' => $entry_fee,
						'transaction_id' => $transaction_id,
						'transaction' => 'Fee for Entry',
						'transaction_type' => 'out',
						'paid_by' => 'cash',
						'created_by' => $this->session->userdata('user_id'),
						'type' => 'received'
					);
				}
				
				$saving_product=$this->savings_model->getSavingProductByID($saving_product);
				$total_amount = $initial_amount - $entry_fee;
				if($total_amount > (double)$saving_product->balance_max){
					$this->session->set_flashdata('error', lang("available_balance_invalid"));
					$this->bpas->md();
				}
			}
			
        } elseif ($this->input->post('add_saving')) {
            $this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->savings_model->addSaving($data, $payment)) {
            $this->session->set_flashdata('message', lang("saving_added"));
            admin_redirect('savings');
			
        } else {
			$this->data['saving_products'] = $this->savings_model->getSavingProducts();
			$this->data['saving_officers'] = $this->site->getAllUsers();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('savings')), array('link' => '#', 'page' => lang('add_saving')));
			$meta = array('page_title' => lang('add_saving'), 'bc' => $bc);
			$this->page_construct('savings/add', $meta, $this->data);
        }
	}
		
	public function get_saving_product()
	{
		$id = $this->input->get("id");
		if($id){
			$saving = $this->savings_model->getSavingProductByID($id);
			echo json_encode($saving);
		}
	}

	public function delete($id = null)
    {
        $this->bpas->checkPermissions("delete");
        if ($this->savings_model->deleteSaving($id)) {
			if ($this->input->is_ajax_request()) {
				echo lang("saving_deleted"); exit;
			}
        }
		$this->session->set_flashdata('message', lang("saving_deleted"));
        admin_redirect($_SERVER["HTTP_REFERER"]);
    }
	
	public function edit($id = false)
	{
		$this->bpas->checkPermissions("edit");
		$saving = $this->savings_model->getSavingByID($id);
		if ($saving->status != 'active') {
			$this->session->set_flashdata('error', lang("saving_cannot_edit"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('date', lang("date"), 'required');
		if ($this->form_validation->run() == true) {
			$date = $this->input->post('date',true);
			
			if ($this->Owner || $this->Admin || $GP['savings-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$biller_id = $this->input->post("biller");
			$biller_details = $this->site->getCompanyByID($biller_id);
			$biller = $biller_details->company ? $biller_details->company : $biller_details->name;
			$initial_amount = $this->input->post('initial_amount',true);
			$interest_rate = $this->input->post('interest_rate',true);
			$interest_period = $this->input->post('interest_period',true);
			$entry_fee = $this->input->post('entry_fee',true);
			$withdraw_fee = $this->input->post('withdraw_fee',true);
			$deposit_fee = $this->input->post('deposit_fee',true);
			$transfer_fee = $this->input->post('transfer_fee',true);
			$saving_officer = $this->input->post('saving_officer',true);
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sav',$biller_id);
			
			$data = array(
				"date" => $date,
				"reference_no" => $reference_no,
				"biller" => $biller,
				"biller_id" => $biller_id,
				"saving_officer_id"=> $saving_officer,
				"initial_amount" => $initial_amount,
				"interest_rate" => $interest_rate,
				"interest_period"=> $interest_period,
				"entry_fee"=> $entry_fee,
				"withdraw_fee"=> $withdraw_fee,
				"deposit_fee"=> $deposit_fee,
				"transfer_fee"=> $transfer_fee,
				"updated_by" => $this->session->userdata("user_id"),
				"updated_at" => date("Y-m-d H:i"),
			);
			
        } elseif ($this->input->post('edit_saving')) {
            $this->session->set_flashdata('error', validation_errors());
			admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->savings_model->updateSaving($id, $data)) {
            $this->session->set_flashdata('message', lang("saving_updated"));
            admin_redirect('savings');
        } else {
			$this->data['id'] = $id;
			$this->data['saving'] = $this->savings_model->getSavingByID($id);
			$this->data['saving_products'] = $this->savings_model->getSavingProducts();
			$this->data['saving_officers'] = $this->site->getAllUsers();
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('savings')), array('link' => '#', 'page' => lang('edit_saving')));
			$meta = array('page_title' => lang('edit_saving'), 'bc' => $bc);
			$this->page_construct('savings/edit', $meta, $this->data);
        }
	}
	
	public function view($id = NULL)
	{
		$saving = $this->savings_model->getSavingByID($id);
		$borrower = $this->savings_model->getBorrowerByID($saving->borrower_id);
		$this->data['id'] = $id;
		$this->data['saving'] = $saving;
		$this->data['borrower'] = $borrower;
		$this->data['payment_balance'] = $this->savings_model->getPaymentBalance($id);
		$this->data['country'] = $this->savings_model->getLocationByID($borrower->country_id); 
		$this->data['province'] = $this->savings_model->getLocationByID($borrower->province_id);
		$this->data['district'] = $this->savings_model->getLocationByID($borrower->district_id);
		$this->data['commune'] = $this->savings_model->getLocationByID($borrower->commune_id);
		$this->data['village'] = $this->savings_model->getLocationByID($borrower->village_id);
		$this->data['working'] = $this->savings_model->getWorkingStatusByID($borrower->working_status);
		$this->data['saving_officer'] = $this->site->getUser($saving->saving_officer_id);
		$this->data['saving_product'] = $this->savings_model->getSavingProductByID($saving->saving_product_id);
		$this->data['closed_by'] = $saving->status == 'closed' ? $this->site->getUser($saving->closed_by) : null;
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('savings')), array('link' => '#', 'page' => lang('saving_details')));
        $meta = array('page_title' => lang('saving_details'), 'bc' => $bc);
        $this->page_construct('savings/view', $meta, $this->data);
	}
	
	public function getTransactions($id = NULL)
	{
		$start_date = $this->input->get('start_date');
		$end_date = $this->input->get('end_date');
		if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date = $this->bpas->fld($end_date,false,1);
        }
		$this->bpas->checkPermissions("transactions");
		$this->load->library('datatables');
		$this->datatables
			->select("
				payments.date,
				payments.reference_no,
				IF(bpas_payments.transaction_type='in',bpas_payments.amount,0) as debit,
				IF(bpas_payments.transaction_type='out',bpas_payments.amount,0) as credit,
				payments.transaction_id,
				payments.transaction,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as created_by,
				payments.paid_by,
				payments.type,
				payments.id")
			->from('payments')
			->join('savings', 'savings.id=payments.saving_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->where("payments.saving_id",$id);
		
		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('payments').'.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
		}
		echo $this->datatables->generate();
	}
	
	public function transaction_actions($id = NULL, $pdf = NULL, $xls = NULL)
	{
		if (!$this->Owner && !$this->GP['bulk_actions']) {
			$this->session->set_flashdata('warning', lang('access_denied'));
			admin_redirect($_SERVER["HTTP_REFERER"]);
		}
		if ($pdf || $xls) {
			$start_date = $this->input->get('start_date');
			$end_date = $this->input->get('end_date');
			if ($start_date) {
				$start_date = $this->bpas->fld($start_date);
				$end_date = $this->bpas->fld($end_date,false,1);
			}
		
			$this->db
			->select("
				payments.date,
				payments.reference_no,
				IF(bpas_payments.transaction_type='in',bpas_payments.amount,0) as debit,
				IF(bpas_payments.transaction_type='out',bpas_payments.amount,0) as credit,
				payments.transaction_id,
				payments.transaction,
				CONCAT(bpas_users.last_name,' ',bpas_users.first_name) as user,
				payments.paid_by,
				payments.type,
				payments.id")
			->from('payments')
			->join('savings', 'savings.id=payments.saving_id', 'left')
			->join('users','users.id=payments.created_by','left')
			->where("payments.saving_id",$id);
			
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
				$this->excel->getActiveSheet()->setTitle(lang('transactions'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('debit'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('credit'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('transaction_id'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('transaction'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('user'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid_by'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('type'));
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
				$this->excel->getActiveSheet()->getStyle("A1:I1")->applyFromArray($style)->getFont()->setBold(true);
				$t_debit = 0;
				$t_credit = 0;
				$row = 2;
				foreach ($data as $data_row){
					$t_debit += $data_row->debit;
					$t_credit += $data_row->credit;
					
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->debit);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->credit);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->transaction_id);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->transaction);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->user);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->paid_by);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, ucfirst($data_row->type));
					$row++;
				}
				$this->excel->getActiveSheet()->getStyle("C".$row.":D".$row)->getFont()->setBold(true);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($t_debit));
				$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($t_credit));
				$this->excel->getActiveSheet()->getStyle("C".$row.":D".($row+1))->getFont()->setBold(true);
				$this->excel->getActiveSheet()->SetCellValue('D' . ($row+1), $this->bpas->formatDecimal($t_debit-$t_credit));
				
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
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
	
	public function add_deposit($id = NULL)
    {
		$this->bpas->checkPermissions('add_deposit');
		$saving = $this->savings_model->getSavingByID($id);
		if ($saving->status != 'active') {
			$this->session->set_flashdata('error', lang("saving_cannot_add_deposit"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('amount', lang("amount"), 'required');
        if ($this->form_validation->run() == true) {
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sav_tr',$saving->biller_id);
            $transaction_id = $this->savings_model->getLastTran();
			if ($this->Owner || $this->Admin || $GP['savings-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$amount = $this->input->post('amount');
			$transaction_fee = $this->input->post('transaction_fee');
            $payment[] = array(
                'date' => $date,
				'saving_id' => $saving->id,
                'reference_no' => $reference_no,
				'transaction_id' => $transaction_id,
				'transaction' => 'Deposit',
				'transaction_type' => 'in',
                'amount' => $amount,
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
            );
			if($transaction_fee > 0){
				$payment[] = array(
					'date' => $date,
					'saving_id' => $saving->id,
					'reference_no' => $reference_no,
					'transaction_id' => $transaction_id,
					'transaction' => 'Fee for Deposit',
					'transaction_type' => 'out',
					'amount' => $transaction_fee,
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
				);
			}
			
			$saving_product=$this->savings_model->getSavingProductByID($saving->saving_product_id);
			$available_balance = $this->savings_model->getPaymentBalance($id);
			$total_amount = $amount - $transaction_fee;
			if(($available_balance + $total_amount) > (double)$saving_product->balance_max){
				$this->session->set_flashdata('error', lang("available_balance_invalid"));
				$this->bpas->md();
			}
		}
		if ($this->form_validation->run() == true && $this->savings_model->addDeposit($payment)) {
			$this->session->set_flashdata('message', lang("deposit_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['payment_ref'] = '';
			$this->data['saving'] = $saving;
			$this->data['saving_product'] = $this->savings_model->getSavingProductByID($saving->saving_product_id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'savings/add_deposit', $this->data);
        }
    }
	
	public function add_withdraw($id = NULL)
    {
		$this->bpas->checkPermissions('add_withdraw');
		$saving = $this->savings_model->getSavingByID($id);
		if ($saving->status != 'active') {
			$this->session->set_flashdata('error', lang("saving_cannot_add_withdraw"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('amount', lang("amount"), 'required');
        if ($this->form_validation->run() == true) {
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sav_tr',$saving->biller_id);
            $transaction_id = $this->savings_model->getLastTran();
			
			if ($this->Owner || $this->Admin || $GP['savings-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			
			$amount = $this->input->post('amount');
			$transaction_fee = $this->input->post('transaction_fee');
            $payment[] = array(
                'date' => $date,
				'saving_id' => $saving->id,
                'reference_no' => $reference_no,
				'transaction_id' => $transaction_id,
				'transaction' => 'Withdrawal',
				'transaction_type' => 'out',
                'amount' => $amount,
                'paid_by' => $this->input->post('paid_by'),
				'cheque_no' => $this->input->post('cheque_no'),
				'cc_no' => $this->input->post('pcc_no'),
				'cc_holder' => $this->input->post('pcc_holder'),
				'cc_month' => $this->input->post('pcc_month'),
				'cc_year' => $this->input->post('pcc_year'),
				'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
            );
			if($transaction_fee > 0){
				$payment[] = array(
					'date' => $date,
					'saving_id' => $saving->id,
					'reference_no' => $reference_no,
					'transaction_id' => $transaction_id,
					'transaction' => 'Fee for Withdraw',
					'transaction_type' => 'out',
					'amount' => $transaction_fee,
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
				);
			}
			$saving_product=$this->savings_model->getSavingProductByID($saving->saving_product_id);
			$available_balance = $this->savings_model->getPaymentBalance($id);
			$total_amount = $amount + $transaction_fee;
			if(($available_balance - $total_amount) < (double)$saving_product->balance_min){
				$this->session->set_flashdata('error', lang("available_balance_invalid"));
				$this->bpas->md();
			}
		}
		if ($this->form_validation->run() == true && $this->savings_model->addDeposit($payment)) {
			$this->session->set_flashdata('message', lang("withdraw_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['payment_ref'] = '';
			$this->data['saving'] = $saving;
			$this->data['saving_product'] = $this->savings_model->getSavingProductByID($saving->saving_product_id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'savings/add_withdraw', $this->data);
        }
    }
	
	public function add_transfer($id = NULL)
    {
		$this->bpas->checkPermissions('add_transfer');
		$saving = $this->savings_model->getSavingByID($id);
		if ($saving->status != 'active') {
			$this->session->set_flashdata('error', lang("saving_cannot_add_transfer"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('amount', lang("amount"), 'required');
        if ($this->form_validation->run() == true) {
			$reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sav_tr',$saving->biller_id);
            $transaction_id = $this->savings_model->getLastTran();
			
			if ($this->Owner || $this->Admin || $GP['savings-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$amount = $this->input->post('amount');
			$transaction_fee = $this->input->post('transaction_fee');
			$to_account_no = $this->input->post('to_account_no');
			$saving_to = $this->savings_model->getSavingByID($to_account_no);
			
			if($saving_to){
				
				$saving_account = $this->savings_model->getBorrowerByID($saving->borrower_id);
				$saving_account_name = $saving_account->first_name .' '.$saving_account->last_name;
				
				$saving_account_to = $this->savings_model->getBorrowerByID($saving_to->borrower_id);
				$saving_account_to_name = $saving_account_to->first_name .' '.$saving_account_to->last_name;
				
				$payment[] = array(
					'date' => $date,
					'saving_id' => $saving->id,
					'reference_no' => $reference_no,
					'transaction_id' => $transaction_id,
					'transaction' => 'Transfer to '.strtoupper($saving_account_to_name),
					'transaction_type' => 'out',
					'amount' => $amount,
					'paid_by' => $this->input->post('paid_by'),
					'cheque_no' => $this->input->post('cheque_no'),
					'cc_no' => $this->input->post('pcc_no'),
					'cc_holder' => $this->input->post('pcc_holder'),
					'cc_month' => $this->input->post('pcc_month'),
					'cc_year' => $this->input->post('pcc_year'),
					'cc_type' => $this->input->post('pcc_type'),
					'note' => $this->input->post('note'),
					'created_by' => $this->session->userdata('user_id'),
					'type' => 'sent',
				);
			
				$payment[] = array(
					'date' => $date,
					'saving_id' => $saving_to->id,
					'reference_no' => $reference_no,
					'transaction_id' => $transaction_id,
					'transaction' => 'Transfer from '.strtoupper($saving_account_name),
					'transaction_type' => 'in',
					'amount' => $amount,
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
				);
			}
			
			if($transaction_fee > 0){
				$payment[] = array(
					'date' => $date,
					'saving_id' => $saving->id,
					'reference_no' => $reference_no,
					'transaction_id' => $transaction_id,
					'transaction' => 'Fee for Transfer',
					'transaction_type' => 'out',
					'amount' => $transaction_fee,
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
				);
			}
			
			// Valiation balance max for Account transfer to
			$saving_product_to=$this->savings_model->getSavingProductByID($saving_to->saving_product_id);
			$available_balance_to = $this->savings_model->getPaymentBalance($saving_to->id);
			$total_amount_to = $amount - $transaction_fee;
			if(($available_balance_to + $total_amount_to) > (double)$saving_product_to->balance_max){
				$this->session->set_flashdata('error', lang("available_balance_invalid"));
				$this->bpas->md();
			}
			
			// Valiation balance min for Account transfer
			$saving_product=$this->savings_model->getSavingProductByID($saving->saving_product_id);
			$available_balance = $this->savings_model->getPaymentBalance($id);
			$total_amount = $amount + $transaction_fee;
			if(($available_balance - $total_amount) < (double)$saving_product->balance_min){
				$this->session->set_flashdata('error', lang("available_balance_invalid"));
				$this->bpas->md();
			}
		}
		if ($this->form_validation->run() == true && $this->savings_model->addDeposit($payment)) {
			$this->session->set_flashdata('message', lang("transfer_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['payment_ref'] = '';
			$this->data['saving'] = $saving;
			$this->data['saving_accounts'] = $this->savings_model->getSavingAccounts();
			$this->data['saving_product'] = $this->savings_model->getSavingProductByID($saving->saving_product_id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'savings/add_transfer', $this->data);
        }
    }
	
	public function cancel_last_transaction($id = NULL)
    {
		$this->bpas->checkPermissions('edit');
		$saving = $this->savings_model->getSavingByID($id);
		if ($saving->status != 'active') {
			$this->session->set_flashdata('error', lang("saving_cannot_cancel_last_transaction"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('comment', lang("comment"), 'required');
        if ($this->form_validation->run() == true) {
			$comment = $this->input->post('comment');
			$transaction_id = $this->savings_model->getLastTranBySavingID($id);
			$payments = $this->savings_model->getPaymentByTran($transaction_id);
			if($payments){
				foreach($payments as $payment){
					$data[] = array(
						'date' => $payment->date,
						'reference_no'=> $payment->reference_no,
						'saving_id'=> $payment->saving_id,
						'transaction_id'=> $payment->transaction_id,
						'transaction'=> $payment->transaction,
						'amount'=> $payment->amount,
						'type'=> $payment->type,
						'note'=> $payment->note,
						'comment'=> $comment,
					);
				}
			}else{
				$this->session->set_flashdata('error', lang("saving_cannot_cancel_last_transaction"));
				$this->bpas->md();
			}
		}
		if ($this->form_validation->run() == true && $this->savings_model->cancelLastOperation($transaction_id, $data)) {
			$this->session->set_flashdata('message', lang("cancel_last_transaction_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['payment_ref'] = '';
			$this->data['saving'] = $saving;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['saving_product'] = $this->savings_model->getSavingProductByID($saving->saving_product_id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'savings/cancel_last_transaction', $this->data);
        }
    }
	
	public function close($id = NULL)
    {
		$this->bpas->checkPermissions('edit');
		$saving = $this->savings_model->getSavingByID($id);
		if ($saving->status != 'active') {
			$this->session->set_flashdata('error', lang("saving_cannot_close"));
			$this->bpas->md();
		}
		$this->form_validation->set_rules('reason', lang("reason"), 'required');
        if ($this->form_validation->run() == true) {
			$reason = $this->input->post('reason', true);
			$data = array(
				"reason" => $reason,
				"status" => "closed",
				"closed_by" => $this->session->userdata("user_id"),
				"closed_at" => date("Y-m-d H:i"),
			);
		}
		if ($this->form_validation->run() == true && $this->savings_model->closeSaving($id, $data)) {
			$this->session->set_flashdata('message', lang("saving_closed"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$this->data['saving'] = $saving;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['saving_product'] = $this->savings_model->getSavingProductByID($saving->saving_product_id);
			$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'savings/close', $this->data);
        }
    }
	
	public function combine_transactions_pdf($data = NULL)
    {
		$this->data['rows'] = $data;
        $inv_html = $this->load->view($this->theme . 'savings/combine_transactions_pdf', $this->data, true);
        $name = lang("transactions").time() . ".pdf";
        $html[] = array(
                'content' => $inv_html,
                'footer' => '',
            );
        $file = $this->bpas->generate_pdf($html, $name, false, false, false, false, false, 'P');
		if($file){
			admin_redirect(base_url($file));
		}
    }
	
}
