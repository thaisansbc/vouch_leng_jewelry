<?php defined('BASEPATH') or exit('No direct script access allowed');

class Drivers extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->admin_load('driver', $this->Settings->language);
        $this->load->library('form_validation');
        $this->load->admin_model('companies_model');
        $this->load->admin_model('settings_model');
    }

   public function index($action = NULL)
    {
        $this->bpas->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['action'] = $action;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('drivers')));
        $meta = array('page_title' => lang('List_Driver'), 'bc' => $bc);
        $this->page_construct('drivers/index', $meta, $this->data);
    }
    function getDrivers()
    {
        $this->bpas->checkPermissions('index');
        $this->load->library('datatables');
        $this->datatables
            ->select("id, name,name_kh, cf1, phone,cf2")
            ->from("companies")
            ->where('group_id', 5)
            ->where('group_name', 'driver')
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . lang("edit_driver") . "' href='" . admin_url('drivers/edit/$1') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("Delete_Driver") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger' href='" . admin_url('drivers/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        //->unset_column('id');
        echo $this->datatables->generate();
    }
    //Export in list driver
    function driver_action()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteDriver($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('Driver_Deleted_Have_Sales'));
                    } else {
                        $this->session->set_flashdata('message', lang("Driver_Deleted"));
                    }
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
                if ($this->input->post('form_action') == 'export_excel' || $this->input->post('form_action') == 'export_pdf') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('driver_name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('email_address'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone_number'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $customer = $this->site->getDriverByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $customer->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $customer->email);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $customer->phone . " ");
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'drivers_' . date('Y_m_d_H_i_s');
                    if ($this->input->post('form_action') == 'export_pdf') {
                        $html = $this->combine_pdf($_POST['val']);
                    }
                    if ($this->input->post('form_action') == 'export_excel') {
                        // $filename = 'sales_' . date('Y_m_d_H_i_s');
                        $this->load->helper('excel');
                        create_excel($this->excel, $filename);
                    }
                    admin_redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $this->session->set_flashdata('error', lang("no_driver_selected"));
                admin_redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function combine_pdf($sales_id)
    {
        $this->bpas->checkPermissions('pdf');

        foreach ($sales_id as $id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                 = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user']        = $this->site->getUser($inv->created_by);
            $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv']         = $inv;
            $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $html_data                 = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }
            $html[] = [
                'content' => $html_data,
                'footer'  => $this->data['biller']->invoice_footer,
            ];
        }
        $name = lang('sales') . '.pdf';
        $this->bpas->generate_pdf($html, $name);
    }
    function edit($id = NULL){
        $this->bpas->checkPermissions('edit', false, 'drivers');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $company_details = $this->companies_model->getCompanyByID($id);
        $this->form_validation->set_rules('driver_name', lang('driver_name'), 'trim|required');
        // if ($this->input->post('email') != $company_details->email) {
        //     $this->form_validation->set_rules('code', lang('email_address'), 'is_unique[companies.email]');
        // }

        if ($this->form_validation->run('companies/add') == true) {
            $data = [
                'name'          => $this->input->post('driver_name'),
                'name_kh'        => $this->input->post('name_kh'),
                'email'          => $this->input->post('email'),
                'group_id'       => 5,
                'group_name'     => 'driver',
                'company'        => $this->input->post('company'),
                'address'        => $this->input->post('address'),
                'vat_no'         => $this->input->post('vat_no'),
                'city'           => $this->input->post('city'),
                'state'          => $this->input->post('state'),
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
                'gst_no'         => $this->input->post('gst_no'),
            ];
          
        } elseif ($this->input->post('edit_driver')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers');
        }

        if ($this->form_validation->run() == true && $this->companies_model->updateCompany($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('driver_updated'));
            admin_redirect('drivers');
        } else {
            $this->data['driver'] = $company_details;
            $this->data['logos']    = $this->getLogoList();
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'drivers/edit_driver', $this->data);
        }
      
    }
    function create_driver(){
        $this->bpas->checkPermissions('add', false, 'drivers');
        $this->form_validation->set_rules('driver_name', lang("driver_name"), 'required');
        if ($this->form_validation->run('drivers/create_driver') == true) {
            $data = [
                'name'          => $this->input->post('driver_name'),
                'name_kh'        => $this->input->post('name_kh'),
                'email'          => $this->input->post('email'),
                'group_id'       => 5,
                'group_name'     => 'driver',
                'company'        => $this->input->post('company'),
                'address'        => $this->input->post('address'),
                'vat_no'         => $this->input->post('vat_no'),
                'city'           => $this->input->post('city'),
                'state'          => $this->input->post('state'),
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
                'gst_no'         => $this->input->post('gst_no'),
            ];
        } elseif ($this->input->post('add_driver')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers');
        }
        if ($this->form_validation->run() == true && $this->companies_model->createDriver($data)) {
            $this->session->set_flashdata('message', $this->lang->line('Driver_Added'));
            admin_redirect('drivers');
        } else {
            $this->data['logos']    = $this->getLogoList();
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'drivers/add', $this->data);
        }
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
    function Save($id = null)
    {

        $this->bpas->checkPermissions(false, true);

        $this->form_validation->set_rules('driver_name', lang("driver_name"), 'required');
        if ($this->form_validation->run('drivers/Save') == true) {

            $data = [
                'name'          => $this->input->post('driver_name'),
                'name_kh'        => $this->input->post('name_kh'),
                'email'          => $this->input->post('email'),
                'group_id'       => 5,
                'group_name'     => 'driver',
                'company'        => $this->input->post('company'),
                'address'        => $this->input->post('address'),
                'vat_no'         => $this->input->post('vat_no'),
                'city'           => $this->input->post('city'),
                'state'          => $this->input->post('state'),
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
                'gst_no'         => $this->input->post('gst_no'),
            ];
        }
     
        if ($this->companies_model->saveDriver($id, $data)) {
            $this->session->set_flashdata('message', lang("driver_Save"));
            admin_redirect('drivers');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers');
        }
    }

    function delete($id = null)
    {
      
        $this->bpas->checkPermissions(false, true);
        if ($this->companies_model->delete_driver($id)) {
            $this->session->set_flashdata('message', lang("Driver_Deleted"));
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        } else {
            $this->session->set_flashdata('error', validation_errors());
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : site_url('welcome')) . "'; }, 0);</script>");
        }
    }
    public function vehicles()
    {
        $this->bpas->checkPermissions('vehicles');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')),array('link' => 'truckings', 'page' => lang('trucking')), array('link' => '#', 'page' => lang('vehicles')));
        $meta = array('page_title' => lang('vehicles'), 'bc' => $bc);
        $this->page_construct('drivers/vehicles', $meta, $this->data);
    }
    public function getVehicles()
    {
        $this->bpas->checkPermissions('vehicles');
        $this->load->library('datatables');
        $this->datatables
            ->select("vehicles.id as id,
                        vehicles.code,
                        vehicles.model,
                        {$this->db->dbprefix('companies')}.name as driver,
                        vehicles.note,
                        vehicles.status,
                        vehicles.attachment,
                        ")
            ->from("vehicles")
            ->join("companies","companies.id = vehicles.driver_id","left")
            ->add_column("Actions", "<div class=\"text-center\"><a class=\"tip\" title='" . $this->lang->line("edit_vehicle") . "' href='" . admin_url('drivers/edit_vehicle/$1') . "' data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line("delete_vehicle") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('drivers/delete_vehicle/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }
    public function add_vehicle()
    {
        $this->bpas->checkPermissions('vehicles', true);
        
        $this->form_validation->set_rules('code', lang("plate"), 'required|is_unique[vehicles.code]');
        $this->form_validation->set_rules('model', lang("model"), 'required');
        if ($this->form_validation->run() == true) {
            $data = array(
                        'code' => $this->input->post('code'),
                        'model' => $this->input->post('model'),
                        'driver_id' => $this->input->post('driver_id'),
                        'fuel_id' => $this->input->post('fuel_id'),
                        'note' => $this->input->post('note'),
                    );
            if ($_FILES['attachment']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('attachment')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_truck')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers/vehicles');
        }
        if ($this->form_validation->run() == true && $id = $this->companies_model->addVehicle($data)) {
            $this->session->set_flashdata('message', $this->lang->line("vehicle_added"));
            admin_redirect('drivers/vehicles');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['drivers'] = $this->companies_model->getDrivers();
            $this->data['products'] = $this->site->getProducts();
            $this->load->view($this->theme . 'drivers/add_vehicle', $this->data);
        }
    }
    public function edit_vehicle($id = false)
    {
        $this->bpas->checkPermissions('vehicles', true);
        $truck = $this->companies_model->getVehicleByID($id);
        $this->form_validation->set_rules('model', lang("model"), 'required');
        $this->form_validation->set_rules('code', lang("plate"), 'required');
        if ($this->input->post('code') !== $truck->code) {
            $this->form_validation->set_rules('code', lang("code"), 'is_unique[vehicles.code]');
        }
        if ($this->form_validation->run() == true) {
            $data = array(
                        'code' => $this->input->post('code'),
                        'model' => $this->input->post('model'),
                        'driver_id' => $this->input->post('driver_id'),
                        'fuel_id' => $this->input->post('fuel_id'),
                        'status' => $this->input->post('status'),
                        'note' => $this->input->post('note'),
                    );
            if ($_FILES['attachment']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('attachment')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }
        } elseif ($this->input->post('edit_vehicle')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers/vehicles');
        }
        if ($this->form_validation->run() == true && $id = $this->companies_model->updateVehicle($id,$data)) {
            $this->session->set_flashdata('message', $this->lang->line("vehicle_edited"));
            admin_redirect('drivers/vehicles');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['drivers'] = $this->companies_model->getDrivers();
            $this->data['truck'] = $truck;
            $this->data['products'] = $this->site->getProducts();
            $this->load->view($this->theme . 'drivers/edit_vehicle', $this->data);
        }
    }
    
    public function delete_vehicle($id = NULL)
    {   
        $this->bpas->checkPermissions('vehicles', true);
        if ($this->companies_model->deleteVehicle($id)) {
            $this->bpas->send_json(['error' => 0, 'msg' => lang('vehicle_deleted')]);
        } else {
            $this->session->set_flashdata('warning', lang('vehicle_cannot_delete'));
            admin_redirect('drivers/vehicles');
        }
        
    }
    
    public function vehicle_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('vehicles');
                    $error = false;
                    foreach ($_POST['val'] as $id) {
                        if (!$this->companies_model->deleteVehicle($id)) {
                            $error = true;
                        }
                    }
                    if ($error) {
                        $this->session->set_flashdata('warning', lang('vehicle_cannot_delete'));
                    } else {
                        $this->session->set_flashdata('message', $this->lang->line("vehicle_deleted"));
                    }
                }
                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('trucks'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('plate'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('model'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('driver'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('status'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $vehicle = $this->companies_model->getVehicleByID($id);
                        $driver = $this->companies_model->getDriverByID($vehicle->driver_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $vehicle->code);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $vehicle->model);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $driver->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $this->bpas->remove_tag($vehicle->note));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($vehicle->status));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'vehicles_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_account_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
     public function route()
    {
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('system_settings'), 'page' => lang('system_settings')], ['link' => '#', 'page' => lang('custom_field')]];
        $meta                = ['page_title' => lang('custom_field'), 'bc' => $bc];
        $this->page_construct('drivers/route', $meta, $this->data);
    }

    public function getroute()
    {
        $this->load->library('datatables');
        $patent = $this->site->getCustomeFieldByCode('code');
        $this->datatables
            ->select("
                {$this->db->dbprefix('custom_field')}.id as id, 
                {$this->db->dbprefix('custom_field')}.name,
                {$this->db->dbprefix('custom_field')}.price", false)
            ->from('custom_field')
            ->join('custom_field c', 'c.id=custom_field.parent_id', 'left')
            ->group_by('custom_field.id')
            ->where('custom_field.parent_id',$patent->id)
            ->add_column('Actions', "<div class=\"text-center\"> 
                <a href='" . admin_url('drivers/edit_route/$1') . "' data-toggle='modal' data-target='#myModal' class='tip' title='" . lang('edit_route') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_custom_field') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('system_settings/delete_custom_field/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');

        echo $this->datatables->generate();
    }
    public function add_route(){

        $patent_id = $this->site->getCustomeFieldByCode('code')->id;

        $this->form_validation->set_rules('name', lang('name'), 'required|is_unique[custom_field.name]');
        $this->form_validation->set_rules('price', lang('price'), 'required|trim');
        $this->form_validation->set_rules('description', lang('description'), 'trim');
        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'price' => $this->input->post('price'),
                'description' => $this->input->post('description'),
                'parent_id'   => $patent_id,
            ];
        } elseif ($this->input->post('add_route')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers/route');
        }

        if ($this->form_validation->run() == true && $this->settings_model->addcustom_field($data)) {
            $this->session->set_flashdata('message', lang('custom_field_added'));
            admin_redirect('drivers/route');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['expenses'] = $this->settings_model->getParentCustomField();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'drivers/add_custom_field', $this->data);
        }
    }
    public function edit_route($id = null)
    {
        $patent_id = $this->site->getCustomeFieldByCode('code')->id;
        $this->form_validation->set_rules('description', lang('description'), '');
        $category = $this->settings_model->getCustomeFieldByID($id);
        if ($this->input->post('name') != $category->name) {
            $this->form_validation->set_rules('name', lang('category_code'), 'required|is_unique[custom_field.name]');
        }
        $this->form_validation->set_rules('name', lang('category_name'), 'required|min_length[2]');

        if ($this->form_validation->run() == true) {
            $data = [
                'name' => $this->input->post('name'),
                'price'    => $this->input->post('price'),
                'description' => $this->input->post('description'),
                'parent_id'   => $patent_id,
            ];
  
        } elseif ($this->input->post('edit_route')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('drivers/route');
        }

        if ($this->form_validation->run() == true && $this->settings_model->updatecustomField($id, $data)) {
            $this->session->set_flashdata('message', lang('custom_field_updated'));
            //admin_redirect('system_settings/custom_field');
            admin_redirect('drivers/route');
        } else {
            $this->data['error']    = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['expenses'] = $this->settings_model->getParentCustomField();
            $this->data['category'] = $category;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'drivers/edit_custom_field', $this->data);
        }
    }

}
