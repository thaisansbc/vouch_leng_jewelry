<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Welcome extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->session->unset_userdata('module');
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        if ($this->Customer || $this->Supplier) {
            redirect('/');
        }
        $this->load->admin_model('loan_model');
        $this->load->admin_model('reports_model');
        $this->load->admin_model('settings_model');
        $this->load->library('form_validation');
        $this->load->admin_model('db_model');
        $this->load->admin_model('attendances_model');
        $this->load->helper('widget');
        $this->load->helper('date_time');
        
    }

    public function index($start_date = null, $end_date = null)
    {     
        if (!$start_date) {
            $start      = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end      = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $sales = null; // $this->reports_model->getAllSales($start, $end);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['total_products']               = $this->reports_model->getTotalProducts();
        $this->data['total_property']               = $this->reports_model->getTotalProperties();
        $this->data['total_purchases']              = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales']                  = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_status']                 = $this->reports_model->getTotalStatus($start, $end);
        $this->data['total_return_sales']           = $this->reports_model->getTotalReturnSales($start, $end);
        $this->data['total_users']                  = $this->reports_model->getTotalUsers($start, $end);
        $this->data['total_discounts']              = $this->reports_model->getTotaldiscounts($start, $end);
        $this->data['total_expenses']               = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid']                   = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received']               = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash']          = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc']            = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque']        = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp']           = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe']        = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['gettop10sale']                 = $this->reports_model->getTop10Sale();
        $this->data['getallexpenses']               = $this->reports_model->getAllExpenses();
        $this->data['getallwarehousesproducts']     = $this->reports_model->getStockWarehouse();
        $this->data['total_received_currencies']    = $this->reports_model->getTotalReceivedByCurrencyAmount($start, $end);
        $this->data['total_returned']               = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start']                        = urldecode($start_date);
        $this->data['end']                          = urldecode($end_date);
        $this->data['Categories']                   = $this->reports_model->getExpenseCategories();
        $this->data['user']                         = $this->site->getUser();
        if ($this->Settings->module_hr) {
            $this->data['total_employees']             = $this->reports_model->getTotalEmployees($start, $end);
            $this->data['total_employees_department']  = $this->reports_model->getTotalEmployeesDepartment($start, $end);
            $this->data['total_employees_position']    = $this->reports_model->getTotalEmployeesPosition($start, $end);
            $this->data['employee']                    = $this->reports_model->getEmployeeByCode($this->data['user']->emp_code);
            if ($this->data['employee']) {
                $this->data['employees_working_info']      = $this->reports_model->getEmployeesWorkingInfoByEmployeeID($this->data['employee']->id);
                $this->data['employees_policy']            = $this->reports_model->getPolicyByEmployeeID($this->data['employee']->id);
                $this->data['attendances']                 = $this->site->getCheck_in_out_ByIDwithPolicies($this->session->userdata('user_id'));
            }
        }
        if($this->Settings->module_crm){
            $this->data['lead_status']                         = $this->site->get_lead_status();
            $this->data['source_lead']                         = $this->site->get_source_lead();
        }
        $pro_cost = 0;
        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $pro = $this->site->getProductByID($sale->product_id);
                $pro_cost += 0; //$this->bpas->formatMoney($pro->cost*$sale->quantity);
            }
        }
        $this->data['product_cost'] = $pro_cost;
        $warehouses = $this->site->getAllWarehouses();
        if (!empty($warehouses)) {
            foreach ($warehouses as $warehouse) {
                $total_purchases        = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
                $total_sales            = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
                $total_returns          = $this->reports_model->getTotalReturnSales($start, $end, $warehouse->id);
                $total_expenses         = $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
                $total_cost             = $this->reports_model->getTotalCost($start, $end, $warehouse->id);
                $total_return_warehouse = $this->reports_model->getTotalReturnedwarehouse($start, $end,$warehouse->id);
                $total_discounts        = $this->reports_model->getTotaldiscounts($start, $end,$warehouse->id);
                $warehouses_report[]    = [
                    'warehouse'         => $warehouse,
                    'total_purchases'   => $total_purchases,
                    'total_sales'       => $total_sales,
                    'total_returns'     => $total_returns,
                    'total_expenses'    => $total_expenses,
                    'total_discounts'   => $total_discounts->total_amount,
                    'total_returned'    => $total_return_warehouse->total_return,   
                    'total_cost'        => $total_cost->cost
                ];
            }
        }
        if (!empty($warehouses_report)) {
            $this->data['warehouses_report'] = $warehouses_report;
        }
        
        if($this->Settings->module_loan){
            $status = 'alert';
            $rows = $this->loan_model->getAllScheduleByUser($status,$start, $end);
            $this->data['rows'] = $rows;
        }
        $this->data['chatData']  = $this->db_model->getChartData();
        $this->data['bs']        = $this->db_model->getBestSeller();
        $lmsdate                 = date('Y-m-d', strtotime('first day of last month')) . ' 00:00:00';
        $lmedate                 = date('Y-m-d', strtotime('last day of last month')) . ' 23:59:59';
        $this->data['lmbs']      = $this->db_model->getBestSeller($lmsdate, $lmedate);
        $bc    = [['link' => '#', 'page' => lang('dashboard')]];

        if ($this->Settings->ui == 'full'){
            $meta   = ['page_view'=>'full','page_title' => lang('dashboard'), 'bc' => $bc];
            $this->page_construct('all_modules', $meta, $this->data);
        } elseif ($this->Settings->ui == 'module_property'){
            $meta   = ['page_title' => lang('dashboard'), 'bc' => $bc];
            $this->page_construct('dashboard_property', $meta, $this->data);
        } else {
            $meta   = ['page_title' => lang('dashboard'), 'bc' => $bc];
            $this->page_construct('dashboard', $meta, $this->data);
        }
    }
    public function download($file)
    {
        if (file_exists('./files/' . $file)) {
            $this->load->helper('download');
            force_download('./files/' . $file, null);
            exit();
        }
        $this->session->set_flashdata('error', lang('file_x_exist'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function hideNotification($id = null)
    {
        $this->session->set_userdata('hidden' . $id, 1);
        echo true;
    }

    public function image_upload()
    {
        if (DEMO) {
            $error = ['error' => $this->lang->line('disabled_in_demo')];
            $this->bpas->send_json($error);
            exit;
        }
        $this->security->csrf_verify();
        if (isset($_FILES['file'])) {
            $this->load->library('upload');
            $config['upload_path']   = 'assets/uploads/';
            $config['allowed_types'] = 'gif|jpg|png|jpeg';
            $config['max_size']      = '500';
            $config['max_width']     = $this->Settings->iwidth;
            $config['max_height']    = $this->Settings->iheight;
            $config['encrypt_name']  = true;
            $config['overwrite']     = false;
            $config['max_filename']  = 25;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                $error = $this->upload->display_errors();
                $error = ['error' => $error];
                $this->bpas->send_json($error);
                exit;
            }
            $photo = $this->upload->file_name;
            $array = [
                'filelink' => base_url() . 'assets/uploads/images/' . $photo,
            ];
            echo stripslashes(json_encode($array));
            exit;
        } else {
            $error = ['error' => 'No file selected to upload!'];
            $this->bpas->send_json($error);
            exit;
        }
    }
    	// get department > employee > chart
	public function employee_department()
	{
		/* Define return | here result is used to return user data and error for error message */
		$Return = array('chart_data'=>'', 'c_name'=>'', 'd_rows'=>'','c_color'=>'');
		$c_name = array();
		$c_am = array();	
		$c_color = array('#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC','#00A5A8','#FF4558','#16D39A','#8A2BE2','#D2691E','#6495ED','#DC143C','#006400','#556B2F','#9932CC');
		$someArray = array();
		$j=0;
		foreach($this->Department_model->all_departments() as $department) {
		
			$condition = "department_id =" . "'" . $department->department_id . "'";
			$this->db->select('*');
			$this->db->from('hr_employees');
			$this->db->where($condition);
			$this->db->group_by('location_id');
			$query = $this->db->get();
			$checke  = $query->result();
			// check if department available
			if ($query->num_rows() > 0) {
				$row = $query->num_rows();
				$d_rows [] = $row;	
				$c_name[] = htmlspecialchars_decode($department->department_name);
		
				$someArray[] = array(
				  'label'   => htmlspecialchars_decode($department->department_name),
				  'value' => $row,
				  'bgcolor' => $c_color[$j]
				  );
				  $j++;
			}
		}
		$Return['c_name'] = $c_name;
		$Return['d_rows'] = $d_rows;
		$Return['chart_data'] = $someArray;
		$this->output($Return);
		exit;
	}
	
	// get designation > employee > chart
	public function employee_designation()
	{
		/* Define return | here result is used to return user data and error for error message */
		$Return = array('chart_data'=>'', 'c_name'=>'', 'd_rows'=>'','c_color'=>'');
		$c_name = array();
		$c_am = array();	
		$c_color = array('#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED','#9932CC','#556B2F','#16D39A','#DC143C','#D2691E','#8A2BE2','#FF976A','#FF4558','#00A5A8','#6495ED');
		$someArray = array();
		$j=0;
		foreach($this->Designation_model->all_designations() as $designation) {
		
			$condition = "designation_id =" . "'" . $designation->designation_id . "'";
			$this->db->select('*');
			$this->db->from('hr_employees');
			$this->db->where($condition);
			$this->db->group_by('location_id');
			$query = $this->db->get();
			$checke  = $query->result();
			// check if department available
			if ($query->num_rows() > 0) {
				$row = $query->num_rows();
				$d_rows [] = $row;	
				$c_name[] = htmlspecialchars_decode($designation->designation_name);
				$someArray[] = array(
				  'label'   => htmlspecialchars_decode($designation->designation_name),
				  'value' => $row,
				  'bgcolor' => $c_color[$j]
				  );
				  $j++;
			}
		}
		$Return['c_name'] = $c_name;
		$Return['d_rows'] = $row;
		$Return['chart_data'] = $someArray;
		$this->output($Return);
		exit;
	}

    //===========end===========//
    

    public function language($lang = false)
    {
        if ($this->input->get('lang')) {
            $lang = $this->input->get('lang');
        }
        $data = [
            'language' =>  $lang,
        ];
        $this->db->update('users', $data, array('id' => $this->session->userdata('user_id')));
        //$this->settings_model->updateSetting($data);
        //$this->load->helper('cookie');
        $folder        = 'bpas/language/';
        $languagefiles = scandir($folder);
        if (in_array($lang, $languagefiles)) {
            $cookie = [
                'name'   => 'language',
                'value'  => $lang,
                'expire' => '31536000',
                'prefix' => 'bpas_',
                'secure' => false,
            ];
            $this->input->set_cookie($cookie);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function promotions()
    {
        $this->load->view($this->theme . 'promotions', $this->data);
    }

    public function set_data($ud, $value)
    {
        $this->session->set_userdata($ud, $value);
        echo true;
    }

    public function slug()
    {
        echo $this->bpas->slug($this->input->get('title', true), $this->input->get('type', true));
        exit();
    }

    public function toggle_rtl()
    {
        $cookie = [
            'name'   => 'rtl_support',
            'value'  => $this->Settings->user_rtl == 1 ? 0 : 1,
            'expire' => '31536000',
            'prefix' => 'bpas_',
            'secure' => false,
        ];
        $this->input->set_cookie($cookie);
        redirect($_SERVER['HTTP_REFERER']);
    }
    function checking_module($modules){
        

        $Getmodule = $this->site->getModuleByID($modules);
        $this->session->set_userdata('module',$Getmodule->name);
        $module = $this->session->userdata('module');

        echo $Getmodule->controller;

        // }elseif($modules == 'accounting'){
        //     echo 'account/listJournal';
        // }elseif($modules == 'crm'){
        //     echo 'leads';
        // }elseif($modules == 'hr'){
        //     echo 'hr';
        // }elseif($modules == 'attendance'){
        //     echo 'attendances';
        // }elseif($modules == 'payroll'){
        //     echo 'payrolls';
        // }elseif($modules == 'project'){
        //     echo 'projects';
        // }elseif($modules == 'manufaturing'){
        //     echo 'workorder';
        // }elseif($modules == 'property'){
        //     echo 'property';
        // }elseif($modules == 'clinic'){
        //     echo '';
        // }elseif($modules == 'school'){
        //     echo '';
        // }elseif($modules == 'ecommerce'){
        //     echo 'ecommerce';
        // }elseif($modules == 'hotel_apartment'){
        //     echo 'room';
        // }elseif($modules == 'email_marketing'){
        //     echo '';
        // }
    }
    public function getMoneysCountDataTableReport($pdf = null, $xls = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $status           = $this->input->get('status') ? $this->input->get('status') : null;
        $user           = $this->input->get('user') ? $this->input->get('user') : null;
        $supplier       = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $customer       = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller         = $this->input->get('biller') ? $this->input->get('biller') : null;
        $payment_ref    = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : null;
        $paid_by        = $this->input->get('paid_by') ? $this->input->get('paid_by') : null;
        $sale_ref       = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : null;
        $purchase_ref   = $this->input->get('purchase_ref') ? $this->input->get('purchase_ref') : null;
        $card           = $this->input->get('card') ? $this->input->get('card') : null;
        $cheque         = $this->input->get('cheque') ? $this->input->get('cheque') : null;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : null;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date   = $this->bpas->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {
           $this->db
            ->select("{$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.image as image, 
                {$this->db->dbprefix('products')}.code as code,
                {$this->db->dbprefix('products')}.name as proname, 
                {$this->db->dbprefix('sales')}.customer as name, 
                {$this->db->dbprefix('brands')}.name as brand, 
                {$this->db->dbprefix('categories')}.name as cname, 
                {$this->db->dbprefix('products')}.cost as cost, 
                {$this->db->dbprefix('products')}.price as price,
                {$this->db->dbprefix('sales')}.paid as paid,
                ({$this->db->dbprefix('products')}.price - {$this->db->dbprefix('sales')}.paid) as balance,
                {$this->db->dbprefix('products')}.quantity as quantity,
                payment_status")
            ->from('products')
            ->join('booking', 'booking.product_id = products.id', 'left')
            ->join('sale_items', 'sale_items.product_id = products.id', 'left')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->where('products.module_type', 'property')
            ->where('products.quantity', $status);
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
            // var_dump($status);
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('property'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('id'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('image'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('code'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('brand'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('product_cost'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('product_price'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('M1', lang('payment_status'));
                $row   = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->id);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->image);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->proname);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->brand);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->cname);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->cost);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->price);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->balance);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, lang($data_row->quantity));
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->payment_status);
                        $total += $data_row->price;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('F' . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $filename = 'payments_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
            ->select("{$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.image as image, 
                {$this->db->dbprefix('products')}.code as code,
                {$this->db->dbprefix('products')}.name as proname, 
                {$this->db->dbprefix('sales')}.customer as name, 
                {$this->db->dbprefix('brands')}.name as brand, 
                {$this->db->dbprefix('categories')}.name as cname, 
                {$this->db->dbprefix('products')}.cost as cost, 
                {$this->db->dbprefix('products')}.price as price,
                {$this->db->dbprefix('sales')}.paid as paid,
                ({$this->db->dbprefix('products')}.price - {$this->db->dbprefix('sales')}.paid) as balance,
                {$this->db->dbprefix('products')}.quantity as quantity,
                payment_status")
            ->from('products')
            ->join('booking', 'booking.product_id = products.id', 'left')
            ->join('sale_items', 'sale_items.product_id = products.id', 'left')
            ->join('sales', 'sales.id = sale_items.sale_id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->join('categories', 'products.category_id=categories.id', 'left')
            ->where('products.module_type', 'property')
            ->where('products.quantity', $status);
        // $q = $this->db->get();
       if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($start_date) {
                $this->datatables->where('FSJ.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }
}
