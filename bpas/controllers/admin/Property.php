<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Property extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        $this->lang->admin_load('products', $this->Settings->user_language);
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('loan_model');
        $this->load->admin_model('pos_model');
        $this->load->admin_model('reports_model');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : NULL;
        $this->data['pos_settings'] = $this->pos_settings;

        $this->load->admin_model('products_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->popup_attributes = array('width' => '900', 'height' => '600', 'window_name' => 'bpas_popup', 'menubar' => 'yes', 'scrollbars' => 'yes', 'status' => 'no', 'resizable' => 'yes', 'screenx' => '0', 'screeny' => '0');
    }

    function index($warehouse_id = NULL)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }
        $this->data['products'] = $this->site->getProducts('property');
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('property')));
        $meta = array('page_title' => lang('property'), 'bc' => $bc);
        $this->page_construct('property/products/index', $meta, $this->data);
    }

    function list_property($warehouse_id = NULL){
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : NULL;
        } else {
            $this->data['warehouses'] = NULL;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : NULL;
        }

        $this->data['supplier'] = $this->input->get('supplier') ? $this->site->getCompanyByID($this->input->get('supplier')) : NULL;
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('property')));
        $meta = array('page_title' => lang('property'), 'bc' => $bc);
        $this->page_construct('property/products/list_property', $meta, $this->data);
    }
    public function view_sale($id = null){
        $sale_id= $this->site->getSaleByIDByProId($id);
        $sale_id = $sale_id->sale_id;
        if ($sale_id != null)
            admin_redirect('sales/view/'.$sale_id.'');
        else {
            $this->session->set_flashdata('message', lang("product_not_yet_sold"));
            admin_redirect('products/index');
        }

    }
    public function view_property($id = null)
    {
        $sale_id= $this->site->getSaleByIDByProId($id);
        $sale_id = $sale_id->sale_id;
        if ($sale_id != null)
            admin_redirect('property/sales/view_property/'.$sale_id.'');
        else {
            $this->session->set_flashdata('message', lang("product_not_yet_sold"));
            admin_redirect('property/products/index');
        }

    }
    function getProperty($warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('index', TRUE);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;

        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
        if ($this->input->get('product_type')) {
            $product_type = $this->input->get('product_type');
        } else {
            $product_type = NULL;
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

        if ((! $this->Owner || ! $this->Admin) && ! $warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $installment_link = '';
        if($this->Settings->module_installment && (isset($this->GP['installments-add']) || ($this->Owner || $this->Admin))){
            $installment_link = anchor('admin/installments/add/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_installment'),'class="add_installment"');
        }
        $down_payments_link   = anchor('admin/sales/view_down_payments/0/$1', '<i class="fa fa-money"></i> ' . lang('view_down_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_Downpayment_link = anchor('admin/sales/add_downpayment/0/$1', '<i class="fa fa-money"></i> ' . lang('add_down_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $sale_detail_link = anchor('admin/property/view_sale/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'), array('target' => '_blank'));
        $detail_link = anchor('admin/property/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('property_details'), array('target' => '_blank'));
        $sale_link = anchor('admin/property/add_sale/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_sale'), array('target' => '_blank'));
        $delete_link = "<a href='#' class='tip po' title='<b>" . $this->lang->line("delete_product") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('property/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_property') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $sale_detail_link . '</li>
            <li>' . $detail_link . '</li>
            <li><a href="' . admin_url('property/add/$1') . '"><i class="fa fa-plus-square"></i> ' . lang('duplicate_property') . '</a></li>
            <li><a href="' . admin_url('property/edit/$1') . '"><i class="fa fa-edit"></i> ' . lang('edit_property') . '</a></li>';
        $setrack ='<a href="' . admin_url('property/set_rack/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-tasks"></i> '. lang('set_rack') . '</a>';
        $book ='<a href="' . admin_url('property/booking/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-book"></i> '. lang('book') . '</a>';
        $block ='<a href="' . admin_url('property/blocking/$1/' . $warehouse_id) . '" data-toggle="modal" data-backdrop="static" data-target="#myModal"><i class="fa fa-lock"></i> '. lang('block') . '</a>';
        $realise = "<a href='#' class='tip po' title='<b>" . $this->lang->line("unblock") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete1' id='a__$1' href='" . admin_url('property/realising/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-retweet\"></i> "
            . lang('unblock') . "</a>";
        $action .= '
        
            <li>' . $sale_link . '</li>
            <li class="booking hide">' . $book . '</li>
            <li class="blocking">' . $block . '</li>
            <li class="realising">' . $realise . '</li>
            <li class="divider"></li>
            <li>' . $delete_link . '</li>
            </ul>
        </div></div>';
        $this->load->library('datatables');
   
             $ai = "( SELECT sales, product_id, {$this->db->dbprefix('adjustment_items')}.serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, ' (', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END), ')') SEPARATOR '\n') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";
            $this->datatables
                ->select("{$this->db->dbprefix('products')}.id as id, 
                {$this->db->dbprefix('products')}.image as image, 
                {$this->db->dbprefix('projects')}.project_name, 

                {$this->db->dbprefix('products')}.code as code,
                {$this->db->dbprefix('sales')}.reference_no as proname, 
                {$this->db->dbprefix('sales')}.customer as name, 
                {$this->db->dbprefix('brands')}.name as brand, 
                {$this->db->dbprefix('categories')}.name as cname, 
                
                {$this->db->dbprefix('products')}.price as price,
                {$this->db->dbprefix('sales')}.paid as paid,
                ({$this->db->dbprefix('products')}.price - {$this->db->dbprefix('sales')}.paid) as balance,
                {$this->db->dbprefix('products')}.quantity as quantity,
                payment_status")
                ->join('booking', 'booking.product_id = products.id', 'left')
                ->join('projects', 'projects.project_id = products.project_id', 'left')
                ->join('sale_items', 'sale_items.product_id = products.id', 'left')
                ->join('sales', 'sales.id = sale_items.sale_id', 'left')
                ->from('products');
         //if ($warehouse_id) {
           // $this->datatables->join('warehouses_products wp', 'products.id=wp.product_id', 'left')
              //  ->where('wp.warehouse_id', $warehouse_id);
          // }

            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
            ->join('units', 'products.unit=units.id', 'left')
            ->join('brands', 'products.brand=brands.id', 'left')
            ->where('products.module_type', 'property');
            // ->group_by("products.id");
        if ($product) {
            $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
        }
        if ($category) {
            $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
        }
        if ($product_type) {
            $this->datatables->where($this->db->dbprefix('products') . ".quantity", $product_type);

            //$this->datatables->where($this->db->dbprefix('products') . ".quantity <=", '-1');
            //$this->datatables->where($this->db->dbprefix('products') . ".quantity !=", '-2');
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
 
    public function booking_list($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $biller_id = $this->session->userdata('biller_id');
        $this->data['users'] = $this->site->getStaff();
        $this->data['products'] = $this->site->getProducts();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['user_billers'] = $this->site->getCompanyByID($biller_id);
        $this->data['drivers']  = $this->site->getDriver();
        if ($warehouse_id) {
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
            $meta = ['page_title' => lang('sales'), 'bc' => $bc];
            $this->page_construct('sales/index', $meta, $this->data);
        } else {
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
            $meta = ['page_title' => lang('sales'), 'bc' => $bc];
            $this->page_construct('property/sales/booking_list', $meta, $this->data);
        }
    }
    function decrease()
    {   
        return true;
        /*
        $product_id = $this->input->get('product_id', true);
        if($this->products_model->UpdateProductBYID($product_id)){
            return 'success';
        };*/
    }
    
    function increase()
    {   
        $product_id = $this->input->get('product_id', true);
        if($this->products_model->realiseProduct($product_id)){
            return 'success';
        };
    }
     function view_blocking($booking_id){
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $booking = $this->reports_model->getBlockByID($booking_id);
       var_dump($booking);
       exit();
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($booking->booking_by, true);
        }
        $this->data['customer']  = $this->site->getCompanyByID($booking->booker);
        $this->data['booking']   = $booking;
        $this->data['created_by']= $this->site->getUser($booking->booking_by); 
        $this->load->view($this->theme . 'property/products/block_detail_report', $this->data);
    }
    function view_booking($booking_id){
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $booking = $this->reports_model->getBookingByID($booking_id);
       
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($booking->booking_by, true);
        }
        $this->data['customer']  = $this->site->getCompanyByID($booking->booker);
        $this->data['booking']   = $booking;
        $this->data['created_by']= $this->site->getUser($booking->booking_by); 
        $this->load->view($this->theme . 'property/products/book_detail_report', $this->data);
    }
     function booking($product_id = NULL, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->form_validation->set_rules('expiry_date', lang("expiry_date"), 'required');
        $this->form_validation->set_rules('booking_price', lang("booking_price"), 'required');
        $qty = $this->products_model->getProductByID($product_id);
        if($qty->quantity == 2){
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $booking = $this->products_model->getBooking($product_id);
       
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($booking->booking_by, true);
        }
        $this->data['customer']  = $this->site->getCompanyByID($booking->booker);
        $this->data['booking']   = $booking;
        $this->data['created_by']= $this->site->getUser($booking->booking_by); 
        $this->load->view($this->theme . 'property/products/book_detail', $this->data);
        }else if($qty->quantity == 1){
        if ($this->form_validation->run() == true) {
            $customer_id = $this->input->post('customer');
            $customer = $this->site->getCompanyByID($customer_id);
            $product = $this->site->getProductByID($product_id);
            $payment = [];
            $products = [
                        'product_id'        => $product->id,
                        'product_code'      => $product->code,
                        'option_id'      => $product->option_id,
                        'product_name'      => $product->name,
                        'product_type'      => $product->type,
                        'net_unit_price'    => $product->price,
                        'unit_price'        => $product->price,
                        'quantity'          => 1,
                        'unit_quantity'     => 1,
                        'warehouse_id'      => $product->warehouse,
                        
                        'subtotal'          => $product->price,
                        'serial_no'         => $product->serial_no,
                        'max_serial'         => $product->max_serial,
                        'real_unit_price'   => $product->price,
                    ];
            $data = ['date' => date("Y-m-d", strtotime($this->input->post('current_date'))),
                'project_id'        =>  $product->project_id,
                'product_id' => $product->id,
                'reference_no'        => '$reference',
                'customer_id'         => $customer_id,
                'customer'            => $customer->name . " ". $customer->company,
                'biller_id'           => 1,
                'biller'              => 1,
                'note'                => $this->input->post('note'),
                'total'               => $product->price,
                'grand_total'         => $product->price,
                'total_items'         => 1,
                'sale_status'         => 'booking',
                'payment_status'      => "due",
                'paid'                => $this->input->post('booking_price'),
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'module_type'         => 'property',
                'buy_status'            => 'booking',
                'expired_paid' => date("Y-m-d", strtotime($this->input->post('expiry_date'))),
            ];
            $data = array(
                'product_id' => $product_id,
                'product_code' => $product->code,
                'product_name' => $product->name,
                'current_date' => date("Y-m-d", strtotime($this->input->post('current_date'))),
                'expiry_date' => date("Y-m-d", strtotime($this->input->post('expiry_date'))),
                'booking_price' => $this->input->post('booking_price'),
                'create_by' => $this->input->post('create_by'),
                'note' => $this->input->post('note'),
                'customer' => $customer_id,
                'customer_name' => $customer->name . " ". $customer->company,
            );
        } elseif ($this->input->post('booking')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("property");
        }
        if ($this->form_validation->run() == true && $this->products_model->setBooking($data) && $this->sales_model->addSale($data, $products, $payment)) {
            if($this->products_model->UpdateProductBYID($product_id)){
                $this->session->set_flashdata('message', lang("product_has_been_booking"));
                admin_redirect("property");
            };
           
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product'] = $this->site->getProductByID($product_id);
            $this->data['users'] = $this->site->getAllUsers();
            $this->data['customers'] = $this->site->getAllCompanies('customer');
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'property/products/book', $this->data);
        }
        }else{
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
                 $this->session->set_flashdata('message', lang("product_has_been_booking"));
        exit();
        }
    
    }
    function realising($id = NULL)
    {
        $this->bpas->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
         $qty = $this->products_model->getProductByID($id);
        if($qty->quantity != -2){
           if($this->input->is_ajax_request()) {
                $this->bpas->send_json(array('error' => 0, 'msg' => lang("could_not_realise")));
            }
            $this->session->set_flashdata('message', lang('could_not_realis'));
            admin_redirect('welcome');
        }
        if ($datas = $this->products_model->realiseProduct($id)) {
            $data = array(
		                'id'      	        => $datas->id,
		        	    'product_id'      	=> $datas->product_id,
		        	    'current_date'      => $datas->current_date,
		        	    'expiry_date'       => $datas->expiry_date,
		        	    'create_by'   	    => $datas->create_by,
		        	    'note'              => $datas->note,
                        'status'      	    => 0,
                        'product_name'      => $datas->product_name,
                        'product_code'      => $datas->product_code,
                        'realise_by'        => $this->session->userdata('user_id'),
                        );
            $this->db->insert('audit_blocking', $data);
            $this->db->delete('blocking', ['product_id' => $id]);

            if($this->input->is_ajax_request()) {
                $this->bpas->send_json(array('error' => 0, 'msg' => lang("has_been_realised")));
            }
            $this->session->set_flashdata('message', lang('has_been_realised'));
            admin_redirect('welcome');
        }
    }
    function expiry_realise()
    {
        $realises = $this->products_model->getAllBlock();
        if($realises){
            foreach($realises as $value){
                var_dump("relise");
                var_dump(date("Y-m-d H:i:s"));
                var_dump($value->expiry_date);
                if($value->expiry_date < date("Y-m-d H:i:s") && $value->status == 1){
                    $datas = $this->products_model->realiseProduct($value->product_id);
                    $data = array(
		                'id'      	        => $datas->id,
		        	    'product_id'      	=> $datas->product_id,
		        	    'current_date'      => $datas->current_date,
		        	    'expiry_date'       => $datas->expiry_date,
                        'create_by'   	    => $datas->create_by,
		        	    'note'              => $datas->note,
                        'status'      	    => $datas->status,
                        'product_name'      => $datas->product_name,
		        	    'product_code'      => $datas->product_code,
                        );
                    $this->db->insert('audit_blocking', $data);
                    $this->db->delete('blocking', ['product_id' => $value->product_id]);
                }
            }
        }
        $deadline = $this->site->getAllSales();
        var_dump($deadline);
        if($deadline){
         foreach($deadline as $value){
             var_dump(date("Y-m-d H:i:s"));
                var_dump($value->expired_paid);
             $item = $this->site->getItemBySaleID($value->id);
                    // if($value->grand_total == $value->paid){
                    //     $this->db->update('products', ['quantity' => -1], ['id' => $item->product_id]);
                    // }
             if($value->expired_paid){
                    $pdata = $this->site->getProductByID($item->product_id);
                if($value->expired_paid < date("Y-m-d H:i:s") && $pdata->quantity != -1){
                     
                       
                    $this->products_model->unBookingProduct($item->product_id);
                    $data = ['date' => $value->date,
                            'product_id'      	=> $item->product_id,
                            'product_name'      => $item->product_name,
                            'product_code'      => $item->product_code,
                            'project_id'        => $value->project_id,
                            'reference_no'        => $value->reference_no,
                            'customer_id'         => $value->customer_id,
                            'customer'            => $value->customer,
                            'biller_id'           => $value->biller_id,
                            'biller'              => $value->biller,
                            'warehouse_id'        => $value->warehouse_id,
                            'note'                => $value->note,
                            'staff_note'          =>$value->staff_note,
                            'total'               => $value->total,
                            'product_discount'    => $value->product_discount,
                            'order_discount_id'   => $value->order_discount_id,
                            'order_discount'      => $value->order_discount,
                            'total_discount'      => $value->total_discount,
                            'product_tax'         => $value->product_tax,
                            'order_tax_id'        => $value->order_tax_id,
                            'order_tax'           => $value->order_tax,
                            'total_tax'           => $value->total_tax,
                            'shipping'            => $value->shipping,
                            'grand_total'         => $value->grand_total,
                            'total_items'         => $value->total_items,
                            'sale_status'         => $value->sale_status,
                            'payment_status'      => $value->payment_status,
                            'payment_term'        => $value->payment_term,
                            'due_date'            => $value->due_date,
                            'paid'                => $value->paid,
                            'created_by'          => $value->created_by,
                            'hash'                => $value->hash,
                            'module_type'         => $value->module_type,
                            'buy_status'            => $value->buy_status,
                            'astatus'            => 1,
                            'expired_paid'      => $value->expired_paid,
                            ];
                    $this->db->insert('audit_booking', $data);
                    if($this->db->delete('sales', ['id'=>$value->id])){
                        $this->db->delete('sale_items',['sale_id'=>$value->id]);
                    }
                    // $this->db->delete('booking', ['product_id' => $value->product_id]);
                    // $this->products_model->deleteBookingProperty($value->product_id);
                }

            }
        }
    }
        return true;

    }
    function blocking($product_id = NULL, $warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->form_validation->set_rules('current_date', lang("current_date"), 'trim|required');
         $this->form_validation->set_rules('expiry_date', lang("expiry_date"), 'trim|required');
         $this->form_validation->set_rules('create_by', lang("create_by"), 'trim|required');


        $qty = $this->products_model->getProductByID($product_id);
        if($qty->quantity != 1){
             $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $booking = $this->products_model->getBlocking($product_id);
     
        if (!$this->session->userdata('view_right')) {
            // $this->bpas->view_rights($booking->booking_by, true);
        }
        // $this->data['customer']  = $this->site->getCompanyByID($booking->booker);
        $this->data['booking']   = $booking;
        $this->data['created_by']= $this->site->getUser($booking->block_by); 
        $this->load->view($this->theme . 'property/products/block_detail_report', $this->data);
        }else{
        if ($this->form_validation->run() == true) {
            $product = $this->site->getProductByID($product_id);
            $date = $this->input->post('expiry_date');
            $date = date("Y-m-d H:i:s",strtotime(str_replace('/','-',$date)));
            $data = array( 
                'product_id'    => $product_id,
                'current_date'  => date("Y-m-d H:i:s"),
                'expiry_date'   => $date,
                'create_by'     => $this->input->post('create_by'),
                'note'          => $this->input->post('note'),
                'product_code'  => $product->code,
                'product_name'  => $product->name,
            );
        } elseif ($this->input->post('block')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect("products");
        }

        if ($this->form_validation->run() == true && $this->products_model->setBlocking($data)) {
           if($this->products_model->BlockProductBYID($product_id)){
                $this->session->set_flashdata('message', lang("product_has_been_blocking"));
                admin_redirect("property");
            }
        } else {
            $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['product'] = $this->site->getProductByID($product_id);
            $this->data['users'] = $this->site->getAllUsers();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'property/products/block', $this->data);

        }
    }
    }
    // function booking($product_id = NULL, $warehouse_id = NULL)
    // {
    //     $this->bpas->checkPermissions('edit', true);
    //     $this->form_validation->set_rules('expiry_date', lang("expiry_date"), 'required');
    //     $this->form_validation->set_rules('booking_price', lang("booking_price"), 'required');
    //     $qty = $this->products_model->getProductByID($product_id);
    //     if($qty->quantity == 2){
    //     $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
    //     $booking = $this->products_model->getBooking($product_id);
       
    //     if (!$this->session->userdata('view_right')) {
    //         $this->bpas->view_rights($booking->booking_by, true);
    //     }
    //     $this->data['customer']  = $this->site->getCompanyByID($booking->booker);
    //     $this->data['booking']   = $booking;
    //     $this->data['created_by']= $this->site->getUser($booking->booking_by); 
    //     $this->load->view($this->theme . 'property/products/book_detail', $this->data);
    //     }else if($qty->quantity == 1){
    //     if ($this->form_validation->run() == true) {
    //         $customer_id = $this->input->post('customer');
    //         $customer = $this->site->getCompanyByID($customer_id);
    //         $product = $this->site->getProductByID($product_id);
    //         $payment = [];
    //         $products = [
    //                     'product_id'        => $product->id,
    //                     'product_code'      => $product->code,
    //                     'option_id'      => $product->option_id,
    //                     'product_name'      => $product->name,
    //                     'product_type'      => $product->type,
    //                     'net_unit_price'    => $product->price,
    //                     'unit_price'        => $product->price,
    //                     'quantity'          => 1,
    //                     'unit_quantity'     => 1,
    //                     'warehouse_id'      => $product->warehouse,
                        
    //                     'subtotal'          => $product->price,
    //                     'serial_no'         => $product->serial_no,
    //                     'max_serial'         => $product->max_serial,
    //                     'real_unit_price'   => $product->price,
    //                 ];
    //         $data = ['date' => date("Y-m-d", strtotime($this->input->post('current_date'))),
    //             'project_id'        =>  $product->project_id,
    //             'product_id' => $product->id,
    //             'reference_no'        => '$reference',
    //             'customer_id'         => $customer_id,
    //             'customer'            => $customer->name . " ". $customer->company,
    //             'biller_id'           => 1,
    //             'biller'              => 1,
    //             'note'                => $this->input->post('note'),
    //             'total'               => $product->price,
    //             'grand_total'         => $product->price,
    //             'total_items'         => 1,
    //             'sale_status'         => 'booking',
    //             'payment_status'      => "due",
    //             'paid'                => $this->input->post('booking_price'),
    //             'created_by'          => $this->session->userdata('user_id'),
    //             'hash'                => hash('sha256', microtime() . mt_rand()),
    //             'module_type'         => 'property',
    //             'buy_status'            => 'booking',
    //             'expired_paid' => date("Y-m-d", strtotime($this->input->post('expiry_date'))),
    //         ];
    //         $data = array(
    //             'product_id' => $product_id,
    //             'product_code' => $product->code,
    //             'product_name' => $product->name,
    //             'current_date' => date("Y-m-d", strtotime($this->input->post('current_date'))),
    //             'expiry_date' => date("Y-m-d", strtotime($this->input->post('expiry_date'))),
    //             'booking_price' => $this->input->post('booking_price'),
    //             'create_by' => $this->input->post('create_by'),
    //             'note' => $this->input->post('note'),
    //             'customer' => $customer_id,
    //             'customer_name' => $customer->name . " ". $customer->company,
    //         );
    //     } elseif ($this->input->post('booking')) {
    //         $this->session->set_flashdata('error', validation_errors());
    //         admin_redirect("property");
    //     }
    //     if ($this->form_validation->run() == true && $this->products_model->setBooking($data) && $this->sales_model->addSale($data, $products, $payment)) {
    //         if($this->products_model->UpdateProductBYID($product_id)){
    //             $this->session->set_flashdata('message', lang("product_has_been_booking"));
    //             admin_redirect("property");
    //         };
           
    //     } else {
    //         $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
    //         $this->data['warehouse_id'] = $warehouse_id;
    //         $this->data['product'] = $this->site->getProductByID($product_id);
    //         $this->data['users'] = $this->site->getAllUsers();
    //         $this->data['customers'] = $this->site->getAllCompanies('customer');
    //         $this->data['modal_js'] = $this->site->modal_js();
    //         $this->load->view($this->theme . 'property/products/book', $this->data);
    //     }
    //     }else{
    //         $this->session->set_flashdata('error', lang('sale_x_action'));
    //         admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
    //              $this->session->set_flashdata('message', lang("product_has_been_booking"));
    //     exit();
    //     }
    
    // }
    // function realising($id = NULL)
    // {
    //     $this->bpas->checkPermissions(NULL, TRUE);

    //     if ($this->input->get('id')) {
    //         $id = $this->input->get('id');
    //     }
    //      $qty = $this->products_model->getProductByID($id);
    //     if($qty->quantity == 0){
    //        if($this->input->is_ajax_request()) {
    //             $this->bpas->send_json(array('error' => 0, 'msg' => lang("could_not_realised_have_booking")));
    //         }
    //         $this->session->set_flashdata('message', lang('could_not_realised_have_booking'));
    //         admin_redirect('welcome');
    //     }
    //     if($qty->quantity == 2){
    //         if($this->input->is_ajax_request()) {
    //             $this->bpas->send_json(array('error' => 0, 'msg' => lang("could_not_realised_have_booking")));
    //         }
    //         $this->session->set_flashdata('message', lang('could_not_realised_have_booking'));
    //         admin_redirect('welcome');
    //     }elseif($qty->quantity == 1){
    //         if($this->input->is_ajax_request()) {
    //             $this->bpas->send_json(array('error' => 0, 'msg' => lang("product_is_availabled")));
    //         }
    //         $this->session->set_flashdata('message', lang('product_is_availabled'));
    //         admin_redirect('welcome');
    //     }else{
    //     if ($datas = $this->products_model->realiseProduct($id)) {
    //         $data = array(
	// 	                'id'      	        => $datas->id,
	// 	        	    'product_id'      	=> $datas->product_id,
	// 	        	    'current_date'      => $datas->current_date,
	// 	        	    'expiry_date'       => $datas->expiry_date,
	// 	        	    'create_by'   	    => $datas->create_by,
	// 	        	    'note'              => $datas->note,
    //                     'status'      	    => 0,
    //                     'product_name'      => $datas->product_name,
    //                     'product_code'      => $datas->product_code,
    //                     'realise_by'        => $this->session->userdata('user_id'),
    //                     );
    //         $this->db->insert('audit_blocking', $data);
    //         $this->db->delete('blocking', ['product_id' => $id]);

    //         if($this->input->is_ajax_request()) {
    //             $this->bpas->send_json(array('error' => 0, 'msg' => lang("has_been_realised")));
    //         }
    //         $this->session->set_flashdata('message', lang('has_been_realised'));
    //         admin_redirect('welcome');
    //     }
    // }
    // }
    // function expiry_realise()
    // {
    //     $realises = $this->products_model->getAllBlock();
    //         foreach($realises as $value){
    //             if($value->expiry_date < date("Y-d-m") && $value->status == 1){
    //                 $datas = $this->products_model->realiseProduct($value->product_id);

    //                 $data = array(
	// 	                'id'      	        => $datas->id,
	// 	        	    'product_id'      	=> $datas->product_id,
	// 	        	    'current_date'      => $datas->current_date,
	// 	        	    'expiry_date'       => $datas->expiry_date,
	// 	        	    'create_by'   	    => $datas->create_by,
	// 	        	    'note'              => $datas->note,
    //                     'status'      	    => $datas->status,
    //                     'product_name'      => $datas->product_name,
	// 	        	    'product_code'      => $datas->product_code,
    //                     );
    //                 $this->db->insert('audit_blocking', $data);
    //                 $this->db->delete('blocking', ['product_id' => $value->product_id]);
    //                 // $this->products_model->deleteBlockProperty($value->product_id);
    //             }
    //         }
    //     $deadline = $this->products_model->getAllBooking();
    //      foreach($deadline as $value){
    //             if($value->expiry_date < date("Y-d-m") && $value->status == 1){
    //                 $datas = $this->products_model->unBookingProduct($value->product_id);
    //                 $data = array(
	// 	                        'id'      	        => $datas->id,
	// 	        	            'product_id'      	=> $datas->product_id,
	// 	        	            'current_date'      => $datas->current_date,
	// 	        	            'expiry_date'       => $datas->expiry_date,
	// 	        	            'create_by'   	    => $datas->create_by,
	// 	        	            'booking_price'     => $datas->booking_price,
	// 	        	            'note'              => $datas->note,
	// 	        	            'customer'          => $datas->customer,
    //                             'status'      	    => $datas->status,
    //                             'product_name'      => $datas->product_name,
	// 	        	            'customer_name'     => $datas->customer_name,
	// 	        	            'product_code'      => $datas->product_code,
    //                     );
    //                 $this->db->insert('audit_booking',$data);
    //                 $this->db->delete('booking', ['product_id' => $value->product_id]);
    //                 // $this->products_model->deleteBookingProperty($value->product_id);
    //             }
    //         }
    //     return true;

    // }
    // function blocking($product_id = NULL, $warehouse_id = NULL)
    // {
    //     $this->bpas->checkPermissions('edit', true);
    //     $this->form_validation->set_rules('current_date', lang("current_date"), 'trim|required');
    //      $this->form_validation->set_rules('expiry_date', lang("expiry_date"), 'trim|required');
    //     // $qty = $this->products_model->getProductByID($product_id);
    //     // if($qty->quantity == 0){
    //     //      $this->session->set_flashdata('error', lang('sale_x_action'));
    //     //     admin_redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
    //     //          $this->session->set_flashdata('message', lang("total"));
    //     // }
    //     if ($this->form_validation->run() == true) {
    //         $product = $this->site->getProductByID($product_id);
    //         $data = array( 
    //             'product_id' => $product_id,
    //             'current_date' => date("Y-m-d", strtotime($this->input->post('current_date'))),
    //             'expiry_date' => date("Y-m-d", strtotime($this->input->post('expiry_date'))),
    //             'create_by' => $this->input->post('create_by'),
    //             'note' => $this->input->post('note'),
    //             'product_code' => $product->code,
    //             'product_name' => $product->name,
    //         );
    //     } elseif ($this->input->post('block')) {
    //         $this->session->set_flashdata('error', validation_errors());
    //         admin_redirect("products");
    //     }

    //     if ($this->form_validation->run() == true && $this->products_model->setBlocking($data)) {
    //        if($this->products_model->BlockProductBYID($product_id)){
    //             $this->session->set_flashdata('message', lang("product_has_beeaan_blocking"));
    //             admin_redirect("property");
    //         }
    //     } else {
    //         $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
    //         $this->data['warehouse_id'] = $warehouse_id;
    //         $this->data['product'] = $this->site->getProductByID($product_id);
    //         $this->data['users'] = $this->site->getAllUsers();
    //         $this->data['modal_js'] = $this->site->modal_js();
    //         $this->load->view($this->theme . 'property/products/block', $this->data);

    //     }
    // }


    /* ------------------------------------------------------- */

    function add($id = NULL,$para=null)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        $warehouses = $this->site->getAllWarehouses();
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard' || $this->input->post('type') == 'fabric' || $this->input->post('type') == 'accessories') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
        }
        $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]|alpha_dash');
    //    $this->form_validation->set_rules('slug', lang("slug"), 'alpha_dash');
      //  $this->form_validation->set_rules('weight', lang("weight"), 'numeric');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $tax_rate = $this->input->post('tax_rate') ? $this->site->getTaxRateByID($this->input->post('tax_rate')) : NULL;
            $data = array(
                'code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->bpas->formatDecimal($this->input->post('cost')),
                'price' => $this->bpas->formatDecimal($this->input->post('price')),
                'module_type' => 'property',
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'quantity' => '1',
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '1',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->bpas->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->bpas->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->bpas->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->bpas->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->bpas->formatDecimal($this->input->post('supplier_5_price')),
                'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->bpas->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'file' => $this->input->post('file_link'),
                'slug' => $this->input->post('slug'),
             //   'slug' => $this->bpas->slug($this->input->post('name')),
                'weight' => $this->input->post('weight'),
                'featured' => $this->input->post('featured'),
                'hsn_code' => $this->input->post('hsn_code'),
                'hide' => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'second_name' => $this->input->post('second_name'),
                'project_id' => $this->input->post('project'),
                'status' => $this->input->post('status'),
            );
            $warehouse_qty = NULL;
            $product_attributes = NULL;
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                $wh_total_quantity = 0;
                $pv_total_quantity = 0;
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_qty_' . $warehouse->id)) {
                        $warehouse_qty[] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id),
                            'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                        );
                        $wh_total_quantity += $this->input->post('wh_qty_' . $warehouse->id);
                    }
                }

                if ($this->input->post('attributes')) {
                    $a = sizeof($_POST['attr_name']);
                    for ($r = 0; $r <= $a; $r++) {
                        if (isset($_POST['attr_name'][$r])) {
                            $product_attributes[] = array(
                                'name' => $_POST['attr_name'][$r],
                                'warehouse_id' => $_POST['attr_warehouse'][$r],
                                'quantity' => $_POST['attr_quantity'][$r],
                                'price' => $_POST['attr_price'][$r],
                            );
                            $pv_total_quantity += $_POST['attr_quantity'][$r];
                        }
                    }

                } else {
                    $product_attributes = NULL;
                }

                if ($wh_total_quantity != $pv_total_quantity && $pv_total_quantity != 0) {
                    $this->form_validation->set_rules('wh_pr_qty_issue', 'wh_pr_qty_issue', 'required');
                    $this->form_validation->set_message('required', lang('wh_pr_qty_issue'));
                }
            }
            if (!isset($items)) {
                $items = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['max_filename'] = 25;
                $config['encrypt_name'] = TRUE;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/add");
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
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
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/add");
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            
            $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 1;
            if($wh_total_quantity == 0)
            {
               $data['quantity'] = 1; 
            }
            // $this->bpas->print_arrays($data, $warehouse_qty, $product_attributes);
        }
       
        if ($this->form_validation->run() == true && $this->products_model->addProperty($data, $items, $warehouse_qty, $product_attributes, $photos)) {
            $this->session->set_flashdata('message', lang("product_added"));
            admin_redirect('property');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currencies'] = $this->bpas->getAllCurrencies();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $id ? $this->products_model->getAllWarehousesWithPQ($id) : NULL;
            $this->data['product'] = $id ? $this->products_model->getProductByID($id) : NULL;
            $this->data['para'] = $para ? $para : NULL;
            $this->data['slug'] = '';//$this->site->getReference('slug');
            $this->data['projects']         = $this->site->getAllProject();
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['combo_items'] = ($id && $this->data['product']->type == 'combo') ? $this->products_model->getProductComboItems($id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('property')), array('link' => '#', 'page' => lang('add_property')));
            $meta = array('page_title' => lang('add_property'), 'bc' => $bc);

            $this->data['exchange_rate_khm'] = $this->bpas->getExchange_rate('KHM');
            $this->data['exchange_rate_bat'] = $this->bpas->getExchange_rate('BAT');
            $this->page_construct('property/products/add', $meta, $this->data);
        }
    }

    function suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1);
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function get_suggestions()
    {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $rows = $this->products_model->getProductsForPrinting($term);
        if ($rows) {
            foreach ($rows as $row) {
                $variants = $this->products_model->getProductOptions($row->id);
                $pr[] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1, 'variants' => $variants);
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    function addByAjax()
    {
        if (!$this->mPermissions('add')) {
            exit(json_encode(array('msg' => lang('access_denied'))));
        }
        if ($this->input->get('token') && $this->input->get('token') == $this->session->userdata('user_csrf') && $this->input->is_ajax_request()) {
            $product = $this->input->get('product');
            if (!isset($product['code']) || empty($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_is_required'))));
            }
            if (!isset($product['name']) || empty($product['name'])) {
                exit(json_encode(array('msg' => lang('product_name_is_required'))));
            }
            if (!isset($product['category_id']) || empty($product['category_id'])) {
                exit(json_encode(array('msg' => lang('product_category_is_required'))));
            }
            if (!isset($product['unit']) || empty($product['unit'])) {
                exit(json_encode(array('msg' => lang('product_unit_is_required'))));
            }
            if (!isset($product['price']) || empty($product['price'])) {
                exit(json_encode(array('msg' => lang('product_price_is_required'))));
            }
            if (!isset($product['cost']) || empty($product['cost'])) {
                exit(json_encode(array('msg' => lang('product_cost_is_required'))));
            }
            if ($this->products_model->getProductByCode($product['code'])) {
                exit(json_encode(array('msg' => lang('product_code_already_exist'))));
            }
            if ($row = $this->products_model->addAjaxProduct($product)) {
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $pr = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'qty' => 1, 'cost' => $row->cost, 'name' => $row->name, 'tax_method' => $row->tax_method, 'tax_rate' => $tax_rate, 'discount' => '0');
                $this->bpas->send_json(array('msg' => 'success', 'result' => $pr));
            } else {
                exit(json_encode(array('msg' => lang('failed_to_add_product'))));
            }
        } else {
            json_encode(array('msg' => 'Invalid token'));
        }

    }


    /* -------------------------------------------------------- */
    
    function edit($id = NULL)
    {
        $this->bpas->checkPermissions();
        $this->load->helper('security');
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        $warehouses = $this->site->getAllWarehouses();
        $warehouses_products = $this->products_model->getAllWarehousesWithPQ($id);
        $product = $this->site->getProductByID($id);
        if (!$id || !$product) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->form_validation->set_rules('category', lang("category"), 'required|is_natural_no_zero');
        if ($this->input->post('type') == 'standard') {
            $this->form_validation->set_rules('cost', lang("product_cost"), 'required');
            $this->form_validation->set_rules('unit', lang("product_unit"), 'required');
        }
        $this->form_validation->set_rules('code', lang("product_code"), 'alpha_dash');
        if ($this->input->post('code') !== $product->code) {
            $this->form_validation->set_rules('code', lang("product_code"), 'is_unique[products.code]');
        }
       
        $this->form_validation->set_rules('weight', lang("weight"), 'numeric');
        $this->form_validation->set_rules('product_image', lang("product_image"), 'xss_clean');
        $this->form_validation->set_rules('digital_file', lang("digital_file"), 'xss_clean');
        $this->form_validation->set_rules('userfile', lang("product_gallery_images"), 'xss_clean');

        if ($this->form_validation->run('products/add') == true) {
            $data = array('code' => $this->input->post('code'),
                'barcode_symbology' => $this->input->post('barcode_symbology'),
                'name' => $this->input->post('name'),
                'type' => $this->input->post('type'),
                'brand' => $this->input->post('brand'),
                'category_id' => $this->input->post('category'),
                'subcategory_id' => $this->input->post('subcategory') ? $this->input->post('subcategory') : NULL,
                'cost' => $this->bpas->formatDecimal($this->input->post('cost')),
                'price' => $this->bpas->formatDecimal($this->input->post('price')),
                'currency'    => $this->input->post('currency'),
                'unit' => $this->input->post('unit'),
                'sale_unit' => $this->input->post('default_sale_unit'),
                'purchase_unit' => $this->input->post('default_purchase_unit'),
                'tax_rate' => $this->input->post('tax_rate'),
                'tax_method' => $this->input->post('tax_method'),
                'alert_quantity' => $this->input->post('alert_quantity'),
                'track_quantity' => $this->input->post('track_quantity') ? $this->input->post('track_quantity') : '0',
                'details' => $this->input->post('details'),
                'product_details' => $this->input->post('product_details'),
                'supplier1' => $this->input->post('supplier'),
                'supplier1price' => $this->bpas->formatDecimal($this->input->post('supplier_price')),
                'supplier2' => $this->input->post('supplier_2'),
                'supplier2price' => $this->bpas->formatDecimal($this->input->post('supplier_2_price')),
                'supplier3' => $this->input->post('supplier_3'),
                'supplier3price' => $this->bpas->formatDecimal($this->input->post('supplier_3_price')),
                'supplier4' => $this->input->post('supplier_4'),
                'supplier4price' => $this->bpas->formatDecimal($this->input->post('supplier_4_price')),
                'supplier5' => $this->input->post('supplier_5'),
                'supplier5price' => $this->bpas->formatDecimal($this->input->post('supplier_5_price')),
				'cf1' => $this->input->post('cf1'),
                'cf2' => $this->input->post('cf2'),
                'cf3' => $this->input->post('cf3'),
                'cf4' => $this->input->post('cf4'),
                'cf5' => $this->input->post('cf5'),
                'cf6' => $this->input->post('cf6'),
                'promotion' => $this->input->post('promotion'),
                'promo_price' => $this->bpas->formatDecimal($this->input->post('promo_price')),
                'start_date' => $this->input->post('start_date') ? $this->bpas->fsd($this->input->post('start_date')) : NULL,
                'end_date' => $this->input->post('end_date') ? $this->bpas->fsd($this->input->post('end_date')) : NULL,
                'supplier1_part_no' => $this->input->post('supplier_part_no'),
                'supplier2_part_no' => $this->input->post('supplier_2_part_no'),
                'supplier3_part_no' => $this->input->post('supplier_3_part_no'),
                'supplier4_part_no' => $this->input->post('supplier_4_part_no'),
                'supplier5_part_no' => $this->input->post('supplier_5_part_no'),
                'slug' => $this->input->post('slug'),
				//'slug' => $this->bpas->slug($this->input->post('name')),
                'weight' => $this->input->post('weight'),
                'featured' => $this->input->post('featured'),
                'hsn_code' => $this->input->post('hsn_code'),
                'hide' => $this->input->post('hide') ? $this->input->post('hide') : 0,
                'second_name' => $this->input->post('second_name'),
                'status' => $this->input->post('status'),
            );
            $warehouse_qty = NULL;
            $product_attributes = NULL;
            $update_variants = array();
            $this->load->library('upload');
            if ($this->input->post('type') == 'standard') {
                if ($product_variants = $this->products_model->getProductOptions($id)) {
                    foreach ($product_variants as $pv) {
                        $update_variants[] = array(
                            'id' => $this->input->post('variant_id_'.$pv->id),
                            'name' => $this->input->post('variant_name_'.$pv->id),
                            'cost' => $this->input->post('variant_cost_'.$pv->id),
                            'price' => $this->input->post('variant_price_'.$pv->id),
                        );
                    }
                }
                for ($s = 2; $s > 5; $s++) {
                    $data['suppliers' . $s] = $this->input->post('supplier_' . $s);
                    $data['suppliers' . $s . 'price'] = $this->input->post('supplier_' . $s . '_price');
                }
                foreach ($warehouses as $warehouse) {
                    $warehouse_qty[] = array(
                        'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                        'rack' => $this->input->post('rack_' . $warehouse->id) ? $this->input->post('rack_' . $warehouse->id) : NULL
                    );
                }

                
                    $product_attributes = NULL;
                

            }

            if (!isset($items)) {
                $items = NULL;
            }
            if ($_FILES['product_image']['size'] > 0) {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('product_image')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/edit/" . $id);
                }
                $photo = $this->upload->file_name;
                $data['image'] = $photo;
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
                if ($this->Settings->watermark) {
                    $this->image_lib->clear();
                    $wm['source_image'] = $this->upload_path . $photo;
                    $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                    $wm['wm_type'] = 'text';
                    $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                    $wm['quality'] = '100';
                    $wm['wm_font_size'] = '16';
                    $wm['wm_font_color'] = '999999';
                    $wm['wm_shadow_color'] = 'CCCCCC';
                    $wm['wm_vrt_alignment'] = 'top';
                    $wm['wm_hor_alignment'] = 'left';
                    $wm['wm_padding'] = '10';
                    $this->image_lib->initialize($wm);
                    $this->image_lib->watermark();
                }
                $this->image_lib->clear();
                $config = NULL;
            }

            if ($_FILES['userfile']['name'][0] != "") {

                $config['upload_path'] = $this->upload_path;
                $config['allowed_types'] = $this->image_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['max_width'] = $this->Settings->iwidth;
                $config['max_height'] = $this->Settings->iheight;
                $config['overwrite'] = FALSE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $files = $_FILES;
                $cpt = count($_FILES['userfile']['name']);
                for ($i = 0; $i < $cpt; $i++) {

                    $_FILES['userfile']['name'] = $files['userfile']['name'][$i];
                    $_FILES['userfile']['type'] = $files['userfile']['type'][$i];
                    $_FILES['userfile']['tmp_name'] = $files['userfile']['tmp_name'][$i];
                    $_FILES['userfile']['error'] = $files['userfile']['error'][$i];
                    $_FILES['userfile']['size'] = $files['userfile']['size'][$i];

                    $this->upload->initialize($config);

                    if (!$this->upload->do_upload()) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        admin_redirect("products/edit/" . $id);
                    } else {

                        $pho = $this->upload->file_name;

                        $photos[] = $pho;

                        $this->load->library('image_lib');
                        $config['image_library'] = 'gd2';
                        $config['source_image'] = $this->upload_path . $pho;
                        $config['new_image'] = $this->thumbs_path . $pho;
                        $config['maintain_ratio'] = TRUE;
                        $config['width'] = $this->Settings->twidth;
                        $config['height'] = $this->Settings->theight;

                        $this->image_lib->initialize($config);

                        if (!$this->image_lib->resize()) {
                            echo $this->image_lib->display_errors();
                        }

                        if ($this->Settings->watermark) {
                            $this->image_lib->clear();
                            $wm['source_image'] = $this->upload_path . $pho;
                            $wm['wm_text'] = 'Copyright ' . date('Y') . ' - ' . $this->Settings->site_name;
                            $wm['wm_type'] = 'text';
                            $wm['wm_font_path'] = 'system/fonts/texb.ttf';
                            $wm['quality'] = '100';
                            $wm['wm_font_size'] = '16';
                            $wm['wm_font_color'] = '999999';
                            $wm['wm_shadow_color'] = 'CCCCCC';
                            $wm['wm_vrt_alignment'] = 'top';
                            $wm['wm_hor_alignment'] = 'left';
                            $wm['wm_padding'] = '10';
                            $this->image_lib->initialize($wm);
                            $this->image_lib->watermark();
                        }

                        $this->image_lib->clear();
                    }
                }
                $config = NULL;
            } else {
                $photos = NULL;
            }
            $data['quantity'] = isset($wh_total_quantity) ? $wh_total_quantity : 0;
            // $this->bpas->print_arrays($data, $warehouse_qty, $update_variants, $product_attributes, $photos, $items);
        }

        if ($this->form_validation->run() == true && $this->products_model->updateProperty($id, $data, $items, $warehouse_qty, $product_attributes, $photos, $update_variants)) {
            $this->session->set_flashdata('message', lang("product_updated"));
            admin_redirect('property');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['currencies'] = $this->bpas->getAllCurrencies();
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['brands'] = $this->site->getAllBrands();
            $this->data['base_units'] = $this->site->getAllBaseUnits();
            $this->data['warehouses'] = $warehouses;
            $this->data['warehouses_products'] = $warehouses_products;
            $this->data['product'] = $product;
            $this->data['variants'] = $this->products_model->getAllVariants();
            $this->data['subunits'] = $this->site->getUnitsByBUID($product->unit);
            $this->data['product_variants'] = $this->products_model->getProductOptions($id);
            $this->data['combo_items'] = $product->type == 'combo' ? $this->products_model->getProductComboItems($product->id) : NULL;
            $this->data['product_options'] = $id ? $this->products_model->getProductOptionsWithWH($id) : NULL;
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('property')), array('link' => '#', 'page' => lang('edit_property')));
            $meta = array('page_title' => lang('edit_property'), 'bc' => $bc);
            
			$this->data['exchange_rate_khm'] = $this->bpas->getExchange_rate('KHM');
            $this->data['exchange_rate_bat'] = $this->bpas->getExchange_rate('BAT');
			$this->page_construct('property/products/edit', $meta, $this->data);
        }
    }

    /* ---------------------------------------------------------------- */
    function import_excel(){
    
        $this->bpas->checkPermissions('import', null);
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');
        if ($this->form_validation->run() == true) {
        
        $this->load->library('excel');
        if(isset($_FILES["userfile"]["name"]))
        {
            $this->load->library('upload');
            $config['upload_path']   = $this->digital_upload_path;
            $config['allowed_types'] = ['csv','xls' , 'xlsx'];
            $config['max_size']      = $this->allowed_file_size;
            $config['overwrite']     = true;
            $config['encrypt_name']  = true;
            $config['max_filename']  = 25;
            $this->upload->initialize($config);

            if (!$this->upload->do_upload()) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                admin_redirect('property/import_excel');
            }

            $path = $_FILES["userfile"]["tmp_name"];
            $object = PHPExcel_IOFactory::load($path);
            if (!$object) {
                $error = $this->excel->display_errors();
                $this->session->set_flashdata('error', $error);
                admin_redirect("property/import_excel");
            }
            foreach($object->getWorksheetIterator() as $worksheet)
            {
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();

                $rw = 0; 
                $items = array();
                $existingPro = '';
                $failedImport = 0;
                $successImport = 0;
                for($row=2; $row<=$highestRow; $row++){    

                    $name = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                    $code = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                    $serial_no = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                    $max_serial = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                    $barcode_symbology = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                    $brand = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
                    $category_code = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
                    $unit= $worksheet->getCellByColumnAndRow(7, $row)->getValue();
                    $sale_units=$worksheet->getCellByColumnAndRow(8,$row)->getValue();
                    $purchase_unit = $worksheet->getCellByColumnAndRow(9, $row)->getValue();
                    $cost = $worksheet->getCellByColumnAndRow(10, $row)->getValue();
                    $price = $worksheet->getCellByColumnAndRow(11, $row)->getValue();
                    $alert_quantity = $worksheet->getCellByColumnAndRow(12, $row)->getValue();
                    $tax_rate = $worksheet->getCellByColumnAndRow(13, $row)->getValue();
                    $tax_method = $worksheet->getCellByColumnAndRow(14, $row)->getValue();
                    $image = $worksheet->getCellByColumnAndRow(15, $row)->getValue();
                    $subcategory_code = $worksheet->getCellByColumnAndRow(16, $row)->getValue();
                    $product_variants = $worksheet->getCellByColumnAndRow(17, $row)->getValue();
                    $cf1 = $worksheet->getCellByColumnAndRow(18, $row)->getValue();
                    $cf2 = $worksheet->getCellByColumnAndRow(19, $row)->getValue();
                    $cf3 = $worksheet->getCellByColumnAndRow(20, $row)->getValue();
                    $cf4 = $worksheet->getCellByColumnAndRow(21, $row)->getValue();
                    $cf5 = $worksheet->getCellByColumnAndRow(22, $row)->getValue();
                    $cf6 = $worksheet->getCellByColumnAndRow(23, $row)->getValue();
                    
                    /*--------------Checked products existing code---------------*/
                    
                    if ( !$this->products_model->getProductByCode(trim($code))){    
                        $successImport++;
                        
                        if ($catd = $this->products_model->getCategoryByCode(trim($category_code))) {
                            $brand = $this->products_model->getBrandByName(trim($brand));
                            $unit = $this->products_model->getUnitByCode(trim($unit));
                            $base_unit = $unit ? $unit->id : NULL;
                            $sale_unit = $base_unit;
                            $purcahse_unit = $base_unit;
                            if ($base_unit) {
                                $units = $this->site->getUnitsByBUID($base_unit);
                                foreach ($units as $u) {
                                    if ($u->code == trim($sale_units)) {
                                        $sale_unit = $u->id;
                                    }
                                    if ($u->code == trim($purchase_unit)) {
                                        $purcahse_unit = $u->id;
                                    }
                                }
                            } else {
                                $this->session->set_flashdata('error', lang("check_unit") . " (" . $unit . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("property/import_excel");
                            }
                            $tax_details = $this->products_model->getTaxRateByName(trim($tax_rate));
                            $prsubcat = $this->products_model->getCategoryByCode(trim($subcategory_code));
                            $items[] = array (
                                'code' => trim($code),
                                'name' => trim($name),
                                'serial_no' => trim($serial_no),
                                'category_id' => $catd->id,
                                'barcode_symbology' => mb_strtolower(trim($barcode_symbology), 'UTF-8'),
                                'brand' => ($brand ? $brand->id : NULL),
                                'unit' => $base_unit,
                                'sale_unit' => $sale_unit,
                                'purchase_unit' => $purcahse_unit,
                                'cost' => trim($cost),
                                'price' => trim($price),
                                'alert_quantity' => trim($alert_quantity),
                                'tax_rate' => ($tax_details ? $tax_details->id : NULL),
                                'tax_method' => ($tax_method == 'exclusive' ? 1 : 0),
                                'subcategory_id' => ($prsubcat ? $prsubcat->id : NULL),
                                'variants' => trim($product_variants),
                                'cf1' => trim($cf1),
                                'cf2' => trim($cf2),
                                'cf3' => trim($cf3),
                                'cf4' => trim($cf4),
                                'cf5' => trim($cf5),
                                'cf6' => trim($cf6),
                                'image' => trim($image),
                                'module_type' => 'property',
                                'quantity' => 1
                            );

                            }else{
                                $this->session->set_flashdata('error', lang("check_category_code") . " (" .$category_code . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("property/import_excel");
                            }
                        }else{
               /*-------------------updated items existing code-----------------*/
                         
                            $successImport++;
                            if ($catd = $this->products_model->getCategoryByCode(trim($category_code))) {
                                $brand = $this->products_model->getBrandByName(trim($brand));
                                $unit = $this->products_model->getUnitByCode(trim($unit));
                                $base_unit = $unit ? $unit->id : NULL;
                                $sale_unit = $base_unit;
                                $purcahse_unit = $base_unit;
                                if ($base_unit) {
                                    $units = $this->site->getUnitsByBUID($base_unit);
                                    foreach ($units as $u) {
                                        if ($u->code == trim($sale_units)) {
                                            $sale_unit = $u->id;
                                        }
                                        if ($u->code == trim($purchase_unit)) {
                                            $purcahse_unit = $u->id;
                                        }
                                    }
                                } else {
                                    $this->session->set_flashdata('error', lang("check_unit") . " (" . $unit . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                    admin_redirect("property/import_excel");
                                }
                                $tax_details = $this->products_model->getTaxRateByName(trim($tax_rate));
                                $prsubcat = $this->products_model->getCategoryByCode(trim($subcategory_code));
                                $items_update[] = array (
                                    'code' => trim($code),
                                    'name' => trim($name),
                                    'serial_no' => trim($serial_no),
                                    'category_id' => $catd->id,
                                    'barcode_symbology' => mb_strtolower(trim($barcode_symbology), 'UTF-8'),
                                    'brand' => ($brand ? $brand->id : NULL),
                                    'unit' => $base_unit,
                                    'sale_unit' => $sale_unit,
                                    'purchase_unit' => $purcahse_unit,
                                    'cost' => trim($cost),
                                    'price' => trim($price),
                                    'alert_quantity' => trim($alert_quantity),
                                    'tax_rate' => ($tax_details ? $tax_details->id : NULL),
                                    'tax_method' => ($tax_method == 'exclusive' ? 1 : 0),
                                    'subcategory_id' => ($prsubcat ? $prsubcat->id : NULL),
                                    'variants' => trim($product_variants),
                                    'cf1' => trim($cf1),
                                    'cf2' => trim($cf2),
                                    'cf3' => trim($cf3),
                                    'cf4' => trim($cf4),
                                    'cf5' => trim($cf5),
                                    'cf6' => trim($cf6),
                                    'image' => trim($image),
                                    'module_type' => 'property',
                                    'quantity' => 1
                                );
                            } else {
                                $this->session->set_flashdata('error', lang("check_category_code") . " (" . $category_code . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("property/import_excel");
                            }
                            $existingPro .= $code;
                            $failedImport++;
                           
                        }                              
                       $rw++;  
                      
                    }
             
                  
                }
                /*
                 * Finde number of add products
                 */
                $successImport1=$successImport-$failedImport;
            }
          
        }   
            /*
             ********** Add products ***********
             */
            if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items)) {
                $this->session->set_flashdata('message', sprintf($successImport . ' ' . lang("property_added") . '. ' . ($failedImport >= 1 ? $failedImport . ' already to updated' . $existingPro : ''), $successImport1));
                admin_redirect('property');
            } else {

                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

                $this->data['userfile'] = array('name' => 'userfile',
                    'id' => 'userfile',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('userfile')
                );

                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('property')), array('link' => '#', 'page' => lang('import_products_by_excel')));
                $meta = array('page_title' => lang('import_products_by_excel'), 'bc' => $bc);
                if(isset($existingPro)){
                    if($existingPro !== ''){
                        
                     $this->session->set_flashdata('error', 'Products already exist:' . $existingPro);
                    }
                
                }
                //    admin_redirect('products');        
                 $this->page_construct('property/products/import_excel', $meta, $this->data);

           
            }

    }
    function import_csv()
    {
     //   $this->bpas->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang("upload_file"), 'xss_clean');

        if ($this->form_validation->run() == true) {

            if (isset($_FILES["userfile"])) {

                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = TRUE;
                $config['encrypt_name'] = TRUE;
                $config['max_filename'] = 25;
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect("products/import_csv");
                }

                $csv = $this->upload->file_name;

                $arrResult = array();
                $handle = fopen($this->digital_upload_path . $csv, "r");
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ",")) !== FALSE) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $titles = array_shift($arrResult);
                $keys = array('name', 'code','property_type','unit','price','image','quantity');

                $final = array();
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                // $this->bpas->print_arrays($final);
                $rw = 0; 
				$items = array();
                foreach ($final as $csv_pr) {
                    if ( ! $this->products_model->getProductByCode(trim($csv_pr['code']))) {
                        if ($catd = $this->products_model->getCategoryByCode(trim($csv_pr['property_type']))) {
                            $unit = $this->products_model->getUnitByCode(trim($csv_pr['unit']));
                            $base_unit = $unit ? $unit->id : NULL;
                            $sale_unit = $base_unit;
                            $purcahse_unit = $base_unit;
                            if ($base_unit) {
                                $units = $this->site->getUnitsByBUID($base_unit);
                                foreach ($units as $u) {
                                    if ($u->code == trim($csv_pr['sale_unit'])) {
                                        $sale_unit = $u->id;
                                    }
                                    if ($u->code == trim($csv_pr['purchase_unit'])) {
                                        $purcahse_unit = $u->id;
                                    }
                                }
                            } else {
                                $this->session->set_flashdata('error', lang("check_unit") . " (" . $csv_pr['unit'] . "). " . lang("unit_code_x_exist") . " " . lang("line_no") . " " . $rw);
                                admin_redirect("products/import_csv");
                            }
                            $items[] = array (
                                'code' => trim($csv_pr['code']),
                                'name' => trim($csv_pr['name']),
                                'category_id' => $catd->id,
                                'barcode_symbology' => mb_strtolower(trim('code128'), 'UTF-8'),
                                'brand' =>  NULL,
                                'unit' => $base_unit,
                                'sale_unit' => $sale_unit,
                                'purchase_unit' => $purcahse_unit,
                                'cost' => 0,
                                'price' => trim($csv_pr['price']),
                                'alert_quantity' => 10,
                                'tax_rate' => NULL,
                                'tax_method' => 1,
								'subcategory_id' => NULL,
                                'image' => trim($csv_pr['image']),
                                'slug' => $this->bpas->slug(trim($csv_pr['name'])),
                                'hsn_code' => trim('HSN')
                                );
                        } else {
                            $this->session->set_flashdata('error', lang("check_category_code") . " (" . $csv_pr['category_code'] . "). " . lang("category_code_x_exist") . " " . lang("line_no") . " " . $rw);
                            admin_redirect("property/import_csv");
                        }
                    }

                    $rw++;
                }
            }
            // $this->bpas->print_arrays($items);
        }

        if ($this->form_validation->run() == true && $prs = $this->products_model->add_products($items)) {
            $this->session->set_flashdata('message', sprintf(lang("products_added"), $prs));
            admin_redirect('property');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['userfile'] = array('name' => 'userfile',
                'id' => 'userfile',
                'type' => 'text',
                'value' => $this->form_validation->set_value('userfile')
            );

            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('property'), 'page' => lang('property')), array('link' => '#', 'page' => lang('import_products_by_csv')));
            $meta = array('page_title' => lang('import_products_by_csv'), 'bc' => $bc);
            $this->page_construct('property/products/import_csv', $meta, $this->data);

        }
    }

    /* ------------------------------------------------------------------ */

 

    /* ------------------------------------------------------------------------------- */

    function delete($id = NULL)
    {
        $this->bpas->checkPermissions(NULL, TRUE);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->products_model->deleteProduct($id)) {
            if($this->input->is_ajax_request()) {
                $this->bpas->send_json(array('error' => 0, 'msg' => lang("product_deleted")));
            }
            $this->session->set_flashdata('message', lang('product_deleted'));
            admin_redirect('welcome');
        }

    }

    /* ----------------------------------------------------------------------------- */

    function quantity_adjustments($warehouse_id = NULL)
    {
        $this->bpas->checkPermissions('adjustments');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('property')), array('link' => '#', 'page' => lang('quantity_adjustments')));
        $meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->page_construct('products/quantity_adjustments', $meta, $this->data);
    }



    /* --------------------------------------------------------------------------------------------- */

    function modal_view($id = NULL)
    {
        $this->bpas->checkPermissions('index', TRUE);

        $pr_details = $this->site->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            $this->bpas->md();
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $this->load->view($this->theme.'products/modal_view', $this->data);
    }

    function view($id = NULL)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);
        $this->data['sold'] = $this->products_model->getSoldQty($id);
        $this->data['purchased'] = $this->products_model->getPurchasedQty($id);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('property')), array('link' => '#', 'page' => $pr_details->name));
        $meta = array('page_title' => $pr_details->name, 'bc' => $bc);
        $this->page_construct('products/view', $meta, $this->data);
    }

    function pdf($id = NULL, $view = NULL)
    {
        $this->bpas->checkPermissions('index');

        $pr_details = $this->products_model->getProductByID($id);
        if (!$id || !$pr_details) {
            $this->session->set_flashdata('error', lang('prduct_not_found'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $pr_details->code . '/' . $pr_details->barcode_symbology . '/40/0') . "' alt='" . $pr_details->code . "' class='pull-left' />";
        if ($pr_details->type == 'combo') {
            $this->data['combo_items'] = $this->products_model->getProductComboItems($id);
        }
        $this->data['product'] = $pr_details;
        $this->data['unit'] = $this->site->getUnitByID($pr_details->unit);
        $this->data['brand'] = $this->site->getBrandByID($pr_details->brand);
        $this->data['images'] = $this->products_model->getProductPhotos($id);
        $this->data['category'] = $this->site->getCategoryByID($pr_details->category_id);
        $this->data['subcategory'] = $pr_details->subcategory_id ? $this->site->getCategoryByID($pr_details->subcategory_id) : NULL;
        $this->data['tax_rate'] = $pr_details->tax_rate ? $this->site->getTaxRateByID($pr_details->tax_rate) : NULL;
        $this->data['popup_attributes'] = $this->popup_attributes;
        $this->data['warehouses'] = $this->products_model->getAllWarehousesWithPQ($id);
        $this->data['options'] = $this->products_model->getProductOptionsWithWH($id);
        $this->data['variants'] = $this->products_model->getProductOptions($id);

        $name = $pr_details->code . '_' . str_replace('/', '_', $pr_details->name) . ".pdf";
        if ($view) {
            $this->load->view($this->theme . 'products/pdf', $this->data);
        } else {
            $html = $this->load->view($this->theme . 'products/pdf', $this->data, TRUE);
            if (! $this->Settings->barcode_img) {
                $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
            }
            $this->bpas->generate_pdf($html, $name);
        }
    }

    function getSubCategories($category_id = NULL)
    {
        if ($rows = $this->products_model->getSubCategories($category_id)) {
            $data = json_encode($rows);
        } else {
            $data = false;
        }
        echo $data;
    }

    function product_actions($wh = NULL)
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        
        if ($this->form_validation->run() == true) {

            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'sync_quantity') {

                    foreach ($_POST['val'] as $id) {
                        $this->site->syncQuantity(NULL, NULL, NULL, $id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_quantity_sync"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'delete') {

                    $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->products_model->deleteProduct($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line("products_deleted"));
                    redirect($_SERVER["HTTP_REFERER"]);

                } elseif ($this->input->post('form_action') == 'labels') {

                    foreach ($_POST['val'] as $id) {
                        $row = $this->products_model->getProductByID($id);
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }

                    $this->data['items'] = isset($pr) ? json_encode($pr) : false;
                    $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                    $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('products'), 'page' => lang('property')), array('link' => '#', 'page' => lang('print_barcodes')));
                    $meta = array('page_title' => lang('print_barcodes'), 'bc' => $bc);
                    $this->page_construct('products/print_barcodes', $meta, $this->data);

                } elseif ($this->input->post('form_action') == 'export_excel') {

                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle('Products');
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('name'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('category_code'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('unit_code'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('price'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('image'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('pcf1'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('pcf2'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('pcf3'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('pcf4'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('pcf5'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('pcf6'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('quantity'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $product = $this->products_model->getProductDetail($id);
                        $brand = $this->site->getBrandByID($product->brand);
                        $base_unit = $sale_unit = $purchase_unit = '';
                        if($units = $this->site->getUnitsByBUID($product->unit)) {
                            foreach($units as $u) {
                                if ($u->id == $product->unit) {
                                    $base_unit = $u->code;
                                }
                                if ($u->id == $product->sale_unit) {
                                    $sale_unit = $u->code;
                                }
                                if ($u->id == $product->purchase_unit) {
                                    $purchase_unit = $u->code;
                                }
                            }
                        }
                        $variants = $this->products_model->getProductOptions($id);
                        $product_variants = '';
                        if ($variants) {
                            foreach ($variants as $variant) {
                                $product_variants .= trim($variant->name) . '|';
                            }
                        }
                        $quantity = $product->quantity;
                        if ($wh) {
                            if($wh_qty = $this->products_model->getProductQuantity($id, $wh)) {
                                $quantity = $wh_qty['quantity'];
                            } else {
                                $quantity = 0;
                            }
                        }
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $product->name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $product->code);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $product->category_code);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $base_unit);
						$this->excel->getActiveSheet()->SetCellValue('E' . $row, $product->price);
             
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $product->image);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $product->cf1);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $product->cf2);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $product->cf3);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $product->cf4);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $product->cf5);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, $product->cf6);
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, $quantity);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'properties_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);

                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line("no_product_selected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'admin/products');
        }
    }

    public function delete_image($id = NULL)
    {
        $this->bpas->checkPermissions('edit', true);
        if ($id && $this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $this->db->delete('product_photos', array('id' => $id));
            $this->bpas->send_json(array('error' => 0, 'msg' => lang("image_deleted")));
        }
        $this->bpas->send_json(array('error' => 1, 'msg' => lang("ajax_error")));
    }

    public function getSubUnits($unit_id)
    {
        // $unit = $this->site->getUnitByID($unit_id);
        // if ($units = $this->site->getUnitsByBUID($unit_id)) {
        //     array_push($units, $unit);
        // } else {
        //     $units = array($unit);
        // }
        $units = $this->site->getUnitsByBUID($unit_id);
        $this->bpas->send_json($units);
    }

    public function qa_suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->bpas->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $rows = $this->products_model->getQASuggestions($sr);
        if ($rows) {
            foreach ($rows as $row) {
                $row->qty = 1;
                $options = $this->products_model->getProductOptions($row->id);
                $row->option = $option_id;
                $row->serial = '';

                $pr[] = array('id' => str_replace(".", "", microtime(true)), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")",
                    'row' => $row, 'options' => $options);

            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    public function sales($warehouse_id = null){
        
        $this->bpas->checkPermissions("index");

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link'   => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('property_sales')));
        $meta = array('page_title' => lang('property_sales'), 'bc' => $bc);
        $this->page_construct('property/sales/index', $meta, $this->data);
    }

    public function getSales($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        $biller_id = $this->session->userdata('biller_id');
        $percent   = $this->input->get('percent') ? $this->input->get('percent') : NULL;
        if ($biller_id) {
            $get_sub_biller = $this->site->get_Sub_Biller($biller_id);
            $elements = array();
            if (!empty($get_sub_biller)) {
                foreach ($get_sub_biller as $sub_biller){
                    $elements[] =  $sub_biller->bill_id;
                }
            }
            $sub_bill=implode(' OR biller_id =', $elements);
        }
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link              = anchor('admin/sale_property/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $detail_link_kh           = anchor('admin/sale_property/view_kh/$1', '<i class="fa fa-file-text-o"></i> ' . lang('receipt'));
        $agreement_link           = anchor('admin/sale_property/view_baht/$1', '<i class="fa fa-file-text-o"></i> ' . lang('land_agreement_owner'));
        $agreement2_link          = anchor('admin/sale_property/view_baht2/$1', '<i class="fa fa-file-text-o"></i> ' . lang('land_agreement_seller'));
        $agreement_house_link     = anchor('admin/sale_property/house_agreement/$1', '<i class="fa fa-file-text-o"></i> ' . lang('house_agreement'));
        $attachment               = anchor('admin/sales/view_attachment/$1', '<i class="fa fa-file-text-o"></i> ' . "View Attachment", 'data-toggle="modal" data-target="#myModal"');
        $add_doc                  = anchor('admin/sales/add_attachment/$1', '<i class="fa fa-file-text-o"></i> ' . "Add Attachment", 'data-toggle="modal" data-target="#myModal"');
        $adp_agreement_no_stall   = anchor('admin/sales/view_adp_no_stall/$1', '<i class="fa fa-file-text-o"></i> ' . "Agreement");
        $duplicate_link           = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link            = anchor('admin/sale_property/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_commission           = anchor('admin/commission/add_commission/$1', '<i class="fa fa-plus-circle"></i> ' . lang('add_commission'), 'data-toggle="modal" data-target="#myModal"');
        $view_commission          = anchor('admin/commission/commissions/$1', '<i class="fa fa-plus-circle"></i> ' . lang('view_commission'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link         = anchor('admin/sale_property/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $view_step_payments_link  = anchor('admin/sale_property/payments_step/$1', '<i class="fa fa-money"></i> ' . lang('view_step_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_step_payment_link    = anchor('admin/sale_property/add_step_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_step_payment'), 'data-toggle="modal" data-target="#myModal"');
        $add_loan_link            = anchor('admin/installments/add/$1', '<i class="fa fa-money"></i> ' . lang('add_installment'));
        $down_payments_link   = anchor('admin/sales/view_down_payments/$1', '<i class="fa fa-money"></i> ' . lang('view_down_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_Downpayment_link = anchor('admin/sales/add_downpayment/$1', '<i class="fa fa-money"></i> ' . lang('add_down_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link               = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link                = anchor('admin/property/edit_sale/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link                 = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link              = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link              = "<a href='#' class='po' title='<b>" . lang("delete_sale") . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li class="hide">' . $agreement_house_link . '</li>
            <li class="add_agreement hide">' . $agreement_link . '</li>
            <li class="add_agreement hide">' . $agreement2_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $down_payments_link . '</li>
            <li>' . $add_Downpayment_link . '</li>
            <li class="add_loan_">' . $add_loan_link . '</li>
            <li>' . $attachment . '</li> 
            <li>' . $add_doc . '</li>
            <li>' . $view_commission . '</li>
            <li>' . $add_commission . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
            
          $si = "( SELECT sale_id, product_id,GROUP_CONCAT(
                            CONCAT(
                            {$this->db->dbprefix('sale_items')}.product_code, '__', 
                            {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane 
                from {$this->db->dbprefix('sale_items')} ";

           /* if ($product || $serial) {
                $si .= ' WHERE ';
            }
            if ($product) {
                $si .= " {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }*/
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";

        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('sales')}.reference_no,
                project_name,
                FSI.item_nane as iqty, 
                {$this->db->dbprefix('sales')}.customer, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
                sale_status, 
                grand_total, 
                paid,
                (grand_total-paid) as balance, 
                {$this->db->dbprefix('custom_field')}.description,
                CONCAT(FORMAT(({$this->db->dbprefix('sales')}.paid*100/{$this->db->dbprefix('sales')}.grand_total),2),'%') as percent,
                payment_status, 
                {$this->db->dbprefix('sales')}.attachment, 
                {$this->db->dbprefix('sales')}.return_id")
                ->from('sales')
                ->join('projects', 'sales.project_id = projects.project_id', 'left')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('custom_field','custom_field.id=sales.buy_term', 'left')
                ->join('users', 'sales.saleman_by = users.id', 'left')
                ->where('sales.warehouse_id', $warehouse_id)
                ->where('module_type', 'property')
                ->order_by('date', 'DESC');
                
                
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
                {$this->db->dbprefix('sales')}.reference_no,
                project_name,
                {$this->db->dbprefix('sales')}.biller_id, 
                {$this->db->dbprefix('sales')}.customer, 
                CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as saleman_by,
                sale_status, 
                grand_total, 
                paid,
                (grand_total-paid) as balance, 
                {$this->db->dbprefix('custom_field')}.description,
                CONCAT(FORMAT(({$this->db->dbprefix('sales')}.paid*100/{$this->db->dbprefix('sales')}.grand_total),2),'%') as percent,
                payment_status, 
                {$this->db->dbprefix('sales')}.attachment, 
                {$this->db->dbprefix('sales')}.return_id")
                ->from('sales')
                ->join('projects', 'sales.project_id = projects.project_id', 'left')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('custom_field','custom_field.id=sales.buy_term', 'left')
                ->join('users', 'sales.saleman_by = users.id', 'left')
                ->where('module_type', 'property')
                ->order_by('date', 'DESC');
        } 
        if ($this->input->get('status') == 'due') {
            $this->datatables->where(array('payment_status !=' => 'paid', 'sale_status !=' => 'returned'));
        }
    
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', NULL);
        }
        $this->datatables->where('pos !=', 1);
        
        if($this->SaleLeader){
            $this->datatables->where('biller_id = '.$sub_bill.'');
        }elseif($this->SaleAgent){
            $this->datatables->where('biller_id', $this->session->userdata('biller_id'));
        }
        if ($percent) {
            $this->datatables->like("CONCAT(FORMAT(({$this->db->dbprefix('sales')}.paid*100/{$this->db->dbprefix('sales')}.grand_total),2),'%')", $percent);
        }
        
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }
    public function add_sale($property_id = NULL, $sale_order_id = NULL,$quote_id = null){
        if($property_id){
           $condition = $this->products_model->getProductByID($property_id);
           if($condition->quantity == 2){
            $data = $this->products_model->getBookingByPID($property_id);
            $this->data['booking_items']  = $data;
           }
        }
        $this->bpas->checkPermissions();
        if($sale_order_id){
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id);
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'pending'){
                $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->order_status) == 'rejected'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            
            if(($this->sales_order_model->getSaleOrder($sale_order_id)->sale_status) == 'sale'){
                $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

        }
        // $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $sale_id = $sale_order_id ? $sale_order_id : null;
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $project_id = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
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
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $cost = $product_details->cost;
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
                        'max_serial'         => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                    ];
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && ($sale_status=='completed' || $sale_status=='consignment')){
                        $getproduct = $this->site->getProductByID($item_id);
                            $default_sale  = $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
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
            $data = ['date' => $date,
                'project_id'        => $this->input->post('project'),
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
                'module_type'         => 'property',
                'saleman_by'          => $this->input->post('saleman_by'),
                'zone_id'             => $this->input->post('zone_id') ? $this->input->post('zone_id') : null,
                'buy_status'            => $payment_status,
                'buy_term'            => $this->input->post('buy_term'),
                'expired_paid' => ($payment_status == 'booking') ? $this->bpas->fld($this->input->post('slexpired_day')) : null
            ];

            if ($payment_status == 'booking' || $payment_status == 'partial' || $payment_status == 'paid') {
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
                    'type'          => ($payment_status == 'booking'   )? $payment_status : 'received',
                ];
              
                $amount_paying = $this->bpas->formatDecimal($this->input->post('amount-paid'));
       
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
        }

        if ($this->form_validation->run() == true && 
            $this->sales_model->addSale($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, $payment_status)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_id) {
                $this->db->update('sales_order', array('sale_status' => 'sale'), array('id' => $sale_id));
            }
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('property/sales');
        } else {
            if ($quote_id || $sale_id) {

                if ($quote_id) {
                    $this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
                    $items               = $this->sales_model->getAllQuoteItems($quote_id);
                    $this->data['inv'] = $this->data['quote'];
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_id);
                    $this->data['inv'] = $this->data['quote'];
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
                    $row->serial_no       = isset($row->serial_no);
                    
                    $row->option          = $item->option_id;
                    $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
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
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = ['id' => 1, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }
            $c = rand(100000, 9999999);
            if($property_id){
                $this->data['booking'] = $this->products_model->getBookingByPID($property_id);
               
                $this->data['inv'] = $this->data['booking'];
                $row = $this->site->getProductByID($property_id);
                // var_dump($row);
                if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                $row->quantity = 1;
                    $ri       = $this->Settings->item_addition ? $row->id : 1;
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, 1);
                    }
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => 1, 'units' => 1, 'options' => 1, ];
                 $this->data['booking_item'] = json_encode($pr);
                //  var_dump($this->data['booking_item']);
            }
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']         = $this->site->getAllProject();
            $this->data['zones']      = $this->site->getAllZones();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']   = $quote_id ? $quote_id : $sale_id;
            $this->data['booking_id'] = $property_id ? $property_id : NULL;
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['units']      = $this->site->getAllBaseUnits();
            //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']    = $this->site->getReference('so');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sale_property'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                      = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('property/sales/add', $meta, $this->data);
        }
    }
    /* ------------------------------------------------------------------------ */
    public function edit_sale($id = null)
    {
        $this->bpas->checkPermissions();
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
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

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
            $due_date               = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);

                //$due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
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
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial        = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_detail      = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $cost = $product_details->cost;
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
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);
                    $getitems = $this->site->getProductByID($item_id);
                    $commission_item = $this->site->getProductCommissionByID($getitems->id);
                    $purchase_unit_cost = $product_details->cost;

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
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
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'max_serial'        => $item_max_serial,
                        'real_unit_price'   => $real_unit_price,
                        'addition_type'     => $item_addition_type,
                        'warranty'          => $item_warranty,
                        'comment'           => $item_detail,
                        'commission'        => isset($commission_item->price) ? $commission_item->price* $item_quantity : 0,
                    ];
                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) :0;
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
                        /*
                        $getproduct = $this->site->getProductByID($item_id);
                        if($getproduct->gender =='WOMEN'){
                            $default_sale = 7001101;
                        }elseif ($getproduct->gender =='MEN') {
                            $default_sale = 7001102;
                        }elseif ($getproduct->gender =='GIRLS') {
                            $default_sale = 7001103;
                        }elseif ($getproduct->gender =='BOY') {
                            $default_sale = 7001104;
                        }else{*/
                            $default_sale = ($item_type == 'standard') ? $this->accounting_setting->default_sale : $this->accounting_setting->other_income;
                        //}
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' =>$this->accounting_setting->default_stock,
                            'amount' => -($cost * $item_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => ($cost * $item_quantity),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => -(($item_net_price + $item_tax) * $item_unit_quantity),
                            'narrative' =>  $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
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
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                    }
                }

            //=======acounting=========//
            if($this->Settings->accounting == 1){
                //$saleAcc = $this->site->getAccountSettingByBiller();
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_sale_discount,
                        'amount'        => $order_discount,
                        'narrative'     => 'Order Discount',
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'people_id'     => $this->session->userdata('user_id'),
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
                if($order_tax != 0){
                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_sale_tax,
                        'amount'        => -$order_tax,
                        'narrative'     => 'Order Tax',
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'people_id'     => $this->session->userdata('user_id'),
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
                if($shipping != 0){
                    $accTrans[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_sale_freight,
                        'amount'        => -$shipping,
                        'narrative'     => 'Shipping',
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'people_id'     => $this->session->userdata('user_id'),
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//

            $data           = ['date' => $date,
                'project_id'        => $this->input->post('project'),
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
                'consignment_status'  => $sale_status == "consignment" ? 1 : 0,
                'payment_status'      => $payment_status,
                'payment_term'        => $payment_term,
                'due_date'            => $due_date,
                'updated_by'          => $this->session->userdata('user_id'),
                'saleman_by'          => $this->input->post('saleman_by'),
                'zone_id'             => $this->input->post('zone_id'),
                'updated_at'          => date('Y-m-d H:i:s'),
                'buy_term'            => $this->input->post('buy_term'),
                'saleman_award_points'=> $saleman_award_points
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
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && 
            $this->sales_model->updateSale($id, $data, $products,$accTrans,'', $commission_product)) {
            
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect($inv->pos ? 'pos/sales' : 'property/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
                // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                    // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                $cate_id = !empty($item->subcategory_id)?$item->subcategory_id:$item->category_id;
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
                $row->warranty        = $item->warranty;
                $row->option          = $item->option_id;
                $row->addition_type   = $item->addition_type;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                $row->details         = $item->comment;
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
                    // var_dump($fiber_type);
                $categories->type_id  = $row->addition_type;
                    // foreach ($fiber_type as $key => $value) {
                    //     if ($categories->type_id == $value->id) {
                    //         $fiber_type[$key]->qty = $value->qty + $row->base_quantity;
                    //         $categories->qty = $fiber_type[$key]->qty;
                    //     }
                    // }
                $fibers = array('fiber' => $categories, 'type' => $fiber_type, );

                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'fiber' => $fibers, ];
                $c++;
            }
            $this->data['count'] = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']         = $this->site->getAllProject();
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['agencies'] = $this->site->getAllUsers();
                //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['zones']      = $this->site->getAllZones();
            $Settings = $this->site->getSettings();
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_sale')]];
            $meta = ['page_title' => lang('edit_sale'), 'bc' => $bc];
            $this->page_construct('property/sales/edit', $meta, $this->data);
        }
    }
    function getPropertyReport($pdf = null, $xls = null)
    {
        $this->bpas->checkPermissions('index', TRUE);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : NULL;

        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = NULL;
        }
        if ($this->input->get('category')) {
            $category = $this->input->get('category');
        } else {
            $category = NULL;
        }
        if ($this->input->get('product_type')) {
            $product_type = $this->input->get('product_type');
        } else {
            $product_type = NULL;
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

        $ai = "( SELECT sales, product_id, {$this->db->dbprefix('adjustment_items')}.serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, ' (', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END), ')') SEPARATOR '\n') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";

        if ($pdf || $xls) {
            $this->db
                    ->select("
                    {$this->db->dbprefix('products')}.image as image, 
                    {$this->db->dbprefix('projects')}.project_name, 
                    {$this->db->dbprefix('products')}.name as pname,
                    {$this->db->dbprefix('sales')}.reference_no as proname, 
                    {$this->db->dbprefix('sales')}.customer as name, 
                    {$this->db->dbprefix('brands')}.name as brand, 
                    {$this->db->dbprefix('categories')}.name as cname, 
                    
                    {$this->db->dbprefix('products')}.price as price,
                    {$this->db->dbprefix('sales')}.paid as paid,
                    ({$this->db->dbprefix('products')}.price - {$this->db->dbprefix('sales')}.paid) as balance,
                    {$this->db->dbprefix('products')}.quantity as quantity,
                    payment_status")
                    ->join('booking', 'booking.product_id = products.id', 'left')
                    ->join('projects', 'projects.project_id = products.project_id', 'left')
                    ->join('sale_items', 'sale_items.product_id = products.id', 'left')
                    ->join('sales', 'sales.id = sale_items.sale_id', 'left')
                    ->from('products');

            $this->db->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.module_type', 'property');
                // ->group_by("products.id");
            if ($product) {
                $this->db->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($product_type) {
                $this->db->where($this->db->dbprefix('products') . ".quantity", $product_type);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Property_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('project'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('code'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('block'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('type'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('price'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('status'));


                $row = 2;
                foreach ($data as $data_row) {
                
                    if ($data_row->quantity == -2) {
                        $status = lang('Blocking');
                    } else if ($data_row->quantity <= -1 && $data_row->quantity != -2 ) {
                        $status = lang('Sold');
                    } else if ($data_row->quantity == 1) {
                        $status = lang('Available');
                    } else if ($data_row->quantity == 2) {
                        $status = lang('Booking');
                    } else if ($data_row->quantity == 0) {
                        $status = lang('Unavailable');
                    }
                   
                     
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->project_name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->pname);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->proname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->brand);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->cname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->price);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                $filename = 'Properties_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }else{
            $this->load->library('datatables');
            $this->datatables
                    ->select("
                    {$this->db->dbprefix('products')}.image as image, 
                    {$this->db->dbprefix('projects')}.project_name, 
                    {$this->db->dbprefix('products')}.name as pname,
                    {$this->db->dbprefix('sales')}.reference_no as proname, 
                    {$this->db->dbprefix('sales')}.customer as name, 
                    {$this->db->dbprefix('brands')}.name as brand, 
                    {$this->db->dbprefix('categories')}.name as cname, 
                    
                    {$this->db->dbprefix('products')}.price as price,
                    {$this->db->dbprefix('sales')}.paid as paid,
                    ({$this->db->dbprefix('products')}.price - {$this->db->dbprefix('sales')}.paid) as balance,
                    {$this->db->dbprefix('products')}.quantity as quantity,
                    payment_status")
                    ->join('booking', 'booking.product_id = products.id', 'left')
                    ->join('projects', 'projects.project_id = products.project_id', 'left')
                    ->join('sale_items', 'sale_items.product_id = products.id', 'left')
                    ->join('sales', 'sales.id = sale_items.sale_id', 'left')
                    ->from('products');
     
                $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('units', 'products.unit=units.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->where('products.module_type', 'property');
                // ->group_by("products.id");
            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . ".id", $product);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . ".category_id", $category);
            }
            if ($product_type) {
                $this->datatables->where($this->db->dbprefix('products') . ".quantity", $product_type);
            }
            echo $this->datatables->generate();
        }
    }
}
