<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendances extends MY_Controller
{
	function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        //$this->lang->admin_load('attendances',  $this->Settings->user_language);
		$this->load->library('ion_auth');
        $this->load->library('form_validation');
        $this->load->admin_model('attendances_model');
		$this->load->admin_model('hr_model');
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
		$this->load->helper('widget');
		$this->load->helper('date_time');
    }
	
	public function index()
	{
		$this->bpas->checkPermissions('generate_attendances');
		$post = $this->input->post();
		if (isset($post['generate_attendance'])) {
			$employee_id = $post['employee'];
			$start_date = $this->bpas->fld($post['start_date']);
			$end_date = $this->bpas->fld($post['end_date']);
			$biller_id = $post['biller'];
			$position_id = $post['position'];
			$department_id = $post['department'];
			$group_id = $post['group'];
			$this->getAttLog();
		}
		if (isset($post['generate_attendance']) && $this->attendances_model->generateAttendance($employee_id,$start_date,$end_date,$biller_id,$position_id,$department_id,$group_id,'active')) {
            $this->session->set_flashdata('message', lang("attendance_generated"));
            admin_redirect('attendances');
        } else {
			$this->data['billers'] = $this->site->getAllBiller();
			$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('generate_attendances')));
			$meta = array('page_title' => lang('generate_attendances'), 'bc' => $bc);
			$this->page_construct('attendances/generate_attendances', $meta, $this->data);
		}
		
	}

    public function suggestions()
    {
    	if (($this->Owner || $this->Admin) && !$this->session->userdata('employee_id')) {
    		$term = $this->input->get('term', true); 
    	}else{
    		$employee_id = $this->session->userdata('employee_id');
    		$employee 	= $this->hr_model->getEmployeeById($employee_id);
    		$term = 'mean'; //$employee->lastname;
    	}
        
		$biller_id = $this->input->get('biller_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->attendances_model->getAllEmployee($sr,$biller_id);
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

	
	public function approve_attendances()
	{
		$this->bpas->checkPermissions('approve_attendances');
		$post = $this->input->post();
		
		if(isset($post['approve'])){
			if($post['val']){
				$data = array();
				$month = explode("/",$post['approve_month']);
				foreach($post['val'] as $i => $att_id){
					$attendance = $this->attendances_model->getAttendanceByID($att_id);
					$data[] = array(
								"employee_id"	=> $attendance->employee_id,
								"month"			=> $month[0],
								"year"			=> $month[1],
								"working_day"	=> $post['working_day'][$i],
								"present"		=> $post['present'][$i],
								"permission"	=> $post['permission'][$i],
								"absent"		=> $post['absent'][$i],
								"late"			=> $post['late'][$i],
								"leave_early"	=> $post['leave_early'][$i],
								"normal_ot"		=> $post['normal_ot'][$i],
								"weekend_ot"	=> $post['weekend_ot'][$i],
								"holiday_ot"	=> $post['holiday_ot'][$i],
								"start_date"	=> $post['start_date'],
								"end_date"		=> $post['end_date'],
								"project_id"    => $this->input->post('project'),
							);
				}
			}
			
			if($post['val'] && $this->attendances_model->approveAttendance($data)){
				$this->session->set_flashdata('message', $this->lang->line("attendance_added"));
				admin_redirect($_SERVER['HTTP_REFERER']);
			}else{
				$this->session->set_flashdata('error', $this->lang->line("attendance_required"));
				admin_redirect($_SERVER['HTTP_REFERER']);
			}
		}
		
		$this->data['modal_js'] = $this->site->modal_js();
		$this->data['billers'] = $this->site->getAllBiller();
		$this->data['policies'] = $this->hr_model->getPolicies();
		if($this->Settings->project == 1){
            $this->data['projects'] = $this->site->getAllProjects();
        }
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('approve_attendances')));
		$meta = array('page_title' => lang('approve_attendances'), 'bc' => $bc);
		$this->page_construct('attendances/approve_attendances', $meta, $this->data);
	}
	
	public function getApproveAttendances()
    {
		$this->bpas->checkPermissions('approve_attendances');
        $this->load->library('datatables');
		$biller 	= $this->input->get("biller");
		$position 	= $this->input->get("position");
		$department = $this->input->get("department");
		$group 		= $this->input->get("group");
		$policy_id  = $this->input->get("policy_id");

		if($this->input->get("start_date")){
			$start_date = $this->input->get("start_date");
		}else{
			$start_date = date('d/m/Y');
		}
		if($this->input->get("end_date")){
			$end_date = $this->input->get("end_date");
		}else{
			$end_date = date('d/m/Y');
		}
		
		$this->attendances_model->generateAttendance(false,$this->bpas->fsd($start_date),$this->bpas->fsd($end_date),$biller,$position,$department,$group);
		
        $this->datatables->select("
					bpas_att_attedances.id as id,
					CONCAT(bpas_hr_employees.empcode, ' ', bpas_att_attedances.working_day) as ccc,
					concat(bpas_hr_employees.lastname,' ',bpas_hr_employees.firstname) as name,
					bpas_hr_positions.name as position,
					bpas_hr_departments.name as department,
					IFNULL(SUM(bpas_att_attedances.working_day),0) as working_day,
					IFNULL(SUM(bpas_att_attedances.present),0) as present,
					IFNULL(SUM(bpas_att_attedances.permission),0) as permission,
					IFNULL(SUM(bpas_att_attedances.absent),0) as absent,
					IFNULL(SUM(bpas_att_attedances.late),0) as late,
					IFNULL(SUM(bpas_att_attedances.leave_early),0) as leave_early,
					IFNULL(SUM(bpas_att_attedances.normal_ot),0) as normal_ot,
					IFNULL(SUM(bpas_att_attedances.weekend_ot),0) as weekend_ot,
					IFNULL(SUM(bpas_att_attedances.holiday_ot),0) as holiday_ot,
					")
            ->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner")
			->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
			->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left")
			->join("att_attedances","att_attedances.employee_id = hr_employees.id","left")
			->where("att_attedances.working_day >", 0)
			->where("att_attedances.status", 0)
			->where("hr_employees_working_info.status", 'active')
			->group_by('att_attedances.employee_id');
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
		}
		if($biller){
			$this->datatables->where("hr_employees_working_info.biller_id",$biller);
		}
		if($policy_id){
			$this->datatables->where("hr_employees_working_info.policy_id",$policy_id);
		}
		if($department){
			$this->datatables->where("department_id",$department);
		}
		if($position){
			$this->datatables->where("position_id",$position);
		}
		if($group){
			$this->datatables->where("group_id",$group);
		}

		if ($start_date) {
			$this->datatables->where($this->db->dbprefix('att_attedances').'.date BETWEEN "' . $this->bpas->fld($start_date) . '" and "' . $this->bpas->fld($end_date) . '"');
		}
		if(!$biller && !$department && !$position && !$group && !$start_date && !$end_date){
			$this->datatables->where("att_attedances.id",0);
		}
        echo $this->datatables->generate();
    }
	
	public function cancel_attendances()
	{
		$this->bpas->checkPermissions('cancel_attendances');
		$post = $this->input->post();
		if(isset($post['cancel'])){
			if($post['val']){
				$ids = array();
				foreach($post['val'] as $i => $id){
					$ids[] = $id;
				}
			}
			if($post['val'] && $this->attendances_model->cancelAttendance($ids)){
				$this->session->set_flashdata('message', $this->lang->line("attendance_canceled"));
			}else{
				$this->session->set_flashdata('error', $this->lang->line("attendance_required"));
				admin_redirect($_SERVER['HTTP_REFERER']);
			}
		}
		$this->data['modal_js'] = $this->site->modal_js();
		$this->data['billers'] = $this->site->getAllBiller();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('cancel_attendances')));
		$meta = array('page_title' => lang('cancel_attendances'), 'bc' => $bc);
		$this->page_construct('attendances/cancel_attendances', $meta, $this->data);
	}
	
	public function getCancelAttendances()
    {
		$this->bpas->checkPermissions('cancel_attendances');
        $this->load->library('datatables');
		$biller 	= $this->input->get("biller");
		$position 	= $this->input->get("position");
		$department = $this->input->get("department");
		$group 		= $this->input->get("group");
		$employee 	= $this->input->get("employee");
		$approve_month = $this->input->get("approve_month");
		
        $this->datatables->select("
					att_approve_attedances.id as id,
					hr_employees.empcode,
					concat(lastname,' ',firstname) as name,
					hr_positions.name as position,
					hr_departments.name as department,
					att_approve_attedances.working_day,
					att_approve_attedances.present,
					att_approve_attedances.permission,
					att_approve_attedances.absent,
					att_approve_attedances.late,
					att_approve_attedances.leave_early,
					att_approve_attedances.normal_ot,
					att_approve_attedances.weekend_ot,
					att_approve_attedances.holiday_ot")
            ->from("hr_employees")
			->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner")
			->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
			->join("hr_positions","hr_employees_working_info.position_id = hr_positions.id","left")
			->join("att_approve_attedances","att_approve_attedances.employee_id=hr_employees.id","left");
			// ->where("att_approve_attedances.status", 0);
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
		}
		if($biller){
			$this->datatables->where("hr_employees_working_info.biller_id",$biller);
		}
		if($department){
			$this->datatables->where("hr_employees_working_info.department_id",$department);
		}
		if($position){
			$this->datatables->where("hr_employees_working_info.position_id",$position);
		}
		if($group){
			$this->datatables->where("hr_employees_working_info.group_id",$group);
		}
		if($employee){
			$this->datatables->where("hr_employees.id",$employee);
		}
		if ($approve_month) {
			$month = explode("/",$approve_month);
			$this->datatables->where("month", $month[0]);
			$this->datatables->where("year", $month[1]);
		}else{
			$this->datatables->where("month", date("m"));
			$this->datatables->where("year", date("Y"));
		}
        echo $this->datatables->generate();
    }
	
	public function take_leaves()
    {
        $this->bpas->checkPermissions('take_leaves');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('take_leaves')));
        $meta = array('page_title' => lang('take_leaves'), 'bc' => $bc);
        $this->page_construct('attendances/take_leaves', $meta, $this->data);
    }
	
	public function get_take_leaves()
    {
        $this->bpas->checkPermissions('take_leaves');
        $edit_link = anchor('admin/attendances/edit_take_leave/$1', '<i class="fa fa-edit"></i> ' . lang('edit_take_leave'), 'class="sledit"');
        
		$approve_link = "<a href='#' class='po' title='<b>" . lang("approve_take_leave") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/approve_take_leave/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
        . lang('approve_take_leave') . "</a>";
		
		$reject_link = "<a href='#' class='po' title='<b>" . lang("reject_take_leave") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/approve_take_leave/$1/1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-close\"></i> "
        . lang('reject_take_leave') . "</a>";
		
		$delete_link = "<a href='#' class='po' title='<b>" . lang("delete_take_leave") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/delete_take_leave/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_take_leave') . "</a>";


        $pi = "( 
            SELECT take_leave_id,  
                GROUP_CONCAT(CONCAT(
                {$this->db->dbprefix('hr_employees')}.empcode, ' - ', 
                (CONCAT({$this->db->dbprefix('hr_employees')}.lastname, ' ', {$this->db->dbprefix('hr_employees')}.firstname))
				)) as item_nane 
            from {$this->db->dbprefix('att_take_leave_details')} 

            LEFT JOIN {$this->db->dbprefix('hr_employees')} ON {$this->db->dbprefix('hr_employees')}.id={$this->db->dbprefix('att_take_leave_details')}.employee_id";

            $pi .= " GROUP BY {$this->db->dbprefix('att_take_leave_details')}.take_leave_id ) FPI";



        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $edit_link . '</li>
				<li>' . $approve_link . '</li>
				<li>' . $reject_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
			
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('att_take_leaves')}.id as id, date, 
            	reference_no,
            	(FPI.item_nane) as iname,
            	CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, 
            	note, 
            	IF({$this->db->dbprefix('att_take_leaves')}.status=0,'pending',IF({$this->db->dbprefix('att_take_leaves')}.status=1,'approved','rejected')), 
            	attachment")
            ->from('att_take_leaves')
            ->join($pi, 'FPI.take_leave_id=att_take_leaves.id', 'left')
            ->join('users', 'users.id=att_take_leaves.created_by', 'left')
            ->group_by("att_take_leaves.id");

			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('att_take_leaves.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('att_take_leaves.created_by', $this->session->userdata('user_id'));
			}
		$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();

    }
	
	public function add_take_leave()
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
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tl',$biller_id);
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$employee_info = $this->attendances_model->getEmployeeInfoByID($employee_id);
				$start_date = $this->bpas->fld($_POST['start_date'][$r]);
				$end_date = $this->bpas->fld($_POST['end_date'][$r]);
				if($start_date > $end_date){
					$this->session->set_flashdata('error', lang("start_date")." cannot bigger than ".lang("end_date"));
					$this->bpas->md();
				}
				$timeshift = $_POST['timeshift'][$r];
				$reason = $_POST['reason'][$r];
				$leave_type_id = isset($_POST['leave_type'][$r])? $_POST['leave_type'][$r]: '';
				$leave_type = $this->hr_model->getLeaveTypeByID($leave_type_id);
				if($leave_type){
					$dataDetails[] = array(
						'employee_id' => $employee_id,
						'leave_type' =>$leave_type_id,
						'leave_category_id' =>$leave_type->category_id,
						'start_date' =>$start_date,
						'end_date' =>$end_date,
						'timeshift' =>$timeshift,
						'reason' =>$reason,
					);
				}
				
				$begin = new DateTime($start_date);
				$end = new DateTime($end_date);
				$year_begin = date("Y", strtotime($start_date));
				$year_end = date("Y", strtotime($end_date));
				$month_begin = date("m", strtotime($start_date));
				$month_end = date("m", strtotime($end_date));
				$old_year = $year_begin;
				$old_month = $month_begin;
				$t = 0;
				$m = 0;
				$y = 0;
				for($d = $begin; $d <= $end; $d->modify('+1 day')){
					$working_day = $this->attendances_model->getEmployeeWorkingDay($employee_id,$d->format("Y-m-d"),$d->format("Y-m-d"),$d->format("Y-m-d"));
					if($working_day > 0){
						if($working_day > 0.5 && $timeshift=="full"){
							$t += 1;
						}else{
							$t += 0.5;
						}
						$dataEmployees[] = array(
							'employee_id' => $employee_id,
							'leave_type' =>$leave_type_id,
							'leave_category_id' =>$leave_type->category_id,
							'date' =>$d->format("Y-m-d"),
							'timeshift' => $working_day > 0.5 ? $timeshift : "morning",
							'reason' =>$reason,
						);
						
						if($year_begin != $year_end){
							if($old_year == $d->format("Y")){
								if($working_day > 0.5 && $timeshift=="full"){
									$y += 1;
								}else{
									$y += 0.5;
								}
							}else{
								$old_year = $d->format("Y");
								if($working_day > 0.5 && $timeshift=="full"){
									$y = 1;
								}else{
									$y = 0.5;
								}
							}
							$used_leave = $this->attendances_model->getUsedLeaveByEmployee($employee_id,$leave_type->category_id,$d->format("Y"));
							if($y > ($used_leave->total_leave - $used_leave->used_leave)){
								$employee = $this->hr_model->getEmployeeById($employee_id);
								$this->session->set_flashdata('error', lang($used_leave->category_code."_balance")." ".($used_leave->total_leave - $used_leave->used_leave)." - ".$employee->empcode);
								$this->bpas->md();
							}
						}
						
						
						if($employee_info->monthly_annual_leave > 0 && $leave_type->category_id == 1 && $month_begin != $month_end){
							if($old_month == $d->format("m")){
								if($working_day > 0.5 && $timeshift=="full"){
									$m += 1;
								}else{
									$m += 0.5;
								}
							}else{
								$old_month = $d->format("m");
								if($working_day > 0.5 && $timeshift=="full"){
									$m = 1;
								}else{
									$m = 0.5;
								}
							}
							$used_leave = $this->attendances_model->getMonthlyUsedLeaveByEmployee($employee_id,$leave_type->category_id,$year_begin,$d->format("m"));
							$total_leave = $employee_info->monthly_annual_leave;
							if($m > ($total_leave - $used_leave->used_leave)){
								$employee = $this->hr_model->getEmployeeById($employee_id);
								$this->session->set_flashdata('error', lang("monthly")." ".lang($used_leave->category_code."_balance")." ".($total_leave - $used_leave->used_leave)." - ".$employee->empcode);
								$this->bpas->md();
							}
						}
						
					}
				}

				if($year_begin == $year_end){
					$used_leave = $this->attendances_model->getUsedLeaveByEmployee($employee_id,$leave_type->category_id,$year_begin);
					if($t > ($used_leave->total_leave - $used_leave->used_leave)){
						$employee = $this->hr_model->getEmployeeById($employee_id);
						$this->session->set_flashdata('error', lang($used_leave->category_code."_balance")." ".($used_leave->total_leave - $used_leave->used_leave)." - ".$employee->empcode);
						$this->bpas->md();
					}
				}
				
				if($employee_info->monthly_annual_leave > 0 && $leave_type->category_id == 1 && $month_begin == $month_end){
					$used_leave = $this->attendances_model->getMonthlyUsedLeaveByEmployee($employee_id,$leave_type->category_id,$year_begin,$month_begin);
					$total_leave = $employee_info->monthly_annual_leave;
					if($t > ($total_leave - $used_leave->used_leave)){
						$employee = $this->hr_model->getEmployeeById($employee_id);
						$this->session->set_flashdata('error', lang("monthly")." ".lang($used_leave->category_code."_balance")." ".($total_leave - $used_leave->used_leave)." - ".$employee->empcode);
						$this->bpas->md();
					}
				}
            }
            if (empty($dataDetails)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'biller_id' => $biller_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
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
        if ($this->form_validation->run() == true && $this->attendances_model->addTakeLeave($data, $dataDetails, $dataEmployees)) {
            $this->session->set_userdata('remove_tls', 1);
            $this->session->set_flashdata('message', lang("take_leave_added")." - ".$data['reference_no']);
            admin_redirect('attendances/take_leaves');
        } else {
			$leave_types = $this->hr_model->getAllLeaveTypes();
			$leave_type_html = '<select name="leave_type[]"  class="form-control select leave_type" style="width:100%;">';
			if($leave_types){
				foreach($leave_types as $leave_type){
					$leave_type_html .='<option value="'.$leave_type->id.'">'.$leave_type->name.'</option>';
				}
			}
			$leave_type_html .='</select>';
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
			$this->data['leave_type'] = $leave_type_html;
			$this->data['reference'] = $this->site->getReference('tl');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => site_url('attendances/take_leaves'), 'page' => lang('take_leaves')), array('link' => '#', 'page' => lang('add_take_leave')));
            $meta = array('page_title' => lang('add_take_leave'), 'bc' => $bc);
            $this->page_construct('attendances/add_take_leave', $meta, $this->data);
        }
    }
	
	public function edit_take_leave($id)
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
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('tl',$biller_id);
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$employee_info = $this->attendances_model->getEmployeeInfoByID($employee_id);
				$start_date = $this->bpas->fld($_POST['start_date'][$r]);
				$end_date = $this->bpas->fld($_POST['end_date'][$r]);
				if($start_date > $end_date){
					$this->session->set_flashdata('error', lang("start_date")." cannot bigger than ".lang("end_date"));
					$this->bpas->md();
				}
				$timeshift = $_POST['timeshift'][$r];
				$reason = $_POST['reason'][$r];
				$leave_type_id = isset($_POST['leave_type'][$r])? $_POST['leave_type'][$r]: '';
				$leave_type = $this->hr_model->getLeaveTypeByID($leave_type_id);
				if($leave_type){
					$dataDetails[] = array(
						'take_leave_id' => $id,
						'employee_id' => $employee_id,
						'leave_type' =>$leave_type_id,
						'leave_category_id' =>$leave_type->category_id,
						'start_date' =>$start_date,
						'end_date' =>$end_date,
						'timeshift' =>$timeshift,
						'reason' =>$reason,
					);
				}

				$begin = new DateTime($start_date);
				$end = new DateTime($end_date);
				$year_begin = date("Y", strtotime($start_date));
				$year_end = date("Y", strtotime($end_date));
				$month_begin = date("m", strtotime($start_date));
				$month_end = date("m", strtotime($end_date));
				$old_year = $year_begin;
				$old_month = $month_begin;
				$t = 0;
				$m = 0;
				$y = 0;
				for($d = $begin; $d <= $end; $d->modify('+1 day')){
					$working_day = $this->attendances_model->getEmployeeWorkingDay($employee_id,$d->format("Y-m-d"),$d->format("Y-m-d"),$d->format("Y-m-d"));
					if($working_day > 0){
						if($working_day > 0.5 && $timeshift=="full"){
							$t += 1;
						}else{
							$t += 0.5;
						}
						$dataEmployees[] = array(
							'take_leave_id' => $id,
							'employee_id' => $employee_id,
							'leave_type' =>$leave_type_id,
							'leave_category_id' =>$leave_type->category_id,
							'date' =>$d->format("Y-m-d"),
							'timeshift' => $working_day > 0.5 ? $timeshift : "morning",
							'reason' =>$reason,
						);
						
						if($year_begin != $year_end){
							if($old_year == $d->format("Y")){
								if($working_day > 0.5 && $timeshift=="full"){
									$y += 1;
								}else{
									$y += 0.5;
								}
							}else{
								$old_year = $d->format("Y");
								if($working_day > 0.5 && $timeshift=="full"){
									$y = 1;
								}else{
									$y = 0.5;
								}
							}
							$used_leave = $this->attendances_model->getUsedLeaveByEmployee($employee_id,$leave_type->category_id,$d->format("Y"));
							if($y > ($used_leave->total_leave - $used_leave->used_leave)){
								$employee = $this->hr_model->getEmployeeById($employee_id);
								$this->session->set_flashdata('error', lang($used_leave->category_code."_balance")." ".($used_leave->total_leave - $used_leave->used_leave)." - ".$employee->empcode);
								$this->bpas->md();
							}
						}

						if($employee_info->monthly_annual_leave > 0 && $leave_type->category_id == 1 && $month_begin != $month_end){
							if($old_month == $d->format("m")){
								if($working_day > 0.5 && $timeshift=="full"){
									$m += 1;
								}else{
									$m += 0.5;
								}
							}else{
								$old_month = $d->format("m");
								if($working_day > 0.5 && $timeshift=="full"){
									$m = 1;
								}else{
									$m = 0.5;
								}
							}
							$used_leave = $this->attendances_model->getMonthlyUsedLeaveByEmployee($employee_id,$leave_type->category_id,$year_begin,$d->format("m"));
							$total_leave = $employee_info->monthly_annual_leave;
							if($m > ($total_leave - $used_leave->used_leave)){
								$employee = $this->hr_model->getEmployeeById($employee_id);
								$this->session->set_flashdata('error', lang("monthly")." ".lang($used_leave->category_code."_balance")." ".($total_leave - $used_leave->used_leave)." - ".$employee->empcode);
								$this->bpas->md();
							}
						}
					}
				}
				
				
				if($year_begin == $year_end){
					$used_leave = $this->attendances_model->getUsedLeaveByEmployee($employee_id,$leave_type->category_id,$year_begin);
					if($t > ($used_leave->total_leave - $used_leave->used_leave)){
						$employee = $this->hr_model->getEmployeeById($employee_id);
						$this->session->set_flashdata('error', lang($used_leave->category_code."_balance")." ".($used_leave->total_leave - $used_leave->used_leave)." - ".$employee->empcode);
						$this->bpas->md();
					}
				}
				
				if($employee_info->monthly_annual_leave > 0 && $leave_type->category_id == 1 && $month_begin == $month_end){
					$used_leave = $this->attendances_model->getMonthlyUsedLeaveByEmployee($employee_id,$leave_type->category_id,$year_begin,$month_begin);
					$total_leave = $employee_info->monthly_annual_leave;
					if($t > ($total_leave - $used_leave->used_leave)){
						$employee = $this->hr_model->getEmployeeById($employee_id);
						$this->session->set_flashdata('error', lang("monthly")." ".lang($used_leave->category_code."_balance")." ".($total_leave - $used_leave->used_leave)." - ".$employee->empcode);
						$this->bpas->md();
					}
				}
				
            }
            if (empty($dataDetails)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }
            $data = array(
                'date' => $date,
                'reference_no' => $reference_no,
				'biller_id' => $biller_id,
                'note' => $note,
				'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id'),
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

        if ($this->form_validation->run() == true && $this->attendances_model->updateTakeLeave($id, $data, $dataDetails, $dataEmployees)) {
            $this->session->set_userdata('remove_tls', 1);
            $this->session->set_flashdata('message', lang("take_leave_updated")." - ".$data['reference_no']);
            admin_redirect('attendances/take_leaves');
        } else {
			$take_leave = $this->attendances_model->getTakeLeaveByID($id);
            $inv_items = $this->attendances_model->getTakeLeaveDetail($id);
            krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $employee = $this->hr_model->getEmployeeById($item->employee_id);
                $row = json_decode('{}');
                $row->id = $item->employee_id;
                $row->empcode = $employee->empcode;
                $row->firstname = $employee->firstname;
				$row->lastname = $employee->lastname;
                $row->start_date = $this->bpas->hrsd($item->start_date);
                $row->end_date = $this->bpas->hrsd($item->end_date);
				$row->leave_type = $item->leave_id;
				$row->timeshift = $item->timeshift;
				$row->reason = $item->reason;
				
                $pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->lastname .' '.$row->firstname. " (" . $row->empcode . ")",
                    'row' => $row);

                $c++;
            }
			$leave_types = $this->hr_model->getAllLeaveTypes();
			$leave_type_html = '<select name="leave_type[]"  class="form-control select leave_type" style="width:100%;">';
			if($leave_types){
				foreach($leave_types as $leave_type){
					$leave_type_html .='<option value="'.$leave_type->id.'">'.$leave_type->name.'</option>';
				}
			}
			$leave_type_html .='</select>';
			$this->data['leave_type'] = $leave_type_html;
            $this->data['take_leave'] = $take_leave;
            $this->data['take_leave_details'] = json_encode($pr);
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->session->set_userdata('remove_tls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')),array('link' => site_url('attendances/take_leaves'), 'page' => lang('take_leaves')), array('link' => '#', 'page' => lang('edit_take_leave')));
            $meta = array('page_title' => lang('edit_take_leave'), 'bc' => $bc);
            $this->page_construct('attendances/edit_take_leave', $meta, $this->data);

        }
    }
	
	public function delete_take_leave($id = null)
    {
        $this->bpas->checkPermissions('take_leaves', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if ($this->attendances_model->deleteTakeLeave($id)) {
            if ($this->input->is_ajax_request()) {
				$this->bpas->send_json(['error' => 0, 'msg' => lang('take_leave_deleted')." ".$take_leave->reference_no]);
            }
            $this->session->set_flashdata('message', lang('take_leave_deleted')." ".$take_leave->reference_no);
            admin_redirect('attendances/take_leaves');
        }
    }
	
	public function take_leave_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
						$take_leave = $this->attendances_model->getTakeLeaveByID($id);
						if($take_leave->status == 0){
							$deleted = $this->attendances_model->deleteTakeLeave($id);
						}
                        
                    }
					if($deleted){
						$this->session->set_flashdata('message', lang("take_leaves_deleted"));
						admin_redirect($_SERVER["HTTP_REFERER"]);
					}else{
						$this->session->set_flashdata('error', lang("take_leave_cannot_delete"));
						admin_redirect($_SERVER["HTTP_REFERER"]);
					}
                    

                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('take_leaves');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
					$this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));


                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $take_leave = $this->attendances_model->getTakeLeaveByID($id);
                        $created_by = $this->site->getUser($take_leave->created_by);
						$biller = $this->site->getCompanyByID($take_leave->biller_id);
						if($take_leave->status==1){
							$status = lang('approved');
						}else if($take_leave->status==2){
							$status = lang('rejected');
						}else{
							$status = lang('pending');
						}

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($take_leave->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $take_leave->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $biller->company);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->decode_html($take_leave->note));
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, $status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'take_leave_' . date('Y_m_d_H_i_s');
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
	
	public function approve_take_leave($id = null, $reject = false)
    {
        $this->bpas->checkPermissions('approve_take_leave', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
		if($reject){
			$status = 2;
		}else{
			$status = 1;
		}
		$take_leave = $this->attendances_model->getTakeLeaveByID($id);
		if($take_leave->status==1  && $status==1){
			$this->session->set_flashdata('error', lang('take_leave_is_already_approved').' '.$take_leave->reference_no);
            admin_redirect('attendances/take_leaves');
			die();
		}
		if($take_leave->status==2  && $status==2){
			$this->session->set_flashdata('error', lang('take_leave_is_already_rejected').' '.$take_leave->reference_no);
            admin_redirect('attendances/take_leaves');
			die();
		}
		$data = array('status'=>$status, 'status_by'=>$this->session->userdata('user_id'));
        if ($this->attendances_model->approveTakeLeave($id,$data)) {
			if($status==2){
				$this->session->set_flashdata('message', lang('take_leave_rejected')." ".$take_leave->reference_no);
			}else{
				$this->session->set_flashdata('message', lang('take_leave_approved')." ".$take_leave->reference_no);
			}
			admin_redirect('attendances/take_leaves');
        }
    }
	
	public function view_take_leave($id)
    {
        $this->bpas->checkPermissions('take_leaves', TRUE);

        $take_leave = $this->attendances_model->getTakeLeaveByID($id);
        if (!$id || !$take_leave) {
            $this->session->set_flashdata('error', lang('take_leave_not_found'));
            $this->bpas->md();
        }

        $this->data['inv'] = $take_leave;
	
		$this->data['biller'] = $this->site->getCompanyByID($take_leave->biller_id);
        $this->data['rows'] = $this->attendances_model->getTakeLeaveDetail($id);
	//     var_dump($this->data['rows']);exit();
        $this->data['created_by'] = $this->site->getUser($take_leave->created_by);
        $this->data['updated_by'] = $this->site->getUser($take_leave->updated_by);
		
		if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
			$this->data['print'] = 0;
		}else{
			if($this->Settings->limit_print=='1' && $this->site->checkPrint('Take Leave',$take_leave->id)){
				$this->data['print'] = 1;
			}else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Take Leave',$take_leave->id)){
				$this->data['print'] = 2;
			}else{
				$this->data['print'] = 0;
			}
		}
        $this->load->view($this->theme.'attendances/view_take_leave', $this->data);
    }
	
	public function check_in_outs()
    {
        $this->bpas->checkPermissions('check_in_outs');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('check_in_outs')));
        $meta = array('page_title' => lang('check_in_outs'), 'bc' => $bc);
        $this->page_construct('attendances/check_in_outs', $meta, $this->data);
    }
	
	public function get_check_in_outs()
    {
        $this->bpas->checkPermissions('check_in_outs');
		$delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_check_in_out") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' href='" . admin_url('attendances/delete_check_in_out/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select('att_check_in_out.id as id,empcode,
            	concat('.$this->db->dbprefix('hr_employees').'.lastname," ",'.$this->db->dbprefix('hr_employees').'.firstname) as full_name, check_time')
            ->from('att_check_in_out')
			->join('hr_employees','hr_employees.id = att_check_in_out.employee_id','inner')
            ->group_by('att_check_in_out.id');
		$this->datatables->add_column("Actions", "<div class='text-center'>" . $delete_link . "</div>", "id");
        echo $this->datatables->generate();

    }
	
	public function delete_check_in_out($id = null)
    {
        $this->bpas->checkPermissions('delete_check_in_out', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

		if ($this->attendances_model->deleteCheckInOut($id)) {
            if ($this->input->is_ajax_request()) {
				$this->bpas->send_json(['error' => 0, 'msg' => lang('check_in_out_deleted')]);
            }
            $this->session->set_flashdata('message', lang('check_in_out_deleted'));
            admin_redirect('attendances/check_in_outs');
        }
    }

	public function employee_check_in_out()
    {
        $this->bpas->checkPermissions('add_check_in_out', true);
		$user_id 	 = $this->session->userdata('user_id');
		$user 		 = $this->site->getUser();
		$employee 	 = $this->hr_model->getEmployeeByCode($user->emp_code);
		if($employee){
			$employee_id = $employee->id;
			$check_time  = $this->input->get('date');
			$data[] 	 = array('employee_id' => $employee_id, 'check_time' => $check_time);
			if($this->attendances_model->addCheckInOut($data)){
				$this->bpas->send_json(['message' => 0, 'msg' => lang('check_in_out_added')]);
			}
			$this->bpas->send_json(['error' => 0, 'msg' => lang('check_in_out_failed')]);
		} 
		$this->bpas->send_json(['error' => 0, 'msg' => lang('employee_not_found')]);
		
    }

	public function add_check_in_out()
    {
        $this->bpas->checkPermissions('add_check_in_out', true);
		$this->form_validation->set_rules('add_check_in_out', lang("add_check_in_out"), 'required');
        if ($this->form_validation->run() == true) {
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $i = isset($_POST['employee_id']) ? sizeof($_POST['employee_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $employee_id = $_POST['employee_id'][$r];
				$check_time = $this->bpas->fld($_POST['check_time'][$r],true);
				if($employee_id && $check_time){
					$data[] = array(
						'employee_id' => $employee_id,
						'check_time'  => $check_time,
					);
				}
            }
            if (empty($data)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            } else {
                krsort($data);
            }
        }
        if ($this->form_validation->run() == true && $this->attendances_model->addCheckInOut($data)) {
            $this->session->set_userdata('remove_iols', 1);
            $this->session->set_flashdata('message', lang("check_in_out_added"));
            admin_redirect('attendances/check_in_outs');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['projects']     = $this->site->getAllProject();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')),array('link' => admin_url('attendances/check_in_outs'), 'page' => lang('check_in_outs')), array('link' => '#', 'page' => lang('add_check_in_out')));
            $meta = array('page_title' => lang('add_check_in_out'), 'bc' => $bc);
            $this->page_construct('attendances/add_check_in_out', $meta, $this->data);

        }
    }
	
	
	public function check_in_out_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_check_in_out');
                    foreach ($_POST['val'] as $id) {
						$deleted = $this->attendances_model->deleteCheckInOut($id);
                    }
					if($deleted){
						$this->session->set_flashdata('message', lang("check_in_out_deleted"));
						admin_redirect($_SERVER["HTTP_REFERER"]);
					}else{
						$this->session->set_flashdata('error', lang("check_in_out_cannot_delete"));
						admin_redirect($_SERVER["HTTP_REFERER"]);
					}
                    

                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('check_in_out');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('employee'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('check_time'));


                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $check_in_out = $this->attendances_model->getCheckInOutByID($id);
                        $employee = $this->hr_model->getEmployeeById($check_in_out->employee_id);
						$this->excel->getActiveSheet()->SetCellValue('A' . $row, $employee->lastname.' '.$employee->firstname);
						$this->excel->getActiveSheet()->SetCellValue('B' . $row, ($check_in_out->check_time));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'check_in_out_' . date('Y_m_d_H_i_s');
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
	
	public function policies()
	{
		$this->bpas->checkPermissions('policies');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('policies')));
		$meta = array('page_title' => lang('policies'), 'bc' => $bc);
		$this->page_construct('attendances/policies', $meta, $this->data);
	}
	
	public function getPolicies()
	{	
		$this->bpas->checkPermissions('policies');	
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_policy") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/delete_policy/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_policy') . "</a>";
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
					        	<li><a href="'.admin_url('attendances/policy_working_days/$1').'" ><i class="fa fa fa-cogs"></i>'.lang('policy_working_days').'</a></li>
								<li><a href="'.admin_url('attendances/edit_policy/$1').'" data-toggle="modal" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_policy').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					att_policies.id as id, 
					att_policies.code,
					att_policies.policy,
					att_policies.time_in_one,
					att_policies.time_out_one,
					att_policies.time_in_two,
					att_policies.time_out_two,
					att_policies.note")
            ->from("att_policies")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_policy()
	{
		$this->bpas->checkPermissions('policies');	
		$post = $this->input->post();
		$this->form_validation->set_rules('policy', lang("policy"), 'required');
		$this->form_validation->set_rules('time_in_one', lang("time_in_one"), 'required');
		$this->form_validation->set_rules('start_in_one', lang("start_in_one"), 'required');
		$this->form_validation->set_rules('end_in_one', lang("end_in_one"), 'required');
		$this->form_validation->set_rules('time_out_one', lang("time_out_one"), 'required');
		$this->form_validation->set_rules('start_out_one', lang("start_out_one"), 'required');
		$this->form_validation->set_rules('end_out_one', lang("end_out_one"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'policy'  		=> $post['policy'],
				'time_in_one'  	=> $post['time_in_one'],
				'start_in_one'  => $post['start_in_one'],
				'end_in_one'  	=> $post['end_in_one'],
				'time_out_one' 	=> $post['time_out_one'],
				'start_out_one' => $post['start_out_one'],
				'end_out_one'  	=> $post['end_out_one'],
				'time_in_two'  	=> $post['time_in_two'],
				'start_in_two'  => $post['start_in_two'],
				'end_in_two'  	=> $post['end_in_two'],
				'time_out_two' 	=> $post['time_out_two'],
				'start_out_two' => $post['start_out_two'],
				'end_out_two'  	=> $post['end_out_two'],
				'minimum_min'  	=> $post['minimum_min'],
				'round_min'  	=> $post['round_min'],
				'monthly_working_day'	=> $this->input->post('monthly_working_day'),
				'note' 			=> $post['note'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->attendances_model->addPolicy($data)) {
            $this->session->set_flashdata('message', $this->lang->line("policy_added").' '.$post['policy']);
            admin_redirect("attendances/policies");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/add_policy', $this->data);	
		}	
	}
	
	public function edit_policy($id = null)
	{		
		$this->bpas->checkPermissions('policies');	
		$post = $this->input->post();		
		
		$this->form_validation->set_rules('policy', lang("policy"), 'required');
		$this->form_validation->set_rules('time_in_one', lang("time_in_one"), 'required');
		$this->form_validation->set_rules('start_in_one', lang("start_in_one"), 'required');
		$this->form_validation->set_rules('end_in_one', lang("end_in_one"), 'required');
		$this->form_validation->set_rules('time_out_one', lang("time_out_one"), 'required');
		$this->form_validation->set_rules('start_out_one', lang("start_out_one"), 'required');
		$this->form_validation->set_rules('end_out_one', lang("end_out_one"), 'required');

		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'policy'  		=> $post['policy'],
				'time_in_one'  	=> $post['time_in_one'],
				'start_in_one'  => $post['start_in_one'],
				'end_in_one'  	=> $post['end_in_one'],
				'time_out_one' 	=> $post['time_out_one'],
				'start_out_one' => $post['start_out_one'],
				'end_out_one'  	=> $post['end_out_one'],
				'time_in_two'  	=> $post['time_in_two'],
				'start_in_two'  => $post['start_in_two'],
				'end_in_two'  	=> $post['end_in_two'],
				'time_out_two' 	=> $post['time_out_two'],
				'start_out_two' => $post['start_out_two'],
				'end_out_two'  	=> $post['end_out_two'],
				'minimum_min'  	=> $post['minimum_min'],
				'round_min'  	=> $post['round_min'],
				'monthly_working_day'	=> $this->input->post('monthly_working_day'),
				'note' 			=> $post['note'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $this->attendances_model->updatePolicy($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("policy_updated").' '.$post['policy']);
            admin_redirect("attendances/policies");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['policy'] = $this->attendances_model->getPolicyByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/edit_policy', $this->data);
		}			
	}
	
	public function delete_policy($id = null)
    {	
		$this->bpas->checkPermissions('policy');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("att_policies");
        	if($result){
        		$this->session->set_flashdata('message', lang("policy_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function policy_actions()
    {
		
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete-policy');
                    foreach ($_POST['val'] as $id) {
                        $this->attendances_model->deletePolicy($id);
                    }
                    $this->session->set_flashdata('message', lang("policy_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('policy');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('policy'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('time_in_one'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('time_out_one'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('time_in_two'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('time_out_two'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));	
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $policy = $this->attendances_model->getPolicyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $policy->policy);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $policy->time_in_one);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $policy->time_out_one);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $policy->time_in_two);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $policy->time_out_two);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, strip_tags($policy->note));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'policy_list_' . date('Y_m_d_H_i_s');
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
	
	function policy_working_days($id = NULL)
    {
        $this->form_validation->set_rules('policy', lang("policy"), 'required');
        $ot_policies = $this->attendances_model->getOTPolices();;
		if ($this->form_validation->run() == true) {
			$ot_data = false;
			$days = array(
						'0'=>'Mon',
						'1'=>'Tue',
						'2'=>'Wed',
						'3'=>'Thu',
						'4'=>'Fri',
						'5'=>'Sat',
						'6'=>'Sun',
						'7'=>'Hol',
					);
			foreach($days as $day){
				$day_one = $day.'-one';
				$day_two = $day.'-two';
				$time_one = ($this->input->post($day_one) ? 1 : 0);
				$time_two = ($this->input->post($day_two) ? 1 : 0);
				if($time_one!=0 || $time_two!=0){
					$data[] = array(
						'policy_id' => $id,
						'day' => $day,
						'time_one' => $time_one,
						'time_two' => $time_two
					);
				}
				if($ot_policies){
					foreach($ot_policies as $ot_policie){
						$ot_day = ($this->input->post($day.'-'.$ot_policie->id) ? 1 : 0);
						if($ot_day==1){
							$ot_data[] = array('policy_id' => $id,
										'ot_policy_id' => $ot_policie->id,
										'day' => $day,	
									);
						}
					}
				}
			}	
        }


        if ($this->form_validation->run() == true && $this->attendances_model->updatePolicyWorkingDay($id, $data, $ot_data)) {
            $this->session->set_flashdata('message', lang("policy_working_day_updated"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['id'] = $id;
			$this->data['policy'] = $this->attendances_model->getPolicyByID($id);
			$this->data['policy_working_days'] = $this->attendances_model->getPolicyWorkingDay($id);
			$this->data['ot_policies'] = $ot_policies;
			$this->data['ot_policy_working_days'] = $this->attendances_model->getOTPolicyWorkingDay($id);
			
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => admin_url('attendances/policies'), 'page' => lang('policies')), array('link' => '#', 'page' => lang('policy_working_days')));
            $meta = array('page_title' => lang('policy_working_days'), 'bc' => $bc);
            $this->page_construct('attendances/policy_working_days', $meta, $this->data);
        }
    }
	
	public function ot_policies()
	{
		$this->bpas->checkPermissions('ot_policies');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('ot_policies')));
		$meta = array('page_title' => lang('ot_policies'), 'bc' => $bc);
		$this->page_construct('attendances/ot_policies', $meta, $this->data);
	}
	
	public function getOTPolices()
	{	
		$this->bpas->checkPermissions('ot_policies');
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_ot_policy") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/delete_ot_policy/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_ot_policy') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('attendances/edit_ot_policy/$1').'" data-toggle="modal" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_ot_policy').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					att_ot_policies.id as id, 
					att_ot_policies.ot_policy, 
					att_ot_policies.time_in,
					att_ot_policies.time_out,
					att_ot_policies.note")
            ->from("att_ot_policies")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_ot_policy()
	{
		$this->bpas->checkPermissions('ot_policies');	
		$post = $this->input->post();
		$this->form_validation->set_rules('ot_policy', lang("ot_policy"), 'required');
		$this->form_validation->set_rules('time_in', lang("time_in"), 'required');
		$this->form_validation->set_rules('time_out', lang("time_out"), 'required');
		$this->form_validation->set_rules('type', lang("type"), 'required');
		$this->form_validation->set_rules('minimum_min', lang("minimum_min"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'ot_policy' => $post['ot_policy'],
				'time_in'  	=> $post['time_in'],
				'time_out'  => $post['time_out'],
				'start_in'  => $post['start_in'],
				'end_in'  	=> $post['end_in'],
				'start_out' => $post['start_out'],
				'end_out'  	=> $post['end_out'],
				'type'  	=> $post['type'],
				'minimum_min' => $post['minimum_min'],
				'round_min' => $post['round_min'],
				'note' 		=> $post['note'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->attendances_model->addOTPolicy($data)) {
            $this->session->set_flashdata('message', $this->lang->line("ot_policy_added").' '.$post['policy']);
            admin_redirect("attendances/ot_policies");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/add_ot_policy', $this->data);	
		}	
	}
	
	public function edit_ot_policy($id = null)
	{		
		$this->bpas->checkPermissions('ot_policies');	
		$post = $this->input->post();		
		
		$this->form_validation->set_rules('ot_policy', lang("ot_policy"), 'required');
		$this->form_validation->set_rules('time_in', lang("time_in"), 'required');
		$this->form_validation->set_rules('time_out', lang("time_out"), 'required');
		$this->form_validation->set_rules('type', lang("type"), 'required');
		$this->form_validation->set_rules('minimum_min', lang("minimum_min"), 'required');

		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'ot_policy' => $post['ot_policy'],
				'time_in'  	=> $post['time_in'],
				'time_out'  => $post['time_out'],
				'start_in'  => $post['start_in'],
				'end_in'  	=> $post['end_in'],
				'start_out' => $post['start_out'],
				'end_out'  	=> $post['end_out'],
				'type'  	=> $post['type'],
				'minimum_min' => $post['minimum_min'],
				'round_min' => $post['round_min'],
				'note' 		=> $post['note'],
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $this->attendances_model->updateOTPolicy($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("ot_policy_updated").' '.$post['policy']);
            admin_redirect("attendances/ot_policies");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['ot_policy'] = $this->attendances_model->getOTPolicyByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/edit_ot_policy', $this->data);
		}			
	}
	
	public function delete_ot_policy($id = null)
    {	
		$this->bpas->checkPermissions('ot_policies');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("att_ot_policies");
        	if($result){
        		$this->session->set_flashdata('message', lang("ot_policy_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
	
	function ot_policy_actions()
    {
		
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete-ot_policy');
                    foreach ($_POST['val'] as $id) {
                        $this->attendances_model->deleteOTPolicy($id);
                    }
                    $this->session->set_flashdata('message', lang("ot_policy_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('ot_policy');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('ot_policy'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('time_in'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('time_out'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));	
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $policy = $this->attendances_model->getOTPolicyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $policy->ot_policy);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $policy->time_in);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $policy->time_out);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, strip_tags($policy->note));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'ot_policy_list_' . date('Y_m_d_H_i_s');
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
	public function ot()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('ot')));
        $meta = array('page_title' => lang('ot'), 'bc' => $bc);
        $this->page_construct('attendances/ot', $meta, $this->data);
    }
	public function getOT()
    {
        $this->bpas->checkPermissions('ot');
        $approve_link = "<a href='#' class='po' title='<b>" . lang("approve_ot") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/approve_ot/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-check\"></i> "
        . lang('approve_ot') . "</a>";
		
		$reject_link = "<a href='#' class='po' title='<b>" . lang("reject_ot") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/reject_ot/$1/1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-close\"></i> "
        . lang('reject_ot') . "</a>";
		

		$edit_link = anchor('admin/attendances/edit_apply_ot/$1', '<i class="fa fa-edit"></i> ' . lang('edit_apply_ot'), 'class="edit_apply_ot"');
		
		$delete_link = "<a href='#' class='delete_ot po' title='<b>" . $this->lang->line("delete_ot") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('attendances/delete_ot/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_ot') . "</a>";
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $approve_link.'</li>
				<li>' . $reject_link . '</li>
				<li>' . $edit_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
			
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('att_apply_ot')}.id as id, 
            	created_date, 
            	CONCAT({$this->db->dbprefix('hr_employees')}.lastname, ' ',{$this->db->dbprefix('hr_employees')}.firstname) as employee, 
            	{$this->db->dbprefix('att_apply_ot')}.from_time,
            	{$this->db->dbprefix('att_apply_ot')}.to_time,
            	{$this->db->dbprefix('att_apply_ot')}.approve_status,
            	")
            ->from('att_apply_ot')
            ->join('hr_employees', 'hr_employees.id=att_apply_ot.employee_id', 'left');

			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('att_apply_ot.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('att_apply_ot.created_by', $this->session->userdata('user_id'));
			}
		$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
	}
	public function add_ot()
    {
        $this->bpas->checkPermissions('add_ot');	 
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		if ($this->form_validation->run() == true) {	
			$data = array(
				'candidate_id'  	=> $this->input->post('employee'),
				'from_time'  		=> $this->bpas->fsd($this->input->post('from_time')),
				'to_time'  			=> $this->bpas->fsd($this->input->post('to_time')),
				'description' 		=> $this->input->post('description'),
			);
		}
		if ($this->form_validation->run() == true && $id = $this->hr_model->add_shortlist($data)) {
            $this->session->set_flashdata('message', $this->lang->line("ot_added"));
            admin_redirect("attendances/ot");
        }else{
			$this->data['error']     = validation_errors() ? validation_errors() : $this->session->flashdata('error');  
			$this->data['id'] 		 = isset($id)? $id: '';
			$this->data['billers'] 	 = $this->hr_model->getCompanies();
			$this->data['positions'] = $this->hr_model->getAllPositions();
			$this->data['employees'] = $this->hr_model->getEmployees(false,false,false,false,'active');
			$this->data['modal_js']  = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/apply_ot', $this->data);	
		}
    }
	
	public function edit_apply_ot($id)
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
				$day_off = $this->bpas->fsd($_POST['day_off'][$r]);
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'day_off_id' => $id,
						'employee_id' => $employee_id,
						'day_off' =>$day_off,
						'description' => $description,
					);
				}
            }
 

            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }

            $data = array(
                'date' => $date,
				'biller_id' => $biller_id,
                'note' => $note,
				'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id'),
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

        if ($this->form_validation->run() == true && $this->attendances_model->updateDayOff($id, $data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("day_off_updated"));
            admin_redirect('attendances/day_off');
        } else {
			$day_off = $this->attendances_model->getApplyOtByID($id);
            $items = $this->attendances_model->getApplyOtItems($id);
            krsort($items);
            $c = rand(100000, 9999999);
            foreach ($items as $item) {
				$item->id = $item->employee_id;
				$item->day_off = $this->bpas->hrsd($item->from_time);
                $pr[$c] = array('id' => $c, 'item_id' => $item->id, 'label' => $item->lastname .' '.$item->firstname. " (" . $item->empcode . ")",'row' => $item);
                $c++;
            }
            $this->data['day_off'] = $day_off;
            $this->data['day_off_items'] = json_encode($pr);
			$this->data['billers'] = ($this->Owner || $this->Admin||!$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->session->set_userdata('remove_dfls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')),array('link' => site_url('attendances/day_off'), 'page' => lang('approve_ot')), array('link' => '#', 'page' => lang('edit_apply_ot')));
            $meta = array('page_title' => lang('edit_apply_ot'), 'bc' => $bc);
            $this->page_construct('attendances/edit_apply_ot', $meta, $this->data);

        }
    }

	
	public function delete_ot($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->attendances_model->deleteOT($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('ot_deleted')]);
            }
            $this->session->set_flashdata('message', lang('ot_deleted'));
            admin_redirect('welcome');
        }
    }
	public function approve_ot()
	{
		$this->bpas->checkPermissions('approve_ot');
		if(isset($_POST['approve'])){
			$post = $this->input->post();
			if($post['employee_id']){
				$data = array();
				$month = explode("/",$post['approve_month']);
				foreach($post['employee_id'] as $i => $employee_id){
					$data[] = array(
								'employee_id'=>$employee_id,
								'policy_ot_id'=>$post['policy_ot_id'][$i],
								'date'=>$post['date'][$i],
								'check_in'=>$post['check_in'][$i],
								'check_out'=>$post['check_out'][$i],
								'ot'=>$post['ot'][$i],
								'type'=>$post['type'][$i],
					
					);
				}
			}
			if($post['employee_id'] && $this->attendances_model->approveOT($data)){
				$this->session->set_flashdata('message', $this->lang->line("ot_approved"));
				admin_redirect($_SERVER['HTTP_REFERER']);
			}else{
				$this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
			}
		}
		
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('approve_ot')));
		$meta = array('page_title' => lang('approve_ot'), 'bc' => $bc);	
		$this->page_construct('attendances/approve_ot',$meta,$this->data);
	}
	
	public function getAttLogByMachine($id){
		$device = $this->attendances_model->getDeviceByID($id);
		$data = false;
		if($device){
			$this->load->library('Zk');
			$ip_address = $device->ip_address;
			$port = $device->port;
			$zk = new ZKLib($ip_address, $port);
			if($zk->connect()){
				$attendances = $zk->getAttendance();	
				if($attendances){
					foreach($attendances as $attendance){
						$employee_info = $this->attendances_model->getEmployeeByFingerID($attendance[1]);
						$employee_id = (int) $employee_info->id;
						$check_time_int = strtotime($attendance[3]);
						if($employee_info && !$this->attendances_model->getCheckInOutByEmployeeCheckTimeInt($employee_id,$check_time_int)){
							$data[] = array(
										'employee_id'=>$employee_id,
										'check_time'=>$attendance[3],
										'check_time_int'=>$check_time_int,
										'device_id'=>$device->id
									);
						}
					}
					if($data){
						$this->attendances_model->addCheckInOut($data);
					}
				}
			}
		}
	}
	
	public function synchronizeTimeByMachine($id){
		$device = $this->attendances_model->getDeviceByID($id);
		if($device){
			$this->load->library('Zk');
			$ip_address = $device->ip_address;
			$port = $device->port;
			$zk = new ZKLib($ip_address, $port);
			if($zk->connect()){
				$current_date = date('Y-m-d H:i:s');
				if($zk->setTime($current_date)){
					return true;
				}
			}
		}
		return false;
	}

	public function clearAttLogByMachine($id){
		$device = $this->attendances_model->getDeviceByID($id);
		$data = false;
		$clear = false;
		if($device){
			$this->load->library('Zk');
			$ip_address = $device->ip_address;
			$port = $device->port;
			$zk = new ZKLib($ip_address, $port);
			if($zk->connect()){
				$clear = array(
								'ip_address'=>$ip_address,
								'port'=>$port
							);
				$attendances = $zk->getAttendance();	
				if($attendances){
					foreach($attendances as $attendance){
						$employee_info = $this->attendances_model->getEmployeeByFingerID($attendance[1]);
						$employee_id = (int) $employee_info->id;
						$check_time_int = strtotime($attendance[3]);
						if($employee_info && !$this->attendances_model->getCheckInOutByEmployeeCheckTimeInt($employee_id,$check_time_int)){
							$data[] = array(
										'employee_id'=>$employee_id,
										'check_time'=>$attendance[3],
										'check_time_int'=>$check_time_int,
										'device_id'=>$device->id
									);
						}
					}
				}
				if($clear){
					$this->attendances_model->clearAttLog($clear,$data);
				}
			}
		}
	}
	
	public function getAttLog($device_id = false)
	{
		$devices = $this->attendances_model->getDevices('active');
		if($devices){
			$this->load->library('Zk');
			foreach($devices as $device){
				$data = false;
				$clear = false;
				$count_attendance = 0;
				$ip_address = $device->ip_address;
				$port = $device->port;
				$zk = new ZKLib($ip_address, $port);

				if($zk->connect()){
					$attendances = $zk->getAttendance();
					if($attendances){
						foreach($attendances as $attendance){
							$employee_info = $this->attendances_model->getEmployeeByFingerID($attendance[1]);
							$employee_id = (int) $employee_info->id;
							$check_time_int = strtotime($attendance[3]);
							if($employee_info && !$this->attendances_model->getCheckInOutByEmployeeCheckTimeInt($employee_id,$check_time_int)){
								$data[] = array(
											'employee_id'=>$employee_id,
											'check_time'=>$attendance[3],
											'check_time_int'=>$check_time_int,
											'device_id'=>$device->id
										);
							}
						}
						
						if($data){
							$count_attendance = count($attendances);
							if($device->clear==1 && $count_attendance > $device->max_att_log){
								$clear = array(
												'ip_address'=>$ip_address,
												'port'=>$port,
												'count_attendance'=>$count_attendance,
											);
							}
							
							$this->attendances_model->addCheckInOut($data,$clear);
						}
					}
				}
			}
			
		}
		
	}

	function montly_attendance_report()
    {
		$this->bpas->checkPermissions('montly_attendance_report');
		$post = $this->input->post();
        $this->data['billers'] = $this->site->getAllBiller();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('montly_attendance_report')));
        $meta = array('page_title' => lang('montly_attendance_report'), 'bc' => $bc);
        $this->page_construct('attendances/montly_attendance_report', $meta, $this->data);
    }
	
	function getMonthlyAttedance($pdf = NULL, $xls = NULL)
    {
		$this->bpas->checkPermissions('montly_attendance_report');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
        $group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $month = $this->input->get('approve_month');
        if ($pdf || $xls) {
			$this->db->select('
								hr_employees.empcode,
								CONCAT('.$this->db->dbprefix("hr_employees").'.lastname," ",'.$this->db->dbprefix("hr_employees").'.firstname) as full_name,
								hr_departments.name as department,
								hr_groups.name as group,
								DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y") as month,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.working_day,0)) as working_day,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.present,0)) as present,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.permission,0)) as permission,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.absent,0)) as absent,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.late,0)) as late,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.leave_early,0)) as early,
							')
							->from('hr_employees')
							->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','left')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->group_by('hr_employees.id')
							->order_by('hr_employees.firstname');
            if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
           
            if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->db->where('hr_employees.id', $employee);
            }

			if ($month) {
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , $month);
			}else{
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , date('m/Y'));
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
                $this->excel->getActiveSheet()->setTitle(lang('montly_attendance_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('department'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('group'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('month'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('working_day'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('present'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('permission'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('absent'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('coming_late'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('leave_early'));
              

                $row = 2;
                $total_working_day = 0;
                $total_present = 0;
                $total_permission = 0;
				$total_absent = 0;
				$total_leave_early =0;
				$total_late =0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->full_name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->department);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->group);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->month);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, ($data_row->working_day));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, ($data_row->present));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->permission));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->absent));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->secTotime($data_row->late));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->secTotime($data_row->early));
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
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'montly_attendance_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
                create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			$this->datatables->select('	
										hr_employees.empcode,
										CONCAT('.$this->db->dbprefix("hr_employees").'.lastname," ",'.$this->db->dbprefix("hr_employees").'.firstname) as full_name,
										hr_departments.name as department,
										hr_groups.name as group,
										DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y") as month,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.working_day,0)) as working_day,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.present,0)) as present,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.permission,0)) as permission,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.absent,0)) as absent,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.late,0)) as late,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.leave_early,0)) as early,
										hr_employees.id,
										DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%Y-%m") as raw_month
									')
							->from('hr_employees')
							->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','left')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->group_by('hr_employees.id');
            if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
           
            if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->db->where('hr_employees.id', $employee);
            }

			if ($month) {
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , $month);
			}else{
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , date('m/Y'));
			}

            echo $this->datatables->generate();

        }
    }
	
	public function monthly_time_card($id = null)
    {
		$data = explode('date',$id);
		$employee_id = $data[0];
		$month = $data[1];
		$start_date = $month.'-01';
		$end_date = date('Y-m-t',strtotime($start_date));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$employee_info = $this->attendances_model->getEmployeeInfoByID($employee_id);
		$this->data['employee'] = $employee_info;
		$this->data['department'] = $this->hr_model->getDepartmentById($employee_info->department_id);
		$this->data['group'] = $this->hr_model->getGroupById($employee_info->group_id);
		$this->data['position'] = $this->hr_model->getPositionById($employee_info->position_id);
		$this->data['id'] = $id;
		$this->data['month'] = $month;
		$this->data['attendances'] = $this->attendances_model->getEmployeeAttedance($employee_id,$start_date,$end_date);
		$this->data['biller'] = $this->site->getCompanyByID($employee_info->biller_id);
        $this->load->view($this->theme . 'attendances/monthly_time_card', $this->data);
    }
	
	public function check_in_out_report()
	{
		$this->bpas->checkPermissions('check_in_out_report');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('check_in_out_report')));
		$meta = array('page_title' => lang('check_in_out_report'), 'bc' => $bc);	
		$this->page_construct('attendances/check_in_out_report',$meta,$this->data);
	}
	
	public function employee_leave_report()
	{
		$this->bpas->checkPermissions('employee_leave_report');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$this->data['employee_leave_categories'] = $this->attendances_model->getEmployeeLeaveCategory();
		$this->data['leave_categories'] = $this->hr_model->getLeaveCategories();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('employee_leave_report')));
		$meta = array('page_title' => lang('employee_leave_report'), 'bc' => $bc);	
		$this->page_construct('attendances/employee_leave_report',$meta,$this->data);
	}
	function getEmployeeLeaveReport($xls = NULL)
    {
		$this->bpas->checkPermissions('employee_leave_report');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
        $group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $start_date = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : NULL;
		$end_date = $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date')) : NULL;
        if ($xls) {
			$this->db->select('	
							hr_employees.empcode,
							CONCAT('.$this->db->dbprefix('hr_employees').'.lastname, " ", '.$this->db->dbprefix('hr_employees').'.firstname ) AS employee_name,
							hr_departments.name AS department,
							hr_groups.name AS group,
							hr_positions.name AS position,	
							hr_leave_categories.name as leave_category,
							hr_leave_types.name as leave_type,
							att_take_leave_details.start_date,
							att_take_leave_details.end_date,
							att_take_leave_details.timeshift,
							att_take_leave_details.reason,
							att_take_leave_details.take_leave_id
						')
				->from('att_take_leave_details')
				->join('att_take_leave_employees','att_take_leave_employees.take_leave_id = att_take_leave_details.take_leave_id','inner')
				->join('hr_employees','att_take_leave_details.employee_id = hr_employees.id','inner')
				->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
				->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
				->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
				->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
				->join('hr_leave_types','hr_leave_types.id = att_take_leave_details.leave_type','left')
				->join('hr_leave_categories','hr_leave_categories.id = hr_leave_types.category_id','left')
				->group_by("att_take_leave_details.id");
		
            if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
            if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
			if ($position) {
                $this->db->where('hr_employees_working_info.position_id', $position);
            }
            if ($employee) {
                $this->db->where('att_take_leave_details.employee_id', $employee);
            }
			if ($start_date) {
                $this->db->where('att_take_leave_employees.date >=', $start_date);
            }
			if ($end_date) {
                $this->db->where('att_take_leave_employees.date <=', $end_date);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('hr_employees_working_info.biller_id =', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('employee_leave_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('leave_category'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('leave_type'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('start_date'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('end_date'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('timeshift'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('reason'));
                $row = 2;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->employee_name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->department);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->group);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->leave_category);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->leave_type);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->hrsd($data_row->start_date));
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->hrsd($data_row->end_date));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, lang($data_row->timeshift));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->reason);
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);

                $filename = 'employee_leave_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			$this->datatables->select('	
										hr_employees.empcode,
										CONCAT('.$this->db->dbprefix('hr_employees').'.lastname, " ", '.$this->db->dbprefix('hr_employees').'.firstname ) AS employee_name,
										hr_departments.name AS department,
										hr_groups.name AS group,
										hr_positions.name AS position,	
										hr_leave_categories.name as leave_category,
										hr_leave_types.name as leave_type,
										att_take_leave_details.start_date,
										att_take_leave_details.end_date,
										att_take_leave_details.timeshift,
										att_take_leave_details.reason,
										att_take_leave_details.take_leave_id
									')
							->from('att_take_leave_details')
							->join('att_take_leave_employees','att_take_leave_employees.take_leave_id = att_take_leave_details.take_leave_id','inner')
							->join('hr_employees','att_take_leave_details.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->join('hr_leave_types','hr_leave_types.id = att_take_leave_details.leave_type','left')
							->join('hr_leave_categories','hr_leave_categories.id = hr_leave_types.category_id','left')
							->group_by("att_take_leave_details.id");
		
            if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
            if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
			if ($position) {
                $this->datatables->where('hr_employees_working_info.position_id', $position);
            }
            if ($employee) {
                $this->datatables->where('att_take_leave_details.employee_id', $employee);
            }
			if ($start_date) {
                $this->datatables->where('att_take_leave_employees.date >=', $start_date);
            }
			if ($end_date) {
                $this->datatables->where('att_take_leave_employees.date <=', $end_date);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('hr_employees_working_info.biller_id =', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();

        }
    }
	public function employee_leave_by_year_report()
	{
		$this->bpas->checkPermissions('employee_leave_report');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('employee_leave_by_year_report')));
		$meta = array('page_title' => lang('employee_leave_by_year_report'), 'bc' => $bc);	
		$this->page_construct('attendances/employee_leave_by_year_report',$meta,$this->data);
	}
	
	function getEmployeeLeaveByYear($pdf = NULL, $xls = NULL)
    {
		$this->bpas->checkPermissions('employee_leave_report');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
        $group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        if($this->input->get('year')){
			$year = $this->input->get('year');
		}else{
			$year = date('Y');
		}
        if ($pdf || $xls) {
			$this->db->select('	
								hr_employees.empcode,
								CONCAT('.$this->db->dbprefix('hr_employees').'.lastname, " ", '.$this->db->dbprefix('hr_employees').'.firstname ) AS employee_name,
								hr_positions.name AS position,
								hr_departments.name AS department,
								hr_groups.name AS group,
								hr_employees_working_info.annual_leave,
								IFNULL( annual_leave.total_leave, 0 ) AS used_annual_leave,
								hr_employees_working_info.special_leave,
								IFNULL( special_leave.total_leave, 0 ) AS used_special_leave,
								hr_employees_working_info.sick_leave,
								IFNULL( sick_leave.total_leave, 0 ) AS used_sick_leave,
								hr_employees_working_info.other_leave,
								IFNULL( other_leave.total_leave, 0 ) AS used_other_leave 
							')
					->from('hr_employees')
					->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
					->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
					->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
					->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
					->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
								SUM(
								IF
									(
										timeshift = "full",
										( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
									(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
							FROM
								'.$this->db->dbprefix('att_take_leave_details').'
								INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
								INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
								INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
							WHERE
								'.$this->db->dbprefix('att_take_leaves').'.status = 1 
								AND '.$this->db->dbprefix('hr_leave_categories').'.code = "annual_leave"
								AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
							GROUP BY
								'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as annual_leave','hr_employees.id = annual_leave.employee_id','LEFT')
					->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
								SUM(
								IF
									(
										timeshift = "full",
										( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
									(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
							FROM
								'.$this->db->dbprefix('att_take_leave_details').'
								INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
								INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
								INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
							WHERE
								'.$this->db->dbprefix('att_take_leaves').'.status = 1 
								AND '.$this->db->dbprefix('hr_leave_categories').'.code = "special_leave"
								AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
							GROUP BY
								'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as special_leave','hr_employees.id = special_leave.employee_id','LEFT')
					->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
								SUM(
								IF
									(
										timeshift = "full",
										( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
									(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
							FROM
								'.$this->db->dbprefix('att_take_leave_details').'
								INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
								INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
								INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
							WHERE
								'.$this->db->dbprefix('att_take_leaves').'.status = 1 
								AND '.$this->db->dbprefix('hr_leave_categories').'.code = "sick_leave"
								AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
							GROUP BY
								'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as sick_leave','hr_employees.id = sick_leave.employee_id','LEFT')					
					->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
								SUM(
								IF
									(
										timeshift = "full",
										( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
									(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
							FROM
								'.$this->db->dbprefix('att_take_leave_details').'
								INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
								INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
								INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
							WHERE
								'.$this->db->dbprefix('att_take_leaves').'.status = 1 
								AND '.$this->db->dbprefix('hr_leave_categories').'.code = "other_leave"
								AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
							GROUP BY
								'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as other_leave','hr_employees.id = other_leave.employee_id','LEFT')			
					->group_by('hr_employees.id');
			$this->db->where('('.$this->db->dbprefix("hr_employees_working_info").'.status != "inactive" OR year('.$this->db->dbprefix("hr_employees_working_info").'.resigned_date) >= "'.$year.'")');				
            if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
            if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->db->where('hr_employees.id', $employee);
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
                $this->excel->getActiveSheet()->setTitle(lang('daily_attendance_report'));
				
				$this->excel->getActiveSheet()->mergeCells('A1:A2');
				$this->excel->getActiveSheet()->mergeCells('B1:B2');
				$this->excel->getActiveSheet()->mergeCells('C1:C2');
				$this->excel->getActiveSheet()->mergeCells('D1:D2');
				$this->excel->getActiveSheet()->mergeCells('E1:E2');
				$this->excel->getActiveSheet()->mergeCells('F1:G1');
				$this->excel->getActiveSheet()->mergeCells('H1:I1');
				$this->excel->getActiveSheet()->mergeCells('J1:K1');
				$this->excel->getActiveSheet()->mergeCells('L1:M1');
				
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('annual_leave'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('special_leave'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('sick_leave'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('other_leave'));
				
				$this->excel->getActiveSheet()->SetCellValue('F2', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('G2', lang('used'));
				$this->excel->getActiveSheet()->SetCellValue('H2', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('I2', lang('used'));
				$this->excel->getActiveSheet()->SetCellValue('J2', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('K2', lang('used'));
				$this->excel->getActiveSheet()->SetCellValue('L2', lang('total'));
				$this->excel->getActiveSheet()->SetCellValue('M2', lang('used'));

				$this->excel->getActiveSheet()->getStyle('F1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('H1:I1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('J1:K1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$this->excel->getActiveSheet()->getStyle('L1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
                $row = 3;
                $total_working_day = 0;
                $total_present = 0;
                $total_permission = 0;
				$total_absent = 0;
				$total_leave_early =0;
				$total_late =0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->employee_name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->position);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->department);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->group);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->formatQuantity($data_row->annual_leave));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->formatQuantity($data_row->used_annual_leave));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->bpas->formatQuantity($data_row->special_leave));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->bpas->formatQuantity($data_row->used_special_leave));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->formatQuantity($data_row->sick_leave));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->formatQuantity($data_row->used_sick_leave));
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->formatQuantity($data_row->other_leave));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->formatQuantity($data_row->used_other_leave));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
                $filename = 'employee_leave_by_year_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			$this->datatables->select('	
										hr_employees.empcode,
										CONCAT('.$this->db->dbprefix('hr_employees').'.lastname, " ", '.$this->db->dbprefix('hr_employees').'.firstname ) AS employee_name,
										hr_positions.name AS position,
										hr_departments.name AS department,
										hr_groups.name AS group,
										hr_employees_working_info.annual_leave,
										IFNULL( annual_leave.total_leave, 0 ) AS used_annual_leave,
										hr_employees_working_info.special_leave,
										IFNULL( special_leave.total_leave, 0 ) AS used_special_leave,
										hr_employees_working_info.sick_leave,
										IFNULL( sick_leave.total_leave, 0 ) AS used_sick_leave,
										hr_employees_working_info.other_leave,
										IFNULL( other_leave.total_leave, 0 ) AS used_other_leave 
									')
							->from('hr_employees')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
										SUM(
										IF
											(
												timeshift = "full",
												( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
											(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
									FROM
										'.$this->db->dbprefix('att_take_leave_details').'
										INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
										INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
										INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
									WHERE
										'.$this->db->dbprefix('att_take_leaves').'.status = 1 
										AND '.$this->db->dbprefix('hr_leave_categories').'.code = "annual_leave"
										AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
									GROUP BY
										'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as annual_leave','hr_employees.id = annual_leave.employee_id','LEFT')
							->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
										SUM(
										IF
											(
												timeshift = "full",
												( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
											(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
									FROM
										'.$this->db->dbprefix('att_take_leave_details').'
										INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
										INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
										INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
									WHERE
										'.$this->db->dbprefix('att_take_leaves').'.status = 1 
										AND '.$this->db->dbprefix('hr_leave_categories').'.code = "special_leave"
										AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
									GROUP BY
										'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as special_leave','hr_employees.id = special_leave.employee_id','LEFT')
							->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
										SUM(
										IF
											(
												timeshift = "full",
												( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
											(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
									FROM
										'.$this->db->dbprefix('att_take_leave_details').'
										INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
										INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
										INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
									WHERE
										'.$this->db->dbprefix('att_take_leaves').'.status = 1 
										AND '.$this->db->dbprefix('hr_leave_categories').'.code = "sick_leave"
										AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
									GROUP BY
										'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as sick_leave','hr_employees.id = sick_leave.employee_id','LEFT')					
							->join('(SELECT '.$this->db->dbprefix('att_take_leave_details').'.employee_id,
										SUM(
										IF
											(
												timeshift = "full",
												( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ),
											(( DATEDIFF( '.$this->db->dbprefix('att_take_leave_details').'.end_date, '.$this->db->dbprefix('att_take_leave_details').'.start_date ) + 1 ) / 2 ))) AS total_leave 
									FROM
										'.$this->db->dbprefix('att_take_leave_details').'
										INNER JOIN '.$this->db->dbprefix('att_take_leaves').' ON '.$this->db->dbprefix('att_take_leaves').'.id = '.$this->db->dbprefix('att_take_leave_details').'.take_leave_id
										INNER JOIN '.$this->db->dbprefix('hr_leave_types').' ON '.$this->db->dbprefix('hr_leave_types').'.id = '.$this->db->dbprefix('att_take_leave_details').'.leave_type
										INNER JOIN '.$this->db->dbprefix('hr_leave_categories').' ON '.$this->db->dbprefix('hr_leave_categories').'.id = '.$this->db->dbprefix('hr_leave_types').'.category_id 
									WHERE
										'.$this->db->dbprefix('att_take_leaves').'.status = 1 
										AND '.$this->db->dbprefix('hr_leave_categories').'.code = "other_leave"
										AND YEAR('.$this->db->dbprefix('att_take_leave_details').'.start_date) = "'.$year.'"
									GROUP BY
										'.$this->db->dbprefix('att_take_leave_details').'.employee_id) as other_leave','hr_employees.id = other_leave.employee_id','LEFT')			
							->group_by('hr_employees.id');
			$this->datatables->where('('.$this->db->dbprefix("hr_employees_working_info").'.status != "inactive" OR year('.$this->db->dbprefix("hr_employees_working_info").'.resigned_date) >= "'.$year.'")');		
            if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
            if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->datatables->where('hr_employees.id', $employee);
            }
            echo $this->datatables->generate();

        }
    }

	
	public function view_employee_leave($id = null)
    {
		$this->bpas->checkPermissions('employee_leave_report');
		$data = explode('date',$id);
		$employee_id = $data[0];
		$start_date = $data[1];
		$end_date = $data[2];
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$employee_info = $this->attendances_model->getEmployeeInfoByID($employee_id);
		$this->data['employee'] = $employee_info;
		$this->data['id'] = $id;
		$this->data['start_date'] = $start_date;
		$this->data['end_date'] = $end_date;
		$this->data['department'] = $this->hr_model->getDepartmentById($employee_info->department_id);
		$this->data['group'] = $this->hr_model->getGroupById($employee_info->group_id);
		$this->data['position'] = $this->hr_model->getPositionById($employee_info->position_id);
		$this->data['leaves'] = $this->attendances_model->getEmployeeTakeLeave($employee_id,$start_date,$end_date);
		$this->data['biller'] = $this->site->getCompanyByID($employee_info->biller_id);
        $this->load->view($this->theme . 'attendances/view_employee_leave', $this->data);
    }
	
	
	public function list_devices()
    {
		$this->bpas->checkPermissions('list_devices');
		$this->data['devices'] = $this->attendances_model->getDevices();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('devices')));
        $meta = array('page_title' => lang('devices'), 'bc' => $bc);
        $this->page_construct('attendances/list_devices', $meta, $this->data);
    }
	public function getDevices()
	{
		$this->bpas->checkPermissions('list_devices');
		$this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_device") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/delete_device/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_device') . "</a>";
		$action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
						<ul class="dropdown-menu pull-right" role="menu">
							<li><a href="'.admin_url('attendances/edit_device/$1').'" data-toggle="modal" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_device').'</a></li>
							<li>'.$delete_link.'</li>
						</ul>
					</div>';
        $this->datatables
            ->select("id, name, ip_address, port,description")
            ->from("att_devices")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	
	public function add_device()
	{
		$this->bpas->checkPermissions('list_devices');
		$this->load->helper('security');
        $this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('ip_address', lang("ip_address"), 'required|is_unique[att_devices.ip_address]');
		$this->form_validation->set_rules('port', lang("port"), 'required'); 
        if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => $this->input->post('port'),
				'description' => $this->input->post('description'),
				'clear' => $this->input->post('clear'),
				'max_att_log' => $this->input->post('maximum_att_log')
            ); 
        } elseif ($this->input->post('add_device')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        } 
        if ($this->form_validation->run() == true && $this->attendances_model->addDevice($data)) {
            $this->session->set_flashdata('message', lang("device_added")."  ".$data['name']);
            admin_redirect("attendances/list_devices");
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'attendances/add_device', $this->data);

        }
	}
	public function edit_device($id = null)
	{
		$this->bpas->checkPermissions('list_devices');
		$device = $this->attendances_model->getDeviceByID($id);
		$this->load->helper('security');
		$this->form_validation->set_rules('name', lang("name"), 'required');
		$this->form_validation->set_rules('ip_address', lang("ip_address"), 'required');
		$this->form_validation->set_rules('port', lang("port"), 'required');
		if($device->ip_address != $this->input->post('ip_address')){
			$this->form_validation->set_rules('ip_address', lang("ip_address"), 'is_unique[att_devices.ip_address]');
		}
		if ($this->form_validation->run() == true) {
            $data = array(
                'name' => $this->input->post('name'),
                'ip_address' => $this->input->post('ip_address'),
                'port' => $this->input->post('port'),
				'description' => $this->input->post('description'),
				'inactive' => $this->input->post('inactive'),
				'clear' => $this->input->post('clear'),
				'max_att_log' => $this->input->post('maximum_att_log')
            );

        } elseif ($this->input->post('add_devices')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->attendances_model->updateDevice($id,$data)) {
            $this->session->set_flashdata('message', lang("device_edited")."  ".$data['name']);
            admin_redirect("attendances/list_devices");
        } else {
			$this->data['device'] = $device;
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'attendances/edit_device', $this->data);

        }
	}
	public function delete_device($id = null)
    {	
		$this->bpas->checkPermissions('list_devices');
        if (isset($id) || $id != null){
			$device = $this->attendances_model->getDeviceByID($id);
        	if($this->attendances_model->deleteDevice($id)){
        		$this->session->set_flashdata('message', lang("device_deleted")."  ".$device->name);
            	admin_redirect("attendances/list_devices");
        	}
        }
    }
	
	function device_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
				if($this->input->post('form_action') == 'get_att_log'){
					foreach ($_POST['val'] as $id) {
                        $this->getAttLogByMachine($id);
                    }
					$this->session->set_flashdata('message', lang("att_log_get"));
					admin_redirect("attendances/list_devices");
				}else if ($this->input->post('form_action') == 'connect_device') {
					foreach ($_POST['val'] as $id) {
						$connect_devices[$id] = 1;
                    }
					$this->data['connect'] = $connect_devices;
                    $this->data['devices'] = $this->attendances_model->getDevices();
					$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
					$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('devices')));
					$meta = array('page_title' => lang('devices'), 'bc' => $bc);
					$this->page_construct('attendances/list_devices', $meta, $this->data);
				}else if ($this->input->post('form_action') == 'clear_att_log') {
					foreach ($_POST['val'] as $id) {
                        $this->clearAttLogByMachine($id);
                    }
                    $this->session->set_flashdata('message', lang("att_log_cleared"));
					admin_redirect("attendances/list_devices");
                }else if ($this->input->post('form_action') == 'synchronize_time') {
					foreach ($_POST['val'] as $id) {
                        $true = $this->synchronizeTimeByMachine($id);
                    }
                    $this->session->set_flashdata('message', lang("time_synchronize"));
					admin_redirect("attendances/list_devices");
                }else if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->attendances_model->deleteDevice($id);
                    }
                    $this->session->set_flashdata('message', lang("devices_deleted"));
                    admin_redirect("attendances/list_devices");
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('device');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('device_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('ip_address'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('port'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $device = $this->attendances_model->getDeviceByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $device->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $device->ip_address);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $device->port);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $device->description);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'device_list_' . date('Y_m_d_H_i_s');
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
	
	
	public function attendance_department_report()
	{
		$this->bpas->checkPermissions('attendance_department_report');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$this->data['departments'] = $this->attendances_model->getDepartment();
		$this->data['groups'] = $this->attendances_model->getGroup();
		$this->data['attendances'] = $this->attendances_model->getDailyAttendanceDepartmentGroup();
		
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('attendance_department_report')));
		$meta = array('page_title' => lang('attendance_department_report'), 'bc' => $bc);	
		$this->page_construct('attendances/attendance_department_report',$meta,$this->data);
	}
	
	public function daily_attendance_report()
	{
		$this->bpas->checkPermissions('daily_attendance_report');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('daily_attendance_report')));
		$meta = array('page_title' => lang('daily_attendance_report'), 'bc' => $bc);	
		$this->page_construct('attendances/daily_attendance_report',$meta,$this->data);
	}
	
	function getDailyAttedance($pdf = NULL, $xls = NULL)
    {
		$this->bpas->checkPermissions('daily_attendance_report');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
        $group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        if($this->input->get('start_date')){
			$date = $this->bpas->fsd($this->input->get('start_date'));
		}else{
			$date = date('Y-m-d');
		}
        if ($pdf || $xls) {
			$this->db->select('
								hr_employees.empcode,
								CONCAT('.$this->db->dbprefix("hr_employees").'.lastname," ",'.$this->db->dbprefix("hr_employees").'.firstname) as full_name,
								hr_departments.name as department,
								hr_groups.name as group,
								att_attedances.date,
								att_attedances.working_day,
								att_attedances.present,
								att_attedances.permission,
								att_attedances.absent,
								att_attedances.late,
								att_attedances.leave_early,
								hr_employees.id
							')
							->from('hr_employees')
							->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','left')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->group_by('hr_employees.id')
							->order_by('hr_employees.empcode');
            if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
           
            if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->db->where('hr_employees.id', $employee);
            }

			if ($date) {
				$this->db->where('att_attedances.date' , $date);
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
                $this->excel->getActiveSheet()->setTitle(lang('daily_attendance_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('department'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('group'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('working_day'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('present'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('permission'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('absent'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('coming_late'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('leave_early'));
              

                $row = 2;
                $total_working_day = 0;
                $total_present = 0;
                $total_permission = 0;
				$total_absent = 0;
				$total_leave_early =0;
				$total_late =0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->full_name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->department);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->group);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->hrsd($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, ($data_row->working_day));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, ($data_row->present));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->permission));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->absent));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->secTotime($data_row->late));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->secTotime($data_row->early));
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
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'daily_attendance_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
                create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            admin_redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			$this->datatables->select('	
										hr_employees.empcode,
										CONCAT('.$this->db->dbprefix("hr_employees").'.lastname," ",'.$this->db->dbprefix("hr_employees").'.firstname) as full_name,
										hr_departments.name as department,
										hr_groups.name as group,
										att_attedances.date,
										att_attedances.working_day,
										att_attedances.present,
										att_attedances.permission,
										att_attedances.absent,
										att_attedances.late,
										att_attedances.leave_early,
										hr_employees.id
									')
							->from('hr_employees')
							->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','left')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->group_by('hr_employees.id');
							
			$this->datatables->where('hr_employees_working_info.status','active');

            if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
           
            if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->datatables->where('hr_employees.id', $employee);
            }

			if ($date) {
				$this->datatables->where('att_attedances.date' , $date);
			}

            echo $this->datatables->generate();

        }
    }
	
	
	public function daily_time_card($id = null)
    {
		$data = explode('date',$id);
		$employee_id = $data[0];
		$date = $data[1];
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$employee_info = $this->attendances_model->getEmployeeInfoByID($employee_id);
		$this->data['employee'] = $employee_info;
		$this->data['department'] = $this->hr_model->getDepartmentById($employee_info->department_id);
		$this->data['group'] = $this->hr_model->getGroupById($employee_info->group_id);
		$this->data['position'] = $this->hr_model->getPositionById($employee_info->position_id);
		$this->data['id'] = $id;
		$this->data['date'] = $date;
		$this->data['attendances'] = $this->attendances_model->getEmployeeAttedance($employee_id,$date,$date);
		$this->data['biller'] = $this->site->getCompanyByID($employee_info->biller_id);
        $this->load->view($this->theme . 'attendances/daily_time_card', $this->data);
    }
	
	
	public function department_time_card($date,$department_id = false, $group_id = false)
    {
		if($department_id > 0){
			$this->data['department'] = $this->hr_model->getDepartmentById($department_id); 
		}
		if($group_id > 0){
			$this->data['group'] = $this->hr_model->getGroupById($group_id);
		}
		$biller_id = $this->data['department']->biller_id;
		$this->data['date'] = $date;
		$this->data['attendances'] = $this->attendances_model->getEmployeeAttedance(false,$date,$date, $department_id, $group_id);
		$this->data['employees'] = $this->attendances_model->getEmployeeWorkingInfo(false,$biller_id,false,$department_id,$group_id,false,$date);
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['biller'] = $this->site->getCompanyByID($biller_id);
        $this->load->view($this->theme . 'attendances/department_time_card', $this->data);
    }
	
	
	function import_check_in_out(){
        $this->bpas->checkPermissions('add_check_in_out', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('excel_file', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["excel_file"]))  {
				$this->load->library('excel');
				$path = $_FILES["excel_file"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$employee_code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$check_time = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$employee_info = $this->attendances_model->getEmployeeByCode(trim($employee_code));
						if (!$employee_info) {
							$this->session->set_flashdata('error', lang("check_employee_code") . " (" . $employee_code . "). " . lang("employee_not_exist") . " (" . lang("line_no") . " " . $row . ")");
							admin_redirect("attendances/check_in_outs");
						}
						
						if($check_time == ''){
							$this->session->set_flashdata('error', lang("check_time"). lang("line_no") . " " . $row . ")");
							admin_redirect("attendances/check_in_outs");
						}
						 
						$data[] = array(
							'employee_id'  => $employee_info->id,
							'check_time'  => $this->bpas->fld($check_time,true),
						);
					}
				}
            }


        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('attendances/check_in_outs');
        }
        if ($this->form_validation->run() == true && !empty($data) && $this->attendances_model->addCheckInOut($data)) {
			$this->session->set_flashdata('message', lang("check_in_out_imported"));
			admin_redirect('attendances/check_in_outs');
            
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'attendances/import_check_in_out', $this->data);
        }
    }
    public function day_off()
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('day_off')));
        $meta = array('page_title' => lang('day_off'), 'bc' => $bc);
        $this->page_construct('attendances/day_off', $meta, $this->data);
    }
	public function getDayOff()
    {
        $this->bpas->checkPermissions('day_off');
		$edit_link = anchor('admin/attendances/edit_day_off/$1', '<i class="fa fa-edit"></i> ' . lang('edit_day_off'), 'class="edit_day_off"');
		$delete_link = "<a href='#' class='delete_day_off po' title='<b>" . $this->lang->line("delete_day_off") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('attendances/delete_day_off/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_day_off') . "</a>";
		
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
			<ul class="dropdown-menu pull-right" role="menu">
				<li>' . $edit_link . '</li>
				<li>' . $delete_link . '</li>
			</ul>
		</div></div>';
			
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('att_day_off')}.id as id, date, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, note, attachment")
            ->from('att_day_off')
            ->join('users', 'users.id=att_day_off.created_by', 'left')
            ->group_by("att_day_off.id");

			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('att_day_off.biller_id =', $this->session->userdata('biller_id'));
			}
			if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
				$this->datatables->where('att_day_off.created_by', $this->session->userdata('user_id'));
			}
		$this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();

    }
	
	
	public function add_day_off()
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
				$day_off = $this->bpas->fsd($_POST['day_off'][$r]);
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'employee_id' => $employee_id,
						'day_off' =>$day_off,
						'description' => $description,
					);
				}
            }
			
            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }
			
            $data = array(
                'date' => $date,
				'biller_id' => $biller_id,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
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
        if ($this->form_validation->run() == true && $this->attendances_model->addDayOff($data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("day_off_added"));
            admin_redirect('attendances/day_off');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
			$this->data['billers'] = $this->site->getAllCompanies('biller');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => site_url('attendances/day_off'), 'page' => lang('day_off')), array('link' => '#', 'page' => lang('add_day_off')));
            $meta = array('page_title' => lang('add_day_off'), 'bc' => $bc);
            $this->page_construct('attendances/add_day_off', $meta, $this->data);
        }
    }
	
	public function edit_day_off($id)
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
				$day_off = $this->bpas->fsd($_POST['day_off'][$r]);
				$description = $_POST['description'][$r];
				if($employee_id){
					$items[] = array(
						'day_off_id' => $id,
						'employee_id' => $employee_id,
						'day_off' =>$day_off,
						'description' => $description,
					);
				}
            }
 

            if (empty($items)) {
                $this->form_validation->set_rules('employee', lang("employee"), 'required');
            }

            $data = array(
                'date' => $date,
				'biller_id' => $biller_id,
                'note' => $note,
				'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->userdata('user_id'),
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

        if ($this->form_validation->run() == true && $this->attendances_model->updateDayOff($id, $data, $items)) {
            $this->session->set_userdata('remove_dfls', 1);
            $this->session->set_flashdata('message', lang("day_off_updated"));
            admin_redirect('attendances/day_off');
        } else {
			$day_off = $this->attendances_model->getDayOffByID($id);
            $items = $this->attendances_model->getDayOffItems($id);
            krsort($items);
            $c = rand(100000, 9999999);
            foreach ($items as $item) {
				$item->id = $item->employee_id;
				$item->day_off = $this->bpas->hrsd($item->day_off);
                $pr[$c] = array('id' => $c, 'item_id' => $item->id, 'label' => $item->lastname .' '.$item->firstname. " (" . $item->empcode . ")",'row' => $item);
                $c++;
            }
            $this->data['day_off'] = $day_off;
            $this->data['day_off_items'] = json_encode($pr);
			$this->data['billers'] = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
			$this->session->set_userdata('remove_dfls', 1);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')),array('link' => site_url('attendances/day_off'), 'page' => lang('day_off')), array('link' => '#', 'page' => lang('edit_day_off')));
            $meta = array('page_title' => lang('edit_day_off'), 'bc' => $bc);
            $this->page_construct('attendances/edit_day_off', $meta, $this->data);

        }
    }

	
	public function delete_day_off($id = null){
        $this->bpas->checkPermissions(NULL, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->attendances_model->deleteDayOff($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('day_off_deleted')]);
            }
            $this->session->set_flashdata('message', lang('day_off_deleted'));
            admin_redirect('welcome');
        }
    }
	
	public function modal_view_day_off($id)
    {
        $this->bpas->checkPermissions('day_off', TRUE);
        $day_off = $this->attendances_model->getDayOffByID($id);
        $this->data['day_off'] = $day_off;
		$this->data['biller'] = $this->site->getCompanyByID($day_off->biller_id);
        $this->data['rows'] = $this->attendances_model->getDayOffItems($id);
		$this->data['created_by'] = $this->site->getUser($day_off->created_by);
        $this->load->view($this->theme.'attendances/modal_view_day_off', $this->data);
    }
	
	public function day_off_actions()
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
						$this->attendances_model->deleteDayOff($id);
                    }
					$this->session->set_flashdata('message', lang("day_off_deleted"));
					admin_redirect($_SERVER["HTTP_REFERER"]);
					
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('day_off');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $day_off = $this->attendances_model->getDayOffByID($id);
                        $created_by = $this->site->getUser($day_off->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($day_off->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->decode_html($day_off->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'day_off_' . date('Y_m_d_H_i_s');
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
	
	
	public function day_off_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getAllPositions($biller);
			$this->data['departments'] = $this->hr_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('day_off_report')));
        $meta = array('page_title' => lang('day_off_report'), 'bc' => $bc);
        $this->page_construct('attendances/day_off_report', $meta, $this->data);
	}
	
	public function getDayOffReport($xls = NULL){
        $this->bpas->checkPermissions('day_off_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $start_date = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : false;
        $end_date = $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date')) : false;
		if ($xls) {
			$this->db->select("	
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								DATE_FORMAT(".$this->db->dbprefix('att_day_off_items').".day_off, '%Y-%m-%d') as day_off,
								att_day_off_items.description,
								att_day_off_items.day_off_id as id")
						->from("att_day_off_items")
						->join("hr_employees","hr_employees.id = att_day_off_items.employee_id","left")
						->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_day_off_items.employee_id","left")
						->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
						->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
						->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
						->group_by("att_day_off_items.id");

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
                $this->db->where('att_day_off_items.employee_id', $employee);
            }
			if ($start_date) {
                $this->db->where('att_day_off_items.day_off >=', $start_date);
            }
			if ($end_date) {
                $this->db->where('att_day_off_items.day_off <=', $end_date);
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
                $this->excel->getActiveSheet()->setTitle(lang('day_off_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('day_off'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('description'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->hrsd($data_row->day_off));
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->remove_tag($data_row->description));
                    $row++;
                }
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
				$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$filename = 'day_off_report_' . date('Y_m_d_H_i_s');
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
									DATE_FORMAT(".$this->db->dbprefix('att_day_off_items').".day_off, '%Y-%m-%d') as day_off,
									att_day_off_items.description,
									att_day_off_items.day_off_id as id")
							->from("att_day_off_items")
							->join("hr_employees","hr_employees.id = att_day_off_items.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_day_off_items.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->group_by("att_day_off_items.id");

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
                $this->datatables->where('att_day_off_items.employee_id', $employee);
            }
			if ($start_date) {
                $this->datatables->where('att_day_off_items.day_off >=', $start_date);
            }
			if ($end_date) {
                $this->datatables->where('att_day_off_items.day_off <=', $end_date);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
	function montly_attendance_summary_report()
    {
		$this->bpas->checkPermissions('montly_attendance_report');
        $this->data['billers'] = $this->site->getAllBiller();
		$this->data['departments'] = $this->hr_model->getDepartments();
		$this->data['groups'] = $this->hr_model->getGroups();
		$this->data['positions'] = $this->hr_model->getAllPositions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('montly_attendance_summary_report')));
        $meta = array('page_title' => lang('montly_attendance_summary_report'), 'bc' => $bc);
        $this->page_construct('attendances/montly_attendance_summary_report', $meta, $this->data);
    }
	
	function getMonthlyAttedanceSummary($xls = NULL)
    {
		$this->bpas->checkPermissions('montly_attendance_summary_report');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
        $group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $month = $this->input->get('approve_month');
        //$start_date =$this->input->get('start_date') ? $this->input->get('start_date') : NULL;
        if ($xls) {
			$this->db->select('
								hr_employees.empcode,
								CONCAT('.$this->db->dbprefix("hr_employees").'.lastname," ",'.$this->db->dbprefix("hr_employees").'.firstname) as full_name,
								hr_departments.name as department,
								hr_groups.name as group,
								DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y") as month,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.working_day,0)) as working_day,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.present,0)) as present,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.permission,0)) as permission,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.absent,0)) as absent,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.late,0)) as late,
								SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.leave_early,0)) as early,
							')
							->from('hr_employees')
							->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->where('('.$this->db->dbprefix("hr_employees_working_info").'.status != "inactive" OR '.$this->db->dbprefix("hr_employees_working_info").'.resigned_date > "'.$start_date.'")')
							->group_by('hr_employees.id')
							->order_by('hr_employees.firstname');
            if ($biller) {
                $this->db->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->db->where('hr_employees_working_info.department_id', $department);
            }
            if ($group) {
                $this->db->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->db->where('hr_employees.id', $employee);
            }
			if ($month) {
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , $month);
			}else{
				$this->db->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , date('m/Y'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->db->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
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
                $this->excel->getActiveSheet()->setTitle(lang('montly_attendance_summary_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('department'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('group'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('month'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('working_day'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('present'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('permission'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('absent'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('coming_late'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('leave_early'));
              

                $row = 2;
                $total_working_day = 0;
                $total_present = 0;
                $total_permission = 0;
				$total_absent = 0;
				$total_leave_early =0;
				$total_late =0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->full_name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->department);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->group);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->month);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, ($data_row->working_day));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, ($data_row->present));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->permission));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->absent));
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->bpas->secTotime($data_row->late));
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->bpas->secTotime($data_row->early));
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
				$this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $filename = 'montly_attendance_summary_report';
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->load->helper('excel');
                create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);

        } else {
            $this->load->library('datatables');
			$this->datatables->select('	
										hr_employees.empcode,
										CONCAT('.$this->db->dbprefix("hr_employees").'.lastname," ",'.$this->db->dbprefix("hr_employees").'.firstname) as full_name,
										hr_departments.name as department,
										hr_groups.name as group,
										DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y") as month,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.working_day,0)) as working_day,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.present,0)) as present,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.permission,0)) as permission,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.absent,0)) as absent,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.late,0)) as late,
										SUM(IFNULL('.$this->db->dbprefix("att_attedances").'.leave_early,0)) as early,
										hr_employees.id,
										DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%Y-%m") as raw_month
									')
							->from('hr_employees')
							->join('att_attedances','att_attedances.employee_id = hr_employees.id','inner')
							->join('hr_employees_working_info','hr_employees_working_info.employee_id = hr_employees.id','inner')
							->join('hr_departments','hr_departments.id = hr_employees_working_info.department_id','left')
							->join('hr_groups','hr_groups.id = hr_employees_working_info.group_id','left')
							->join('hr_positions','hr_positions.id = hr_employees_working_info.position_id','left')
							->where('('.$this->db->dbprefix("hr_employees_working_info").'.status != "inactive" OR '.$this->db->dbprefix("hr_employees_working_info").'.resigned_date > "'.$start_date.'")')
							->group_by('hr_employees.id');
            if ($biller) {
                $this->datatables->where('hr_employees_working_info.biller_id', $biller);
            }
            if ($department) {
                $this->datatables->where('hr_employees_working_info.department_id', $department);
            }
            if ($group) {
                $this->datatables->where('hr_employees_working_info.group_id', $group);
            }
            if ($employee) {
                $this->datatables->where('hr_employees.id', $employee);
            }
			if ($month) {
				$this->datatables->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , $month);
			}else{
				$this->datatables->where('DATE_FORMAT('.$this->db->dbprefix("att_attedances").'.date,"%m/%Y")' , date('m/Y'));
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function ot_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getAllPositions($biller);
			$this->data['departments'] = $this->hr_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('ot_report')));
        $meta = array('page_title' => lang('ot_report'), 'bc' => $bc);
        $this->page_construct('attendances/ot_report', $meta, $this->data);
	}
	
	public function getOTReport($xls = NULL){
        $this->bpas->checkPermissions('ot_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $start_date = $this->input->get('start_date') ? $this->bpas->fsd($this->input->get('start_date')) : false;
        $end_date = $this->input->get('end_date') ? $this->bpas->fsd($this->input->get('end_date')) : false;
		if ($xls) {
			$this->db->select("	
								hr_employees.empcode,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name_kh,
								CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
								hr_positions.name as position,
								hr_departments.name as department,
								hr_groups.name as group,
								DATE_FORMAT(".$this->db->dbprefix('att_dailies_ot').".date, '%Y-%m-%d') as date,
								att_ot_policies.ot_policy,
								CONCAT(TIME_FORMAT(".$this->db->dbprefix('att_ot_policies').".time_in,'%H:%i'),' - ',TIME_FORMAT(".$this->db->dbprefix('att_ot_policies').".time_out,'%H:%i')) as ot_time,
								CONCAT(TIME_FORMAT(".$this->db->dbprefix('att_dailies_ot').".check_in,'%H:%i'),' - ',TIME_FORMAT(".$this->db->dbprefix('att_dailies_ot').".check_out,'%H:%i')) as check_time,
								TIME_FORMAT(".$this->db->dbprefix('att_dailies_ot').".ot,'%H:%i') as ot,
								IFNULL(".$this->db->dbprefix('att_dailies_ot').".food_fee,0) as food_fee
							")
					->from("att_dailies_ot")
					->join("hr_employees","hr_employees.id = att_dailies_ot.employee_id","left")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_dailies_ot.employee_id","left")
					->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
					->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
					->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
					->join("att_ot_policies","att_ot_policies.id = att_dailies_ot.policy_ot_id","left")
					->group_by("att_dailies_ot.id");

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
                $this->db->where('att_dailies_ot.employee_id', $employee);
            }
			if ($start_date) {
                $this->db->where('att_dailies_ot.date >=', $start_date);
            }
			if ($end_date) {
                $this->db->where('att_dailies_ot.date <=', $end_date);
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
                $this->excel->getActiveSheet()->setTitle(lang('ot_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name_kh'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('group'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('date'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('ot_policy'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('ot_time'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('check_time'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('ot'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('food_fee'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name_kh);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->group);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->bpas->hrsd($data_row->date));
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->ot_policy);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->ot_time);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->check_time);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->ot);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->food_fee);
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

				$filename = 'ot_report_' . date('Y_m_d_H_i_s');
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
										CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name_kh,
										CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
										hr_positions.name as position,
										hr_departments.name as department,
										hr_groups.name as group,
										DATE_FORMAT(".$this->db->dbprefix('att_dailies_ot').".date, '%Y-%m-%d') as date,
										att_ot_policies.ot_policy,
										CONCAT(TIME_FORMAT(".$this->db->dbprefix('att_ot_policies').".time_in,'%H:%i'),' - ',TIME_FORMAT(".$this->db->dbprefix('att_ot_policies').".time_out,'%H:%i')) as ot_time,
										CONCAT(TIME_FORMAT(".$this->db->dbprefix('att_dailies_ot').".check_in,'%H:%i'),' - ',TIME_FORMAT(".$this->db->dbprefix('att_dailies_ot').".check_out,'%H:%i')) as check_time,
										TIME_FORMAT(".$this->db->dbprefix('att_dailies_ot').".ot,'%H:%i') as ot,
										IFNULL(".$this->db->dbprefix('att_dailies_ot').".food_fee,0) as food_fee
									")
							->from("att_dailies_ot")
							->join("hr_employees","hr_employees.id = att_dailies_ot.employee_id","left")
							->join("hr_employees_working_info","hr_employees_working_info.employee_id = att_dailies_ot.employee_id","left")
							->join("hr_positions","hr_positions.id = hr_employees_working_info.position_id","left")
							->join("hr_departments","hr_departments.id = hr_employees_working_info.department_id","left")
							->join("hr_groups","hr_groups.id = hr_employees_working_info.group_id","left")
							->join("att_ot_policies","att_ot_policies.id = att_dailies_ot.policy_ot_id","left")
							->group_by("att_dailies_ot.id");

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
                $this->datatables->where('att_dailies_ot.employee_id', $employee);
            }
			if ($start_date) {
                $this->datatables->where('att_dailies_ot.date >=', $start_date);
            }
			if ($end_date) {
                $this->datatables->where('att_dailies_ot.date <=', $end_date);
            }
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function approve_attendance_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllCompanies('biller');
		$biller = $this->input->post('biller') ? $this->input->post('biller') : NULL;
		if($biller){
			$department = $this->input->post('department') ? $this->input->post('department') : NULL;
			$this->data['positions'] = $this->hr_model->getAllPositions($biller);
			$this->data['departments'] = $this->hr_model->getDepartments($biller);
			if($department){
				$this->data['groups'] = $this->hr_model->getGroups($department);
			}
		}
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('approve_attendance_report')));
        $meta = array('page_title' => lang('approve_attendance_report'), 'bc' => $bc);
        $this->page_construct('attendances/approve_attendance_report', $meta, $this->data);
	}
	
	public function getApproveAttendanceReport($xls = NULL){
        $this->bpas->checkPermissions('approve_attendance_report');
		$biller = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee = $this->input->get('employee') ? $this->input->get('employee') : NULL;
        $month = $this->input->get('month') ? $this->input->get('month') : NULL;
		if ($xls) {
			$this->db->select("
							hr_employees.empcode,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name_kh,
							CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
							hr_positions.name as position,
							hr_departments.name as department,
							att_approve_attedances.year,
							att_approve_attedances.month,
							att_approve_attedances.working_day,
							att_approve_attedances.present,
							att_approve_attedances.permission,
							att_approve_attedances.absent,
							att_approve_attedances.late,
							att_approve_attedances.leave_early,
							att_approve_attedances.normal_ot,
							att_approve_attedances.weekend_ot,
							att_approve_attedances.holiday_ot")
					->from("att_approve_attedances")
					->join("hr_employees","att_approve_attedances.employee_id=hr_employees.id","left")
					->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner")
					->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
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
                $this->db->where('att_approve_attedances.employee_id', $employee);
            }
			if ($month) {
				$month = explode("/",$month);
				$this->db->where("att_approve_attedances.month", $month[0]);
				$this->db->where("att_approve_attedances.year", $month[1]);
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
                $this->excel->getActiveSheet()->setTitle(lang('approve_attendance_report'));
				$this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
				$this->excel->getActiveSheet()->SetCellValue('B1', lang('name_kh'));
				$this->excel->getActiveSheet()->SetCellValue('C1', lang('name'));
				$this->excel->getActiveSheet()->SetCellValue('D1', lang('position'));
				$this->excel->getActiveSheet()->SetCellValue('E1', lang('department'));
				$this->excel->getActiveSheet()->SetCellValue('F1', lang('year'));
				$this->excel->getActiveSheet()->SetCellValue('G1', lang('month'));
				$this->excel->getActiveSheet()->SetCellValue('H1', lang('working_day'));
				$this->excel->getActiveSheet()->SetCellValue('I1', lang('present'));
				$this->excel->getActiveSheet()->SetCellValue('J1', lang('permission'));
				$this->excel->getActiveSheet()->SetCellValue('K1', lang('absent'));
				$this->excel->getActiveSheet()->SetCellValue('L1', lang('late'));
				$this->excel->getActiveSheet()->SetCellValue('M1', lang('leave_early'));
				$this->excel->getActiveSheet()->SetCellValue('N1', lang('normalot'));
				$this->excel->getActiveSheet()->SetCellValue('O1', lang('weekend_ot'));
				$this->excel->getActiveSheet()->SetCellValue('P1', lang('holiday_ot'));

                $row = 2; $total = 0;
                foreach ($data as $data_row) {
					$this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->empcode);
					$this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name_kh);
					$this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
					$this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->position);
					$this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->department);
					$this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->year);
					$this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->month);
					$this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->working_day);
					$this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->present);
					$this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->permission);
					$this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->absent);
					$this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->bpas->secTotime($data_row->late));
					$this->excel->getActiveSheet()->SetCellValue('M' . $row, $this->bpas->secTotime($data_row->leave_early));
					$this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->normal_ot);
					$this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->weekend_ot);
					$this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->holiday_ot);

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
				$this->excel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
				
				$filename = 'approve_attendance_report_' . date('Y_m_d_H_i_s');
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
										CONCAT(".$this->db->dbprefix('hr_employees').".lastname_kh,' ',".$this->db->dbprefix('hr_employees').".firstname_kh) as name_kh,
										CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
										hr_positions.name as position,
										hr_departments.name as department,
										att_approve_attedances.year,
										att_approve_attedances.month,
										att_approve_attedances.working_day,
										att_approve_attedances.present,
										att_approve_attedances.permission,
										att_approve_attedances.absent,
										att_approve_attedances.late,
										att_approve_attedances.leave_early,
										att_approve_attedances.normal_ot,
										att_approve_attedances.weekend_ot,
										att_approve_attedances.holiday_ot")
								->from("att_approve_attedances")
								->join("hr_employees","att_approve_attedances.employee_id=hr_employees.id","left")
								->join("hr_employees_working_info","hr_employees_working_info.employee_id = hr_employees.id","inner")
								->join("hr_departments","hr_employees_working_info.department_id = hr_departments.id","left")
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
                $this->datatables->where('att_approve_attedances.employee_id', $employee);
            }
			if ($month) {
				$month = explode("/",$month);
				$this->datatables->where("att_approve_attedances.month", $month[0]);
				$this->datatables->where("att_approve_attedances.year", $month[1]);
			}
			if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
				$this->datatables->where('hr_employees_working_info.biller_id', $this->session->userdata('biller_id'));
			}
            echo $this->datatables->generate();
        }
    }
    public function monthly_time_card_report(){
		$this->bpas->checkPermissions();
		$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
		$this->data['billers'] = $this->site->getAllBiller();
		$this->data['departments'] = $this->hr_model->getDepartments();
		$this->data['groups'] = $this->hr_model->getGroups();
		$this->data['positions'] = $this->hr_model->getAllPositions();
		$this->data['policy_hour'] = $this->attendances_model->getIndexPolicyWorkingHour();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('monthly_time_card_report')));
        $meta = array('page_title' => lang('monthly_time_card_report'), 'bc' => $bc);
        $this->page_construct('attendances/monthly_time_card_report', $meta, $this->data);
	}
	
	public function monthly_time_card_excel($xls = false){
		$this->bpas->checkPermissions('monthly_time_card_report',true);
		$biller_id = $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department_id = $this->input->get('department') ? $this->input->get('department') : NULL;
		$group_id = $this->input->get('group') ? $this->input->get('group') : NULL;
		$position_id = $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee_id = $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$month = $this->input->get("month") ? $this->input->get("month") : date("m/Y");
		$split_month = explode('/',$month);
		$split_month = $split_month[1].'-'.$split_month[0];
        if ($xls) {
			$policy_hour = $this->attendances_model->getIndexPolicyWorkingHour();
			$attendances = $this->attendances_model->getMonthlyAttendances($biller_id,$position_id,$department_id,$group_id,$employee_id,$month);
			$employee_attendances = $this->attendances_model->getIndexEmployeeAttedances($biller_id,$position_id,$department_id,$group_id,$employee_id,$month);
			$check_in_outs = $employee_attendances["check_in_out"];
			$daily_attendances = $employee_attendances["attendances"];
			
			
            if ($attendances && $employee_attendances) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
				$this->excel->getActiveSheet()->setTitle(lang('monthly_time_card_report'));
				
				$row = 1;
				foreach($attendances as $attendance){
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':R'.$row);
					$this->excel->getActiveSheet()->SetCellValue('A'.$row, $attendance->name);
					$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
					$excel_style['font']['name'] = 'Arial';
					$excel_style['font']['size'] = 18;
					$excel_style['font']['bold'] = true;
					$excel_style['borders'] = array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_NONE));							
					$this->excel->getActiveSheet()->getStyle('A'.$row)->applyFromArray($excel_style);	
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(21.75);
					$row++;
					
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':R'.$row);
					$this->excel->getActiveSheet()->SetCellValue('A'.$row, lang("employee_monthly_time_in_out_report"));
					$excel_style['font']['size'] = 16;
					$excel_style['font']['bold'] = false;
					$this->excel->getActiveSheet()->getStyle('A'.$row)->applyFromArray($excel_style);	
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(21.75);
					$row++;
					
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':R'.$row);
					$this->excel->getActiveSheet()->SetCellValue('A'.$row, $this->bpas->hrsd($split_month.'-01').' - '.$this->bpas->hrsd(date("Y-m-t", strtotime($split_month.'-01'))));
					$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$excel_style['font']['size'] = 10;
					$this->excel->getActiveSheet()->getStyle('A'.$row)->applyFromArray($excel_style);
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(18);
					$row++;
					
					$this->excel->getActiveSheet()->SetCellValue('A'.$row, lang('ល-រ'));
					$this->excel->getActiveSheet()->SetCellValue('B'.$row, lang('កាលបរិច្ឆេទ'));
					$this->excel->getActiveSheet()->SetCellValue('C'.$row, lang('ម៉ោងចូល'));
					$this->excel->getActiveSheet()->SetCellValue('D'.$row, lang('ម៉ោងចេញ'));
					$this->excel->getActiveSheet()->SetCellValue('E'.$row, lang('ម៉ោងចូល'));
					$this->excel->getActiveSheet()->SetCellValue('F'.$row, lang('ម៉ោងចេញ'));
					$this->excel->getActiveSheet()->SetCellValue('G'.$row, lang('មកយឹត'));
					$this->excel->getActiveSheet()->SetCellValue('H'.$row, lang('ចេញមុន'));
					$this->excel->getActiveSheet()->SetCellValue('I'.$row, lang('ម៉ោងធ្វើការ'));
					$this->excel->getActiveSheet()->SetCellValue('J'.$row, lang('ម៉ោងថែម'));
					$this->excel->getActiveSheet()->SetCellValue('K'.$row, lang('ម៉ោងថែម'));
					$this->excel->getActiveSheet()->SetCellValue('L'.$row, lang('ម៉ោងថែម'));
					$this->excel->getActiveSheet()->SetCellValue('M'.$row, lang('អវត្តអត់ច្បាប់'));
					$this->excel->getActiveSheet()->SetCellValue('N'.$row, lang('អវត្តមាន មានច្បាប់'));
					$this->excel->getActiveSheet()->SetCellValue('O'.$row, lang('ច្បាប់ឈឺ'));
					$this->excel->getActiveSheet()->SetCellValue('P'.$row, lang('ឈប់ប្រចាំឆ្នាំ'));
					$this->excel->getActiveSheet()->SetCellValue('Q'.$row, lang('ច្បាប់ពិសេស'));
					$this->excel->getActiveSheet()->SetCellValue('R'.$row, lang('ច្បាប់សំរាល'));
					$row++;
					
					$this->excel->getActiveSheet()->SetCellValue('A'.$row, lang('Nº'));
					$this->excel->getActiveSheet()->SetCellValue('B'.$row, lang('Date'));
					$this->excel->getActiveSheet()->SetCellValue('C'.$row, lang('Time In'));
					$this->excel->getActiveSheet()->SetCellValue('D'.$row, lang('Time Out'));
					$this->excel->getActiveSheet()->SetCellValue('E'.$row, lang('Time In'));
					$this->excel->getActiveSheet()->SetCellValue('F'.$row, lang('Time Out'));
					$this->excel->getActiveSheet()->SetCellValue('G'.$row, lang('Late'));
					$this->excel->getActiveSheet()->SetCellValue('H'.$row, lang('Early'));
					$this->excel->getActiveSheet()->SetCellValue('I'.$row, lang('WH'));
					$this->excel->getActiveSheet()->SetCellValue('J'.$row, lang('OT'));
					$this->excel->getActiveSheet()->SetCellValue('K'.$row, lang('OT Sunday'));
					$this->excel->getActiveSheet()->SetCellValue('L'.$row, lang('OT PH'));
					$this->excel->getActiveSheet()->SetCellValue('M'.$row, lang('AB'));
					$this->excel->getActiveSheet()->SetCellValue('N'.$row, lang('AW'));
					$this->excel->getActiveSheet()->SetCellValue('O'.$row, lang('Sick'));
					$this->excel->getActiveSheet()->SetCellValue('P'.$row, lang('ANL'));
					$this->excel->getActiveSheet()->SetCellValue('Q'.$row, lang('SP'));
					$this->excel->getActiveSheet()->SetCellValue('R'.$row, lang('Maternity'));
					
					$excel_style['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
					$excel_style['fill']['color']['rgb'] = '99CCFF';
					$excel_style['font']['size'] = 8;
					$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
					$excel_style['borders'] = array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN));							
					$this->excel->getActiveSheet()->getStyle('A'.$row.':R'.$row)->applyFromArray($excel_style);	
					$this->excel->getActiveSheet()->getStyle('A'.($row-1).':R'.($row-1))->applyFromArray($excel_style);	
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
					$this->excel->getActiveSheet()->getRowDimension($row-1)->setRowHeight(20);
					$row++;
					
					$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER);
					$excel_style['font']['size'] = 10;
					$excel_style['fill']['color']['rgb'] = 'FFFFFF';
					$excel_style['font']['bold'] = true;
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':R'.$row);
					$this->excel->getActiveSheet()->SetCellValue('A'.$row, $attendance->lastname.' '.$attendance->firstname.', '.$attendance->empcode.', '.$attendance->department);
					$this->excel->getActiveSheet()->getStyle('A'.$row.':R'.$row)->applyFromArray($excel_style);	
					$this->excel->getActiveSheet()->getRowDimension($row)->setRowHeight(20);
					$row++;
					
					$excel_style['font']['bold'] = false;
					$excel_style['alignment'] = array('vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,'horizontal'  => PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$b = 1;
					$tlate = 0;
					$tleave_early = 0;
					$tpresent = 0;
					$tnormal_ot = 0;
					$tweekend_ot = 0;
					$tholiday_ot = 0;
					$tabsent = 0;
					$tother_leave = 0;
					$tsick_leave = 0;
					$tannual_leave = 0;
					$tspecial_leave = 0;
					$thalf_pay_leave = 0;
					$begin = new DateTime($split_month.'-01');
					$end = new DateTime(date("Y-m-t", strtotime($split_month.'-01')));
					for($i = $begin; $i <= $end; $i->modify('+1 day')){
						$date = $i->format("Y-m-d");
						$one_in = (isset($check_in_outs[$attendance->id][$date]['one']['in']) ? $check_in_outs[$attendance->id][$date]['one']['in'] : "");
						$one_out = (isset($check_in_outs[$attendance->id][$date]['one']['out']) ? $check_in_outs[$attendance->id][$date]['one']['out'] : "");
						$two_in = (isset($check_in_outs[$attendance->id][$date]['two']['in']) ? $check_in_outs[$attendance->id][$date]['two']['in'] : "");
						$two_out = (isset($check_in_outs[$attendance->id][$date]['two']['out']) ? $check_in_outs[$attendance->id][$date]['two']['out'] : "");
						$late = "";
						$leave_early = "";
						$present = "";
						$normal_ot = "";
						$weekend_ot = "";
						$holiday_ot = ""; 
						$absent = "";
						$other_leave = "";
						$sick_leave = "";
						$annual_leave = "";
						$special_leave = "";
						$half_pay_leave = "";
						
						if(isset($daily_attendances[$attendance->id][$date])){
							if($daily_attendances[$attendance->id][$date]->weekend > 0){
								if($one_in == "" || $one_out == ""){
									$one_in = "SUN";
									$one_out = "SUN";
								}
								if($two_in == "" || $two_out == ""){
									$two_in = "SUN";
									$two_out = "SUN";
								}
								
							}else if($daily_attendances[$attendance->id][$date]->holiday > 0){
								if($one_in == "" || $one_out == ""){
									$one_in = "HOL";
									$one_out = "HOL";
								}
								if($two_in == "" || $two_out == ""){
									$two_in = "HOL";
									$two_out = "HOL";
								}
							}
							
							if(isset($policy_hour[$attendance->policy_id])){
								if($daily_attendances[$attendance->id][$date]->present > 0){
									$present = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->present * $policy_hour[$attendance->policy_id],0);
									$tpresent += $present;
								}
								if($daily_attendances[$attendance->id][$date]->absent > 0){
									$absent = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->absent * $policy_hour[$attendance->policy_id],0);
									$tabsent += $absent;
								}
								if($daily_attendances[$attendance->id][$date]->other_leave > 0){
									$other_leave = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->other_leave * $policy_hour[$attendance->policy_id],0);
									$tother_leave += $other_leave;
								}
								if($daily_attendances[$attendance->id][$date]->sick_leave > 0){
									$sick_leave = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->sick_leave * $policy_hour[$attendance->policy_id],0);
									$tsick_leave += $sick_leave;
								}
								if($daily_attendances[$attendance->id][$date]->annual_leave > 0){
									$annual_leave = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->annual_leave * $policy_hour[$attendance->policy_id],0);
									$tannual_leave += $annual_leave;
								}
								if($daily_attendances[$attendance->id][$date]->special_leave > 0){
									$special_leave = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->special_leave * $policy_hour[$attendance->policy_id],0);
									$tspecial_leave += $special_leave;
								}
								if($daily_attendances[$attendance->id][$date]->half_pay_leave > 0){
									$half_pay_leave = $this->bpas->formatDecimal($daily_attendances[$attendance->id][$date]->half_pay_leave * $policy_hour[$attendance->policy_id],0);
									$thalf_pay_leave += $half_pay_leave;
								}
							}
							if($daily_attendances[$attendance->id][$date]->late > 0){
								$late = $this->bpas->formatDecimal(($daily_attendances[$attendance->id][$date]->late / 60),0);
								$tlate += $late;
							}
							if($daily_attendances[$attendance->id][$date]->leave_early > 0){
								$leave_early = $this->bpas->formatDecimal(($daily_attendances[$attendance->id][$date]->leave_early / 60),0);
								$tleave_early += $leave_early;
							}
							if($daily_attendances[$attendance->id][$date]->normal_ot > 0){
								$normal_ot = $this->bpas->formatDecimal(($daily_attendances[$attendance->id][$date]->normal_ot / 3600),0);
								$tnormal_ot += $normal_ot;
							}
							if($daily_attendances[$attendance->id][$date]->weekend_ot > 0){
								$weekend_ot = $this->bpas->formatDecimal(($daily_attendances[$attendance->id][$date]->weekend_ot / 3600),0);
								$tweekend_ot += $weekend_ot;
							}
							if($daily_attendances[$attendance->id][$date]->holiday_ot > 0){
								$holiday_ot = $this->bpas->formatDecimal(($daily_attendances[$attendance->id][$date]->holiday_ot / 3600),0);
								$tholiday_ot += $holiday_ot;
							}
						}
						
						$this->excel->getActiveSheet()->SetCellValue('A'.$row, ($b++));
						$this->excel->getActiveSheet()->SetCellValue('B'.$row, $this->bpas->hrsd($date));
						$this->excel->getActiveSheet()->SetCellValue('C'.$row, $one_in);
						$this->excel->getActiveSheet()->SetCellValue('D'.$row, $one_out);
						$this->excel->getActiveSheet()->SetCellValue('E'.$row, $two_in);
						$this->excel->getActiveSheet()->SetCellValue('F'.$row, $two_out);
						$this->excel->getActiveSheet()->SetCellValue('G'.$row, $late);
						$this->excel->getActiveSheet()->SetCellValue('H'.$row, $leave_early);
						$this->excel->getActiveSheet()->SetCellValue('I'.$row, $present);
						$this->excel->getActiveSheet()->SetCellValue('J'.$row, $normal_ot);
						$this->excel->getActiveSheet()->SetCellValue('K'.$row, $weekend_ot);
						$this->excel->getActiveSheet()->SetCellValue('L'.$row, $holiday_ot);
						$this->excel->getActiveSheet()->SetCellValue('M'.$row, $absent);
						$this->excel->getActiveSheet()->SetCellValue('N'.$row, $other_leave);
						$this->excel->getActiveSheet()->SetCellValue('O'.$row, $sick_leave);
						$this->excel->getActiveSheet()->SetCellValue('P'.$row, $annual_leave);
						$this->excel->getActiveSheet()->SetCellValue('Q'.$row, $special_leave);
						$this->excel->getActiveSheet()->SetCellValue('R'.$row, $half_pay_leave);
						
						$this->excel->getActiveSheet()->getStyle('A'.$row.':R'.$row)->applyFromArray($excel_style);	
						$row++;
					}
					
					$this->excel->getActiveSheet()->SetCellValue('G'.$row, $tlate);
					$this->excel->getActiveSheet()->SetCellValue('H'.$row, $tleave_early);
					$this->excel->getActiveSheet()->SetCellValue('I'.$row, $tpresent);
					$this->excel->getActiveSheet()->SetCellValue('J'.$row, $tnormal_ot);
					$this->excel->getActiveSheet()->SetCellValue('K'.$row, $tweekend_ot);
					$this->excel->getActiveSheet()->SetCellValue('L'.$row, $tholiday_ot);
					$this->excel->getActiveSheet()->SetCellValue('M'.$row, $tabsent);
					$this->excel->getActiveSheet()->SetCellValue('N'.$row, $tother_leave);
					$this->excel->getActiveSheet()->SetCellValue('O'.$row, $tsick_leave);
					$this->excel->getActiveSheet()->SetCellValue('P'.$row, $tannual_leave);
					$this->excel->getActiveSheet()->SetCellValue('Q'.$row, $tspecial_leave);
					$this->excel->getActiveSheet()->SetCellValue('R'.$row, $thalf_pay_leave);
					
					$this->excel->getActiveSheet()->mergeCells('A'.$row.':F'.$row);
					$this->excel->getActiveSheet()->getStyle('A'.$row.':R'.$row)->applyFromArray($excel_style);	
					$row++;
					
					for($i = $b; $i < 36; $i++){
						$row++;
					}
				}
				
				$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
				$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(10.5);	
				$this->excel->getActiveSheet()->getColumnDimension('M')->setWidth(9.45);	
				$this->excel->getActiveSheet()->getColumnDimension('N')->setWidth(13.71);	

				$this->excel->getActiveSheet()->getPageMargins()->setTop(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setRight(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setLeft(0.1);
				$this->excel->getActiveSheet()->getPageMargins()->setBottom(0.1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$this->excel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				$this->excel->getActiveSheet()->getPageSetup()->setPrintArea('A:R');
				$this->excel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				$this->excel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);


				$filename = 'monthly_time_card_report_' . date('Y_m_d_H_i_s');
				$this->load->helper('excel');
				create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
	}
	  //show attendance note modal
    function note_modal_form($user_id = 0, $id = null) {
        $view_data["clock_out"]  = $this->input->post("clock_out"); //trigger clockout after submit?
        $view_data["user_id"] 	 = ($user_id ? $user_id : $this->session->userdata('user_id'));
		// $this->input->post('id')
        $view_data['model_info'] = $this->attendances_model->get_log_time($id);
		$this->load->view($this->theme . 'attendances/note_modal_form', $view_data);
        // $this->load->view('attendance/note_modal_form', $view_data);
    }
	function log_time($user_id = 0) {
        $note = $this->input->post('note'); 
        if ($user_id && $user_id != $this->session->userdata('user_id')) {
            //check if the login user has permission to clock in/out this user
            $this->access_only_allowed_members($user_id);
        } 
        $this->attendances_model->log_time($user_id ? $user_id : $this->session->userdata('user_id'), $note);
        if ($user_id) {
            echo json_encode(array("success" => true, "data" => $this->_clock_in_out_row_data($user_id), 'id' => $user_id, 'message' => lang('record_saved'), "isUpdate" => true));
        } else if ($this->input->post("clock_out")) {
            echo json_encode(array("success" => true, "clock_widget" => clock_widget(true, $this->session->userdata('user_id'))));
        } else {
            clock_widget(false, $this->session->userdata('user_id'));
        }
    }
    public function roster_code(){
		$this->bpas->checkPermissions('policies');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('roster_code')));
		$meta = array('page_title' => lang('roster_code'), 'bc' => $bc);
		$this->page_construct('attendances/roster_code', $meta, $this->data);
	}
	
	public function getRosterCode()
	{	
		$this->bpas->checkPermissions('policies');	
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_roster_code") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/delete_roster_code/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_roster_code') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('attendances/edit_roster_code/$1').'" data-toggle="modal" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_roster_code').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					att_roster_code.id as id, 
					att_roster_code.code, 
					att_roster_code.hour,
					att_roster_code.from_time,
					att_roster_code.to_time,
					att_roster_code.note")
            ->from("att_roster_code")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	public function add_roster_code()
	{
		$this->bpas->checkPermissions('policies');
		$post = $this->input->post();	
		$this->form_validation->set_rules('code', lang("code"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'code'  	=> $this->input->post('code'),
				'from_time' => $this->input->post('from_time'),
				'to_time'  	=> $this->input->post('to_time'),
				'hour'  	=> $this->input->post('hour'),
				'note' 		=> $this->input->post('note'),
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->attendances_model->addRosterCode($data)) {
            $this->session->set_flashdata('message', $this->lang->line("roster_code_added").' '.$post['policy']);
            admin_redirect("attendances/roster_code");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/add_roster_code', $this->data);	
		}	
	}
	public function edit_roster_code($id = null)
	{		
		$this->bpas->checkPermissions('policies');	
		$post = $this->input->post();		
		
		$this->form_validation->set_rules('code', lang("code"), 'required');
		// $this->form_validation->set_rules('from_time', lang("from_time"), 'required');
		// $this->form_validation->set_rules('to_time', lang("to_time"), 'required');
		// $this->form_validation->set_rules('hour', lang("hour"), 'required');

		if ($this->form_validation->run() == true) 
		{	
			$data = array(
				'code'  	=> $this->input->post('code'),
				'from_time' => $this->input->post('from_time'),
				'to_time'  	=> $this->input->post('to_time'),
				'hour'  	=> $this->input->post('hour'),
				'note' 		=> $this->input->post('note'),
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $this->attendances_model->updateRosterCode($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("roster_code_updated").' '.$post['policy']);
            admin_redirect("attendances/roster_code");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']=$id;
			$this->data['policy'] = $this->attendances_model->getRosterCodeByID($id);
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/edit_roster_code', $this->data);
		}			
	}
	public function delete_roster_code($id = null)
    {	
		$this->bpas->checkPermissions('policy');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("att_roster_code");
        	if($result){
        		$this->session->set_flashdata('message', lang("roster_policy_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }

	public function roster(){
		$this->bpas->checkPermissions('policies');	
		$this->data['modal_js'] = $this->site->modal_js();
		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('roster')));
		$meta = array('page_title' => lang('roster'), 'bc' => $bc);
		$this->page_construct('attendances/roster', $meta, $this->data);
	}
	public function getRoster()
	{	
		$this->bpas->checkPermissions('policies');	
        $this->load->library('datatables');
		$delete_link = "<a href='#' class='po' title='" . lang("delete_policy") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('attendances/delete_roster/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_roster') . "</a>";
		
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
					        <ul class="dropdown-menu pull-right" role="menu">
								<li><a href="'.admin_url('attendances/edit_roster/$1').'" data-toggle="modal" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_roster').'</a></li>
					            <li>'.$delete_link.'</li>
					        </ul>
					    </div>';
        $this->datatables
            ->select("
					{$this->db->dbprefix('att_roster')}.id as id, 
					CONCAT(".$this->db->dbprefix('hr_employees').".lastname,' ',".$this->db->dbprefix('hr_employees').".firstname) as name,
					att_policies.code, 
					att_roster.working_day,
					att_policies.time_in_one,
					att_policies.time_out_two,
					att_roster.time_one,
					att_roster.time_two,
					att_policies.note")
            ->from("att_roster")
            ->join("att_policies","att_policies.id = att_roster.policy_id","left")
            ->join("hr_employees","hr_employees.id = att_roster.employee_id","left")
            ->order_by("att_roster.working_day","DESC")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
	}
	public function add_roster()
	{
		$this->bpas->checkPermissions('policies');
		$post = $this->input->post();	
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		$this->form_validation->set_rules('working_day', lang("working_day"), 'required');
		$this->form_validation->set_rules('policy', lang("policy"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$employee_id 	= $this->input->post('employee');
			$employee = $this->attendances_model->getEmployeeInfoByID($employee_id);

			$data = array(
				'employee_id'  	=> $employee_id,
				'working_day' 	=> $this->bpas->fsd($this->input->post('working_day')),
				'policy_id'  	=> $this->input->post('policy'),
				'time_one'  	=> $this->input->post('first_half'),
				'time_two' 		=> $this->input->post('second_half'),
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }	

		if ($this->form_validation->run() == true && $id = $this->attendances_model->addRoster($data)) {
            $this->session->set_flashdata('message', $this->lang->line("roster_added").' '.$post['policy']);
            admin_redirect("attendances/roster");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
			$this->data['employees'] = $this->hr_model->getEmployees(false,false,false,false,'active');
			$this->data['policies'] = $this->hr_model->getPolicies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/add_roster', $this->data);	
		}	
	}
	public function edit_roster($id = null)
	{		
		$this->bpas->checkPermissions('policies');	
		$post = $this->input->post();		
		
		$this->form_validation->set_rules('employee', lang("employee"), 'required');
		$this->form_validation->set_rules('working_day', lang("working_day"), 'required');
		$this->form_validation->set_rules('policy', lang("policy"), 'required');
		if ($this->form_validation->run() == true) 
		{	
			$employee_id 	= $this->input->post('employee');
			$employee = $this->attendances_model->getEmployeeInfoByID($employee_id);

			$data = array(
				'employee_id'  	=> $employee_id,
				'working_day' 	=> $this->bpas->fsd($this->input->post('working_day')),
				'policy_id'  	=> $this->input->post('policy'),
				'time_one'  	=> $this->input->post('first_half'),
				'time_two' 		=> $this->input->post('second_half'),
			);
		} elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
		if ($this->form_validation->run() == true && $this->attendances_model->updateRoster($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("roster_updated").' '.$post['policy']);
            admin_redirect("attendances/roster");
        }else{
			$this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
			$this->data['id']		=$id;
			$this->data['employees']= $this->hr_model->getEmployees(false,false,false,false,'active');
			$this->data['policy'] 	= $this->attendances_model->getRosterByID($id);
			$this->data['policies'] = $this->hr_model->getPolicies();
			$this->data['modal_js'] = $this->site->modal_js();
			$this->load->view($this->theme . 'attendances/edit_roster', $this->data);
		}			
	}
	public function delete_roster($id = null)
    {	
		$this->bpas->checkPermissions('policy');
        if (isset($id) || $id != null){        	
        	$result = $this->db->where("id",$id)->delete("att_roster");
        	if($result){
        		$this->session->set_flashdata('message', lang("roster_deleted"));
            	admin_redirect($_SERVER['HTTP_REFERER']);
        	}
        }
    }
    function import_roster(){
        $this->bpas->checkPermissions('add_roster', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('excel_file', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if (isset($_FILES["excel_file"]))  {
				$this->load->library('excel');
				$path = $_FILES["excel_file"]["tmp_name"];
				$object = PHPExcel_IOFactory::load($path);
				foreach($object->getWorksheetIterator() as $worksheet){
					$highestRow = $worksheet->getHighestRow();
					$highestColumn = $worksheet->getHighestColumn();
					for($row=2; $row<=$highestRow; $row++){
						$employee_code 	= $worksheet->getCellByColumnAndRow(0, $row)->getValue();
						$working_day 	= $worksheet->getCellByColumnAndRow(1, $row)->getValue();
						$first_half 	= $worksheet->getCellByColumnAndRow(2, $row)->getValue();
						$second_half 	= $worksheet->getCellByColumnAndRow(3, $row)->getValue();
						$employee_info 	= $this->attendances_model->getEmployeeByCode(trim($employee_code));

						if (!$employee_info) {
							$this->session->set_flashdata('error', lang("check_employee_code") . " (" . $employee_code . "). " . lang("employee_not_exist") . " (" . lang("line_no") . " " . $row . ")");
							admin_redirect("attendances/roster");
						}
						
						if($working_day == ''){
							$this->session->set_flashdata('error', lang("working_day"). lang("line_no") . " " . $row . ")");
							admin_redirect("attendances/roster");
						}
						$employee = $this->attendances_model->getEmployeeInfoByID($employee_info->id);
						 
						$data[] = array(
							'employee_id'  	=> $employee_info->id,
							'working_day'  	=> $this->bpas->fsd($working_day,true),
							'policy_id'  	=> $employee->policy_id ? $employee->policy_id:null,
							'time_one'  	=> $first_half,
							'time_two' 		=> $second_half,
						);
					}
				}
            }


        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('attendances/roster');
        }
        if ($this->form_validation->run() == true && !empty($data) && $this->attendances_model->ImportRoster($data)) {
			$this->session->set_flashdata('message', lang("roster_imported"));
			admin_redirect('attendances/roster');
            
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'attendances/import_roster', $this->data);
        }
    }
	public function roster_calendar($biller_id = false){
		$this->bpas->checkPermissions();
		$biller_id 		= $this->input->get('biller') ? $this->input->get('biller') : NULL;
		$department_id 	= $this->input->get('department') ? $this->input->get('department') : NULL;
		$group_id 		= $this->input->get('group') ? $this->input->get('group') : NULL;
		$position_id 	= $this->input->get('position') ? $this->input->get('position') : NULL;
		$employee_id 	= $this->input->get('employee') ? $this->input->get('employee') : NULL;
		$month 		 	= $this->input->get("month") ? $this->input->get("month") : date("m/Y");
		$split_month 	= explode('/',$month);
		$split_month 	= $split_month[1].'-'.$split_month[0];

		$this->data['billers'] 		= $this->site->getBillers();
		$this->data['roster_code'] 	= $this->attendances_model->getRosterCode();
		$this->data['employees'] 	= $this->hr_model->getEmployees(false,false,false,false,'active');
		$this->data['leave_types']  = $this->hr_model->getAllLeaveTypes();

		$this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payrolls'), 'page' => lang('payroll')), array('link' => '#', 'page' => lang('rosters')));
        $meta = array('page_title' => lang('rosters'), 'bc' => $bc);
        $this->page_construct('attendances/roster_calendar', $meta, $this->data);
	}
	public function view_roster()
    {
        $this->bpas->checkPermissions('check_in_outs');
        $this->data['getRoster'] = $this->attendances_model->getRoster();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('roster')));
        $meta = array('page_title' => lang('roster'), 'bc' => $bc);
        $this->page_construct('attendances/view_roster', $meta, $this->data);
    }
    function roster_actions()
    {
		
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
				
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete-policy');
                    foreach ($_POST['val'] as $id) {
                        $this->attendances_model->deleteRosterByID($id);
                    }
                    $this->session->set_flashdata('message', lang("roster_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('policy');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('policy'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('time_in_one'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('time_out_one'));
					$this->excel->getActiveSheet()->SetCellValue('D1', lang('time_in_two'));
					$this->excel->getActiveSheet()->SetCellValue('E1', lang('time_out_two'));
					$this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));	
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $policy = $this->attendances_model->getPolicyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $policy->policy);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $policy->time_in_one);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $policy->time_out_one);
						$this->excel->getActiveSheet()->SetCellValue('D' . $row, $policy->time_in_two);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $policy->time_out_two);
						$this->excel->getActiveSheet()->SetCellValue('F' . $row, strip_tags($policy->note));
						$row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
					$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
					
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'policy_list_' . date('Y_m_d_H_i_s');
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
    public function new_checkin_out_report()
	{
		$this->bpas->checkPermissions('check_in_out_report');
		$this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
		$this->data['billers'] = $this->site->getAllBiller();
		$this->data['groups'] = $this->ion_auth->groups()->result_array();
		$this->data['attendances'] = empty($this->attendances_model->getTodayAttendance())?[]:$this->attendances_model->getTodayAttendance();

		$bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('attendances'), 'page' => lang('attendance')), array('link' => '#', 'page' => lang('check_in_out_report')));
		$meta = array('page_title' => lang('check_in_out_report'), 'bc' => $bc);	
		$this->page_construct('attendances/new_checkin_out_report',$meta,$this->data);
	}
}
?>