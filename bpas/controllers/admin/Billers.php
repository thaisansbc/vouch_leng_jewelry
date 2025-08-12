<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Billers extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if (!$this->Owner && !$this->Store) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('billers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');
    }

    public function add()
    {
        $this->bpas->checkPermissions(false, true);
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        if($this->Settings->module_hr){
            $this->form_validation->set_rules('latitude', lang('latitude'), 'trim|required');
            $this->form_validation->set_rules('longitude', lang('longitude'), 'trim|required');
            $this->form_validation->set_rules('radius', lang('radius'), 'trim|required');
        }
        // $this->form_validation->set_rules('email', $this->lang->line('email_address'), 'is_unique[companies.email]');
        if ($this->form_validation->run('companies/add') == true) {
            $prefix = $this->input->post('code') ? $this->input->post('code') : null;
            $data   = [
                'name'           => $this->input->post('name'),
                'email'          => $this->input->post('email'),
                'group_id'       => null,
                'group_name'     => 'biller',
                'company'        => $this->input->post('company'),
                'address'        => $this->input->post('address'),
                'vat_no'         => $this->input->post('vat_no'),
                'city'           => $this->input->post('city'),
                'state'          => $this->input->post('state'),
                'code'           => $this->input->post('code'),
                'postal_code'    => $this->input->post('postal_code'),
                'country'        => $this->input->post('country'),
                'phone'          => $this->input->post('phone'),
                'logo'           => $this->input->post('logo'),
                'cf1'            => $this->input->post('cf1'),
                'cf2'            => $this->input->post('cf2'),
                'cf3'            => $this->input->post('cf3'),
                'cf4'            => $this->input->post('cf4'),
                'cf5'            => $this->input->post('cf5'),
                'cf6'            => $this->input->post('cf6'),
                'invoice_footer' => $this->input->post('invoice_footer'),
                'warehouse_id'   => $this->input->post('warehouse'),
                'gst_no'         => $this->input->post('gst_no'),
                'latitude'       => $this->input->post('latitude'),
                'longitude'      => $this->input->post('longitude'),
                'radius'         => $this->input->post('radius'),
            ];
        } elseif ($this->input->post('add_biller')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('billers');
        }
        if ($this->form_validation->run() == true && $id = $this->companies_model->addCompany($data)) {
            $this->companies_model->addPrefix($id, $prefix);
            $this->session->set_flashdata('message', $this->lang->line('biller_added'));
            admin_redirect('billers');
        } else {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['logos']    = $this->getLogoList();
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/add', $this->data);
        }
    }

    public function edit($id = null)
    {
        // $this->bpas->checkPermissions(false, true);
        if ((!$this->Owner && !$this->Admin) && !$this->Store) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $company_details = $this->companies_model->getCompanyByID($id);
        $this->form_validation->set_rules('name', lang('name'), 'trim|required');
        // $this->form_validation->set_rules('address', lang('address'), 'trim|required');
        // if ($this->input->post('email') != $company_details->email) {
        //     $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        // }
        if($this->Settings->module_hr){
            $this->form_validation->set_rules('latitude', lang('latitude'), 'trim|required');
            $this->form_validation->set_rules('longitude', lang('longitude'), 'trim|required');
            $this->form_validation->set_rules('radius', lang('radius'), 'trim|required');
        }
        if ($this->form_validation->run('companies/add') == true) {
            $prefix = $this->input->post('code') ? $this->input->post('code') : null;
            $data   = [
                'name'           => $this->input->post('name'),
                'email'          => $this->input->post('email'),
                'group_id'       => null,
                'group_name'     => 'biller',
                'company'        => $this->input->post('company'),
                'address'        => $this->input->post('address'),
                'vat_no'         => $this->input->post('vat_no'),
                'city'           => $this->input->post('city'),
                'state'          => $this->input->post('state'),
                'code'           => $this->input->post('code'),
                'postal_code'    => $this->input->post('postal_code'),
                'country'        => $this->input->post('country'),
                'phone'          => $this->input->post('phone'),
                'logo'           => $this->input->post('logo'),
                'cf1'            => $this->input->post('cf1'),
                'cf2'            => $this->input->post('cf2'),
                'cf3'            => $this->input->post('cf3'),
                'cf4'            => $this->input->post('cf4'),
                'cf5'            => $this->input->post('cf5'),
                'cf6'            => $this->input->post('cf6'),
                'invoice_footer' => $this->input->post('invoice_footer'),
                'warehouse_id'   => $this->input->post('warehouse'),
                'gst_no'         => $this->input->post('gst_no'),
                'latitude'       => $this->input->post('latitude'),
                'longitude'      => $this->input->post('longitude'),
                'radius'         => $this->input->post('radius'),
            ];
        } elseif ($this->input->post('edit_biller')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('billers');
        }
        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->companies_model->updatePrefix($id, $prefix);
            $this->session->set_flashdata('message', $this->lang->line('biller_updated'));
            admin_redirect('billers');
        } else {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['biller']   = $company_details;
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['logos']    = $this->getLogoList();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/edit', $this->data);
        }
    }
    public function biller_actions()
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
                        if (!$this->companies_model->deleteBiller($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('billers_x_deleted_have_sales'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line('billers_deleted'));
                    }
                    redirect($_SERVER['HTTP_REFERER']);
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('billers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('city'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getCompanyByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->company);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->phone);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $customer->city);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'billers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_biller_selected'));
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

        if ($this->companies_model->deleteBiller($id)) {
            $this->db->delete('account_settings', ['biller_id' => $id]);
            $this->bpas->send_json(['error' => 0, 'msg' => lang('biller_deleted')]);
        } else {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('biller_x_deleted_have_sales')]);
        }
    }

    public function getBiller($id = null)
    {
        $this->bpas->checkPermissions('index');

        $row = $this->companies_model->getCompanyByID($id);
        $this->bpas->send_json([['id' => $row->id, 'text' => $row->company]]);
    }
    public function index($action = null)
    {
        if ((!$this->Owner && !$this->Admin) && !$this->Store) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('billers')]];
        $meta                 = ['page_title' => lang('billers'), 'bc' => $bc];
        $this->page_construct('billers/index', $meta, $this->data);
    }
    public function getBillers()
    {
        if ((!$this->Owner && !$this->Admin) && !$this->Store) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $biller_id = null;
        if ($this->Store) {
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $biller_id = $user->multi_biller ? $user->multi_biller : null;
            } else {
                $biller_id = $user->biller_id ? $user->biller_id : null;
            }
        } 
        $this->load->library('datatables');
        if($this->Settings->multi_biller){
            $this->datatables
                ->select('id, company, name, vat_no, phone, email, city, country')
                ->from('companies')
                ->where('group_name', 'biller');
                // have't botton delete (thaisan)
                // ->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('edit_biller') . "' href='" . admin_url('billers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_biller') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p></div>", 'id');
                // have botton delete//
            if ($biller_id) {
                $this->datatables->where("{$this->db->dbprefix('companies')}.id IN ({$biller_id})");
            }
            $edit_link   = anchor('admin/billers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_billers'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

            if ($this->Admin || $this->Owner) {
                $this->datatables->add_column('Actions', "<div class=\"text-center\">
                    ".$edit_link."<a class=\"tip\" title='" . $this->lang->line('qrcode') . "' href='" . admin_url('billers/qrcode/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static'><i class=\"fa fa-qrcode\"></i></a><a href='#' class='tip po' title='<b>" . $this->lang->line('delete_biller') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('billers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');            
            } elseif ($this->Store) {
                $this->datatables->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('edit_biller') . "' href='" . admin_url('billers/edit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static'><i class=\"fa fa-edit\"></i></a> </div>", 'id');
            }
        } else {
            $this->datatables
                ->select('id, company, name, vat_no, phone, email, city, country')
                ->from('companies')
                ->where('group_name', 'biller');    
            if ($biller_id) {
                $this->datatables->where("{$this->db->dbprefix('companies')}.id IN ({$biller_id})");
            }
            if ($this->Admin || $this->Owner) {
                $this->datatables->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('edit_biller') . "' href='" . admin_url('billers/edit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static'><i class=\"fa fa-edit\"></i></a> <a class=\"tip\" title='" . $this->lang->line('qrcode') . "' href='" . admin_url('billers/qrcode/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static'><i class=\"fa fa-qrcode\"></i></a><a href='#' class='tip po' title='<b>" . $this->lang->line('delete_biller') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p></div>", 'id');
            } elseif ($this->Store) {
                $this->datatables->add_column('Actions', "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line('edit_biller') . "' href='" . admin_url('billers/edit/$1') . "' data-toggle='modal' data-target='#myModal' data-backdrop='static'><i class=\"fa fa-edit\"></i></a> </div>", 'id');
            }
        }
        echo $this->datatables->generate();
    }

    public function getLogoList()
    {
        $this->load->helper('directory');
        $dirname = 'assets/uploads/logos';
        $ext     = ['jpg', 'png', 'jpeg', 'gif'];
        $files   = [];
        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                for ($i = 0; $i < sizeof($ext); $i++) {
                    if (stristr($file, '.' . $ext[$i])) { //NOT case sensitive: OK with JpeG, JPG, ecc.
                        $files[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        sort($files);
        return $files;
    }



    public function suggestions($term = null, $limit = null)
    {
        $this->bpas->checkPermissions('index');

        if ($this->input->get('term')) {
            $term = $this->input->get('term', true);
        }
        $limit           = $this->input->get('limit', true);
        $rows['results'] = $this->companies_model->getBillerSuggestions($term, $limit);
        $this->bpas->send_json($rows);
    }
    public function get_qr_code($file_name = null)
    {
        if($file_name != null) {            
            $data['title']      = 'Qr Code'; 
            $data['file_name']  = $file_name;
            $data['Settings']   = $this->Settings;  

            $this->load->view($this->theme .'billers/qr_code_display', $data);
        }
        else {
            $this->session->set_flashdata('error', 'Invalid QR Code.');
            redirect(base_url('dashboard'));
        }
    }


    public function qrcode($id = null)
    {
        
        if ((!$this->Owner && !$this->Admin) && !$this->Store) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $company_details = $this->companies_model->getCompanyByID($id);
        if ($this->input->post('generate_qr_code') == true) {
            $this->data['file_name'] = ''; 
            $data = [
                'qr_code' => md5(microtime(true).mt_Rand())
            ];
            if ($this->companies_model->updateCompany($id, $data)) {
                $this->session->set_flashdata('message', $this->lang->line('biller_updated'));
                admin_redirect('billers');
            }
        } else {
            $this->data['biller']   = $company_details;
            $this->data['file_name'] = ''; 
            // if(!empty($company_details->qr_code)){
            //     $bar_code = file_get_contents("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".$company_details->qr_code."&choe=UTF-8");
            //     $file_name = "assets/qr_code/qr_".$company_details->qr_code.".png";
            //     $file_handle = fopen($file_name, 'w');
            //     fwrite($file_handle, $bar_code);
            //     $this->data['file_name'] = $file_name;
            // }
            if(!empty($company_details->qr_code)){
                // $bar_code = file_get_contents("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".$company_details->qr_code."&choe=UTF-8");
                // $bar_code = file_get_contents("https://developers.google.com/chart?chs=300x300&cht=qr&chl=".$company_details->qr_code."&choe=UTF-8");
                // $file_name = "assets/qr_code/qr_".$company_details->qr_code.".png";
                // $file_handle = fopen($file_name, 'w');
                // fwrite($file_handle, $bar_code);
                // $this->data['file_name'] = $file_name;
                $company_qr_code_data = urlencode($company_details->qr_code);  // URL encode the QR code data

                // Use GoQR.me API for generating the QR code
                $bar_code = file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=".$company_qr_code_data);

                // Define the file path and name where the QR code will be saved
                $file_name = "assets/qr_code/qr_".$company_details->qr_code.".png";

                // Create and write the QR code image file
                $file_handle = fopen($file_name, 'w');
                fwrite($file_handle, $bar_code);
                fclose($file_handle);

                // Store the file name for later use
                $this->data['file_name'] = $file_name;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'billers/qrcode', $this->data);
        }
    }
}
