<?php defined('BASEPATH') or exit('No direct script access allowed');
class Payrolls extends MY_Controller
{
	public function __construct(){
		parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        $this->lang->admin_load('payrolls', $this->Settings->user_language);
		$this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->load->admin_model('payrolls_model');
        $this->load->admin_model("hr_model");
        $this->load->admin_model('accounts_model');	
        $this->load->admin_model('schools_model');	
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types 	= 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size 	= '10240';

        $this->spouses_reduction 	= 150000;
        $this->childs_reduction		= 150000;
        $this->accident_duction		= 0.8;
        $this->health_duction		= 2.6;
		$this->seniority_except_tax	= 2000000;
	}
	public function cellsToMergeByColsRow($start = NULL, $end = NULL, $row = NULL){
	    $merge = 'A1:A1';
	    if($start && $end && $row){
	        $start = PHPExcel_Cell::stringFromColumnIndex($start);
	        $end = PHPExcel_Cell::stringFromColumnIndex($end);
	        $merge = "$start{$row}:$end{$row}";

	    }

	    return $merge;
	}
	public function deductions()
	{
		$this->bpas->checkPermissions('deductions');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('deductions')));
		$meta = array('page_title' => lang('deductions'), 'bc' => $bc);
		$this->page_construct('payrolls/deductions', $meta, $this->data);
	}
	public function getDeductions()
	{	
		$this->bpas->checkPermissions('deductions');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_deduction") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_deduction/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_deduction') . "</a>";
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('payrolls/edit_deduction/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_deduction').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					pay_deductions.id as id, 
					pay_deductions.name,
					pay_deductions.value")
            ->from("pay_deductions")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function delete_deduction($id = null)
    {		
		$this->bpas->checkPermissions('deductions');
        if (isset($id) || $id != null){
        	 if ($this->payrolls_model->deleteDeduction($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('deduction_deleted')]);
				}
				$this->session->set_flashdata('message', lang('deduction_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	public function deduction_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->payrolls_model->deleteDeduction($id);
                    }
                    $this->session->set_flashdata('message', lang("deductions_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('deductions');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('value'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->payrolls_model->getDeductionByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->value);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'deductions_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function add_deduction()
	{
		$this->bpas->checkPermissions('deductions');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('value', lang("value"), 'required');
		if ($this->form_validation->run() == true){	
			$data = array(
					'name'	=> $this->input->post('name'),
					'value'	=> $this->input->post('value'),
				);
		}
		if ($this->form_validation->run() == true && $id = $this->payrolls_model->addDeduction($data)) {
			$this->session->set_flashdata('message', $this->lang->line("deduction_added"));
            admin_redirect("payrolls/deductions");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'payrolls/add_deduction', $this->data);	
		}	
	}
	public function edit_deduction($id = false)
	{
		$this->bpas->checkPermissions('deductions');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('value', lang("value"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
						'name'	=> $this->input->post('name'),
						'value'	=> $this->input->post('value'),
					);
		}
		if ($this->form_validation->run() == true && $this->payrolls_model->updateDeduction($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("deduction_updated"));
            admin_redirect("payrolls/deductions");
        }else{
			$this->data['id'] = $id;
			$this->data['row'] = $this->payrolls_model->getDeductionByID($id);
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'payrolls/edit_deduction', $this->data);	
		}	
	}
	public function additions()
	{
		$this->bpas->checkPermissions('additions');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('additions')));
		$meta = array('page_title' => lang('additions'), 'bc' => $bc);
		$this->page_construct('payrolls/additions', $meta, $this->data);
	}

	public function getAdditions()
	{	
		$this->bpas->checkPermissions('additions');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_addition") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_addition/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_addition') . "</a>";
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('payrolls/edit_addition/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_addition').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					pay_additions.id as id, 
					pay_additions.name,
					pay_additions.value")
            ->from("pay_additions")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}

	public function delete_addition($id = null)
    {		
		$this->bpas->checkPermissions('additions');
        if (isset($id) || $id != null){
        	 if ($this->payrolls_model->deleteAddition($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('addition_deleted')]);
				}
				$this->session->set_flashdata('message', lang('addition_deleted'));
				admin_redirect('welcome');
			}
        }
	}
	public function addition_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->payrolls_model->deleteAddition($id);
                    }
                    $this->session->set_flashdata('message', lang("additions_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('additions');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('value'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->payrolls_model->getAdditionByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->value);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'addition_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function add_addition()
	{
		$this->bpas->checkPermissions('additions');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('value', lang("value"), 'required');
		if ($this->form_validation->run() == true){	
			$data = array(
					'name'	=> $this->input->post('name'),
					'value'	=> $this->input->post('value'),
					'account'	=> $this->input->post('account'),
				);
		}
		if ($this->form_validation->run() == true && $id = $this->payrolls_model->addAddition($data)) {
			$this->session->set_flashdata('message', $this->lang->line("addition_added"));
            admin_redirect("payrolls/additions");
        }else{
			if($this->Settings->accounting == 1){
				$this->data['accounts'] = $this->site->getAccount(array('EX','OX'));
			}
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'payrolls/add_addition', $this->data);	
		}	
	}
	public function edit_addition($id = false)
	{
		$this->bpas->checkPermissions('deductions');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('value', lang("value"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
						'name'	=> $this->input->post('name'),
						'value'	=> $this->input->post('value'),
						'account'	=> $this->input->post('account'),
					);
		}
		if ($this->form_validation->run() == true && $this->payrolls_model->updateAddition($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("addition_updated"));
            admin_redirect("payrolls/additions");
        }else{
			$addition = $this->payrolls_model->getAdditionByID($id);
			if($this->Settings->accounting == 1){
				$this->data['accounts'] = $this->site->getAccount(array('EX','OX'),$addition->account);
			}
			$this->data['id'] = $id;
			$this->data['row'] = $addition;
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'payrolls/edit_addition', $this->data);	
		}	
	}


	
	public function get_positions(){
		$biller_id = $this->input->get('biller_id');
		$positions = $this->payrolls_model->getPositions($biller_id);
		echo json_encode($positions);
	}
	public function get_departments(){
		$biller_id = $this->input->get('biller_id');
		$departments = $this->payrolls_model->getDepartments($biller_id);
		echo json_encode($departments);
	}
	public function get_groups(){
		$department_id = $this->input->get('department_id');
		$groups = $this->payrolls_model->getGroups($department_id);
		echo json_encode($groups);
	}
	public function get_benefit_employees(){
		$biller_id = $this->input->get('biller_id');
		$position_id = $this->input->get('position_id');
		$department_id = $this->input->get('department_id');
		$group_id = $this->input->get('group_id');
		$y_month = explode("/",$this->input->get('month'));
		$month = $y_month[0];
		$year = $y_month[1];
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getBenefitEmployee($biller_id,$position_id,$department_id,$group_id,$month,$year,$edit_id);
		if($employees){
			$deductions = $this->payrolls_model->getDeductions();
			$additions = $this->payrolls_model->getAdditions();
			foreach($employees as $employee){
				$approve_additions = false;
				if($additions){
					$emp_additions = false;
					if(json_decode($employee->additions)){
						foreach(json_decode($employee->additions) as $index => $value){
							if (preg_match('/[0-9]+%/', $value, $matches)){
								$rate = explode("%", $matches[0]);
								$emp_additions[$index] = $this->bpas->formatDecimal($employee->net_salary * $rate[0] / 100);
							}else if(is_numeric($value)){
								$emp_additions[$index] = $value;
							}
						}
					}
					foreach($additions as $addition){
						$amount = 0;
						if(isset($emp_additions[$addition->id]) && $emp_additions[$addition->id]){
							$amount = $emp_additions[$addition->id];
						}
						$approve_additions[] = array("id"=>$addition->id,"name"=> $addition->name ,"value" => $amount);
					}
				}
				$approve_deductions = false;
				if($deductions){
					$emp_deductions = false;
					if(json_decode($employee->deductions)){
						foreach(json_decode($employee->deductions) as $index => $value){
							if (preg_match('/[0-9]+%/', $value, $matches)){
								$rate = explode("%", $matches[0]);
								$emp_deductions[$index] = $this->bpas->formatDecimal($employee->net_salary * $rate[0] / 100);
							}else if(is_numeric($value)){
								$emp_deductions[$index] = $value;
							}
							
						}
					}
					foreach($deductions as $deduction){
						$amount = 0;
						if(isset($emp_deductions[$deduction->id]) && $emp_deductions[$deduction->id]){
							$amount = $emp_deductions[$deduction->id];
						}
						$approve_deductions[] = array("id"=>$deduction->id,"name"=> $deduction->name ,"value" => $amount);
					}
				}
				$employee->approve_additions = $approve_additions;
				$employee->approve_deductions = $approve_deductions;
			}
		}
		echo json_encode($employees);
	}
	public function benefits($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('benefits')));
        $meta = array('page_title' => lang('benefits'), 'bc' => $bc);
        $this->page_construct('payrolls/benefits', $meta, $this->data);
	}
	public function getBenefits($biller_id = false){
		$this->bpas->checkPermissions("benefits");
        $edit_link = anchor('admin/payrolls/edit_benefit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_benefit'), ' class="edit_benefit"');
        $delete_link = "<a href='#' class='delete_benefit po' title='<b>" . $this->lang->line("delete_benefit") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_benefit/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_benefit') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_benefit']){
			$approve_link = "<a href='#' class='po approve_benefit' title='" . $this->lang->line("approve_benefit") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/approve_benefit/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_benefit') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_benefit' title='<b>" . $this->lang->line("unapprove_benefit") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('payrolls/unapprove_benefit/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_benefit') . "</a>";
		}
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_benefits.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_benefits').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_benefits').".month,'/',".$this->db->dbprefix('pay_benefits').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_benefits').".grand_cash_advanced,0) as grand_cash_advanced,
									IFNULL(".$this->db->dbprefix('pay_benefits').".grand_addition,0) as grand_addition,
									IFNULL(".$this->db->dbprefix('pay_benefits').".grand_deduction,0) as grand_deduction,	
									note,
									pay_benefits.status,
									attachment
									")
							->from("pay_benefits")
							->join("users","users.id = pay_benefits.created_by","left")
							;
		if ($biller_id) {
            $this->datatables->where("pay_benefits.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_benefits.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_benefits.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_benefit(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-benefits_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$grand_addition = 0;
			$grand_deduction = 0;
			$grand_cash_advanced = 0;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$total_addition = 0;
				$additions = false;
				if($_POST['addition'][$employee_id]){
					foreach($_POST['addition'][$employee_id] as $index => $value){
						$additions[] = array(
											"id"=>$index,
											"name"=>$_POST['addition_name'][$employee_id][$index],
											"value"=>$value
										);
						$total_addition += $value;				
					}
				}
				$total_deduction = 0;
				$deductions = false;
				if($_POST['deduction'][$employee_id]){
					foreach($_POST['deduction'][$employee_id] as $index => $value){
						$deductions[] = array(
											"id"=>$index,
											"name"=>$_POST['deduction_name'][$employee_id][$index],
											"value"=>$value
										);
						$total_deduction += $value;				
					}
				}
				$cash_advance_ids = null;
				if(isset($_POST['cash_advanced'][$r]) && $_POST['cash_advanced'][$r] > 0){
					$payback_amount = $_POST['cash_advanced'][$r];
					$cash_advances = $this->payrolls_model->getCashAdvanceByEmployee($employee_id, "approved");
					if($cash_advances){
						foreach($cash_advances as $cash_advance){
							$c_balance = $this->bpas->formatDecimal($cash_advance->amount - $cash_advance->paid);
							if($c_balance > 0){
								if($payback_amount >= $c_balance){
									$cash_advance_ids[] = array("cash_advance_id" => $cash_advance->id,"payback_amount" => $c_balance);
									$payback_amount = $payback_amount - $c_balance;
								}else if($payback_amount > 0){
									$cash_advance_ids[] = array("cash_advance_id" => $cash_advance->id,"payback_amount" => $payback_amount);
									$payback_amount = 0;
								}
							}
						}
						if($cash_advance_ids){
							$cash_advance_ids = json_encode($cash_advance_ids);
						}
					}
				}
				$items[] = array(
								"employee_id" => $employee_id,
								"additions" => ($additions ? json_encode($additions) : null),
								"deductions" => ($deductions ? json_encode($deductions) : null),
								"total_addition" => $total_addition,
								"total_deduction" => $total_deduction,
								"cash_advanced" => $_POST['cash_advanced'][$r],
								"cash_advance_ids" => $cash_advance_ids
							);
				$grand_addition += $total_addition;
				$grand_deduction += $total_deduction;
				$grand_cash_advanced += $_POST['cash_advanced'][$r];
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
				'grand_addition' => $grand_addition,
				'grand_deduction' => $grand_deduction,
				'grand_cash_advanced' => $grand_cash_advanced,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addBenefit($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("benefit_added"));          
			admin_redirect('payrolls/benefits');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['deductions'] = $this->payrolls_model->getDeductions();
			$this->data['additions'] = $this->payrolls_model->getAdditions();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/benefits'), 'page' => lang('benefits')), array('link' => '#', 'page' => lang('add_benefit')));
            $meta = array('page_title' => lang('add_benefit'), 'bc' => $bc);
            $this->page_construct('payrolls/add_benefit', $meta, $this->data);
        }
	}
	public function edit_benefit($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-benefits_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$grand_addition = 0;
			$grand_deduction = 0;
			$grand_cash_advanced = 0;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$total_addition = 0;
				$additions = false;
				if($_POST['addition'][$employee_id]){
					foreach($_POST['addition'][$employee_id] as $index => $value){
						$additions[] = array(
											"id"=>$index,
											"name"=>$_POST['addition_name'][$employee_id][$index],
											"value"=>$value
										);
						$total_addition += $value;				
					}
				}
				$total_deduction = 0;
				$deductions = false;
				if($_POST['deduction'][$employee_id]){
					foreach($_POST['deduction'][$employee_id] as $index => $value){
						$deductions[] = array(
											"id"=>$index,
											"name"=>$_POST['deduction_name'][$employee_id][$index],
											"value"=>$value
										);
						$total_deduction += $value;				
					}
				}
				$cash_advance_ids = null;
				if(isset($_POST['cash_advanced'][$r]) && $_POST['cash_advanced'][$r] > 0){
					$payback_amount = $_POST['cash_advanced'][$r];
					$cash_advances = $this->payrolls_model->getCashAdvanceByEmployee($employee_id, "approved");
					if($cash_advances){
						foreach($cash_advances as $cash_advance){
							$c_balance = $this->bpas->formatDecimal($cash_advance->amount - $cash_advance->paid);
							if($c_balance > 0){
								if($payback_amount >= $c_balance){
									$cash_advance_ids[] = array("cash_advance_id" => $cash_advance->id,"payback_amount" => $c_balance);
									$payback_amount = $payback_amount - $c_balance;
								}else if($payback_amount > 0){
									$cash_advance_ids[] = array("cash_advance_id" => $cash_advance->id,"payback_amount" => $payback_amount);
									$payback_amount = 0;
								}
							}
						}
						if($cash_advance_ids){
							$cash_advance_ids = json_encode($cash_advance_ids);
						}
					}
				}
				$items[] = array(
								"benefit_id" 		=> $id,
								"employee_id" 		=> $employee_id,
								"additions" 		=> ($additions ? json_encode($additions) : null),
								"deductions" 		=> ($deductions ? json_encode($deductions) : null),
								"total_addition" 	=> $total_addition,
								"total_deduction" 	=> $total_deduction,
								"cash_advanced" 	=> $_POST['cash_advanced'][$r],
								"cash_advance_ids" 	=> $cash_advance_ids
							);
				$grand_addition += $total_addition;
				$grand_deduction += $total_deduction;
				$grand_cash_advanced += $_POST['cash_advanced'][$r];
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' 			=> $date,
                'month' 		=> $month,
                'year' 			=> $year,
                'biller_id' 	=> $biller_id,
                'position_id' 	=> $position_id,
				'department_id' => $department_id,
				'group_id' 		=> $group_id,
				'note' 			=> $note,
				'status' 		=> $status,
				'grand_addition' 	=> $grand_addition,
				'grand_deduction' 	=> $grand_deduction,
				'grand_cash_advanced' => $grand_cash_advanced,
				'updated_by' 		=> $this->session->userdata('user_id'),
				'updated_at' 		=> date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateBenefit($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("benefit_updated"));          
			admin_redirect('payrolls/benefits');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$benefit = $this->payrolls_model->getBenefitByID($id);
			$this->data['benefit'] = $benefit;
			$this->data['benefit_items'] = $this->payrolls_model->getBenefitItems($id);
			$this->data['positions'] = $this->payrolls_model->getPositions($benefit->biller_id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($benefit->biller_id);
			if($benefit->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($benefit->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$this->data['deductions'] = $this->payrolls_model->getDeductions();
			$this->data['additions'] = $this->payrolls_model->getAdditions();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/benefits'), 'page' => lang('benefits')), array('link' => '#', 'page' => lang('edit_benefit')));
            $meta = array('page_title' => lang('edit_benefit'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_benefit', $meta, $this->data);
        }
	}
	public function delete_benefit($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteBenefit($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('benefit_deleted')]);
            }
            $this->session->set_flashdata('message', lang('benefit_deleted'));
            admin_redirect('welcome');
        }
    }
	function benefit_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_benefit');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$benefit = $this->payrolls_model->getBenefitByID($id);
						if($benefit->status == "pending"){
							$deleted = 1;
							$this->payrolls_model->deleteBenefit($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("benefit_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("benefit_cannot_delete"));
					}
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('benefit');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('cash_advanced'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('addition'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('deduction'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $benefit = $this->payrolls_model->getBenefitByID($id); 
						$user = $this->site->getUserByID($benefit->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($benefit->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $benefit->month."/".$benefit->year);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($benefit->grand_cash_advanced));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($benefit->grand_addition));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($benefit->grand_deduction));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($benefit->note));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($benefit->status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'benefit_list_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function approve_benefit($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateBenefitStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('benefit_approved')]);
            }
            $this->session->set_flashdata('message', lang('benefit_approved'));
            admin_redirect('welcome');
        }
    }
	public function unapprove_benefit($id = null){
        $this->bpas->checkPermissions("approve_benefit", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if($this->payrolls_model->checkBenefitSalaried($id)){
			$this->session->set_flashdata('error', lang('benefit_cannot_unapprove'));
        }elseif ($this->payrolls_model->updateBenefitStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('benefit_unapproved'));
        }
		admin_redirect('payrolls/benefits');
    }
	public function modal_view_benefit($id = false){
		$this->bpas->checkPermissions('benefits', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $benefit = $this->payrolls_model->getBenefitByID($id);
		$this->data['benefit'] = $benefit;
        $this->data['benefit_items'] = $this->payrolls_model->getBenefitItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($benefit->biller_id);
        $this->data['created_by'] = $this->site->getUser($benefit->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_benefit', $this->data);
	}
	public function benefits_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['users'] = $this->site->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('benefits_report')));
        $meta = array('page_title' => lang('benefits_report'), 'bc' => $bc);
        $this->page_construct('payrolls/benefits_report', $meta, $this->data);
	}
	public function getBenefitsReport($pdf = NULL, $xls = NULL)
    {
        $this->bpas->checkPermissions('benefits_report');
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_benefits').".date, '%Y-%m-%d %T') as date,
								companies.company,
								CONCAT(".$this->db->dbprefix('pay_benefits').".month,'/',".$this->db->dbprefix('pay_benefits').".year) as month,
								CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
								IFNULL(".$this->db->dbprefix('pay_benefits').".grand_cash_advanced,0) as grand_cash_advanced,
								IFNULL(".$this->db->dbprefix('pay_benefits').".grand_addition,0) as grand_addition,
								IFNULL(".$this->db->dbprefix('pay_benefits').".grand_deduction,0) as grand_deduction,
								note,
								pay_benefits.attachment,
								pay_benefits.status,
								pay_benefits.id as id")
						->from("pay_benefits")
						->join("companies","companies.id = pay_benefits.biller_id","left")
						->join("users","users.id = pay_benefits.created_by","left");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_benefits.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_benefits.biller_id', $biller);
            }
            if ($user) {
                $this->db->where('pay_benefits.created_by', $user);
            }
			if($y_month){
				$this->db->where('pay_benefits.year', $year);
				$this->db->where('pay_benefits.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_benefits.biller_id', $this->session->userdata('biller_id'));
			}
			
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('benefits_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));	
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('cash_advanced'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('addition'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('deduction'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));	
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->company);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->grand_cash_advanced));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->grand_addition));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->grand_deduction));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->status));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$filename = 'benefits_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_benefits').".date, '%Y-%m-%d %T') as date,
									companies.company,
									CONCAT(".$this->db->dbprefix('pay_benefits').".month,'/',".$this->db->dbprefix('pay_benefits').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_benefits').".grand_cash_advanced,0) as grand_cash_advanced,
									IFNULL(".$this->db->dbprefix('pay_benefits').".grand_addition,0) as grand_addition,
									IFNULL(".$this->db->dbprefix('pay_benefits').".grand_deduction,0) as grand_deduction,
									note,
									pay_benefits.attachment,
									pay_benefits.status,
									pay_benefits.id as id")
							->from("pay_benefits")
							->join("companies","companies.id = pay_benefits.biller_id","left")
							->join("users","users.id = pay_benefits.created_by","left");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_benefits.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_benefits.biller_id', $biller);
            }
            if ($user) {
                $this->datatables->where('pay_benefits.created_by', $user);
            }
			if($y_month){
				$this->datatables->where('pay_benefits.year', $year);
				$this->datatables->where('pay_benefits.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_benefits.biller_id', $this->session->userdata('biller_id'));
			}

            echo $this->datatables->generate();
        }
    }
	public function benefit_details_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('benefit_details_report')));
        $meta = array('page_title' => lang('benefit_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/benefit_details_report', $meta, $this->data);
	}
	public function getBenefitDetailsReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('benefit_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_benefits').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_benefits').".month,'/',".$this->db->dbprefix('pay_benefits').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								IFNULL(".$this->db->dbprefix('pay_benefit_items').".cash_advanced,0) as cash_advanced,
								IFNULL(".$this->db->dbprefix('pay_benefit_items').".total_addition,0) as total_addition,
								IFNULL(".$this->db->dbprefix('pay_benefit_items').".total_deduction,0) as total_deduction,
								IFNULL(".$this->db->dbprefix('pay_benefit_items').".additions,0) as additions,
								IFNULL(".$this->db->dbprefix('pay_benefit_items').".deductions,0) as deductions,
								pay_benefits.status,
								pay_benefits.id as id")
						->from("pay_benefits")
						->join("pay_benefit_items","pay_benefit_items.benefit_id = pay_benefits.id","inner")
						->join("hr_employees","hr_employees.id = pay_benefit_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_benefit_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_benefit_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_benefits.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_benefits.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_benefit_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_benefits.year', $year);
				$this->db->where('pay_benefits.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_benefits.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
			$title = ['I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
				'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
				'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK','CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
				'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK','DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ',
				'EA','EB','EC','ED','EE','EF','EG','EH','EI','EJ','EK','EL','EM','EN','EO','EP','EQ','ER','ES','ET','EU','EV','EW','EX','EY','EZ','FA','FB','FC','FD','FE'
			];
            if (!empty($data)) {
            	$additions 	= $this->payrolls_model->getAdditions($biller);
				$deductions = $this->payrolls_model->getDeductions($biller);

            	$th_addition = "";
				$th_addition_sub = "";
				if($additions){
					$colspan = 0;
					foreach($additions as $addition){
						$th_addition_sub .= '<th>'.$addition->name.'</th>';
						$colspan++;
					}

					$th_addition =  '<th colspan="'.$colspan.'">'.lang("ប្រាក់បន្ថែម").'</th>';
				}
				
				$th_deduction = "";
				$th_deduction_sub = "";
				if($deductions){
					$colspan = 0;
					foreach($deductions as $deduction){
						$th_deduction_sub .= '<th>'.$deduction->name.'</th>';
						$colspan++;
					}
					$th_deduction =  '<th colspan="'.$colspan.'">'.lang("ប្រាក់កាត់").'</th>';
				}

                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
                $this->excel->getActiveSheet()->setTitle(lang('benefit_details_report'));

				$this->excel->getActiveSheet()->mergeCells('A1:A2');
				$this->excel->getActiveSheet()->mergeCells('B1:B2');
				$this->excel->getActiveSheet()->mergeCells('C1:C2');
				$this->excel->getActiveSheet()->mergeCells('D1:D2');
				$this->excel->getActiveSheet()->mergeCells('E1:E2');
				$this->excel->getActiveSheet()->mergeCells('F1:F2');
				$this->excel->getActiveSheet()->mergeCells('G1:G2');
				$this->excel->getActiveSheet()->mergeCells('H1:H2');

				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('cash_advanced'));
				$additions = $this->payrolls_model->getAdditions();
				$deductions = $this->payrolls_model->getDeductions();
				// $this->excel->getActiveSheet()->SetCellValue('I1', lang('addition'));


				// $this->excel->getActiveSheet()->SetCellValue('J1', lang('deduction'));
				// $this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$add_ignor = array(1,8,9,10);
				$add_ignor = array(0);
					$decuct_ignor = array();
					for ($i=0; $i < sizeof($additions)-sizeof($add_ignor); $i++) { 
						
						if($i == 0){
							$this->excel->getActiveSheet()->SetCellValue($title[$i].'1', lang('addition'));
							$this->excel->getActiveSheet()->mergeCells($title[$i].'1:'.$title[sizeof($additions)-$i-1-sizeof($add_ignor)].'1');
							$this->excel->getActiveSheet()->getStyle($title[$i].'1:'.$title[sizeof($additions)-$i-1-sizeof($add_ignor)].'1')->applyFromArray($style);
						}
					}
					$p = 0;
					foreach ($additions as $key => $value) {
						if(!in_array($value->id, $add_ignor)){
							$this->excel->getActiveSheet()->SetCellValue($title[$p].'2', $value->name);
							$p++;
						}
					}
					$k = $i;
					for ($j = $i; $j < $k + sizeof($deductions); $j++) { 
						if($j == $k){
							$this->excel->getActiveSheet()->SetCellValue($title[$k].'1', lang('deduction'));
							$this->excel->getActiveSheet()->mergeCells($title[$k].'1:'.$title[$k-1+ sizeof($deductions)].'1');
							$this->excel->getActiveSheet()->getStyle($title[$k].'1:'.$title[$k-1+ sizeof($deductions)].'1')->applyFromArray($style);
						}
					} 
					$q = $i;
					foreach ($deductions as $key => $value) {
						if(!in_array($value->id, $decuct_ignor)){
							$this->excel->getActiveSheet()->SetCellValue($title[$q].'2', $value->name);
							$q++;
						}
					}
					$r= $j; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('status'));
                $row = 3; $total = 0;
				$s= $j;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->formatDecimal($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->cash_advanced));
					// $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->total_addition));
					// $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->total_deduction));
					// $this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
					$data_additions = json_decode($data_row->additions);
					$data_deductions = json_decode($data_row->deductions);
					
					$ip = 0;
					foreach ($additions as $main_key => $main_value) {
						foreach ($data_additions as $key => $value) {
							if(!in_array($main_value->id, $add_ignor) && $value->id == $main_value->id){
								$this->excel->getActiveSheet()->SetCellValue($title[$ip] .$row, $value->value);
								$ip++;
							}
						}
					}
					$iq = $ip;
					foreach ($deductions as $main_key => $main_value) {
						foreach ($data_deductions as $key => $value) {
							if(!in_array($main_value->id, $decuct_ignor) && $value->id == $main_value->id){
								$this->excel->getActiveSheet()->SetCellValue($title[$iq] . $row, $value->value);
								$iq++;
							}
						}
					}
					// var_dump($s);
					// var_dump($title[$s]);
					if(isset($title[$s])){
						$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, lang($data_row->status));
					}
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$filename = 'benefit_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_benefits').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_benefits').".month,'/',".$this->db->dbprefix('pay_benefits').".year) as month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_benefit_items').".cash_advanced,0) as cash_advanced,
									IFNULL(".$this->db->dbprefix('pay_benefit_items').".total_addition,0) as total_addition,
									IFNULL(".$this->db->dbprefix('pay_benefit_items').".total_deduction,0) as total_deduction,
									pay_benefits.status,
									pay_benefits.id as id")
							->from("pay_benefits")
							->join("pay_benefit_items","pay_benefit_items.benefit_id = pay_benefits.id","inner")
							->join("hr_employees","hr_employees.id = pay_benefit_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_benefit_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_benefit_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_benefits.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_benefits.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_benefit_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_benefits.year', $year);
				$this->datatables->where('pay_benefits.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_benefits.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function index($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salaries')));
        $meta = array('page_title' => lang('salaries'), 'bc' => $bc);
        $this->page_construct('payrolls/index', $meta, $this->data);
	}
	public function getSalaries($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$view_salary_link = anchor('admin/payrolls/modal_view_salary/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_salary'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_salary" data-target="#myModal"');
		$payment_link = anchor('admin/payrolls/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add_payment"');
        $edit_link = anchor('admin/payrolls/edit_salary/$1', '<i class="fa fa-edit"></i> ' . lang('edit_salary'), ' class="edit_salary"');
        $add_nssf_link = anchor('admin/payrolls/add_contribution_payment/$1', '<i class="fa fa-money"></i> ' . lang('contribution_payment'), ' class="nssf_payment"');

        $email_link           = anchor('admin/payrolls/email_payslip/$1', '<i class="fa fa-envelope"></i> ' . lang('email_payslip'), 'data-toggle="modal" class="email_payslip" data-backdrop="static" data-target="#myModal"');

        $delete_link = "<a href='#' class='delete_salary po' title='<b>" . $this->lang->line("delete_salary") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('payrolls/delete_salary/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_salary') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_salary']){
			$approve_link = "<a href='#' class='po approve_salary' title='" . $this->lang->line("approve_salary") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success' href='" . admin_url('payrolls/approve_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_salary') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_salary' title='<b>" . $this->lang->line("unapprove_salary") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('payrolls/unapprove_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_salary') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_salary_link . '</li>
						<li class="add_payment">' . $payment_link . '</li>
						<li>' . $approve_link . '</li>
						<li class="unapprove_salary">' . $unapprove_link . '</li>
	                	<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_salaries.id as id, 
				DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
				CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
				CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_gross_salary,0) as total_gross_salary,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_overtime,0) as total_overtime,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_addition,0) as total_addition,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_cash_advanced,0) as total_cash_advanced,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_payment,0) as total_tax_payment,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_salary,0) as total_net_salary,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) as total_net_pay,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_paid,0) as total_tax_paid,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_salary_paid,0) as total_salary_paid,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) as total_paid,
				(IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) - IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0)) as total_balance,
				pay_salaries.status,
				pay_salaries.payment_status,
				pay_salaries.attachment")
		->from("pay_salaries")
		->join("users","users.id = pay_salaries.created_by","left");
		$this->datatables->where("pay_salaries.type",'monthly');
		if ($biller_id) {
            $this->datatables->where("pay_salaries.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_salaries.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_salaries.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	function email_payslip($salary_id){
		$salary = $this->payrolls_model->getSalaryByID($id);
	}
	public function get_salary_employees(){
		$biller_id = $this->input->get('biller_id') ? $this->input->get('biller_id') : false;
		$position_id = $this->input->get('position_id') ? $this->input->get('position_id') : false;
		$department_id = $this->input->get('department_id') ? $this->input->get('department_id') : false;
		$group_id = $this->input->get('group_id') ? $this->input->get('group_id') : false;

		$currency   = $this->site->getCurrencyByCode("KHR");
		$kh_rate 	= $this->input->get('kh_rate') ? $this->input->get('kh_rate') : $currency->rate;
		$baht_rate 	= $this->input->get('baht_rate') ? $this->input->get('baht_rate') : $this->site->getCurrencyByCode("BAHT")->rate;
		$nssf_rate 	= $this->input->get('nssf_rate') ? $this->input->get('nssf_rate') : false;

		$y_month = explode("/",$this->input->get('month'));
		$month = $y_month[0];
		$year = $y_month[1];
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getSalaryEmployee($biller_id,$position_id,$department_id,$group_id,$month,$year,$edit_id);
		if($employees){
			$hour = 8;
			$emp_benefits = false;
			$additions = $this->payrolls_model->getAdditions($biller_id);
			$deductions = $this->payrolls_model->getDeductions($biller_id);
			$employee_benefits = $this->payrolls_model->getBenefitedEmployee($month,$year,"approved");
			if($employee_benefits){
				foreach($employee_benefits as $employee_benefit){
					$emp_benefits[$employee_benefit->employee_id] = $employee_benefit;
				}
			}

			foreach($employees as $employee){
				$absent_amount = 0;
				$str_absent = $employee->absent_rate;
				if (preg_match('/[0-9]+%/', $str_absent, $matches)){
					$rate = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day);
					$absent_amount = $this->bpas->formatDecimal(($employee->absent * $per_fee) * $rate[0] / 100);
				}else if(is_numeric($str_absent)){
					$absent_amount = $this->bpas->formatDecimal($employee->absent * $str_absent);
				}
				$permission_amount = 0;
				$str_permission = $employee->permission_rate;
				if (preg_match('/[0-9]+%/', $str_permission, $matches)){
					$rate = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day);
					$permission_amount = ($employee->permission * $per_fee) * $rate[0] / 100;
					
				}else if(is_numeric($str_permission)){
					$permission_amount = ($employee->permission * $str_permission);
				}
				$late_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->late_early_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$late_amount = ($employee->late * $per_fee) * $percent[0] / 100;
				}else{
					$late_amount = ($employee->late * $employee->late_early_rate);
				}
				$cash_advanced = 0;
				$deduction = 0;
				$addition = 0;
				$deduction_amount = 0;
				$addition_amount = 0;
				$emp_additions = false;
				$emp_deductions = false;
				$approve_additions = false;
				$approve_deductions = false;

				if(isset($emp_benefits[$employee->employee_id])){
					$cash_advanced = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->cash_advanced);
					$deduction = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_deduction);
					$addition = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_addition);

					$deduction_amount = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_deduction);
					$addition_amount = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_addition);

					if(json_decode($emp_benefits[$employee->employee_id]->additions)){
						foreach(json_decode($emp_benefits[$employee->employee_id]->additions) as $emp_benefit){
							$emp_additions[$emp_benefit->id] = $emp_benefit->value;
						}
					}
					if(json_decode($emp_benefits[$employee->employee_id]->deductions)){
						foreach(json_decode($emp_benefits[$employee->employee_id]->deductions) as $emp_benefit){
							$emp_deductions[$emp_benefit->id] = $emp_benefit->value;
						}
					}
				}
				$seniority_payment = [];
				$severance 			=[];
				$indemnity 			=[];
				if($additions){
					foreach($additions as $row){
						$amount = 0;
						if(isset($emp_additions[$row->id]) && $emp_additions[$row->id]){
							$amount = $emp_additions[$row->id];
						}
						$approve_additions[] = array("id"=>$row->id,"name"=> $row->name ,"value" => $amount);
						///------manual------
						if($row->name =='Seniority'){
							$seniority_payment []= $amount;
						}
						if($row->name =='Severance'){
							$severance []= $amount;
						}
						if($row->name =='Indemnity'){
							$indemnity []= $amount;
						}
						//--------close manual----
					}
					$seniority_payment  = $seniority_payment[0];
					$severance 			= $severance[0];
					$indemnity 			= $indemnity[0];
				}else{
					$seniority_payment  = 0;
					$severance 			= 0;
					$indemnity 			= 0;
				}

				if($deductions){
					foreach($deductions as $row){
						$amount = 0;
						if(isset($emp_deductions[$row->id]) && $emp_deductions[$row->id]){
							$amount = $emp_deductions[$row->id];
						}
						$approve_deductions[] = array("id"=>$row->id,"name"=> $row->name ,"value" => $amount);
					}
				}
				
				$normal_ot_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->normal_ot_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$normal_ot_amount = $this->bpas->formatDecimal(($employee->normal_ot * $per_fee) * $percent[0] / 100);
				}else{
					$normal_ot_amount = $this->bpas->formatDecimal($employee->normal_ot * $employee->normal_ot_rate);
				}

				$weekend_ot_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->weekend_ot_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$weekend_ot_amount = $this->bpas->formatDecimal(($employee->weekend_ot * $per_fee) * $percent[0] / 100);
				}else{
					$weekend_ot_amount = $this->bpas->formatDecimal($employee->weekend_ot * $employee->weekend_ot_rate);
				}
				$holiday_ot_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->holiday_ot_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$holiday_ot_amount = $this->bpas->formatDecimal(($employee->holiday_ot * $per_fee) * $percent[0] / 100);
				}else{
					$holiday_ot_amount = $this->bpas->formatDecimal($employee->holiday_ot * $employee->holiday_ot_rate);
				}
				$overtime = $normal_ot_amount + $weekend_ot_amount + $holiday_ot_amount;

				$employee->absent_amount 	= $absent_amount;
				$employee->permission_amount= $permission_amount;
				$employee->late_amount 		= $late_amount;
				$employee->cash_advanced 	= $cash_advanced;
				$employee->deduction 		= $deduction;
				$employee->addition 		= $addition;
				$employee->overtime 		= $overtime;
				$employee->approve_additions= $approve_additions;
				$employee->addition_amount 	= $addition_amount; // - seniority temporary
				$employee->approve_deductions = $approve_deductions;
				$employee->deduction_amount = $deduction_amount;
					//----------------
					if($seniority_pay){ // auto
						$gross_salary 			= $this->bpas->formatDecimal(($employee->basic_salary+ ($addition_amount) + $overtime) - ($absent_amount + $permission_amount + $late_amount + $deduction_amount));
					}else{ //manual
						$gross_salary 			= $this->bpas->formatDecimal(($employee->basic_salary+ ($addition_amount-$seniority_payment-$severance-$indemnity) + $overtime) - ($absent_amount + $permission_amount + $late_amount + $deduction_amount)+ $seniority_payment);
					}
					// baht to dollar
					if($this->Settings->default_currency=='BAHT'){
						$gross_salary = $this->bpas->formatDecimal($gross_salary / $baht_rate);
					}
					
					$nssf_gross_salary_riel = ($gross_salary * $nssf_rate);
					//---------------
				$employee->nssf_salary_usd 	= $gross_salary;
				$employee->nssf_salary_riel = $nssf_gross_salary_riel;
					//----------nssf-----------
					$pension_deduct_percent = $employee->pension;
					$pension_deduct = 0;$pension_by_staff =0;$pension_by_company=0;
					$contributory_nssf=0;$Accident_NSSF=0;$Health_NSSF=0;
					if($employee->nssf > 0 && $nssf_gross_salary_riel > 0){						
						if($nssf_gross_salary_riel < 400000){ //400,000
							$contributory_nssf 	= 400000;
							$pension_by_staff 	= round(($contributory_nssf * $pension_deduct_percent) / 100);
							$pension_by_company = $pension_by_staff; //2% also
						}else if($nssf_gross_salary_riel > 1200000){ //1,200,000
							$contributory_nssf 	= 1200000;
							$pension_by_staff 	= round(($contributory_nssf * $pension_deduct_percent) / 100);
							$pension_by_company = $pension_by_staff; //2% also
						}else{
							$contributory_nssf 	= round($nssf_gross_salary_riel);//real salary
						 	$pension_by_staff 	= round(($nssf_gross_salary_riel * $pension_deduct_percent) / 100);
						 	$pension_by_company = $pension_by_staff; //2% also
						}
						$pension_deduct = $this->bpas->formatDecimal($pension_by_staff / $nssf_rate);

						if($employee->age > 60){
							$pension_by_staff 	=0;
							$pension_by_company =0;
						}
						//-------------nssf-------
						$Accident_NSSF  = ($contributory_nssf *  $this->accident_duction)/100;
						$Health_NSSF	= ($contributory_nssf * $this->health_duction)/100;
						//------------close nssf-=-----------
					}
					//---------close nssf----------
				$employee->contributory_nssf  = $contributory_nssf;
				$employee->pension_by_staff   = $pension_by_staff; //$pension;
				$employee->pension_by_company = $pension_by_company;
				$employee->health_nssf 		  = $Health_NSSF;
				$employee->accident_nssf 	  = $Accident_NSSF;

					//-------------seniority-------------
					$except_seniority_paid	= $this->seniority_except_tax;
					if($seniority_pay){ //auto
						$seniority_payment 		= $this->payrolls_model->calculate_seniority($biller_id,$employee->employee_id,$month,$year,$gross_salary);
					}
					//$seniority_payment
					if($this->Settings->default_currency=='BAHT'){
						// baht -> dollar
						$seniority_payment =  $seniority_payment ? ($seniority_payment / $baht_rate) :0;
						// dollar -> riel
						$seniority_payment_riel = $seniority_payment ? ($seniority_payment * $kh_rate) :0;
					}else{
						$seniority_payment_riel = $seniority_payment ? ($seniority_payment * $kh_rate) :0;
					}
					$seniority_paid = ($seniority_payment_riel > $except_seniority_paid)? ($seniority_payment_riel - $except_seniority_paid):0;

				$employee->seniority 		= $seniority_payment;//$seniority_payment dollar;
				$employee->seniority_response_tax 	= $seniority_paid ? $this->bpas->formatDecimal($seniority_paid/$kh_rate):0; // dollar
					//-------------close seniority--------
					if($seniority_pay){ //auto
						$severance 		= $this->payrolls_model->calculate_severance($biller_id,$employee->employee_id,$month,$year,$gross_salary);
					}
					//$severance;
					if($this->Settings->default_currency=='BAHT'){
						// baht -> dollar
						$severance =  $severance ? ($severance / $baht_rate) :0;
					}
				$employee->severance 	= $severance;
					$employee->severance_riel		= $this->bpas->formatDecimal(round($severance*$kh_rate));
					if($this->Settings->default_currency=='BAHT'){
						// baht -> dollar
						$indemnity =  $indemnity ? ($indemnity / $baht_rate) :0;
					}
				$employee->indemnity 	= $indemnity;
					$employee->indemnity_riel 	= $this->bpas->formatDecimal(round($indemnity*$kh_rate));
					$gross_salary = $this->bpas->formatDecimal($employee->nssf_salary_usd-$pension_deduct +$employee->seniority_response_tax);

				$employee->gross_salary 	  = $gross_salary;
				$employee->gross_salary_riel  = $this->bpas->formatDecimal(round($employee->gross_salary*$kh_rate));

					$spouses           = $this->hr_model->getSpouseMemberByEmployeeID($employee->employee_id);
			        $spouses           = $spouses?count($spouses):0;
			        $spouses_reduction = $spouses * $this->spouses_reduction;
				$employee->spouse 			= $spouses;
					$childs            = $this->hr_model->getChildrenMemberByEmployeeID($employee->employee_id);
			        $childs            = $childs?count($childs):0;
			        $childs_reduction  = $childs * $this->childs_reduction;
				$employee->children 		= $childs;
				$employee->spouse_children_reduction = $spouses_reduction + $childs_reduction;
				$employee->Taxbasesalary 	= $employee->gross_salary_riel-$employee->spouse_children_reduction;

					//=======tax calculate=======//
					$tax_declaration = $employee->Taxbasesalary;
					if($employee->salary_tax > 0){
						$tax_declaration = $this->bpas->formatDecimal($employee->salary_tax * $kh_rate);
					}
					$tax_calculation = $this->site->getSalaryTax($employee->employee_id,$tax_declaration);
					$net_tax 			= $tax_calculation ? $tax_calculation['tax_on_salary'] : 0;
					$net_tax_riel 		= round($tax_calculation ? $tax_calculation['tax_on_salary_riel'] : 0);
					$tax_payment = ($employee->self_tax == 1 ? $net_tax : 0);
					//=====end tax calculate=====//

				$employee->tax_declaration 	= $tax_declaration;
				$employee->tax_payment 		= $net_tax;
				$employee->tax_payment_riel	= $net_tax_riel;
					$net_salary = $this->bpas->formatDecimal($gross_salary-$tax_payment+$seniority_payment-$employee->seniority_response_tax);
					$pre_salary = $employee->pre_salary;

					$net_pay = $net_salary - ($pre_salary + $cash_advanced) + $severance+$indemnity;
				$employee->self_tax 		= ($employee->self_tax == 1) ? lang('yes') : lang('no');
					if($this->Settings->default_currency=='BAHT'){
						$net_salary =  ($net_salary * $baht_rate);
					}
				$employee->net_salary 		= $net_salary;
					if($this->Settings->default_currency=='BAHT'){
						$net_pay =  ($net_pay * $baht_rate);
					}
				$employee->net_pay 			= $net_pay;
			}
		}
		echo json_encode($employees);
	}
	public function salaries_daily($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salaries')));
        $meta = array('page_title' => lang('salaries'), 'bc' => $bc);
        $this->page_construct('payrolls/salaries_daily', $meta, $this->data);
	}
	public function getDailySalaries($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$view_salary_link = anchor('admin/payrolls/modal_view_salary/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_salary'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_salary" data-target="#myModal"');
		$payment_link = anchor('admin/payrolls/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add_payment"');
        $edit_link = anchor('admin/payrolls/edit_daily_salary/$1', '<i class="fa fa-edit"></i> ' . lang('edit_daily_salary'), ' class="edit_salary"');
   

        $email_link           = anchor('admin/payrolls/email_payslip/$1', '<i class="fa fa-envelope"></i> ' . lang('email_payslip'), 'data-toggle="modal" class="email_payslip" data-backdrop="static" data-target="#myModal"');

        $delete_link = "<a href='#' class='delete_salary po' title='<b>" . $this->lang->line("delete_daily_salary") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('payrolls/delete_daily_salary/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_daily_salary') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_salary']){
			$approve_link = "<a href='#' class='po approve_salary' title='" . $this->lang->line("approve_salary") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success' href='" . admin_url('payrolls/approve_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_salary') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_salary' title='<b>" . $this->lang->line("unapprove_salary") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('payrolls/unapprove_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_salary') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_salary_link . '</li>
						<li class="add_payment">' . $payment_link . '</li>
						<li>' . $approve_link . '</li>
						<li class="unapprove_salary">' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_salaries.id as id, 
				DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
				CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
				CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_gross_salary,0) as total_gross_salary,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_overtime,0) as total_overtime,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_addition,0) as total_addition,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_cash_advanced,0) as total_cash_advanced,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_payment,0) as total_tax_payment,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_salary,0) as total_net_salary,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) as total_net_pay,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_paid,0) as total_tax_paid,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_salary_paid,0) as total_salary_paid,
				IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) as total_paid,
				(IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) - IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0)) as total_balance,
				pay_salaries.status,
				pay_salaries.payment_status,
				pay_salaries.attachment")
		->from("pay_salaries")
		->join("users","users.id = pay_salaries.created_by","left");
		$this->datatables->where("pay_salaries.type",'daily');
		if ($biller_id) {
            $this->datatables->where("pay_salaries.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_salaries.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_salaries.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	public function get_salary_daily_employees(){
		$biller_id 		= $this->input->get('biller_id') ? $this->input->get('biller_id') : false;
		$position_id 	= $this->input->get('position_id') ? $this->input->get('position_id') : false;
		$department_id  = $this->input->get('department_id') ? $this->input->get('department_id') : false;
		$group_id 	= $this->input->get('group_id') ? $this->input->get('group_id') : false;
		$project_id = $this->input->get('project_id') ? $this->input->get('project_id') : false;
		$currency   = $this->site->getCurrencyByCode("KHR");
		$kh_rate 	= $this->input->get('kh_rate') ? $this->input->get('kh_rate') : $currency->rate;
		$baht_rate 	= $this->input->get('baht_rate') ? $this->input->get('baht_rate') : $this->site->getCurrencyByCode("BAHT")->rate;
		$nssf_rate 	= $this->input->get('nssf_rate') ? $this->input->get('nssf_rate') : false;

		$y_month = explode("/",$this->input->get('month'));
		$month = $y_month[0];
		$year = $y_month[1];

		$edit_id  	= $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getSalaryEmployeeDaily($biller_id,$position_id,$department_id,$group_id,$project_id,$month,$year,$edit_id);
		if($employees){
			$hour = 8;
			$emp_benefits = false;
			$additions = $this->payrolls_model->getAdditions($biller_id);
			$deductions = $this->payrolls_model->getDeductions($biller_id);
			$employee_benefits = $this->payrolls_model->getBenefitedEmployee($month,$year,"approved");
			if($employee_benefits){
				foreach($employee_benefits as $employee_benefit){
					$emp_benefits[$employee_benefit->employee_id] = $employee_benefit;
				}
			}

			foreach($employees as $employee){
				$absent_amount = 0;
				$str_absent = $employee->absent_rate;
				if (preg_match('/[0-9]+%/', $str_absent, $matches)){
					$rate = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day);
					$absent_amount = $this->bpas->formatDecimal(($employee->absent * $per_fee) * $rate[0] / 100);
				}else if(is_numeric($str_absent)){
					$absent_amount = $this->bpas->formatDecimal($employee->absent * $str_absent);
				}
				$permission_amount = 0;
				$str_permission = $employee->permission_rate;
				if (preg_match('/[0-9]+%/', $str_permission, $matches)){
					$rate = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day);
					$permission_amount = ($employee->permission * $per_fee) * $rate[0] / 100;
					
				}else if(is_numeric($str_permission)){
					$permission_amount = ($employee->permission * $str_permission);
				}
				$late_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->late_early_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$late_amount = ($employee->late * $per_fee) * $percent[0] / 100;
				}else{
					$late_amount = ($employee->late * $employee->late_early_rate);
				}
				$cash_advanced = 0;
				$deduction = 0;
				$addition = 0;
				$deduction_amount = 0;
				$addition_amount = 0;
				$emp_additions = false;
				$emp_deductions = false;
				$approve_additions = false;
				$approve_deductions = false;

				if(isset($emp_benefits[$employee->employee_id])){
					$cash_advanced = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->cash_advanced);
					$deduction = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_deduction);
					$addition = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_addition);

					$deduction_amount = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_deduction);
					$addition_amount = $this->bpas->formatDecimal($emp_benefits[$employee->employee_id]->total_addition);

					if(json_decode($emp_benefits[$employee->employee_id]->additions)){
						foreach(json_decode($emp_benefits[$employee->employee_id]->additions) as $emp_benefit){
							$emp_additions[$emp_benefit->id] = $emp_benefit->value;
						}
					}
					if(json_decode($emp_benefits[$employee->employee_id]->deductions)){
						foreach(json_decode($emp_benefits[$employee->employee_id]->deductions) as $emp_benefit){
							$emp_deductions[$emp_benefit->id] = $emp_benefit->value;
						}
					}
				}
				$seniority_payment 	= [];
				$severance 			= [];
				$indemnity 			= [];
				if($additions){
					foreach($additions as $row){
						$amount = 0;
						if(isset($emp_additions[$row->id]) && $emp_additions[$row->id]){
							$amount = $emp_additions[$row->id];
						}
						$approve_additions[] = array("id"=>$row->id,"name"=> $row->name ,"value" => $amount);
						///------manual------
						if($row->name =='Seniority'){
							$seniority_payment []= $amount;
						}
						if($row->name =='Severance'){
							$severance []= $amount;
						}
						if($row->name =='Indemnity'){
							$indemnity []= $amount;
						}
						//--------close manual----
					}
					$seniority_payment  = $seniority_payment[0];
					$severance 			= $severance[0];
					$indemnity 			= $indemnity[0];
				}else{
					$seniority_payment  = 0;
					$severance 			= 0;
					$indemnity 			= 0;
				}

				if($deductions){
					foreach($deductions as $row){
						$amount = 0;
						if(isset($emp_deductions[$row->id]) && $emp_deductions[$row->id]){
							$amount = $emp_deductions[$row->id];
						}
						$approve_deductions[] = array("id"=>$row->id,"name"=> $row->name ,"value" => $amount);
					}
				}
				
				$normal_ot_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->normal_ot_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$normal_ot_amount = $this->bpas->formatDecimal(($employee->normal_ot * $per_fee) * $percent[0] / 100);
				}else{
					$normal_ot_amount = $this->bpas->formatDecimal($employee->normal_ot * $employee->normal_ot_rate);
				}

				$weekend_ot_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->weekend_ot_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$weekend_ot_amount = $this->bpas->formatDecimal(($employee->weekend_ot * $per_fee) * $percent[0] / 100);
				}else{
					$weekend_ot_amount = $this->bpas->formatDecimal($employee->weekend_ot * $employee->weekend_ot_rate);
				}
				$holiday_ot_amount = 0;
				if (preg_match('/[0-9]+%/', $employee->holiday_ot_rate, $matches)){
					$percent = explode("%", $matches[0]);
					$per_fee = ($employee->basic_salary / $employee->working_day) / $hour;
					$holiday_ot_amount = $this->bpas->formatDecimal(($employee->holiday_ot * $per_fee) * $percent[0] / 100);
				}else{
					$holiday_ot_amount = $this->bpas->formatDecimal($employee->holiday_ot * $employee->holiday_ot_rate);
				}
				$overtime = $normal_ot_amount + $weekend_ot_amount + $holiday_ot_amount;

				$employee->approved_att_id  = $employee->approved_att_id;

				$employee->absent_amount 	= $absent_amount;
				$employee->permission_amount= $permission_amount;
				$employee->late_amount 		= $late_amount;
				$employee->cash_advanced 	= $cash_advanced;
				$employee->deduction 		= $deduction;
				$employee->addition 		= $addition;
				$employee->overtime 		= $overtime;
				$employee->approve_additions= $approve_additions;
				$employee->addition_amount 	= $addition_amount; // - seniority temporary
				$employee->approve_deductions = $approve_deductions;
				$employee->deduction_amount = $deduction_amount;

				if($employee->payment_type == "daily"){
					$basic_salary = ($employee->basic_salary * $employee->working_day);
				}else{
					$basic_salary = $employee->basic_salary;
				}

					//----------------
					if($seniority_pay){ // auto
						$gross_salary 			= $this->bpas->formatDecimal(($basic_salary+ ($addition_amount) + $overtime) - ($absent_amount + $permission_amount + $late_amount + $deduction_amount));
					}else{ //manual
						$gross_salary 			= $this->bpas->formatDecimal(($basic_salary+ ($addition_amount-$seniority_payment-$severance-$indemnity) + $overtime) - ($absent_amount + $permission_amount + $late_amount + $deduction_amount)+ $seniority_payment);
					}
					// baht to dollar
					if($this->Settings->default_currency =='BAHT'){
						$gross_salary = $this->bpas->formatDecimal($gross_salary / $baht_rate);
					}
					
					$nssf_gross_salary_riel = ($gross_salary * $nssf_rate);
					//---------------
				$employee->nssf_salary_usd 	= $gross_salary;
				$employee->nssf_salary_riel = $nssf_gross_salary_riel;
					//----------nssf-----------
					$pension_deduct_percent = $employee->pension;
					$pension_deduct = 0;$pension_by_staff =0;$pension_by_company=0;
					$contributory_nssf=0;$Accident_NSSF=0;$Health_NSSF=0;
					if($employee->nssf > 0 && $nssf_gross_salary_riel > 0){						
						if($nssf_gross_salary_riel < 400000){ //400,000
							$contributory_nssf 	= 400000;
							$pension_by_staff 	= round(($contributory_nssf * $pension_deduct_percent) / 100);
							$pension_by_company = $pension_by_staff; //2% also
						}else if($nssf_gross_salary_riel > 1200000){ //1,200,000
							$contributory_nssf 	= 1200000;
							$pension_by_staff 	= round(($contributory_nssf * $pension_deduct_percent) / 100);
							$pension_by_company = $pension_by_staff; //2% also
						}else{
							$contributory_nssf 	= round($nssf_gross_salary_riel);//real salary
						 	$pension_by_staff 	= round(($nssf_gross_salary_riel * $pension_deduct_percent) / 100);
						 	$pension_by_company = $pension_by_staff; //2% also
						}
						$pension_deduct = $this->bpas->formatDecimal($pension_by_staff / $nssf_rate);

						if($employee->age > 60){
							$pension_by_staff 	=0;
							$pension_by_company =0;
						}
						//-------------nssf-------
						$Accident_NSSF  = ($contributory_nssf *  $this->accident_duction)/100;
						$Health_NSSF	= ($contributory_nssf * $this->health_duction)/100;
						//------------close nssf-=-----------
					}
					//---------close nssf----------
				$employee->contributory_nssf  = $contributory_nssf;
				$employee->pension_by_staff   = $pension_by_staff; //$pension;
				$employee->pension_by_company = $pension_by_company;
				$employee->health_nssf 		  = $Health_NSSF;
				$employee->accident_nssf 	  = $Accident_NSSF;

					//-------------seniority-------------
					$except_seniority_paid	= $this->seniority_except_tax;
					if($seniority_pay){ //auto
						$seniority_payment 		= $this->payrolls_model->calculate_seniority($biller_id,$employee->employee_id,$month,$year,$gross_salary);
					}
					//$seniority_payment
					if($this->Settings->default_currency=='BAHT'){
						// baht -> dollar
						$seniority_payment =  $seniority_payment ? ($seniority_payment / $baht_rate) :0;
						// dollar -> riel
						$seniority_payment_riel = $seniority_payment ? ($seniority_payment * $kh_rate) :0;
					}else{
						$seniority_payment_riel = $seniority_payment ? ($seniority_payment * $kh_rate) :0;
					}
					$seniority_paid = ($seniority_payment_riel > $except_seniority_paid)? ($seniority_payment_riel - $except_seniority_paid):0;

				$employee->seniority 		= $seniority_payment;//$seniority_payment dollar;
				$employee->seniority_response_tax 	= $seniority_paid ? $this->bpas->formatDecimal($seniority_paid/$kh_rate):0; // dollar
					//-------------close seniority--------
					if($seniority_pay){ //auto
						$severance 		= $this->payrolls_model->calculate_severance($biller_id,$employee->employee_id,$month,$year,$gross_salary);
					}
					//$severance;
					if($this->Settings->default_currency=='BAHT'){
						// baht -> dollar
						$severance =  $severance ? ($severance / $baht_rate) :0;
					}
				$employee->severance 	= $severance;
					$employee->severance_riel		= $this->bpas->formatDecimal(round($severance*$kh_rate));
					if($this->Settings->default_currency=='BAHT'){
						// baht -> dollar
						$indemnity =  $indemnity ? ($indemnity / $baht_rate) :0;
					}
				$employee->indemnity 	= $indemnity;
					$employee->indemnity_riel 	= $this->bpas->formatDecimal(round($indemnity*$kh_rate));
					$gross_salary = $this->bpas->formatDecimal($employee->nssf_salary_usd-$pension_deduct +$employee->seniority_response_tax);

				$employee->gross_salary 	  = $gross_salary;
				$employee->gross_salary_riel  = $this->bpas->formatDecimal(round($employee->gross_salary*$kh_rate));

					$spouses           = $this->hr_model->getSpouseMemberByEmployeeID($employee->employee_id);
			        $spouses           = $spouses?count($spouses):0;
			        $spouses_reduction = $spouses * $this->spouses_reduction;
				$employee->spouse 			= $spouses;
					$childs            = $this->hr_model->getChildrenMemberByEmployeeID($employee->employee_id);
			        $childs            = $childs?count($childs):0;
			        $childs_reduction  = $childs * $this->childs_reduction;
				$employee->children 		= $childs;
				$employee->spouse_children_reduction = $spouses_reduction + $childs_reduction;
				$employee->Taxbasesalary 	= $employee->gross_salary_riel-$employee->spouse_children_reduction;

					//=======tax calculate=======//
					$tax_declaration = $employee->Taxbasesalary;
					if($employee->salary_tax > 0){
						$tax_declaration = $this->bpas->formatDecimal($employee->salary_tax * $kh_rate);
					}
					$tax_calculation = $this->site->getSalaryTax($employee->employee_id,$tax_declaration);
					$net_tax 			= $tax_calculation ? $tax_calculation['tax_on_salary'] : 0;
					$net_tax_riel 		= round($tax_calculation ? $tax_calculation['tax_on_salary_riel'] : 0);
					$tax_payment = ($employee->self_tax == 1 ? $net_tax : 0);
					//=====end tax calculate=====//

				$employee->tax_declaration 	= $tax_declaration;
				$employee->tax_payment 		= $net_tax;
				$employee->tax_payment_riel	= $net_tax_riel;
					$net_salary = $this->bpas->formatDecimal($gross_salary-$tax_payment+$seniority_payment-$employee->seniority_response_tax);
					$pre_salary = $employee->pre_salary;

					$net_pay = $net_salary - ($pre_salary + $cash_advanced) + $severance+$indemnity;
				$employee->self_tax 		= ($employee->self_tax == 1) ? lang('yes') : lang('no');
					if($this->Settings->default_currency=='BAHT'){
						$net_salary =  ($net_salary * $baht_rate);
					}
				$employee->net_salary 		= $net_salary;
					if($this->Settings->default_currency=='BAHT'){
						$net_pay =  ($net_pay * $baht_rate);
					}
				$employee->net_pay 			= $net_pay;
			}
		}
		echo json_encode($employees);
	}
	public function add_salary_daily($biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id 		= $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id 	= $this->input->post('position') ? $this->input->post('position') : null;
			$department_id 	= $this->input->post('department') ? $this->input->post('department') : null;
			$group_id 		= $this->input->post('group') ? $this->input->post('group') : null;
			$note 			= $this->input->post('note') ? $this->input->post('note') : null;

			$kh_rate 		= $this->input->post('kh_rate') ? $this->input->post('kh_rate') : 0;
			$nssf_rate 		= $this->input->post('nssf_rate') ? $this->input->post('nssf_rate') : 0;

			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$status = "pending";
			$payment_status = "pending";
			$items = false;
			$total_gross_salary = 0;
			$total_overtime = 0;
			$total_addition = 0;
			$total_cash_advanced = 0;
			$total_tax_payment = 0;
			$total_net_salary = 0;
			$total_net_pay = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {

				$employee_id = $_POST['employee_id'][$r];
				$additions = false;
				$deductions = false;
				if($_POST['addition'][$employee_id]){
					foreach($_POST['addition'][$employee_id] as $index => $value){
						$additions[] = array(
							"id"=>$index,
							"name"=>$_POST['addition_name'][$employee_id][$index],
							"value"=>$value
						);		
					}
				}
				if($_POST['deduction'][$employee_id]){
					foreach($_POST['deduction'][$employee_id] as $index => $value){
						$deductions[] = array(
							"id"=>$index,
							"name"=>$_POST['deduction_name'][$employee_id][$index],
							"value"=>$value
						);			
					}
				}

				$items[] = array(
							"approved_att_id"   => $_POST['approved_att_id'][$r],
							"employee_id" 		=> $_POST['employee_id'][$r],
							"working_day" 		=> $_POST['working_day'][$r],
							"absent" 			=> $_POST['absent'][$r],
							"permission" 		=> $_POST['permission'][$r],
							"late" 				=> $_POST['late'][$r],
							"normal_ot" 		=> $_POST['normal_ot'][$r],
							"weekend_ot" 		=> $_POST['weekend_ot'][$r],
							"holiday_ot" 		=> $_POST['holiday_ot'][$r],
							"basic_salary" 		=> $_POST['basic_salary'][$r],
							"absent_amount" 	=> $_POST['absent_amount'][$r],
							"permission_amount" => $_POST['permission_amount'][$r],
							"late_amount" 		=> $_POST['late_amount'][$r],
							"overtime" 			=> $_POST['overtime'][$r],
							"additions" 		=> json_encode($additions),
							"addition_amount" 	=> $_POST['addition_amount'][$r],
							"deductions" 		=> json_encode($deductions),
							"deduction_amount" 	=> $_POST['deduction_amount'][$r],
							"seniority" 		=> $this->bpas->formatDecimal($_POST['seniority'][$r]),
							"seniority_response_tax" => $this->bpas->formatDecimal($_POST['seniority_response_tax'][$r]),
							"severance" 	=> $this->bpas->formatDecimal($_POST['severance'][$r]),
							"indemnity" 	=> $this->bpas->formatDecimal($_POST['indemnity'][$r]),
							"severance_riel" 	=> $this->bpas->formatDecimal($_POST['severance_riel'][$r]),
							"indemnity_riel" 	=> $this->bpas->formatDecimal($_POST['indemnity_riel'][$r]),

							"nssf_salary_usd" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_usd'][$r]),
							"nssf_salary_riel" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_riel'][$r]),
							"contributory_nssf" => $this->bpas->formatDecimal($_POST['contributory_nssf'][$r]),
							"pension_by_staff" 	=> $this->bpas->formatDecimal($_POST['pension_by_staff'][$r]),
							"pension_by_company" => $this->bpas->formatDecimal($_POST['pension_by_company'][$r]),
							"health_nssf" 		=> $this->bpas->formatDecimal($_POST['health_nssf'][$r]),
							"accident_nssf" 	=> $this->bpas->formatDecimal($_POST['accident_nssf'][$r]),
							"gross_salary" 		=> $_POST['gross_salary'][$r],
							"gross_salary_riel" => $_POST['gross_salary_riel'][$r],
							"spouse" 			=> $_POST['spouse'][$r],
							"children" 			=> $_POST['children'][$r],
							"spouse_children_reduction" => $_POST['spouse_children_reduction'][$r],
							"Taxbasesalary" 	=> $_POST['Taxbasesalary'][$r],
							"salary_paid" 		=> 0,
							"tax_paid" 			=> 0,
							"tax_declaration" 	=> $_POST['tax_declaration'][$r],
							"tax_payment" 		=> $this->bpas->formatDecimal($_POST['tax_payment'][$r]),
							"tax_payment_riel" 	=> $this->bpas->formatDecimal($_POST['tax_payment_riel'][$r]),
							"net_salary" 		=> $this->bpas->formatDecimal($_POST['net_salary'][$r]),
							"pre_salary" 		=> $_POST['pre_salary'][$r],
							"cash_advanced" 	=> $_POST['cash_advanced'][$r],
							"net_pay" 			=> $this->bpas->formatDecimal($_POST['net_pay'][$r]),
							"payment_status" 	=> "pending"

						);
				$total_gross_salary += $_POST['gross_salary'][$r]; 	
				$total_overtime += $_POST['overtime'][$r];
				$total_addition += $_POST['addition_amount'][$r];
				$total_cash_advanced += $_POST['cash_advanced'][$r];
				$total_tax_payment += $this->bpas->formatDecimal($_POST['tax_payment'][$r]);
				$total_net_salary += $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$total_net_pay += $this->bpas->formatDecimal($_POST['net_pay'][$r]);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' 				=> $date,
				'type' 				=> 'daily',
                'month' 			=> $month,
                'year' 				=> $year,
                'biller_id' 		=> $biller_id,
                'position_id' 		=> $position_id,
				'department_id' 	=> $department_id,
				'group_id' 			=> $group_id,
				'total_gross_salary' 	=> $total_gross_salary,
				'total_overtime' 		=> $total_overtime,
				'total_addition' 		=> $total_addition,
				'total_cash_advanced' 	=> $total_cash_advanced,
				'total_tax_payment' 	=> $total_tax_payment,
				'total_net_salary' 	=> $total_net_salary,
				'total_net_pay' 	=> $total_net_pay,
				'note' 				=> $note,
				'status' 			=> $status,
				'kh_rate' 			=> $this->input->post('kh_rate'),
				'nssf_rate' 		=> $this->input->post('nssf_rate'),
				'payment_status' 	=> $payment_status,
				'created_by' 		=> $this->session->userdata('user_id'),
				'created_at' 		=> date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addDailySalary($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_daily_added"));          
			admin_redirect('payrolls/salaries_daily');
        } else {
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] 		= $this->site->getBillers();
			$this->data['biller_id'] 	= $biller_id;
			$this->data['projects']     = $this->site->getAllProject();
			$this->data['additions'] 	= $this->payrolls_model->getAdditions($biller_id);
			$this->data['deductions'] 	= $this->payrolls_model->getDeductions($biller_id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/'), 'page' => lang('salaries')), array('link' => '#', 'page' => lang('add_salary_daily')));
            $meta = array('page_title' => lang('add_salary_daily'), 'bc' => $bc);
            $this->page_construct('payrolls/add_salary_daily', $meta, $this->data);
        }
	}
	public function edit_daily_salary($id = false, $biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$items = false;
			$total_gross_salary = 0;
			$total_overtime = 0;
			$total_addition = 0;
			$total_cash_advanced = 0;
			$total_tax_payment = 0;
			$total_net_salary = 0;
			$total_net_pay = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$additions = false;
				$deductions = false;
				if($_POST['addition'][$employee_id]){
					foreach($_POST['addition'][$employee_id] as $index => $value){
						$additions[] = array(
											"id"=>$index,
											"name"=>$_POST['addition_name'][$employee_id][$index],
											"value"=>$value
										);				
					}
				}
				if($_POST['deduction'][$employee_id]){
					foreach($_POST['deduction'][$employee_id] as $index => $value){
						$deductions[] = array(
											"id"=>$index,
											"name"=>$_POST['deduction_name'][$employee_id][$index],
											"value"=>$value
										);		
					}
				}
				$items[] = array(
								"approved_att_id" 	=> $_POST['approved_att_id'][$r],
								"salary_id" 		=> $id,
								"employee_id" 		=> $_POST['employee_id'][$r],
								"working_day" 		=> $_POST['working_day'][$r],
								"absent" 			=> $_POST['absent'][$r],
								"permission" 		=> $_POST['permission'][$r],
								"late" 				=> $_POST['late'][$r],
								"normal_ot" 		=> $_POST['normal_ot'][$r],
								"weekend_ot" 		=> $_POST['weekend_ot'][$r],
								"holiday_ot" 		=> $_POST['holiday_ot'][$r],
								"basic_salary" 		=> $_POST['basic_salary'][$r],
								"absent_amount" 	=> $_POST['absent_amount'][$r],
								"permission_amount" => $_POST['permission_amount'][$r],
								"late_amount" 		=> $_POST['late_amount'][$r],
								"overtime" 			=> $_POST['overtime'][$r],
								"additions" 		=> json_encode($additions),
								"addition_amount" 	=> $_POST['addition_amount'][$r],
								"deductions" 		=> json_encode($deductions),
								"deduction_amount" 	=> $_POST['deduction_amount'][$r],
								"seniority" 		=> $this->bpas->formatDecimal($_POST['seniority'][$r]),
								"seniority_response_tax" => $this->bpas->formatDecimal($_POST['seniority_response_tax'][$r]),
								"severance" 	=> $this->bpas->formatDecimal($_POST['severance'][$r]),
								"indemnity" 	=> $this->bpas->formatDecimal($_POST['indemnity'][$r]),
								"severance_riel" 	=> $this->bpas->formatDecimal($_POST['severance_riel'][$r]),
								"indemnity_riel" 	=> $this->bpas->formatDecimal($_POST['indemnity_riel'][$r]),
								
								"nssf_salary_usd" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_usd'][$r]),
								"nssf_salary_riel" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_riel'][$r]),
								"contributory_nssf" => $this->bpas->formatDecimal($_POST['contributory_nssf'][$r]),
								"pension_by_staff" 	=> $this->bpas->formatDecimal($_POST['pension_by_staff'][$r]),
								"pension_by_company"=> $this->bpas->formatDecimal($_POST['pension_by_company'][$r]),
								"health_nssf" 		=> $this->bpas->formatDecimal($_POST['health_nssf'][$r]),
								"accident_nssf" 	=> $this->bpas->formatDecimal($_POST['accident_nssf'][$r]),
								"gross_salary" 		=> $_POST['gross_salary'][$r],
								"gross_salary_riel" => $_POST['gross_salary_riel'][$r],

								"spouse" 			=> $_POST['spouse'][$r],
								"children" 			=> $_POST['children'][$r],
								"spouse_children_reduction" => $_POST['spouse_children_reduction'][$r],
								"Taxbasesalary" 	=> $_POST['Taxbasesalary'][$r],

								"tax_declaration" 	=> $_POST['tax_declaration'][$r],
								"tax_payment" 		=> $this->bpas->formatDecimal($_POST['tax_payment'][$r]),
								"tax_payment_riel" 	=> $this->bpas->formatDecimal($_POST['tax_payment_riel'][$r]),
								"salary_paid" 		=> 0,
								"tax_paid" 			=> 0,
								"net_salary" 		=> $this->bpas->formatDecimal($_POST['net_salary'][$r]),
								"pre_salary" 		=> $_POST['pre_salary'][$r],
								"cash_advanced" 	=> $_POST['cash_advanced'][$r],
								"net_pay" 			=> $this->bpas->formatDecimal($_POST['net_pay'][$r]),
								"payment_status" 	=> "pending"
							);

				$total_gross_salary += $_POST['gross_salary'][$r]; 	
				$total_overtime += $_POST['overtime'][$r];
				$total_addition += $_POST['addition_amount'][$r];
				$total_cash_advanced += $_POST['cash_advanced'][$r];
				$total_tax_payment += $this->bpas->formatDecimal($_POST['tax_payment'][$r]);
				$total_net_salary += $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$total_net_pay += $this->bpas->formatDecimal($_POST['net_pay'][$r]);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'total_gross_salary' => $total_gross_salary,
				'total_overtime' => $total_overtime,
				'total_addition' => $total_addition,
				'total_cash_advanced' => $total_cash_advanced,
				'total_tax_payment' => $total_tax_payment,
				'total_net_salary' => $total_net_salary,
				'total_net_pay' => $total_net_pay,
				'note' => $note,
				'kh_rate' 	=> $this->input->post('kh_rate'),
				'nssf_rate' => $this->input->post('nssf_rate'),
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateDailySalary($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_daily_updated"));          
			admin_redirect('payrolls/salaries_daily');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$salary = $this->payrolls_model->getSalaryByID($id);
			$this->data['salary'] = $salary;
			$this->data['salary_items'] = $this->payrolls_model->getSalaryItems($id);
			$this->data['positions'] = $this->payrolls_model->getPositions($salary->biller_id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($salary->biller_id);
			if($salary->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($salary->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$this->data['biller_id'] = $biller_id;
			$this->data['additions'] = $this->payrolls_model->getAdditions($biller_id);
			$this->data['deductions'] = $this->payrolls_model->getDeductions($biller_id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/'), 'page' => lang('salaries')), array('link' => '#', 'page' => lang('edit_daily_salary')));
            $meta = array('page_title' => lang('edit_daily_salary'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_daily_salary', $meta, $this->data);
        }
	}
	public function delete_daily_salary($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteDailySalary($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('salary_deleted')]);
            }
            $this->session->set_flashdata('message', lang('salary_deleted'));
            admin_redirect('payrolls/salaries_daily');
        }
    }

	public function add_salary($biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id 		= $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id 	= $this->input->post('position') ? $this->input->post('position') : null;
			$department_id 	= $this->input->post('department') ? $this->input->post('department') : null;
			$group_id 		= $this->input->post('group') ? $this->input->post('group') : null;
			$note 			= $this->input->post('note') ? $this->input->post('note') : null;

			$kh_rate 		= $this->input->post('kh_rate') ? $this->input->post('kh_rate') : 0;
			$nssf_rate 		= $this->input->post('nssf_rate') ? $this->input->post('nssf_rate') : 0;

			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$status = "pending";
			$payment_status = "pending";
			$items = false;
			$total_gross_salary = 0;
			$total_overtime = 0;
			$total_addition = 0;
			$total_cash_advanced = 0;
			$total_tax_payment = 0;
			$total_net_salary = 0;
			$total_net_pay = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {

				$employee_id = $_POST['employee_id'][$r];
				$additions = false;
				$deductions = false;
				if($_POST['addition'][$employee_id]){
					foreach($_POST['addition'][$employee_id] as $index => $value){
						$additions[] = array(
							"id"=>$index,
							"name"=>$_POST['addition_name'][$employee_id][$index],
							"value"=>$value
						);		
					}
				}
				if($_POST['deduction'][$employee_id]){
					foreach($_POST['deduction'][$employee_id] as $index => $value){
						$deductions[] = array(
							"id"=>$index,
							"name"=>$_POST['deduction_name'][$employee_id][$index],
							"value"=>$value
						);			
					}
				}

				$items[] = array(
								"employee_id" 		=> $_POST['employee_id'][$r],
								"working_day" 		=> $_POST['working_day'][$r],
								"absent" 			=> $_POST['absent'][$r],
								"permission" 		=> $_POST['permission'][$r],
								"late" 				=> $_POST['late'][$r],
								"normal_ot" 		=> $_POST['normal_ot'][$r],
								"weekend_ot" 		=> $_POST['weekend_ot'][$r],
								"holiday_ot" 		=> $_POST['holiday_ot'][$r],
								"basic_salary" 		=> $_POST['basic_salary'][$r],
								"absent_amount" 	=> $_POST['absent_amount'][$r],
								"permission_amount" => $_POST['permission_amount'][$r],
								"late_amount" 		=> $_POST['late_amount'][$r],
								"overtime" 			=> $_POST['overtime'][$r],
								"additions" 		=> json_encode($additions),
								"addition_amount" 	=> $_POST['addition_amount'][$r],
								"deductions" 		=> json_encode($deductions),
								"deduction_amount" 	=> $_POST['deduction_amount'][$r],
								"seniority" 		=> $this->bpas->formatDecimal($_POST['seniority'][$r]),
								"seniority_response_tax" => $this->bpas->formatDecimal($_POST['seniority_response_tax'][$r]),
								"severance" 	=> $this->bpas->formatDecimal($_POST['severance'][$r]),
								"indemnity" 	=> $this->bpas->formatDecimal($_POST['indemnity'][$r]),
								"severance_riel" 	=> $this->bpas->formatDecimal($_POST['severance_riel'][$r]),
								"indemnity_riel" 	=> $this->bpas->formatDecimal($_POST['indemnity_riel'][$r]),

								"nssf_salary_usd" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_usd'][$r]),
								"nssf_salary_riel" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_riel'][$r]),
								"contributory_nssf" => $this->bpas->formatDecimal($_POST['contributory_nssf'][$r]),
								"pension_by_staff" 	=> $this->bpas->formatDecimal($_POST['pension_by_staff'][$r]),
								"pension_by_company" => $this->bpas->formatDecimal($_POST['pension_by_company'][$r]),
								"health_nssf" 		=> $this->bpas->formatDecimal($_POST['health_nssf'][$r]),
								"accident_nssf" 	=> $this->bpas->formatDecimal($_POST['accident_nssf'][$r]),
								"gross_salary" 		=> $_POST['gross_salary'][$r],
								"gross_salary_riel" => $_POST['gross_salary_riel'][$r],
								"spouse" 			=> $_POST['spouse'][$r],
								"children" 			=> $_POST['children'][$r],
								"spouse_children_reduction" => $_POST['spouse_children_reduction'][$r],
								"Taxbasesalary" 	=> $_POST['Taxbasesalary'][$r],
								"salary_paid" 		=> 0,
								"tax_paid" 			=> 0,
								"tax_declaration" 	=> $_POST['tax_declaration'][$r],
								"tax_payment" 		=> $this->bpas->formatDecimal($_POST['tax_payment'][$r]),
								"tax_payment_riel" 	=> $this->bpas->formatDecimal($_POST['tax_payment_riel'][$r]),
								"net_salary" 		=> $this->bpas->formatDecimal($_POST['net_salary'][$r]),
								"pre_salary" 		=> $_POST['pre_salary'][$r],
								"cash_advanced" 	=> $_POST['cash_advanced'][$r],
								"net_pay" 			=> $this->bpas->formatDecimal($_POST['net_pay'][$r]),
								"payment_status" 	=> "pending"

							);
				$total_gross_salary += $_POST['gross_salary'][$r]; 	
				$total_overtime += $_POST['overtime'][$r];
				$total_addition += $_POST['addition_amount'][$r];
				$total_cash_advanced += $_POST['cash_advanced'][$r];
				$total_tax_payment += $this->bpas->formatDecimal($_POST['tax_payment'][$r]);
				$total_net_salary += $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$total_net_pay += $this->bpas->formatDecimal($_POST['net_pay'][$r]);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' 			=> $date,
                'month' 		=> $month,
                'year' 			=> $year,
                'biller_id' 		=> $biller_id,
                'position_id' 		=> $position_id,
				'department_id' 	=> $department_id,
				'group_id' 			=> $group_id,
				'total_gross_salary' => $total_gross_salary,
				'total_overtime' 	=> $total_overtime,
				'total_addition' 	=> $total_addition,
				'total_cash_advanced' => $total_cash_advanced,
				'total_tax_payment' => $total_tax_payment,
				'total_net_salary' 	=> $total_net_salary,
				'total_net_pay' 	=> $total_net_pay,
				'note' 				=> $note,
				'status' 			=> $status,
				'kh_rate' 			=> $this->input->post('kh_rate'),
				'nssf_rate' 		=> $this->input->post('nssf_rate'),
				'payment_status' 	=> $payment_status,
				'created_by' 		=> $this->session->userdata('user_id'),
				'created_at' 		=> date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addSalary($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_added"));          
			admin_redirect('payrolls/');
        } else {
            $this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] 		= $this->site->getBillers();
			$this->data['biller_id'] 	= $biller_id;
			$this->data['projects']     = $this->site->getAllProject();
			$this->data['additions'] 	= $this->payrolls_model->getAdditions($biller_id);
			$this->data['deductions'] 	= $this->payrolls_model->getDeductions($biller_id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/'), 'page' => lang('salaries')), array('link' => '#', 'page' => lang('add_salary')));
            $meta = array('page_title' => lang('add_salary'), 'bc' => $bc);
            $this->page_construct('payrolls/add_salary', $meta, $this->data);
        }
	}
	
	public function edit_salary($id = false, $biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$items = false;
			$total_gross_salary = 0;
			$total_overtime = 0;
			$total_addition = 0;
			$total_cash_advanced = 0;
			$total_tax_payment = 0;
			$total_net_salary = 0;
			$total_net_pay = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$additions = false;
				$deductions = false;
				if($_POST['addition'][$employee_id]){
					foreach($_POST['addition'][$employee_id] as $index => $value){
						$additions[] = array(
											"id"=>$index,
											"name"=>$_POST['addition_name'][$employee_id][$index],
											"value"=>$value
										);				
					}
				}
				if($_POST['deduction'][$employee_id]){
					foreach($_POST['deduction'][$employee_id] as $index => $value){
						$deductions[] = array(
											"id"=>$index,
											"name"=>$_POST['deduction_name'][$employee_id][$index],
											"value"=>$value
										);		
					}
				}
				$items[] = array(
								"salary_id" 		=> $id,
								"employee_id" 		=> $_POST['employee_id'][$r],
								"working_day" 		=> $_POST['working_day'][$r],
								"absent" 			=> $_POST['absent'][$r],
								"permission" 		=> $_POST['permission'][$r],
								"late" 				=> $_POST['late'][$r],
								"normal_ot" 		=> $_POST['normal_ot'][$r],
								"weekend_ot" 		=> $_POST['weekend_ot'][$r],
								"holiday_ot" 		=> $_POST['holiday_ot'][$r],
								"basic_salary" 		=> $_POST['basic_salary'][$r],
								"absent_amount" 	=> $_POST['absent_amount'][$r],
								"permission_amount" => $_POST['permission_amount'][$r],
								"late_amount" 		=> $_POST['late_amount'][$r],
								"overtime" 			=> $_POST['overtime'][$r],
								"additions" 		=> json_encode($additions),
								"addition_amount" 	=> $_POST['addition_amount'][$r],
								"deductions" 		=> json_encode($deductions),
								"deduction_amount" 	=> $_POST['deduction_amount'][$r],
								"seniority" 		=> $this->bpas->formatDecimal($_POST['seniority'][$r]),
								"seniority_response_tax" => $this->bpas->formatDecimal($_POST['seniority_response_tax'][$r]),
								"severance" 	=> $this->bpas->formatDecimal($_POST['severance'][$r]),
								"indemnity" 	=> $this->bpas->formatDecimal($_POST['indemnity'][$r]),
								"severance_riel" 	=> $this->bpas->formatDecimal($_POST['severance_riel'][$r]),
								"indemnity_riel" 	=> $this->bpas->formatDecimal($_POST['indemnity_riel'][$r]),
								
								"nssf_salary_usd" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_usd'][$r]),
								"nssf_salary_riel" 	=> $this->bpas->formatDecimal($_POST['nssf_salary_riel'][$r]),
								"contributory_nssf" => $this->bpas->formatDecimal($_POST['contributory_nssf'][$r]),
								"pension_by_staff" 	=> $this->bpas->formatDecimal($_POST['pension_by_staff'][$r]),
								"pension_by_company"=> $this->bpas->formatDecimal($_POST['pension_by_company'][$r]),
								"health_nssf" 		=> $this->bpas->formatDecimal($_POST['health_nssf'][$r]),
								"accident_nssf" 	=> $this->bpas->formatDecimal($_POST['accident_nssf'][$r]),
								"gross_salary" 		=> $_POST['gross_salary'][$r],
								"gross_salary_riel" => $_POST['gross_salary_riel'][$r],

								"spouse" 			=> $_POST['spouse'][$r],
								"children" 			=> $_POST['children'][$r],
								"spouse_children_reduction" => $_POST['spouse_children_reduction'][$r],
								"Taxbasesalary" 	=> $_POST['Taxbasesalary'][$r],

								"tax_declaration" 	=> $_POST['tax_declaration'][$r],
								"tax_payment" 		=> $this->bpas->formatDecimal($_POST['tax_payment'][$r]),
								"tax_payment_riel" 	=> $this->bpas->formatDecimal($_POST['tax_payment_riel'][$r]),
								"salary_paid" 		=> 0,
								"tax_paid" 			=> 0,
								"net_salary" 		=> $this->bpas->formatDecimal($_POST['net_salary'][$r]),
								"pre_salary" 		=> $_POST['pre_salary'][$r],
								"cash_advanced" 	=> $_POST['cash_advanced'][$r],
								"net_pay" 			=> $this->bpas->formatDecimal($_POST['net_pay'][$r]),
								"payment_status" 	=> "pending"
							);

				$total_gross_salary += $_POST['gross_salary'][$r]; 	
				$total_overtime += $_POST['overtime'][$r];
				$total_addition += $_POST['addition_amount'][$r];
				$total_cash_advanced += $_POST['cash_advanced'][$r];
				$total_tax_payment += $this->bpas->formatDecimal($_POST['tax_payment'][$r]);
				$total_net_salary += $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$total_net_pay += $this->bpas->formatDecimal($_POST['net_pay'][$r]);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'total_gross_salary' => $total_gross_salary,
				'total_overtime' => $total_overtime,
				'total_addition' => $total_addition,
				'total_cash_advanced' => $total_cash_advanced,
				'total_tax_payment' => $total_tax_payment,
				'total_net_salary' => $total_net_salary,
				'total_net_pay' => $total_net_pay,
				'note' => $note,
				'kh_rate' 	=> $this->input->post('kh_rate'),
				'nssf_rate' => $this->input->post('nssf_rate'),
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateSalary($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_updated"));          
			admin_redirect('payrolls/');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$salary = $this->payrolls_model->getSalaryByID($id);
			$this->data['salary'] = $salary;
			$this->data['salary_items'] = $this->payrolls_model->getSalaryItems($id);
			$this->data['positions'] = $this->payrolls_model->getPositions($salary->biller_id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($salary->biller_id);
			if($salary->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($salary->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$this->data['biller_id'] = $biller_id;
			$this->data['additions'] = $this->payrolls_model->getAdditions($biller_id);
			$this->data['deductions'] = $this->payrolls_model->getDeductions($biller_id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/'), 'page' => lang('salaries')), array('link' => '#', 'page' => lang('edit_salary')));
            $meta = array('page_title' => lang('edit_salary'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_salary', $meta, $this->data);
        }
	}
	public function delete_salary($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteSalary($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('salary_deleted')]);
            }
            $this->session->set_flashdata('message', lang('salary_deleted'));
            admin_redirect('payrolls');
        }
    }
	
	function salary_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_salary');
                    $deleted = 0;
					foreach ($_POST['val'] as $id) {
						$salary = $this->payrolls_model->getSalaryByID($id);
						if($salary->status == "pending" && $salary->payment_status == "pending"){
							$deleted = 1;
							$this->payrolls_model->deleteSalary($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("salary_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("salary_cannot_delete"));
					}
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('salary');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('gross_salary'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('overtime'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('addition'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('cash_advanced'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('tax_payment'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('net_salary'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('net_pay'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('tax_paid'));
					$this->excel->getActiveSheet()->SetCellValue('L1', lang('salary_paid'));
					$this->excel->getActiveSheet()->SetCellValue('M1', lang('total_paid'));
					$this->excel->getActiveSheet()->SetCellValue('N1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('O1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('P1', lang('status'));
					$this->excel->getActiveSheet()->SetCellValue('Q1', lang('payment_status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $salary = $this->payrolls_model->getSalaryByID($id); 
						$user = $this->site->getUserByID($salary->created_by);
						$total_balance = $salary->total_net_pay - $salary->total_paid;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($salary->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $salary->month."/".$salary->year);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($salary->total_gross_salary));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($salary->total_overtime));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($salary->total_addition));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($salary->total_cash_advanced));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($salary->total_tax_payment));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($salary->total_net_salary));
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($salary->total_net_pay));
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($salary->total_tax_paid));
						$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($salary->total_salary_paid));
						$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($salary->total_paid));
						$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($total_balance));
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->remove_tag($salary->note));
						$this->excel->getActiveSheet()->SetCellValue('P' . $row, lang($salary->status));
						$this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($salary->payment_status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
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
					$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);

					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salary_list_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function approve_salary($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateSalaryStatus($id,"approved")) {
            $this->session->set_flashdata('message', lang('salary_approved'));
            admin_redirect('payrolls');
        }
    }
	public function unapprove_salary($id = null){
        $this->bpas->checkPermissions("approve_salary", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateSalaryStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('salary_unapproved'));
            admin_redirect('payrolls');
        }
    }
	public function modal_view_salary($id = false){
		$this->bpas->checkPermissions('salaries', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary = $this->payrolls_model->getSalaryByID($id);
		$this->data['salary'] = $salary;
        $this->data['salary_items'] = $this->payrolls_model->getSalaryItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_salary', $this->data);
	}
	public function modal_view_payslip($id = false){
		$this->bpas->checkPermissions('salaries', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary_item = $this->payrolls_model->getSalaryItemByID($id);
		$salary = $this->payrolls_model->getSalaryByID($salary_item->salary_id);
		$this->data['salary_item'] = $salary_item;
		$this->data['salary'] = $salary;
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->load->view($this->theme . 'payrolls/modal_view_payslip', $this->data);
	}
	
	public function payslips_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : false;
		$position = $this->input->post('position') ? $this->input->post('position') : false;
		$department = $this->input->post('department') ? $this->input->post('department') : false;
		$group = $this->input->post('group') ? $this->input->post('group') : false;
		$employee = $this->input->post('employee') ? $this->input->post('employee') : false;
		if($this->input->post('month') ){
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
		}else{
			$month = date("m");
			$year = date("Y");
		}
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
		$this->data["payslips"] = $this->payrolls_model->getPayslips($year,$month,$biller,$position,$department,$group,$employee);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('payslips_report')));
        $meta = array('page_title' => lang('payslips_report'), 'bc' => $bc);
        $this->page_construct('payrolls/payslips_report', $meta, $this->data);
	}
	
	
	public function salaries_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['users'] = $this->site->getStaff();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salaries_report')));
        $meta = array('page_title' => lang('salaries_report'), 'bc' => $bc);
        $this->page_construct('payrolls/salaries_report', $meta, $this->data);
	}
	public function getSalariesReport($pdf = NULL, $xls = NULL)
    {
        $this->bpas->checkPermissions('salaries_report');
        $user 		= $this->input->get('user') ? $this->input->get('user') : NULL;
		$biller 	= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$position 	= $this->input->get('position') ? $this->input->get('position') : NULL;
		$group 		= $this->input->get('group') ? $this->input->get('group') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;

		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
								companies.company,
								CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
								CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_gross_salary,0) as total_gross_salary,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_overtime,0) as total_overtime,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_addition,0) as total_addition,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_cash_advanced,0) as total_cash_advanced,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_payment,0) as total_tax_payment,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_salary,0) as total_net_salary,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) as total_net_pay,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_paid,0) as total_tax_paid,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_salary_paid,0) as total_salary_paid,
								IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) as total_paid,
								(IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) - IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0)) as total_balance,
								pay_salaries.note,
								pay_salaries.status,
								pay_salaries.payment_status,
								pay_salaries.attachment,
								pay_salaries.id as id")
						->from("pay_salaries")
						->join("companies","companies.id = pay_salaries.biller_id","left")
						->join("users","users.id = pay_salaries.created_by","left");				

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('pay_salaries.department_id', $department);
            }
            if ($position) {
                $this->db->where('pay_salaries.position_id', $position);
            }
			if ($group) {
                $this->db->where('pay_salaries.group_id', $group);
            }
            if ($user) {
                $this->db->where('pay_salaries.created_by', $user);
            }
			if($y_month){
				$this->db->where('pay_salaries.year', $year);
				$this->db->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('salaries_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('gross_salary'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('overtime'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('addition'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('cash_advanced'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('tax_payment'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('net_salary'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('net_pay'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('tax_paid'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('salary_paid'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('total_paid'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('payment_status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$total_balance = $data_row->total_net_pay - $data_row->total_paid;
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->company);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->month."/".$data_row->year);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->total_gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->total_overtime));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->total_addition));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->total_cash_advanced));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->total_tax_payment));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->total_net_salary));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->total_net_pay));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->total_tax_paid));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->total_salary_paid));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->total_paid));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($total_balance));
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $this->bpas->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, lang($data_row->payment_status));
                    $row++;
                }
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
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
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
				$filename = 'salaries_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
									companies.company,
									CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_gross_salary,0) as total_gross_salary,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_overtime,0) as total_overtime,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_addition,0) as total_addition,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_cash_advanced,0) as total_cash_advanced,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_payment,0) as total_tax_payment,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_salary,0) as total_net_salary,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) as total_net_pay,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_paid,0) as total_tax_paid,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_salary_paid,0) as total_salary_paid,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) as total_paid,
									(IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) - IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0)) as total_balance,
									pay_salaries.note,
									pay_salaries.status,
									pay_salaries.payment_status,
									pay_salaries.attachment,
									pay_salaries.id as id")
							->from("pay_salaries")
							->join("companies","companies.id = pay_salaries.biller_id","left")
							->join("users","users.id = pay_salaries.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries.biller_id', $biller);
            }
            if ($position) {
                $this->datatables->where('pay_salaries.position_id', $position);
            }
            if ($department) {
                $this->datatables->where('pay_salaries.department_id', $department);
            }
			if ($group) {
                $this->datatables->where('pay_salaries.group_id', $group);
            }
            if ($user) {
                $this->datatables->where('pay_salaries.created_by', $user);
            }
			if($y_month){
				$this->datatables->where('pay_salaries.year', $year);
				$this->datatables->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function salary_details_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salary_details_report')));
        $meta = array('page_title' => lang('salary_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/salary_details_report', $meta, $this->data);
	}
	public function getSalaryDetailsReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('salary_details_report');
		$biller 	= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group 		= $this->input->get('group') ? $this->input->get('group') : NULL;
		$position 	= $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								hr_employees_working_info.self_tax as self_tax,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".basic_salary,0) as basic_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".overtime,0) as overtime,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".addition_amount,0) as addition_amount,
								".$this->db->dbprefix('pay_salary_items').".additions as additions,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".deduction_amount,0) as deduction_amount,
								".$this->db->dbprefix('pay_salary_items').".deductions as deductions,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".severance,0) as severance,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".indemnity,0) as indemnity,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".seniority,0) as seniority,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".seniority_response_tax,0) as seniority_response_tax,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".nssf_salary_usd,0) as nssf_salary_usd,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".nssf_salary_riel,0) as nssf_salary_riel,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".contributory_nssf,0) as contributory_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_staff,0) as pension_by_staff,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_company,0) as pension_by_company,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".health_nssf,0) as health_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".accident_nssf,0) as accident_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary,0) as gross_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary_riel,0) as gross_salary_riel,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".spouse,0) as spouse,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".children,0) as children,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".spouse_children_reduction,0) as spouse_children_reduction,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".Taxbasesalary,0) as Taxbasesalary,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) as tax_payment,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment_riel,0) as tax_payment_riel,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) as net_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".cash_advanced,0) as cash_advanced,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pre_salary,0) as pre_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0) as tax_paid,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) as salary_paid,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".severance,0) as severance,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".severance_riel,0) as severance_riel,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".indemnity,0) as indemnity,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".severance_riel,0) as severance_riel,
								(IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as total_paid,
								(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) - (IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0))) as balance,
								pay_salaries.status,
								pay_salary_items.payment_status,
								pay_salary_items.id as id")
						->from("pay_salaries")
						->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_salaries.year', $year);
				$this->db->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
			$title = ['J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
				'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
				'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK','CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
				'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK','DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ',
				'EA','EB','EC','ED','EE','EF','EG','EH','EI','EJ','EK','EL','EM','EN','EO','EP','EQ','ER','ES','ET','EU','EV','EW','EX','EY','EZ'
			];
			
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
				$this->excel->getActiveSheet()->mergeCells('A1:A2');
				$this->excel->getActiveSheet()->mergeCells('B1:B2');
				$this->excel->getActiveSheet()->mergeCells('C1:C2');
				$this->excel->getActiveSheet()->mergeCells('D1:D2');
				$this->excel->getActiveSheet()->mergeCells('E1:E2');
				$this->excel->getActiveSheet()->mergeCells('F1:F2');
				$this->excel->getActiveSheet()->mergeCells('G1:G2');
				$this->excel->getActiveSheet()->mergeCells('H1:H2');
				$this->excel->getActiveSheet()->mergeCells('I1:I2');

					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('basic_salary'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('overtime'));
					$additions = $this->payrolls_model->getAdditions();
					$deductions = $this->payrolls_model->getDeductions();
					// $this->excel->getActiveSheet()->SetCellValue('J1', lang('addition'));
					$add_ignor = array(1,8,9,10);
					$decuct_ignor = array();
					for ($i=0; $i < sizeof($additions)-sizeof($add_ignor); $i++) { 
						
						if($i == 0){
							$this->excel->getActiveSheet()->SetCellValue($title[$i].'1', lang('addition'));
							$this->excel->getActiveSheet()->mergeCells($title[$i].'1:'.$title[sizeof($additions)-$i-1-sizeof($add_ignor)].'1');
							$this->excel->getActiveSheet()->getStyle($title[$i].'1:'.$title[sizeof($additions)-$i-1-sizeof($add_ignor)].'1')->applyFromArray($style);
						}
					}
					$p = 0;
					foreach ($additions as $key => $value) {
						if(!in_array($value->id, $add_ignor)){
							$this->excel->getActiveSheet()->SetCellValue($title[$p].'2', $value->name);
							$p++;
						}
					}
					$k = $i;
					for ($j = $i; $j < $k + sizeof($deductions); $j++) { 
						if($j == $k){
							$this->excel->getActiveSheet()->SetCellValue($title[$k].'1', lang('deduction'));
							$this->excel->getActiveSheet()->mergeCells($title[$k].'1:'.$title[$k-1+ sizeof($deductions)].'1');
							$this->excel->getActiveSheet()->getStyle($title[$k].'1:'.$title[$k-1+ sizeof($deductions)].'1')->applyFromArray($style);
						}
					} 
					$q = $i;
					foreach ($deductions as $key => $value) {
						if(!in_array($value->id, $decuct_ignor)){
							$this->excel->getActiveSheet()->SetCellValue($title[$q].'2', $value->name);
							$q++;
						}
					}
					$r= $j; 
					
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('seniority'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('seniority_response_tax'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('salary_usd'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('salary_riel'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('contributory_nssf'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('pension_by_staff'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('pension_by_company'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('health_nssf'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('accident_nssf'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('gross_salary'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('gross_salary_riel'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('spouse'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('children'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('amount_reduction'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_base_for_salary'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_payment'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_payment_riel'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('self_tax'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('net_salary'));
					$r= $r+ 1;
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('severance'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('indemnity'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('cash_advanced'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('pre_salary'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('net_pay'));
					$r= $r+ 1;
					
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_paid'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('salary_paid'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('total_paid'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('balance'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('status'));
					$r= $r+ 1; 
					$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
					$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('payment_status'));
                $row = 3; $total = 0;
				$s= $j; 
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($data_row->basic_salary));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatMoney($data_row->overtime));
					$data_additions = json_decode($data_row->additions);
					$data_deductions = json_decode($data_row->deductions);
					$ip = 0;
					foreach ($additions as $main_key => $main_value) {
						foreach ($data_additions as $key => $value) {
							if(!in_array($main_value->id, $add_ignor) && $value->id == $main_value->id){
								$this->excel->getActiveSheet()->SetCellValue($title[$ip] .$row, $value->value);
								$ip++;
							}
						}
					}
					$iq = $ip;
					foreach ($deductions as $main_key => $main_value) {
						foreach ($data_deductions as $key => $value) {
							if(!in_array($main_value->id, $decuct_ignor) && $value->id == $main_value->id){
								$this->excel->getActiveSheet()->SetCellValue($title[$iq] . $row, $value->value);
								$iq++;
							}
						}
					}
					
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->seniority));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->seniority_response_tax));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->nssf_salary_usd));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->nssf_salary_riel));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->contributory_nssf));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->pension_by_staff));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->pension_by_company));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->health_nssf));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->accident_nssf));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->gross_salary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoneykh($data_row->gross_salary_riel));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatQuantity($data_row->spouse));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatQuantity($data_row->children));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->spouse_children_reduction));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->Taxbasesalary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->tax_payment));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->tax_payment_riel));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $data_row->self_tax ? lang('yes') :lang('no') );
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->net_salary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->severance));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->indemnity));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->cash_advanced));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->pre_salary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->net_pay));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->tax_paid));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->salary_paid));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->total_paid));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, $this->bpas->formatMoney($data_row->balance));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, lang($data_row->status));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s] . $row, lang($data_row->payment_status));
					$s= $j; 
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(15);
				$filename = 'salary_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
				DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
				CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
				hr_employees.empcode,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
				
				IFNULL(".$this->db->dbprefix('pay_salary_items').".basic_salary,0) as basic_salary,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".overtime,0) as overtime,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".addition_amount,0) as addition,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".deduction_amount,0) as deduction,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".seniority_response_tax,0) as seniority_response_tax,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_staff,0) as pension_by_staff,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary,0) as gross_salary,
				
				IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) as tax_payment,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) as net_salary,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".severance,0) as severance,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".indemnity,0) as indemnity,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".cash_advanced,0) as cash_advanced,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".pre_salary,0) as pre_salary,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0) as tax_paid,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) as salary_paid,
				(IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as total_paid,
				(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) - (IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) )) as balance,
				{$this->db->dbprefix('pay_salaries')}.status,
				pay_salary_items.payment_status,
				pay_salary_items.id as id")
							->from("pay_salaries")
							->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_salaries.year', $year);
				$this->datatables->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function getSalaryDetailsReport_($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('salary_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								hr_employees_working_info.self_tax as self_tax,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".basic_salary,0) as basic_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".overtime,0) as overtime,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".addition_amount,0) as addition_amount,
								".$this->db->dbprefix('pay_salary_items').".additions as additions,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".deduction_amount,0) as deduction_amount,
								".$this->db->dbprefix('pay_salary_items').".deductions as deductions,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".seniority,0) as seniority,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".seniority_response_tax,0) as seniority_response_tax,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".severance,0) as severance,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".indemnity,0) as indemnity,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".nssf_salary_usd,0) as nssf_salary_usd,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".nssf_salary_riel,0) as nssf_salary_riel,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".contributory_nssf,0) as contributory_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_staff,0) as pension_by_staff,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_company,0) as pension_by_company,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".health_nssf,0) as health_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".accident_nssf,0) as accident_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary,0) as gross_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary_riel,0) as gross_salary_riel,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".spouse,0) as spouse,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".children,0) as children,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".spouse_children_reduction,0) as spouse_children_reduction,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".Taxbasesalary,0) as Taxbasesalary,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) as tax_payment,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment_riel,0) as tax_payment_riel,

								IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) as net_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".cash_advanced,0) as cash_advanced,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pre_salary,0) as pre_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0) as tax_paid,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) as salary_paid,
								
								(IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as total_paid,
								(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) - (IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0))) as balance,
								pay_salaries.status,
								pay_salary_items.payment_status,
								pay_salary_items.id as id")
						->from("pay_salaries")
						->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_salaries.year', $year);
				$this->db->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            $title = ['J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
				'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
				'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
				'CA','CB','CC','CD','CE','CF','CG','CH','CI','CJ','CK','CL','CM','CN','CO','CP','CQ','CR','CS','CT','CU','CV','CW','CX','CY','CZ',
				'DA','DB','DC','DD','DE','DF','DG','DH','DI','DJ','DK','DL','DM','DN','DO','DP','DQ','DR','DS','DT','DU','DV','DW','DX','DY','DZ',
				'EA','EB','EC','ED','EE','EF','EG','EH','EI','EJ','EK','EL','EM','EN','EO','EP','EQ','ER','ES','ET','EU','EV','EW','EX','EY','EZ'
			];
			// var_dump($title);
			// exit();
            if (!empty($data)) {
            	$additions = $this->payrolls_model->getAdditions();
				$deductions = $this->payrolls_model->getDeductions();
                $this->load->library('excel');
                $style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('salary_details_report'));
				$this->excel->getActiveSheet()->mergeCells('A1:A2');
				$this->excel->getActiveSheet()->mergeCells('B1:B2');
				$this->excel->getActiveSheet()->mergeCells('C1:C2');
				$this->excel->getActiveSheet()->mergeCells('D1:D2');
				$this->excel->getActiveSheet()->mergeCells('E1:E2');
				$this->excel->getActiveSheet()->mergeCells('F1:F2');
				$this->excel->getActiveSheet()->mergeCells('G1:G2');
				$this->excel->getActiveSheet()->mergeCells('H1:H2');
				$this->excel->getActiveSheet()->mergeCells('I1:I2');

				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('basic_salary'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('overtime'));
				$add_ignor = array(1,8,9,10);
				$decuct_ignor = array();
				for ($i=0; $i < sizeof($additions)-sizeof($add_ignor); $i++) { 
					
					if($i == 0){
						$this->excel->getActiveSheet()->SetCellValue($title[$i].'1', lang('addition'));
						$this->excel->getActiveSheet()->mergeCells($title[$i].'1:'.$title[sizeof($additions)-$i-1-sizeof($add_ignor)].'1');
						$this->excel->getActiveSheet()->getStyle($title[$i].'1:'.$title[sizeof($additions)-$i-1-sizeof($add_ignor)].'1')->applyFromArray($style);
					}
				}
				$p = 0;
				foreach ($additions as $key => $value) {
					if(!in_array($value->id, $add_ignor)){
						$this->excel->getActiveSheet()->SetCellValue($title[$p].'2', $value->name);
						$p++;
					}
				}
				$k = $i;
				for ($j = $i; $j < $k + sizeof($deductions); $j++) { 
					if($j == $k){
						$this->excel->getActiveSheet()->SetCellValue($title[$k].'1', lang('deduction'));
						$this->excel->getActiveSheet()->mergeCells($title[$k].'1:'.$title[$k-1+ sizeof($deductions)].'1');
						$this->excel->getActiveSheet()->getStyle($title[$k].'1:'.$title[$k-1+ sizeof($deductions)].'1')->applyFromArray($style);
					}
				} 
				$q = $i;
				foreach ($deductions as $key => $value) {
					if(!in_array($value->id, $decuct_ignor)){
						$this->excel->getActiveSheet()->SetCellValue($title[$q].'2', $value->name);
						$q++;
					}
				}
				$r= $j; 

				//$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('addition'));
				//$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('deduction'));

				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('seniority'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('seniority_response_tax'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('severance'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('indemnity'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('salary_usd'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('salary_riel'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('contributory_nssf'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('pension_by_staff'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('pension_by_company'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('health_nssf'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('accident_nssf'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('gross_salary'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('gross_salary_riel'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('spouse'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('children'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('amount_reduction'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_base_for_salary'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_payment'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_payment_riel'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('self_tax'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('net_salary'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('cash_advanced'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('pre_salary'));
				$r= $r+ 1;
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('net_pay'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('tax_paid'));
				$r= $r+ 1;
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('salary_paid'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('total_paid'));
				$r= $r+ 1;
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('balance'));
				$r= $r+ 1; 
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('status'));
				$r= $r+ 1;
				$this->excel->getActiveSheet()->mergeCells($title[$r].'1:'.$title[$r].'2');
				$this->excel->getActiveSheet()->SetCellValue($title[$r].'1', lang('payment_status'));
				
                $row = 3; $total = 0;$s= $j; 
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($data_row->basic_salary));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatMoney($data_row->overtime));

					//$this->excel->getActiveSheet()->SetCellValue('J' .$row, $this->bpas->formatMoney($data_row->addition_amount-$data_row->seniority));
					//$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatMoney($data_row->deduction_amount));
					$data_additions = json_decode($data_row->additions);
					$data_deductions = json_decode($data_row->deductions);
					$ip = 0;
					foreach ($additions as $main_key => $main_value) {
						foreach ($data_additions as $key => $value) {
							if(!in_array($main_value->id, $add_ignor) && $value->id == $main_value->id){
								$this->excel->getActiveSheet()->SetCellValue($title[$ip] .$row, $value->value);
								$ip++;
							}
						}
					}
					$iq = $ip;
					foreach ($deductions as $main_key => $main_value) {
						foreach ($data_deductions as $key => $value) {
							if(!in_array($main_value->id, $decuct_ignor) && $value->id == $main_value->id){
								$this->excel->getActiveSheet()->SetCellValue($title[$iq] . $row, $value->value);
								$iq++;
							}
						}
					}


					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->seniority));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->seniority_response_tax));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->severance));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->indemnity));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->nssf_salary_usd));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->nssf_salary_riel));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->contributory_nssf));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->pension_by_staff));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->pension_by_company));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->health_nssf));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->accident_nssf));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->gross_salary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoneykh($data_row->gross_salary_riel));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatQuantity($data_row->spouse));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatQuantity($data_row->children));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->spouse_children_reduction));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->Taxbasesalary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->tax_payment));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->tax_payment_riel));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $data_row->self_tax ? lang('yes') :lang('no') );
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->net_salary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->cash_advanced));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->pre_salary));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->net_pay));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->tax_paid));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->salary_paid));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->total_paid));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, $this->bpas->formatMoney($data_row->balance));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, lang($data_row->status));
					$s++;
					$this->excel->getActiveSheet()->SetCellValue($title[$s]. $row, lang($data_row->payment_status));
					$s= $j; 
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('T')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('U')->setWidth(15);
				$filename = 'salaries_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
				DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
				CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
				hr_employees.empcode,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
				hr_positions.name as position,
				hr_departments.name as department,
				hr_groups.name as group,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".gross_salary,0) as gross_salary,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".overtime,0) as overtime,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".addition_amount,0) as addition,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".deduction_amount,0) as deduction,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".cash_advanced,0) as cash_advanced,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_payment,0) as tax_payment,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".net_salary,0) as net_salary,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".tax_paid,0) as tax_paid,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) as salary_paid,
				(IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0)) as total_paid,
				(IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) - (IFNULL(".$this->db->dbprefix('pay_salary_items').".salary_paid,0) )) as balance,
				pay_salaries.status,
				pay_salary_items.payment_status,
				pay_salary_items.id as id")
							->from("pay_salaries")
							->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_salaries.year', $year);
				$this->datatables->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	
	public function salary_banks_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salary_banks_report')));
        $meta = array('page_title' => lang('salary_banks_report'), 'bc' => $bc);
        $this->page_construct('payrolls/salary_banks_report', $meta, $this->data);
	}
	public function getSalaryBanksReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('salary_banks_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								hr_employees_bank.bank_account,
								hr_employees_bank.account_no,
								hr_employees_bank.account_name,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
								pay_salary_items.id as id")
						->from("pay_salaries")
						->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
						->join("hr_employees_bank","hr_employees_bank.employee_id = pay_salary_items.employee_id","inner")
						->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_salaries.year', $year);
				$this->db->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('salary_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('bank_name'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('account_no'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('account_name'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('net_pay'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->bank_account);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->account_no);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->account_name);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->net_pay));

                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$filename = 'salary_banks_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									{$this->db->dbprefix('hr_employees_bank')}.bank_account,
									hr_employees_bank.account_no,
									hr_employees_bank.account_name,
									IFNULL(".$this->db->dbprefix('pay_salary_items').".net_pay,0) as net_pay,
									pay_salary_items.id as id")
							->from("pay_salaries")
							->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
							->join("hr_employees_bank","hr_employees_bank.employee_id = pay_salary_items.employee_id","inner")
							->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_salaries.year', $year);
				$this->datatables->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function modal_view_bank($id = false){
		$this->bpas->checkPermissions('salary_banks_report', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary = $this->payrolls_model->getSalaryByID($id);
		$this->data['salary'] = $salary;
        $this->data['salary_items'] = $this->payrolls_model->getSalaryBankItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_bank', $this->data);
	}
	public function get_payment_employees(){
		$biller_id = $this->input->get('biller_id');
		$position_id = $this->input->get('position_id');
		$department_id = $this->input->get('department_id');
		$group_id = $this->input->get('group_id');
		$y_month = explode("/",$this->input->get('month'));
		$month = $y_month[0];
		$year = $y_month[1];
		$employees = $this->payrolls_model->getPaymentEmployee($biller_id,$position_id,$department_id,$group_id,$month,$year);
		echo json_encode($employees);
	}
	public function payments($biller_id = false){
		$this->bpas->checkPermissions("payments");
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('payments')));
        $meta = array('page_title' => lang('payments'), 'bc' => $bc);
        $this->page_construct('payrolls/payments', $meta, $this->data);
	}
	public function getPayments($biller_id = false){
		$this->bpas->checkPermissions("payments");
        $edit_link = anchor('admin/payrolls/edit_payment/$1', '<i class="fa fa-edit"></i> ' . lang('edit_payment'), ' class="edit_payment"');
        $delete_link = "<a href='#' class='delete_payment po' title='<b>" . $this->lang->line("delete_payment") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_payment/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_payment') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_payments.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_payments').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_payments').".month,'/',".$this->db->dbprefix('pay_payments').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_payments').".total_tax_paid,0) as total_tax_paid,
									IFNULL(".$this->db->dbprefix('pay_payments').".total_salary_paid,0) as total_salary_paid,
									IFNULL(".$this->db->dbprefix('pay_payments').".total_salary_paid,0) as total_paid,
									pay_payments.note,
									pay_payments.attachment")
							->from("pay_payments")
							->join("users","users.id = pay_payments.created_by","left");
		if ($biller_id) {
            $this->datatables->where("pay_payments.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_payments.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_payments.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_payment($salary_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$paying_from = $this->input->post('paying_from') ? $this->input->post('paying_from') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$total_tax_paid = 0;
			$total_salary_paid = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$items[] = array(
								"employee_id"   => $_POST['employee_id'][$r],
								"tax_paid" 		=> $_POST['pay_tax'][$r],
								"salary_paid" 	=> $_POST['pay_salary'][$r]
							);
				$total_tax_paid += $_POST['pay_tax'][$r];		
				$total_salary_paid += $_POST['pay_salary'][$r];
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				"salary_id"		=> $salary_id,
				'date' 			=> $date,
                'month' 		=> $month,
                'year' 			=> $year,
                'biller_id' 	=> $biller_id,
                'position_id' 	=> $position_id,
				'department_id' => $department_id,
				'group_id' 		=> $group_id,
				'note' 			=> $note,
				'total_tax_paid' => $total_tax_paid,
				'total_salary_paid' => $total_salary_paid,
				'created_by' 	=> $this->session->userdata('user_id'),
				'created_at' 	=> date('Y-m-d H:i:s'),
				'account_code' 	=> $paying_from
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addPayment($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("payment_added"));          
			admin_redirect('payrolls/payments');
        } else {
			if($salary_id){
				$salary = $this->payrolls_model->getSalaryByID($salary_id);
				$this->data['salary'] = $salary;
				$this->data['salary_items'] = $this->payrolls_model->getSalaryItems($salary_id);
				$this->data['positions'] = $this->payrolls_model->getPositions($salary->biller_id);
				$this->data['departments'] = $this->payrolls_model->getDepartments($salary->biller_id);
				if($salary->department_id){
					$this->data['groups'] = $this->payrolls_model->getGroups($salary->department_id);
				}
			}
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('','','1');
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/payments'), 'page' => lang('payments')), array('link' => '#', 'page' => lang('add_payment')));
            $meta = array('page_title' => lang('add_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/add_payment', $meta, $this->data);
        }
	}
	public function edit_payment($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$paying_from = $this->input->post('paying_from') ? $this->input->post('paying_from') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$total_tax_paid = 0;
			$total_salary_paid = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$items[] = array(
								"payment_id" => $id,
								"employee_id" => $_POST['employee_id'][$r],
								"tax_paid" => $_POST['pay_tax'][$r],
								"salary_paid" => $_POST['pay_salary'][$r]
							);
				$total_tax_paid += $_POST['pay_tax'][$r];		
				$total_salary_paid += $_POST['pay_salary'][$r];
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'total_tax_paid' => $total_tax_paid,
				'total_salary_paid' => $total_salary_paid,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'account_code' => $paying_from
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updatePayment($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("payment_updated"));          
			admin_redirect('payrolls/payments');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$payment = $this->payrolls_model->getPaymentByID($id);
			$this->data['payment'] = $payment;
			$this->data['payment_items'] = $this->payrolls_model->getPaymentItems($id);
			$this->data['positions'] = $this->payrolls_model->getPositions($payment->biller_id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($payment->biller_id);
			if($payment->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($payment->department_id);
			}
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$payment->account_code,'1');
			}
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/payments'), 'page' => lang('payments')), array('link' => '#', 'page' => lang('edit_payment')));
            $meta = array('page_title' => lang('edit_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_payment', $meta, $this->data);
        }
	}
	public function delete_payment($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deletePayment($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('payment_deleted')]);
            }
            $this->session->set_flashdata('message', lang('payment_deleted'));
            admin_redirect('welcome');
        }
    }
	public function modal_view_payment($id = false){
		$this->bpas->checkPermissions('payments', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $payment = $this->payrolls_model->getPaymentByID($id);
		$this->data['payment'] = $payment;
        $this->data['payment_items'] = $this->payrolls_model->getPaymentItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($payment->biller_id);
        $this->data['created_by'] = $this->site->getUser($payment->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_payment', $this->data);
	}
	
	function payment_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_payment');
                    foreach ($_POST['val'] as $id) {
						$this->payrolls_model->deletePayment($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("payment_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('payment');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));	
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('tax_paid'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('salary_paid'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('total_paid'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
		
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $payment = $this->payrolls_model->getPaymentByID($id); 
						$user = $this->site->getUserByID($payment->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($payment->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $payment->month."/".$payment->year);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($payment->total_tax_paid));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($payment->total_salary_paid));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($payment->total_tax_paid + $payment->total_salary_paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($payment->note));

						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'payment_list_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	public function payments_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['users'] = $this->site->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('payments_report')));
        $meta = array('page_title' => lang('payments_report'), 'bc' => $bc);
        $this->page_construct('payrolls/payments_report', $meta, $this->data);
	}
	public function getPaymentsReport($pdf = NULL, $xls = NULL)
    {
        $this->bpas->checkPermissions('payments_report');
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('pay_payments').".date, '%Y-%m-%d %T') as date,
								companies.company,
								CONCAT(".$this->db->dbprefix('pay_payments').".month,'/',".$this->db->dbprefix('pay_payments').".year) as month,
								CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
								IFNULL(".$this->db->dbprefix('pay_payments').".total_tax_paid,0) as total_tax_paid,
								IFNULL(".$this->db->dbprefix('pay_payments').".total_salary_paid,0) as total_salary_paid,
								(IFNULL(".$this->db->dbprefix('pay_payments').".total_salary_paid,0)) as total_paid,
								pay_payments.note,
								pay_payments.attachment,
								pay_payments.id as id")
						->from("pay_payments")
						->join("companies","companies.id = pay_payments.biller_id","left")
						->join("users","users.id = pay_payments.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_payments.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_payments.biller_id', $biller);
            }
            if ($user) {
                $this->db->where('pay_payments.created_by', $user);
            }
			if($y_month){
				$this->db->where('pay_payments.year', $year);
				$this->db->where('pay_payments.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_payments.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('tax_paid'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('salary_paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('total_paid'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('note'));
	
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));	
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->company);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->month."/".$data_row->year);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->total_tax_paid));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->total_salary_paid));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->total_paid));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->remove_tag($data_row->note));
                    $row++;
                }
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$filename = 'payments_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('pay_payments').".date, '%Y-%m-%d %T') as date,
									companies.company,
									CONCAT(".$this->db->dbprefix('pay_payments').".month,'/',".$this->db->dbprefix('pay_payments').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_payments').".total_tax_paid,0) as total_tax_paid,
									IFNULL(".$this->db->dbprefix('pay_payments').".total_salary_paid,0) as total_salary_paid,
									IFNULL(".$this->db->dbprefix('pay_payments').".total_salary_paid,0) as total_paid,
									pay_payments.note,
									pay_payments.attachment,
									pay_payments.id as id")
							->from("pay_payments")
							->join("companies","companies.id = pay_payments.biller_id","left")
							->join("users","users.id = pay_payments.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_payments.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_payments.biller_id', $biller);
            }
            if ($user) {
                $this->datatables->where('pay_payments.created_by', $user);
            }
			if($y_month){
				$this->datatables->where('pay_payments.year', $year);
				$this->datatables->where('pay_payments.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_payments.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	public function payment_details_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('payment_details_report')));
        $meta = array('page_title' => lang('payment_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/payment_details_report', $meta, $this->data);
	}
	public function getPaymentDetailsReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('payment_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_payments').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_payments').".month,'/',".$this->db->dbprefix('pay_payments').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								IFNULL(".$this->db->dbprefix('pay_payment_items').".tax_paid,0) as tax_paid,
								IFNULL(".$this->db->dbprefix('pay_payment_items').".salary_paid,0) as salary_paid,
								(IFNULL(".$this->db->dbprefix('pay_payment_items').".tax_paid,0) + IFNULL(".$this->db->dbprefix('pay_payment_items').".salary_paid,0)) as total_paid,
								pay_payments.id as id")
						->from("pay_payments")
						->join("pay_payment_items","pay_payment_items.payment_id = pay_payments.id","inner")
						->join("hr_employees","hr_employees.id = pay_payment_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_payment_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_payment_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_payments.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_payments.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_payment_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_payments.year', $year);
				$this->db->where('pay_payments.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_payments.biller_id', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payment_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('tax_paid'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('salary_paid'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('total_paid'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->tax_paid));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->salary_paid));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->total_paid));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$filename = 'payment_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_payments').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_payments').".month,'/',".$this->db->dbprefix('pay_payments').".year) as month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_payment_items').".tax_paid,0) as tax_paid,
									IFNULL(".$this->db->dbprefix('pay_payment_items').".salary_paid,0) as salary_paid,
									(IFNULL(".$this->db->dbprefix('pay_payment_items').".tax_paid,0) + IFNULL(".$this->db->dbprefix('pay_payment_items').".salary_paid,0)) as total_paid,
									pay_payments.id as id")
							->from("pay_payments")
							->join("pay_payment_items","pay_payment_items.payment_id = pay_payments.id","inner")
							->join("hr_employees","hr_employees.id = pay_payment_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_payment_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_payment_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_payments.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_payments.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_payment_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_payments.year', $year);
				$this->datatables->where('pay_payments.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_payments.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function cash_advances($biller_id = null)
	{
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('cash_advances')));
        $meta = array('page_title' => lang('cash_advances'), 'bc' => $bc);
        $this->page_construct('payrolls/cash_advances', $meta, $this->data);
	}
	public function getCashAdvances($biller_id = null)
	{
		$this->bpas->checkPermissions('cash_advances');
		$view_payback_link = anchor('admin/payrolls/view_payback/$1', '<i class="fa fa-money"></i> ' . lang('view_payback'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payback" data-target="#myModal"');
		$add_payback_link = anchor('admin/payrolls/add_payback/$1', '<i class="fa fa-money"></i> ' . lang('add_payback'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_payback" data-target="#myModal"');
		$edit_link = anchor('admin/payrolls/edit_cash_advance/$1', '<i class="fa fa-edit"></i> ' . lang('edit_cash_advance'), ' class="edit_cash_advance"');
        $delete_link = "<a href='#' class='delete_cash_advance po' title='<b>" . $this->lang->line("delete_cash_advance") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_cash_advance/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_cash_advance') . "</a>";
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_cash_advance']){
			$approve_link = "<a href='#' class='po approve_cash_advance' title='" . $this->lang->line("approve_cash_advance") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/approve_cash_advance/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_cash_advance') . "</a>";

			$reject_link = "<a href='#' class='po reject_cash_advance' title='" . $this->lang->line("reject_cash_advance") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/reject_cash_advance/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('reject_cash_advance') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_cash_advance' title='<b>" . $this->lang->line("unapprove_cash_advance") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/unapprove_cash_advance/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_cash_advance') . "</a>";
		}
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $approve_link . '</li>
						<li>' . $view_payback_link . '</li>
						<li>' . $add_payback_link . '</li>
						<li>' . $reject_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
		$this->load->library('datatables');
		$this->datatables
             ->select("
						pay_cash_advances.id as id, 
						pay_cash_advances.date, 
						pay_cash_advances.reference_no,
						concat(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as requested_by,
						IFNULL(".$this->db->dbprefix('pay_cash_advances').".amount,0) as amount,
						IFNULL(".$this->db->dbprefix('pay_cash_advances').".paid,0) as paid,
						(IFNULL(".$this->db->dbprefix('pay_cash_advances').".amount,0) - IFNULL(".$this->db->dbprefix('pay_cash_advances').".paid,0))  as balance,
						pay_cash_advances.description,
						pay_cash_advances.status,
						pay_cash_advances.payment_status,
						pay_cash_advances.attachment
						")
            ->from("pay_cash_advances")
			->join("hr_employees","hr_employees.id=requested_by","left");
			if ($biller_id) {
				$this->datatables->where("pay_cash_advances.biller_id", $biller_id);
			}	
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where("pay_cash_advances.biller_id", $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where("pay_cash_advances.created_by", $this->session->userdata('user_id'));
			}
			$this->datatables->add_column("Actions", $action, "id");
		echo $this->datatables->generate();
	}
	
	public function add_cash_advance()
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_rules('amount', $this->lang->line("amount"), 'required|greater_than[0]');
		$this->form_validation->set_rules('employee', $this->lang->line("employee"), 'required');
        if ($this->form_validation->run() == true) {			
			$employee_detail = $this->payrolls_model->getEmployeeByID($this->input->post("employee"));
			$biller = $employee_detail->biller_id;
			$biller_detail = $this->site->getCompanyByID($biller);			
			$reference = $this->input->post("reference") ? $this->input->post("reference") : $this->site->getReference('cv',$biller);
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-cash_advances_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$data = array(
					  "date"			=> $date,
					  "reference_no"	=> $reference,
					  "biller_id"       => $biller_detail->id,
					  "biller"			=> ($biller_detail->company != '-' ? $biller_detail->company : $biller_detail->name),
					  "requested_by"    => $this->input->post("employee", true),
					  "amount"      	=> $this->input->post("amount", true),
					  "description" 	=> $this->input->post("description", true),
					  "created_by" 		=> $this->session->userdata('user_id'),
					  "account_code"	=> $this->input->post('paying_from', true),
					  "status"			=> "pending",
					  "payment_status"	=> "pending"
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
                $data['attachment'] = $photo;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addCashAdvance($data)) {
			$this->session->set_flashdata('message', lang("cash_advance_added"));
			admin_redirect('payrolls/cash_advances');
				
		}else{
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('','','1');
				$this->data['paid_by'] = $this->accounts_model->getAllChartAccountBank();
			}
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/cash_advances'), 'page' => lang('cash_advances')), array('link' => '#', 'page' => lang('add_cash_advance')));
			$meta = array('page_title' => lang('add_cash_advance'), 'bc' => $bc);
			$this->page_construct('payrolls/add_cash_advance', $meta, $this->data);
		}
	}
	public function edit_cash_advance($id = false)
	{
		$this->bpas->checkPermissions();
		$this->form_validation->set_rules('amount', $this->lang->line("amount"), 'required|greater_than[0]');
		$this->form_validation->set_rules('employee', $this->lang->line("employee"), 'required');
        if ($this->form_validation->run() == true) {			
			$employee_detail = $this->payrolls_model->getEmployeeByID($this->input->post("employee"));
			$biller = $employee_detail->biller_id;
			$biller_detail = $this->site->getCompanyByID($biller);			
			$reference = $this->input->post("reference") ? $this->input->post("reference") : $this->site->getReference('cv',$biller);
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-cash_advances_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$data = array(
					  "date"			=> $date,
					  "reference_no"	=> $reference,
					  "biller_id"       => $biller_detail->id,
					  "biller"			=> ($biller_detail->company != '-' ? $biller_detail->company : $biller_detail->name),
					  "requested_by"    => $this->input->post("employee", true),
					  "amount"      	=> $this->input->post("amount", true),
					  "description" 	=> $this->input->post("description", true),
					  "updated_by" 		=> $this->session->userdata('user_id'),
					  "account_code"	=> $this->input->post('paying_from', true)
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
                $data['attachment'] = $photo;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateCashAdvance($id, $data)) {
			$this->session->set_flashdata('message', lang("cash_advance_updated"));
			admin_redirect('payrolls/cash_advances');
				
		}else{
			$cash_advance = $this->payrolls_model->getCashAdvanceByID($id);
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$cash_advance->account_code,'1');
			}
			$this->data['cash_advance'] = $cash_advance;
			$this->data['requested_by'] = $this->payrolls_model->getEmployeeByID($cash_advance->requested_by);
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/cash_advances'), 'page' => lang('cash_advances')), array('link' => '#', 'page' => lang('edit_cash_advance')));
			$meta = array('page_title' => lang('edit_cash_advance'), 'bc' => $bc);
			$this->page_construct('payrolls/edit_cash_advance', $meta, $this->data);
		}
	}
	public function delete_cash_advance($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteCashAdavnce($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('cash_advance_deleted')]);
            }
            $this->session->set_flashdata('message', lang('cash_advance_deleted'));
            admin_redirect('welcome');
        }
    }
	
	function cash_advance_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_benefit');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$cash_advance = $this->payrolls_model->getCashAdvanceByID($id);
						if($cash_advance->status == "pending" && $cash_advance->payment_status == "pending"){
							$deleted = 1;
							$this->payrolls_model->deleteCashAdavnce($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("cash_advance_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("cash_advance_cannot_delete"));
					}
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('cash_advance');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('requested_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $cash_advance = $this->payrolls_model->getCashAdvanceByID($id); 
						$employee = $this->payrolls_model->getEmployeeByID($cash_advance->requested_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($cash_advance->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $cash_advance->reference_no);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $employee->lastname." ".$employee->firstname);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($cash_advance->amount));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($cash_advance->paid));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($cash_advance->amount - $cash_advance->paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($cash_advance->description));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang($cash_advance->status));
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($cash_advance->payment_status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'cash_advance_list_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function approve_cash_advance($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateCashAdvanceStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('cash_advance_approved')]);
            }
            $this->session->set_flashdata('message', lang('cash_advance_approved'));
            admin_redirect('welcome');
        }
    }
	public function unapprove_cash_advance($id = null){
        $this->bpas->checkPermissions("approve_cash_advance", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
		}
		if ($this->payrolls_model->updateCashAdvanceStatus($id,"pending")) {
            if ($this->input->is_ajax_request()) {
                 $this->bpas->send_json(['error' => 0, 'msg' => lang('cash_advance_unapproved')]);
            }
            $this->session->set_flashdata('message', lang('cash_advance_unapproved'));
            admin_redirect('welcome');
        }
    }
	public function reject_cash_advance($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateCashAdvanceStatus($id,"rejected")) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('cash_advance_rejected')]);
            }
            $this->session->set_flashdata('message', lang('cash_advance_rejected'));
            admin_redirect('welcome');
        }
    }
	public function add_payback($id = false){
		$this->bpas->checkPermissions('payback');
		$this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $GP['payrolls-cash_advances_date']) {
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
            $payment = array(
                'date' => $date,
				'cash_advance_id' => $id,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
				'account_code' => $this->input->post('paying_to'),
                'created_by' => $this->session->userdata('user_id'),
				'currencies' => json_encode($currencies),
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
		}
		if($this->form_validation->run() == true && $this->payrolls_model->addPayback($payment)){
			$this->session->set_flashdata('message', lang("payback_added"));
			admin_redirect('payrolls/cash_advances');
		}else{
			$cash_advance = $this->payrolls_model->getCashAdvanceByID($id);
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['id'] = $id;
			$this->data['cash_advance'] = $cash_advance;
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('','','1');
			}
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->load->view($this->theme . 'payrolls/add_payback', $this->data);
		}
	}

	public function edit_payback($id = null)
    {
		$this->bpas->checkPermissions('payback');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $GP['payrolls-cash_advances_date']) {
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
            $payment = array(
                'date' => $date,
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
				'account_code' => $this->input->post('paying_to'),
                'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'currencies' => json_encode($currencies),
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

        } elseif ($this->input->post('edit_payback')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        if ($this->form_validation->run() == true && $this->payrolls_model->updatePayback($id, $payment)) {
			$this->session->set_flashdata('message', lang("payback_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
			$payback = $this->payrolls_model->getPaybackByID($id);
			$this->data['payback'] = $payback;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$payback->account_code,'1');
			}
            $this->load->view($this->theme . 'payrolls/edit_payback', $this->data);
        }
    }
	public function delete_payback($id = null)
    {
        $this->bpas->checkPermissions('payback');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deletePayback($id)) {
            $this->session->set_flashdata('message', lang("payback_deleted"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	public function view_payback($id = null){
        $this->bpas->checkPermissions("payback", true);
        $this->data['paybacks'] = $this->payrolls_model->getPaybacksByCashAdvance($id);
        $this->load->view($this->theme . 'payrolls/paybacks', $this->data);
    }
	
	public function cash_advances_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('cash_advances_report')));
        $meta = array('page_title' => lang('cash_advances_report'), 'bc' => $bc);
        $this->page_construct('payrolls/cash_advances_report', $meta, $this->data);
	}
	public function getCashAdvanceReports($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('benefit_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        if ($pdf || $xls) {
			$this->db->select("	
							DATE_FORMAT(".$this->db->dbprefix('pay_cash_advances').".date, '%Y-%m-%d %T') as date,
							pay_cash_advances.biller,
							hr_employees.empcode,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							IFNULL(".$this->db->dbprefix('pay_cash_advances').".amount,0) as amount,
							IFNULL(".$this->db->dbprefix('pay_cash_advances').".paid,0) as paid,
							(IFNULL(".$this->db->dbprefix('pay_cash_advances').".amount,0) - IFNULL(".$this->db->dbprefix('pay_cash_advances').".paid,0)) as balance,
							pay_cash_advances.description,
							CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
							pay_cash_advances.attachment,
							pay_cash_advances.status,
							pay_cash_advances.payment_status,
							pay_cash_advances.id as id")
					->from("pay_cash_advances")
					->join("hr_employees","hr_employees.id = pay_cash_advances.requested_by","left")
					->join("users","users.id = pay_cash_advances.created_by","left")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_cash_advances.requested_by","left")
					->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
					->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
					->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
					->group_by("pay_cash_advances.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_cash_advances.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_cash_advances.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_cash_advances.employee_id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_cash_advances.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('cash_advances_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('payment_status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->biller);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->amount));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->remove_tag($data_row->description));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, lang($data_row->payment_status));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);

				$filename = 'cash_advances_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_cash_advances').".date, '%Y-%m-%d %T') as date,
									pay_cash_advances.biller,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_cash_advances').".amount,0) as amount,
									IFNULL(".$this->db->dbprefix('pay_cash_advances').".paid,0) as paid,
									(IFNULL(".$this->db->dbprefix('pay_cash_advances').".amount,0) - IFNULL(".$this->db->dbprefix('pay_cash_advances').".paid,0)) as balance,
									pay_cash_advances.description,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									pay_cash_advances.attachment,
									pay_cash_advances.status,
									pay_cash_advances.payment_status,
									pay_cash_advances.id as id")
							->from("pay_cash_advances")
							->join("hr_employees","hr_employees.id = pay_cash_advances.requested_by","left")
							->join("users","users.id = pay_cash_advances.created_by","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_cash_advances.requested_by","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_cash_advances.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_cash_advances.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_cash_advances.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_cash_advances.employee_id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_cash_advances.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function modal_view_cash_advance($id = false){
		$this->bpas->checkPermissions('cash_advances', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $cash_advance = $this->payrolls_model->getCashAdvanceByID($id);
		$this->data['cash_advance'] = $cash_advance;
        $this->data['biller'] = $this->site->getCompanyByID($cash_advance->biller_id);
        $this->data['created_by'] = $this->site->getUser($cash_advance->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_cash_advance', $this->data);
	}
	
	
	public function salaries_13($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salaries_13')));
        $meta = array('page_title' => lang('salaries_13'), 'bc' => $bc);
        $this->page_construct('payrolls/salaries_13', $meta, $this->data);
	}
	public function getSalaries13($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$view_payment_link = anchor('admin/payrolls/view_payments_13/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payments" data-target="#myModal"');
		$payment_link = anchor('admin/payrolls/add_payment_13/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add_payment"');
        $edit_link = anchor('admin/payrolls/edit_salary_13/$1', '<i class="fa fa-edit"></i> ' . lang('edit_salary_13'), ' class="edit_salary"');
        $delete_link = "<a href='#' class='delete_salary po' title='<b>" . $this->lang->line("delete_salary_13") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_salary_13/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_salary_13') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_salary']){
			$approve_link = "<a href='#' class='po approve_salary' title='" . $this->lang->line("approve_salary_13") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/approve_salary_13/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_salary_13') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_salary' title='<b>" . $this->lang->line("unapprove_salary_13") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/unapprove_salary_13/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_salary_13') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_payment_link . '</li>
						<li>' . $payment_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_salaries_13.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_salaries_13').".date, '%Y-%m-%d %T') as date,
									pay_salaries_13.year,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".gross_salary,0) as gross_salary,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".annual_amount,0) as annual_amount,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".net_amount,0) as net_amount,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".paid,0) as paid,
									(IFNULL(".$this->db->dbprefix('pay_salaries_13').".net_amount,0) - IFNULL(".$this->db->dbprefix('pay_salaries_13').".paid,0)) as balance,
									pay_salaries_13.status,
									pay_salaries_13.payment_status,
									pay_salaries_13.attachment")
							->from("pay_salaries_13")
							->join("users","users.id = pay_salaries_13.created_by","left");
		if ($biller_id) {
            $this->datatables->where("pay_salaries_13.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_salaries_13.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_salaries_13.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	
	public function add_salary_13(){
		$this->bpas->checkPermissions('add_salary');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('year', $this->lang->line("year"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$year = $this->input->post('year') ? $this->input->post('year') : null;
			$status = "pending";
			$payment_status = "pending";
			$items = false;
			$gross_salary = 0;
			$annual_amount = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$net_salary = $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$annual_leave = $_POST['annual_leave'][$r];
				$al_rate = $this->bpas->formatDecimal($_POST['al_rate'][$r]);
				$al_amount = $annual_leave * $al_rate;
				$subtotal = $net_salary + $al_amount;
				$items[] = array(
								"employee_id" => $_POST['employee_id'][$r],
								"net_salary" => $net_salary,
								"annual_leave" => $annual_leave,
								"al_rate" => $al_rate,
								"al_amount" => $al_amount,
								"subtotal" => $subtotal,
								"paid" => 0,
								"payment_status" => "pending"
							);
				$gross_salary += $net_salary; 	
				$annual_amount += $al_amount;
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'gross_salary' => $gross_salary,
				'annual_amount' => $annual_amount,
				'net_amount' => $gross_salary + $annual_amount,
				'note' => $note,
				'status' => $status,
				'payment_status' => $payment_status,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addSalary13($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_13_added"));          
			admin_redirect('payrolls/salaries_13');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/salaries_13'), 'page' => lang('salaries_13')), array('link' => '#', 'page' => lang('add_salary_13')));
            $meta = array('page_title' => lang('add_salary_13'), 'bc' => $bc);
            $this->page_construct('payrolls/add_salary_13', $meta, $this->data);
        }
	}
	
	
	public function edit_salary_13($id = false){
		$this->bpas->checkPermissions('edit_salary');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('year', $this->lang->line("year"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$year = $this->input->post('year') ? $this->input->post('year') : null;
			$items = false;
			$gross_salary = 0;
			$annual_amount = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$net_salary = $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$annual_leave = $_POST['annual_leave'][$r];
				$al_rate = $this->bpas->formatDecimal($_POST['al_rate'][$r]);
				$al_amount = $annual_leave * $al_rate;
				$subtotal = $net_salary + $al_amount;
				$items[] = array(
								"salary_id" => $id,
								"employee_id" => $_POST['employee_id'][$r],
								"net_salary" => $net_salary,
								"annual_leave" => $annual_leave,
								"al_rate" => $al_rate,
								"al_amount" => $al_amount,
								"subtotal" => $subtotal,
								"paid" => 0,
								"payment_status" => "pending"
							);
				$gross_salary += $net_salary; 	
				$annual_amount += $al_amount;
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			
			$data = array(
				'date' => $date,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'gross_salary' => $gross_salary,
				'annual_amount' => $annual_amount,
				'net_amount' => $gross_salary + $annual_amount,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateSalary13($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_13_updated"));          
			admin_redirect('payrolls/salaries_13');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$salary = $this->payrolls_model->getSalary13ByID($id);
			$this->data['salary'] = $salary;
			$this->data['salary_items'] = $this->payrolls_model->getSalary13Items($id);
			$this->data['positions'] = $this->payrolls_model->getPositions($salary->biller_id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($salary->biller_id);
			if($salary->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($salary->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/salaries_13'), 'page' => lang('salaries_13')), array('link' => '#', 'page' => lang('edit_salary_13')));
            $meta = array('page_title' => lang('edit_salary_13'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_salary_13', $meta, $this->data);
        }
	}
	
	public function delete_salary_13($id = null){
        $this->bpas->checkPermissions('delete_salary', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteSalary13($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('salary_13_deleted')]);
            }
            $this->session->set_flashdata('message', lang('salary_13_deleted'));
            admin_redirect('welcome');
        }
    }
	
	public function approve_salary_13($id = null){
        $this->bpas->checkPermissions('approve_salary', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateSalary13Status($id,"approved")) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('salary_approved')]);
            }
            $this->session->set_flashdata('message', lang('salary_approved'));
            admin_redirect('welcome');
        }
    }
	public function unapprove_salary_13($id = null){
        $this->bpas->checkPermissions("approve_salary", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateSalary13Status($id,"pending")) {
            if ($this->input->is_ajax_request()) {
                 $this->bpas->send_json(['error' => 0, 'msg' => lang('salary_13_unapproved')]);
            }
            $this->session->set_flashdata('message', lang('salary_13_unapproved'));
            admin_redirect('welcome');
        }
    }
	
	public function modal_view_salary_13($id = false){
		$this->bpas->checkPermissions('salaries', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary = $this->payrolls_model->getSalary13ByID($id);
		$this->data['salary'] = $salary;
        $this->data['salary_items'] = $this->payrolls_model->getSalary13Items($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_salary_13', $this->data);
	}
	public function modal_view_salary_employee_13($id = false){
		$this->bpas->checkPermissions('salaries', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary_item = $this->payrolls_model->getSalary13ItemsByID($id);
		$this->data['salary_item'] = $salary_item;
		$salary = $this->payrolls_model->getSalary13ByID($salary_item->salary_id);
		$this->data['salary'] = $salary;
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_salary_employee_13', $this->data);
	}
	
	function salary_13_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_salary');
                    $deleted = 0;
					foreach ($_POST['val'] as $id) {
						$salary = $this->payrolls_model->getSalary13ByID($id);
						if($salary->status == "pending" && $salary->payment_status == "pending"){
							$deleted = 1;
							$this->payrolls_model->deleteSalary13($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("salary_13_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("salary_13_cannot_delete"));
					}
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('salary_13');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('year'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('salary_13'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('annual_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('net_amount'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('note'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('payment_status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $salary = $this->payrolls_model->getSalary13ByID($id); 
						$user = $this->site->getUserByID($salary->created_by);
						$balance = $salary->net_amount - $salary->paid;
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($salary->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $salary->year);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($salary->gross_salary));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($salary->annual_amount));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($salary->net_amount));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($salary->paid));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($balance));
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->remove_tag($salary->note));
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($salary->status));
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($salary->payment_status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salary_13_list_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
					create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
	
	public function get_salary_13_employees(){
		$working_day = 26;
		$minimum_day = 183;
		$biller_id = $this->input->get('biller_id');
		$position_id = $this->input->get('position_id');
		$department_id = $this->input->get('department_id');
		$group_id = $this->input->get('group_id');
		$year = $this->input->get('year');
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getSalary13Employee($biller_id,$position_id,$department_id,$group_id,$year,$edit_id);
		$last_day = $year.'-12-31';
		$employee_13 = false;
		if($employees){
			foreach($employees as $employee){
				$employee->day_rate = $employee->net_salary / $working_day;
				$date1=date_create($employee->employee_date);
				$date2=date_create($last_day);
				$diff=date_diff($date1,$date2)->format("%a");
				if($diff > $minimum_day){
					$employee_13[] = $employee;
				}
			}
		}
		echo json_encode($employee_13);
	}
	
	public function view_payments_13($id = null)
    {
        $this->bpas->checkPermissions("payments", true);
        $this->data['payments'] = $this->payrolls_model->getPayments13BySalaryID($id);
        $this->load->view($this->theme . 'payrolls/view_payments_13', $this->data);
    }
	
	
	public function add_payment_13($salary_id = false){
		$this->bpas->checkPermissions("add_payment");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('salary_id', $this->lang->line("employee"), 'required');
		$this->form_validation->set_rules('biller_id', $this->lang->line("biller"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$salary_id = $this->input->post("salary_id");
			$biller_id = $this->input->post("biller_id");
			$year = $this->input->post("year");
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$paying_from = $this->input->post('paying_from') ? $this->input->post('paying_from') : null;
			$amount = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['pay'][$r] > 0){
					$items[] = array(
									"employee_id" => $_POST['employee_id'][$r],
									"amount" => $_POST['pay'][$r]
								);
					$amount += $_POST['pay'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'salary_id' => $salary_id,
                'biller_id' => $biller_id,
				'note' => $note,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'account_code' => $paying_from,
				'amount' => $amount,
				'year' => $year
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addPayment13($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("payment_added"));          
			admin_redirect('payrolls/salaries_13');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$salary = $this->payrolls_model->getSalary13ByID($salary_id);
			$this->data['salary'] = $salary;
			$this->data['salary_items'] = $this->payrolls_model->getSalary13Items($salary_id);
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('','','1');
			}
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/salaries_13'), 'page' => lang('salaries_13')), array('link' => '#', 'page' => lang('add_payment')));
            $meta = array('page_title' => lang('add_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/add_payment_13', $meta, $this->data);
        }
	}
	
	public function edit_payment_13($id = false){
		$this->bpas->checkPermissions("edit_payment");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('salary_id', $this->lang->line("employee"), 'required');
		$this->form_validation->set_rules('biller_id', $this->lang->line("biller"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$salary_id = $this->input->post("salary_id");
			$biller_id = $this->input->post("biller_id");
			$year = $this->input->post("year");
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$paying_from = $this->input->post('paying_from') ? $this->input->post('paying_from') : null;
			$amount = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['pay'][$r] > 0){
					$items[] = array(
									"payment_id" => $id,
									"employee_id" => $_POST['employee_id'][$r],
									"amount" => $_POST['pay'][$r]
								);
					$amount += $_POST['pay'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'salary_id' => $salary_id,
                'biller_id' => $biller_id,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s'),
				'account_code' => $paying_from,
				'amount' => $amount,
				'year' => $year
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updatePayment13($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("payment_updated"));          
			admin_redirect('payrolls/salaries_13');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['payment'] = $this->payrolls_model->getPayment13ByID($id);
			$this->data['payment_items'] = $this->payrolls_model->getPayments13ItemByPaymentID($id);
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('',$this->data['payment']->account_code,'1');
			}
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/salaries_13'), 'page' => lang('salaries_13')), array('link' => '#', 'page' => lang('edit_payment')));
            $meta = array('page_title' => lang('edit_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_payment_13', $meta, $this->data);
        }
	}
	
	public function delete_payment_13($id = null)
    {
        $this->bpas->checkPermissions('delete_payment');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deletePayment13($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	public function payment_13_note($id = false){
		$this->bpas->checkPermissions('payments', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$payment = $this->payrolls_model->getPayment13ByID($id);
		$this->data['payment'] = $this->payrolls_model->getPayment13ByID($id);
        $this->data['payment_items'] = $this->payrolls_model->getPayments13ItemByPaymentID($id);
        $this->data['biller'] = $this->site->getCompanyByID($payment->biller_id);
        $this->data['created_by'] = $this->site->getUser($payment->created_by);
        $this->load->view($this->theme . 'payrolls/payment_13_note', $this->data);
	}
	
	public function salaries_13_report(){
		$this->bpas->checkPermissions('salaries_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['users'] = $this->site->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salaries_13_report')));
        $meta = array('page_title' => lang('salaries_13_report'), 'bc' => $bc);
        $this->page_construct('payrolls/salaries_13_report', $meta, $this->data);
	}
	public function getSalaries13Report($pdf = NULL, $xls = NULL)
    {
        $this->bpas->checkPermissions('salaries_report');
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $year = $this->input->get('year') ? $this->input->get('year') : NULL;

        if ($pdf || $xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries_13').".date, '%Y-%m-%d %T') as date,
								companies.company,
								pay_salaries_13.year,
								CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
								IFNULL(".$this->db->dbprefix('pay_salaries_13').".gross_salary,0) as gross_salary,
								IFNULL(".$this->db->dbprefix('pay_salaries_13').".annual_amount,0) as annual_amount,
								IFNULL(".$this->db->dbprefix('pay_salaries_13').".net_amount,0) as net_amount,
								IFNULL(".$this->db->dbprefix('pay_salaries_13').".paid,0) as paid,
								(IFNULL(".$this->db->dbprefix('pay_salaries_13').".net_amount,0) - IFNULL(".$this->db->dbprefix('pay_salaries_13').".paid,0)) as balance,
								pay_salaries_13.note,
								pay_salaries_13.status,
								pay_salaries_13.payment_status,
								pay_salaries_13.attachment,
								pay_salaries_13.id as id")
						->from("pay_salaries_13")
						->join("companies","companies.id = pay_salaries_13.biller_id","left")
						->join("users","users.id = pay_salaries_13.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries_13.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries_13.biller_id', $biller);
            }
            if ($user) {
                $this->db->where('pay_salaries_13.created_by', $user);
            }
			if($year){
				$this->db->where('pay_salaries_13.year', $year);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries_13.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('salaries_13_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('year'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('salary_13'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('annual_amount'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('net_amount'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('payment_status'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->company);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->year);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->annual_amount));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->net_amount));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, lang($data_row->payment_status));
                    $row++;
                }
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);

				$filename = 'salaries_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('pay_salaries_13').".date, '%Y-%m-%d %T') as date,
									companies.company,
									pay_salaries_13.year,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".gross_salary,0) as gross_salary,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".annual_amount,0) as annual_amount,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".net_amount,0) as net_amount,
									IFNULL(".$this->db->dbprefix('pay_salaries_13').".paid,0) as paid,
									(IFNULL(".$this->db->dbprefix('pay_salaries_13').".net_amount,0) - IFNULL(".$this->db->dbprefix('pay_salaries_13').".paid,0)) as balance,
									pay_salaries_13.note,
									pay_salaries_13.status,
									pay_salaries_13.payment_status,
									pay_salaries_13.attachment,
									pay_salaries_13.id as id")
							->from("pay_salaries_13")
							->join("companies","companies.id = pay_salaries_13.biller_id","left")
							->join("users","users.id = pay_salaries_13.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries_13.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries_13.biller_id', $biller);
            }
            if ($user) {
                $this->datatables->where('pay_salaries_13.created_by', $user);
            }
			if($year){
				$this->datatables->where('pay_salaries_13.year', $year);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries_13.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	public function salary_13_details_report(){
		$this->bpas->checkPermissions('salary_details_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salary_13_details_report')));
        $meta = array('page_title' => lang('salary_13_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/salary_13_details_report', $meta, $this->data);
	}
	public function getSalary13DetailsReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('salary_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $year = $this->input->get('year') ? $this->input->get('year') : NULL;
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries_13').".date, '%Y-%m-%d %T') as date,
								pay_salaries_13.year,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								hr_employees_bank.bank_account,
								hr_employees_bank.account_no,
								hr_employees_bank.account_name,
								IFNULL(".$this->db->dbprefix('pay_salary_items_13').".net_salary,0) as net_salary,
								IFNULL(".$this->db->dbprefix('pay_salary_items_13').".annual_leave,0) as annual_leave,
								IFNULL(".$this->db->dbprefix('pay_salary_items_13').".subtotal,0) as subtotal,
								IFNULL(".$this->db->dbprefix('pay_salary_items_13').".paid,0) as paid,
								(IFNULL(".$this->db->dbprefix('pay_salary_items_13').".subtotal,0) - IFNULL(".$this->db->dbprefix('pay_salary_items_13').".paid,0)) as balance,
								pay_salaries_13.status,
								pay_salary_items_13.payment_status,
								pay_salaries_13.id as id")
						->from("pay_salaries_13")
						->join("pay_salary_items_13","pay_salary_items_13.salary_id = pay_salaries_13.id","inner")
						->join("hr_employees","hr_employees.id = pay_salary_items_13.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items_13.employee_id","left")
						->join("hr_employees_bank","hr_employees_bank.employee_id = pay_salary_items_13.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_salary_items_13.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries_13.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries_13.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_salary_items_13.employee_id', $employee);
            }
			if($year){
				$this->db->where('pay_salaries_13.year', $year);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries_13.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('salary_13_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('year'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('bank_name'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('account_no'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('account_name'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('salary_13'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('annual_amount'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('net_amount'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('payment_status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->year);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->bank_account);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->account_no);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->account_name);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->net_salary));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->annual_leave));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->subtotal));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->paid));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->payment_status));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('Q')->setWidth(15);

				$filename = 'salary_13_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_salaries_13').".date, '%Y-%m-%d %T') as date,
									pay_salaries_13.year,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									hr_employees_bank.bank_account,
									hr_employees_bank.account_no,
									hr_employees_bank.account_name,
									IFNULL(".$this->db->dbprefix('pay_salary_items_13').".net_salary,0) as net_salary,
									IFNULL(".$this->db->dbprefix('pay_salary_items_13').".annual_leave,0) as annual_leave,
									IFNULL(".$this->db->dbprefix('pay_salary_items_13').".subtotal,0) as subtotal,
									IFNULL(".$this->db->dbprefix('pay_salary_items_13').".paid,0) as paid,
									(IFNULL(".$this->db->dbprefix('pay_salary_items_13').".subtotal,0) - IFNULL(".$this->db->dbprefix('pay_salary_items_13').".paid,0)) as balance,
									pay_salaries_13.status,
									pay_salary_items_13.payment_status,
									pay_salary_items_13.id as id")
							->from("pay_salaries_13")
							->join("pay_salary_items_13","pay_salary_items_13.salary_id = pay_salaries_13.id","inner")
							->join("hr_employees","hr_employees.id = pay_salary_items_13.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items_13.employee_id","left")
							->join("hr_employees_bank","hr_employees_bank.employee_id = pay_salary_items_13.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_salary_items_13.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries_13.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries_13.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_salary_items_13.employee_id', $employee);
            }
			if($year){
				$this->datatables->where('pay_salaries_13.year', $year);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries_13.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function salaries_teacher($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('salaries')));
        $meta = array('page_title' => lang('salaries'), 'bc' => $bc);
        $this->page_construct('payrolls/salary_teacher', $meta, $this->data);
	}
	public function getTeacherSalaries($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$view_salary_link = anchor('admin/payrolls/modal_view_salary_teacher/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_salary'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_salary" data-target="#myModal"');
		$payment_link = anchor('admin/payrolls/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add_payment"');
        $edit_link = anchor('admin/payrolls/edit_salary_teacher/$1', '<i class="fa fa-edit"></i> ' . lang('edit_salary'), ' class="edit_salary"');
        $delete_link = "<a href='#' class='delete_salary po' title='<b>" . $this->lang->line("delete_salary") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_salary/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_salary') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_salary']){
			$approve_link = "<a href='#' class='po approve_salary' title='" . $this->lang->line("approve_salary") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/approve_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_salary') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_salary' title='<b>" . $this->lang->line("unapprove_salary") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/unapprove_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_salary') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_salary_link . '</li>
						<li>' . $payment_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_salaries.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_gross_salary,0) as total_gross_salary,
					
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_payment,0) as total_tax_payment,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_salary,0) as total_net_salary,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) as total_net_pay,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_tax_paid,0) as total_tax_paid,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_salary_paid,0) as total_salary_paid,
									IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0) as total_paid,
									(IFNULL(".$this->db->dbprefix('pay_salaries').".total_net_pay,0) - IFNULL(".$this->db->dbprefix('pay_salaries').".total_paid,0)) as total_balance,
									pay_salaries.status,
									pay_salaries.payment_status,
									pay_salaries.attachment")
							->from("pay_salaries")
							->join("users","users.id = pay_salaries.created_by","left");
		if ($biller_id) {
            $this->datatables->where("pay_salaries.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_salaries.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_salaries.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_salary_teacher(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$status = "pending";
			$payment_status = "pending";
			$items = false;
			$total_gross_salary = 0;
			$total_overtime = 0;
			$total_addition = 0;
			$total_cash_advanced = 0;
			$total_tax_payment = 0;
			$total_net_salary = 0;
			$total_net_pay = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$items[] = array(
								"employee_id" => $_POST['employee_id'][$r],
								"working_day" => $_POST['working_day'][$r],
								"absent" => (isset($_POST['absent'][$r]) ? $_POST['absent'][$r] : null),
								"permission" => (isset($_POST['permission'][$r]) ? $_POST['permission'][$r] : null),
								"late" => (isset($_POST['late'][$r]) ? $_POST['late'][$r] : null),
								"normal_ot" => (isset($_POST['normal_ot'][$r]) ? $_POST['normal_ot'][$r] : null),
								"weekend_ot" => (isset($_POST['weekend_ot'][$r]) ? $_POST['weekend_ot'][$r] : null),
								"holiday_ot" => (isset($_POST['holiday_ot'][$r]) ? $_POST['holiday_ot'][$r] : null),
								"basic_salary" => $_POST['basic_salary'][$r],
								"absent_amount" => $_POST['absent_amount'][$r],
								"permission_amount" => $_POST['permission_amount'][$r],
								"late_amount" => $_POST['late_amount'][$r],
								"deduction" => $_POST['deduction'][$r],
								"gross_salary" => $_POST['gross_salary'][$r],
								"overtime" => $_POST['overtime'][$r],
								"addition" => $_POST['addition'][$r],
								"cash_advanced" => $_POST['cash_advanced'][$r],
								"tax_declaration" => $_POST['tax_declaration'][$r],
								"tax_payment" => $this->bpas->formatDecimal($_POST['tax_payment'][$r]),
								"net_salary" => $this->bpas->formatDecimal($_POST['net_salary'][$r]),
								"net_pay" => $this->bpas->formatDecimal($_POST['net_pay'][$r]),
								"salary_paid" => 0,
								"tax_paid" => 0,
								"payment_status" => "pending"
							);
				$total_gross_salary += $_POST['gross_salary'][$r]; 	
				$total_overtime += $_POST['overtime'][$r];
				$total_addition += $_POST['addition'][$r];
				$total_cash_advanced += $_POST['cash_advanced'][$r];
				$total_tax_payment += $this->bpas->formatDecimal($_POST['tax_payment'][$r]);
				$total_net_salary += $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$total_net_pay += $this->bpas->formatDecimal($_POST['net_pay'][$r]);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'total_gross_salary' => $total_gross_salary,
				'total_overtime' => $total_overtime,
				'total_addition' => $total_addition,
				'total_cash_advanced' => $total_cash_advanced,
				'total_tax_payment' => $total_tax_payment,
				'total_net_salary' => $total_net_salary,
				'total_net_pay' => $total_net_pay,
				'note' => $note,
				'status' => $status,
				'payment_status' => $payment_status,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addSalary($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_added"));          
			admin_redirect('payrolls/');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/salaries_teacher'), 'page' => lang('salaries')), array('link' => '#', 'page' => lang('add_salary_teacher')));
            $meta = array('page_title' => lang('add_salary_teacher'), 'bc' => $bc);
            $this->page_construct('payrolls/add_salary_teacher.php', $meta, $this->data);
        }
	}

	public function get_salary_teacher()
	{
		$biller_id = $this->input->get('biller_id');
		$position_id = $this->input->get('position_id');
		$department_id = $this->input->get('department_id');
		$group_id = $this->input->get('group_id');
		$y_month = explode("/",$this->input->get('month'));
		$month = $y_month[0];
		$year = $y_month[1];
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getSalaryTeacher($biller_id, $position_id, $department_id, $group_id, $month, $year, $edit_id);
		if($employees){
			$hour = 8;
			$emp_benefits = false;
			foreach($employees as $employee){
				$absent_amount = 0;
				$permission_amount = 0;
				$late_amount = 0;
				$cash_advanced = 0;
				$deduction = 0;
				$addition = 0;
				$normal_ot_amount = 0;
				$weekend_ot_amount = 0;
				$holiday_ot_amount = 0;
				$overtime = 0;
				$employee->basic_salary = $employee->working_hour * $employee->basic_salary;
				$gross_salary = $this->bpas->formatDecimal($employee->basic_salary - ($absent_amount + $permission_amount + $late_amount + $deduction));
				$employee->salary_tax = ($employee->salary_tax > 0 ? $employee->salary_tax : $employee->basic_salary);
				$tax_declaration = $this->bpas->formatDecimal($employee->salary_tax - ($absent_amount + $permission_amount + $late_amount));
				$tax_calculation = $this->site->getTeacherSalaryTax($employee->employee_id,$tax_declaration);
				$tax_payment = $this->bpas->formatDecimal($tax_calculation['tax_on_salary']);
				$self_tax = ($employee->self_tax == 1 ? $tax_payment : 0);
				$net_salary = $this->bpas->formatDecimal(($gross_salary + $addition + $overtime) - ($cash_advanced + $self_tax));
				$net_pay = $this->bpas->formatDecimal($net_salary + $tax_payment);

				$employee->working_hour;
				$employee->absent_amount = $absent_amount;
				$employee->permission_amount = $permission_amount;
				$employee->late_amount = $late_amount;
				$employee->cash_advanced = $cash_advanced;
				$employee->deduction = $deduction;
				$employee->addition = $addition;
				$employee->overtime = $overtime;
				$employee->gross_salary = $gross_salary;
				$employee->tax_declaration = $tax_declaration;
				$employee->tax_payment = $tax_payment;
				$employee->net_salary = $net_salary;
				$employee->net_pay = $net_pay;
			}
		}
		echo json_encode($employees);
	}

	public function edit_salary_teacher($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$items = false;
			$total_gross_salary = 0;
			$total_overtime = 0;
			$total_addition = 0;
			$total_cash_advanced = 0;
			$total_tax_payment = 0;
			$total_net_salary = 0;
			$total_net_pay = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$items[] = array(
								"salary_id" => $id,
								"employee_id" => $_POST['employee_id'][$r],
								"working_day" => $_POST['working_day'][$r],
								"absent" => $_POST['absent'][$r],
								"permission" => $_POST['permission'][$r],
								"late" => $_POST['late'][$r],
								"normal_ot" => $_POST['normal_ot'][$r],
								"weekend_ot" => $_POST['weekend_ot'][$r],
								"holiday_ot" => $_POST['holiday_ot'][$r],
								"basic_salary" => $_POST['basic_salary'][$r],
								"absent_amount" => $_POST['absent_amount'][$r],
								"permission_amount" => $_POST['permission_amount'][$r],
								"late_amount" => $_POST['late_amount'][$r],
								"deduction" => $_POST['deduction'][$r],
								"gross_salary" => $_POST['gross_salary'][$r],
								"overtime" => $_POST['overtime'][$r],
								"addition" => $_POST['addition'][$r],
								"cash_advanced" => $_POST['cash_advanced'][$r],
								"tax_declaration" => $_POST['tax_declaration'][$r],
								"tax_payment" => $this->bpas->formatDecimal($_POST['tax_payment'][$r]),
								"net_salary" => $this->bpas->formatDecimal($_POST['net_salary'][$r]),
								"net_pay" => $this->bpas->formatDecimal($_POST['net_pay'][$r]),
								"salary_paid" => 0,
								"tax_paid" => 0,
								"payment_status" => "pending"
							);
				$total_gross_salary += $_POST['gross_salary'][$r]; 	
				$total_overtime += $_POST['overtime'][$r];
				$total_addition += $_POST['addition'][$r];
				$total_cash_advanced += $_POST['cash_advanced'][$r];
				$total_tax_payment += $this->bpas->formatDecimal($_POST['tax_payment'][$r]);
				$total_net_salary += $this->bpas->formatDecimal($_POST['net_salary'][$r]);
				$total_net_pay += $this->bpas->formatDecimal($_POST['net_pay'][$r]);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'total_gross_salary' => $total_gross_salary,
				'total_overtime' => $total_overtime,
				'total_addition' => $total_addition,
				'total_cash_advanced' => $total_cash_advanced,
				'total_tax_payment' => $total_tax_payment,
				'total_net_salary' => $total_net_salary,
				'total_net_pay' => $total_net_pay,
				'note' => $note,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateSalary($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_updated"));          
			admin_redirect('payrolls/salaries_teacher');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$salary = $this->payrolls_model->getSalaryByID($id);
			$this->data['salary'] = $salary;
			$this->data['salary_items'] = $this->payrolls_model->getSalaryTeacherItems($id);
			$this->data['positions'] = $this->payrolls_model->getPositions($salary->biller_id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($salary->biller_id);
			if($salary->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($salary->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/salaries_teacher'), 'page' => lang('salaries')), array('link' => '#', 'page' => lang('edit_salary')));
            $meta = array('page_title' => lang('edit_salary'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_salary_teacher', $meta, $this->data);
        }
	}
	public function modal_view_salary_teacher($id = false){
		$this->bpas->checkPermissions('salaries', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary = $this->payrolls_model->getSalaryByID($id);
		$this->data['salary'] = $salary;
        $this->data['salary_items'] = $this->payrolls_model->getSalaryTeacherItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_salary_teacher', $this->data);
	}
	public function add_contribution_payment($salary_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$nssf_rate = $this->input->post('nssf_rate') ? $this->input->post('nssf_rate') : null;

			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$total_OR_scheme = 0;
			$total_HC_scheme = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$items[] = array(
								"employee_id" 		=> $_POST['employee_id'][$r],
								"contributory_wage" => $_POST['contributory_wage'][$r],
								"OR_scheme" 		=> $_POST['contributory_or'][$r],
								"HC_scheme" 		=> $_POST['contributory_hc'][$r]
							);
				$total_OR_scheme += $_POST['contributory_or'][$r];		
				$total_HC_scheme += $_POST['contributory_hc'][$r];
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'total_OR_scheme' => $total_OR_scheme,
				'total_HC_scheme' => $total_HC_scheme,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'nssf_rate' => $nssf_rate
            );
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
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addNssfPayment($data,$items)) {	
			 if ($salary_id) {
                $this->db->update('pay_salaries', array('nssf_status' => 1), array('id' => $salary_id));
            }
            $this->session->set_flashdata('message', $this->lang->line("contribution_payment_added"));          
			admin_redirect('payrolls');
        } else {
			if($salary_id){
				$salary = $this->payrolls_model->getSalaryByID($salary_id);
				$this->data['salary'] = $salary;
				$this->data['salary_items'] = $this->payrolls_model->getSalaryItems($salary_id);
				$this->data['positions'] = $this->payrolls_model->getPositions($salary->biller_id);
				$this->data['departments'] = $this->payrolls_model->getDepartments($salary->biller_id);
				if($salary->department_id){
					$this->data['groups'] = $this->payrolls_model->getGroups($salary->department_id);
				}
			}
			if($this->Settings->accounting == 1){
				$this->data['cash_account'] = $this->site->getAccount('','','1');
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => admin_url('payrolls/payments'), 'page' => lang('payments')), array('link' => '#', 'page' => lang('add_contribution_payment')));
            $meta = array('page_title' => lang('add_contribution_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/add_contribution_payment', $meta, $this->data);
        }
	}

    public function pre_salaries($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_salaries')));
        $meta = array('page_title' => lang('pre_salaries'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_salaries', $meta, $this->data);
	}

	public function getPreSalaries($biller_id = false){
		$this->bpas->checkPermissions("salaries");
		$view_payment_link = anchor('admin/payrolls/view_pre_salary_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_payments" data-target="#myModal"');
		$payment_link = anchor('admin/payrolls/add_pre_salary_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), ' class="add_payment"');
        $edit_link = anchor('admin/payrolls/edit_pre_salary/$1', '<i class="fa fa-edit"></i> ' . lang('edit_pre_salary'), ' class="edit_salary"');
        $delete_link = "<a href='#' class='delete_pre_salary po delete_salary' title='<b>" . $this->lang->line("delete_pre_salary") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_pre_salary/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_pre_salary') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_salary']){
			$approve_link = "<a href='#' class='po approve_salary' title='" . $this->lang->line("approve_pre_salary") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/approve_pre_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_pre_salary') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_salary' title='<b>" . $this->lang->line("unapprove_pre_salary") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/unapprove_pre_salary/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_pre_salary') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_payment_link . '</li>
						<li>' . $payment_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_pre_salaries.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,0) as total_gross_salary,
									IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_paid,0) as total_paid,
									(IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,0) - IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_paid,0)) as balance,
									pay_pre_salaries.status,
									pay_pre_salaries.payment_status,
									pay_pre_salaries.attachment")
							->from("pay_pre_salaries")
							->join("hr_departments","hr_departments.id = pay_pre_salaries.department_id","left")
							->join("hr_groups","hr_groups.id = pay_pre_salaries.group_id","left")
							->join("users","users.id = pay_pre_salaries.created_by","left");
		if ($biller_id) {
            $this->datatables->where("pay_pre_salaries.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_pre_salaries.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_pre_salaries.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
    public function add_pre_salary(){
		$this->bpas->checkPermissions("add_salary");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$items = false;
			$total_gross_salary = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				//$bank_notes = $this->bpas->seperateBankNote($_POST['gross_salary'][$r],$kh_rate);
				$items[] = array(
								"att_id" => $_POST['att_id'][$r],
								"employee_id" => $_POST['employee_id'][$r],
								"basic_salary" => $_POST['basic_salary'][$r],
								"present" => $_POST['present'][$r],
								"holiday" => $_POST['holiday'][$r],
								"annual_leave" => $_POST['annual_leave'][$r],
								"sick_leave" => $_POST['sick_leave'][$r],
								"special_leave" => $_POST['special_leave'][$r],
								"gross_salary" => $_POST['gross_salary'][$r],
								"net_paid" => 0,
								"payment_status" => "pending",
							);
				$total_gross_salary += $_POST['gross_salary'][$r]; 	
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' 			=> $date,
                'month' 		=> $month,
                'year' 			=> $year,
                'biller_id' 	=> $biller_id,
                'position_id' 	=> $position_id,
				'department_id' => $department_id,
				'group_id' 		=> $group_id,
				'total_gross_salary' => $total_gross_salary,
				'note' 			=> $note,
				'status' 		=> "pending",
				'type' 			=> $this->input->post("type"),
				'payment_status'=> "pending",
				'created_by' 	=> $this->session->userdata('user_id'),
				'created_at' 	=> date('Y-m-d H:i:s')
            );
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addPreSalary($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("pre_salary_added"));          
			if($this->input->post('add_pre_salary_next')){
				admin_redirect('payrolls/add_pre_salary');
			}else{
				admin_redirect('payrolls/pre_salaries');
			}
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/pre_salaries'), 'page' => lang('pre_salaries')), array('link' => '#', 'page' => lang('add_pre_salary')));
            $meta = array('page_title' => lang('add_pre_salary'), 'bc' => $bc);
            $this->page_construct('payrolls/add_pre_salary', $meta, $this->data);
        }
	}
	public function edit_pre_salary($id = false){
		$this->bpas->checkPermissions("edit_salary");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-salaries_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$items = false;
			$total_gross_salary = 0;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				//$bank_notes = $this->bpas->seperateBankNote($_POST['gross_salary'][$r],$kh_rate);
				$items[] = array(
								"pre_salary_id" => $id,
								"att_id" => $_POST['att_id'][$r],
								"employee_id" => $_POST['employee_id'][$r],
								"basic_salary" => $_POST['basic_salary'][$r],
								"present" => $_POST['present'][$r],
								"holiday" => $_POST['holiday'][$r],
								"annual_leave" => $_POST['annual_leave'][$r],
								"sick_leave" => $_POST['sick_leave'][$r],
								"special_leave" => $_POST['special_leave'][$r],
								"gross_salary" => $_POST['gross_salary'][$r],
								"net_paid" => 0,
								"payment_status" => "pending",
							);
				$total_gross_salary += $_POST['gross_salary'][$r]; 	
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' 			=> $date,
                'month' 		=> $month,
                'year' 			=> $year,
                'biller_id' 	=> $biller_id,
                'position_id'	=> $position_id,
				'department_id' => $department_id,
				'group_id' 		=> $group_id,
				'total_gross_salary' => $total_gross_salary,
				'note' 			=> $note,
				'status' 		=> "pending",
				'type' 			=> $this->input->post("type"),
				'payment_status'=> "pending",
				'updated_by' 	=> $this->session->userdata('user_id'),
				'updated_at' 	=> date('Y-m-d H:i:s'),
            );
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updatePreSalary($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("pre_salary_updated"));          
			admin_redirect('payrolls/pre_salaries');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$pre_salary = $this->payrolls_model->getPreSalaryByID($id);
			$this->data['billers'] = $this->site->getBillers();
			$this->data['pre_salary'] = $pre_salary;
			$this->data['pre_salary_items'] = $this->payrolls_model->getPreSalaryItems($id);
			$this->data['departments'] = $this->payrolls_model->getDepartments($pre_salary->biller_id);
			if($pre_salary->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($pre_salary->department_id);
				$this->data['positions'] = $this->payrolls_model->getPositions($pre_salary->department_id);
			}
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/pre_salaries'), 'page' => lang('pre_salaries')), array('link' => '#', 'page' => lang('edit_pre_salary')));
            $meta = array('page_title' => lang('edit_pre_salary'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_pre_salary', $meta, $this->data);
        }
	}
	
	public function delete_pre_salary($id = null){
        $this->bpas->checkPermissions('delete_salary', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deletePreSalary($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('pre_salary_deleted')]);
            }
            $this->session->set_flashdata('message', lang('pre_salary_deleted'));
            admin_redirect('welcome');
        }
    }
    public function approve_pre_salary($id = null){
        $this->bpas->checkPermissions("approve_salary", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updatePreSalaryStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('pre_salary_approved')]);
            }
            $this->session->set_flashdata('message', lang('salary_approved'));
            admin_redirect('welcome');
        }
    }
	public function unapprove_pre_salary($id = null){
        $this->bpas->checkPermissions("approve_salary", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updatePreSalaryStatus($id,"pending")) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('pre_salary_unapproved')]);
            }
            $this->session->set_flashdata('message', lang('salary_unapproved'));
            admin_redirect('welcome');
        }
    }
    public function get_groups_positions(){
		$department_id = $this->input->get('department_id');
		$groups = $this->payrolls_model->getGroups($department_id);
		$positions = $this->payrolls_model->getPositions($department_id);
		echo json_encode(array("groups"=>$groups,"positions"=>$positions));
	}
    public function get_pre_salary_employees(){
		$biller_id 		= $this->input->get('biller_id') ? $this->input->get('biller_id') : false;
		$position_id 	= $this->input->get('position_id') ? $this->input->get('position_id') : false;
		$department_id 	= $this->input->get('department_id')? $this->input->get('department_id') : false;
		$group_id 		= $this->input->get('group_id') ? $this->input->get('group_id') : false;
		$type 			= $this->input->get('type') ? $this->input->get('type') : false;
		$y_month 		= explode("/",$this->input->get('month'));
		$month 			= $y_month[0];
		$year 			= $y_month[1];
		$edit_id 		= $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees 		= $this->payrolls_model->getPreSalaryEmployee($biller_id,$position_id,$department_id,$group_id,$month,$year,$edit_id);
		if($employees){
			foreach($employees as $employee){
				$contract_salary = $this->payrolls_model->getContractSalary($employee->employee_id,$employee->start_date,$employee->end_date);
				if($contract_salary > 0){
					$employee->basic_salary = $contract_salary;
				}
				
				$weekend = 0;
				if($employee->weekend > 2){
					$weekend = $employee->weekend - 2;
				}
				if($employee->holiday){
					$employee->present += $employee->holiday;
				}
				
				$salary_per_day = $employee->basic_salary / $employee->working_day;
				$gross_salary = (int) ($salary_per_day * ($employee->present + $weekend  + $employee->annual_leave + $employee->sick_leave + $employee->special_leave));
				
				if($type =='half'){
					$gross_salary = $this->bpas->formatDecimal($employee->basic_salary/2);
				}else{
					$gross_salary = $this->bpas->formatDecimal($employee->basic_salary);
				}
				//$this->bpas->formatDecimal($gross_salary);
				$employee->gross_salary = $gross_salary;
			}
		}
		echo json_encode($employees);
	}
	public function view_pre_salary_payments($id = null)
    {
        $this->bpas->checkPermissions("payments", true);
        $this->data['payments'] = $this->payrolls_model->getPrePaymentByPreSalaryID($id);
        $this->load->view($this->theme . 'payrolls/view_pre_salary_payments', $this->data);
    }
	
	
	public function add_pre_salary_payment($pre_salary_id = false){
		$this->bpas->checkPermissions("add_payment");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('pre_salary_id', $this->lang->line("employee"), 'required');
		$this->form_validation->set_rules('biller_id', $this->lang->line("biller"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$pre_salary_id 	= $this->input->post("pre_salary_id");
			$biller_id 		= $this->input->post("biller_id");
			$year 			= $this->input->post("year");
			$month 			= $this->input->post("month");
			$note 			= $this->input->post('note') ? $this->input->post('note') : null;
			$paid_by 		= $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
			$amount = 0;
			$i = isset($_POST['pre_salary_item_id']) ? sizeof($_POST['pre_salary_item_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['pay'][$r] > 0){
					$items[] = array(
									"pre_salary_item_id" => $_POST['pre_salary_item_id'][$r],
									"employee_id" => $_POST['employee_id'][$r],
									"amount" => $_POST['pay'][$r]
								);
					$amount += $_POST['pay'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' 			=> $date,
                'pre_salary_id' => $pre_salary_id,
                'biller_id' 	=> $biller_id,
				'note' 			=> $note,
				'created_by' 	=> $this->session->userdata('user_id'),
				'created_at' 	=> date('Y-m-d H:i:s'),
				'paid_by' 		=> $paid_by,
				'amount' 		=> $amount,
				'year' 			=> $year,
				'month' 		=> $month
            );
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addPreSalaryPayment($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("payment_added"));          
			admin_redirect('payrolls/pre_salaries');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$pre_salary = $this->payrolls_model->getPreSalaryByID($pre_salary_id);
			$this->data['pre_salary'] = $pre_salary;
			$this->data['pre_salary_items'] = $this->payrolls_model->getPreSalaryItems($pre_salary_id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/pre_salaries'), 'page' => lang('pre_salaries')), array('link' => '#', 'page' => lang('add_payment')));
            $meta = array('page_title' => lang('add_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/add_pre_salary_payment', $meta, $this->data);
        }
	}
	public function edit_pre_salary_payment($id = false){
		$this->bpas->checkPermissions("edit_payment");
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('pre_salary_id', $this->lang->line("employee"), 'required');
		$this->form_validation->set_rules('biller_id', $this->lang->line("biller"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['payrolls-payments_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
			$pre_salary_id 	= $this->input->post("pre_salary_id");
			$biller_id 		= $this->input->post("biller_id");
			$year 			= $this->input->post("year");
			$month 			= $this->input->post("month");
			$note 			= $this->input->post('note') ? $this->input->post('note') : null;
			$paid_by 		= $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
			$amount = 0;
			$i = isset($_POST['pre_salary_item_id']) ? sizeof($_POST['pre_salary_item_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['pay'][$r] > 0){
					//$bank_notes = $this->bpas->seperateBankNote($_POST['pay'][$r],$kh_rate);
					$items[] = array(
									"payment_id" => $id,
									"pre_salary_item_id" => $_POST['pre_salary_item_id'][$r],
									"employee_id" => $_POST['employee_id'][$r],
									"amount" => $_POST['pay'][$r]
								);
					$amount += $_POST['pay'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' 			=> $date,
                'pre_salary_id' => $pre_salary_id,
                'biller_id' 	=> $biller_id,
				'note' 			=> $note,
				'updated_by' 	=> $this->session->userdata('user_id'),
				'updated_at' 	=> date('Y-m-d H:i:s'),
				'paid_by' 		=> $paid_by,
				'amount' 		=> $amount,
				'year' 			=> $year,
				'month' 		=> $month
            );
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updatePreSalaryPayment($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("payment_updated"));          
			admin_redirect('payrolls/pre_salaries');
        } else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['payment'] = $this->payrolls_model->getPreSalaryPaymentByID($id);
			$this->data['payment_items'] = $this->payrolls_model->getPreSalaryPaymentItems($id);
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/pre_salaries'), 'page' => lang('pre_salaries')), array('link' => '#', 'page' => lang('edit_payment')));
            $meta = array('page_title' => lang('edit_payment'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_pre_salary_payment', $meta, $this->data);
        }
	}
	
	public function delete_pre_salary_payment($id = null)
    {
        $this->bpas->checkPermissions('delete_payment');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deletePreSalaryPayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	public function modal_view_pre_salary($id = false){
		$this->bpas->checkPermissions('salaries', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary = $this->payrolls_model->getPreSalaryByID($id);
		$this->data['salary'] = $salary;
        $this->data['salary_items'] = $this->payrolls_model->getSummaryPreSalaryItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_pre_salary', $this->data);
	}
	public function modal_view_pre_salary_bank_note($id = false){
		$this->bpas->checkPermissions('salary_bank_notes_report', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $salary = $this->payrolls_model->getPreSalaryByID($id);
		$this->data['salary'] = $salary;
        $this->data['salary_items'] = $this->payrolls_model->getPreSalaryItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_pre_salary_bank_note', $this->data);
	}
	public function pre_salaries_report(){
		$this->bpas->checkPermissions('salaries_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['users'] = $this->site->getStaff();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_salaries_report')));
        $meta = array('page_title' => lang('pre_salaries_report'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_salaries_report', $meta, $this->data);
	}
	public function getPreSalaryReport($xls = NULL)
    {
        $this->bpas->checkPermissions('salaries_report');
        $user = $this->input->get('user') ? $this->input->get('user') : NULL;
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($xls) {
			$this->db->select("
								DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
								companies.company,
								CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
								CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
								IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,0) as total_gross_salary,
								IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_paid,0) as total_paid,
								(IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,0) - IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_paid,0)) as balance,
								pay_pre_salaries.note,
								pay_pre_salaries.status,
								pay_pre_salaries.payment_status,
								pay_pre_salaries.attachment,
								pay_pre_salaries.id as id")
						->from("pay_pre_salaries")
						->join("companies","companies.id = pay_pre_salaries.biller_id","left")
						->join("users","users.id = pay_pre_salaries.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_pre_salaries.biller_id', $biller);
            }
            if ($user) {
                $this->db->where('pay_pre_salaries.created_by', $user);
            }
			if($y_month){
				$this->db->where('pay_salaries.year', $year);
				$this->db->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('pre_salaries_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('year'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('gross_salary'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->company);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($data_row->total_gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->total_paid));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($data_row->payment_status));
                    $row++;
                }
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

				$filename = 'pre_salaries_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
									companies.company,
									CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,0) as total_gross_salary,
									IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_paid,0) as total_paid,
									(IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_gross_salary,0) - IFNULL(".$this->db->dbprefix('pay_pre_salaries').".total_paid,0)) as balance,
									pay_pre_salaries.note,
									pay_pre_salaries.status,
									pay_pre_salaries.payment_status,
									pay_pre_salaries.attachment,
									pay_pre_salaries.id as id")
							->from("pay_pre_salaries")
							->join("companies","companies.id = pay_pre_salaries.biller_id","left")
							->join("users","users.id = pay_pre_salaries.created_by","left");				
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_pre_salaries.biller_id', $biller);
            }
            if ($user) {
                $this->datatables->where('pay_pre_salaries.created_by', $user);
            }
			if($y_month){
				$this->datatables->where('pay_salaries.year', $year);
				$this->datatables->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	public function pre_salary_details_report(){
		$this->bpas->checkPermissions('salary_details_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_salary_details_report')));
        $meta = array('page_title' => lang('pre_salary_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_salary_details_report', $meta, $this->data);
	}
	
	public function getPreSalaryDetailsReport($xls = NULL){
        $this->bpas->checkPermissions('salary_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) as gross_salary,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".net_paid,0) as net_paid,
								(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) - IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".net_paid,0)) as balance,
								pay_pre_salaries.status,
								pay_pre_salary_items.payment_status,
								pay_pre_salaries.id as id")
						->from("pay_pre_salaries")
						->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_pre_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_pre_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_pre_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_pre_salaries.year', $year);
				$this->db->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('pre_salary_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('gross_salary'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('paid'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('balance'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('status'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('payment_status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->net_paid));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->balance));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, lang($data_row->status));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, lang($data_row->payment_status));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);

				$filename = 'pre_salary_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) as gross_salary,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".net_paid,0) as net_paid,
									(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) - IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".net_paid,0)) as balance,
									pay_pre_salaries.status,
									pay_pre_salary_items.payment_status,
									pay_pre_salaries.id as id")
							->from("pay_pre_salaries")
							->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_pre_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_pre_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_pre_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_pre_salaries.year', $year);
				$this->datatables->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	public function pre_payslips_report(){
		$this->bpas->checkPermissions('salary_bank_notes_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_payslips_report')));
        $meta = array('page_title' => lang('pre_payslips_report'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_payslips_report', $meta, $this->data);
	}
	
	public function getPrePayslipsReport($xls = false){
        $this->bpas->checkPermissions('salary_bank_notes_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		if($this->input->get('month')){
			$y_month = explode("/",$this->input->get('month'));
			$month = $y_month[0];
			$year = $y_month[1];
		}else{
			$month = date("m");
			$year = date("Y");
		}
        if ($xls) {
			$this->db->select("	
								'' as num_row,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name,
								IF(".$this->db->dbprefix('hr_employees').".gender = 'male','M','F') as gender,
								hr_groups.name as group,
								DATE_FORMAT(".$this->db->dbprefix('hr_employees_working_info').".employee_date, '%Y-%m-%d') as employee_date,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".basic_salary,0) as basic_salary,
								(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".present,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".holiday,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".annual_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".special_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".sick_leave,0)) as present,
								(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".present,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".holiday,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".annual_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".special_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".sick_leave,0)) * (IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".basic_salary,0) / IFNULL(".$this->db->dbprefix('companies').".working_day,0)) as present_amount,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) as gross_salary,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_usd,0) as total_usd,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_khr,0) as total_khr,
								'' as signature,
								pay_pre_salaries.id as id")
						->from("pay_pre_salaries")
						->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","inner")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","inner")
						->join("companies","companies.id = hr_employees_working_info.biller_id","inner")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->where("pay_pre_salary_items.present >",0)
						->group_by("pay_pre_salary_items.id");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
			}
			if ($biller) {
				$this->db->where('pay_pre_salaries.biller_id', $biller);
			}
			if ($department) {
				$this->db->where('hr_employees_working_info.department_id', $department);
			}
			if ($position) {
				$this->db->where('hr_employees_working_info.position_id', $position);
			}
			if ($group) {
				$this->db->where('hr_employees_working_info.group_id', $group);
			}
			if ($employee) {
				$this->db->where('pay_pre_salary_items.employee_id', $employee);
			}
			if($year){
				$this->db->where('pay_pre_salaries.year', $year);
			}
			if($month){
				$this->db->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('pre_payslips_report'));

				$this->excel->getActiveSheet()->mergeCells('A1:M1');
				
				if($biller){
					$biller_info = $this->site->getCompanyByID($biller);
					$this->excel->getActiveSheet()->SetCellValue('A1', $biller_info->name);
				}else if(!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')){
					$this->excel->getActiveSheet()->SetCellValue('A1', $biller_info->name);
				}else{
					$this->excel->getActiveSheet()->SetCellValue('A1', $this->Settings->site_name);
				}
				
				$this->excel->getActiveSheet()->mergeCells('A2:M2');
				$this->excel->getActiveSheet()->SetCellValue('A2', "បញ្ជីប្រាក់ខែបើលើកទី១សំរាប់ ខែ".$this->bpas->numberToKhmerMonth(sprintf("%02s", $month))." ឆ្នាំ".$this->bpas->numberToKhmer($year));
				
				$excel_style['font'] = array('bold'  => true,'underline'  => true,'size'  => 18,'color' => array('rgb' => '0070C0'),'name'  => 'Times New Roman');				
				$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);							
				$this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($excel_style);
				
				$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_TOP,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				$excel_style['font']['name'] = 'Khmer OS Bokor';
				$this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($excel_style);	
				$this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(37);
				$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);						
				$row = 3;
				if($group){
					$group_info = $this->payrolls_model->getGroupByID($group);
					$this->excel->getActiveSheet()->mergeCells('A3:M3');
					$this->excel->getActiveSheet()->SetCellValue('A3', "របស់ក្រុម ".$group_info->name);
					$this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($excel_style);		
					$this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(37);
					$row++;
				}
				
				$this->excel->getActiveSheet()->mergeCells('A'.$row.':A'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('B'.$row.':B'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('C'.$row.':C'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('D'.$row.':D'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('E'.$row.':E'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('J'.$row.':L'.$row);
				$this->excel->getActiveSheet()->mergeCells('M'.$row.':M'.($row+1));

                
				$this->excel->getActiveSheet()->SetCellValue('A'.$row, lang('ល-រ'));
                $this->excel->getActiveSheet()->SetCellValue('B'.$row, lang('អត្តលេខ'));
				$this->excel->getActiveSheet()->SetCellValue('C'.$row, lang('ឈ្មោះ'));
				$this->excel->getActiveSheet()->SetCellValue('D'.$row, lang('ភេទ'));
				$this->excel->getActiveSheet()->SetCellValue('E'.$row, lang('ក្រុម'));
				$this->excel->getActiveSheet()->SetCellValue('F'.$row, lang('ថ្ងៃចូល'));
				$this->excel->getActiveSheet()->SetCellValue('F'.($row+1), lang('ធ្វើការ'));
				$this->excel->getActiveSheet()->SetCellValue('G'.$row, lang('ប្រាក់'));
				$this->excel->getActiveSheet()->SetCellValue('G'.($row+1), lang('គោល'));
				$this->excel->getActiveSheet()->SetCellValue('H'.$row, lang('ចំនួន'));
				$this->excel->getActiveSheet()->SetCellValue('H'.($row+1), lang('ថ្ងៃ'));
				$this->excel->getActiveSheet()->SetCellValue('I'.$row, lang('ប្រាក់'));
				$this->excel->getActiveSheet()->SetCellValue('I'.($row+1), lang('ធ្វើបាន'));
				$this->excel->getActiveSheet()->SetCellValue('J'.$row, lang('សរុបប្រាក់'));
				$this->excel->getActiveSheet()->SetCellValue('J'.($row+1), lang('ត្រូវបើក'));
				$this->excel->getActiveSheet()->SetCellValue('K'.($row+1), lang('ប្រាក់ដុល្លារ'));
				$this->excel->getActiveSheet()->SetCellValue('L'.($row+1), lang('ប្រាក់រៀល'));
				$this->excel->getActiveSheet()->SetCellValue('M'.$row, lang('ហត្ថលេខា'));
	
				$excel_style['font']['size'] = 9;
				$excel_style['font']['bold'] = false;
				$excel_style['font']['underline'] = false;
				$excel_style['font']['color']['rgb'] = '000000';
				$excel_style['borders'] = array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN));							
			
				$this->excel->getActiveSheet()->getStyle('A'.$row.':M'.$row)->applyFromArray($excel_style);	
				$this->excel->getActiveSheet()->getStyle('A'.($row+1).':M'.($row+1))->applyFromArray($excel_style);	
				
				$row = $row+2;
				$i = 1;
				
				$present_amount = 0;
				$gross_salary = 0;
				$total_usd = 0;
				$total_khr = 0;
				
				$excel_style['font']['size'] = 8;
				$excel_style['font']['name'] = 'Khmer Kep';
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, ($i++));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, lang($data_row->gender));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($data_row->employee_date));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->basic_salary);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->present);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatNumberWithoutZero($data_row->present_amount));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatNumberWithoutZero($data_row->gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatNumberWithoutZero($data_row->total_usd));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatNumberWithoutZero($data_row->total_khr));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, "");
					
					$excel_style["font"]["bold"] = false;
					$this->excel->getActiveSheet()->getStyle('A'.$row.':M'.$row)->applyFromArray($excel_style);	
					$excel_style["font"]["bold"] = true;
					$this->excel->getActiveSheet()->getStyle('J'.$row.':L'.$row)->applyFromArray($excel_style);	
					
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(45);
					
					$present_amount +=  $this->bpas->formatDecimal($data_row->present_amount);
                    $gross_salary +=  $this->bpas->formatDecimal($data_row->gross_salary);
					$total_usd +=  $this->bpas->formatDecimal($data_row->total_usd);
					$total_khr +=  $this->bpas->formatDecimal($data_row->total_khr);
					$row++;
                }
				$excel_style["font"]["bold"] = true;	
				$this->excel->getActiveSheet()->SetCellValue('H' . $row, lang("total"));
				$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatNumberWithoutZero($present_amount));
				$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatNumberWithoutZero($gross_salary));
				$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatNumberWithoutZero($total_usd));
				$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatNumberWithoutZero($total_khr));
				$this->excel->getActiveSheet()->getStyle('H'.$row.':L'.$row)->applyFromArray($excel_style);	
				
				$row += 5;
				$this->excel->getActiveSheet()->SetCellValue('A' . $row, "Prepared by:_____________        Checked by:_____________        Verified by:_____________        Approved by:_____________");

				
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(4);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(11);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(4);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(13);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(9);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(6);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(8);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
				
				
				$this->excel->getActiveSheet()->getPageMargins()->setTop(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setRight(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setLeft(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setBottom(0.1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				$this->excel->getActiveSheet()->getPageSetup()->setPrintArea('A:M');
				$this->excel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);


				$filename = 'pre_payslips_report_' . date('Y_m_d_H_i_s');
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									'' as num_row,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name,
									IF(".$this->db->dbprefix('hr_employees').".gender = 'male','M','F') as gender,
									hr_groups.name as group,
									DATE_FORMAT(".$this->db->dbprefix('hr_employees_working_info').".employee_date, '%Y-%m-%d') as employee_date,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".basic_salary,0) as basic_salary,
									(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".present,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".holiday,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".annual_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".special_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".sick_leave,0)) as present,
									(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".present,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".holiday,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".annual_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".special_leave,0) + IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".sick_leave,0)) * (IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".basic_salary,0) / IFNULL(".$this->db->dbprefix('companies').".working_day,0)) as present_amount,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) as gross_salary,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_usd,0) as total_usd,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_khr,0) as total_khr,
									'' as signature,
									pay_pre_salaries.id as id")
							->from("pay_pre_salaries")
							->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","inner")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","inner")
							->join("companies","companies.id = hr_employees_working_info.biller_id","inner")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->where("pay_pre_salary_items.present >",0)
							->group_by("pay_pre_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_pre_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_pre_salary_items.employee_id', $employee);
            }
			if($year){
				$this->datatables->where('pay_pre_salaries.year', $year);
			}
			if($month){
				$this->datatables->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function pre_salary_bank_notes_report(){
		$this->bpas->checkPermissions('salary_bank_notes_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_salary_bank_notes_report')));
        $meta = array('page_title' => lang('pre_salary_bank_notes_report'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_salary_bank_notes_report', $meta, $this->data);
	}
	public function getPreSalaryBankNotesReport($xls = false){
        $this->bpas->checkPermissions('salary_bank_notes_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) as gross_salary,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_usd,0) as total_usd,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_khr,0) as total_khr,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_100,0) as usd_100,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_50,0) as usd_50,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_20,0) as usd_20,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_10,0) as usd_10,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_20000,0) as khr_20000,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_10000,0) as khr_10000,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_5000,0) as khr_5000,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_2000,0) as khr_2000,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_1000,0) as khr_1000,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_500,0) as khr_500,
								IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_100,0) as khr_100,
								pay_pre_salaries.id as id")
						->from("pay_pre_salaries")
						->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("pay_pre_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_pre_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_pre_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_pre_salaries.year', $year);
				$this->db->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('pre_salary_bank_notes_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('amount'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('total_usd'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('total_khr'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('100'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('50'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('20'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('10'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('20000'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('10000'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('5000'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('2000'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('1000'));
				$this->excel->getActiveSheet()->SetCellValue('T1', lang('500'));
				$this->excel->getActiveSheet()->SetCellValue('U1', lang('100'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->total_usd));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->total_khr));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->usd_100);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->usd_50);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->usd_20);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->usd_10);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->khr_20000);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->khr_10000);
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->khr_5000);
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->khr_2000);
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->khr_1000);
					$this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->khr_500);
					$this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->khr_100);
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

				$filename = 'pre_salary_bank_notes_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_pre_salaries').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_pre_salaries').".month,'/',".$this->db->dbprefix('pay_pre_salaries').".year) as month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0) as gross_salary,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_usd,0) as total_usd,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_khr,0) as total_khr,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_100,0) as usd_100,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_50,0) as usd_50,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_20,0) as usd_20,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_10,0) as usd_10,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_20000,0) as khr_20000,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_10000,0) as khr_10000,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_5000,0) as khr_5000,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_2000,0) as khr_2000,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_1000,0) as khr_1000,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_500,0) as khr_500,
									IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_100,0) as khr_100,
									pay_pre_salaries.id as id")
							->from("pay_pre_salaries")
							->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_pre_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_pre_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_pre_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_pre_salaries.year', $year);
				$this->datatables->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function pre_salary_groups_report(){
		$this->bpas->checkPermissions('salary_details_report');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_salary_groups_report')));
        $meta = array('page_title' => lang('pre_salary_groups_report'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_salary_groups_report', $meta, $this->data);
	}
	
	public function getPreSalaryGroupsReport($xls = false){
        $this->bpas->checkPermissions('salary_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		if($this->input->get('month')){
			$y_month = explode("/",$this->input->get('month'));
			$month = $y_month[0];
			$year = $y_month[1];
		}else{
			$month = date("m");
			$year = date("Y");
		}
        if ($xls) {
			$this->db->select("	
							hr_groups.name as group_name,
							COUNT(".$this->db->dbprefix('hr_employees').".id) as total_employee,
							SUM(IF(".$this->db->dbprefix('hr_employees').".gender = 'male',0,1)) as female,
							SUM(IF(".$this->db->dbprefix('hr_employees').".gender = 'male',1,0)) as male,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0)) as total_salary,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_usd,0)) as total_usd,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_khr,0)) as total_khr,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_100,0)) as usd_100,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_50,0)) as usd_50,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_20,0)) as usd_20,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_10,0)) as usd_10,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_20000,0)) as khr_20000,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_10000,0)) as khr_10000,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_5000,0)) as khr_5000,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_2000,0)) as khr_2000,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_1000,0)) as khr_1000,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_500,0)) as khr_500,
							SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_100,0)) as khr_100,
							pay_pre_salaries.id as id")
					->from("pay_pre_salaries")
					->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
					->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","inner")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","inner")
					->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","inner")
					->where("pay_pre_salary_items.present >",0)
					->group_by("hr_employees_working_info.group_id");

			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->db->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
			}
			if ($biller) {
				$this->db->where('pay_pre_salaries.biller_id', $biller);
			}
			if ($department) {
				$this->db->where('hr_employees_working_info.department_id', $department);
			}
			if ($position) {
				$this->db->where('hr_employees_working_info.position_id', $position);
			}
			if ($group) {
				$this->db->where('hr_employees_working_info.group_id', $group);
			}
			if ($employee) {
				$this->db->where('pay_pre_salary_items.employee_id', $employee);
			}
			if($year){
				$this->db->where('pay_pre_salaries.year', $year);
			}
			if($month){
				$this->db->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('pre_salary_groups_report'));
				$this->excel->getActiveSheet()->mergeCells('A1:S1');
				if($biller){
					$biller_info = $this->site->getCompanyByID($biller);
					$this->excel->getActiveSheet()->SetCellValue('A1', $biller_info->name);
				}else if(!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')){
					$this->excel->getActiveSheet()->SetCellValue('A1', $biller_info->name);
				}else{
					$this->excel->getActiveSheet()->SetCellValue('A1', $this->Settings->site_name);
				}
				
				$this->excel->getActiveSheet()->mergeCells('A2:S2');
				$this->excel->getActiveSheet()->SetCellValue('A2', "បញ្ជីប្រាក់ខែបើលើកទី១តាមក្រុមសំរាប់ ខែ".$this->bpas->numberToKhmerMonth(sprintf("%02s", $month))." ឆ្នាំ".$this->bpas->numberToKhmer($year));
				
				$excel_style['font'] = array('bold'  => true,'underline'  => true,'size'  => 18,'color' => array('rgb' => '0070C0'),'name'  => 'Times New Roman');				
				$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);							
				$this->excel->getActiveSheet()->getStyle('A1')->applyFromArray($excel_style);
				
				$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_TOP,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				$excel_style['font']['name'] = 'Khmer OS Bokor';
				$this->excel->getActiveSheet()->getStyle('A2')->applyFromArray($excel_style);	
				$this->excel->getActiveSheet()->getRowDimension(2)->setRowHeight(37);
				$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);						
				$row = 3;
				if($group){
					$group_info = $this->payrolls_model->getGroupByID($group);
					$this->excel->getActiveSheet()->mergeCells('A3:S3');
					$this->excel->getActiveSheet()->SetCellValue('A3', "របស់ក្រុម ".$group_info->name);
					$this->excel->getActiveSheet()->getStyle('A3')->applyFromArray($excel_style);		
					$this->excel->getActiveSheet()->getRowDimension(3)->setRowHeight(37);
					$row++;
				}
				
				$this->excel->getActiveSheet()->mergeCells('A'.$row.':A'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('B'.$row.':B'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('C'.$row.':C'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('D'.$row.':D'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('E'.$row.':E'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('F'.$row.':F'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('G'.$row.':G'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('H'.$row.':H'.($row+1));
				$this->excel->getActiveSheet()->mergeCells('I'.$row.':L'.$row);
				$this->excel->getActiveSheet()->mergeCells('M'.$row.':S'.$row);

                
				$this->excel->getActiveSheet()->SetCellValue('A'.$row, lang('#'));
                $this->excel->getActiveSheet()->SetCellValue('B'.$row, lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('C'.$row, lang('total_employee'));
				$this->excel->getActiveSheet()->SetCellValue('D'.$row, lang('female'));
				$this->excel->getActiveSheet()->SetCellValue('E'.$row, lang('male'));
				$this->excel->getActiveSheet()->SetCellValue('F'.$row, lang('total_salary'));
				$this->excel->getActiveSheet()->SetCellValue('G'.$row, lang('total_usd'));
				$this->excel->getActiveSheet()->SetCellValue('H'.$row, lang('total_khr'));
				
				$this->excel->getActiveSheet()->SetCellValue('I'.$row, lang('usd'));
				$this->excel->getActiveSheet()->SetCellValue('I'.($row+1), lang('100'));
				$this->excel->getActiveSheet()->SetCellValue('J'.($row+1), lang('50'));
				$this->excel->getActiveSheet()->SetCellValue('K'.($row+1), lang('20'));
				$this->excel->getActiveSheet()->SetCellValue('L'.($row+1), lang('10'));
				
				$this->excel->getActiveSheet()->SetCellValue('M'.$row, lang('khr'));
				$this->excel->getActiveSheet()->SetCellValue('M'.($row+1), lang('20000'));
				$this->excel->getActiveSheet()->SetCellValue('N'.($row+1), lang('10000'));
				$this->excel->getActiveSheet()->SetCellValue('O'.($row+1), lang('5000'));
				$this->excel->getActiveSheet()->SetCellValue('P'.($row+1), lang('2000'));
				$this->excel->getActiveSheet()->SetCellValue('Q'.($row+1), lang('1000'));
				$this->excel->getActiveSheet()->SetCellValue('R'.($row+1), lang('500'));
				$this->excel->getActiveSheet()->SetCellValue('S'.($row+1), lang('100'));
	
				$excel_style['font']['size'] = 9;
				$excel_style['font']['bold'] = false;
				$excel_style['font']['underline'] = false;
				$excel_style['font']['color']['rgb'] = '000000';
				$excel_style['borders'] = array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN));							
			
				$this->excel->getActiveSheet()->getStyle('A'.$row.':S'.$row)->applyFromArray($excel_style);	
				$this->excel->getActiveSheet()->getStyle('A'.($row+1).':S'.($row+1))->applyFromArray($excel_style);	
				
				$row = $row+2;
				$i = 1;
				
				$total_employee = 0;
				$female = 0;
				$male = 0;
				$total_salary = 0;
				$total_usd = 0;
				$total_khr = 0;
				$usd_100 = 0;
				$usd_50 = 0;
				$usd_20 = 0;
				$usd_10 = 0;
				$khr_20000 = 0;
				$khr_10000 = 0;
				$khr_5000 = 0;
				$khr_2000 = 0;
				$khr_1000 = 0;
				$khr_500 = 0;
				$khr_100 = 0;

				
				$excel_style['font']['size'] = 8;
				$excel_style['font']['name'] = 'Khmer Kep';
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, ($i++));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->group_name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->total_employee);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->female);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->male);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatNumberWithoutZero($data_row->total_salary));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatNumberWithoutZero($data_row->total_usd));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatNumberWithoutZero($data_row->total_khr));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->usd_100);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->usd_50);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->usd_20);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->usd_10);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->khr_20000);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->khr_10000);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->khr_5000);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->khr_2000);
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->khr_1000);
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->khr_500);
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->khr_100);
					
					$excel_style["font"]["bold"] = false;
					$this->excel->getActiveSheet()->getStyle('A'.$row.':S'.$row)->applyFromArray($excel_style);	
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(45);
					
					$total_employee += $data_row->total_employee;
					$female += $data_row->female;
					$male += $data_row->male;
                    $total_salary +=  $this->bpas->formatDecimal($data_row->total_salary);
					$total_usd +=  $this->bpas->formatDecimal($data_row->total_usd);
					$total_khr +=  $this->bpas->formatDecimal($data_row->total_khr);
					$usd_100 += $data_row->usd_100;
					$usd_50 += $data_row->usd_50;
					$usd_20 += $data_row->usd_20;
					$usd_10 += $data_row->usd_10;
					$khr_20000 += $data_row->khr_20000;
					$khr_10000 += $data_row->khr_10000;
					$khr_5000 += $data_row->khr_5000;
					$khr_2000 += $data_row->khr_2000;
					$khr_1000 += $data_row->khr_1000;
					$khr_500 += $data_row->khr_500;
					$khr_100 += $data_row->khr_100;
					$row++;
                }
				$excel_style["font"]["bold"] = true;	
				$this->excel->getActiveSheet()->SetCellValue('B' . $row, lang("total"));
				$this->excel->getActiveSheet()->SetCellValue('C' . $row, $total_employee);
				$this->excel->getActiveSheet()->SetCellValue('D' . $row, $female);
				$this->excel->getActiveSheet()->SetCellValue('E' . $row, $male);
				$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($total_salary));
				$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatDecimal($total_usd));
				$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($total_khr));
				$this->excel->getActiveSheet()->SetCellValue('I' . $row, $usd_100);
				$this->excel->getActiveSheet()->SetCellValue('J' . $row, $usd_50);
				$this->excel->getActiveSheet()->SetCellValue('K' . $row, $usd_20);
				$this->excel->getActiveSheet()->SetCellValue('L' . $row, $usd_10);
				$this->excel->getActiveSheet()->SetCellValue('M' . $row, $khr_20000);
				$this->excel->getActiveSheet()->SetCellValue('N' . $row, $khr_10000);
				$this->excel->getActiveSheet()->SetCellValue('O' . $row, $khr_5000);
				$this->excel->getActiveSheet()->SetCellValue('P' . $row, $khr_2000);
				$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $khr_1000);
				$this->excel->getActiveSheet()->SetCellValue('R' . $row, $khr_500);
				$this->excel->getActiveSheet()->SetCellValue('S' . $row, $khr_100);
				$this->excel->getActiveSheet()->getStyle('B'.$row.':S'.$row)->applyFromArray($excel_style);	
				
				$row += 5;
				$this->excel->getActiveSheet()->SetCellValue('A' . $row, "Prepared by:_____________                Checked by:_____________                Verified by:_____________                Approved by:_____________");

				
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(7);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(10);

				$this->excel->getActiveSheet()->getPageMargins()->setTop(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setRight(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setLeft(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setBottom(0.1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

				$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				$this->excel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

				$filename = 'pre_salary_groups_report_' . date('Y_m_d_H_i_s');
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									'' as num_row,
									hr_groups.name as group_name,
									COUNT(".$this->db->dbprefix('hr_employees').".id) as total_employee,
									SUM(IF(".$this->db->dbprefix('hr_employees').".gender = 'male',0,1)) as female,
									SUM(IF(".$this->db->dbprefix('hr_employees').".gender = 'male',1,0)) as male,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".gross_salary,0)) as gross_salary,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_usd,0)) as total_usd,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".total_khr,0)) as total_khr,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_100,0)) as usd_100,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_50,0)) as usd_50,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_20,0)) as usd_20,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".usd_10,0)) as usd_10,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_20000,0)) as khr_20000,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_10000,0)) as khr_10000,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_5000,0)) as khr_5000,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_2000,0)) as khr_2000,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_1000,0)) as khr_1000,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_500,0)) as khr_500,
									SUM(IFNULL(".$this->db->dbprefix('pay_pre_salary_items').".khr_100,0)) as khr_100,
									pay_pre_salaries.id as id")
							->from("pay_pre_salaries")
							->join("pay_pre_salary_items","pay_pre_salary_items.pre_salary_id = pay_pre_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_pre_salary_items.employee_id","inner")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_pre_salary_items.employee_id","inner")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","inner")
							->where("pay_pre_salary_items.present >",0)
							->group_by("hr_employees_working_info.group_id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_pre_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_pre_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_pre_salary_items.employee_id', $employee);
            }
			if($year){
				$this->datatables->where('pay_pre_salaries.year', $year);
			}
			if($month){
				$this->datatables->where('pay_pre_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_pre_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	public function pre_payslip_forms_report(){
		$this->bpas->checkPermissions("payslips_report");
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : false;
		$position = $this->input->post('position') ? $this->input->post('position') : false;
		$department = $this->input->post('department') ? $this->input->post('department') : false;
		$group = $this->input->post('group') ? $this->input->post('group') : false;
		$employee = $this->input->post('employee') ? $this->input->post('employee') : false;
		if($this->input->post('month') ){
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
		}else{
			$month = date("m");
			$year = date("Y");
		}
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
		$this->data["payslips"] = $this->payrolls_model->getPrePayslips($year,$month,$biller,$position,$department,$group,$employee);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('pre_payslip_forms_report')));
        $meta = array('page_title' => lang('pre_payslip_forms_report'), 'bc' => $bc);
        $this->page_construct('payrolls/pre_payslip_forms_report', $meta, $this->data);
	}

	public function severances($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('severances')));
        $meta = array('page_title' => lang('severances'), 'bc' => $bc);
        $this->page_construct('payrolls/severances', $meta, $this->data);
	}
	
	public function getSeverances($biller_id = false){
		$this->bpas->checkPermissions("severances");
        $edit_link = anchor('admin/payrolls/edit_severance/$1', '<i class="fa fa-edit"></i> ' . lang('edit_severance'), ' class="edit_severance"');
        $delete_link = "<a href='#' class='delete_severance po' title='<b>" . $this->lang->line("delete_severance") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payrolls/delete_severance/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_severance') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_severance']){
			$approve_link = "<a href='#' class='po approve_severance' title='" . $this->lang->line("approve_severance") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('payrolls/approve_severance/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_severance') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_severance' title='<b>" . $this->lang->line("unapprove_severance") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('payrolls/unapprove_severance/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_severance') . "</a>";
		}
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_severances.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_severances').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_severances').".month,'/',".$this->db->dbprefix('pay_severances').".year) as month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_severances').".total,0) as total,	
									pay_severances.note,
									pay_severances.status,
									pay_severances.attachment
									")
							->from("pay_severances")
							->join("users","users.id = pay_severances.created_by","left")
							;
		if ($biller_id) {
            $this->datatables->where("pay_severances.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_severances.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_severances.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	function severance_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_severance');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$severance = $this->payrolls_model->getSeveranceByID($id);
						if($severance->status == "pending"){
							$deleted = 1;
							$this->payrolls_model->deleteseverance($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("severance_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("severance_cannot_delete"));
					}
                    redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('severance');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('total'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $severance = $this->payrolls_model->getSeveranceByID($id); 
						$user = $this->site->getUserByID($severance->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($severance->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $severance->month."/".$severance->year);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($severance->total));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($severance->note));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($severance->status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'severance_list_' . date('Y_m_d_H_i_s');
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
	
	public function add_severance($biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
		if ($this->form_validation->run() == true) {
			$kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$paid_by = $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$total = 0;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['severance'][$r]){
					$employee_id = $_POST['employee_id'][$r];
					$bank_notes = $this->bpas->seperateBankNote($_POST['severance'][$r],$kh_rate);
					$items[] = array(
									"employee_id" => $employee_id,
									"first_salary" => $_POST['first_salary'][$r],
									"second_salary" => $_POST['second_salary'][$r],
									"third_salary" => $_POST['third_salary'][$r],
									"total_salary" => $_POST['total_salary'][$r],
									"severance" => $_POST['severance'][$r],
									"total_usd" => $bank_notes["total_usd"],
									"total_khr" => $bank_notes["total_khr"],
									"usd_100" => $bank_notes["usd_100"],
									"usd_50" => $bank_notes["usd_50"],
									"usd_20" => $bank_notes["usd_20"],
									"usd_10" => $bank_notes["usd_10"],
									"khr_20000" => $bank_notes["khr_20000"],
									"khr_10000" => $bank_notes["khr_10000"],
									"khr_5000" => $bank_notes["khr_5000"],
									"khr_2000" => $bank_notes["khr_2000"],
									"khr_1000" => $bank_notes["khr_1000"],
									"khr_500" => $bank_notes["khr_500"],
									"khr_100" => $bank_notes["khr_100"]
								);
					$total += $_POST['severance'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
				'total' => $total,
				'paid_by' => $paid_by,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s')
            );
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addSeverance($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("severance_added"));          
			admin_redirect('payrolls/severances');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['biller_id'] = $biller_id;
			$this->data['departments'] = $biller_id ? $this->payrolls_model->getDepartments($biller_id) : false;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/severances'), 'page' => lang('severances')), array('link' => '#', 'page' => lang('add_severance')));
            $meta = array('page_title' => lang('add_severance'), 'bc' => $bc);
            $this->page_construct('payrolls/add_severance', $meta, $this->data);
        }
	}
	
	public function edit_severance($id = false, $biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			$kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$paid_by = $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$y_month = explode("/",$this->input->post('month'));
			$month = $y_month[0];
			$year = $y_month[1];
			$total = 0;
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['severance'][$r]){
					$employee_id = $_POST['employee_id'][$r];
					$bank_notes = $this->bpas->seperateBankNote($_POST['severance'][$r],$kh_rate);
					$items[] = array(
									"severance_id" => $id,
									"employee_id" => $employee_id,
									"first_salary" => $_POST['first_salary'][$r],
									"second_salary" => $_POST['second_salary'][$r],
									"third_salary" => $_POST['third_salary'][$r],
									"total_salary" => $_POST['total_salary'][$r],
									"severance" => $_POST['severance'][$r],
									"total_usd" => $bank_notes["total_usd"],
									"total_khr" => $bank_notes["total_khr"],
									"usd_100" => $bank_notes["usd_100"],
									"usd_50" => $bank_notes["usd_50"],
									"usd_20" => $bank_notes["usd_20"],
									"usd_10" => $bank_notes["usd_10"],
									"khr_20000" => $bank_notes["khr_20000"],
									"khr_10000" => $bank_notes["khr_10000"],
									"khr_5000" => $bank_notes["khr_5000"],
									"khr_2000" => $bank_notes["khr_2000"],
									"khr_1000" => $bank_notes["khr_1000"],
									"khr_500" => $bank_notes["khr_500"],
									"khr_100" => $bank_notes["khr_100"]
								);
					$total += $_POST['severance'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' => $date,
                'month' => $month,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'total' => $total,
				'paid_by' => $paid_by,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
			
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateSeverance($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("severance_updated"));          
			admin_redirect('payrolls/severances');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$severance = $this->payrolls_model->getSeveranceByID($id);
			$this->data['severance'] = $severance;
			$this->data['severance_items'] = $this->payrolls_model->getSeveranceItems($id);
			if($severance->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($severance->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			if(!$biller_id){
				$biller_id = $severance->biller_id;;
			}
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller_id);
			if($severance->department_id && $biller_id == $severance->biller_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($severance->department_id);
				$this->data['positions'] = $this->payrolls_model->getPositions($severance->department_id);
			}
			$this->data['biller_id'] = $biller_id;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/severances'), 'page' => lang('severances')), array('link' => '#', 'page' => lang('edit_severance')));
            $meta = array('page_title' => lang('edit_severance'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_severance', $meta, $this->data);
        }
	}
	
	public function delete_severance($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteSeverance($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('severance_deleted')]);
            }
            $this->session->set_flashdata('message', lang('severance_deleted'));
            redirect('welcome');
        }
    }
	
	public function approve_severance($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateSeveranceStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('severance_approved')]);
            }
            $this->session->set_flashdata('message', lang('severance_approved'));
            redirect('welcome');
        }
    }
	
	public function unapprove_severance($id = null){
        $this->bpas->checkPermissions("approve_severance", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->payrolls_model->updateSeveranceStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('severance_unapproved'));
        }
		redirect('payrolls/severances');
    }
    public function get_severance_employees(){
		$biller_id = $this->input->get('biller_id') ? $this->input->get('biller_id') : false;
		$position_id = $this->input->get('position_id') ? $this->input->get('position_id') : false;
		$department_id = $this->input->get('department_id') ? $this->input->get('department_id') : false;
		$group_id = $this->input->get('group_id') ? $this->input->get('group_id') : false;
		$y_month = explode("/",$this->input->get('month'));
		$month = $y_month[0];
		$year = $y_month[1];
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getSeveranceEmployee($biller_id,$position_id,$department_id,$group_id,$month,$year,$edit_id);
		if($employees){
			foreach($employees as $employee){
				if (preg_match('/[0-9]+%/', $employee->severance, $matches)){
					$rate = explode("%", $matches[0]);
					$employee->severance = $this->bpas->formatDecimal($employee->total_salary * $rate[0] / 100);
				}
			}
		}
		echo json_encode($employees);
	}
	
	public function modal_view_severance($id = false){
		$this->bpas->checkPermissions('severances', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $severance = $this->payrolls_model->getSeveranceByID($id);
		$this->data['severance'] = $severance;
        $this->data['severance_items'] = $this->payrolls_model->getSeveranceItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($severance->biller_id);
        $this->data['created_by'] = $this->site->getUser($severance->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_severance', $this->data);
	}
	
	public function severance_details_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('severance_details_report')));
        $meta = array('page_title' => lang('severance_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/severance_details_report', $meta, $this->data);
	}
	
	public function getSeverancDetailsReport($xls = NULL){
        $this->bpas->checkPermissions('severance_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : date("m/Y");
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($xls) {
			$this->db->select("	
							DATE_FORMAT(".$this->db->dbprefix('pay_severances').".date, '%Y-%m-%d %T') as date,
							CONCAT(".$this->db->dbprefix('pay_severances').".month,'/',".$this->db->dbprefix('pay_severances').".year) as month,
							hr_employees.empcode,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".first_salary,0) as first_salary,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".second_salary,0) as second_salary,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".third_salary,0) as third_salary,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".total_salary,0) as total_salary,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".severance,0) as severance,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".total_usd,0) as total_usd,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".total_khr,0) as total_khr,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_100,0) as usd_100,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_50,0) as usd_50,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_20,0) as usd_20,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_10,0) as usd_10,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_20000,0) as khr_20000,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_10000,0) as khr_10000,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_5000,0) as khr_5000,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_2000,0) as khr_2000,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_1000,0) as khr_1000,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_500,0) as khr_500,
							IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_100,0) as khr_100,
							pay_severances.id as id")
					->from("pay_severances")
					->join("pay_severance_items","pay_severance_items.severance_id = pay_severances.id","inner")
					->join("hr_employees","hr_employees.id = pay_severance_items.employee_id","left")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_severance_items.employee_id","left")
					->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
					->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
					->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
					->group_by("pay_severance_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_severances.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_severances.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_severance_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_severances.year', $year);
				$this->db->where('pay_severances.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_severances.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
				$date = $this->bpas->fsd("01/".($this->input->get('month') ? $this->input->get('month') : date("m/Y")));
				$date = strtotime($date);
				$first_month = date("m/Y", strtotime("-3 month", $date));
				$second_month = date("m/Y", strtotime("-2 month", $date));
				$third_month = date("m/Y", strtotime("-1 month", $date));
				
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('severance_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', $first_month);
				$this->excel->getActiveSheet()->SetCellValue('I1', $second_month);
				$this->excel->getActiveSheet()->SetCellValue('J1', $third_month);
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('total_salary'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('severance'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('total_usd'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('total_khr'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('100'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('50'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('20'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('10'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('20000'));
				$this->excel->getActiveSheet()->SetCellValue('T1', lang('10000'));
				$this->excel->getActiveSheet()->SetCellValue('U1', lang('5000'));
				$this->excel->getActiveSheet()->SetCellValue('V1', lang('2000'));
				$this->excel->getActiveSheet()->SetCellValue('W1', lang('1000'));
				$this->excel->getActiveSheet()->SetCellValue('X1', lang('500'));
				$this->excel->getActiveSheet()->SetCellValue('Y1', lang('100'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatDecimal($data_row->first_salary));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->second_salary));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->third_salary));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->total_salary));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->severance));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->total_usd));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatDecimal($data_row->total_khr));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->usd_100);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->usd_50);
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->usd_20);
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->usd_10);
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->khr_20000);
					$this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->khr_10000);
					$this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->khr_5000);
					$this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->khr_2000);
					$this->excel->getActiveSheet()->SetCellValue('W' . $row, $data_row->khr_1000);
					$this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->khr_500);
					$this->excel->getActiveSheet()->SetCellValue('Y' . $row, $data_row->khr_100);
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$filename = 'severance_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_severances').".date, '%Y-%m-%d %T') as date,
									CONCAT(".$this->db->dbprefix('pay_severances').".month,'/',".$this->db->dbprefix('pay_severances').".year) as month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".first_salary,0) as first_salary,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".second_salary,0) as second_salary,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".third_salary,0) as third_salary,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".total_salary,0) as total_salary,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".severance,0) as severance,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".total_usd,0) as total_usd,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".total_khr,0) as total_khr,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_100,0) as usd_100,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_50,0) as usd_50,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_20,0) as usd_20,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".usd_10,0) as usd_10,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_20000,0) as khr_20000,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_10000,0) as khr_10000,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_5000,0) as khr_5000,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_2000,0) as khr_2000,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_1000,0) as khr_1000,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_500,0) as khr_500,
									IFNULL(".$this->db->dbprefix('pay_severance_items').".khr_100,0) as khr_100,
									pay_severances.id as id")
							->from("pay_severances")
							->join("pay_severance_items","pay_severance_items.severance_id = pay_severances.id","inner")
							->join("hr_employees","hr_employees.id = pay_severance_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_severance_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("pay_severance_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_severances.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_severances.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_severance_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_severances.year', $year);
				$this->datatables->where('pay_severances.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_severances.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }

    public function indemnity($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('indemnity')));
        $meta = array('page_title' => lang('indemnity'), 'bc' => $bc);
        $this->page_construct('payrolls/indemnity', $meta, $this->data);
	}
	
	public function getALCompensates($biller_id = false){
		$this->bpas->checkPermissions("indemnity");
        $edit_link = anchor('payrolls/edit_al_compensate/$1', '<i class="fa fa-edit"></i> ' . lang('edit_al_compensate'), ' class="edit_al_compensate"');
        $delete_link = "<a href='#' class='delete_al_compensate po' title='<b>" . $this->lang->line("delete_al_compensate") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('payrolls/delete_al_compensate/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_al_compensate') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['payrolls-approve_al_compensate']){
			$approve_link = "<a href='#' class='po approve_al_compensate' title='" . $this->lang->line("approve_al_compensate") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . site_url('payrolls/approve_al_compensate/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_al_compensate') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_al_compensate' title='<b>" . $this->lang->line("unapprove_al_compensate") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . site_url('payrolls/unapprove_al_compensate/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_al_compensate') . "</a>";
		}
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
                        <li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	pay_al_compensates.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('pay_al_compensates').".date, '%Y-%m-%d %T') as date,
									pay_al_compensates.year,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									IFNULL(".$this->db->dbprefix('pay_al_compensates').".total,0) as total,	
									pay_al_compensates.note,
									pay_al_compensates.status,
									pay_al_compensates.attachment
									")
							->from("pay_al_compensates")
							->join("users","users.id = pay_al_compensates.created_by","left")
							;
		if ($biller_id) {
            $this->datatables->where("pay_al_compensates.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("pay_al_compensates.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("pay_al_compensates.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	
	public function add_indemnity($biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('year', $this->lang->line("year"), 'required');
		if ($this->form_validation->run() == true) {
			$kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
            $date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$paid_by = $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$year = $this->input->post('year') ? $this->input->post('year') : null;
			$total = 0;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['employee_id'][$r]){
					$employee_id = $_POST['employee_id'][$r];
					$bank_notes = $this->bpas->seperateBankNote($_POST['al_amount'][$r],$kh_rate);
					$items[] = array(
									"employee_id" => $employee_id,
									"position_id" => $_POST['position_id'][$r],
									"department_id" => $_POST['department_id'][$r],
									"group_id" => $_POST['group_id'][$r],
									"employee_date" => $_POST['employee_date'][$r],
									"basic_salary" => $_POST['basic_salary'][$r],
									"al_day" => $_POST['al_day'][$r],
									"al_amount" => $_POST['al_amount'][$r],
									"total_usd" => $bank_notes["total_usd"],
									"total_khr" => $bank_notes["total_khr"],
									"usd_100" => $bank_notes["usd_100"],
									"usd_50" => $bank_notes["usd_50"],
									"usd_20" => $bank_notes["usd_20"],
									"usd_10" => $bank_notes["usd_10"],
									"khr_20000" => $bank_notes["khr_20000"],
									"khr_10000" => $bank_notes["khr_10000"],
									"khr_5000" => $bank_notes["khr_5000"],
									"khr_2000" => $bank_notes["khr_2000"],
									"khr_1000" => $bank_notes["khr_1000"],
									"khr_500" => $bank_notes["khr_500"],
									"khr_100" => $bank_notes["khr_100"]
								);
					$total += $_POST['al_amount'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' => $date,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
				'total' => $total,
				'paid_by' => $paid_by,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s')
            );
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->addALCompensate($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("al_compensate_added"));          
			admin_redirect('payrolls/al_compensates');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['biller_id'] = $biller_id;
			$this->data['departments'] = $biller_id ? $this->payrolls_model->getDepartments($biller_id) : false;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/al_compensates'), 'page' => lang('al_compensates')), array('link' => '#', 'page' => lang('add_indemnity')));
            $meta = array('page_title' => lang('add_indemnity'), 'bc' => $bc);
            $this->page_construct('payrolls/add_indemnity', $meta, $this->data);
        }
	}
	
	public function edit_al_compensate($id = false, $biller_id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('year', $this->lang->line("year"), 'required');
        if ($this->form_validation->run() == true) {
			$kh_rate = $this->site->getCurrencyByCode("KHR")->rate;
			$date = $this->bpas->fld(trim($this->input->post('date')));
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$paid_by = $this->input->post('paid_by') ? $this->input->post('paid_by') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$year = $this->input->post('year') ? $this->input->post('year') : null;
			$total = 0;
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				if($_POST['employee_id'][$r]){
					$employee_id = $_POST['employee_id'][$r];
					$bank_notes = $this->bpas->seperateBankNote($_POST['al_amount'][$r],$kh_rate);
					$items[] = array(
									"al_compensate_id" => $id,
									"employee_id" => $employee_id,
									"position_id" => $_POST['position_id'][$r],
									"department_id" => $_POST['department_id'][$r],
									"group_id" => $_POST['group_id'][$r],
									"employee_date" => $_POST['employee_date'][$r],
									"basic_salary" => $_POST['basic_salary'][$r],
									"al_day" => $_POST['al_day'][$r],
									"al_amount" => $_POST['al_amount'][$r],
									"total_usd" => $bank_notes["total_usd"],
									"total_khr" => $bank_notes["total_khr"],
									"usd_100" => $bank_notes["usd_100"],
									"usd_50" => $bank_notes["usd_50"],
									"usd_20" => $bank_notes["usd_20"],
									"usd_10" => $bank_notes["usd_10"],
									"khr_20000" => $bank_notes["khr_20000"],
									"khr_10000" => $bank_notes["khr_10000"],
									"khr_5000" => $bank_notes["khr_5000"],
									"khr_2000" => $bank_notes["khr_2000"],
									"khr_1000" => $bank_notes["khr_1000"],
									"khr_500" => $bank_notes["khr_500"],
									"khr_100" => $bank_notes["khr_100"]
								);
					$total += $_POST['al_amount'][$r];
				}
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			}
			
			$data = array(
				'date' => $date,
                'year' => $year,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'total' => $total,
				'paid_by' => $paid_by,
				'updated_by' => $this->session->userdata('user_id'),
				'updated_at' => date('Y-m-d H:i:s')
            );
			
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
                $attachment = $this->upload->file_name;
                $data['attachment'] = $attachment;
            }
        }
		if ($this->form_validation->run() == true && $this->payrolls_model->updateALCompensate($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("al_compensate_updated"));          
			admin_redirect('payrolls/al_compensates');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$al_compensate = $this->payrolls_model->getALCompensateByID($id);
			$this->data['al_compensate'] = $al_compensate;
			$this->data['al_compensate_items'] = $this->payrolls_model->getALCompensateItems($id);
			if($al_compensate->department_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($al_compensate->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			if(!$biller_id){
				$biller_id = $al_compensate->biller_id;;
			}
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller_id);
			if($al_compensate->department_id && $biller_id == $al_compensate->biller_id){
				$this->data['groups'] = $this->payrolls_model->getGroups($al_compensate->department_id);
				$this->data['positions'] = $this->payrolls_model->getPositions($al_compensate->department_id);
			}
			$this->data['biller_id'] = $biller_id;
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => site_url('payrolls/al_compensates'), 'page' => lang('al_compensates')), array('link' => '#', 'page' => lang('edit_al_compensate')));
            $meta = array('page_title' => lang('edit_al_compensate'), 'bc' => $bc);
            $this->page_construct('payrolls/edit_al_compensate', $meta, $this->data);
        }
	}
	
	public function delete_al_compensate($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->deleteALCompensate($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('al_compensate_deleted')]);
            }
            $this->session->set_flashdata('message', lang('al_compensate_deleted'));
            redirect('welcome');
        }
    }
    public function get_al_compensate_employees(){
		$biller_id = $this->input->get('biller_id') ? $this->input->get('biller_id') : false;
		$position_id = $this->input->get('position_id') ? $this->input->get('position_id') : false;
		$department_id = $this->input->get('department_id') ? $this->input->get('department_id') : false;
		$group_id = $this->input->get('group_id') ? $this->input->get('group_id') : false;
		$year = $this->input->get('year') ? $this->input->get('year') : false;
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->payrolls_model->getALCompensateEmployee($biller_id,$position_id,$department_id,$group_id,$year,$edit_id);
		if($employees){
			foreach($employees as $employee){
				$employee->annual_leave = $employee->annual_leave / 12;
				$employee_year =  date("Y", strtotime($employee->employee_date));
				$working_month = 12;
				if($employee_year >= $year){
					$working_month = (int) date("m", strtotime($employee->employee_date));
				}
				$employee->annual_leave = $employee->annual_leave * $working_month;
				$employee->al_day = $employee->annual_leave - $employee->al_day;
				$employee->al_amount = $employee->al_day * ($employee->basic_salary / $employee->working_day);
			}
		}
		echo json_encode($employees);
	}
	
	public function al_compensate_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_al_compensate');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$al_compensate = $this->payrolls_model->getALCompensateByID($id);
						if($al_compensate->status == "pending"){
							$deleted = 1;
							$this->payrolls_model->deleteALCompensate($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("al_compensate_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("al_compensate_cannot_delete"));
					}
                    redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('al_compensate');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('year'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('total'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $al_compensate = $this->payrolls_model->getALCompensateByID($id); 
						$user = $this->site->getUserByID($al_compensate->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($al_compensate->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $al_compensate->year);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($al_compensate->total));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($al_compensate->note));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($al_compensate->status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'al_compensate_list_' . date('Y_m_d_H_i_s');
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
	
	public function modal_view_al_compensate($id = false){
		$this->bpas->checkPermissions('al_compensates', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $al_compensate = $this->payrolls_model->getALCompensateByID($id);
		$this->data['al_compensate'] = $al_compensate;
        $this->data['al_compensate_items'] = $this->payrolls_model->getALCompensateItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($al_compensate->biller_id);
        $this->data['created_by'] = $this->site->getUser($al_compensate->created_by);
        $this->load->view($this->theme . 'payrolls/modal_view_al_compensate', $this->data);
	}
	
	public function approve_al_compensate($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->payrolls_model->updateALCompensateStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('al_compensate_approved')]);
            }
            $this->session->set_flashdata('message', lang('al_compensate_approved'));
            admin_redirect('welcome');
        }
    }
	
	public function unapprove_al_compensate($id = null){
        $this->bpas->checkPermissions("approve_al_compensate", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->payrolls_model->updateALCompensateStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('al_compensate_unapproved'));
        }
		admin_redirect('payrolls/al_compensates');
    }
	
	public function al_compensate_details_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
				$this->data['positions'] = $this->payrolls_model->getPositions($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('al_compensate_details_report')));
        $meta = array('page_title' => lang('al_compensate_details_report'), 'bc' => $bc);
        $this->page_construct('payrolls/al_compensate_details_report', $meta, $this->data);
	}
	
	public function getALCompensateDetailsReport($xls = NULL){
        $this->bpas->checkPermissions('al_compensate_details_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $year = $this->input->get('year') ? $this->input->get('year') : date("Y");
        if ($xls) {
			$this->db->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_al_compensates').".date, '%Y-%m-%d %T') as date,
									pay_al_compensates.year,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									DATE_FORMAT(".$this->db->dbprefix('pay_al_compensate_items').".employee_date, '%Y-%m-%d') as employee_date,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".basic_salary,0) as basic_salary,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".al_day,0) as al_day,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".al_amount,0) as al_amount,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".total_usd,0) as total_usd,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".total_khr,0) as total_khr,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_100,0) as usd_100,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_50,0) as usd_50,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_20,0) as usd_20,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_10,0) as usd_10,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_20000,0) as khr_20000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_10000,0) as khr_10000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_5000,0) as khr_5000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_2000,0) as khr_2000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_1000,0) as khr_1000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_500,0) as khr_500,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_100,0) as khr_100,
									pay_al_compensates.id as id")
							->from("pay_al_compensates")
							->join("pay_al_compensate_items","pay_al_compensate_items.al_compensate_id = pay_al_compensates.id","inner")
							->join("hr_employees","hr_employees.id = pay_al_compensate_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_al_compensate_items.employee_id","left")
							->join("hr_positions","hr_positions.id = pay_al_compensate_items.position_id","left")
							->join("hr_departments","hr_departments.id = pay_al_compensate_items.department_id","left")
							->join("hr_groups","hr_groups.id = pay_al_compensate_items.group_id","left")
							->group_by("pay_al_compensate_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_al_compensates.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_al_compensates.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('pay_al_compensate_items.department_id', $department);
            }
			if ($position) {
                $this->db->where('pay_al_compensate_items.position_id', $position);
            }
			if ($group) {
                $this->db->where('pay_al_compensate_items.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_al_compensate_items.employee_id', $employee);
            }
			if($year){
				$this->db->where('pay_al_compensates.year', $year);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_al_compensates.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
				$date = $this->bpas->fsd("01/".($this->input->get('month') ? $this->input->get('month') : date("m/Y")));
				$date = strtotime($date);
				$first_month = date("m/Y", strtotime("-3 month", $date));
				$second_month = date("m/Y", strtotime("-2 month", $date));
				$third_month = date("m/Y", strtotime("-1 month", $date));
				
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('al_compensate_details_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('year'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('employee_date'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('basic_salary'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('al_day'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('al_amount'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('total_usd'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('total_khr'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('100'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('50'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('20'));
				$this->excel->getActiveSheet()->SetCellValue('Q1', lang('10'));
				$this->excel->getActiveSheet()->SetCellValue('R1', lang('20000'));
				$this->excel->getActiveSheet()->SetCellValue('S1', lang('10000'));
				$this->excel->getActiveSheet()->SetCellValue('T1', lang('5000'));
				$this->excel->getActiveSheet()->SetCellValue('U1', lang('2000'));
				$this->excel->getActiveSheet()->SetCellValue('V1', lang('1000'));
				$this->excel->getActiveSheet()->SetCellValue('W1', lang('500'));
				$this->excel->getActiveSheet()->SetCellValue('X1', lang('100'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->year);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->hrsd($data_row->employee_date));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->basic_salary));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->al_day);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatDecimal($data_row->al_amount));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatDecimal($data_row->total_usd));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatDecimal($data_row->total_khr));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->usd_100);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->usd_50);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->usd_20);
					$this->excel->getActiveSheet()->SetCellValue('Q' . $row, $data_row->usd_10);
					$this->excel->getActiveSheet()->SetCellValue('R' . $row, $data_row->khr_20000);
					$this->excel->getActiveSheet()->SetCellValue('S' . $row, $data_row->khr_10000);
					$this->excel->getActiveSheet()->SetCellValue('T' . $row, $data_row->khr_5000);
					$this->excel->getActiveSheet()->SetCellValue('U' . $row, $data_row->khr_2000);
					$this->excel->getActiveSheet()->SetCellValue('V' . $row, $data_row->khr_1000);
					$this->excel->getActiveSheet()->SetCellValue('W' . $row, $data_row->khr_500);
					$this->excel->getActiveSheet()->SetCellValue('X' . $row, $data_row->khr_100);
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$filename = 'al_compensate_details_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									DATE_FORMAT(".$this->db->dbprefix('pay_al_compensates').".date, '%Y-%m-%d %T') as date,
									pay_al_compensates.year,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									DATE_FORMAT(".$this->db->dbprefix('pay_al_compensate_items').".employee_date, '%Y-%m-%d') as employee_date,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".basic_salary,0) as basic_salary,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".al_day,0) as al_day,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".al_amount,0) as al_amount,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".total_usd,0) as total_usd,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".total_khr,0) as total_khr,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_100,0) as usd_100,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_50,0) as usd_50,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_20,0) as usd_20,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".usd_10,0) as usd_10,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_20000,0) as khr_20000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_10000,0) as khr_10000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_5000,0) as khr_5000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_2000,0) as khr_2000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_1000,0) as khr_1000,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_500,0) as khr_500,
									IFNULL(".$this->db->dbprefix('pay_al_compensate_items').".khr_100,0) as khr_100,
									pay_al_compensates.id as id")
							->from("pay_al_compensates")
							->join("pay_al_compensate_items","pay_al_compensate_items.al_compensate_id = pay_al_compensates.id","inner")
							->join("hr_employees","hr_employees.id = pay_al_compensate_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_al_compensate_items.employee_id","left")
							->join("hr_positions","hr_positions.id = pay_al_compensate_items.position_id","left")
							->join("hr_departments","hr_departments.id = pay_al_compensate_items.department_id","left")
							->join("hr_groups","hr_groups.id = pay_al_compensate_items.group_id","left")
							->group_by("pay_al_compensate_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_al_compensates.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_al_compensates.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('pay_al_compensate_items.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('pay_al_compensate_items.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('pay_al_compensate_items.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_al_compensate_items.employee_id', $employee);
            }
			if($year){
				$this->datatables->where('pay_al_compensates.year', $year);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_al_compensates.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function nssf_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->payrolls_model->getPositions($biller);
			$this->data['departments'] = $this->payrolls_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->payrolls_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('nssf_report')));
        $meta = array('page_title' => lang('nssf_report'), 'bc' => $bc);
        $this->page_construct('payrolls/nssf_report', $meta, $this->data);
	}
	public function getNssfReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('nssf_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $y_month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if($y_month){
			$y_month = explode("/",$y_month);
			$month = $y_month[0];
			$year = $y_month[1];
		}
        if ($pdf || $xls) {
			$this->db->select("	
								DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
								CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".contributory_nssf,0) as contributory_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_staff,0) as pension_by_staff,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_company,0) as pension_by_company,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".health_nssf,0) as health_nssf,
								IFNULL(".$this->db->dbprefix('pay_salary_items').".accident_nssf,0) as accident_nssf,
								pay_salary_items.id as id")
						->from("pay_salaries")
						->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
						->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->where("hr_employees.nssf",1)
						->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->db->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->db->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->db->where('pay_salaries.year', $year);
				$this->db->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            $q = $this->db->get();
			$data = false;
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					)
				);
			
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('contributory_nssf'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('pension_by_staff'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('pension_by_company'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('health_nssf'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('accident_nssf'));
				$row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A'. $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B'. $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C'. $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D'. $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E'. $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F'. $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G'. $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H'. $row, $this->bpas->formatMoney($data_row->contributory_nssf));
					$this->excel->getActiveSheet()->SetCellValue('I'. $row, $this->bpas->formatMoney($data_row->pension_by_staff));
					$this->excel->getActiveSheet()->SetCellValue('J'. $row, $this->bpas->formatMoney($data_row->pension_by_company));
					$this->excel->getActiveSheet()->SetCellValue('K'. $row, $this->bpas->formatMoney($data_row->health_nssf));
					$this->excel->getActiveSheet()->SetCellValue('L'. $row, $this->bpas->formatMoney($data_row->accident_nssf)); 
					$row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
				$filename = 'nssf_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
				DATE_FORMAT(".$this->db->dbprefix('pay_salaries').".date, '%Y-%m-%d %T') as date,
				CONCAT(".$this->db->dbprefix('pay_salaries').".month,'/',".$this->db->dbprefix('pay_salaries').".year) as month,
				hr_employees.empcode,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
				hr_positions.name as position,
				hr_departments.name as department,
				hr_groups.name as group,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".contributory_nssf,0) as contributory_nssf,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_staff,0) as pension_by_staff,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".pension_by_company,0) as pension_by_company,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".health_nssf,0) as health_nssf,
				IFNULL(".$this->db->dbprefix('pay_salary_items').".accident_nssf,0) as accident_nssf,
				pay_salary_items.id as id")
							->from("pay_salaries")
							->join("pay_salary_items","pay_salary_items.salary_id = pay_salaries.id","inner")
							->join("hr_employees","hr_employees.id = pay_salary_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = pay_salary_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->where("hr_employees.nssf",1)
							->group_by("pay_salary_items.id");

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('pay_salaries.created_by', $this->session->userdata('user_id'));
            }
			if ($biller) {
                $this->datatables->where('pay_salaries.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('pay_salary_items.employee_id', $employee);
            }
			if($y_month){
				$this->datatables->where('pay_salaries.year', $year);
				$this->datatables->where('pay_salaries.month', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('pay_salaries.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    

}
?>