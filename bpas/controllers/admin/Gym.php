<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Gym extends MY_Controller
{
	function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        $this->lang->admin_load('schools', $this->Settings->user_language);
		$this->load->library('ion_auth');
        $this->load->library('form_validation');
        
        $this->load->admin_model('schools_model');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('gym_model');
        $this->load->admin_model('companies_model'); 
        //$this->load->admin_model('hr_model');		
		$this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '10240';
    }
    public function schedules() 
    {
        $this->bpas->checkPermissions('index', TRUE, 'schedules');
        $year  = $this->input->post('year') ? $this->input->post('year') : date("Y");
        $month = $this->input->post('month') ? $this->input->post('month') : date("n");
        $type  = $this->input->post('suspend_type') ? $this->input->post('suspend_type') : null;
        $bed   = $this->input->post('bed') ? $this->input->post('bed') : null;  
          
        $this->data['activities'] = $this->gym_model->getActivities();

        $this->data['users']      = $this->site->getStaff();
        $this->data['billers']    = $this->site->getAllCompanies('biller');

        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('room'), 'page' => lang('reports')), array('link' => '#', 'page' => lang('schedules')));
        $meta = array('page_title' => lang('schedules'), 'bc' => $bc);
        $this->page_construct('gym/schedules', $meta, $this->data);
    }
    public function trainees($action = null)
    {
        $this->bpas->checkPermissions('index', true, 'trainees');

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('trainees')]];
        $meta                 = ['page_title' => lang('trainees'), 'bc' => $bc];
        $this->page_construct('gym/trainees', $meta, $this->data);
    }
    public function getTrainees(){
        $this->bpas->checkPermissions('index', true, 'trainees');
        $this->load->library('datatables');

    
        $view_detail = anchor('admin/customers/actions/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
        $edit_customer ="<a class='tip' title='" . lang('edit_trainee') . "' href='" . admin_url('gym/edit_trainee/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-edit\"></i> ".lang("edit_trainee")."</a> ";
        $delete_customer ="<a href='#' class='tip po' title='<b>" . lang('delete_customer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ".lang("delete_trainee")."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$view_detail.'</li>
                        <li>'.$edit_customer.'</li>
                        <li>'.$delete_customer.'</li>
                    </ul>
                </div></div>';
        $this->datatables
            ->select("
                {$this->db->dbprefix('companies')}.id as id, 
                company, 
                companies.code, 
                companies.name, 
                phone, 
                sh_grades.name as level, 
                customer_package.name as membership, 
                vat_no
            ")
            ->from('companies')
            ->join('zones z', 'z.id=companies.zone_id', 'left')
            ->join('sh_grades', 'sh_grades.id=companies.level_id', 'left')
            ->join('customer_package', 'customer_package.id=companies.service_package', 'left')  
            ->where('group_name', 'customer');
        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->order_by("{$this->db->dbprefix('companies')}.id", "ASC");
        echo $this->datatables->generate();
    }
    public function add_trainee(){
        $this->bpas->checkPermissions('add', true, 'trainees');
        $this->form_validation->set_rules('code', lang('code'), 'is_unique[companies.code]');
        $this->form_validation->set_rules('contact_person', lang('contact_person'), 'is_unique[companies.contact_person]');
        $this->form_validation->set_rules('find_consumer_comission', lang("find_consumer_comission"), 'numeric|greater_than[0]');
        // $this->form_validation->set_rules('credit_limit', lang("credit_limit"), 'numeric');
        
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run('companies/add') == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'code'                      => $this->input->post('code'),
                'name'                      => $this->input->post('name'),
                'agent'                     => $this->input->post('agent') ? $this->input->post('agent'): null,
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'paid_by'                   => $this->input->post('paid_by') ? $this->input->post('paid_by'): null,
                // 'prefer_cantact_by'         => $this->input->post('prefer_cantact_by') ? $this->input->post('prefer_cantact_by'): null,
                'service_package'           => $this->input->post('service_package') ? $this->input->post('service_package'): null,
                // 'stamp_type'                => $this->input->post('stamp_type') ? $this->input->post('stamp_type'): null,
                // 'number_of_people'          => $this->input->post('number_of_people') ? $this->input->post('number_of_people') : null,
                'street_no'                 => $this->input->post('street_no') ? $this->input->post('street_no'): null,
                'commune'                   => $this->input->post('commune') ? $this->input->post('commune') : $this->input->post('commune'),
                'village'                   => $this->input->post('village') ? $this->input->post('village') : null,
                'email'                     => $this->input->post('email'),
                'service_fee'               => $this->input->post('service_fee') ? $this->input->post('service_fee') : 0,
                'vat_no'                    => $this->input->post('vat_no') ? $this->input->post('vat_no') : null,
                'contact_person'            => $this->input->post('contact_person') ? $this->input->post('contact_person') : null,
                'group_id'                  => '4',
                'group_name'                => 'customer',
                'customer_group_id'         => $this->input->post('customer_group') ? $this->input->post('customer_group') : 1,
                'customer_group_name'       => $cg->name,
                'price_group_id'            => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'          => $this->input->post('price_group') ? $pg->name : null,
                'company'                   => $this->Settings->customer_detail ? $this->input->post('company') : '-',
                'address'                   => $this->input->post('address'),
                'vat_no'                    => $this->input->post('vat_no'),
                'city'                      => $this->input->post('city'),
                'state'                     => $this->input->post('state'),
                'postal_code'               => $this->input->post('postal_code'),
                'country'                   => $this->input->post('country'),
                'phone'                     => $this->input->post('phone'),
                'gender'                    => $this->input->post('gender'),
                'age'                       => $this->input->post('age'),
                'cf1'                       => $this->input->post('cf1'),
                'cf2'                       => $this->input->post('cf2'),
                'cf3'                       => $this->input->post('cf3'),
                'cf4'                       => $this->input->post('cf4'),
                'cf5'                       => $this->input->post('cf5'),
                'cf6'                       => $this->input->post('cf6'),
                'gst_no'                    => $this->input->post('gst_no'),
                'zone_id'                   => $this->input->post('zone_id') ? $this->input->post('zone_id') : null,
                'save_point'                => trim($this->input->post('save_point')),
                'credit_limit'              => ($this->input->post('credit_limit') != '' ? $this->input->post('credit_limit') : null),    
            ];
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
                $photo        = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang('customer_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['Lastrow']        = $this->companies_model->getLastCompanies('customer');
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['trainers']         = $this->gym_model->getAllTrainers();
            $this->data['levels']           = $this->companies_model->getAllLevel();
            $this->data['memberships']      = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'gym/add_trainee', $this->data);
        }
    }
    public function edit_trainee($id = null)
    {
        $this->bpas->checkPermissions('edit', true, 'trainees');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        } 
        $company_details = $this->companies_model->getCompanyByID($id); 
        if ($this->input->post('code') != $company_details->code) {
            $this->form_validation->set_rules('code', lang('code'), 'is_unique[companies.code]');
        }
        if ($this->input->post('phone') != $company_details->phone) {
            $this->form_validation->set_rules('phone', lang('phone'), 'is_unique[companies.phone]');
        }
        // $this->form_validation->set_rules('credit_limit', lang("credit_limit"), 'numeric');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run('companies/add') == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));

            $data = [
                'code'                      => $this->input->post('code'),
                'name'                      => $this->input->post('name'),
                'agent'                     => $this->input->post('agent') ? $this->input->post('agent'): null,
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'service_package'           => $this->input->post('service_package') ? $this->input->post('service_package'): null,
                'street_no'                 => $this->input->post('street_no') ? $this->input->post('street_no'): null,
                'commune'                   => $this->input->post('commune') ? $this->input->post('commune') : $this->input->post('commune'),
                'village'                   => $this->input->post('village') ? $this->input->post('village') : null,
                'email'                     => $this->input->post('email'),
                'service_fee'               => $this->input->post('service_fee') ? $this->input->post('service_fee') : 0,
                'vat_no'                    => $this->input->post('vat_no') ? $this->input->post('vat_no') : null,
                'contact_person'            => $this->input->post('contact_person') ? $this->input->post('contact_person') : null,
                'group_id'                  => '4',
                'group_name'                => 'customer',
                'customer_group_id'         => $this->input->post('customer_group') ? $this->input->post('customer_group') : 1,
                'customer_group_name'       => $cg->name,
                'price_group_id'            => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'          => $this->input->post('price_group') ? $pg->name : null,
                'company'                   => $this->Settings->customer_detail ? $this->input->post('company') : '-',
                'address'                   => $this->input->post('address'),
                'vat_no'                    => $this->input->post('vat_no'),
                'city'                      => $this->input->post('city'),
                'state'                     => $this->input->post('state'),
                'postal_code'               => $this->input->post('postal_code'),
                'country'                   => $this->input->post('country'),
                'phone'                     => $this->input->post('phone'),
                'gender'                    => $this->input->post('gender'),
                'age'                       => $this->input->post('age'),
                'cf1'                       => $this->input->post('cf1'),
                'cf2'                       => $this->input->post('cf2'),
                'cf3'                       => $this->input->post('cf3'),
                'cf4'                       => $this->input->post('cf4'),
                'cf5'                       => $this->input->post('cf5'),
                'cf6'                       => $this->input->post('cf6'),
                'level_id'                  => $this->input->post('level'), 
            ];

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
                $photo        = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang('customer_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['customer']        = $company_details;
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['trainers']         = $this->gym_model->getAllTrainers();
            $this->data['levels']           = $this->companies_model->getAllLevel();
            $this->data['memberships']      = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'gym/edit_trainee', $this->data);
        }
    }
    public function classes($biller_id = false)
    {   
        $this->bpas->checkPermissions('index', true, 'class');
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('gym'), 'page' => lang('gym')), array('link' => '#', 'page' => lang('classes')));
        $meta = array('page_title' => lang('classes'), 'bc' => $bc);
        $this->page_construct('gym/classes', $meta, $this->data);
    }
    public function getClasses($biller_id = false)
    {   
        $this->bpas->checkPermissions('index', true, 'class');
        $this->load->library('datatables');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_class") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_class/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        
        $time_table  = '<a href="'.admin_url('gym/time_tables/$1').'" ><i class="fa-regular fa-eye"></i></a>';
        $edit_link   = '<a href="'.admin_url('gym/edit_class/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i></a>';

        $action_link = "<div class=\"text-center\"> {$time_table} {$edit_link} {$delete_link}</div>";


        $this->datatables
            ->select("
                    sh_classes.id as id, 
                    sh_classes.code,
                    sh_classes.name,
               
                    sh_classes.description")
            ->from("sh_classes")
            ->join("sh_teachers","sh_teachers.id = sh_classes.teacher_id","left")
            ->add_column("Actions", $action_link, "id");
             $this->datatables->where('sh_classes.type','gym');
            
            if ($biller_id) {
                $this->datatables->where('sh_classes.biller_id', $biller_id);
            }else if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
                $this->datatables->where('sh_classes.biller_id =', $this->session->userdata('biller_id'));
            }
            
        echo $this->datatables->generate();
    }
    public function add_class()
    {
        $this->bpas->checkPermissions('add', true, 'class'); 
        $post = $this->input->post();
        $this->form_validation->set_rules('code', lang("code"), 'is_unique[sh_classes.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {    
            $data = array(
                'biller_id'   => $post['biller'],
                'code'        => $post['code'],
                'name'        => $post['name'],
                'program_id'  => $post['program'],
                'grade_id'    => $post['grade'],
                'skill_id'    => $post['skill'] ? $post['skill'] : null,
                'timeshift_id'=> $post['timeshift'] ? $post['timeshift'] : null,
                'description' => $this->bpas->clear_tags($post['description']),
                'teacher_id'  => $post['teacher'],
                'type'        => 'gym',
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }   

        if ($this->form_validation->run() == true && $id = $this->gym_model->addClass($data)) {
            $this->session->set_flashdata('message', $this->lang->line("class_added"));
            admin_redirect("gym/classes");
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['programs'] = $this->gym_model->getPrograms();
            $this->data['skills'] = $this->gym_model->getSkills();
            $this->data['grades'] = $this->gym_model->getGrades();
            $this->data['trainers']     = $this->gym_model->getAllTrainers();
            $this->load->view($this->theme . 'gym/add_class', $this->data); 
        }   
    }
    
    public function edit_class($id = null)
    {       
        $this->bpas->checkPermissions('edit', true, 'class');
        $post = $this->input->post();       
        $class_info = $this->gym_model->getClassByID($id);  
        if ($post && $post['code'] != $class_info->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[sh_classes.code]');
        }
        $this->form_validation->set_rules('name', lang("name"), 'required');

        if ($this->form_validation->run() == true) 
        {                       
            $data = array(
                'biller_id'    => $post['biller'],
                'code'         => $post['code'],
                'name'         => $post['name'],
                'program_id'   => $post['program'],
                'grade_id'     => $post['grade'],
                'skill_id'     => $post['skill'] ? $post['skill'] : null,
                'timeshift_id' => $post['timeshift'] ? $post['timeshift'] : null,
                'description'  => $this->bpas->clear_tags($post['description']),
                'teacher_id'  => $post['teacher'],
                'type'        => 'gym',
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $id = $this->gym_model->updateClass($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("class_updated"));
            admin_redirect("gym/classes");
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['id']=$id;
            $this->data['row'] = $class_info;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['trainers']         = $this->gym_model->getAllTrainers();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/edit_class', $this->data);
        }           
    }
    
    public function delete_class($id = null)
    {   
        $this->bpas->checkPermissions('delete', true, 'class');
        if (isset($id) || $id != null){         
            if($this->gym_model->deleteClassByID($id)){
                $this->session->set_flashdata('message', lang("class_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    
    function class_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');

        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('classes');
                    foreach ($_POST['val'] as $id) {
                        $this->gym_model->deleteClassByID($id);
                    }
                    $this->session->set_flashdata('message', lang("class_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('class');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('program'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('grade'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('description'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $class = $this->gym_model->getClassByID($id);
                        $program = $this->gym_model->getProgramByID($class->program_id);
                        $grade = $this->gym_model->getGradeByID($class->grade_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $class->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $class->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $program->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $grade->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->bpas->remove_tag($class->description));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);


                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'class_list_' . date('Y_m_d_H_i_s');
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
    public function getCategories()
    {   
        $this->bpas->checkPermissions('index', true, 'category'); 
        $this->load->library('datatables');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_category") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_category/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_category') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('gym/edit_category/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_category').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        $this->datatables
            ->select("
                        sh_skills.id as id, 
                        sh_skills.name,
                        sh_skills.description
                    ")
            ->from("sh_skills")
            ->join("sh_colleges", "sh_colleges.id=sh_skills.college_id", "left")
            ->order_by('sh_skills.id')
            ->order_by('sh_colleges.id')
            ->add_column("Actions", $action_link, "id");
        $this->datatables->where('sh_skills.type','gym');
        echo $this->datatables->generate();
    }
    
    public function add_category()
    {
        $this->bpas->checkPermissions('add', true, 'category');  
        $post = $this->input->post();
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true){
            $data = array(
                'type'        => 'gym',
                'name'        => $post['name'],
                'description' => $this->bpas->clear_tags($post['description']),
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }   
        if ($this->form_validation->run() == true && $id = $this->gym_model->addSkill($data)) {
            $this->session->set_flashdata('message', $this->lang->line("category_added"));
            admin_redirect("gym/categories");
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['colleges'] = $this->gym_model->getColleges();
            $this->load->view($this->theme . 'gym/add_category', $this->data); 
        }   
    }
    
    public function edit_category($id = null)
    {       
        $this->bpas->checkPermissions('edit', true, 'category'); 
        $post = $this->input->post();       
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if($this->form_validation->run() == true){                      
            $data = array(
                'name'        => $post['name'],
                'description' => $this->bpas->clear_tags($post['description']),
            );
        }elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
        if($this->form_validation->run() == true && $id = $this->gym_model->updateSkill($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line("category_updated"));
            admin_redirect("gym/categories");
        }else{
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['id']       = $id;
            $this->data['row']      = $this->gym_model->getSkillByID($id);
            $this->data['colleges'] = $this->gym_model->getColleges();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/edit_category', $this->data);
        }           
    }
    
    public function delete_category($id = null)
    {   
        $this->bpas->checkPermissions('delete', true, 'category'); ;
        if (isset($id) || $id != null){         
            if($this->gym_model->deleteSkill($id)){
                $this->session->set_flashdata('message', lang("category_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    
    function categories_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('skills');
                    foreach ($_POST['val'] as $id) {
                        $this->gym_model->deleteSkill($id);
                    }
                    $this->session->set_flashdata('message', lang("skill_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Category');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('description'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $skill   = $this->gym_model->getSkillByID($id);
                        $college = $this->gym_model->getCollegeByID($skill->college_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $skill->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $skill->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->remove_tag($skill->description));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'Category_List_' . date('Y_m_d_H_i_s');
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
    public function activity()
    {   
        // $this->bpas->checkPermissions('activity');  
        $this->bpas->checkPermissions('index', true, 'activitys');
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('gym'), 'page' => lang('gym')), array('link' => '#', 'page' => lang('activity')));
        $meta = array('page_title' => lang('activity'), 'bc' => $bc);
        $this->page_construct('gym/activity', $meta, $this->data);
    }

    public function getActivitys()
    {   
        $this->bpas->checkPermissions('index', true, 'activitys');
        $this->load->library('datatables');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_activity") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_activity/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_activity') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('gym/edit_activity/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_activity').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        $this->datatables
            ->select("
                    sh_subjects.id as id, 
                    sh_subjects.name,
                    sh_skills.name as skill,
                    sh_subjects.description,
                    
                ")
            ->from("sh_subjects")
            ->join("sh_skills", "sh_skills.id=sh_subjects.skill_id", "left")
            ->order_by('sh_subjects.code')
            ->order_by('sh_skills.id')
            ->add_column("Actions", $action_link, "id");
        $this->datatables->where('sh_subjects.type','gym');
        echo $this->datatables->generate();
    }
    public function add_activity()
    {
        $this->bpas->checkPermissions('add', true, 'activitys');
        $post = $this->input->post();
        $this->form_validation->set_rules('category', lang("category"), 'required');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) 
        {   
            $data = array(
                'skill_id'    => $post['category'],
                'type'        => 'gym',
                'name'        => $post['name'],
                'description' => $this->bpas->clear_tags($post['description']),
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }   

        if ($this->form_validation->run() == true && $id = $this->gym_model->addSubject($data)) {
            $this->session->set_flashdata('message', $this->lang->line("activity_added"));
            admin_redirect("gym/activity");
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['categories']   = $this->gym_model->getActivityCategories();
            $this->load->view($this->theme . 'gym/add_activity', $this->data);   
        }   
    }
    public function edit_activity($id = null)
    {       
        $this->bpas->checkPermissions('edit', true, 'activitys');
        $post = $this->input->post();       
         
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('category', lang("category"), 'required');
        if ($this->form_validation->run() == true) 
        {                       
            $data = array(
                'skill_id'    => $post['category'],
                'name'        => $post['name'],
                'description' => $this->bpas->clear_tags($post['description']),
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $id = $this->gym_model->updateSubject($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("activity_updated"));
            admin_redirect("gym/activity");
        }else{
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['id']       = $id;
            $this->data['row']      = $this->gym_model->getActivityByID($id); 
            $this->data['categories']   = $this->gym_model->getActivityCategories();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/edit_activity', $this->data);
        }           
    }
    
    public function delete_activity($id = null)
    {   
        $this->bpas->checkPermissions('delete', true, 'activitys');
        if (isset($id) || $id != null){         
            if($this->gym_model->deleteActivityByID($id)){
                $this->session->set_flashdata('message', lang("activity_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    
    function subject_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('subjects');
                    foreach ($_POST['val'] as $id) {
                        $this->gym_model->deleteSubjectByID($id);
                    }
                    $this->session->set_flashdata('message', lang("subject_deleted"));
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                } else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('subject');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('skill'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $subject = $this->gym_model->getSubjectByID($id);
                        $skill   = $this->gym_model->getSkillByID($subject->skill_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $subject->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $subject->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->bpas->remove_tag($subject->description));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($skill ? $skill->name : ''));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(65);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'subject_list_' . date('Y_m_d_H_i_s');
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
    public function categories()
    {   
        $this->bpas->checkPermissions('index', true, 'category'); 
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'gym', 'page' => lang('gym')), array('link' => '#', 'page' => lang('categories')));
        $meta = array('page_title' => lang('categories'), 'bc' => $bc);
        $this->page_construct('gym/categories', $meta, $this->data);
    }
    public function sales($biller_id = null){
        $this->bpas->checkPermissions('index', true, 'sales'); 
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

        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $this->data['users']            = $this->site->getStaff();
        $this->data['products']         = $this->site->getProducts();
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $this->data['count_warehouses'] = explode(',', $this->session->userdata('warehouse_id'));
     

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('gym/sales', $meta, $this->data);
    }
    public function getSales($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'sales'); 
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
        $warehouse      = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $delivered_by   = $this->input->get('delivered_by') ? $this->input->get('delivered_by') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : NULL;
        $start_date     = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date       = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $a              = $this->input->get('a') ? $this->input->get('a') : null;

        if ($start_date) {
            $start_date = $this->bpas->fld($start_date . ' 00:00:00');
            $end_date   = $this->bpas->fld($end_date . ' 23:59:00');
        }
        $view_logo        = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $return_detail_link   = anchor('admin/gym/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/gym/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');


        $email_link           = anchor('admin/gym/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/gym/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'));
        
        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/gym/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
      
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $view_logo . '</li>';
     
         
            $action .= '
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $return_link . '</li>
                <li class="delete">' . $delete_link . '</li>
        </ul>
        </div></div>';


        $this->load->library('datatables');
        $this->datatables
        ->select("{$this->db->dbprefix('sales')}.id as id, 
            DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, 
            CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
            {$this->db->dbprefix('sales_order')}.reference_no as sr_ref, 
            {$this->db->dbprefix('sales')}.reference_no, 
            {$this->db->dbprefix('sales')}.biller, 
            {$this->db->dbprefix('sales')}.customer, 
            {$this->db->dbprefix('sales')}.sale_status, 
            {$this->db->dbprefix('sales')}.grand_total, 
            {$this->db->dbprefix('sales')}.paid, 
            ({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) as balance,
            {$this->db->dbprefix('sales')}.payment_status,
            {$this->db->dbprefix('sales')}.return_id")
        ->join('projects', 'sales.project_id = projects.project_id', 'left')
        ->join('sales_order', 'sales.so_id = sales_order.id', 'left')
        ->join('users', 'sales.saleman_by = users.id', 'left') 
        ->order_by('sales.id', 'desc')
        ->from('sales')
        ->where('sales.store_sale !=', 1); 
        $this->datatables->where('sales.module_type','gym');
        $this->datatables->where('sales.hide', 1);
        if ($biller_id) {
            $this->datatables->where_in('sales.biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id','bpas_projects.customer_id');
        }
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }
        if ($user_query) {
            $this->datatables->where('sales.created_by', $user_query);
        }
        if ($payment_status) {
            $get_status = explode('_', $payment_status);
            $this->datatables->where_in('sales.payment_status', $get_status);
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
        if ($saleman_by) {
            $this->datatables->where('sales.saleman_by', $saleman_by);
        }
        if ($warehouse) {
            $this->datatables->where('sales.warehouse_id', $warehouse);
        } 
        // if ($start_date ) {
        //  $pp .= " AND p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
        // $this->datatables->where($this->db->dbprefix('sales').'.date BETWEEN "' . $start_date . ' 00:00:00" and "' . $end_date . '23:59:00"');
        // $this->datatables->where("sales.date>='{$start_date}'AND sales.date < '{$end_date}'");
        // } 
        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;
            if (count($alert_ids) > 1) {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where_in('sales.id', $alert_ids);
            } else {
                // $this->datatables->where('sales.payment_term <>', 0);
                // $this->datatables->where('DATE_SUB(bpas_sales.date, INTERVAL 1 DAY) <= CURDATE()');
                $this->datatables->where('sales.id', $alert_id);
            }
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->where($this->db->dbprefix('sales') . '.pos !=', 1);
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_sale($sale_order_id = null, $quote_id = null)
    {   
        $this->bpas->checkPermissions('add', true, 'sales'); 
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $getexchange_bat = $this->bpas->getExchange_rate('BAHT');
        $exchange_khm    = $getexchange_khm->rate;
        $exchange_bat    = $getexchange_bat->rate;

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');

            // $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('so'));

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            }else{
                $due_date         = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $text_items = "";
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = []; 
            $i                = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0; 
            for ($r = 0; $r < $i; $r++) { 
                $item_id            = $_POST['product_id'][$r];
                $item_price         = $_POST['price'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_period_type   = $_POST['product_period_type'][$r];
                $item_start_time    = $this->bpas->fld(trim($_POST['start_time'][$r]));
                $item_end_time      = $this->bpas->fld(trim($_POST['end_time'][$r]));
                $item_quantity      = $_POST['quantity'][$r];
                // $item_unit_quantity = $_POST['product_base_quantity'][$r];
                 $item_unit_quantity = $_POST['quantity'][$r];
                $item_net_price     = $_POST['price'][$r];
                $product_type       = 'standard'; 
                if (isset($item_name) && isset($item_price) && isset($item_quantity)) {
                    $cost = 0;
                    $subtotal = $item_price * $item_quantity;
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_name,
                        'product_name'      => $item_name,
                        'start_time'        => $item_start_time,
                        'end_time'          => $item_end_time,
                        'period_type'        => $item_period_type,
                        'net_unit_price'    => $item_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_price),
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal)
                    ];
               
                    //========add accounting=========//
    
                    if($this->Settings->accounting == 1 && ($sale_status=='completed')){
                        $getproduct = $this->site->getProductByID($item_id);
                            $default_sale  = $this->accounting_setting->default_sale;
                            $inventory_acc = $this->accounting_setting->default_stock;
                            $costing_acc   = $this->accounting_setting->default_cost;
                        
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),

                        );
                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );

                        $accTrans[] = array(
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' => $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
              
                    }
                    //============end accounting=======//

                    $products[] = $product;
                    $total += $this->bpas->formatDecimal(($item_net_price * $item_quantity), 4);
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
            $saleman_award_points = 0;
            $user = $this->site->getUser($this->session->userdata('user_id'));

            //=======acounting=========//
            if($this->Settings->accounting == 1){
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }

            //============end accounting=======//

            $data       = [
                'date'                => $date,
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
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
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $this->input->post('saleman_by'),
                'currency_rate_kh'    => $exchange_khm,
                'module_type'         => 'gym'
            ];

            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
      
                $payment = [
                    'date'         => $date,
                    'reference_no' => $this->input->post('payment_reference_no'),
                    'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                    'paid_by'      => $this->input->post('paid_by'),
                    'cheque_no'    => $this->input->post('cheque_no'),
                    'cc_no'        => $this->input->post('pcc_no'),
                    'cc_holder'    => $this->input->post('pcc_holder'),
                    'cc_month'     => $this->input->post('pcc_month'),
                    'cc_year'      => $this->input->post('pcc_year'),
                    'cc_type'      => $this->input->post('pcc_type'),
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => $this->input->post('payment_note'),
                    'type'         => 'received',
                ];
                
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                    //=====add accountig=====//
                if($this->Settings->accounting == 1){
                    if($this->input->post('paid_by') == 'deposit'){
                        $payment['bank_account'] = $this->accounting_setting->default_sale_deposit;
                        $paying_to = $this->accounting_setting->default_sale_deposit;
                    }else{
                        $payment['bank_account'] = $this->input->post('bank_account');
                        $paying_to = $this->input->post('bank_account');
                    }

                    if($amount_paying < $grand_total){
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => ($grand_total - $amount_paying),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    } 
                    $accTranPayments[] = array(
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'account_code' => $paying_to,
                        'amount' => $amount_paying,
                        'narrative' => $this->site->getAccountName($paying_to),
                        'description' => $this->input->post('payment_note'),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                //=====end accountig=====//
            } else {
                $accTranPayments= [];
                $payment = [];
                $accTrans[] = array(
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
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
            //----checked orver credit--------
            $cus_sales = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit != null) && (($cus_sales->total_amount - $cus_sales->paid) + $data['grand_total'] - (($payment_status == 'partial' || $payment_status == 'paid') ? $payment['amount'] : 0)) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            } 
        }
        if ($this->form_validation->run() == true && $this->gym_model->generate_membership_invoice($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null,'')) {
            $this->session->set_userdata('remove_gymls', 1);
            $this->session->set_flashdata('message', lang('generate_membership_added'));
            admin_redirect('gym/sales');
        } else {
           
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']      = $quote_id ? $quote_id : $sale_order_id;
            $this->data['sale_order_id'] = $sale_order_id;
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $companyID                   = explode(',',$this->data['data']->multi_biller);
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', $companyID);
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['zones']         = $this->site->getAllZones();
            $this->data['group_price']   = json_encode($this->site->getAllGroupPrice());
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id); 
            $this->data['gymnumber']      = $this->site->getReference('so');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = ''; //$this->site->getReference('pay');
            $this->data['customers']     = $this->site->getCustomers();
            $user = $this->site->getUser($this->session->userdata('user_id'));
           
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                        = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('gym/add_sale', $meta, $this->data);
        }
    }


    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        if (empty($inv)) {
            $this->session->set_flashdata('error', lang('unable_to_deleted'));
            admin_redirect('gym/sales');
        }
        if ($inv->sale_status == 'returned') {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
        }
        if ($inv->return_id) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            if ($this->Settings->hide != 0) {
                if ($this->sales_model->deleteSale($id)) {
                    $this->session->set_flashdata('message', lang('sale_deleted'));
                    admin_redirect('gym/sales');
                }
            } else {
                if ($this->sales_model->removeSale($id)) {
                    $this->session->set_flashdata('message', lang('sale_removed'));
                    admin_redirect('gym/sales');
                }   
            }
        }
    }



    public function edit($id = null){
        $this->bpas->checkPermissions('edit', true, 'sales'); 
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);

        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->saleman_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        // $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
      
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse'); 
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            }else{
                $due_date         = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product = 0;
            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0; 
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_period_type   = $_POST['product_period_type'][$r] ? $_POST['product_period_type'][$r] : 'days'; 
                $item_start_time    = $this->bpas->fld(trim($_POST['start_time'][$r]));
                $item_end_time      = $this->bpas->fld(trim($_POST['end_time'][$r]));
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : ''; 
                if (isset($item_code) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null; 
                    $cost = (!empty($product_details) ? $product_details->cost : 0);
                    if ($item_type == 'digital') {
                        $digital = true;
                    } 
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax      = $item_tax = 0;
                    $tax              = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $commission_item = (!empty($product_details) ? $this->site->getProductCommissionByID($product_details->id) : null);
                    $purchase_unit_cost = (!empty($product_details) ? $product_details->cost : 0);
                    if (!empty($product_details) && ($unit->id != $product_details->unit)) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost,$unit);
                    } else {
                        $cost = $cost;
                    }
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'start_time'        => $item_start_time,
                        'end_time'          => $item_end_time,
                        'period_type'       => $item_period_type,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
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
                        'product_type'      => 'service',
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                        'warranty'          => $item_warranty,
                        'comment'           => $item_detail,
                        'commission'        => isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0,
                        ];
                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0;
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
                        $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' =>$this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_unit_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost),
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => - $subtotal,
                            'narrative' =>  $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
            
                    }
                    //============end accounting=======//
                    $products[] = ($product + $gst_data);
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
            $saleman_award_points = 0;
            $staff = $this->site->getUser($inv->saleman_by);
          

            //=======acounting=========//
            if($this->Settings->accounting == 1){

                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => $order_discount,
                        'narrative' => 'Order Discount',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                         'activity_type' => 0
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => -$order_tax,
                        'narrative' => 'Order Tax',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_freight,
                        'amount' => -$shipping,
                        'narrative' => 'Shipping',
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0
                    );
                }
            }
            //============end accounting=======//

            $data   = ['date' => $date,
                'project_id'          => $this->input->post('project'),
                'reference_no'        => $reference,
                'po_number'           => $this->input->post('po'),
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
                'sale_status'         => $sale_status,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'updated_by'          => $this->session->userdata('user_id'),
                'saleman_by'          => $this->input->post('saleman_by'),
                'zone_id'             => $this->input->post('zone_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'saleman_award_points'=> $saleman_award_points,
                'from_id'             => $this->input->post('from'),
                'date_out'            => $this->input->post('date_out') ? $this->bpas->fsd($this->input->post('date_out')) : null,
                'time_out_id'         => $this->input->post('time_out'),
                'destination_id'      => $this->input->post('destination'),
            ];
   
            if($payment_status != 'paid'){
                if ($payment_status == 'partial') {
                    if ($this->input->post('paid_by') == 'deposit') {
                        if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                            $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    if ($this->input->post('paid_by') == 'gift_card') {
                        $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                        $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                        $gc_balance    = $gc->balance - $amount_paying;
                        $payment       = [
                            'date'         => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount'       => $this->bpas->formatDecimal($amount_paying),
                            'paid_by'      => $this->input->post('paid_by'),
                            'cheque_no'    => $this->input->post('cheque_no'),
                            'cc_no'        => $this->input->post('gift_card_no'),
                            'cc_holder'    => $this->input->post('pcc_holder'),
                            'cc_month'     => $this->input->post('pcc_month'),
                            'cc_year'      => $this->input->post('pcc_year'),
                            'cc_type'      => $this->input->post('pcc_type'),
                            'created_by'   => $this->session->userdata('user_id'),
                            'note'         => $this->input->post('payment_note'),
                            'type'         => 'received',
                            'gc_balance'   => $gc_balance,
                        ];
                    } else {
                        $payment = [
                            'date'         => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'amount'       => $this->bpas->formatDecimal($this->input->post('amount-paid')),
                            'paid_by'      => $this->input->post('paid_by'),
                            'cheque_no'    => $this->input->post('cheque_no'),
                            'cc_no'        => $this->input->post('pcc_no'),
                            'cc_holder'    => $this->input->post('pcc_holder'),
                            'cc_month'     => $this->input->post('pcc_month'),
                            'cc_year'      => $this->input->post('pcc_year'),
                            'cc_type'      => $this->input->post('pcc_type'),
                            'created_by'   => $this->session->userdata('user_id'),
                            'note'         => $this->input->post('payment_note'),
                            'type'         => 'received',
                        ];
                    }
                    $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
                        //=====add accountig=====//
                    if($this->Settings->accounting == 1){
                        if($amount_paying < $grand_total){
                            $accTranPayments[] = array(
                                'tran_type' => 'Payment',
                                'tran_date' => $date,
                                'reference_no' => $this->input->post('payment_reference_no'),
                                'account_code' => $this->accounting_setting->default_receivable,
                                'amount' => ($grand_total - $amount_paying),
                                'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                                'description' => $this->input->post('payment_note'),
                                'biller_id' => $biller_id,
                                'project_id' => $project_id,
                                'customer_id' => $customer_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                            );
                        }
                        if($this->input->post('paid_by') == 'deposit'){
                            $paying_to = $this->accounting_setting->default_sale_deposit;
                        }else{
                            $paying_to = $this->input->post('bank_account');
                        }
                        $accTranPayments[] = array(
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $this->input->post('payment_reference_no'),
                            'account_code' => $paying_to,
                            'amount' => $amount_paying,
                            'narrative' => $this->site->getAccountName($paying_to),
                            'description' => $this->input->post('payment_note'),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );
                        $accTrans[]=array();
                    }
                        //=====end accountig=====//
                } else {
                    $payment = [];
                    $accTranPayments[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => $grand_total,
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payment_id' => $id,
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                }
            }else{
                $accTranPayments[] = array(
                    'tran_no' => $id,
                    'tran_type' => 'Sale',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $this->accounting_setting->default_receivable,
                    'amount' => $grand_total,
                    'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id' => $biller_id,
                    'project_id' => $project_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                    'payment_id' => $id,
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
               // $accTrans=[];
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


            //----checked orver credit--------
            $cus_sales         = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) - $inv->grand_total + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
            
        if ($this->form_validation->run() == true && $this->gym_model->update_membership_invoice($id, $data, $products,'','','')) {
            $this->session->set_userdata('remove_gymls', 1);
            $this->session->set_flashdata('message', lang('membership_invoice_updated'));
             admin_redirect('gym/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
                // krsort($inv_items);
            $c = rand(100000, 9999999);
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
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id              = $item->product_id;
                $row->start_time      = $this->bpas->hrld($item->start_time);
                $row->end_time        = $this->bpas->hrld($item->end_time);
                $row->period_type     = $item->period_type;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity        = $item->quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax > 0 ? $item->item_tax / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                $row->unit_price    = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = '';
                $row->serial_no       = $item->serial_no;
                $row->max_serial      = $item->max_serial;
                $row->warranty        = $item->warranty;
                $row->option          = $item->option_id;
                $row->addition_type   = $item->addition_type;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                $product_options      = $this->site->getAllProductOption($row->id);
                $row->details         = $item->comment;
                $row->option_name     = $item->option_name;
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        $option_quantity += $item->quantity;
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                } 
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                } 
                $categories           = $this->site->getCategoryByID($cate_id);
                $fiber_type           = $this->sales_model->getFiberTypeById($row->id); 
                $fibers = array('fiber' => $categories, 'type' => $fiber_type, ); 
                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,  'units' => $units, 'options' => $options, 'fiber' => $fibers,'product_options' => $product_options, ];
                $c++;
            } 
            $this->data['count']        = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']     = $this->site->getAllProject();
            $this->data['inv_items']    = json_encode($pr); 
            $this->data['id']           = $id;
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['agencies']     = $this->site->getAllUsers();
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers']      = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']        = $this->site->getAllBaseUnits();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['zones']        = $this->site->getAllZones();
            $Settings                   = $this->site->getSettings();
            $this->data['salemans']     = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('membership')], ['link' => '#', 'page' => lang('edit_membership')]];
            $meta = ['page_title' => lang('edit_membership'), 'bc' => $bc];
            $this->session->set_userdata('seen_edit_gymls', 1);
            $this->page_construct('gym/edit_sale', $meta, $this->data);
        }
    }
    public function time_tables($class_id = NULL)
    {
        $this->bpas->checkPermissions('time_tables', true, 'class');
        $this->form_validation->set_rules('subject', lang("subject"), 'required');
        $this->form_validation->set_rules('day', lang("day"), 'required');
        $this->form_validation->set_rules('start_time', lang("start_time"), 'required');
        $this->form_validation->set_rules('end_time', lang("end_time"), 'required');
        
        if ($this->form_validation->run() == true) {
            $this->bpas->checkPermissions('time_tables', true, 'class');
            $section = $this->input->post('section');
            $subject = $this->input->post('subject');
            $teacher = $this->input->post('teacher');
            $room = $this->input->post('room');
            $day = $this->input->post('day');

            $academic_year = $this->input->post('academic_year');
            $start_time = $this->input->post('start_time');
            $end_time = $this->input->post('end_time');

            $data = array(
                    'class_id'      => $class_id,
                    'section_id'    => $section,
                    'subject_id'    => $subject,
                    'teacher_id'    => $teacher,
                    'day_name'      => $day,
                    'start_time'    => trim($start_time),
                    'end_time'      => trim($end_time)
                );
        }

        if ($this->form_validation->run() == true && $this->gym_model->addTimeTable($data)) {
            $this->session->set_flashdata('message', lang("time_table_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $class = $this->gym_model->getClassByID($class_id);
            $this->data['class']    = $class;
            $this->data['rooms']    = $this->gym_model->getRooms($class->biller_id);
            $this->data['class_id'] = $class_id;
            $this->data['grade_id'] = $class->grade_id; 
            $this->data['activities']      = $this->gym_model->getActivities();
            $this->data['trainers']         = $this->gym_model->getAllTrainers();

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('schools'), 'page' => lang('schools')), array('link' => admin_url('schools/classes'), 'page' => lang('classes')), array('link' => '#', 'page' => lang('time_tables')));
            $meta = array('page_title' => lang('time_tables'), 'bc' => $bc);
            $this->page_construct('gym/time_tables', $meta, $this->data);
        }
    }
    
    public function getTimeTables()
    {
        $this->bpas->checkPermissions('time_tables', true, 'class');
        $this->load->library('datatables');
        
        $delete_link = "<a href='#' class='po' title='" . lang("delete_time_table") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_time_table/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_time_table') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('gym/edit_time_table/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_time_table').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        
        $this->datatables
        ->select("
                sh_subjects.name as subject,
                concat(".$this->db->dbprefix('sh_teachers').".lastname,' ',".$this->db->dbprefix('sh_teachers').".firstname) as teacher, 
                sh_table_times.day_name,
                sh_table_times.start_time,
                sh_table_times.end_time,
                sh_table_times.id as id
            ", false)
        ->from('sh_table_times')
        ->join('sh_sections', 'sh_sections.id=sh_table_times.section_id', 'left')
        ->join('sh_subjects', 'sh_subjects.id=sh_table_times.subject_id', 'left')
        ->join('sh_rooms', 'sh_rooms.id=sh_table_times.room_id', 'left')
        ->join('sh_teachers', 'sh_teachers.id=sh_table_times.teacher_id', 'left')
        // ->where('sh_table_times.class_id', $class_id)
        ->add_column("Actions", $action_link, "id")
        ->unset_column('id');
        echo $this->datatables->generate();
        
    }
    public function edit_time_table($id = null)
    {       
        $this->bpas->checkPermissions('time_tables',true);
        $post = $this->input->post();
        $time_table = $this->schools_model->getTimeTableByID($id);  

        $this->form_validation->set_rules('activity', lang("activity"), 'required');
        $this->form_validation->set_rules('teacher', lang("teacher"), 'required');
        $this->form_validation->set_rules('po_day', lang("day"), 'required');
        $this->form_validation->set_rules('po_start_time', lang("start_time"), 'required');
        $this->form_validation->set_rules('po_end_time', lang("end_time"), 'required');

        if ($this->form_validation->run() == true) {    
        
            $data = array(   
                            'subject_id'=> $post['activity'],
                            'teacher_id'=> $post['teacher'],
                            'day_name'  => $post['po_day'],
                            'start_time'=> trim($post['po_start_time']),
                            'end_time'  => trim($post['po_end_time'])
                        );
            
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->schools_model->updateTimeTable($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("time_table_updated"));
            admin_redirect("gym/time_tables/".$time_table->class_id);
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $class = $this->schools_model->getClassByID($time_table->class_id);
            $this->data['time_table'] = $time_table;
            $this->data['rooms'] = $this->schools_model->getRooms();
            $this->data['id'] = $id;
            $this->data['grade_id'] = $class->grade_id;
            
            $this->data['activities']      = $this->gym_model->getActivities();
            $this->data['trainers']         = $this->gym_model->getAllTrainers();

            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/edit_time_table', $this->data);
        }           
    }
    public function delete_time_table($id = null)
    {   
        $this->bpas->checkPermissions('time_tables');
        if (isset($id) || $id != null){         
            if($this->gym_model->deleteTimeTableByID($id)){
                $this->session->set_flashdata('message', lang("time_table_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    public function assign_service($package_id = NULL)
    {
        $this->bpas->checkPermissions('assign_service');
        $this->form_validation->set_rules('package', lang("package"), 'required');
        $this->form_validation->set_rules('subject', lang("subject"), 'required');
        $this->form_validation->set_rules('qty', lang("qty"), 'required');
        $this->form_validation->set_rules('description', lang("description"), '');

        if ($this->form_validation->run() == true) {

            $package        = $this->input->post('package');
            $subject        = $this->input->post('subject');
            $qty            = $this->bpas->formatNumber($this->input->post('qty'));
            $description    = $this->bpas->clear_tags($this->input->post('description'));

            $data = array(
                        'package_id' => $package,
                        'service_id' => $subject,
                        'qty'        => $qty,
                        'description'=> $description
                        );
        }

        if ($this->form_validation->run() == true && $this->schools_model->addServicePackage($data)) {
            $this->session->set_flashdata('message', lang("service_package_added"));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $class = $this->schools_model->getPackageByID($package_id);
            $this->data['class'] = $class;
            $this->data['package_id'] = $package_id; 
            $this->data['subjects']      = $this->schools_model->getSubjects();
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('gym'), 'page' => lang('gym')), array('link' => admin_url('gym/classes'), 'page' => lang('classes')), array('link' => '#', 'page' => lang('assign_package')));
            $meta = array('page_title' => lang('assign_package'), 'bc' => $bc);
            $this->page_construct('gym/assign_package', $meta, $this->data);
        }
    }
    public function getServicePackage($package_id=false)
    {
        $this->bpas->checkPermissions('time_tables');
        $this->load->library('datatables');
        
        $delete_link = "<a href='#' class='po' title='" . lang("delete_assign_service") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_assign_service/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_assign_service') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('gym/edit_assign_service/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_assign_service').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        
        if($package_id){
            $this->datatables->where('package_id',$package_id);
        }
        $this->datatables->select("
            customer_package.name as package,
            sh_subjects.name as service,
            service_package.qty as qty,
            {$this->db->dbprefix('service_package')}.description as description, 
            service_package.id as id", false)
        ->from('service_package')
        ->join('customer_package', 'customer_package.id=service_package.package_id', 'left')
        ->join('sh_subjects', 'sh_subjects.id=service_package.service_id', 'left')
        ->add_column("Actions", $action_link, "id")
        ->unset_column('id');
        echo $this->datatables->generate();
        
    }
     public function edit_assign_service($id = null)
    {       
        $this->bpas->checkPermissions('assign_service',true);
        $post = $this->input->post();
        $assign = $this->schools_model->getServicePackageByID($id);  
        $this->form_validation->set_rules('subject', lang("subject"), 'required');
        $this->form_validation->set_rules('qty', lang("qty"), 'required');
        $this->form_validation->set_rules('description', lang("description"), '');

        if ($this->form_validation->run() == true) {    
            $package        = $this->input->post('package');
            $subject        = $this->input->post('subject');
            $qty            = $this->bpas->formatNumber($this->input->post('qty'));
            $description    = $this->bpas->clear_tags($this->input->post('description'));

            $data = array(
                        'package_id' => $package,
                        'service_id' => $subject,
                        'qty'        => $qty,
                        'description'=> $description
                        );
            
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->schools_model->updateAssignService($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("assgin_service_updated"));
            admin_redirect("gym/assign_service/".$assign->package_id);
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['assign'] = $assign;
            $this->data['id'] = $id;
            $this->data['subjects']      = $this->schools_model->getSubjects();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/edit_assign_service', $this->data);
        }           
    }
    public function delete_assign_service($id = null)
    {   
        $this->bpas->checkPermissions('assign_service');
        if (isset($id) || $id != null){         
            if($this->schools_model->deleteAssginServiceByID($id)){
                $this->session->set_flashdata('message', lang("assign_service_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    public function get_customer_package(){
        $biller_id = $this->input->get('biller_id');
        $customer_id = $this->input->get('customer_id');
        $status = "active";
        $package_info = $this->gym_model->get_package_by_customer($biller_id,$customer_id);
        $r = 0; $pr = array();
        foreach ($package_info as $package) {
            $c = uniqid(mt_rand(), true);
              $pr[] = [
                'id'        => sha1($c . $r), 
                'item_id'   => $package->id, 
                'label'     => $package->name . ' (' . $package->period_type . ')', 
                'quantity'  =>1,
                'row'       => $package
            ];
            $r++;
        }
        echo json_encode($pr);
    }

    function trainers($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'trainers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if(isset($action)) {
            $this->data['action'] = $action;
        }
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('trainers')));
        $meta = array('page_title' => lang('trainers'), 'bc' => $bc);
        $this->page_construct('gym/trainers', $meta, $this->data);
    }
    
    function getTrainers($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'trainers');
        $this->load->library('datatables');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_trainer") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_trainer/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_trainer') . "</a>";

        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('gym/edit_trainer/$1').'" ><i class="fa fa fa-edit"></i>'.lang('edit_trainer').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
                        
        $this->datatables
            ->select("
                sh_teachers.id as id,
                sh_teachers.photo,
                sh_teachers.code,
                sh_teachers.lastname,
                sh_teachers.firstname,
                sh_teachers.bank_account,
                sh_teachers.gender,
                sh_teachers.phone,      
                sh_teachers.status")
            ->from("sh_teachers")
            ->add_column("Actions", $action_link, "id");
        
        if ($biller_id) {
            $this->datatables->where('sh_teachers.biller_id', $biller_id);
        } 
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('sh_teachers.biller_id =', $this->session->userdata('biller_id'));
        }
        echo $this->datatables->generate();
    }
    function add_trainer()
    {
        $this->bpas->checkPermissions('add', true, 'trainers');  
        $post = $this->input->post();
        $this->form_validation->set_rules('code', lang("code"), 'is_unique[sh_teachers.code]');
        $this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
            $biller = $this->site->getCompanyByID($post['biller']);
            $area = $this->site->getZoneByID($post['area']);
            $data = array(
                'biller'         => $biller->name,
                'biller_id'      => $biller->id,
                'code'           => $post['code'],
                'date'           => $this->bpas->fsd(trim($post['date'])),
                'lastname'           => $post['last_name'],
                'firstname'          => $post['first_name'],
                'firstname_other'    => $post['firstname_other'],
                'lastname_other'     => $post['lastname_other'],
                'dob'            => $this->bpas->fsd(trim($post['dob'])),
                'pob'            => $post['pob'],               
                'gender'         => $post['gender'],
                'phone'          => $post['phone'],
                'email'          => $post['email'],
                'nationality'    => $post['nationality'],
                'bank_account'   => $post['bank_account'],
                'address'        => $post['address'].' '.$area->name,
                'created_at'     => date('Y-m-d H:i'),
                'created_by'     => $this->session->userdata('user_id'),
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
                $config['width'] = $this->Settings->twidth;
                $config['height'] = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
            }
            if($id = $this->schools_model->addTeacher($data)){
                $this->session->set_flashdata('message', lang("teacher_added"));
                admin_redirect('gym/trainers');
            }
        }else{
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));            
            $this->data['last_teacher'] = $this->schools_model->getLastTeacher();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['areas'] = $this->site->getAllZones();
            $bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('gym/trainers'), 'page' => lang('teachers')), array('link' => '#', 'page' => lang('add_teacher')));
            $meta = array('page_title' => lang('add_teacher'), 'bc' => $bc);
            $this->page_construct('gym/add_trainer', $meta, $this->data);
        }
    }
    
    function edit_trainer($id = false)
    {
        $this->bpas->checkPermissions('edit', true, 'trainers');
        $post = $this->input->post();
        $teacher = $this->schools_model->getTeacherByID($id);
        if(isset($post['code']) && $post['code'] != $teacher->code){
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[sh_teachers.code]');
        }
        $this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
            $biller = $this->site->getCompanyByID($post['biller']);
            $data = array(
                'biller'         => $biller->name,
                'biller_id'      => $biller->id,
                'code'           => $post['code'],
                'date'           => $this->bpas->fsd(trim($post['date'])),
                'lastname'           => $post['last_name'],
                'firstname'          => $post['first_name'],
                'firstname_other'    => $post['firstname_other'],
                'lastname_other'     => $post['lastname_other'],
                'dob'            => $this->bpas->fsd(trim($post['dob'])),       
                'pob'            => $post['pob'],
                'gender'         => $post['gender'],
                'phone'          => $post['phone'],
                'email'          => $post['email'],
                'nationality'    => $post['nationality'],
                'address'        => $post['address'],
                'bank_account'   => $post['bank_account'],
                'updated_at'     => date('Y-m-d H:i'),
                'updated_by'     => $this->session->userdata('user_id'),
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
        
        if ($this->form_validation->run() == true && $this->gym_model->updateTeacher($id, $data)) {
         
                $this->session->set_flashdata('message', lang("teacher_updated"));
                admin_redirect('gym/trainers');
            
        }else{
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));            
            $this->data['id'] = $id;
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['teacher'] = $teacher;
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['working_info'] = $this->schools_model->getTeachersWorkingInfoByEmployeeID($id);
            $this->data['types'] = $this->schools_model->getEmployeeTypes();
            $bc = array(array('link' => admin_url(), 'page' => lang('home')), array('link' => admin_url('gym/trainers'), 'page' => lang('trainers')), array('link' => '#', 'page' => lang('edit_trainer')));
            $meta = array('page_title' => lang('edit_trainer'), 'bc' => $bc);
            $this->page_construct('gym/edit_trainer', $meta, $this->data);
        }
    }
    public function delete_trainer($id = null)
    {       
        $this->bpas->checkPermissions('delete', true, 'trainees');
        if (isset($id) || $id != null){
            $delete = $this->gym_model->deleteTeacherByID($id);
            if($delete){
                $this->session->set_flashdata('message', lang("teacher_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
            else{
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }

    public function level()
    {   
        $this->bpas->checkPermissions('index', true, 'levels');
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('schools'), 'page' => lang('schools')), array('link' => '#', 'page' => lang('levels')));
        $meta = array('page_title' => lang('levels'), 'bc' => $bc);
        $this->page_construct('gym/levels', $meta, $this->data);
    }

    public function getLevels()
    {   
        $this->bpas->checkPermissions('index', true, 'levels');
        $this->load->library('datatables');

        $delete_link = "<a href='#' class='po' title='" . lang("delete_level") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_level/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_level') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                ';
                $action_link .= '   <li><a href="'.admin_url('gym/edit_level/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_level').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        $this->datatables
            ->select("
                    {$this->db->dbprefix('sh_grades')}.id as id, 
                    {$this->db->dbprefix('sh_grades')}.code,
                    {$this->db->dbprefix('sh_grades')}.name,
                    {$this->db->dbprefix('sh_grades')}.description")
            ->from("sh_grades")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
    }
    
    public function add_level()
    {
        $this->bpas->checkPermissions('add', true, 'levels'); 
        $post = $this->input->post();
        $this->form_validation->set_rules('code', lang("code"), 'is_unique[sh_grades.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) 
        {   
            $data = array(
                'code'  => $post['code'],
                'name'  => $post['name'],
                'description' => $this->bpas->clear_tags($post['description']),
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }   

        if ($this->form_validation->run() == true && $id = $this->schools_model->addGrade($data)) {
            $this->session->set_flashdata('message', $this->lang->line("level_added"));
            admin_redirect("gym/level");
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/add_level', $this->data); 
        }   
    }
    
    public function edit_level($id = null)
    {       
        $this->bpas->checkPermissions('edit', true, 'levels');
        $post = $this->input->post();       
        $grade_info = $this->gym_model->getLevelByID($id);  
        if ($post && $post['code'] != $grade_info->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[sh_grades.code]');
        }
        $this->form_validation->set_rules('name', lang("name"), 'required');

        if ($this->form_validation->run() == true) 
        {                       
            $data = array(
                'code'  => $post['code'],
                'name'  => $post['name'],
                'description' => $this->bpas->clear_tags($post['description']),
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $id = $this->schools_model->updateGrade($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("level_updated"));
            admin_redirect("gym/level");
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['id']=$id;
            $this->data['row'] = $grade_info;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/edit_level', $this->data);
        }           
    }
    
    public function delete_level($id = null)
    {   
        $this->bpas->checkPermissions('delete', true, 'levels');
        if (isset($id) || $id != null){         
            if($this->schools_model->deleteGradeByID($id)){
                $this->session->set_flashdata('message', lang("level_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    public function workouts($biller_id = false)
    {   
        $this->bpas->checkPermissions('index', true, 'workouts');
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('gym'), 'page' => lang('gym')), array('link' => '#', 'page' => lang('workouts')));
        $meta = array('page_title' => lang('workouts'), 'bc' => $bc);
        $this->page_construct('gym/workouts', $meta, $this->data);
    }
    public function getWorkouts($biller_id = false)
    {   
        $this->bpas->checkPermissions('index', true, 'workouts');
        $this->load->library('datatables');
        $delete_link = "<a href='#' class='po' title='" . lang("delete_workout") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('gym/delete_workout/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";
        
        $time_table  = '<a href="'.admin_url('gym/view_workout/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa-regular fa-eye"></i></a>';
        $edit_link   = '<a href="'.admin_url('gym/edit_workout/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i></a>';

        $action_link = "<div class=\"text-center\"> {$time_table} {$edit_link} {$delete_link}</div>";


        $this->datatables
            ->select("
                    gym_workouts.id as id, 
                    companies.name as trainee,
                    sh_grades.name as level,
                    gym_workouts.start_date,
                    gym_workouts.end_date,
                    CONCAT({$this->db->dbprefix('users')}.last_name, ' ',{$this->db->dbprefix('users')}.first_name) as created_by
            ")
            ->from("gym_workouts")
            ->join("companies","companies.id = gym_workouts.trainee_id","left")
            ->join("sh_grades","sh_grades.id = gym_workouts.level_id","left")
            ->join("users","users.id = gym_workouts.created_by","left")
            ->add_column("Actions", $action_link, "id");
           // $this->datatables->where('companies.group_name','customer');

        if ($biller_id) {
            $this->datatables->where('gym_workouts.biller_id', $biller_id);
        }else if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
            $this->datatables->where('gym_workouts.biller_id =', $this->session->userdata('biller_id'));
        }
            
        echo $this->datatables->generate();
    }
   
    public function add_workout()
    {
        $this->bpas->checkPermissions('add', true, 'workouts');
        $this->form_validation->set_rules('trainee', lang("trainee"), 'required');
        $this->form_validation->set_rules('level', lang("level"), 'required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'required');

        if ($this->form_validation->run() == true) {
            $trainee            = $this->input->post('trainee');
            $start_date         = $this->bpas->fsd($this->input->post('start_date'));
            $end_date           = $this->bpas->fsd($this->input->post('end_date'));
            $level              = $this->input->post('level');
            $description        = $this->input->post('description');
            $r = 0;
            for($r=0;$r<count($_POST['day']);$r++) {
                $product = array(
                    'day'           => $_POST['day'][$r],
                    'activity_id'   => $_POST['activity'][$r],
                    'kg'            => $_POST['kg'][$r],
                    'sets'          => $_POST['sets'][$r],
                    'reps'          => $_POST['reps'][$r],
                    'rest_time'     => $_POST['rest_time'][$r],
                );
                $products[]= $product;
            }
            $data = array(
                'trainee_id'    => $trainee,
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'level_id'      => $level,
                'description'   => $description,
                'created_by'    => $this->session->userdata('user_id')
            ); 

        } elseif ($this->input->post('add_workout')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->gym_model->addWorkout($data,$products)) {
            $this->session->set_flashdata('message', lang("workout_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['trainees']     = $this->gym_model->getAllTrainees();
            $this->data['activities']   = $this->gym_model->getActivities();
            $this->data['levels']       = $this->companies_model->getAllLevel();
            $this->load->view($this->theme . 'gym/add_workout', $this->data);
        }
    }
    public function edit_workout($id)
    {
        $this->bpas->checkPermissions('edit', true, 'workouts');
        $workout = $this->gym_model->getWorkoutByID($id);
        $this->form_validation->set_rules('trainee', lang("trainee"), 'required');
        $this->form_validation->set_rules('level', lang("level"), 'required');
        $this->form_validation->set_rules('start_date', lang("start_date"), 'required');
        $this->form_validation->set_rules('end_date', lang("end_date"), 'required');

        if ($this->form_validation->run() == true) {
            $trainee            = $this->input->post('trainee');
            $start_date         = $this->bpas->fsd($this->input->post('start_date'));
            $end_date           = $this->bpas->fsd($this->input->post('end_date'));
            $level              = $this->input->post('level');
            $description        = $this->input->post('description');
            $r = 0;
            for($r=0;$r<count($_POST['day']);$r++) {
                $product = array(
                    'day'           => $_POST['day'][$r],
                    'activity_id'   => $_POST['activity'][$r],
                    'kg'            => $_POST['kg'][$r],
                    'sets'          => $_POST['sets'][$r],
                    'reps'          => $_POST['reps'][$r],
                    'rest_time'     => $_POST['rest_time'][$r],
                );
                $products[]= $product;
            }
            $data = array(
                'trainee_id'    => $trainee,
                'start_date'    => $start_date,
                'end_date'      => $end_date,
                'level_id'      => $level,
                'description'   => $description,
                'created_by'    => $this->session->userdata('user_id')
            ); 

        } elseif ($this->input->post('edit_workout')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->gym_model->UpdateWorkout($id,$data,$products)) {
            $this->session->set_flashdata('message', lang("workout_uddated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['workout']      = $workout;
            $this->data['workout_items']= $this->gym_model->getWorkoutItemByID($id);
            $this->data['trainees']     = $this->gym_model->getAllTrainees();
            $this->data['activities']   = $this->gym_model->getActivities();
            $this->data['levels']       = $this->companies_model->getAllLevel();
            $this->load->view($this->theme . 'gym/edit_workout', $this->data);
        }
    }
    public function delete_workout($id = null)
    {   
        $this->bpas->checkPermissions('delete', true, 'workouts');
        if (isset($id) || $id != null){         
            if($this->gym_model->deleteWorkoutByID($id)){
                $this->session->set_flashdata('message', lang("workout_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    public function view_workout($id = null)
    {
        $this->bpas->checkPermissions('index', true, 'workouts');
        $this->data['workout']  = $this->gym_model->getWorkoutByID($id);
        $this->data['workoutItems'] = $this->gym_model->getWorkoutItemByID($id);
        $this->load->view($this->theme . 'gym/view_workout', $this->data);
    }
    function attendances()
    {
        $this->bpas->checkPermissions('attendances');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller']  = null;
        $this->data['classes']      = $this->gym_model->getClasses();
        $this->data['activities']   = $this->gym_model->getActivities();

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('gym'), 'page' => lang('school')), array('link' => '#', 'page' => lang('attendances')));
        $meta = array('page_title' => lang('attendances'), 'bc' => $bc);
        $this->page_construct('gym/attendances', $meta, $this->data);
    }
    function getAttendances()
    {
        // $this->bpas->checkPermissions('attendances', TRUE);
       
        // $activity      = $this->input->get('activity') ? $this->input->get('activity') : null;
        $attendance_date = $this->input->get('attendance_date') ? $this->input->get('attendance_date') : null;
        if ($attendance_date) {
            $attendance_date = $this->bpas->fld($attendance_date);
        }
        $detail_link = '<a href="'.admin_url("schools/view_attendance/$1") . '" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa-file-text-o"></i>'.lang('view_attendance').'</a>';
        $delete_link = "<a href='#' class='po' title='" . lang("delete_attendance") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('schools/delete_attendance/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_attendance') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li>'.$detail_link.'</li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        
        $this->load->library('datatables');
        $this->datatables
            ->select("
                    sh_attendances.id as id, 
                    {$this->db->dbprefix('sh_attendances')}.date as date,
                    {$this->db->dbprefix('sh_attendances')}.day as attendance_date,
                    CONCAT({$this->db->dbprefix('users')}.last_name, ' ',{$this->db->dbprefix('users')}.first_name) as user_by,
                    {$this->db->dbprefix('sh_attendances')}.note as note

                ")
            ->from('sh_attendances')
            ->join('users', 'sh_attendances.created_by = users.id', 'left');
            if ($attendance_date) {
                $this->datatables->where('sh_attendances.day',$attendance_date);
            }
        $this->datatables->add_column('Actions', $action_link, "id");
        echo $this->datatables->generate();
    }
    public function add_attendance_student()
    {
       
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line("no_zero_required"));
        $this->form_validation->set_rules('biller', $this->lang->line("biller"), 'required');
        // $this->form_validation->set_rules('timetable', $this->lang->line("subject"), 'required');
        // var_dump(1);exit();
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->bpas->GP['hr-id_cards_date'] ) {
               
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $biller_id      = $this->input->post('biller') ? $this->input->post('biller') : null;
            $academic_year  = $this->input->post('academic_year') ? $this->input->post('academic_year') : null;
            $program_id     = $this->input->post('program') ? $this->input->post('program') : null;
            $skill_id       = $this->input->post('skill') ? $this->input->post('skill') : null;
            $grade_id       = $this->input->post('grade') ? $this->input->post('grade') : null;
            $section_id     = $this->input->post('section') ? $this->input->post('section') : null;
            $timeshift      = $this->input->post('timeshift') ? $this->input->post('timeshift') : null;
            $class_id       = $this->input->post('class_id') ? $this->input->post('class_id') : null;
            $day            = $this->input->post('day') ? $this->bpas->fld($this->input->post('day')) : null;
            $noteAtt       = $this->input->post('noteAtt') ? $this->input->post('noteAtt') : null;
            $table_time_id  = $this->input->post('timetable') ? $this->input->post('timetable') : null;
            $status         = "completed";
            if($class_id){
                $class_name = $this->schools_model->getClassByID($class_id);
            }
            if($section_id){
                $section_name = $this->schools_model->getSectionByID($section_id);
            }
            $month      = date('m', strtotime($day));
            $academic_year  = date('Y', strtotime($day));
            $year       = date('Y', strtotime($day));
            $day_num    = $day;
            $student_id = $this->input->post('study_info_id');
            foreach ($student_id as $key => $value) {
                $attendance_id  = $value;
                $student_id     = $_POST['student_id'][$key];
                $att            = $this->input->post('att' . $value);
                $note           = $this->input->post('note' . $value);
                $present    = 0;
                $absent     = 0;
                $permission = 0;
                if($att == 1){
                    $present = 1;
                    $statusAtt="present";
                } elseif ($att == 2) {
                    $absent = 1;
                    $statusAtt="absent";
                } elseif ($att == 3) {
                    $permission = 1;
                    $statusAtt="permission";
                }
                $items[] = array(
                    "student_id" => $student_id,
                    "date"       => $day,
                    "present"    => $present,
                    "absent"     => $absent,
                    "permission" => $permission,
                    "status"     => $statusAtt,
                    "note"       => $note,
                );
            }
            $data = array(  
                'date'          => $date,
                'biller_id'     => $biller_id,
                'academic_year' => $academic_year,
                'program_id'    => $program_id,
                'grade_id'      => $grade_id,
                'class_id'      => $class_id,
                'class_name'    => $class_name->name,
                'section_id'    => $section_id,
                'section_name'  => $section_name->name,
                'year'          => $year,
                'month'         => $month,
                'day'           => $day_num,
                'final_file'    => '',
                'note'          => $noteAtt,
                'created_by'    => $this->session->userdata('user_id'),
                'updated_by'    => null,
                'status'        => $status,
                'table_time_id' => $table_time_id,
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
            // $this->bpas->print_arrays($data, $items);
        }
        if ($this->form_validation->run() == true && $this->schools_model->addAttendanceStudent($data,$items)) {    
            $this->session->set_flashdata('message', $this->lang->line("attendances_added"));      
                
            admin_redirect('gym/attendances');
        } else {
            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']      = $this->site->getBillers(); 

            $this->data['classes']      = $this->gym_model->getClasses();
            $this->data['activities']   = $this->gym_model->getActivities();
                
            $bc = array(array('link'    => admin_url(), 'page' => lang('home')),array('link' => admin_url('gym'), 'page' => lang('gym')), array('link' => admin_url('gym/add_attendance_student'), 'page' => lang('add_attendance_student')), array('link' => '#', 'page' => lang('add_attendance_student')));
            $meta = array('page_title'  => lang('add_attendance_student'), 'bc' => $bc);
           
            $this->page_construct('gym/add_attendance_student', $meta, $this->data);
        }
    }
    public function getClassStudentAttendances()
    {
        $biller_id     = $this->input->get('biller_id')     ? $this->input->get('biller_id') : null;
        $class_id      = $this->input->get('class_id')      ? $this->input->get('class_id') : null;
        $activity      = $this->input->get('activity')    ? $this->input->get('activity') : null;
        $day           = $this->input->get('day')     ? $this->input->get('day') : null;
      
        $date = DateTime::createFromFormat('d/m/Y', $day);
        if ($date) {
            $dayName = $date->format('l');   
            $dayNameLower = strtolower($dayName);
        }
       
        
        if ($day) {
            if ($studentAttendance = $this->gym_model->getClassStudentAttendances(null,$class_id,$activity,$day,$dayNameLower)) {
                $this->bpas->send_json($studentAttendance);
            }   
            $this->bpas->send_json(false);
        }
        $this->bpas->send_json(false);
    }
    public function membercards()
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('member_cards')]];
        $meta = ['page_title' => lang('member_cards'), 'bc' => $bc];
        $this->page_construct('gym/member_cards', $meta, $this->data);
    }
    public function getMemberCards()
    {
        $this->load->library('datatables');
        $this->datatables
        ->select(
            $this->db->dbprefix('member_cards') . '.id as id, card_no, 
            start_date,
            expiry,
            CONCAT('.$this->db->dbprefix('users').".first_name,' ',".$this->db->dbprefix('users').'.last_name) as created_by,
            
            ', false)
        ->join('users', 'users.id=member_cards.created_by', 'left')
        ->from('member_cards')
        ->add_column('Actions', "<div class=\"text-center\">
            <a href='" . admin_url('gym/view_member_package_card/$1') . "' class='tip' title='" . lang('view_member_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> 
            <a href='" . admin_url('member_cards/edit_member_card/$1') . "' class='tip' title='" . lang('edit_member_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> 
            <a href='#' class='tip po' title='<b>" . lang('delete_member_card') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('member_cards/delete_member_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function view_member_package_card($id = null)
    {
        $this->data['page_title'] = lang('gift_card');
        $gift_card                = $this->site->getMemberCardByID($id);
        $this->data['gift_card']  = $this->site->getMemberCardByID($id);
        $this->load->view($this->theme . 'gym/view_member_card', $this->data);
    }
    public function add_member_card()
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang('value'), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = [
                'card_no'                  => $this->input->post('card_no'),
                'discount'                 => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'expiry'                   => $this->input->post('expiry') ? $this->bpas->fsd($this->input->post('expiry')) : null,
                'created_by'               => $this->session->userdata('user_id'),
            ];        
        }else if ($this->input->post('add_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('member_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->addMemberCard($data)) {
            $this->session->set_flashdata('message', lang('member_card_added'));
            admin_redirect('member_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['users']      = $this->sales_model->getStaff();
            $this->data['page_title'] = lang('new_member_card');
            $this->load->view($this->theme . 'gym/add_member_card', $this->data);
        }
    }
    public function edit_member_card($id = null)
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|required');
        $gc_details = $this->site->getMemberCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang('card_no'), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang('value'), 'required');
            //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card        = $this->site->getMemberCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = [
                'card_no'                  => $this->input->post('card_no'),
                'discount'                 => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'expiry'                   => $this->input->post('expiry') ? $this->bpas->fsd($this->input->post('expiry')) : null,
                'created_by'               => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('edit_member_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('member_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateMemberCard($id, $data)) {
            $this->session->set_flashdata('message', lang('member_card_updated'));
            admin_redirect('member_cards');
        } else {
            $this->data['error']     = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getMemberCardByID($id);
            $this->data['id']        = $id;
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/edit_member_card', $this->data);
        }
    }
    public function delete_member_card($id = null)
    {
        $this->bpas->checkPermissions();

        if ($this->sales_model->deleteMemberCard($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('member_card_deleted')]);
        }
    }
    public function add_booking()
    {
        $this->bpas->checkPermissions('add',null);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('phone', lang('phone'), 'required');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $data = [
                'date'         => $date,
                'note'         => $this->input->post('note', true),
                'category_id'  => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
                'project_id'   => $this->input->post('project'),
                'biller_id'    => $this->input->post('biller'),
                'created_by'   => $this->session->userdata('user_id')
            ];
          
        
        } elseif ($this->input->post('add_booking')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->gym_model->addbooking($data)) {
            $this->session->set_flashdata('message', lang('booking_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']     = $this->site->modal_js();
            $this->load->view($this->theme . 'gym/add_booking', $this->data);
        }
    }
}