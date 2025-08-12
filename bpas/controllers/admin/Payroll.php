<?php
//real
defined('BASEPATH') or exit('No direct script access allowed');

class Payroll extends MY_Controller
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
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('auth', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');
        $this->load->admin_model('payrolls_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('auth_model');
        $this->load->admin_model('site');
        $this->load->library('ion_auth');

        $this->commissionPayable =  200405;

    }

    public function index()
    {
        $this->bpas->checkPermissions();
        $months = array();
        for ($m = 1; $m <= 12; $m++) {
            $mth = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
            $months[$m] = $mth;
        }

        $start_y   = date("Y", strtotime("-1 year"));
        $current_y = date("Y");
        $years[$current_y] = $current_y;
        for($y = 1; $y <= ($current_y - $start_y); $y++){
            $y_ = date("Y", strtotime("" . -$y . " year"));
            $years[$y_] = $y_;
        }

        $this->data["months"] = $months;
        $this->data["years"]  = $years;
        $this->data['groups'] = $this->site->getAllGroup();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('users')]];
        $meta = ['page_title' => lang('users'), 'bc' => $bc];
        $this->page_construct('payrolls/index_basic', $meta, $this->data);
    }

    public function getUsers()
    {
        $this->bpas->checkPermissions('index');

        $group  = $this->input->get('group') ? $this->input->get('group') : null;
        $month  = $this->input->get('month') ? $this->input->get('month') : date('F');
        $year   = $this->input->get('year') ? $this->input->get('year') : date('Y');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;
        // $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;

        $this->load->library('datatables');
        $this->datatables
        ->select(
            $this->db->dbprefix('users') . '.id as id, 
            CONCAT_WS(" ", first_name, last_name) as uname, 
            users.gender, phone, email, 
            basic_salary, 
            ' . $this->db->dbprefix('users') . '.commission, 
            ' . $this->db->dbprefix('groups') . '.name, 
            active, 
            IFNULL(' . $this->db->dbprefix('staff_payslip') . '.status, 0),
            CONCAT(IFNULL(' . $this->db->dbprefix('staff_payslip') . '.status, 2), "___", ' . $this->db->dbprefix('users') . '.id, "__", "'.$month.'", "__", "'.$year.'") as gen')
        ->from('users')
        ->join('groups', 'users.group_id  = groups.id', 'left')
        ->join('staff_payslip', 'users.id = staff_payslip.staff_id and month = "'. $month .'" and year = "'. $year .'"', 'left')
        ->edit_column('active', '$1__$2', 'active, id')
        ->group_by('users.id');

        if (!$this->Owner && !$this->Admin) {
            $this->datatables->where("users.biller_id",$this->session->userdata('biller_id'));
        }
        if($group){
            $this->datatables->where('users.group_id', $group);
        }
        if($biller){
            $this->datatables->where('users.biller_id', $biller);
        }
        // if($warehouse){
        //     $this->datatables->where('users.warehouse_id', $warehouse);
        // }
        // if($month){
        //     $this->datatables->where('staff_payslip.month', $month);
        // }
        // if($year){
        //     $this->datatables->where('staff_payslip.year', $year);
        // }

        echo $this->datatables->generate();
    }

    public function user_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {

                $month = $this->input->get('month') ? $this->input->get('month') : date('F');
                $year  = $this->input->get('year') ? $this->input->get('year') : date('Y');
                if ($this->input->post('form_action') == 'generate') {
                    $this->session->set_flashdata('message', lang('generate_payslip'));
                    redirect($_SERVER['HTTP_REFERER']);

                } elseif ($this->input->post('form_action') == 'combine') {
                    $data = [];
                    foreach ($_POST['val'] as $id) {
                        $biller_id = $user->biller_id ? $user->biller_id : $this->accounting_setting->biller_id;

                        $user            = $this->site->getUser($id);
                        $comm            = $this->auth_model->getUserCommissionByID_M_Y($id, $month, $year);
                        $staff_payslip   = $this->auth_model->getAllStaffPaySlipByID($id);
                        $total_allowance = $total_deduction = $leave_deduction = $tax = 0;                        
                        $net_salary      = $user->basic_salary + $total_allowance + $total_deduction + $leave_deduction + (isset($comm->commission) ? $comm->commission : 0);

                        if($user->active == 0){
                            continue;
                        }
                        $b = false;
                        if($staff_payslip !== false){
                            foreach($staff_payslip as $st_ps){
                                if($st_ps->month == $month && $st_ps->year == $year){
                                    $b = true;
                                    break;
                                }
                            }
                        }
                        if($b == true){
                            continue;
                        }
                        $data[] = array(
                            'staff_id'          => $user->id,
                            'basic'             => $user->basic_salary,
                            'total_allowance'   => $total_allowance,
                            'total_deduction'   => $total_deduction,
                            'leave_deduction'   => $leave_deduction,
                            'tax'               => $tax,
                            'commission'        => $comm->commission,
                            'net_salary'        => $net_salary,
                            'status'            => '0',
                            'month'             => $month,
                            'year'              => $year,
                            'payment_date'      => null, //date('Y/m/d H:i:s', time())
                            'biller_id' => $biller_id,
                        );
                        //=======add acounting=========//
                        if($this->Settings->accounting == 1){
                            $date = $this->bpas->convertMonthToLatang($month,$year);
                            $reference = '';//$this->site->getReference('payroll');
                            $salary_payable = $this->accounting_setting->default_payable;
                            $commission = $comm->commission;
                            $account_commission = $this->accounting_setting->payroll_commission;
                            $biller_id = $user->biller_id ? $user->biller_id : $this->accounting_setting->biller_id;
                            $param = $user->id.'__'.$month.'__'.$year;
                            
                            $payroll_account = $this->accounting_setting->default_payroll;
                            
                            $accTrans = array(
                                'tran_type' => 'Payroll',
                                'tran_date' => $date,
                                'reference_no' => $reference,
                                'account_code' => $payroll_account,
                                'amount' => $user->basic_salary,
                                'narrative' => $this->site->getAccountName($payroll_account),
                                'biller_id' => $biller_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'payroll_id' => $param,
                                'activity_type' => $this->site->get_activity($payroll_account)
                            );
                            $this->db->insert('gl_trans', $accTrans);

                            $accTrans1 = array(
                                'tran_type' => 'Payroll',
                                'tran_date' => $date,
                                'reference_no' => $reference,
                                'account_code' => $salary_payable,
                                'amount' => -($user->basic_salary),
                                'narrative' => $this->site->getAccountName($salary_payable),
                                'biller_id' => $biller_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'payroll_id' => $param,
                                'activity_type' => $this->site->get_activity($salary_payable)
                            );

                            $this->db->insert('gl_trans', $accTrans1);

                            if($commission >0){
                                $accTrans2 = array(
                                    'tran_type' => 'Payroll',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $account_commission,
                                    'amount' => $commission,
                                    'narrative' => $this->site->getAccountName($account_commission),
                                    'biller_id' => $biller_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                    'payroll_id' => $param,
                                    'activity_type' => $this->site->get_activity($account_commission)
                                );
                                $this->db->insert('gl_trans', $accTrans2);

                                $accTrans3 = array(
                                    'tran_type' => 'Payroll',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $this->commissionPayable,
                                    'amount' => - $commission,
                                    'narrative' => $this->site->getAccountName($this->commissionPayable),
                                    'biller_id' => $biller_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                    'payroll_id' => $param,
                                    'activity_type' => $this->site->get_activity($this->commissionPayable)
                                );
                                $this->db->insert('gl_trans', $accTrans3);
                            }
                        }
                        //============end accounting=======//
                    }

                    if(!empty($data)){
                        $this->db->insert_batch('staff_payslip', $data);
                        $this->session->set_flashdata('message', lang('generate_payslip_successfully'));
                    } else {
                        if($user->active == 0){
                            $this->session->set_flashdata('error', lang('cannot_generate_payslip'));    
                        } else {
                            $this->session->set_flashdata('error', lang('generate_payslip_already'));
                        }
                    }
                    redirect($_SERVER['HTTP_REFERER']);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('users'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('username'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('gender'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('award_points'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('basic_salary'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('commission'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('active_status'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('payment_status'));

                    $row = 2;
                    $act = ['0' => 'inactive', '1' => 'active'];
                    $pmt = ['0' => 'unpaid',   '1' => 'paid'];

                    foreach ($_POST['val'] as $id) {
                        $user       = $this->site->getUser($id);
                        $group      = $this->site->getUserGroup($user->id);
                        $st_payslip = $this->auth_model->getStaffPaySlipByID_M_Y($user->id, $month, $year);

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $user->first_name . ' ' . $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $user->gender);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->phone);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $user->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user->award_points);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $user->basic_salary);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $user->commission);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $group->name);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $act[$user->active]);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $pmt[$st_payslip != false ? 1 : 0]);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'staff_payslip_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_user_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function ungenerate($param = null)
    {
        $this->bpas->checkPermissions('index');
        $this->form_validation->set_rules('confirm', lang('confirm'), 'required');

        $arr = explode("__", $param);
        $id = $arr[0];
        $month = $arr[1];
        $year = $arr[2];
        if ($this->form_validation->run() == false) {
            if ($this->input->post('ungenerate')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $this->data['user']     = $this->auth_model->getStaffPaySlipByID_M_Y($id, $month, $year);
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'payroll/ungenerate_user', $this->data);
            }
        } else {
            if ($this->input->post('confirm') == 'yes') {
                if ($this->ion_auth->logged_in() && $this->Owner || $this->GP['bulk_actions']) {
                    $this->auth_model->ungenerate($id, $month, $year);
                    $this->site->deleteAccPayroll('Payroll', $param);
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function generate($param = null)
    {
        $this->bpas->checkPermissions('index');
        $this->form_validation->set_rules('net_salary', lang('net_salary'), 'required');

        if($param) {
            $arr = explode("__", $param);
            $id = $arr[0];
            $month = $arr[1];
            $year = $arr[2];
        }

        if ($this->form_validation->run() == false) {
            if ($this->input->post('generate')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $user            = $this->site->getUser($id);
                $comm            = $this->auth_model->getUserCommissionByID_M_Y($id, $month, $year);
                $total_allowance = $total_deduction = $leave_deduction = $tax = 0;
                $overtime =0;$loan =0;
                $net_salary      = $user->basic_salary + $total_allowance + $total_deduction + $leave_deduction + (isset($comm->commission) ? $comm->commission : 0);
                $biller_id       = $user->biller_id ? $user->biller_id : $this->accounting_setting->biller_id;
                $data = array(
                    'staff_id'          => $user->id,
                    'basic'             => $user->basic_salary,
                    'total_allowance'   => $total_allowance,
                    'total_deduction'   => $total_deduction,
                    'leave_deduction'   => $leave_deduction,
                    'tax'               => $tax,
                    'commission'        => !empty($comm) ? $comm->commission : 0,
                    'overtime'          => $overtime,
                    'loan'              => $loan,
                    'net_salary'        => $net_salary,
                    'month'             => $month,
                    'year'              => $year,
                    'biller_id' => $biller_id,
                );

                $this->data['data']     = $data;
                $this->data['staff']    = $user;
                $this->data['billers']  = $this->site->getAllCompanies('biller');
                // $this->data['user']     = $this->auth_model->getStaffPaySlipByID_M_Y($id, $month, $year);
                $this->data['modal_js'] = $this->site->modal_js();
                $this->load->view($this->theme . 'payroll/generate_user', $this->data);
            }
        } else {
            $biller_id = $this->input->post('biller');
            $user            = $this->site->getUser($id);
            $date = $this->bpas->convertMonthToLatang($month,$year);
            if ($this->ion_auth->logged_in() && $this->Owner || $this->GP['bulk_actions']) {
                $data = array(
                    'staff_id'          => $id,
                    'biller_id'         => $this->input->post('biller'),
                    'basic'             => $this->input->post('basic_salary'),
                    'total_allowance'   => $this->input->post('total_allowance'),
                    'total_deduction'   => $this->input->post('total_deduction'),
                    'leave_deduction'   => $this->input->post('leave_deduction'),
                    'tax'               => $this->input->post('tax'),
                    'commission'        => $this->input->post('commission'),
                    'overtime'          => $this->input->post('overtime'),
                    'loan'              => $this->input->post('loan'),
                    'net_salary'        => $this->input->post('net_salary'),
                    'status'            => '0',
                    'month'             => $month,
                    'year'              => $year,
                    'payment_date'      => null,
                );

                $this->db->insert('staff_payslip', $data);
                $payroll_id = $this->db->insert_id();
                //=======add acounting=========//
                if($this->Settings->accounting == 1){

                    $reference = '';//$this->site->getReference('payroll');
                    $salary_payable = $this->accounting_setting->default_payable;
                    $commission = $this->input->post('commission');
                    $account_commission = $this->accounting_setting->payroll_commission;
                    $payroll_account = $this->accounting_setting->default_payroll;

                    $accTrans[] = array(
                        'tran_type' => 'Payroll',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $payroll_account,
                        'amount' => $this->input->post('basic_salary'),
                        'narrative' => $this->site->getAccountName($payroll_account),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payroll_id' => $param,
                        'activity_type' => $this->site->get_activity($payroll_account)
                    );
                    $accTrans[] = array(
                        'tran_type' => 'Payroll',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $salary_payable,
                        'amount' => -($this->input->post('basic_salary')),
                        'narrative' => $this->site->getAccountName($salary_payable),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payroll_id' => $param,
                        'activity_type' => $this->site->get_activity($salary_payable)
                    );

                    if($commission >0){
                        $accTrans[] = array(
                            'tran_type' => 'Payroll',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $account_commission,
                            'amount' => $this->input->post('commission'),
                            'narrative' => $this->site->getAccountName($account_commission),
                            'biller_id' => $biller_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'payroll_id' => $param,
                            'activity_type' => $this->site->get_activity($account_commission)
                        );
                        $accTrans[] = array(
                            'tran_type' => 'Payroll',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->commissionPayable,
                            'amount' => - $this->input->post('commission'),
                            'narrative' => $this->site->getAccountName($this->commissionPayable),
                            'biller_id' => $biller_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'payroll_id' => $param,
                            'activity_type' => $this->site->get_activity($this->commissionPayable)
                        );
                    }

                    foreach($accTrans as $accTran){
                        $accTran['tran_no'] = $payroll_id;
                        $this->db->insert('gl_trans', $accTran);
                    }
                    
                }
                //============end accounting=======//
                $this->session->set_flashdata('message', lang('generate_payslip_successfully'));
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function salary_list()
    {
        $this->bpas->checkPermissions('index');
        $months = array();
        for ($m = 1; $m <= 12; $m++) {
            $mth = date('F', mktime(0, 0, 0, $m, 1, date('Y')));
            $months[$m] = $mth;
        }

        $start_y   = date("Y", strtotime("-1 year"));
        $current_y = date("Y");
        $years[$current_y] = $current_y;
        for($y = 1; $y <= ($current_y - $start_y); $y++){
            $y_ = date("Y", strtotime("" . -$y . " year"));
            $years[$y_] = $y_;
        }

        $this->data["months"] = $months;
        $this->data["years"]  = $years;
        $this->data['groups'] = $this->site->getAllGroup();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('salary_list')]];
        $meta = ['page_title' => lang('salary_list'), 'bc' => $bc];
        $this->page_construct('payroll/salary_list', $meta, $this->data);
    }
    
    public function getSalaryList()
    {
        $this->bpas->checkPermissions('index');
        $group  = $this->input->get('group') ? $this->input->get('group') : null;
        $month  = $this->input->get('month') ? $this->input->get('month') : date('F');
        $year   = $this->input->get('year') ? $this->input->get('year') : date('Y');
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;

        $this->load->library('datatables');
        $this->datatables
        ->select(
            $this->db->dbprefix('users') . '.id as id, 
            CONCAT_WS(" ", first_name, last_name) as uname, 
            gender, phone, email, 
            ' . $this->db->dbprefix('groups') . '.name, 
            ' . $this->db->dbprefix('staff_payslip') . '.net_salary,
            CONCAT_WS(", ", month, year),
            CONCAT(' . $this->db->dbprefix('staff_payslip') . '.status, "___", ' . $this->db->dbprefix('users') . '.id, "__", "'.$month.'", "__", "'.$year.'") as gen')
        ->from('users')
        ->join('groups', 'users.group_id  = groups.id', 'left')
        ->join('staff_payslip', 'users.id = staff_payslip.staff_id', 'left')
        ->group_by('users.id');

        if (!$this->Owner && !$this->Admin) {
            $this->datatables->where("users.biller_id",$this->session->userdata('biller_id'));
        }
        if($group){
            $this->datatables->where('users.group_id', $group);
        }
        if($biller){
            $this->datatables->where('users.biller_id', $biller);
        }
        if($month){
            $this->datatables->where('staff_payslip.month', $month);
        }
        if($year){
            $this->datatables->where('staff_payslip.year', $year);
        }

        echo $this->datatables->generate();
    }

    public function paid_staff_payslip($param = null)
    {
        if($param) {
            $arr = explode("__", $param);
            $id = $arr[0];
            $month = $arr[1];
            $year = $arr[2];


            if ($this->db->update('staff_payslip', ['status' => 1, 'payment_date' => date('Y/m/d H:i:s', time())], ['staff_id' => $id, 'month' => $month, 'year' => $year])) {

                $staff_payslip = $this->payrolls_model->getPayrollByParam($id,$month,$year);
                $biller_id= $staff_payslip->biller_id;
                $date = date('Y-m-d H:i:s');
                //=======add acounting=========//
                if($this->Settings->accounting == 1){
                    $tran_no = $this->accounts_model->getTranNo();
                    $param = $id.'__'.$month.'__'.$year;
                    $reference = '';//$this->site->getReference('payroll');
                    $salary_paid = $this->accounting_setting->default_cash;
                    $accTrans[] = array(
                        'tran_type' => 'Payroll',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $salary_paid,
                        'amount' => -($staff_payslip->basic),
                        'narrative' => $this->site->getAccountName($salary_paid),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payroll_id' => $param,
                        'activity_type' => $this->site->get_activity($salary_paid)
                    );

                    $accTrans[] = array(
                        'tran_type' => 'Payroll',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_payable,
                        'amount' => $staff_payslip->basic,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_payable),
                        'biller_id' => $biller_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payroll_id' => $param,
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                    );
                    //-------
                    if($staff_payslip->commission>0){
                        $accTrans[] = array(
                            'tran_type' => 'Payroll',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $salary_paid,
                            'amount' => -($staff_payslip->commission),
                            'narrative' => $this->site->getAccountName($salary_paid),
                            'biller_id' => $biller_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'payroll_id' => $param,
                            'activity_type' => $this->site->get_activity($staff_payslip->commission)
                        );
                        $accTrans[] = array(
                            'tran_type' => 'Payroll',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->commissionPayable,
                            'amount' => $staff_payslip->commission,
                            'narrative' => $this->site->getAccountName($this->commissionPayable),
                            'biller_id' => $biller_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'payroll_id' => $param,
                            'activity_type' => $this->site->get_activity($this->commissionPayable)
                        );
                    }
                    foreach($accTrans as $accTran){
                        $accTran['tran_no'] = $tran_no;
                        $this->db->insert('gl_trans', $accTran);
                    }
                    
                }
                //============end accounting=======//

                $this->session->set_flashdata('message', lang('paid_successful'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }

        redirect($_SERVER['HTTP_REFERER']);
    }

    public function salary_list_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                $month = $this->input->get('month') ? $this->input->get('month') : date('F');
                $year  = $this->input->get('year') ? $this->input->get('year') : date('Y');
                if ($this->input->post('form_action') == 'generate') {
                    $this->session->set_flashdata('message', lang('generate_payslip'));
                    redirect($_SERVER['HTTP_REFERER']);

                } elseif ($this->input->post('form_action') == 'combine') {

                    $generated = false;
                    foreach ($_POST['val'] as $id) {
                        $b = false;
                        $staff_payslip   = $this->auth_model->getAllStaffPaySlipByID($id);
                        if($staff_payslip !== false){
                            foreach($staff_payslip as $st_ps){
                                if($st_ps->month == $month && $st_ps->year == $year && $st_ps->status == 1){
                                    $b = true;
                                    break;
                                }
                            }
                        }
                        if($b == true){ continue; }
                        if ($this->db->update('staff_payslip', ['status' => 1, 'payment_date' => date('Y/m/d H:i:s', time())], ['staff_id' => $id, 'month' => $month, 'year' => $year])) {
                            
                            $staff_payslip = $this->payrolls_model->getPayrollByParam($id,$month,$year);
                            $biller_id= $user->biller_id ? $user->biller_id : $this->accounting_setting->biller_id; //$staff_payslip->biller_id;
                            $date = date('Y-m-d H:i:s');
                            //=======add acounting=========//
                            if($this->Settings->accounting == 1){
                                $tran_no = $this->accounts_model->getTranNo();
                                $param = $id.'__'.$month.'__'.$year;
                                $reference = '';//$this->site->getReference('payroll');
                                $salary_paid = $this->accounting_setting->default_cash;
                                $accTrans = array(
                                    'tran_type' => 'Payroll',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $salary_paid,
                                    'amount' => -($staff_payslip->basic),
                                    'narrative' => $this->site->getAccountName($salary_paid),
                                    'biller_id' => $biller_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                    'payroll_id' => $param,
                                    'activity_type' => $this->site->get_activity($salary_paid)
                                );

                                $accTrans1 = array(
                                    'tran_type' => 'Payroll',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $this->accounting_setting->default_payable,
                                    'amount' => $staff_payslip->basic,
                                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_payable),
                                    'biller_id' => $biller_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                    'payroll_id' => $param,
                                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_payable)
                                );
                                //-------
                                $accTran['tran_no'] = $tran_no;
                                $this->db->insert('gl_trans', $accTrans);
                                $this->db->insert('gl_trans', $accTrans1);

                                if($staff_payslip->commission>0){
                                    $accTrans3 = array(
                                        'tran_type' => 'Payroll',
                                        'tran_date' => $date,
                                        'reference_no' => $reference,
                                        'account_code' => $salary_paid,
                                        'amount' => -($staff_payslip->commission),
                                        'narrative' => $this->site->getAccountName($salary_paid),
                                        'biller_id' => $biller_id,
                                        'created_by'  => $this->session->userdata('user_id'),
                                        'payroll_id' => $param,
                                        'activity_type' => $this->site->get_activity($salary_paid)
                                    );
                                    $accTrans4 = array(
                                        'tran_type' => 'Payroll',
                                        'tran_date' => $date,
                                        'reference_no' => $reference,
                                        'account_code' => $this->commissionPayable,
                                        'amount' => $staff_payslip->commission,
                                        'narrative' => $this->site->getAccountName($this->commissionPayable),
                                        'biller_id' => $biller_id,
                                        'created_by'  => $this->session->userdata('user_id'),
                                        'payroll_id' => $param,
                                        'activity_type' => $this->site->get_activity($this->commissionPayable)
                                    );

                                    $this->db->insert('gl_trans', $accTrans3);
                                    $this->db->insert('gl_trans', $accTrans4);
                                }  
                            }
                            //============end accounting=======//

                            $generated = true;
                        }
                    }
                    if ($generated) {
                        $this->session->set_flashdata('message', lang('paid_successful'));
                    } else {
                        $this->session->set_flashdata('error', lang('generate_payment_already'));
                    }
                    redirect($_SERVER['HTTP_REFERER']);

                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('salary_list'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('username'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('gender'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('group'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('monthly_payslip'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('salary_month'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('payment_status'));

                    $row = 2;
                    $pmt = ['0' => 'unpaid',   '1' => 'paid'];
                    foreach ($_POST['val'] as $id) {
                        $user       = $this->site->getUser($id);
                        $group      = $this->site->getUserGroup($user->id);
                        $st_payslip = $this->auth_model->getStaffPaySlipByID_M_Y($user->id, $month, $year);

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $user->first_name . ' ' . $user->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $user->gender);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $user->phone);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $user->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $group->name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, !empty($st_payslip) ? $st_payslip->net_salary : '');
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, !empty($st_payslip) ? $st_payslip->month . ' ' . $st_payslip->year : '');
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, !empty($st_payslip) ? $pmt[$st_payslip->status] : '');
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'salary_list_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_user_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
