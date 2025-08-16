<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Workorder extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->lang->admin_load('products', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('products_model');
        $this->load->admin_model('settings_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->popup_attributes    = ['width' => '900', 'height' => '600', 'window_name' => 'sma_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0'];
    }
    /* ------------------------------------------------------- */
    function boms() {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('workorder'), 'page' => lang('workorder')), array('link' => '#', 'page' => lang('boms')));
        $meta = array('page_title' => lang('boms'), 'bc' => $bc);
        $this->page_construct('workorder/boms', $meta, $this->data);
    }

    function getBoms(){
        $this->bpas->checkPermissions('boms');
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' href='" .admin_url('workorder/delete_bom/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("boms.id as id, boms.name, CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by,  attachment")
            ->from('boms')
            ->join('users', 'users.id=boms.created_by', 'left')
            ->group_by("boms.id");
            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('boms.created_by', $this->session->userdata('user_id'));
            }
        $this->datatables->add_column("Actions", "<div class='text-center'><a href='" .admin_url('workorder/edit_bom/$1') . "' class='tip' title='" . lang("edit") . "'><i class='fa fa-edit'></i></a> " . $delete_link . "</div>", "id");
        echo $this->datatables->generate();
    }
    function add_bom(){
        $this->bpas->checkPermissions('boms', true);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $name = $this->input->post('name');
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $unit_qty = $_POST['unit_qty'][$r];
                $unit_id = $_POST['unit'][$r];
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $products[] = array(
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'unit_id' =>$unit_id,
                    'type' =>'raw_material'
                );
            }
            
            $f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r];
                $unit_qty = $_POST['funit_qty'][$r];
                $unit_id = $_POST['funit'][$r];
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $finished_goods[] = array(
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'unit_id' =>$unit_id,
                    'type' =>'finished_good'
                );
            }
            if (empty($products) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
                krsort($finished_goods);
            }
            
            $data = array(
                'name' => $name,
                'created_by' => $this->session->userdata('user_id'),
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

        }
        if ($this->form_validation->run() == true && $this->settings_model->addBom($data, $products, $finished_goods)) {
            $this->session->set_userdata('remove_bomls', 1);
            $this->session->set_flashdata('message', lang("bom_added"));
            admin_redirect('workorder/boms');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/boms'), 'page' => lang('boms')), array('link' => '#', 'page' => lang('add_bom')));
            $meta = array('page_title' => lang('add_bom'), 'bc' => $bc);
            $this->page_construct('workorder/add_bom', $meta, $this->data);
        }
    }
    
    function edit_bom($id = false){
        
        $this->bpas->checkPermissions('boms', true);
        $this->form_validation->set_rules('name', lang("name"), 'required');
        if ($this->form_validation->run() == true) {
            $name = $this->input->post('name');
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r];
                $unit_qty = $_POST['unit_qty'][$r];
                $unit_id = $_POST['unit'][$r];
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $products[] = array(
                    'bom_id' => $id,
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'unit_id' =>$unit_id,
                    'type' =>'raw_material'
                );
            }
            
            $f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r];
                $unit_qty = $_POST['funit_qty'][$r];
                $unit_id = $_POST['funit'][$r];
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $finished_goods[] = array(
                    'bom_id' => $id,
                    'product_id' => $product_id,
                    'quantity' =>$quantity,
                    'unit_qty' =>$unit_qty,
                    'unit_id' =>$unit_id,
                    'type' =>'finished_good'
                );
            }
            if (empty($products) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("products"), 'required');
            } else {
                krsort($products);
                krsort($finished_goods);
            }
            
            $data = array(
                'name' => $name,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
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
        }
        if ($this->form_validation->run() == true && $this->settings_model->updateBom($id, $data, $products, $finished_goods)) {
            $this->session->set_userdata('remove_bomls', 1);
            $this->session->set_flashdata('message', lang("bom_edited"));
            admin_redirect('workorder/boms');
        } else {
            $bom_items = $this->settings_model->getBomItems($id);
            krsort($bom_items);
            $c = rand(100000, 9999999);
            foreach ($bom_items as $bom_item) {
                $product = $this->site->getProductByID($bom_item->product_id);
                $row = json_decode('{}');
                $row->id = $product->id;
                $row->code = $product->code;
                $row->name = $product->name;
                $row->quantity = $bom_item->quantity;
                $row->unit_qty = $bom_item->unit_qty;
                $row->unit = $bom_item->unit_id;
                $units = $this->site->getUnitbyProduct($product->id,$product->unit);        
                if($bom_item->type == "raw_material"){
                    $pr[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row,'units' => $units);
                }else{
                    $pf[$c] = array('id' => $c, 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'row' => $row,'units' => $units);
                }
                $c++;
            }
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['raw_materials'] = json_encode($pr);
            $this->data['finished_goods'] = json_encode($pf);
            $this->data['bom'] = $this->settings_model->getBomByID($id);
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('system_settings'), 'page' => lang('system_settings')),array('link' => site_url('system_settings/boms'), 'page' => lang('boms')), array('link' => '#', 'page' => lang('edit_bom')));
            $meta = array('page_title' => lang('edit_bom'), 'bc' => $bc);
            $this->session->set_userdata('remove_bomls', 1);
            $this->page_construct('workorder/edit_bom', $meta, $this->data);
        }
    }
    public function delete_bom($id)
    {
        $this->bpas->checkPermissions('boms', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->settings_model->deleteBom($id)) {
            if ($this->input->is_ajax_request()) {
                echo lang("bom_deleted");
                die();
            }
            $this->session->set_flashdata('message', lang('bom_deleted'));
            admin_redirect('workorder/boms');
        }
    }
    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . site_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $rows = $this->settings_model->getAllProductNames($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->quantity = 1;
                $row->unit_cost = $row->cost;
                $row->unit_qty = 1;
                $units = $this->site->getUnitbyProduct($row->id,$row->unit);
                $pr[] = array(
                    'id' => str_replace(".", "", microtime(true)), 
                    'item_id' => $row->id, 
                    'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 
                    'units'=> $units);
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function bom_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->settings_model->deleteBom($id);
                    }
                    $this->session->set_flashdata('message', lang("bom_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }else if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('boms');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $boms = $this->settings_model->getBomByID($id);
                        $created_by = $this->site->getUser($boms->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $boms->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'boms_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function index($warehouse_id = NULL, $biller_id = NULL)
    {
        $this->bpas->checkPermissions('converts');
        $this->data['warehouses'] = $this->site->getWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $this->data['billers'] = $this->site->getBillers();
        $this->data['biller'] = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => '#', 'page' => lang('converts')));
        $meta = array('page_title' => lang('converts'), 'bc' => $bc);
        $this->page_construct('workorder/list_convert', $meta, $this->data);
    }
    function getListConvert($warehouse_id = NULL, $biller_id = NULL)
    {        
        $this->bpas->checkPermissions('converts');
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_convert") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete11' href='" . admin_url('workorder/delete_convert/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a>";

        $this->load->library('datatables');
        $this->datatables
            ->select("
                {$this->db->dbprefix('converts')}.id as id, 
                {$this->db->dbprefix('converts')}.date, 
                {$this->db->dbprefix('converts')}.reference_no, 
                boms.name as bom_name,
                warehouses.name as wh_name, 
                CONCAT({$this->db->dbprefix('users')}.last_name, ' ', {$this->db->dbprefix('users')}.first_name) as created_by, 
                {$this->db->dbprefix('converts')}.note
            ")
            ->from('converts')
            ->join('warehouses', 'warehouses.id=converts.warehouse_id', 'left')
            ->join('boms', 'boms.id=converts.bom_id', 'left')
            ->join('users', 'users.id=converts.created_by', 'left')
            ->group_by("converts.id");
            
        if ($warehouse_id) {
            $this->datatables->where('converts.warehouse_id', $warehouse_id);
        }
        if ($biller_id) {
            $this->datatables->where('converts.biller_id', $biller_id);
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
            $this->datatables->where('converts.biller_id', $this->session->userdata('biller_id'));
        }
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) { 
            $this->datatables->where_in('converts.warehouse_id', json_decode($this->session->userdata('warehouse_id')));
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('converts.created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", "<div class='text-center'>
            <a href='" . admin_url('workorder/view_convert/$1') . "' class='tip' title='" . lang("view_convert") . " 'data-backdrop='static' data-keyboard='false' data-toggle='modal' data-target='#myModal'><i class='fa fa-file-text-o'></i></a> 
            <a href='" . admin_url('workorder/edit_convert/$1') . "' class='tip' title='" . lang("edit_convert") . "'><i class='fa fa-edit'></i></a> 
            " . $delete_link . "</div>", "id");
        echo $this->datatables->generate();
    }
    public function view_convert($id)
    {
        $this->bpas->checkPermissions('converts');
        $convert = $this->products_model->getConvertByID($id);
        $this->data['convert']      = $convert;
        $this->data['bom']          = $this->settings_model->getBomByID($convert->bom_id);;
        $this->data['biller']       = $this->site->getCompanyByID($convert->biller_id);
        $this->data['convert_items'] = $this->products_model->getConvertItems($id);
        $this->data['created_by'] = $this->site->getUser($convert->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($convert->warehouse_id);
        if($this->Owner || $this->Admin || $this->bpas->GP['unlimited-print']){
            $this->data['print'] = 0;
        }else{
            if($this->Settings->limit_print=='1' && $this->site->checkPrint('Convert',$convert->id)){
                $this->data['print'] = 1;
            }else if($this->Settings->limit_print=='2' && $this->site->checkPrint('Convert',$convert->id)){
                $this->data['print'] = 2;
            }else{
                $this->data['print'] = 0;
            }
        }
        $this->load->view($this->theme.'workorder/view_convert', $this->data);
    }
    public function add_convert() {
        $this->bpas->checkPermissions('converts-add', true);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $this->form_validation->set_rules('bom', lang("bom"), 'required');
        $this->form_validation->set_rules('bom_quantity', lang("quality"), 'required');
        if ($this->form_validation->run() == true){
            $biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('con',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
            $bom_id = $this->input->post('bom');
            $bom_qty = $this->input->post('bom_quantity');
            $bom_finish_qty = $this->products_model->getFinishGoodBomQty($bom_id);
            $note = $this->bpas->clear_tags($this->input->post('note'));
            if ($this->Owner || $this->Admin || $this->bpas->GP['products-converts-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $raw_materials = false;
            $finished_goods = false;
            $convert_cost = 0;
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r]; 
                $unit_qty = $_POST['unit_qty'][$r]; 
                $unit_id = $_POST['unit_id'][$r]; 
                $type = $_POST['type'][$r];
                $product_details = $this->products_model->getProductByID($product_id);
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $cost = $_POST['cost'][$r];
                $convert_cost += (($cost * $quantity) / $bom_qty);
                $raw_materials[] = array(
                                        "product_id" => $product_id,
                                        "quantity" => $quantity,
                                        "unit_qty" => $unit_qty,
                                        "unit_id" => $unit_id,
                                        "cost" => $cost,
                                        "type" => $type,
                                    );
                $stockmoves[] = array(
                    'transaction'   => 'Convert',
                    'product_id'    => $product_id,
                    'product_code'  => $product_details->code,
                    'product_type'  => $product_details->type,
                    'quantity'      => $quantity * (-1),
                    'unit_quantity' => $unit->unit_qty,
                    'unit_code'     => $unit->code,
                    'unit_id'       => $unit_id,
                    'warehouse_id'  => $warehouse_id,
                    'date'          => $date,
                    'real_unit_cost'=> $cost,
                    'reference_no'  => $reference_no,
                    'user_id'       => $this->session->userdata('user_id'),
                );
                if($this->Settings->module_account == 1){       
                    $productAcc = $this->site->getProductAccByProductId($product_id);
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->stock_account?$productAcc->stock_account:$this->accounting_setting->default_stock,
                        'amount'        => -($cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->convert_account?$productAcc->convert_account:$this->accounting_setting->default_convert_account,
                        'amount'        => ($cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
            }
            $finish_cost = $convert_cost / $bom_finish_qty->quantity;
            $f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r]; 
                $unit_qty = $_POST['funit_qty'][$r]; 
                $unit_id = $_POST['funit_id'][$r]; 
                $type = $_POST['ftype'][$r];
                $product_details = $this->products_model->getProductByID($product_id);
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $finished_goods[] = array(
                                        "product_id" => $product_id,
                                        "quantity" => $quantity,
                                        "unit_qty" => $unit_qty,
                                        "unit_id" => $unit_id,
                                        "cost" => $finish_cost,
                                        "type" => $type,
                                    );
                $stockmoves[] = array(
                    'transaction'   => 'Convert',
                    'product_id'    => $product_id,
                    'product_code'  => $product_details->code,
                    'product_type'  => $product_details->type,
                    'quantity'      => $quantity,
                    'unit_quantity' => $unit->unit_qty,
                    'unit_code'     => $unit->code,
                    'unit_id'       => $unit_id,
                    'warehouse_id'  => $warehouse_id,
                    'date'          => $date,
                    'real_unit_cost'=> $finish_cost,
                    'reference_no'  => $reference_no,
                    'user_id'       => $this->session->userdata('user_id'),
                );
                if($this->Settings->module_account == 1){       
                    $productAcc = $this->site->getProductAccByProductId($product_id);
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->stock_account?$productAcc->stock_account:$this->accounting_setting->default_stock,
                        'amount'        => ($finish_cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->convert_account?$productAcc->convert_account:$this->accounting_setting->default_convert_account,
                        'amount'        => -($finish_cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }else{
                    $accTrans[] = array();
                }
            }
            if (empty($raw_materials) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($raw_materials);
                krsort($finished_goods);
            }
            $data = array(
                'reference_no' => $reference_no,
                'date' => $date,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'bom_id' => $bom_id,
                'quantity' => $bom_qty,
                'note' => $note,
                'created_by' => $this->session->userdata('user_id'),
            );
        }
        if ($this->form_validation->run() == true && $this->products_model->addConvert($data, $raw_materials, $finished_goods, $stockmoves, $accTrans)){
            $this->session->set_userdata('remove_cvls', 1);
            $this->session->set_flashdata('message', lang("convert_added")." - ".$data['reference_no']);
            admin_redirect('workorder');
        }else{
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));                        
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['boms'] = $this->products_model->getBoms();     
            $this->data['billers'] = $this->site->getBillers();         
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/converts'), 'page' => lang('converts')), array('link' => '#', 'page' => lang('add_convert')));
            $meta = array('page_title' => lang('add_convert'), 'bc' => $bc);
            $this->page_construct('workorder/add_convert', $meta, $this->data);
        }
    }       
    
    public function edit_convert($id) {
        $this->bpas->checkPermissions('converts-add', true);
        $this->form_validation->set_rules('biller', lang("biller"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        $this->form_validation->set_rules('bom', lang("bom"), 'required');
        $this->form_validation->set_rules('bom_quantity', lang("quality"), 'required');
        if ($this->form_validation->run() == true){
            $biller_id = $this->input->post('biller');
            $project_id = $this->input->post('project');
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('con',$biller_id);
            $warehouse_id = $this->input->post('warehouse');
            $bom_id = $this->input->post('bom');
            $bom_qty = $this->input->post('bom_quantity');
            $bom_finish_qty = $this->products_model->getFinishGoodBomQty($bom_id);
            $note = $this->bpas->clear_tags($this->input->post('note'));
            if ($this->Owner || $this->Admin || $this->bpas->GP['products-converts-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $raw_materials = false;
            $finished_goods = false;
            $convert_cost = 0;
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $quantity = $_POST['quantity'][$r]; 
                $unit_qty = $_POST['unit_qty'][$r]; 
                $unit_id = $_POST['unit_id'][$r]; 
                $type = $_POST['type'][$r];
                $product_details = $this->products_model->getProductByID($product_id);
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $cost = $_POST['cost'][$r];
                $convert_cost += (($cost * $quantity) / $bom_qty);
                $raw_materials[] = array(
                                        "convert_id" => $id,
                                        "product_id" => $product_id,
                                        "quantity" => $quantity,
                                        "unit_qty" => $unit_qty,
                                        "unit_id" => $unit_id,
                                        "cost" => $cost,
                                        "type" => $type,
                                    );
                $stockmoves[] = array(
                    'transaction'   => 'Convert',
                    'transaction_id' => $id,
                    'product_id' => $product_id,
                    'product_code' => $product_details->code,
                    'product_type' => $product_details->type,
                    'quantity' => $quantity * (-1),
                    'unit_quantity' => $unit->unit_qty,
                    'unit_code' => $unit->code,
                    'unit_id' => $unit_id,
                    'warehouse_id' => $warehouse_id,
                    'date' => $date,
                    'real_unit_cost' => $cost,
                    'reference_no' => $reference_no,
                    'user_id' => $this->session->userdata('user_id'),
                );
                if($this->Settings->module_account == 1){       
                    $productAcc = $this->site->getProductAccByProductId($product_id);
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->stock_account?$productAcc->stock_account:$this->accounting_setting->default_stock,
                        'amount'        => -($cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->convert_account?$productAcc->convert_account:$this->accounting_setting->default_convert_account,
                        'amount'        => ($cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'       => $this->session->userdata('user_id'),
                    );
                }else{
                    $accTrans[] = array();
                }
            }
            $finish_cost = $convert_cost / $bom_finish_qty->quantity;
            $f = isset($_POST['fproduct_id']) ? sizeof($_POST['fproduct_id']) : 0;
            for ($r = 0; $r < $f; $r++) {
                $product_id = $_POST['fproduct_id'][$r];
                $quantity = $_POST['fquantity'][$r]; 
                $unit_qty = $_POST['funit_qty'][$r]; 
                $unit_id = $_POST['funit_id'][$r]; 
                $type = $_POST['ftype'][$r];
                $product_details = $this->products_model->getProductByID($product_id);
                $unit = $this->site->getProductUnit($product_id,$unit_id);
                $finished_goods[] = array(
                                        "convert_id" => $id,
                                        "product_id" => $product_id,
                                        "quantity" => $quantity,
                                        "unit_qty" => $unit_qty,
                                        "unit_id" => $unit_id,
                                        "cost" => $finish_cost,
                                        "type" => $type,
                                    );
                $stockmoves[] = array(
                    'transaction' => 'Convert',
                    'transaction_id' => $id,
                    'product_id' => $product_id,
                    'product_code' => $product_details->code,
                    'product_type' => $product_details->type,
                    'quantity' => $quantity,
                    'unit_quantity' => $unit->unit_qty,
                    'unit_code' => $unit->code,
                    'unit_id' => $unit_id,
                    'warehouse_id' => $warehouse_id,
                    'date' => $date,
                    'real_unit_cost' => $finish_cost,
                    'reference_no' => $reference_no,
                    'user_id' => $this->session->userdata('user_id'),
                );
                if($this->Settings->module_account == 1){       
                    $productAcc = $this->site->getProductAccByProductId($product_id);
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->stock_account?$productAcc->stock_account:$this->accounting_setting->default_stock,
                        'amount'        => ($finish_cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'     => 'Convert',
                        'tran_no'       => $id,
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $productAcc->convert_account?$productAcc->convert_account:$this->accounting_setting->default_convert_account,
                        'amount'        => -($finish_cost * $quantity),
                        'narrative'     => 'Product Code: '.$product_details->code.'#'.'Qty: '.$quantity.'#'.'Cost: '.$finish_cost,
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
            }
            if (empty($raw_materials) || empty($finished_goods)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            } else {
                krsort($raw_materials);
                krsort($finished_goods);
            }
            $data = array(
                'reference_no' => $reference_no,
                'date' => $date,
                'biller_id' => $biller_id,
                'project_id' => $project_id,
                'warehouse_id' => $warehouse_id,
                'bom_id' => $bom_id,
                'quantity' => $bom_qty,
                'note' => $note,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }
        if ($this->form_validation->run() == true && $this->products_model->updateConvert($id, $data, $raw_materials, $finished_goods, $stockmoves, $accTrans)){
            $this->session->set_userdata('remove_cvls', 1);
            $this->session->set_flashdata('message', lang("convert_edited")." - ".$data['reference_no']);
            admin_redirect('workorder');
        }else{
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));                        
            $this->data['warehouses'] = $this->site->getWarehouses();
            $this->data['boms'] = $this->products_model->getBoms();     
            $this->data['billers'] = $this->site->getBillers();     
            $this->data['convert'] = $this->products_model->getConvertByID($id);
            $this->session->set_userdata('remove_cvls', 1);         
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => site_url('products'), 'page' => lang('inventory')), array('link' => site_url('products/converts'), 'page' => lang('converts')), array('link' => '#', 'page' => lang('edit_convert')));
            $meta = array('page_title' => lang('edit_convert'), 'bc' => $bc);
            $this->page_construct('workorder/edit_convert', $meta, $this->data);
        }
    }
    function delete_convert($id = NULL)
    {
        $this->bpas->checkPermissions('converts-delete', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->products_model->deleteConvert($id)) {
            if($this->input->is_ajax_request()) {
                echo lang("convert_deleted"); die();
            }
            $this->session->set_flashdata('message', lang('convert_deleted')." - ". $row->reference_no);
        }
        admin_redirect('workorder');
    }
    
    public function product_analysis($id = null)
    {
        //$convert = $this->products_model->getConvertByID($id);

        $header = $this->products_model->convertHeader($id);
        $deduct = $this->products_model->ConvertDeduct($id);
        $add    = $this->products_model->ConvertAdd($id);
        $this->data['header'] =$header;
        $this->data['deduct'] = $deduct;
        $this->data['add'] = $add;
        $this->data['logo'] = true;
        $this->data['page_title'] = $this->lang->line("product_analysis");
        $this->load->view($this->theme . 'workorder/product_anlysis', $this->data);
    }
    public function get_bom_items(){
        $bom_id = $this->input->get('bom_id');
        $warehouse_id = $this->input->get('warehouse_id');
        $quantity = $this->input->get('quantity');
        $boms_items = $this->products_model->getBomItems($bom_id);
        $raw_materials = false;
        $finish_products = false;
        if($boms_items){
            foreach($boms_items as $boms_item){
                $unit = $this->site->getProductUnit($boms_item->product_id,$boms_item->unit_id);
                $boms_item->unit_name = $unit->name;
                $boms_item->quantity = $boms_item->quantity * $quantity;
                $boms_item->unit_qty = $boms_item->unit_qty * $quantity;
                if($boms_item->type=='raw_material'){
                    $product_qty = $this->products_model->getProductQuantity($boms_item->product_id,$warehouse_id);
                    $boms_item->qoh = $product_qty['quantity'];
                    $raw_materials[] = $boms_item;
                }else{
                    $boms_item->qoh = 0;
                    $finish_products[] = $boms_item;
                }
            }
        }
        echo json_encode(array("raw_materials"=>$raw_materials,"finish_products"=>$finish_products));
    }
    function convert_actions()
    {
        if (!$this->Owner && !$this->Admin && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('converts-delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteConvert($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("convert_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);
                    
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('converts');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('bom'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('note'));
                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $convert = $this->products_model->getConvertByID($id);
                        $bom = $this->products_model->getBomByID($convert->bom_id);
                        $created_by = $this->site->getUser($convert->created_by);
                        $warehouse = $this->site->getWarehouseByID($convert->warehouse_id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($convert->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $convert->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $bom->name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $warehouse->name);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $created_by->last_name.' ' .$created_by->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->bpas->decode_html($convert->note));
                        $row++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'convert_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_record_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
}
