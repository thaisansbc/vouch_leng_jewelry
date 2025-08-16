<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends MY_Controller
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
        $this->lang->admin_load('suppliers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');
    }

    public function add()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        // $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[companies.email]');

        if ($this->form_validation->run('companies/add') == true) {
            $data = [
                'name'   => $this->input->post('name'),
                'email'       => $this->input->post('email'),
                'group_id'    => '5',
                'group_name'  => 'supplier',
                'company'     => $this->input->post('company'),
                'address'     => $this->input->post('address'),
                'vat_no'      => $this->input->post('vat_no'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'cf1'         => $this->input->post('cf1'),
                'cf2'         => $this->input->post('cf2'),
                'cf3'         => $this->input->post('cf3'),
                'cf4'         => $this->input->post('cf4'),
                'cf5'         => $this->input->post('cf5'),
                'cf6'         => $this->input->post('cf6'),
                'gst_no'      => $this->input->post('gst_no'),
                'gender'      => $this->input->post('gender'),
            ];
        } elseif ($this->input->post('add_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $sid = $this->companies_model->addCompany($data)) {
            $this->session->set_flashdata('message', $this->lang->line('supplier_added'));
            $ref = isset($_SERVER['HTTP_REFERER']) ? explode('?', $_SERVER['HTTP_REFERER']) : null;
            admin_redirect($ref[0] . '?supplier=' . $sid);
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/add', $this->data);
        }
    }

    public function add_user($company_id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $company_id = $this->input->get('id');
        }
        $company = $this->companies_model->getCompanyByID($company_id);

        $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[users.email]');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('confirm_password'), 'required');

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
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data, $active, $notify)) {
            $this->session->set_flashdata('message', $this->lang->line('user_added'));
            admin_redirect('suppliers');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company']  = $company;
            $this->load->view($this->theme . 'suppliers/add_user', $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->deleteSupplier($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('supplier_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('supplier_x_deleted_have_purchases')]);
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        // if ($this->input->post('email') != $company_details->email) {
        //     $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        // }

        if ($this->form_validation->run('companies/add') == true) {
            $data = [
                'name'   => $this->input->post('name'),
                'email'       => $this->input->post('email'),
                'group_id'    => '5',
                'group_name'  => 'supplier',
                'company'     => $this->input->post('company'),
                'address'     => $this->input->post('address'),
                'vat_no'      => $this->input->post('vat_no'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'phone'       => $this->input->post('phone'),
                'cf1'         => $this->input->post('cf1'),
                'cf2'         => $this->input->post('cf2'),
                'cf3'         => $this->input->post('cf3'),
                'cf4'         => $this->input->post('cf4'),
                'cf5'         => $this->input->post('cf5'),
                'cf6'         => $this->input->post('cf6'),
                'gst_no'      => $this->input->post('gst_no'),
                'gender'      => $this->input->post('gender'),
            ];
        } elseif ($this->input->post('edit_supplier')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('supplier_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['supplier'] = $company_details;
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/edit', $this->data);
        }
    }

    public function getSupplier($id = null)
    {
        // $this->bpas->checkPermissions('index');
        $row = $this->companies_model->getCompanyByID($id);
        $this->bpas->send_json([['id' => $row->id, 'text' => ($row->company != '-' ? $row->company : $row->name)]]);
    }

    public function getSuppliers()
    {
        $this->bpas->checkPermissions('index');
        $list_products ="<a class=\"tip\" title='" . $this->lang->line('list_products') . "' href='" . admin_url('products?supplier=$1') . "'><i class=\"fa fa-list\"></i>".lang('list_products')."</a> ";
        $list_deposits ="<a class=\"tip\" title='" . lang("list_deposits") . "' href='" . admin_url('suppliers/deposits/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-money\"></i>".lang('list_deposits')."</a> ";
        $add_deposit ="<a class=\"tip\" title='" . lang("add_deposit") . "' href='" . admin_url('suppliers/add_deposit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus\"></i>".lang('add_deposit')."</a>";
        $list_users ="<a class=\"tip\" title='" . $this->lang->line('list_users') . "' href='" . admin_url('suppliers/users/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-users\"></i>".lang('list_users')."</a>";
        $add_user ="<a class=\"tip\" title='" . $this->lang->line('add_user') . "' href='" . admin_url('suppliers/add_user/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-plus-circle\"></i>".lang('add_user')."</a> ";
        $edit_supplier ="<a class=\"tip\" title='" . $this->lang->line('edit_supplier') . "' href='" . admin_url('suppliers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i>".lang('edit_supplier')."</a>";
        $delete_supplier ="<a href='#' class='tip po' title='<b>".$this->lang->line('delete_supplier')."</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('suppliers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>".lang('delete_supplier')."</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>'.$list_products.'</li>
                        <li>'.$list_deposits.'</li>
                        <li>'.$add_deposit.'</li>
                        <li>'.$list_users.'</li>
                        <li>'.$add_user.'</li>
                        <li>'.$edit_supplier.'</li>
                        <li>'.$delete_supplier.'</li>
                    </ul>
                </div></div>';
        $this->load->library('datatables');
        $this->datatables
            ->select('id, company, name, code, phone, city, address, deposit_amount,deposit_amount,gst_no')
            ->from('companies')
            ->where('group_name', 'supplier');
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    function import_csv()
    {
        $this->bpas->checkPermissions('add', true);
        $this->load->helper('security');
        $this->form_validation->set_rules('csv_file', $this->lang->line("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            if (DEMO) {
                $this->session->set_flashdata('warning', $this->lang->line("disabled_in_demo"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
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
                     $address = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                     $city = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                     $state = $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                     $postal_code = $worksheet->getCellByColumnAndRow(8, $row)->getValue();
                     $country = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                     $vat_no = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                     $cf1s = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                     $cf2s = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                     $cf3s = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                     $cf4s = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                     $cf5s = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                     $cf6s = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                        if(empty($code)){
                            $this->session->set_flashdata('error', lang("check_supplier_code") . " (" . $code . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $row . ")");
                            admin_redirect("suppliers");
                        }
                        if(empty($phone)){
                            $this->session->set_flashdata('error', lang("check_supplier_phone") . " (" . $phone . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $row . ")");
                            admin_redirect("suppliers");
                        }
                        
                        $data[] = array(
                          'code'  => $code,
                          'company'  => $company,
                          'name'   => $name,
                          'email'    => $email,
                          'phone'  => $phone,
                          'address'   => $address,
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
                          'group_id'   => 4,
                          'group_name'   => 'supplier',
                         );
                    }
                }
                
                $rw = 2;
                $checkCode = false;
                $checkPhone = false;
                foreach ($data as $csv_com) {
                    if(!$this->companies_model->getCompanyByCodeGroupName(trim($csv_com['code'],'supplier'))) {
                        if ($csv_com['email'] && $this->companies_model->getCompanyByEmail($csv_com['email'])) {
                            $this->session->set_flashdata('error', lang("check_customer_email") . " (" . $email . "). " . lang("customer_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("suppliers");
                        }
                        if(isset($checkCode[trim($csv_com['code'])]) && $checkCode[trim($csv_com['code'])]){
                            $this->session->set_flashdata('error', lang("check_supplier_code") . " (" . $csv_com['code'] . "). " . lang("supplier_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("suppliers");
                        }
                        if(isset($checkPhone[trim($csv_com['phone'])]) && $checkPhone[trim($csv_com['phone'])]){
                            $this->session->set_flashdata('error', lang("check_supplier_phone") . " (" . $csv_com['phone'] . "). " . lang("supplier_duplicate_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("suppliers");
                        }
                        if ($this->companies_model->getCompanyByPhone($csv_com['phone'],'supplier')) {
                            $this->session->set_flashdata('error', lang("check_supplier_phone") . " (" . $csv_com['phone'] . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                            admin_redirect("suppliers");
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
                        
                    }else{
                        $this->session->set_flashdata('error', lang("check_supplier_code") . " (" . $csv_com['code'] . "). " . lang("supplier_already_exist") . " (" . lang("line_no") . " " . $rw . ")");
                        admin_redirect("suppliers");
                    }

                    $rw++;
                }
                $ikeys = array('code', 'company', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country', 'vat_no', 'cf1', 'cf2', 'cf3', 'cf4', 'cf5', 'cf6', 'group_id','group_name');

                $companies = array();
                foreach (array_map(null, $company_code, $company_company, $company_name, $company_email, $company_phone,$company_address, $company_city, $company_state, $company_postal_code, $company_country, $company_vat_no, $cf1, $cf2, $cf3, $cf4, $cf5, $cf6, $group_id,$group_name) as $ikey => $value) {
                    $companies[] = array_combine($ikeys, $value);
                    
                }
            }
        } elseif ($this->input->post('import')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('suppliers');
        }

        if ($this->form_validation->run() == true && !empty($companies)) {
            if ($this->companies_model->addCompanies($companies)) {
                $this->session->set_flashdata('message', $this->lang->line("suppliers_added"));
                admin_redirect('suppliers');
            }
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'suppliers/import', $this->data);
        }
    }
    public function index($action = null)
    {
        $this->bpas->checkPermissions();

        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('suppliers')]];
        $meta                 = ['page_title' => lang('suppliers'), 'bc' => $bc];
        $this->page_construct('suppliers/index', $meta, $this->data);
    }

    public function suggestions($term = null, $limit = null)
    {
        // $this->bpas->checkPermissions('index');
        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getSupplierSuggestions($term, $limit);
        $this->bpas->send_json($rows);
    }

    public function supplier_actions()
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
                        if (!$this->companies_model->deleteSupplier($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('suppliers_x_deleted_have_purchases'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line('suppliers_deleted'));
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
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'suppliers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_supplier_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
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
        $this->load->view($this->theme . 'suppliers/users', $this->data);
    }

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index', true);
        $this->data['error']    = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['supplier'] = $this->companies_model->getCompanyByID($id);
        $this->load->view($this->theme . 'suppliers/view', $this->data);
    }
    function deposits($supplier_id = NULL)
    {
        $this->bpas->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $supplier_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['modal_js'] = $this->site->modal_js();
        $this->data['supplier'] = $this->companies_model->getCompanyByID($supplier_id);
        $this->load->view($this->theme .'suppliers/deposits', $this->data);

    }
    
    function get_deposits($id)
    {
        
        $this->bpas->checkPermissions('deposits');
        $this->load->library('datatables');
        $this->datatables
            ->select("deposits.id as id, date,reference, amount, deposits.note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, opening as opening_ap", false)
            ->from("deposits")
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->order_by('id','ASC')
            ->where('deposits.company_id', $id)
            //->where('deposits.order_status', '0')
            
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("deposit_note") . "' href='" . admin_url('suppliers/deposit_note/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-file-text-o\"></i></a> <a class=\"tip\" title='" . lang("edit_deposit") . "' href='" . admin_url('suppliers/edit_deposit/$1') . "' data-toggle='modal' data-target='#myModal2'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_deposit') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('suppliers/delete_deposit/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id")
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    
    public function getDeposits(){

        $return_deposit = anchor('admin/suppliers/return_deposit/$1', '<i class="fa fa-reply"></i> ' . lang('return_deposit'), 'data-toggle="modal" data-target="#myModal2"');
        $deposit_note = anchor('admin/suppliers/deposit_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('deposit_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_deposit = anchor('admin/suppliers/edit_deposit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_deposit'), 'data-toggle="modal" data-target="#myModal2"');
        $delete_deposit = "<a href='#' class='po' title='<b>" . lang("delete_deposit") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . site_url('suppliers/deleteDeposit/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_deposit') . "</a>";

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $deposit_note . '</li>
            <li>' . $edit_deposit . '</li>
            <li>' . $return_deposit . '</li>
            <li>' . $delete_deposit . '</li>
            <ul>
            </div></div>';

            $this->load->library('datatables');
            $this->datatables
            ->select("deposits.id as dep_id, companies.id AS id , deposits.reference, deposits.date,companies.name, deposits.amount, deposits.paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by", false)
            ->from("deposits")
            ->join('companies', 'companies.id = deposits.company_id', 'left')
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where('companies.group_name', 'supplier')
            ->where('deposits.amount <>', 0)
            ->where('deposits.reference <>', '')
            ->add_column("Actions", $action, "dep_id")
            ->unset_column('dep_id');

            echo $this->datatables->generate();
    }
    
    function add_deposit($id =null)
    {
        $this->bpas->checkPermissions('deposits', true);
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('date', lang("date"), 'required');
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        if($this->Settings->accounting == 1){
            $this->form_validation->set_rules('bank_account', lang("bank_account"), 'required');
        }
        if ($this->form_validation->run() == true) {
            $supplier_id = $this->input->post('supplier_id');
            $company = $this->site->getCompanyByID($supplier_id);
            $reference = $this->site->getReference('sd') ? $this->site->getReference('sd'): $this->input->post('reference_no');
            $biller_id = $this->input->post('biller');
            
            $date = $this->bpas->fld(trim($this->input->post('date')));
           
            $reference_no=$this->input->post('reference_no');
            $po_paid=$this->input->post('po_paid');
            $amount_dep=$this->input->post('amount');
            $po=array(
                'paid' => $po_paid+$amount_dep,
            );
            $data = array(
                'reference' => $reference,
                'date' => $date,
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note') ? $this->input->post('note') : $company->name,
                'company_id' => $company->id,
                'created_by' => $this->session->userdata('user_id'),
                'bank_code' => $this->input->post('bank_account'),
                'biller_id' => $this->input->post('biller')
            );

            $cdata = array(
                'deposit_amount' => ($company->deposit_amount+$this->input->post('amount'))
                 
                 
            );
            //=====add accounting=====//
            if($this->Settings->accounting == 1){
               
                $paying_to = $this->input->post('bank_account');
                $accTranPayments[] = array(
                    'tran_type' => 'Deposit',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $paying_to,
                    'amount' => -($this->input->post('amount')),
                    'narrative' => $this->site->getAccountName($paying_to),
                    'description' => $this->input->post('note'),
                    'biller_id' => $biller_id,
                    'project_id' => '',
                    'people_id' => $this->session->userdata('user_id'),
                    'customer_id' => $company->id,
                    'created_by'  => $this->session->userdata('user_id'),
                );
                
                $account_deposit = $this->accounting_setting->default_purchase_deposit;
                $accTranPayments[] = array(
                    'tran_type' => 'Deposit',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $account_deposit,
                    'amount' => $this->input->post('amount'),
                    'narrative' => $this->site->getAccountName($account_deposit),
                    'description' => $this->input->post('note'),
                    'biller_id' => $biller_id,
                    'project_id' => '',
                    'people_id' => $this->session->userdata('user_id'),
                    'customer_id' => $company->id,
                    'created_by'  => $this->session->userdata('user_id'),
                );
               
                
            
            }
            //=====end accounting=====//

        } elseif ($this->input->post('add_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && 
          //  $this->companies_model->addDeposit($data, $cdata, $payment,$po,$reference_no)) {
            $this->companies_model->addDeposit($data, $cdata, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            admin_redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['po_reference'] = $this->companies_model->getPOReference();
            $this->data['reference'] = $this->site->getReference('sd');
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $company = $this->companies_model->getCompanyByID($id);
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['supplier'] = $company;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['suppliers'] = $this->site->getSuppliers();
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $this->data['userBankAccounts'] =  $this->site->getAllBankAccountsByUserID();
            
            $this->load->view($this->theme . 'suppliers/add_deposit', $this->data);
        }
    }
    
    function edit_deposit($id = NULL)
    {
        $this->bpas->checkPermissions('deposits', true);
        
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);
        $payment = $this->companies_model->getPaymentBySupplierDeposit($id);
        $deposit_items = $this->companies_model->getDepositItems($deposit->company_id);

        $total_deposit_items = 0;
        if($deposit_items){
            foreach($deposit_items as $deposit_item){
                $total_deposit_items += $deposit_item->amount;
            }
        }
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if ($this->form_validation->run() == true) {
            $reference = $this->site->getReference('sd') ? $this->site->getReference('sd'): $this->input->post('reference_no');
            $biller_id = $this->input->post('biller');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'date' => $date,
                'reference' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount'),
                'paid_by' => $this->input->post('paid_by'),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
                'bank_code' => $this->input->post('bank_account'),
                'biller_id' => $this->input->post('biller')
            );
    
            $cdata = array(
                'deposit_amount' => (($company->deposit_amount - $deposit->amount) + $this->input->post('amount')),
            );
            //=====add accounting=====//
            if($this->Settings->accounting == 1){
               
                $paying_to = $this->input->post('bank_account');
                $accTranPayments[] = array(
                    'tran_no' => $id,
                    'tran_type' => 'Deposit',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $paying_to,
                    'amount' => -($this->input->post('amount')),
                    'narrative' => $this->site->getAccountName($paying_to),
                    'description' => $this->input->post('note'),
                    'biller_id' => $biller_id,
                    'project_id' => '',
                    'people_id' => $this->session->userdata('user_id'),
                    'customer_id' => $company->id,
                    'created_by'  => $this->session->userdata('user_id'),
                );
                
                $account_deposit = $this->accounting_setting->default_purchase_deposit;
                $accTranPayments[] = array(
                    'tran_no' => $id,
                    'tran_type' => 'Deposit',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $account_deposit,
                    'amount' => $this->input->post('amount'),
                    'narrative' => $this->site->getAccountName($account_deposit),
                    'description' => $this->input->post('note'),
                    'biller_id' => $biller_id,
                    'project_id' => '',
                    'people_id' => $this->session->userdata('user_id'),
                    'customer_id' => $company->id,
                    'created_by'  => $this->session->userdata('user_id'),
                );
            }
            //=====end accounting=====//
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("suppliers");
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateDeposit($id, $data, $cdata, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            admin_redirect("suppliers");
        } else {
            
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['supplier'] = $company;
            $this->data['deposit'] = $deposit;
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $this->load->view($this->theme . 'suppliers/edit_deposit', $this->data);
        }
    }
    public function delete_deposit($id)
    {
        $this->bpas->checkPermissions(null, true);

        if ($this->companies_model->deleteDeposit($id)) {
            //account---
            $this->site->deleteAccTran('Deposit',$id);
            //---end account
            $this->bpas->send_json(['error' => 0, 'msg' => lang('deposit_deleted')]);
         //   redirect($_SERVER['HTTP_REFERER']);

          //  $this->session->set_flashdata('message', lang("deposit_deleted"));
           // admin_redirect("suppliers");

        }
    }
    public function deposit_note($id = null)
    {
        $this->bpas->checkPermissions('deposits', true);
        $deposit = $this->companies_model->getDepositByID($id);
        $this->data['customer'] = $this->companies_model->getCompanyByID($deposit->company_id);
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = $this->lang->line("deposit_note");
        $this->load->view($this->theme . 'suppliers/deposit_note', $this->data);
    }
    
    public function return_deposit($id){
        $this->bpas->checkPermissions('deposits', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->companies_model->getDepositByID($id);
        $company = $this->companies_model->getCompanyByID($deposit->company_id);
        if ($this->Owner || $this->Admin) {
            $this->form_validation->set_rules('date', lang("date"), 'required');
        }
        $this->form_validation->set_rules('amount', lang("amount"), 'required|numeric');
        
        if($this->form_validation->run() == true){
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $deposit->date;
            }
            $data = array(
                'amount' => ($deposit->amount - $this->input->post('amount')),
                'note' => $this->input->post('note'),
                'company_id' => $deposit->company_id,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => $date = date('Y-m-d H:i:s'),
                'biller_id' => $this->input->post('biller')
            );
            
            $payment = array(
                'date' => $date,
                'deposit_id' => $id,
                'reference_no' => $this->site->getReference('sp'),
                'amount' => $this->input->post('amount'),
                'paid_by' => 'cash',
                'note' => $this->input->post('note') ? $this->input->post('note') : $company->name,
                'bank_account' => $this->input->post('bank_account'),
                'type' => 'received',
                'biller_id' => $this->input->post('biller')
            );

            $cdata = array(
                'deposit_amount' => (($deposit->amount - $this->input->post('amount')))
            );
        } elseif ($this->input->post('return_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->companies_model->ReturnDeposit($id, $data, $cdata, $payment)) {
            $this->session->set_flashdata('message', lang("deposit_returned"));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['company'] = $company;
            $this->data['deposit'] = $deposit;
            //$this->data['ponumber'] = $this->site->getReference('pq');
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $this->load->view($this->theme . 'suppliers/return_deposit', $this->data);
        }
    }
    
    function deleteDeposit($id = NULL)
    {
        $this->bpas->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->companies_model->deleteSupplierDeposit($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("purchase_deposit_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('purchase_deposit_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    function getPORef()
    {
        $data=array();
        $ref = $this->input->get('ref', TRUE);
        $data = $this->companies_model->getPORef($ref);
        echo json_encode($data);
    }
}
