<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Customers extends MY_Controller
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
        $this->lang->admin_load('customers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');    
        $this->load->admin_model('settings_model');
        $this->load->admin_model('reports_model'); 
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }
    function updatephone()
    {
        $this->db->select('id,group_name,phone,IF( LEFT( phone, 1) != 0, 1, 0) as cp');
        $this->db->where('group_name','customer');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if($row->cp !=0){
                    $this->db->update('companies', 
                        array( 'phone' => '0'.$row->phone), 
                        array( 'id' => $row->id));
                }
                echo '<br>';
            }
        }
        return FALSE;
    }
    public function add_consumer($company_id = null)
    {
        $this->bpas->checkPermissions(false, true);
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);
        $this->form_validation->set_rules('phone', lang("phone"), 'required|is_unique[consumers.phone]');
        $this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'phone'                => $this->input->post('phone'),
                'first_name'           => $this->input->post('first_name'),
                'last_name'            => $this->input->post('last_name'),
                'address'              => $this->input->post('address'),
                'description'          => $this->input->post('description'),
                'company_id'           => $company_id,
                'created_by'           => $this->session->userdata("user_id"),
                'commission'            => $company->find_consumer_comission,
                'gender'               => $this->input->post('gender'),
                'create_date'          => date("Y-m-d h:i:s"),
                'update_date'          => date("Y-m-d h:i:s"),
            ];
         
        } elseif ($this->input->post('add_consumer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addConsumer($data)) {
            $this->session->set_flashdata('message', lang('consumer_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'customers/add_consumer', $this->data);
        }
    }
    public function add_consumera()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('phone', lang("phone"), 'required');
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'phone'               => $this->input->post('phone'),
                'name'                => $this->input->post('name'),
                'compay_id'           => $this->input->post('compay_id'),
            ];
        } elseif ($this->input->post('add_consumer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang('consumer_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect('customers');
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['zones']           = $this->settings_model->getAllZones();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'customers/add_consumer', $this->data);
        }
    }
    public function add()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('code', lang('code'), 'is_unique[companies.code]');
        $this->form_validation->set_rules('contact_person', lang('contact_person'), 'is_unique[companies.contact_person]');
        //$this->form_validation->set_rules('find_consumer_comission', lang("find_consumer_comission"), 'numeric|greater_than[0]');
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

              //  'find_consumer_comission'   => $this->input->post('find_consumer_comission') ? $this->input->post('find_consumer_comission') : null,
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
                'find_consumer_comission'   => trim($this->input->post('find_consumer_comission')),
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
            $this->data['zones']           = $this->settings_model->getAllZones();
            $this->data['Lastrow']        = $this->companies_model->getLastCompanies('customer');
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['agents']          = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'customers/add', $this->data);
        }
    }

    public function add_address($company_id = null)
    {
        $this->bpas->checkPermissions('add', true);
        $company = $this->companies_model->getCompanyByID($company_id);
        $this->form_validation->set_rules('line1', lang('line1'), 'required');
        $this->form_validation->set_rules('phone', lang('phone'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'line1'       => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'city'        => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state'       => $this->input->post('state'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'kilometer'   => $this->input->post('kilometer'),
                'latitude'      => $this->input->post('latitude'),
                'longitude'     => $this->input->post('longitude'),
                'color_marker'  => $this->input->post('color_marker'),
                'company_id'    => $company->id,
            ];
        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $this->session->set_flashdata('message', lang('address_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            //$this->load->view($this->theme . 'customers/add_address', $this->data);
            $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('add_address')));
            $meta = array('page_title' => lang('add_address'), 'bc' => $bc);
            $this->page_construct('customers/add_address2', $meta, $this->data);

        }
    }
    public function edit_address($id = null){
        $this->bpas->checkPermissions('edit', true);

        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('phone', lang('phone'), 'required');

        if ($this->form_validation->run() == true) {
            $data = [
                'name'        => $this->input->post('name'),
                'line1'       => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'city'        => $this->input->post('city'),
                'postal_code' => $this->input->post('postal_code'),
                'state'       => $this->input->post('state'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'kilometer'   => $this->input->post('kilometer'),
                'latitude'      => $this->input->post('latitude'),
                'longitude'     => $this->input->post('longitude'),
                'color_marker'  => $this->input->post('color_marker'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ];
        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $this->session->set_flashdata('message', lang('address_updated'));
            admin_redirect('customers');
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
            $address = $this->companies_model->getAddressByID($id);
            $this->data['address']  = $address;
            $this->data['company']  = $this->companies_model->getCompanyByID($address->company_id);  
            // $this->load->view($this->theme . 'customers/edit_address', $this->data);
            $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('edit_address')));
            $meta = array('page_title' => lang('edit_address'), 'bc' => $bc);
            $this->page_construct('customers/edit_address2', $meta, $this->data);
        }
    }
    public function delete_address($id)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang('address_deleted'));
            admin_redirect('customers');
        }
    }
    function view_address($id = false, $company_id = false, $color_marker = false)
    {   
        $this->bpas->checkPermissions('index');
        if($this->input->post("customer")){
            $company_id = $this->input->post("customer");
        }
        if($this->input->post("color_marker")){
            $color_marker = $this->input->post("color_marker");
        }
        $all_addresses = $this->companies_model->getAddresses(false,$company_id);
        $addresses = $this->companies_model->getAddresses($id, $company_id, $color_marker);
        if($company_id && $company_id != "false"){
            $this->data['company'] = $this->companies_model->getCompanyByID($company_id);
        }else{
            $this->data['company'] = false;
        }   
        $this->data['customer_id'] = $company_id;
        $this->data['customers'] = $this->site->getAllCompanies('customer');
        $this->data['all_addresses'] = $all_addresses;
        $this->data['addresses'] = $addresses;
        $this->data['address_id'] = $id;
        $bc = array(array('link' => site_url('home'), 'page' => lang('home')), array('link' => site_url('customers'), 'page' => lang('customers')), array('link' => '#', 'page' => lang('view_address')));
        $meta = array('page_title' => lang('view_address'), 'bc' => $bc);
        $this->page_construct('customers/view_address', $meta, $this->data);
        
    }
    public function add_user($company_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', lang('email_address'), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

        if ($this->form_validation->run('companies/add_user') == true) {
            $active                  = $this->input->post('status');
            $notify                  = $this->input->post('notify');
            list($username, $domain) = explode('@', $this->input->post('email'));
            $email                   = strtolower($this->input->post('email'));
            $password                = $this->input->post('password');
            $additional_data         = [
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name'),
                'phone'      => $this->input->post('phone'),
                'gender'     => $this->input->post('gender'),
                'company_id' => $company->id,
                'company'    => $company->company,
                'group_id'   => 3,
            ];
            $this->load->library('ion_auth');
        } elseif ($this->input->post('add_user')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', lang('user_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'customers/add_user', $this->data);
        }
    }

    public function addresses($company_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['modal_js']  = $this->site->modal_js();
        $this->data['company']   = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'customers/addresses', $this->data);
    }

    public function customer_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteCustomer($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('customers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang('customers_deleted'));
                    }
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('city'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('state'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('postal_code'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('country'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('vat_no'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('gst_no'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('scf1'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('scf2'));
                    $this->excel->getActiveSheet()->SetCellValue('N1', lang('scf3'));
                    $this->excel->getActiveSheet()->SetCellValue('O1', lang('scf4'));
                    $this->excel->getActiveSheet()->SetCellValue('P1', lang('scf5'));
                    $this->excel->getActiveSheet()->SetCellValue('Q1', lang('scf6'));
                    $this->excel->getActiveSheet()->SetCellValue('R1', lang('deposit_amount'));
                    $this->excel->getActiveSheet()->SetCellValue('S1', lang('zone'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $z_name = '';
                        $customer = $this->site->getCompanyByID($id);
                        if(isset($customer->zone_id)){
                            $z_name = $this->site->getZoneByID($customer->zone_id)->zone_name;
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $customer->city);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $customer->state);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $customer->postal_code);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $customer->country);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $customer->vat_no);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $customer->gst_no);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $customer->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $customer->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('N' . $row, $customer->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('O' . $row, $customer->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('P' . $row, $customer->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('Q' . $row, $customer->cf6);
                        $this->excel->getActiveSheet()->SetCellValue('R' . $row, $customer->deposit_amount);
                        $this->excel->getActiveSheet()->SetCellValue('S' . $row, $z_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_customer_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('customer_x_deleted')]);
        }

        if ($this->companies_model->deleteCustomer($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('customer_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('customer_x_deleted_have_sales')]);
        }
    }
    public function delete_consumer($id)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->companies_model->deleteConsumer($id)) {
            $this->session->set_flashdata('message', lang('consumer_deleted'));
            admin_redirect('customers');
        }
    }

    public function edit($id = null)
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
                'agent'                     => $this->input->post('agent') ? $this->input->post('agent'): null,
                'date'                      => $this->input->post('date') ? $this->bpas->fld(trim($this->input->post('date'))) : null,
                'paid_by'                   => $this->input->post('paid_by') ? $this->input->post('paid_by'): null,
                'service_package'           => $this->input->post('service_package') ? $this->input->post('service_package'): null,
                'street_no'                 => $this->input->post('street_no') ? $this->input->post('street_no'): null,
                'commune'                   => $this->input->post('commune') ? $this->input->post('commune') : $this->input->post('commune'),
                'village'                   => $this->input->post('village') ? $this->input->post('village') : null,
                'email'                     => $this->input->post('email'),
                'service_fee'               => $this->input->post('service_fee') ? $this->input->post('service_fee') : 0,
                'find_consumer_comission'   => $this->input->post('find_consumer_comission') ? $this->input->post('find_consumer_comission') : null,
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
                'award_points'              => trim($this->input->post('award_points')),
                'credit_limit'              => ($this->input->post('credit_limit') != '' ? $this->input->post('credit_limit') : null),   
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['max_width']     = $this->Settings->iwidth;
                $config['max_height']    = $this->Settings->iheight;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $config['max_filename']  = 25;
                
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
            $this->data['zones']           = $this->settings_model->getAllZones();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['customer_package'] = $this->companies_model->getAllCustomerPackage();
            $this->load->view($this->theme . 'customers/edit', $this->data);
        }
    }
    public function edit_consumer($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
        $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
        $this->form_validation->set_rules('phone', lang('phone'), 'required');
        if ($this->form_validation->run() == true) {
            $data = [
                'last_name' => $this->input->post('last_name'),
                'first_name'=> $this->input->post('first_name'),
                'phone'     => $this->input->post('phone'),
                'gender'    => $this->input->post('gender'),
                'description'    => $this->input->post('description'),
                'address'    => $this->input->post('address'),
                'update_date'    => date('Y-m-d h:i:s'),
            ];
        } elseif ($this->input->post('edit_consumer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && $this->companies_model->updateConsumer($id, $data)) {
            $this->session->set_flashdata('message', lang('cosumer_updated'));
            admin_redirect('customers');
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['consumer']  = $this->companies_model->getConsumerByID($id);
            $this->data['modal_js']        = $this->site->modal_js();
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('customers'), 'page' => lang('customers')], ['link' => '#', 'page' => lang('edit_consumer')]];
            $meta = ['page_title' => lang('edit_consumer'), 'bc' => $bc];
            // $this->load->view($this->theme . 'customers/edit_consumer', $this->data);
            $this->page_construct('customers/edit_consumer', $meta, $this->data);
        }
    }
    // public function edit_consumerd($id = null)
    // {
    //     $this->bpas->checkPermissions('edit', true);

    //     $this->form_validation->set_rules('first_name', lang('first_name'), 'required');
    //     $this->form_validation->set_rules('last_name', lang('last_name'), 'required');
    //     $this->form_validation->set_rules('phone', lang('phone'), 'required');

    //     if ($this->form_validation->run() == true) {
    //         $data = [
    //             'last_name'       => $this->input->post('last_name'),
    //             'first_name'     => $this->input->post('first_name'),
    //             'phone'       => $this->input->post('phone'),
    //             'gender'  => $this->input->post('gender'),
    //         ];
    //         // var_dump($data);
    //         // exit();
    //     } elseif ($this->input->post('edit_consusmer')) {
    //         $this->session->set_flashdata('error', validation_errors());
    //         admin_redirect('customers');
    //     }
    //     if ($this->form_validation->run() == true && $this->companies_model->updateConsumer($id, $data)) {
    //         $this->session->set_flashdata('message', lang('consusmer_updated'));
    //         admin_redirect('customers');
    //     } else {
    //         var_dump($id);
    //         // exit();
    //         $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    //         $this->data['modal_js'] = $this->site->modal_js();
    //         $this->data['consumer']  = $this->companies_model->getConsumerByID($id);
    //         $this->load->view($this->theme . 'customers/edit_consumer', $this->data);
    //     }
    // }
  
    public function deposit_note($id = null)
    {
        $this->bpas->checkPermissions('deposits', true);
        $deposit                  = $this->companies_model->getDepositByID($id);
        $this->data['customer']   = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit']    = $deposit;
        $this->data['page_title'] = $this->lang->line('deposit_note');
        $this->load->view($this->theme . 'customers/deposit_note', $this->data);
    }

    public function deposits($company_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company']  = $this->companies_model->getCompanyByID($company_id);
        $this->load->view($this->theme . 'customers/deposits', $this->data);
    }
    public function get_deposits($company_id = null)
    {
        $this->bpas->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from('deposits')
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits') . '.company_id', $company_id)
            ->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . lang('deposit_note') . "' href='" . admin_url('customers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang('edit_deposit') . "' href='" . admin_url('customers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_deposit') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id')
        ->unset_column('id');
        echo $this->datatables->generate();
    }
    public function add_deposit($company_id = null)
    {
        $this->bpas->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $paid_by        = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = isset($paid_by->account_code) ? $paid_by->account_code : $this->accounting_setting->default_cash ;
            $data = [
                'date'       => $date,
                'amount'     => $this->input->post('amount'),
                'paid_by'    => $this->input->post('paid_by'),
                'note'       => $this->input->post('note'),
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
                'biller_id'     => $this->input->post('biller'),
                'project_id'    => $this->input->post('project'),
                'account_code'  => $paid_by_account,
            ];
            //=====accountig=====//
            if($this->Settings->module_account == 1){
                //$depositAcc = $this->site->getAccountSettingByBiller($this->input->post('biller'));
                

                $accTranDeposit[] = array(
                    'tran_type'     => 'CustomerDeposit',
                    'tran_date'     => $date,
                    'reference_no'  => $company->name,
                    'account_code'  => $this->accounting_setting->default_sale_deposit,
                    'amount'        => -($this->input->post('amount')),
                    'narrative'     => 'Customer Deposit '.$company->name,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $this->input->post('biller'),
                    'project_id'    => $this->input->post('project'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $company->id,
                );
                $accTranDeposit[] = array(
                    'tran_type'     => 'CustomerDeposit',
                    'tran_date'     => $date,
                    'reference_no'  => $company->name,
                    'account_code'  => $paid_by_account,
                    'amount'        => $this->input->post('amount'),
                    'narrative'     => 'Customer Deposit '.$company->name,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $this->input->post('biller'),
                    'project_id'    => $this->input->post('project'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $company->id,
                );
            }
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            //=====end accountig=====//
            $cdata = [
                'deposit_amount'     => ((!empty($company->deposit_amount) ? $company->deposit_amount : 0) + $this->input->post('amount')),
            ];
        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addDeposit($data, $cdata, $accTranDeposit)) {
            $this->session->set_flashdata('message', lang('deposit_added'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['company']  = $company;
            $this->data['projects'] = $this->site->getAllProjects();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->load->view($this->theme . 'customers/add_deposit', $this->data);
        }
    }
    public function edit_deposit($id = null)
    {
        $this->bpas->checkPermissions('deposits', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);

        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang('date'), 'required');
        }
        $this->form_validation->set_rules('amount', lang('amount'), 'required|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $paid_by        = $this->site->getCashAccountByCode($this->input->post('paid_by'));
            $paid_by_account = isset($paid_by->account_code) ? $paid_by->account_code : $this->accounting_setting->default_cash ;

            $data = [
                'date'       => $date,
                'amount'     => $this->input->post('amount'),
                'paid_by'    => $this->input->post('paid_by'),
                'note'       => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
                'biller_id'     => $this->input->post('biller'),
                'project_id'    => $this->input->post('project'),
                'account_code' => $paid_by_account,
            ];
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
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
            
            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $depositAcc = $this->site->getAccountSettingByBiller($this->input->post('biller'));
                $accTranDeposit[] = array(
                    'tran_type'     => 'CustomerDeposit',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $company->name,
                    'account_code'  => $this->accounting_setting->default_sale_deposit,
                    'amount'        => -($this->input->post('amount')),
                    'narrative'     => 'Customer Deposit '.$company->name,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $this->input->post('biller'),
                    'project_id'    => $this->input->post('project'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $company->id,
                    );
                $accTranDeposit[] = array(
                    'tran_type'     => 'CustomerDeposit',
                    'tran_no'       => $id,
                    'tran_date'     => $date,
                    'reference_no'  => $company->name,
                    'account_code'  => $paid_by_account,
                    'amount'        => $this->input->post('amount'),
                    'narrative'     => 'Customer Deposit '.$company->name,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $this->input->post('biller'),
                    'project_id'    => $this->input->post('project'),
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $company->id,
                );
            }
            //=====end accountig=====//
            $cdata = [
                'deposit_amount' => (($company->deposit_amount - $deposit->amount) + $this->input->post('amount')),
            ];
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCustomerDeposit($id, $data, $cdata, $accTranDeposit)) {
            $this->session->set_flashdata('message', lang('deposit_updated'));
            admin_redirect('customers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['projects'] = $this->site->getAllProjects();
            $this->data['company']  = $company;
            $this->data['deposit']  = $deposit;
            $this->load->view($this->theme . 'customers/edit_deposit', $this->data);
        }
    }
    public function delete_deposit($id)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->companies_model->deleteDeposit($id)) {
            $this->site->deleteAccTran('CustomerDeposit',$id);
            $this->bpas->send_json(['error' => 0, 'msg' => lang('deposit_deleted')]);
        }
    }
    public function get_award_points($id = null)
    {
        $this->bpas->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->bpas->send_json(['ca_points' => $row->award_points]);
    }

    public function get_customer_details($id = null)
    {
        $this->bpas->send_json($this->companies_model->getCompanyByID($id));
    }

    public function getCustomer($id = null)
    {
        // $this->bpas->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->bpas->send_json([['id' => $row->id, 'text' => ($row->company && $row->company != '-' ? $row->company : $row->name)]]);
    }

    public function getCustomers()
    {
        $this->bpas->checkPermissions('index');
        $this->load->library('datatables');

    
        $view_detail = anchor('admin/customers/actions/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_details'));


        $clear_award_points ="<a href='#' class='tip po' title='" . lang("clear_award_points") . "' data-content=\"<p>" . lang('r_u_sure') . "</p>
                    <a class='btn btn-danger' href='" . admin_url('customers/clear_AP/$1') . "'>" . lang('i_m_sure') . "</a> 
                    <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-eraser\"></i> ".lang("clear_award_points")."
                </a>";
        $list_deposits ="<a class='tip' title='" . lang('list_deposits') . "' href='" . admin_url('customers/deposits/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-money'> </i>".lang("list_deposits")."</a>";
        $add_deposit ="<a class='tip' title='" . lang('add_deposit') . "' href='" . admin_url('customers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-plus'></i> ".lang("add_deposit")."</a>";
        $list_addresses ="<a class='tip' title='" . lang('list_addresses') . "' href='" . admin_url('customers/addresses/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-location-arrow\"></i> ".lang("list_addresses")."</a>";
        $list_users ="<a class='tip' title='" . lang('list_users') . "' href='" . admin_url('customers/users/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-users'></i> ".lang("list_users")."</a> ";
        $add_user ="<a class='tip' title='" . lang('add_user') . "' href='" . admin_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class='fa fa-user-plus'></i> ".lang("add_user")."</a>";
        $list_consumers ="<a class='tip' title='" . lang('list_consumers') . "' href='" . admin_url('customers/consumers/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-book\"></i> ".lang("list_consumers")."</a>";
        $add_consumer ="<a class='tip' title='" . lang('add_consumer') . "' href='" . admin_url('customers/add_consumer/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-plus\"></i> ".lang("add_consumer")."</a>";
        $edit_customer ="<a class='tip' title='" . lang('edit_customer') . "' href='" . admin_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='false'><i class=\"fa fa-edit\"></i> ".lang("edit_customer")."</a> ";
        $delete_customer ="<a href='#' class='tip po' title='<b>" . lang('delete_customer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> ".lang("delete_customer")."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$view_detail.'</li>
                        <li>'.$clear_award_points.'</li>
                        <li>'.$list_deposits.'</li>
                        <li>'.$add_deposit.'</li>
                        <li>'.$list_addresses.'</li>
                        <li>'.$list_users.'</li>
                        <li>'.$add_user.'</li>
                        <li>'.$list_consumers.'</li>
                        <li>'.$add_consumer.'</li>
                        <li>'.$edit_customer.'</li>
                        <li>'.$delete_customer.'</li>
                    </ul>
                </div></div>';
        $this->datatables
            ->select("{$this->db->dbprefix('companies')}.id as id, company, name, code, phone, price_group_name, customer_group_name, vat_no, gst_no, deposit_amount, award_points, z.zone_name as zone_id")
            ->from('companies')
            ->join('zones z', 'z.id=companies.zone_id', 'left')
            ->where('group_name', 'customer');

        $this->datatables->add_column("Actions", $action, "id");
        $this->datatables->order_by("{$this->db->dbprefix('companies')}.id", "ASC");
        echo $this->datatables->generate();
    }

    function import_csv()
    {
        $this->bpas->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('excel_file', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (DEMO) {
                $this->session->set_flashdata('warning', lang("disabled_in_demo"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            if (isset($_FILES["excel_file"]))  {
                $this->load->library('excel');
                $path = $_FILES["excel_file"]["tmp_name"];
                $object = PHPExcel_IOFactory::load($path);
                
                foreach($object->getWorksheetIterator() as $worksheet){
                    $highestRow = $worksheet->getHighestRow();
                    $highestColumn = $worksheet->getHighestColumn();
                    for($row=2; $row<=$highestRow; $row++)
                    {
                     
                     $code = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                     $company = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                     $name = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                     $email = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                     $phone = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                     $customer_group_name = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                     $price_group_name = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                     $address = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                     $city = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                     $state = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                     $postal_code = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                     $country = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                     $vat_no = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                     $cf1s = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                     $cf2s = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                     $cf3s = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                     $cf4s = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                     $cf5s = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                     $cf6s = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                     
                    if(empty($code)){
                        $this->session->set_flashdata('error', lang("check_customer_code") . " (" . $code . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $row . ")");
                        admin_redirect("customers");
                    }
                    if(empty($phone)){
                        $this->session->set_flashdata('error', lang("check_customer_phone") . " (" . $phone . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $row . ")");
                        admin_redirect("customers");
                    }
                     
                     if($cust_group = $this->companies_model->getCustomerGroupByName(trim($customer_group_name))){
                        $cust_group_id = $cust_group->id;
                        $cust_group_name = $cust_group->name;
                     }
                     if($price_group = $this->companies_model->getPriceGroupByName(trim($price_group_name))){
                         $price_group_id = $price_group->id;
                         $price_group_name = $price_group->name;
                     }
                     
                        $data[] = array(
                          'code'  => $code,
                          'company'  => $company,
                          'name'   => $name,
                          'email'    => $email,
                          'phone'  => $phone,
                          'address'   => $address,
                          'customer_group_id'   => $cust_group_id,
                          'customer_group_name'   => $cust_group_name,
                          'price_group_id'   => $price_group_id,
                          'price_group_name'   => $price_group_name,
                          'city'   => $city,
                          'state'   => $state,
                          'postal_code'   => $postal_code,
                          'country'   => $country,
                          'vat_no'   => $vat_no,
                          'cf1'   => $cf1s,
                          'cf2'   => $cf2s,
                          'cf3'   => $cf3s,
                          'cf4'   => $cf4s,
                          'cf5'   => $cf5s,
                          'cf6'   => $cf6s,
                          'group_id'   => 3,
                          'group_name'   => 'customer',
                         );
                    }
                }
                
                $rw = 2;
                $checkCode = false;
                $checkPhone = false;
                foreach ($data as $csv_com) {

                    if(!$this->companies_model->getCompanyByCodeGroupName(trim($csv_com['code'],'customer'))) {
                        if ($csv_com['email'] && $this->companies_model->getCompanyByEmail($csv_com['email'])) {
                            $this->session->set_flashdata('error', lang("check_customer_email") . " (" . $email . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("customers");
                        }
                        if(isset($checkCode[trim($csv_com['code'])]) && $checkCode[trim($csv_com['code'])]){
                            $this->session->set_flashdata('error', lang("check_customer_code") . " (" . $csv_com['code'] . "). " . lang("customer_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("customers");
                        }
                        if(isset($checkPhone[trim($csv_com['phone'])]) && $checkPhone[trim($csv_com['phone'])]){
                            $this->session->set_flashdata('error', lang("check_customer_phone") . " (" . $csv_com['phone'] . "). " . lang("customer_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("customers");
                        }
                        if ($this->companies_model->getCompanyByPhone($csv_com['phone'],'customer')) {
                            $this->session->set_flashdata('error', lang("check_customer_phone") . " (" . $csv_com['phone'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("customers");
                        }
                            $checkCode[trim($csv_com['code'])] = true;
                            $checkPhone[trim($csv_com['phone'])] = true;
                            
                            $company_code[] = trim($csv_com['code']);
                            $company_company[] = trim($csv_com['company']);
                            $company_name[] = trim($csv_com['name']);
                            $company_email[] = trim($csv_com['email']);
                            $company_phone[] = trim($csv_com['phone']);
                            $company_address[] = trim($csv_com['address']);
                            $company_city[] = trim($csv_com['city']);
                            $company_state[] = trim($csv_com['state']);
                            $company_postal_code[] = trim($csv_com['postal_code']);
                            $company_country[] = trim($csv_com['country']);
                            $company_vat_no[] = trim($csv_com['vat_no']);
                            $cf1[] = trim($csv_com['cf1']);
                            $cf2[] = trim($csv_com['cf2']);
                            $cf3[] = trim($csv_com['cf3']);
                            $cf4[] = trim($csv_com['cf4']);
                            $cf5[] = trim($csv_com['cf5']);
                            $cf6[] = trim($csv_com['cf6']);
                            $group_id[] = 4;
                            $group_name[] = 'supplier';
                            $credit_limit[] = null;
                        
                    }else{
                        $this->session->set_flashdata('error', lang("check_customer_code") . " (" . $csv_com['code'] . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                        admin_redirect("customers");
                    }

                    $rw++;
                }
                $ikeys = array('code', 'company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'group_id','group_name', 'credit_limit');

                $companies = array();
                foreach (array_map(null, $company_code, $company_company, $company_name, $company_email, $company_phone,$company_address, $company_city, $company_state, $company_postal_code, $company_country, $company_vat_no, $cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $group_id,$group_name,$credit_limit) as $ikey => $value) {
                    $companies[] = array_combine($ikeys, $value);
                    
                }
            }

        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', lang("customers_added"));
                admin_redirect('customers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import', $this->data);
        }
    }
    public function import_csv_new()
    {
        $this->bpas->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                redirect($_SERVER['HTTP_REFERER']);
            }

            if (isset($_FILES['csv_file'])) {
                $this->load->library('upload');

                $config['upload_path']   = 'files/';
                $config['allowed_types'] = 'csv';
                $config['max_size']      = '2000';
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;

                $this->upload->initialize($config);
                
                if (!$this->upload->do_upload('csv_file')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('customers');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen('files/' . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5001, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles         = array_shift($arrResult);
              
  
                $rw             = 2;
                $updated        = '';
                $data           = [];
                $customer_group = $this->site->getCustomerGroupByID($this->Settings->customer_group);
                $price_group    = $this->site->getPriceGroupByID($this->Settings->price_group);
                foreach ($arrResult as $key => $value) {
                    $user_id = $value[14];
                    $username = $this->site->getUserName($user_id);
                    if ($username){
                        $username_id = $username->id;
                    }else{
                        $username_id = '';
                    }
                   $a =$value[15];
     
                   $dates = $this->bpas->fld($a);
                   $date = preg_replace('/[^0-9_ -]/s','',$dates);
              
                    $customer = [   
                        'name'                 => isset($value[0])  ? trim($value[0])   : '',
                        'contact_person'       => isset($value[1])  ? trim($value[1])   : '',
                        'telegram'             => isset($value[2])  ? trim($value[2])   : '',
                        'village'              => isset($value[3])  ? trim($value[3])   : '',
                        'commune'              => isset($value[4])  ? trim($value[4])   : '',
                        'stamp_number'         => isset($value[5])  ? trim($value[5])   : '',
                        'business_type'        => isset($value[6])  ? trim($value[6])   : '',
                        'street_no'            => isset($value[7])  ? trim($value[7])   : '',
                        'number_of_people'     => isset($value[8])  ? trim($value[8])   : '',
                        'service_fee'          => isset($value[9])  ? trim($value[9])   : '',
                        'stamp_type'           => isset($value[10]) ? trim($value[10]) : '',
                        'service_package'      => isset($value[11]) ? trim($value[11]) : '',
                        'prefer_cantact_by'    => isset($value[12]) ? trim($value[12]) : '',
                        'paid_by'              => isset($value[13]) ? trim($value[13]) : '',
                        'agent'                => $username_id ,
                        'date'                 => $date,
                        'group_id'             => 3,
                        'group_name'           => 'customer',
                        'customer_group_id'    => (!empty($customer_group)) ? $customer_group->id : null,
                        'customer_group_name'  => (!empty($customer_group)) ? $customer_group->name : null,
                        'price_group_id'       => (!empty($price_group)) ? $price_group->id : null,
                        'price_group_name'     => (!empty($price_group)) ? $price_group->name : null,
                        'credit_limit'         => null
                    ];
           
                    if (empty($customer['name']) || empty($customer['contact_person']) || empty($customer['village'])) {
                        $this->session->set_flashdata('error', lang('name') . ', ' . lang('contact_person') . ', ' . lang('village') . ' ' . lang('are_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                        admin_redirect('customers');
                    } else {
                        if ($this->Settings->indian_gst && empty($customer['state'])) {
                            $this->session->set_flashdata('error', lang('state') . ' ' . lang('is_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                            admin_redirect('customers');
                        }
                        $data[] = $customer;
                        // if ($customer_details = $this->companies_model->getCompanyByEmail($customer['email'])) {
                        //     if ($customer_details->group_id == 3) {
                        //         $updated .= '<p>' . lang('customer_updated') . ' (' . $customer['email'] . ')</p>';
                        //         $this->companies_model->updateCompany($customer_details->id, $customer);
                        //     }
                        // } else {
                        //     $data[] = $customer;
                        // }
                        $rw++;
                    }
                }
                // $this->bpas->print_arrays($data);
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }
      
        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', lang('customers_added') . $updated);
                admin_redirect('customers');
            }
        } else {
            if (isset($data) && empty($data)) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_customers'));
                }
                admin_redirect('customers');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/import_excel', $this->data);
        }
    }


    public function index($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('customers')]];
        $meta                 = ['page_title' => lang('customers'), 'bc' => $bc];
        $this->page_construct('customers/index', $meta, $this->data);
    }
     public function getCustomerCommissionReport($pdf = null, $xls = null,$preview=null,$excel_deatail=null)
    {
        $this->bpas->checkPermissions('sales', true);
        $product      = $this->input->get('product') ? $this->input->get('product') : null;
        $user         = $this->input->get('user') ? $this->input->get('user') : null;
        $customer     = $this->input->get('customer') ? $this->input->get('customer') : null;
        $phone        = $this->input->get('phone') ? $this->input->get('phone') : null;
        $address      = $this->input->get('address') ? $this->input->get('address') : null;
        $biller       = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse    = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date   = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date     = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial       = $this->input->get('serial') ? $this->input->get('serial') : null;
        $project       = $this->input->get('project') ? $this->input->get('project') : null;
        $sale_type       = $this->input->get('sale_type') ? $this->input->get('sale_type') : null;

        $sale_status  = $this->input->get('sale_status') ? $this->input->get('sale_status') : null;
        $zone         = $this->input->get('zone_id') ? $this->input->get('zone_id') : null;
        $payment_status = $this->input->get('payment_status') ? $this->input->get('payment_status') : null;
        if ($start_date) {
            $start_date = $this->bpas->fld($start_date);
            $end_date   = $this->bpas->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
  
        if($preview){
            $this->db->select('date,
                    CONCAT('.$this->db->dbprefix('users').'.first_name," ",
                    '.$this->db->dbprefix('users').'.last_name) as created_by,
                    reference_no, biller, customer, 
                    '.$this->db->dbprefix('sale_items') . ".product_code,
                    " . $this->db->dbprefix('sale_items') .".product_name,
                    " . $this->db->dbprefix('sale_items') .".expiry,
                    " . $this->db->dbprefix('sale_items') . ".quantity, 
                    " . $this->db->dbprefix('sale_items') . ".product_unit_code, 
                    " . $this->db->dbprefix('warehouses') . ".name as warehouse_name,
                    " . $this->db->dbprefix('sale_items') . ".subtotal,
                    " . $this->db->dbprefix('sale_items') . ".unit_price,
                    " . $this->db->dbprefix('sale_items') . ".item_discount,
                    " . $this->db->dbprefix('sale_items') . ".item_tax,
                    grand_total,
                    total,
                    order_discount,
                    paid,
                    payment_status
                ", false)
                ->from('sale_items')
                ->join('sales', 'sales.id = sale_items.sale_id', 'left')
                ->join('users', 'users.id=sales.created_by', 'left')
                ->join('warehouses', 'warehouses.id=sale_items.warehouse_id', 'left')
                ->join('companies c', 'c.id=sales.customer_id', 'left')
                ->order_by('sales.date desc');

            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($sale_status != null) {
                $this->db->where('sales.sale_status', $sale_status);
            }
            
            if ($project) {
                $this->db->where('sales.project_id', $project);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($phone) {
                $this->db->where('c.phone', $phone);
            }
            if ($address) {
                $this->db->where('c.address', $address);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($project) {
                $this->db->where('sales.project_id', $project);
            }
            if ($sale_type) {
                $this->db->where('sales.pos', $sale_type);
            }
            if ($zone) {
                $this->db->where_in('sales.zone_id', $arr_zone);
            }
            if ($payment_status) {
                if($payment_status == 'paid'){
                    $this->db->where('sales.payment_status', $payment_status);
                } else {
                    $this->db->where('sales.payment_status !=', 'paid');
                }
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                $data = $q->result();
            } else {
                $data = null;
            }
            
            $this->data['biller'] =  $this->site->getCompanyByID($biller);
            $this->data['rows'] = $data;
            $this->data['start_date'] = $start_date;
            $this->data['end_date'] = $end_date;
            if($zone){
                $this->data['zone'] = $this->site->getZoneByID($zone);    
            }
            
            $bc = array(array('link'   => base_url(), 'page' => lang('home')), array('link' => admin_url('sales'), 'page' => lang('sales')), array('link' => '#', 'page' => lang('sale_report')));
            $meta = array('page_title' => lang('sale_report'), 'bc' => $bc);
            $this->page_construct('reports/sale_preview', $meta, $this->data);
        } elseif ($pdf || $xls) {
            $this->db->select('date, project_name,
                    CONCAT('.$this->db->dbprefix('users').'.first_name," ",
                    '.$this->db->dbprefix('users').'.last_name) as created_by,
                    '. $this->db->dbprefix('suspended_note').'.name as suspend,
                    reference_no, biller, customer, c.phone, c.address, '. $this->db->dbprefix('sales').'.customer_qty, 

                    GROUP_CONCAT(
                        CONCAT(
                            '.$this->db->dbprefix('sale_items') . ".product_code,
                            '_', " . $this->db->dbprefix('sale_items') .".product_name,
                            ' (', " . $this->db->dbprefix('sale_items') . ".quantity, ')') SEPARATOR '\n') as iname, grand_total, total,order_discount, paid, payment_status", false)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('projects', 'projects.project_id=sales.project_id', 'left')
                ->join('suspended_note', 'suspended_note.note_id=sales.suspend_note', 'left')
                ->join('users', 'users.id=sales.created_by', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->join('companies c', 'c.id=sales.customer_id', 'left')
                ->group_by('sales.id')
                ->order_by('sales.date desc');

            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($project) {
                $this->db->where('sales.project_id', $project);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($phone) {
                $this->db->where('c.phone', $phone);
            }
            if ($address) {
                $this->db->where('c.address', $address);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
             
            if($start_date){
                $report_date = 'From '.$start_date.' To '.$end_date ;
            }else{
               $report_date = '' ;
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $styleArray = array(
                    'font'  => array(
                        'bold'  => true,
                        'color' => array('rgb' => '#000000'),
                        'size'  => 25,
                        'name'  => 'Verdana'
                    )
                );

                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('D1', $this->Settings->site_name);
                $this->excel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
                $this->excel->getActiveSheet()->SetCellValue('D2', lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('D3', $report_date);

                $this->excel->getActiveSheet()->SetCellValue('A4', lang('no'));
                $this->excel->getActiveSheet()->SetCellValue('B4', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('C4', lang('project'));
                $this->excel->getActiveSheet()->SetCellValue('D4', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('E4', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('F4', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('G4', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('H4', lang('address'));
                $this->excel->getActiveSheet()->SetCellValue('I4', lang('user'));
                $this->excel->getActiveSheet()->SetCellValue('J4', lang('customer') .' (QTY)');
                $this->excel->getActiveSheet()->SetCellValue('K4',($this->pos_settings->pos_type =='pos') ? lang('product_qty'): lang('suspend_no'));
                $this->excel->getActiveSheet()->SetCellValue('L4', lang('total'));
                $this->excel->getActiveSheet()->SetCellValue('M4', lang('discount'));
                $this->excel->getActiveSheet()->SetCellValue('N4', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('O4', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('P4', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('Q4', lang('payment_status'));

                $row = 5; $cus_qty = 0; $total = 0; $grand_total = 0; $discount = 0; $paid = 0; $balance = 0; $n = 1;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $n);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->project_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->address);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->customer_qty);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, ($this->pos_settings->pos_type =='pos') ? $data_row->iname : $data_row->suspend);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->order_discount);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->payment_status));
                    $cus_qty     += $data_row->customer_qty;
                    $total       += $data_row->total;
                    $grand_total += $data_row->grand_total;
                    $paid        += $data_row->paid;
                    $balance     += ($data_row->grand_total - $data_row->paid);
                    $row++;
                    $n++;
                }
                $this->excel->getActiveSheet()->getStyle('N' . $row . ':P' . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $cus_qty);
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('N' . $row, $grand_total);
                $this->excel->getActiveSheet()->SetCellValue('O' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
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
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'sales_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }elseif($excel_deatail){

            $this->db->select('date,
                    project_name,
                    CONCAT('.$this->db->dbprefix('users').'.first_name," ",
                    '.$this->db->dbprefix('users').'.last_name) as created_by,
                    reference_no, 
                    biller, customer,c.phone, c.address, 
                    '. $this->db->dbprefix('sales').'.customer_qty,
                    '.$this->db->dbprefix('sale_items') . ".product_code,
                    " . $this->db->dbprefix('sale_items') .".product_name,
                    " . $this->db->dbprefix('sale_items') .".expiry,
                    " . $this->db->dbprefix('sale_items') . ".quantity, 
                    " . $this->db->dbprefix('sale_items') . ".product_unit_code, 
                    " . $this->db->dbprefix('warehouses') . ".name as warehouse_name,
                    " . $this->db->dbprefix('sale_items') . ".subtotal,
                    " . $this->db->dbprefix('sale_items') . ".unit_price,
                    " . $this->db->dbprefix('sale_items') . ".item_discount,
                    " . $this->db->dbprefix('sale_items') . ".item_tax,
                    grand_total,
                    total,
                    order_discount,
                    paid,
                    payment_status
                ", false)
                ->from('sale_items')
                ->join('sales', 'sales.id = sale_items.sale_id', 'left')
                ->join('projects', 'projects.project_id=sales.project_id', 'left')
                ->join('users', 'users.id=sales.created_by', 'left')
                ->join('warehouses', 'warehouses.id=sale_items.warehouse_id', 'left')
                ->join('companies c', 'c.id=sales.customer_id', 'left')
                ->order_by('sales.date desc');


            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($project) {
                $this->db->where('sales.project_id', $project);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($phone) {
                $this->db->where('c.phone', $phone);
            }
            if ($address) {
                $this->db->where('c.address', $address);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }
             
            if($start_date){
                $report_date = 'From '.$start_date.' To '.$end_date ;
            }else{
               $report_date = '' ;
            }
            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $styleArray = array(
                    'font'  => array(
                        'bold'  => true,
                        'color' => array('rgb' => '#000000'),
                        'size'  => 25,
                        'name'  => 'Verdana'
                    )
                );

                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('D1', $this->Settings->site_name);
                $this->excel->getActiveSheet()->getStyle('D1')->applyFromArray($styleArray);
                $this->excel->getActiveSheet()->SetCellValue('D2', lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('D3', $report_date);

                $this->excel->getActiveSheet()->SetCellValue('A4', lang('no'));
                $this->excel->getActiveSheet()->SetCellValue('B4', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('C4', lang('project'));
                $this->excel->getActiveSheet()->SetCellValue('D4', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('E4', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('F4', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('G4', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('H4', lang('address'));
                $this->excel->getActiveSheet()->SetCellValue('I4', lang('user'));
                $this->excel->getActiveSheet()->SetCellValue('J4', lang('product'));
                $this->excel->getActiveSheet()->SetCellValue('K4',($this->pos_settings->pos_type =='pos') ? lang('product_qty'): lang('suspend_no'));
                $this->excel->getActiveSheet()->SetCellValue('L4', lang('unit'));
                $this->excel->getActiveSheet()->SetCellValue('M4', lang('price'));
                $this->excel->getActiveSheet()->SetCellValue('N4', lang('subtotal'));
                $this->excel->getActiveSheet()->SetCellValue('O4', lang('discount'));
                $this->excel->getActiveSheet()->SetCellValue('P4', lang('vat'));
                $this->excel->getActiveSheet()->SetCellValue('Q4', lang('subtotal'));

                $row = 5; $sub_qty = 0; $total = 0; $grand_total = 0; $discount = 0; $paid = 0; $balance = 0; $n = 1;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $n);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->project_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->address);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->created_by);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->product_name);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $data_row->product_unit_code);
                    $this->excel->getActiveSheet()->SetCellValue('M' . $row, $data_row->unit_price);
                    $this->excel->getActiveSheet()->SetCellValue('N' . $row, $data_row->quantity * $data_row->unit_price);
                    $this->excel->getActiveSheet()->SetCellValue('O' . $row, $data_row->item_discount);
                    $this->excel->getActiveSheet()->SetCellValue('P' . $row, $data_row->item_tax);
                    $this->excel->getActiveSheet()->SetCellValue('Q' . $row, lang($data_row->subtotal));
                    $sub_qty     += $data_row->quantity;
                    $total       += $data_row->total;
                    $grand_total += $data_row->grand_total;
                    $paid        += $data_row->paid;
                    $balance     += ($data_row->grand_total - $data_row->paid);
                    $row++;
                    $n++;
                }
                $this->excel->getActiveSheet()->getStyle('N' . $row . ':P' . $row)->getBorders()
                    ->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $sub_qty);
                /*$this->excel->getActiveSheet()->SetCellValue('L' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('N' . $row, $grand_total);
                $this->excel->getActiveSheet()->SetCellValue('O' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('P' . $row, $balance);*/

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
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
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'sales_report_detail';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $si = "( SELECT sale_id, product_id, serial_no,
            GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            $sj = "( SELECT sale_id, product_id, serial_no,
             SUM({$this->db->dbprefix('sale_items')}.original_price * {$this->db->dbprefix('sale_items')}.unit_quantity) as item_real_price from {$this->db->dbprefix('sale_items')} ";
            if ($product || $serial) {
                $si .= ' WHERE ';
                $sj .= ' WHERE ';
            }
            if ($product) {
                $si .= " {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
                $sj .= " {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            if ($product && $serial) {
                $si .= ' AND ';
                $sj .= ' AND ';
            }
            if ($serial) {
                $si .= " {$this->db->dbprefix('sale_items')}.serial_no LIKe '%{$serial}%' ";
                $sj .= " {$this->db->dbprefix('sale_items')}.serial_no LIKe '%{$serial}%' ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $sj .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSJ";
            $this->load->library('datatables');
            $this->datatables
                ->select("id,DATE_FORMAT(create_date, '%Y-%m-%d %T') as create_date,
                    DATE_FORMAT(update_date, '%Y-%m-%d %T') as update_date,
                    first_name,
                    last_name,
                    phone,
                    commission,
                    address,
                    description")
                ->from('consumers');
                // ->join($si, 'FSI.sale_id=sales.id', 'left')
                // ->join($sj, 'FSJ.sale_id=sales.id', 'left')
                // ->join('projects', 'projects.project_id=sales.project_id', 'left')
                // ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                // ->join('companies c', 'c.id=sales.customer_id', 'left')
            // ->where('sales.id');
                $this->datatables->where('company_id', $customer);
            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            // if ($customer) {
            //     $this->datatables->where('sales.customer_id', $customer);
            // }
            if ($phone) {
                $this->datatables->where('c.phone', $phone);
            }
            if ($address) {
                $this->datatables->where('c.address', $address);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            if ($project) {
                $this->db->where('sales.project_id', $project);
            }
            if ($sale_type) {
                $this->datatables->where('sales.pos', $sale_type);
            }
        // var_dump($action);
                  $edit_link            = anchor('admin/customers/edit_consumer/$1', '<i class="fa fa-edit"></i> ' . lang('edit_consumer'), 'class="sledit"');
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_consumer') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('customers/delete_consumer/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_consumer') . '</a>';
         $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
            $this->datatables->add_column('Actions', $action,"id");
            
            echo $this->datatables->generate();
        }
    }
    public function suggestions($term = null, $limit = null, $a = null)
    {
        // $this->bpas->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        if (strlen($term) < 1) {
            return false;
        }
        $limit  = $this->input->get('limit', true);
        $result = $this->companies_model->getCustomerSuggestions($term, $limit);
        if ($a) {
            $this->bpas->send_json($result);
        }
        $rows['results'] = $result;
        $this->bpas->send_json($rows);
    }
    public function suggestions_biller($term = null, $limit = null, $a = null)
    {
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        if (strlen($term) < 1) {
            return false;
        }
        $limit  = $this->input->get('limit', true);
        $result = $this->companies_model->getBillerSuggestions($term, $limit);
        if ($a) {
            $this->bpas->send_json($result);
        }
        $rows['results'] = $result;
        $this->bpas->send_json($rows);
    }
    public function consumers($company_id = null)
    {
        $this->bpas->checkPermissions(false, true);
        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company']  = $this->companies_model->getCompanyByID($company_id);
        $this->data['consumers'] = $this->companies_model->getCompanyConsumers($company_id);
        $this->load->view($this->theme . 'customers/consumers', $this->data);
    }

    public function users($company_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }

        $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['company']  = $this->companies_model->getCompanyByID($company_id);
        $this->data['users']    = $this->companies_model->getCompanyUsers($company_id);
        $this->load->view($this->theme . 'customers/users', $this->data);
    }
    public function choose($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyByID($id);
        if($this->data['customer']->zone_id){
            $this->data['zone']     = $this->site->getZoneByID($this->data['customer']->zone_id);
        }
        $this->load->view($this->theme . 'customers/choose', $this->data);
    }
    public function view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCustomerInfoByID($id);
        if($this->data['customer']->zone_id){
            $this->data['zone']     = $this->site->getZoneByID($this->data['customer']->zone_id);
        }
        $this->load->view($this->theme . 'customers/view', $this->data);
    }

    public function clear_AP($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'admin/welcome');
        }
        if ($this->companies_model->clear_award_points($id)) {
            $this->session->set_flashdata('message', lang('award_points_clear_successfully'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function membership()
    {   
        $this->bpas->checkPermissions('index', true, 'memberships');
        $this->data['modal_js'] = $this->site->modal_js();
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'customers', 'page' => lang('customers')), array('link' => '#', 'page' => lang('membership')));
        $meta = array('page_title' => lang('membership'), 'bc' => $bc);
        $this->page_construct('customers/membership', $meta, $this->data);
    }
   public function getMembership()
    {   
        $this->bpas->checkPermissions('index', true, 'memberships');
        $this->load->library('datatables');

        $delete_link = "<a href='#' class='po' title='" . lang("delete_membership") . "' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('customers/delete_membership/$1') . "'>"
            . lang('i_m_sure') . "</a><button class='btn'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_membership') . "</a>";
        
        $action_link = '<div class="btn-group text-left"><button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'.lang("actions").'<span class="caret"></span></button>
                            <ul class="dropdown-menu pull-right" role="menu">
                                <li><a href="'.admin_url('customers/edit_membership/$1').'" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#myModal"><i class="fa fa fa-edit"></i>'.lang('edit_membership').'</a></li>
                                <li>'.$delete_link.'</li>
                            </ul>
                        </div>';
        $this->datatables
            ->select("
                        customer_package.id as id, 
                        customer_package.name, 
                        CONCAT(period,' ',period_type) as package,
                        customer_package.price,
                        customer_package.class, 
                        customer_package.description

                        
                    ")
            ->from("customer_package")
            ->add_column("Actions", $action_link, "id");
        echo $this->datatables->generate();
    }
    public function add_membership()
    {
        $this->bpas->checkPermissions('add', true, 'memberships');
        $post = $this->input->post();
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true){
            $data = array(
                'name'          => $post['name'],
                'period_type'   => $post['period_type'],
                'period'        => $post['membership_period'],
                'price'         => $this->bpas->formatDecimal($post['price']),
                'class'         => $post['class'],
                'description'   => $this->bpas->clear_tags($post['description']),
            );
        } elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }   
        if ($this->form_validation->run() == true && $id = $this->companies_model->addCustomerPackage($data)) {
            $this->session->set_flashdata('message', $this->lang->line("membership_added"));
            admin_redirect("customers/membership");
        }else{
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/add_membership', $this->data);   
        }   
    }
    public function edit_membership($id = null)
    {       
        $this->bpas->checkPermissions('edit', true, 'memberships');
        $post = $this->input->post();       
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if($this->form_validation->run() == true){                      
            $data = array(
                'name'          => $post['name'],
                'period'        => $post['membership_period'],
                'period_type'   => $post['period_type'],
                'price'         => $this->bpas->formatDecimal($post['price']),
                'class'         => $post['class'],
                'description'   => $this->bpas->clear_tags($post['description']),
            );
        }elseif ($post) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }
        if($this->form_validation->run() == true && $id = $this->companies_model->updateCustomerPackage($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("membership_updated"));
            admin_redirect("customers/membership");
        }else{
            $skill_info = $this->companies_model->getCustomerPackageByID($id);    
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');          
            $this->data['id']=$id;
            $this->data['row'] = $skill_info;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'customers/edit_membership', $this->data);
        }           
    }
    public function delete_membership($id = null)
    {   
        // $this->bpas->checkPermissions('membership-delete');
        $this->bpas->checkPermissions('delete', true, 'memberships');
        if (isset($id) || $id != null){         
            if($this->companies_model->deleteCustomerPackage($id)){
                $this->session->set_flashdata('message', lang("membership_deleted"));
                admin_redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
        // delete  or export excel
    public function customer_package_actions()
    {
        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteCustomerpackage($id);
                    }
                    $this->session->set_flashdata('message', lang('customer_package_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer_package'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('description'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('membership_period'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('period_type'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('price'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc              = $this->settings_model->getCustomerPackageByID($id);
                       
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->remove_tag($sc->description));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->period);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sc->period_type);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sc->price);
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'customer_package_' . date('Y_m_d_H_i_s');
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
    public function actions($customer_id)
    {
        $this->bpas->checkPermissions('customers', true);
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
        $this->page_construct('customers/view_detail', $meta, $this->data);
    }
}
