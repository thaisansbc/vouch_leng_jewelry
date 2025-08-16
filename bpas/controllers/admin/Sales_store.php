<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_store extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('sales_store_model');
        $this->load->admin_model('pos_model');
        $this->load->admin_model('auth_model');
        $this->load->admin_model('projects_model');
        $this->load->admin_model('quotes_model');
        $this->load->admin_model('promos_model');
        $this->load->admin_model('sales_order_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('table_model'); 

        $this->pos_settings         = $this->pos_model->getSetting();
        $this->data['pos_settings'] = $this->pos_settings;
        
        $this->digital_upload_path  = 'files/';
        $this->upload_path          = 'assets/uploads/';
        $this->thumbs_path          = 'assets/uploads/thumbs/';
        $this->image_types          = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types   = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size    = '1024';
        $this->data['logo']         = true;
    }

    public function index($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales');
        $count = explode(',', $this->session->userdata('biller_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) {
            $this->data['billers']   = $this->site->getAllCompanies('biller');
            $this->data['biller_id'] = $biller_id;
            $this->data['biller']    = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        } else {
            if (count($count)>1) {
                $this->data['billers']   = $this->site->getAllCompanies('biller');
            } else {
                $this->data['billers']   = null;
            }
            $this->data['count_billers'] = $count;
            $this->data['biller_id']     = $biller_id;
            $this->data['biller']        = $biller_id ? $this->site->getCompanyByID($biller_id) : null;
        }

        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $biller_id = $this->session->userdata('biller_id');
        $this->data['users']        = $this->site->getStaff();
        $this->data['products']     = $this->site->getProducts();
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $this->data['billers']      = $this->site->getAllCompanies('biller');
        $this->data['user_billers'] = $this->site->getCompanyByID($biller_id);
        $this->data['drivers']      = $this->site->getDriver();

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales_store')]];
        $meta = ['page_title' => lang('sales_store'), 'bc' => $bc];
        $this->page_construct('sales_store/index', $meta, $this->data);
    }

    public function getSales($biller_id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales');
        if ($biller_id) {
            $biller_ids = explode('-', $biller_id);
        }
        if ((!$this->Owner && !$this->Admin) && !$biller_id) {
            $biller_id = $this->session->userdata('biller_id');
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
        $detail_link          = anchor('admin/sales_store/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $view_logo            = anchor('admin/sales_store/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $duplicate_link       = anchor('admin/sales_store/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales_store/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales_store/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $packagink_link       = anchor('admin/sales_store/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_delivery_link    = anchor('admin/sales_store/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $email_link           = anchor('admin/sales_store/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/sales_store/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link             = anchor('admin/sales_store/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/sales_store/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $add_warranty_link    = anchor('admin/sales_store/add_maintenance/$1', '<i class="fa fa-money"></i> ' . lang('add_maintenance'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales_store/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $view_logo . '</li>';
            $action .= '
                <li>' . $duplicate_link . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $packagink_link . '</li>
                <li>' . $add_delivery_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $add_warranty_link . '</li>
                <li>' . $return_link . '</li>
                <li class="delete">' . $delete_link . '</li>
        </ul>
        </div></div>';

        $ds = "( SELECT d.sale_id,d.delivered_by,d.status,c.name as delivery_name
                from {$this->db->dbprefix('deliveries')} d LEFT JOIN {$this->db->dbprefix('companies')} c 
                on d.delivered_by = c.id ) FSI";

        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('sales')}.id as id, 
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date,
                project_name,
                FSI.delivery_name as delivered_by,
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
                FSI.status as delivery_status, 
                {$this->db->dbprefix('sales')}.return_id")
            ->join('projects', 'sales.project_id = projects.project_id', 'left')
            ->join('sales_order', 'sales.so_id = sales_order.id', 'left')
            ->join('users', 'sales.saleman_by = users.id', 'left')
            ->join($ds, 'FSI.sale_id=sales.id', 'left')
            ->order_by('sales.id', 'desc')
            ->from('sales')
            ->where('sales.store_sale', 1);
        $this->datatables->where('sales.module_type','inventory');
        $this->datatables->where('sales.hide', 1);
        if ($biller_id) {
            $this->datatables->where('sales.biller_id', $biller_id);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where_in("FIND_IN_SET(bpas_sales.biller_id, '" . $this->session->userdata('biller_id') . "')");
            $this->datatables->where("FIND_IN_SET(bpas_sales.created_by, '" . $this->session->userdata('user_id') . "')");
        }
        if ($this->Customer) {
            $this->datatables->where('projects.customer_id','bpas_projects.customer_id');
        }
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
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
        if ($a || $a == 'empty') {
            $alert_ids = explode('-', $a);
            $alert_id  = $a;
            if (count($alert_ids) > 1) {
                $this->datatables->where_in('sales.id', $alert_ids);
            } else {
                $this->datatables->where('sales.id', $alert_id);
            }
        }
        if ($start_date) {
            $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
        }
        $this->datatables->where($this->db->dbprefix('sales') . '.pos !=', 1); // ->where('sale_status !=', 'returned');
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function sale_actions()
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
                    $settings = $this->site->getSettings();
                    if($settings->hide != 0){
                        foreach ($_POST['val'] as $id) {
                            $this->sales_model->deleteSale($id);
                        }
                        $this->session->set_flashdata('message', lang('sales_deleted'));
                        redirect($_SERVER['HTTP_REFERER']);
                    } else {
                        foreach ($_POST['val'] as $id) {
                            $this->sales_model->removeSale($id);
                        }
                        $this->session->set_flashdata('message', lang('sales_removed'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                } elseif ($this->input->post('form_action') == 'apply_to_tax') {
                    foreach ($_POST['val'] as $id) {
                        $inv = $this->sales_model->getInvoiceByID($id);
                        if($inv->is_tax == 1) continue;
                        $warehouseCode = $this->site->getWarehouseByID($inv->warehouse_id)->code;
                        $taxReference = $this->site->getTaxReference($warehouseCode);
                        $data = [
                            'tax_reference_no' => $taxReference,
                            'is_tax' => 1
                        ];
                        if ($this->db->update('sales', $data, ['id' => $id])){
                            $this->site->updateTaxReference($warehouseCode);
                        }
                    }
                    $this->session->set_flashdata('message', lang('Tax has been applied!'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'generate') {
                    //  $this->bpas->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $row        = $this->site->getMaintenanceByID($id);
                        $sale_id    = $row->sale_id;
                        $sale       = $this->sales_model->getInvoiceByID($sale_id);
                        $saleItems  = $this->sales_model->getAllInvoiceItems($sale_id);
                        $datas      = [
                            'date'                => date('Y-m-d H:i'),
                            'project_id'          => $sale->project_id,
                            'so_id'               => $sale->so_id? $sale->so_id : null,
                            'reference_no'        => $this->site->getReference('so'),
                            'po_number'           => $sale->po_number,
                            'customer_id'         => $sale->customer_id,
                            'customer'            => $sale->customer,
                            'biller_id'           => $sale->biller_id,
                            'biller'              => $sale->biller,
                            'warehouse_id'        => $sale->warehouse_id,
                            'note'                => $sale->note,
                            'staff_note'          => $sale->staff_note,
                            'total'               => $sale->total,
                            'product_discount'    => $sale->product_discount,
                            'order_discount_id'   => $sale->order_discount_id,
                            'order_discount'      => $sale->order_discount,
                            'total_discount'      => $sale->total_discount,
                            'product_tax'         => $sale->product_tax,
                            'order_tax_id'        => $sale->order_tax_id,
                            'order_tax'           => $sale->order_tax,
                            'total_tax'           => $sale->total_tax,
                            'shipping'            => $sale->shipping,
                            'grand_total'         => $sale->grand_total,
                            'total_items'         => $sale->total_items,
                            'sale_status'         => 'completed',
                            'payment_status'      => 'pending',
                            'payment_term'        => $sale->payment_term,
                            'due_date'            => $sale->due_date,
                            'paid'                => 0, 
                            'created_by'          => $this->session->userdata('user_id'),
                            'hash'                => hash('sha256', microtime() . mt_rand()),
                            'saleman_by'          => $sale->saleman_by,
                        ];
                        foreach ($saleItems as $item) {
                            $product = [
                                'product_id'        => $item->product_id,
                                'product_code'      => $item->product_code,
                                'product_name'      => $item->product_name,
                                'product_type'      => $item->product_type,
                                'option_id'         => $item->option_id,
                                'purchase_unit_cost'=> $item->purchase_unit_cost ? $item->purchase_unit_cost: NULL,
                                'net_unit_price'    => $item->net_unit_price,
                                'unit_price'        => $item->unit_price,
                                'quantity'          => $item->quantity,
                                'product_unit_id'   => $item->product_unit_id ? $item->product_unit_id : null,
                                'product_unit_code' => $item->product_unit_code ? $item->product_unit_code : null,
                                'unit_quantity'     => $item->unit_quantity,
                                'warehouse_id'      => $item->warehouse_id,
                                'item_tax'          => $item->item_tax,
                                'tax_rate_id'       => $item->tax_rate_id,
                                'tax'               => $item->tax,
                                'discount'          => $item->discount,
                                'item_discount'     => $item->item_discount,
                                'subtotal'          => $item->subtotal,
                                'serial_no'         => $item->serial_no,
                                'max_serial'        => $item->max_serial,
                                'real_unit_price'   => $item->real_unit_price,
                                'addition_type'     => $item->addition_type,
                                'warranty'          => $item->warranty,
                                'weight'            => $item->weight,
                                'total_weight'      => $item->total_weight,
                                'comment'           => $item->comment,
                            ];
                            $products[] = $product;
                        }
                        if (empty($products)) {
                            $this->form_validation->set_rules('product', lang('order_items'), 'required');
                        } else {
                            krsort($products);
                        }
                        $this->sales_model->addSale($datas, $products,'','', '', '', null,'');
                    }
                    $this->session->set_flashdata('message', lang('Invoice generate'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'sync_account'){
                    $i=1;
                    foreach ($_POST['val'] as $id) {
                        $sales = $this->sales_model->getsale_detail_ByID($id);
                        foreach($sales as $sale) {
                            $item_type          = $sale->product_type;
                            $item_code          = $sale->product_code;
                            $product_details    = $this->sales_model->getProductByCode($item_code);
                            $cost               = $product_details->cost;
                            $id                 = $sale->id;
                            $date               = $sale->date;
                            $reference          = $sale->reference_no;
                            $order_discount     = $sale->order_discount;
                            $order_tax          = $sale->order_tax;
                            $shipping           = $sale->shipping;
                            $item_quantity      = $sale->quantity;
                            $note               = $sale->note;
                            $biller_id          = $sale->biller_id;
                            $project_id         = $sale->project_id;
                            $user_id            = $sale->created_by;
                            $customer_id        = $sale->customer_id;
                            $item_net_price     = $sale->unit_price;
                            $item_tax           = $sale->item_tax;
                            $item_unit_quantity = $sale->quantity;
                            $item_id            = $sale->product_id;
                            
                            if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale->sale_status=='completed'){
                                $getproduct = $this->site->getProductByID($item_id);
                                /*if($getproduct->gender =='WOMEN'){
                                    $default_sale = 7001101;
                                }elseif ($getproduct->gender =='MEN') {
                                    $default_sale = 7001102;
                                }elseif ($getproduct->gender =='GIRLS') {
                                    $default_sale = 7001103;
                                }elseif ($getproduct->gender =='BOY') {
                                    $default_sale = 7001104;
                                }else{*/
                                    $default_sale = $this->accounting_setting->default_sale;
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
                                    'customer_id' => $customer_id,
                                    'created_by'  => $user_id,
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
                                    'customer_id' => $customer_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                                );

                                $accTrans[] = array(
                                    'tran_no' => $id,
                                    'tran_type' => 'Sale',
                                    'tran_date' => $date,
                                    'reference_no' => $reference,
                                    'account_code' => $default_sale,//$this->accounting_setting->default_sale,
                                    'amount' => -(($item_net_price + $item_tax) * $item_unit_quantity),
                                    'narrative' =>  $this->site->getAccountName($default_sale),
                                    'description' => $note,
                                    'biller_id' => $biller_id,
                                    'project_id' => $project_id,
                                    'customer_id' => $customer_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                );

                            }
                            $data           = [
                                'sync_account' => 1,
                            ];
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
                                        'people_id' => $this->session->userdata('user_id'),
                                        'customer_id' => $customer_id,
                                        'created_by'  => $this->session->userdata('user_id'),
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
                                        'people_id' => $this->session->userdata('user_id'),
                                        'customer_id' => $customer_id,
                                        'created_by'  => $this->session->userdata('user_id'),
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
                                        'people_id' => $this->session->userdata('user_id'),
                                        'customer_id' => $customer_id,
                                        'created_by'  => $this->session->userdata('user_id'),
                                    );
                                }

                            }
                            //============end accounting=======//
                        }
                        $this->sales_model->syncAcc_Sale($id, $data, $products,$accTrans);
                    }
                    $this->session->set_flashdata('message', lang('sync_account_successful'));
                    admin_redirect('sales_store');

                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('project'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('saleman'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('order_ref'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('H1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('I1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('J1', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('K1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('L1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('M1', lang('payment_status'));

                    $row = 2;
                    $i=1;
                    foreach ($_POST['val'] as $id) {
                        $sale       = $this->sales_model->getInvoiceByID($id);
                        $saleman    = $this->auth_model->getUserByID($sale->saleman_by);
                        $project    = $this->projects_model->getProjectByID($sale->project_id);
                        $sale_order = $this->sales_order_model->getSaleOrderRefByID($sale->so_id);

                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $i);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $project ? $project->project_name : '');
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $saleman != false ? $saleman->first_name . ' ' . $saleman->last_name : '');
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale_order != false ? $sale_order->reference_no : '');
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $row, $sale->customer);
                        $this->excel->getActiveSheet()->SetCellValue('I' . $row, $sale->total);
                        $this->excel->getActiveSheet()->SetCellValue('J' . $row, $sale->total_discount);
                        $this->excel->getActiveSheet()->SetCellValue('K' . $row, $sale->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('L' . $row, lang($sale->paid));
                        $this->excel->getActiveSheet()->SetCellValue('M' . $row, lang($sale->payment_status));
                        $row++;
                        $i++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_store_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);

                } elseif ($this->input->post('form_action') == 'preview') {
                    /*  $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('total'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('discount'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));

                    $row = 2;
                    $i=1;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_model->getInvoiceByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $i);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $this->bpas->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->total);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->total_discount);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sale->grand_total);
                        $row++;
                        $i++;
                    }
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                    */
                    // $this->bpas->checkPermissions('payments', true);
                    $this->load->helper('security');
                    $this->data['start_date']   = $this->input->post('start_date') ? $this->input->post('start_date') : null;
                    $this->data['end_date']     = $this->input->post('end_date') ? $this->input->post('end_date') : null;
                    $this->data['sales']         = $_POST['val'];
                    $this->data['payment_ref'] = $this->site->getReference('pay');
                    $this->data['modal_js']    = $this->site->modal_js();
                    $this->load->view($this->theme . 'sales/preview_sale', $this->data);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_sale_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function add($sale_order_id = null, $quote_id = null)
    {   
        $this->bpas->checkPermissions('add', true, 'store_sales');
        $sale_id         = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $getexchange_bat = $this->bpas->getExchange_rate('THB');
        $exchange_khm    = $getexchange_khm->rate;
        $exchange_bat    = $getexchange_bat->rate;
        if(isset($sale_order_id)){
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id);
            if(isset($sale_o)){
                //     if($sale_o->order_status == 'pending'){
                //         $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                //         redirect($_SERVER["HTTP_REFERER"]);
                //     }
                //     if($sale_o->order_status == 'rejected'){
                //         $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                //         redirect($_SERVER["HTTP_REFERER"]);
                //     }
                if($sale_o->sale_status == 'completed'){
                    $this->session->set_flashdata('error', lang("sale_order_has_been_created"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            }
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('from_warehouse', lang('from_warehouse'), 'required');
        $this->form_validation->set_rules('to_warehouse', lang('to_warehouse'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $project_id       = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $from_warehouse_id= $this->input->post('from_warehouse');
            $to_warehouse_id  = $this->input->post('to_warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date      = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;
            $commission_product = 0;
            $text_items = "";
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
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = $_POST['product_weight'][$r];
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
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
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculteIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $total_weight = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');
                    $getitems     = $this->site->getProductByID($item_id);
                    $commission_item = $this->site->getProductCommissionByID($getitems->id);
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    if($commission_item){
                        $commission_product += $commission_item->price * $item_quantity;
                    }

                    if ($unit->id != $product_details->unit) {
                        $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }
                    if ($unit->id != $product_details->unit) {
                        $base_unit_cost = $this->site->convertToBase($unit, ($item_net_price + $item_tax));
                    } else {
                        $base_unit_cost = ($item_net_price + $item_tax);
                    }

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'purchase_unit_cost'=> $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $from_warehouse_id,
                        'to_warehouse_id'   => $to_warehouse_id,
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
                        'weight'            => $item_weight,
                        'total_weight'      => $total_weight,
                        'comment'           => $item_detail,
                        'commission'        => isset($commission_item->price) ? $commission_item->price * $item_quantity : 0,
                    ];

                    $store_item = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'option_id'         => $item_option,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'warehouse_id'      => $to_warehouse_id,
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $sale_status == 'completed' ? $item_quantity : 0,
                        'quantity_received' => $sale_status == 'completed' ? $item_quantity : 0,
                        'real_unit_cost'    => $real_unit_price,
                        'net_unit_cost'     => $item_net_price,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'base_unit_cost'    => $base_unit_cost,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $sale_status == 'completed' ? 'received' : 'pending',
                        'addition_type'     => $item_addition_type,
                        'description'       => $item_detail,
                        'supplier_part_no'  => null,
                        'expiry'            => null,
                    ];

                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
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
                    $products[]    = ($product + $gst_data);
                    $store_items[] = ($store_item + []);
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
            $user  = $this->site->getUser($this->session->userdata('user_id'));
            $staff = $this->site->getUser($this->input->post('saleman_by'));
            if(!empty($staff) && $staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }

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
            $data = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'from_id'             => $this->input->post('from'),
                'time_out_id'         => $this->input->post('time_out'),
                'destination_id'      => $this->input->post('destination'),
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
                'reference_no'        => $reference,
                'po_number'           => $this->input->post('po'),
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $from_warehouse_id,
                'to_warehouse_id'     => $to_warehouse_id,
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
                'zone_id'             => $this->input->post('zone_id') ? $this->input->post('zone_id') : null,
                'currency_rate_kh'    => $exchange_khm,
                'currency_rate_bat'   => $exchange_bat,
                'saleman_award_points'=> (!empty($saleman_award_points) && !is_nan($saleman_award_points)) ? $saleman_award_points : 0,
                'store_sale'          => 1,
            ];

            if ($payment_status == 'partial' || $payment_status == 'paid') {
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
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->sales_store_model->addSale($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product, $store_items)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_order_id) {
                $status = 'completed';
                $sale_order_id    = $this->input->post('sale_order_id');
                $sale_items       = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                $sale_order_items = $this->site->getSaleOrderItemsBySaleID($sale_order_id);
                foreach($sale_order_items as $item) {
                    $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                    if($key !== false){
                        if($item->quantity > $sale_items[$key]->quantity){
                            $status = 'partial';
                            break;
                        }
                    } else {
                        $status = 'partial';
                        break;
                    }
                }
                $this->db->update('sales_order', array('sale_status' => $status), array('id' => $sale_order_id));
            }
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales_store');
        } else {
            if ($quote_id || $sale_id || $sale_order_id) {
                if ($quote_id) {
                    $this->data['quote'] = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $sale_items = [];
                } elseif ($sale_order_id) {
                    $this->data['quote'] = $this->sales_order_model->getInvoiceByID($sale_order_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_order_id);
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                }elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_model->getAllInvoiceItems($sale_id);
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $b = false;
                    if(!$sale_id){
                        if($sale_items !== false){
                            $key = array_search($item->product_code, array_column($sale_items, 'product_code'));
                            if($key !== false){
                                if($item->unit_quantity > $sale_items[$key]->quantity){
                                    $item->unit_quantity = $item->unit_quantity - $sale_items[$key]->quantity;
                                } else {
                                    $b = true;
                                }
                            } 
                        }
                        if($b == true){
                            continue;
                        }
                    }
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
                    $row->id                 = $item->product_id;
                    $row->code               = $item->product_code;
                    $row->name               = $item->product_name;
                    $row->type               = $item->product_type;
                    $row->qty                = $item->quantity;
                    $row->base_quantity      = $item->quantity;
                    $row->base_unit          = isset($row->unit) ? $row->unit : $item->product_unit_id;
                    $row->base_unit_price    = isset($row->price) ? $row->price : $item->unit_price;
                    $row->unit               = $item->product_unit_id;
                    $row->qty                = $item->unit_quantity;
                    $row->discount           = $item->discount ? $item->discount : '0';
                    $row->item_tax           = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount      = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price              = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($row->item_discount));
                    $row->unit_price         = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($row->item_discount) + $this->bpas->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price    = $item->real_unit_price;
                    $row->tax_rate           = $item->tax_rate_id;
                    $row->serial             = '';
                    $row->serial_no          = isset($row->serial_no);
                    $row->option             = $item->option_id;
                    $row->details            = $item->comment;
                    $options                 = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
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
                   
                    $units     = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                    $ri        = $this->Settings->item_addition ? $row->id : $c;
                    $set_price = $this->site->getUnitByProId($row->id);
                    $pr[$ri]   = [
                        'id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, 
                    ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['inv']         = $this->data['quote'];
            }
            
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']      = ($quote_id ? $quote_id : ($sale_id ? $sale_id : $sale_order_id));
            $this->data['sale_order_id'] = $sale_order_id;
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['data']          = $this->site->getBillerByUser($this->session->userdata('user_id'));
            $companyID                   = explode(',', $this->data['data']->multi_biller);
            $this->data['mbillers']      = $this->site->getAllCompaniesByBiller('biller', $companyID);
            $this->data['agencies']      = $this->site->getAllUsers();
            $this->data['payment_term']  = $this->site->getAllPaymentTerm();
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['zones']         = $this->site->getAllZones();
            $this->data['group_price']   = json_encode($this->site->getAllGroupPrice());
            $this->data['salemans']      = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['slnumber']      = $this->site->getReference('sp');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = '';
            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_store'), 'page' => lang('sales_store')], ['link' => '#', 'page' => lang('add_sale_store')]];
            $meta                        = ['page_title' => lang('add_sale_store'), 'bc' => $bc];
            $this->page_construct('sales_store/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions('edit', true, 'store_sales');
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
        $this->form_validation->set_rules('from_warehouse', lang('from_warehouse'), 'required');
        $this->form_validation->set_rules('to_warehouse', lang('to_warehouse'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $project_id         = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project;
            $from_warehouse_id  = $this->input->post('from_warehouse');
            $to_warehouse_id    = $this->input->post('to_warehouse');
            $customer_id        = $this->input->post('customer');
            $biller_id          = $this->input->post('biller');
            $total_items        = $this->input->post('total_items');
            $sale_status        = $this->input->post('sale_status');
            $payment_status     = $this->input->post('payment_status');
            $payment_term       = $this->input->post('payment_term');
            $payment_term_details = $this->site->getAllPaymentTermByID($payment_term);
            // $due_date        = (isset($payment_term_details[0]->id) ? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping           = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details   = $this->site->getCompanyByID($customer_id);
            $customer           = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details     = $this->site->getCompanyByID($biller_id);
            $biller             = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note               = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note         = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product = 0;
            $total              = 0;
            $product_tax        = 0;
            $product_discount   = 0;
            $gst_data           = [];
            $total_cgst         = $total_sgst       = $total_igst       = 0;
            $i                  = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_max_serial    = isset($_POST['max_serial'][$r]) ? $_POST['max_serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];

                $item_unit_quantity = $_POST['quantity'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $quantity_received  = $_POST['product_base_quantity'][$r];
                $quantity_balance   = $_POST['quantity_balance'][$r];
                $ordered_quantity   = $_POST['ordered_quantity'][$r];

                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';

                if ($sale_status == 'completed') {
                    $balance_qty       = $quantity_received - ($ordered_quantity - $quantity_balance);
                } else {
                    $balance_qty       = $item_quantity;
                    $quantity_received = $item_quantity;
                }

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
                    if ($unit->id != $product_details->unit) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost,$unit);
                    } else {
                        $cost = $cost;
                    }
                    if ($unit->id != $product_details->unit) {
                        $base_unit_cost = $this->site->convertToBase($unit, ($item_net_price + $item_tax));
                    } else {
                        $base_unit_cost = ($item_net_price + $item_tax);
                    }
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
                        'warehouse_id'      => $from_warehouse_id,
                        'to_warehouse_id'   => $to_warehouse_id,
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
                        'commission'        => isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0,
                    ];

                    $store_item = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'option_id'         => $item_option,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'warehouse_id'      => $to_warehouse_id,
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $balance_qty,
                        'quantity_received' => $item_quantity,
                        'real_unit_cost'    => $real_unit_price,
                        'net_unit_cost'     => $item_net_price,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'base_unit_cost'    => $base_unit_cost,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => $sale_status == 'completed' ? 'received' : 'pending',
                        'addition_type'     => $item_addition_type,
                        'description'       => $item_detail,
                        'supplier_part_no'  => null,
                        'expiry'            => null,
                    ];

                    $commission_product += isset($commission_item->price) ? ($commission_item->price * $item_quantity) : 0;
                       
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $item_type != 'manual' && $sale_status=='completed'){
                       /* $getproduct = $this->site->getProductByID($item_id);
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
                    $store_items[] = ($store_item + []);
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
            if(!empty($staff) && $staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }

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

            $data   = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'from_id'             => $this->input->post('from'),
                'time_out_id'         => $this->input->post('time_out'),
                'destination_id'      => $this->input->post('destination'),
                'reference_no'        => $reference,
                'po_number'           => $this->input->post('po'),
                'customer_id'         => $customer_id,
                'customer'            => $customer,
                'biller_id'           => $biller_id,
                'biller'              => $biller,
                'warehouse_id'        => $from_warehouse_id,
                'to_warehouse_id'     => $to_warehouse_id,
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
                'store_sale'          => 1,
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
                        $gc                = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                        $amount_paying     = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                        $gc_balance        = $gc->balance - $amount_paying;
                        $payment           = [
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
                            $accTranPayments[]  = array(
                                'tran_type'     => 'Payment',
                                'tran_date'     => $date,
                                'reference_no'  => $this->input->post('payment_reference_no'),
                                'account_code'  => $this->accounting_setting->default_receivable,
                                'amount'        => ($grand_total - $amount_paying),
                                'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                                'description'   => $this->input->post('payment_note'),
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'customer_id'   => $customer_id,
                                'created_by'    => $this->session->userdata('user_id'),
                                'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                            );
                        }
                        if($this->input->post('paid_by') == 'deposit'){
                            $paying_to = $this->accounting_setting->default_sale_deposit;
                        } else {
                            $paying_to = $this->input->post('bank_account');
                        }
                        $accTranPayments[]  = array(
                            'tran_type'     => 'Payment',
                            'tran_date'     => $date,
                            'reference_no'  => $this->input->post('payment_reference_no'),
                            'account_code'  => $paying_to,
                            'amount'        => $amount_paying,
                            'narrative'     => $this->site->getAccountName($paying_to),
                            'description'   => $this->input->post('payment_note'),
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'customer_id'   => $customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => 0
                        );

                    }
                    //=====end accountig=====//
                } else {
                    $payment = [];
                    $accTranPayments[]  = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_receivable,
                        'amount'        => $grand_total,
                        'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'payment_id'    => $id,
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                }
            } else {
                $accTranPayments[]  = array(
                    'tran_no'       => $id,
                    'tran_type'     => 'Sale',
                    'tran_date'     => $date,
                    'reference_no'  => $reference,
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'biller_id'     => $biller_id,
                    'project_id'    => $project_id,
                    'customer_id'   => $customer_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'payment_id'    => $id,
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
            if(($customer_details->credit_limit !=0) && (($cus_sales->total_amount - $cus_sales->paid) - $inv->grand_total + $data['grand_total']) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
            // $this->bpas->print_arrays($products, $store_items);
        }
            
        if ($this->form_validation->run() == true && $this->sales_store_model->updateSale($id, $data, $products, $accTrans, $accTranPayments, $commission_product, $store_items)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect($inv->pos ? 'pos/sales' : 'sales_store');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']   = $this->sales_store_model->getInvoiceByID($id);
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_store_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row     = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
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
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;

                $row->qty             = $item->unit_quantity;
                $row->quantity       += $item->quantity;
                $row->base_quantity   = $item->quantity;

                $row->new_entry       = 0;
                $row->oqty            = $item->p_quantity;
                $row->received        = $item->p_quantity_received ? $item->p_quantity_received : $item->p_quantity;
                $row->quantity_balance= $item->p_quantity_balance + ($item->p_quantity - $row->received);

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
                $categories->type_id  = $row->addition_type;
                $fibers    = array('fiber' => $categories, 'type' => $fiber_type, );
                $units     = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                $ri        = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri]   = [
                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $fibers,'product_options' => $product_options, 
                ];
                $c++;
            }
            $this->data['id']         = $id;
            $this->data['count']      = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']   = $this->site->getAllProject();
            $this->data['inv_items']  = json_encode($pr);
            $this->data['payment_term'] = $this->site->getAllPaymentTerm();
            $this->data['agencies']   = $this->site->getAllUsers();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['zones']      = $this->site->getAllZones();
            $this->data['salemans']   = $this->site->getAllSalemans($this->Settings->group_saleman_id);

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_store'), 'page' => lang('sales_store')], ['link' => '#', 'page' => lang('edit_sale_store')]];
            $meta = ['page_title' => lang('edit_sale_store'), 'bc' => $bc];
            $this->page_construct('sales_store/edit', $meta, $this->data);
        }
    }

    public function return_sale($id = null)
    {
        $this->bpas->checkPermissions('return_sales');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        // if ($sale->return_id) {
        //     $this->session->set_flashdata('error', lang('sale_already_returned'));
        //     redirect($_SERVER['HTTP_REFERER']);
        // }
        $sale_balance_items = null;
        if ($sale->return_id) {
            if($this->sales_model->checkReturned($sale->id)){
                $this->session->set_flashdata('error', lang('sale_already_return_items_completed'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
        $this->form_validation->set_rules('return_surcharge', lang('return_surcharge'), 'required');
        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($sale->customer_id);
            $biller_details   = $this->site->getCompanyByID($sale->biller_id);
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
                $sale_item_id       = $_POST['sale_item_id'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : '';
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = (0 - $_POST['product_base_quantity'][$r]);
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $cost             = $product_details->cost;
                    // $unit_price    = $real_unit_price;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->bpas->formatDecimal(($unit_price - $pr_discount), 4);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->bpas->formatDecimal($pr_discount * $item_unit_quantity, 4);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax      = $item_tax = 0;
                    $tax              = '';
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details  = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax         = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax     = $ctax['amount'];
                        $tax          = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->bpas->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }
                    $product_tax       += $pr_item_tax;
                    $subtotal           = $this->bpas->formatDecimal((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit               = $item_unit ? $this->site->getUnitByID($item_unit) : false;
                    $purchase_unit_cost = $product_details->cost;
                    $getitems           = $this->site->getProductByID($item_id);
                    $commission_item    = $this->site->getProductCommissionByID($getitems->id);
                    if ($unit->id != $product_details->unit) {
                        $cost = $this->site->convertCostingToBase($purchase_unit_cost, $unit);
                    } else {
                        $cost = $cost;
                    }
                    if ($unit->id != $product_details->unit) {
                        $base_unit_cost = $this->site->convertToBase($unit, ($item_net_price + $item_tax));
                    } else {
                        $base_unit_cost = ($item_net_price + $item_tax);
                    }
                    //========add accounting=========//
                    if($this->Settings->accounting == 1 && $sale->sale_status=='completed'){
                        $getproduct   = $this->site->getProductByID($item_id);
                        $default_sale = $this->accounting_setting->default_sale;
                        $accTrans[]   = array(
                            'tran_type'     => 'SaleReturn',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_stock,
                            'amount'        => ($cost * abs($item_unit_quantity)),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description'   => $note,
                            'biller_id'     => $sale->biller_id,
                            'project_id'    => $sale->project_id,
                            'customer_id'   => $sale->customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'SaleReturn',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $this->accounting_setting->default_cost,
                            'amount'        => -($cost * abs($item_unit_quantity)),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description'   => $note,
                            'biller_id'     => $sale->biller_id,
                            'project_id'    => $sale->project_id,
                            'customer_id'   => $sale->customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_type'     => 'SaleReturn',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => -($subtotal),
                            'narrative'     => $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $sale->biller_id,
                            'project_id'    => $sale->project_id,
                            'customer_id'   => $sale->customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                    }
                    //============end accounting=======//
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
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $sale->warehouse_id,
                        'to_warehouse_id'   => $sale->to_warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'sale_item_id'      => $sale_item_id,
                        'commission'        => isset($commission_item->price) ? $commission_item->price * $item_quantity : 0,
                    ];

                    $store_item = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'option_id'         => $item_option,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'warehouse_id'      => $sale->to_warehouse_id,
                        'quantity'          => $item_quantity,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $item_quantity,
                        'real_unit_cost'    => $real_unit_price,
                        'net_unit_cost'     => $item_net_price,
                        'unit_cost'         => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'base_unit_cost'    => $base_unit_cost,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->bpas->formatDecimal($subtotal),
                        'date'              => date('Y-m-d', strtotime($date)),
                        'status'            => 'received',
                        'expiry'            => null,
                    ];

                    $commission_product += isset($commission_item->price) ? $commission_item->price * $item_quantity : 0;
                    $si_return[] = [
                        'id'           => $sale_item_id,
                        'sale_id'      => $id,
                        'product_id'   => $item_id,
                        'option_id'    => $item_option,
                        'quantity'     => (0 - $item_quantity),
                        'warehouse_id' => $sale->warehouse_id,
                    ];

                    $products[]    = ($product + $gst_data);
                    $store_items[] = ($store_item + $gst_data);
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $this->bpas->formatDecimal($return_surcharge) + (0 - $shipping) - $order_discount), 4);
            $saleman_award_points = 0;
            $staff = $this->site->getUser($sale->saleman_by);
            if(!empty($staff)){
               if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                    }
                } 
            }
            //=======acounting=========//
            if($this->Settings->accounting == 1){
                if(abs($order_discount) != 0){
                    $accTrans[] = array(
                        'tran_type'    => 'SaleReturn',
                        'tran_date'    => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount'       => -abs($order_discount),
                        'narrative'    => 'Order Discount Return '.$sale->reference_no,
                        'description'  => $note,
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'people_id'    => $this->session->userdata('user_id'),
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                if(abs($order_tax) != 0){
                    $accTrans[] = array(
                        'tran_type'    => 'SaleReturn',
                        'tran_date'    => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount'       => abs($order_tax),
                        'narrative'    => 'Order Tax Return '.$sale->reference_no,
                        'description'  => $note,
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'people_id'    => $this->session->userdata('user_id'),
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );  
                }
                if($return_surcharge != 0){
                    $accTrans[] = array(
                        'tran_type'    => 'SaleReturn',
                        'tran_date'    => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->other_income,
                        'amount'       => -$return_surcharge,
                        'narrative'    => 'Surcharge Return '.$sale->reference_no,
                        'description'  => $note,
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'people_id'    => $this->session->userdata('user_id'),
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data  = [
                'sale_id'              => $id,
                'date'                 => $date,
                'project_id'           => $this->input->post('project'),
                'reference_no'         => $sale->reference_no,
                'customer_id'          => $sale->customer_id,
                'customer'             => $sale->customer,
                'biller_id'            => $sale->biller_id,
                'biller'               => $sale->biller,
                'warehouse_id'         => $sale->warehouse_id,
                'to_warehouse_id'      => $sale->to_warehouse_id,
                'total_items'          => $total_items,
                'note'                 => $note,
                'total'                => $total,
                'product_discount'     => $product_discount,
                'order_discount_id'    => $this->input->post('discount') ? $this->input->post('order_discount') : null,
                'order_discount'       => $order_discount,
                'total_discount'       => $total_discount,
                'product_tax'          => $product_tax,
                'order_tax_id'         => $this->input->post('order_tax'),
                'order_tax'            => $order_tax,
                'total_tax'            => $total_tax,
                'surcharge'            => $this->bpas->formatDecimal($return_surcharge),
                'grand_total'          => $grand_total,
                'created_by'           => $this->session->userdata('user_id'),
                'saleman_by'           => $sale->saleman_by,
                'zone_id'              => $sale->zone_id,
                'return_sale_ref'      => $reference,
                'shipping'             => $shipping,
                'original_price'       => $sale->original_price,
                'module_type'          => $sale->module_type,
                'currency_rate_kh'     => $sale->currency_rate_kh,
                'sale_status'          => 'returned',
                'pos'                  => $sale->pos,
                'payment_status'       => $sale->payment_status == 'paid' ? 'due' : 'pending',
                'saleman_award_points' => $saleman_award_points,
                'store_sale'           => 1,
            ];
            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
                $pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pp');
                $payment = [
                    'date'         => $date,
                    'reference_no' => $pay_ref,
                    'amount'       => (0 - $this->input->post('amount-paid')),
                    'paid_by'      => $this->input->post('paid_by'),
                    'cheque_no'    => $this->input->post('cheque_no'),
                    'cc_no'        => $this->input->post('pcc_no'),
                    'cc_holder'    => $this->input->post('pcc_holder'),
                    'cc_month'     => $this->input->post('pcc_month'),
                    'cc_year'      => $this->input->post('pcc_year'),
                    'cc_type'      => $this->input->post('pcc_type'),
                    'created_by'   => $this->session->userdata('user_id'),
                    'type'         => 'returned',
                ];
                $data['payment_status'] = ($grand_total == $this->input->post('amount-paid')) ? 'paid' : 'partial';
                //------------accounting-----------
                $paying_to = ($this->input->post('bank_account') !=0) ? $this->input->post('bank_account'): $this->accounting_setting->default_cash;
                $amount_paying = $this->input->post('amount-paid');
                if($amount_paying > (-1 * $grand_total)){
                    $accTrans[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $pay_ref,
                        'account_code' => $this->accounting_setting->other_income,
                        'amount'       => ($amount_paying - (-1 * $grand_total)),
                        'narrative'    => $this->site->getAccountName($this->accounting_setting->other_income),
                        'description'  => $note,
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'people_id'    => $this->session->userdata('user_id'),
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                    $accTrans[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $pay_ref,
                        'account_code' => $paying_to,
                        'amount'       => -($amount_paying),
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $note,
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'people_id'    => $this->session->userdata('user_id'),
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                } else {
                    if($amount_paying < (-1 * $grand_total)) {
                        $accTrans[] = array(
                            'tran_type'     => 'Payment',
                            'tran_date'     => $date,
                            'reference_no'  => $pay_ref,
                            'account_code'  => $this->accounting_setting->default_receivable,
                            'amount'        => -((-1 * $grand_total) - $amount_paying),
                            'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description'   => $note,
                            'biller_id'     => $sale->biller_id,
                            'project_id'    => $sale->project_id,
                            'customer_id'   => $sale->customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    $accTrans[] = array(
                        'tran_type'    => 'Payment',
                        'tran_date'    => $date,
                        'reference_no' => $pay_ref,
                        'account_code' => $paying_to,
                        'amount'       => -($this->input->post('amount-paid')),
                        'narrative'    => $this->site->getAccountName($paying_to),
                        'description'  => $note,
                        'biller_id'    => $sale->biller_id,
                        'project_id'   => $sale->project_id,
                        'people_id'    => $this->session->userdata('user_id'),
                        'customer_id'  => $sale->customer_id,
                        'created_by'   => $this->session->userdata('user_id'),
                    );
                }
                //------close accounting------
            } else {
                $accTrans[] = array(
                    'tran_type'     => 'Payment',
                    'tran_date'     => $date,
                    'reference_no'  => $this->site->getReference('pay'),
                    'account_code'  => $this->accounting_setting->default_receivable,
                    'amount'        => $grand_total,
                    'narrative'     => $this->site->getAccountName($this->accounting_setting->default_receivable),
                    'description'   => 'Due '. $grand_total,
                    'biller_id'     => $sale->biller_id,
                    'project_id'    => $sale->project_id,
                    'customer_id'   => $sale->customer_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                );
                $payment = [];
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
            // $this->bpas->print_arrays($data, $products, $si_return, $payment);
        }
        if ($this->form_validation->run() == true && $this->sales_store_model->addSale($data, $products, $payment, $si_return, $accTrans, null, null, $commission_product, $store_items)) {
            $this->session->set_flashdata('message', lang('return_sale_added'));
            admin_redirect($sale->pos ? 'pos/sales' : 'sales_store');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $sale;
            if ($this->data['inv']->sale_status != 'consignment' && $this->data['inv']->sale_status != 'completed') {
                $this->session->set_flashdata('error', lang('sale_already_return_items_completed'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            if ($sale->return_id) {
                if (!$this->sales_model->checkReturned($sale->id)){
                    $inv_items = $this->sales_model->getSaleBalance_Items($id);
                }
            }
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
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
                $row->sale_item_id    = $item->id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->unit            = $item->product_unit_id;
                
                // $row->qty             = $item->quantity;
                // $row->oqty            = $item->quantity;
                $row->qty             = $item->unit_quantity;
                $row->oqty            = $item->unit_quantity;
                $row->discount        = $item->discount ? $item->discount : '0';

                // $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity));
                // $row->unit_price      = $row->tax_method ? $item->unit_price + $this->bpas->formatDecimal($item->item_discount / $item->quantity) + $this->bpas->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                $row->price           = $this->bpas->formatDecimal($item->net_unit_price + $this->bpas->formatDecimal($item->item_discount / (isset($item->real_saleItem_quantity) ? $item->real_saleItem_quantity : $item->quantity)));
                $row->unit_price      = $row->tax_method ? 
                                        ($item->unit_price + $this->bpas->formatDecimal($item->item_discount / (isset($item->real_saleItem_quantity) ? $item->real_saleItem_quantity : $item->quantity)) + $this->bpas->formatDecimal($item->item_tax / (isset($item->real_saleItem_quantity) ? $item->real_saleItem_quantity : $item->quantity))) : 
                                        $item->unit_price + ($item->item_discount / (isset($item->real_saleItem_quantity) ? $item->real_saleItem_quantity : $item->quantity));
                
                $row->real_unit_price = $item->real_unit_price;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = $item->serial_no;
                $row->option          = $item->option_id;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                $units                = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                $ri                   = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];
                $c++;
            }
            $this->data['id']           = $id;
            $this->data['inv_items']    = json_encode($pr);
            $this->data['billers']      = $this->site->getAllCompanies('biller');
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['agencies']     = $this->site->getAllUsers();
            $this->data['customers']    = $this->site->getCustomers();
            $this->data['currency']     = $this->site->getCurrency();
            $this->data['reference']    = $this->site->getReference('re');
            $this->data['payment_ref']  = $this->site->getReference('pp');
            $this->data['tax_rates']    = $this->site->getAllTaxRates();
            $this->data['setting']      = $this->site->get_setting();
            $this->data['bankAccounts'] =  $this->site->getAllBankAccounts();
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_store'), 'page' => lang('sales_store')], ['link' => '#', 'page' => lang('return_sale_store')]];
            $meta = ['page_title' => lang('return_sale_store'), 'bc' => $bc];
            $this->page_construct('sales_store/return_sale', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions('delete', true, 'store_sales');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_store_model->getInvoiceByID($id);
        if (empty($inv)) {
            $this->session->set_flashdata('error', lang('unable_to_deleted'));
            admin_redirect('sales_store');
        }
        if ($inv->sale_status == 'returned') {
            $this->bpas->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
        }
        if($inv->return_id){
            $this->bpas->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            if($this->Settings->hide != 0){
                if ($this->sales_store_model->deleteSale($id)) {
                    if ($this->input->is_ajax_request()) {
                        $this->bpas->send_json(['error' => 0, 'msg' => lang('sale_deleted')]);
                    }
                    $this->session->set_flashdata('message', lang('sale_deleted'));
                    admin_redirect('welcome');
                }
            } else {
                if ($this->sales_store_model->removeSale($id)) {
                    if ($this->input->is_ajax_request()) {
                        $this->bpas->send_json(['error' => 0, 'msg' => lang('sale_removed')]);
                    }
                    $this->session->set_flashdata('message', lang('sale_removed'));
                    admin_redirect('welcome');
                }   
            }
        }
    }

    public function suggestions($pos = 0)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed       = $this->bpas->analyze_term($term);
        $sr             = $analyzed['term'];
        $option_id      = $analyzed['option_id'];
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        // $customer       = $this->site->getCompanyByID($customer_id);
        // $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->sales_model->getProductNames($sr, $warehouse_id, $pos);

        if ($rows) {
            $r = 0; $pr = array();
            foreach ($rows as $row) {
                $promotions = $this->promos_model->getPromotionByProduct($warehouse_id, $row->category_id);
                $discount_promotion = 0;
                if($promotions){
                    foreach ($promotions as $promotion) {
                        $discount_promotion = $promotion->discount;
                    }
                }
                $cate_id = $row->subcategory_id?$row->subcategory_id:$row->category_id;
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        =  $discount_promotion;
                $row->serial          = '';
                $options              = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                $product_options      = $this->site->getAllProductOption($row->id);
                    
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                $set_price   = $this->site->getUnitByProId($row->id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
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
                // if ($this->bpas->isPromo($row)) {
                //     $row->price = $row->promo_price;
                // } elseif ($customer->price_group_id) {
                //     if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                //         $row->price = $pr_group_price->price;
                //     }
                // } elseif ($warehouse->price_group_id) {
                //          if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                //         $row->price = $pr_group_price->price;
                //     }
                // }
                // $row->price           = $row->price + (($row->price * $customer_group->percent) / 100);

                $row->new_entry       = 1;
                $row->price           = $row->price;
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                $categories           = $this->site->getCategoryByID($cate_id);
                $fiber_type           = $this->sales_model->getFiberTypeById($row->id);
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }

                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $fibers   = array('fiber' => $categories, 'type' => $fiber_type, );
                $pr[]     = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id,
                'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,  'set_price' => $set_price, 'units' => $units, 'options' => $options, 'fiber' => $fibers,'product_options' => $product_options, ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function getWarehouseByBiller() 
    {
        $biller_id = $this->input->get('biller_id');
        $biller    = $this->sales_store_model->getWarehouseByBiller($biller_id);
        $this->bpas->send_json($biller);
    }

    public function modal_view($id = null, $logo = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);

        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['currency']    = $this->site->getCurrencyByCode($inv->currency);
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['islogo']      = $logo;
        $this->data['sold_by']     = $this->site->getsaleman($inv->saleman_by);
        $this->data['TotalSalesDue'] = $this->sales_model->getTotalSalesDue($inv->customer_id,'');
        $this->load->view($this->theme . 'sales_store/modal_view', $this->data);
    }

    public function view($id = null, $issue_inv = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->get('issue_inv')) {
            $issue_inv = $this->input->get('issue_inv');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $inv_down_payment          = $this->sales_model->getInvoicedownPaymentstatus($id);
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['issue_inv']   =$issue_inv;
        $this->data['down_payment']= $inv_down_payment;
        $this->data['get_all_down_payments'] = $this->sales_model->getInvoicedownPayments($id);
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['KHM']         = $this->bpas->getExchange_rate('KHR');
        $this->data['BAT']         = $this->bpas->getExchange_rate('THB');
        $this->data['TotalSalesDue'] = $this->sales_model->getTotalSalesDue($inv->customer_id,'');
             
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales_store'), 'page' => lang('sales_store')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales_store/view', $meta, $this->data);
    }

    public function view_a5($id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;

        $this->load->view($this->theme . 'sales_store/view_a5', $this->data);
    }

    public function view_a4($id = null)
    {
        $this->bpas->checkPermissions('index', true, 'store_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;

        $this->load->view($this->theme . 'sales_store/view_a4', $this->data);
    }

    public function view_dental_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by']  = $this->site->getUser($payment->created_by);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = lang('view_dental_payment');
        $this->load->view($this->theme . 'sales_store/view_dental_payment', $this->data);
    }

    public function packaging($id)
    {
        $sale                   = $this->sales_model->getInvoiceByID($id);
        $this->data['returned'] = false;
        if ($sale->sale_status == 'returned' || $sale->return_id) {
            $this->data['returned'] = true;
        }
        $this->data['warehouse'] = $this->site->getWarehouseByID($sale->warehouse_id);
        $items                   = $this->sales_model->getAllInvoiceItems($sale->id);
        foreach ($items as $item) {
            $packaging[] = [
                'name'     => $item->product_code . ' - ' . $item->product_name,
                'quantity' => $item->quantity,
                'unit'     => $item->product_unit_code,
                'rack'     => $this->sales_model->getItemRack($item->product_id, $sale->warehouse_id),
            ];
        }
        $this->data['packaging'] = $packaging;
        $this->data['sale']      = $sale;

        $this->load->view($this->theme . 'sales_store/packaging', $this->data);
    }

    public function add_delivery($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->sale_status != 'completed') {
            $this->session->set_flashdata('error', lang('status_is_x_completed'));
            $this->bpas->md();
        }

        if ($delivery = $this->sales_model->getDeliveryBySaleID($id)) {
            $this->edit_delivery($delivery->id);
        } else {
            $this->form_validation->set_rules('sale_reference_no', lang('sale_reference_no'), 'required');
            $this->form_validation->set_rules('customer', lang('customer'), 'required');
            $this->form_validation->set_rules('address', lang('address'), 'required');

            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                    $date = $this->bpas->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $dlDetails = [
                    'date'              => $date,
                    'sale_id'           => $this->input->post('sale_id'),
                    'do_reference_no'   => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer'          => $this->input->post('customer'),
                    'address'           => $this->input->post('address'),
                    'status'            => $this->input->post('status'),
                    'delivered_by'      => $this->input->post('delivered_by'),
                    'received_by'       => $this->input->post('received_by'),
                    // 'note'              => $this->bpas->clear_tags($this->input->post('note')),
                    'note'              => $this->input->post('note'),
                    'created_by'        => $this->session->userdata('user_id'),
                    'biller_id'         => $sale->biller_id,
                    'money_collection'  => $this->input->post('collection_status'),
                    'collection_amount'  => $this->input->post('collection_amount'),
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
                    $photo                   = $this->upload->file_name;
                    $dlDetails['attachment'] = $photo;
                }
            } elseif ($this->input->post('add_delivery')) {
                if ($sale->shop) {
                    $this->load->library('sms');
                    $this->sms->delivering($sale->id, $dlDetails['do_reference_no']);
                }
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }
            if ($this->form_validation->run() == true && $this->sales_model->addDelivery($dlDetails)) {
                $this->session->set_flashdata('message', lang('delivery_added'));
                admin_redirect('sales/deliveries');
            } else {
                $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['customer']        = $this->site->getCompanyByID($sale->customer_id);
                $this->data['address']         = $this->site->getAddressByID($sale->address_id);
                $this->data['inv']             = $sale;
                $this->data['do_reference_no'] = $this->site->getReference('do');
                $this->data['modal_js']        = $this->site->modal_js();
                $this->data['drivers']  = $this->site->getDriver();
                $this->load->view($this->theme . 'deliveries/add_delivery', $this->data);
            }
        }
    }

    public function edit_delivery($id = null)
    {
        $this->bpas->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('do_reference_no', lang('do_reference_no'), 'required');
        $this->form_validation->set_rules('sale_reference_no', lang('sale_reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('address', lang('address'), 'required');

        if ($this->form_validation->run() == true) {
            $dlDetails = [
                'sale_id'           => $this->input->post('sale_id'),
                'do_reference_no'   => $this->input->post('do_reference_no'),
                'sale_reference_no' => $this->input->post('sale_reference_no'),
                'customer'          => $this->input->post('customer'),
                'address'           => $this->input->post('address'),
                'status'            => $this->input->post('status'),
                'delivered_by'      => $this->input->post('delivered_by'),
                'received_by'       => $this->input->post('received_by'),
                'note'              => $this->bpas->clear_tags($this->input->post('note')),
                'created_by'        => $this->session->userdata('user_id'),
                'money_collection'  => $this->input->post('collection_status'),
                'collection_amount'  => $this->input->post('collection_amount'),
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
                $photo                   = $this->upload->file_name;
                $dlDetails['attachment'] = $photo;
            }

            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date              = $this->bpas->fld(trim($this->input->post('date')));
                $dlDetails['date'] = $date;
            }
        } elseif ($this->input->post('edit_delivery')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails)) {
            $this->session->set_flashdata('message', lang('delivery_updated'));
            admin_redirect('sales_store/deliveries');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['drivers']  = $this->site->getDriver();
            $this->data['get_driver'] = $this->sales_model->getDriverByID($id);
            $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'deliveries/edit_delivery', $this->data);
        }
    }

    public function deliveries()
    {
        $this->bpas->checkPermissions();

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('deliveries')]];
        $meta          = ['page_title' => lang('deliveries'), 'bc' => $bc];
        $this->page_construct('deliveries/deliveries', $meta, $this->data);
    }

    public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');
        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->bpas->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang('deliveries_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->sales_model->getDeliveryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->bpas->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->sale_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($delivery->status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_delivery_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function payments($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        $this->data['payments'] = $this->sales_store_model->getInvoicePayments($id);
        $this->data['inv']      = $this->sales_store_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales_store/payments', $this->data);
    }

    public function add_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->get('payment_term')) {
            $payment_term = $this->input->get('payment_term');
        } else {
            $payment_term = null;
        }

        $sale = $this->sales_model->getInvoiceByID($id);
        $balance= $sale->grand_total - $sale->paid;
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang('sale_already_paid'));
            $this->bpas->md();
        }
        // $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        // $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if($this->Settings->accounting == 1){
            $this->form_validation->set_rules('bank_account', lang('bank_account'), 'required');
        }
        if ($this->form_validation->run() == true) {
            
            if($this->input->post('amount-paid') == '0') {
                $this->session->set_flashdata('error', lang('payment_not_be_zero'));
                $this->bpas->md();
            }
            $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount_paid_usd'), $this->input->post('amount_paid_khr'), $this->input->post('amount_paid_thb'))) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            // $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay');
            $reference_no = $this->site->CheckedPaymentReference($this->input->post('reference_no'), $this->site->getReference('pay'));

            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $reference_no,
                'amount'       => $this->input->post('amount-paid'),
                'amount_usd'   => $this->input->post('amount_paid_usd'),
                'amount_khr'   => $this->input->post('amount_paid_khr'),
                'amount_thb'   => $this->input->post('amount_paid_thb'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => $sale->sale_status == 'returned' ? 'returned' : 'received',
                'bank_account' => $this->input->post('bank_account'),
                'payment_term' => $this->input->post('payment_term') ? $this->input->post('payment_term') : null,
                'write_off'    => $this->input->post('write_off') ? $this->input->post('write_off') : 0,
            ];

            //=====add accounting=====//
            if($this->Settings->accounting == 1){

                if($this->input->post('write_off')){
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => -($this->input->post('amount-paid')),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $sale->customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_write_off,
                        'amount' => $this->input->post('amount-paid'),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_write_off),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $sale->customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0, // 1= bussiness, 2 = investing, 3= financing activity
                    );
                }else{
                    if ($this->input->post('amount-paid') > $balance) {
                        $accTranPayments[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($balance),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );

                        $other_amount = $this->input->post('amount-paid') - $balance;
                        $accTranPayments[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->other_income,
                            'amount' => -($other_amount),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->other_income),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 1, // 1= bussiness, 2 = investing, 3= financing activity
                        );
                    }else{
                        $amount = $this->input->post('amount-paid');
                        $accTranPayments[] = array(
                            'tran_no'   => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($this->input->post('amount-paid')),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    }
                    if($this->input->post('paid_by') == 'deposit'){
                        $paying_to = isset($this->accounting_setting->default_sale_deposit) ? $this->accounting_setting->default_sale_deposit : '';
                    }else{
                        $paying_to = $this->input->post('bank_account');
                    }
                    $accTranPayments[] = array(
                        'tran_no'   => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $paying_to,
                        'amount' => $this->input->post('amount-paid'),
                        'narrative' => $this->site->getAccountName($paying_to),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'people_id' => $this->session->userdata('user_id'),
                        'customer_id' => $sale->customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 1, // 1= bussiness, 2 = investing, 3= financing activity
                    );
                }
            }
                //=====end accounting=====//
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
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id, $accTranPayments)) {
            if ($sale->shop) {
                $this->load->library('sms');
                $this->sms->paymentReceived($sale->id, $payment['reference_no'], $payment['amount']);
            }
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->bpas->md();
            }
            $this->data['inv']             = $sale;
            $this->data['payment_term']    = $payment_term;
            $this->data['payments_groupby_term'] = $this->sales_model->getInvoicePaymentsGroupByTerm($sale->id);
            $this->data['payments']        = $this->sales_model->getInvoicePayments($sale->id);
            $this->data['deposit']         = $this->site->getCustomerDeposit($sale->customer_id);
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['currency_riel']   = $this->site->getCurrencyByCode('KHR');
            $this->data['currency_baht']   = $this->site->getCurrencyByCode('THB');
            $this->data['payment_ref']     = $this->site->getReference('pay');
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'sales_store/add_payment', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->bpas->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->input->get('payment_term')) {
            $payment_term = $this->input->get('payment_term');
        } else {
            $payment_term = null;
        }

        $sale_id = $this->input->post('sale_id');
        $payment = $this->sales_model->getPaymentByID($id);
        $sale    = $this->sales_model->getInvoiceByID($payment->sale_id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'paypal' || $payment->paid_by == 'skrill') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->bpas->md();
        }
        
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $last_payment    = $payment->amount;
            if ($this->input->post('paid_by') == 'deposit') {
                $sale        = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                $amount_usd  = $this->input->post('amount_paid_usd') - $payment->amount_usd;
                $amount_khr  = $this->input->post('amount_paid_khr') - $payment->amount_khr;
                $amount_thb  = $this->input->post('amount_paid_thb') - $payment->amount_thb;
                if (!$this->site->check_customer_deposit($customer_id, $amount_usd, $amount_khr, $amount_thb)) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay');
            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'amount_usd'   => $this->input->post('amount_paid_usd'),
                'amount_khr'   => $this->input->post('amount_paid_khr'),
                'amount_thb'   => $this->input->post('amount_paid_thb'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'bank_account' => $this->input->post('bank_account'),
                'write_off'    => $this->input->post('write_off')?$this->input->post('write_off'):0
            ];

            //=====add accounting=====//
            if($this->Settings->accounting == 1){
                $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $balance= $sale->grand_total - ($sale->paid - $last_payment);
                if($this->input->post('write_off')){
                    $accTranPayments[] = array(
                        'tran_no' => $sale_id,
                        'payment_id' => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_receivable,
                        'amount' => -($this->input->post('amount-paid')),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                    );
                    $accTranPayments[] = array(
                        'tran_no'   => $sale_id,
                        'payment_id' => $id,
                        'tran_type' => 'Payment',
                        'tran_date' => $date,
                        'reference_no' => $reference_no,
                        'account_code' => $this->accounting_setting->default_write_off,
                        'amount' => $this->input->post('amount-paid'),
                        'narrative' => $this->site->getAccountName($this->accounting_setting->default_write_off),
                        'description' => $this->input->post('note'),
                        'biller_id' => $sale->biller_id,
                        'project_id' => $sale->project_id,
                        'customer_id' => $sale->customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => 0, // 1= bussiness, 2 = investing, 3= financing activity
                    );
                } else {
                    /*if ($this->input->post('amount-paid') > $balance) {
                        $accTranPayments[] = array(
                            'tran_no' => $sale_id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($balance),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );

                        $other_amount = $this->input->post('amount-paid') - $balance;
                        $accTranPayments[] = array(
                            'tran_no' => $sale_id,
                            'tran_type' => 'Sale',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->other_income,
                            'amount' => -($other_amount),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->other_income),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'people_id' => $this->session->userdata('user_id'),
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                    } else {*/
                        $amount = $this->input->post('amount-paid');
                        $accTranPayments[] = array(
                            'tran_no' => $sale_id,
                            'payment_id' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $this->accounting_setting->default_receivable,
                            'amount' => -($this->input->post('amount-paid')),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_receivable),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_receivable)
                        );
                    // }
                        if ($this->input->post('paid_by') == 'deposit') {
                            $paying_to = $this->accounting_setting->default_sale_deposit;
                        } else {
                            $paying_to = ($this->input->post('bank_account') !=0) ? $this->input->post('bank_account'): $this->accounting_setting->default_cash ;
                        }
                        $accTranPayments[] = array(
                            'tran_no' => $sale_id,
                            'payment_id' => $id,
                            'tran_type' => 'Payment',
                            'tran_date' => $date,
                            'reference_no' => $reference_no,
                            'account_code' => $paying_to,
                            'amount' => $this->input->post('amount-paid'),
                            'narrative' => $this->site->getAccountName($paying_to),
                            'description' => $this->input->post('note'),
                            'biller_id' => $sale->biller_id,
                            'project_id' => $sale->project_id,
                            'customer_id' => $sale->customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => 1 // 1= bussiness, 2 = investing, 3= financing activity
                        );
                    }
                }
                //=====end accounting=====//
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
                    $photo                 = $this->upload->file_name;
                    $payment['attachment'] = $photo;
                }
            } elseif ($this->input->post('edit_payment')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('sales_store');
        } else {
            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv']             = $sale;
            $this->data['payment']         = $payment;
            $this->data['payment_term']    = $payment_term;
            $this->data['deposit']         = $this->site->getCustomerDeposit($sale->customer_id);
            $this->data['currency_dollar'] = $this->site->getCurrencyByCode('USD');
            $this->data['currency_riel']   = $this->site->getCurrencyByCode('KHR');
            $this->data['currency_baht']   = $this->site->getCurrencyByCode('THB');
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'sales_store/edit_payment', $this->data);
        }
    }

    public function email_payment($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment              = $this->sales_model->getPaymentByID($id);
        $inv                  = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $customer             = $this->site->getCompanyByID($inv->customer_id);
        if (!$customer->email) {
            $this->bpas->send_json(['msg' => lang('update_customer_email')]);
        }
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['customer']   = $customer;
        $this->data['page_title'] = lang('payment_note');
        $html                     = $this->load->view($this->theme . 'sales/payment_note', $this->data, true);
        $html = str_replace(['<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>' . lang('stamp_sign') . '</p>'], '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
            // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = [
            'stylesheet' => '<link href="' . $this->data['assets'] . 'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name'       => $customer->company && $customer->company != '-' ? $customer->company : $customer->name,
            'email'      => $customer->email,
            'heading'    => lang('payment_note') . '<hr>',
            'msg'        => $html,
            'site_link'  => base_url(),
            'site_name'  => $this->Settings->site_name,
            'logo'       => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
        ];
        $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->bpas->send_email($customer->email, $subject, $message)) {
            $this->bpas->send_json(['msg' => lang('email_sent')]);
        } else {
            $this->bpas->send_json(['msg' => lang('email_failed')]);
        }
    }

    public function payment_note($id = null)
    {
        $this->bpas->checkPermissions('payments', true);
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = lang('payment_note');

        $this->load->view($this->theme . 'sales_store/payment_note', $this->data);
    }

    public function maintenance($warehouse_id = null) 
    {
        $this->bpas->checkPermissions();
        $count = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            if (count($count) > 1) {
                $this->data['warehouses']   =  $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }

        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $biller_id = $this->session->userdata('biller_id');
        $this->data['users']        = $this->site->getStaff();
        $this->data['products']     = $this->site->getProducts();
        $this->data['warehouses']   = $this->site->getAllWarehouses();
        $this->data['billers']      = $this->site->getAllCompanies('biller');
        $this->data['user_billers'] = $this->site->getCompanyByID($biller_id);
        $this->data['drivers']      = $this->site->getDriver();
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('maintenance')]];
        $meta = ['page_title' => lang('maintenance'), 'bc' => $bc];
        $this->page_construct('sales_store/maintenance_list', $meta, $this->data);
    }  

    public function add_maintenance($id = null)
    {
        $this->bpas->checkPermissions('maintenance', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);

        if ($maintenace = $this->sales_model->getMaintenanceBySaleID($id)) {
            $this->edit_maintenance($maintenace->id);
        } else {
            $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
            $this->form_validation->set_rules('maintenance_date', lang('maintenance_date'), 'required');
            $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
            if ($this->form_validation->run() == true) {
               // $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                    $date = $this->bpas->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                    
                }
                $month_value = '+'.$this->input->post('maintenance_date').'';
                $start = strtotime($date);
                $start = strtotime($month_value, $start);
                $payment = [
                    'date'              => $date,
                    'sale_id'           => $this->input->post('sale_id'),
                    'customer_id'       => $customer_id,
                    'reference_no'      => $this->input->post('reference_no'),
                    'maintenance_date'  => $this->bpas->fsd($this->input->post('maintenance_date')),
                    'note'              => $this->input->post('note'),
                    'created_by'        => $this->session->userdata('user_id'),
                    'status'            => $this->input->post('term'),
                    'type'              => $this->input->post('type'),
                    'term'              => $this->input->post('term'),
                ];

                //=====end accounting=====//
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
                    $photo                 = $this->upload->file_name;
                    $payment['attachment'] = $photo;
                }

            } elseif ($this->input->post('add_maintenance')) {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }

            if ($this->form_validation->run() == true && 
                $this->sales_model->addMaintenance($payment, $customer_id)) {
                $this->session->set_flashdata('message', lang('maintenance_added'));
                admin_redirect('sales_store/maintenance');
            } else {
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['maintenance'] = $this->sales_model->getMaintenanceByID($id);
                $this->data['inv']         = $sale;
                $this->data['mainta_ref']  = $this->site->getReference('main');
                $this->data['modal_js']    = $this->site->modal_js();
                $this->load->view($this->theme . 'sales_store/add_maintenance', $this->data);
            }
        }
    }

    public function edit_maintenance($id = null)
    {
        $this->bpas->checkPermissions('maintenance', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $maintenance =$this->sales_model->getMaintenanceByID($id);
        $sale = $this->sales_model->getInvoiceByID($maintenance->sale_id);
        $this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('maintenance_date', lang('maintenance_date'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $customer_id = $sale->customer_id;
            if ($this->Owner || $this->Admin || $this->GP['change_date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $month_value = '+'.$this->input->post('maintenance_date').'';
            $start = strtotime($date);
            $start = strtotime($month_value, $start);
            $maintenace = [
                'date'              => $date,
                'sale_id'           => $this->input->post('sale_id'),
                'customer_id'       => $customer_id,
                'reference_no'      => $this->input->post('reference_no'),
                'maintenance_date'  => $this->bpas->fsd($this->input->post('maintenance_date')),
                'note'              => $this->input->post('note'),
                'created_by'        => $this->session->userdata('user_id'),
                'status'            => $this->input->post('term'),
                'maintenance_status'=> $this->input->post('maintenance_status'),
                'type'              => $this->input->post('type'),
                'term'              => $this->input->post('term'),
            ];
            //=====end accounting=====//
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
                $photo                 = $this->upload->file_name;
                $maintenace['attachment'] = $photo;
            }
        } elseif ($this->input->post('add_maintenance')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->UpdateMaintenance($maintenace, $id)) {
            $this->session->set_flashdata('message', lang('maintenance_updated'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['maintenance'] = $this->sales_model->getMaintenanceByID($id);
            $this->data['inv']         = $maintenance;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();
            $this->load->view($this->theme . 'sales_store/edit_maintenance', $this->data);
        }
    }

    public function email($id = null)
    {
        $this->bpas->checkPermissions(false, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('to', lang('to') . ' ' . lang('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', lang('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang('message'), 'trim');
        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->bpas->view_rights($inv->created_by);
            }
            $to       = $this->input->post('to');
            $subject  = $this->input->post('subject');
            $cc       = $this->input->post('cc') ? $this->input->post('cc') : null;
            $bcc      = $this->input->post('bcc') ? $this->input->post('bcc') : null;
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $customer->name,
                'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                'order_link'       => $inv->shop ? shop_url('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash)) : base_url(),
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg      = $this->input->post('note');
            $message  = $this->parser->parse_string($msg, $parse_data);
            $paypal   = $this->sales_model->getPaypalSettings();
            $skrill   = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . admin_url('sales/view/' . $inv->id) . '&cancel_return=' . admin_url('sales/view/' . $inv->id) . '&notify_url=' . admin_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            }
            if ($skrill->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . admin_url('sales/view/' . $inv->id) . '&cancel_url=' . admin_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->bpas->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . admin_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code  .= '<div class="clearfix"></div></div>';
            $message    = $message . $btn_code;
            $attachment = $this->pdf($id, null, 'S');
            try {
                if ($this->bpas->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->session->set_flashdata('message', lang('email_sent'));
                    admin_redirect('sales_store');
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/admin/views/email_templates/sale.html');
            }

            $this->data['subject'] = [
                'name'  => 'subject',
                'id'    => 'subject',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            ];
            $this->data['note'] = [
                'name'  => 'note',
                'id'    => 'note',
                'type'  => 'text',
                'value' => $this->form_validation->set_value('note', $sale_temp),
            ];
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales_store/email', $this->data);
        }
    }

    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

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

        $name = lang('sale') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales_store/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales_store/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->bpas->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->bpas->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        }
    }
}