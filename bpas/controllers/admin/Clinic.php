<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Clinic extends MY_Controller
{
    function __construct()
    {
       parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect('login');
        }
        $this->lang->admin_load('employee', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->admin_model('clinic_model');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('sales_order_model');
        $this->load->admin_model('companies_model');
        $this->load->admin_model('settings_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('table_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('reports_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    public function index($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('patience')]];
        $meta                 = ['page_title' => lang('patience'), 'bc' => $bc];
        $this->page_construct('clinic/index', $meta, $this->data);
    }
    
    public function getPatients()
    {
        $this->bpas->checkPermissions('index');
        $this->load->library('datatables');
        $view_detail ="<a class='tip' title='" . lang('view_details') . "' href='" . admin_url('customers/view/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-file-text-o\"></i> ".lang("view_details")."</a> ";


        $clear_award_points ="<a href='#' class='tip po' title='" . lang("clear_award_points") . "' data-content=\"<p>" . lang('r_u_sure') . "</p>
                    <a class='btn btn-danger' href='" . admin_url('customers/clear_AP/$1') . "'>" . lang('i_m_sure') . "</a> 
                    <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-eraser\"></i> ".lang("clear_award_points")."
                </a>";
        $list_deposits ="<a class='tip' title='" . lang('list_deposits') . "' href='" . admin_url('customers/deposits/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-money'> </i>".lang("list_deposits")."</a>";
        $add_deposit ="<a class='tip' title='" . lang('add_deposit') . "' href='" . admin_url('customers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-plus'></i> ".lang("add_deposit")."</a>";

        $edit_customer ="<a class='tip' title='" . lang('edit_customer') . "' href='" . admin_url('clinic/edit_patient/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-edit\"></i> ".lang("edit_patient")."</a> ";

        $add_consultation           = anchor('admin/clinic/add_consultation/$1', '<i class="fa-regular fa-user-doctor-message"></i>' . lang('add_consultation'));
        $delete_customer ="<a href='#' class='tip po' title='<b>" . lang('delete_customer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ".lang("delete_patience")."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$view_detail.'</li>
                        <li>'.$add_consultation.'</li>
                        <li>'.$list_deposits.'</li>
                        <li>'.$add_deposit.'</li>
                        <li>'.$edit_customer.'</li>
                        <li>'.$delete_customer.'</li>
                    </ul>
                </div></div>';
        $this->datatables
            ->select("{$this->db->dbprefix('companies')}.id as id, company, code,name,phone, gender, customer_group_name, vat_no, gst_no, deposit_amount")
            ->from('companies')
            ->where('group_name', 'customer');

        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->order_by("{$this->db->dbprefix('companies')}.id", "ASC");
        echo $this->datatables->generate();
    }
    public function add_patient()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('code', lang('code'), 'is_unique[companies.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run('companies/add') == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'code'                      => $this->input->post('code'),
                'name'                      => $this->input->post('name'),
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'street_no'                 => $this->input->post('street_no') ? $this->input->post('street_no'): null,
                'email'                     => $this->input->post('email'),
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
                'blood_group'               => $this->input->post('blood_group') ? $this->input->post('blood_group') : null,
                'nssf'                      => $this->input->post('nssf'),
                'nssf_number'                      => $this->input->post('nssf_number'),
            
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
        } elseif ($this->input->post('patience_added')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('clinic');
        }
        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang('customer_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['zones']           = $this->settings_model->getAllZones();
            $this->data['Lastrow']        = $this->companies_model->getLastCompanies('customer');
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['agents']          = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'clinic/add_patience', $this->data);
        }
    }
    public function edit_patient($id = null)
    {
        $this->bpas->checkPermissions(false, true);
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
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'street_no'                 => $this->input->post('street_no') ? $this->input->post('street_no'): null,
                'email'                     => $this->input->post('email'),
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
                'blood_group'               => $this->input->post('blood_group') ? $this->input->post('blood_group') : null,
                'nssf'                      => $this->input->post('nssf'),
                'nssf_number'                      => $this->input->post('nssf_number'),
            
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
            $this->session->set_flashdata('message', lang('patience_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['customer']        = $company_details;
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['zones']           = $this->settings_model->getAllZones();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'clinic/edit_patience', $this->data);
        }
    }
    public function birth($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('birth_record')]];
        $meta                 = ['page_title' => lang('birth_record'), 'bc' => $bc];
        $this->page_construct('clinic/birth_record', $meta, $this->data);
    }
    public function getbirthRecord()
    {
        $this->bpas->checkPermissions('index');
        $this->load->library('datatables');

    
        $view_detail = anchor('admin/customers/actions/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
        $edit_customer ="<a class='tip' title='" . lang('edit_customer') . "' href='" . admin_url('clinic/edit_birth_record/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-edit\"></i> ".lang("edit_birth_record")."</a> ";

        $delete_birth ="<a href='#' class='tip po' title='<b>" . lang('delete_birth') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_birth/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ".lang("delete_birth")."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                    
                        <li>'.$edit_customer.'</li>
                        <li>'.$delete_birth.'</li>
                    </ul>
                </div></div>';
        $this->datatables
            ->select("
                {$this->db->dbprefix('companies')}.id as id, 
                {$this->db->dbprefix('companies')}.company,
                {$this->db->dbprefix('companies')}.code,
                {$this->db->dbprefix('companies')}.name,
                {$this->db->dbprefix('companies')}.gender,
                {$this->db->dbprefix('companies')}.cf1, 
                {$this->db->dbprefix('companies')}.cf3,
                cus.name as monther, 
                {$this->db->dbprefix('companies')}.cf5,
                {$this->db->dbprefix('companies')}.phone")
            ->from('companies')
            ->join('companies cus', 'cus.id = companies.parent_id', 'left') 
            ->where('companies.group_name', 'birth');

        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->order_by("{$this->db->dbprefix('companies')}.id", "ASC");
        echo $this->datatables->generate();
    }
    public function add_birth_record()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('code', lang('code'), 'is_unique[companies.code]');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'code'                      => $this->input->post('code'),
                'name'                      => $this->input->post('name'),
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'parent_id'                 => $this->input->post('customer'),
                'vat_no'                    => $this->input->post('vat_no') ? $this->input->post('vat_no') : null,
                'contact_person'            => $this->input->post('contact_person') ? $this->input->post('contact_person') : null,
                'group_id'                  => '9',
                'group_name'                => 'birth',
                'customer_group_id'         => $this->input->post('customer_group') ? $this->input->post('customer_group') : 1,
                'customer_group_name'       => $cg->name,
                'price_group_id'            => null,
                'price_group_name'          => null,
                'company'                   => $this->Settings->customer_detail ? $this->input->post('company') : '-',
                'address'                   => $this->input->post('address'),
                'city'                      => $this->input->post('city'),
                'phone'                     => $this->input->post('phone'),
                'gender'                    => $this->input->post('gender'),
                'age'                       => $this->input->post('age'),
                'cf1'                       => $this->input->post('cf1'),
                'cf2'                       => $this->input->post('cf2'),
                'cf3'                       => $this->input->post('cf3'),
                'cf4'                       => $this->input->post('cf4'),
                'cf5'                       => $this->input->post('cf5'),
                'cf6'                       => $this->input->post('cf6'),
                'invoice_footer'            => $this->input->post('noted'),   
            ];
            if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
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
        } elseif ($this->input->post('add_birth_record')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('clinic/birth');
        }
        if ($this->form_validation->run() == true && $cid = $this->clinic_model->addBirthRecord($data)) {
            $this->session->set_flashdata('message', lang('birth_record'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            
            $this->data['Lastrow']         = $this->companies_model->getLastCompanies('customer');
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['customers']       = $this->site->getCustomers();
            $this->data['agents']          = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/add_birth_record', $this->data);
        }
    }
    public function edit_birth_record($id = null)
    {
        $this->bpas->checkPermissions(false, true);
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
        if ($this->form_validation->run() == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));

            $data = [
                'code'                      => $this->input->post('code'),
                'name'                      => $this->input->post('name'),
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'parent_id'                 => $this->input->post('customer'),
                'vat_no'                    => $this->input->post('vat_no') ? $this->input->post('vat_no') : null,
                'contact_person'            => $this->input->post('contact_person') ? $this->input->post('contact_person') : null,
                'group_id'                  => '9',
                'group_name'                => 'birth',
                'customer_group_id'         => $this->input->post('customer_group') ? $this->input->post('customer_group') : 1,
                'customer_group_name'       => $cg->name,
                'price_group_id'            => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'          => $this->input->post('price_group') ? $pg->name : null,
                'company'                   => $this->Settings->customer_detail ? $this->input->post('company') : '-',
                'address'                   => $this->input->post('address'),
                'city'                      => $this->input->post('city'),
                'phone'                     => $this->input->post('phone'),
                'gender'                    => $this->input->post('gender'),
                'age'                       => $this->input->post('age'),
                'cf1'                       => $this->input->post('cf1'),
                'cf2'                       => $this->input->post('cf2'),
                'cf3'                       => $this->input->post('cf3'),
                'cf4'                       => $this->input->post('cf4'),
                'cf5'                       => $this->input->post('cf5'),
                'cf6'                       => $this->input->post('cf6'),
                'invoice_footer'            => $this->input->post('noted'), 
            ];

            if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
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
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'clinic/edit_birth_record', $this->data);
        }
    }
    public function delete_birth($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('birth_x_deleted')]);
        }

        if ($this->clinic_model->deleteBirth($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('birth_record_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('birth_x_deleted_have_record')]);
        }
    }
    public function death($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('birth_record')]];
        $meta                 = ['page_title' => lang('birth_record'), 'bc' => $bc];
        $this->page_construct('clinic/death_record', $meta, $this->data);
    }
    public function getdeathRecord()
    {
        $this->bpas->checkPermissions('index');
        $this->load->library('datatables');

    
        $view_detail = anchor('admin/customers/actions/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));     
        $edit_customer ="<a class='tip' title='" . lang('edit_customer') . "' href='" . admin_url('clinic/edit_death_record/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-edit\"></i> ".lang("edit_death_record")."</a> ";

        $delete_death ="<a href='#' class='tip po' title='<b>" . lang('delete_death_record') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_death/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ".lang("delete_death_record")."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
            
                        <li>'.$edit_customer.'</li>
                        <li>'.$delete_death.'</li>
                    </ul>
                </div></div>';
        $this->datatables
            ->select("
                {$this->db->dbprefix('companies')}.id as id, 
                {$this->db->dbprefix('companies')}.company,
                {$this->db->dbprefix('companies')}.code,
                {$this->db->dbprefix('companies')}.name,
                cus.gender,
                {$this->db->dbprefix('companies')}.contact_person, 
                {$this->db->dbprefix('companies')}.date2,
                {$this->db->dbprefix('companies')}.invoice_footer as note")
            ->from('companies')
            ->join('companies cus', 'cus.id = companies.parent_id', 'left') 
            ->where('companies.group_name', 'death');

        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->order_by("{$this->db->dbprefix('companies')}.id", "ASC");
        echo $this->datatables->generate();
    }
    public function add_death_record()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('code', lang('code'), 'is_unique[companies.code]');
        $this->form_validation->set_rules('customer', lang("patience"), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');

        $company_details = $this->companies_model->getCompanyByID($this->input->post('customer')); 
        
        if ($this->form_validation->run() == true) {
            if(!$company_details){
                $this->session->set_flashdata('error', lang('patience_not_found'));
                $this->bpas->md();
            }

            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'code'                      => $company_details->code,
                'name'                      => $company_details->name,
                'date2'                     => $this->input->post('death_date') ? $this->bpas->fld(trim($this->input->post('death_date'))) : null,
                'parent_id'                 => $this->input->post('customer'),
                'contact_person'            => $this->input->post('contact_person') ? $this->input->post('contact_person') : null,
                'group_id'                  => '10',
                'group_name'                => 'death',
                'customer_group_id'         => $this->input->post('customer_group') ? $this->input->post('customer_group') : 1,
                'customer_group_name'       => $cg->name,
                'price_group_id'            => null,
                'price_group_name'          => null,
                'company'                   => '-',
                'invoice_footer'            => $this->input->post('noted'),   
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
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('clinic/death');
        }
        if ($this->form_validation->run() == true && $cid = $this->clinic_model->addBirthRecord($data)) {
            $this->session->set_flashdata('message', lang('birth_record'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['Lastrow']        = $this->companies_model->getLastCompanies('customer');
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['agents']          = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'clinic/add_death_record', $this->data);
        }
    }
    public function edit_death_record($id = null)
    {
        $this->bpas->checkPermissions(false, true);
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
        if ($this->form_validation->run() == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            if(!$company_details){
                $this->session->set_flashdata('error', lang('patience_not_found'));
                $this->bpas->md();
            }
            $data = [
                'code'                      => $company_details->code,
                'name'                      => $company_details->name,
                'date2'                     => $this->input->post('death_date') ? $this->bpas->fld(trim($this->input->post('death_date'))) : null,
                'parent_id'                 => $this->input->post('customer'),
                'vat_no'                    => $this->input->post('vat_no') ? $this->input->post('vat_no') : null,
                'contact_person'            => $this->input->post('contact_person') ? $this->input->post('contact_person') : null,
                'group_id'                  => '10',
                'group_name'                => 'death',
                'customer_group_id'         => $this->input->post('customer_group') ? $this->input->post('customer_group') : 1,
                'customer_group_name'       => $cg->name,
                'price_group_id'            => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'          => $this->input->post('price_group') ? $pg->name : null,
                'company'                   => $this->Settings->customer_detail ? $this->input->post('company') : '-',
                'address'                   => $this->input->post('address'),
                'city'                      => $this->input->post('city'),
                'phone'                     => $this->input->post('phone'),
                'gender'                    => $this->input->post('gender'),
                'age'                       => $this->input->post('age'),
                'cf1'                       => $this->input->post('cf1'),
                'cf2'                       => $this->input->post('cf2'),
                'cf3'                       => $this->input->post('cf3'),
                'cf4'                       => $this->input->post('cf4'),
                'cf5'                       => $this->input->post('cf5'),
                'cf6'                       => $this->input->post('cf6'),
                'invoice_footer'            => $this->input->post('noted'), 
            ];

            if ($_FILES['photo']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
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
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang('death_record_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['customer']        = $company_details;
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'clinic/edit_death_record', $this->data);
        }
    }
    public function delete_death($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('death_record_deleted')]);
        }

        if ($this->clinic_model->deletedeath($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('death_record_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('death_x_deleted_have_record')]);
        }
    }
    public function consultation($biller_id = null)
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

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('consultation')]];
        $meta = ['page_title' => lang('consultation'), 'bc' => $bc];
        $this->page_construct('clinic/consultation', $meta, $this->data);
    }
    public function getConsultations($biller_id = null)
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
        $detail_link        = anchor('admin/sales_order/view/$1', '<i class="fa fa-file-text-o"></i>' . lang('sale_details'));
        $return_detail_link = anchor('admin/sales_order/return_view/$1', '<i class="fa fa-file-text-o"></i>' . lang('return_sale').' '. lang('details'));
        $detail_link_clinic = anchor('admin/clinic/dental_clinic/$1', '<i class="fa fa-file-text-o"></i>'.lang('view_details'));

        $add_draw_link      = anchor('admin/clinic/add_draw/$1', '<i class="fa fa-pencil"></i>' . lang('add_draw'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $duplicate_link     = anchor('admin/sales_order/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link      = anchor('admin/sales_order/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link   = anchor('admin/sales_order/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"'); 

        $email_link         = anchor('admin/sales_order/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/clinic/edit_consultation/$1', '<i class="fa fa-edit"></i>' . lang('edit_consultation'), 'class="sledit"');

        $pdf_link           = anchor('admin/sales_order/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link        = anchor('admin/sales_order/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
       
        $add_sale           = anchor('admin/sales/add/$1', '<i class="fa fa-money"></i>' . lang('add_sale'));

        $add_opd           = anchor('admin/clinic/add_opd/0/$1', '<i class="fa-regular fa-syringe"></i>' . lang('add_opd'));
        $add_ipd           = anchor('admin/clinic/add_ipd/0/$1', 
                            '<i class="fa-regular fa-bed-pulse"></i>' . lang('add_ipd'));

        $authorization      = anchor('admin/sales_order/getAuthorization/$1', '<i class="fa fa-check"></i>' . lang('approved'), '');
        $unapproved         = anchor('admin/sales_order/getunapproved/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('unapproved'), '');
        $rejected           = anchor('admin/sales_order/getrejected/$1', '<i class="fa fa-times"></i> ' . lang('rejected'), '');

        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale_order') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales_order/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link_clinic . '</li>
                <li>' . $add_draw_link . '</li>
                <li class="opd_link">' . $add_opd . '</li>
                <li class="ipd_link">' . $add_ipd . '</li>
            ';
            $action .= 
                (($this->Owner || $this->Admin) ? '<li class="approved">'.$authorization.'</li>' : ($this->GP['sales_order-approved'] ? '<li class="approved">'.$authorization.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="unapproved">'.$unapproved.'</li>' : ($this->GP['sales_order-rejected'] ? '<li class="unapproved">'.$unapproved.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="reject">'.$rejected.'</li>' : ($this->GP['sales_order-rejected'] ? '<li class="reject">'.$rejected.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['sales_order-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add">'.$add_sale.'</li>' : ($this->GP['sales-add'] ? '<li class="add">'.$add_sale.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['sales_order-delete'] ? '<li class="delete">'.$delete_link.'</li>' : '')).
            '</ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('sales_order')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales_order')}.date, '%Y-%m-%d %T') as date, 
                reference_no, 
                biller, 
                {$this->db->dbprefix('sales_order')}.customer, 
                {$this->db->dbprefix('companies')}.phone as phone, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by, 
                sale_status as so,
                grand_total,
                {$this->db->dbprefix('sales_order')}.patience_type, 
                order_status,
                {$this->db->dbprefix('sales_order')}.attachment, 
                return_id")
            ->join('(select sum(amount) as deposit,sale_order_id from '.$this->db->dbprefix('payments').' where sale_order_id > 0 GROUP BY sale_order_id) as payments','payments.sale_order_id = sales_order.id','left')
            ->join('companies', 'companies.id = sales_order.customer_id', 'left')
            ->join('users', 'sales_order.saleman_by = users.id', 'left')
            ->from('sales_order');
        if ($biller_id) {
            $this->datatables->where_in('sales_order.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        // if ($this->input->get('attachment') == 'yes') {
        //     $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        // }
        //$this->datatables->where('patience_type','opd'); // ->where('sale_status !=', 'returned');
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
     public function add_consultation($patient_id = null)
    {
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $exchange_khm    = $getexchange_khm->rate;

        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sr');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
  
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != false && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
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
                      
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
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
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail,
                    ];
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
            $diagnosis      = implode(",", $this->input->post('symptoms[]'));
            $data           = [
                'date'                => $date,
                'project_id'          => $project_id,
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
                'order_status'        => 'pending',
                'payment_status'      => $payment_status,
                'paid'                => 0,
                'saleman_by'          => $this->input->post('saleman_by'),
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'currency_rate_kh'    => $exchange_khm,
     
                'patience_type'       => $this->input->post('patience_type'),
                'weight'              => $this->input->post('weight'),
                'height'              => $this->input->post('height'),
                'temperature'         => $this->input->post('temperature'),
                'symptoms'            => $diagnosis ? $diagnosis:null,
                'symptoms_description'=> $this->input->post('symptoms_description'),
            ];
            $payment = [];
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
            // $this->bpas->print_arrays($data, $products, $payment);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addConsult($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            // if ($patient_id) {
            //     $this->db->update('quotes', ['status' => 'completed'], ['id' => $patient_id]);
            // }
            $this->session->set_flashdata('message', lang('consultation_added'));
            admin_redirect('clinic/consultation');
        } else {
            if ($sale_id) {
                if ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis           = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
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
                    $row->discount        = $item->discount ? $item->discount : '0';
                    $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = '';
                    $row->serial_no       = $row->serial_no;
                    $row->option          = $item->option_id;
                    $row->details         = $item->comment;
                    $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if ($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }

           

            $Settings                  = $this->site->getSettings();
            $this->data['count']       = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']    = $this->site->getAllProject();
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            

            $this->data['billers']     = $this->site->getAllCompanies('biller');
            $this->data['warehouses']  = $this->site->getAllWarehouses();
            $this->data['tax_rates']   = $this->site->getAllTaxRates();
            $this->data['units']       = $this->site->getAllBaseUnits();
            $this->data['slnumber']    = $this->site->getReference('sr');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['salemans']    = $this->site->getAllSalemans($Settings->group_saleman_id);

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['patient_id']    = $patient_id;
            $this->data['patient']     = $this->companies_model->getCompanyByID($patient_id); 

            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('consultation'), 'page' => lang('sales_order')], ['link' => '#', 'page' => lang('add_consultation')]];
            $meta                      = ['page_title' => lang('add_consultation'), 'bc' => $bc];
            $this->page_construct('clinic/add_consultation', $meta, $this->data);
        }
    }
    public function edit_consultation($id = null)
    {
        $this->bpas->checkPermissions();
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $exchange_khm    = $getexchange_khm->rate;

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_order_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));

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
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != false && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial        = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_detail      = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_order_model->getProductByCode($item_code) : null;

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

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
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
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail
                    ];

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

            $diagnosis = implode(",", $this->input->post('symptoms[]'));
            
            $data           = [
                'date' => $date,
                'project_id'        => $project_id,
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
                'saleman_by'          => $this->input->post('saleman_by'),
                'updated_by'          => $this->session->userdata('user_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'currency_rate_kh'    => $exchange_khm,
                
                'patience_type'       => $this->input->post('patience_type'),
                'weight'              => $this->input->post('weight'),
                'height'              => $this->input->post('height'),
                'temperature'         => $this->input->post('temperature'),
                'symptoms'            => $diagnosis ? $diagnosis:null,
                'symptoms_description'=> $this->input->post('symptoms_description'),

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

            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sales_order_model->updateSale($id, $data, $products)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('consultation_updated'));
            admin_redirect('clinic/consultation');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_order_model->getInvoiceByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_order_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_order_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                
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
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity += $item->quantity;
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
                $row->details         = $item->comment;
                $row->option          = $item->option_id;
                $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
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
                    $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }

                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                $c++;
            }

            $Settings = $this->site->getSettings();
            $this->data['count']      = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['salemans']   = $this->site->getAllSalemans($Settings->group_saleman_id);

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ?explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  =$user->warehouse_id ?explode(',', $user->warehouse_id) : null;
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('clinic'), 'page' => lang('clinic')], ['link' => '#', 'page' => lang('edit_consultation')]];
            $meta = ['page_title' => lang('edit_consultation'), 'bc' => $bc];
            $this->page_construct('clinic/edit_consultation', $meta, $this->data);
        }
    }
    public function opd($biller_id = null)
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
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id):null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('opd_patient')]];
        $meta = ['page_title' => lang('opd_patient'), 'bc' => $bc];
        $this->page_construct('clinic/opd_patient', $meta, $this->data);
    }
    public function getOPDPatients($biller_id = null)
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
    
        $view_deposit_link = anchor('admin/sales_order/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_deposit" data-target="#myModal"');
        $add_deposit_link = anchor('admin/sales_order/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_deposit" data-target="#myModal"');

        $detail_link        = anchor('admin/clinic/opd_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
        $detail_link_clinic = anchor('admin/clinic/dental_clinic/$1', '<i class="fa fa-file-text-o"></i>'.lang('invoice_dental_clinic'));
        $add_draw_link      = anchor('admin/clinic/add_draw/$1', '<i class="fa fa-pencil"></i>' . lang('add_draw'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $duplicate_link     = anchor('admin/sales_order/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link      = anchor('admin/sales_order/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link   = anchor('admin/sales_order/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
     
        $email_link         = anchor('admin/sales_order/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/clinic/edit_opd/$1', '<i class="fa fa-edit"></i>' . lang('edit_opd'), 'class="sledit"');
        $pdf_link           = anchor('admin/sales_order/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link        = anchor('admin/sales_order/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
      
        $add_sale           = anchor('admin/sales/add/$1', '<i class="fa fa-money"></i>' . lang('add_sale'));
        $authorization      = anchor('admin/sales_order/getAuthorization/$1', '<i class="fa fa-check"></i>' . lang('approved'), '');
        $unapproved         = anchor('admin/sales_order/getunapproved/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('unapproved'), '');
        $rejected           = anchor('admin/sales_order/getrejected/$1', '<i class="fa fa-times"></i> ' . lang('rejected'), '');

        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_opd') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_opd/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_opd') . '</a>';

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>';
            $action .=         
                (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['sales_order-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['sales_order-delete'] ? '<li class="delete">'.$delete_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add">'.$add_sale.'</li>' : ($this->GP['sales-add'] ? '<li class="add">'.$add_sale.'</li>' : '')).
            '</ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('clinic_ipd_opd')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('clinic_ipd_opd')}.date, '%Y-%m-%d %T') as date, 
                {$this->db->dbprefix('companies')}.name as biller,
                reference_no,
                cus.code as code, 
                cus.name as patient, 
                cus.gender as gender, 
                cus.phone as phone, 
                doctor_id,
                weight,
                symptoms_description,
                {$this->db->dbprefix('clinic_ipd_opd')}.attachment, 
                {$this->db->dbprefix('clinic_ipd_opd')}.created_by")
        ->from('clinic_ipd_opd')
        ->join('companies','companies.id=clinic_ipd_opd.biller_id', 'left')
        ->join('companies cus','cus.id = clinic_ipd_opd.patient_id', 'left') ;
        $this->datatables->where('clinic_ipd_opd.patience_type','opd');

        if ($biller_id) {
            $this->datatables->where_in('ipd_opd.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        // if ($this->input->get('attachment') == 'yes') {
        //     $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        // }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_opd($customer_id = null,$consult_id=null)
    {
        $this->bpas->checkPermissions('add',null);
        $this->load->helper('security');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('doctor', lang('doctor'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $diagnosis      = implode(",", $this->input->post('symptoms[]'));
            $data = [
                'date'         => $date,
                'reference_no' => $this->input->post('reference'),
                'biller_id'    => $this->input->post('biller'),
                'patient_id'   => $this->input->post('customer', true),
                'doctor_id'    => $this->input->post('doctor'),
                'patience_type' => 'opd',
                'weight'        => $this->input->post('weight'),
                'height'        => $this->input->post('height'),
                'temperature'   => $this->input->post('temperature'),
                'note'          => $this->input->post('note', true),
                'symptoms'      => $diagnosis ? $diagnosis:null,
                'symptoms_description'  => $this->input->post('symptoms_description', true),
                'created_by'   => $this->session->userdata('user_id'),

            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
           
        
        } elseif ($this->input->post('add_opd')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addOPD($data)) {
            $this->session->set_flashdata('message', lang('opd_added'));
            admin_redirect('clinic/opd');
        } else {
         
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['customer_id']      = $customer_id ? $customer_id : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['users']            = $this->site->getStaff();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();
            $this->data['consult']     = $this->sales_order_model->getInvoiceByID($consult_id);
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('clinic'), 'page' => lang('clinic')], ['link' => '#', 'page' => lang('add_opd')]];
            $meta                      = ['page_title' => lang('add_opd'), 'bc' => $bc];
            $this->page_construct('clinic/add_opd', $meta, $this->data);
        }
    }
    public function edit_opd($id=false)
    {
        $this->bpas->checkPermissions('add',null);
        $this->load->helper('security');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('doctor', lang('doctor'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');

        $this->data['data']    = $this->clinic_model->getOpdByID($id);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $diagnosis      = implode(",", $this->input->post('symptoms[]'));
            $data = [
                'date'         => $date,
                'reference_no' => $this->input->post('reference'),
                'biller_id'    => $this->input->post('biller'),
                'patient_id'   => $this->input->post('customer', true),
                'doctor_id'    => $this->input->post('doctor'),
                'patience_type' => 'opd',
                'weight'        => $this->input->post('weight'),
                'height'        => $this->input->post('height'),
                'temperature'   => $this->input->post('temperature'),
                'note'          => $this->input->post('note', true),
                'symptoms'      => $diagnosis ? $diagnosis:null,
                'symptoms_description'  => $this->input->post('symptoms_description', true),
                'created_by'   => $this->session->userdata('user_id'),

            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
           
        
        } elseif ($this->input->post('add_opd')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->updateOPD($id,$data)) {
            $this->session->set_flashdata('message', lang('opd_updated'));
            admin_redirect('clinic/opd');
        } else {
         
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['datas']            = $this->clinic_model->getOpdByID($id);
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['users']            = $this->site->getStaff();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();

            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('clinic'), 'page' => lang('clinic')], ['link' => '#', 'page' => lang('edit_opd')]];
            $meta                      = ['page_title' => lang('edit_opd'), 'bc' => $bc];
            $this->page_construct('clinic/edit_opd', $meta, $this->data);
        }
    }
    public function delete_opd($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $data    = $this->clinic_model->getOpdByID($id);

        if($this->clinic_model->deleteOpd($data->id)){
            if ($data->attachment) {
                unlink($this->upload_path . $data->attachment);
            }
        }  
        $this->bpas->send_json(['error' => 0, 'msg' => lang('opd_deleted')]);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function progress_note($biller_id = null){
        $this->bpas->checkPermissions('index',null);
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
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

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('progress_note')]];
        $meta                = ['page_title' => lang('progress_note'), 'bc' => $bc];
        $this->page_construct('clinic/progress_note', $meta, $this->data);
    }
    public function getProgressNote($biller_id = null)
    {
        $this->bpas->checkPermissions('index',null);
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
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

        $detail_link = anchor('admin/clinic/view_progress_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_progress_note'), 'data-toggle="modal" data-target="#myModal2"');

        $edit_link   = anchor('admin/clinic/edit_progress_note/$1', '<i class="fa fa-edit"></i> ' . lang('edit_progress_note'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_progress_note') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_progress_note/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_progress_note') . '</a>';
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
            ->select($this->db->dbprefix('progress_note') . ".id as id, 
                {$this->db->dbprefix('progress_note')}.date, 
                {$this->db->dbprefix('progress_note')}.reference, 
                {$this->db->dbprefix('companies')}.name as biller,
                cus.name as customer,
                {$this->db->dbprefix('custom_field')}.name as type, 
                {$this->db->dbprefix('progress_note')}.effective_date as effective_date, 
                CONCAT(doctor.first_name, ' ', doctor.last_name) as doctor, 
                {$this->db->dbprefix('progress_note')}.note as note,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, 
                {$this->db->dbprefix('progress_note')}.attachment", false)
            ->from('progress_note')
            ->join('companies', 'companies.id=progress_note.biller_id', 'left')
            ->join('companies cus', 'cus.id = progress_note.customer_id', 'left') 
            ->join('custom_field', 'custom_field.id = progress_note.type_id', 'left') 
            ->join('users', 'users.id=progress_note.created_by', 'left')
            ->join('users doctor', 'doctor.id=progress_note.doctor_id', 'left');

        if ($biller_id) {
            $this->datatables->where('progress_note.biller_id IN ('.$biller_id.')');
        } 
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('progress_note.created_by', $this->session->userdata('user_id'));
        }
        if($customer){
            $this->datatables->where('progress_note.customer_id', $customer);
        }
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_progress_note($customer_id=null)
    {
        $this->bpas->checkPermissions('add',null);
        $this->load->helper('security');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('type', lang('type'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'         => $date,
                'biller_id'    => $this->input->post('biller'),
                'note'         => $this->input->post('note', true),
                'customer_id'  => $this->input->post('customer', true),
                'type_id'      => $this->input->post('type', true),
                'doctor_id'    => $this->input->post('doctor'),
                'created_by'   => $this->session->userdata('user_id'),
                'effective_date'   => $this->bpas->fld($this->input->post('effective_date')),

            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            $datas[] = $data;
            
            krsort($datas);
        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addProgressNote($datas)) {
            $this->session->set_flashdata('message', lang('progress_note_added'));
            //admin_redirect('clinic/progress_note');
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['customer_id']      = $customer_id ? $customer_id : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['users']            = $this->site->getStaff();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();
            $this->data['modal_js']         = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/add_progress_note', $this->data);
        }
    }
    public function edit_progress_note($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('type', lang('type'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        $this->data['expense']    = $this->clinic_model->getProgressNoteByID($id);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'         => $date,
                'biller_id'    => $this->input->post('biller'),
                'note'         => $this->input->post('note', true),
                'customer_id'  => $this->input->post('customer', true),
                'type_id'      => $this->input->post('type', true),
                'doctor_id'    => $this->input->post('doctor'),
                'created_by'   => $this->session->userdata('user_id'),
                'effective_date'   => $this->bpas->fld($this->input->post('effective_date')),

            ];
             
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        
        } elseif ($this->input->post('edit_progress_note'))  {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true &&  $this->clinic_model->updateProgressNote($id, $data)) {
            $this->session->set_flashdata('message', lang('progress_note_updated'));
            //admin_redirect('clinic/progress_note');
            redirect($_SERVER['HTTP_REFERER']);

        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['pnote']    = $this->clinic_model->getProgressNoteByID($id);
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();

            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['users']            = $this->site->getStaff();
            $this->data['modal_js']         = $this->site->modal_js();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();
            $this->load->view($this->theme . 'clinic/edit_progress_note', $this->data);
        }
    }
    public function delete_progress_note($id = null)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $expense    = $this->clinic_model->getProgressNoteByID($id);

        if($this->clinic_model->deleteProgressNoteByID($expense->id)){
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
        }  
        $this->bpas->send_json(['error' => 0, 'msg' => lang('progress_note_deleted')]);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function view_progress_note($id = null)
    {
        $pnote                      = $this->clinic_model->getProgressNoteByID($id);
        $this->data['user']         = $this->site->getUser($pnote->created_by);
        $this->data['biller']       = $pnote->biller_id ? $this->site->getCompanyByID($pnote->biller_id) : null;
        $this->data['pnote']        = $pnote;
        $this->data['projects']     = $this->site->getAllProject();
        $this->data['page_title']   = $this->lang->line('view_progress_note');
        $this->load->view($this->theme . 'clinic/view_progress_note', $this->data);
    }

    public function sales($biller_id = null)
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

        $this->data['alert_id']         = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $this->data['users']            = $this->site->getStaff();
        $this->data['products']         = $this->site->getProducts();
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $this->data['count_warehouses'] = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['drivers']          = $this->site->getDriver();

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('clinic/sales', $meta, $this->data);
    }
    public function getSales($biller_id = null)
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
        $installment_link = '';
        if($this->Settings->module_installment && (isset($this->GP['installments-add']) || ($this->Owner || $this->Admin))){
            $installment_link = anchor('admin/installments/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_installment'),'class="add_installment"');
        }

        $view_logo            = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $view_ticket          = anchor('admin/sales/view_ticket/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_ticket'));
        $dental_invoice_link  = '';$add_draw_link = '';

        if($this->Settings->module_clinic){
            $dental_invoice_link  = anchor('admin/sales/dental_clinic/$1', '<i class="fa fa-file-text-o"></i> ' . lang('invoice_dental_clinic'));
            $add_draw_link        = anchor('admin/sales/add_draw/$1', '<i class="fa fa-pencil"></i> ' . lang('add_draw'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        }
        
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $down_payments_link   = anchor('admin/sales/view_down_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_down_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_Downpayment_link = anchor('admin/sales/add_downpayment/$1', '<i class="fa fa-money"></i> ' . lang('add_down_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link       = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        // $add_delivery_link    = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link    = anchor('admin/deliveries/add/0/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
        $add_credit_note_link = anchor('admin/sales/add_credit_note/$1', '<i class="fa fa-truck"></i> ' . lang('add_credit_note'));
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'));
        $request_edit_link    = anchor('admin/sales/add_request_edit_sale/$1', '<i class="fa fa-file-text"></i> ' . lang('request_edit_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        
        $view_agreement       = anchor('admin/sales/view_agreement/$1', '<i class="fa fa-file-text-o"></i> ' . lang('agreement'));
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $view_logo . '</li> 
            <li>' . $dental_invoice_link . '</li>
            <li>' . $add_draw_link . '</li>';
            $action .= '
                <li>' . $duplicate_link . '</li>
                <li>' . $payments_link . '</li>
                <li class="add_payment">' . $add_payment_link . '</li>
                <li>' . $installment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li class="hide">' . $add_credit_note_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $view_agreement . '</li>
                <li>' . $return_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';
        $ds = "( SELECT d.sale_id,d.delivered_by,d.status,c.name as delivery_name
            from {$this->db->dbprefix('deliveries')} d LEFT JOIN {$this->db->dbprefix('companies')} c 
            on d.delivered_by = c.id GROUP BY d.sale_id) FSI";
            //FSI.status as delivery_status, 
            // COALESCE({$this->db->dbprefix('sales')}.delivery_status, FSI.status) as delivery_status,
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('sales')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('sales')}.reference_no,
                {$this->db->dbprefix('sales')}.biller, 
                {$this->db->dbprefix('sales')}.customer, 
                project_name,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
                {$this->db->dbprefix('sales_order')}.reference_no as sr_ref, 
                {$this->db->dbprefix('sales')}.sale_status, 
                {$this->db->dbprefix('sales')}.grand_total, 
                {$this->db->dbprefix('sales')}.paid, 
                ({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) as balance,
                {$this->db->dbprefix('sales')}.payment_status, 
                {$this->db->dbprefix('sales')}.return_id")
            ->join('projects', 'sales.project_id = projects.project_id', 'left')
            ->join('sales_order', 'sales.so_id = sales_order.id', 'left')
            ->join('users', 'sales.saleman_by = users.id', 'left')
            ->join($ds, 'FSI.sale_id=sales.id', 'left')
            ->order_by('sales.id', 'desc')
            ->from('sales');

        $this->datatables->where('sales.module_type','inventory');

        if ((!$this->Owner && !$this->Admin) && $this->GP['view_tax']) {
            $this->datatables->where('sales.declare_tax', 1 );
        }

        $this->datatables->where('sales.hide', 1);
        if ($biller_id) {
            $this->datatables->where_in('sales.biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id', 'bpas_projects.customer_id');
        }
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('sales.shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('sales.shop !=', 1);
        }
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }
        // if ($user_query) {
        //     $this->datatables->where('sales.created_by', $user_query);
        // }
        if ($payment_status) {
            $get_status = explode('_', $payment_status);
            $this->datatables->where_in('sales.payment_status', $get_status);
        }
        if ($reference_no) {
            $this->datatables->where('sales.reference_no', $reference_no);
        }
        // if ($product_id) {
        //     $this->datatables->where('sales.product_id', $product_id);
        // }
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
    public function prescription($biller_id = null)
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
        
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('prescription')]];
        $meta = ['page_title' => lang('prescription'), 'bc' => $bc];
        $this->page_construct('clinic/prescription', $meta, $this->data);
    }
    public function getPrescriptions($biller_id = null)
    {
        // $this->bpas->checkPermissions();

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

        $detail_link        = anchor('admin/clinic/view_prescription/$1', '<i class="fa fa-file-text-o"></i>' . lang('view_prescription'),'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $duplicate_link     = anchor('admin/sales_order/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $email_link         = anchor('admin/sales_order/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/clinic/edit_prescription/$1', '<i class="fa fa-edit"></i>' . lang('edit_prescription'), 'class="sledit"');
        $pdf_link           = anchor('admin/sales_order/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $add_sale           = anchor('admin/sales/add/0/0/0/$1', '<i class="fa fa-money"></i>' . lang('generate_bill'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_prescription') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_prescription/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_prescription') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $add_sale . '</li>
                ';
                $action .= (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['sales_order-edit_prescription'] ? '<li class="add">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['sales_order-delete_prescription'] ? '<li class="add">'.$delete_link.'</li>' : '')). 
                '
            </ul>
        </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('prescription')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('prescription')}.date, '%Y-%m-%d %T') as date,
                CONCAT({$this->db->dbprefix('users')}.first_name,' ', {$this->db->dbprefix('users')}.last_name) as salesman_by,
                reference_no, 
                biller,  
                CONCAT({$this->db->dbprefix('companies')}.name, ' ' ,{$this->db->dbprefix('companies')}.phone) as customer,
                {$this->db->dbprefix('prescription')}.note as diagnosis,
                {$this->db->dbprefix('prescription')}.sale_status as sale_status,
                {$this->db->dbprefix('prescription')}.attachment")
            ->join('users', 'users.id = prescription.saleman_by', 'left')
            ->join('companies', 'companies.id = prescription.customer_id', 'left')
            // ->join('custom_field', 'custom_field.id = prescription.diagnosis_id', 'left')
            ->from('prescription');
        if ($biller_id) {
            $this->datatables->where_in('prescription.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        if ($biller) {
            $this->datatables->where('prescription.biller_id', $biller);
        }
        if ($customer) {
            $this->datatables->where('prescription.customer_id', $customer);
        }
        if ($saleman_by) {
            $this->datatables->where('prescription.saleman_by', $saleman_by);
        }
        if ($warehouse) {
            $this->datatables->where('prescription.warehouse_id', $warehouse);
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_prescription($customer_id = null)
    {
        $this->bpas->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('sr');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $history        = $this->input->post('history');
            $symptoms        = $this->input->post('symptoms');
            $age        = $this->input->post('age');
            $weight        = $this->input->post('weight');
            $height        = $this->input->post('height');
            $medicine        = $this->input->post('medicine');
            $laboratory        = $this->input->post('laboratory');
            $vital_signs        = $this->input->post('vital_signs');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_morning_no    = $_POST['morning'][$r];
                $item_afteroon_no   = $_POST['afteroon'][$r];
                $item_evening_no   = $_POST['evening'][$r];
                $night_no           = $_POST['night'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = 0;//$this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = 0;//$this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_order_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
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

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                      
                        'morning_no'        => $item_morning_no,
                        'afteroon_no'       => $item_afteroon_no,
                        'evening_no'       => $item_evening_no,
                        'night_no'          => $night_no,
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
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail,
                    ];

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
            $data           = [
                'date' => $date,
                'diagnosis_id'        => $this->input->post('diagnosis'),
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'history'           => $history,
                'symptoms'         => $symptoms,
                'medicine'         => $medicine,
                'age'               => $age,
                'weight'            => $weight,
                'height'            => $height,
                'laboratory'         => $laboratory,
                'vital_signs'         => $vital_signs,
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
                'order_status'          => 'pending',
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'paid'                => 0,
                'saleman_by'          => $this->input->post('saleman_by'),
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
            ];
        //   var_dump($data );exit();
            $payment = [];
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

            // $this->bpas->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_order_model->addPrescription($data, $products, $payment)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('prescription_sucessfull_added'));
            admin_redirect('clinic/prescription');
            
        } else {
            if ($sale_id) {
                if ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                }
                krsort($items);
                $c = rand(100000, 9999999);
            
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis           = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
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
                    $row->discount        = $item->discount ? $item->discount : '0';
                    $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = '';
                    $row->serial_no       = $row->serial_no;
                    $row->option          = $item->option_id;
                    $row->details         = $item->comment;
                    $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);

                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if ($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $Settings                  = $this->site->getSettings();
            $this->data['count']       = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']    = $this->site->getAllProject();
            $this->data['error']       = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']    = $sale_id;
            $this->data['billers']     = $this->site->getAllCompanies('biller');
            $this->data['warehouses']  = $this->site->getAllWarehouses();
            $this->data['tax_rates']   = $this->site->getAllTaxRates();
            $this->data['units']       = $this->site->getAllBaseUnits();
            $this->data['slnumber']    = $this->site->getReference('sr');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['salemans']    = $this->site->getAllSalemans($Settings->group_saleman_id);
            $this->data['customer_id'] = $customer_id ? $customer_id : null;
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_order'), 'page' => lang('sales_order')], ['link' => '#', 'page' => lang('add_prescription')]];
            $meta                      = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('clinic/add_prescription', $meta, $this->data);
        }
    }
    public function edit_prescription($id = null)
    {
        $this->bpas->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_order_model->getPrescriptionByID($id);
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $project_id =$this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            // var_dump($this->session->userdata());
            // exit();
            // if ($this->Owner || $this->Admin || $GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            // } else {
            //     $date = $inv->date;
            // }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $history        = $this->input->post('history');
            $symptoms        = $this->input->post('symptoms');
            $age        = $this->input->post('age');
            $weight        = $this->input->post('weight');
            $height        = $this->input->post('height');
            $medicine        = $this->input->post('medicine');
            $laboratory        = $this->input->post('laboratory');
            $vital_signs        = $this->input->post('vital_signs');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));

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
                $item_morning_no    = $_POST['morning'][$r];
                $item_afteroon_no   = $_POST['afteroon'][$r];
                $item_evening_no   = $_POST['evening'][$r];
                $item_night_no   = $_POST['night'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = 0;//$this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = 0;//$this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial        = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_detail      = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_order_model->getProductByCode($item_code) : null;

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
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'morning_no'        => $item_morning_no,
                        'afteroon_no'       => $item_afteroon_no,
                        'evening_no'        => $item_evening_no,
                        'night_no'        => $item_night_no, 
                        'option_id'         => $item_option,
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
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_detail
                    ]; 
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
            $data           = [
                'date' => $date,
                'diagnosis_id'        => $this->input->post('diagnosis'),
                'reference_no'        => $reference,
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'history'           => $history,
                'symptoms'         => $symptoms,
                'age'               => $age,
                'weight'            => $weight,
                'height'            => $height,
                'medicine'         => $medicine,
                'laboratory'         => $laboratory,
                'vital_signs'         => $vital_signs,
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
                'saleman_by'          => $this->input->post('saleman_by'),
                'updated_by'          => $this->session->userdata('user_id'),
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

            // $this->bpas->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->sales_order_model->updatePrescription($id, $data, $products)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('prescription_updated'));
            admin_redirect('clinic/prescription');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_order_model->getPrescriptionByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_order_model->getAllPrescriptionItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
   
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_order_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                
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
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                
                $row->morning_no        = $item->morning_no;
                $row->afteroon_no       = $item->afteroon_no;
                $row->evening_no       = $item->evening_no;
                $row->night_no          = $item->night_no;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity += $item->quantity;
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
                $row->details         = $item->comment;
                $row->option          = $item->option_id;
                $options              = $this->sales_order_model->getProductOptions($row->id, $item->warehouse_id);
            
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
                    $combo_items = $this->sales_order_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }

                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                $c++;
            }

            $Settings = $this->site->getSettings();
            $this->data['count']      = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            // $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['billers']     = $this->site->getAllCompanies('biller');
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['salemans']   = $this->site->getAllSalemans($Settings->group_saleman_id);

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_prescription')]];
            $meta = ['page_title' => lang('edit_prescription'), 'bc' => $bc];
            $this->page_construct('clinic/edit_prescription', $meta, $this->data);
        }
    }
    public function delete_prescription($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->sales_order_model->getPrescriptionByID($id);
        if ($this->sales_order_model->deletePrescription($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('prescription_deleted')]);
            }
            $this->session->set_flashdata('message', lang('prescription_deleted'));
            admin_redirect('clinic/prescription');
        }
    }
    public function view_prescription($id = null)
    {
        // $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_order_model->getPrescriptionByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode']        = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']       = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']         = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']     = $this->site->getUser($inv->created_by);
        $this->data['updated_by']     = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']      = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']            = $inv;
        $this->data['rows']           = $this->sales_order_model->getAllPrescriptionItems($id);
        $this->data['sold_by']  = $this->site->getsaleman($inv->saleman_by);
        $this->data['diagnosis']  = $this->site->getcustomfieldById($inv->diagnosis_id);
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        //$this->page_construct('clinic/view_prescription', $meta, $this->data);
        $this->load->view($this->theme . 'clinic/view_prescription', $this->data);
    }
    public function dental_clinic($id = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_order_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_order_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_order_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_order_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_order_model->getAllInvoiceItems($inv->return_id) : null;

        $this->data['saleman']   = $this->site->getsaleman($inv->saleman_by);

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('clinic/view_clinic_plan', $meta, $this->data);
    }

    public function add_draw($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->sales_order_model->getInvoiceByID($id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['draw_image'] = $this->sales_order_model->getLastDrawImage($id, $inv->customer_id);
        $this->data['page_title'] = lang('add_draw');

        $this->load->view($this->theme . 'clinic/add_draw', $this->data);
    }

    public function wpain_upload($id = null)
    {
        $teeth_model = $_POST["teeth_model"];
        $image       = $_POST['image'];
        $sale_id     = $_POST['sale_id'];
        $customer_id = $_POST['customer_id'];
        if ($teeth_model == "teeth_model1") {
            $teeth_model = "Adult";
        } elseif ($teeth_model== "teeth_model2") {
            $teeth_model = "Child";
        } else {
            $teeth_model ="AdultChild";
        }
        $image = imagecreatefrompng($image);
        $id    = uniqid();
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $image_name = $teeth_model.'-' . $id . '.png';
        imagepng($image, 'assets/uploads/wpain/' . $image_name . '');
        $this->db->update('sales_order', ['image' => $image_name], ['id' => $sale_id]);
        // $this->db->update('companies', ['attachment' => $image_name], ['id' => $customer_id]);
        echo 'success';
    }

    public function operation_categories() 
    {
        $this->bpas->checkPermissions('add', null);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('operation_categories')]];
        $meta = ['page_title' => lang('operation_categories'), 'bc' => $bc];
        $this->page_construct('clinic/operation_categories', $meta, $this->data);
    }

    public function getOperationCategories()
    {
        $this->load->library('datatables');
        $this->datatables->select("
                {$this->db->dbprefix('clinic_op_categories')}.id as id, 
                {$this->db->dbprefix('clinic_op_categories')}.name, 
                {$this->db->dbprefix('clinic_op_categories')}.description, 
                c.name as parent", false)
            ->from('clinic_op_categories')
            ->join('clinic_op_categories c', 'c.id=clinic_op_categories.parent_id', 'left')
            ->group_by('clinic_op_categories.id')
            ->order_by('clinic_op_categories.parent_id, clinic_op_categories.name')
            ->add_column('Actions', '<div class="text-center">' . " <a href='" . admin_url('clinic/edit_operation_category/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_operation_category') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_operation_category') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_operation_category/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        echo $this->datatables->generate();
    }

    public function add_operation_category()
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang('name'), 'trim|is_unique[clinic_op_categories.name]|required');
        $this->form_validation->set_rules('description', lang('description'), 'trim');
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'status'      => $this->input->post('status'),
                'parent_id'   => $this->input->post('parent'),
            ];
        } elseif ($this->input->post('add_operation_category')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addOperationCategory($data)) {
            $this->session->set_flashdata('message', lang('operation_category_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/add_operation_category', $this->data);
        }
    }

    public function edit_operation_category($id = null)
    {
        $this->load->helper('security');
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        $pr_details = $this->clinic_model->getOperationCategoryByID($id);
        if ($this->input->post('name') != $pr_details->name) {
            $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[clinic_op_categories.name]');
        }
        $this->form_validation->set_rules('description', lang('description'), 'trim');
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'description' => $this->input->post('description'),
                'parent_id'   => $this->input->post('parent'),
                'status'      => $this->input->post('status'),
            ];
        } elseif ($this->input->post('edit_operation_category')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('clinic/operation_categories');
        }
        if ($this->form_validation->run() == true && $this->clinic_model->updateOperationCategory($id, $data)) {
            $this->session->set_flashdata('message', lang('operation_category_updated'));
            admin_redirect('clinic/operation_categories');
        } else {
            $this->data['error']      = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['category']   = $this->clinic_model->getOperationCategoryByID($id);
            $this->data['categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']   = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/edit_operation_category', $this->data);
        }
    }

    public function delete_operation_category($id = null)
    {
        if ($this->clinic_model->deleteOperationCategory($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('operation_category_deleted')]);
        }
    }

    public function operation_category_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->clinic_model->deleteOperationCategory($id);
                    }
                    $this->session->set_flashdata('message', lang('operation_categories_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('operation_categories'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('parent_category'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->clinic_model->getOperationCategoryByID($id);
                        $parent_category = '';
                        if ($sc->parent_id) {
                            $pc = $this->clinic_model->getOperationCategoryByID($sc->parent_id);
                            $parent_category = $pc->code;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->description);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $parent_category);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'operation_categories_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function operations($biller_id = null) 
    {
        $this->bpas->checkPermissions('index',null);
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
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
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('operations')]];
        $meta = ['page_title' => lang('operations'), 'bc' => $bc];
        $this->page_construct('clinic/operations', $meta, $this->data);
    }

    public function getOperations($biller_id = null)
    { 
        $this->bpas->checkPermissions('index', null);
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
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
        $detail_link = anchor('admin/clinic/modal_view_operation/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/clinic/edit_operation/$1', '<i class="fa fa-edit"></i> ' . lang('edit_operation'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_operation') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_operation/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_operation') . '</a>';
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
        $this->datatables->select(
                $this->db->dbprefix('clinic_operations') . ".id as id, 
                {$this->db->dbprefix('clinic_operations')}.date, 
                IF ({$this->db->dbprefix('biilers')}.company != '-', {$this->db->dbprefix('biilers')}.company, {$this->db->dbprefix('biilers')}.name) AS biller,
                {$this->db->dbprefix('companies')}.name as patience,
                {$this->db->dbprefix('op_categories')}.name as operation_category, 
                {$this->db->dbprefix('op_names')}.name as operation_name,
                CONCAT({$this->db->dbprefix('doctor')}.first_name, ' ', {$this->db->dbprefix('doctor')}.last_name) as doctor, 
                {$this->db->dbprefix('clinic_operations')}.assistant, 
                {$this->db->dbprefix('clinic_operations')}.anesthetist, 
                {$this->db->dbprefix('clinic_operations')}.ot_technician, 
                {$this->db->dbprefix('clinic_operations')}.note as note,
                {$this->db->dbprefix('clinic_operations')}.result as result,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user,
                {$this->db->dbprefix('clinic_operations')}.attachment"
            , false)
            ->from('clinic_operations')
            ->join('companies bpas_biilers', 'biilers.id = clinic_operations.biller_id', 'left')  
            ->join('companies', 'companies.id = clinic_operations.patience_id', 'left')  
            ->join('users', 'users.id=clinic_operations.created_by', 'left')
            ->join('clinic_op_categories bpas_op_categories', 'op_categories.id=clinic_operations.operation_category', 'left')
            ->join('clinic_op_categories bpas_op_names', 'op_names.id=clinic_operations.operation_name', 'left')
            ->join('users bpas_doctor', 'doctor.id=clinic_operations.doctor', 'left');
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('clinic_operations.created_by', $this->session->userdata('user_id'));
        }
        if ($customer) {
            $this->datatables->where('clinic_operations.patience_id', $customer);
        }
        if ($biller_id) {
            $this->datatables->where('clinic_operations.biller_id', $biller_id);
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_operation($customer_id=null)
    {
        $this->load->helper('security');
        $this->bpas->checkPermissions('add', null);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('operation_category', lang('operation_category'), 'required');
        $this->form_validation->set_rules('operation_name', lang('operation_name'), 'required');
        $this->form_validation->set_rules('doctor', lang('doctor'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'               => $date,
                'biller_id'          => $this->input->post('biller'),
                'patience_id'        => $this->input->post('customer'),
                'doctor'             => $this->input->post('doctor'),
                'operation_category' => $this->input->post('operation_category'),
                'operation_name'     => $this->input->post('operation_name'),
                'assistant'          => $this->input->post('assistant_consultant_1'),
                'assistant_2'        => $this->input->post('assistant_consultant_2'),
                'anesthetist'        => $this->input->post('anesthetist'),
                'anesthesia_type'    => $this->input->post('anesthesia_type'),
                'ot_technician'      => $this->input->post('ot_technician'),
                'ot_assistant'       => $this->input->post('ot_assistant'),
                'note'               => $this->input->post('note'),
                'result'             => $this->input->post('result'),
                'created_by'         => $this->session->userdata('user_id'),
                'created_at'         => date('Y-m-d H:i:s')
            ];
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('add_operation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addOperation($data)) {
            $this->session->set_flashdata('message', lang('operation_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['agents']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['users']     = $this->site->getStaff();
            $this->data['customer_id']      = $customer_id ? $customer_id : null;
            $this->data['parent_operation_categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/add_operation', $this->data);
        }
    }
    public function edit_operation($id = null)
    {
        $this->load->helper('security');
        $this->bpas->checkPermissions('edit', null);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('operation_category', lang('operation_category'), 'required');
        $this->form_validation->set_rules('operation_name', lang('operation_name'), 'required');
        $this->form_validation->set_rules('doctor', lang('doctor'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'               => $date,
                'biller_id'          => $this->input->post('biller'),
                'patience_id'        => $this->input->post('customer'),
                'doctor'             => $this->input->post('doctor'),
                'operation_category' => $this->input->post('operation_category'),
                'operation_name'     => $this->input->post('operation_name'),
                'assistant'          => $this->input->post('assistant_consultant_1'),
                'assistant_2'        => $this->input->post('assistant_consultant_2'),
                'anesthetist'        => $this->input->post('anesthetist'),
                'anesthesia_type'    => $this->input->post('anesthesia_type'),
                'ot_technician'      => $this->input->post('ot_technician'),
                'ot_assistant'       => $this->input->post('ot_assistant'),
                'note'               => $this->input->post('note'),
                'result'             => $this->input->post('result'),
                'updated_by'         => $this->session->userdata('user_id'),
                'updated_at'         => date('Y-m-d H:i:s')
            ];
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('edit_operation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->updateOperation($id, $data)) {
            $this->session->set_flashdata('message', lang('operation_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['operation'] = $this->clinic_model->getOperationByID($id);
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['agents']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['users']     = $this->site->getStaff();
            $this->data['parent_operation_categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/edit_operation', $this->data);
        }
    }

    public function delete_operation($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if($this->clinic_model->deleteOperation($id)){
            $this->bpas->send_json(['error' => 0, 'msg' => lang('operation_deleted')]);
        }  
        $this->bpas->send_json(['error' => 1, 'msg' => lang('operation_delete_failed')]);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function getOperationCategoriesByParent()
    {
        $id = $this->input->get('operation_category_id') ? $this->input->get('operation_category_id') : null;
        if (!empty($id)) {
            $operation_categories = $this->clinic_model->getOperationCategoriesByParent($id);
            $this->bpas->send_json($operation_categories);
        }
        $this->bpas->send_json(false);
    }

    public function modal_view_operation($id = null)
    {
        $this->bpas->checkPermissions('index');
        $operation = $this->clinic_model->getOperationByID($id);
        $this->data['operation_category'] = $this->clinic_model->getOperationCategoryByID($operation->operation_category);
        $this->data['operation_name']     = $this->clinic_model->getOperationCategoryByID($operation->operation_name);
        $this->data['doctor']             = $this->site->getUser($operation->doctor);
        $this->data['created_by']         = $this->site->getUser($operation->created_by);
        $this->data['updated_by']         = $operation->updated_by ? $this->site->getUser($operation->updated_by) : null;
        $this->data['patience']           = $this->site->getCompanyByID($operation->patience_id);
        $this->data['biller']             = $operation->biller_id ? $this->site->getCompanyByID($operation->biller_id) : null;
        $this->data['operation']          = $operation;
        $this->data['page_title']         = $this->lang->line('view_operation');
        $this->load->view($this->theme . 'clinic/modal_view_operation', $this->data);
    }

    public function operation_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->clinic_model->deleteOperation($id);
                    }
                    $this->session->set_flashdata('message', lang('operations_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('operations'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('patience'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('operation_category'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('operation_name'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('doctor'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', (lang('assistant') . '_1'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', (lang('assistant') . '_2'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('anesthetist'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('anesthesia_type'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('ot_technician'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('ot_assistant'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('result'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $operation          = $this->clinic_model->getOperationByID($id);
                        $operation_category = $operation->operation_category ? $this->clinic_model->getOperationCategoryByID($operation->operation_category) : null;
                        $operation_name     = $operation->operation_name ? $this->clinic_model->getOperationCategoryByID($operation->operation_name) : null;
                        $biller             = $operation->biller_id ? $this->site->getCompanyByID($operation->biller_id) : null;
                        $doctor             = $operation->doctor ? $this->site->getUser($operation->doctor) : null;
                        $patience           = $operation->patience_id ? $this->site->getCompanyByID($operation->patience_id) : null;
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($operation->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, ($biller ? ($biller->company != '-' ? $biller->company : $biller->name) : ''));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, ($patience ? ($patience->company != '-' ? $patience->company : $patience->name) : ''));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, ($operation_category ? $operation_category->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, ($operation_name ? $operation_name->name : ''));
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, ($doctor ? ($doctor->first_name . ' ' . $doctor->last_name) : ''));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $operation->assistant);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $operation->assistant_2);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $operation->anesthetist);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $operation->anesthesia_type);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $operation->ot_technician);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $operation->ot_assistant);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, strip_tags($operation->note));
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, strip_tags($operation->result));
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
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'operations_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_record_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function medicines($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));

        $products   = $this->site->getAllProducts();
        $warehouses = $this->site->getAllWarehouses();
        $sync       = $this->input->get('sync') ? $this->input->get('sync') : null;
        if ($sync == 'sync_quantity_all') {
            foreach ($products as $product) {
                $this->site->syncQuantity_13_05_21($product->id);
            }
            $this->session->set_flashdata('message', $this->lang->line('products_quantity_sync'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id ? $warehouse_id : null;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            
            $this->data['count_warehouses'] = $count;
            $this->data['user_warehouse']   = (isset($count) && count($count) == 1) ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['warehouse_id']     = $warehouse_id;
            $this->data['warehouse']        = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $this->data['products']   = $this->site->getProducts();
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['nest_categories']     = $this->site->getNestedCategories();
        $this->data['supplier']   = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : null;

        $bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('medinices')]];
        $meta                   = ['page_title' => lang('medinices'), 'bc' => $bc];
        $this->page_construct('clinic/medinices', $meta, $this->data);
    }
    public function getMedicices($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $product = $this->input->get('product') ? $this->input->get('product') : NULL;
        $category = $this->input->get('category') ? $this->input->get('category') : NULL;
        $product_type = $this->input->get('product_type') ? $this->input->get('product_type') :NULL;
        $start_date = $this->input->get('start_date')? $this->input->get('start_date') : NULL;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : NULL;
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/products/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('product_details'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line('delete_product') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('products/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_product') . '</a>';
        $single_barcode = anchor('admin/products/print_barcodes/$1', '<i class="fa fa-print"></i> ' . lang('print_barcode_label'));
        // $single_label = anchor_popup('products/single_label/$1/' . ($warehouse_id ? $warehouse_id : ''), '<i class="fa fa-print"></i> ' . lang('print_label'), $this->popup_attributes);
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('products/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_product') . '</a></li>
            <li><a href="' . admin_url('clinic/edit_medicince/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_medicince') . '</a></li>';
        if ($warehouse_id) {
            $action .= '<li><a href="' . admin_url('products/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-bars"></i> '
                . lang('set_rack') . '</a></li>';
        }
        $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
            . lang('view_image') . '</a></li>
            <li>' . $single_barcode . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select(
                    $this->db->dbprefix('products') . ".id as productid, 
                    {$this->db->dbprefix('products')}.image as image, 
                    {$this->db->dbprefix('products')}.code as code, 
                    {$this->db->dbprefix('products')}.name as name,
                    {$this->db->dbprefix('products')}.type as type, 
                    {$this->db->dbprefix('brands')}.name as brand, 
                    {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, 
                    COALESCE(wp.quantity, 0) as quantity, 
                    {$this->db->dbprefix('units')}.code as unit, wp.rack as rack, alert_quantity", false)
                ->from('products')
                ->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->join("( SELECT product_id, SUM(quantity) quantity, rack from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id IN ({$warehouse_id}) GROUP BY product_id) wp", 'products.id=wp.product_id')
                ->group_by('products.id')
                ->order_by('products.name');
        } else {
            $this->datatables
                ->select(
                    $this->db->dbprefix('products') . ".id as productid, 
                    {$this->db->dbprefix('products')}.image as image, 
                    {$this->db->dbprefix('products')}.code as code, 
                    {$this->db->dbprefix('products')}.name as name, 
                    {$this->db->dbprefix('products')}.type as type, 
                    {$this->db->dbprefix('brands')}.name as brand, 
                    {$this->db->dbprefix('categories')}.name as cname, cost as cost, price as price, 
                    COALESCE({$this->db->dbprefix('products')}.quantity, 0) as quantity, 
                    {$this->db->dbprefix('units')}.code as unit, '' as rack, alert_quantity", false)
                ->from('products')->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->group_by('products.id')
                ->order_by('products.name');

            if ((!$this->Owner && !$this->Admin)) {
                if($this->session->userdata('warehouse_id')){
                    $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id');
                    $this->datatables->where_in('wp.warehouse_id', $this->session->userdata('warehouse_id'));
                }
            }
        }
        $this->datatables->where('products.is_medicince',1);   
        if (!$this->Owner && !$this->Admin) {
            if (!$this->GP['products-cost']) {
                $this->datatables->unset_column('cost');
            }
            if (!$this->GP['products-price']) {
                $this->datatables->unset_column('price');
            }
        }
        if ($product) {
            $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
        }
        if ($category) {
            $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
        }
        /*
        if ($product_type) {
            $this->datatables->where($this->db->dbprefix('products') . ".inactived", $product_type);
        }else{
            $this->datatables->where($this->db->dbprefix('products') . ".inactived !=", '1');
        }*/  
        if ($supplier) {
            $this->datatables->where('supplier1', $supplier)
            ->or_where('supplier2', $supplier)
            ->or_where('supplier3', $supplier)
            ->or_where('supplier4', $supplier)
            ->or_where('supplier5', $supplier);
        }
        $this->datatables->add_column('Actions', $action, 'productid, image, code, name');
        echo $this->datatables->generate();
    }
    public function add_medicine($id = null, $type = null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if($this->input->post('default_sale_unit') && $this->input->post('default_purchase_unit')){
            $default_sale_unit = $this->site->getUnitByID($this->input->post('default_sale_unit'));
            $default_purchase_unit = $this->site->getUnitByID($this->input->post('default_purchase_unit'));
        }
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules($default_sale_unit->code."_cost", lang('product_cost'), 'required');
            $this->form_validation->set_rules($default_purchase_unit->code."_price", lang('product_unit'), 'required');
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]|alpha_dash');
        if (SHOP) {
            $this->form_validation->set_rules('slug', lang('slug'), 'required|is_unique[products.slug]|alpha_dash');
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');
        $this->form_validation->set_rules('serial_no', lang('serial_no'), 'is_unique[products.serial_no]|alpha_dash');
        if ($this->input->post('units_div[]')) { 
            $units_div = $this->input->post('units_div[]');
            for ($j=0 ; $j < sizeof($units_div); $j++) { 
                $this->form_validation->set_rules($units_div[$j]."_code" , lang('product_code'), 'is_unique[cost_price_by_units.product_code]|alpha_dash');
            }
        }
        $item_code   = $this->input->post('item_code');
        $product_code = $this->input->post('code');
        $prod_code    = $item_code ? ($product_code."|".$item_code) : $product_code;
        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : null;
            $units_div = $this->input->post('units_div[]');
            for ($j=0; $j < sizeof($units_div); $j++) { 
                if (filter_var($this->input->post($units_div[$j]."_cost"), FILTER_VALIDATE_FLOAT) === false || filter_var($this->input->post($units_div[$j]."_price"), FILTER_VALIDATE_FLOAT) === false) {
                    $this->session->set_flashdata('error', 'Please input cost and price decimal number!');
                    admin_redirect('products/add');
                }
                $unit = $this->site->getUnitByCode($units_div[$j]);
                $unit_data = [
                    'unit_id'      => $unit->id,
                    'price'        => $this->input->post($units_div[$j]."_price"),
                    'cost'         => $this->input->post($units_div[$j]."_cost"),
                    'product_code' => $this->input->post($units_div[$j]."_code"),
                ];  
                $unit_datas[] = $unit_data;
            }
            $punit = $this->site->getUnitByID($this->input->post('unit'));
            $stock_type_selected = implode(',', $this->input->post('stock_type'));
            if (filter_var($this->input->post($punit->code."_cost"), FILTER_VALIDATE_FLOAT) === false || filter_var($this->input->post($punit->code."_price"), FILTER_VALIDATE_FLOAT) === false) {
                $this->session->set_flashdata('error', 'Please input cost and price decimal number!');
                admin_redirect('products/add');
            }
            $data  = [
                'code'              => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'item_code'         => $item_code,
                'serial_no'         => $this->input->post('serial_no'),
                'max_serial'        => $this->input->post('max_serial'),
                'batch_numer'        => $this->input->post('batch_numer'),
                'name'              => $this->input->post('name'),
                'type'              => $this->input->post('type'),
                'brand'             => $this->input->post('brand'),
                'stock_type'        => $stock_type_selected,
                'category_id'       => $this->input->post('category'),
                'subcategory_id'    => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost'              => $this->bpas->formatDecimal($this->input->post($punit->code."_cost")),
                'price'             => $this->bpas->formatDecimal($this->input->post($punit->code."_price")),
                'other_cost'        => $this->bpas->formatDecimal($this->input->post('other_cost')),
                'other_price'       => $this->bpas->formatDecimal($this->input->post('other_price')),
                'currency'          => $this->input->post('currency'),
                'unit'              => $this->input->post('unit'),
                'sale_unit'         => $this->input->post('default_sale_unit'),
                'purchase_unit'     => $this->input->post('default_purchase_unit'),
                'tax_rate'          => $this->input->post('tax_rate'),
                'tax_method'        => $this->input->post('tax_method'),
                'alert_quantity'    => ($this->input->post('alert_quantity') != null && $this->input->post('alert_quantity') != '') ? $this->input->post('alert_quantity') : null,
                'expiry_alert_days' => ($this->input->post('expiry_alert_days') != null && $this->input->post('expiry_alert_days') != '') ? $this->input->post('expiry_alert_days') : null,
                'track_quantity'    => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details'           => $this->input->post('details'),
                'product_details'   => $this->input->post('product_details'),
                'supplier1'         => $this->input->post('supplier'),
                'supplier1price'    => $this->bpas->formatDecimal($this->input->post('supplier_price')),
                'supplier2'         => $this->input->post('supplier_2'),
                'supplier2price'    => $this->bpas->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3'         => $this->input->post('supplier_3'),
                'supplier3price'    => $this->bpas->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4'         => $this->input->post('supplier_4'),
                'supplier4price'    => $this->bpas->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5'         => $this->input->post('supplier_5'),
                'supplier5price'    => $this->bpas->formatDecimal($this->input->post('supplier_5_price')),
                'cf1'               => $this->input->post('cf1'),
                'cf2'               => $this->input->post('cf2'),
                'cf3'               => $this->input->post('cf3'),
                'cf4'               => $this->input->post('cf4'),
                'cf5'               => $this->input->post('cf5'),
                'cf6'               => $this->input->post('cf6'),
                'promotion'         => $this->input->post('promotion'),
                'promo_price'       => $this->bpas->formatDecimal($this->input->post('promo_price')),
                'start_date'        => $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : null,
                'end_date'          => $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : null,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file'              => $this->input->post('file_link'),
                'slug'              => $this->input->post('slug'),
                'weight'            => $this->input->post('weight'),
                'featured'          => $this->input->post('featured'),
                'hsn_code'          => $this->input->post('hsn_code'),
                'hide'              => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'second_name'       => $this->input->post('second_name'),
                'status'            => $this->input->post('status'),
                'is_medicince'      => 1,
            ]; 
            $product_account = [];
            if ($this->Settings->accounting == 1) {
                $product_account = array(
                    'revenue_account'   => $this->input->post('revenue_account'),
                    'stock_account'     => $this->input->post('stock_account'),
                    'costing_account'   => $this->input->post('pro_cost_account'),
                    'adjustment_account'=> $this->input->post('adjustment_account'),
                    'using_account'     => $this->input->post('stock_using_account'),
                    'convert_account'   => $this->input->post('convert_account'),
                    'ar_account'        => $this->input->post('ar_account'),
                );
            }   
            // $this->bpas->print_arrays($data, $unit_datas); 
            $warehouse_qty      = null;
            $product_attributes = null;
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s]           = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if (isset($_POST['wh_qty_' . $warehouse->id]) && !empty($_POST['wh_qty_' . $warehouse->id]) && $_POST['wh_qty_' . $warehouse->id] != '') {
                        $warehouse_qty[] = [
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity'     => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack'         => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null,
                        ];
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                } 
                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = [
                                'name'         => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity'     => $_POST['attr_quantity'][$r],
                                'price'        => $_POST['attr_price'][$r],
                            ];
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }
                } else {
                    $product_attributes = null;
                }
                //------option-----------
                if ($this->input->post('product_option')) {
                    $a = sizeof($_POST['product_option']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['product_option'][$r])) {
                            $product_options[] = [
                                'option_id' => $_POST['product_option'][$r],
                            ];
                        }
                    }
                } else {
                    $product_options = null;
                } 
                if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }
            } 
            if ($this->input->post('type') == 'combo') {
                $total_price = 0;
                $c           = sizeof($_POST['combo_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                        $items[] = [
                            'item_code'  => $_POST['combo_item_code'][$r],
                            'quantity'   => $_POST['combo_item_quantity'][$r],
                            'unit_price' => $_POST['combo_item_price'][$r],
                        ];
                    }
                    $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                }
                if($this->Settings->combo_price_match == 1){
                    if ($this->bpas->formatDecimal($total_price) != $this->bpas->formatDecimal($this->input->post('price'))) {
                        $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                        $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                    }
                }
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = null;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['max_filename']  = 25;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/add');
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
            if ($_FILES['userfile']['name'][0] != '') {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $files                   = $_FILES;
                $cpt                     = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name']     = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type']     = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error']    = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size']     = $files['userfile']['size'][$i];
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                    } else {
                        $pho = $this->upload->file_name;
                        $photos[] = $pho;
                        $this->load->library('image_lib');
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $this->upload_path . $pho;
                        $config['new_image']      = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width']          = $this->Settings->twidth;
                        $config['height']         = $this->Settings->theight;
                        $this->image_lib->initialize($config);
                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }
                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image']     = $this->upload_path . $pho;
                            $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type']          = 'text';
                            $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                            $wm['quality']          = '100';
                            $wm['wm_font_size']     = '16';
                            $wm['wm_font_color']    = '999999';
                            $wm['wm_shadow_color']  = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding']       = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }
                        $this->image_lib->clear();
                    }
                }
                $config = null;
            } else {
                $photos = null;
            }
            $data['quantity'] = $wh_total_quantity ?? 0;
        }
        if ($this->form_validation->run() == true && $this->products_model->addProduct($data, $items, $warehouse_qty, $product_attributes, $photos,null, null, $unit_datas, $product_account)) {
            $this->session->set_flashdata('message', lang('medicine_added'));
            admin_redirect('clinic/medicines');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['type']                = $type;
            $this->data['projects']            = $this->site->getAllProject();
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['nest_categories']     = $this->site->getNestedByCategories();
            //$this->data['nest_categories']     = $this->site->getNestedCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['stock_types']         = $this->site->getAllStockType();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : null;
            $this->data['product']             = $id ? $this->products_model->getProductByID($id) : null;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['combo_items']         = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : null;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['options']             = $this->products_model->getAllOptions();
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($id) : null;
            $this->data['chart_accounts']      = $this->accounts_model->getAllChartAccounts();
            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('add_medicince')]];
            $meta                              = ['page_title' => lang('add_medicince'), 'bc' => $bc];
            $this->page_construct('clinic/add_medicince', $meta, $this->data);
        }
    }
    public function edit_medicince($id = null, $type=null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses          = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product             = $this->site->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->form_validation->set_rules('category', lang('category'), 'required|is_natural_no_zero');
        if($this->input->post('default_sale_unit') && $this->input->post('default_purchase_unit')){
            $default_sale_unit = $this->site->getUnitByID($this->input->post('default_sale_unit'));
            $default_purchase_unit = $this->site->getUnitByID($this->input->post('default_purchase_unit'));
        }
        if ($this->input->post('units_div[]') == NULL) {
            $units_div = $this->input->post('units_div2[]');
        } else {
            $units_div = $this->input->post('units_div[]');
        }
        $unit_id = $this->input->post('unit');
        $units   = $this->site->getUnitsByBUID($unit_id);
        foreach($units as $unit) {
            $unit_arr[] = array(
                'cost'          => $this->input->post($unit->code . '_cost'),
                'unit_id'       => $unit->id,
                'price'         => $this->input->post($unit->code . '_price'),
                'product_code'  => $this->input->post($unit->code.'_code') ? $this->input->post($unit->code.'_code') : null
            );  
        }
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('unit', lang('product_unit'), 'required');
            $this->form_validation->set_rules($default_sale_unit->code."_cost", lang('product_cost'), 'required');
            $this->form_validation->set_rules($default_purchase_unit->code."_price", lang('product_price'), 'required');
        }
        foreach ($units as $unit) {
            $this->form_validation->set_rules($unit->code.'_code', lang('product_code'), 'alpha_dash');
            if(isset($_POST[$unit->code.'_code'])){
                $productcodeunit = $this->site->getProductCostPriceByID($unit->id, $id);
                $pcodes = $this->input->post($unit->code.'_code');
                if( $productcodeunit->product_code != $pcodes){
                    $checkdubplicate = $this->site->getCostPriceUnit($pcodes);
                    if($checkdubplicate){
                        $this->session->set_flashdata('error', lang('Product_code_has_already_!'));
                        admin_redirect('products/edit/' . $id);
                    }
                }
            }
        }
        $this->form_validation->set_rules('code', lang('product_code'), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang('product_code'), 'is_unique[products.code]');
        }
        $this->form_validation->set_rules('weight', lang('weight'), 'numeric');
        $this->form_validation->set_rules('product_image', lang('product_image'), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang('digital_file'), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang('product_gallery_images'), 'xss_clean');

        $item_code   = $this->input->post('item_code');
        $product_code = $this->input->post('code');
        $prod_code    = $item_code ? ($product_code."|".$item_code) : $product_code;

        if ($this->form_validation->run('products/add') == true) {
            $punit = $this->site->getUnitByID($unit_id);
            $stock_type_selected = implode(',', $this->input->post('stock_type'));
            $data  = [
                'code'              => $prod_code,
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'item_code'         => $item_code,
                'serial_no'         => $this->input->post('serial_no'),
                'max_serial'        => $this->input->post('max_serial'),
                'batch_numer'        => $this->input->post('batch_numer'),
                'name'              => $this->input->post('name'),
                'type'              => $this->input->post('type'),
                'brand'             => ($this->input->post('brand') ? $this->input->post('brand') : 0),
                'stock_type'        => $stock_type_selected,
                'category_id'       => $this->input->post('category'),
                'subcategory_id'    => $this->input->post('subcategory') ? $this->input->post('subcategory') : null,
                'cost'              => $this->bpas->formatDecimal($this->input->post($punit->code."_cost")),
                'price'             => $this->bpas->formatDecimal($this->input->post($punit->code."_price")),
                'other_cost'        => $this->bpas->formatDecimal($this->input->post('other_cost')),
                'other_price'       => $this->bpas->formatDecimal($this->input->post('other_price')),
                'currency'          => $this->input->post('currency'),
                'unit'              => $this->input->post('unit'),
                'sale_unit'         => $this->input->post('default_sale_unit'),
                'purchase_unit'     => $this->input->post('default_purchase_unit'),
                'tax_rate'          => $this->input->post('tax_rate'),
                'tax_method'        => $this->input->post('tax_method'),
                'alert_quantity'    => ($this->input->post('alert_quantity') != null && $this->input->post('alert_quantity') != '') ? $this->input->post('alert_quantity') : null,
                'expiry_alert_days'    => ($this->input->post('expiry_alert_days') != null && $this->input->post('expiry_alert_days') != '') ? $this->input->post('expiry_alert_days') : null,
                'track_quantity'    => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details'           => $this->input->post('details'),
                'product_details'   => $this->input->post('product_details'),
                'supplier1'         => ($this->input->post('supplier') ? $this->input->post('supplier') : 0),
                'supplier1price'    => $this->bpas->formatDecimal($this->input->post('supplier_price')),
                'supplier2'         => $this->input->post('supplier_2'),
                'supplier2price'    => $this->bpas->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3'         => $this->input->post('supplier_3'),
                'supplier3price'    => $this->bpas->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4'         => $this->input->post('supplier_4'),
                'supplier4price'    => $this->bpas->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5'         => $this->input->post('supplier_5'),
                'supplier5price'    => $this->bpas->formatDecimal($this->input->post('supplier_5_price')),
                'cf1'               => $this->input->post('cf1'),
                'cf2'               => $this->input->post('cf2'),
                'cf3'               => $this->input->post('cf3'),
                'cf4'               => $this->input->post('cf4'),
                'cf5'               => $this->input->post('cf5'),
                'cf6'               => $this->input->post('cf6'),
                'promotion'         => $this->input->post('promotion'),
                'promo_price'       => $this->bpas->formatDecimal($this->input->post('promo_price')),
                'start_date'        => $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : null,
                'end_date'          => $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : null,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'slug'              => $this->input->post('slug'),
                'weight'            => $this->input->post('weight'),
                'featured'          => $this->input->post('featured'),
                'hsn_code'          => $this->input->post('hsn_code'),
                'hide'              => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'is_medicince'      => 1,
                'second_name'       => $this->input->post('second_name'),
                'status'            => $this->input->post('status'),
            ];
            if($this->Settings->cbm == 1){
                $data['p_length'] = $this->input->post('p_length');
                $data['p_width'] = $this->input->post('p_width');
                $data['p_height'] = $this->input->post('p_height');
                $data['p_weight'] = $this->input->post('p_weight');
            }
            $product_account =[];
            if($this->Settings->accounting == 1){
                $product_account = array(
                    'revenue_account'      => $this->input->post('revenue_account'),
                    'stock_account'     => $this->input->post('stock_account'),
                    'costing_account'   => $this->input->post('pro_cost_account'),
                    'adjustment_account'=> $this->input->post('adjustment_account'),
                    'using_account'     => $this->input->post('stock_using_account'),
                    'convert_account'   => $this->input->post('convert_account'),
                    'ar_account'        => $this->input->post('ar_account'),
                );
            } 
            $warehouse_qty      = null;
            $product_attributes = null;
            $update_variants    = [];
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = [
                            'id'    => $this->input->post('variant_id_' . $pv->id),
                            'name'  => $this->input->post('variant_name_' . $pv->id),
                            'cost'  => $this->input->post('variant_cost_' . $pv->id),
                            'price' => $this->input->post('variant_price_' . $pv->id),
                        ];
                    }
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s]           = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = [
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack'         => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : null,
                    ];
                }
                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            if ($product_variatnt = $this->products_model->getPrductVariantByPIDandName($id, trim($_POST['attr_name'][$r]))) {
                                $this->form_validation->set_message('required', lang('product_already_has_variant') . ' (' . $_POST['attr_name'][$r] . ')');
                                $this->form_validation->set_rules('new_product_variant', lang('new_product_variant'), 'required');
                            } else {
                                $product_attributes[] = [
                                    'name'         => $_POST['attr_name'][$r],
                                    'warehouse_id' => $_POST['attr_warehouse'][$r],
                                    'quantity'     => $_POST['attr_quantity'][$r],
                                    'price'        => $_POST['attr_price'][$r],
                                ];
                            }
                        }
                    }
                } else {
                    $product_attributes = null;
                }
                //---------option-----------
                if ($this->input->post('product_option')) {
                    $a = sizeof($_POST['product_option']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['product_option'][$r])) {
                            $product_options[] = [
                                'option_id' => $_POST['product_option'][$r],
                            ];
                        }
                    }
                } else {
                    $product_options = null;
                }
            }
            if ($this->input->post('type') == 'service') {
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'combo') {
                if(isset($_POST['combo_item_code'])){
                    $total_price = 0;
                    $c           = sizeof($_POST['combo_item_code']) - 1;
                    for ($r = 0; $r <= $c; $r++) {
                        if (isset($_POST['combo_item_code'][$r]) && isset($_POST['combo_item_quantity'][$r]) && isset($_POST['combo_item_price'][$r])) {
                            $items[] = [
                                'item_code'  => $_POST['combo_item_code'][$r],
                                'quantity'   => $_POST['combo_item_quantity'][$r],
                                'unit_price' => $_POST['combo_item_price'][$r],
                            ];
                        }
                        $total_price += $_POST['combo_item_price'][$r] * $_POST['combo_item_quantity'][$r];
                    }
                    if($this->Settings->combo_price_match == 1){
                        if ($this->bpas->formatDecimal($total_price) != $this->bpas->formatDecimal($this->input->post($default_sale_unit->code . '_price'))) {
                            $this->form_validation->set_rules('combo_price', 'combo_price', 'required');
                            $this->form_validation->set_message('required', lang('pprice_not_match_ciprice'));
                        }
                    }
                }
                $data['track_quantity'] = 0;
            } elseif ($this->input->post('type') == 'digital') {
                if ($this->input->post('file_link')) {
                    $data['file'] = $this->input->post('file_link');
                }
                if ($_FILES['digital_file']['size'] > 0) {
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $config['max_filename']  = 25;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('digital_file')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/add');
                        
                    }
                    $file         = $this->upload->file_name;
                    $data['file'] = $file;
                }
                $config                 = null;
                $data['track_quantity'] = 0;
            }
            if (!isset($items)) {
                $items = null;
            }
            if ($_FILES['product_image']['size'] > 0) {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('products/edit/' . $id);
                }
                $photo         = $this->upload->file_name;
                $data['image'] = $photo;
                $this->load->library('image_lib');
                $config['image_library']  = 'gd2';
                $config['source_image']   = $this->upload_path . $photo;
                $config['new_image']      = $this->thumbs_path . $photo;
                $config['maintain_ratio'] = true;
                $config['width']          = $this->Settings->twidth;
                $config['height']         = $this->Settings->theight;
                $this->image_lib->clear();
                $this->image_lib->initialize($config);
                if (!$this->image_lib->resize()) {
                    echo $this->image_lib->display_errors();
                }
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image']     = $this->upload_path . $photo;
                    $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type']          = 'text';
                    $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                    $wm['quality']          = '100';
                    $wm['wm_font_size']     = '16';
                    $wm['wm_font_color']    = '999999';
                    $wm['wm_shadow_color']  = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding']       = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = null;
            }
            if ($_FILES['userfile']['name'][0] != '') {
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                $files                   = $_FILES;
                $cpt                     = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {
                    $_FILES['userfile']['name']     = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type']     = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error']    = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size']     = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect('products/edit/' . $id);
                    } else {
                        $pho = $this->upload->file_name;
                        $photos[] = $pho;
                        $this->load->library('image_lib');
                        $config['image_library']  = 'gd2';
                        $config['source_image']   = $this->upload_path . $pho;
                        $config['new_image']      = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = true;
                        $config['width']          = $this->Settings->twidth;
                        $config['height']         = $this->Settings->theight;

                        $this->image_lib->initialize($config);
                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image']     = $this->upload_path . $pho;
                            $wm['wm_text']          = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type']          = 'text';
                            $wm['wm_font_path']     = 'system/fonts/texb.ttf';
                            $wm['quality']          = '100';
                            $wm['wm_font_size']     = '16';
                            $wm['wm_font_color']    = '999999';
                            $wm['wm_shadow_color']  = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding']       = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = null;
            } else {
                $photos = null;
            }
            if(isset($_POST['addOn_item_code'])){
                $c = sizeof($_POST['addOn_item_code']) - 1;
                for ($r = 0; $r <= $c; $r++) {
                    if (isset($_POST['addOn_item_code'][$r])) {
                        $addOn_items[] = [
                            'item_code'   => $_POST['addOn_item_code'][$r],
                            'price'       => $_POST['addOn_item_price'][$r],
                            'description' => $_POST['addOn_item_description'][$r]
                        ];
                    }
                }
            }
            if (!isset($addOn_items)) {
                $addOn_items = null;
            }
            // $data['quantity'] = $wh_total_quantity ?? 0;
            // $this->bpas->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
            // $this->bpas->print_arrays($unit_arr);
            // exit;
        }
        if ($this->form_validation->run() == true && $this->products_model->updateProduct($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants, $addOn_items, $product_options, $unit_arr, $product_account)) {
            $this->session->set_flashdata('message', lang('product_updated'));
            admin_redirect('clinic/medicines');
        } else {
            $this->data['error']               = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currencies']          = $this->bpas->getAllCurrencies();
            $this->data['categories']          = $this->site->getAllCategories();
            $this->data['nest_categories']     = $this->site->getNestedByCategories();
           // $this->data['nest_categories']     = $this->site->getNestedCategories();
            $this->data['tax_rates']           = $this->site->getAllTaxRates();
            $this->data['brands']              = $this->site->getAllBrands();
            $this->data['stock_types']         = $this->site->getAllStockType();
            $this->data['base_units']          = $this->site->getAllBaseUnits();
            $this->data['product_units']       = $this->site->getUnitByProId($id) ? $this->site->getUnitByProId($id) : $this->site->getUnitByProId_($id,$product->unit);
            
            $this->data['warehouses']          = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product']             = $product;
            $this->data['protype']             = $type;
            $this->data['variants']            = $this->products_model->getAllVariants();
            $this->data['subunits']            = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants']    = $this->products_model->getProductOptions($id);
            $this->data['combo_items']         = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : null;
            $this->data['addon_items']         = $id ? $this->products_model->getProductAddOnItems($product->id) : null;
            $this->data['product_options']     = $id ? $this->products_model->getProductOptionsWithWH($id) : null;
            $this->data['options']             = $this->products_model->getAllOptions();
            $this->data['option_product']      = $this->products_model->getOptionProduct($id);

            $this->data['chart_accounts']       = $this->accounts_model->getAllChartAccounts();
            $this->data['productAccount']       = $this->products_model->getProductAccByProductId($product->id);
            $bc                                = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('products'), 'page' => lang('products')], ['link' => '#', 'page' => lang('edit_medicince')]];
            $meta                              = ['page_title' => lang('edit_medicince'), 'bc' => $bc];
            $this->page_construct('clinic/edit_medicince', $meta, $this->data);
        }
    }
    function doctors()
    {
        if ( ! $this->loggedIn) {
            redirect('login');
        }
        $this->bpas->checkPermissions('saleman');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('doctors')));
        $meta = array('page_title' => lang('doctors'), 'bc' => $bc);
        $this->page_construct('clinic/doctors', $meta, $this->data);
    }
    
    function getDoctors()
    {
        $leader_commission = "";
        if($this->config->item("saleman_commission")){
            $leader_commission = "<a href='" .admin_url('auth/share_commissions/$1') . "' class='tip' title='" . lang("share_commissions") . "'><i class=\"fa fa-money\"></i></a>";
        }
        $this->bpas->checkPermissions('doctor');
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users').".id as id, first_name, last_name, gender, phone, position, saleman_commission, saleman_group,zones.zone_name, active")
            ->from("users")
            ->join("zones","zones.id = users.salesman_area","LEFT")
            ->where('users.group_id', $this->Settings->group_saleman_id)
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column("Actions", "<div class=\"text-center\">".$leader_commission.($this->Settings->product_commission == 1 ? "<a href='" 
            . admin_url('auth/salesman_product_commissions/$1') . "' class='tip' title='" 
            . lang("product_commissions") . "'><i class=\"fa fa-eye\"></i></a> " : "")." <a class=\"tip\" title='" 
            . lang("edit_saleman") . "' href='" 
            . admin_url('auth/edit_saleman/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" 
            . lang("delete_saleman") . "</b>' data-content=\"<p>" 
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" 
            . admin_url('auth/delete_saleman/$1') . "'>" 
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" 
            . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
    public function medication_dose($biller_id = null) 
    {
        $this->bpas->checkPermissions('index',null);
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
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
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('medication_dose')]];
        $meta = ['page_title' => lang('medication_dose'), 'bc' => $bc];
        $this->page_construct('clinic/medication_dose', $meta, $this->data);
    }
    public function getMedicationDose($biller_id = null)
    { 
        $this->bpas->checkPermissions('index', null);
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
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
        $detail_link = anchor('admin/clinic/modal_medication_dose/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/clinic/edit_medication_dose/$1', '<i class="fa fa-edit"></i> ' . lang('edit_medication_dose'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_medication_dose') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_medication_dose/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_medication_dose') . '</a>';
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
        $this->datatables->select(
                $this->db->dbprefix('clinic_medication_dose') . ".id as id, 
                {$this->db->dbprefix('clinic_medication_dose')}.date, 
                IF ({$this->db->dbprefix('biilers')}.company != '-', {$this->db->dbprefix('biilers')}.company, {$this->db->dbprefix('biilers')}.name) AS biller,
                {$this->db->dbprefix('companies')}.name as patience,

                {$this->db->dbprefix('categories')}.name as category,
                {$this->db->dbprefix('products')}.name as medicine,

                {$this->db->dbprefix('clinic_medication_dose')}.dosage, 
                {$this->db->dbprefix('clinic_medication_dose')}.note as note,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user"
            , false)
            ->from('clinic_medication_dose')
            ->join('companies bpas_biilers', 'biilers.id = clinic_medication_dose.biller_id', 'left')  
            ->join('companies', 'companies.id = clinic_medication_dose.patience_id', 'left')
            ->join('categories', 'categories.id = clinic_medication_dose.medicine_category', 'left')
            ->join('products', 'products.id = clinic_medication_dose.medicine_name', 'left')
            ->join('users', 'users.id=clinic_medication_dose.created_by', 'left');
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('clinic_medication_dose.created_by', $this->session->userdata('user_id'));
        }
        if ($customer) {
            $this->datatables->where('clinic_medication_dose.patience_id', $customer);
        }
        if ($biller_id) {
            $this->datatables->where('clinic_medication_dose.biller_id', $biller_id);
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_medication_dose($customer_id=null)
    {
        $this->load->helper('security');
        $this->bpas->checkPermissions('add', null);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('medicine_category', lang('medicine_category'), 'required');
        $this->form_validation->set_rules('medicine_name', lang('medicine_name'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'               => $date,
                'biller_id'          => $this->input->post('biller'),
                'patience_id'        => $this->input->post('customer'),
                'medicine_category' => $this->input->post('medicine_category'),
                'medicine_name'     => $this->input->post('medicine_name'),
                'dosage'             => $this->input->post('dosage'),
                'note'               => $this->input->post('note'),
                'created_by'         => $this->session->userdata('user_id'),
                'created_at'         => date('Y-m-d H:i:s')
            ];
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('add_operation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addMedicationDose($data)) {
            $this->session->set_flashdata('message', lang('medication_dose_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['agents']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['users']     = $this->site->getStaff();
            $this->data['nest_categories']     = $this->site->getNestedByCategories();
            $this->data['customer_id']      = $customer_id ? $customer_id : null;
            $this->data['parent_operation_categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/add_medication_dose', $this->data);
        }
    }
    public function edit_medication_dose($id = null)
    {
        $this->load->helper('security');
        $this->bpas->checkPermissions('edit', null);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('medicine_category', lang('medicine_category'), 'required');
        $this->form_validation->set_rules('medicine_name', lang('medicine_name'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'               => $date,
                'biller_id'          => $this->input->post('biller'),
                'patience_id'        => $this->input->post('customer'),
                'medicine_category'  => $this->input->post('medicine_category'),
                'medicine_name'      => $this->input->post('medicine_name'),
                'dosage'             => $this->input->post('dosage'),
                'note'               => $this->input->post('note'),
                'updated_by'         => $this->session->userdata('user_id'),
                'updated_at'         => date('Y-m-d H:i:s')
            ];
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('edit_operation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->updateMedicationDose($id, $data)) {
            $this->session->set_flashdata('message', lang('operation_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['dose'] = $this->clinic_model->getMedicationDoseByID($id);
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['agents']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['users']     = $this->site->getStaff();
            $this->data['nest_categories']     = $this->site->getNestedByCategories();
            $this->data['parent_operation_categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/edit_medication_dose', $this->data);
        }
    }
    public function getDoseCategoriesByParent()
    {
        $id = $this->input->get('dose_category_id') ? $this->input->get('dose_category_id') : null;
        if (!empty($id)) {
            $operation_categories = $this->clinic_model->getMedicationNameByCategory($id);
            $this->bpas->send_json($operation_categories);
        }
        $this->bpas->send_json(false);
    }
    public function delete_medication_dose($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if($this->clinic_model->deleteMedicationDose($id)){
            $this->bpas->send_json(['error' => 0, 'msg' => lang('medication_dose_deleted')]);
        }  
        $this->bpas->send_json(['error' => 1, 'msg' => lang('medication_dose_failed')]);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function modal_medication_dose($id = null)
    {
        $this->bpas->checkPermissions('index');
        $operation = $this->clinic_model->getMedicationDoseByID($id);
        $this->data['operation_category'] = $this->site->getCategoryByID($operation->medicine_category);
        $this->data['operation_name']     = $this->site->getProductByID($operation->medicine_name);
        $this->data['created_by']         = $this->site->getUser($operation->created_by);
        $this->data['updated_by']         = $operation->updated_by ? $this->site->getUser($operation->updated_by) : null;
        $this->data['patience']           = $this->site->getCompanyByID($operation->patience_id);
        $this->data['biller']             = $operation->biller_id ? $this->site->getCompanyByID($operation->biller_id) : null;
        $this->data['operation']          = $operation;
        $this->data['page_title']         = $this->lang->line('view_medication_dose');
        $this->load->view($this->theme . 'clinic/modal_view_medication_dose', $this->data);
    }
     public function pathology($biller_id = null) 
    {
        $this->bpas->checkPermissions('index',null);
        $user = $this->site->getUser($this->session->userdata('user_id'));
        if ($this->Settings->multi_biller) {
            $count = $user->multi_biller ? explode(',', $user->multi_biller) : null;         
        } else {
            $count = $user->biller_id ? ((array) $user->biller_id) : null;
        }
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
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('pathology')]];
        $meta = ['page_title' => lang('pathology'), 'bc' => $bc];
        $this->page_construct('clinic/pathology', $meta, $this->data);
    }
    public function getPathology($biller_id = null)
    { 
        $this->bpas->checkPermissions('index', null);
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller;
            } else {
                $biller_id = $user->biller_id;
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
        $detail_link = anchor('admin/clinic/modal_view_pathology/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link   = anchor('admin/clinic/edit_pathology/$1', '<i class="fa fa-edit"></i> ' . lang('edit_pathology'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_pathology') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_pathology/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_pathology') . '</a>';
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
        $this->datatables->select(
                $this->db->dbprefix('clinic_pathology') . ".id as id, 
                {$this->db->dbprefix('clinic_pathology')}.date, 
                IF ({$this->db->dbprefix('biilers')}.company != '-', {$this->db->dbprefix('biilers')}.company, {$this->db->dbprefix('biilers')}.name) AS biller,
                {$this->db->dbprefix('clinic_pathology')}.test_name,
                {$this->db->dbprefix('clinic_pathology')}.test_type, 
                {$this->db->dbprefix('op_categories')}.name as category, 
                {$this->db->dbprefix('companies')}.name as patience,
               
                {$this->db->dbprefix('clinic_pathology')}.method as method,
                {$this->db->dbprefix('clinic_pathology')}.report_day as report_day,
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user"
            , false)
            ->from('clinic_pathology')
            ->join('companies bpas_biilers', 'biilers.id = clinic_pathology.biller_id', 'left')  
            ->join('companies', 'companies.id = clinic_pathology.patience_id', 'left')  
            ->join('clinic_op_categories bpas_op_categories', 'op_categories.id=clinic_pathology.category', 'left')
            ->join('users', 'users.id=clinic_pathology.created_by', 'left');


        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('clinic_pathology.created_by', $this->session->userdata('user_id'));
        }
        if ($customer) {
            $this->datatables->where('clinic_pathology.patience_id', $customer);
        }
        if ($biller_id) {
            $this->datatables->where('clinic_pathology.biller_id', $biller_id);
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_pathology($customer_id=null)
    {
        $this->load->helper('security');
        $this->bpas->checkPermissions('add', null);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('category', lang('category'), 'required');
        $this->form_validation->set_rules('test_name', lang('test_name'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'               => $date,
                'biller_id'          => $this->input->post('biller'),
                'patience_id'        => $this->input->post('customer'),
                'category'          => $this->input->post('category'),
                'test_name'         => $this->input->post('test_name'),
                'test_type'         => $this->input->post('test_type'),
                'method'            => $this->input->post('method'),
                'report_day'        => $this->input->post('report_day'),
                'note'               => $this->input->post('note'),
                'result'             => $this->input->post('result'),
                'created_by'         => $this->session->userdata('user_id'),
                'created_at'         => date('Y-m-d H:i:s')
            ];
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('add_operation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addPathology($data)) {
            $this->session->set_flashdata('message', lang('pathology_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['agents']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['users']     = $this->site->getStaff();
            $this->data['customer_id']      = $customer_id ? $customer_id : null;
            $this->data['parent_operation_categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/add_pathology', $this->data);
        }
    }
    public function edit_pathology($id = null)
    {
        $this->load->helper('security');
        $this->bpas->checkPermissions('edit', null);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('category', lang('category'), 'required');
        $this->form_validation->set_rules('test_name', lang('test_name'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date'               => $date,
                'biller_id'          => $this->input->post('biller'),
                'patience_id'        => $this->input->post('customer'),
                'category'          => $this->input->post('category'),
                'test_name'         => $this->input->post('test_name'),
                'test_type'         => $this->input->post('test_type'),
                'method'            => $this->input->post('method'),
                'report_day'        => $this->input->post('report_day'),
                'note'               => $this->input->post('note'),
                'result'             => $this->input->post('result'),
                'updated_by'         => $this->session->userdata('user_id'),
                'updated_at'         => date('Y-m-d H:i:s')
            ];
            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            // $this->bpas->print_arrays($data);
        } elseif ($this->input->post('edit_operation')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->updatePathology($id, $data)) {
            $this->session->set_flashdata('message', lang('pathology_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['operation'] = $this->clinic_model->getPathologyByID($id);
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['agents']    = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers'] = $this->site->getCustomers();
            $this->data['users']     = $this->site->getStaff();
            $this->data['parent_operation_categories'] = $this->clinic_model->getParentOperationCategories();
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'clinic/edit_pathology', $this->data);
        }
    }
    public function delete_pathology($id = null)
    {
        $this->bpas->checkPermissions('delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if($this->clinic_model->deletePathology($id)){
            $this->bpas->send_json(['error' => 0, 'msg' => lang('pathology_deleted')]);
        }  
        $this->bpas->send_json(['error' => 1, 'msg' => lang('pathology_delete_failed')]);
        redirect($_SERVER['HTTP_REFERER']);
    }
    public function modal_view_pathology($id = null)
    {
        $this->bpas->checkPermissions('index');
        $operation = $this->clinic_model->getPathologyByID($id);
        $this->data['operation_category'] = $this->clinic_model->getOperationCategoryByID($operation->category);
        //$this->data['doctor']             = $this->site->getUser($operation->doctor);
        $this->data['created_by']         = $this->site->getUser($operation->created_by);
        $this->data['updated_by']         = $operation->updated_by ? $this->site->getUser($operation->updated_by) : null;
        $this->data['patience']           = $this->site->getCompanyByID($operation->patience_id);
        $this->data['biller']             = $operation->biller_id ? $this->site->getCompanyByID($operation->biller_id) : null;
        $this->data['operation']          = $operation;
        $this->data['page_title']         = $this->lang->line('view_pathology');
        $this->load->view($this->theme . 'clinic/modal_view_pathology', $this->data);
    }
    public function ipd($biller_id = null)
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

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('clinic')]];
        $meta = ['page_title' => lang('ipd_patient'), 'bc' => $bc];
        $this->page_construct('clinic/ipd_patient', $meta, $this->data);
    }
    public function getIPDPatients($biller_id = null)
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
        $detail_link        = anchor('admin/clinic/ipd_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));
        $duplicate_link     = anchor('admin/sales_order/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $email_link         = anchor('admin/sales_order/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/clinic/edit_ipd/$1', '<i class="fa fa-edit"></i>' . lang('edit_ipd'), 'class="sledit"');
        $pdf_link           = anchor('admin/sales_order/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link        = anchor('admin/sales_order/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
      
        $add_sale           = anchor('admin/sales/add/$1', '<i class="fa fa-money"></i>' . lang('add_sale'));


        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_opd') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('clinic/delete_opd/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_opd') . '</a>';

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                ';
            $action .= 
                (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['sales_order-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['sales_order-delete'] ? '<li class="delete">'.$delete_link.'</li>' : '')).
            '</ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('clinic_ipd_opd')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('clinic_ipd_opd')}.date, '%Y-%m-%d %T') as date, 
                {$this->db->dbprefix('companies')}.name as biller,
                reference_no,
                cus.code as code, 
                cus.name as patient, 
                cus.gender as gender, 
                cus.phone as phone, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user,
                weight,
                {$this->db->dbprefix('suspended_note')}.name,
                {$this->db->dbprefix('clinic_ipd_opd')}.attachment, 
                {$this->db->dbprefix('clinic_ipd_opd')}.created_by")
        ->from('clinic_ipd_opd')
        ->join('companies', 'companies.id=clinic_ipd_opd.biller_id','left')
        ->join('suspended_note', 'suspended_note.note_id=clinic_ipd_opd.bed','left')
        ->join('users', 'users.id=clinic_ipd_opd.doctor_id','left')
        ->join('companies cus', 'cus.id = clinic_ipd_opd.patient_id','left');
        $this->datatables->where('clinic_ipd_opd.patience_type','ipd');

        if ($biller_id) {
            $this->datatables->where_in('ipd_opd.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        // if ($this->input->get('attachment') == 'yes') {
        //     $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        // }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    public function add_ipd($customer_id = null,$consult_id=null)
    {
        $this->bpas->checkPermissions('add',null);
        $this->load->helper('security');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('doctor', lang('doctor'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('bed', lang('bed'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $diagnosis      = implode(",", $this->input->post('symptoms[]'));
            $data = [
                'date'          => $date,
                'reference_no'  => $this->input->post('reference'),
                'biller_id'     => $this->input->post('biller'),
                'patient_id'    => $this->input->post('customer', true),
                'doctor_id'     => $this->input->post('doctor'),
                'patience_type' => 'ipd',
                'weight'        => $this->input->post('weight'),
                'height'        => $this->input->post('height'),
                'temperature'   => $this->input->post('temperature'),
                'note'          => $this->input->post('note', true),
                'symptoms'      => $diagnosis ? $diagnosis:null,
                'symptoms_description'  => $this->input->post('symptoms_description', true),
                'bed'           => $this->input->post('bed', true),
                'created_by'    => $this->session->userdata('user_id'),

            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
           
        
        } elseif ($this->input->post('add_ipd')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->addOPD($data)) {
            $this->session->set_flashdata('message', lang('ipd_added'));
            admin_redirect('clinic/ipd');
        } else {
         
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['customer_id']     = $customer_id ? $customer_id : null;
            $this->data['billers']         = $this->site->getAllCompanies('biller');
            $this->data['warehouses']      = $this->site->getAllWarehouses();
            $this->data['agents']          = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']       = $this->site->getCustomers();
            $this->data['users']           = $this->site->getStaff();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();
            $this->data['tables']      = $this->table_model->getsuspend_note();
            $this->data['consult']     = $this->sales_order_model->getInvoiceByID($consult_id);
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('clinic'), 'page' => lang('clinic')], ['link' => '#', 'page' => lang('add_ipd')]];
            $meta                      = ['page_title' => lang('add_ipd'), 'bc' => $bc];
            $this->page_construct('clinic/add_ipd', $meta, $this->data);
        }
    }
    public function edit_ipd($id=false)
    {
        $this->bpas->checkPermissions('add',null);
        $this->load->helper('security');
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('doctor', lang('doctor'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');

        $this->data['data']    = $this->clinic_model->getOpdByID($id);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->form_validation->run() == true) {

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $diagnosis      = implode(",", $this->input->post('symptoms[]'));
            $data = [
                'date'         => $date,
                'reference_no' => $this->input->post('reference'),
                'biller_id'    => $this->input->post('biller'),
                'patient_id'   => $this->input->post('customer', true),
                'doctor_id'    => $this->input->post('doctor'),
                'patience_type' => 'ipd',
                'weight'        => $this->input->post('weight'),
                'height'        => $this->input->post('height'),
                'temperature'   => $this->input->post('temperature'),
                'note'          => $this->input->post('note', true),
                'symptoms'      => $diagnosis ? $diagnosis:null,
                'symptoms_description'  => $this->input->post('symptoms_description', true),
                'bed'           => $this->input->post('bed', true),
                'created_by'   => $this->session->userdata('user_id'),

            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
           
        
        } elseif ($this->input->post('edit_ipd')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->clinic_model->updateOPD($id,$data)) {
            $this->session->set_flashdata('message', lang('ipd_updated'));
            admin_redirect('clinic/ipd');
        } else {
         
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber']   = $this->site->getReference('ex');
            if ($this->Owner || $this->Admin) {
                $this->data['projects'] = $this->site->getAllProject();
            }else{
                $this->data['projects'] = $this->site->getAllProject($this->session->userdata('warehouse_id'));
            }

            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['datas']            = $this->clinic_model->getOpdByID($id);
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customers']        = $this->site->getCustomers();
            $this->data['users']            = $this->site->getStaff();
            $this->data['tables']           = $this->table_model->getsuspend_note();
            $this->data['nest_categories']  = $this->site->getNestedExpenseCategories();

            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('clinic'), 'page' => lang('clinic')], ['link' => '#', 'page' => lang('edit_ipd')]];
            $meta                      = ['page_title' => lang('edit_ipd'), 'bc' => $bc];
            $this->page_construct('clinic/edit_ipd', $meta, $this->data);
        }
    }
    public function opd_view($id)
    {
        $this->bpas->checkPermissions('customers', true);
        $data = $this->clinic_model->getIpdOpdByID($id);
        $this->data['data']     = $data;
        $customer_id            = $data->patient_id;
        $this->data['customer'] = $this->companies_model->getCustomerInfoByID($customer_id);
        if($this->data['customer']->zone_id){
            $this->data['zone']     = $this->site->getZoneByID($this->data['customer']->zone_id);
        }

        $this->data['sales']         = $this->reports_model->getSalesTotals($customer_id);
        $this->data['total_sales']   = $this->reports_model->getCustomerSales($customer_id);
        $this->data['total_quotes']  = $this->reports_model->getCustomerQuotes($customer_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($customer_id);
        $this->data['users']         = $this->reports_model->getStaff();
        $this->data['warehouses']    = $this->site->getAllWarehouses();
        $this->data['billers']       = $this->site->getAllCompanies('biller');
        $this->data['error']        = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer_id']  = $customer_id;

        $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
        $meta                  = ['page_title' => lang('customers_report'), 'bc' => $bc];
        $this->page_construct('clinic/opd_view_detail', $meta, $this->data);
    }
    public function ipd_view($id)
    {
        $this->bpas->checkPermissions('customers', true);
        $data = $this->clinic_model->getIpdOpdByID($id);
        $this->data['data']     = $data;
        $customer_id            = $data->patient_id;
        $this->data['customer'] = $this->companies_model->getCustomerInfoByID($customer_id);
        if($this->data['customer']->zone_id){
            $this->data['zone']     = $this->site->getZoneByID($this->data['customer']->zone_id);
        }

        $this->data['sales']         = $this->reports_model->getSalesTotals($customer_id);
        $this->data['total_sales']   = $this->reports_model->getCustomerSales($customer_id);
        $this->data['total_quotes']  = $this->reports_model->getCustomerQuotes($customer_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($customer_id);
        $this->data['users']         = $this->reports_model->getStaff();
        $this->data['warehouses']    = $this->site->getAllWarehouses();
        $this->data['billers']       = $this->site->getAllCompanies('biller');
        $this->data['error']        = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer_id']  = $customer_id;

        $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
        $meta                  = ['page_title' => lang('customers_report'), 'bc' => $bc];
        $this->page_construct('clinic/ipd_view_detail', $meta, $this->data);
    }
    public function getTreatments($biller_id = null)
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

        $view_deposit_link = anchor('admin/sales_order/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposits'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="view_deposit" data-target="#myModal"');
        $add_deposit_link = anchor('admin/sales_order/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-keyboard="false" class="add_deposit" data-target="#myModal"');
        $detail_link        = anchor('admin/sales_order/view/$1', '<i class="fa fa-file-text-o"></i>' . lang('sale_details'));
        $return_detail_link = anchor('admin/sales_order/return_view/$1', '<i class="fa fa-file-text-o"></i>' . lang('return_sale').' '. lang('details'));
        $detail_link_clinic = anchor('admin/clinic/dental_clinic/$1', '<i class="fa fa-file-text-o"></i>'.lang('invoice_dental_clinic'));
        $add_draw_link      = anchor('admin/clinic/add_draw/$1', '<i class="fa fa-pencil"></i>' . lang('add_draw'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $duplicate_link     = anchor('admin/sales_order/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link      = anchor('admin/sales_order/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link   = anchor('admin/sales_order/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link     = anchor('admin/sales_order/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link  = anchor('admin/sales_order/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link         = anchor('admin/sales_order/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link          = anchor('admin/sales_order/edit/$1', '<i class="fa fa-edit"></i>' . lang('edit_sale_order'), 'class="sledit"');
        $pdf_link           = anchor('admin/sales_order/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link        = anchor('admin/sales_order/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $add_delivery_link  = anchor('admin/deliveries/add/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'));
        $add_sale           = anchor('admin/sales/add/$1', '<i class="fa fa-money"></i>' . lang('add_sale'));
        $authorization      = anchor('admin/sales_order/getAuthorization/$1', '<i class="fa fa-check"></i>' . lang('approved'), '');
        $unapproved         = anchor('admin/sales_order/getunapproved/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('unapproved'), '');
        $rejected           = anchor('admin/sales_order/getrejected/$1', '<i class="fa fa-times"></i> ' . lang('rejected'), '');

        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale_order') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales_order/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li class="view_deposit">' . $view_deposit_link . '</li>
                <li class="add_deposit">' . $add_deposit_link . '</li>';
                if($this->Settings->module_clinic){
                    $action .= '  
                        <li>' . $detail_link_clinic . '</li>
                        <li>' . $add_draw_link . '</li>';
                }
            $action .= 
                (($this->Owner || $this->Admin) ? '<li class="approved">'.$authorization.'</li>' : ($this->GP['sales_order-approved'] ? '<li class="approved">'.$authorization.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="unapproved">'.$unapproved.'</li>' : ($this->GP['sales_order-rejected'] ? '<li class="unapproved">'.$unapproved.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="reject">'.$rejected.'</li>' : ($this->GP['sales_order-rejected'] ? '<li class="reject">'.$rejected.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="edit">'.$edit_link.'</li>' : ($this->GP['sales_order-edit'] ? '<li class="edit">'.$edit_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="delete">'.$delete_link.'</li>' : ($this->GP['sales_order-delete'] ? '<li class="delete">'.$delete_link.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add">'.$add_sale.'</li>' : ($this->GP['sales-add'] ? '<li class="add">'.$add_sale.'</li>' : '')).
                (($this->Owner || $this->Admin) ? '<li class="add_delivery">'.$add_delivery_link.'</li>' : ($this->GP['sales-add'] ? '<li class="add_delivery">'.$add_delivery_link.'</li>' : '')).
            '</ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';
        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('sales_order')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales_order')}.date, '%Y-%m-%d %T') as date, 
                patience_type,
                reference_no, 
                biller, 
                {$this->db->dbprefix('sales_order')}.customer, 
                sale_status, 
                grand_total, 
                IFNULL(payments.deposit,0) as deposit,
                (grand_total-(IFNULL(payments.deposit,0))) as balance,
                order_status,
                delivery_status, 
                {$this->db->dbprefix('sales_order')}.attachment, 
                return_id")
            ->join('projects', 'sales_order.project_id = projects.project_id', 'left')
            ->join('(select sum(amount) as deposit,sale_order_id from '.$this->db->dbprefix('payments').' where sale_order_id > 0 GROUP BY sale_order_id) as payments','payments.sale_order_id = sales_order.id','left')
            ->where('sales_order.store_sale !=', 1)
            ->from('sales_order');
        if ($biller_id) {
            $this->datatables->where_in('sales_order.biller_id', $biller_id);
        } 
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        // if ($this->input->get('attachment') == 'yes') {
        //     $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        // }
        // $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
    
}