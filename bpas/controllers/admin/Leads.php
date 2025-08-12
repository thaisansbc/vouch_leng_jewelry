<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Leads extends MY_Controller
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
        $this->load->admin_model('leads_model');
        
    }

    public function add()
    {
        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('email', lang('email_address'), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'name'           => $this->input->post('name'),
                'email'               => $this->input->post('email'),
                'group_id'            => '',
                'group_name'          => 'lead',
                'customer_group_id'   => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id'      => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'    => $this->input->post('price_group') ? $pg->name : null,
                'company'             => $this->input->post('company'),
                'address'             => $this->input->post('address'),
                'vat_no'              => $this->input->post('vat_no'),
                'city'                => $this->input->post('city'),
                'state'               => $this->input->post('state'),
                'postal_code'         => $this->input->post('postal_code'),
                'country'             => $this->input->post('country'),
                'phone'               => $this->input->post('phone'),
                'gender'              => $this->input->post('gender'),
                'age'                 => $this->input->post('age'),
                'cf1'                 => $this->input->post('cf1'),
                'cf2'                 => $this->input->post('cf2'),
                'cf3'                 => $this->input->post('cf3'),
                'cf4'                 => $this->input->post('cf4'),
                'cf5'                 => $this->input->post('cf5'),
                'cf6'                 => $this->input->post('cf6'),
                'gst_no'              => $this->input->post('gst_no'),
                'source'              => $this->input->post('source'),
                'lead_group'          => $this->input->post('lead_group'),
                'projects'          => $this->input->post('project'),
                'products' 			  =>implode(",", $this->input->post('products[]')),
            ];
        } elseif ($this->input->post('add_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('customers');
        }

        if ($this->form_validation->run() == true && $cid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', lang('lead_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?customer=' . $cid);
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['lead_group']      = $this->leads_model->getAllGroup();
            $this->data['com']        		= $this->companies_model->getCompaniesbyID();
           //  var_dump( $this->data['com']);
           // exit();
            // $this->data['products'] 		= $this->site->getAllProducts();
             $this->data['projects']       = $this->site->getAllProject();
            $this->data['products']         = $this->site->getProducts();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->load->view($this->theme . 'leads/add', $this->data);
        }
    }
    public function edit($id = null){
        $this->bpas->checkPermissions(false, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run('companies/add') == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'name'           => $this->input->post('name'),
                'email'               => $this->input->post('email'),
                'group_id'            => '3',
                'customer_group_id'   => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id'      => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'    => $this->input->post('price_group') ? $pg->name : null,
                'company'             => $this->input->post('company'),
                'address'             => $this->input->post('address'),
                'vat_no'              => $this->input->post('vat_no'),
                'city'                => $this->input->post('city'),
                'state'               => $this->input->post('state'),
                'postal_code'         => $this->input->post('postal_code'),
                'country'             => $this->input->post('country'),
                'phone'               => $this->input->post('phone'),
                'gender'              => $this->input->post('gender'),
                'age'                 => $this->input->post('age'),
                'cf1'                 => $this->input->post('cf1'),
                'cf2'                 => $this->input->post('cf2'),
                'cf3'                 => $this->input->post('cf3'),
                'cf4'                 => $this->input->post('cf4'),
                'cf5'                 => $this->input->post('cf5'),
                'cf6'                 => $this->input->post('cf6'),
                'award_points'        => $this->input->post('award_points'),
                'gst_no'              => $this->input->post('gst_no'),
                'source'              => $this->input->post('source'),
                'lead_group'          => $this->input->post('lead_group'),
                'projects'          => $this->input->post('project'),
                'products' 			  =>implode(",", $this->input->post('products[]')),
            ];
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
            $this->data['projects']       = $this->site->getAllProject();
            $this->data['products']         = $this->site->getProducts();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['lead_group']      = $this->leads_model->getAllGroup();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->data['agents']           = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->load->view($this->theme . 'leads/edit', $this->data);
        }
    }
    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->input->get('id') == 1) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('lead_x_deleted')]);
        }

        if ($this->leads_model->deleteLead($id)) {
            // $this->session->set_flashdata('message', lang('lead_deleted'));
            // admin_redirect('leads/pipeline');

           $this->bpas->send_json(['error' => 0, 'msg' => lang('lead_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('lead_x_deleted_have_sales')]);
        }
    }
    public function add_address($company_id = null)
    {
        $this->bpas->checkPermissions('add', true);
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('noted', lang('noted'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'noted'       => $this->input->post('noted'),
                'created_by'  => $this->session->userdata('user_id'),
                'company_id'  => $company->id,
                'next_followup' => $this->input->post('next_follow_up')? $this->bpas->fld(trim($this->input->post('next_follow_up'))): ''
            ];
        } elseif ($this->input->post('add_address')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leads');
        }

        if ($this->form_validation->run() == true && $this->companies_model->addAddress($data)) {
            $this->session->set_flashdata('message', lang('noted_added'));
            admin_redirect('leads');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'leads/add_address', $this->data);
        }
    }
    public function addresses($company_id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['modal_js']  = $this->site->modal_js();
        $this->data['company']   = $this->companies_model->getCompanyByID($company_id);
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($company_id);
        $this->load->view($this->theme . 'leads/addresses', $this->data);
    }
    public function lead_actions()
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
                        if (!$this->leads_model->deleteLead($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('Leads_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', lang('leads_deleted'));
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

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
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
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'leads_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_lead_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    
    public function delete_address($id)
    {
        $this->bpas->checkPermissions('delete', true);

        if ($this->companies_model->deleteAddress($id)) {
            $this->session->set_flashdata('message', lang('noted_deleted'));
            admin_redirect('leads');
        }
    }

    
    public function edit_address($id = null)
    {
        $this->bpas->checkPermissions('edit', true);

        $this->form_validation->set_rules('noted', lang('noted'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'noted'       => $this->input->post('noted'),
                'created_by'  => $this->session->userdata('user_id'),
                'updated_at'  => date('Y-m-d H:i:s'),
                'next_followup' => $this->input->post('next_follow_up')?$this->bpas->fld(trim($this->input->post('next_follow_up'))): ''
            ];
        } elseif ($this->input->post('edit_address')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leads');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateAddress($id, $data)) {
            $this->session->set_flashdata('message', lang('noted_updated'));
            admin_redirect('leads');
        } else {
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['address']  = $this->companies_model->getAddressByID($id);
            $this->load->view($this->theme . 'leads/edit_address', $this->data);
        }
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

    public function getLeads()
    {
        $this->bpas->checkPermissions('index');

        $detail_link = anchor('admin/leads/view_lead/$1', '<i class="fa fa-file-text-o"></i> ' . lang('profile'));
        $list_addresses   = anchor('admin/leads/addresses/$1', '<i class="fa fa-file-text-o"></i> ' . lang('list_noted'), 'data-toggle="modal" data-target="#myModal"');
        
        $list_users   = anchor('admin/leads/users/$1', '<i class="fa fa-file-text-o"></i> ' . lang('list_users'), 'data-toggle="modal" data-target="#myModal"');
        
        $add_user   = anchor('admin/leads/add_user/$1', '<i class="fa fa-file-text-o"></i> ' . lang('add_user'), 'data-toggle="modal" data-target="#myModal"');
        $edit_lead  = anchor('admin/leads/edit/$1', '<i class="fa fa-file-text-o"></i> ' . lang('edit_lead'), 'data-toggle="modal" data-target="#myModal"');
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('delete_lead') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('leads/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_lead') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $list_addresses . '</li>
            <li>' . $edit_lead . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');
        $this->datatables
            //->select('id, company, name, email, phone, source, customer_group_name')

            ->select($this->db->dbprefix('companies') . '.id as id, 
            '.$this->db->dbprefix('companies') . '.company as company,
            '.$this->db->dbprefix('companies') . '.name as name,
              '.$this->db->dbprefix('projects') . '.project_name as project_name,
              '.$this->db->dbprefix('companies') . '.products as products,
              '.$this->db->dbprefix('companies') . '.email as email,
              '.$this->db->dbprefix('companies') . '.phone as phone,
              '.$this->db->dbprefix('companies') . '.gender as gender,
              '.$this->db->dbprefix('companies') . '.age as age,
              '.$this->db->dbprefix('custom_field') . '.name as source,
              '.$this->db->dbprefix('containers') . '.container_name as container_name,
                ', false)
                ->from('companies')
                ->join('containers', 'containers.container_id=companies.lead_group', 'left')
                ->join('projects', 'projects.project_id=companies.projects', 'left')
                ->join('custom_field', 'custom_field.id=companies.source', 'left')
                ->where('companies.group_name', 'lead')
                ->group_by('companies.id');
            //->add_column('Actions', "<div class=\"text-center\"> <a class=\"tip\" title='" . lang('list_users') . "' href='" . admin_url('customers/users/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-users\"></i></a> <a class=\"tip\" title='" . lang('add_user') . "' href='" . admin_url('customers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-user-plus\"></i></a> <a class=\"tip\" title='" . lang('edit_customer') . "' href='" . admin_url('customers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_customer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('leads/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function import_csv()
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
                    $customer = [
                        'company'             => isset($value[0]) ? trim($value[0]) : '',
                        'name'                => isset($value[1]) ? trim($value[1]) : '',
                        'email'               => isset($value[2]) ? trim($value[2]) : '',
                        'phone'               => isset($value[3]) ? trim($value[3]) : '',
                        'address'             => isset($value[4]) ? trim($value[4]) : '',
                        'city'                => isset($value[5]) ? trim($value[5]) : '',
                        'state'               => isset($value[6]) ? trim($value[6]) : '',
                        'postal_code'         => isset($value[7]) ? trim($value[7]) : '',
                        'country'             => isset($value[8]) ? trim($value[8]) : '',
                        'vat_no'              => isset($value[9]) ? trim($value[9]) : '',
                        'gst_no'              => isset($value[10]) ? trim($value[10]) : '',
                        'cf1'                 => isset($value[11]) ? trim($value[11]) : '',
                        'cf2'                 => isset($value[12]) ? trim($value[12]) : '',
                        'cf3'                 => isset($value[13]) ? trim($value[13]) : '',
                        'cf4'                 => isset($value[14]) ? trim($value[14]) : '',
                        'cf5'                 => isset($value[15]) ? trim($value[15]) : '',
                        'cf6'                 => isset($value[16]) ? trim($value[16]) : '',
                        'group_id'            => 3,
                        'group_name'          => 'lead',
                        'customer_group_id'   => (!empty($customer_group)) ? $customer_group->id : null,
                        'customer_group_name' => (!empty($customer_group)) ? $customer_group->name : null,
                        'price_group_id'      => (!empty($price_group)) ? $price_group->id : null,
                        'price_group_name'    => (!empty($price_group)) ? $price_group->name : null,
                    ];
                    if (empty($customer['company']) || empty($customer['name']) || empty($customer['email'])) {
                        $this->session->set_flashdata('error', lang('company') . ', ' . lang('name') . ', ' . lang('email') . ' ' . lang('are_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                        admin_redirect('leads');
                    } else {
                        if ($this->Settings->indian_gst && empty($customer['state'])) {
                            $this->session->set_flashdata('error', lang('state') . ' ' . lang('is_required') . ' (' . lang('line_no') . ' ' . $rw . ')');
                            admin_redirect('leads');
                        }
                        if ($customer_details = $this->companies_model->getCompanyByEmail($customer['email'])) {
                            if ($customer_details->group_id == 3) {
                                $updated .= '<p>' . lang('customer_updated') . ' (' . $customer['email'] . ')</p>';
                                $this->companies_model->updateCompany($customer_details->id, $customer);
                            }
                        } else {
                            $data[] = $customer;
                        }
                        $rw++;
                    }
                }

                // $this->bpas->print_arrays($data, $updated);
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leads');
        }

        if ($this->form_validation->run() == true && !empty($data)) {
            if ($this->companies_model->addCompanies($data)) {
                $this->session->set_flashdata('message', lang('customers_added') . $updated);
                admin_redirect('leads');
            }
        } else {
            if (isset($data) && empty($data)) {
                if ($updated) {
                    $this->session->set_flashdata('message', $updated);
                } else {
                    $this->session->set_flashdata('warning', lang('data_x_customers'));
                }
                admin_redirect('leads');
            }

            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'leads/import', $this->data);
        }
    }

    public function index($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('leads')]];
        $meta                 = ['page_title' => lang('leads'), 'bc' => $bc];
        $this->page_construct('leads/index', $meta, $this->data);
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

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $this->companies_model->getCompanyID($id);
        $this->data['products'] = $this->companies_model->getProductMultiByID($this->data['customer']->products);
        // var_dump($this->data['products']);
        // exit();
        // var_dump($this->data['customer']);
        // exit();
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($id);
        $this->load->view($this->theme . 'leads/view', $this->data);
    }
    public function view_lead($id = null)
    {
        $this->bpas->checkPermissions('customers', true);
   
        $this->data['customer'] = $this->companies_model->getCompanyByID($id);
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['addresses'] = $this->companies_model->getCompanyAddresses($id);

        $this->data['user_id'] = $id;
        $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('leads_report')]];
        $meta                  = ['page_title' => lang('leads_report'), 'bc' => $bc];
        $this->page_construct('leads/lead_report', $meta, $this->data);
    }
    public function convert_lead($id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('email') != $company_details->email) {
            $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        }

        if ($this->form_validation->run('companies/add') == true) {
            $cg   = $this->site->getCustomerGroupByID($this->input->post('customer_group'));
            $pg   = $this->site->getPriceGroupByID($this->input->post('price_group'));
            $data = [
                'name'           => $this->input->post('name'),
                'email'               => $this->input->post('email'),
                'group_id'            => '3',
                'group_name'          => 'customer',
                'customer_group_id'   => $this->input->post('customer_group'),
                'customer_group_name' => $cg->name,
                'price_group_id'      => $this->input->post('price_group') ? $this->input->post('price_group') : null,
                'price_group_name'    => $this->input->post('price_group') ? $pg->name : null,
                'company'             => $this->input->post('company'),
                'address'             => $this->input->post('address'),
                'vat_no'              => $this->input->post('vat_no'),
                'city'                => $this->input->post('city'),
                'state'               => $this->input->post('state'),
                'postal_code'         => $this->input->post('postal_code'),
                'country'             => $this->input->post('country'),
                'phone'               => $this->input->post('phone'),
                'cf1'                 => $this->input->post('cf1'),
                'cf2'                 => $this->input->post('cf2'),
                'cf3'                 => $this->input->post('cf3'),
                'cf4'                 => $this->input->post('cf4'),
                'cf5'                 => $this->input->post('cf5'),
                'cf6'                 => $this->input->post('cf6'),
                'award_points'        => $this->input->post('award_points'),
                'gst_no'              => $this->input->post('gst_no'),
                'lead_convert'        => 1,
            ];
        } elseif ($this->input->post('edit_customer')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', lang('lead_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['customer']        = $company_details;
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']        = $this->site->modal_js();
            $this->data['customer_groups'] = $this->companies_model->getAllCustomerGroups();
            $this->data['price_groups']    = $this->companies_model->getAllPriceGroups();
            $this->load->view($this->theme . 'leads/lead_convert', $this->data);
        }
    }
    function pipeline(){
        $this->bpas->checkPermissions();
        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['containers'] = $this->leads_model->getContainers();
        foreach ($this->data['containers'] as $key => $container) {
            // Convert hex in rgb for background
            $hex = unserialize(CONTAINER_COLORS)[$container['container_color']];
            list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
            $this->data['containers'][$key]['container_rgb'] = "$r,$g,$b";
            $this->data['tasks'][$container['container_id']] = $this->db->query("SELECT * FROM bpas_companies WHERE group_name='lead' AND lead_group = '{$container['container_id']}' ORDER BY order_no ASC")->result_array();
        }
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('customers')]];
        $meta                 = ['page_title' => lang('leads'), 'bc' => $bc,'data' => $this->data];
        $this->page_construct('leads/pipeline', $meta, $this->data);
    }
    //--------pipeline----------
    public function update_position()
    {
        $data = $this->input->post('movedata');
        $to_done = false;
        foreach ($data as $container_id => $tasks) {
            $x = 1;
            if ($this->db->query("SELECT * FROM bpas_containers WHERE container_id = '$container_id' ")->num_rows() > 0) {
                $to_done = true;
            }
            foreach ($tasks as $task) {
                //Check if drag to DONE column
                if ($to_done == true) {
                    $this->db->query("UPDATE bpas_companies SET closed_date = IF(closed_date IS NULL AND lead_group <> '$container_id', NOW(), closed_date), lead_group = '$container_id', order_no = '$x' WHERE group_name='lead' AND id = '$task'");
                } else {
                    $this->db->query("UPDATE bpas_companies SET lead_group = '$container_id', order_no = '$x' WHERE group_name='lead' AND id = '$task'");
                }
                $x++;
            }
        }
    }
    public function update_field($table, $field, $value, $field_id, $element_id)
    {
        $this->db->where($field_id, $element_id);
        $this->db->update($table, array($field => $value));
        echo json_encode(array('status' => 4));
    }
    public function groups()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('brands')]];
        $meta                = ['page_title' => lang('groups'), 'bc' => $bc];
        $this->page_construct('leads/groups', $meta, $this->data);
    }
    public function getGroups()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select('container_id as id, container_name, container_order, container_color')
            ->from('containers')
            ->order_by('container_order','ASC')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('leads/edit_group/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_group') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_group') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('leads/delete_group/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_group()
    {
        $this->form_validation->set_rules('board', lang('board'), 'trim');
        $this->form_validation->set_rules('name', lang('slug'), 'trim|required');
        $this->form_validation->set_rules('order', lang('order'), 'trim|required');
        $this->form_validation->set_rules('color', lang('color'), 'trim');
        if ($this->form_validation->run() == true) {
            $data = [
                'container_board'        => $this->input->post('board'),
                'container_name'        => $this->input->post('name'),
                'container_order'        => $this->input->post('order'),
                'container_color' => $this->input->post('color'),
            ];

        } elseif ($this->input->post('add_group')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->leads_model->addGroup($data)) {
            $this->session->set_flashdata('message', lang('group_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'leads/add_group', $this->data);
        }
    }
     public function edit_group($id = null)
    {

        $brand_details = $this->leads_model->getGroupByID($id);

        $this->form_validation->set_rules('board', lang('board'), 'trim');
        $this->form_validation->set_rules('name', lang('slug'), 'trim|required');
        $this->form_validation->set_rules('order', lang('order'), 'trim|required');
        $this->form_validation->set_rules('color', lang('color'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = [
                'container_board'        => $this->input->post('board'),
                'container_name'        => $this->input->post('name'),
                'container_order'        => $this->input->post('order'),
                'container_color' => $this->input->post('color'),
            ];

        } elseif ($this->input->post('edit_brand')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('leads/groups');
        }

        if ($this->form_validation->run() == true && $this->leads_model->updateGroup($id, $data)) {
            $this->session->set_flashdata('message', lang('group_updated'));
            admin_redirect('leads/groups');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['brand']    = $brand_details;
            $this->load->view($this->theme . 'leads/edit_group', $this->data);
        }
    }
    public function delete_group($id = null)
    {
        if ($this->leads_model->leadHasGroups($id)) {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('group_has_leads')]);
        }

        if ($this->leads_model->deleteGroup($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('group_deleted')]);
        }
    }
}
