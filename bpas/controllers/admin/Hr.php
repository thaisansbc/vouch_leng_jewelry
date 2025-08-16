<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hr extends MY_Controller
{
	function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        $this->lang->admin_load('hr', $this->Settings->user_language);
		$this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->load->admin_model('hr_model');		
        $this->load->admin_model('schools_model');		
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
    }
	
	function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1) {
            die();
        }

        $rows = $this->hr_model->getEmployeeNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->lastname." ".$row->firstname." (" . $row->empcode . ")");

            }
            $this->bpas->send_json($pr);
        } else {
            echo FALSE;
        }
    }
     public function Employeesuggestions()
    {
    	$term = $this->input->get('term', true); 
		$biller_id = $this->input->get('biller_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->hr_model->getAllEmployee($sr,$biller_id);
        if ($rows) {
            foreach ($rows as $row) {		
				$row->start_date = date('d/m/Y');
				$row->end_date = date('d/m/Y');
				$row->day_off = date('d/m/Y');
				$row->reason = '';
				$row->description = '';
				$row->timeshift = 'full';
				$pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->lastname_kh .' '.$row->firstname_kh. " - ".$row->lastname .' '.$row->firstname. " (" . $row->empcode . ")",'row' => $row);
			}
			$this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
	public function get_employees(){
		$biller_id  	= $this->input->get('biller_id');
		$position_id 	= $this->input->get('position_id');
		$department_id 	= $this->input->get('department_id');
		$group_id  		= $this->input->get('group_id');
		$status  		= "active";
		$employees  	= $this->hr_model->getEmployees($biller_id,$position_id,$department_id,$group_id,$status);
		echo json_encode($employees);
	}
	function index($biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
       
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employees')));
        $meta = array('page_title' => lang('employees'), 'bc' => $bc);
        $this->page_construct('hr/index', $meta, $this->data);
    }
	
	function getEmployees($biller_id = null)
    {
		$this->bpas->checkPermissions('index');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_employee") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_employee/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_employee') . "</a>";

		$action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('hr/edit_employee/$1').'" ><i class="fa fa fa-edit"></i>'.lang('edit_employee').'</a></li>
					            <li>'.$delete_link.'</li>
							</ul>
						</div>';
						
        $this->datatables
            ->select("
				hr_employees.id as id,
				empcode,
				hr_employees.finger_id,
				firstname,
				lastname,
				{$this->db->dbprefix('hr_positions')}.name as position,
				{$this->db->dbprefix('hr_departments')}.name as department,
				{$this->db->dbprefix('hr_employees')}.phone,
				{$this->db->dbprefix('hr_employees_types')}.name as employee_type,
				{$this->db->dbprefix('hr_employees_working_info')}.employee_date,				
				{$this->db->dbprefix('hr_employees_working_info')}.status")
            ->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
			->join("hr_employees_types","hr_employees_working_info.employee_type_id = hr_employees_types.id","left")
			->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
			->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left")
			->join("companies","companies.id=hr_employees_working_info.biller_id","left")
			->add_column("Actions", $action_link, "id");
			$this->datatables->where('hr_employees.candidate', 0);
			
		if ($biller_id) {
             $this->datatables->like('hr_employees_working_info.biller_id', $biller_id);
        }
		
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
           	     $this->datatables->where('hr_employees.created_by', $this->session->userdata('user_id'));
        	}
        echo $this->datatables->generate();
    }
	
	function employees_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteEmployeeByID($id);
                    }
                    $this->session->set_flashdata('message', lang("employee_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('employee');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('first_name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('last_name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('first_name_kh'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('last_name_kh'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('dob'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('gender'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('phone'));
					$this->excel->getActiveSheet()->SetCellValue('I1', lang('email'));
					$this->excel->getActiveSheet()->SetCellValue('J1', lang('employed_date'));
					$this->excel->getActiveSheet()->SetCellValue('K1', lang('department'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $employee = $this->hr_model->getEmployeeById($id);
						$employee_info = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($id);
						$department = $this->hr_model->getDepartmentById($employee_info->department_id);

						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $employee->empcode);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $employee->firstname);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $employee->lastname);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $employee->firstname_kh);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $employee->lastname_kh);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($employee->dob));
						$this->excel->getActiveSheet()->SetCellValue('G' . $row, $employee->gender);
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $employee->phone);
						$this->excel->getActiveSheet()->SetCellValue('I' . $row, $employee->email);
						$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->hrsd($employee_info->employee_date));
						$this->excel->getActiveSheet()->SetCellValue('K' . $row, $department->name);
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'employee_list_' . date('Y_m_d_H_i_s');
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
	
	function add_employee()
	{
		$this->bpas->checkPermissions('add');	
		$post = $this->input->post();
		$this->form_validation->set_rules('empcode', lang("empcode"), 'is_unique[hr_employees.empcode]');
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		$this->form_validation->set_rules('dob', lang("dob"), 'required');
		$this->form_validation->set_rules('workbook_numbe', lang("workbook_numbe"));
		$this->form_validation->set_rules('work_permit_number', lang("work_permit_number"));
		$this->form_validation->set_rules('finger_id', lang("finger_id"), 'is_unique[hr_employees.finger_id]');
		
		if ($this->form_validation->run() == true) {
			
			$data = array(
				'finger_id' 	 => $post['finger_id'],
				'nric_no' 	 	 => $post['nric_no'],
				'empcode' 		 => $post['empcode'],
				'firstname' 	 => $post['first_name'],
				'lastname' 		 => $post['last_name'],
				'firstname_kh' 	 => $post['first_name_kh'],
				'lastname_kh' 	 => $post['last_name_kh'],
				'dob' 			 => $this->bpas->fsd($post['dob']),				
				'gender' 		 => $post['gender'],
				'phone' 		 => $post['phone'],
				'email' 		 => $post['email'],
				'nationality'	 => $post['nationality'],
				'marital_status' => $post['marital_status'],
				'address' 		 => $post['address'],
				'note' 			 => $post['note'],
				'non_resident'	 => $post['non_resident'],
				'nssf'	 	     => $post['nssf'],
				'book_type' 	 => $post['book_type'],
				'workbook_number' => $post['workbook_number'],
				'work_permit_number'=>$post['work_permit_number'],
				'nssf_number'    => $post['nssf_number'],
				'type' 			 => $post['type'],
				'created_at'	 => date('Y-m-d H:i'),
				'created_by'	 => $this->session->userdata('user_id'),
			);
			// var_dump($data);
			// exit();
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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
			
			$add_employee = $this->hr_model->addEmployee($data);	
			if($add_employee){
				$this->session->set_flashdata('message', lang("employee_added"));
				admin_redirect(admin_url('hr'));
			}
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));			
			$this->data['companies'] = $this->hr_model->getCompanies();
			$this->data['departments'] = $this->hr_model->getDepartments();
			$this->data['last_employee'] = $this->hr_model->getLastEmployee();
			$bc = array(array('link' => admin_url('home'), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr'), 'page' => lang('employees')), array('link' => '#', 'page' => lang('add_employee')));
			$meta = array('page_title' => lang('add_employee'), 'bc' => $bc);
			$this->page_construct('hr/add_employee', $meta, $this->data);
		}
	}

	function edit_employee($id = false)
	{
		$this->bpas->checkPermissions('edit');	
		$post = $this->input->post();
		if(isset($post['working_info_id'])){
			$additions = null;
			if(isset($_POST['addition']) && $_POST['addition']){
				$additions = json_encode($_POST['addition']);
			}
			$deductions = null;
			if(isset($_POST['deduction']) && $_POST['deduction']){
				$deductions = json_encode($_POST['deduction']);
			}

			$data = array(
				'biller_id' => $post['biller_id'],
				'employee_id' => $id,
				'department_id' => $post['department_id'],
				'group_id' => $post['group_id'],
				'position_id' => $post['position_id'],
				'policy_id' => $post['policy_id'],
				'employee_date' => $this->bpas->fld($post['employee_date']),
				'employee_type_id' => $post['employee_type'],
				//'resigned_date' => $this->bpas->fld($post['resigned_date']),
				'monthly_rate' => $post['monthly_rate'],
				'currency' => $post['currency'],
				'net_salary' => $post['net_salary'],
				'self_tax' => $post['self_tax'],
				'salary_tax' => $post['salary_tax'],
				'absent_rate' => $post['absent_rate'],
				'late_early_rate' => $post['late_early'],
				'permission_rate' => $post['permission_rate'],
				'tax_rate' => $post['tax_rate'],
				'normal_ot_rate' => $post['normal_ot'],
				'weekend_ot_rate' => $post['weekend_ot'],
				'holiday_ot_rate' => $post['holiday_ot'],
				'additions' => $additions,
				'deductions' => $deductions,
				'status' => $post['status'],
				'kpi_type' => $post['kpi_type'],
				'annual_leave' => $post['annual_leave'],
				'special_leave' => $post['special_leave'],
				'sick_leave' 		=> $post['sick_leave'],
				'other_leave' 		=> $post['other_leave'],
				'no_seniority' 		=> $post['no_seniority'],
				'attendance_bonus' => $post['attendance_bonus'],
				'special_bonus' 	=> $post['special_bonus'],
				'tra_acc_allowance' => $post['tra_acc_allowance'],
				'union' 			=> $post['union'],
				'pension' 			=> $post['pension'],
				'payment_type' 		=> $post['payment_type'],
				'salary_two_time' 	=> $this->input->post('salary_two_time'),
				'employee_level' 	=> $this->input->post('employee_level'),
				
			);
			if($this->hr_model->addEmployeesWorkingInfo($id, $data)){
				if($this->input->post("update_close")){
					$this->session->set_flashdata('message', lang("employee_updated"));
					admin_redirect('hr/');
				}else{
					$this->session->set_flashdata('message', lang("employee_updated"));
					admin_redirect('hr/edit_employee/'.$id."#working");
				}
			}
		}
		$employee_details = $this->hr_model->getEmployeeById($id);	
		if ($post && $post['empcode'] != $employee_details->empcode) {
			$this->form_validation->set_rules('empcode', lang("empcode"), 'is_unique[hr_employees.empcode]');
        }
		if ($post && $post['finger_id'] != $employee_details->finger_id) {
			$this->form_validation->set_rules('finger_id', lang("finger_id"), 'is_unique[hr_employees.finger_id]');
        }
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		// $this->form_validation->set_rules('workbook_numbe', lang("workbook_numbe"));
		// $this->form_validation->set_rules('work_permit_number', lang("work_permit_number"));
		if ($this->form_validation->run() == true) {
			$data = array(
				'finger_id' 	 => $post['finger_id'],
				'nric_no' 	 	 => $post['nric_no'],
				'empcode' 		 => $post['empcode'],
				'firstname' 	 => $post['first_name'],
				'lastname' 		 => $post['last_name'],
				'firstname_kh' 	 => $post['first_name_kh'],
				'lastname_kh' 	 => $post['last_name_kh'],
				'dob' 			 => $this->bpas->fsd($post['dob']),	
				'retirement'     => $this->bpas->fsd($post['retirement']),				
				'gender' 		 => $post['gender'],
				'phone' 		 => $post['phone'],
				'email' 		 => $post['email'],
				'nationality'	 => $post['nationality'],
				'marital_status' => $post['marital_status'],
				'non_resident'	 => $post['non_resident'],
				'nssf'	 		 => $post['nssf'],
				'nssf_number'    => $post['nssf_number'],
				'book_type' 	 => $post['book_type'],
				'workbook_number' => $post['workbook_number'],
				'work_permit_number'=>$post['work_permit_number'],
				'type' 			 => $post['type'],
				'address' 		 => $post['address'],
				'note' 			 => $post['note'],
				'updated_at'	 => date('Y-m-d H:i'),
				'updated_by'	 => $this->session->userdata('user_id'),
			);
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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
                $config['width'] = 150;
                $config['height'] = 150;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
				if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
		}
		if ($this->form_validation->run() == true && $this->hr_model->updateEmployee($id, $data)) {
			if($this->input->post("update_close")){
				$this->session->set_flashdata('message', lang("employee_updated"));
				admin_redirect('hr/');
			}else{
				$this->session->set_flashdata('message', lang("employee_updated"));
				admin_redirect('hr/edit_employee/'.$id);
			}
		}else{
			$this->data['error'] 		= (validation_errors() ? validation_errors() : $this->session->flashdata('error'));			
			$this->data['id'] 			= $id;
			$this->data['row'] 			= $this->hr_model->getEmployeeById($id);
			$this->data['emp_user'] 	= $this->hr_model->getEmployeeByIdemp($this->data['row']->id);
			$this->data['working_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($id);
			$this->data['companies'] 	= $this->hr_model->getCompanies();
			$this->data['currencies'] 	= $this->site->getAllCurrencies();
			$this->data['additions'] 	= $this->hr_model->getAllAdditions();
			$this->data['deductions'] 	= $this->hr_model->getAllDeductions();
			$this->data['policies'] 	= $this->hr_model->getPolicies();
			$this->data['types'] 		= $this->hr_model->getEmployeeTypes();
			$this->data['kpi_types'] 	= $this->hr_model->getKPITypes();
			$this->data['departments'] 	= $this->hr_model->getDepartments();
			$this->data['zones']      	= $this->site->getAllZones_Order_Group();
			$this->data['groups']     	= $this->ion_auth->groups()->result_array();
            $this->data['employee_levels']     = $this->hr_model->getNestedByCategories();

			$user = $this->site->getUser($this->session->userdata('user_id'));
			if ($this->Settings->multi_biller) {
			$this->data['billers'] = (($this->Admin || $this->Owner) || !$user->multi_biller) ? $this->site->getAllCompanies('biller') : $this->site->getMultiBillerByID($user->multi_biller);
			} else {
			$this->data['billers'] = (($this->Admin || $this->Owner) || !$user->biller_id) ? $this->site->getAllCompanies('biller') : $this->site->getMultiBillerByID($user->biller_id);
			}
			$this->data['warehouses']  = (($this->Admin || $this->Owner) || !$user->warehouse_id) ? $this->site->getAllWarehouses() : $this->site->getMultiWarehouseByID($user->warehouse_id);
			$user = $this->ion_auth->user($id)->row();			
			$bc = array(array('link' => admin_url('home'), 'page' => lang('home')),  array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr'), 'page' => lang('employees')), array('link' => '#', 'page' => lang('edit_employee')));
			$meta = array('page_title' => lang('edit_employee'), 'bc' => $bc);
			$this->page_construct('hr/edit_employee', $meta, $this->data);
		}
	}

	public function add_family_info($employee_id = false)
	{
		$this->form_validation->set_rules('f_first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('f_last_name', lang("last_name"), 'required');
		$this->form_validation->set_rules('f_relationship', lang("relationship"), 'required');
		if ($this->form_validation->run() == true){	
			$f_first_name = $this->input->post('f_first_name');
			$f_last_name = $this->input->post('f_last_name');
			$f_occupation = $this->input->post('f_occupation');
			$f_relationship = $this->input->post('f_relationship');
			$f_telephone = $this->input->post('f_telephone');
			$f_dob = $this->input->post('f_dob');
			$f_pob = $this->input->post('f_pob');
			$f_address = $this->input->post('f_address');
			$data = array(
					'firstname' => $f_first_name,
					'lastname' => $f_last_name,
					'occupation' => $f_occupation,
					'relationship' => $f_relationship,
					'telephone' => $f_telephone,
					'dob' => $this->bpas->fld($f_dob),
					'pob' => $f_pob,
					'address' => $f_address,
					'employee_id' => $employee_id
				);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->addFamily($data)) {
			$this->session->set_flashdata('message', $this->lang->line("family_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#family");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['relationships'] = $this->hr_model->getAllRelationShips();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_family_info', $this->data);	
		}	
	}
	
	public function edit_family_info($id = false)
	{
		$this->form_validation->set_rules('f_first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('f_last_name', lang("last_name"), 'required');
		$this->form_validation->set_rules('f_relationship', lang("relationship"), 'required');
		$family = $this->hr_model->getFamilyByID($id);
		if ($this->form_validation->run() == true){	
			$f_first_name = $this->input->post('f_first_name');
			$f_last_name = $this->input->post('f_last_name');
			$f_occupation = $this->input->post('f_occupation');
			$f_relationship = $this->input->post('f_relationship');
			$f_telephone = $this->input->post('f_telephone');
			$f_dob = $this->input->post('f_dob');
			$f_pob = $this->input->post('f_pob');
			$f_address = $this->input->post('f_address');
			$data = array(
					'firstname' => $f_first_name,
					'lastname' => $f_last_name,
					'occupation' => $f_occupation,
					'relationship' => $f_relationship,
					'telephone' => $f_telephone,
					'dob' => $this->bpas->fld($f_dob),
					'pob' => $f_pob,
					'address' => $f_address
				);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateFamily($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("family_updated"));
            admin_redirect("hr/edit_employee/".$family->employee_id."/#family");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $family;
			$this->data['relationships'] = $this->hr_model->getAllRelationShips();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_family_info', $this->data);	
		}	
	}
	
	public function getFamilyInfo($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_family") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_family/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_family') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_family_info/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_family').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_employees_family.id as id,
					concat(lastname,' ',firstname) as name,
					hr_employees_family.occupation,
					hr_employees_family.dob,
					hr_employees_relationship.name as relationship,
					hr_employees_family.telephone,
					hr_employees_family.pob,
					hr_employees_family.address")
            ->from("hr_employees_family")
			->join("hr_employees_relationship","hr_employees_relationship.id=relationship","left")
			->where("hr_employees_family.employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function delete_family($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteFamily($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('family_deleted')]);
				}
				$this->session->set_flashdata('message', lang('family_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function delete_employee($id = null)
    {		
		$this->bpas->checkPermissions('delete');
        if (isset($id) || $id != null){
        	$delete = $this->db->where("id",$id)->delete("hr_employees");
        	if($delete){
        		$this->session->set_flashdata('message', lang("employee_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
            else{
            	admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
	
	public function departments($biller_id = null)
	{	
		$this->bpas->checkPermissions('departments');	
		$this->data['modal_js'] = $this->site->modal_js();
		if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('departments')));
		$meta = array('page_title' => lang('departments'), 'bc' => $bc);
		$this->page_construct('hr/departments', $meta, $this->data);
	}

	public function getDepartments($biller_id = null, $parent_id = NULL)
	{	
		$this->bpas->checkPermissions('departments');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_department") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_department/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_department') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_department/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_department').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_departments.id as id, 
					hr_departments.code,
					hr_departments.name,
					hr_departments.description")
            ->from("hr_departments")
			->join("companies","hr_departments.biller_id=companies.id","left")
            ->add_column("Actions", $action_link, "id");
			if ($biller_id) {
				 $this->datatables->like('companies.id', $biller_id);
			}
        echo $this->datatables->generate();
	}

	public function add_department()
	{
		$this->bpas->checkPermissions('departments');	
		$post = $this->input->post();
		$this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_departments.code]');
		$this->form_validation->set_rules('biller_id', lang("biller"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'biller_id'  => $post['biller_id'],
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->hr_model->addDepartment($data)) {
            $this->session->set_flashdata('message', $this->lang->line("department_added"));
            admin_redirect("hr/departments");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = isset($id)? $id: '';
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_department', $this->data);	
		}	
	}
	
	public function edit_department($id = null)
	{		
		$this->bpas->checkPermissions('departments');	
		$post = $this->input->post();		
		$department_detail = $this->hr_model->getDepartmentById($id);	
		if ($post && $post['code'] != $department_detail->code) {
			$this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_departments.code]');
        }
		$this->form_validation->set_rules('biller_id', lang("biller"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');

		if ($this->form_validation->run() == true) 
		{						
			$data = array(
				'biller_id'  => $post['biller_id'],
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

		if ($this->form_validation->run() == true && $id = $this->hr_model->updateDepartment($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("department_updated"));
            admin_redirect("hr/departments");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $this->hr_model->getDepartmentById($id);
			
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_department', $this->data);
		}			
	}
	
	public function delete_department($id = null)
    {	
		$this->bpas->checkPermissions('departments');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_departments");
        	if($result){
        		$this->session->set_flashdata('message', lang("department_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function department_actions()
    {
		
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('department-delete');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteDepartmentByID($id);
                    }
                    $this->session->set_flashdata('message', lang("department_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('departments');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $department = $this->hr_model->getDepartmentById($id);
						$biller = $this->site->getCompanyByID($department->biller_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $biller->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $department->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $department->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $department->description);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'department_list_' . date('Y_m_d_H_i_s');
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
	
	public function groups()
	{	
		$this->bpas->checkPermissions('groups');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('groups')));
		$meta = array('page_title' => lang('groups'), 'bc' => $bc);
		$this->page_construct('hr/group', $meta, $this->data);
	}

	public function getGroups($parent_id = NULL)
	{	
		$this->bpas->checkPermissions('groups');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_group") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_group/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_group') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_group/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_group').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_groups.id as id, 
					hr_departments.name as department, 
					hr_groups.code,
					hr_groups.name,
					hr_groups.description")
            ->from("hr_groups")
			->join("hr_departments","hr_groups.department_id=hr_departments.id","left")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_group()
	{
		$this->bpas->checkPermissions('groups');	
		$post = $this->input->post();
		$this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_departments.code]');
		$this->form_validation->set_rules('department', lang("department"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'department_id'  => $post['department'],
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->hr_model->addGroup($data)) {
            $this->session->set_flashdata('message', $this->lang->line("group_added"));
            admin_redirect("hr/groups");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = isset($id)? $id: '';
			$this->data['departments'] = $this->hr_model->getDepartments();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_group', $this->data);	
		}	
	}
	
	public function edit_group($id = null)
	{		
		$this->bpas->checkPermissions('groups');
		$post = $this->input->post();		
		$group_info = $this->hr_model->getGroupById($id);	
		if ($post && $post['code'] != $group_info->code) {
			$this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_groups.code]');
        }
		$this->form_validation->set_rules('department', lang("department"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');

		if ($this->form_validation->run() == true) 
		{						
			$data = array(
				'department_id'  => $post['department'],
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

		if ($this->form_validation->run() == true && $id = $this->hr_model->updateGroup($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("group_updated"));
            admin_redirect("hr/groups");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $group_info;
			$this->data['departments'] = $this->hr_model->getDepartments();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_group', $this->data);
		}			
	}
	
	public function delete_group($id = null)
    {	
		$this->bpas->checkPermissions('groups');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_groups");
        	if($result){
        		$this->session->set_flashdata('message', lang("group_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function group_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('group-delete');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteGroupByID($id);
                    }
                    $this->session->set_flashdata('message', lang("group_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('group');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('department'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $group = $this->hr_model->getGroupById($id);
						$department = $this->hr_model->getDepartmentById($group->department_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $department->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $group->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $group->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $group->description);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'group_list_' . date('Y_m_d_H_i_s');
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
	
	public function positions($biller_id =null)
	{	
		$this->bpas->checkPermissions('positions');	
		$this->data['modal_js'] = $this->site->modal_js();
		if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('positions')));
		$meta = array('page_title' => lang('positions'), 'bc' => $bc);
		$this->page_construct('hr/position', $meta, $this->data);
	}

	public function getPositions($biller_id =null, $parent_id = NULL)
	{	
		$this->bpas->checkPermissions('positions');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_position") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_position/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_position') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_position/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_position').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_positions.id as id, 
					hr_positions.code,
					hr_positions.name,
					hr_positions.description")
            ->from("hr_positions")
			->join("companies","hr_positions.biller_id=companies.id","left")
            ->add_column("Actions", $action_link, "id");
			if ($biller_id) {
				 $this->datatables->like('companies.id', $biller_id);
			}
			
        echo $this->datatables->generate();
	}

	public function add_position()
	{
		$this->bpas->checkPermissions('positions');	
		$post = $this->input->post();
		$this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_positions.code]');
		$this->form_validation->set_rules('biller_id', lang("biller"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'biller_id'  => $post['biller_id'],
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->hr_model->addPosition($data)) {
            $this->session->set_flashdata('message', $this->lang->line("position_added"));
            admin_redirect("hr/positions");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = isset($id)? $id: '';
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_position', $this->data);	
		}	
	}
	
	public function edit_position($id = null)
	{		
		$this->bpas->checkPermissions('positions');	
		$post = $this->input->post();		
		$position_info = $this->hr_model->getPositionById($id);	
		if ($post && $post['code'] != $position_info->code) {
			$this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_positions.code]');
        }
		$this->form_validation->set_rules('biller_id', lang("biller"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');

		if ($this->form_validation->run() == true) 
		{						
			$data = array(
				'biller_id'  => $post['biller_id'],
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

		if ($this->form_validation->run() == true && $id = $this->hr_model->updatePosition($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("position_updated"));
            admin_redirect("hr/positions");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $position_info;
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_position', $this->data);
		}			
	}
	
	public function delete_position($id = null)
    {	
		$this->bpas->checkPermissions('positions');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_positions");
        	if($result){
        		$this->session->set_flashdata('message', lang("position_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function position_actions()
    {
		
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('position-delete');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deletePositionByID($id);
                    }
                    $this->session->set_flashdata('message', lang("position_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('position');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $position = $this->hr_model->getPositionById($id);
						$biller = $this->site->getCompanyByID($position->biller_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $biller->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $position->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $position->name);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $position->description);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'position_list_' . date('Y_m_d_H_i_s');
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
	
	public function get_dep_pos()
	{
		$id = $this->input->get("biller");
		$department_id = $this->input->get("department");
		$position_id = $this->input->get("position");
		
		$departments = $this->hr_model->getDepartmentByBiller($id);
		$department_opt = "<option value=''>".lang('select')." ".lang('department')."</option>";
		if($departments){
			foreach($departments as $department){
				if($department_id == $department->id){
					$department_opt .="<option value='".$department->id."' selected>".$department->name."</option>";
				}else{
					$department_opt .="<option value='".$department->id."'>".$department->name."</option>";
				}
			}
		}
		
		$positions = $this->hr_model->getPositionByBiller($id);
		$position_opt = "<option value=''>".lang('select')." ".lang('position')."</option>";
		if($positions){
			foreach($positions as $position){
				if($position_id == $position->id){
					$position_opt .="<option value='".$position->id."' selected>".$position->name."</option>";
				}else{
					$position_opt .="<option value='".$position->id."'>".$position->name."</option>";
				}
			}
		}		
		echo json_encode(array("department_opt" => $department_opt,"position_opt" => $position_opt));
	}
	
	public function get_group()
	{
		$department = $this->input->get("department_id");
		$group_id = $this->input->get("group");
		$groups = $this->hr_model->getGroupByDepartment($department);
		$group_opt = "<option value=''>".lang('select')." ".lang('group')."</option>";
		if($groups){
			foreach($groups as $group){
				if($group_id==$group->id){
					$group_opt .="<option value='".$group->id."' selected>".$group->name."</option>";
				}else{
					$group_opt .="<option value='".$group->id."'>".$group->name."</option>";
				}
			}
		}
		
		$employee_id = $this->input->get("employee");
		$employees = $this->hr_model->getEmployeesByDepartment($department);
		$emp_opt = "<option value=''>".lang('select')." ".lang('employee')."</option>";
		if($employees){
			foreach($employees as $employee){
				if($employee_id==$employee->employee_id){
					$emp_opt .="<option value='".$employee->employee_id."' selected>".$employee->lastname .' '. $employee->firstname."</option>";
				}else{
					$emp_opt .="<option value='".$employee->employee_id."'>".$employee->lastname .' '. $employee->firstname."</option>";
				}
			} 
		}
		echo json_encode(array("group_opt" => $group_opt, "emp_opt"=>$emp_opt));
	}
	
	public function getQualification($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_qualification") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_qualification/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_qualification') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_qualification/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_qualification').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					id,
					certificate,
					major,
					school,
					degree,
					start_date,
					end_date,
					language,
					description,
					attachment")
            ->from("hr_employees_qualification")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_qualification($employee_id = false)
	{
		$this->form_validation->set_rules('q_certificate', lang("certificate"), 'required');
		$this->form_validation->set_rules('q_major', lang("major"), 'required');
		$this->form_validation->set_rules('q_school', lang("school"), 'required');
		
		if ($this->form_validation->run() == true) 
		{	
			$q_certificate 	= $this->input->post('q_certificate');
			$q_major  		= $this->input->post('q_major');
			$q_school 		= $this->input->post('q_school');
			$q_degree 		= $this->input->post('q_level');
			$q_start_date 	= $this->input->post('q_start_date');
			$q_end_date		= $this->input->post('q_end_date');
			$q_language		= $this->input->post('q_language');
			$q_description  = $this->input->post('q_description');
		
			$data = array(
				'certificate'  => $q_certificate,
				'major'        => $q_major,
				'school'       => $q_school,		
			    'degree'       => $q_degree,		
			    'start_date'   => $this->bpas->fld($q_start_date),
			    'end_date'     => $this->bpas->fld($q_end_date),
			    'language'     => $q_language,
			    'description'  => $q_description,
				'employee_id'  => $employee_id
			);
			
			if ($_FILES['q_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('q_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->addQualification($data)) {
			$this->session->set_flashdata('message', $this->lang->line("qualification_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#qualification");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_qualification', $this->data);	
		}	
	}
	
	public function edit_qualification($id = false)
	{
		$this->form_validation->set_rules('q_certificate', lang("certificate"), 'required');
		$this->form_validation->set_rules('q_major', lang("major"), 'required');
		$this->form_validation->set_rules('q_school', lang("school"), 'required');
		
		$qualification= $this->hr_model->getQualificationByID($id);
		if ($this->form_validation->run() == true) 
		{	
			$q_certificate 	= $this->input->post('q_certificate');
			$q_major  		= $this->input->post('q_major');
			$q_school 		= $this->input->post('q_school');
			$q_degree 		= $this->input->post('q_level');
			$q_start_date 	= $this->input->post('q_start_date');
			$q_end_date		= $this->input->post('q_end_date');
			$q_language		= $this->input->post('q_language');
			$q_description  = $this->input->post('q_description');
		
			$data = array(
				'certificate'  => $q_certificate,
				'major'        => $q_major,
				'school'       => $q_school,		
			    'degree'       => $q_degree,		
			    'start_date'   => $this->bpas->fld($q_start_date),
			    'end_date'     => $this->bpas->fld($q_end_date),
			    'language'     => $q_language,
			    'description'  => TRIM($q_description)
			);
			
			if ($_FILES['q_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('q_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateQualification($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("qualification_updated"));
            admin_redirect("hr/edit_employee/".$qualification->employee_id."/#qualification");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $qualification;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_qualification', $this->data);	
		}	
	}
	
	public function delete_qualification($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteQualification($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('qualification_deleted')]);
					
				}
				$this->session->set_flashdata('message', lang('qualification_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function getWorkingHistory($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_working_history") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_working_history/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_working_history') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_working_history/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_working_history').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					id,
					company,
					position,
					start_date,
					end_date,
					description,
					attachment")
            ->from("hr_employees_working_history")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_working_history($employee_id = false)
	{
		$this->form_validation->set_rules('w_company', lang("company"), 'required');
		$this->form_validation->set_rules('w_position', lang("position"), 'required');
		
		if ($this->form_validation->run() == true) 
		{	
			$w_company		= $this->input->post('w_company');
			$w_position  	= $this->input->post('w_position');
			$w_start_date 	= $this->input->post('w_start_date');
			$w_end_date 	= $this->input->post('w_end_date');
			$w_description	= $this->input->post('w_description');
		
			$data = array(
				'company'  	   => $w_company,
				'position'     => $w_position,
				'start_date'   => $this->bpas->fld($w_start_date),		
			    'end_date'     => $this->bpas->fld($w_end_date),		
			    'description'  => $q_description,
				'employee_id'  => $employee_id
			);
			
			if ($_FILES['w_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('w_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->addWorkingHistory($data)) {
			$this->session->set_flashdata('message', $this->lang->line("working_history_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#work");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_working_history', $this->data);	
		}	
	}
	
	public function edit_working_history($id = false)
	{
		$this->form_validation->set_rules('w_company', lang("company"), 'required');
		$this->form_validation->set_rules('w_position', lang("position"), 'required');
		$work = $this->hr_model->getWorkingHistoryByID($id);
		if ($this->form_validation->run() == true) 
		{	
			$w_company		= $this->input->post('w_company');
			$w_position  	= $this->input->post('w_position');
			$w_start_date 	= $this->input->post('w_start_date');
			$w_end_date 	= $this->input->post('w_end_date');
			$w_description	= $this->input->post('w_description');
		
			$data = array(
				'company'  	   => $w_company,
				'position'     => $w_position,
				'start_date'   => $this->bpas->fld($w_start_date),		
			    'end_date'     => $this->bpas->fld($w_end_date),		
			    'description'  => $w_description
			);
			
			if ($_FILES['w_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('w_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateWorkingHistory($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("working_history_updated"));
            admin_redirect("hr/edit_employee/".$work->employee_id."/#work");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $work;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_working_history', $this->data);	
		}	
	}
	
	public function delete_working_history($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteWorkingHistory($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('working_history_deleted')]);
					
				}
				$this->session->set_flashdata('message', lang('working_history_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function getBankAccounts($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_bank_account") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_bank_account/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_bank_account') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_bank_account/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_bank_account').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					id,
					bank_account,
					account_no,
					account_name,
					account_type,
					account_no,
					account_currency,
					date_opened,
					date_issued,
					description")
            ->from("hr_employees_bank")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function delete_bank_account($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteBankAccount($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('bank_account_deleted')]);
				
				}
				$this->session->set_flashdata('message', lang('bank_account_deleted'));
				admin_redirect('welcome');
			}
        }
    }

	public function add_bank_account($employee_id = false)
	{
		$this->form_validation->set_rules('account', lang("account"), 'required');
		$this->form_validation->set_rules('account_no', lang("account_no"), 'required');
		
		if ($this->form_validation->run() == true) 
		{	
			$account			 = $this->input->post('account');
			$account_no			 = $this->input->post('account_no');
			$account_name		 = $this->input->post('account_name');
			$account_type		 = $this->input->post('account_type');
			$currency			 = $this->input->post('currency');
			$date_opened		 = $this->input->post('date_open');
			$date_issued		 = $this->input->post('date_issue');
			$b_description 		 = $this->input->post('b_description');
			
			$data = array(
					'bank_account'		=> $account,	 
					'account_no'		=> $account_no,	  
					'account_name'		=> $account_name,		
					'account_type'		=> $account_type,
					'account_currency'	=> $currency,			
					'date_opened'		=> $this->bpas->fld($date_opened),		
					'date_issued'		=> $this->bpas->fld($date_issued),		
					'description'		=> $b_description,
					'employee_id'		=> $employee_id,
				);
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->addBankAccount($data)) {
			$this->session->set_flashdata('message', $this->lang->line("bank_account_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#bank");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_bank_account', $this->data);	
		}	
	}
	
	public function edit_bank_account($id = false)
	{
		$this->form_validation->set_rules('account', lang("account"), 'required');
		$this->form_validation->set_rules('account_no', lang("account_no"), 'required');
		
		$bank = $this->hr_model->getBankAccountByID($id);
		
		if ($this->form_validation->run() == true) 
		{	
			$account			 = $this->input->post('account');
			$account_no			 = $this->input->post('account_no');
			$account_name		 = $this->input->post('account_name');
			$account_type		 = $this->input->post('account_type');
			$currency			 = $this->input->post('currency');
			$date_opened		 = $this->input->post('date_opened');
			$date_issued		 = $this->input->post('date_issued');
			$b_description 		 = $this->input->post('b_description');
			
			$data = array(
					'bank_account'		=> $account,	 
					'account_no'		=> $account_no,	  
					'account_name'		=> $account_name,		
					'account_type'		=> $account_type,
					'account_currency'	=> $currency,			
					'date_opened'		=> $this->bpas->fld($date_opened),		
					'date_issued'		=> $this->bpas->fld($date_issued),		
					'description'		=> $b_description,
				);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateBankAccount($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("bank_account_updated"));
            admin_redirect("hr/edit_employee/".$bank->employee_id."/#bank");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $bank;
 			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_bank_account', $this->data);	
		}	
	}
	
	public function getContract($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_contract") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_contract/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_contract') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_contract/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_contract').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees_contract.id as id,
					hr_employees_contract.contract_title,
					hr_employees_types.name,
					start_date,
					end_date,
					description,
					attachment")
            ->from("hr_employees_contract")
			->join("hr_employees_types","hr_employees_types.id=employee_type_id","left")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_contract($employee_id = false)
	{
		$this->form_validation->set_rules('c_title', lang("title"), 'required');
		$this->form_validation->set_rules('c_type', lang("type"), 'required');
		
		if ($this->form_validation->run() == true) 
		{	
			$c_title		= $this->input->post('c_title');
			$c_type_id		= $this->input->post('c_type');
			$c_start_date	= $this->input->post('c_start_date');
			$c_end_date		= $this->input->post('c_end_date');
			$c_description 	= $this->input->post('c_description');
			
			$data = array(
					'contract_title'	=> $c_title,
					'employee_type_id'	=> $c_type_id,
					'start_date'		=> $this->bpas->fld($c_start_date),	  
					'end_date'			=> $this->bpas->fld($c_end_date),		
					'description'		=> $c_description,
					'employee_id'		=> $employee_id,
					'basic_salary'		=> $this->input->post('c_basic_salary'),
					'severance'			=> $this->input->post('c_severance'),
					'contract_type'		=> $this->input->post('contract_type')
				);
				
			if ($_FILES['c_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('c_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->addContract($data)) {
			$this->session->set_flashdata('message', $this->lang->line("contract_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#contract");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['types'] = $this->hr_model->getEmployeeTypes();
			$this->data['employee_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($employee_id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_contract', $this->data);	
		}	
	}
	
	public function edit_contract($id = false)
	{
		$this->form_validation->set_rules('c_title', lang("title"), 'required');
		$this->form_validation->set_rules('c_type', lang("type"), 'required');
		$contract = $this->hr_model->getContractByID($id);
		
		if ($this->form_validation->run() == true) 
		{	
			$c_title		= $this->input->post('c_title');
			$c_type_id		= $this->input->post('c_type');
			$c_start_date	= $this->input->post('c_start_date');
			$c_end_date		= $this->input->post('c_end_date');
			$c_description 	= $this->input->post('c_description');
			
			$data = array(
					'contract_title'	=> $c_title,
					'employee_type_id'	=> $c_type_id,
					'start_date'		=> $this->bpas->fld($c_start_date),	  
					'end_date'			=> $this->bpas->fld($c_end_date),		
					'description'		=> $c_description,
					'basic_salary'		=> $this->input->post('c_basic_salary'),
					'severance'			=> $this->input->post('c_severance'),
					'contract_type'		=> $this->input->post('contract_type')
				);
				
			if ($_FILES['c_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('c_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateContract($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("contract_updated"));
            admin_redirect("hr/edit_employee/".$contract->employee_id."/#contract");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $contract;
			$this->data['employee_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($contract->employee_id);
			$this->data['types'] = $this->hr_model->getEmployeeTypes();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_contract', $this->data);	
		}	
	}
	
	public function delete_contract($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteContract($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('contract_deleted')]);
				}
				$this->session->set_flashdata('message', lang('contract_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function getEmergencyContact($employee_id = false)
	{
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_emergency_contact") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_emergency_contact/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_emergency_contact') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_emergency_contact/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_emergency_contact').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees_emergency.id as id,
					hr_employees_emergency.name,
					hr_employees_relationship.name as relationship,
					hr_employees_emergency.telephone")
            ->from("hr_employees_emergency")
			->join("hr_employees_relationship","hr_employees_relationship.id=relationship","left")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function delete_emergency_contact($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteEmergencyContact($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('emergency_contact_deleted')]);
				}
				$this->session->set_flashdata('message', lang('emergency_contact_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function add_emergency_contact($employee_id = false)
	{
		$this->form_validation->set_rules('e_name', lang("name"), 'required');
		$this->form_validation->set_rules('e_relationship', lang("relationship"), 'required');
		$this->form_validation->set_rules('e_phone', lang("telephone"), 'required');
		
		if ($this->form_validation->run() == true){	
			$e_name			= $this->input->post('e_name');
			$e_relationship	= $this->input->post('e_relationship');
			$e_phone 		= $this->input->post('e_phone');
			$data = array(
					'name'			=> $e_name,
					'relationship'	=> $e_relationship,
					'telephone'		=> $e_phone,
					'employee_id'	=> $employee_id,
				);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->addEmergencyContact($data)) {
			$this->session->set_flashdata('message', $this->lang->line("emergency_contact_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#emergency");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['relationships'] = $this->hr_model->getAllRelationShips();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_emergency_contact', $this->data);	
		}	
	}
	
	public function edit_emergency_contact($id = false)
	{
		$this->form_validation->set_rules('e_name', lang("name"), 'required');
		$this->form_validation->set_rules('e_relationship', lang("relationship"), 'required');
		$this->form_validation->set_rules('e_phone', lang("telephone"), 'required');
		
		$emergency = $this->hr_model->getEmergencyContactByID($id);
		if ($this->form_validation->run() == true){	
			$e_name			= $this->input->post('e_name');
			$e_relationship	= $this->input->post('e_relationship');
			$e_phone 		= $this->input->post('e_phone');
			$data = array(
					'name'			=> $e_name,
					'relationship'	=> $e_relationship,
					'telephone'		=> $e_phone,
				);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateEmergencyContact($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("emergency_contact_updated"));
            admin_redirect("hr/edit_employee/".$emergency->employee_id."/#emergency");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $emergency;
			$this->data['relationships'] = $this->hr_model->getAllRelationShips();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_emergency_contact', $this->data);	
		}	
	}
	
	public function getDocuments($employee_id = false)
	{
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_document") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_document/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_document') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_document/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_document').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees_document.id as id,
					hr_employees_document.name,
					description,
					created_date,
					hr_employees_document.expired_date,
					CONCAT({$this->db->dbprefix('users')}.last_name,' ',{$this->db->dbprefix('users')}.first_name) as created_by,
					attachment")
            ->from("hr_employees_document")
			->join("users","users.id=created_by","left")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_document($employee_id = false)
	{
		$this->form_validation->set_rules('d_name', lang("name"), 'required');
		
		if ($this->form_validation->run() == true) 
		{	
			$d_name			= $this->input->post('d_name');
			$d_description	= $this->input->post('d_description');
			
			$data = array(
					'name'			=> $d_name,
					'description'	=> $d_description,
					'created_by'	=> $this->session->userdata("user_id"),
					'created_date'	=> date("Y-m-d H:i"),
					'employee_id'	=> $employee_id,
					'expired_date'  => $this->input->post('expired_date') ? $this->bpas->fld($this->input->post('expired_date')):null,
				);
			
			if ($_FILES['d_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('d_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->addDocument($data)) {
			$this->session->set_flashdata('message', $this->lang->line("document_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#document");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_document', $this->data);	
		}	
	}
	
	public function delete_document($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteDocument($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('document_deleted')]);
				}
				$this->session->set_flashdata('message', lang('document_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function edit_document($id = false)
	{
		$this->form_validation->set_rules('d_name', lang("name"), 'required');
		$document = $this->hr_model->getDocumentByID($id);
		if ($this->form_validation->run() == true) 
		{	
			$d_name			= $this->input->post('d_name');
			$d_description	= $this->input->post('d_description');
			
			$data = array(
					'name'			=> $d_name,
					'description'	=> $d_description,
					'expired_date'  => $this->input->post('expired_date') ? $this->bpas->fld($this->input->post('expired_date')):null,
				);
			
			if ($_FILES['d_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('d_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateDocument($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("document_added"));
            admin_redirect("hr/edit_employee/".$document->employee_id."/#document");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $document;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_document', $this->data);	
		}	
	}
	public function expired_document($id = false)
	{
		$this->bpas->checkPermissions('expired_document');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employee_types')));
		$meta = array('page_title' => lang('employee_types'), 'bc' => $bc);
		$this->page_construct('hr/expired_document', $meta, $this->data);
		
	}
	public function getExpiredDocuments()
	{
		$this->load->library('datatables');
		$expiry_alert_days    = $this->Settings->expiry_alert_days;
        $settings_expiry_date = date('Y-m-d', strtotime(" +{$expiry_alert_days} days "));

		$delete_link = "<a href='#' class='po' title='" . lang("delete_document") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_document/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_document') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_document/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_document').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees_document.id as id,
					hr_employees_document.name,
					description,
					created_date,
					hr_employees_document.expired_date,
					CONCAT({$this->db->dbprefix('users')}.last_name,' ',{$this->db->dbprefix('users')}.first_name) as created_by")
            ->from("hr_employees_document")
			->join("users","users.id=created_by","left");
			if ($settings_expiry_date) {
                    $this->db->where($this->db->dbprefix('hr_employees_document') . '.expired_date <=', $settings_expiry_date);
                }
			//->where("hr_employees_document.expired_date is not NULL")
			//->where("expired_date !=",'0000-00-00')
			$this->datatables->unset_column("id");
        echo $this->datatables->generate();
	}
	public function salary_tax()
	{
		$data 		  = array();
		$currency 	  = $this->site->getCurrencyByCode("KHR");
		$monthly_rate = $this->input->get("monthly_rate");
		$net_salary   = $this->input->get("net_salary");
		$salary_tax   = ($this->input->get("salary_tax") * $currency->rate);
		
		$employee_id  = $this->input->get("employee_id");
		$employee 	  = $this->hr_model->getEmployeeById($employee_id);
		$salary_taxs  = $this->hr_model->getSalaryTaxCondition();
		$spouses 	  = $this->hr_model->getSpouseMemberByEmployeeID($employee_id);
		$childs 	  = $this->hr_model->getChildrenMemberByEmployeeID($employee_id);
		
		foreach($salary_taxs as $tax){
			if($employee->non_resident==0){
				
				$allowance 		 = (($spouses?count($spouses):0) + ($childs?count($childs):0)) * 150000;
				$base_salary_tax = $salary_tax - $allowance;
				
				if($base_salary_tax <= $tax->max_salary && $base_salary_tax >= $tax->min_salary){
					$tax_on_salary = ($base_salary_tax * $tax->tax_percent) - $tax->reduce_tax;
					$data = array(
							"tax_percent" 	=> $tax->tax_percent,
							"reduce_tax"  	=> $tax->reduce_tax,
							"net_salary"	=> $net_salary,
							"tax_on_salary" => ($tax_on_salary / $currency->rate),
						); 
				}
				
			}else{
					// mutiply with 20% non-resident
					$tax_on_salary = ($salary_tax * 0.2);
					$data = array(
							"tax_percent" 	=> 0,
							"reduce_tax"  	=> 0,
							"net_salary"	=> $net_salary,
							"tax_on_salary" => ($tax_on_salary / $currency->rate),
						);
			}
		}
		echo json_encode($data);
	}
	
	public function employee_types()
	{
		$this->bpas->checkPermissions('employee_types');
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employee_types')));
		$meta = array('page_title' => lang('employee_types'), 'bc' => $bc);
		$this->page_construct('hr/employee_types', $meta, $this->data);
	}
	
	public function getEmployeeTypes()
	{
		$this->bpas->checkPermissions('employee_types');
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_employee_type") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_employee_type/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_employee_type') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_employee_type/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_employee_type').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables->select("id, name")
             ->from("hr_employees_types")
             ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_employee_type()
	{
		$this->bpas->checkPermissions('employee_types');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array('name'=> $this->input->post('name'));
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->addEmployeeType($data)) {
			$this->session->set_flashdata('message', $this->lang->line("employee_type_added"));
            admin_redirect("hr/employee_types");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_employee_type', $this->data);	
		}	
	}
	
	public function edit_employee_type($id = false)
	{
		$this->bpas->checkPermissions('employee_types');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array('name'=> $this->input->post('name'));
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateEmployeeType($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("employee_type_updated"));
            admin_redirect("hr/employee_types");
        }else{
			
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['row'] = $this->hr_model->getEmployeeTypeByID($id);
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_employee_type', $this->data);	
		}	
	}
	
	public function delete_employee_type($id = null)
    {		
		$this->bpas->checkPermissions('employee_types');
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteEmployeeType($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('employee_type_deleted')]);
				}
				$this->session->set_flashdata('message', lang('employee_type_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	function employee_type_actions()
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
                        $this->hr_model->deleteEmployeeType($id);
                    }
                    $this->session->set_flashdata('message', lang("employee_type_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('employee_types');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $type = $this->hr_model->getEmployeeTypeByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $type->name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'employee_types_' . date('Y_m_d_H_i_s');
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
	
	function employees_relationships()
	{
		$this->bpas->checkPermissions('employees_relationships');
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('relationships')));
		$meta = array('page_title' => lang('relationships'), 'bc' => $bc);
		$this->page_construct('hr/relationships', $meta, $this->data);
	}
	
	public function getRelationships()
	{
		$this->bpas->checkPermissions('employees_relationships');
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_relationship") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_relationship/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_relationship') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_relationship/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_relationship').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables->select("
				id, 
				name
				")
             ->from("hr_employees_relationship")
             ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_relationship()
	{
		$this->bpas->checkPermissions('employees_relationships');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array('name'=> $this->input->post('name'));
			if($this->input->post('type') =='spouse'){
				$data['is_spouse'] = 1;
			}
			if($this->input->post('type') =='children'){
				$data['is_children'] = 1;
			}
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->addRelationship($data)) {
			$this->session->set_flashdata('message', $this->lang->line("relationship_added"));
            admin_redirect("hr/employees_relationships");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_relationship', $this->data);	
		}	
	}
	
	public function edit_relationship($id = false)
	{
		$this->bpas->checkPermissions('employees_relationships');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array('name'=> $this->input->post('name'));
			if($this->input->post('type') =='spouse'){
				$data['is_spouse'] 		= 1;
				$data['is_children'] 	= 0;
			}
			if($this->input->post('type') =='children'){
				$data['is_children'] 	= 1;
				$data['is_spouse'] 		= 0;
			}
			if(!$this->input->post('type')){
				$data['is_children'] 	= 0;
				$data['is_spouse'] 		= 0;
			}
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateRelationship($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("relationship_updated"));
            admin_redirect("hr/employees_relationships");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['row'] = $this->hr_model->getRelationshipByID($id);
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_relationship', $this->data);	
		}	
	}
	
	public function delete_relationship($id = null)
    {		
		$this->bpas->checkPermissions('employees_relationships');
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteRelationship($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('relationship_deleted')]);
				}
				$this->session->set_flashdata('message', lang('relationship_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	function relationship_actions()
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
                        $this->hr_model->deleteRelationship($id);
                    }
                    $this->session->set_flashdata('message', lang("relationship_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('relationship');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $type = $this->hr_model->getRelationshipByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $type->name);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'relationships_' . date('Y_m_d_H_i_s');
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
	
	function tax_conditions()
	{
		$this->bpas->checkPermissions('tax_conditions');
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('tax_conditions')));
		$meta = array('page_title' => lang('tax_conditions'), 'bc' => $bc);
		$this->page_construct('hr/tax_conditions', $meta, $this->data);
	}
	
	public function getTaxConditions()
	{
		$this->bpas->checkPermissions('tax_conditions');
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_tax_condition") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_tax_condition/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_tax_condition') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_tax_condition/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_tax_condition').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables->select("
				id, 
				min_salary,
				max_salary,
				tax_percent,
				reduce_tax
				")
             ->from("hr_tax_condition")
             ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_tax_condition()
	{
		$this->bpas->checkPermissions('tax_conditions');
		$this->form_validation->set_rules('min_salary', lang("min_salary"), 'required');
		$this->form_validation->set_rules('max_salary', lang("max_salary"), 'required');
		$this->form_validation->set_rules('tax_percent', lang("tax_percent"), 'required');
		$this->form_validation->set_rules('reduce_tax', lang("reduce_tax"), 'required');
		
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
						'min_salary'	=> $this->input->post('min_salary'),
						'max_salary'	=> $this->input->post('max_salary'),
						'tax_percent'	=> $this->input->post('tax_percent'),
						'reduce_tax'	=> $this->input->post('reduce_tax')
					);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->addTaxCondition($data)) {
			$this->session->set_flashdata('message', $this->lang->line("tax_condition_added"));
            admin_redirect("hr/tax_conditions");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_tax_condition', $this->data);	
		}	
	}
	
	public function edit_tax_condition($id = false)
	{
		$this->bpas->checkPermissions('tax_conditions');
		$this->form_validation->set_rules('min_salary', lang("min_salary"), 'required');
		$this->form_validation->set_rules('max_salary', lang("max_salary"), 'required');
		$this->form_validation->set_rules('tax_percent', lang("tax_percent"), 'required');
		$this->form_validation->set_rules('reduce_tax', lang("reduce_tax"), 'required');
		
		if ($this->form_validation->run() == true){
			$data = array(
						'min_salary'	=> $this->input->post('min_salary'),
						'max_salary'	=> $this->input->post('max_salary'),
						'tax_percent'	=> $this->input->post('tax_percent'),
						'reduce_tax'	=> $this->input->post('reduce_tax')
					);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateTaxCondition($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("tax_condition_updated"));
            admin_redirect("hr/tax_conditions");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['row'] = $this->hr_model->getTaxConditionByID($id);
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_tax_condition', $this->data);	
		}	
	}
	
	public function delete_tax_condition($id = null)
    {		
		$this->bpas->checkPermissions('tax_conditions');
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteTaxCondition($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('tax_condition_deleted')]);
				}
				$this->session->set_flashdata('message', lang('tax_condition_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	function tax_condition_actions()
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
                        $this->hr_model->deleteTaxCondition($id);
                    }
                    $this->session->set_flashdata('message', lang("tax_condition_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('tax_conditions');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('min_salary'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('max_salary'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('tax_percent'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('reduce_tax'));
                    $row = 2;
					
                    foreach ($_POST['val'] as $id) {
                        $tax = $this->hr_model->getTaxConditionByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $tax->min_salary);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $tax->max_salary);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $tax->tax_percent);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $tax->reduce_tax);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'tax_conditions_' . date('Y_m_d_H_i_s');
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
	public function promotions($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('promotions')));
        $meta = array('page_title' => lang('promotions'), 'bc' => $bc);
        $this->page_construct('hr/promotions', $meta, $this->data);
	}
	public function getPromotions($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_promotion") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_promotion/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_promotion') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_promotion/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_promotion').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees_working_promote.id as id,
					{$this->db->dbprefix('hr_employees')}.empcode as code,
					CONCAT({$this->db->dbprefix('hr_employees')}.lastname,' ',{$this->db->dbprefix('hr_employees')}.firstname) as name,
					hr_positions.name as position,
					hr_employees_level.name as employee_level,
					promoted_date,
					official_promote,
					CONCAT(em.lastname,' ',em.firstname) as promoted_by")
            ->from("hr_employees_working_promote")
			->join("hr_employees","hr_employees.id=hr_employees_working_promote.employee_id","left")
			->join("hr_employees em","em.id=hr_employees_working_promote.promoted_by","left")
			->join("hr_positions","hr_positions.id=position_id","left")
			->join("hr_employees_level","hr_employees_level.id=hr_employees_working_promote.employee_level","left")

			->join("hr_employees_types","hr_employees_types.id=employee_type_id","left");
		if($employee_id){
			$this->datatables->where("employee_id",$employee_id);
		}
			
			$this->datatables->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_promotion($employee_id = false)
	{
		$this->form_validation->set_rules('employee_type', lang("employee_type"), 'required');
		$this->form_validation->set_rules('position', lang("position"), 'required');
		$this->form_validation->set_rules('department', lang("department"), 'required');
		$this->form_validation->set_rules('promoted_date', lang("promoted_date"), 'required');
		if ($this->form_validation->run() == true){	
			$data = array(
						'employee_id'		=> $employee_id,
						'employee_type_id'	=> $this->input->post('employee_type'),
						'position_id'		=> $this->input->post('position'),
						'department_id'		=> $this->input->post('department'),
						'promoted_date'		=> $this->bpas->fld($this->input->post('promoted_date')),
						'promoted_by'		=> $this->input->post('promoted_by'),
						'employee_level'    => $this->input->post('employee_level'),
						'official_promote'  => $this->bpas->fld($this->input->post('official_promote')),
						'description'		=> $this->input->post('description')
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
							redirect($_SERVER["HTTP_REFERER"]);
						}
						$photo = $this->upload->file_name;
						$data['attachment'] = $photo;
					}
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->addPromotion($data)) {
			$this->session->set_flashdata('message', $this->lang->line("promotion_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#promotion");
        }else{
			$this->data['employee_id'] = $employee_id;
			$this->data['working_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($employee_id);
			$this->data['departments'] = $this->hr_model->getDepartments();
			$this->data['types'] = $this->hr_model->getEmployeeTypes();
			$this->data['employees'] = $this->hr_model->getAllEmployees();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['employee_levels']     = $this->hr_model->getNestedByCategories();
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_promotion', $this->data);	
		}	
	}
	
	public function delete_promotion($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deletePromotion($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('promotion_deleted')]);
				}
				$this->session->set_flashdata('message', lang('promotion_deleted'));
				admin_redirect('welcome');
			}
        }
    }
	
	public function edit_promotion($id = false)
	{
		$this->form_validation->set_rules('employee_type', lang("employee_type"), 'required');
		$this->form_validation->set_rules('position', lang("position"), 'required');
		$this->form_validation->set_rules('department', lang("department"), 'required');
		$this->form_validation->set_rules('promoted_date', lang("promoted_date"), 'required');
		$promotion = $this->hr_model->getPromotionByID($id);
		if ($this->form_validation->run() == true){	
			$data = array(
				'employee_id'		=> $promotion->employee_id,
				'employee_type_id'	=> $this->input->post('employee_type'),
				'position_id'		=> $this->input->post('position'),
				'department_id'		=> $this->input->post('department'),
				'promoted_date'		=> $this->bpas->fld($this->input->post('promoted_date')),
				'promoted_by'		=> $this->input->post('promoted_by'),
				'employee_level'    => $this->input->post('employee_level'),
				'official_promote'	=> $this->bpas->fld($this->input->post('official_promote')),
				'description'		=> $this->input->post('description')
			);
			if ($_FILES['official_promote']['size'] > 0) {
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
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['attachment'] = $photo;
			}
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updatePromotion($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("promotion_added"));
            admin_redirect("hr/edit_employee/".$promotion->employee_id."/#promotion");
        }else{
			$this->data['id'] = $id;
			$this->data['row'] = $promotion;
			$this->data['departments'] = $this->hr_model->getDepartments();
			$this->data['types'] = $this->hr_model->getEmployeeTypes();
			$this->data['employees'] = $this->hr_model->getAllEmployees();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['employee_levels']     = $this->hr_model->getNestedByCategories();
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_promotion', $this->data);	
		}	
	}
	public function employee_details($id = null)
	{
		$this->data['row'] = $this->hr_model->getEmployeeById($id);
		$this->data['employee_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($id);
		$this->data['department'] = $this->hr_model->getDepartmentById($this->data['employee_info']->department_id);
		$this->data['group'] = $this->hr_model->getGroupById($this->data['employee_info']->group_id);
		$this->data['position'] = $this->hr_model->getPositionById($this->data['employee_info']->position_id);
		$this->data['biller'] = $this->site->getCompanyByID($this->data['employee_info']->biller_id);
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'hr/employee_details', $this->data);
	}
	public function leave_categories()
	{
		$this->bpas->checkPermissions('leave_types');	
		$bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('leave_categories')));
		$meta = array('page_title' => lang('leave_categories'), 'bc' => $bc);
		$this->page_construct('hr/leave_categories', $meta, $this->data);
	}
	
	public function getLeaveCategories()
	{	
		$this->bpas->checkPermissions('leave_types');	
        $this->load->library('datatables');
        $action_link = "<div class=\"text-center\"><a href='" . admin_url('hr/leave_types/$1') . "' class='tip' title='" . lang("leave_types") . "'><i class=\"fa fa-eye\"></i></a>  <a href='" . admin_url('hr/edit_leave_categories/$1') . "' class='tip' title='" . lang("edit_leave_categories") . "' data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#myModal'><i class=\"fa fa-edit\"></i></a></div>";
        $this->datatables
            ->select("
					hr_leave_categories.id as id, 
					hr_leave_categories.name, 
					hr_leave_categories.description,
					hr_leave_categories.days")
            ->from("hr_leave_categories")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function edit_leave_categories($id = null)
	{		
		$this->bpas->checkPermissions('leave_types');
		$this->form_validation->set_rules('day', lang("day"), 'required');
		if ($this->form_validation->run() == true){						
			$data = array(
				'description' => $this->input->post('description'),
				'days'  => $this->input->post('day'),
			);
		} elseif ($this->input->post('submit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect("hr/leave_categories");
        }
		if ($this->form_validation->run() == true && $this->hr_model->updateLeaveCategory($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("leave_categories_updated"));
            admin_redirect("hr/leave_categories");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $this->hr_model->getLeaveCategoryByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_leave_categories', $this->data);
		}			
	}
	public function leave_types($category_id = false){

		$this->bpas->checkPermissions('leave_types');	
		$this->data["category_id"] = $category_id;
		$this->data["category"] = $this->hr_model->getLeaveCategoryByID($category_id);

		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('leave_types')));
		$meta = array('page_title' => lang('leave_types'), 'bc' => $bc);
		$this->page_construct('hr/leave_types', $meta, $this->data);
	}
	
	public function getLeaveTypes($category_id = false){	
		$this->bpas->checkPermissions('leave_types');	
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_leave_type") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_leave_type/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_leave_type') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_leave_type/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_leave_type').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_leave_types.id as id, 
					hr_leave_categories.name as category, 
					hr_leave_types.code,
					hr_leave_types.name,
					hr_leave_types.description,
					hr_leave_types.days,
					IF(".$this->db->dbprefix('hr_leave_types').".include_holiday = 1,'yes','no') as include_holiday")
            ->from("hr_leave_types")
			->join("hr_leave_categories","hr_leave_types.category_id=hr_leave_categories.id","left");
        if($category_id){
        	$this->datatables->where("hr_leave_types.category_id",$category_id);
        }
        $this->datatables->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_leave_type()
	{
		$this->bpas->checkPermissions('leave_types');	
		$post = $this->input->post();
		$this->form_validation->set_rules('name', lang("biller"), 'required');
		$this->form_validation->set_rules('category', lang("category"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'category_id'  => $post['category'],
				'name'  => $post['name'],
				'code'  => $post['code'],
				'description' => $post['description'],
				'days'  => $post['limit_day'],
				'include_holiday'  => $post['include_holiday'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->hr_model->addLeaveType($data)) {
            $this->session->set_flashdata('message', $this->lang->line("leave_type_added").' '.$post['name']);
            admin_redirect("hr/leave_types");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['categories'] = $this->hr_model->getLeaveCategories();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_leave_type', $this->data);	
		}	
	}
	
	public function edit_leave_type($id = null)
	{		
		$this->bpas->checkPermissions('leave_types');
		$post = $this->input->post();		
		$this->form_validation->set_rules('name', lang("biller"), 'required');
		$this->form_validation->set_rules('category', lang("category"), 'required');
		$this->form_validation->set_rules('code', lang("code"), 'required');

		if ($this->form_validation->run() == true) 
		{						
			$data = array(
				'category_id'  	=> $post['category'],
				'name'  		=> $post['name'],
				'code'  		=> $post['code'],
				'description' 	=> $post['description'],
				'days'  		=> $post['limit_day'],
				'include_holiday'  => $post['include_holiday'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

		if ($this->form_validation->run() == true && $id = $this->hr_model->updateLeaveType($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("leave_type_updated").' '.$post['name']);
            admin_redirect("hr/leave_types");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $this->hr_model->getLeaveTypeByID($id);
			$this->data['categories'] = $this->hr_model->getLeaveCategories();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_leave_type', $this->data);
		}			
	}
	
	public function delete_leave_type($id = null)
    {	
		$this->bpas->checkPermissions('leave_types');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_leave_types");
        	if($result){
        		$this->session->set_flashdata('message', lang("leave_type_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function leave_type_actions()
    {
		
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete-leave_type');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteLeaveTypeByID($id);
                    }
                    $this->session->set_flashdata('message', lang("leave_type_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('leave_type');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('category'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('limit_day'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('include_holiday'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $leave_type = $this->hr_model->getLeaveTypeByID($id);
						$category = $this->hr_model->getLeaveCategoryByID($leave_type->category_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $category->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $leave_type->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $leave_type->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $leave_type->description);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($leave_type->days));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, ($leave_type->include_holiday == 1? lang('yes'):lang('no')));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'leave_type_list_' . date('Y_m_d_H_i_s');
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
	
	
	
	
	public function kpi_types()
	{
		$this->bpas->checkPermissions('kpi_types');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('kpi_types')));
		$meta = array('page_title' => lang('kpi_types'), 'bc' => $bc);
		$this->page_construct('hr/kpi_types', $meta, $this->data);
	}
	
	public function getKPITypes()
	{	
		$this->bpas->checkPermissions('kpi_types');	
        $this->load->library('datatables');
		$measure_link = '<a href="'.admin_url('hr/kpi_measures/$1').'" class="tip"><i class="fa fa fa-balance-scale"></i>'.lang('kpi_measures').'</a>';
		$question_link = '<a href="'.admin_url('hr/kpi_questions/$1').'" class="tip"><i class="fa fa fa-question-circle"></i>'.lang('kpi_questions').'</a>';
		$delete_link = "<a href='#' class='po' title='" . lang("delete_kpi_type") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_kpi_type/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_kpi_type') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li>'.$measure_link.'</li>
								<li>'.$question_link.'</li>
								<li><a href="'.admin_url('hr/edit_kpi_type/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_kpi_type').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_kpi_types.id as id, 
					hr_kpi_types.code,
					hr_kpi_types.name,
					hr_kpi_types.description")
            ->from("hr_kpi_types")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_kpi_type() {
		$this->bpas->checkPermissions('kpi_types');	
		$post = $this->input->post();
		$this->form_validation->set_rules('code', lang("code"), 'trim|is_unique[hr_kpi_types.code]|required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description']
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	
		if ($this->form_validation->run() == true && $id = $this->hr_model->addKPIType($data)) {
            $this->session->set_flashdata('message', $this->lang->line("kpi_type_added").' '.$post['name']);
            admin_redirect("hr/kpi_types");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_kpi_type', $this->data);	
		}	
	}
	
	public function edit_kpi_type($id = null) {		
		$this->bpas->checkPermissions('kpi_types');
		$post = $this->input->post();		
		$kpi_type = $this->hr_model->getKPITypeByID($id);
		$this->form_validation->set_rules('code', lang("code"), 'trim|required');
		if ($post && $post['code'] != $kpi_type->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[hr_kpi_types.code]');
        }
		if ($this->form_validation->run() == true){						
			$data = array(
				'code'  => $post['code'],
				'name'  => $post['name'],
				'description' => $post['description']
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateKPIType($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("kpi_type_updated").' '.$post['name']);
            admin_redirect("hr/kpi_types");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $kpi_type;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_kpi_type', $this->data);
		}			
	}
	
	public function delete_kpi_type($id = null) {	
		$this->bpas->checkPermissions('kpi_types');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_kpi_types");
        	if($result){
        		$this->session->set_flashdata('message', lang("kpi_type_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function kpi_type_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('kpi_type');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteKPITypeByID($id);
                    }
                    $this->session->set_flashdata('message', lang("kpi_type_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('kpi_type');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $kpi_type = $this->hr_model->getKPITypeByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $kpi_type->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $kpi_type->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->remove_tag($kpi_type->description));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'kpi_type_list_' . date('Y_m_d_H_i_s');
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

	
	public function kpi_measures($kpi = false){
		if (!$kpi) {
            $this->session->set_flashdata('error', lang('no_kpi_type_selected'));
            admin_redirect('hr/kpi_types');
        }
		$this->bpas->checkPermissions('kpi_types');	
		$this->data['modal_js'] = $this->site->modal_js();
		$this->data['kpi_type'] = $this->hr_model->getKPITypeByID($kpi);
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/kpi_types'), 'page' => lang('kpi_types')), array('link' => '#', 'page' => lang('kpi_measures')));
		$meta = array('page_title' => lang('kpi_measures'), 'bc' => $bc);
		$this->page_construct('hr/kpi_measures', $meta, $this->data);
	}
	
	public function getKPIMeasures($kpi = false)
	{	
		$this->bpas->checkPermissions('kpi_types');	
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_kpi_measure") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_kpi_measure/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_kpi_measure') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('hr/edit_kpi_measure/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_kpi_measure').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_kpi_measures.id as id, 
					hr_kpi_measures.name,
					hr_kpi_measures.name_kh,
					hr_kpi_measures.min_percentage,
					hr_kpi_measures.max_percentage,
					hr_kpi_measures.increase_salary,
					hr_kpi_measures.description,
					hr_kpi_measures.color
					")
            ->from("hr_kpi_measures")
			->where("hr_kpi_measures.kpi_type",$kpi)
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_kpi_measure($kpi_type = false) {
		$this->bpas->checkPermissions('kpi_types');	
		$post = $this->input->post();
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'kpi_type'  => $post['kpi_type'],
				'name'  => $post['name'],
				'name_kh'  => $post['name_kh'],
				'min_percentage'  => $post['min_percentage'],
				'max_percentage'  => $post['max_percentage'],
				'increase_salary'  => $post['increase_salary'],
				'color'  => $post['color'],
				'description' => $post['description']
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	
		if ($this->form_validation->run() == true && $id = $this->hr_model->addKPIMeasure($data)) {
            $this->session->set_flashdata('message', $this->lang->line("kpi_measure_added").' '.$post['name']);
            admin_redirect("hr/kpi_measures/".$post['kpi_type']);
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['kpi_type'] = $kpi_type;
			$this->load->view($this->theme . 'hr/add_kpi_measure', $this->data);	
		}	
	}
	
	public function edit_kpi_measure($id = null) {		
		$this->bpas->checkPermissions('kpi_types');
		$post = $this->input->post();		
		$kpi_measure = $this->hr_model->getKPIMeasureByID($id);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true){						
			$data = array(
				'name'  => $post['name'],
				'name_kh'  => $post['name_kh'],
				'min_percentage'  => $post['min_percentage'],
				'max_percentage'  => $post['max_percentage'],
				'increase_salary'  => $post['increase_salary'],
				'color'  => $post['color'],
				'description' => $post['description']
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateKPIMeasure($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("kpi_measure_updated").' '.$post['name']);
            admin_redirect("hr/kpi_measures/".$kpi_measure->kpi_type);
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $kpi_measure;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_kpi_measure', $this->data);
		}			
	}
	
	public function delete_kpi_measure($id = null) {	
		$this->bpas->checkPermissions('kpi_types');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_kpi_measures");
        	if($result){
        		$this->session->set_flashdata('message', lang("kpi_measure_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function kpi_measure_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('kpi_measure');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteKPIMeasureByID($id);
                    }
                    $this->session->set_flashdata('message', lang("kpi_measure_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('kpi_measure');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name_kh'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('min_percentage'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('max_percentage'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('increase_salary'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $kpi_measure = $this->hr_model->getKPIMeasureByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $kpi_measure->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $kpi_measure->name_kh);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($kpi_measure->min_percentage));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($kpi_measure->max_percentage));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $kpi_measure->increase_salary);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($kpi_measure->description));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'kpi_measure_list_' . date('Y_m_d_H_i_s');
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
	
	
	
	public function kpi_questions($kpi = false){
		if (!$kpi) {
            $this->session->set_flashdata('error', lang('no_kpi_type_selected'));
            admin_redirect('hr/kpi_types');
        }
		$this->bpas->checkPermissions('kpi_types');	
		$this->data['modal_js'] = $this->site->modal_js();
		$this->data['kpi_type'] = $this->hr_model->getKPITypeByID($kpi);
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/kpi_types'), 'page' => lang('kpi_types')), array('link' => '#', 'page' => lang('kpi_questions')));
		$meta = array('page_title' => lang('kpi_questions'), 'bc' => $bc);
		$this->page_construct('hr/kpi_questions', $meta, $this->data);
	}
	
	public function getKPIQuestions($kpi = false)
	{	
		$this->bpas->checkPermissions('kpi_types');	
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_kpi_question") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_kpi_question/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_kpi_question') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('hr/edit_kpi_question/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_kpi_question').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_kpi_questions.id as id, 
					hr_kpi_questions.question,
					hr_kpi_questions.question_kh,
					hr_kpi_questions.value_percentage,
					hr_kpi_questions.min_rate,
					hr_kpi_questions.max_rate,
					hr_kpi_questions.description
					")
            ->from("hr_kpi_questions")
			->where("hr_kpi_questions.kpi_type",$kpi)
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_kpi_question($kpi_type = false) {
		$this->bpas->checkPermissions('kpi_types');	
		$post = $this->input->post();
		$this->form_validation->set_rules('question', lang("question"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'kpi_type'  => $post['kpi_type'],
				'question'  => $post['question'],
				'question_kh'  => $post['question_kh'],
				'value_percentage'  => $post['value_percentage'],
				'min_rate'  => $post['min_rate'],
				'max_rate'  => $post['max_rate'],
				'description' => $post['description'],
				'type'  => $post['type']
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	
		if ($this->form_validation->run() == true && $id = $this->hr_model->addKPIQuestion($data)) {
            $this->session->set_flashdata('message', $this->lang->line("kpi_question_added").' '.$post['name']);
            admin_redirect("hr/kpi_questions/".$post['kpi_type']);
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['kpi_type'] = $kpi_type;
			$this->load->view($this->theme . 'hr/add_kpi_question', $this->data);	
		}	
	}
	
	public function edit_kpi_question($id = null) {		
		$this->bpas->checkPermissions('kpi_types');
		$post = $this->input->post();		
		$kpi_question = $this->hr_model->getKPIQuestionByID($id);
		$this->form_validation->set_rules('question', lang("question"), 'required');
		if ($this->form_validation->run() == true){						
			$data = array(
				'question'  => $post['question'],
				'question_kh'  => $post['question_kh'],
				'value_percentage'  => $post['value_percentage'],
				'min_rate'  => $post['min_rate'],
				'max_rate'  => $post['max_rate'],
				'description' => $post['description'],
				'type'  => $post['type']
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateKPIQuestion($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("kpi_question_updated").' '.$post['name']);
            admin_redirect("hr/kpi_questions/".$kpi_question->kpi_type);
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $kpi_question;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_kpi_question', $this->data);
		}			
	}
	
	public function delete_kpi_question($id = null) {	
		$this->bpas->checkPermissions('kpi_types');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("hr_kpi_questions");
        	if($result){
        		$this->session->set_flashdata('message', lang("kpi_question_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function kpi_question_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('kpi_question');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteKPIQuestionByID($id);
                    }
                    $this->session->set_flashdata('message', lang("kpi_question_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('kpi_question');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('question'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('question_kh'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('value_percentage'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('min_rate'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('max_rate'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $kpi_question = $this->hr_model->getKPIQuestionByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->remove_tag($kpi_question->question));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($kpi_question->question_kh));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->formatDecimal($kpi_question->value_percentage));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->formatDecimal($kpi_question->min_rate));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->formatDecimal($kpi_question->max_rate));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($kpi_question->description));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'kpi_question_list_' . date('Y_m_d_H_i_s');
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
	
	public function modal_view_kpi($id = false){
		$this->bpas->checkPermissions('kpi_index');	
		$kpi = $this->hr_model->getKPIByID($id);
		$kpi_questions = $this->hr_model->getKPIItems($id);	
		$this->data['kpi'] = $kpi;
		$this->data['kpi_questions'] = $kpi_questions;
		$this->data['employee_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($kpi->employee_id);
		$this->data['department'] = $this->hr_model->getDepartmentById($this->data['employee_info']->department_id);
		$this->data['group'] = $this->hr_model->getGroupById($this->data['employee_info']->group_id);
		$this->data['position'] = $this->hr_model->getPositionById($this->data['employee_info']->position_id);
		$this->data['biller'] = $this->site->getCompanyByID($this->data['employee_info']->biller_id);
		$this->data['modal_js'] = $this->site->modal_js();
		$this->load->view($this->theme . 'hr/modal_view_kpi', $this->data);
	}
	
	
	public function kpi()
	{
		$this->bpas->checkPermissions('kpi_index');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('kpi')));
		$meta = array('page_title' => lang('kpi'), 'bc' => $bc);
		$this->page_construct('hr/kpi', $meta, $this->data);
	}
	
	public function getKPI()
	{	
		$this->bpas->checkPermissions('kpi_index');	
        $this->load->library('datatables');
		$edit_link = '<a href="'.admin_url('hr/edit_kpi/$1').'" class="tip"><i class="fa fa fa-edit"></i>'.lang('edit_kpi').'</a>';
		$delete_link = "<a href='#' class='po' title='" . lang("delete_kpi") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_kpi/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_kpi') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
								<li>'.$edit_link.'</li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_kpi.id as id, 
					hr_kpi.date,
					hr_kpi.month,
					CONCAT(".$this->db->dbprefix("hr_employees").".lastname,' ',".$this->db->dbprefix("hr_employees").".firstname) as employee,
					hr_kpi_types.name as kpi_type,
					hr_kpi.result,
					CONCAT(".$this->db->dbprefix("hr_kpi").".measure,".$this->db->dbprefix("hr_kpi").".measure_color) as credit,
					hr_kpi.manager_note,
					hr_kpi.employee_note")
            ->from("hr_kpi")
			->join("hr_employees","hr_employees.id = hr_kpi.employee_id","left")
			->join("hr_kpi_types","hr_kpi_types.id = hr_kpi.kpi_type","left")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	
	function kpi_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('kpi_delete');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteKPI($id);
                    }
                    $this->session->set_flashdata('message', lang("kpi_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('kpi');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('employee'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('kpi_type'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('result'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('credit'));
					$this->excel->getActiveSheet()->SetCellValue('G1', lang('manager_note'));
					$this->excel->getActiveSheet()->SetCellValue('H1', lang('employee_note'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $kpi = $this->hr_model->getKPIByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($kpi->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $kpi->month);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, ($kpi->lastname.' '.$kpi->firstname));
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $kpi->kpi_type);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $kpi->result);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $kpi->measure);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($kpi->manager_note));
						$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->remove_tag($kpi->employee_note));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
					$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(40);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'kpi_list_' . date('Y_m_d_H_i_s');
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
	
	
	
	function add_kpi()
    {
        $this->bpas->checkPermissions('kpi_add');
        $this->form_validation->set_rules('date', lang("date"), 'required');
		$this->form_validation->set_rules('month', lang("month"), 'required');
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		$this->form_validation->set_rules('kpi_type', lang("kpi_type"), 'required');

		
        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fsd($this->input->post('date'));
			$month = $this->input->post('month');
			$employee = $this->input->post('employee');
			$kpi_type = $this->input->post('kpi_type');
			$result = $this->input->post('result');
			$manager_note = $this->bpas->clear_tags($this->input->post('manager_note'));
			$employee_note = $this->bpas->clear_tags($this->input->post('employee_note'));
			$i = isset($_POST['question_id']) ? sizeof($_POST['question_id']) : 0;
			$items = false;
			for ($r = 0; $r < $i; $r++) {
                $question_id = $_POST['question_id'][$r];
				$comment = $_POST['comment'][$r];
				$rate = $_POST['rate'][$r];
				$kpi_question = $this->hr_model->getKPIQuestionByID($question_id);
				$items[] = array(
								'question_id' => $question_id,
								'question' => $kpi_question->question,
								'question_kh' => $kpi_question->question_kh,
								'min_rate' => $kpi_question->min_rate,
								'max_rate' => $kpi_question->max_rate,
								'value_percentage' => $kpi_question->value_percentage,
								'comment' => $comment,
								'rate' => $rate,
			
							);
            }
			
			$kpi_measure  = $this->hr_model->getKPIMeasureByResult($kpi_type,$result);
            $data = array(
                'date' => $date,
				'month' => $month,
				'employee_id' => $employee,
				'kpi_type' => $kpi_type,
				'result' => $result,
				'measure' => ($kpi_measure ? $kpi_measure->name : ''),
				'measure_kh' => ($kpi_measure ? $kpi_measure->name_kh : ''),
				'measure_color' => ($kpi_measure ? $kpi_measure->color : ''),
				'manager_note' => $manager_note,
				'employee_note' => $employee_note,
                'created_by' => $this->session->userdata('user_id')
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if (empty($items)) {
                $this->form_validation->set_rules('kpi_question', lang("kpi_question"), 'required');
			}

        }
        
        if ($this->form_validation->run() == true && $this->hr_model->addKPI($data, $items)) {
            $this->session->set_userdata('remove_kpls', 1);
			$this->session->set_flashdata('message', lang("kpi_added"));
			admin_redirect('hr/kpi');

        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['employees'] = $this->hr_model->getEmployees();
			$this->data['kpi_types'] = $this->hr_model->getKPITypes();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('hr')),array('link' => admin_url('hr/kpi'), 'page' => lang('kpi')), array('link' => '#', 'page' => lang('add_kpi')));
			$meta = array('page_title' => lang('add_kpi'), 'bc' => $bc);
            $this->page_construct('hr/add_kpi', $meta, $this->data);
        }
    }
	
	function edit_kpi($id = false)
    {
        $this->bpas->checkPermissions('kpi_edit');
        $this->form_validation->set_rules('date', lang("date"), 'required');
		$this->form_validation->set_rules('month', lang("month"), 'required');
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		$this->form_validation->set_rules('kpi_type', lang("kpi_type"), 'required');

		
        if ($this->form_validation->run() == true) {
			$date = $this->bpas->fsd($this->input->post('date'));
			$month = $this->input->post('month');
			$employee = $this->input->post('employee');
			$kpi_type = $this->input->post('kpi_type');
			$result = $this->input->post('result');
			$manager_note = $this->bpas->clear_tags($this->input->post('manager_note'));
			$employee_note = $this->bpas->clear_tags($this->input->post('employee_note'));
			$i = isset($_POST['question_id']) ? sizeof($_POST['question_id']) : 0;
			$items = false;
			for ($r = 0; $r < $i; $r++) {
                $question_id = $_POST['question_id'][$r];
				$comment = $_POST['comment'][$r];
				$rate = $_POST['rate'][$r];
				$kpi_question = $this->hr_model->getKPIQuestionByID($question_id);
				$items[] = array(
								'kpi_id' => $id,
								'question_id' => $question_id,
								'question' => $kpi_question->question,
								'question_kh' => $kpi_question->question_kh,
								'min_rate' => $kpi_question->min_rate,
								'max_rate' => $kpi_question->max_rate,
								'value_percentage' => $kpi_question->value_percentage,
								'comment' => $comment,
								'rate' => $rate,
			
							);
            }
			
			$kpi_measure  = $this->hr_model->getKPIMeasureByResult($kpi_type,$result);
            $data = array(
                'date' => $date,
				'month' => $month,
				'employee_id' => $employee,
				'kpi_type' => $kpi_type,
				'result' => $result,
				'measure' => ($kpi_measure ? $kpi_measure->name : ''),
				'measure_kh' => ($kpi_measure ? $kpi_measure->name_kh : ''),
				'measure_color' => ($kpi_measure ? $kpi_measure->color : ''),
				'manager_note' => $manager_note,
				'employee_note' => $employee_note,
                'updated_by' => $this->session->userdata('user_id')
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
			if (empty($items)) {
                $this->form_validation->set_rules('kpi_question', lang("kpi_question"), 'required');
			}

        }
        
        if ($this->form_validation->run() == true && $this->hr_model->updateKPI($id, $data, $items)) {
            $this->session->set_userdata('remove_kpls', 1);
			$this->session->set_flashdata('message', lang("kpi_updated"));
			admin_redirect('hr/kpi');

        } else {
			$kpi_items = $this->hr_model->getKPIItems($id);
			krsort($kpi_items);
			$c = rand(100000, 9999999);
			if($kpi_items){
				foreach ($kpi_items as $kpi_item) {
					$kpi_item->id = $kpi_item->question_id;
					$pr[$c] = array('id' => $c, 'item_id' => $kpi_item->id, 'row' => $kpi_item);
					$c++;
				}
			}else{
				$pr = false;
			}
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['employees'] = $this->hr_model->getEmployees();
			$this->data['kpi_types'] = $this->hr_model->getKPITypes();
			$this->data['kpi_info'] = $this->hr_model->getKPIByID($id);
			$this->data['kpi_items'] = json_encode($pr);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('hr')),array('link' => admin_url('hr/kpi'), 'page' => lang('kpi')), array('link' => '#', 'page' => lang('edit_kpi')));
			$meta = array('page_title' => lang('edit_kpi'), 'bc' => $bc);
            $this->page_construct('hr/edit_kpi', $meta, $this->data);
        }
    }
	
	public function delete_kpi($id = null) {	
		$this->bpas->checkPermissions('kpi_delete');
		if (isset($id) || $id != null){
        	$delete = $this->hr_model->deleteKPI($id);
        	if($delete){
        		$this->session->set_flashdata('message', lang("kpi_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
            else{
            	admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
	
	
	public function get_kpi(){
		$employee_id = $this->input->get('employee_id');
		$employee = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($employee_id);
		if($employee->kpi_type > 0){
			echo json_encode($employee->kpi_type); 
		}else{
			echo json_encode('0');
		}

	}
	
	public function get_kpi_questions(){
		$kpi_type = $this->input->get('kpi_type');
		$kpi_questions = $this->hr_model->getKPIQuestionByKPIType($kpi_type);
		krsort($kpi_questions);
		$c = rand(100000, 9999999);
		if($kpi_questions){
			foreach ($kpi_questions as $kpi_question) {
				$kpi_question->rate = $kpi_question->min_rate;
				$kpi_question->comment = '';
				$pr[$c] = array('id' => $c, 'item_id' => $kpi_question->id, 'row' => $kpi_question);
				$c++;
			}
		}else{
			$pr = false;
		}
		echo json_encode($pr);
		
	}
	
	
	
	public function get_positions(){
		$biller_id = $this->input->get('biller_id');
		$positions = $this->hr_model->getPositionsByBiller($biller_id);
		echo json_encode($positions);
	}
	public function get_departments(){
		$biller_id = $this->input->get('biller_id');
		$departments = $this->hr_model->getDepartmentsByBilller($biller_id);
		echo json_encode($departments);
	}
	public function get_groups(){
		$department_id = $this->input->get('department_id');
		$groups = $this->hr_model->getGroupsByBiller($department_id);
		echo json_encode($groups);
	}
	
	
	public function employees_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['policies'] = $this->hr_model->getPolicies();
		$this->data['employee_types'] = $this->hr_model->getEmployeeTypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getPositionsByBiller($biller);
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($department);
			}
		}
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employees_report')));
        $meta = array('page_title' => lang('employees_report'), 'bc' => $bc);
        $this->page_construct('hr/employees_report', $meta, $this->data);
	}
	public function getEmployeesReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('employees_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$gender = $this->input->get('gender') ? $this->input->get('gender') : NULL;
		$policy = $this->input->get('policy') ? $this->input->get('policy') : NULL;
		$employee_type = $this->input->get('employee_type') ? $this->input->get('employee_type') : NULL;
		$status 	= $this->input->get('status') ? $this->input->get('status') : NULL;

        if ($pdf || $xls) {
			$this->db->select("	
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								DATE_FORMAT(".$this->db->dbprefix('hr_employees').".dob, '%Y-%m-%d') as dob,
								hr_employees.gender,
								hr_employees.phone,
								hr_employees.address,
								hr_employees.note,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								att_policies.policy,
								DATE_FORMAT(".$this->db->dbprefix('hr_employees_working_info').".employee_date, '%Y-%m-%d') as employee_date,
								hr_employees_types.name as employee_type,
								hr_employees_working_info.net_salary,
								hr_employees_working_info.status,
								hr_employees.id as id
								")
						->from("hr_employees")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->join("att_policies","att_policies.id = hr_employees_working_info.policy_id","left")
						->join("hr_employees_types","hr_employees_types.id = hr_employees_working_info.employee_type_id","left")
						->group_by("hr_employees.id");

			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
			if ($gender) {
                $this->db->where('hr_employees.gender', $gender);
            }
			if ($policy) {
                $this->db->where('hr_employees_working_info.policy_id', $policy);
            }
			if ($employee_type) {
                $this->db->where('hr_employees_working_info.employee_type_id', $employee_type);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('employees_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('dob'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('gender'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('phone'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('address'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('note'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('policy'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('employee_date'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('employee_type'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('net_salary'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->dob);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->gender);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->phone);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->remove_tag($data_row->address));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($data_row->note));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->policy);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->employee_date);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->employee_type);
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->formatMoney($data_row->net_salary));
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, lang($data_row->status));
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
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('O')->setWidth(20);

				$filename = 'employee_report_list_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									DATE_FORMAT(".$this->db->dbprefix('hr_employees').".dob, '%Y-%m-%d') as dob,
									hr_employees.gender,
									hr_employees.phone,
									hr_employees.address,
									hr_employees.note,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									att_policies.policy,
									DATE_FORMAT(".$this->db->dbprefix('hr_employees_working_info').".employee_date, '%Y-%m-%d') as employee_date,
									hr_employees_types.name as employee_type,
									hr_employees_working_info.net_salary,
									hr_employees_working_info.status,
									hr_employees.id as id
									")
							->from("hr_employees")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->join("att_policies","att_policies.id = hr_employees_working_info.policy_id","left")
							->join("hr_employees_types","hr_employees_types.id = hr_employees_working_info.employee_type_id","left")
							->group_by("hr_employees.id");

			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
			if ($gender) {
                $this->datatables->where('hr_employees.gender', $gender);
            }
			if ($policy) {
                $this->datatables->where('hr_employees_working_info.policy_id', $policy);
            }
			if ($employee_type) {
                $this->datatables->where('hr_employees_working_info.employee_type_id', $employee_type);
            }
            if ($status) {
                $this->datatables->where('hr_employees_working_info.status', $status);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	
	
	public function banks_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getPositionsByBiller($biller);
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($department);
			}
		}
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('banks_report')));
        $meta = array('page_title' => lang('banks_report'), 'bc' => $bc);
        $this->page_construct('hr/banks_report', $meta, $this->data);
	}
	public function getBanksReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('banks_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        if ($pdf || $xls) {
			$this->db->select("	
							hr_employees.empcode,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_employees_bank.bank_account,
							hr_employees_bank.account_no,
							hr_employees_bank.account_name,
							hr_employees_bank.account_type
							")
					->from("hr_employees_bank")
					->join("hr_employees","hr_employees.id = hr_employees_bank.employee_id","left")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees_bank.employee_id","left")
					->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
					->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
					->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
					->group_by("hr_employees_bank.id");

			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('banks_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('bank_name'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('account_no'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('account_name'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('account_type'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->bank_account);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->account_no);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->account_name);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->account_type);
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

				$filename = 'banks_report_list_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									hr_employees_bank.bank_account,
									hr_employees_bank.account_no,
									hr_employees_bank.account_name,
									hr_employees_bank.account_type
									")
							->from("hr_employees_bank")
							->join("hr_employees","hr_employees.id = hr_employees_bank.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees_bank.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("hr_employees_bank.id");

			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	public function kpi_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['kpi_types'] = $this->hr_model->getKPITypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getPositionsByBiller($biller);
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($department);
			}
		}
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('kpi_report')));
        $meta = array('page_title' => lang('kpi_report'), 'bc' => $bc);
        $this->page_construct('hr/kpi_report', $meta, $this->data);
	}
	public function getKPIReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('banks_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$kpi_type = $this->input->get('kpi_type') ? $this->input->get('kpi_type') : NULL;
        if ($pdf || $xls) {
			$this->db->select("
							DATE_FORMAT(".$this->db->dbprefix('hr_kpi').".date, '%Y-%m-%d') as date,
							hr_kpi.month,
							hr_employees.empcode,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
							hr_positions.name as position,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_kpi_types.name as kpi_type,
							hr_kpi.result,
							hr_kpi.measure as credit,
							hr_kpi.manager_note,
							hr_kpi.employee_note,
							CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by")
					->from("hr_kpi")
					->join("hr_employees","hr_employees.id = hr_kpi.employee_id","left")
					->join("hr_kpi_types","hr_kpi_types.id = hr_kpi.kpi_type","left")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_kpi.employee_id","left")
					->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
					->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
					->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
					->join("users","users.id = hr_kpi.created_by","left");
			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
			if ($kpi_type) {
                $this->db->where('hr_kpi.kpi_type', $kpi_type);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('kpi_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('kpi_type'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('result'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('credit'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('manager_note'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('employee_note'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('created_by'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->kpi_type);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatDecimal($data_row->result));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->credit);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->remove_tag($data_row->manager_note));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->remove_tag($data_row->employee_note));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->created_by);
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
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);

				$filename = 'kpi_report_list_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('hr_kpi').".date, '%Y-%m-%d') as date,
									hr_kpi.month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									hr_kpi_types.name as kpi_type,
									hr_kpi.result,
									CONCAT(".$this->db->dbprefix("hr_kpi").".measure,".$this->db->dbprefix("hr_kpi").".measure_color) as credit,
									hr_kpi.manager_note,
									hr_kpi.employee_note,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									hr_kpi.id as id")
							->from("hr_kpi")
							->join("hr_employees","hr_employees.id = hr_kpi.employee_id","left")
							->join("hr_kpi_types","hr_kpi_types.id = hr_kpi.kpi_type","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_kpi.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->join("users","users.id = hr_kpi.created_by","left");
			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
			if ($kpi_type) {
                $this->datatables->where('hr_kpi.kpi_type', $kpi_type);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	
	
	public function sample_id_cards()
	{
		$this->bpas->checkPermissions('sample_id_cards');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('sample_id_cards')));
		$meta = array('page_title' => lang('sample_id_cards'), 'bc' => $bc);
		$this->page_construct('hr/sample_id_cards', $meta, $this->data);
	}
	
	public function getSampleIDCard()
	{	
		$this->bpas->checkPermissions('sample_id_cards');	
        $this->load->library('datatables');
		$dimension_size = '<a href="'.admin_url('hr/dimension_size/$1').'" class="tip"><i class="fa fa-heart"></i>'.lang('dimension_size').'</a>';
		$delete_link = "<a href='#' class='po' title='" . lang("delete_sample_id_card") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_sample_id_card/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sample_id_card') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li>'.$dimension_size.'</li>
								<li><a href="'.admin_url('hr/edit_sample_id_card/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_sample_id_card').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					hr_sample_id_cards.id as id, 
					hr_sample_id_cards.name,
					hr_sample_id_cards.width,
					hr_sample_id_cards.height,
					hr_sample_id_cards.description")
            ->from("hr_sample_id_cards")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_sample_id_card() {
		$this->bpas->checkPermissions('sample_id_cards');	
		$post = $this->input->post();
		$this->form_validation->set_rules('name', lang("name"), 'trim|is_unique[hr_sample_id_cards.name]|required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'name'  => $post['name'],
				'width' => $post['width'],
				'height' => $post['height'],
				'description' => $post['description']
			);
			
			if ($_FILES['front_card']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('front_card')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['front_card'] = $photo;
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
			if ($_FILES['back_card']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('back_card')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['back_card'] = $photo;
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

		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	
		if ($this->form_validation->run() == true && $id = $this->hr_model->addSampleIDCard($data)) {
            $this->session->set_flashdata('message', $this->lang->line("sample_id_card_added").' '.$post['name']);
            admin_redirect("hr/sample_id_cards");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_sample_id_card', $this->data);	
		}	
	}
	
	public function edit_sample_id_card($id = null) {		
		$this->bpas->checkPermissions('sample_id_cards');
		$post = $this->input->post();		
		$sample_id_card = $this->hr_model->getSampleIDCardByID($id);
		$this->form_validation->set_rules('name', lang("name"), 'trim|required');
		if ($post && $post['name'] != $sample_id_card->name) {
            $this->form_validation->set_rules('name', lang("name"), 'is_unique[hr_sample_id_cards.name]');
        }
		if ($this->form_validation->run() == true){						
			$data = array(
				'name'  => $post['name'],
				'width' => $post['width'],
				'height' => $post['height'],
				'description' => $post['description']
			);
			if ($_FILES['front_card']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('front_card')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['front_card'] = $photo;
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
			if ($_FILES['back_card']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('back_card')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['back_card'] = $photo;
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
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateSampleIDCard($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("sample_id_card_updated").' '.$post['name']);
            admin_redirect("hr/sample_id_cards");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['row'] = $sample_id_card;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_sample_id_card', $this->data);
		}			
	}
	
	public function delete_sample_id_card($id = null) {	
		$this->bpas->checkPermissions('sample_id_cards');
        if (isset($id) || $id != null){        	
        	$result = $this->hr_model->deleteSampleIDCardByID($id);
        	if($result){
        		$this->session->set_flashdata('message', lang("sample_id_card_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function sample_id_card_actions() {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('sample_id_card');
                    foreach ($_POST['val'] as $id) {
                        $this->hr_model->deleteSampleIDCardByID($id);
                    }
                    $this->session->set_flashdata('message', lang("sample_id_card_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('sample_id_card');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('width'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('height'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sample_id_card = $this->hr_model->getSampleIDCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sample_id_card->name);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $sample_id_card->width);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $sample_id_card->height);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($sample_id_card->description));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sample_id_card_list_' . date('Y_m_d_H_i_s');
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
	
	public function dimension_size($id){
		$this->bpas->checkPermissions('sample_id_cards');
		$this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('font_size', $this->lang->line("font_size"), 'required');
		$this->form_validation->set_rules('qrcode_size', $this->lang->line("qrcode_size"), 'required');
		if ($this->form_validation->run() == true) {
			$font_size = $this->input->post('font_size') ? $this->input->post('font_size') : 0;
			$profile_padding_top = $this->input->post('profile_padding_top') ? $this->input->post('profile_padding_top') : 0;
			$profile_padding_left = $this->input->post('profile_padding_left') ? $this->input->post('profile_padding_left') : 0;
			$working_padding_left = $this->input->post('working_padding_left') ? $this->input->post('working_padding_left') : 0;
			$qrcode_size = $this->input->post('qrcode_size') ? $this->input->post('qrcode_size') : 0;
			$qrcode_padding_top = $this->input->post('qrcode_padding_top') ? $this->input->post('qrcode_padding_top') : 0;
			$qrcode_padding_left = $this->input->post('qrcode_padding_left') ? $this->input->post('qrcode_padding_left') : 0;
			$photo_width = $this->input->post('photo_width') ? $this->input->post('photo_width') : 0;
			$photo_height = $this->input->post('photo_height') ? $this->input->post('photo_height') : 0;
			$data = array(
				'font_size' => $font_size,
                'profile_padding_top' => $profile_padding_top,
                'profile_padding_left' => $profile_padding_left,
				'working_padding_left' => $working_padding_left,
                'qrcode_size' => $qrcode_size,
				'qrcode_padding_top' => $qrcode_padding_top,
				'qrcode_padding_left' => $qrcode_padding_left,
				'photo_width' => $photo_width,
				'photo_height' => $photo_height,
            );
		}
		if ($this->form_validation->run() == true && $this->hr_model->updateIDCardDemension($id,$data)) {	
            $this->session->set_flashdata('message', $this->lang->line("demension_size_updated"));          
			admin_redirect('hr/dimension_size/'.$id);
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
			$this->data['sample_id_card'] = $this->hr_model->getSampleIDCardByID($id);
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/sample_id_cards'), 'page' => lang('sample_id_cards')), array('link' => '#', 'page' => lang('dimension_size')));
			$meta = array('page_title' => lang('dimension_size'), 'bc' => $bc);
			$this->page_construct('hr/dimension_size', $meta, $this->data);
        }
		
		
		
	}
	
	public function id_cards($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('id_cards')));
        $meta = array('page_title' => lang('id_cards'), 'bc' => $bc);
        $this->page_construct('hr/id_cards', $meta, $this->data);
	}
	public function getIDCards($biller_id = false){
		$this->bpas->checkPermissions("id_cards");
		$print_link = anchor('admin/hr/print_id_card/$1', '<i class="fa fa-print"></i> ' . lang('print_id_card'), ' class="print_id_card"');
        $edit_link = anchor('admin/hr/edit_id_card/$1', '<i class="fa fa-edit"></i> ' . lang('edit_id_card'), ' class="edit_id_card"');
        $delete_link = "<a href='#' class='delete_id_card po' title='<b>" . $this->lang->line("delete_id_card") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_id_card/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_id_card') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['hr-approve_id_card']){
			$approve_link = "<a href='#' class='po approve_id_card' title='" . $this->lang->line("approve_id_card") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/approve_id_card/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_id_card') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_id_card' title='<b>" . $this->lang->line("unapprove_id_card") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/unapprove_id_card/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_id_card') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>' . $print_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	hr_id_cards.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('hr_id_cards').".date, '%Y-%m-%d %T') as date,
									DATE_FORMAT(".$this->db->dbprefix('hr_id_cards').".invalid_date, '%Y-%m-%d %T') as invalid_date,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									note,
									hr_id_cards.status,
									attachment
									")
							->from("hr_id_cards")
							->join("users","users.id = hr_id_cards.created_by","left")
							;
		if ($biller_id) {
            $this->datatables->where("hr_id_cards.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_id_cards.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_id_cards.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_id_card(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('invalid_date', $this->lang->line("invalid_date"), 'required');
		$this->form_validation->set_rules('sample_id_card', $this->lang->line("sample_id_card"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$invalid_date = $this->bpas->fld(trim($this->input->post('invalid_date')));
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$sample_id_card = $this->input->post('sample_id_card') ? $this->input->post('sample_id_card') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$items[] = array(
								"employee_id" => $employee_id
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'invalid_date' => $invalid_date,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'sample_id_card' => $sample_id_card,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
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
		if ($this->form_validation->run() == true && $this->hr_model->addIDCard($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("id_card_added"));          
			admin_redirect('hr/id_cards');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['sample_id_cards'] = $this->hr_model->getIDCardSamples();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/id_cards'), 'page' => lang('id_cards')), array('link' => '#', 'page' => lang('add_id_card')));
            $meta = array('page_title' => lang('add_id_card'), 'bc' => $bc);
            $this->page_construct('hr/add_id_card', $meta, $this->data);
        }
	}
	public function edit_id_card($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('invalid_date', $this->lang->line("invalid_date"), 'required');
		$this->form_validation->set_rules('sample_id_card', $this->lang->line("sample_id_card"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$invalid_date = $this->bpas->fld(trim($this->input->post('invalid_date')));
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$sample_id_card = $this->input->post('sample_id_card') ? $this->input->post('sample_id_card') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$items[] = array(
								"employee_id" => $employee_id,
								"id_card_id" => $id
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'invalid_date' => $invalid_date,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'sample_id_card' => $sample_id_card,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
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
		if ($this->form_validation->run() == true && $this->hr_model->updateIDCard($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("id_card_updated"));          
			admin_redirect('hr/id_cards');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$id_card = $this->hr_model->getIDCardByID($id);
			$this->data['id_card'] = $id_card;
			$this->data['id_card_items'] = $this->hr_model->getIDCardItems($id);
			$this->data['positions'] = $this->hr_model->getPositionByBiller($id_card->biller_id);
			$this->data['departments'] = $this->hr_model->getDepartmentByBiller($id_card->biller_id);
			if($id_card->department_id){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($id_card->department_id);
			}
			$this->data['sample_id_cards'] = $this->hr_model->getIDCardSamples();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/id_cards'), 'page' => lang('id_cards')), array('link' => '#', 'page' => lang('edit_id_card')));
            $meta = array('page_title' => lang('edit_id_card'), 'bc' => $bc);
            $this->page_construct('hr/edit_id_card', $meta, $this->data);
        }
	}
	public function delete_id_card($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteIDCard($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('id_card_deleted')]);
            }
            $this->session->set_flashdata('message', lang('id_card_deleted'));
            admin_redirect('welcome');
        }
    }
	function id_card_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_id_card');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$this->hr_model->deleteIDCard($id);
                    }
					$this->session->set_flashdata('message', $this->lang->line("id_card_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('id_card');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('invalid_date'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $id_card = $this->hr_model->getIDCardByID($id); 
						$user = $this->site->getUserByID($id_card->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($id_card->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($id_card->invalid_date));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($id_card->note));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($id_card->status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'id_card_list_' . date('Y_m_d_H_i_s');
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
	
	public function approve_id_card($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->updateIDCardStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('id_card_approved')]);
            }
            $this->session->set_flashdata('message', lang('benefit_approved'));
            admin_redirect('welcome');
        }
    }
	public function unapprove_id_card($id = null){
        $this->bpas->checkPermissions("approve_id_card", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->hr_model->updateIDCardStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('id_card_unapproved'));
        }else{
			
		}
		admin_redirect('hr/id_cards');
    }
	public function print_id_card($id){
		$this->bpas->checkPermissions("id_cards");
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$id_card = $this->hr_model->getIDCardByID($id);
		$this->data['id_card'] = $id_card;
		$this->data['id_card_items'] = $this->hr_model->getIDCardItems($id);
		$this->data['biller'] = $this->site->getCompanyByID($id_card->biller_id);
		$this->data['sample_id_card'] = $this->hr_model->getSampleIDCardByID($id_card->sample_id_card);
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/id_cards'), 'page' => lang('id_cards')), array('link' => '#', 'page' => lang('print_id_card')));
		$meta = array('page_title' => lang('print_id_card'), 'bc' => $bc);
		$this->page_construct('hr/print_id_card', $meta, $this->data);
	}
	
	public function view_employee_id_card($id = false){
		$this->bpas->checkPermissions("id_cards_report");
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$id_card_item = $this->hr_model->getIDCardItemByID($id);
		$this->data['id_card_items'] = $this->hr_model->getIDCardItems($id_card_item->id_card_id, $id);
		$id_card = $this->hr_model->getIDCardByID($id_card_item->id_card_id);
		$this->data['id_card'] = $id_card;
		$this->data['biller'] = $this->site->getCompanyByID($id_card->biller_id);
		$this->data['sample_id_card'] = $this->hr_model->getSampleIDCardByID($id_card->sample_id_card);
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/id_cards_report'), 'page' => lang('id_cards_report')), array('link' => '#', 'page' => lang('print_id_card')));
		$meta = array('page_title' => lang('print_id_card'), 'bc' => $bc);
		$this->page_construct('hr/print_id_card', $meta, $this->data);
	}
	
	public function id_cards_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['kpi_types'] = $this->hr_model->getKPITypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getPositionsByBiller($biller);
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($department);
			}
		}
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('id_cards_report')));
        $meta = array('page_title' => lang('id_cards_report'), 'bc' => $bc);
        $this->page_construct('hr/id_cards_report', $meta, $this->data);
	}
	public function getIDCardsReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('banks_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        if ($pdf || $xls) {
			$this->db->select("
									DATE_FORMAT(".$this->db->dbprefix('hr_id_cards').".date, '%Y-%m-%d') as issued_date,
									DATE_FORMAT(".$this->db->dbprefix('hr_id_cards').".invalid_date, '%Y-%m-%d') as invalid_date,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									hr_sample_id_cards.name as sample_id_card,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									hr_id_cards.id as id")
							->from("hr_id_cards")
							->join("hr_id_card_items","hr_id_card_items.id_card_id = hr_id_cards.id","inner")
							->join("hr_employees","hr_employees.id = hr_id_card_items.employee_id","left")
							->join("hr_sample_id_cards","hr_sample_id_cards.id = hr_id_cards.sample_id_card","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_id_card_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->join("users","users.id = hr_id_cards.created_by","left");
			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('id_cards_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('issued_date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('invalid_date'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('sample_id_card'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('created_by'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($data_row->issued_date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrsd($data_row->invalid_date));
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->sample_id_card);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
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

				$filename = 'id_cards_report_list_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
									DATE_FORMAT(".$this->db->dbprefix('hr_id_cards').".date, '%Y-%m-%d') as issued_date,
									DATE_FORMAT(".$this->db->dbprefix('hr_id_cards').".invalid_date, '%Y-%m-%d') as invalid_date,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									hr_sample_id_cards.name as sample_id_card,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									hr_id_card_items.id as id")
							->from("hr_id_cards")
							->group_by("hr_id_card_items.id")
							->join("hr_id_card_items","hr_id_card_items.id_card_id = hr_id_cards.id","inner")
							->join("hr_employees","hr_employees.id = hr_id_card_items.employee_id","left")
							->join("hr_sample_id_cards","hr_sample_id_cards.id = hr_id_cards.sample_id_card","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_id_card_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->join("users","users.id = hr_id_cards.created_by","left");
			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }

	public function print_kpi(){
		$this->bpas->checkPermissions('kpi_add');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['kpi_types'] = $this->hr_model->getKPITypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : false;
		$position = $this->input->post('position') ? $this->input->post('position') : false;
		$department = $this->input->post('department') ? $this->input->post('department') : false;
		$group = $this->input->post('group') ? $this->input->post('group') : false;
		$employee = $this->input->post('employee') ? $this->input->post('employee') : false;
		$kpi_type = $this->input->post('kpi_type') ? $this->input->post('kpi_type') : false;
		$y_month = $this->input->post('month') ? $this->input->post('month') : false;
		if($y_month){
			$y_month = explode("/",$y_month);
			$year = $y_month[1];
		}else{
			$year = date("Y");
		}
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getPositionsByBiller($biller);
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($department);
			}
		}
		$status = "active";
		if($this->input->post('month')){
			$this->data["employees"] = $this->hr_model->getKPIEmployee($biller,$position,$department,$group,$employee,$kpi_type,$year,$status);
		}else{
			$this->data["employees"] = false;
		}
		$this->data["kpi_questions"] = $this->hr_model->getKPIQuestions();
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('print_kpi')));
        $meta = array('page_title' => lang('print_kpi'), 'bc' => $bc);
        $this->page_construct('hr/print_kpi', $meta, $this->data);
	}
	
	public function salary_reviews($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('salary_reviews')));
        $meta = array('page_title' => lang('salary_reviews'), 'bc' => $bc);
        $this->page_construct('hr/salary_reviews', $meta, $this->data);
	}
	public function getSalaryReviews($biller_id = false){
		$this->bpas->checkPermissions("salary_reviews");
        $edit_link = anchor('admin/hr/edit_salary_review/$1', '<i class="fa fa-edit"></i> ' . lang('edit_salary_review'), ' class="edit_salary_review"');
        $delete_link = "<a href='#' class='delete_salary_review po' title='<b>" . $this->lang->line("delete_salary_review") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_salary_review/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_salary_review') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['hr-approve_salary_review']){
			$approve_link = "<a href='#' class='po approve_salary_review' title='" . $this->lang->line("approve_salary_review") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/approve_salary_review/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_salary_review') . "</a>";
			
			$unapprove_link = "<a href='#' class='po unapprove_salary_review' title='" . $this->lang->line("unapprove_salary_review") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/unapprove_salary_review/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('unapprove_salary_review') . "</a>";
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
		$this->datatables->select("	hr_salary_reviews.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('hr_salary_reviews').".date, '%Y-%m-%d %T') as date,
									hr_salary_reviews.month,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									note,
									hr_salary_reviews.status,
									attachment
									")
							->from("hr_salary_reviews")
							->join("users","users.id = hr_salary_reviews.created_by","left")
							;
		if ($biller_id) {
            $this->datatables->where("hr_salary_reviews.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_salary_reviews.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_salary_reviews.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	
	function salary_review_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_salary_review');
                    $deleted = 0;
					foreach ($_POST['val'] as $id) {
						$salary = $this->hr_model->getSalaryReviewByID($id);
						if($salary->status == "pending"){
							$deleted = 1;
							$this->hr_model->deleteSalaryReview($id);
						}
                    }
					if($deleted==1){
						$this->session->set_flashdata('message', $this->lang->line("salary_review_deleted"));
					}else{
						$this->session->set_flashdata('error', $this->lang->line("salary_review_cannot_delete"));
					}
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('salary_review');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('month'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $salary = $this->hr_model->getSalaryReviewByID($id); 
						$user = $this->site->getUserByID($salary->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($salary->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $salary->month);
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($salary->note));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($salary->status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
					$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salary_review_list_' . date('Y_m_d_H_i_s');
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
	
	
	public function add_salary_review(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-salary_reviews_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$month = $this->input->post('month') ? $this->input->post('month') : null;
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$old_salary = $this->bpas->formatDecimal($_POST['old_salary'][$r]);
				$old_addition = $_POST['old_addition'][$r];
				$result = $_POST['result'][$r];
				$increase_salary = $_POST['increase_salary'][$r];
				$new_salary = $this->bpas->formatDecimal($_POST['new_salary'][$r]);
				$gross_salary = $this->bpas->formatDecimal($_POST['gross_salary'][$r]);
				$new_addition = null;
				
				if(isset($_POST['new_addition'][$employee_id]) && $_POST['new_addition'][$employee_id]){
					$new_addition = json_encode($_POST['new_addition'][$employee_id]);
				}
				$items[] = array(
								"employee_id" => $employee_id,
								"old_salary" => $old_salary,
								"old_addition" => $old_addition,
								"result" => $result,
								"increase_salary" => $increase_salary,
								"new_salary" => $new_salary,
								"new_addition" => $new_addition,
								"gross_salary" => $gross_salary
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'status' => "pending",
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
		if ($this->form_validation->run() == true && $this->hr_model->addSalaryReview($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_review_added"));          
			admin_redirect('hr/salary_reviews');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['additions'] = $this->hr_model->getAllAdditions();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/salary_reviews'), 'page' => lang('salary_reviews')), array('link' => '#', 'page' => lang('add_salary_review')));
            $meta = array('page_title' => lang('add_salary_review'), 'bc' => $bc);
            $this->page_construct('hr/add_salary_review', $meta, $this->data);
        }
	}
	
	
	public function edit_salary_review($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-salary_reviews_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$month = $this->input->post('month') ? $this->input->post('month') : null;
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$old_salary = $this->bpas->formatDecimal($_POST['old_salary'][$r]);
				$old_addition = $_POST['old_addition'][$r];
				$result = $_POST['result'][$r];
				$increase_salary = $_POST['increase_salary'][$r];
				$new_salary = $this->bpas->formatDecimal($_POST['new_salary'][$r]);
				$gross_salary = $this->bpas->formatDecimal($_POST['gross_salary'][$r]);
				$new_addition = null;
				
				if(isset($_POST['new_addition'][$employee_id]) && $_POST['new_addition'][$employee_id]){
					$new_addition = json_encode($_POST['new_addition'][$employee_id]);
				}
				$items[] = array(
								"salary_id" => $id,
								"employee_id" => $employee_id,
								"old_salary" => $old_salary,
								"old_addition" => $old_addition,
								"result" => $result,
								"increase_salary" => $increase_salary,
								"new_salary" => $new_salary,
								"new_addition" => $new_addition,
								"gross_salary" => $gross_salary
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'month' => $month,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
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
		if ($this->form_validation->run() == true && $this->hr_model->updateSalaryReview($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("salary_review_updated"));          
			admin_redirect('hr/salary_reviews');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$salary = $this->hr_model->getSalaryReviewByID($id);
			$this->data['salary'] = $salary;
			$this->data['salary_items'] = $this->hr_model->getSalaryReviewItems($id);
			$this->data['positions'] = $this->hr_model->getPositionByBiller($salary->biller_id);
			$this->data['departments'] = $this->hr_model->getDepartmentByBiller($salary->biller_id);
			if($salary->department_id){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($salary->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$this->data['additions'] = $this->hr_model->getAllAdditions();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/salary_reviews'), 'page' => lang('salary_reviews')), array('link' => '#', 'page' => lang('edit_salary_review')));
            $meta = array('page_title' => lang('edit_salary_review'), 'bc' => $bc);
            $this->page_construct('hr/edit_salary_review', $meta, $this->data);
        }
	}
	
	public function delete_salary_review($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteSalaryReview($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('salary_review_deleted')]);
            }
            $this->session->set_flashdata('message', lang('salary_review_deleted'));
            admin_redirect('welcome');
        }
    }
	
	public function approve_salary_review($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->updateSalaryReviewStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('salary_review_approved')]);
               
            }
            $this->session->set_flashdata('message', lang('salary_review_approved'));
            admin_redirect('welcome');
        }
    }
	
	public function unapprove_salary_review($id = null){
        $this->bpas->checkPermissions("approve_salary_review", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->updateSalaryReviewStatus($id,"pending")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('salary_review_unapproved')]);
               
            }
            $this->session->set_flashdata('message', lang('salary_review_unapproved'));
            admin_redirect('welcome');
        }
    }
	
	
	public function get_salary_review_employee(){
		$biller_id = $this->input->get('biller_id');
		$position_id = $this->input->get('position_id');
		$department_id = $this->input->get('department_id');
		$group_id = $this->input->get('group_id');
		$month = $this->input->get('month');
		$edit_id = $this->input->get('edit_id') ? $this->input->get('edit_id') : false;
		$employees = $this->hr_model->getSalaryReviewEmployee($biller_id,$position_id,$department_id,$group_id,$month,$edit_id);
		$review_employees = false;
		if($employees){
			$additions = $this->hr_model->getAllAdditions();
			foreach($employees as $employee){
				$emp_addtions = false;
				$employee_additions = false;
				if(json_decode($employee->additions)){
					foreach(json_decode($employee->additions) as $index => $emp_addtion){
						$emp_addtions[$index] = $emp_addtion;
					}
				}
				if($additions){
					foreach($additions as $addition){
						$amount = 0;
						if(isset($emp_addtions[$addition->id])){
							$amount = $emp_addtions[$addition->id];
						}
						$employee_additions[] = array("id"=>$addition->id,"value"=>$amount);
					}
				}
				$employee->emp_addtions = $employee_additions;
				$review_employees[] = $employee;
			}
		}
		echo json_encode($review_employees);
	}
	
	public function modal_view_salary_review($id = false){
		$this->bpas->checkPermissions('salary_reviews', true);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$salary = $this->hr_model->getSalaryReviewByID($id);
		$this->data['salary'] = $salary;
		$this->data['additions'] = $this->hr_model->getAllAdditions();
        $this->data['salary_items'] = $this->hr_model->getSalaryReviewItems($id);
        $this->data['biller'] = $this->site->getCompanyByID($salary->biller_id);
        $this->data['created_by'] = $this->site->getUser($salary->created_by);
        $this->load->view($this->theme . 'hr/modal_view_salary_review', $this->data);
	}
	
	
	public function salary_reviews_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getPositionsByBiller($biller);
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($department);
			}
		}
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('salary_reviews_report')));
        $meta = array('page_title' => lang('salary_reviews_report'), 'bc' => $bc);
        $this->page_construct('hr/salary_reviews_report', $meta, $this->data);
	}
	public function getSalaryReviewsReport($pdf = NULL, $xls = NULL){
        $this->bpas->checkPermissions('salary_reviews_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$month = $this->input->get('month') ? $this->input->get('month') : NULL;
        if ($pdf || $xls) {
			$this->db->select("	
								hr_salary_reviews.month,
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								hr_salary_review_items.old_salary,
								hr_salary_review_items.new_salary,
								hr_salary_review_items.gross_salary,
								hr_salary_reviews.status,
								hr_salary_reviews.id as id
								")
						->from("hr_salary_review_items")
						->join("hr_salary_reviews","hr_salary_reviews.id = hr_salary_review_items.salary_id","inner")
						->join("hr_employees","hr_employees.id = hr_salary_review_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_salary_review_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("hr_salary_review_items.id");

			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
			if ($month) {
                $this->db->where('hr_salary_reviews.month', $month);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('salary_reviews_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('old_salary'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('new_salary'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('gross_salary'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('status'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatMoney($data_row->old_salary));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatMoney($data_row->new_salary));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatMoney($data_row->gross_salary));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($data_row->status));
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

				$filename = 'salary_reviews_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
									hr_salary_reviews.month,
									hr_employees.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
									hr_positions.name as position,
									hr_departments.name as department,
									hr_groups.name as group,
									hr_salary_review_items.old_salary,
									hr_salary_review_items.new_salary,
									hr_salary_review_items.gross_salary,
									hr_salary_reviews.status,
									hr_salary_reviews.id as id
									")
							->from("hr_salary_review_items")
							->join("hr_salary_reviews","hr_salary_reviews.id = hr_salary_review_items.salary_id","inner")
							->join("hr_employees","hr_employees.id = hr_salary_review_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_salary_review_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("hr_salary_review_items.id");

			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
			if ($month) {
                $this->datatables->where('hr_salary_reviews.month', $month);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	public function awards($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('awards')));
        $meta = array('page_title' => lang('awards'), 'bc' => $bc);
        $this->page_construct('hr/awards', $meta, $this->data);
	}
	public function getAwards($biller_id = false){
		$this->bpas->checkPermissions("awards");
        $edit_link = anchor('admin/hr/edit_award/$1', '<i class="fa fa-edit"></i> ' . lang('edit_award'), ' class="edit_id_card"');
        $delete_link = "<a href='#' class='delete_award po' title='<b>" . $this->lang->line("delete_award") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_award/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_award') . "</a>";
		

		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("hr_award.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('hr_award').".date, '%Y-%m-%d %T') as date,
									{$this->db->dbprefix('hr_award')}.award_month as invalid_date,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									note,
									custom_field.name,
									attachment
									")
							->from("hr_award")
							->join("users","users.id = hr_award.created_by","left")
							->join("custom_field","custom_field.id = hr_award.award_type","left")
							;
		if ($biller_id) {
            $this->datatables->where("hr_award.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_award.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_award.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_award(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$month = $this->input->post('month');
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$award_type = $this->input->post('award_type') ? $this->input->post('award_type') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$items[] = array(
								"employee_id" => $employee_id
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'award_month' => $month,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'award_type' => $award_type,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'gift' => 	$this->input->post('gift'),
                'cash' => $this->input->post('cash'),
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
		if ($this->form_validation->run() == true && $this->hr_model->addAward($data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("award_added"));          
			admin_redirect('hr/awards');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['sample_id_cards'] = $this->hr_model->getIDCardSamples();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/awards'), 'page' => lang('add_award')), array('link' => '#', 'page' => lang('add_award')));
            $meta = array('page_title' => lang('add_award'), 'bc' => $bc);
            $this->page_construct('hr/add_award', $meta, $this->data);
        }
	}
	public function edit_award($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('month', $this->lang->line("month"), 'required');
		$this->form_validation->set_rules('award_type', $this->lang->line("award_type"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$month = $this->input->post('month');
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$award_type = $this->input->post('award_type') ? $this->input->post('award_type') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
			$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$items[] = array(
								"employee_id" => $employee_id,
								"award_id" => $id
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}
			$data = array(
				'date' => $date,
                'award_month' => $month,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'award_type' => $award_type,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
				'created_by' => $this->session->userdata('user_id'),
				'created_at' => date('Y-m-d H:i:s'),
				'gift' => 	$this->input->post('gift'),
                'cash' => $this->input->post('cash'),
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
		if ($this->form_validation->run() == true && $this->hr_model->updateAward($id,$data,$items)) {	
            $this->session->set_flashdata('message', $this->lang->line("awards_updated"));          
			admin_redirect('hr/awards');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$id_card = $this->hr_model->getAwardByID($id);
			$this->data['id_card'] = $id_card;
			$this->data['id_card_items'] = $this->hr_model->getWardItems($id);
			$this->data['positions'] = $this->hr_model->getPositionByBiller($id_card->biller_id);
			$this->data['departments'] = $this->hr_model->getDepartmentByBiller($id_card->biller_id);
			if($id_card->department_id){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($id_card->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/id_cards'), 'page' => lang('award')), array('link' => '#', 'page' => lang('edit_award')));
            $meta = array('page_title' => lang('edit_id_card'), 'bc' => $bc);
            $this->page_construct('hr/edit_award', $meta, $this->data);
        }
	}
	public function delete_award($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteAward($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('award_deleted')]);
         
            }
            $this->session->set_flashdata('message', lang('award_deleted'));
            admin_redirect('welcome');
        }
    }
	public function transfers($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('transfers')));
        $meta = array('page_title' => lang('transfers'), 'bc' => $bc);
        $this->page_construct('hr/transfers', $meta, $this->data);
	}
	public function getTransfers($biller_id = false){
		$this->bpas->checkPermissions("id_cards");

        $edit_link = anchor('admin/hr/edit_transfer/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'), ' class="edit_transfer"');
        $delete_link = "<a href='#' class='delete_transfer po' title='<b>" . $this->lang->line("delete_transfer") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_transfer/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_transfer') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['hr-approve_transfer']){
			$approve_link = "<a href='#' class='po approve_transfer' title='" . $this->lang->line("approve_transfer") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/approve_transfer/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_transfer') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_transfer' title='<b>" . $this->lang->line("unapprove_id_card") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/unapprove_transfer/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_transfer') . "</a>";
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
		$this->datatables->select("	hr_transfers.id as id, 
					DATE_FORMAT(".$this->db->dbprefix('hr_transfers').".date, '%Y-%m-%d %T') as date,
					{$this->db->dbprefix('hr_departments')}.name as from_department,
					dt.name as to_department,
					note,
					hr_transfers.status,
					attachment
					")
			->from("hr_transfers")
			->join("hr_departments","hr_departments.id = hr_transfers.from_department","left")
			->join("hr_departments dt","dt.id = hr_transfers.to_department","left")
			;
		if ($biller_id) {
            $this->datatables->where("hr_transfers.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_transfers.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_transfers.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_transfer()
    {
        $this->bpas->checkPermissions();
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
		
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin || $this->bpas->GP['travel-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$items = false;
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'employee_id' => $employee_id,
						'description' => $description,
					);
				}
            }
            $data = array(
                'date' 				=> $date,
				'biller_id' 		=> $biller_id,
				'from_department'	=> $this->input->post('from_department'),
				'to_department'		=> $this->input->post('to_department'),
                'note' 				=> $note,
                'created_by' 		=> $this->session->userdata('user_id'),
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->hr_model->addTransfer($data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("transfer_added"));
            admin_redirect('hr/transfers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('add_transfer')), array('link' => admin_url('hr/add_transfer'), 'page' => lang('add_training')), array('link' => '#', 'page' => lang('add_transfer')));
            $meta = array('page_title' => lang('add_transfer'), 'bc' => $bc);
            $this->page_construct('hr/add_transfer', $meta, $this->data);
        }
    }
    public function edit_transfer($id)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin || $this->bpas->GP['attendances-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$items = false;
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'transfer_id' => $id,
						'employee_id' => $employee_id,
						'description' => $description,
					);
				}
            }
 

            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }

            $data = array(
            	'date' 				=> $date,
				'biller_id' 		=> $biller_id,
				'from_department'	=> $this->input->post('from_department'),
				'to_department'		=> $this->input->post('to_department'),
                'note' 				=> $note,
				'updated_at'    	=> date('Y-m-d H:i:s'),
                'updated_by'    	=> $this->session->userdata('user_id'),
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->hr_model->updateTransfer($id, $data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("transfer_updated"));
            admin_redirect('hr/transfers');
        } else {
			$transfer = $this->hr_model->getTransferByID($id);
			
            $items = $this->hr_model->getTransferItems($id);
            krsort($items);
            $c = rand(100000, 9999999);
            foreach ($items as $item) {
				$item->id = $item->employee_id;
                $pr[$c] = array('id' => $c, 'item_id' => $item->id, 'label' => $item->lastname .' '.$item->firstname. " (" . $item->empcode . ")",'row' => $item);
                $c++;
            }
            $this->data['trasfer'] = $transfer;
            $this->data['trasfer_items'] = json_encode($pr);
            $this->data['departments'] = $this->hr_model->getDepartmentsByBilller();
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->session->set_userdata('remove_dfls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('hr')),array('link' => site_url('hr/edit_transfer'), 'page' => lang('travels')), array('link' => '#', 'page' => lang('edit_transfer')));
            $meta = array('page_title' => lang('edit_transfer'), 'bc' => $bc);
            $this->page_construct('hr/edit_transfer', $meta, $this->data);

        }
    }
    public function delete_transfer($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteTransfer($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('transfer_deleted')]);
            }
            $this->session->set_flashdata('message', lang('travel_deleted'));
            admin_redirect('hr/transfers');
        }
    }
    public function approve_transfer($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->updateTransferStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('transfer_approved')]);
            }
            $this->session->set_flashdata('message', lang('transfer_approved'));
            admin_redirect('welcome');
        }
    }
    public function unapprove_transfer($id = null){
        $this->bpas->checkPermissions("unapprove_transfer", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->hr_model->updateTransferStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('transfer_unapproved'));
        }else{
			
		}
		admin_redirect('hr/transfers');
    }
    function transfer_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_id_card');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$this->hr_model->deleteIDCard($id);
                    }
					$this->session->set_flashdata('message', $this->lang->line("id_card_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('id_card');
					$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
					$this->excel->getActiveSheet()->SetCellValue('B1', lang('invalid_date'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('created_by'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $id_card = $this->hr_model->getIDCardByID($id); 
						$user = $this->site->getUserByID($id_card->created_by);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($id_card->date));
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($id_card->invalid_date));
						$this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->last_name." ".$user->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($id_card->note));
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($id_card->status));
						$row++;
                    }
					$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'id_card_list_' . date('Y_m_d_H_i_s');
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
	public function resignation($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('resignation')));
        $meta = array('page_title' => lang('resignation'), 'bc' => $bc);
        $this->page_construct('hr/resignation', $meta, $this->data);
	}
	public function getResignation($biller_id = false){
		$this->bpas->checkPermissions("resignation");
		
        $edit_link = anchor('admin/hr/edit_resignation/$1', '<i class="fa fa-edit"></i> ' . lang('edit_resignation'), ' class="edit_resignation"');
        $delete_link = "<a href='#' class='delete_resignation po' title='<b>" . $this->lang->line("delete_resignation") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_resignation/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_resignation') . "</a>";

        if($this->Admin || $this->Owner || $this->GP['hr-approved_resignation']){
			$approve_link = "<a href='#' class='po approved_resignation' title='" . $this->lang->line("approved_resignation") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/approved_resignation/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approved_resignation') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_resignation' title='<b>" . $this->lang->line("unapprove_resignation") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/unapprove_resignation/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_resignation') . "</a>";
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
		$this->datatables->select("	{$this->db->dbprefix('hr_resignation')}.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".date, '%Y-%m-%d %T') as date,
									{$this->db->dbprefix('hr_employees')}.empcode,
									CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as created_by,
									{$this->db->dbprefix('hr_resignation')}.notice_date,
									{$this->db->dbprefix('hr_resignation')}.resignation_date,
									{$this->db->dbprefix('hr_resignation')}.note,
									{$this->db->dbprefix('hr_resignation')}.returned_asset,
									status,
									attachment
									")
							->from("hr_resignation")
							->join("hr_employees","hr_employees.id = hr_resignation.employee_id","left");
						//->join("custom_field","custom_field.id = hr_resignation.warning_type","left")
							;
		if ($biller_id) {
            $this->datatables->where("hr_resignation.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_resignation.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_resignation.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_resignation(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('notice_date', $this->lang->line("notice_date"), 'required');
		$this->form_validation->set_rules('resignation_date', $this->lang->line("resignation_date"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$notice_date 		= $this->bpas->fsd($this->input->post('notice_date'));
			$resignation_date 	= $this->bpas->fsd($this->input->post('resignation_date'));
			$biller_id 			= $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id 		= $this->input->post('position') ? $this->input->post('position') : null;
			$department_id 		= $this->input->post('department') ? $this->input->post('department') : null;
			
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
			/*$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$items[] = array(
								"employee_id" => $employee_id
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}*/
			$data = array(
				'date' 				=> $date,
                'notice_date' 		=> $notice_date,
                'resignation_date'  => $resignation_date,
                'biller_id' 		=> $biller_id,
                'position_id' 		=> $position_id,
				'department_id' 	=> $department_id,
				'group_id' 			=> $group_id,
				'note' 				=> $note,
				'status' 			=> $status,
				'created_by' 		=> $this->session->userdata('user_id'),
				'created_at' 		=> date('Y-m-d H:i:s'),
				'returned_asset' 	=> $this->input->post('returned_asset'),
				'employee_id' 		=> $this->input->post('employee')
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
		if ($this->form_validation->run() == true && $this->hr_model->addResignation($data)) {	
            $this->session->set_flashdata('message', $this->lang->line("resignation_added"));          
			admin_redirect('hr/resignation');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['sample_id_cards'] = $this->hr_model->getIDCardSamples();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/resignation'), 'page' => lang('resignation')), array('link' => '#', 'page' => lang('add_resignation')));
            $meta = array('page_title' => lang('add_resignation'), 'bc' => $bc);
            $this->page_construct('hr/add_resignation', $meta, $this->data);
        }
	}
	public function edit_resignation($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('notice_date', $this->lang->line("notice_date"), 'required');
		$this->form_validation->set_rules('resignation_date', $this->lang->line("resignation_date"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$notice_date = $this->bpas->fsd($this->input->post('notice_date'));
			$resignation_date = $this->bpas->fsd($this->input->post('resignation_date'));

			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
	
			$data = array(
				'date' => $date,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
				'notice_date' 		=> $notice_date,
                'resignation_date' => $resignation_date,
                'employee_id' => $this->input->post('employee'),
				'created_by' => $this->session->userdata('user_id'),
				'returned_asset' 	=> $this->input->post('returned_asset'),
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
		if ($this->form_validation->run() == true && $this->hr_model->updateResignation($id,$data)) {	
            $this->session->set_flashdata('message', $this->lang->line("resignation_updated"));          
			admin_redirect('hr/resignation');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$id_card = $this->hr_model->getResignationByID($id);
			$this->data['id_card'] = $id_card;
			$this->data['positions'] = $this->hr_model->getPositionByBiller($id_card->biller_id);
			$this->data['departments'] = $this->hr_model->getDepartmentByBiller($id_card->biller_id);
			if($id_card->department_id){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($id_card->department_id);
			}
			$this->data['employees'] = $this->hr_model->getEmployees();
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/resignation'), 'page' => lang('resignation')), array('link' => '#', 'page' => lang('edit_resignation')));
            $meta = array('page_title' => lang('edit_resignation'), 'bc' => $bc);
            $this->page_construct('hr/edit_resignation', $meta, $this->data);
        }
	}
	public function delete_resignation($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteResignation($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('resignation_deleted')]);
               
            }
            $this->session->set_flashdata('message', lang('resignation_deleted'));
            admin_redirect('welcome');
        }
    }
	public function travels($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('travels')));
        $meta = array('page_title' => lang('travels'), 'bc' => $bc);
        $this->page_construct('hr/travels', $meta, $this->data);
	}
	public function getTravels($biller_id = false){
		$this->bpas->checkPermissions("travels");

		$view_link = anchor('admin/hr/modal_view_travel/$1', '<i class="fa fa-money"></i> ' . lang('view_detail'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_detail" data-target="#myModal"');

        $edit_link = anchor('admin/hr/edit_travel/$1', '<i class="fa fa-edit"></i> ' . lang('edit_travel'), ' class="edit_travel"');
        $delete_link = "<a href='#' class='delete_travel po' title='<b>" . $this->lang->line("delete_travel") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_travel/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_travel') . "</a>";
		
		if($this->Admin || $this->Owner || $this->GP['hr-approve_travel']){
			$approve_link = "<a href='#' class='po approve_travel' title='" . $this->lang->line("approve_travel") . "' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/approve_travel/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
			. lang('approve_travel') . "</a>";

			$unapprove_link = "<a href='#' class='po unapprove_travel' title='<b>" . $this->lang->line("unapprove_travel") . "</b>' data-content=\"<p>"
			. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/unapprove_travel/$1') . "'>"
			. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
			. lang('unapprove_travel') . "</a>";
		}
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_link . '</li>
						<li>' . $approve_link . '</li>
						<li>' . $unapprove_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("
				hr_travels.id as id, 
				DATE_FORMAT(".$this->db->dbprefix('hr_travels').".date, '%Y-%m-%d %T') as date,
				hr_travels.purpose as purpose,
				hr_travels.place as place, 
				{$this->db->dbprefix('hr_travels')}.start_date as start_date,
				{$this->db->dbprefix('hr_travels')}.end_date as end_date,
				hr_travels.budget as budget, 
				hr_travels.status
				")
		->from("hr_travels")
		->join("users","users.id = hr_travels.created_by","left");
		if ($biller_id) {
            $this->datatables->where("hr_travels.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_travels.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_travels.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_travel()
    {
        $this->bpas->checkPermissions();
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
		$this->form_validation->set_rules('purpose', lang("purpose"), 'required');
		
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin || $this->bpas->GP['travel-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$items = false;
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'employee_id' => $employee_id,
						'description' => $description,
					);
				}
            }
			
 
            $data = array(
                'date' 			=> $date,
				'biller_id' 	=> $biller_id,
				'purpose'		=> $this->input->post('purpose'),
				'place'			=> $this->input->post('place'),
				'travel_mode'	=> $this->input->post('travel_mode'),
				'budget'		=> $this->bpas->formatDecimal($this->input->post('budget')),
				'start_date'	=> $this->bpas->fsd($this->input->post('start_date')),
				'end_date'		=> $this->bpas->fsd($this->input->post('end_date')),
                'note' 			=> $note,
                'created_by' 	=> $this->session->userdata('user_id'),
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->hr_model->addTravel($data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("travel_added"));
            admin_redirect('hr/travels');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('add_travel')), array('link' => admin_url('hr/add_travel'), 'page' => lang('add_training')), array('link' => '#', 'page' => lang('add_travel')));
            $meta = array('page_title' => lang('add_travel'), 'bc' => $bc);
            $this->page_construct('hr/add_travel', $meta, $this->data);
        }
    }
    public function edit_travel($id)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin || $this->bpas->GP['attendances-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$items = false;
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'travel_id' => $id,
						'employee_id' => $employee_id,
						'description' => $description,
					);
				}
            }
 

            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }

            $data = array(
            	'date' 			=> $date,
				'biller_id' 	=> $biller_id,
				'purpose'		=> $this->input->post('purpose'),
				'place'			=> $this->input->post('place'),
				'travel_mode'	=> $this->input->post('travel_mode'),
				'budget'		=> $this->bpas->formatDecimal($this->input->post('budget')),
				'start_date'	=> $this->bpas->fsd($this->input->post('start_date')),
				'end_date'		=> $this->bpas->fsd($this->input->post('end_date')),
                'note' 			=> $note,
				'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => $this->session->userdata('user_id'),
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->hr_model->updateTravel($id, $data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("travel_updated"));
            admin_redirect('hr/travels');
        } else {
			$day_off = $this->hr_model->getTravelByID($id);
			
            $items = $this->hr_model->getTravelItems($id);
            krsort($items);
            $c = rand(100000, 9999999);
            foreach ($items as $item) {
				$item->id = $item->employee_id;
                $pr[$c] = array('id' => $c, 'item_id' => $item->id, 'label' => $item->lastname .' '.$item->firstname. " (" . $item->empcode . ")",'row' => $item);
                $c++;
            }
            $this->data['travel'] = $day_off;
            $this->data['travel_items'] = json_encode($pr);
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->session->set_userdata('remove_dfls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('hr')),array('link' => site_url('hr/edit_travel'), 'page' => lang('travels')), array('link' => '#', 'page' => lang('edit_travel')));
            $meta = array('page_title' => lang('edit_travel'), 'bc' => $bc);
            $this->page_construct('hr/edit_travel', $meta, $this->data);

        }
    }
    public function delete_travel($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteTravel($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('travel_deleted')]);
            }
            $this->session->set_flashdata('message', lang('travel_deleted'));
            admin_redirect('hr/travels');
        }
    }
    public function modal_view_travel($id)
    {
        $this->bpas->checkPermissions('travels', TRUE);
        $day_off = $this->hr_model->getTravelByID($id);
        $this->data['travel'] = $day_off;
		$this->data['biller'] = $this->site->getCompanyByID($day_off->biller_id);
        $this->data['rows'] = $this->hr_model->getTravelItems($id);
		$this->data['created_by'] = $this->site->getUser($day_off->created_by);
        $this->load->view($this->theme.'hr/modal_view_travel', $this->data);
    }
    public function approve_travel($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->updateTravelStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('travel_approved')]);
            }
            $this->session->set_flashdata('message', lang('travel_approved'));
            admin_redirect('welcome');
        }
    }
	public function unapprove_travel($id = null){
        $this->bpas->checkPermissions("unapprove_travel", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->hr_model->updateTravelStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('travel_unapproved'));
        }else{
			
		}
		admin_redirect('hr/travels');
    }
	public function complaints($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('id_cards')));
        $meta = array('page_title' => lang('id_cards'), 'bc' => $bc);
        $this->page_construct('hr/id_cards', $meta, $this->data);
	}
	public function warning($biller_id = false){
		$this->bpas->checkPermissions();
		$this->data['billers'] = $this->site->getBillers();
		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('warning')));
        $meta = array('page_title' => lang('warning'), 'bc' => $bc);
        $this->page_construct('hr/warning', $meta, $this->data);
	}
	public function getWarning($biller_id = false){
		$this->bpas->checkPermissions("warning");
		
        $edit_link = anchor('admin/hr/edit_warning/$1', '<i class="fa fa-edit"></i> ' . lang('edit_warning'), ' class="edit_warning"');
        $delete_link = "<a href='#' class='delete_warning po' title='<b>" . $this->lang->line("delete_warning") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_warning/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_warning') . "</a>";
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("	{$this->db->dbprefix('hr_warning')}.id as id, 
									DATE_FORMAT(".$this->db->dbprefix('hr_warning').".date, '%Y-%m-%d %T') as date,
									{$this->db->dbprefix('hr_warning')}.name,
									CONCAT(".$this->db->dbprefix('users').".last_name,' ',".$this->db->dbprefix('users').".first_name) as created_by,
									note,
									custom_field.name as type,
									attachment
									")
							->from("hr_warning")
							->join("users","users.id = hr_warning.created_by","left")
							->join("custom_field","custom_field.id = hr_warning.warning_type","left")
							;
		if ($biller_id) {
            $this->datatables->where("hr_warning.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_warning.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_warning.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_warning(){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('subject', $this->lang->line("subject"), 'required');
		$this->form_validation->set_rules('warning_type', $this->lang->line("warning_type"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$subject = $this->input->post('subject');
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$warning_type = $this->input->post('warning_type') ? $this->input->post('warning_type') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
			/*$items = false;
			$i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
			for ($r = 0; $r < $i; $r++) {
				$employee_id = $_POST['employee_id'][$r];
				$items[] = array(
								"employee_id" => $employee_id
							);
			}
			if (!$items) {
				$this->form_validation->set_rules('employee', lang("order_items"), 'required');
			} else {
				krsort($items);
			}*/
			$data = array(
				'date' => $date,
                'name' => $subject,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'warning_type' => $warning_type,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
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
		if ($this->form_validation->run() == true && $this->hr_model->addWarning($data)) {	
            $this->session->set_flashdata('message', $this->lang->line("warning_added"));          
			admin_redirect('hr/warning');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getBillers();
			$this->data['sample_id_cards'] = $this->hr_model->getIDCardSamples();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/warning'), 'page' => lang('warning')), array('link' => '#', 'page' => lang('add_warning')));
            $meta = array('page_title' => lang('add_warning'), 'bc' => $bc);
            $this->page_construct('hr/add_warning', $meta, $this->data);
        }
	}
	public function edit_warning($id = false){
		$this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
		$this->form_validation->set_rules('subject', $this->lang->line("subject"), 'required');
		$this->form_validation->set_rules('warning_type', $this->lang->line("warning_type"), 'required');
        if ($this->form_validation->run() == true) {
			if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
			$subject = $this->input->post('subject');
			$biller_id = $this->input->post('biller') ? $this->input->post('biller') : null;
			$position_id = $this->input->post('position') ? $this->input->post('position') : null;
			$department_id = $this->input->post('department') ? $this->input->post('department') : null;
			$warning_type = $this->input->post('warning_type') ? $this->input->post('warning_type') : null;
			$group_id = $this->input->post('group') ? $this->input->post('group') : null;
			$note = $this->input->post('note') ? $this->input->post('note') : null;
			$status = "pending";
	
			$data = array(
				'date' => $date,
                'name' => $subject,
                'biller_id' => $biller_id,
                'position_id' => $position_id,
				'department_id' => $department_id,
				'warning_type' => $warning_type,
				'group_id' => $group_id,
				'note' => $note,
				'status' => $status,
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
		if ($this->form_validation->run() == true && $this->hr_model->updateWarning($id,$data)) {	
            $this->session->set_flashdata('message', $this->lang->line("warning_updated"));          
			admin_redirect('hr/warning');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$id_card = $this->hr_model->getWarningByID($id);
			$this->data['id_card'] = $id_card;
			$this->data['positions'] = $this->hr_model->getPositionByBiller($id_card->biller_id);
			$this->data['departments'] = $this->hr_model->getDepartmentByBiller($id_card->biller_id);
			if($id_card->department_id){
				$this->data['groups'] = $this->hr_model->getGroupsByBiller($id_card->department_id);
			}
			$this->data['billers'] = $this->site->getBillers();
			$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr/warning'), 'page' => lang('warning')), array('link' => '#', 'page' => lang('edit_warning')));
            $meta = array('page_title' => lang('edit_warning'), 'bc' => $bc);
            $this->page_construct('hr/edit_warning', $meta, $this->data);
        }
	}
	public function delete_warning($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteWarning($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('warning_deleted')]);
            }
            $this->session->set_flashdata('message', lang('warning_deleted'));
            admin_redirect('welcome');
        }
    }

    public function teacher_salary_tax()
	{
		$data 		  = array();
		$currency 	  = $this->site->getCurrencyByCode("KHR");
		$monthly_rate = $this->input->get("monthly_rate");
		$net_salary   = $this->input->get("net_salary");
		$salary_tax   = ($this->input->get("salary_tax") * $currency->rate);
		
		$employee_id  = $this->input->get("employee_id");
		$employee 	  = $this->schools_model->getTeacherByID($employee_id);
		$salary_taxs  = $this->hr_model->getSalaryTaxCondition();
		$spouses 	  = $this->hr_model->getSpouseMemberByEmployeeID($employee_id);
		$childs 	  = $this->hr_model->getChildrenMemberByEmployeeID($employee_id);
		foreach($salary_taxs as $tax){
			if($employee->non_resident==0){
				$allowance 		 = (($spouses?count($spouses) : 0) + ($childs ? count($childs) : 0)) * 150000;
				$base_salary_tax = $salary_tax - $allowance;
				if($base_salary_tax <= $tax->max_salary && $base_salary_tax >= $tax->min_salary){
					$tax_on_salary = ($base_salary_tax * $tax->tax_percent) - $tax->reduce_tax;
					$data = array(
						"tax_percent" 	=> $tax->tax_percent,
						"reduce_tax"  	=> $tax->reduce_tax,
						"net_salary"	=> $net_salary,
						"tax_on_salary" => ($tax_on_salary / $currency->rate),
					); 
				}
			} else {
				// mutiply with 20% non-resident
				$tax_on_salary = ($salary_tax * 0.2);
				$data = array(
					"tax_percent" 	=> 0,
					"reduce_tax"  	=> 0,
					"net_salary"	=> $net_salary,
					"tax_on_salary" => ($tax_on_salary / $currency->rate),
				);
			}
		}
		echo json_encode($data);
	}
	function contribution_payment()
	{
		$this->bpas->checkPermissions('contribution_payment');
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('contribution_payment')));
		$meta = array('page_title' => lang('contribution_payment'), 'bc' => $bc);
		$this->page_construct('hr/contribution_payment', $meta, $this->data);
	}
	public function getConPayConditions()
	{
		$this->bpas->checkPermissions('tax_conditions');
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_payment_condition") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_con_payment/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_payment_condition') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_con_payment/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_payment_condition').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables->select("
				id, 
				min_salary,
				max_salary,
				contributory_wage,
				((contributory_wage * or_rate)/100) as tax_percent,
				((contributory_wage * hc_rate)/100) as hc_percent
				")
             ->from("hr_nssf_condition")
             ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	public function edit_con_payment($id = false)
	{
		$this->bpas->checkPermissions('tax_conditions');
		$this->form_validation->set_rules('min_salary', lang("min_salary"), 'required');
		$this->form_validation->set_rules('max_salary', lang("max_salary"), 'required');
		$this->form_validation->set_rules('tax_percent', lang("tax_percent"), 'required');
		$this->form_validation->set_rules('reduce_tax', lang("reduce_tax"), 'required');
		
		if ($this->form_validation->run() == true){
			$data = array(
						'min_salary'	=> $this->input->post('min_salary'),
						'max_salary'	=> $this->input->post('max_salary'),
						'contributory_wage'	=> $this->input->post('reduce_tax'),
						'or_rate'	=> $this->input->post('tax_percent'),
						'hc_rate'	=> $this->input->post('hc_rate')
						
					);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateConPaymentCondition($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("contribution_payment_condition_updated"));
            admin_redirect("hr/contribution_payment");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['row'] = $this->hr_model->getConPaymentConditionByID($id);
			$this->data['id'] = $id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_conpayment_condition', $this->data);	
		}	
	}
	
	public function delete_con_payment($id = null)
    {		
		$this->bpas->checkPermissions('tax_conditions');
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteConPaymentCondition($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('contribution_payment_condition_deleted')]);
				}
				$this->session->set_flashdata('message', lang('contribution_payment_condition_deleted'));
				admin_redirect('hr/contribution_payment');
			}
        }
    }
    function import_employee()
    {
        $this->bpas->checkPermissions('add');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["userfile"])) {
				$this->load->library('excel');
				$path = $_FILES["userfile"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$ex_code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$finger_id = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$nric_no = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$ex_l_name = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$ex_f_name = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
						$ex_lo_name = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
						$ex_fo_name = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
						$ex_gender = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
						$ex_dob = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
						$ex_phone = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
						$ex_email = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
						$ex_nationality = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
						$ex_address = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
						$non_resident = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
						$marital_status = $worksheet->getCellByColumnAndRow(14, $row)->getValue();

						if(trim($ex_code)!=''){
							$final[] = array(
								'empcode'   => $ex_code,
								'finger_id'  => $finger_id,
								'nric_no'    => $nric_no,
								'lastname'  => $ex_l_name,
								'firstname'   => $ex_f_name,
								'lastname_kh'   => $ex_lo_name,
								'firstname_kh'   => $ex_fo_name,
								'gender'   => $ex_gender,
								'dob'   => $ex_dob,
								'phone'   => $ex_phone,
								'email'   => $ex_email,
								'nationality'   => $ex_nationality,
								'address'   => $ex_address,
								'non_resident'   => $non_resident,
								'marital_status'   => $marital_status,
								'created_at'	 => date('Y-m-d H:i'),
								'created_by'	 => $this->session->userdata('user_id'),
							);
						}
					}
				}
			
                $rw = 2;
				$checkCode = false;
                foreach ($final as $csv_pr) {
                    if (!$this->hr_model->getEmployeeByCode(trim($csv_pr['empcode']))) {
						if(isset($checkCode[trim($csv_pr['empcode'])]) && $checkCode[trim($csv_pr['empcode'])]){
							$this->session->set_flashdata('error', lang("teacher_code") . " (" . $csv_pr['empcode'] . "). " . lang("code__exist") . " " . lang("line_no") . " " . $rw);
							admin_redirect("hr/import_employee");
						}
						$checkCode[trim($csv_pr['empcode'])] = true;
						$code[] = trim($csv_pr['empcode']);
						$finger_id[] = trim($csv_pr['finger_id']);
						$nric_no[] = trim($csv_pr['nric_no']);
						$lastname[] = trim($csv_pr['lastname']);
						$firstname[] = trim($csv_pr['firstname']);
						$lastname_other[] = trim($csv_pr['lastname_kh']);
						$firstname_other[] = trim($csv_pr['firstname_kh']);
						$gender[] = trim($csv_pr['gender']);
						$dob[] = $this->bpas->fsd(trim($csv_pr['dob']));
						$phone[] = trim($csv_pr['phone']);
						$email[] = trim($csv_pr['email']);
						$nationality[] = trim($csv_pr['nationality']);
						$address[] = trim($csv_pr['address']);
						$non_resident[] = trim($csv_pr['non_resident']);
						$marital_status[] = trim($csv_pr['marital_status']);
						$created_at[] = date('Y-m-d H:i');
						$created_by[] = $this->session->userdata('user_id');
                    }else{
                        $this->session->set_flashdata('error', lang("employee_code") . " (" . $csv_pr['empcode'] . "). " . lang("code__exist") . " " . lang("line_no") . " " . $rw);
						admin_redirect("hr/import_employee");
                    }
                    $rw++;
                }
            }

            $ikeys = array('empcode', 'finger_id', 'lastname', 'firstname', 'lastname_kh', 'firstname_kh', 'gender', 'dob', 'phone', 'email', 'nationality', 'address', 'non_resident','marital_status', 'created_at', 'created_by');
            $items = array();
            foreach (array_map(null,$code, $finger_id, $lastname, $firstname, $lastname_other, $firstname_other, $gender, $dob, $phone, $email, $nationality, $address, $non_resident,$marital_status, $created_at, $created_by) as $ikey => $value) {
                $items[] = array_combine($ikeys, $value);
            }
        }

        if ($this->form_validation->run() == true && $prs = $this->hr_model->importEmployee($items)) {
            $this->session->set_flashdata('message', lang("employee_added"));
            admin_redirect(admin_url('hr'));
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('employee')), array('link' => '#', 'page' => lang('import_employee')));
			$meta = array('page_title' => lang('import_employee'), 'bc' => $bc);
            $this->page_construct('hr/import_employee', $meta, $this->data);
        }
    }
    public function resignations_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('resignations_report')));
        $meta = array('page_title' => lang('resignations_report'), 'bc' => $bc);
        $this->page_construct('hr/resignations_report', $meta, $this->data);
	}
	public function getResignationsReport($xls = NULL){
        $this->bpas->checkPermissions('resignations_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$from_date = $this->input->get('from_date') ? $this->bpas->fsd($this->input->get('from_date')) : NULL;
		$to_date = $this->input->get('to_date') ? $this->bpas->fsd($this->input->get('to_date')) : NULL;
        if ($xls) {
			$this->db->select("	
							DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".date, '%Y-%m-%d %T') as date,
							hr_employees.empcode,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as full_name,
							hr_departments.name as department,
							hr_groups.name as group,
							hr_positions.name as position,
							DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".notice_date, '%Y-%m-%d') as notice_date,
							DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".resignation_date, '%Y-%m-%d') as resignation_date,
							hr_resignation.note,
							hr_resignation.attachment,
							hr_resignation.id as id
						")
				->from("hr_resignation")
				->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_resignation.employee_id","inner")
				->join("hr_employees","hr_employees.id = hr_resignation.employee_id","inner")
				->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
				->join("hr_groups","hr_employees_working_info.group_id = hr_groups.id","left")
				->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");

			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
			if ($from_date) {
                $this->db->where('hr_resignation.resignation_date >=', $from_date);
            }
			if ($to_date) {
                $this->db->where('hr_resignation.resignation_date <=', $to_date);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('resignations_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('notice_date'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('resignation_date'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('reason'));
                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->full_name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->hrld($data_row->notice_date));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->hrld($data_row->resignation_date));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->remove_tag($data_row->reason));
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
				$filename = 'resignations_report_list_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
            $this->datatables->select("	
										DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".date, '%Y-%m-%d %T') as date,
										hr_employees.empcode,
										CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as full_name,
										hr_departments.name as department,
										hr_groups.name as group,
										hr_positions.name as position,
										DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".notice_date, '%Y-%m-%d') as notice_date,
										DATE_FORMAT(".$this->db->dbprefix('hr_resignation').".resignation_date, '%Y-%m-%d') as resignation_date,
										hr_resignation.note,
										hr_resignation.attachment,
										hr_resignation.id as id
									")
							->from("hr_resignation")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_resignation.employee_id","inner")
							->join("hr_employees","hr_employees.id = hr_resignation.employee_id","inner")
							->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
							->join("hr_groups","hr_employees_working_info.group_id = hr_groups.id","left")
							->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left");

			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
			if ($from_date) {
                $this->datatables->where('hr_resignation.resignation_date >=', $from_date);
            }
			if ($to_date) {
                $this->datatables->where('hr_resignation.resignation_date <=', $to_date);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function contracts_report($alert = false){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['policies'] = $this->hr_model->getPolicies();
		$this->data['employee_types'] = $this->hr_model->getEmployeeTypes();
		$this->data['alert'] = $alert;
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			/*if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}*/
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('contracts_report')));
        $meta = array('page_title' => lang('contracts_report'), 'bc' => $bc);
        $this->page_construct('hr/contracts_report', $meta, $this->data);
	}
	
	public function getContractsReport($xls = NULL){
        $this->bpas->checkPermissions('contracts_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$gender = $this->input->get('gender') ? $this->input->get('gender') : NULL;
		$policy = $this->input->get('policy') ? $this->input->get('policy') : NULL;
		$employee_type = $this->input->get('employee_type') ? $this->input->get('employee_type') : NULL;
		$alert = $this->input->get('alert') ? $this->input->get('alert') : false;
		$month = $this->input->get('month') ? $this->input->get('month') : false;
		$status = $this->input->get('status') ? $this->input->get('status') : NULL;
		$current_date = date("Y-m-d");
        if ($xls) {
			if($alert=="alert"){
				$this->db->join("hr_employees_contract","hr_employees_contract.employee_id = hr_employees.id AND hr_employees_contract.id IN (SELECT SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY start_date DESC),',', 1) AS id FROM ".$this->db->dbprefix('hr_employees_contract')." GROUP BY employee_id)","inner");
				$this->db->where("hr_employees_contract.end_date < DATE_ADD('".$current_date."',INTERVAL 16 DAY)");
				$this->db->where("hr_employees_working_info.status","active");
				$this->db->where("hr_employees_contract.end_date !=", "0000-00-00");
				$this->db->where("IFNULL(".$this->db->dbprefix('hr_employees_contract').".end_date,'') !=", "");
			}else{
				$this->db->join("hr_employees_contract","hr_employees_contract.employee_id = hr_employees.id","inner");
			}
			$this->db->select("	
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_contract').".date, '%Y-%m-%d') as date,
						hr_employees.empcode,
						CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name_kh,
						CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
						hr_employees.gender,
						hr_departments.name as department,
						hr_groups.name as group,
						hr_positions.name as position,
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_working_info').".employee_date, '%Y-%m-%d') as employee_date,
						hr_employees_contract.basic_salary,
						hr_employees_contract.severance,
						hr_employees_types.name as employee_type,
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_contract').".start_date, '%Y-%m-%d') as start_date,
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_contract').".end_date, '%Y-%m-%d') as end_date,
						IF(".$this->db->dbprefix('hr_employees_contract').".end_date < '".$current_date."', 'inactive', 'active') status,
						hr_employees_contract.id as id,
						hr_employees_contract.employee_id
					")
			->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner")
			->join("hr_employees_types","hr_employees_types.id = hr_employees_contract.employee_type_id","inner")
			->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")

			->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")

			->join("hr_groups","hr_groups.id = hr_employees_contract.group_id","left")
			->join("hr_salary_reviews","hr_salary_reviews.id = hr_employees_contract.salary_review_id","left")
			->where("IFNULL(".$this->db->dbprefix('hr_salary_reviews').".status,'') !=","pending")
			->group_by("hr_employees_contract.id");
							
			if ($biller) {
                $this->db->where('hr_employees_contract.biller_id', $biller);
            }
			if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->db->where('hr_employees_contract.group_id', $group);
            }
			if ($employee) {
                $this->db->where('hr_employees.id', $employee);
            }
			if ($gender) {
                $this->db->where('hr_employees.gender', $gender);
            }
			if ($employee_type) {
                $this->db->where('hr_employees_contract.employee_type_id', $employee_type);
            }
			if ($policy) {
                $this->db->where('hr_employees_working_info.policy_id', $policy);
            }
			if($month){
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("hr_employees_contract").'.end_date,"%m/%Y")', $month);
			}
			if($status =='inactive'){
				$this->db->where($this->db->dbprefix("hr_employees_contract").'.end_date <', $current_date);
			}
			if($status =='active'){
				$this->db->where($this->db->dbprefix("hr_employees_contract").'.end_date >=', $current_date);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_contract.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('contracts_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('name_kh'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('gender'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('employee_date'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('basic_salary'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('severance'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('employee_type'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('start_date'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('end_date'));
				$row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrsd($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name_kh);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->gender));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->hrsd($data_row->employee_date));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatDecimal($data_row->basic_salary));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->severance);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->employee_type);
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->hrsd($data_row->start_date));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $this->bpas->hrsd($data_row->end_date));
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
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(20);

				$filename = 'contracts_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			if($alert=="alert"){
				$this->datatables->join("hr_employees_contract","hr_employees_contract.employee_id = hr_employees.id AND hr_employees_contract.id IN (SELECT SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY start_date DESC),',', 1) AS id FROM ".$this->db->dbprefix('hr_employees_contract')." GROUP BY employee_id)","inner");
				$this->datatables->where("hr_employees_contract.end_date < DATE_ADD('".$current_date."',INTERVAL 16 DAY)");
				$this->datatables->where("hr_employees_working_info.status","active");
				$this->datatables->where("hr_employees_contract.end_date !=", "0000-00-00");
				$this->datatables->where("IFNULL(".$this->db->dbprefix('hr_employees_contract').".end_date,'') !=", "");
			}else{
				$this->datatables->join("hr_employees_contract","hr_employees_contract.employee_id = hr_employees.id","inner");
			}
			$this->datatables->select("	
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_contract').".date, '%Y-%m-%d') as date,
						hr_employees.empcode,
						CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name_kh,
						CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
						hr_employees.gender,
						hr_groups.name as group,
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_working_info').".employee_date, '%Y-%m-%d') as employee_date,
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_contract').".start_date, '%Y-%m-%d') as start_date,
						DATE_FORMAT(".$this->db->dbprefix('hr_employees_contract').".end_date, '%Y-%m-%d') as end_date,
						IF(".$this->db->dbprefix('hr_employees_contract').".end_date < '".$current_date."', 'inactive', 'active') status,
						'' as signature,
						hr_employees_contract.id as id,
						hr_employees_contract.employee_id
					")
			->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner")
			->join("hr_employees_types","hr_employees_types.id = hr_employees_contract.employee_type_id","inner")
			->join("hr_groups","hr_groups.id = hr_employees_contract.group_id","left")
			->join("hr_salary_reviews","hr_salary_reviews.id = hr_employees_contract.salary_review_id","left")
			->where("IFNULL(".$this->db->dbprefix('hr_salary_reviews').".status,'') !=","pending")
			->group_by("hr_employees_contract.id");
							
			
			if ($biller) {
                $this->datatables->where('hr_employees_contract.biller_id', $biller);
            }
			if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
			if ($group) {
                $this->datatables->where('hr_employees_contract.group_id', $group);
            }
			if ($employee) {
                $this->datatables->where('hr_employees.id', $employee);
            }
			if ($gender) {
                $this->datatables->where('hr_employees.gender', $gender);
            }
			if ($employee_type) {
                $this->datatables->where('hr_employees_contract.employee_type_id', $employee_type);
            }
			if ($policy) {
                $this->datatables->where('hr_employees_working_info.policy_id', $policy);
            }
			if($month){
				$this->datatables->where('DATE_FORMAT('.$this->db->dbprefix("hr_employees_contract").'.end_date,"%m/%Y")', $month);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_contract.biller_id', $this->session->userdata('biller_id'));
			}
			if($status =='inactive'){
				$this->datatables->where($this->db->dbprefix("hr_employees_contract").'.end_date <', $current_date);
			}
			if($status =='active'){
				$this->datatables->where($this->db->dbprefix("hr_employees_contract").'.end_date >=', $current_date);
			}
            echo $this->datatables->generate();
        }
    }

    public function contract_forms_report(){
		$this->bpas->checkPermissions("contracts_report");
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		$department = $this->input->post('department') ? $this->input->post('department') : NULL;
		$group = $this->input->post('group') ? $this->input->post('group') : NULL;
		$position = $this->input->post('position') ? $this->input->post('position') : NULL;
		$employee = $this->input->post('employee') ? $this->input->post('employee') : NULL;
		$from_date = $this->input->post('from_date') ? $this->bpas->fsd($this->input->post('from_date')) : date("Y-m-d");
		$to_date = $this->input->post('to_date') ? $this->bpas->fsd($this->input->post('to_date')) : date("Y-m-d");
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}
		}
	
		$this->data["templates"] = $this->hr_model->getIndexTemplates();
		$this->data["contracts"] = $this->hr_model->getContracts($biller,$department,$group,$position,$employee,$from_date,$to_date);
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('contract_forms_report')));
        $meta = array('page_title' => lang('contract_forms_report'), 'bc' => $bc);
        $this->page_construct('hr/contract_forms_report', $meta, $this->data);
	}
	public function templates()
	{	
		$this->bpas->checkPermissions('templates');	
		$bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('templates')));
		$meta = array('page_title' => lang('templates'), 'bc' => $bc);
		$this->page_construct('hr/templates', $meta, $this->data);
	}

	public function getTemplates()
	{	
		$this->bpas->checkPermissions('templates');
        $this->load->library('datatables');
        $this->datatables->select("
									hr_templates.id as id, 
									companies.company,
									hr_templates.name,
									hr_templates.type,
									hr_templates.employee_type
								")
            ->from("hr_templates")
			->join("companies","companies.id = hr_templates.biller_id","left")
			->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_template") . "' href='" . admin_url('hr/edit_template/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_template") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_template/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
	}

	public function add_template()
	{
		$this->bpas->checkPermissions('templates');	
		$this->form_validation->set_rules('biller_id', lang("biller"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true){	
			$data = array(
				'biller_id' => $this->input->post("biller_id"),
				'name' => $this->input->post("name"),
				'type' => $this->input->post("type"),
				'employee_type' => $this->input->post("employee_type"),
				'template' => $this->input->post("template",false)
			);
		} elseif ($this->input->post("add_template")) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->hr_model->addTemplate($data)) {
            $this->session->set_flashdata('message', $this->lang->line("template_added"));
            admin_redirect("hr/templates");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_template', $this->data);	
		}	
	}
	
	public function edit_template($id = null)
	{		
		$this->bpas->checkPermissions('templates');	
		$this->form_validation->set_rules('biller_id', lang("biller"), 'required');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) {						
			$data = array(
				'biller_id' => $this->input->post("biller_id"),
				'name' => $this->input->post("name"),
				'type' => $this->input->post("type"),
				'employee_type' => $this->input->post("employee_type"),
				'template' => $this->input->post("template",false)
			);
		} else if($this->input->post("edit_template")){
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $id = $this->hr_model->updatetemplate($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("template_updated"));
            admin_redirect("hr/templates");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['row'] = $this->hr_model->getTemplateByID($id);
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_template', $this->data);
		}			
	}
	
	public function delete_template($id = null)
    {	
		$this->bpas->checkPermissions('templates');
        if ($this->hr_model->deleteTemplateByID($id)) {
			$this->bpas->send_json(['error' => 0, 'msg' => lang('template_deleted')]);
		} else {
			$this->session->set_flashdata('warning', lang('driver_cannot_delete'));
			die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
		}
    }
    public function approved_resignation($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->updateResignationStatus($id,"approved")) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('resignation_approved')]);
            }
            $this->session->set_flashdata('message', lang('resignation_approved'));
            admin_redirect('hr/resignation');
        }
    }
	public function unapprove_resignation($id = null){
        $this->bpas->checkPermissions("approve_id_card", true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->hr_model->updateResignationStatus($id,"pending")) {
            $this->session->set_flashdata('message', lang('resignation_unapproved'));
        }else{
			
		}
		admin_redirect('hr/resignation');
    }

    function candidate($biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
       
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('candidates')));
        $meta = array('page_title' => lang('candidates'), 'bc' => $bc);
        $this->page_construct('hr/candidate', $meta, $this->data);
    }
    function add_candidate()
	{
		$this->bpas->checkPermissions('add');	
		$post = $this->input->post();
		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		$this->form_validation->set_rules('dob', lang("dob"), 'required');
		
		if ($this->form_validation->run() == true) {
			
			$data = array(
				'candidate'		 => 1,
				'nric_no' 	 	 => $post['nric_no'],
				'firstname' 	 => $post['first_name'],
				'lastname' 		 => $post['last_name'],
				'firstname_kh' 	 => $post['first_name_kh'],
				'lastname_kh' 	 => $post['last_name_kh'],
				'dob' 			 => $this->bpas->fsd($post['dob']),				
				'gender' 		 => $post['gender'],
				'phone' 		 => $post['phone'],
				'email' 		 => $post['email'],
				'nationality'	 => $post['nationality'],
				'marital_status' => $post['marital_status'],
				'candidate_status' => 'candidate',
				// 'current_job' => $post['current_job'],
				// 'current_salary' => $post['current_salary'],
				// 'expect_salary' => $post['expect_salary'],
				// 'working_period' => $post['working_period'],
				'address' 		 => $post['address'],
				'note' 			 => $post['note'],
				'created_at'	 => date('Y-m-d H:i'),
				'created_by'	 => $this->session->userdata('user_id'),
			);
			// var_dump($data);
			// exit();
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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
			
			$add_employee = $this->hr_model->addEmployee($data);	
			if($add_employee){
				$this->session->set_flashdata('message', lang("candidate_added"));
				admin_redirect(admin_url('hr'));
			}
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));			
			$this->data['companies'] = $this->hr_model->getCompanies();
			$this->data['departments'] = $this->hr_model->getDepartments();
			$this->data['last_employee'] = $this->hr_model->getLastEmployee();
			$bc = array(array('link' => admin_url('home'), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr'), 'page' => lang('employees')), array('link' => '#', 'page' => lang('add_candidate')));
			$meta = array('page_title' => lang('add_candidate'), 'bc' => $bc);
			$this->page_construct('hr/add_candidate', $meta, $this->data);
		}
	}
	function edit_candidate($id = false)
	{
		$this->bpas->checkPermissions('edit');	
		$post = $this->input->post();
		$employee_details = $this->hr_model->getEmployeeById($id);	

		$this->form_validation->set_rules('first_name', lang("first_name"), 'required');
		$this->form_validation->set_rules('last_name', lang("last_name"), 'required');
		// $this->form_validation->set_rules('workbook_numbe', lang("workbook_numbe"));
		// $this->form_validation->set_rules('work_permit_number', lang("work_permit_number"));
		if ($this->form_validation->run() == true) {
			$data = array(
				'nric_no' 	 	 => $post['nric_no'],
				'firstname' 	 => $post['first_name'],
				'lastname' 		 => $post['last_name'],
				'firstname_kh' 	 => $post['first_name_kh'],
				'lastname_kh' 	 => $post['last_name_kh'],
				'dob' 			 => $this->bpas->fsd($post['dob']),				
				'gender' 		 => $post['gender'],
				'phone' 		 => $post['phone'],
				'email' 		 => $post['email'],
				'nationality'	 => $post['nationality'],
				'marital_status' => $post['marital_status'],
				'address' 		 => $post['address'],
				'note' 			 => $post['note'],
				'updated_at'	 => date('Y-m-d H:i'),
				'updated_by'	 => $this->session->userdata('user_id'),
			);
			// var_dump($data);
			// exit();
			if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
				$config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
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
                $config['width'] = 150;
                $config['height'] = 150;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
				if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
		}
		if ($this->form_validation->run() == true && $this->hr_model->updateEmployee($id, $data)) {
			if($this->input->post("update_close")){
				$this->session->set_flashdata('message', lang("candidate_updated"));
				admin_redirect('hr/');
			}else{
				$this->session->set_flashdata('message', lang("candidate_updated"));
				admin_redirect('hr/edit_candidate/'.$id);
			}
		}else{
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));			
			$this->data['id'] = $id;
			$this->data['row'] = $this->hr_model->getEmployeeById($id); 
			$this->data['working_info'] = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($id);
			$this->data['companies'] = $this->hr_model->getCompanies();
			$this->data['currencies'] = $this->site->getAllCurrencies();
			$this->data['additions'] = $this->hr_model->getAllAdditions();
			$this->data['deductions'] = $this->hr_model->getAllDeductions();
			$this->data['policies'] = $this->hr_model->getPolicies();
			$this->data['types'] = $this->hr_model->getEmployeeTypes();
			$this->data['kpi_types'] = $this->hr_model->getKPITypes();
			$this->data['departments'] = $this->hr_model->getDepartments();
			$bc = array(array('link' => admin_url('home'), 'page' => lang('home')),  array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => admin_url('hr'), 'page' => lang('employees')), array('link' => '#', 'page' => lang('edit_candidate')));
			$meta = array('page_title' => lang('edit_candidate'), 'bc' => $bc);
			$this->page_construct('hr/edit_candidate', $meta, $this->data);
		}
	}
	function getCandidates($biller_id = null)
    {
		$this->bpas->checkPermissions('index');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_candidate") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_candidate/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_candidate') . "</a>";
		$add_shortlist_link = "<a href='". admin_url('hr/add_shortlist/$1') ."' class='add_shortlist' data-toggle='modal' data-backdrop='static' data-target='#myModal'><i class='fa fa-plus'></i>". lang('add_shortlist')."</a>"; 
		$action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('hr/edit_candidate/$1').'" ><i class="fa fa fa-edit"></i>'.lang('edit_candidate').'</a></li>
					            <li>'.$add_shortlist_link.'</li>
					            <li>'.$delete_link.'</li>
							</ul>
						</div>'; 
        $this->datatables->select("{$this->db->dbprefix('hr_employees')}.id as id,
				{$this->db->dbprefix('hr_employees')}.firstname as firstname,
				{$this->db->dbprefix('hr_employees')}.lastname as lastname,
				{$this->db->dbprefix('hr_employees')}.gender as gender,
				{$this->db->dbprefix('hr_employees')}.email as email,
				{$this->db->dbprefix('hr_employees')}.phone as phone,
				{$this->db->dbprefix('hr_employees')}.nationality as nationality,
				{$this->db->dbprefix('hr_employees')}.dob as dob,				
				{$this->db->dbprefix('hr_employees')}.candidate_status as candidate")
            ->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
			// ->join("hr_interview","hr_interview.shortlist_id = hr_employees.id","left")
			// ->join("hr_shortlist","hr_shortlist.candidate_id = hr_employees.id","left")
			->join("hr_employees_types","hr_employees_working_info.employee_type_id = hr_employees_types.id","left")
			->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
			->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left")
			->join("companies","companies.id=hr_employees_working_info.biller_id","left")
			->add_column("Actions", $action_link, "id");
			$this->datatables->where('hr_employees.candidate', 1);
			// $this->datatables->where('hr_employees.candidate_status', 1);
		if ($biller_id) {
             $this->datatables->like('hr_employees_working_info.biller_id', $biller_id);
        }
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        echo $this->datatables->generate();
    }
    public function delete_candidate($id = null)
    {		
		$this->bpas->checkPermissions('delete');
		$interview = $this->hr_model->getCandidateShortlist($id);
        if ((isset($id) || $id != null) && $interview == 0){ 
        	$delete = $this->db->where("id",$id)->delete("hr_employees");
        	if($delete){
        		$this->session->set_flashdata('message', lang("candidate_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
            else{
            	admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
			$this->session->set_flashdata('error', lang("something_wrong"));
            admin_redirect($_SERVER['HTTP_REFERER']);
		}
    }
    function shortlist($biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
       
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('shortlist')));
        $meta = array('page_title' => lang('shortlist'), 'bc' => $bc);
        $this->page_construct('hr/shortlist', $meta, $this->data);
    }
    function getShortlists($biller_id = null)
    {
		$this->bpas->checkPermissions('index');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_shortlist") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_shortlist/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_shortlist') . "</a>";

		$add_interview_link ="<a href='".admin_url('hr/add_interview/$1')."' class='add_interview' data-toggle='modal' data-backdrop='static' data-target='#myModal'><i class='fa fa-plus'></i>" .lang('add_interview'). "</a>";
		
		$action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('hr/edit_shortlist/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_shortlist').'</a></li>
					            <li>'.$add_interview_link.'</li>
					            <li>'.$delete_link.'</li>
							</ul>
						</div>'; 
        $this->datatables->select("
				hr_shortlist.id as id,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
				{$this->db->dbprefix('hr_positions')}.name as position,
				{$this->db->dbprefix('hr_shortlist')}.shortlist_date as shortlist_date,
				{$this->db->dbprefix('hr_shortlist')}.interview_date as interview_date,
				{$this->db->dbprefix('hr_employees')}.candidate_status")
            ->from("hr_shortlist")
			->join("hr_positions","hr_shortlist.job_position_id = hr_positions.id","left")
			->join("hr_employees","hr_employees.id = hr_shortlist.candidate_id","left")
			->add_column("Actions", $action_link, "id");
		$this->datatables->where("hr_employees.candidate", 1); 
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        echo $this->datatables->generate();
    }
    public function add_shortlist($id = null)
	{
		$this->bpas->checkPermissions('positions');	 
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'candidate_id'  	=> $this->input->post('employee'),
				'job_position_id'  	=> $this->input->post('position'),
				'shortlist_date'  	=> $this->bpas->fsd($this->input->post('shortlist_date')),
				'interview_date'  	=> $this->bpas->fsd($this->input->post('interview_date')),
				'description' 		=> $this->input->post('description'),
			);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->add_shortlist($data)) {
            $this->session->set_flashdata('message', $this->lang->line("shortlist_added"));
            admin_redirect("hr/shortlist");
        }else{
			$this->data['error']     = validation_errors() ? validation_errors() : $this->session->flashdata('error');  
			$this->data['id'] 		 = isset($id)? $id: '';
			$this->data['billers'] 	 = $this->hr_model->getCompanies();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['employees'] = $this->hr_model->getCandidates();
			$this->data['modal_js']  = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_shortlist', $this->data);	
		}	
	}
	public function delete_shortlist($id = null)
    {		
		$this->bpas->checkPermissions('delete');
		$interview = $this->hr_model->getShortlistInterview($id);
        if ((isset($id) || $id != null) && $interview == 0){
			$shortlist_info = $this->hr_model->getShortlistById($id);	
			if($this->db->update('hr_employees', ['candidate_status' => 'candidate'], ['id' => $shortlist_info->candidate_id])){
				$delete = $this->db->where("id",$id)->delete("hr_shortlist");
			}
        	if($delete){
        		$this->session->set_flashdata('message', lang("shortlist_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}else{
            	admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }else {
			$this->session->set_flashdata('error', lang("something_wrong"));
            admin_redirect($_SERVER['HTTP_REFERER']);
		}
    }
    public function edit_shortlist($id = null)
	{		
		$this->bpas->checkPermissions('shortlist');			
		$shortlist_info = $this->hr_model->getShortlistById($id);	
		$this->form_validation->set_rules('employee', lang("employee"), 'required'); 
		if ($this->form_validation->run() == true) 
		{
			$data = array(
				'candidate_id'  	=> $this->input->post('employee'),
				'job_position_id'  	=> $this->input->post('position'),
				'shortlist_date'  	=> $this->bpas->fsd($this->input->post('shortlist_date')),
				'interview_date'  	=> $this->bpas->fsd($this->input->post('interview_date')),
				'description' 		=> $this->input->post('description'),
			);
		} 
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateShortlist($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("shortlist_updated"));
            admin_redirect("hr/shortlist");
        }else{
			$this->data['error'] 	 = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']		 = $id;
			$this->data['row'] 		 = $shortlist_info;
			$this->data['billers'] 	 = $this->hr_model->getCompanies();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['employees'] = $this->hr_model->getCandidates();
			$this->data['modal_js']  = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_shortlist', $this->data);
		}			
	}
	function interview($biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
       
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('interview')));
        $meta = array('page_title' => lang('interview'), 'bc' => $bc);
        $this->page_construct('hr/interview', $meta, $this->data);
    }
    function getInterviews($biller_id = null)
    {
		$this->bpas->checkPermissions('index');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_shortlist") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/delete_interview/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_interview') . "</a>";
		$add_interview_link ="<a href='".admin_url('hr/add_interview/$1')."' data-toggle='modal' data-backdrop='static' data-target='#myModal'><i class='fa fa-plus'></i>" .lang('add_interview'). "</a>";
		$action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
							<ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('hr/edit_interview/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_interview').'</a></li>
					            <li>'.$delete_link.'</li>
							</ul>
						</div>';
        $this->datatables->select("
				hr_interview.id as id,
				{$this->db->dbprefix('hr_interview')}.date as date, 
				CONCAT(shl.lastname,' ',shl.firstname) as candidate,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as interview,
				{$this->db->dbprefix('hr_interview')}.total_mark")
            ->from("hr_interview")
			->join("hr_employees","hr_employees.id = hr_interview.interviewer_id","left")
			->join("hr_employees shl","shl.id = hr_interview.shortlist_id","left")
			->add_column("Actions", $action_link, "id");
		if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        echo $this->datatables->generate();
    }
    public function add_interview($id = null)
	{
		$this->bpas->checkPermissions('positions');
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'date'  		 => $this->bpas->fsd($this->input->post('date')),
				'shortlist_id'   => $this->input->post('candidate'),
				'interviewer_id' => $this->input->post('employee'),
				'total_mark'  	 => $this->input->post('total_mark'),
				'selection'  	 => $this->input->post('selection'),
				'description' 	 => $this->input->post('description'),
			);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->add_interview($data)) {
            $this->session->set_flashdata('message', $this->lang->line("interview_added"));
            admin_redirect("hr/interview");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');  
			$this->data['id'] 			= isset($id) ? $id : '';
			$this->data['shortlist_id'] = "";
			if(isset($id)){
				$this->data['shortlist_id'] = $this->hr_model->getShortlistById($id);
			}
			$this->data['billers'] 		= $this->hr_model->getCompanies();
			$this->data['positions'] 	= $this->hr_model->getAllPositions();
			$this->data['shortlists'] 	= $this->hr_model->getShortlists();
			$this->data['employees'] 	= $this->hr_model->getAllEmployees();
			$this->data['modal_js'] 	= $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_interview', $this->data);	
		}	
	}
	
	public function delete_interview($id = null)
    {		
		$this->bpas->checkPermissions('delete');
		$employee = $this->hr_model->getInterviewEmployee($id);
		var_dump($employee);
		exit();
        if ((isset($id) || $id != null) && $employee == 0){
			$interview_info = $this->hr_model->getInterviewById($id);
			if($this->db->update('hr_employees', ['candidate_status' => 'shortlist'], ['id' => $interview_info->shortlist_id])){	
        		$delete = $this->db->where("id",$id)->delete("hr_interview");
			}
        	if($delete){
        		$this->session->set_flashdata('message', lang("interview_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
            else{
            	admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }else{
			$this->session->set_flashdata('error', lang("something_worng"));
            admin_redirect($_SERVER['HTTP_REFERER']);
		}
    }
    public function edit_interview($id = null)
	{		
		$this->bpas->checkPermissions('shortlist');			
		$shortlist_info = $this->hr_model->getInterviewById($id);	
		$this->form_validation->set_rules('employee', lang("employee"), 'required'); 
		if ($this->form_validation->run() == true) 
		{
			$data = array(
				'date'  			=> $this->bpas->fsd($this->input->post('date')),
				'shortlist_id'  	=> $this->input->post('candidate'),
				'interviewer_id'	=> $this->input->post('employee'),
				'total_mark'  		=> $this->input->post('total_mark'),
				'selection'  		=> $this->input->post('selection'),
				'description' 		=> $this->input->post('description'),
			); 
		} 
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateInterview($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("shortlist_updated"));
            admin_redirect("hr/shortlist");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $shortlist_info;
			$this->data['billers'] = $this->hr_model->getCompanies();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['employees'] = $this->hr_model->getAllEmployees();
			$this->data['shortlists'] 	= $this->hr_model->getShortlists();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_interview', $this->data);
		}			
	}
	public function organization_chart($biller_id =false) {
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
		$this->data['main_companies'] = $this->site->getAllCompanies('biller');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employees')));
        $meta = array('page_title' => lang('employees'), 'bc' => $bc);
        $this->page_construct('hr/orgchart', $meta, $this->data);
		
	}

	public function trainers()
	{
		$this->bpas->checkPermissions('trainers');
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => 'concretes', 'page' => lang('concrete')), array('link' => '#', 'page' => lang('trainers')));
		$meta = array('page_title' => lang('trainers'), 'bc' => $bc);
		$this->page_construct('hr/trainers', $meta, $this->data);
	}

	public function getTrainers()
	{
		$this->bpas->checkPermissions('officers');
		$this->load->library('datatables');
		$this->datatables
			->select("hr_trainers.id as id,
				{$this->db->dbprefix('hr_trainers')}.full_name as name,
				{$this->db->dbprefix('hr_trainers')}.full_name_kh as name_kh,
				hr_trainers.phone,
				{$this->db->dbprefix('hr_trainers')}.gender as gender,
				hr_trainers.address,
				hr_trainers.note,
				{$this->db->dbprefix('hr_trainers')}.attachment
			")
			->from("hr_trainers")
			->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_officer") . "' href='" . admin_url('hr/edit_trainer/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_trainer") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_trainer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");

		echo $this->datatables->generate();
	}
	public function add_trainer()
	{
		$this->bpas->checkPermissions('trainer', true);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
				'full_name' 	=> $this->input->post('name'),
				'full_name_kh' 	=> $this->input->post('name_kh'),
				'phone' 		=> $this->input->post('phone'),
				'gender' 		=> $this->input->post('gender'),
				'address' 		=> $this->bpas->decode_html($this->input->post('address')),
				'note' 			=> $this->bpas->decode_html($this->input->post('note'))
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
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['photo'] = $photo;
			}
		} elseif ($this->input->post('add_trainer')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('hr/trainers');
		}

		if ($this->form_validation->run() == true && $id = $this->hr_model->addTrainers($data)) {
			$this->session->set_flashdata('message', $this->lang->line("officer_added"));
			admin_redirect('hr/trainers');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['billers'] =  $this->site->getBillers();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->load->view($this->theme . 'hr/add_trainer', $this->data);
		}
	}

	public function edit_trainer($id = false)
	{
		$this->bpas->checkPermissions('officers', true);

		$officer = $this->hr_model->getTrainerByID($id);
		$this->form_validation->set_rules('name', lang("name"), 'required');
		if ($this->form_validation->run() == true) {
			$data = array(
				'full_name' 	=> $this->input->post('name'),
				'full_name_kh' 	=> $this->input->post('name_kh'),
				'phone' 		=> $this->input->post('phone'),
				'gender' 		=> $this->input->post('gender'),
				'address' 		=> $this->bpas->decode_html($this->input->post('address')),
				'note' 			=> $this->bpas->decode_html($this->input->post('note'))
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
					redirect($_SERVER["HTTP_REFERER"]);
				}
				$photo = $this->upload->file_name;
				$data['photo'] = $photo;
			}
		} elseif ($this->input->post('edit_officer')) {
			$this->session->set_flashdata('error', validation_errors());
			admin_redirect('hr/trainers');
		}

		if ($this->form_validation->run() == true && $id = $this->hr_model->updateTrainers($id, $data)) {
			$this->session->set_flashdata('message', $this->lang->line("trainer_edited"));
			admin_redirect('hr/trainers');
		} else {
			$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['modal_js'] = $this->site->modal_js();
			$this->data['officer'] = $officer;
			$this->data['billers'] =  $this->site->getBillers();
			$this->load->view($this->theme . 'hr/edit_trainer', $this->data);
		}
	}

	public function delete_trainer($id = NULL)
	{
		$this->bpas->checkPermissions('trainer', true);
		if ($this->hr_model->deleteTrainers($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('trainer_deleted')]);
            }
            $this->session->set_flashdata('message', lang('trainer_deleted'));
            admin_redirect('hr/training');
        }
	}
	function trainer_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_trainer');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$this->hr_model->deleteTrainers($id);
                    }
					$this->session->set_flashdata('message', $this->lang->line("trainer_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
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
	function training($biller_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($action)) {
        	$this->data['action'] = $action;
        }
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
       
        $bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('training')));
        $meta = array('page_title' => lang('training'), 'bc' => $bc);
        $this->page_construct('hr/training', $meta, $this->data);
    }
    public function getTraining($biller_id = false){
		$this->bpas->checkPermissions("travels");

		$view_link = anchor('admin/hr/modal_view_travel/$1', '<i class="fa fa-money"></i> ' . lang('view_detail'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_detail" data-target="#myModal"');

        $edit_link = anchor('admin/hr/edit_training/$1', '<i class="fa fa-edit"></i> ' . lang('edit_training'), ' class="edit_training"');

        $delete_link = "<a href='#' class='delete_training po' title='<b>" . $this->lang->line("delete_training") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_training/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_training') . "</a>";
		
		// if($this->Admin || $this->Owner || $this->GP['hr-approve_travel']){
		// 	$approve_link = "<a href='#' class='po approve_travel' title='" . $this->lang->line("approve_travel") . "' data-content=\"<p>"
		// 	. lang('r_u_sure') . "</p><a class='btn btn-success po-delete' href='" . admin_url('hr/approve_travel/$1') . "'>"
		// 	. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
		// 	. lang('approve_travel') . "</a>";

		// 	$unapprove_link = "<a href='#' class='po unapprove_travel' title='<b>" . $this->lang->line("unapprove_travel") . "</b>' data-content=\"<p>"
		// 	. lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('hr/unapprove_travel/$1') . "'>"
		// 	. lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-times\"></i> "
		// 	. lang('unapprove_travel') . "</a>";
		// }
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
						<li>' . $view_link . '</li>
						<li>' . $edit_link . '</li>
                        <li>' . $delete_link . '</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
		$this->datatables->select("
				hr_training.id as id, 
				DATE_FORMAT(".$this->db->dbprefix('hr_training').".date, '%Y-%m-%d %T') as date,
				companies.name as biller,
				custom_field.name as training_type, 
				{$this->db->dbprefix('hr_training')}.start_date as start_date,
				{$this->db->dbprefix('hr_training')}.end_date as end_date,
				hr_trainers.full_name as trainner, 
				hr_training.training_option as training_option
				")
		->from("hr_training")
		->join("companies","companies.id=hr_training.biller_id","left")
		->join("custom_field","custom_field.id=hr_training.training_type","left")
		->join("hr_trainers","hr_trainers.id = hr_training.traininer","left");
		if ($biller_id) {
            $this->datatables->where("hr_training.biller_id", $biller_id);
        }	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where("hr_training.biller_id", $this->session->userdata('biller_id'));
		}
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("hr_training.created_by", $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_training()
    {
        $this->bpas->checkPermissions();
		$this->form_validation->set_rules('biller', lang("biller"), 'required');
		$this->form_validation->set_rules('start_date', lang("start_date"), 'required');
		$this->form_validation->set_rules('end_date', lang("end_date"), 'required');
        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin || $this->bpas->GP['attendances-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$items = false;
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'employee_id' => $employee_id,
						'description' => $description,
					);
				}
            }
			
            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }
			
            $data = array(
                'date' 				=> $date,
				'biller_id' 		=> $biller_id,
				'training_option' 	=> $this->input->post('trainer_option'),
				'training_type'		=> $this->input->post('training_type'),
				'traininer'			=> $this->input->post('trainer'),
				'start_date'		=> $this->bpas->fld($this->input->post('start_date')),
				'end_date'			=> $this->bpas->fld($this->input->post('end_date')),
                'note' 				=> $note,
                'created_by' 		=> $this->session->userdata('user_id'),
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }
        if ($this->form_validation->run() == true && $this->hr_model->addTraining($data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("training_added"));
            admin_redirect('hr/training');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['trainers'] = $this->hr_model->getAllTrainers();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('add_training')), array('link' => admin_url('hr/add_training'), 'page' => lang('add_training')), array('link' => '#', 'page' => lang('add_training')));
            $meta = array('page_title' => lang('add_training'), 'bc' => $bc);
            $this->page_construct('hr/add_training', $meta, $this->data);
        }
    }
    public function edit_training($id)
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'required');
		$this->form_validation->set_rules('end_date', lang("end_date"), 'required');

        if ($this->form_validation->run() == true) {
			$biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin || $this->bpas->GP['attendances-date']) {
                $date = $this->bpas->fld($this->input->post('date'));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $note = $this->bpas->clear_tags($this->input->post('note'));
			$items = false;
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'training_id' => $id,
						'employee_id' => $employee_id,
						'description' => $description,
					);
				}
            }
 

            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }

            $data = array(
            	'date' 			=> $date,
				'biller_id' 	=> $biller_id,
				'training_option' 	=> $this->input->post('trainer_option'),
				'training_type'		=> $this->input->post('training_type'),
				'traininer'			=> $this->input->post('trainer'),
				'start_date'		=> $this->bpas->fld($this->input->post('start_date')),
				'end_date'			=> $this->bpas->fld($this->input->post('end_date')),
                'note' 			=> $note,
				'updated_at'    => date('Y-m-d H:i:s'),
                'updated_by'    => $this->session->userdata('user_id'),
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
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        }

        if ($this->form_validation->run() == true && $this->hr_model->UpdateTraining($id, $data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("training_updated"));
            admin_redirect('hr/training');
        } else {
			$training = $this->hr_model->getTrainingByID($id);
			
            $items = $this->hr_model->getTrainingItems($id);
            krsort($items);
            $c = rand(100000, 9999999);
            foreach ($items as $item) {
				$item->id = $item->employee_id;
                $pr[$c] = array('id' => $c, 'item_id' => $item->id, 'label' => $item->lastname .' '.$item->firstname. " (" . $item->empcode . ")",'row' => $item);
                $c++;
            }
            $this->data['travel'] = $training;
            $this->data['travel_items'] = json_encode($pr);
			$this->data['billers'] = ($this->Owner||$this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->data['trainers'] = $this->hr_model->getAllTrainers();
			$this->session->set_userdata('remove_dfls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('hr'), 'page' => lang('hr')),array('link' => site_url('hr/edit_training'), 'page' => lang('training')), array('link' => '#', 'page' => lang('edit_training')));
            $meta = array('page_title' => lang('edit_training'), 'bc' => $bc);
            $this->page_construct('hr/edit_training', $meta, $this->data);

        }
    }
    public function delete_training($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->hr_model->deleteTraining($id)) {
            if ($this->input->is_ajax_request()) {
            	$this->bpas->send_json(['error' => 0, 'msg' => lang('training_deleted')]);
            }
            $this->session->set_flashdata('message', lang('travel_deleted'));
            admin_redirect('hr/training');
        }
    }
    function training_actions(){
        if (!$this->Owner && !$this->Admin &&  !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_training');
					$deleted = 0;
                    foreach ($_POST['val'] as $id) {
						$this->hr_model->deleteTraining($id);
                    }
					$this->session->set_flashdata('message', $this->lang->line("training_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
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
    public function edit_user_emp($id = null)
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $this->data['title'] = lang('edit_user_emp');
        if (!$this->loggedIn || (!$this->Owner && !$this->Admin) && $id != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $emp_id  = $this->input->post('emp_id');
        $user_id = $this->input->post('user_id')?$this->input->post('user_id'):'';
        $users = $this->site->getAllUserByEmp($emp_id);
        $notify =0;
        if(!$user_id){
			$this->form_validation->set_rules('username', lang('username'), 'trim|is_unique[users.username]');
		}
		$this->form_validation->set_rules('password', lang('password'), 'trim|required');

        $Employee 		=  $this->hr_model->getEmployeeById($emp_id);
		$working_info  = $this->hr_model->getEmployeesWorkingInfoByEmployeeID($emp_id);

		if ($this->form_validation->run() === true) {

			$username = strtolower($this->input->post('username'));
			$email    = strtolower($Employee->email);
			$password = $this->input->post('password');
			$active   = $working_info->status =='active' ? 1:0;

			$data = [
				'username'       => $this->input->post('username'),
				'emp_id'         => $emp_id,
				'emp_code'       => $Employee->empcode,
				'first_name'     => $Employee->firstname,
				'last_name'      => $Employee->lastname,
				'email'          => $Employee->email,
				'phone'          => $Employee->phone,
				'gender'         => $Employee->gender,
				'active'         => $active,
				'group_id'       => 8,
				'biller_id'      => $working_info->biller_id,
			];
		}
		if($user_id){
			$data = [
				'password'       => $this->input->post('password'),
			];
			if ($this->form_validation->run() === true && $this->ion_auth->update($users->id, $data)) {
				$this->session->set_flashdata('message', lang('user_updated'));
				admin_redirect('hr/');
			}
		}else{
			if ($this->form_validation->run() == true && $this->ion_auth->EmployeeRegister($username, $password, $email, $data, $active, $notify)) {
				$this->session->set_flashdata('message', lang('user_has_been_register'));
				admin_redirect('hr/');
			}	
		}
    }
    public function register()
    {
        $this->data['title'] = 'Register';
        if (!$this->allow_reg) {
            $this->session->set_flashdata('error', lang('registration_is_disabled'));
            admin_redirect('login');
        }
        $this->form_validation->set_message('is_unique', lang('account_exists'));
        $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
        $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
        $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('usernam', lang('usernam'), 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[25]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');
        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run() == true) {
            $username = strtolower($this->input->post('username'));
            $email    = strtolower($this->input->post('email'));
            $password = $this->input->post('password');
            $data = [
			// 'emp_id'     => $this->input->post('emp_id'),
			// 'emp_code'     => $this->input->post('empcode'),
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name'),
                'company'    => $this->input->post('company'),
                'phone'      => $this->input->post('phone'),
            ];
		// var_dump($data);exit();
        }
        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $data)) {
            $this->session->set_flashdata('message', $this->ion_auth->messages());
            admin_redirect('login');
        } else {
            $this->data['error']  = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('error')));
            $this->data['groups'] = $this->ion_auth->groups()->result_array();

            $this->load->helper('captcha');
            $vals = [
                'img_path'   => './assets/captcha/',
                'img_url'    => admin_url() . 'assets/captcha/',
                'img_width'  => 150,
                'img_height' => 34,
            ];
            $cap     = create_captcha($vals);
            $capdata = [
                'captcha_time' => $cap['time'],
                'ip_address'   => $this->input->ip_address(),
                'word'         => $cap['word'],
            ];

            $query = $this->db->insert_string('captcha', $capdata);
            $this->db->query($query);
            $this->data['image']   = $cap['image'];
            $this->data['captcha'] = [
                'name'        => 'captcha',
                'id'          => 'captcha',
                'type'        => 'text',
                'class'       => 'form-control',
                'placeholder' => lang('type_captcha'),
            ];

            $this->data['first_name'] = [
                'name'        => 'first_name',
                'id'          => 'first_name',
                'type'        => 'text',
                'class'       => 'form-control',
                'required'    => 'required',
                'value'       => $this->form_validation->set_value('first_name'),
            ];
            $this->data['last_name'] = [
                'name'     => 'last_name',
                'id'       => 'last_name',
                'type'     => 'text',
                'required' => 'required',
                'class'    => 'form-control',
                'value'    => $this->form_validation->set_value('last_name'),
            ];
            $this->data['email'] = [
                'name'     => 'email',
                'id'       => 'email',
                'type'     => 'text',
                'required' => 'required',
                'class'    => 'form-control',
                'value'    => $this->form_validation->set_value('email'),
            ];
            $this->data['company'] = [
                'name'     => 'company',
                'id'       => 'company',
                'type'     => 'text',
                'required' => 'required',
                'class'    => 'form-control',
                'value'    => $this->form_validation->set_value('company'),
            ];
            $this->data['phone'] = [
                'name'     => 'phone',
                'id'       => 'phone',
                'type'     => 'text',
                'required' => 'required',
                'class'    => 'form-control',
                'value'    => $this->form_validation->set_value('phone'),
            ];
            $this->data['password'] = [
                'name'     => 'password',
                'id'       => 'password',
                'type'     => 'password',
                'required' => 'required',
                'class'    => 'form-control',
                'value'    => $this->form_validation->set_value('password'),
            ];
            $this->data['password_confirm'] = [
                'name'     => 'password_confirm',
                'id'       => 'password_confirm',
                'type'     => 'password',
                'required' => 'required',
                'class'    => 'form-control',
                'value'    => $this->form_validation->set_value('password_confirm'),
            ];

            $this->load->view('auth/register', $this->data);
        }
    }
    public function expired_contract($id = false)
	{
		$this->bpas->checkPermissions('expired_contract');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employee_types')));
		$meta = array('page_title' => lang('expired_contract'), 'bc' => $bc);
		$this->page_construct('hr/expired_contract', $meta, $this->data);
		
	}
	public function getExpiredContracts()
	{
		$this->load->library('datatables');
		$current_date = date("Y-m-d");
		$expiry_alert_days    = $this->Settings->expiry_alert_days;
        $settings_expiry_date = date('Y-m-d', strtotime(" +{$expiry_alert_days} days "));

		$delete_link = "<a href='#' class='po' title='" . lang("delete_document") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_document/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_document') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_document/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_document').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees_contract.id as id,
					CONCAT({$this->db->dbprefix('hr_employees')}.lastname,' ',{$this->db->dbprefix('hr_employees')}.firstname) as name,
					hr_employees_contract.contract_title,
					hr_employees_contract.contract_type,
					hr_employees_contract.start_date,
					hr_employees_contract.end_date
					")
            ->from("hr_employees_contract")
			->join("hr_employees","hr_employees.id=hr_employees_contract.employee_id","left");
			if ($settings_expiry_date) {
                    $this->db->where($this->db->dbprefix('hr_employees_contract') . '.end_date <=', $settings_expiry_date);
                }

			$this->datatables->where("hr_employees_contract.end_date is not NULL")
			->where("end_date !=",'0000-00-00');
			$this->datatables->where($this->db->dbprefix("hr_employees_contract").'.end_date >=', $current_date);

			$this->datatables->unset_column("id");
        echo $this->datatables->generate();
	}
	public function promotions_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['policies'] = $this->hr_model->getPolicies();
		$this->data['employee_types'] = $this->hr_model->getEmployeeTypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			/*if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}*/
		}
		$this->data['employee_levels']     = $this->hr_model->getNestedByCategories();
		$this->data['employees'] = $this->hr_model->getAllEmployees();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('promotions_report')));
        $meta = array('page_title' => lang('promotions_report'), 'bc' => $bc);
        $this->page_construct('hr/promotions_report', $meta, $this->data);
	}
	public function getPromotionsReport($xls = NULL){
        $this->bpas->checkPermissions('promotions_report');
		$biller 		= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department 	= $this->input->get('department') ? $this->input->get('department') : NULL;
		$group 			= $this->input->get('group') ? $this->input->get('group') : NULL;
		$position 		= $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee 		= $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$gender 		= $this->input->get('gender') ? $this->input->get('gender') : NULL;
		$employee_level = $this->input->get('employee_level') ? $this->input->get('employee_level') : NULL;
		$employee_type 	= $this->input->get('employee_type') ? $this->input->get('employee_type') : NULL;
		$promoted_date 		= $this->input->get('promoted_date') ? $this->bpas->fsd($this->input->get('promoted_date')) : NULL;
		$official_promote 	= $this->input->get('official_promote') ? $this->bpas->fsd($this->input->get('official_promote')) : NULL;
		$promoted_by    = $this->input->get('promoted_by') ? $this->input->get('promoted_by') : NULL;

		$current_date = date("Y-m-d");
        if ($xls) {
			
			$this->db->select("
					{$this->db->dbprefix('hr_employees')}.empcode as code,
					CONCAT({$this->db->dbprefix('hr_employees')}.lastname,' ',{$this->db->dbprefix('hr_employees')}.firstname) as name,
					hr_employees.gender as gender,
					hr_positions.name as position,
					hr_employees_level.name as employee_level,
					promoted_date,
					official_promote,
					CONCAT(em.lastname,' ',em.firstname) as promoted_by")
            ->from("hr_employees_working_promote")
            ->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees_working_promote.employee_id","inner")

			->join("hr_employees","hr_employees.id=hr_employees_working_promote.employee_id","left")
			->join("hr_employees em","em.id=hr_employees_working_promote.promoted_by","left")


			->join("hr_positions","hr_positions.id=hr_employees_working_promote.position_id","left")
			->join("hr_employees_level","hr_employees_level.id=hr_employees_working_promote.employee_level","left")
			->join("hr_employees_types","hr_employees_types.id=hr_employees_working_promote.employee_type_id","left");
							
			
			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
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
                $this->db->where('hr_employees.id', $employee);
            }
            if ($employee_level) {
                $this->db->where('hr_employees_working_promote.employee_level', $employee_level);
            }
            
			if ($gender) {
                $this->db->where('hr_employees.gender', $gender);
            }
			if ($employee_type) {
                $this->db->where('hr_employees_working_info.employee_type_id', $employee_type);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
			if($promoted_date){
				$this->db->where($this->db->dbprefix("hr_employees_working_promote").'.promoted_date', $promoted_date);
			}
			if($official_promote){
				$this->db->where($this->db->dbprefix("hr_employees_working_promote").'.official_promote', $current_date);
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
                $this->excel->getActiveSheet()->setTitle(lang('promote_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('gender'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('employee_level'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('promoted_date'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('official_promote'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('promoted_by'));

				$row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, lang($data_row->gender));
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->employee_level);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($data_row->promoted_date));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->hrsd($data_row->official_promote));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->promoted_by);
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

				$filename = 'promote_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');

			$this->datatables->select("
					{$this->db->dbprefix('hr_employees')}.empcode as code,
					CONCAT({$this->db->dbprefix('hr_employees')}.lastname,' ',{$this->db->dbprefix('hr_employees')}.firstname) as name,
					hr_positions.name as position,
					hr_employees_level.name as employee_level,
					promoted_date,
					official_promote,
					CONCAT(em.lastname,' ',em.firstname) as promoted_by")
            ->from("hr_employees_working_promote")
            ->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees_working_promote.employee_id","inner")

			->join("hr_employees","hr_employees.id=hr_employees_working_promote.employee_id","left")
			->join("hr_employees em","em.id=hr_employees_working_promote.promoted_by","left")

			->join("hr_positions","hr_positions.id=hr_employees_working_promote.position_id","left")
			->join("hr_employees_level","hr_employees_level.id=hr_employees_working_promote.employee_level","left")
			->join("hr_employees_types","hr_employees_types.id=hr_employees_working_promote.employee_type_id","left");
							
			
			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
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
                $this->datatables->where('hr_employees.id', $employee);
            }
            if ($employee_level) {
                $this->datatables->where('hr_employees_working_promote.employee_level', $employee_level);
            }
            
			if ($gender) {
                $this->datatables->where('hr_employees.gender', $gender);
            }
			if ($employee_type) {
                $this->datatables->where('hr_employees_working_info.employee_type_id', $employee_type);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
			if($promoted_date){
				$this->datatables->where($this->db->dbprefix("hr_employees_working_promote").'.promoted_date', $promoted_date);
			}
			if($official_promote){
				$this->datatables->where($this->db->dbprefix("hr_employees_working_promote").'.official_promote', $current_date);
			}
            echo $this->datatables->generate();
        }
   }
   public function travels_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['policies'] = $this->hr_model->getPolicies();
		$this->data['employee_types'] = $this->hr_model->getEmployeeTypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			/*if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}*/
		}
		$this->data['employee_levels']     = $this->hr_model->getNestedByCategories();
		$this->data['employees'] = $this->hr_model->getAllEmployees();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('travels_report')));
        $meta = array('page_title' => lang('travels_report'), 'bc' => $bc);
        $this->page_construct('hr/travels_report', $meta, $this->data);
	}
	public function getTravelsReport($xls = NULL){
        $this->bpas->checkPermissions('promotions_report');
		$biller 		= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$start_date     = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : NULL;
		$end_date 		= $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date')) : NULL;

		$current_date = date("Y-m-d");
        if ($xls) {
			
			$this->db->select("
				DATE_FORMAT(".$this->db->dbprefix('hr_travels').".date, '%Y-%m-%d %T') as date,
				hr_travels.purpose as purpose,
				hr_travels.place as place, 
				{$this->db->dbprefix('hr_travels')}.start_date as start_date,
				{$this->db->dbprefix('hr_travels')}.end_date as end_date,
				hr_travels.budget as budget, 
				hr_travels.status
			")
			->from("hr_travels")
			->join("users","users.id = hr_travels.created_by","left");
							
			
			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
			if($start_date){
				$this->datatables->where($this->db->dbprefix("hr_travels").'.start_date >=', $start_date);
				$this->datatables->where($this->db->dbprefix("hr_travels").'.end_date <=', $end_date);
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
                $this->excel->getActiveSheet()->setTitle(lang('promote_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('purpose'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('place'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('start_date'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('end_date'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('budget'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

				$row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->purpose);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->place);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->hrsd($data_row->start_date));
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->hrsd($data_row->end_date));
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatDecimal($data_row->budget));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

				$filename = 'travels_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
				DATE_FORMAT(".$this->db->dbprefix('hr_travels').".date, '%Y-%m-%d %T') as date,
				hr_travels.purpose as purpose,
				hr_travels.place as place, 
				{$this->db->dbprefix('hr_travels')}.start_date as start_date,
				{$this->db->dbprefix('hr_travels')}.end_date as end_date,
				hr_travels.budget as budget, 
				hr_travels.status
			")
			->from("hr_travels")
			->join("users","users.id = hr_travels.created_by","left");
							
			
			if ($biller) {
                $this->datatables->where('hr_travels.biller_id', $biller);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_travels.biller_id', $this->session->userdata('biller_id'));
			}
			if($start_date){
				$this->datatables->where($this->db->dbprefix("hr_travels").'.start_date >=', $start_date);
				$this->datatables->where($this->db->dbprefix("hr_travels").'.end_date <=', $end_date);
			}
            echo $this->datatables->generate();
        }
    }
    public function retirement_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['policies'] = $this->hr_model->getPolicies();
		$this->data['employee_types'] = $this->hr_model->getEmployeeTypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			/*if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}*/
		}
		$this->data['employee_levels']     = $this->hr_model->getNestedByCategories();
		$this->data['employees'] = $this->hr_model->getAllEmployees();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('retirement_report')));
        $meta = array('page_title' => lang('retirement_report'), 'bc' => $bc);
        $this->page_construct('hr/retirement_report', $meta, $this->data);
	}
	public function getRetirementReport($xls = NULL){
        $this->bpas->checkPermissions('retirement_report');
		$biller 		= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$employee 		= $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$start_date     = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : NULL;
		$end_date 		= $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date')) : NULL;
        if ($xls) {
			$this->db->select("
				hr_employees.empcode,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
				hr_employees.gender,
				hr_positions.name as position,
				hr_departments.name as department,
				{$this->db->dbprefix('hr_employees')}.dob as dob,
				hr_employees.retirement
			")
			->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
			->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
			->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
							
			
			if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($employee) {
                $this->db->where('hr_employees.id', $employee);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
			if($start_date){
				$this->datatables->where($this->db->dbprefix("hr_employees").'.retirement >=', $start_date);
				$this->datatables->where($this->db->dbprefix("hr_employees").'.retirement <=', $end_date);
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
                $this->excel->getActiveSheet()->setTitle(lang('retirement_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('gender'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('dob'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('retirement'));

				$row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row,$data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->gender);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($data_row->dob));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->hrsd($data_row->retirement));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

				$filename = 'retirement_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');
			$this->datatables->select("
				hr_employees.empcode,
				CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
				hr_employees.gender,
				hr_positions.name as position,
				hr_departments.name as department,

				{$this->db->dbprefix('hr_employees')}.dob as dob,
				hr_employees.retirement
			")
			->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
			->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
			->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");
							
			if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
            }
			if ($employee) {
                $this->datatables->where('hr_employees.id', $employee);
            }

			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
			if($start_date){
				$this->datatables->where($this->db->dbprefix("hr_employees").'.retirement >=', $start_date);
				$this->datatables->where($this->db->dbprefix("hr_employees").'.retirement <=', $end_date);
			}
            echo $this->datatables->generate();
        }
    }
    public function transfer_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$this->data['policies'] = $this->hr_model->getPolicies();
		$this->data['employee_types'] = $this->hr_model->getEmployeeTypes();
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['departments'] = $this->hr_model->getDepartmentsByBilller($biller);
			/*if($department){
				$this->data['groups'] = $this->hr_model->getGroupsByDepartment($department);
				$this->data['positions'] = $this->hr_model->getPositionsByDepartment($department);
			}*/
		}
		$this->data['employee_levels']     = $this->hr_model->getNestedByCategories();
		$this->data['employees'] = $this->hr_model->getAllEmployees();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => site_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('transfer_report')));
        $meta = array('page_title' => lang('transfer_report'), 'bc' => $bc);
        $this->page_construct('hr/transfer_report', $meta, $this->data);
	}
	public function getTransferReport($xls = NULL){
        $this->bpas->checkPermissions('getTransferReport');
		$biller 		= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$employee 		= $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$start_date     = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : NULL;
		$end_date 		= $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date')) : NULL;
        if ($xls) {
			$this->db->select("
					DATE_FORMAT(".$this->db->dbprefix('hr_transfers').".date, '%Y-%m-%d %T') as date,
					{$this->db->dbprefix('hr_departments')}.name as from_department,
					dt.name as to_department,
					note,
					hr_transfers.status
					")
			->from("hr_transfers")
			->join("hr_departments","hr_departments.id = hr_transfers.from_department","left")
			->join("hr_departments dt","dt.id = hr_transfers.to_department","left");
							
			if ($biller) {
                $this->db->where('hr_transfers.biller_id', $biller);
            }
			if ($employee) {
                $this->db->where('hr_transfers.id', $employee);
            }

			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
			if($start_date){
				$this->datatables->where($this->db->dbprefix("hr_transfers").'.date >=', $start_date);
				$this->datatables->where($this->db->dbprefix("hr_transfers").'.date <=', $end_date);
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
                $this->excel->getActiveSheet()->setTitle(lang('retirement_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('gender'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('dob'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('retirement'));

				$row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row,$data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->gender);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($data_row->dob));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->hrsd($data_row->retirement));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);

				$filename = 'retirement_report_' . date('Y_m_d_H_i_s');
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->load->library('datatables');

            $this->datatables->select("
					DATE_FORMAT(".$this->db->dbprefix('hr_transfers').".date, '%Y-%m-%d %T') as date,
					{$this->db->dbprefix('hr_departments')}.name as from_department,
					dt.name as to_department,
					note,
					hr_transfers.status
					")
			->from("hr_transfers")
			->join("hr_departments","hr_departments.id = hr_transfers.from_department","left")
			->join("hr_departments dt","dt.id = hr_transfers.to_department","left");
							
			if ($biller) {
                $this->datatables->where('hr_transfers.biller_id', $biller);
            }
			if ($employee) {
                $this->datatables->where('hr_transfers.id', $employee);
            }

			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_transfers.biller_id', $this->session->userdata('biller_id'));
			}
			if($start_date){
				$this->datatables->where($this->db->dbprefix("hr_transfers").'.date >=', $start_date);
				$this->datatables->where($this->db->dbprefix("hr_transfers").'.date <=', $end_date);
			}
            echo $this->datatables->generate();
        }
    }
    public function alert_birthday($id = false)
	{
		$this->bpas->checkPermissions('alert_birthday');
		$bc = array(array('link' => admin_url(), 'page' => lang('home')),array('link' => admin_url('hr'), 'page' => lang('hr')), array('link' => '#', 'page' => lang('employee_types')));
		$meta = array('page_title' => lang('employee_types'), 'bc' => $bc);
		$this->page_construct('hr/alert_birthday', $meta, $this->data);
		
	}
	public function getAlertBirthday()
	{
		$this->load->library('datatables');
		$expiry_alert_days    = $this->Settings->alert_day;
        $settings_expiry_date = date('Y-m-d', strtotime(" +{$expiry_alert_days} days "));

		$delete_link = "<a href='#' class='po' title='" . lang("delete_document") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_document/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_document') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_document/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_document').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					hr_employees.id as id,
					hr_employees.empcode as code,
					CONCAT({$this->db->dbprefix('hr_employees')}.lastname,' ',{$this->db->dbprefix('hr_employees')}.firstname) as name,
					gender,
					hr_positions.name as position,
					hr_employees.dob
			")
            ->from("hr_employees")
            ->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","left")
            ->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left");

            //SELECT username FROM users WHERE MONTH(dob) = MONTH(NOW()) AND DAY(dob) = DAY(NOW())

			// if ($settings_expiry_date) {
            //     $this->db->where($this->db->dbprefix('hr_employees') . '.dob <=', $settings_expiry_date);

            $this->db->where('MONTH(dob) = MONTH(NOW()) AND DAY(dob) = DAY(NOW())');

            // }

			$this->datatables->unset_column("id");
        echo $this->datatables->generate();
	}

	public function getOnBoarding($employee_id = false)
	{	
		if(!$employee_id){
			$employee_id = $this->input->get("employee_id");
		}
		$this->load->library('datatables');
		
		$delete_link = "<a href='#' class='po' title='" . lang("delete_on_boarding") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('hr/delete_on_boarding/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_on_boarding') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('hr/edit_on_boarding/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_on_boarding').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
	
        $this->datatables
            ->select("
					id,
					joining_date,
					probation_periods,
					probation_end_date,
					received_asset,
					description,
					attachment")
            ->from("hr_employees_on_boardng")
			->where("employee_id",$employee_id)
			->unset_column("id")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	public function add_on_boarding($employee_id = false)
	{
		$this->form_validation->set_rules('joining_date', lang("joining_date"), 'required');
		//$this->form_validation->set_rules('w_position', lang("position"), 'required');
		
		if ($this->form_validation->run() == true) {	

			$joining_date			= $this->input->post('joining_date');
			$probation_end_date  	= $this->input->post('probation_end_date');
			$description			= $this->input->post('description');
		
			$data = array(
				'joining_date'   		=> $this->bpas->fld($joining_date),		
				'probation_periods'     => $this->input->post('probation_periods'),
			    'probation_end_date'    => $this->bpas->fld($probation_end_date),		
			    'description'  			=> $description,
			    'received_asset'        => $this->input->post('asset'),
				'employee_id'  			=> $employee_id
			);
			if ($_FILES['attachment']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('attachment')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->addEmployeeOnBoarding($data)) {
			$this->session->set_flashdata('message', $this->lang->line("on_boarding_added"));
            admin_redirect("hr/edit_employee/".$employee_id."/#on_boarding");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['employee_id'] = $employee_id;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/add_on_boarding', $this->data);	
		}	
	}
	public function edit_on_boarding($id = false)
	{
		$this->form_validation->set_rules('joining_date', lang("joining_date"), 'required');

		$work = $this->hr_model->getOnBoardngByID($id);
		if ($this->form_validation->run() == true) 
		{	
			$joining_date			= $this->input->post('joining_date');
			$probation_end_date  	= $this->input->post('probation_end_date');
			$description			= $this->input->post('description');
		
			$data = array(
				'joining_date'   		=> $this->bpas->fld($joining_date),		
				'probation_periods'     => $this->input->post('probation_periods'),
			    'probation_end_date'    => $this->bpas->fld($probation_end_date),		
			    'description'  			=> $description,
			    'received_asset'        => $this->input->post('asset'),
				'employee_id'  			=> $work->employee_id
			);
			
			if ($_FILES['w_document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('w_document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
		}
		
		if ($this->form_validation->run() == true && $id = $this->hr_model->updateOnBoardng($id,$data)) {
			$this->session->set_flashdata('message', $this->lang->line("on_boarding_updated"));
            admin_redirect("hr/edit_employee/".$work->employee_id."/#on_boarding");
			
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id'] = $id;
			$this->data['row'] = $work;
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'hr/edit_on_boarding', $this->data);	
		}	
	}
	public function delete_on_boarding($id = null)
    {		
        if (isset($id) || $id != null){
        	 if ($this->hr_model->deleteOnBoardng($id)) {
				if ($this->input->is_ajax_request()) {
					$this->bpas->send_json(['error' => 0, 'msg' => lang('on_boarding_deleted')]);
					
				}
				$this->session->set_flashdata('message', lang('on_boarding_deleted'));
				admin_redirect('welcome');
			}
        }
    }
}
?>