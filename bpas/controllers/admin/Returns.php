<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Returns extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->bpas->md('login');
        }
        if ($this->Supplier || $this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('returns', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('returns_model');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('accounts_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    public function add()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        if ($this->form_validation->run() == true) {
            $date             = ($this->Owner || $this->Admin || $this->GP['change_date']) ? $this->bpas->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            $reference        = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
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
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $this->bpas->fsd($_POST['product_expiry'][$r]) : null; 
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $cost             = $product_details->cost;
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
                    if ($product_details->type == 'combo') {
                        if ($combo_items = $this->site->getProductComboItems($item_id)) {
                            foreach ($combo_items as $combo_item) {
                                $combo_detail = $this->site->getProductByID($combo_item->id);
                                $combo_unit   = $this->site->getProductUnit($combo_item->id, $combo_detail->unit);
                                $combo_qty    = $combo_item->qty * $item_quantity;
                                $combo_price  = $combo_item->price;
                                $stockmoves[] = array(
                                    'transaction'    => 'Return',
                                    'product_id'     => $combo_detail->id,
                                    'product_type'   => $combo_detail->type,
                                    'product_code'   => $combo_detail->code,
                                    'product_name'   => $combo_detail->name,
                                    'quantity'       => $combo_qty,
                                    'unit_quantity'  => $combo_unit->unit_qty,
                                    'unit_code'      => $combo_unit->code,
                                    'unit_id'        => $combo_detail->unit,
                                    'warehouse_id'   => $warehouse_id,
                                    'date'           => $date,
                                    'real_unit_cost' => $combo_detail->cost,
                                    'reference_no'   => $reference,
                                    'user_id'        => $this->session->userdata('user_id'),
                                );
                            }
                        }
                    } else {
                        $stockmoves[] = array(
                            'transaction'    => 'Return',
                            'product_id'     => $item_id,
                            'product_type'   => $item_type,
                            'product_code'   => $item_code,
                            'product_name'   => $item_name,
                            'option_id'      => $item_option,
                            'quantity'       => $item_quantity,
                            'unit_quantity'  => $unit->operation_value ? $unit->operation_value : 1,
                            'unit_code'      => $unit->code,
                            'expiry'         => $item_expiry,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'real_unit_cost' => $cost,
                            'reference_no'   => $reference,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                    }
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
                        'real_unit_price'   => $real_unit_price,
                    ];
                    //========add accounting=========// 
                    if ($this->Settings->accounting == 1) {
                        $getproduct   = $this->site->getProductByID($item_id);
                        $default_sale = $this->accounting_setting->default_sale;
                        $accTrans[]   = array(
                            'tran_type' => 'Return',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount' => ($cost * abs($item_unit_quantity)),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_type' => 'Return',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => -($cost * abs($item_unit_quantity)),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_type' => 'Return',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => $subtotal,
                            'narrative' => $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $shipping - $order_discount), 4);
            $data           = [
                'date'              => $date,
                'reference_no'      => $reference,
                'sale_id'           => $this->input->post('si_reference'),
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $shipping,
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                'bank_account'      => $this->input->post('bank_account'),
            ];
            //=======acounting=========//
            if ($this->Settings->accounting == 1) {
                if (abs($order_discount) != 0) {
                    $accTrans[] = array(
                        'tran_type' => 'Return',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => -abs($order_discount),
                        'narrative' => 'Order Discount Return '.$sale->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if (abs($order_tax) != 0) {
                   $accTrans[] = array(
                        'tran_type' => 'Return',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => abs($order_tax),
                        'narrative' => 'Order Tax Return '.$sale->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if ($return_surcharge != 0) {
                    $accTrans[] = array(
                        'tran_type' => 'Return',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->other_income,
                        'amount' => -$return_surcharge,
                        'narrative' => 'Surcharge Return '.$sale->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                $paying_to = ($this->input->post('bank_account') !=0) ? $this->input->post('bank_account'): $this->accounting_setting->default_cash;
                $accTrans[] = array(
                    'tran_type' => 'Return',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $paying_to,
                    'amount' => -($grand_total),
                    'narrative' => $this->site->getAccountName($paying_to),
                    'description' => $note,
                    'biller_id' => $biller_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                );
            }
            //============end accounting=======//
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
        if ($this->form_validation->run() == true && $this->returns_model->addReturn($data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_added'));
            admin_redirect('returns');
        } else {
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['reference']     = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $this->data['si_references'] = $this->sales_model->getSaleRefenceByCustomer();
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('add_return')]];
            $meta = ['page_title' => lang('add_return'), 'bc' => $bc];
            $this->page_construct('returns/add', $meta, $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->bpas->checkPermissions(null, true);
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if ($this->returns_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                $this->bpas->send_json(['error' => 0, 'msg' => lang('return_deleted')]);
            }
            $this->session->set_flashdata('message', lang('return_deleted'));
            admin_redirect('welcome');
        }
    }

    public function edit($id = null)
    {
        $this->bpas->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->returns_model->getReturnByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->bpas->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        if ($this->form_validation->run() == true) {
            $date             = ($this->Owner || $this->Admin || $this->GP['change_date']) ? $this->bpas->fld(trim($this->input->post('date'))) : $inv->date;
            $reference        = $this->input->post('reference_no');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
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
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $this->bpas->fsd($_POST['product_expiry'][$r]) : null; 
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $cost             = $product_details->cost;
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
                    if ($product_details->type == 'combo') {
                        if ($combo_items = $this->site->getProductComboItems($item_id)) {
                            foreach ($combo_items as $combo_item) {
                                $combo_detail = $this->site->getProductByID($combo_item->id);
                                $combo_unit   = $this->site->getProductUnit($combo_item->id, $combo_detail->unit);
                                $combo_qty    = $combo_item->qty * $item_quantity;
                                $combo_price  = $combo_item->price;
                                $stockmoves[] = array(
                                    'transaction_id' => $id,
                                    'transaction'    => 'Return',
                                    'product_id'     => $combo_detail->id,
                                    'product_type'   => $combo_detail->type,
                                    'product_code'   => $combo_detail->code,
                                    'product_name'   => $combo_detail->name,
                                    'quantity'       => $combo_qty,
                                    'unit_quantity'  => $combo_unit->unit_qty,
                                    'unit_code'      => $combo_unit->code,
                                    'unit_id'        => $combo_detail->unit,
                                    'warehouse_id'   => $warehouse_id,
                                    'date'           => $date,
                                    'real_unit_cost' => $combo_detail->cost,
                                    'reference_no'   => $reference,
                                    'user_id'        => $this->session->userdata('user_id'),
                                );
                            }
                        }
                    } else {
                        $stockmoves[] = array(
                            'transaction_id' => $id,
                            'transaction'    => 'Return',
                            'product_id'     => $item_id,
                            'product_type'   => $item_type,
                            'product_code'   => $item_code,
                            'product_name'   => $item_name,
                            'option_id'      => $item_option,
                            'quantity'       => $item_quantity,
                            'unit_quantity'  => $unit->operation_value ? $unit->operation_value : 1,
                            'unit_code'      => $unit->code,
                            'expiry'         => $item_expiry,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'real_unit_cost' => $cost,
                            'reference_no'   => $reference,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                    }
                    $product  = [
                        'return_id'         => $id,
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
                        'real_unit_price'   => $real_unit_price,
                    ];
                    //========add accounting=========//
                    if ($this->Settings->accounting == 1) {
                        $getproduct   = $this->site->getProductByID($item_id);
                        $default_sale = $this->accounting_setting->default_sale;
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Return',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_stock,
                            'amount' => ($cost * abs($item_unit_quantity)),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_stock),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Return',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $this->accounting_setting->default_cost,
                            'amount' => -($cost * abs($item_unit_quantity)),
                            'narrative' => $this->site->getAccountName($this->accounting_setting->default_cost),
                            'description' => $note,
                            'biller_id' => $biller_id,
                            'customer_id' => $customer_id,
                            'created_by'  => $this->session->userdata('user_id'),
                            'activity_type' => $this->site->get_activity($this->accounting_setting->default_cost)
                        );
                        $accTrans[] = array(
                            'tran_no' => $id,
                            'tran_type' => 'Return',
                            'tran_date' => $date,
                            'reference_no' => $reference,
                            'account_code' => $default_sale,
                            'amount' => $subtotal,
                            'narrative' => $this->site->getAccountName($default_sale),
                            'description' => $note,
                            'biller_id' => $biller_id,
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $shipping - $order_discount), 4);
            $data           = [
                'date'              => $date,
                'reference_no'      => $reference,
                'sale_id'           => $this->input->post('si_reference'),
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'grand_total'       => $grand_total,
                'shipping'          => $shipping,
                'total_items'       => $total_items,
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
                'bank_account'      => $this->input->post('bank_account'),
            ];
            //=======acounting=========//
            if($this->Settings->accounting == 1){
                if(abs($order_discount) != 0){
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Return',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_discount,
                        'amount' => -abs($order_discount),
                        'narrative' => 'Order Discount Return '.$sale->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if (abs($order_tax) != 0) {
                   $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Return',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->default_sale_tax,
                        'amount' => abs($order_tax),
                        'narrative' => 'Order Tax Return '.$sale->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                if ($return_surcharge != 0) {
                    $accTrans[] = array(
                        'tran_no' => $id,
                        'tran_type' => 'Return',
                        'tran_date' => $date,
                        'reference_no' => $reference,
                        'account_code' => $this->accounting_setting->other_income,
                        'amount' => -$return_surcharge,
                        'narrative' => 'Surcharge Return '.$sale->reference_no,
                        'description' => $note,
                        'biller_id' => $biller_id,
                        'customer_id' => $customer_id,
                        'created_by'  => $this->session->userdata('user_id'),
                    );
                }
                $paying_to  = ($this->input->post('bank_account') !=0) ? $this->input->post('bank_account'): $this->accounting_setting->default_cash;
                $accTrans[] = array(
                    'tran_no' => $id,
                    'tran_type' => 'Return',
                    'tran_date' => $date,
                    'reference_no' => $reference,
                    'account_code' => $paying_to,
                    'amount' => -($grand_total),
                    'narrative' => $this->site->getAccountName($paying_to),
                    'description' => $note,
                    'biller_id' => $biller_id,
                    'customer_id' => $customer_id,
                    'created_by'  => $this->session->userdata('user_id'),
                );
            }
            //============end accounting=======//
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
        if ($this->form_validation->run() == true && $this->returns_model->updateReturn($id, $data, $products, $stockmoves, $accTrans)) {
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_updated'));
            admin_redirect('returns');
        } else {
            $this->data['inv'] = $inv;
            $this->bpas->deadlineDayEditing($this->data['inv']->date);
            $inv_items = $this->returns_model->getReturnItems($id);
            $c         = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $row->id              = $item->product_id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->item_tax_method = $row->tax_method;
                $options              = $this->returns_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }
                $row->real_unit_price = $row->price;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->serial          = $item->serial_no;
                $row->option          = $item->option_id;
                $row->tax_rate        = $item->tax_rate_id;
                $row->comment         = '';
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $units     = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                $ri        = $this->Settings->item_addition ? $row->id : $c;
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[$ri]   = [
                    'id'  => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate,'set_price' => $set_price, 'units' => $units, 'options' => $options, ];
                $c++;
            }
            $this->data['inv_items']     = json_encode($pr);
            $this->data['id']            = $id;
            $this->data['error']         = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['units']         = $this->site->getAllBaseUnits();
            $this->data['count']         = explode(',', $this->session->userdata('warehouse_id'));
            $this->data['reference']     = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $this->data['si_references'] = $this->sales_model->getSaleRefenceByCustomer();
            
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('edit_return')]];
            $meta = ['page_title' => lang('edit_return'), 'bc' => $bc];
            $this->page_construct('returns/edit', $meta, $this->data);
        }
    }

    public function getReturns($warehouse = null)
    {
        $this->bpas->checkPermissions('index');
        if ((!$this->Owner || !$this->Admin) && !$warehouse) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $this->load->library('datatables');
        $this->datatables
            ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment")
            ->from('returns')->where('type', 'returned');
        if ($warehouse) {
            $this->datatables->where('warehouse_id', $warehouse);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            //$this->datatables->where('created_by', $this->session->userdata('user_id'));
            $this->datatables->where("FIND_IN_SET(bpas_returns.warehouse_id, '".$this->session->userdata('warehouse_id')."')");
        }
        $this->datatables->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('returns/edit/$1') . "' class='tip' title='" . lang('edit_return') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_return') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('returns/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        echo $this->datatables->generate();
    }

    public function index($warehouse_id = null)
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
                $this->data['warehouses']   = $this->site->getAllWarehouses();
            } else {
                $this->data['warehouses']   = null;
            }
            $this->data['count_warehouses'] = $count;
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        }
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('returns')]];
        $meta = ['page_title' => lang('returns'), 'bc' => $bc];
        $this->page_construct('returns/index', $meta, $this->data);
    }

    public function keep($warehouse_id = null)
    {
        $this->bpas->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }
        $bc   = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('keep')));
        $meta = array('page_title' => lang('keep'), 'bc' => $bc);
        $this->page_construct('returns/keep_list', $meta, $this->data);
    }

    public function getKeep($warehouse_id = null)
    {
        $this->bpas->checkPermissions('index');
        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment")
                ->from('returns')
                ->where(array('type' => 'keep','warehouse_id'=> $warehouse_id));
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns')}.customer, grand_total, {$this->db->dbprefix('returns')}.attachment")
                ->from('returns')
                ->where('type', 'keep');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column("Actions", "<div class=\"text-center\"><a href='" . admin_url('returns/edit/$1') . "' class='tip' title='" . lang("edit_return") . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang("delete_return") . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('returns/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", "id");
        echo $this->datatables->generate();
    }

    public function add_keep()
    {
        $this->bpas->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        if ($this->form_validation->run() == true) {
            $date             = ($this->Owner || $this->Admin || $this->GP['change_date']) ? $this->bpas->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            $reference        = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->bpas->clear_tags($this->input->post('note'));
            $staff_note       = $this->bpas->clear_tags($this->input->post('staff_note'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
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
                $item_option        = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' && $_POST['product_option'][$r] != 'NULL' ? $_POST['product_option'][$r] : null;
                $item_expiry        = isset($_POST['product_expiry'][$r]) && !empty($_POST['product_expiry'][$r]) && $_POST['product_expiry'][$r] != 'false' && $_POST['product_expiry'][$r] != 'undefined' && $_POST['product_expiry'][$r] != 'null' && $_POST['product_expiry'][$r] != 'NULL' && $_POST['product_expiry'][$r] != '00/00/0000' && $_POST['product_expiry'][$r] != '' ? $this->bpas->fsd($_POST['product_expiry'][$r]) : null; 
                $real_unit_price    = $this->bpas->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->bpas->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = isset($_POST['serial'][$r]) ? $_POST['serial'][$r] : null;
                $item_tax_rate      = isset($_POST['product_tax'][$r]) ? $_POST['product_tax'][$r] : null;
                $item_discount      = isset($_POST['product_discount'][$r]) ? $_POST['product_discount'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $cost             = $product_details->cost;
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
                    if ($product_details->type == 'combo') {
                        if ($combo_items = $this->site->getProductComboItems($item_id)) {
                            foreach ($combo_items as $combo_item) {
                                $combo_detail = $this->site->getProductByID($combo_item->id);
                                $combo_unit   = $this->site->getProductUnit($combo_item->id, $combo_detail->unit);
                                $combo_qty    = $combo_item->qty * $item_quantity;
                                $combo_price  = $combo_item->price;
                                $stockmoves[] = array(
                                    'transaction'    => 'Return',
                                    'product_id'     => $combo_detail->id,
                                    'product_type'   => $combo_detail->type,
                                    'product_code'   => $combo_detail->code,
                                    'product_name'   => $combo_detail->name,
                                    'quantity'       => $combo_qty,
                                    'unit_quantity'  => $combo_unit->unit_qty,
                                    'unit_code'      => $combo_unit->code,
                                    'unit_id'        => $combo_detail->unit,
                                    'warehouse_id'   => $warehouse_id,
                                    'date'           => $date,
                                    'real_unit_cost' => $combo_detail->cost,
                                    'reference_no'   => $reference,
                                    'user_id'        => $this->session->userdata('user_id'),
                                );
                            }
                        }
                    } else {
                        $stockmoves[] = array(
                            'transaction'    => 'Return',
                            'product_id'     => $item_id,
                            'product_type'   => $item_type,
                            'product_code'   => $item_code,
                            'product_name'   => $item_name,
                            'option_id'      => $item_option,
                            'quantity'       => $item_quantity,
                            'unit_quantity'  => $unit->operation_value ? $unit->operation_value : 1,
                            'unit_code'      => $unit->code,
                            'expiry'         => $item_expiry,
                            'unit_id'        => $item_unit,
                            'warehouse_id'   => $warehouse_id,
                            'date'           => $date,
                            'real_unit_cost' => $cost,
                            'reference_no'   => $reference,
                            'user_id'        => $this->session->userdata('user_id'),
                        );
                    }
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
                        'real_unit_price'   => $real_unit_price,
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
            $grand_total    = $this->bpas->formatDecimal(($total + $total_tax + $shipping - $order_discount), 4);
            $data           = [
                'date'              => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                'type'              => 'keep',
                'expired_date'      => $this->bpas->fld($this->input->post('expired_date')),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
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
        if ($this->form_validation->run() == true && $this->returns_model->addReturn($data, $products, $stockmoves)) {
            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('keep_added'));
            admin_redirect('returns/keep');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['count']      = explode(',', $this->session->userdata('warehouse_id'));
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns/keep'), 'page' => lang('keep')], ['link' => '#', 'page' => lang('add_keep')]];
            $meta = ['page_title' => lang('add_keep'), 'bc' => $bc];
            $this->page_construct('returns/add_keep', $meta, $this->data);
        }
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed  = $this->bpas->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $rows = $this->returns_model->getProductNames($sr);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c      = uniqid(mt_rand(), true);
                $option = false;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $row->item_tax_method = $row->tax_method;
                $options = $this->returns_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->qty             = 1;
                $row->discount        = '0';
                $row->serial          = '';
                $row->comment         = '';
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $units     = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate  = $this->site->getTaxRateByID($row->tax_rate);
                $set_price = $this->site->getUnitByProId($row->id);
                $pr[] = [
                    'id'  => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'tax_rate' => $tax_rate, 'set_price'=> $set_price,'units' => $units, 'options' => $options, ];
                $r++;
            }
            $this->bpas->send_json($pr);
        } else {
            $this->bpas->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function view($id = null)
    {
        $this->bpas->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->returns_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->bpas->view_rights($inv->created_by, true);
        }
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $this->data['rows']       = $this->returns_model->getReturnItems($id);

        $this->load->view($this->theme . 'returns/view', $this->data);
    }
}