<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Projects extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->load->library("pagination");
        $this->lang->admin_load('products', $this->Settings->user_language);       
        $this->load->library('form_validation');
        $this->load->admin_model('projects_model');
        $this->load->admin_model('settings_model');
        $this->load->admin_model('purchases_request_model');
        $this->load->admin_model('purchases_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/tasks/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '2048';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'bpas_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
    }
    function index($page=''){   
        $this->bpas->checkPermissions();

        $id_s = $this->input->post('start_date');
        $id_e = $this->input->post('end_date');
        $id_u = $this->input->post('user');
        
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            // $this->data['warehouse_id'] = $warehouse_id;
            // $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            // $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            // $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $config = array();
        $rows_per_page = $this->settings_model->getSettings();
        $per_page = $rows_per_page->rows_per_page;
        $cur_page = 0;
        if (isset($page) && trim($page) != '') {
            $cur_page=$page;
        }

        //$warehouse_id = $warehouse_id ? 'index/'.$warehouse_id : '';
        $config["base_url"] = admin_url("projects/index");
        if ($this->Owner || $this->Admin) {
            $config["total_rows"] = $this->projects_model->countProjects(null);
        } else {
            $config["total_rows"] = $this->projects_model->countProjects($this->session->userdata('user_id'));
        }
        $config["per_page"] = $per_page;
        $config["uri_segment"] = 4;
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['attributes'] = ['class' => 'page-link'];
        $config['first_link'] = false;
        $config['last_link'] = false;
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
        $config['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $this->pagination->initialize($config);
        $user_id =  $this->session->userdata('user_id');
        $this->data['links'] = $this->pagination->create_links(); 
        $this->data['task_progress'] = $this->projects_model->get_task_progress(null);
        $this->data['getprojects']         = $this->site->getAllProject();
        $this->data['clients'] = $this->site->getAllCompanies('customer');
        if ($this->Owner || $this->Admin) {
            $this->data['projects'] = $this->projects_model->getAllProjects($per_page,$cur_page,null);
            $this->data['users'] = $this->site->getAllUser();
            $id = $this->input->post('project');
            $id_c = $this->input->post('client');
            $id_w = $this->input->post('warehouse');
            $id_user = $this->input->post('user');
            $start_date = $this->input->post('start_date');
            $end_date = $this->input->post('end_date');
            // var_dump($start_date);
            if( $id != NULL  || $id_c != 0 || $id != '' || $id_user != "" || $start_date != "" || $end_date !="" || $id_w != "" ){
                if ($id != NULL) {
                    $project_id = ('project_id=' . $id);
                } else {
                    $project_id = "1";
                }
                if ($id_user != "") {
                    $user_id = ('FIND_IN_SET( '."$id_user".', `customer_id` )');
                } else {
                    $user_id = "1";
                }
                if ($id_c != '' || $id_c != NULL) {
                    $client_id = ('clients_id = ' . $id_c);
                } else {
                    $client_id = "1";
                }
                if($end_date != NULL){
                    $date = str_replace('/', '-', $end_date);
                    $newDate = date("Y-m-d", strtotime($date)); 
                    $date_e = ('end_date <= ' . "'$newDate:00'");
                }else{
                    $date_e = "1";
                }
                if ($start_date != NULL) {
                    $date = str_replace('/', '-', $start_date);
                   $newDate = date("Y-m-d", strtotime($date)); 
                    $date_s = ('start_date >= ' . "'$newDate:00'" );
                } else {
                    $date_s ="1";
                }
                if ($id_w != NULL) {
                    $warehouse_id = ('warehouse_id = ' . $id_w);
                } else {
                    $warehouse_id = "1";
                }
                $where = ($client_id. ' AND ' . $project_id . ' AND ' .$user_id. ' AND ' .$date_s. ' AND ' .$date_e . ' AND ' . $warehouse_id);
                $config = array();
                $rows_per_page = $this->settings_model->getSettings();
                $per_page = $rows_per_page->rows_per_page;
                $cur_page = 0;
                if (isset($page) && trim($page) != '') {
                    $cur_page = $page;
                }
                //$warehouse_id = $warehouse_id ? 'index/'.$warehouse_id : '';
                $config["base_url"] = admin_url("projects/index");
                if ($this->Owner || $this->Admin) {
                    $config["total_rows"] = $this->projects_model->countProjects1(null);
                } else {
                    $config["total_rows"] = $this->projects_model->countProjects1($this->session->userdata('user_id'));
                }
                $config["per_page"] = $per_page;
                $config["uri_segment"] = 4;
                $config['full_tag_open'] = '<ul class="pagination">';
                $config['full_tag_close'] = '</ul>';
                $config['attributes'] = ['class' => 'page-link'];
                $config['first_link'] = false;
                $config['last_link'] = false;
                $config['first_tag_open'] = '<li class="page-item">';
                $config['first_tag_close'] = '</li>';
                $config['prev_link'] = '&laquo';
                $config['prev_tag_open'] = '<li class="page-item">';
                $config['prev_tag_close'] = '</li>';
                $config['next_link'] = '&raquo';
                $config['next_tag_open'] = '<li class="page-item">';
                $config['next_tag_close'] = '</li>';
                $config['last_tag_open'] = '<li class="page-item">';
                $config['last_tag_close'] = '</li>';
                $config['cur_tag_open'] = '<li class="page-item active"><a href="#" class="page-link">';
                $config['cur_tag_close'] = '<span class="sr-only">(current)</span></a></li>';
                $config['num_tag_open'] = '<li class="page-item">';
                $config['num_tag_close'] = '</li>';
                $this->pagination->initialize($config);
                $user_id =  $this->session->userdata('user_id');
                $this->data['links'] = $this->pagination->create_links(); 
                $this->data['projects'] = $this->projects_model->getAllProjects1($per_page,$cur_page,null,$where);
           
            }
        } else {
            $this->data['projects'] = $this->projects_model->getAllProjects($per_page,$cur_page,$user_id);
            $this->data['users'] = $this->site->getAllUser();
        }
            $bc = array(
            array('link' => base_url($config["base_url"]), 'page' => lang('home')), 
            array('link' => $config["base_url"], 'page' => lang('products')), 
            array('link' => '#', 'page' => lang('stock_counts')));
            $meta = array('page_title' => lang('project'), 'bc' => $bc,'links' => $this->pagination->create_links());
        
        $this->page_construct('projects/projects_list', $meta, $this->data);
    }
    public function update_status($id){
        $this->form_validation->set_rules('status', lang('sale_status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->bpas->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        }

        if ($this->form_validation->run() == true && $this->projects_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sales');
        } else {
            $this->data['inv']      = $this->projects_model->getProjectByID($id);
            $this->data['returned'] = false;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'projects/update_status', $this->data);
        }
    }

    function project_detail($warehouse_id)
    {   
        $this->bpas->checkPermissions();
        
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            if ($warehouse_id!=null) {
                $this->session->set_userdata('warehouse_id', $warehouse_id);
            } else {
                $this->session->set_userdata('warehouse_id', $warehouse_id);
            }
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        }

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('products')]];
        $meta = ['page_title' => lang('products'), 'bc' => $bc];
        
        $this->page_construct('projects/project_detail', $meta, $this->data);
    }
    function getCountsByWarehouse(){
 
        $this->bpas->checkPermissions('index', true);
        $detail_link       = anchor('admin/projects/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('details'));
        $milestones     = anchor('admin/projects/milestones/$1', '<i class="fa fa-money"></i> ' . lang('Malestone'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_milestone  = anchor('admin/projects/add_milestone/$1', '<i class="fa fa-money"></i> ' . lang('Add_milestore'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_adjustment") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('projects/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>Delete</a>";
        $detail="<a href='" . admin_url('projects/modal_view/$1')."'  data-toggle='modal' data-target='#myModal'><i class='fa fa-file-text-o'></i> Detail</a>";
        $edit_link="<a href='" . admin_url('projects/edit/$1')."' class='tip' title='" . lang("edit_room") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i>Edit</a>";
         $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
       
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
        $user_id = $this->session->userdata('user_id');
        $warehouse_id = $this->session->userdata('warehouse_id');
        $show = "<a href='" . admin_url('projects/show/$1')."' class='tip btn btn-success btn-xs' title='" . lang("user_access") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i> Show</a>";
    
        $this->load->library('datatables');
        if ($this->Owner||$this->Admin) {
            if ($warehouse_id==null) {
                $this->datatables
                    ->select("project_id as id,project_code,project_name,warehouses.name,description")
                    ->from('projects')
                    ->join('warehouses', 'projects.warehouse_id = warehouses.id');
            } else {
                $this->datatables
                    ->select("project_id as id,project_code,project_name,warehouses.name,description")
                    ->from('projects')
                    ->join('warehouses', 'projects.warehouse_id = warehouses.id');
                    $this->datatables->where('projects.warehouse_id', $warehouse_id);
            }
        } else {
            $this->datatables
                ->select("project_id as id,project_code,project_name,warehouses.name,description")
                ->from('projects')
                ->join('warehouses', 'projects.warehouse_id = warehouses.id');
        }
        $this->datatables->add_column('Shows',$show , "id");
        $this->datatables->add_column('Actions',$action , "id");
        echo $this->datatables->generate();
    }
    public function modal_view($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['rows'] = $this->projects_model->getProjectByID($id);
        //$this->data['rows'] = $this->projects_model->getProjectByID($id);
        $this->load->view($this->theme . 'projects/modal_view', $this->data);
    }
    function add()
    {
        $this->bpas->checkPermissions('add', true);

        $this->form_validation->set_rules('name', lang("title"), 'trim|required');
        $this->form_validation->set_rules('style_code', lang("Style Code"), 'trim');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim');
        $this->form_validation->set_rules('target', lang("target"), 'trim|is_numeric');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'trim');
        //$this->form_validation->set_rules('user', lang("user"), 'trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $user = $this->input->post('user');
            var_dump($user);
            $users_id = '';
            $i = 1;
            foreach($user as $value){
                if(count($user)==$i){
                    $users_id .= $value;
                }else{
                    $users_id .= $value.',';
                }
                $i++;
            }
            $data = array(
                'date'          => $date,
                'user_id'       => $this->session->userdata('user_id'),
                'project_name'  => $this->input->post('name'),
                'project_code'  => $this->input->post('style_code'),
                'customer_id'   => $users_id,
                'clients_id'    =>  $this->input->post('client'),
                'biller_id'     => $this->input->post('biller_id'),
                'start_date'    =>  $this->bpas->fld(trim($this->input->post('start_date'))),
                'end_date'      => $this->bpas->fld(trim($this->input->post('end_date'))),
                'target'        => $this->input->post('target'),
                'description'   => $this->input->post('description')
            );      
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->projects_model->add_style($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("Projects_has_been_added"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("add_style");
            $this->data['customers'] = lang("add_style");
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['clients'] = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['users'] = $this->site->getAllUser();
            $this->load->view($this->theme . 'projects/add', $this->data);
        }
    }
    function edit($id = NULL)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->form_validation->set_rules('name', lang("title"), 'trim|required');
        $this->form_validation->set_rules('style_code', lang("Style Code"), 'trim');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim');
        $this->form_validation->set_rules('target', lang("target"), 'trim');
        //$this->form_validation->set_rules('customer', lang("customer"), 'trim');
        $this->form_validation->set_rules('description', lang("description"), 'trim');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $user = $this->input->post('user');
            $users_id = '';
            $i = 1;
            foreach($user as $value){
                if(count($user)==$i){
                    $users_id .= $value;
                }else{
                    $users_id .= $value.',';
                }
                $i++;
                
            }
                // var_dump($this->session->userdata('user_id'));
            $data = array(
                'date'          => $date,
                'user_id'       => $this->session->userdata('user_id'),
                'project_name'  => $this->input->post('name'),
                'project_code'  => $this->input->post('style_code'),
                'customer_id'   => $users_id ? $users_id:null,
                'clients_id'    =>  $this->input->post('client'),
                'biller_id'     => $this->input->post('biller_id'),
                'start_date'    =>  $this->bpas->fld(trim($this->input->post('start_date'))),
                'end_date'      => $this->bpas->fld(trim($this->input->post('end_date'))),
                'target'        => $this->input->post('target'),
                'description'   => $this->input->post('description'),
                'status'        => $this->input->post('status')
            );
            // var_dump($data);
            // exit();
        } elseif ($this->input->post('edit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects");
        }

        if ($this->form_validation->run() == true && $this->projects_model->update($id,$data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("Project_has_been_updated"));
            admin_redirect("projects");
            
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['data'] = $this->projects_model->getProjectByID($id);
            $this->data['id'] = $id;           
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("edit_style");
            $this->data['billers']          = $this->site->getAllCompanies('biller');
             $this->data['clients'] = $this->site->getAllCompanies('customer');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['users'] = $this->site->getAllUser();
            $this->load->view($this->theme . 'projects/edit', $this->data);
        }
    }
    function show($id = NULL)
    {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['data'] = $this->projects_model->getProjectByID($id);
        $this->data['id'] = $id;           
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['page_title'] = lang("edit_style");
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['users'] = $this->site->getAllUser();
        $this->load->view($this->theme . 'projects/show', $this->data);
    }
    function styles_actions()//add new
    {
       
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        
        if ($this->form_validation->run() == true) {
        
            if (!empty($_POST['val'])) {
				// var_dump($this->input->post('form_action'));
				// exit();
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->projects_model->delete($id);
                    }
                    $this->session->set_flashdata('message', lang("The Projects has been Deleted!"));
                    admin_redirect("projects");
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    // create file name
                    $fileName = 'Supplier_' . date('Y_m_d_H_i_s') . '.xlsx';
                    // load excel library
                    $this->load->library('excel');
                   $this->excel->setActiveSheetIndex(0);
                   $this->excel->getActiveSheet()->mergeCells('B1:G1')->setCellValue('B1', 'This is Data of Project Table');
                   $this->excel->getActiveSheet()->getStyle('B1:G1')->getFont()->setSize(20);
                   $this->excel->getActiveSheet()->getStyle('B1:G1')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('A2', lang('no'))->getStyle('A2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('B2', lang('client'))->getStyle('B2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('C2', lang('project_name'))->getStyle('C2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('D2', lang('warehouse'))->getStyle('D2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('E2', lang('user_access'))->getStyle('E2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('F2', lang('description'))->getStyle('F2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->SetCellValue('G2', lang('progress'))->getStyle('G2')->getFont()->setBold(true);
                   $this->excel->getActiveSheet()->getStyle('B1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('C2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('E2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('F2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                   $this->excel->getActiveSheet()->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    // set Row
                    $row = 3;
                    $users = $this->site->getAllUser();
                    foreach ($_POST['val'] as $id) {
                        $task_progress = $this->projects_model->get_task_progress($id);
                        $warehouse ="";
                        $sc = $this->projects_model->getProjectByID($id);
                        if ($sc->warehouse_id) {
                            $id_w = $sc->warehouse_id;
                            $pc              = $this->projects_model->select_warehouse($id_w);
                            $warehouse = $pc->name;
                        }
                        $customers = explode(',',
                            $sc->customer_id
                        );
                        $user_name = '';
                        $i = 1;
                        foreach ($customers as $key => $value) {
                            if (count($customers) == $i) {
                                foreach ($users as $key => $user) {
                                    if ($user->id == $value) {
                                        $user_name .= $user->last_name . ' ' . $user->first_name;
                                    }
                                }
                            } else {
                                foreach ($users as $key => $user) {
                                    if ($user->id == $value) {
                                        $user_name .= $user->last_name . ' ' . $user->first_name . ' , ';
                                    }
                                }
                            }
                            $i++;
                        }
                        $client  ="";
                        if ($sc->clients_id) {
                            $id_w = $sc->clients_id;
                            $pc              = $this->projects_model->select_client($id_w);

                            $client = $pc->name; 
                        }
                         
                        $progress = 0;
                        foreach ($task_progress as $pro) {
                            if ($id == $pro->project_id) {
                                $progress = $pro->result / $pro->project;
                            }
                        }
                                    
                      $this->excel->getActiveSheet()->SetCellValue('A' . $row, ($row - 2));
                      $this->excel->getActiveSheet()->SetCellValue('B' . $row, $client);
                      $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->project_name);
                      $this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse); 
                      $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user_name);
                      $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sc->description);
                      $this->excel->getActiveSheet()->SetCellValue('G' . $row, $progress);
                      $this->excel->getActiveSheet()->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                      $this->excel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                      $this->excel->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                      $this->excel->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                      $this->excel->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                      $this->excel->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                      $this->excel->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $row++;
                    }
                    $filename = 'categories_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
                
            } else {
                $this->session->set_flashdata('error', lang("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            // redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    function delete($id = NULL){
        $this->bpas->checkPermissions('delete', TRUE);

        if ($this->projects_model->delete_project($id)) {
            $this->session->set_flashdata('message', $this->lang->line('The Project has been delete!'));
            admin_redirect("projects");
        }
    }
    function delete1($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);

        if ($this->projects_model->delete_project($id)) {
            $this->session->set_flashdata('message', $this->lang->line('The Project has been delete!'));
            redirect($_SERVER["HTTP_REFERER"]);
        } 
    }
    function delete2($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);

        if ($this->projects_model->delete_project($id)) {
            $this->session->set_flashdata('message', $this->lang->line('The Project has been delete!'));
            admin_redirect("projects");
        }
    }
    public function view($id = null){
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error']        = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['project']      = $this->projects_model->getProjectByID($id);
        $this->data['users']        = $this->site->getAllUser();
        $this->data['milestones']   = $this->projects_model->get_milestone_ByID($id);
        $this->data['task_progress'] = $this->projects_model->get_task_progress($id);

        $this->data['budgetproject'] = $this->projects_model->getBudgetByProjectID($id);
        $this->data['expense']      = $this->projects_model->getExpenseByProjectID($id);
        $this->data['influencer']   = $this->projects_model->getinfluencerByProjectID($id);

        
        if ($this->Owner || $this->Admin) {
            $this->data['tasks'] = $this->projects_model->get_task_ByID($id,null);
        } else {
            $this->data['tasks'] = $this->projects_model->get_task_ByID($id,$this->session->userdata('user_id'));
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('projects/view', $meta, $this->data);
    }
    public function milestones($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['milestones'] = $this->projects_model->get_milestone_ByID($id);
        $this->data['inv']      = $this->projects_model->getProjectByID($id);
        $this->load->view($this->theme . 'projects/milestone', $this->data);
    }
    function add_milestone($project_id = null)
    {
        $this->form_validation->set_rules('name', lang("title"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim');
        $project_id = $project_id;
        if ($this->form_validation->run() == true) {
            $data = array(
                'title' => $this->input->post('name'),
                'project_id' => $project_id,
                'start_date' =>  $this->bpas->fld(trim($this->input->post('start_date'))),
                'end_date' => $this->bpas->fld(trim($this->input->post('end_date')))
            );      
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects");
        }
        if ($this->form_validation->run() == true && $this->projects_model->add_milestone($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("Milestones_has_been_added"));
            admin_redirect("projects/view/".$project_id."" );
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("Milestones");
            $this->data['customers'] = lang("Milestones");
             $this->data['data']         = $project_id;
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->view($this->theme . 'projects/add_milestone', $this->data);
        }
    }
    function edit_milestone($milestone_id = null,$project_id = null)
    {
        $this->form_validation->set_rules('name', lang("title"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim');
        $project_id = $project_id;
        if ($this->form_validation->run() == true) {
            $data = array(
                'title' => $this->input->post('name'),
                'project_id' => $project_id,
                'start_date' =>  $this->bpas->fld(trim($this->input->post('start_date'))),
                'end_date' => $this->bpas->fld(trim($this->input->post('end_date')))
            );      
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."" );
        }
        if ($this->form_validation->run() == true && $this->projects_model->edit_milestone($milestone_id,$data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("Milestones_has_been_updated"));
            admin_redirect("projects/view/".$project_id."" );
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("Milestones");
            $this->data['customers'] = lang("Milestones");
            $this->data['data']         = $milestone_id.'/'.$project_id;
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['milestone']         = $this->projects_model->get_mileston_edit($milestone_id);
            $this->load->view($this->theme . 'projects/edit_milestone', $this->data);
        }
    }
    function delete_milstone($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);

        if ($this->projects_model->delete_milstone($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("task_deleted")));
        }

    }
    public function tasks()
    {
        $this->bpas->checkPermissions('index');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->site->getAllUser();
        
        if ($this->Owner || $this->Admin) {
            $this->data['tasks'] = $this->projects_model->get_all_task(null);
        } else {
            $this->data['tasks'] = $this->projects_model->get_all_task($this->session->userdata('user_id'));
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $this->data['getprojects']         = $this->site->getAllProject();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['clients'] = $this->site->getAllCompanies('customer');
        $meta = ['page_title' => lang('view_task_details'), 'bc' => $bc];
        $this->page_construct('projects/tasks', $meta, $this->data);
    }
    function add_task($project_id = null)
    {
        $this->load->library('upload');
        $this->form_validation->set_rules('name', lang("title"), 'trim|required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'trim');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'trim');
        $this->form_validation->set_rules('progress', lang("progress"), 'trim|required|numeric');

        if ($this->form_validation->run() == true) {
            $user = $this->input->post('assign_to');
            $users_id = '';
            $i = 1;
            foreach($user as $value){
                if(count($user)==$i){
                    $users_id .= $value;
                }else{
                    $users_id .= $value.',';
                }
                $i++;
                
            }

            $data = array(
                'project_id' => $project_id,
                'milestone_id' => $this->input->post('milestone'),
                'title' => $this->input->post('name'),
                'start_date' =>  $this->bpas->fld(trim($this->input->post('start_date'))),
                'end_date' => $this->bpas->fld(trim($this->input->post('end_date'))),
                'description' => $this->input->post('description'),
                'user_id' => $users_id,
                'status' => $this->input->post('status'),
                'progress' => $this->input->post('progress'),
            );  

            if ($_FILES['task_image']['size'] > 0) {
                $photo = rand().$_FILES['task_image']['name'];
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['file_name']  = $photo;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('task_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("projects/view/".$project_id."");
                }
                $data['icon'] = $photo;
            }    
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."");
        }
        
        
        if ($this->form_validation->run() == true && $this->projects_model->add_task($data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("Task_has_been_added"));
            admin_redirect("projects/view/".$project_id."");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("Milestones");
            $this->data['customers'] = lang("Milestones");
            $this->data['data']         = $project_id;
            $this->data['users'] = $this->site->getAllUser();
            $this->data['milestones'] = $this->projects_model->getmilestone($project_id);
            //$this->data['project']         = $this->projects_model->getProjectByID($project_id);
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->load->view($this->theme . 'projects/add_task', $this->data);
        }
    }
    function edit_task($task_id = null, $project_id = null)
    {
        $this->load->library('upload');
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('progress', lang("progress"), 'trim|required|numeric');
            $this->form_validation->set_rules('name', lang("title"), 'trim|required');
            $this->form_validation->set_rules('start_date', lang("start_date"), 'trim');
            $this->form_validation->set_rules('end_date', lang("end_date"), 'trim');
        }else{
            $this->form_validation->set_rules('progress', lang("progress"), 'trim|required|numeric');
        }

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $user = $this->input->post('assign_to');
                $users_id = '';
                $i = 1;
                foreach($user as $value){
                    if(count($user)==$i){
                        $users_id .= $value;
                    }else{
                        $users_id .= $value.',';
                    }
                    $i++;
                    
                }
                $data = array(
                    'project_id' => $project_id,
                    'milestone_id' => $this->input->post('milestone'),
                    'title' => $this->input->post('name'),
                    'start_date' =>  $this->bpas->fld(trim($this->input->post('start_date'))),
                    'end_date' => $this->bpas->fld(trim($this->input->post('end_date'))),
                    'description' => $this->input->post('description'),
                    'user_id' => $users_id,
                    'status' => $this->input->post('status'),
                    'progress' => $this->input->post('progress'),
                );
                if ($_FILES['task_image']['size'] > 0 && $this->input->post('update_image') != '') {
                    unlink($this->upload_path.$this->input->post('update_image'));
                    $photo = rand().$_FILES['task_image']['name'];
                    $config['upload_path']   = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['file_name']  = $photo;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('task_image')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("projects/view/".$project_id."");
                    }
                    $data['icon'] = $photo;
                } else if ($_FILES['task_image']['name'] == '' && $this->input->post('update_image') != '') {
                    $photo = $this->input->post('update_image');
                    $data['icon'] = $photo;
                } else if ($_FILES['task_image']['name'] != '' && $this->input->post('update_image') == '') {
                    $photo = rand().$_FILES['task_image']['name'];
                    $config['upload_path']   = $this->upload_path;
                    $config['allowed_types'] = $this->image_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['file_name']  = $photo;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('task_image')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("projects/view/".$project_id."");
                    }
                    $data['icon'] = $photo;
                }
            } else {
                $data = array(
                    'status' => $this->input->post('status'),
                    'progress' => $this->input->post('progress'),
                );
            }
                  
        } elseif ($this->input->post('edit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."");
        }
        if ($this->form_validation->run() == true && $this->projects_model->edit_task($task_id,$data)) { //check to see if we are creating the customer
            $this->session->set_flashdata('message', lang("Task_has_been_updated"));
            admin_redirect("projects/view/".$project_id."");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['page_title'] = lang("Milestones");
            $this->data['customers'] = lang("Milestones");
            $this->data['users'] = $this->site->getAllUser();
            $this->data['milestones'] = $this->projects_model->getmilestone($project_id);
            //$this->data['project']         = $this->projects_model->getProjectByID($project_id);
            $this->data['data']         = $task_id.'/'.$project_id;
            $this->data['task']         = $this->projects_model->get_task_edit($task_id);
            $this->load->view($this->theme . 'projects/edit_task', $this->data);
        }
    }
    function detail_task($task_id = null, $project_id = null)
    {
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['page_title'] = lang("Milestones");
        $this->data['customers'] = lang("Milestones");
        $this->data['users'] = $this->site->getAllUser();
        $this->data['milestones'] = $this->projects_model->getmilestone($project_id);
        //$this->data['project']         = $this->projects_model->getProjectByID($project_id);
        $this->data['data']         = $task_id.'/'.$project_id;
        $this->data['task']         = $this->projects_model->get_task_detail($task_id);
        $this->load->view($this->theme . 'projects/detail_task', $this->data);
    }
    public function gettasks($project_id)
    {
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_task") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('projects/delete_task/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>Delete</a>";
        $detail_link="<a href='" . admin_url('projects/modal_view/$1')."'  data-toggle='modal' data-target='#myModal'><i class='fa fa-file-text-o'></i> Detail</a>";
        $edit_link="<a href='" . admin_url('projects/edit_task/$1')."' class='tip' title='" . lang("edit_task") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i>Edit</a>";

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
       
                <li>' . $detail_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("projects_tasks.id as id,
                projects_milestones.title as milstone, 
                projects_tasks.title,
                 projects_tasks.start_date, 
                 projects_tasks.end_date, 
                 projects_tasks.status", false)
            ->from('projects_tasks')
            ->join('projects_milestones', 'bpas_projects_milestones.id = projects_tasks.milestone_id','left');

        if ($project_id) {
            $this->datatables->where('projects_tasks.project_id', $project_id);
        }
        $this->datatables->add_column('Actions',$action , "id");
        echo $this->datatables->generate();
    }
    function delete_task($id = NULL,$image = null)
    {
        $this->bpas->checkPermissions('delete', TRUE);
        if ($image != null) {
            unlink($this->upload_path.$image);
        }
        if ($this->projects_model->delete_task($id)) {
            $this->session->set_flashdata('message', lang("Task_has_been_deleted"));
            admin_redirect('projects/tasks');
        }

    }
    public function plans($biller_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'purchases_request');
        $this->load->admin_model('reports_model');

        if(isset($_GET['d']) != ""){
            $date = $_GET['d'];
            $this->data['date'] = $date;
        }

        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }

        $this->data['users'] = $this->reports_model->getStaff();
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

        $this->data['warehouse_id']     =  null;
        $this->data['projects']         = $this->site->getAllProject();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('purchase_request')));
        $meta = array('page_title' => lang('purchase_request'), 'bc' => $bc);
        $this->page_construct('projects/plans', $meta, $this->data);
    }
    public function getplans($biller_id = null)
    {
        $this->bpas->checkPermissions('index', null, 'purchases_request');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
            } else {
                $biller_id = $user->biller_id ? ((array) $user->biller_id) : null;
            }
        }
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
        if ($this->input->get('project')) {
            $project = $this->input->get('project');
        } else {
            $project = NULL;
        }
        if ($this->input->get('note')) {
            $note = $this->input->get('note');
        } else {
            $note = NULL;
        }

        $view_a5 = anchor('admin/projects/view_a5/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_plan'));
        $edit_link = anchor('admin/projects/edit_plan/$1', '<i class="fa fa-edit"></i>' . lang('edit_plan'), array('class' => 'auth'));
        $auth_link = anchor('admin/projects/approved/$1', '<i class="fa fa-check"></i>' . lang('approved'));
        $unauth_link = anchor('admin/projects/unapproved/$1', '<i class="fa fa-check"></i>' . lang('unapproved'));
        $reject = anchor('admin/purchases_request/rejected/$1', '<i class="fa fa-times" aria-hidden="true"></i> ' . lang('reject'));
        $unreject = anchor('admin/purchases_request/unreject/$1', '<i class="fa fa-check"></i> ' . lang('unreject'));
        $create_link = anchor('admin/purchases/add/0/0/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_purchase'), array('class' => 'disabled-link'));
        $create_purchase = anchor('admin/purchases_request/add/$1', '<i class="fa fa-plus-circle"></i>' . lang('creat_puchases'), array('class' => 'disabled-link'));
        $approve_link = anchor('admin/purchases_request/approved_status/$1', '<i class="fa fa-check"></i> ' . lang('approved_status'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line("delete_plan") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('projects/delete_plan/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_plan') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $view_a5 . '</li>'
               // .'<li>' . $approve_link . '</li>'
                . (($this->Owner || $this->Admin) ? '<li class="approved">' . $auth_link . '</li>' : ($this->GP['purchases_request-approved'] ? '<li class="approved">' . $auth_link . '</li>' : '')) 
                .(($this->Owner || $this->Admin) ? '<li class="unapproved">' . $unauth_link . '</li>' : ($this->GP['purchases_request-rejected'] ? '<li class="unapproved">' . $unauth_link . '</li>' : '')) 
                //.(($this->Owner || $this->Admin) ? '<li class="reject">' . $reject . '</li>' : ($this->GP['purchases_request-rejected'] ? '<li class="reject">' . $reject . '</li>' : '')) 
                .(($this->Owner || $this->Admin) ? '<li class="edit">' . $edit_link . '</li>' : ($this->GP['projects-edit_plan'] ? '<li class="edit">' . $edit_link . '</li>' : '')) 
                 .(($this->Owner || $this->Admin) ? '<li class="create">' . $create_link . '</li>' : ($this->GP['purchases-add'] ? '<li class="create">' . $create_link . '</li>' : '')) 
                 .(($this->Owner || $this->Admin) ? '<li class="create">' . $delete_link . '</li>' : ($this->GP['projects-delete_plan'] ? '<li class="create">' . $delete_link . '</li>' : '')) .
            '</ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        $this->datatables
            ->select("projects_plan.id as id,DATE_FORMAT({$this->db->dbprefix('projects_plan')}.date, '%Y-%m-%d %T') as date,project_name,title,reference_no, supplier,order_status, projects_plan.status")
            ->from('projects_plan')
            ->join('projects', 'projects_plan.project_id = projects.project_id', 'left');
        if ($biller_id) {
            $this->datatables->where_in('projects_plan.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_projects_plan.created_by, '" . $this->session->userdata('user_id') . "')");
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        if ($user_query) {
            $this->datatables->where('projects_plan.created_by', $user_query);
        }
        if ($supplier) {
            $this->datatables->where('projects_plan.supplier_id', $supplier);
        }
        if ($warehouse) {
            $this->datatables->where('projects_plan.warehouse_id', $warehouse);
        }
        if ($reference_no) {
            $this->datatables->like('projects_plan.reference_no', $reference_no, 'both');
        }
        if ($project) {
            $this->datatables->like('projects_plan.project_id', $project);
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('projects_plan'). '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        if ($note) {
            $this->datatables->like('projects_plan.note', $note, 'both');
        }

        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function view_a5($purchase_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->projects_model->getProjectPlanByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $approve= $this->approved_model->getApprovedByID(['purchase_request_id' => $purchase_id]);
        $data["approves"] = $inv->approved_by;
        $data["approves"] = $approve->approved_date;
        $this->data['approves'] = $this->site->getUser($inv->approved_by);
        if($inv->approved_by == NULL){
            $this->data['approves'] = NULL;
        }
        $this->data['rows'] = $this->projects_model->getAllProjectPlanItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->projects_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->projects_model->getPurchaseByID($inv->return_id) : NULL;
        $this->data['return_rows'] = $inv->return_id ? $this->projects_model->getAllPurchaseItems($inv->return_id) : NULL;
        $this->load->view($this->theme . 'projects/view_plan', $this->data);
    }
    public function add_plan($quote_id = null)
    {
        $this->bpas->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('project', $this->lang->line("project"), '');
        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $project_id     = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            $reference_purchase_no = $this->input->post('reference_purchase_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
    
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date     = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $biller_id    = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $i = sizeof($_POST['product']);
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->bpas->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_description = $_POST['description'][$r];
                $item_qoh = isset($_POST['qoh'][$r]) ? $_POST['qoh'][$r] : 0;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER["HTTP_REFERER"]);
                        }
                    }
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'quantity_received' => 0,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'supplier_part_no' => $supplier_part_no,
                        'description'       => $item_description,
                        'qoh'               => $item_qoh,
                    );

                    $products[] = ($product + $gst_data);
                    $total += $this->bpas->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'date'          => $date,
                'project_id'    => $project_id,
                'title'         => $this->input->post('title'),
                'reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->bpas->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'due_date' => $due_date,
                'biller_id' => $biller_id
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

            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->projects_model->addProjectPlan($data, $products)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("projects_plan_added"));
            admin_redirect('projects/plans');
        } else {
            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $supplier_id =$this->data['quote']->supplier_id;
                $items = $this->purchases_model->getAllQuoteItems($quote_id);
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty*$item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;
                            $crow->description    = '';

                            $pr[$ri] = array('id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . " (" . $crow->code . ")", 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }

                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;
                        $options = $this->purchases_model->getProductOptions($row->id);
                        $row->description    = '';
                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                            'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
               
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']   = $quote_id;
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber']   = $this->site->getReference('pr');
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['billers']    = $this->site->getAllCompanies('biller');

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
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('projects'), 'page' => lang('projects')), array('link' => '#', 'page' => lang('add_plan')));
            $meta = array('page_title' => lang('add_plan'), 'bc' => $bc);
            $this->page_construct('projects/add_plan', $meta, $this->data);
        }
    }
    public function edit_plan($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->projects_model->getProjectPlanByID($id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('title', $this->lang->line("title"), 'required');
        $this->form_validation->set_rules('reference_no', $this->lang->line("ref_no"), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line("warehouse"), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line("supplier"), 'required');
        $this->form_validation->set_rules('project', $this->lang->line("project"), '');
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            if($this->Owner || $this->Admin){
                $deadline = $this->bpas->fld(trim($this->input->post('deadline')));//var_dump($deadline);exit();
            }else{
                $deadline=date('Y-m-d H:i:s'); //var_dump($deadline);exit();
                
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            if(!empty($_POST['status']))
            {
            $status = $this->input->post('status');
            }else{
                $status="Pending";
            }
            $biller_id  = $this->input->post('biller') ?  $this->input->post('biller') : $this->Settings->default_biller;
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company != '-'  ? $supplier_details->company : $supplier_details->name;
            $note = $this->bpas->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $partial = false;
            $i = sizeof($_POST['product']);
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code = $_POST['product'][$r];
                $item_net_cost = $this->bpas->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->bpas->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->bpas->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $quantity_received = $_POST['received_base_quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->bpas->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance = $_POST['quantity_balance'][$r];
                $ordered_quantity = $_POST['ordered_quantity'][$r];
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_description = $_POST['description'][$r];
                $item_qoh = isset($_POST['qoh'][$r]) ? $_POST['qoh'][$r] : 0;

                if ($status == 'received' || $status == 'partial') {
                    if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang("received_more_than_ordered"));
                        redirect($_SERVER["HTTP_REFERER"]);
                    }
                    $balance_qty =  $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                }
                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $unit_cost = $this->bpas->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = "";

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {

                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity, 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    $item = array(
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->bpas->formatDecimal($item_net_cost + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->bpas->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                        'description'       => $item_description,
                        'qoh'       => $item_qoh,
                    );

                    $items[] = ($item+$gst_data);
                    $total += $item_net_cost * $item_unit_quantity;
                }
            }

            if (empty($items)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                foreach ($items as $item) {
                    $item["status"] = ($status == 'partial') ? 'received' : $status;
                    $products[] = $item;
                }
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax));
            $total_discount = $this->bpas->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->bpas->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($shipping) - $order_discount), 4);
            $data = array(
                'date'          => $date,
                'project_id'    => $project_id,
                'title'         => $this->input->post('title'),
                'reference_no'  => $reference,
                'supplier_id'   => $supplier_id,
                'supplier'      => $supplier,
                'warehouse_id'  => $warehouse_id,
                'note'          => $note,
                'total'         => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->bpas->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'biller_id' => $biller_id
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

            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->projects_model->UpdateProjectPlan($id, $data, $products)) {
            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line("projects_plan_updated"));
            admin_redirect('projects/plans');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->projects_model->getAllProjectPlanItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->bpas->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity-$row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                $row->cost = $this->bpas->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->tax_rate = $item->tax_rate_id;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $row->description    = $item->description;
                $row->qoh    = $item->qoh;
                $pr[$ri] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $c++;
            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
            $this->data['purchase']   = $this->projects_model->getProjectPlanByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['billers']    = $this->site->getAllCompanies('biller');

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
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('project/plans'), 'page' => lang('projects')), array('link' => '#', 'page' => lang('edit_plan')));
            $meta = array('page_title' => lang('edit_plan'), 'bc' => $bc);
            $this->page_construct('projects/edit_plan', $meta, $this->data);
        }
    }
    public function delete_plan($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
  
        if ($this->projects_model->deletePlan($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(array('error' => 0, 'msg' => lang("plan_deleted")));
            }
            $this->session->set_flashdata('message', lang('plan_deleted'));
            admin_redirect('projects/plans');
        }
    }
    public function approved($request_id = null)
    {
        $data['approved_date']           =  date('Y-m-d H:i:s');
        $data['approved_by']             = $this->session->userdata('user_id');
        $data['purchase_request_id']     = $request_id;
        $request                         = ['purchase_request_id' => $request_id];
        $aprroved['purchase_request_id'] = $request_id;
        $col                             = "purchase_request_id";
        $status                          = "approved";

        //$this->approved_model->changeStatus($request_id, $col, $request, $data);
        $this->db->set(
            array(
                'status'        => $status,
                'approved_by'   => $this->session->userdata('user_id'),
                'rejected_by'   => null,
                'approved_date' => date('Y-m-d H:i:s')
            )
        ); 
        $this->db->where('id', $request_id);  
        if($this->db->update('projects_plan')){
            $this->session->set_flashdata('message', $this->lang->line("projects_plan_approved"));
            redirect($_SERVER["HTTP_REFERER"]);
        } 
    }
    public function unapproved($request_id = null)
    {
        $data['unapproved_date']         = date('Y-m-d H:i:s');
        $data['unapproved_by']           = $this->session->userdata('user_id');
        $data['purchase_request_id']     = $request_id;
        $request                         = ['purchase_request_id' => $request_id];
        $aprroved['purchase_request_id'] = $request_id;
        $col                             = "purchase_request_id";
        $status                          = "pending";
        //$this->approved_model->changeStatus($request_id, $col, $request, $data);
        $this->db->set('status', $status);
        $this->db->where('id', $request_id);
        if($this->db->update('projects_plan')){

            $this->session->set_flashdata('message', $this->lang->line("projects_plan_unapproved"));

            redirect($_SERVER["HTTP_REFERER"]);

        }
    }
    public function get_projects(){
        $biller_id = $this->input->get('biller_id');
        $positions = $this->site->getAllProject($biller_id);
        echo json_encode($positions);
    }
    //----------vendor-----------
    public function getVendors($project_id){
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_vendor") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('projects/delete_vendor/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>Delete</a>";
        $detail_link="<a href='" . admin_url('projects/modal_view/$1')."'  data-toggle='modal' data-target='#myModal'><i class='fa fa-file-text-o'></i> Detail</a>";
        $edit_link="<a href='" . admin_url('projects/edit_vendor/$1')."' class='tip' title='" . lang("edit_vendor") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i>Edit</a>";

        $add_payment_link='';
        //if($this->Settings->payment_expense==1){
            $payments_link = anchor('admin/projects/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
            $add_payment_link = anchor('admin/expenses/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        //}

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                projects_vendors.id as id,
                projects_vendors.date as date, 
                companies.name, 
                companies.phone, 
                companies.gender, 
                price,
                paid,
                (price - paid) as balance
            ", false)
            ->from('projects_vendors')
            ->join('companies', 'companies.id = projects_vendors.supplier_id','left');

        if ($project_id) {
            $this->datatables->where('projects_vendors.project_id', $project_id);
        }
        $this->datatables->add_column('Actions',$action , "id");
        echo $this->datatables->generate();
    }
    function add_vendor($project_id = null)
    {
        $this->load->library('upload');
        $this->form_validation->set_rules('project_id', lang("project_id"), 'trim|required');
        $this->form_validation->set_rules('date', lang("title"), 'trim|required');
        $this->form_validation->set_rules('vendor', lang("vendor"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          =>  $this->bpas->fld(trim($this->input->post('date'))),
                'project_id'    => $project_id,
                'supplier_id'   => $this->input->post('vendor'),
                'description'   => $this->bpas->remove_tag($this->input->post('description')),
                'created_by'    => $this->session->userdata('user_id'),
            );     
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."#vendors");
        }
        if ($this->form_validation->run() == true && $this->projects_model->add_project_vendor($data)) {
            $this->session->set_flashdata('message', lang("vendor_has_been_added"));
            admin_redirect("projects/view/".$project_id."#vendors");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['page_title']   = lang("Milestones");
            $this->data['customers']    = lang("Milestones");
            $this->data['project_id']   = $project_id;
            $this->data['suppliers']    = $this->site->getAllCompanies('supplier');
            $this->load->view($this->theme . 'projects/add_vendor', $this->data);
        }
    }
    function edit_vendor($id = null, $project_id = null)
    {
        $project_id = $this->input->post('project_id');

        $this->form_validation->set_rules('project_id', lang("project_id"), 'trim|required');
        $this->form_validation->set_rules('date', lang("date"), 'trim|required');
        $this->form_validation->set_rules('vendor', lang("vendor"), 'trim|required');
 

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          => $this->bpas->fld(trim($this->input->post('date'))),
                'project_id'    => $this->input->post('project_id'),
                'supplier_id'   => $this->input->post('vendor'),
                'description'   => $this->bpas->remove_tag($this->input->post('description')),
                'updated_at'    => date('Y-m-d H:i:s'),
            );    
        } elseif ($this->input->post('edit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."#vendors");
        }
        if ($this->form_validation->run() == true && $this->projects_model->edit_project_vendor($id,$data)) {
            $this->session->set_flashdata('message', lang("vendor_updated"));
            admin_redirect("projects/view/".$project_id."#vendors");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['page_title']   = lang("milestones");
            $this->data['customers']    = lang("milestones");
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['milestones']   = $this->projects_model->getmilestone($project_id);
            $this->data['data']         = $id.'/'.$project_id;
            $this->data['vendor']       = $this->projects_model->getProjectVendorbyID($id);
            $this->load->view($this->theme . 'projects/edit_vendor', $this->data);
        }
    }
    function delete_vendor($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);
        if ($this->projects_model->delete_projects_vendors($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("vendor_has_been_deleted")));
           // admin_redirect('projects/view'.$id.'#vendors');
        }

    }
    public function payments($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        if($id){
            $expense = $this->projects_model->getProjectVendorbyID($id); 
            $this->data['payments'] = $this->projects_model->getInfluencerPayments($id);
            $this->data['inv'] = $expense;
            $this->load->view($this->theme . 'projects/payments', $this->data);
        }
    }
    //------------get note---------
    public function getNoted($project_id){
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_note") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('projects/delete_note/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>Delete</a>";
        $detail_link="<a href='" . admin_url('projects/modal_view/$1')."'  data-toggle='modal' data-target='#myModal'><i class='fa fa-file-text-o'></i> Detail</a>";
        $edit_link="<a href='" . admin_url('projects/edit_note/$1')."' class='tip' title='" . lang("edit_note") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i>Edit</a>";

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
            ->select("
                projects_note.id as id,
                projects_note.date as date, 
                projects_note.title, 
                projects_note.description", false
             )
            ->from('projects_note');

        if ($project_id) {
            $this->datatables->where('projects_note.project_id', $project_id);
        }
        $this->datatables->add_column('Actions',$action , "id");
        echo $this->datatables->generate();
    }
    function add_note($project_id = null)
    {
        $this->load->library('upload');
        $this->form_validation->set_rules('project_id', lang("project_id"), 'trim|required');
        $this->form_validation->set_rules('date', lang("title"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          =>  $this->bpas->fld(trim($this->input->post('date'))),
                'project_id'    => $project_id,
                'title'         => $this->input->post('title'),
                'description'   => $this->bpas->remove_tag($this->input->post('description')),
                'created_by'    => $this->session->userdata('user_id'),
            );     
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."#noted");
        }
        if ($this->form_validation->run() == true && $this->projects_model->add_project_note($data)) {
            $this->session->set_flashdata('message', lang("vendor_has_been_added"));
            admin_redirect("projects/view/".$project_id."#noted");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['page_title']   = lang("Milestones");
            $this->data['customers']    = lang("Milestones");
            $this->data['project_id']   = $project_id;
            $this->data['suppliers']    = $this->site->getAllCompanies('supplier');
            $this->load->view($this->theme . 'projects/add_note', $this->data);
        }
    }
    function edit_note($id = null, $project_id = null)
    {
        $project_id = $this->input->post('project_id');

        $this->form_validation->set_rules('project_id', lang("project_id"), 'trim|required');
        $this->form_validation->set_rules('date', lang("date"), 'trim|required');
        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          => $this->bpas->fld(trim($this->input->post('date'))),
                'project_id'    => $this->input->post('project_id'),
                'title'         => $this->input->post('title'),
                'description'   => $this->bpas->remove_tag($this->input->post('description')),
                'updated_at'    => date('Y-m-d H:i:s'),
            );    
        } elseif ($this->input->post('edit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."#noted");
        }
        if ($this->form_validation->run() == true && $this->projects_model->edit_project_note($id,$data)) {
            $this->session->set_flashdata('message', lang("vendor_updated"));
            admin_redirect("projects/view/".$project_id."#noted");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['page_title']   = lang("milestones");
            $this->data['customers']    = lang("milestones");
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['milestones']   = $this->projects_model->getmilestone($project_id);
            $this->data['data']         = $id.'/'.$project_id;
            $this->data['vendor']       = $this->projects_model->getProjectNotebyID($id);
            $this->load->view($this->theme . 'projects/edit_note', $this->data);
        }
    }
    function delete_note($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);
        if ($this->projects_model->delete_projects_note($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("vendor_has_been_deleted")));
           // admin_redirect('projects/view'.$id.'#vendors');
        }

    }
    //-----------
     public function getMembers($project_id){
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_member") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('projects/delete_member/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>Delete</a>";
        $detail_link="<a href='" . admin_url('projects/modal_view/$1')."'  data-toggle='modal' data-target='#myModal'><i class='fa fa-file-text-o'></i> Detail</a>";
        $edit_link="<a href='" . admin_url('projects/edit_member/$1')."' class='tip' title='" . lang("edit_member") . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i>Edit</a>";

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
            ->select("
                projects_members.id as id,
                projects_members.date as date, 
                concat({$this->db->dbprefix('users')}.last_name,' ',{$this->db->dbprefix('users')}.first_name) as user, 
                users.phone, 
                users.gender, 
                projects_members.description", false
             )
            ->from('projects_members')
            ->join('users', 'users.id = projects_members.member_id','left');

        if ($project_id) {
            $this->datatables->where('projects_members.project_id', $project_id);
        }
        $this->datatables->add_column('Actions',$action , "id");
        echo $this->datatables->generate();
    }
    function add_member($project_id = null)
    {
        $this->load->library('upload');
        $this->form_validation->set_rules('project_id', lang("project_id"), 'trim|required');
        $this->form_validation->set_rules('date', lang("title"), 'trim|required');
        $this->form_validation->set_rules('member', lang("member"), 'trim|required');

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          =>  $this->bpas->fld(trim($this->input->post('date'))),
                'project_id'    => $project_id,
                'member_id'     => $this->input->post('member'),
                'description'   => $this->bpas->remove_tag($this->input->post('description')),
                'created_by'    => $this->session->userdata('user_id'),
            );     
        } elseif ($this->input->post('add')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."#members");
        }
        if ($this->form_validation->run() == true && $this->projects_model->add_project_member($data)) {
            $this->session->set_flashdata('message', lang("vendor_has_been_added"));
            admin_redirect("projects/view/".$project_id."#members");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['page_title']   = lang("add_member");
            $this->data['customers']    = lang("add_member");
            $this->data['project_id']   = $project_id;
            $this->data['users']        = $this->site->getAllUser();
            $this->load->view($this->theme . 'projects/add_member', $this->data);
        }
    }
    function edit_member($id = null, $project_id = null)
    {
        $project_id = $this->input->post('project_id');

        $this->form_validation->set_rules('project_id', lang("project_id"), 'trim|required');
        $this->form_validation->set_rules('date', lang("date"), 'trim|required');
        $this->form_validation->set_rules('member', lang("member"), 'trim|required');
 

        if ($this->form_validation->run() == true) {
            $data = array(
                'date'          => $this->bpas->fld(trim($this->input->post('date'))),
                'project_id'    => $this->input->post('project_id'),
                'member_id'     => $this->input->post('member'),
                'description'   => $this->bpas->remove_tag($this->input->post('description')),
                'updated_at'    => date('Y-m-d H:i:s'),
            );    
        } elseif ($this->input->post('edit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("projects/view/".$project_id."#members");
        }
        if ($this->form_validation->run() == true && $this->projects_model->edit_project_member($id,$data)) {
            $this->session->set_flashdata('message', lang("member_updated"));
            admin_redirect("projects/view/".$project_id."#members");
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['page_title']   = lang("edit_member");
            $this->data['customers']    = lang("milestones");
            $this->data['users']        = $this->site->getAllUser();
            $this->data['member']       = $this->projects_model->getProjectMemberbyID($id);
            $this->load->view($this->theme . 'projects/edit_member', $this->data);
        }
    }
    function delete_member($id = NULL)
    {
        $this->bpas->checkPermissions('delete', TRUE);
        if ($this->projects_model->delete_projects_member($id)) {
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("member_has_been_deleted")));
           // admin_redirect('projects/view'.$id.'#vendors');
        }

    }
    public function getExpenses($project_id = null)
    {
        $this->bpas->checkPermissions('index',null);
        $payments_link ='';
        $add_payment_link='';
        if($this->Settings->payment_expense==1){
            $payments_link = anchor('admin/purchases/payments/0/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), ' class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
            $add_payment_link = anchor('admin/expenses/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'class="expense_payment" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"');
        }

        $detail_link = anchor('admin/expenses/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/expenses/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'));
       // $edit_link   = anchor('admin/expenses/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_expense') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('expenses/delete_expense/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_expense') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, 
                {$this->db->dbprefix('expenses')}.date, 
                {$this->db->dbprefix('expenses')}.reference, 
                {$this->db->dbprefix('companies')}.name as biller,
                {$this->db->dbprefix('expenses')}.grand_total as amount,
                {$this->db->dbprefix('expenses')}.paid as paid, 
                (grand_total- IFNULL(paid,0)) as balance, 
                {$this->db->dbprefix('expenses')}.payment_status, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, 
                {$this->db->dbprefix('expenses')}.attachment", false)
            ->from('expenses')
            ->join('companies', 'companies.id=expenses.biller_id', 'left')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('users exby', 'exby.id=expenses.expense_by', 'left');

        if ($project_id) {
            $this->datatables->where('expenses.project_id',$project_id);
        } 
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('expenses.created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function getBudgets($biller_id = null)
    {
        $this->bpas->checkPermissions('budgets');
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
            }
        }
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $project_id   = $this->input->get('project') ? $this->input->get('project') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $biller       = $this->input->get('project') ? $this->input->get('project') : null;
        $title        = $this->input->get('title') ? $this->input->get('title') : null;

        if ($start_date) {
            $start_date = $this->bpas->fsd($start_date);
            $end_date   = $this->bpas->fsd($end_date);
        }

        $edit_link   = anchor('admin/expenses/edit_budget/$1', '<i class="fa fa-edit"></i> ' . lang('edit_budget'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='" . $this->lang->line('delete_budget') . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('expenses/delete_budget/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_budget') . '</a>';
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
            ->select(
                $this->db->dbprefix('budgets').".id as id, 
                {$this->db->dbprefix('budgets')}.date,
                reference, 
                title, 
                sum(amount) as amount, 
                {$this->db->dbprefix('budgets')}.attachment", false)
            ->from('budgets')
            ->join('users', 'users.id = budgets.created_by', 'left')
            ->join('companies', 'companies.id = budgets.biller_id', 'left')
            ->join('projects', 'projects.project_id = budgets.project_id', 'left')
            ->group_by('budgets.reference');

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

        if ($reference_no) {
            $this->datatables->where('budgets.reference', $reference_no);
        }
        if ($title) {
            $this->datatables->like('budgets.title', $title, 'both');
        }
        if ($project_id) {
            $this->datatables->where('budgets.project_id', $project_id);
        }
        if ($biller_id) {
            $this->datatables->where('budgets.biller_id IN ('.$biller_id.')');
        }
        if ($start_date) {
            $this->datatables->where('budgets' . '.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . ' 23:59:00"');
        }

        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
}
