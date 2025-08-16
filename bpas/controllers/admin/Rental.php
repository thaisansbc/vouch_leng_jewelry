<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rental extends MY_Controller
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
        $this->load->admin_model('quotes_model');
        $this->load->admin_model('sales_order_model');
        $this->load->admin_model('accounts_model');
        $this->load->admin_model('deliveries_model'); 
        
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    public function index($biller_id = null)
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
        $this->data['alert_id'] = isset($_GET['alert_id']) ? ($_GET['alert_id'] !== '' ? $_GET['alert_id'] : 'empty')  : null;
        $this->data['users']            = $this->site->getStaff();
        $this->data['products']         = $this->site->getProducts();
        $this->data['warehouses']       = $this->site->getAllWarehouses();
        $this->data['count_warehouses'] = explode(',', $this->session->userdata('warehouse_id'));
        $this->data['drivers']          = $this->site->getDriver();

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('rental')]];
        $meta = ['page_title' => lang('rental'), 'bc' => $bc];
        $this->page_construct('rental/index', $meta, $this->data);
    }

    public function getRental($biller_id = null)
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

        $view_logo        = anchor('admin/sales/modal_view/$1/logo', '<i class="fa fa-money"></i> ' . lang('print_with_logo'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $detail_link          = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $down_payments_link   = anchor('admin/rental/deposits/$1', '<i class="fa fa-money"></i> ' . lang('view_deposit'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_Downpayment_link = anchor('admin/rental/add_deposit/$1', '<i class="fa fa-money"></i> ' . lang('add_deposit'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $return_detail_link   = anchor('admin/sales/return_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('return_sale') . ' ' . lang('details'));
        $duplicate_link       = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link        = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $add_payment_link     = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');

        $add_credit_note_link = anchor('admin/sales/add_credit_note/$1', '<i class="fa fa-truck"></i> ' . lang('add_credit_note'));
        $email_link           = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $edit_link            = anchor('admin/rental/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'));

        $pdf_link             = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link          = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $add_warranty_link    = anchor('admin/sales/add_maintenance/$1', '<i class="fa fa-money"></i> ' . lang('add_maintenance'), 'data-toggle="modal" data-backdrop="static" data-target="#myModal"');
        $delete_link          = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
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
                <li class="add_payment">' . $add_payment_link . '</li>
                <li class="">' . $down_payments_link . '</li>
                <li class="add_downpayment">' . $add_Downpayment_link . '</li>
                <li>' . $installment_link . '</li>
                <li class="edit">' . $edit_link . '</li>
                <li>' . $add_credit_note_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $return_link . '</li>
                <li class="delete">' . $delete_link . '</li>
            </ul>
        </div></div>';

        $ds = "( SELECT d.sale_id,d.delivered_by,d.status,c.name as delivery_name
        from {$this->db->dbprefix('deliveries')} d LEFT JOIN {$this->db->dbprefix('companies')} c 
        on d.delivered_by = c.id GROUP BY d.sale_id) FSI";
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

            IFNULL(payments.deposit,0) as deposit,

            {$this->db->dbprefix('sales')}.paid, 
            ({$this->db->dbprefix('sales')}.grand_total - {$this->db->dbprefix('sales')}.paid) as balance,
            {$this->db->dbprefix('sales')}.payment_status, 
            {$this->db->dbprefix('sales')}.return_id")
        ->join('projects', 'sales.project_id = projects.project_id', 'left')
        ->join('sales_order', 'sales.so_id = sales_order.id', 'left')
        ->join('users', 'sales.saleman_by = users.id', 'left')
        ->join($ds, 'FSI.sale_id=sales.id', 'left')

        ->join('
            (select sum(amount) as deposit,sale_id 
            from '.$this->db->dbprefix('payments').' 
            where sale_id > 0 AND transaction ="SaleDeposit" 
            GROUP BY sale_id) as payments','payments.sale_id = sales.id','left')

        ->order_by('sales.id', 'desc')
        ->from('sales')
        ->where('sales.store_sale !=', 1);

        $this->datatables->where('sales.module_type','rental');
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
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }
        /*
        if ($this->input->get('delivery') == 'no') {
            $this->datatables->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
            ->where("({$this->db->dbprefix('deliveries')}.status != 'delivered' OR {$this->db->dbprefix('deliveries')}.status IS NULL)", null);
        }*/
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
        /*
        if ($delivered_by) {
            $this->datatables->where('deliveries.delivered_by', $delivered_by);
        }*/
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

    public function add($sale_order_id = null, $quote_id = null, $delivery_id = null)
    {   
        $this->bpas->checkPermissions();
        $sale_id         = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;
        $getexchange_khm = $this->bpas->getExchange_rate('KHR');
        $getexchange_bat = $this->bpas->getExchange_rate('THB');
        $exchange_khm    = $getexchange_khm->rate;
        $exchange_bat    = isset($getexchange_bat->rate) ?$getexchange_bat->rate:'';
        if(isset($sale_order_id)){
            $sale_o = $this->sales_order_model->getSaleOrder($sale_order_id);
            if(isset($sale_o)){
                if($sale_o->order_status == 'pending'){
                    $this->session->set_flashdata('error', lang("sale_order_n_approved"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
                if($sale_o->order_status == 'rejected'){
                    $this->session->set_flashdata('error', lang("sale_order_has_been_rejected"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
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
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin || $this->GP['change_invoiceNo']) {
                // it is dubplicate $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
                $reference = $this->site->CheckedSaleReference($this->input->post('reference_no'), $this->site->getReference('ren'));
            } else {
                $reference = $this->site->getReference('ren');
            }
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
            // $total_weight     = 0;
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
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] : '';
                $item_weight        = $_POST['product_weight'][$r];
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                $item_expiry        = isset($_POST['product_expiry'][$r]) ? $_POST['product_expiry'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    // $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByID($item_id) : null;
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
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity);
                    }
                    $product_tax += $pr_item_tax;
                    $subtotal     = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit         = $this->site->getUnitByID($item_unit);
                    $total_weight = number_format((float) ($item_weight * $item_unit_quantity), 4, '.', '');
                    $getitems     = $this->site->getProductByID($item_id);
                    $commission_item = $this->site->getProductCommissionByID($getitems->id);
                    $purchase_unit_cost = $product_details->cost;
                    $saleman = $this->site->getuser($this->input->post('saleman_by'));
                    if($commission_item && $saleman){
                        $commission_product += $commission_item->price * $item_quantity;
                    }
                    if ($unit->id != $product_details->unit) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost,$unit);
                    } else {
                        $cost = $cost;
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
                        'expiry'            => $item_expiry,
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
                        'weight'            => $item_weight,
                        'total_weight'      => $total_weight,
                        'comment'           => $item_detail,
                        'commission'        => isset($commission_item->price) ? $commission_item->price * $item_quantity : 0,
                    ];
                    $text_items .=  $r+1 . "/ " . $item_name . "(".$item_code.")" ." | ". $item_quantity." | ".$this->bpas->formatDecimal($real_unit_price) ." | ". $pr_item_discount ." | ". $this->bpas->formatDecimal($subtotal)."\n";
                    //========add accounting=========//
                    $productAcc = $this->site->getProductAccByProductId($item_id);
                    if($this->Settings->module_account == 1 && $item_type != 'manual' && ($sale_status=='completed')){
                        $getproduct    = $this->site->getProductByID($item_id);
                        $default_sale  = ($productAcc->revenue_account) ? $productAcc->revenue_account: $this->accounting_setting->default_sale;
                        $inventory_acc = $this->accounting_setting->default_stock;
                        $costing_acc   = $this->accounting_setting->default_cost;
                        $product_tax   = $this->bpas->formatDecimal($product_tax);
                        $accTrans[] = array(
                            'tran_type'     => 'Sale',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => -($product_tax >0 ? ($subtotal-$product_tax) : $subtotal),
                            'narrative'     => $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'customer_id'   => $customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
                        );
                        if($product_tax > 0){
                            $accTrans[] = array(
                                'tran_type'     => 'Sale',
                                'tran_date'     => $date,
                                'reference_no'  => $reference,
                                'account_code'  => $this->accounting_setting->default_sale_tax,
                                'amount'        => -($product_tax),
                                'narrative'     => $this->site->getAccountName($this->accounting_setting->default_sale_tax),
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'customer_id'   => $customer_id,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                        }
                        if ($this->Settings->ar_in_product) {
                            $ar_account  = ($productAcc->revenue_account) ? $productAcc->ar_account : $this->accounting_setting->default_sale;
                            $accTrans[] = array(
                                'tran_type'     => 'Sale',
                                'tran_date'     => $date,
                                'reference_no'  => $reference,
                                'account_code'  => $ar_account,
                                'amount'        => $subtotal,
                                'narrative'     => $this->site->getAccountName($ar_account),
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'customer_id'   => $customer_id,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                        }
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
            $user  = $this->site->getUser($this->session->userdata('user_id'));
            $staff = $this->site->getUser($this->input->post('saleman_by'));
            if(!empty($staff) && $staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }
            //=======acounting=========//
            if($this->Settings->module_account == 1){
                if($order_discount != 0){
                    $accTrans[] = array(
                        'tran_type'     => 'Sale',
                        'tran_date'     => $date,
                        'reference_no'  => $reference,
                        'account_code'  => $this->accounting_setting->default_sale_discount,
                        'amount'        => $order_discount,
                        'narrative'     => 'Order Discount',
                        'description'   => $note,
                        'biller_id'     => $biller_id,
                        'project_id'    => $project_id,
                        'customer_id'   => $customer_id,
                        'created_by'    => $this->session->userdata('user_id'),
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
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
            }
            //============end accounting=======//
            $data       = [
                'date'                => $date,
                'project_id'          => $this->input->post('project'),
                'from_id'             => $this->input->post('from'),
                'time_out_id'         => $this->input->post('time_out'),
                'destination_id'      => $this->input->post('destination'),
                'so_id'               => $this->input->post('sale_order_id') ? $this->input->post('sale_order_id') : null,
                'delivery_id'         => $this->input->post('delivery_id') ? $this->input->post('delivery_id') : null,
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
                'paid'                => 0, 
                'created_by'          => $this->session->userdata('user_id'),
                'hash'                => hash('sha256', microtime() . mt_rand()),
                'saleman_by'          => $this->input->post('saleman_by'),
                'zone_id'             => $this->input->post('zone_id') ? $this->input->post('zone_id') : null,
                'currency_rate_kh'    => $exchange_khm,
                'currency_rate_bat'   => $exchange_bat,
                'saleman_award_points'=> (!empty($saleman_award_points) && !is_nan($saleman_award_points)) ? $saleman_award_points : 0,
                'diagnosis_id'        => $this->input->post('diagnosis'),
                'module_type'         => 'rental',
                'ar_account'        => $this->input->post('account_receivable'),
            ];
            $payment         = null;
            $accTranPayments = null;
            if ($this->Settings->ar_in_product == 0) {
                $ac_account = $this->input->post('account_receivable') ? $this->input->post('account_receivable') : $this->accounting_setting->default_receivable;
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
                                'account_code' => $ac_account,
                                'amount' => ($grand_total - $amount_paying),
                                'narrative' => $this->site->getAccountName($ac_account),
                                'description' => $this->input->post('payment_note'),
                                'biller_id' => $biller_id,
                                'project_id' => $project_id,
                                'customer_id' => $customer_id,
                                'created_by'  => $this->session->userdata('user_id'),
                                'activity_type' => $this->site->get_activity($ac_account)
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
                        'account_code' => $ac_account,
                        'amount' => $grand_total,
                        'narrative' => $this->site->getAccountName($ac_account),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'activity_type' => $this->site->get_activity($ac_account)
                    );
                }
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
            // $this->bpas->print_arrays($data, $products);
        }
        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return = array(), $accTrans, $accTranPayments, null, $commission_product, 'module_rental')) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            if ($sale_order_id) {
                $status = 'completed';
                $sale_order_id    = $this->input->post('sale_order_id');
                $sale_items       = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                $sale_order_items = $this->site->getSaleOrderItemsBySaleID($sale_order_id);
                foreach($sale_order_items as $item){
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
            if ($delivery_id) {
                $status           = 'completed';
                $delivery_id      = $this->input->post('delivery_id');
                $delivery         = $this->deliveries_model->getDeliveryByID($delivery_id);
                if (!empty($delivery->sale_order_id)) {
                    $sale_items       = $this->site->getSaleItemsByDeliverySaleOrderID($delivery->sale_order_id);
                    $sale_order_items = $this->site->getSaleOrderItemsBySaleID($delivery->sale_order_id);
                    foreach($sale_order_items as $item){
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
                    $this->db->update('sales_order', array('sale_status' => $status), array('id' => $delivery->sale_order_id));
                }
            }
            $t_customer = $this->site->getCompanyByID($customer_id);
            $header = lang("no"). " /     ".lang("name"). "  (".lang("code").")"."    |    ". lang("qty")."    |    ".lang("price") ."    |    ". lang("discount") ."    |    ". lang("total");
            $token      = $this->Settings->token_telegram;
            // $token = '1882945178:AAHI-f7eaHDz8ryCruFeGd3pvdKR3muXeME';
            if($token != "2131"){
                $link = 'https://api.telegram.org:443/bot'.$token.'';
                $getupdate = file_get_contents($link.'/getUpdates');
                $responsearray = json_decode($getupdate, TRUE);
                if($responsearray['result'][0]['message']['chat']['id'] != ""){
                    $chatid = $responsearray['result'][0]['message']['chat']['id'];
                }else{
                    $chatid = $this->Settings->update_id;
                }
                $chatname = $responsearray['result'][0]['message']['chat']['first_name'] ." " . $responsearray['result'][0]['message']['chat']['last_name'];
                $message =  "** CUSTOMER INFO ** " . 
                            "\nCustomer Name: " . $customer .
                            "\nPhone Number: " . $t_customer->phone .
                            "\nAddress: " . $t_customer->address .
                            "\n** SALE INFO **" . 
                            "\nRreference No: " . $reference .
                            "\nTotal Items: " . $total_items .
                            "\nGrand Total: " . $grand_total .
                            "\nDiscount: " . $total_discount .
                            "\nTax: " . $total_tax .
                            "\nTotal: " . $total .
                            "\nPayment Status: " . $payment_status . 
                            "\nSale Status: " . $sale_status.
                            "\n**Create By **" .
                            "\nIP Address: " . $user->last_ip_address .
                            "\nName: " . $user->last_name ." ".$user->first_name .
                            "\nEmail: " . $user->email .
                            "\nCompany: " . $user->company.
                            "\n**ITEMS INFO **" .
                            "\n" . $header .
                            "\n" . $text_items .
                            "\n" . $chatname .
                            "\n" . $token .
                            "\n" . $chatid;
                $parameter = array('chat_id' => $chatid, 'text' => $message);
                $this->bpas->send_Telegram($link,$parameter);
            }
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('rental');
        } else {
            if ($quote_id || $sale_id || $sale_order_id || $delivery_id) {
                if ($quote_id) {
                    $getSaleslist        = $this->quotes_model->getQuoteByID($quote_id);
                    $items               = $this->quotes_model->getAllQuoteItems($quote_id);
                    $sale_items          = [];
                    $q_id                = $quote_id;
                } elseif ($sale_order_id) {
                    $getSaleslist        = $this->sales_order_model->getInvoiceByID($sale_order_id);
                    $items               = $this->sales_order_model->getAllInvoiceItems($sale_order_id);
                    $sale_items          = $this->site->getSaleItemsBySaleOrderID($sale_order_id);
                    $q_id                = $sale_order_id;
                } elseif ($sale_id) {
                    $getSaleslist        = $this->sales_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_model->getAllInvoiceItems($sale_id);
                    $sale_items          = [];
                    $q_id                = $sale_id;
                } elseif ($delivery_id) {
                    $delivery            = $this->deliveries_model->getDeliveryByID($delivery_id);
                    $getSaleslist        = $this->sales_order_model->getInvoiceByID($delivery->sale_order_id);
                    $items               = $this->deliveries_model->getAllDeliveryItems($delivery_id);
                    $sale_items          = [];
                    $q_id                = $delivery_id;
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
                    $pis           = $this->site->getPurchasedItemstoSales($item->product_id, $item->warehouse_id, $item->option_id);
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
                    $row->serial_no          = (isset($row->serial_no) ? $row->serial_no : '');
                    //  $row->weight            = $item->weight;
                    $row->option             = $item->option_id;
                    $row->details            = (isset($item->comment) ? $item->comment : '');
                    $options                 = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItemstoSales($row->id, $item->warehouse_id, $item->option_id);
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
                    if ($quote_id || $sale_id || $sale_order_id) {
                        if (!empty($set_price)) {
                            foreach ($set_price as $key => $p) {
                                if ($p->unit_id == $row->unit) {
                                    $set_price[$key]->price = $row->real_unit_price;
                                }
                            }
                        }
                    }
                    // $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    // 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'set_price'=>$set_price, 'units' => $units, 'options' => $options, ];
                    if($pis){
                        foreach ($pis as $pi) {
                            if($pi->quantity_balance > 0){
                                $pr[$ri] = ['id' => $ri, 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' .($pi->expiry !=null ?  '('.$pi->expiry .')' : ''), 'category' => $row->category_id,
                                    'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units,  'set_price' => $set_price, 'options' => $options , 'pitems' => $pis, 'expiry' => $pi->expiry ];
                                $c++;
                            }
                        }
                    } else {
                        $pr[$ri] = ['id' => $ri, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id,
                            'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units,  'set_price' => $set_price, 'options' => $options,  'expiry'=>"0000-00-00" ];
                        $c++;
                    }
                }
                $this->data['quote_items'] = json_encode($pr);
                $this->data['quote']       = $getSaleslist;
                $this->data['inv']         = $getSaleslist;
                $this->data['quote_id']    = $q_id;
            }
            $this->data['projects']      = $this->site->getAllProject();
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['sale_order_id'] = $sale_order_id;
            $this->data['delivery_id']   = $delivery_id;
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
            $this->data['slnumber']      = $this->site->getReference('ren');
            $this->data['sltaxnumber']   = $this->site->getReference('st');
            $this->data['payment_ref']   = ''; //$this->site->getReference('pay');
            $this->data['sectionacc']    = $this->accounts_model->getAllChartAccount();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc                          = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('rental'), 'page' => lang('rental')], ['link' => '#', 'page' => lang('add_rental')]];
            $meta                        = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('rental/add', $meta, $this->data);
        }
    }

    public function edit($id = null)
    {
        $rsale = $this->sales_model->getEditSaleRequestBySaleID($id);
        if($rsale == false){ 
            $this->bpas->checkPermissions();
        }else{
            if($rsale->status != "approved"){
                $this->bpas->checkPermissions();
            }
        }
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
            $project_id             = $this->input->post('project') ? $this->input->post('project') : $this->Settings->default_project ;
            $warehouse_id           = $this->input->post('warehouse');
            $customer_id            = $this->input->post('customer');
            $biller_id              = $this->input->post('biller');
            $total_items            = $this->input->post('total_items');
            $sale_status            = $this->input->post('sale_status');
            $payment_status         = $this->input->post('payment_status');
            $payment_term           = $this->input->post('payment_term');
            $payment_term_details   = $this->site->getAllPaymentTermByID($payment_term);
            if ($this->Settings->payment_term) {
                $due_date           = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            }else{
                $due_date           = (isset($payment_term_details[0]->id)? date('Y-m-d', strtotime($date . '+' . $payment_term_details[0]->due_day . ' days')) : NULL);
            }
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details       = $this->site->getCompanyByID($customer_id);
            $customer               = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company.'/'.$customer_details->name : $customer_details->name;
            $biller_details         = $this->site->getCompanyByID($biller_id);
            $biller                 = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company.'/'.$biller_details->name : $biller_details->name;
            $note                   = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note             = $this->bpas->clear_tags($this->input->post('staff_note'));
            $commission_product     = 0;
            $total                  = 0;
            $product_tax            = 0;
            $product_discount       = 0;
            $gst_data               = [];
            $total_cgst             = $total_sgst       = $total_igst       = 0;
            $i                      = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
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
                $item_addition_type = isset($_POST['addition_type'][$r]) ? $_POST['addition_type'][$r] :'';
                $item_warranty      = isset($_POST['warranty'][$r]) ? $_POST['warranty'][$r] :'';
                $item_expiry      = isset($_POST['product_expiry'][$r]) ? $_POST['product_expiry'][$r] : '';
                $item_detail        = isset($_POST['product_detail'][$r]) ? $_POST['product_detail'][$r] : '';
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null; 
                    $cost = $product_details ? $product_details->cost : 0;
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
                    $getitems = $this->site->getProductByID($item_id);
                    $commission_item = $this->site->getProductCommissionByID($getitems->id);
                    $purchase_unit_cost = $product_details->cost;
                    if ($unit->id != $product_details->unit) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost,$unit);
                    } else {
                        $cost = $cost;
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
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'expiry'            => $item_expiry,
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
                    $productAcc = $this->site->getProductAccByProductId($item_id);
                    if($this->Settings->module_account == 1 && $item_type != 'manual' && $sale_status=='completed'){
                        $default_sale  = ($productAcc->revenue_account) ? $productAcc->revenue_account : $this->accounting_setting->default_sale;
                        $product_tax = $this->bpas->formatDecimal($product_tax);

                        $accTrans[] = array(
                            'tran_no'       => $id,
                            'tran_type'     => 'Sale',
                            'tran_date'     => $date,
                            'reference_no'  => $reference,
                            'account_code'  => $default_sale,
                            'amount'        => -($product_tax >0 ? ($subtotal-$product_tax) : $subtotal),
                            'narrative'     =>  $this->site->getAccountName($default_sale),
                            'description'   => $note,
                            'biller_id'     => $biller_id,
                            'project_id'    => $project_id,
                            'customer_id'   => $customer_id,
                            'created_by'    => $this->session->userdata('user_id'),
          
                        );
                        if($product_tax > 0){
                            $accTrans[] = array(
                                'tran_no'       => $id,
                                'tran_type'     => 'Sale',
                                'tran_date'     => $date,
                                'reference_no'  => $reference,
                                'account_code'  => $this->accounting_setting->default_sale_tax,
                                'amount'        => -($product_tax),
                                'narrative'     => $this->site->getAccountName($this->accounting_setting->default_sale_tax),
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'customer_id'   => $customer_id,
                                'created_by'    => $this->session->userdata('user_id'),
           
                            );
                        }
                        if($this->Settings->ar_in_product){
                            $ar_account  = ($productAcc->revenue_account) ? $productAcc->ar_account: $this->accounting_setting->default_sale;
                            $accTrans[] = array(
                                'tran_no'       => $id,
                                'tran_type'     => 'Sale',
                                'tran_date'     => $date,
                                'reference_no'  => $reference,
                                'account_code'  => $ar_account,
                                'amount'        => $subtotal,
                                'narrative'     => $this->site->getAccountName($ar_account),
                                'description'   => $note,
                                'biller_id'     => $biller_id,
                                'project_id'    => $project_id,
                                'customer_id'   => $customer_id,
                                'created_by'    => $this->session->userdata('user_id'),
                            );
                        }
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
            if(!empty($staff) && $staff->save_point){
                if (!empty($this->Settings->each_sale)) {
                    $saleman_award_points = floor(($grand_total / $this->Settings->each_sale) * $this->Settings->sa_point);
                }
            }

            //=======acounting=========//
            if($this->Settings->module_account == 1 && $sale_status=='completed'){
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
                'diagnosis_id'        => $this->input->post('diagnosis'),
                'ar_account'          => $this->input->post('account_receivable'),
            ];
            $payment         = null;
            $accTranPayments = null;
            if ($this->Settings->ar_in_product == 0) {
                $ac_account = $this->input->post('account_receivable') ? $this->input->post('account_receivable') : $this->accounting_setting->default_receivable;
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
                                    'account_code' => $ac_account,
                                    'amount' => ($grand_total - $amount_paying),
                                    'narrative' => $this->site->getAccountName($ac_account),
                                    'description' => $this->input->post('payment_note'),
                                    'biller_id' => $biller_id,
                                    'project_id' => $project_id,
                                    'customer_id' => $customer_id,
                                    'created_by'  => $this->session->userdata('user_id'),
                                    'activity_type' => $this->site->get_activity($ac_account)
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
                            'account_code' => $ac_account,
                            'amount' => $grand_total,
                            'narrative' => $this->site->getAccountName($ac_account),
                            'biller_id' => $biller_id,
                            'project_id' => $project_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'payment_id' => $id,
                            'activity_type' => $this->site->get_activity($ac_account)
                        );
                    }
                } else {
                    $accTranPayments[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Sale',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $ac_account,
                        'amount' => $grand_total,
                        'narrative' => $this->site->getAccountName($ac_account),
                        'biller_id' => $biller_id,
                        'project_id' => $project_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                        'payment_id' => $id,
                        'activity_type' => $this->site->get_activity($ac_account)
                    );
                }
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
            // $this->bpas->print_arrays($data);

            //----checked orver credit--------
            $cus_sales = $this->sales_model->getSalesTotals($customer_id);
            if(($customer_details->credit_limit != null) && (($cus_sales->total_amount - $cus_sales->paid) - ($inv->grand_total - $inv->paid) + $data['grand_total'] - $inv->paid) > $customer_details->credit_limit){
                $this->session->set_flashdata('error', lang("customer_due_over_credit_amount"));
                redirect($_SERVER["HTTP_REFERER"]);
            }
        }
            
        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products,$accTrans,$accTranPayments, $commission_product,'module_rental')) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('rental_updated'));
            admin_redirect('rental');
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
                $cate_id = !empty($item->subcategory_id) ? $item->subcategory_id : $item->category_id;
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItemstoSales($item->product_id, $item->warehouse_id, $item->option_id);
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
                $row->expiry          = $item->expiry;
                $row->base_unit       = (!empty($row->unit) ? $row->unit : $item->product_unit_id);
                $row->base_unit_price = (!empty($row->price) ? $row->price : $item->unit_price);
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity       += $item->quantity;
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
                        $pis = $this->site->getPurchasedItemstoSales($row->id, $item->warehouse_id, $item->option_id);
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
                $categories           = false;
                $categories           = $this->site->getCategoryByID($cate_id);
                $fiber_type           = $this->sales_model->getFiberTypeById($row->id);
                $categories->type_id  = $row->addition_type;
                // $categories->type_id  = $row->addition_type;
                // foreach ($fiber_type as $key => $value) {
                //     if ($categories->type_id == $value->id) {
                //         $fiber_type[$key]->qty = $value->qty + $row->base_quantity;
                //         $categories->qty = $fiber_type[$key]->qty;
                //     }
                // } 
                $fibers   = array('fiber' => $categories, 'type' => $fiber_type, );
                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                // $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                // 'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price'=>$set_price, 'units' => $units, 'options' => $options, 'fiber' => $fibers,'product_options' => $product_options, ];

                $pr[$ri] = [
                    'id' => $c, 'item_id' => $row->id, 'label' => $row->name. ' (' . $row->code . ')' . ($row->expiry != null ?  '('.$row->expiry .')' : ''), 
                    'category' => (isset($row->category_id) ? $row->category_id : ""), 
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 
                    'units' => $units, 'options' => $options, 'fiber' => $fibers, 'expiry'=> $row->expiry, 'set_price' => $set_price,
                ];
                $c++;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['projects']         = $this->site->getAllProject();
            $this->data['inv_items']        = json_encode($pr);
            $this->data['id']               = $id;
            $this->data['payment_term']     = $this->site->getAllPaymentTerm();
            $this->data['agencies']         = $this->site->getAllUsers();
            $this->data['billers']          = $this->site->getAllCompanies('biller');
            $this->data['units']            = $this->site->getAllBaseUnits();
            $this->data['tax_rates']        = $this->site->getAllTaxRates();
            $this->data['warehouses']       = $this->site->getAllWarehouses();
            $this->data['zones']            = $this->site->getAllZones();

            $this->data['salemans']         = $this->site->getAllSalemans($this->Settings->group_saleman_id);
            $this->data['sectionacc']    = $this->accounts_model->getAllChartAccount();
            $user = $this->site->getUser($this->session->userdata('user_id'));
            if ($this->Settings->multi_biller) {
                $this->data['user_billers'] = $user->multi_biller ? explode(',', $user->multi_biller) : null;
            } else {
                $this->data['user_billers'] = $user->biller_id ? ((array) $user->biller_id) : null;
            }
            $this->data['count']            = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['user_warehouses']  = $user->warehouse_id ? explode(',', $user->warehouse_id) : null;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('rental'), 'page' => lang('rental')], ['link' => '#', 'page' => lang('edit_rental')]];
            $meta = ['page_title' => lang('edit_rental'), 'bc' => $bc];
            $this->page_construct('rental/edit', $meta, $this->data);
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
                $item_expiry          = ($_POST['product_expiry'][$r] ? $_POST['product_expiry'][$r] : "");
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    $cost = $product_details->cost;
                    // $unit_price = $real_unit_price;
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
                        $pr_item_tax = $this->bpas->formatDecimal($item_tax * $item_unit_quantity);
                    }

                    $product_tax       += $pr_item_tax;
                    $subtotal           = $this->bpas->formatDecimal(($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit               = $item_unit ? $this->site->getUnitByID($item_unit) : false;
                    $purchase_unit_cost = $product_details->cost;
                    $getitems           = $this->site->getProductByID($item_id);
                    $commission_item    = $this->site->getProductCommissionByID($getitems->id);
                    if ($unit->id != $product_details->unit) {
                        $cost =$this->site->convertCostingToBase($purchase_unit_cost,$unit);
                    } else {
                        $cost = $cost;
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
                        'product_id'            => $item_id,
                        'product_code'          => $item_code,
                        'product_name'          => $item_name,
                        'product_type'          => $item_type,
                        'option_id'             => $item_option,
                        'purchase_unit_cost'    => $purchase_unit_cost ? $purchase_unit_cost : NULL,
                        'net_unit_price'        => $item_net_price,
                        'unit_price'            => $this->bpas->formatDecimal($item_net_price + $item_tax),
                        'quantity'              => $item_quantity,
                        'product_unit_id'       => $item_unit,
                        'product_unit_code'     => $unit ? $unit->code : null,
                        'unit_quantity'         => $item_unit_quantity,
                        'warehouse_id'          => $sale->warehouse_id,
                        'item_tax'              => $pr_item_tax,
                        'tax_rate_id'           => $item_tax_rate,
                        'tax'                   => $tax,
                        'discount'              => $item_discount,
                        'item_discount'         => $pr_item_discount,
                        'subtotal'              => $this->bpas->formatDecimal($subtotal),
                        'serial_no'             => $item_serial,
                        'sale_item_id'          => $sale_item_id,
                        'expiry'                => $item_expiry,
                        'commission'            => isset($commission_item->price) ? $commission_item->price * $item_quantity : 0,
                    ];
                    $commission_product += isset($commission_item->price) ? $commission_item->price * $item_quantity : 0;
                    $si_return[] = [
                        'id'           => $sale_item_id,
                        'sale_id'      => $id,
                        'product_id'   => $item_id,
                        'option_id'    => $item_option,
                        'quantity'     => (0 - $item_quantity),
                        'expiry'       => $item_expiry,
                        'warehouse_id' => $sale->warehouse_id,
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
                'saleman_award_points' => $saleman_award_points
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
        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return, $accTrans, null, null, $commission_product)) {
            $this->session->set_flashdata('message', lang('return_rental_added'));
            admin_redirect('rental');
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
                $row = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
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
                $row->expiry          = $item->expiry;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_unit       = (!empty($row->unit) ? $row->unit : $item->product_unit_id);
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
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('rental')], ['link' => '#', 'page' => lang('return_rental')]];
            $meta = ['page_title' => lang('return_rental'), 'bc' => $bc];
            $this->page_construct('rental/return_sale', $meta, $this->data);
        }
    }
    public function deposits($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->data['deposits'] = $this->sales_model->getSaleDeposits($id);
        $this->data['saleorder'] = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/deposits', $this->data);
    }
    public function add_deposit($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sale-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }
            $reference_no = $this->input->post('reference_no') ? $this->input->post('reference_no') :$this->site->getReference('pay',$sale->biller_id);

            $paymentAcc     = $this->site->getAccountSettingByBiller($sale->biller_id);
            $customer_deposit = $paymentAcc->default_sale_deposit ? $paymentAcc->default_sale_deposit:$this->accounting_setting->default_sale_deposit;

            if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
                $paying_to = $customer_deposit;
            }else{
                $cash_account = $this->site->getCashAccountByCode($this->input->post('paid_by'));
                $paying_to = $cash_account->account_code;
                if($cash_account->type=="bank"){
                    $bank_name      = $cash_account->name;
                    $account_name   = $this->input->post('account_name');
                    $account_number = $this->input->post('account_number');
                }else if($cash_account->type=="cheque"){
                    $bank_name      = $this->input->post('bank_name');
                    $cheque_number  = $this->input->post('cheque_number');
                    $cheque_date    = $this->bpas->fsd($this->input->post('cheque_date'));
                }
            }
            $payment = array(
                'date'          => $date,
                'sale_id'       => $this->input->post('sale_id'),
                'transaction'   => "SaleDeposit",
                'reference_no'  => $reference_no,
                'amount'        => $this->input->post('amount-paid'),
                'paid_by'       => $this->input->post('paid_by'),
                'cc_no'         => $this->input->post('paid_by')=='gift_card'?$this->input->post('gift_card_no') : '',
                'note'          => $this->input->post('note'),
                'created_by'    => $this->session->userdata('user_id'),
                'type'          => 'received',
                'currencies'    => json_encode($currencies),
                'account_code'  => $this->input->post('deposit_account'),
                'bank_account'  => $paying_to,
                'bank_name'     => $bank_name,
                'account_name'  => $account_name,
                'account_number'=> $account_number,
                'cheque_number' => $cheque_number,
                'cheque_date'   => $cheque_date,
            );
            
            //=====accountig=====//
            if($this->Settings->accounting == 1){
                $accTranPayments[] = array(
                    'tran_type'     => 'SaleDeposit',
                    'tran_date'     => $date,
                    'reference_no'  => $reference_no,
                    'account_code'  => $this->input->post('deposit_account') ? $this->input->post('deposit_account'): $customer_deposit,
                    'amount'        => -($this->input->post('amount-paid')+$this->input->post('discount')),
                    'narrative'     => 'Sale Deposit '.$sale->reference_no,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $sale->biller_id,
                    'project_id'    => $sale->project_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $sale->customer_id,
                );
                $accTranPayments[] = array(
                    'tran_type'     => 'SaleDeposit',
                    'tran_date'     => $date,
                    'reference_no'  => $reference_no,
                    'account_code'  => $paying_to,
                    'amount'        => $this->input->post('amount-paid'),
                    'narrative'     => 'Sale Deposit '.$sale->reference_no,
                    'description'   => $this->input->post('note'),
                    'biller_id'     => $sale->biller_id,
                    'project_id'    => $sale->project_id,
                    'created_by'    => $this->session->userdata('user_id'),
                    'customer_id'   => $sale->customer_id,
                );
            }
            //=====end accountig=====//

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
                $payment['attachment'] = $photo;
            }

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addDeposit($payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_added"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['error']        = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['sale']         = $sale;
            $this->data['payment_ref']  = '';
            $this->data['modal_js']     = $this->site->modal_js();
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountexpense('20');
            $this->data['currencies']   = $this->site->getAllCurrencies();
            $this->load->view($this->theme . 'sales/add_deposit', $this->data);
        }
    }
    
    public function edit_deposit($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->sales_model->getDepositByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        
        if ($this->form_validation->run() == true) {
            $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sale-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }
            $reference_no = $this->input->post('reference_no') ?$this->input->post('reference_no') : $this->site->getReference('pay',$sale->biller_id);
            $paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
            $customer_deposit = $paymentAcc->default_sale_deposit ? $paymentAcc->default_sale_deposit:$this->accounting_setting->default_sale_deposit;

            if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
                $paying_to = $customer_deposit;
            }else{
                $cash_account       = $this->site->getCashAccountByCode($this->input->post('paid_by'));
                $paying_to          = $cash_account->account_code;
                if($cash_account->type=="bank"){
                    $bank_name      = $cash_account->name;
                    $account_name   = $this->input->post('account_name');
                    $account_number = $this->input->post('account_number');
                }else if($cash_account->type=="cheque"){
                    $bank_name      = $this->input->post('bank_name');
                    $cheque_number  = $this->input->post('cheque_number');
                    $cheque_date    = $this->bpas->fsd($this->input->post('cheque_date'));
                }
            }
            $payment = array(
                'date'          => $date,
                'sale_id'       => $this->input->post('sale_id'),
                'transaction'   => "SaleDeposit",
                'reference_no'  => $reference_no,
                'amount'        => $this->input->post('amount-paid'),
                'paid_by'       => $this->input->post('paid_by'),
                'cc_no'         => $this->input->post('paid_by')=='gift_card'?$this->input->post('gift_card_no'):'',
                'note'          => $this->input->post('note'),
                'updated_by'    => $this->session->userdata('user_id'),
                'updated_at'    => date("Y-m-d H:i"),
                'type'          => 'received',
                'currencies'    => json_encode($currencies),
                'account_code'  => $this->input->post('deposit_account'),
                'bank_account'  => $paying_to,
                'bank_name'     => $bank_name,
                'account_name'  => $account_name,
                'account_number'=> $account_number,
                'cheque_number' => $cheque_number,
                'cheque_date'   => $cheque_date,
            );

            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $accTranPayments[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'SaleDeposit',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $customer_deposit,
                        'amount'        => -($this->input->post('amount-paid')+$this->input->post('discount')),
                        'narrative'     => 'Sale Deposit '.$sale->reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale->customer_id,
                    );
                $accTranPayments[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'SaleDeposit',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $paying_to,
                        'amount'        => $this->input->post('amount-paid'),
                        'narrative'     => 'Sale Deposit '.$sale->reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale->customer_id,
                    );
            }
                //=====end accountig=====//

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
                $payment['attachment'] = $photo;
            }
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDeposit($id, $payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['sale'] = $this->sales_model->getInvoiceByID($deposit->sale_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['deposit'] = $deposit;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountexpense('20');
            $this->load->view($this->theme . 'sales/edit_deposit', $this->data);
        }
    }
    public function return_deposit($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $deposit = $this->sales_model->getDepositByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        
        if ($this->form_validation->run() == true) {
            $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                if ( ! $this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang("amount_greater_than_deposit"));
                    redirect($_SERVER["HTTP_REFERER"]);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin  || $this->bpas->GP['sale-date']) {
                $date = $this->bpas->fld(trim($this->input->post('date')));
            } else {
                $date = ($this->Settings->date_with_time==0 ? date('Y-m-d') : date('Y-m-d H:i:s'));
            }
            $currencies = array();
            $camounts = $this->input->post("c_amount");
            if(!empty($camounts)){
                foreach($camounts as $key => $camount){
                    $currency = $this->input->post("currency");
                    $rate = $this->input->post("rate");
                    $currencies[] = array(
                                "amount" => $camounts[$key],
                                "currency" => $currency[$key],
                                "rate" => $rate[$key],
                            );
                }
            }
            $reference_no = $this->input->post('reference_no') ?$this->input->post('reference_no') : $this->site->getReference('pay',$sale->biller_id);
            $paymentAcc = $this->site->getAccountSettingByBiller($sale->biller_id);
            $customer_deposit = $paymentAcc->default_sale_deposit ? $paymentAcc->default_sale_deposit:$this->accounting_setting->default_sale_deposit;

            if($this->input->post('paid_by')=='deposit' || $this->input->post('paid_by')=='gift_card'){
                $paying_to = $customer_deposit;
            }else{
                $cash_account       = $this->site->getCashAccountByCode($this->input->post('paid_by'));
                $paying_to          = $cash_account->account_code;
                if($cash_account->type=="bank"){
                    $bank_name      = $cash_account->name;
                    $account_name   = $this->input->post('account_name');
                    $account_number = $this->input->post('account_number');
                }else if($cash_account->type=="cheque"){
                    $bank_name      = $this->input->post('bank_name');
                    $cheque_number  = $this->input->post('cheque_number');
                    $cheque_date    = $this->bpas->fsd($this->input->post('cheque_date'));
                }
            }
            $payment = array(
                'date'          => $date,
                'sale_id'       => $this->input->post('sale_id'),
                'transaction_type'=> "ReturnDeposit",
                'transaction'   => "SaleDeposit",
                'reference_no'  => $reference_no,
                'amount'        => $this->input->post('amount-paid'),
                'paid_by'       => $this->input->post('paid_by'),
                'cc_no'         => $this->input->post('paid_by')=='gift_card'?$this->input->post('gift_card_no'):'',
                'note'          => $this->input->post('note'),
                'updated_by'    => $this->session->userdata('user_id'),
                'updated_at'    => date("Y-m-d H:i"),
                'type'          => 'received',
                'currencies'    => json_encode($currencies),
                'account_code'  => $this->input->post('deposit_account'),
                'bank_account'  => $paying_to,
                'bank_name'     => $bank_name,
                'account_name'  => $account_name,
                'account_number'=> $account_number,
                'cheque_number' => $cheque_number,
                'cheque_date'   => $cheque_date,
            );

            //=====accountig=====//
            if($this->Settings->module_account == 1){
                $accTranPayments[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'SaleDeposit',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $customer_deposit,
                        'amount'        => -1 *($this->input->post('amount-paid')+$this->input->post('discount')),
                        'narrative'     => 'Sale Deposit '.$sale->reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale->customer_id,
                    );
                $accTranPayments[] = array(
                        'tran_no'       => $id,
                        'tran_type'     => 'SaleDeposit',
                        'tran_date'     => $date,
                        'reference_no'  => $reference_no,
                        'account_code'  => $paying_to,
                        'amount'        => $this->input->post('amount-paid'),
                        'narrative'     => 'Sale Deposit '.$sale->reference_no,
                        'description'   => $this->input->post('note'),
                        'biller_id'     => $sale->biller_id,
                        'project_id'    => $sale->project_id,
                        'created_by'    => $this->session->userdata('user_id'),
                        'customer_id'   => $sale->customer_id,
                    );
            }
                //=====end accountig=====//

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
                $payment['attachment'] = $photo;
            }
        } elseif ($this->input->post('edit_deposit')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addDeposit($payment, $customer_id, $accTranPayments)) {
            $this->session->set_flashdata('message', lang("deposit_updated"));
            redirect($_SERVER["HTTP_REFERER"]);
        } else {

            $this->data['sale'] = $this->sales_model->getInvoiceByID($deposit->sale_id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['deposit'] = $deposit;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['bankAccounts'] = $this->accounts_model->getAllChartAccountexpense('20');
            $this->load->view($this->theme . 'sales/return_deposit', $this->data);
        }
    }
    public function delete_deposit($id = null)
    {
        $this->bpas->checkPermissions('delete_deposit', true, 'customers');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $opay = $this->sales_model->getDepositByID($id);
        if ($this->sales_model->deleteDeposit($id)) {
            $this->session->set_flashdata('message', lang("deposit_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    public function deposit_note($id = null)
    {
        $this->bpas->checkPermissions('deposits', true, 'customers');
        $deposit = $this->sales_model->getDepositByID($id);
        $sale_order = $this->sales_model->getInvoiceByID($deposit->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($sale_order->biller_id);
        $this->data['customer'] = $this->site->getCompanyByID($sale_order->customer_id);
        $this->data['sale_order'] = $sale_order;
        $this->data['deposit'] = $deposit;
        $this->data['page_title'] = lang("deposit_note");
        
        $this->load->view($this->theme . 'sales/deposit_note', $this->data);
    }

    public function modal_view($id = null, $logo = null)
    {
        $this->bpas->checkPermissions('index', true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']      = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']        = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']    = $this->site->getUser($inv->created_by);
        $this->data['updated_by']    = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']     = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']           = $inv;
        $this->data['zone']          = $this->site->getZoneByID($inv->zone_id);
        $this->data['currency']      = $this->site->getCurrencyByCode($inv->currency);
        $this->data['rows']          = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale']   = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']   = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['islogo']        = $logo;
        $this->data['print']         = $this->site->Assgin_Print('Sale',$inv->id);
        $this->data['sold_by']       = $this->site->getsaleman($inv->saleman_by);
        $this->data['TotalSalesDue'] = $this->sales_model->getTotalSalesDue($inv->customer_id,'');
        $this->load->view($this->theme . 'rental/modal_view', $this->data);
    }
}